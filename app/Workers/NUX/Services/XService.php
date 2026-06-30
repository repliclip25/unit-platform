<?php

namespace App\Workers\NUX\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * X (Twitter) OAuth 2.0 with PKCE service for NUX.
 *
 * Config required in .env:
 *   X_CLIENT_ID
 *   X_CLIENT_SECRET
 *   X_REDIRECT_URI   (e.g. https://your-domain.com/nux/x/callback)
 *
 * Scopes requested:
 *   tweet.read users.read offline.access
 *
 * Uses PKCE (code_verifier / code_challenge) — no client secret in the
 * authorization URL. The code_verifier is stored temporarily per-user
 * in nux_oauth_tokens and cleared after the callback.
 */
class XService
{
    private const AUTH_URL    = 'https://twitter.com/i/oauth2/authorize';
    private const TOKEN_URL   = 'https://api.twitter.com/2/oauth2/token';
    private const ME_URL      = 'https://api.twitter.com/2/users/me';
    private const TWEETS_URL  = 'https://api.twitter.com/2/users/{id}/tweets';

    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $this->clientId     = config('services.x.client_id')     ?: env('X_CLIENT_ID', '');
        $this->clientSecret = config('services.x.client_secret') ?: env('X_CLIENT_SECRET', '');
        $this->redirectUri  = config('services.x.redirect')      ?: env('X_REDIRECT_URI', '');
    }

    /**
     * Build the authorization URL and persist the code_verifier for this user.
     * Returns the URL to redirect the user to.
     */
    public function authorizationUrl(int $userId, string $state): string
    {
        $codeVerifier  = Str::random(64);
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        // Store verifier so the callback can complete the PKCE exchange
        DB::table('nux_oauth_tokens')->upsert([
            'user_id'       => $userId,
            'platform'      => 'x',
            'access_token'  => Crypt::encryptString('pending'),
            'code_verifier' => $codeVerifier,
            'state'         => $state,
            'active'        => false,
            'created_at'    => now(),
            'updated_at'    => now(),
        ], ['user_id', 'platform'], ['code_verifier', 'state', 'updated_at']);

        return self::AUTH_URL . '?' . http_build_query([
            'response_type'         => 'code',
            'client_id'             => $this->clientId,
            'redirect_uri'          => $this->redirectUri,
            'scope'                 => 'tweet.read users.read offline.access',
            'state'                 => $state,
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);
    }

    /**
     * Exchange authorization code for tokens.
     * Validates state and code_verifier, then stores encrypted tokens.
     */
    public function handleCallback(int $userId, string $code, string $state): bool
    {
        $row = DB::table('nux_oauth_tokens')
            ->where('user_id', $userId)
            ->where('platform', 'x')
            ->where('state', $state)
            ->first();

        if (!$row || !$row->code_verifier) {
            Log::error('[NUX X] State/verifier mismatch', ['user_id' => $userId]);
            return false;
        }

        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post(self::TOKEN_URL, [
                'code'          => $code,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $this->redirectUri,
                'code_verifier' => $row->code_verifier,
            ]);

        if (!$response->successful()) {
            Log::error('[NUX X] Token exchange failed', ['body' => $response->body()]);
            return false;
        }

        $data    = $response->json();
        $profile = $this->fetchMe($data['access_token']);

        $expiresAt = isset($data['expires_in'])
            ? now()->addSeconds((int) $data['expires_in'])
            : null;

        DB::table('nux_oauth_tokens')
            ->where('user_id', $userId)
            ->where('platform', 'x')
            ->update([
                'platform_user_id'     => $profile['id']       ?? null,
                'platform_username'    => $profile['username'] ?? null,
                'platform_display_name'=> $profile['name']     ?? null,
                'access_token'         => Crypt::encryptString($data['access_token']),
                'refresh_token'        => isset($data['refresh_token'])
                                            ? Crypt::encryptString($data['refresh_token'])
                                            : null,
                'token_expires_at'     => $expiresAt,
                'code_verifier'        => null,
                'state'                => null,
                'active'               => true,
                'updated_at'           => now(),
            ]);

        return true;
    }

    /**
     * Fetch recent tweets by the authenticated user.
     */
    public function fetchRecentTweets(int $userId, int $limit = 10): array
    {
        $row = DB::table('nux_oauth_tokens')
            ->where('user_id', $userId)
            ->where('platform', 'x')
            ->where('active', true)
            ->first();

        if (!$row) return [];

        $token         = $this->resolveToken($userId, $row);
        $platformUserId = $row->platform_user_id;

        if (!$token || !$platformUserId) return [];

        try {
            $url      = str_replace('{id}', $platformUserId, self::TWEETS_URL);
            $response = Http::withToken($token)
                ->timeout(15)
                ->get($url, [
                    'max_results'  => $limit,
                    'tweet.fields' => 'created_at,text',
                    'exclude'      => 'retweets,replies',
                ]);

            if (!$response->successful()) return [];

            return array_map(fn($tweet) => [
                'post_id'   => $tweet['id'],
                'platform'  => 'x',
                'post_text' => $tweet['text'] ?? '',
                'post_url'  => "https://x.com/{$row->platform_username}/status/{$tweet['id']}",
                'posted_at' => $tweet['created_at'] ?? null,
            ], $response->json('data') ?? []);

        } catch (\Throwable $e) {
            Log::warning('[NUX X] fetchRecentTweets failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * True if a valid, non-expired token exists for this user.
     */
    public function isConnected(int $userId): bool
    {
        $row = DB::table('nux_oauth_tokens')
            ->where('user_id', $userId)
            ->where('platform', 'x')
            ->where('active', true)
            ->first();

        if (!$row) return false;
        if ($row->token_expires_at && now()->gt($row->token_expires_at) && !$row->refresh_token) return false;

        return true;
    }

    /**
     * Disconnect — mark token inactive.
     */
    public function disconnect(int $userId): void
    {
        DB::table('nux_oauth_tokens')
            ->where('user_id', $userId)
            ->where('platform', 'x')
            ->update(['active' => false, 'updated_at' => now()]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function resolveToken(int $userId, object $row): ?string
    {
        if ($row->token_expires_at && now()->gte($row->token_expires_at) && $row->refresh_token) {
            return $this->refreshAccessToken($userId, $row);
        }

        return Crypt::decryptString($row->access_token);
    }

    private function refreshAccessToken(int $userId, object $row): ?string
    {
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post(self::TOKEN_URL, [
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => Crypt::decryptString($row->refresh_token),
                ]);

            if (!$response->successful()) return null;

            $data      = $response->json();
            $expiresAt = isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : null;

            DB::table('nux_oauth_tokens')
                ->where('user_id', $userId)
                ->where('platform', 'x')
                ->update([
                    'access_token'     => Crypt::encryptString($data['access_token']),
                    'refresh_token'    => isset($data['refresh_token'])
                                           ? Crypt::encryptString($data['refresh_token'])
                                           : $row->refresh_token,
                    'token_expires_at' => $expiresAt,
                    'updated_at'       => now(),
                ]);

            return $data['access_token'];

        } catch (\Throwable $e) {
            Log::warning('[NUX X] Token refresh failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function fetchMe(string $accessToken): array
    {
        try {
            return Http::withToken($accessToken)
                ->timeout(10)
                ->get(self::ME_URL, ['user.fields' => 'name,username'])
                ->json('data') ?? [];
        } catch (\Throwable) {
            return [];
        }
    }
}

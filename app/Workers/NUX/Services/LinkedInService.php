<?php

namespace App\Workers\NUX\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * LinkedIn OAuth 2.0 service for NUX.
 *
 * Config required in .env:
 *   LINKEDIN_CLIENT_ID
 *   LINKEDIN_CLIENT_SECRET
 *   LINKEDIN_REDIRECT_URI   (e.g. https://your-domain.com/nux/linkedin/callback)
 *
 * Scopes requested:
 *   openid profile email           — identity
 *   w_member_social                — post on behalf of member
 *   r_basicprofile                 — read profile
 *
 * Note: Reading your own posts programmatically requires LinkedIn's
 * Marketing Developer Platform approval. Until then, NUX accepts
 * manually pasted post text or URL via the fast-track / manual submit flow.
 */
class LinkedInService
{
    private const AUTH_URL    = 'https://www.linkedin.com/oauth/v2/authorization';
    private const TOKEN_URL   = 'https://www.linkedin.com/oauth/v2/accessToken';
    private const PROFILE_URL = 'https://api.linkedin.com/v2/userinfo';
    private const POSTS_URL   = 'https://api.linkedin.com/v2/ugcPosts';

    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $this->clientId     = config('services.linkedin.client_id')     ?: env('LINKEDIN_CLIENT_ID', '');
        $this->clientSecret = config('services.linkedin.client_secret') ?: env('LINKEDIN_CLIENT_SECRET', '');
        $this->redirectUri  = config('services.linkedin.redirect')      ?: env('LINKEDIN_REDIRECT_URI', '');
    }

    /**
     * Build the authorization URL to redirect the user to LinkedIn.
     */
    public function authorizationUrl(string $state): string
    {
        return self::AUTH_URL . '?' . http_build_query([
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'state'         => $state,
            'scope'         => 'openid profile email w_member_social',
        ]);
    }

    /**
     * Exchange authorization code for access + refresh tokens.
     * Stores encrypted tokens in nux_oauth_tokens.
     */
    public function handleCallback(int $userId, string $code, ?int $deploymentId = null): bool
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $this->redirectUri,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (!$response->successful()) {
            Log::error('[NUX LinkedIn] Token exchange failed', ['body' => $response->body()]);
            return false;
        }

        $data = $response->json();

        // Fetch profile info
        $profile = $this->fetchProfile($data['access_token']);

        $expiresAt = isset($data['expires_in'])
            ? now()->addSeconds((int) $data['expires_in'])
            : null;

        DB::table('nux_oauth_tokens')->upsert([
            'user_id'              => $userId,
            'deployment_id'        => $deploymentId,
            'platform'             => 'linkedin',
            'platform_user_id'     => $profile['sub'] ?? null,
            'platform_username'    => $profile['email'] ?? null,
            'platform_display_name'=> $profile['name'] ?? null,
            'access_token'         => Crypt::encryptString($data['access_token']),
            'refresh_token'        => isset($data['refresh_token'])
                                        ? Crypt::encryptString($data['refresh_token'])
                                        : null,
            'token_expires_at'     => $expiresAt,
            'active'               => true,
            'created_at'           => now(),
            'updated_at'           => now(),
        ], ['user_id', 'platform'], [
            'access_token', 'refresh_token', 'token_expires_at',
            'platform_user_id', 'platform_username', 'platform_display_name',
            'deployment_id', 'active', 'updated_at',
        ]);

        return true;
    }

    /**
     * Fetch recent posts authored by the authenticated user.
     * Returns an array of post objects.
     *
     * Note: This requires Marketing Developer Platform access in production.
     * Returns empty array if API access is not granted.
     */
    public function fetchRecentPosts(int $userId, int $limit = 10): array
    {
        $token = $this->getAccessToken($userId);
        if (!$token) return [];

        try {
            $profileResponse = Http::withToken($token)
                ->timeout(15)
                ->get(self::PROFILE_URL);

            if (!$profileResponse->successful()) return [];

            $authorUrn = 'urn:li:person:' . ($profileResponse->json('sub') ?? '');

            $response = Http::withToken($token)
                ->timeout(15)
                ->get(self::POSTS_URL, [
                    'q'       => 'authors',
                    'authors' => "List({$authorUrn})",
                    'count'   => $limit,
                ]);

            if (!$response->successful()) return [];

            return array_map(fn($post) => $this->normalizePost($post), $response->json('elements') ?? []);

        } catch (\Throwable $e) {
            Log::warning('[NUX LinkedIn] fetchRecentPosts failed', ['error' => $e->getMessage()]);
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
            ->where('platform', 'linkedin')
            ->where('active', true)
            ->first();

        if (!$row) return false;
        if ($row->token_expires_at && now()->gt($row->token_expires_at)) return false;

        return true;
    }

    /**
     * Disconnect — mark token inactive.
     */
    public function disconnect(int $userId): void
    {
        DB::table('nux_oauth_tokens')
            ->where('user_id', $userId)
            ->where('platform', 'linkedin')
            ->update(['active' => false, 'updated_at' => now()]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function getAccessToken(int $userId): ?string
    {
        $row = DB::table('nux_oauth_tokens')
            ->where('user_id', $userId)
            ->where('platform', 'linkedin')
            ->where('active', true)
            ->first();

        if (!$row) return null;

        // Attempt refresh if expired and refresh token exists
        if ($row->token_expires_at && now()->gte($row->token_expires_at) && $row->refresh_token) {
            return $this->refreshAccessToken($userId, $row);
        }

        return Crypt::decryptString($row->access_token);
    }

    private function refreshAccessToken(int $userId, object $row): ?string
    {
        try {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'grant_type'    => 'refresh_token',
                'refresh_token' => Crypt::decryptString($row->refresh_token),
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if (!$response->successful()) return null;

            $data      = $response->json();
            $expiresAt = isset($data['expires_in']) ? now()->addSeconds($data['expires_in']) : null;

            DB::table('nux_oauth_tokens')
                ->where('user_id', $userId)
                ->where('platform', 'linkedin')
                ->update([
                    'access_token'     => Crypt::encryptString($data['access_token']),
                    'token_expires_at' => $expiresAt,
                    'updated_at'       => now(),
                ]);

            return $data['access_token'];

        } catch (\Throwable $e) {
            Log::warning('[NUX LinkedIn] Token refresh failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function fetchProfile(string $accessToken): array
    {
        try {
            return Http::withToken($accessToken)
                ->timeout(10)
                ->get(self::PROFILE_URL)
                ->json() ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function normalizePost(array $post): array
    {
        $text = $post['specificContent']['com.linkedin.ugc.ShareContent']['shareCommentary']['text']
            ?? $post['commentary']
            ?? '';

        return [
            'post_id'   => $post['id'] ?? null,
            'platform'  => 'linkedin',
            'post_text' => $text,
            'post_url'  => isset($post['id'])
                ? 'https://www.linkedin.com/feed/update/' . $post['id']
                : null,
            'posted_at' => isset($post['created']['time'])
                ? date('c', (int) ($post['created']['time'] / 1000))
                : null,
        ];
    }
}

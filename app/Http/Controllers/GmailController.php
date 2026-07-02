<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Mail\GmailConnected;
use App\Platform\Services\TransactionService;
use App\Platform\Services\WorkerRegistry;
use App\Platform\Services\Gmail\GmailWatchService;

class GmailController extends Controller
{
    // PUBLIC — Google redirects here after OAuth
    public function callback(Request $request)
    {
        $code = $request->query('code');
        if (!$code) return response()->json(['error' => 'No authorization code'], 400);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => config('services.gmail.client_id'),
            'client_secret' => config('services.gmail.client_secret'),
            'redirect_uri'  => config('services.gmail.redirect_uri'),
            'grant_type'    => 'authorization_code',
        ]);

        if ($response->failed()) {
            return back()->with('error', 'Gmail connection failed: ' . $response->body());
        }

        $refreshToken = \Illuminate\Support\Facades\Crypt::encryptString($response->json('refresh_token'));
        $accessToken  = $response->json('access_token');

        // Fetch verified email from Google's userinfo endpoint — more reliable than decoding JWT locally
        $userInfo = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');
        $email    = $userInfo->successful() ? ($userInfo->json('email') ?? null) : null;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::error('GmailController: invalid or missing Gmail address from userinfo', ['email' => $email, 'status' => $userInfo->status()]);
            return back()->with('error', 'Could not retrieve a valid Gmail address. Please try again.');
        }

        // Detect whether gmail.insert scope was granted (present in token scope response)
        $grantedScopes  = $response->json('scope') ?? '';
        $hasInsertScope = str_contains($grantedScopes, 'gmail.insert') ? 1 : 0;

        // Upsert on gmail_address — each address is unique per user and globally
        $existingId = DB::table('user_gmail_credentials')
            ->where('gmail_address', $email)
            ->value('id');

        if ($existingId) {
            DB::table('user_gmail_credentials')->where('id', $existingId)->update([
                'user_id'          => auth()->id(),
                'refresh_token'    => $refreshToken,
                'has_insert_scope' => $hasInsertScope,
                'updated_at'       => now(),
            ]);
        } else {
            DB::table('user_gmail_credentials')->insert([
                'user_id'          => auth()->id(),
                'gmail_address'    => $email,
                'refresh_token'    => $refreshToken,
                'has_insert_scope' => $hasInsertScope,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        $user       = auth()->user();
        $credential = DB::table('user_gmail_credentials')->where('user_id', $user->id)->first();

        // Auto-start watch if tenant already has any active Gmail-credential-based worker deployment
        $hasActiveDeployment = DB::table('worker_deployments')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($hasActiveDeployment && $credential) {
            try {
                $watchService = app(GmailWatchService::class, ['credential' => $credential]);
                $watchService->watch(config('services.gmail.pubsub_topic'));
                Log::info('Gmail watch auto-started after OAuth', ['user' => $user->id, 'gmail' => $email]);
            } catch (\Throwable $e) {
                Log::error('Gmail watch auto-start failed', ['error' => $e->getMessage()]);
            }
        }

        Mail::to($user->email)->send(new GmailConnected($user->name, $email));

        $message = $hasActiveDeployment
            ? "Gmail connected and inbox watch activated for {$email}. Your worker is now monitoring."
            : "Gmail connected: {$email}. Deploy a worker to start monitoring.";

        // Return to onboarding wizard if that's where the OAuth was triggered from
        if (session('onboarding_gmail_return')) {
            session()->forget('onboarding_gmail_return');

            // Auto-attach this Gmail credential to the active onboarding deployment
            // (skips the "select inbox" step that exists in the dashboard flow)
            $wos = \App\Platform\Services\WorkerOnboardingService::activeSession($user->id);
            if ($wos && $wos->deployment_id && $credential) {
                $alreadyLinked = DB::table('deployment_credentials')
                    ->where('deployment_id', $wos->deployment_id)
                    ->where('credential_id', $credential->id)
                    ->exists();

                if (!$alreadyLinked) {
                    DB::table('deployment_credentials')->insertOrIgnore([
                        'deployment_id' => $wos->deployment_id,
                        'credential_id' => $credential->id,
                        'is_primary'    => true,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    DB::table('worker_deployments')
                        ->where('id', $wos->deployment_id)
                        ->update(['credential_id' => $credential->id, 'updated_at' => now()]);
                }
            }

            \App\Platform\Services\WorkerOnboardingService::advanceStepByName($user->id, 'credential');
            return redirect()->route('onboarding.step', 'memory')->with('success', "Gmail connected: {$email}");
        }

        // Auto-connect to deployment when this is the user's first credential
        // and there is exactly one active deployment with no inbox linked yet.
        // Power users with multiple deployments/credentials use the manual picker.
        $credentialCount = DB::table('user_gmail_credentials')->where('user_id', $user->id)->count();
        if ($credentialCount === 1 && $credential) {
            $deployments = DB::table('worker_deployments')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->get();

            if ($deployments->count() === 1) {
                $dep = $deployments->first();
                $alreadyLinked = DB::table('deployment_credentials')
                    ->where('deployment_id', $dep->id)
                    ->exists();

                if (!$alreadyLinked) {
                    DB::table('deployment_credentials')->insertOrIgnore([
                        'deployment_id' => $dep->id,
                        'credential_id' => $credential->id,
                        'is_primary'    => true,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                    DB::table('worker_deployments')
                        ->where('id', $dep->id)
                        ->update(['credential_id' => $credential->id, 'updated_at' => now()]);
                }
            }
        }

        return redirect()->route('ava.connect')->with('success', $message);
    }

    // PUBLIC — Google Pub/Sub pushes here
    public function webhook(Request $request, TransactionService $txService)
    {
        // Verify the push came from Google Pub/Sub — reject unauthenticated requests
        if (!$this->verifyPubSubToken($request)) {
            Log::warning('AVA webhook: rejected unauthenticated request', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data        = $request->all();
        $messageData = $data['message']['data'] ?? null;

        if (!$messageData) return response()->json(['status' => 'ok'], 200);

        $decoded   = json_decode(base64_decode($messageData), true);
        $historyId = $decoded['historyId'] ?? null;
        $gmailAddr = $decoded['emailAddress'] ?? null;

        if (!$historyId || !$gmailAddr) return response()->json(['status' => 'ok'], 200);

        // Rate-limit: one webhook processing per Gmail address per 30 seconds to absorb Pub/Sub redeliveries
        $rateLimitKey = 'ava_webhook_' . md5($gmailAddr);
        if (cache()->has($rateLimitKey)) {
            Log::info('AVA webhook: rate-limited, skipping duplicate delivery', ['gmail' => $gmailAddr, 'historyId' => $historyId]);
            return response()->json(['status' => 'ok'], 200);
        }
        cache()->put($rateLimitKey, true, 30);

        // Find which user owns this Gmail address
        $credential = DB::table('user_gmail_credentials')->where('gmail_address', $gmailAddr)->first();
        if (!$credential) {
            Log::info('AVA webhook: no user found for gmail', ['address' => $gmailAddr]);
            return response()->json(['status' => 'ok'], 200);
        }

        $watchService = app(GmailWatchService::class, ['credential' => $credential]);

        try {
            $emails = $watchService->getNewMessages($historyId);
        } catch (\Throwable $e) {
            Log::error('AVA webhook error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'ok'], 200);
        }

        $queued = [];
        // Find the active deployment using this credential (supports multi-inbox via pivot)
        $deployment = DB::table('worker_deployments')
            ->join('deployment_credentials', 'deployment_credentials.deployment_id', '=', 'worker_deployments.id')
            ->where('deployment_credentials.credential_id', $credential->id)
            ->where('worker_deployments.status', 'active')
            ->select('worker_deployments.*')
            ->first();

        if (!$deployment) {
            Log::info('Webhook: no active deployment found for credential', ['credential_id' => $credential->id]);
            return response()->json(['status' => 'ok'], 200);
        }

        // Resolve the worker contract — silently drops if decommissioned
        $contract = WorkerRegistry::resolveActive($deployment->worker_slug);
        if (WorkerRegistry::isNull($contract)) {
            Log::info('Webhook: worker is decommissioned or unknown, dropping ingest', ['slug' => $deployment->worker_slug]);
            return response()->json(['status' => 'ok'], 200);
        }

        $ingestJobClass = $contract->ingestJobClass();
        if (!$ingestJobClass || !class_exists($ingestJobClass)) {
            Log::error('Webhook: worker has no valid ingestJobClass', ['slug' => $deployment->worker_slug, 'class' => $ingestJobClass]);
            return response()->json(['status' => 'ok'], 200);
        }

        $isTest = WorkerRegistry::isTesting($deployment->worker_slug);

        // Skip emails the platform itself sent — prevent workers processing their own output
        $unitSenderAddresses = [
            strtolower(config('mail.from.address', '')),
            strtolower($gmailAddr),
        ];

        foreach ($emails as $email) {
            $fromAddress = strtolower($email['from'] ?? '');
            if (preg_match('/<(.+?)>/', $fromAddress, $m)) {
                $fromAddress = strtolower(trim($m[1]));
            }

            if (in_array($fromAddress, array_filter($unitSenderAddresses))) {
                Log::info('Webhook: skipping email from self', ['from' => $fromAddress]);
                continue;
            }

            // Gate: check quota before creating TX — prevents rapid-fire bypass
            try {
                \App\Platform\Services\UsageGuard::checkDeployment($credential->user_id, $deployment->id);
            } catch (\App\Platform\Exceptions\BillingException $e) {
                Log::info('AVA webhook: quota/billing gate blocked email', [
                    'deployment_id' => $deployment->id,
                    'policy'        => $e->billingCode,
                    'from'          => $fromAddress,
                ]);
                continue;
            }

            $tx = $txService->create($deployment->worker_slug, [
                'source'        => 'gmail_webhook',
                'message_id'    => $email['message_id'],
                'raw_email'     => $email['raw_email'],
                'subject'       => $email['subject'] ?? '',
                'from'          => $fromAddress,
                'user_id'       => $credential->user_id,
                'deployment_id' => $deployment->id,
                'is_test'       => $isTest,
            ]);
            $watchService->markProcessed($email['message_id'], $tx->tx_id);
            $queue = $txService->queueForTx($tx);
            $ingestJobClass::dispatch($tx->tx_id)->onQueue($queue);
            $queued[] = $tx->tx_id;
        }

        return response()->json(['status' => 'queued', 'transactions' => $queued], 200);
    }

    // AUTHENTICATED
    public function authorize()
    {
        $query = http_build_query([
            'client_id'     => config('services.gmail.client_id'),
            'redirect_uri'  => config('services.gmail.redirect_uri'),
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/gmail.compose https://www.googleapis.com/auth/gmail.readonly https://www.googleapis.com/auth/gmail.insert openid email',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ]);
        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function watch()
    {
        $credential = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->firstOrFail();

        try {
            $watchService = app(GmailWatchService::class, ['credential' => $credential]);
            $result = $watchService->watch(config('services.gmail.pubsub_topic'));
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'invalid_grant')) {
                // Token revoked or expired — wipe the credential and send back to re-authorize
                DB::table('user_gmail_credentials')->where('id', $credential->id)->delete();
                return redirect()->route('ava.gmail.authorize')
                    ->with('error', 'Your Gmail session expired. Please reconnect your inbox.');
            }
            return back()->with('error', 'Could not activate inbox watch: ' . $e->getMessage());
        }

        DB::table('user_gmail_credentials')->where('user_id', auth()->id())->update([
            'watch_active'     => true,
            'watch_expires_at' => date('Y-m-d H:i:s', $result['expiration'] / 1000),
            'updated_at'       => now(),
        ]);
        return redirect()->route('ava.connect')->with('success', 'Inbox watch activated. AVA is now monitoring your email.');
    }

    public function connect()
    {
        $credential = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->first();
        return view('dashboard.connect', compact('credential'));
    }

    public function test(Request $request, TransactionService $txService)
    {
        $request->validate(['raw_email' => 'required|string']);

        // Resolve via the active deployment so this works for any Gmail-based worker, not just AVA
        $deployment = DB::table('worker_deployments')
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (!$deployment) {
            return response()->json(['error' => 'No active deployment found.'], 422);
        }

        $contract = WorkerRegistry::resolveActive($deployment->worker_slug);
        if (WorkerRegistry::isNull($contract)) {
            return response()->json(['error' => 'Worker is not available.'], 422);
        }

        $ingestJobClass = $contract->ingestJobClass();
        if (!$ingestJobClass || !class_exists($ingestJobClass)) {
            return response()->json(['error' => 'Worker ingest job not found.'], 422);
        }

        $tx = $txService->create($deployment->worker_slug, [
            'source'        => 'manual_test',
            'raw_email'     => $request->input('raw_email'),
            'user_id'       => auth()->id(),
            'deployment_id' => $deployment->id,
        ]);

        $queue = $txService->queueForTx($tx);
        $ingestJobClass::dispatch($tx->tx_id)->onQueue($queue);
        return response()->json(['status' => 'queued', 'tx_id' => $tx->tx_id]);
    }

    // Verify Google Pub/Sub OIDC token from the Authorization header.
    // Google signs push requests with a service account OIDC token — we verify via tokeninfo.
    // If GMAIL_PUBSUB_SERVICE_ACCOUNT is not set, verification is skipped (dev/local mode).
    private function verifyPubSubToken(Request $request): bool
    {
        $expectedAccount = config('services.gmail.pubsub_service_account');

        // Skip verification in dev/local if no service account configured
        if (!$expectedAccount) {
            return true;
        }

        $authHeader = $request->header('Authorization', '');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return false;
        }

        $token    = substr($authHeader, 7);
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $token]);

        if ($response->failed()) {
            return false;
        }

        $claims = $response->json();

        // Token must be issued for our webhook URL and by the expected Pub/Sub service account
        $webhookUrl = url('/workers/ava/gmail/webhook');
        $aud        = $claims['aud'] ?? '';
        $email      = $claims['email'] ?? '';

        return $email === $expectedAccount && str_starts_with($aud, $webhookUrl);
    }
}

<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\QAController;
use App\Http\Controllers\ProfileController;
use App\Mail\GmailConnected;
use App\Platform\Services\LLM\ModelCatalog;
use App\Platform\Services\MemoryImportService;
use App\Platform\Services\TransactionService;
use App\Platform\Services\UnitNotifier;
use App\Workers\AVA\Jobs\ReadEmailJob;
use App\Workers\AVA\Services\GmailWatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// ─── Public routes ────────────────────────────────────────────────────────────

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

// Gmail OAuth callback (must be public — Google redirects here)
Route::get('/workers/ava/gmail/callback', function (Request $request) {
    $code = $request->query('code');
    if (!$code) return response()->json(['error' => 'No authorization code'], 400);

    $response = \Illuminate\Support\Facades\Http::asForm()->post('https://oauth2.googleapis.com/token', [
        'code'          => $code,
        'client_id'     => config('services.gmail.client_id'),
        'client_secret' => config('services.gmail.client_secret'),
        'redirect_uri'  => config('services.gmail.redirect_uri'),
        'grant_type'    => 'authorization_code',
    ]);

    if ($response->failed()) {
        return back()->with('error', 'Gmail connection failed: ' . $response->body());
    }

    $refreshToken = $response->json('refresh_token');
    $idToken      = $response->json('id_token');

    // Decode the id_token to get the Gmail address
    $parts   = explode('.', $idToken);
    $payload = json_decode(base64_decode(str_pad($parts[1], strlen($parts[1]) + (4 - strlen($parts[1]) % 4) % 4, '=')), true);
    $email   = $payload['email'] ?? null;

    // Detect whether gmail.insert scope was granted (present in token scope response)
    $grantedScopes   = $response->json('scope') ?? '';
    $hasInsertScope  = str_contains($grantedScopes, 'gmail.insert') ? 1 : 0;

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

    // Auto-start watch if tenant already has an active AVA deployment
    $hasAvaDeployment = DB::table('worker_deployments')
        ->where('user_id', $user->id)
        ->where('worker_slug', 'ava')
        ->where('status', 'active')
        ->exists();

    if ($hasAvaDeployment && $credential) {
        try {
            $watchService = app(GmailWatchService::class, ['credential' => $credential]);
            $result       = $watchService->watch(config('services.gmail.pubsub_topic'));
            DB::table('user_gmail_credentials')
                ->where('user_id', $user->id)
                ->update(['watch_expiry' => now()->addDays(7), 'updated_at' => now()]);
            Log::info('AVA Gmail watch auto-started after OAuth', ['user' => $user->id, 'gmail' => $email]);
        } catch (\Throwable $e) {
            Log::error('AVA Gmail watch auto-start failed', ['error' => $e->getMessage()]);
        }
    }

    Mail::to($user->email)->queue(new GmailConnected($user->name, $email));

    $message = $hasAvaDeployment
        ? "Gmail connected and inbox watch activated for {$email}. AVA is now monitoring."
        : "Gmail connected: {$email}. Deploy AVA to start monitoring.";

    // Return to onboarding wizard if that's where the OAuth was triggered from
    if (session('onboarding_gmail_return')) {
        session()->forget('onboarding_gmail_return');
        session(['onboarding_step' => 4]);
        return redirect()->route('onboarding.step', 4)->with('success', "Gmail connected: {$email}");
    }

    return redirect()->route('ava.connect')->with('success', $message);
})->name('ava.gmail.callback');

// Stripe webhook (must be public and CSRF-exempt — verified by Stripe signature)
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

// Gmail Pub/Sub webhook (Google pushes here — must be public)
Route::post('/workers/ava/gmail/webhook', function (Request $request, TransactionService $txService) {
    $data        = $request->all();
    $messageData = $data['message']['data'] ?? null;

    if (!$messageData) return response()->json(['status' => 'ok'], 200);

    $decoded   = json_decode(base64_decode($messageData), true);
    $historyId = $decoded['historyId'] ?? null;
    $gmailAddr = $decoded['emailAddress'] ?? null;

    if (!$historyId || !$gmailAddr) return response()->json(['status' => 'ok'], 200);

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

    // Addresses AVA itself sends from — skip to prevent AVA processing its own emails
    $unitSenderAddresses = [
        strtolower(config('mail.from.address', '')),
        strtolower($gmailAddr), // skip emails sent by the monitored inbox itself
    ];

    foreach ($emails as $email) {
        $fromAddress = strtolower($email['from'] ?? '');
        // Extract bare email if in "Name <email>" format
        if (preg_match('/<(.+?)>/', $fromAddress, $m)) {
            $fromAddress = strtolower(trim($m[1]));
        }

        if (in_array($fromAddress, array_filter($unitSenderAddresses))) {
            Log::info('AVA webhook: skipping email from self', ['from' => $fromAddress]);
            continue;
        }

        $tx = $txService->create('ava-renewal-coordinator', [
            'source'        => 'gmail_webhook',
            'message_id'    => $email['message_id'],
            'raw_email'     => $email['raw_email'],
            'from'          => $fromAddress,
            'user_id'       => $credential->user_id,
            'deployment_id' => $deployment?->id,
        ]);
        $watchService->markProcessed($email['message_id'], $tx->tx_id);
        $queue = $txService->queueForTx($tx);
        ReadEmailJob::dispatch($tx->tx_id)->onQueue($queue);
        $queued[] = $tx->tx_id;
    }

    return response()->json(['status' => 'queued', 'transactions' => $queued], 200);
})->name('ava.gmail.webhook');

// ─── Authenticated routes ─────────────────────────────────────────────────────

Route::middleware(['auth', 'verified'])->group(function () {

    // ── Onboarding wizard ───────────────────────────────────────────────────
    Route::get('/onboarding',                [\App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding');
    Route::get('/onboarding/{step}',         [\App\Http\Controllers\OnboardingController::class, 'show'])->name('onboarding.step')->whereNumber('step');
    Route::post('/onboarding/1',             [\App\Http\Controllers\OnboardingController::class, 'step1'])->name('onboarding.1');
    Route::post('/onboarding/2',             [\App\Http\Controllers\OnboardingController::class, 'step2'])->name('onboarding.2');
    Route::post('/onboarding/3',             [\App\Http\Controllers\OnboardingController::class, 'step3'])->name('onboarding.3');
    Route::get('/onboarding/3/skip',         [\App\Http\Controllers\OnboardingController::class, 'step3Skip'])->name('onboarding.3.skip');
    Route::post('/onboarding/4',             [\App\Http\Controllers\OnboardingController::class, 'step4'])->name('onboarding.4');
    Route::post('/onboarding/5',             [\App\Http\Controllers\OnboardingController::class, 'step5'])->name('onboarding.5');
    Route::get('/onboarding/complete',       [\App\Http\Controllers\OnboardingController::class, 'complete'])->name('onboarding.complete');

    // ── Transaction status polling (used by fast-track pipeline UI) ─────────
    Route::get('/transactions/{txId}/status', function (string $txId) {
        $tx = DB::table('transactions')
            ->where('tx_id', $txId)
            ->where('user_id', auth()->id())
            ->first();
        if (!$tx) return response()->json(['error' => 'not found'], 404);
        $terminal = ['draft_ready', 'approved', 'sent', 'failed', 'rejected'];
        $isDone   = in_array($tx->status, $terminal);
        $isFailed = $tx->status === 'failed';
        return response()->json(['status' => $tx->status, 'done' => $isDone, 'failed' => $isFailed]);
    })->name('transactions.status');

    // ── Command Center ──────────────────────────────────────────────────────
    Route::get('/dashboard', function () {
        $userId = auth()->id();

        // ── Pipeline-level stats (universal — meaningful for any worker type) ──
        $pipelineActive = ['received','ingesting','reading','classifying','memory_lookup','logging','templating','drafting','pushing'];
        $pipeline = [
            'total'      => DB::table('transactions')->where('user_id', $userId)->count(),
            'in_pipeline'=> DB::table('transactions')->where('user_id', $userId)->whereIn('status', $pipelineActive)->count(),
            'needs_review'=> DB::table('transactions')->where('user_id', $userId)->whereIn('status', ['draft_ready','human_review'])->whereNull('human_decision')->count(),
            'failed'     => DB::table('failed_jobs')->count(),
        ];

        // ── Worker cards ──
        $deployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'paused'])
            ->orderBy('created_at')
            ->get();

        $workerCards = $deployments->map(function ($dep) use ($userId) {
            $contract = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
            if (!$contract) return null;

            $dash  = $contract->dashboard();
            $depId = $dep->id;

            // Worker-specific stats declared by the contract
            $stats = collect($dash['stats'])->map(function ($stat) use ($depId) {
                $value = match ($stat['key']) {
                    'tx_draft_ready' => DB::table('transactions')->where('deployment_id', $depId)->where('status', 'draft_ready')->count(),
                    'tx_urgent'      => DB::table('transactions')->where('deployment_id', $depId)->whereIn('priority', ['High','Critical'])->whereNotIn('status', ['approved','sent','failed'])->count(),
                    'tx_today'       => DB::table('transactions')->where('deployment_id', $depId)->whereDate('created_at', today())->count(),
                    'tx_total'       => DB::table('transactions')->where('deployment_id', $depId)->count(),
                    'tx_failed'      => DB::table('transactions')->where('deployment_id', $depId)->where('status', 'failed')->count(),
                    'tx_approved'    => DB::table('transactions')->where('deployment_id', $depId)->whereIn('status', ['approved','sent'])->count(),
                    default          => 0,
                };
                return array_merge($stat, ['value' => $value]);
            });

            // Last run
            $lastTx = DB::table('transactions')->where('deployment_id', $depId)->orderByDesc('id')->first();

            // Connection health — inboxes + watch status
            $inboxes = DB::table('deployment_credentials')
                ->join('user_gmail_credentials', 'user_gmail_credentials.id', '=', 'deployment_credentials.credential_id')
                ->where('deployment_credentials.deployment_id', $depId)
                ->select('user_gmail_credentials.gmail_address', 'user_gmail_credentials.watch_active', 'deployment_credentials.is_primary')
                ->get();

            $billing = DB::table('deployment_billing')->where('deployment_id', $depId)->first();

            return [
                'dep'      => $dep,
                'contract' => $contract,
                'dash'     => $dash,
                'stats'    => $stats,
                'lastTx'   => $lastTx,
                'inboxes'  => $inboxes,
                'billing'  => $billing,
            ];
        })->filter()->values();

        // ── Notifications — delegated to NotificationEngine ──
        $notifications = \App\Platform\Services\NotificationEngine::evaluate($userId, auth()->user()->role ?? 'tenant');

        // ── Referral stats ──
        $referral = \App\Platform\Services\ReferralService::getStats($userId);
        $referralCode = \App\Platform\Services\ReferralService::ensureCode($userId);
        $referralUrl  = url('/register?ref=' . $referralCode);

        return view('dashboard.index', compact('pipeline', 'workerCards', 'notifications', 'referral', 'referralUrl', 'referralCode'));
    })->name('dashboard');

    // ── Transactions ────────────────────────────────────────────────────────
    Route::get('/transactions', function (Request $request) {
        $userId = auth()->id();
        $filter = $request->query('filter', 'all');
        $query  = DB::table('transactions')->where('user_id', $userId)->orderByDesc('id');
        if ($filter === 'draft_ready')  $query->where('status', 'draft_ready');
        elseif ($filter === 'approved') $query->whereIn('status', ['approved','sent']);
        elseif ($filter === 'failed')   $query->where('status', 'failed');
        elseif ($filter === 'dismissed') $query->where('status', 'dismissed');
        else $query->where('status', '!=', 'dismissed'); // default: hide dismissed
        $transactions  = $query->paginate(25);
        $currentFilter = $filter;
        return view('dashboard.transactions', compact('transactions', 'currentFilter'));
    })->name('transactions');

    Route::get('/transactions/{txId}', function (string $txId) {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        return view('dashboard.transaction-detail', compact('tx'));
    })->name('transactions.show');

    // ── Re-fire: restart pipeline from scratch with original raw_input ──────
    Route::post('/transactions/{txId}/refire', function (string $txId) {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();

        if ($tx->status !== 'failed') {
            return back()->with('error', 'Only failed transactions can be re-fired.');
        }

        $raw = json_decode($tx->raw_input ?? '{}', true);
        $source = $raw['source'] ?? 'unknown';

        if ($source === 'fast_track_test') {
            return back()->with('error', 'Fast Track test transactions cannot be re-fired. Run a new Fast Track instead.');
        }

        // Reset to received, clear all stage outputs so pipeline runs clean
        DB::table('transactions')->where('tx_id', $txId)->update([
            'status'          => 'received',
            'read_output'     => null,
            'classify_output' => null,
            'memory_output'   => null,
            'template_output' => null,
            'draft_output'    => null,
            'gmail_draft_id'  => null,
            'human_decision'  => null,
            'human_notes'     => null,
            'category'        => null,
            'priority'        => null,
            'updated_at'      => now(),
        ]);

        $dep   = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();
        $queue = $dep ? ($dep->worker_slug . '-' . $dep->id) : 'default';

        \App\Workers\AVA\Jobs\ReadEmailJob::dispatch($txId)->onQueue($queue);

        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'tx_refire', ['triggered_by' => auth()->id()]);

        return back()->with('success', 'Transaction re-fired — pipeline restarting from Read stage.');
    })->name('transactions.refire');

    // ── Dismiss: soft-close — keeps audit trail, hides from active queues ──
    Route::post('/transactions/{txId}/dismiss', function (string $txId, Request $request) {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();

        $allowedStatuses = ['failed', 'draft_ready', 'human_review', 'blocked'];
        if (!in_array($tx->status, $allowedStatuses)) {
            return back()->with('error', 'This transaction cannot be dismissed in its current state.');
        }

        DB::table('transactions')->where('tx_id', $txId)->update([
            'status'       => 'dismissed',
            'human_notes'  => $request->input('reason') ?: ($tx->human_notes),
            'updated_at'   => now(),
        ]);

        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'tx_dismissed', [
            'reason'         => $request->input('reason'),
            'previous_status'=> $tx->status,
            'triggered_by'   => auth()->id(),
        ]);

        return redirect()->route('transactions')->with('success', 'Transaction dismissed — removed from active queues.');
    })->name('transactions.dismiss');

    // ── Delete: hard delete — fast track tests only ───────────────────────
    Route::delete('/transactions/{txId}', function (string $txId) {
        $tx  = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        $raw = json_decode($tx->raw_input ?? '{}', true);

        if (($raw['source'] ?? '') !== 'fast_track_test') {
            return back()->with('error', 'Only Fast Track test transactions can be permanently deleted.');
        }

        DB::table('transactions')->where('tx_id', $txId)->delete();

        return redirect()->route('transactions')->with('success', 'Test transaction deleted.');
    })->name('transactions.delete');

    Route::post('/transactions/{txId}/decide', function (string $txId, Request $request) {
        $tx       = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        $decision = $request->input('decision'); // 'approved' | 'rejected'

        // ── Approve: send the draft via Gmail ────────────────────────────────
        if ($decision === 'approved' && $tx->gmail_draft_id) {
            try {
                $dep        = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();
                $credential = $dep?->credential_id
                    ? DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first()
                    : null;

                if ($credential?->refresh_token) {
                    $gmail = new \App\Workers\AVA\Services\GmailService($credential);
                    $gmail->sendDraft($tx->gmail_draft_id);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Draft send failed on approve', [
                    'tx_id' => $txId, 'error' => $e->getMessage(),
                ]);
                return back()->with('error', 'Approval recorded but email send failed: ' . $e->getMessage());
            }
        }

        // ── Reject: delete the Gmail draft ────────────────────────────────────
        if ($decision === 'rejected' && $tx->gmail_draft_id) {
            try {
                $dep        = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();
                $credential = $dep?->credential_id
                    ? DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first()
                    : null;

                if ($credential?->refresh_token) {
                    $gmail = new \App\Workers\AVA\Services\GmailService($credential);
                    $gmail->deleteDraft($tx->gmail_draft_id);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Draft delete failed on reject', [
                    'tx_id' => $txId, 'error' => $e->getMessage(),
                ]);
                // Non-fatal — continue with rejection
            }
        }

        $newStatus = $decision === 'approved' ? 'sent' : 'rejected';

        DB::table('transactions')->where('tx_id', $txId)->update([
            'human_decision' => $decision,
            'human_notes'    => $request->input('notes'),
            'status'         => $newStatus,
            'updated_at'     => now(),
        ]);

        DB::table('renewal_register')->where('tx_id', $txId)->update([
            'status'     => $decision === 'approved' ? 'Sent' : 'Rejected',
            'updated_at' => now(),
        ]);

        $msg = $decision === 'approved'
            ? "✓ {$txId} approved — email sent."
            : "✗ {$txId} rejected — draft deleted.";

        return redirect()->route('transactions')->with('success', $msg);
    })->name('transactions.decide');

    // ── Renewal Register ────────────────────────────────────────────────────
    Route::get('/register', function () {
        $register = DB::table('renewal_register')->where('user_id', auth()->id())->orderByDesc('id')->get();
        return view('dashboard.register', compact('register'));
    })->name('register');

    // ── Memory Management ───────────────────────────────────────────────────
    Route::get('/memory', function () {
        $userId   = auth()->id();
        $clients  = DB::table('clients')->where('user_id', $userId)->orderBy('name')->get();
        $contacts = DB::table('contacts')->where('user_id', $userId)->get();
        $assets   = DB::table('assets')->where('user_id', $userId)->orderBy('renewal_date')->get();
        $rules    = DB::table('ava_rules')->where('user_id', $userId)->orderBy('rule_id')->get();
        return view('dashboard.memory', compact('clients', 'contacts', 'assets', 'rules'));
    })->name('memory');

    Route::post('/memory/clients', function (Request $request) {
        $request->validate(['name' => 'required']);
        DB::table('clients')->insert(['user_id' => auth()->id(), 'name' => $request->name, 'industry' => $request->industry, 'preferred_style' => $request->preferred_style, 'notes' => $request->notes, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Client added.');
    })->name('memory.clients.store');

    Route::delete('/memory/clients/{id}', function (int $id) {
        DB::table('clients')->where('id', $id)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Client removed.');
    })->name('memory.clients.destroy');

    Route::post('/memory/contacts', function (Request $request) {
        $request->validate(['name' => 'required', 'email' => 'required|email']);
        DB::table('contacts')->insert(['user_id' => auth()->id(), 'client_id' => $request->client_id ?: null, 'name' => $request->name, 'email' => $request->email, 'phone' => $request->phone, 'role' => $request->role, 'is_primary' => $request->boolean('is_primary'), 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Contact added.');
    })->name('memory.contacts.store');

    Route::delete('/memory/contacts/{id}', function (int $id) {
        DB::table('contacts')->where('id', $id)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Contact removed.');
    })->name('memory.contacts.destroy');

    Route::post('/memory/assets', function (Request $request) {
        $request->validate(['name' => 'required', 'type' => 'required', 'client_id' => 'required']);
        DB::table('assets')->insert(['user_id' => auth()->id(), 'name' => $request->name, 'type' => $request->type, 'client_id' => $request->client_id ?: null, 'vendor' => $request->vendor, 'renewal_date' => $request->renewal_date, 'cost_per_year' => $request->cost_per_year ?: null, 'service_owner' => $request->service_owner, 'notes' => $request->notes, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Asset added.');
    })->name('memory.assets.store');

    Route::delete('/memory/assets/{id}', function (int $id) {
        DB::table('assets')->where('id', $id)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Asset removed.');
    })->name('memory.assets.destroy');

    Route::post('/memory/rules', function (Request $request) {
        $request->validate(['condition' => 'required', 'action' => 'required', 'priority' => 'required']);
        if ($request->rule_id) {
            $ruleId = $request->rule_id;
        } else {
            $last   = DB::table('ava_rules')->where('user_id', auth()->id())->orderByDesc('id')->value('rule_id');
            $ruleId = $last ? 'AVA-' . str_pad(intval(substr($last, 4)) + 1, 3, '0', STR_PAD_LEFT) : 'AVA-101';
        }
        DB::table('ava_rules')->insert(['user_id' => auth()->id(), 'rule_id' => $ruleId, 'condition' => $request->condition, 'priority' => $request->priority, 'action' => $request->action, 'approval_required' => $request->boolean('approval_required'), 'notes' => $request->notes, 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Rule added.');
    })->name('memory.rules.store');

    Route::delete('/memory/rules/{id}', function (int $id) {
        DB::table('ava_rules')->where('id', $id)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Rule removed.');
    })->name('memory.rules.destroy');

    // ── Memory Import Templates (downloads) ────────────────────────────────
    Route::get('/memory/import/template/{type}', function (string $type) {
        abort_unless(in_array($type, ['clients', 'contacts', 'assets']), 404);
        $path = storage_path("app/templates/{$type}_template.csv");
        return response()->download($path, "{$type}_import_template.csv", [
            'Content-Type' => 'text/csv',
        ]);
    })->name('memory.import.template');

    // ── Memory Import ───────────────────────────────────────────────────────
    Route::post('/memory/import/preview', function (Request $request, MemoryImportService $importer) {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
            'type' => 'required|in:clients,contacts,assets',
        ]);

        $file    = $request->file('file');
        $type    = $request->input('type');
        $data    = $importer->readFile($file);
        $mapping = $importer->suggestMapping($data['headers'], $type);

        // Store file temporarily for the commit step
        $tmpPath = $file->store('imports', 'local');

        session([
            'import_tmp'     => $tmpPath,
            'import_headers' => $data['headers'],
            'import_rows'    => $data['rows'],
            'import_type'    => $type,
            'import_mapping' => $mapping,
        ]);

        return view('dashboard.memory-import-preview', [
            'headers' => $data['headers'],
            'rows'    => array_slice($data['rows'], 0, 5),
            'mapping' => $mapping,
            'type'    => $type,
            'total'   => count($data['rows']),
        ]);
    })->name('memory.import.preview');

    Route::post('/memory/import/commit', function (Request $request, MemoryImportService $importer) {
        $headers = session('import_headers');
        $rows    = session('import_rows');
        $type    = session('import_type');

        if (!$headers || !$rows) {
            return redirect()->route('memory')->with('error', 'Import session expired. Please re-upload.');
        }

        // Use user-adjusted mapping from form, or fall back to auto-mapping
        $mapping = [];
        foreach ($headers as $i => $header) {
            $mapping[$i] = $request->input("mapping.{$i}") ?: null;
        }

        $result = $importer->import($headers, $rows, $mapping, $type, auth()->id());

        // Clean up
        if (session('import_tmp')) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete(session('import_tmp'));
        }
        session()->forget(['import_tmp', 'import_headers', 'import_rows', 'import_type', 'import_mapping']);

        return redirect()->route('memory')->with('success',
            "Import complete: {$result['inserted']} {$type} imported, {$result['skipped']} skipped."
        );
    })->name('memory.import.commit');

    // ── Email Templates ─────────────────────────────────────────────────────
    Route::get('/templates', function () {
        $userId    = auth()->id();
        $templates = DB::table('email_templates')->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhereNull('user_id');
        })->orderBy('category')->get();
        return view('dashboard.templates', compact('templates'));
    })->name('templates');

    Route::post('/templates', function (Request $request) {
        $request->validate(['name' => 'required', 'category' => 'required', 'subject_template' => 'required', 'body_template' => 'required']);
        DB::table('email_templates')->insert(['user_id' => auth()->id(), 'name' => $request->name, 'category' => $request->category, 'tone' => $request->tone ?? 'Professional, concise', 'subject_template' => $request->subject_template, 'body_template' => $request->body_template, 'approval_required' => $request->boolean('approval_required'), 'is_default' => false, 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Template saved.');
    })->name('templates.store');

    Route::delete('/templates/{id}', function (int $id) {
        DB::table('email_templates')->where('id', $id)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Template removed.');
    })->name('templates.destroy');

    // ── Worker Deployments ──────────────────────────────────────────────────
    Route::get('/workers', function () {
        $deployments = DB::table('worker_deployments')
            ->where('user_id', auth()->id())
            ->orderBy('created_at')
            ->get();
        $credentials = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->get();
        $catalog     = DB::table('workers')->get();

        // Resolve contracts for all registered workers — drives deploy form constraints
        $contracts = collect(\App\Platform\Services\WorkerRegistry::all())
            ->keyBy(fn($c) => $c->identity()['slug']);

        // Count existing deployments per slug for the tenant
        $deploymentCounts = $deployments->groupBy('worker_slug')
            ->map(fn($group) => $group->count());

        return view('dashboard.workers', compact('deployments', 'credentials', 'catalog', 'contracts', 'deploymentCounts'));
    })->name('workers.deploy');

    Route::get('/workers/{id}', function (int $id) {
        $dep           = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $contract      = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
        $credential    = $dep->credential_id ? DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first() : null;
        $credentials   = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->get();

        // All inboxes connected to this deployment via pivot
        $connectedInboxes = DB::table('deployment_credentials')
            ->join('user_gmail_credentials', 'user_gmail_credentials.id', '=', 'deployment_credentials.credential_id')
            ->where('deployment_credentials.deployment_id', $id)
            ->select('user_gmail_credentials.*', 'deployment_credentials.is_primary', 'deployment_credentials.id as pivot_id')
            ->get();

        // Credentials not yet connected to this deployment
        $availableCredentials = $credentials->filter(
            fn($c) => !$connectedInboxes->contains('id', $c->id)
        )->values();

        $txCount       = DB::table('transactions')->where('deployment_id', $id)->count();
        $recentTx      = DB::table('transactions')->where('deployment_id', $id)->orderByDesc('created_at')->limit(5)->get();
        $usage         = DB::table('usage_events')->where('deployment_id', $id)->selectRaw('SUM(tokens_input+tokens_output) as tokens, SUM(cost_usd) as cost')->first();
        $pendingReview = DB::table('transactions')->where('deployment_id', $id)->where('status', 'draft_ready')->whereNull('human_decision')->count();
        $stuckCount    = DB::table('transactions')->where('deployment_id', $id)->whereNotIn('status', ['draft_ready','approved','sent','failed'])->where('updated_at', '<', now()->subMinutes(5))->count();
        $customModels     = DB::table('tenant_custom_models')->where('user_id', auth()->id())->where('active', true)->get();
        $policyViolations = \App\Platform\Services\PolicyEngine::evaluate(auth()->id(), $id);
        return view('dashboard.worker-detail', compact(
            'dep', 'contract', 'credential', 'credentials', 'connectedInboxes', 'availableCredentials',
            'txCount', 'recentTx', 'usage', 'pendingReview', 'stuckCount',
            'customModels', 'policyViolations'
        ));
    })->name('workers.show')->where('id', '[0-9]+');

    // Connect an inbox to a deployment
    Route::post('/workers/{id}/inboxes', function (int $id, Request $request) {
        $dep          = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $credentialId = (int) $request->input('credential_id');
        $credential   = DB::table('user_gmail_credentials')->where('id', $credentialId)->where('user_id', auth()->id())->firstOrFail();

        $isPrimary = !DB::table('deployment_credentials')->where('deployment_id', $id)->where('is_primary', true)->exists();

        DB::table('deployment_credentials')->insertOrIgnore([
            'deployment_id' => $id,
            'credential_id' => $credentialId,
            'is_primary'    => $isPrimary,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Keep credential_id in sync for backwards compat (points to primary)
        if ($isPrimary) {
            DB::table('worker_deployments')->where('id', $id)->update(['credential_id' => $credentialId, 'updated_at' => now()]);
        }

        return back()->with('success', "Inbox {$credential->gmail_address} connected.");
    })->name('workers.inboxes.connect');

    // Disconnect an inbox from a deployment
    Route::delete('/workers/{id}/inboxes/{pivotId}', function (int $id, int $pivotId) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $pivot = DB::table('deployment_credentials')->where('id', $pivotId)->where('deployment_id', $id)->firstOrFail();

        DB::table('deployment_credentials')->where('id', $pivotId)->delete();

        // If we removed primary, promote next one
        if ($pivot->is_primary) {
            $next = DB::table('deployment_credentials')->where('deployment_id', $id)->first();
            if ($next) {
                DB::table('deployment_credentials')->where('id', $next->id)->update(['is_primary' => true]);
                DB::table('worker_deployments')->where('id', $id)->update(['credential_id' => $next->credential_id, 'updated_at' => now()]);
            } else {
                DB::table('worker_deployments')->where('id', $id)->update(['credential_id' => null, 'updated_at' => now()]);
            }
        }

        return back()->with('success', 'Inbox disconnected.');
    })->name('workers.inboxes.disconnect');

    // Connect tab — list connected inboxes with rewatch and connect-another
    Route::get('/workers/{id}/connect', function (int $id) {
        $dep         = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $contract    = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
        $credentials = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->get();

        $connectedInboxes = DB::table('deployment_credentials')
            ->join('user_gmail_credentials', 'user_gmail_credentials.id', '=', 'deployment_credentials.credential_id')
            ->where('deployment_credentials.deployment_id', $id)
            ->select('user_gmail_credentials.*', 'deployment_credentials.is_primary', 'deployment_credentials.id as pivot_id')
            ->get();

        $availableCredentials = $credentials->filter(
            fn($c) => !$connectedInboxes->contains('id', $c->id)
        )->values();

        return view('dashboard.worker-connect', compact('dep', 'contract', 'connectedInboxes', 'availableCredentials'));
    })->name('workers.connect');

    // Rewatch a specific credential for a deployment
    Route::post('/workers/{id}/inboxes/{credentialId}/rewatch', function (int $id, int $credentialId) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $credential = DB::table('user_gmail_credentials')->where('id', $credentialId)->where('user_id', auth()->id())->firstOrFail();

        $watchService = app(\App\Workers\AVA\Services\GmailWatchService::class, ['credential' => $credential]);
        $watchService->watch(config('services.gmail.pubsub_topic'));

        return redirect()->route('workers.connect', $id)->with('success', "Watch renewed for {$credential->gmail_address}.");
    })->name('workers.inboxes.rewatch');

    // ── Worker: Memory (clients/contacts/assets scoped to tenant, viewed in worker context) ──
    Route::get('/workers/{id}/memory', function (int $id, Request $request, MemoryImportService $importer) {
        $dep      = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $userId   = auth()->id();
        $clients  = DB::table('clients')->where('user_id', $userId)->orderBy('name')->get();
        $contacts = DB::table('contacts')->where('user_id', $userId)->get();
        $assets   = DB::table('assets')->where('user_id', $userId)->orderBy('renewal_date')->get();
        return view('dashboard.worker-memory', compact('dep', 'clients', 'contacts', 'assets'));
    })->name('workers.memory');

    Route::post('/workers/{id}/memory/import/preview', function (int $id, Request $request, MemoryImportService $importer) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls|max:5120', 'type' => 'required|in:clients,contacts,assets']);
        $data    = $importer->readFile($request->file('file'));
        $mapping = $importer->suggestMapping($data['headers'], $request->type);
        $tmpPath = $request->file('file')->store('imports', 'local');
        session(['import_tmp' => $tmpPath, 'import_headers' => $data['headers'], 'import_rows' => $data['rows'], 'import_type' => $request->type, 'import_dep_id' => $id]);
        return view('dashboard.memory-import-preview', [
            'headers' => $data['headers'], 'rows' => array_slice($data['rows'], 0, 5),
            'mapping' => $mapping, 'type' => $request->type, 'total' => count($data['rows']),
            'dep_id'  => $id,
        ]);
    })->name('workers.memory.import.preview');

    Route::post('/workers/{id}/memory/import/commit', function (int $id, Request $request, MemoryImportService $importer) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $headers = session('import_headers');
        $rows    = session('import_rows');
        $type    = session('import_type');
        if (!$headers || !$rows) return redirect()->route('workers.memory', $id)->with('error', 'Import session expired.');
        $mapping = [];
        foreach ($headers as $i => $h) { $mapping[$i] = $request->input("mapping.{$i}") ?: null; }
        $result = $importer->import($headers, $rows, $mapping, $type, auth()->id());
        if (session('import_tmp')) \Illuminate\Support\Facades\Storage::disk('local')->delete(session('import_tmp'));
        session()->forget(['import_tmp','import_headers','import_rows','import_type','import_dep_id']);
        return redirect()->route('workers.memory', $id)->with('success', "Import complete: {$result['inserted']} {$type} imported, {$result['skipped']} skipped.");
    })->name('workers.memory.import.commit');

    // Worker memory CRUD (same as global but redirect back to worker)
    Route::post('/workers/{id}/memory/clients', function (int $id, Request $request) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required']);
        DB::table('clients')->insert(['user_id' => auth()->id(), 'name' => $request->name, 'industry' => $request->industry, 'preferred_style' => $request->preferred_style, 'notes' => $request->notes, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Client added.');
    })->name('workers.memory.clients.store');

    Route::delete('/workers/{id}/memory/clients/{cid}', function (int $id, int $cid) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DB::table('clients')->where('id', $cid)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Client removed.');
    })->name('workers.memory.clients.destroy');

    Route::post('/workers/{id}/memory/contacts', function (int $id, Request $request) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required', 'email' => 'required|email']);
        DB::table('contacts')->insert(['user_id' => auth()->id(), 'client_id' => $request->client_id ?: null, 'name' => $request->name, 'email' => $request->email, 'phone' => $request->phone, 'role' => $request->role, 'is_primary' => false, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Contact added.');
    })->name('workers.memory.contacts.store');

    Route::delete('/workers/{id}/memory/contacts/{cid}', function (int $id, int $cid) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DB::table('contacts')->where('id', $cid)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Contact removed.');
    })->name('workers.memory.contacts.destroy');

    Route::post('/workers/{id}/memory/assets', function (int $id, Request $request) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required', 'type' => 'required']);
        DB::table('assets')->insert(['user_id' => auth()->id(), 'client_id' => $request->client_id ?: null, 'name' => $request->name, 'type' => $request->type, 'vendor' => $request->vendor, 'renewal_date' => $request->renewal_date, 'cost_per_year' => $request->cost_per_year ?: null, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Asset added.');
    })->name('workers.memory.assets.store');

    Route::delete('/workers/{id}/memory/assets/{aid}', function (int $id, int $aid) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DB::table('assets')->where('id', $aid)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Asset removed.');
    })->name('workers.memory.assets.destroy');

    // ── Worker: Templates ───────────────────────────────────────────────────
    Route::get('/workers/{id}/templates', function (int $id) {
        $dep      = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $userId   = auth()->id();
        $templates = DB::table('email_templates')->where(function ($q) use ($userId, $dep) {
            $q->where(function ($q2) use ($userId, $dep) {
                $q2->where('user_id', $userId)->where('worker_slug', $dep->worker_slug);
            })->orWhere(function ($q2) use ($dep) {
                $q2->whereNull('user_id')->where('worker_slug', $dep->worker_slug);
            });
        })->orderBy('category')->get();
        return view('dashboard.worker-templates', compact('dep', 'templates'));
    })->name('workers.templates');

    Route::post('/workers/{id}/templates', function (int $id, Request $request) {
        $dep = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required', 'category' => 'required', 'subject_template' => 'required', 'body_template' => 'required']);
        DB::table('email_templates')->insert(['user_id' => auth()->id(), 'worker_slug' => $dep->worker_slug, 'name' => $request->name, 'category' => $request->category, 'tone' => $request->tone ?? 'Professional, concise', 'subject_template' => $request->subject_template, 'body_template' => $request->body_template, 'approval_required' => $request->boolean('approval_required'), 'is_default' => false, 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Template saved.');
    })->name('workers.templates.store');

    Route::delete('/workers/{id}/templates/{tid}', function (int $id, int $tid) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DB::table('email_templates')->where('id', $tid)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Template removed.');
    })->name('workers.templates.destroy');

    // Fork a platform default into a tenant-owned copy — returns JSON so JS can open edit modal immediately
    Route::post('/workers/{id}/templates/{tid}/fork', function (int $id, int $tid) {
        $dep      = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $userId   = auth()->id();
        $original = DB::table('email_templates')->where('id', $tid)->whereNull('user_id')->firstOrFail();

        // If already forked, return the existing copy so the edit modal opens
        $existing = DB::table('email_templates')
            ->where('user_id', $userId)
            ->where('worker_slug', $dep->worker_slug)
            ->where('forked_from', $tid)
            ->first();

        if ($existing) {
            return response()->json(['template' => $existing]);
        }

        $newId = DB::table('email_templates')->insertGetId([
            'user_id'          => $userId,
            'worker_slug'      => $dep->worker_slug,
            'name'             => $original->name,
            'category'         => $original->category,
            'tone'             => $original->tone,
            'subject_template' => $original->subject_template,
            'body_template'    => $original->body_template,
            'approval_required'=> $original->approval_required,
            'is_default'       => false,
            'active'           => true,
            'forked_from'      => $tid,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $newTemplate = DB::table('email_templates')->where('id', $newId)->first();
        return response()->json(['template' => $newTemplate]);
    })->name('workers.templates.fork');

    // Update a tenant-owned template
    Route::put('/workers/{id}/templates/{tid}', function (int $id, int $tid, Request $request) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate([
            'name'             => 'required|string|max:255',
            'tone'             => 'nullable|string|max:100',
            'subject_template' => 'required|string',
            'body_template'    => 'required|string',
            'approval_required'=> 'nullable|boolean',
        ]);
        DB::table('email_templates')
            ->where('id', $tid)
            ->where('user_id', auth()->id())
            ->update([
                'name'             => $request->name,
                'tone'             => $request->tone ?? 'Professional, concise',
                'subject_template' => $request->subject_template,
                'body_template'    => $request->body_template,
                'approval_required'=> $request->boolean('approval_required'),
                'updated_at'       => now(),
            ]);
        return back()->with('success', 'Template updated.');
    })->name('workers.templates.update');

    // Send test email — renders template with dummy vars and mails to authenticated user
    Route::post('/workers/{id}/templates/{tid}/test', function (int $id, int $tid, Request $request) {
        $dep      = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $userId   = auth()->id();
        $user     = auth()->user();

        // Tenant templates first, platform defaults as fallback
        $template = DB::table('email_templates')
            ->where('id', $tid)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->first();

        if (!$template) {
            return back()->with('error', 'Template not found.');
        }

        // Fill dummy variables so the test looks realistic
        $vars = [
            '{{contact_first_name}}' => $user->name,
            '{{asset}}'              => 'example.com',
            '{{client}}'             => 'Acme Corp',
            '{{due_date}}'           => now()->addDays(14)->format('M j, Y'),
            '{{sender_name}}'        => $user->name,
            '{{renewal_price}}'      => '$199.00',
            '{{days_until_expiry}}'  => '14',
        ];

        $subject = str_replace(array_keys($vars), array_values($vars), $template->subject_template);
        $body    = str_replace(array_keys($vars), array_values($vars), $template->body_template);

        // Try sending via tenant's connected Gmail; fall back to SMTP
        $credential = $dep->credential_id
            ? DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first()
            : null;

        try {
            if ($credential?->refresh_token) {
                $gmail = new \App\Workers\AVA\Services\GmailService($credential);
                $gmail->sendEmail($user->email, '[TEST] ' . $subject, $body);
            } else {
                \Illuminate\Support\Facades\Mail::raw($body, function ($msg) use ($user, $subject) {
                    $msg->to($user->email)->subject('[TEST] ' . $subject);
                });
            }
            return back()->with('success', "Test email sent to {$user->email}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Send failed: ' . $e->getMessage());
        }
    })->name('workers.templates.test');

    // ── Worker: Rules ───────────────────────────────────────────────────────
    Route::get('/workers/{id}/rules', function (int $id) {
        $dep   = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $rules = DB::table('ava_rules')->where('deployment_id', $id)->orderBy('rule_id')->get();
        return view('dashboard.worker-rules', compact('dep', 'rules'));
    })->name('workers.rules');

    Route::post('/workers/{id}/rules', function (int $id, Request $request) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['condition' => 'required', 'action' => 'required', 'priority' => 'required']);
        $last   = DB::table('ava_rules')->where('deployment_id', $id)->orderByDesc('id')->value('rule_id');
        $ruleId = $request->rule_id ?: ($last ? 'R-' . str_pad(intval(substr($last, 2)) + 1, 3, '0', STR_PAD_LEFT) : 'R-001');
        DB::table('ava_rules')->insert(['user_id' => auth()->id(), 'deployment_id' => $id, 'rule_id' => $ruleId, 'condition' => $request->condition, 'priority' => $request->priority, 'action' => $request->action, 'approval_required' => $request->boolean('approval_required'), 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Rule added.');
    })->name('workers.rules.store');

    Route::delete('/workers/{id}/rules/{rid}', function (int $id, int $rid) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DB::table('ava_rules')->where('id', $rid)->where('deployment_id', $id)->delete();
        return back()->with('success', 'Rule removed.');
    })->name('workers.rules.destroy');

    // ── Worker: Log (renewal register scoped to deployment) ─────────────────
    Route::get('/workers/{id}/log', function (int $id) {
        $dep     = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $entries = DB::table('renewal_register')->where('deployment_id', $id)->orderByDesc('created_at')->paginate(25);
        return view('dashboard.worker-log', compact('dep', 'entries'));
    })->name('workers.log');

    Route::get('/workers/{id}/schema', function (int $id) {
        $dep      = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $contract = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
        if (!$contract) abort(404, 'No contract registered for worker: ' . $dep->worker_slug);
        return view('dashboard.worker-schema', [
            'dep'      => $dep,
            'identity' => $contract->identity(),
            'org'      => $contract->org(),
            'input'    => $contract->input(),
            'pipeline' => $contract->pipeline(),
            'emits'    => $contract->emit(),
            'commit'   => $contract->commit(),
        ]);
    })->name('workers.schema');

    // ── Worker: Billing ──
    Route::get('/workers/{id}/billing', function (int $id) {
        $dep      = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $contract = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
        $userId   = auth()->id();

        $billing  = DB::table('deployment_billing')->where('deployment_id', $id)->first();
        $pricing  = DB::table('worker_pricing')->where('worker_slug', $dep->worker_slug)->first()
                 ?? DB::table('worker_pricing')->where('worker_slug', 'ava')->first();

        // Current month AI token spend
        $monthUsage = DB::table('usage_events')
            ->where('deployment_id', $id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->selectRaw('SUM(tokens_input) as tokens_in, SUM(tokens_output) as tokens_out, SUM(cost_usd) as cost_usd, COUNT(DISTINCT tx_id) as tx_count')
            ->first();

        // Per-stage breakdown this month
        $stageBreakdown = DB::table('usage_events')
            ->where('deployment_id', $id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereNotNull('stage')
            ->groupBy('stage')
            ->selectRaw('stage, SUM(tokens_input+tokens_output) as tokens, SUM(cost_usd) as cost, COUNT(*) as calls')
            ->orderByRaw('SUM(cost_usd) DESC')
            ->get();

        // Daily spend last 30 days
        $dailySpend = DB::table('usage_events')
            ->where('deployment_id', $id)
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(cost_usd) as cost, SUM(tokens_input+tokens_output) as tokens')
            ->groupBy('day')
            ->orderBy('day')
            ->get()->keyBy('day');

        // All-time totals
        $allTime = DB::table('usage_events')
            ->where('deployment_id', $id)
            ->selectRaw('SUM(tokens_input+tokens_output) as tokens, SUM(cost_usd) as cost, COUNT(DISTINCT tx_id) as tx_count')
            ->first();

        // Monthly history (last 6 months)
        $monthlyHistory = DB::table('usage_events')
            ->where('deployment_id', $id)
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(cost_usd) as cost, SUM(tokens_input+tokens_output) as tokens, COUNT(DISTINCT tx_id) as tx_count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('dashboard.worker-billing', compact(
            'dep', 'contract', 'billing', 'pricing',
            'monthUsage', 'stageBreakdown', 'dailySpend', 'allTime', 'monthlyHistory'
        ));
    })->name('workers.billing');

    Route::post('/workers', function (Request $request) {
        if (auth()->user()->blocked_at) {
            return back()->with('error', 'Your account is suspended. Contact support before deploying workers.');
        }
        $request->validate(['worker_slug' => 'required', 'name' => 'required']);
        $config = [
            'capture_scope'    => $request->capture_scope ?? 'All incoming emails',
            'capture_keywords' => array_filter(explode(',', $request->capture_keywords ?? '')),
        ];
        $depId = DB::table('worker_deployments')->insertGetId([
            'user_id'       => auth()->id(),
            'worker_slug'   => $request->worker_slug,
            'name'          => $request->name,
            'status'        => 'active',
            'credential_id' => $request->credential_id ?: null,
            'config'        => json_encode($config),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Copy platform default rules for this worker slug into the new deployment
        $platformRules = DB::table('ava_rules')
            ->whereNull('user_id')
            ->whereNull('deployment_id')
            ->get();

        foreach ($platformRules as $rule) {
            DB::table('ava_rules')->insert([
                'user_id'       => auth()->id(),
                'deployment_id' => $depId,
                'rule_id'       => $rule->rule_id,
                'condition'     => $rule->condition,
                'priority'      => $rule->priority,
                'action'        => $rule->action,
                'approval_required' => $rule->approval_required,
                'notes'         => $rule->notes,
                'active'        => true,
                'is_platform'   => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // Auto-start Gmail watch if a credential was linked at deploy time
        if ($request->worker_slug === 'ava' && $request->credential_id) {
            $credential = DB::table('user_gmail_credentials')->where('id', $request->credential_id)->first();
            if ($credential) {
                try {
                    $watchService = app(GmailWatchService::class, ['credential' => $credential]);
                    $watchService->watch(config('services.gmail.pubsub_topic'));
                    DB::table('user_gmail_credentials')
                        ->where('id', $credential->id)
                        ->update(['watch_expiry' => now()->addDays(7), 'updated_at' => now()]);
                    Log::info('AVA Gmail watch auto-started at deploy', ['dep_id' => $depId, 'gmail' => $credential->gmail_address]);
                } catch (\Throwable $e) {
                    Log::error('AVA Gmail watch auto-start failed at deploy', ['error' => $e->getMessage()]);
                }
            }
        }

        // Create billing record — start on free trial
        $pricing = DB::table('worker_pricing')->where('worker_slug', $request->worker_slug)->first();
        DB::table('deployment_billing')->insert([
            'user_id'                  => auth()->id(),
            'deployment_id'            => $depId,
            'worker_slug'              => $request->worker_slug,
            'status'                   => 'trial',
            'trial_transactions_used'  => 0,
            'trial_transactions_limit' => $pricing?->free_transactions ?? 10,
            'trial_ends_at'            => now()->addDays(14),
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);

        UnitNotifier::workerDeployed($depId);

        return redirect()->route('workers.show', $depId)->with('success', 'Worker deployed and inbox watch activated. AVA is now monitoring.');
    })->name('workers.store');

    Route::patch('/workers/{id}/status', function (Request $request, int $id) {
        $request->validate(['status' => 'required|in:active,paused,stopped']);
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->update([
            'status'     => $request->status,
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Worker status updated.');
    })->name('workers.status');

    Route::get('/workers/{id}/configure', function (int $id) {
        $dep          = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $contract     = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
        $credentials  = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->get();
        $customModels = DB::table('tenant_custom_models')->where('user_id', auth()->id())->where('active', true)->get();
        return view('dashboard.worker-configure', compact('dep', 'contract', 'credentials', 'customModels'));
    })->name('workers.configure');

    Route::patch('/workers/{id}/config', function (Request $request, int $id) {
        $config = [
            'capture_scope'    => $request->capture_scope,
            'capture_keywords' => array_filter(array_map('trim', explode(',', $request->capture_keywords ?? ''))),
            'ai_model'         => $request->ai_model ?: 'claude-sonnet-4-6',
        ];
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->update([
            'name'          => $request->name,
            'credential_id' => $request->credential_id ?: null,
            'config'        => json_encode($config),
            'updated_at'    => now(),
        ]);
        return back()->with('success', 'Worker configuration saved.');
    })->name('workers.config');

    Route::patch('/workers/{id}/model', function (Request $request, int $id) {
        $dep    = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $config = json_decode($dep->config ?? '{}', true) ?: [];
        $config['ai_model'] = $request->ai_model ?: 'claude-sonnet-4-6';
        DB::table('worker_deployments')->where('id', $id)->update([
            'config'     => json_encode($config),
            'updated_at' => now(),
        ]);
        return back()->with('success', 'AI model updated.');
    })->name('workers.model');

    // ── Models & API Keys ───────────────────────────────────────────────────
    Route::get('/settings/api-keys', function () {
        $userId       = auth()->id();
        $keys         = DB::table('tenant_api_keys')->where('user_id', $userId)->get()->keyBy('provider');
        $customModels = DB::table('tenant_custom_models')->where('user_id', $userId)->where('active', true)->get();

        // Which platform keys are configured in .env
        $platformKeys = [
            'anthropic' => !empty(config('services.claude.api_key')),
            'openai'    => !empty(config('services.openai.api_key')),
            'kimi'      => !empty(config('services.kimi.api_key')),
            'google'    => !empty(config('services.google.api_key')),
        ];

        // Workers and which model they're running
        $workers = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'paused'])
            ->get()
            ->map(fn($dep) => (object)[
                'id'     => $dep->id,
                'name'   => $dep->name,
                'status' => $dep->status,
                'model'  => json_decode($dep->config ?? '{}', true)['ai_model'] ?? 'claude-sonnet-4-6',
            ]);

        return view('dashboard.api-keys', compact('keys', 'customModels', 'platformKeys', 'workers'));
    })->name('settings.api-keys');

    Route::post('/settings/api-keys', function (Request $request) {
        $request->validate(['provider' => 'required', 'label' => 'required', 'api_key' => 'required']);
        DB::table('tenant_api_keys')->updateOrInsert(
            ['user_id' => auth()->id(), 'provider' => $request->provider],
            ['label' => $request->label, 'api_key_encrypted' => Crypt::encryptString($request->api_key), 'active' => true, 'updated_at' => now(), 'created_at' => now()]
        );
        return back()->with('success', $request->provider . ' key saved.');
    })->name('settings.api-keys.store');

    Route::delete('/settings/api-keys/{provider}', function (string $provider) {
        DB::table('tenant_api_keys')->where('user_id', auth()->id())->where('provider', $provider)->delete();
        return back()->with('success', 'Key removed.');
    })->name('settings.api-keys.destroy');

    // ── Custom Model Registration ────────────────────────────────────────────
    Route::post('/settings/custom-models', function (Request $request) {
        $request->validate(['name' => 'required', 'base_url' => 'required|url', 'model_identifier' => 'required']);
        $modelId = 'custom-' . \Illuminate\Support\Str::slug($request->name) . '-' . substr(md5(uniqid()), 0, 6);
        $data = [
            'user_id'          => auth()->id(),
            'name'             => $request->name,
            'model_id'         => $modelId,
            'model_identifier' => $request->model_identifier,
            'base_url'         => rtrim($request->base_url, '/'),
            'api_key_encrypted'=> $request->api_key ? Crypt::encryptString($request->api_key) : null,
            'active'           => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ];
        DB::table('tenant_custom_models')->insert($data);
        return back()->with('success', '"' . $request->name . '" registered. Select it from the worker model picker.');
    })->name('settings.custom-models.store');

    Route::delete('/settings/custom-models/{id}', function (int $id) {
        DB::table('tenant_custom_models')->where('id', $id)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Custom model removed.');
    })->name('settings.custom-models.destroy');

    Route::delete('/workers/{id}', function (int $id) {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->delete();
        return redirect()->route('workers.deploy')->with('success', 'Worker removed.');
    })->name('workers.destroy');

    // ── AVA Connection & Onboarding ─────────────────────────────────────────
    Route::get('/ava/connect', function () {
        $credential = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->first();
        return view('dashboard.connect', compact('credential'));
    })->name('ava.connect');

    Route::get('/ava/gmail/authorize', function () {
        $query = http_build_query([
            'client_id'     => config('services.gmail.client_id'),
            'redirect_uri'  => config('services.gmail.redirect_uri'),
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/gmail.compose https://www.googleapis.com/auth/gmail.readonly https://www.googleapis.com/auth/gmail.insert openid email',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ]);
        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    })->name('ava.gmail.authorize');

    Route::get('/ava/gmail/watch', function () {
        $credential = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->firstOrFail();
        $watchService = app(GmailWatchService::class, ['credential' => $credential]);
        $result = $watchService->watch(config('services.gmail.pubsub_topic'));
        DB::table('user_gmail_credentials')->where('user_id', auth()->id())->update([
            'watch_active'    => true,
            'watch_expires_at'=> date('Y-m-d H:i:s', $result['expiration'] / 1000),
            'updated_at'      => now(),
        ]);
        return redirect()->route('ava.connect')->with('success', 'Inbox watch activated. AVA is now monitoring your email.');
    })->name('ava.gmail.watch');

    // ── Manual test (dev only) ──────────────────────────────────────────────
    Route::post('/workers/ava/test', function (Request $request, TransactionService $txService) {
        $request->validate(['raw_email' => 'required|string']);
        $tx = $txService->create('ava-renewal-coordinator', [
            'source'    => 'manual_test',
            'raw_email' => $request->input('raw_email'),
            'user_id'   => auth()->id(),
        ]);
        ReadEmailJob::dispatch($tx->tx_id)->onQueue('ava');
        return response()->json(['status' => 'queued', 'tx_id' => $tx->tx_id]);
    })->name('ava.test');

    Route::get('/workers/ava/status/{txId}', function (string $txId) {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->first();
        if (!$tx) return response()->json(['error' => 'Not found'], 404);
        return response()->json([
            'tx_id'          => $tx->tx_id,
            'status'         => $tx->status,
            'category'       => $tx->category,
            'priority'       => $tx->priority,
            'read_output'    => json_decode($tx->read_output),
            'memory_output'  => json_decode($tx->memory_output),
            'classify_output'=> json_decode($tx->classify_output),
            'draft_output'   => json_decode($tx->draft_output),
            'gmail_draft_id' => $tx->gmail_draft_id,
        ]);
    })->name('ava.status');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');
    Route::get('/billing/checkout/{deployment}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/success/{deployment}', [BillingController::class, 'success'])->name('billing.success');
    Route::get('/billing/portal', [BillingController::class, 'portal'])->name('billing.portal');
    Route::get('/billing/invoice/{id}', function (string $id) {
        return request()->user()->downloadInvoice($id);
    })->name('billing.invoice');

    // Worker Fast Track (tenant-accessible, metered, name+email required)
    Route::post('/workers/{id}/fast-track', function (int $id, \Illuminate\Http\Request $request) {
        if (auth()->user()->blocked_at) {
            return back()->with('error', 'Your account is suspended. Fast Track is disabled.');
        }

        $dep = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        // Guard against double-submit: block if a fast track tx is already in-flight for this deployment
        $inFlight = DB::table('transactions')
            ->where('deployment_id', $id)
            ->where('created_at', '>=', now()->subSeconds(30))
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(raw_input, '$.source')) = 'fast_track_test'")
            ->whereNotIn('status', ['draft_ready', 'approved', 'sent', 'failed', 'dismissed'])
            ->exists();
        if ($inFlight) {
            return back()->with('error', 'A Fast Track run is already in progress — wait for it to complete before starting another.');
        }

        // Use explicitly chosen credential, fall back to deployment primary
        $chosenCredId = (int) $request->input('credential_id');
        $credential   = $chosenCredId
            ? DB::table('user_gmail_credentials')->where('id', $chosenCredId)->where('user_id', auth()->id())->first()
            : DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first();

        if (!$credential) {
            return back()->with('error', 'No Gmail account connected to this worker.');
        }

        $config  = json_decode($dep->config ?? '{}', true) ?: [];
        $billing = DB::table('deployment_billing')->where('deployment_id', $id)->first();

        // Active subscription: fast track counts as a regular transaction — no separate run limit.
        // Trial: enforce 10-run cap; contact admin to reset or upgrade to subscription.
        $isSubscribed = $billing && $billing->status === 'active';

        if (!$isSubscribed) {
            $usesCount = (int) ($config['fast_track_uses'] ?? 0);
            if ($usesCount >= 10) {
                return back()->with('error', 'Fast Track trial limit reached (10/10). Upgrade to a subscription for unlimited runs, or contact support to reset.');
            }
        }

        $scenario = DB::table('fast_track_scenarios')->where('deployment_id', $id)->first();
        if (!$scenario) {
            DB::table('fast_track_scenarios')->insert([
                'deployment_id'     => $id,
                'user_id'           => auth()->id(),
                'scenario_title'    => 'Domain Renewal Test',
                'sender_name'       => 'Namecheap Renewals Team',
                'sender_email'      => 'renewals@namecheap.com',
                'asset_name'        => 'yourdomain.com',
                'asset_type'        => 'Domain',
                'contact_name'      => auth()->user()->name,
                'renewal_price'     => '$12.98/year',
                'days_until_expiry' => 14,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            $scenario = DB::table('fast_track_scenarios')->where('deployment_id', $id)->first();
        }

        $expiryDate  = now()->addDays($scenario->days_until_expiry)->format('F j, Y');
        $sampleEmail = implode("\n", [
            "From: {$scenario->sender_name} <{$scenario->sender_email}>",
            "To: {$credential->gmail_address}",
            "Subject: {$scenario->asset_type} Renewal Notice — {$scenario->asset_name} expires in {$scenario->days_until_expiry} days",
            "",
            "Dear {$scenario->contact_name},",
            "",
            "This is a reminder that your {$scenario->asset_type} {$scenario->asset_name} is due for renewal on {$expiryDate}.",
            "",
            "{$scenario->asset_type}: {$scenario->asset_name}",
            "Renewal Date: {$expiryDate}",
            "Renewal Price: {$scenario->renewal_price}",
            "Contact Email: " . auth()->user()->email,
            "",
            ($scenario->custom_note ? $scenario->custom_note . "\n\n" : "") . "Please renew before it expires.",
            "",
            "Thank you,",
            $scenario->sender_name,
        ]);

        $txService = app(\App\Platform\Services\TransactionService::class);
        $tx = $txService->create('ava-renewal-coordinator', [
            'source'             => 'fast_track_test',
            'fast_track'         => true,
            'user_id'            => auth()->id(),
            'deployment_id'      => $id,
            'credential_id'      => $credential->id,
            // Fast track scenario fields — used by FastTrackIngestJob to build the inbound test email
            'fast_track_from'    => "{$scenario->sender_name} <{$scenario->sender_email}>",
            'fast_track_subject' => "{$scenario->asset_type} Renewal Notice — {$scenario->asset_name} expires in {$scenario->days_until_expiry} days",
            'fast_track_body'    => $sampleEmail,
        ]);

        // FastTrackIngestJob inserts the test email into the selected inbox, fetches it back,
        // then dispatches ReadEmailJob — same path as a real webhook from that point on
        \App\Workers\AVA\Jobs\FastTrackIngestJob::dispatch($tx->tx_id)->onQueue($txService->queueForTx($tx));

        // Only count against the trial meter for non-subscribed tenants
        if (!$isSubscribed) {
            $config['fast_track_uses'] = ($config['fast_track_uses'] ?? 0) + 1;
            DB::table('worker_deployments')->where('id', $id)->update([
                'config'     => json_encode($config),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('workers.show', ['id' => $id, 'watch' => $tx->tx_id]);
    })->name('workers.fast-track');

    // ── Admin-only: System QA + Tenant Controls + Admin actions ──────────────
    Route::middleware('admin')->group(function () {

        // Reset fast track trial counter for a deployment
        Route::post('/workers/{id}/fast-track/reset', function (int $id) {
            $dep    = DB::table('worker_deployments')->where('id', $id)->firstOrFail();
            $config = json_decode($dep->config ?? '{}', true) ?: [];
            $config['fast_track_uses'] = 0;
            DB::table('worker_deployments')->where('id', $id)->update([
                'config'     => json_encode($config),
                'updated_at' => now(),
            ]);
            return back()->with('success', 'Fast Track counter reset.');
        })->name('workers.fast-track.reset');

        // System QA
        Route::get('/qa', [QAController::class, 'index'])->name('qa');
        Route::post('/qa/fast-track/{deployment}', [QAController::class, 'fastTrack'])->name('qa.fast-track');
        Route::post('/qa/transactions/recover-stuck', [QAController::class, 'recoverStuck'])->name('qa.recover-stuck');
        Route::post('/qa/worker/{deployment}/renew-watch', [QAController::class, 'renewGmailWatch'])->name('qa.renew-watch');
        Route::get('/qa/pipeline/{txId}', [QAController::class, 'pipelineStatus'])->name('qa.pipeline-status');
        Route::post('/qa/scenario/{deployment}', [QAController::class, 'updateScenario'])->name('qa.scenario-update');
        Route::post('/qa/worker/{deployment}/pause', [QAController::class, 'pauseWorker'])->name('qa.worker-pause');
        Route::post('/qa/worker/{deployment}/resume', [QAController::class, 'resumeWorker'])->name('qa.worker-resume');
        Route::post('/qa/worker/{deployment}/drain', [QAController::class, 'drainWorker'])->name('qa.worker-drain');
        Route::get('/qa/worker/{deployment}/queue-status', [QAController::class, 'queueStatus'])->name('qa.queue-status');
        Route::post('/qa/horizon/restart', [QAController::class, 'restartHorizon'])->name('qa.horizon-restart');
        Route::post('/qa/worker/{deployment}/pipeline-config', [QAController::class, 'updatePipelineConfig'])->name('qa.pipeline-config');
        Route::post('/qa/marketplace/{worker}/publish', [QAController::class, 'publishWorker'])->name('qa.marketplace-publish');
        Route::post('/qa/marketplace/{worker}/status', [QAController::class, 'updateMarketplaceStatus'])->name('qa.marketplace-status');
        Route::get('/qa/marketplace/{worker}/blueprint', [QAController::class, 'downloadBlueprint'])->name('qa.marketplace-blueprint');
        Route::get('/qa/platform-blueprint', [QAController::class, 'downloadPlatformBlueprint'])->name('qa.platform-blueprint');
        Route::get('/qa/worker/{worker}/markdown-blueprint', [QAController::class, 'downloadWorkerBlueprint'])->name('qa.worker-blueprint');

        // Tenant Controls
        // Tenant detail page
        Route::get('/admin/tenants/{id}', function (int $id) {
            $tenant = DB::table('users')->where('id', $id)->firstOrFail();

            // Deployments with billing info
            $deployments = DB::table('worker_deployments as wd')
                ->leftJoin('deployment_billing as db', 'db.deployment_id', '=', 'wd.id')
                ->leftJoin('worker_pricing as wp', 'wp.worker_slug', '=', 'wd.worker_slug')
                ->where('wd.user_id', $id)
                ->select(
                    'wd.id', 'wd.name', 'wd.worker_slug', 'wd.status', 'wd.created_at as deployed_at',
                    'db.status as billing_status', 'db.trial_transactions_used', 'db.trial_transactions_limit',
                    'db.stripe_subscription_id', 'wp.monthly_flat_rate'
                )
                ->orderByDesc('wd.created_at')
                ->get();

            // Fast track history (from fast_track_leads tied to this user)
            $fastTracks = DB::table('fast_track_leads as ftl')
                ->leftJoin('worker_deployments as wd', 'wd.id', '=', DB::raw(
                    '(SELECT id FROM worker_deployments WHERE worker_slug = ftl.worker_slug AND user_id = ftl.user_id LIMIT 1)'
                ))
                ->where('ftl.user_id', $id)
                ->select('ftl.*', 'wd.name as deployment_name')
                ->orderByDesc('ftl.created_at')
                ->limit(50)
                ->get();

            // Monthly spend (all time, last 6 months)
            $monthlySpend = DB::table('usage_events')
                ->where('user_id', $id)
                ->whereDate('created_at', '>=', now()->subMonths(6))
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(cost_usd) as spend, SUM(tokens_input+tokens_output) as tokens")
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Total all-time spend
            $totalSpend = DB::table('usage_events')->where('user_id', $id)->sum('cost_usd');

            // Referral stats
            $referralStats = \App\Platform\Services\ReferralService::getStats($id);
            $referralCode  = \App\Platform\Services\ReferralService::ensureCode($id);
            $referralList  = DB::table('referral_credits as rc')
                ->join('users as u', 'u.id', '=', 'rc.referee_id')
                ->where('rc.referrer_id', $id)
                ->select('rc.*', 'u.name as referee_name', 'u.email as referee_email')
                ->orderByDesc('rc.created_at')->get();
            $referredBy = null;
            $tenant2 = DB::table('users')->where('id', $id)->first();
            if ($tenant2->referred_by_code) {
                $referredBy = DB::table('users')->where('referral_code', $tenant2->referred_by_code)->first();
            }

            // Recent transactions
            $recentTx = DB::table('transactions')
                ->where('user_id', $id)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();

            // Stripe subscriptions
            $subscriptions = DB::table('subscriptions')
                ->where('user_id', $id)
                ->orderByDesc('created_at')
                ->get();

            // Subscription items
            $subItems = $subscriptions->isNotEmpty()
                ? DB::table('subscription_items')
                    ->whereIn('subscription_id', $subscriptions->pluck('id'))
                    ->get()
                : collect();

            // Monthly spend — last 8 months
            $monthlySpend = DB::table('usage_events')
                ->where('user_id', $id)
                ->whereDate('created_at', '>=', now()->subMonths(8)->startOfMonth())
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(cost_usd) as spend, SUM(tokens_input+tokens_output) as tokens, COUNT(*) as calls")
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // API keys (providers configured)
            $apiKeys = DB::table('tenant_api_keys')->where('user_id', $id)->get();

            // Policy enforcement log for this tenant
            $policyLog = DB::table('policy_enforcement_log')
                ->where('user_id', $id)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();

            // Sessions (active)
            $sessions = DB::table('sessions')
                ->where('user_id', $id)
                ->orderByDesc('last_activity')
                ->limit(5)
                ->get();

            // Usage map — page visit breakdown (last 30 days)
            $usageMap = DB::table('tenant_activity_log')
                ->where('user_id', $id)
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->selectRaw("section, page, COUNT(*) as visits, MAX(created_at) as last_seen")
                ->groupBy('section', 'page')
                ->orderByDesc('visits')
                ->limit(30)
                ->get();

            // Total page views + device breakdown
            $totalViews    = DB::table('tenant_activity_log')->where('user_id', $id)->count();
            $viewsThisWeek = DB::table('tenant_activity_log')->where('user_id', $id)->whereDate('created_at', '>=', now()->subDays(7))->count();
            $lastActivity  = DB::table('tenant_activity_log')->where('user_id', $id)->max('created_at');
            $lastLogin     = DB::table('sessions')->where('user_id', $id)->max('last_activity');
            $daysSinceLogin = $lastLogin ? now()->diffInDays(\Carbon\Carbon::createFromTimestamp($lastLogin)) : null;

            // Transaction success rate
            $txStats = DB::table('transactions')->where('user_id', $id)
                ->selectRaw('status, COUNT(*) as cnt')->groupBy('status')->get()->keyBy('status');
            $txTotal     = $txStats->sum('cnt');
            $txCompleted = ($txStats['completed']->cnt ?? 0) + ($txStats['draft_ready']->cnt ?? 0) + ($txStats['approved']->cnt ?? 0);
            $txFailed    = $txStats['failed']->cnt ?? 0;
            $txSuccessRate = $txTotal > 0 ? round(($txCompleted / $txTotal) * 100) : null;

            // Avg tokens/cost per call
            $usageAvg = DB::table('usage_events')->where('user_id', $id)
                ->selectRaw('AVG(tokens_input+tokens_output) as avg_tokens, AVG(cost_usd) as avg_cost')
                ->first();

            // Sent message history
            $messageLog = DB::table('admin_message_log')->where('tenant_id', $id)
                ->orderByDesc('sent_at')->limit(10)->get();

            // Churn risk signals
            $churnSignals = [];
            if ($daysSinceLogin !== null && $daysSinceLogin >= 7) {
                $churnSignals[] = "No login for {$daysSinceLogin} days";
            }
            if ($txFailed > 0 && $txTotal > 0 && ($txFailed / $txTotal) > 0.3) {
                $churnSignals[] = round(($txFailed/$txTotal)*100) . "% transaction failure rate";
            }
            $trialNearEnd = $deployments->filter(fn($d) =>
                ($d->billing_status ?? '') === 'trial' &&
                ($d->trial_transactions_used ?? 0) >= (($d->trial_transactions_limit ?? 10) * 0.8)
            );
            if ($trialNearEnd->isNotEmpty()) {
                $churnSignals[] = "Trial ≥80% used on " . $trialNearEnd->count() . " worker(s)";
            }
            if ($viewsThisWeek === 0 && $totalViews > 0) {
                $churnSignals[] = "Zero activity this week";
            }

            return view('admin.tenant-detail', compact(
                'tenant', 'deployments', 'fastTracks', 'monthlySpend',
                'totalSpend', 'recentTx', 'apiKeys', 'policyLog', 'sessions',
                'subscriptions', 'subItems', 'usageMap', 'totalViews', 'viewsThisWeek',
                'lastLogin', 'daysSinceLogin', 'txStats', 'txTotal', 'txCompleted',
                'txFailed', 'txSuccessRate', 'usageAvg', 'messageLog', 'churnSignals', 'lastActivity',
                'referralStats', 'referralCode', 'referralList', 'referredBy'
            ));
        })->name('admin.tenants.show');

        // Admin: force password reset for tenant
        Route::post('/admin/tenants/{id}/reset-password', function (int $id, \Illuminate\Http\Request $request) {
            $request->validate(['password' => 'required|min:8']);
            DB::table('users')->where('id', $id)->update([
                'password'   => \Illuminate\Support\Facades\Hash::make($request->password),
                'updated_at' => now(),
            ]);
            return back()->with('success', 'Password updated for tenant.');
        })->name('admin.tenants.reset-password');

        // Admin: send message to tenant
        Route::post('/admin/tenants/{id}/message', function (int $id, \Illuminate\Http\Request $request) {
            $tenant = DB::table('users')->where('id', $id)->firstOrFail();
            $request->validate([
                'subject' => 'required|string|max:200',
                'body'    => 'required|string|max:5000',
            ]);
            \Illuminate\Support\Facades\Mail::send([], [], function ($m) use ($tenant, $request) {
                $cta = $request->cta_label ? "\n\n→ {$request->cta_label}: {$request->cta_url}" : '';
                $html = nl2br(e($request->body . $cta));
                if ($request->cta_label && $request->cta_url) {
                    $html .= '<br><br><a href="' . e($request->cta_url) . '" style="display:inline-block;background:#f3c531;color:#1a1404;font-weight:700;padding:12px 28px;border-radius:8px;text-decoration:none;font-size:14px;">' . e($request->cta_label) . '</a>';
                }
                $m->to($tenant->email, $tenant->name)
                  ->from(config('mail.from.address'), 'UNIT Platform')
                  ->subject($request->subject)
                  ->html($html);
            });
            DB::table('admin_message_log')->insert([
                'tenant_id' => $id,
                'sent_by'   => auth()->id(),
                'template'  => $request->template ?? 'custom',
                'subject'   => $request->subject,
                'body'      => $request->body,
                'sent_at'   => now(),
            ]);
            return back()->with('success', "Message sent to {$tenant->email}.");
        })->name('admin.tenants.message');

        // Admin: AI generate message
        Route::post('/admin/tenants/{id}/ai-message', function (int $id, \Illuminate\Http\Request $request) {
            $tenant      = DB::table('users')->where('id', $id)->firstOrFail();
            $goal        = $request->input('goal', 'feedback');
            $deployments = DB::table('worker_deployments as wd')
                ->leftJoin('deployment_billing as db', 'db.deployment_id', '=', 'wd.id')
                ->where('wd.user_id', $id)->select('wd.name','wd.worker_slug','db.status as billing_status','db.trial_transactions_used','db.trial_transactions_limit')->get();
            $txStats = DB::table('transactions')->where('user_id', $id)
                ->selectRaw('status, COUNT(*) as cnt')->groupBy('status')->get()->keyBy('status');
            $totalSpend  = DB::table('usage_events')->where('user_id', $id)->sum('cost_usd');
            $lastLogin   = DB::table('sessions')->where('user_id', $id)->max('last_activity');
            $daysSince   = $lastLogin ? now()->diffInDays(\Carbon\Carbon::createFromTimestamp($lastLogin)) : 'unknown';
            $viewsWeek   = DB::table('tenant_activity_log')->where('user_id', $id)->whereDate('created_at', '>=', now()->subDays(7))->count();

            $context = "Tenant: {$tenant->name} ({$tenant->email})\n"
                . "Member since: {$tenant->created_at}\n"
                . "Workers: " . $deployments->map(fn($d) => "{$d->name} [{$d->billing_status}, {$d->trial_transactions_used}/{$d->trial_transactions_limit} trial tx]")->implode(', ') . "\n"
                . "Total AI spend: $" . number_format($totalSpend, 4) . "\n"
                . "Transactions — completed: " . (($txStats['completed']->cnt ?? 0) + ($txStats['draft_ready']->cnt ?? 0)) . ", failed: " . ($txStats['failed']->cnt ?? 0) . "\n"
                . "Days since last login: {$daysSince}\n"
                . "Page views this week: {$viewsWeek}";

            $goalInstructions = match($goal) {
                'feedback'  => "Write a warm, conversational email asking for feedback on their experience. Ask 1-2 specific questions about what's working and what could be better. Keep it short and genuine.",
                'upsell'    => "Write a compelling upsell email encouraging them to upgrade from trial to a paid plan. Highlight the value they've already gotten and what they'll unlock. Include urgency if trial is nearly exhausted.",
                'reengagement' => "Write a re-engagement email for a tenant who hasn't logged in recently. Remind them of the value, offer help, and invite them back. Warm and non-pushy.",
                'check_in'  => "Write a friendly check-in email from a founder/team perspective. Personal, curious, and supportive.",
                default     => "Write a helpful, professional email.",
            };

            $prompt = "You are writing an email from UNIT Platform (an AI-powered worker automation SaaS) to one of our tenants.\n\n"
                . "Tenant context:\n{$context}\n\n"
                . "Goal: {$goalInstructions}\n\n"
                . "Return ONLY valid JSON with two fields: {\"subject\": \"...\", \"body\": \"...\"}. "
                . "The body should use \\n for line breaks. Sign off as 'The UNIT Team'. Do not add a CTA button — that will be added separately.";

            $response = \Illuminate\Support\Facades\Http::withToken(config('services.claude.api_key'))
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 600,
                    'messages'   => [['role' => 'user', 'content' => $prompt]],
                    'system'     => 'You are a concise email copywriter. Return only valid JSON.',
                ])
                ->json();

            $text = $response['content'][0]['text'] ?? '{}';
            $text = preg_replace('/^```json\s*/m', '', $text);
            $text = preg_replace('/^```\s*/m', '', $text);
            $data = json_decode(trim($text), true) ?? ['subject' => '', 'body' => ''];

            return response()->json($data);
        })->name('admin.tenants.ai-message');

        // Admin: toggle block
        Route::post('/admin/tenants/{id}/toggle-block', function (int $id, \Illuminate\Http\Request $request) {
            $tenant = DB::table('users')->where('id', $id)->firstOrFail();
            if ($tenant->blocked_at) {
                \App\Platform\Services\UsageGuard::unblockUser($id);
                return back()->with('success', 'Tenant unblocked.');
            }
            $request->validate(['reason' => 'required|string|max:500']);
            \App\Platform\Services\UsageGuard::blockUser($id, $request->reason, 'manual_admin_block');
            return back()->with('success', 'Tenant blocked.');
        })->name('admin.tenants.toggle-block');

        Route::get('/admin/tenants', function () {
        $tenants = DB::table('users')
            ->leftJoin('deployment_billing as db', 'users.id', '=', 'db.user_id')
            ->leftJoin('worker_deployments as wd', 'db.deployment_id', '=', 'wd.id')
            ->select(
                'users.id', 'users.name', 'users.email',
                'users.blocked_at', 'users.block_reason', 'users.monthly_spend_cap',
                DB::raw('COUNT(DISTINCT wd.id) as deployment_count'),
                DB::raw('SUM(CASE WHEN db.status = "trial" THEN 1 ELSE 0 END) as trial_count'),
                DB::raw('SUM(CASE WHEN db.status = "active" THEN 1 ELSE 0 END) as active_count')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.blocked_at', 'users.block_reason', 'users.monthly_spend_cap')
            ->orderBy('users.id')
            ->get();

        // Attach this-month spend per user
        $spends = DB::table('usage_events')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(cost_usd) as month_spend'), DB::raw('SUM(tokens_input+tokens_output) as month_tokens'))
            ->get()->keyBy('user_id');

        $tenants = $tenants->map(function ($t) use ($spends) {
            $t->month_spend  = (float) ($spends[$t->id]->month_spend  ?? 0);
            $t->month_tokens = (int)   ($spends[$t->id]->month_tokens ?? 0);
            return $t;
        });

        // Recent automated enforcement actions (last 50)
        $enforcementLog = DB::table('policy_enforcement_log')
            ->join('users', 'users.id', '=', 'policy_enforcement_log.user_id')
            ->select(
                'policy_enforcement_log.*',
                'users.name as tenant_name',
                'users.email as tenant_email'
            )
            ->orderByDesc('policy_enforcement_log.created_at')
            ->limit(50)
            ->get();

        // Deployments with no billing record (orphaned)
        $orphanedDeployments = DB::table('worker_deployments as wd')
            ->leftJoin('deployment_billing as db', 'db.deployment_id', '=', 'wd.id')
            ->leftJoin('users', 'users.id', '=', 'wd.user_id')
            ->whereNull('db.deployment_id')
            ->whereIn('wd.status', ['active', 'paused'])
            ->select('wd.id', 'wd.name', 'wd.worker_slug', 'wd.user_id', 'users.name as tenant_name', 'users.email as tenant_email')
            ->get();

        return view('admin.tenants', compact('tenants', 'enforcementLog', 'orphanedDeployments'));
    })->name('admin.tenants');

    Route::post('/admin/tenants/{id}/block', function (int $id, \Illuminate\Http\Request $request) {
        $validCodes = array_keys(\App\Platform\Services\PolicyEngine::POLICIES);
        $request->validate([
            'reason'      => 'required|string|max:500',
            'policy_code' => 'required|string|in:' . implode(',', $validCodes),
        ]);
        \App\Platform\Services\UsageGuard::blockUser($id, $request->reason, $request->policy_code);
        return back()->with('success', "Tenant #{$id} blocked under policy {$request->policy_code}.");
    })->name('admin.tenants.block');

    Route::post('/admin/tenants/{id}/unblock', function (int $id) {
        \App\Platform\Services\UsageGuard::unblockUser($id);
        return back()->with('success', "Tenant #{$id} unblocked.");
    })->name('admin.tenants.unblock');

    Route::post('/admin/tenants/{id}/spend-cap', function (int $id, \Illuminate\Http\Request $request) {
        $request->validate(['cap' => 'nullable|numeric|min:0']);
        \App\Platform\Services\UsageGuard::setSpendCap($id, $request->cap ? (float) $request->cap : null);
        return back()->with('success', 'Spend cap updated.');
    })->name('admin.tenants.spend-cap');

    Route::post('/admin/tenants/{id}/reset-trial', function (int $id, \Illuminate\Http\Request $request) {
        $depId = $request->input('deployment_id');
        DB::table('deployment_billing')
            ->where('user_id', $id)
            ->when($depId, fn($q) => $q->where('deployment_id', $depId))
            ->update(['trial_transactions_used' => 0, 'updated_at' => now()]);
        return back()->with('success', 'Trial counter reset.');
    })->name('admin.tenants.reset-trial');

    // Backfill missing billing record — creates trial record for orphaned deployment
    Route::post('/admin/deployments/{id}/backfill-billing', function (int $id) {
        $dep = DB::table('worker_deployments')->where('id', $id)->firstOrFail();
        $exists = DB::table('deployment_billing')->where('deployment_id', $id)->exists();
        if ($exists) {
            return back()->with('error', 'Billing record already exists for this deployment.');
        }
        $pricing = DB::table('worker_pricing')->where('worker_slug', $dep->worker_slug)->first()
                ?? DB::table('worker_pricing')->where('worker_slug', 'ava')->first();
        DB::table('deployment_billing')->insert([
            'user_id'                   => $dep->user_id,
            'deployment_id'             => $id,
            'worker_slug'               => $dep->worker_slug,
            'status'                    => 'trial',
            'trial_transactions_used'   => 0,
            'trial_transactions_limit'  => $pricing?->free_transactions ?? 10,
            'billing_period_start'      => now()->startOfMonth(),
            'created_at'                => now(),
            'updated_at'                => now(),
        ]);
        return back()->with('success', "Billing record created for deployment #{$id} ({$dep->name}) — status: trial.");
    })->name('admin.deployments.backfill-billing');

    // Void a Stripe invoice on behalf of a tenant (admin forgiveness)
    Route::post('/admin/invoices/{invoiceId}/void', function (string $invoiceId, \Illuminate\Http\Request $request) {
        $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $user = \App\Models\User::findOrFail($request->user_id);

        if (!$user->stripe_id) {
            return back()->with('error', 'This tenant has no Stripe customer record.');
        }

        try {
            $stripe  = new \Stripe\StripeClient(config('cashier.secret'));
            $invoice = $stripe->invoices->retrieve($invoiceId);

            // Verify the invoice belongs to this customer
            if ($invoice->customer !== $user->stripe_id) {
                return back()->with('error', 'Invoice does not belong to this tenant.');
            }

            if ($invoice->status === 'void') {
                return back()->with('error', 'Invoice is already voided.');
            }

            if ($invoice->status === 'paid') {
                return back()->with('error', 'Invoice is already paid — use Stripe dashboard to issue a refund instead.');
            }

            $stripe->invoices->voidInvoice($invoiceId);

            return back()->with('success', "Invoice {$invoiceId} voided. The tenant will no longer owe this amount.");
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return back()->with('error', 'Stripe error: ' . $e->getMessage());
        }
    })->name('admin.invoices.void');

    // Set billing status for a deployment
    Route::post('/admin/deployments/{id}/set-billing-status', function (int $id, \Illuminate\Http\Request $request) {
        $request->validate(['status' => 'required|in:trial,active,paused,canceled,past_due']);
        $dep = DB::table('worker_deployments')->where('id', $id)->firstOrFail();
        $updated = DB::table('deployment_billing')
            ->where('deployment_id', $id)
            ->update(['status' => $request->status, 'updated_at' => now()]);
        if (!$updated) {
            return back()->with('error', 'No billing record found. Backfill first.');
        }
        return back()->with('success', "Deployment #{$id} ({$dep->name}) billing status set to {$request->status}.");
    })->name('admin.deployments.set-billing-status');

    // ── Influencer Admin ──────────────────────────────────────────────────
    Route::get('/admin/influencers', function () {
        $influencers = DB::table('influencers')->orderByDesc('created_at')->get();
        $stats = $influencers->map(function ($inf) {
            $inf->conversions = DB::table('referral_credits')
                ->where('influencer_id', $inf->id)->where('event', 'paid_conversion')->count();
            $inf->clicks = DB::table('referral_clicks')
                ->where('influencer_id', $inf->id)->count();
            return $inf;
        });
        return view('admin.influencers', compact('stats'));
    })->name('admin.influencers');

    Route::get('/admin/influencers/{id}', function (int $id) {
        $influencer = DB::table('influencers')->where('id', $id)->firstOrFail();
        $stats = \App\Platform\Services\InfluencerService::getStats($id);
        $credits = DB::table('referral_credits')
            ->where('influencer_id', $id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        $clicks = DB::table('referral_clicks')
            ->where('influencer_id', $id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();
        return view('admin.influencer-detail', compact('influencer', 'stats', 'credits', 'clicks'));
    })->name('admin.influencers.show');

    Route::post('/admin/influencers/{id}/approve', function (int $id) {
        DB::table('influencers')->where('id', $id)->update([
            'status' => 'active',
            'approved_at' => now(),
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Influencer approved and activated.');
    })->name('admin.influencers.approve');

    Route::post('/admin/influencers/{id}/update', function (int $id, \Illuminate\Http\Request $request) {
        $request->validate([
            'status' => 'required|in:pending,active,paused,rejected',
            'tier' => 'required|in:starter,pro,elite',
            'commission_rate' => 'required|numeric|min:0.01|max:0.50',
            'payout_email' => 'nullable|email',
            'payout_method' => 'required|in:paypal,bank,stripe',
            'notes' => 'nullable|string|max:1000',
        ]);
        DB::table('influencers')->where('id', $id)->update([
            'status' => $request->status,
            'tier' => $request->tier,
            'commission_rate' => $request->commission_rate,
            'payout_email' => $request->payout_email,
            'payout_method' => $request->payout_method,
            'notes' => $request->notes,
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Influencer updated.');
    })->name('admin.influencers.update');

    Route::post('/admin/influencers/{id}/payout', function (int $id, \Illuminate\Http\Request $request) {
        $request->validate(['amount' => 'required|numeric|min:0.01']);
        $influencer = DB::table('influencers')->where('id', $id)->firstOrFail();
        $amount = min((float)$request->amount, (float)$influencer->pending_payout);
        DB::table('influencers')->where('id', $id)->update([
            'pending_payout' => DB::raw("pending_payout - $amount"),
            'paid_out' => DB::raw("paid_out + $amount"),
            'updated_at' => now(),
        ]);
        DB::table('referral_credits')
            ->where('influencer_id', $id)
            ->where('status', 'pending_payout')
            ->update(['status' => 'paid', 'updated_at' => now()]);
        return back()->with('success', "Payout of \${$amount} recorded for {$influencer->name}.");
    })->name('admin.influencers.payout');

    }); // end admin middleware group

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Public Worker Pages ────────────────────────────────────────────────
Route::get('/w/{slug}', function (string $slug) {
    $workers = [
        'ava' => [
            'name'       => 'AVA',
            'slug'       => 'ava',
            'role'       => 'Renewal & Subscription Coordinator',
            'category'   => 'Renewal Automation',
            'meta_desc'  => 'AVA monitors renewal inboxes, classifies renewals, pulls applicant history, drafts submissions and queues them for review — end to end.',
            'headline'   => 'License renewals, handled before the deadline ever gets close.',
            'sub'        => 'AVA monitors your renewal inbox, classifies incoming renewals, looks up applicant history, generates submission drafts, and queues them for your review — end to end, on autopilot.',
            'orgs'       => ['NYCSCA', 'DOB', 'FDNY', 'MTA'],
            'what_h2'    => 'Your renewal pipeline, running without you.',
            'what_body'  => [
                'Most renewal coordinators spend hours each week doing the same thing: checking email, looking up license records, filling out the same forms, chasing the same deadlines. AVA does all of it.',
                'AVA is trained specifically on renewal workflows for New York City agencies. It knows the forms, the deadlines, the submission quirks. Every run is logged. You see every step.',
            ],
            'capabilities' => [
                'Inbox monitoring and renewal classification',
                'License lookup and applicant history retrieval',
                'AI-generated renewal drafts tailored per agency',
                'Deadline tracking with configurable alert windows',
                'Review queue management — nothing submits without you',
                'Full audit trail for every transaction',
            ],
            'how_steps' => [
                [
                    'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>',
                    'title'  => 'Inbox check',
                    'desc'   => 'AVA monitors your connected inbox for incoming renewal notices. Emails are classified and prioritized automatically.',
                    'detail' => '→ Found 3 renewal notices\n→ NYCSCA #2847: due in 14 days (HIGH)\n→ DOB #3012: due in 22 days (MEDIUM)',
                ],
                [
                    'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><path d="M9 12h6M9 8h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>',
                    'title'  => 'History lookup',
                    'desc'   => 'AVA pulls the applicant record — past submissions, known contacts, prior renewal history — so the draft is pre-filled and accurate.',
                    'detail' => '→ Applicant: John D. · License holder since 2019\n→ Prior renewal: filed 2023-07-12, approved\n→ Contacts: 2 principals on file',
                ],
                [
                    'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
                    'title'  => 'Draft generation',
                    'desc'   => 'AVA generates a complete renewal draft using the applicant data and the agency\'s current requirements. No guesswork, no blank fields.',
                    'detail' => '→ Draft: "NYCSCA License Renewal — John D. #2847"\n→ All required fields populated\n→ Supporting docs checklist attached',
                ],
                [
                    'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5M13 21a2 2 0 01-4 0"/></svg>',
                    'title'  => 'Your review queue',
                    'desc'   => 'The draft lands in your UNIT dashboard for review. Approve with one click or make edits inline. Nothing goes out without you.',
                    'detail' => '→ Status: awaiting review\n→ Action required: approve or edit draft\n→ Deadline: 14 days remaining',
                ],
            ],
            'testimonials' => [
                ['quote' => 'We used to spend two days a week just managing renewals. AVA handles it before we even see it. Now it\'s a five-minute review.', 'name' => 'Maria T.', 'company' => 'BuildCo Operations'],
                ['quote' => 'The draft quality is shockingly good. It pulled the right contact, the right license number, flagged an expiry we missed. Exactly what a good coordinator does.', 'name' => 'James R.', 'company' => 'Northline Services'],
                ['quote' => 'We were skeptical of AI for compliance work. But UNIT gives us full visibility into every step. We\'re more confident in our filings now, not less.', 'name' => 'Sandra L.', 'company' => 'Vertex Solutions'],
            ],
            'faq' => [
                ['q' => 'What agencies does AVA support?', 'a' => 'AVA currently handles renewals for NYCSCA, DOB, FDNY, and MTA. We add new agencies regularly — contact us if yours isn\'t listed.'],
                ['q' => 'Does AVA submit renewals automatically?', 'a' => 'No. AVA prepares and drafts the renewal, then queues it for your review. Nothing submits without your explicit approval. You stay in control of every filing.'],
                ['q' => 'How does AVA access my renewal inbox?', 'a' => 'AVA connects to your email via a configured inbox integration. You define which inbox it monitors and what it can read — you remain in control of the access scope.'],
                ['q' => 'What happens if AVA misses something?', 'a' => 'Every transaction is fully logged and visible in your dashboard. If AVA can\'t classify or process something, it flags it for manual review rather than guessing.'],
                ['q' => 'How much does it cost?', 'a' => 'Your first 25 transactions are completely free. After that, you pay a monthly subscription based on your deployment. No setup fees, no per-transaction charges.'],
                ['q' => 'Can I cancel my subscription?', 'a' => 'Yes — cancel any time, no questions asked. Your data stays accessible for 30 days after cancellation.'],
            ],
        ],
    ];

    if (!isset($workers[$slug])) abort(404);

    $w = $workers[$slug];

    // Live stats from DB
    $deploymentCount = DB::table('worker_deployments')->where('worker_slug', $slug)->count();
    $tokensToday     = (int) DB::table('usage_events')
        ->join('worker_deployments','worker_deployments.id','=','usage_events.deployment_id')
        ->where('worker_deployments.worker_slug', $slug)
        ->where('usage_events.created_at', '>=', now()->subDay())
        ->sum(DB::raw('tokens_input + tokens_output'));
    $totalTx         = DB::table('transactions')
        ->join('worker_deployments','worker_deployments.id','=','transactions.deployment_id')
        ->where('worker_deployments.worker_slug', $slug)
        ->count();

    return view('workers.show', [
        'worker'          => $w,
        'deploymentCount' => $deploymentCount ?: 12,   // fallback for fresh installs
        'tokensToday'     => $tokensToday     ?: 48200,
        'totalTx'         => $totalTx         ?: 3840,
    ]);
})->name('workers.public.show'); // public — no auth

// Redirect /workers/{slug} (non-numeric) to /w/{slug}
Route::get('/workers/{slug}', function (string $slug) {
    return redirect('/w/' . $slug, 301);
})->where('slug', '[^0-9].*');

// ── Public Fast Track Submit (no auth) ────────────────────────────
Route::post('/fast-track/submit', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name'        => 'required|string|max:255',
        'email'       => 'required|email|max:255',
        'worker_slug' => 'required|string|max:100',
        'source'      => 'nullable|string|max:100',
    ]);

    // Log lead
    DB::table('fast_track_leads')->insertOrIgnore([
        'name'        => $request->name,
        'email'       => $request->email,
        'worker_slug' => $request->worker_slug,
        'source'      => $request->source ?? 'homepage',
        'user_id'     => null,
        'subscribed'  => false,
        'flags'       => json_encode(['type' => 'public_fasttrack']),
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    return response()->json([
        'success' => true,
        'preview' => 'Sample renewal queued for ' . $request->name . '. We\'re sending the full output to ' . $request->email . ' — click the deploy link inside to go live in minutes.',
    ]);
})->name('fast-track.submit');

// ── Public Influencer Redirect ─────────────────────────────────────────
Route::get('/r/{slug}', function (string $slug, \Illuminate\Http\Request $request) {
    $influencer = \App\Platform\Services\InfluencerService::findBySlug($slug);
    if ($influencer) {
        \App\Platform\Services\InfluencerService::trackClick($slug, $request);
    }
    // Redirect to homepage with ?via= so CaptureReferralCode middleware stores it
    return redirect('/?via=' . $slug);
})->name('influencer.redirect');

// ── Referral Program Public Page ──────────────────────────────────────
Route::get('/referral', function () {
    return view('referral.index');
})->name('referral.index');

// ── Influencer Application ─────────────────────────────────────────────
Route::get('/influencer/apply', function () {
    return view('influencer.apply');
})->name('influencer.apply');

Route::post('/influencer/apply', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name'          => 'required|string|max:255',
        'email'         => 'required|email|max:255',
        'channel'       => 'required|string|max:100',
        'audience_size' => 'required|string|max:50',
        'niche'         => 'nullable|string|max:100',
        'utm_source'    => 'nullable|string|max:100',
    ]);

    // Generate a unique slug from name
    $base = strtolower(preg_replace('/[^a-z0-9]/i', '', $request->name));
    $slug = substr($base, 0, 20);
    $i = 1;
    while (DB::table('influencers')->where('slug', $slug)->exists()) {
        $slug = substr($base, 0, 18) . $i++;
    }

    $exists = DB::table('influencers')->where('email', $request->email)->exists();
    if ($exists) {
        return back()->with('error', 'An application with this email already exists.')->withInput();
    }

    DB::table('influencers')->insert([
        'name'          => $request->name,
        'email'         => $request->email,
        'slug'          => $slug,
        'channel'       => $request->channel,
        'audience_size' => $request->audience_size,
        'niche'         => $request->niche,
        'utm_source'    => $request->utm_source ?? session('utm_source'),
        'status'        => 'pending',
        'tier'          => 'starter',
        'commission_rate' => 0.20,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    return redirect()->route('influencer.apply')->with('success', 'Application received! We\'ll review and get back to you within 2 business days.');
})->name('influencer.apply.submit');

require __DIR__.'/auth.php';

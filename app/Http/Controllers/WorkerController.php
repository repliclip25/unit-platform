<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Platform\Services\Gmail\GmailWatchService;
use App\Platform\Services\TransactionService;
use App\Platform\Services\UnitNotifier;
use App\Platform\Services\WorkerRegistry;

class WorkerController extends Controller
{
    public function index()
    {
        $deployments = DB::table('worker_deployments')
            ->where('user_id', auth()->id())
            ->whereIn('status', ['active', 'paused'])
            ->orderBy('created_at')
            ->get();
        $credentials = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->get();

        // Catalog: only workers whose slug is registered in WorkerRegistry (source of truth)
        $registeredSlugs = \App\Platform\Services\WorkerRegistry::slugs();
        $catalog = DB::table('workers')
            ->whereIn('slug', $registeredSlugs)
            ->whereIn('status', ['running', 'configured', 'connected'])
            ->get();

        // Pull registry rows for images and visual identity
        $registryRows = DB::table('worker_registry')
            ->whereIn('slug', $registeredSlugs)
            ->get()
            ->keyBy('slug');

        // Resolve contracts for workers that are active or testing (not decommissioned)
        $contracts = collect(\App\Platform\Services\WorkerRegistry::all())
            ->filter(fn($c) => !\App\Platform\Services\WorkerRegistry::isDecommissioned($c->identity()['slug']))
            ->keyBy(fn($c) => $c->identity()['slug']);

        // Count existing deployments per slug for the tenant
        $deploymentCounts = $deployments->groupBy('worker_slug')
            ->map(fn($group) => $group->count());

        return view('dashboard.workers', compact('deployments', 'credentials', 'catalog', 'contracts', 'deploymentCounts', 'registryRows'));
    }

    public function show(string $slug)
    {
        $dep = DB::table('worker_deployments')
            ->where('user_id', auth()->id())
            ->where(fn($q) => $q->where('worker_slug', $slug)->when(is_numeric($slug), fn($q2) => $q2->orWhere('id', (int)$slug)))
            ->firstOrFail();
        $id               = $dep->id;
        $contract         = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
        $credential       = $dep->credential_id ? DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first() : null;
        $credentials      = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->get();
        $connectedInboxes = DB::table('deployment_credentials')
            ->join('user_gmail_credentials', 'user_gmail_credentials.id', '=', 'deployment_credentials.credential_id')
            ->where('deployment_credentials.deployment_id', $id)
            ->select('user_gmail_credentials.*', 'deployment_credentials.is_primary', 'deployment_credentials.id as pivot_id')
            ->get();
        $availableCredentials = $credentials->filter(fn($c) => !$connectedInboxes->contains('id', $c->id))->values();
        $txCount          = DB::table('transactions')->where('deployment_id', $id)->count();
        $recentTx         = DB::table('transactions')->where('deployment_id', $id)->orderByDesc('created_at')->limit(5)->get();
        $usage            = DB::table('usage_events')->where('deployment_id', $id)->selectRaw('SUM(tokens_input+tokens_output) as tokens, SUM(cost_usd) as cost')->first();
        $pendingReview    = DB::table('transactions')->where('deployment_id', $id)->where('status', 'draft_ready')->whereNull('human_decision')->count();
        $stuckCount       = DB::table('transactions')->where('deployment_id', $id)->whereNotIn('status', ['draft_ready','approved','sent','failed'])->where('updated_at', '<', now()->subMinutes(5))->count();
        $customModels     = DB::table('tenant_custom_models')->where('user_id', auth()->id())->where('active', true)->get();
        $policyViolations = \App\Platform\Services\PolicyEngine::evaluate(auth()->id(), $id);
        $registryRow      = DB::table('worker_registry')->where('slug', $dep->worker_slug)->first();

        // ── Contract-driven production readiness ──────────────────────────────
        $credDef = $contract ? $contract->credential() : [];
        $isMultiCredential = isset($credDef[0]); // indexed = multi-slot worker (e.g. NUX)

        if ($isMultiCredential) {
            // Multi-slot workers: check nux_oauth_tokens (or equivalent) for connected slots
            $connectedOauthPlatforms = DB::table('nux_oauth_tokens')
                ->where('user_id', auth()->id())
                ->where('deployment_id', $id)
                ->where('active', true)
                ->pluck('platform')
                ->toArray();
            $sourceSlots = collect($credDef)->filter(fn($s) => ($s['key'] ?? '') !== 'inbox');
            $productionReady = $sourceSlots->every(fn($s) => in_array($s['key'], $connectedOauthPlatforms))
                               || $sourceSlots->contains(fn($s) => in_array($s['key'], $connectedOauthPlatforms));
            $productionReadiness = [
                'ready'           => $productionReady,
                'banner_title'    => 'Not production ready — no social accounts connected',
                'banner_body'     => 'Connect at least one source account (LinkedIn or X) via the Connect tab before this worker can monitor your feed.',
                'connect_label'   => 'Connect Account →',
                'fast_track_desc' => 'Run a sample social post through the full pipeline — draft lands in your inbox',
                'no_credential_msg' => 'Connect a social account to run Fast Track',
                'connected_accounts' => $connectedOauthPlatforms,
            ];
        } else {
            // Single-slot workers (AVA): use Gmail inbox connections
            $productionReadiness = [
                'ready'           => $connectedInboxes->isNotEmpty(),
                'banner_title'    => 'Not production ready — no inbox connected',
                'banner_body'     => 'This worker has no Gmail inbox connected via the Connect tab. Real emails will not be received or processed until you connect an inbox.',
                'connect_label'   => 'Connect Inbox →',
                'fast_track_desc' => 'Run a sample email through the full pipeline — draft lands in your Drafts folder',
                'no_credential_msg' => 'Connect an inbox to run Fast Track',
                'connected_accounts' => [],
            ];
        }

        $overviewPanels = [];
        $overviewMeta   = [];
        if ($contract && method_exists($contract, 'overview')) {
            $resolved       = \App\Platform\Services\DashboardService::resolve($dep, $contract->overview(), $contract);
            $overviewPanels = $resolved['panels'] ?? [];
            $overviewMeta   = $resolved['meta']   ?? [];
        }

        return view('dashboard.worker-detail', compact(
            'dep', 'contract', 'credential', 'credentials', 'connectedInboxes', 'availableCredentials',
            'txCount', 'recentTx', 'usage', 'pendingReview', 'stuckCount',
            'customModels', 'policyViolations', 'registryRow',
            'isMultiCredential', 'productionReadiness', 'overviewPanels', 'overviewMeta'
        ));
    }

    public function store(Request $request)
    {
        if (auth()->user()->blocked_at) {
            return back()->with('error', 'Your account is suspended. Contact support before deploying workers.');
        }
        $request->validate(['worker_slug' => 'required', 'name' => 'required']);

        $slug          = $request->worker_slug;

        if (\App\Platform\Services\WorkerRegistry::isDecommissioned($slug)) {
            return back()->with('error', 'This worker has been decommissioned and is no longer accepting new deployments.');
        }

        $contract      = \App\Platform\Services\WorkerRegistry::resolve($slug);
        if (!$contract) {
            return back()->with('error', 'Worker not found. Please refresh and try again.');
        }
        $inst          = $contract->instances();
        $existingCount = DB::table('worker_deployments')
                           ->where('user_id', auth()->id())
                           ->where('worker_slug', $slug)
                           ->whereNull('deleted_at')
                           ->whereNotIn('status', ['decommissioned', 'removed'])
                           ->count();

        // Hard fixed max (e.g. max: 1 with multiple: false)
        if (isset($inst['max']) && $inst['max'] !== null && $existingCount >= $inst['max']) {
            return back()->with('error', "You've reached the maximum deployments for this worker.");
        }

        // One deployment per inbox: block if this specific credential already has a live deployment
        if (($inst['limit_by'] ?? null) === 'gmail_credentials' && $request->credential_id) {
            $inboxTaken = DB::table('worker_deployments')
                ->where('user_id', auth()->id())
                ->where('worker_slug', $slug)
                ->where('credential_id', $request->credential_id)
                ->whereNull('deleted_at')
                ->whereNotIn('status', ['decommissioned', 'removed', 'paused'])
                ->exists();
            if ($inboxTaken) {
                $inboxEmail = DB::table('user_gmail_credentials')->where('id', $request->credential_id)->value('gmail_address');
                return back()->with('error', "That inbox ({$inboxEmail}) already has an active AVA deployment. Connect a different Gmail account to add another instance.");
            }
        }

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

        // Capture first worker touchpoint for segmented messaging
        $user = auth()->user();
        try {
            if (!$user->first_worker_slug) {
                DB::table('users')->where('id', $user->id)->update([
                    'first_worker_slug' => $request->worker_slug,
                    'first_worker_at'   => now(),
                ]);
            }
        } catch (\Throwable) {
            // Column may not exist on older production schema — non-fatal
        }

        // Copy platform default rules for this worker slug into the new deployment
        try {
            $platformRules = DB::table('ava_rules')
                ->whereNull('user_id')
                ->whereNull('deployment_id')
                ->get();

            foreach ($platformRules as $rule) {
                $alreadyCopied = DB::table('ava_rules')
                    ->where('user_id', auth()->id())
                    ->where('deployment_id', $depId)
                    ->where('rule_id', $rule->rule_id)
                    ->exists();
                if ($alreadyCopied) continue;

                DB::table('ava_rules')->insert([
                    'user_id'           => auth()->id(),
                    'deployment_id'     => $depId,
                    'rule_id'           => $rule->rule_id,
                    'condition'         => $rule->condition,
                    'priority'          => $rule->priority,
                    'action'            => $rule->action,
                    'approval_required' => $rule->approval_required ?? false,
                    'notes'             => $rule->notes,
                    'active'            => true,
                    'is_platform'       => true,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Worker deploy: rules copy failed', ['dep_id' => $depId, 'error' => $e->getMessage()]);
        }

        // Auto-start Gmail watch if a credential was linked at deploy time
        if ($request->worker_slug === 'ava' && $request->credential_id) {
            $credential = DB::table('user_gmail_credentials')->where('id', $request->credential_id)->first();
            if ($credential) {
                try {
                    $watchService = app(GmailWatchService::class, ['credential' => $credential]);
                    $watchService->watch(config('services.gmail.pubsub_topic'));
                    Log::info('AVA Gmail watch auto-started at deploy', ['dep_id' => $depId, 'gmail' => $credential->gmail_address]);
                } catch (\Throwable $e) {
                    Log::error('AVA Gmail watch auto-start failed at deploy', ['error' => $e->getMessage()]);
                }
            }
        }

        // Create billing record
        $pricing    = DB::table('worker_pricing')->where('worker_slug', $request->worker_slug)->first();
        $trialGated = false;
        try {
            $trialGated = DB::table('platform_configs')->where('key', 'trial_payment_required')->value('value') === 'true';
            $trialDays  = (int) (DB::table('platform_configs')->where('key', 'trial_days')->value('value') ?? 14);

            DB::table('deployment_billing')->insert([
                'user_id'                  => auth()->id(),
                'deployment_id'            => $depId,
                'worker_slug'              => $request->worker_slug,
                'status'                   => 'trial',
                'trial_transactions_used'  => 0,
                'trial_transactions_limit' => $pricing?->free_transactions ?? 10,
                'trial_ends_at'            => now()->addDays($trialDays),
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Worker deploy: billing insert failed', ['dep_id' => $depId, 'error' => $e->getMessage()]);
        }

        try {
            UnitNotifier::workerDeployed($depId);
        } catch (\Throwable $e) {
            Log::error('Worker deploy: notifier failed', ['dep_id' => $depId, 'error' => $e->getMessage()]);
        }

        // If trial gate is on, send tenant to billing to pick a plan (card collected there, trial days applied)
        if ($trialGated) {
            return redirect()->route('billing', ['pick' => $depId])
                ->with('info', 'Your trial starts free. Choose a plan — your card will not be charged until the trial ends.');
        }

        return redirect()->route('workers.show', $request->worker_slug)->with('success', 'Worker deployed. AVA is now monitoring.');
    }

    public function destroy(int $id)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->delete();
        return redirect()->route('workers.deploy')->with('success', 'Worker removed.');
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate(['status' => 'required|in:active,paused,stopped']);
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->update([
            'status'     => $request->status,
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Worker status updated.');
    }

    public function connect(string $slug)
    {
        $dep = DB::table('worker_deployments')->where('user_id', auth()->id())
            ->where(fn($q) => $q->where('worker_slug', $slug)->when(is_numeric($slug), fn($q2) => $q2->orWhere('id', (int)$slug)))
            ->firstOrFail();
        $id          = $dep->id;
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
    }

    public function connectInbox(int $id, Request $request)
    {
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
    }

    public function disconnectInbox(int $id, int $pivotId)
    {
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
    }

    public function rewatchInbox(int $id, int $credentialId)
    {
        $deployment = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $credential = DB::table('user_gmail_credentials')->where('id', $credentialId)->where('user_id', auth()->id())->firstOrFail();

        try {
            $watchService = app(GmailWatchService::class, ['credential' => $credential]);
            $result = $watchService->watch(config('services.gmail.pubsub_topic'));

            DB::table('user_gmail_credentials')->where('id', $credentialId)->update([
                'watch_active'     => true,
                'watch_expires_at' => date('Y-m-d H:i:s', $result['expiration'] / 1000),
                'updated_at'       => now(),
            ]);
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'invalid_grant')) {
                DB::table('user_gmail_credentials')->where('id', $credentialId)->delete();
                DB::table('deployment_credentials')->where('credential_id', $credentialId)->delete();
                return redirect()->route('ava.gmail.authorize')
                    ->with('error', 'Gmail session expired for ' . $credential->gmail_address . '. Please reconnect.');
            }
            return back()->with('error', 'Watch renewal failed: ' . $e->getMessage());
        }

        return redirect()->route('workers.connect', $deployment->worker_slug)->with('success', "Watch renewed for {$credential->gmail_address}.");
    }

    public function configure(string $slug)
    {
        $dep = DB::table('worker_deployments')->where('user_id', auth()->id())
            ->where(fn($q) => $q->where('worker_slug', $slug)->when(is_numeric($slug), fn($q2) => $q2->orWhere('id', (int)$slug)))
            ->firstOrFail();
        $id           = $dep->id;
        $contract     = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
        $credentials  = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->get();
        $customModels = DB::table('tenant_custom_models')->where('user_id', auth()->id())->where('active', true)->get();

        // Prompt overrides — keyed by stage_key
        $overrideRows = DB::table('deployment_prompt_overrides')
            ->where('deployment_id', $dep->id)
            ->get()
            ->keyBy('stage_key');

        // Pipeline stages + default prompts from contract (keyed by stage key)
        $pipelineStages  = $contract ? $contract->pipelineStages() : [];
        $defaultPrompts  = collect($contract ? $contract->prompts() : [])
            ->keyBy('stage');

        // Most recent transaction for prompt test
        $lastTx = DB::table('transactions')
            ->where('deployment_id', $dep->id)
            ->whereNotNull('raw_input')
            ->orderByDesc('created_at')
            ->first();

        return view('dashboard.worker-configure', compact(
            'dep', 'contract', 'credentials', 'customModels',
            'overrideRows', 'pipelineStages', 'defaultPrompts', 'lastTx'
        ));
    }

    public function updateConfig(Request $request, int $id)
    {
        // Accept newline-separated (textarea) or comma-separated (legacy)
        $splitTrim = function(string $raw): array {
            $sep = str_contains($raw, "\n") ? "\n" : ",";
            return array_values(array_filter(array_map('trim', explode($sep, $raw))));
        };

        $dep    = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $existing = json_decode($dep->config ?? '{}', true) ?: [];

        $config = array_merge($existing, [
            'capture' => [
                'capture_scope'       => $request->capture_scope ?? 'All incoming emails',
                'capture_keywords'    => $splitTrim($request->capture_keywords ?? ''),
                'capture_domains'     => $splitTrim($request->capture_domains ?? ''),
                'capture_senders_only'=> $splitTrim($request->capture_senders_only ?? ''),
                'exclude_senders'     => $splitTrim($request->exclude_senders ?? ''),
                'capture_require_all' => (bool) $request->capture_require_all,
            ],
            'ai_model'     => $request->ai_model ?: ($existing['ai_model'] ?? 'claude-sonnet-4-6'),
            'summary_hour' => (int) ($request->summary_hour ?? $existing['summary_hour'] ?? 8),
        ]);

        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->update([
            'name'          => $request->name,
            'credential_id' => $request->credential_id ?: null,
            'config'        => json_encode($config),
            'updated_at'    => now(),
        ]);
        return back()->with('success', 'Worker configuration saved.');
    }

    public function updateModel(Request $request, int $id)
    {
        $dep    = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $config = json_decode($dep->config ?? '{}', true) ?: [];
        $config['ai_model'] = $request->ai_model ?: 'claude-sonnet-4-6';
        DB::table('worker_deployments')->where('id', $id)->update([
            'config'     => json_encode($config),
            'updated_at' => now(),
        ]);
        return back()->with('success', 'AI model updated.');
    }

    public function testPrompt(Request $request, int $id)
    {
        $userId = auth()->id();
        $dep    = DB::table('worker_deployments')->where('id', $id)->where('user_id', $userId)->firstOrFail();

        // ── Billing gate ──────────────────────────────────────────────────────
        $billing = DB::table('deployment_billing')->where('deployment_id', $dep->id)->first();
        $isTrial = !$billing || $billing->status === 'trial';

        if ($isTrial) {
            $used  = (int)($billing?->prompt_test_uses  ?? 0);
            $limit = (int)($billing?->prompt_test_limit ?? 5);
            if ($used >= $limit) {
                return response()->json([
                    'error'     => "You've used all {$limit} free prompt tests for this deployment.",
                    'gate'      => 'PROMPT_TEST_EXHAUSTED',
                    'used'      => $used,
                    'limit'     => $limit,
                    'subscribe' => route('billing'),
                ], 402);
            }
        }

        // Hard-blocked accounts can't run anything
        $user = DB::table('users')->where('id', $userId)->first();
        if ($user?->blocked_at) {
            return response()->json(['error' => 'Your account is suspended. Contact support.'], 403);
        }

        $stageKey = $request->input('stage_key');
        $system   = trim($request->input('system', ''));
        $user_    = trim($request->input('user',   ''));
        $model    = $dep->config ? (json_decode($dep->config, true)['ai_model'] ?? 'claude-sonnet-4-6') : 'claude-sonnet-4-6';

        if (!$system && !$user_) {
            return response()->json(['error' => 'Enter at least a system or user prompt to test.'], 422);
        }

        // Pull most recent transaction for context
        $lastTx     = DB::table('transactions')
            ->where('deployment_id', $dep->id)
            ->whereNotNull('raw_input')
            ->orderByDesc('created_at')
            ->first();

        $rawInput    = json_decode($lastTx?->raw_input     ?? '{}', true) ?: [];
        $readOutput  = json_decode($lastTx?->read_output   ?? '{}', true) ?: [];
        $classifyOut = json_decode($lastTx?->classify_output ?? '{}', true) ?: [];
        $memoryOut   = json_decode($lastTx?->memory_output  ?? '{}', true) ?: [];
        $rawEmail    = $rawInput['raw_email'] ?? '[No email found — send a real email to your inbox first]';

        // Pull the template used on the last transaction (for draft stage placeholders)
        $templateOut = json_decode($lastTx?->template_output ?? '{}', true) ?: [];
        $templateRow = null;
        if (!empty($templateOut['template_id'])) {
            $templateRow = DB::table('email_templates')->where('id', $templateOut['template_id'])->first();
        }
        if (!$templateRow) {
            $templateRow = DB::table('email_templates')
                ->where('user_id', $userId)
                ->where('worker_slug', $dep->worker_slug)
                ->where('active', 1)
                ->orderByDesc('is_default')
                ->first();
        }
        $template = [
            'template_name'     => $templateRow?->name            ?? 'Professional',
            'tone'              => $templateRow?->tone             ?? 'Formal',
            'body_template'     => $templateRow?->body_template    ?? 'Dear {{contact_first_name}}, ...',
            'approval_required' => $templateRow?->approval_required ?? 'No',
        ];
        $firstName = $memoryOut['primary_contact_name'] ?? $rawInput['from_name'] ?? 'there';

        // Substitute all known placeholders — standard + draft-specific
        $userPrompt = str_replace(
            ['{RAW_EMAIL}', '{READ_OUTPUT}', '{MEMORY_TABLES}',
             '{FIRST_NAME}', '{ASSET}', '{CLIENT}', '{DUE_DATE}', '{CATEGORY}',
             '{APPROVAL_REQUIRED}', '{SENDER_NAME}', '{TEMPLATE_NAME}', '{TONE}', '{BODY_TEMPLATE}'],
            [$rawEmail, json_encode($readOutput, JSON_PRETTY_PRINT), json_encode($memoryOut, JSON_PRETTY_PRINT),
             $firstName, $memoryOut['asset'] ?? '(asset)', $memoryOut['matched_client'] ?? '(client)',
             $readOutput['due_date_or_deadline'] ?? '(due date)', $classifyOut['category'] ?? '(category)',
             $template['approval_required'], 'Franklin', $template['template_name'], $template['tone'], $template['body_template']],
            $user_
        );
        $systemPrompt = $system ?: 'You are a helpful assistant.';

        try {
            $claude = app(\App\Platform\Services\ClaudeService::class);
            $claude->configure($model, $userId, $dep->worker_slug);

            // Pass a synthetic txId that carries deployment context for usage logging.
            // ClaudeService resolves deployment_id via transactions table, so we pass
            // the real lastTx txId if available; otherwise usage is logged user-only.
            $txId  = $lastTx?->tx_id ?? null;
            $output = $claude->ask($systemPrompt, $userPrompt, 1024, $txId, $stageKey . '_test');

            // If txId was null, log usage manually so it's always attributed to the deployment
            if (!$txId) {
                DB::table('usage_events')->insert([
                    'user_id'       => $userId,
                    'deployment_id' => $dep->id,
                    'worker_slug'   => $dep->worker_slug,
                    'tx_id'         => null,
                    'stage'         => $stageKey . '_test',
                    'tokens_input'  => 0,
                    'tokens_output' => 0,
                    'cost_usd'      => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // Increment trial counter
            if ($isTrial && $billing) {
                DB::table('deployment_billing')
                    ->where('deployment_id', $dep->id)
                    ->increment('prompt_test_uses');
            }

            $remaining = $isTrial ? max(0, (int)($billing?->prompt_test_limit ?? 5) - (int)($billing?->prompt_test_uses ?? 0) - 1) : null;

            return response()->json([
                'success'   => true,
                'output'    => $output,
                'tx_used'   => $lastTx ? [
                    'tx_id'   => $lastTx->tx_id,
                    'from'    => $rawInput['from']    ?? '?',
                    'subject' => $rawInput['subject'] ?? '?',
                ] : null,
                'trial'     => $isTrial ? ['used' => ($billing?->prompt_test_uses ?? 0) + 1, 'limit' => $billing?->prompt_test_limit ?? 5, 'remaining' => $remaining] : null,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function savePromptOverride(Request $request, int $id)
    {
        $dep = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        foreach ($request->input('stages', []) as $stageKey => $prompts) {
            $system = trim($prompts['system'] ?? '');
            $user   = trim($prompts['user']   ?? '');

            $existing = DB::table('deployment_prompt_overrides')
                ->where('deployment_id', $dep->id)
                ->where('stage_key', $stageKey)
                ->first();

            if (!$system && !$user) {
                // Empty = delete override (revert to default)
                if ($existing) {
                    DB::table('deployment_prompt_overrides')
                        ->where('deployment_id', $dep->id)
                        ->where('stage_key', $stageKey)
                        ->delete();
                }
                continue;
            }

            $data = [
                'system_prompt' => $system ?: null,
                'user_prompt'   => $user   ?: null,
                'worker_slug'   => $dep->worker_slug,
                'created_by'    => auth()->id(),
                'updated_at'    => now(),
            ];

            if ($existing) {
                DB::table('deployment_prompt_overrides')
                    ->where('deployment_id', $dep->id)
                    ->where('stage_key', $stageKey)
                    ->update($data);
            } else {
                DB::table('deployment_prompt_overrides')->insert(array_merge($data, [
                    'deployment_id' => $dep->id,
                    'stage_key'     => $stageKey,
                    'created_at'    => now(),
                ]));
            }
        }

        return back()->with('success', 'Prompt overrides saved.');
    }

    public function log(string $slug)
    {
        $dep = DB::table('worker_deployments')->where('user_id', auth()->id())
            ->where(fn($q) => $q->where('worker_slug', $slug)->when(is_numeric($slug), fn($q2) => $q2->orWhere('id', (int)$slug)))
            ->firstOrFail();
        $id      = $dep->id;
        $entries = DB::table('renewal_register')->where('deployment_id', $id)->orderByDesc('created_at')->paginate(25);
        return view('dashboard.worker-log', compact('dep', 'entries'));
    }

    public function schema(string $slug)
    {
        $dep = DB::table('worker_deployments')->where('user_id', auth()->id())
            ->where(fn($q) => $q->where('worker_slug', $slug)->when(is_numeric($slug), fn($q2) => $q2->orWhere('id', (int)$slug)))
            ->firstOrFail();
        $id       = $dep->id;
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
            'output'   => $contract->output(),
            'prompts'  => $contract->prompts(),
            'owner'    => $contract->owner(),
        ]);
    }

    public function observe(string $slug)
    {
        $dep = DB::table('worker_deployments')->where('user_id', auth()->id())
            ->where(fn($q) => $q->where('worker_slug', $slug)->when(is_numeric($slug), fn($q2) => $q2->orWhere('id', (int)$slug)))
            ->firstOrFail();
        $id = $dep->id;

        $days = (int) request('days', 7);
        $days = in_array($days, [1, 7, 14, 30]) ? $days : 7;
        $from = now()->subDays($days - 1)->startOfDay();

        // ── Pub/Sub hits per day ──────────────────────────────────────────────
        $pubsubHits = DB::table('platform_events')
            ->where('type', 'gmail.pubsub.hit')
            ->where('created_at', '>=', $from)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.gmail_address')) IN (
                SELECT ugc.gmail_address FROM user_gmail_credentials ugc
                JOIN deployment_credentials dc ON dc.credential_id = ugc.id
                WHERE dc.deployment_id = ?
            )", [$id])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as hits')
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');

        // ── Transaction funnel ────────────────────────────────────────────────
        $funnel = DB::table('transactions')
            ->where('deployment_id', $id)
            ->where('created_at', '>=', $from)
            ->selectRaw("
                COUNT(*) as total,
                SUM(status = 'filtered_out') as filtered_out,
                SUM(status = 'dismissed') as dismissed,
                SUM(status IN ('draft_ready','approved','sent')) as completed,
                SUM(status = 'failed') as failed,
                SUM(status = 'blocked') as blocked
            ")->first();

        // ── Transactions per day ──────────────────────────────────────────────
        $txPerDay = DB::table('transactions')
            ->where('deployment_id', $id)
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total,
                SUM(status = "filtered_out") as filtered,
                SUM(status IN ("draft_ready","approved","sent")) as completed')
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');

        // ── AI spend per stage ────────────────────────────────────────────────
        $stageSpend = DB::table('usage_events')
            ->where('deployment_id', $id)
            ->where('created_at', '>=', $from)
            ->whereNotNull('stage')
            ->groupBy('stage')
            ->selectRaw('stage, COUNT(*) as calls, SUM(cost_usd) as cost, SUM(tokens_input+tokens_output) as tokens')
            ->orderByRaw('SUM(cost_usd) DESC')
            ->get();

        // ── Average transaction duration ──────────────────────────────────────
        $avgDuration = DB::table('transactions')
            ->where('deployment_id', $id)
            ->where('created_at', '>=', $from)
            ->whereIn('status', ['draft_ready', 'approved', 'sent', 'failed'])
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_seconds')
            ->value('avg_seconds');

        // ── Per-inbox hit breakdown ───────────────────────────────────────────
        $inboxBreakdown = DB::table('transactions')
            ->where('deployment_id', $id)
            ->where('created_at', '>=', $from)
            ->selectRaw('COUNT(*) as total,
                SUM(status = "filtered_out") as filtered,
                SUM(status IN ("draft_ready","approved","sent")) as completed')
            ->first();

        // ── Connected inboxes ─────────────────────────────────────────────────
        $inboxes = DB::table('deployment_credentials')
            ->join('user_gmail_credentials', 'user_gmail_credentials.id', '=', 'deployment_credentials.credential_id')
            ->where('deployment_credentials.deployment_id', $id)
            ->select('user_gmail_credentials.gmail_address')
            ->get();

        // Build day-by-day chart data for last N days
        $chartDays = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $chartDays->push([
                'day'       => $day,
                'label'     => now()->subDays($i)->format('M d'),
                'hits'      => $pubsubHits->get($day)?->hits ?? 0,
                'total'     => $txPerDay->get($day)?->total ?? 0,
                'filtered'  => $txPerDay->get($day)?->filtered ?? 0,
                'completed' => $txPerDay->get($day)?->completed ?? 0,
            ]);
        }

        return view('dashboard.worker-observe', compact(
            'dep', 'days', 'funnel', 'stageSpend',
            'avgDuration', 'chartDays', 'inboxes'
        ));
    }

    public function billing(string $slug)
    {
        $dep = DB::table('worker_deployments')->where('user_id', auth()->id())
            ->where(fn($q) => $q->where('worker_slug', $slug)->when(is_numeric($slug), fn($q2) => $q2->orWhere('id', (int)$slug)))
            ->firstOrFail();
        $id       = $dep->id;
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
    }

    public function fastTrack(int $id, Request $request)
    {
        if (auth()->user()->blocked_at) {
            return back()->with('error', 'Your account is suspended. Fast Track is disabled.');
        }

        $dep = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if ($dep->status === 'paused') {
            return back()->with('error', 'Worker is paused. Resume it before running Fast Track.');
        }
        if ($dep->status === 'stopped') {
            return back()->with('error', 'Worker is stopped. Re-activate it before running Fast Track.');
        }
        if (in_array($dep->status, ['decommissioned', 'removed'])) {
            return back()->with('error', 'This worker has been decommissioned and cannot process transactions.');
        }

        // Testing mode gate — only testing_access users and admins may run Fast Track on testing workers
        $workerStatus = \App\Platform\Services\WorkerRegistry::status($dep->worker_slug);
        if ($workerStatus === 'testing' && !\App\Platform\Services\WorkerRegistry::canAccessTesting(auth()->user())) {
            return back()->with('error', 'This worker is in testing mode. Testing access is required to run Fast Track.');
        }

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

        $txService = app(TransactionService::class);
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

        // Dispatch the worker's fast track job via contract — worker owns the fast track entry point
        $contract         = WorkerRegistry::resolveActive($dep->worker_slug);
        $fastTrackJob     = $contract->fastTrackJobClass() ?: $contract->ingestJobClass();
        $fastTrackJob::dispatch($tx->tx_id)->onQueue($txService->queueForTx($tx));

        // Only count against the trial meter for non-subscribed tenants
        if (!$isSubscribed) {
            $config['fast_track_uses'] = ($config['fast_track_uses'] ?? 0) + 1;
            DB::table('worker_deployments')->where('id', $id)->update([
                'config'     => json_encode($config),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('workers.show', ['slug' => $dep->worker_slug, 'watch' => $tx->tx_id]);
    }

    public function fastTrackStatus(string $txId)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->first();
        if (!$tx) return response()->json(['error' => 'Not found'], 404);
        return response()->json([
            'tx_id'           => $tx->tx_id,
            'status'          => $tx->status,
            'category'        => $tx->category,
            'priority'        => $tx->priority,
            'read_output'     => json_decode($tx->read_output),
            'memory_output'   => json_decode($tx->memory_output),
            'classify_output' => json_decode($tx->classify_output),
            'draft_output'    => json_decode($tx->draft_output),
            'gmail_draft_id'  => $tx->gmail_draft_id,
        ]);
    }
}

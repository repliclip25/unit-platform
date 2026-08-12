<?php

namespace App\Platform\SDK;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Platform\SDK\Columns\TransactionColumns;

/**
 * The single gateway between a worker and the UNIT platform.
 *
 * Workers may ONLY import from:
 *   - App\Platform\SDK\*          (this class + DTOs)
 *   - App\Platform\Services\ClaudeService  (AI compute utility)
 *   - Their own App\Workers\{Slug}\* namespace
 *   - Laravel framework classes (facades, queue traits, etc.)
 *
 * Workers must NEVER import:
 *   - App\Platform\Services\TransactionService (replaced by this class)
 *   - Illuminate\Support\Facades\DB  (no direct DB access in jobs)
 *   - Any other App\Platform\* or App\Models\* class
 */
final class UnitPlatform
{
    // ── Stage → DB column map ─────────────────────────────────────────────
    // Was a hand-maintained const, independent of AvaWorker::pipelineStages()
    // — the two were supposed to always agree and silently didn't, twice
    // (schedule_next_watch's column was missing entirely at one point, and
    // this map drifted from pipelineStages() elsewhere too). Derived now, so
    // the same class of bug is structurally impossible: add a stage with a
    // real output_column to pipelineStages() and this map picks it up for
    // free, exactly like prompts() already derives from the same source.
    //
    // Six stages from the original synchronous drafting chain commit under a
    // short stage name ('read', 'template', 'draft', ...) that differs from
    // their pipelineStages() key ('read_email', 'select_template',
    // 'draft_email', ...) — every stage already declares this exact alias in
    // its own 'log_stage_key' field (originally added for the Desk activity
    // feed), so it doubles as the map key here with no new data needed.
    private static ?array $stageColumnsCache = null;

    private static function stageColumns(): array
    {
        if (self::$stageColumnsCache !== null) {
            return self::$stageColumnsCache;
        }

        $stages = \App\Platform\Services\WorkerRegistry::resolve('ava')->pipelineStages();

        $map = [];
        foreach ($stages as $stage) {
            if (empty($stage['output_column'])) continue;
            $map[$stage['log_stage_key'] ?? $stage['key']] = $stage['output_column'];
        }

        return self::$stageColumnsCache = $map;
    }

    // ── Transaction status → (stage_key, event) for stage log ────────────
    private const STATUS_STAGE_MAP = [
        'queued'        => ['stage' => 'filter',   'event' => 'completed'],
        'ingesting'     => ['stage' => 'ingest',   'event' => 'started'],
        'reading'       => ['stage' => 'read',     'event' => 'started'],
        'classifying'   => ['stage' => 'classify', 'event' => 'started'],
        'memory_lookup' => ['stage' => 'memory',   'event' => 'started'],
        'logging'       => ['stage' => 'log',      'event' => 'started'],
        'templating'    => ['stage' => 'template', 'event' => 'started'],
        'drafting'      => ['stage' => 'draft',    'event' => 'started'],
        'pushing'       => ['stage' => 'push',     'event' => 'started'],
        'draft_ready'   => ['stage' => 'push',     'event' => 'completed'],
        'sent'          => ['stage' => 'push',     'event' => 'completed'],
        'approved'      => ['stage' => 'push',     'event' => 'completed'],
        'failed'        => ['stage' => null,        'event' => 'failed'],
        'blocked'       => ['stage' => null,        'event' => 'failed'],
    ];

    // ─────────────────────────────────────────────────────────────────────
    // INPUT — build the full context package for a worker job
    // ─────────────────────────────────────────────────────────────────────

    public static function getInput(string $txId): WorkerInput
    {
        // Hard billing gate — blocks account-suspended, past_due, canceled tenants
        \App\Platform\Services\UsageGuard::checkHard($txId);

        $tx  = DB::table('transactions')->where('tx_id', $txId)->firstOrFail();
        $dep = $tx->deployment_id
            ? DB::table('worker_deployments')->where('id', $tx->deployment_id)->first()
            : null;

        // Accumulated stage outputs (decoded once here, not in every job)
        $stages = [];
        foreach (self::stageColumns() as $name => $col) {
            if (!empty($tx->{$col})) {
                $stages[$name] = json_decode($tx->{$col}, true) ?? [];
            }
        }

        // Memory — driven by the worker blueprint's memory.shared + memory.owned declarations
        $userId = $tx->user_id;
        $slug   = $dep?->worker_slug ?? 'ava';
        $depId  = $dep?->id;

        $blueprint = json_decode(
            DB::table('workers')->where('slug', $slug)->value('blueprint') ?? '{}',
            true
        ) ?? [];

        $memory   = self::loadMemoryFromBlueprint($blueprint, $userId, $slug, $depId);
        $rawInput = json_decode($tx->raw_input ?? '{}', true) ?? [];

        // Gmail credential — prefer explicit credential_id from raw_input (e.g. fast track inbox choice),
        // then fall back to deployment primary, then first available for this user.
        $explicitCredId = $rawInput['credential_id'] ?? null;
        $credential = $explicitCredId
            ? DB::table('user_gmail_credentials')->where('id', $explicitCredId)->where('user_id', $userId)->first()
            : ($dep?->credential_id
                ? DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first()
                : DB::table('user_gmail_credentials')->where('user_id', $userId)->first());

        $tenantEmail = DB::table('users')->where('id', $userId)->value('email');

        // Queue routing: worker-type queues, not per-deployment queues.
        // Tenant isolation is at the job level (deployment_id + user_id in payload).
        // _queue in raw_input allows explicit override (e.g. 'fast-track' for onboarding tests).
        $queue = $rawInput['_queue'] ?? ($slug ?: 'default');

        // Per-stage pipeline config — stored on the deployment, editable from UI
        $defaultConfig = [
            'read'     => ['max_tokens' => 1024, 'timeout' => 90,  'tries' => 3],
            'classify' => ['max_tokens' => 1024, 'timeout' => 90,  'tries' => 3],
            'memory'   => ['max_tokens' => 768,  'timeout' => 90,  'tries' => 3],
            'template' => ['max_tokens' => null,  'timeout' => 30,  'tries' => 3],
            'draft'    => ['max_tokens' => 2048, 'timeout' => 90,  'tries' => 3],
            'push'     => ['max_tokens' => null,  'timeout' => 60,  'tries' => 3],
        ];
        $savedConfig   = json_decode($dep?->pipeline_config ?? '{}', true) ?: [];
        $pipelineConfig = array_replace_recursive($defaultConfig, $savedConfig);

        $depConfig = json_decode($dep?->config ?? '{}', true) ?: [];
        $tenantOverrideModel = $depConfig['ai_model'] ?? null;

        // Billing/plan lookups only run when there's no tenant override —
        // the decision itself (priority order, threshold downgrade) is pure
        // logic in Internal\ModelResolver; see its own docblock for why.
        $billingUnitCount = null;
        $plan             = null;

        if (empty($tenantOverrideModel)) {
            $billing = $tx->deployment_id
                ? DB::table('deployment_billing')->where('deployment_id', $tx->deployment_id)->first()
                : null;

            if ($billing?->plan_slug) {
                $billingUnitCount = $billing->unit_count;
                $planRow = DB::table('worker_pricing')
                    ->where('worker_slug', $slug)
                    ->where('plan_slug', $billing->plan_slug)
                    ->first();

                if ($planRow) {
                    $plan = [
                        'stage_models'          => $planRow->stage_models,
                        'classify_model'        => $planRow->classify_model,
                        'draft_model'           => $planRow->draft_model,
                        'draft_model_threshold' => $planRow->draft_model_threshold,
                    ];
                }
            }
        }

        $resolvedModels = (new Internal\ModelResolver())->resolve($tenantOverrideModel, $billingUnitCount, $plan);
        $classifyModel  = $resolvedModels['classifyModel'];
        $draftModel     = $resolvedModels['draftModel'];
        $stageModels    = $resolvedModels['stageModels'];

        return new WorkerInput(
            txId:           $txId,
            deploymentId:   $tx->deployment_id ?? 0,
            userId:         $userId,
            workerSlug:     $slug,
            persona:        $dep?->persona ?? null,
            queue:          $queue,
            source:         $rawInput['source'] ?? 'unknown',
            raw:            $rawInput,
            stages:         $stages,
            memory:         $memory,
            credential:     $credential,
            tenantEmail:    $tenantEmail,
            pipelineConfig: $pipelineConfig,
            aiModel:        $depConfig['ai_model'] ?? $draftModel,
            classifyModel:  $classifyModel,
            draftModel:     $draftModel,
            stageModels:    $stageModels,
        );
    }

    // ── CREATE TRANSACTION — the only way a worker starts a transaction that
    //    didn't arrive via the standard webhook/ingest controller (e.g. a
    //    scheduled watcher with no inbound email at all, like
    //    AssetExpiryWatchJob). Wraps TransactionService::create() so workers
    //    never import it directly. ─────────────────────────────────────────
    public static function createTransaction(string $workerSlug, array $rawInput): object
    {
        return (new \App\Platform\Services\TransactionService())->create($workerSlug, $rawInput);
    }

    // ─────────────────────────────────────────────────────────────────────
    // MEMORY LOADING — blueprint-driven, not hardcoded
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Build the memory array for a worker by reading its blueprint's
     * memory.shared and memory.owned declarations.
     *
     * Scope rules:
     *   "user"             → WHERE user_id = $userId
     *   "user+worker_slug" → WHERE user_id = $userId AND worker_slug = $slug  (+ active=true if col exists)
     *   "deployment"       → WHERE deployment_id = $depId AND active = true
     *
     * The table name from the blueprint becomes the memory key (e.g. "contacts", "clients").
     * Owned tables with a worker-specific naming convention (ava_rules, email_templates) are
     * aliased via OWNED_TABLE_ALIASES so workers can reference them by a stable logical name.
     */
    private static function loadMemoryFromBlueprint(
        array  $blueprint,
        int    $userId,
        string $slug,
        ?int   $depId
    ): array {
        // Logical name → [table, key override] for owned tables that have worker-prefixed names
        // or secondary queries (templates_default).
        $owned_aliases = [
            'email_templates' => [
                'primary' => fn() => DB::table('email_templates')
                    ->where('user_id', $userId)
                    ->where('worker_slug', $slug)
                    ->where('active', true)
                    ->get()->toArray(),
                'key' => 'templates',
                'extra' => [
                    'templates_default' => fn() => DB::table('email_templates')
                        ->whereNull('user_id')
                        ->where('active', true)
                        ->get()->toArray(),
                ],
            ],
            'ava_rules' => [
                'primary' => fn() => $depId
                    ? DB::table('ava_rules')->where('deployment_id', $depId)->where('active', true)->get()->toArray()
                    : [],
                'key' => 'rules',
            ],
        ];

        $memory = [];

        $allLayers = array_merge(
            $blueprint['memory']['shared'] ?? [],
            $blueprint['memory']['owned']  ?? []
        );

        foreach ($allLayers as $layer) {
            $table = $layer['table'] ?? null;
            if (!$table) continue;

            // Use alias resolver if one exists for this table
            if (isset($owned_aliases[$table])) {
                $alias = $owned_aliases[$table];
                $key   = $alias['key'] ?? $table;
                $memory[$key] = ($alias['primary'])();
                foreach ($alias['extra'] ?? [] as $extraKey => $extraFn) {
                    $memory[$extraKey] = $extraFn();
                }
                continue;
            }

            // Generic scope-driven loader for shared tables
            $scope = $layer['scope'] ?? 'user';
            $key   = $table; // memory key matches table name

            $softDelete = in_array($table, ['clients', 'contacts', 'assets']);
            $memory[$key] = match ($scope) {
                'user'             => DB::table($table)->where('user_id', $userId)->when($softDelete, fn($q) => $q->whereNull('deleted_at'))->get()->toArray(),
                'user+worker_slug' => DB::table($table)->where('user_id', $userId)->where('worker_slug', $slug)->when($softDelete, fn($q) => $q->whereNull('deleted_at'))->get()->toArray(),
                'deployment'       => $depId ? DB::table($table)->where('deployment_id', $depId)->when($softDelete, fn($q) => $q->whereNull('deleted_at'))->get()->toArray() : [],
                'global'           => DB::table($table)->when($softDelete, fn($q) => $q->whereNull('deleted_at'))->get()->toArray(),
                default            => DB::table($table)->where('user_id', $userId)->when($softDelete, fn($q) => $q->whereNull('deleted_at'))->get()->toArray(),
            };
        }

        return $memory;
    }

    // ─────────────────────────────────────────────────────────────────────
    // OUTPUT — commit a stage result back to UNIT
    // ─────────────────────────────────────────────────────────────────────

    public static function commitOutput(string $txId, WorkerOutput $output): void
    {
        $update = ['updated_at' => now()];

        if ($output->status !== null) {
            self::maybeIncrementTrialUsage($txId, $output->status);
            $update['status'] = $output->status;
        }

        $stageColumns = self::stageColumns();
        if (isset($stageColumns[$output->stage])) {
            $col          = $stageColumns[$output->stage];
            $update[$col] = json_encode($output->data, JSON_INVALID_UTF8_SUBSTITUTE);
        }

        if ($output->category !== null)     $update['category']       = $output->category;
        if ($output->priority !== null)     $update['priority']       = $output->priority;
        if ($output->gmailDraftId !== null) $update['gmail_draft_id'] = $output->gmailDraftId;

        DB::table('transactions')->where('tx_id', $txId)->update($update);

        // Mark stage completed in the stage log with duration
        (new Internal\StageLog())->completed($txId, $output->stage);
    }

    // ── PIPELINE ADVANCE — dispatches whatever comes after a given stage,
    //    resolved from the contract's pipelineStages() instead of each job
    //    hardcoding its own successor. This is what makes a stage a genuine,
    //    resumable unit: any job (or a synthetic entry point like a scheduled
    //    watcher with no inbound email) can call advance() from wherever it
    //    is, and the platform figures out what runs next — not the job
    //    itself. Adding, removing, or reordering a stage in the contract
    //    changes execution order platform-wide with no job file to touch.
    // ─────────────────────────────────────────────────────────────────────
    public static function advance(string $txId, string $fromStageKey): void
    {
        $input    = self::getInput($txId);
        $contract = \App\Platform\Services\WorkerRegistry::resolve($input->workerSlug);
        $stages   = $contract->pipelineStages();

        $currentIndex = collect($stages)->search(fn($s) => $s['key'] === $fromStageKey);
        if ($currentIndex === false) {
            Log::warning('UnitPlatform::advance — unknown stage key', ['tx_id' => $txId, 'stage' => $fromStageKey]);
            return;
        }

        // The actual "what's next" decision (skip disabled gates, halt at a
        // hard gate, dispatch the next runnable stage, or report the
        // pipeline complete) is pure logic with no DB/queue dependency — see
        // Internal\PipelineAdvancer's own docblock for why it's split out.
        // A caller resuming FROM a hard-gate stage (e.g. after the human
        // acts) starts its own scan past that same stage, so it's never
        // self-blocking.
        $decision = (new Internal\PipelineAdvancer())->resolveNextDispatch(
            $stages,
            $currentIndex,
            fn(string $stageKey) => self::gateEnabled($input->deploymentId, $stageKey, true),
        );

        if ($decision['type'] === Internal\PipelineAdvancer::COMPLETE) {
            return; // no stage after this one — the pipeline is complete for this transaction
        }

        if ($decision['type'] === Internal\PipelineAdvancer::HALT) {
            self::setFulfillmentStage($txId, $decision['stage']['key']);
            return; // waiting on a human action to resume from here
        }

        // Derive the Jobs namespace from the worker contract's own FQCN (via
        // WorkerRegistry) rather than re-deriving it from the slug —
        // Str::studly('ava') produces 'Ava', not 'AVA', which only ever
        // worked locally because macOS's filesystem is case-insensitive;
        // Linux (production) is case-sensitive and fails class_exists().
        $contractClass   = \App\Platform\Services\WorkerRegistry::resolve($input->workerSlug)::class;
        $workerNamespace = substr($contractClass, 0, strrpos($contractClass, '\\'));
        $jobFqn = $workerNamespace . '\\Jobs\\' . $decision['stage']['job_class'];

        if (!class_exists($jobFqn)) {
            Log::error('UnitPlatform::advance — job class not found', ['tx_id' => $txId, 'job_class' => $jobFqn]);
            return;
        }

        $jobFqn::dispatch($txId)->onQueue($input->queue);
    }

    // ── Trial quota: charged once, the first time a transaction reaches a
    //    success status (see TransactionService::SUCCESS_STATUSES). Must run
    //    BEFORE the status column is overwritten, since the "already counted"
    //    check depends on seeing the pre-update status.
    private static function maybeIncrementTrialUsage(string $txId, string $newStatus): void
    {
        if (!in_array($newStatus, \App\Platform\Services\TransactionService::SUCCESS_STATUSES, true)) {
            return;
        }

        $tx = DB::table('transactions')->where('tx_id', $txId)->first(['status', 'deployment_id', 'is_test']);
        if (!$tx || !$tx->deployment_id) {
            return;
        }

        // Already counted on an earlier success status (e.g. draft_ready → approved) — skip
        if (in_array($tx->status, \App\Platform\Services\TransactionService::SUCCESS_STATUSES, true)) {
            return;
        }

        // Fast Track and real transactions share one trial pool now — Fast
        // Track additionally counts against its own reserved sub-allocation
        // within that pool. See PlatformDefaults::fastTrackAllocationFor()
        // and TransactionService::incrementTrialUsage().
        \App\Platform\Services\TransactionService::incrementTrialUsage($tx->deployment_id, (bool) $tx->is_test);
    }

    // ── FULFILLMENT STAGE — tracks stage 10-16 progress separately from
    //    transactions.status (a fixed enum that stays at 'approved'/'sent'
    //    through fulfillment). Free-text, not enum-constrained, so a new
    //    fulfillment stage never needs another ALTER TABLE migration. ──────
    public static function setFulfillmentStage(string $txId, string $stage): void
    {
        DB::table('transactions')->where('tx_id', $txId)->update([
            'fulfillment_stage' => $stage,
            'updated_at'        => now(),
        ]);
    }

    // ── REMINDERS — every reminder actually sent while a transaction waits
    //    at a gate, packaged as one JSON array per transaction rather than a
    //    dedicated table. Shared by any gate on any worker (Approve & Send,
    //    Confirm Payment today) — callers only need a stage key and the
    //    message content; attempt numbering and storage are handled here. ──
    public static function recordReminder(string $txId, string $stageKey, string $subject, string $body): int
    {
        $tx        = DB::table('transactions')->where('tx_id', $txId)->first(['reminders']);
        $reminders = json_decode($tx->reminders ?? '[]', true) ?: [];

        $attempt = count(array_filter($reminders, fn($r) => ($r['stage_key'] ?? null) === $stageKey)) + 1;

        $reminders[] = [
            'stage_key'      => $stageKey,
            'attempt_number' => $attempt,
            'subject'        => $subject,
            'body'           => $body,
            'sent_at'        => now()->toISOString(),
        ];

        DB::table('transactions')->where('tx_id', $txId)->update([
            'reminders'  => json_encode($reminders, JSON_INVALID_UTF8_SUBSTITUTE),
            'updated_at' => now(),
        ]);

        return $attempt;
    }

    // ── CLIENT DRAFTS — the client-facing renewal draft, now sent up to 3
    //    times on a real calendar cadence (30/15/0 days before expiry)
    //    instead of once. Each occurrence's full content is preserved here;
    //    draft_output stays as "most recent" for backward compatibility with
    //    anything still reading it directly. ────────────────────────────────
    public static function recordClientDraft(string $txId, string $to, string $subject, string $body, ?int $daysBeforeExpiry = null, ?int $templateId = null): int
    {
        $tx     = DB::table('transactions')->where('tx_id', $txId)->first(['client_drafts', 'client_reminder_number']);
        $drafts = json_decode($tx->client_drafts ?? '[]', true) ?: [];

        // Counted off the column, not count($drafts) — a preview (see
        // recordClientDraftPreview()) can already occupy a slot in the
        // array for a round that hasn't really happened yet, so array
        // length alone would overcount.
        $reminderNumber = (int) $tx->client_reminder_number + 1;

        $entry = [
            'reminder_number'    => $reminderNumber,
            'days_before_expiry' => $daysBeforeExpiry,
            'to'                 => $to,
            'subject'            => $subject,
            'body'               => $body,
            'drafted_at'         => now()->toISOString(),
            'approved_at'        => null,
            // Each cadence round can resolve to a different email_templates
            // row (round 2/3 templates are separate rows from round 1's) —
            // capture which one so the Transaction Center can link each
            // round to its own template, not just round 1's.
            'template_id'        => $templateId,
        ];

        // A preview already generated for this exact round (see below) gets
        // replaced in place with the real thing instead of duplicated.
        $previewIdx = collect($drafts)->search(
            fn($d) => ($d['reminder_number'] ?? null) === $reminderNumber && !empty($d['preview'])
        );

        if ($previewIdx !== false) {
            $drafts[$previewIdx] = $entry;
        } else {
            $drafts[] = $entry;
        }

        DB::table('transactions')->where('tx_id', $txId)->update([
            'client_drafts'           => json_encode($drafts, JSON_INVALID_UTF8_SUBSTITUTE),
            'client_reminder_number'  => $reminderNumber,
            'updated_at'              => now(),
        ]);

        return $reminderNumber;
    }

    // ── CLIENT DRAFT PREVIEWS — generated once, upfront, alongside round 1
    //    (see DraftEmailJob), so approving round 1 means approving a
    //    realistic sense of the whole cadence, not a blank "not drafted
    //    yet" placeholder for rounds 2/3. A preview is explicitly NOT what
    //    gets sent later — ClientReminderCycleJob still regenerates each
    //    round fresh against whatever's true when its real threshold hits,
    //    via recordClientDraft() above, which replaces the preview in place.
    public static function recordClientDraftPreview(string $txId, int $reminderNumber, ?int $daysBeforeExpiry, string $to, string $subject, string $body, ?int $templateId = null): void
    {
        $tx     = DB::table('transactions')->where('tx_id', $txId)->first(['client_drafts']);
        $drafts = json_decode($tx->client_drafts ?? '[]', true) ?: [];

        // Never overwrite a round that's already real (or already
        // previewed) — this only ever fills a genuinely empty slot.
        if (collect($drafts)->contains(fn($d) => ($d['reminder_number'] ?? null) === $reminderNumber)) {
            return;
        }

        $drafts[] = [
            'reminder_number'    => $reminderNumber,
            'days_before_expiry' => $daysBeforeExpiry,
            'to'                 => $to,
            'subject'            => $subject,
            'body'               => $body,
            'drafted_at'         => now()->toISOString(),
            'approved_at'        => null,
            'preview'            => true,
            'template_id'        => $templateId,
        ];

        usort($drafts, fn($a, $b) => ($a['reminder_number'] ?? 0) <=> ($b['reminder_number'] ?? 0));

        DB::table('transactions')->where('tx_id', $txId)->update([
            'client_drafts' => json_encode($drafts, JSON_INVALID_UTF8_SUBSTITUTE),
            'updated_at'    => now(),
        ]);
    }

    // Marks the most recent client draft as approved — called from
    // TransactionController::decide() so the Transaction Center can show
    // exactly when each of the (up to 3) reminders was approved, not just
    // the latest decision.
    public static function markLatestClientDraftApproved(string $txId): void
    {
        $tx     = DB::table('transactions')->where('tx_id', $txId)->first(['client_drafts']);
        $drafts = json_decode($tx->client_drafts ?? '[]', true) ?: [];
        if (empty($drafts)) return;

        $last               = count($drafts) - 1;
        $drafts[$last]['approved_at'] = now()->toISOString();

        DB::table('transactions')->where('tx_id', $txId)->update([
            'client_drafts' => json_encode($drafts, JSON_INVALID_UTF8_SUBSTITUTE),
            'updated_at'    => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // STATUS — lightweight status-only update (also drives stage log)
    // ─────────────────────────────────────────────────────────────────────

    public static function setStatus(string $txId, string $status, ?string $errorMessage = null): void
    {
        self::maybeIncrementTrialUsage($txId, $status);

        $mapped = self::STATUS_STAGE_MAP[$status] ?? null;
        $currentStage = $mapped ? $mapped['stage'] : null;

        $txUpdate = ['status' => $status, 'updated_at' => now()];
        if ($currentStage) {
            $txUpdate['current_stage'] = $currentStage;
        }

        DB::table('transactions')->where('tx_id', $txId)->update($txUpdate);

        if (!$mapped) return;

        $tx = DB::table('transactions')->where('tx_id', $txId)
            ->select('deployment_id', 'user_id', 'worker_slug', 'current_stage')
            ->first();

        $stageKey = $mapped['stage'] ?? $tx?->current_stage;
        if (!$stageKey) return;

        // See Internal\StageLog's own docblock — the transaction_stage_log
        // audit trail (started/completed/failed, with duration and attempt
        // number) was only ever a private, internal-only concern of this
        // class; extracted with no public API change.
        // $errorMessage carries the real exception text (job's failed($e)
        // handler passes $e->getMessage()) instead of falling back to the
        // generic $status literal ('failed'/'blocked') that error_summary
        // used to always contain — a status string told you THAT it failed,
        // never WHY.
        match ($mapped['event']) {
            'started'   => (new Internal\StageLog())->started($txId, $stageKey),
            'completed' => (new Internal\StageLog())->completed($txId, $stageKey),
            'failed'    => (new Internal\StageLog())->failed($txId, $stageKey, $errorMessage ?? $status),
            default     => null,
        };
    }

    // ─────────────────────────────────────────────────────────────────────
    // LOG — structured event log
    // ─────────────────────────────────────────────────────────────────────

    public static function log(
        string $workerSlug,
        string $txId,
        string $event,
        mixed  $data  = [],
        string $level = 'info'
    ): void {
        $context = ['worker' => $workerSlug, 'tx_id' => $txId, 'event' => $event, 'data' => $data];
        Log::{$level}("[UNIT:{$workerSlug}] {$event}", $context);
    }

    // ─────────────────────────────────────────────────────────────────────
    // REGISTER — write to the renewal register (worker-agnostic log table)
    // ─────────────────────────────────────────────────────────────────────

    public static function register(string $txId, array $record): void
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->first();

        if (DB::table('renewal_register')->where('tx_id', $txId)->exists()) {
            DB::table('renewal_register')
                ->where('tx_id', $txId)
                ->update(array_merge($record, ['updated_at' => now()]));
        } else {
            DB::table('renewal_register')->insert(array_merge($record, [
                'tx_id'         => $txId,
                'user_id'       => $tx?->user_id,
                'deployment_id' => $tx?->deployment_id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]));
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // PROMPT OVERRIDES — per-deployment prompt tuning
    // ─────────────────────────────────────────────────────────────────────

    public static function getPromptOverride(int $deploymentId, string $stageKey): ?array
    {
        if (!$deploymentId) return null;

        $row = DB::table('deployment_prompt_overrides')
            ->where('deployment_id', $deploymentId)
            ->where('stage_key', $stageKey)
            ->first();

        if (!$row) return null;

        return array_filter([
            'system'     => $row->system_prompt ?: null,
            'user'       => $row->user_prompt   ?: null,
            'model'      => $row->model         ?: null,
            'max_tokens' => $row->max_tokens    ?: null,
        ], fn($v) => $v !== null);
    }

    // ── Deployment-level gates — "AVA Settings" page. Trigger sources
    // (gmail_watch, asset_watch), stage gates (whether a pipeline stage runs
    // at all — key matches the stage's own pipelineStages() key), and
    // message gates (whether a stage that DOES run also sends its email).
    // No row for a key means "use the default" — existing deployments never
    // need a backfill when a new gate is introduced.
    public static function gateEnabled(?int $deploymentId, string $key, bool $default = true): bool
    {
        if (!$deploymentId) return $default;

        $row = DB::table('deployment_stage_settings')
            ->where('deployment_id', $deploymentId)
            ->where('key', $key)
            ->first();

        return $row ? (bool) $row->enabled : $default;
    }

    // ── STAGE COMPLETED AT — real completion timestamp for any stage of any
    // transaction, read from transaction_stage_log (see Internal\StageLog).
    // commitOutput() already logs a 'completed' row there for every stage
    // on every worker, so this is the universal source of truth for "when
    // did X happen" — callers should use this instead of hand-adding a
    // timestamp field to their own stage output, which only covers that one
    // job and is easy to forget on the next stage or the next worker.
    public static function stageCompletedAt(string $txId, string $stageKey): ?string
    {
        $row = DB::table('transaction_stage_log')
            ->where('tx_id', $txId)
            ->where('stage_key', $stageKey)
            ->where('event', 'completed')
            ->latest('id')
            ->first();

        return $row?->created_at;
    }

    // ── STAGE STARTED — logs a 'started' row so the matching commitOutput()
    // call's 'completed' row gets a real duration_ms (StageLog::completed()
    // computes it from the most recent 'started' row for the same stage_key
    // — with none logged, duration is always null). The drafting chain
    // already gets this for free from setStatus()'s STATUS_STAGE_MAP; the
    // fulfillment stages (request_invoice onward) only ever call
    // commitOutput() directly, so they never had a 'started' row until now.
    // Deliberately NOT routed through setStatus() — that also writes
    // transactions.status/current_stage, which fulfillment jobs shouldn't
    // have overwritten just to get a timing log; this is a pure log write.
    public static function stageStarted(string $txId, string $stageKey): void
    {
        (new Internal\StageLog())->started($txId, $stageKey);
    }

    // ── STAGE FAILED — for jobs whose failed() handler never calls
    // setStatus('failed') at all (the fulfillment stages — a failure there
    // is absorbed and the pipeline advances anyway, so transactions.status
    // deliberately isn't touched). Without this, those failures never
    // reached transaction_stage_log at all — not just with a weak error
    // message, but with zero record of the failure existing.
    public static function stageFailed(string $txId, string $stageKey, string $errorMessage): void
    {
        (new Internal\StageLog())->failed($txId, $stageKey, $errorMessage);
    }

    // ── CADENCE STATE — lets PushToGmailJob decide whether a client
    // reminder round should auto-continue (already blanket-approved once)
    // or halt and wait for the tenant's first decision, without querying
    // DB directly (jobs may only reach the transactions table through
    // UnitPlatform — see this class's own docblock).
    public static function getCadenceState(string $txId): array
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)
            ->select('deployment_id', 'human_decision', 'client_reminder_number', TransactionColumns::CADENCE_STOPPED)
            ->first();

        $sendModeDirect = $tx?->deployment_id
            ? DB::table('worker_deployments')->where('id', $tx->deployment_id)->value('send_mode') === 'direct'
            : false;

        return [
            'approvedOnce'    => $tx?->human_decision === 'approved',
            'reminderNumber'  => (int) ($tx?->client_reminder_number ?? 0),
            'cadenceStopped'  => (bool) ($tx?->{TransactionColumns::CADENCE_STOPPED} ?? false),
            'sendModeDirect'  => $sendModeDirect,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────
    // SCHEDULED JOB HELPERS — context for jobs without a txId
    // ─────────────────────────────────────────────────────────────────────

    public static function getDeploymentContext(int $deploymentId): ?object
    {
        $dep  = DB::table('worker_deployments')->where('id', $deploymentId)->first();
        if (!$dep) return null;
        $user = DB::table('users')->where('id', $dep->user_id)->first();
        if (!$user) return null;
        return (object) ['deployment' => $dep, 'user' => $user];
    }

    public static function getRegisterEntries(int $deploymentId, string $date): \Illuminate\Support\Collection
    {
        return DB::table('renewal_register')
            ->where('deployment_id', $deploymentId)
            ->whereDate('created_at', $date)
            ->get();
    }

    // ─────────────────────────────────────────────────────────────────────
    // MEMORY CONTRIBUTION — write discovered data back to the shared pool
    // ─────────────────────────────────────────────────────────────────────

    // Tables workers are permitted to contribute to. Owned tables (ava_rules,
    // email_templates) are intentionally excluded — only shared tenant tables.
    // Only nullable, worker-resolvable columns — never FK columns or NOT NULL without defaults
    private const SHARED_TABLES = [
        'clients'  => ['name', 'industry', 'notes'],
        'contacts' => ['name', 'email', 'phone', 'role'],
        'assets'   => ['name', 'vendor', 'renewal_date', 'notes'],
    ];

    // Values that indicate Claude couldn't resolve the field — never persist these
    private const UNKNOWN_VALUES = ['unknown', 'n/a', 'none', '', null];

    /**
     * Contribute newly discovered data to a shared memory table.
     *
     * Workers call this when they encounter a client, contact, or asset that
     * isn't already in memory. UNIT writes it to the shared pool so ALL
     * workers for this tenant benefit immediately.
     *
     * Usage:
     *   UnitPlatform::contributeMemory($txId, 'contacts', [
     *       'name'    => 'Jane Doe',
     *       'email'   => 'jane@client.com',
     *       'company' => 'Acme Corp',
     *   ]);
     */
    public static function contributeMemory(string $txId, string $table, array $data): void
    {
        if (!isset(self::SHARED_TABLES[$table])) {
            throw new \InvalidArgumentException(
                "Table '{$table}' is not a shared memory table. "
                . "Allowed: " . implode(', ', array_keys(self::SHARED_TABLES))
            );
        }

        $tx = DB::table('transactions')->where('tx_id', $txId)->first();
        if (!$tx) return;

        $userId = $tx->user_id;
        $dep    = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();

        // Strip any keys not allowed for this table (safety guard)
        $allowed = self::SHARED_TABLES[$table];
        $safe    = array_intersect_key($data, array_flip($allowed));

        // Scrub fields whose values are placeholder unknowns — never persist "Unknown" or "" to memory
        $safe = array_filter($safe, fn($v) => !in_array(strtolower((string) $v), self::UNKNOWN_VALUES, true));

        // Nothing real to contribute
        if (empty($safe)) return;

        $action   = 'created';
        $recordId = null;

        if ($table === 'contacts' && !empty($safe['email'])) {
            $existing = DB::table('contacts')->whereNull('deleted_at')
                ->where('user_id', $userId)
                ->where('email', $safe['email'])
                ->first();

            if ($existing) {
                DB::table('contacts')->where('id', $existing->id)
                    ->update(array_merge($safe, ['updated_at' => now()]));
                $recordId = $existing->id;
                $action   = 'updated';
            } else {
                $recordId = DB::table('contacts')->insertGetId(
                    array_merge($safe, ['user_id' => $userId, 'client_id' => null, 'created_at' => now(), 'updated_at' => now()])
                );
            }

        } elseif ($table === 'clients' && !empty($safe['name'])) {
            $existing = DB::table('clients')
                ->where('user_id', $userId)
                ->whereNull('deleted_at')
                ->whereRaw('LOWER(name) = ?', [strtolower($safe['name'])])
                ->first();

            if ($existing) {
                DB::table('clients')->where('id', $existing->id)
                    ->update(array_merge($safe, ['updated_at' => now()]));
                $recordId = $existing->id;
                $action   = 'updated';
            } else {
                $recordId = DB::table('clients')->insertGetId(
                    array_merge($safe, ['user_id' => $userId, 'created_at' => now(), 'updated_at' => now()])
                );
            }

        } elseif ($table === 'assets' && !empty($safe['name'])) {
            $existing = DB::table('assets')
                ->where('user_id', $userId)
                ->whereNull('deleted_at')
                ->whereRaw('LOWER(name) = ?', [strtolower($safe['name'])])
                ->first();

            if ($existing) {
                DB::table('assets')->where('id', $existing->id)
                    ->update(array_merge($safe, ['updated_at' => now()]));
                $recordId = $existing->id;
                $action   = 'updated';
            } else {
                $recordId = DB::table('assets')->insertGetId(
                    array_merge($safe, ['user_id' => $userId, 'client_id' => null, 'type' => 'discovered', 'created_at' => now(), 'updated_at' => now()])
                );
            }
        }

        if ($recordId) {
            self::log($dep?->worker_slug ?? 'unknown', $txId, 'memory_contributed', [
                'table'  => $table,
                'action' => $action,
                'id'     => $recordId,
                'data'   => $safe,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // EMIT — fire a break-injection event for other workers to consume
    // ─────────────────────────────────────────────────────────────────────

    public static function emit(string $txId, WorkerEvent $event): void
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->first();

        // Log the event to the broker table
        $eventId = DB::table('worker_events')->insertGetId([
            'event_name'           => $event->name,
            'tx_id'                => $txId,
            'source_deployment_id' => $tx?->deployment_id,
            'source_user_id'       => $tx?->user_id,
            'payload'              => json_encode($event->payload, JSON_INVALID_UTF8_SUBSTITUTE),
            'status'               => 'pending',
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Route to all active deployments whose worker blueprint subscribes to this event
        // Scoped to the same tenant (user_id) so events never cross tenant boundaries
        // JSON_CONTAINS/JSON_EXTRACT are MySQL-only — no subscriber routing under the
        // SQLite test suite (nothing currently subscribes to any event in tests).
        $subscribers = DB::getDriverName() === 'sqlite'
            ? collect()
            : DB::table('worker_deployments as wd')
                ->join('workers as w', 'w.slug', '=', 'wd.worker_slug')
                ->where('wd.user_id', $tx?->user_id)
                ->where('wd.status', 'active')
                ->whereRaw("JSON_CONTAINS(JSON_EXTRACT(w.blueprint, '$.subscribes'), ?)", ['"' . $event->name . '"'])
                ->select('wd.id', 'wd.worker_slug')
                ->get();

        $routedTo = [];
        foreach ($subscribers as $sub) {
            // Future: dispatch the subscriber's entry-point job with the event payload
            // For now: record routing so the subscriber can poll or be wired up later
            $routedTo[] = $sub->id;
            self::log('unit-platform', $txId, 'event_routed', [
                'event'         => $event->name,
                'routed_to'     => "{$sub->worker_slug}:{$sub->id}",
            ]);
        }

        DB::table('worker_events')->where('id', $eventId)->update([
            'routed_to'  => json_encode($routedTo),
            'status'     => empty($routedTo) ? 'no_subscribers' : 'routed',
            'updated_at' => now(),
        ]);
    }
}

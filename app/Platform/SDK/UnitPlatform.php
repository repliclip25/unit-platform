<?php

namespace App\Platform\SDK;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    private const STAGE_COLUMNS = [
        'read'     => 'read_output',
        'classify' => 'classify_output',
        'memory'   => 'memory_output',
        'template' => 'template_output',
        'draft'    => 'draft_output',
    ];

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
        foreach (self::STAGE_COLUMNS as $name => $col) {
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

        // ── Plan-driven model resolution ──────────────────────────────────
        // Priority: tenant custom model (depConfig) → stage_models JSON → classify/draft legacy → platform default
        $classifyModel = 'claude-haiku-4-5-20251001';
        $draftModel    = 'claude-sonnet-4-6';
        $stageModels   = [];

        if (!empty($depConfig['ai_model'])) {
            // Tenant custom model override — applies to every stage
            $classifyModel = $depConfig['ai_model'];
            $draftModel    = $depConfig['ai_model'];
        } else {
            $billing = $tx->deployment_id
                ? DB::table('deployment_billing')->where('deployment_id', $tx->deployment_id)->first()
                : null;

            if ($billing?->plan_slug) {
                $plan = DB::table('worker_pricing')
                    ->where('worker_slug', $slug)
                    ->where('plan_slug', $billing->plan_slug)
                    ->first();

                if ($plan) {
                    // Per-stage model map (new system — takes priority)
                    if (!empty($plan->stage_models)) {
                        $stageModels = json_decode($plan->stage_models, true) ?: [];
                    }

                    // Legacy two-model fields (fallback when stage_models not set)
                    $classifyModel = $plan->classify_model ?: $classifyModel;
                    $draftModel    = $plan->draft_model    ?: $draftModel;

                    // Threshold check — downgrade draft stage to classify model to protect margin
                    if (empty($stageModels) && $plan->draft_model_threshold && $billing->unit_count >= $plan->draft_model_threshold) {
                        $draftModel = $classifyModel;
                    }
                    if (!empty($stageModels) && $plan->draft_model_threshold && $billing->unit_count >= $plan->draft_model_threshold) {
                        $downgrade = $stageModels['classify'] ?? $stageModels['read'] ?? $classifyModel;
                        $stageModels['draft'] = $downgrade;
                    }
                }
            }
        }

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
        $update = ['updated_at' => now(), 'status' => $output->status];

        if (isset(self::STAGE_COLUMNS[$output->stage])) {
            $col          = self::STAGE_COLUMNS[$output->stage];
            $update[$col] = json_encode($output->data, JSON_INVALID_UTF8_SUBSTITUTE);
        }

        if ($output->category !== null)     $update['category']       = $output->category;
        if ($output->priority !== null)     $update['priority']       = $output->priority;
        if ($output->gmailDraftId !== null) $update['gmail_draft_id'] = $output->gmailDraftId;

        DB::table('transactions')->where('tx_id', $txId)->update($update);

        // Mark stage completed in the stage log with duration
        self::logStageCompleted($txId, $output->stage);
    }

    // ─────────────────────────────────────────────────────────────────────
    // STATUS — lightweight status-only update (also drives stage log)
    // ─────────────────────────────────────────────────────────────────────

    public static function setStatus(string $txId, string $status): void
    {
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

        $event = $mapped['event'];

        try {
            if ($event === 'started') {
                DB::table('transaction_stage_log')->insert([
                    'tx_id'         => $txId,
                    'deployment_id' => $tx?->deployment_id,
                    'user_id'       => $tx?->user_id,
                    'worker_slug'   => $tx?->worker_slug,
                    'stage_key'     => $stageKey,
                    'event'         => 'started',
                    'attempt'       => self::currentAttempt($txId, $stageKey),
                    'created_at'    => now(),
                ]);
            } elseif ($event === 'completed') {
                self::logStageCompleted($txId, $stageKey);
            } elseif ($event === 'failed') {
                self::logStageFailed($txId, $stageKey, $status);
            }
        } catch (\Throwable $e) {
            Log::warning('StageLog write failed', ['tx_id' => $txId, 'error' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // STAGE LOG HELPERS — internal only
    // ─────────────────────────────────────────────────────────────────────

    private static function logStageCompleted(string $txId, string $stageKey): void
    {
        try {
            $started = DB::table('transaction_stage_log')
                ->where('tx_id', $txId)
                ->where('stage_key', $stageKey)
                ->where('event', 'started')
                ->latest('id')
                ->first();

            $durationMs = $started
                ? max(0, (int) \Carbon\Carbon::parse($started->created_at)->diffInMilliseconds(now()))
                : null;

            $tx = DB::table('transactions')->where('tx_id', $txId)
                ->select('deployment_id', 'user_id', 'worker_slug')
                ->first();

            DB::table('transaction_stage_log')->insert([
                'tx_id'         => $txId,
                'deployment_id' => $tx?->deployment_id,
                'user_id'       => $tx?->user_id,
                'worker_slug'   => $tx?->worker_slug,
                'stage_key'     => $stageKey,
                'event'         => 'completed',
                'duration_ms'   => $durationMs,
                'attempt'       => self::currentAttempt($txId, $stageKey),
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('StageLog completed write failed', ['tx_id' => $txId, 'error' => $e->getMessage()]);
        }
    }

    private static function logStageFailed(string $txId, string $stageKey, string $errorSummary = 'failed'): void
    {
        try {
            $started = DB::table('transaction_stage_log')
                ->where('tx_id', $txId)
                ->where('stage_key', $stageKey)
                ->where('event', 'started')
                ->latest('id')
                ->first();

            $durationMs = $started
                ? max(0, (int) \Carbon\Carbon::parse($started->created_at)->diffInMilliseconds(now()))
                : null;

            $tx = DB::table('transactions')->where('tx_id', $txId)
                ->select('deployment_id', 'user_id', 'worker_slug')
                ->first();

            DB::table('transaction_stage_log')->insert([
                'tx_id'         => $txId,
                'deployment_id' => $tx?->deployment_id,
                'user_id'       => $tx?->user_id,
                'worker_slug'   => $tx?->worker_slug,
                'stage_key'     => $stageKey,
                'event'         => 'failed',
                'duration_ms'   => $durationMs,
                'error_summary' => $errorSummary,
                'attempt'       => self::currentAttempt($txId, $stageKey),
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('StageLog failed write failed', ['tx_id' => $txId, 'error' => $e->getMessage()]);
        }
    }

    private static function currentAttempt(string $txId, string $stageKey): int
    {
        return (int) DB::table('transaction_stage_log')
            ->where('tx_id', $txId)
            ->where('stage_key', $stageKey)
            ->where('event', 'started')
            ->count() + 1;
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
            DB::table('memory_contributions')->insert([
                'tx_id'         => $txId,
                'worker_slug'   => $dep?->worker_slug ?? 'unknown',
                'deployment_id' => $tx->deployment_id,
                'user_id'       => $userId,
                'table_name'    => $table,
                'record_id'     => $recordId,
                'action'        => $action,
                'data'          => json_encode($safe),
                'status'        => 'active',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

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
        $subscribers = DB::table('worker_deployments as wd')
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

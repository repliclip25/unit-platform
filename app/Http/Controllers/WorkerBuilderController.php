<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkerBuilderController extends Controller
{
    private array $statuses = [
        'registered'     => ['label' => 'Registered',     'color' => '#94a3b8'],
        'scaffolded'     => ['label' => 'Scaffolded',     'color' => '#60a5fa'],
        'in_development' => ['label' => 'In Development', 'color' => '#f59e0b'],
        'testing'        => ['label' => 'Testing',        'color' => '#a78bfa'],
        'published'      => ['label' => 'Published',      'color' => '#34d399'],
        'retired'        => ['label' => 'Retired',        'color' => '#f87171'],
    ];

    // ── Index ────────────────────────────────────────────────────────────────

    public function index()
    {
        $workers = DB::table('worker_registry')->orderByDesc('created_at')->get()
            ->map(function ($w) {
                $w->org            = json_decode($w->org ?? '{}', true);
                $w->pipeline_stages = json_decode($w->pipeline_stages ?? '[]', true);
                $w->tags           = json_decode($w->tags ?? '[]', true);
                $w->status_meta    = $this->statuses[$w->status] ?? ['label' => $w->status, 'color' => '#94a3b8'];
                return $w;
            });

        return view('admin.worker-builder.index', [
            'workers'  => $workers,
            'statuses' => $this->statuses,
        ]);
    }

    // ── Create / Store ───────────────────────────────────────────────────────

    public function create()
    {
        return view('admin.worker-builder.form', [
            'worker'   => null,
            'statuses' => $this->statuses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                   => 'required|string|max:100',
            'slug'                   => 'required|string|max:60|unique:worker_registry,slug|regex:/^[a-z0-9\-]+$/',
            'media_items.*.file'     => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm|max:51200',
        ]);

        $payload = array_merge($this->buildPayload($request), [
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $slug = $request->slug;
        [$mediaItems, $profileImage, $coverImage] = $this->processMediaItems($request, $slug);
        $payload['gallery']       = json_encode($mediaItems);
        $payload['profile_image'] = $profileImage;
        $payload['cover_image']   = $coverImage;

        $id = DB::table('worker_registry')->insertGetId($payload);

        return redirect()->route('admin.workers.edit', DB::table('worker_registry')->find($id)->slug)
            ->with('success', 'Worker registered. Fill out the full DNA below, then generate the scaffold.');
    }

    // ── Edit / Update ────────────────────────────────────────────────────────

    public function edit(string $slug)
    {
        $worker = DB::table('worker_registry')->where('slug', $slug)->firstOrFail();

        foreach (['org','pipeline_stages','qa_requirements','credential','instances',
                  'deployment_fields','train_schema','tags','owner','media','notifications',
                  'subscriptions','version_changelog'] as $col) {
            $default = in_array($col, ['tags','pipeline_stages','qa_requirements','notifications','subscriptions','version_changelog']) ? '[]' : '{}';
            $worker->$col = json_decode($worker->$col ?? $default, true) ?? [];
        }
        // Normalise credential: always present as array of slots
        if (!empty($worker->credential) && isset($worker->credential['type'])) {
            $worker->credential = [$worker->credential];
        }

        return view('admin.worker-builder.form', [
            'worker'   => $worker,
            'statuses' => $this->statuses,
        ]);
    }

    public function update(Request $request, string $slug)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:60|regex:/^[a-z0-9\-]+$/|unique:worker_registry,slug,' . DB::table('worker_registry')->where('slug', $slug)->value('id'),
        ]);

        $payload = array_merge($this->buildPayload($request), [
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        DB::table('worker_registry')->where('slug', $slug)->update($payload);

        return back()->with('success', 'Worker DNA saved.');
    }

    // ── Status ───────────────────────────────────────────────────────────────

    public function updateStatus(Request $request, string $slug)
    {
        $request->validate(['status' => 'required|in:registered,scaffolded,in_development,testing,published,retired']);

        $updates = ['status' => $request->status, 'updated_at' => now()];
        if ($request->status === 'published') {
            $updates['published_at'] = now();
        }

        DB::table('worker_registry')->where('slug', $slug)->update($updates);

        return back()->with('success', 'Status updated to ' . $request->status . '.');
    }

    // ── Scaffold Generator ───────────────────────────────────────────────────

    public function generateScaffold(string $slug)
    {
        $row = DB::table('worker_registry')->where('slug', $slug)->firstOrFail();

        $stages    = json_decode($row->pipeline_stages ?? '[]', true) ?? [];
        $qa        = json_decode($row->qa_requirements ?? '[]', true) ?? [];
        $org       = json_decode($row->org ?? '{}', true) ?? [];
        $cred      = json_decode($row->credential ?? '{}', true) ?? [];
        $instances = json_decode($row->instances ?? '{}', true) ?? [];
        $depFields = json_decode($row->deployment_fields ?? '[]', true) ?? [];
        $trainSch  = json_decode($row->train_schema ?? '[]', true) ?? [];
        $tags      = json_decode($row->tags ?? '[]', true) ?? [];
        $owner     = json_decode($row->owner ?? '{}', true) ?? [];
        $media     = json_decode($row->media ?? '{}', true) ?? [];
        $notifs    = json_decode($row->notifications ?? '[]', true) ?? [];

        $className  = Str::studly($slug) . 'Worker';
        $namespace  = 'App\\Workers\\' . Str::studly($slug);
        $workerDir  = app_path('Workers/' . Str::studly($slug));
        $jobsDir    = $workerDir . '/Jobs';

        // Create directories
        if (!is_dir($jobsDir)) {
            mkdir($jobsDir, 0755, true);
        }

        // Generate job stubs
        $jobImports = [];
        foreach ($stages as $stage) {
            if (empty($stage['job_class'])) continue;
            $jobClass = Str::studly($stage['job_class']);
            $jobImports[] = "use {$namespace}\\Jobs\\{$jobClass};";
            $this->writeJobStub($jobsDir, $namespace, $jobClass, $stage);
        }

        // Build pipeline() method body
        $pipelineEntries = [];
        $total = count($stages);
        foreach ($stages as $i => $stage) {
            $stageNum  = $i + 1;
            $jobClass  = !empty($stage['job_class']) ? Str::studly($stage['job_class']) : 'null';
            $jobFqn    = !empty($stage['job_class']) ? "{$namespace}\\Jobs\\" . Str::studly($stage['job_class']) . "::class" : 'null';
            $prevLabel = $i === 0 ? 'input' : ($stages[$i - 1]['label'] ?? 'Previous Stage');
            $nextFqn   = isset($stages[$i + 1]) && !empty($stages[$i + 1]['job_class'])
                ? "{$namespace}\\Jobs\\" . Str::studly($stages[$i + 1]['job_class']) . "::class"
                : 'null';

            $pipelineEntries[] = <<<PHP
            [
                'stage'         => {$stageNum},
                'total'         => {$total},
                'job'           => {$jobFqn},
                'label'         => '{$stage['label']}',
                'receives_from' => '{$prevLabel}',
                'accepts'       => [],
                'produces'      => [],
                'connects_to'   => {$nextFqn},
                'can_emit'      => [],
            ],
PHP;
        }

        // Build prompts() method body
        $promptEntries = [];
        foreach ($stages as $stage) {
            $usesAi      = !empty($stage['uses_ai']) ? 'true' : 'false';
            $model       = !empty($stage['model']) ? "'" . addslashes($stage['model']) . "'" : 'null';
            $system      = addslashes($stage['system_prompt'] ?? '');
            $user        = addslashes($stage['user_prompt'] ?? '');
            $outFormat   = $stage['output_format'] ?? 'json';
            $maxTokens   = (int)($stage['max_tokens'] ?? 500);

            $promptEntries[] = <<<PHP
            [
                'stage'        => '{$stage['key']}',
                'label'        => '{$stage['label']}',
                'uses_ai'      => {$usesAi},
                'model'        => {$model},
                'system'       => '{$system}',
                'user'         => '{$user}',
                'output_format' => '{$outFormat}',
                'output_shape' => [],
                'max_tokens'   => {$maxTokens},
            ],
PHP;
        }

        // Build qaRequirements() method body
        $qaEntries = [];
        foreach ($qa as $q) {
            $field     = !empty($q['field']) ? "'" . addslashes($q['field']) . "'" : 'null';
            $threshold = isset($q['threshold']) && $q['threshold'] !== '' ? (float)$q['threshold'] : 'null';
            $values    = !empty($q['values']) ? "['" . implode("','", (array)$q['values']) . "']" : '[]';

            $qaEntries[] = <<<PHP
            [
                'stage'     => '{$q['stage']}',
                'check'     => \App\Platform\Enums\QACheck::{$q['check']},
                'label'     => '{$q['label']}',
                'field'     => {$field},
                'threshold' => {$threshold},
                'values'    => {$values},
            ],
PHP;
        }

        // Build pipelineStages() for fast track visual
        $pipelineStageEntries = [];
        foreach ($stages as $stage) {
            $jobShort = !empty($stage['job_class']) ? "'" . Str::studly($stage['job_class']) . "'" : 'null';
            $icon     = $stage['icon'] ?? 'check';
            $sub      = addslashes($stage['sub'] ?? $stage['label'] ?? '');
            $pipelineStageEntries[] = <<<PHP
            ['key' => '{$stage['key']}', 'label' => '{$stage['label']}', 'sub' => '{$sub}', 'icon' => '{$icon}', 'job_class' => {$jobShort}],
PHP;
        }

        // Helper closures for array-to-PHP
        $phpArray = fn(array $a) => empty($a) ? '[]' : "[\n            '" . implode("',\n            '", array_map('addslashes', $a)) . "',\n        ]";
        $phpAssoc = function (array $a) {
            $lines = [];
            foreach ($a as $k => $v) {
                $val = is_bool($v) ? ($v ? 'true' : 'false') :
                       (is_null($v) ? 'null' :
                       (is_int($v) || is_float($v) ? $v : "'" . addslashes((string)$v) . "'"));
                $lines[] = "            '{$k}' => {$val},";
            }
            return "[\n" . implode("\n", $lines) . "\n        ]";
        };

        $subscriptions = json_decode($row->subscriptions ?? '[]', true) ?? [];
        $changelog     = json_decode($row->version_changelog ?? '[]', true) ?? [];

        // Build subscriptions() entries
        $subEntries = [];
        foreach ($subscriptions as $s) {
            $req = !empty($s['required']) ? 'true' : 'false';
            $subEntries[] = "            ['event' => '{$s['event']}', 'from_worker' => '{$s['from_worker']}', 'description' => '" . addslashes($s['description'] ?? '') . "', 'handler_stage' => '{$s['handler_stage']}', 'required' => {$req}],";
        }
        $subBlock = implode("\n", $subEntries);

        // Build versionChangelog() entries
        $clEntries = [];
        foreach ($changelog as $c) {
            $breaking = !empty($c['breaking']) ? 'true' : 'false';
            $steps = !empty($c['upgrade_steps']) ? "['" . implode("','", array_map('addslashes', (array)$c['upgrade_steps'])) . "']" : '[]';
            $clEntries[] = "            ['version' => '{$c['version']}', 'date' => '{$c['date']}', 'notes' => '" . addslashes($c['notes'] ?? '') . "', 'breaking' => {$breaking}, 'breaking_reason' => '" . addslashes($c['breaking_reason'] ?? '') . "', 'upgrade_steps' => {$steps}],";
        }
        $clBlock = implode("\n", $clEntries);

        $importBlock    = implode("\n", array_unique($jobImports));
        $pipelineBlock  = implode("\n", $pipelineEntries);
        $promptBlock    = implode("\n", $promptEntries);
        $qaBlock        = implode("\n", $qaEntries);
        $psBlock        = implode("\n", $pipelineStageEntries);

        $orgPhp        = $phpAssoc(array_merge(['name'=>'','abbreviation'=>null,'type'=>'platform','website'=>'','logo'=>''], $org));
        // credential is stored as array of slots — render each as phpAssoc
        $credSlots = is_array($cred) && isset($cred[0]) ? $cred : [$cred]; // normalise legacy single-object
        $credItems = array_map(fn($slot) => "            " . $phpAssoc(array_merge(
            ['key'=>'default','type'=>'none','label'=>'','hint'=>'','required'=>true,'multiple'=>false,'connect_route'=>'','authorize_route'=>''],
            (array)$slot
        )) . ",", $credSlots);
        $credPhp = "[\n" . implode("\n", $credItems) . "\n        ]";
        $instancesPhp  = $phpAssoc(array_merge(['multiple'=>false,'min'=>1,'max'=>1,'label'=>'deployment','rationale'=>''], $instances));
        $tagsPhp       = $phpArray($tags);
        $ownerPhp      = $phpAssoc(array_merge(['type'=>'platform','name'=>'UNIT','contact'=>'hello@unit.report','website'=>'https://unit.report','license'=>'proprietary','sla'=>'','since'=>date('Y'),'verified'=>true], $owner));
        $mediaPhp      = $phpAssoc(array_merge(['color'=>'#f1d362','quote'=>'','avatar'=>null,'banner'=>null], $media));

        $stub = <<<PHP
<?php

namespace {$namespace};

use App\Platform\Contracts\WorkerContract;
use App\Platform\Enums\QACheck;
{$importBlock}

class {$className} implements WorkerContract
{
    // ── Block 1: Identity ────────────────────────────────────────────────────

    public function identity(): array
    {
        return [
            'name'        => '{$row->name}',
            'slug'        => '{$row->slug}',
            'version'     => '{$row->version}',
            'description' => '{$row->description}',
        ];
    }

    public function org(): array
    {
        return {$orgPhp};
    }

    public function demoPayload(): array
    {
        return [
            'source'     => 'public_demo',
            'message_id' => 'demo_' . substr(md5('{$row->slug}-demo'), 0, 12),
        ];
    }

    // ── Block 2: Onboarding Requirements ────────────────────────────────────

    public function platformRequirements(): array
    {
        return ['email'];
    }

    public function onboardingSteps(): array
    {
        return [];
    }

    // ── Block 2: Deployment DNA ──────────────────────────────────────────────

    public function instances(): array
    {
        return {$instancesPhp};
    }

    public function credential(): array
    {
        return {$credPhp};
    }

    public function deploymentFields(): array
    {
        return [];
    }

    public function trainSchema(): array
    {
        return [];
    }

    public function tags(): array
    {
        return {$tagsPhp};
    }

    public function media(): array
    {
        return {$mediaPhp};
    }

    public function fastTrack(): array
    {
        return [
            'source' => 'fast_track_test',
        ];
    }

    public function fastTrackOutcome(): array
    {
        return [
            'headline'      => 'Your first run completed.',
            'what_happened' => [],
            'where_to_find' => ['label' => 'Transactions', 'hint' => 'View the pipeline output'],
            'going_forward' => 'The worker will now run automatically.',
        ];
    }

    public function pipelineStages(): array
    {
        return [
{$psBlock}
        ];
    }

    // ── Block 3: Pipeline ────────────────────────────────────────────────────

    public function input(): array
    {
        return [
            'description' => 'TODO: describe the worker input',
            'source'      => 'TODO: describe the source',
            'fields'      => [],
        ];
    }

    public function pipeline(): array
    {
        return [
{$pipelineBlock}
        ];
    }

    public function emit(): array
    {
        return [];
    }

    public function commit(): ?array
    {
        return null;
    }

    public function subscriptions(): array
    {
        return [
{$subBlock}
        ];
    }

    public function versionChangelog(): array
    {
        return [
{$clBlock}
        ];
    }

    // ── Block 3b: Notifications ─────────────────────────────────────────────

    public function notifications(): array
    {
        return [];
    }

    public function overview(): array
    {
        return ['panels' => [
            ['type' => 'action_queue',  'title' => 'Awaiting Your Review', 'empty' => 'Nothing awaiting review.', 'priority' => 1],
            ['type' => 'metric_strip',  'title' => 'This Month', 'period' => 'month', 'metrics' => ['emails_processed','drafts_ready','approved_sent','hours_saved'], 'priority' => 2],
            ['type' => 'activity_feed', 'title' => 'Recent Activity', 'limit' => 8, 'priority' => 3],
        ]];
    }

    // ── Block 3d: Dashboard Surface ─────────────────────────────────────────

    public function dashboard(): array
    {
        return [
            'accent' => 'violet',
            'icon'   => 'M13 10V3L4 14h7v7l9-11h-7z',
            'stats'  => [
                ['key' => 'drafts',  'label' => 'Drafts Ready', 'query' => 'tx_draft_ready'],
                ['key' => 'today',   'label' => 'Today',        'query' => 'tx_today'],
                ['key' => 'failed',  'label' => 'Failed',       'query' => 'tx_failed'],
            ],
        ];
    }

    // ── Block 4: Quality ────────────────────────────────────────────────────

    public function qaRequirements(): array
    {
        return [
{$qaBlock}
        ];
    }

    // ── Block 5: Output ──────────────────────────────────────────────────────

    public function output(): array
    {
        return [
            'description'  => 'TODO: describe the final output',
            'destination'  => 'TODO: where it lands',
            'format'       => 'document',
            'fields'       => [],
            'human_action' => 'TODO: what a human does with this',
            'auto_action'  => null,
        ];
    }

    // ── Block 6: Prompts ─────────────────────────────────────────────────────

    public function prompts(): array
    {
        return [
{$promptBlock}
        ];
    }

    // ── Block 7: Owner ───────────────────────────────────────────────────────

    public function owner(): array
    {
        return {$ownerPhp};
    }
}
PHP;

        file_put_contents("{$workerDir}/{$className}.php", $stub);

        DB::table('worker_registry')->where('slug', $slug)->update([
            'status'               => 'scaffolded',
            'folder_path'          => "app/Workers/" . Str::studly($slug),
            'scaffold_generated_at' => now(),
            'updated_at'           => now(),
        ]);

        return back()->with('success', "Scaffold generated at app/Workers/" . Str::studly($slug) . "/. Status set to scaffolded.");
    }

    // ── Delete ───────────────────────────────────────────────────────────────

    public function exportSchema(string $slug)
    {
        $row = DB::table('worker_registry')->where('slug', $slug)->firstOrFail();

        // ── Resolve contract for live method data ─────────────────────────────
        $contract   = \App\Platform\Services\WorkerRegistry::resolve($slug);
        $hasContract = !\App\Platform\Services\WorkerRegistry::isNull($contract);

        // ── Core DNA from DB row ──────────────────────────────────────────────
        $pipeline  = json_decode($row->pipeline_stages   ?? '[]', true) ?: [];
        $qa        = json_decode($row->qa_requirements   ?? '[]', true) ?: [];
        $creds     = json_decode($row->credential        ?? '[]', true) ?: [];
        $depFields = json_decode($row->deployment_fields ?? '[]', true) ?: [];
        $tags      = json_decode($row->tags              ?? '[]', true) ?: [];
        $media     = json_decode($row->media             ?? '[]', true) ?: [];
        $notifs    = json_decode($row->notifications     ?? '[]', true) ?: [];
        $changelog = json_decode($row->version_changelog ?? '[]', true) ?: [];

        // ── AI calls — read from contract prompts(), not hardcoded stage keys ─
        $aiCallMap = [];
        if ($hasContract) {
            try {
                foreach ($contract->prompts() as $prompt) {
                    $key = $prompt['stage'] ?? null;
                    if (!$key) continue;
                    $aiCallMap[$key] = [
                        'has_ai'      => $prompt['uses_ai'] ?? false,
                        'provider'    => 'anthropic',
                        'model'       => $prompt['model'] ?? 'deployment.ai_model (per tenant)',
                        'max_tokens'  => $prompt['max_tokens'] ?? null,
                        'output_format' => $prompt['output_format'] ?? null,
                    ];
                }
            } catch (\Throwable) {}
        }

        // ── Platform integration methods from contract ─────────────────────────
        $platformIntegration = [];
        if ($hasContract) {
            try {
                $platformIntegration = [
                    'ingest_job_class'    => $contract->ingestJobClass(),
                    'fast_track_job_class'=> $contract->fastTrackJobClass(),
                    'scheduled_jobs'      => $contract->scheduledJobs(),
                    'stuck_recovery_map'  => $contract->stuckRecoveryMap(),
                ];
            } catch (\Throwable) {}
        }

        // ── Pricing tiers from worker_pricing ────────────────────────────────
        $pricing = DB::table('worker_pricing')
            ->where('worker_slug', $slug)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($p) => [
                'plan'                  => $p->plan_slug ?? 'default',
                'display_name'          => $p->display_name ?? null,
                'monthly_flat_rate'     => $p->monthly_flat_rate,
                'overage_price_per_tx'  => $p->overage_price_per_tx ?? null,
                'included_transactions' => $p->included_transactions ?? null,
                'free_transactions'     => $p->free_transactions ?? null,
                'transaction_limit'     => $p->transaction_limit ?? null,
                'prompt_overrides'      => (bool) ($p->prompt_overrides ?? false),
                'stripe_flat_price_id'  => $p->stripe_flat_price_id ?? null,
            ])->toArray();

        // ── Trial limit from worker_pricing (authoritative source) ────────────
        $defaultTrialLimit = DB::table('worker_pricing')
            ->where('worker_slug', $slug)
            ->value('free_transactions') ?? 25;

        // ── Live deployment sample (first deployment as example + summary) ────
        $totalDeployments  = DB::table('worker_deployments')->where('worker_slug', $slug)->count();
        $activeDeployments = DB::table('worker_deployments')->where('worker_slug', $slug)->where('status', 'active')->count();

        $sampleDep = DB::table('worker_deployments')
            ->where('worker_slug', $slug)
            ->orderBy('id')
            ->first();

        $deploymentSample = null;
        if ($sampleDep) {
            $config  = json_decode($sampleDep->config ?? '{}', true) ?: [];
            $capture = $config['capture'] ?? [];
            $billing = DB::table('deployment_billing')->where('deployment_id', $sampleDep->id)->first();

            $inboxCount = DB::table('deployment_credentials')
                ->where('deployment_id', $sampleDep->id)
                ->count();

            $overrideCount = DB::table('deployment_prompt_overrides')
                ->where('deployment_id', $sampleDep->id)
                ->count();

            $txStats = DB::table('transactions')
                ->where('deployment_id', $sampleDep->id)
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $deploymentSample = [
                '_note'          => 'Sample deployment — illustrative only. All deployments share this contract.',
                'deployment_id'  => $sampleDep->id,
                'name'           => $sampleDep->name,
                'status'         => $sampleDep->status,
                'ai_model'       => $config['ai_model'] ?? 'claude-sonnet-4-6',
                'billing_status' => $billing?->status ?? 'unknown',
                'trial_tx_used'  => $billing?->trial_transactions_used ?? 0,
                'trial_tx_limit' => $billing?->trial_transactions_limit ?? $defaultTrialLimit,
                'capture_guardrails' => [
                    'scope'            => $capture['capture_scope']        ?? null,
                    'keywords'         => $capture['capture_keywords']     ?? [],
                    'require_all_kw'   => $capture['capture_require_all']  ?? false,
                    'allowed_domains'  => $capture['capture_domains']      ?? [],
                    'allowed_senders'  => $capture['capture_senders_only'] ?? [],
                    'excluded_senders' => $capture['exclude_senders']      ?? [],
                ],
                'connected_inboxes_count' => $inboxCount,
                'prompt_overrides_count'  => $overrideCount,
                'transaction_summary'     => $txStats,
                'deployed_at'            => $sampleDep->created_at,
            ];
        }

        // ── Assemble blueprint ────────────────────────────────────────────────
        $schema = [
            '_meta' => [
                'exported_at'    => now()->toISOString(),
                'exported_by'    => auth()->user()->email ?? 'admin',
                'platform'       => 'UNIT',
                'schema_version' => '2.0',
                'purpose'        => 'Worker blueprint — use for auditing, refactoring, or building new workers',
                'contract_class' => $row->worker_class ?? null,
                'contract_live'  => $hasContract,
            ],
            'identity' => [
                'name'             => $row->name,
                'slug'             => $row->slug,
                'version'          => $row->version,
                'description'      => $row->description,
                'lifecycle_status' => $row->lifecycle_status ?? $row->status,
                'org'              => $row->org,
                'owner'            => $row->owner,
                'folder_path'      => $row->folder_path,
                'tags'             => $tags,
                'created_at'       => $row->created_at,
                'published_at'     => $row->published_at,
            ],
            'pipeline' => [
                'stages'          => $pipeline,
                'ai_calls'        => $aiCallMap,
                'qa_requirements' => $qa,
            ],
            'platform_integration' => $platformIntegration,
            'credentials_schema'       => $creds,
            'deployment_config_fields' => $depFields,
            'pricing_tiers'            => $pricing,
            'notifications'            => $notifs,
            'version_changelog'        => $changelog,
            'media'                    => $media,
            'live_deployments' => [
                'total'   => $totalDeployments,
                'active'  => $activeDeployments,
                'sample'  => $deploymentSample,
            ],
        ];

        $filename = $slug . '-schema-' . now()->format('Ymd-His') . '.json';

        return response(json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function destroy(string $slug)
    {
        $row = DB::table('worker_registry')->where('slug', $slug)->first();
        if (!$row) return back()->with('error', 'Worker not found.');
        if ($row->status === 'published') return back()->with('error', 'Cannot delete a published worker. Retire it first.');

        DB::table('worker_registry')->where('slug', $slug)->delete();
        return redirect()->route('admin.workers.index')->with('success', "Worker [{$slug}] deleted.");
    }

    // ── Media (AJAX) ─────────────────────────────────────────────────────────

    public function saveMedia(Request $request, string $slug)
    {
        $row = DB::table('worker_registry')->where('slug', $slug)->first();
        if (!$row) return response()->json(['error' => 'Worker not found.'], 404);

        // Debug: return exactly what PHP sees for the upload
        $debugFiles = [];
        foreach ($request->file('media_items', []) as $i => $item) {
            foreach ($item as $k => $f) {
                $debugFiles[$i][$k] = $f instanceof \Illuminate\Http\UploadedFile
                    ? ['valid'=>$f->isValid(),'error'=>$f->getError(),'size'=>$f->getSize(),'mime'=>$f->getMimeType(),'name'=>$f->getClientOriginalName()]
                    : gettype($f);
            }
        }
        \Illuminate\Support\Facades\Log::info('saveMedia files', ['files'=>$debugFiles,'input_keys'=>array_keys($request->input('media_items',[])[0]??[])]);

        try {
            $request->validate([
                'media_items.*.file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:20480',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => collect($e->errors())->flatten()->first(), 'debug_files' => $debugFiles], 422);
        }

        [$mediaItems, $profileImage, $coverImage] = $this->processMediaItems($request, $slug);

        DB::table('worker_registry')->where('slug', $slug)->update([
            'gallery'       => json_encode($mediaItems),
            'profile_image' => $profileImage,
            'cover_image'   => $coverImage,
            'updated_at'    => now(),
        ]);

        return response()->json([
            'success' => true,
            'count'   => count($mediaItems),
            'items'   => $mediaItems,
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function processMediaItems(Request $request, string $slug): array
    {
        $urlTypes = ['youtube_intro', 'youtube_pipeline'];
        $items = [];
        $profileImage = null;
        $coverImage   = null;

        $submittedItems = $request->input('media_items', []);
        $uploadedFiles  = $request->file('media_items', []);

        foreach ($submittedItems as $i => $item) {
            $type    = $item['type']    ?? 'gallery';
            $kind    = $item['kind']    ?? 'file';
            $caption = $item['caption'] ?? '';

            if (in_array($type, $urlTypes, true)) {
                // URL-based media (YouTube)
                $url = trim($item['url'] ?? '');
                if (!$url) continue;
                $items[] = ['type' => $type, 'kind' => 'url', 'url' => $url, 'caption' => $caption];
            } else {
                // File-based media
                $existingPath = $item['path'] ?? '';
                $file = $uploadedFiles[$i]['file'] ?? null;

                if ($file && $file->isValid()) {
                    if ($existingPath) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($existingPath);
                    }
                    $path = $file->store("workers/{$slug}/media", 'public');
                } else {
                    $path = $existingPath;
                }

                if (!$path) continue;
                $items[] = ['type' => $type, 'kind' => 'file', 'path' => $path, 'caption' => $caption];

                if ($type === 'profile') $profileImage = $path;
                if ($type === 'cover')   $coverImage   = $path;
            }
        }

        // Fall back to existing DB values if no profile/cover item submitted
        $existing = DB::table('worker_registry')->where('slug', $slug)->first(['profile_image','cover_image']);
        if ($profileImage === null && $existing) $profileImage = $existing->profile_image ?: null;
        if ($coverImage   === null && $existing) $coverImage   = $existing->cover_image   ?: null;

        // Sync profile/cover from items if not explicitly found above but exists in items
        if ($profileImage === null) {
            $p = collect($items)->firstWhere('type', 'profile');
            $profileImage = $p['path'] ?? null;
        }
        if ($coverImage === null) {
            $c = collect($items)->firstWhere('type', 'cover');
            $coverImage = $c['path'] ?? null;
        }

        return [$items, $profileImage, $coverImage];
    }

    private function buildPayload(Request $request): array
    {
        // Pipeline stages — including branches per stage
        $stages = [];
        foreach ($request->input('stages', []) as $i => $stage) {
            $branches = [];
            foreach ($stage['branches'] ?? [] as $b) {
                if (empty($b['next_stage'])) continue;
                $branches[] = [
                    'condition'   => $b['condition']   ?? '',
                    'next_job'    => $b['next_job']    ?? '',
                    'next_stage'  => $b['next_stage'],
                    'is_default'  => !empty($b['is_default']),
                    'description' => $b['description'] ?? '',
                ];
            }
            $stages[] = [
                'key'           => Str::slug($stage['label'] ?? 'stage-' . ($i + 1)),
                'label'         => $stage['label']         ?? '',
                'sub'           => $stage['sub']           ?? '',
                'job_class'     => $stage['job_class']     ?? '',
                'icon'          => $stage['icon']          ?? 'check',
                'uses_ai'       => !empty($stage['uses_ai']),
                'model'         => $stage['model']         ?? null,
                'system_prompt' => $stage['system_prompt'] ?? '',
                'user_prompt'   => $stage['user_prompt']   ?? '',
                'output_format' => $stage['output_format'] ?? 'json',
                'output_shape'  => $stage['output_shape']  ?? '',
                'max_tokens'    => (int)($stage['max_tokens'] ?? 500),
                'branches'      => $branches,
            ];
        }

        // QA requirements
        $qa = [];
        foreach ($request->input('qa', []) as $q) {
            if (empty($q['stage']) || empty($q['check'])) continue;
            $qa[] = [
                'stage'     => $q['stage'],
                'check'     => $q['check'],
                'label'     => $q['label']     ?? '',
                'field'     => $q['field']     ?? null,
                'threshold' => isset($q['threshold']) && $q['threshold'] !== '' ? (float)$q['threshold'] : null,
                'values'    => !empty($q['values']) ? array_filter(array_map('trim', explode(',', $q['values']))) : [],
            ];
        }

        // Credentials — always stored as array of credential slots
        $credentials = [];
        foreach ($request->input('credentials', []) as $c) {
            if (empty($c['type']) || $c['type'] === 'none') continue;
            $credentials[] = [
                'key'             => Str::slug($c['label'] ?? 'credential-' . count($credentials)),
                'type'            => $c['type'],
                'label'           => $c['label']           ?? '',
                'hint'            => $c['hint']            ?? '',
                'required'        => !empty($c['required']),
                'multiple'        => !empty($c['multiple']),
                'connect_route'   => $c['connect_route']   ?? '',
                'authorize_route' => $c['authorize_route'] ?? '',
            ];
        }
        // If no credentials submitted (old single-cred form), fall back gracefully
        if (empty($credentials) && $request->input('cred_type') && $request->input('cred_type') !== 'none') {
            $credentials[] = [
                'key'             => 'default',
                'type'            => $request->input('cred_type'),
                'label'           => $request->input('cred_label', ''),
                'hint'            => $request->input('cred_hint', ''),
                'required'        => true,
                'multiple'        => (bool)$request->input('cred_multiple'),
                'connect_route'   => $request->input('cred_connect_route', ''),
                'authorize_route' => $request->input('cred_authorize_route', ''),
            ];
        }

        // Subscriptions
        $subscriptions = [];
        foreach ($request->input('subscriptions', []) as $s) {
            if (empty($s['event']) || empty($s['from_worker'])) continue;
            $subscriptions[] = [
                'event'         => $s['event'],
                'from_worker'   => $s['from_worker'],
                'description'   => $s['description']   ?? '',
                'handler_stage' => $s['handler_stage'] ?? '',
                'required'      => !empty($s['required']),
            ];
        }

        // Version changelog
        $changelog = [];
        foreach ($request->input('changelog', []) as $c) {
            if (empty($c['version'])) continue;
            $steps = array_filter(array_map('trim', explode("\n", $c['upgrade_steps'] ?? '')));
            $changelog[] = [
                'version'         => $c['version'],
                'date'            => $c['date'] ?? now()->toDateString(),
                'notes'           => $c['notes']           ?? '',
                'breaking'        => !empty($c['breaking']),
                'breaking_reason' => $c['breaking_reason'] ?? '',
                'upgrade_steps'   => array_values($steps),
            ];
        }

        // Tags
        $tags = array_filter(array_map('trim', explode(',', $request->input('tags_raw', ''))));

        return [
            'name'              => $request->name,
            'slug'              => $request->slug,
            'version'           => $request->version ?? '1.0',
            'description'       => $request->description,
            'org'               => json_encode([
                'name'         => $request->input('org_name'),
                'abbreviation' => $request->input('org_abbreviation') ?: null,
                'type'         => $request->input('org_type', 'platform'),
                'website'      => $request->input('org_website'),
                'logo'         => $request->input('org_logo'),
            ]),
            'pipeline_stages'   => json_encode($stages),
            'qa_requirements'   => json_encode($qa),
            'credential'        => json_encode($credentials),
            'instances'         => json_encode([
                'multiple'  => (bool)$request->input('inst_multiple'),
                'min'       => (int)($request->input('inst_min', 1)),
                'max'       => $request->input('inst_max') ? (int)$request->input('inst_max') : null,
                'label'     => $request->input('inst_label', 'deployment'),
                'rationale' => $request->input('inst_rationale'),
            ]),
            'tags'              => json_encode(array_values($tags)),
            'owner'             => json_encode([
                'type'     => $request->input('owner_type', 'platform'),
                'name'     => $request->input('owner_name', 'UNIT'),
                'contact'  => $request->input('owner_contact'),
                'website'  => $request->input('owner_website'),
                'license'  => $request->input('owner_license', 'proprietary'),
                'sla'      => $request->input('owner_sla'),
                'since'    => (int)($request->input('owner_since', date('Y'))),
                'verified' => (bool)$request->input('owner_verified'),
            ]),
            'media'             => json_encode([
                'color' => $request->input('media_color', '#f1d362'),
                'quote' => $request->input('media_quote'),
            ]),
            'notifications'     => json_encode([]),
            'subscriptions'     => json_encode($subscriptions),
            'version_changelog' => json_encode($changelog),
        ];
    }

    private function writeJobStub(string $dir, string $namespace, string $jobClass, array $stage): void
    {
        $path = "{$dir}/{$jobClass}.php";
        if (file_exists($path)) return;

        $usesAi  = !empty($stage['uses_ai']);
        $aiBlock = $usesAi ? "\n        // TODO: call ClaudeService with prompts from the worker contract" : '';

        $stub = <<<PHP
<?php

namespace {$namespace}\\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class {$jobClass} implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int \$tries   = 3;
    public int \$timeout = 120;

    public function __construct(
        public readonly int   \$transactionId,
        public readonly array \$payload,
    ) {}

    public function handle(): void
    {
        // Stage: {$stage['label']}
        // {$stage['sub']}
{$aiBlock}

        // TODO: implement stage logic
        // On completion, dispatch the next job in the pipeline
    }
}
PHP;
        file_put_contents($path, $stub);
    }
}

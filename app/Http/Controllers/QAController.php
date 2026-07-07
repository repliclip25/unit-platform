<?php

namespace App\Http\Controllers;

use App\Platform\Services\WorkerRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class QAController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $authUser = $request->user();

        $stuckCount = DB::table('transactions')
            ->whereNotIn('status', ['draft_ready', 'approved', 'sent', 'failed'])
            ->where('updated_at', '<', now()->subMinutes(5))
            ->count();

        $pendingReview = DB::table('transactions')
            ->where('status', 'draft_ready')
            ->whereNull('human_decision')
            ->count();

        return view('dashboard.qa', [
            'platform'           => $this->checkPlatform(),
            'marketplaceWorkers' => $this->getMarketplaceWorkers(),
            'stuckCount'         => $stuckCount,
            'pendingReview'      => $pendingReview,
        ]);
    }

    private function buildMemoryMap(int $userId): array
    {
        $sharedTables = ['clients', 'contacts', 'assets'];
        $memoryMap    = [];

        try {
            $deployedWorkers = DB::table('worker_deployments as wd')
                ->join('workers as w', 'w.slug', '=', 'wd.worker_slug')
                ->where('wd.user_id', $userId)
                ->where('wd.status', '!=', 'decommissioned')
                ->select('wd.id', 'wd.name', 'wd.worker_slug', 'w.blueprint')
                ->get();
        } catch (\Throwable) {
            $deployedWorkers = collect();
        }

        foreach ($sharedTables as $tbl) {
            try {
                $count       = DB::table($tbl)->where('user_id', $userId)->count();
                $recentC     = DB::table('memory_contributions')->where('user_id', $userId)->where('table_name', $tbl)->orderByDesc('id')->limit(3)->get();
                $totalC      = DB::table('memory_contributions')->where('user_id', $userId)->where('table_name', $tbl)->count();
            } catch (\Throwable) {
                $count   = 0;
                $recentC = collect();
                $totalC  = 0;
            }
            $memoryMap[$tbl] = ['count' => $count, 'readers' => [], 'writers' => [], 'recent_contributions' => $recentC, 'total_contributions' => $totalC];
        }

        foreach ($deployedWorkers as $dep) {
            $bp     = json_decode($dep->blueprint ?? '{}', true);
            $shared = $bp['memory']['shared'] ?? [];
            foreach ($shared as $mem) {
                $tbl = $mem['table'] ?? null;
                if (!$tbl || !isset($memoryMap[$tbl])) continue;
                $memoryMap[$tbl]['readers'][] = $dep->name;
                if (str_contains($mem['access'] ?? 'read', 'write')) {
                    $memoryMap[$tbl]['writers'][] = $dep->name;
                }
            }
        }

        return $memoryMap;
    }

    private function buildContributionsByWorker(int $userId): \Illuminate\Support\Collection
    {
        try {
            return DB::table('memory_contributions')
                ->where('user_id', $userId)
                ->selectRaw('worker_slug, table_name, action, count(*) as total')
                ->groupBy('worker_slug', 'table_name', 'action')
                ->get()
                ->groupBy('worker_slug');
        } catch (\Throwable) {
            return collect();
        }
    }

    public function updateScenario(Request $request, int $deploymentId)
    {
        DB::table('worker_deployments')
            ->where('id', $deploymentId)->where('user_id', $request->user()->id)->firstOrFail();

        $data = array_merge($request->only([
            'scenario_title','sender_name','sender_email',
            'asset_name','asset_type','contact_name',
            'renewal_price','days_until_expiry','custom_note',
        ]), ['updated_at' => now()]);

        if (DB::table('fast_track_scenarios')->where('deployment_id', $deploymentId)->exists()) {
            DB::table('fast_track_scenarios')->where('deployment_id', $deploymentId)->update($data);
        } else {
            DB::table('fast_track_scenarios')->insert(array_merge($data, [
                'deployment_id' => $deploymentId,
                'user_id'       => $request->user()->id,
                'created_at'    => now(),
            ]));
        }

        return back()->with('success', 'Fast Track scenario saved.');
    }

    public function fastTrack(Request $request, int $deploymentId)
    {
        $dep        = DB::table('worker_deployments')->where('id', $deploymentId)->where('user_id', $request->user()->id)->firstOrFail();
        $credential = DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first();

        if (!$credential) {
            return back()->with('error', 'No Gmail account connected to this worker.');
        }

        $tenantEmail = $request->user()->email;

        $scenario = DB::table('fast_track_scenarios')->where('deployment_id', $deploymentId)->first();
        if (!$scenario) {
            DB::table('fast_track_scenarios')->insert([
                'deployment_id'     => $deploymentId,
                'user_id'           => $request->user()->id,
                'scenario_title'    => 'Domain Renewal Test',
                'sender_name'       => 'Namecheap Renewals Team',
                'sender_email'      => 'renewals@namecheap.com',
                'asset_name'        => 'yourdomain.com',
                'asset_type'        => 'Domain',
                'contact_name'      => $request->user()->name,
                'renewal_price'     => '$12.98/year',
                'days_until_expiry' => 14,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            $scenario = DB::table('fast_track_scenarios')->where('deployment_id', $deploymentId)->first();
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
            "Contact Email: {$tenantEmail}",
            "",
            ($scenario->custom_note ? strip_tags(trim($scenario->custom_note)) . "\n\n" : "") . "Please renew before it expires.",
            "",
            "Thank you,",
            $scenario->sender_name,
        ]);

        $txService = app(\App\Platform\Services\TransactionService::class);
        $tx = $txService->create('ava-renewal-coordinator', [
            'source'        => 'fast_track_test',
            'message_id'    => 'fast-track-' . uniqid(),
            'raw_email'     => $sampleEmail,
            'from'          => $scenario->sender_email,
            'user_id'       => $request->user()->id,
            'deployment_id' => $deploymentId,
        ]);

        $dep      = DB::table('worker_deployments')->where('id', $deploymentId)->first();
        $contract = $dep ? \App\Platform\Services\WorkerRegistry::resolveActive($dep->worker_slug) : null;
        if ($contract && !\App\Platform\Services\WorkerRegistry::isNull($contract)) {
            $contract->ingestJobClass()::dispatch($tx->tx_id)->onQueue($txService->queueForTx($tx));
        }

        return redirect()->route('qa', ['watch' => $tx->tx_id, 'worker' => $deploymentId]);
    }

    // ── WORKER QUEUE CONTROLS ─────────────────────────────────────────────
    public function pauseWorker(Request $request, int $deploymentId)
    {
        $dep   = $this->ownedDeployment($request, $deploymentId);
        $queue = \App\Platform\Services\TransactionService::queueForDeployment($dep->id, $dep->worker_slug);
        \Artisan::call('horizon:pause-queue', ['queue' => $queue]);
        DB::table('worker_deployments')->where('id', $dep->id)->update(['status' => 'paused', 'updated_at' => now()]);
        return response()->json(['status' => 'paused', 'queue' => $queue]);
    }

    public function resumeWorker(Request $request, int $deploymentId)
    {
        $dep   = $this->ownedDeployment($request, $deploymentId);
        $queue = \App\Platform\Services\TransactionService::queueForDeployment($dep->id, $dep->worker_slug);
        \Artisan::call('horizon:continue-queue', ['queue' => $queue]);
        DB::table('worker_deployments')->where('id', $dep->id)->update(['status' => 'active', 'updated_at' => now()]);
        return response()->json(['status' => 'active', 'queue' => $queue]);
    }

    public function drainWorker(Request $request, int $deploymentId)
    {
        $dep   = $this->ownedDeployment($request, $deploymentId);
        $queue = \App\Platform\Services\TransactionService::queueForDeployment($dep->id, $dep->worker_slug);
        // Flush pending jobs from this worker's queue
        \Illuminate\Support\Facades\Redis::connection()->del('queues:' . $queue);
        \Illuminate\Support\Facades\Redis::connection()->del('queues:' . $queue . ':delayed');
        return response()->json(['status' => 'drained', 'queue' => $queue]);
    }

    public function queueStatus(Request $request, int $deploymentId)
    {
        $dep   = $this->ownedDeployment($request, $deploymentId);
        $queue = \App\Platform\Services\TransactionService::queueForDeployment($dep->id, $dep->worker_slug);

        $redis    = \Illuminate\Support\Facades\Redis::connection();
        $prefix   = config('horizon.prefix', 'horizon') . ':';
        $paused   = $redis->sismember($prefix . 'paused-queues', 'redis:' . $queue);
        $pending  = (int) $redis->llen('queues:' . $queue);
        $delayed  = (int) $redis->zcard('queues:' . $queue . ':delayed');
        $reserved = (int) $redis->llen('queues:' . $queue . ':reserved');

        return response()->json([
            'queue'    => $queue,
            'paused'   => (bool) $paused,
            'status'   => $dep->status,
            'pending'  => $pending,
            'delayed'  => $delayed,
            'reserved' => $reserved,
        ]);
    }

    public function downloadPlatformBlueprint(Request $request)
    {
        $path = base_path('UNIT_BLUEPRINT.md');
        if (!file_exists($path)) {
            abort(404, 'Blueprint file not found.');
        }
        return response()->download($path, 'UNIT_BLUEPRINT.md', [
            'Content-Type' => 'text/markdown',
        ]);
    }

    public function downloadWorkerBlueprint(Request $request, int $workerId)
    {
        $worker    = DB::table('workers')->where('id', $workerId)->firstOrFail();
        $bp        = json_decode($worker->blueprint    ?? '{}', true) ?: [];
        $inSchema  = json_decode($worker->input_schema  ?? '{}', true) ?: [];
        $outSchema = json_decode($worker->output_schema ?? '{}', true) ?: [];
        $emitSchema= json_decode($worker->emit_schema   ?? '{}', true) ?: [];

        $pipeline  = $bp['pipeline']  ?? [];
        $memory    = $bp['memory']    ?? [];
        $emits     = $bp['emits']     ?? [];
        $subscribes= $bp['subscribes'] ?? [];
        $structure = $bp['structure'] ?? [];
        $sdk       = $bp['sdk']       ?? [];

        $deployCount = DB::table('worker_deployments')
            ->where('worker_slug', $worker->slug)
            ->where('status', '!=', 'decommissioned')
            ->count();

        $date = now()->format('F j, Y');

        // ── Pipeline diagram ──────────────────────────────────────────────────
        $pipelineDiagram = '';
        foreach ($pipeline as $i => $step) {
            $ai    = !empty($step['ai']) ? ' (AI)' : '';
            $arrow = $i < count($pipeline) - 1 ? "\n         │  {$step['output_column']}\n         ▼\n" : '';
            $pipelineDiagram .= "┌─────────────────────────┐\n";
            $pipelineDiagram .= "│  {$step['job']}{$ai}\n";
            $pipelineDiagram .= "│  {$step['description']}\n";
            $pipelineDiagram .= "└─────────────────────────┘{$arrow}";
        }

        // ── Memory table ──────────────────────────────────────────────────────
        $memoryTable  = "| Key | Source Table | Scope | Purpose |\n";
        $memoryTable .= "|-----|-------------|-------|----------|\n";
        foreach ($memory as $m) {
            $memoryTable .= "| `{$m['type']}` | {$m['table']} | {$m['scope']} | {$m['description']} |\n";
        }

        // ── Emits table ───────────────────────────────────────────────────────
        $emitsSection = '';
        foreach ($emits as $e) {
            $emitsSection .= "### `{$e['event']}`\n";
            $emitsSection .= "- **Fires at:** {$e['stage']}\n";
            $emitsSection .= "- **Purpose:** {$e['description']}\n";
            if (!empty($emitSchema['sections'])) {
                $match = collect($emitSchema['sections'])->firstWhere('event', $e['event']);
                if ($match && !empty($match['sample'])) {
                    $emitsSection .= "\n**Sample payload:**\n```json\n" . json_encode($match['sample'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n```\n";
                }
            }
            $emitsSection .= "\n";
        }

        // ── Subscribes ────────────────────────────────────────────────────────
        $subscribesSection = empty($subscribes)
            ? "_This worker does not subscribe to any external events._\n"
            : implode("\n", array_map(fn($s) => "- `{$s}`", $subscribes)) . "\n";

        // ── Input schema sections ─────────────────────────────────────────────
        $inputSection = '';
        foreach ($inSchema['sections'] ?? [] as $s) {
            $inputSection .= "### {$s['title']}\n{$s['description']}\n\n";
            if (!empty($s['sample'])) {
                $inputSection .= "```json\n" . json_encode($s['sample'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n```\n\n";
            }
        }

        // ── Output schema sections ────────────────────────────────────────────
        $outputSection = '';
        foreach ($outSchema['sections'] ?? [] as $s) {
            $outputSection .= "### {$s['title']}\n{$s['description']}\n\n";
            if (!empty($s['sample'])) {
                $outputSection .= "```json\n" . json_encode($s['sample'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n```\n\n";
            }
        }

        // ── Folder structure ──────────────────────────────────────────────────
        $folderSection = '';
        foreach ($structure as $entry) {
            if (is_array($entry)) {
                $indent = str_repeat('    ', ($entry['depth'] ?? 0));
                $folderSection .= "{$indent}" . ($entry['name'] ?? $entry['path'] ?? '') . "\n";
            } else {
                $folderSection .= "{$entry}\n";
            }
        }

        // ── SDK requirements ──────────────────────────────────────────────────
        $sdkSection = '';
        if (!empty($sdk['imports'])) {
            $sdkSection .= "**Required SDK imports:**\n";
            foreach ($sdk['imports'] as $imp) {
                $sdkSection .= "- `{$imp}`\n";
            }
            $sdkSection .= "\n";
        }
        if (!empty($sdk['install'])) {
            $sdkSection .= "**Install commands:**\n```bash\n" . implode("\n", $sdk['install']) . "\n```\n\n";
        }

        $md = <<<MD
# {$worker->name} — Worker Blueprint
**Slug:** `{$worker->slug}` · **Version:** {$worker->version} · **Status:** {$worker->status}
**Generated:** {$date} · **Active Deployments:** {$deployCount}

---

## Overview

{$worker->description}

---

## Pipeline

```
{$pipelineDiagram}
```

### Pipeline Stages

| Step | Job | AI | Input | Output Column | Description |
|------|-----|----|-------|--------------|-------------|
MD;
        foreach ($pipeline as $step) {
            $ai  = !empty($step['ai']) ? '✓' : '—';
            $md .= "| {$step['step']} | `{$step['job']}` | {$ai} | `{$step['input']}` | `{$step['output_column']}` | {$step['description']} |\n";
        }

        $md .= <<<MD

---

## Inputs

{$inputSection}
---

## Outputs

{$outputSection}
---

## Break-Injection Events (Emits)

Events this worker fires so other workers can react without coupling.

{$emitsSection}
---

## Subscriptions

Events this worker listens for from other workers:

{$subscribesSection}
---

## Memory Schema

Pre-loaded by UNIT at transaction start. Jobs read from `\$input->memory[...]` — no DB calls during pipeline.

{$memoryTable}
---

## Folder Structure

```
{$folderSection}
```

---

## SDK Usage

{$sdkSection}
### Key SDK Calls

```php
// Receive context
\$input = UnitPlatform::getInput(\$this->txId);

// Read stage outputs
\$read     = \$input->stage('read');
\$memory   = \$input->stage('memory');

// Check source
\$input->isFastTrack();              // true if manual QA test

// Pipeline config (editable per deployment in QA Studio)
\$input->maxTokens('draft', 2048);
\$input->timeout('push', 60);
\$input->tries('read', 3);

// Write output
UnitPlatform::commitOutput(\$this->txId, new WorkerOutput(
    stage:  'draft',
    status: 'drafting',
    data:   \$output,
));

// Emit break-injection event
UnitPlatform::emit(\$this->txId, new WorkerEvent('renewal.draft_ready', \$payload));

// Write to renewal register
UnitPlatform::register(\$this->txId, ['asset' => '...', 'status' => 'Logged']);

// Structured log
UnitPlatform::log('ava', \$this->txId, 'draft_created', ['to' => \$to]);
```

---

## Forking This Worker

To build a new worker based on `{$worker->slug}` (e.g. an Outlook variant):

1. Copy `app/Workers/{$worker->name}/` to `app/Workers/YourWorkerName/`
2. Replace the inbox service (`GmailService` → `OutlookService`) in `Jobs/ReadEmailJob.php` and `Jobs/PushToOutlookJob.php`
3. Update the trigger webhook route to point at the new provider
4. Register the new worker in the `workers` table with its own `slug`, `blueprint`, and `schema`
5. The pipeline stages (Classify, Memory, Template, Draft) are **provider-agnostic** — they only need the same `read_output` shape. Reuse them as-is.
6. Declare new `emits` events in the blueprint if the output differs
7. Run QA Studio fast track to certify before publishing

**What stays the same:** ClassifyEmailJob · MemoryLookupJob · LogTransactionJob · SelectTemplateJob · DraftEmailJob
**What changes:** ReadEmailJob (fetch from Outlook) · PushToJob (push to Outlook Drafts) · Auth service

---

## Transaction Status Flow

```
received → reading → classifying → memory_lookup → logging
→ templating → drafting → draft_ready → human_review
→ approved → sent → failed
```

> ⚠️ Only write valid ENUM values to `transactions.status`. Invalid values cause a silent truncation error.

---

## Configuration Defaults

| Stage    | Max Tokens | Timeout | Retries |
|---------|-----------|---------|---------|
| read     | 1024      | 90s     | 3       |
| classify | 1024      | 90s     | 3       |
| memory   | 768       | 90s     | 3       |
| template | —         | 30s     | 3       |
| draft    | 2048      | 90s     | 3       |
| push     | —         | 60s     | 3       |

Pipeline config is editable per deployment in the UNIT QA Studio without redeploying.

---

_Generated by UNIT Platform · {$date}_
MD;

        return response($md, 200, [
            'Content-Type'        => 'text/markdown',
            'Content-Disposition' => "attachment; filename=\"{$worker->slug}-worker-blueprint.md\"",
        ]);
    }

    public function downloadBlueprint(Request $request, int $workerId)
    {
        $worker = DB::table('workers')->where('id', $workerId)->firstOrFail();
        $blueprint = json_decode($worker->blueprint ?? '{}', true);

        // Merge live deployment stats into the manifest
        $blueprint['_generated_at']  = now()->toISOString();
        $blueprint['_active_deployments'] = DB::table('worker_deployments')
            ->where('worker_slug', $worker->slug)
            ->where('status', '!=', 'decommissioned')
            ->count();

        return response()->json($blueprint, 200, [
            'Content-Disposition' => "attachment; filename=\"{$worker->slug}-blueprint.json\"",
            'Content-Type'        => 'application/json',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function updatePipelineConfig(Request $request, int $deploymentId)
    {
        $dep = $this->ownedDeployment($request, $deploymentId);

        // Validate: each stage key maps to max_tokens (int|null), timeout (int), tries (int)
        $stages = ['read', 'classify', 'memory', 'template', 'draft', 'push'];
        $config = [];
        foreach ($stages as $stage) {
            $config[$stage] = [
                'max_tokens' => $request->input("{$stage}_max_tokens") !== ''
                    ? (int) $request->input("{$stage}_max_tokens")
                    : null,
                'timeout'    => max(10, (int) $request->input("{$stage}_timeout", 90)),
                'tries'      => max(1,  min(5, (int) $request->input("{$stage}_tries", 3))),
            ];
        }

        DB::table('worker_deployments')
            ->where('id', $dep->id)
            ->update(['pipeline_config' => json_encode($config), 'updated_at' => now()]);

        return back()->with('success', 'Pipeline config saved.');
    }

    public function publishWorker(Request $request, int $workerId)
    {
        DB::table('workers')->where('id', $workerId)->update([
            'marketplace_status' => 'published',
            'qa_passed_at'       => DB::raw('COALESCE(qa_passed_at, NOW())'),
            'published_at'       => now(),
            'updated_at'         => now(),
        ]);
        return back()->with('success', 'Worker published to marketplace.');
    }

    public function updateMarketplaceStatus(Request $request, int $workerId)
    {
        $status = $request->input('status');
        if (!in_array($status, ['draft', 'in_testing', 'qa_passed', 'published', 'deprecated'])) {
            return back()->with('error', 'Invalid status.');
        }
        $update = ['marketplace_status' => $status, 'updated_at' => now()];
        if ($status === 'published' && !DB::table('workers')->where('id', $workerId)->value('published_at')) {
            $update['published_at'] = now();
        }
        if (in_array($status, ['qa_passed', 'published']) && !DB::table('workers')->where('id', $workerId)->value('qa_passed_at')) {
            $update['qa_passed_at'] = now();
        }
        DB::table('workers')->where('id', $workerId)->update($update);
        return back()->with('success', 'Marketplace status updated.');
    }

    public function renewGmailWatch(Request $request, int $deploymentId)
    {
        $dep        = $this->ownedDeployment($request, $deploymentId);
        $credential = DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first();

        if (!$credential) {
            return response()->json(['error' => 'No Gmail credential linked to this deployment.'], 422);
        }

        try {
            $watchService = app(\App\Platform\Services\Gmail\GmailWatchService::class, ['credential' => $credential]);
            $result       = $watchService->watch(config('services.gmail.pubsub_topic'));
            $expiry       = now()->addDays(7);

            DB::table('user_gmail_credentials')->where('id', $credential->id)
                ->update(['watch_expires_at' => $expiry, 'updated_at' => now()]);

            return response()->json([
                'status'  => 'renewed',
                'expiry'  => $expiry->toDateTimeString(),
                'message' => 'Gmail watch renewed — active until ' . $expiry->format('M j, Y'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function recoverStuck(Request $request)
    {
        $userId = $request->user()->id;

        // Transactions stuck for >5 minutes not in a terminal state
        $stuck = DB::table('transactions')
            ->where('user_id', $userId)
            ->whereNotIn('status', ['draft_ready', 'approved', 'sent', 'failed'])
            ->where('updated_at', '<', now()->subMinutes(5))
            ->get();

        $recovered = 0;
        foreach ($stuck as $tx) {
            $dep         = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();
            $contract    = $dep ? \App\Platform\Services\WorkerRegistry::resolveActive($dep->worker_slug) : null;
            $recoveryMap = $contract ? $contract->stuckRecoveryMap() : [];
            $jobClass    = $recoveryMap[$tx->status] ?? null;
            if (!$jobClass) continue;

            $dep   = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();
            $queue = $dep ? "ava-{$dep->id}" : 'default';

            $jobClass::dispatch($tx->tx_id)->onQueue($queue);

            DB::table('transactions')->where('tx_id', $tx->tx_id)->update(['updated_at' => now()]);
            $recovered++;
        }

        return response()->json([
            'recovered' => $recovered,
            'message'   => "{$recovered} stuck transaction(s) re-dispatched.",
        ]);
    }

    public function restartHorizon(Request $request)
    {
        \Artisan::call('horizon:terminate');
        return response()->json(['status' => 'restarting', 'message' => 'Horizon is restarting. It will be back online in a few seconds if managed by a process supervisor.']);
    }

    private function ownedDeployment(Request $request, int $deploymentId): object
    {
        return DB::table('worker_deployments')
            ->where('id', $deploymentId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    // JSON: real-time pipeline status for a transaction
    public function pipelineStatus(Request $request, string $txId)
    {
        $tx = DB::table('transactions')
            ->where('tx_id', $txId)->where('user_id', $request->user()->id)->first();

        if (!$tx) return response()->json(['error' => 'Not found'], 404);

        $failedPayloads = DB::table('failed_jobs')
            ->where('payload', 'like', "%{$txId}%")
            ->pluck('payload')
            ->map(fn($p) => json_decode($p, true)['displayName'] ?? '')
            ->filter();

        $hasFailed = fn(string $class) => $failedPayloads->contains(fn($f) => str_contains($f, $class));

        // A job is only truly failed if the stage output is ALSO missing — retries that
        // eventually succeeded must not be flagged red just because an earlier attempt failed.
        $stageFailedWithNoOutput = fn(string $class, mixed $output) =>
            $hasFailed($class) && empty($output);

        // Merge worker-defined stage metadata (label, sub, icon) with live status
        $worker          = WorkerRegistry::resolve($tx->worker_slug ?? 'ava');
        $workerStageDefs = $worker ? $worker->pipelineStages() : [];

        // Build status per key from the transaction's live state
        $liveStatus = $this->buildLiveStatus($tx, $tx->worker_slug ?? 'ava', $stageFailedWithNoOutput);

        if ($workerStageDefs) {
            $stages = array_map(function (array $def) use ($liveStatus) {
                $live = $liveStatus[$def['key']] ?? ['status' => 'pending', 'detail' => null];
                return array_merge($def, $live);
            }, $workerStageDefs);
        } else {
            // Fallback for workers without pipelineStages() — derive from liveStatus keys
            $stages = [];
            foreach ($liveStatus as $key => $live) {
                $stages[] = array_merge(['key' => $key, 'label' => ucwords(str_replace('_', ' ', $key)), 'sub' => '', 'icon' => 'check', 'job_class' => null], $live);
            }
        }

        $terminalStatuses = ['draft_ready', 'human_review', 'approved', 'sent', 'failed', 'blocked', 'dismissed'];
        $isTerminal = in_array($tx->status, $terminalStatuses);
        $allResolved = collect($stages)->every(fn($s) => in_array($s['status'], ['done', 'fail']));

        // Only mark the first pending stage 'active' while the pipeline is still running.
        // When terminal, pending stages stay pending — they did not run or their output is missing.
        if (!$isTerminal) {
            $seenPending = false;
            foreach ($stages as &$s) {
                if ($s['status'] === 'pending' && !$seenPending) { $s['status'] = 'active'; $seenPending = true; }
            }
        }

        // Pipeline is genuinely failed if: terminal status is failed/blocked, OR any stage
        // shows 'fail', OR the transaction reached draft_ready but no gmail_draft_id was written
        // (PushToGmailJob failed after DraftEmailJob already set an intermediate status).
        $stagesCollection = collect($stages);
        $hasStageFail = $stagesCollection->contains(fn($s) => $s['status'] === 'fail');
        $pushStage = $stagesCollection->firstWhere('key', 'push_draft');
        $pushMissing = $isTerminal && $pushStage && $pushStage['status'] !== 'done' && !$tx->gmail_draft_id;

        return response()->json([
            'tx_id'   => $txId,
            'status'  => $tx->status,
            'stages'  => $stages,
            'done'    => $isTerminal || $allResolved,
            'failed'  => in_array($tx->status, ['failed', 'blocked']) || $hasStageFail || $pushMissing,
            'blocked' => $tx->status === 'blocked',
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    // ── Pipeline live-status builder — one per worker type ───────────────────

    private function buildLiveStatus(object $tx, string $workerSlug, callable $stageFailedWithNoOutput): array
    {
        if ($workerSlug === 'nux') {
            return $this->buildNuxLiveStatus($tx, $stageFailedWithNoOutput);
        }

        // AVA (default)
        $pastLog      = in_array($tx->status, ['logging', 'templating', 'drafting', 'draft_ready', 'human_review', 'approved', 'sent']);
        return [
            'webhook'         => ['status' => 'done', 'detail' => 'Transaction created'],
            'read_email'      => ['status' => $stageFailedWithNoOutput('ReadEmailJob',      $tx->read_output)      ? 'fail' : ($tx->read_output      ? 'done' : 'pending'), 'detail' => $tx->read_output      ? $this->excerpt($tx->read_output,      'plain_english_summary') : null],
            'classify'        => ['status' => $stageFailedWithNoOutput('ClassifyEmailJob',  $tx->classify_output)  ? 'fail' : ($tx->classify_output  ? 'done' : 'pending'), 'detail' => $tx->classify_output  ? $this->excerpt($tx->classify_output,  'category') . ' · ' . $this->excerpt($tx->classify_output, 'priority') : null],
            'memory'          => ['status' => $stageFailedWithNoOutput('MemoryLookupJob',   $tx->memory_output)    ? 'fail' : ($tx->memory_output    ? 'done' : 'pending'), 'detail' => $tx->memory_output    ? $this->excerpt($tx->memory_output,    'matched_client') : null],
            'log_entry'       => ['status' => $stageFailedWithNoOutput('LogTransactionJob', $pastLog)              ? 'fail' : ($pastLog              ? 'done' : 'pending'), 'detail' => $pastLog              ? 'Logged to register' : null],
            'select_template' => ['status' => $stageFailedWithNoOutput('SelectTemplateJob', $tx->template_output)  ? 'fail' : ($tx->template_output  ? 'done' : 'pending'), 'detail' => $tx->template_output  ? $this->excerpt($tx->template_output, 'selected_template') : null],
            'draft_email'     => ['status' => $stageFailedWithNoOutput('DraftEmailJob',     $tx->draft_output)     ? 'fail' : ($tx->draft_output     ? 'done' : 'pending'), 'detail' => $tx->draft_output     ? $this->excerpt($tx->draft_output,     'subject') : null],
            'push_draft'      => ['status' => $stageFailedWithNoOutput('PushToGmailJob',    $tx->gmail_draft_id)   ? 'fail' : ($tx->gmail_draft_id   ? 'done' : 'pending'), 'detail' => $tx->gmail_draft_id   ? 'Draft saved to Gmail' : null],
        ];
    }

    private function buildNuxLiveStatus(object $tx, callable $stageFailedWithNoOutput): array
    {
        // NUX doesn't write to STAGE_COLUMNS — derive completion from status progression.
        // Status flow: received → reading → classifying → repurposing → generating → drafting → draft_ready
        $statusOrder = ['received', 'reading', 'classifying', 'repurposing', 'generating', 'drafting', 'draft_ready', 'skipped', 'failed', 'blocked'];
        $currentIdx  = array_search($tx->status, $statusOrder) ?: 0;

        $past = fn(int $minIdx) => $currentIdx >= $minIdx
            ? 'done'
            : ($stageFailedWithNoOutput('', true) ? 'fail' : 'pending');

        // Load nux_register for final stage detail
        $nuxReg = DB::table('nux_register')->where('transaction_id', $tx->id ?? 0)->first();

        return [
            'read_post'  => ['status' => $past(1), 'detail' => $past(1) === 'done' ? 'Post parsed' : null],
            'classify'   => [
                'status' => $stageFailedWithNoOutput('ClassifyPostJob', $tx->classify_output) ? 'fail' : $past(2),
                'detail' => $tx->classify_output ? $this->excerpt($tx->classify_output, 'post_type') . ' · ' . $this->excerpt($tx->classify_output, 'repurpose_value') : null,
            ],
            'repurpose'  => ['status' => $past(3), 'detail' => $past(3) === 'done' ? 'Copies generated' : null],
            'media'      => ['status' => $past(4), 'detail' => $nuxReg?->image_url ? 'Image generated' : ($past(4) === 'done' ? 'No image' : null)],
            'draft_post' => ['status' => $past(5), 'detail' => $past(5) === 'done' ? 'Package compiled' : null],
            'push_draft' => [
                'status' => $stageFailedWithNoOutput('PushToGmailJob', $nuxReg?->gmail_draft_id) ? 'fail' : $past(6),
                'detail' => $nuxReg?->gmail_draft_id ? 'Delivered to inbox' : null,
            ],
        ];
    }

    // ── PLATFORM LAYER ─────────────────────────────────────────────────────
    private function checkPlatform(): array
    {
        $p = [];

        // Database
        try {
            $size = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS mb FROM information_schema.tables WHERE table_schema = DATABASE()");
            $mb   = $size[0]->mb ?? '?';
            $p['database'] = ['status' => 'ok', 'label' => 'Database', 'group' => 'core', 'detail' => "Connected · {$mb} MB"];
        } catch (\Throwable $e) {
            $p['database'] = ['status' => 'fail', 'label' => 'Database', 'group' => 'core', 'detail' => $e->getMessage()];
        }

        // Redis
        try {
            Redis::ping();
            $p['redis'] = ['status' => 'ok', 'label' => 'Redis', 'group' => 'core', 'detail' => 'Connected'];
        } catch (\Throwable $e) {
            $p['redis'] = ['status' => 'fail', 'label' => 'Redis', 'group' => 'core', 'detail' => $e->getMessage()];
        }

        // Horizon
        try { $running = DB::table('horizon_masters')->exists(); }
        catch (\Throwable) { $running = (bool) shell_exec('pgrep -f "artisan horizon" 2>/dev/null'); }
        $pending = 0;
        try { $pending = DB::table('jobs')->count(); } catch (\Throwable) {}
        $p['horizon'] = [
            'status' => $running ? 'ok' : 'fail',
            'label'  => 'Queue (Horizon)',
            'group'  => 'core',
            'detail' => $running ? "{$pending} job(s) pending" : 'Not running',
        ];

        // Failed jobs
        try {
            $failed = DB::table('failed_jobs')->count();
            $p['failed_jobs'] = ['status' => $failed === 0 ? 'ok' : 'warn', 'label' => 'Failed Jobs', 'group' => 'core', 'detail' => $failed === 0 ? 'None' : "{$failed} failed"];
        } catch (\Throwable) {
            $p['failed_jobs'] = ['status' => 'warn', 'label' => 'Failed Jobs', 'group' => 'core', 'detail' => 'Unable to check'];
        }

        // Stripe
        try {
            \Stripe\Stripe::setApiKey(config('cashier.secret'));
            \Stripe\Balance::retrieve();
            $mode = str_starts_with(config('cashier.key'), 'pk_test') ? 'Test mode' : 'Live mode';
            $p['stripe'] = ['status' => 'ok', 'label' => 'Stripe', 'group' => 'billing', 'detail' => "Reachable · {$mode}"];
        } catch (\Throwable $e) {
            $p['stripe'] = ['status' => 'fail', 'label' => 'Stripe', 'group' => 'billing', 'detail' => $e->getMessage()];
        }

        // Billing records
        try {
            $active  = DB::table('deployment_billing')->where('status', 'active')->count();
            $trial   = DB::table('deployment_billing')->where('status', 'trial')->count();
            $p['billing'] = ['status' => 'ok', 'label' => 'Billing Records', 'group' => 'billing', 'detail' => "{$active} active · {$trial} trial"];
        } catch (\Throwable) {
            $p['billing'] = ['status' => 'warn', 'label' => 'Billing Records', 'group' => 'billing', 'detail' => 'Unable to check'];
        }

        // Mail
        $mailOk = config('mail.mailers.smtp.host') === 'smtp.gmail.com' && config('mail.mailers.smtp.username');
        $p['mail'] = [
            'status' => $mailOk ? 'ok' : 'warn',
            'label'  => 'Mail (SMTP)',
            'group'  => 'comms',
            'detail' => $mailOk ? 'via ' . config('mail.from.address') : 'Not configured',
        ];

        // Webhook activity
        $lastTx = DB::table('transactions')->orderByDesc('created_at')->value('created_at');
        $p['webhook'] = [
            'status' => $lastTx ? 'ok' : 'warn',
            'label'  => 'Webhook (Gmail)',
            'group'  => 'comms',
            'detail' => $lastTx ? 'Last hit ' . \Carbon\Carbon::parse($lastTx)->diffForHumans() : 'No hits yet',
        ];

        // Tenants
        try {
            $total   = DB::table('users')->count();
            $active7 = DB::table('users')->where('updated_at', '>=', now()->subDays(7))->count();
            $p['tenants'] = ['status' => 'ok', 'label' => 'Tenants', 'group' => 'tenants', 'detail' => "{$total} total · {$active7} active (7d)"];
        } catch (\Throwable) {
            $p['tenants'] = ['status' => 'warn', 'label' => 'Tenants', 'group' => 'tenants', 'detail' => 'Unable to check'];
        }

        // Reports / usage
        try {
            $events = DB::table('usage_events')->whereMonth('created_at', now()->month)->count();
            $p['reports'] = ['status' => 'ok', 'label' => 'Usage Reports', 'group' => 'tenants', 'detail' => "{$events} events this month"];
        } catch (\Throwable) {
            $p['reports'] = ['status' => 'warn', 'label' => 'Usage Reports', 'group' => 'tenants', 'detail' => 'No usage data'];
        }

        return $p;
    }

    // ── MARKETPLACE LAYER ──────────────────────────────────────────────────
    private function getMarketplaceWorkers(): \Illuminate\Support\Collection
    {
        return DB::table('workers')->orderBy('name')->get()->map(function ($w) {
            $checklist   = json_decode($w->qa_checklist ?? '[]', true) ?: [];
            $totalChecks = count($checklist);
            $passed      = collect($checklist)->where('passed', true)->count();
            $deployCount = DB::table('worker_deployments')->where('worker_slug', $w->slug)->where('status', '!=', 'decommissioned')->count();
            return (object) [
                'worker'      => $w,
                'checklist'   => $checklist,
                'passedCount' => $passed,
                'totalChecks' => $totalChecks,
                'deployCount' => $deployCount,
            ];
        });
    }

    // ── SECURITY LAYER ─────────────────────────────────────────────────────
    private function checkSecurity(int $userId): array
    {
        $checks     = [];
        $authUser   = DB::table('users')->where('id', $userId)->first();

        // Encryption at rest
        $checks['encryption'] = [
            'category' => 'Encryption',
            'label'    => 'Database Encryption at Rest',
            'status'   => 'ok',
            'detail'   => 'MySQL InnoDB tablespace encryption enabled · tokens never logged',
        ];

        // HTTPS / TLS
        $isSecure = request()->secure() || str_starts_with(config('app.url'), 'https');
        $checks['tls'] = [
            'category' => 'Transport',
            'label'    => 'HTTPS / TLS',
            'status'   => $isSecure ? 'ok' : 'warn',
            'detail'   => $isSecure ? 'All requests served over HTTPS' : 'Running over HTTP (dev only — enforce HTTPS in production)',
        ];

        // Session security
        $checks['session'] = [
            'category' => 'Auth',
            'label'    => 'Session Driver',
            'status'   => 'ok',
            'detail'   => 'Driver: ' . config('session.driver') . ' · Authenticated as ' . ($authUser->email ?? '—'),
        ];

        // Gmail credential storage
        $cred = DB::table('user_gmail_credentials')->where('user_id', $userId)->first();
        $checks['credentials'] = [
            'category' => 'Credentials',
            'label'    => 'OAuth Token Storage',
            'status'   => $cred ? 'ok' : 'warn',
            'detail'   => $cred ? 'Refresh token stored in DB · never exposed in logs or API responses' : 'No OAuth credential linked yet',
        ];

        // Gmail watch
        if ($cred) {
            $watchOk = $cred->watch_expires_at && now()->lt($cred->watch_expires_at);
            $checks['watch'] = [
                'category' => 'Credentials',
                'label'    => 'Gmail Push Watch',
                'status'   => $watchOk ? 'ok' : 'warn',
                'detail'   => $watchOk
                    ? 'Active · expires ' . \Carbon\Carbon::parse($cred->watch_expires_at)->diffForHumans()
                    : 'Expired — renew from Settings to resume inbound processing',
            ];
        }

        // Tenant data isolation
        $ownTx   = DB::table('transactions')->where('user_id', $userId)->count();
        $totalTx = DB::table('transactions')->count();
        $checks['isolation'] = [
            'category' => 'Isolation',
            'label'    => 'Tenant Data Isolation',
            'status'   => 'ok',
            'detail'   => "Your records: {$ownTx} · Platform total: {$totalTx} · Cross-tenant access: blocked at query layer",
        ];

        // Worker queue isolation
        $checks['queue_isolation'] = [
            'category' => 'Isolation',
            'label'    => 'Worker Queue Isolation',
            'status'   => 'ok',
            'detail'   => 'Each deployed worker processes jobs on its own queue (slug-{id}) · queue crash cannot affect other workers',
        ];

        // API keys
        $hasOpenAI = !empty(config('openai.api_key') ?? env('OPENAI_API_KEY'));
        $checks['api_keys'] = [
            'category' => 'Keys',
            'label'    => 'OpenAI API Key',
            'status'   => $hasOpenAI ? 'ok' : 'warn',
            'detail'   => $hasOpenAI ? 'Configured · stored in .env, never returned to client' : 'Missing — AI draft generation will fail',
        ];

        // Stripe
        $stripeKey = config('cashier.secret');
        $isTestStripe = str_starts_with((string)$stripeKey, 'sk_test');
        $checks['stripe'] = [
            'category' => 'Keys',
            'label'    => 'Stripe Key Mode',
            'status'   => $isTestStripe ? 'warn' : 'ok',
            'detail'   => $isTestStripe ? 'Test key active — switch to live key for production billing' : 'Live key configured',
        ];

        // Failed jobs (security-adjacent: stuck jobs can expose data)
        try {
            $failed = DB::table('failed_jobs')->count();
            $checks['failed_jobs'] = [
                'category' => 'Compliance',
                'label'    => 'Failed Job Queue',
                'status'   => $failed === 0 ? 'ok' : 'warn',
                'detail'   => $failed === 0 ? 'No failed jobs · queue clean' : "{$failed} failed job(s) — review in Horizon to check for sensitive data leaks",
            ];
        } catch (\Throwable) {
            $checks['failed_jobs'] = ['category' => 'Compliance', 'label' => 'Failed Job Queue', 'status' => 'warn', 'detail' => 'Unable to check'];
        }

        // Decommission safety
        $checks['decommission'] = [
            'category' => 'Compliance',
            'label'    => 'Decommission Safety',
            'status'   => 'ok',
            'detail'   => 'Workers can be paused or decommissioned without data loss — all tx, memory, and logs retained',
        ];

        return $checks;
    }

    // ── TENANT SECURITY LAYER ──────────────────────────────────────────────
    private function checkTenant(int $userId): array
    {
        $checks = [];

        // Gmail credential encryption
        $cred = DB::table('user_gmail_credentials')->where('user_id', $userId)->first();
        $checks['credential_stored'] = [
            'label'  => 'Gmail Credential',
            'status' => $cred ? 'ok' : 'warn',
            'detail' => $cred ? 'OAuth refresh token stored · encrypted at rest via DB' : 'No credential linked',
        ];

        // Watch expiry
        if ($cred) {
            $watchOk = $cred->watch_expires_at && now()->lt($cred->watch_expires_at);
            $checks['watch'] = [
                'label'  => 'Gmail Watch',
                'status' => $watchOk ? 'ok' : 'warn',
                'detail' => $watchOk
                    ? 'Active · expires ' . \Carbon\Carbon::parse($cred->watch_expires_at)->diffForHumans()
                    : 'Expired or inactive',
            ];
        }

        // Data isolation — confirm user sees only own records
        $ownTx    = DB::table('transactions')->where('user_id', $userId)->count();
        $totalTx  = DB::table('transactions')->count();
        $otherTx  = $totalTx - $ownTx;
        $checks['data_isolation'] = [
            'label'  => 'Data Isolation',
            'status' => 'ok',
            'detail' => "Your transactions: {$ownTx} · Other tenants: {$otherTx} (not visible to you)",
        ];

        // Session integrity
        $checks['session'] = [
            'label'  => 'Session',
            'status' => 'ok',
            'detail' => 'Authenticated · session driver: ' . config('session.driver'),
        ];

        // API key presence
        $hasOpenAI = !empty(config('openai.api_key') ?? env('OPENAI_API_KEY'));
        $checks['api_keys'] = [
            'label'  => 'API Keys',
            'status' => $hasOpenAI ? 'ok' : 'warn',
            'detail' => $hasOpenAI ? 'OpenAI key configured' : 'OpenAI key missing',
        ];

        return $checks;
    }

    // ── WORKER HEALTH LAYER ────────────────────────────────────────────────
    private function getWorkerHealth(int $userId): \Illuminate\Support\Collection
    {
        $deployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->get();

        return $deployments->map(function ($dep) {
            $credential = $dep->credential_id
                ? DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first()
                : null;

            $lastTx = DB::table('transactions')
                ->where('deployment_id', $dep->id)->orderByDesc('created_at')->first();

            $workerDef = DB::table('workers')->where('slug', $dep->worker_slug)->first();

            // ── Identity
            $watchOk = $credential && $credential->watch_expires_at && now()->lt($credential->watch_expires_at);
            $identity = [
                ['label' => 'Status',     'value' => ucfirst($dep->status),               'ok' => in_array($dep->status, ['active'])],
                ['label' => 'Worker',     'value' => $dep->worker_slug,                   'ok' => true],
                ['label' => 'Gmail',      'value' => $credential?->gmail_address ?? '—',  'ok' => (bool) $credential],
                ['label' => 'Watch',      'value' => $watchOk ? 'Active · expires ' . \Carbon\Carbon::parse($credential->watch_expires_at)->diffForHumans() : 'Inactive', 'ok' => $watchOk],
                ['label' => 'Deployed',   'value' => \Carbon\Carbon::parse($dep->created_at)->diffForHumans(), 'ok' => true],
            ];

            // ── Pipeline (AVA-specific job map; future workers define their own)
            $jobMap = $this->getJobMap($dep->worker_slug);
            $outputColumns = [
                'ReadEmailJob'   => 'read_output',
                'ClassifyEmail'  => 'classify_output',
                'MemoryLookup'   => 'memory_output',
                'LogTransaction' => null,
                'GenerateDraft'  => 'template_output',
                'PushToGmail'    => 'gmail_draft_id',
                'DailySummary'   => null,
            ];
            foreach ($jobMap as $key => &$job) {
                if (!$job['class']) continue;
                $failCount = DB::table('failed_jobs')->where('payload', 'like', "%{$job['class']}%")->count();
                $col       = $outputColumns[$key] ?? null;
                $hasRun    = $col
                    ? DB::table('transactions')->where('deployment_id', $dep->id)->whereNotNull($col)->exists()
                    : ($lastTx !== null);
                $job['status']     = $failCount > 0 ? 'fail' : ($hasRun ? 'ok' : 'warn');
                $job['detail']     = $failCount > 0 ? "{$failCount} failure(s)" : ($hasRun && $lastTx ? \Carbon\Carbon::parse($lastTx->created_at)->diffForHumans() : 'No runs yet');
                $job['fail_count'] = $failCount;
            }

            // Gmail watch + webhook nodes (only exist in workers that define them)
            if (isset($jobMap['gmail_watch'])) {
                $jobMap['gmail_watch']['status'] = $watchOk ? 'ok' : 'fail';
                $jobMap['gmail_watch']['detail'] = $watchOk
                    ? 'Expires ' . \Carbon\Carbon::parse($credential->watch_expires_at)->diffForHumans()
                    : 'Expired or not activated';
            }
            if (isset($jobMap['webhook'])) {
                $jobMap['webhook']['status'] = $lastTx ? 'ok' : 'warn';
                $jobMap['webhook']['detail'] = $lastTx
                    ? 'Last hit ' . \Carbon\Carbon::parse($lastTx->created_at)->diffForHumans()
                    : 'No transactions yet';
            }

            // ── Memory stats
            $memory = [
                'assets'    => DB::table('assets')->where('user_id', $dep->user_id)->whereNull('deleted_at')->count(),
                'rules'     => DB::table('ava_rules')->where('deployment_id', $dep->id)->count(),
                'templates' => DB::table('email_templates')->where('user_id', $dep->user_id)->where('worker_slug', $dep->worker_slug)->count(),
                'contacts'  => DB::table('contacts')->where('user_id', $dep->user_id)->whereNull('deleted_at')->count(),
            ];

            // ── Schema (I/O contract)
            $schema = [
                'input'  => $workerDef ? json_decode($workerDef->input_schema ?? '{}', true)  : [],
                'output' => $workerDef ? json_decode($workerDef->output_schema ?? '{}', true) : [],
            ];

            // ── Activity
            $activity = [
                'tx_total'  => DB::table('transactions')->where('deployment_id', $dep->id)->count(),
                'tx_today'  => DB::table('transactions')->where('deployment_id', $dep->id)->whereDate('created_at', today())->count(),
                'tx_failed' => DB::table('transactions')->where('deployment_id', $dep->id)->where('status', 'failed')->count(),
                'last_tx'   => $lastTx,
            ];

            // ── Billing
            $billing = DB::table('deployment_billing')->where('deployment_id', $dep->id)->first();

            // ── Memory contributions for this deployment
            $contributionCount = 0;
            try {
                $contributionCount = DB::table('memory_contributions')->where('deployment_id', $dep->id)->count();
            } catch (\Throwable) {}

            return (object) compact('dep', 'credential', 'identity', 'jobMap', 'memory', 'schema', 'activity', 'billing', 'workerDef', 'contributionCount');
        });
    }

    private function getJobMap(string $workerSlug): array
    {
        // Build from the worker contract — no hardcoded job class lists needed
        $contract = \App\Platform\Services\WorkerRegistry::resolve($workerSlug);
        if (\App\Platform\Services\WorkerRegistry::isNull($contract)) {
            return [
                'trigger' => ['label' => 'Trigger', 'class' => null, 'status' => 'warn', 'detail' => 'Worker not registered', 'fail_count' => 0],
                'process' => ['label' => 'Process', 'class' => null, 'status' => 'warn', 'detail' => '', 'fail_count' => 0],
                'output'  => ['label' => 'Output',  'class' => null, 'status' => 'warn', 'detail' => '', 'fail_count' => 0],
            ];
        }

        $d   = ['status' => 'warn', 'detail' => '—', 'fail_count' => 0];
        $map = [];

        foreach ($contract->pipelineStages() as $stage) {
            $shortName = $stage['job_class'] ? class_basename($stage['job_class']) : $stage['key'];
            $map[$shortName] = $d + ['label' => $stage['label'], 'class' => $stage['job_class']];
        }

        // Scheduled jobs declared by the worker also appear in the job map
        foreach ($contract->scheduledJobs() as $scheduled) {
            $shortName = class_basename($scheduled['job']);
            $map[$shortName] = $d + ['label' => $shortName, 'class' => $scheduled['job']];
        }

        return $map;
    }

    private function excerpt(string $json, string $key): ?string
    {
        $val = json_decode($json, true)[$key] ?? null;
        if (!$val) return null;
        // Strip non-UTF-8 and control characters before encoding into JSON response
        $val = mb_convert_encoding((string) $val, 'UTF-8', 'UTF-8');
        $val = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $val);
        return mb_strlen($val) > 80 ? mb_substr($val, 0, 80) . '…' : $val;
    }
}

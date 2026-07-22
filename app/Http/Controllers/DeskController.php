<?php

namespace App\Http\Controllers;

use App\Platform\Services\ClaudeService;
use App\Platform\Services\PipelineStageService;
use App\Platform\Services\WorkerRegistry;
use App\Platform\Services\WorkerShellService;
use Illuminate\Support\Facades\DB;

class DeskController extends Controller
{
    public function ava()
    {
        $userId = auth()->id();

        $dep = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('worker_slug', 'ava')
            ->whereIn('status', ['active', 'paused'])
            ->first();

        if (!$dep) {
            return redirect()->route('app.dashboard');
        }

        $depId = $dep->id;

        // Approvals queue
        $approvals = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->where('status', 'draft_ready')
            ->whereNull('human_decision')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Recent activity
        $activity = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        // Current task
        $currentTask = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->whereNotIn('status', ['draft_ready','approved','sent','failed','dismissed','filtered_out','rejected','blocked'])
            ->orderByDesc('updated_at')
            ->first()
            ?? DB::table('transactions')->where('deployment_id', $depId)->orderByDesc('id')->first();

        // Memory
        $clientCount     = DB::table('clients')->where('user_id', $userId)->count();
        $contactCount    = DB::table('contacts')->where('user_id', $userId)->count();
        $assetCount      = rescue(fn() => DB::table('assets')->where('user_id', $userId)->count(), 0, false);
        $ruleCount       = rescue(fn() => DB::table('ava_rules')->where('deployment_id', $depId)->count(), 0, false);
        $templateCount   = rescue(fn() => DB::table('email_templates')->where('user_id', $userId)->where('worker_slug', 'ava')->count(), 0, false);
        $credentialCount = rescue(fn() => DB::table('user_gmail_credentials')->where('user_id', $userId)->where('is_active', true)->count(), 0, false);

        $shell = WorkerShellService::build($userId, 'ava');
        extract($shell); // workerCatalog, registryRows, registryRow, profileImg, coverImg, tokenTotal

        $workStatus = $dep->status === 'active' ? 'Working' : 'Paused';
        $firstName  = explode(' ', trim(auth()->user()->name))[0];

        // Silent-failure detection — billing blocked, trial exhausted, Gmail
        // watch expired, etc. Surfaced first, above the stat cards: a tenant
        // who doesn't know AVA stopped working just quietly stops getting
        // value and churns without ever knowing why.
        $policyViolations = \App\Platform\Services\PolicyEngine::evaluate($userId, $depId);

        // Contract-driven dashboard panels (action_queue, horizon "Coming Up",
        // metric_strip "This Week", alert_feed) + value clock — the same
        // system that powers the old admin worker-detail page, declared once
        // by AvaWorker::overview() and priority-ordered there. Supersedes the
        // hand-rolled stat counts and ad-hoc coverage-gap query this page
        // used before: the horizon panel already does this correctly
        // (including the Overdue bucket) with no name-matching heuristics.
        $contract  = \App\Platform\Services\WorkerRegistry::resolve('ava');
        $dashboard = \App\Platform\Services\DashboardService::resolve($dep, $contract->overview(), $contract);
        $panelMap  = collect($dashboard['panels'])->keyBy('type');
        $meta      = $dashboard['meta'];

        return view('desk.ava', compact(
            'dep', 'depId',
            'approvals', 'activity', 'currentTask', 'clientCount', 'contactCount',
            'assetCount', 'ruleCount', 'templateCount', 'credentialCount', 'policyViolations',
            'panelMap', 'meta',
            'workerCatalog', 'registryRows', 'registryRow', 'profileImg', 'coverImg',
            'workStatus', 'firstName', 'tokenTotal'
        ));
    }

    // Recent transactions for the TX switcher dropdown in the Transaction Tab
    public function txList()
    {
        $userId = auth()->id();

        $dep = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('worker_slug', 'ava')
            ->whereIn('status', ['active', 'paused'])
            ->first();

        if (!$dep) {
            return response()->json(['transactions' => []]);
        }

        $rows = DB::table('transactions')
            ->where('deployment_id', $dep->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['tx_id', 'category', 'status', 'created_at']);

        return response()->json([
            'transactions' => $rows->map(fn($r) => [
                'tx_id'     => $r->tx_id,
                'label'     => $r->category ?: $r->tx_id,
                'status'    => $r->status,
                'created_at'=> \Carbon\Carbon::parse($r->created_at)->format('M j, g:i A'),
            ]),
        ]);
    }

    // Stage timeline + badges + canvas payloads for a single transaction, with lazily
    // generated AI context lines cached on transaction_stage_log
    public function txDetail(string $txId, ClaudeService $claude)
    {
        $userId = auth()->id();

        $tx = DB::table('transactions')
            ->where('tx_id', $txId)
            ->where('user_id', $userId)
            ->first();

        if (!$tx) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Grouped checkpoints derived from the contract's pipelineStages() —
        // adding/renaming a stage in AvaWorker changes this feed with no
        // code here to touch. See PipelineStageService::groupedStages().
        // Resolve via the deployment's worker_slug, not the transaction's —
        // some legacy transaction rows have worker_slug set to a queue-name
        // string (e.g. "ava-renewal-coordinator") instead of the clean slug.
        $dep          = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();
        $contract     = WorkerRegistry::resolve($dep->worker_slug ?? 'ava');
        $groups       = PipelineStageService::groupedStages($contract->pipelineStages());
        $allLogKeys   = collect($groups)->pluck('log_stage_keys')->flatten()->all();

        $completedStages = DB::table('transaction_stage_log')
            ->where('tx_id', $txId)
            ->where('event', 'completed')
            ->whereIn('stage_key', $allLogKeys)
            ->orderBy('created_at')
            ->get()
            ->groupBy('stage_key');

        $stages = [];
        foreach ($groups as $group) {
            // A checkpoint completes when the last of its raw stages logs
            // "completed" — pick whichever of its log keys logged latest.
            $log = collect($group['log_stage_keys'])
                ->map(fn($key) => $completedStages->get($key)?->last())
                ->filter()
                ->sortBy('created_at')
                ->last();
            if (!$log) continue; // only show checkpoints that actually ran

            $payload = $group['output_column']
                ? (json_decode($tx->{$group['output_column']} ?? 'null', true) ?? [])
                : ['gmail_draft_id' => $tx->gmail_draft_id];

            $summary = $log->context_summary;
            if (!$summary) {
                $summary = $this->generateStageContext($claude, $txId, $group['key'], $group['label'], $payload, $tx);
                DB::table('transaction_stage_log')->where('id', $log->id)->update(['context_summary' => $summary]);
            }

            $canvas = $group['key'] === 'prepared'
                ? ['type' => 'email', 'payload' => [
                    'to'      => $payload['recipient_email'] ?? null,
                    'from'    => auth()->user()->email,
                    'subject' => $payload['subject'] ?? null,
                    'body'    => $payload['body'] ?? $payload['email_body'] ?? null,
                  ]]
                : ['type' => 'data', 'payload' => $payload];

            $stages[] = [
                'stage_key' => $group['key'],
                'label'     => $group['label'],
                'color'     => $group['color'],
                'timestamp' => \Carbon\Carbon::parse($log->created_at)->format('g:i A'),
                'summary'   => $summary,
                'canvas'    => $canvas,
            ];
        }

        return response()->json([
            'tx_id'  => $tx->tx_id,
            'status' => $tx->status,
            'stages' => $stages,
        ]);
    }

    private function generateStageContext(ClaudeService $claude, string $txId, string $stageKey, string $label, array $payload, object $tx): string
    {
        try {
            $claude->configure(ClaudeService::platformDefaultModel(), auth()->id(), 'ava');

            $system = 'You write single-sentence executive status updates for an internal business workflow report, '
                . 'as if a regional manager were briefing the president of the company. Past tense, factual, under 20 words, '
                . 'plain text only, no quotes, no markdown. Reference specific details (amounts, dates, names) when available in the data.';

            $user = "Stage: {$label} ({$stageKey})\n"
                . "Transaction category: " . ($tx->category ?? 'Unclassified') . "\n"
                . "Stage data: " . json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE);

            return $claude->askForText($system, $user, 80, $txId, $stageKey);
        } catch (\Throwable $e) {
            return $label; // fall back to the plain badge label if AI generation fails
        }
    }
}

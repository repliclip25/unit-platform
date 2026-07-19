<?php

namespace App\Http\Controllers;

use App\Platform\Services\ClaudeService;
use App\Platform\Services\WorkerShellService;
use Illuminate\Support\Facades\DB;

class DeskController extends Controller
{
    // stage_key => [output column, business-facing badge label, timeline dot color]
    private const STAGE_META = [
        'read'     => ['col' => 'read_output',     'label' => 'Reviewed',  'color' => '#6366f1'],
        'classify' => ['col' => 'classify_output', 'label' => 'Assessed',  'color' => '#f59e0b'],
        'memory'   => ['col' => 'memory_output',   'label' => 'Verified',  'color' => '#8b5cf6'],
        'draft'    => ['col' => 'draft_output',    'label' => 'Prepared',  'color' => '#f97316'],
        'push'     => ['col' => null,              'label' => 'Delivered','color' => '#06b6d4'],
    ];
    public function ava()
    {
        $userId = auth()->id();

        $dep = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('worker_slug', 'ava')
            ->whereIn('status', ['active', 'paused'])
            ->first();

        if (!$dep) {
            return redirect()->route('dashboard');
        }

        $depId = $dep->id;

        // Pipeline counts
        $incomingCount   = DB::table('transactions')->where('deployment_id', $depId)->whereDate('created_at', today())->count();
        $inProgressCount = DB::table('transactions')->where('deployment_id', $depId)
            ->whereNotIn('status', ['draft_ready','approved','sent','failed','dismissed','filtered_out','rejected','blocked'])
            ->count();
        $waitingCount    = DB::table('transactions')->where('deployment_id', $depId)->where('status', 'draft_ready')->whereNull('human_decision')->count();
        $completedCount  = DB::table('transactions')->where('deployment_id', $depId)->whereIn('status', ['approved','sent'])->whereDate('updated_at', today())->count();

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

        return view('desk.ava', compact(
            'dep', 'depId', 'incomingCount', 'inProgressCount', 'waitingCount', 'completedCount',
            'approvals', 'activity', 'currentTask', 'clientCount', 'contactCount',
            'assetCount', 'ruleCount', 'templateCount', 'credentialCount', 'policyViolations',
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

        $completedStages = DB::table('transaction_stage_log')
            ->where('tx_id', $txId)
            ->where('event', 'completed')
            ->whereIn('stage_key', array_keys(self::STAGE_META))
            ->orderBy('created_at')
            ->get()
            ->keyBy('stage_key');

        $stages = [];
        foreach (self::STAGE_META as $stageKey => $meta) {
            $log = $completedStages->get($stageKey);
            if (!$log) continue; // only show stages that actually ran

            $payload = $meta['col']
                ? (json_decode($tx->{$meta['col']} ?? 'null', true) ?? [])
                : ['gmail_draft_id' => $tx->gmail_draft_id];

            $summary = $log->context_summary;
            if (!$summary) {
                $summary = $this->generateStageContext($claude, $txId, $stageKey, $meta['label'], $payload, $tx);
                DB::table('transaction_stage_log')->where('id', $log->id)->update(['context_summary' => $summary]);
            }

            $canvas = $stageKey === 'draft'
                ? ['type' => 'email', 'payload' => [
                    'to'      => $payload['recipient_email'] ?? null,
                    'from'    => auth()->user()->email,
                    'subject' => $payload['subject'] ?? null,
                    'body'    => $payload['body'] ?? $payload['email_body'] ?? null,
                  ]]
                : ['type' => 'data', 'payload' => $payload];

            $stages[] = [
                'stage_key' => $stageKey,
                'label'     => $meta['label'],
                'color'     => $meta['color'],
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

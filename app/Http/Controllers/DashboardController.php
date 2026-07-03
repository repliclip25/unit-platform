<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Platform\Services\ClockResolver;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // ── Overview list data ────────────────────────────────────────────────
        $weekStart    = now()->startOfWeek();
        $ovProcessed  = DB::table('transactions')->where('user_id', $userId)->where('created_at', '>=', $weekStart)->count();
        $ovDrafts     = DB::table('transactions')->where('user_id', $userId)->where('status', 'draft_ready')->whereNull('human_decision')->count();
        $ovUrgent     = DB::table('transactions')->where('user_id', $userId)->where('status', 'draft_ready')->whereIn('priority', ['High','Critical'])->count();
        $ovFailed     = DB::table('transactions')->where('user_id', $userId)->where('status', 'failed')->where('created_at', '>=', $weekStart)->count();
        $ovStuck      = DB::table('transactions')->where('user_id', $userId)
            ->whereNotIn('status', ['draft_ready','approved','sent','failed','dismissed','filtered_out'])
            ->where('updated_at', '<', now()->subMinutes(5))->count();

        // ── Platform Value Clock — aggregated from each worker's contract ────
        // Resolved after $deployments is built below; placeholder until then.
        $clockValue = 0;

        // ── Pipeline-level stats (kept for worker cards) ──────────────────────
        $pipelineActive = ['received','ingesting','reading','classifying','memory_lookup','logging','templating','drafting','pushing'];
        $pipeline = [
            'total'       => DB::table('transactions')->where('user_id', $userId)->count(),
            'in_pipeline' => DB::table('transactions')->where('user_id', $userId)->whereIn('status', $pipelineActive)->count(),
            'needs_review'=> $ovDrafts,
            'failed'      => $ovFailed,
        ];

        // ── Worker cards ──
        $deployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'paused'])
            ->orderBy('created_at')
            ->get();

        // Aggregate Value Clock across all deployed workers using their contracts
        $clockRaw = 0;
        foreach ($deployments as $dep) {
            $c = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
            if (\App\Platform\Services\WorkerRegistry::isNull($c)) continue;
            $def = $c->valueClock();
            if (empty($def)) continue;
            $clockRaw += \App\Platform\Services\ClockResolver::resolveWorker($dep->id, $def)['raw'];
        }
        $clockValue = is_float($clockRaw) ? round($clockRaw, 1) : (int) $clockRaw;

        $workerCards = $deployments->map(function ($dep) use ($userId) {
            $contract = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
            if (!$contract) return null;

            $dash  = $contract->dashboard();
            $depId = $dep->id;

            // Worker-specific stats
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

            $drafts   = (int) ($stats->firstWhere('key', 'tx_draft_ready')['value'] ?? 0);
            $approved = (int) ($stats->firstWhere('key', 'tx_approved')['value']    ?? 0);
            $total    = (int) ($stats->firstWhere('key', 'tx_total')['value']       ?? 0);
            $failed   = (int) ($stats->firstWhere('key', 'tx_failed')['value']      ?? 0);

            // Personal morning quote — worker speaks in first person
            $overview  = method_exists($contract, 'overview') ? $contract->overview() : [];
            $verbs     = $overview['briefing_verbs'] ?? [];
            $verbProc  = $verbs['processed'] ?? 'processed';
            $unit      = $verbs['unit']      ?? 'items';
            $outputWord= $verbs['output']    ?? 'drafts';

            if ($total === 0) {
                $quote = "Ready and standing by — send me something to work on.";
                $cta   = ['label' => 'Run Fast Track', 'url' => route('workers.show', $dep->worker_slug) . '#fast-track'];
            } elseif ($drafts > 0) {
                $quote = "Morning! I've {$verbProc} {$total} {$unit} and prepared {$drafts} " . ($drafts === 1 ? $outputWord : $outputWord . 's') . ". " . ($drafts === 1 ? 'It needs' : 'They need') . " your approval.";
                $cta   = ['label' => 'Review now', 'url' => route('transactions', ['filter' => 'draft_ready'])];
            } elseif ($failed > 0) {
                $quote = "I hit {$failed} " . ($failed === 1 ? 'issue' : 'issues') . " I couldn't resolve on my own — flagged for your review.";
                $cta   = ['label' => 'See what happened', 'url' => route('workers.show', $dep->worker_slug)];
            } elseif ($approved > 0) {
                $quote = "All caught up. {$approved} " . ($approved === 1 ? $outputWord . ' has' : $outputWord . 's have') . " been approved and sent — nothing waiting on you.";
                $cta   = ['label' => 'Open workspace', 'url' => route('workers.show', $dep->worker_slug)];
            } else {
                $quote = "Working through the queue — I'll flag anything that needs you.";
                $cta   = ['label' => 'Open workspace', 'url' => route('workers.show', $dep->worker_slug)];
            }

            // Last run
            $lastTx = DB::table('transactions')->where('deployment_id', $depId)->orderByDesc('id')->first();

            // Connection health
            $inboxes = DB::table('deployment_credentials')
                ->join('user_gmail_credentials', 'user_gmail_credentials.id', '=', 'deployment_credentials.credential_id')
                ->where('deployment_credentials.deployment_id', $depId)
                ->select('user_gmail_credentials.gmail_address', 'user_gmail_credentials.watch_active', 'deployment_credentials.is_primary')
                ->get();

            $billing     = DB::table('deployment_billing')->where('deployment_id', $depId)->first();
            $registryRow = DB::table('worker_registry')->where('slug', $dep->worker_slug)->first();
            $employee    = method_exists($contract, 'employee') ? $contract->employee() : [];

            return [
                'dep'         => $dep,
                'contract'    => $contract,
                'dash'        => $dash,
                'stats'       => $stats,
                'lastTx'      => $lastTx,
                'inboxes'     => $inboxes,
                'billing'     => $billing,
                'registryRow' => $registryRow,
                'employee'    => $employee,
                'quote'       => $quote,
                'cta'         => $cta,
                'drafts'      => $drafts,
            ];
        })->filter()->values();

        // ── Notifications — delegated to NotificationEngine ──
        $notifications = \App\Platform\Services\NotificationEngine::evaluate($userId, auth()->user()->role ?? 'tenant');

        // ── Referral ──
        $referralCode = \App\Platform\Services\ReferralService::ensureCode($userId);
        $referralUrl  = url('/register?ref=' . $referralCode);

        // Engagement gate: show referral banner only once the user has genuinely used the product.
        // Threshold: 5+ real (non-fast-track) transactions processed. Until then, show a small chip.
        $realTxCount      = DB::table('transactions')
            ->where('user_id', $userId)
            ->whereNotIn('status', ['received', 'failed'])
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(raw_input, '$.source')) != 'fast_track'")
            ->count();
        $referralEligible = $realTxCount >= 5;

        return view('dashboard.index', compact(
            'pipeline', 'workerCards', 'notifications', 'referralCode', 'referralUrl', 'referralEligible',
            'ovProcessed', 'ovDrafts', 'ovUrgent', 'ovFailed', 'ovStuck', 'clockValue'
        ));
    }
}

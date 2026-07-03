<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

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

        // ── Platform Value Clock — total hours returned this week ─────────────
        $clockValue = round($ovProcessed * 0.25, 1);

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

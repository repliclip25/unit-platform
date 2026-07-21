<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Platform\Services\ClockResolver;
use App\Platform\Services\DeskService;
use App\Platform\Services\DeskCardRegistry;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // ── Raw pipeline counts (fed into DeskService context) ────────────────
        $weekStart   = now()->startOfWeek();
        $ovProcessed = DB::table('transactions')->where('user_id', $userId)->where('created_at', '>=', $weekStart)->count();
        $ovDrafts    = DB::table('transactions')->where('user_id', $userId)->where('status', 'draft_ready')->whereNull('human_decision')->count();
        $ovUrgent    = DB::table('transactions')->where('user_id', $userId)->where('status', 'draft_ready')->whereIn('priority', ['High','Critical'])->count();
        $ovFailed    = DB::table('transactions')->where('user_id', $userId)->where('status', 'failed')->where('created_at', '>=', $weekStart)->count();
        $ovStuck     = DB::table('transactions')->where('user_id', $userId)
            ->whereNotIn('status', ['draft_ready','approved','sent','failed','dismissed','filtered_out'])
            ->where('updated_at', '<', now()->subMinutes(5))->count();

        // ── Gmail URL for drafts deep-link ────────────────────────────────────
        $primaryCred = DB::table('user_gmail_credentials')->where('user_id', $userId)->where('watch_active', true)->first()
            ?? DB::table('user_gmail_credentials')->where('user_id', $userId)->first();
        $gmailUrl = $primaryCred
            ? 'https://mail.google.com/mail/u/' . urlencode($primaryCred->gmail_address) . '/#drafts'
            : 'https://mail.google.com/mail/#drafts';

        // ── Deployments ───────────────────────────────────────────────────────
        $deployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'paused'])
            ->orderBy('created_at')
            ->get();

        // ── Platform Value Clock — aggregated from each worker's contract ─────
        $clockRaw = 0;
        foreach ($deployments as $dep) {
            $c = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
            if (\App\Platform\Services\WorkerRegistry::isNull($c)) continue;
            $def = $c->valueClock();
            if (empty($def)) continue;
            $clockRaw += ClockResolver::resolveWorker($dep->id, $def)['raw'];
        }
        $clockValue = is_float($clockRaw) ? round($clockRaw, 1) : (int) $clockRaw;

        // ── Your Desk ─────────────────────────────────────────────────────────
        $deskCards = DeskService::resolve($userId, [
            'ovProcessed' => $ovProcessed,
            'ovDrafts'    => $ovDrafts,
            'ovUrgent'    => $ovUrgent,
            'ovFailed'    => $ovFailed,
            'ovStuck'     => $ovStuck,
            'gmailUrl'    => $gmailUrl,
        ]);

        // All cards (for customize drawer)
        $deskAllCards = DeskService::allForUser($userId);

        // ── Worker cards ──────────────────────────────────────────────────────
        $workerCards = $deployments->map(function ($dep) use ($userId) {
            $contract = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
            if (!$contract) return null;

            $dash  = $contract->dashboard();
            $depId = $dep->id;

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

            $drafts    = (int) ($stats->firstWhere('key', 'tx_draft_ready')['value'] ?? 0);
            $approved  = (int) ($stats->firstWhere('key', 'tx_approved')['value']    ?? 0);
            $total     = (int) ($stats->firstWhere('key', 'tx_total')['value']       ?? 0);
            $failed    = (int) ($stats->firstWhere('key', 'tx_failed')['value']      ?? 0);

            $overview   = method_exists($contract, 'overview') ? $contract->overview() : [];
            $verbs      = $overview['briefing_verbs'] ?? [];
            $verbProc   = $verbs['processed'] ?? 'processed';
            $unit       = $verbs['unit']      ?? 'items';
            $outputWord = $verbs['output']    ?? 'drafts';

            if ($total === 0) {
                $quote = "Ready and standing by — send me something to work on.";
                $cta   = ['label' => 'Run Fast Track', 'url' => route('app.workers.show', $dep->worker_slug) . '#fast-track'];
            } elseif ($drafts > 0) {
                $quote = "Morning! I've {$verbProc} {$total} {$unit} and prepared {$drafts} " . ($drafts === 1 ? $outputWord : $outputWord . 's') . ". " . ($drafts === 1 ? 'It needs' : 'They need') . " your approval.";
                $cta   = ['label' => 'Review now', 'url' => route('app.transactions', ['filter' => 'draft_ready'])];
            } elseif ($failed > 0) {
                $quote = "I hit {$failed} " . ($failed === 1 ? 'issue' : 'issues') . " I couldn't resolve on my own — flagged for your review.";
                $cta   = ['label' => 'See what happened', 'url' => route('app.workers.show', $dep->worker_slug)];
            } elseif ($approved > 0) {
                $quote = "All caught up. {$approved} " . ($approved === 1 ? $outputWord . ' has' : $outputWord . 's have') . " been approved and sent — nothing waiting on you.";
                $cta   = ['label' => 'Open workspace', 'url' => route('app.workers.show', $dep->worker_slug)];
            } else {
                $quote = "Working through the queue — I'll flag anything that needs you.";
                $cta   = ['label' => 'Open workspace', 'url' => route('app.workers.show', $dep->worker_slug)];
            }

            $lastTx      = DB::table('transactions')->where('deployment_id', $depId)->orderByDesc('id')->first();
            $inboxes     = DB::table('deployment_credentials')
                ->join('user_gmail_credentials', 'user_gmail_credentials.id', '=', 'deployment_credentials.credential_id')
                ->where('deployment_credentials.deployment_id', $depId)
                ->select('user_gmail_credentials.gmail_address', 'user_gmail_credentials.watch_active', 'deployment_credentials.is_primary')
                ->get();
            $billing     = DB::table('deployment_billing')->where('deployment_id', $depId)->first();
            $registryRow = DB::table('worker_registry')->where('slug', $dep->worker_slug)->first();
            $employee    = method_exists($contract, 'employee') ? $contract->employee() : [];

            return compact('dep', 'contract', 'dash', 'stats', 'lastTx', 'inboxes', 'billing', 'registryRow', 'employee', 'quote', 'cta', 'drafts');
        })->filter()->values();

        // ── Notifications ─────────────────────────────────────────────────────
        $notifications = \App\Platform\Services\NotificationEngine::evaluate($userId, auth()->user()->role ?? 'tenant');

        // ── Referral ──────────────────────────────────────────────────────────
        $referralCode     = \App\Platform\Services\ReferralService::ensureCode($userId);
        $referralUrl      = url('/register?ref=' . $referralCode);
        $realTxCount      = DB::table('transactions')
            ->where('user_id', $userId)
            ->whereNotIn('status', ['received', 'failed'])
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(raw_input, '$.source')) != 'fast_track'")
            ->count();
        $referralEligible = $realTxCount >= 5;

        $shell = \App\Platform\Services\WorkerShellService::build($userId, '');
        extract($shell); // workerCatalog, registryRows, registryRow, profileImg, coverImg, tokenTotal
        $firstName = explode(' ', trim(auth()->user()->name))[0];

        return view('dashboard.index', compact(
            'workerCards', 'notifications', 'referralCode', 'referralUrl', 'referralEligible',
            'clockValue', 'deskCards', 'deskAllCards',
            'ovProcessed', 'ovDrafts', 'ovUrgent', 'ovFailed', 'ovStuck',
            'workerCatalog', 'tokenTotal', 'firstName'
        ));
    }

    // ── AVA Desk ───────────────────────────────────────────────────────────────
    public function avaDesk()
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
        $today = now()->startOfDay();

        // Pipeline counts
        $incomingCount  = DB::table('transactions')->where('deployment_id', $depId)->whereDate('created_at', today())->count();
        $inProgressCount= DB::table('transactions')->where('deployment_id', $depId)
            ->whereNotIn('status', ['draft_ready','approved','sent','failed','dismissed','filtered_out','rejected','blocked'])
            ->count();
        $waitingCount   = DB::table('transactions')->where('deployment_id', $depId)->where('status', 'draft_ready')->whereNull('human_decision')->count();
        $completedCount = DB::table('transactions')->where('deployment_id', $depId)->whereIn('status', ['approved','sent'])->whereDate('updated_at', today())->count();

        // Approvals queue (draft_ready, newest first, limit 5)
        $approvals = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->where('status', 'draft_ready')
            ->whereNull('human_decision')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Recent activity (last 6 transactions)
        $activity = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        // Current task (most recent in-progress)
        $currentTask = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->whereNotIn('status', ['draft_ready','approved','sent','failed','dismissed','filtered_out','rejected','blocked'])
            ->orderByDesc('updated_at')
            ->first()
            ?? DB::table('transactions')->where('deployment_id', $depId)->orderByDesc('id')->first();

        // Memory stats
        $clientCount  = DB::table('clients')->where('user_id', $userId)->count();
        $contactCount = DB::table('contacts')->where('user_id', $userId)->count();
        $assetCount   = DB::table('assets')->where('user_id', $userId)->count();
        $memoryTotal  = max(1, $clientCount + $contactCount + $assetCount);
        $memoryPct    = min(100, (int) round(($clientCount / max(1, $memoryTotal)) * 100));

        // All deployments for worker switcher sidebar
        $allDeployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active','paused'])
            ->orderBy('created_at')
            ->get();

        $registryRow = DB::table('worker_registry')->where('slug', 'ava')->first();
        $profileImg  = $registryRow?->profile_image ? asset('storage/' . $registryRow->profile_image) : null;
        $coverImg    = $registryRow?->cover_image   ? asset('storage/' . $registryRow->cover_image)   : null;

        $workStatus  = $dep->status === 'active' ? 'Working' : 'Paused';
        $firstName   = explode(' ', trim(auth()->user()->name))[0];

        return view('dashboard.ava-desk', compact(
            'dep', 'depId', 'incomingCount', 'inProgressCount', 'waitingCount', 'completedCount',
            'approvals', 'activity', 'currentTask', 'clientCount', 'contactCount', 'assetCount',
            'memoryPct', 'allDeployments', 'registryRow', 'profileImg', 'coverImg',
            'workStatus', 'firstName'
        ));
    }

    // ── Save desk card preferences ─────────────────────────────────────────────
    public function deskSave(Request $request)
    {
        $userId = auth()->id();
        $cards  = $request->input('cards', []); // [['key'=>..,'visible'=>bool,'position'=>int]]

        foreach ($cards as $card) {
            $key = $card['key'] ?? null;
            if (!$key || !DeskCardRegistry::get($key)) continue;

            DB::table('user_desk_cards')->updateOrInsert(
                ['user_id' => $userId, 'card_key' => $key],
                [
                    'visible'    => (bool) ($card['visible'] ?? true),
                    'position'   => (int)  ($card['position'] ?? 50),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return response()->json(['ok' => true]);
    }

    // ── Dismiss a Self Learn panel ─────────────────────────────────────────────
    public function selfLearnDismiss(Request $request)
    {
        $key    = $request->input('page_key');
        $userId = auth()->id();
        if (!$key) return response()->json(['ok' => false], 422);

        $version = (int) DB::table('platform_self_learn')
            ->where('page_key', $key)
            ->value('version') ?: 1;

        DB::table('user_self_learn_dismissed')->updateOrInsert(
            ['user_id' => $userId, 'page_key' => $key],
            ['version' => $version, 'dismissed_at' => now()]
        );

        DB::table('user_self_learn_events')->insert([
            'user_id'    => $userId,
            'page_key'   => $key,
            'event'      => 'dismissed',
            'version'    => $version,
            'created_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Dismiss a one-time card ────────────────────────────────────────────────
    public function deskDismiss(Request $request)
    {
        $userId = auth()->id();
        $key    = $request->input('key');

        if (!$key || !DeskCardRegistry::get($key)) {
            return response()->json(['ok' => false], 422);
        }

        DB::table('user_desk_cards')->updateOrInsert(
            ['user_id' => $userId, 'card_key' => $key],
            ['last_dismissed_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['ok' => true]);
    }
}

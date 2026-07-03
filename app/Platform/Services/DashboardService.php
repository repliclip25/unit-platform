<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

/**
 * Resolves data for each overview panel type declared in a WorkerContract's overview() method.
 * The worker declares WHAT panels it wants. This service provides the DATA for each panel.
 * The Blade view renders generically — no per-worker rendering logic.
 */
class DashboardService
{
    public static function resolve(object $dep, array $overview): array
    {
        $panels   = $overview['panels'] ?? [];
        $userId   = $dep->user_id;
        $depId    = $dep->id;

        usort($panels, fn($a, $b) => ($a['priority'] ?? 99) <=> ($b['priority'] ?? 99));

        $slug = $dep->worker_slug;

        return array_map(fn($panel) => array_merge(
            $panel,
            ['data' => self::resolvePanel($panel, $depId, $userId, $slug)]
        ), $panels);
    }

    private static function resolvePanel(array $panel, int $depId, int $userId, string $slug = ''): array
    {
        return match($panel['type']) {
            'action_queue'  => self::actionQueue($depId, $panel),
            'horizon'       => self::horizon($userId, $panel),
            'metric_strip'  => self::metricStrip($depId, $panel),
            'proof_of_work' => self::proofOfWork($depId, $panel),
            'alert_feed'    => self::alertFeed($depId, $panel, $slug),
            'activity_feed' => self::activityFeed($depId, $panel),
            'insight'       => self::insight($depId, $panel),
            'status_map'    => self::statusMap($depId, $panel),
            default         => [],
        };
    }

    // ── action_queue ─────────────────────────────────────────────────────────
    // Items needing a human decision right now.

    private static function actionQueue(int $depId, array $panel): array
    {
        $txs = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->where('status', 'draft_ready')
            ->whereNull('human_decision')
            ->orderByDesc('created_at')
            ->limit($panel['max_items'] ?? 10)
            ->get();

        $items = $txs->map(function ($tx) {
            $payload = json_decode($tx->payload ?? '{}', true) ?? [];
            $output  = json_decode($tx->output  ?? '{}', true) ?? [];

            // Extract human-readable context from pipeline output
            $client   = $output['client_name']   ?? $payload['from_name'] ?? null;
            $asset    = $output['asset_name']     ?? $output['subject']    ?? $tx->tx_id;
            $category = $output['category']       ?? null;
            $priority = $tx->priority             ?? 'normal';

            // Renewal date urgency
            $renewalDate = $output['renewal_date'] ?? null;
            $daysLeft    = null;
            if ($renewalDate) {
                try { $daysLeft = (int) now()->diffInDays(\Carbon\Carbon::parse($renewalDate), false); } catch (\Throwable) {}
            }

            return [
                'tx_id'       => $tx->tx_id,
                'client'      => $client,
                'asset'       => $asset,
                'category'    => $category,
                'priority'    => $priority,
                'days_left'   => $daysLeft,
                'created_at'  => $tx->created_at,
                'age_hours'   => (int) now()->diffInHours($tx->created_at),
            ];
        })->all();

        return [
            'items' => $items,
            'count' => count($items),
        ];
    }

    // ── horizon ──────────────────────────────────────────────────────────────
    // Upcoming deadlines bucketed by time window.

    private static function horizon(int $userId, array $panel): array
    {
        $windows = $panel['windows'] ?? [30, 60, 90];
        $maxDays = max($windows);

        $assets = DB::table('assets')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->where('type', '!=', 'discovered')
            ->whereNotNull('renewal_date')
            ->whereRaw('renewal_date >= CURDATE()')
            ->whereRaw('renewal_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)', [$maxDays])
            ->orderBy('renewal_date')
            ->get();

        // Join client names
        $clientIds = $assets->pluck('client_id')->filter()->unique();
        $clients   = $clientIds->isNotEmpty()
            ? DB::table('clients')->whereIn('id', $clientIds)->pluck('name', 'id')
            : collect();

        $bucketed = [];
        $prev     = 0;
        foreach ($windows as $win) {
            $bucket = $assets->filter(function ($a) use ($prev, $win) {
                $days = (int) now()->diffInDays($a->renewal_date, false);
                return $days > $prev && $days <= $win;
            })->map(fn($a) => [
                'name'         => $a->name,
                'type'         => $a->type,
                'vendor'       => $a->vendor,
                'client'       => $clients[$a->client_id] ?? null,
                'renewal_date' => $a->renewal_date,
                'days_left'    => (int) now()->diffInDays($a->renewal_date, false),
            ])->values()->all();

            $bucketed[] = ['window' => $win, 'prev' => $prev, 'items' => $bucket];
            $prev = $win;
        }

        return [
            'buckets'   => $bucketed,
            'total'     => $assets->count(),
        ];
    }

    // ── metric_strip ─────────────────────────────────────────────────────────
    // KPI numbers for the current period.

    private static function metricStrip(int $depId, array $panel): array
    {
        return self::computeMetrics($depId, $panel['metrics'] ?? [], $panel['period'] ?? 'month');
    }

    // ── proof_of_work ────────────────────────────────────────────────────────
    // What the worker accomplished this period.

    private static function proofOfWork(int $depId, array $panel): array
    {
        return self::computeMetrics($depId, $panel['metrics'] ?? [], $panel['period'] ?? 'week');
    }

    private static function computeMetrics(int $depId, array $metricKeys, string $period): array
    {
        $start = match($period) {
            'week'    => now()->startOfWeek(),
            'month'   => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            default   => now()->startOfMonth(),
        };

        $txBase = DB::table('transactions')->where('deployment_id', $depId)->where('created_at', '>=', $start);

        $processed   = (clone $txBase)->count();
        $draftsReady = (clone $txBase)->where('status', 'draft_ready')->count();
        $approved    = (clone $txBase)->whereIn('status', ['approved', 'sent'])->count();
        $failed      = (clone $txBase)->where('status', 'failed')->count();
        $dismissed   = (clone $txBase)->where('status', 'dismissed')->count();

        // Response rate: approved / (approved + dismissed + failed) — only meaningful if there's volume
        $resolved = $approved + $dismissed + $failed;
        $responseRate = $resolved > 0 ? round(($approved / $resolved) * 100) : null;

        // Time saved estimate: 15 min per email processed
        $hoursSaved = round($processed * 0.25, 1);

        $allMetrics = [
            'emails_processed' => ['label' => 'Emails Processed',  'value' => $processed,    'suffix' => ''],
            'drafts_ready'     => ['label' => 'Drafts Ready',      'value' => $draftsReady,  'suffix' => ''],
            'approved_sent'    => ['label' => 'Approved & Sent',   'value' => $approved,     'suffix' => ''],
            'hours_saved'      => ['label' => 'Est. Hours Saved',  'value' => $hoursSaved,   'suffix' => 'h'],
            'response_rate'    => ['label' => 'Response Rate',     'value' => $responseRate, 'suffix' => '%'],
            'failed'           => ['label' => 'Failed',            'value' => $failed,       'suffix' => ''],
        ];

        $result = [];
        foreach ($metricKeys as $key) {
            if (isset($allMetrics[$key])) {
                $result[] = array_merge(['key' => $key], $allMetrics[$key]);
            }
        }

        return ['metrics' => $result, 'period' => $period, 'since' => $start->toDateString()];
    }

    // ── alert_feed ───────────────────────────────────────────────────────────
    // Issues needing awareness.

    private static function alertFeed(int $depId, array $panel, string $slug = ''): array
    {
        $alerts = [];

        // Drafts sitting unreviewed for >3 days
        $staleDrafts = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->where('status', 'draft_ready')
            ->whereNull('human_decision')
            ->where('created_at', '<', now()->subDays(3))
            ->count();
        if ($staleDrafts > 0) {
            $alerts[] = [
                'severity' => 'warning',
                'message'  => "{$staleDrafts} draft" . ($staleDrafts > 1 ? 's have' : ' has') . " been waiting for review for more than 3 days.",
                'action'   => 'Review',
                'route'    => 'transactions',
                'params'   => ['filter' => 'draft_ready'],
            ];
        }

        // Transactions stuck in pipeline > 10 min
        $stuck = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->whereNotIn('status', ['draft_ready', 'approved', 'sent', 'failed', 'dismissed', 'rejected', 'blocked'])
            ->where('updated_at', '<', now()->subMinutes(10))
            ->count();
        if ($stuck > 0) {
            $alerts[] = [
                'severity' => 'error',
                'message'  => "{$stuck} transaction" . ($stuck > 1 ? 's are' : ' is') . " stuck mid-pipeline and may need recovery.",
                'action'   => 'Recover',
                'route'    => 'qa.recover-stuck',
                'params'   => [],
            ];
        }

        // High failure rate this week (>20% of processed)
        $weekStart  = now()->startOfWeek();
        $weekTotal  = DB::table('transactions')->where('deployment_id', $depId)->where('created_at', '>=', $weekStart)->count();
        $weekFailed = DB::table('transactions')->where('deployment_id', $depId)->where('status', 'failed')->where('created_at', '>=', $weekStart)->count();
        if ($weekTotal > 5 && $weekFailed / $weekTotal > 0.20) {
            $pct = round(($weekFailed / $weekTotal) * 100);
            $alerts[] = [
                'severity' => 'warning',
                'message'  => "{$pct}% of this week's emails failed processing — check the Log tab for details.",
                'action'   => 'View Log',
                'route'    => 'workers.log',
                'params'   => ['slug' => $slug],
            ];
        }

        return ['alerts' => $alerts, 'count' => count($alerts)];
    }

    // ── activity_feed ────────────────────────────────────────────────────────
    // Human-readable chronological log.

    private static function activityFeed(int $depId, array $panel): array
    {
        $txs = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->orderByDesc('created_at')
            ->limit($panel['limit'] ?? 8)
            ->get();

        $items = $txs->map(function ($tx) {
            $output   = json_decode($tx->output  ?? '{}', true) ?? [];
            $payload  = json_decode($tx->payload ?? '{}', true) ?? [];

            $client   = $output['client_name'] ?? $payload['from_name'] ?? 'Unknown sender';
            $asset    = $output['asset_name']  ?? null;
            $category = $output['category']    ?? null;

            $sentence = self::activitySentence($tx->status, $tx->human_decision, $client, $asset, $category);

            return [
                'tx_id'      => $tx->tx_id,
                'status'     => $tx->status,
                'sentence'   => $sentence,
                'created_at' => $tx->created_at,
            ];
        })->all();

        return ['items' => $items];
    }

    private static function activitySentence(string $status, ?string $decision, string $client, ?string $asset, ?string $category): string
    {
        $subject = $asset ? "\"{$asset}\"" : ($category ? "a {$category} request" : "an email");

        return match(true) {
            $status === 'draft_ready' && !$decision => "AVA drafted a response to {$client} regarding {$subject} — awaiting your review.",
            $status === 'approved'                  => "You approved AVA's response to {$client} regarding {$subject}.",
            $status === 'sent'                      => "Response sent to {$client} regarding {$subject}.",
            $status === 'dismissed'                 => "Email from {$client} was dismissed.",
            $status === 'rejected'                  => "You rejected AVA's draft for {$client}.",
            $status === 'failed'                    => "AVA could not process an email from {$client} — check the Log.",
            $status === 'blocked'                   => "Processing blocked for {$client} — spending limit reached.",
            default                                 => "AVA processed {$subject} from {$client}.",
        };
    }

    // ── insight ──────────────────────────────────────────────────────────────
    // AI-generated briefing (optional, expensive — cache aggressively).

    private static function insight(int $depId, array $panel): array
    {
        // Reserved for future implementation — requires ClaudeService call + caching
        return ['text' => null, 'generated_at' => null];
    }

    // ── status_map ───────────────────────────────────────────────────────────
    // Visual state breakdown (optional).

    private static function statusMap(int $depId, array $panel): array
    {
        $counts = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        return ['states' => $counts];
    }
}

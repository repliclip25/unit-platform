<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminPipelineHealthController extends Controller
{
    private const STAGE_ORDER = ['filter','ingest','read','classify','memory','log','template','draft','push'];

    public function index()
    {
        $window = now()->subHours(24);

        // ── Per-stage summary (last 24h) ──────────────────────────────────
        $stageSummary = DB::table('transaction_stage_log')
            ->where('created_at', '>=', $window)
            ->selectRaw("
                stage_key,
                COUNT(*) as total,
                SUM(event = 'started')   as started,
                SUM(event = 'completed') as completed,
                SUM(event = 'failed')    as failed,
                ROUND(AVG(CASE WHEN event = 'completed' THEN duration_ms END)) as avg_ms,
                ROUND(MAX(CASE WHEN event = 'completed' THEN duration_ms END)) as max_ms
            ")
            ->groupBy('stage_key')
            ->get()
            ->keyBy('stage_key');

        // ── Transactions currently stuck (started but no completed/failed in >5 min) ──
        $stuck = DB::table('transaction_stage_log as s')
            ->where('s.event', 'started')
            ->where('s.created_at', '<=', now()->subMinutes(5))
            ->whereNotExists(function ($q) {
                $q->from('transaction_stage_log as c')
                    ->whereColumn('c.tx_id', 's.tx_id')
                    ->whereColumn('c.stage_key', 's.stage_key')
                    ->whereIn('c.event', ['completed', 'failed'])
                    ->where('c.id', '>', DB::raw('s.id'));
            })
            ->join('transactions as t', 't.tx_id', '=', 's.tx_id')
            ->select('s.tx_id', 's.stage_key', 's.created_at', 's.attempt', 't.user_id', 't.deployment_id', 't.worker_slug')
            ->orderByDesc('s.created_at')
            ->limit(50)
            ->get();

        // ── Recent failures (last 24h) ────────────────────────────────────
        $recentFailures = DB::table('transaction_stage_log as s')
            ->where('s.event', 'failed')
            ->where('s.created_at', '>=', $window)
            ->join('transactions as t', 't.tx_id', '=', 's.tx_id')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->select('s.tx_id', 's.stage_key', 's.error_summary', 's.attempt', 's.created_at', 'u.email', 't.worker_slug')
            ->orderByDesc('s.created_at')
            ->limit(100)
            ->get();

        // ── Avg duration per stage per worker (last 7 days) ──────────────
        $stageDurations = DB::table('transaction_stage_log')
            ->where('event', 'completed')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw("worker_slug, stage_key, ROUND(AVG(duration_ms)) as avg_ms, COUNT(*) as runs")
            ->groupBy('worker_slug', 'stage_key')
            ->orderBy('worker_slug')->orderBy('stage_key')
            ->get()
            ->groupBy('worker_slug');

        // ── Hourly throughput (last 24h) ──────────────────────────────────
        $hourlyThroughput = DB::table('transaction_stage_log')
            ->where('event', 'completed')
            ->where('stage_key', 'push')    // count completions at the final stage = full pipeline runs
            ->where('created_at', '>=', $window)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00') as hour, COUNT(*) as count")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00')")
            ->orderBy('hour')
            ->get();

        // ── Retry rate by stage ───────────────────────────────────────────
        $retryRate = DB::table('transaction_stage_log')
            ->where('created_at', '>=', $window)
            ->where('attempt', '>', 1)
            ->selectRaw("stage_key, COUNT(*) as retries")
            ->groupBy('stage_key')
            ->get()->keyBy('stage_key');

        return view('admin.pipeline-health', compact(
            'stageSummary', 'stuck', 'recentFailures', 'stageDurations',
            'hourlyThroughput', 'retryRate'
        ));
    }
}

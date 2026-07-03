<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AdminPlatformUsageController extends Controller
{
    public function index()
    {
        $byKey = DB::table('platform_usage_events')
            ->select('prompt_key',
                DB::raw('COUNT(*) as calls'),
                DB::raw('SUM(tokens_input) as tokens_in'),
                DB::raw('SUM(tokens_output) as tokens_out'),
                DB::raw('SUM(cost_usd) as total_cost'),
                DB::raw('MAX(created_at) as last_used'))
            ->groupBy('prompt_key')
            ->orderByDesc('total_cost')
            ->get();

        $totals = DB::table('platform_usage_events')
            ->select(
                DB::raw('COUNT(*) as calls'),
                DB::raw('SUM(tokens_input) as tokens_in'),
                DB::raw('SUM(tokens_output) as tokens_out'),
                DB::raw('SUM(cost_usd) as total_cost'))
            ->first();

        $recent = DB::table('platform_usage_events')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // Tenant (worker) usage from usage_events
        $byTenant = DB::table('usage_events')
            ->join('users', 'usage_events.user_id', '=', 'users.id')
            ->select(
                'usage_events.user_id',
                'users.name',
                'users.email',
                DB::raw('COUNT(*) as calls'),
                DB::raw('SUM(usage_events.tokens_input) as tokens_in'),
                DB::raw('SUM(usage_events.tokens_output) as tokens_out'),
                DB::raw('SUM(usage_events.cost_usd) as total_cost'),
                DB::raw('MAX(usage_events.created_at) as last_used'))
            ->groupBy('usage_events.user_id', 'users.name', 'users.email')
            ->orderByDesc('total_cost')
            ->get();

        $byWorker = DB::table('usage_events')
            ->select(
                'worker_slug',
                DB::raw('COUNT(*) as calls'),
                DB::raw('SUM(tokens_input) as tokens_in'),
                DB::raw('SUM(tokens_output) as tokens_out'),
                DB::raw('SUM(cost_usd) as total_cost'))
            ->groupBy('worker_slug')
            ->orderByDesc('total_cost')
            ->get();

        $tenantTotals = DB::table('usage_events')
            ->select(
                DB::raw('COUNT(*) as calls'),
                DB::raw('SUM(tokens_input) as tokens_in'),
                DB::raw('SUM(tokens_output) as tokens_out'),
                DB::raw('SUM(cost_usd) as total_cost'))
            ->first();

        // Per-stage breakdown — microscopic spend by worker+stage+model
        $byStage = DB::table('usage_events')
            ->select(
                'worker_slug',
                'stage',
                'model',
                DB::raw('COUNT(*) as calls'),
                DB::raw('SUM(tokens_input) as tokens_in'),
                DB::raw('SUM(tokens_output) as tokens_out'),
                DB::raw('SUM(cost_usd) as total_cost'),
                DB::raw('AVG(cost_usd) as avg_cost'),
                DB::raw('MAX(created_at) as last_used'))
            ->groupBy('worker_slug', 'stage', 'model')
            ->orderByDesc('total_cost')
            ->get();

        // Individual call log — atom-level, last 100 calls
        $callLog = DB::table('usage_events')
            ->join('users', 'usage_events.user_id', '=', 'users.id')
            ->select(
                'usage_events.worker_slug',
                'usage_events.stage',
                'usage_events.model',
                'usage_events.tx_id',
                'usage_events.tokens_input',
                'usage_events.tokens_output',
                'usage_events.cost_usd',
                'usage_events.created_at',
                'users.name as tenant_name')
            ->orderByDesc('usage_events.created_at')
            ->limit(100)
            ->get();

        return view('admin.platform-usage', compact(
            'byKey', 'totals', 'recent', 'byTenant', 'byWorker', 'tenantTotals',
            'byStage', 'callLog'
        ));
    }
}

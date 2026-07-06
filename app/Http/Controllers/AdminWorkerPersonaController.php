<?php

namespace App\Http\Controllers;

use App\Platform\Services\WorkerRegistry;
use Illuminate\Support\Facades\DB;

class AdminWorkerPersonaController extends Controller
{
    public function index(string $slug)
    {
        $worker   = DB::table('workers')->where('slug', $slug)->firstOrFail();
        $contract = WorkerRegistry::resolve($slug);
        $personas = $contract->personas();

        // Adoption: count deployments per persona for this worker
        $adoptionCounts = DB::table('worker_deployments')
            ->where('worker_slug', $slug)
            ->whereNotNull('persona')
            ->selectRaw('persona, COUNT(*) as total')
            ->groupBy('persona')
            ->pluck('total', 'persona')
            ->all();

        $totalDeployments = DB::table('worker_deployments')
            ->where('worker_slug', $slug)
            ->count();

        $noPersonaCount = DB::table('worker_deployments')
            ->where('worker_slug', $slug)
            ->whereNull('persona')
            ->count();

        // Platform master rule counts per persona
        $platformRuleCounts = DB::table('ava_rules')
            ->whereNull('user_id')
            ->whereNull('deployment_id')
            ->whereNotNull('persona')
            ->selectRaw('persona, COUNT(*) as total')
            ->groupBy('persona')
            ->pluck('total', 'persona')
            ->all();

        // Contract rule counts per persona (source of truth)
        $contractRuleCounts = [];
        foreach ($personas as $key => $p) {
            $contractRuleCounts[$key] = count($p['capture_rules'] ?? []);
        }

        return view('admin.worker-personas', compact(
            'worker', 'slug', 'personas',
            'adoptionCounts', 'totalDeployments', 'noPersonaCount',
            'platformRuleCounts', 'contractRuleCounts'
        ));
    }
}

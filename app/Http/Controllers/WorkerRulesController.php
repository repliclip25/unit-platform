<?php

namespace App\Http\Controllers;

use App\Platform\Services\PersonaRuleSeeder;
use App\Platform\Services\WorkerRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerRulesController extends Controller
{
    public function index(string $slug)
    {
        $dep = DB::table('worker_deployments')->where('user_id', auth()->id())
            ->where(fn($q) => $q->where('worker_slug', $slug)->when(is_numeric($slug), fn($q2) => $q2->orWhere('id', (int)$slug)))
            ->firstOrFail();

        $contract      = WorkerRegistry::resolve($dep->worker_slug ?? 'ava');
        $personas      = $contract->personas();
        $activePersona = $dep->persona ?? (empty($personas) ? null : array_key_first($personas));

        $allRules = DB::table('ava_rules')
            ->where('deployment_id', $dep->id)
            ->orderByRaw("FIELD(priority,'Critical','High','Medium','Low')")
            ->orderBy('rule_id')
            ->get();

        $rulesByPersona = [];
        $platformRules  = [];
        foreach ($allRules as $rule) {
            if ($rule->persona === null) {
                $platformRules[] = $rule;
            } else {
                $rulesByPersona[$rule->persona][] = $rule;
            }
        }

        // Diff each persona's deployed rules against the current contract definition
        $diffByPersona = [];
        foreach ($personas as $key => $p) {
            if (!empty($rulesByPersona[$key])) {
                $diffByPersona[$key] = PersonaRuleSeeder::diff(
                    $rulesByPersona[$key], $contract, $key
                );
            }
        }

        // Total stale/orphaned/missing across all personas for the banner
        $totalIssues = 0;
        foreach ($diffByPersona as $d) {
            $totalIssues += count($d['stale']) + count($d['orphaned']) + count($d['missing']);
        }

        return view('dashboard.worker-rules', compact(
            'dep', 'personas', 'activePersona',
            'rulesByPersona', 'platformRules',
            'diffByPersona', 'totalIssues'
        ));
    }

    public function store(int $id, Request $request)
    {
        $dep = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate([
            'condition' => 'required',
            'action'    => 'required',
            'priority'  => 'required|in:Critical,High,Medium,Low',
            'persona'   => 'required',
        ]);

        $contract = WorkerRegistry::resolve($dep->worker_slug ?? 'ava');
        $allowed  = array_keys($contract->personas());
        if (!in_array($request->persona, $allowed)) {
            return back()->withErrors(['persona' => 'Invalid persona.']);
        }

        $prefix = strtoupper(substr($request->persona, 0, 2));
        $last   = DB::table('ava_rules')
            ->where('deployment_id', $id)
            ->where('persona', $request->persona)
            ->orderByDesc('id')
            ->value('rule_id');

        if ($request->rule_id) {
            $ruleId = $request->rule_id;
        } elseif ($last && preg_match('/(\d+)$/', $last, $m)) {
            $ruleId = $prefix . '-' . str_pad((int)$m[1] + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $count  = DB::table('ava_rules')->where('deployment_id', $id)->where('persona', $request->persona)->count();
            $ruleId = $prefix . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        }

        DB::table('ava_rules')->insert([
            'user_id'           => auth()->id(),
            'deployment_id'     => $id,
            'persona'           => $request->persona,
            'rule_id'           => $ruleId,
            'condition'         => $request->condition,
            'priority'          => $request->priority,
            'action'            => $request->action,
            'approval_required' => $request->boolean('approval_required'),
            'notes'             => $request->notes,
            'active'            => true,
            'is_platform'       => false,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return back()->with('success', "Rule {$ruleId} added.");
    }

    public function destroy(int $id, int $rid)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DB::table('ava_rules')
            ->where('id', $rid)
            ->where('deployment_id', $id)
            ->where('is_platform', false)
            ->delete();
        return back()->with('success', 'Rule removed.');
    }

    /**
     * Reset all persona rules for this deployment to the latest contract definition.
     * Custom rules the tenant added manually are wiped — only contract rules are seeded.
     */
    public function resetToContract(int $id, Request $request)
    {
        $dep = DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        if (!$dep->persona) {
            return back()->withErrors(['reset' => 'No persona set for this deployment.']);
        }

        $contract = WorkerRegistry::resolve($dep->worker_slug ?? 'ava');
        PersonaRuleSeeder::seed($id, auth()->id(), $contract, $dep->persona);

        return back()->with('success', 'Rules reset to the latest ' . ucfirst(str_replace('_', ' ', $dep->persona)) . ' definition.');
    }
}

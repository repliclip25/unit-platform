<?php

namespace App\Http\Controllers;

use App\Platform\Services\PersonaRuleSeeder;
use App\Platform\Services\WorkerRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWorkerRulesController extends Controller
{
    public function index(string $slug)
    {
        $worker   = DB::table('workers')->where('slug', $slug)->firstOrFail();
        $contract = WorkerRegistry::resolve($slug);
        $personas = $contract->personas();

        // Platform master rules — no user_id or deployment_id
        $allRules = DB::table('ava_rules')
            ->whereNull('user_id')
            ->whereNull('deployment_id')
            ->orderByRaw("FIELD(priority,'Critical','High','Medium','Low')")
            ->orderBy('rule_id')
            ->get();

        $rulesByPersona = [];
        $platformRules  = []; // universal rules (persona IS NULL)
        foreach ($allRules as $rule) {
            if ($rule->persona === null) {
                $platformRules[] = $rule;
            } else {
                $rulesByPersona[$rule->persona][] = $rule;
            }
        }

        // Diff each persona's master rules against contract (shows sync state)
        $diffByPersona = [];
        foreach ($personas as $key => $p) {
            if (!empty($rulesByPersona[$key])) {
                $diffByPersona[$key] = PersonaRuleSeeder::diff(
                    $rulesByPersona[$key], $contract, $key
                );
            }
        }

        $activePersona = array_key_first($personas);

        return view('admin.worker-rules', compact(
            'worker', 'slug', 'personas', 'activePersona',
            'rulesByPersona', 'platformRules', 'diffByPersona'
        ));
    }

    public function store(Request $request, string $slug)
    {
        DB::table('workers')->where('slug', $slug)->firstOrFail();
        $request->validate([
            'condition' => 'required',
            'action'    => 'required',
            'priority'  => 'required|in:Critical,High,Medium,Low',
            'persona'   => 'required',
        ]);

        $contract = WorkerRegistry::resolve($slug);
        $allowed  = array_keys($contract->personas());
        if (!in_array($request->persona, $allowed)) {
            return back()->withErrors(['persona' => 'Invalid persona.']);
        }

        $prefix = strtoupper(substr($request->persona, 0, 2));
        $last   = DB::table('ava_rules')
            ->whereNull('user_id')->whereNull('deployment_id')
            ->where('persona', $request->persona)
            ->orderByDesc('id')->value('rule_id');

        if ($request->rule_id) {
            $ruleId = $request->rule_id;
        } elseif ($last && preg_match('/(\d+)$/', $last, $m)) {
            $ruleId = $prefix . '-' . str_pad((int)$m[1] + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $count  = DB::table('ava_rules')->whereNull('user_id')->whereNull('deployment_id')->where('persona', $request->persona)->count();
            $ruleId = $prefix . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        }

        DB::table('ava_rules')->insert([
            'user_id'           => null,
            'deployment_id'     => null,
            'persona'           => $request->persona,
            'rule_id'           => $ruleId,
            'condition'         => $request->condition,
            'priority'          => $request->priority,
            'action'            => $request->action,
            'approval_required' => $request->boolean('approval_required'),
            'notes'             => $request->notes,
            'active'            => true,
            'is_platform'       => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return back()->with('success', "Platform rule {$ruleId} added.");
    }

    public function destroy(string $slug, int $id)
    {
        DB::table('workers')->where('slug', $slug)->firstOrFail();
        DB::table('ava_rules')
            ->where('id', $id)
            ->whereNull('user_id')
            ->whereNull('deployment_id')
            ->delete();
        return back()->with('success', 'Platform rule removed.');
    }

    /**
     * Sync platform master rules for a persona from the contract definition.
     * This replaces the master template — it does NOT touch existing tenant deployments.
     */
    public function syncFromContract(Request $request, string $slug)
    {
        $request->validate(['persona' => 'required']);
        $contract = WorkerRegistry::resolve($slug);
        $allowed  = array_keys($contract->personas());

        if (!in_array($request->persona, $allowed)) {
            return back()->withErrors(['persona' => 'Invalid persona.']);
        }

        PersonaRuleSeeder::seedPlatformRules($slug, $contract, $request->persona);

        return back()->with('success', ucfirst(str_replace('_', ' ', $request->persona)) . ' platform rules synced from contract.');
    }
}

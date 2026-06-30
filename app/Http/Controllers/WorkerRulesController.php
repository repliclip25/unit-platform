<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerRulesController extends Controller
{
    public function index(string $slug)
    {
        $dep = DB::table('worker_deployments')->where('user_id', auth()->id())
            ->where(fn($q) => $q->where('worker_slug', $slug)->when(is_numeric($slug), fn($q2) => $q2->orWhere('id', (int)$slug)))
            ->firstOrFail();
        $id    = $dep->id;
        $rules = DB::table('ava_rules')->where('deployment_id', $id)->orderBy('rule_id')->get();
        return view('dashboard.worker-rules', compact('dep', 'rules'));
    }

    public function store(int $id, Request $request)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['condition' => 'required', 'action' => 'required', 'priority' => 'required']);
        $last   = DB::table('ava_rules')->where('deployment_id', $id)->orderByDesc('id')->value('rule_id');
        $ruleId = $request->rule_id ?: ($last ? 'R-' . str_pad(intval(substr($last, 2)) + 1, 3, '0', STR_PAD_LEFT) : 'R-001');
        DB::table('ava_rules')->insert(['user_id' => auth()->id(), 'deployment_id' => $id, 'rule_id' => $ruleId, 'condition' => $request->condition, 'priority' => $request->priority, 'action' => $request->action, 'approval_required' => $request->boolean('approval_required'), 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Rule added.');
    }

    public function destroy(int $id, int $rid)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DB::table('ava_rules')->where('id', $rid)->where('deployment_id', $id)->delete();
        return back()->with('success', 'Rule removed.');
    }
}

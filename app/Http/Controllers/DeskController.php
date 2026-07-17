<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DeskController extends Controller
{
    public function ava()
    {
        $userId = auth()->id();

        $dep = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('worker_slug', 'ava')
            ->whereIn('status', ['active', 'paused'])
            ->first();

        if (!$dep) {
            return redirect()->route('dashboard');
        }

        $depId = $dep->id;

        // Pipeline counts
        $incomingCount   = DB::table('transactions')->where('deployment_id', $depId)->whereDate('created_at', today())->count();
        $inProgressCount = DB::table('transactions')->where('deployment_id', $depId)
            ->whereNotIn('status', ['draft_ready','approved','sent','failed','dismissed','filtered_out','rejected','blocked'])
            ->count();
        $waitingCount    = DB::table('transactions')->where('deployment_id', $depId)->where('status', 'draft_ready')->whereNull('human_decision')->count();
        $completedCount  = DB::table('transactions')->where('deployment_id', $depId)->whereIn('status', ['approved','sent'])->whereDate('updated_at', today())->count();

        // Approvals queue
        $approvals = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->where('status', 'draft_ready')
            ->whereNull('human_decision')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Recent activity
        $activity = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        // Current task
        $currentTask = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->whereNotIn('status', ['draft_ready','approved','sent','failed','dismissed','filtered_out','rejected','blocked'])
            ->orderByDesc('updated_at')
            ->first()
            ?? DB::table('transactions')->where('deployment_id', $depId)->orderByDesc('id')->first();

        // Memory
        $clientCount     = DB::table('clients')->where('user_id', $userId)->count();
        $contactCount    = DB::table('contacts')->where('user_id', $userId)->count();
        $assetCount      = rescue(fn() => DB::table('assets')->where('user_id', $userId)->count(), 0, false);
        $ruleCount       = rescue(fn() => DB::table('ava_rules')->where('deployment_id', $depId)->count(), 0, false);
        $templateCount   = rescue(fn() => DB::table('email_templates')->where('user_id', $userId)->where('worker_slug', 'ava')->count(), 0, false);
        $credentialCount = rescue(fn() => DB::table('user_gmail_credentials')->where('user_id', $userId)->where('is_active', true)->count(), 0, false);

        // All deployments for worker switcher sidebar
        $allDeployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active','paused'])
            ->orderBy('created_at')
            ->get();

        // Registry rows keyed by slug for sidebar avatars
        $slugs = $allDeployments->pluck('worker_slug')->unique()->values()->all();
        $registryRows = DB::table('worker_registry')->whereIn('slug', $slugs)->get()->keyBy('slug');

        $registryRow = $registryRows->get('ava');
        $profileImg  = $registryRow?->profile_image ? asset('storage/' . $registryRow->profile_image) : null;
        $coverImg    = $registryRow?->cover_image   ? asset('storage/' . $registryRow->cover_image)   : null;

        $tokenTotal = rescue(fn() => (int) DB::table('usage_events')->where('user_id', $userId)->sum(DB::raw('tokens_input + tokens_output')), 0, false);
        $workStatus = $dep->status === 'active' ? 'Working' : 'Paused';
        $firstName  = explode(' ', trim(auth()->user()->name))[0];

        return view('desk.ava', compact(
            'dep', 'depId', 'incomingCount', 'inProgressCount', 'waitingCount', 'completedCount',
            'approvals', 'activity', 'currentTask', 'clientCount', 'contactCount',
            'assetCount', 'ruleCount', 'templateCount', 'credentialCount',
            'allDeployments', 'registryRows', 'registryRow', 'profileImg', 'coverImg',
            'workStatus', 'firstName', 'tokenTotal'
        ));
    }
}

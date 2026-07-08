<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Platform\Services\WorkerRegistry;

class AssetGroupController extends Controller
{
    // ── Index (groups tab on worker-memory page) ─────────────────────────────

    public function index(int $depId)
    {
        $dep = DB::table('worker_deployments')
            ->where('id', $depId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $userId   = auth()->id();
        $contract = WorkerRegistry::resolve($dep->worker_slug);
        $groupTypes = $contract ? $contract->groupTypes() : [];

        $groups = DB::table('asset_groups as g')
            ->leftJoin('clients as c', 'c.id', '=', 'g.client_id')
            ->where('g.deployment_id', $depId)
            ->where('g.user_id', $userId)
            ->select('g.*', 'c.name as client_name')
            ->orderBy('g.name')
            ->get();

        // Attach items to each group
        $groups = $groups->map(function ($group) use ($userId) {
            $group->items = DB::table('asset_group_items as gi')
                ->join('assets as a', 'a.id', '=', 'gi.asset_id')
                ->where('gi.group_id', $group->id)
                ->whereNull('a.deleted_at')
                ->orderBy('gi.sort_order')
                ->select('a.id', 'a.name', 'a.type', 'a.vendor', 'a.renewal_date', 'a.status', 'gi.sort_order')
                ->get();
            return $group;
        });

        $clients = DB::table('clients')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $assets = DB::table('assets')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('dashboard.asset-groups', compact('dep', 'groups', 'clients', 'assets', 'groupTypes'));
    }

    // ── Store new group ───────────────────────────────────────────────────────

    public function store(int $depId, Request $request)
    {
        DB::table('worker_deployments')
            ->where('id', $depId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate(['name' => 'required|string|max:200']);

        DB::table('asset_groups')->insert([
            'deployment_id' => $depId,
            'user_id'       => auth()->id(),
            'client_id'     => $request->client_id ?: null,
            'name'          => $request->name,
            'type'          => $request->type ?: null,
            'notes'         => $request->notes,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return back()->with('success', 'Group created.');
    }

    // ── Update group ──────────────────────────────────────────────────────────

    public function update(int $depId, int $groupId, Request $request)
    {
        $this->authoriseGroup($depId, $groupId);
        $request->validate(['name' => 'required|string|max:200']);

        DB::table('asset_groups')->where('id', $groupId)->update([
            'name'       => $request->name,
            'client_id'  => $request->client_id ?: null,
            'type'       => $request->type ?: null,
            'notes'      => $request->notes,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Group updated.');
    }

    // ── Destroy group (leaves assets untouched) ───────────────────────────────

    public function destroy(int $depId, int $groupId)
    {
        $this->authoriseGroup($depId, $groupId);

        DB::table('asset_group_items')->where('group_id', $groupId)->delete();
        DB::table('asset_groups')->where('id', $groupId)->delete();

        return back()->with('success', 'Group removed.');
    }

    // ── Add asset to group ────────────────────────────────────────────────────

    public function addItem(int $depId, int $groupId, Request $request)
    {
        $this->authoriseGroup($depId, $groupId);
        $request->validate(['asset_id' => 'required|integer']);

        $assetId = (int) $request->asset_id;

        // Verify asset belongs to this user and isn't deleted
        $asset = DB::table('assets')
            ->where('id', $assetId)
            ->where('user_id', auth()->id())
            ->whereNull('deleted_at')
            ->first();

        abort_unless($asset, 404);

        // Max sort_order + 1
        $maxSort = DB::table('asset_group_items')
            ->where('group_id', $groupId)
            ->max('sort_order') ?? -1;

        DB::table('asset_group_items')->insertOrIgnore([
            'group_id'   => $groupId,
            'asset_id'   => $assetId,
            'sort_order' => $maxSort + 1,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Asset added to group.');
    }

    // ── Remove asset from group ───────────────────────────────────────────────

    public function removeItem(int $depId, int $groupId, int $assetId)
    {
        $this->authoriseGroup($depId, $groupId);

        DB::table('asset_group_items')
            ->where('group_id', $groupId)
            ->where('asset_id', $assetId)
            ->delete();

        return back()->with('success', 'Asset removed from group.');
    }

    // ── Reorder items ─────────────────────────────────────────────────────────

    public function reorder(int $depId, int $groupId, Request $request)
    {
        $this->authoriseGroup($depId, $groupId);
        $request->validate(['order' => 'required|array']);

        foreach ($request->order as $sort => $assetId) {
            DB::table('asset_group_items')
                ->where('group_id', $groupId)
                ->where('asset_id', (int) $assetId)
                ->update(['sort_order' => (int) $sort]);
        }

        return response()->json(['ok' => true]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function authoriseGroup(int $depId, int $groupId): void
    {
        $dep = DB::table('worker_deployments')
            ->where('id', $depId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $group = DB::table('asset_groups')
            ->where('id', $groupId)
            ->where('deployment_id', $depId)
            ->where('user_id', auth()->id())
            ->first();

        abort_unless($group, 403);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Platform\Services\WorkerRegistry;
use App\Platform\SDK\Columns\AssetGroupColumns;

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
                ->select('a.id', 'a.name', 'a.type', 'a.vendor', 'a.renewal_date', 'a.renewal_cadence_days', 'a.status', 'gi.sort_order')
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

        $shell = \App\Platform\Services\WorkerShellService::build($userId, $dep->worker_slug);
        extract($shell); // workerCatalog, registryRows, registryRow, profileImg, coverImg, tokenTotal
        $firstName = explode(' ', trim(auth()->user()->name))[0];

        return view('dashboard.asset-groups', compact(
            'dep', 'groups', 'clients', 'assets', 'groupTypes',
            'workerCatalog', 'tokenTotal', 'firstName'
        ));
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
            'deployment_id'    => $depId,
            'user_id'          => auth()->id(),
            'client_id'        => $request->client_id ?: null,
            'name'             => $request->name,
            'type'             => $request->type ?: null,
            'notes'            => $request->notes,
            AssetGroupColumns::RENEWS_TOGETHER => $request->boolean('renews_together'),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return back()->with('success', 'Group created.');
    }

    // ── Update group ──────────────────────────────────────────────────────────

    public function update(int $depId, int $groupId, Request $request)
    {
        $this->authoriseGroup($depId, $groupId);
        $request->validate(['name' => 'required|string|max:200']);

        DB::table('asset_groups')->where('id', $groupId)->update([
            'name'            => $request->name,
            'client_id'       => $request->client_id ?: null,
            'type'            => $request->type ?: null,
            'notes'           => $request->notes,
            AssetGroupColumns::RENEWS_TOGETHER => $request->boolean('renews_together'),
            'updated_at'      => now(),
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

    // ── Human Trigger — push an asset into the pipeline on demand ────────────
    // The third entry point alongside Gmail Watch and Asset Watch: a tenant
    // (or a client asking directly) wants this started right now instead of
    // waiting for tomorrow's threshold scan. Always available — not gated
    // by AVA Settings, since it's an explicit, one-off human action rather
    // than a background job.
    public function renewNow(int $depId, int $assetId)
    {
        $dep = DB::table('worker_deployments')
            ->where('id', $depId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $asset = DB::table('assets')
            ->where('id', $assetId)
            ->where('user_id', auth()->id())
            ->whereNull('deleted_at')
            ->firstOrFail();

        // Don't create a second in-flight transaction for the same asset —
        // dismissed/rejected transactions don't count, everything else does.
        $inFlight = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->whereNotIn('status', ['dismissed', 'rejected'])
            ->whereRaw("JSON_EXTRACT(raw_input, '$.asset_id') = ?", [$assetId])
            ->where('created_at', '>', now()->subDay())
            ->exists();

        if ($inFlight) {
            return back()->with('error', "{$asset->name} already has a transaction in progress — check Activity Log.");
        }

        $tx = \App\Workers\AVA\Services\AssetTransactionSynthesizer::create($asset, $dep, 'human_trigger');

        return redirect()
            ->route('app.transactions.show', ['slug' => $dep->worker_slug, 'txId' => $tx->tx_id])
            ->with('success', "{$asset->name} pushed into the pipeline.");
    }

    // Group-level Human Trigger — bundles every member with a
    // renewal_date into one transaction right now, same as the per-asset
    // "Renew Now" above but for a whole renews_together group at once.
    // Available regardless of whether renews_together is actually set —
    // it's an explicit human override either way, the flag only controls
    // AssetExpiryWatchJob's automated behavior.
    public function renewGroupNow(int $depId, int $groupId)
    {
        $dep   = DB::table('worker_deployments')->where('id', $depId)->where('user_id', auth()->id())->firstOrFail();
        $group = DB::table('asset_groups')->where('id', $groupId)->where('deployment_id', $depId)->where('user_id', auth()->id())->firstOrFail();

        $assets = DB::table('assets as a')
            ->join('asset_group_items as gi', 'gi.asset_id', '=', 'a.id')
            ->where('gi.group_id', $groupId)
            ->where('a.user_id', auth()->id())
            ->whereNull('a.deleted_at')
            ->select('a.*')
            ->get()
            ->all();

        if (empty($assets)) {
            return back()->with('error', "\"{$group->name}\" has no assets to renew.");
        }

        $assetIds = collect($assets)->pluck('id')->all();
        $inFlight = DB::table('transactions')
            ->where('deployment_id', $depId)
            ->whereNotIn('status', ['dismissed', 'rejected'])
            ->where('created_at', '>', now()->subDay())
            ->get()
            ->contains(fn ($tx) => !empty(array_intersect(json_decode($tx->raw_input, true)['asset_ids'] ?? [], $assetIds)));

        if ($inFlight) {
            return back()->with('error', "\"{$group->name}\" already has a transaction in progress — check Activity Log.");
        }

        $client = $group->client_id ? DB::table('clients')->where('id', $group->client_id)->first() : null;
        $tx = \App\Workers\AVA\Services\AssetTransactionSynthesizer::createForGroup($assets, $client, $dep, 'human_trigger');

        return redirect()
            ->route('app.transactions.show', ['slug' => $dep->worker_slug, 'txId' => $tx->tx_id])
            ->with('success', "\"{$group->name}\" pushed into the pipeline as one bundled transaction.");
    }

    // Asset data integrity, not pipeline behavior — a file-imported asset
    // commonly has no renewal_date and never had renewal_cadence_days set
    // at all (that field has no UI anywhere else in the app; every asset
    // silently defaults to a 365-day cadence in UpdateRenewalDateJob
    // regardless of whether that's true). Fixes every asset in a group in
    // one batch, since that's where a tenant actually notices the gap.
    public function fixDates(int $depId, int $groupId, Request $request)
    {
        $this->authoriseGroup($depId, $groupId);

        $memberIds = DB::table('asset_group_items')->where('group_id', $groupId)->pluck('asset_id')->all();
        $submitted = $request->input('assets', []);

        $updated = 0;
        foreach ($submitted as $assetId => $fields) {
            $assetId = (int) $assetId;
            if (!in_array($assetId, $memberIds, true)) continue;

            DB::table('assets')->where('id', $assetId)->where('user_id', auth()->id())->update([
                'renewal_date'         => $fields['renewal_date'] ?: null,
                'renewal_cadence_days' => $fields['renewal_cadence_days'] ?: null,
                'updated_at'           => now(),
            ]);
            $updated++;
        }

        return back()->with('success', "Updated {$updated} asset date" . ($updated === 1 ? '' : 's') . '.');
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

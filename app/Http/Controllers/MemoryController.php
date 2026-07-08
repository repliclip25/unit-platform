<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Platform\Services\MemoryImportService;

class MemoryController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // ── My Memory ────────────────────────────────────────────────────────
        $clients  = DB::table('clients')->where('user_id', $userId)->whereNull('deleted_at')->orderBy('name')->get();
        $contacts = DB::table('contacts')->where('user_id', $userId)->whereNull('deleted_at')->get();
        $assets   = DB::table('assets')->where('user_id', $userId)->whereNull('deleted_at')->where('type', '!=', 'discovered')->orderBy('renewal_date')->get();
        $rules    = DB::table('ava_rules')->where('user_id', $userId)->orderBy('rule_id')->get();

        // ── Groups across all my deployments ─────────────────────────────────
        $myDeployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('status', '!=', 'decommissioned')
            ->orderBy('name')
            ->get();

        $myGroups = collect();
        if ($myDeployments->isNotEmpty()) {
            $depIds   = $myDeployments->pluck('id')->toArray();
            $groups   = DB::table('asset_groups as g')
                ->leftJoin('clients as c', 'c.id', '=', 'g.client_id')
                ->whereIn('g.deployment_id', $depIds)
                ->where('g.user_id', $userId)
                ->select('g.*', 'c.name as client_name')
                ->orderBy('g.deployment_id')->orderBy('g.name')
                ->get();

            foreach ($groups as $group) {
                $group->items = DB::table('asset_group_items as gi')
                    ->join('assets as a', 'a.id', '=', 'gi.asset_id')
                    ->where('gi.group_id', $group->id)
                    ->whereNull('a.deleted_at')
                    ->orderBy('gi.sort_order')
                    ->select('a.id', 'a.name', 'a.type', 'a.vendor', 'a.renewal_date', 'a.status')
                    ->get();
                $group->deployment_name = $myDeployments->firstWhere('id', $group->deployment_id)?->name;
                $group->worker_slug     = $myDeployments->firstWhere('id', $group->deployment_id)?->worker_slug;
                $myGroups->push($group);
            }
        }

        // ── Shared With Me (incoming grants) ─────────────────────────────────
        $incoming = DB::table('memory_access_grants as g')
            ->join('users as u', 'u.id', '=', 'g.owner_user_id')
            ->join('worker_deployments as d', 'd.id', '=', 'g.deployment_id')
            ->where('g.grantee_user_id', $userId)
            ->where('g.status', 'accepted')
            ->select('g.*', 'u.name as owner_name', 'u.email as owner_email',
                     'd.name as deployment_name', 'd.worker_slug')
            ->orderByDesc('g.accepted_at')
            ->get()
            ->map(function ($g) use ($userId) {
                // Attach memory summary counts for preview
                $g->client_count  = DB::table('clients')->where('user_id', $g->owner_user_id)->whereNull('deleted_at')->count();
                $g->contact_count = DB::table('contacts')->where('user_id', $g->owner_user_id)->whereNull('deleted_at')->count();
                $g->asset_count   = DB::table('assets')->where('user_id', $g->owner_user_id)->whereNull('deleted_at')->where('type', '!=', 'discovered')->count();
                $g->group_count   = DB::table('asset_groups')->where('deployment_id', $g->deployment_id)->where('user_id', $g->owner_user_id)->count();
                return $g;
            });

        // ── Access Management (outgoing grants) ───────────────────────────────
        $outgoing = DB::table('memory_access_grants as g')
            ->join('users as u', 'u.id', '=', 'g.grantee_user_id')
            ->join('worker_deployments as d', 'd.id', '=', 'g.deployment_id')
            ->where('g.owner_user_id', $userId)
            ->whereIn('g.status', ['pending', 'accepted'])
            ->select('g.*', 'u.name as grantee_name', 'u.email as grantee_email',
                     'u.profile_code as grantee_code', 'd.name as deployment_name', 'd.worker_slug')
            ->orderByDesc('g.created_at')
            ->get()
            ->map(function ($g) {
                $g->event_count = DB::table('memory_access_events')->where('grant_id', $g->id)->count();
                $g->last_action = DB::table('memory_access_events')->where('grant_id', $g->id)->orderByDesc('created_at')->value('created_at');
                return $g;
            });

        $myProfileCode = DB::table('users')->where('id', $userId)->value('profile_code');

        return view('dashboard.memory', compact(
            'clients', 'contacts', 'assets', 'rules',
            'myDeployments', 'myGroups',
            'incoming', 'outgoing', 'myProfileCode'
        ));
    }

    public function storeClient(Request $request)
    {
        $request->validate(['name' => 'required']);
        DB::table('clients')->insert(['user_id' => auth()->id(), 'name' => $request->name, 'industry' => $request->industry, 'preferred_style' => $request->preferred_style, 'status' => $request->status ?: 'active', 'address' => $request->address, 'notes' => $request->notes, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Client added.');
    }

    public function destroyClient(int $id)
    {
        DB::table('clients')->where('id', $id)->where('user_id', auth()->id())->update(['deleted_at' => now()]);
        return back()->with('success', 'Client removed.');
    }

    public function storeContact(Request $request)
    {
        $request->validate(['name' => 'required', 'email' => 'required|email']);
        DB::table('contacts')->insert(['user_id' => auth()->id(), 'client_id' => $request->client_id ?: null, 'name' => $request->name, 'email' => $request->email, 'phone' => $request->phone, 'role' => $request->role, 'department' => $request->department, 'is_decision_maker' => $request->boolean('is_decision_maker'), 'is_primary' => $request->boolean('is_primary'), 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Contact added.');
    }

    public function destroyContact(int $id)
    {
        DB::table('contacts')->where('id', $id)->where('user_id', auth()->id())->update(['deleted_at' => now()]);
        return back()->with('success', 'Contact removed.');
    }

    public function storeAsset(Request $request)
    {
        $request->validate(['name' => 'required', 'type' => 'required', 'client_id' => 'required']);
        DB::table('assets')->insert(['user_id' => auth()->id(), 'name' => $request->name, 'type' => $request->type, 'client_id' => $request->client_id ?: null, 'vendor' => $request->vendor, 'renewal_date' => $request->renewal_date, 'cost_per_year' => $request->cost_per_year ?: null, 'status' => $request->status ?: 'active', 'service_owner' => $request->service_owner, 'notes' => $request->notes, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Asset added.');
    }

    public function updateAsset(Request $request, int $id)
    {
        $request->validate(['name' => 'required', 'type' => 'required']);
        DB::table('assets')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->update([
                'name'         => $request->name,
                'type'         => $request->type,
                'vendor'       => $request->vendor,
                'renewal_date' => $request->renewal_date ?: null,
                'cost_per_year'=> $request->cost_per_year ?: null,
                'status'       => $request->status ?: 'active',
                'client_id'    => $request->client_id ?: null,
                'notes'        => $request->notes,
                'updated_at'   => now(),
            ]);
        return back()->with('success', 'Asset updated.');
    }

    public function destroyAsset(int $id)
    {
        DB::table('assets')->where('id', $id)->where('user_id', auth()->id())->update(['deleted_at' => now()]);
        DB::table('asset_group_items')->where('asset_id', $id)->delete();
        return back()->with('success', 'Asset removed.');
    }

    public function storeRule(Request $request)
    {
        $request->validate(['condition' => 'required', 'action' => 'required', 'priority' => 'required']);
        if ($request->rule_id) {
            $ruleId = $request->rule_id;
        } else {
            $last   = DB::table('ava_rules')->where('user_id', auth()->id())->orderByDesc('id')->value('rule_id');
            $ruleId = $last ? 'AVA-' . str_pad(intval(substr($last, 4)) + 1, 3, '0', STR_PAD_LEFT) : 'AVA-101';
        }
        DB::table('ava_rules')->insert(['user_id' => auth()->id(), 'rule_id' => $ruleId, 'condition' => $request->condition, 'priority' => $request->priority, 'action' => $request->action, 'approval_required' => $request->boolean('approval_required'), 'notes' => $request->notes, 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Rule added.');
    }

    public function destroyRule(int $id)
    {
        DB::table('ava_rules')->where('id', $id)->where('user_id', auth()->id())->delete();
        return back()->with('success', 'Rule removed.');
    }

    public function importTemplate(string $type)
    {
        abort_unless(in_array($type, ['clients', 'contacts', 'assets']), 404);

        $headers = [
            'clients'  => ['name', 'industry', 'preferred_style', 'status', 'address', 'notes'],
            'contacts' => ['name', 'email', 'phone', 'role', 'department', 'is_decision_maker', 'client_name', 'notes'],
            'assets'   => ['name', 'type', 'vendor', 'renewal_date', 'cost_per_year', 'status', 'client_name', 'notes'],
        ];

        $examples = [
            'clients'  => [['Acme Corp', 'Technology', 'Professional', 'active', '123 Main St, New York NY', 'Key account']],
            'contacts' => [['Jane Smith', 'jane@acmecorp.com', '555-1234', 'IT Manager', 'IT', '1', 'Acme Corp', 'Primary contact']],
            'assets'   => [['acmecorp.com', 'Domain', 'Namecheap', '2026-12-01', '15.99', 'active', 'Acme Corp', '']],
        ];

        $csv  = implode(',', $headers[$type]) . "\r\n";
        foreach ($examples[$type] as $row) {
            $csv .= implode(',', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\r\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$type}_import_template.csv\"",
        ]);
    }

    public function importPreview(Request $request, MemoryImportService $importer)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
            'type' => 'required|in:clients,contacts,assets',
        ]);

        $file    = $request->file('file');
        $type    = $request->input('type');
        $data    = $importer->readFile($file);
        $mapping = $importer->suggestMapping($data['headers'], $type);

        // Store file temporarily for the commit step
        $tmpPath = $file->store('imports', 'local');

        session([
            'import_tmp'     => $tmpPath,
            'import_headers' => $data['headers'],
            'import_rows'    => $data['rows'],
            'import_type'    => $type,
            'import_mapping' => $mapping,
        ]);

        return view('dashboard.memory-import-preview', [
            'headers' => $data['headers'],
            'rows'    => array_slice($data['rows'], 0, 5),
            'mapping' => $mapping,
            'type'    => $type,
            'total'   => count($data['rows']),
        ]);
    }

    public function importCommit(Request $request, MemoryImportService $importer)
    {
        $headers = session('import_headers');
        $rows    = session('import_rows');
        $type    = session('import_type');

        if (!$headers || !$rows) {
            return redirect()->route('memory')->with('error', 'Import session expired. Please re-upload.');
        }

        // Use user-adjusted mapping from form, or fall back to auto-mapping
        $mapping = [];
        foreach ($headers as $i => $header) {
            $mapping[$i] = $request->input("mapping.{$i}") ?: null;
        }

        $result = $importer->import($headers, $rows, $mapping, $type, auth()->id());

        // Clean up
        if (session('import_tmp')) {
            Storage::disk('local')->delete(session('import_tmp'));
        }
        session()->forget(['import_tmp', 'import_headers', 'import_rows', 'import_type', 'import_mapping']);

        return redirect()->route('memory')->with('success',
            "Import complete: {$result['inserted']} {$type} imported, {$result['skipped']} skipped."
        );
    }
}

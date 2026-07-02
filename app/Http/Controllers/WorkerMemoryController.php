<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Platform\Services\MemoryImportService;

class WorkerMemoryController extends Controller
{
    public function index(string $slug, Request $request, MemoryImportService $importer)
    {
        $dep = DB::table('worker_deployments')->where('user_id', auth()->id())
            ->where(fn($q) => $q->where('worker_slug', $slug)->when(is_numeric($slug), fn($q2) => $q2->orWhere('id', (int)$slug)))
            ->firstOrFail();
        $id       = $dep->id;
        $userId   = auth()->id();
        $clients  = DB::table('clients')->where('user_id', $userId)->whereNull('deleted_at')->orderBy('name')->get();
        $contacts = DB::table('contacts')->where('user_id', $userId)->whereNull('deleted_at')->get();
        $assets         = DB::table('assets')->where('user_id', $userId)->whereNull('deleted_at')->where('type', '!=', 'discovered')->orderBy('renewal_date')->get();
        $discoveredAssets = DB::table('assets')->where('user_id', $userId)->whereNull('deleted_at')->where('type', 'discovered')->orderByDesc('created_at')->get();
        return view('dashboard.worker-memory', compact('dep', 'clients', 'contacts', 'assets', 'discoveredAssets'));
    }

    public function importPreview(int $id, Request $request, MemoryImportService $importer)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls|max:5120', 'type' => 'required|in:clients,contacts,assets']);
        $data    = $importer->readFile($request->file('file'));
        $mapping = $importer->suggestMapping($data['headers'], $request->type);
        $tmpPath = $request->file('file')->store('imports', 'local');
        session(['import_tmp' => $tmpPath, 'import_headers' => $data['headers'], 'import_rows' => $data['rows'], 'import_type' => $request->type, 'import_dep_id' => $id]);
        return view('dashboard.memory-import-preview', [
            'headers' => $data['headers'], 'rows' => array_slice($data['rows'], 0, 5),
            'mapping' => $mapping, 'type' => $request->type, 'total' => count($data['rows']),
            'dep_id'  => $id,
        ]);
    }

    public function importCommit(int $id, Request $request, MemoryImportService $importer)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $headers = session('import_headers');
        $rows    = session('import_rows');
        $type    = session('import_type');
        if (!$headers || !$rows) return redirect()->route('workers.memory', $id)->with('error', 'Import session expired.');
        $mapping = [];
        foreach ($headers as $i => $h) { $mapping[$i] = $request->input("mapping.{$i}") ?: null; }
        $result = $importer->import($headers, $rows, $mapping, $type, auth()->id());
        if (session('import_tmp')) Storage::disk('local')->delete(session('import_tmp'));
        session()->forget(['import_tmp','import_headers','import_rows','import_type','import_dep_id']);
        return redirect()->route('workers.memory', $id)->with('success', "Import complete: {$result['inserted']} {$type} imported, {$result['skipped']} skipped.");
    }

    public function storeClient(int $id, Request $request)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required']);
        DB::table('clients')->insert(['user_id' => auth()->id(), 'name' => $request->name, 'industry' => $request->industry, 'preferred_style' => $request->preferred_style, 'notes' => $request->notes, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Client added.');
    }

    public function updateClient(int $id, int $cid, Request $request)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required']);
        DB::table('clients')->where('id', $cid)->where('user_id', auth()->id())->update([
            'name'            => $request->name,
            'industry'        => $request->industry,
            'preferred_style' => $request->preferred_style,
            'notes'           => $request->notes,
            'updated_at'      => now(),
        ]);
        return back()->with('success', 'Client updated.');
    }

    public function destroyClient(int $id, int $cid)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DB::table('clients')->where('id', $cid)->where('user_id', auth()->id())->update(['deleted_at' => now()]);
        return back()->with('success', 'Client removed.');
    }

    public function storeContact(int $id, Request $request)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required', 'email' => 'required|email']);
        DB::table('contacts')->insert(['user_id' => auth()->id(), 'client_id' => $request->client_id ?: null, 'name' => $request->name, 'email' => $request->email, 'phone' => $request->phone, 'role' => $request->role, 'is_primary' => false, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Contact added.');
    }

    public function updateContact(int $id, int $cid, Request $request)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required', 'email' => 'required|email']);
        DB::table('contacts')->where('id', $cid)->where('user_id', auth()->id())->update([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'role'       => $request->role,
            'client_id'  => $request->client_id ?: null,
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Contact updated.');
    }

    public function destroyContact(int $id, int $cid)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DB::table('contacts')->where('id', $cid)->where('user_id', auth()->id())->update(['deleted_at' => now()]);
        return back()->with('success', 'Contact removed.');
    }

    public function storeAsset(int $id, Request $request)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required', 'type' => 'required']);
        DB::table('assets')->insert(['user_id' => auth()->id(), 'client_id' => $request->client_id ?: null, 'name' => $request->name, 'type' => $request->type, 'vendor' => $request->vendor, 'renewal_date' => $request->renewal_date, 'cost_per_year' => $request->cost_per_year ?: null, 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('success', 'Asset added.');
    }

    public function updateAsset(int $id, int $aid, Request $request)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required', 'type' => 'required']);
        DB::table('assets')->where('id', $aid)->where('user_id', auth()->id())->update([
            'name'          => $request->name,
            'type'          => $request->type,
            'vendor'        => $request->vendor,
            'renewal_date'  => $request->renewal_date ?: null,
            'cost_per_year' => $request->cost_per_year ?: null,
            'client_id'     => $request->client_id ?: null,
            'updated_at'    => now(),
        ]);
        return back()->with('success', 'Asset updated.');
    }

    public function approveAsset(int $id, int $aid, Request $request)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['name' => 'required', 'type' => 'required']);
        DB::table('assets')->where('id', $aid)->where('user_id', auth()->id())->update([
            'name'          => $request->name,
            'type'          => $request->type,
            'vendor'        => $request->vendor,
            'renewal_date'  => $request->renewal_date ?: null,
            'cost_per_year' => $request->cost_per_year ?: null,
            'client_id'     => $request->client_id ?: null,
            'updated_at'    => now(),
        ]);
        return back()->with('success', 'Asset confirmed and added to memory.');
    }

    public function destroyAsset(int $id, int $aid)
    {
        DB::table('worker_deployments')->where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        DB::table('assets')->where('id', $aid)->where('user_id', auth()->id())->update(['deleted_at' => now()]);
        return back()->with('success', 'Asset removed.');
    }
}

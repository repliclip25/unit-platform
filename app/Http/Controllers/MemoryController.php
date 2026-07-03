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
        $userId   = auth()->id();
        $clients  = DB::table('clients')->where('user_id', $userId)->whereNull('deleted_at')->orderBy('name')->get();
        $contacts = DB::table('contacts')->where('user_id', $userId)->whereNull('deleted_at')->get();
        $assets   = DB::table('assets')->where('user_id', $userId)->whereNull('deleted_at')->orderBy('renewal_date')->get();
        $rules    = DB::table('ava_rules')->where('user_id', $userId)->orderBy('rule_id')->get();
        return view('dashboard.memory', compact('clients', 'contacts', 'assets', 'rules'));
    }

    public function storeClient(Request $request)
    {
        $request->validate(['name' => 'required']);
        DB::table('clients')->insert(['user_id' => auth()->id(), 'name' => $request->name, 'industry' => $request->industry, 'preferred_style' => $request->preferred_style, 'notes' => $request->notes, 'created_at' => now(), 'updated_at' => now()]);
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
        DB::table('contacts')->insert(['user_id' => auth()->id(), 'client_id' => $request->client_id ?: null, 'name' => $request->name, 'email' => $request->email, 'phone' => $request->phone, 'role' => $request->role, 'is_primary' => $request->boolean('is_primary'), 'created_at' => now(), 'updated_at' => now()]);
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
        DB::table('assets')->insert(['user_id' => auth()->id(), 'name' => $request->name, 'type' => $request->type, 'client_id' => $request->client_id ?: null, 'vendor' => $request->vendor, 'renewal_date' => $request->renewal_date, 'cost_per_year' => $request->cost_per_year ?: null, 'service_owner' => $request->service_owner, 'notes' => $request->notes, 'created_at' => now(), 'updated_at' => now()]);
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
                'client_id'    => $request->client_id ?: null,
                'notes'        => $request->notes,
                'updated_at'   => now(),
            ]);
        return back()->with('success', 'Asset updated.');
    }

    public function destroyAsset(int $id)
    {
        DB::table('assets')->where('id', $id)->where('user_id', auth()->id())->update(['deleted_at' => now()]);
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
        $path = storage_path("app/templates/{$type}_template.csv");
        return response()->download($path, "{$type}_import_template.csv", [
            'Content-Type' => 'text/csv',
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

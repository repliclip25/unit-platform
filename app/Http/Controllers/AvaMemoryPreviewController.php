<?php

namespace App\Http\Controllers;

use App\Platform\Services\Memory\AvaMemoryCoverageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AvaMemoryPreviewController extends Controller
{
    /**
     * A deliberately minimal entry point into AVA's real memory tables
     * (clients/contacts — platform-scoped by user_id, not deployment-scoped,
     * per WORKER.md) for tenants who haven't deployed AVA yet. The full
     * /workers/ava/memory page (MemoryController) requires an active
     * deployment and is persona/asset-group aware — this page intentionally
     * isn't, so it works with zero commitment. Whatever gets entered here
     * shows up automatically in the real memory page once AVA is deployed,
     * since it's the same underlying tables.
     */
    public function show(): View
    {
        $userId  = auth()->id();
        $clients = DB::table('clients')->where('user_id', $userId)->whereNull('deleted_at')->orderBy('name')->get();

        $clientIds = $clients->pluck('id');
        $contacts  = DB::table('contacts')->whereIn('client_id', $clientIds)->whereNull('deleted_at')->orderBy('name')->get()->groupBy('client_id');
        $assets    = DB::table('assets')->whereIn('client_id', $clientIds)->whereNull('deleted_at')->orderBy('name')->get()->groupBy('client_id');

        $coverage = AvaMemoryCoverageService::score($userId);

        return view('hire.ava-memory', compact('clients', 'contacts', 'assets', 'coverage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $exists = DB::table('clients')->where('user_id', auth()->id())->where('name', $data['name'])->exists();
        if ($exists) {
            return redirect()->route('hire.ava.memory')->with('error', "You already have a client named \"{$data['name']}\".");
        }

        DB::table('clients')->insert([
            'user_id'    => auth()->id(),
            'name'       => $data['name'],
            'notes'      => $data['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('hire.ava.memory')->with('success', "Added {$data['name']}.");
    }

    public function destroy(int $id): RedirectResponse
    {
        DB::table('clients')->where('id', $id)->where('user_id', auth()->id())->delete();

        return redirect()->route('hire.ava.memory')->with('success', 'Removed.');
    }

    public function storeContact(Request $request, int $clientId): RedirectResponse
    {
        $client = DB::table('clients')->where('id', $clientId)->where('user_id', auth()->id())->first();
        abort_unless($client, 404);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        DB::table('contacts')->insert([
            'user_id'    => auth()->id(),
            'client_id'  => $clientId,
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('hire.ava.memory')->with('success', "Added contact {$data['name']} to {$client->name}.");
    }

    public function destroyContact(int $id): RedirectResponse
    {
        DB::table('contacts')->where('id', $id)->where('user_id', auth()->id())->delete();

        return redirect()->route('hire.ava.memory')->with('success', 'Removed.');
    }

    public function storeAsset(Request $request, int $clientId): RedirectResponse
    {
        $client = DB::table('clients')->where('id', $clientId)->where('user_id', auth()->id())->first();
        abort_unless($client, 404);

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'type'         => ['required', 'string', 'max:100'],
            'renewal_date' => ['nullable', 'date'],
        ]);

        DB::table('assets')->insert([
            'user_id'      => auth()->id(),
            'client_id'    => $clientId,
            'name'         => $data['name'],
            'type'         => $data['type'],
            'renewal_date' => $data['renewal_date'] ?: null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('hire.ava.memory')->with('success', "Added asset {$data['name']} to {$client->name}.");
    }

    public function destroyAsset(int $id): RedirectResponse
    {
        DB::table('assets')->where('id', $id)->where('user_id', auth()->id())->delete();

        return redirect()->route('hire.ava.memory')->with('success', 'Removed.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWorkerRequestController extends Controller
{
    public function index()
    {
        $requests = DB::table('worker_requests')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.worker-requests.index', compact('requests'));
    }

    public function show(int $id)
    {
        $req = DB::table('worker_requests')->where('id', $id)->firstOrFail();
        return view('admin.worker-requests.show', compact('req'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate(['status' => 'required|in:pending,contacted,scoping,declined,building,done']);
        DB::table('worker_requests')->where('id', $id)->update([
            'status'     => $request->status,
            'updated_at' => now(),
        ]);
        return back()->with('saved', true);
    }

    public function destroy(int $id)
    {
        DB::table('worker_requests')->where('id', $id)->delete();
        return redirect()->route('admin.worker-requests')->with('deleted', true);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReviewController extends Controller
{
    public function index()
    {
        $reviews = DB::table('worker_reviews')->orderByDesc('created_at')->get();
        $workers = DB::table('worker_registry')->select('slug', 'name')->orderBy('name')->get();
        return view('admin.reviews.index', compact('reviews', 'workers'));
    }

    public function create()
    {
        $workers = DB::table('worker_registry')->select('slug', 'name')->orderBy('name')->get();
        return view('admin.reviews.form', compact('workers'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_at'] = now();
        $data['updated_at'] = now();
        if ($data['status'] === 'approved') {
            $data['approved_at'] = now();
        }

        DB::table('worker_reviews')->insert($data);

        return redirect()->route('admin.reviews')->with('saved', 'Review added.');
    }

    public function approve(int $id)
    {
        DB::table('worker_reviews')->where('id', $id)->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'updated_at'  => now(),
        ]);
        return back()->with('saved', 'Review approved and now live on the public page.');
    }

    public function reject(int $id)
    {
        DB::table('worker_reviews')->where('id', $id)->update([
            'status'      => 'rejected',
            'approved_at' => null,
            'updated_at'  => now(),
        ]);
        return back()->with('saved', 'Review rejected.');
    }

    public function destroy(int $id)
    {
        DB::table('worker_reviews')->where('id', $id)->delete();
        return redirect()->route('admin.reviews')->with('saved', 'Review deleted.');
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'worker_slug'     => 'required|string|max:60',
            'author_name'     => 'required|string|max:120',
            'author_company'  => 'nullable|string|max:120',
            'rating'          => 'required|integer|min:1|max:5',
            'quote'           => 'required|string|max:1000',
            'status'          => 'required|in:pending,approved,rejected',
        ]);

        return [
            'worker_slug'    => $request->worker_slug,
            'author_name'    => $request->author_name,
            'author_company' => $request->author_company ?: null,
            'rating'         => (int) $request->rating,
            'quote'          => $request->quote,
            'status'         => $request->status,
        ];
    }
}

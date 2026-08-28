<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

// Generic renderer for a worker's /{worker}/{path} search-market pages (e.g.
// /ava/renewal-management/, /ava/assets/domains/). One route, one controller,
// one shared view for every worker — see worker_content_pages migration.
class WorkerContentController extends Controller
{
    public function show(string $worker, string $path = '')
    {
        $page = DB::table('worker_content_pages')
            ->where('worker_slug', $worker)
            ->where('url_path', trim($path, '/'))
            ->where('status', 'published')
            ->first();

        if (!$page) {
            abort(404);
        }

        $workerRow = DB::table('worker_registry')->where('slug', $worker)->first();

        $faqs = DB::table('worker_content_faqs')
            ->where('page_id', $page->id)
            ->orderBy('sort_order')
            ->get();

        $secondaryQueries = json_decode($page->secondary_queries ?? '[]', true) ?: [];

        return view('workers.content.show', [
            'page'             => $page,
            'worker'           => $workerRow,
            'workerSlug'       => $worker,
            'faqs'             => $faqs,
            'secondaryQueries' => $secondaryQueries,
        ]);
    }
}

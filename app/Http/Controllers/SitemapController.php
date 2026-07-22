<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    // Slugs with a real public profile page — see WorkerPublicController::show(),
    // which only defines a $workers entry for 'ava' and 404s on anything else.
    private const PUBLIC_WORKER_SLUGS = ['ava'];

    public function index()
    {
        $urls = [];

        $urls[] = ['loc' => route('home'), 'priority' => '1.0'];
        $urls[] = ['loc' => route('public.workers.index'), 'priority' => '0.9'];
        $urls[] = ['loc' => route('about'), 'priority' => '0.5'];
        $urls[] = ['loc' => route('pricing'), 'priority' => '0.6'];
        $urls[] = ['loc' => route('blog'), 'priority' => '0.6'];
        $urls[] = ['loc' => route('terms'), 'priority' => '0.2'];
        $urls[] = ['loc' => route('privacy'), 'priority' => '0.2'];
        $urls[] = ['loc' => route('influencer.apply'), 'priority' => '0.3'];

        foreach (self::PUBLIC_WORKER_SLUGS as $slug) {
            $urls[] = ['loc' => route('public.workers.show', $slug), 'priority' => '0.8'];
        }

        $posts = DB::table('blog_posts')->where('status', 'published')->get(['slug', 'updated_at']);
        foreach ($posts as $post) {
            $urls[] = [
                'loc'     => route('blog.show', $post->slug),
                'lastmod' => optional($post->updated_at)
                    ? \Illuminate\Support\Carbon::parse($post->updated_at)->toAtomString()
                    : null,
                'priority' => '0.5',
            ];
        }
        // Hardcoded fallback post (not in the DB) — see PublicPageController::blogPostData()
        $urls[] = ['loc' => route('blog.show', 'how-ava-processes-nycsca-renewal'), 'priority' => '0.5'];

        return response()
            ->view('sitemap.index', ['urls' => $urls])
            ->header('Content-Type', 'text/xml');
    }
}

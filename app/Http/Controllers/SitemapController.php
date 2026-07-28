<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    // Slugs with a real public profile page — see WorkerPublicController::show(),
    // which only defines a $workers entry for 'ava' and 404s on anything else.
    private const PUBLIC_WORKER_SLUGS = ['ava'];

    // Worker detail for llms.txt, keyed the same as PUBLIC_WORKER_SLUGS so a
    // worker can't show up in one list without the other.
    private const WORKER_DETAILS = [
        'ava' => [
            'name'          => 'AVA',
            'role'          => 'AI Renewal Coordinator',
            'status'        => 'Live',
            'description'   => "AVA helps businesses manage renewal workflows from a shared inbox.\n\nAVA monitors incoming renewal emails, identifies actionable requests, gathers the information needed to process them, and prepares responses for human review before sending.",
            'audienceIntro' => 'AVA is being tested and refined today with organizations such as:',
            'audienceList'  => ['IT & Digital Agencies', 'Insurance Brokers', 'Compliance & Licensing Firms'],
            'audienceOutro' => 'Additional industries and renewal workflows will be supported as the platform evolves.',
        ],
    ];

    // Workers that exist in the product but don't have a public page yet —
    // listed honestly as not-yet-available rather than omitted or oversold.
    private const UPCOMING_WORKERS = [
        'DOX' => 'Document Specialist',
        'MOX' => 'Brand Moments Hunter',
        'NUX' => 'Publishing Specialist',
    ];

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

    public function llmsTxt()
    {
        $workers = [];
        foreach (self::PUBLIC_WORKER_SLUGS as $slug) {
            $detail = self::WORKER_DETAILS[$slug] ?? null;
            if (!$detail) continue;
            $workers[] = $detail + ['url' => route('public.workers.show', $slug)];
        }

        return response()
            ->view('llms.index', [
                'workers'         => $workers,
                'upcomingWorkers' => self::UPCOMING_WORKERS,
            ])
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}

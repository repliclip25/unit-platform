<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    // Slugs with a real public profile page , see WorkerPublicController::show(),
    // which only defines a $workers entry for 'ava' and 404s on anything else.
    private const PUBLIC_WORKER_SLUGS = ['ava'];

    // Worker detail for llms.txt, keyed the same as PUBLIC_WORKER_SLUGS so a
    // worker can't show up in one list without the other.
    private const WORKER_DETAILS = [
        'ava' => [
            'name'          => 'AVA',
            'role'          => 'AI Renewal Agent',
            'status'        => 'Live',
            'description'   => "AVA is UNITELO AI's Renewal Agent. She watches two places at once: the connected Gmail inbox for incoming renewal emails, and the asset registry directly for expiration dates, so a renewal still gets caught even if the notice email never arrives.\n\nShe reads and classifies each renewal, checks her memory (the tenant's own client, contact, and renewal history, never shared across tenants) and drafts a response using the tenant's own templates and tone. The draft goes into Gmail Drafts, not Sent, and nothing reaches a client without explicit human approval.\n\nIf a draft sits unapproved, she reminds the tenant on an escalating cadence (gentle, then direct, then urgent) and pauses after a few unanswered attempts rather than nagging indefinitely. Once a renewal closes, she archives a complete record, every draft, every reminder, every approval, every payment confirmation, as a downloadable PDF with a QR code, then resets to watch for the next cycle.",
            'audienceIntro' => "AVA started as a fix for a real problem: tracking domain, hosting, and vendor renewals for an IT & digital agency. That's still the primary use case today. The platform is also being tested and refined with:",
            'audienceList'  => ['Insurance Brokers', 'Compliance & Licensing Firms'],
            'audienceOutro' => 'Additional industries and renewal workflows will be supported as the platform evolves.',
        ],
    ];

    // Workers that exist in the product but don't have a public page yet ,
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
        $urls[] = ['loc' => route('agencies'), 'priority' => '0.75'];
        $urls[] = ['loc' => route('insurance'), 'priority' => '0.7'];
        $urls[] = ['loc' => route('compliance'), 'priority' => '0.7'];
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
        // Hardcoded fallback post (not in the DB) , see PublicPageController::blogPostData()
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

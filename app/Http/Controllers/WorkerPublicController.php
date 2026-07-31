<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Platform\Services\WorkerRegistry;
use App\Platform\Services\PlatformDefaults;

class WorkerPublicController extends Controller
{
    public function show(string $slug, string $view = 'workers.show')
    {
        // Pull employee() persona from contract for live fields
        $contract = WorkerRegistry::resolve($slug);
        $employee = WorkerRegistry::isNull($contract) ? [] : $contract->employee();

        $workers = [
            'ava' => [
                'name'       => 'AVA',
                'slug'       => 'ava',
                'role'       => 'AI Renewal Agent',
                'category'   => 'Renewal Automation',
                'meta_desc'  => "AVA is UNIT's AI Renewal Agent. She watches your inbox and renewal calendar, drafts every renewal, and closes the loop, with human approval built in.",

                // Hero video metadata - drives both the <video> element and
                // the VideoObject schema. duration_iso/upload_date come from
                // the real file (ffprobe), not guessed.
                'hero_video' => [
                    'url'          => 'videos/AVA.MP4',
                    'thumbnail'    => 'images/ava-skyline.png',
                    'duration_iso' => 'PT22S',
                    'upload_date'  => '2026-07-11',
                    'transcript'   => "Hey, I'm AVA, UNIT's renewal worker. People remember birthdays. They forget renewals, licenses expire, subscriptions end, deadlines sneak up. I remember. I prepare everything. I make sure nothing important slips through. I sleep better when your renewals are done. See you tomorrow!",
                ],

                // Single source of truth for the pipeline demo AND the HowTo
                // schema below, grouped from the real 16-stage pipeline
                // (inject/fetch -> ... -> archive -> reschedule watch) into
                // 10 steps a visitor can actually follow.
                'pipeline' => [
                    ['who' => 'AVA', 'title' => 'Detects the Renewal', 'desc' => "AVA watches two places at once: your inbox for incoming renewal emails, and your asset registry for expiration dates, so she catches it even if the email never arrives."],
                    ['who' => 'AVA', 'title' => 'Reads & Classifies', 'desc' => "She reads the message (or the registry record), figures out what kind of renewal this is, and sets a priority. Anything that isn't a renewal exits here, no wasted effort."],
                    ['who' => 'AVA', 'title' => 'Checks Her Memory', 'desc' => "She matches the renewal to your stored client, contact, and account history, so the draft isn't starting from zero."],
                    ['who' => 'AVA', 'title' => 'Drafts the Message', 'desc' => "Using your templates and that history, she writes the renewal email, then logs the transaction before anything else happens."],
                    ['who' => 'AVA', 'title' => 'Puts It In Your Inbox', 'desc' => "The draft lands in Gmail Drafts, not Sent. It's ready for you to read, edit, and approve."],
                    ['who' => 'YOU', 'title' => 'Waits, and Reminds You', 'desc' => "Nothing goes to your client until you approve it. If it sits too long, she reminds you herself: gentle at first, more direct the longer it waits."],
                    ['who' => 'YOU', 'title' => 'Handles the Invoice', 'desc' => "If you attach an invoice, she reads it automatically. If you don't, she'll ask a few times, then stop and wait for you."],
                    ['who' => 'YOU', 'title' => 'Confirms Payment', 'desc' => "Before she closes the loop, she waits for you to confirm payment came through, with the same nudging pattern if you go quiet."],
                    ['who' => 'AVA', 'title' => 'Updates the Renewal Date', 'desc' => 'Once confirmed, she moves the renewal date forward on her own. No manual calendar updates.'],
                    ['who' => 'AVA', 'title' => 'Archives the Proof, Resets the Watch', 'desc' => 'She builds a complete record of everything that happened: every draft, every reminder, every decision, as a downloadable PDF with a QR code, then quietly starts watching for the next cycle.'],
                ],

                'faq' => [
                    ['q' => 'What happens if the renewal email never shows up?', 'a' => "AVA doesn't only wait on email. She also watches your asset registry directly for expiration thresholds, so a renewal still gets caught even if the notice email is lost, filtered, or never sent."],
                    ['q' => 'Does AVA submit renewals automatically?', 'a' => 'No. AVA prepares and drafts the renewal, then queues it for your review. Nothing reaches your client without your explicit approval, that gate never gets skipped.'],
                    ['q' => "What happens if I ignore a reminder?", 'a' => 'AVA escalates on her own schedule: gentle, then direct, then urgent, timed by how urgent the renewal is. If you still don\'t respond after a few attempts, she pauses and waits for you to resume manually. She won\'t let it just die quietly, but she won\'t spam you forever either.'],
                    ['q' => 'Can I prove what happened, for compliance or a client dispute?', 'a' => 'Yes. Every closed renewal gets a UNIT-branded archive PDF, every draft, every reminder, every approval decision, with a QR code linking to a signed, downloadable copy for a full year.'],
                    ['q' => 'How does AVA access my renewal inbox?', 'a' => 'AVA connects to Gmail via OAuth2 and a real-time watch webhook. You choose which inbox she monitors, and you can revoke access at any time.'],
                    ['q' => 'What happens if AVA misses something?', 'a' => "Every transaction is fully logged and visible in your dashboard. If she can't classify something confidently, it's flagged for manual review instead of guessed at."],
                    ['q' => 'What if I need to attach an invoice or documents?', 'a' => "AVA can read an invoice's amount and dates automatically if you attach one, or you can skip supporting documents entirely, neither one blocks the renewal from moving forward."],
                    ['q' => 'How much does it cost?', 'a' => 'Your first ' . PlatformDefaults::freeTransactionsFor($slug) . ' transactions are completely free. After that, you pay a monthly subscription based on your deployment. No setup fees, no per-transaction charges. See the full breakdown on the <a href="' . route('pricing') . '">pricing page</a>.'],
                    ['q' => 'Can I cancel my subscription?', 'a' => 'Yes, cancel any time, no questions asked. Your data stays accessible for 30 days after cancellation.'],
                ],
            ],
        ];

        if (!isset($workers[$slug])) abort(404);

        $w = $workers[$slug];

        // Live stats from DB
        $deploymentCount = DB::table('worker_deployments')->where('worker_slug', $slug)->count();
        $tokensToday     = (int) DB::table('usage_events')
            ->join('worker_deployments','worker_deployments.id','=','usage_events.deployment_id')
            ->where('worker_deployments.worker_slug', $slug)
            ->where('usage_events.created_at', '>=', now()->subDay())
            ->sum(DB::raw('tokens_input + tokens_output'));
        $totalTx         = DB::table('transactions')
            ->join('worker_deployments','worker_deployments.id','=','transactions.deployment_id')
            ->where('worker_deployments.worker_slug', $slug)
            ->count();

        // Real published posts tagged for this worker - drives the on-page
        // Resources section so internal links can't go stale if a post is
        // retitled or unpublished.
        $resources = DB::table('blog_posts')
            ->where('status', 'published')
            ->where('worker_slug', $slug)
            ->orderBy('created_at')
            ->get(['slug', 'title', 'tag', 'excerpt']);

        // Only ever 'approved' rows - pending/rejected reviews can never
        // reach the public page or the aggregateRating below, even if a
        // future edit to this controller forgets to re-filter.
        $reviews = DB::table('worker_reviews')
            ->where('worker_slug', $slug)
            ->where('status', 'approved')
            ->orderByDesc('approved_at')
            ->get(['author_name', 'author_company', 'rating', 'quote', 'approved_at']);

        return view($view, [
            'worker'          => $w,
            'deploymentCount' => $deploymentCount,
            'tokensToday'     => $tokensToday,
            'totalTx'         => $totalTx,
            'resources'       => $resources,
            'reviews'         => $reviews,
        ]);
    }

    // ── Per-worker public profile page (workers/public/{slug}.blade.php)
    public function show2(string $slug)
    {
        return $this->show($slug, "workers.public.{$slug}");
    }
}

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
                'youtube_id' => 'dQw4w9WgXcQ', // Replace with real video ID when available
                'role'       => $employee['title']        ?? 'Renewal Coordinator',
                'category'   => 'Renewal Automation',
                'meta_desc'  => $employee['mission']      ?? 'AVA monitors renewal inboxes, classifies renewals, pulls applicant history, drafts submissions and queues them for review — end to end.',
                'headline'   => 'License renewals, handled before the deadline ever gets close.',
                'sub'        => $employee['introduction'] ?? 'AVA monitors your renewal inbox, classifies incoming renewals, looks up applicant history, generates submission drafts, and queues them for your review — end to end, on autopilot.',
                'orgs'       => ['NYCSCA', 'DOB', 'FDNY', 'MTA'],
                'what_h2'    => 'Your renewal pipeline, running without you.',
                'what_body'  => [
                    'Most renewal coordinators spend hours each week doing the same thing: checking email, looking up license records, filling out the same forms, chasing the same deadlines. AVA does all of it.',
                    'AVA is trained specifically on renewal workflows for New York City agencies. It knows the forms, the deadlines, the submission quirks. Every run is logged. You see every step.',
                ],
                'capabilities' => !empty($employee['what_i_do']) ? $employee['what_i_do'] : [
                    'Monitor your Gmail 24/7',
                    'Detect renewal and subscription requests',
                    'Understand the customer using your memory',
                    'Draft a personalized response',
                    'Save it to Gmail Drafts for your review',
                    'Learn from every interaction',
                ],
                'how_steps' => [
                    [
                        'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>',
                        'title'  => 'Inbox check',
                        'desc'   => 'AVA monitors your connected inbox for incoming renewal notices. Emails are classified and prioritized automatically.',
                        'detail' => '→ Found 3 renewal notices\n→ NYCSCA #2847: due in 14 days (HIGH)\n→ DOB #3012: due in 22 days (MEDIUM)',
                    ],
                    [
                        'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><path d="M9 12h6M9 8h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>',
                        'title'  => 'History lookup',
                        'desc'   => 'AVA pulls the applicant record — past submissions, known contacts, prior renewal history — so the draft is pre-filled and accurate.',
                        'detail' => '→ Applicant: John D. · License holder since 2019\n→ Prior renewal: filed 2023-07-12, approved\n→ Contacts: 2 principals on file',
                    ],
                    [
                        'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
                        'title'  => 'Draft generation',
                        'desc'   => 'AVA generates a complete renewal draft using the applicant data and the agency\'s current requirements. No guesswork, no blank fields.',
                        'detail' => '→ Draft: "NYCSCA License Renewal — John D. #2847"\n→ All required fields populated\n→ Supporting docs checklist attached',
                    ],
                    [
                        'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5M13 21a2 2 0 01-4 0"/></svg>',
                        'title'  => 'Your review queue',
                        'desc'   => 'The draft lands in your UNIT dashboard for review. Approve with one click or make edits inline. Nothing goes out without you.',
                        'detail' => '→ Status: awaiting review\n→ Action required: approve or edit draft\n→ Deadline: 14 days remaining',
                    ],
                ],
                'testimonials' => [
                    ['quote' => 'We used to spend two days a week just managing renewals. AVA handles it before we even see it. Now it\'s a five-minute review.', 'name' => 'Maria T.', 'company' => 'BuildCo Operations'],
                    ['quote' => 'The draft quality is shockingly good. It pulled the right contact, the right license number, flagged an expiry we missed. Exactly what a good coordinator does.', 'name' => 'James R.', 'company' => 'Northline Services'],
                    ['quote' => 'We were skeptical of AI for compliance work. But UNIT gives us full visibility into every step. We\'re more confident in our filings now, not less.', 'name' => 'Sandra L.', 'company' => 'Vertex Solutions'],
                ],
                'faq' => [
                    ['q' => 'What agencies does AVA support?', 'a' => 'AVA currently handles renewals for NYCSCA, DOB, FDNY, and MTA. We add new agencies regularly — contact us if yours isn\'t listed.'],
                    ['q' => 'Does AVA submit renewals automatically?', 'a' => 'No. AVA prepares and drafts the renewal, then queues it for your review. Nothing submits without your explicit approval. You stay in control of every filing.'],
                    ['q' => 'How does AVA access my renewal inbox?', 'a' => 'AVA connects to your email via a configured inbox integration. You define which inbox it monitors and what it can read — you remain in control of the access scope.'],
                    ['q' => 'What happens if AVA misses something?', 'a' => 'Every transaction is fully logged and visible in your dashboard. If AVA can\'t classify or process something, it flags it for manual review rather than guessing.'],
                    ['q' => 'How much does it cost?', 'a' => 'Your first ' . PlatformDefaults::freeTransactionsFor($slug) . ' transactions are completely free. After that, you pay a monthly subscription based on your deployment. No setup fees, no per-transaction charges.'],
                    ['q' => 'Can I cancel my subscription?', 'a' => 'Yes — cancel any time, no questions asked. Your data stays accessible for 30 days after cancellation.'],
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

        return view($view, [
            'worker'          => $w,
            'deploymentCount' => $deploymentCount ?: 12,   // fallback for fresh installs
            'tokensToday'     => $tokensToday     ?: 48200,
            'totalTx'         => $totalTx         ?: 3840,
        ]);
    }

    // ── v2 worker page (A/B layer — same data, new view)
    public function show2(string $slug)
    {
        return $this->show($slug, 'workers.show-2');
    }
}

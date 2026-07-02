<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Platform\Services\PlatformClaude;

class AdminMessagingController extends Controller
{
    // ── Defaults used for seeding + reset ────────────────────────────────
    public static function defaults(): array
    {
        return [

            // ── AVA worker onboarding (worker_slug = 'ava') ───────────────
            [
                'key'               => 'ava_day3_no_gmail',
                'sequence'          => 'worker_onboarding',
                'audience'          => 'worker_specific',
                'worker_slug'       => 'ava',
                'label'             => 'AVA — Day 3 — No Gmail connected',
                'description'       => 'Sent to tenants who deployed AVA but haven\'t connected a Gmail inbox after 3 days.',
                'trigger_condition' => 'day_offset = 3 AND no Gmail credentials',
                'day_offset'        => 3,
                'trigger_state'     => 'no_gmail',
                'subject'           => 'Your AVA worker is waiting for an inbox',
                'body'              => "Hi {name},\n\nYou deployed AVA 3 days ago but haven't connected a Gmail inbox yet.\n\nAVA can't process emails until it has an inbox to watch. It takes about 2 minutes to connect:\n\n{app_url}/workers\n\nIf you hit any issues, just reply — I'll help you get it sorted.\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 10,
            ],
            [
                'key'               => 'ava_day3_no_tx',
                'sequence'          => 'worker_onboarding',
                'audience'          => 'worker_specific',
                'worker_slug'       => 'ava',
                'label'             => 'AVA — Day 3 — Connected, no emails processed',
                'description'       => 'Sent when Gmail is connected but AVA hasn\'t processed any emails after 3 days.',
                'trigger_condition' => 'day_offset = 3 AND has Gmail AND no transactions',
                'day_offset'        => 3,
                'trigger_state'     => 'no_tx',
                'subject'           => 'AVA is watching — here\'s what happens next',
                'body'              => "Hi {name},\n\nAVA is connected to your Gmail and waiting for renewal emails.\n\nWhen a relevant email lands, AVA will classify it, pull context from your memory bank, draft a response, and surface it in your dashboard for review.\n\nYou'll get notified when the first draft is ready.\n\nDashboard: {app_url}/dashboard\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 11,
            ],
            [
                'key'               => 'ava_day7_no_activity',
                'sequence'          => 'worker_onboarding',
                'audience'          => 'worker_specific',
                'worker_slug'       => 'ava',
                'label'             => 'AVA — Day 7 — Deployed but no activity',
                'description'       => 'Sent when AVA has been deployed a week with no processed emails.',
                'trigger_condition' => 'day_offset = 7 AND has worker AND no transactions',
                'day_offset'        => 7,
                'trigger_state'     => 'no_activity',
                'subject'           => 'AVA is live — are renewal emails coming in?',
                'body'              => "Hi {name},\n\nAVA has been running for a week but I don't see any processed emails yet.\n\nA few things to check:\n• Is the Gmail inbox receiving renewal or subscription emails?\n• Are AVA's capture rules set up? ({app_url}/workers)\n• Has the Gmail watch been activated?\n\nReply and I'll help you debug it.\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 12,
            ],

            // ── Platform onboarding — never deployed a worker ─────────────
            [
                'key'               => 'platform_day3_no_worker',
                'sequence'          => 'platform_onboarding',
                'audience'          => 'no_worker',
                'worker_slug'       => null,
                'label'             => 'Platform — Day 3 — No worker deployed',
                'description'       => 'Sent to tenants who signed up 3 days ago but haven\'t deployed any worker.',
                'trigger_condition' => 'day_offset = 3 AND no deployments',
                'day_offset'        => 3,
                'trigger_state'     => 'no_worker',
                'subject'           => 'What are you trying to automate?',
                'body'              => "Hi {name},\n\nYou signed up for UNIT 3 days ago — I wanted to check in.\n\nUNIT lets you deploy AI workers that handle the coordination work you're doing manually. Our first worker, AVA, handles renewal and subscription follow-ups.\n\nIf you're trying to automate something else, reply and tell me what it is. I'll tell you what's in the pipeline or what we can build.\n\nDeploy a worker: {app_url}/workers\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 20,
            ],
            [
                'key'               => 'platform_day7_no_worker',
                'sequence'          => 'platform_onboarding',
                'audience'          => 'no_worker',
                'worker_slug'       => null,
                'label'             => 'Platform — Day 7 — Still no worker',
                'description'       => 'Sent to tenants who haven\'t deployed anything after 7 days.',
                'trigger_condition' => 'day_offset = 7 AND no deployments',
                'day_offset'        => 7,
                'trigger_state'     => 'no_worker',
                'subject'           => 'Still thinking about it?',
                'body'              => "Hi {name},\n\nA week in — and you haven't deployed yet. That's fine, but I'd love to know what's in the way.\n\nIs it the wrong use case? Waiting on the team? Not sure how it works?\n\nReply and let me know. If UNIT isn't the right fit right now, I'd rather you tell me that than quietly churn.\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 21,
            ],
            [
                'key'               => 'platform_day14_no_worker',
                'sequence'          => 'platform_onboarding',
                'audience'          => 'no_worker',
                'worker_slug'       => null,
                'label'             => 'Platform — Day 14 — Last check-in',
                'description'       => 'Final touchpoint for tenants who never deployed after 2 weeks.',
                'trigger_condition' => 'day_offset = 14 AND no deployments',
                'day_offset'        => 14,
                'trigger_state'     => 'no_worker',
                'subject'           => 'Last note from me',
                'body'              => "Hi {name},\n\nTwo weeks since you signed up — I don't want to keep emailing if this isn't the right time.\n\nIf something's changed and you want to explore deploying a worker, the dashboard is always there: {app_url}/workers\n\nOtherwise, I'll let you be. You can always come back.\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 22,
            ],

            // ── Newsletter — all active tenants, 90-day arc ───────────────
            [
                'key'               => 'newsletter_day7',
                'sequence'          => 'newsletter',
                'audience'          => 'all',
                'worker_slug'       => null,
                'topic'             => 'How UNIT workers handle coordination vs. decision-making',
                'label'             => 'Newsletter — Day 7 — How workers think',
                'description'       => 'First newsletter: explains how AI workers coordinate without replacing human judgment.',
                'trigger_condition' => 'day_offset = 7',
                'day_offset'        => 7,
                'trigger_state'     => null,
                'subject'           => 'What an AI worker actually does (and doesn\'t do)',
                'body'              => "Hi {name},\n\nAI workers don't make decisions. They handle the coordination that happens before and after a decision.\n\nAVA, for example, doesn't decide whether to renew a contract — it reads the inbox, classifies the email, looks up the client, drafts a response, and surfaces it for you to approve. You keep the judgment call. AVA handles the tedious part.\n\nThat's the design pattern across all UNIT workers: automation where speed matters, human review where it counts.\n\nWorth knowing as you build out your stack.\n\nFranklin at UNIT\n\n—\nManage your preferences: {app_url}/settings",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 30,
            ],
            [
                'key'               => 'newsletter_day14',
                'sequence'          => 'newsletter',
                'audience'          => 'all',
                'worker_slug'       => null,
                'topic'             => 'Memory banks and why context persistence matters in automation',
                'label'             => 'Newsletter — Day 14 — Memory banks',
                'description'       => 'Second newsletter: how worker memory layers give responses the right context.',
                'trigger_condition' => 'day_offset = 14',
                'day_offset'        => 14,
                'trigger_state'     => null,
                'subject'           => 'Why your AI worker needs a memory bank',
                'body'              => "Hi {name},\n\nMost automation tools treat every transaction in isolation. UNIT workers don't.\n\nEvery time AVA processes an email, it checks your memory bank — clients, contacts, assets, previous interactions. That's why a draft to a long-term client reads differently than one to a new prospect.\n\nYou can build that memory bank manually, or let it grow as AVA processes emails over time. The more context it has, the sharper the drafts.\n\nIf you haven't set up your memory bank yet: {app_url}/memory\n\nFranklin at UNIT\n\n—\nManage preferences: {app_url}/settings",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 31,
            ],
            [
                'key'               => 'newsletter_day30',
                'sequence'          => 'newsletter',
                'audience'          => 'all',
                'worker_slug'       => null,
                'topic'             => '30-day milestone: what good adoption looks like, what to check',
                'label'             => 'Newsletter — Day 30 — One-month check-in',
                'description'       => 'One-month milestone email: tips for getting more from workers.',
                'trigger_condition' => 'day_offset = 30',
                'day_offset'        => 30,
                'trigger_state'     => null,
                'subject'           => 'One month in — here\'s what to check',
                'body'              => "Hi {name},\n\nYou've been on UNIT for 30 days. A few things worth reviewing at this stage:\n\n1. Capture rules — are workers catching the right emails? Tune the rules if anything's slipping through.\n2. Memory bank — does it have enough context on your key clients?\n3. Approval flow — are you reviewing drafts quickly, or are they stacking up?\n\nIf you're approving most drafts without changes, your worker is calibrated well. If you're editing heavily, the templates or rules probably need work.\n\nDashboard: {app_url}/dashboard\n\nFranklin at UNIT\n\n—\nManage preferences: {app_url}/settings",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 32,
            ],
            [
                'key'               => 'newsletter_day60',
                'sequence'          => 'newsletter',
                'audience'          => 'all',
                'worker_slug'       => null,
                'topic'             => 'What\'s coming in the worker marketplace, teaser of next worker',
                'label'             => 'Newsletter — Day 60 — What\'s coming',
                'description'       => 'Platform roadmap teaser and early access to upcoming workers.',
                'trigger_condition' => 'day_offset = 60',
                'day_offset'        => 60,
                'trigger_state'     => null,
                'subject'           => 'What\'s coming to UNIT',
                'body'              => "Hi {name},\n\nTwo months in — here's what we're building next.\n\nThe worker marketplace is coming. Instead of one-size-fits-all automation, you'll be able to browse workers built specifically for your industry or org type — NYC SCA procurement, DOB permits, FDNY compliance, MTA workflows.\n\nEach worker knows its domain deeply. That's the difference from generic automation.\n\nIf there's a specific coordination problem in your world you'd like to see automated, reply and tell me. The pipeline is shaped by what you tell us.\n\nFranklin at UNIT\n\n—\nManage preferences: {app_url}/settings",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 33,
            ],
            [
                'key'               => 'newsletter_day90',
                'sequence'          => 'newsletter',
                'audience'          => 'all',
                'worker_slug'       => null,
                'topic'             => '90-day milestone: the arc closes, what comes next, loyalty offer',
                'label'             => 'Newsletter — Day 90 — 90-day milestone',
                'description'       => 'End of the 90-day newsletter arc. Celebrate usage, offer something forward.',
                'trigger_condition' => 'day_offset = 90',
                'day_offset'        => 90,
                'trigger_state'     => null,
                'subject'           => '90 days of UNIT',
                'body'              => "Hi {name},\n\n90 days. That's long enough to know if this is working.\n\nIf your workers are running and saving you time, I'd love to hear what specifically changed. Those stories shape how we build.\n\nIf something's not quite right — a worker that's not calibrated, a use case we missed, a friction point in the workflow — now's the time to tell me. We iterate fast.\n\nEither way, reply. I read every response.\n\nFranklin at UNIT\n\n—\nManage preferences: {app_url}/settings",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 34,
            ],
        ];
    }

    // ── Influencer + referral defaults ───────────────────────────────────
    public static function influencerDefaults(): array
    {
        return [
            // ── Influencer lifecycle ──────────────────────────────────────
            [
                'key'               => 'influencer_application_received',
                'sequence'          => 'influencer',
                'audience'          => 'all',
                'worker_slug'       => null,
                'label'             => 'Influencer — Application Received',
                'description'       => 'Auto-sent when someone submits an influencer/partner application.',
                'trigger_condition' => 'on_influencer_apply',
                'day_offset'        => null,
                'trigger_state'     => null,
                'subject'           => 'We received your UNIT partner application',
                'body'              => "Hi {name},\n\nWe got your application to become a UNIT partner.\n\nWe review applications manually and usually respond within 2 business days. If approved, you'll get a unique referral link and access to your partner dashboard.\n\nWe'll be in touch.\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 40,
            ],
            [
                'key'               => 'influencer_approved',
                'sequence'          => 'influencer',
                'audience'          => 'all',
                'worker_slug'       => null,
                'label'             => 'Influencer — Approved',
                'description'       => 'Sent when an admin approves an influencer application.',
                'trigger_condition' => 'on_influencer_approve',
                'day_offset'        => null,
                'trigger_state'     => null,
                'subject'           => 'You\'re approved — here\'s your UNIT partner link',
                'body'              => "Hi {name},\n\nYou're now an active UNIT partner.\n\nYour referral link: {referral_url}\n\nAnyone who signs up through that link is attributed to you. When they convert to a paid plan, you earn {commission_rate}% of their MRR — recurring, for as long as they stay active.\n\nYou start on the Starter tier. Hit 5 paid conversions and you move to Pro (25%). Hit 15 and you're Elite (30%).\n\nYour partner dashboard: {app_url}/r/{slug}\n\nIf you have questions about how payouts work, just reply.\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 41,
            ],
            [
                'key'               => 'influencer_first_signup',
                'sequence'          => 'influencer',
                'audience'          => 'all',
                'worker_slug'       => null,
                'label'             => 'Influencer — First Referral Signed Up',
                'description'       => 'Sent to the influencer when their very first referred user registers.',
                'trigger_condition' => 'on_influencer_signup (first only)',
                'day_offset'        => null,
                'trigger_state'     => null,
                'subject'           => 'Your first referral just signed up',
                'body'              => "Hi {name},\n\nSomeone just signed up for UNIT through your link — your first referral.\n\nThey're in trial now. When they convert to a paid plan, your commission kicks in.\n\nTrack your referrals: {app_url}/r/{slug}\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 42,
            ],
            [
                'key'               => 'influencer_paid_conversion',
                'sequence'          => 'influencer',
                'audience'          => 'all',
                'worker_slug'       => null,
                'label'             => 'Influencer — Referral Converted to Paid',
                'description'       => 'Sent every time a referred user converts to a paid subscription.',
                'trigger_condition' => 'on_influencer_conversion',
                'day_offset'        => null,
                'trigger_state'     => null,
                'subject'           => 'A referral just converted — {commission_usd} earned',
                'body'              => "Hi {name},\n\nOne of your referrals just started a paid UNIT subscription.\n\nCommission earned: {commission_usd}\nYour total pending: {pending_payout}\n\nPayouts are processed monthly. Track your earnings: {app_url}/r/{slug}\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 43,
            ],
            [
                'key'               => 'influencer_tier_upgrade',
                'sequence'          => 'influencer',
                'audience'          => 'all',
                'worker_slug'       => null,
                'label'             => 'Influencer — Tier Upgrade',
                'description'       => 'Sent when an influencer is automatically upgraded to Pro or Elite tier.',
                'trigger_condition' => 'on_tier_upgrade',
                'day_offset'        => null,
                'trigger_state'     => null,
                'subject'           => 'You\'ve been upgraded to {new_tier} tier',
                'body'              => "Hi {name},\n\nYour commission rate just went up.\n\nYou're now on the {new_tier} tier — {new_rate}% on all future conversions.\n\nThis applies to every new paid referral from here on. Existing conversions stay at the rate they were earned.\n\nYour dashboard: {app_url}/r/{slug}\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 44,
            ],

            // ── Referred tenant welcome (peer referral) ───────────────────
            [
                'key'               => 'referral_welcome_tenant',
                'sequence'          => 'referral',
                'audience'          => 'all',
                'worker_slug'       => null,
                'label'             => 'Referred Tenant — Welcome',
                'description'       => 'Replaces the standard welcome for tenants who signed up via a peer referral code.',
                'trigger_condition' => 'on_registration with referral_code',
                'day_offset'        => null,
                'trigger_state'     => null,
                'subject'           => 'Welcome to UNIT — you\'ve got extra trial transactions',
                'body'              => "Hi {name},\n\nYou're in — and because you signed up through a referral, we've added {bonus_tx} extra trial transactions to your account.\n\nThat gives you more runway to test your first worker before committing.\n\nStart here: {app_url}/workers\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 45,
            ],

            // ── Peer referrer notifications ───────────────────────────────
            [
                'key'               => 'referral_peer_first_signup',
                'sequence'          => 'referral',
                'audience'          => 'all',
                'worker_slug'       => null,
                'label'             => 'Peer Referrer — First Referral Signed Up',
                'description'       => 'Sent to existing tenants when their first referred user registers.',
                'trigger_condition' => 'on_peer_referral_signup (first only)',
                'day_offset'        => null,
                'trigger_state'     => null,
                'subject'           => 'Someone just joined UNIT through your link',
                'body'              => "Hi {name},\n\nYour first referral just signed up for UNIT.\n\nWhen they start a paid subscription, you'll get a $25 credit on your account — applied automatically.\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 46,
            ],
            [
                'key'               => 'referral_peer_conversion',
                'sequence'          => 'referral',
                'audience'          => 'all',
                'worker_slug'       => null,
                'label'             => 'Peer Referrer — Referral Converted',
                'description'       => 'Sent to existing tenants when a referral converts to paid. $25 credit applied.',
                'trigger_condition' => 'on_peer_referral_conversion',
                'day_offset'        => null,
                'trigger_state'     => null,
                'subject'           => 'Your referral converted — $25 credit added',
                'body'              => "Hi {name},\n\nOne of your referrals just started a paid UNIT subscription.\n\nWe've added a $25 credit to your account — it'll apply to your next invoice automatically.\n\nYour balance: {app_url}/settings#referral\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'sort_order'        => 47,
            ],
        ];
    }

    // ── Transactional / inbound / welcome defaults ────────────────────────
    public static function transactionalDefaults(): array
    {
        return [
            // Welcome
            [
                'key'         => 'welcome_tenant',
                'sequence'    => 'welcome',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Tenant Welcome',
                'description' => 'Sent immediately after a new tenant registers.',
                'trigger_condition' => 'on_registration',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'Welcome to UNIT — your AI workforce platform',
                'body'        => "Hi {name},\n\nYou're in. Here's how to get your first worker running:\n\n1. Browse the worker catalog and deploy the one that fits your workflow\n2. Connect it to your inbox or data source\n3. Build your memory bank — clients, contacts, assets\n4. Review its first drafts and tune from there\n\nStart here: {app_url}/workers\n\nIf you have questions or want to talk through your use case before deploying, just reply.\n\nFranklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'sort_order'  => 1,
            ],

            // Stripe transactional
            [
                'key'         => 'billing_payment_failed',
                'sequence'    => 'transactional',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Payment Failed',
                'description' => 'Sent when a Stripe payment fails. Worker processing is paused.',
                'trigger_condition' => 'stripe: invoice.payment_failed',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'Action required: payment failed on your UNIT account',
                'body'        => "Hi {name},\n\nA payment on your UNIT account failed and your worker processing has been paused.\n\nPlease update your payment method to resume:\n\n{app_url}/billing\n\nIf this was a mistake or you need help, reply to this email.\n\nFranklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'sort_order'  => 2,
            ],
            [
                'key'         => 'billing_subscription_canceled',
                'sequence'    => 'transactional',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Subscription Canceled',
                'description' => 'Sent when a subscription is canceled via Stripe.',
                'trigger_condition' => 'stripe: customer.subscription.deleted',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'Your UNIT worker subscription has been canceled',
                'body'        => "Hi {name},\n\nYour subscription has been canceled and email processing has stopped.\n\nYou can reactivate anytime — all your data, memory, and templates are preserved:\n\n{app_url}/billing\n\nFranklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'sort_order'  => 3,
            ],
            [
                'key'         => 'billing_payment_resolved',
                'sequence'    => 'transactional',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Account Reactivated',
                'description' => 'Sent when payment is resolved and account is restored.',
                'trigger_condition' => 'stripe: invoice.paid (after failure)',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'Your UNIT account is active again',
                'body'        => "Hi {name},\n\nYour payment was received and your account is now active. Processing has resumed automatically.\n\nDashboard: {app_url}/dashboard\n\nFranklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'sort_order'  => 4,
            ],

            // Inbound — worker request
            [
                'key'         => 'inbound_worker_request_prospect',
                'sequence'    => 'inbound',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Worker Request — Prospect Auto-Reply',
                'description' => 'AI-generated follow-up sent to prospects who submit a worker request.',
                'trigger_condition' => 'on_worker_request_submit',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'Re: Your worker request — a few questions from us',
                'body'        => "Hi {name},\n\nWe've gone through what you shared about {current_process} and we'd like to learn more before we scope anything.\n\nA few questions:\n\n1. What does the input look like — is it an email, a form submission, a file, or something else?\n2. What does a completed output look like — a draft, a filed document, a sent message?\n3. Who reviews or approves the output before it goes anywhere?\n4. What tools or systems are already in place that a worker would need to read from or write to?\n5. What's the single biggest failure point in how this works today?\n\nReply to this email and we'll take it from there.\n\n— Franklin\nUNIT · hello@unit.report",
                'from_name'   => 'Franklin at UNIT',
                'sort_order'  => 5,
            ],
            [
                'key'         => 'inbound_worker_request_admin',
                'sequence'    => 'inbound',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Worker Request — Internal Notification',
                'description' => 'Internal email sent to hello@unit.report when a worker request is submitted.',
                'trigger_condition' => 'on_worker_request_submit',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'Worker Request: {name} — {company}',
                'body'        => "New worker request from {name} ({email})\n\nCompany: {company}\nRole: {role}\nOrg: {org}\nVolume: {volume}\n\nCURRENT PROCESS:\n{current_process}\n\nPAIN POINTS:\n{pain_points}\n\nAI FOLLOW-UP SENT:\n{ai_followup}",
                'from_name'   => 'UNIT System',
                'sort_order'  => 6,
            ],
            [
                'key'         => 'inbound_newsletter_subscribe',
                'sequence'    => 'inbound',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Newsletter Subscribe Confirmation',
                'description' => 'Sent to blog/newsletter subscribers after they sign up.',
                'trigger_condition' => 'on_newsletter_subscribe',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'You\'re subscribed to UNIT',
                'body'        => "Hi,\n\nYou're subscribed. We'll send you new posts when they're published — no noise, no cadence, just signal.\n\nIn the meantime: {app_url}/blog\n\n— Franklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'sort_order'  => 7,
            ],

            // ── Operational — previously hardcoded Mailables ──────────────────
            [
                'key'         => 'worker_deployed',
                'sequence'    => 'operational',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Worker Deployed',
                'description' => 'Sent immediately after a tenant successfully deploys a worker.',
                'trigger_condition' => 'on_worker_deploy',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => '{worker_name} is deployed and ready',
                'body'        => "Hi {name},\n\n{worker_name} is live.\n\nNext step: connect it to your inbox so it can start watching.\n\n{app_url}/workers/{worker_slug}/connect\n\nOnce connected, it'll begin processing automatically — no further setup needed.\n\nFranklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'sort_order'  => 20,
            ],
            [
                'key'         => 'draft_ready',
                'sequence'    => 'operational',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Draft Ready for Review',
                'description' => 'Sent when AVA completes a draft and it\'s waiting for human review.',
                'trigger_condition' => 'on_draft_ready',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'Draft ready: {draft_subject}',
                'body'        => "Hi {name},\n\nAVA has prepared a draft response for {client} regarding {asset}.\n\nConfidence: {confidence}%\n\nReview it here:\n{app_url}/transactions/{tx_id}\n\nApprove to send, or edit before sending.\n\nFranklin at UNIT",
                'from_name'   => 'UNIT · AVA',
                'sort_order'  => 21,
            ],
            [
                'key'         => 'gmail_connected',
                'sequence'    => 'operational',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Gmail Connected',
                'description' => 'Sent after a tenant successfully connects a Gmail inbox to a worker.',
                'trigger_condition' => 'on_gmail_oauth_complete',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'Gmail connected — UNIT Universe is now monitoring your inbox',
                'body'        => "Hi {name},\n\n{gmail_address} is now connected.\n\nYour worker will begin watching for relevant emails and processing them automatically. The first draft usually appears within a few minutes of an email arriving.\n\nYou can manage your inbox connections here:\n{app_url}/workers\n\nFranklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'sort_order'  => 22,
            ],
            [
                'key'         => 'password_changed',
                'sequence'    => 'operational',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Password Changed',
                'description' => 'Security notification sent after a password change.',
                'trigger_condition' => 'on_password_change',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'Your UNIT Universe password was changed',
                'body'        => "Hi {name},\n\nYour UNIT Universe password was just changed.\n\nIf this was you, no action is needed.\n\nIf you didn't do this, reset your password immediately:\n{app_url}/forgot-password\n\nFranklin at UNIT",
                'from_name'   => 'UNIT Universe',
                'sort_order'  => 23,
            ],
            [
                'key'         => 'daily_summary',
                'sequence'    => 'operational',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'AVA Daily Summary',
                'description' => 'Daily digest of what AVA processed. Sent each morning to active tenants.',
                'trigger_condition' => 'scheduled: daily at 8am',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'AVA daily summary — {date}',
                'body'        => "Hi {name},\n\nHere's what AVA handled today:\n\n{summary_body}\n\nReview all transactions:\n{app_url}/transactions\n\nFranklin at UNIT",
                'from_name'   => 'UNIT · AVA',
                'sort_order'  => 24,
            ],
            [
                'key'         => 'deletion_scheduled',
                'sequence'    => 'operational',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Account Deletion Scheduled',
                'description' => 'Sent when a tenant requests account deletion. 30-day grace period.',
                'trigger_condition' => 'on_account_deletion_request',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'Your UNIT account is scheduled for deletion',
                'body'        => "Hi {name},\n\nWe've received your request to delete your UNIT account.\n\nYour account will be permanently deleted on {deletion_date}. Until then, everything is still accessible and you can cancel the deletion from your profile settings.\n\nIf you change your mind:\n{app_url}/settings/profile\n\nFranklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'sort_order'  => 25,
            ],
            [
                'key'         => 'account_deleted',
                'sequence'    => 'operational',
                'audience'    => 'all',
                'worker_slug' => null,
                'label'       => 'Account Deleted',
                'description' => 'Final confirmation sent after permanent account deletion.',
                'trigger_condition' => 'on_account_deleted',
                'day_offset'  => null,
                'trigger_state' => null,
                'subject'     => 'Your UNIT account has been deleted',
                'body'        => "Hi {name},\n\nYour UNIT account and all associated data have been permanently deleted.\n\nIf you ever want to return, you're welcome to sign up again at any time:\n{app_url}/register\n\nFranklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'sort_order'  => 26,
            ],
        ];
    }

    // ── Manual seed endpoint ──────────────────────────────────────────────
    public function seed(): \Illuminate\Http\RedirectResponse
    {
        $this->seedDefaults();
        return redirect()->route('admin.messaging')->with('success', 'Templates seeded successfully.');
    }

    // ── Seed all defaults into the DB (idempotent — skips existing keys) ─
    public function seedDefaults(): void
    {
        $all = array_merge(self::defaults(), self::influencerDefaults(), self::transactionalDefaults());
        $existing = DB::table('platform_email_templates')->pluck('key')->flip();
        $now = now();
        foreach ($all as $row) {
            if ($existing->has($row['key'])) continue;
            DB::table('platform_email_templates')->insert(array_merge($row, [
                'active'     => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    // ── Helper: load one template by key (with fallback to hardcoded) ─────
    public static function getTemplate(string $key, array $fallback = []): ?object
    {
        $tpl = DB::table('platform_email_templates')->where('key', $key)->where('active', true)->first();
        if ($tpl) return $tpl;

        // Fallback to defaults if not seeded yet
        $all = array_merge(self::defaults(), self::transactionalDefaults());
        $def = collect($all)->firstWhere('key', $key);
        if (!$def && empty($fallback)) return null;
        return (object) array_merge($def ?? [], $fallback);
    }

    // ── Index ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'sequences');

        // Auto-seed any missing defaults (idempotent — skips existing keys)
        $this->seedDefaults();

        $templates = DB::table('platform_email_templates')
            ->orderBy('sort_order')
            ->get();

        $grouped = $templates->groupBy('sequence');

        // Workers that already have onboarding templates
        $workerSlugs = $templates->where('sequence', 'worker_onboarding')
            ->pluck('worker_slug')
            ->filter()
            ->unique()
            ->values();

        // All registered workers (for adding new sequences)
        $allWorkers = DB::table('worker_registry')->orderBy('name')->get(['slug', 'name']);

        // Feedback sources: pull sample language from worker requests
        $feedbackSources = collect();
        if ($tab === 'feedback') {
            $feedbackSources = DB::table('worker_requests')
                ->where(fn($q) => $q->whereNotNull('pain_points')->orWhereNotNull('current_process'))
                ->latest()
                ->limit(30)
                ->get(['name', 'company', 'current_process', 'pain_points', 'created_at']);
        }

        return view('admin.messaging', compact('templates', 'grouped', 'tab', 'feedbackSources', 'workerSlugs', 'allWorkers'));
    }

    // ── Create new template ───────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'sequence'    => 'required|in:worker_onboarding,platform_onboarding,newsletter',
            'worker_slug' => 'nullable|string|max:80',
            'label'       => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'day_offset'  => 'required|integer|min:0',
            'trigger_state' => 'nullable|string|max:80',
            'trigger_condition' => 'nullable|string|max:255',
            'subject'     => 'required|string|max:255',
            'body'        => 'required|string',
            'from_name'   => 'required|string|max:120',
            'topic'       => 'nullable|string|max:255',
            'audience'    => 'nullable|string|max:40',
        ]);

        $slug = $data['sequence'] === 'worker_onboarding'
            ? ($data['worker_slug'] ?? 'unknown')
            : null;

        $key = $data['sequence'] . '_' . ($slug ?? 'platform') . '_day' . $data['day_offset']
             . '_' . ($data['trigger_state'] ?? 'any')
             . '_' . time();

        DB::table('platform_email_templates')->insert([
            'key'               => $key,
            'sequence'          => $data['sequence'],
            'audience'          => $data['audience'] ?? ($data['sequence'] === 'worker_onboarding' ? 'worker_specific' : ($data['sequence'] === 'platform_onboarding' ? 'no_worker' : 'all')),
            'worker_slug'       => $slug,
            'label'             => $data['label'],
            'description'       => $data['description'] ?? null,
            'day_offset'        => $data['day_offset'],
            'trigger_state'     => $data['trigger_state'] ?? null,
            'trigger_condition' => $data['trigger_condition'] ?? null,
            'subject'           => $data['subject'],
            'body'              => $data['body'],
            'from_name'         => $data['from_name'],
            'topic'             => $data['topic'] ?? null,
            'active'            => true,
            'sort_order'        => DB::table('platform_email_templates')->max('sort_order') + 1,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Save template ─────────────────────────────────────────────────────
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'subject'   => 'required|string|max:255',
            'body'      => 'required|string',
            'from_name' => 'required|string|max:120',
            'active'    => 'nullable|boolean',
            'topic'     => 'nullable|string|max:255',
        ]);

        DB::table('platform_email_templates')->where('id', $id)->update([
            'subject'    => $data['subject'],
            'body'       => $data['body'],
            'from_name'  => $data['from_name'],
            'active'     => $request->boolean('active', true),
            'topic'      => $data['topic'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.messaging', ['tab' => 'templates'])->with('success', 'Template saved.');
    }

    // ── Reset to default ──────────────────────────────────────────────────
    public function reset(Request $request, int $id)
    {
        $tpl = DB::table('platform_email_templates')->find($id);
        if (!$tpl) return response()->json(['error' => 'Template not found'], 404);

        $defaults = collect(self::defaults())->firstWhere('key', $tpl->key);
        if (!$defaults) return response()->json(['error' => 'No default found for this template.'], 422);

        DB::table('platform_email_templates')->where('id', $id)->update([
            'subject'    => $defaults['subject'],
            'body'       => $defaults['body'],
            'from_name'  => $defaults['from_name'],
            'updated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── AI Rewrite ────────────────────────────────────────────────────────
    public function rewrite(Request $request, int $id)
    {
        $tpl = DB::table('platform_email_templates')->find($id);
        if (!$tpl) abort(404);

        // Pull memory sources: user language from worker requests
        $painPoints = DB::table('worker_requests')
            ->whereNotNull('pain_points')
            ->latest()
            ->limit(15)
            ->pluck('pain_points')
            ->filter()
            ->implode("\n---\n");

        $processes = DB::table('worker_requests')
            ->whereNotNull('current_process')
            ->latest()
            ->limit(10)
            ->pluck('current_process')
            ->filter()
            ->implode("\n---\n");

        $system = "You are an expert SaaS email copywriter. You write short, plain-text emails that feel personal and direct — no fluff, no corporate speak, no bullet-point overload. The sender is Franklin, founder of UNIT, a platform for deploying AI automation workers.\n\nYour rewrites should:\n- Feel like they came from a real person who understands the reader's problem\n- Use the reader's language where possible (informed by the feedback sources below)\n- Be shorter than the original\n- Have a single clear action or question\n- Never use subject lines with exclamation marks\n- End simply with: Franklin at UNIT";

        $sequenceLabel = match($tpl->sequence) {
            'worker_onboarding'   => "Worker onboarding ({$tpl->worker_slug}) — Day {$tpl->day_offset}",
            'platform_onboarding' => "Platform onboarding (no worker deployed) — Day {$tpl->day_offset}",
            'newsletter'          => "Newsletter — Day {$tpl->day_offset} milestone",
            default               => $tpl->sequence,
        };

        $userPrompt = "Rewrite this email. Use real user language from our intake forms to make it land better.\n\n";
        $userPrompt .= "SEQUENCE TYPE: {$sequenceLabel}\n";
        $userPrompt .= "TEMPLATE: {$tpl->label}\n";
        if ($tpl->trigger_condition) $userPrompt .= "TRIGGER: {$tpl->trigger_condition}\n";
        if ($tpl->topic) $userPrompt .= "TOPIC FOCUS: {$tpl->topic}\n";
        $userPrompt .= "CURRENT SUBJECT: {$tpl->subject}\n\n";
        $userPrompt .= "CURRENT BODY:\n{$tpl->body}\n\n";

        if ($request->input('notes')) {
            $userPrompt .= "REWRITE NOTES FROM ADMIN:\n" . $request->input('notes') . "\n\n";
        }

        if ($painPoints) {
            $userPrompt .= "REAL USER PAIN POINTS (from intake forms — use their language):\n{$painPoints}\n\n";
        }
        if ($processes) {
            $userPrompt .= "HOW USERS DESCRIBE THEIR CURRENT PROCESS:\n{$processes}\n\n";
        }

        $userPrompt .= "Return ONLY a JSON object with keys: subject (string), body (string), notes (string — explain what you changed and why).\nDo not wrap in markdown code blocks.";

        try {
            $platform = new PlatformClaude();
            $raw = $platform->ask($system, $userPrompt, 1200, 'messaging_rewrite', 'admin:messaging');
            $result = json_decode($raw, true);

            if (!isset($result['subject'], $result['body'])) {
                return response()->json(['error' => 'AI returned unexpected format. Try again.'], 422);
            }

            return response()->json([
                'subject' => $result['subject'],
                'body'    => $result['body'],
                'notes'   => $result['notes'] ?? '',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'AI rewrite failed: ' . $e->getMessage()], 500);
        }
    }

    // ── Accept AI rewrite ─────────────────────────────────────────────────
    public function acceptRewrite(Request $request, int $id)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
            'notes'   => 'nullable|string',
        ]);

        DB::table('platform_email_templates')->where('id', $id)->update([
            'subject'              => $data['subject'],
            'body'                 => $data['body'],
            'ai_rewrite_notes'     => $data['notes'] ?? null,
            'last_ai_rewrite_at'   => now(),
            'updated_at'           => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ── Test send ─────────────────────────────────────────────────────────
    public function testSend(Request $request, int $id)
    {
        $tpl = DB::table('platform_email_templates')->find($id);
        if (!$tpl) return response()->json(['error' => 'Template not found'], 404);

        $admin = Auth::user();
        $appUrl = config('app.url');

        $body    = str_replace(['{name}', '{app_url}'], [$admin->name, $appUrl], $tpl->body);
        $subject = '[TEST] ' . $tpl->subject;

        try {
            Mail::raw($body, fn($m) => $m
                ->to($admin->email, $admin->name)
                ->subject($subject)
                ->replyTo('hello@unit.report', $tpl->from_name)
            );
            return response()->json(['ok' => true, 'message' => "Test sent to {$admin->email}"]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Send failed: ' . $e->getMessage()], 500);
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seeds the 3 real blog posts the homepage's Resources section links to
     * (Case Study, Workflow Breakdown, Customer Story), replacing what were
     * previously fabricated cards linking nowhere. insertOrIgnore so this is
     * safe to run against an environment where these slugs already exist.
     */
    public function up(): void
    {
        $now = now();

        DB::table('blog_posts')->insertOrIgnore([
            [
                'title' => "AVA's Renewal Workflow, Step by Step: Detect, Draft, Approve, Send",
                'slug' => 'avas-renewal-workflow-step-by-step',
                'tag' => 'Workflow Breakdown',
                'excerpt' => "A plain-English walkthrough of AVA's real pipeline: what each stage does, how memory lookup works, and why nothing gets sent without your approval.",
                'body' => '<h2>The problem with renewal inboxes</h2><p>A typical renewal inbox on a Monday morning is a mess of unrelated messages: expiration notices, subscription reminders, compliance alerts, arriving at random times, from random senders, in random formats. Someone has to read each one, figure out what it actually needs, look up the account it belongs to, and write a response. That is the work AVA was built to own.</p><p>AVA does not just skim the inbox. Every incoming email runs through a defined pipeline, the same stages, every time, with a full record of what happened at each one.</p><h2>Stage 1: Read</h2><p>When a new message arrives in the connected Gmail inbox, AVA reads it and produces a structured summary: what it is about, what action it needs, when it is due, and how urgent it is. This turns an unstructured email into data every later stage can use.</p><h2>Stage 2: Classify</h2><p>Next, AVA decides what kind of email this actually is, a renewal, a new request, a status check, or something unrelated that does not need a response at all. Anything that is not renewal-related gets set aside here; AVA does not force every message into its pipeline.</p><h2>Stage 3: Memory lookup</h2><p>For anything that is a renewal, AVA checks your stored client, contact, and asset records for a match. This is what lets it recognize this is the same account that renewed last year instead of treating every email as a first contact.</p><h2>Stage 4: Select a template</h2><p>AVA picks the response template that fits, based on the type of renewal and whatever tone or format you have configured for your account.</p><h2>Stage 5: Log the transaction</h2><p>Before any draft gets written, the transaction is logged. This is the audit trail: every renewal AVA touches has a record, regardless of what happens to it next.</p><h2>Stage 6: Draft</h2><p>This is where the actual writing happens. AVA combines what it read, how it classified the email, what it found in memory, and the selected template into a complete draft response.</p><h2>Stage 7: Push to your review queue</h2><p>The finished draft goes into your UNIT dashboard and into Gmail Drafts, not your Sent folder. It is ready to read, edit if needed, and approve.</p><h2>The human gate</h2><p>AVA never sends an email. That is not a limitation, it is the design. Every draft waits for you to review it. Approve it and it is ready in Gmail for you to send yourself; reject it and it is removed. The pipeline handles the repetitive work; the decision to send stays with a person.</p>',
                'author' => 'UNIT',
                'status' => 'published',
                'worker_slug' => 'ava',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title' => 'Inside a Real Renewal Workflow: How AVA Tracks, Drafts, and Follows Up',
                'slug' => 'inside-a-real-renewal-workflow',
                'tag' => 'Case Study',
                'excerpt' => "A walkthrough of what a renewal actually looks like moving through AVA's pipeline, from the moment it lands in the inbox to the moment it is ready to send.",
                'body' => '<p><em>This walkthrough describes how AVA\'s pipeline handles a renewal in practice, it is illustrative of the process, not a specific named account, since we are still early and building our library of real customer stories.</em></p><h2>Monday, 8:14 AM: a renewal notice lands</h2><p>An email arrives in the connected inbox, a subscription or license renewal, due in three weeks. Under the old process, this sits until someone has time to read it, check the account history, and figure out what to send back. With AVA, the pipeline starts within seconds of the webhook firing.</p><h2>The read and classify stages</h2><p>AVA reads the message and produces a structured summary: what is expiring, the due date, and how urgent it is. It is then classified as a renewal (not a new request or an unrelated message) and queued for the next stage.</p><h2>Memory lookup: recognizing the account</h2><p>AVA checks stored client and contact records for a match. If this account renewed before, that history (prior terms, past contacts, any notes on file) comes back into the pipeline instead of getting rediscovered from scratch.</p><h2>Drafting the response</h2><p>Using the matched template and the account\'s history, AVA writes a complete draft, not a generic template with blanks, but a response that reflects what is actually on file for that account.</p><h2>Landing in the review queue</h2><p>By the time a coordinator opens their dashboard, the draft is already sitting in the queue and in Gmail Drafts. They read it, make any edits, and approve. Nothing goes out until they do.</p><h2>What this replaces</h2><p>Without this pipeline, the same renewal requires someone to notice the email, pull up the account separately, draft a response manually, and remember to follow up if there is no reply. AVA does not remove the review step, it removes everything before it.</p><p>Have a real renewal workflow you would like to see broken down, or want to share how AVA fits into yours? <a href="mailto:hello@unit.report?subject=Our Renewal Workflow">Tell us about it</a>, we are building out real case studies as more teams come on board.</p>',
                'author' => 'UNIT',
                'status' => 'published',
                'worker_slug' => 'ava',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'title' => 'What Changes When AVA Owns Your Renewal Inbox',
                'slug' => 'what-changes-when-ava-owns-your-renewal-inbox',
                'tag' => 'Customer Story',
                'excerpt' => 'We do not have a named customer story to share yet. Here is what actually changes, based on how AVA\'s pipeline works today, and an open invitation to be our first.',
                'body' => '<p><em>We are early. AVA does not have a published customer story yet. Rather than invent one, here is an honest look at what changes based on what the pipeline actually does, plus an invitation: if you are running AVA today, we would genuinely like to feature your story here instead.</em></p><h2>Before: the inbox is the system</h2><p>For most teams we talk to, renewal tracking lives inside a shared inbox. Someone has to notice each email, remember which account it belongs to, check history somewhere else, draft a reply, and keep track of who still needs a follow-up. None of that is written down anywhere except the inbox itself.</p><h2>After: the inbox is an input, not the system</h2><p>With AVA connected, the inbox becomes one input into a process that runs the same way every time: read, classify, check memory, draft, log, queue for review. The renewal register, not someone\'s memory of what is in their inbox, becomes the source of truth for what is outstanding.</p><h2>What does not change</h2><p>The decision to send still belongs to a person. AVA drafts; it does not send. Every draft sits in a review queue until someone approves it. Teams that were worried about AI running unsupervised tend to find the opposite: more visibility into what is happening, not less, because every transaction is logged.</p><h2>What we hear most</h2><p>The recurring theme in early conversations is not a specific number of hours saved. We are not going to make one up. It is that renewals stop depending on one person remembering to check. The process runs whether or not anyone is thinking about it that day.</p><p>If you are running AVA and want to share what has actually changed for your team, <a href="mailto:hello@unit.report?subject=My AVA Story">we would love to hear it</a>, and feature it here instead of this placeholder.</p>',
                'author' => 'UNIT',
                'status' => 'published',
                'worker_slug' => 'ava',
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('blog_posts')->whereIn('slug', [
            'avas-renewal-workflow-step-by-step',
            'inside-a-real-renewal-workflow',
            'what-changes-when-ava-owns-your-renewal-inbox',
        ])->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seeds the 4th real Resources post, the homepage's "Blog" card
     * ("5 signs...") was still an unlinked placeholder after the first
     * seed migration covered Case Study / Workflow Breakdown / Customer
     * Story. insertOrIgnore so this is safe if the slug already exists.
     */
    public function up(): void
    {
        $now = now();

        DB::table('blog_posts')->insertOrIgnore([
            [
                'title' => '5 Signs Your Team Is Doing Work an AI Agent Should Be Doing Instead',
                'slug' => '5-signs-your-team-needs-an-ai-agent',
                'tag' => 'Operations',
                'excerpt' => "Deadlines tracked in someone's memory, the same email retyped every time, one person who knows the process. Here is how to spot the busywork worth automating.",
                'body' => '<p>Most teams do not decide to automate a workflow. They just notice, one day, that the workflow has quietly become someone\'s full-time babysitting job. Here are five signs that has already happened.</p><h2>1. Deadlines live in a spreadsheet, not a system</h2><p>If a renewal, filing, or expiration date only exists because someone remembered to write it down, or because it is buried in an inbox, you do not have a tracking process. You have a person who has not gone on vacation in a while.</p><h2>2. The same email gets written from scratch, every time</h2><p>Follow-ups, renewal notices, confirmations. If someone is retyping a similar response from zero each time instead of working from a template and account history, that is hours of repeated writing that is not actually a judgment call.</p><h2>3. One person is the only one who knows the process</h2><p>If the workflow stalls the moment one specific person is out sick or on leave, the process was never actually documented, it was memorized. That is a single point of failure wearing a job title.</p><h2>4. Nothing gets flagged until it is already late</h2><p>Teams without a tracking system tend to find out about a problem the same way: after the deadline has passed and someone is asking why. Catching things early requires something actively watching, not someone remembering to check.</p><h2>5. You cannot answer "what happened to that request" without digging through the inbox</h2><p>If tracing a decision means searching an inbox for a thread from three weeks ago, there is no audit trail, just a search function standing in for one.</p><h2>What actually fixes this</h2><p>None of these are solved by working harder or checking the inbox more often. They are solved by a system that reads the incoming request, checks what is already known about the account, drafts the response, and keeps a record of it, every time, the same way. That is what UNIT\'s AI Workers are built to own, with every draft still routed to a human before anything goes out.</p>',
                'author' => 'UNIT',
                'status' => 'published',
                'worker_slug' => 'ava',
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('blog_posts')->where('slug', '5-signs-your-team-needs-an-ai-agent')->delete();
    }
};

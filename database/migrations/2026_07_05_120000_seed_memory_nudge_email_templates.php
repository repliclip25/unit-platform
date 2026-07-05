<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now  = now();
        $base = DB::table('platform_email_templates')->max('sort_order') ?? 0;

        $templates = [
            [
                'key'         => 'memory_nudge_d1',
                'label'       => 'AVA — Memory Nudge (Day 1)',
                'subject'     => 'AVA ran — but didn\'t recognise your clients',
                'body'        =>
"Hi {name},

AVA is up and running — but her memory is still mostly empty.

Right now she's processing emails without knowing your clients, which means every draft will say \"Unknown\" and need manual correction before it's usable.

You're at {score}% memory coverage. You need {needed} more complete client records to reach reliable drafts.

A complete record takes 90 seconds: client name + contact email + one policy.

Add your next client here:
{app_url}/onboarding/step/memory

The sooner you load your book, the sooner AVA starts producing drafts you can actually approve.

Franklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'active'      => true,
                'sort_order'  => $base + 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'memory_nudge_d3',
                'label'       => 'AVA — Memory Nudge (Day 3)',
                'subject'     => 'AVA is {score}% there — {needed} more clients to go',
                'body'        =>
"Hi {name},

Three days in — AVA has been processing your inbox, but memory is still at {score}%.

At {score}% she recognises some emails. At 100% ({threshold} complete records), drafts become reliable enough to approve in one click.

You're {needed} complete records away. Each one takes about 90 seconds.

Client name. Contact email. Policy name. That's it.

Add them here:
{app_url}/onboarding/step/memory

Most brokers hit the threshold in one sitting. Takes about 10 minutes for 5 clients.

Franklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'active'      => true,
                'sort_order'  => $base + 2,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'memory_nudge_d7',
                'label'       => 'AVA — Memory Nudge (Day 7)',
                'subject'     => 'One week in — AVA is still waiting on your clients',
                'body'        =>
"Hi {name},

A week since you set up AVA — and memory is still at {score}%.

I'll be straight: without your book of business loaded, AVA can't do what she was built to do. Every email comes back \"Unknown.\" Every draft needs a rewrite.

You need {needed} complete records to reach reliable drafts. That's {needed} clients with a contact email and a policy — about {needed} minutes of work.

Here's the link:
{app_url}/onboarding/step/memory

If the quick-add form isn't working for you, reply to this email and I'll help you import via CSV.

Franklin at UNIT",
                'from_name'   => 'Franklin at UNIT',
                'active'      => true,
                'sort_order'  => $base + 3,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        foreach ($templates as $tpl) {
            $exists = DB::table('platform_email_templates')->where('key', $tpl['key'])->exists();
            if (!$exists) {
                DB::table('platform_email_templates')->insert($tpl);
            }
        }
    }

    public function down(): void
    {
        DB::table('platform_email_templates')
            ->whereIn('key', ['memory_nudge_d1', 'memory_nudge_d3', 'memory_nudge_d7'])
            ->delete();
    }
};

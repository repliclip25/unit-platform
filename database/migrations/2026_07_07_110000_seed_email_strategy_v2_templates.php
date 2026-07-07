<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Update welcome_tenant subject + body in place
        DB::table('platform_email_templates')
            ->where('key', 'welcome_tenant')
            ->update([
                'subject'    => 'Meet Ava — your first AI employee.',
                'body'       => "Hi {name},\n\nWelcome to UNIT.\n\nToday you're hiring your first AI employee.\n\nHer name is Ava.\n\nIn less than a minute you'll:\n\n• Give her access to work\n• Teach her about your business\n• Watch her complete her first assignment\n\nOnce that's done, Ava will quietly monitor your inbox, prepare renewal replies, and leave everything ready for your approval.\n\nLet's get her started.\n\n{app_url}/onboarding\n\nFranklin at UNIT",
                'updated_at' => $now,
            ]);

        // Seed the 3 abandonment templates + First Value Email if not already present
        $templates = [
            [
                'key'               => 'ava_abandon_no_gmail',
                'sequence'          => 'worker_onboarding',
                'audience'          => 'worker_specific',
                'worker_slug'       => 'ava',
                'label'             => 'AVA — Onboarding — No Gmail (1hr)',
                'description'       => 'Sent when user started onboarding but hasn\'t connected Gmail after 1 hour.',
                'trigger_condition' => 'delay_hours = 1 AND no Gmail credentials AND in onboarding',
                'day_offset'        => null,
                'delay_hours'       => 1,
                'trigger_state'     => 'no_gmail',
                'subject'           => 'Ava is waiting for her workspace.',
                'body'              => "Hi {name},\n\nEverything is ready.\n\nThe only thing missing is access to your Gmail inbox.\n\nOnce connected, Ava can begin watching for renewal emails and preparing replies for you.\n\nFinish setup — it only takes a moment.\n\n{app_url}/onboarding\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'active'            => true,
                'sort_order'        => 10,
            ],
            [
                'key'               => 'ava_abandon_no_clients',
                'sequence'          => 'worker_onboarding',
                'audience'          => 'worker_specific',
                'worker_slug'       => 'ava',
                'label'             => 'AVA — Onboarding — No Clients (24hr)',
                'description'       => 'Sent when Gmail is connected but no clients added after 24 hours.',
                'trigger_condition' => 'delay_hours = 24 AND has Gmail AND no clients AND in onboarding',
                'day_offset'        => null,
                'delay_hours'       => 24,
                'trigger_state'     => 'no_clients',
                'subject'           => 'Help Ava recognize your clients.',
                'body'              => "Hi {name},\n\nAva is ready to work.\n\nShe just needs to know who she's working for.\n\nAdd your first client so she can recognize renewal emails and prepare accurate drafts.\n\nYou only need one to get started.\n\n{app_url}/onboarding\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'active'            => true,
                'sort_order'        => 11,
            ],
            [
                'key'               => 'ava_abandon_no_fasttrack',
                'sequence'          => 'worker_onboarding',
                'audience'          => 'worker_specific',
                'worker_slug'       => 'ava',
                'label'             => 'AVA — Onboarding — No Test Run (24hr)',
                'description'       => 'Sent when clients are added but fast-track test hasn\'t been run after 24 hours.',
                'trigger_condition' => 'delay_hours = 24 AND has clients AND no fast_track AND in onboarding',
                'day_offset'        => null,
                'delay_hours'       => 24,
                'trigger_state'     => 'no_fast_track',
                'subject'           => "Let's watch Ava's first assignment.",
                'body'              => "Hi {name},\n\nEverything is configured.\n\nNow it's time to watch Ava work.\n\nRun the live test and see exactly how she reads, understands, and prepares a renewal response before anything reaches your customers.\n\n{app_url}/onboarding\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'active'            => true,
                'sort_order'        => 12,
            ],
            [
                'key'               => 'ava_first_real_renewal',
                'sequence'          => 'worker_onboarding',
                'audience'          => 'worker_specific',
                'worker_slug'       => 'ava',
                'label'             => 'AVA — First Real Renewal Processed',
                'description'       => 'Sent once when Ava processes the first genuine renewal email (not a fast-track demo). Most important onboarding email.',
                'trigger_condition' => 'first real draft_ready transaction (source != fast_track)',
                'day_offset'        => null,
                'delay_hours'       => null,
                'trigger_state'     => 'first_real_renewal',
                'subject'           => 'Ava just completed her first assignment.',
                'body'              => "Hi {name},\n\nAva just finished her first job.\n\nHere's what she did — automatically, without any input from you:\n\n✓ Read the renewal email\n✓ Identified the correct client\n✓ Retrieved the relevant information\n✓ Selected the right response\n✓ Prepared a draft for your review\n\nNothing was sent. Nothing changed in your inbox.\n\nThe draft is sitting in Gmail Drafts, ready whenever you are.\n\nThis is exactly how Ava will handle every renewal that comes in from here on out.\n\nOpen your dashboard: {app_url}/dashboard\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'active'            => true,
                'sort_order'        => 13,
            ],
            [
                'key'               => 'weekly_summary',
                'sequence'          => 'operational',
                'audience'          => 'all',
                'worker_slug'       => null,
                'label'             => 'AVA Weekly Summary',
                'description'       => 'Monday morning digest of what Ava accomplished in the past 7 days. Only sends if there was at least 1 transaction. DB-computed stats — no AI cost.',
                'trigger_condition' => 'scheduled: every Monday at 8am (cron: 0 8 * * 1)',
                'day_offset'        => null,
                'delay_hours'       => null,
                'trigger_state'     => null,
                'subject'           => "Here's what Ava accomplished this week.",
                'body'              => "Hi {name},\n\nHere's what Ava accomplished this week ({week_label}):\n\n{summary_body}\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'active'            => true,
                'sort_order'        => 25,
            ],
        ];

        foreach ($templates as $tpl) {
            $exists = DB::table('platform_email_templates')->where('key', $tpl['key'])->exists();
            if (!$exists) {
                DB::table('platform_email_templates')->insert(array_merge($tpl, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        // Retire daily_summary
        DB::table('platform_email_templates')
            ->where('key', 'daily_summary')
            ->update(['active' => false, 'updated_at' => $now]);
    }

    public function down(): void
    {
        DB::table('platform_email_templates')
            ->whereIn('key', [
                'ava_abandon_no_gmail',
                'ava_abandon_no_clients',
                'ava_abandon_no_fasttrack',
                'ava_first_real_renewal',
                'weekly_summary',
            ])->delete();

        DB::table('platform_email_templates')
            ->where('key', 'daily_summary')
            ->update(['active' => true]);
    }
};

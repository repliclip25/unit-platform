<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // Revert welcome_tenant to generic platform welcome — fires on registration,
        // before the user has selected a worker. Introduces UNIT, not Ava specifically.
        DB::table('platform_email_templates')
            ->where('key', 'welcome_tenant')
            ->update([
                'subject'    => 'Welcome to UNIT.',
                'body'       => "Hi {name},\n\nWelcome to UNIT.\n\nYou're one step away from hiring your first AI employee.\n\nStart setup and we'll have your worker running in under a minute.\n\n{app_url}/onboarding\n\nFranklin at UNIT",
                'updated_at' => $now,
            ]);

        // Add ava_worker_selected — fires when Ava is deployed during onboarding.
        // This is the "Meet Ava" moment — user has just picked their worker.
        $exists = DB::table('platform_email_templates')->where('key', 'ava_worker_selected')->exists();
        if (!$exists) {
            DB::table('platform_email_templates')->insert([
                'key'               => 'ava_worker_selected',
                'sequence'          => 'worker_onboarding',
                'audience'          => 'worker_specific',
                'worker_slug'       => 'ava',
                'label'             => 'AVA — Worker Selected',
                'description'       => 'Fires when Ava is deployed at the start of onboarding. Introduces Ava to the new hire.',
                'trigger_condition' => 'on_ava_deployment_during_onboarding',
                'day_offset'        => null,
                'delay_hours'       => null,
                'trigger_state'     => null,
                'subject'           => 'Meet Ava — your first AI employee.',
                'body'              => "Hi {name},\n\nYou just hired Ava.\n\nHer name is Ava.\n\nIn less than a minute you'll:\n\n• Give her access to work\n• Teach her about your business\n• Watch her complete her first assignment\n\nOnce that's done, Ava will quietly monitor your inbox, prepare renewal replies, and leave everything ready for your approval.\n\nLet's get her started.\n\n{app_url}/onboarding\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'active'            => true,
                'sort_order'        => 9,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('platform_email_templates')
            ->where('key', 'welcome_tenant')
            ->update([
                'subject' => 'Meet Ava — your first AI employee.',
                'body'    => "Hi {name},\n\nWelcome to UNIT.\n\nToday you're hiring your first AI employee.\n\nHer name is Ava.\n\nIn less than a minute you'll:\n\n• Give her access to work\n• Teach her about your business\n• Watch her complete her first assignment\n\nOnce that's done, Ava will quietly monitor your inbox, prepare renewal replies, and leave everything ready for your approval.\n\nLet's get her started.\n\n{app_url}/onboarding\n\nFranklin at UNIT",
            ]);

        DB::table('platform_email_templates')->where('key', 'ava_worker_selected')->delete();
    }
};

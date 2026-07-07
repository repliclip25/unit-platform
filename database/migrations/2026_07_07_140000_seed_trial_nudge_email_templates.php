<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $templates = [
            [
                'key'               => 'ava_trial_halfway',
                'sequence'          => 'worker_lifecycle',
                'audience'          => 'worker_specific',
                'worker_slug'       => 'ava',
                'label'             => 'AVA — Trial 50% Used',
                'description'       => 'Fires once when the tenant has used half their trial transactions. Encourages upgrade before the trial runs out.',
                'trigger_condition' => 'on_trial_halfway',
                'day_offset'        => null,
                'delay_hours'       => null,
                'trigger_state'     => null,
                'subject'           => 'Ava is halfway through her trial — here\'s what she\'s done',
                'body'              => "Hi {name},\n\nAva has processed {used} of your {limit} trial transactions.\n\nHere's what that means: every renewal email she handled is one you didn't have to write yourself. Every draft is ready and waiting for your approval.\n\nWhen the trial ends, Ava stops.\n\nIf you want her to keep going — and keep the drafts coming — now's a good time to subscribe.\n\n{app_url}/billing\n\nAny questions before you decide? Just reply to this email.\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'active'            => true,
                'sort_order'        => 15,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'key'               => 'ava_trial_exhausted',
                'sequence'          => 'worker_lifecycle',
                'audience'          => 'worker_specific',
                'worker_slug'       => 'ava',
                'label'             => 'AVA — Trial Exhausted',
                'description'       => 'Fires once when the tenant\'s trial transactions are fully used or expired. Drives conversion to a paid plan.',
                'trigger_condition' => 'on_trial_exhausted',
                'day_offset'        => null,
                'delay_hours'       => null,
                'trigger_state'     => null,
                'subject'           => 'Ava has used all her trial transactions',
                'body'              => "Hi {name},\n\nAva has processed all {limit} of your trial transactions.\n\nShe's done exactly what she was built to do — but she needs a subscription to keep going.\n\nWithout one, Ava will stop monitoring your inbox and no new drafts will be prepared.\n\nSubscribe now to keep Ava working:\n{app_url}/billing\n\nIf you have questions about plans or pricing, reply to this email and I'll help you choose the right one.\n\nFranklin at UNIT",
                'from_name'         => 'Franklin at UNIT',
                'active'            => true,
                'sort_order'        => 16,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ];

        foreach ($templates as $tpl) {
            if (!DB::table('platform_email_templates')->where('key', $tpl['key'])->exists()) {
                DB::table('platform_email_templates')->insert($tpl);
            }
        }
    }

    public function down(): void
    {
        DB::table('platform_email_templates')
            ->whereIn('key', ['ava_trial_halfway', 'ava_trial_exhausted'])
            ->delete();
    }
};

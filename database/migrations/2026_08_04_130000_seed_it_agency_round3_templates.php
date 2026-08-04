<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Category-specific round 3 templates for the it_agency persona — the
 * founding/primary use case (see AVA.md). Same reasoning as the
 * insurance/compliance round 3 migration: the generic template is honest
 * but vague, and each of these 4 categories has a genuinely different
 * real consequence worth naming directly.
 *
 * SaaS Renewal deliberately does NOT get the same "URGENT" framing as the
 * other three — its round 1 template already establishes that renewal is
 * typically automatic ("No action is required unless you'd like to make
 * changes"), so manufacturing outage urgency for it would be dishonest.
 * Its round 3 stays true to what's actually true: today is the last
 * chance to change or cancel before the charge processes, not a service
 * interruption risk.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $rows = [
            [
                'category' => 'SSL Expiry',
                'name'     => 'SSL Expiry — round 3 (urgent)',
                'subject_template' => 'URGENT: SSL certificate for {{asset}} expires today',
                'body_template'    => "Hi {{contact_first_name}},\n\nURGENT — the SSL certificate for {{asset}} expires today. Once it expires, visitors will see security warnings and may be blocked from accessing the site entirely, which can seriously affect trust and traffic. Please confirm approval to renew as soon as possible.\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'category' => 'Domain Renewal',
                'name'     => 'Domain Renewal — round 3 (urgent)',
                'subject_template' => 'URGENT: {{asset}} expires today',
                'body_template'    => "Hi {{contact_first_name}},\n\nURGENT — {{asset}} expires today. If it isn't renewed, the website and any associated email may stop working, and the domain risks being lost entirely if it isn't renewed soon after. Please confirm as soon as possible so we can process the renewal.\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'category' => 'Hosting Invoice',
                'name'     => 'Hosting Invoice — round 3 (urgent)',
                'subject_template' => 'URGENT: Hosting invoice for {{asset}} is due today',
                'body_template'    => "Hi {{contact_first_name}},\n\nURGENT — the hosting invoice for {{asset}} is due today. Non-payment may result in the hosting account being suspended, taking the site offline. Please arrange payment as soon as possible to avoid any interruption.\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'category' => 'SaaS Renewal',
                'name'     => 'SaaS Renewal — round 3 (final chance to change)',
                'subject_template' => 'Final reminder: {{asset}} renews today',
                'body_template'    => "Hi {{contact_first_name}},\n\nFinal reminder — your {{asset}} subscription renews today. If you'd like to make any changes to your plan or cancel before the renewal processes, today is the last chance to do so. No action is needed if you're happy to continue as-is.\n\nBest regards,\n{{sender_name}}",
            ],
        ];

        foreach ($rows as $row) {
            DB::table('email_templates')->insert(array_merge([
                'user_id'           => null,
                'worker_slug'       => 'ava',
                'stage_key'         => null,
                'tone'              => 'Professional, urgent',
                'cadence_round'     => 3,
                'approval_required' => true,
                'is_default'        => true,
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ], $row));
        }
    }

    public function down(): void
    {
        DB::table('email_templates')
            ->whereNull('user_id')
            ->where('cadence_round', 3)
            ->whereIn('category', ['SSL Expiry', 'Domain Renewal', 'Hosting Invoice', 'SaaS Renewal'])
            ->delete();
    }
};

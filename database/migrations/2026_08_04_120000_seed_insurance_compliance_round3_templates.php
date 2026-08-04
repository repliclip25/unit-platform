<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Category-specific round 3 (0-days-out, final) templates for the
 * insurance_broker and compliance personas — the generic round 3
 * template ("service may be interrupted or disconnected") is honest but
 * vague for these categories, where what's actually at stake is coverage
 * lapsing, a carrier declining renewal, a license/permit/certification
 * expiring, or a regulatory deadline — each with different real
 * consequences worth naming directly rather than a generic pipeline
 * placeholder ("service") standing in for all nine of them.
 *
 * Matched by TemplateResolver::resolve()'s existing precedence — a
 * category + exact round match (step 3) beats the round-specific,
 * category-agnostic generic template (step 4) added earlier. Rounds 1
 * and 2 for these categories are untouched; only round 3 gets its own
 * voice here, since that's the one where the generic wording reads
 * weakest against real stakes.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $rows = [
            // ── insurance_broker (second person, {{asset}} = the policy —
            //    matches round 1's own established style for this category) ──
            [
                'category' => 'Policy Renewal',
                'name'     => 'Policy Renewal — round 3 (urgent)',
                'subject_template' => 'URGENT: Your policy, {{asset}}, expires today',
                'body_template'    => "Hi {{contact_first_name}},\n\nURGENT — your policy, {{asset}}, expires today. If it isn't renewed, coverage lapses immediately, leaving you exposed with no protection in place. Please confirm as soon as possible so we can process the renewal without a gap in coverage.\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'category' => 'Policy Lapse Warning',
                'name'     => 'Policy Lapse Warning — round 3 (urgent)',
                'subject_template' => 'FINAL NOTICE: Your policy, {{asset}}, lapses today',
                'body_template'    => "Hi {{contact_first_name}},\n\nFINAL NOTICE — this is the last reminder before your policy, {{asset}}, officially lapses today. Once lapsed, reinstating coverage may require a new application and could affect future rates. Please confirm immediately to avoid the lapse.\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'category' => 'Premium Payment',
                'name'     => 'Premium Payment — round 3 (urgent)',
                'subject_template' => 'URGENT: Premium payment for {{asset}} due today',
                'body_template'    => "Hi {{contact_first_name}},\n\nURGENT — the premium for {{asset}} is due today. Non-payment by end of day risks cancellation for non-payment, which can also affect future insurability. Please confirm payment as soon as possible.\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'category' => 'Carrier Non-Renewal',
                'name'     => 'Carrier Non-Renewal — round 3 (urgent)',
                'subject_template' => 'URGENT: Your coverage under {{asset}} ends today',
                'body_template'    => "Hi {{contact_first_name}},\n\nURGENT — the carrier has declined to renew {{asset}}, and your current coverage ends today. Without a replacement policy in place, you'll have a gap in coverage starting tomorrow. Please confirm how you'd like to proceed today.\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'category' => 'Coverage Change',
                'name'     => 'Coverage Change — round 3 (urgent)',
                'subject_template' => 'URGENT: Confirm the coverage change on {{asset}} today',
                'body_template'    => "Hi {{contact_first_name}},\n\nURGENT — the pending coverage change on {{asset}} needs to be confirmed today, or the policy will continue under its current terms, which may no longer match what's actually needed. Please confirm as soon as possible.\n\nBest regards,\n{{sender_name}}",
            ],

            // ── compliance ───────────────────────────────────────────────
            [
                'category' => 'License Renewal',
                'name'     => 'License Renewal — round 3 (urgent)',
                'subject_template' => 'URGENT: {{asset}} license expires today',
                'body_template'    => "Hi {{contact_first_name}},\n\nURGENT — the {{asset}} license expires today. Operating without a valid license after today may violate licensing requirements and carry penalties. Please confirm renewal status as soon as possible.\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'category' => 'Permit Renewal',
                'name'     => 'Permit Renewal — round 3 (urgent)',
                'subject_template' => 'URGENT: {{asset}} permit expires today — work may need to stop',
                'body_template'    => "Hi {{contact_first_name}},\n\nURGENT — the {{asset}} permit expires today. Continuing work without a valid permit after today may result in a stop-work order or fines. Please confirm renewal status as soon as possible.\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'category' => 'Certification Renewal',
                'name'     => 'Certification Renewal — round 3 (urgent)',
                'subject_template' => 'URGENT: {{asset}} certification expires today',
                'body_template'    => "Hi {{contact_first_name}},\n\nURGENT — the {{asset}} certification expires today. Once expired, credentials or eligibility tied to this certification may lapse until it's renewed. Please confirm status as soon as possible.\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'category' => 'Regulatory Notice',
                'name'     => 'Regulatory Notice — round 3 (urgent)',
                'subject_template' => 'URGENT: Regulatory deadline for {{asset}} is today',
                'body_template'    => "Hi {{contact_first_name}},\n\nURGENT — the regulatory deadline for {{asset}} is today. Missing this deadline may result in a compliance violation and possible penalties. Please confirm this has been addressed as soon as possible.\n\nBest regards,\n{{sender_name}}",
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
            ->whereIn('category', [
                'Policy Renewal', 'Policy Lapse Warning', 'Premium Payment', 'Carrier Non-Renewal', 'Coverage Change',
                'License Renewal', 'Permit Renewal', 'Certification Renewal', 'Regulatory Notice',
            ])
            ->delete();
    }
};

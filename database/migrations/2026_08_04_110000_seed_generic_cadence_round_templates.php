<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Every one of AVA's ~20 category default templates (Domain Renewal, SSL
 * Expiry, Hosting Invoice, ...) has cadence_round = null — TemplateResolver
 * supports round-specific escalation, but no round-2/round-3 content was
 * ever actually written, so all 3 client-facing reminder rounds read
 * word-for-word identical regardless of category. These two rows are the
 * fix: category-agnostic (category = '', matching the existing convention
 * for non-category templates — see the stage-message seed migration this
 * mirrors), matched by TemplateResolver's new round-specific-any-category
 * step. They land between "category + exact round" (still wins if a
 * category ever gets its own escalation later) and "category, no round"
 * (the old always-identical fallback) in precedence.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $rows = [
            [
                'cadence_round'     => 2,
                'name'              => 'Renewal reminder — round 2 (generic escalation)',
                'subject_template'  => 'Action needed: {{asset}} renews in 15 days',
                'body_template'     => "Hi {{contact_first_name}},\n\nJust a follow-up — {{asset}} is due for renewal in 15 days ({{due_date}}). Please confirm you'd like us to proceed with the renewal so there's no interruption to service.\n\nBest regards,\n{{sender_name}}",
            ],
            [
                'cadence_round'     => 3,
                'name'              => 'Renewal reminder — round 3 (generic escalation)',
                'subject_template'  => 'URGENT: {{asset}} renewal is due today',
                'body_template'     => "Hi {{contact_first_name}},\n\nURGENT — {{asset}} is due for renewal today ({{due_date}}). If this isn't renewed, the service may be interrupted or disconnected. Please confirm as soon as possible so we can proceed without a lapse.\n\nBest regards,\n{{sender_name}}",
            ],
        ];

        foreach ($rows as $row) {
            DB::table('email_templates')->insert(array_merge([
                'user_id'           => null,
                'worker_slug'       => 'ava',
                'category'          => '',
                'stage_key'         => null,
                'tone'              => '',
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
            ->where('category', '')
            ->whereIn('cadence_round', [2, 3])
            ->delete();
    }
};

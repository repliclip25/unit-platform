<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // Null = a category-scoped client-facing draft (existing rows,
            // unchanged). Set = a "system message" template scoped to a
            // pipeline stage instead of a renewal category — the tenant
            // reminders/nudges/closing notice that were hardcoded PHP
            // strings until now. `tone` doubles as the escalation tier
            // (gentle/direct/urgent) for stages that have one; null tone
            // means the template applies regardless of attempt number.
            $table->string('stage_key')->nullable()->after('category');
        });

        $now = now();
        $rows = [
            // ── confirm_payment (PaymentReminderJob) — tenant-facing ──────
            ['stage_key' => 'confirm_payment', 'tone' => 'gentle', 'name' => 'Payment reminder — gentle',
                'subject_template' => 'Confirm payment for {{asset}}?',
                'body_template'    => "Hi,\n\nJust checking in — has payment for the {{asset}} renewal gone through? No rush, just let me know when it's done so I can close this out.\n\n— AVA"],
            ['stage_key' => 'confirm_payment', 'tone' => 'direct', 'name' => 'Payment reminder — direct',
                'subject_template' => 'Following up — confirm payment for {{asset}}',
                'body_template'    => "Hi,\n\nFollowing up again — this renewal is still waiting on payment confirmation. {{asset}} will lapse if this isn't confirmed soon. Please confirm in UNIT when it's done.\n\n— AVA"],
            ['stage_key' => 'confirm_payment', 'tone' => 'urgent', 'name' => 'Payment reminder — urgent',
                'subject_template' => 'URGENT — confirm payment for {{asset}}',
                'body_template'    => "URGENT — {{asset}} renewal is still unconfirmed after multiple reminders. This needs your attention today to avoid a lapse. Confirm payment or cancel the renewal in UNIT.\n\n— AVA"],

            // ── human_decide (ApprovalReminderJob) — tenant-facing ────────
            ['stage_key' => 'human_decide', 'tone' => 'gentle', 'name' => 'Approval reminder — gentle',
                'subject_template' => 'A draft is waiting on you — {{asset}}',
                'body_template'    => "Hi,\n\nI drafted a renewal reply for {{asset}} and it's sitting in your Gmail drafts, ready to review. No rush — just approve it when you get a chance.\n\n— AVA"],
            ['stage_key' => 'human_decide', 'tone' => 'direct', 'name' => 'Approval reminder — direct',
                'subject_template' => 'Still waiting — approve the {{asset}} draft',
                'body_template'    => "Hi,\n\nFollowing up — the draft for {{asset}} is still unapproved. It won't send until you review it in UNIT or Gmail.\n\n— AVA"],
            ['stage_key' => 'human_decide', 'tone' => 'urgent', 'name' => 'Approval reminder — urgent',
                'subject_template' => 'URGENT — {{asset}} draft still needs your approval',
                'body_template'    => "URGENT — the renewal draft for {{asset}} has been waiting several days with no response. Please review and approve it in UNIT today.\n\n— AVA"],

            // ── request_invoice_nudge (InvoiceNudgeJob) — tenant-facing ───
            ['stage_key' => 'request_invoice_nudge', 'tone' => 'gentle', 'name' => 'Invoice nudge — gentle',
                'subject_template' => 'Got an invoice for {{asset}}?',
                'body_template'    => "Hi,\n\nIf you have an invoice for the {{asset}} renewal, attach it in UNIT whenever it's convenient — no rush, this won't hold anything up.\n\n— AVA"],
            ['stage_key' => 'request_invoice_nudge', 'tone' => 'direct', 'name' => 'Invoice nudge — direct',
                'subject_template' => 'Still no invoice attached — {{asset}}',
                'body_template'    => "Hi,\n\nFollowing up — still no invoice attached for {{asset}}. If one exists, attach it in UNIT so it's on file with the renewal.\n\n— AVA"],
            ['stage_key' => 'request_invoice_nudge', 'tone' => 'urgent', 'name' => 'Invoice nudge — urgent',
                'subject_template' => 'Last check — invoice for {{asset}}?',
                'body_template'    => "Hi,\n\nOne more check-in — if there's an invoice for {{asset}}, attach it in UNIT. If not, no action needed.\n\n— AVA"],

            // ── request_invoice_followup (InvoiceFollowUpJob) — client-facing, no tone escalation ──
            ['stage_key' => 'request_invoice_followup', 'tone' => '', 'name' => 'Invoice follow-up to client',
                'subject_template' => 'Following up — invoice for {{asset}}',
                'body_template'    => "Hi,\n\nFollowing up on the invoice sent for {{asset}}. Let us know if you need anything further.\n\nBest regards,\n{{sender_name}}"],

            // ── notify_stakeholders (NotifyStakeholdersJob) — tenant-facing, no tone escalation ──
            ['stage_key' => 'notify_stakeholders', 'tone' => '', 'name' => 'Renewal complete notice',
                'subject_template' => 'Renewal complete — {{asset}}',
                'body_template'    => "Hi,\n\nThe renewal for {{asset}}{{client_suffix}} is complete. The next cycle is already being watched.\n\n— AVA"],
        ];

        foreach ($rows as $row) {
            DB::table('email_templates')->insert(array_merge([
                'user_id'           => null,
                'worker_slug'       => 'ava',
                'category'          => '',
                'cadence_round'     => null,
                'approval_required' => false,
                'is_default'        => true,
                'active'            => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ], $row));
        }
    }

    public function down(): void
    {
        DB::table('email_templates')->whereNotNull('stage_key')->whereNull('user_id')->delete();
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn(['stage_key']);
        });
    }
};

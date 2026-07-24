<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name'              => 'SSL Expiry — Approval Request',
                'category'          => 'SSL Expiry',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Action Required: SSL Certificate for {{asset}} expires {{due_date}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nI hope you're doing well. I'm reaching out regarding the upcoming SSL certificate expiration for {{asset}}.\n\nExpiry Date: {{due_date}}\n\nTo avoid any service disruption or security warnings for your visitors, we'll need to initiate the renewal process shortly. As per your preference, we'd like your approval before proceeding.\n\nCould you please confirm your approval to move forward with the SSL renewal at your earliest convenience?\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Domain Renewal — Reminder',
                'category'          => 'Domain Renewal',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Domain Renewal Reminder: {{asset}} expires {{due_date}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nThis is a friendly reminder that the domain {{asset}} is due for renewal on {{due_date}}.\n\nPlease confirm if you'd like us to proceed with the renewal to ensure there's no disruption to your online presence.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Hosting Invoice — Payment Reminder',
                'category'          => 'Hosting Invoice',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Invoice Due: Hosting for {{asset}} — {{due_date}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nA hosting invoice for {{asset}} is due on {{due_date}}.\n\nPlease arrange payment at your earliest convenience to avoid any service interruption.\n\nIf you have any questions regarding this invoice, don't hesitate to reach out.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'SaaS Renewal — Summary',
                'category'          => 'SaaS Renewal',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'SaaS Renewal Notice: {{asset}} renews {{due_date}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nJust a heads-up that your {{asset}} subscription is set to renew on {{due_date}}.\n\nNo action is required unless you'd like to make changes to your plan. Please let us know if you have any questions.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => false,
                'is_default'        => true,
            ],
            [
                'name'              => 'Failed Payment — Urgent',
                'category'          => 'Failed Payment',
                'tone'              => 'Professional, urgent',
                'subject_template'  => 'URGENT: Payment Failed for {{asset}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nWe've been notified that a payment for {{asset}} has failed.\n\nThis requires immediate attention to avoid service interruption. Please update your payment information or contact us as soon as possible.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Unknown Item — Review Required',
                'category'          => 'Other',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Action May Be Required: {{asset}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nI'm reaching out regarding a recent notice we received related to {{asset}}.\n\nCould you please review and advise on the best course of action?\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],

            // ── Insurance Broker persona ─────────────────────────────────
            [
                'name'              => 'Policy Renewal — Reminder',
                'category'          => 'Policy Renewal',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Policy Renewal Reminder: {{asset}} expires {{due_date}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nThis is a friendly reminder that your policy, {{asset}}, is due for renewal on {{due_date}}.\n\nPlease confirm the coverage and premium remain correct, and let us know if you'd like us to proceed with the renewal.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Policy Lapse Warning — Urgent',
                'category'          => 'Policy Lapse Warning',
                'tone'              => 'Professional, urgent',
                'subject_template'  => 'URGENT: {{asset}} expires {{due_date}} — action needed to avoid a coverage gap',
                'body_template'     => "Hi {{contact_first_name}},\n\n{{asset}} is set to expire on {{due_date}}. Without renewal before that date, coverage will lapse.\n\nPlease confirm as soon as possible so we can process the renewal and avoid any gap in coverage.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Premium Payment — Reminder',
                'category'          => 'Premium Payment',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Premium Payment Due: {{asset}} — {{due_date}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nA premium payment for {{asset}} is due on {{due_date}}.\n\nPlease arrange payment at your earliest convenience to keep the policy in good standing.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Carrier Non-Renewal — Review Required',
                'category'          => 'Carrier Non-Renewal',
                'tone'              => 'Professional, urgent',
                'subject_template'  => 'Carrier notice — {{asset}} will not be renewed',
                'body_template'     => "Hi {{contact_first_name}},\n\nWe've received a non-renewal notice from the carrier for {{asset}}.\n\nWe're reviewing replacement options and will follow up with next steps shortly — no action is needed from you yet.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Coverage Change — Summary',
                'category'          => 'Coverage Change',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Coverage update on {{asset}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nThere's an update to the coverage or terms on {{asset}}. We're reviewing the change and will confirm whether anything is needed from you.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => false,
                'is_default'        => true,
            ],

            // ── Compliance / Licensing persona ────────────────────────────
            [
                'name'              => 'License Renewal — Reminder',
                'category'          => 'License Renewal',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'License Renewal Reminder: {{asset}} expires {{due_date}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nThis is a reminder that {{asset}} is due for renewal on {{due_date}}.\n\nPlease confirm if you'd like us to proceed with the renewal so there's no gap in your ability to operate.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Permit Renewal — Reminder',
                'category'          => 'Permit Renewal',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Permit Renewal Reminder: {{asset}} expires {{due_date}}',
                'body_template'     => "Hi {{contact_first_name}},\n\n{{asset}} is due for renewal on {{due_date}}.\n\nPlease confirm so we can proceed and keep operations uninterrupted.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Certification Renewal — Action Plan',
                'category'          => 'Certification Renewal',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Certification Renewal: {{asset}} — due {{due_date}}',
                'body_template'     => "Hi {{contact_first_name}},\n\n{{asset}} needs to be renewed or re-examined by {{due_date}}.\n\nPlease let us know how you'd like to proceed so we can plan around any exam or submission requirements.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Regulatory Notice — Escalation',
                'category'          => 'Regulatory Notice',
                'tone'              => 'Professional, urgent',
                'subject_template'  => 'Regulatory notice received — {{asset}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nWe've received a regulatory or enforcement notice related to {{asset}}.\n\nThis has been escalated for review before any response is sent — we'll follow up with next steps shortly.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Fee Payment — Reminder',
                'category'          => 'Fee Payment',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Fee Payment Due: {{asset}} — {{due_date}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nA renewal fee for {{asset}} is due on {{due_date}}.\n\nPlease arrange payment at your earliest convenience.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],

            // ── Other persona (contracts, memberships, warranties, etc.) ──
            [
                'name'              => 'Renewal Notice — Reminder',
                'category'          => 'Renewal Notice',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Renewal Reminder: {{asset}} — due {{due_date}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nThis is a reminder that {{asset}} is due for renewal on {{due_date}}.\n\nPlease confirm if you'd like us to proceed.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Payment Overdue — Reminder',
                'category'          => 'Payment Overdue',
                'tone'              => 'Professional, concise',
                'subject_template'  => 'Payment Overdue: {{asset}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nA payment for {{asset}} is now overdue.\n\nPlease arrange payment at your earliest convenience to avoid any disruption.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
            [
                'name'              => 'Cancellation Notice — Urgent',
                'category'          => 'Cancellation Notice',
                'tone'              => 'Professional, urgent',
                'subject_template'  => 'URGENT: Cancellation notice for {{asset}}',
                'body_template'     => "Hi {{contact_first_name}},\n\nWe've received a cancellation or lapse notice for {{asset}}.\n\nThis needs prompt attention — please let us know how you'd like to proceed as soon as possible.\n\nBest regards,\n{{sender_name}}",
                'approval_required' => true,
                'is_default'        => true,
            ],
        ];

        foreach ($templates as $template) {
            // No unique constraint on `category` in the schema, so check
            // explicitly rather than relying on insertOrIgnore to dedupe —
            // safe to re-run this seeder without creating duplicate rows.
            $exists = DB::table('email_templates')
                ->whereNull('user_id')
                ->where('category', $template['category'])
                ->exists();
            if ($exists) continue;

            DB::table('email_templates')->insert(array_merge($template, [
                'user_id'    => null, // platform defaults belong to no user
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

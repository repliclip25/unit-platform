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
        ];

        foreach ($templates as $template) {
            DB::table('email_templates')->insertOrIgnore(array_merge($template, [
                'user_id'    => null, // platform defaults belong to no user
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

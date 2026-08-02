<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // notify_stakeholders tells the TENANT a renewal closed out;
            // this is the first message that tells the actual CLIENT — with
            // the next renewal date and when they'll hear from AVA again.
            // Brand new capability, so gated OFF by default (see the
            // 'notify_customer' message gate) — a tenant opts in explicitly
            // rather than every existing deployment suddenly emailing
            // clients it never used to.
            $table->json('notify_customer_output')->nullable()->after('notify_output');
        });

        DB::table('email_templates')->insert([
            'user_id'           => null,
            'worker_slug'       => 'ava',
            'category'          => '',
            'stage_key'         => 'notify_customer',
            'tone'              => '',
            'cadence_round'     => null,
            'name'              => 'Renewal complete notice to client',
            'subject_template'  => 'Your renewal for {{asset}} is complete',
            'body_template'     => "Hi,\n\nThis confirms the renewal for {{asset}} is complete. Your next renewal is due {{next_renewal_date}} — we'll reach out again around {{next_reminder_date}} to start that cycle.\n\nBest regards,\n{{sender_name}}",
            'approval_required' => false,
            'is_default'        => true,
            'active'            => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')->where('stage_key', 'notify_customer')->whereNull('user_id')->delete();
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['notify_customer_output']);
        });
    }
};

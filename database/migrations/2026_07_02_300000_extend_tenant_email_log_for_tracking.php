<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_email_log', function (Blueprint $table) {
            $table->string('resend_id')->nullable()->after('template_key');
            $table->string('to_email')->nullable()->after('resend_id');
            $table->string('subject')->nullable()->after('to_email');
            $table->timestamp('opened_at')->nullable()->after('sent_at');
            $table->timestamp('clicked_at')->nullable()->after('opened_at');
            $table->string('status')->default('sent')->after('clicked_at'); // sent, opened, clicked, bounced, failed
        });
    }

    public function down(): void
    {
        Schema::table('tenant_email_log', function (Blueprint $table) {
            $table->dropColumn(['resend_id', 'to_email', 'subject', 'opened_at', 'clicked_at', 'status']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add hour-based delay support to email templates
        // delay_hours replaces day_offset for abandonment emails (1hr, 24hr etc.)
        // day_offset stays for newsletter/legacy day-based sequences
        Schema::table('platform_email_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('platform_email_templates', 'delay_hours')) {
                $table->unsignedSmallInteger('delay_hours')->nullable()->after('day_offset');
            }
        });

        // Track when each onboarding milestone was first hit per user
        // Used by the abandonment job to calculate elapsed time since user got stuck
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'onboarding_gmail_at')) {
                $table->timestamp('onboarding_gmail_at')->nullable()->after('onboarding_completed_at');
            }
            if (!Schema::hasColumn('users', 'onboarding_clients_at')) {
                $table->timestamp('onboarding_clients_at')->nullable()->after('onboarding_gmail_at');
            }
            if (!Schema::hasColumn('users', 'onboarding_fasttrack_at')) {
                $table->timestamp('onboarding_fasttrack_at')->nullable()->after('onboarding_clients_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('platform_email_templates', function (Blueprint $table) {
            $table->dropColumn('delay_hours');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['onboarding_gmail_at', 'onboarding_clients_at', 'onboarding_fasttrack_at']);
        });
    }
};

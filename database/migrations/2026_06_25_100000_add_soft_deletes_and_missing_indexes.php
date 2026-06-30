<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Note: deleted_at columns already exist on transactions, clients, contacts, assets
        // from a prior migration — only indexes are added here.

        // ── Missing indexes ─────────────────────────────────────────────────
        // Skip on SQLite (tests)
        if (DB::getDriverName() === 'sqlite') return;

        // email_templates(user_id, worker_slug, active) — SelectTemplateJob scans this per TX
        Schema::table('email_templates', function (Blueprint $table) {
            $table->index(['user_id', 'worker_slug', 'active'], 'idx_tpl_user_slug_active');
        });

        // ava_rules(deployment_id) — MemoryLookupJob scans this per TX
        Schema::table('ava_rules', function (Blueprint $table) {
            $table->index(['deployment_id', 'active'], 'idx_rules_deployment_active');
        });

        // user_gmail_credentials(user_id) — looked up on every webhook
        Schema::table('user_gmail_credentials', function (Blueprint $table) {
            $table->index('user_id', 'idx_gmail_cred_user');
        });

        // referral_credits — queried by referrer_id and influencer_id frequently
        Schema::table('referral_credits', function (Blueprint $table) {
            $table->index(['referrer_id', 'event'], 'idx_refcredit_referrer_event');
            $table->index(['influencer_id', 'event'], 'idx_refcredit_influencer_event');
        });

        // platform_email_templates(sequence, active) — loaded on every OnboardingSequenceJob run
        Schema::table('platform_email_templates', function (Blueprint $table) {
            $table->index(['sequence', 'active'], 'idx_pet_sequence_active');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') return;

        Schema::table('email_templates',          fn($t) => $t->dropIndex('idx_tpl_user_slug_active'));
        Schema::table('ava_rules',                fn($t) => $t->dropIndex('idx_rules_deployment_active'));
        Schema::table('user_gmail_credentials',   fn($t) => $t->dropIndex('idx_gmail_cred_user'));
        Schema::table('referral_credits',         fn($t) => $t->dropIndex('idx_refcredit_referrer_event') && $t->dropIndex('idx_refcredit_influencer_event'));
        Schema::table('platform_email_templates', fn($t) => $t->dropIndex('idx_pet_sequence_active'));
    }
};

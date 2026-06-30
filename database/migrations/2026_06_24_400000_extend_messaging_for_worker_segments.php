<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Track which worker a tenant first deployed/interacted with
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_worker_slug')->nullable()->after('onboarding_skipped');
            $table->timestamp('first_worker_at')->nullable()->after('first_worker_slug');
        });

        // Extend email templates: audience targeting + worker specificity
        Schema::table('platform_email_templates', function (Blueprint $table) {
            // audience: all | no_worker | worker_specific
            $table->string('audience')->default('all')->after('sequence');
            // null = applies to all workers, otherwise e.g. 'ava'
            $table->string('worker_slug')->nullable()->after('audience');
            // for newsletter: topic/focus for AI rewrite context
            $table->string('topic')->nullable()->after('worker_slug');
        });

        // Prevent double-sending: track which emails each user already received
        Schema::create('tenant_email_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('template_key');
            $table->timestamp('sent_at')->useCurrent();

            $table->index(['user_id', 'template_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_email_log');

        Schema::table('platform_email_templates', function (Blueprint $table) {
            $table->dropColumn(['audience', 'worker_slug', 'topic']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_worker_slug', 'first_worker_at']);
        });
    }
};

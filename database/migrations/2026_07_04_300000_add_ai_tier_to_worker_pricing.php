<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->string('ai_tier', 20)->default('economy')->after('billing_mode');
            // Model IDs used at each pipeline stage
            $table->string('classify_model', 60)->default('claude-haiku-4-5-20251001')->after('ai_tier');
            $table->string('draft_model', 60)->default('claude-haiku-4-5-20251001')->after('classify_model');
            // After this many transactions/mo the draft model downgrades to classify_model.
            // NULL = no threshold (draft model never downgrades).
            $table->unsignedInteger('draft_model_threshold')->nullable()->after('draft_model');
        });

        // Backfill existing AVA plans with sensible defaults
        DB::table('worker_pricing')->where('worker_slug', 'ava')->where('plan_slug', 'starter')->update([
            'ai_tier'               => 'economy',
            'classify_model'        => 'claude-haiku-4-5-20251001',
            'draft_model'           => 'claude-haiku-4-5-20251001',
            'draft_model_threshold' => null,
        ]);

        DB::table('worker_pricing')->where('worker_slug', 'ava')->where('plan_slug', 'pro')->update([
            'ai_tier'               => 'standard',
            'classify_model'        => 'claude-haiku-4-5-20251001',
            'draft_model'           => 'claude-sonnet-4-6',
            'draft_model_threshold' => 500, // downgrade to Haiku after 500 emails/mo
        ]);

        DB::table('worker_pricing')->where('worker_slug', 'ava')->where('plan_slug', 'enterprise')->update([
            'ai_tier'               => 'premium',
            'classify_model'        => 'claude-sonnet-4-6',
            'draft_model'           => 'claude-sonnet-4-6',
            'draft_model_threshold' => null,
        ]);
    }

    public function down(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->dropColumn(['ai_tier', 'classify_model', 'draft_model', 'draft_model_threshold']);
        });
    }
};

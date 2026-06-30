<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── worker_registry additions ────────────────────────────────────────

        Schema::table('worker_registry', function (Blueprint $table) {
            // credential was a single object — now always an array of credential objects
            // (column already exists as JSON, shape change only — no ALTER needed)

            // Events this worker subscribes to from other workers
            // [{event, from_worker, description, handler_stage}]
            $table->json('subscriptions')->nullable()->after('notifications');

            // Changelog across versions with upgrade instructions
            // [{version, date, notes, breaking, breaking_reason, upgrade_steps[]}]
            $table->json('version_changelog')->nullable()->after('subscriptions');
        });

        // ── deployment_prompt_overrides ──────────────────────────────────────
        // Allows per-deployment prompt tuning without touching the worker PHP class.
        // ClaudeService checks this table before using the contract default.

        Schema::create('deployment_prompt_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deployment_id');
            $table->string('worker_slug');
            $table->string('stage_key');               // matches pipeline stage key e.g. 'classify'

            $table->text('system_prompt')->nullable();  // overrides prompts()[stage].system
            $table->text('user_prompt')->nullable();    // overrides prompts()[stage].user
            $table->string('model')->nullable();        // overrides prompts()[stage].model
            $table->unsignedInteger('max_tokens')->nullable(); // overrides prompts()[stage].max_tokens

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['deployment_id', 'stage_key']); // one override per stage per deployment
            $table->index('deployment_id');
        });
    }

    public function down(): void
    {
        Schema::table('worker_registry', function (Blueprint $table) {
            $table->dropColumn(['subscriptions', 'version_changelog']);
        });

        Schema::dropIfExists('deployment_prompt_overrides');
    }
};

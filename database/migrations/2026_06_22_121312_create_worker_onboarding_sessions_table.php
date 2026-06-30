<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('worker_onboarding_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('worker_slug');
            $table->foreignId('deployment_id')->nullable()->constrained('worker_deployments')->nullOnDelete();
            $table->string('status')->default('in_progress'); // in_progress, completed, skipped, abandoned
            $table->string('current_step')->default('welcome'); // welcome, credential, memory, fast_track
            $table->json('steps_data')->nullable();            // completed steps + any per-step metadata
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('skipped_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamps();

            // Only one active session per user at a time — enforced at app level
            $table->index(['user_id', 'status']);
            $table->index(['worker_slug', 'status']);         // for follow-up messaging queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_onboarding_sessions');
    }
};

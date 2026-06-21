<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Worker registry — every deployed worker is registered here
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();          // e.g. ava-renewal-coordinator
            $table->string('name');
            $table->string('category');                // e.g. Operations
            $table->string('version')->default('1.0');
            $table->enum('status', ['created', 'configured', 'connected', 'running', 'paused', 'archived'])->default('created');
            $table->json('manifest')->nullable();      // full worker DNA
            $table->timestamps();
        });

        // Every email/event that enters the platform becomes a transaction
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tx_id')->unique();         // e.g. TX-001
            $table->string('worker_slug');
            $table->enum('status', [
                'received', 'reading', 'classifying', 'memory_lookup',
                'logging', 'templating', 'drafting', 'draft_ready',
                'human_review', 'approved', 'sent', 'failed'
            ])->default('received');
            $table->string('category')->nullable();    // SSL Expiry, Domain Renewal, etc.
            $table->string('priority')->nullable();    // Low, Medium, High, Critical
            $table->json('raw_input')->nullable();     // original email payload
            $table->json('read_output')->nullable();
            $table->json('classify_output')->nullable();
            $table->json('memory_output')->nullable();
            $table->json('template_output')->nullable();
            $table->json('draft_output')->nullable();
            $table->string('gmail_draft_id')->nullable();
            $table->string('human_decision')->nullable();
            $table->text('human_notes')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamps();

            $table->foreign('worker_slug')->references('slug')->on('workers');
        });

        // Platform events — audit trail of everything that happens
        Schema::create('platform_events', function (Blueprint $table) {
            $table->id();
            $table->string('worker_slug');
            $table->string('tx_id')->nullable();
            $table->string('event');                   // e.g. draft_created, memory_matched
            $table->json('payload')->nullable();
            $table->enum('level', ['info', 'warning', 'error'])->default('info');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_events');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('workers');
    }
};

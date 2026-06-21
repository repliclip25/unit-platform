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
        // Allow multiple Gmail credentials per user — drop FK, drop unique, re-add FK
        Schema::table('user_gmail_credentials', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->string('label')->default('Primary Inbox')->after('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // A tenant's deployed worker instance
        Schema::create('worker_deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('worker_slug');              // e.g. 'ava'
            $table->string('name');                     // e.g. "AVA — Renewals"
            $table->enum('status', ['active', 'paused', 'stopped'])->default('active');
            $table->unsignedBigInteger('credential_id')->nullable();
            $table->json('config')->nullable();          // capture rules, scope, etc.
            $table->timestamps();
        });

        // AI usage metering per transaction
        Schema::create('usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('deployment_id')->nullable();
            $table->string('worker_slug');
            $table->string('tx_id')->nullable();
            $table->string('stage')->nullable();
            $table->unsignedInteger('tokens_input')->default(0);
            $table->unsignedInteger('tokens_output')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->timestamps();
        });

        // Track which deployment processed each transaction
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('deployment_id')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('deployment_id');
        });
        Schema::dropIfExists('usage_events');
        Schema::dropIfExists('worker_deployments');
        Schema::table('user_gmail_credentials', function (Blueprint $table) {
            $table->dropColumn('label');
            $table->unique('user_id');
        });
    }
};

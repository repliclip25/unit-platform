<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_stage_log', function (Blueprint $table) {
            $table->id();
            $table->string('tx_id', 100)->index();
            $table->unsignedBigInteger('deployment_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('worker_slug', 60)->nullable();
            $table->string('stage_key', 60);          // filter, read, classify, memory, log, template, draft, push
            $table->string('event', 20);               // started | completed | failed | skipped
            $table->unsignedInteger('duration_ms')->nullable(); // null on started, set on completed/failed
            $table->tinyInteger('attempt')->default(1);
            $table->string('error_summary', 500)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // Add current_stage to transactions for fast "where is this tx right now?" lookups
        if (!Schema::hasColumn('transactions', 'current_stage')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('current_stage', 60)->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_stage_log');
        if (Schema::hasColumn('transactions', 'current_stage')) {
            Schema::table('transactions', fn($t) => $t->dropColumn('current_stage'));
        }
    }
};

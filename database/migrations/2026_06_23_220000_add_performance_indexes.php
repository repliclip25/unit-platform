<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip on SQLite (tests) — SQLite handles indexes differently and these are for production MySQL performance
        if (DB::getDriverName() === 'sqlite') return;

        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['user_id', 'status'],      'idx_tx_user_status');
            $table->index(['user_id', 'created_at'],  'idx_tx_user_created');
            $table->index('status',                    'idx_tx_status');
        });

        Schema::table('usage_events', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'],  'idx_usage_user_created');
        });

        Schema::table('platform_usage_events', function (Blueprint $table) {
            $table->index('prompt_key',               'idx_pusage_prompt_key');
            $table->index('created_at',               'idx_pusage_created');
        });

        Schema::table('worker_deployments', function (Blueprint $table) {
            $table->index(['user_id', 'status'],      'idx_deploy_user_status');
            $table->index(['user_id', 'worker_slug'], 'idx_deploy_user_slug');
        });

        Schema::table('renewal_register', function (Blueprint $table) {
            $table->index(['user_id', 'status'],      'idx_register_user_status');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') return;

        Schema::table('transactions',        fn($t) => $t->dropIndex('idx_tx_user_status') && $t->dropIndex('idx_tx_user_created') && $t->dropIndex('idx_tx_status'));
        Schema::table('usage_events',        fn($t) => $t->dropIndex('idx_usage_user_created'));
        Schema::table('platform_usage_events', fn($t) => $t->dropIndex('idx_pusage_prompt_key') && $t->dropIndex('idx_pusage_created'));
        Schema::table('worker_deployments',  fn($t) => $t->dropIndex('idx_deploy_user_status') && $t->dropIndex('idx_deploy_user_slug'));
        Schema::table('renewal_register',    fn($t) => $t->dropIndex('idx_register_user_status'));
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── worker_registry: standardise lifecycle status + timestamps ─────
        Schema::table('worker_registry', function (Blueprint $table) {
            $table->timestamp('commissioned_at')->nullable()->after('published_at');
            $table->timestamp('decommissioned_at')->nullable()->after('commissioned_at');
        });

        // Rename 'published' → 'active' for existing rows
        DB::table('worker_registry')->where('status', 'published')->update(['status' => 'active']);

        // ── transactions: generic in-progress status + test flag ──────────
        // Add is_test column
        if (!Schema::hasColumn('transactions', 'is_test')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->boolean('is_test')->default(false)->after('current_stage');
            });
        }

        // Add 'processing' to the status enum (MySQL only — agnostic in-progress value)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM(
                'received','queued','ingesting','reading','classifying','memory_lookup',
                'logging','templating','drafting','pushing','processing',
                'draft_ready','human_review','approved','sent',
                'failed','blocked','dismissed','filtered_out','rejected'
            ) NOT NULL DEFAULT 'received'");
        }
    }

    public function down(): void
    {
        Schema::table('worker_registry', function (Blueprint $table) {
            $table->dropColumn(['commissioned_at', 'decommissioned_at']);
        });
        DB::table('worker_registry')->where('status', 'active')->update(['status' => 'published']);
        if (Schema::hasColumn('transactions', 'is_test')) {
            Schema::table('transactions', fn($t) => $t->dropColumn('is_test'));
        }
    }
};

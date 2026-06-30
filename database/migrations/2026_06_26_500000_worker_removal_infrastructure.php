<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Soft-delete columns ───────────────────────────────────────────────
        Schema::table('renewal_register', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('worker_deployments', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('deployment_billing', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('deployment_credentials', function (Blueprint $table) {
            $table->softDeletes();
        });
        // transactions already has deleted_at

        // ── worker_deployments: add 'removed' status ──────────────────────────
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE worker_deployments MODIFY COLUMN status ENUM('active','paused','stopped','decommissioned','removed') NOT NULL DEFAULT 'active'");
        }

        // ── users: testing_access flag ────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('testing_access')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('renewal_register',      fn($t) => $t->dropSoftDeletes());
        Schema::table('worker_deployments',    fn($t) => $t->dropSoftDeletes());
        Schema::table('deployment_billing',    fn($t) => $t->dropSoftDeletes());
        Schema::table('deployment_credentials',fn($t) => $t->dropSoftDeletes());

        if (DB::getDriverName() !== 'sqlite') {
            DB::table('worker_deployments')->where('status', 'removed')->update(['status' => 'decommissioned']);
            DB::statement("ALTER TABLE worker_deployments MODIFY COLUMN status ENUM('active','paused','stopped','decommissioned') NOT NULL DEFAULT 'active'");
        }

        Schema::table('users', fn($t) => $t->dropColumn('testing_access'));
    }
};

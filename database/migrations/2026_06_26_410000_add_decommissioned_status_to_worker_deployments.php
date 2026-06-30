<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE worker_deployments MODIFY COLUMN status ENUM('active','paused','stopped','decommissioned') NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::table('worker_deployments')->where('status', 'decommissioned')->update(['status' => 'stopped']);
            DB::statement("ALTER TABLE worker_deployments MODIFY COLUMN status ENUM('active','paused','stopped') NOT NULL DEFAULT 'active'");
        }
    }
};

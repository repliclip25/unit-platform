<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE deployment_billing MODIFY COLUMN status ENUM('trial','active','past_due','canceled','paused','decommissioned') NOT NULL DEFAULT 'trial'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::table('deployment_billing')->where('status', 'decommissioned')->update(['status' => 'canceled']);
            DB::statement("ALTER TABLE deployment_billing MODIFY COLUMN status ENUM('trial','active','past_due','canceled','paused') NOT NULL DEFAULT 'trial'");
        }
    }
};

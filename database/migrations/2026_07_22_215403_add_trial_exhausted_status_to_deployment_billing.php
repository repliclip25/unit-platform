<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // PolicyEngine::checkPolicies() writes status='trial_exhausted' when a
        // trial runs out (see PolicyEngine.php), but the enum never included
        // that value — the write silently failed inside a catch(\Throwable){},
        // so the exhausted-trial nudge email never fired and status stayed
        // 'trial' forever.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE deployment_billing MODIFY COLUMN status ENUM('trial','trial_exhausted','active','past_due','canceled','paused','decommissioned') NOT NULL DEFAULT 'trial'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::table('deployment_billing')->where('status', 'trial_exhausted')->update(['status' => 'trial']);
            DB::statement("ALTER TABLE deployment_billing MODIFY COLUMN status ENUM('trial','active','past_due','canceled','paused','decommissioned') NOT NULL DEFAULT 'trial'");
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deployment_billing', function (Blueprint $table) {
            // Fast Track and real transactions now share one pool
            // (trial_transactions_used/limit) instead of two disconnected
            // counters. fast_track_allocation is how much of that shared
            // pool is reserved for Fast Track specifically; fast_track_used
            // is the sub-counter checked against it. See PlatformDefaults.
            $table->unsignedInteger('fast_track_allocation')->default(10)->after('trial_transactions_limit');
            $table->unsignedInteger('fast_track_used')->default(0)->after('fast_track_allocation');
        });

        // Existing trial rows were sized without the Fast Track allocation —
        // bump their limit up by the allocation so this migration doesn't
        // retroactively shrink anyone's real trial headroom. Also carry over
        // whatever they'd already used via the old separate config counter,
        // so a tenant who'd already run Fast Track tests doesn't get a fresh
        // 10 for free while their real pool stays exhausted underneath it.
        DB::table('deployment_billing')
            ->where('status', 'trial')
            ->update(['trial_transactions_limit' => DB::raw('trial_transactions_limit + 10')]);

        $rows = DB::table('worker_deployments')->whereNotNull('config')->select('id', 'config')->get();
        foreach ($rows as $row) {
            $config = json_decode($row->config, true) ?: [];
            $ftUses = (int) ($config['fast_track_uses'] ?? 0);
            if ($ftUses > 0) {
                DB::table('deployment_billing')->where('deployment_id', $row->id)->update([
                    'fast_track_used' => $ftUses,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('deployment_billing', function (Blueprint $table) {
            $table->dropColumn(['fast_track_allocation', 'fast_track_used']);
        });
    }
};

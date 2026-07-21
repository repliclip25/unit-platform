<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * trial_transactions_used was incremented at transaction *creation* time,
 * so failed/blocked/dismissed/filtered-out attempts silently burned trial
 * quota alongside genuinely successful ones. Fixed going forward in
 * TransactionService/UnitPlatform (quota now charges only on first arrival
 * at a success status: draft_ready/approved/sent). This one-time backfill
 * recomputes existing deployments' counters from actual successful
 * transaction counts so no one is unfairly stuck trial-exhausted from the
 * old counting bug.
 */
return new class extends Migration
{
    public function up(): void
    {
        $successStatuses = ['draft_ready', 'approved', 'sent'];

        DB::table('deployment_billing')
            ->where('status', 'trial')
            ->orderBy('id')
            ->get(['deployment_id', 'user_id', 'worker_slug'])
            ->each(function ($billing) use ($successStatuses) {
                $actualUsed = DB::table('transactions')
                    ->where('deployment_id', $billing->deployment_id)
                    ->whereIn('status', $successStatuses)
                    ->count();

                DB::table('deployment_billing')
                    ->where('deployment_id', $billing->deployment_id)
                    ->update([
                        'trial_transactions_used' => $actualUsed,
                        'updated_at'              => now(),
                    ]);

                if ($billing->user_id && $billing->worker_slug) {
                    DB::table('user_worker_trial_ledger')
                        ->where('user_id', $billing->user_id)
                        ->where('worker_slug', $billing->worker_slug)
                        ->update([
                            'used'       => $actualUsed,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Not reversible — the pre-backfill (inflated) counts aren't recoverable.
    }
};

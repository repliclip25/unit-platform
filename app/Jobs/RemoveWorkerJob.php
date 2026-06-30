<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Soft-deletes all tenant data associated with a worker slug.
 * Runs in the background — sets lifecycle_status = 'removing' on dispatch,
 * then 'removed' on completion. All deletes are soft (deleted_at = now()).
 */
class RemoveWorkerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(public readonly string $slug, public readonly int $adminId) {}

    public function handle(): void
    {
        $now = now();
        Log::info("RemoveWorkerJob: starting removal for worker '{$this->slug}'", ['admin' => $this->adminId]);

        // ── 1. Collect all deployment IDs for this worker ─────────────────────
        $deploymentIds = DB::table('worker_deployments')
            ->where('worker_slug', $this->slug)
            ->pluck('id')
            ->toArray();

        // ── 2. Soft-delete deployment-level tables ────────────────────────────
        if (!empty($deploymentIds)) {
            DB::table('deployment_billing')
                ->whereIn('deployment_id', $deploymentIds)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now]);

            DB::table('deployment_credentials')
                ->whereIn('deployment_id', $deploymentIds)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now]);

            DB::table('email_templates')
                ->whereIn('deployment_id', $deploymentIds)
                ->update(['deleted_at' => $now]);

            DB::table('renewal_register')
                ->whereIn('deployment_id', $deploymentIds)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now]);
        }

        // ── 3. Soft-delete transactions ───────────────────────────────────────
        DB::table('transactions')
            ->where('worker_slug', $this->slug)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => $now]);

        // ── 4. Soft-delete transaction stage logs (via transaction IDs) ───────
        // These are operational logs — remove with the transactions
        DB::table('transaction_stage_log')
            ->whereIn('tx_id', function ($q) {
                $q->select('tx_id')->from('transactions')->where('worker_slug', $this->slug);
            })
            ->update(['deleted_at' => $now]);

        // ── 5. Soft-delete the deployments themselves, mark as removed ────────
        if (!empty($deploymentIds)) {
            DB::table('worker_deployments')
                ->whereIn('id', $deploymentIds)
                ->update(['status' => 'removed', 'deleted_at' => $now, 'updated_at' => $now]);
        }

        // ── 6. Mark removal complete in worker_registry ───────────────────────
        DB::table('worker_registry')->where('slug', $this->slug)->update([
            'lifecycle_status' => 'removed',
            'updated_at'       => $now,
        ]);

        // ── 7. Audit log ──────────────────────────────────────────────────────
        DB::table('platform_events')->insert([
            'user_id'     => $this->adminId,
            'worker_slug' => $this->slug,
            'tx_id'       => null,
            'event'       => 'worker_removed',
            'payload'     => json_encode([
                'slug'             => $this->slug,
                'deployment_count' => count($deploymentIds),
                'admin_id'         => $this->adminId,
            ]),
            'level'      => 'info',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Log::info("RemoveWorkerJob: completed for '{$this->slug}'", ['deployments_removed' => count($deploymentIds)]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("RemoveWorkerJob: failed for '{$this->slug}'", ['error' => $e->getMessage()]);
        // Roll back lifecycle_status to decommissioned so admin can retry
        DB::table('worker_registry')->where('slug', $this->slug)->update([
            'lifecycle_status' => 'decommissioned',
            'updated_at'       => now(),
        ]);
    }
}

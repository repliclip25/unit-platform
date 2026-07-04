<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') return;

        // For every deployment_billing row with no plan_slug, resolve the default
        // plan from worker_pricing (lowest sort_order active plan for that worker).
        // This backfills all existing deployments so PolicyEngine can enforce the
        // correct transaction_limit and AI tier from the pricing table immediately.
        $rows = DB::table('deployment_billing')
            ->whereNull('plan_slug')
            ->orWhere('plan_slug', '')
            ->get(['id', 'worker_slug']);

        foreach ($rows as $row) {
            $defaultPlan = DB::table('worker_pricing')
                ->where('worker_slug', $row->worker_slug)
                ->where('active', true)
                ->orderBy('sort_order')
                ->value('plan_slug');

            if ($defaultPlan) {
                DB::table('deployment_billing')
                    ->where('id', $row->id)
                    ->update(['plan_slug' => $defaultPlan, 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // Irreversible data backfill — no rollback
    }
};

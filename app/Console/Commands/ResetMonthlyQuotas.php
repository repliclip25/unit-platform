<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetMonthlyQuotas extends Command
{
    protected $signature   = 'billing:reset-monthly-quotas';
    protected $description = 'Reset unit_count and billing_period_start for all active subscriptions at the start of each month.';

    public function handle(): int
    {
        $now     = now();
        $updated = DB::table('deployment_billing')
            ->where('status', 'active')
            ->whereNotNull('deployment_id')
            ->update([
                'unit_count'           => 0,
                'billing_period_start' => $now->startOfMonth()->toDateString(),
                'updated_at'           => $now,
            ]);

        Log::info("billing:reset-monthly-quotas — reset {$updated} active deployment(s)");
        $this->info("Reset {$updated} active deployment(s) for new billing period.");

        return self::SUCCESS;
    }
}

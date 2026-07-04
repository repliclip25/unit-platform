<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

class PlatformDefaults
{
    const TRIAL_TRANSACTIONS = 25;
    const TRIAL_DAYS = 14;

    /**
     * Resolve the trial transaction limit for a worker slug.
     * Priority: DB admin override → WorkerContract billing() → constant default.
     * The DB override lets admins adjust limits without a code deploy.
     */
    public static function freeTransactionsFor(string $workerSlug): int
    {
        // 1. Admin override in worker_pricing
        $dbOverride = DB::table('worker_pricing')
            ->where('worker_slug', $workerSlug)
            ->value('free_transactions');
        if ($dbOverride > 0) return (int) $dbOverride;

        // 2. Worker contract billing DNA
        $contract = WorkerRegistry::resolve($workerSlug);
        $billing  = $contract->billing();
        if (!empty($billing['trial_transactions'])) return (int) $billing['trial_transactions'];

        // 3. Platform constant
        return self::TRIAL_TRANSACTIONS;
    }

    /**
     * Resolve trial duration in days.
     * Priority: DB platform_configs → WorkerContract billing() → constant default.
     */
    public static function trialDays(string $workerSlug = ''): int
    {
        // 1. Platform-level config override
        $dbDays = DB::table('platform_configs')->where('key', 'trial_days')->value('value');
        if ($dbDays > 0) return (int) $dbDays;

        // 2. Worker contract billing DNA
        if ($workerSlug) {
            $contract = WorkerRegistry::resolve($workerSlug);
            $billing  = $contract->billing();
            if (!empty($billing['trial_days'])) return (int) $billing['trial_days'];
        }

        // 3. Platform constant
        return self::TRIAL_DAYS;
    }

    /**
     * Resolve the billing unit label for a worker (e.g. 'email', 'post', 'video').
     */
    public static function billingUnit(string $workerSlug): string
    {
        $contract = WorkerRegistry::resolve($workerSlug);
        return $contract->billing()['billing_unit'] ?? 'transaction';
    }

    public static function unitLabel(string $workerSlug, bool $plural = false): string
    {
        $contract = WorkerRegistry::resolve($workerSlug);
        $billing  = $contract->billing();
        return $plural
            ? ($billing['unit_label_plural'] ?? 'transactions')
            : ($billing['unit_label']        ?? 'transaction');
    }
}

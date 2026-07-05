<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

class PlatformDefaults
{
    const TRIAL_TRANSACTIONS = 25;
    const TRIAL_DAYS = 14;

    /**
     * Resolve the trial transaction limit for a worker slug.
     * Priority: trial plan row free_transactions → WorkerContract billing() → constant default.
     */
    public static function freeTransactionsFor(string $workerSlug): int
    {
        // 1. Trial plan row in worker_pricing (is_trial_plan = true)
        $dbOverride = DB::table('worker_pricing')
            ->where('worker_slug', $workerSlug)
            ->where('is_trial_plan', true)
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
     * Priority: trial plan row trial_days → platform_configs → WorkerContract billing() → constant default.
     * Per-plan trial_days lets admins set 14/30/60/90 days per worker from the admin pricing panel.
     */
    public static function trialDays(string $workerSlug = ''): int
    {
        // 1. Per-worker trial plan row — most specific, fully admin-configurable
        if ($workerSlug) {
            $planDays = DB::table('worker_pricing')
                ->where('worker_slug', $workerSlug)
                ->where('is_trial_plan', true)
                ->value('trial_days');
            if ($planDays > 0) return (int) $planDays;
        }

        // 2. Platform-level config override (global fallback)
        $dbDays = DB::table('platform_configs')->where('key', 'trial_days')->value('value');
        if ($dbDays > 0) return (int) $dbDays;

        // 3. Worker contract billing DNA
        if ($workerSlug) {
            $contract = WorkerRegistry::resolve($workerSlug);
            $billing  = $contract->billing();
            if (!empty($billing['trial_days'])) return (int) $billing['trial_days'];
        }

        // 4. Platform constant
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

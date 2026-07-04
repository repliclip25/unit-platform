<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

class PlatformDefaults
{
    const TRIAL_TRANSACTIONS = 25;
    const TRIAL_DAYS = 14;

    public static function trialTransactions(): int
    {
        return (int) (DB::table('platform_configs')->where('key', 'trial_transactions')->value('value') ?: self::TRIAL_TRANSACTIONS);
    }

    public static function trialDays(): int
    {
        return (int) (DB::table('platform_configs')->where('key', 'trial_days')->value('value') ?: self::TRIAL_DAYS);
    }

    public static function freeTransactionsFor(string $workerSlug): int
    {
        $fromPricing = DB::table('worker_pricing')->where('worker_slug', $workerSlug)->value('free_transactions');
        return (int) ($fromPricing ?: self::trialTransactions());
    }
}

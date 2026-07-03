<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

/**
 * PlatformClockRegistry — non-worker value clock entries.
 *
 * Worker-specific clocks come from WorkerContract::valueClock().
 * This registry holds clocks for platform-level modules (referral,
 * team memory, billing credits, etc.) that have no worker contract.
 *
 * Each entry declares the same shape as valueClock() plus a
 * `resolver` callable that receives ($userId) and returns
 * ['value' => ..., 'count' => ...].
 */
class PlatformClockRegistry
{
    public static function all(): array
    {
        return [
            'referral' => [
                'label'   => 'Referral Earnings',
                'metric'  => 'referral_earnings',
                'unit'    => '',
                'prefix'  => '$',
                'subtitle'=> '{count} paid conversions',
                'formula' => 'sum of applied referral_credits.credit_usd',
                'source'  => 'You earn a credit for every colleague who signs up via your referral link and converts to a paid plan.',
                'scope'   => 'user',
                'owner'   => 'YOU',
                'resolver'=> fn(int $userId) => [
                    'value' => number_format(
                        DB::table('referral_credits')->where('referrer_id', $userId)->where('status', 'applied')->sum('credit_usd'),
                        2
                    ),
                    'count' => DB::table('referral_credits')->where('referrer_id', $userId)->where('event', 'paid_conversion')->count(),
                ],
            ],

            'memory' => [
                'label'   => 'Memory Enrichments',
                'metric'  => 'memory_enrichments',
                'unit'    => '',
                'prefix'  => '',
                'subtitle'=> '{count} enrichments stored',
                'formula' => 'sum of all records across memory types',
                'source'  => 'Memory on UNIT grows with your workers. Every piece of data they learn — clients, contacts, assets, and future types — is stored once and reused across every interaction.',
                'scope'   => 'user',
                'owner'   => 'TEAM',
                'resolver'=> fn(int $userId) => [
                    'value' => number_format(
                        DB::table('clients')->where('user_id', $userId)->count()
                        + DB::table('contacts')->where('user_id', $userId)->count()
                        + DB::table('assets')->where('user_id', $userId)->count()
                    ),
                    'count' => DB::table('clients')->where('user_id', $userId)->count()
                               + DB::table('contacts')->where('user_id', $userId)->count()
                               + DB::table('assets')->where('user_id', $userId)->count(),
                ],
            ],
        ];
    }
}

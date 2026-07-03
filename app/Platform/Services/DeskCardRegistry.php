<?php

namespace App\Platform\Services;

/**
 * DeskCardRegistry — static pool of non-worker Desk card types.
 *
 * Worker pipeline cards are NOT stored here — they are declared by each
 * WorkerContract::deskCards() and keyed dynamically as `worker.{slug}.{metric}`.
 *
 * This registry covers:
 *   memory.*   — memory-level cards (asset expiry, enrichments, top queried)
 *   referral.* — growth / referral events
 *   platform.* / milestone.* — marketing and milestone cards
 */
class DeskCardRegistry
{
    public static function all(): array
    {
        return [
            // ── Memory tier ──────────────────────────────────────────────────
            'memory.asset_expiry' => [
                'tier'        => 'memory',
                'label'       => 'Asset Expiry',
                'description' => 'Assets with renewal dates coming up in the next 30 days',
                'default'     => true,
                'default_pos' => 5,
                'dismissible' => false,
            ],
            'memory.enrichments' => [
                'tier'        => 'memory',
                'label'       => 'Memory Enrichments',
                'description' => 'New entries added to your team memory this week',
                'default'     => true,
                'default_pos' => 6,
                'dismissible' => false,
            ],
            'memory.summary' => [
                'tier'        => 'memory',
                'label'       => 'Memory Summary',
                'description' => 'Total clients, contacts, and assets loaded in your memory',
                'default'     => true,
                'default_pos' => 7,
                'dismissible' => false,
            ],

            // ── Growth tier ──────────────────────────────────────────────────
            'referral.conversions' => [
                'tier'        => 'growth',
                'label'       => 'Referral Conversions',
                'description' => 'Referrals that converted to paid this week',
                'default'     => true,
                'default_pos' => 50,
                'dismissible' => false,
            ],
            'referral.signups' => [
                'tier'        => 'growth',
                'label'       => 'New Referral Signups',
                'description' => 'Someone just joined using your referral link',
                'default'     => true,
                'default_pos' => 60,
                'dismissible' => false,
            ],

            // ── Platform / Marketing tier ────────────────────────────────────
            'marketing.new_worker' => [
                'tier'        => 'platform',
                'label'       => 'New Worker Available',
                'description' => 'A new worker is available to add to your team',
                'default'     => true,
                'default_pos' => 70,
                'dismissible' => true,
            ],
            'marketing.worker_reviews' => [
                'tier'        => 'platform',
                'label'       => 'Worker Reviews',
                'description' => 'Your workers received new reviews on the marketplace',
                'default'     => true,
                'default_pos' => 80,
                'dismissible' => true,
            ],
            'milestone.hours_saved' => [
                'tier'        => 'platform',
                'label'       => 'Hours Saved Milestone',
                'description' => 'Celebrate a new hours-saved milestone',
                'default'     => true,
                'default_pos' => 90,
                'dismissible' => true,
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        if (str_starts_with($key, 'worker.')) return ['dynamic' => true];
        return self::all()[$key] ?? null;
    }

    /**
     * Returns cards with admin DB overrides merged in, filtering inactive ones.
     */
    public static function active(): array
    {
        $pool = self::all();
        try {
            $config = \Illuminate\Support\Facades\DB::table('platform_desk_card_config')
                ->get()->keyBy('card_key');
        } catch (\Throwable) {
            return $pool; // table not yet migrated
        }

        $result = [];
        foreach ($pool as $key => $def) {
            $row = $config->get($key);
            if ($row && !(bool) $row->active) continue; // globally disabled by admin
            $result[$key] = array_merge($def, [
                'label'       => $row?->label       ?: $def['label'],
                'description' => $row?->description ?: $def['description'],
                'default'     => $row ? (bool) $row->default_on : $def['default'],
                'default_pos' => $row?->sort_order ?? $def['default_pos'],
            ]);
        }
        return $result;
    }

    public static function defaults(): array
    {
        return collect(self::active())
            ->filter(fn($c) => $c['default'])
            ->sortBy('default_pos')
            ->keys()
            ->values()
            ->toArray();
    }
}

<?php

namespace App\Platform\Services;

/**
 * DeskCardRegistry — the full pool of available Desk card types.
 *
 * Each entry declares:
 *   key         Unique identifier stored in user_desk_cards.card_key
 *   tier        'operational' | 'growth' | 'platform'
 *   label       Short display name shown in the Customize Desk drawer
 *   description One line explaining what the card shows
 *   default     Whether the card is visible by default for new users
 *   default_pos Default sort position (lower = higher on desk)
 *   dismissible Whether the card can be hidden after showing (milestone cards)
 *
 * Card data is resolved at runtime by DeskService::resolve().
 */
class DeskCardRegistry
{
    public static function all(): array
    {
        return [
            // ── Tier 1: Operational ──────────────────────────────────────────
            'pipeline.processed' => [
                'tier'        => 'operational',
                'label'       => 'Emails Processed',
                'description' => 'How many emails your workers handled this week',
                'default'     => true,
                'default_pos' => 10,
                'dismissible' => false,
            ],
            'pipeline.drafts' => [
                'tier'        => 'operational',
                'label'       => 'Drafts Ready',
                'description' => 'Drafts waiting for your review in Gmail',
                'default'     => true,
                'default_pos' => 20,
                'dismissible' => false,
            ],
            'pipeline.urgent' => [
                'tier'        => 'operational',
                'label'       => 'Urgent Items',
                'description' => 'High-priority items needing your attention',
                'default'     => true,
                'default_pos' => 30,
                'dismissible' => false,
            ],
            'pipeline.stuck' => [
                'tier'        => 'operational',
                'label'       => 'Failed / Stuck',
                'description' => 'Items that failed or are stuck in the pipeline',
                'default'     => true,
                'default_pos' => 40,
                'dismissible' => false,
            ],

            // ── Tier 2: Growth ───────────────────────────────────────────────
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

            // ── Tier 3: Platform / Marketing ─────────────────────────────────
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
        return self::all()[$key] ?? null;
    }

    public static function defaults(): array
    {
        return collect(self::all())
            ->filter(fn($c) => $c['default'])
            ->sortBy('default_pos')
            ->keys()
            ->values()
            ->toArray();
    }
}

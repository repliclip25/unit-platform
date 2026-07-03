<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

/**
 * DeskService — resolves "Your Desk" card data for a user.
 *
 * Responsibilities:
 *   - Seed default card config for new users
 *   - Return the user's ordered, visible card definitions
 *   - Resolve live data for each visible card
 */
class DeskService
{
    /**
     * Ensure a user has desk card rows seeded.
     * Called on first dashboard load — safe to call repeatedly (upsert).
     */
    public static function seedDefaults(int $userId): void
    {
        $existing = DB::table('user_desk_cards')
            ->where('user_id', $userId)
            ->pluck('card_key')
            ->flip();

        $defaults = DeskCardRegistry::defaults();

        foreach ($defaults as $pos => $key) {
            if ($existing->has($key)) continue;
            DB::table('user_desk_cards')->insert([
                'user_id'    => $userId,
                'card_key'   => $key,
                'visible'    => true,
                'position'   => ($pos + 1) * 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Resolve the user's desk — visible cards in order, with live data.
     * Returns a collection of resolved card arrays ready for the view.
     */
    public static function resolve(int $userId, array $context): \Illuminate\Support\Collection
    {
        self::seedDefaults($userId);

        $rows = DB::table('user_desk_cards')
            ->where('user_id', $userId)
            ->where('visible', true)
            ->orderBy('position')
            ->get()
            ->keyBy('card_key');

        $pool = DeskCardRegistry::all();

        return $rows->map(function ($row) use ($pool, $context, $userId) {
            $def = $pool[$row->card_key] ?? null;
            if (!$def) return null;

            $data = self::resolveCard($row->card_key, $userId, $context, $row);
            if ($data === null) return null; // card has nothing to show right now

            return array_merge($def, [
                'key'              => $row->card_key,
                'position'         => $row->position,
                'last_dismissed_at'=> $row->last_dismissed_at,
            ], $data);
        })->filter()->values();
    }

    /**
     * All card configs (visible + hidden) for the Customize Desk drawer.
     */
    public static function allForUser(int $userId): \Illuminate\Support\Collection
    {
        self::seedDefaults($userId);

        $rows = DB::table('user_desk_cards')
            ->where('user_id', $userId)
            ->orderBy('position')
            ->get()
            ->keyBy('card_key');

        $pool = DeskCardRegistry::all();

        // Include pool cards not yet in user rows (newly added to registry)
        $cards = collect();
        foreach ($pool as $key => $def) {
            $row = $rows->get($key);
            $cards->push(array_merge($def, [
                'key'      => $key,
                'visible'  => $row ? (bool) $row->visible : $def['default'],
                'position' => $row ? $row->position : $def['default_pos'],
            ]));
        }

        return $cards->sortBy('position')->values();
    }

    // ── Card resolvers ────────────────────────────────────────────────────────

    private static function resolveCard(string $key, int $userId, array $ctx, object $row): ?array
    {
        $weekStart = now()->startOfWeek();

        return match($key) {

            'pipeline.processed' => [
                'text'   => $ctx['ovProcessed'] > 0
                    ? '<strong>' . number_format($ctx['ovProcessed']) . '</strong> emails processed this week across all workers'
                    : 'No emails processed this week',
                'dot'    => 'grey',
                'action' => null,
                'always' => true,
            ],

            'pipeline.drafts' => $ctx['ovDrafts'] > 0 ? [
                'text'      => '<strong>' . $ctx['ovDrafts'] . '</strong> ' . ($ctx['ovDrafts'] === 1 ? 'draft' : 'drafts') . ' ready for your review',
                'dot'       => 'accent',
                'action'    => ['label' => 'Open Gmail', 'url' => $ctx['gmailUrl'], 'external' => true],
                'always'    => false,
            ] : null,

            'pipeline.urgent' => $ctx['ovUrgent'] > 0 ? [
                'text'   => '<strong>' . $ctx['ovUrgent'] . '</strong> ' . ($ctx['ovUrgent'] === 1 ? 'item' : 'items') . ' marked urgent — needs your attention',
                'dot'    => 'amber',
                'action' => ['label' => 'Review', 'url' => route('transactions', ['filter' => 'draft_ready', 'priority' => 'high'])],
                'always' => false,
            ] : null,

            'pipeline.stuck' => ($ctx['ovFailed'] + $ctx['ovStuck']) > 0 ? [
                'text'   => '<strong>' . ($ctx['ovFailed'] + $ctx['ovStuck']) . '</strong> ' . (($ctx['ovFailed'] + $ctx['ovStuck']) === 1 ? 'item' : 'items') . ' failed or stuck in pipeline',
                'dot'    => 'red',
                'action' => ['label' => 'View', 'url' => route('transactions', ['filter' => 'failed'])],
                'always' => false,
            ] : null,

            'referral.conversions' => (function() use ($userId, $weekStart) {
                $count = DB::table('referral_credits')
                    ->where('referrer_id', $userId)
                    ->where('event', 'paid_conversion')
                    ->where('created_at', '>=', $weekStart)
                    ->count();
                return $count > 0 ? [
                    'text'   => '<strong>' . $count . '</strong> ' . ($count === 1 ? 'referral' : 'referrals') . ' converted to paid this week',
                    'dot'    => 'green',
                    'action' => ['label' => 'View earnings', 'url' => route('referral.index')],
                    'always' => false,
                ] : null;
            })(),

            'referral.signups' => (function() use ($userId, $weekStart) {
                $count = DB::table('referral_credits')
                    ->where('referrer_id', $userId)
                    ->where('event', 'signup')
                    ->where('created_at', '>=', $weekStart)
                    ->count();
                $latest = DB::table('referral_credits')
                    ->where('referrer_id', $userId)
                    ->where('event', 'signup')
                    ->where('created_at', '>=', $weekStart)
                    ->orderByDesc('created_at')
                    ->first();
                return $count > 0 ? [
                    'text'   => $count === 1
                        ? 'Someone just joined UNIT with your referral link'
                        : '<strong>' . $count . '</strong> people joined UNIT with your referral link this week',
                    'dot'    => 'green',
                    'action' => ['label' => 'View referrals', 'url' => route('referral.index')],
                    'always' => false,
                ] : null;
            })(),

            'marketing.new_worker' => (function() use ($userId, $row) {
                // Show if a worker exists in the registry that the user hasn't deployed
                $deployed = DB::table('worker_deployments')->where('user_id', $userId)->pluck('worker_slug')->unique();
                $available = collect(WorkerRegistry::all())->filter(fn($c) => !$deployed->contains($c->identity()['slug']));
                if ($available->isEmpty()) return null;
                if ($row->last_dismissed_at) return null; // dismissed
                $worker = $available->first();
                $name = $worker->identity()['name'] ?? 'A new worker';
                return [
                    'text'        => 'New! <strong>' . $name . '</strong> is available — expand your team',
                    'dot'         => 'accent',
                    'action'      => ['label' => 'See worker', 'url' => route('workers.deploy')],
                    'always'      => false,
                    'dismissible' => true,
                    'dismiss_key' => 'marketing.new_worker',
                ];
            })(),

            'marketing.worker_reviews' => (function() use ($userId, $row, $weekStart) {
                if ($row->last_dismissed_at && $row->last_dismissed_at >= $weekStart) return null;
                // Placeholder — when marketplace reviews are wired, query here
                return null;
            })(),

            'milestone.hours_saved' => (function() use ($userId, $row) {
                $total = DB::table('transactions')
                    ->where('user_id', $userId)
                    ->whereNotIn('status', ['received','failed','filtered_out','dismissed'])
                    ->count();
                $hours = round($total * 0.25);
                $milestones = [10, 25, 50, 100, 250, 500, 1000];
                $hit = collect($milestones)->last(fn($m) => $hours >= $m);
                if (!$hit) return null;
                // Only show if not dismissed after hitting this milestone
                if ($row->last_dismissed_at) return null;
                return [
                    'text'        => '🎉 You\'ve saved over <strong>' . $hit . ' hours</strong> with UNIT — keep going',
                    'dot'         => 'accent',
                    'action'      => ['label' => 'Share', 'url' => route('referral.index')],
                    'always'      => false,
                    'dismissible' => true,
                    'dismiss_key' => 'milestone.hours_saved',
                ];
            })(),

            default => null,
        };
    }
}

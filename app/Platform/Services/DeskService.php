<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

/**
 * DeskService — resolves "Your Desk" card data for a user.
 *
 * Card key namespaces:
 *   worker.{slug}.{metric}  Pipeline cards declared by each WorkerContract::deskCards()
 *   memory.*                Memory-level cards (asset expiry, enrichments, top queried)
 *   referral.*              Growth / referral event cards
 *   marketing.* / milestone.* Platform and milestone cards
 */
class DeskService
{
    // ── Seeding ───────────────────────────────────────────────────────────────

    public static function seedDefaults(int $userId): void
    {
        try {
            DB::table('user_desk_cards')->where('user_id', $userId)->exists();
        } catch (\Throwable) {
            return; // table not yet migrated — skip seeding silently
        }

        $existing = DB::table('user_desk_cards')
            ->where('user_id', $userId)
            ->pluck('card_key')
            ->flip();

        // Seed static (non-worker) default cards (respects admin active/default config)
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

        // Seed per-worker pipeline cards for this user's deployments
        $deployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'paused'])
            ->get();

        foreach ($deployments as $dep) {
            $contract = WorkerRegistry::resolve($dep->worker_slug);
            if (WorkerRegistry::isNull($contract)) continue;
            $cards = $contract->deskCards();
            foreach ($cards as $metric => $def) {
                $key = "worker.{$dep->worker_slug}.{$metric}";
                if ($existing->has($key)) continue;
                DB::table('user_desk_cards')->insert([
                    'user_id'    => $userId,
                    'card_key'   => $key,
                    'visible'    => $def['default'] ?? true,
                    'position'   => $def['default_pos'] ?? 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    // ── Resolve visible desk feed ─────────────────────────────────────────────

    public static function resolve(int $userId, array $context): \Illuminate\Support\Collection
    {
        try {
            self::seedDefaults($userId);

            $rows = DB::table('user_desk_cards')
                ->where('user_id', $userId)
                ->orderBy('position')
                ->get()
                ->keyBy('card_key');

            $staticPool = DeskCardRegistry::active();
            $workerPool = self::buildWorkerPool($userId);
            $allPool    = array_merge($workerPool, $staticPool);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[DeskService] resolve() setup failed', ['user' => $userId, 'error' => $e->getMessage()]);
            return collect();
        }

        $results = collect();

        foreach ($allPool as $key => $def) {
            // Isolated per-card: one broken card resolver must not blank out
            // the entire desk feed for every other (working) card.
            try {
                $row = $rows->get($key);

                // Determine visibility: use saved row if exists, else default
                $visible  = $row ? (bool) $row->visible : ($def['default'] ?? true);
                $position = $row ? $row->position : ($def['default_pos'] ?? 50);

                if (!$visible) continue;

                $fakeRow = $row ?? (object) [
                    'card_key'          => $key,
                    'visible'           => true,
                    'position'          => $position,
                    'last_dismissed_at' => null,
                ];

                $data = self::resolveCard($key, $userId, $context, $fakeRow, $workerPool);
                if ($data === null) continue;

                $results->push(array_merge($def, [
                    'key'               => $key,
                    'position'          => $position,
                    'last_dismissed_at' => $fakeRow->last_dismissed_at,
                ], $data));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('[DeskService] card resolver failed', ['user' => $userId, 'key' => $key, 'error' => $e->getMessage()]);
                continue;
            }
        }

        return $results->sortBy('position')->values();
    }

    // ── All cards for Customize Desk drawer ──────────────────────────────────

    public static function allForUser(int $userId): \Illuminate\Support\Collection
    {
        try {
            self::seedDefaults($userId);

            $rows = DB::table('user_desk_cards')
                ->where('user_id', $userId)
                ->orderBy('position')
                ->get()
                ->keyBy('card_key');

            $staticPool = DeskCardRegistry::active();
            $workerPool = self::buildWorkerPool($userId);
            $allPool    = array_merge($workerPool, $staticPool);

            $cards = collect();
            foreach ($allPool as $key => $def) {
                $row = $rows->get($key);
                $cards->push(array_merge($def, [
                    'key'      => $key,
                    'visible'  => $row ? (bool) $row->visible : ($def['default'] ?? true),
                    'position' => $row ? $row->position : ($def['default_pos'] ?? 50),
                ]));
            }

            return $cards->sortBy('position')->values();
        } catch (\Throwable) {
            return collect();
        }
    }

    // ── Build worker card pool dynamically ────────────────────────────────────

    private static function buildWorkerPool(int $userId): array
    {
        $pool        = [];
        $deployments = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'paused'])
            ->get();

        foreach ($deployments as $dep) {
            $contract = WorkerRegistry::resolve($dep->worker_slug);
            if (WorkerRegistry::isNull($contract)) continue;
            $name  = $contract->employee()['name'] ?? strtoupper($dep->worker_slug);
            $cards = $contract->deskCards();
            foreach ($cards as $metric => $def) {
                $key = "worker.{$dep->worker_slug}.{$metric}";
                $pool[$key] = array_merge($def, [
                    'tier'        => 'pipeline',
                    'worker_slug' => $dep->worker_slug,
                    'worker_name' => $name,
                    'deployment'  => $dep,
                    'label'       => $name . ' · ' . ($def['label'] ?? ucfirst($metric)),
                ]);
            }
        }

        return $pool;
    }

    // ── Card data resolvers ───────────────────────────────────────────────────

    private static function resolveCard(string $key, int $userId, array $ctx, object $row, array $workerPool): ?array
    {
        $weekStart = now()->startOfWeek();

        // ── Worker-specific pipeline cards ────────────────────────────────────
        if (str_starts_with($key, 'worker.')) {
            $def = $workerPool[$key] ?? null;
            if (!$def) return null;

            $dep    = $def['deployment'];
            $metric = explode('.', $key)[2] ?? '';
            $name   = $def['worker_name'];
            $slug   = $def['worker_slug'];

            return match($metric) {
                'processed' => [
                    'text'   => self::count(
                        DB::table('transactions')->where('deployment_id', $dep->id)->where('created_at', '>=', $weekStart)->count(),
                        fn($n) => "<strong>{$n}</strong> emails processed this week",
                        'No emails processed this week'
                    ),
                    'dot'    => 'grey',
                    'action' => null,
                    'always' => true,
                ],
                'drafts' => (function() use ($dep, $ctx, $slug) {
                    $n = DB::table('transactions')->where('deployment_id', $dep->id)->where('status', 'draft_ready')->whereNull('human_decision')->count();
                    return $n > 0 ? [
                        'text'   => "<strong>{$n}</strong> " . ($n === 1 ? 'draft' : 'drafts') . ' ready for your review',
                        'dot'    => 'accent',
                        'action' => ['label' => 'Open Gmail', 'url' => $ctx['gmailUrl'] ?? '#', 'external' => true],
                        'always' => false,
                    ] : null;
                })(),
                'urgent' => (function() use ($dep) {
                    $n = DB::table('transactions')->where('deployment_id', $dep->id)->where('status', 'draft_ready')->whereIn('priority', ['High','Critical'])->count();
                    return $n > 0 ? [
                        'text'   => "<strong>{$n}</strong> " . ($n === 1 ? 'item' : 'items') . ' marked urgent',
                        'dot'    => 'amber',
                        'action' => ['label' => 'Review', 'url' => route('transactions', ['filter' => 'draft_ready', 'priority' => 'high'])],
                        'always' => false,
                    ] : null;
                })(),
                'stuck' => (function() use ($dep) {
                    $failed = DB::table('transactions')->where('deployment_id', $dep->id)->where('status', 'failed')->count();
                    $stuck  = DB::table('transactions')->where('deployment_id', $dep->id)->whereNotIn('status', ['draft_ready','approved','sent','failed','dismissed','filtered_out'])->where('updated_at', '<', now()->subMinutes(5))->count();
                    $n = $failed + $stuck;
                    return $n > 0 ? [
                        'text'   => "<strong>{$n}</strong> " . ($n === 1 ? 'item' : 'items') . ' failed or stuck in pipeline',
                        'dot'    => 'red',
                        'action' => ['label' => 'View', 'url' => route('transactions', ['filter' => 'failed'])],
                        'always' => false,
                    ] : null;
                })(),
                'drafted' => (function() use ($dep, $weekStart) {
                    $n = DB::table('transactions')->where('deployment_id', $dep->id)->where('status', 'draft_ready')->where('created_at', '>=', $weekStart)->count();
                    return $n > 0 ? [
                        'text'   => "<strong>{$n}</strong> " . ($n === 1 ? 'post' : 'posts') . ' drafted this week',
                        'dot'    => 'accent',
                        'action' => null,
                        'always' => false,
                    ] : null;
                })(),
                'published' => (function() use ($dep, $weekStart) {
                    $n = DB::table('transactions')->where('deployment_id', $dep->id)->whereIn('status', ['approved','sent'])->where('updated_at', '>=', $weekStart)->count();
                    return $n > 0 ? [
                        'text'   => "<strong>{$n}</strong> " . ($n === 1 ? 'post' : 'posts') . ' published this week',
                        'dot'    => 'green',
                        'action' => null,
                        'always' => false,
                    ] : null;
                })(),
                'trial' => (function() use ($dep, $name) {
                    $billing = DB::table('deployment_billing')
                        ->where('deployment_id', $dep->id)
                        ->first(['status', 'trial_transactions_used', 'trial_transactions_limit']);

                    // Only show during trial — hide once on a paid plan
                    if (!$billing || $billing->status !== 'trial') return null;

                    $used  = (int) $billing->trial_transactions_used;
                    $limit = (int) ($billing->trial_transactions_limit ?: 25);
                    $left  = max(0, $limit - $used);
                    $pct   = $limit > 0 ? round(($used / $limit) * 100) : 0;

                    if ($left === 0) {
                        $text = "🎁 <strong>{$name}</strong> — free trial exhausted ({$used}/{$limit} used)";
                        $dot  = 'red';
                    } elseif ($left <= 3) {
                        $text = "🎁 <strong>{$name}</strong> — <strong>{$left}</strong> free " . ($left === 1 ? 'credit' : 'credits') . " left ({$used}/{$limit} used)";
                        $dot  = 'amber';
                    } else {
                        $text = "🎁 <strong>{$name}</strong> — <strong>{$used}/{$limit}</strong> free transactions used";
                        $dot  = 'green';
                    }

                    return [
                        'text'   => $text,
                        'dot'    => $dot,
                        'action' => $left === 0
                            ? ['label' => 'Upgrade', 'url' => route('billing')]
                            : null,
                        'always' => true,
                    ];
                })(),
                default => null,
            };
        }

        // ── Memory cards ──────────────────────────────────────────────────────

        if ($key === 'memory.asset_expiry') {
            $expiring = DB::table('assets')
                ->where('user_id', $userId)
                ->whereNotNull('renewal_date')
                ->whereDate('renewal_date', '<=', now()->addDays(30))
                ->whereDate('renewal_date', '>=', today())
                ->orderBy('renewal_date')
                ->limit(3)
                ->get(['name', 'type', 'renewal_date']);

            if ($expiring->isEmpty()) return null;

            $n = $expiring->count();
            $first = $expiring->first();
            $days  = (int) now()->diffInDays($first->renewal_date, false);
            $label = $days === 0 ? 'today' : ($days === 1 ? 'tomorrow' : "in {$days} days");

            $text = $n === 1
                ? "<strong>{$first->name}</strong> ({$first->type}) renews {$label}"
                : "<strong>{$n}</strong> assets renewing soon — <strong>{$first->name}</strong> is next ({$label})";

            return [
                'text'   => $text,
                'dot'    => $days <= 7 ? 'amber' : 'grey',
                'action' => ['label' => 'View assets', 'url' => route('memory')],
                'always' => false,
            ];
        }

        if ($key === 'memory.enrichments') {
            $clients  = DB::table('clients')->where('user_id', $userId)->where('created_at', '>=', $weekStart)->count();
            $contacts = DB::table('contacts')->where('user_id', $userId)->where('created_at', '>=', $weekStart)->count();
            $assets   = DB::table('assets')->where('user_id', $userId)->where('created_at', '>=', $weekStart)->count();
            $n = $clients + $contacts + $assets;

            return [
                'text'   => $n > 0
                    ? "<strong>{$n}</strong> memory " . ($n === 1 ? 'enrichment' : 'enrichments') . " added this week"
                    : 'No new memory enrichments this week',
                'dot'    => $n > 0 ? 'green' : 'grey',
                'action' => $n > 0 ? ['label' => 'View memory', 'url' => route('memory')] : null,
                'always' => true,
            ];
        }

        if ($key === 'memory.summary') {
            $clients  = DB::table('clients')->where('user_id', $userId)->whereNull('deleted_at')->count();
            $contacts = DB::table('contacts')->where('user_id', $userId)->whereNull('deleted_at')->count();
            $assets   = DB::table('assets')->where('user_id', $userId)->whereNull('deleted_at')->count();
            $total    = $clients + $contacts + $assets;

            $cLabel = $clients  === 1 ? 'client'  : 'clients';
            $tLabel = $contacts === 1 ? 'contact' : 'contacts';
            $aLabel = $assets   === 1 ? 'asset'   : 'assets';

            return [
                'text'   => "<strong>{$clients}</strong> {$cLabel} · <strong>{$contacts}</strong> {$tLabel} · <strong>{$assets}</strong> {$aLabel} in your memory",
                'dot'    => $total > 0 ? 'green' : 'grey',
                'action' => ['label' => 'View memory', 'url' => route('memory')],
                'always' => true,
            ];
        }

        // ── Referral cards ────────────────────────────────────────────────────

        if ($key === 'referral.conversions') {
            $count = DB::table('referral_credits')->where('referrer_id', $userId)->where('event', 'paid_conversion')->where('created_at', '>=', $weekStart)->count();
            return $count > 0 ? [
                'text'   => "<strong>{$count}</strong> " . ($count === 1 ? 'referral' : 'referrals') . ' converted to paid this week',
                'dot'    => 'green',
                'action' => ['label' => 'View earnings', 'url' => route('referral.index')],
                'always' => false,
            ] : null;
        }

        if ($key === 'referral.signups') {
            $count = DB::table('referral_credits')->where('referrer_id', $userId)->where('event', 'signup')->where('created_at', '>=', $weekStart)->count();
            return $count > 0 ? [
                'text'   => $count === 1
                    ? 'Someone just joined UNIT with your referral link'
                    : "<strong>{$count}</strong> people joined UNIT with your referral link this week",
                'dot'    => 'green',
                'action' => ['label' => 'View referrals', 'url' => route('referral.index')],
                'always' => false,
            ] : null;
        }

        // ── Platform / milestone cards ─────────────────────────────────────────

        if ($key === 'marketing.new_worker') {
            if ($row->last_dismissed_at) return null;
            $deployed  = DB::table('worker_deployments')->where('user_id', $userId)->pluck('worker_slug')->unique();
            $available = collect(WorkerRegistry::all())->filter(fn($c) => !$deployed->contains($c->identity()['slug'] ?? ''));
            if ($available->isEmpty()) return null;
            $worker = $available->first();
            $name   = $worker->identity()['name'] ?? 'A new worker';
            return [
                'text'        => "New! <strong>{$name}</strong> is available — expand your team",
                'dot'         => 'accent',
                'action'      => ['label' => 'See worker', 'url' => route('workers.deploy')],
                'always'      => false,
                'dismissible' => true,
                'dismiss_key' => 'marketing.new_worker',
            ];
        }

        if ($key === 'marketing.worker_reviews') {
            if ($row->last_dismissed_at && $row->last_dismissed_at >= $weekStart) return null;
            return null; // wired when marketplace reviews land
        }

        if ($key === 'milestone.hours_saved') {
            if ($row->last_dismissed_at) return null;
            $total = DB::table('transactions')->where('user_id', $userId)->whereNotIn('status', ['received','failed','filtered_out','dismissed'])->count();
            $hours = round($total * 0.25);
            $hit   = collect([10, 25, 50, 100, 250, 500, 1000])->last(fn($m) => $hours >= $m);
            if (!$hit) return null;
            return [
                'text'        => "🎉 You've saved over <strong>{$hit} hours</strong> with UNIT — keep going",
                'dot'         => 'accent',
                'action'      => ['label' => 'Share', 'url' => route('referral.index')],
                'always'      => false,
                'dismissible' => true,
                'dismiss_key' => 'milestone.hours_saved',
            ];
        }

        return null;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function count(int $n, callable $some, string $none): string
    {
        return $n > 0 ? $some($n) : $none;
    }
}

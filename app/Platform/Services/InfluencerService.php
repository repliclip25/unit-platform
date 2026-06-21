<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class InfluencerService
{
    const TIER_RATES = [
        'starter' => 0.20,
        'pro'     => 0.25,
        'elite'   => 0.30,
    ];

    const TIER_THRESHOLDS = [
        'pro'   => 5,   // 5 conversions → pro
        'elite' => 15,  // 15 conversions → elite
    ];

    public static function findBySlug(string $slug): ?object
    {
        return DB::table('influencers')->where('slug', $slug)->where('status', 'active')->first();
    }

    public static function trackClick(string $slug, Request $request): void
    {
        $influencer = DB::table('influencers')->where('slug', $slug)->first();
        DB::table('referral_clicks')->insert([
            'ref_type'      => 'influencer',
            'ref_code'      => $slug,
            'influencer_id' => $influencer?->id,
            'ip'            => $request->ip(),
            'user_agent'    => substr($request->userAgent() ?? '', 0, 512),
            'utm_source'    => $request->query('utm_source'),
            'utm_medium'    => $request->query('utm_medium'),
            'utm_campaign'  => $request->query('utm_campaign'),
            'landing_page'  => $request->fullUrl(),
            'converted'     => false,
            'created_at'    => now(),
        ]);
    }

    public static function handleSignup(int $newUserId, string $slug): void
    {
        $influencer = DB::table('influencers')->where('slug', $slug)->where('status', 'active')->first();
        if (!$influencer) return;

        DB::table('users')->where('id', $newUserId)->update(['referred_by_code' => 'inf:' . $slug]);

        DB::table('referral_credits')->insert([
            'referrer_id'    => null,
            'referee_id'     => $newUserId,
            'influencer_id'  => $influencer->id,
            'ref_type'       => 'influencer',
            'event'          => 'signup',
            'credit_usd'     => 0,
            'bonus_tx'       => 0,
            'commission_rate' => $influencer->commission_rate,
            'mrr_attributed' => 0,
            'status'         => 'pending',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Mark latest click as converted
        DB::table('referral_clicks')
            ->where('ref_type', 'influencer')
            ->where('ref_code', $slug)
            ->where('converted', false)
            ->orderByDesc('created_at')
            ->limit(1)
            ->update(['converted' => true]);
    }

    public static function handleConversion(int $refereeId): void
    {
        $referee = DB::table('users')->where('id', $refereeId)->first();
        if (!$referee || !str_starts_with($referee->referred_by_code ?? '', 'inf:')) return;

        $slug = substr($referee->referred_by_code, 4);
        $influencer = DB::table('influencers')->where('slug', $slug)->first();
        if (!$influencer) return;

        $already = DB::table('referral_credits')
            ->where('referee_id', $refereeId)
            ->where('ref_type', 'influencer')
            ->where('event', 'paid_conversion')
            ->exists();
        if ($already) return;

        // Get MRR from active subscription
        $sub = DB::table('subscriptions')
            ->where('user_id', $refereeId)
            ->where('stripe_status', 'active')
            ->first();
        $mrr = 0;
        if ($sub) {
            $mrr = (float) DB::table('subscription_items')
                ->where('subscription_id', $sub->id)
                ->join('worker_pricing', 'subscription_items.worker_pricing_id', '=', 'worker_pricing.id')
                ->sum('worker_pricing.monthly_flat_rate');
        }

        $commission = round($mrr * $influencer->commission_rate, 2);

        DB::table('referral_credits')->insert([
            'referrer_id'     => null,
            'referee_id'      => $refereeId,
            'influencer_id'   => $influencer->id,
            'ref_type'        => 'influencer',
            'event'           => 'paid_conversion',
            'credit_usd'      => $commission,
            'bonus_tx'        => 0,
            'commission_rate' => $influencer->commission_rate,
            'mrr_attributed'  => $mrr,
            'status'          => 'pending_payout',
            'converted_at'    => now(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('influencers')->where('id', $influencer->id)->update([
            'total_earned'   => DB::raw("total_earned + $commission"),
            'pending_payout' => DB::raw("pending_payout + $commission"),
        ]);

        // Update signup record
        DB::table('referral_credits')
            ->where('referee_id', $refereeId)
            ->where('ref_type', 'influencer')
            ->where('event', 'signup')
            ->update(['status' => 'converted', 'converted_at' => now(), 'updated_at' => now()]);

        // Auto-upgrade tier
        self::maybeUpgradeTier($influencer->id);
    }

    public static function maybeUpgradeTier(int $influencerId): void
    {
        $conversions = DB::table('referral_credits')
            ->where('influencer_id', $influencerId)
            ->where('event', 'paid_conversion')
            ->count();

        $influencer = DB::table('influencers')->where('id', $influencerId)->first();
        $current = $influencer->tier;
        $newTier = $current;
        $newRate = $influencer->commission_rate;

        if ($conversions >= self::TIER_THRESHOLDS['elite'] && $current !== 'elite') {
            $newTier = 'elite';
            $newRate = self::TIER_RATES['elite'];
        } elseif ($conversions >= self::TIER_THRESHOLDS['pro'] && $current === 'starter') {
            $newTier = 'pro';
            $newRate = self::TIER_RATES['pro'];
        }

        if ($newTier !== $current) {
            DB::table('influencers')->where('id', $influencerId)->update([
                'tier'            => $newTier,
                'commission_rate' => $newRate,
            ]);
        }
    }

    public static function getStats(int $influencerId): object
    {
        $influencer = DB::table('influencers')->where('id', $influencerId)->first();
        $clicks     = DB::table('referral_clicks')->where('influencer_id', $influencerId)->count();
        $signups    = DB::table('referral_credits')->where('influencer_id', $influencerId)->where('event', 'signup')->count();
        $converted  = DB::table('referral_credits')->where('influencer_id', $influencerId)->where('event', 'paid_conversion')->count();
        $mrr        = DB::table('referral_credits')->where('influencer_id', $influencerId)->where('event', 'paid_conversion')->sum('mrr_attributed');
        $earned     = $influencer->total_earned;
        $pending    = $influencer->pending_payout;
        $paidOut    = $influencer->paid_out;

        $convRate = $clicks > 0 ? round(($converted / $clicks) * 100, 1) : 0;

        // Monthly breakdown (last 6 months)
        $monthly = DB::table('referral_credits')
            ->where('influencer_id', $influencerId)
            ->where('event', 'paid_conversion')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as conversions, SUM(credit_usd) as commission")
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return (object) compact('influencer', 'clicks', 'signups', 'converted', 'mrr', 'earned', 'pending', 'paidOut', 'convRate', 'monthly');
    }
}

<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

class ReferralService
{
    const REFERRER_CREDIT_USD   = 25.00; // $25 credit on paid conversion
    const REFEREE_BONUS_TX      = 10;    // +10 extra trial tx for referee
    const TIER_THRESHOLDS       = [1, 3, 5, 10]; // referrals to unlock tiers
    const TIER_LABELS           = ['First Referral', 'Bronze', 'Silver', 'Gold'];

    public static function generateCode(int $userId, string $email): string
    {
        return strtoupper(substr(md5($userId . $email . 'unit'), 0, 8));
    }

    public static function ensureCode(int $userId): string
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if ($user->referral_code) {
            return $user->referral_code;
        }
        $code = self::generateCode($userId, $user->email);
        DB::table('users')->where('id', $userId)->update(['referral_code' => $code]);
        return $code;
    }

    // Called at registration if a ref code was in the URL
    public static function handleSignup(int $newUserId, string $refCode): void
    {
        $referrer = DB::table('users')->where('referral_code', $refCode)->first();
        if (!$referrer || $referrer->id === $newUserId) return;

        // Store the referral link on the new user
        DB::table('users')->where('id', $newUserId)->update(['referred_by_code' => $refCode]);

        // Give referee bonus trial transactions
        DB::table('deployment_billing')
            ->where('user_id', $newUserId)
            ->where('status', 'trial')
            ->increment('trial_transactions_limit', self::REFEREE_BONUS_TX);

        // Log signup event (credit pending until paid conversion)
        DB::table('referral_credits')->insert([
            'referrer_id'  => $referrer->id,
            'referee_id'   => $newUserId,
            'event'        => 'signup',
            'credit_usd'   => 0,
            'bonus_tx'     => self::REFEREE_BONUS_TX,
            'status'       => 'pending',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    // Called when a referred user converts to paid
    public static function handleConversion(int $refereeId): void
    {
        $referee = DB::table('users')->where('id', $refereeId)->first();
        if (!$referee?->referred_by_code) return;

        $referrer = DB::table('users')->where('referral_code', $referee->referred_by_code)->first();
        if (!$referrer) return;

        // Check not already credited for this conversion
        $already = DB::table('referral_credits')
            ->where('referee_id', $refereeId)
            ->where('event', 'paid_conversion')
            ->exists();
        if ($already) return;

        // Award credit to referrer
        DB::table('referral_credits')->insert([
            'referrer_id'    => $referrer->id,
            'referee_id'     => $refereeId,
            'event'          => 'paid_conversion',
            'credit_usd'     => self::REFERRER_CREDIT_USD,
            'bonus_tx'       => 0,
            'status'         => 'applied',
            'converted_at'   => now(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        DB::table('users')->where('id', $referrer->id)
            ->increment('referral_credit_balance', self::REFERRER_CREDIT_USD);

        // Update the signup record to converted
        DB::table('referral_credits')
            ->where('referee_id', $refereeId)
            ->where('event', 'signup')
            ->update(['status' => 'converted', 'converted_at' => now(), 'updated_at' => now()]);
    }

    public static function getStats(int $userId): object
    {
        $credits = DB::table('referral_credits')->where('referrer_id', $userId)->get();
        $user    = DB::table('users')->where('id', $userId)->first();

        $signups    = $credits->where('event', 'signup')->count();
        $converted  = $credits->where('event', 'paid_conversion')->count();
        $earned     = $credits->where('event', 'paid_conversion')->sum('credit_usd');
        $balance    = (float) ($user->referral_credit_balance ?? 0);

        $nextTier   = collect(self::TIER_THRESHOLDS)->first(fn($t) => $converted < $t);
        $tierIdx    = collect(self::TIER_THRESHOLDS)->search(fn($t) => $converted < $t);
        $tierLabel  = $tierIdx !== false ? (self::TIER_LABELS[$tierIdx] ?? null) : 'Gold+';
        $tierPct    = $nextTier ? min(100, ($converted / $nextTier) * 100) : 100;

        return (object) compact('signups', 'converted', 'earned', 'balance', 'nextTier', 'tierLabel', 'tierPct');
    }
}

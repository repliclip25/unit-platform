<?php

namespace App\Platform\Services\Memory;

use Illuminate\Support\Facades\DB;

/**
 * Shared formula for the clients/contacts/assets memory pool: given a set of
 * declared requirements (from a worker's memoryRequirements(), regardless of
 * whether that worker is deployed), scores how many clients are "complete"
 * against them. Extracted out of MemoryHealthService so the same formula can
 * be reused standalone (pre-deployment) instead of only for deployed workers.
 */
class MemoryCoverageCalculator
{
    public const HEALTHY_THRESHOLD = 5;

    /**
     * Returns: score (0-100), complete, total, needed, healthy.
     */
    public static function scoreClients(int $userId, array $requirements, int $threshold = self::HEALTHY_THRESHOLD): array
    {
        $empty = [
            'score'    => 0,
            'complete' => 0,
            'total'    => 0,
            'needed'   => $threshold,
            'healthy'  => false,
        ];

        if (empty($requirements)) {
            return $empty;
        }

        $clients = DB::table('clients')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($clients->isEmpty()) {
            return $empty;
        }

        $complete = 0;
        foreach ($clients as $clientId) {
            if (self::clientSatisfiesRequirements($userId, $clientId, $requirements)) {
                $complete++;
            }
        }

        $score = (int) min(100, round($complete / $threshold * 100));

        return [
            'score'    => $score,
            'complete' => $complete,
            'total'    => $clients->count(),
            'needed'   => max(0, $threshold - $complete),
            'healthy'  => $score >= 100,
        ];
    }

    /**
     * A contact is required only if 'contacts' is in requirements.
     * An asset is required only if 'assets' is in requirements.
     */
    private static function clientSatisfiesRequirements(int $userId, int $clientId, array $requirements): bool
    {
        if (isset($requirements['contacts'])) {
            $needsEmail = in_array('email', $requirements['contacts']);

            $contactQuery = DB::table('contacts')
                ->where('user_id', $userId)
                ->where('client_id', $clientId)
                ->whereNull('deleted_at');

            if ($needsEmail) {
                $contactQuery->whereNotNull('email')->where('email', '!=', '');
            }

            if (!$contactQuery->exists()) return false;
        }

        if (isset($requirements['assets'])) {
            $needsRenewal = in_array('renewal_date', $requirements['assets']);

            $assetQuery = DB::table('assets')
                ->where('user_id', $userId)
                ->where('client_id', $clientId)
                ->whereNull('deleted_at');

            if ($needsRenewal) {
                $assetQuery->whereNotNull('renewal_date');
            }

            if (!$assetQuery->exists()) return false;
        }

        return true;
    }
}

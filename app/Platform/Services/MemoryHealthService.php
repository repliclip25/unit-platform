<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

class MemoryHealthService
{
    // A broker needs this many complete records for AVA to work reliably.
    // "Complete" = client with at least one contact email AND one asset.
    public const HEALTHY_THRESHOLD = 5;

    /**
     * Calculate memory health for a user.
     *
     * Returns:
     *   score        — 0–100 integer (complete_records / HEALTHY_THRESHOLD * 100, capped at 100)
     *   complete     — number of clients with both a contact email and an asset
     *   total        — total clients loaded
     *   needed       — complete records still needed to reach threshold
     *   healthy      — bool: score >= 100
     */
    public static function score(int $userId): array
    {
        $clients = DB::table('clients')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($clients->isEmpty()) {
            return [
                'score'    => 0,
                'complete' => 0,
                'total'    => 0,
                'needed'   => self::HEALTHY_THRESHOLD,
                'healthy'  => false,
            ];
        }

        $contactClientIds = DB::table('contacts')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->whereNotNull('email')
            ->whereIn('client_id', $clients)
            ->pluck('client_id')
            ->unique();

        $assetClientIds = DB::table('assets')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->whereIn('client_id', $clients)
            ->pluck('client_id')
            ->unique();

        $complete = $contactClientIds->intersect($assetClientIds)->count();
        $score    = (int) min(100, round($complete / self::HEALTHY_THRESHOLD * 100));

        return [
            'score'    => $score,
            'complete' => $complete,
            'total'    => $clients->count(),
            'needed'   => max(0, self::HEALTHY_THRESHOLD - $complete),
            'healthy'  => $score >= 100,
        ];
    }
}

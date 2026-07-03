<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

/**
 * ClockResolver — resolves a valueClock() declaration into a display-ready array.
 *
 * For worker clocks (scope = 'deployment'):  resolveWorker($depId, $clock)
 * For platform clocks:                        use PlatformClockRegistry resolver callable
 */
class ClockResolver
{
    /**
     * Resolve a worker's valueClock() for a specific deployment.
     * Returns ['value', 'count', 'display', 'subtitle'].
     */
    public static function resolveWorker(int $deploymentId, array $clock): array
    {
        $metric = $clock['metric'] ?? '';

        $txBase = DB::table('transactions')
            ->where('deployment_id', $deploymentId)
            ->whereNotIn('status', ['received', 'failed', 'filtered_out', 'dismissed']);

        [$raw, $count] = match($metric) {
            'hours_saved_alltime' => [
                round($txBase->count() * 0.25, 1),
                $txBase->count(),
            ],
            'posts_drafted_alltime' => [
                $txBase->whereIn('status', ['approved', 'sent', 'draft_ready'])->count(),
                $txBase->count(),
            ],
            'approved_sent_alltime' => [
                (clone $txBase)->whereIn('status', ['approved', 'sent'])->count(),
                $txBase->count(),
            ],
            default => [0, 0],
        };

        $unit     = $clock['unit']    ?? '';
        $prefix   = $clock['prefix']  ?? '';
        $display  = $prefix . (is_float($raw) ? number_format($raw, 1) : number_format($raw)) . ($unit ? ' ' . $unit : '');
        $subtitle = str_replace('{count}', number_format($count), $clock['subtitle'] ?? '');

        return compact('raw', 'count', 'display', 'subtitle');
    }
}

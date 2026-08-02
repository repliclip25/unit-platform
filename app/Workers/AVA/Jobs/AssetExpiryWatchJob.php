<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Workers\AVA\Services\AssetTransactionSynthesizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Runs daily per active AVA deployment (see AvaWorker::scheduledJobs()).
 *
 * AVA's pipeline previously only ever started from an inbound Gmail email —
 * a tenant who maintains their asset registry directly but never connects
 * Gmail (or connects it, but the renewal notice lands somewhere else) got
 * nothing: the "Coming Up" horizon panel would show an asset 80 days
 * overdue forever, with no action ever taken on it.
 *
 * This job scans that same asset data and, when a renewal crosses a watch
 * threshold, seeds a transaction directly from the asset record — no email
 * to read or classify, so it synthesizes those two stage outputs itself and
 * hands off to UnitPlatform::advance() from 'memory' onward, reusing the
 * exact same LogTransactionJob -> SelectTemplateJob -> DraftEmailJob ->
 * PushToGmailJob chain a real inbound email would go through. PushToGmailJob
 * already degrades to an in-app-only draft_ready when there's no Gmail
 * credential connected.
 */
class AssetExpiryWatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    // Escalating lead-time buckets (days out) an asset can fall into, plus
    // 'overdue' for anything already past its renewal date. Each bucket
    // fires exactly once per asset — see alreadyNotified() — except
    // 'overdue', which re-fires periodically so a missed renewal doesn't
    // silently go quiet forever.
    private const OVERDUE_RENOTIFY_DAYS = 7;

    public function __construct(public int $deploymentId) {}

    public function handle(): void
    {
        $dep = DB::table('worker_deployments')->where('id', $this->deploymentId)->first();
        if (!$dep || $dep->status !== 'active') return;

        // Trigger gate — a tenant can run on Gmail Watch alone without the
        // asset registry proactively creating transactions, or vice versa.
        if (!UnitPlatform::gateEnabled($dep->id, 'asset_watch', true)) return;

        $assets = DB::table('assets')
            ->where('user_id', $dep->user_id)
            ->whereNull('deleted_at')
            ->where('type', '!=', 'discovered')
            ->whereNotNull('renewal_date')
            ->get();

        foreach ($assets as $asset) {
            try {
                $daysLeft  = (int) now()->diffInDays($asset->renewal_date, false);
                $threshold = $this->resolveThreshold($daysLeft);
                if (!$threshold || $this->alreadyNotified($asset->id, $threshold)) continue;

                AssetTransactionSynthesizer::create($asset, $dep, 'asset_watch', $threshold);

                DB::table('asset_watch_log')->insert([
                    'asset_id'    => $asset->id,
                    'threshold'   => $threshold,
                    'notified_at' => now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error('AssetExpiryWatchJob failed for asset', [
                    'asset_id' => $asset->id, 'deployment_id' => $this->deploymentId, 'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function resolveThreshold(int $daysLeft): ?string
    {
        if ($daysLeft < 0)  return 'overdue';
        if ($daysLeft <= 1) return '1';
        if ($daysLeft <= 7) return '7';
        if ($daysLeft <= 14) return '14';
        if ($daysLeft <= 30) return '30';
        return null; // too far out to be actionable yet
    }

    private function alreadyNotified(int $assetId, string $threshold): bool
    {
        $last = DB::table('asset_watch_log')
            ->where('asset_id', $assetId)
            ->where('threshold', $threshold)
            ->orderByDesc('notified_at')
            ->first();

        if (!$last) return false;

        return $threshold === 'overdue'
            ? now()->diffInDays($last->notified_at) < self::OVERDUE_RENOTIFY_DAYS
            : true; // non-overdue buckets fire exactly once as the asset crosses them
    }

}

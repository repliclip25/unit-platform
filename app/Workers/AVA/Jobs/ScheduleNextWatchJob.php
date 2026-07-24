<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Stage 16 — Schedule Next Watch. The terminal stage: the renewal cycle
 * genuinely closes here. AssetExpiryWatchJob already re-evaluates every asset
 * daily, so once UpdateRenewalDateJob (stage 13) advanced renewal_date,
 * nothing new needs to be scheduled — except one real bug this closes: the
 * asset_watch_log dedup table keys purely on (asset_id, threshold) forever.
 * Without clearing it here, an asset that already fired its "30 days out"
 * notification once would never fire it again on the *next* cycle either.
 * Clearing this asset's watch log on completion makes the dedup cycle-aware.
 */
class ScheduleNextWatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input  = UnitPlatform::getInput($this->txId);
        $memory = $input->stage('memory');

        $assetName = $memory['asset'] ?? null;
        $asset = $assetName
            ? DB::table('assets')->where('user_id', $input->userId)->where('name', $assetName)->whereNull('deleted_at')->first()
            : null;

        $cleared = 0;
        if ($asset) {
            $cleared = DB::table('asset_watch_log')->where('asset_id', $asset->id)->delete();
        }

        UnitPlatform::setFulfillmentStage($this->txId, 'schedule_next_watch');
        UnitPlatform::log('ava', $this->txId, 'renewal_cycle_complete', [
            'asset_id' => $asset->id ?? null, 'watch_log_cleared' => $cleared,
        ]);

        // Terminal — advance() will find nothing after this stage and return.
        // Called anyway for consistency: every stage hands off the same way.
        UnitPlatform::advance($this->txId, 'schedule_next_watch');
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::log('ava', $this->txId, 'job_failed', ['job' => 'ScheduleNextWatchJob', 'error' => $e->getMessage()], 'error');
    }
}

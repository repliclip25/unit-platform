<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Stage 13 — Update Next Renewal Date. Only runs after a human confirms
 * payment (TransactionController::confirmRenewal()), which is what dispatches
 * this via advance(). Advances the underlying asset's renewal_date by its
 * cadence — the actual "close the loop" moment: without this, the asset
 * would sit at its old (now-renewed) date and AssetExpiryWatchJob would just
 * flag it as overdue again forever.
 */
class UpdateRenewalDateJob implements ShouldQueue
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

        if (!$asset) {
            Log::warning('UpdateRenewalDateJob: no matching asset found — renewal_date not advanced', [
                'tx_id' => $this->txId, 'asset_name' => $assetName,
            ]);
            UnitPlatform::log('ava', $this->txId, 'renewal_date_not_updated', ['reason' => 'No matching asset found']);
            UnitPlatform::setFulfillmentStage($this->txId, 'update_renewal_date');
            UnitPlatform::advance($this->txId, 'update_renewal_date');
            return;
        }

        $cadenceDays = $asset->renewal_cadence_days ?? 365;
        $oldDate     = $asset->renewal_date;
        $newDate     = \Carbon\Carbon::parse($asset->renewal_date ?? now())->addDays($cadenceDays)->toDateString();

        DB::table('assets')->where('id', $asset->id)->update([
            'renewal_date' => $newDate,
            'updated_at'   => now(),
        ]);

        UnitPlatform::log('ava', $this->txId, 'renewal_date_updated', [
            'asset_id' => $asset->id, 'old_date' => $oldDate, 'new_date' => $newDate, 'cadence_days' => $cadenceDays,
        ]);
        UnitPlatform::setFulfillmentStage($this->txId, 'update_renewal_date');

        UnitPlatform::advance($this->txId, 'update_renewal_date');
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::log('ava', $this->txId, 'job_failed', ['job' => 'UpdateRenewalDateJob', 'error' => $e->getMessage()], 'error');
    }
}

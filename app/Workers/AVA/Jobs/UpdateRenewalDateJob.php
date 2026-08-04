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
        UnitPlatform::stageStarted($this->txId, 'update_renewal_date');

        $input  = UnitPlatform::getInput($this->txId);
        $memory = $input->stage('memory');

        $assetName = $memory['asset'] ?? null;

        // Fast Track has no real asset behind it — simulate the date math so
        // the demo shows something real, without ever touching a live row
        // (which could coincidentally collide with a tenant's actual asset
        // named the same as the test scenario).
        if ($input->isFastTrack()) {
            $cadenceDays = 365;
            $oldDate     = now()->toDateString();
            $newDate     = now()->addDays($cadenceDays)->toDateString();
            $output      = ['asset' => $assetName, 'old_date' => $oldDate, 'new_date' => $newDate, 'cadence_days' => $cadenceDays, 'simulated' => true];

            UnitPlatform::commitOutput($this->txId, new WorkerOutput(stage: 'update_renewal_date', data: $output));
            UnitPlatform::setFulfillmentStage($this->txId, 'update_renewal_date');
            UnitPlatform::log('ava', $this->txId, 'renewal_date_updated', $output);
            UnitPlatform::advance($this->txId, 'update_renewal_date');
            return;
        }

        // A renews_together bundle carries every real asset ID in
        // line_items — look those up directly instead of the fragile
        // name-match below, and advance every one of them, not just
        // whichever single asset happened to be named in memory['asset']
        // (the group label for a bundle, not a real asset name).
        $lineItems = $memory['line_items'] ?? null;

        if ($lineItems) {
            $advanced = [];
            foreach ($lineItems as $item) {
                $asset = DB::table('assets')->where('id', $item['id'])->where('user_id', $input->userId)->whereNull('deleted_at')->first();
                if (!$asset) {
                    Log::warning('UpdateRenewalDateJob: bundled asset not found — skipped', ['tx_id' => $this->txId, 'asset_id' => $item['id']]);
                    continue;
                }

                $cadenceDays = $asset->renewal_cadence_days ?? 365;
                $oldDate     = $asset->renewal_date;
                $newDate     = \Carbon\Carbon::parse($asset->renewal_date ?? now())->addDays($cadenceDays)->toDateString();

                DB::table('assets')->where('id', $asset->id)->update(['renewal_date' => $newDate, 'updated_at' => now()]);

                $advanced[] = ['id' => $asset->id, 'name' => $asset->name, 'old_date' => $oldDate, 'new_date' => $newDate, 'cadence_days' => $cadenceDays];
            }

            // Top-level old_date/new_date/cadence_days mirror the first
            // advanced item so anything reading those keys directly
            // (Transaction Center's generic dump, ArchiveEvidenceJob)
            // still shows something sensible without needing to know
            // about line_items specifically.
            $output = [
                'asset'        => $assetName,
                'line_items'   => $advanced,
                'old_date'     => $advanced[0]['old_date']     ?? null,
                'new_date'     => $advanced[0]['new_date']     ?? null,
                'cadence_days' => $advanced[0]['cadence_days'] ?? null,
            ];
            UnitPlatform::commitOutput($this->txId, new WorkerOutput(stage: 'update_renewal_date', data: $output));
            UnitPlatform::log('ava', $this->txId, 'renewal_date_updated_bundle', $output);
            UnitPlatform::setFulfillmentStage($this->txId, 'update_renewal_date');
            UnitPlatform::advance($this->txId, 'update_renewal_date');
            return;
        }

        $asset = $assetName
            ? DB::table('assets')->where('user_id', $input->userId)->where('name', $assetName)->whereNull('deleted_at')->first()
            : null;

        if (!$asset) {
            Log::warning('UpdateRenewalDateJob: no matching asset found — renewal_date not advanced', [
                'tx_id' => $this->txId, 'asset_name' => $assetName,
            ]);
            $output = ['asset' => $assetName, 'reason' => 'No matching asset found'];
            UnitPlatform::commitOutput($this->txId, new WorkerOutput(stage: 'update_renewal_date', data: $output));
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

        $output = ['asset' => $assetName, 'old_date' => $oldDate, 'new_date' => $newDate, 'cadence_days' => $cadenceDays];
        UnitPlatform::commitOutput($this->txId, new WorkerOutput(stage: 'update_renewal_date', data: $output));
        UnitPlatform::log('ava', $this->txId, 'renewal_date_updated', ['asset_id' => $asset->id] + $output);
        UnitPlatform::setFulfillmentStage($this->txId, 'update_renewal_date');

        UnitPlatform::advance($this->txId, 'update_renewal_date');
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::stageFailed($this->txId, 'update_renewal_date', $e->getMessage());
        UnitPlatform::log('ava', $this->txId, 'job_failed', ['job' => 'UpdateRenewalDateJob', 'error' => $e->getMessage()], 'error');
    }
}

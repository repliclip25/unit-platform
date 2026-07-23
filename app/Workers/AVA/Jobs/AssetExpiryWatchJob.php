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

                $this->createSyntheticTransaction($asset, $dep, $daysLeft, $threshold);

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

    private function createSyntheticTransaction(object $asset, object $dep, int $daysLeft, string $threshold): void
    {
        $client  = $asset->client_id ? DB::table('clients')->where('id', $asset->client_id)->first() : null;
        $contact = $client
            ? DB::table('contacts')->where('client_id', $client->id)->orderByDesc('is_primary')->first()
            : null;

        $urgency = match (true) {
            $threshold === 'overdue' => 'Critical',
            in_array($threshold, ['1', '7'], true) => 'High',
            $threshold === '14' => 'Medium',
            default => 'Low',
        };

        $tx = UnitPlatform::createTransaction('ava', [
            'source'        => 'asset_watch',
            'user_id'       => $dep->user_id,
            'deployment_id' => $dep->id,
            'asset_id'      => $asset->id,
            'threshold'     => $threshold,
        ]);

        // No inbound email for this trigger — the asset record itself is the
        // source of truth, so 'read' and 'classify' are synthesized directly
        // instead of running ReadEmailJob/ClassifyEmailJob against nothing.
        UnitPlatform::commitOutput($tx->tx_id, new WorkerOutput(
            stage:  'read',
            status: 'reading',
            data:   [
                'plain_english_summary' => $threshold === 'overdue'
                    ? "{$asset->name} is overdue for renewal ({$asset->renewal_date})."
                    : "{$asset->name} is due for renewal in {$daysLeft} day(s) ({$asset->renewal_date}).",
                'what_happened'              => 'Renewal date reached from the asset registry — no inbound email for this one.',
                'action_needed'              => 'Confirm renewal status with the client and follow up as needed.',
                'due_date_or_deadline'       => $asset->renewal_date,
                'risk_if_ignored'            => 'Service interruption or an unplanned lapse in coverage.',
                'urgency'                    => $urgency,
                'questions_for_memory_lookup'=> [],
            ],
        ));

        UnitPlatform::commitOutput($tx->tx_id, new WorkerOutput(
            stage:  'classify',
            status: 'classifying',
            data:   [
                'category'            => $this->categoryForAssetType($asset->type),
                'subcategory'         => $asset->type,
                'priority'            => $urgency,
                'required_action'     => 'Confirm renewal',
                'register_to_update'  => 'renewal_register',
                'status'              => 'Renewal Watch',
                'reason'              => "Asset expiry threshold crossed ({$threshold})",
            ],
        ));

        UnitPlatform::commitOutput($tx->tx_id, new WorkerOutput(
            stage:  'memory',
            status: 'memory_lookup',
            data:   [
                'asset'                      => $asset->name,
                'matched_client'             => $client->name ?? null,
                'primary_contact_name'       => $contact->name  ?? null,
                'primary_contact_email'      => $contact->email ?? null,
                'related_project_or_service' => $asset->vendor,
                'client_preference'          => null,
                'ava_rule'                   => null,
                'matched_rule_id'            => null,
                // Certain — the asset record is already known, there's no AI
                // match to be uncertain about.
                'confidence'                 => 100,
                'missing_information'        => $contact ? [] : ['No contact on file for this client'],
                'rule_requires_approval'     => true,
            ],
        ));

        UnitPlatform::log('ava', $tx->tx_id, 'asset_watch_triggered', [
            'asset_id' => $asset->id, 'threshold' => $threshold, 'days_left' => $daysLeft,
        ]);

        UnitPlatform::advance($tx->tx_id, 'memory');
    }

    private function categoryForAssetType(?string $type): string
    {
        return match (strtolower($type ?? '')) {
            'domain'  => 'Domain Renewal',
            'ssl'     => 'SSL Expiry',
            'hosting' => 'Hosting Invoice',
            'saas'    => 'SaaS Renewal',
            default   => 'Other',
        };
    }
}

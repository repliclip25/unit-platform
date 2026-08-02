<?php

namespace App\Workers\AVA\Services;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use Illuminate\Support\Facades\DB;

/**
 * Builds a real (non-Fast-Track) AVA transaction straight from an asset
 * record — no inbound email exists for either trigger that uses this:
 * AssetExpiryWatchJob (a watch threshold crossed on the daily scan) and a
 * tenant's own "Renew Now" action (Human Trigger — on demand, any time,
 * e.g. a client asks directly and the tenant wants to push it into the
 * pipeline right now instead of waiting for tomorrow's scan). Same
 * synthesis either way; only the source label and urgency basis differ.
 */
class AssetTransactionSynthesizer
{
    public static function create(object $asset, object $dep, string $source, ?string $threshold = null): object
    {
        $daysLeft = $asset->renewal_date
            ? (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($asset->renewal_date)->startOfDay(), false)
            : null;

        $urgency = match (true) {
            $threshold === 'overdue' => 'Critical',
            in_array($threshold, ['1', '7'], true) => 'High',
            $threshold === '14' => 'Medium',
            $threshold === '30' => 'Low',
            // Human Trigger has no watch threshold — base urgency on the
            // real days left the same way; a tenant explicitly asking for
            // this now is itself a signal, so default to High if there's
            // no renewal date to judge by at all.
            $daysLeft === null => 'High',
            $daysLeft < 0      => 'Critical',
            $daysLeft <= 7     => 'High',
            $daysLeft <= 14    => 'Medium',
            default            => 'Low',
        };

        $client  = $asset->client_id ? DB::table('clients')->where('id', $asset->client_id)->first() : null;
        $contact = $client
            ? DB::table('contacts')->where('client_id', $client->id)->orderByDesc('is_primary')->first()
            : null;

        $tx = UnitPlatform::createTransaction('ava', [
            'source'        => $source,
            'user_id'       => $dep->user_id,
            'deployment_id' => $dep->id,
            'asset_id'      => $asset->id,
            'threshold'     => $threshold,
        ]);

        $summary = match (true) {
            $threshold === 'overdue' => "{$asset->name} is overdue for renewal ({$asset->renewal_date}).",
            $daysLeft !== null       => "{$asset->name} is due for renewal in {$daysLeft} day(s) ({$asset->renewal_date}).",
            default                  => "{$asset->name} was pushed into the pipeline manually.",
        };

        // No inbound email for either trigger — the asset record itself is
        // the source of truth, so 'read' and 'classify' are synthesized
        // directly instead of running ReadEmailJob/ClassifyEmailJob against
        // nothing.
        UnitPlatform::commitOutput($tx->tx_id, new WorkerOutput(
            stage:  'read',
            status: 'reading',
            data:   [
                'plain_english_summary' => $summary,
                'what_happened'         => $source === 'human_trigger'
                    ? 'Manually pushed into the pipeline — no inbound email for this one.'
                    : 'Renewal date reached from the asset registry — no inbound email for this one.',
                'action_needed'               => 'Confirm renewal status with the client and follow up as needed.',
                'due_date_or_deadline'        => $asset->renewal_date,
                'risk_if_ignored'             => 'Service interruption or an unplanned lapse in coverage.',
                'urgency'                     => $urgency,
                'questions_for_memory_lookup' => [],
            ],
        ));

        UnitPlatform::commitOutput($tx->tx_id, new WorkerOutput(
            stage:  'classify',
            status: 'classifying',
            data:   [
                'category'           => self::categoryForAssetType($asset->type),
                'subcategory'        => $asset->type,
                'priority'           => $urgency,
                'required_action'    => 'Confirm renewal',
                'register_to_update' => 'renewal_register',
                'status'             => 'Renewal Watch',
                'reason'             => $threshold ? "Asset expiry threshold crossed ({$threshold})" : 'Manually triggered (Renew Now)',
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
                // Certain — the asset record is already known, there's no
                // AI match to be uncertain about.
                'confidence'                 => 100,
                'missing_information'        => $contact ? [] : ['No contact on file for this client'],
                'rule_requires_approval'     => true,
            ],
        ));

        UnitPlatform::log('ava', $tx->tx_id, 'asset_transaction_synthesized', [
            'asset_id' => $asset->id, 'source' => $source, 'threshold' => $threshold, 'days_left' => $daysLeft,
        ]);

        UnitPlatform::advance($tx->tx_id, 'memory');

        return $tx;
    }

    private static function categoryForAssetType(?string $type): string
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

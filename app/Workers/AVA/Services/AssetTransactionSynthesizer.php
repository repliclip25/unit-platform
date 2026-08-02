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

        $categoryForColumn = self::categoryForAssetType($asset->type);

        UnitPlatform::commitOutput($tx->tx_id, new WorkerOutput(
            stage:    'classify',
            status:   'classifying',
            data:     [
                'category'           => $categoryForColumn,
                'subcategory'        => $asset->type,
                'priority'           => $urgency,
                'required_action'    => 'Confirm renewal',
                'register_to_update' => 'renewal_register',
                'status'             => 'Renewal Watch',
                'reason'             => $threshold ? "Asset expiry threshold crossed ({$threshold})" : 'Manually triggered (Renew Now)',
            ],
            // Named shortcuts — the ONLY thing that syncs transactions.category
            // and .priority (the Transaction Center title and the Transactions
            // list columns both read those top-level columns, not this data
            // blob). Without these, every Human Trigger / watch-synthesized
            // transaction shows "Processing..." and blank Category/Priority
            // forever, even after the renewal fully completes.
            category: $categoryForColumn,
            priority: $urgency,
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

    /**
     * Same synthesis as create(), but for an asset_groups row flagged
     * renews_together (see AssetExpiryWatchJob) — one transaction covering
     * every member, not one per asset. Real-world precedent: a tenant's
     * actual invoice can bundle a domain + SSL + hosting renewal into one
     * line-itemed bill even when the individually-tracked renewal_date on
     * each asset doesn't line up — the bundle is billed together because
     * that's how the tenant actually runs it, not because the dates match.
     * Triggered off whichever member's date comes first; every member with
     * a renewal_date joins the bundle once it fires, not just the one that
     * crossed threshold.
     */
    public static function createForGroup(array $assets, ?object $client, object $dep, string $source, ?string $threshold = null): object
    {
        $dated  = collect($assets)->filter(fn ($a) => $a->renewal_date)->sortBy('renewal_date')->values();
        $anchor = $dated->first() ?? $assets[0];

        $daysLeft = $anchor->renewal_date
            ? (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($anchor->renewal_date)->startOfDay(), false)
            : null;

        $urgency = self::resolveUrgency($threshold, $daysLeft);

        $contact = $client
            ? DB::table('contacts')->where('client_id', $client->id)->orderByDesc('is_primary')->first()
            : null;

        $lineItems = collect($assets)->map(fn ($a) => [
            'id'           => $a->id,
            'name'         => $a->name,
            'type'         => $a->type,
            'vendor'       => $a->vendor,
            'renewal_date' => $a->renewal_date,
        ])->all();

        $groupLabel = ($client->name ?? 'Bundle') . ' — ' . count($assets) . ' service' . (count($assets) === 1 ? '' : 's');

        // Same category only if every member agrees — a genuinely mixed
        // bundle (domain + hosting + SSL, as in the real Baskel example)
        // isn't any one of those things, so it falls to the generic bucket
        // rather than mislabeling it as whichever type happens to sort first.
        $types = collect($assets)->pluck('type')->unique();
        $category = $types->count() === 1 ? self::categoryForAssetType($types->first()) : 'Other';

        $tx = UnitPlatform::createTransaction('ava', [
            'source'        => $source,
            'user_id'       => $dep->user_id,
            'deployment_id' => $dep->id,
            'asset_ids'     => collect($assets)->pluck('id')->all(),
            'threshold'     => $threshold,
        ]);

        $summary = match (true) {
            $threshold === 'overdue' => "{$groupLabel} is overdue for renewal ({$anchor->renewal_date}).",
            $daysLeft !== null       => "{$groupLabel} is due for renewal in {$daysLeft} day(s) ({$anchor->renewal_date}).",
            default                  => "{$groupLabel} was pushed into the pipeline manually.",
        };

        UnitPlatform::commitOutput($tx->tx_id, new WorkerOutput(
            stage:  'read',
            status: 'reading',
            data:   [
                'plain_english_summary' => $summary,
                'what_happened'         => 'These assets renew together — bundled into one transaction instead of one per asset.',
                'action_needed'               => 'Confirm renewal status with the client and follow up as needed.',
                'due_date_or_deadline'        => $anchor->renewal_date,
                'risk_if_ignored'             => 'Service interruption or an unplanned lapse in coverage across the bundle.',
                'urgency'                     => $urgency,
                'questions_for_memory_lookup' => [],
            ],
        ));

        UnitPlatform::commitOutput($tx->tx_id, new WorkerOutput(
            stage:    'classify',
            status:   'classifying',
            data:     [
                'category'           => $category,
                'subcategory'        => 'bundle',
                'priority'           => $urgency,
                'required_action'    => 'Confirm renewal',
                'register_to_update' => 'renewal_register',
                'status'             => 'Renewal Watch',
                'reason'             => $threshold ? "Asset expiry threshold crossed ({$threshold})" : 'Manually triggered (Renew Now)',
            ],
            // Same shortcut-param requirement as create() above — without
            // these, transactions.category/.priority never get set, and
            // the transaction shows "Processing..." forever even after the
            // renewal fully completes (confirmed live on TX-80E589DD).
            category: $category,
            priority: $urgency,
        ));

        UnitPlatform::commitOutput($tx->tx_id, new WorkerOutput(
            stage:  'memory',
            status: 'memory_lookup',
            data:   [
                'asset'                      => $groupLabel,
                'line_items'                 => $lineItems,
                'matched_client'             => $client->name ?? null,
                'primary_contact_name'       => $contact->name  ?? null,
                'primary_contact_email'      => $contact->email ?? null,
                'related_project_or_service' => null,
                'client_preference'          => null,
                'ava_rule'                   => null,
                'matched_rule_id'            => null,
                'confidence'                 => 100,
                'missing_information'        => $contact ? [] : ['No contact on file for this client'],
                'rule_requires_approval'     => true,
            ],
        ));

        UnitPlatform::log('ava', $tx->tx_id, 'asset_group_transaction_synthesized', [
            'asset_ids' => collect($assets)->pluck('id')->all(), 'source' => $source, 'threshold' => $threshold, 'days_left' => $daysLeft,
        ]);

        UnitPlatform::advance($tx->tx_id, 'memory');

        return $tx;
    }

    private static function resolveUrgency(?string $threshold, ?int $daysLeft): string
    {
        return match (true) {
            $threshold === 'overdue' => 'Critical',
            in_array($threshold, ['1', '7'], true) => 'High',
            $threshold === '14' => 'Medium',
            $threshold === '30' => 'Low',
            $daysLeft === null => 'High',
            $daysLeft < 0      => 'Critical',
            $daysLeft <= 7     => 'High',
            $daysLeft <= 14    => 'Medium',
            default            => 'Low',
        };
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

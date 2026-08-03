<?php

namespace App\Platform\SDK\Columns;

/**
 * See TransactionColumns' docblock for why this exists and why it's scoped
 * narrowly rather than mirroring the whole `asset_groups` schema.
 */
final class AssetGroupColumns
{
    // Explicit, tenant-set flag — NOT inferred from renewal-date proximity.
    // Read by AssetExpiryWatchJob (clusters group members before the
    // per-asset loop) and AssetGroupController (Renew Group Now, Fix
    // Dates). See AVA.md's "Asset Groups & Bundled Renewals".
    public const RENEWS_TOGETHER = 'renews_together';
}

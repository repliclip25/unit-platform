<?php

namespace App\Platform\SDK\Columns;

/**
 * Column name constants for the `transactions` table — not an exhaustive
 * mirror of the schema (that's DATABASE.md's job), only the columns worth
 * protecting from a silent typo or rename: no Eloquent model exists for
 * this table (per project convention, see CLAUDE.md), so every reference
 * anywhere in the app is a raw string literal with zero compile-time
 * safety. 199 files reference 'transactions' directly — retrofitting all
 * of them would be disproportionate to the actual risk. Scoped instead to
 * the columns added this session, which are the least battle-tested and
 * most likely to be renamed or mistyped next: `cadence_skipped` (Approve
 * & proceed) and `notify_customer_output` (the notify_customer stage).
 *
 * Add a constant here when a new column becomes load-bearing across more
 * than one file — that's the actual trigger for "this is worth
 * protecting," not "every column should eventually be listed."
 */
final class TransactionColumns
{
    // Set by TransactionController::decide() when "Approve & proceed" fires
    // — read by NotifyCustomerJob to suppress its send, and by
    // ArchiveEvidenceJob to explain why the reminder cadence is incomplete.
    public const CADENCE_SKIPPED = 'cadence_skipped';

    // Written by NotifyCustomerJob, read by the Transaction Center and
    // ArchiveEvidenceJob's "sent" check.
    public const NOTIFY_CUSTOMER_OUTPUT = 'notify_customer_output';
}

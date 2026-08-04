<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\Columns\TransactionColumns;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Runs daily per active AVA deployment. The client-facing renewal draft is
 * sent up to 3 times on a real calendar cadence — 30, 15, and 0 days before
 * the asset expires — not once. Approving round 1 authorizes the whole
 * cadence, not just that one message — this job watches every transaction
 * that's been approved once and is waiting on the next threshold; once the
 * asset's renewal_date crosses it, re-drafts (DraftEmailJob, reused as-is)
 * and lets PushToGmailJob decide whether to continue automatically or halt
 * (see that job's own docblock — it checks human_decision itself, this job
 * no longer resets it). Fast Track never reaches here — it always treats
 * its one draft as final (see TransactionController::decide()), since it
 * doesn't simulate real calendar days.
 */
class ClientReminderCycleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    // client_reminder_number => days-before-expiry threshold for the NEXT round
    private const NEXT_THRESHOLD = [
        1 => 15,
        2 => 0,
    ];

    public function __construct(public int $deploymentId) {}

    public function handle(): void
    {
        $dep = DB::table('worker_deployments')->where('id', $this->deploymentId)->first();
        if (!$dep || $dep->status !== 'active') return;

        // Message gate — with client_cadence off, decide() already treats
        // round 1 as final, so no transaction should ever be sitting here
        // waiting on a next round for this deployment. Checked explicitly
        // anyway rather than relying on that as the only guarantee.
        if (!UnitPlatform::gateEnabled($dep->id, 'client_cadence', true)) return;

        $waiting = DB::table('transactions')
            ->where('deployment_id', $this->deploymentId)
            ->where('fulfillment_stage', 'human_decide')
            ->where('human_decision', 'approved')
            ->where(TransactionColumns::CADENCE_STOPPED, false)
            ->where('is_test', false)
            ->whereIn('client_reminder_number', [1, 2])
            ->get();

        foreach ($waiting as $tx) {
            $nextThreshold = self::NEXT_THRESHOLD[$tx->client_reminder_number] ?? null;
            if ($nextThreshold === null) continue;

            $memory    = json_decode($tx->memory_output ?? '{}', true) ?: [];
            $assetName = $memory['asset'] ?? null;
            $asset     = $assetName
                ? DB::table('assets')->where('user_id', $tx->user_id)->where('name', $assetName)->whereNull('deleted_at')->first()
                : null;

            if (!$asset || !$asset->renewal_date) continue;

            $daysLeft = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($asset->renewal_date)->startOfDay(), false);
            if ($daysLeft > $nextThreshold) continue; // not time yet

            // human_decision stays 'approved' — that one decision authorizes
            // the whole cadence now, not just this round. status is still
            // reset so this round's redraft looks fresh while it's in
            // flight; PushToGmailJob (not this job) decides afterward
            // whether to auto-continue or, for the final round, resume
            // advance() into fulfillment.
            DB::table('transactions')->where('id', $tx->id)->update([
                'status'     => 'draft_ready',
                'updated_at' => now(),
            ]);

            $queue = UnitPlatform::getInput($tx->tx_id)->queue;
            \App\Workers\AVA\Jobs\DraftEmailJob::dispatch($tx->tx_id, $nextThreshold)->onQueue($queue);

            UnitPlatform::log('ava', $tx->tx_id, 'client_reminder_cycle_started', [
                'reminder_number' => $tx->client_reminder_number + 1,
                'days_before_expiry' => $nextThreshold,
            ]);
        }
    }
}

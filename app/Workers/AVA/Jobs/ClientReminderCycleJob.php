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
 * Runs daily per active AVA deployment. The client-facing renewal draft is
 * sent up to 3 times on a real calendar cadence — 30, 15, and 0 days before
 * the asset expires — not once. This job watches transactions that have
 * approved their current reminder and are waiting on the next threshold;
 * once the asset's renewal_date crosses it, re-drafts (DraftEmailJob,
 * reused as-is) and reopens the Approve & Send gate for that next round.
 * Fast Track never reaches here — it always treats its one draft as final
 * (see TransactionController::decide()), since it doesn't simulate real
 * calendar days.
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

        $waiting = DB::table('transactions')
            ->where('deployment_id', $this->deploymentId)
            ->where('fulfillment_stage', 'human_decide')
            ->where('human_decision', 'approved')
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

            // Reopen the gate for a fresh round — reset to draft_ready so it
            // looks and behaves exactly like a transaction awaiting its
            // first decision, then let the normal pipeline machinery
            // (DraftEmailJob -> advance -> PushToGmailJob -> advance) redraft
            // and re-halt at human_decide on its own.
            DB::table('transactions')->where('id', $tx->id)->update([
                'status'         => 'draft_ready',
                'human_decision' => null,
                'updated_at'     => now(),
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

<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\Services\EmailDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Runs daily per active AVA deployment. Finds transactions stuck at the
 * 'confirm_payment' pause point and re-nudges the tenant — AVA "hunts for
 * user actions via email reminders on various cadence based on priority"
 * rather than silently waiting forever for a click.
 */
class PaymentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    // Days between reminders, by classify_output.priority — more urgent
    // renewals get chased more often.
    private const CADENCE_DAYS = [
        'Critical' => 1,
        'High'     => 2,
        'Medium'   => 4,
        'Low'      => 7,
    ];

    public function __construct(public int $deploymentId) {}

    public function handle(): void
    {
        $dep = DB::table('worker_deployments')->where('id', $this->deploymentId)->first();
        if (!$dep || $dep->status !== 'active') return;

        $stuck = DB::table('transactions')
            ->where('deployment_id', $this->deploymentId)
            ->where('fulfillment_stage', 'confirm_payment')
            ->get();

        foreach ($stuck as $tx) {
            $priority = $tx->priority ?? 'Medium';
            $cadence  = self::CADENCE_DAYS[$priority] ?? self::CADENCE_DAYS['Medium'];

            $lastSent = $tx->payment_reminder_sent_at ?? $tx->updated_at;
            if (now()->diffInDays($lastSent) < $cadence) continue;

            $tenantEmail = DB::table('users')->where('id', $tx->user_id)->value('email');
            if (!$tenantEmail) continue;

            $memory = json_decode($tx->memory_output ?? '{}', true) ?: [];

            EmailDispatcher::send(
                'ava_payment_reminder',
                $tenantEmail,
                'there',
                $tx->user_id,
                ['{asset}' => $memory['asset'] ?? 'a renewal', '{tx_id}' => $tx->tx_id],
                [
                    'subject' => 'Action needed — confirm payment for ' . ($memory['asset'] ?? $tx->tx_id),
                    'body'    => "Hi,\n\nStill waiting to hear back — has the renewal for "
                        . ($memory['asset'] ?? 'this item') . " gone through? Confirm it in UNIT so AVA can close it out.\n\n— AVA",
                ]
            );

            DB::table('transactions')->where('id', $tx->id)->update(['payment_reminder_sent_at' => now()]);

            UnitPlatform::log('ava', $tx->tx_id, 'payment_reminder_sent', ['priority' => $priority, 'cadence_days' => $cadence]);
        }
    }
}

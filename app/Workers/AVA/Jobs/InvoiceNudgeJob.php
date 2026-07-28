<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\Services\EmailDispatcher;
use App\Platform\Services\ReminderCopy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

/**
 * Runs daily per active AVA deployment. Request Invoice is a soft gate —
 * the pipeline never waits on it — but AVA still chases the tenant to
 * attach one if they haven't, same escalating cadence and pause-after-N
 * pattern as the two hard gates. Never blocks anything; a transaction can
 * be fully renewed with an unattached invoice.
 */
class InvoiceNudgeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    private const CADENCE_DAYS = [
        'Critical' => 2,
        'High'     => 3,
        'Medium'   => 5,
        'Low'      => 7,
    ];

    public function __construct(public int $deploymentId) {}

    public function handle(): void
    {
        $dep = DB::table('worker_deployments')->where('id', $this->deploymentId)->first();
        if (!$dep || $dep->status !== 'active') return;

        $notAttached = DB::table('transactions')
            ->where('deployment_id', $this->deploymentId)
            ->where('is_test', false)
            ->whereNull('nudging_paused_at')
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(invoice_output, '$.status')) = 'not_attached'")
            ->get();

        foreach ($notAttached as $tx) {
            $priority = $tx->priority ?? 'Medium';
            $cadence  = self::CADENCE_DAYS[$priority] ?? self::CADENCE_DAYS['Medium'];

            $reminders  = json_decode($tx->reminders ?? '[]', true) ?: [];
            $invoiceReminders = array_filter($reminders, fn($r) => ($r['stage_key'] ?? null) === 'request_invoice');
            $lastSent   = collect($invoiceReminders)->last()['sent_at'] ?? $tx->updated_at;

            if (\Carbon\Carbon::parse($lastSent)->gt(now()->subDays($cadence))) continue;

            $tenantEmail = DB::table('users')->where('id', $tx->user_id)->value('email');
            if (!$tenantEmail) continue;

            $memory  = json_decode($tx->memory_output ?? '{}', true) ?: [];
            $asset   = $memory['asset'] ?? 'this item';
            $attempt = count($invoiceReminders) + 1;

            if ($attempt > ReminderCopy::MAX_ATTEMPTS) {
                DB::table('transactions')->where('id', $tx->id)->update(['nudging_paused_at' => now()]);
                UnitPlatform::log('ava', $tx->tx_id, 'nudging_paused', ['stage_key' => 'request_invoice', 'attempts' => $attempt - 1], 'warning');
                continue;
            }

            [$subject, $body] = match (ReminderCopy::tone($attempt)) {
                'gentle' => [
                    "Got an invoice for {$asset}?",
                    "Hi,\n\nIf you have an invoice for the {$asset} renewal, attach it in UNIT whenever it's convenient — no rush, this won't hold anything up.\n\n— AVA",
                ],
                'direct' => [
                    "Still no invoice attached — {$asset}",
                    "Hi,\n\nFollowing up — still no invoice attached for {$asset}. If one exists, attach it in UNIT so it's on file with the renewal.\n\n— AVA",
                ],
                default => [
                    "Last check — invoice for {$asset}?",
                    "Hi,\n\nOne more check-in — if there's an invoice for {$asset}, attach it in UNIT. If not, no action needed.\n\n— AVA",
                ],
            };

            EmailDispatcher::send(
                'ava_invoice_nudge', $tenantEmail, 'there', $tx->user_id,
                ['{asset}' => $asset, '{tx_id}' => $tx->tx_id], ['subject' => $subject, 'body' => $body]
            );

            UnitPlatform::recordReminder($tx->tx_id, 'request_invoice', $subject, $body);
            UnitPlatform::log('ava', $tx->tx_id, 'invoice_nudge_sent', ['priority' => $priority, 'attempt' => $attempt]);
        }
    }
}

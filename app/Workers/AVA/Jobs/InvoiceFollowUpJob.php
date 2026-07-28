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
 * Runs daily per active AVA deployment. Once an invoice is attached, AVA's
 * first message to the client goes out immediately (attachInvoice()) — this
 * job sends up to 2 follow-ups after that on a fixed 3-day interval, since
 * there's no inbound-reply detection to know if the client already acted.
 * Client-facing, not the tenant nudge (see InvoiceNudgeJob) — distinct
 * audience, distinct purpose.
 */
class InvoiceFollowUpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    private const MAX_FOLLOW_UPS  = 3; // total messages including the first
    private const INTERVAL_DAYS   = 3;

    public function __construct(public int $deploymentId) {}

    public function handle(): void
    {
        $dep = DB::table('worker_deployments')->where('id', $this->deploymentId)->first();
        if (!$dep || $dep->status !== 'active') return;

        $attached = DB::table('transactions')
            ->where('deployment_id', $this->deploymentId)
            ->where('is_test', false)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(invoice_output, '$.status')) = 'attached'")
            ->get();

        foreach ($attached as $tx) {
            $invoiceOutput = json_decode($tx->invoice_output ?? '{}', true) ?: [];
            $messages      = $invoiceOutput['client_messages'] ?? [];
            if (count($messages) >= self::MAX_FOLLOW_UPS) continue;

            $last = end($messages);
            if (\Carbon\Carbon::parse($last['sent_at'])->gt(now()->subDays(self::INTERVAL_DAYS))) continue;

            $clientEmail = $last['to'] ?? null;
            if (!$clientEmail) continue;

            $memory  = json_decode($tx->memory_output ?? '{}', true) ?: [];
            $asset   = $memory['asset'] ?? 'this renewal';
            $seq     = count($messages) + 1;
            $subject = "Following up — invoice for {$asset}";
            $body    = "Hi,\n\nFollowing up on the invoice sent for {$asset}. Let us know if you need anything further.\n\nBest regards,\nFranklin";

            EmailDispatcher::send(
                'ava_invoice_client_followup', $clientEmail, $memory['matched_client'] ?? 'there', null,
                ['{asset}' => $asset], ['subject' => $subject, 'body' => $body]
            );

            $messages[]              = ['sequence' => $seq, 'to' => $clientEmail, 'subject' => $subject, 'body' => $body, 'sent_at' => now()->toISOString()];
            $invoiceOutput['client_messages'] = $messages;

            DB::table('transactions')->where('id', $tx->id)->update([
                'invoice_output' => json_encode($invoiceOutput, JSON_INVALID_UTF8_SUBSTITUTE),
                'updated_at'     => now(),
            ]);

            UnitPlatform::log('ava', $tx->tx_id, 'invoice_client_followup_sent', ['sequence' => $seq]);
        }
    }
}

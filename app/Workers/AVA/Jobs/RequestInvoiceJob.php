<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use App\Platform\Services\EmailDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Stage 10 — Request/Attach Invoice. Never blocks: this is a best-effort
 * request, not a gate. If there's a vendor address to ask (the email this
 * renewal came from), AVA sends the request and marks it 'requested'. If
 * there's nothing to ask (e.g. an asset-watch-triggered transaction with no
 * inbound email), it's marked 'not_applicable' and the pipeline continues
 * immediately — "not every transaction needs the full pipeline."
 */
class RequestInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input   = UnitPlatform::getInput($this->txId);
        $memory  = $input->stage('memory');
        $vendorEmail = $input->raw['from'] ?? null;

        if ($input->isFastTrack()) {
            // Fast Track lets a tenant preview the full lifecycle end-to-end,
            // but it must never email a real vendor address — simulate the
            // outcome instead of actually sending. If the scenario provided a
            // sample invoice, show that as what the vendor would attach.
            $sample = $input->raw['fast_track_invoice_sample'] ?? null;
            $output = [
                'status'       => 'simulated',
                'to'           => $vendorEmail,
                'requested_at' => now()->toISOString(),
                'sample'       => $sample ?: 'No sample invoice provided in the test scenario — this is what would be requested from the vendor on a real renewal.',
            ];
        } elseif ($vendorEmail && filter_var($vendorEmail, FILTER_VALIDATE_EMAIL)) {
            EmailDispatcher::send(
                'ava_request_invoice',
                $vendorEmail,
                $memory['matched_client'] ?? 'there',
                null,
                ['{asset}' => $memory['asset'] ?? 'this renewal', '{sender}' => 'Franklin'],
                [
                    'subject' => 'Invoice request — ' . ($memory['asset'] ?? 'renewal'),
                    'body'    => "Hi,\n\nCould you send over the invoice for the upcoming renewal of "
                        . ($memory['asset'] ?? 'this item') . "?\n\nThanks,\nFranklin",
                ]
            );

            $output = ['status' => 'requested', 'to' => $vendorEmail, 'requested_at' => now()->toISOString()];
        } else {
            $output = ['status' => 'not_applicable', 'reason' => 'No vendor address available for this transaction'];
        }

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(stage: 'request_invoice', data: $output));
        UnitPlatform::setFulfillmentStage($this->txId, 'request_invoice');
        UnitPlatform::log('ava', $this->txId, 'invoice_requested', $output);

        UnitPlatform::advance($this->txId, 'request_invoice');
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::log('ava', $this->txId, 'job_failed', ['job' => 'RequestInvoiceJob', 'error' => $e->getMessage()], 'error');
        // Never block fulfillment on a failed best-effort request — continue.
        UnitPlatform::advance($this->txId, 'request_invoice');
    }
}

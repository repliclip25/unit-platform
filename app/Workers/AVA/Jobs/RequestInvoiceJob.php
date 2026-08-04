<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Stage 10 — Request Invoice (gate_type: soft). No longer emails a vendor —
 * AVA surfaces an "Attach invoice" button for the tenant instead
 * (TransactionController::attachInvoice()). This job just marks the
 * starting state and moves on immediately; the pipeline never waits here.
 * InvoiceNudgeJob chases the tenant separately on a cadence if they don't
 * act, same pattern as the human-gate reminders.
 */
class RequestInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input = UnitPlatform::getInput($this->txId);

        // Fast Track previews the outcome (a sample invoice, if the test
        // scenario provided one) rather than sitting at "not attached" —
        // there's no real upload flow possible in a synchronous demo run.
        $output = $input->isFastTrack()
            ? [
                'status'  => 'simulated',
                'sample'  => $input->raw['fast_track_invoice_sample'] ?? 'No sample invoice provided in the test scenario — this is what a tenant would attach on a real renewal.',
              ]
            : ['status' => 'not_attached'];
        $output['opened_at'] = now()->toISOString();

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(stage: 'request_invoice', data: $output));
        UnitPlatform::setFulfillmentStage($this->txId, 'request_invoice');
        UnitPlatform::log('ava', $this->txId, 'invoice_stage_opened', $output);

        UnitPlatform::advance($this->txId, 'request_invoice');
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::log('ava', $this->txId, 'job_failed', ['job' => 'RequestInvoiceJob', 'error' => $e->getMessage()], 'error');
        UnitPlatform::advance($this->txId, 'request_invoice');
    }
}

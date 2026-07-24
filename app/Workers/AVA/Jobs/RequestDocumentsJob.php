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
 * Stage 11 — Request/Attach Supporting Documents. Same best-effort pattern
 * as RequestInvoiceJob — never blocks the pipeline. Actual document
 * collection (merging whatever came in, invoice included, into one PDF) is
 * ArchiveEvidenceJob's job at stage 14, not here — this stage only sends the
 * request.
 */
class RequestDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input       = UnitPlatform::getInput($this->txId);
        $memory      = $input->stage('memory');
        $vendorEmail = $input->raw['from'] ?? null;

        if ($vendorEmail && filter_var($vendorEmail, FILTER_VALIDATE_EMAIL)) {
            EmailDispatcher::send(
                'ava_request_documents',
                $vendorEmail,
                $memory['matched_client'] ?? 'there',
                null,
                ['{asset}' => $memory['asset'] ?? 'this renewal', '{sender}' => 'Franklin'],
                [
                    'subject' => 'Supporting documents — ' . ($memory['asset'] ?? 'renewal'),
                    'body'    => "Hi,\n\nCould you send any supporting documents needed for the renewal of "
                        . ($memory['asset'] ?? 'this item') . " (certificate, contract, or similar)?\n\nThanks,\nFranklin",
                ]
            );

            $output = ['status' => 'requested', 'to' => $vendorEmail, 'requested_at' => now()->toISOString()];
        } else {
            $output = ['status' => 'not_applicable', 'reason' => 'No vendor address available for this transaction'];
        }

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(stage: 'request_documents', data: $output));
        UnitPlatform::setFulfillmentStage($this->txId, 'request_documents');
        UnitPlatform::log('ava', $this->txId, 'documents_requested', $output);

        // This is where the pipeline pauses — the next stage in the contract,
        // 'confirm_payment', is a pauses_pipeline stage. advance() will stop
        // there on its own; TransactionController::confirmRenewal()/
        // cancelRenewal() is what resumes it.
        UnitPlatform::advance($this->txId, 'request_documents');
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::log('ava', $this->txId, 'job_failed', ['job' => 'RequestDocumentsJob', 'error' => $e->getMessage()], 'error');
        UnitPlatform::advance($this->txId, 'request_documents');
    }
}

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
 * Stage 11 — Request Documents (gate_type: skippable). A yes/no moment, not
 * a vendor request: "any documents to send the client?" Never blocks the
 * pipeline either way — TransactionController::skipDocuments()/
 * attachDocuments() resolve it whenever the tenant gets to it.
 */
class RequestDocumentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        UnitPlatform::stageStarted($this->txId, 'request_documents');

        $input = UnitPlatform::getInput($this->txId);

        $output = $input->isFastTrack()
            ? ['status' => 'skipped', 'reason' => 'Fast Track always skips — no real document flow in a test run.']
            : ['status' => 'pending_decision'];

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(stage: 'request_documents', data: $output));
        UnitPlatform::setFulfillmentStage($this->txId, 'request_documents');
        UnitPlatform::log('ava', $this->txId, 'documents_stage_opened', $output);

        UnitPlatform::advance($this->txId, 'request_documents');
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::stageFailed($this->txId, 'request_documents', $e->getMessage());
        UnitPlatform::log('ava', $this->txId, 'job_failed', ['job' => 'RequestDocumentsJob', 'error' => $e->getMessage()], 'error');
        UnitPlatform::advance($this->txId, 'request_documents');
    }
}

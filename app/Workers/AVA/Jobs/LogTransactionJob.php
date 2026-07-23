<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input    = UnitPlatform::getInput($this->txId);
        $classify = $input->stage('classify');
        $memory   = $input->stage('memory');
        $read     = $input->stage('read');

        // Advance status immediately so polling never stalls on memory_lookup
        UnitPlatform::setStatus($this->txId, 'logging');

        // Best-effort — never block the pipeline for a logging failure
        try {
            UnitPlatform::register($this->txId, [
                'category' => $classify['category']           ?? 'Unknown',
                'asset'    => $memory['asset']                ?? 'Unknown',
                'client'   => $memory['matched_client']       ?? 'Unknown',
                'contact'  => $memory['primary_contact_name'] ?? 'Unknown',
                'due_date' => $this->parseDueDate($read),
                'priority' => $classify['priority']           ?? 'Medium',
                'status'   => 'Logged',
            ]);

            UnitPlatform::log('ava', $this->txId, 'transaction_logged', [
                'category' => $classify['category'] ?? null,
                'asset'    => $memory['asset']      ?? null,
            ]);
        } catch (\Throwable $e) {
            UnitPlatform::log('ava', $this->txId, 'log_transaction_skipped', [
                'error' => $e->getMessage(),
            ], 'warning');
        }

        UnitPlatform::advance($this->txId, 'log_entry');
    }

    public function failed(\Throwable $e): void
    {
        if ($e instanceof \App\Platform\Exceptions\BillingException) {
            UnitPlatform::setStatus($this->txId, 'blocked');
            UnitPlatform::log('ava', $this->txId, 'billing_blocked', ['code' => $e->billingCode, 'reason' => $e->getMessage()], 'warning');
            $this->delete();
            return;
        }
        UnitPlatform::setStatus($this->txId, 'failed');
        UnitPlatform::log('ava', $this->txId, 'job_failed', [
            'job' => 'LogTransactionJob', 'error' => $e->getMessage(),
        ], 'error');
    }

    private function parseDueDate(array $read): ?string
    {
        $dateStr = $read['due_date_or_deadline'] ?? null;
        if (!$dateStr) return null;
        try {
            return \Carbon\Carbon::parse($dateStr)->toDateString();
        } catch (\Exception) {
            return null;
        }
    }
}

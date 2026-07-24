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
 * Stage 15 — Notify Stakeholders. Email only for V1 (Slack/Teams/SMS are a
 * V2 project, per agreed scope) — reuses EmailDispatcher, the same
 * infrastructure every other AVA notification already goes through.
 */
class NotifyStakeholdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input  = UnitPlatform::getInput($this->txId);
        $memory = $input->stage('memory');

        $subject = 'Renewal complete — ' . ($memory['asset'] ?? $this->txId);
        $body    = "Hi,\n\nThe renewal for " . ($memory['asset'] ?? 'this item')
            . ($memory['matched_client'] ? " ({$memory['matched_client']})" : '')
            . " is complete. The next cycle is already being watched.\n\n— AVA";

        // Fast Track runs every stage for real so the tenant can preview the
        // full lifecycle, but must never actually spam their own inbox every
        // time they click "Run Fast Track" — draft the message, don't send it.
        if ($input->tenantEmail && !$input->isFastTrack()) {
            EmailDispatcher::send(
                'ava_renewal_complete',
                $input->tenantEmail,
                'there',
                $input->userId,
                ['{asset}' => $memory['asset'] ?? 'your renewal', '{client}' => $memory['matched_client'] ?? ''],
                ['subject' => $subject, 'body' => $body]
            );
        }

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage: 'notify_stakeholders',
            data:  ['to' => $input->tenantEmail, 'subject' => $subject, 'body' => $body, 'sent' => !$input->isFastTrack()],
        ));
        UnitPlatform::setFulfillmentStage($this->txId, 'notify_stakeholders');
        UnitPlatform::log('ava', $this->txId, 'stakeholders_notified', ['to' => $input->tenantEmail]);

        UnitPlatform::advance($this->txId, 'notify_stakeholders');
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::log('ava', $this->txId, 'job_failed', ['job' => 'NotifyStakeholdersJob', 'error' => $e->getMessage()], 'error');
        UnitPlatform::advance($this->txId, 'notify_stakeholders');
    }
}

<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use App\Platform\Services\EmailDispatcher;
use App\Platform\Services\TemplateResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
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

        $asset        = $memory['asset'] ?? $this->txId;
        $clientSuffix = $memory['matched_client'] ? " ({$memory['matched_client']})" : '';

        $userTemplates = DB::table('email_templates')
            ->where('user_id', $input->userId)->where('worker_slug', $input->workerSlug)
            ->where('stage_key', 'notify_stakeholders')->where('active', true)->get()->all();
        $defaultTemplates = DB::table('email_templates')
            ->whereNull('user_id')->where('worker_slug', $input->workerSlug)
            ->where('stage_key', 'notify_stakeholders')->get()->all();
        $template = TemplateResolver::resolveByStage($userTemplates, $defaultTemplates);

        $subject = str_replace('{{asset}}', $asset, $template['subject_template'] ?? "Renewal complete — {$asset}");
        $body    = str_replace(
            ['{{asset}}', '{{client_suffix}}'],
            [$asset, $clientSuffix],
            $template['body_template'] ?? "Hi,\n\nThe renewal for {$asset}{$clientSuffix} is complete. The next cycle is already being watched.\n\n— AVA"
        );

        // Message gate — defaults enabled, preserving today's live behavior.
        // The stage itself still runs and advances the pipeline either way;
        // this only controls whether the email actually goes out.
        $gateOn = UnitPlatform::gateEnabled($input->deploymentId, 'notify_stakeholders', true);

        // Fast Track runs every stage for real so the tenant can preview the
        // full lifecycle, but must never actually spam their own inbox every
        // time they click "Run Fast Track" — draft the message, don't send it.
        if ($gateOn && $input->tenantEmail && !$input->isFastTrack()) {
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
            data:  ['to' => $input->tenantEmail, 'subject' => $subject, 'body' => $body, 'sent' => $gateOn && !$input->isFastTrack(), 'resolved_at' => now()->toISOString()],
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

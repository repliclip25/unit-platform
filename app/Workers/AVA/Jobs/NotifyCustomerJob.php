<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use App\Platform\SDK\Columns\TransactionColumns;
use App\Platform\Services\EmailDispatcher;
use App\Platform\Services\TemplateResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * The first message that goes to the actual CLIENT rather than the tenant
 * — notify_stakeholders tells the tenant a renewal closed out, this tells
 * their customer the same thing, plus the next renewal date and roughly
 * when they'll hear from AVA again (30 days before that date, matching
 * the client cadence everywhere else). Brand new capability, so gated off
 * by default (see 'notify_customer' in AVA Settings) — a tenant opts in
 * rather than every existing deployment suddenly emailing clients it
 * never used to. The stage always runs regardless (so archive/
 * schedule_next_watch keep moving) — only the send itself is gated.
 */
class NotifyCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        UnitPlatform::stageStarted($this->txId, 'notify_customer');

        $input   = UnitPlatform::getInput($this->txId);
        $memory  = $input->stage('memory');
        $renewal = $input->stage('update_renewal_date');

        $asset        = $memory['asset'] ?? $this->txId;
        $clientEmail  = $memory['primary_contact_email'] ?? null;
        $contactName  = $memory['primary_contact_name'] ?? null;
        // Same placeholder name DraftEmailJob's fillPlaceholders() already
        // uses for this — keeps the convention consistent across every
        // client-facing template, not just the drafting stage's.
        $contactFirstName = $contactName ? explode(' ', trim($contactName))[0] : 'there';

        $nextRenewalDate = $renewal['new_date'] ?? null;
        $nextReminderDate = $nextRenewalDate
            ? \Carbon\Carbon::parse($nextRenewalDate)->subDays(30)->format('M j, Y')
            : null;
        $nextRenewalDateFmt = $nextRenewalDate
            ? \Carbon\Carbon::parse($nextRenewalDate)->format('M j, Y')
            : 'the next cycle';

        $userTemplates = DB::table('email_templates')
            ->where('user_id', $input->userId)->where('worker_slug', $input->workerSlug)
            ->where('stage_key', 'notify_customer')->where('active', true)->get()->all();
        $defaultTemplates = DB::table('email_templates')
            ->whereNull('user_id')->where('worker_slug', $input->workerSlug)
            ->where('stage_key', 'notify_customer')->get()->all();
        $template = TemplateResolver::resolveByStage($userTemplates, $defaultTemplates);

        $placeholders = [
            '{{asset}}'              => $asset,
            '{{contact_first_name}}' => $contactFirstName,
            '{{next_renewal_date}}'  => $nextRenewalDateFmt,
            '{{next_reminder_date}}' => $nextReminderDate ?? 'closer to renewal',
            '{{sender_name}}'        => 'Franklin',
        ];

        $subject = str_replace(array_keys($placeholders), array_values($placeholders), $template['subject_template'] ?? "Your renewal for {$asset} is complete");
        $body    = str_replace(array_keys($placeholders), array_values($placeholders), $template['body_template'] ?? "Hi {$contactFirstName},\n\nThis confirms the renewal for {$asset} is complete. Your next renewal is due {$nextRenewalDateFmt}.\n\nBest regards,\nFranklin");

        // Message gate — off by default (opt-in), unlike every other gate
        // in this pipeline which defaults on. See class docblock.
        $gateOn = UnitPlatform::gateEnabled($input->deploymentId, 'notify_customer', false);

        // "Approve & proceed" (TransactionController::decide()) means the
        // tenant already closed this renewal with the client outside AVA —
        // an AVA-generated "your renewal is complete" email at this point
        // would be redundant or confusing, same reasoning as skipping the
        // direct-send path there. The stage still runs and commits its
        // output either way (see class docblock), only the send is skipped.
        $cadenceSkipped = (bool) DB::table('transactions')->where('tx_id', $this->txId)->value(TransactionColumns::CADENCE_SKIPPED);
        $shouldSend     = $gateOn && !$cadenceSkipped;

        // Fast Track drafts this so the tenant can preview it, but never
        // sends to a real inbox on a test run — same guard as every other
        // client-facing send.
        if ($shouldSend && $clientEmail && !$input->isFastTrack()) {
            EmailDispatcher::send(
                'ava_renewal_complete_customer',
                $clientEmail,
                $memory['primary_contact_name'] ?? 'there',
                null,
                ['{asset}' => $asset],
                ['subject' => $subject, 'body' => $body]
            );
        }

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage: 'notify_customer',
            data:  [
                'to'                => $clientEmail,
                'subject'           => $subject,
                'body'              => $body,
                'next_renewal_date' => $nextRenewalDate,
                'sent'              => $shouldSend && !$input->isFastTrack() && (bool) $clientEmail,
                'cadence_skipped'   => $cadenceSkipped,
                'template_id'       => $template['id'] ?? null,
            ],
        ));
        UnitPlatform::setFulfillmentStage($this->txId, 'notify_customer');
        UnitPlatform::log('ava', $this->txId, 'customer_notified', ['to' => $clientEmail, 'gate_on' => $gateOn, 'cadence_skipped' => $cadenceSkipped]);

        UnitPlatform::advance($this->txId, 'notify_customer');
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::stageFailed($this->txId, 'notify_customer', $e->getMessage());
        UnitPlatform::log('ava', $this->txId, 'job_failed', ['job' => 'NotifyCustomerJob', 'error' => $e->getMessage()], 'error');
        UnitPlatform::advance($this->txId, 'notify_customer');
    }
}

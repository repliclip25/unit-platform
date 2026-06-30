<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerEvent;
use App\Platform\SDK\WorkerOutput;
use App\Platform\Services\UnitNotifier;
use App\Workers\AVA\Services\GmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushToGmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input  = UnitPlatform::getInput($this->txId);
        $draft  = $input->stage('draft');
        $memory = $input->stage('memory');

        // Credential fetched fresh by UnitPlatform — never from queue payload
        $gmail = new GmailService($input->credential);

        // Resolve recipient: draft → fallback to tenant email
        $to = $draft['to'] ?? null;
        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $to = $input->tenantEmail;
        }

        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            UnitPlatform::log('ava', $this->txId, 'draft_skipped', [
                'reason' => 'No valid recipient email address',
            ]);
            return;
        }

        if ($input->isFastTrack()) {
            // Fast track: create a draft only — never send to real contacts during testing
            $subject = '[Fast Track Test] ' . ($draft['subject'] ?? 'AVA Test');
            $body    = "⚡ Fast Track Test — no real email was sent.\n\nAVA drafted this reply for your review:\n\n" . ($draft['body'] ?? '');
            $draftId = $gmail->createDraft(to: $to, subject: $subject, body: $body);

            UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                stage:        'push',
                status:       'draft_ready',
                data:         ['gmail_draft_id' => $draftId, 'to' => $to, 'fast_track' => true],
                gmailDraftId: $draftId,
            ));

            UnitPlatform::log('ava', $this->txId, 'fast_track_drafted', [
                'to' => $to, 'gmail_draft_id' => $draftId,
            ]);
        } else {
            $template        = $input->stage('template');
            $approvalRequired = (bool) ($template['approval_required'] ?? true);

            $draftId = $gmail->createDraft(
                to:      $to,
                subject: $draft['subject'] ?? '',
                body:    $draft['body']    ?? '',
            );

            if (!$approvalRequired) {
                // Auto-send: send immediately and mark as sent — no human review needed
                $gmail->sendDraft($draftId);
                $gmail->deleteDraft($draftId); // clean up now-sent draft

                UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                    stage:        'push',
                    status:       'sent',
                    data:         ['gmail_draft_id' => $draftId, 'to' => $to, 'auto_sent' => true],
                    gmailDraftId: $draftId,
                ));

                UnitPlatform::register($this->txId, ['draft_id' => $draftId, 'status' => 'Sent']);

                UnitPlatform::log('ava', $this->txId, 'draft_auto_sent', [
                    'to' => $to,
                ]);
            } else {
                // Hold for human review
                UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                    stage:        'push',
                    status:       'draft_ready',
                    data:         ['gmail_draft_id' => $draftId, 'to' => $to],
                    gmailDraftId: $draftId,
                ));

                UnitPlatform::register($this->txId, ['draft_id' => $draftId, 'status' => 'Draft Ready']);

                UnitPlatform::log('ava', $this->txId, 'draft_pushed_to_gmail', [
                    'gmail_draft_id' => $draftId, 'human_review_note' => $draft['human_review_note'] ?? null,
                ]);
            }
        }

        // ── Break-injection: fires for ALL runs (fast track and production).
        //    Full handover packet so downstream workers need no further lookups.
        $classify = $input->stage('classify');
        $eventPayload = [
            'draft' => [
                'gmail_draft_id' => $draftId,
                'subject'        => $draft['subject'] ?? null,
                'to'             => $to,
                'status'         => 'draft_created',
                'fast_track'     => $input->isFastTrack(),
                'low_confidence' => $draft['low_confidence']    ?? false,
                'review_note'    => $draft['human_review_note'] ?? null,
                'created_at'     => now()->toISOString(),
            ],
            'asset' => [
                'name'      => $memory['asset']   ?? null,
                'type'      => null,
                'registrar' => null,
                'expiry'    => null,
                'days_left' => null,
            ],
            'client'  => ['name' => $memory['matched_client'] ?? null, 'account' => null],
            'contact' => [
                'name'  => $memory['primary_contact_name']  ?? null,
                'email' => $memory['primary_contact_email'] ?? null,
                'phone' => null,
                'role'  => null,
            ],
            'service' => [
                'related_project'   => $memory['related_project_or_service'] ?? null,
                'client_preference' => $memory['client_preference'] ?? null,
                'ava_rule'          => $memory['ava_rule'] ?? null,
            ],
            'classification' => [
                'category'    => $classify['category']       ?? null,
                'subcategory' => $classify['subcategory']    ?? null,
                'priority'    => $classify['priority']       ?? null,
                'action'      => $classify['required_action'] ?? null,
            ],
            'ava' => [
                'confidence'     => $memory['confidence'] ?? null,
                'draft_ready_at' => now()->toISOString(),
            ],
        ];

        UnitPlatform::emit($this->txId, new WorkerEvent('renewal.draft_ready', $eventPayload));

        // Notify tenant via email — only for production runs (not fast track tests)
        if (!$input->isFastTrack()) {
            UnitNotifier::draftReady($this->txId, $eventPayload);
        }
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
            'job' => 'PushToGmailJob', 'error' => $e->getMessage(),
        ], 'error');
    }
}

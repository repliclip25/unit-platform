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

    /**
     * @param autoSend      Send immediately after creating the draft — true
     *                      when no approval was ever required (the old
     *                      "structurally auto-send" case), or when a human
     *                      already approved AND the deployment's send_mode
     *                      is 'direct' (the old maybeSendDirect() case,
     *                      absorbed here now that creation and sending are
     *                      one step instead of two).
     * @param advanceAfter  Continue the pipeline scan past this stage once
     *                      delivery is done. False for a non-final cadence
     *                      round — this round's draft still needs to reach
     *                      the tenant, but fulfillment shouldn't start until
     *                      the cadence actually finishes.
     */
    public function __construct(public string $txId, public bool $autoSend = false, public bool $advanceAfter = false) {}

    public function handle(): void
    {
        $input  = UnitPlatform::getInput($this->txId);
        $draft  = $input->stage('draft');
        $memory = $input->stage('memory');

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

        $draftId = null;

        // No Gmail inbox connected for this deployment — e.g. a tenant who
        // only maintains their asset registry and never granted inbox access
        // (see AssetExpiryWatchJob). Surface the draft in-app for review
        // instead of attempting a Gmail API call with no credential.
        if (!$input->credential) {
            // 'approved' not 'draft_ready' — this job never runs before a
            // decision now (human_decide precedes push_draft), so it should
            // never regress the status a decision already advanced to.
            UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                stage:  'push',
                status: 'approved',
                data:   ['to' => $to, 'in_app_only' => true],
            ));

            UnitPlatform::register($this->txId, ['status' => 'Approved']);

            UnitPlatform::log('ava', $this->txId, 'draft_ready_in_app', [
                'to' => $to, 'reason' => 'No Gmail credential connected',
            ]);
        } elseif ($input->isFastTrack()) {
            // Credential fetched fresh by UnitPlatform — never from queue payload
            $gmail = new GmailService($input->credential);

            // Fast track: create a draft only — never send to real contacts
            // during testing, regardless of $this->autoSend.
            $subject = '[Fast Track Test] ' . ($draft['subject'] ?? 'AVA Test');
            $body    = "⚡ Fast Track Test — no real email was sent.\n\nAVA drafted this reply for your review:\n\n" . ($draft['body'] ?? '');
            $draftId = $gmail->createDraft(to: $to, subject: $subject, body: $body);

            UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                stage:        'push',
                status:       'approved',
                data:         ['gmail_draft_id' => $draftId, 'to' => $to, 'fast_track' => true],
                gmailDraftId: $draftId,
            ));

            UnitPlatform::log('ava', $this->txId, 'fast_track_drafted', [
                'to' => $to, 'gmail_draft_id' => $draftId,
            ]);
        } else {
            // Credential fetched fresh by UnitPlatform — never from queue payload
            $gmail = new GmailService($input->credential);

            $draftId = $gmail->createDraft(
                to:      $to,
                subject: $draft['subject'] ?? '',
                body:    $draft['body']    ?? '',
            );

            if ($this->autoSend) {
                // Whether to send at all was already decided by the caller
                // (DraftEmailJob or TransactionController::decide()) — this
                // job only handles delivery, not whether it's warranted.
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
                UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                    stage:        'push',
                    status:       'approved',
                    data:         ['gmail_draft_id' => $draftId, 'to' => $to],
                    gmailDraftId: $draftId,
                ));

                UnitPlatform::register($this->txId, ['draft_id' => $draftId, 'status' => 'Approved']);

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

        // draft_ready notification — only for production runs (not fast track)
        if (!$input->isFastTrack()) {
            UnitNotifier::draftReady($this->txId, $eventPayload);
        }

        // First Value Email — fires after fast-track AND after first real renewal.
        // Fast-track is Ava's first completed job. The email lands in the user's inbox
        // while they're still engaged, reinforcing what just happened before they close the tab.
        UnitNotifier::maybeFirstRealRenewal($this->txId);

        // Whether to continue into fulfillment now was already decided by
        // whoever dispatched this job (DraftEmailJob for the no-approval-
        // needed and cadence-auto-continue cases, TransactionController::
        // decide() for a human's own approval) — this job only handles
        // delivery, not pipeline sequencing decisions. false means a
        // non-final cadence round: this round's draft still needed to
        // reach the tenant, but fulfillment shouldn't start until the
        // cadence actually finishes.
        if ($this->advanceAfter) {
            UnitPlatform::advance($this->txId, 'push_draft');
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

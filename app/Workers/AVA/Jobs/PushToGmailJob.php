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

        $draftId  = null;
        $autoSent = false;

        // No Gmail inbox connected for this deployment — e.g. a tenant who
        // only maintains their asset registry and never granted inbox access
        // (see AssetExpiryWatchJob). Surface the draft in-app for review
        // instead of attempting a Gmail API call with no credential.
        if (!$input->credential) {
            UnitPlatform::commitOutput($this->txId, new WorkerOutput(
                stage:  'push',
                status: 'draft_ready',
                data:   ['to' => $to, 'in_app_only' => true],
            ));

            UnitPlatform::register($this->txId, ['status' => 'Draft Ready']);

            UnitPlatform::log('ava', $this->txId, 'draft_ready_in_app', [
                'to' => $to, 'reason' => 'No Gmail credential connected',
            ]);
        } elseif ($input->isFastTrack()) {
            // Credential fetched fresh by UnitPlatform — never from queue payload
            $gmail = new GmailService($input->credential);

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
            // Credential fetched fresh by UnitPlatform — never from queue payload
            $gmail = new GmailService($input->credential);

            $template = $input->stage('template');

            // Hard gate: approval is required if ANY of these say so — the template,
            // the specific rule MemoryLookupJob matched (its real DB value, not the AI's
            // own claim), or a low-confidence memory match. Any one of them is enough to
            // force human review; none of them can be overridden by the others.
            $approvalRequired = (bool) ($template['approval_required'] ?? true)
                || (bool) ($memory['rule_requires_approval'] ?? false)
                || (bool) ($draft['low_confidence'] ?? false);

            $draftId = $gmail->createDraft(
                to:      $to,
                subject: $draft['subject'] ?? '',
                body:    $draft['body']    ?? '',
            );

            if (!$approvalRequired) {
                // Auto-send: send immediately and mark as sent — no human review needed
                $autoSent = true;
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

        // draft_ready notification — only for production runs (not fast track)
        if (!$input->isFastTrack()) {
            UnitNotifier::draftReady($this->txId, $eventPayload);
        }

        // First Value Email — fires after fast-track AND after first real renewal.
        // Fast-track is Ava's first completed job. The email lands in the user's inbox
        // while they're still engaged, reinforcing what just happened before they close the tab.
        UnitNotifier::maybeFirstRealRenewal($this->txId);

        // Fast Track now runs the real fulfillment stages too (guarded
        // per-job against real vendor/tenant emails and real asset writes)
        // so a tenant can preview the full lifecycle end-to-end, not just
        // the draft. It always lands here via the draft_ready branch above
        // (never auto-sent), so this always stops at the human_decide pause
        // point — TransactionController::decide() resumes it either way.
        if ($autoSent) {
            // Already decided structurally (no approval was required) —
            // advance FROM the pause stage itself, skipping straight past
            // it into fulfillment instead of stopping there.
            UnitPlatform::advance($this->txId, 'human_decide');
        } else {
            // draft_ready (Gmail, in-app-only, or Fast Track) — advance FROM
            // push_draft so it correctly stops AT the human_decide pause
            // point, which marks fulfillment_stage for accurate display
            // without dispatching anything.
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

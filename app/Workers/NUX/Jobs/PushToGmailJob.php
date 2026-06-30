<?php

namespace App\Workers\NUX\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerEvent;
use App\Platform\SDK\WorkerOutput;
use App\Workers\AVA\Services\GmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PushToGmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input = UnitPlatform::getInput($this->txId);

        $draftData = $input->stage('draft_post');
        $read      = $input->stage('read_post');
        $classify  = $input->stage('classify');
        $repurpose = $input->stage('repurpose');
        $media     = $input->stage('media');

        $subject = $draftData['email_subject'] ?? 'NUX: Repurposed content ready';
        $body    = $draftData['email_body']    ?? '';
        $to      = $input->tenantEmail;

        // Deliver to Gmail using the inbox credential (same GmailService as AVA)
        $gmail   = new GmailService($input->credential);
        $draftId = null;

        if ($input->isFastTrack()) {
            $subject = '[Fast Track] ' . $subject;
            $body    = "<p><strong>⚡ Fast Track Test — this is a preview of what NUX will deliver.</strong></p>\n" . $body;
        }

        $draftId = $gmail->createDraft(to: $to, subject: $subject, body: $body);

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:        'push_draft',
            status:       'draft_ready',
            data:         ['gmail_draft_id' => $draftId, 'status' => 'draft_ready'],
            gmailDraftId: $draftId,
        ));

        // Write to nux_register
        $this->writeRegister($input, $draftId, $read, $classify, $repurpose, $media, $draftData);

        // Mark post as processed in tracker
        $postId   = $input->raw['post_id'] ?? null;
        $platform = $read['platform'] ?? 'linkedin';
        if ($postId) {
            DB::table('nux_post_tracker')
                ->where('deployment_id', $input->deploymentId)
                ->where('platform', $platform)
                ->where('post_id', $postId)
                ->update([
                    'post_type'       => $classify['post_type'] ?? null,
                    'topic'           => $classify['topic'] ?? null,
                    'tone'            => $classify['tone'] ?? null,
                    'repurpose_value' => $classify['repurpose_value'] ?? null,
                    'confidence'      => $classify['confidence'] ?? null,
                    'transaction_id'  => DB::table('transactions')->where('tx_id', $this->txId)->value('id'),
                    'processed_at'    => now(),
                    'updated_at'      => now(),
                ]);
        }

        UnitPlatform::log('nux', $this->txId, 'draft_pushed_to_gmail', [
            'gmail_draft_id' => $draftId,
            'to'             => $to,
        ]);

        // Emit content.draft_ready event
        UnitPlatform::emit($this->txId, new WorkerEvent('content.draft_ready', [
            'draft' => [
                'gmail_draft_id' => $draftId,
                'subject'        => $subject,
                'status'         => 'draft_ready',
                'fast_track'     => $input->isFastTrack(),
                'created_at'     => now()->toISOString(),
            ],
            'source' => [
                'platform'   => $read['platform']   ?? null,
                'post_id'    => $input->raw['post_id'] ?? null,
                'post_url'   => $read['post_url']   ?? null,
                'author'     => $read['author']     ?? null,
                'posted_at'  => $read['posted_at']  ?? null,
            ],
            'content' => [
                'copies'          => $repurpose['repurposed_copies'] ?? [],
                'image_url'       => $media['image_url']       ?? null,
                'image_generated' => $media['image_generated'] ?? false,
            ],
            'classify' => [
                'post_type'       => $classify['post_type']       ?? null,
                'topic'           => $classify['topic']           ?? null,
                'tone'            => $classify['tone']            ?? null,
                'repurpose_value' => $classify['repurpose_value'] ?? null,
                'confidence'      => $classify['confidence']      ?? null,
            ],
        ]));
    }

    public function failed(\Throwable $e): void
    {
        if ($e instanceof \App\Platform\Exceptions\BillingException) {
            UnitPlatform::setStatus($this->txId, 'blocked');
            UnitPlatform::log('nux', $this->txId, 'billing_blocked', [
                'code' => $e->billingCode, 'reason' => $e->getMessage(),
            ], 'warning');
            $this->delete();
            return;
        }
        UnitPlatform::setStatus($this->txId, 'failed');
        UnitPlatform::log('nux', $this->txId, 'job_failed', [
            'job' => 'PushToGmailJob', 'error' => $e->getMessage(),
        ], 'error');
    }

    private function writeRegister(
        object $input,
        ?string $draftId,
        array $read,
        array $classify,
        array $repurpose,
        array $media,
        array $draftData
    ): void {
        DB::table('nux_register')->insert([
            'user_id'          => $input->userId,
            'deployment_id'    => $input->deploymentId,
            'transaction_id'   => DB::table('transactions')->where('tx_id', $this->txId)->value('id'),
            'source_platform'  => $read['platform']   ?? 'linkedin',
            'source_post_id'   => $input->raw['post_id'] ?? null,
            'source_post_url'  => $read['post_url']   ?? null,
            'source_author'    => $read['author']     ?? null,
            'source_posted_at' => $read['posted_at']  ?? null,
            'target_channels'  => json_encode(array_column($repurpose['repurposed_copies'] ?? [], 'channel')),
            'repurposed_copies'=> json_encode($repurpose['repurposed_copies'] ?? []),
            'image_url'        => $media['image_url']  ?? null,
            'image_path'       => $media['image_path'] ?? null,
            'draft_summary'    => $draftData['draft_summary'] ?? null,
            'gmail_draft_id'   => $draftId,
            'post_type'        => $classify['post_type']       ?? null,
            'topic'            => $classify['topic']           ?? null,
            'tone'             => $classify['tone']            ?? null,
            'repurpose_value'  => $classify['repurpose_value'] ?? null,
            'status'           => 'draft_ready',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}

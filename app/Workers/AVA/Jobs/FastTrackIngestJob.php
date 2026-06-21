<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Workers\AVA\Services\GmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class FastTrackIngestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input = UnitPlatform::getInput($this->txId);
        $raw   = $input->raw;

        UnitPlatform::setStatus($this->txId, 'ingesting');

        $from    = $raw['fast_track_from']    ?? 'Namecheap Renewals <renewals@namecheap.com>';
        $to      = $input->credential->gmail_address ?? $input->tenantEmail;
        $subject = $raw['fast_track_subject'] ?? 'Domain Renewal Notice — yourdomain.com expires in 30 days';
        $body    = $raw['fast_track_body']    ?? $raw['raw_email'] ?? '';

        $rawEmail    = null;
        $messageId   = null;
        $ingestMethod = 'synthetic';

        // Attempt to insert the test email into the real inbox via Gmail API.
        // Requires the gmail.insert OAuth scope — falls back to synthetic if not granted.
        if ($input->credential) {
            try {
                $gmail     = new GmailService($input->credential);
                $messageId = $gmail->insertIntoInbox($from, $to, $subject, $body);
                $rawEmail  = $gmail->fetchRawMessage($messageId);
                $ingestMethod = 'gmail_insert';
            } catch (\RuntimeException $e) {
                // 403 = insufficient scope (gmail.insert not granted on this credential).
                // Fall through to synthetic email — pipeline still runs the full read path.
                UnitPlatform::log('ava', $this->txId, 'fast_track_insert_unavailable', [
                    'reason' => $e->getMessage(),
                    'fallback' => 'synthetic',
                ]);
            }
        }

        // Build a realistic RFC 2822 string when Gmail insert isn't available
        if (!$rawEmail) {
            $date     = now()->format('D, d M Y H:i:s O');
            $rawEmail = implode("\r\n", [
                "From: {$from}",
                "To: {$to}",
                "Date: {$date}",
                "Subject: {$subject}",
                "Content-Type: text/plain; charset=UTF-8",
                "",
                $body,
            ]);
        }

        // Update transaction raw_input with the email content (real or synthetic)
        $updatedRaw = array_merge($raw, [
            'raw_email'     => $rawEmail,
            'message_id'    => $messageId ?? ('ft-synthetic-' . substr($this->txId, 0, 8)),
            'ingest_method' => $ingestMethod,
            'to'            => $to,
        ]);

        DB::table('transactions')->where('tx_id', $this->txId)->update([
            'raw_input'  => json_encode($updatedRaw),
            'updated_at' => now(),
        ]);

        UnitPlatform::log('ava', $this->txId, 'fast_track_ingested', [
            'method'  => $ingestMethod,
            'to'      => $to,
            'from'    => $from,
            'subject' => $subject,
        ]);

        // Hand off to the standard read stage — same pipeline as a real inbound email
        ReadEmailJob::dispatch($this->txId)->onQueue($input->queue);
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::setStatus($this->txId, 'failed');
        UnitPlatform::log('ava', $this->txId, 'job_failed', [
            'job'   => 'FastTrackIngestJob',
            'error' => $e->getMessage(),
        ], 'error');
    }
}

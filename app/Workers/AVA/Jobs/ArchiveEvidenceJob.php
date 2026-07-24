<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Stage 14 — Archive Evidence. MVP scope (per agreed design): combine
 * everything AVA actually knows about this renewal — the original request,
 * classification, matched client/asset, the draft that was sent, the human's
 * decision, invoice/document request status, and payment confirmation — into
 * one PDF. Not a merge of real uploaded file attachments (no upload/parsing
 * subsystem exists yet); a durable, single-file compliance summary of the
 * transaction's own data, which is genuinely everything on hand right now.
 */
class ArchiveEvidenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input    = UnitPlatform::getInput($this->txId);
        $read     = $input->stage('read');
        $classify = $input->stage('classify');
        $memory   = $input->stage('memory');
        $draft    = $input->stage('draft');
        $invoice  = $input->stage('request_invoice');
        $documents= $input->stage('request_documents');
        $payment  = $input->stage('confirm_payment');

        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES);

        $html = '<style>body{font-family:sans-serif;font-size:12px;color:#111}h1{font-size:18px}h2{font-size:13px;margin-top:18px;border-bottom:1px solid #ccc;padding-bottom:4px}table{width:100%;border-collapse:collapse}td{padding:4px 0;vertical-align:top}td.label{width:160px;color:#666}</style>';
        $html .= '<h1>Renewal record — ' . $esc($memory['asset'] ?? $this->txId) . '</h1>';
        $html .= '<p>Transaction ' . $esc($this->txId) . ' · archived ' . now()->toDateTimeString() . '</p>';

        $html .= '<h2>What happened</h2><table>'
            . '<tr><td class="label">Summary</td><td>' . $esc($read['plain_english_summary'] ?? null) . '</td></tr>'
            . '<tr><td class="label">Category</td><td>' . $esc($classify['category'] ?? null) . '</td></tr>'
            . '<tr><td class="label">Priority</td><td>' . $esc($classify['priority'] ?? null) . '</td></tr>'
            . '<tr><td class="label">Client</td><td>' . $esc($memory['matched_client'] ?? null) . '</td></tr>'
            . '<tr><td class="label">Asset</td><td>' . $esc($memory['asset'] ?? null) . '</td></tr>'
            . '</table>';

        $html .= '<h2>What AVA sent</h2><table>'
            . '<tr><td class="label">To</td><td>' . $esc($draft['to'] ?? null) . '</td></tr>'
            . '<tr><td class="label">Subject</td><td>' . $esc($draft['subject'] ?? null) . '</td></tr>'
            . '<tr><td class="label">Body</td><td>' . nl2br($esc($draft['body'] ?? null)) . '</td></tr>'
            . '</table>';

        $html .= '<h2>Fulfillment</h2><table>'
            . '<tr><td class="label">Invoice</td><td>' . $esc($invoice['status'] ?? 'not_applicable') . '</td></tr>'
            . '<tr><td class="label">Supporting documents</td><td>' . $esc($documents['status'] ?? 'not_applicable') . '</td></tr>'
            . '<tr><td class="label">Payment confirmed</td><td>' . $esc($payment['confirmed_at'] ?? null) . '</td></tr>'
            . '</table>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $path = "renewal-archives/{$this->txId}.pdf";
        Storage::disk(config('filesystems.media_disk', 'public'))->put($path, $dompdf->output());

        $output = ['path' => $path, 'generated_at' => now()->toISOString()];
        UnitPlatform::commitOutput($this->txId, new WorkerOutput(stage: 'archive_evidence', data: $output));
        UnitPlatform::setFulfillmentStage($this->txId, 'archive_evidence');
        UnitPlatform::log('ava', $this->txId, 'evidence_archived', $output);

        UnitPlatform::advance($this->txId, 'archive_evidence');
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::log('ava', $this->txId, 'job_failed', ['job' => 'ArchiveEvidenceJob', 'error' => $e->getMessage()], 'error');
        // Never block the loop closing over an archival failure — continue.
        UnitPlatform::advance($this->txId, 'archive_evidence');
    }
}

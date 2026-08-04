<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * Stage 14 — Archive Evidence. Closes the transaction: one PDF with the
 * complete blueprint of what happened — every draft, every reminder sent
 * (with timestamps), every gate decision, invoice/document handling, and
 * payment confirmation. This is the document anyone (client, auditor,
 * the tenant six months later) looks at to see the full record — it must
 * stay as detailed as the live Transaction Center page, not a summary.
 *
 * Carries a UNIT-branded header and a QR code linking to a signed, expiring
 * download URL, so the PDF is self-contained proof — no login required to
 * pull the same file back up.
 */
class ArchiveEvidenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        UnitPlatform::stageStarted($this->txId, 'archive_evidence');

        $tx = DB::table('transactions')->where('tx_id', $this->txId)->first();

        $input     = UnitPlatform::getInput($this->txId);
        $read      = $input->stage('read');
        $classify  = $input->stage('classify');
        $memory    = $input->stage('memory');
        $draft     = $input->stage('draft');
        $invoice   = $input->stage('request_invoice');
        $documents = $input->stage('request_documents');
        $payment   = $input->stage('confirm_payment');
        $renewal   = $input->stage('update_renewal_date');
        $notify    = $input->stage('notify_stakeholders');
        $notifyCustomer = $input->stage('notify_customer');

        $reminders    = json_decode($tx->reminders ?? '[]', true) ?: [];
        $clientDrafts = json_decode($tx->client_drafts ?? '[]', true) ?: [];

        // Archive honesty — a transaction with only 1 of 3 drafts on record
        // reads very differently depending on why: cadence still in
        // progress vs. a tenant deliberately cutting it short because the
        // renewal was already closed with the client outside AVA.
        // Read directly off the transaction row, not platform_events —
        // UnitPlatform::log() writes to the Laravel log file, not that
        // table, so a platform_events query for this event never matched
        // and this banner never actually fired until now.
        $skippedCadence = (bool) $tx->cadence_skipped;
        $stoppedCadence = (bool) $tx->cadence_stopped;

        // Previews (rounds 2/3 generated upfront alongside round 1, see
        // DraftEmailJob) never actually went anywhere — the archive is a
        // record of what happened, not what was planned, so they're
        // excluded here rather than misleadingly listed as sent drafts.
        $clientDrafts = array_values(array_filter($clientDrafts, fn ($cd) => empty($cd['preview'])));

        $avaVersion = \App\Platform\Services\WorkerRegistry::resolveActive($input->workerSlug)->identity()['version'] ?? '—';

        $esc  = fn ($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES);
        $when = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('M j, Y · g:i A') : '—';
        $remindersFor = fn (string $stageKey) => array_values(array_filter($reminders, fn ($r) => ($r['stage_key'] ?? null) === $stageKey));

        // Section title with an optional right-aligned activity timestamp —
        // only rendered when the section actually has one on record (see
        // per-section $lastActivity computation below each h2 call). No
        // timestamp is invented for a section that never captured one.
        $sectionTitle = fn (string $label, ?string $ts) => '<h2 class="sec"><span>' . $label . '</span>'
            . ($ts ? '<span class="sec-ts">' . $when($ts) . '</span>' : '') . '</h2>';

        // Signed, expiring URL — this is what the QR code resolves to. 1 year
        // out: an archive is a closed-transaction record, not a live session,
        // so it should stay retrievable well past the renewal itself.
        $signedUrl = URL::temporarySignedRoute('archive.public-download', now()->addYear(), ['txId' => $this->txId]);
        $qrDataUri = $this->qrDataUri($signedUrl);

        // Total time from the transaction entering the pipeline to this
        // archive closing it out — the "AVA is fast, AVA sticks with you"
        // metric requested for the footer. Both ends are real timestamps
        // (transactions.created_at, this job running right now), not
        // estimated or reconstructed from stage data.
        $enteredAt   = \Carbon\Carbon::parse($tx->created_at);
        $completedAt = now();
        $duration    = $enteredAt->diff($completedAt);
        $durationParts = [];
        if ($duration->days > 0) $durationParts[] = $duration->days . 'd';
        if ($duration->h > 0)    $durationParts[] = $duration->h . 'h';
        if ($duration->i > 0 || !$durationParts) $durationParts[] = $duration->i . 'm';
        $durationText = implode(' ', array_slice($durationParts, 0, 2));

        $html = $this->styles();
        $html .= '<div class="hd">'
            . '<div class="hd-logo"><div class="hd-logo-unit">UNIT</div><div class="hd-logo-worker">AVA</div></div>'
            . '<div class="hd-meta">'
            . '<div class="hd-title">Renewal record — ' . $esc($memory['asset'] ?? $this->txId) . '</div>'
            . '<div class="hd-sub">Transaction ' . $esc($this->txId) . ' &middot; archived ' . now()->format('M j, Y · g:i A') . ' &middot; Generated by AVA v' . $esc($avaVersion) . '</div>'
            . '</div>'
            . '<div class="hd-qr"><img src="' . $qrDataUri . '" width="72" height="72"><div class="hd-qr-label">Scan to re-download</div></div>'
            . '</div>';

        $html .= '<h2>What happened</h2><table>'
            . '<tr><td class="label">Summary</td><td>' . $esc($read['plain_english_summary'] ?? null) . '</td></tr>'
            . '<tr><td class="label">Category</td><td>' . $esc($classify['category'] ?? null) . '</td></tr>'
            . '<tr><td class="label">Priority</td><td>' . $esc($classify['priority'] ?? null) . '</td></tr>'
            . '<tr><td class="label">Client</td><td>' . $esc($memory['matched_client'] ?? null) . '</td></tr>'
            . '<tr><td class="label">Asset</td><td>' . $esc($memory['asset'] ?? null) . '</td></tr>'
            . '<tr><td class="label">Contact</td><td>' . $esc($memory['primary_contact_name'] ?? null) . ' &lt;' . $esc($memory['primary_contact_email'] ?? null) . '&gt;</td></tr>'
            . '</table>';

        // A renews_together bundle — the itemized breakdown behind the
        // group label used as "Asset" above, so the archive is a complete
        // record of exactly what renewed, not just a summary count.
        if (!empty($memory['line_items'])) {
            $html .= '<h2>Bundled services</h2><table>'
                . '<tr><td class="label"><strong>Name</strong></td><td><strong>Type</strong></td><td><strong>Renewal date</strong></td></tr>';
            foreach ($memory['line_items'] as $item) {
                $html .= '<tr><td class="label">' . $esc($item['name'] ?? null) . '</td><td>' . $esc($item['type'] ?? null) . '</td><td>' . $esc($item['renewal_date'] ?? null) . '</td></tr>';
            }
            $html .= '</table>';
        }

        // 1. Every client-facing draft round, in order, with its approval.
        // Title timestamp = most recent real activity on record for this
        // section (last round's approval, or its draft time if never
        // approved) — blank if this is old data with no client_drafts at all.
        $lastDraft = $clientDrafts ? end($clientDrafts) : null;
        // Note: transaction_stage_log stores each job's own short internal
        // alias, not the pipelineStages() key — DraftEmailJob logs itself as
        // 'draft', not 'draft_email' (see that stage's log_stage_key).
        $html .= $sectionTitle('1. Renewal drafts sent to client', $lastDraft['approved_at'] ?? $lastDraft['drafted_at'] ?? UnitPlatform::stageCompletedAt($this->txId, 'draft'));
        if ($skippedCadence) {
            $html .= '<p style="color:#b45309;font-size:13px;margin-bottom:10px">⚠ Approved via "Approve &amp; proceed" — the tenant confirmed this renewal was already closed with the client outside AVA, and the remaining reminder rounds below were deliberately skipped rather than sent.</p>';
        } elseif ($stoppedCadence) {
            $html .= '<p style="color:#b45309;font-size:13px;margin-bottom:10px">⚠ The tenant stopped the remaining reminder rounds after this point — the renewal itself was not affected, AVA was just told to stop nudging the client about it.</p>';
        }
        if ($clientDrafts) {
            foreach ($clientDrafts as $cd) {
                $ordinal = ['1st', '2nd', '3rd'][($cd['reminder_number'] ?? 1) - 1] ?? (($cd['reminder_number'] ?? '?') . 'th');
                $html .= '<table>'
                    . '<tr><td class="label">' . $esc($ordinal) . ' draft</td><td>drafted ' . $when($cd['drafted_at'] ?? null)
                    . (!empty($cd['days_before_expiry']) ? ' &middot; ' . $esc($cd['days_before_expiry']) . ' days before expiry' : '')
                    . (!empty($cd['approved_at']) ? ' &middot; approved ' . $when($cd['approved_at']) : ' &middot; not approved')
                    . '</td></tr>'
                    . '<tr><td class="label">To</td><td>' . $esc($cd['to'] ?? null) . '</td></tr>'
                    . '<tr><td class="label">Subject</td><td>' . $esc($cd['subject'] ?? null) . '</td></tr>'
                    . '</table><div class="msg">' . nl2br($esc($cd['body'] ?? null)) . '</div>';
            }
        } else {
            $html .= '<table><tr><td class="label">To</td><td>' . $esc($draft['to'] ?? null) . '</td></tr>'
                . '<tr><td class="label">Subject</td><td>' . $esc($draft['subject'] ?? null) . '</td></tr>'
                . '</table><div class="msg">' . nl2br($esc($draft['body'] ?? null)) . '</div>';
        }
        foreach ($remindersFor('human_decide') as $r) {
            $html .= '<div class="msg-meta">Nudge to review &amp; approve &mdash; attempt ' . $esc($r['attempt_number'] ?? null) . ' &middot; ' . $when($r['sent_at'] ?? null) . '</div>'
                . '<div class="msg"><strong>' . $esc($r['subject'] ?? null) . '</strong><br><br>' . nl2br($esc($r['body'] ?? null)) . '</div>';
        }

        // A stage-gated section with no data at all reads very differently
        // depending on why — "the tenant didn't need this" vs. "this was
        // turned off in AVA Settings and never even ran." The archive is
        // supposed to be the honest record of what actually happened, so
        // say which one it was rather than leaving it ambiguous.
        $gateSkippedNote = fn (string $stageKey, string $label) => !UnitPlatform::gateEnabled($input->deploymentId, $stageKey, true)
            ? "<h2>{$label}</h2><p style=\"color:#888\">Skipped — this stage is turned off in AVA Settings.</p>"
            : '';

        // 2. Invoice — most recent client message wins over the stage's own
        // completion time (a follow-up is more "activity" than the moment
        // the stage was entered). stageCompletedAt() reads transaction_stage_log
        // — the universal, automatic completion log every stage writes to via
        // commitOutput(), not a hand-added field on this one job's output.
        if (!empty($invoice['status']) || !empty($invoice['sample'])) {
            $lastInvoiceMsg = !empty($invoice['client_messages']) ? end($invoice['client_messages']) : null;
            $html .= $sectionTitle('2. Invoice request', $lastInvoiceMsg['sent_at'] ?? UnitPlatform::stageCompletedAt($this->txId, 'request_invoice')) . '<table>'
                . '<tr><td class="label">Status</td><td>' . $esc($invoice['status'] ?? 'not_applicable') . '</td></tr>';
            if (!empty($invoice['ocr'])) {
                $ocr = $invoice['ocr'];
                $html .= '<tr><td class="label">Amount</td><td>' . $esc($ocr['amount'] ?? null) . ' ' . $esc($ocr['currency'] ?? null) . '</td></tr>'
                    . '<tr><td class="label">Issued</td><td>' . $esc($ocr['issued_date'] ?? null) . '</td></tr>'
                    . '<tr><td class="label">Due</td><td>' . $esc($ocr['due_date'] ?? null) . '</td></tr>'
                    . '<tr><td class="label">Invoice #</td><td>' . $esc($ocr['invoice_number'] ?? null) . '</td></tr>';
            }
            $html .= '</table>';
            if (!empty($invoice['sample'])) {
                $html .= '<div class="msg">' . nl2br($esc($invoice['sample'])) . '</div>';
            }
            foreach ($invoice['client_messages'] ?? [] as $m) {
                $html .= '<div class="msg-meta">Message ' . $esc($m['sequence'] ?? null) . ' to client &middot; ' . $when($m['sent_at'] ?? null) . '</div>'
                    . '<div class="msg"><strong>' . $esc($m['subject'] ?? null) . '</strong><br><br>' . nl2br($esc($m['body'] ?? null)) . '</div>';
            }
            foreach ($remindersFor('request_invoice') as $r) {
                $html .= '<div class="msg-meta">Nudge to attach invoice &mdash; attempt ' . $esc($r['attempt_number'] ?? null) . ' &middot; ' . $when($r['sent_at'] ?? null) . '</div>'
                    . '<div class="msg"><strong>' . $esc($r['subject'] ?? null) . '</strong><br><br>' . nl2br($esc($r['body'] ?? null)) . '</div>';
            }
        } else {
            $html .= $gateSkippedNote('request_invoice', '2. Invoice request');
        }

        // 3. Documents
        if (!empty($documents['status']) || !empty($documents['sample'])) {
            $lastDocMsg = !empty($documents['client_messages']) ? end($documents['client_messages']) : null;
            $html .= $sectionTitle('3. Supporting document request', $lastDocMsg['sent_at'] ?? UnitPlatform::stageCompletedAt($this->txId, 'request_documents')) . '<table>'
                . '<tr><td class="label">Status</td><td>' . $esc($documents['status'] ?? 'not_applicable') . '</td></tr>'
                . '</table>';
            if (!empty($documents['sample'])) {
                $html .= '<div class="msg">' . nl2br($esc($documents['sample'])) . '</div>';
            }
            foreach ($documents['client_messages'] ?? [] as $m) {
                $html .= '<div class="msg-meta">Message ' . $esc($m['sequence'] ?? null) . ' to client &middot; ' . $when($m['sent_at'] ?? null) . '</div>'
                    . '<div class="msg"><strong>' . $esc($m['subject'] ?? null) . '</strong><br><br>' . nl2br($esc($m['body'] ?? null)) . '</div>';
            }
        } else {
            $html .= $gateSkippedNote('request_documents', '3. Supporting document request');
        }

        // 4. Payment & renewal — confirm_payment is a hard gate, so a
        // missing confirmation with the gate off is the highest-stakes
        // case of this whole "skipped vs never reached" distinction: it
        // means the renewal closed out with nobody ever confirming money
        // changed hands.
        if (empty($payment) && !UnitPlatform::gateEnabled($input->deploymentId, 'confirm_payment', true)) {
            $html .= $sectionTitle('4. Payment &amp; renewal', null)
                . '<p style="color:#c0392b"><strong>Payment confirmation was skipped</strong> — this gate is turned off in AVA Settings. No one confirmed payment for this renewal.</p>'
                . '<table><tr><td class="label">Renewal date</td><td>' . $esc($renewal['old_date'] ?? null) . ' &rarr; ' . $esc($renewal['new_date'] ?? null) . '</td></tr></table>';
        } else {
            $paymentConfirmedText = ($payment['confirmed'] ?? null) === true
                ? $when($payment['confirmed_at'] ?? null)
                : $esc($payment['confirmed'] ?? null);
            $html .= $sectionTitle('4. Payment &amp; renewal', $payment['confirmed_at'] ?? UnitPlatform::stageCompletedAt($this->txId, 'confirm_payment')) . '<table>'
                . '<tr><td class="label">Payment confirmed</td><td>' . $paymentConfirmedText . '</td></tr>'
                . '<tr><td class="label">Renewal date</td><td>' . $esc($renewal['old_date'] ?? null) . ' &rarr; ' . $esc($renewal['new_date'] ?? null) . '</td></tr>'
                . '</table>';
        }
        foreach ($remindersFor('confirm_payment') as $r) {
            $html .= '<div class="msg-meta">Payment reminder &mdash; attempt ' . $esc($r['attempt_number'] ?? null) . ' &middot; ' . $when($r['sent_at'] ?? null) . '</div>'
                . '<div class="msg"><strong>' . $esc($r['subject'] ?? null) . '</strong><br><br>' . nl2br($esc($r['body'] ?? null)) . '</div>';
        }

        // 5. Closing message to the tenant
        if ($notify) {
            $html .= $sectionTitle('5. Closing message to stakeholders', UnitPlatform::stageCompletedAt($this->txId, 'notify_stakeholders')) . '<table>'
                . '<tr><td class="label">To</td><td>' . $esc($notify['to'] ?? null) . '</td></tr>'
                . '<tr><td class="label">Subject</td><td>' . $esc($notify['subject'] ?? null) . '</td></tr>'
                . '</table><div class="msg">' . nl2br($esc($notify['body'] ?? null)) . '</div>';
        }

        // 6. Closing message to the client — only if the tenant opted in
        // (notify_customer gate); the stage still ran either way, but
        // 'sent' reflects whether anything actually went out.
        if ($notifyCustomer && !empty($notifyCustomer['sent'])) {
            $html .= $sectionTitle('6. Closing message to client', UnitPlatform::stageCompletedAt($this->txId, 'notify_customer')) . '<table>'
                . '<tr><td class="label">To</td><td>' . $esc($notifyCustomer['to'] ?? null) . '</td></tr>'
                . '<tr><td class="label">Subject</td><td>' . $esc($notifyCustomer['subject'] ?? null) . '</td></tr>'
                . '<tr><td class="label">Next renewal</td><td>' . $esc($notifyCustomer['next_renewal_date'] ?? null) . '</td></tr>'
                . '</table><div class="msg">' . nl2br($esc($notifyCustomer['body'] ?? null)) . '</div>';
        }

        $html .= '<div class="ft-time"><strong>' . $esc($durationText) . '</strong> from entering the pipeline (' . $enteredAt->format('M j, Y · g:i A') . ') to this closeout (' . $completedAt->format('M j, Y · g:i A') . ') — however long a renewal takes, AVA stays on it end to end.</div>';
        $html .= '<div class="ft">This record is durable proof of a UNIT-coordinated renewal. Re-download at any time via the QR code above or the link it resolves to.</div>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        $path = "renewal-archives/{$this->txId}.pdf";
        Storage::disk(config('filesystems.media_disk', 'public'))->put($path, $dompdf->output());

        $output = ['path' => $path, 'generated_at' => now()->toISOString(), 'signed_url_expires_at' => now()->addYear()->toISOString()];
        UnitPlatform::commitOutput($this->txId, new WorkerOutput(stage: 'archive_evidence', data: $output));
        UnitPlatform::setFulfillmentStage($this->txId, 'archive_evidence');
        UnitPlatform::log('ava', $this->txId, 'evidence_archived', $output);

        UnitPlatform::advance($this->txId, 'archive_evidence');
    }

    private function qrDataUri(string $url): string
    {
        $builder = new \Endroid\QrCode\Builder\Builder(
            writer: new \Endroid\QrCode\Writer\PngWriter(),
            data: $url,
            size: 200,
            margin: 4,
        );

        return $builder->build()->getDataUri();
    }

    private function styles(): string
    {
        // 'DejaVu Sans' (not a bare 'sans-serif' alias) — dompdf's bundled
        // font with a real embeddable Bold face. font-weight:900 isn't
        // reliably matched to a bold face by dompdf's font selector and was
        // silently rendering at regular weight; 'bold' is the value it
        // actually resolves.
        return '<style>
            body{font-family:"DejaVu Sans",sans-serif;font-size:12px;color:#111}
            h2{font-size:13px;margin-top:18px;border-bottom:1px solid #ccc;padding-bottom:4px}
            h2.sec{display:table;width:100%}
            h2.sec span:first-child{display:table-cell}
            h2.sec .sec-ts{display:table-cell;text-align:right;font-size:10px;font-weight:normal;color:#888;white-space:nowrap;vertical-align:bottom}
            table{width:100%;border-collapse:collapse}
            td{padding:4px 0;vertical-align:top}
            td.label{width:160px;color:#666}
            .msg{background:#f7f7f7;border-radius:6px;padding:8px 10px;margin-top:4px}
            .msg-meta{font-size:11px;color:#666;margin-top:10px}
            .hd{display:table;width:100%;margin-bottom:14px;border-bottom:2px solid #111;padding-bottom:10px}
            .hd-logo{display:table-cell;width:90px;vertical-align:top}
            .hd-logo-unit{font-size:22px;font-weight:bold;letter-spacing:-1px}
            .hd-logo-worker{font-size:15px;font-weight:bold;letter-spacing:-0.5px;color:#666;margin-top:1px}
            .hd-meta{display:table-cell;vertical-align:top}
            .hd-title{font-size:17px;font-weight:bold}
            .hd-sub{font-size:11px;color:#666;margin-top:3px}
            .hd-qr{display:table-cell;width:90px;text-align:right;vertical-align:top}
            .hd-qr-label{font-size:9px;color:#666;margin-top:2px}
            .ft-time{margin-top:26px;padding-top:10px;border-top:1px solid #ccc;font-size:11px;color:#444}
            .ft{margin-top:8px;font-size:10px;color:#888}
        </style>';
    }

    public function failed(\Throwable $e): void
    {
        UnitPlatform::log('ava', $this->txId, 'job_failed', ['job' => 'ArchiveEvidenceJob', 'error' => $e->getMessage()], 'error');
        // Never block the loop closing over an archival failure — continue.
        UnitPlatform::advance($this->txId, 'archive_evidence');
    }
}

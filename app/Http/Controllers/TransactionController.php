<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function index(Request $request, string $slug)
    {
        $userId = auth()->id();
        $dep = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('worker_slug', $slug)
            ->whereIn('status', ['active', 'paused'])
            ->orderByDesc('id')
            ->firstOrFail();

        $filter = $request->query('filter', 'all');
        $query  = DB::table('transactions')->where('deployment_id', $dep->id)->orderByDesc('id');
        if ($filter === 'draft_ready')   $query->where('status', 'draft_ready');
        elseif ($filter === 'approved')  $query->whereIn('status', ['approved','sent']);
        elseif ($filter === 'failed')    $query->where('status', 'failed');
        elseif ($filter === 'filtered')  $query->where('status', 'filtered_out');
        elseif ($filter === 'dismissed') $query->where('status', 'dismissed');
        else $query->whereNotIn('status', ['dismissed', 'filtered_out']); // default: hide dismissed + filtered noise
        $transactions  = $query->paginate(25);
        $currentFilter = $filter;

        $shell = \App\Platform\Services\WorkerShellService::build($userId, $slug);
        extract($shell); // workerCatalog, registryRows, registryRow, profileImg, coverImg, tokenTotal
        $firstName = explode(' ', trim(auth()->user()->name))[0];

        return view('dashboard.transactions', compact(
            'transactions', 'currentFilter', 'dep',
            'workerCatalog', 'tokenTotal', 'firstName'
        ));
    }

    public function show(string $slug, string $txId)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();

        if ($tx->worker_slug !== $slug) {
            return redirect()->route('app.transactions.show', ['slug' => $tx->worker_slug, 'txId' => $txId]);
        }

        $nuxRegister = null;
        if ($tx->worker_slug === 'nux') {
            $nuxRegister = DB::table('nux_register')->where('transaction_id', $tx->id)->first();
        }

        // Stage titles must come from the contract, not be hand-typed here —
        // resolve via the deployment's worker_slug, not the transaction's:
        // some legacy transaction rows store a queue-name-style string there
        // (e.g. "ava-renewal-coordinator") instead of the clean slug.
        $dep         = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();
        $contract    = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug ?? 'ava');
        $rawStages   = $contract->pipelineStages();
        $stagesByKey = collect($rawStages)->keyBy('key');

        // Transaction Center — the standardized, contract-driven stage list.
        // Any worker declaring gate_type on its own stages renders here the
        // same way, no new UI code required.
        $stages = $this->buildStageList($tx, $rawStages);

        $shell = \App\Platform\Services\WorkerShellService::build(auth()->id(), '');
        extract($shell); // workerCatalog, registryRows, registryRow, profileImg, coverImg, tokenTotal
        $firstName = explode(' ', trim(auth()->user()->name))[0];

        return view('dashboard.transaction-detail', compact(
            'tx', 'nuxRegister', 'stagesByKey', 'stages', 'dep',
            'workerCatalog', 'tokenTotal', 'firstName'
        ));
    }

    // ── Transaction Center stage assembly ────────────────────────────────
    // For each contract stage, in order: is it done/active/pending, and
    // what does it actually have to show? Driven entirely by output_column
    // + gate_type from the contract — adding a stage to a worker's
    // pipelineStages() shows up here automatically.
    private function buildStageList(object $tx, array $rawStages): array
    {
        $currentKey = $tx->fulfillment_stage ?: $this->legacyCurrentStageKey($tx->status);
        $currentIdx = collect($rawStages)->search(fn($s) => $s['key'] === $currentKey);
        if ($currentIdx === false) $currentIdx = 0;

        $reminders    = json_decode($tx->reminders ?? '[]', true) ?: [];
        $clientDrafts = json_decode($tx->client_drafts ?? '[]', true) ?: [];

        // Show the full cadence up front, not just what's happened so far —
        // real (non-Fast-Track) transactions get up to 3 rounds, so a
        // not-yet-drafted 2nd/3rd round renders as an upcoming placeholder
        // tab instead of silently not existing. Fast Track is exempt from
        // the cadence entirely (its one draft is always final), so it only
        // ever shows the one real round.
        $daysBeforeExpiryByRound = [1 => 30, 2 => 15, 3 => 0];
        if (!$tx->is_test) {
            $presentRounds = collect($clientDrafts)->pluck('reminder_number')->all();
            for ($n = 1; $n <= 3; $n++) {
                if (in_array($n, $presentRounds, true)) continue;
                $clientDrafts[] = [
                    'reminder_number'    => $n,
                    'days_before_expiry' => $daysBeforeExpiryByRound[$n],
                    'placeholder'        => true,
                ];
            }
            usort($clientDrafts, fn($a, $b) => ($a['reminder_number'] ?? 0) <=> ($b['reminder_number'] ?? 0));
        }

        return collect($rawStages)->map(function ($stage, $i) use ($tx, $currentIdx, $reminders, $clientDrafts) {
            $state = $i < $currentIdx ? 'done' : ($i === $currentIdx ? 'active' : 'pending');

            $content = $stage['output_column'] && $tx->{$stage['output_column']}
                ? json_decode($tx->{$stage['output_column']}, true)
                : null;

            $stageReminders = array_values(array_filter($reminders, fn($r) => ($r['stage_key'] ?? null) === $stage['key']));

            return array_merge(['gate_type' => null], $stage, [
                'i'         => $i,
                'state'     => $state,
                'content'   => $content,
                'reminders' => $stageReminders,
                // Draft Email and human_decide both show the same up-to-3
                // client drafts — one tenant reviews every cadence message
                // once, instead of hunting across two stage cards for it.
                'client_drafts' => in_array($stage['key'], ['draft_email', 'human_decide'], true) ? $clientDrafts : [],
            ]);
        })->all();
    }

    // Transactions created before fulfillment_stage existed (or that never
    // left the fast synchronous chain) fall back to the old status-driven
    // mapping so they still render sensibly here.
    private function legacyCurrentStageKey(string $status): string
    {
        return match ($status) {
            'draft_ready', 'approved', 'sent' => 'human_decide',
            default => 'webhook',
        };
    }

    public function status(string $txId)
    {
        $tx = DB::table('transactions')
            ->where('tx_id', $txId)
            ->where('user_id', auth()->id())
            ->first();
        if (!$tx) return response()->json(['error' => 'not found'], 404);
        $terminal = ['draft_ready', 'approved', 'sent', 'failed', 'rejected', 'blocked', 'dismissed'];
        $isDone   = in_array($tx->status, $terminal);
        $isFailed = in_array($tx->status, ['failed', 'blocked']);

        $classify = json_decode($tx->classify_output ?? '{}', true) ?: [];
        $memory   = json_decode($tx->memory_output   ?? '{}', true) ?: [];
        $draft    = json_decode($tx->draft_output    ?? '{}', true) ?: [];

        return response()->json([
            'status'   => $tx->status,
            'done'     => $isDone,
            'failed'   => $isFailed,
            'blocked'  => $tx->status === 'blocked',
            // Additive summary fields — used by Fast Track's completion card.
            // Existing consumers of this endpoint ignore unknown keys.
            'category'         => $tx->category ?? $classify['category'] ?? null,
            'priority'         => $tx->priority ?? $classify['priority'] ?? null,
            'matched_client'   => $memory['matched_client'] ?? null,
            'asset'            => $memory['asset'] ?? null,
            'confidence'       => $memory['confidence'] ?? null,
            'ava_rule'         => $memory['ava_rule'] ?? null,
            'subject'          => $draft['subject'] ?? null,
            'body'             => $draft['body'] ?? null,
            'low_confidence'   => $draft['low_confidence'] ?? false,
            'gmail_draft_id'   => $tx->gmail_draft_id,
            // Fulfillment (stages 9-16) — used by Fast Track's full lifecycle preview.
            'fulfillment_stage' => $tx->fulfillment_stage,
            'invoice_output'    => json_decode($tx->invoice_output   ?? 'null', true),
            'documents_output'  => json_decode($tx->documents_output ?? 'null', true),
            'payment_output'    => json_decode($tx->payment_output   ?? 'null', true),
            'renewal_output'    => json_decode($tx->renewal_output   ?? 'null', true),
            'archive_output'    => json_decode($tx->archive_output   ?? 'null', true),
            'notify_output'     => json_decode($tx->notify_output    ?? 'null', true),
            // Every reminder sent so far at any gate, in order — the
            // Transaction Center renders these under whichever stage they
            // belong to (stage_key on each entry).
            'reminders'         => json_decode($tx->reminders ?? '[]', true) ?: [],
            'nudging_paused_at' => $tx->nudging_paused_at,
            // Client-facing draft history — up to 3 on the 30/15/0-day
            // cadence, each with its own approval timestamp.
            'client_drafts'          => json_decode($tx->client_drafts ?? '[]', true) ?: [],
            'client_reminder_number' => (int) $tx->client_reminder_number,
        ]);
    }

    public function refire(string $txId)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();

        if ($tx->status !== 'failed') {
            return back()->with('error', 'Only failed transactions can be re-fired.');
        }

        $raw    = json_decode($tx->raw_input ?? '{}', true);
        $source = $raw['source'] ?? 'unknown';

        if ($source === 'fast_track_test') {
            return back()->with('error', 'Fast Track test transactions cannot be re-fired. Run a new Fast Track instead.');
        }

        // Reset to received, clear all stage outputs so pipeline runs clean
        DB::table('transactions')->where('tx_id', $txId)->update([
            'status'          => 'received',
            'read_output'     => null,
            'classify_output' => null,
            'memory_output'   => null,
            'template_output' => null,
            'draft_output'    => null,
            'gmail_draft_id'  => null,
            'human_decision'  => null,
            'human_notes'     => null,
            'category'        => null,
            'priority'        => null,
            'updated_at'      => now(),
        ]);

        $dep      = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();
        $queue    = $dep ? ($dep->worker_slug . '-' . $dep->id) : 'default';
        $contract = $dep ? \App\Platform\Services\WorkerRegistry::resolveActive($dep->worker_slug) : null;

        if (!$contract || \App\Platform\Services\WorkerRegistry::isNull($contract)) {
            return back()->with('error', 'Worker is no longer available — cannot re-fire.');
        }

        $contract->ingestJobClass()::dispatch($txId)->onQueue($queue);

        \App\Platform\SDK\UnitPlatform::log($dep->worker_slug, $txId, 'tx_refire', ['triggered_by' => auth()->id()]);

        return back()->with('success', 'Transaction re-fired — pipeline restarting from Read stage.');
    }

    public function dismiss(string $txId, Request $request)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();

        $allowedStatuses = ['failed', 'draft_ready', 'human_review', 'blocked'];
        if (!in_array($tx->status, $allowedStatuses)) {
            return back()->with('error', 'This transaction cannot be dismissed in its current state.');
        }

        DB::table('transactions')->where('tx_id', $txId)->update([
            'status'      => 'dismissed',
            'human_notes' => $request->input('reason') ?: ($tx->human_notes),
            'updated_at'  => now(),
        ]);

        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'tx_dismissed', [
            'reason'          => $request->input('reason'),
            'previous_status' => $tx->status,
            'triggered_by'    => auth()->id(),
        ]);

        // Dismissing still leaves the record readable — stay on the
        // Transaction Center, don't bounce back to the list.
        return redirect()->route('app.transactions.show', ['slug' => $tx->worker_slug, 'txId' => $txId])
            ->with('success', 'Transaction dismissed — removed from active queues.');
    }

    public function destroy(string $txId)
    {
        $tx  = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        $raw = json_decode($tx->raw_input ?? '{}', true);

        if (($raw['source'] ?? '') !== 'fast_track_test') {
            return back()->with('error', 'Only Fast Track test transactions can be permanently deleted.');
        }

        DB::table('transactions')->where('tx_id', $txId)->delete();

        return redirect()->route('app.workers.transactions', $tx->worker_slug)->with('success', 'Test transaction deleted.');
    }

    public function decide(string $txId, Request $request)
    {
        $tx       = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        $decision = $request->input('decision'); // 'approved' | 'rejected'

        // ── Reject: delete the Gmail draft so it can't be sent accidentally ──
        if ($decision === 'rejected' && $tx->gmail_draft_id) {
            try {
                $dep        = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();
                $credential = $dep?->credential_id
                    ? DB::table('user_gmail_credentials')->where('id', $dep->credential_id)->first()
                    : null;

                if ($credential?->refresh_token) {
                    $gmail = new \App\Platform\Services\Gmail\GmailService($credential);
                    $gmail->deleteDraft($tx->gmail_draft_id);
                }
            } catch (\Throwable $e) {
                Log::warning('Draft delete failed on reject', [
                    'tx_id' => $txId, 'error' => $e->getMessage(),
                ]);
                // Non-fatal — decision is still recorded
            }
        }

        // ── Approve: draft stays in Gmail — tenant reviews and sends themselves
        // Decision is recorded for memory enrichment and audit purposes.
        $newStatus = $decision === 'approved' ? 'approved' : 'rejected';

        DB::table('transactions')->where('tx_id', $txId)->update([
            'human_decision' => $decision,
            'human_notes'    => $request->input('notes'),
            'status'         => $newStatus,
            'updated_at'     => now(),
        ]);

        DB::table('renewal_register')->where('tx_id', $txId)->update([
            'status'     => $decision === 'approved' ? 'Approved' : 'Rejected',
            'updated_at' => now(),
        ]);

        // Approval is what unblocks the fulfillment stages (invoice, documents,
        // payment confirmation, reschedule) — advance() stops at the
        // 'human_decide' pause point until this fires. Rejected transactions
        // never enter fulfillment. Fast Track transactions DO enter
        // fulfillment (so a tenant can preview the full lifecycle) — each
        // fulfillment job individually guards against real vendor/tenant
        // emails and real asset writes when the transaction is a test run.
        //
        // A real transaction's client draft is sent up to 3 times on the
        // 30/15/0-day cadence (see ClientReminderCycleJob) — approving
        // reminder 1 or 2 just sends that reminder and waits for the next
        // scheduled one; only approving the 3rd (final) reminder actually
        // unblocks fulfillment. Fast Track never simulates real calendar
        // days, so it always treats its one draft as final.
        if ($decision === 'approved') {
            \App\Platform\SDK\UnitPlatform::markLatestClientDraftApproved($txId);

            $reminderNumber  = (int) $tx->client_reminder_number;
            $isFinalReminder = $tx->is_test || $reminderNumber === 0 || $reminderNumber >= 3;

            if ($isFinalReminder) {
                \App\Platform\SDK\UnitPlatform::advance($txId, 'human_decide');
            } else {
                \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'client_reminder_approved', ['reminder_number' => $reminderNumber]);
            }
        }

        $msg = $decision === 'approved'
            ? "✓ {$txId} approved — draft is in your Gmail, ready to review and send."
            : "✗ {$txId} rejected — draft deleted.";

        // Approving/rejecting doesn't end the transaction's story — more
        // gates and cadence rounds can still follow. Stay on the Transaction
        // Center rather than bouncing to the list.
        return redirect()->route('app.transactions.show', ['slug' => $tx->worker_slug, 'txId' => $txId])->with('success', $msg);
    }

    // ── Stage 12 (Confirm Payment) — the second and last human gate in the
    // renewal lifecycle. AVA reminds the tenant until one of these two fires;
    // neither is automatic. ─────────────────────────────────────────────────

    public function confirmRenewal(string $txId, Request $request)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();

        \App\Platform\SDK\UnitPlatform::commitOutput($txId, new \App\Platform\SDK\WorkerOutput(
            stage: 'confirm_payment',
            data:  ['confirmed' => true, 'confirmed_at' => now()->toISOString(), 'confirmed_by' => auth()->id()],
        ));

        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'payment_confirmed', ['confirmed_by' => auth()->id()]);

        // Resume from the pause point — continues into update_renewal_date,
        // archive_evidence, notify_stakeholders, schedule_next_watch.
        \App\Platform\SDK\UnitPlatform::advance($txId, 'confirm_payment');

        return back()->with('success', "✓ {$txId} — renewal confirmed. AVA is closing out this cycle.");
    }

    public function cancelRenewal(string $txId, Request $request)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();

        \App\Platform\SDK\UnitPlatform::commitOutput($txId, new \App\Platform\SDK\WorkerOutput(
            stage: 'confirm_payment',
            data:  ['confirmed' => false, 'canceled_at' => now()->toISOString(), 'canceled_by' => auth()->id(), 'reason' => $request->input('reason')],
        ));

        \App\Platform\SDK\UnitPlatform::setFulfillmentStage($txId, 'canceled');
        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'renewal_canceled', ['canceled_by' => auth()->id(), 'reason' => $request->input('reason')]);

        // Terminal — does not advance further. The asset is left as-is;
        // AssetExpiryWatchJob will naturally pick it up again on its own
        // schedule if it's still overdue.

        return back()->with('success', "○ {$txId} — renewal canceled.");
    }

    // ── Stage 10 (Request Invoice, soft gate) ────────────────────────────
    // Never blocks the pipeline — this just resolves the "attach invoice"
    // nudge whenever the tenant gets to it, real invoices assumed PDF-only.
    public function attachInvoice(string $txId, Request $request)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['invoice_file' => 'required|file|mimes:pdf|max:10240']);

        $disk = \Illuminate\Support\Facades\Storage::disk(config('filesystems.media_disk', 'public'));
        $path = $request->file('invoice_file')->storeAs(
            "invoices/{$txId}", 'invoice-' . now()->timestamp . '.pdf', config('filesystems.media_disk', 'public')
        );

        $ocr = \App\Platform\Services\InvoiceOcrService::extract($disk->path($path), auth()->id(), $tx->worker_slug, $txId);

        $memory  = json_decode($tx->memory_output ?? '{}', true) ?: [];
        $asset   = $memory['asset'] ?? 'this renewal';
        $subject = "Invoice received — {$asset}";
        $body    = "Hi,\n\nAttaching the invoice for {$asset}"
            . (!empty($ocr['amount']) ? " — {$ocr['amount']}" . (!empty($ocr['currency']) ? " {$ocr['currency']}" : '') : '')
            . (!empty($ocr['due_date']) ? ", due {$ocr['due_date']}" : '') . ".\n\nBest regards,\nFranklin";

        $clientEmail = $memory['primary_contact_email'] ?? null;
        if ($clientEmail && !$tx->is_test) {
            \App\Platform\Services\EmailDispatcher::send(
                'ava_invoice_client_message', $clientEmail, $memory['matched_client'] ?? 'there', null,
                ['{asset}' => $asset], ['subject' => $subject, 'body' => $body]
            );
        }

        $output = [
            'status'         => 'attached',
            'file_path'      => $path,
            'ocr'            => $ocr,
            'attached_at'    => now()->toISOString(),
            'client_messages' => [[
                'sequence' => 1, 'to' => $clientEmail, 'subject' => $subject, 'body' => $body,
                'sent_at'  => now()->toISOString(),
            ]],
        ];

        \App\Platform\SDK\UnitPlatform::commitOutput($txId, new \App\Platform\SDK\WorkerOutput(stage: 'request_invoice', data: $output));
        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'invoice_attached', ['ocr' => $ocr]);

        return back()->with('success', "✓ Invoice attached for {$txId}.");
    }

    // ── Stage 11 (Request Documents, skippable) ──────────────────────────
    public function skipDocuments(string $txId)
    {
        DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();

        \App\Platform\SDK\UnitPlatform::commitOutput($txId, new \App\Platform\SDK\WorkerOutput(
            stage: 'request_documents',
            data:  ['status' => 'skipped', 'skipped_at' => now()->toISOString(), 'skipped_by' => auth()->id()],
        ));
        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'documents_skipped', ['skipped_by' => auth()->id()]);

        return back()->with('success', "○ {$txId} — no documents needed.");
    }

    public function attachDocuments(string $txId, Request $request)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['document_file' => 'required|file|max:10240']);

        $path = $request->file('document_file')->storeAs(
            "documents/{$txId}", 'document-' . now()->timestamp . '.' . $request->file('document_file')->extension(),
            config('filesystems.media_disk', 'public')
        );

        $memory  = json_decode($tx->memory_output ?? '{}', true) ?: [];
        $asset   = $memory['asset'] ?? 'this renewal';
        $subject = "Supporting document — {$asset}";
        $body    = "Hi,\n\nAttaching a supporting document for {$asset}.\n\nBest regards,\nFranklin";

        $clientEmail = $memory['primary_contact_email'] ?? null;
        if ($clientEmail && !$tx->is_test) {
            \App\Platform\Services\EmailDispatcher::send(
                'ava_documents_client_message', $clientEmail, $memory['matched_client'] ?? 'there', null,
                ['{asset}' => $asset], ['subject' => $subject, 'body' => $body]
            );
        }

        \App\Platform\SDK\UnitPlatform::commitOutput($txId, new \App\Platform\SDK\WorkerOutput(
            stage: 'request_documents',
            data:  [
                'status' => 'attached', 'file_path' => $path, 'attached_at' => now()->toISOString(),
                'client_messages' => [['sequence' => 1, 'to' => $clientEmail, 'subject' => $subject, 'body' => $body, 'sent_at' => now()->toISOString()]],
            ],
        ));
        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'documents_attached', []);

        return back()->with('success', "✓ Document attached for {$txId}.");
    }

    // Nudging stopped after ReminderCopy::MAX_ATTEMPTS with no response —
    // nothing was lost, the gate is exactly where it was. This just clears
    // the pause so the reminder jobs pick it back up on their next run.
    public function resumeNudging(string $txId)
    {
        DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        DB::table('transactions')->where('tx_id', $txId)->update([
            'nudging_paused_at' => null,
            'updated_at'        => now(),
        ]);
        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'nudging_resumed', ['resumed_by' => auth()->id()]);

        return back()->with('success', "▶ {$txId} — reminders resumed.");
    }

    public function downloadArchive(string $txId)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        $archive = json_decode($tx->archive_output ?? '{}', true) ?: [];
        $path    = $archive['path'] ?? null;

        $disk = \Illuminate\Support\Facades\Storage::disk(config('filesystems.media_disk', 'public'));
        if (!$path || !$disk->exists($path)) {
            abort(404, 'Archive not found');
        }

        return $disk->download($path, "{$txId}-renewal-archive.pdf");
    }

    // Unauthenticated counterpart for the QR code printed on the archive —
    // validity comes entirely from the request's signature/expiry, not a
    // logged-in session.
    public function downloadArchivePublic(string $txId)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->first();
        if (!$tx) abort(404);

        $archive = json_decode($tx->archive_output ?? '{}', true) ?: [];
        $path    = $archive['path'] ?? null;

        $disk = \Illuminate\Support\Facades\Storage::disk(config('filesystems.media_disk', 'public'));
        if (!$path || !$disk->exists($path)) {
            abort(404, 'Archive not found');
        }

        return $disk->download($path, "{$txId}-renewal-archive.pdf");
    }
}

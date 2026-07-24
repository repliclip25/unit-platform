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

    public function show(string $txId)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();

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
        $stagesByKey = collect($contract->pipelineStages())->keyBy('key');

        $shell = \App\Platform\Services\WorkerShellService::build(auth()->id(), '');
        extract($shell); // workerCatalog, registryRows, registryRow, profileImg, coverImg, tokenTotal
        $firstName = explode(' ', trim(auth()->user()->name))[0];

        return view('dashboard.transaction-detail', compact(
            'tx', 'nuxRegister', 'stagesByKey', 'dep',
            'workerCatalog', 'tokenTotal', 'firstName'
        ));
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

        return redirect()->route('app.workers.transactions', $tx->worker_slug)->with('success', 'Transaction dismissed — removed from active queues.');
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
        if ($decision === 'approved') {
            \App\Platform\SDK\UnitPlatform::advance($txId, 'human_decide');
        }

        $msg = $decision === 'approved'
            ? "✓ {$txId} approved — draft is in your Gmail, ready to review and send."
            : "✗ {$txId} rejected — draft deleted.";

        return redirect()->route('app.workers.transactions', $tx->worker_slug)->with('success', $msg);
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
}

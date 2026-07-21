<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $filter = $request->query('filter', 'all');
        $query  = DB::table('transactions')->where('user_id', $userId)->orderByDesc('id');
        if ($request->filled('deployment')) $query->where('deployment_id', (int) $request->query('deployment'));
        if ($filter === 'draft_ready')   $query->where('status', 'draft_ready');
        elseif ($filter === 'approved')  $query->whereIn('status', ['approved','sent']);
        elseif ($filter === 'failed')    $query->where('status', 'failed');
        elseif ($filter === 'dismissed') $query->where('status', 'dismissed');
        else $query->where('status', '!=', 'dismissed'); // default: hide dismissed
        $transactions  = $query->paginate(25);
        $currentFilter = $filter;

        $shell = \App\Platform\Services\WorkerShellService::build($userId, '');
        extract($shell); // workerCatalog, registryRows, registryRow, profileImg, coverImg, tokenTotal
        $firstName = explode(' ', trim(auth()->user()->name))[0];

        return view('dashboard.transactions', compact(
            'transactions', 'currentFilter',
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

        $shell = \App\Platform\Services\WorkerShellService::build(auth()->id(), '');
        extract($shell); // workerCatalog, registryRows, registryRow, profileImg, coverImg, tokenTotal
        $firstName = explode(' ', trim(auth()->user()->name))[0];

        return view('dashboard.transaction-detail', compact(
            'tx', 'nuxRegister',
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
            'low_confidence'   => $draft['low_confidence'] ?? false,
            'gmail_draft_id'   => $tx->gmail_draft_id,
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

        return redirect()->route('transactions')->with('success', 'Transaction dismissed — removed from active queues.');
    }

    public function destroy(string $txId)
    {
        $tx  = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        $raw = json_decode($tx->raw_input ?? '{}', true);

        if (($raw['source'] ?? '') !== 'fast_track_test') {
            return back()->with('error', 'Only Fast Track test transactions can be permanently deleted.');
        }

        DB::table('transactions')->where('tx_id', $txId)->delete();

        return redirect()->route('transactions')->with('success', 'Test transaction deleted.');
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

        $msg = $decision === 'approved'
            ? "✓ {$txId} approved — draft is in your Gmail, ready to review and send."
            : "✗ {$txId} rejected — draft deleted.";

        return redirect()->route('transactions')->with('success', $msg);
    }
}

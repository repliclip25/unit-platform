<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Platform\SDK\Columns\TransactionColumns;

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

        // TX- selector — lets the tenant jump to any other transaction that
        // entered this deployment's pipeline without going back to the list
        // view first. Same deployment scope as index(), same noise filter
        // (hide dismissed/filtered — nothing a tenant would deliberately
        // look for by number here).
        $otherTransactions = DB::table('transactions')
            ->where('deployment_id', $tx->deployment_id)
            ->whereNotIn('status', ['dismissed', 'filtered_out'])
            ->orderByDesc('id')
            ->limit(100)
            ->get(['tx_id', 'category', 'status', 'created_at']);

        // Log Transaction (stage 'log_entry') has no output_column of its
        // own — nothing renders on that card today. The right panel is the
        // first place this data appears: the actual renewal_register row
        // this stage wrote, not a re-derivation of it.
        $renewalRegisterRow = DB::table('renewal_register')->where('tx_id', $txId)->first();

        $shell = \App\Platform\Services\WorkerShellService::build(auth()->id(), '');
        extract($shell); // workerCatalog, registryRows, registryRow, profileImg, coverImg, tokenTotal
        $firstName = explode(' ', trim(auth()->user()->name))[0];

        return view('dashboard.transaction-detail', compact(
            'tx', 'nuxRegister', 'stagesByKey', 'stages', 'dep',
            'workerCatalog', 'tokenTotal', 'firstName',
            'otherTransactions', 'renewalRegisterRow'
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

        // One bulk query for every stage's completion time + duration, keyed
        // by each stage's log_stage_key (transaction_stage_log uses each
        // job's own short internal alias — e.g. 'draft' for the
        // 'draft_email' stage — not the full pipelineStages() key;
        // log_stage_key is the contract's own declared mapping between the
        // two, already used by UnitPlatform::stageColumns() for the same
        // reconciliation). Latest row per stage_key wins — orderBy('id')
        // then last-write-wins in the loop below, cheaper than a query per
        // stage. duration_ms is only non-null for stages that log a
        // 'started' row (UnitPlatform::stageStarted()) before completing —
        // the drafting chain always has one via setStatus(); the fulfillment
        // stages only got one recently, so older transactions show a
        // timestamp with no duration, which is honest, not a bug.
        $completedAtByLogKey  = [];
        $durationMsByLogKey   = [];
        foreach (
            DB::table('transaction_stage_log')
                ->where('tx_id', $tx->tx_id)->where('event', 'completed')
                ->orderBy('id')->get(['stage_key', 'created_at', 'duration_ms'])
            as $row
        ) {
            $completedAtByLogKey[$row->stage_key] = $row->created_at;
            $durationMsByLogKey[$row->stage_key]  = $row->duration_ms;
        }

        // Which model actually answered each AI call — usage_events already
        // logs this for every real call (it's what billing/cost is computed
        // from), so this is genuine historical record, not current config.
        // The prompt text itself is never persisted anywhere (no per-call
        // snapshot exists — deployment_prompt_overrides is live config, not
        // versioned), so deliberately not attempting to show "the exact
        // prompt used" here — that would be a current-state guess dressed
        // up as a historical fact. Same log_stage_key reconciliation as the
        // stage_log lookups above (usage_events.stage uses the same short
        // aliases — 'read', 'classify', 'memory', 'draft').
        $modelByLogKey = [];
        foreach (
            DB::table('usage_events')
                ->where('tx_id', $tx->tx_id)
                ->orderBy('id')->get(['stage', 'model'])
            as $row
        ) {
            $modelByLogKey[$row->stage] = $row->model;
        }

        // Show the full cadence up front, not just what's happened so far —
        // real (non-Fast-Track) transactions get up to 3 rounds, so a
        // not-yet-drafted 2nd/3rd round renders as an upcoming placeholder
        // tab instead of silently not existing. Fast Track is exempt from
        // the cadence entirely (its one draft is always final), so it only
        // ever shows the one real round.
        // Two genuinely different entry routes exist — a real inbound Gmail
        // email, or AssetExpiryWatchJob detecting a renewal date directly
        // from the asset registry (no email at all; see that job's own
        // docblock for why 'read'/'classify' are synthesized rather than
        // run). The tenant should be able to tell which one fired without
        // reading a raw source string.
        $source          = json_decode($tx->raw_input ?? '{}', true)['source'] ?? null;
        $isDetectRoute   = $source === 'asset_watch';
        $isHumanTrigger  = $source === 'human_trigger';
        $routeOverrides  = match (true) {
            $isDetectRoute  => [
                'webhook'    => ['label' => 'Detected — Asset Watch', 'sub' => 'Renewal date crossed a watch threshold in your asset registry'],
                'read_email' => ['label' => 'Asset Data',             'sub' => 'No inbound email — pulled directly from the asset registry'],
            ],
            // "Renew Now" — a tenant manually starting a renewal from the
            // asset/group view, no email and no watch threshold involved.
            $isHumanTrigger => [
                'webhook'    => ['label' => 'Detected — Manual Push', 'sub' => 'Started manually from the asset registry ("Renew Now")'],
                'read_email' => ['label' => 'Asset Data',             'sub' => 'No inbound email — pulled directly from the asset registry'],
            ],
            default         => [],
        };

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

        return collect($rawStages)->map(function ($stage, $i) use ($tx, $currentIdx, $reminders, $clientDrafts, $routeOverrides, $completedAtByLogKey, $durationMsByLogKey, $modelByLogKey) {
            $state = $i < $currentIdx ? 'done' : ($i === $currentIdx ? 'active' : 'pending');

            // Not every worker's pipelineStages() declares output_column on
            // every entry (AVA always does, even as null; NUX's don't) —
            // this page is shared across all workers, so treat it as optional.
            $outputColumn = $stage['output_column'] ?? null;
            $content = $outputColumn && ($tx->{$outputColumn} ?? null)
                ? json_decode($tx->{$outputColumn}, true)
                : null;

            $stageReminders = array_values(array_filter($reminders, fn($r) => ($r['stage_key'] ?? null) === $stage['key']));

            // A stage-gated stage (Request Invoice/Documents, Confirm
            // Payment, Generate Closeout Report) that's already behind us
            // but has no output at all was skipped by a disabled AVA
            // Settings gate, not just "hasn't happened yet" — worth telling
            // the tenant apart from a normal completed stage, especially
            // for confirm_payment where "skipped" means no one ever
            // confirmed money changed hands.
            $skippedByGate = $state === 'done' && empty($content)
                && !\App\Platform\SDK\UnitPlatform::gateEnabled($tx->deployment_id, $stage['key'], true);

            return array_merge(['gate_type' => null], $stage, $routeOverrides[$stage['key']] ?? [], [
                'i'              => $i,
                'state'          => $state,
                'content'        => $content,
                'skipped_by_gate'=> $skippedByGate,
                'reminders'      => $stageReminders,
                'completed_at'   => $completedAtByLogKey[$stage['log_stage_key'] ?? $stage['key']] ?? null,
                'duration_ms'    => $durationMsByLogKey[$stage['log_stage_key'] ?? $stage['key']] ?? null,
                'ai_model'       => $modelByLogKey[$stage['log_stage_key'] ?? $stage['key']] ?? null,
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
        $dep      = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();

        $msg = $decision === 'rejected'
            ? $this->rejectTransaction($tx, $txId, $request)
            : $this->approveTransaction($tx, $dep, $txId, $request);

        // Approving/rejecting doesn't end the transaction's story — more
        // gates and cadence rounds can still follow. Stay on the Transaction
        // Center rather than bouncing to the list.
        return redirect()->route('app.transactions.show', ['slug' => $tx->worker_slug, 'txId' => $txId])->with('success', $msg);
    }

    // human_decide now sits BEFORE push_draft (see AvaWorker::pipelineStages()'s
    // comment on why that moved) — nothing is ever created in Gmail before a
    // decision is made, so rejecting no longer means deleting anything.
    private function rejectTransaction(object $tx, string $txId, Request $request): string
    {
        $this->recordDecision($txId, 'rejected', $request->input('notes'));

        return "✗ {$txId} rejected — nothing was sent.";
    }

    // ── Approve: records the decision, then dispatches PushToGmailJob to
    // actually deliver the draft (Gmail, or in-app if no inbox connected) —
    // approval is what triggers delivery now, not the other way around.
    // Decision is recorded for memory enrichment and audit purposes
    // regardless of what happens next.
    //
    // Approval is what unblocks the fulfillment stages (invoice, documents,
    // payment confirmation, reschedule) — advance() stops at the
    // 'human_decide' pause point until this fires. Rejected transactions
    // never enter fulfillment. Fast Track transactions DO enter fulfillment
    // (so a tenant can preview the full lifecycle) — each fulfillment job
    // individually guards against real vendor/tenant emails and real asset
    // writes when the transaction is a test run.
    //
    // A real transaction's client draft is sent up to 3 times on the
    // 30/15/0-day cadence (see ClientReminderCycleJob) — approving any round
    // now delivers that round's draft (it has to reach the tenant either
    // way), but only the 3rd (final) round's approval unblocks true
    // fulfillment (invoice/payment). Fast Track never simulates real
    // calendar days, so it always treats its one draft as final.
    // Approve & Proceed (skip_cadence) is the human override for "already
    // closed this outside AVA" — no draft is ever created for it at all,
    // since nothing needs to go out over email for a deal closed elsewhere.
    private function approveTransaction(object $tx, ?object $dep, string $txId, Request $request): string
    {
        $this->recordDecision($txId, 'approved', $request->input('notes'));
        \App\Platform\SDK\UnitPlatform::markLatestClientDraftApproved($txId);

        $skipCadence = (bool) $request->boolean('skip_cadence');

        if ($skipCadence) {
            // Persisted (not just logged) so NotifyCustomerJob — which may
            // run much later, after invoice/documents/payment — can also
            // skip its own client-facing send. A deal closed outside AVA
            // shouldn't get an AVA-generated "your renewal is complete"
            // email either, and no draft should ever be created for this
            // round or any later one.
            DB::table('transactions')->where('tx_id', $txId)->update([TransactionColumns::CADENCE_SKIPPED => true]);
            \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'human_decide_skip_cadence', [
                'reason' => 'Closed outside AVA — remaining reminder rounds skipped',
            ]);
            // Resume the scan from AFTER push_draft — skips delivery
            // entirely and goes straight into fulfillment.
            \App\Platform\SDK\UnitPlatform::advance($txId, 'push_draft');

            return "✓ {$txId} approved — remaining reminders skipped, moved straight to fulfillment.";
        }

        // Message gate — "Send Reminders (3 cadence)" master switch on AVA
        // Settings. Off means every transaction behaves like Fast Track: the
        // first (only) draft is always treated as final, same as if the
        // tenant chose a single-shot draft from the start.
        $cadenceOn       = \App\Platform\SDK\UnitPlatform::gateEnabled($tx->deployment_id, 'client_cadence', true);
        $reminderNumber  = (int) $tx->client_reminder_number;
        $isFinalReminder = $tx->is_test || !$cadenceOn || $reminderNumber === 0 || $reminderNumber >= 3;

        // send_mode = 'direct' — the tenant has opted for UNIT to send the
        // moment they approve, instead of leaving the Gmail draft for them
        // to open and send themselves (the 'draft' default). Same OAuth
        // scope either way — gmail.compose already covers sending, not just
        // drafting; this is a behavior toggle, not a permissions change.
        // Never applies to Fast Track (no real recipient) — PushToGmailJob
        // itself already exempts Fast Track from ever really sending.
        $autoSend = !$tx->is_test && ($dep->send_mode ?? 'draft') === 'direct';

        \App\Workers\AVA\Jobs\PushToGmailJob::dispatch($txId, autoSend: $autoSend, advanceAfter: $isFinalReminder)
            ->onQueue(\App\Platform\SDK\UnitPlatform::getInput($txId)->queue);

        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'client_reminder_approved', ['reminder_number' => $reminderNumber, 'final' => $isFinalReminder]);

        return $autoSend ? "✓ {$txId} approved and sent." : $this->approvedMessage($txId);
    }

    // PushToGmailJob skips the Gmail API entirely when no inbox is connected
    // (in_app_only) — checked here via the same credential resolution
    // PushToGmailJob itself uses (UnitPlatform::getInput()'s fallback chain:
    // explicit credential_id in raw_input → deployment's credential_id →
    // any credential for this user), not a narrower deployment-only lookup,
    // since the draft doesn't exist yet at this point to check gmail_draft_id
    // against — delivery is dispatched, not synchronous, under this order.
    private function approvedMessage(string $txId): string
    {
        $credential = \App\Platform\SDK\UnitPlatform::getInput($txId)->credential;

        return $credential?->refresh_token
            ? "✓ {$txId} approved — draft is in your Gmail, ready to review and send."
            : "✓ {$txId} approved — no Gmail connected, so copy the draft or open it in your email client below.";
    }

    private function recordDecision(string $txId, string $decision, ?string $notes): void
    {
        DB::table('transactions')->where('tx_id', $txId)->update([
            'human_decision' => $decision,
            'human_notes'    => $notes,
            'status'         => $decision,
            'updated_at'     => now(),
        ]);

        DB::table('renewal_register')->where('tx_id', $txId)->update([
            'status'     => $decision === 'approved' ? 'Approved' : 'Rejected',
            'updated_at' => now(),
        ]);
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
    // nudge whenever the tenant gets to it. Accepts a PDF/Word file (OCR'd
    // for amount/dates) or a URL (a Stripe/Xero/QuickBooks invoice link —
    // nothing to extract from, and fetching arbitrary external URLs
    // server-side isn't something to do without it being asked for
    // explicitly, so it's stored as a plain reference, OCR skipped).
    public function attachInvoice(string $txId, Request $request)
    {
        $tx = DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        $request->validate([
            'invoice_file' => 'required_without:invoice_url|nullable|file|mimes:pdf,docx|max:10240',
            'invoice_url'  => 'required_without:invoice_file|nullable|url|max:2048',
        ]);

        $memory  = json_decode($tx->memory_output ?? '{}', true) ?: [];
        $asset   = $memory['asset'] ?? 'this renewal';

        if ($request->hasFile('invoice_file')) {
            $file      = $request->file('invoice_file');
            $extension = strtolower($file->getClientOriginalExtension()) ?: 'pdf';
            $disk      = \Illuminate\Support\Facades\Storage::disk(config('filesystems.media_disk', 'public'));
            $path      = $file->storeAs(
                "invoices/{$txId}", 'invoice-' . now()->timestamp . '.' . $extension, config('filesystems.media_disk', 'public')
            );
            $ocr = \App\Platform\Services\InvoiceOcrService::extract($disk->path($path), auth()->id(), $tx->worker_slug, $txId, $tx->deployment_id, $extension);
            $referenceNote = '';
        } else {
            // URL case — nothing uploaded, nothing to OCR.
            $path = null;
            $ocr  = [];
            $referenceNote = " — {$request->input('invoice_url')}";
        }

        $subject = "Invoice received — {$asset}";
        $body    = "Hi,\n\nAttaching the invoice for {$asset}"
            . (!empty($ocr['amount']) ? " — {$ocr['amount']}" . (!empty($ocr['currency']) ? " {$ocr['currency']}" : '') : '')
            . (!empty($ocr['due_date']) ? ", due {$ocr['due_date']}" : '') . "{$referenceNote}.\n\nBest regards,\nFranklin";

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
            'url'            => $request->input('invoice_url'),
            'ocr'            => $ocr,
            'attached_at'    => now()->toISOString(),
            'client_messages' => [[
                'sequence' => 1, 'to' => $clientEmail, 'subject' => $subject, 'body' => $body,
                'sent_at'  => now()->toISOString(),
            ]],
        ];

        \App\Platform\SDK\UnitPlatform::commitOutput($txId, new \App\Platform\SDK\WorkerOutput(stage: 'request_invoice', data: $output));
        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'invoice_attached', ['ocr' => $ocr, 'url' => $request->input('invoice_url')]);

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

    // Approving round 1 authorizes the whole 30/15/0-day cadence, not just
    // that one message (see ClientReminderCycleJob/PushToGmailJob) — this
    // is the off-switch. Available at any point after that approval, distinct
    // from Reject: rejecting deletes the draft and blocks fulfillment
    // entirely, this only stops further reminders. Fulfillment (invoice,
    // payment, etc.) is untouched — the renewal itself is still open, AVA
    // just stops nudging the client about it.
    public function stopCadence(string $txId)
    {
        DB::table('transactions')->where('tx_id', $txId)->where('user_id', auth()->id())->firstOrFail();
        DB::table('transactions')->where('tx_id', $txId)->update([
            TransactionColumns::CADENCE_STOPPED => true,
            'updated_at'                        => now(),
        ]);
        \App\Platform\SDK\UnitPlatform::log('ava', $txId, 'client_reminder_cadence_stopped', ['stopped_by' => auth()->id()]);

        return back()->with('success', "■ {$txId} — remaining reminders stopped.");
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

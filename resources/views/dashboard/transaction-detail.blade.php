<x-app-layout title="Transaction {{ $tx->tx_id }}">

    <div class="mb-4">
        <a href="{{ route('transactions') }}" class="text-gray-500 text-sm hover:text-white">← Back to Transactions</a>
    </div>

    @php
        $read     = $tx->read_output     ? json_decode($tx->read_output)     : null;
        $memory   = $tx->memory_output   ? json_decode($tx->memory_output)   : null;
        $classify = $tx->classify_output ? json_decode($tx->classify_output) : null;
        $draft    = $tx->draft_output    ? json_decode($tx->draft_output)    : null;
        $rawInput = json_decode($tx->raw_input ?? '{}', true);
        $source   = $rawInput['source'] ?? 'unknown';
        $isFastTrack    = $source === 'fast_track_test';
        $isFailed       = $tx->status === 'failed';
        $isDismissed    = $tx->status === 'dismissed';
        $canRefire      = $isFailed && !$isFastTrack;
        $canDismiss     = in_array($tx->status, ['failed','draft_ready','human_review','blocked']);
        $canDelete      = $isFastTrack;

        // Determine failure type: data failure vs infrastructure failure
        // Infrastructure failures: no pipeline output at all (job never ran or crashed before producing output)
        // Data failures: pipeline ran but produced a bad result (low confidence, unassigned, etc.)
        $hasAnyOutput    = $read || $memory || $classify;
        $lowConfidence   = $memory && ($memory->confidence ?? 100) < 70;
        $unassigned      = $memory && str_contains(strtolower($memory->matched_client ?? ''), 'unassigned');
        $failureType     = null;
        if ($isFailed) {
            $failureType = $hasAnyOutput ? 'data' : 'infrastructure';
        }

        $statusColors = [
            'draft_ready'  => 'bg-brand/15 text-brand',
            'failed'       => 'bg-red-900 text-red-300',
            'dismissed'    => 'bg-gray-800 text-gray-500',
            'human_review' => 'bg-amber-900 text-amber-300',
            'approved'     => 'bg-green-900 text-green-300',
            'sent'         => 'bg-green-900 text-green-300',
            'blocked'      => 'bg-orange-900 text-orange-300',
        ];
        $statusColor = $statusColors[$tx->status] ?? 'bg-gray-800 text-gray-400';
    @endphp

    @if(session('success'))
        <div class="mb-4 bg-green-900/40 border border-green-700/40 text-green-300 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900/40 border border-red-700/40 text-red-300 rounded-xl px-5 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Header --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 mb-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="font-mono text-gray-500 text-sm">{{ $tx->tx_id }}</span>
                    @if($tx->priority)
                        <span class="text-xs px-2 py-0.5 rounded-full {{ in_array($tx->priority, ['High','Critical']) ? 'bg-amber-900 text-amber-300' : 'bg-gray-800 text-gray-400' }}">
                            {{ $tx->priority }}
                        </span>
                    @endif
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $statusColor }}">{{ $tx->status }}</span>
                    @if($isFastTrack)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-900/40 text-yellow-400 border border-yellow-800/40">⚡ Fast Track Test</span>
                    @endif
                </div>
                <h2 class="text-white text-xl font-semibold">{{ $tx->category ?? 'Processing...' }}</h2>
                @if($tx->worker_slug === 'nux' && $nuxRegister)
                    <p class="text-gray-400 text-sm mt-1">
                        {{ strtoupper($nuxRegister->source_platform ?? '') }} → {{ implode(', ', json_decode($nuxRegister->target_channels ?? '[]', true) ?: []) }} · {{ $nuxRegister->topic ?? '—' }}
                    </p>
                @elseif($memory)
                    <p class="text-gray-400 text-sm mt-1">
                        {{ $memory->matched_client ?? '—' }} · {{ $memory->asset ?? '—' }} · {{ $memory->primary_contact_name ?? '—' }}
                    </p>
                @endif
                <p class="text-gray-600 text-xs mt-1">{{ \Carbon\Carbon::parse($tx->created_at)->format('M j, Y · g:i A') }} · {{ $source }}</p>
            </div>
            @if($tx->gmail_draft_id)
                <div class="text-right shrink-0">
                    <p class="text-gray-500 text-xs mb-1">Gmail Draft</p>
                    <p class="text-brand text-xs font-mono">{{ $tx->gmail_draft_id }}</p>
                    <p class="text-green-400 text-xs mt-1">✓ Saved in Gmail</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Failure context banner --}}
    @if($isFailed)
    <div class="mb-4 rounded-xl border px-5 py-4
        {{ $failureType === 'infrastructure' ? 'bg-red-900/20 border-red-700/40' : 'bg-amber-900/20 border-amber-700/40' }}">
        <div class="flex items-start gap-3">
            <span class="{{ $failureType === 'infrastructure' ? 'text-red-400' : 'text-amber-400' }} mt-0.5 text-base shrink-0">
                {{ $failureType === 'infrastructure' ? '✕' : '⚠' }}
            </span>
            <div class="flex-1">
                @if($failureType === 'infrastructure')
                    <p class="text-red-300 font-semibold text-sm mb-1">Infrastructure failure</p>
                    <p class="text-gray-400 text-xs leading-relaxed">
                        The pipeline job crashed before completing — likely a transient error (token expiry, queue restart, API timeout).
                        <strong class="text-gray-300">Re-firing is safe</strong> — the original email will be re-processed from scratch.
                    </p>
                @endif
                @if($failureType === 'data')
                    <p class="text-amber-300 font-semibold text-sm mb-1">Data failure</p>
                    <p class="text-gray-400 text-xs leading-relaxed">
                        The pipeline ran but couldn't complete due to missing or mismatched data.
                        @if($lowConfidence) Confidence was {{ $memory->confidence }}% — below the required threshold.@endif
                        @if($unassigned) No client is linked to this asset.@endif
                        <strong class="text-gray-300">Re-firing without fixing the underlying data will produce the same result.</strong>
                    </p>
                @endif
            </div>
            {{-- Action buttons inside banner --}}
            <div class="flex flex-col items-end gap-2 shrink-0">
                @if($failureType === 'infrastructure' && $canRefire)
                    <form method="POST" action="{{ route('transactions.refire', $tx->tx_id) }}">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1.5 rounded-lg font-semibold transition bg-red-700 hover:bg-red-600 text-white">
                            ↺ Re-fire
                        </button>
                    </form>
                @endif
                @if($failureType === 'data')
                    <a href="{{ route('memory') }}" class="text-xs px-3 py-1.5 rounded-lg font-semibold transition bg-amber-600 hover:bg-amber-500 text-white whitespace-nowrap">
                        Fix in Memory →
                    </a>
                    @if($canDismiss)
                    <form method="POST" action="{{ route('transactions.dismiss', $tx->tx_id) }}">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1.5 rounded-lg font-semibold transition border border-gray-600 text-gray-400 hover:bg-gray-700/40 whitespace-nowrap">
                            Dismiss
                        </button>
                    </form>
                    @endif
                    @if($canRefire)
                    <form method="POST" action="{{ route('transactions.refire', $tx->tx_id) }}">
                        @csrf
                        <button type="submit"
                                class="text-xs text-gray-500 hover:text-amber-400 underline underline-offset-2 transition"
                                onclick="return confirm('Re-firing will not fix the data issue — it will likely fail again. Fix the missing client or asset in Memory first.\n\nContinue anyway?')">
                            Re-fire anyway
                        </button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Dismissed notice --}}
    @if($isDismissed)
    <div class="mb-4 bg-gray-800/40 border border-gray-700/40 rounded-xl px-5 py-3 flex items-center gap-3">
        <span class="text-gray-500 text-sm">○</span>
        <p class="text-gray-500 text-sm flex-1">This transaction was dismissed and removed from active queues. The audit trail is preserved below.</p>
    </div>
    @endif

    <div class="grid grid-cols-2 gap-6">

        {{-- Left column --}}
        <div class="space-y-4">

            {{-- Read output --}}
            @if($read)
            <div class="bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-3 border-b border-gray-800 flex items-center gap-2">
                    <span class="w-5 h-5 bg-blue-900 text-blue-300 rounded text-xs flex items-center justify-center font-bold">1</span>
                    <h3 class="text-white text-sm font-medium">Read</h3>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <div>
                        <p class="text-gray-500 text-xs mb-1">Summary</p>
                        <p class="text-gray-200 text-sm">{{ $read->plain_english_summary }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Due Date</p>
                            <p class="text-white text-sm">{{ $read->due_date_or_deadline ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Urgency</p>
                            <p class="text-amber-400 text-sm">{{ $read->urgency }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs mb-1">Risk if ignored</p>
                        <p class="text-gray-400 text-sm">{{ $read->risk_if_ignored }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Memory output --}}
            @if($memory)
            <div class="bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-3 border-b border-gray-800 flex items-center gap-2">
                    <span class="w-5 h-5 bg-brand/10 text-brand rounded text-xs flex items-center justify-center font-bold">2</span>
                    <h3 class="text-white text-sm font-medium">Memory Lookup</h3>
                    <span class="ml-auto text-xs text-green-400">{{ $memory->confidence }}% confidence</span>
                </div>
                <div class="px-5 py-4 space-y-2">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Client</p>
                            <p class="text-white text-sm">{{ $memory->matched_client }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Asset</p>
                            <p class="text-white text-sm">{{ $memory->asset }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Contact</p>
                            <p class="text-white text-sm">{{ $memory->primary_contact_name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs mb-1">Email</p>
                            <p class="text-gray-300 text-sm">{{ $memory->primary_contact_email }}</p>
                        </div>
                    </div>
                    @if(!empty($memory->ava_rule))
                    <div>
                        <p class="text-gray-500 text-xs mb-1">Rule Applied</p>
                        <p class="text-brand text-xs">{{ $memory->ava_rule }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- NUX: repurposed copies --}}
            @if($tx->worker_slug === 'nux' && $nuxRegister)
            @php
                $nuxCopies = json_decode($nuxRegister->repurposed_copies ?? '[]', true) ?: [];
                $nuxChannels = json_decode($nuxRegister->target_channels ?? '[]', true) ?: [];
            @endphp
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px">
                <div style="padding:12px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px">
                    <span style="width:20px;height:20px;background:rgba(94,234,212,.15);color:#5eead4;border-radius:4px;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center">⇄</span>
                    <h3 style="color:var(--text-primary);font-size:13px;font-weight:500">Repurposed Content</h3>
                </div>
                <div style="padding:16px 20px;display:flex;flex-direction:column;gap:14px">
                    @forelse($nuxCopies as $copy)
                    <div>
                        <p style="font-size:11px;font-weight:700;color:#5eead4;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">{{ strtoupper($copy['channel'] ?? '') }}</p>
                        <p style="font-size:13px;color:var(--text-primary);white-space:pre-wrap;line-height:1.6;background:var(--bg-surface);border-radius:8px;padding:10px 12px">{{ $copy['copy'] ?? '' }}</p>
                        <p style="font-size:11px;color:var(--text-muted);margin-top:4px">{{ $copy['char_count'] ?? 0 }} characters</p>
                    </div>
                    @empty
                    <p style="font-size:13px;color:var(--text-muted)">No copies available.</p>
                    @endforelse

                    @if($nuxRegister->image_url)
                    <div style="border-top:1px solid var(--border);padding-top:14px">
                        <p style="font-size:11px;font-weight:700;color:#5eead4;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Generated Image</p>
                        <img src="{{ $nuxRegister->image_url }}" alt="NUX generated image"
                             style="max-width:100%;border-radius:8px;border:1px solid var(--border)">
                    </div>
                    @endif

                    @if($nuxRegister->draft_summary)
                    <p style="font-size:12px;color:var(--text-muted);border-top:1px solid var(--border);padding-top:10px">{{ $nuxRegister->draft_summary }}</p>
                    @endif
                </div>
            </div>
            @endif

        </div>

        {{-- Right column --}}
        <div class="space-y-4">

            {{-- Classify --}}
            @if($classify)
            <div class="bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-3 border-b border-gray-800 flex items-center gap-2">
                    <span class="w-5 h-5 bg-amber-900 text-amber-300 rounded text-xs flex items-center justify-center font-bold">3</span>
                    <h3 class="text-white text-sm font-medium">Classification</h3>
                </div>
                <div class="px-5 py-4 grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-gray-500 text-xs mb-1">Category</p>
                        <p class="text-white text-sm">{{ $classify->category }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs mb-1">Priority</p>
                        <p class="text-amber-400 text-sm">{{ $classify->priority }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-500 text-xs mb-1">Required Action</p>
                        <p class="text-gray-300 text-sm">{{ $classify->required_action }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Draft --}}
            @if($draft)
            <div class="bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-3 border-b border-gray-800 flex items-center gap-2">
                    <span class="w-5 h-5 bg-green-900 text-green-300 rounded text-xs flex items-center justify-center font-bold">4</span>
                    <h3 class="text-white text-sm font-medium">Draft Email</h3>
                </div>
                <div class="px-5 py-4 space-y-3">
                    <div>
                        <p class="text-gray-500 text-xs mb-1">To</p>
                        <p class="text-white text-sm">{{ $draft->to }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs mb-1">Subject</p>
                        <p class="text-white text-sm">{{ $draft->subject }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs mb-1">Body</p>
                        <pre class="text-gray-300 text-xs whitespace-pre-wrap bg-gray-800 rounded-lg p-3">{{ $draft->body }}</pre>
                    </div>
                    @if($draft->human_review_note)
                    <div class="bg-amber-950 border border-amber-800 rounded-lg p-3">
                        <p class="text-amber-300 text-xs font-medium mb-1">Review Note</p>
                        <p class="text-amber-200 text-xs">{{ $draft->human_review_note }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Human decision --}}
            <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-800">
                    <p class="text-white text-sm font-semibold">Review &amp; Decide</p>
                    @if($tx->gmail_draft_id)
                        <p class="text-gray-500 text-xs mt-0.5">
                            <span class="text-green-400">●</span> Draft saved in your Gmail Drafts folder —
                            open Gmail to edit and send it yourself.
                        </p>
                        <p class="text-gray-600 text-xs mt-1">
                            <strong class="text-gray-400">Approve</strong> marks it as reviewed · <strong class="text-gray-400">Reject</strong> deletes the draft from Gmail.
                        </p>
                    @else
                        <p class="text-gray-500 text-xs mt-0.5">No Gmail draft — decision recorded for learning only.</p>
                    @endif
                </div>
                <form method="POST" action="{{ route('transactions.decide', $tx->tx_id) }}" class="p-5 space-y-3">
                    @csrf
                    <textarea name="notes" rows="2" placeholder="Optional notes — why approved or rejected? Helps AVA improve."
                        class="w-full bg-gray-800 text-gray-200 text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none"></textarea>
                    <div class="flex gap-2">
                        <button name="decision" value="approved"
                                class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition"
                                style="background:#15803d">
                            ✓ Approve
                        </button>
                        <button name="decision" value="rejected"
                                onclick="return confirm('Reject and delete the Gmail draft?')"
                                class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition"
                                style="background:#7f1d1d">
                            ✗ Reject &amp; Discard
                        </button>
                    </div>
                </form>
            </div>
            @endif

        </div>
    </div>

    {{-- Footer actions --}}
    @if($canDismiss || $canDelete)
    <div class="mt-6 pt-4 border-t border-gray-800 flex items-center justify-between">
        <div class="flex items-center gap-3">
            @if($canDismiss)
            <form method="POST" action="{{ route('transactions.dismiss', $tx->tx_id) }}">
                @csrf
                <input type="hidden" name="reason" value="Manually dismissed from detail view">
                <button type="submit"
                        onclick="return confirm('Dismiss this transaction? It will be removed from active queues but preserved in the audit log.')"
                        class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:text-white hover:border-gray-500 transition font-medium">
                    ○ Dismiss
                </button>
            </form>
            @endif

            @if($canDelete)
            <form method="POST" action="{{ route('transactions.delete', $tx->tx_id) }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                        onclick="return confirm('Permanently delete this fast-track test transaction? This cannot be undone.')"
                        class="text-xs px-3 py-1.5 rounded-lg border border-red-900 text-red-400 hover:bg-red-900/20 transition font-medium">
                    ✕ Delete
                </button>
            </form>
            @endif
        </div>
        <p class="text-gray-700 text-xs">{{ trim(($canDismiss && !$isFastTrack ? 'Dismiss removes from active queues · ' : '') . ($canDelete ? 'Delete permanently removes test data' : ''), ' · ') }}</p>
    </div>
    @endif

</x-app-layout>

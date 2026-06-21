<x-app-layout title="Transactions">

@php
    $pendingCount = $transactions->getCollection()->where('status', 'draft_ready')->whereNull('human_decision')->count();
    $statusMeta = [
        'draft_ready'  => ['bg' => 'rgba(243,197,49,0.15)', 'color' => '#a78bfa', 'label' => 'Pending Review'],
        'approved'     => ['bg' => 'rgba(34,197,94,0.15)',  'color' => '#86efac', 'label' => 'Approved'],
        'sent'         => ['bg' => 'rgba(34,197,94,0.15)',  'color' => '#86efac', 'label' => 'Sent'],
        'failed'       => ['bg' => 'rgba(239,68,68,0.15)',  'color' => '#fca5a5', 'label' => 'Failed'],
        'human_review' => ['bg' => 'rgba(245,158,11,0.15)','color' => '#fcd34d', 'label' => 'In Review'],
        'blocked'      => ['bg' => 'rgba(249,115,22,0.15)','color' => '#fb923c', 'label' => 'Blocked'],
        'drafting'     => ['bg' => 'rgba(99,102,241,0.1)', 'color' => '#818cf8', 'label' => 'Drafting'],
        'dismissed'    => ['bg' => 'rgba(75,85,99,0.2)',   'color' => '#6b7280', 'label' => 'Dismissed'],
    ];
    $priorityColors = ['Critical'=>'#ef4444','High'=>'#f59e0b','Medium'=>'#9ca3af','Low'=>'#6b7280'];
@endphp

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-white text-lg font-bold">Transactions</h1>
        <p class="text-gray-500 text-sm mt-0.5">AVA pipeline activity · human review queue</p>
    </div>
    @if($pendingCount > 0)
    <div class="flex items-center gap-2 px-4 py-2 rounded-xl border"
         style="background:rgba(243,197,49,0.12);border-color:rgba(243,197,49,0.35)">
        <span class="w-2 h-2 rounded-full animate-pulse" style="background:#a78bfa"></span>
        <span class="text-sm font-semibold" style="color:#f3c531">{{ $pendingCount }} awaiting your review</span>
    </div>
    @endif
</div>

{{-- Filter tabs --}}
<div class="flex gap-1 mb-5 bg-gray-900 border border-gray-800 rounded-xl p-1 w-fit">
    @foreach([['all','All'],['draft_ready','Pending Review'],['approved','Approved'],['failed','Failed'],['dismissed','Dismissed']] as [$val,$label])
    <a href="{{ route('transactions', ['filter' => $val]) }}"
       class="text-xs px-3 py-1.5 rounded-lg transition {{ ($currentFilter ?? 'all') === $val ? 'bg-gray-700 text-white' : 'text-gray-500 hover:text-gray-300' }}">
        {{ $label }}
        @if($val === 'draft_ready' && $pendingCount > 0)
            <span class="ml-1 px-1.5 rounded-full text-xs font-bold"
                  style="background:rgba(243,197,49,0.35);color:#f3c531">{{ $pendingCount }}</span>
        @endif
    </a>
    @endforeach
</div>

{{-- Table --}}
<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-800">
                <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">TX</th>
                <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Asset · Client</th>
                <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Category</th>
                <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Priority</th>
                <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Draft Subject</th>
                <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Status</th>
                <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Age</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @forelse($transactions as $tx)
            @php
                $memory   = $tx->memory_output ? json_decode($tx->memory_output) : null;
                $draft    = $tx->draft_output  ? json_decode($tx->draft_output)  : null;
                $sc       = $statusMeta[$tx->status] ?? ['bg'=>'rgba(75,85,99,0.2)','color'=>'#6b7280','label'=>ucfirst(str_replace('_',' ',$tx->status))];
                $pc       = $priorityColors[$tx->priority ?? ''] ?? '#6b7280';
                $isReview = $tx->status === 'draft_ready' && !$tx->human_decision;
            @endphp
            <tr class="hover:bg-gray-800/40 transition {{ $isReview ? '' : '' }}"
                style="{{ $isReview ? 'border-left:2px solid #f3c531' : 'border-left:2px solid transparent' }}">
                <td class="px-5 py-3">
                    <span class="font-mono text-gray-400 text-xs">{{ $tx->tx_id }}</span>
                </td>
                <td class="px-5 py-3">
                    @if($memory)
                        <p class="text-white text-xs font-medium">{{ $memory->asset ?? '—' }}</p>
                        <p class="text-gray-500 text-xs">{{ $memory->matched_client ?? '—' }}</p>
                    @else
                        <span class="text-gray-600 text-xs italic">Processing…</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-gray-300 text-xs">{{ $tx->category ?? '—' }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium" style="color:{{ $pc }}">{{ $tx->priority ?? '—' }}</span>
                </td>
                <td class="px-5 py-3 max-w-xs">
                    @if($draft)
                        <p class="text-gray-300 text-xs truncate">{{ $draft->subject ?? '—' }}</p>
                        @if(!empty($draft->low_confidence))
                            <p class="text-xs mt-0.5" style="color:#f59e0b">⚠ Low confidence</p>
                        @endif
                    @else
                        <span class="text-gray-600 text-xs">—</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                          style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }}">
                        {{ $sc['label'] }}
                    </span>
                    @if($tx->human_decision)
                        <p class="text-gray-600 text-xs mt-0.5">{{ ucfirst($tx->human_decision) }}</p>
                    @endif
                </td>
                <td class="px-5 py-3 text-gray-600 text-xs whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($tx->created_at)->diffForHumans(null, true) }}
                </td>
                <td class="px-5 py-3">
                    <a href="{{ route('transactions.show', $tx->tx_id) }}"
                       class="text-xs px-3 py-1.5 rounded-lg transition font-medium"
                       style="{{ $isReview ? 'background:rgba(243,197,49,0.20);color:#f3c531;border:1px solid rgba(243,197,49,0.45)' : 'color:#6b7280' }}">
                        {{ $isReview ? 'Review →' : 'View →' }}
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-12 text-center text-gray-600 text-sm">No transactions found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($transactions->hasPages())
    <div class="px-5 py-4 border-t border-gray-800">
        {{ $transactions->appends(request()->query())->links() }}
    </div>
    @endif
</div>

</x-app-layout>

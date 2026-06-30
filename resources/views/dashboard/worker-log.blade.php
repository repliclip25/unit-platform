<x-app-layout title="{{ $dep->name }} · Log">

    @include('partials.worker-subnav')

    <div class="bg-gray-900 border border-gray-800 rounded-xl">
        <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
            <div>
                <h3 class="text-white text-sm font-semibold">Activity Log</h3>
                <p class="text-gray-500 text-xs mt-0.5">Every email this worker processed, matched, drafted, and actioned.</p>
            </div>
            <span class="text-gray-600 text-xs">{{ $entries->total() }} entries</span>
        </div>

        @forelse($entries as $entry)
            @php
                $statusColors = [
                    'Draft Ready' => 'bg-brand/15 text-brand',
                    'Approved'    => 'bg-green-900 text-green-300',
                    'Sent'        => 'bg-green-900 text-green-300',
                    'Rejected'    => 'bg-red-900 text-red-300',
                    'Pending'     => 'bg-gray-800 text-gray-400',
                ];
                $color = $statusColors[$entry->status] ?? 'bg-gray-800 text-gray-400';
            @endphp
            <div class="px-5 py-4 border-b border-gray-800 last:border-0 flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <a href="{{ route('transactions.show', $entry->tx_id) }}" class="text-xs font-mono hover:underline" style="color:var(--text-secondary)">{{ $entry->tx_id }}</a>
                        <span class="text-xs px-1.5 py-0.5 rounded {{ $color }}">{{ $entry->status }}</span>
                        @if($entry->priority)
                            <span class="text-xs text-gray-600">{{ $entry->priority }}</span>
                        @endif
                    </div>
                    <p class="text-white text-sm">{{ $entry->asset ?? '—' }}</p>
                    <div class="flex items-center gap-3 mt-1">
                        @if($entry->client)<span class="text-gray-500 text-xs">{{ $entry->client }}</span>@endif
                        @if($entry->contact)<span class="text-brand text-xs">{{ $entry->contact }}</span>@endif
                        @if($entry->due_date)<span class="text-gray-600 text-xs">due {{ \Carbon\Carbon::parse($entry->due_date)->format('M d, Y') }}</span>@endif
                    </div>
                </div>
                <span class="text-gray-600 text-xs ml-4 shrink-0">{{ \Carbon\Carbon::parse($entry->created_at)->format('M d, H:i') }}</span>
            </div>
        @empty
            <div class="px-5 py-12 text-center">
                <p class="text-gray-600 text-sm">No activity logged yet.</p>
                <p class="text-gray-700 text-xs mt-1">Once this worker processes emails, every action will appear here.</p>
            </div>
        @endforelse
    </div>

    @if($entries->hasPages())
        <div class="mt-4">{{ $entries->links() }}</div>
    @endif

</x-app-layout>

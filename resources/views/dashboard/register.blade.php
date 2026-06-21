<x-app-layout title="Renewal Register">

    <div class="bg-gray-900 border border-gray-800 rounded-xl">
        <div class="px-5 py-4 border-b border-gray-800">
            <h2 class="text-white font-semibold text-sm">Renewal Register</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">TX</th>
                    <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Category</th>
                    <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Asset</th>
                    <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Client</th>
                    <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Contact</th>
                    <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Due Date</th>
                    <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Priority</th>
                    <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Status</th>
                    <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Decision</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($register as $row)
                    @php $days = $row->due_date ? now()->diffInDays($row->due_date, false) : null; @endphp
                    <tr class="hover:bg-gray-800 transition">
                        <td class="px-5 py-3 font-mono text-gray-500 text-xs">{{ $row->tx_id }}</td>
                        <td class="px-5 py-3 text-white">{{ $row->category }}</td>
                        <td class="px-5 py-3 text-gray-300">{{ $row->asset }}</td>
                        <td class="px-5 py-3 text-gray-300">{{ $row->client }}</td>
                        <td class="px-5 py-3 text-gray-400 text-xs">{{ $row->contact }}</td>
                        <td class="px-5 py-3 text-xs">
                            @if($days !== null)
                                <span class="{{ $days <= 0 ? 'text-red-400' : ($days <= 15 ? 'text-amber-400' : 'text-gray-400') }}">
                                    {{ $row->due_date }} @if($days > 0)({{ $days }}d)@else(Expired)@endif
                                </span>
                            @else
                                <span class="text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs {{ $row->priority === 'High' || $row->priority === 'Critical' ? 'text-amber-400' : 'text-gray-500' }}">
                                {{ $row->priority }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-brand/15 text-brand">{{ $row->status }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">{{ $row->human_decision ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-5 py-8 text-center text-gray-600">No entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-app-layout>

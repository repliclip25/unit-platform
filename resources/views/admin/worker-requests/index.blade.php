<x-app-layout title="Worker Requests">
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-white font-semibold">Worker Requests</h2>
      <p class="text-gray-500 text-xs mt-0.5">Submissions from the marketplace "Request a Worker" form</p>
    </div>
    <span class="text-xs text-gray-600 border border-gray-800 rounded px-2 py-1">{{ $requests->count() }} total</span>
  </div>

  @if(session('deleted'))
    <div class="bg-red-950/40 border border-red-800/50 rounded-xl px-4 py-3 text-red-300 text-sm">Request deleted.</div>
  @endif

  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-gray-800">
          <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Submitter</th>
          <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Company</th>
          <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Org / Use case</th>
          <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Status</th>
          <th class="text-right px-4 py-3 text-gray-500 text-xs font-medium">Received</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($requests as $r)
        @php
          $statusColors = [
            'pending'   => 'bg-yellow-900/40 text-yellow-400 border-yellow-800/50',
            'contacted' => 'bg-blue-900/40 text-blue-400 border-blue-800/50',
            'scoping'   => 'bg-purple-900/40 text-purple-300 border-purple-800/50',
            'building'  => 'bg-green-900/40 text-green-400 border-green-800/50',
            'done'      => 'bg-gray-800 text-gray-400 border-gray-700',
            'declined'  => 'bg-red-950/40 text-red-400 border-red-800/50',
          ];
          $sc = $statusColors[$r->status] ?? 'bg-gray-800 text-gray-400 border-gray-700';
        @endphp
        <tr class="border-b border-gray-800/60 last:border-0 hover:bg-gray-800/30 transition">
          <td class="px-5 py-3">
            <div class="text-white text-xs font-medium">{{ $r->name }}</div>
            <div class="text-gray-500 text-xs">{{ $r->email }}</div>
          </td>
          <td class="px-4 py-3 text-gray-400 text-xs">{{ $r->company ?: '—' }}</td>
          <td class="px-4 py-3 text-gray-400 text-xs max-w-xs">
            <div class="text-gray-300 text-xs">{{ $r->org ?: '—' }}</div>
            <div class="text-gray-600 text-xs truncate" style="max-width:220px">{{ Str::limit($r->current_process, 60) }}</div>
          </td>
          <td class="px-4 py-3">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $sc }}">{{ ucfirst($r->status) }}</span>
          </td>
          <td class="px-4 py-3 text-right text-gray-600 text-xs">{{ \Carbon\Carbon::parse($r->created_at)->format('M j, Y') }}</td>
          <td class="px-4 py-3 text-right">
            <a href="{{ route('admin.worker-requests.show', $r->id) }}" class="text-xs text-gray-400 hover:text-white transition">View →</a>
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-5 py-12 text-center text-gray-600 text-sm">No requests yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
</x-app-layout>

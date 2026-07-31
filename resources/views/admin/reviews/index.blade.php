<x-app-layout title="Reviews">
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-white font-semibold">Worker Reviews</h2>
      <p class="text-gray-500 text-xs mt-0.5">Approved reviews appear on that worker's public page and feed the Service schema's aggregateRating. Pending/rejected never show publicly.</p>
    </div>
    <a href="{{ route('admin.reviews.create') }}" class="text-xs px-4 py-2 rounded-lg font-semibold ac-on">+ Add Review</a>
  </div>

  @if(session('saved'))
    <div class="bg-green-950/40 border border-green-800/50 rounded-xl px-4 py-3 text-green-300 text-sm">{{ session('saved') }}</div>
  @endif

  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-gray-800">
          <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Author</th>
          <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Worker</th>
          <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Rating</th>
          <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Quote</th>
          <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Status</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($reviews as $review)
        <tr class="border-b border-gray-800/60 last:border-0 hover:bg-gray-800/30 transition">
          <td class="px-5 py-3">
            <div class="text-white text-xs font-medium">{{ $review->author_name }}</div>
            @if($review->author_company)
              <div class="text-gray-600 text-xs">{{ $review->author_company }}</div>
            @endif
          </td>
          <td class="px-4 py-3 text-gray-400 text-xs uppercase">{{ $review->worker_slug }}</td>
          <td class="px-4 py-3 text-gray-400 text-xs">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
          <td class="px-4 py-3 text-gray-400 text-xs max-w-xs truncate">{{ $review->quote }}</td>
          <td class="px-4 py-3">
            @if($review->status === 'approved')
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-900/40 text-green-400 border border-green-800/50">Approved</span>
            @elseif($review->status === 'rejected')
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-900/40 text-red-400 border border-red-800/50">Rejected</span>
            @else
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-800 text-gray-500 border border-gray-700">Pending</span>
            @endif
          </td>
          <td class="px-4 py-3 text-right">
            <div class="flex items-center gap-3 justify-end">
              @if($review->status !== 'approved')
                <form method="POST" action="{{ route('admin.reviews.approve', $review->id) }}">
                  @csrf
                  <button type="submit" class="text-xs font-semibold px-2.5 py-1 rounded-lg transition" style="background:rgba(34,197,94,0.12);color:#4ade80;border:1px solid rgba(34,197,94,0.25)">Approve</button>
                </form>
              @endif
              @if($review->status !== 'rejected')
                <form method="POST" action="{{ route('admin.reviews.reject', $review->id) }}">
                  @csrf
                  <button type="submit" class="text-xs font-semibold px-2.5 py-1 rounded-lg transition" style="background:rgba(248,113,113,0.12);color:#f87171;border:1px solid rgba(248,113,113,0.25)">Reject</button>
                </form>
              @endif
              <form method="POST" action="{{ route('admin.reviews.destroy', $review->id) }}" onsubmit="return confirm('Delete this review permanently?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-gray-600 hover:text-red-400 transition">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-5 py-12 text-center text-gray-600 text-sm">No reviews yet. <a href="{{ route('admin.reviews.create') }}" class="text-gray-400 underline">Add one →</a></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
</x-app-layout>

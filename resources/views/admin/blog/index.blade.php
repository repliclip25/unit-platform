<x-app-layout title="Blog Posts">
<div class="space-y-6">

  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-white font-semibold">Blog Posts</h2>
      <p class="text-gray-500 text-xs mt-0.5">Published posts appear on the public blog. Drafts are hidden.</p>
    </div>
    <a href="{{ route('admin.blog.create') }}" class="text-xs px-4 py-2 rounded-lg font-semibold" style="background:var(--accent);color:#000">+ New Post</a>
  </div>

  @if(session('saved'))
    <div class="bg-green-950/40 border border-green-800/50 rounded-xl px-4 py-3 text-green-300 text-sm">{{ session('saved') }}</div>
  @endif

  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-gray-800">
          <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Title</th>
          <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Tag</th>
          <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Author</th>
          <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Status</th>
          <th class="text-right px-4 py-3 text-gray-500 text-xs font-medium">Date</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($posts as $post)
        <tr class="border-b border-gray-800/60 last:border-0 hover:bg-gray-800/30 transition">
          <td class="px-5 py-3">
            <div class="text-white text-xs font-medium">{{ $post->title }}</div>
            <div class="text-gray-600 text-xs">/blog/{{ $post->slug }}</div>
          </td>
          <td class="px-4 py-3 text-gray-400 text-xs">{{ $post->tag }}</td>
          <td class="px-4 py-3 text-gray-400 text-xs">{{ $post->author }}</td>
          <td class="px-4 py-3">
            @if($post->status === 'published')
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-900/40 text-green-400 border border-green-800/50">Published</span>
            @else
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-800 text-gray-500 border border-gray-700">Draft</span>
            @endif
          </td>
          <td class="px-4 py-3 text-right text-gray-600 text-xs">{{ \Carbon\Carbon::parse($post->created_at)->format('M j, Y') }}</td>
          <td class="px-4 py-3 text-right">
            <div class="flex items-center gap-3 justify-end">
              @if($post->status === 'published')
                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="text-xs text-gray-600 hover:text-white transition">View ↗</a>
              @else
                <form method="POST" action="{{ route('admin.blog.publish', $post->id) }}">
                  @csrf
                  <button type="submit" class="text-xs font-semibold px-2.5 py-1 rounded-lg transition" style="background:rgba(34,197,94,0.12);color:#4ade80;border:1px solid rgba(34,197,94,0.25)">Publish</button>
                </form>
              @endif
              <a href="{{ route('admin.blog.edit', $post->id) }}" class="text-xs text-gray-400 hover:text-white transition">Edit →</a>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-5 py-12 text-center text-gray-600 text-sm">No posts yet. <a href="{{ route('admin.blog.create') }}" class="text-gray-400 underline">Create one →</a></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>
</x-app-layout>

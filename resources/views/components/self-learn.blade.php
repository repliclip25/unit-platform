@props(['pageKey', 'title' => null, 'body' => null])

@php
    if (!auth()->check()) return;

    $userId = auth()->id();

    // Load DB entry — fall back to props if not seeded yet
    $entry = \Illuminate\Support\Facades\DB::table('platform_self_learn')
        ->where('page_key', $pageKey)
        ->where('active', true)
        ->first();

    if (!$entry && !$title) return; // nothing to show

    $displayTitle   = $entry?->title   ?? $title;
    $displayBody    = $entry?->body    ?? $body;
    $currentVersion = (int) ($entry?->version ?? 1);

    // Check if dismissed at current version
    $dismissedVersion = \Illuminate\Support\Facades\DB::table('user_self_learn_dismissed')
        ->where('user_id', $userId)
        ->where('page_key', $pageKey)
        ->value('version');

    $dismissed = $dismissedVersion !== null && (int) $dismissedVersion >= $currentVersion;

    if ($dismissed) return;

    // Track shown event (deduplicated — once per user per page_key per version per day)
    $alreadyTracked = \Illuminate\Support\Facades\DB::table('user_self_learn_events')
        ->where('user_id', $userId)
        ->where('page_key', $pageKey)
        ->where('event', 'shown')
        ->where('version', $currentVersion)
        ->where('created_at', '>=', now()->subDay())
        ->exists();

    if (!$alreadyTracked) {
        \Illuminate\Support\Facades\DB::table('user_self_learn_events')->insert([
            'user_id'    => $userId,
            'page_key'   => $pageKey,
            'event'      => 'shown',
            'version'    => $currentVersion,
            'created_at' => now(),
        ]);
    }
@endphp

<div id="self-learn-{{ $pageKey }}"
     class="mt-8 rounded-xl px-5 py-4 flex gap-4 items-start"
     style="background:var(--bg-raised);border:1px solid var(--border-subtle)">

    {{-- Icon --}}
    <div class="shrink-0 mt-0.5">
        <svg class="w-4 h-4" style="color:var(--accent-text)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
    </div>

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        <p class="text-xs font-bold uppercase tracking-widest mb-1" style="color:var(--accent-text)">Self Learn · v{{ $currentVersion }}</p>
        <p class="text-sm font-semibold mb-1" style="color:var(--text-primary)">{{ $displayTitle }}</p>
        <p class="text-xs leading-relaxed" style="color:var(--text-muted)">{{ $displayBody }}</p>
    </div>

    {{-- Dismiss --}}
    <button onclick="selfLearnDismiss('{{ $pageKey }}')"
            class="shrink-0 mt-0.5 rounded-md p-1 transition hover:opacity-70"
            title="Dismiss"
            style="color:var(--text-faint)">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

<script>
function selfLearnDismiss(key) {
    var el = document.getElementById('self-learn-' + key);
    if (el) el.style.display = 'none';
    fetch('/self-learn/dismiss', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
        },
        body: JSON.stringify({ page_key: key })
    });
}
</script>

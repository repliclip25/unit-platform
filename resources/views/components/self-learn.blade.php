@props(['pageKey', 'title' => null, 'body' => null])

@php
    $slEntry          = null;
    $slTitle          = $title;
    $slBody           = $body;
    $slVersion        = 1;
    $slShouldShow     = false;

    if (auth()->check()) {
        $userId = auth()->id();

        try {
            $slEntry = \Illuminate\Support\Facades\DB::table('platform_self_learn')
                ->where('page_key', $pageKey)
                ->where('active', true)
                ->first();

            // Also check inactive so we know the row exists (for auto-register guard)
            $slEntryExists = !$slEntry && \Illuminate\Support\Facades\DB::table('platform_self_learn')
                ->where('page_key', $pageKey)
                ->exists();

            // Auto-register on first render if no DB entry exists yet (not even inactive)
            if (!$slEntry && !($slEntryExists ?? false) && $title) {
                \Illuminate\Support\Facades\DB::table('platform_self_learn')->insert([
                    'page_key'   => $pageKey,
                    'title'      => $title,
                    'body'       => $body ?? '',
                    'active'     => true,
                    'version'    => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $slEntry = \Illuminate\Support\Facades\DB::table('platform_self_learn')
                    ->where('page_key', $pageKey)
                    ->first();
            }
        } catch (\Throwable) {}

        if ($slEntry || $title) {
            $slTitle   = $slEntry?->title ?? $title;
            $slBody    = $slEntry?->body  ?? $body;
            $slVersion = (int) ($slEntry?->version ?? 1);

            try {
                $dismissedVersion = \Illuminate\Support\Facades\DB::table('user_self_learn_dismissed')
                    ->where('user_id', $userId)
                    ->where('page_key', $pageKey)
                    ->value('version');

                $dismissed = $dismissedVersion !== null && (int) $dismissedVersion >= $slVersion;
            } catch (\Throwable) {
                $dismissed = false;
            }

            if (!$dismissed) {
                $slShouldShow = true;

                // Track shown (deduplicated — once per user per version per day)
                try {
                    $alreadyTracked = \Illuminate\Support\Facades\DB::table('user_self_learn_events')
                        ->where('user_id', $userId)
                        ->where('page_key', $pageKey)
                        ->where('event', 'shown')
                        ->where('version', $slVersion)
                        ->where('created_at', '>=', now()->subDay())
                        ->exists();

                    if (!$alreadyTracked) {
                        \Illuminate\Support\Facades\DB::table('user_self_learn_events')->insert([
                            'user_id'    => $userId,
                            'page_key'   => $pageKey,
                            'event'      => 'shown',
                            'version'    => $slVersion,
                            'created_at' => now(),
                        ]);
                    }
                } catch (\Throwable) {}
            }
        }
    }
@endphp

@if($slShouldShow)
<div id="self-learn-{{ $pageKey }}"
     class="mt-8 rounded-xl px-5 py-4 flex gap-4 items-start"
     style="background:var(--bg-raised);border:1px solid var(--border-subtle)">

    <div class="shrink-0 mt-0.5">
        <svg class="w-4 h-4" class="ac-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
    </div>

    <div class="flex-1 min-w-0">
        <p class="text-xs font-bold uppercase tracking-widest mb-1" class="ac-text">Self Learn · v{{ $slVersion }}</p>
        <p class="text-sm font-semibold mb-1" style="color:var(--text-primary)">{{ $slTitle }}</p>
        <p class="text-xs leading-relaxed" style="color:var(--text-muted)">{{ $slBody }}</p>
    </div>

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
@endif

<x-app-layout title="Command Center">

@php
    $accentMap = [
        'violet'  => ['ring' => 'ring-brand/40',  'text' => 'text-brand',  'bg' => 'bg-brand/10',  'statVal' => 'text-brand'],
        'blue'    => ['ring' => 'ring-blue-700/40',    'text' => 'text-blue-400',    'bg' => 'bg-blue-900/25',    'statVal' => 'text-blue-300'],
        'emerald' => ['ring' => 'ring-emerald-700/40', 'text' => 'text-emerald-400', 'bg' => 'bg-emerald-900/25', 'statVal' => 'text-emerald-300'],
        'amber'   => ['ring' => 'ring-amber-700/40',   'text' => 'text-amber-400',   'bg' => 'bg-amber-900/25',   'statVal' => 'text-amber-300'],
        'rose'    => ['ring' => 'ring-rose-700/40',    'text' => 'text-rose-400',    'bg' => 'bg-rose-900/25',    'statVal' => 'text-rose-300'],
    ];

    $statusColors = [
        'draft_ready'   => 'bg-brand/20 text-brand',
        'human_review'  => 'bg-amber-900/60 text-amber-300',
        'failed'        => 'bg-red-900/60 text-red-300',
        'approved'      => 'bg-green-900/60 text-green-300',
        'sent'          => 'bg-green-900/60 text-green-300',
        'received'      => 'bg-gray-800 text-gray-400',
        'ingesting'     => 'bg-blue-900/60 text-blue-300',
        'reading'       => 'bg-blue-900/60 text-blue-300',
        'classifying'   => 'bg-blue-900/60 text-blue-300',
        'memory_lookup' => 'bg-blue-900/60 text-blue-300',
        'drafting'      => 'bg-blue-900/60 text-blue-300',
        'pushing'       => 'bg-blue-900/60 text-blue-300',
    ];
@endphp

{{-- ── Referral chip / compact banner ── --}}
@if($referralEligible)
{{-- Engaged user: show a slim one-line banner --}}
<div class="mb-5 flex items-center gap-3 rounded-xl px-4 py-3"
     style="background:rgba(var(--accent-rgb),0.07);border:1px solid rgba(var(--accent-rgb),0.2)">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="var(--accent-text)" viewBox="0 0 24 24" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    <p class="flex-1 text-sm" style="color:var(--text-secondary)">
        Earn <span class="font-semibold" style="color:var(--accent-text)">$25 credit</span> for every colleague you bring to UNIT.
    </p>
    <a href="{{ route('referral.index') }}"
       class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-lg transition hover:opacity-90"
       style="background:var(--accent);color:#000000">
        Refer & Earn
    </a>
</div>
@else
{{-- Not yet engaged: just a subtle chip in the top-right corner of the page header area --}}
<div class="mb-5 flex justify-end">
    <a href="{{ route('referral.index') }}"
       class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full border transition hover:opacity-80"
       style="border-color:rgba(var(--accent-rgb),0.25);color:rgba(var(--accent-rgb),0.55)">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Refer & Earn
    </a>
</div>
@endif

{{-- ── Body: overview + worker cards (left) + value clock + notifications (right) ── --}}

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: overview list + worker cards --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Your Desk header --}}
        <div class="flex items-center justify-between mb-1">
            <p class="text-xs font-medium" style="color:var(--text-muted)">{{ now()->format('l, F j · g:i A') }}</p>
            <button onclick="toggleDeskDrawer()"
                    class="text-xs font-medium transition hover:opacity-80"
                    style="color:var(--text-faint)">
                Customize Desk ↗
            </button>
        </div>

        {{-- Your Desk feed --}}
        @if($deskCards->isEmpty())
        <div class="rounded-xl px-5 py-4 text-sm" style="color:var(--text-faint);border:1px solid var(--border-subtle)">
            Everything's quiet — nothing needs your attention right now.
        </div>
        @else
        <style>#desk-feed strong { color:var(--text-primary); font-size:1.05em; }</style>
        <div id="desk-feed" class="divide-y" style="border-top:1px solid var(--border-subtle);border-bottom:1px solid var(--border-subtle)">
            @foreach($deskCards as $card)
            @php
                $dotColors = [
                    'accent' => 'var(--accent)',
                    'green'  => '#4ade80',
                    'amber'  => '#fbbf24',
                    'red'    => '#f87171',
                    'grey'   => 'var(--text-faint)',
                ];
                $dot = $dotColors[$card['dot'] ?? 'grey'] ?? 'var(--text-faint)';
            @endphp
            <div class="flex items-center justify-between py-3 gap-3 desk-card-row"
                 data-key="{{ $card['key'] }}"
                 @if($card['dismissible'] ?? false) data-dismissible="1" @endif>
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background:{{ $dot }}"></span>
                    <span class="leading-snug" style="color:var(--text-secondary);font-size:0.9rem">{!! $card['text'] !!}</span>
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-2">
                    @if($card['action'] ?? null)
                    <a href="{{ $card['action']['url'] }}"
                       @if($card['action']['external'] ?? false) target="_blank" rel="noopener" @endif
                       class="text-xs font-semibold whitespace-nowrap transition hover:opacity-80"
                       style="color:var(--accent-text)">
                        {{ $card['action']['label'] }} →
                    </a>
                    @endif
                    @if($card['dismissible'] ?? false)
                    <button onclick="deskDismiss('{{ $card['dismiss_key'] ?? $card['key'] }}', this)"
                            class="text-xs transition hover:opacity-60"
                            style="color:var(--text-faint)" title="Dismiss">✕</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Customize Desk drawer --}}
        <div id="desk-drawer" style="display:none" class="mt-2 rounded-xl overflow-hidden"
             style="border:1px solid var(--border);background:var(--bg-card)">
            <div class="px-5 py-4 flex items-center justify-between"
                 style="border-bottom:1px solid var(--border-subtle)">
                <p class="text-sm font-semibold" style="color:var(--text-primary)">Your Desk</p>
                <button onclick="toggleDeskDrawer()" class="text-xs transition hover:opacity-60"
                        style="color:var(--text-faint)">✕ Close</button>
            </div>
            @php
                $tierLabels = ['pipeline' => 'Pipeline', 'memory' => 'Memory', 'growth' => 'Growth', 'platform' => 'Platform'];
                $grouped = collect($deskAllCards)->groupBy('tier');
            @endphp
            <style>
            .desk-tog { position:relative;width:34px;height:20px;cursor:pointer;flex-shrink:0 }
            .desk-tog input { opacity:0;width:0;height:0;position:absolute }
            .desk-tog-track { position:absolute;inset:0;border-radius:10px;transition:.18s;background:var(--bg-raised);border:1px solid var(--border) }
            .desk-tog input:checked ~ .desk-tog-track { background:var(--accent);border-color:transparent }
            .desk-tog-thumb { position:absolute;top:3px;left:3px;width:12px;height:12px;border-radius:50%;transition:.18s;pointer-events:none;background:var(--text-muted) }
            .desk-tog input:checked ~ .desk-tog-track .desk-tog-thumb { transform:translateX(14px);background:#000 }
            </style>
            @foreach(['pipeline','memory','growth','platform'] as $tier)
            @if($grouped->has($tier))
            <div class="px-5 py-3" style="border-bottom:1px solid var(--border-subtle)">
                <p class="text-xs font-bold uppercase tracking-widest mb-3" style="color:var(--text-muted)">
                    {{ $tierLabels[$tier] }}
                </p>
                <div class="space-y-3">
                    @foreach($grouped[$tier] as $item)
                    <div class="flex items-center gap-3">
                        <label class="desk-tog" title="{{ $item['visible'] ? 'Showing' : 'Hidden' }}">
                            <input type="checkbox"
                                   class="desk-toggle"
                                   data-key="{{ $item['key'] }}"
                                   @if($item['visible']) checked @endif
                                   onchange="deskSaveToggle(this)">
                            <div class="desk-tog-track"><div class="desk-tog-thumb"></div></div>
                        </label>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold leading-tight" style="color:var(--text-primary)">{{ $item['label'] }}</p>
                            <p class="text-xs mt-0.5" style="color:var(--text-faint)">{{ $item['description'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
            <div class="px-5 py-3">
                <p class="text-xs" style="color:var(--text-faint)">
                    Cards only show when there's something to display. Toggle off to permanently hide a card type from your desk.
                </p>
            </div>
        </div>

        <script>
        function toggleDeskDrawer() {
            var d = document.getElementById('desk-drawer');
            d.style.display = d.style.display === 'none' ? 'block' : 'none';
        }

        function deskSaveToggle(checkbox) {
            var key     = checkbox.dataset.key;
            var visible = checkbox.checked;

            // Optimistically hide/show in feed
            var row = document.querySelector('.desk-card-row[data-key="' + key + '"]');
            if (row) row.style.display = visible ? '' : 'none';

            fetch('{{ route('dashboard.desk.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ cards: [{ key: key, visible: visible, position: 50 }] }),
            });
        }

        function deskDismiss(key, btn) {
            // Remove from DOM immediately
            var row = btn.closest('.desk-card-row');
            if (row) row.remove();

            fetch('{{ route('dashboard.desk.dismiss') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ key: key }),
            });
        }
        </script>

        @forelse($workerCards as $card)
        @php
            $dep         = $card['dep'];
            $dash        = $card['dash'];
            $registryRow = $card['registryRow'];
            $inboxes     = $card['inboxes'];
            $watchOk     = $inboxes->every(fn($i) => $i->watch_active);

            $employee    = $card['employee'];
            $workerName  = $employee['name']  ?? strtoupper($dep->worker_slug);
            $workerRole  = $employee['title'] ?? '';
            $statement   = $employee['statement']   ?? $employee['mission'] ?? '';
            $connectsTo  = $employee['connects_to'] ?? [];

            $profileImg = $registryRow?->profile_image ? asset('storage/' . $registryRow->profile_image) : null;
            $mediaJson  = json_decode($registryRow?->media ?? '{}', true);
            $accentHex  = $mediaJson['color'] ?? '#f1d362';

            $isActive = $dep->status === 'active';
        @endphp

        <div class="rounded-2xl" style="background:var(--bg-card);border:1px solid var(--border)">

            {{-- Header --}}
            <div class="px-5 pt-5 pb-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    @if($profileImg)
                    <img src="{{ $profileImg }}" alt="{{ $workerName }}"
                         style="width:44px;height:44px;border-radius:12px;object-fit:cover;flex-shrink:0;border:2px solid {{ $accentHex }}44">
                    @else
                    <div style="width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;background:{{ $accentHex }}18;border:1px solid {{ $accentHex }}40;color:{{ $accentHex }}">
                        {{ strtoupper(substr($workerName, 0, 1)) }}
                    </div>
                    @endif
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-sm" style="color:var(--text-primary)">{{ $workerName }}</span>
                            <span class="text-xs" style="color:var(--text-muted)">{{ $workerRole }}</span>
                        </div>
                        <p class="text-xs font-medium mt-1 {{ $isActive ? 'text-green-400' : 'text-amber-400' }}">
                            ● {{ $isActive ? 'On duty' : ucfirst($dep->status) }}
                        </p>
                    </div>
                </div>
                <a href="{{ route('workers.show', $dep->worker_slug) }}"
                   class="text-xs px-3 py-1.5 rounded-lg font-medium shrink-0 transition hover:opacity-80"
                   style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">
                    Open →
                </a>
            </div>

            {{-- Statement --}}
            @if($statement)
            <div class="px-5 pb-4">
                <p class="text-sm leading-relaxed italic" style="color:var(--text-secondary)">
                    "{{ $statement }}"
                </p>
            </div>
            @endif

            {{-- You control --}}
            @php
                $controls = array_merge(
                    array_map('strtolower', $connectsTo),
                    ['rules', 'prompts']
                );

            @endphp
            <div class="px-5 py-3" style="border-top:1px solid var(--border-subtle)">
                <p class="text-xs">
                    <span style="color:var(--text-faint)">You control:</span>
                    <span style="color:var(--text-secondary)">{{ implode(' · ', array_map('strtoupper', $controls)) }}</span>
                </p>
            </div>

            {{-- Footer: Fast Track + Last shift --}}
            <div class="px-5 py-3 flex items-center justify-between gap-3" style="border-top:1px solid var(--border-subtle)">
                <a href="{{ route('workers.show', $dep->worker_slug) }}#fast-track"
                   class="text-xs font-semibold transition hover:opacity-80"
                   style="color:var(--accent-text)">
                    Fast Track →
                </a>
                <span class="text-xs" style="color:var(--text-faint)">
                    @if($card['lastTx'])
                        Last shift: {{ \Carbon\Carbon::parse($card['lastTx']->created_at)->diffForHumans(null, true, true, 1) }} ago
                    @else
                        Never run
                    @endif
                </span>
            </div>

        </div>
        @empty
        <div class="rounded-2xl px-6 py-10 text-center" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-sm" style="color:var(--text-muted)">No workers deployed yet.</p>
            <p class="text-xs mt-1 mb-4" style="color:var(--text-faint)">Deploy a worker to get started.</p>
            <a href="{{ route('workers.deploy') }}"
               class="text-xs px-4 py-2 rounded-lg font-semibold transition hover:opacity-90"
               style="background:var(--accent);color:#000">
                Deploy a Worker →
            </a>
        </div>
        @endforelse

    </div>

    {{-- Right: value clock + notifications --}}
    <div class="space-y-4">

        {{-- Value Clock --}}
        <div class="rounded-2xl px-5 py-6 text-center relative overflow-hidden"
             style="background:var(--bg-card);border:1px solid var(--border)">
            <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 50% 120%, rgba(var(--accent-rgb),0.07) 0%, transparent 70%);pointer-events:none"></div>
            <div class="flex items-center justify-center gap-1.5 mb-3">
                <p class="text-xs font-bold uppercase tracking-widest" style="color:var(--text-muted)">This week's value</p>
                {{-- Tooltip --}}
                <div class="relative" style="display:inline-flex">
                    <svg id="clock-info-icon" class="w-3.5 h-3.5 cursor-pointer" style="color:var(--text-faint)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                {{-- Fixed tooltip rendered at body level via JS --}}
                <div id="clock-tooltip" style="display:none;position:fixed;z-index:9999;width:220px;padding:12px;border-radius:12px;text-align:left;pointer-events:none"
                     class="shadow-2xl"
                     style="background:var(--bg-raised);border:1px solid var(--border)">
                    <div style="background:var(--bg-raised);border:1px solid var(--border);border-radius:12px;padding:12px">
                        <p class="text-xs font-semibold mb-1" style="color:var(--text-primary)">How this is calculated</p>
                        <p class="text-xs leading-relaxed" style="color:var(--text-muted)">Each email processed by your workers saves an estimated <strong style="color:var(--text-secondary)">15 minutes</strong> of manual work.<br>Total hours = emails × 0.25h, aggregated across all your workers.</p>
                    </div>
                </div>
                <script>
                (function(){
                    const icon = document.getElementById('clock-info-icon');
                    const tip  = document.getElementById('clock-tooltip');
                    if (!icon || !tip) return;
                    icon.addEventListener('mouseenter', function(e){
                        const r = icon.getBoundingClientRect();
                        tip.style.display = 'block';
                        tip.style.left = Math.max(8, r.right - 220) + 'px';
                        tip.style.top  = (r.top - tip.offsetHeight - 8) + 'px';
                    });
                    icon.addEventListener('mouseleave', function(){ tip.style.display = 'none'; });
                })();
                </script>
            </div>
            <p class="font-black leading-none mb-1"
               style="font-size:clamp(48px,8vw,80px);color:var(--accent-text);letter-spacing:-0.03em">
                {{ $clockValue > 0 ? number_format($clockValue, 1) : '—' }}
            </p>
            <p class="text-sm" style="color:var(--text-secondary)">hours returned to your team</p>
            <p class="text-xs mt-1" style="color:var(--text-faint)">{{ number_format($ovProcessed) }} emails · {{ $workerCards->count() }} {{ $workerCards->count() === 1 ? 'worker' : 'workers' }}</p>
        </div>

        {{-- Notifications --}}
        <div>
        <h2 class="text-gray-500 text-xs uppercase tracking-wide px-1 mb-3">Notifications</h2>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
            @if($notifications->isEmpty())
            <div class="px-5 py-10 text-center">
                <p class="text-green-400 text-sm">✓ All clear</p>
                <p class="text-gray-600 text-xs mt-1">No issues across your workers.</p>
            </div>
            @else
            <div class="divide-y divide-gray-800">
                @foreach($notifications as $note)
                @php
                    $levelStyles = [
                        'error'   => ['dot' => 'bg-red-500',    'text' => 'text-red-300',    'action' => 'text-red-400 hover:text-red-300'],
                        'warning' => ['dot' => 'bg-amber-500',  'text' => 'text-amber-300',  'action' => 'text-amber-400 hover:text-amber-300'],
                        'info'    => ['dot' => 'bg-brand', 'text' => 'text-gray-300',   'action' => 'text-brand hover:text-brand'],
                    ];
                    $ls = $levelStyles[$note['level']] ?? $levelStyles['info'];
                @endphp
                <div class="px-5 py-3.5 flex items-start gap-3">
                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0 {{ $ls['dot'] }}"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs {{ $ls['text'] }} leading-snug">{{ $note['message'] }}</p>
                        @if($note['source'] !== 'platform')
                            <p class="text-gray-700 text-xs mt-0.5">{{ $note['source'] }}</p>
                        @endif
                    </div>
                    <a href="{{ $note['actionUrl'] }}"
                       class="text-xs shrink-0 font-medium {{ $ls['action'] }} transition">
                        {{ $note['actionLabel'] }} →
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        </div>{{-- end notifications --}}

    </div>{{-- end right column --}}

</div>{{-- end grid --}}

</x-app-layout>

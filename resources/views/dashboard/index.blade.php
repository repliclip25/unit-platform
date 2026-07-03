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
@php
    $primaryInbox = null;
    foreach($workerCards as $wc) {
        $pi = $wc['inboxes']->firstWhere('is_primary', true) ?? $wc['inboxes']->first();
        if ($pi) { $primaryInbox = $pi; break; }
    }
    $gmailUrl    = $primaryInbox
        ? 'https://mail.google.com/mail/u/' . urlencode($primaryInbox->gmail_address) . '/#drafts'
        : 'https://mail.google.com/mail/#drafts';
    $problemCount = $ovFailed + $ovStuck;
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: overview list + worker cards --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Date/time + overview rows --}}
        <div>
            <p class="text-xs font-medium mb-3" style="color:var(--text-muted)">{{ now()->format('l, F j · g:i A') }}</p>
            <div class="divide-y" style="border-top:1px solid var(--border-subtle);border-bottom:1px solid var(--border-subtle)">

                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background:var(--text-faint)"></span>
                        <span class="text-sm" style="color:var(--text-secondary)">
                            @if($ovProcessed > 0)
                                <strong style="color:var(--text-primary)">{{ number_format($ovProcessed) }}</strong> emails processed this week across all workers
                            @else
                                No emails processed this week
                            @endif
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0"
                              style="background:{{ $ovDrafts > 0 ? 'var(--accent)' : 'var(--text-faint)' }}"></span>
                        <span class="text-sm" style="color:var(--text-secondary)">
                            @if($ovDrafts > 0)
                                <strong style="color:var(--text-primary)">{{ $ovDrafts }}</strong> {{ $ovDrafts === 1 ? 'draft' : 'drafts' }} ready for your review
                            @else
                                No drafts waiting for review
                            @endif
                        </span>
                    </div>
                    @if($ovDrafts > 0)
                    <a href="{{ $gmailUrl }}" target="_blank" rel="noopener"
                       class="text-xs font-semibold flex items-center gap-1 shrink-0 ml-4 transition hover:opacity-80"
                       style="color:var(--accent-text)">
                        Open Gmail
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    @endif
                </div>

                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0"
                              style="background:{{ $ovUrgent > 0 ? '#fbbf24' : 'var(--text-faint)' }}"></span>
                        <span class="text-sm" style="color:var(--text-secondary)">
                            @if($ovUrgent > 0)
                                <strong style="color:#fbbf24">{{ $ovUrgent }}</strong> {{ $ovUrgent === 1 ? 'item' : 'items' }} marked urgent — needs your attention
                            @else
                                No urgent items
                            @endif
                        </span>
                    </div>
                    @if($ovUrgent > 0)
                    <a href="{{ route('transactions', ['filter' => 'draft_ready', 'priority' => 'high']) }}"
                       class="text-xs font-semibold shrink-0 ml-4 transition hover:opacity-80" style="color:#fbbf24">Review →</a>
                    @endif
                </div>

                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0"
                              style="background:{{ $problemCount > 0 ? '#f87171' : 'var(--text-faint)' }}"></span>
                        <span class="text-sm" style="color:var(--text-secondary)">
                            @if($problemCount > 0)
                                <strong style="color:#f87171">{{ $problemCount }}</strong> {{ $problemCount === 1 ? 'item' : 'items' }} failed or stuck in pipeline
                            @else
                                Pipeline running clean — no failures this week
                            @endif
                        </span>
                    </div>
                    @if($problemCount > 0)
                    <a href="{{ route('transactions', ['filter' => 'failed']) }}"
                       class="text-xs font-semibold shrink-0 ml-4 transition hover:opacity-80" style="color:#f87171">View →</a>
                    @endif
                </div>

            </div>
        </div>

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

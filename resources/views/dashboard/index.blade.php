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

{{-- ── Referral Card ── --}}
<div class="mb-6 rounded-2xl overflow-hidden relative"
     style="background:linear-gradient(135deg,#1a1404 0%,#2a1f05 40%,#111 100%);border:1px solid rgba(243,197,49,0.25)"
     x-data="{ copied: false }">
    {{-- Gold glow --}}
    <div class="absolute top-0 right-0 w-64 h-64 rounded-full pointer-events-none"
         style="background:radial-gradient(circle,rgba(243,197,49,0.12) 0%,transparent 70%);transform:translate(30%,-30%)"></div>

    <div class="relative px-6 py-5 flex flex-col md:flex-row md:items-center gap-5">

        {{-- Left: headline --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="#f3c531" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="text-xs font-semibold uppercase tracking-widest" style="color:#f3c531">Refer & Earn</span>
            </div>
            <p class="text-white font-semibold text-base leading-snug">
                Get <span style="color:#f3c531">$25 credit</span> for every colleague you bring to UNIT
            </p>
            <p class="text-sm mt-1" style="color:rgba(243,197,49,0.6)">
                They get 20 free trial transactions (double the usual). You earn $25 when they go paid.
            </p>

            {{-- Referral link --}}
            <div class="flex items-center gap-2 mt-3">
                <div class="flex-1 flex items-center gap-2 rounded-lg px-3 py-2 min-w-0"
                     style="background:rgba(0,0,0,0.3);border:1px solid rgba(243,197,49,0.2)">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="#f3c531" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <span class="text-xs font-mono truncate" style="color:rgba(243,197,49,0.8)">{{ $referralUrl }}</span>
                </div>
                <button @click="navigator.clipboard.writeText('{{ $referralUrl }}'); copied=true; setTimeout(()=>copied=false,2000)"
                        class="shrink-0 text-xs px-4 py-2 rounded-lg font-bold transition"
                        style="background:#f3c531;color:#1a1404">
                    <span x-show="!copied">Copy Link</span>
                    <span x-show="copied">Copied ✓</span>
                </button>
            </div>

            {{-- Share shortcuts --}}
            <div class="flex items-center gap-3 mt-2.5">
                <span class="text-xs" style="color:rgba(243,197,49,0.4)">Share via:</span>
                <a href="mailto:?subject=Try UNIT Platform&body=Hey! I've been using UNIT to automate my license renewal workflow — it's really good. Use my link and you'll get double the free trial: {{ $referralUrl }}"
                   class="text-xs hover:underline" style="color:rgba(243,197,49,0.65)">Email</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($referralUrl) }}"
                   target="_blank" class="text-xs hover:underline" style="color:rgba(243,197,49,0.65)">LinkedIn</a>
                <a href="https://twitter.com/intent/tweet?text={{ urlencode('I use UNIT Platform to automate license renewals. You should try it — use my link for double the free trial: ' . $referralUrl) }}"
                   target="_blank" class="text-xs hover:underline" style="color:rgba(243,197,49,0.65)">X / Twitter</a>
            </div>
        </div>

        {{-- Right: stats --}}
        <div class="shrink-0 flex md:flex-col gap-4 md:gap-3 md:text-right">

            {{-- Referral code badge --}}
            <div class="md:mb-1">
                <p class="text-xs" style="color:rgba(243,197,49,0.5)">Your code</p>
                <p class="font-mono font-bold text-lg" style="color:#f3c531">{{ $referralCode }}</p>
            </div>

            {{-- Stats row --}}
            <div class="flex md:flex-row gap-4">
                <div class="text-center md:text-right">
                    <p class="text-white font-bold text-xl leading-none">{{ $referral->signups }}</p>
                    <p class="text-xs mt-0.5" style="color:rgba(243,197,49,0.5)">Signed up</p>
                </div>
                <div class="text-center md:text-right">
                    <p class="font-bold text-xl leading-none" style="color:#f3c531">{{ $referral->converted }}</p>
                    <p class="text-xs mt-0.5" style="color:rgba(243,197,49,0.5)">Converted</p>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-white font-bold text-xl leading-none">${{ number_format($referral->balance, 0) }}</p>
                    <p class="text-xs mt-0.5" style="color:rgba(243,197,49,0.5)">Credit</p>
                </div>
            </div>

            {{-- Tier progress --}}
            @if($referral->nextTier)
            <div class="md:text-right">
                <div class="flex items-center justify-end gap-2 mb-1">
                    <span class="text-xs" style="color:rgba(243,197,49,0.5)">→ {{ $referral->tierLabel }}</span>
                    <span class="text-xs" style="color:rgba(243,197,49,0.7)">{{ $referral->converted }}/{{ $referral->nextTier }}</span>
                </div>
                <div class="w-full md:w-28 h-1 rounded-full" style="background:rgba(243,197,49,0.15)">
                    <div class="h-full rounded-full" style="width:{{ $referral->tierPct }}%;background:#f3c531"></div>
                </div>
            </div>
            @else
            <div class="md:text-right">
                <p class="text-xs font-semibold" style="color:#f3c531">🏆 Gold Referrer</p>
                <p class="text-xs" style="color:rgba(243,197,49,0.5)">10+ conversions</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Pipeline report ── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-8">

    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
        <p class="text-gray-600 text-xs uppercase tracking-wide">Total Processed</p>
        <p class="text-white text-3xl font-bold mt-1">{{ number_format($pipeline['total']) }}</p>
        <p class="text-gray-700 text-xs mt-1">all time · all workers</p>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
        <p class="text-gray-600 text-xs uppercase tracking-wide">In Pipeline</p>
        <p class="{{ $pipeline['in_pipeline'] > 0 ? 'text-blue-400' : 'text-gray-500' }} text-3xl font-bold mt-1">
            {{ number_format($pipeline['in_pipeline']) }}
        </p>
        <p class="text-gray-700 text-xs mt-1">currently running</p>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
        <p class="text-gray-600 text-xs uppercase tracking-wide">Needs Review</p>
        <p class="{{ $pipeline['needs_review'] > 0 ? 'text-amber-400' : 'text-gray-500' }} text-3xl font-bold mt-1">
            {{ number_format($pipeline['needs_review']) }}
        </p>
        <p class="text-gray-700 text-xs mt-1">awaiting human decision</p>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
        <p class="text-gray-600 text-xs uppercase tracking-wide">Failed Jobs</p>
        <p class="{{ $pipeline['failed'] > 0 ? 'text-red-400' : 'text-gray-500' }} text-3xl font-bold mt-1">
            {{ number_format($pipeline['failed']) }}
        </p>
        <p class="text-gray-700 text-xs mt-1">in queue · needs attention</p>
    </div>

</div>

{{-- ── Body: worker cards + notifications ── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Worker cards --}}
    <div class="xl:col-span-2 space-y-4">

        <h2 class="text-gray-500 text-xs uppercase tracking-wide px-1">Your Workers</h2>

        @forelse($workerCards as $card)
        @php
            $dep     = $card['dep'];
            $dash    = $card['dash'];
            $accent  = $accentMap[$dash['accent']] ?? $accentMap['violet'];
            $billing = $card['billing'];
            $lastTx  = $card['lastTx'];
            $inboxes = $card['inboxes'];

            $isTrial     = $billing?->status === 'trial';
            $trialLeft   = max(0, ($billing?->trial_transactions_limit ?? 10) - ($billing?->trial_transactions_used ?? 0));
            $watchOk     = $inboxes->every(fn($i) => $i->watch_active);
            $inboxCount  = $inboxes->count();
        @endphp

        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">

            {{-- Header --}}
            <div class="px-5 pt-4 pb-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    {{-- Icon --}}
                    <div class="w-9 h-9 {{ $accent['bg'] }} ring-1 {{ $accent['ring'] }} rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 {{ $accent['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $dash['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-white text-sm font-semibold">{{ $dep->name }}</span>
                            <span class="text-xs {{ $dep->status === 'active' ? 'text-green-400' : 'text-yellow-400' }}">
                                ● {{ ucfirst($dep->status) }}
                            </span>
                        </div>
                        <p class="text-gray-600 text-xs mt-0.5">{{ $card['contract']->identity()['slug'] }} · v{{ $card['contract']->identity()['version'] }}</p>
                    </div>
                </div>
                <a href="{{ route('workers.show', $dep->id) }}"
                   class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:text-white hover:border-gray-500 transition font-medium shrink-0">
                    Open →
                </a>
            </div>

            {{-- Stats row: worker-specific + connection ──
                 Divider row: each cell separated by a 1px gray-800 gap --}}
            <div class="grid border-t border-gray-800"
                 style="grid-template-columns: repeat({{ count($card['stats']) + 2 }}, 1fr)">

                {{-- Connection health cell --}}
                <div class="px-4 py-3 border-r border-gray-800">
                    <p class="text-gray-600 text-xs">Connection</p>
                    <div class="flex items-center gap-1 mt-1">
                        <span class="text-xs {{ $watchOk ? 'text-green-400' : 'text-yellow-400' }}">
                            {{ $watchOk ? '●' : '⚠' }}
                        </span>
                        <span class="text-white text-xs font-semibold">{{ $inboxCount }}</span>
                        <span class="text-gray-600 text-xs">inbox{{ $inboxCount !== 1 ? 'es' : '' }}</span>
                    </div>
                </div>

                {{-- Worker-specific stats --}}
                @foreach($card['stats'] as $i => $stat)
                <div class="px-4 py-3 {{ !$loop->last ? 'border-r border-gray-800' : '' }}">
                    <p class="text-gray-600 text-xs">{{ $stat['label'] }}</p>
                    <p class="text-sm font-semibold mt-1
                        {{ $stat['value'] > 0 && $stat['key'] === 'tx_draft_ready' ? $accent['statVal'] : '' }}
                        {{ $stat['value'] > 0 && $stat['key'] === 'tx_urgent'      ? 'text-amber-300' : '' }}
                        {{ !in_array($stat['key'], ['tx_draft_ready','tx_urgent'])  ? 'text-white' : '' }}">
                        {{ number_format($stat['value']) }}
                    </p>
                </div>
                @endforeach

                {{-- Billing / trial cell --}}
                <div class="px-4 py-3 border-l border-gray-800">
                    <p class="text-gray-600 text-xs">Plan</p>
                    @if($isTrial)
                        <p class="text-xs font-semibold mt-1 {{ $trialLeft <= 2 ? 'text-red-400' : 'text-gray-400' }}">
                            {{ $trialLeft }} left
                        </p>
                    @else
                        <p class="text-xs font-semibold mt-1 text-green-400">Active</p>
                    @endif
                </div>
            </div>

            {{-- Last run --}}
            <div class="border-t border-gray-800 px-5 py-3 flex items-center justify-between gap-3">
                <div>
                    <span class="text-gray-600 text-xs">Last run</span>
                    @if($lastTx)
                        <span class="text-gray-400 text-xs ml-2">{{ $lastTx->category ?? 'Processing…' }}</span>
                        <span class="text-gray-700 text-xs ml-1">·</span>
                        <span class="text-gray-600 text-xs ml-1">{{ \Carbon\Carbon::parse($lastTx->created_at)->diffForHumans() }}</span>
                    @else
                        <span class="text-gray-700 text-xs ml-2">No runs yet</span>
                    @endif
                </div>
                @if($lastTx)
                    <span class="text-xs px-2 py-0.5 rounded-full shrink-0 {{ $statusColors[$lastTx->status] ?? 'bg-gray-800 text-gray-400' }}">
                        {{ $lastTx->status }}
                    </span>
                @endif
            </div>

        </div>
        @empty
        <div class="bg-gray-900 border border-gray-800 rounded-2xl px-6 py-10 text-center">
            <p class="text-gray-500 text-sm">No workers deployed yet.</p>
            <p class="text-gray-600 text-xs mt-1 mb-4">Deploy a worker from the marketplace to get started.</p>
            <a href="{{ route('marketplace') }}"
               class="text-xs px-4 py-2 rounded-lg bg-brand hover:bg-brand-deep text-brand-text font-semibold transition">
                Browse Marketplace →
            </a>
        </div>
        @endforelse

    </div>

    {{-- Notifications --}}
    <div>
        <h2 class="text-gray-500 text-xs uppercase tracking-wide px-1 mb-4">Notifications</h2>

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
    </div>

</div>

</x-app-layout>

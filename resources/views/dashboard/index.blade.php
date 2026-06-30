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
        <p class="text-gray-600 text-xs uppercase tracking-wide">Failed</p>
        <p class="{{ $pipeline['failed'] > 0 ? 'text-red-400' : 'text-gray-500' }} text-3xl font-bold mt-1">
            {{ number_format($pipeline['failed']) }}
        </p>
        <p class="text-gray-700 text-xs mt-1">transactions · need attention</p>
    </div>

</div>

{{-- ── Body: worker cards + notifications ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Worker cards --}}
    <div class="lg:col-span-2 space-y-4">

        <h2 class="text-gray-500 text-xs uppercase tracking-wide px-1">Your Team</h2>

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
                <a href="{{ route('workers.show', $dep->worker_slug) }}"
                   class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:text-white hover:border-gray-500 transition font-medium shrink-0">
                    Open →
                </a>
            </div>

            {{-- Stats row: worker-specific + connection — flex-wrap for mobile --}}
            <div class="flex flex-wrap border-t border-gray-800">

                {{-- Connection health cell --}}
                <div class="w-1/2 sm:flex-1 px-4 py-3 border-r border-gray-800 border-b sm:border-b-0">
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
                <div class="w-1/2 sm:flex-1 px-4 py-3 border-r border-gray-800 border-b sm:border-b-0">
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
                <div class="w-1/2 sm:flex-1 px-4 py-3">
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
            <div class="border-t border-gray-800 px-5 py-3 flex items-center justify-between gap-2 flex-wrap">
                <div class="flex items-center gap-1 flex-wrap">
                    <span class="text-gray-600 text-xs">Last run</span>
                    @if($lastTx)
                        <span class="text-gray-400 text-xs">· {{ $lastTx->category ?? 'Processing…' }}</span>
                        <span class="text-gray-600 text-xs">{{ \Carbon\Carbon::parse($lastTx->created_at)->diffForHumans() }}</span>
                    @else
                        <span class="text-gray-700 text-xs">· No runs yet</span>
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
            <p class="text-gray-500 text-sm">No employees hired yet.</p>
            <p class="text-gray-600 text-xs mt-1 mb-4">Hire an employee from the roster to get started.</p>
            <a href="{{ route('workers.deploy') }}"
               class="text-xs px-4 py-2 rounded-lg bg-brand hover:bg-brand-deep text-brand-text font-semibold transition">
                Hire an Employee →
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

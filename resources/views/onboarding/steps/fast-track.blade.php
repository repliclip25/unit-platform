<x-onboarding-layout :stepName="$stepName" :sequence="$sequence" :stepIndex="$stepIndex" :wide="true">

@php
    $ft            = $contract?->fastTrack() ?? [];
    $sampleSubject = $ft['subject'] ?? 'Domain Renewal Notice — yourdomain.com expires in 30 days';
    $sampleRaw     = $ft['raw_email'] ?? "Subject: Domain Renewal Notice — example.com\n\nHi,\n\nThis is a reminder that your domain example.com is due for renewal in 30 days. Please renew at your earliest convenience to avoid any service interruption.\n\nBest regards,\nDomain Services Team";
    $outcome       = $outcome ?? [];
    $workerName    = $contract?->identity()['name'] ?? 'Your worker';
@endphp

@php
    // flash ('fast_track_running') is one-load only; fall back to the persistent key
    $activeTxId = session('fast_track_running') ?? session('onboarding_fast_track_tx') ?? ($txId ?? null);
@endphp

@if($activeTxId)
{{-- ── PIPELINE RUNNING ── --}}
<div x-data="{ success: false }" @ft-pipeline-done.window="success = !$event.detail.failed">

    {{-- Dynamic header — updates with pipeline state --}}
    <div class="mb-8">
        <h1 class="text-2xl font-black text-white mb-2 transition-all duration-300">
            <span x-show="!success">{{ $workerName }} is running your email through the pipeline.</span>
            <span x-show="success" x-cloak>Your first deal, handled.</span>
        </h1>
        <p class="text-sm transition-all duration-300">
            <span x-show="!success" class="text-gray-400">Watch each stage complete in real time — this is exactly what happens to every renewal that hits your inbox.</span>
            <span x-show="success" x-cloak class="text-green-400/80">AVA read it, understood it, matched it to your contacts, and prepared a response — without you touching a thing.</span>
        </p>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl px-6 pt-5 pb-4 mb-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-2 h-2 rounded-full bg-brand animate-pulse" x-show="!success"></div>
            <div class="w-2 h-2 rounded-full bg-green-500" x-show="success" x-cloak></div>
            <p class="text-brand text-xs font-semibold uppercase tracking-widest" x-show="!success">Fast Track Pipeline</p>
            <p class="text-green-400 text-xs font-semibold uppercase tracking-widest" x-show="success" x-cloak>Pipeline Complete</p>
            <span class="text-gray-700 text-xs font-mono ml-auto">{{ $activeTxId }}</span>
        </div>

        <x-pipeline-tracker
            :txId="$activeTxId"
            :autoStart="true"
            :onCompleteUrl="route('onboarding.complete')"
            context="onboarding"
        />
    </div>

    {{-- ── SUCCESS OUTCOME CARD ── --}}
    @if(!empty($outcome))
    <div x-show="success" x-cloak
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mb-5">

        {{-- Headline --}}
        <div class="rounded-2xl border border-green-500/20 bg-green-500/5 px-6 py-5 mb-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-green-500/15 border border-green-500/20 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-black text-lg leading-snug mb-1">{{ $outcome['headline'] ?? 'Your worker just completed its first run.' }}</p>
                    <p class="text-green-400/70 text-sm">Here's what happened behind the scenes.</p>
                </div>
            </div>
        </div>

        {{-- What happened --}}
        <div class="rounded-2xl border border-gray-800 bg-gray-900 px-6 py-5 mb-4">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-widest mb-4">What {{ $workerName }} did</p>
            <div class="space-y-4">
                @foreach($outcome['what_happened'] ?? [] as $i => $item)
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold text-gray-950"
                         style="background:var(--accent)">{{ $i + 1 }}</div>
                    <p class="text-gray-300 text-sm leading-relaxed">{{ $item['text'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Where to find it --}}
        @if(!empty($outcome['where_to_find']))
        <div class="rounded-2xl border border-yellow-400/15 bg-yellow-400/5 px-5 py-4 mb-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <div>
                <p class="text-yellow-400 text-sm font-semibold mb-0.5">{{ $outcome['where_to_find']['label'] }}</p>
                <p class="text-gray-500 text-xs leading-relaxed">{{ $outcome['where_to_find']['hint'] }}</p>
            </div>
        </div>
        @endif

        {{-- CTA --}}
        <a href="{{ route('onboarding.complete') }}"
           class="block w-full text-center font-black text-base py-4 rounded-xl transition-all mb-4"
           style="background:var(--accent);color:#1a1404">
            Go to dashboard →
        </a>

        {{-- Going forward --}}
        @if(!empty($outcome['going_forward']))
        <div class="rounded-2xl border border-gray-800 bg-gray-900/50 px-5 py-4 flex items-start gap-3">
            <svg class="w-5 h-5 text-gray-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-gray-400 text-sm leading-relaxed">{{ $outcome['going_forward'] }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Skip link — hide once success card appears --}}
    <a x-show="!success" href="{{ route('onboarding.complete') }}"
       class="block text-center text-gray-600 hover:text-gray-400 text-sm transition-colors mt-4">
        Skip — go straight to dashboard
    </a>
</div>

@else
{{-- ── NOT YET RUN ── --}}

<div class="mb-8">
    <h1 class="text-2xl font-black text-white mb-2">See {{ $workerName }} work — before you commit to anything.</h1>
    <p class="text-gray-400 text-sm">Fire a sample renewal email through your worker and watch it classify, match, and draft a response in real time.</p>
</div>

@if(session('fast_track_error'))
<div class="bg-red-500/10 border border-red-500/30 rounded-xl px-5 py-4 mb-5">
    <p class="text-red-400 text-sm font-semibold mb-1">&#9888; Can't run Fast Track</p>
    <p class="text-gray-400 text-xs">{{ session('fast_track_error') }}</p>
</div>
@endif

@if(!$hasCredential)
<div class="bg-yellow-400/10 border border-yellow-400/20 rounded-xl px-5 py-4 mb-5">
    <p class="text-yellow-400 text-sm font-semibold mb-1">&#9888; Gmail not connected</p>
    <p class="text-gray-400 text-xs">
        <a href="{{ route('onboarding.step', 'credential') }}" class="text-yellow-400 underline font-semibold">Connect Gmail first</a>
        — Fast Track needs it to read the sample email and push the draft.
    </p>
</div>
@endif

<form method="POST" action="{{ route('onboarding.step.handle', 'fast-track') }}" id="ft-form">
    @csrf
    <div class="mb-5">
        <label class="block text-gray-400 text-xs font-semibold uppercase tracking-widest mb-2">
            Sample email — edit to personalise
        </label>
        <textarea name="sample_email" rows="8"
            class="w-full bg-gray-900 border border-gray-800 focus:border-yellow-400/50 rounded-xl px-4 py-3 text-sm text-gray-300 leading-relaxed font-mono resize-none outline-none transition-colors"
            placeholder="Paste or type an email to test...">{{ $sampleRaw }}</textarea>
        <p class="text-gray-700 text-xs mt-1.5">This email will be fed directly into your worker's pipeline — exactly as if it arrived in your Gmail inbox.</p>
    </div>

    <button type="submit" id="run-btn"
        class="w-full bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-bold text-base py-4 rounded-xl transition-colors mb-3">
        &#9889; Run live test
    </button>
</form>

<a href="{{ route('onboarding.complete') }}" class="block text-center text-gray-600 hover:text-gray-400 text-sm transition-colors">
    Skip — go straight to dashboard
</a>
@endif

</x-onboarding-layout>

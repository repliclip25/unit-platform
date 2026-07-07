<x-onboarding-layout :stepName="$stepName" :sequence="$sequence" :stepIndex="$stepIndex" :wide="true">

@php
    $ft            = $contract?->fastTrack() ?? [];
    $sampleRaw     = $personaSample ?? $ft['raw_email'] ?? "Subject: Domain Renewal Notice — example.com\n\nHi,\n\nThis is a reminder that your domain example.com is due for renewal in 30 days. Please renew at your earliest convenience to avoid any service interruption.\n\nBest regards,\nDomain Services Team";
    $outcome       = $outcome ?? [];
    $workerName    = $contract?->identity()['name'] ?? 'Your worker';
    $isPersonalised = isset($personaSample) && $personaSample !== null;
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
        <h1 class="text-2xl font-black text-white mb-2 transition-all duration-300 leading-snug">
            <span x-show="!success">Ava is working...</span>
            <span x-show="success" x-cloak>Ava completed her first assignment.</span>
        </h1>
        <p class="text-sm transition-all duration-300 leading-relaxed">
            <span x-show="!success" class="text-gray-400">Here's everything happening behind the scenes.</span>
            <span x-show="success" x-cloak class="text-green-400/80">Your first renewal has already been processed from start to finish.</span>
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
            :onCompleteUrl="route('onboarding.gmail-draft')"
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
                    <p class="text-white font-black text-lg leading-snug mb-1">{{ $outcome['headline'] ?? 'Ava completed her first assignment.' }}</p>
                    <p class="text-green-400/70 text-sm">Your first renewal has already been processed from start to finish.</p>
                </div>
            </div>
        </div>

        {{-- What happened --}}
        <div class="rounded-2xl border border-gray-800 bg-gray-900 px-6 py-5 mb-4">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-widest mb-4">Without any manual work, Ava:</p>
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
        <a href="{{ route('onboarding.gmail-draft') }}"
           class="block w-full text-center font-black text-base py-4 rounded-xl transition-all mb-4"
           style="background:var(--accent);color:#1a1404">
            See My Draft
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
    <a x-show="!success" href="{{ route('onboarding.gmail-draft') }}"
       class="block text-center text-gray-600 hover:text-gray-400 text-sm transition-colors mt-4">
        Skip for now
    </a>
</div>

@else
{{-- ── NOT YET RUN ── --}}

<div class="mb-6">
    <p class="text-xs font-bold uppercase tracking-widest mb-4" style="color:var(--accent-text)">Step 4 of 4 &nbsp;·&nbsp; First assignment</p>
    <h1 class="text-2xl font-black text-white mb-3 leading-snug">Watch Ava handle her first job.</h1>
    <p class="text-gray-400 text-sm leading-relaxed">Rather than explaining what Ava does...</p>
    <p class="text-gray-400 text-sm leading-relaxed mt-1">Let's watch her work.</p>
    <p class="text-gray-400 text-sm leading-relaxed mt-2">
        We'll run one safe test renewal through her workflow so you can see exactly how she reads, understands, and prepares a response.
    </p>
    <div class="flex items-center gap-4 mt-3">
        <p class="text-gray-500 text-sm">No emails are sent.</p>
        <p class="text-gray-500 text-sm">Nothing changes inside your inbox.</p>
    </div>
</div>

@php
    // Parse the raw email into header fields + body for the inbox UI
    $rawLines     = explode("\n", str_replace("\r\n", "\n", trim($sampleRaw)));
    $parsedFrom    = '';
    $parsedSubject = '';
    $parsedDate    = '';
    $bodyStartIdx  = count($rawLines);
    foreach ($rawLines as $i => $line) {
        if (trim($line) === '') { $bodyStartIdx = $i + 1; break; }
        if (str_starts_with($line, 'From:'))    $parsedFrom    = trim(substr($line, 5));
        if (str_starts_with($line, 'Subject:')) $parsedSubject = trim(substr($line, 8));
        if (str_starts_with($line, 'Date:'))    $parsedDate    = trim(substr($line, 5));
    }
    $emailBody = implode("\n", array_slice($rawLines, $bodyStartIdx));
    // Header lines to reconstruct the full raw email on submit
    $headerLines = implode("\n", array_slice($rawLines, 0, $bodyStartIdx - 1));
@endphp

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
    {{-- Hidden field carries the reconstructed full raw email --}}
    <input type="hidden" name="sample_email" id="sample_email_full" value="{{ htmlspecialchars($sampleRaw) }}">
    {{-- Header block as hidden data for reconstruction --}}
    <input type="hidden" id="email_header_lines" value="{{ htmlspecialchars($headerLines) }}">

    {{-- ── Email inbox card ── --}}
    <div class="mb-5 rounded-2xl overflow-hidden border border-gray-800 bg-gray-950">

        {{-- Window chrome --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800 bg-gray-900">
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-red-500/70"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-400/70"></div>
                <div class="w-3 h-3 rounded-full bg-green-500/70"></div>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full {{ $isPersonalised ? 'bg-green-500' : 'bg-gray-600' }}"></div>
                <span class="text-xs font-medium {{ $isPersonalised ? 'text-green-400' : 'text-gray-500' }}">
                    {{ $isPersonalised ? 'Using your client data' : 'Sample email' }}
                </span>
            </div>
            <div class="w-16"></div>{{-- spacer --}}
        </div>

        {{-- Email header fields --}}
        <div class="px-5 pt-4 pb-3 border-b border-gray-800/60 space-y-2">
            <div class="flex items-start gap-3">
                <span class="text-gray-600 text-xs w-14 pt-0.5 shrink-0">From</span>
                <span class="text-gray-300 text-sm truncate">{{ $parsedFrom ?: 'Renewal System <renewals@example.com>' }}</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-gray-600 text-xs w-14 pt-0.5 shrink-0">To</span>
                <span class="text-gray-400 text-sm truncate">{{ auth()->user()->email }}</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-gray-600 text-xs w-14 pt-0.5 shrink-0">Subject</span>
                <span class="text-white text-sm font-medium truncate">{{ $parsedSubject ?: 'Domain Renewal Notice' }}</span>
            </div>
        </div>

        {{-- Editable body --}}
        <textarea id="email_body_edit" rows="9"
            class="w-full bg-gray-950 px-5 py-4 text-sm text-gray-300 leading-relaxed font-mono outline-none resize-y block"
            placeholder="Type or paste the email body here..."
            style="min-height:180px">{{ $emailBody }}</textarea>

        {{-- Footer hint --}}
        <div class="px-5 py-2.5 border-t border-gray-800/60 flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-gray-600 text-xs">AVA will process this exactly as if it arrived in your Gmail inbox — no real email is sent.</p>
        </div>
    </div>

    <button type="submit" id="run-btn"
        class="w-full font-bold text-base py-4 rounded-xl transition-colors mb-3"
        style="background:var(--accent);color:#1a1404">
        Run Test
    </button>
</form>

<a href="{{ route('onboarding.complete') }}" class="block text-center text-gray-600 hover:text-gray-400 text-sm transition-colors">
    Skip for now
</a>

<script>
// Reconstruct full raw email from headers + edited body before submit
(function() {
    const bodyEdit   = document.getElementById('email_body_edit');
    const fullField  = document.getElementById('sample_email_full');
    const headerLines = document.getElementById('email_header_lines').value;

    function sync() {
        fullField.value = headerLines + "\n\n" + bodyEdit.value;
    }
    bodyEdit.addEventListener('input', sync);
    document.getElementById('ft-form').addEventListener('submit', sync);
    sync(); // initial sync
})();
</script>
@endif

</x-onboarding-layout>

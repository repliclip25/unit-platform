<x-onboarding-layout :stepName="$stepName" :sequence="$sequence" :stepIndex="$stepIndex">

@if($intentWorker && $intentMeta)

{{-- Eyebrow --}}
<div class="flex items-center justify-center gap-2 mb-6">
    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full tracking-widest uppercase"
          style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);color:#22c55e">
        <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block" style="animation:pulse 1.4s ease infinite"></span>
        Your first AI employee
    </span>
</div>

{{-- Heading --}}
<div class="text-center mb-8">
    <h1 class="text-4xl font-black text-white mb-3 leading-tight">Meet Ava.</h1>
    <p class="text-gray-400 text-base">Your first AI employee is ready to start work.</p>
</div>

{{-- Body --}}
<div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-5 mb-6 space-y-3">
    <p class="text-gray-300 text-sm leading-relaxed">
        Imagine arriving tomorrow morning to find every renewal email already read, matched to the right client, and drafted for your approval.
    </p>
    <p class="text-gray-300 text-sm leading-relaxed">
        That's Ava.
    </p>
    <p class="text-gray-400 text-sm leading-relaxed">
        She works quietly in the background, monitors your inbox around the clock, and prepares renewal responses before you even open Gmail.
    </p>
    <p class="text-gray-400 text-sm leading-relaxed">
        You'll spend about three minutes getting her ready.
    </p>
    <p class="text-gray-400 text-sm leading-relaxed font-medium" style="color:var(--text-primary)">
        Then she starts working.
    </p>
</div>

{{-- Trust bullets --}}
<div class="space-y-2 mb-8">
    @foreach([
        'Monitors your inbox 24/7',
        'Detects renewal requests automatically',
        'Matches emails to the right client',
        'Drafts personalized replies',
        'Saves everything to Gmail Drafts',
        'Learns from every interaction',
    ] as $item)
    <div class="flex items-center gap-3">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"
             style="color:var(--accent-text)">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        <p class="text-gray-300 text-sm">{{ $item }}</p>
    </div>
    @endforeach
</div>

{{-- CTA --}}
<form method="POST" action="{{ route('onboarding.step.handle', 'welcome') }}">
    @csrf
    <button type="submit" class="w-full font-bold text-base py-4 rounded-xl transition-colors"
            style="background:var(--accent);color:#ffffff">
        Hire Ava
    </button>
</form>

@else
{{-- No intent — redirect to worker picker --}}
<script>window.location = '{{ route('onboarding.step', 'select-worker') }}';</script>
@endif

</x-onboarding-layout>

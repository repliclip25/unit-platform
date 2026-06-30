<x-onboarding-layout :stepName="$stepName" :sequence="$sequence" :stepIndex="$stepIndex">

@if($intentWorker && $intentMeta)
{{-- ── WORKER-SPECIFIC WELCOME ── --}}
<div class="text-center mb-10">
    <div class="inline-flex w-16 h-16 rounded-2xl items-center justify-center mb-6"
         style="background:rgba({{ $intentMeta['color'] === 'var(--accent)' ? '243,197,49' : ($intentMeta['color'] === '#818cf8' ? '129,140,248' : '52,211,153') }},0.12);border:1px solid rgba({{ $intentMeta['color'] === 'var(--accent)' ? '243,197,49' : ($intentMeta['color'] === '#818cf8' ? '129,140,248' : '52,211,153') }},0.25)">
        <span style="font-family:'Space Grotesk',sans-serif;font-size:26px;font-weight:800;color:{{ $intentMeta['color'] }}">
            {{ $intentMeta['label'][0] }}
        </span>
    </div>

    <div class="flex items-center justify-center gap-2 mb-4">
        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full"
              style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);color:#22c55e">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block" style="animation:pulse 1.4s ease infinite"></span>
            EMPLOYEE READY TO HIRE
        </span>
    </div>

    <h1 class="text-3xl font-black text-white mb-2">
        Welcome, {{ $userName }}.<br>
        <span style="color:{{ $intentMeta['color'] }}">{{ $intentMeta['label'] }}</span> is ready for you.
    </h1>
    <p class="text-gray-400 text-base leading-relaxed max-w-sm mx-auto">
        {{ $intentMeta['role'] }}
    </p>
</div>

@if(!empty($intentMeta['introduction']))
<div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 mb-6">
    <p class="text-gray-300 text-sm leading-relaxed italic">"{{ $intentMeta['introduction'] }}"</p>
</div>
@endif

@if(!empty($intentMeta['what_i_do']))
<div class="space-y-2 mb-8">
    @foreach($intentMeta['what_i_do'] as $capability)
    <div class="flex items-center gap-3 bg-gray-900 border border-gray-800 rounded-xl px-4 py-3">
        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0"
             style="background:rgba({{ $intentMeta['color'] === 'var(--accent)' ? '243,197,49' : ($intentMeta['color'] === '#818cf8' ? '129,140,248' : '52,211,153') }},0.15)">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"
                 style="color:{{ $intentMeta['color'] }}">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-gray-300 text-sm">{{ $capability }}</p>
    </div>
    @endforeach
</div>
@else
<div class="space-y-3 mb-10">
    @foreach($intentMeta['steps'] as $i => $stepLabel)
    <div class="flex items-center gap-4 bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm shrink-0"
             style="background:rgba({{ $intentMeta['color'] === 'var(--accent)' ? '243,197,49' : ($intentMeta['color'] === '#818cf8' ? '129,140,248' : '52,211,153') }},0.1);color:{{ $intentMeta['color'] }}">
            {{ $i + 1 }}
        </div>
        <p class="text-white font-semibold text-sm">{{ $stepLabel }}</p>
    </div>
    @endforeach
</div>
@endif

<div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 mb-8">
    <p class="text-gray-500 text-xs leading-relaxed">
        🎁 <strong class="text-gray-400">25 free transactions included.</strong>
        No credit card needed. {{ $intentMeta['label'] }} starts processing the moment it's connected.
    </p>
</div>

<form method="POST" action="{{ route('onboarding.step.handle', 'welcome') }}">
    @csrf
    <button type="submit" class="w-full font-bold text-base py-4 rounded-xl transition-colors"
            style="background:{{ $intentMeta['color'] }};color:#12100a">
        Hire {{ $intentMeta['label'] }} →
    </button>
</form>

@else
{{-- ── GENERIC WELCOME (no intent) — redirect to picker --}}
<script>window.location = '{{ route('onboarding.step', 'select-worker') }}';</script>
@endif

</x-onboarding-layout>

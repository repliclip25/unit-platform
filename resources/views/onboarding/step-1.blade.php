<x-onboarding-layout :step="$step">

@if($intentWorker && $intentMeta)
{{-- ── WORKER-SPECIFIC WELCOME ── --}}
<div class="text-center mb-10">
    <div class="inline-flex w-16 h-16 rounded-2xl items-center justify-center mb-6"
         style="background:rgba({{ $intentMeta['color'] === 'var(--accent)' ? '243,197,49' : ($intentMeta['color'] === '#818cf8' ? '129,140,248' : '52,211,153') }},0.12);border:1px solid rgba({{ $intentMeta['color'] === 'var(--accent)' ? '243,197,49' : ($intentMeta['color'] === '#818cf8' ? '129,140,248' : '52,211,153') }},0.25)">
        <span style="font-family:'Space Grotesk',sans-serif;font-size:26px;font-weight:800;color:{{ $intentMeta['color'] }}">
            {{ $intentMeta['label'][0] }}
        </span>
    </div>

    {{-- Live badge --}}
    <div class="flex items-center justify-center gap-2 mb-4">
        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full"
              style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);color:#22c55e">
            <span class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block" style="animation:pulse 1.4s ease infinite"></span>
            WORKER READY TO DEPLOY
        </span>
    </div>

    <h1 class="text-3xl font-black text-white mb-2">
        Welcome, {{ $userName }}.<br>
        <span style="color:{{ $intentMeta['color'] }}">{{ $intentMeta['label'] }}</span> is ready for you.
    </h1>
    <p class="text-gray-400 text-base leading-relaxed max-w-sm mx-auto">
        {{ $intentMeta['role'] }}. We'll have it running on your account in under 5 minutes.
    </p>
</div>

{{-- Worker-specific steps --}}
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

<div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 mb-8">
    <p class="text-gray-500 text-xs leading-relaxed">
        🎁 <strong class="text-gray-400">25 free transactions included.</strong>
        No credit card needed. {{ $intentMeta['label'] }} starts processing the moment it's connected.
    </p>
</div>

<form method="POST" action="{{ route('onboarding.1') }}">
    @csrf
    <button type="submit" class="w-full font-bold text-base py-4 rounded-xl transition-colors"
            style="background:{{ $intentMeta['color'] }};color:#ffffff">
        Set up {{ $intentMeta['label'] }} →
    </button>
</form>

@else
{{-- ── GENERIC WELCOME ── --}}
<div class="text-center mb-10">
    <div class="inline-flex w-16 h-16 bg-yellow-400/10 border border-yellow-400/20 rounded-2xl items-center justify-center mb-6">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2">
            <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
        </svg>
    </div>
    <h1 class="text-3xl font-black text-white mb-3">Welcome, {{ $userName }}</h1>
    <p class="text-gray-400 text-lg leading-relaxed">
        UNIT deploys AI workers that handle the operational work that slows your team down.<br>
        Let's get you set up in under 5 minutes.
    </p>
</div>

<div class="space-y-3 mb-10">
    <div class="flex items-center gap-4 bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
        <div class="w-8 h-8 bg-yellow-400/10 rounded-lg flex items-center justify-center text-yellow-400 font-bold text-sm shrink-0">1</div>
        <div>
            <p class="text-white font-semibold text-sm">Pick your first worker</p>
            <p class="text-gray-500 text-sm">Choose the AI worker that fits your workflow</p>
        </div>
    </div>
    <div class="flex items-center gap-4 bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
        <div class="w-8 h-8 bg-yellow-400/10 rounded-lg flex items-center justify-center text-yellow-400 font-bold text-sm shrink-0">2</div>
        <div>
            <p class="text-white font-semibold text-sm">Connect your inbox</p>
            <p class="text-gray-500 text-sm">One click — no passwords, fully secure OAuth</p>
        </div>
    </div>
    <div class="flex items-center gap-4 bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
        <div class="w-8 h-8 bg-yellow-400/10 rounded-lg flex items-center justify-center text-yellow-400 font-bold text-sm shrink-0">3</div>
        <div>
            <p class="text-white font-semibold text-sm">Watch it work</p>
            <p class="text-gray-500 text-sm">Run a live test and see your worker in action</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('onboarding.1') }}">
    @csrf
    <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-bold text-base py-4 rounded-xl transition-colors">
        Let's go →
    </button>
</form>
@endif

</x-onboarding-layout>

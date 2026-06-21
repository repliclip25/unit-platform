<x-onboarding-layout :step="$step">

<div class="text-center mb-10">
    <div class="inline-flex w-16 h-16 bg-yellow-400/10 border border-yellow-400/20 rounded-2xl items-center justify-center mb-6">
        <span class="text-3xl">👋</span>
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

</x-onboarding-layout>

<x-onboarding-layout :stepName="$stepName" :sequence="$sequence" :stepIndex="$stepIndex">

@php
    $health    = $memoryHealth;
    $complete  = $health['complete'];
    $hasAny    = ($clientCount + $contactCount + $assetCount) > 0;

    // Onboarding UX: 1 client = ready. Show 100% once any client exists.
    $displayScore   = $complete >= 1 ? 100 : 0;
    $displayHealthy = $complete >= 1;

    $mc = $personaDef['memory_copy'] ?? [
        'client_noun'        => 'client',
        'client_noun_plural' => 'clients',
        'asset_noun'         => 'asset',
        'example_client'     => 'Acme Corp',
        'example_asset'      => 'Service Agreement',
    ];
    $clientNounPlural = $mc['client_noun_plural'] ?? ($mc['client_noun'] . 's');
    $assetTypeOptions = $personaDef['asset_types'] ?? ['other' => 'Other'];
@endphp

<div class="mb-6">
    <p class="text-xs font-bold uppercase tracking-widest mb-4" style="color:var(--accent-text)">Step 3 of 4 &nbsp;·&nbsp; Build memory</p>
    <h1 class="text-2xl font-black text-white mb-3 leading-snug">Introduce Ava to your {{ $clientNounPlural }}.</h1>
    <p class="text-gray-400 text-sm leading-relaxed">A new employee becomes more useful after learning who your customers are.</p>
    <p class="text-gray-400 text-sm leading-relaxed mt-1">The same is true here.</p>
    <p class="text-gray-400 text-sm leading-relaxed mt-2">
        Each {{ $mc['client_noun'] }} you add helps Ava recognize future renewal emails automatically and write more accurate drafts.
    </p>
    <p class="text-gray-400 text-sm leading-relaxed mt-2">You only need one {{ $mc['client_noun'] }} to get started.</p>
</div>

{{-- Success flash --}}
@if(session('quick_add_success'))
<div class="mb-4 flex items-center gap-3 bg-green-500/10 border border-green-500/20 rounded-xl px-4 py-3">
    <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <p class="text-green-400 text-sm">{{ session('quick_add_success') }}</p>
</div>
@endif
@if(session('success'))
<div class="mb-4 flex items-center gap-3 bg-green-500/10 border border-green-500/20 rounded-xl px-4 py-3">
    <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <p class="text-green-400 text-sm">{{ session('success') }}</p>
</div>
@endif

{{-- Memory health bar --}}
<div class="mb-4 bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
    <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-semibold text-gray-400">Memory Coverage</span>
        <span class="text-xs font-bold {{ $displayHealthy ? 'text-green-400' : 'text-gray-500' }}">
            {{ $displayScore }}%
        </span>
    </div>
    <div class="h-2 rounded-full overflow-hidden bg-gray-800">
        <div class="h-full rounded-full transition-all duration-700"
             style="width:{{ $displayScore }}%;background:{{ $displayHealthy ? '#4ade80' : 'rgba(255,255,255,0.1)' }}"></div>
    </div>
    <p class="text-xs mt-2 {{ $displayHealthy ? 'text-green-400/70' : 'text-gray-600' }}">
        @if($displayHealthy)
            Ava knows <strong>{{ $complete }} {{ $mc['client_noun'] }}{{ $complete === 1 ? '' : 's' }}</strong>. She's ready.
        @else
            Add your first {{ $mc['client_noun'] }} below to get started.
        @endif
    </p>
</div>

{{-- Continue button — only shown above form when client has been added --}}
@if($displayHealthy)
<form method="POST" action="{{ route('onboarding.step.handle', 'memory') }}" class="mb-6">
    @csrf
    <button type="submit"
            class="w-full font-bold text-base py-4 rounded-xl transition"
            style="background:var(--accent);color:#111">
        Continue
    </button>
    <p class="text-center text-xs text-gray-600 mt-2">
        You can add more clients from your dashboard to improve Ava's accuracy.
    </p>
</form>
@endif

{{-- ── Quick-add form ── --}}
<div class="mb-5">
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Add a {{ $mc['client_noun'] }} to Ava's memory</p>
    <form method="POST" action="{{ route('onboarding.memory.quickadd') }}"
          class="bg-gray-900 border border-gray-800 rounded-xl p-5 space-y-3">
        @csrf

        @if($errors->any())
        <div class="text-red-400 text-xs">{{ $errors->first() }}</div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ ucfirst($mc['client_noun']) }} / Company name <span class="text-red-400">*</span></label>
                <input type="text" name="client_name" value="{{ old('client_name') }}"
                       placeholder="e.g. {{ $mc['example_client'] }}"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-yellow-400/50">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Contact name <span class="text-red-400">*</span></label>
                <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                       placeholder="e.g. Maria Torres"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-yellow-400/50">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Contact email <span class="text-red-400">*</span></label>
                <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                       placeholder="e.g. maria@riversideauto.com"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-yellow-400/50">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ ucfirst($mc['asset_noun']) }} <span class="text-red-400">*</span></label>
                <input type="text" name="asset_name" value="{{ old('asset_name') }}"
                       placeholder="e.g. {{ $mc['example_asset'] }}"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-yellow-400/50">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ ucfirst($mc['asset_noun']) }} type</label>
                <select name="asset_type"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:outline-none focus:border-yellow-400/50">
                    @foreach($assetTypeOptions as $val => $label)
                    <option value="{{ $val }}" {{ old('asset_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Renewal date</label>
                <input type="date" name="renewal_date" value="{{ old('renewal_date') }}"
                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-300 focus:outline-none focus:border-yellow-400/50">
            </div>
        </div>

        <button type="submit"
                class="w-full py-2.5 rounded-xl text-sm font-bold transition"
                style="background:var(--accent);color:#111">
            + Add client
        </button>
    </form>
</div>

{{-- Clients added so far --}}
@if($sampleClients->count())
<div class="mb-5 bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-800 flex items-center justify-between">
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Added so far</span>
        <span class="text-xs text-gray-600">{{ $clientCount }} client{{ $clientCount === 1 ? '' : 's' }} · {{ $contactCount }} contact{{ $contactCount === 1 ? '' : 's' }} · {{ $assetCount }} asset{{ $assetCount === 1 ? '' : 's' }}</span>
    </div>
    <div class="divide-y divide-gray-800/60">
        @foreach($sampleClients->take(8) as $client)
        <div class="px-5 py-3">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-white text-sm font-medium">{{ $client->name }}</span>
                @if($client->contacts->whereNotNull('email')->count())
                    <span class="text-xs text-green-400">✓ contact</span>
                @else
                    <span class="text-xs text-gray-600">no contact email</span>
                @endif
                @if($client->assets->count())
                    <span class="text-xs text-green-400">✓ {{ $client->assets->count() }} asset{{ $client->assets->count() === 1 ? '' : 's' }}</span>
                @else
                    <span class="text-xs text-gray-600">no assets</span>
                @endif
            </div>
        </div>
        @endforeach
        @if($sampleClients->count() > 8)
        <div class="px-5 py-2">
            <p class="text-xs text-gray-600">+ {{ $sampleClients->count() - 8 }} more</p>
        </div>
        @endif
    </div>
</div>
@endif

{{-- Secondary options --}}
<div class="mb-6 space-y-2">
    <a href="{{ route('memory.import.template', 'clients') }}"
       class="flex items-center gap-3 bg-gray-900/60 border border-gray-800 hover:border-gray-700 rounded-xl px-4 py-3 group transition-colors">
        <span class="text-lg shrink-0">📥</span>
        <div class="flex-1 min-w-0">
            <p class="text-gray-300 text-sm font-medium group-hover:text-white transition-colors">Already have a client list?</p>
            <p class="text-gray-600 text-xs">Import everyone in one click.</p>
        </div>
        <svg class="w-3.5 h-3.5 text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>

    <form method="POST" action="{{ route('onboarding.memory.seed') }}">
        @csrf
        <button type="submit"
                class="w-full flex items-center gap-3 bg-gray-900/40 border border-gray-800/60 hover:border-gray-700 rounded-xl px-4 py-3 group transition-colors text-left">
            <span class="text-lg shrink-0">🧪</span>
            <div class="flex-1 min-w-0">
                <p class="text-gray-500 text-sm group-hover:text-gray-400 transition-colors">Just exploring?</p>
                <p class="text-gray-700 text-xs">Load sample clients and replace them later.</p>
            </div>
        </button>
    </form>
</div>

{{-- Platform defaults --}}
<div class="bg-gray-900/60 border border-gray-800/60 rounded-xl px-5 py-4 mb-6">
    <div class="flex items-center gap-2 mb-1">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <p class="text-gray-300 text-xs font-semibold">Platform defaults loaded</p>
    </div>
    <p class="text-gray-600 text-xs leading-relaxed">
        6 automation rules and {{ $platformTemplates->count() }} email templates are pre-configured. Customise them from your worker dashboard.
    </p>
</div>

{{-- Ghost continue — only shown at bottom when no client added yet --}}
@if(!$displayHealthy)
<form method="POST" action="{{ route('onboarding.step.handle', 'memory') }}">
    @csrf
    <button type="submit"
            class="w-full font-semibold text-base py-4 rounded-xl transition border border-gray-700/60 text-gray-500 hover:text-gray-300 hover:border-gray-600">
        Continue without adding a client
    </button>
    <p class="text-center text-xs text-gray-700 mt-2">
        Ava will still run — but she won't recognize your clients until you add them.
    </p>
</form>
@endif

</x-onboarding-layout>

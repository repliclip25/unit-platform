<x-onboarding-layout :step="$step">

<div class="mb-8">
    <p class="text-yellow-400 text-sm font-semibold uppercase tracking-widest mb-2">Step 3 of 4</p>
    <h1 class="text-2xl font-black text-white mb-2">Load your memory</h1>
    <p class="text-gray-400">Your worker uses this to recognize clients, contacts, and assets in emails. The more you add, the smarter it gets.</p>
</div>

{{-- Memory counters --}}
<div class="grid grid-cols-3 gap-3 mb-6">
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-4 text-center">
        <p class="text-2xl font-black text-white mb-1">{{ $clientCount }}</p>
        <p class="text-gray-500 text-xs">Clients</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-4 text-center">
        <p class="text-2xl font-black text-white mb-1">{{ $contactCount }}</p>
        <p class="text-gray-500 text-xs">Contacts</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-4 text-center">
        <p class="text-2xl font-black text-white mb-1">{{ $assetCount }}</p>
        <p class="text-gray-500 text-xs">Assets</p>
    </div>
</div>

{{-- Training layers from contract --}}
@if(!empty($trainSchema))
<div class="bg-gray-900 border border-gray-800 rounded-xl divide-y divide-gray-800 mb-5">
    @foreach($trainSchema as $layer)
    <div class="px-5 py-4 flex items-start gap-4">
        <div class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
            <span class="text-xs font-bold text-gray-400">{{ strtoupper(substr($layer['key'], 0, 2)) }}</span>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-0.5">
                <p class="text-white font-semibold text-sm">{{ $layer['label'] }}</p>
                @if(!($layer['required'] ?? false))
                    <span class="text-gray-600 text-xs">optional</span>
                @else
                    <span class="text-yellow-400 text-xs">required</span>
                @endif
            </div>
            <p class="text-gray-500 text-xs leading-relaxed">{{ $layer['description'] }}</p>
            <p class="text-gray-600 text-xs mt-1">{{ $layer['format_hint'] }}</p>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Import links --}}
<div class="space-y-3 mb-6">
    <a href="{{ route('workers.memory', $depId) }}"
        class="flex items-center gap-4 bg-gray-900 border border-gray-800 hover:border-yellow-400/40 rounded-xl px-5 py-4 transition-all group">
        <div class="w-10 h-10 bg-gray-800 group-hover:bg-yellow-400/10 rounded-xl flex items-center justify-center text-xl transition-colors">📁</div>
        <div>
            <p class="text-white font-semibold text-sm">Upload a spreadsheet</p>
            <p class="text-gray-500 text-xs">Import from a CSV or Excel file</p>
        </div>
        <svg class="w-4 h-4 text-gray-600 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>

    <a href="{{ route('workers.memory', $depId) }}"
        class="flex items-center gap-4 bg-gray-900 border border-gray-800 hover:border-yellow-400/40 rounded-xl px-5 py-4 transition-all group">
        <div class="w-10 h-10 bg-gray-800 group-hover:bg-yellow-400/10 rounded-xl flex items-center justify-center text-xl transition-colors">✏️</div>
        <div>
            <p class="text-white font-semibold text-sm">Add manually</p>
            <p class="text-gray-500 text-xs">Type in entries one at a time</p>
        </div>
        <svg class="w-4 h-4 text-gray-600 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
</div>

<form method="POST" action="{{ route('onboarding.4') }}">
    @csrf
    <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-bold text-base py-4 rounded-xl transition-colors mb-3">
        {{ ($clientCount + $contactCount + $assetCount) > 0 ? 'Continue →' : 'Skip for now →' }}
    </button>
</form>

@if(($clientCount + $contactCount + $assetCount) === 0)
<p class="text-center text-gray-600 text-xs">You can always add memory later from your worker dashboard</p>
@endif

</x-onboarding-layout>

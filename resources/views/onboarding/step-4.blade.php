<x-onboarding-layout :step="$step">

<div class="mb-8">
    <p class="text-yellow-400 text-sm font-semibold uppercase tracking-widest mb-2">Step 3 of 4</p>
    <h1 class="text-2xl font-black text-white mb-2">Load your memory</h1>
    <p class="text-gray-400 text-sm leading-relaxed">
        Your worker uses this to recognise clients, contacts, and assets in emails.
        The more you add, the smarter it gets from day one.
    </p>
</div>

@php $hasMemory = ($clientCount + $contactCount + $assetCount) > 0; @endphp

{{-- Memory counters --}}
<div class="grid grid-cols-3 gap-2 mb-5">
    <div class="bg-gray-900 border {{ $clientCount > 0 ? 'border-yellow-400/30' : 'border-gray-800' }} rounded-xl px-3 py-4 text-center">
        <p class="text-2xl font-black {{ $clientCount > 0 ? 'text-yellow-400' : 'text-white' }}">{{ $clientCount }}</p>
        <p class="text-gray-600 text-xs mt-0.5">Clients</p>
    </div>
    <div class="bg-gray-900 border {{ $contactCount > 0 ? 'border-yellow-400/30' : 'border-gray-800' }} rounded-xl px-3 py-4 text-center">
        <p class="text-2xl font-black {{ $contactCount > 0 ? 'text-yellow-400' : 'text-white' }}">{{ $contactCount }}</p>
        <p class="text-gray-600 text-xs mt-0.5">Contacts</p>
    </div>
    <div class="bg-gray-900 border {{ $assetCount > 0 ? 'border-yellow-400/30' : 'border-gray-800' }} rounded-xl px-3 py-4 text-center">
        <p class="text-2xl font-black {{ $assetCount > 0 ? 'text-yellow-400' : 'text-white' }}">{{ $assetCount }}</p>
        <p class="text-gray-600 text-xs mt-0.5">Assets</p>
    </div>
</div>

@if($hasMemory)
{{-- Has data — show add more option --}}
<div class="bg-green-500/10 border border-green-500/20 rounded-xl px-5 py-4 mb-5 flex items-center gap-3">
    <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <p class="text-green-400 text-sm font-medium">Memory loaded — your worker can start recognising clients right away.</p>
</div>
<a href="{{ route('workers.memory', $depId) }}" target="_blank"
   class="flex items-center gap-3 bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl px-5 py-3.5 mb-6 group transition-colors">
    <span class="text-gray-400 text-sm group-hover:text-white transition-colors">Add more clients, contacts &amp; assets</span>
    <svg class="w-3.5 h-3.5 text-gray-600 ml-auto shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
</a>

@else
{{-- No data — two options --}}
<div class="space-y-3 mb-5">
    {{-- Primary: import their own --}}
    <a href="{{ route('workers.memory', $depId) }}" target="_blank"
       class="flex items-center gap-4 bg-gray-900 border border-gray-800 hover:border-yellow-400/40 rounded-xl px-5 py-4 group transition-all">
        <div class="w-10 h-10 bg-gray-800 group-hover:bg-yellow-400/10 rounded-xl flex items-center justify-center text-xl shrink-0 transition-colors">📁</div>
        <div class="flex-1 min-w-0">
            <p class="text-white font-semibold text-sm">Import your clients &amp; assets</p>
            <p class="text-gray-500 text-xs mt-0.5">Upload a CSV or add entries manually — takes 2 minutes</p>
        </div>
        <svg class="w-4 h-4 text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
    </a>

    {{-- Secondary: use sample defaults --}}
    <form method="POST" action="{{ route('onboarding.memory.seed') }}">
        @csrf
        <button type="submit"
                class="w-full flex items-center gap-4 bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl px-5 py-4 group transition-all text-left">
            <div class="w-10 h-10 bg-gray-800 group-hover:bg-gray-700 rounded-xl flex items-center justify-center text-xl shrink-0 transition-colors">🧪</div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-semibold text-sm">Use sample data to see the worker in action</p>
                <p class="text-gray-500 text-xs mt-0.5">We'll load example clients &amp; assets so you can run a live test — replace with real data anytime</p>
            </div>
        </button>
    </form>
</div>
<p class="text-gray-700 text-xs text-center mb-6">You can always update memory from your worker dashboard</p>
@endif

{{-- Platform defaults notice — mentioned, not interactive --}}
<div class="bg-gray-900/60 border border-gray-800/60 rounded-xl px-5 py-4 mb-6">
    <div class="flex items-center gap-2 mb-2">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <p class="text-gray-300 text-xs font-semibold">Platform defaults loaded</p>
    </div>
    <p class="text-gray-600 text-xs leading-relaxed">
        6 automation rules and {{ $platformTemplates->count() }} email templates are pre-configured for your worker.
        You can review and customise these from your worker dashboard after setup.
    </p>
</div>

{{-- CTA --}}
<form method="POST" action="{{ route('onboarding.4') }}">
    @csrf
    <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-bold text-base py-4 rounded-xl transition-colors">
        {{ $hasMemory ? 'Continue →' : 'Skip for now →' }}
    </button>
</form>

@if(!$hasMemory)
<p class="text-center text-gray-700 text-xs mt-3">No data required — your worker will still run the fast track test</p>
@endif

</x-onboarding-layout>

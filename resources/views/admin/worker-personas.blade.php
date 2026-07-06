<x-app-layout title="Personas — {{ $worker->name ?? $slug }}">

    {{-- Admin worker subnav --}}
    @include('partials.admin-worker-subnav', ['slug' => $slug, 'active' => 'personas'])

    @if(session('success'))
        <div class="mb-4 bg-green-900/40 border border-green-700/50 text-green-300 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @php
        $icons = [
            'computer'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
            'shield'    => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
            'clipboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
            'grid'      => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>',
        ];
    @endphp

    {{-- Summary strip --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3">
            <p class="text-gray-500 text-xs mb-1">Total deployments</p>
            <p class="text-white text-xl font-bold">{{ $totalDeployments }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3">
            <p class="text-gray-500 text-xs mb-1">Personas defined</p>
            <p class="text-white text-xl font-bold">{{ count($personas) }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3">
            <p class="text-gray-500 text-xs mb-1">No persona set</p>
            <p class="text-xl font-bold {{ $noPersonaCount > 0 ? 'text-amber-400' : 'text-gray-600' }}">{{ $noPersonaCount }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3">
            <p class="text-gray-500 text-xs mb-1">Source</p>
            <p class="text-gray-400 text-xs mt-1 font-mono">WorkerContract::personas()</p>
        </div>
    </div>

    {{-- Persona cards --}}
    <div class="space-y-4">
        @foreach($personas as $key => $p)
        @php
            $adopted      = $adoptionCounts[$key] ?? 0;
            $adoptPct     = $totalDeployments > 0 ? round(($adopted / $totalDeployments) * 100) : 0;
            $masterRules  = $platformRuleCounts[$key] ?? 0;
            $contractRules= $contractRuleCounts[$key] ?? 0;
            $rulesMissing = $contractRules > $masterRules;
            $icon         = $icons[$p['icon'] ?? 'grid'];
        @endphp
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <div class="px-5 py-4 flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                     style="background:rgba(var(--accent-rgb),0.1);color:var(--accent-text)">
                    {!! $icon !!}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <h3 class="text-white font-semibold text-sm">{{ $p['label'] }}</h3>
                                <span class="font-mono text-xs text-gray-600 bg-gray-800 px-1.5 py-0.5 rounded">{{ $key }}</span>
                            </div>
                            <p class="text-gray-400 text-xs">{{ $p['tagline'] }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-white text-lg font-bold">{{ $adopted }}</p>
                            <p class="text-gray-600 text-xs">{{ $adoptPct }}% adoption</p>
                        </div>
                    </div>

                    <p class="text-gray-500 text-xs mt-2 leading-relaxed">{{ $p['detail'] }}</p>

                    <div class="flex flex-wrap gap-1.5 mt-2">
                        @foreach($p['examples'] as $ex)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-800 text-gray-500">{{ $ex }}</span>
                        @endforeach
                    </div>

                    {{-- Adoption bar --}}
                    @if($totalDeployments > 0)
                    <div class="mt-3 h-1 rounded-full bg-gray-800 overflow-hidden w-full max-w-xs">
                        <div class="h-full rounded-full transition-all" style="width:{{ $adoptPct }}%;background:var(--accent)"></div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Footer strip --}}
            <div class="border-t border-gray-800 px-5 py-3 flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-4">
                    <span class="text-xs text-gray-500">
                        <span class="font-medium {{ $masterRules > 0 ? 'text-gray-300' : 'text-amber-400' }}">{{ $masterRules }}</span>
                        / {{ $contractRules }} platform rules synced
                    </span>
                    @if($rulesMissing)
                    <span class="text-xs text-amber-400 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Master rules out of sync
                    </span>
                    @endif
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <span>Asset types:</span>
                        @foreach(array_keys($p['asset_types']) as $at)
                        <span class="bg-gray-800 px-1.5 py-0.5 rounded text-gray-500">{{ $at }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.workers.rules', $slug) }}?persona={{ $key }}"
                       class="text-xs px-3 py-1.5 rounded-lg bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition">
                       Manage rules
                    </a>
                    @if($rulesMissing)
                    <form method="POST" action="{{ route('admin.workers.rules.sync', $slug) }}">
                        @csrf
                        <input type="hidden" name="persona" value="{{ $key }}">
                        <button class="text-xs px-3 py-1.5 rounded-lg transition font-semibold"
                                style="background:rgba(var(--accent-rgb),0.15);color:var(--accent-text)">
                            Sync from contract
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($noPersonaCount > 0)
    <div class="mt-6 bg-amber-900/10 border border-amber-700/30 rounded-xl px-5 py-4">
        <div class="flex items-start gap-3">
            <svg class="w-4 h-4 text-amber-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-amber-300 text-sm font-medium">{{ $noPersonaCount }} deployment{{ $noPersonaCount === 1 ? '' : 's' }} without a persona</p>
                <p class="text-amber-700 text-xs mt-0.5">These deployments are using generic rules only. Consider prompting these tenants to complete the Use Case step.</p>
            </div>
        </div>
    </div>
    @endif

</x-app-layout>

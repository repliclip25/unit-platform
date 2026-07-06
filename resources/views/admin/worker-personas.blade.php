<x-app-layout title="Personas — {{ $worker->name ?? $slug }}">

    @include('partials.admin-worker-subnav', ['slug' => $slug, 'active' => 'personas'])

    @if(session('success'))
        <div class="mb-4 bg-green-900/40 border border-green-700/50 text-green-300 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-900/40 border border-red-700/50 text-red-300 rounded-xl px-5 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    @php
        $icons = [
            'computer'  => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
            'shield'    => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
            'clipboard' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
            'grid'      => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>',
            'briefcase' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
            'home'      => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        ];
        $iconOptions = array_keys($icons);
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
            <p class="text-gray-400 text-xs mt-1 font-mono">worker_personas table</p>
        </div>
    </div>

    {{-- Persona cards --}}
    <div class="space-y-4 mb-8">
        @foreach($personas as $key => $p)
        @php
            $adopted      = $adoptionCounts[$key] ?? 0;
            $adoptPct     = $totalDeployments > 0 ? round(($adopted / $totalDeployments) * 100) : 0;
            $masterRules  = $platformRuleCounts[$key] ?? 0;
            $contractRules= $contractRuleCounts[$key] ?? 0;
            $rulesMissing = $contractRules > $masterRules;
            $icon         = $icons[$p['icon'] ?? 'grid'];
            $dbRow        = $personaRows[$key] ?? null;
        @endphp
        <div x-data="{ editing: false }" class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">

            {{-- View mode --}}
            <div x-show="!editing">
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

                        @if($totalDeployments > 0)
                        <div class="mt-3 h-1 rounded-full bg-gray-800 overflow-hidden w-full max-w-xs">
                            <div class="h-full rounded-full transition-all" style="width:{{ $adoptPct }}%;background:var(--accent)"></div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-800 px-5 py-3 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-4">
                        <span class="text-xs text-gray-500">
                            <span class="font-medium {{ $masterRules > 0 ? 'text-gray-300' : 'text-amber-400' }}">{{ $masterRules }}</span>
                            / {{ $contractRules }} platform rules synced
                        </span>
                        @if($rulesMissing)
                        <span class="text-xs text-amber-400 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Rules out of sync
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
                        @if($dbRow)
                        <button @click="editing = true"
                                class="text-xs px-3 py-1.5 rounded-lg bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 transition">
                            Edit
                        </button>
                        @endif
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
                        @if($dbRow && $adopted === 0)
                        <form method="POST" action="{{ route('admin.workers.personas.destroy', [$slug, $dbRow->id]) }}"
                              onsubmit="return confirm('Remove persona {{ $p['label'] }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-gray-700 hover:text-red-400 transition">Remove</button>
                        </form>
                        @elseif($adopted > 0)
                        <span class="text-xs text-gray-700" title="{{ $adopted }} deployment(s) using this persona">· in use</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Edit mode --}}
            @if($dbRow)
            <div x-show="editing" x-cloak class="px-5 py-5">
                <form method="POST" action="{{ route('admin.workers.personas.update', [$slug, $dbRow->id]) }}" class="space-y-4">
                    @csrf @method('PATCH')

                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-white text-sm font-semibold">Edit: {{ $p['label'] }} <span class="font-mono text-xs text-gray-600">{{ $key }}</span></h3>
                        <button type="button" @click="editing = false" class="text-xs text-gray-600 hover:text-gray-400 transition">Cancel</button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Label</label>
                            <input type="text" name="label" value="{{ $p['label'] }}" required
                                   class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                        </div>
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Icon</label>
                            <select name="icon" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                                @foreach($iconOptions as $ic)
                                <option value="{{ $ic }}" {{ ($p['icon'] ?? 'grid') === $ic ? 'selected' : '' }}>{{ $ic }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-gray-500 text-xs block mb-1">Tagline <span class="text-gray-700">(shown on onboarding persona card)</span></label>
                        <input type="text" name="tagline" value="{{ $p['tagline'] }}" required
                               class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                    </div>

                    <div>
                        <label class="text-gray-500 text-xs block mb-1">Detail <span class="text-gray-700">(longer description)</span></label>
                        <textarea name="detail" rows="2" required
                                  class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none">{{ $p['detail'] }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Asset types <span class="text-gray-700">(one per line: key: Label)</span></label>
                            <textarea name="asset_types" rows="5"
                                      class="w-full bg-gray-800 text-white text-xs font-mono rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none">@foreach($p['asset_types'] as $k => $v){{ $k }}: {{ $v }}
@endforeach</textarea>
                        </div>
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Examples <span class="text-gray-700">(one per line)</span></label>
                            <textarea name="examples" rows="5"
                                      class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none">{{ implode("\n", $p['examples'] ?? []) }}</textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Client noun</label>
                            <input type="text" name="client_noun" value="{{ $p['memory_copy']['client_noun'] ?? 'client' }}"
                                   class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                        </div>
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Asset noun</label>
                            <input type="text" name="asset_noun" value="{{ $p['memory_copy']['asset_noun'] ?? 'asset' }}"
                                   class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                        </div>
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Example client</label>
                            <input type="text" name="example_client" value="{{ $p['memory_copy']['example_client'] ?? '' }}"
                                   class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                        </div>
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Example asset</label>
                            <input type="text" name="example_asset" value="{{ $p['memory_copy']['example_asset'] ?? '' }}"
                                   class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                        </div>
                    </div>

                    {{-- Nudge copy --}}
                    <div class="border-t border-gray-800 pt-4">
                        <p class="text-gray-500 text-xs mb-3 font-medium">Nudge emails <span class="text-gray-700 font-normal">— placeholders: {name} {score} {needed} {threshold} {app_url}</span></p>
                        @foreach(['d1' => 'Day 1', 'd3' => 'Day 3', 'd7' => 'Day 7'] as $day => $dayLabel)
                        <div class="mb-3">
                            <p class="text-gray-600 text-xs mb-1.5">{{ $dayLabel }}</p>
                            <input type="text" name="nudge_{{ $day }}_subject"
                                   value="{{ $p['nudge_copy'][$day]['subject'] ?? '' }}"
                                   placeholder="Subject"
                                   class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 mb-1.5">
                            <textarea name="nudge_{{ $day }}_body" rows="3"
                                      placeholder="Email body"
                                      class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none">{{ $p['nudge_copy'][$day]['body'] ?? '' }}</textarea>
                        </div>
                        @endforeach
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ ($dbRow->is_active ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-700 bg-gray-800 text-yellow-400">
                        <span class="text-gray-400 text-xs">Active (shown to tenants)</span>
                    </label>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                                class="text-sm px-5 py-2 rounded-lg font-semibold transition"
                                style="background:var(--accent);color:#111">
                            Save changes
                        </button>
                        <button type="button" @click="editing = false" class="text-xs text-gray-600 hover:text-gray-400 transition">Cancel</button>
                    </div>
                </form>
            </div>
            @endif

        </div>
        @endforeach
    </div>

    @if($noPersonaCount > 0)
    <div class="mb-8 bg-amber-900/10 border border-amber-700/30 rounded-xl px-5 py-4">
        <div class="flex items-start gap-3">
            <svg class="w-4 h-4 text-amber-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-amber-300 text-sm font-medium">{{ $noPersonaCount }} deployment{{ $noPersonaCount === 1 ? '' : 's' }} without a persona</p>
                <p class="text-amber-700 text-xs mt-0.5">These deployments are using generic rules only. Consider prompting these tenants to complete the Use Case step.</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Add new persona --}}
    <div x-data="{ open: false }" class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <button @click="open = !open"
                class="w-full px-5 py-4 flex items-center justify-between text-left">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg border border-gray-700 flex items-center justify-center text-gray-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div>
                    <p class="text-white text-sm font-medium">Add new persona</p>
                    <p class="text-gray-600 text-xs">New market segment or use case for {{ $worker->name }}</p>
                </div>
            </div>
            <svg class="w-4 h-4 text-gray-600 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div x-show="open" x-cloak class="border-t border-gray-800 px-5 py-5">
            <form method="POST" action="{{ route('admin.workers.personas.store', $slug) }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-gray-500 text-xs block mb-1">Key <span class="text-gray-700">(slug, e.g. real_estate)</span></label>
                        <input type="text" name="key" required placeholder="real_estate"
                               class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                    </div>
                    <div>
                        <label class="text-gray-500 text-xs block mb-1">Label</label>
                        <input type="text" name="label" required placeholder="Real Estate Agency"
                               class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                    </div>
                    <div>
                        <label class="text-gray-500 text-xs block mb-1">Icon</label>
                        <select name="icon" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                            @foreach($iconOptions as $ic)
                            <option value="{{ $ic }}">{{ $ic }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-gray-500 text-xs block mb-1">Tagline</label>
                    <input type="text" name="tagline" required placeholder="Leases, listings, and vendor contracts"
                           class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                </div>

                <div>
                    <label class="text-gray-500 text-xs block mb-1">Detail</label>
                    <textarea name="detail" rows="2" required
                              placeholder="Describe what this persona does and why it matters..."
                              class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-gray-500 text-xs block mb-1">Asset types <span class="text-gray-700">(key: Label per line)</span></label>
                        <textarea name="asset_types" rows="4"
                                  placeholder="lease: Lease Agreement&#10;listing: Listing Contract&#10;other: Other"
                                  class="w-full bg-gray-800 text-white text-xs font-mono rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="text-gray-500 text-xs block mb-1">Examples <span class="text-gray-700">(one per line)</span></label>
                        <textarea name="examples" rows="4"
                                  placeholder="Lease renewals&#10;Listing expirations&#10;Vendor contracts"
                                  class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="text-gray-500 text-xs block mb-1">Client noun</label>
                        <input type="text" name="client_noun" value="client"
                               class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                    </div>
                    <div>
                        <label class="text-gray-500 text-xs block mb-1">Asset noun</label>
                        <input type="text" name="asset_noun" value="asset"
                               class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                    </div>
                    <div>
                        <label class="text-gray-500 text-xs block mb-1">Example client</label>
                        <input type="text" name="example_client" placeholder="Sunrise Realty"
                               class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                    </div>
                    <div>
                        <label class="text-gray-500 text-xs block mb-1">Example asset</label>
                        <input type="text" name="example_asset" placeholder="Office Lease — Suite 400"
                               class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                    </div>
                </div>

                <p class="text-gray-700 text-xs">Nudge emails and capture rules can be added after creation via Edit and the Rules tab.</p>

                <button type="submit"
                        class="text-sm px-5 py-2.5 rounded-lg font-semibold transition"
                        style="background:var(--accent);color:#111">
                    Create persona
                </button>
            </form>
        </div>
    </div>

</x-app-layout>

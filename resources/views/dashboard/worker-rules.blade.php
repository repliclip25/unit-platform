<x-app-layout title="{{ $dep->name }} · Rules">

    @include('partials.worker-subnav')

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-900/40 border border-red-700/50 text-red-300 rounded-xl px-5 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    @php
        $priorityBadge = [
            'Critical' => 'bg-red-900/60 text-red-300 border border-red-700/40',
            'High'     => 'bg-amber-900/60 text-amber-300 border border-amber-700/40',
            'Medium'   => 'bg-gray-800 text-gray-400 border border-gray-700',
            'Low'      => 'bg-gray-900 text-gray-600 border border-gray-800',
        ];
        $activeKey  = $dep->persona;
        $activeP    = $activeKey && isset($personas[$activeKey]) ? $personas[$activeKey] : null;
        $myRules    = $activeKey ? ($rulesByPersona[$activeKey] ?? []) : [];
        $myDiff     = $activeKey ? ($diffByPersona[$activeKey] ?? ['stale'=>[],'orphaned'=>[],'missing'=>[]]) : [];
        $myIssues   = !empty($myDiff['stale']) || !empty($myDiff['orphaned']) || !empty($myDiff['missing']);
    @endphp

    {{-- Persona panel --}}
    <div x-data="{ editPersona: false }" class="mb-5 bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <p class="text-gray-500 text-xs mb-0.5">Active use case</p>
            @if($activeP)
                <p class="text-white text-sm font-semibold">{{ $activeP['label'] }}</p>
                <p class="text-gray-500 text-xs mt-0.5">{{ $activeP['tagline'] }}</p>
            @else
                <p class="text-amber-400 text-sm font-semibold">No use case selected</p>
                <p class="text-gray-600 text-xs mt-0.5">Select one below to unlock your rules.</p>
            @endif
        </div>
        @if($activeP)
        <button @click="editPersona = !editPersona"
                class="text-xs px-3 py-2 rounded-lg font-medium transition shrink-0"
                style="background:var(--bg-raised);color:var(--text-secondary);border:1px solid var(--border)">
            <span x-text="editPersona ? 'Cancel' : 'Change use case'"></span>
        </button>
        @endif

        <div x-show="editPersona" x-cloak class="w-full mt-3 pt-3 border-t border-gray-800">
            <form method="POST" action="{{ route('workers.persona', $dep->id) }}" class="space-y-3">
                @csrf @method('PATCH')
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach($personas as $key => $p)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="persona" value="{{ $key }}"
                               {{ $activeKey === $key ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="peer-checked:border-yellow-400/70 peer-checked:bg-yellow-400/5 border border-gray-700 rounded-xl px-4 py-3 transition">
                            <p class="text-white text-xs font-semibold mb-0.5">{{ $p['label'] }}</p>
                            <p class="text-gray-500 text-xs leading-relaxed">{{ $p['tagline'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                <p class="text-gray-600 text-xs">Changing use case will re-seed your rules from the latest definition. Custom rules you added will be removed.</p>
                <button type="submit" class="text-xs px-4 py-2 rounded-lg font-semibold transition" style="background:var(--accent);color:#111">
                    Save use case
                </button>
            </form>
        </div>

        {{-- No persona: show picker inline always --}}
        @if(!$activeP)
        <div class="w-full mt-3 pt-3 border-t border-gray-800">
            <form method="POST" action="{{ route('workers.persona', $dep->id) }}" class="space-y-3">
                @csrf @method('PATCH')
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach($personas as $key => $p)
                    <label class="relative cursor-pointer">
                        <input type="radio" name="persona" value="{{ $key }}" class="sr-only peer">
                        <div class="peer-checked:border-yellow-400/70 peer-checked:bg-yellow-400/5 border border-gray-700 rounded-xl px-4 py-3 transition">
                            <p class="text-white text-xs font-semibold mb-0.5">{{ $p['label'] }}</p>
                            <p class="text-gray-500 text-xs leading-relaxed">{{ $p['tagline'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                <button type="submit" class="text-xs px-4 py-2 rounded-lg font-semibold transition" style="background:var(--accent);color:#111">
                    Set use case
                </button>
            </form>
        </div>
        @endif
    </div>

    @if(!$activeP)
    {{-- Gate: no persona set yet --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-12 text-center">
        <p class="text-gray-500 text-sm">Select a use case above to see your rules.</p>
    </div>

    @else

    {{-- Stale rule notice --}}
    @if($myIssues)
    <div class="mb-5 flex items-start justify-between gap-4 bg-amber-900/10 border border-amber-700/30 rounded-xl px-5 py-4">
        <div class="flex items-start gap-3">
            <svg class="w-4 h-4 text-amber-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-amber-300 text-sm font-medium">Your rules are out of date</p>
                <p class="text-amber-700 text-xs mt-0.5">
                    @if(!empty($myDiff['stale'])) {{ count($myDiff['stale']) }} stale · @endif
                    @if(!empty($myDiff['orphaned'])) {{ count($myDiff['orphaned']) }} orphaned · @endif
                    @if(!empty($myDiff['missing'])) {{ count($myDiff['missing']) }} new rule{{ count($myDiff['missing']) === 1 ? '' : 's' }} available · @endif
                    Resetting takes 2 seconds and keeps platform rules intact.
                </p>
            </div>
        </div>
        <form method="POST" action="{{ route('workers.rules.reset', $dep->id) }}" class="shrink-0">
            @csrf
            <button class="text-xs px-3 py-2 rounded-lg font-semibold transition whitespace-nowrap"
                    style="background:rgba(var(--accent-rgb),0.15);color:var(--accent-text)">
                Reset to latest
            </button>
        </form>
    </div>
    @endif

    <div x-data="{ tab: 'mine' }" class="space-y-6">

        {{-- Two-tab bar: My rules / Platform defaults --}}
        <div class="flex items-center gap-1 border-b border-gray-800 pb-0">
            <button @click="tab = 'mine'"
                    class="px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors"
                    :class="tab === 'mine' ? 'border-yellow-400 text-white' : 'border-transparent text-gray-500 hover:text-gray-300'">
                {{ $activeP['label'] }}
                @if(count($myRules))
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full"
                      :class="tab === 'mine' ? 'bg-yellow-400/20 text-yellow-300' : 'bg-gray-800 text-gray-600'">
                    {{ count($myRules) }}
                </span>
                @endif
            </button>
            <button @click="tab = 'platform'"
                    class="px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors"
                    :class="tab === 'platform' ? 'border-yellow-400 text-white' : 'border-transparent text-gray-500 hover:text-gray-300'">
                Platform defaults
                @if(count($platformRules))
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-gray-800 text-gray-600">{{ count($platformRules) }}</span>
                @endif
            </button>
        </div>

        {{-- My rules tab --}}
        <div x-show="tab === 'mine'" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Rules list --}}
                <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-xl">
                    <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-white text-sm font-semibold">{{ $activeP['label'] }} rules</h3>
                            <p class="text-gray-500 text-xs mt-0.5">{{ $activeP['tagline'] }}</p>
                        </div>
                        @if(count($myRules))
                        <span class="text-xs text-gray-600">{{ count($myRules) }} rule{{ count($myRules) === 1 ? '' : 's' }}</span>
                        @endif
                    </div>
                    <div class="divide-y divide-gray-800/60">
                        @forelse($myRules as $rule)
                        <div x-data="{ editing: false }" class="px-5 py-4">
                            {{-- View mode --}}
                            <div x-show="!editing" class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                        <span class="font-mono text-xs" style="color:var(--accent-text)">{{ $rule->rule_id }}</span>
                                        <span class="text-xs px-1.5 py-0.5 rounded {{ $priorityBadge[$rule->priority] ?? $priorityBadge['Medium'] }}">
                                            {{ $rule->priority }}
                                        </span>
                                        @if(!$rule->active)
                                            <span class="text-xs text-gray-700">· disabled</span>
                                        @endif
                                    </div>
                                    <p class="text-gray-300 text-xs leading-relaxed">
                                        <span class="text-gray-600">When</span> {{ $rule->condition }}
                                    </p>
                                    <p class="text-gray-500 text-xs mt-1 leading-relaxed">
                                        <span class="text-gray-600">→</span> {{ $rule->action }}
                                    </p>
                                    @if($rule->notes)
                                    <p class="text-gray-700 text-xs mt-1.5 italic">{{ $rule->notes }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <button @click="editing = true" class="text-gray-600 hover:text-gray-300 text-xs transition-colors">Edit</button>
                                    <form method="POST" action="{{ route('workers.rules.destroy', [$dep->id, $rule->id]) }}">
                                        @csrf @method('DELETE')
                                        <button class="text-gray-700 hover:text-red-400 text-xs transition-colors">Remove</button>
                                    </form>
                                </div>
                            </div>

                            {{-- Edit mode --}}
                            <div x-show="editing" x-cloak>
                                <form method="POST" action="{{ route('workers.rules.update', [$dep->id, $rule->id]) }}" class="space-y-3">
                                    @csrf @method('PATCH')
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="font-mono text-xs text-gray-600">{{ $rule->rule_id }}</span>
                                        <select name="priority" class="bg-gray-800 text-white text-xs rounded-lg px-2 py-1 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                                            @foreach(['Critical','High','Medium','Low'] as $pri)
                                            <option {{ $rule->priority === $pri ? 'selected' : '' }}>{{ $pri }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-gray-600 text-xs block mb-1">When…</label>
                                        <textarea name="condition" rows="2" required
                                                  class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none">{{ $rule->condition }}</textarea>
                                    </div>
                                    <div>
                                        <label class="text-gray-600 text-xs block mb-1">Then…</label>
                                        <textarea name="action" rows="2" required
                                                  class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none">{{ $rule->action }}</textarea>
                                    </div>
                                    <div>
                                        <label class="text-gray-600 text-xs block mb-1">Notes</label>
                                        <input type="text" name="notes" value="{{ $rule->notes }}"
                                               class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                                    </div>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="approval_required" value="1"
                                               {{ $rule->approval_required ? 'checked' : '' }}
                                               class="rounded border-gray-700 bg-gray-800 text-yellow-400">
                                        <span class="text-gray-500 text-xs">Require human approval</span>
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <button type="submit" class="text-xs px-4 py-2 rounded-lg font-semibold transition" style="background:var(--accent);color:#111">Save</button>
                                        <button type="button" @click="editing = false" class="text-xs text-gray-600 hover:text-gray-400 transition">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @empty
                        <div class="px-5 py-10 text-center">
                            <p class="text-gray-600 text-sm">No rules yet.</p>
                            <p class="text-gray-700 text-xs mt-1">Add one using the form →</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Add rule form --}}
                <div class="bg-gray-900 border border-gray-800 rounded-xl h-fit">
                    <div class="px-5 py-4 border-b border-gray-800">
                        <h3 class="text-white text-sm font-semibold">Add rule</h3>
                        <p class="text-gray-600 text-xs mt-0.5">Scoped to {{ $activeP['label'] }}</p>
                    </div>
                    <form method="POST" action="{{ route('workers.rules.store', $dep->id) }}" class="px-5 py-4 space-y-3">
                        @csrf
                        <input type="hidden" name="persona" value="{{ $activeKey }}">
                        <div>
                            <label class="text-gray-400 text-xs block mb-1">Priority</label>
                            <select name="priority" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                                <option>Critical</option>
                                <option>High</option>
                                <option selected>Medium</option>
                                <option>Low</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-gray-400 text-xs block mb-1">When… <span class="text-gray-600">(condition)</span></label>
                            <textarea name="condition" rows="3" required
                                      placeholder="e.g. {{ $activeP['examples'][0] ?? 'renewal notice received' }}"
                                      class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="text-gray-400 text-xs block mb-1">Then… <span class="text-gray-600">(action)</span></label>
                            <textarea name="action" rows="3" required
                                      placeholder="e.g. Log + draft renewal reminder"
                                      class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="text-gray-400 text-xs block mb-1">Notes <span class="text-gray-600">(optional)</span></label>
                            <input type="text" name="notes" placeholder="e.g. Never auto-send"
                                   class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="approval_required" value="1" checked class="rounded border-gray-700 bg-gray-800 text-yellow-400">
                            <span class="text-gray-400 text-xs">Require human approval before sending</span>
                        </label>
                        <button type="submit"
                                class="w-full text-sm font-semibold rounded-lg py-2.5 transition"
                                style="background:var(--accent);color:#111">
                            Add rule
                        </button>
                    </form>
                </div>

            </div>
        </div>

        {{-- Platform defaults tab --}}
        <div x-show="tab === 'platform'" x-cloak>
            <div class="bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-4 border-b border-gray-800">
                    <h3 class="text-white text-sm font-semibold">Platform defaults</h3>
                    <p class="text-gray-500 text-xs mt-0.5">These rules apply to all use cases and cannot be removed. They form the baseline every deployment starts with.</p>
                </div>
                <div class="divide-y divide-gray-800/60">
                    @forelse($platformRules as $rule)
                    <div class="px-5 py-4">
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span class="font-mono text-xs text-gray-600">{{ $rule->rule_id }}</span>
                            <span class="text-xs px-1.5 py-0.5 rounded {{ $priorityBadge[$rule->priority] ?? $priorityBadge['Medium'] }}">{{ $rule->priority }}</span>
                            <span class="text-xs bg-gray-800/60 text-gray-600 px-1.5 py-0.5 rounded border border-gray-800">Platform</span>
                        </div>
                        <p class="text-gray-400 text-xs leading-relaxed"><span class="text-gray-600">When</span> {{ $rule->condition }}</p>
                        <p class="text-gray-500 text-xs mt-1"><span class="text-gray-600">→</span> {{ $rule->action }}</p>
                        @if($rule->notes)<p class="text-gray-700 text-xs mt-1.5 italic">{{ $rule->notes }}</p>@endif
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-gray-600 text-sm">No platform defaults for this deployment.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
    @endif

</x-app-layout>

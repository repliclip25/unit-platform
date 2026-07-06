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
    @endphp

    <div x-data="{ tab: '{{ $activePersona ?? 'platform' }}' }" class="space-y-6">

        {{-- Tab bar --}}
        <div class="flex items-center gap-1 border-b border-gray-800 pb-0 overflow-x-auto">
            @foreach($personas as $key => $p)
            <button @click="tab = '{{ $key }}'"
                    class="px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors"
                    :class="tab === '{{ $key }}'
                        ? 'border-yellow-400 text-white'
                        : 'border-transparent text-gray-500 hover:text-gray-300'">
                {{ $p['label'] }}
                @php $count = count($rulesByPersona[$key] ?? []); @endphp
                @if($count)
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full"
                      :class="tab === '{{ $key }}' ? 'bg-yellow-400/20 text-yellow-300' : 'bg-gray-800 text-gray-600'">
                    {{ $count }}
                </span>
                @endif
            </button>
            @endforeach
            {{-- Platform tab always last --}}
            <button @click="tab = 'platform'"
                    class="px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors"
                    :class="tab === 'platform'
                        ? 'border-yellow-400 text-white'
                        : 'border-transparent text-gray-500 hover:text-gray-300'">
                Platform defaults
                @if(count($platformRules))
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-gray-800 text-gray-600">{{ count($platformRules) }}</span>
                @endif
            </button>
        </div>

        {{-- Persona tabs --}}
        @foreach($personas as $key => $p)
        <div x-show="tab === '{{ $key }}'" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Rules list --}}
            <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-white text-sm font-semibold">{{ $p['label'] }} rules</h3>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $p['tagline'] }}</p>
                    </div>
                    @if(!empty($rulesByPersona[$key]))
                    <span class="text-xs text-gray-600">{{ count($rulesByPersona[$key]) }} rule{{ count($rulesByPersona[$key]) === 1 ? '' : 's' }}</span>
                    @endif
                </div>
                <div class="divide-y divide-gray-800/60">
                    @forelse($rulesByPersona[$key] ?? [] as $rule)
                    <div class="px-5 py-4 flex items-start justify-between gap-4">
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
                        <form method="POST" action="{{ route('workers.rules.destroy', [$dep->id, $rule->id]) }}" class="shrink-0">
                            @csrf @method('DELETE')
                            <button class="text-gray-700 hover:text-red-400 text-xs transition-colors">Remove</button>
                        </form>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-gray-600 text-sm">No rules for this persona yet.</p>
                        <p class="text-gray-700 text-xs mt-1">Add one using the form →</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Add rule form --}}
            <div class="bg-gray-900 border border-gray-800 rounded-xl h-fit">
                <div class="px-5 py-4 border-b border-gray-800">
                    <h3 class="text-white text-sm font-semibold">Add rule</h3>
                    <p class="text-gray-600 text-xs mt-0.5">Scoped to {{ $p['label'] }}</p>
                </div>
                <form method="POST" action="{{ route('workers.rules.store', $dep->id) }}" class="px-5 py-4 space-y-3">
                    @csrf
                    <input type="hidden" name="persona" value="{{ $key }}">

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
                                  placeholder="e.g. {{ $p['examples'][0] ?? 'renewal notice received' }}"
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
                        Add rule to {{ $p['label'] }}
                    </button>
                </form>
            </div>

        </div>
        @endforeach

        {{-- Platform defaults tab --}}
        <div x-show="tab === 'platform'" x-cloak>
            <div class="bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-4 border-b border-gray-800">
                    <h3 class="text-white text-sm font-semibold">Platform defaults</h3>
                    <p class="text-gray-500 text-xs mt-0.5">These rules apply to all personas and cannot be removed from the UI. They form the baseline every deployment starts with.</p>
                </div>
                <div class="divide-y divide-gray-800/60">
                    @forelse($platformRules as $rule)
                    <div class="px-5 py-4">
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span class="font-mono text-xs text-gray-600">{{ $rule->rule_id }}</span>
                            <span class="text-xs px-1.5 py-0.5 rounded {{ $priorityBadge[$rule->priority] ?? $priorityBadge['Medium'] }}">
                                {{ $rule->priority }}
                            </span>
                            <span class="text-xs bg-gray-800/60 text-gray-600 px-1.5 py-0.5 rounded border border-gray-800">Platform</span>
                        </div>
                        <p class="text-gray-400 text-xs leading-relaxed">
                            <span class="text-gray-600">When</span> {{ $rule->condition }}
                        </p>
                        <p class="text-gray-500 text-xs mt-1">
                            <span class="text-gray-600">→</span> {{ $rule->action }}
                        </p>
                        @if($rule->notes)
                        <p class="text-gray-700 text-xs mt-1.5 italic">{{ $rule->notes }}</p>
                        @endif
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

</x-app-layout>

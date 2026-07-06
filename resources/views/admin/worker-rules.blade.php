<x-app-layout title="Platform Rules — {{ $worker->name ?? $slug }}">

    @include('partials.admin-worker-subnav', ['slug' => $slug, 'active' => 'rules'])

    @if(session('success'))
        <div class="mb-4 bg-green-900/40 border border-green-700/50 text-green-300 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
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

    <div x-data="{ tab: '{{ request('persona') ?? $activePersona ?? 'platform' }}' }" class="space-y-6">

        {{-- Tab bar --}}
        <div class="flex items-center gap-1 border-b border-gray-800 pb-0 overflow-x-auto">
            @foreach($personas as $key => $p)
            @php
                $count    = count($rulesByPersona[$key] ?? []);
                $hasIssue = !empty($diffByPersona[$key]['stale']) || !empty($diffByPersona[$key]['orphaned']) || !empty($diffByPersona[$key]['missing']);
            @endphp
            <button @click="tab = '{{ $key }}'"
                    class="px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors relative"
                    :class="tab === '{{ $key }}' ? 'border-yellow-400 text-white' : 'border-transparent text-gray-500 hover:text-gray-300'">
                {{ $p['label'] }}
                @if($hasIssue)
                <span class="ml-1 inline-block w-1.5 h-1.5 rounded-full bg-amber-400 align-middle -mt-1"></span>
                @elseif($count)
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full"
                      :class="tab === '{{ $key }}' ? 'bg-yellow-400/20 text-yellow-300' : 'bg-gray-800 text-gray-600'">{{ $count }}</span>
                @endif
            </button>
            @endforeach
            <button @click="tab = 'platform'"
                    class="px-4 py-2.5 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors"
                    :class="tab === 'platform' ? 'border-yellow-400 text-white' : 'border-transparent text-gray-500 hover:text-gray-300'">
                Universal
                @if(count($platformRules))
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-gray-800 text-gray-600">{{ count($platformRules) }}</span>
                @endif
            </button>
        </div>

        {{-- Persona tabs --}}
        @foreach($personas as $key => $p)
        @php
            $diff     = $diffByPersona[$key] ?? ['stale' => [], 'orphaned' => [], 'missing' => []];
            $hasIssues= !empty($diff['stale']) || !empty($diff['orphaned']) || !empty($diff['missing']);
        @endphp
        <div x-show="tab === '{{ $key }}'" x-cloak class="space-y-4">

            {{-- Sync status banner --}}
            @if($hasIssues)
            <div class="flex items-start justify-between gap-4 bg-amber-900/10 border border-amber-700/30 rounded-xl px-5 py-4">
                <div class="flex items-start gap-3">
                    <svg class="w-4 h-4 text-amber-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-amber-300 text-sm font-medium">Platform master rules out of sync with contract</p>
                        <p class="text-amber-700 text-xs mt-0.5">
                            @if(!empty($diff['stale'])) {{ count($diff['stale']) }} stale (condition or action changed). @endif
                            @if(!empty($diff['orphaned'])) {{ count($diff['orphaned']) }} orphaned (no longer in contract). @endif
                            @if(!empty($diff['missing'])) {{ count($diff['missing']) }} missing (in contract, not seeded). @endif
                            Existing tenant deployments are unaffected.
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.workers.rules.sync', $slug) }}" class="shrink-0">
                    @csrf
                    <input type="hidden" name="persona" value="{{ $key }}">
                    <button class="text-xs px-3 py-2 rounded-lg font-semibold transition whitespace-nowrap"
                            style="background:rgba(var(--accent-rgb),0.15);color:var(--accent-text)">
                        Sync from contract
                    </button>
                </form>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Rules list --}}
                <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-xl">
                    <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-white text-sm font-semibold">{{ $p['label'] }} — platform master rules</h3>
                            <p class="text-gray-500 text-xs mt-0.5">Copied to every new {{ $p['label'] }} deployment. Editing here does not update existing deployments.</p>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-800/60">
                        @forelse($rulesByPersona[$key] ?? [] as $rule)
                        @php $isStale = in_array($rule->rule_id, $diff['stale']) || in_array($rule->rule_id, $diff['orphaned']); @endphp
                        <div class="px-5 py-4 flex items-start justify-between gap-4 {{ $isStale ? 'bg-amber-900/5' : '' }}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                    <span class="font-mono text-xs {{ $isStale ? 'text-amber-400' : '' }}" style="{{ $isStale ? '' : 'color:var(--accent-text)' }}">{{ $rule->rule_id }}</span>
                                    <span class="text-xs px-1.5 py-0.5 rounded {{ $priorityBadge[$rule->priority] ?? $priorityBadge['Medium'] }}">{{ $rule->priority }}</span>
                                    @if($isStale)
                                    <span class="text-xs bg-amber-900/40 text-amber-400 border border-amber-700/30 px-1.5 py-0.5 rounded">
                                        {{ in_array($rule->rule_id, $diff['orphaned']) ? 'Orphaned' : 'Stale' }}
                                    </span>
                                    @endif
                                </div>
                                <p class="text-gray-300 text-xs leading-relaxed"><span class="text-gray-600">When</span> {{ $rule->condition }}</p>
                                <p class="text-gray-500 text-xs mt-1"><span class="text-gray-600">→</span> {{ $rule->action }}</p>
                                @if($rule->notes)<p class="text-gray-700 text-xs mt-1.5 italic">{{ $rule->notes }}</p>@endif
                            </div>
                            <form method="POST" action="{{ route('admin.workers.rules.destroy', [$slug, $rule->id]) }}" class="shrink-0">
                                @csrf @method('DELETE')
                                <button class="text-gray-700 hover:text-red-400 text-xs transition-colors">Remove</button>
                            </form>
                        </div>
                        @empty
                        <div class="px-5 py-10 text-center">
                            <p class="text-gray-600 text-sm">No platform master rules for this persona.</p>
                            <p class="text-gray-700 text-xs mt-1">Sync from contract to seed them, or add manually →</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Add rule --}}
                <div class="bg-gray-900 border border-gray-800 rounded-xl h-fit">
                    <div class="px-5 py-4 border-b border-gray-800">
                        <h3 class="text-white text-sm font-semibold">Add platform rule</h3>
                        <p class="text-gray-600 text-xs mt-0.5">Scoped to {{ $p['label'] }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.workers.rules.store', $slug) }}" class="px-5 py-4 space-y-3">
                        @csrf
                        <input type="hidden" name="persona" value="{{ $key }}">
                        <div>
                            <label class="text-gray-400 text-xs block mb-1">Priority</label>
                            <select name="priority" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                                <option>Critical</option><option>High</option><option selected>Medium</option><option>Low</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-gray-400 text-xs block mb-1">When… <span class="text-gray-600">(condition)</span></label>
                            <textarea name="condition" rows="3" required placeholder="e.g. {{ $p['examples'][0] ?? 'renewal notice received' }}"
                                      class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="text-gray-400 text-xs block mb-1">Then… <span class="text-gray-600">(action)</span></label>
                            <textarea name="action" rows="3" required placeholder="e.g. Log + draft renewal reminder"
                                      class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="text-gray-400 text-xs block mb-1">Notes <span class="text-gray-600">(optional)</span></label>
                            <input type="text" name="notes" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400/50">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="approval_required" value="1" checked class="rounded border-gray-700 bg-gray-800">
                            <span class="text-gray-400 text-xs">Require human approval</span>
                        </label>
                        <button type="submit" class="w-full text-sm font-semibold rounded-lg py-2.5 transition" style="background:var(--accent);color:#111">
                            Add to {{ $p['label'] }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Universal (platform null-persona) tab --}}
        <div x-show="tab === 'platform'" x-cloak>
            <div class="bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-4 border-b border-gray-800">
                    <h3 class="text-white text-sm font-semibold">Universal rules</h3>
                    <p class="text-gray-500 text-xs mt-0.5">Applied to every deployment regardless of persona. No persona column set on these rows.</p>
                </div>
                <div class="divide-y divide-gray-800/60">
                    @forelse($platformRules as $rule)
                    <div class="px-5 py-4 flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                <span class="font-mono text-xs text-gray-500">{{ $rule->rule_id }}</span>
                                <span class="text-xs px-1.5 py-0.5 rounded {{ $priorityBadge[$rule->priority] ?? $priorityBadge['Medium'] }}">{{ $rule->priority }}</span>
                                <span class="text-xs bg-gray-800 text-gray-600 px-1.5 py-0.5 rounded border border-gray-800">Universal</span>
                            </div>
                            <p class="text-gray-400 text-xs leading-relaxed"><span class="text-gray-600">When</span> {{ $rule->condition }}</p>
                            <p class="text-gray-500 text-xs mt-1"><span class="text-gray-600">→</span> {{ $rule->action }}</p>
                            @if($rule->notes)<p class="text-gray-700 text-xs mt-1.5 italic">{{ $rule->notes }}</p>@endif
                        </div>
                        <form method="POST" action="{{ route('admin.workers.rules.destroy', [$slug, $rule->id]) }}" class="shrink-0">
                            @csrf @method('DELETE')
                            <button class="text-gray-700 hover:text-red-400 text-xs transition-colors">Remove</button>
                        </form>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-gray-600 text-sm">No universal rules.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</x-app-layout>

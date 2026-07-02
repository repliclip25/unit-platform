<x-app-layout title="{{ $dep->name }} · Rules">

    @include('partials.worker-subnav')

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-xl">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="text-white text-sm font-semibold">Rules for {{ $dep->name }}</h3>
                <p class="text-gray-500 text-xs mt-0.5">Platform default rules ship with every AVA deployment. Add custom rules below to extend behaviour.</p>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($rules as $rule)
                    <div class="px-5 py-4 flex items-start justify-between">
                        <div class="flex-1 mr-4">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-brand text-xs font-mono">{{ $rule->rule_id }}</span>
                                <span class="text-xs px-1.5 py-0.5 rounded {{ $rule->priority === 'Critical' ? 'bg-red-900 text-red-300' : ($rule->priority === 'High' ? 'bg-amber-900 text-amber-300' : 'bg-gray-800 text-gray-400') }}">{{ $rule->priority }}</span>
                                @if($rule->is_platform)
                                    <span class="text-xs bg-gray-800 text-gray-600 px-1.5 py-0.5 rounded">Platform default</span>
                                @endif
                                @if(!$rule->active)<span class="text-xs text-gray-600">· disabled</span>@endif
                            </div>
                            <p class="text-gray-300 text-xs">{{ $rule->condition }}</p>
                            <p class="text-gray-500 text-xs mt-1">→ {{ $rule->action }}</p>
                            @if($rule->notes)<p class="text-gray-600 text-xs mt-1 italic">{{ $rule->notes }}</p>@endif
                        </div>
                        @if(!$rule->is_platform)
                            <form method="POST" action="{{ route('workers.rules.destroy', [$dep->id, $rule->id]) }}">
                                @csrf @method('DELETE')
                                <button class="text-gray-600 hover:text-red-400 text-xs">Remove</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-gray-600 text-sm">No rules yet. Add rules to guide how this worker thinks.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl h-fit">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="text-white text-sm font-semibold">Add Rule</h3>
            </div>
            <form method="POST" action="{{ route('workers.rules.store', $dep->id) }}" class="px-5 py-4 space-y-3">
                @csrf
                <div><label class="text-gray-400 text-xs block mb-1">Rule ID <span class="text-gray-600">(optional — auto-assigned)</span></label><input type="text" name="rule_id" placeholder="e.g. R-007" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Priority</label>
                    <select name="priority" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                        <option>Critical</option><option>High</option><option selected>Medium</option><option>Low</option>
                    </select>
                </div>
                <div><label class="text-gray-400 text-xs block mb-1">When… (condition)</label><textarea name="condition" rows="3" required placeholder="e.g. SSL certificate expires within 14 days" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></textarea></div>
                <div><label class="text-gray-400 text-xs block mb-1">Then… (action)</label><textarea name="action" rows="3" required placeholder="e.g. Flag as Critical priority and include renewal link in draft" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></textarea></div>
                <button type="submit" class="w-full bg-brand hover:bg-brand-deep text-brand-text text-sm rounded-lg py-2 transition">Add Rule</button>
            </form>
        </div>

    </div>

</x-app-layout>

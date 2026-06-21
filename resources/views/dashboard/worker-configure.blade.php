<x-app-layout title="Configure — {{ $dep->name }}">

    @include('partials.worker-subnav')

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @php
        $config         = json_decode($dep->config, true) ?? [];
        $deployFields   = $contract?->deploymentFields() ?? [];
        $currentModel   = $config['ai_model'] ?? 'claude-sonnet-4-6';
        $catalog        = \App\Platform\Services\LLM\ModelCatalog::all();

        $providerColors = [
            'anthropic' => ['color' => '#F5C100', 'bg' => 'rgba(245,193,0,0.1)',  'border' => 'rgba(245,193,0,0.3)'],
            'openai'    => ['color' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)', 'border' => 'rgba(16,185,129,0.3)'],
            'kimi'      => ['color' => '#06b6d4', 'bg' => 'rgba(6,182,212,0.1)',  'border' => 'rgba(6,182,212,0.3)'],
            'google'    => ['color' => '#a855f7', 'bg' => 'rgba(168,85,247,0.1)', 'border' => 'rgba(168,85,247,0.3)'],
        ];
        $tierColors = [
            'Fast'      => ['bg' => 'rgba(6,182,212,0.15)',  'color' => '#67e8f9'],
            'Balanced'  => ['bg' => 'rgba(245,193,0,0.15)',  'color' => '#fde68a'],
            'Powerful'  => ['bg' => 'rgba(168,85,247,0.15)','color' => '#c4b5fd'],
            'Reasoning' => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#fca5a5'],
        ];
    @endphp

    <div class="grid grid-cols-3 gap-6">

        {{-- Left: deployment config --}}
        <div class="col-span-2 space-y-6">

            {{-- Deployment Settings --}}
            <div class="bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-4 border-b border-gray-800">
                    <h3 class="text-white text-sm font-semibold">Deployment Settings</h3>
                    <p class="text-gray-500 text-xs mt-0.5">Core configuration for this worker instance</p>
                </div>
                <form method="POST" action="{{ route('workers.config', $dep->id) }}" class="px-5 py-5 space-y-4">
                    @csrf @method('PATCH')

                    <div>
                        <label class="text-gray-400 text-xs block mb-1">Deployment Name</label>
                        <input type="text" name="name" value="{{ $dep->name }}" required
                               class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-brand">
                        <p class="text-gray-600 text-xs mt-1">A friendly name to identify this deployment in your dashboard.</p>
                    </div>

                    {{-- Contract-driven deployment fields --}}
                    @foreach($deployFields as $field)
                    <div>
                        <label class="text-gray-400 text-xs block mb-1">
                            {{ $field['label'] }}
                            @if(!empty($field['hint']))
                                <span class="text-gray-600 ml-1">— {{ $field['hint'] }}</span>
                            @endif
                        </label>
                        <input type="text"
                               name="{{ $field['key'] }}"
                               value="{{ $field['key'] === 'capture_keywords'
                                   ? implode(', ', $config['capture_keywords'] ?? [])
                                   : ($config[$field['key']] ?? $field['default'] ?? '') }}"
                               placeholder="{{ $field['placeholder'] ?? '' }}"
                               class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-brand">
                    </div>
                    @endforeach

                    <div class="pt-2">
                        <button type="submit"
                                class="bg-brand hover:bg-brand-deep text-brand-text text-sm font-medium rounded-lg px-5 py-2.5 transition">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>

            {{-- Danger zone --}}
            <div class="bg-gray-900 border border-red-900/40 rounded-xl">
                <div class="px-5 py-4 border-b border-red-900/40">
                    <h3 class="text-red-400 text-sm font-semibold">Danger Zone</h3>
                    <p class="text-gray-500 text-xs mt-0.5">These actions are permanent and cannot be undone.</p>
                </div>
                <div class="px-5 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-white text-sm font-medium">Remove this worker</p>
                        <p class="text-gray-500 text-xs mt-0.5">Deletes the deployment and all associated configuration. Transaction history is preserved.</p>
                    </div>
                    <form method="POST" action="{{ route('workers.destroy', $dep->id) }}"
                          onsubmit="return confirm('Permanently remove {{ addslashes($dep->name) }}? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="text-xs text-red-400 border border-red-800 rounded-lg px-4 py-2 hover:bg-red-900/30 transition">
                            Remove Worker
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- Right: AI model --}}
        <div class="space-y-4">

            <div class="bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-4 border-b border-gray-800">
                    <h3 class="text-white text-sm font-semibold">AI Processing Model</h3>
                    <p class="text-gray-600 text-xs mt-0.5">Choose the model powering this worker's pipeline</p>
                </div>
                <form method="POST" action="{{ route('workers.model', $dep->id) }}" class="px-5 py-5 space-y-5">
                    @csrf @method('PATCH')

                    @foreach($catalog as $providerKey => $provider)
                        @php $pc = $providerColors[$providerKey] ?? $providerColors['anthropic']; @endphp
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-bold px-2 py-0.5 rounded"
                                      style="background:{{ $pc['bg'] }};color:{{ $pc['color'] }};border:1px solid {{ $pc['border'] }}">
                                    {{ $provider['label'] }}
                                </span>
                            </div>
                            <div class="space-y-1.5">
                                @foreach($provider['models'] as $modelId => $m)
                                    @php
                                        $selected = $currentModel === $modelId;
                                        $tc = $tierColors[$m['tier']] ?? $tierColors['Balanced'];
                                    @endphp
                                    <label class="block cursor-pointer">
                                        <input type="radio" name="ai_model" value="{{ $modelId }}"
                                               {{ $selected ? 'checked' : '' }} class="sr-only">
                                        <div class="rounded-xl border px-3.5 py-3 transition-all duration-150 hover:border-gray-600"
                                             style="background:{{ $selected ? $pc['bg'] : 'rgba(255,255,255,0.02)' }};border-color:{{ $selected ? $pc['color'] : '#374151' }}">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="text-xs font-bold" style="color:{{ $selected ? $pc['color'] : '#e5e7eb' }}">{{ $m['name'] }}</span>
                                                        <span class="text-xs px-1.5 py-0.5 rounded-full font-medium"
                                                              style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }}">{{ $m['tier'] }}</span>
                                                        @if(!empty($m['recommended']))
                                                            <span class="text-xs px-1.5 py-0.5 rounded-full" style="background:rgba(255,255,255,0.06);color:#9ca3af">recommended</span>
                                                        @endif
                                                    </div>
                                                    <p class="text-gray-600 text-xs mt-1 font-mono">
                                                        in ${{ number_format($m['cost_in'], 2) }} · out ${{ number_format($m['cost_out'], 2) }} / M tokens
                                                    </p>
                                                </div>
                                                <span class="w-3.5 h-3.5 rounded-full border-2 shrink-0 ml-2 mt-0.5 flex items-center justify-center"
                                                      style="border-color:{{ $selected ? $pc['color'] : '#4b5563' }};background:{{ $selected ? $pc['color'] : 'transparent' }}">
                                                    @if($selected)<span class="w-1 h-1 rounded-full bg-gray-900"></span>@endif
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @if(!$loop->last)<div class="border-t border-gray-800/60"></div>@endif
                    @endforeach

                    @if($customModels->isNotEmpty())
                        <div class="border-t border-gray-800/60"></div>
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-bold px-2 py-0.5 rounded"
                                      style="background:rgba(99,102,241,0.1);color:#a5b4fc;border:1px solid rgba(99,102,241,0.3)">Custom</span>
                            </div>
                            <div class="space-y-1.5">
                                @foreach($customModels as $cm)
                                    @php $selected = $currentModel === $cm->model_id; @endphp
                                    <label class="block cursor-pointer">
                                        <input type="radio" name="ai_model" value="{{ $cm->model_id }}"
                                               {{ $selected ? 'checked' : '' }} class="sr-only">
                                        <div class="rounded-xl border px-3.5 py-3 transition-all duration-150 hover:border-gray-600"
                                             style="background:{{ $selected ? 'rgba(99,102,241,0.1)' : 'rgba(255,255,255,0.02)' }};border-color:{{ $selected ? '#6366f1' : '#374151' }}">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-xs font-bold" style="color:{{ $selected ? '#a5b4fc' : '#e5e7eb' }}">{{ $cm->name }}</span>
                                                        <span class="text-xs px-1.5 py-0.5 rounded-full" style="background:rgba(99,102,241,0.15);color:#a5b4fc">Custom</span>
                                                    </div>
                                                    <p class="text-gray-600 text-xs mt-1 font-mono">{{ $cm->model_identifier }}</p>
                                                </div>
                                                <span class="w-3.5 h-3.5 rounded-full border-2 shrink-0 ml-2 mt-0.5"
                                                      style="border-color:{{ $selected ? '#6366f1' : '#4b5563' }};background:{{ $selected ? '#6366f1' : 'transparent' }}"></span>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button type="submit"
                            class="w-full text-sm font-bold py-2.5 rounded-lg text-gray-900 hover:opacity-90 transition"
                            style="background:#F5C100">
                        Apply Model
                    </button>
                </form>
            </div>

        </div>

    </div>

</x-app-layout>

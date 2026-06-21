<x-app-layout title="Models & API Keys">

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">{{ session('error') }}</div>
    @endif

    <div class="mb-6 flex items-end justify-between">
        <div>
            <h1 class="text-white text-lg font-bold">Models & API Keys</h1>
            <p class="text-gray-500 text-xs mt-0.5">All available models · BYOK keys · custom endpoints</p>
        </div>
        <button onclick="document.getElementById('register-panel').classList.toggle('hidden')"
            class="text-sm font-bold px-4 py-2 rounded-xl text-gray-900 hover:opacity-90 flex items-center gap-2"
            style="background:#F5C100">
            + Register Model
        </button>
    </div>

    @php
        $catalog = \App\Platform\Services\LLM\ModelCatalog::all();

        $providerMeta = [
            'anthropic' => ['color' => '#F5C100', 'bg' => 'rgba(245,193,0,0.1)',   'border' => 'rgba(245,193,0,0.3)',   'keyHint' => 'sk-ant-…',  'docsUrl' => 'https://console.anthropic.com/settings/keys'],
            'openai'    => ['color' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)',  'border' => 'rgba(16,185,129,0.3)',  'keyHint' => 'sk-…',      'docsUrl' => 'https://platform.openai.com/api-keys'],
            'kimi'      => ['color' => '#06b6d4', 'bg' => 'rgba(6,182,212,0.1)',   'border' => 'rgba(6,182,212,0.3)',   'keyHint' => 'sk-…',      'docsUrl' => 'https://platform.moonshot.cn/console/api-keys'],
            'google'    => ['color' => '#a855f7', 'bg' => 'rgba(168,85,247,0.1)',  'border' => 'rgba(168,85,247,0.3)',  'keyHint' => 'AIza…',     'docsUrl' => 'https://aistudio.google.com/app/apikey'],
            'custom'    => ['color' => '#9ca3af', 'bg' => 'rgba(156,163,175,0.1)', 'border' => 'rgba(156,163,175,0.3)', 'keyHint' => 'optional',  'docsUrl' => '#'],
        ];

        $tierColors = [
            'Fast'      => ['bg' => 'rgba(6,182,212,0.15)',  'color' => '#67e8f9'],
            'Balanced'  => ['bg' => 'rgba(245,193,0,0.15)',  'color' => '#fde68a'],
            'Powerful'  => ['bg' => 'rgba(168,85,247,0.15)', 'color' => '#c4b5fd'],
            'Reasoning' => ['bg' => 'rgba(239,68,68,0.15)',  'color' => '#fca5a5'],
            'Custom'    => ['bg' => 'rgba(156,163,175,0.1)', 'color' => '#9ca3af'],
        ];

        // Group workers by model for "in use" display
        $workersByModel = $workers->groupBy('model');
    @endphp

    {{-- ── Register Custom Model (slide-down panel) ──────────────────────────── --}}
    <div id="register-panel" class="hidden mb-6 bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
            <div>
                <p class="text-white font-semibold text-sm">Register Custom Model</p>
                <p class="text-gray-500 text-xs mt-0.5">Any OpenAI-compatible endpoint — self-hosted Llama, Mistral, Groq, Together.ai, Ollama, etc.</p>
            </div>
            <button onclick="document.getElementById('register-panel').classList.add('hidden')" class="text-gray-600 hover:text-white text-lg">✕</button>
        </div>
        <form method="POST" action="{{ route('settings.custom-models.store') }}" class="px-6 py-5">
            @csrf
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-gray-500 text-xs block mb-1.5">Display Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" placeholder="My Llama 3 Server"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-yellow-500 transition">
                </div>
                <div>
                    <label class="text-gray-500 text-xs block mb-1.5">Model Identifier <span class="text-red-500">*</span> <span class="text-gray-700">(sent to API)</span></label>
                    <input type="text" name="model_identifier" placeholder="llama3.2:latest"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:border-yellow-500 transition">
                </div>
                <div>
                    <label class="text-gray-500 text-xs block mb-1.5">Base URL <span class="text-red-500">*</span> <span class="text-gray-700">(OpenAI-compatible)</span></label>
                    <input type="url" name="base_url" placeholder="http://localhost:11434/v1"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:border-yellow-500 transition">
                </div>
                <div>
                    <label class="text-gray-500 text-xs block mb-1.5">API Key <span class="text-gray-700">(optional — leave blank if not required)</span></label>
                    <input type="password" name="api_key" placeholder="sk-… or leave blank"
                        autocomplete="new-password"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:border-yellow-500 transition">
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="text-sm font-bold px-6 py-2 rounded-lg text-gray-900 hover:opacity-90" style="background:#F5C100">
                    Register Model
                </button>
                <p class="text-gray-600 text-xs">After registering, select this model from any worker's configuration page.</p>
            </div>
        </form>
    </div>

    {{-- ── Model Catalog ───────────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        @foreach($catalog as $providerKey => $provider)
            @php $pm = $providerMeta[$providerKey]; @endphp
            <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">

                {{-- Provider header --}}
                <div class="px-5 py-3.5 border-b border-gray-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                             style="background:{{ $pm['bg'] }};border:1px solid {{ $pm['border'] }};color:{{ $pm['color'] }}">
                            {{ strtoupper(substr($providerKey, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-white text-sm font-semibold">{{ $provider['label'] }}</p>
                            <p class="text-gray-600 text-xs">{{ count($provider['models']) }} models</p>
                        </div>
                    </div>

                    {{-- Key status + action --}}
                    <div class="flex items-center gap-3">
                        @if($platformKeys[$providerKey] ?? false)
                            <span class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full"
                                  style="background:rgba(16,185,129,0.12);color:#6ee7b7;border:1px solid rgba(16,185,129,0.25)">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                                Platform key active
                            </span>
                        @endif
                        @if($keys->has($providerKey))
                            <span class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full"
                                  style="background:rgba(245,193,0,0.12);color:#fde68a;border:1px solid rgba(245,193,0,0.25)">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:#F5C100"></span>
                                Your key connected
                            </span>
                            <form method="POST" action="{{ route('settings.api-keys.destroy', $providerKey) }}"
                                  onsubmit="return confirm('Remove {{ $provider['label'] }} key?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:text-red-400 border border-red-900/50 rounded px-2.5 py-1 hover:bg-red-900/20 transition">
                                    Remove
                                </button>
                            </form>
                        @else
                            <button onclick="toggleKeyForm('{{ $providerKey }}')"
                                class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:text-white hover:border-gray-600 transition">
                                + Add Your Key
                                <a href="{{ $pm['docsUrl'] }}" target="_blank" onclick="event.stopPropagation()"
                                   class="ml-1 text-gray-600 hover:text-gray-400">↗</a>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Inline key form (hidden by default) --}}
                @if(!$keys->has($providerKey))
                <div id="key-form-{{ $providerKey }}" class="hidden border-b border-gray-800 px-5 py-4"
                     style="background:rgba(255,255,255,0.02)">
                    <form method="POST" action="{{ route('settings.api-keys.store') }}" class="flex items-end gap-3">
                        @csrf
                        <input type="hidden" name="provider" value="{{ $providerKey }}">
                        <div class="flex-1">
                            <label class="text-gray-600 text-xs block mb-1">Label</label>
                            <input type="text" name="label" placeholder="{{ $provider['label'] }} Key"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-yellow-500 transition">
                        </div>
                        <div class="flex-1">
                            <label class="text-gray-600 text-xs block mb-1">API Key</label>
                            <input type="password" name="api_key" placeholder="{{ $pm['keyHint'] }}"
                                autocomplete="new-password"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:border-yellow-500 transition">
                        </div>
                        <button type="submit" class="text-sm font-bold px-5 py-2 rounded-lg text-gray-900 shrink-0" style="background:#F5C100">
                            Save Key
                        </button>
                        <button type="button" onclick="toggleKeyForm('{{ $providerKey }}')"
                            class="text-xs text-gray-600 hover:text-white px-2">✕</button>
                    </form>
                </div>
                @endif

                {{-- Models grid --}}
                <div class="divide-y divide-gray-800/60">
                    @foreach($provider['models'] as $modelId => $m)
                        @php
                            $tc          = $tierColors[$m['tier']] ?? $tierColors['Balanced'];
                            $usingWorkers= $workersByModel->get($modelId, collect());
                            $hasPlatform = $platformKeys[$providerKey] ?? false;
                            $hasOwnKey   = $keys->has($providerKey);
                            $accessible  = $hasPlatform || $hasOwnKey;
                        @endphp
                        <div class="px-5 py-3 flex items-center gap-4">

                            {{-- Status dot --}}
                            <div class="shrink-0">
                                @if($usingWorkers->isNotEmpty())
                                    <span class="w-2.5 h-2.5 rounded-full bg-green-400 block animate-pulse" title="Running"></span>
                                @elseif($accessible)
                                    <span class="w-2.5 h-2.5 rounded-full bg-gray-600 block" title="Available"></span>
                                @else
                                    <span class="w-2.5 h-2.5 rounded-full bg-gray-800 border border-gray-700 block" title="No key"></span>
                                @endif
                            </div>

                            {{-- Model info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-white text-xs font-semibold">{{ $m['name'] }}</span>
                                    <span class="text-xs px-1.5 py-0.5 rounded-full font-medium"
                                          style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }}">{{ $m['tier'] }}</span>
                                    @if(!empty($m['recommended']))
                                        <span class="text-xs px-1.5 py-0.5 rounded-full" style="background:rgba(255,255,255,0.05);color:#6b7280">recommended</span>
                                    @endif
                                    @if($hasPlatform && !$hasOwnKey)
                                        <span class="text-xs px-1.5 py-0.5 rounded-full" style="background:rgba(16,185,129,0.1);color:#6ee7b7;border:1px solid rgba(16,185,129,0.2)">Platform</span>
                                    @elseif($hasOwnKey)
                                        <span class="text-xs px-1.5 py-0.5 rounded-full" style="background:rgba(245,193,0,0.1);color:#fde68a;border:1px solid rgba(245,193,0,0.2)">Your Key</span>
                                    @endif
                                </div>
                                <p class="text-gray-700 text-xs font-mono mt-0.5">{{ $modelId }}</p>
                            </div>

                            {{-- Pricing --}}
                            <div class="text-right shrink-0 hidden sm:block">
                                <p class="text-gray-600 text-xs font-mono">${{ number_format($m['cost_in'], 2) }} in</p>
                                <p class="text-gray-700 text-xs font-mono">${{ number_format($m['cost_out'], 2) }} out / M</p>
                            </div>

                            {{-- Workers using this model --}}
                            <div class="shrink-0 w-44 text-right">
                                @if($usingWorkers->isNotEmpty())
                                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                        @foreach($usingWorkers as $w)
                                            <a href="{{ route('workers.show', $w->id) }}"
                                               class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full hover:opacity-80 transition"
                                               style="background:rgba(245,193,0,0.12);color:#F5C100;border:1px solid rgba(245,193,0,0.25)"
                                               title="{{ $w->name }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $w->status === 'active' ? 'bg-green-400 animate-pulse' : 'bg-yellow-400' }}"></span>
                                                {{ Str::limit($w->name, 18) }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-800 text-xs">— no workers</span>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>
        @endforeach

        {{-- ── Custom Models ─────────────────────────────────────────────────── --}}
        @if($customModels->isNotEmpty())
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-800 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                     style="background:rgba(156,163,175,0.1);border:1px solid rgba(156,163,175,0.3);color:#9ca3af">CU</div>
                <div>
                    <p class="text-white text-sm font-semibold">Custom Models</p>
                    <p class="text-gray-600 text-xs">Self-hosted & custom endpoints</p>
                </div>
            </div>
            <div class="divide-y divide-gray-800/60">
                @foreach($customModels as $cm)
                    @php $usingWorkers = $workersByModel->get($cm->model_id, collect()); @endphp
                    <div class="px-5 py-3 flex items-center gap-4">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $usingWorkers->isNotEmpty() ? 'bg-green-400 animate-pulse' : 'bg-gray-600' }}"></span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-white text-xs font-semibold">{{ $cm->name }}</span>
                                <span class="text-xs px-1.5 py-0.5 rounded-full" style="background:rgba(156,163,175,0.1);color:#9ca3af">Custom</span>
                            </div>
                            <p class="text-gray-700 text-xs font-mono mt-0.5 truncate">{{ $cm->base_url }} · {{ $cm->model_identifier }}</p>
                        </div>
                        <div class="shrink-0 w-44 text-right">
                            @foreach($usingWorkers as $w)
                                <a href="{{ route('workers.show', $w->id) }}"
                                   class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full"
                                   style="background:rgba(245,193,0,0.12);color:#F5C100;border:1px solid rgba(245,193,0,0.25)">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                                    {{ Str::limit($w->name, 18) }}
                                </a>
                            @endforeach
                        </div>
                        <form method="POST" action="{{ route('settings.custom-models.destroy', $cm->id) }}"
                              onsubmit="return confirm('Remove {{ $cm->name }}?')" class="shrink-0">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-700 hover:text-red-400 border border-red-900/40 rounded px-2 py-1 hover:bg-red-900/20 transition">Remove</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Legend + note --}}
    <div class="mt-5 flex items-start gap-6">
        <div class="flex items-center gap-4 text-xs text-gray-600">
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>Running on a worker</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-600"></span>Available · not in use</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-gray-800 border border-gray-700"></span>No key — add one to unlock</span>
        </div>
        <p class="text-gray-700 text-xs ml-auto">Your keys take priority over platform keys · Encrypted at rest · Never logged</p>
    </div>

    <script>
    function toggleKeyForm(provider) {
        document.getElementById('key-form-' + provider)?.classList.toggle('hidden');
    }
    </script>

</x-app-layout>

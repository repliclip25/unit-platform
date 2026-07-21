<x-app-layout title="Models & API Keys">

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">{{ session('error') }}</div>
    @endif

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-white text-lg font-bold">Models & API Keys</h1>
            <p class="text-gray-500 text-xs mt-0.5">All available models · BYOK keys · custom endpoints</p>
        </div>
        <button onclick="document.getElementById('register-panel').classList.toggle('hidden')"
            class="text-sm font-bold px-4 py-2 rounded-xl text-gray-900 hover:opacity-90 flex items-center gap-2 shrink-0 ac-bg">
            + Register Model
        </button>
    </div>

    @php
        $catalog = \App\Platform\Services\LLM\ModelCatalog::all();

        $providerMeta = [
            'anthropic' => ['color' => 'var(--accent)', 'bg' => 'rgba(var(--accent-rgb),0.1)',   'border' => 'rgba(var(--accent-rgb),0.3)',   'keyHint' => 'sk-ant-…',  'docsUrl' => 'https://console.anthropic.com/settings/keys'],
            'openai'    => ['color' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)',  'border' => 'rgba(16,185,129,0.3)',  'keyHint' => 'sk-…',      'docsUrl' => 'https://platform.openai.com/api-keys'],
            'kimi'      => ['color' => '#06b6d4', 'bg' => 'rgba(6,182,212,0.1)',   'border' => 'rgba(6,182,212,0.3)',   'keyHint' => 'sk-…',      'docsUrl' => 'https://platform.moonshot.cn/console/api-keys'],
            'google'    => ['color' => '#a855f7', 'bg' => 'rgba(168,85,247,0.1)',  'border' => 'rgba(168,85,247,0.3)',  'keyHint' => 'AIza…',     'docsUrl' => 'https://aistudio.google.com/app/apikey'],
            'custom'    => ['color' => '#9ca3af', 'bg' => 'rgba(156,163,175,0.1)', 'border' => 'rgba(156,163,175,0.3)', 'keyHint' => 'optional',  'docsUrl' => '#'],
        ];

        $tierColors = [
            'Fast'      => ['bg' => 'var(--badge-fast-bg)',      'color' => 'var(--badge-fast-text)'],
            'Balanced'  => ['bg' => 'var(--badge-balanced-bg)',  'color' => 'var(--badge-balanced-text)'],
            'Powerful'  => ['bg' => 'var(--badge-powerful-bg)',  'color' => 'var(--badge-powerful-text)'],
            'Reasoning' => ['bg' => 'var(--badge-reasoning-bg)', 'color' => 'var(--badge-reasoning-text)'],
            'Custom'    => ['bg' => 'var(--badge-custom-bg)',    'color' => 'var(--badge-custom-text)'],
        ];

        $workersByModel = $workers->groupBy('model');
    @endphp

    {{-- ── Register Custom Model ──────────────────────────────────────────────── --}}
    <div id="register-panel" class="hidden mb-6 rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4 flex items-start justify-between gap-3" style="border-bottom:1px solid var(--border)">
            <div>
                <p class="font-semibold text-sm" style="color:var(--text-primary)">Register Custom Model</p>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">Any OpenAI-compatible endpoint — self-hosted Llama, Mistral, Groq, Together.ai, Ollama, etc.</p>
            </div>
            <button onclick="document.getElementById('register-panel').classList.add('hidden')" class="text-lg shrink-0" style="color:var(--text-faint)">✕</button>
        </div>
        <form method="POST" action="{{ route('settings.custom-models.store') }}" class="px-5 py-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-xs block mb-1.5" style="color:var(--text-muted)">Display Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" placeholder="My Llama 3 Server"
                        class="w-full rounded-lg px-3 py-2 text-sm focus:outline-none transition"
                        style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-primary)">
                </div>
                <div>
                    <label class="text-xs block mb-1.5" style="color:var(--text-muted)">Model Identifier <span class="text-red-500">*</span></label>
                    <input type="text" name="model_identifier" placeholder="llama3.2:latest"
                        class="w-full rounded-lg px-3 py-2 text-sm font-mono focus:outline-none transition"
                        style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-primary)">
                </div>
                <div>
                    <label class="text-xs block mb-1.5" style="color:var(--text-muted)">Base URL <span class="text-red-500">*</span></label>
                    <input type="url" name="base_url" placeholder="http://localhost:11434/v1"
                        class="w-full rounded-lg px-3 py-2 text-sm font-mono focus:outline-none transition"
                        style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-primary)">
                </div>
                <div>
                    <label class="text-xs block mb-1.5" style="color:var(--text-muted)">API Key <span style="color:var(--text-faint)">(optional)</span></label>
                    <input type="password" name="api_key" placeholder="sk-… or leave blank"
                        autocomplete="new-password"
                        class="w-full rounded-lg px-3 py-2 text-sm font-mono focus:outline-none transition"
                        style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-primary)">
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="text-sm font-bold px-6 py-2 rounded-lg hover:opacity-90 ac-on">
                    Register Model
                </button>
                <p class="text-xs" style="color:var(--text-faint)">After registering, select from any worker's Configure tab.</p>
            </div>
        </form>
    </div>

    {{-- ── Model Catalog ───────────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        @foreach($catalog as $providerKey => $provider)
            @php $pm = $providerMeta[$providerKey]; @endphp
            <div class="rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">

                {{-- Provider header --}}
                <div class="px-4 py-3.5 flex flex-wrap items-center gap-3" style="border-bottom:1px solid var(--border)">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                             style="background:{{ $pm['bg'] }};border:1px solid {{ $pm['border'] }};color:{{ $pm['color'] }}">
                            {{ strtoupper(substr($providerKey, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold" style="color:var(--text-primary)">{{ $provider['label'] }}</p>
                            <p class="text-xs" style="color:var(--text-faint)">{{ count($provider['models']) }} models</p>
                        </div>
                    </div>

                    {{-- Key status + action --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @if($platformKeys[$providerKey] ?? false)
                            <span class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full"
                                  style="background:rgba(16,185,129,0.12);color:#6ee7b7;border:1px solid rgba(16,185,129,0.25)">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                                Platform key
                            </span>
                        @endif
                        @if($keys->has($providerKey))
                            <span class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full"
                                  style="background:rgba(var(--accent-rgb),0.12);color:var(--accent-text);border:1px solid rgba(var(--accent-rgb),0.25)">
                                <span class="w-1.5 h-1.5 rounded-full ac-bg"></span>
                                Your key
                            </span>
                            <form method="POST" action="{{ route('settings.api-keys.destroy', $providerKey) }}"
                                  onsubmit="return confirm('Remove {{ $provider['label'] }} key?')">
                                @csrf @method('DELETE')
                                <button class="text-xs px-2.5 py-1 rounded-lg transition"
                                        style="color:#f87171;border:1px solid rgba(239,68,68,.35);background:rgba(239,68,68,.07)">
                                    Remove
                                </button>
                            </form>
                        @else
                            <button onclick="toggleKeyForm('{{ $providerKey }}')"
                                class="text-xs px-3 py-1.5 rounded-lg transition"
                                style="border:1px solid var(--border);color:var(--text-muted)">
                                + Add key
                                <a href="{{ $pm['docsUrl'] }}" target="_blank" onclick="event.stopPropagation()"
                                   class="ml-1" style="color:var(--text-faint)">↗</a>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Inline key form --}}
                @if(!$keys->has($providerKey))
                <div id="key-form-{{ $providerKey }}" class="hidden px-4 py-4" style="border-bottom:1px solid var(--border);background:rgba(255,255,255,0.02)">
                    <form method="POST" action="{{ route('settings.api-keys.store') }}">
                        @csrf
                        <input type="hidden" name="provider" value="{{ $providerKey }}">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="text-xs block mb-1" style="color:var(--text-muted)">Label</label>
                                <input type="text" name="label" placeholder="{{ $provider['label'] }} Key"
                                    class="w-full rounded-lg px-3 py-2 text-sm focus:outline-none transition"
                                    style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-primary)">
                            </div>
                            <div>
                                <label class="text-xs block mb-1" style="color:var(--text-muted)">API Key</label>
                                <input type="password" name="api_key" placeholder="{{ $pm['keyHint'] }}"
                                    autocomplete="new-password"
                                    class="w-full rounded-lg px-3 py-2 text-sm font-mono focus:outline-none transition"
                                    style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-primary)">
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" class="text-sm font-bold px-5 py-2 rounded-lg hover:opacity-90 ac-on">Save Key</button>
                            <button type="button" onclick="toggleKeyForm('{{ $providerKey }}')"
                                class="text-xs" style="color:var(--text-faint)">Cancel</button>
                        </div>
                    </form>
                </div>
                @endif

                {{-- Models list --}}
                <div class="divide-y" style="border-color:var(--border-subtle)">
                    @foreach($provider['models'] as $modelId => $m)
                        @php
                            $tc           = $tierColors[$m['tier']] ?? $tierColors['Balanced'];
                            $usingWorkers = $workersByModel->get($modelId, collect());
                            $hasPlatform  = $platformKeys[$providerKey] ?? false;
                            $hasOwnKey    = $keys->has($providerKey);
                        @endphp
                        <div class="px-4 py-3">
                            <div class="flex items-start gap-3">
                                {{-- Status dot --}}
                                <div class="mt-1 shrink-0">
                                    @if($usingWorkers->isNotEmpty())
                                        <span class="w-2 h-2 rounded-full bg-green-400 block animate-pulse"></span>
                                    @elseif($hasPlatform || $hasOwnKey)
                                        <span class="w-2 h-2 rounded-full bg-gray-600 block"></span>
                                    @else
                                        <span class="w-2 h-2 rounded-full block" style="background:var(--bg-raised);border:1px solid var(--border)"></span>
                                    @endif
                                </div>

                                {{-- Model info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="text-xs font-semibold" style="color:var(--text-primary)">{{ $m['name'] }}</span>
                                        <span class="text-xs px-1.5 py-0.5 rounded-full font-medium"
                                              style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }}">{{ $m['tier'] }}</span>
                                        @if(!empty($m['recommended']))
                                            <span class="text-xs px-1.5 py-0.5 rounded-full" style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">recommended</span>
                                        @endif
                                        @if($hasPlatform && !$hasOwnKey)
                                            <span class="text-xs px-1.5 py-0.5 rounded-full" style="background:var(--badge-platform-bg);color:var(--badge-platform-text)">Platform</span>
                                        @elseif($hasOwnKey)
                                            <span class="text-xs px-1.5 py-0.5 rounded-full" style="background:var(--badge-yourkey-bg);color:var(--badge-yourkey-text)">Your Key</span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-mono mt-0.5 truncate" style="color:var(--text-faint)">{{ $modelId }}</p>
                                    {{-- Pricing (visible on all sizes, compact) --}}
                                    <p class="text-xs font-mono mt-0.5" style="color:var(--text-faint)">${{ number_format($m['cost_in'], 2) }} in · ${{ number_format($m['cost_out'], 2) }} out / M</p>
                                    {{-- Workers using --}}
                                    @if($usingWorkers->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach($usingWorkers as $w)
                                            <a href="{{ route('workers.show', $w->worker_slug) }}"
                                               class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full hover:opacity-80 transition"
                                               style="background:rgba(var(--accent-rgb),0.10);color:var(--accent-text);border:1px solid rgba(var(--accent-rgb),0.22)">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $w->status === 'active' ? 'bg-green-400 animate-pulse' : 'bg-yellow-400' }}"></span>
                                                {{ Str::limit($w->name, 18) }}
                                            </a>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        @endforeach

        {{-- ── Custom Models ─────────────────────────────────────────────────── --}}
        @if($customModels->isNotEmpty())
        <div class="rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
            <div class="px-4 py-3.5 flex items-center gap-3" style="border-bottom:1px solid var(--border)">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                     style="background:rgba(156,163,175,0.1);border:1px solid rgba(156,163,175,0.3);color:#9ca3af">CU</div>
                <div>
                    <p class="text-sm font-semibold" style="color:var(--text-primary)">Custom Models</p>
                    <p class="text-xs" style="color:var(--text-faint)">Self-hosted & custom endpoints</p>
                </div>
            </div>
            <div class="divide-y" style="border-color:var(--border-subtle)">
                @foreach($customModels as $cm)
                    @php $usingWorkers = $workersByModel->get($cm->model_id, collect()); @endphp
                    <div class="px-4 py-3 flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full shrink-0 mt-1 {{ $usingWorkers->isNotEmpty() ? 'bg-green-400 animate-pulse' : 'bg-gray-600' }}"></span>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-semibold" style="color:var(--text-primary)">{{ $cm->name }}</span>
                                <span class="text-xs px-1.5 py-0.5 rounded-full" style="background:rgba(156,163,175,0.1);color:#9ca3af">Custom</span>
                            </div>
                            <p class="text-xs font-mono mt-0.5 truncate" style="color:var(--text-faint)">{{ $cm->base_url }} · {{ $cm->model_identifier }}</p>
                            @if($usingWorkers->isNotEmpty())
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach($usingWorkers as $w)
                                    <a href="{{ route('workers.show', $w->worker_slug) }}"
                                       class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full"
                                       style="background:rgba(var(--accent-rgb),0.10);color:var(--accent-text);border:1px solid rgba(var(--accent-rgb),0.22)">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                                        {{ Str::limit($w->name, 18) }}
                                    </a>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('settings.custom-models.destroy', $cm->id) }}"
                              onsubmit="return confirm('Remove {{ $cm->name }}?')" class="shrink-0">
                            @csrf @method('DELETE')
                            <button class="text-xs px-2.5 py-1 rounded-lg transition"
                                    style="color:#f87171;border:1px solid rgba(239,68,68,.35);background:rgba(239,68,68,.07)">Remove</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Legend --}}
    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2">
        <span class="flex items-center gap-1.5 text-xs" style="color:var(--text-faint)"><span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>Running on a worker</span>
        <span class="flex items-center gap-1.5 text-xs" style="color:var(--text-faint)"><span class="w-2 h-2 rounded-full bg-gray-600"></span>Available · not in use</span>
        <span class="flex items-center gap-1.5 text-xs" style="color:var(--text-faint)"><span class="w-2 h-2 rounded-full" style="background:var(--bg-raised);border:1px solid var(--border)"></span>No key — add one to unlock</span>
        <p class="text-xs ml-auto" style="color:var(--text-faint)">Your keys take priority · Encrypted at rest · Never logged</p>
    </div>

    <script>
    function toggleKeyForm(provider) {
        document.getElementById('key-form-' + provider)?.classList.toggle('hidden');
    }
    </script>

    {{-- ── Danger Zone ──────────────────────────────────────────────────────── --}}
    <div class="mt-10" style="border:1px solid rgba(239,68,68,.25);border-radius:16px;overflow:hidden">
        <div class="px-5 py-4" style="background:rgba(239,68,68,.05);border-bottom:1px solid rgba(239,68,68,.15)">
            <h2 class="text-sm font-bold" style="color:#f87171">Danger Zone</h2>
        </div>
        <div class="px-5 py-5 flex flex-col sm:flex-row sm:items-start gap-4">
            <div class="flex-1">
                <p class="text-sm font-semibold" style="color:var(--text-primary)">Delete account</p>
                <p class="text-xs mt-1 leading-relaxed" style="color:var(--text-muted)">Schedules your account for deletion — you'll have 30 days to cancel before all workers, transactions, Gmail connections, memory, and billing records are permanently removed.</p>
            </div>
            <button onclick="document.getElementById('delete-account-modal').classList.remove('hidden')"
                    class="shrink-0 text-xs font-semibold px-4 py-2 rounded-lg transition self-start"
                    style="border:1px solid rgba(239,68,68,.4);background:rgba(239,68,68,.07);color:#f87171">
                Delete account
            </button>
        </div>
    </div>

    {{-- Delete account modal --}}
    <div id="delete-account-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.7)">
        <div class="w-full max-w-md mx-4 rounded-2xl p-6" style="background:var(--bg-card);border:1px solid rgba(239,68,68,.3)">
            <h3 class="text-base font-bold mb-2" style="color:#f87171">Delete your account</h3>
            <p class="text-sm mb-5 leading-relaxed" style="color:var(--text-muted)">Your account will be <strong style="color:var(--text-primary)">scheduled for deletion</strong> — not immediate. You have <strong style="color:var(--text-primary)">30 days</strong> to cancel from your Profile page. After that, all workers, transactions, Gmail connections, memory, and billing records are permanently removed.</p>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf @method('DELETE')
                <div class="mb-4">
                    <label class="block text-xs font-semibold mb-2" style="color:var(--text-muted)">
                        Type <span style="color:#f87171;font-family:monospace">DELETE</span> to confirm
                    </label>
                    <input type="text" name="confirm_delete" autocomplete="off" placeholder="DELETE"
                           class="w-full rounded-xl px-4 py-2.5 text-sm font-mono outline-none transition"
                           style="background:var(--bg-raised);border:1px solid rgba(239,68,68,.3);color:var(--text-primary)"
                           oninput="document.getElementById('confirm-delete-btn').disabled = this.value !== 'DELETE'">
                </div>
                <div class="flex gap-3">
                    <button type="submit" id="confirm-delete-btn" disabled
                            class="flex-1 py-2.5 rounded-xl text-sm font-bold transition disabled:opacity-40 disabled:cursor-not-allowed"
                            style="background:#ef4444;color:#fff">
                        Schedule deletion
                    </button>
                    <button type="button" onclick="document.getElementById('delete-account-modal').classList.add('hidden')"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold"
                            style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-secondary)">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>

<x-app-layout title="Configure — {{ $dep->name }}">

    @include('partials.worker-subnav')

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @php
        $config        = json_decode($dep->config, true) ?? [];
        $capture       = $config['capture'] ?? [];
        $currentModel  = $config['ai_model'] ?? 'claude-sonnet-4-6';
        $catalog       = \App\Platform\Services\LLM\ModelCatalog::all();

        $kwLines  = implode("\n", $capture['capture_keywords']     ?? []);
        $domLines = implode("\n", $capture['capture_domains']      ?? []);
        $sndLines = implode("\n", $capture['capture_senders_only'] ?? []);
        $excLines = implode("\n", $capture['exclude_senders']      ?? []);

        $providerColors = [
            'anthropic' => ['color' => 'var(--accent)', 'bg' => 'rgba(var(--accent-rgb),0.1)',  'border' => 'rgba(var(--accent-rgb),0.3)'],
            'openai'    => ['color' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)', 'border' => 'rgba(16,185,129,0.3)'],
            'kimi'      => ['color' => '#06b6d4', 'bg' => 'rgba(6,182,212,0.1)',  'border' => 'rgba(6,182,212,0.3)'],
            'google'    => ['color' => '#a855f7', 'bg' => 'rgba(168,85,247,0.1)', 'border' => 'rgba(168,85,247,0.3)'],
        ];
        $tierColors = [
            'Fast'      => ['bg' => 'rgba(6,182,212,0.15)',  'color' => '#67e8f9'],
            'Balanced'  => ['bg' => 'rgba(var(--accent-rgb),0.15)',  'color' => '#fde68a'],
            'Powerful'  => ['bg' => 'rgba(168,85,247,0.15)','color' => '#c4b5fd'],
            'Reasoning' => ['bg' => 'rgba(239,68,68,0.15)', 'color' => '#fca5a5'],
        ];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: config sections --}}
        <div class="lg:col-span-2 space-y-6">

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
                    </div>
                    <div class="pt-2">
                        <button type="submit" style="background:var(--accent);color:#000"
                                class="text-sm font-medium rounded-lg px-5 py-2.5 transition hover:opacity-90">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>

            {{-- Capture Guardrails --}}
            <div class="bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-4 border-b border-gray-800">
                    <h3 class="text-white text-sm font-semibold">Capture Guardrails</h3>
                    <p class="text-gray-500 text-xs mt-0.5">Filter which emails enter the pipeline. Emails that don't match are marked <code class="text-xs bg-gray-800 px-1 rounded">filtered_out</code> — no AI runs, no cost.</p>
                </div>
                <form method="POST" action="{{ route('workers.config', $dep->id) }}" class="px-5 py-5 space-y-5">
                    @csrf @method('PATCH')
                    <input type="hidden" name="name" value="{{ $dep->name }}">
                    <input type="hidden" name="ai_model" value="{{ $currentModel }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-gray-400 text-xs font-semibold uppercase tracking-wide block mb-1.5">Keywords</label>
                            <textarea name="capture_keywords" rows="4"
                                      placeholder="renew&#10;invoice&#10;expires&#10;subscription"
                                      class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-brand font-mono resize-y">{{ $kwLines }}</textarea>
                            <p class="text-gray-600 text-xs mt-1.5">One per line. Email must contain at least one (subject or body). Leave blank to capture all.</p>
                        </div>
                        <div>
                            <label class="text-gray-400 text-xs font-semibold uppercase tracking-wide block mb-1.5">Allowed Domains</label>
                            <textarea name="capture_domains" rows="4"
                                      placeholder="godaddy.com&#10;namecheap.com&#10;stripe.com"
                                      class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-brand font-mono resize-y">{{ $domLines }}</textarea>
                            <p class="text-gray-600 text-xs mt-1.5">One per line. Sender must be from one of these. Leave blank to allow any domain.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-gray-400 text-xs font-semibold uppercase tracking-wide block mb-1.5">Allowed Senders Only</label>
                            <textarea name="capture_senders_only" rows="3"
                                      placeholder="billing@godaddy.com&#10;alerts@stripe.com"
                                      class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-brand font-mono resize-y">{{ $sndLines }}</textarea>
                            <p class="text-gray-600 text-xs mt-1.5">One per line. Only process emails from these exact addresses. Leave blank to allow any sender.</p>
                        </div>
                        <div>
                            <label class="text-gray-400 text-xs font-semibold uppercase tracking-wide block mb-1.5">Excluded Senders</label>
                            <textarea name="exclude_senders" rows="3"
                                      placeholder="noreply@parking.com&#10;promo@ads.com"
                                      class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-brand font-mono resize-y">{{ $excLines }}</textarea>
                            <p class="text-gray-600 text-xs mt-1.5">One per line. Always blocked — checked before all other rules.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        @php $requireAll = !empty($capture['capture_require_all']); @endphp
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            {{-- toggle track --}}
                            <div class="relative flex-shrink-0" style="width:40px;height:22px">
                                <input type="checkbox" name="capture_require_all" value="1"
                                       {{ $requireAll ? 'checked' : '' }}
                                       id="toggle_require_all"
                                       class="sr-only peer">
                                <div id="toggle_require_all_track"
                                     onclick="document.getElementById('toggle_require_all').click(); updateToggle();"
                                     style="width:40px;height:22px;border-radius:11px;background:{{ $requireAll ? 'var(--accent)' : 'rgba(255,255,255,0.12)' }};position:relative;cursor:pointer;transition:background .2s;border:1px solid rgba(255,255,255,0.08);">
                                    <span id="toggle_require_all_knob"
                                          style="position:absolute;top:2px;{{ $requireAll ? 'left:20px' : 'left:2px' }};width:16px;height:16px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.4);"></span>
                                </div>
                            </div>
                            <span class="text-sm" style="color:var(--text-primary)">
                                Require <strong>all</strong> keywords to match
                                <span style="color:var(--text-muted)" class="text-xs ml-1">(off = any match is enough)</span>
                            </span>
                        </label>
                    </div>
                    <script>
                    function updateToggle() {
                        const cb = document.getElementById('toggle_require_all');
                        const track = document.getElementById('toggle_require_all_track');
                        const knob  = document.getElementById('toggle_require_all_knob');
                        setTimeout(function() {
                            track.style.background = cb.checked ? 'var(--accent)' : 'rgba(255,255,255,0.12)';
                            knob.style.left = cb.checked ? '20px' : '2px';
                        }, 0);
                    }
                    </script>

                    <div>
                        <label class="text-gray-400 text-xs font-semibold uppercase tracking-wide block mb-1.5">Capture Scope</label>
                        <input type="text" name="capture_scope" value="{{ $capture['capture_scope'] ?? 'All incoming emails' }}"
                               placeholder="Renewal and subscription emails only"
                               class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-brand">
                        <p class="text-gray-600 text-xs mt-1.5">Human-readable description shown on your dashboard.</p>
                    </div>

                    <div>
                        <button type="submit" style="background:var(--accent);color:#000"
                                class="text-sm font-medium rounded-lg px-5 py-2.5 transition hover:opacity-90">
                            Save Guardrails
                        </button>
                    </div>
                </form>
            </div>

            {{-- Prompt Overrides --}}
            @if(!empty($pipelineStages))
            @php
                $hasLastTx = !is_null($lastTx);
                $lastTxRaw = $hasLastTx ? (json_decode($lastTx->raw_input, true) ?? []) : [];
            @endphp
            <div class="bg-gray-900 border border-gray-800 rounded-xl" id="prompt-overrides-card">
                <div class="px-5 py-4 border-b border-gray-800">
                    <h3 class="text-white text-sm font-semibold">Prompt Overrides</h3>
                    <p class="text-gray-500 text-xs mt-0.5">
                        Customise what AVA does at each AI stage. Click <strong class="text-gray-300">View Default</strong> to see the built-in prompt before editing.
                        @if($hasLastTx)
                            <span class="text-gray-600">· "Test this prompt" uses your last email (from <span class="font-mono text-gray-500">{{ $lastTxRaw['from'] ?? '?' }}</span>) as input.</span>
                        @else
                            <span class="text-gray-600">· No transactions yet — send an email to your inbox to enable prompt testing.</span>
                        @endif
                    </p>
                </div>

                <form method="POST" action="{{ route('workers.prompt-overrides', $dep->id) }}" class="px-5 py-5 space-y-8">
                    @csrf

                    @foreach($pipelineStages as $stage)
                    @php
                        $stageKey    = $stage['key'];
                        $override    = $overrideRows[$stageKey] ?? null;
                        $hasJob      = !empty($stage['job_class']);
                        $defaults    = $defaultPrompts[$stageKey] ?? null;
                        $defaultSys  = $defaults['system']  ?? '';
                        $defaultUser = $defaults['user']    ?? '';
                        $isOverridden = $override && ($override->system_prompt || $override->user_prompt);
                    @endphp
                    @if($hasJob && ($defaultSys || $defaultUser))
                    <div class="stage-prompt-block" data-stage="{{ $stageKey }}">

                        {{-- Stage header --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-white">{{ $stage['label'] }}</span>
                                <span class="text-xs text-gray-600 font-mono">{{ $stageKey }}</span>
                                @if($isOverridden)
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                      style="background:rgba(var(--accent-rgb),0.15);color:var(--accent-text)">overridden</span>
                                @else
                                <button type="button"
                                        onclick="showDefaultModal('{{ $stageKey }}', '{{ addslashes($stage['label']) }}')"
                                        class="text-xs px-2 py-0.5 rounded-full border transition hover:border-gray-500"
                                        style="border-color:#374151;color:#6b7280">
                                    using default — view
                                </button>
                                @endif
                                @if($isOverridden)
                                <button type="button"
                                        onclick="showDefaultModal('{{ $stageKey }}', '{{ addslashes($stage['label']) }}')"
                                        class="text-xs text-gray-600 hover:text-gray-400 transition underline underline-offset-2">
                                    view default
                                </button>
                                @endif
                            </div>
                            @if($isOverridden)
                            <button type="button" onclick="clearStage('{{ $stageKey }}')"
                                    class="text-xs text-red-500 hover:text-red-400 transition">
                                Reset to default
                            </button>
                            @endif
                        </div>

                        {{-- System prompt --}}
                        <div class="mb-2">
                            <label class="text-gray-600 text-xs uppercase tracking-wide block mb-1">
                                System Prompt <span class="text-gray-700 normal-case">— who AVA is in this stage</span>
                            </label>
                            <textarea id="sys_{{ $stageKey }}" name="stages[{{ $stageKey }}][system]" rows="3"
                                      placeholder="{{ $defaultSys ? 'Leave blank to use default...' : 'No default — enter a system prompt' }}"
                                      class="w-full bg-gray-800 text-white text-xs font-mono rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-brand resize-y">{{ $override?->system_prompt ?? '' }}</textarea>
                        </div>

                        {{-- User prompt --}}
                        <div class="mb-3">
                            <label class="text-gray-600 text-xs uppercase tracking-wide block mb-1">
                                User Prompt
                                <span class="text-gray-700 normal-case">— placeholders:
                                    <code class="bg-gray-800 px-1 rounded">{RAW_EMAIL}</code>
                                    <code class="bg-gray-800 px-1 rounded">{READ_OUTPUT}</code>
                                </span>
                            </label>
                            <textarea id="user_{{ $stageKey }}" name="stages[{{ $stageKey }}][user]" rows="6"
                                      placeholder="{{ $defaultUser ? 'Leave blank to use default...' : 'No default — enter a user prompt' }}"
                                      class="w-full bg-gray-800 text-white text-xs font-mono rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-brand resize-y">{{ $override?->user_prompt ?? '' }}</textarea>
                        </div>

                        {{-- Test button + result --}}
                        <div>
                            <button type="button"
                                    onclick="testPrompt('{{ $stageKey }}', {{ $dep->id }})"
                                    {{ $hasLastTx ? '' : 'disabled' }}
                                    class="text-xs font-medium px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:border-gray-500 hover:text-gray-300 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Test this prompt
                            </button>
                            <div id="test_result_{{ $stageKey }}" class="mt-3 hidden"></div>
                        </div>

                        @if(!$loop->last)
                        <div class="border-t border-gray-800/50 mt-6"></div>
                        @endif
                    </div>
                    @endif
                    @endforeach

                    <div class="pt-2 flex items-center gap-4">
                        <button type="submit" style="background:var(--accent);color:#000"
                                class="text-sm font-medium rounded-lg px-5 py-2.5 transition hover:opacity-90">
                            Save Prompt Overrides
                        </button>
                        <p class="text-gray-600 text-xs">Clear both fields for a stage to revert it to the worker default.</p>
                    </div>
                </form>
            </div>

            {{-- Default prompt modal --}}
            <div id="default-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
                 style="background:rgba(0,0,0,0.7)">
                <div class="bg-gray-900 border border-gray-700 rounded-2xl w-full max-w-2xl max-h-[80vh] flex flex-col">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                        <div>
                            <h3 class="text-white text-sm font-semibold" id="modal-title">Default Prompt</h3>
                            <p class="text-gray-500 text-xs mt-0.5">Read-only — this is what AVA uses when no override is set</p>
                        </div>
                        <button onclick="closeDefaultModal()" class="text-gray-600 hover:text-gray-400 text-lg leading-none">✕</button>
                    </div>
                    <div class="overflow-y-auto px-5 py-4 space-y-4 flex-1">
                        <div>
                            <p class="text-gray-500 text-xs uppercase tracking-wide font-semibold mb-2">System Prompt</p>
                            <pre id="modal-system" class="bg-gray-800 text-gray-300 text-xs font-mono rounded-lg p-3 whitespace-pre-wrap break-words"></pre>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase tracking-wide font-semibold mb-2">User Prompt</p>
                            <pre id="modal-user" class="bg-gray-800 text-gray-300 text-xs font-mono rounded-lg p-3 whitespace-pre-wrap break-words"></pre>
                        </div>
                    </div>
                    <div class="px-5 py-4 border-t border-gray-800 flex gap-3">
                        <button onclick="useDefaultAsStartingPoint()" style="background:var(--accent);color:#000"
                                class="text-xs font-semibold rounded-lg px-4 py-2 transition hover:opacity-90">
                            Use as starting point
                        </button>
                        <button onclick="closeDefaultModal()"
                                class="text-xs text-gray-500 hover:text-gray-300 transition px-4 py-2">
                            Close
                        </button>
                    </div>
                </div>
            </div>
            @endif

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

                    <button type="submit" class="w-full text-sm font-bold py-2.5 rounded-lg text-gray-900 hover:opacity-90 transition"
                            style="background:var(--accent)">
                        Apply Model
                    </button>
                </form>
            </div>
        </div>

    </div>

{{-- Default prompts embedded as JSON for JS --}}
@if(!empty($pipelineStages))
<script>
const DEFAULT_PROMPTS = @json($defaultPrompts);
let _activeModalStage = null;

function showDefaultModal(stageKey, stageLabel) {
    const d = DEFAULT_PROMPTS[stageKey] ?? {};
    document.getElementById('modal-title').textContent = stageLabel + ' — Default Prompt';
    document.getElementById('modal-system').textContent = d.system || '(none)';
    document.getElementById('modal-user').textContent   = d.user   || '(none)';
    _activeModalStage = stageKey;
    const modal = document.getElementById('default-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDefaultModal() {
    const modal = document.getElementById('default-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    _activeModalStage = null;
}

function useDefaultAsStartingPoint() {
    if (!_activeModalStage) return;
    const d = DEFAULT_PROMPTS[_activeModalStage] ?? {};
    const sysEl  = document.getElementById('sys_'  + _activeModalStage);
    const userEl = document.getElementById('user_' + _activeModalStage);
    if (sysEl  && !sysEl.value)  sysEl.value  = d.system || '';
    if (userEl && !userEl.value) userEl.value = d.user   || '';
    closeDefaultModal();
    // Scroll to the stage block
    document.querySelector(`[data-stage="${_activeModalStage}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function clearStage(stageKey) {
    if (!confirm('Reset "' + stageKey + '" to default? Your override will be cleared.')) return;
    document.getElementById('sys_'  + stageKey).value = '';
    document.getElementById('user_' + stageKey).value = '';
    document.getElementById('sys_'  + stageKey).closest('form').requestSubmit();
}

// Close modal on backdrop click
document.getElementById('default-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDefaultModal();
});

async function testPrompt(stageKey, depId) {
    const sysEl    = document.getElementById('sys_'  + stageKey);
    const userEl   = document.getElementById('user_' + stageKey);
    const resultEl = document.getElementById('test_result_' + stageKey);
    const defaults = DEFAULT_PROMPTS[stageKey] ?? {};

    const systemVal = sysEl?.value.trim()  || defaults.system || '';
    const userVal   = userEl?.value.trim() || defaults.user   || '';

    if (!systemVal && !userVal) {
        resultEl.innerHTML = '<p class="text-yellow-500 text-xs">Enter a prompt or the default will be used.</p>';
        resultEl.classList.remove('hidden');
        return;
    }

    resultEl.innerHTML = `
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center gap-2 text-gray-400 text-xs">
                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Running against your last email…
            </div>
        </div>`;
    resultEl.classList.remove('hidden');

    try {
        const resp = await fetch(`/workers/${depId}/prompt-test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
            },
            body: JSON.stringify({ stage_key: stageKey, system: systemVal, user: userVal }),
        });
        const data = await resp.json();

        if (resp.status === 402 || data.gate === 'PROMPT_TEST_EXHAUSTED') {
            resultEl.innerHTML = `
                <div class="bg-yellow-950 border border-yellow-700 rounded-xl p-4">
                    <p class="text-yellow-300 text-xs font-semibold mb-1">Free tests used up</p>
                    <p class="text-yellow-200 text-xs">${escHtml(data.error ?? 'You have used all free prompt tests.')}</p>
                    ${data.subscribe ? `<a href="${escHtml(data.subscribe)}" class="inline-block mt-2 text-xs px-3 py-1 rounded" style="background:var(--accent);color:#000">Subscribe to continue</a>` : ''}
                </div>`;
            return;
        }

        if (!resp.ok || data.error) {
            resultEl.innerHTML = `
                <div class="bg-red-950 border border-red-800 rounded-xl p-4">
                    <p class="text-red-400 text-xs font-semibold mb-1">Error</p>
                    <p class="text-red-300 text-xs font-mono">${escHtml(data.error ?? 'Unknown error')}</p>
                </div>`;
            return;
        }

        const txInfo = data.tx_used
            ? `<p style="color:var(--text-muted)" class="text-xs mt-2">Tested against: <span class="font-mono">${escHtml(data.tx_used.from)} — ${escHtml(data.tx_used.subject)}</span></p>`
            : '';

        const trialBadge = data.trial
            ? `<p style="color:var(--text-muted)" class="text-xs mt-1">${data.trial.remaining} free test${data.trial.remaining !== 1 ? 's' : ''} remaining</p>`
            : '';

        const outputStr = typeof data.output === 'object'
            ? JSON.stringify(data.output, null, 2)
            : String(data.output);

        resultEl.innerHTML = `
            <div class="bg-gray-800 border border-gray-700 rounded-xl p-4">
                <p class="text-gray-400 text-xs font-semibold uppercase tracking-wide mb-2">Output</p>
                <pre class="text-green-300 text-xs font-mono whitespace-pre-wrap break-words">${escHtml(outputStr)}</pre>
                ${txInfo}
                ${trialBadge}
            </div>`;
    } catch(e) {
        resultEl.innerHTML = `<p class="text-red-400 text-xs">Request failed: ${escHtml(e.message)}</p>`;
    }
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endif

</x-app-layout>

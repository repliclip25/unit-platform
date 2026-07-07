<x-app-layout title="System QA">

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Page header --}}
    <div class="mb-6 flex items-end justify-between">
        <div>
            <h1 class="text-white text-lg font-bold">System QA & Architecture</h1>
            <p class="text-gray-500 text-xs mt-0.5">Platform · Marketplace · Deployed Workers · Security</p>
        </div>
        <div class="text-right">
            <p class="text-gray-600 text-xs">Deployed Workers</p>
            <p class="text-white text-xl font-bold">{{ $deployedCount }}</p>
        </div>
    </div>

    {{-- Horizon restart confirmation bar --}}
    <div id="horizon-restart-bar" class="hidden mb-4 bg-gray-900 border border-yellow-800 rounded-xl px-5 py-3 flex items-center justify-between">
        <div>
            <p class="text-yellow-300 text-sm font-semibold">⚠ Restart Horizon</p>
            <p class="text-gray-500 text-xs mt-0.5">Terminates and restarts all queue supervisors. Workers pause briefly. Requires Supervisor/PM2 to auto-restart.</p>
        </div>
        <div class="flex gap-3 ml-6">
            <button onclick="restartHorizon()" class="text-xs font-bold px-4 py-2 rounded-lg bg-red-900 text-red-300 hover:bg-red-800 transition">Confirm Restart</button>
            <button onclick="document.getElementById('horizon-restart-bar').classList.add('hidden')" class="text-xs text-gray-500 hover:text-white">Cancel</button>
        </div>
    </div>

    {{-- ── TAB BAR ────────────────────────────────────────────────────────── --}}
    <div class="flex gap-1 mb-6 bg-gray-900 border border-gray-800 p-1 rounded-xl w-fit">
        <button onclick="switchLayer('platform')" id="layer-tab-platform"
            class="layer-tab px-5 py-2 rounded-lg text-sm font-semibold transition bg-gray-800 text-white">
            Platform
        </button>
        <button onclick="switchLayer('marketplace')" id="layer-tab-marketplace"
            class="layer-tab px-5 py-2 rounded-lg text-sm font-semibold transition text-gray-400 hover:text-white">
            Marketplace
        </button>
        <button onclick="switchLayer('workers')" id="layer-tab-workers"
            class="layer-tab px-5 py-2 rounded-lg text-sm font-semibold transition text-gray-400 hover:text-white">
            Deployed Workers
        </button>
        <button onclick="switchLayer('security')" id="layer-tab-security"
            class="layer-tab px-5 py-2 rounded-lg text-sm font-semibold transition text-gray-400 hover:text-white">
            Security
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         PLATFORM LAYER
    ══════════════════════════════════════════════════════════════════ --}}
    <div id="layer-platform" class="layer-panel space-y-4">
        {{-- $stuckCount and $pendingReview passed from controller --}}

        {{-- Operational alerts --}}
        @if($stuckCount > 0 || $pendingReview > 0)
        <div class="space-y-2">
            @if($pendingReview > 0)
            <div class="flex items-center justify-between px-4 py-3 rounded-xl border"
                 style="background:rgba(var(--accent-rgb),0.08);border-color:rgba(var(--accent-rgb),0.35)">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full animate-pulse" style="background:#a78bfa"></span>
                    <span class="text-sm" style="color:var(--accent)">
                        <strong>{{ $pendingReview }}</strong> draft{{ $pendingReview !== 1 ? 's' : '' }} awaiting your review
                    </span>
                </div>
                <a href="{{ route('transactions', ['filter' => 'draft_ready']) }}"
                   class="text-xs px-3 py-1.5 rounded-lg font-medium transition"
                   style="background:rgba(var(--accent-rgb),0.20);color:var(--accent);border:1px solid rgba(var(--accent-rgb),0.45)">
                    Review Now →
                </a>
            </div>
            @endif

            @if($stuckCount > 0)
            <div class="flex items-center justify-between px-4 py-3 rounded-xl border"
                 style="background:rgba(245,158,11,0.08);border-color:rgba(245,158,11,0.3)">
                <div class="flex items-center gap-3">
                    <span style="color:#f59e0b">⚠</span>
                    <span class="text-sm" style="color:#fcd34d">
                        <strong>{{ $stuckCount }}</strong> transaction{{ $stuckCount !== 1 ? 's' : '' }} stuck mid-pipeline for &gt;5 min
                    </span>
                </div>
                <button id="recover-btn" onclick="recoverStuck()"
                    class="text-xs px-3 py-1.5 rounded-lg font-medium transition"
                    style="background:rgba(245,158,11,0.2);color:#fcd34d;border:1px solid rgba(245,158,11,0.4)">
                    ↺ Recover All
                </button>
            </div>
            @endif
        </div>
        @endif

        <div class="flex justify-end">
            <button onclick="document.getElementById('horizon-restart-bar').classList.remove('hidden'); window.scrollTo(0,0)"
                class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-500 hover:text-red-400 hover:border-red-800 transition">
                ↺ Restart Horizon
            </button>
        </div>

        @php
            $groups = [
                'core'    => ['label' => 'Core Infrastructure',   'icon' => '⬡', 'desc' => 'Database, queue, cache — the backbone everything runs on'],
                'billing' => ['label' => 'Billing & Payments',    'icon' => '◈', 'desc' => 'Stripe integration and subscription records'],
                'comms'   => ['label' => 'Communications',        'icon' => '◎', 'desc' => 'Email delivery and inbound webhook receivers'],
                'tenants' => ['label' => 'Tenant & Reports',      'icon' => '◉', 'desc' => 'Tenant registry, activity, and usage reporting'],
            ];
        @endphp

        @foreach($groups as $groupKey => $group)
            @php $grouped = collect($platform)->where('group', $groupKey); @endphp
            <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800 flex items-center gap-3">
                    <span class="text-gray-500 text-lg">{{ $group['icon'] }}</span>
                    <div>
                        <p class="text-white text-sm font-semibold">{{ $group['label'] }}</p>
                        <p class="text-gray-600 text-xs">{{ $group['desc'] }}</p>
                    </div>
                    <div class="ml-auto flex items-center gap-1.5">
                        @foreach($grouped as $s)
                            <span class="w-2 h-2 rounded-full {{ $s['status'] === 'ok' ? 'bg-green-400 animate-pulse' : ($s['status'] === 'warn' ? 'bg-yellow-400' : 'bg-red-500') }}"></span>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-x sm:divide-y divide-gray-800">
                    @foreach($grouped as $s)
                        @php $sc = match($s['status']) { 'ok' => ['dot' => 'bg-green-400', 'text' => 'text-green-400', 'badge' => 'OK'], 'warn' => ['dot' => 'bg-yellow-400', 'text' => 'text-yellow-400', 'badge' => 'WARN'], default => ['dot' => 'bg-red-500', 'text' => 'text-red-400', 'badge' => 'FAIL'] }; @endphp
                        <div class="px-5 py-4 flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full shrink-0 {{ $sc['dot'] }}"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-white text-xs font-semibold">{{ $s['label'] }}</p>
                                <p class="text-gray-500 text-xs mt-0.5 truncate">{{ $s['detail'] }}</p>
                            </div>
                            <span class="text-xs font-bold {{ $sc['text'] }} shrink-0">{{ $sc['badge'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         MARKETPLACE LAYER
    ══════════════════════════════════════════════════════════════════ --}}
    <div id="layer-marketplace" class="layer-panel hidden space-y-4">

        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
            <p class="text-white text-sm font-semibold">Worker Marketplace</p>
            <p class="text-gray-500 text-xs mt-0.5">Workers pass through QA before being available to tenants. Once published, tenants can deploy them to their workspace.</p>
        </div>

        @foreach($marketplaceWorkers as $mw)
            @php
                $w      = $mw->worker;
                $status = $w->marketplace_status;
                $lifecycle = [
                    'draft'      => ['bg' => 'bg-gray-800',    'text' => 'text-gray-400',   'label' => 'Draft'],
                    'in_testing' => ['bg' => 'bg-blue-900/40', 'text' => 'text-blue-300',   'label' => 'In Testing'],
                    'qa_passed'  => ['bg' => 'bg-teal-900/40', 'text' => 'text-teal-300',   'label' => 'QA Passed'],
                    'published'  => ['bg' => 'bg-green-900/40','text' => 'text-green-300',  'label' => 'Published'],
                    'deprecated' => ['bg' => 'bg-red-900/40',  'text' => 'text-red-300',    'label' => 'Deprecated'],
                ];
                $lc = $lifecycle[$status] ?? $lifecycle['draft'];
                $steps = ['draft', 'in_testing', 'qa_passed', 'published'];
                $stepIdx = array_search($status, $steps);
            @endphp
            <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">

                {{-- Header --}}
                <div class="px-5 py-4 border-b border-gray-800 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold shrink-0" style="background:rgba(var(--accent-rgb),0.15);color:var(--accent)">
                        {{ strtoupper(substr($w->slug, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-semibold text-sm">{{ $w->name }}</p>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $w->description ?? 'No description yet.' }}</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="text-xs font-bold px-3 py-1.5 rounded-full {{ $lc['bg'] }} {{ $lc['text'] }}">{{ $lc['label'] }}</span>
                        <span class="text-gray-600 text-xs border border-gray-800 rounded-lg px-3 py-1.5">{{ $mw->deployCount }} deployed</span>
                    </div>
                </div>

                <div class="px-5 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">

                    {{-- Lifecycle progress --}}
                    <div>
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3">Lifecycle</p>
                        <div class="flex items-center gap-0">
                            @foreach($steps as $si => $step)
                                @php
                                    $done    = $si <= ($stepIdx !== false ? $stepIdx : -1);
                                    $current = $si === ($stepIdx !== false ? $stepIdx : -1);
                                    $labels  = ['draft' => 'Draft', 'in_testing' => 'Testing', 'qa_passed' => 'QA Pass', 'published' => 'Published'];
                                @endphp
                                <div class="flex items-center">
                                    <div class="text-center w-20">
                                        <div class="w-6 h-6 rounded-full mx-auto flex items-center justify-center text-xs font-bold mb-1
                                            {{ $done ? ($current ? 'bg-yellow-400 text-gray-900' : 'bg-green-600 text-white') : 'bg-gray-800 text-gray-600' }}">
                                            {{ $done && !$current ? '✓' : ($si + 1) }}
                                        </div>
                                        <p class="text-xs {{ $done ? 'text-gray-400' : 'text-gray-700' }}">{{ $labels[$step] }}</p>
                                    </div>
                                    @if($si < count($steps) - 1)
                                        <div class="w-4 h-px {{ $si < ($stepIdx !== false ? $stepIdx : -1) ? 'bg-green-600' : 'bg-gray-800' }} mb-4 shrink-0"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Status controls --}}
                        <div class="mt-4 flex items-center gap-2 flex-wrap">
                            @if($status !== 'published' && $status !== 'deprecated')
                                <form method="POST" action="{{ route('qa.marketplace-publish', $w->id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold px-4 py-1.5 rounded-lg text-gray-900 hover:opacity-90" style="background:var(--accent)">
                                        ✓ Publish to Marketplace
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('qa.marketplace-status', $w->id) }}" class="flex items-center gap-2">
                                @csrf
                                <select name="status" class="bg-gray-800 border border-gray-700 text-white text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-brand">
                                    @foreach($lifecycle as $val => $lci)
                                        <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $lci['label'] }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:text-white hover:border-gray-600 transition">Set</button>
                            </form>
                            @if($w->published_at)
                                <span class="text-gray-700 text-xs">Published {{ \Carbon\Carbon::parse($w->published_at)->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- QA Checklist --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">QA Checklist</p>
                            @if($mw->totalChecks > 0)
                                <span class="text-xs {{ $mw->passedCount === $mw->totalChecks ? 'text-green-400' : 'text-yellow-400' }}">
                                    {{ $mw->passedCount }}/{{ $mw->totalChecks }} passed
                                </span>
                            @endif
                        </div>
                        @if(empty($mw->checklist))
                            <p class="text-gray-700 text-xs">No checklist defined yet.</p>
                        @else
                            <div class="space-y-1.5">
                                @foreach($mw->checklist as $item)
                                    <div class="flex items-start gap-2.5">
                                        <span class="text-xs mt-0.5 {{ $item['passed'] ? 'text-green-400' : 'text-gray-700' }} shrink-0">{{ $item['passed'] ? '✓' : '○' }}</span>
                                        <div class="min-w-0">
                                            <p class="text-xs {{ $item['passed'] ? 'text-gray-300' : 'text-gray-600' }}">{{ $item['check'] }}</p>
                                            @if(!empty($item['notes']))
                                                <p class="text-gray-700 text-xs truncate">{{ $item['notes'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($w->qa_passed_at)
                                <p class="text-green-600 text-xs mt-3">QA passed {{ \Carbon\Carbon::parse($w->qa_passed_at)->diffForHumans() }}</p>
                            @endif
                        @endif
                    </div>

                </div>

                {{-- Blueprint Section --}}
                @php $bp = json_decode($w->blueprint ?? '{}', true); @endphp
                @if(!empty($bp))
                <div class="border-t border-gray-800">
                    <button type="button" onclick="toggleBlueprint({{ $w->id }})"
                        class="w-full px-5 py-3.5 flex items-center justify-between text-left hover:bg-gray-800/40 transition">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400 text-sm">⬡</span>
                            <div>
                                <p class="text-white text-xs font-semibold">Worker Blueprint</p>
                                <p class="text-gray-600 text-xs">Folder structure · pipeline · memory schema · SDK manifest</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('qa.marketplace-blueprint', $w->id) }}"
                               onclick="event.stopPropagation()"
                               class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:text-white hover:border-gray-600 transition"
                               download="{{ $w->slug }}-blueprint.json">
                                ↓ Download SDK Manifest
                            </a>
                            <span id="bp-chevron-{{ $w->id }}" class="text-gray-600 text-xs transition-transform">▶</span>
                        </div>
                    </button>

                    <div id="blueprint-{{ $w->id }}" class="hidden border-t border-gray-800/60 px-5 py-5 space-y-6">

                        {{-- Folder Structure --}}
                        @if(!empty($bp['structure']['tree']))
                        <div>
                            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3">Folder Structure</p>
                            <div class="bg-gray-950 border border-gray-800 rounded-xl overflow-hidden font-mono">
                                <div class="px-4 py-2 border-b border-gray-800 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span>
                                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                                    <span class="text-gray-600 text-xs ml-2">app/Workers/{{ strtoupper($w->slug) }}/</span>
                                </div>
                                <div class="px-4 py-3 space-y-2.5">
                                    @php
                                        $roleColors = [
                                            'trigger'  => ['color' => '#fde68a', 'bg' => 'rgba(120,80,0,0.45)'],
                                            'classify' => ['color' => '#93c5fd', 'bg' => 'rgba(30,60,140,0.45)'],
                                            'memory'   => ['color' => '#c4b5fd', 'bg' => 'rgba(80,40,140,0.45)'],
                                            'log'      => ['color' => '#d1d5db', 'bg' => 'rgba(55,65,81,0.6)'],
                                            'template' => ['color' => '#5eead4', 'bg' => 'rgba(20,80,70,0.45)'],
                                            'generate' => ['color' => '#86efac', 'bg' => 'rgba(20,80,40,0.45)'],
                                            'output'   => ['color' => '#6ee7b7', 'bg' => 'rgba(10,80,55,0.45)'],
                                            'scheduled'=> ['color' => '#fdba74', 'bg' => 'rgba(120,50,10,0.45)'],
                                            'api'      => ['color' => '#f9a8d4', 'bg' => 'rgba(120,20,60,0.45)'],
                                            'watch'    => ['color' => '#67e8f9', 'bg' => 'rgba(10,80,100,0.45)'],
                                        ];
                                    @endphp
                                    @foreach($bp['structure']['tree'] as $file)
                                        @php
                                            $parts = explode('/', $file['path']);
                                            $fname = array_pop($parts);
                                            $dir   = implode('/', $parts);
                                            $rc    = $roleColors[$file['role']] ?? ['color' => '#d1d5db', 'bg' => 'rgba(55,65,81,0.6)'];
                                        @endphp
                                        <div class="grid gap-x-3 gap-y-0 items-center py-0.5" style="grid-template-columns: 1fr 90px 1fr">
                                            <div class="flex items-center gap-1 min-w-0">
                                                @if($dir)
                                                    <span class="text-xs shrink-0" style="color:#6b7280">{{ $dir }}/</span>
                                                @endif
                                                <span class="text-xs font-bold" style="color:{{ $rc['color'] }}">{{ $fname }}</span>
                                            </div>
                                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold text-center" style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }}">{{ $file['role'] }}</span>
                                            <span class="text-xs leading-relaxed" style="color:#9ca3af">{{ $file['description'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Pipeline Flow --}}
                        @if(!empty($bp['pipeline']))
                        <div>
                            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3">Pipeline Flow</p>
                            <div class="overflow-x-auto pb-2">
                                <div class="flex items-start min-w-max gap-0">
                                    @foreach($bp['pipeline'] as $step)
                                        <div class="flex items-center">
                                            <div class="w-36">
                                                <div class="border border-gray-700 bg-gray-800/60 rounded-xl px-3 py-3 text-center">
                                                    <p class="text-gray-400 text-xs font-mono mb-1">Step {{ $step['step'] ?? '?' }}</p>
                                                    <p class="text-white text-xs font-bold">{{ str_replace('Job', '', $step['job'] ?? '—') }}</p>
                                                    <div class="mt-2 pt-2 border-t border-gray-700/60">
                                                        <p class="text-xs truncate" style="color:#93c5fd" title="{{ $step['input'] ?? '' }}">in: {{ $step['input'] ?? '—' }}</p>
                                                        @if($step['output_column'] ?? null)
                                                            <p class="text-xs truncate" style="color:#86efac" title="{{ $step['output_column'] }}">out: {{ $step['output_column'] }}</p>
                                                        @else
                                                            <p class="text-xs" style="color:#4b5563">out: —</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <p class="text-gray-500 text-xs text-center mt-1.5 leading-snug px-1">{{ $step['description'] ?? '' }}</p>
                                            </div>
                                            @if(!$loop->last)
                                                <div class="flex flex-col items-center w-8 shrink-0 pb-8">
                                                    <div class="text-gray-700 text-lg leading-none">→</div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Memory + Config side by side --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            {{-- Memory Schema --}}
                            @php
                                // Support both old flat format and new shared/owned format
                                $memShared = $bp['memory']['shared'] ?? (isset($bp['memory'][0]['type']) ? $bp['memory'] : []);
                                $memOwned  = $bp['memory']['owned']  ?? [];
                            @endphp
                            @if(!empty($memShared) || !empty($memOwned))
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3">Memory Schema</p>
                                <div class="bg-gray-950 border border-gray-800 rounded-xl overflow-hidden">
                                    @if(!empty($memShared))
                                    <div class="px-3 py-1.5 border-b border-gray-800" style="background:rgba(99,102,241,0.08)">
                                        <span class="text-xs font-semibold" style="color:#818cf8">Shared — readable by all workers</span>
                                    </div>
                                    @foreach($memShared as $mem)
                                        <div class="px-4 py-3 border-b border-gray-800/60 last:border-0">
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <span class="text-xs font-bold font-mono" style="color:#6ee7b7">{{ $mem['table'] }}</span>
                                                <span class="text-xs border border-gray-700 rounded px-1.5" style="color:#9ca3af">{{ $mem['scope'] ?? '' }}</span>
                                                <span class="text-xs ml-auto px-1.5 rounded" style="background:rgba(16,185,129,0.15);color:#6ee7b7">{{ $mem['access'] ?? 'read' }}</span>
                                            </div>
                                            <p class="text-gray-400 text-xs">{{ $mem['description'] }}</p>
                                        </div>
                                    @endforeach
                                    @endif
                                    @if(!empty($memOwned))
                                    <div class="px-3 py-1.5 border-b border-gray-800" style="background:rgba(75,85,99,0.15)">
                                        <span class="text-xs font-semibold" style="color:#9ca3af">Owned — this worker only</span>
                                    </div>
                                    @foreach($memOwned as $mem)
                                        <div class="px-4 py-3 border-b border-gray-800/60 last:border-0">
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <span class="text-xs font-bold font-mono" style="color:#c4b5fd">{{ $mem['table'] }}</span>
                                                <span class="text-xs border border-gray-700 rounded px-1.5" style="color:#9ca3af">{{ $mem['scope'] ?? '' }}</span>
                                                <span class="text-xs ml-auto px-1.5 rounded" style="background:rgba(75,85,99,0.2);color:#9ca3af">{{ $mem['access'] ?? 'read' }}</span>
                                            </div>
                                            <p class="text-gray-400 text-xs">{{ $mem['description'] }}</p>
                                        </div>
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            @endif

                            {{-- Config Requirements --}}
                            @if(!empty($bp['config']))
                            <div>
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3">Config Requirements</p>
                                <div class="bg-gray-950 border border-gray-800 rounded-xl overflow-hidden">
                                    @foreach($bp['config']['required'] as $cfg)
                                        <div class="px-4 py-3 border-b border-gray-800/60 last:border-0 flex items-start gap-3">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0 mt-1.5"></span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-mono" style="color:#fde68a">{{ $cfg['key'] }}</p>
                                                <p class="text-gray-400 text-xs">{{ $cfg['description'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                    @foreach(($bp['config']['optional'] ?? []) as $cfg)
                                        <div class="px-4 py-3 border-b border-gray-800/60 last:border-0 flex items-start gap-3">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-600 shrink-0 mt-1.5"></span>
                                            <div class="min-w-0">
                                                <p class="text-gray-400 text-xs font-mono">{{ $cfg['key'] }}</p>
                                                <p class="text-gray-400 text-xs">{{ $cfg['description'] }} <span class="text-gray-500">(optional)</span></p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                        </div>

                        {{-- Queue + Platform requirements --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            @if(!empty($bp['queue']))
                            <div class="bg-gray-950 border border-gray-800 rounded-xl px-4 py-4">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3">Queue Isolation</p>
                                <div class="space-y-2">
                                    @foreach($bp['queue'] as $k => $v)
                                        @if($k !== 'note')
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500 text-xs">{{ $k }}</span>
                                            <span class="text-gray-200 text-xs font-mono">{{ $v }}</span>
                                        </div>
                                        @endif
                                    @endforeach
                                    @if(!empty($bp['queue']['note']))
                                        <p class="text-gray-400 text-xs pt-2 border-t border-gray-800 leading-relaxed">{{ $bp['queue']['note'] }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if(!empty($bp['platform_requirements']))
                            <div class="bg-gray-950 border border-gray-800 rounded-xl px-4 py-4">
                                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3">Platform Requirements</p>
                                <div class="space-y-2">
                                    @foreach($bp['platform_requirements'] as $k => $v)
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500 text-xs">{{ $k }}</span>
                                            <span class="text-gray-200 text-xs font-mono">{{ $v }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                        </div>

                        {{-- SDK Commands --}}
                        @if(!empty($bp['sdk']))
                        <div>
                            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3">SDK Commands</p>
                            <div class="bg-gray-950 border border-gray-800 rounded-xl overflow-hidden font-mono">
                                @foreach($bp['sdk'] as $k => $v)
                                    @if(!str_starts_with($k, '_') && $k !== 'manifest_version')
                                    <div class="px-4 py-3 border-b border-gray-800/60 last:border-0 flex items-center gap-3">
                                        <span class="text-gray-500 text-xs shrink-0 w-28">{{ str_replace('_', ' ', $k) }}</span>
                                        <span class="text-xs" style="color:#86efac">$ {{ $v }}</span>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
                @endif

                {{-- Footer --}}
                <div class="px-5 py-3 border-t border-gray-800 flex items-center gap-4 text-xs text-gray-600">
                    <span>Built by <span class="text-gray-500">{{ $w->built_by ?? 'UNIT Platform' }}</span></span>
                    <span>·</span>
                    <span>Slug: <span class="font-mono text-gray-500">{{ $w->slug }}</span></span>
                    <span>·</span>
                    <span>v{{ $w->version ?? '1.0.0' }}</span>
                    <span>·</span>
                    <span>{{ $mw->deployCount }} active deployment{{ $mw->deployCount !== 1 ? 's' : '' }}</span>
                </div>
            </div>
        @endforeach

    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         DEPLOYED WORKERS LAYER
    ══════════════════════════════════════════════════════════════════ --}}
    <div id="layer-workers" class="layer-panel hidden">
        @if($deployedWorkers->isEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-16 text-center">
                <p class="text-gray-500 text-sm">No workers deployed yet.</p>
                <a href="{{ route('workers.deploy') }}" class="mt-3 inline-block text-xs text-brand hover:underline">Deploy a worker →</a>
            </div>
        @else
        <div class="flex gap-4">

            {{-- Worker selector sidebar --}}
            <div class="w-52 shrink-0 space-y-1.5">
                <p class="text-gray-600 text-xs uppercase tracking-wider px-2 mb-2">Deployed Workers</p>
                @foreach($deployedWorkers as $i => $w)
                    <button type="button" onclick="selectWorker({{ $w->dep->id }})"
                        id="wtab-{{ $w->dep->id }}"
                        class="wtab w-full text-left px-4 py-3 rounded-xl border transition {{ $i === 0 ? 'border-brand bg-brand/10 text-white' : 'border-gray-800 bg-gray-900 text-gray-400 hover:text-white hover:border-gray-700' }}">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0" style="background:rgba(var(--accent-rgb),0.15);color:var(--accent)">
                                {{ strtoupper(substr($w->dep->worker_slug, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold truncate">{{ $w->dep->name }}</p>
                                <p class="text-gray-600 text-xs">{{ $w->activity['tx_total'] }} tx</p>
                            </div>
                        </div>
                        <div class="mt-2 flex gap-1.5 pl-9">
                            <span class="w-1.5 h-1.5 rounded-full mt-0.5 {{ $w->dep->status === 'active' ? 'bg-green-400' : 'bg-yellow-400' }}"></span>
                            <span class="text-xs text-gray-600">{{ ucfirst($w->dep->status) }}</span>
                        </div>
                    </button>
                @endforeach
            </div>

            {{-- Worker panels --}}
            <div class="flex-1 min-w-0">
            @foreach($deployedWorkers as $i => $w)
                @php $sc = $scenarios[$w->dep->id] ?? null; @endphp
                <div id="wpanel-{{ $w->dep->id }}" class="wpanel {{ $i !== 0 ? 'hidden' : '' }} space-y-4">

                    {{-- Header: identity + controls --}}
                    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold" style="background:rgba(var(--accent-rgb),0.15);color:var(--accent)">
                                    {{ strtoupper(substr($w->dep->worker_slug, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-white font-semibold text-sm">{{ $w->dep->name }}</p>
                                    <p class="text-gray-500 text-xs mt-0.5">
                                        Queue: <span class="font-mono text-gray-400">{{ $w->dep->worker_slug }}-{{ $w->dep->id }}</span>
                                        @if($w->billing)
                                            @php $bs = match($w->billing->status) { 'active' => 'bg-green-900 text-green-300', 'trial' => 'bg-blue-900 text-blue-300', default => 'bg-yellow-900 text-yellow-300' }; @endphp
                                            · <span class="text-xs px-2 py-0.5 rounded-full {{ $bs }}">{{ ucfirst($w->billing->status) }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div id="wctl-{{ $w->dep->id }}" class="flex items-center gap-2">
                                    @if($w->dep->status === 'active')
                                        <button onclick="workerAction({{ $w->dep->id }}, 'pause')"
                                            class="text-xs px-3 py-1.5 rounded-lg border border-yellow-700 text-yellow-400 hover:bg-yellow-900/30 transition">
                                            ⏸ Pause
                                        </button>
                                    @else
                                        <button onclick="workerAction({{ $w->dep->id }}, 'resume')"
                                            class="text-xs px-3 py-1.5 rounded-lg border border-green-700 text-green-400 hover:bg-green-900/30 transition">
                                            ▶ Resume
                                        </button>
                                    @endif
                                    <button onclick="workerAction({{ $w->dep->id }}, 'drain')"
                                        class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:bg-gray-800 transition"
                                        title="Clear pending jobs from this worker's queue">
                                        ⊘ Drain Queue
                                    </button>
                                </div>
                                <div id="qstats-{{ $w->dep->id }}" class="text-xs text-gray-600 border border-gray-800 rounded-lg px-3 py-1.5 font-mono">
                                    loading…
                                </div>
                                <form method="POST" action="{{ route('qa.fast-track', $w->dep->id) }}">
                                    @csrf
                                    <button type="submit"
                                        class="text-sm font-bold px-4 py-2 rounded-xl text-gray-900 hover:opacity-90 flex items-center gap-1.5"
                                        style="background:var(--accent)"
                                        onclick="return confirm('Run Fast Track test through {{ $w->dep->name }}?')">
                                        ⚡ Fast Track
                                    </button>
                                </form>
                                <a href="{{ route('qa.worker-blueprint', $w->workerDef->id) }}"
                                   class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:border-brand hover:text-brand transition flex items-center gap-1.5"
                                   title="Download worker blueprint as Markdown">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Blueprint
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Identity + Memory --}}
                    @php
                        $watchExpiry  = $w->credential?->watch_expiry ? \Carbon\Carbon::parse($w->credential->watch_expiry) : null;
                        $watchDaysLeft = $watchExpiry ? now()->diffInDays($watchExpiry, false) : null;
                        $watchWarning = $watchDaysLeft !== null && $watchDaysLeft <= 3;
                        $watchCritical= $watchDaysLeft !== null && $watchDaysLeft <= 0;
                    @endphp

                    {{-- Gmail watch expiry alert --}}
                    @if($watchWarning || $watchCritical)
                    <div class="flex items-center justify-between px-4 py-3 rounded-xl border"
                         style="background:{{ $watchCritical ? 'rgba(239,68,68,0.08)' : 'rgba(245,158,11,0.08)' }};border-color:{{ $watchCritical ? 'rgba(239,68,68,0.3)' : 'rgba(245,158,11,0.3)' }}">
                        <div class="flex items-center gap-2">
                            <span style="color:{{ $watchCritical ? '#ef4444' : '#f59e0b' }}">{{ $watchCritical ? '✗' : '⚠' }}</span>
                            <span class="text-xs" style="color:{{ $watchCritical ? '#fca5a5' : '#fcd34d' }}">
                                Gmail watch {{ $watchCritical ? 'expired' : "expires in {$watchDaysLeft} day(s)" }} — {{ $w->credential->gmail_address }}
                            </span>
                        </div>
                        <button id="renew-watch-{{ $w->dep->id }}"
                            onclick="renewWatch({{ $w->dep->id }})"
                            class="text-xs px-3 py-1.5 rounded-lg font-medium transition"
                            style="background:rgba(245,158,11,0.2);color:#fcd34d;border:1px solid rgba(245,158,11,0.4)">
                            ↺ Renew Watch
                        </button>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-800">
                                <p class="text-white text-xs font-semibold">Identity & Connection</p>
                            </div>
                            <div class="divide-y divide-gray-800/60">
                                @foreach($w->identity as $item)
                                    <div class="px-4 py-2.5 flex items-center justify-between">
                                        <span class="text-gray-500 text-xs">{{ $item['label'] }}</span>
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $item['ok'] ? 'bg-green-400' : 'bg-yellow-400' }}"></span>
                                            <span class="text-gray-300 text-xs text-right max-w-40 truncate">{{ $item['value'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-800 flex items-center justify-between">
                                <p class="text-white text-xs font-semibold">Worker Memory</p>
                                <a href="{{ route('workers.show', $w->dep->worker_slug) }}" class="text-brand text-xs hover:underline">Manage →</a>
                            </div>
                            <div class="divide-y divide-gray-800/60">
                                @foreach([
                                    ['Assets',    $w->memory['assets'],    'shared'],
                                    ['Contacts',  $w->memory['contacts'],  'shared'],
                                    ['Templates', $w->memory['templates'], 'owned'],
                                    ['Rules',     $w->memory['rules'],     'owned'],
                                ] as [$label, $count, $ownership])
                                    <div class="px-4 py-2.5 flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-gray-500 text-xs">{{ $label }}</span>
                                            <span class="text-xs px-1 rounded" style="{{ $ownership === 'shared' ? 'background:rgba(99,102,241,0.15);color:#818cf8' : 'background:rgba(75,85,99,0.2);color:#6b7280' }}">{{ $ownership }}</span>
                                        </div>
                                        <span class="text-gray-300 text-xs font-mono {{ $count === 0 ? 'text-yellow-600' : '' }}">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                            @if($w->contributionCount > 0)
                            @php $workerContribs = $w->contributionCount; @endphp
                            <div class="px-4 py-2 border-t border-gray-800/60" style="background:rgba(99,102,241,0.05)">
                                <p class="text-xs" style="color:#818cf8">
                                    ↑ {{ $workerContribs }} contribution{{ $workerContribs !== 1 ? 's' : '' }} to shared pool
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Pipeline health --}}
                    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-gray-800 flex items-center justify-between">
                            <div>
                                <p class="text-white text-sm font-semibold">Pipeline Health <span class="text-gray-600 text-xs font-normal">(historical — run Fast Track to test live)</span></p>
                                <p class="text-gray-600 text-xs mt-0.5">{{ $w->activity['tx_total'] }} total · {{ $w->activity['tx_today'] }} today · {{ $w->activity['tx_failed'] }} failed</p>
                            </div>
                            @if($w->activity['last_tx'])
                                <a href="{{ route('transactions.show', $w->activity['last_tx']->tx_id) }}" class="text-brand text-xs hover:underline">
                                    Last: {{ $w->activity['last_tx']->tx_id }} →
                                </a>
                            @endif
                        </div>
                        <div class="px-5 py-5 overflow-x-auto">
                            <div class="flex items-start min-w-max">
                                @foreach($w->jobMap as $key => $job)
                                    @php
                                        $jc = match($job['status'] ?? 'warn') {
                                            'ok'   => ['border' => 'border-green-500',  'bg' => 'bg-green-900/20',  'icon' => 'text-green-400',  'sym' => '✓'],
                                            'fail' => ['border' => 'border-red-500',    'bg' => 'bg-red-900/20',    'icon' => 'text-red-400',    'sym' => '✗'],
                                            default=> ['border' => 'border-gray-700',   'bg' => 'bg-gray-800/50',   'icon' => 'text-gray-600',   'sym' => '·'],
                                        };
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="w-28">
                                            <div class="border rounded-lg px-2.5 py-2.5 text-center {{ $jc['border'] }} {{ $jc['bg'] }}">
                                                <p class="text-xs font-bold {{ $jc['icon'] }}">{{ $jc['sym'] }} {{ $job['label'] }}</p>
                                                <p class="text-gray-600 text-xs mt-0.5 truncate">{{ $job['detail'] ?? '—' }}</p>
                                                @if(!empty($job['fail_count']) && $job['fail_count'] > 0)
                                                    <p class="text-red-400 text-xs">{{ $job['fail_count'] }}✗</p>
                                                @endif
                                            </div>
                                        </div>
                                        @if(!$loop->last)
                                            <div class="w-5 text-center text-gray-700 text-xs shrink-0">→</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Schema --}}
                    @php
                        $inputSchema  = json_decode($w->workerDef->input_schema  ?? 'null', true);
                        $outputSchema = json_decode($w->workerDef->output_schema ?? 'null', true);
                        $emitSchema   = json_decode($w->workerDef->emit_schema   ?? 'null', true);
                    @endphp
                    <div class="space-y-4">

                        {{-- Inputs --}}
                        @if(!empty($inputSchema['sections']))
                        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" style="background:#3b82f6"></span>
                                <p class="text-white text-xs font-semibold">Inputs</p>
                                <span class="ml-auto text-gray-600 text-xs">{{ count($inputSchema['sections']) }} trigger{{ count($inputSchema['sections']) !== 1 ? 's' : '' }}</span>
                            </div>
                            <div class="divide-y divide-gray-800">
                                @foreach($inputSchema['sections'] as $section)
                                <details class="group">
                                    <summary class="px-4 py-3 flex items-start gap-3 cursor-pointer list-none hover:bg-gray-800/40">
                                        <span class="mt-0.5 text-gray-600 group-open:text-gray-400 text-xs select-none">▶</span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-gray-300 text-xs font-medium">{{ $section['title'] }}</p>
                                            <p class="text-gray-600 text-xs mt-0.5">{{ $section['description'] }}</p>
                                        </div>
                                    </summary>
                                    <div class="px-4 pb-3">
                                        <pre class="bg-gray-950 border border-gray-800 rounded-lg p-3 text-xs overflow-x-auto" style="color:#86efac; font-family:monospace; line-height:1.5">{{ json_encode($section['sample'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </details>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Outputs --}}
                        @if(!empty($outputSchema['sections']))
                        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" style="background:#f59e0b"></span>
                                <p class="text-white text-xs font-semibold">Outputs</p>
                                <span class="ml-auto text-gray-600 text-xs">{{ count($outputSchema['sections']) }} output{{ count($outputSchema['sections']) !== 1 ? 's' : '' }}</span>
                            </div>
                            <div class="divide-y divide-gray-800">
                                @foreach($outputSchema['sections'] as $section)
                                <details class="group">
                                    <summary class="px-4 py-3 flex items-start gap-3 cursor-pointer list-none hover:bg-gray-800/40">
                                        <span class="mt-0.5 text-gray-600 group-open:text-gray-400 text-xs select-none">▶</span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-gray-300 text-xs font-medium">{{ $section['title'] }}</p>
                                            <p class="text-gray-600 text-xs mt-0.5">{{ $section['description'] }}</p>
                                        </div>
                                    </summary>
                                    <div class="px-4 pb-3">
                                        <pre class="bg-gray-950 border border-gray-800 rounded-lg p-3 text-xs overflow-x-auto" style="color:#fde68a; font-family:monospace; line-height:1.5">{{ json_encode($section['sample'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </details>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Emits (Break-Injections) --}}
                        @if(!empty($emitSchema['sections']))
                        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" style="background:#a855f7"></span>
                                <p class="text-white text-xs font-semibold">Emits <span style="color:#6b7280; font-weight:400">— Break-Injections</span></p>
                                <span class="ml-auto text-gray-600 text-xs">{{ count($emitSchema['sections']) }} event{{ count($emitSchema['sections']) !== 1 ? 's' : '' }}</span>
                            </div>
                            <div class="divide-y divide-gray-800">
                                @foreach($emitSchema['sections'] as $section)
                                <details class="group">
                                    <summary class="px-4 py-3 flex items-start gap-3 cursor-pointer list-none hover:bg-gray-800/40">
                                        <span class="mt-0.5 text-gray-600 group-open:text-gray-400 text-xs select-none">▶</span>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-0.5">
                                                <code class="text-xs px-1.5 py-0.5 rounded" style="background:rgba(168,85,247,0.15); color:#c084fc">{{ $section['event'] ?? '' }}</code>
                                                <span class="text-gray-600 text-xs">{{ $section['stage'] ?? ($section['title'] ?? '') }}</span>
                                            </div>
                                            <p class="text-gray-600 text-xs">{{ $section['description'] }}</p>
                                        </div>
                                    </summary>
                                    <div class="px-4 pb-3">
                                        <pre class="bg-gray-950 border border-gray-800 rounded-lg p-3 text-xs overflow-x-auto" style="color:#d8b4fe; font-family:monospace; line-height:1.5">{{ json_encode($section['sample'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                </details>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>

                    {{-- Pipeline Config --}}
                    @php
                        $pc = json_decode($w->dep->pipeline_config ?? '{}', true) ?: [];
                        $stagesMeta = [
                            'read'     => ['label' => 'Read Email',     'ai' => true,  'default_tokens' => 1024, 'default_timeout' => 90],
                            'classify' => ['label' => 'Classify',       'ai' => true,  'default_tokens' => 1024, 'default_timeout' => 90],
                            'memory'   => ['label' => 'Memory Lookup',  'ai' => true,  'default_tokens' => 768,  'default_timeout' => 90],
                            'template' => ['label' => 'Select Template','ai' => false, 'default_tokens' => null, 'default_timeout' => 30],
                            'draft'    => ['label' => 'Generate Draft', 'ai' => true,  'default_tokens' => 2048, 'default_timeout' => 90],
                            'push'     => ['label' => 'Push to Gmail',  'ai' => false, 'default_tokens' => null, 'default_timeout' => 60],
                        ];
                    @endphp
                    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                        <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')"
                            class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-gray-800/30 transition">
                            <div>
                                <p class="text-white text-sm font-semibold">Pipeline Config</p>
                                <p class="text-gray-500 text-xs mt-0.5">Max tokens · timeout · retries — per stage, applied at runtime</p>
                            </div>
                            <span class="text-gray-600 text-xs shrink-0 ml-4">▾ Edit</span>
                        </button>
                        <div class="hidden border-t border-gray-800">
                            <form method="POST" action="{{ route('qa.pipeline-config', $w->dep->id) }}">
                                @csrf
                                {{-- Column headers --}}
                                <div class="flex items-center gap-3 px-5 pt-4 pb-2 text-gray-600 text-xs font-semibold uppercase tracking-wider">
                                    <div style="width:180px">Stage</div>
                                    <div style="width:110px" class="text-center">Max Tokens</div>
                                    <div style="width:100px" class="text-center">Timeout (s)</div>
                                    <div style="width:80px"  class="text-center">Retries</div>
                                </div>
                                @foreach($stagesMeta as $stageKey => $sm)
                                    @php
                                        $saved   = $pc[$stageKey] ?? [];
                                        $tokens  = $saved['max_tokens'] ?? $sm['default_tokens'];
                                        $timeout = $saved['timeout']    ?? $sm['default_timeout'];
                                        $tries   = $saved['tries']      ?? 3;
                                        $inp     = 'bg-gray-800 border border-gray-700 rounded-lg px-2 py-1.5 text-white text-xs text-center font-mono focus:outline-none focus:border-brand transition w-full';
                                    @endphp
                                    <div class="flex items-center gap-3 px-5 py-2.5 border-t border-gray-800/60">
                                        {{-- Stage label --}}
                                        <div class="flex items-center gap-2 shrink-0" style="width:180px">
                                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $sm['ai'] ? 'bg-brand' : 'bg-gray-600' }}"></span>
                                            <span class="text-gray-300 text-xs font-semibold">{{ $sm['label'] }}</span>
                                            @if($sm['ai'])
                                                <span class="text-xs px-1.5 py-0.5 rounded bg-brand/15 text-brand leading-none">AI</span>
                                            @endif
                                        </div>
                                        {{-- Max tokens --}}
                                        <div class="shrink-0" style="width:110px">
                                            @if($sm['ai'])
                                                <input type="number" name="{{ $stageKey }}_max_tokens"
                                                    value="{{ $tokens }}" min="128" max="8192" step="128" class="{{ $inp }}">
                                            @else
                                                <input type="hidden" name="{{ $stageKey }}_max_tokens" value="">
                                                <p class="text-gray-700 text-xs text-center">—</p>
                                            @endif
                                        </div>
                                        {{-- Timeout --}}
                                        <div class="shrink-0" style="width:100px">
                                            <input type="number" name="{{ $stageKey }}_timeout"
                                                value="{{ $timeout }}" min="10" max="300" class="{{ $inp }}">
                                        </div>
                                        {{-- Retries --}}
                                        <div class="shrink-0" style="width:80px">
                                            <input type="number" name="{{ $stageKey }}_tries"
                                                value="{{ $tries }}" min="1" max="5" class="{{ $inp }}">
                                        </div>
                                    </div>
                                @endforeach
                                <div class="px-5 py-4 border-t border-gray-800 flex items-center justify-between">
                                    <p class="text-gray-600 text-xs">Changes take effect on next job dispatch · no Horizon restart needed</p>
                                    <button type="submit" class="text-sm font-bold px-5 py-2 rounded-lg text-gray-900 hover:opacity-90" style="background:var(--accent)">Save Config</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Fast Track scenario editor --}}
                    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                        <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')"
                            class="w-full px-5 py-4 flex items-center justify-between text-left">
                            <div>
                                <p class="text-white text-sm font-semibold">Fast Track Scenario</p>
                                <p class="text-gray-500 text-xs mt-0.5">{{ $sc->scenario_title ?? 'Domain Renewal Test' }} — edit the synthetic data injected during tests</p>
                            </div>
                            <span class="text-gray-600 text-xs shrink-0 ml-4">▾ Edit</span>
                        </button>
                        <div class="hidden border-t border-gray-800 px-5 py-5">
                            <form method="POST" action="{{ route('qa.scenario-update', $w->dep->id) }}" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @php
                                        $fields = [
                                            ['scenario_title',    'Scenario Title',       $sc->scenario_title    ?? 'Domain Renewal Test'],
                                            ['asset_name',        'Asset / Item Name',    $sc->asset_name        ?? 'yourdomain.com'],
                                            ['asset_type',        'Asset Type',           $sc->asset_type        ?? 'Domain'],
                                            ['renewal_price',     'Renewal Price',        $sc->renewal_price     ?? '$12.98/year'],
                                            ['sender_name',       'Sender Name',          $sc->sender_name       ?? 'Namecheap Renewals Team'],
                                            ['sender_email',      'Sender Email',         $sc->sender_email      ?? 'renewals@namecheap.com'],
                                            ['contact_name',      'Contact / Client',     $sc->contact_name      ?? $authUserName],
                                            ['days_until_expiry', 'Days Until Expiry',    (string)($sc->days_until_expiry ?? 14)],
                                        ];
                                    @endphp
                                    @foreach($fields as [$name, $label, $value])
                                        <div>
                                            <label class="text-gray-500 text-xs block mb-1.5">{{ $label }}</label>
                                            <input name="{{ $name }}" value="{{ $value }}" {{ $name === 'days_until_expiry' ? 'type=number min=1 max=365' : '' }}
                                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-brand transition">
                                        </div>
                                    @endforeach
                                </div>
                                <div>
                                    <label class="text-gray-500 text-xs block mb-1.5">Custom Note <span class="text-gray-700">(appended to email body)</span></label>
                                    <textarea name="custom_note" rows="2"
                                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-brand transition">{{ $sc->custom_note ?? '' }}</textarea>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <p class="text-gray-600 text-xs">Draft & email → <span class="text-gray-400">{{ $authUserEmail }}</span></p>
                                    <button type="submit" class="text-sm font-bold px-5 py-2 rounded-lg text-gray-900 hover:opacity-90" style="background:var(--accent)">Save Scenario</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>{{-- end wpanel --}}
            @endforeach
            </div>

        </div>{{-- end flex --}}
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         SHARED MEMORY MAP (bottom of Platform tab)
    ══════════════════════════════════════════════════════════════════ --}}
    @php
        $tableLabels = [
            'clients'  => ['icon' => '◈', 'color' => '#6366f1', 'label' => 'Clients'],
            'contacts' => ['icon' => '◉', 'color' => '#06b6d4', 'label' => 'Contacts'],
            'assets'   => ['icon' => '◆', 'color' => '#10b981', 'label' => 'Assets'],
        ];
    @endphp

    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
            <div>
                <p class="text-white text-sm font-semibold">Shared Memory Map</p>
                <p class="text-gray-600 text-xs mt-0.5">Tables shared across all deployed workers for this tenant — any worker can read, contributing workers write back discoveries</p>
            </div>
            @php $totalContributions = collect($memoryMap)->sum('total_contributions'); @endphp
            @if($totalContributions > 0)
            <span class="text-xs px-2 py-1 rounded-full" style="background:rgba(99,102,241,0.15);color:#818cf8">
                {{ $totalContributions }} total contributions
            </span>
            @endif
        </div>

        {{-- Three shared table cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-800">
            @foreach($memoryMap as $tbl => $info)
            @php $meta = $tableLabels[$tbl]; @endphp
            <div class="p-5">
                {{-- Table header --}}
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-lg" style="color:{{ $meta['color'] }}">{{ $meta['icon'] }}</span>
                    <div>
                        <p class="text-white text-sm font-semibold">{{ $meta['label'] }}</p>
                        <p class="text-gray-500 text-xs">{{ number_format($info['count']) }} records</p>
                    </div>
                </div>

                {{-- Workers with access --}}
                <div class="space-y-2 mb-4">
                    @if(!empty($info['readers']))
                        @foreach(array_unique($info['readers']) as $wName)
                        @php $canWrite = in_array($wName, $info['writers']); @endphp
                        <div class="flex items-center justify-between py-1.5 px-2.5 rounded-lg"
                             style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06)">
                            <span class="text-gray-300 text-xs">{{ $wName }}</span>
                            <div class="flex items-center gap-1">
                                <span class="text-xs px-1.5 py-0.5 rounded" style="background:rgba(99,102,241,0.15);color:#818cf8">read</span>
                                @if($canWrite)
                                <span class="text-xs px-1.5 py-0.5 rounded" style="background:rgba(16,185,129,0.15);color:#6ee7b7">write</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-gray-700 text-xs italic">No workers deployed yet</p>
                    @endif
                </div>

                {{-- Contribution activity --}}
                @if($info['total_contributions'] > 0)
                <div class="border-t border-gray-800 pt-3">
                    <p class="text-gray-600 text-xs mb-2">Recent contributions</p>
                    <div class="space-y-1.5">
                        @foreach($info['recent_contributions'] as $c)
                        @php $cd = json_decode($c->data, true); @endphp
                        <div class="flex items-start gap-2">
                            <span class="text-xs mt-0.5 shrink-0"
                                  style="color:{{ $c->action === 'created' ? '#6ee7b7' : '#93c5fd' }}">
                                {{ $c->action === 'created' ? '＋' : '↺' }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-gray-400 text-xs truncate">
                                    {{ $cd['name'] ?? $cd['email'] ?? 'record #'.$c->record_id }}
                                </p>
                                <p class="text-gray-700 text-xs">{{ $c->worker_slug }} · {{ \Carbon\Carbon::parse($c->created_at)->diffForHumans(null,true) }} ago</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-gray-700 text-xs mt-2">{{ $info['total_contributions'] }} total write{{ $info['total_contributions'] !== 1 ? 's' : '' }}</p>
                </div>
                @else
                <div class="border-t border-gray-800 pt-3">
                    <p class="text-gray-700 text-xs italic">No contributions yet — run a Fast Track to populate</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Per-worker contribution breakdown --}}
        @if($contributionsByWorker->isNotEmpty())
        <div class="border-t border-gray-800 px-5 py-4">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-3">Contribution Activity by Worker</p>
            <div class="space-y-2">
                @foreach($contributionsByWorker as $workerSlug => $rows)
                <div class="flex items-center gap-4">
                    <span class="text-gray-300 text-xs w-32 shrink-0">{{ $workerSlug }}</span>
                    <div class="flex items-center gap-2 flex-wrap">
                        @foreach($rows as $r)
                        <span class="text-xs px-2 py-0.5 rounded-full"
                              style="background:rgba(255,255,255,0.05);color:#9ca3af">
                            {{ $r->table_name }} · {{ $r->action }} · {{ $r->total }}×
                        </span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         SECURITY LAYER
    ══════════════════════════════════════════════════════════════════ --}}
    <div id="layer-security" class="layer-panel hidden space-y-4">

        @php
            $secCategories = collect($security)->groupBy('category');
            $catMeta = [
                'Encryption'  => ['icon' => '🔒', 'desc' => 'Data at rest and in transit'],
                'Transport'   => ['icon' => '🌐', 'desc' => 'HTTPS / TLS enforcement'],
                'Auth'        => ['icon' => '🔑', 'desc' => 'Session and authentication state'],
                'Credentials' => ['icon' => '🗝', 'desc'  => 'OAuth tokens and third-party keys'],
                'Isolation'   => ['icon' => '⬡',  'desc' => 'Tenant and worker data boundaries'],
                'Keys'        => ['icon' => '⚙',  'desc' => 'API keys and service credentials'],
                'Compliance'  => ['icon' => '✓',  'desc' => 'Operational compliance checks'],
            ];
        @endphp

        @foreach($secCategories as $cat => $checks)
            @php
                $meta   = $catMeta[$cat] ?? ['icon' => '●', 'desc' => ''];
                $allOk  = $checks->every(fn($c) => $c['status'] === 'ok');
                $hasErr = $checks->contains(fn($c) => $c['status'] === 'fail');
                $groupStatus = $hasErr ? 'fail' : ($allOk ? 'ok' : 'warn');
            @endphp
            <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800 flex items-center gap-3">
                    <span class="text-xl">{{ $meta['icon'] }}</span>
                    <div class="flex-1">
                        <p class="text-white text-sm font-semibold">{{ $cat }}</p>
                        <p class="text-gray-600 text-xs">{{ $meta['desc'] }}</p>
                    </div>
                    <span class="w-2.5 h-2.5 rounded-full {{ $groupStatus === 'ok' ? 'bg-green-400 animate-pulse' : ($groupStatus === 'warn' ? 'bg-yellow-400' : 'bg-red-500') }}"></span>
                </div>
                <div class="divide-y divide-gray-800">
                    @foreach($checks as $check)
                        @php $cc = match($check['status']) { 'ok' => ['dot' => 'bg-green-400', 'text' => 'text-green-400', 'badge' => 'OK'], 'warn' => ['dot' => 'bg-yellow-400', 'text' => 'text-yellow-400', 'badge' => 'WARN'], default => ['dot' => 'bg-red-500', 'text' => 'text-red-400', 'badge' => 'FAIL'] }; @endphp
                        <div class="px-5 py-4 flex items-center gap-4">
                            <span class="w-2 h-2 rounded-full shrink-0 {{ $cc['dot'] }}"></span>
                            <div class="flex-1">
                                <p class="text-white text-xs font-semibold">{{ $check['label'] }}</p>
                                <p class="text-gray-500 text-xs mt-0.5">{{ $check['detail'] }}</p>
                            </div>
                            <span class="text-xs font-bold {{ $cc['text'] }} shrink-0">{{ $cc['badge'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Architecture compliance --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                <div>
                    <p class="text-white text-sm font-semibold">Platform Architecture Compliance</p>
                    <p class="text-gray-600 text-xs mt-0.5">Structural guarantees that protect tenants and enable safe worker decommission</p>
                </div>
                <a href="{{ route('qa.platform-blueprint') }}"
                   class="flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-300 hover:border-brand hover:text-brand transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Blueprint
                </a>
            </div>
            <div class="divide-y divide-gray-800">
                @php
                    $principles = [
                        ['Plug & Play Workers',     'Workers are self-contained services — decommission has zero impact on platform or other workers.'],
                        ['Schema-Driven I/O',        'Workers declare I/O contracts via JSON schema. UNIT routes data based on contracts, no tight coupling.'],
                        ['Tenant Data Isolation',    'All queries scoped by user_id and deployment_id. Cross-tenant access blocked at the application layer.'],
                        ['Credential Encryption',    'OAuth tokens stored encrypted at rest. Never logged, never returned in API responses.'],
                        ['Queue-Based Processing',   'Jobs run through isolated per-worker queues (slug-{id}). One worker failure cannot block others.'],
                        ['Decommission Safety',      'Workers can be paused or removed without data loss. Transactions, memory, and logs are always retained.'],
                        ['Billing by Deployment',    'Tenant billing is tied to deployed worker count — no workers deployed, no charges.'],
                    ];
                @endphp
                @foreach($principles as [$label, $desc])
                    <div class="px-5 py-3.5 flex items-start gap-4">
                        <span class="w-2 h-2 rounded-full shrink-0 mt-1.5 bg-green-400"></span>
                        <div>
                            <p class="text-white text-xs font-semibold">{{ $label }}</p>
                            <p class="text-gray-500 text-xs mt-0.5 leading-relaxed">{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ── LIVE PIPELINE MODAL ────────────────────────────────────────────── --}}
    <div id="pipeline-modal"
         x-data="{ shown: false, currentTxId: '' }"
         x-show="shown"
         x-cloak
         @open-pipeline.window="shown = true; currentTxId = $event.detail.txId"
         @close-pipeline.window="shown = false; currentTxId = ''"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,0.8)">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-5xl shadow-2xl">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <p class="text-white font-bold">Fast Track Pipeline</p>
                    </div>
                    <p class="text-gray-500 text-xs font-mono mt-0.5" x-text="currentTxId"></p>
                </div>
                <button @click="$dispatch('close-pipeline')" class="text-gray-600 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-6 py-2">
                <x-pipeline-tracker txId="" context="dashboard" />
            </div>
        </div>
    </div>

    <script>
    // ── Layer tabs
    function switchLayer(id) {
        document.querySelectorAll('.layer-tab').forEach(t => {
            t.classList.remove('bg-gray-800', 'text-white');
            t.classList.add('text-gray-400', 'hover:text-white');
        });
        document.querySelectorAll('.layer-panel').forEach(p => p.classList.add('hidden'));
        const tab = document.getElementById('layer-tab-' + id);
        tab.classList.remove('text-gray-400', 'hover:text-white');
        tab.classList.add('bg-gray-800', 'text-white');
        document.getElementById('layer-' + id).classList.remove('hidden');
    }

    // ── Worker tabs
    function selectWorker(id) {
        document.querySelectorAll('.wtab').forEach(t => {
            t.classList.remove('border-brand', 'bg-brand/10', 'text-white');
            t.classList.add('border-gray-800', 'bg-gray-900', 'text-gray-400');
        });
        document.querySelectorAll('.wpanel').forEach(p => p.classList.add('hidden'));
        const tab = document.getElementById('wtab-' + id);
        tab.classList.remove('border-gray-800', 'bg-gray-900', 'text-gray-400');
        tab.classList.add('border-brand', 'bg-brand/10', 'text-white');
        document.getElementById('wpanel-' + id)?.classList.remove('hidden');
    }

    // ── Pipeline modal — delegates to the Alpine pipeline-tracker component via events
    function openPipelineModal(txId) {
        window.dispatchEvent(new CustomEvent('open-pipeline', { detail: { txId } }));
        const url = new URL(window.location);
        url.searchParams.set('watch', txId);
        window.history.replaceState({}, '', url);
    }

    function closePipelineModal() {
        window.dispatchEvent(new CustomEvent('close-pipeline'));
        const url = new URL(window.location);
        url.searchParams.delete('watch');
        url.searchParams.delete('worker');
        window.history.replaceState({}, '', url);
    }

    @if(request('watch'))
        document.addEventListener('DOMContentLoaded', () => {
            switchLayer('workers');
            @if(request('worker'))
                selectWorker({{ (int) request('worker') }});
            @endif
            openPipelineModal('{{ request("watch") }}');
        });
    @elseif(request('worker'))
        document.addEventListener('DOMContentLoaded', () => {
            switchLayer('workers');
            selectWorker({{ (int) request('worker') }});
        });
    @endif

    // ── Worker queue controls
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

    async function workerAction(depId, action) {
        const labels = { pause: 'Pausing…', resume: 'Resuming…', drain: 'Draining…' };
        const btn = event.target;
        const orig = btn.textContent;
        btn.textContent = labels[action] || '…';
        btn.disabled = true;
        try {
            await fetch(`/qa/worker/${depId}/${action}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            if (action === 'drain') {
                btn.textContent = '✓ Drained';
                setTimeout(() => { btn.textContent = orig; btn.disabled = false; }, 2000);
            } else {
                window.location.reload();
            }
        } catch(e) {
            btn.textContent = '✗ Error'; btn.disabled = false;
        }
    }

    async function restartHorizon() {
        const btn = event.target;
        btn.textContent = 'Restarting…'; btn.disabled = true;
        try {
            await fetch('/qa/horizon/restart', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            btn.textContent = '✓ Signal Sent';
            document.getElementById('horizon-restart-bar').classList.add('hidden');
        } catch(e) {
            btn.textContent = '✗ Error'; btn.disabled = false;
        }
    }

    async function pollQueueStats() {
        document.querySelectorAll('[id^="qstats-"]').forEach(async el => {
            const depId = el.id.replace('qstats-', '');
            try {
                const res = await fetch(`/qa/worker/${depId}/queue-status`, {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const d = await res.json();
                const paused = d.paused ? '<span class="text-yellow-500">⏸ paused</span> · ' : '';
                el.innerHTML = `${paused}${d.pending} pending · ${d.reserved} running`;
            } catch(e) { el.textContent = '—'; }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        pollQueueStats();
        setInterval(pollQueueStats, 5000);
    });

    // ── Blueprint toggle
    function toggleBlueprint(workerId) {
        const panel   = document.getElementById('blueprint-' + workerId);
        const chevron = document.getElementById('bp-chevron-' + workerId);
        const hidden  = panel.classList.toggle('hidden');
        chevron.textContent = hidden ? '▶' : '▼';
    }

    async function renewWatch(depId) {
        const btn = document.getElementById('renew-watch-' + depId);
        btn.textContent = 'Renewing…';
        btn.disabled = true;
        try {
            const res = await fetch(`/qa/worker/${depId}/renew-watch`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.error) throw new Error(data.error);
            btn.textContent = '✓ Renewed';
            btn.style.color = '#86efac';
            btn.style.borderColor = 'rgba(34,197,94,0.4)';
            btn.style.background = 'rgba(34,197,94,0.1)';
            btn.closest('.flex').querySelector('span:last-of-type').textContent = data.message;
        } catch(e) {
            btn.textContent = 'Failed — retry';
            btn.disabled = false;
        }
    }

    async function recoverStuck() {
        const btn = document.getElementById('recover-btn');
        btn.textContent = 'Recovering…';
        btn.disabled = true;
        try {
            const res = await fetch('{{ route('qa.recover-stuck') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            });
            const data = await res.json();
            btn.textContent = `✓ ${data.recovered} re-dispatched`;
            btn.style.color = '#86efac';
            btn.style.borderColor = 'rgba(34,197,94,0.4)';
            btn.style.background = 'rgba(34,197,94,0.1)';
        } catch(e) {
            btn.textContent = 'Error — retry';
            btn.disabled = false;
        }
    }
    </script>

</x-app-layout>

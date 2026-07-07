<x-app-layout title="System QA">

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Page header --}}
    <div class="mb-6">
        <h1 class="text-white text-lg font-bold">System QA & Architecture</h1>
        <p class="text-gray-500 text-xs mt-0.5">Platform health · Worker marketplace · AI engine · Queue state</p>
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
    <div class="overflow-x-auto mb-6">
        <div class="flex gap-1 bg-gray-900 border border-gray-800 p-1 rounded-xl w-fit min-w-max">
            <button onclick="switchLayer('platform')" id="layer-tab-platform"
                class="layer-tab px-4 py-2 rounded-lg text-sm font-semibold transition bg-gray-800 text-white whitespace-nowrap">
                Platform
            </button>
            <button onclick="switchLayer('marketplace')" id="layer-tab-marketplace"
                class="layer-tab px-4 py-2 rounded-lg text-sm font-semibold transition text-gray-400 hover:text-white whitespace-nowrap">
                Marketplace
            </button>
        </div>
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
                <div class="px-5 py-4 border-b border-gray-800 flex flex-wrap items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold shrink-0" style="background:rgba(var(--accent-rgb),0.15);color:var(--accent)">
                        {{ strtoupper(substr($w->slug, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-semibold text-sm">{{ $w->name }}</p>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $w->description ?? 'No description yet.' }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
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
                                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold text-center" style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }}">{{ $file['role'] ?? '' }}</span>
                                            <span class="text-xs leading-relaxed" style="color:#9ca3af">{{ $file['description'] ?? '' }}</span>
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
                                                <span class="text-xs font-bold font-mono" style="color:#6ee7b7">{{ $mem['table'] ?? '—' }}</span>
                                                <span class="text-xs border border-gray-700 rounded px-1.5" style="color:#9ca3af">{{ $mem['scope'] ?? '' }}</span>
                                                <span class="text-xs ml-auto px-1.5 rounded" style="background:rgba(16,185,129,0.15);color:#6ee7b7">{{ $mem['access'] ?? 'read' }}</span>
                                            </div>
                                            <p class="text-gray-400 text-xs">{{ $mem['description'] ?? '' }}</p>
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
                                                <span class="text-xs font-bold font-mono" style="color:#c4b5fd">{{ $mem['table'] ?? '—' }}</span>
                                                <span class="text-xs border border-gray-700 rounded px-1.5" style="color:#9ca3af">{{ $mem['scope'] ?? '' }}</span>
                                                <span class="text-xs ml-auto px-1.5 rounded" style="background:rgba(75,85,99,0.2);color:#9ca3af">{{ $mem['access'] ?? 'read' }}</span>
                                            </div>
                                            <p class="text-gray-400 text-xs">{{ $mem['description'] ?? '' }}</p>
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
                                    @foreach(($bp['config']['required'] ?? []) as $cfg)
                                        <div class="px-4 py-3 border-b border-gray-800/60 last:border-0 flex items-start gap-3">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0 mt-1.5"></span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-mono" style="color:#fde68a">{{ $cfg['key'] ?? '' }}</p>
                                                <p class="text-gray-400 text-xs">{{ $cfg['description'] ?? '' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                    @foreach(($bp['config']['optional'] ?? []) as $cfg)
                                        <div class="px-4 py-3 border-b border-gray-800/60 last:border-0 flex items-start gap-3">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-600 shrink-0 mt-1.5"></span>
                                            <div class="min-w-0">
                                                <p class="text-gray-400 text-xs font-mono">{{ $cfg['key'] ?? '' }}</p>
                                                <p class="text-gray-400 text-xs">{{ $cfg['description'] ?? '' }} <span class="text-gray-500">(optional)</span></p>
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

    <x-self-learn
        page-key="admin.qa"
        title="QA Center"
        body="This page gives you platform-wide health visibility — AI engine status, queue state, Stripe connectivity, Gmail watches, and the worker marketplace. Use it to diagnose pipeline issues, publish workers, and monitor platform health. Stuck transactions and pending drafts shown here are across all tenants."
    />

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

    // ── Blueprint toggle
    function toggleBlueprint(workerId) {
        const panel   = document.getElementById('blueprint-' + workerId);
        const chevron = document.getElementById('bp-chevron-' + workerId);
        const hidden  = panel.classList.toggle('hidden');
        chevron.textContent = hidden ? '▶' : '▼';
    }

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

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

    async function recoverStuck() {
        const btn = document.getElementById('recover-btn');
        btn.textContent = 'Recovering…';
        btn.disabled = true;
        try {
            const res = await fetch('{{ route('qa.recover-stuck') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
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

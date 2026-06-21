<x-app-layout title="{{ $dep->name }}">

    @include('partials.worker-subnav')

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @php $config = json_decode($dep->config, true) ?? []; @endphp

    {{-- ── Policy Violations ───────────────────────────────────────────────── --}}
    @if(!empty($policyViolations))
    <div class="mb-5">
        @include('partials.policy-violations', ['violations' => $policyViolations])
    </div>
    @endif

    {{-- ── Production Readiness ───────────────────────────────────────────── --}}
    @if($connectedInboxes->isEmpty())
    <div class="mb-4 flex items-start justify-between gap-4 px-5 py-4 rounded-xl border"
         style="background:rgba(239,68,68,0.06);border-color:rgba(239,68,68,0.3)">
        <div class="flex items-start gap-3">
            <span class="text-red-400 mt-0.5 shrink-0">⛔</span>
            <div>
                <p class="text-red-300 font-semibold text-sm">Not production ready — no inbox connected</p>
                <p class="text-gray-400 text-xs mt-0.5 leading-relaxed">
                    This worker has no Gmail inbox connected via the Connect tab.
                    Real emails will not be received or processed until you connect an inbox.
                    @if($credential)
                        Fast Track can still run using <span class="text-gray-300">{{ $credential->gmail_address }}</span>
                        (a credential on your account), but that account is <strong>not monitoring this worker</strong> in production.
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('workers.connect', $dep->id) }}"
           class="shrink-0 text-xs px-3 py-1.5 rounded-lg border border-red-700 text-red-400 hover:bg-red-900/20 transition font-medium">
            Connect Inbox →
        </a>
    </div>
    @endif

    {{-- ── Operational Alerts ──────────────────────────────────────────────── --}}
    @if($pendingReview > 0 || $stuckCount > 0)
    <div class="space-y-2 mb-4">
        @if($pendingReview > 0)
        <div class="flex items-center justify-between px-4 py-3 rounded-xl border"
             style="background:rgba(243,197,49,0.08);border-color:rgba(243,197,49,0.35)">
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full animate-pulse" style="background:#a78bfa"></span>
                <span class="text-sm" style="color:#f3c531">
                    <strong>{{ $pendingReview }}</strong> draft{{ $pendingReview !== 1 ? 's' : '' }} awaiting your review
                </span>
            </div>
            <a href="{{ route('transactions', ['filter' => 'draft_ready']) }}"
               class="text-xs px-3 py-1.5 rounded-lg font-medium transition"
               style="background:rgba(243,197,49,0.20);color:#f3c531;border:1px solid rgba(243,197,49,0.45)">
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

    <div class="grid grid-cols-3 gap-6">

        {{-- Left: stats + recent activity --}}
        <div class="col-span-2 space-y-4">

            {{-- Status bar --}}
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-brand/15 rounded-lg flex items-center justify-center">
                        <span class="text-brand font-bold">{{ strtoupper(substr($dep->worker_slug, 0, 1)) }}</span>
                    </div>
                    <div>
                        <p class="text-white font-semibold">{{ $dep->name }}</p>
                        <p class="text-gray-500 text-xs">{{ $dep->worker_slug }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($dep->status === 'active')
                        <span class="flex items-center gap-1.5 text-xs text-green-400 bg-green-900 border border-green-800 px-2 py-1 rounded">
                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span> Active
                        </span>
                        <form method="POST" action="{{ route('workers.status', $dep->id) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="paused">
                            <button class="text-xs text-yellow-500 border border-yellow-800 rounded px-3 py-1 hover:bg-yellow-900">Pause</button>
                        </form>
                    @elseif($dep->status === 'paused')
                        <span class="flex items-center gap-1.5 text-xs text-yellow-400 bg-yellow-900 border border-yellow-800 px-2 py-1 rounded">
                            <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full"></span> Paused
                        </span>
                        <form method="POST" action="{{ route('workers.status', $dep->id) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="active">
                            <button class="text-xs text-green-500 border border-green-800 rounded px-3 py-1 hover:bg-green-900">Resume</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('workers.destroy', $dep->id) }}" onsubmit="return confirm('Remove this worker?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-gray-600 hover:text-red-400 border border-gray-700 rounded px-3 py-1">Remove</button>
                    </form>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                    <p class="text-gray-500 text-xs">Transactions</p>
                    <p class="text-white text-2xl font-semibold mt-1">{{ $txCount }}</p>
                </div>
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                    <p class="text-gray-500 text-xs">Tokens Used</p>
                    <p class="text-white text-2xl font-semibold mt-1">{{ number_format($usage->tokens ?? 0) }}</p>
                </div>
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-4">
                    <p class="text-gray-500 text-xs">AI Cost</p>
                    <p class="text-white text-2xl font-semibold mt-1">${{ number_format($usage->cost ?? 0, 4) }}</p>
                </div>
            </div>

            {{-- Fast Track --}}
            @php
                $ftUses      = (int) ($config['fast_track_uses'] ?? 0);
                $ftMax       = 10;
                $ftLeft      = max(0, $ftMax - $ftUses);
                $ftBilling   = \Illuminate\Support\Facades\DB::table('deployment_billing')->where('deployment_id', $dep->id)->first();
                $ftSubscribed = $ftBilling && $ftBilling->status === 'active';
                $watchTxId   = request('watch');
            @endphp
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-white text-sm font-semibold">Fast Track Test</h3>
                        <p class="text-gray-500 text-xs mt-0.5">Runs a simulated email through the full pipeline — draft appears in the selected inbox's Drafts folder</p>
                    </div>
                    <div class="text-right">
                        @if($ftSubscribed)
                            <span class="text-xs text-green-400 font-medium">Unlimited · Active Plan</span>
                        @else
                            <span class="text-xs font-mono {{ $ftLeft > 0 ? 'text-gray-400' : 'text-red-400' }}">
                                {{ $ftLeft }}/{{ $ftMax }} trial runs left
                            </span>
                            <div class="mt-1 h-1.5 w-24 bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all"
                                     style="width:{{ ($ftUses / $ftMax) * 100 }}%;background:{{ $ftLeft > 3 ? '#a78bfa' : ($ftLeft > 0 ? '#f59e0b' : '#ef4444') }}"></div>
                            </div>
                        @endif
                    </div>
                </div>

                @if($ftSubscribed || $ftLeft > 0)
                    @php
                        // Effective credential for fast track: connected inbox first, then dep fallback
                        $ftInbox = $connectedInboxes->firstWhere('is_primary', true)
                                ?? $connectedInboxes->first();
                        $ftFallback = ($ftInbox === null && $credential) ? $credential : null;
                        $ftCanRun   = $ftInbox !== null || $ftFallback !== null;
                    @endphp
                    <form method="POST" action="{{ route('workers.fast-track', $dep->id) }}" class="space-y-2">
                        @csrf
                        {{-- Inbox selector: only show when multiple inboxes connected --}}
                        @if($connectedInboxes->count() > 1)
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Run on inbox</label>
                            <select name="credential_id"
                                    class="w-full bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:border-yellow-400 focus:outline-none">
                                @foreach($connectedInboxes as $inbox)
                                    <option value="{{ $inbox->id }}" {{ $inbox->is_primary ? 'selected' : '' }}>
                                        {{ $inbox->gmail_address }}{{ $inbox->is_primary ? ' (Primary)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @elseif($ftInbox)
                            <p class="text-gray-600 text-xs">
                                Will run on <span class="text-gray-400">{{ $ftInbox->gmail_address }}</span>
                            </p>
                            <input type="hidden" name="credential_id" value="{{ $ftInbox->id }}">
                        @elseif($ftFallback)
                            {{-- Fallback: dep->credential_id is set but not in deployment_credentials --}}
                            <div class="px-3 py-2 rounded-lg border border-amber-800/50 text-xs"
                                 style="background:rgba(120,53,15,0.15)">
                                <p class="text-amber-400 font-medium">⚠ Using fallback credential</p>
                                <p class="text-amber-200/70 mt-0.5">
                                    Fast Track will use <strong>{{ $ftFallback->gmail_address }}</strong> — but this inbox is not connected to this worker via the Connect tab.
                                    Production emails will not be processed until you
                                    <a href="{{ route('workers.connect', $dep->id) }}" class="underline hover:text-amber-300">connect it properly</a>.
                                </p>
                            </div>
                        @endif

                        @if($ftCanRun)
                        <button type="submit" id="ft-submit-btn"
                                onclick="this.disabled=true;this.innerHTML='<span style=\'opacity:0.6\'>Running…</span>';this.form.submit();"
                                class="w-full text-sm font-bold px-4 py-2.5 rounded-xl text-gray-900 hover:opacity-90 flex items-center justify-center gap-2"
                                style="background:#F5C100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Run Fast Track{{ $ftFallback ? ' (fallback)' : '' }}
                        </button>
                        @else
                        <div class="w-full text-center text-xs text-gray-600 py-2.5 rounded-xl border border-gray-800">
                            No inbox available — <a href="{{ route('workers.connect', $dep->id) }}" class="text-brand hover:text-brand">connect one</a> to run Fast Track
                        </div>
                        @endif
                    </form>
                @else
                    <div class="bg-red-900/20 border border-red-900/50 rounded-xl px-4 py-3 text-center space-y-2">
                        <p class="text-red-400 text-xs font-semibold">Trial Fast Track limit reached (10/10)</p>
                        <p class="text-gray-500 text-xs">Subscribe to unlock unlimited runs, or contact support to reset your trial counter.</p>
                        <a href="{{ route('billing.checkout', $dep->id) }}"
                           class="inline-block text-xs px-4 py-1.5 rounded-lg font-medium text-gray-900 hover:opacity-90 transition"
                           style="background:#F5C100">
                            Upgrade to subscription →
                        </a>
                    </div>
                @endif
            </div>

            {{-- Pipeline modal (auto-opens when ?watch= is present) --}}
            <div id="pipeline-modal" class="fixed inset-0 z-50 flex items-center justify-center {{ $watchTxId ? '' : 'hidden' }}"
                 style="background:rgba(2,4,10,0.85);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px)">
                <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-2xl"
                     style="width:calc(100vw - 48px);max-width:1000px">

                    {{-- Header --}}
                    <div class="px-7 py-4 border-b border-gray-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                 style="background:rgba(245,193,0,0.12);border:1px solid rgba(245,193,0,0.25)">
                                <svg class="w-4 h-4" fill="none" stroke="#F5C100" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-semibold text-sm">Fast Track Pipeline</p>
                                <p id="modal-tx-id" class="text-gray-600 text-xs font-mono">{{ $watchTxId ?? '' }}</p>
                            </div>
                        </div>
                        <button onclick="closePipelineModal()"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-600 hover:text-white hover:bg-gray-800 transition text-sm">✕</button>
                    </div>

                    {{-- Pipeline flow --}}
                    @php
                    $pipelineSteps = [
                        'ingest'   => ['label'=>'Inject & Fetch',   'sub'=>'Insert into inbox, read back',  'icon'=>'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
                        'read'     => ['label'=>'Read Email',       'sub'=>'Parse & extract fields',        'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        'classify' => ['label'=>'Classify',         'sub'=>'Category, priority & type',    'icon'=>'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                        'memory'   => ['label'=>'Memory Lookup',    'sub'=>'Match client, asset & rules',  'icon'=>'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18'],
                        'log'      => ['label'=>'Log Transaction',   'sub'=>'Write to register',            'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        'template' => ['label'=>'Select Template',   'sub'=>'Pick best-match template',     'icon'=>'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                        'draft'    => ['label'=>'Draft Email',       'sub'=>'AI-personalised draft',        'icon'=>'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z'],
                        'push'     => ['label'=>'Push to Gmail',     'sub'=>'Create draft in inbox',        'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ];
                    @endphp

                    <div class="px-6 pt-8 pb-5">
                        <div class="flex items-start justify-between">
                            @foreach($pipelineSteps as $key => $step)
                                {{-- Step --}}
                                <div id="stage-{{ $key }}" class="flex flex-col items-center flex-1">
                                    {{-- Circle --}}
                                    <div class="stage-bubble w-14 h-14 rounded-full border-2 flex items-center justify-center relative transition-all duration-300"
                                         style="border-color:#2d3748;background:#0d1117">
                                        {{-- Pending icon --}}
                                        <svg class="stage-icon w-6 h-6 transition-colors duration-300 absolute" style="color:#374151" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $step['icon'] }}"/>
                                        </svg>
                                        {{-- Checkmark --}}
                                        <svg class="stage-check w-7 h-7 hidden absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{-- Error X --}}
                                        <svg class="stage-x w-6 h-6 hidden absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </div>
                                    {{-- Label --}}
                                    <p class="stage-label text-xs font-semibold text-center mt-3 leading-tight px-1" style="color:#4b5563">{{ $step['label'] }}</p>
                                    <p class="text-gray-700 text-xs text-center mt-1 leading-tight px-1 hidden sm:block">{{ $step['sub'] }}</p>
                                    {{-- Status badge --}}
                                    <p class="stage-badge-text text-xs font-mono mt-2" style="color:#2d3748">·</p>
                                </div>

                                {{-- Arrow connector --}}
                                @if(!$loop->last)
                                <div id="arrow-{{ $key }}" class="flex items-center shrink-0" style="padding-bottom:48px;width:32px">
                                    <svg viewBox="0 0 32 10" fill="none" class="w-full">
                                        <path d="M0 5 H24 M20 1 L30 5 L20 9" stroke="#2d3748" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="arrow-path"/>
                                    </svg>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Status bar --}}
                    <div class="mx-6 mb-5 rounded-xl border px-5 py-3 flex items-center gap-3 transition-all duration-500"
                         id="pipeline-status-bar" style="background:rgba(255,255,255,0.02);border-color:#1f2937">
                        <div id="pipeline-spinner" class="{{ $watchTxId ? '' : 'hidden' }} shrink-0">
                            <svg class="animate-spin w-4 h-4" style="color:#f3c531" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>
                        <span id="pipeline-overall" class="text-sm font-medium flex-1" style="color:#6b7280">
                            {{ $watchTxId ? 'Initialising pipeline…' : '' }}
                        </span>
                        <button onclick="closePipelineModal()" class="text-xs text-gray-600 hover:text-gray-400 shrink-0">Close</button>
                    </div>

                </div>
            </div>

            {{-- Recent transactions --}}
            <div class="bg-gray-900 border border-gray-800 rounded-xl">
                <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                    <h3 class="text-white text-sm font-semibold">Recent Activity</h3>
                    <a href="{{ route('transactions') }}" class="text-xs text-gray-500 hover:text-brand">View all →</a>
                </div>
                @forelse($recentTx as $tx)
                    <div class="px-5 py-3 border-b border-gray-800 last:border-0 flex items-center justify-between">
                        <div>
                            <a href="{{ route('transactions.show', $tx->tx_id) }}" class="text-brand text-xs font-mono hover:underline">{{ $tx->tx_id }}</a>
                            <span class="text-gray-500 text-xs ml-2">{{ $tx->category }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($tx->created_at)->diffForHumans() }}</span>
                            <span class="text-xs px-2 py-0.5 rounded {{ $tx->status === 'draft_ready' ? 'bg-brand/15 text-brand' : ($tx->status === 'failed' ? 'bg-red-900 text-red-300' : 'bg-gray-800 text-gray-400') }}">{{ $tx->status }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-600 text-sm">No transactions yet for this worker.</div>
                @endforelse
            </div>

            {{-- Connected Accounts summary — links to Connect tab --}}
            @php
                $credContract = $contract?->credential() ?? [];
                $credLabel    = $credContract['label'] ?? 'Account';
                $watchInactive = $connectedInboxes->where('watch_active', false)->count();
            @endphp
            <a href="{{ route('workers.connect', $dep->id) }}"
               class="block bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl px-5 py-4 transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white text-sm font-semibold group-hover:text-brand transition">
                            Connected {{ $credLabel }}s
                        </p>
                        <p class="text-gray-500 text-xs mt-0.5">
                            @if($connectedInboxes->isEmpty())
                                <span class="text-red-400">⛔ No inbox connected — not production ready</span>
                            @else
                                {{ $connectedInboxes->count() }} connected
                                @if($watchInactive > 0)
                                    · <span class="text-yellow-400">{{ $watchInactive }} watch inactive</span>
                                @else
                                    · <span class="text-green-400">all watching</span>
                                @endif
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        @foreach($connectedInboxes->take(3) as $inbox)
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0
                                        {{ $inbox->watch_active ? 'bg-green-900 ring-1 ring-green-700' : 'bg-yellow-900 ring-1 ring-yellow-700' }}">
                                {{ strtoupper(substr($inbox->gmail_address, 0, 1)) }}
                            </div>
                        @endforeach
                        @if($connectedInboxes->count() > 3)
                            <span class="text-gray-600 text-xs">+{{ $connectedInboxes->count() - 3 }}</span>
                        @endif
                        <svg class="w-4 h-4 text-gray-600 group-hover:text-brand transition ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

        </div>

        {{-- Right: quick links --}}
        <div class="space-y-3">

            @php $config2 = json_decode($dep->config, true) ?? []; @endphp

            {{-- Configure link --}}
            <a href="{{ route('workers.configure', $dep->id) }}"
               class="flex items-center justify-between bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl px-4 py-3.5 transition group">
                <div>
                    <p class="text-white text-sm font-medium group-hover:text-brand transition">Configuration</p>
                    <p class="text-gray-500 text-xs mt-0.5">
                        {{ $config2['ai_model'] ?? 'claude-sonnet-4-6' }}
                        · {{ $config2['capture_scope'] ?? 'All emails' }}
                    </p>
                </div>
                <svg class="w-4 h-4 text-gray-600 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Memory link --}}
            <a href="{{ route('workers.memory', $dep->id) }}"
               class="flex items-center justify-between bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl px-4 py-3.5 transition group">
                <div>
                    <p class="text-white text-sm font-medium group-hover:text-brand transition">Memory</p>
                    <p class="text-gray-500 text-xs mt-0.5">Clients · Contacts · Assets</p>
                </div>
                <svg class="w-4 h-4 text-gray-600 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Rules link --}}
            <a href="{{ route('workers.rules', $dep->id) }}"
               class="flex items-center justify-between bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl px-4 py-3.5 transition group">
                <div>
                    <p class="text-white text-sm font-medium group-hover:text-brand transition">Rules</p>
                    <p class="text-gray-500 text-xs mt-0.5">Processing & action rules</p>
                </div>
                <svg class="w-4 h-4 text-gray-600 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Schema link --}}
            <a href="{{ route('workers.schema', $dep->id) }}"
               class="flex items-center justify-between bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl px-4 py-3.5 transition group">
                <div>
                    <p class="text-white text-sm font-medium group-hover:text-brand transition">Pipeline Schema</p>
                    <p class="text-gray-500 text-xs mt-0.5">Input · Pipeline · Emit</p>
                </div>
                <svg class="w-4 h-4 text-gray-600 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

        </div>

    </div>

    <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

    async function recoverStuck() {
        const btn = document.getElementById('recover-btn');
        if (!btn) return;
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

    // ── Pipeline modal polling ──────────────────────────────────────────────
    const WATCH_TX = '{{ $watchTxId ?? '' }}';
    const STAGE_ORDER = ['ingest','read','classify','memory','log','template','draft','push'];
    const STAGE_STATUS_MAP = {
        ingesting:        'ingest',
        reading:          'read',
        classifying:      'classify',
        memory_lookup:    'memory',
        logging:          'log',
        template_select:  'template',
        drafting:         'draft',
        pushing:          'push',
        draft_ready:      'push',
        blocked:          'read',
        sent:             'push',
        approved:         'push',
    };

    function closePipelineModal() {
        document.getElementById('pipeline-modal').classList.add('hidden');
    }

    function setStageState(key, state) { // state: pending | active | done | failed
        const el = document.getElementById('stage-' + key);
        if (!el) return;
        const bubble  = el.querySelector('.stage-bubble');
        const icon    = el.querySelector('.stage-icon');
        const check   = el.querySelector('.stage-check');
        const x       = el.querySelector('.stage-x');
        const label   = el.querySelector('.stage-label');
        const badgeEl = el.querySelector('.stage-badge-text');

        // Reset
        icon.classList.remove('hidden');
        check.classList.add('hidden');
        x.classList.add('hidden');

        if (state === 'done') {
            bubble.style.borderColor = '#34d399';
            bubble.style.background  = 'rgba(52,211,153,0.12)';
            icon.classList.add('hidden');
            check.classList.remove('hidden');
            label.style.color  = '#34d399';
            badgeEl.textContent = 'Done';
            badgeEl.style.color = '#34d399';
        } else if (state === 'active') {
            bubble.style.borderColor = '#a78bfa';
            bubble.style.background  = 'rgba(167,139,250,0.12)';
            icon.style.color = '#a78bfa';
            bubble.style.boxShadow = '0 0 0 4px rgba(167,139,250,0.15)';
            label.style.color  = '#c4b5fd';
            badgeEl.textContent = 'Running…';
            badgeEl.style.color = '#a78bfa';
        } else if (state === 'failed') {
            bubble.style.borderColor = '#f87171';
            bubble.style.background  = 'rgba(248,113,113,0.12)';
            icon.classList.add('hidden');
            x.classList.remove('hidden');
            label.style.color  = '#f87171';
            badgeEl.textContent = 'Failed';
            badgeEl.style.color = '#f87171';
        } else {
            bubble.style.borderColor = '#374151';
            bubble.style.background  = '#111827';
            bubble.style.boxShadow   = '';
            icon.style.color = '#4b5563';
            label.style.color  = '#6b7280';
            badgeEl.textContent = '—';
            badgeEl.style.color = '#374151';
        }

        // Arrow connector after this stage
        const arrowEl = document.getElementById('arrow-' + key);
        if (arrowEl) {
            const path = arrowEl.querySelector('.arrow-path');
            if (path) path.style.stroke = state === 'done' ? '#34d399' : '#2d3748';
        }
    }

    function updatePipelineUI(data) {
        const currentKey = STAGE_STATUS_MAP[data.status] ?? null;
        const currentIdx = currentKey ? STAGE_ORDER.indexOf(currentKey) : -1;

        STAGE_ORDER.forEach((key, idx) => {
            if (data.failed && idx === currentIdx) {
                setStageState(key, 'failed');
            } else if (idx < currentIdx || (data.done && !data.failed)) {
                setStageState(key, 'done');
            } else if (idx === currentIdx) {
                setStageState(key, data.failed ? 'failed' : 'active');
            } else {
                setStageState(key, 'pending');
            }
        });

        const overall  = document.getElementById('pipeline-overall');
        const spinner  = document.getElementById('pipeline-spinner');
        const bar      = document.getElementById('pipeline-status-bar');

        if (data.done && !data.failed) {
            spinner.classList.add('hidden');
            overall.textContent    = '✓ Complete — draft ready in Gmail';
            overall.style.color    = '#34d399';
            bar.style.borderColor  = 'rgba(52,211,153,0.3)';
            bar.style.background   = 'rgba(52,211,153,0.06)';
            setTimeout(() => {
                closePipelineModal();
                const url = new URL(window.location.href);
                url.searchParams.delete('watch');
                window.location.href = url.toString();
            }, 3500);
        } else if (data.failed) {
            spinner.classList.add('hidden');
            overall.textContent   = '✕ Pipeline failed — check the Transactions log for details';
            overall.style.color   = '#f87171';
            bar.style.borderColor = 'rgba(248,113,113,0.3)';
            bar.style.background  = 'rgba(248,113,113,0.06)';
        } else {
            const labels = { reading:'Reading email…', classifying:'Classifying…', memory_lookup:'Looking up memory…', logging:'Logging transaction…', template_select:'Selecting template…', drafting:'Drafting email with AI…', pushing:'Pushing to Gmail…' };
            overall.textContent = labels[data.status] ?? 'Processing…';
        }
    }

    if (WATCH_TX) {
        const statusUrl = '{{ url('/transactions') }}/' + WATCH_TX + '/status';

        function poll() {
            fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    updatePipelineUI(data);
                    if (!data.done) setTimeout(poll, 2000);
                })
                .catch(() => setTimeout(poll, 3000));
        }

        poll();
    }

    // Highlight selected model card on radio change
    document.querySelectorAll('input[name="ai_model"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('input[name="ai_model"]').forEach(r => {
                const card = r.closest('label').querySelector('div');
                if (r.checked) {
                    // styling is handled server-side on next load; JS just re-submits visually
                }
            });
        });
    });
    </script>

</x-app-layout>

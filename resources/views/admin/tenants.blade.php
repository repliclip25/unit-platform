<x-app-layout title="Tenant Billing Controls">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-white font-semibold">Tenant Billing Controls</h2>
                <p class="text-gray-500 text-xs mt-0.5">Monitor usage, set spend caps, block policy violations</p>
            </div>
            <span class="text-xs text-gray-600 border border-gray-800 rounded px-2 py-1">{{ $tenants->total() }} tenants</span>
        </div>

        {{-- Leaderboard cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4">

            {{-- Top Spenders --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
                <p class="text-xs text-gray-500 font-medium mb-3 uppercase tracking-wide">Top Spenders</p>
                <div class="space-y-2">
                    @forelse($topSpenders as $i => $u)
                    <a href="{{ route('admin.tenants.show', $u->user_id) }}" class="flex items-center gap-2 group">
                        <span class="text-xs text-gray-600 w-4">{{ $i+1 }}</span>
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0" style="background:var(--accent);color:#1a1404">{{ strtoupper(substr($u->name,0,1)) }}</div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-white truncate group-hover:underline">{{ $u->name }}</p>
                            <p class="text-xs font-semibold" style="color:var(--accent-text)">${{ number_format($u->total_spend,4) }}</p>
                        </div>
                    </a>
                    @empty
                    <p class="text-xs text-gray-600">No data yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Top Token Consumers --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
                <p class="text-xs text-gray-500 font-medium mb-3 uppercase tracking-wide">Top Token Use</p>
                <div class="space-y-2">
                    @forelse($topTokens as $i => $u)
                    <a href="{{ route('admin.tenants.show', $u->user_id) }}" class="flex items-center gap-2 group">
                        <span class="text-xs text-gray-600 w-4">{{ $i+1 }}</span>
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 bg-purple-900 text-purple-300">{{ strtoupper(substr($u->name,0,1)) }}</div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-white truncate group-hover:underline">{{ $u->name }}</p>
                            <p class="text-xs text-purple-400 font-semibold">{{ number_format($u->total_tokens) }}</p>
                        </div>
                    </a>
                    @empty
                    <p class="text-xs text-gray-600">No data yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Top Deployments --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
                <p class="text-xs text-gray-500 font-medium mb-3 uppercase tracking-wide">Most Workers</p>
                <div class="space-y-2">
                    @forelse($topDeployments as $i => $u)
                    <a href="{{ route('admin.tenants.show', $u->id) }}" class="flex items-center gap-2 group">
                        <span class="text-xs text-gray-600 w-4">{{ $i+1 }}</span>
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 bg-blue-900 text-blue-300">{{ strtoupper(substr($u->name,0,1)) }}</div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-white truncate group-hover:underline">{{ $u->name }}</p>
                            <p class="text-xs text-blue-400 font-semibold">{{ $u->dep_count }} worker{{ $u->dep_count != 1 ? 's' : '' }}</p>
                        </div>
                    </a>
                    @empty
                    <p class="text-xs text-gray-600">No data yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Top Referrers --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
                <p class="text-xs text-gray-500 font-medium mb-3 uppercase tracking-wide">Top Referrers</p>
                <div class="space-y-2">
                    @forelse($topReferrers as $i => $u)
                    <a href="{{ route('admin.tenants.show', $u->id) }}" class="flex items-center gap-2 group">
                        <span class="text-xs text-gray-600 w-4">{{ $i+1 }}</span>
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 bg-green-900 text-green-300">{{ strtoupper(substr($u->name,0,1)) }}</div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-white truncate group-hover:underline">{{ $u->name }}</p>
                            <p class="text-xs text-green-400 font-semibold">{{ $u->referral_count }} referral{{ $u->referral_count != 1 ? 's' : '' }}</p>
                        </div>
                    </a>
                    @empty
                    <p class="text-xs text-gray-600">No referrals yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Newest Registrants --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
                <p class="text-xs text-gray-500 font-medium mb-3 uppercase tracking-wide">Newest Signups</p>
                <div class="space-y-2">
                    @forelse($newestTenants as $i => $u)
                    <a href="{{ route('admin.tenants.show', $u->id) }}" class="flex items-center gap-2 group">
                        <span class="text-xs text-gray-600 w-4">{{ $i+1 }}</span>
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 bg-orange-900 text-orange-300">{{ strtoupper(substr($u->name,0,1)) }}</div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-white truncate group-hover:underline">{{ $u->name }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($u->created_at)->diffForHumans() }}</p>
                        </div>
                    </a>
                    @empty
                    <p class="text-xs text-gray-600">No tenants yet</p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Search bar --}}
        <form method="GET" action="{{ route('admin.tenants') }}" class="flex items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email…"
                    class="w-full bg-gray-900 border border-gray-800 rounded-xl pl-9 pr-4 py-2 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-gray-600">
            </div>
            @if($search)
            <a href="{{ route('admin.tenants') }}" class="text-xs text-gray-500 hover:text-white">Clear</a>
            @endif
        </form>

        {{-- Tenant table --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Tenant</th>
                        <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Workers</th>
                        <th class="text-right px-4 py-3 text-gray-500 text-xs font-medium">This Month</th>
                        <th class="text-right px-4 py-3 text-gray-500 text-xs font-medium">Spend Cap</th>
                        <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($tenants as $tenant)
                    @php
                        $isBlocked    = (bool) $tenant->blocked_at;
                        $capPct       = $tenant->monthly_spend_cap > 0
                            ? min(100, ($tenant->month_spend / $tenant->monthly_spend_cap) * 100)
                            : null;
                        $capDanger    = $capPct !== null && $capPct >= 80;
                    @endphp
                    <tr class="border-b border-gray-800/60 last:border-0 {{ $isBlocked ? 'bg-red-950/20' : '' }}">

                        {{-- Tenant identity --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                                     style="background:{{ $isBlocked ? '#7f1d1d' : 'var(--accent)' }};color:{{ $isBlocked ? '#fca5a5' : '#000000' }}">
                                    {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="text-white text-xs font-medium hover:text-brand transition">{{ $tenant->name }}</a>
                                    <p class="text-gray-600 text-xs">{{ $tenant->email }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Workers --}}
                        <td class="px-4 py-4">
                            <div class="flex flex-col gap-0.5">
                                @if($tenant->active_count > 0)
                                    <span class="text-xs text-green-400">{{ $tenant->active_count }} active</span>
                                @endif
                                @if($tenant->trial_count > 0)
                                    <span class="text-xs text-yellow-400">{{ $tenant->trial_count }} trial</span>
                                @endif
                                @if($tenant->deployment_count == 0)
                                    <span class="text-xs text-gray-600">none</span>
                                @endif
                            </div>
                        </td>

                        {{-- Monthly usage --}}
                        <td class="px-4 py-4 text-right">
                            <p class="text-white text-xs font-mono">${{ number_format($tenant->month_spend, 4) }}</p>
                            <p class="text-gray-600 text-xs">{{ number_format($tenant->month_tokens) }} tokens</p>
                            @if($capPct !== null)
                                <div class="mt-1.5 h-1 w-20 ml-auto rounded-full overflow-hidden bg-gray-800">
                                    <div class="h-full rounded-full transition-all"
                                         style="width:{{ $capPct }}%;background:{{ $capDanger ? '#ef4444' : '#34d399' }}"></div>
                                </div>
                                <p class="text-xs mt-0.5 {{ $capDanger ? 'text-red-400' : 'text-gray-600' }}">
                                    {{ number_format($capPct, 0) }}% of cap
                                </p>
                            @endif
                        </td>

                        {{-- Spend cap --}}
                        <td class="px-4 py-4 text-right">
                            <form method="POST" action="{{ route('admin.tenants.spend-cap', $tenant->id) }}" class="flex items-center gap-1.5 justify-end">
                                @csrf
                                <span class="text-gray-600 text-xs">$</span>
                                <input type="number" name="cap" step="0.01" min="0"
                                       value="{{ $tenant->monthly_spend_cap ?? '' }}"
                                       placeholder="No cap"
                                       class="w-20 bg-gray-800 border border-gray-700 rounded px-2 py-1 text-xs text-white text-right focus:border-brand focus:outline-none">
                                <button type="submit" class="text-xs px-2 py-1 rounded bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white transition">Set</button>
                            </form>
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-4">
                            @if($isBlocked)
                                <div>
                                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-red-900/40 text-red-400 border border-red-800">
                                        ⛔ Blocked
                                    </span>
                                    <p class="text-red-600 text-xs mt-1 max-w-xs">{{ $tenant->block_reason }}</p>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-green-900/30 text-green-400 border border-green-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Active
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-4">
                            @if($isBlocked)
                                <form method="POST" action="{{ route('admin.tenants.unblock', $tenant->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs px-3 py-1.5 rounded-lg font-medium bg-green-900/30 text-green-400 border border-green-800 hover:bg-green-900/60 transition">
                                        Unblock
                                    </button>
                                </form>
                            @else
                                <button onclick="openBlockModal({{ $tenant->id }}, '{{ addslashes($tenant->name) }}')"
                                        class="text-xs px-3 py-1.5 rounded-lg font-medium bg-red-900/20 text-red-400 border border-red-900 hover:bg-red-900/40 transition">
                                    Block
                                </button>
                            @endif
                        </td>

                    </tr>
                @endforeach
                </tbody>
            </table>
            @if($tenants->hasPages())
            <div class="mt-4">
                {{ $tenants->links() }}
            </div>
            @endif
        </div>

        {{-- Orphaned deployments (no billing record) --}}
        @if($orphanedDeployments->isNotEmpty())
        <div>
            <div class="flex items-center gap-3 mb-3">
                <h3 class="text-white font-semibold text-sm">Orphaned Deployments</h3>
                <span class="text-xs px-2 py-0.5 rounded-full bg-red-900/40 text-red-400 border border-red-800">{{ $orphanedDeployments->count() }} missing billing records</span>
            </div>
            <div class="bg-gray-900 border border-red-900/40 rounded-2xl overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-800 bg-red-900/10">
                    <p class="text-red-400 text-xs">These deployments are active but have no <code class="font-mono">deployment_billing</code> row — they will show "No billing record" to tenants and cannot be subscribed. Backfill creates a fresh trial record.</p>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Deployment</th>
                            <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Tenant</th>
                            <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Worker</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($orphanedDeployments as $dep)
                    <tr class="border-b border-gray-800/60 last:border-0">
                        <td class="px-5 py-3">
                            <p class="text-white text-xs font-medium">{{ $dep->name }}</p>
                            <p class="text-gray-600 text-xs font-mono">ID #{{ $dep->id }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-gray-300 text-xs">{{ $dep->tenant_name }}</p>
                            <p class="text-gray-600 text-xs">{{ $dep->tenant_email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-mono px-2 py-0.5 rounded" style="background:var(--bg-raised);color:var(--text-secondary);border:1px solid var(--border)">{{ $dep->worker_slug }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.deployments.backfill-billing', $dep->id) }}">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Create a trial billing record for {{ addslashes($dep->name) }}?')"
                                        class="text-xs px-3 py-1.5 rounded-lg font-medium bg-amber-900/30 text-amber-400 border border-amber-800 hover:bg-amber-900/60 transition">
                                    ↺ Backfill as Trial
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Per-deployment billing management --}}
        <div>
            <h3 class="text-white font-semibold text-sm mb-3">Deployment Billing Status</h3>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                @php
                    $allDeployments = DB::table('worker_deployments as wd')
                        ->leftJoin('deployment_billing as db', 'db.deployment_id', '=', 'wd.id')
                        ->leftJoin('users', 'users.id', '=', 'wd.user_id')
                        ->whereIn('wd.status', ['active', 'paused'])
                        ->select('wd.id', 'wd.name', 'wd.worker_slug', 'wd.user_id',
                                 'users.name as tenant_name',
                                 'db.status as billing_status',
                                 'db.trial_transactions_used', 'db.trial_transactions_limit')
                        ->orderBy('users.name')->orderBy('wd.id')
                        ->get();
                @endphp
                @forelse($allDeployments as $dep)
                @php
                    $bStatusColor = match($dep->billing_status) {
                        'active'   => 'text-green-400',
                        'trial'    => 'text-yellow-400',
                        'past_due' => 'text-red-400',
                        'paused'   => 'text-gray-500',
                        'canceled' => 'text-red-600',
                        default    => 'text-gray-600',
                    };
                @endphp
                <div class="px-5 py-3 border-b border-gray-800/60 last:border-0 flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-xs font-medium">{{ $dep->name }}</p>
                        <p class="text-gray-600 text-xs">{{ $dep->tenant_name }} · <span class="font-mono">{{ $dep->worker_slug }}</span></p>
                    </div>
                    <div class="shrink-0 w-28 text-right">
                        @if($dep->billing_status)
                            <span class="text-xs font-medium {{ $bStatusColor }}">{{ ucfirst($dep->billing_status) }}</span>
                            @if($dep->billing_status === 'trial')
                                <p class="text-gray-600 text-xs">{{ $dep->trial_transactions_used }}/{{ $dep->trial_transactions_limit }} used</p>
                            @endif
                        @else
                            <span class="text-xs text-red-500">No record</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.deployments.set-billing-status', $dep->id) }}" class="flex items-center gap-2 shrink-0">
                        @csrf
                        <select name="status" class="bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded px-2 py-1 focus:outline-none focus:border-brand">
                            @foreach(['trial','active','paused','canceled','past_due'] as $s)
                                <option value="{{ $s }}" {{ $dep->billing_status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="text-xs px-2 py-1 rounded bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white transition border border-gray-700">Set</button>
                    </form>
                </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-600 text-sm">No active deployments.</div>
                @endforelse
            </div>
        </div>

        {{-- Invoice void tool --}}
        <div>
            <h3 class="text-white font-semibold text-sm mb-3">Void a Stripe Invoice</h3>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl px-5 py-4">
                <p class="text-gray-500 text-xs mb-4">Use this when a tenant has an incorrect "Due" invoice — typically from a misconfigured deployment or billing state change. Voiding removes the balance from Stripe without charging the tenant. Paid invoices cannot be voided here — issue a refund from the Stripe dashboard instead.</p>
                <form method="POST" action="" id="void-invoice-form" class="flex items-end gap-3 flex-wrap">
                    @csrf
                    <div>
                        <label class="block text-gray-500 text-xs mb-1">Tenant</label>
                        <select name="user_id" id="void-tenant-select" required
                                class="bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-lg px-3 py-2 focus:outline-none focus:border-brand min-w-48">
                            <option value="">— Select tenant —</option>
                            @foreach($tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-500 text-xs mb-1">Stripe Invoice ID</label>
                        <input type="text" name="_invoice_id" id="void-invoice-id" required
                               placeholder="in_1ABC..."
                               class="bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-lg px-3 py-2 focus:outline-none focus:border-brand w-52 font-mono">
                    </div>
                    <button type="button"
                            onclick="submitVoidForm()"
                            class="text-xs px-4 py-2 rounded-lg border border-amber-800 text-amber-400 hover:bg-amber-900/20 transition font-medium">
                        Void Invoice
                    </button>
                </form>
            </div>
        </div>

        {{-- Automated enforcement log --}}
        @if($enforcementLog->count() > 0)
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-white font-semibold text-sm">Automated Enforcement Log</h3>
                <span class="text-xs text-gray-600 border border-gray-800 rounded px-2 py-1">{{ $enforcementLog->count() }} recent actions</span>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left px-5 py-3 text-gray-500 text-xs font-medium">Time</th>
                            <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Tenant</th>
                            <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Action</th>
                            <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Policy</th>
                            <th class="text-left px-4 py-3 text-gray-500 text-xs font-medium">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($enforcementLog as $entry)
                        @php
                            $isBlock = $entry->action === 'auto_block';
                        @endphp
                        <tr class="border-b border-gray-800/60 last:border-0">
                            <td class="px-5 py-3 text-gray-500 text-xs font-mono whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($entry->created_at)->format('M j H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-white text-xs">{{ $entry->tenant_name }}</p>
                                <p class="text-gray-600 text-xs">{{ $entry->tenant_email }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium"
                                      style="{{ $isBlock ? 'background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.3)' : 'background:rgba(245,158,11,0.12);color:#fbbf24;border:1px solid rgba(245,158,11,0.3)' }}">
                                    {{ $isBlock ? '⛔' : '⚠' }} {{ str_replace('_', ' ', ucfirst($entry->action)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-mono text-gray-300">{{ $entry->policy_code }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $entry->detail }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- Block modal --}}
    @php
    $blockablePolicies = collect(\App\Platform\Services\PolicyEngine::POLICIES)
        ->filter(fn($p) => $p['level'] === 'platform' && !$p['self_service'])
        ->merge(
            collect(\App\Platform\Services\PolicyEngine::POLICIES)
                ->filter(fn($p) => $p['severity'] === 'hard')
        )
        ->unique()
        ->sortBy('title');
    @endphp

    <div id="block-modal" class="fixed inset-0 z-50 hidden items-center justify-center"
         style="background:rgba(0,0,0,0.8);backdrop-filter:blur(10px)">
        <div class="bg-gray-900 border border-red-900/60 rounded-2xl w-full max-w-lg mx-4 overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-800 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(239,68,68,0.12)">
                    <svg class="w-4 h-4" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-semibold text-sm">Block Tenant</h3>
                    <p id="block-modal-name" class="text-gray-500 text-xs"></p>
                </div>
            </div>

            <form id="block-form" method="POST" class="p-6 space-y-4">
                @csrf

                {{-- Policy code selector --}}
                <div>
                    <label class="block text-gray-400 text-xs font-medium mb-2">Policy Violation Code</label>
                    <div class="space-y-2" id="policy-options">
                        @foreach(\App\Platform\Services\PolicyEngine::POLICIES as $code => $policy)
                        @if($policy['severity'] === 'hard' || !$policy['self_service'])
                        <label class="block cursor-pointer">
                            <input type="radio" name="policy_code" value="{{ $code }}"
                                   class="sr-only policy-radio"
                                   onchange="onPolicyChange('{{ $code }}', '{{ addslashes($policy['description']) }}')">
                            <div class="policy-card rounded-xl border px-4 py-3 transition-all duration-150"
                                 style="border-color:#374151;background:rgba(255,255,255,0.02)"
                                 data-code="{{ $code }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-mono font-bold text-gray-300">{{ $code }}</span>
                                            <span class="text-xs px-1.5 py-0.5 rounded"
                                                  style="background:{{ $policy['color'] === 'red' ? 'rgba(239,68,68,0.12)' : 'rgba(245,158,11,0.12)' }};color:{{ $policy['color'] === 'red' ? '#f87171' : '#fbbf24' }}">
                                                {{ $policy['severity'] }} · {{ $policy['level'] }}
                                            </span>
                                        </div>
                                        <p class="text-gray-500 text-xs mt-0.5">{{ $policy['title'] }}</p>
                                    </div>
                                    <div class="w-3.5 h-3.5 rounded-full border-2 shrink-0 ml-3 policy-dot"
                                         style="border-color:#4b5563"></div>
                                </div>
                            </div>
                        </label>
                        @endif
                        @endforeach
                    </div>
                    <p class="text-red-600 text-xs mt-1.5 hidden" id="policy-error">Select a policy code</p>
                </div>

                {{-- What this policy blocks (auto-filled) --}}
                <div id="policy-impact" class="hidden rounded-xl px-4 py-3 text-xs" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2)">
                    <p class="text-red-400 font-medium mb-1">What this blocks:</p>
                    <p id="policy-impact-text" class="text-red-300"></p>
                </div>

                {{-- Admin notes (internal) --}}
                <div>
                    <label class="block text-gray-400 text-xs font-medium mb-1">
                        Admin Notes <span class="text-gray-600">(internal — not shown to tenant)</span>
                    </label>
                    <textarea name="reason" rows="2" required
                              class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:border-red-500 focus:outline-none resize-none"
                              placeholder="Specific details, evidence, or context for this block…"></textarea>
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="submit" onclick="return validateBlock()"
                            class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition"
                            style="background:#b91c1c">
                        Confirm Block
                    </button>
                    <button type="button" onclick="closeBlockModal()"
                            class="px-5 py-2.5 rounded-xl text-sm text-gray-400 border border-gray-700 hover:bg-gray-800 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function submitVoidForm() {
        const invoiceId = document.getElementById('void-invoice-id').value.trim();
        const userId    = document.getElementById('void-tenant-select').value;
        if (!invoiceId || !userId) { alert('Select a tenant and enter an invoice ID.'); return; }
        if (!confirm(`Void invoice ${invoiceId}? This removes the outstanding balance in Stripe — the tenant will no longer be charged.`)) return;
        const form = document.getElementById('void-invoice-form');
        form.action = '/admin/invoices/' + invoiceId + '/void';
        // Remove the temp field name so it doesn't conflict
        document.getElementById('void-invoice-id').name = '_invoice_id_unused';
        form.submit();
    }
    </script>

    <script>
    @php
    $policyDescriptions = collect(\App\Platform\Services\PolicyEngine::POLICIES)
        ->map(fn($p, $code) => ['blocks' => implode(', ', $p['blocks']), 'desc' => $p['description']])
        ->toJson();
    @endphp
    const POLICY_META = {!! $policyDescriptions !!};

    function openBlockModal(id, name) {
        document.getElementById('block-modal-name').textContent = name;
        document.getElementById('block-form').action = '/admin/tenants/' + id + '/block';
        // Reset selections
        document.querySelectorAll('.policy-radio').forEach(r => r.checked = false);
        document.querySelectorAll('.policy-card').forEach(c => {
            c.style.borderColor = '#374151';
            c.style.background  = 'rgba(255,255,255,0.02)';
            c.querySelector('.policy-dot').style.borderColor = '#4b5563';
            c.querySelector('.policy-dot').style.background  = 'transparent';
        });
        document.getElementById('policy-impact').classList.add('hidden');
        document.getElementById('policy-error').classList.add('hidden');
        const modal = document.getElementById('block-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeBlockModal() {
        const modal = document.getElementById('block-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function onPolicyChange(code, desc) {
        // Style all cards
        document.querySelectorAll('.policy-card').forEach(c => {
            const isSelected = c.dataset.code === code;
            c.style.borderColor = isSelected ? '#ef4444' : '#374151';
            c.style.background  = isSelected ? 'rgba(239,68,68,0.08)' : 'rgba(255,255,255,0.02)';
            c.querySelector('.policy-dot').style.borderColor = isSelected ? '#ef4444' : '#4b5563';
            c.querySelector('.policy-dot').style.background  = isSelected ? '#ef4444'  : 'transparent';
        });
        // Show impact
        const meta = POLICY_META[code];
        if (meta) {
            document.getElementById('policy-impact-text').textContent = 'Blocks: ' + meta.blocks;
            document.getElementById('policy-impact').classList.remove('hidden');
        }
        document.getElementById('policy-error').classList.add('hidden');
    }

    function validateBlock() {
        const selected = document.querySelector('.policy-radio:checked');
        if (!selected) {
            document.getElementById('policy-error').classList.remove('hidden');
            return false;
        }
        return true;
    }
    </script>

</x-app-layout>

<x-app-layout title="Tenant Billing Controls">

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="font-semibold" style="color:var(--text-primary)">Tenant Billing Controls</h2>
            <p class="text-xs mt-0.5" style="color:var(--text-muted)">Monitor usage, set spend caps, block policy violations</p>
        </div>
        <span class="text-xs shrink-0 px-2 py-1 rounded" style="color:var(--text-faint);border:1px solid var(--border)">{{ $tenants->total() }} tenants</span>
    </div>

    {{-- Leaderboard cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3">

        @php
        $boards = [
            ['title'=>'Top Spenders',   'items'=>$topSpenders,    'avatarClass'=>'',                  'valueKey'=>'spend',    'linkKey'=>'user_id'],
            ['title'=>'Top Token Use',  'items'=>$topTokens,      'avatarClass'=>'bg-purple-900/60 text-purple-300', 'valueKey'=>'tokens',   'linkKey'=>'user_id'],
            ['title'=>'Most Workers',   'items'=>$topDeployments,  'avatarClass'=>'bg-blue-900/60 text-blue-300',    'valueKey'=>'workers',  'linkKey'=>'id'],
            ['title'=>'Top Referrers',  'items'=>$topReferrers,    'avatarClass'=>'bg-green-900/60 text-green-300',  'valueKey'=>'referrals','linkKey'=>'id'],
            ['title'=>'Newest Signups', 'items'=>$newestTenants,   'avatarClass'=>'bg-orange-900/60 text-orange-300','valueKey'=>'joined',   'linkKey'=>'id'],
        ];
        @endphp

        {{-- Top Spenders --}}
        <div class="rounded-2xl p-4" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-xs font-semibold uppercase tracking-wider mb-3" style="color:var(--text-muted)">Top Spenders</p>
            <div class="space-y-2">
                @forelse($topSpenders as $i => $u)
                <a href="{{ route('admin.tenants.show', $u->user_id) }}" class="flex items-center gap-2 group">
                    <span class="text-xs w-4 shrink-0" style="color:var(--text-faint)">{{ $i+1 }}</span>
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0" class="ac-on">{{ strtoupper(substr($u->name,0,1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs truncate group-hover:underline" style="color:var(--text-primary)">{{ $u->name }}</p>
                        <p class="text-xs font-semibold" class="ac-text">${{ number_format($u->total_spend,4) }}</p>
                    </div>
                </a>
                @empty
                <p class="text-xs" style="color:var(--text-faint)">No data yet</p>
                @endforelse
            </div>
        </div>

        {{-- Top Token Use --}}
        <div class="rounded-2xl p-4" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-xs font-semibold uppercase tracking-wider mb-3" style="color:var(--text-muted)">Top Token Use</p>
            <div class="space-y-2">
                @forelse($topTokens as $i => $u)
                <a href="{{ route('admin.tenants.show', $u->user_id) }}" class="flex items-center gap-2 group">
                    <span class="text-xs w-4 shrink-0" style="color:var(--text-faint)">{{ $i+1 }}</span>
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 bg-purple-900/60 text-purple-300">{{ strtoupper(substr($u->name,0,1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs truncate group-hover:underline" style="color:var(--text-primary)">{{ $u->name }}</p>
                        <p class="text-xs font-semibold text-purple-400">{{ number_format($u->total_tokens) }}</p>
                    </div>
                </a>
                @empty
                <p class="text-xs" style="color:var(--text-faint)">No data yet</p>
                @endforelse
            </div>
        </div>

        {{-- Most Workers --}}
        <div class="rounded-2xl p-4" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-xs font-semibold uppercase tracking-wider mb-3" style="color:var(--text-muted)">Most Workers</p>
            <div class="space-y-2">
                @forelse($topDeployments as $i => $u)
                <a href="{{ route('admin.tenants.show', $u->id) }}" class="flex items-center gap-2 group">
                    <span class="text-xs w-4 shrink-0" style="color:var(--text-faint)">{{ $i+1 }}</span>
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 bg-blue-900/60 text-blue-300">{{ strtoupper(substr($u->name,0,1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs truncate group-hover:underline" style="color:var(--text-primary)">{{ $u->name }}</p>
                        <p class="text-xs font-semibold text-blue-400">{{ $u->dep_count }} worker{{ $u->dep_count != 1 ? 's' : '' }}</p>
                    </div>
                </a>
                @empty
                <p class="text-xs" style="color:var(--text-faint)">No data yet</p>
                @endforelse
            </div>
        </div>

        {{-- Top Referrers --}}
        <div class="rounded-2xl p-4" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-xs font-semibold uppercase tracking-wider mb-3" style="color:var(--text-muted)">Top Referrers</p>
            <div class="space-y-2">
                @forelse($topReferrers as $i => $u)
                <a href="{{ route('admin.tenants.show', $u->id) }}" class="flex items-center gap-2 group">
                    <span class="text-xs w-4 shrink-0" style="color:var(--text-faint)">{{ $i+1 }}</span>
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 bg-green-900/60 text-green-300">{{ strtoupper(substr($u->name,0,1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs truncate group-hover:underline" style="color:var(--text-primary)">{{ $u->name }}</p>
                        <p class="text-xs font-semibold text-green-400">{{ $u->referral_count }} referral{{ $u->referral_count != 1 ? 's' : '' }}</p>
                    </div>
                </a>
                @empty
                <p class="text-xs" style="color:var(--text-faint)">No referrals yet</p>
                @endforelse
            </div>
        </div>

        {{-- Newest Signups --}}
        <div class="rounded-2xl p-4 col-span-2 sm:col-span-1" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-xs font-semibold uppercase tracking-wider mb-3" style="color:var(--text-muted)">Newest Signups</p>
            <div class="space-y-2">
                @forelse($newestTenants as $i => $u)
                <a href="{{ route('admin.tenants.show', $u->id) }}" class="flex items-center gap-2 group">
                    <span class="text-xs w-4 shrink-0" style="color:var(--text-faint)">{{ $i+1 }}</span>
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 bg-orange-900/60 text-orange-300">{{ strtoupper(substr($u->name,0,1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs truncate group-hover:underline" style="color:var(--text-primary)">{{ $u->name }}</p>
                        <p class="text-xs" style="color:var(--text-faint)">{{ \Carbon\Carbon::parse($u->created_at)->diffForHumans() }}</p>
                    </div>
                </a>
                @empty
                <p class="text-xs" style="color:var(--text-faint)">No tenants yet</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.tenants') }}" class="flex items-center gap-3">
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color:var(--text-faint)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/></svg>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email…"
                   class="w-full text-sm rounded-xl pl-9 pr-4 py-2 border focus:outline-none"
                   style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
        </div>
        @if($search)
        <a href="{{ route('admin.tenants') }}" class="text-xs transition hover:opacity-80" style="color:var(--text-muted)">Clear</a>
        @endif
    </form>

    {{-- Tenant list — card layout on mobile, table on lg+ --}}
    <div class="rounded-2xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">

        {{-- Desktop table (hidden on mobile) --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--border)">
                        <th class="text-left px-5 py-3 text-xs font-medium" style="color:var(--text-muted)">Tenant</th>
                        <th class="text-left px-4 py-3 text-xs font-medium" style="color:var(--text-muted)">Workers</th>
                        <th class="text-right px-4 py-3 text-xs font-medium" style="color:var(--text-muted)">This Month</th>
                        <th class="text-right px-4 py-3 text-xs font-medium" style="color:var(--text-muted)">Spend Cap</th>
                        <th class="text-left px-4 py-3 text-xs font-medium" style="color:var(--text-muted)">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($tenants as $tenant)
                    @php
                        $isBlocked = (bool) $tenant->blocked_at;
                        $capPct    = $tenant->monthly_spend_cap > 0 ? min(100, ($tenant->month_spend / $tenant->monthly_spend_cap) * 100) : null;
                        $capDanger = $capPct !== null && $capPct >= 80;
                    @endphp
                    <tr style="border-bottom:1px solid rgba(var(--border-rgb,255,255,255),0.06);{{ $isBlocked ? 'background:rgba(239,68,68,0.04)' : '' }}">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0"
                                     style="background:{{ $isBlocked ? 'rgba(239,68,68,0.15)' : 'var(--accent)' }};color:{{ $isBlocked ? '#fca5a5' : '#000' }}">
                                    {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="text-xs font-medium transition hover:opacity-80" style="color:var(--text-primary)">{{ $tenant->name }}</a>
                                    <p class="text-xs" style="color:var(--text-faint)">{{ $tenant->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-col gap-0.5">
                                @if($tenant->active_count > 0) <span class="text-xs text-green-400">{{ $tenant->active_count }} active</span> @endif
                                @if($tenant->trial_count > 0)  <span class="text-xs text-yellow-400">{{ $tenant->trial_count }} trial</span> @endif
                                @if($tenant->deployment_count == 0) <span class="text-xs" style="color:var(--text-faint)">none</span> @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <p class="text-xs font-mono" style="color:var(--text-primary)">${{ number_format($tenant->month_spend, 4) }}</p>
                            <p class="text-xs" style="color:var(--text-faint)">{{ number_format($tenant->month_tokens) }} tokens</p>
                            @if($capPct !== null)
                                <div class="mt-1.5 h-1 w-20 ml-auto rounded-full overflow-hidden" style="background:var(--bg-raised)">
                                    <div class="h-full rounded-full" style="width:{{ $capPct }}%;background:{{ $capDanger ? '#ef4444' : '#34d399' }}"></div>
                                </div>
                                <p class="text-xs mt-0.5 {{ $capDanger ? 'text-red-400' : '' }}" style="{{ $capDanger ? '' : 'color:var(--text-faint)' }}">{{ number_format($capPct, 0) }}% of cap</p>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <form method="POST" action="{{ route('admin.tenants.spend-cap', $tenant->id) }}" class="flex items-center gap-1.5 justify-end">
                                @csrf
                                <span class="text-xs" style="color:var(--text-faint)">$</span>
                                <input type="number" name="cap" step="0.01" min="0" value="{{ $tenant->monthly_spend_cap ?? '' }}" placeholder="No cap"
                                       class="w-20 text-xs text-right rounded px-2 py-1 border focus:outline-none"
                                       style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                                <button type="submit" class="text-xs px-2 py-1 rounded transition hover:opacity-80"
                                        style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">Set</button>
                            </form>
                        </td>
                        <td class="px-4 py-4">
                            @if($isBlocked)
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full" style="background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.3)">⛔ Blocked</span>
                                @if($tenant->block_reason)<p class="text-xs mt-1 max-w-xs" style="color:#f87171">{{ $tenant->block_reason }}</p>@endif
                            @else
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full" style="background:rgba(34,197,94,0.1);color:#4ade80;border:1px solid rgba(34,197,94,0.25)">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Active
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($isBlocked)
                                <form method="POST" action="{{ route('admin.tenants.unblock', $tenant->id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs px-3 py-1.5 rounded-lg font-medium transition hover:opacity-80"
                                            style="background:rgba(34,197,94,0.1);color:#4ade80;border:1px solid rgba(34,197,94,0.25)">Unblock</button>
                                </form>
                            @else
                                <button onclick="openBlockModal({{ $tenant->id }}, '{{ addslashes($tenant->name) }}')"
                                        class="text-xs px-3 py-1.5 rounded-lg font-medium transition hover:opacity-80"
                                        style="background:rgba(239,68,68,0.08);color:#f87171;border:1px solid rgba(239,68,68,0.25)">Block</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile card list (hidden on lg+) --}}
        <div class="lg:hidden divide-y" style="border-color:var(--border-subtle)">
            @foreach($tenants as $tenant)
            @php
                $isBlocked = (bool) $tenant->blocked_at;
                $capPct    = $tenant->monthly_spend_cap > 0 ? min(100, ($tenant->month_spend / $tenant->monthly_spend_cap) * 100) : null;
                $capDanger = $capPct !== null && $capPct >= 80;
            @endphp
            <div class="px-4 py-4 space-y-3" style="{{ $isBlocked ? 'background:rgba(239,68,68,0.04)' : '' }}">

                {{-- Identity + status --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold shrink-0"
                             style="background:{{ $isBlocked ? 'rgba(239,68,68,0.15)' : 'var(--accent)' }};color:{{ $isBlocked ? '#fca5a5' : '#000' }}">
                            {{ strtoupper(substr($tenant->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="text-sm font-semibold block truncate" style="color:var(--text-primary)">{{ $tenant->name }}</a>
                            <p class="text-xs truncate" style="color:var(--text-faint)">{{ $tenant->email }}</p>
                        </div>
                    </div>
                    @if($isBlocked)
                        <span class="shrink-0 text-xs px-2 py-0.5 rounded-full" style="background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.3)">⛔</span>
                    @else
                        <span class="shrink-0 text-xs px-2 py-0.5 rounded-full" style="background:rgba(34,197,94,0.1);color:#4ade80;border:1px solid rgba(34,197,94,0.25)">Active</span>
                    @endif
                </div>

                {{-- Stats row --}}
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div>
                        <p style="color:var(--text-faint)">Workers</p>
                        @if($tenant->active_count > 0) <p class="font-medium text-green-400">{{ $tenant->active_count }} active</p>
                        @elseif($tenant->trial_count > 0) <p class="font-medium text-yellow-400">{{ $tenant->trial_count }} trial</p>
                        @else <p style="color:var(--text-faint)">none</p>
                        @endif
                    </div>
                    <div>
                        <p style="color:var(--text-faint)">This month</p>
                        <p class="font-mono font-medium" style="color:var(--text-primary)">${{ number_format($tenant->month_spend, 4) }}</p>
                        <p style="color:var(--text-faint)">{{ number_format($tenant->month_tokens) }} tok</p>
                    </div>
                    <div>
                        <p style="color:var(--text-faint)">Cap</p>
                        <p class="font-medium" style="color:var(--text-primary)">{{ $tenant->monthly_spend_cap ? '$'.number_format($tenant->monthly_spend_cap,2) : '—' }}</p>
                        @if($capPct !== null)
                            <p class="{{ $capDanger ? 'text-red-400' : '' }}" style="{{ $capDanger ? '' : 'color:var(--text-faint)' }}">{{ number_format($capPct,0) }}%</p>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.tenants.spend-cap', $tenant->id) }}" class="flex items-center gap-1.5">
                        @csrf
                        <span class="text-xs" style="color:var(--text-faint)">$</span>
                        <input type="number" name="cap" step="0.01" min="0" value="{{ $tenant->monthly_spend_cap ?? '' }}" placeholder="No cap"
                               class="w-20 text-xs text-right rounded px-2 py-1.5 border focus:outline-none"
                               style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                        <button type="submit" class="text-xs px-2 py-1.5 rounded transition"
                                style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">Set cap</button>
                    </form>
                    @if($isBlocked)
                        <form method="POST" action="{{ route('admin.tenants.unblock', $tenant->id) }}">
                            @csrf
                            <button type="submit" class="text-xs px-3 py-1.5 rounded-lg font-medium transition hover:opacity-80"
                                    style="background:rgba(34,197,94,0.1);color:#4ade80;border:1px solid rgba(34,197,94,0.25)">Unblock</button>
                        </form>
                    @else
                        <button onclick="openBlockModal({{ $tenant->id }}, '{{ addslashes($tenant->name) }}')"
                                class="text-xs px-3 py-1.5 rounded-lg font-medium transition hover:opacity-80"
                                style="background:rgba(239,68,68,0.08);color:#f87171;border:1px solid rgba(239,68,68,0.25)">Block</button>
                    @endif
                </div>

            </div>
            @endforeach
        </div>

        @if($tenants->hasPages())
        <div class="px-4 py-3" style="border-top:1px solid var(--border-subtle)">
            {{ $tenants->links() }}
        </div>
        @endif
    </div>

    {{-- Orphaned deployments --}}
    @if($orphanedDeployments->isNotEmpty())
    <div>
        <div class="flex items-center gap-3 mb-3 flex-wrap">
            <h3 class="font-semibold text-sm" style="color:var(--text-primary)">Orphaned Deployments</h3>
            <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.3)">{{ $orphanedDeployments->count() }} missing billing records</span>
        </div>
        <div class="rounded-2xl overflow-hidden" style="border:1px solid rgba(239,68,68,0.25)">
            <div class="px-5 py-3" style="border-bottom:1px solid var(--border);background:rgba(239,68,68,0.06)">
                <p class="text-xs" style="color:#f87171">These deployments are active but have no <code class="font-mono">deployment_billing</code> row. Backfill creates a fresh trial record.</p>
            </div>
            <div class="divide-y" style="border-color:var(--border-subtle)">
                @foreach($orphanedDeployments as $dep)
                <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium" style="color:var(--text-primary)">{{ $dep->name }}</p>
                        <p class="text-xs font-mono" style="color:var(--text-faint)">#{{ $dep->id }} · {{ $dep->worker_slug }}</p>
                        <p class="text-xs" style="color:var(--text-muted)">{{ $dep->tenant_name }} · {{ $dep->tenant_email }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.deployments.backfill-billing', $dep->id) }}" class="shrink-0">
                        @csrf
                        <button type="submit" onclick="return confirm('Create a trial billing record for {{ addslashes($dep->name) }}?')"
                                class="text-xs px-3 py-1.5 rounded-lg font-medium transition hover:opacity-80 whitespace-nowrap"
                                style="background:rgba(245,158,11,0.1);color:#fbbf24;border:1px solid rgba(245,158,11,0.3)">↺ Backfill as Trial</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Deployment Billing Status --}}
    <div>
        <h3 class="font-semibold text-sm mb-3" style="color:var(--text-primary)">Deployment Billing Status</h3>
        <div class="rounded-2xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
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
                    'active'   => 'color:#4ade80',
                    'trial'    => 'color:#facc15',
                    'past_due' => 'color:#f87171',
                    'paused'   => 'color:var(--text-faint)',
                    'canceled' => 'color:#f87171',
                    default    => 'color:var(--text-faint)',
                };
            @endphp
            <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center gap-3" style="border-bottom:1px solid var(--border-subtle)">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium" style="color:var(--text-primary)">{{ $dep->name }}</p>
                    <p class="text-xs" style="color:var(--text-faint)">{{ $dep->tenant_name }} · <span class="font-mono">{{ $dep->worker_slug }}</span></p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <div class="text-right min-w-16">
                        @if($dep->billing_status)
                            <span class="text-xs font-medium" style="{{ $bStatusColor }}">{{ ucfirst($dep->billing_status) }}</span>
                            @if($dep->billing_status === 'trial')
                                <p class="text-xs" style="color:var(--text-faint)">{{ $dep->trial_transactions_used }}/{{ $dep->trial_transactions_limit }}</p>
                            @endif
                        @else
                            <span class="text-xs text-red-500">No record</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.deployments.set-billing-status', $dep->id) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="status" class="text-xs rounded px-2 py-1.5 border focus:outline-none"
                                style="background:var(--bg-raised);color:var(--text-secondary);border-color:var(--border)">
                            @foreach(['trial','active','paused','canceled','past_due'] as $s)
                                <option value="{{ $s }}" {{ $dep->billing_status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="text-xs px-2 py-1.5 rounded transition hover:opacity-80"
                                style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">Set</button>
                    </form>
                </div>
            </div>
            @empty
                <div class="px-5 py-8 text-center text-sm" style="color:var(--text-faint)">No active deployments.</div>
            @endforelse
        </div>
    </div>

    {{-- Void invoice --}}
    <div>
        <h3 class="font-semibold text-sm mb-3" style="color:var(--text-primary)">Void a Stripe Invoice</h3>
        <div class="rounded-2xl px-5 py-4" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-xs mb-4" style="color:var(--text-muted)">Use this when a tenant has an incorrect "Due" invoice. Voiding removes the balance from Stripe without charging the tenant. Paid invoices cannot be voided here — issue a refund from the Stripe dashboard instead.</p>
            <form method="POST" action="" id="void-invoice-form" class="flex flex-col sm:flex-row sm:items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs mb-1" style="color:var(--text-muted)">Tenant</label>
                    <select name="user_id" id="void-tenant-select" required
                            class="w-full text-xs rounded-lg px-3 py-2 border focus:outline-none"
                            style="background:var(--bg-raised);color:var(--text-secondary);border-color:var(--border)">
                        <option value="">— Select tenant —</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs mb-1" style="color:var(--text-muted)">Stripe Invoice ID</label>
                    <input type="text" name="_invoice_id" id="void-invoice-id" required placeholder="in_1ABC…"
                           class="w-full text-xs rounded-lg px-3 py-2 border focus:outline-none font-mono"
                           style="background:var(--bg-raised);color:var(--text-secondary);border-color:var(--border)">
                </div>
                <button type="button" onclick="submitVoidForm()"
                        class="text-xs px-4 py-2 rounded-lg font-medium transition hover:opacity-80 whitespace-nowrap"
                        style="border:1px solid rgba(245,158,11,0.35);color:#fbbf24">Void Invoice</button>
            </form>
        </div>
    </div>

    {{-- Enforcement log --}}
    @if($enforcementLog->count() > 0)
    <div>
        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
            <h3 class="font-semibold text-sm" style="color:var(--text-primary)">Automated Enforcement Log</h3>
            <span class="text-xs px-2 py-1 rounded" style="color:var(--text-faint);border:1px solid var(--border)">{{ $enforcementLog->count() }} recent actions</span>
        </div>
        <div class="rounded-2xl overflow-x-auto" style="background:var(--bg-card);border:1px solid var(--border)">
            <table class="w-full text-sm min-w-[560px]">
                <thead>
                    <tr style="border-bottom:1px solid var(--border)">
                        <th class="text-left px-5 py-3 text-xs font-medium" style="color:var(--text-muted)">Time</th>
                        <th class="text-left px-4 py-3 text-xs font-medium" style="color:var(--text-muted)">Tenant</th>
                        <th class="text-left px-4 py-3 text-xs font-medium" style="color:var(--text-muted)">Action</th>
                        <th class="text-left px-4 py-3 text-xs font-medium" style="color:var(--text-muted)">Policy</th>
                        <th class="text-left px-4 py-3 text-xs font-medium" style="color:var(--text-muted)">Detail</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($enforcementLog as $entry)
                    @php $isBlock = $entry->action === 'auto_block'; @endphp
                    <tr style="border-bottom:1px solid var(--border-subtle)">
                        <td class="px-5 py-3 text-xs font-mono whitespace-nowrap" style="color:var(--text-faint)">{{ \Carbon\Carbon::parse($entry->created_at)->format('M j H:i') }}</td>
                        <td class="px-4 py-3">
                            <p class="text-xs" style="color:var(--text-primary)">{{ $entry->tenant_name }}</p>
                            <p class="text-xs" style="color:var(--text-faint)">{{ $entry->tenant_email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium"
                                  style="{{ $isBlock ? 'background:rgba(239,68,68,0.12);color:#f87171;border:1px solid rgba(239,68,68,0.3)' : 'background:rgba(245,158,11,0.12);color:#fbbf24;border:1px solid rgba(245,158,11,0.3)' }}">
                                {{ $isBlock ? '⛔' : '⚠' }} {{ str_replace('_',' ',ucfirst($entry->action)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs font-mono" style="color:var(--text-secondary)">{{ $entry->policy_code }}</td>
                        <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">{{ $entry->detail }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

{{-- Block modal --}}
<div id="block-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     style="background:rgba(0,0,0,0.8);backdrop-filter:blur(10px)">
    <div class="w-full max-w-lg rounded-2xl overflow-hidden" style="background:var(--bg-card);border:1px solid rgba(239,68,68,0.4)">
        <div class="px-6 py-4 flex items-center gap-3" style="border-bottom:1px solid var(--border)">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(239,68,68,0.12)">
                <svg class="w-4 h-4" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-sm" style="color:var(--text-primary)">Block Tenant</h3>
                <p id="block-modal-name" class="text-xs" style="color:var(--text-muted)"></p>
            </div>
        </div>
        <form id="block-form" method="POST" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-2" style="color:var(--text-secondary)">Policy Violation Code</label>
                <div class="space-y-2" id="policy-options">
                    @foreach(\App\Platform\Services\PolicyEngine::POLICIES as $code => $policy)
                    @if($policy['severity'] === 'hard' || !$policy['self_service'])
                    <label class="block cursor-pointer">
                        <input type="radio" name="policy_code" value="{{ $code }}" class="sr-only policy-radio"
                               onchange="onPolicyChange('{{ $code }}', '{{ addslashes($policy['description']) }}')">
                        <div class="policy-card rounded-xl border px-4 py-3 transition-all duration-150"
                             style="border-color:var(--border);background:var(--bg-raised)" data-code="{{ $code }}">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-mono font-bold" style="color:var(--text-secondary)">{{ $code }}</span>
                                        <span class="text-xs px-1.5 py-0.5 rounded"
                                              style="background:{{ $policy['color'] === 'red' ? 'rgba(239,68,68,0.12)' : 'rgba(245,158,11,0.12)' }};color:{{ $policy['color'] === 'red' ? '#f87171' : '#fbbf24' }}">
                                            {{ $policy['severity'] }} · {{ $policy['level'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ $policy['title'] }}</p>
                                </div>
                                <div class="w-3.5 h-3.5 rounded-full border-2 shrink-0 ml-3 policy-dot" style="border-color:var(--border)"></div>
                            </div>
                        </div>
                    </label>
                    @endif
                    @endforeach
                </div>
                <p class="text-red-400 text-xs mt-1.5 hidden" id="policy-error">Select a policy code</p>
            </div>
            <div id="policy-impact" class="hidden rounded-xl px-4 py-3 text-xs" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2)">
                <p class="font-medium mb-1" style="color:#f87171">What this blocks:</p>
                <p id="policy-impact-text" style="color:#fca5a5"></p>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1" style="color:var(--text-secondary)">
                    Admin Notes <span style="color:var(--text-faint)">(internal — not shown to tenant)</span>
                </label>
                <textarea name="reason" rows="2" required
                          class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none resize-none"
                          style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"
                          placeholder="Specific details, evidence, or context…"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="submit" onclick="return validateBlock()"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                        style="background:#b91c1c">Confirm Block</button>
                <button type="button" onclick="closeBlockModal()"
                        class="px-5 py-2.5 rounded-xl text-sm transition hover:opacity-80"
                        style="color:var(--text-muted);border:1px solid var(--border)">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function submitVoidForm() {
    const invoiceId = document.getElementById('void-invoice-id').value.trim();
    const userId    = document.getElementById('void-tenant-select').value;
    if (!invoiceId || !userId) { alert('Select a tenant and enter an invoice ID.'); return; }
    if (!confirm(`Void invoice ${invoiceId}? This removes the outstanding balance in Stripe.`)) return;
    const form = document.getElementById('void-invoice-form');
    form.action = '/admin/invoices/' + invoiceId + '/void';
    document.getElementById('void-invoice-id').name = '_invoice_id_unused';
    form.submit();
}

@php
$policyDescriptions = collect(\App\Platform\Services\PolicyEngine::POLICIES)
    ->map(fn($p, $code) => ['blocks' => implode(', ', $p['blocks']), 'desc' => $p['description']])
    ->toJson();
@endphp
const POLICY_META = {!! $policyDescriptions !!};

function openBlockModal(id, name) {
    document.getElementById('block-modal-name').textContent = name;
    document.getElementById('block-form').action = '/admin/tenants/' + id + '/block';
    document.querySelectorAll('.policy-radio').forEach(r => r.checked = false);
    document.querySelectorAll('.policy-card').forEach(c => {
        c.style.borderColor = 'var(--border)';
        c.style.background  = 'var(--bg-raised)';
        c.querySelector('.policy-dot').style.borderColor = 'var(--border)';
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
    document.querySelectorAll('.policy-card').forEach(c => {
        const sel = c.dataset.code === code;
        c.style.borderColor = sel ? '#ef4444' : 'var(--border)';
        c.style.background  = sel ? 'rgba(239,68,68,0.08)' : 'var(--bg-raised)';
        c.querySelector('.policy-dot').style.borderColor = sel ? '#ef4444' : 'var(--border)';
        c.querySelector('.policy-dot').style.background  = sel ? '#ef4444'  : 'transparent';
    });
    const meta = POLICY_META[code];
    if (meta) {
        document.getElementById('policy-impact-text').textContent = 'Blocks: ' + meta.blocks;
        document.getElementById('policy-impact').classList.remove('hidden');
    }
    document.getElementById('policy-error').classList.add('hidden');
}

function validateBlock() {
    if (!document.querySelector('.policy-radio:checked')) {
        document.getElementById('policy-error').classList.remove('hidden');
        return false;
    }
    return true;
}
</script>

</x-app-layout>

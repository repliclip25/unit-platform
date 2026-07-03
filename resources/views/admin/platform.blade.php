<x-app-layout title="Platform Control Tower">

<style>
.ct-card        { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; }
.ct-label       { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; font-weight: 600; color: var(--text-muted); }
.ct-value       { font-size: 26px; font-weight: 700; color: var(--text-primary); line-height: 1.1; }
.ct-sub         { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.ct-section     { margin-top: 32px; }
.ct-section-title { font-size: 13px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
.ct-dot         { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.dot-green      { background: #22c55e; }
.dot-yellow     { background: var(--accent); }
.dot-red        { background: #ef4444; }
.dot-gray       { background: #555; }
.ct-pill        { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 99px; }
.pill-green     { background: rgba(34,197,94,.15); color: #4ade80; }
.pill-red       { background: rgba(239,68,68,.15); color: #f87171; }
.pill-yellow    { background: rgba(241,211,98,.15); color: var(--accent-text); }
.pill-gray      { background: rgba(156,163,175,.1); color: var(--text-muted); }
.pill-blue      { background: rgba(96,165,250,.12); color: #60a5fa; }
.ct-row         { display: grid; gap: 12px; }
.ct-row-4       { grid-template-columns: repeat(4,1fr); }
.ct-row-3       { grid-template-columns: repeat(3,1fr); }
.ct-row-2       { grid-template-columns: repeat(2,1fr); }
.ct-row-5       { grid-template-columns: repeat(5,1fr); }
@media(max-width:1100px) { .ct-row-4,.ct-row-5 { grid-template-columns: repeat(2,1fr); } .ct-row-3 { grid-template-columns: repeat(2,1fr); } }
@media(max-width:700px)  { .ct-row-4,.ct-row-5,.ct-row-3,.ct-row-2 { grid-template-columns: 1fr; } }
.alert-banner   { border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 500; }
.alert-critical { background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.3); color: #fca5a5; }
.alert-warning  { background: rgba(234,179,8,.10); border: 1px solid rgba(234,179,8,.25); color: #fde047; }
.alert-info     { background: rgba(96,165,250,.10); border: 1px solid rgba(96,165,250,.2); color: #93c5fd; }
.connector-row  { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--border-soft); }
.connector-row:last-child { border-bottom: 0; }
table.ct-table  { width: 100%; font-size: 12px; border-collapse: collapse; }
table.ct-table th { text-align: left; padding: 8px 12px; color: var(--text-muted); font-weight: 500; font-size: 11px; border-bottom: 1px solid var(--border); }
table.ct-table td { padding: 10px 12px; border-bottom: 1px solid var(--border-soft); color: var(--text-secondary); vertical-align: middle; }
table.ct-table tr:last-child td { border-bottom: 0; }
table.ct-table tr:hover td { background: var(--bg-raised); }
.funnel-step    { text-align: center; }
.funnel-bar     { height: 6px; border-radius: 3px; background: var(--bg-raised); margin: 6px 0; overflow: hidden; }
.funnel-fill    { height: 100%; border-radius: 3px; background: var(--accent); }
.section-anchor { scroll-margin-top: 80px; }
.ct-action-btn  { font-size:10px;font-weight:600;padding:3px 8px;border-radius:6px;border:none;cursor:pointer; }
.ct-icon        { width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
details summary { cursor:pointer;list-style:none; }
details summary::-webkit-details-marker { display:none; }
</style>

{{-- Flash messages --}}
@if(session('ct_success'))
<div class="alert-banner pill-green mb-4" style="background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:#4ade80;border-radius:10px;padding:10px 16px">
    ✅ {{ session('ct_success') }}
</div>
@endif
@if(session('ct_error'))
<div class="alert-banner mb-4" style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#f87171;border-radius:10px;padding:10px 16px">
    ❌ {{ session('ct_error') }}
</div>
@endif

<div class="space-y-1 pb-12">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-semibold" style="color:var(--text-primary);font-size:18px">Platform Control Tower</h2>
            <p style="color:var(--text-muted);font-size:12px;margin-top:2px">Full-stack view of UNIT health, configuration, and performance</p>
        </div>
        <div class="flex items-center gap-3">
            <span style="color:var(--text-faint);font-size:11px">Last refresh: {{ now()->format('H:i:s') }}</span>
            <a href="{{ route('admin.platform') }}" class="ct-pill pill-gray" style="text-decoration:none">↻ Refresh</a>
            <a href="{{ route('admin.tenants') }}" class="ct-pill pill-blue" style="text-decoration:none">Tenants →</a>
        </div>
    </div>

    {{-- ── Alert Banner ─────────────────────────────────────────────────────── --}}
    @if(count($alerts) > 0)
    <div id="alerts" class="section-anchor space-y-2 mb-6">
        @foreach($alerts as $alert)
        <div class="alert-banner alert-{{ $alert['level'] }}">
            <span style="font-size:16px">{{ $alert['icon'] }}</span>
            <span class="flex-1">{{ $alert['message'] }}</span>
            @if(!empty($alert['action']))
                <a href="{{ $alert['action'] }}" style="font-size:11px;font-weight:700;opacity:.8;text-decoration:underline">{{ $alert['action_label'] ?? 'View →' }}</a>
            @endif
        </div>
        @endforeach
    </div>
    @else
    <div class="alert-banner mb-6" style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#4ade80">
        <span>✅</span>
        <span>All systems nominal — no active alerts</span>
    </div>
    @endif

    {{-- ── Top KPI Row ──────────────────────────────────────────────────────── --}}
    <div class="ct-row ct-row-5">
        {{-- MRR --}}
        <div class="ct-card p-4">
            <p class="ct-label">MRR</p>
            <p class="ct-value">${{ number_format($businessStats['mrr'], 0) }}</p>
            <p class="ct-sub">{{ $tenantStats['paying'] }} paying tenants</p>
        </div>
        {{-- AI Cost Today --}}
        <div class="ct-card p-4">
            <p class="ct-label">AI Cost Today</p>
            <p class="ct-value">${{ number_format($aiHealth['today_cost'], 4) }}</p>
            <p class="ct-sub">${{ number_format($aiHealth['month_cost'], 2) }} this month</p>
        </div>
        {{-- Margin --}}
        <div class="ct-card p-4">
            @php $margin = $businessStats['mrr'] > 0 ? max(0, (($businessStats['mrr'] - $aiHealth['month_cost']) / $businessStats['mrr']) * 100) : null; @endphp
            <p class="ct-label">Est. Margin</p>
            <p class="ct-value">{{ $margin !== null ? number_format($margin, 0) . '%' : '—' }}</p>
            <p class="ct-sub">MRR minus AI cost</p>
        </div>
        {{-- Tenants --}}
        <div class="ct-card p-4">
            <p class="ct-label">Tenants</p>
            <p class="ct-value">{{ $tenantStats['total'] }}</p>
            <p class="ct-sub">{{ $tenantStats['trial'] }} trial · {{ $tenantStats['paying'] }} paid · {{ $tenantStats['active_now'] }} online</p>
        </div>
        {{-- Pipeline Success --}}
        <div class="ct-card p-4">
            <p class="ct-label">Pipeline Success</p>
            <p class="ct-value">{{ $pipelineStats['success_rate'] }}%</p>
            <p class="ct-sub">{{ number_format($pipelineStats['total']) }} total transactions</p>
        </div>
    </div>

    {{-- ── AI + Queue + Connectors Row ─────────────────────────────────────── --}}
    <div class="ct-row ct-row-3 ct-section" id="ai">

        {{-- AI Health --}}
        @php
            $activeProviderInfo = \App\Platform\Services\LLM\ModelCatalog::providerForModelFull(
                \Illuminate\Support\Facades\DB::table('platform_configs')->where('key','default_ai_model')->value('value') ?? 'claude-sonnet-4-6'
            ) ?? \App\Platform\Services\LLM\ModelCatalog::providerForModelFull('claude-sonnet-4-6');
        @endphp
        <div class="ct-card p-5 section-anchor">
            <div class="flex items-center justify-between mb-4">
                <p class="ct-section-title" style="margin:0"><span class="ct-dot {{ $aiHealth['credit_usd'] !== null && $aiHealth['credit_usd'] < 10 ? 'dot-red' : 'dot-green' }}"></span> AI Engine</p>
                <a href="{{ $activeProviderInfo['dashboard'] ?? 'https://console.anthropic.com' }}" target="_blank" class="ct-pill pill-gray" style="text-decoration:none;font-size:10px">{{ $activeProviderInfo['label'] ?? 'Console' }} →</a>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="ct-label">Credit Balance</span>
                    @if($aiHealth['credit_usd'] !== null)
                        <span class="ct-pill {{ $aiHealth['credit_usd'] < 10 ? 'pill-red' : ($aiHealth['credit_usd'] < 25 ? 'pill-yellow' : 'pill-green') }}">${{ number_format($aiHealth['credit_usd'], 2) }}</span>
                    @else
                        <span class="ct-pill pill-gray">Fetch error</span>
                    @endif
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Active Model</span>
                    <span style="font-size:12px;color:var(--text-secondary);font-family:monospace">{{ $aiHealth['active_model'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Month Calls</span>
                    <span style="font-size:12px;color:var(--text-secondary)">{{ number_format($aiHealth['total_calls']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Month Tokens</span>
                    <span style="font-size:12px;color:var(--text-secondary)">
                        {{ $aiHealth['month_tokens'] ? number_format(($aiHealth['month_tokens']->input ?? 0) + ($aiHealth['month_tokens']->output ?? 0)) : '—' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Custom Keys (tenants)</span>
                    <span style="font-size:12px;color:var(--text-secondary)">{{ $aiHealth['custom_keys'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Month Cost</span>
                    <span style="font-size:13px;font-weight:700;color:var(--text-primary)">${{ number_format($aiHealth['month_cost'], 4) }}</span>
                </div>
            </div>
            {{-- Switch Platform Default Model --}}
            @php
                $currentDefault = \Illuminate\Support\Facades\DB::table('platform_configs')->where('key','default_ai_model')->value('value') ?? 'claude-sonnet-4-6';
                $catalog = \App\Platform\Services\LLM\ModelCatalog::PROVIDERS;
            @endphp
            <div class="mt-4 pt-4" style="border-top:1px solid var(--border-soft)">
                <div class="flex items-center justify-between mb-2">
                    <p class="ct-label">Platform Default Model</p>
                    <span style="font-size:10px;font-family:monospace;color:var(--text-faint)">{{ $currentDefault }}</span>
                </div>
                <form method="POST" action="{{ route('admin.platform.ai.switch-model') }}" class="flex gap-2">
                    @csrf
                    <select name="model" data-no-search style="flex:1;background:var(--bg-raised);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                        @foreach($catalog as $providerKey => $provider)
                            <optgroup label="{{ $provider['label'] }}">
                                @foreach($provider['models'] as $modelId => $meta)
                                <option value="{{ $modelId }}" {{ $currentDefault === $modelId ? 'selected' : '' }}>
                                    {{ $meta['name'] }} — {{ $meta['tier'] }}{{ isset($meta['recommended']) ? ' ✓' : '' }}
                                </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <button type="submit" style="background:var(--accent);color:#000;font-size:11px;font-weight:700;padding:6px 12px;border-radius:8px;border:none;cursor:pointer">Switch</button>
                </form>
                <p class="mt-2" style="font-size:10px;color:var(--text-faint)">Applies to all deployments that haven't set their own model. Tenants override per-deployment in their Configure tab.</p>
                <a href="{{ $activeProviderInfo['credits_url'] ?? 'https://console.anthropic.com/settings/billing' }}" target="_blank" class="ct-pill pill-yellow mt-2" style="text-decoration:none;display:inline-block">+ Add Credits — {{ $activeProviderInfo['label'] ?? 'Anthropic' }}</a>
            </div>
        </div>

        {{-- Queue Health --}}
        <div class="ct-card p-5 section-anchor" id="queue">
            <div class="flex items-center justify-between mb-4">
                <p class="ct-section-title" style="margin:0">
                    <span class="ct-dot {{ $queueHealth['failed'] > 0 ? 'dot-red' : ($queueHealth['worker_running'] ? 'dot-green' : 'dot-yellow') }}"></span> Queue / Jobs
                </p>
                <a href="/horizon" target="_blank" class="ct-pill pill-gray" style="text-decoration:none;font-size:10px">Horizon →</a>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="ct-label">Worker Process</span>
                    <span class="ct-pill {{ $queueHealth['worker_running'] ? 'pill-green' : 'pill-red' }}">{{ $queueHealth['worker_running'] ? 'Running' : 'STOPPED' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Horizon</span>
                    <span class="ct-pill {{ $queueHealth['horizon_running'] ? 'pill-green' : 'pill-gray' }}">{{ $queueHealth['horizon_running'] ? 'Running' : 'Off' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Failed Jobs</span>
                    <span class="ct-pill {{ $queueHealth['failed'] > 0 ? 'pill-red' : 'pill-green' }}">{{ $queueHealth['failed'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Failed (last 24h)</span>
                    <span style="font-size:12px;color:var(--text-secondary)">{{ $queueHealth['recent_failed'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Pending Jobs</span>
                    <span style="font-size:12px;color:var(--text-secondary)">{{ $queueHealth['pending'] }}</span>
                </div>
                @if(count($queueHealth['queue_depths']) > 0)
                <div class="mt-1">
                    <p class="ct-label mb-1">Queue Depths</p>
                    @foreach($queueHealth['queue_depths'] as $qname => $depth)
                    <div class="flex justify-between text-xs" style="color:var(--text-muted);padding:2px 0">
                        <span style="font-family:monospace">{{ $qname }}</span>
                        <span class="{{ $depth > 10 ? 'text-red-400' : '' }}">{{ $depth }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            {{-- Queue Actions --}}
            <div class="mt-4 pt-4 space-y-2" style="border-top:1px solid var(--border-soft)">
                <p class="ct-label">Actions</p>
                <div class="flex flex-wrap gap-2">
                    @if($queueHealth['failed'] > 0)
                    <form method="POST" action="{{ route('admin.platform.queue.retry-all') }}">@csrf
                        <button type="submit" style="background:rgba(34,197,94,.15);color:#4ade80;border:1px solid rgba(34,197,94,.3);font-size:11px;font-weight:600;padding:5px 12px;border-radius:8px;cursor:pointer">↩ Retry All Failed</button>
                    </form>
                    <form method="POST" action="{{ route('admin.platform.queue.clear-failed') }}" onsubmit="return confirm('Permanently delete all failed jobs?')">@csrf
                        <button type="submit" style="background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.25);font-size:11px;font-weight:600;padding:5px 12px;border-radius:8px;cursor:pointer">🗑 Clear Failed</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.platform.queue.clear-stuck') }}" onsubmit="return confirm('Mark all stuck transactions (>30 min processing) as failed?')">@csrf
                        <button type="submit" style="background:rgba(234,179,8,.10);color:#fde047;border:1px solid rgba(234,179,8,.25);font-size:11px;font-weight:600;padding:5px 12px;border-radius:8px;cursor:pointer">⚠ Clear Stuck TX</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Stripe Health --}}
        <div class="ct-card p-5 section-anchor" id="stripe">
            <div class="flex items-center justify-between mb-4">
                <p class="ct-section-title" style="margin:0">
                    <span class="ct-dot {{ $stripeHealth['configured'] ? 'dot-green' : 'dot-red' }}"></span> Stripe
                </p>
                <a href="https://dashboard.stripe.com" target="_blank" class="ct-pill pill-gray" style="text-decoration:none;font-size:10px">Dashboard →</a>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="ct-label">Status</span>
                    <span class="ct-pill {{ $stripeHealth['configured'] ? 'pill-green' : 'pill-red' }}">{{ $stripeHealth['configured'] ? 'Configured' : 'Missing Keys' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Active Subs</span>
                    <span style="font-size:13px;font-weight:700;color:var(--text-primary)">{{ $stripeHealth['active_subs'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Trial Subs</span>
                    <span style="font-size:12px;color:var(--text-secondary)">{{ $stripeHealth['trial_subs'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Past Due</span>
                    <span class="ct-pill {{ $stripeHealth['past_due'] > 0 ? 'pill-red' : 'pill-green' }}">{{ $stripeHealth['past_due'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">MRR Estimate</span>
                    <span style="font-size:13px;font-weight:700;color:var(--text-primary)">${{ number_format($stripeHealth['mrr_estimate'], 0) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="ct-label">Last Webhook</span>
                    <span style="font-size:11px;color:var(--text-muted)">
                        {{ $stripeHealth['last_webhook'] ? \Carbon\Carbon::parse($stripeHealth['last_webhook']->created_at)->diffForHumans() : 'Never' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Connectors ────────────────────────────────────────────────────────── --}}
    <div class="ct-section section-anchor" id="connectors">
        <p class="ct-section-title"><span class="ct-dot dot-green"></span> Connectors & APIs</p>
        <div class="ct-card p-5">
            <div class="divide-y" style="border-color:var(--border-soft)">

                {{-- Anthropic --}}
                <div class="connector-row">
                    <div class="ct-icon" style="background:#cc785c20">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M13.83 2.24L20.4 16.5H17.2l-1.3-3H8.1l-1.3 3H3.6L10.17 2.24h3.66zM12 6.5l-2.4 5.5h4.8L12 6.5z" fill="#cc785c"/></svg>
                    </div>
                    <div class="flex-1">
                        <p style="font-size:13px;font-weight:600;color:var(--text-primary)">Anthropic (Claude)</p>
                        <p style="font-size:11px;color:var(--text-muted)">Model: {{ config('services.claude.model') }} · <span style="font-family:monospace">CLAUDE_API_KEY</span></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="ct-pill pill-green">Connected</span>
                        <a href="https://console.anthropic.com" target="_blank" class="ct-pill pill-gray" style="text-decoration:none">Console →</a>
                        <a href="https://console.anthropic.com/settings/billing" target="_blank" class="ct-pill pill-yellow" style="text-decoration:none">Billing →</a>
                    </div>
                </div>

                {{-- Google Pub/Sub --}}
                <div class="connector-row">
                    <div class="ct-icon" style="background:#4285f420">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                    </div>
                    <div class="flex-1">
                        <p style="font-size:13px;font-weight:600;color:var(--text-primary)">Google Pub/Sub</p>
                        <p style="font-size:11px;color:var(--text-muted);font-family:monospace">{{ config('services.gmail.pubsub_topic') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="ct-pill pill-green">Connected</span>
                        <a href="https://console.cloud.google.com/cloudpubsub/topic/list?project=unit-platform" target="_blank" class="ct-pill pill-gray" style="text-decoration:none">GCP Console →</a>
                    </div>
                </div>

                {{-- Gmail OAuth --}}
                <div class="connector-row">
                    <div class="ct-icon" style="background:#ea433520">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 010 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.909 1.528-1.145C21.69 2.28 24 3.434 24 5.457z" fill="#EA4335"/></svg>
                    </div>
                    <div class="flex-1">
                        <p style="font-size:13px;font-weight:600;color:var(--text-primary)">Gmail OAuth 2.0</p>
                        <p style="font-size:11px;color:var(--text-muted)">Client: <span style="font-family:monospace">{{ substr(config('services.gmail.client_id',''),0,24) }}...</span></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="ct-pill pill-green">Connected</span>
                        <a href="https://console.cloud.google.com/apis/credentials?project=unit-platform" target="_blank" class="ct-pill pill-gray" style="text-decoration:none">Credentials →</a>
                    </div>
                </div>

                {{-- Stripe --}}
                <div class="connector-row">
                    <div class="ct-icon" style="background:#635bff20">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.6 7.2c0-.8.7-1.1 1.7-1.1 1.5 0 3.5.5 4.8 1.2V3.5C18.8 2.9 17 2.5 15.3 2.5c-3.5 0-5.9 1.8-5.9 4.9 0 4.8 6.6 4 6.6 6.1 0 .9-.8 1.2-1.9 1.2-1.7 0-3.8-.7-5.4-1.6v3.9c1.8.8 3.7 1.2 5.4 1.2 3.6 0 6.1-1.8 6.1-4.9-.1-5.1-6.7-4.2-6.6-6.1z" fill="#635BFF"/></svg>
                    </div>
                    <div class="flex-1">
                        <p style="font-size:13px;font-weight:600;color:var(--text-primary)">Stripe</p>
                        <p style="font-size:11px;color:var(--text-muted)">Key: <span style="font-family:monospace">{{ substr(config('cashier.key',''),0,12) }}...</span> · Webhook secret configured</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="ct-pill {{ !empty(config('cashier.key')) ? 'pill-green' : 'pill-red' }}">{{ !empty(config('cashier.key')) ? 'Connected' : 'Missing' }}</span>
                        <a href="https://dashboard.stripe.com" target="_blank" class="ct-pill pill-gray" style="text-decoration:none">Dashboard →</a>
                        <a href="https://dashboard.stripe.com/webhooks" target="_blank" class="ct-pill pill-gray" style="text-decoration:none">Webhooks →</a>
                    </div>
                </div>

                {{-- Trial Gate Toggle --}}
                @php
                    $trialGated  = \Illuminate\Support\Facades\DB::table('platform_configs')->where('key', 'trial_payment_required')->value('value') === 'true';
                    $trialDays   = (int) (\Illuminate\Support\Facades\DB::table('platform_configs')->where('key', 'trial_days')->value('value') ?? 14);
                @endphp
                <div class="connector-row" style="flex-wrap:wrap;gap:12px">
                    <div class="ct-icon" style="background:#f1d36220">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 1a11 11 0 100 22A11 11 0 0012 1zm0 4a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm3 13H9v-2h2v-5H9v-2h4v7h2v2z" fill="#f1d362"/></svg>
                    </div>
                    <div class="flex-1">
                        <p style="font-size:13px;font-weight:600;color:var(--text-primary)">Trial Payment Gate</p>
                        <p style="font-size:11px;color:var(--text-muted)">
                            When ON, new tenants must enter a payment method before the trial starts. Card is not charged until the trial ends ({{ $trialDays }} days).
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.platform.billing.trial-gate') }}" class="flex items-center gap-3">
                        @csrf
                        <span class="ct-pill {{ $trialGated ? 'pill-yellow' : 'pill-gray' }}" style="font-size:10px">
                            {{ $trialGated ? 'Card Required' : 'Free Trial' }}
                        </span>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;user-select:none">
                            <div onclick="this.closest('form').submit()" style="
                                width:40px;height:22px;border-radius:11px;cursor:pointer;transition:background .2s;
                                background:{{ $trialGated ? 'var(--accent)' : 'var(--bg-raised)' }};
                                border:1px solid var(--border);display:flex;align-items:center;padding:2px;
                            ">
                                <div style="
                                    width:16px;height:16px;border-radius:50%;background:#fff;
                                    transition:transform .2s;transform:translateX({{ $trialGated ? '18px' : '0' }});
                                    box-shadow:0 1px 3px rgba(0,0,0,.3)
                                "></div>
                            </div>
                        </label>
                        <input type="hidden" name="value" value="{{ $trialGated ? 'false' : 'true' }}">
                    </form>
                </div>

                {{-- SMTP --}}
                <div class="connector-row">
                    <div class="ct-icon" style="background:#06b6d420">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#06b6d4" stroke-width="1.8"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="flex-1">
                        <p style="font-size:13px;font-weight:600;color:var(--text-primary)">SMTP (unit.report)</p>
                        <p style="font-size:11px;color:var(--text-muted)">{{ config('mail.mailers.smtp.host') }}:{{ config('mail.mailers.smtp.port') }} · {{ count($smtpHealth['routes']) }} routes configured</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="ct-pill pill-green">Connected</span>
                        <a href="#smtp" class="ct-pill pill-gray" style="text-decoration:none">Edit Routes ↓</a>
                    </div>
                </div>

                {{-- Redis --}}
                <div class="connector-row">
                    @php
                        $redisOk = false;
                        try { app('redis')->ping(); $redisOk = true; } catch(\Throwable $e) {}
                    @endphp
                    <div class="ct-icon" style="background:#dc382d20">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><ellipse cx="12" cy="6" rx="9" ry="3" fill="#dc382d"/><path d="M3 6v4c0 1.66 4.03 3 9 3s9-1.34 9-3V6" stroke="#dc382d" stroke-width="1.5" fill="none"/><path d="M3 10v4c0 1.66 4.03 3 9 3s9-1.34 9-3v-4" stroke="#dc382d" stroke-width="1.5" fill="none"/><path d="M3 14v4c0 1.66 4.03 3 9 3s9-1.34 9-3v-4" stroke="#dc382d" stroke-width="1.5" fill="none"/></svg>
                    </div>
                    <div class="flex-1">
                        <p style="font-size:13px;font-weight:600;color:var(--text-primary)">Redis</p>
                        <p style="font-size:11px;color:var(--text-muted)">{{ config('database.redis.default.host') }}:{{ config('database.redis.default.port') }} · Queue driver</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="ct-pill {{ $redisOk ? 'pill-green' : 'pill-red' }}">{{ $redisOk ? 'Connected' : 'Unreachable' }}</span>
                        <a href="/horizon" target="_blank" class="ct-pill pill-gray" style="text-decoration:none">Horizon →</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Gmail Watches ─────────────────────────────────────────────────────── --}}
    <div class="ct-section section-anchor" id="gmail-watches">
        <p class="ct-section-title">
            <span class="ct-dot {{ $gmailWatches['expired'] > 0 ? 'dot-red' : 'dot-green' }}"></span>
            Gmail Watch Health
            <span class="ct-pill pill-green ml-2">{{ $gmailWatches['healthy'] }} healthy</span>
            @if($gmailWatches['expired'] > 0)<span class="ct-pill pill-red">{{ $gmailWatches['expired'] }} expired</span>@endif
            @if($gmailWatches['expiring_soon'] > 0)<span class="ct-pill pill-yellow">{{ $gmailWatches['expiring_soon'] }} expiring soon</span>@endif
        </p>
        <div class="ct-card overflow-hidden">
            <div class="px-4 py-3 flex items-center justify-between" style="background:var(--bg-raised);border-bottom:1px solid var(--border-soft)">
                <p style="font-size:11px;color:var(--text-muted)">
                    Pub/Sub: <code style="color:var(--accent-text)">{{ $gmailWatches['pubsub_topic'] }}</code>
                    &nbsp;·&nbsp;
                    Webhook: <code style="color:var(--accent-text)">{{ $gmailWatches['webhook_url'] }}</code>
                </p>
                <form method="POST" action="{{ route('admin.platform.watches.renew-all') }}">@csrf
                    <button type="submit" style="background:var(--accent);color:#000;font-size:11px;font-weight:700;padding:5px 12px;border-radius:8px;border:none;cursor:pointer">↻ Renew All Watches</button>
                </form>
            </div>
            <table class="ct-table">
                <thead>
                    <tr>
                        <th>Gmail Address</th>
                        <th>Tenant</th>
                        <th>Deployment</th>
                        <th>Status</th>
                        <th>Expires</th>
                        <th>Last Webhook</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gmailWatches['watches'] as $w)
                    <tr>
                        <td style="font-family:monospace;font-size:11px">{{ $w->gmail_address }}</td>
                        <td>{{ $w->tenant_name }}</td>
                        <td>
                            @if($w->deployment_name)
                                <span style="color:var(--text-secondary)">{{ $w->deployment_name }}</span>
                                <span class="ct-pill {{ $w->deployment_status === 'active' ? 'pill-green' : 'pill-gray' }}" style="font-size:10px">{{ $w->deployment_status }}</span>
                            @else
                                <span style="color:var(--text-faint)">—</span>
                            @endif
                        </td>
                        <td>
                            @if($w->is_expired)
                                <span class="ct-pill pill-red">Expired</span>
                            @elseif($w->expires_soon)
                                <span class="ct-pill pill-yellow">Expiring {{ $w->hours_left }}h</span>
                            @elseif($w->watch_active)
                                <span class="ct-pill pill-green">Active</span>
                            @else
                                <span class="ct-pill pill-gray">Inactive</span>
                            @endif
                        </td>
                        <td style="font-size:11px;color:var(--text-muted)">
                            {{ $w->expires_at ? \Carbon\Carbon::parse($w->expires_at)->format('M d H:i') : '—' }}
                        </td>
                        <td style="font-size:11px;color:var(--text-muted)">
                            {{ $w->last_webhook ? \Carbon\Carbon::parse($w->last_webhook)->diffForHumans() : 'Never' }}
                        </td>
                        <td>
                            <div class="flex gap-1 flex-wrap">
                                <form method="POST" action="{{ route('admin.platform.watch.renew', $w->id) }}">@csrf
                                    <button type="submit" style="font-size:10px;font-weight:600;padding:3px 8px;border-radius:6px;border:none;cursor:pointer;background:rgba(34,197,94,.15);color:#4ade80">↻ Renew</button>
                                </form>
                                <form method="POST" action="{{ route('admin.platform.watch.reset-history', $w->id) }}" title="Reset history ID to now — AVA ignores past emails">@csrf
                                    <button type="submit" style="font-size:10px;font-weight:600;padding:3px 8px;border-radius:6px;border:none;cursor:pointer;background:rgba(241,211,98,.12);color:var(--accent-text)">⟳ Reset ID</button>
                                </form>
                                @if($w->watch_active)
                                <form method="POST" action="{{ route('admin.platform.watch.deactivate', $w->id) }}" onsubmit="return confirm('Deactivate watch for {{ $w->gmail_address }}? AVA will stop monitoring this inbox.')">@csrf
                                    <button type="submit" style="font-size:10px;font-weight:600;padding:3px 8px;border-radius:6px;border:none;cursor:pointer;background:rgba(239,68,68,.12);color:#f87171">⏹ Stop</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @if($gmailWatches['watches']->isEmpty())
                    <tr><td colspan="7" style="text-align:center;color:var(--text-faint);padding:20px">No Gmail inboxes connected</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Worker Registry ──────────────────────────────────────────────────── --}}
    <div class="ct-section section-anchor" id="workers">
        <div class="flex items-center justify-between">
            <p class="ct-section-title"><span class="ct-dot dot-green"></span> Worker Registry</p>
            <a href="{{ route('admin.workers.index') }}" style="font-size:11px;font-weight:600;padding:5px 12px;border-radius:8px;background:var(--accent);color:#000;text-decoration:none">+ Register Worker</a>
        </div>
        <div class="ct-card overflow-hidden">
            <table class="ct-table">
                <thead>
                    <tr>
                        <th>Worker</th>
                        <th>Version</th>
                        <th>Owner</th>
                        <th>Deployments</th>
                        <th>TX Today</th>
                        <th>Error Rate</th>
                        <th>Cost / Month</th>
                        <th>Total TX</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workerStats['workers'] as $w)
                    @php
                        $active  = $w['deployments']['active']->cnt  ?? 0;
                        $paused  = $w['deployments']['paused']->cnt  ?? 0;
                        $stopped = $w['deployments']['stopped']->cnt ?? 0;
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold" style="background:var(--accent);color:#000">{{ strtoupper(substr($w['slug'],0,1)) }}</div>
                                <div>
                                    <p style="font-weight:600;color:var(--text-primary)">{{ $w['name'] }}</p>
                                    <p style="font-size:10px;font-family:monospace;color:var(--text-faint)">{{ $w['slug'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td><span style="font-family:monospace;font-size:11px">v{{ $w['version'] }}</span></td>
                        <td>
                            <span style="font-size:12px">{{ $w['owner'] }}</span>
                            @if($w['verified'])<span style="color:#4ade80;font-size:10px"> ✓</span>@endif
                        </td>
                        <td>
                            @if($active > 0)<span class="ct-pill pill-green">{{ $active }} active</span>@endif
                            @if($paused > 0)<span class="ct-pill pill-yellow">{{ $paused }} paused</span>@endif
                            @if($stopped > 0)<span class="ct-pill pill-gray">{{ $stopped }} stopped</span>@endif
                            @if(!$active && !$paused && !$stopped)<span style="color:var(--text-faint)">—</span>@endif
                        </td>
                        <td>{{ $w['tx_today'] }}</td>
                        <td>
                            @if($w['tx_today'] > 0)
                                <span class="ct-pill {{ $w['error_rate'] > 20 ? 'pill-red' : ($w['error_rate'] > 5 ? 'pill-yellow' : 'pill-green') }}">{{ $w['error_rate'] }}%</span>
                            @else
                                <span style="color:var(--text-faint)">—</span>
                            @endif
                        </td>
                        <td style="font-family:monospace">${{ number_format($w['cost_month'], 4) }}</td>
                        <td>{{ number_format($w['tx_total']) }}</td>
                        <td>
                            <div class="flex gap-1 flex-wrap">
                                @if(($w['deployments']['active']->cnt ?? 0) > 0)
                                <form method="POST" action="{{ route('admin.platform.worker.pause-all', $w['slug']) }}" onsubmit="return confirm('Pause ALL active {{ $w['name'] }} deployments?')">@csrf
                                    <button type="submit" class="ct-action-btn" style="background:rgba(241,211,98,.12);color:var(--accent-text)">⏸ Pause</button>
                                </form>
                                @endif
                                @if(($w['deployments']['paused']->cnt ?? 0) > 0)
                                <form method="POST" action="{{ route('admin.platform.worker.resume-all', $w['slug']) }}">@csrf
                                    <button type="submit" class="ct-action-btn" style="background:rgba(34,197,94,.15);color:#4ade80">▶ Resume</button>
                                </form>
                                @endif
                                @if(($w['deployments']['stopped']->cnt ?? 0) > 0)
                                <form method="POST" action="{{ route('admin.platform.worker.start-all', $w['slug']) }}">@csrf
                                    <button type="submit" class="ct-action-btn" style="background:rgba(34,197,94,.15);color:#4ade80">▶ Start</button>
                                </form>
                                @endif
                                @if(($w['deployments']['active']->cnt ?? 0) + ($w['deployments']['paused']->cnt ?? 0) > 0)
                                <form method="POST" action="{{ route('admin.platform.worker.stop-all', $w['slug']) }}" onsubmit="return confirm('STOP all {{ $w['name'] }} deployments?')">@csrf
                                    <button type="submit" class="ct-action-btn" style="background:rgba(239,68,68,.12);color:#f87171">⏹ Stop</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Pipeline Performance + Stage Failures ───────────────────────────── --}}
    <div class="ct-row ct-row-2 ct-section" id="pipeline">
        <div class="ct-card p-5 section-anchor">
            <p class="ct-section-title" style="margin-bottom:16px"><span class="ct-dot dot-green"></span> Pipeline Performance</p>
            <div class="ct-row ct-row-2" style="gap:16px;margin-bottom:16px">
                <div>
                    <p class="ct-label">Success Rate</p>
                    <p class="ct-value">{{ $pipelineStats['success_rate'] }}%</p>
                    <div class="funnel-bar mt-2"><div class="funnel-fill" style="width:{{ $pipelineStats['success_rate'] }}%"></div></div>
                </div>
                <div>
                    <p class="ct-label">Avg Pipeline Time</p>
                    <p class="ct-value">{{ $pipelineStats['avg_minutes'] ?? '—' }}<span style="font-size:14px;font-weight:400"> min</span></p>
                    <p class="ct-sub">last 7 days completed</p>
                </div>
            </div>
            <div class="space-y-2">
                @foreach([
                    ['label' => 'Successful', 'count' => $pipelineStats['successful'], 'color' => '#22c55e'],
                    ['label' => 'Processing', 'count' => $pipelineStats['processing'], 'color' => '#f1d362'],
                    ['label' => 'Failed',     'count' => $pipelineStats['failed'],     'color' => '#ef4444'],
                ] as $stat)
                <div class="flex items-center gap-3">
                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $stat['color'] }};flex-shrink:0"></div>
                    <span style="font-size:12px;color:var(--text-secondary);flex:1">{{ $stat['label'] }}</span>
                    <span style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ number_format($stat['count']) }}</span>
                </div>
                @endforeach
            </div>
            {{-- Daily Volume Sparkline --}}
            @if($pipelineStats['daily_volume']->count() > 0)
            <div class="mt-4">
                <p class="ct-label mb-2">Daily Volume (7 days)</p>
                @php $maxVol = $pipelineStats['daily_volume']->max('cnt') ?: 1; @endphp
                <div class="flex items-end gap-1" style="height:40px">
                    @foreach($pipelineStats['daily_volume'] as $day)
                    <div class="flex-1 rounded-t" style="background:var(--accent);opacity:0.7;height:{{ max(4, ($day->cnt/$maxVol)*40) }}px" title="{{ $day->day }}: {{ $day->cnt }}"></div>
                    @endforeach
                </div>
                <div class="flex justify-between mt-1">
                    <span style="font-size:10px;color:var(--text-faint)">{{ $pipelineStats['daily_volume']->first()->day ?? '' }}</span>
                    <span style="font-size:10px;color:var(--text-faint)">Today</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Stage Failures + Failed Jobs --}}
        <div class="ct-card p-5">
            <p class="ct-section-title" style="margin-bottom:16px"><span class="ct-dot {{ count($failedJobs) > 0 ? 'dot-red' : 'dot-green' }}"></span> Failed Jobs (last 20)</p>
            @if(count($failedJobs) > 0)
            <div style="max-height:300px;overflow-y:auto">
                <table class="ct-table">
                    <thead><tr><th>Job</th><th>Failed At</th><th>Error</th></tr></thead>
                    <tbody>
                        @foreach($failedJobs as $job)
                        <tr>
                            <td style="font-family:monospace;font-size:10px;color:var(--text-secondary)">{{ $job->job_name }}</td>
                            <td style="font-size:10px;white-space:nowrap;color:var(--text-muted)">{{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</td>
                            <td style="font-size:10px;color:#f87171;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $job->short_error }}">{{ substr($job->short_error, 0, 80) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="text-align:center;padding:40px 0;color:var(--text-faint)">
                <p style="font-size:24px;margin-bottom:8px">✅</p>
                <p style="font-size:13px">No failed jobs</p>
            </div>
            @endif

            @if(count($pipelineStats['stage_failures']) > 0)
            <div class="mt-4">
                <p class="ct-label mb-2">Stage Failures (last 7 days)</p>
                @foreach($pipelineStats['stage_failures'] as $sf)
                <div class="flex justify-between items-center py-1">
                    <span style="font-size:11px;font-family:monospace;color:var(--text-secondary)">{{ $sf->job }}</span>
                    <span class="ct-pill pill-red">{{ $sf->cnt }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ── Tenants + Activation Funnel ─────────────────────────────────────── --}}
    <div class="ct-row ct-row-2 ct-section" id="tenants">
        <div class="ct-card p-5 section-anchor">
            <div class="flex items-center justify-between mb-4">
                <p class="ct-section-title" style="margin:0"><span class="ct-dot dot-green"></span> Tenant Overview</p>
                <a href="{{ route('admin.tenants') }}" class="ct-pill pill-blue" style="text-decoration:none;font-size:10px">Manage →</a>
            </div>
            <div class="ct-row ct-row-2" style="gap:12px;margin-bottom:16px">
                @foreach([
                    ['label' => 'Total',    'val' => $tenantStats['total'],      'sub' => ''],
                    ['label' => 'Paying',   'val' => $tenantStats['paying'],     'sub' => 'active plan'],
                    ['label' => 'Trial',    'val' => $tenantStats['trial'],      'sub' => 'free tier'],
                    ['label' => 'Blocked',  'val' => $tenantStats['blocked'],    'sub' => 'access denied'],
                ] as $s)
                <div style="padding:12px;background:var(--bg-raised);border-radius:10px">
                    <p class="ct-label">{{ $s['label'] }}</p>
                    <p style="font-size:22px;font-weight:700;color:var(--text-primary)">{{ $s['val'] }}</p>
                    @if($s['sub'])<p class="ct-sub">{{ $s['sub'] }}</p>@endif
                </div>
                @endforeach
            </div>
            <div class="flex items-center gap-3" style="padding:10px;background:var(--bg-raised);border-radius:10px">
                <div class="ct-dot dot-green"></div>
                <span style="font-size:12px;color:var(--text-secondary)">{{ $tenantStats['active_now'] }} tenant(s) active right now</span>
                <span style="font-size:11px;color:var(--text-faint)">(last 15 min)</span>
            </div>
            <div class="flex items-center gap-3 mt-2" style="padding:10px;background:var(--bg-raised);border-radius:10px">
                <div class="ct-dot dot-yellow"></div>
                <span style="font-size:12px;color:var(--text-secondary)">{{ $tenantStats['new_month'] }} new signup(s) this month</span>
                <span style="font-size:11px;color:var(--text-faint)">({{ $tenantStats['new_last_month'] }} last month)</span>
            </div>
        </div>

        {{-- Activation Funnel --}}
        <div class="ct-card p-5">
            <p class="ct-section-title" style="margin-bottom:16px"><span class="ct-dot dot-yellow"></span> Activation Funnel</p>
            @php
                $f = $businessStats;
                $funnel = [
                    ['label' => 'Signed Up',      'count' => $f['funnel_signups'],    'pct' => 100],
                    ['label' => 'Deployed Worker', 'count' => $f['funnel_deployed'],   'pct' => $f['funnel_signups'] > 0 ? round($f['funnel_deployed']/$f['funnel_signups']*100) : 0],
                    ['label' => 'Used Trial',      'count' => $f['funnel_trial_used'], 'pct' => $f['funnel_signups'] > 0 ? round($f['funnel_trial_used']/$f['funnel_signups']*100) : 0],
                    ['label' => 'Converted',       'count' => $f['funnel_converted'],  'pct' => $f['funnel_signups'] > 0 ? round($f['funnel_converted']/$f['funnel_signups']*100) : 0],
                ];
            @endphp
            <div class="space-y-4">
                @foreach($funnel as $step)
                <div>
                    <div class="flex justify-between mb-1">
                        <span style="font-size:12px;color:var(--text-secondary)">{{ $step['label'] }}</span>
                        <span style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ $step['count'] }} <span style="color:var(--text-muted);font-weight:400">({{ $step['pct'] }}%)</span></span>
                    </div>
                    <div class="funnel-bar"><div class="funnel-fill" style="width:{{ $step['pct'] }}%"></div></div>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4" style="border-top:1px solid var(--border-soft)">
                <div class="flex justify-between">
                    <span style="font-size:12px;color:var(--text-muted)">Referrals issued</span>
                    <span style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ $f['referrals_total'] }}</span>
                </div>
                <div class="flex justify-between mt-1">
                    <span style="font-size:12px;color:var(--text-muted)">Credits issued</span>
                    <span style="font-size:12px;font-weight:600;color:var(--text-primary)">${{ number_format($f['credits_issued'], 2) }}</span>
                </div>
                <div class="flex justify-between mt-1">
                    <span style="font-size:12px;color:var(--text-muted)">Active influencers</span>
                    <span style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ $f['influencers'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SMTP Routes ──────────────────────────────────────────────────────── --}}
    <div class="ct-section section-anchor" id="smtp">
        <p class="ct-section-title"><span class="ct-dot dot-green"></span> SMTP Routes</p>
        <div class="ct-card p-5">

            {{-- Test bar --}}
            <div class="flex items-center gap-3 mb-5">
                <form method="POST" action="{{ route('admin.platform.smtp.test') }}" class="flex gap-2 flex-1">
                    @csrf
                    <input type="email" name="to" placeholder="Send test email to..." value="{{ auth()->user()->email }}"
                        style="flex:1;background:var(--bg-raised);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                    <button type="submit" style="background:var(--accent);color:#000;font-size:11px;font-weight:700;padding:6px 14px;border-radius:8px;border:none;cursor:pointer;white-space:nowrap">✉ Send Test</button>
                </form>
                <a href="{{ route('admin.platform.smtp.add') }}" onclick="document.getElementById('smtp-add-form').style.display='block';return false"
                    style="font-size:11px;font-weight:700;padding:6px 14px;border-radius:8px;border:1px solid var(--border);cursor:pointer;color:var(--text-primary);text-decoration:none;white-space:nowrap">+ Add Route</a>
            </div>

            {{-- Per-route editable cards --}}
            <div style="display:flex;flex-direction:column;gap:10px">
            @foreach($smtpHealth['routes'] as $route)
            <details style="background:var(--bg-raised);border:1px solid var(--border-soft);border-radius:12px;overflow:hidden">
                <summary style="padding:14px 16px;display:flex;align-items:center;gap:12px">
                    <div style="flex:1">
                        <span style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $route['name'] }}</span>
                        <span style="font-size:11px;color:var(--text-muted);margin-left:10px">{{ $route['purpose'] }}</span>
                    </div>
                    <span style="font-size:10px;font-family:monospace;color:var(--text-faint)">{{ $route['from'] ?? ($route['from_address'] ?? '') }}</span>
                    <span class="ct-pill {{ ($route['active'] ?? true) ? 'pill-green' : 'pill-gray' }}">{{ ($route['active'] ?? true) ? 'active' : 'off' }}</span>
                    <span style="font-size:10px;color:var(--text-faint);margin-left:4px">▾</span>
                </summary>
                <div style="padding:16px;border-top:1px solid var(--border-soft)">
                    <form method="POST" action="{{ route('admin.platform.smtp.update', $route['key']) }}">
                        @csrf
                        <div class="ct-row ct-row-2" style="gap:12px;margin-bottom:12px">
                            <div>
                                <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">ROUTE NAME</label>
                                <input type="text" name="name" value="{{ $route['name'] }}" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                            </div>
                            <div>
                                <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">PURPOSE</label>
                                <input type="text" name="purpose" value="{{ $route['purpose'] }}" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                            </div>
                        </div>
                        <div class="ct-row ct-row-3" style="gap:12px;margin-bottom:12px">
                            <div>
                                <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">HOST</label>
                                <input type="text" name="host" value="{{ $route['host'] }}" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                            </div>
                            <div>
                                <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">PORT</label>
                                <input type="number" name="port" value="{{ $route['port'] }}" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                            </div>
                            <div>
                                <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">ENCRYPTION</label>
                                <select name="encryption" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                                    <option value="ssl" {{ ($route['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="tls" {{ ($route['encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="" {{ ($route['encryption'] ?? '') === '' ? 'selected' : '' }}>None</option>
                                </select>
                            </div>
                        </div>
                        <div class="ct-row ct-row-3" style="gap:12px;margin-bottom:12px">
                            <div>
                                <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">USERNAME</label>
                                <input type="text" name="username" value="{{ $route['username'] }}" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                            </div>
                            <div>
                                <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">PASSWORD</label>
                                <input type="password" name="password" placeholder="(leave blank to keep)" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                            </div>
                            <div>
                                <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">FROM ADDRESS</label>
                                <input type="email" name="from_address" value="{{ $route['from_address'] }}" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                            </div>
                        </div>
                        <div class="ct-row ct-row-2" style="gap:12px;margin-bottom:16px">
                            <div>
                                <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">FROM NAME</label>
                                <input type="text" name="from_name" value="{{ $route['from_name'] }}" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                            </div>
                            <div style="display:flex;align-items:flex-end;gap:8px">
                                <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-primary);cursor:pointer;padding-bottom:8px">
                                    <input type="checkbox" name="active" value="1" {{ ($route['active'] ?? true) ? 'checked' : '' }}> Active
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <button type="submit" style="background:var(--accent);color:#000;font-size:11px;font-weight:700;padding:6px 16px;border-radius:8px;border:none;cursor:pointer">Save Route</button>
                            <form method="POST" action="{{ route('admin.platform.smtp.delete', $route['key']) }}" onsubmit="return confirm('Delete this route?')" style="margin:0">
                                @csrf
                                <button type="submit" style="background:transparent;border:1px solid #f87171;color:#f87171;font-size:11px;font-weight:600;padding:5px 12px;border-radius:8px;cursor:pointer">Delete</button>
                            </form>
                        </div>
                    </form>
                </div>
            </details>
            @endforeach
            </div>

            {{-- Add Route form (hidden by default) --}}
            <div id="smtp-add-form" style="display:none;margin-top:16px;padding:16px;background:var(--bg-raised);border:1px dashed var(--border);border-radius:12px">
                <p style="font-size:12px;font-weight:600;color:var(--text-primary);margin-bottom:12px">New SMTP Route</p>
                <form method="POST" action="{{ route('admin.platform.smtp.add') }}">
                    @csrf
                    <div class="ct-row ct-row-2" style="gap:12px;margin-bottom:12px">
                        <div>
                            <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">KEY (slug)</label>
                            <input type="text" name="key" placeholder="e.g. notifications" required style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                        </div>
                        <div>
                            <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">NAME</label>
                            <input type="text" name="name" placeholder="e.g. Notifications" required style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                        </div>
                    </div>
                    <div style="margin-bottom:12px">
                        <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">PURPOSE</label>
                        <input type="text" name="purpose" placeholder="What this route is used for" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                    </div>
                    <div class="ct-row ct-row-3" style="gap:12px;margin-bottom:12px">
                        <div>
                            <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">HOST</label>
                            <input type="text" name="host" value="{{ config('mail.mailers.smtp.host') }}" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                        </div>
                        <div>
                            <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">PORT</label>
                            <input type="number" name="port" value="465" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                        </div>
                        <div>
                            <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">ENCRYPTION</label>
                            <select name="encryption" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                                <option value="ssl">SSL</option>
                                <option value="tls">TLS</option>
                                <option value="">None</option>
                            </select>
                        </div>
                    </div>
                    <div class="ct-row ct-row-3" style="gap:12px;margin-bottom:16px">
                        <div>
                            <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">USERNAME</label>
                            <input type="text" name="username" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                        </div>
                        <div>
                            <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">PASSWORD</label>
                            <input type="password" name="password" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                        </div>
                        <div>
                            <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">FROM ADDRESS</label>
                            <input type="email" name="from_address" value="{{ config('mail.from.address') }}" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                        </div>
                    </div>
                    <div style="margin-bottom:16px">
                        <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">FROM NAME</label>
                        <input type="text" name="from_name" value="{{ config('mail.from.name') }}" style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" style="background:var(--accent);color:#000;font-size:11px;font-weight:700;padding:6px 16px;border-radius:8px;border:none;cursor:pointer">Add Route</button>
                        <button type="button" onclick="document.getElementById('smtp-add-form').style.display='none'" style="background:transparent;border:1px solid var(--border);color:var(--text-secondary);font-size:11px;font-weight:600;padding:5px 12px;border-radius:8px;cursor:pointer">Cancel</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- ── Messaging Templates ─────────────────────────────────────────────── --}}
    <div class="ct-section section-anchor" id="messaging">
        <p class="ct-section-title"><span class="ct-dot dot-blue" style="background:#60a5fa"></span> Messaging Templates</p>
        <div class="ct-card p-5">
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px">
                Subject and body overrides stored in <code style="font-size:11px">platform_configs</code>. Leave blank to use Blade template defaults.
            </p>
            @php $grouped = collect($msgTemplates)->groupBy('category') @endphp
            @foreach($grouped as $category => $templates)
            <div style="margin-bottom:20px">
                <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;color:var(--text-muted);text-transform:uppercase;margin-bottom:10px">{{ $category }}</p>
                <div style="display:flex;flex-direction:column;gap:8px">
                @foreach($templates as $tpl)
                <details style="background:var(--bg-raised);border:1px solid var(--border-soft);border-radius:10px;overflow:hidden">
                    <summary style="padding:12px 14px;display:flex;align-items:center;gap:10px">
                        <div style="flex:1">
                            <span style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ $tpl['label'] }}</span>
                            <span style="font-size:11px;color:var(--text-muted);margin-left:8px">{{ $tpl['desc'] }}</span>
                        </div>
                        @if($tpl['view'])
                        <a href="{{ route('admin.platform.msg.preview', $tpl['key']) }}" target="_blank"
                            style="font-size:10px;font-weight:600;padding:3px 8px;border-radius:6px;border:1px solid var(--border);color:var(--text-secondary);text-decoration:none"
                            onclick="event.stopPropagation()">Preview</a>
                        @else
                        <span style="font-size:10px;padding:3px 8px;border-radius:6px;background:rgba(251,191,36,0.15);color:#f59e0b">No Blade</span>
                        @endif
                        <span style="font-size:10px;font-family:monospace;color:var(--text-faint)">{{ $tpl['key'] }}</span>
                        <span style="font-size:10px;color:var(--text-faint)">▾</span>
                    </summary>
                    <div style="padding:14px;border-top:1px solid var(--border-soft)">
                        <form method="POST" action="{{ route('admin.platform.msg.save', $tpl['key']) }}">
                            @csrf
                            <div style="margin-bottom:10px">
                                <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">SUBJECT OVERRIDE</label>
                                <input type="text" name="subject" value="{{ $tpl['subject_override'] ?? '' }}"
                                    placeholder="Leave blank to use default"
                                    style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary)">
                            </div>
                            <div style="margin-bottom:12px">
                                <label style="font-size:10px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:4px">BODY OVERRIDE (HTML)</label>
                                <textarea name="body" rows="5" placeholder="Leave blank to use Blade template"
                                    style="width:100%;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:8px 10px;font-size:11px;color:var(--text-primary);font-family:monospace;resize:vertical">{{ $tpl['body_override'] ?? '' }}</textarea>
                            </div>
                            @if(!empty($tpl['vars']))
                            <p style="font-size:10px;color:var(--text-faint);margin-bottom:10px">
                                Variables: @foreach($tpl['vars'] as $v)<code style="font-size:10px">@{{{{ $v }}}}</code>@if(!$loop->last), @endif@endforeach
                            </p>
                            @endif
                            <button type="submit" style="background:var(--accent);color:#000;font-size:11px;font-weight:700;padding:5px 14px;border-radius:8px;border:none;cursor:pointer">Save</button>
                        </form>
                    </div>
                </details>
                @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Policy Log ───────────────────────────────────────────────────────── --}}
    <div class="ct-section section-anchor" id="policy">
        <p class="ct-section-title"><span class="ct-dot {{ count($recentAlerts) > 0 ? 'dot-yellow' : 'dot-gray' }}"></span> Policy Enforcement Log</p>
        <div class="ct-card overflow-hidden">
            @if(count($recentAlerts) > 0)
            <table class="ct-table">
                <thead><tr><th>Tenant</th><th>Policy</th><th>Action</th><th>When</th></tr></thead>
                <tbody>
                    @foreach($recentAlerts as $log)
                    <tr>
                        <td>
                            <p style="font-size:12px;font-weight:500;color:var(--text-primary)">{{ $log->tenant_name }}</p>
                            <p style="font-size:10px;color:var(--text-faint)">{{ $log->tenant_email }}</p>
                        </td>
                        <td><span style="font-family:monospace;font-size:11px;color:var(--accent-text)">{{ $log->policy_code ?? '—' }}</span></td>
                        <td><span style="font-size:11px;color:var(--text-secondary)">{{ $log->action ?? $log->reason ?? '—' }}</span></td>
                        <td style="font-size:11px;color:var(--text-muted);white-space:nowrap">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="text-align:center;padding:32px;color:var(--text-faint)">
                <p style="font-size:13px">No policy enforcement actions recorded</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Platform SDK + Worker DNA ───────────────────────────────────────── --}}
    <div class="ct-row ct-row-2 ct-section" id="sdk">
        <div class="ct-card p-5 section-anchor">
            <p class="ct-section-title" style="margin-bottom:12px"><span class="ct-dot dot-blue" style="background:#60a5fa"></span> Platform SDK</p>
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px">
                Reference for integrating with the UNIT platform — webhooks, transaction API, auth flow, tenant data access.
            </p>
            <div class="space-y-2">
                @foreach([
                    ['label' => 'WorkerContract Interface', 'route' => 'qa.platform-blueprint', 'desc' => 'Full PHP interface for all 7 blocks'],
                    ['label' => 'Transaction API',          'route' => null,                     'desc' => '/api/transactions — status, poll, decide'],
                    ['label' => 'Worker Registry',          'route' => null,                     'desc' => 'WorkerRegistry::all() · resolve()'],
                    ['label' => 'Platform Events',          'route' => null,                     'desc' => 'platform_events schema · emit format'],
                ] as $item)
                <div class="flex items-center justify-between p-3 rounded-xl" style="background:var(--bg-raised)">
                    <div>
                        <p style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ $item['label'] }}</p>
                        <p style="font-size:11px;color:var(--text-muted)">{{ $item['desc'] }}</p>
                    </div>
                    @if($item['route'] && \Illuminate\Support\Facades\Route::has($item['route']))
                        <a href="{{ route($item['route']) }}" class="ct-pill pill-blue" style="text-decoration:none;font-size:10px">Download →</a>
                    @else
                        <span class="ct-pill pill-gray" style="font-size:10px">Coming soon</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <div class="ct-card p-5">
            <p class="ct-section-title" style="margin-bottom:12px"><span class="ct-dot" style="background:#a78bfa"></span> Worker DNA SDK</p>
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px">
                Everything a third-party developer needs to build and publish a worker on UNIT.
            </p>
            <div class="space-y-2">
                @foreach($workerStats['workers'] as $w)
                <div class="flex items-center justify-between p-3 rounded-xl" style="background:var(--bg-raised)">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold" style="background:var(--accent);color:#000">{{ strtoupper(substr($w['slug'],0,1)) }}</div>
                        <div>
                            <p style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ $w['name'] }} <span style="font-size:10px;font-family:monospace;color:var(--text-faint)">v{{ $w['version'] }}</span></p>
                            <p style="font-size:10px;color:var(--text-muted)">{{ $w['tx_total'] }} transactions · ${{ number_format($w['cost_month'], 4) }}/mo cost</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.tenants') }}?worker={{ $w['slug'] }}" class="ct-pill pill-gray" style="text-decoration:none;font-size:10px">Tenants →</a>
                </div>
                @endforeach
                <div class="p-3 rounded-xl" style="background:var(--bg-raised);border:1px dashed var(--border)">
                    <p style="font-size:12px;color:var(--text-faint);text-align:center">+ New workers registered via WorkerRegistry</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── System Actions ───────────────────────────────────────────────────── --}}
    <div class="ct-section">
        <p class="ct-section-title"><span class="ct-dot dot-gray"></span> System Actions</p>
        <div class="ct-card p-4">
            <div class="flex flex-wrap gap-3 items-center">
                <form method="POST" action="{{ route('admin.platform.system.clear-caches') }}">@csrf
                    <button type="submit" style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-secondary);font-size:12px;font-weight:600;padding:8px 16px;border-radius:8px;cursor:pointer">🗑 Clear All Caches</button>
                </form>
                <a href="{{ route('admin.platform') }}" style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-secondary);font-size:12px;font-weight:600;padding:8px 16px;border-radius:8px;text-decoration:none;display:inline-block">↻ Refresh Tower</a>
                <a href="/horizon" target="_blank" style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-secondary);font-size:12px;font-weight:600;padding:8px 16px;border-radius:8px;text-decoration:none;display:inline-block">⚡ Horizon Dashboard</a>
                <span style="font-size:11px;color:var(--text-faint);margin-left:auto">UNIT Platform · {{ config('app.env') }} · PHP {{ phpversion() }} · Laravel {{ app()->version() }}</span>
            </div>
        </div>
    </div>

    {{-- ── Quick Links Footer ───────────────────────────────────────────────── --}}
    <div class="ct-section">
        <div class="ct-row ct-row-5" style="gap:8px">
            @foreach([
                ['label' => 'Tenants',      'icon' => '👥', 'route' => 'admin.tenants'],
                ['label' => 'Influencers',  'icon' => '🌟', 'route' => 'admin.influencers'],
                ['label' => 'Transactions', 'icon' => '📋', 'route' => 'transactions'],
                ['label' => 'Horizon',      'icon' => '⚡', 'url' => '/horizon'],
                ['label' => 'QA Center',    'icon' => '🔬', 'route' => 'qa'],
            ] as $link)
            <a href="{{ isset($link['url']) ? $link['url'] : route($link['route']) }}"
               class="ct-card p-4 text-center block"
               style="text-decoration:none;transition:border-color .15s"
               onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
                <div style="font-size:20px;margin-bottom:6px">{{ $link['icon'] }}</div>
                <p style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ $link['label'] }}</p>
            </a>
            @endforeach
        </div>
    </div>

</div>

</x-app-layout>

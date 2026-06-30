<x-app-layout title="Pipeline Health">

<style>
.ph-grid   { display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:24px }
.ph-card   { background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:16px }
.ph-num    { font-size:28px;font-weight:800;color:var(--text-primary);line-height:1 }
.ph-lbl    { font-size:11px;font-weight:600;color:var(--text-muted);margin-top:4px;text-transform:uppercase;letter-spacing:.05em }
.ph-sub    { font-size:11px;color:var(--text-muted);margin-top:2px }
.ph-ok     { color:#4ade80 }
.ph-warn   { color:#fbbf24 }
.ph-err    { color:#f87171 }
.ph-sec    { font-size:13px;font-weight:700;color:var(--text-primary);margin:20px 0 10px;padding-bottom:8px;border-bottom:1px solid var(--border) }
.ph-table  { width:100%;border-collapse:collapse;font-size:12px }
.ph-table th { text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);padding:6px 10px;border-bottom:1px solid var(--border) }
.ph-table td { padding:8px 10px;border-bottom:1px solid rgba(255,255,255,0.04);color:var(--text-secondary) }
.ph-table tr:last-child td { border-bottom:none }
.ph-table tr:hover td { background:var(--bg-raised) }
.ph-badge  { display:inline-block;padding:1px 7px;border-radius:99px;font-size:10px;font-weight:700 }
.ph-stage  { font-family:monospace;font-size:11px;padding:2px 7px;border-radius:5px;background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border) }
.ph-bar-wrap { height:6px;background:var(--bg-raised);border-radius:3px;overflow:hidden;width:80px;display:inline-block;vertical-align:middle }
.ph-bar    { height:100%;border-radius:3px;background:#4ade80 }
.ph-bar-err { background:#f87171 }
.ph-empty  { text-align:center;padding:32px;color:var(--text-muted);font-size:13px }
.ph-stuck-dot { width:8px;height:8px;border-radius:50%;background:#fbbf24;display:inline-block;margin-right:6px;animation:pulse 1.5s infinite }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
</style>

@php
$stageOrder = ['filter','ingest','read','classify','memory','log','template','draft','push'];
$totalCompleted24h = $stageSummary->where('stage_key','push')->first()?->completed ?? 0;
$totalFailed24h    = $stageSummary->sum('failed');
$totalStuck        = $stuck->count();
$avgDraftMs        = $stageSummary->get('draft')?->avg_ms ?? 0;
@endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold" style="color:var(--text-primary)">Pipeline Health</h1>
        <p class="text-sm mt-0.5" style="color:var(--text-muted)">Stage-level performance and failure tracking · last 24 hours</p>
    </div>
    <a href="{{ route('admin.platform-usage') }}"
       style="font-size:12px;padding:6px 12px;border-radius:8px;border:1px solid var(--border);color:var(--text-secondary);text-decoration:none">
        Token Usage →
    </a>
</div>

{{-- KPI row --}}
<div class="ph-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="ph-card">
        <div class="ph-num ph-ok">{{ number_format($totalCompleted24h) }}</div>
        <div class="ph-lbl">Completed (24h)</div>
        <div class="ph-sub">full pipeline runs</div>
    </div>
    <div class="ph-card">
        <div class="ph-num {{ $totalFailed24h > 0 ? 'ph-err' : 'ph-ok' }}">{{ number_format($totalFailed24h) }}</div>
        <div class="ph-lbl">Stage Failures (24h)</div>
        <div class="ph-sub">across all stages</div>
    </div>
    <div class="ph-card">
        <div class="ph-num {{ $totalStuck > 0 ? 'ph-warn' : 'ph-ok' }}">{{ $totalStuck }}</div>
        <div class="ph-lbl">Stuck > 5 min</div>
        <div class="ph-sub">started, not completed</div>
    </div>
    <div class="ph-card">
        <div class="ph-num">{{ $avgDraftMs ? round($avgDraftMs / 1000, 1) . 's' : '—' }}</div>
        <div class="ph-lbl">Avg Draft Time</div>
        <div class="ph-sub">slowest AI stage</div>
    </div>
</div>

{{-- Stage breakdown table --}}
<div class="ph-sec">Stage Breakdown — Last 24h</div>
<div class="ph-card" style="padding:0;overflow:hidden;margin-bottom:20px">
<table class="ph-table">
    <thead>
        <tr>
            <th>Stage</th>
            <th>Started</th>
            <th>Completed</th>
            <th>Failed</th>
            <th>Success rate</th>
            <th>Avg time</th>
            <th>Max time</th>
            <th>Retries (24h)</th>
        </tr>
    </thead>
    <tbody>
    @foreach($stageOrder as $sk)
        @php $s = $stageSummary->get($sk); $r = $retryRate->get($sk); @endphp
        @if(!$s) @continue @endif
        @php
            $rate = $s->started > 0 ? round(($s->completed / $s->started) * 100) : 0;
            $rateColor = $rate >= 95 ? 'ph-ok' : ($rate >= 80 ? 'ph-warn' : 'ph-err');
        @endphp
        <tr>
            <td><span class="ph-stage">{{ $sk }}</span></td>
            <td>{{ number_format($s->started) }}</td>
            <td class="ph-ok">{{ number_format($s->completed) }}</td>
            <td class="{{ $s->failed > 0 ? 'ph-err' : '' }}">{{ number_format($s->failed) }}</td>
            <td>
                <span class="{{ $rateColor }}">{{ $rate }}%</span>
                <span class="ph-bar-wrap" style="margin-left:6px">
                    <span class="ph-bar {{ $rate < 80 ? 'ph-bar-err' : '' }}" style="width:{{ $rate }}%"></span>
                </span>
            </td>
            <td>{{ $s->avg_ms ? round($s->avg_ms / 1000, 2) . 's' : '—' }}</td>
            <td style="color:{{ $s->max_ms > 30000 ? '#f87171' : 'var(--text-muted)' }}">
                {{ $s->max_ms ? round($s->max_ms / 1000, 1) . 's' : '—' }}
            </td>
            <td class="{{ $r?->retries > 0 ? 'ph-warn' : '' }}">{{ $r?->retries ?? 0 }}</td>
        </tr>
    @endforeach
    @if($stageSummary->isEmpty())
        <tr><td colspan="8" class="ph-empty">No stage data in the last 24 hours</td></tr>
    @endif
    </tbody>
</table>
</div>

{{-- Stuck transactions --}}
<div class="ph-sec">
    <span class="{{ $totalStuck > 0 ? '' : '' }}">
        @if($totalStuck > 0)<span class="ph-stuck-dot"></span>@endif
        Stuck Transactions
        @if($totalStuck > 0)
            <span style="font-size:11px;font-weight:400;color:var(--text-muted);margin-left:6px">started >5 min ago with no completion or failure</span>
        @endif
    </span>
</div>
@if($stuck->isEmpty())
    <div class="ph-card ph-empty ph-ok" style="padding:20px">✓ No stuck transactions</div>
@else
<div class="ph-card" style="padding:0;overflow:hidden;margin-bottom:20px">
<table class="ph-table">
    <thead>
        <tr>
            <th>TX ID</th>
            <th>Stage</th>
            <th>Worker</th>
            <th>Attempt</th>
            <th>Stuck Since</th>
            <th>Deployment</th>
        </tr>
    </thead>
    <tbody>
    @foreach($stuck as $s)
        <tr>
            <td style="font-family:monospace;font-size:10px">{{ $s->tx_id }}</td>
            <td><span class="ph-stage ph-warn">{{ $s->stage_key }}</span></td>
            <td>{{ $s->worker_slug }}</td>
            <td>{{ $s->attempt }}</td>
            <td style="color:#fbbf24">{{ \Carbon\Carbon::parse($s->created_at)->diffForHumans() }}</td>
            <td>
                @if($s->deployment_id)
                <a href="{{ route('admin.tenants.show', $s->user_id) }}" style="color:var(--accent-text);text-decoration:none">
                    dep#{{ $s->deployment_id }}
                </a>
                @else —
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif

{{-- Recent failures --}}
<div class="ph-sec">Recent Failures — Last 24h</div>
@if($recentFailures->isEmpty())
    <div class="ph-card ph-empty ph-ok" style="padding:20px">✓ No failures in the last 24 hours</div>
@else
<div class="ph-card" style="padding:0;overflow:hidden;margin-bottom:20px">
<table class="ph-table">
    <thead>
        <tr>
            <th>TX ID</th>
            <th>Stage</th>
            <th>Worker</th>
            <th>Attempt</th>
            <th>Error</th>
            <th>Tenant</th>
            <th>When</th>
        </tr>
    </thead>
    <tbody>
    @foreach($recentFailures as $f)
        <tr>
            <td style="font-family:monospace;font-size:10px">{{ $f->tx_id }}</td>
            <td><span class="ph-stage ph-err">{{ $f->stage_key }}</span></td>
            <td>{{ $f->worker_slug }}</td>
            <td class="{{ $f->attempt > 1 ? 'ph-warn' : '' }}">{{ $f->attempt }}</td>
            <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#f87171" title="{{ $f->error_summary }}">
                {{ $f->error_summary ?: '—' }}
            </td>
            <td style="font-size:11px">{{ $f->email }}</td>
            <td style="font-size:11px;color:var(--text-muted)">{{ \Carbon\Carbon::parse($f->created_at)->diffForHumans() }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif

{{-- Avg duration per worker per stage (7 days) --}}
@if($stageDurations->isNotEmpty())
<div class="ph-sec">Avg Stage Duration by Worker — Last 7 Days</div>
@foreach($stageDurations as $workerSlug => $rows)
<div style="margin-bottom:16px">
    <div style="font-size:11px;font-weight:700;color:var(--text-muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em">{{ $workerSlug }}</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
    @foreach($rows as $row)
        @php $secs = round($row->avg_ms / 1000, 2); @endphp
        <div class="ph-card" style="padding:10px 14px;min-width:100px">
            <div style="font-size:15px;font-weight:700;color:{{ $secs > 10 ? '#fbbf24' : 'var(--text-primary)' }}">{{ $secs }}s</div>
            <div style="font-size:10px;color:var(--text-muted);font-family:monospace">{{ $row->stage_key }}</div>
            <div style="font-size:10px;color:var(--text-muted)">{{ number_format($row->runs) }} runs</div>
        </div>
    @endforeach
    </div>
</div>
@endforeach
@endif

{{-- Hourly throughput sparkline (text-based) --}}
@if($hourlyThroughput->isNotEmpty())
<div class="ph-sec">Hourly Pipeline Completions — Last 24h</div>
<div class="ph-card">
    @php $maxCount = $hourlyThroughput->max('count') ?: 1; @endphp
    <div style="display:flex;align-items:flex-end;gap:4px;height:48px">
    @foreach($hourlyThroughput as $h)
        @php $pct = round(($h->count / $maxCount) * 100); @endphp
        <div title="{{ $h->hour }}: {{ $h->count }}"
             style="flex:1;min-width:8px;height:{{ max(4,$pct*0.48) }}px;background:var(--accent);border-radius:2px 2px 0 0;opacity:.8"></div>
    @endforeach
    </div>
    <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text-muted);margin-top:4px">
        <span>{{ \Carbon\Carbon::parse($hourlyThroughput->first()->hour)->format('H:i') }}</span>
        <span>{{ $hourlyThroughput->sum('count') }} total completions</span>
        <span>{{ \Carbon\Carbon::parse($hourlyThroughput->last()->hour)->format('H:i') }}</span>
    </div>
</div>
@endif

</x-app-layout>

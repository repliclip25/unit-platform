<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $dep->name }} · Billing — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
button,select,input,textarea{outline:none}
button:focus,select:focus{outline:none;box-shadow:none}
html,body{height:100%;overflow:hidden}

:root,[data-theme="dark"]{
  --db-bg:#0D0D0D; --db-card:#1A1A1A; --db-text:#F5F5F5; --db-text-muted:#9CA3AF;
  --db-border:rgba(255,255,255,.14); --db-chip:#262626;
  --db-invert-bg:#F5F5F5; --db-invert-text:#0D0D0D;
}
[data-theme="light"]{
  --db-bg:#F4F3F1; --db-card:#ffffff; --db-text:#0D0D0D; --db-text-muted:#6B7280;
  --db-border:#E5E7EB; --db-chip:#ECEAE6;
  --db-invert-bg:#0D0D0D; --db-invert-text:#ffffff;
}

body{font-family:'Inter',sans-serif;background:var(--db-bg);color:var(--db-text);-webkit-font-smoothing:antialiased}

/* ── SHELL (identical to /desk/{slug}, /workers/{slug}/overview, /memory, /templates, /rules, /fast-track, /connect) ── */
.ob-shell{display:flex;flex-direction:column;height:100vh;overflow:hidden}
.ob-topbar{background:var(--db-bg);display:flex;align-items:center;justify-content:space-between;padding:0 24px;height:52px;flex-shrink:0}
.ob-topbar-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.ob-topbar-name{font-size:13.5px;font-weight:700;color:var(--db-text)}
.ob-topbar-email{font-size:12px;color:var(--db-text-muted)}
.ob-topbar-right{display:flex;align-items:center;gap:12px}
.ob-token-badge{font-size:11px;font-weight:600;color:var(--db-text-muted);background:var(--db-chip);border-radius:5px;padding:2px 7px;white-space:nowrap}
.ob-theme-toggle{width:36px;height:20px;border-radius:10px;border:none;cursor:pointer;position:relative;background:var(--db-chip)}
.ob-theme-toggle::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:var(--db-invert-bg);transition:transform .2s ease}
[data-theme="dark"] .ob-theme-toggle::after{transform:translateX(16px)}
.ob-menu-wrap{position:relative}
.ob-hamburger{width:32px;height:32px;border-radius:8px;border:1px solid var(--db-border);background:var(--db-card);display:flex;align-items:center;justify-content:center;cursor:pointer}
.ob-hamburger svg{width:15px;height:15px;stroke:var(--db-text);stroke-width:2;fill:none}
.ob-menu-dropdown{position:absolute;top:calc(100% + 8px);right:0;min-width:220px;background:var(--db-card);border:1px solid var(--db-border);border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.18);padding:8px;z-index:50;display:none}
.ob-menu-dropdown.open{display:block}
.ob-menu-user{padding:8px 10px 10px;border-bottom:1px solid var(--db-border);margin-bottom:6px}
.ob-menu-avatar{width:34px;height:34px;border-radius:50%;background:var(--db-chip);color:var(--db-text);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0}
.ob-menu-item-icon{width:13px;height:13px;stroke:currentColor;stroke-width:1.8;fill:none;margin-right:8px;vertical-align:-2px;flex-shrink:0}
.ob-menu-token{padding:0 10px 8px}
.ob-menu-item{display:block;width:100%;text-align:left;padding:8px 10px;border-radius:8px;font-size:13.5px;font-weight:600;color:var(--db-text);text-decoration:none;background:none;border:none;cursor:pointer;font-family:inherit}
.ob-menu-item:hover{background:var(--db-chip)}
.ob-menu-mobile-links{display:none}

.ob-page{display:grid;grid-template-columns:260px 1fr;flex:1;overflow:hidden}
.mem-card-area{display:grid;grid-template-columns:1fr 320px;margin:12px 12px 12px 0;background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.ob-sidebar{background:var(--db-bg);display:flex;flex-direction:column;overflow-y:auto}
.ob-steps{display:flex;flex-direction:column;padding:18px 24px 0;flex:1}
.ob-workers-hd{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:10px}
.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative;text-decoration:none;color:inherit}
.ob-step:not(:last-child) .ob-step-rail::after{content:'';position:absolute;left:13px;top:30px;width:2px;height:calc(100% - 6px);background:var(--db-border);border-radius:2px}
.ob-step.done:not(:last-child) .ob-step-rail::after{background:var(--db-invert-bg)}
.ob-step-rail{position:relative;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding-bottom:20px}
.ob-step:last-child .ob-step-rail{padding-bottom:0}
.ob-step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;position:relative;z-index:1;flex-shrink:0;overflow:hidden}
.ob-step.pending .ob-step-num{background:var(--db-chip);color:var(--db-text-muted);border:1.5px solid var(--db-border)}
.ob-step.done .ob-step-num{background:var(--db-invert-bg);color:var(--db-invert-text)}
.ob-step-body{padding-top:4px;padding-bottom:20px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:14px;font-weight:700;color:var(--db-text);line-height:1.2}
.ob-step.pending .ob-step-label{color:var(--db-text-muted)}
.ob-step-desc{font-size:12px;color:var(--db-text-muted);margin-top:2px;line-height:1.4;display:flex;align-items:center;gap:5px}

.ob-links-section{padding:16px 24px 8px;border-top:1px solid var(--db-border);flex-shrink:0}
.ob-links-hd{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:8px}
.ob-link{display:flex;align-items:center;gap:9px;padding:6px 10px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:500;color:var(--db-text-muted);transition:all .12s}
.ob-link:hover{background:var(--db-card);color:var(--db-text)}
.ob-link svg{width:13px;height:13px;stroke:currentColor;stroke-width:1.8;fill:none;flex-shrink:0}
.ob-link.active{background:var(--db-card);color:var(--db-text)}

.ob-security{margin:8px 24px 16px;padding:13px 15px;border-radius:12px;background:var(--db-chip);border:1px solid var(--db-border);flex-shrink:0}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:4px}
.ob-security-row svg{width:12px;height:12px;stroke:var(--db-text-muted);flex-shrink:0;fill:none}
.ob-security-title{font-size:12.5px;font-weight:700;color:var(--db-text)}
.ob-security p{font-size:11.5px;color:var(--db-text-muted);line-height:1.55}

.mem-right{background:var(--db-card);border-left:1px solid var(--db-border);overflow-y:auto}

/* ── CONTENT ── */
.mem-main{overflow-y:auto;padding:28px 32px 60px}
.mem-wrap{max-width:900px;margin:0 auto}

.mem-status{border-radius:12px;padding:10px 14px;font-size:13.5px;margin-bottom:16px}
.mem-status.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#22c55e}
.mem-status.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444}

.wb-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);margin-bottom:4px}
.wb-sub{font-size:13px;color:var(--db-text-muted);margin-bottom:20px}

.wb-banner{border-radius:12px;padding:14px 18px;margin-bottom:18px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px}
.wb-banner.trial{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25)}
.wb-banner.active{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25)}
.wb-banner.warn{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25)}
.wb-banner-title{font-size:13px;font-weight:700;color:var(--db-text);margin-bottom:2px}
.wb-banner-sub{font-size:12px;color:var(--db-text-muted);line-height:1.5}
.wb-bar{margin-top:8px;width:100%;max-width:280px;height:5px;border-radius:99px;overflow:hidden;background:var(--db-chip)}
.wb-bar-fill{height:100%;border-radius:99px;background:#f59e0b}
.wb-bar-fill.danger{background:#ef4444}
.wb-btn{flex-shrink:0;padding:9px 16px;border-radius:9px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text);text-decoration:none;white-space:nowrap;display:inline-block}
.wb-btn:hover{opacity:.9}
.wb-btn-secondary{flex-shrink:0;padding:8px 14px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text-muted);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;white-space:nowrap;display:inline-block}
.wb-btn-secondary:hover{background:var(--db-chip);color:var(--db-text)}

.wb-section-title{font-size:13px;font-weight:700;color:var(--db-text);margin:24px 0 12px}
.wb-section-title:first-child{margin-top:0}

.wb-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:8px}
@media(max-width:700px){.wb-stats{grid-template-columns:repeat(2,1fr)}}
.wb-stat{border:1px solid var(--db-border);border-radius:12px;padding:14px}
.wb-stat-label{font-size:11px;color:var(--db-text-muted)}
.wb-stat-num{font-size:1.4rem;font-weight:900;color:var(--db-text);margin-top:4px}
.wb-stat-sub{font-size:11px;color:var(--db-text-muted);margin-top:2px}

.wb-card{border:1px solid var(--db-border);border-radius:16px;padding:18px 20px;margin-bottom:16px}
.wb-card-title{font-size:13px;font-weight:700;color:var(--db-text);margin-bottom:14px}
.wb-row{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;font-size:12.5px;padding:7px 0}
.wb-row-lbl{color:var(--db-text-muted)}
.wb-row-val{color:var(--db-text);font-family:monospace;flex-shrink:0}
.wb-row-note{color:#f59e0b;font-size:11px;margin-top:3px}
.wb-row-total{border-top:1px solid var(--db-border);padding-top:10px;margin-top:4px;font-weight:700}
.wb-row-total .wb-row-val{color:var(--accent-text,var(--db-text))}
.wb-note{font-size:11px;color:var(--db-text-muted);margin-top:10px;line-height:1.5}

.wb-list{border:1px solid var(--db-border);border-radius:16px;overflow:hidden}
.wb-list-row{padding:12px 18px;border-bottom:1px solid var(--db-border);display:flex;align-items:center;gap:14px}
.wb-list-row:last-child{border-bottom:none}
.wb-stage-tag{font-size:11px;font-family:monospace;color:var(--db-text);background:var(--db-chip);border:1px solid var(--db-border);padding:2px 8px;border-radius:6px;flex-shrink:0;width:100px;text-align:center}
.wb-stage-bar-track{flex:1;height:5px;border-radius:99px;overflow:hidden;background:var(--db-chip)}
.wb-stage-bar-fill{height:100%;border-radius:99px;background:var(--db-invert-bg)}
.wb-stage-tokens{font-size:11px;color:var(--db-text-muted);width:96px;text-align:right;flex-shrink:0}
.wb-stage-cost{text-align:right;flex-shrink:0;width:110px}
.wb-stage-cost-val{font-size:11.5px;font-family:monospace;color:var(--db-text)}
.wb-stage-cost-calls{font-size:10.5px;color:var(--db-text-muted)}

.wb-spark{display:flex;align-items:flex-end;gap:2px;height:64px}
.wb-spark-bar{flex:1;border-radius:2px;background:rgba(var(--accent-rgb,241,211,98),.5);min-height:2px}
.wb-spark-labels{display:flex;justify-content:space-between;margin-top:14px;font-size:11px;color:var(--db-text-muted)}

.wb-history-row{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px;padding:10px 18px;font-size:12.5px}
.wb-history-head{color:var(--db-text-muted);font-size:10.5px;text-transform:uppercase;letter-spacing:.06em;border-bottom:1px solid var(--db-border)}
.wb-history-row:not(.wb-history-head){border-bottom:1px solid var(--db-border)}
.wb-history-row:last-child{border-bottom:none}
.wb-history-right{text-align:right}

.wb-alltime{display:flex;flex-wrap:wrap;gap:28px}
.wb-alltime-num{font-size:1.15rem;font-weight:800;color:var(--db-text)}
.wb-alltime-lbl{font-size:11px;color:var(--db-text-muted);margin-top:2px}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow-x:hidden;overflow-y:auto;height:auto;width:100%}
  .ob-shell{height:auto;overflow:visible;width:100%}
  .ob-shell,.ob-shell *{min-width:0}
  .ob-topbar{height:auto;padding:12px 16px;flex-wrap:wrap;gap:6px}
  .ob-topbar-logo{font-size:18px}
  .ob-topbar-email{display:none}
  .ob-page{display:block;height:auto;overflow:visible;width:100%}
  .ob-sidebar{width:100%;flex-direction:column;padding:0;overflow:hidden;border-bottom:none}
  .ob-steps,.ob-links-section,.ob-security{display:none}
  .ob-menu-mobile-links{display:block}
  .mem-right{display:none}
  .mem-main{padding:16px}
  .mem-card-area{display:block;margin:0;border-radius:0;border:none;box-shadow:none;background:var(--db-card)}
  .wb-stats{grid-template-columns:1fr 1fr}
  .wb-history-row{grid-template-columns:1fr 1fr}
}
</style>
<script>
(function () {
  var saved = localStorage.getItem('unit-theme-v2') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();
</script>
</head>
<body>

@php
$isActiveSub = $billing?->status === 'active';
$isTrial     = $billing?->status === 'trial';
$trialUsed   = $billing?->trial_transactions_used ?? 0;
$trialLimit  = $billing?->trial_transactions_limit ?? 10;
$trialLeft   = max(0, $trialLimit - $trialUsed);
$trialPct    = $trialLimit > 0 ? round(($trialUsed / $trialLimit) * 100) : 0;

$flatRate      = (float) ($pricing?->monthly_flat_rate ?? 25.00);
$aiCostMonth   = (float) ($monthUsage?->cost_usd ?? 0);
$markup        = 1.30;
$aiPassthrough = $aiCostMonth * $markup;

$subStart    = $billing?->billing_period_start ? \Carbon\Carbon::parse($billing->billing_period_start) : null;
$isProrated  = $isActiveSub && $subStart && $subStart->month == now()->month && $subStart->year == now()->year && $subStart->day > 1;
$daysInMonth = now()->daysInMonth;
$daysActive  = $isProrated ? (now()->diffInDays($subStart) + 1) : $daysInMonth;
$flatCharge  = $isProrated ? round($flatRate * ($daysActive / $daysInMonth), 2) : $flatRate;
$estimatedBill = $isActiveSub ? ($flatCharge + $aiPassthrough) : 0;

$tokensIn  = (int) ($monthUsage?->tokens_in  ?? 0);
$tokensOut = (int) ($monthUsage?->tokens_out ?? 0);
$txCount   = (int) ($monthUsage?->tx_count   ?? 0);

$allTimeCost = (float) ($allTime?->cost ?? 0);
$allTimeTx   = (int)   ($allTime?->tx_count ?? 0);

$tokenFmt = $tokenTotal >= 1000000 ? number_format($tokenTotal/1000000,1).'M' : number_format($tokenTotal);
$sidebarLinks = [
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('workers.memory',$dep->worker_slug), false],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('workers.templates',['slug'=>$dep->worker_slug]), false],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('workers.rules',$dep->worker_slug), false],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('workers.fast-track.page',$dep->worker_slug), false],
  ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('workers.connect',$dep->worker_slug), false],
  ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('billing'), true],
  ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('transactions').'?deployment='.$dep->id, false],
];
@endphp

<div class="ob-shell">

{{-- ══ TOP BAR ══ --}}
<div class="ob-topbar">
  <a href="{{ route('dashboard') }}" class="ob-topbar-logo" style="text-decoration:none">UNIT</a>
  <div class="ob-topbar-right">
    <a href="{{ route('profile.show') }}" class="ob-topbar-name" style="text-decoration:none">{{ auth()->user()->name }}</a>
    <button class="ob-theme-toggle" id="theme-toggle" type="button" title="Toggle dark/light mode" aria-label="Toggle theme"></button>
    <div class="ob-menu-wrap">
      <button class="ob-hamburger" id="menu-toggle" type="button" aria-label="Menu">
        <svg viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="ob-menu-dropdown" id="menu-dropdown">
        <div class="ob-menu-user" style="display:flex;align-items:center;gap:10px">
          <div class="ob-menu-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
          <div style="min-width:0">
            <div class="ob-topbar-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth()->user()->name }}</div>
            <div class="ob-topbar-email" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth()->user()->email }}</div>
          </div>
        </div>
        <div class="ob-menu-mobile-links">
          <a href="{{ route('dashboard') }}" class="ob-menu-item">
            <svg viewBox="0 0 24 24" class="ob-menu-item-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
          </a>
          @foreach($sidebarLinks as [$lbl,$ico,$href,])
          <a href="{{ $href }}" class="ob-menu-item">
            <svg viewBox="0 0 24 24" class="ob-menu-item-icon"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ico }}"/></svg>
            {{ $lbl }}
          </a>
          @endforeach
          <div style="border-top:1px solid var(--db-border);margin:6px 0"></div>
        </div>
        <div class="ob-menu-token"><span class="ob-token-badge">{{ $tokenFmt }} tokens</span></div>
        <a href="{{ route('settings.api-keys') }}" class="ob-menu-item">Settings</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="ob-menu-item">Logout</button></form>
      </div>
    </div>
  </div>
</div>

<div class="ob-page">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ob-sidebar">
    <div class="ob-steps">
      <div class="ob-workers-hd">
        <a href="{{ route('profile.show') }}" style="color:inherit;text-decoration:none">{{ strtoupper($firstName) }}'S WORKERS</a>
      </div>
      @foreach($workerCatalog as $wc)
      @php
        $wDot  = $wc->status==='active' ? '#22c55e' : '#f59e0b';
        $wHref = !$wc->active ? route('workers.page') : ($wc->slug==='ava' ? route('desk.ava') : route('workers.overview',$wc->slug));
        $isActive = $wc->active && $wc->slug === $dep->worker_slug;
      @endphp
      <a href="{{ $wHref }}" class="ob-step {{ $isActive ? 'active' : ($wc->active ? 'done' : 'pending') }}" style="text-decoration:none{{ !$wc->active ? ';opacity:.5' : '' }}">
        <div class="ob-step-rail">
          <div class="ob-step-num" style="padding:0">
            @if($wc->image)
              <img src="{{ $wc->image }}" style="width:100%;height:100%;object-fit:cover;display:block{{ !$wc->active ? ';filter:grayscale(1)' : '' }}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
              <span style="display:none;font-size:11px;font-weight:800;color:#6B7280;width:100%;height:100%;align-items:center;justify-content:center">{{ substr($wc->name,0,1) }}</span>
            @else
              <span style="font-size:11px;font-weight:800;color:#6B7280">{{ substr($wc->name,0,1) }}</span>
            @endif
          </div>
        </div>
        <div class="ob-step-body">
          <div class="ob-step-label">{{ $wc->name }}</div>
          <div class="ob-step-desc">
            @if($wc->active)
              <span style="width:5px;height:5px;border-radius:50%;background:{{ $wDot }};flex-shrink:0;display:inline-block"></span>{{ $wc->role }}
            @else
              Not hired — {{ $wc->role }}
            @endif
          </div>
        </div>
      </a>
      @endforeach
      <a href="{{ route('workers.page') }}" class="ob-step pending" style="text-decoration:none;margin-top:4px">
        <div class="ob-step-rail"><div class="ob-step-num" style="background:var(--db-chip);border:1.5px dashed var(--db-border);color:var(--db-text-muted);font-size:16px;font-weight:400">+</div></div>
        <div class="ob-step-body"><div class="ob-step-label">Hire a worker</div></div>
      </a>
    </div>

    <div class="ob-links-section">
      <div class="ob-links-hd">LINKS</div>
      @foreach($sidebarLinks as [$lbl,$ico,$href,$isActive2])
      <a href="{{ $href }}" class="ob-link {{ $isActive2 ? 'active' : '' }}">
        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ico }}"/></svg>
        {{ $lbl }}
      </a>
      @endforeach
    </div>

    <div class="ob-security">
      <div class="ob-security-row">
        <svg viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>Flat fee + AI passthrough at cost, billed to one invoice.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      <a href="{{ route('billing') }}" style="font-size:12px;color:var(--db-text-muted);text-decoration:none;display:inline-block;margin-bottom:8px">← Billing</a>
      <div class="wb-h1">Billing — {{ $dep->name }}</div>
      <div class="wb-sub">Usage and cost breakdown for this worker — subscription and plan live on the main <a href="{{ route('billing') }}" style="color:var(--db-text-muted);text-decoration:underline">Billing</a> page.</div>

      {{-- Status banner --}}
      @if($isTrial)
      <div class="wb-banner trial">
        <div>
          <div class="wb-banner-title">Trial Plan</div>
          <div class="wb-banner-sub">
            {{ $trialLeft }} of {{ $trialLimit }} free transactions remaining.
            @if($trialLeft === 0)
              <strong style="color:#ef4444">Trial exhausted — upgrade to continue processing.</strong>
            @else
              Upgrade anytime to unlock unlimited processing and full AI cost reporting.
            @endif
          </div>
          <div class="wb-bar"><div class="wb-bar-fill {{ $trialPct >= 80 ? 'danger' : '' }}" style="width:{{ $trialPct }}%"></div></div>
        </div>
        <a href="{{ route('billing.checkout', $dep->id) }}" class="wb-btn">Upgrade →</a>
      </div>
      @elseif($isActiveSub)
      <div class="wb-banner active">
        <div>
          <div class="wb-banner-title" style="color:#22c55e">✓ Active Subscription</div>
          <div class="wb-banner-sub">${{ number_format($flatRate, 2) }}/mo flat + AI passthrough at cost + 30%</div>
        </div>
        <a href="{{ route('billing.portal') }}" class="wb-btn-secondary">Manage billing →</a>
      </div>
      @else
      <div class="wb-banner warn">
        <div>
          <div class="wb-banner-title" style="color:#ef4444">{{ $billing ? ucfirst($billing->status) : 'No billing record' }}</div>
          @if(!$billing)
            <div class="wb-banner-sub">This deployment was created without a billing record — likely deployed before the billing system was in place. It can still process emails but cannot be subscribed until an admin backfills the record.</div>
            <div class="wb-banner-sub" style="margin-top:4px">Contact your platform admin to fix this in Tenant Controls → Deployment Billing Status.</div>
          @else
            <div class="wb-banner-sub">Contact support or upgrade to restore access.</div>
          @endif
        </div>
      </div>
      @endif

      {{-- This month snapshot --}}
      <div class="wb-section-title">This Month — {{ now()->format('F Y') }}</div>
      <div class="wb-stats">
        <div class="wb-stat">
          <div class="wb-stat-label">Transactions</div>
          <div class="wb-stat-num">{{ number_format($txCount) }}</div>
          @if($isActiveSub && $pricing?->included_transactions)
          <div class="wb-stat-sub">of {{ number_format($pricing->included_transactions) }} included</div>
          @endif
        </div>
        <div class="wb-stat">
          <div class="wb-stat-label">Tokens In</div>
          <div class="wb-stat-num">{{ $tokensIn >= 1000 ? number_format($tokensIn/1000, 1).'K' : number_format($tokensIn) }}</div>
          <div class="wb-stat-sub">input tokens</div>
        </div>
        <div class="wb-stat">
          <div class="wb-stat-label">Tokens Out</div>
          <div class="wb-stat-num">{{ $tokensOut >= 1000 ? number_format($tokensOut/1000, 1).'K' : number_format($tokensOut) }}</div>
          <div class="wb-stat-sub">output tokens</div>
        </div>
        <div class="wb-stat">
          <div class="wb-stat-label">AI Cost</div>
          <div class="wb-stat-num">${{ number_format($aiCostMonth, 4) }}</div>
          @if($isActiveSub)
          <div class="wb-stat-sub">+30% = ${{ number_format($aiPassthrough, 4) }} billed</div>
          @else
          <div class="wb-stat-sub">platform cost</div>
          @endif
        </div>
      </div>

      {{-- Billing model breakdown (active subscribers) --}}
      @if($isActiveSub)
      <div class="wb-card">
        <div class="wb-card-title">Estimated Bill This Month</div>
        <div class="wb-row">
          <div class="wb-row-lbl">
            Platform flat rate
            @if($isProrated)
            <div class="wb-row-note">Prorated {{ $daysActive }}/{{ $daysInMonth }} days (subscribed {{ $subStart->format('M j') }}) · full rate ${{ number_format($flatRate, 2) }}/mo</div>
            @endif
          </div>
          <span class="wb-row-val">${{ number_format($flatCharge, 2) }}</span>
        </div>
        <div class="wb-row">
          <span class="wb-row-lbl">AI passthrough (${{ number_format($aiCostMonth, 4) }} × 1.30)</span>
          <span class="wb-row-val">${{ number_format($aiPassthrough, 4) }}</span>
        </div>
        <div class="wb-row wb-row-total">
          <span class="wb-row-lbl" style="color:var(--db-text)">Estimated total</span>
          <span class="wb-row-val">${{ number_format($estimatedBill, 2) }}</span>
        </div>
        <div class="wb-note">Final invoice generated at end of billing period. AI passthrough reflects actual Anthropic/OpenAI cost + 30% platform fee.</div>
      </div>
      @endif

      {{-- Per-stage breakdown --}}
      @if($stageBreakdown->isNotEmpty())
      <div class="wb-section-title">AI Cost by Pipeline Stage</div>
      <div class="wb-list">
        @php $maxCost = $stageBreakdown->max('cost'); @endphp
        @foreach($stageBreakdown as $stage)
        <div class="wb-list-row">
          <span class="wb-stage-tag">{{ $stage->stage }}</span>
          <div class="wb-stage-bar-track"><div class="wb-stage-bar-fill" style="width:{{ $maxCost > 0 ? round(($stage->cost / $maxCost) * 100) : 0 }}%"></div></div>
          <span class="wb-stage-tokens">{{ number_format($stage->tokens) }} tokens</span>
          <div class="wb-stage-cost">
            <div class="wb-stage-cost-val">${{ number_format($stage->cost, 6) }}</div>
            <div class="wb-stage-cost-calls">{{ number_format($stage->calls) }} calls</div>
          </div>
        </div>
        @endforeach
      </div>
      @endif

      {{-- Daily sparkline (last 30 days) --}}
      @if($dailySpend->isNotEmpty())
      <div class="wb-section-title">Daily AI Cost — Last 30 Days</div>
      <div class="wb-card">
        @php
          $days   = collect();
          $maxDay = 0;
          for ($i = 29; $i >= 0; $i--) {
            $d    = now()->subDays($i)->format('Y-m-d');
            $cost = (float) ($dailySpend[$d]->cost ?? 0);
            $days->push(['day' => $d, 'label' => now()->subDays($i)->format('M j'), 'cost' => $cost]);
            if ($cost > $maxDay) $maxDay = $cost;
          }
        @endphp
        <div class="wb-spark">
          @foreach($days as $d)
          @php $pct = $maxDay > 0 ? ($d['cost'] / $maxDay) * 100 : 0; @endphp
          <div class="wb-spark-bar" style="height:{{ max(2, $pct) }}%" title="{{ $d['label'] }}: ${{ number_format($d['cost'], 6) }}"></div>
          @endforeach
        </div>
        <div class="wb-spark-labels">
          <span>$0.000000</span>
          <span>Max/day: ${{ number_format($maxDay, 6) }}</span>
        </div>
      </div>
      @endif

      {{-- Monthly history --}}
      @if($monthlyHistory->isNotEmpty())
      <div class="wb-section-title">Monthly History</div>
      <div class="wb-list">
        <div class="wb-history-row wb-history-head">
          <span>Month</span>
          <span class="wb-history-right">Transactions</span>
          <span class="wb-history-right">Tokens</span>
          <span class="wb-history-right">AI Cost</span>
        </div>
        @foreach($monthlyHistory->reverse() as $month)
        <div class="wb-history-row">
          <span>{{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('M Y') }}</span>
          <span class="wb-history-right" style="color:var(--db-text-muted)">{{ number_format($month->tx_count) }}</span>
          <span class="wb-history-right" style="color:var(--db-text-muted)">{{ number_format($month->tokens) }}</span>
          <span class="wb-history-right" style="font-family:monospace">${{ number_format($month->cost, 4) }}</span>
        </div>
        @endforeach
      </div>
      @endif

      {{-- All-time --}}
      <div class="wb-section-title">All-Time Totals</div>
      <div class="wb-card">
        <div class="wb-alltime">
          <div>
            <div class="wb-alltime-num">{{ number_format($allTimeTx) }}</div>
            <div class="wb-alltime-lbl">transactions processed</div>
          </div>
          <div>
            <div class="wb-alltime-num">{{ number_format($allTime?->tokens ?? 0) }}</div>
            <div class="wb-alltime-lbl">total tokens</div>
          </div>
          <div>
            <div class="wb-alltime-num">${{ number_format($allTimeCost, 4) }}</div>
            <div class="wb-alltime-lbl">AI cost (platform)</div>
          </div>
          @if($isActiveSub)
          <div>
            <div class="wb-alltime-num">${{ number_format($allTimeCost * $markup, 4) }}</div>
            <div class="wb-alltime-lbl">AI billed (+ 30%)</div>
          </div>
          @endif
        </div>
      </div>

      {{-- Billing model explainer --}}
      <div class="wb-card" style="margin-bottom:0">
        <div class="wb-card-title" style="text-transform:uppercase;font-size:10.5px;letter-spacing:.06em;color:var(--db-text-muted)">How billing works</div>
        <div class="wb-stats" style="grid-template-columns:1fr 1fr 1fr;margin-bottom:0">
          <div>
            <div style="font-size:12px;font-weight:600;color:var(--db-text)">Platform flat fee</div>
            <div class="wb-stat-sub" style="margin-top:4px">${{ number_format($flatRate, 2) }}/mo per active worker. Covers infrastructure, scheduling, watch renewals, and support.</div>
          </div>
          <div>
            <div style="font-size:12px;font-weight:600;color:var(--db-text)">AI passthrough</div>
            <div class="wb-stat-sub" style="margin-top:4px">Actual token cost billed at cost + 30%. Tenants using their own API key pay flat fee only.</div>
          </div>
          <div>
            <div style="font-size:12px;font-weight:600;color:var(--db-text)">Overage</div>
            <div class="wb-stat-sub" style="margin-top:4px">{{ number_format($pricing?->included_transactions ?? 500) }} transactions included. ${{ number_format($pricing?->overage_price_per_tx ?? 0.10, 2) }} per additional transaction beyond that.</div>
          </div>
        </div>
      </div>

    </div>
  </main>

  <aside class="mem-right"></aside>
  </div>

</div>{{-- ob-page --}}
</div>{{-- ob-shell --}}

<script>
(function () {
  document.getElementById('theme-toggle').addEventListener('click', function () {
    var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('unit-theme-v2', next);
  });

  var menuToggle = document.getElementById('menu-toggle');
  var menuDropdown = document.getElementById('menu-dropdown');
  menuToggle.addEventListener('click', function (e) {
    e.stopPropagation();
    menuDropdown.classList.toggle('open');
  });
  document.addEventListener('click', function (e) {
    if (!menuDropdown.contains(e.target) && e.target !== menuToggle) {
      menuDropdown.classList.remove('open');
    }
  });
})();
</script>
</body>
</html>

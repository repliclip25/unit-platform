<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $dep->name }} — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
button,select,input,textarea{outline:none}
button:focus,select:focus{outline:none;box-shadow:none}
html,body{height:100%;overflow:hidden}

/* ── THEME TOKENS: light default, dark override — same as /desk/{slug} ── */
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

/* ── OUTER SHELL ── */
.ob-shell{display:flex;flex-direction:column;height:100vh;overflow:hidden}

/* ── TOP BAR: logo left, theme toggle + menu right ── */
.ob-topbar{background:var(--db-bg);display:flex;align-items:center;justify-content:space-between;padding:0 24px;height:52px;flex-shrink:0}
.ob-topbar-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.ob-topbar-name{font-size:13.5px;font-weight:700;color:var(--db-text)}
.ob-topbar-email{font-size:12px;color:var(--db-text-muted)}
.ob-topbar-right{display:flex;align-items:center;gap:12px}
.ob-token-badge{font-size:11px;font-weight:600;color:var(--db-text-muted);background:var(--db-chip);border-radius:5px;padding:2px 7px;white-space:nowrap}
.ob-topbar-link{font-size:11px;font-weight:600;color:var(--db-text-muted);text-decoration:none}
.ob-topbar-link:hover{color:var(--db-text)}

.ob-theme-toggle{width:36px;height:20px;border-radius:10px;border:none;cursor:pointer;position:relative;transition:background .2s ease;flex-shrink:0;background:var(--db-chip)}
.ob-theme-toggle::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:var(--db-invert-bg);transition:transform .2s ease}
[data-theme="dark"] .ob-theme-toggle::after{transform:translateX(16px)}

.ob-menu-wrap{position:relative}
.ob-hamburger{width:32px;height:32px;border-radius:8px;border:1px solid var(--db-border);background:var(--db-card);display:flex;align-items:center;justify-content:center;cursor:pointer}
.ob-hamburger svg{width:15px;height:15px;stroke:var(--db-text);stroke-width:2;fill:none}
.ob-menu-dropdown{position:absolute;top:calc(100% + 8px);right:0;min-width:220px;background:var(--db-card);border:1px solid var(--db-border);border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.18);padding:8px;z-index:50;display:none}
.ob-menu-dropdown.open{display:block}
.ob-menu-user{padding:8px 10px 10px;border-bottom:1px solid var(--db-border);margin-bottom:6px}
.ob-menu-token{padding:0 10px 8px}
.ob-menu-item{display:block;width:100%;text-align:left;padding:8px 10px;border-radius:8px;font-size:13.5px;font-weight:600;color:var(--db-text);text-decoration:none;background:none;border:none;cursor:pointer;font-family:inherit}
.ob-menu-item:hover{background:var(--db-chip)}

/* ── PAGE: sidebar + content area ── */
.ob-page{display:grid;grid-template-columns:260px 1fr;flex:1;overflow:hidden}
.wo-card-area{display:grid;grid-template-columns:1fr 320px;margin:12px 12px 12px 0;background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)}

/* ── SIDEBAR — identical to /desk/{slug} ── */
.ob-sidebar{background:var(--db-bg);display:flex;flex-direction:column;overflow-y:auto}
.ob-steps{display:flex;flex-direction:column;padding:18px 24px 0;flex:1}
.ob-workers-hd{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}
.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative;text-decoration:none;color:inherit}
.ob-step:not(:last-child) .ob-step-rail::after{content:'';position:absolute;left:13px;top:30px;width:2px;height:calc(100% - 6px);background:var(--db-border);border-radius:2px}
.ob-step.done:not(:last-child) .ob-step-rail::after{background:var(--db-invert-bg)}
.ob-step-rail{position:relative;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding-bottom:20px}
.ob-step:last-child .ob-step-rail{padding-bottom:0}
.ob-step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;position:relative;z-index:1;flex-shrink:0;overflow:hidden}
.ob-step.pending .ob-step-num{background:var(--db-chip);color:var(--db-text-muted);border:1.5px solid var(--db-border)}
.ob-step.active  .ob-step-num{background:var(--db-invert-bg);color:var(--db-invert-text);box-shadow:0 0 0 4px rgba(128,128,128,.15)}
.ob-step.done    .ob-step-num{background:var(--db-invert-bg);color:var(--db-invert-text)}
.ob-step-body{padding-top:4px;padding-bottom:20px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:14px;font-weight:700;color:var(--db-text);line-height:1.2}
.ob-step.pending .ob-step-label{color:var(--db-text-muted)}
.ob-step-desc{font-size:12px;color:var(--db-text-muted);margin-top:2px;line-height:1.4;display:flex;align-items:center;gap:5px}
.ob-step.active .ob-step-desc{color:var(--db-text)}
.ob-step.active .ob-step-body{background:var(--db-card);border:1.5px solid var(--db-border);border-radius:12px;padding:10px 14px;margin-right:-4px}

.ob-links-section{padding:16px 24px 8px;border-top:1px solid var(--db-border);flex-shrink:0}
.ob-links-hd{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:8px}
.ob-link{display:flex;align-items:center;gap:9px;padding:6px 10px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:500;color:var(--db-text-muted);transition:all .12s}
.ob-link:hover{background:var(--db-card);color:var(--db-text)}
.ob-link svg{width:13px;height:13px;stroke:currentColor;stroke-width:1.8;fill:none;flex-shrink:0}

.ob-security{margin:8px 24px 16px;padding:13px 15px;border-radius:12px;background:var(--db-chip);border:1px solid var(--db-border);flex-shrink:0}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:4px}
.ob-security-row svg{width:12px;height:12px;stroke:var(--db-text-muted);flex-shrink:0;fill:none}
.ob-security-title{font-size:12.5px;font-weight:700;color:var(--db-text)}
.ob-security p{font-size:11.5px;color:var(--db-text-muted);line-height:1.55}

@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}

/* ── CONTENT AREA — this page's own single-column body ── */
.wo-main{overflow-y:auto;padding:28px 32px 60px}
.wo-wrap{max-width:640px;margin:0 auto}

.wo-identity{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;padding-left:2px}
.wo-name{font-size:1.4rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.wo-role{font-size:13px;color:var(--db-text-muted);margin-top:2px}
.wo-status{display:inline-flex;align-items:center;gap:6px;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:5px 12px;border-radius:99px}
.wo-status.active{background:rgba(34,197,94,.12);color:#22c55e}
.wo-status.paused{background:rgba(245,158,11,.12);color:#f59e0b}
.wo-status-dot{width:6px;height:6px;border-radius:50%;background:currentColor}

.wo-card{background:var(--db-bg);border:1px solid var(--db-border);border-radius:16px;padding:20px;margin-bottom:16px}
.wo-card-title{font-size:13.5px;font-weight:700;color:var(--db-text);margin-bottom:14px}

.wo-paywall{border-color:var(--db-border)}
.wo-paywall-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px}
.wo-paywall-title{font-size:13.5px;font-weight:700;color:var(--db-text)}
.wo-paywall-body{font-size:12px;color:var(--db-text-muted);margin-top:3px}
.wo-paywall-count{font-size:1.4rem;font-weight:900;color:var(--db-text);text-align:right}
.wo-paywall-count-label{font-size:10.5px;color:var(--db-text-muted);text-align:right}
.wo-plans{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.wo-plan{border:1px solid var(--db-border);border-radius:12px;padding:14px;position:relative}
.wo-plan.recommended{border-color:#F5C518;border-width:1.5px;background:rgba(245,197,24,.06)}
.wo-plan-badge{position:absolute;top:-10px;left:12px;font-size:9px;font-weight:700;background:#F5C518;color:#0D0D0D;padding:3px 8px;border-radius:99px}
.wo-plan-name{font-size:12.5px;font-weight:700;color:var(--db-text)}
.wo-plan-tagline{font-size:10.5px;color:var(--db-text-muted);margin-top:2px}
.wo-plan-price{font-size:1.3rem;font-weight:900;color:var(--db-text);margin:8px 0 2px}
.wo-plan.recommended .wo-plan-price{color:#B8890A}
.wo-plan-price span{font-size:11px;font-weight:500;color:var(--db-text-muted)}
.wo-plan-limit{font-size:11px;color:var(--db-text-muted);margin-bottom:10px}
/* Buttons are always black or white — gold is accent-only (badges/borders), never a button fill */
.wo-plan-btn{width:100%;padding:9px;border-radius:8px;border:none;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text)}

.wo-banner{display:flex;align-items:flex-start;gap:12px;border-radius:12px;padding:14px 16px;margin-bottom:12px}
.wo-banner.warn{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25)}
.wo-banner.notice{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25)}
.wo-banner-title{font-size:12.5px;font-weight:700;color:var(--db-text)}
.wo-banner-body{font-size:11.5px;color:var(--db-text-muted);margin-top:2px}
.wo-banner-action{flex-shrink:0;font-size:11.5px;font-weight:700;color:var(--db-text);text-decoration:none;white-space:nowrap}

.wo-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.wo-stat{background:var(--db-card);border:1px solid var(--db-border);border-radius:12px;padding:14px;text-align:center}
.wo-stat-num{font-size:1.4rem;font-weight:900;color:var(--db-text)}
.wo-stat-label{font-size:10px;color:var(--db-text-muted);margin-top:2px}

.wo-links{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.wo-link{display:flex;align-items:center;gap:10px;padding:12px;border-radius:10px;border:1px solid var(--db-border);text-decoration:none}
.wo-link:hover{background:var(--db-chip)}
.wo-link-icon{width:32px;height:32px;border-radius:8px;background:var(--db-chip);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.wo-link-icon svg{stroke:var(--db-text);stroke-width:1.8;fill:none}
.wo-link-label{font-size:12.5px;font-weight:600;color:var(--db-text)}

.wo-manage-row{display:flex;gap:10px;flex-wrap:wrap}
.wo-manage-btn{font-size:12px;font-weight:600;padding:9px 16px;border-radius:9px;border:1px solid var(--db-border);background:var(--db-card);color:var(--db-text);cursor:pointer;font-family:inherit}
.wo-manage-btn:hover{background:var(--db-chip)}
.wo-manage-btn.danger{color:#ef4444;border-color:rgba(239,68,68,.3)}

/* Per-transaction data columns (no canvas, just labeled rows) */
.wo-tx-back{font-size:12px;font-weight:600;color:var(--db-text-muted);text-decoration:none;display:inline-block;margin-bottom:14px}
.wo-tx-back:hover{color:var(--db-text)}
.wo-tx-header-row{display:flex;flex-direction:column;gap:2px;padding:9px 0;border-bottom:1px solid var(--db-border)}
.wo-tx-header-label{font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--db-text-muted)}
.wo-tx-header-val{font-size:13px;color:var(--db-text)}
.wo-tx-stage-title{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:var(--db-text);margin:16px 0 8px}
.wo-tx-stage-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.wo-tx-data-row{padding:6px 0;border-bottom:1px solid var(--db-border)}
.wo-tx-data-key{font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--db-text-muted)}
.wo-tx-data-val{font-size:12px;color:var(--db-text);line-height:1.6;white-space:pre-wrap;word-break:break-word}

/* ── RIGHT PANEL ── */
.wo-right{background:var(--db-card);border-left:1px solid var(--db-border);padding:20px;overflow-y:auto}
.wo-right-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:6px}
.wo-right-head{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:16px}
.wo-right-name{font-size:1.4rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);line-height:1}
.wo-right-role{font-size:12px;color:var(--db-text-muted);margin-top:3px}

.tx-switcher-wrap{position:relative;flex-shrink:0}
.tx-switcher-btn{display:flex;align-items:center;gap:5px;font-size:10.5px;font-weight:700;font-family:monospace;color:var(--db-text);background:var(--db-chip);border:1px solid var(--db-border);border-radius:7px;padding:5px 9px;cursor:pointer;text-decoration:none}
.tx-switcher-btn svg{stroke:var(--db-text-muted);stroke-width:2;fill:none}
.tx-switcher-dropdown{display:none;position:absolute;top:calc(100% + 6px);right:0;width:230px;max-height:260px;overflow-y:auto;background:var(--db-card);border:1px solid var(--db-border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);padding:6px;z-index:40}
.tx-switcher-dropdown.open{display:block}
.tx-switcher-item{display:block;width:100%;text-align:left;padding:7px 9px;border-radius:7px;text-decoration:none}
.tx-switcher-item:hover{background:var(--db-chip)}
.tx-switcher-item-label{font-size:11.5px;font-weight:700;color:var(--db-text)}
.tx-switcher-item-meta{font-size:10px;color:var(--db-text-muted);margin-top:1px}

.wo-ii-badge{display:inline-block;font-size:9px;font-weight:700;background:var(--db-chip);color:var(--db-text-muted);border-radius:5px;padding:2px 7px;margin-left:6px;vertical-align:middle}
.wo-ii-stats{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
.wo-ii-stat{border:1px solid var(--db-border);border-radius:10px;padding:10px}
.wo-ii-stat-num{font-size:1.3rem;font-weight:900;color:var(--db-text)}
.wo-ii-stat-label{font-size:10px;color:var(--db-text-muted);margin-top:2px}

.wo-timeline{display:flex;align-items:flex-end;gap:4px;height:70px;margin-bottom:8px}
.wo-timeline-col{flex:1;display:flex;flex-direction:column-reverse;height:100%;border-radius:3px 3px 0 0;overflow:hidden;background:var(--db-bg)}
.wo-timeline-seg{width:100%}
.wo-timeline-labels{display:flex;gap:4px;margin-bottom:14px}
.wo-timeline-labels span{flex:1;font-size:9px;color:var(--db-text-muted);text-align:center}
.wo-timeline-legend{display:flex;gap:12px;font-size:10.5px;color:var(--db-text-muted);margin-bottom:18px}
.wo-timeline-legend span{display:inline-flex;align-items:center;gap:4px}
.wo-timeline-legend i{width:8px;height:8px;border-radius:2px;display:inline-block}

.wo-funnel-row{margin-bottom:10px}
.wo-funnel-label-row{display:flex;justify-content:space-between;font-size:11.5px;color:var(--db-text);margin-bottom:3px}
.wo-funnel-track{height:6px;border-radius:99px;background:var(--db-bg);overflow:hidden}
.wo-funnel-fill{height:100%;border-radius:99px}
.wo-funnel-totals{display:flex;justify-content:space-around;text-align:center;margin:12px 0 18px;padding-top:10px;border-top:1px solid var(--db-border)}
.wo-funnel-total-num{font-size:13px;font-weight:800;color:var(--db-text)}
.wo-funnel-total-label{font-size:9.5px;color:var(--db-text-muted)}

.wo-spend-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.wo-spend-label{font-size:11.5px;color:var(--db-text);text-transform:capitalize}
.wo-spend-track{flex:1;height:5px;border-radius:99px;background:var(--db-bg);margin:0 8px;overflow:hidden}
.wo-spend-fill{height:100%;border-radius:99px;background:var(--db-invert-bg)}
.wo-spend-val{font-size:11px;color:var(--db-text-muted);white-space:nowrap;flex-shrink:0}
.wo-spend-total{display:flex;justify-content:space-between;padding-top:10px;margin-top:6px;border-top:1px solid var(--db-border);font-size:12px;font-weight:700;color:var(--db-text)}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow-x:hidden;overflow-y:auto;height:auto;width:100%}
  .ob-shell{height:auto;overflow:visible;width:100%}
  .ob-shell,.ob-shell *{min-width:0}
  .ob-topbar{height:auto;padding:12px 16px;flex-wrap:wrap;gap:6px}
  .ob-topbar-logo{font-size:18px}
  .ob-topbar-email{display:none}
  .ob-topbar-name{max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .ob-page{display:block;height:auto;overflow:visible;width:100%}
  .ob-sidebar{width:100%;flex-direction:column;padding:0;overflow:hidden;border-bottom:none}
  .ob-steps{display:flex;flex-direction:row;align-items:center;gap:8px;padding:10px 16px;overflow-x:auto;width:100%;flex:none;-webkit-overflow-scrolling:touch}
  .ob-workers-hd{margin-bottom:0;flex-shrink:0;gap:8px}
  .ob-step{flex-shrink:0;align-items:center;gap:8px;background:var(--db-card);border:1.5px solid var(--db-border);border-radius:99px;padding:5px 12px 5px 6px}
  .ob-step.active{border-color:var(--db-invert-bg)}
  .ob-step-rail{padding-bottom:0}
  .ob-step-rail::after{display:none !important}
  .ob-step-num{width:24px;height:24px}
  .ob-step-body{padding:0 !important;background:none !important;border:none !important;margin:0 !important}
  .ob-step-label{font-size:12.5px}
  .ob-step-desc{display:none}
  .ob-links-section{display:none}
  .ob-security{display:none}
  .wo-main{padding:16px}
  .wo-card-area{display:block;margin:0;border-radius:0;border:none;box-shadow:none;background:transparent}
  .wo-plans,.wo-stats,.wo-links,.wo-ii-stats{grid-template-columns:1fr}
  .wo-right{border-left:none;border-top:1px solid var(--db-border);width:100%}
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
$tokenFmt = $tokenTotal >= 1000000
  ? number_format($tokenTotal/1000000,1).'M'
  : number_format($tokenTotal);
$unitLabel = $contract ? ($contract->billing()['unit_label_plural'] ?? 'transactions') : 'transactions';
@endphp

<div class="ob-shell">

{{-- ══ TOP BAR: logo left · theme toggle + menu right — identical to /desk/{slug} ══ --}}
<div class="ob-topbar">
  <div class="ob-topbar-logo">UNIT</div>
  <div class="ob-topbar-right">
    <a href="{{ route('profile.show') }}" class="ob-topbar-name" style="text-decoration:none">{{ auth()->user()->name }}</a>
    <button class="ob-theme-toggle" id="theme-toggle" type="button" title="Toggle dark/light mode" aria-label="Toggle theme"></button>
    <div class="ob-menu-wrap">
      <button class="ob-hamburger" id="menu-toggle" type="button" aria-label="Menu">
        <svg viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="ob-menu-dropdown" id="menu-dropdown">
        <div class="ob-menu-user">
          <div class="ob-topbar-name">{{ auth()->user()->name }}</div>
          <div class="ob-topbar-email">{{ auth()->user()->email }}</div>
        </div>
        <div class="ob-menu-token"><span class="ob-token-badge">{{ $tokenFmt }} tokens</span></div>
        <a href="{{ route('settings.api-keys') }}" class="ob-menu-item">Settings</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="ob-menu-item">Logout</button></form>
      </div>
    </div>
  </div>
</div>

<div class="ob-page">

  {{-- ══ SIDEBAR — identical to /desk/{slug} ══ --}}
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
          <div class="ob-step-num" style="{{ !$isActive ? 'background:#E8E7E4;border:none;padding:0' : 'padding:0' }}">
            @if($wc->image)
              <img src="{{ $wc->image }}" style="width:100%;height:100%;object-fit:cover;display:block{{ !$wc->active ? ';filter:grayscale(1)' : '' }}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
              <span style="display:none;font-size:11px;font-weight:800;color:{{ $isActive?'#fff':'#6B7280' }};width:100%;height:100%;align-items:center;justify-content:center">{{ substr($wc->name,0,1) }}</span>
            @else
              <span style="font-size:11px;font-weight:800;color:{{ $isActive?'#fff':'#6B7280' }}">{{ substr($wc->name,0,1) }}</span>
            @endif
          </div>
        </div>
        <div class="ob-step-body">
          <div class="ob-step-label">{{ $wc->name }}</div>
          <div class="ob-step-desc">
            @if($wc->active)
              <span style="width:5px;height:5px;border-radius:50%;background:{{ $wDot }};flex-shrink:0;display:inline-block;animation:{{ $wc->status==='active'?'pdot 1.4s ease infinite':'none' }}"></span>
              {{ $wc->role }}
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
      @foreach([
        ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('memory')],
        ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('workers.templates',['slug'=>$dep->worker_slug])],
        ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('workers.rules',$dep->worker_slug)],
        ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('workers.fast-track.page',$dep->worker_slug)],
        ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('workers.connect',$dep->worker_slug)],
        ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('billing')],
        ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('transactions')],
      ] as [$lbl,$ico,$href])
      <a href="{{ $href }}" class="ob-link">
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
      <p>You're in control of what<br>{{ $dep->name }} can see and access.</p>
    </div>

  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="wo-card-area">
  <main class="wo-main">
    <div class="wo-wrap">

    @if($selectedTx)
      {{-- ── Per-transaction data (no canvas — plain labeled columns) ── --}}
      <a href="{{ route('workers.overview', $dep->worker_slug) }}" class="wo-tx-back">← Back to Overview</a>

      <div class="wo-card">
        <div class="wo-card-title">{{ $selectedTx->tx_id }}</div>
        <div class="wo-tx-header-row"><div class="wo-tx-header-label">Category</div><div class="wo-tx-header-val">{{ $selectedTx->category ?? '—' }}</div></div>
        <div class="wo-tx-header-row"><div class="wo-tx-header-label">Status</div><div class="wo-tx-header-val">{{ ucfirst(str_replace('_',' ',$selectedTx->status)) }}</div></div>
        <div class="wo-tx-header-row" style="border-bottom:none"><div class="wo-tx-header-label">Received</div><div class="wo-tx-header-val">{{ \Carbon\Carbon::parse($selectedTx->created_at)->format('M j, Y g:i A') }}</div></div>

        @forelse($txStages as $stage)
        <div class="wo-tx-stage-title"><span class="wo-tx-stage-dot" style="background:{{ $stage['color'] }}"></span>{{ $stage['label'] }} <span style="font-weight:400;color:var(--db-text-muted);font-size:11px">· {{ $stage['timestamp'] }}</span></div>
        @foreach($stage['payload'] as $key => $val)
        @if(is_array($val)) @php $val = json_encode($val, JSON_PRETTY_PRINT); @endphp @endif
        <div class="wo-tx-data-row">
          <div class="wo-tx-data-key">{{ str_replace('_',' ',$key) }}</div>
          <div class="wo-tx-data-val">{{ $val }}</div>
        </div>
        @endforeach
        @empty
        <p style="font-size:12px;color:var(--db-text-muted);margin-top:12px">No stage data recorded for this transaction yet.</p>
        @endforelse
      </div>
    @else
      <div class="wo-identity" style="margin-top:4px">
        <div>
          <div class="wo-name">{{ $dep->name }}</div>
          <div class="wo-role">{{ $registryRow->description ?? ucfirst($dep->worker_slug).' Specialist' }}</div>
        </div>
        <span class="wo-status {{ $dep->status }}"><span class="wo-status-dot"></span> {{ $dep->status === 'active' ? 'On Shift' : 'Paused' }}</span>
      </div>

      @if($isTrialExhausted)
      <div class="wo-card wo-paywall">
        <div class="wo-paywall-head">
          <div>
            <div class="wo-paywall-title">Trial {{ $trialReason === 'expired' ? 'Expired' : 'Complete' }}</div>
            <div class="wo-paywall-body">
              @if($trialReason === 'expired')
                Your 14-day trial period has ended. Subscribe to keep {{ $dep->name }} running.
              @else
                You've used all {{ $billing?->trial_transactions_limit ?? 25 }} free {{ $unitLabel }}. Choose a plan to continue.
              @endif
            </div>
          </div>
          <div>
            <div class="wo-paywall-count">{{ $billing?->trial_transactions_used ?? 0 }}/{{ $billing?->trial_transactions_limit ?? 25 }}</div>
            <div class="wo-paywall-count-label">{{ $unitLabel }} used</div>
          </div>
        </div>
        @if($pricingTiers->isNotEmpty())
        <div class="wo-plans">
          @foreach($pricingTiers as $tier)
          @php $isRecommended = $tier->plan_slug === 'pro'; @endphp
          <div class="wo-plan {{ $isRecommended ? 'recommended' : '' }}">
            @if($isRecommended)<span class="wo-plan-badge">Most popular</span>@endif
            <div class="wo-plan-name">{{ $tier->display_name }}</div>
            <div class="wo-plan-tagline">{{ $tier->tagline }}</div>
            <div class="wo-plan-price">${{ number_format($tier->monthly_flat_rate, 0) }}<span>/month</span></div>
            <div class="wo-plan-limit">{{ $tier->transaction_limit ? number_format($tier->transaction_limit).' '.$unitLabel.'/month' : 'Unlimited '.$unitLabel }}</div>
            <form method="POST" action="{{ route('billing.checkout', $dep->id) }}">
              @csrf
              <input type="hidden" name="plan" value="{{ $tier->plan_slug }}">
              <button type="submit" class="wo-plan-btn">Subscribe — ${{ number_format($tier->monthly_flat_rate, 0) }}/mo</button>
            </form>
          </div>
          @endforeach
        </div>
        @else
        <p style="font-size:12px;color:var(--db-text-muted);text-align:center">Contact us to set up your subscription — {{ config('services.unit.support_email') }}</p>
        @endif
      </div>
      @endif

      @foreach($otherViolations as $violation)
      <div class="wo-banner warn">
        <div style="flex:1">
          <div class="wo-banner-title">{{ $violation['title'] ?? 'Attention needed' }}</div>
          <div class="wo-banner-body">{{ $violation['description'] ?? '' }}</div>
        </div>
        @if(!empty($violation['cta_url']))
          <a href="{{ $violation['cta_url'] }}" class="wo-banner-action">{{ $violation['cta_label'] ?? 'Resolve' }} →</a>
        @endif
      </div>
      @endforeach

      @unless($productionReadiness['ready'])
      <div class="wo-banner notice">
        <div style="flex:1">
          <div class="wo-banner-title">{{ $productionReadiness['title'] }}</div>
          <div class="wo-banner-body">{{ $productionReadiness['body'] }}</div>
        </div>
        <a href="{{ route('workers.connect', $dep->worker_slug) }}" class="wo-banner-action">{{ $productionReadiness['connect_label'] }} →</a>
      </div>
      @endunless

      <div class="wo-card">
        <div class="wo-card-title">Activity</div>
        <div class="wo-stats">
          <div class="wo-stat"><div class="wo-stat-num">{{ $txCount }}</div><div class="wo-stat-label">Total processed</div></div>
          <div class="wo-stat"><div class="wo-stat-num">{{ $pendingReview }}</div><div class="wo-stat-label">Awaiting review</div></div>
          <div class="wo-stat"><div class="wo-stat-num" style="color:{{ $stuckCount > 0 ? '#ef4444' : 'var(--db-text)' }}">{{ $stuckCount }}</div><div class="wo-stat-label">Stuck / delayed</div></div>
        </div>
      </div>

      <div class="wo-card">
        <div class="wo-card-title">Connected accounts</div>
        @if($connectedInboxes->isNotEmpty())
          @foreach($connectedInboxes as $inbox)
          <div style="display:flex;align-items:center;gap:10px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--db-border)' : '' }}">
            <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;flex-shrink:0"></span>
            <span style="font-size:12.5px;color:var(--db-text)">{{ $inbox->gmail_address ?? $inbox->email ?? 'Connected account' }}</span>
          </div>
          @endforeach
        @else
          <p style="font-size:12px;color:var(--db-text-muted)">No accounts connected yet.</p>
        @endif
      </div>

      <div class="wo-card">
        <div class="wo-card-title">Configure</div>
        <div class="wo-links">
          <a href="{{ route('workers.configure', $dep->worker_slug) }}" class="wo-link">
            <div class="wo-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg></div>
            <span class="wo-link-label">Configure</span>
          </a>
          <a href="{{ route('workers.memory', $dep->worker_slug) }}" class="wo-link">
            <div class="wo-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg></div>
            <span class="wo-link-label">Memory</span>
          </a>
          <a href="{{ route('workers.rules', $dep->worker_slug) }}" class="wo-link">
            <div class="wo-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></div>
            <span class="wo-link-label">Rules</span>
          </a>
          <a href="{{ route('workers.templates', $dep->worker_slug) }}" class="wo-link">
            <div class="wo-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
            <span class="wo-link-label">Templates</span>
          </a>
        </div>
      </div>

      <div class="wo-card">
        <div class="wo-card-title">Manage worker</div>
        <div class="wo-manage-row">
          <form method="POST" action="{{ route('workers.status', $dep->id) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="{{ $dep->status === 'active' ? 'paused' : 'active' }}">
            <button type="submit" class="wo-manage-btn">{{ $dep->status === 'active' ? 'Pause worker' : 'Resume worker' }}</button>
          </form>
          <form method="POST" action="{{ route('workers.destroy', $dep->id) }}" onsubmit="return confirm('Remove {{ $dep->name }}? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="wo-manage-btn danger">Remove worker</button>
          </form>
        </div>
      </div>
    @endif

    </div>
  </main>

  {{-- ══ RIGHT PANEL — Inbox Intelligence + TX switcher ══ --}}
  <aside class="wo-right">
    <div class="wo-right-head">
      <div>
        <div class="wo-right-eyebrow">On Shift</div>
        <div class="wo-right-name">{{ strtoupper($dep->worker_slug) }}</div>
        <div class="wo-right-role">{{ $registryRow->description ?? ucfirst($dep->worker_slug).' Specialist' }}</div>
      </div>
      <div class="tx-switcher-wrap">
        <button type="button" class="tx-switcher-btn" id="tx-switcher-btn">
          <span>{{ $selectedTxId ?? 'Select TX' }}</span>
          <svg viewBox="0 0 24 24" width="10" height="10"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="tx-switcher-dropdown" id="tx-switcher-dropdown">
          @forelse($txList as $tx)
          <a href="{{ route('workers.overview', ['slug' => $dep->worker_slug, 'tx' => $tx->tx_id]) }}" class="tx-switcher-item">
            <div class="tx-switcher-item-label">{{ $tx->tx_id }}</div>
            <div class="tx-switcher-item-meta">{{ $tx->category ?: ucfirst(str_replace('_',' ',$tx->status)) }} · {{ \Carbon\Carbon::parse($tx->created_at)->format('M j') }}</div>
          </a>
          @empty
          <p style="font-size:11px;color:var(--db-text-muted);padding:8px">No transactions yet.</p>
          @endforelse
        </div>
      </div>
    </div>

    <div style="font-size:13px;font-weight:700;color:var(--db-text);margin-bottom:16px">
      Inbox Intelligence <span class="wo-ii-badge">7d</span>
    </div>

    <div class="wo-ii-stats">
      <div class="wo-ii-stat"><div class="wo-ii-stat-num">{{ $pubsubHits->sum('hits') }}</div><div class="wo-ii-stat-label">Pub/Sub hits</div></div>
      <div class="wo-ii-stat"><div class="wo-ii-stat-num">{{ $ingestedCount }}</div><div class="wo-ii-stat-label">Ingested</div></div>
      <div class="wo-ii-stat"><div class="wo-ii-stat-num">{{ $observeFunnel->completed ?? 0 }}</div><div class="wo-ii-stat-label">Drafted</div></div>
      <div class="wo-ii-stat"><div class="wo-ii-stat-num">{{ $avgDuration ? round($avgDuration).'s' : '—' }}</div><div class="wo-ii-stat-label">Avg duration</div></div>
    </div>

    <div style="font-size:12px;font-weight:700;color:var(--db-text);margin-bottom:10px">Activity Timeline</div>
    <div class="wo-timeline">
      @foreach($chartDays as $day)
      <div class="wo-timeline-col" title="{{ $day['label'] }}">
        @if($day['hits'] > 0)<div class="wo-timeline-seg" style="height:{{ ($day['hits']/$chartMax)*100 }}%;background:#8b8fd8"></div>@endif
        @if($day['completed'] > 0)<div class="wo-timeline-seg" style="height:{{ ($day['completed']/$chartMax)*100 }}%;background:#22c55e"></div>@endif
      </div>
      @endforeach
    </div>
    <div class="wo-timeline-labels">
      @foreach($chartDays as $day)<span>{{ \Carbon\Carbon::parse($day['day'])->format('j') }}</span>@endforeach
    </div>
    <div class="wo-timeline-legend">
      <span><i style="background:#8b8fd8"></i>Hits</span>
      <span><i style="background:#22c55e"></i>Drafted</span>
    </div>

    <div style="font-size:12px;font-weight:700;color:var(--db-text);margin-bottom:10px">Pipeline Funnel</div>
    @php
      $funnelSteps = [
        ['label' => 'Ingested',      'value' => $observeFunnel->total ?? 0,     'color' => '#142C74'],
        ['label' => 'Passed Filter', 'value' => ($observeFunnel->total ?? 0) - ($observeFunnel->filtered_out ?? 0), 'color' => '#818cf8'],
        ['label' => 'Classified',    'value' => ($observeFunnel->total ?? 0) - ($observeFunnel->filtered_out ?? 0), 'color' => '#38bdf8'],
        ['label' => 'Drafted',       'value' => $observeFunnel->completed ?? 0, 'color' => '#22c55e'],
      ];
      $funnelMax = max(1, $observeFunnel->total ?? 1);
    @endphp
    @foreach($funnelSteps as $step)
    <div class="wo-funnel-row">
      <div class="wo-funnel-label-row"><span>{{ $step['label'] }}</span><span>{{ $step['value'] }}</span></div>
      <div class="wo-funnel-track"><div class="wo-funnel-fill" style="width:{{ ($step['value']/$funnelMax)*100 }}%;background:{{ $step['color'] }}"></div></div>
    </div>
    @endforeach
    <div class="wo-funnel-totals">
      <div><div class="wo-funnel-total-num">{{ $observeFunnel->filtered_out ?? 0 }}</div><div class="wo-funnel-total-label">filtered</div></div>
      <div><div class="wo-funnel-total-num">{{ $observeFunnel->dismissed ?? 0 }}</div><div class="wo-funnel-total-label">dismissed</div></div>
      <div><div class="wo-funnel-total-num" style="color:{{ ($observeFunnel->failed ?? 0) > 0 ? '#ef4444' : 'var(--db-text)' }}">{{ $observeFunnel->failed ?? 0 }}</div><div class="wo-funnel-total-label">failed</div></div>
    </div>

    @if($stageSpend->isNotEmpty())
    <div style="font-size:12px;font-weight:700;color:var(--db-text);margin-bottom:10px">AI Spend by Stage</div>
    @php $spendMax = max(0.0001, $stageSpend->max('cost')); @endphp
    @foreach($stageSpend as $s)
    <div class="wo-spend-row">
      <div class="wo-spend-label" style="flex-shrink:0;width:56px">{{ $s->stage }}</div>
      <div class="wo-spend-track"><div class="wo-spend-fill" style="width:{{ ($s->cost/$spendMax)*100 }}%"></div></div>
      <div class="wo-spend-val">{{ $s->calls }}x ${{ number_format($s->cost, 4) }}</div>
    </div>
    @endforeach
    <div class="wo-spend-total"><span>Total this period</span><span>${{ number_format($stageSpend->sum('cost'), 4) }}</span></div>
    @endif
  </aside>
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

  var txBtn = document.getElementById('tx-switcher-btn');
  var txDrop = document.getElementById('tx-switcher-dropdown');
  if (txBtn && txDrop) {
    txBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      txDrop.classList.toggle('open');
    });
    document.addEventListener('click', function (e) {
      if (!txDrop.contains(e.target) && e.target !== txBtn) {
        txDrop.classList.remove('open');
      }
    });
  }
})();
</script>
</body>
</html>

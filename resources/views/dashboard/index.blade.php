<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Command Center — UNIT</title>
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

/* ── SHELL ── */
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
.mem-wrap{max-width:1100px;margin:0 auto}

.dc-referral-banner{margin-bottom:20px;display:flex;align-items:center;gap:10px;border-radius:12px;padding:12px 16px;background:var(--db-chip);border:1px solid var(--db-border)}
.dc-referral-chip{margin-bottom:20px;display:flex;justify-content:flex-end}
.dc-referral-chip a{display:inline-flex;align-items:center;gap:6px;font-size:12px;padding:6px 12px;border-radius:99px;border:1px solid var(--db-border);color:var(--db-text-muted);text-decoration:none}
.dc-referral-chip a:hover{color:var(--db-text)}
.dc-refer-btn{flex-shrink:0;font-size:11px;font-weight:700;padding:6px 12px;border-radius:8px;background:var(--db-invert-bg);color:var(--db-invert-text);text-decoration:none;white-space:nowrap}

.dc-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:20px;align-items:flex-start}
@media(max-width:900px){.dc-grid{grid-template-columns:1fr}}

.dc-desk-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.dc-date{font-size:11.5px;color:var(--db-text-muted)}
.dc-customize-btn{font-size:11.5px;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit}
.dc-customize-btn:hover{color:var(--db-text)}
.dc-empty-desk{border:1px solid var(--db-border);border-radius:12px;padding:16px 20px;font-size:13px;color:var(--db-text-muted);margin-bottom:20px}
#desk-feed{border-top:1px solid var(--db-border);border-bottom:1px solid var(--db-border);margin-bottom:20px}
#desk-feed strong{color:var(--db-text);font-size:1.05em}
.dc-desk-row{display:flex;align-items:center;justify-content:space-between;padding:12px 0;gap:12px;border-bottom:1px solid var(--db-border)}
.dc-desk-row:last-child{border-bottom:none}
.dc-desk-text{font-size:13px;color:var(--db-text);line-height:1.4}
.dc-desk-action{font-size:12px;font-weight:700;color:var(--db-text);text-decoration:none;white-space:nowrap}
.dc-desk-dismiss{font-size:12px;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit}
.dc-desk-dismiss:hover{color:var(--db-text)}

.dc-drawer{border:1px solid var(--db-border);border-radius:12px;overflow:hidden;margin-bottom:20px}
.dc-drawer-head{padding:14px 18px;border-bottom:1px solid var(--db-border);display:flex;align-items:center;justify-content:space-between}
.dc-drawer-title{font-size:13px;font-weight:700;color:var(--db-text)}
.dc-drawer-close{font-size:11.5px;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit}
.dc-drawer-close:hover{color:var(--db-text)}
.dc-drawer-tier{padding:14px 18px;border-bottom:1px solid var(--db-border)}
.dc-drawer-tier-hd{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--db-text-muted);margin-bottom:10px}
.dc-drawer-item{display:flex;align-items:center;gap:10px;padding:6px 0}
.dc-drawer-item-label{font-size:12.5px;font-weight:600;color:var(--db-text)}
.dc-drawer-item-desc{font-size:11.5px;color:var(--db-text-muted);margin-top:1px}
.dc-toggle{position:relative;width:34px;height:20px;flex-shrink:0}
.dc-toggle input{opacity:0;width:0;height:0;position:absolute}
.dc-toggle-track{position:absolute;inset:0;border-radius:10px;background:var(--db-chip);border:1px solid var(--db-border);transition:.18s}
.dc-toggle-thumb{position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;background:var(--db-text-muted);transition:.18s;pointer-events:none}
.dc-toggle input:checked ~ .dc-toggle-track{background:var(--db-invert-bg);border-color:transparent}
.dc-toggle input:checked ~ .dc-toggle-track .dc-toggle-thumb{transform:translateX(14px);background:var(--db-invert-text)}
.dc-drawer-note{padding:14px 18px;font-size:11.5px;color:var(--db-text-muted)}

.dc-worker-card{border-radius:16px;border:1px solid var(--db-border);margin-bottom:16px;overflow:hidden}
.dc-wc-head{padding:18px 20px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.dc-wc-avatar-img{width:44px;height:44px;border-radius:12px;object-fit:cover;flex-shrink:0}
.dc-wc-avatar-fallback{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;flex-shrink:0;background:var(--db-chip);color:var(--db-text)}
.dc-wc-name{font-weight:700;font-size:13.5px;color:var(--db-text)}
.dc-wc-role{font-size:12px;color:var(--db-text-muted)}
.dc-wc-status{font-size:12px;font-weight:600;margin-top:3px}
.dc-wc-open{font-size:12px;padding:6px 12px;border-radius:8px;background:var(--db-chip);border:1px solid var(--db-border);color:var(--db-text-muted);text-decoration:none;white-space:nowrap;flex-shrink:0}
.dc-wc-open:hover{color:var(--db-text)}
.dc-wc-statement{padding:0 20px 16px;font-size:13.5px;font-style:italic;color:var(--db-text-muted);line-height:1.5}
.dc-wc-controls{padding:12px 20px;border-top:1px solid var(--db-border);font-size:12px}
.dc-wc-controls-label{color:var(--db-text-muted)}
.dc-wc-controls-val{color:var(--db-text)}
.dc-wc-footer{padding:12px 20px;border-top:1px solid var(--db-border);display:flex;align-items:center;justify-content:space-between;gap:12px}
.dc-wc-fast-track{font-size:12px;font-weight:700;color:var(--db-text);text-decoration:none}
.dc-wc-fast-track:hover{opacity:.8}
.dc-wc-last{font-size:11.5px;color:var(--db-text-muted);text-align:right}
.dc-wc-last-tx{color:var(--db-text-muted);text-decoration:none;font-family:monospace}
.dc-wc-last-tx:hover{color:var(--db-text);text-decoration:underline}

.dc-empty-worker{border-radius:16px;border:1px solid var(--db-border);padding:36px 20px;text-align:center}
.dc-empty-worker p:first-child{font-size:13.5px;color:var(--db-text-muted)}
.dc-empty-worker p:nth-child(2){font-size:12px;color:var(--db-text-muted);margin:4px 0 14px}

.dc-clock-card{border-radius:16px;border:1px solid var(--db-border);padding:24px 20px;text-align:center;margin-bottom:16px}
.dc-clock-hd{display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:10px}
.dc-clock-hd-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--db-text-muted)}
.dc-clock-info{width:14px;height:14px;cursor:pointer;color:var(--db-text-muted);flex-shrink:0}
.dc-clock-val{font-weight:900;line-height:1;margin-bottom:6px;font-size:clamp(42px,7vw,68px);letter-spacing:-.03em;color:var(--db-text)}
.dc-clock-sub{font-size:13.5px;color:var(--db-text-muted)}
.dc-clock-meta{font-size:11.5px;color:var(--db-text-muted);margin-top:4px}

.dc-notif-hd{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--db-text-muted);padding:0 2px;margin-bottom:10px}
.dc-notif-list{border-radius:16px;border:1px solid var(--db-border);overflow:hidden}
.dc-notif-clear{padding:36px 20px;text-align:center}
.dc-notif-clear p:first-child{color:#22c55e;font-size:13.5px}
.dc-notif-clear p:last-child{font-size:12px;color:var(--db-text-muted);margin-top:4px}
.dc-notif-row{padding:14px 18px;border-bottom:1px solid var(--db-border);display:flex;align-items:flex-start;gap:10px}
.dc-notif-row:last-child{border-bottom:none}
.dc-notif-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;margin-top:5px}
.dc-notif-msg{font-size:12.5px;line-height:1.4}
.dc-notif-source{font-size:11px;color:var(--db-text-muted);margin-top:2px}
.dc-notif-action{font-size:12px;font-weight:600;text-decoration:none;flex-shrink:0;color:var(--db-text)}
.dc-notif-action:hover{opacity:.8}

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
$tokenFmt = $tokenTotal >= 1000000 ? number_format($tokenTotal/1000000,1).'M' : number_format($tokenTotal);
$sidebarLinks = [
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('app.workers.memory','ava'), false],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('app.workers.templates',['slug'=>'ava']), false],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('app.workers.rules','ava'), false],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('app.workers.fast-track.page','ava'), false],
  ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('app.workers.connect','ava'), false],
  ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('app.billing'), false],
  ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('app.transactions'), false],
];
$notifDotColors = ['error'=>'#ef4444','warning'=>'#f59e0b','info'=>'#9ca3af'];
$notifTextColors = ['error'=>'#f87171','warning'=>'#fbbf24','info'=>'var(--db-text)'];
@endphp

<div class="ob-shell">

{{-- ══ TOP BAR ══ --}}
<div class="ob-topbar">
  <a href="{{ route('app.dashboard') }}" class="ob-topbar-logo" style="text-decoration:none">UNIT</a>
  <div class="ob-topbar-right">
    <a href="{{ route('app.profile.show') }}" class="ob-topbar-name" style="text-decoration:none">{{ auth()->user()->name }}</a>
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
          <a href="{{ route('app.dashboard') }}" class="ob-menu-item">
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
        <a href="{{ route('app.settings.api-keys') }}" class="ob-menu-item">Settings</a>
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
        <a href="{{ route('app.profile.show') }}" style="color:inherit;text-decoration:none">{{ strtoupper($firstName) }}'S WORKERS</a>
      </div>
      @foreach($workerCatalog as $wc)
      @php
        $wDot  = $wc->status==='active' ? '#22c55e' : '#f59e0b';
        $wHref = !$wc->active ? route('public.workers.index') : ($wc->slug==='ava' ? route('app.desk.ava') : route('app.workers.overview',$wc->slug));
      @endphp
      <a href="{{ $wHref }}" class="ob-step {{ $wc->active ? 'done' : 'pending' }}" style="text-decoration:none{{ !$wc->active ? ';opacity:.5' : '' }}">
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
      <a href="{{ route('public.workers.index') }}" class="ob-step pending" style="text-decoration:none;margin-top:4px">
        <div class="ob-step-rail"><div class="ob-step-num" style="background:var(--db-chip);border:1.5px dashed var(--db-border);color:var(--db-text-muted);font-size:16px;font-weight:400">+</div></div>
        <div class="ob-step-body"><div class="ob-step-label">Hire a worker</div></div>
      </a>
    </div>

    <div class="ob-links-section">
      <div class="ob-links-hd">LINKS</div>
      @foreach($sidebarLinks as [$lbl,$ico,$href,$isActive])
      <a href="{{ $href }}" class="ob-link {{ $isActive ? 'active' : '' }}">
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
      <p>Everything your workers do, at a glance.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      {{-- Referral chip / banner --}}
      @if($referralEligible)
      <div class="dc-referral-banner">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--db-text-muted)" stroke-width="1.8" style="flex-shrink:0">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p style="flex:1;font-size:13px;color:var(--db-text-muted)">
          Earn <strong style="color:var(--db-text)">$25 credit</strong> for every colleague you bring to UNIT.
        </p>
        <a href="{{ route('referral.index') }}" class="dc-refer-btn">Refer & Earn</a>
      </div>
      @else
      <div class="dc-referral-chip">
        <a href="{{ route('referral.index') }}">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Refer & Earn
        </a>
      </div>
      @endif

      <div class="dc-grid">

        {{-- Left: overview list + worker cards --}}
        <div>

          <div class="dc-desk-head">
            <span class="dc-date">{{ now()->format('l, F j · g:i A') }}</span>
            <button onclick="toggleDeskDrawer()" class="dc-customize-btn">Customize Desk ↗</button>
          </div>

          @if($deskCards->isEmpty())
          <div class="dc-empty-desk">Everything's quiet — nothing needs your attention right now.</div>
          @else
          <div id="desk-feed">
            @foreach($deskCards as $card)
            @php
              $dotColors = ['accent'=>'var(--db-invert-bg)','green'=>'#22c55e','amber'=>'#f59e0b','red'=>'#ef4444','grey'=>'var(--db-text-muted)'];
              $dot = $dotColors[$card['dot'] ?? 'grey'] ?? 'var(--db-text-muted)';
            @endphp
            <div class="dc-desk-row desk-card-row" data-key="{{ $card['key'] }}" @if($card['dismissible'] ?? false) data-dismissible="1" @endif>
              <div style="display:flex;align-items:center;gap:10px;min-width:0">
                <span style="width:6px;height:6px;border-radius:50%;background:{{ $dot }};flex-shrink:0;display:inline-block"></span>
                <span class="dc-desk-text">{!! $card['text'] !!}</span>
              </div>
              <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                @if($card['action'] ?? null)
                <a href="{{ $card['action']['url'] }}" @if($card['action']['external'] ?? false) target="_blank" rel="noopener" @endif class="dc-desk-action">
                  {{ $card['action']['label'] }} →
                </a>
                @endif
                @if($card['dismissible'] ?? false)
                <button onclick="deskDismiss('{{ $card['dismiss_key'] ?? $card['key'] }}', this)" class="dc-desk-dismiss" title="Dismiss">✕</button>
                @endif
              </div>
            </div>
            @endforeach
          </div>
          @endif

          {{-- Customize Desk drawer --}}
          <div id="desk-drawer" class="dc-drawer" style="display:none">
            <div class="dc-drawer-head">
              <span class="dc-drawer-title">Your Desk</span>
              <button onclick="toggleDeskDrawer()" class="dc-drawer-close">✕ Close</button>
            </div>
            @php
              $tierLabels = ['pipeline' => 'Pipeline', 'memory' => 'Memory', 'growth' => 'Growth', 'platform' => 'Platform'];
              $grouped = collect($deskAllCards)->groupBy('tier');
            @endphp
            @foreach(['pipeline','memory','growth','platform'] as $tier)
            @if($grouped->has($tier))
            <div class="dc-drawer-tier">
              <div class="dc-drawer-tier-hd">{{ $tierLabels[$tier] }}</div>
              @foreach($grouped[$tier] as $item)
              <div class="dc-drawer-item">
                <label class="dc-toggle" title="{{ $item['visible'] ? 'Showing' : 'Hidden' }}">
                  <input type="checkbox" class="desk-toggle" data-key="{{ $item['key'] }}" @if($item['visible']) checked @endif onchange="deskSaveToggle(this)">
                  <div class="dc-toggle-track"><div class="dc-toggle-thumb"></div></div>
                </label>
                <div style="flex:1;min-width:0">
                  <div class="dc-drawer-item-label">{{ $item['label'] }}</div>
                  <div class="dc-drawer-item-desc">{{ $item['description'] }}</div>
                </div>
              </div>
              @endforeach
            </div>
            @endif
            @endforeach
            <div class="dc-drawer-note">Cards only show when there's something to display. Toggle off to permanently hide a card type from your desk.</div>
          </div>

          <script>
          function toggleDeskDrawer() {
              var d = document.getElementById('desk-drawer');
              d.style.display = d.style.display === 'none' ? 'block' : 'none';
          }
          function deskSaveToggle(checkbox) {
              var key = checkbox.dataset.key, visible = checkbox.checked;
              var row = document.querySelector('.desk-card-row[data-key="' + key + '"]');
              if (row) row.style.display = visible ? '' : 'none';
              fetch('{{ route('app.dashboard.desk.save') }}', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                  body: JSON.stringify({ cards: [{ key: key, visible: visible, position: 50 }] }),
              });
          }
          function deskDismiss(key, btn) {
              var row = btn.closest('.desk-card-row');
              if (row) row.remove();
              fetch('{{ route('app.dashboard.desk.dismiss') }}', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                  body: JSON.stringify({ key: key }),
              });
          }
          </script>

          @forelse($workerCards as $card)
          @php
              $dep         = $card['dep'];
              $registryRow = $card['registryRow'];
              $inboxes     = $card['inboxes'];
              $employee    = $card['employee'];
              $workerName  = $employee['name']  ?? strtoupper($dep->worker_slug);
              $workerRole  = $employee['title'] ?? '';
              $statement   = $employee['statement'] ?? $employee['mission'] ?? '';
              $connectsTo  = $employee['connects_to'] ?? [];
              $profileImg  = $registryRow?->profile_image ? asset('storage/' . $registryRow->profile_image) : null;
              $isActive    = $dep->status === 'active';
              $controls    = array_merge(array_map('strtolower', $connectsTo), ['rules', 'prompts']);
              $deskHref    = $dep->worker_slug === 'ava' ? route('app.desk.ava') : route('app.workers.overview', $dep->worker_slug);
          @endphp
          <div class="dc-worker-card">
              <div class="dc-wc-head">
                  <div style="display:flex;align-items:center;gap:12px;min-width:0">
                      @if($profileImg)
                      <img src="{{ $profileImg }}" alt="{{ $workerName }}" class="dc-wc-avatar-img">
                      @else
                      <div class="dc-wc-avatar-fallback">{{ strtoupper(substr($workerName, 0, 1)) }}</div>
                      @endif
                      <div style="min-width:0">
                          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                              <span class="dc-wc-name">{{ $workerName }}</span>
                              <span class="dc-wc-role">{{ $workerRole }}</span>
                          </div>
                          <p class="dc-wc-status" style="color:{{ $isActive ? '#22c55e' : '#f59e0b' }}">● {{ $isActive ? 'On duty' : ucfirst($dep->status) }}</p>
                      </div>
                  </div>
                  <div style="display:flex;gap:6px;flex-shrink:0">
                      <a href="{{ route('app.workers.billing', $dep->worker_slug) }}" class="dc-wc-open">Billing</a>
                      <a href="{{ $deskHref }}" class="dc-wc-open">Open →</a>
                  </div>
              </div>

              @if($statement)
              <p class="dc-wc-statement">"{{ $statement }}"</p>
              @endif

              <div class="dc-wc-controls">
                  <span class="dc-wc-controls-label">You control:</span>
                  <span class="dc-wc-controls-val">{{ implode(' · ', array_map('strtoupper', $controls)) }}</span>
              </div>

              <div class="dc-wc-footer">
                  <a href="{{ route('app.workers.fast-track.page', $dep->worker_slug) }}" class="dc-wc-fast-track">Fast Track →</a>
                  <span class="dc-wc-last">
                      @if($card['lastTx'])
                          Last shift: {{ \Carbon\Carbon::parse($card['lastTx']->created_at)->diffForHumans(null, true, true, 1) }} ago
                          · <a href="{{ route('app.transactions.show', $card['lastTx']->tx_id) }}" class="dc-wc-last-tx">{{ $card['lastTx']->tx_id }}</a>
                      @else
                          Never run
                      @endif
                  </span>
              </div>
          </div>
          @empty
          <div class="dc-empty-worker">
              <p>No workers deployed yet.</p>
              <p>Deploy a worker to get started.</p>
              <a href="{{ route('app.workers.index') }}" class="dc-refer-btn">Deploy a Worker →</a>
          </div>
          @endforelse

        </div>

        {{-- Right: value clock + notifications --}}
        <div>

          {{-- Value Clock --}}
          <div class="dc-clock-card">
              <div class="dc-clock-hd">
                  <span class="dc-clock-hd-label">This week's value</span>
                  <svg id="clock-info-icon" class="dc-clock-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
              </div>
              <div id="clock-tooltip" style="display:none;position:fixed;z-index:9999;width:220px;padding:12px;border-radius:12px;text-align:left;pointer-events:none;background:var(--db-card);border:1px solid var(--db-border);box-shadow:0 8px 24px rgba(0,0,0,.18)">
                  <p style="font-size:12px;font-weight:700;color:var(--db-text);margin-bottom:4px">How this is calculated</p>
                  <p style="font-size:12px;line-height:1.5;color:var(--db-text-muted)">Each email processed by your workers saves an estimated <strong style="color:var(--db-text)">15 minutes</strong> of manual work.<br>Total hours = emails × 0.25h, aggregated across all your workers.</p>
              </div>
              <script>
              (function(){
                  const icon = document.getElementById('clock-info-icon');
                  const tip  = document.getElementById('clock-tooltip');
                  if (!icon || !tip) return;
                  icon.addEventListener('mouseenter', function(){
                      const r = icon.getBoundingClientRect();
                      tip.style.display = 'block';
                      tip.style.left = Math.max(8, r.right - 220) + 'px';
                      tip.style.top  = (r.top - tip.offsetHeight - 8) + 'px';
                  });
                  icon.addEventListener('mouseleave', function(){ tip.style.display = 'none'; });
              })();
              </script>
              <p class="dc-clock-val">{{ $clockValue > 0 ? number_format($clockValue, 1) : '—' }}</p>
              <p class="dc-clock-sub">hours returned to your team</p>
              <p class="dc-clock-meta">{{ number_format($ovProcessed) }} emails · {{ $workerCards->count() }} {{ $workerCards->count() === 1 ? 'worker' : 'workers' }}</p>
          </div>

          {{-- Notifications --}}
          <div class="dc-notif-hd">Notifications</div>
          <div class="dc-notif-list">
              @if($notifications->isEmpty())
              <div class="dc-notif-clear">
                  <p>✓ All clear</p>
                  <p>No issues across your workers.</p>
              </div>
              @else
              @foreach($notifications as $note)
              @php $dot = $notifDotColors[$note['level']] ?? $notifDotColors['info']; $txt = $notifTextColors[$note['level']] ?? $notifTextColors['info']; @endphp
              <div class="dc-notif-row">
                  <span class="dc-notif-dot" style="background:{{ $dot }}"></span>
                  <div style="flex:1;min-width:0">
                      <p class="dc-notif-msg" style="color:{{ $txt }}">{{ $note['message'] }}</p>
                      @if($note['source'] !== 'platform')<p class="dc-notif-source">{{ $note['source'] }}</p>@endif
                  </div>
                  <a href="{{ $note['actionUrl'] }}" class="dc-notif-action">{{ $note['actionLabel'] }} →</a>
              </div>
              @endforeach
              @endif
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

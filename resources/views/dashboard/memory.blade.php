<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Memory — UNIT</title>
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

/* ── SHELL (identical to /desk/{slug} and /workers/{slug}/overview) ── */
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
.ob-menu-token{padding:0 10px 8px}
.ob-menu-item{display:block;width:100%;text-align:left;padding:8px 10px;border-radius:8px;font-size:13.5px;font-weight:600;color:var(--db-text);text-decoration:none;background:none;border:none;cursor:pointer;font-family:inherit}
.ob-menu-item:hover{background:var(--db-chip)}
.ob-menu-mobile-links{display:none}

.ob-page{display:grid;grid-template-columns:260px 1fr;flex:1;overflow:hidden}
.mem-card-area{display:grid;grid-template-columns:1fr 320px;margin:12px 12px 12px 0;background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.mem-right{background:var(--db-card);border-left:1px solid var(--db-border);overflow-y:auto}
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
.ob-step-label{font-size:13px;font-weight:700;color:var(--db-text);line-height:1.2}
.ob-step.pending .ob-step-label{color:var(--db-text-muted)}
.ob-step-desc{font-size:12px;color:var(--db-text-muted);margin-top:2px;line-height:1.4;display:flex;align-items:center;gap:5px}
@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}

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

/* ── CONTENT ── */
.mem-main{overflow-y:auto;padding:28px 32px 60px}
.mem-wrap{max-width:920px;margin:0 auto}

.mem-status{border-radius:12px;padding:10px 14px;font-size:13.5px;margin-bottom:16px}
.mem-status.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#22c55e}
.mem-status.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444}

.mem-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.mem-sub{font-size:13.5px;color:var(--db-text-muted);margin-top:3px;max-width:520px}
.mem-header-row{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:20px}
.mem-code-label{font-size:11.5px;color:var(--db-text-muted);text-align:right}
.mem-code{display:flex;align-items:center;gap:8px}
.mem-code-val{font-family:monospace;font-size:13px;font-weight:700;letter-spacing:.05em;padding:6px 12px;border-radius:8px;background:transparent;border:1px solid var(--db-border);color:var(--db-text)}
.mem-code-copy{font-size:12.5px;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit}
.mem-code-copy:hover{color:var(--db-text)}

.wo-card{background:transparent;border:1px solid var(--db-border);border-radius:16px;padding:20px;margin-bottom:16px}
.wo-card-title{font-size:13.5px;font-weight:700;color:var(--db-text);margin-bottom:14px}

.mem-persona-prompt{display:flex;flex-wrap:wrap;align-items:center;gap:12px}
.mem-persona-prompt-text{flex:1;min-width:220px}
.mem-persona-prompt-title{font-size:13px;font-weight:700;color:var(--db-text)}
.mem-persona-prompt-sub{font-size:12.5px;color:var(--db-text-muted);margin-top:2px}
.mem-persona-form{display:flex;gap:8px;flex-shrink:0}

.mem-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
.mem-stat{background:transparent;border:1px solid var(--db-border);border-radius:12px;padding:14px}
.mem-stat-num{font-size:1.4rem;font-weight:900;color:var(--db-text)}
.mem-stat-label{font-size:11.5px;color:var(--db-text-muted);margin-top:2px}
.mem-stat-sub{font-size:11px;color:#f59e0b;margin-top:2px}

.mem-tabs{display:flex;gap:4px;border-bottom:1px solid var(--db-border);margin-bottom:20px;overflow-x:auto}
.mem-tab{padding:9px 4px;margin-right:18px;font-size:13.5px;font-weight:600;color:var(--db-text-muted);background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;font-family:inherit;white-space:nowrap;outline:none}
.mem-tab:focus,.mem-tab:focus-visible{outline:none;box-shadow:none}
.mem-tab.active{color:var(--db-text);border-color:var(--db-invert-bg)}
.mem-tab-badge{font-size:11px;background:var(--db-chip);color:var(--db-text-muted);border-radius:99px;padding:1px 6px;margin-left:4px}

.mem-import-row{display:flex;flex-wrap:wrap;align-items:center;gap:12px}
.mem-import-text{flex:1;min-width:200px}
.mem-import-title{font-size:13px;font-weight:600;color:var(--db-text)}
.mem-import-sub{font-size:12.5px;color:var(--db-text-muted);margin-top:2px}
.mem-import-form{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.mem-select,.mem-input,.mem-textarea{width:100%;border-radius:9px;padding:9px 12px;font-size:13.5px;background:transparent;border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.mem-select:focus,.mem-input:focus,.mem-textarea:focus{outline:none;border-color:var(--db-invert-bg)}
.mem-file-label{display:inline-block;padding:9px 14px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text-muted);font-size:13.5px;cursor:pointer}
.mem-btn{padding:9px 16px;border-radius:9px;border:none;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text)}
.mem-btn:hover{opacity:.9}
.mem-btn-secondary{padding:8px 14px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text-muted);font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block}
.mem-btn-secondary:hover{background:var(--db-chip);color:var(--db-text)}
.mem-tpl-link{font-size:12.5px;color:var(--db-text-muted);margin-top:8px;display:block}
.mem-tpl-link a{color:var(--db-text)}

.mem-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:16px;align-items:flex-start}
@media(max-width:900px){.mem-grid{grid-template-columns:1fr}}
.mem-list{background:transparent;border:1px solid var(--db-border);border-radius:16px;overflow:hidden}
.mem-row{padding:14px 18px;border-bottom:1px solid var(--db-border);display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.mem-row:last-child{border-bottom:none}
.mem-row-name{font-size:13px;font-weight:600;color:var(--db-text)}
.mem-row-sub{font-size:12.5px;color:var(--db-text-muted);margin-top:2px}
.mem-row-sub2{font-size:12px;color:var(--db-text-muted);margin-top:1px}
.mem-row-empty{padding:36px 18px;text-align:center;font-size:13.5px;color:var(--db-text-muted)}
.mem-row-action{font-size:12.5px;font-weight:600;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit;text-decoration:none}
.mem-row-action:hover{color:var(--db-text)}
.mem-badge{font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:99px;margin-left:6px}

.mem-form-card{background:transparent;border:1px solid var(--db-border);border-radius:16px;overflow:hidden}
.mem-form-head{padding:14px 18px;border-bottom:1px solid var(--db-border);font-size:13px;font-weight:700;color:var(--db-text)}
.mem-form-body{padding:16px 18px;display:flex;flex-direction:column;gap:12px}
.mem-field-label{font-size:12px;font-weight:600;color:var(--db-text-muted);margin-bottom:5px;display:block}
.mem-field-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.mem-toggle-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border:1px solid var(--db-border);border-radius:9px;background:transparent}
.mem-toggle-title{font-size:13px;font-weight:600;color:var(--db-text)}
.mem-toggle-sub{font-size:11.5px;color:var(--db-text-muted);margin-top:1px}
.mem-toggle{position:relative;width:36px;height:20px;flex-shrink:0}
.mem-toggle input{position:absolute;opacity:0;width:0;height:0}
.mem-toggle-track{position:absolute;inset:0;border-radius:99px;background:var(--db-chip);transition:.15s}
.mem-toggle-thumb{position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:var(--db-invert-bg);transition:.15s}
.mem-toggle input:checked ~ .mem-toggle-track{background:var(--db-invert-bg)}
.mem-toggle input:checked ~ .mem-toggle-track .mem-toggle-thumb{transform:translateX(16px);background:var(--db-invert-text)}

.mem-edit-panel{background:transparent;border-top:1px solid var(--db-border);padding:14px 18px}

.mem-empty-card{background:transparent;border:1px solid var(--db-border);border-radius:16px;padding:40px 20px;text-align:center}
.mem-empty-title{font-size:13px;font-weight:700;color:var(--db-text);margin-bottom:4px}
.mem-empty-sub{font-size:13px;color:var(--db-text-muted)}

.mem-group-dep{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.mem-group-dep-name{font-size:11.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--db-text-muted)}
.mem-group-dep-slug{font-size:11px;font-family:monospace;color:var(--db-text-muted);background:var(--db-chip);padding:1px 6px;border-radius:5px;margin-left:6px}
.mem-group-card{background:transparent;border:1px solid var(--db-border);border-radius:14px;margin-bottom:10px;overflow:hidden}
.mem-group-head{padding:12px 16px;display:flex;flex-wrap:wrap;align-items:center;gap:8px}
.mem-group-items{padding:0 16px 12px;display:flex;flex-wrap:wrap;gap:6px}
.mem-group-chip{font-size:12px;padding:4px 9px;border-radius:8px;background:transparent;border:1px solid var(--db-border);color:var(--db-text-muted);display:flex;align-items:center;gap:5px}

.mem-shared-card{background:transparent;border:1px solid var(--db-border);border-radius:14px;margin-bottom:12px;overflow:hidden}
.mem-shared-head{padding:16px 18px;border-bottom:1px solid var(--db-border);display:flex;flex-wrap:wrap;align-items:flex-start;gap:14px}
.mem-shared-stats{display:flex;gap:16px}
.mem-shared-stat{text-align:center}
.mem-shared-stat-num{font-size:15px;font-weight:800;color:var(--db-text)}
.mem-shared-stat-label{font-size:11px;color:var(--db-text-muted)}
.mem-shared-meta{padding:8px 18px;display:flex;flex-wrap:wrap;gap:14px;font-size:12px;color:var(--db-text-muted)}

.mem-access-audit-btn{width:100%;text-align:left;padding:10px 18px;font-size:12.5px;color:var(--db-text-muted);background:none;border:none;border-top:1px solid var(--db-border);cursor:pointer;font-family:inherit;display:flex;align-items:center;gap:6px}
.mem-access-audit-row{padding:9px 18px;border-top:1px solid var(--db-border);display:flex;align-items:center;gap:10px;font-size:12.5px}

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
  .mem-main{padding:16px}
  .mem-card-area{display:block;margin:0;border-radius:0;border:none;box-shadow:none;background:var(--db-card)}
  .mem-right{display:none}
  .mem-grid,.mem-stats,.mem-field-row{grid-template-columns:1fr}
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
$urgentAssets = $assets->filter(fn($a) => $a->renewal_date && now()->diffInDays($a->renewal_date, false) <= 30 && now()->diffInDays($a->renewal_date, false) >= 0)->count();
$expiredAssets = $assets->filter(fn($a) => $a->renewal_date && now()->diffInDays($a->renewal_date, false) < 0)->count();
$sidebarLinks = [
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('memory'), true],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('workers.templates',['slug'=>'ava']), false],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('workers.rules','ava'), false],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('workers.fast-track.page','ava'), false],
  ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('workers.connect','ava'), false],
  ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('billing'), false],
  ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('transactions'), false],
];
@endphp

<div class="ob-shell">

{{-- ══ TOP BAR ══ --}}
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
        <div class="ob-menu-mobile-links">
          @foreach($sidebarLinks as [$lbl,,$href,])
          <a href="{{ $href }}" class="ob-menu-item">{{ $lbl }}</a>
          @endforeach
          <div style="border-top:1px solid var(--db-border);margin:6px 0"></div>
        </div>
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
      @endphp
      <a href="{{ $wHref }}" class="ob-step {{ $wc->active ? 'done' : 'pending' }}" style="text-decoration:none{{ !$wc->active ? ';opacity:.5' : '' }}">
        <div class="ob-step-rail">
          <div class="ob-step-num" style="padding:0">
            @if($wc->image)
              <img src="{{ $wc->image }}" style="width:100%;height:100%;object-fit:cover;display:block{{ !$wc->active ? ';filter:grayscale(1)' : '' }}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
              <span style="display:none;font-size:12px;font-weight:800;color:#6B7280;width:100%;height:100%;align-items:center;justify-content:center">{{ substr($wc->name,0,1) }}</span>
            @else
              <span style="font-size:12px;font-weight:800;color:#6B7280">{{ substr($wc->name,0,1) }}</span>
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
      <p>Your memory powers every worker you deploy.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      @if(session('success'))<div class="mem-status success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="mem-status error">{{ session('error') }}</div>@endif

      @if(!$personaKey && $avaDeploymentId && !empty($personaOptions))
      <div class="wo-card mem-persona-prompt">
        <div class="mem-persona-prompt-text">
          <div class="mem-persona-prompt-title">Tell us what AVA is renewing for you</div>
          <div class="mem-persona-prompt-sub">Pick your use case to get the right asset types and terminology below — one click.</div>
        </div>
        <form method="POST" action="{{ route('workers.persona', $avaDeploymentId) }}" class="mem-persona-form">
          @csrf @method('PATCH')
          <select name="persona" required class="mem-select" style="width:auto">
            <option value="">Choose a use case…</option>
            @foreach($personaOptions as $key => $p)
            <option value="{{ $key }}">{{ $p['label'] }}</option>
            @endforeach
          </select>
          <button type="submit" class="mem-btn">Save</button>
        </form>
      </div>
      @endif

      <div class="mem-header-row">
        <div>
          <div class="mem-h1">Memory</div>
          <div class="mem-sub">Your memory is your AI's training data. Every {{ $memoryCopy['client_noun'] }}, contact, and {{ $memoryCopy['asset_noun'] }} you build here powers every worker you deploy.</div>
        </div>
        <div>
          <div class="mem-code-label">Your profile code</div>
          <div class="mem-code">
            <span class="mem-code-val">{{ $myProfileCode ?? '—' }}</span>
            <button type="button" class="mem-code-copy" onclick="navigator.clipboard.writeText('{{ $myProfileCode }}');this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500)">Copy</button>
          </div>
        </div>
      </div>

      <div class="mem-stats">
        <div class="mem-stat"><div class="mem-stat-num">{{ $clients->count() }}</div><div class="mem-stat-label">{{ ucfirst($memoryCopy['client_noun_plural']) }}</div></div>
        <div class="mem-stat"><div class="mem-stat-num">{{ $contacts->count() }}</div><div class="mem-stat-label">Contacts</div></div>
        <div class="mem-stat"><div class="mem-stat-num">{{ $assets->count() }}</div><div class="mem-stat-label">Assets</div>
          @if($urgentAssets)<div class="mem-stat-sub">{{ $urgentAssets }} expiring soon</div>@elseif($expiredAssets)<div class="mem-stat-sub">{{ $expiredAssets }} expired</div>@endif
        </div>
        <div class="mem-stat"><div class="mem-stat-num">{{ $myGroups->count() }}</div><div class="mem-stat-label">Groups</div><div class="mem-stat-sub" style="color:var(--db-text-muted)">{{ $myDeployments->count() }} worker{{ $myDeployments->count()!==1?'s':'' }}</div></div>
      </div>

      <div class="mem-tabs">
        <button onclick="showTab('mine')" id="tab-mine" class="mem-tab active">My Memory</button>
        <button onclick="showTab('shared')" id="tab-shared" class="mem-tab">Shared With Me @if($incoming->count())<span class="mem-tab-badge">{{ $incoming->count() }}</span>@endif</button>
        <button onclick="showTab('access')" id="tab-access" class="mem-tab">Access @if($outgoing->count())<span class="mem-tab-badge">{{ $outgoing->count() }}</span>@endif</button>
      </div>

      {{-- ════════ TAB: MY MEMORY ════════ --}}
      <div id="pane-mine" class="hub-pane">

        <div class="wo-card">
          <div class="mem-import-row">
            <div class="mem-import-text">
              <div class="mem-import-title">Bulk Import</div>
              <div class="mem-import-sub">Upload a CSV or Excel file to populate {{ $memoryCopy['client_noun_plural'] }}, contacts, or assets.</div>
            </div>
            <form method="POST" action="{{ route('memory.import.preview') }}" enctype="multipart/form-data" class="mem-import-form" id="import-form">
              @csrf
              <select name="type" id="import-type" onchange="updateTemplateLink(this.value)" class="mem-select" style="width:auto">
                <option value="clients">{{ ucfirst($memoryCopy['client_noun_plural']) }}</option>
                <option value="contacts">Contacts</option>
                <option value="assets">Assets</option>
              </select>
              <label class="mem-file-label">
                <span id="file-label">Choose file…</span>
                <input type="file" name="file" accept=".csv,.xlsx,.xls" required class="hidden" style="display:none" onchange="document.getElementById('file-label').textContent = this.files[0]?.name ?? 'Choose file…'">
              </label>
              <button type="submit" class="mem-btn">Preview Import</button>
            </form>
          </div>
          <div class="mem-tpl-link">Download template: <a id="tpl-link" href="{{ route('memory.import.template', 'clients') }}">clients_import_template.csv</a></div>
        </div>

        <div class="mem-tabs" style="margin-bottom:16px">
          <button onclick="showSubTab('clients')" id="subtab-clients" class="mem-tab active">{{ ucfirst($memoryCopy['client_noun_plural']) }} <span class="mem-tab-badge">{{ $clients->count() }}</span></button>
          <button onclick="showSubTab('contacts')" id="subtab-contacts" class="mem-tab">Contacts <span class="mem-tab-badge">{{ $contacts->count() }}</span></button>
          <button onclick="showSubTab('assets')" id="subtab-assets" class="mem-tab">Assets <span class="mem-tab-badge">{{ $assets->count() }}</span></button>
          <button onclick="showSubTab('groups')" id="subtab-groups" class="mem-tab">Groups <span class="mem-tab-badge">{{ $myGroups->count() }}</span></button>
          <button onclick="showSubTab('rules')" id="subtab-rules" class="mem-tab">AVA Rules <span class="mem-tab-badge">{{ $rules->count() }}</span></button>
        </div>

        {{-- ── CLIENTS ── --}}
        <div id="sub-clients" class="sub-pane">
          <div class="mem-grid">
            <div class="mem-list">
              @forelse($clients as $client)
              <div class="mem-row">
                <div class="min-w-0">
                  <div class="mem-row-name">{{ $client->name }}
                    @if(!empty($client->status) && $client->status !== 'active')
                    <span class="mem-badge" style="background:var(--db-chip);color:var(--db-text-muted)">{{ ucfirst($client->status) }}</span>
                    @endif
                  </div>
                  <div class="mem-row-sub">{{ $client->preferred_style }}{{ $client->industry ? ' · ' . $client->industry : '' }}</div>
                  @if(!empty($client->address))<div class="mem-row-sub2">{{ $client->address }}</div>@endif
                </div>
                <div style="display:flex;gap:10px;flex-shrink:0">
                  <button type="button" class="mem-row-action" onclick="toggleEdit('client-{{ $client->id }}')">Edit</button>
                  <form method="POST" action="{{ route('memory.clients.destroy', $client->id) }}">
                    @csrf @method('DELETE')
                    <button class="mem-row-action" onclick="return confirm('Remove {{ addslashes($client->name) }}?')">Remove</button>
                  </form>
                </div>
              </div>
              <div id="edit-client-{{ $client->id }}" class="mem-edit-panel" style="display:none">
                <form method="POST" action="{{ route('memory.clients.update', $client->id) }}" style="display:flex;flex-direction:column;gap:10px">
                  @csrf @method('PATCH')
                  <div><label class="mem-field-label">{{ ucfirst($memoryCopy['client_noun']) }} name</label><input type="text" name="name" value="{{ $client->name }}" required class="mem-input"></div>
                  <div class="mem-field-row">
                    <div><label class="mem-field-label">Industry</label><input type="text" name="industry" value="{{ $client->industry }}" class="mem-input"></div>
                    <div><label class="mem-field-label">Status</label>
                      <select name="status" class="mem-select">@foreach(['active','prospect','inactive','churned'] as $s)<option value="{{ $s }}" @if(($client->status ?? 'active') === $s) selected @endif>{{ ucfirst($s) }}</option>@endforeach</select>
                    </div>
                  </div>
                  <div><label class="mem-field-label">Preferred Style</label>
                    <select name="preferred_style" class="mem-select">@foreach(['Professional','Friendly','Formal','Concise'] as $st)<option @if($client->preferred_style === $st) selected @endif>{{ $st }}</option>@endforeach</select>
                  </div>
                  <div><label class="mem-field-label">Address</label><input type="text" name="address" value="{{ $client->address }}" class="mem-input"></div>
                  <div><label class="mem-field-label">Notes</label><textarea name="notes" rows="2" class="mem-textarea">{{ $client->notes }}</textarea></div>
                  <div style="display:flex;gap:8px">
                    <button type="submit" class="mem-btn">Save</button>
                    <button type="button" class="mem-btn-secondary" onclick="toggleEdit('client-{{ $client->id }}')">Cancel</button>
                  </div>
                </form>
              </div>
              @empty
              <div class="mem-row-empty">No {{ $memoryCopy['client_noun_plural'] }} yet.</div>
              @endforelse
            </div>
            <div class="mem-form-card">
              <div class="mem-form-head">Add {{ ucfirst($memoryCopy['client_noun']) }}</div>
              <form method="POST" action="{{ route('memory.clients.store') }}" class="mem-form-body">
                @csrf
                <div><label class="mem-field-label">{{ ucfirst($memoryCopy['client_noun']) }} name</label><input type="text" name="name" required placeholder="e.g. {{ $memoryCopy['example_client'] }}" class="mem-input"></div>
                <div class="mem-field-row">
                  <div><label class="mem-field-label">Industry</label><input type="text" name="industry" class="mem-input"></div>
                  <div><label class="mem-field-label">Status</label>
                    <select name="status" class="mem-select"><option value="active">Active</option><option value="prospect">Prospect</option><option value="inactive">Inactive</option><option value="churned">Churned</option></select>
                  </div>
                </div>
                <div><label class="mem-field-label">Preferred Style</label>
                  <select name="preferred_style" class="mem-select"><option>Professional</option><option>Friendly</option><option>Formal</option><option>Concise</option></select>
                </div>
                <div><label class="mem-field-label">Address</label><input type="text" name="address" placeholder="Street, City, State…" class="mem-input"></div>
                <div><label class="mem-field-label">Notes</label><textarea name="notes" rows="2" class="mem-textarea"></textarea></div>
                <button type="submit" class="mem-btn">Add {{ ucfirst($memoryCopy['client_noun']) }}</button>
              </form>
            </div>
          </div>
        </div>

        {{-- ── CONTACTS ── --}}
        <div id="sub-contacts" class="sub-pane" style="display:none">
          <div class="mem-grid">
            <div class="mem-list">
              @forelse($contacts as $contact)
              @php $cn = $clients->firstWhere('id', $contact->client_id); @endphp
              <div class="mem-row">
                <div class="min-w-0">
                  <div class="mem-row-name">{{ $contact->name }}
                    @if(!empty($contact->is_decision_maker))<span class="mem-badge" style="background:var(--db-chip);color:var(--db-text)">Decision Maker</span>@endif
                  </div>
                  <div class="mem-row-sub">{{ $contact->email }}</div>
                  <div class="mem-row-sub2">{{ implode(' · ', array_filter([$contact->phone, $contact->role, $contact->department])) }}{{ $cn ? ' · '.$cn->name : '' }}</div>
                </div>
                <div style="display:flex;gap:10px;flex-shrink:0">
                  <button type="button" class="mem-row-action" onclick="toggleEdit('contact-{{ $contact->id }}')">Edit</button>
                  <form method="POST" action="{{ route('memory.contacts.destroy', $contact->id) }}">
                    @csrf @method('DELETE')
                    <button class="mem-row-action" onclick="return confirm('Remove {{ addslashes($contact->name) }}?')">Remove</button>
                  </form>
                </div>
              </div>
              <div id="edit-contact-{{ $contact->id }}" class="mem-edit-panel" style="display:none">
                <form method="POST" action="{{ route('memory.contacts.update', $contact->id) }}" style="display:flex;flex-direction:column;gap:10px">
                  @csrf @method('PATCH')
                  <div><label class="mem-field-label">Full Name</label><input type="text" name="name" value="{{ $contact->name }}" required class="mem-input"></div>
                  <div><label class="mem-field-label">Email</label><input type="email" name="email" value="{{ $contact->email }}" required class="mem-input"></div>
                  <div class="mem-field-row">
                    <div><label class="mem-field-label">Phone</label><input type="text" name="phone" value="{{ $contact->phone }}" class="mem-input"></div>
                    <div><label class="mem-field-label">Role</label><input type="text" name="role" value="{{ $contact->role }}" class="mem-input"></div>
                  </div>
                  <div><label class="mem-field-label">Department</label><input type="text" name="department" value="{{ $contact->department }}" class="mem-input"></div>
                  <div><label class="mem-field-label">{{ ucfirst($memoryCopy['client_noun']) }}</label>
                    <select name="client_id" class="mem-select"><option value="">— none —</option>@foreach($clients as $c)<option value="{{ $c->id }}" @if($contact->client_id == $c->id) selected @endif>{{ $c->name }}</option>@endforeach</select>
                  </div>
                  <label class="mem-toggle-row">
                    <div><div class="mem-toggle-title">Decision Maker</div></div>
                    <div class="mem-toggle"><input type="checkbox" name="is_decision_maker" value="1" @if($contact->is_decision_maker) checked @endif><div class="mem-toggle-track"><div class="mem-toggle-thumb"></div></div></div>
                  </label>
                  <div style="display:flex;gap:8px">
                    <button type="submit" class="mem-btn">Save</button>
                    <button type="button" class="mem-btn-secondary" onclick="toggleEdit('contact-{{ $contact->id }}')">Cancel</button>
                  </div>
                </form>
              </div>
              @empty
              <div class="mem-row-empty">No contacts yet.</div>
              @endforelse
            </div>
            <div class="mem-form-card">
              <div class="mem-form-head">Add Contact</div>
              <form method="POST" action="{{ route('memory.contacts.store') }}" class="mem-form-body">
                @csrf
                <div><label class="mem-field-label">Full Name</label><input type="text" name="name" required class="mem-input"></div>
                <div><label class="mem-field-label">Email</label><input type="email" name="email" required class="mem-input"></div>
                <div class="mem-field-row">
                  <div><label class="mem-field-label">Phone</label><input type="text" name="phone" class="mem-input"></div>
                  <div><label class="mem-field-label">Role</label><input type="text" name="role" class="mem-input"></div>
                </div>
                <div><label class="mem-field-label">Department</label><input type="text" name="department" placeholder="e.g. Procurement, IT, Finance" class="mem-input"></div>
                <div><label class="mem-field-label">{{ ucfirst($memoryCopy['client_noun']) }}</label>
                  <select name="client_id" class="mem-select"><option value="">— none —</option>@foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
                </div>
                <label class="mem-toggle-row">
                  <div><div class="mem-toggle-title">Decision Maker</div><div class="mem-toggle-sub">Key decision authority for this {{ $memoryCopy['client_noun'] }}</div></div>
                  <div class="mem-toggle"><input type="checkbox" name="is_decision_maker" value="1"><div class="mem-toggle-track"><div class="mem-toggle-thumb"></div></div></div>
                </label>
                <button type="submit" class="mem-btn">Add Contact</button>
              </form>
            </div>
          </div>
        </div>

        {{-- ── ASSETS ── --}}
        <div id="sub-assets" class="sub-pane" style="display:none">
          <div class="mem-grid">
            <div class="mem-list">
              @forelse($assets as $asset)
              @php
                $days     = $asset->renewal_date ? now()->diffInDays($asset->renewal_date, false) : null;
                $urgColor = $days === null ? 'var(--db-text-muted)' : ($days <= 0 ? '#ef4444' : ($days <= 30 ? '#f59e0b' : 'var(--db-text-muted)'));
                $cn       = $clients->firstWhere('id', $asset->client_id);
              @endphp
              <div class="mem-row">
                <div class="min-w-0">
                  <div class="mem-row-name">{{ $asset->name }}
                    @if(!empty($asset->status) && $asset->status !== 'active')<span class="mem-badge" style="background:var(--db-chip);color:var(--db-text-muted)">{{ ucfirst($asset->status) }}</span>@endif
                  </div>
                  <div class="mem-row-sub">{{ $asset->type }}{{ $asset->vendor ? ' · '.$asset->vendor : '' }}{{ $cn ? ' · '.$cn->name : '' }}</div>
                  @if($asset->renewal_date)<div class="mem-row-sub2" style="color:{{ $urgColor }}">{{ $asset->renewal_date }} — {{ $days <= 0 ? 'expired' : $days.' days' }}</div>@endif
                </div>
                <div style="display:flex;gap:10px;flex-shrink:0">
                  <button type="button" class="mem-row-action" onclick="toggleAssetEdit({{ $asset->id }})">Edit</button>
                  <form method="POST" action="{{ route('memory.assets.destroy', $asset->id) }}">
                    @csrf @method('DELETE')
                    <button class="mem-row-action" onclick="return confirm('Remove {{ addslashes($asset->name) }}?')">Remove</button>
                  </form>
                </div>
              </div>
              <div id="asset-edit-{{ $asset->id }}" class="mem-edit-panel" style="display:none">
                <form method="POST" action="{{ route('memory.assets.update', $asset->id) }}" style="display:flex;flex-direction:column;gap:10px">
                  @csrf @method('PATCH')
                  <div><label class="mem-field-label">{{ ucfirst($memoryCopy['asset_noun']) }} name</label><input type="text" name="name" value="{{ $asset->name }}" required class="mem-input"></div>
                  <div class="mem-field-row">
                    <div><label class="mem-field-label">Type</label>
                      <select name="type" class="mem-select">@foreach($assetTypes as $t)<option @if($asset->type === $t) selected @endif>{{ $t }}</option>@endforeach</select>
                    </div>
                    <div><label class="mem-field-label">Vendor</label><input type="text" name="vendor" value="{{ $asset->vendor }}" class="mem-input"></div>
                  </div>
                  <div class="mem-field-row">
                    <div><label class="mem-field-label">Renewal Date</label><input type="date" name="renewal_date" value="{{ $asset->renewal_date }}" class="mem-input"></div>
                    <div><label class="mem-field-label">Status</label>
                      <select name="status" class="mem-select">@foreach(['active','expiring','expired','cancelled'] as $s)<option @if(($asset->status ?? 'active') === $s) selected @endif>{{ $s }}</option>@endforeach</select>
                    </div>
                  </div>
                  <div class="mem-field-row">
                    <div><label class="mem-field-label">Cost / Year ($)</label><input type="number" name="cost_per_year" step="0.01" value="{{ $asset->cost_per_year }}" class="mem-input"></div>
                    <div><label class="mem-field-label">{{ ucfirst($memoryCopy['client_noun']) }}</label>
                      <select name="client_id" class="mem-select"><option value="">— none —</option>@foreach($clients as $cl)<option value="{{ $cl->id }}" @if($asset->client_id == $cl->id) selected @endif>{{ $cl->name }}</option>@endforeach</select>
                    </div>
                  </div>
                  <div style="display:flex;gap:8px">
                    <button type="submit" class="mem-btn">Save</button>
                    <button type="button" class="mem-btn-secondary" onclick="toggleAssetEdit({{ $asset->id }})">Cancel</button>
                  </div>
                </form>
              </div>
              @empty
              <div class="mem-row-empty">No assets yet.</div>
              @endforelse
            </div>
            <div class="mem-form-card">
              <div class="mem-form-head">Add {{ ucfirst($memoryCopy['asset_noun']) }}</div>
              <form method="POST" action="{{ route('memory.assets.store') }}" class="mem-form-body">
                @csrf
                <div><label class="mem-field-label">{{ ucfirst($memoryCopy['asset_noun']) }} name</label><input type="text" name="name" required placeholder="e.g. {{ $memoryCopy['example_asset'] }}" class="mem-input"></div>
                <div class="mem-field-row">
                  <div><label class="mem-field-label">Type</label>
                    <select name="type" class="mem-select">@foreach($assetTypes as $t)<option>{{ $t }}</option>@endforeach</select>
                  </div>
                  <div><label class="mem-field-label">Vendor</label><input type="text" name="vendor" class="mem-input"></div>
                </div>
                <div class="mem-field-row">
                  <div><label class="mem-field-label">Renewal Date</label><input type="date" name="renewal_date" required class="mem-input"></div>
                  <div><label class="mem-field-label">Status</label>
                    <select name="status" class="mem-select"><option value="active">Active</option><option value="expiring">Expiring</option><option value="expired">Expired</option><option value="cancelled">Cancelled</option></select>
                  </div>
                </div>
                <div><label class="mem-field-label">Cost / Year ($)</label><input type="number" name="cost_per_year" step="0.01" class="mem-input"></div>
                <div><label class="mem-field-label">{{ ucfirst($memoryCopy['client_noun']) }}</label>
                  <select name="client_id" class="mem-select"><option value="">— none —</option>@foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select>
                </div>
                <button type="submit" class="mem-btn">Add {{ ucfirst($memoryCopy['asset_noun']) }}</button>
              </form>
            </div>
          </div>
        </div>

        {{-- ── GROUPS ── --}}
        <div id="sub-groups" class="sub-pane" style="display:none">
          @if($myDeployments->isEmpty())
          <div class="mem-empty-card"><div class="mem-empty-sub">No workers deployed yet. Deploy a worker to start creating asset groups.</div></div>
          @elseif($myGroups->isEmpty())
          <div class="mem-empty-card">
            <div class="mem-empty-title">No groups yet</div>
            <div class="mem-empty-sub" style="margin-bottom:14px">Groups are created from within each worker's memory page.</div>
            @foreach($myDeployments as $dep)
            <a href="{{ route('workers.memory.groups', $dep->id) }}" class="mem-btn-secondary" style="margin:0 6px">{{ $dep->name }} →</a>
            @endforeach
          </div>
          @else
          @php $groupsByDep = $myGroups->groupBy('deployment_id'); @endphp
          @foreach($groupsByDep as $depId => $depGroups)
          @php $depName = $depGroups->first()->deployment_name; $workerSlug = $depGroups->first()->worker_slug; @endphp
          <div style="margin-bottom:18px">
            <div class="mem-group-dep">
              <div><span class="mem-group-dep-name">{{ $depName }}</span><span class="mem-group-dep-slug">{{ $workerSlug }}</span></div>
              <a href="{{ route('workers.memory.groups', $depId) }}" class="mem-row-action">Manage →</a>
            </div>
            @foreach($depGroups as $group)
            @php
              $nearestExpiry = $group->items->whereNotNull('renewal_date')->sortBy('renewal_date')->first();
              $gDays = $nearestExpiry ? (int) now()->diffInDays($nearestExpiry->renewal_date, false) : null;
            @endphp
            <div class="mem-group-card">
              <div class="mem-group-head">
                <div style="flex:1;min-width:0">
                  <span class="mem-row-name">{{ $group->name }}</span>
                  @if($group->type)<span class="mem-badge" style="background:var(--db-chip);color:var(--db-text-muted)">{{ $group->type }}</span>@endif
                  @if($gDays !== null)<span class="mem-badge" style="background:{{ $gDays<=0?'rgba(239,68,68,.15)':($gDays<=30?'rgba(245,158,11,.15)':'var(--db-chip)') }};color:{{ $gDays<=0?'#ef4444':($gDays<=30?'#f59e0b':'var(--db-text-muted)') }}">{{ $gDays <= 0 ? 'Expired' : 'Next '.$gDays.'d' }}</span>@endif
                  <div class="mem-row-sub">{{ $group->items->count() }} asset{{ $group->items->count() !== 1 ? 's' : '' }}{{ $group->client_name ? ' · '.$group->client_name : '' }}</div>
                </div>
                <a href="{{ route('workers.memory.groups', $depId) }}" class="mem-btn-secondary">Edit</a>
              </div>
              @if($group->items->isNotEmpty())
              <div class="mem-group-items">
                @foreach($group->items as $item)
                @php $iDays = $item->renewal_date ? (int) now()->diffInDays($item->renewal_date, false) : null; @endphp
                <span class="mem-group-chip"><span style="width:6px;height:6px;border-radius:50%;background:{{ $iDays!==null && $iDays<=0 ? '#ef4444' : ($iDays!==null && $iDays<=30 ? '#f59e0b' : 'var(--db-border)') }}"></span>{{ $item->name }}</span>
                @endforeach
              </div>
              @endif
            </div>
            @endforeach
          </div>
          @endforeach
          @endif
        </div>

        {{-- ── AVA RULES ── --}}
        <div id="sub-rules" class="sub-pane" style="display:none">
          <div class="mem-grid">
            <div class="mem-list">
              @forelse($rules as $rule)
              @php $pc = match($rule->priority) { 'Critical'=>'#ef4444','High'=>'#f59e0b','Medium'=>'var(--db-text-muted)',default=>'var(--db-text-muted)' }; @endphp
              <div class="mem-row">
                <div class="min-w-0">
                  <div class="mem-row-name" style="font-family:monospace">{{ $rule->rule_id }} <span style="font-family:'Inter',sans-serif;color:{{ $pc }};font-size:12px;font-weight:600">{{ $rule->priority }}</span>
                    @if(!$rule->active)<span class="mem-badge" style="background:var(--db-chip);color:var(--db-text-muted)">Inactive</span>@endif
                  </div>
                  <div class="mem-row-sub">{{ $rule->condition }}</div>
                  <div class="mem-row-sub2">→ {{ $rule->action }}</div>
                </div>
                <div style="display:flex;gap:10px;flex-shrink:0">
                  <button type="button" class="mem-row-action" onclick="toggleEdit('rule-{{ $rule->id }}')">Edit</button>
                  <form method="POST" action="{{ route('memory.rules.destroy', $rule->id) }}">
                    @csrf @method('DELETE')
                    <button class="mem-row-action" onclick="return confirm('Remove rule {{ addslashes($rule->rule_id) }}?')">Remove</button>
                  </form>
                </div>
              </div>
              <div id="edit-rule-{{ $rule->id }}" class="mem-edit-panel" style="display:none">
                <form method="POST" action="{{ route('memory.rules.update', $rule->id) }}" style="display:flex;flex-direction:column;gap:10px">
                  @csrf @method('PATCH')
                  <div class="mem-field-row">
                    <div><label class="mem-field-label">Rule ID</label><input type="text" name="rule_id" value="{{ $rule->rule_id }}" class="mem-input" style="font-family:monospace"></div>
                    <div><label class="mem-field-label">Priority</label>
                      <select name="priority" class="mem-select">@foreach(['Critical','High','Medium','Low'] as $p)<option @if($rule->priority === $p) selected @endif>{{ $p }}</option>@endforeach</select>
                    </div>
                  </div>
                  <div><label class="mem-field-label">Condition (when…)</label><textarea name="condition" rows="3" required class="mem-textarea">{{ $rule->condition }}</textarea></div>
                  <div><label class="mem-field-label">Action (then…)</label><textarea name="action" rows="3" required class="mem-textarea">{{ $rule->action }}</textarea></div>
                  <label class="mem-toggle-row">
                    <div><div class="mem-toggle-title">Active</div></div>
                    <div class="mem-toggle"><input type="checkbox" name="active" value="1" @if($rule->active) checked @endif><div class="mem-toggle-track"><div class="mem-toggle-thumb"></div></div></div>
                  </label>
                  <div style="display:flex;gap:8px">
                    <button type="submit" class="mem-btn">Save</button>
                    <button type="button" class="mem-btn-secondary" onclick="toggleEdit('rule-{{ $rule->id }}')">Cancel</button>
                  </div>
                </form>
              </div>
              @empty
              <div class="mem-row-empty">No rules yet.</div>
              @endforelse
            </div>
            <div class="mem-form-card">
              <div class="mem-form-head">Add Rule</div>
              <form method="POST" action="{{ route('memory.rules.store') }}" class="mem-form-body">
                @csrf
                <div class="mem-field-row">
                  <div><label class="mem-field-label">Rule ID</label><input type="text" name="rule_id" placeholder="AVA-007" class="mem-input" style="font-family:monospace"></div>
                  <div><label class="mem-field-label">Priority</label>
                    <select name="priority" class="mem-select"><option>Critical</option><option>High</option><option>Medium</option><option>Low</option></select>
                  </div>
                </div>
                <div><label class="mem-field-label">Condition (when…)</label><textarea name="condition" rows="3" required class="mem-textarea"></textarea></div>
                <div><label class="mem-field-label">Action (then…)</label><textarea name="action" rows="3" required class="mem-textarea"></textarea></div>
                <button type="submit" class="mem-btn">Add Rule</button>
              </form>
            </div>
          </div>
        </div>

      </div>{{-- /pane-mine --}}

      {{-- ════════ TAB: SHARED WITH ME ════════ --}}
      <div id="pane-shared" class="hub-pane" style="display:none">
        @forelse($incoming as $grant)
        @php $perms = json_decode($grant->permissions, true); @endphp
        <div class="mem-shared-card">
          <div class="mem-shared-head">
            <div style="flex:1;min-width:0">
              <div class="mem-row-name">{{ $grant->owner_name }}'s Memory</div>
              <div class="mem-row-sub">{{ $grant->deployment_name }} · {{ $grant->worker_slug }}</div>
              <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:5px">
                @foreach($perms as $p)<span class="mem-badge" style="background:var(--db-chip);color:var(--db-text-muted)">{{ $p }}</span>@endforeach
              </div>
            </div>
            <div class="mem-shared-stats">
              @foreach([['Clients',$grant->client_count],['Contacts',$grant->contact_count],['Assets',$grant->asset_count],['Groups',$grant->group_count]] as [$label,$count])
              <div class="mem-shared-stat"><div class="mem-shared-stat-num">{{ $count }}</div><div class="mem-shared-stat-label">{{ $label }}</div></div>
              @endforeach
            </div>
            <a href="{{ route('memory.shared', $grant->id) }}" class="mem-btn" style="align-self:center">Open Memory →</a>
          </div>
          <div class="mem-shared-meta">
            <span>Accepted {{ \Carbon\Carbon::parse($grant->accepted_at)->diffForHumans() }}</span>
            <span>{{ $grant->owner_email }}</span>
          </div>
        </div>
        @empty
        <div class="mem-empty-card"><div class="mem-empty-title">No shared memories yet</div><div class="mem-empty-sub">When a team member grants you access to their memory, it will appear here.</div></div>
        @endforelse
      </div>

      {{-- ════════ TAB: ACCESS MANAGEMENT ════════ --}}
      <div id="pane-access" class="hub-pane" style="display:none">
        <div class="wo-card-title" style="margin-bottom:10px">Who has access to your memory</div>
        @forelse($outgoing as $grant)
        @php $perms = json_decode($grant->permissions, true); @endphp
        <div class="mem-shared-card">
          <div class="mem-shared-head" style="border-bottom:none">
            <div style="flex:1;min-width:0">
              <div class="mem-row-name">{{ $grant->grantee_name }} <span style="font-family:monospace;color:var(--db-text-muted);font-weight:400;font-size:12px">{{ $grant->grantee_code }}</span>
                <span class="mem-badge" style="background:{{ $grant->status==='accepted' ? 'rgba(34,197,94,.15)' : 'rgba(245,158,11,.15)' }};color:{{ $grant->status==='accepted' ? '#22c55e' : '#f59e0b' }}">{{ ucfirst($grant->status) }}</span>
              </div>
              <div class="mem-row-sub">{{ $grant->deployment_name }} · {{ $grant->worker_slug }}</div>
              <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:5px">
                @foreach($perms as $p)<span class="mem-badge" style="background:var(--db-chip);color:var(--db-text-muted)">{{ $p }}</span>@endforeach
              </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
              <div class="mem-row-sub">{{ $grant->event_count }} actions</div>
              @if($grant->last_action)<div class="mem-row-sub2">Last: {{ \Carbon\Carbon::parse($grant->last_action)->diffForHumans() }}</div>@endif
              <form method="POST" action="{{ route('memory.access.revoke', $grant->id) }}" style="margin-top:8px" onsubmit="return confirm('Revoke access for {{ $grant->grantee_name }}?')">
                @csrf
                <button class="mem-btn-secondary" style="color:#ef4444;border-color:rgba(239,68,68,.3)">Revoke</button>
              </form>
            </div>
          </div>
          @if($grant->event_count > 0)
          <button type="button" class="mem-access-audit-btn" onclick="toggleAudit({{ $grant->id }})"><span id="audit-chevron-{{ $grant->id }}">▶</span> Activity trail</button>
          <div id="audit-{{ $grant->id }}" style="display:none">
            @php
              $events = DB::table('memory_access_events as e')->join('users as u', 'u.id', '=', 'e.actor_user_id')->where('e.grant_id', $grant->id)->select('e.*', 'u.name as actor_name')->orderByDesc('e.created_at')->limit(20)->get();
            @endphp
            @foreach($events as $ev)
            <div class="mem-access-audit-row">
              <span style="width:6px;height:6px;border-radius:50%;background:{{ $ev->action==='modified'?'#f59e0b':($ev->action==='uploaded'?'#3b82f6':($ev->action==='copied'?'#8b5cf6':'var(--db-border)')) }}"></span>
              <span style="flex:1"><strong style="color:var(--db-text)">{{ $ev->actor_name }}</strong> <span style="color:var(--db-text-muted)">{{ $ev->action }}</span> <span style="font-family:monospace;color:var(--db-text-muted)">{{ $ev->table_name }}#{{ $ev->record_id }}</span></span>
              <span style="color:var(--db-text-muted)">{{ \Carbon\Carbon::parse($ev->created_at)->diffForHumans() }}</span>
            </div>
            @endforeach
          </div>
          @endif
        </div>
        @empty
        <div class="mem-empty-card" style="margin-bottom:20px"><div class="mem-empty-sub">You haven't shared your memory with anyone yet.</div></div>
        @endforelse

        <div class="mem-form-card" style="margin-top:20px">
          <div class="mem-form-head">
            Invite a team member
            <div style="font-size:12px;font-weight:400;color:var(--db-text-muted);margin-top:3px">They must already have a UNIT account. Enter their profile code (UNIT-XXXXX) or email.</div>
          </div>
          <form method="POST" action="{{ route('memory.access.invite') }}" class="mem-form-body">
            @csrf
            @if($errors->any())<div class="mem-status error">{{ $errors->first() }}</div>@endif
            <div><label class="mem-field-label">Profile code or email</label><input type="text" name="lookup" value="{{ old('lookup') }}" placeholder="UNIT-AB3XY or name@company.com" class="mem-input" style="font-family:monospace"></div>
            <div><label class="mem-field-label">Which deployment's memory</label>
              <select name="deployment_id" class="mem-select">
                <option value="">Select a deployment…</option>
                @foreach($myDeployments as $dep)<option value="{{ $dep->id }}" {{ old('deployment_id') == $dep->id ? 'selected' : '' }}>{{ $dep->name }} ({{ $dep->worker_slug }})</option>@endforeach
              </select>
            </div>
            @php
              $permOptions = [['view','View','Read memory records — clients, contacts, assets'],['copy','Copy','Duplicate records into their own workspace'],['upload','Upload','Add new records to your memory'],['modify','Modify','Edit existing records in your memory']];
            @endphp
            <div>
              <label class="mem-field-label">Permissions</label>
              <div style="display:flex;flex-direction:column;gap:8px">
                @foreach($permOptions as [$val, $label, $desc])
                @php $checked = in_array($val, old('permissions', ['view'])); @endphp
                <label class="mem-toggle-row">
                  <div><div class="mem-toggle-title">{{ $label }}</div><div class="mem-toggle-sub">{{ $desc }}</div></div>
                  <div class="mem-toggle"><input type="checkbox" name="permissions[]" value="{{ $val }}" {{ $checked ? 'checked' : '' }}><div class="mem-toggle-track"><div class="mem-toggle-thumb"></div></div></div>
                </label>
                @endforeach
                <div class="mem-toggle-row" style="opacity:.4">
                  <div><div class="mem-toggle-title">Delete</div><div class="mem-toggle-sub">Never available to collaborators</div></div>
                  <div class="mem-toggle"><div class="mem-toggle-track"><div class="mem-toggle-thumb"></div></div></div>
                </div>
              </div>
            </div>
            <button type="submit" class="mem-btn" style="align-self:flex-start;padding:10px 22px">Send Invitation</button>
          </form>
        </div>
      </div>

    </div>
  </main>

  {{-- ══ RIGHT PANEL — reserved for the immutable 3-column layout, empty on this page ══ --}}
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

function showTab(name) {
  document.querySelectorAll('.hub-pane').forEach(function (p) { p.style.display = 'none'; });
  document.querySelectorAll('.mem-tab').forEach(function (b) { b.classList.remove('active'); });
  document.getElementById('pane-' + name).style.display = 'block';
  var btn = document.getElementById('tab-' + name);
  if (btn) btn.classList.add('active');
}

function showSubTab(name) {
  document.querySelectorAll('.sub-pane').forEach(function (p) { p.style.display = 'none'; });
  document.querySelectorAll('#pane-mine > .mem-tabs .mem-tab').forEach(function (b) { b.classList.remove('active'); });
  document.getElementById('sub-' + name).style.display = 'block';
  var btn = document.getElementById('subtab-' + name);
  if (btn) btn.classList.add('active');
}

function toggleAssetEdit(id) {
  var el = document.getElementById('asset-edit-' + id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function toggleEdit(key) {
  var el = document.getElementById('edit-' + key);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function toggleAudit(id) {
  var panel = document.getElementById('audit-' + id);
  var chevron = document.getElementById('audit-chevron-' + id);
  var hidden = panel.style.display === 'none' || !panel.style.display;
  panel.style.display = hidden ? 'block' : 'none';
  chevron.textContent = hidden ? '▼' : '▶';
}

function updateTemplateLink(type) {
  var link = document.getElementById('tpl-link');
  link.href = link.href.replace(/\/import\/template\/\w+/, '/import/template/' + type);
  link.textContent = type + '_import_template.csv';
}

var hash = window.location.hash.replace('#', '');
if (['mine','shared','access'].includes(hash)) {
  showTab(hash);
} else if (['clients','contacts','assets','groups','rules'].includes(hash)) {
  showSubTab(hash);
}

@if($errors->any())
  document.addEventListener('DOMContentLoaded', function () { showTab('access'); });
@endif
</script>
</body>
</html>

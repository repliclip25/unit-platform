<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $dep->name }} · Fast Track — UNIT</title>
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

/* ── SHELL (identical to /desk/{slug}, /workers/{slug}/overview, /memory, /templates, /rules) ── */
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
.ob-step.active .ob-step-num{background:var(--db-invert-bg);color:var(--db-invert-text);box-shadow:0 0 0 4px rgba(128,128,128,.15)}
.ob-step-body{padding-top:4px;padding-bottom:20px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:14px;font-weight:700;color:var(--db-text);line-height:1.2}
.ob-step.pending .ob-step-label{color:var(--db-text-muted)}
.ob-step-desc{font-size:12px;color:var(--db-text-muted);margin-top:2px;line-height:1.4;display:flex;align-items:center;gap:5px}
.ob-step.active .ob-step-body{background:var(--db-card);border:1.5px solid var(--db-border);border-radius:12px;padding:10px 14px;margin-right:-4px}

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

.ft-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);margin-bottom:4px}
.ft-sub{font-size:13px;color:var(--db-text-muted);margin-bottom:20px}

.ft-card{border:1px solid var(--db-border);border-radius:16px;padding:20px;margin-bottom:16px}
.ft-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;flex-wrap:wrap}
.ft-card-title{font-size:14px;font-weight:700;color:var(--db-text)}
.ft-card-sub{font-size:12px;color:var(--db-text-muted);margin-top:2px}

.ft-usage{font-size:11.5px;font-family:monospace;color:var(--db-text-muted)}
.ft-usage.warn{color:#f59e0b}
.ft-usage.done{color:#22c55e}
.ft-usage-bar{margin-top:6px;height:4px;width:110px;margin-left:auto;border-radius:99px;overflow:hidden;background:var(--db-chip)}
.ft-usage-fill{height:100%;border-radius:99px}

/* Pipeline stage flow */
.ft-flow{display:flex;align-items:flex-start;gap:2px;overflow-x:auto;padding-bottom:6px;scrollbar-width:none}
.ft-flow::-webkit-scrollbar{display:none}
.ft-stage{display:flex;flex-direction:column;align-items:center;flex-shrink:0;min-width:78px}
.ft-bubble{width:40px;height:40px;border-radius:14px;border:2px solid var(--db-border);background:var(--db-chip);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:var(--db-text-muted);position:relative;margin-bottom:8px;transition:all .25s}
.ft-bubble svg{width:18px;height:18px}
.ft-label{font-size:11px;font-weight:700;color:var(--db-text-muted);text-align:center;line-height:1.25}
.ft-arrow{flex-shrink:0;width:18px;padding-top:16px;opacity:.35}
.ft-status-line{margin-top:14px;font-size:12.5px;font-family:monospace;color:var(--db-text-muted);text-align:center;display:none}

/* Completion summary card */
.ft-result{margin-top:18px;border-radius:14px;border:1px solid rgba(34,197,94,.3);background:rgba(34,197,94,.06);padding:18px;display:none}
.ft-result-head{display:flex;align-items:center;gap:8px;margin-bottom:14px}
.ft-result-head svg{width:18px;height:18px;color:#22c55e;flex-shrink:0}
.ft-result-title{font-size:14px;font-weight:700;color:var(--db-text)}
.ft-result-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px 20px;margin-bottom:14px}
.ft-result-row{font-size:12.5px}
.ft-result-row .lbl{color:var(--db-text-muted);display:block;font-size:11px;margin-bottom:2px}
.ft-result-row .val{color:var(--db-text);font-weight:600}
.ft-result-actions{display:flex;gap:10px;flex-wrap:wrap}

.ft-inbox-select{width:100%;border-radius:9px;padding:9px 12px;font-size:13px;background:transparent;border:1px solid var(--db-border);color:var(--db-text);font-family:inherit;margin-bottom:12px}
.ft-run-row{display:flex;gap:10px}
.ft-run-btn{flex:1;padding:11px 16px;border-radius:11px;border:none;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text);display:flex;align-items:center;justify-content:center;gap:8px}
.ft-run-btn:hover{opacity:.9}
.ft-run-btn:disabled{opacity:.5;cursor:not-allowed}
.ft-run-btn svg{width:15px;height:15px}
.mem-btn-secondary{padding:8px 14px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text-muted);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block}
.mem-btn-secondary:hover{background:var(--db-chip);color:var(--db-text)}
.mem-btn{padding:9px 16px;border-radius:9px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text);white-space:nowrap}
.mem-btn:hover{opacity:.9}

.ft-empty{text-align:center;padding:14px;border-radius:10px;border:1px dashed var(--db-border);font-size:12.5px;color:var(--db-text-muted)}
.ft-empty a{color:var(--db-text);font-weight:600}

/* ── Human-action gates (Confirm & Send / Confirm Payment) — mirrors the
   real production flow: only these two stages ever need a click. ── */
.ft-gate{display:none;margin-top:16px;border-radius:14px;border:1.5px solid #F5C518;background:rgba(245,197,24,.08);padding:16px 18px}
.ft-gate.is-visible{display:block}
.ft-gate-title{font-size:13px;font-weight:800;color:var(--db-text);margin-bottom:3px;display:flex;align-items:center;gap:7px}
.ft-gate-dot{width:7px;height:7px;border-radius:50%;background:#F5C518;animation:ftpulse 1.2s ease infinite;flex-shrink:0}
@keyframes ftpulse{0%,100%{opacity:1}50%{opacity:.35}}
.ft-gate-sub{font-size:12px;color:var(--db-text-muted);margin-bottom:12px;line-height:1.5}
.ft-gate-btns{display:flex;gap:8px;flex-wrap:wrap}
.ft-gate-btn{padding:10px 16px;border-radius:10px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text)}
.ft-gate-btn:hover{opacity:.9}
.ft-gate-btn:disabled{opacity:.5;cursor:not-allowed}
.ft-gate-btn.secondary{background:transparent;border:1.5px solid var(--db-border);color:var(--db-text)}

/* ── Rolling stage-output log — one entry per completed stage, in order,
   showing exactly what that stage produced. ── */
.ft-log{margin-top:18px;display:none}
.ft-log.is-visible{display:block}
.ft-log-title{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:10px}
.ft-log-item{border:1px solid var(--db-border);border-radius:10px;padding:10px 14px;margin-bottom:8px;font-size:12.5px}
.ft-log-item-head{display:flex;align-items:center;gap:8px;font-weight:700;color:var(--db-text);margin-bottom:4px}
.ft-log-item-head svg{width:13px;height:13px;stroke:#22c55e;flex-shrink:0}
.ft-log-item-body{color:var(--db-text-muted);line-height:1.5;white-space:pre-wrap}

/* Scenario editor */
.ft-scenario-toggle{width:100%;display:flex;align-items:center;justify-content:space-between;background:none;border:none;cursor:pointer;font-family:inherit;padding:0}
.ft-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px}
.ft-form-grid.full{grid-template-columns:1fr}
.mem-field-label{font-size:11px;font-weight:600;color:var(--db-text-muted);margin-bottom:5px;display:block}
.mem-input,.mem-textarea{width:100%;border-radius:9px;padding:9px 12px;font-size:13px;background:transparent;border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.mem-input:focus,.mem-textarea:focus{border-color:var(--db-invert-bg)}

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
  .ft-form-grid{grid-template-columns:1fr}
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
$tokenFmt  = $tokenTotal >= 1000000 ? number_format($tokenTotal/1000000,1).'M' : number_format($tokenTotal);
$firstName2 = $firstName;
$ftDefaults = [
  'scenario_title'    => 'Domain Renewal Test',
  'sender_name'       => 'Namecheap Renewals Team',
  'sender_email'      => 'renewals@namecheap.com',
  'asset_name'        => 'yourdomain.com',
  'asset_type'        => 'Domain',
  'contact_name'      => auth()->user()->name,
  'renewal_price'     => '$12.98/year',
  'days_until_expiry' => 14,
  'custom_note'       => '',
];
$sv = fn($f) => old($f, $scenario->{$f} ?? $ftDefaults[$f]);
$ftCanRun = $isMultiCredential || $connectedInboxes->isNotEmpty();
$sidebarLinks = [
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('app.workers.memory',$dep->worker_slug), false],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('app.workers.templates',['slug'=>$dep->worker_slug]), false],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('app.workers.rules',$dep->worker_slug), false],
  ['Configure',    'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', route('app.workers.configure',$dep->worker_slug), false],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('app.workers.fast-track.page',$dep->worker_slug), true],
  ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('app.workers.connect',$dep->worker_slug), false],
  ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('app.billing'), false],
  ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('app.workers.transactions', $dep->worker_slug), false],
];
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
        <a href="{{ route('app.profile.show') }}" style="color:inherit;text-decoration:none">{{ strtoupper($firstName2) }}'S WORKERS</a>
      </div>
      @foreach($workerCatalog as $wc)
      @php
        $wDot  = $wc->status==='active' ? '#22c55e' : '#f59e0b';
        $wHref = !$wc->active ? route('public.workers.index') : ($wc->slug==='ava' ? route('app.desk.ava') : route('app.workers.overview',$wc->slug));
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
      <a href="{{ route('public.workers.index') }}" class="ob-step pending" style="text-decoration:none;margin-top:4px">
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
      <p>Fast Track runs the real pipeline — nothing is sent to a real contact.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      @if(session('success'))<div class="mem-status success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="mem-status error">{{ session('error') }}</div>@endif
      @if($errors->any())<div class="mem-status error">{{ $errors->first() }}</div>@endif

      <div class="ft-h1">Fast Track</div>
      <div class="ft-sub">Run {{ $dep->name }} through its real pipeline with a test email — see exactly how it will behave before touching a real inbox.</div>

      {{-- Pipeline card --}}
      <div class="ft-card">
        <div class="ft-card-head">
          <div>
            <div class="ft-card-title">Pipeline</div>
            <div class="ft-card-sub">{{ count($pipelineStages) }}-stage process · how {{ $dep->name }} handles every email</div>
          </div>
          <div style="text-align:right">
            @if($ftSubscribed)
              <div class="ft-usage done">Unlimited</div>
            @elseif($ftLeft > 0)
              <div class="ft-usage {{ $ftLeft <= 2 ? 'warn' : '' }}">{{ $ftLeft }}/{{ $ftMax }} runs left</div>
              <div class="ft-usage-bar"><div class="ft-usage-fill" style="width:{{ $ftMax>0 ? ($ftUses/$ftMax)*100 : 0 }}%;background:{{ $ftLeft>3?'var(--db-invert-bg)':($ftLeft>0?'#f59e0b':'#ef4444') }}"></div></div>
            @else
              <div class="ft-usage" style="color:#ef4444">Trial exhausted</div>
            @endif
          </div>
        </div>

        <div class="ft-flow" id="ft-flow">
          @foreach($pipelineStages as $i => $stage)
          <div class="ft-stage" id="ft-stage-{{ $stage['key'] }}">
            <div class="ft-bubble" id="ft-bubble-{{ $stage['key'] }}">{{ $i+1 }}</div>
            <div class="ft-label">{{ $stage['label'] }}</div>
          </div>
          @if(!$loop->last)
          <svg class="ft-arrow" viewBox="0 0 20 8" fill="none"><path d="M0 4 H14 M10 1 L16 4 L10 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--db-text-muted)"/></svg>
          @endif
          @endforeach
        </div>
        <div class="ft-status-line" id="ft-status-line"></div>

        <div class="ft-result" id="ft-result">
          <div class="ft-result-head">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <div class="ft-result-title">Run complete — here's what {{ $dep->name }} decided</div>
          </div>
          <div class="ft-result-grid">
            <div class="ft-result-row"><span class="lbl">Category</span><span class="val" id="ft-r-category">—</span></div>
            <div class="ft-result-row"><span class="lbl">Priority</span><span class="val" id="ft-r-priority">—</span></div>
            <div class="ft-result-row"><span class="lbl">Client matched</span><span class="val" id="ft-r-client">—</span></div>
            <div class="ft-result-row"><span class="lbl">Asset</span><span class="val" id="ft-r-asset">—</span></div>
            <div class="ft-result-row"><span class="lbl">Rule applied</span><span class="val" id="ft-r-rule">—</span></div>
            <div class="ft-result-row"><span class="lbl">Confidence</span><span class="val" id="ft-r-confidence">—</span></div>
            <div class="ft-result-row" style="grid-column:1/-1"><span class="lbl">Draft subject</span><span class="val" id="ft-r-subject">—</span></div>
          </div>
          <div class="ft-result-actions" id="ft-result-actions">
            <button type="button" class="mem-btn" onclick="var u=new URL(location.href);u.searchParams.delete('watch');location.href=u.toString();">Run again</button>
            <a href="#" id="ft-r-gmail-link" class="mem-btn-secondary" style="display:none" target="_blank" rel="noopener">Open draft in Gmail →</a>
          </div>
        </div>

        {{-- Human gate — swaps content depending on which stage is waiting.
             Only Confirm & Send (stage 7) and Confirm Payment (stage 12) ever
             show here — every other stage animates through automatically,
             matching how AVA actually behaves on a real inbox. --}}
        <div class="ft-gate" id="ft-gate">
          <div class="ft-gate-title"><span class="ft-gate-dot"></span><span id="ft-gate-title">—</span></div>
          <div class="ft-gate-sub" id="ft-gate-sub"></div>
          <div class="ft-gate-btns" id="ft-gate-btns"></div>
        </div>

        {{-- Rolling log — one entry per completed stage, in the order it ran --}}
        <div class="ft-log" id="ft-log">
          <div class="ft-log-title">What each stage produced</div>
          <div id="ft-log-items"></div>
        </div>

        <div style="margin-top:18px" id="ft-run-section">
          @if($ftLeft > 0 || $ftSubscribed)
            @if($ftCanRun)
            <form method="POST" action="{{ route('app.workers.fast-track', $dep->id) }}" id="ft-form">
              @csrf
              <input type="hidden" name="return" value="page">
              @if($isMultiCredential)
              @elseif($connectedInboxes->count() > 1)
                <select name="credential_id" class="ft-inbox-select">
                  @foreach($connectedInboxes as $inbox)
                  <option value="{{ $inbox->id }}" {{ $inbox->is_primary ? 'selected' : '' }}>{{ $inbox->gmail_address }}{{ $inbox->is_primary ? ' · Primary' : '' }}</option>
                  @endforeach
                </select>
              @elseif($connectedInboxes->count() === 1)
                <input type="hidden" name="credential_id" value="{{ $connectedInboxes->first()->id }}">
              @endif
              <div class="ft-run-row">
                <button type="submit" class="ft-run-btn" id="ft-run-btn">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                  Run Fast Track
                </button>
                @if(!$ftSubscribed)
                <a href="{{ route('app.workers.billing', $dep->worker_slug) }}" class="mem-btn-secondary">Subscribe for unlimited →</a>
                @endif
              </div>
            </form>
            @else
            <div class="ft-empty">No inbox connected yet. <a href="{{ route('app.workers.connect', $dep->id) }}">Connect one</a> to run Fast Track.</div>
            @endif
          @else
            <div class="ft-empty">Trial runs used up. <a href="{{ route('app.workers.billing', $dep->worker_slug) }}">Choose a plan</a> for unlimited Fast Track runs.</div>
          @endif
        </div>
      </div>

      {{-- Scenario editor --}}
      <div class="ft-card">
        <button type="button" class="ft-scenario-toggle" onclick="toggleScenario()">
          <div>
            <div class="ft-card-title">Test scenario</div>
            <div class="ft-card-sub">{{ $sv('scenario_title') }} — edit to match how your clients actually email you</div>
          </div>
          <span class="mem-btn-secondary" id="ft-scenario-btn">Edit</span>
        </button>

        <div id="ft-scenario-form" style="display:none">
          <form method="POST" action="{{ route('app.workers.fast-track.scenario', $dep->id) }}">
            @csrf @method('PATCH')
            <div class="ft-form-grid full">
              <div><label class="mem-field-label">Scenario title</label><input type="text" name="scenario_title" class="mem-input" value="{{ $sv('scenario_title') }}" required></div>
            </div>
            <div class="ft-form-grid">
              <div><label class="mem-field-label">Sender name</label><input type="text" name="sender_name" class="mem-input" value="{{ $sv('sender_name') }}" required></div>
              <div><label class="mem-field-label">Sender email</label><input type="email" name="sender_email" class="mem-input" value="{{ $sv('sender_email') }}" required></div>
              <div><label class="mem-field-label">Asset name</label><input type="text" name="asset_name" class="mem-input" value="{{ $sv('asset_name') }}" required></div>
              <div><label class="mem-field-label">Asset type</label><input type="text" name="asset_type" class="mem-input" value="{{ $sv('asset_type') }}" required></div>
              <div><label class="mem-field-label">Contact name</label><input type="text" name="contact_name" class="mem-input" value="{{ $sv('contact_name') }}" required></div>
              <div><label class="mem-field-label">Renewal price</label><input type="text" name="renewal_price" class="mem-input" value="{{ $sv('renewal_price') }}" required></div>
              <div><label class="mem-field-label">Days until expiry</label><input type="number" name="days_until_expiry" min="1" max="365" class="mem-input" value="{{ $sv('days_until_expiry') }}" required></div>
            </div>
            <div class="ft-form-grid full">
              <div><label class="mem-field-label">Custom note (optional)</label><textarea name="custom_note" rows="2" class="mem-textarea">{{ $sv('custom_note') }}</textarea></div>
            </div>
            <div style="margin-top:14px;display:flex;gap:8px">
              <button type="submit" class="mem-btn">Save scenario</button>
              <button type="button" class="mem-btn-secondary" onclick="toggleScenario()">Cancel</button>
            </div>
          </form>
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

function toggleScenario() {
  var el = document.getElementById('ft-scenario-form');
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

// ── Live pipeline polling ────────────────────────────────────────────────
var WATCH_TX = @json($watchTxId);
var STAGE_KEYS = @json(collect($pipelineStages)->pluck('key'));
var STATUS_TO_STAGE = {
  received:        'webhook',
  ingesting:       'webhook',
  reading:         'read_email',
  classifying:     'classify',
  memory_lookup:   'memory',
  logging:         'log_entry',
  template_select: 'select_template',
  drafting:        'draft_email',
  pushing:         'push_draft',
  draft_ready:     'push_draft',
  approved:        'push_draft',
  sent:            'push_draft',
  blocked:         'read_email',
};
var STATUS_LABELS = {
  reading: 'Reading email…', classifying: 'Classifying…', memory_lookup: 'Looking up memory…',
  logging: 'Logging transaction…', template_select: 'Selecting template…',
  drafting: 'Drafting email with AI…', pushing: 'Pushing to Gmail…',
};

function setStage(key, state) {
  var bubble = document.getElementById('ft-bubble-' + key);
  var label  = document.getElementById('ft-stage-' + key)?.querySelector('.ft-label');
  if (!bubble) return;
  if (state === 'done') {
    bubble.style.borderColor = '#22c55e'; bubble.style.background = 'rgba(34,197,94,.12)'; bubble.style.color = '#22c55e';
    bubble.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
    if (label) label.style.color = '#22c55e';
  } else if (state === 'active') {
    bubble.style.borderColor = '#a78bfa'; bubble.style.background = 'rgba(167,139,250,.14)'; bubble.style.color = '#a78bfa';
    bubble.style.boxShadow = '0 0 0 4px rgba(167,139,250,.15)';
    if (label) label.style.color = 'var(--db-text)';
  } else if (state === 'failed') {
    bubble.style.borderColor = '#ef4444'; bubble.style.background = 'rgba(239,68,68,.12)'; bubble.style.color = '#ef4444';
    bubble.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
    if (label) label.style.color = '#ef4444';
  }
}

function showResult(data) {
  document.getElementById('ft-r-category').textContent   = data.category || '—';
  document.getElementById('ft-r-priority').textContent   = data.priority || '—';
  document.getElementById('ft-r-client').textContent     = data.matched_client || '—';
  document.getElementById('ft-r-asset').textContent      = data.asset || '—';
  document.getElementById('ft-r-rule').textContent       = data.ava_rule || '—';
  document.getElementById('ft-r-confidence').textContent = data.confidence != null ? (data.confidence + '%' + (data.low_confidence ? ' (low — flagged for review)' : '')) : '—';
  document.getElementById('ft-r-subject').textContent    = data.subject || '—';

  var gmailLink = document.getElementById('ft-r-gmail-link');
  if (data.gmail_draft_id) {
    gmailLink.href = 'https://mail.google.com/mail/u/0/#drafts/' + data.gmail_draft_id;
    gmailLink.style.display = 'inline-block';
  }

  document.getElementById('ft-result').style.display = 'block';
}

// ── Human gates — only these two stages ever require a click, matching
// production exactly. Everything else animates through automatically. ────
var CSRF = document.querySelector('meta[name=csrf-token]').content;
var GATE_BUSY = false;

function showGate(kind, data) {
  var gate  = document.getElementById('ft-gate');
  var title = document.getElementById('ft-gate-title');
  var sub   = document.getElementById('ft-gate-sub');
  var btns  = document.getElementById('ft-gate-btns');

  if (kind === 'human_decide') {
    title.textContent = 'AVA is ready to send';
    sub.textContent    = 'Approve to send this draft and continue the renewal through invoice, payment, and archiving — exactly like a real renewal.';
    btns.innerHTML = '<button type="button" class="ft-gate-btn" id="ft-approve-btn">Confirm &amp; send</button>';
    document.getElementById('ft-approve-btn').addEventListener('click', function () {
      gateAction(this, '{{ url("/app/transactions") }}/' + WATCH_TX + '/decide', { decision: 'approved' });
    });
  } else if (kind === 'confirm_payment') {
    title.textContent = 'Waiting on payment confirmation';
    sub.textContent    = 'In a real renewal, AVA emails you reminders on a cadence until you confirm or cancel. Confirm to close out this cycle.';
    btns.innerHTML = '<button type="button" class="ft-gate-btn" id="ft-pay-confirm-btn">Confirm payment</button>'
                    + '<button type="button" class="ft-gate-btn secondary" id="ft-pay-cancel-btn">Cancel renewal</button>';
    document.getElementById('ft-pay-confirm-btn').addEventListener('click', function () {
      gateAction(this, '{{ url("/app/transactions") }}/' + WATCH_TX + '/confirm-renewal', {});
    });
    document.getElementById('ft-pay-cancel-btn').addEventListener('click', function () {
      gateAction(this, '{{ url("/app/transactions") }}/' + WATCH_TX + '/cancel-renewal', {});
    });
  }
  gate.classList.add('is-visible');
}

function hideGate() {
  document.getElementById('ft-gate').classList.remove('is-visible');
}

function gateAction(btn, url, body) {
  if (GATE_BUSY) return;
  GATE_BUSY = true;
  var btns = document.getElementById('ft-gate-btns').querySelectorAll('button');
  btns.forEach(function (b) { b.disabled = true; });
  btn.textContent = 'Working…';
  fetch(url, {
    method: 'POST', credentials: 'same-origin',
    headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify(body),
  }).then(function () {
    hideGate();
    GATE_BUSY = false;
    setTimeout(pollFastTrack, 600);
  }).catch(function () {
    GATE_BUSY = false;
    btn.disabled = false;
    btn.textContent = 'Try again';
  });
}

// ── Rolling log — one entry per completed stage, appended once each. ─────
var LOGGED = {};
function logStage(key, title, body) {
  if (LOGGED[key] || !body) return;
  LOGGED[key] = true;
  var wrap = document.getElementById('ft-log-items');
  var item = document.createElement('div');
  item.className = 'ft-log-item';
  item.innerHTML = '<div class="ft-log-item-head"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>' + title + '</div>'
    + '<div class="ft-log-item-body"></div>';
  item.querySelector('.ft-log-item-body').textContent = body; // textContent — never render stage data as HTML
  wrap.appendChild(item);
  document.getElementById('ft-log').classList.add('is-visible');
}

function logAllStages(data) {
  if (data.category) logStage('classify', 'Understand', 'Category: ' + data.category + ' · Priority: ' + (data.priority || '—'));
  if (data.matched_client || data.asset) logStage('memory', 'Verify', 'Client: ' + (data.matched_client || '—') + ' · Asset: ' + (data.asset || '—') + (data.confidence != null ? (' · Confidence: ' + data.confidence + '%') : ''));
  if (data.subject) logStage('draft_email', 'Draft', 'Subject: ' + data.subject + (data.body ? ('\n\n' + data.body) : ''));
  if (data.gmail_draft_id || ['draft_ready','approved','sent'].indexOf(data.status) > -1) logStage('push_draft', 'Deliver', 'Draft created in Gmail, ready for review.');
  if (['approved','sent'].indexOf(data.status) > -1 || (data.fulfillment_stage && STAGE_KEYS.indexOf(data.fulfillment_stage) > STAGE_KEYS.indexOf('human_decide'))) logStage('human_decide', 'You approve', 'You approved — AVA is continuing the renewal.');
  if (data.invoice_output) logStage('request_invoice', 'Request Invoice', data.invoice_output.status === 'simulated' ? 'Simulated — would request an invoice from the vendor on a real renewal.' : (data.invoice_output.status === 'requested' ? 'Requested from ' + data.invoice_output.to : 'No vendor address available — skipped.'));
  if (data.documents_output) logStage('request_documents', 'Request Documents', data.documents_output.status === 'simulated' ? 'Simulated — would request supporting documents from the vendor on a real renewal.' : (data.documents_output.status === 'requested' ? 'Requested from ' + data.documents_output.to : 'No vendor address available — skipped.'));
  if (data.payment_output) logStage('confirm_payment', 'Confirm Payment', data.payment_output.confirmed === false ? 'Renewal canceled by you.' : 'You confirmed payment.');
  if (data.renewal_output) logStage('update_renewal_date', 'Update Next Renewal Date', 'Renewal date moves from ' + (data.renewal_output.old_date || '—') + ' to ' + (data.renewal_output.new_date || '—') + '.');
  if (data.archive_output) logStage('archive_evidence', 'Archive Evidence', 'A PDF record of this renewal was generated.');
  if (data.notify_output) logStage('notify_stakeholders', 'Notify Stakeholders', 'Drafted: "' + (data.notify_output.subject || '') + '" (not sent for a test run).');
  if (data.fulfillment_stage === 'schedule_next_watch') logStage('schedule_next_watch', 'Confirm & Renew', 'Cycle complete — the asset re-enters continuous monitoring.');
}

var FT_FAIL_STREAK = 0;

function pollFastTrack() {
  var line = document.getElementById('ft-status-line');
  fetch('{{ url("/app/transactions") }}/' + WATCH_TX + '/status', { headers: { Accept: 'application/json' } })
    .then(function (r) {
      if (!r.ok) throw new Error('status ' + r.status);
      return r.json();
    })
    .then(function (data) {
      FT_FAIL_STREAK = 0;

      var fulfillmentDone = data.fulfillment_stage === 'schedule_next_watch';
      var currentKey = fulfillmentDone ? 'schedule_next_watch'
        : (data.fulfillment_stage || STATUS_TO_STAGE[data.status] || 'webhook');
      var currentIdx = STAGE_KEYS.indexOf(currentKey);
      if (currentIdx < 0) currentIdx = 0;

      STAGE_KEYS.forEach(function (key, idx) {
        if (data.failed && idx === currentIdx) setStage(key, 'failed');
        else if (idx < currentIdx || fulfillmentDone) setStage(key, 'done');
        else if (idx === currentIdx) setStage(key, data.failed ? 'failed' : 'active');
        // idx > currentIdx stays pending until reached — Fast Track now runs
        // the real fulfillment stages too, so this only reflects genuine
        // progress, not a blanket "done" the moment the draft is ready.
      });

      logAllStages(data);

      if (data.fulfillment_stage === 'human_decide') {
        showGate('human_decide', data);
      } else if (data.fulfillment_stage === 'confirm_payment') {
        showGate('confirm_payment', data);
      } else {
        hideGate();
      }

      if (fulfillmentDone) {
        line.textContent = '✓ Complete — full renewal cycle simulated';
        line.style.color = '#22c55e';
        showResult(data);
        return; // stop polling — nothing left to change
      }

      if (data.payment_output && data.payment_output.confirmed === false) {
        line.textContent = '○ Canceled — renewal stopped at your request';
        line.style.color = 'var(--db-text-muted)';
        showResult(data);
        return;
      }

      if (data.failed) {
        line.textContent = '✕ Pipeline failed — check the Activity Log for details';
        line.style.color = '#ef4444';
        document.getElementById('ft-run-section').style.display = '';
        return;
      }

      line.textContent = STATUS_LABELS[data.status] || (data.fulfillment_stage ? 'Working — ' + data.fulfillment_stage + '…' : ('Working — ' + data.status + '…'));
      line.style.color = 'var(--db-text-muted)';
      setTimeout(pollFastTrack, 2000);
    })
    .catch(function () {
      FT_FAIL_STREAK++;
      if (FT_FAIL_STREAK >= 5) {
        line.textContent = 'Lost connection checking status — refresh the page to check manually.';
        line.style.color = '#ef4444';
        document.getElementById('ft-run-section').style.display = '';
        return;
      }
      line.textContent = 'Checking status…';
      line.style.color = 'var(--db-text-muted)';
      setTimeout(pollFastTrack, 3000);
    });
}

if (WATCH_TX) {
  // Hide Run Fast Track / Subscribe entirely while a run is in progress —
  // not just disabled. They reappear via "Run again" (which reloads without
  // ?watch) or automatically if the run fails.
  document.getElementById('ft-run-section').style.display = 'none';
  var line0 = document.getElementById('ft-status-line');
  if (line0) { line0.style.display = 'block'; line0.textContent = 'Starting…'; }
  setStage(STAGE_KEYS[0], 'active');
  pollFastTrack();
}

var ftForm = document.getElementById('ft-form');
if (ftForm) {
  ftForm.addEventListener('submit', function () {
    var btn = document.getElementById('ft-run-btn');
    if (btn) { btn.disabled = true; btn.querySelector('svg') && btn.querySelector('svg').remove(); btn.textContent = 'Running…'; }
  });
}
</script>
</body>
</html>

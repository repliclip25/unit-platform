<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $dep->name }} · Templates — UNIT</title>
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

/* ── SHELL (identical to /desk/{slug}, /workers/{slug}/overview, /memory) ── */
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

.mem-right{background:var(--db-card);border-left:1px solid var(--db-border);overflow-y:auto}

/* ── CONTENT ── */
.mem-main{overflow-y:auto;padding:28px 32px 60px}
.mem-wrap{max-width:820px;margin:0 auto}

.mem-status{border-radius:12px;padding:10px 14px;font-size:13.5px;margin-bottom:16px}
.mem-status.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#22c55e}
.mem-status.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444}

.tpl-header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px}
.tpl-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.tpl-sub{font-size:13px;color:var(--db-text-muted);margin-top:3px;max-width:480px}
.tpl-sub strong{color:var(--db-text);font-weight:600}

.mem-btn{padding:9px 16px;border-radius:9px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text);white-space:nowrap}
.mem-btn:hover{opacity:.9}
.mem-btn-secondary{padding:8px 14px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text-muted);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block}
.mem-btn-secondary:hover{background:var(--db-chip);color:var(--db-text)}

.tpl-cat{border:1px solid var(--db-border);border-radius:16px;margin-bottom:16px;overflow:hidden}
.tpl-cat-head{padding:14px 18px;border-bottom:1px solid var(--db-border);display:flex;align-items:center;justify-content:space-between}
.tpl-cat-title{font-size:13.5px;font-weight:700;color:var(--db-text);display:flex;align-items:center;gap:8px}
.tpl-cat-count{font-size:11.5px;color:var(--db-text-muted)}
.mem-badge{font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:99px}

.tpl-row{padding:16px 18px;border-bottom:1px solid var(--db-border)}
.tpl-row:last-child{border-bottom:none}
.tpl-row-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:10px;flex-wrap:wrap}
.tpl-row-name{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.tpl-row-name-text{font-size:13.5px;font-weight:600;color:var(--db-text)}
.tpl-row-actions{display:flex;align-items:center;gap:8px;flex-shrink:0}
.tpl-meta{font-size:12px;color:var(--db-text-muted);margin-bottom:4px}
.tpl-meta strong{color:var(--db-text);font-weight:600;font-family:monospace}
.tpl-body{font-size:12px;color:var(--db-text-muted);white-space:pre-wrap;border:1px solid var(--db-border);border-radius:10px;padding:12px 14px;line-height:1.6}

.mem-empty-card{background:transparent;border:1px dashed var(--db-border);border-radius:16px;padding:40px 20px;text-align:center}
.mem-empty-title{font-size:13px;font-weight:700;color:var(--db-text);margin-bottom:4px}
.mem-empty-sub{font-size:12px;color:var(--db-text-muted)}

/* Modals */
.tpl-modal-overlay{position:fixed;inset:0;z-index:60;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.5);backdrop-filter:blur(6px)}
.tpl-modal-overlay.open{display:flex}
.tpl-modal{background:var(--db-card);border:1px solid var(--db-border);border-radius:18px;width:100%;max-width:600px;margin:16px;max-height:88vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 12px 40px rgba(0,0,0,.25)}
.tpl-modal-head{padding:18px 22px;border-bottom:1px solid var(--db-border);display:flex;align-items:flex-start;justify-content:space-between;flex-shrink:0}
.tpl-modal-title{font-size:14px;font-weight:700;color:var(--db-text)}
.tpl-modal-sub{font-size:11.5px;color:var(--db-text-muted);margin-top:3px}
.tpl-modal-close{background:none;border:none;color:var(--db-text-muted);font-size:18px;cursor:pointer;line-height:1;padding:0}
.tpl-modal-close:hover{color:var(--db-text)}
.tpl-modal-body{padding:18px 22px;overflow-y:auto;display:flex;flex-direction:column;gap:12px}
.mem-field-label{font-size:11px;font-weight:600;color:var(--db-text-muted);margin-bottom:5px;display:block}
.mem-field-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.mem-select,.mem-input,.mem-textarea{width:100%;border-radius:9px;padding:9px 12px;font-size:13px;background:transparent;border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.mem-select:focus,.mem-input:focus,.mem-textarea:focus{border-color:var(--db-invert-bg)}
.tpl-hint{font-size:10.5px;color:var(--db-text-muted);margin-top:5px}
.tpl-hint span{font-family:monospace}
.mem-toggle-row{display:flex;align-items:center;gap:10px}
.mem-toggle{position:relative;width:36px;height:20px;flex-shrink:0}
.mem-toggle input{position:absolute;opacity:0;width:0;height:0}
.mem-toggle-track{position:absolute;inset:0;border-radius:99px;background:var(--db-chip);transition:.15s}
.mem-toggle-thumb{position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:var(--db-invert-bg);transition:.15s}
.mem-toggle input:checked ~ .mem-toggle-track{background:var(--db-invert-bg)}
.mem-toggle input:checked ~ .mem-toggle-track .mem-toggle-thumb{transform:translateX(16px);background:var(--db-invert-text)}
.tpl-modal-foot{padding:16px 22px;border-top:1px solid var(--db-border);display:flex;gap:10px;flex-shrink:0}
.tpl-modal-foot .mem-btn{flex:1}

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
  .mem-card-area{display:block;margin:0;border-radius:0;border:none;box-shadow:none;background:transparent}
  .mem-field-row{grid-template-columns:1fr}
  .tpl-row-top{flex-direction:column}
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
$userId = auth()->id();
$byCategory = $templates->groupBy('category');
$sidebarLinks = [
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('memory'), false],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('workers.templates',['slug'=>$dep->worker_slug]), true],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('workers.rules',$dep->worker_slug), false],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('workers.fast-track.page',$dep->worker_slug), false],
  ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('workers.connect',$dep->worker_slug), false],
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
      <p>Your templates power what {{ $dep->name }} sends.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      @if(session('success'))<div class="mem-status success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="mem-status error">{{ session('error') }}</div>@endif

      <div class="tpl-header">
        <div>
          <div class="tpl-h1">Email Templates</div>
          <div class="tpl-sub">Platform defaults are read-only — click <strong>Customize</strong> to create your editable copy. {{ $dep->name }} always uses your custom version first.</div>
        </div>
        <button type="button" class="mem-btn" onclick="openAddModal()">+ New Template</button>
      </div>

      @forelse($byCategory as $category => $group)
      @php
        $tenantCount = $group->where('user_id', $userId)->count();
        $forkedIds = $group->where('user_id', $userId)->pluck('forked_from')->filter()->flip();
      @endphp
      <div class="tpl-cat">
        <div class="tpl-cat-head">
          <div class="tpl-cat-title">
            {{ $category }}
            @if($tenantCount)<span class="mem-badge" style="background:var(--db-chip);color:var(--db-text)">{{ $tenantCount }} custom</span>@endif
          </div>
          <div class="tpl-cat-count">{{ $group->count() }} template{{ $group->count() !== 1 ? 's' : '' }}</div>
        </div>

        @foreach($group as $t)
        @php
          $isDefault = !$t->user_id;
          if ($isDefault && isset($forkedIds[$t->id])) continue;
        @endphp
        <div class="tpl-row" id="template-{{ $t->id }}">
          <div class="tpl-row-top">
            <div class="tpl-row-name">
              <span class="tpl-row-name-text">{{ $t->name }}</span>
              @if($isDefault)
                <span class="mem-badge" style="background:var(--db-chip);color:var(--db-text-muted)">Platform default</span>
              @else
                <span class="mem-badge" style="background:var(--db-chip);color:var(--db-text)">Your template</span>
                @if($t->forked_from)<span style="font-size:11px;color:var(--db-text-muted)">forked from default</span>@endif
              @endif
              @if($t->approval_required)
                <span class="mem-badge" style="background:rgba(245,158,11,.12);color:#f59e0b" title="{{ $dep->name }} will draft the email but wait for you to review and send it from Transactions">✋ You review before send</span>
              @else
                <span class="mem-badge" style="background:rgba(34,197,94,.12);color:#22c55e" title="{{ $dep->name }} sends immediately without waiting for your review">⚡ Auto-sends</span>
              @endif
            </div>
            <div class="tpl-row-actions">
              <form method="POST" action="{{ route('workers.templates.test', [$dep->id, $t->id]) }}">
                @csrf
                <button type="submit" class="mem-btn-secondary" title="Send test to {{ auth()->user()->email }}">▶ Test Send</button>
              </form>
              @if($isDefault)
                <button type="button" class="mem-btn-secondary" id="customize-btn-{{ $t->id }}" onclick="customizeTemplate({{ $t->id }})">Customize</button>
              @else
                <button type="button" class="mem-btn-secondary" onclick='openEditModal({{ $t->id }}, @json($t))'>Edit</button>
                <form method="POST" action="{{ route('workers.templates.destroy', [$dep->id, $t->id]) }}">
                  @csrf @method('DELETE')
                  <button type="submit" class="mem-btn-secondary" style="color:#ef4444;border-color:rgba(239,68,68,.3)" onclick="return confirm('Remove this template?')">Remove</button>
                </form>
              @endif
            </div>
          </div>
          <div class="tpl-meta">Tone: <strong style="font-family:inherit;font-weight:600;color:var(--db-text)">{{ $t->tone }}</strong></div>
          <div class="tpl-meta">Subject: <strong>{{ $t->subject_template }}</strong></div>
          <div class="tpl-body">{{ $t->body_template }}</div>
        </div>
        @endforeach
      </div>
      @empty
      <div class="mem-empty-card">
        <div class="mem-empty-title">No templates yet</div>
        <div class="mem-empty-sub">Platform defaults will appear here once seeded.</div>
      </div>
      @endforelse

    </div>
  </main>

  <aside class="mem-right"></aside>
  </div>

</div>{{-- ob-page --}}

{{-- ══ EDIT MODAL ══ --}}
<div class="tpl-modal-overlay" id="edit-modal">
  <div class="tpl-modal">
    <div class="tpl-modal-head">
      <div>
        <div class="tpl-modal-title">Edit Template</div>
        <div class="tpl-modal-sub">Changes apply to your worker only — platform default is unchanged.</div>
      </div>
      <button type="button" class="tpl-modal-close" onclick="closeEditModal()">✕</button>
    </div>
    <form id="edit-form" method="POST">
      @csrf @method('PUT')
      <div class="tpl-modal-body">
        <div class="mem-field-row">
          <div><label class="mem-field-label">Template Name</label><input type="text" name="name" id="edit-name" required class="mem-input"></div>
          <div><label class="mem-field-label">Tone</label><input type="text" name="tone" id="edit-tone" class="mem-input"></div>
        </div>
        <div>
          <label class="mem-field-label">Subject Template</label>
          <input type="text" name="subject_template" id="edit-subject" required class="mem-input" style="font-family:monospace">
          <div class="tpl-hint">Available: <span>@{{asset}}, @{{due_date}}, @{{client}}, @{{renewal_price}}</span></div>
        </div>
        <div>
          <label class="mem-field-label">Body Template</label>
          <textarea name="body_template" id="edit-body" rows="10" required class="mem-textarea" style="font-family:monospace;resize:none"></textarea>
          <div class="tpl-hint">Available: <span>@{{contact_first_name}}, @{{asset}}, @{{client}}, @{{due_date}}, @{{sender_name}}, @{{renewal_price}}, @{{days_until_expiry}}</span></div>
        </div>
        <label class="mem-toggle-row">
          <div class="mem-toggle"><input type="checkbox" name="approval_required" id="edit-approval" value="1"><div class="mem-toggle-track"><div class="mem-toggle-thumb"></div></div></div>
          <span style="font-size:12px;color:var(--db-text-muted)">You review drafts before {{ $dep->name }} sends <span style="color:var(--db-text-muted);opacity:.7">(uncheck to auto-send)</span></span>
        </label>
      </div>
      <div class="tpl-modal-foot">
        <button type="submit" class="mem-btn">Save Changes</button>
        <button type="button" class="mem-btn-secondary" onclick="closeEditModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- ══ ADD MODAL ══ --}}
<div class="tpl-modal-overlay" id="add-modal">
  <div class="tpl-modal">
    <div class="tpl-modal-head">
      <div class="tpl-modal-title">New Template</div>
      <button type="button" class="tpl-modal-close" onclick="closeAddModal()">✕</button>
    </div>
    <form method="POST" action="{{ route('workers.templates.store', $dep->id) }}">
      @csrf
      <div class="tpl-modal-body">
        <div class="mem-field-row">
          <div><label class="mem-field-label">Template Name</label><input type="text" name="name" required class="mem-input"></div>
          <div><label class="mem-field-label">Category</label><input type="text" name="category" required placeholder="e.g. Domain Renewal" class="mem-input"></div>
        </div>
        <div><label class="mem-field-label">Tone</label><input type="text" name="tone" value="Professional, concise" class="mem-input"></div>
        <div><label class="mem-field-label">Subject Template</label><input type="text" name="subject_template" required class="mem-input" style="font-family:monospace"></div>
        <div>
          <label class="mem-field-label">Body Template</label>
          <textarea name="body_template" rows="8" required class="mem-textarea" style="font-family:monospace;resize:none"></textarea>
          <div class="tpl-hint">Available: <span>@{{contact_first_name}}, @{{asset}}, @{{client}}, @{{due_date}}, @{{sender_name}}, @{{renewal_price}}</span></div>
        </div>
        <label class="mem-toggle-row">
          <div class="mem-toggle"><input type="checkbox" name="approval_required" value="1" checked><div class="mem-toggle-track"><div class="mem-toggle-thumb"></div></div></div>
          <span style="font-size:12px;color:var(--db-text-muted)">You review drafts before {{ $dep->name }} sends <span style="opacity:.7">(uncheck to auto-send)</span></span>
        </label>
      </div>
      <div class="tpl-modal-foot">
        <button type="submit" class="mem-btn">Save Template</button>
        <button type="button" class="mem-btn-secondary" onclick="closeAddModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

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

const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const WORKER_ID = {{ $dep->id }};

function openEditModal(id, t) {
    document.getElementById('edit-name').value    = t.name;
    document.getElementById('edit-tone').value    = t.tone || '';
    document.getElementById('edit-subject').value = t.subject_template;
    document.getElementById('edit-body').value    = t.body_template;
    document.getElementById('edit-approval').checked = !!parseInt(t.approval_required);
    document.getElementById('edit-form').action   = '/workers/' + WORKER_ID + '/templates/' + id;
    showModal('edit-modal');
}
function closeEditModal() { hideModal('edit-modal'); }
function openAddModal()   { showModal('add-modal'); }
function closeAddModal()  { hideModal('add-modal'); }

async function customizeTemplate(defaultId) {
    const btn = document.getElementById('customize-btn-' + defaultId);
    btn.textContent = 'Creating…';
    btn.disabled = true;

    try {
        const res = await fetch('/workers/' + WORKER_ID + '/templates/' + defaultId + '/fork', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.template) {
            openEditModal(data.template.id, data.template);
            document.getElementById('edit-modal').addEventListener('click', function handler(e) {
                if (e.target === this) { location.reload(); this.removeEventListener('click', handler); }
            });
            document.getElementById('edit-form').addEventListener('submit', function() {
                sessionStorage.setItem('template_saved', '1');
            }, { once: true });
        }
    } catch(e) {
        btn.textContent = 'Customize';
        btn.disabled = false;
        alert('Failed to customize template. Please try again.');
    }
}
function showModal(id) { document.getElementById(id).classList.add('open'); }
function hideModal(id) { document.getElementById(id).classList.remove('open'); }

@if(session('edit_template'))
document.addEventListener('DOMContentLoaded', function () {
    const card = document.getElementById('template-{{ session('edit_template') }}');
    if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
});
@endif
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $dep->name }} · Asset Groups — UNIT</title>
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

/* ── SHELL (identical to other worker-scoped pages) ── */
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

.ag-header{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px}
.ag-back{font-size:12px;color:var(--db-text-muted);text-decoration:none}
.ag-back:hover{color:var(--db-text)}
.ag-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);margin-top:4px}
.ag-sub{font-size:12.5px;color:var(--db-text-muted);margin-top:3px;max-width:520px}

.mem-btn{padding:9px 16px;border-radius:9px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text);white-space:nowrap}
.mem-btn:hover{opacity:.9}
.mem-btn-secondary{padding:8px 14px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text-muted);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block}
.mem-btn-secondary:hover{background:var(--db-chip);color:var(--db-text)}

.mem-form-card{border:1px solid var(--db-border);border-radius:16px;overflow:hidden;margin-bottom:16px}
.mem-form-head{padding:14px 18px;border-bottom:1px solid var(--db-border);font-size:13px;font-weight:700;color:var(--db-text)}
.mem-form-body{padding:16px 18px;display:flex;flex-direction:column;gap:12px}
.mem-field-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.mem-field-full{grid-column:1/-1}
.mem-field-label{font-size:11px;font-weight:600;color:var(--db-text-muted);margin-bottom:5px;display:block}
.mem-field-req{color:#ef4444;margin-left:2px}
.mem-select,.mem-input,.mem-textarea{width:100%;border-radius:9px;padding:9px 12px;font-size:13px;background:transparent;border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.mem-select:focus,.mem-input:focus,.mem-textarea:focus{outline:none;border-color:var(--db-invert-bg)}

.ag-legend{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px}
.ag-legend-chip{display:flex;align-items:center;gap:6px;font-size:11.5px;padding:5px 12px;border-radius:99px;background:var(--db-chip);border:1px solid var(--db-border);color:var(--db-text-muted)}
.ag-legend-chip strong{color:var(--db-text)}

.mem-badge{font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:99px;background:var(--db-chip);color:var(--db-text-muted)}

.ag-group{border:1px solid var(--db-border);border-radius:16px;margin-bottom:14px;overflow:hidden}
.ag-group-head{padding:14px 18px;border-bottom:1px solid var(--db-border);display:flex;flex-wrap:wrap;align-items:flex-start;gap:10px}
.ag-group-name{font-size:13.5px;font-weight:700;color:var(--db-text)}
.ag-group-sub{font-size:12px;color:var(--db-text-muted);margin-top:3px}
.ag-group-notes{font-size:12px;color:var(--db-text-muted);font-style:italic;margin-top:4px}
.ag-group-actions{display:flex;align-items:center;gap:12px;margin-left:auto}
.ag-link-action{font-size:12px;font-weight:600;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit}
.ag-link-action:hover{color:var(--db-text)}

.mem-edit-panel{background:transparent;border-top:1px solid var(--db-border);padding:16px 18px}

.ag-item-row{padding:12px 18px;border-bottom:1px solid var(--db-border);display:flex;align-items:center;gap:10px}
.ag-item-row:last-child{border-bottom:none}
.ag-item-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
.ag-item-name{font-size:12.5px;font-weight:600;color:var(--db-text)}
.ag-item-sub{font-size:11.5px;color:var(--db-text-muted);margin-top:1px}
.ag-item-remove{margin-left:auto;font-size:11.5px;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit}
.ag-item-remove:hover{color:#ef4444}

.mem-row-empty{padding:24px 18px;text-align:center;font-size:12.5px;color:var(--db-text-muted)}

.ag-add-asset{padding:14px 18px;border-top:1px solid var(--db-border);background:var(--db-chip)}
.ag-add-asset-note{padding:14px 18px;border-top:1px solid var(--db-border);font-size:12px;color:var(--db-text-muted)}
.ag-add-row{display:flex;flex-wrap:wrap;gap:8px}
.ag-add-row select{flex:1;min-width:180px}

.mem-empty-card{background:transparent;border:1px dashed var(--db-border);border-radius:16px;padding:40px 20px;text-align:center}
.mem-empty-title{font-size:13px;font-weight:700;color:var(--db-text);margin-bottom:4px}
.mem-empty-sub{font-size:12px;color:var(--db-text-muted)}

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
  .mem-field-row{grid-template-columns:1fr}
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
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('app.workers.memory',$dep->worker_slug), true],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('app.workers.templates',['slug'=>$dep->worker_slug]), false],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('app.workers.rules',$dep->worker_slug), false],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('app.workers.fast-track.page',$dep->worker_slug), false],
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
        <a href="{{ route('app.profile.show') }}" style="color:inherit;text-decoration:none">{{ strtoupper($firstName) }}'S WORKERS</a>
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
      <p>Groups are specific to {{ $dep->name }} — the underlying assets stay shared platform memory.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      @if(session('success'))<div class="mem-status success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="mem-status error">{{ session('error') }}</div>@endif

      <div class="ag-header">
        <div>
          <a href="{{ route('app.workers.memory', $dep->worker_slug) }}" class="ag-back">← Memory</a>
          <div class="ag-h1">Asset Groups</div>
          <div class="ag-sub">Bundle related assets from your memory into logical groups for this worker.</div>
        </div>
        <button onclick="document.getElementById('new-group-form').style.display='block'" class="mem-btn">+ New Group</button>
      </div>

      {{-- New Group Form --}}
      <div id="new-group-form" class="mem-form-card" style="display:none">
        <div class="mem-form-head">Create a new group</div>
        <form method="POST" action="{{ route('app.workers.memory.groups.store', $dep->id) }}" class="mem-form-body">
          @csrf
          <div class="mem-field-row">
            <div class="mem-field-full">
              <label class="mem-field-label">Group Name <span class="mem-field-req">*</span></label>
              <input type="text" name="name" required placeholder="e.g. ACME Corp Website Stack" class="mem-input">
            </div>
            @if($groupTypes)
            <div>
              <label class="mem-field-label">Group Type</label>
              <select name="type" class="mem-select">
                <option value="">— select type —</option>
                @foreach($groupTypes as $gt)<option value="{{ $gt['value'] }}">{{ $gt['label'] }}</option>@endforeach
              </select>
            </div>
            @endif
            <div>
              <label class="mem-field-label">Client (optional)</label>
              <select name="client_id" class="mem-select">
                <option value="">— no client —</option>
                @foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
              </select>
            </div>
            <div class="mem-field-full">
              <label class="mem-field-label">Notes</label>
              <textarea name="notes" rows="2" placeholder="What does this group represent?" class="mem-textarea"></textarea>
            </div>
          </div>
          <div style="display:flex;gap:8px">
            <button type="submit" class="mem-btn">Create Group</button>
            <button type="button" class="mem-btn-secondary" onclick="document.getElementById('new-group-form').style.display='none'">Cancel</button>
          </div>
        </form>
      </div>

      {{-- Group type legend --}}
      @if($groupTypes)
      <div class="ag-legend">
        @foreach($groupTypes as $gt)
        <span class="ag-legend-chip"><strong>{{ $gt['label'] }}</strong> · {{ $gt['description'] }}</span>
        @endforeach
      </div>
      @endif

      {{-- Groups list --}}
      @forelse($groups as $group)
      @php
        $typeLabel = collect($groupTypes)->firstWhere('value', $group->type)['label'] ?? $group->type;
        $nearestExpiry = $group->items->whereNotNull('renewal_date')->sortBy('renewal_date')->first();
        $days = $nearestExpiry ? (int) now()->diffInDays($nearestExpiry->renewal_date, false) : null;
      @endphp
      <div class="ag-group">
        <div class="ag-group-head">
          <div style="flex:1;min-width:0">
            <span class="ag-group-name">{{ $group->name }}</span>
            @if($group->type)<span class="mem-badge">{{ $typeLabel }}</span>@endif
            @if($days !== null)
            <span class="mem-badge" style="background:{{ $days<=0?'rgba(239,68,68,.15)':($days<=15?'rgba(245,158,11,.15)':($days<=30?'rgba(234,179,8,.15)':'var(--db-chip)')) }};color:{{ $days<=0?'#ef4444':($days<=15?'#f59e0b':($days<=30?'#eab308':'var(--db-text-muted)')) }}">{{ $days <= 0 ? 'Expired' : 'Next expiry ' . $days . 'd' }}</span>
            @endif
            <div class="ag-group-sub">{{ $group->items->count() }} asset{{ $group->items->count() !== 1 ? 's' : '' }}@if($group->client_name) · {{ $group->client_name }}@endif</div>
            @if($group->notes)<div class="ag-group-notes">{{ $group->notes }}</div>@endif
          </div>
          <div class="ag-group-actions">
            <button onclick="toggleGroupEdit({{ $group->id }})" class="ag-link-action">Edit</button>
            <form method="POST" action="{{ route('app.workers.memory.groups.destroy', [$dep->id, $group->id]) }}" onsubmit="return confirm('Remove group \'{{ addslashes($group->name) }}\'? Assets are not deleted.')">
              @csrf @method('DELETE')
              <button type="submit" class="ag-link-action">Remove</button>
            </form>
          </div>
        </div>

        {{-- Inline edit form --}}
        <div id="group-edit-{{ $group->id }}" class="mem-edit-panel" style="display:none">
          <form method="POST" action="{{ route('app.workers.memory.groups.update', [$dep->id, $group->id]) }}" style="display:flex;flex-direction:column;gap:10px">
            @csrf @method('PATCH')
            <div class="mem-field-row">
              <div class="mem-field-full">
                <label class="mem-field-label">Group Name</label>
                <input type="text" name="name" value="{{ $group->name }}" required class="mem-input">
              </div>
              @if($groupTypes)
              <div>
                <label class="mem-field-label">Group Type</label>
                <select name="type" class="mem-select">
                  <option value="">— none —</option>
                  @foreach($groupTypes as $gt)<option value="{{ $gt['value'] }}" {{ $group->type === $gt['value'] ? 'selected' : '' }}>{{ $gt['label'] }}</option>@endforeach
                </select>
              </div>
              @endif
              <div>
                <label class="mem-field-label">Client</label>
                <select name="client_id" class="mem-select">
                  <option value="">— none —</option>
                  @foreach($clients as $c)<option value="{{ $c->id }}" {{ $group->client_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
              </div>
              <div class="mem-field-full">
                <label class="mem-field-label">Notes</label>
                <textarea name="notes" rows="2" class="mem-textarea">{{ $group->notes }}</textarea>
              </div>
            </div>
            <div style="display:flex;gap:8px">
              <button type="submit" class="mem-btn">Save</button>
              <button type="button" class="mem-btn-secondary" onclick="toggleGroupEdit({{ $group->id }})">Cancel</button>
            </div>
          </form>
        </div>

        {{-- Asset items --}}
        @forelse($group->items as $item)
        @php
          $iDays = $item->renewal_date ? (int) now()->diffInDays($item->renewal_date, false) : null;
          $iColor = $iDays === null ? 'var(--db-text-muted)' : ($iDays <= 0 ? '#ef4444' : ($iDays <= 15 ? '#f59e0b' : ($iDays <= 30 ? '#eab308' : 'var(--db-text-muted)')));
        @endphp
        <div class="ag-item-row">
          <span class="ag-item-dot" style="background:{{ $iColor }}"></span>
          <div style="flex:1;min-width:0">
            <div class="ag-item-name">{{ $item->name }}</div>
            <div class="ag-item-sub">
              {{ $item->type }}{{ $item->vendor ? ' · ' . $item->vendor : '' }}
              @if($item->renewal_date)
                · <span style="color:{{ $iColor }}">{{ $item->renewal_date }}</span>
                @if($iDays !== null)<span style="color:var(--db-text-muted)">({{ $iDays <= 0 ? 'expired' : $iDays . 'd' }})</span>@endif
              @endif
            </div>
          </div>
          <form method="POST" action="{{ route('app.workers.memory.groups.items.remove', [$dep->id, $group->id, $item->id]) }}">
            @csrf @method('DELETE')
            <button class="ag-item-remove">Remove</button>
          </form>
        </div>
        @empty
        <div class="mem-row-empty">No assets in this group yet. Add one below.</div>
        @endforelse

        {{-- Add asset to group --}}
        @php
          $groupItemIds    = $group->items->pluck('id')->toArray();
          $availableAssets = $assets
              ->where('client_id', $group->client_id)
              ->where('type', '!=', 'discovered')
              ->whereNotIn('id', $groupItemIds);
        @endphp
        @if(!$group->client_id)
        <div class="ag-add-asset-note">Assign a client to this group to add assets.</div>
        @elseif($availableAssets->isNotEmpty())
        <div class="ag-add-asset">
          <form method="POST" action="{{ route('app.workers.memory.groups.items.add', [$dep->id, $group->id]) }}" class="ag-add-row">
            @csrf
            <select name="asset_id" required class="mem-select">
              <option value="">— select asset to add —</option>
              @foreach($availableAssets as $a)
              <option value="{{ $a->id }}">{{ $a->name }} ({{ $a->type }}{{ $a->renewal_date ? ' · ' . $a->renewal_date : '' }})</option>
              @endforeach
            </select>
            <button type="submit" class="mem-btn-secondary">Add Asset</button>
          </form>
        </div>
        @endif
      </div>
      @empty
      <div class="mem-empty-card">
        <div class="mem-empty-title">No groups yet</div>
        <div class="mem-empty-sub" style="margin-bottom:14px">Group related assets together — e.g. all assets for one client's website, or all policies under one contract.</div>
        <button onclick="document.getElementById('new-group-form').style.display='block'; window.scrollTo({top:0,behavior:'smooth'})" class="mem-btn">Create your first group</button>
      </div>
      @endforelse

    </div>
  </main>

  <aside class="mem-right"></aside>
  </div>

</div>{{-- ob-page --}}
</div>{{-- ob-shell --}}

<x-self-learn
    pageKey="workers.memory.groups"
    title="Asset Groups"
    body="Groups let you bundle related assets into logical sets for this worker. The assets themselves stay in platform memory — groups are just a worker-specific lens. When AVA drafts a renewal notice for any asset in a group, she can reference the full group context and bundle multiple expiring assets into one message instead of sending separate notices." />

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

function toggleGroupEdit(id) {
  var el = document.getElementById('group-edit-' + id);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>

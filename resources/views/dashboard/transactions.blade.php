<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Transactions — UNIT</title>
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

/* ── SHELL (identical to /desk/{slug}, /workers/{slug}/overview, /memory, /templates, /rules, /fast-track, /connect, /billing) ── */
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

.tx-header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap}
.tx-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.tx-sub{font-size:13px;color:var(--db-text-muted);margin-top:2px}
.tx-pending-badge{display:flex;align-items:center;gap:8px;padding:8px 16px;border-radius:12px;border:1px solid rgba(var(--accent-rgb,241,211,98),.35);background:rgba(var(--accent-rgb,241,211,98),.1)}
.tx-pending-dot{width:8px;height:8px;border-radius:50%;background:#a78bfa;flex-shrink:0;animation:pdot 1.4s ease infinite}
@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}
.tx-pending-text{font-size:13px;font-weight:600;color:var(--accent-text,var(--db-text))}

.tx-tabs{display:flex;gap:4px;background:var(--db-chip);border:1px solid var(--db-border);border-radius:12px;padding:4px;width:fit-content;margin-bottom:18px;flex-wrap:wrap}
.tx-tab{font-size:12px;padding:7px 14px;border-radius:9px;color:var(--db-text-muted);text-decoration:none;font-weight:600;white-space:nowrap}
.tx-tab.active{background:var(--db-invert-bg);color:var(--db-invert-text)}
.tx-tab-badge{margin-left:4px;padding:1px 6px;border-radius:99px;font-size:10.5px;font-weight:700;background:rgba(var(--accent-rgb,241,211,98),.25);color:var(--accent-text,var(--db-text))}

.tx-list{border:1px solid var(--db-border);border-radius:16px;overflow:hidden}
.tx-table{width:100%;border-collapse:collapse;font-size:13px}
.tx-table thead th{text-align:left;padding:12px 18px;font-size:11px;font-weight:600;color:var(--db-text-muted);border-bottom:1px solid var(--db-border)}
.tx-table tbody tr{border-bottom:1px solid var(--db-border)}
.tx-table tbody tr:last-child{border-bottom:none}
.tx-table tbody tr:hover{background:var(--db-chip)}
.tx-table td{padding:11px 18px;vertical-align:top}
.tx-empty{padding:48px 18px;text-align:center;color:var(--db-text-muted);font-size:13.5px}
.tx-tx-id{font-family:monospace;color:var(--db-text-muted);font-size:11.5px}
.tx-asset{font-size:12.5px;font-weight:600;color:var(--db-text)}
.tx-client{font-size:11.5px;color:var(--db-text-muted);margin-top:1px}
.tx-processing{font-size:11.5px;color:var(--db-text-muted);font-style:italic}
.tx-priority{font-size:12px;font-weight:600}
.tx-subject{font-size:12px;color:var(--db-text);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.tx-low-conf{font-size:11px;color:#f59e0b;margin-top:2px}
.tx-status-badge{font-size:11px;padding:2px 9px;border-radius:99px;font-weight:600}
.tx-human-decision{font-size:10.5px;color:var(--db-text-muted);margin-top:2px}
.tx-age{font-size:11.5px;color:var(--db-text-muted);white-space:nowrap}
.tx-view-btn{font-size:11.5px;font-weight:600;padding:5px 12px;border-radius:8px;text-decoration:none;white-space:nowrap}
.tx-view-btn.review{background:rgba(var(--accent-rgb,241,211,98),.18);color:var(--accent-text,var(--db-text));border:1px solid rgba(var(--accent-rgb,241,211,98),.4)}
.tx-view-btn.view{color:var(--db-text-muted)}

.tx-pagination{padding:14px 18px;border-top:1px solid var(--db-border)}
.tx-pagination :is(a,span){font-size:12.5px}

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
  .tx-list{overflow-x:auto}
  .tx-table{min-width:820px}
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
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('workers.memory','ava'), false],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('workers.templates',['slug'=>'ava']), false],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('workers.rules','ava'), false],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('workers.fast-track.page','ava'), false],
  ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('workers.connect','ava'), false],
  ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('billing'), false],
  ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('transactions'), true],
];
$pendingCount = $transactions->getCollection()->where('status', 'draft_ready')->whereNull('human_decision')->count();
$statusMeta = [
    'draft_ready'  => ['bg' => 'rgba(167,139,250,.15)', 'color' => '#a78bfa', 'label' => 'Pending Review'],
    'approved'     => ['bg' => 'rgba(34,197,94,.15)',  'color' => '#86efac', 'label' => 'Approved'],
    'sent'         => ['bg' => 'rgba(34,197,94,.15)',  'color' => '#86efac', 'label' => 'Sent'],
    'failed'       => ['bg' => 'rgba(239,68,68,.15)',  'color' => '#fca5a5', 'label' => 'Failed'],
    'human_review' => ['bg' => 'rgba(245,158,11,.15)', 'color' => '#fcd34d', 'label' => 'In Review'],
    'blocked'      => ['bg' => 'rgba(249,115,22,.15)', 'color' => '#fb923c', 'label' => 'Blocked'],
    'drafting'     => ['bg' => 'rgba(99,102,241,.1)',  'color' => '#818cf8', 'label' => 'Drafting'],
    'dismissed'    => ['bg' => 'rgba(75,85,99,.2)',    'color' => '#6b7280', 'label' => 'Dismissed'],
];
$priorityColors = ['Critical'=>'#ef4444','High'=>'#f59e0b','Medium'=>'#9ca3af','Low'=>'#6b7280'];
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
      <p>Every action your workers take is logged here.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      <div class="tx-header">
        <div>
          <div class="tx-h1">Transactions</div>
          <div class="tx-sub">AVA pipeline activity · human review queue</div>
        </div>
        @if($pendingCount > 0)
        <div class="tx-pending-badge">
          <span class="tx-pending-dot"></span>
          <span class="tx-pending-text">{{ $pendingCount }} awaiting your review</span>
        </div>
        @endif
      </div>

      <div class="tx-tabs">
        @foreach([['all','All'],['draft_ready','Pending Review'],['approved','Approved'],['failed','Failed'],['dismissed','Dismissed']] as [$val,$label])
        <a href="{{ route('transactions', ['filter' => $val]) }}" class="tx-tab {{ ($currentFilter ?? 'all') === $val ? 'active' : '' }}">
          {{ $label }}
          @if($val === 'draft_ready' && $pendingCount > 0)<span class="tx-tab-badge">{{ $pendingCount }}</span>@endif
        </a>
        @endforeach
      </div>

      <div class="tx-list">
        <table class="tx-table">
          <thead>
            <tr>
              <th>TX</th>
              <th>Asset · Client</th>
              <th>Category</th>
              <th>Priority</th>
              <th>Draft Subject</th>
              <th>Status</th>
              <th>Age</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($transactions as $tx)
            @php
              $memory   = $tx->memory_output ? json_decode($tx->memory_output) : null;
              $draft    = $tx->draft_output  ? json_decode($tx->draft_output)  : null;
              $sc       = $statusMeta[$tx->status] ?? ['bg'=>'rgba(75,85,99,.2)','color'=>'#6b7280','label'=>ucfirst(str_replace('_',' ',$tx->status))];
              $pc       = $priorityColors[$tx->priority ?? ''] ?? '#6b7280';
              $isReview = $tx->status === 'draft_ready' && !$tx->human_decision;
            @endphp
            <tr style="{{ $isReview ? 'border-left:2px solid var(--accent,#f1d362)' : '' }}">
              <td><span class="tx-tx-id">{{ $tx->tx_id }}</span></td>
              <td>
                @if($memory)
                  <div class="tx-asset">{{ $memory->asset ?? '—' }}</div>
                  <div class="tx-client">{{ $memory->matched_client ?? '—' }}</div>
                @else
                  <span class="tx-processing">Processing…</span>
                @endif
              </td>
              <td style="color:var(--db-text-muted);font-size:12px">{{ $tx->category ?? '—' }}</td>
              <td><span class="tx-priority" style="color:{{ $pc }}">{{ $tx->priority ?? '—' }}</span></td>
              <td>
                @if($draft)
                  <div class="tx-subject">{{ $draft->subject ?? '—' }}</div>
                  @if(!empty($draft->low_confidence))<div class="tx-low-conf">⚠ Low confidence</div>@endif
                @else
                  <span style="color:var(--db-text-muted);font-size:12px">—</span>
                @endif
              </td>
              <td>
                <span class="tx-status-badge" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }}">{{ $sc['label'] }}</span>
                @if($tx->human_decision)<div class="tx-human-decision">{{ ucfirst($tx->human_decision) }}</div>@endif
              </td>
              <td><span class="tx-age">{{ \Carbon\Carbon::parse($tx->created_at)->diffForHumans(null, true) }}</span></td>
              <td>
                <a href="{{ route('transactions.show', $tx->tx_id) }}" class="tx-view-btn {{ $isReview ? 'review' : 'view' }}">
                  {{ $isReview ? 'Review →' : 'View →' }}
                </a>
              </td>
            </tr>
            @empty
            <tr><td colspan="8" class="tx-empty">No transactions found.</td></tr>
            @endforelse
          </tbody>
        </table>

        @if($transactions->hasPages())
        <div class="tx-pagination">
          {{ $transactions->appends(request()->query())->links() }}
        </div>
        @endif
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

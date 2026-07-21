<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Billing — UNIT</title>
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

/* ── SHELL (identical to /desk/{slug}, /workers/{slug}/overview, /memory, etc.) ── */
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

.bill-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.bill-sub{font-size:12.5px;color:var(--db-text-muted);margin-top:2px}
.bill-header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap}
.mem-btn-secondary{padding:8px 14px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text-muted);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block}
.mem-btn-secondary:hover{background:var(--db-chip);color:var(--db-text)}
.mem-btn{padding:9px 16px;border-radius:9px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text);white-space:nowrap;text-decoration:none;display:inline-block}
.mem-btn:hover{opacity:.9}

.bill-promo{display:flex;align-items:center;gap:10px;padding:11px 16px;border-radius:12px;border:1px solid rgba(245,158,11,.35);background:rgba(245,158,11,.06);margin-bottom:16px;font-size:12.5px;color:#f59e0b;flex-wrap:wrap}

.bill-card{border:1px solid var(--db-border);border-radius:16px;overflow:hidden;margin-bottom:16px}
.bill-card.highlight{border-color:rgba(245,158,11,.35)}
.bill-card-head{padding:16px 18px;border-bottom:1px solid var(--db-border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px}
.bill-identity{display:flex;align-items:center;gap:12px;min-width:0}
.bill-avatar{width:38px;height:38px;border-radius:11px;background:var(--db-chip);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:var(--db-text-muted);flex-shrink:0}
.bill-name-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.bill-name{font-size:13.5px;font-weight:700;color:var(--db-text)}
.bill-badge{font-size:10.5px;font-weight:700;padding:2px 9px;border-radius:99px}
.bill-plan-badge{font-size:10.5px;padding:2px 9px;border-radius:99px;background:var(--db-chip);color:var(--db-text-muted);border:1px solid var(--db-border)}
.bill-meta{font-size:11.5px;color:var(--db-text-muted);margin-top:2px}
.bill-clock{text-align:right}
.bill-clock-num{font-size:1.55rem;font-weight:800;line-height:1;color:var(--db-text)}
.bill-clock-lbl{font-size:11px;font-weight:600;color:var(--db-text-muted)}
.bill-clock-link{font-size:11.5px;color:var(--db-text-muted);text-decoration:none;display:block;margin-top:2px}
.bill-clock-link:hover{text-decoration:underline}

.bill-card-body{padding:18px}
.bill-bar-row{display:flex;align-items:center;justify-content:space-between;font-size:12px;margin-bottom:6px}
.bill-bar{height:7px;border-radius:99px;overflow:hidden;background:var(--db-chip)}
.bill-bar-fill{height:100%;border-radius:99px;transition:width .5s ease}
.bill-note{font-size:12px;margin-top:6px}
.bill-status-box{padding:14px 16px;border-radius:12px;margin-bottom:16px}
.bill-status-box.warn{background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.2)}
.bill-status-box.canceled{background:rgba(127,29,29,.08);border:1px solid rgba(127,29,29,.3)}
.bill-status-title{font-size:13px;font-weight:700;margin-bottom:2px}
.bill-status-sub{font-size:12px;color:var(--db-text-muted)}

/* Plan picker */
.bill-plans{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;margin-top:12px}
.bill-plan{background:var(--db-chip);border:1px solid var(--db-border);border-radius:14px;padding:16px;display:flex;flex-direction:column;gap:10px;position:relative}
.bill-plan.pro{border:2px solid #F5C518}
.bill-plan-tag{position:absolute;top:-10px;left:14px;font-size:9.5px;padding:2px 9px;border-radius:99px;font-weight:700;letter-spacing:.04em;color:#3a2e00;background:#F5C518}
.bill-plan-name{font-size:12.5px;font-weight:700;color:var(--db-text)}
.bill-plan-price{font-size:24px;font-weight:800;color:var(--db-text);margin-top:3px;line-height:1}
.bill-plan-price span{font-size:11px;font-weight:400;color:var(--db-text-muted)}
.bill-plan-cap{font-size:10.5px;color:var(--db-text-muted);margin-top:3px}
.bill-plan-list{flex:1;display:flex;flex-direction:column;gap:5px;list-style:none;font-size:10.5px;color:var(--db-text)}
.bill-plan-list li{display:flex;align-items:flex-start;gap:5px}
.bill-plan-list li span{color:#22c55e;flex-shrink:0;font-size:9px;margin-top:2px}
.bill-plan-cta{display:block;width:100%;text-align:center;font-size:11.5px;font-weight:700;padding:9px 12px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text);cursor:pointer;font-family:inherit;text-decoration:none}
.bill-plan-cta.primary{border:none;background:var(--db-invert-bg);color:var(--db-invert-text)}

.bill-empty{text-align:center;padding:40px 20px;border:1px dashed var(--db-border);border-radius:16px}
.bill-empty p{font-size:13px;color:var(--db-text-muted)}

/* Invoices */
.bill-inv-row{padding:12px 18px;border-bottom:1px solid var(--db-border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.bill-inv-row:last-child{border-bottom:none}
.bill-inv-date{font-size:13px;color:var(--db-text)}
.bill-inv-amt{font-size:11.5px;color:var(--db-text-muted)}
.bill-inv-actions{display:flex;align-items:center;gap:10px}
.bill-inv-badge{font-size:10.5px;padding:2px 8px;border-radius:99px}
.bill-inv-link{font-size:11.5px;color:var(--db-text-muted);text-decoration:none}
.bill-inv-link:hover{color:var(--db-text)}
.bill-void-btn{font-size:11px;padding:4px 10px;border-radius:8px;border:1px solid rgba(245,158,11,.35);color:#f59e0b;background:transparent;cursor:pointer;font-family:inherit}
.bill-admin-tag{font-size:10.5px;padding:3px 8px;border-radius:6px;color:#f59e0b;border:1px solid rgba(245,158,11,.3);background:rgba(245,158,11,.06)}

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
  .bill-plans{grid-template-columns:1fr}
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
  ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('billing'), true],
  ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('transactions'), false],
];
$firstName2 = $firstName;
$statusColors = [
  'active'          => ['bg'=>'rgba(34,197,94,.1)',  'border'=>'rgba(34,197,94,.3)',  'text'=>'#22c55e', 'label'=>'Active'],
  'trial'           => ['bg'=>'rgba(245,158,11,.1)', 'border'=>'rgba(245,158,11,.3)', 'text'=>'#f59e0b', 'label'=>'Free Trial'],
  'trial_exhausted' => ['bg'=>'rgba(239,68,68,.1)',  'border'=>'rgba(239,68,68,.3)',  'text'=>'#ef4444', 'label'=>'Trial Ended'],
  'past_due'        => ['bg'=>'rgba(239,68,68,.1)',  'border'=>'rgba(239,68,68,.3)',  'text'=>'#ef4444', 'label'=>'Past Due'],
  'canceled'        => ['bg'=>'rgba(127,29,29,.12)', 'border'=>'rgba(127,29,29,.35)', 'text'=>'#ef4444', 'label'=>'Canceled'],
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
        <a href="{{ route('profile.show') }}" style="color:inherit;text-decoration:none">{{ strtoupper($firstName2) }}'S WORKERS</a>
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
      <p>One Stripe invoice covers every worker.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      @if(!empty($policyViolations['platform']))
      <div style="margin-bottom:16px">
        @include('partials.policy-violations', ['violations' => $policyViolations['platform']])
      </div>
      @endif

      @if($promotions->isNotEmpty())
      <div class="bill-promo">
        <span>✦</span>
        @foreach($promotions as $promo)
        <span>{{ $promo->discount_pct }}% off {{ $promo->applies_to === 'all' ? 'all workers' : $promo->worker_slug }} — {{ $promo->name }}</span>
        @endforeach
      </div>
      @endif

      <div class="bill-header">
        <div>
          <div class="bill-h1">Billing & Subscriptions</div>
          <div class="bill-sub">Each worker subscription bills to one monthly invoice</div>
        </div>
        <a href="{{ route('billing.portal') }}" class="mem-btn-secondary">Manage Payment →</a>
      </div>

      @forelse($deployments as $dep)
      @php
        $bill        = $billingRecords[$dep->id] ?? null;
        $status      = $bill?->status ?? 'unknown';
        $depTiers    = $pricingTiers[$dep->worker_slug] ?? collect();

        $processed   = (int) ($emailsProcessed[$dep->id]?->total ?? 0);
        $hoursSaved  = $processed > 0 ? round($processed * 16 / 60, 1) : null;

        $trialUsed   = (int) ($bill?->trial_transactions_used  ?? 0);
        $trialLimit  = (int) ($bill?->trial_transactions_limit ?? 25);
        $trialLeft   = max(0, $trialLimit - $trialUsed);
        $trialPct    = $trialLimit > 0 ? min(100, ($trialUsed / $trialLimit) * 100) : 0;
        $trialDaysLeft = $bill?->trial_ends_at ? max(0, (int) now()->diffInDays(\Carbon\Carbon::parse($bill->trial_ends_at), false)) : null;

        $activeTier  = null;
        $unitUsed    = (int) ($bill?->unit_count ?? 0);
        $unitLimit   = null;
        $unitLabel   = $bill?->billing_unit ?? 'email';
        $renewalDate = null;
        if ($status === 'active' && $bill?->plan_slug) {
          $activeTier  = $depTiers->firstWhere('plan_slug', $bill->plan_slug);
          $unitLimit   = $activeTier?->transaction_limit ?? null;
          if ($bill?->billing_period_start) {
            $renewalDate = \Carbon\Carbon::parse($bill->billing_period_start)->addMonth()->startOfMonth();
          }
        }
        $usagePct  = ($unitLimit && $unitLimit > 0) ? min(100, ($unitUsed / $unitLimit) * 100) : 0;
        $usageWarn = $unitLimit && $usagePct >= 80;

        $isTrialState    = in_array($status, ['trial', 'trial_exhausted']);
        $isCanceled      = $status === 'canceled';
        $isActive        = $status === 'active';
        $isPastDue       = $status === 'past_due';
        $isExhausted     = $status === 'trial_exhausted';
        $needsConversion = $isTrialState || $isCanceled;

        $sc = $statusColors[$status] ?? ['bg'=>'var(--db-chip)','border'=>'var(--db-border)','text'=>'var(--db-text-muted)','label'=>ucfirst($status)];
      @endphp

      <div class="bill-card {{ $needsConversion && $processed > 0 ? 'highlight' : '' }}" id="dep-{{ $dep->id }}">
        <div class="bill-card-head">
          <div class="bill-identity">
            <div class="bill-avatar">{{ strtoupper(substr($dep->worker_slug,0,1)) }}</div>
            <div style="min-width:0">
              <div class="bill-name-row">
                <span class="bill-name">{{ $dep->name }}</span>
                <span class="bill-badge" style="background:{{ $sc['bg'] }};border:1px solid {{ $sc['border'] }};color:{{ $sc['text'] }}">{{ $sc['label'] }}</span>
                @if($isActive && $activeTier)
                <span class="bill-plan-badge">{{ $activeTier->display_name }}@if($activeTier->monthly_flat_rate > 0) · ${{ number_format($activeTier->monthly_flat_rate, 0) }}/mo @endif</span>
                @endif
              </div>
              <div class="bill-meta">
                {{ $dep->worker_slug }}
                @if($isActive && $renewalDate) · renews {{ $renewalDate->format('M j, Y') }} @endif
                @if($isPastDue) · <span style="color:#ef4444">payment failed</span> @endif
              </div>
            </div>
          </div>
          <div class="bill-clock">
            @if($hoursSaved !== null)
              <div class="bill-clock-num">{{ $hoursSaved }} <span class="bill-clock-lbl">hrs saved</span></div>
              <a href="{{ route('transactions') }}?deployment={{ $dep->id }}" class="bill-clock-link">{{ number_format($processed) }} emails → view transactions</a>
            @else
              <a href="{{ route('transactions') }}?deployment={{ $dep->id }}" class="bill-clock-link">View transactions →</a>
            @endif
            <a href="{{ route('workers.billing', $dep->worker_slug) }}" class="bill-clock-link">View cost breakdown →</a>
          </div>
        </div>

        <div class="bill-card-body">
          @if(!empty($policyViolations[$dep->id]))
          <div style="margin-bottom:16px">
            @include('partials.policy-violations', ['violations' => $policyViolations[$dep->id]])
          </div>
          @endif

          @if($status === 'trial')
          <div style="margin-bottom:18px">
            <div class="bill-bar-row">
              <span style="color:{{ $trialPct >= 60 ? '#f59e0b' : 'var(--db-text-muted)' }}">{{ $trialUsed }} / {{ $trialLimit }} free emails used</span>
              <span style="color:var(--db-text-muted)">{{ $trialLeft }} remaining @if(!is_null($trialDaysLeft)) · {{ $trialDaysLeft }}d left @endif</span>
            </div>
            <div class="bill-bar"><div class="bill-bar-fill" style="width:{{ $trialPct }}%;background:{{ $trialPct >= 60 ? 'linear-gradient(90deg,#f59e0b,#ef4444)' : '#f59e0b' }}"></div></div>
            @if($trialLeft <= 5 && $trialLeft > 0)
            <div class="bill-note" style="color:#f59e0b">Only {{ $trialLeft }} free email{{ $trialLeft === 1 ? '' : 's' }} left — subscribe to keep processing</div>
            @endif
          </div>
          @elseif($isExhausted)
          <div class="bill-status-box warn">
            <div class="bill-status-title" style="color:#ef4444">Your trial has ended. {{ $dep->name }} is paused.</div>
            <div class="bill-status-sub">Subscribe to resume automatic email processing.</div>
          </div>
          @elseif($isPastDue)
          <div class="bill-status-box warn">
            <div class="bill-status-title" style="color:#ef4444">Payment failed — worker is paused</div>
            <div class="bill-status-sub" style="margin-bottom:10px">Update your payment method to resume.</div>
            <a href="{{ route('billing.portal') }}" class="mem-btn" style="background:#ef4444;color:#fff">Update Payment Method →</a>
          </div>
          @elseif($isCanceled)
          <div class="bill-status-box canceled">
            <div class="bill-status-title" style="color:#ef4444">Subscription canceled</div>
            <div class="bill-status-sub">
              @if($hoursSaved !== null) {{ $dep->name }} returned {{ $hoursSaved }} hours to your team. Reactivate to keep that going.
              @else Reactivate to resume automatic email processing.
              @endif
            </div>
          </div>
          @elseif($isActive)
            @if($unitLimit && $usageWarn)
            <div style="margin-bottom:14px;max-width:340px">
              <div class="bill-bar-row">
                <span style="color:{{ $usagePct >= 100 ? '#ef4444' : '#f59e0b' }}">{{ number_format($unitUsed) }} / {{ number_format($unitLimit) }} {{ $unitLabel }}s this period</span>
                @if($renewalDate)<span style="color:var(--db-text-muted)">resets {{ $renewalDate->format('M j') }}</span>@endif
              </div>
              <div class="bill-bar" style="height:5px"><div class="bill-bar-fill" style="width:{{ $usagePct }}%;background:{{ $usagePct >= 100 ? '#ef4444' : '#f59e0b' }}"></div></div>
              @if($usagePct >= 100)
              <div class="bill-note" style="color:#ef4444">Monthly limit reached — emails queue until renewal</div>
              @else
              <div class="bill-note" style="color:#f59e0b">Approaching your plan limit · renews {{ $renewalDate?->format('M j') }}</div>
              @endif
            </div>
            @else
            <p style="font-size:13px;color:var(--db-text-muted)">
              @if($unitLimit) {{ number_format($unitUsed) }} / {{ number_format($unitLimit) }} {{ $unitLabel }}s used this period
              @else {{ number_format($unitUsed) }} {{ $unitLabel }}s processed this period · unlimited plan
              @endif
            </p>
            @endif
            <div style="margin-top:12px"><a href="{{ route('billing.portal') }}" class="mem-btn-secondary">Manage Subscription →</a></div>
          @endif

          @if($needsConversion && $depTiers->isNotEmpty())
          <div style="{{ ($isExhausted || $isCanceled) ? 'margin-top:16px' : 'margin-top:8px' }}">
            @if($status === 'trial')
            <p style="font-size:12px;color:var(--db-text-muted);margin-bottom:10px">Choose a plan — your trial emails carry over, no charge until you subscribe:</p>
            @elseif($isCanceled)
            <p style="font-size:12px;color:var(--db-text-muted);margin-bottom:10px">Select a plan to reactivate:</p>
            @else
            <p style="font-size:12px;color:var(--db-text-muted);margin-bottom:10px">Pick a plan to resume:</p>
            @endif

            <div class="bill-plans">
              @foreach($depTiers as $tier)
              @php
                $isEnterprise = $tier->plan_slug === 'enterprise';
                $isPro        = $tier->plan_slug === 'pro';
                $highlights   = json_decode($tier->plan_highlights ?? '[]', true);
              @endphp
              <div class="bill-plan {{ $isPro ? 'pro' : '' }}">
                @if($isPro)<span class="bill-plan-tag">RECOMMENDED</span>@endif
                <div>
                  <div class="bill-plan-name">{{ $tier->display_name }}</div>
                  <div class="bill-plan-price">
                    @if($isEnterprise) Custom
                    @else ${{ number_format($tier->monthly_flat_rate, 0) }}<span>/mo</span>
                    @endif
                  </div>
                  <div class="bill-plan-cap">
                    @if($tier->transaction_limit) {{ number_format($tier->transaction_limit) }} emails/mo
                    @else Unlimited emails
                    @endif
                  </div>
                </div>
                <ul class="bill-plan-list">
                  @foreach($highlights as $h)
                  <li><span>✓</span> {{ $h }}</li>
                  @endforeach
                </ul>
                @if($isEnterprise)
                <a href="mailto:hello@unit.report?subject=AVA Enterprise Enquiry" class="bill-plan-cta">Contact Us →</a>
                @elseif($isCanceled)
                <form method="POST" action="{{ route('billing.reactivate', $dep->id) }}">
                  @csrf
                  <input type="hidden" name="plan" value="{{ $tier->plan_slug }}">
                  <button type="submit" class="bill-plan-cta {{ $isPro ? 'primary' : '' }}" style="width:100%">Reactivate {{ ucfirst($tier->plan_slug) }} →</button>
                </form>
                @else
                <a href="{{ route('billing.checkout', $dep->id) }}?plan={{ $tier->plan_slug }}" class="bill-plan-cta {{ $isPro ? 'primary' : '' }}">Get {{ ucfirst($tier->plan_slug) }} →</a>
                @endif
              </div>
              @endforeach
            </div>
            <p style="font-size:11px;color:var(--db-text-muted);margin-top:10px">All plans billed monthly · cancel anytime · one Stripe invoice for all your workers</p>
          </div>
          @endif
        </div>
      </div>
      @empty
      <div class="bill-empty"><p>No workers deployed yet.</p></div>
      @endforelse

      @if($invoices->isNotEmpty())
      <div class="bill-card" style="margin-top:8px">
        <div class="bill-card-head">
          <div>
            <div class="bill-name">Invoice History</div>
            <div class="bill-meta">All worker subscriptions appear on one monthly invoice</div>
          </div>
          @if(auth()->user()->isAdmin())
          <span class="bill-admin-tag">Admin: void clears invoice in Stripe</span>
          @endif
        </div>

        @foreach($invoices as $invoice)
        @php
          $isDue    = !$invoice->paid && ($invoice->asStripeInvoice()->status ?? '') !== 'void';
          $isVoided = ($invoice->asStripeInvoice()->status ?? '') === 'void';
        @endphp
        <div class="bill-inv-row">
          <div>
            <div class="bill-inv-date">{{ $invoice->date()->format('F j, Y') }}</div>
            <div class="bill-inv-amt">${{ number_format($invoice->rawTotal() / 100, 2) }}</div>
          </div>
          <div class="bill-inv-actions">
            @if($isVoided)
              <span class="bill-inv-badge" style="background:var(--db-chip);color:var(--db-text-muted)">Voided</span>
            @elseif($invoice->paid)
              <span class="bill-inv-badge" style="background:rgba(34,197,94,.1);color:#22c55e;border:1px solid rgba(34,197,94,.25)">Paid</span>
            @else
              <span class="bill-inv-badge" style="background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.25)">Due</span>
            @endif
            <a href="{{ route('billing.invoice', $invoice->id) }}" class="bill-inv-link">Download PDF →</a>
            @if(auth()->user()->isAdmin() && $isDue)
            <form method="POST" action="{{ route('admin.invoices.void', $invoice->id) }}">
              @csrf
              <input type="hidden" name="user_id" value="{{ auth()->id() }}">
              <button type="submit" onclick="return confirm('Void invoice {{ $invoice->id }} for ${{ number_format($invoice->rawTotal() / 100, 2) }}?')" class="bill-void-btn">Void</button>
            </form>
            @endif
          </div>
        </div>
        @endforeach
      </div>
      @endif

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

(function () {
  var params = new URLSearchParams(window.location.search);
  var pickId = params.get('pick');
  if (pickId) {
    var el = document.getElementById('dep-' + pickId);
    if (el) setTimeout(function () { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 150);
  }
})();
</script>
</body>
</html>

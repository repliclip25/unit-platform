<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Refer & Earn — UNIT</title>
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

/* ── SHELL (identical to /app/billing, /app/memory, /desk/{slug}, etc.) ── */
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
.mem-card-area{display:grid;grid-template-columns:1fr;margin:12px 12px 12px 0;background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)}
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

/* ── CONTENT ── */
.mem-main{overflow-y:auto;padding:28px 32px 60px}
.mem-wrap{max-width:720px;margin:0 auto}

.rf-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.rf-sub{font-size:12.5px;color:var(--db-text-muted);margin-top:2px;margin-bottom:20px}

.rf-card{background:var(--db-bg);border:1px solid var(--db-border);border-radius:16px;padding:20px;margin-bottom:16px}

.rf-stats{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px}
.rf-stat{background:var(--db-bg);border:1px solid var(--db-border);border-radius:16px;padding:18px 14px;text-align:center}
.rf-stat-num{font-size:1.7rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.rf-stat-label{font-size:10px;color:var(--db-text-muted);text-transform:uppercase;letter-spacing:.06em;margin-top:4px}

.rf-progress-track{width:100%;height:8px;border-radius:99px;background:var(--db-chip);overflow:hidden}
.rf-progress-fill{height:100%;border-radius:99px;background:#F5C518}

.rf-tier-gold{display:flex;align-items:center;gap:12px}

.rf-link-label{font-size:12.5px;color:var(--db-text-muted);margin-bottom:10px;font-weight:600}
.rf-link-row{display:flex;align-items:center;gap:10px}
.rf-link-box{flex:1;display:flex;align-items:center;min-width:0;padding:11px 14px;border-radius:10px;border:1px solid var(--db-border);background:var(--db-card)}
.rf-link-text{font-size:12.5px;font-family:monospace;color:var(--db-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rf-copy-btn{flex-shrink:0;font-size:12.5px;font-weight:700;padding:11px 18px;border-radius:10px;border:none;background:var(--db-invert-bg);color:var(--db-invert-text);cursor:pointer;font-family:inherit}
.rf-code{font-size:11px;color:var(--db-text-muted);margin-top:8px}
.rf-code span{font-family:monospace;color:var(--db-text)}

.rf-card-title{font-size:13.5px;font-weight:700;color:var(--db-text);margin-bottom:14px}

.rf-share-item{display:flex;align-items:center;gap:14px;padding:12px;border-radius:10px;border:1px solid var(--db-border);text-decoration:none;margin-bottom:8px}
.rf-share-item:last-child{margin-bottom:0}
.rf-share-item:hover{background:var(--db-chip)}
.rf-share-icon{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.rf-share-text{flex:1;min-width:0}
.rf-share-title{font-size:12.5px;font-weight:600;color:var(--db-text)}
.rf-share-sub{font-size:11px;color:var(--db-text-muted);margin-top:1px}
.rf-share-item svg.rf-chev{stroke:var(--db-text-muted);flex-shrink:0}

.rf-table{width:100%;font-size:12.5px;border-collapse:collapse}
.rf-table th{text-align:left;padding:10px 4px;color:var(--db-text-muted);font-size:10px;text-transform:uppercase;letter-spacing:.05em;font-weight:600;border-bottom:1px solid var(--db-border)}
.rf-table td{padding:10px 4px;border-bottom:1px solid var(--db-border);color:var(--db-text)}
.rf-table tr:last-child td{border-bottom:none}
.rf-status-badge{font-size:10.5px;padding:2px 8px;border-radius:99px;font-weight:600}
.rf-empty{text-align:center;padding:28px 0;font-size:12.5px;color:var(--db-text-muted)}

.rf-step{display:flex;gap:12px;margin-bottom:14px}
.rf-step:last-child{margin-bottom:0}
.rf-step-num{width:24px;height:24px;border-radius:50%;background:var(--db-chip);color:var(--db-text);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0}
.rf-step-title{font-size:12.5px;font-weight:600;color:var(--db-text)}
.rf-step-sub{font-size:11px;color:var(--db-text-muted);margin-top:2px}

.rf-partner-strip{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.rf-partner-strip h3{font-size:14px;font-weight:700;color:var(--db-text);margin-bottom:3px}
.rf-partner-strip p{font-size:12px;color:var(--db-text-muted)}
.rf-partner-cta{flex-shrink:0;font-size:12.5px;font-weight:700;padding:9px 18px;border-radius:9px;background:var(--db-invert-bg);color:var(--db-invert-text);text-decoration:none;white-space:nowrap}

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
  .rf-stats{grid-template-columns:1fr}
  .rf-partner-strip{flex-direction:column;align-items:flex-start}
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
      <p>Your referral link and credit balance are private to your account.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      <div class="rf-h1">Refer & Earn</div>
      <div class="rf-sub">Invite colleagues to UNIT. Earn $25 credit when they go paid.</div>

      <div class="rf-stats">
        <div class="rf-stat"><div class="rf-stat-num">{{ $referral->signups }}</div><div class="rf-stat-label">Signed up</div></div>
        <div class="rf-stat"><div class="rf-stat-num" style="color:#F5C518">{{ $referral->converted }}</div><div class="rf-stat-label">Converted</div></div>
        <div class="rf-stat"><div class="rf-stat-num">${{ number_format($referral->balance, 0) }}</div><div class="rf-stat-label">Credit balance</div></div>
      </div>

      @if($referral->nextTier)
      <div class="rf-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
          <span style="font-size:12.5px;color:var(--db-text-muted)">Progress to <strong style="color:var(--db-text)">{{ $referral->tierLabel }}</strong></span>
          <span style="font-size:12px;color:var(--db-text-muted)">{{ $referral->converted }} / {{ $referral->nextTier }} conversions</span>
        </div>
        <div class="rf-progress-track"><div class="rf-progress-fill" style="width:{{ $referral->tierPct }}%"></div></div>
      </div>
      @else
      <div class="rf-card rf-tier-gold">
        <span style="font-size:26px">🏆</span>
        <div>
          <div style="font-size:13px;font-weight:700;color:#F5C518">Gold Referrer</div>
          <div style="font-size:12px;color:var(--db-text-muted)">10+ conversions — you're in the top tier.</div>
        </div>
      </div>
      @endif

      <div class="rf-card">
        <div class="rf-link-label">Your referral link</div>
        <div class="rf-link-row">
          <div class="rf-link-box"><span class="rf-link-text">{{ $referralUrl }}</span></div>
          <button type="button" class="rf-copy-btn" id="rf-copy-btn" data-url="{{ $referralUrl }}">Copy Link</button>
        </div>
        <div class="rf-code">Code: <span>{{ $referralCode }}</span></div>
      </div>

      <div class="rf-card">
        <div class="rf-card-title">Best ways to refer</div>

        <a href="mailto:?subject=Tool that automates license renewals&body=Hey%2C%0A%0AI've been using UNIT Platform to automate our license renewal workflow — it handles reading the email%2C looking up the client%2C and drafting the response automatically. Saves a ton of time.%0A%0AThought you might want to try it. Use my link and you'll get double the usual free trial%3A%0A%0A{{ $referralUrl }}%0A%0A" class="rf-share-item">
          <div class="rf-share-icon" style="background:var(--db-chip)">
            <svg width="16" height="16" fill="none" stroke="var(--db-text)" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <div class="rf-share-text"><div class="rf-share-title">Email a colleague</div><div class="rf-share-sub">Works best — personal and specific to their workflow.</div></div>
          <svg class="rf-chev" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>

        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($referralUrl) }}" target="_blank" class="rf-share-item">
          <div class="rf-share-icon" style="background:rgba(10,102,194,.12)">
            <svg width="16" height="16" fill="#0a66c2" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
          </div>
          <div class="rf-share-text"><div class="rf-share-title">Share on LinkedIn</div><div class="rf-share-sub">Great for reaching procurement and compliance teams.</div></div>
          <svg class="rf-chev" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>

        <a href="https://twitter.com/intent/tweet?text={{ urlencode('I use UNIT Platform to automate license renewal workflows — it reads the email, looks up the client, and drafts the response. Use my link for double the free trial: ' . $referralUrl) }}" target="_blank" class="rf-share-item">
          <div class="rf-share-icon" style="background:var(--db-chip)">
            <svg width="16" height="16" fill="var(--db-text)" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.261 5.632zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          </div>
          <div class="rf-share-text"><div class="rf-share-title">Post on X</div><div class="rf-share-sub">Good for visibility in your professional network.</div></div>
          <svg class="rf-chev" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
      </div>

      <div class="rf-card">
        <div class="rf-card-title">Recent activity</div>
        @if($credits->count() > 0)
        <table class="rf-table">
          <thead><tr><th>Referred user</th><th>Status</th><th style="text-align:right">Credit</th><th style="text-align:right">Date</th></tr></thead>
          <tbody>
            @foreach($credits as $credit)
            <tr>
              <td>{{ $credit->referred_email ?? '—' }}</td>
              <td>
                @if($credit->event === 'paid_conversion')
                  <span class="rf-status-badge" style="background:rgba(34,197,94,.15);color:#22c55e">Converted</span>
                @elseif($credit->event === 'signup')
                  <span class="rf-status-badge" style="background:rgba(245,197,24,.15);color:#F5C518">Signed up</span>
                @else
                  <span class="rf-status-badge" style="background:var(--db-chip);color:var(--db-text-muted)">{{ ucfirst($credit->event) }}</span>
                @endif
              </td>
              <td style="text-align:right;font-family:monospace">${{ number_format($credit->credit_usd ?? 0, 0) }}</td>
              <td style="text-align:right;color:var(--db-text-muted)">{{ \Carbon\Carbon::parse($credit->created_at)->format('M j, Y') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @else
        <div class="rf-empty">No referrals yet. Share your link to get started.</div>
        @endif
      </div>

      <div class="rf-card">
        <div class="rf-card-title">How it works</div>
        <div class="rf-step">
          <div class="rf-step-num">1</div>
          <div><div class="rf-step-title">Share your link</div><div class="rf-step-sub">Send it to anyone who does license renewal or compliance work.</div></div>
        </div>
        <div class="rf-step">
          <div class="rf-step-num">2</div>
          <div><div class="rf-step-title">They sign up and get 20 free transactions</div><div class="rf-step-sub">Double the usual free trial — a meaningful incentive to try it.</div></div>
        </div>
        <div class="rf-step">
          <div class="rf-step-num">3</div>
          <div><div class="rf-step-title">You earn $25 credit when they subscribe</div><div class="rf-step-sub">Applied to your UNIT account automatically. No cap on earnings.</div></div>
        </div>
      </div>

      {{-- Cross-link to the creator/influencer program — different audience,
           worth surfacing here since both are "share UNIT, get paid" programs --}}
      <div class="rf-card rf-partner-strip">
        <div>
          <h3>Have an audience? Check out the Partner Program.</h3>
          <p>Creators and consultants earn 20–30% recurring commission — a different model built for reaching a wider audience, not just colleagues.</p>
        </div>
        <a href="{{ route('influencer.apply') }}" class="rf-partner-cta">Partner Program →</a>
      </div>

    </div>
  </main>
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

var copyBtn = document.getElementById('rf-copy-btn');
copyBtn.addEventListener('click', function () {
  navigator.clipboard.writeText(copyBtn.dataset.url).then(function () {
    var original = copyBtn.textContent;
    copyBtn.textContent = 'Copied ✓';
    setTimeout(function () { copyBtn.textContent = original; }, 2500);
  });
});
</script>
</body>
</html>

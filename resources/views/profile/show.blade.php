<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>My Profile — UNIT</title>
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

  /* ── Same tokens as layouts/app.blade.php — the profile content below (.pf-*)
     was built against these, kept as-is rather than rewritten to --db-* ── */
  --bg-base:#000000; --bg-surface:#111111; --bg-raised:#1a1a1a; --bg-card:#212121;
  --border:rgba(255,255,255,.12); --border-soft:rgba(255,255,255,.06); --border-subtle:rgba(255,255,255,.10);
  --text-primary:#ffffff; --text-secondary:#cccccc; --text-muted:#999999; --text-faint:#555555;
  --accent:#142C74; --accent-rgb:20,44,116; --accent-dark:#0e2260; --accent-text:#ffffff;
  --badge-fast-bg:rgba(6,182,212,.15); --badge-fast-text:#67e8f9;
  --badge-balanced-bg:rgba(20,44,116,.18); --badge-balanced-text:#93aee8;
  --badge-powerful-bg:rgba(168,85,247,.15); --badge-powerful-text:#c4b5fd;
  --badge-reasoning-bg:rgba(239,68,68,.15); --badge-reasoning-text:#fca5a5;
  --badge-platform-bg:rgba(16,185,129,.12); --badge-platform-text:#6ee7b7;
  --badge-yourkey-bg:rgba(20,44,116,.15); --badge-yourkey-text:#93aee8;
  --badge-custom-bg:rgba(156,163,175,.1); --badge-custom-text:#9ca3af;
}
[data-theme="light"]{
  --db-bg:#F4F3F1; --db-card:#ffffff; --db-text:#0D0D0D; --db-text-muted:#6B7280;
  --db-border:#E5E7EB; --db-chip:#ECEAE6;
  --db-invert-bg:#0D0D0D; --db-invert-text:#ffffff;

  --bg-base:#f9f9f7; --bg-surface:#ffffff; --bg-raised:#f0f0ee; --bg-card:#ffffff;
  --border:#e2e2e0; --border-soft:rgba(0,0,0,.05); --border-subtle:#e8e8e6;
  --text-primary:#000000; --text-secondary:#1a1a1a; --text-muted:#555555; --text-faint:#999999;
  --accent:#142C74; --accent-rgb:20,44,116; --accent-dark:#0e2260; --accent-text:#ffffff;
  --badge-fast-bg:rgba(6,182,212,.12); --badge-fast-text:#0369a1;
  --badge-balanced-bg:rgba(20,44,116,.10); --badge-balanced-text:#142C74;
  --badge-powerful-bg:rgba(124,58,237,.12); --badge-powerful-text:#6d28d9;
  --badge-reasoning-bg:rgba(239,68,68,.12); --badge-reasoning-text:#b91c1c;
  --badge-platform-bg:rgba(5,150,105,.10); --badge-platform-text:#047857;
  --badge-yourkey-bg:rgba(20,44,116,.10); --badge-yourkey-text:#142C74;
  --badge-custom-bg:rgba(107,114,128,.10); --badge-custom-text:#4b5563;
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

/* ── Profile content (unchanged from previous x-app-layout version) ── */
.pf-input {
    width:100%;box-sizing:border-box;background:var(--bg-raised);color:var(--text-primary);
    font-size:13px;border:1px solid var(--border);border-radius:9px;padding:9px 12px;
    outline:none;transition:border-color .15s;font-family:inherit;
}
.pf-input:focus   { border-color:rgba(var(--accent-rgb),.5); }
.pf-input:disabled{ opacity:.5;cursor:default; }
.pf-label {
    font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
    color:var(--text-muted);margin-bottom:6px;display:block;
}
.pf-btn {
    padding:9px 18px;border-radius:9px;border:none;font-size:13px;font-weight:700;
    cursor:pointer;font-family:inherit;transition:opacity .15s;
}
.pf-btn:hover { opacity:.88; }
.pf-btn-primary { background:var(--accent);color:#ffffff; }
.pf-btn-ghost   { background:transparent;border:1px solid var(--border);color:var(--text-secondary); }
.pf-btn-danger  { background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);color:#f87171; }
.pf-card        { background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:16px; }
.pf-card-head   { padding:16px 20px;border-bottom:1px solid var(--border); }
.pf-card-head h2{ font-size:13px;font-weight:700;color:var(--text-primary); }
.pf-card-head p { font-size:12px;color:var(--text-muted);margin-top:2px; }
.pf-card-body   { padding:20px; }
.pf-field       { margin-bottom:16px; }
.pf-field:last-child{ margin-bottom:0; }
.pf-row {
    display:flex;align-items:center;justify-content:space-between;
    gap:12px;padding:11px 0;border-bottom:1px solid var(--border-subtle);
}
.pf-row:last-child{ border-bottom:none;padding-bottom:0; }
.pf-divider     { border:none;border-top:1px solid var(--border-subtle);margin:18px 0; }

/* Collapsible sections */
.pf-accordion-body { overflow:hidden; transition:max-height .25s ease, padding .2s ease; }
.pf-accordion-body.closed { max-height:0 !important; padding-top:0; padding-bottom:0; }
.pf-chevron { transition:transform .2s ease; display:inline-block; }
.pf-accordion-body.closed ~ * .pf-chevron,
.pf-chevron.open { transform:rotate(180deg); }

/* Grid */
.pf-grid { display:grid;grid-template-columns:1fr 1fr;gap:20px; }
@media(max-width:768px) { .pf-grid { grid-template-columns:1fr; } }

/* Hero */
.pf-hero { display:flex;flex-wrap:wrap;align-items:flex-start;gap:16px;padding:20px;background:var(--bg-card);border:1px solid var(--border);border-radius:16px;margin-bottom:16px; }
.pf-hero-left { display:flex;align-items:flex-start;gap:14px;flex:1;min-width:220px; }

/* Stat strip */
.pf-stats-strip { display:flex;gap:12px;overflow-x:auto;padding-bottom:4px;margin-bottom:16px;scrollbar-width:none; }
.pf-stats-strip::-webkit-scrollbar { display:none; }
.pf-stat-card {
    flex-shrink:0;min-width:160px;padding:14px 16px;background:var(--bg-card);
    border:1px solid var(--border);border-radius:14px;
}

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
$hasPassword = !empty(auth()->user()->password);
$initials    = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
$tokenFmt = $tokenTotal >= 1000000 ? number_format($tokenTotal/1000000,1).'M' : number_format($tokenTotal);
$sidebarLinks = [
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('workers.memory','ava'), false],
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
      <p>Your account, your team, your data.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

{{-- Pending deletion banner --}}
@if($user->deletion_requested_at)
@php $deletionDate = \Carbon\Carbon::parse($user->deletion_requested_at)->addDays(30); @endphp
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px">
    <div>
        <p style="font-size:14px;font-weight:700;color:#f87171;margin-bottom:2px">⚠ Account scheduled for deletion</p>
        <p style="font-size:12px;color:rgba(248,113,113,.7)">All data deleted on <strong>{{ $deletionDate->format('F j, Y') }}</strong> ({{ $deletionDate->diffForHumans() }}).</p>
    </div>
    <form method="POST" action="{{ route('profile.cancel-deletion') }}">
        @csrf
        <button type="submit" style="padding:8px 16px;border-radius:9px;border:none;background:#ef4444;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">Cancel deletion</button>
    </form>
</div>
@endif

@if(session('success'))
<div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80;border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;margin-bottom:14px">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171;border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;margin-bottom:14px">{{ session('error') }}</div>
@endif

{{-- ── Profile hero ── --}}
<div class="pf-hero">
    <div class="pf-hero-left">
        @if($user->avatar)
            <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                 style="width:58px;height:58px;border-radius:14px;object-fit:cover;border:2px solid var(--border);flex-shrink:0">
        @else
            <div style="width:58px;height:58px;border-radius:14px;background:linear-gradient(135deg,rgba(var(--accent-rgb),.15),rgba(var(--accent-rgb),.05));border:2px solid rgba(var(--accent-rgb),.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:800;font-size:20px;color:var(--accent-text)">{{ $initials }}</div>
        @endif
        <div style="flex:1;min-width:0">
            <p style="font-size:18px;font-weight:800;color:var(--text-primary);line-height:1.2;word-break:break-word">{{ $user->name }}</p>
            <p style="font-size:13px;color:var(--text-muted);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $user->email }}</p>
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:8px">
                <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80">● {{ ucfirst($user->role) }}</span>
                <span style="font-size:11px;color:var(--text-faint)">Member since {{ \Carbon\Carbon::parse($user->created_at)->format('M Y') }}</span>
                @if($user->google_id)
                <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--text-muted)">G Google</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Referral code --}}
    @if($referralUrl)
    <div style="flex-shrink:0">
        <p style="font-size:10px;color:var(--text-faint);margin-bottom:5px;text-transform:uppercase;letter-spacing:.05em">Referral link</p>
        <div style="display:flex;align-items:center;gap:6px">
            <code style="font-size:12px;font-weight:700;color:var(--accent-text);background:rgba(var(--accent-rgb),.08);border:1px solid rgba(var(--accent-rgb),.2);padding:4px 10px;border-radius:7px;letter-spacing:.03em">{{ $user->referral_code }}</code>
            <button id="copy-ref-btn"
                    onclick="navigator.clipboard.writeText('{{ $referralUrl }}').then(()=>{ this.textContent='✓ Copied';setTimeout(()=>this.textContent='Share link',1800) })"
                    style="font-size:11px;font-weight:600;padding:5px 11px;border-radius:7px;border:1px solid var(--border);background:transparent;color:var(--text-muted);cursor:pointer;white-space:nowrap">
                Share link
            </button>
        </div>
    </div>
    @endif
</div>

{{-- ── Value Clock strip ── --}}
<div class="pf-stats-strip" id="clock-strip">
    @foreach($clockCards as $i => $card)
    @php $tipId = 'clock-tip-' . $i; $iconId = 'clock-icon-' . $i; @endphp
    <div class="pf-stat-card">
        {{-- Header: OWNER · LABEL + (!) --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:6px;margin-bottom:4px">
            <p style="font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-faint);line-height:1.3">
                {{ $card['owner'] }} · {{ $card['label'] }}
            </p>
            <button id="{{ $iconId }}" style="flex-shrink:0;background:none;border:none;cursor:pointer;padding:0;color:var(--text-faint);font-size:11px;line-height:1;margin-top:1px" title="How this is calculated">ⓘ</button>
        </div>
        <p style="font-size:30px;font-weight:900;line-height:1;letter-spacing:-.03em;color:var(--accent-text)">{{ $card['value'] }}</p>
        <p style="font-size:11px;color:var(--text-faint);margin-top:3px">{{ $card['subtitle'] }}</p>

        {{-- Fixed tooltip --}}
        <div id="{{ $tipId }}"
             style="display:none;position:fixed;z-index:9999;width:240px;pointer-events:none"
             class="shadow-2xl">
            <div style="background:var(--bg-raised);border:1px solid var(--border);border-radius:12px;padding:12px">
                <p style="font-size:11px;font-weight:700;color:var(--text-primary);margin-bottom:4px">How this is calculated</p>
                <p style="font-size:11px;font-family:monospace;color:var(--accent-text);margin-bottom:6px">{{ $card['formula'] }}</p>
                <p style="font-size:11px;color:var(--text-muted);line-height:1.5">{{ $card['source'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>

<script>
(function(){
    @foreach($clockCards as $i => $card)
    (function(){
        var icon = document.getElementById('clock-icon-{{ $i }}');
        var tip  = document.getElementById('clock-tip-{{ $i }}');
        if (!icon || !tip) return;
        icon.addEventListener('mouseenter', function(){
            var r = icon.getBoundingClientRect();
            tip.style.display = 'block';
            tip.style.left = Math.max(8, r.left) + 'px';
            tip.style.top  = (r.bottom + 8) + 'px';
            // keep within viewport right edge
            var tw = 240;
            if (r.left + tw > window.innerWidth - 8) {
                tip.style.left = Math.max(8, window.innerWidth - tw - 8) + 'px';
            }
        });
        icon.addEventListener('mouseleave', function(){ tip.style.display = 'none'; });
    })();
    @endforeach
})();
</script>

{{-- ── Main grid: Team + Connected (left), Collapsible settings (right) ── --}}
<div class="pf-grid">

{{-- LEFT: My Team + Connected Accounts --}}
<div>

    {{-- My Team --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <h2>My Team</h2>
            <p>{{ $deployments->count() }} employee{{ $deployments->count() !== 1 ? 's' : '' }} hired</p>
        </div>
        <div class="pf-card-body">
            @forelse($deployments as $dep)
            @php
                $contract = $contracts->get($dep->worker_slug);
                $employee = $contract ? $contract->employee() : [];
                $isActive = $dep->status === 'active';
            @endphp
            <div class="pf-row" style="align-items:flex-start">
                <div style="width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;flex-shrink:0;background:rgba(var(--accent-rgb),.1);color:var(--accent-text)">
                    {{ strtoupper(substr($dep->worker_slug, 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <p style="font-size:13px;font-weight:700;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $dep->name }}</p>
                    <p style="font-size:11px;color:var(--text-muted);margin-top:1px">{{ $employee['title'] ?? strtoupper($dep->worker_slug) }}</p>
                    <p style="font-size:10px;font-weight:600;margin-top:4px;color:{{ $isActive ? '#4ade80' : '#fbbf24' }}">
                        ● {{ ucfirst($dep->status) }}
                        <span style="font-weight:400;color:var(--text-faint)"> · since {{ \Carbon\Carbon::parse($dep->created_at)->format('M j, Y') }}</span>
                    </p>
                </div>
                <a href="{{ route('workers.show', $dep->worker_slug) }}"
                   style="font-size:11px;font-weight:700;padding:6px 12px;border-radius:8px;background:var(--bg-raised);border:1px solid var(--border);color:var(--text-secondary);text-decoration:none;white-space:nowrap;flex-shrink:0;transition:border-color .15s"
                   onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
                    Open →
                </a>
            </div>
            @empty
            <div style="text-align:center;padding:20px 0">
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:10px">No employees hired yet.</p>
                <a href="{{ url('/workers') }}"
                   style="font-size:12px;font-weight:700;padding:8px 16px;border-radius:9px;background:var(--accent);color:#ffffff;text-decoration:none">
                    Hire your first employee →
                </a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Connected Accounts --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <h2>Connected Accounts</h2>
            <p>OAuth credentials and integrations</p>
        </div>
        <div class="pf-card-body">

            <div class="pf-row">
                <div style="display:flex;align-items:center;gap:10px;min-width:0">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.06);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">G</div>
                    <div style="min-width:0">
                        <p style="font-size:13px;font-weight:600;color:var(--text-primary)">Google Account</p>
                        <p style="font-size:11px;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $user->email }}</p>
                    </div>
                </div>
                <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;flex-shrink:0;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80">
                    {{ $user->google_id ? 'Linked' : 'Not linked' }}
                </span>
            </div>

            @forelse($gmailCredentials as $cred)
            @php
                $watchOk    = $cred->watch_active && $cred->watch_expires_at && \Carbon\Carbon::parse($cred->watch_expires_at)->isFuture();
                $usedByDeps = $deploymentCredentials->get($cred->id, collect());
                // Build label: "AVA — Gmail Access" from the workers using this inbox
                $workerLabels = $usedByDeps->map(function($dc) use ($deployments, $contracts) {
                    $dep = $deployments->firstWhere('id', $dc->deployment_id ?? null);
                    if (!$dep) return null;
                    $contract = $contracts->get($dep->worker_slug);
                    $name = $contract ? ($contract->employee()['name'] ?? strtoupper($dep->worker_slug)) : strtoupper($dep->worker_slug);
                    return $name . ' — Gmail Access';
                })->filter()->unique()->values();
                $connLabel = $workerLabels->isNotEmpty() ? $workerLabels->implode(', ') : 'Gmail Access';
            @endphp
            <div class="pf-row">
                <div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(241,211,98,.08);border:1px solid rgba(241,211,98,.15);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;color:var(--accent-text)">✉</div>
                    <div style="min-width:0">
                        <p style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $connLabel }}</p>
                        <p style="font-size:11px;color:var(--text-muted);margin-top:1px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cred->gmail_address }}</p>
                        <span style="font-size:10px;font-weight:600;color:{{ $watchOk ? '#4ade80' : '#fbbf24' }}">{{ $watchOk ? '● Active' : '⚠ Inactive' }}</span>
                    </div>
                </div>
                @if(!$watchOk)
                <a href="{{ route('ava.gmail.authorize') }}"
                   style="font-size:11px;font-weight:600;padding:5px 10px;border-radius:7px;background:rgba(241,211,98,.1);border:1px solid rgba(241,211,98,.2);color:var(--accent-text);text-decoration:none;white-space:nowrap;flex-shrink:0">
                    Reconnect
                </a>
                @endif
            </div>
            @empty
            <div style="text-align:center;padding:12px 0">
                <p style="font-size:12px;color:var(--text-muted);margin-bottom:8px">No Gmail inboxes connected.</p>
                <a href="{{ route('ava.gmail.authorize') }}" style="font-size:12px;font-weight:600;color:var(--accent-text);text-decoration:none">+ Connect Gmail →</a>
            </div>
            @endforelse

            <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border-subtle)">
                <div class="pf-row">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;color:var(--text-faint)">in</div>
                        <div><p style="font-size:13px;font-weight:600;color:var(--text-muted)">LinkedIn</p><p style="font-size:11px;color:var(--text-faint)">Required for NUX</p></div>
                    </div>
                    <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--text-faint);white-space:nowrap">Coming soon</span>
                </div>
                <div class="pf-row">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;color:var(--text-faint)">𝕏</div>
                        <div><p style="font-size:13px;font-weight:600;color:var(--text-muted)">X (Twitter)</p><p style="font-size:11px;color:var(--text-faint)">Required for NUX</p></div>
                    </div>
                    <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--text-faint);white-space:nowrap">Coming soon</span>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- RIGHT: Collapsible Account / Security / Danger --}}
<div>

    {{-- Account --}}
    <div class="pf-card">
        <button onclick="toggleAccordion('acc-account', this)"
                style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:none;border:none;cursor:pointer;text-align:left">
            <div>
                <p style="font-size:13px;font-weight:700;color:var(--text-primary)">Account</p>
                <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Name and email address</p>
            </div>
            <span class="pf-chevron" style="color:var(--text-faint);font-size:14px">▾</span>
        </button>
        <div id="acc-account" class="pf-accordion-body closed" style="max-height:600px">
            <div style="padding:0 20px 20px;border-top:1px solid var(--border)">
                <div style="height:16px"></div>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')
                    <div class="pf-field">
                        <label class="pf-label" for="name">Full name</label>
                        <input id="name" name="name" type="text" class="pf-input" value="{{ old('name', $user->name) }}" required>
                        @error('name')<p style="color:#f87171;font-size:11px;margin-top:4px">{{ $message }}</p>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Email address</label>
                        <input type="email" class="pf-input" value="{{ $user->email }}" disabled>
                        <p style="font-size:11px;color:var(--text-faint);margin-top:4px">
                            @if($user->google_id) Managed by your Google account. @else Contact support to change your email. @endif
                        </p>
                    </div>
                    <button type="submit" class="pf-btn pf-btn-primary">Save changes</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Security --}}
    <div class="pf-card">
        <button onclick="toggleAccordion('acc-security', this)"
                style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:none;border:none;cursor:pointer;text-align:left">
            <div>
                <p style="font-size:13px;font-weight:700;color:var(--text-primary)">Security</p>
                <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Password and active sessions</p>
            </div>
            <span class="pf-chevron" style="color:var(--text-faint);font-size:14px">▾</span>
        </button>
        <div id="acc-security" class="pf-accordion-body closed" style="max-height:800px">
            <div style="padding:0 20px 20px;border-top:1px solid var(--border)">
                <div style="height:16px"></div>
                @if($hasPassword)
                <p style="font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:12px">Change password</p>
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf @method('PUT')
                    <div class="pf-field">
                        <label class="pf-label" for="current_password">Current password</label>
                        <input id="current_password" name="current_password" type="password" class="pf-input" autocomplete="current-password" required>
                        @error('current_password')<p style="color:#f87171;font-size:11px;margin-top:4px">{{ $message }}</p>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="password">New password</label>
                        <input id="password" name="password" type="password" class="pf-input" autocomplete="new-password" required>
                        @error('password')<p style="color:#f87171;font-size:11px;margin-top:4px">{{ $message }}</p>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="password_confirmation">Confirm new password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="pf-input" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="pf-btn pf-btn-primary">Update password</button>
                </form>
                <hr class="pf-divider">
                @else
                <div style="background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;padding:12px 14px;margin-bottom:16px">
                    <p style="font-size:12px;color:var(--text-muted)">You signed in with Google — no password set on this account.</p>
                </div>
                @endif

                <div class="pf-row">
                    <div><p style="font-size:13px;font-weight:600;color:var(--text-primary)">Two-factor auth</p><p style="font-size:11px;color:var(--text-muted);margin-top:2px">Authenticator app (TOTP)</p></div>
                    <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--text-muted);white-space:nowrap">Coming soon</span>
                </div>

                @if($sessions->isNotEmpty())
                <p style="font-size:12px;font-weight:700;color:var(--text-secondary);margin:16px 0 8px">Active sessions</p>
                @foreach($sessions as $sess)
                <div class="pf-row">
                    <div style="min-width:0">
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:5px;margin-bottom:2px">
                            <p style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $sess->device['browser'] }} on {{ $sess->device['os'] }}</p>
                            @if($sess->is_current)
                            <span style="font-size:9px;font-weight:700;padding:2px 7px;border-radius:20px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.25);color:#4ade80;white-space:nowrap">This device</span>
                            @endif
                        </div>
                        <p style="font-size:11px;color:var(--text-faint)">{{ $sess->ip_address ?? 'Unknown IP' }} · {{ $sess->last_active_at->diffForHumans() }}</p>
                    </div>
                    @if(!$sess->is_current)
                    <form method="POST" action="{{ route('profile.session.revoke', $sess->id) }}" style="flex-shrink:0">
                        @csrf @method('DELETE')
                        <button type="submit" class="pf-btn pf-btn-ghost" style="font-size:11px;padding:6px 12px">End</button>
                    </form>
                    @endif
                </div>
                @endforeach
                @if($sessions->where('is_current', false)->count() > 1)
                <div style="margin-top:12px">
                    <form method="POST" action="{{ route('profile.sessions.revoke-all') }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="pf-btn pf-btn-ghost" style="font-size:12px;width:100%">End all other sessions</button>
                    </form>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Danger Zone --}}
    <div class="pf-card" style="border-color:rgba(239,68,68,.2)">
        <button onclick="toggleAccordion('acc-danger', this)"
                style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:none;border:none;cursor:pointer;text-align:left">
            <div>
                <p style="font-size:13px;font-weight:700;color:#f87171">Danger zone</p>
                <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Permanently delete your account</p>
            </div>
            <span class="pf-chevron" style="color:var(--text-faint);font-size:14px">▾</span>
        </button>
        <div id="acc-danger" class="pf-accordion-body closed" style="max-height:400px">
            <div style="padding:0 20px 20px;border-top:1px solid rgba(239,68,68,.15)">
                <div style="height:16px"></div>
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:14px;line-height:1.6">
                    Schedules deletion of your account and all data. You have 30 days to cancel.
                </p>
                <button onclick="document.getElementById('delete-modal').style.display='flex'"
                        class="pf-btn pf-btn-danger"
                        {{ $user->deletion_requested_at ? 'disabled style=opacity:.4;cursor:not-allowed' : '' }}>
                    {{ $user->deletion_requested_at ? 'Deletion already scheduled' : 'Delete my account' }}
                </button>
            </div>
        </div>
    </div>

</div>
</div>{{-- end grid --}}

{{-- Delete modal --}}
<div id="delete-modal" style="display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.75);align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--bg-card);border:1px solid rgba(239,68,68,.3);border-radius:16px;padding:24px;max-width:420px;width:100%">
        <h3 style="font-size:16px;font-weight:800;color:#f87171;margin-bottom:8px">Delete account</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px;line-height:1.6">
            Your account will be <strong style="color:var(--text-secondary)">scheduled for deletion</strong> — not immediate. You have <strong style="color:var(--text-secondary)">30 days</strong> to cancel. After that, all employees, transactions, memory, and subscriptions are permanently removed.
        </p>
        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf @method('DELETE')
            <div class="pf-field">
                <label class="pf-label" for="confirm_delete">Type DELETE to confirm</label>
                <input id="confirm_delete" name="confirm_delete" type="text" class="pf-input" placeholder="DELETE" autocomplete="off">
                @error('confirm_delete')<p style="color:#f87171;font-size:11px;margin-top:4px">{{ $message }}</p>@enderror
            </div>
            <div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap">
                <button type="button" onclick="document.getElementById('delete-modal').style.display='none'"
                        class="pf-btn pf-btn-ghost" style="flex:1;min-width:120px">Cancel</button>
                <button type="submit" id="delete-submit" disabled
                        class="pf-btn pf-btn-danger" style="flex:1;min-width:120px;opacity:.5">
                    Delete permanently
                </button>
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

function toggleAccordion(id, btn) {
    const body    = document.getElementById(id);
    const chevron = btn.querySelector('.pf-chevron');
    const isOpen  = !body.classList.contains('closed');
    if (isOpen) {
        body.style.maxHeight = '0';
        body.style.paddingTop = '0';
        body.style.paddingBottom = '0';
        body.classList.add('closed');
        chevron.style.transform = 'rotate(0deg)';
    } else {
        body.style.maxHeight = body.getAttribute('style').match(/max-height:(\d+px)/)?.[1] ?? '600px';
        body.classList.remove('closed');
        chevron.style.transform = 'rotate(180deg)';
    }
}

document.getElementById('confirm_delete')?.addEventListener('input', function() {
    const btn = document.getElementById('delete-submit');
    btn.disabled = this.value !== 'DELETE';
    btn.style.opacity = this.value === 'DELETE' ? '1' : '.5';
});
</script>
</body>
</html>

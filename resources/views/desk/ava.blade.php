<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>AVA's Desk — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}

/* ── THEME TOKENS: light default, dark override ── */
:root,[data-theme="dark"]{
  --db-bg:#0D0D0D; --db-card:#1A1A1A; --db-text:#F5F5F5; --db-text-muted:#9CA3AF;
  --db-border:rgba(255,255,255,.14); --db-chip:#262626;
  --db-invert-bg:#F5F5F5; --db-invert-text:#0D0D0D;
}
[data-theme="light"]{
  --db-bg:#F4F3F1; --db-card:#ffffff; --db-text:#0D0D0D; --db-text-muted:#9CA3AF;
  --db-border:#E5E7EB; --db-chip:#ECEAE6;
  --db-invert-bg:#0D0D0D; --db-invert-text:#ffffff;
}

body{font-family:'Inter',sans-serif;background:var(--db-bg);color:var(--db-text);-webkit-font-smoothing:antialiased}

/* ── OUTER SHELL ── */
.ob-shell{display:flex;flex-direction:column;height:100vh;overflow:hidden}

/* ── TOP BAR: logo left, theme toggle + menu right ── */
.ob-topbar{background:var(--db-bg);display:flex;align-items:center;justify-content:space-between;padding:0 24px;height:52px;flex-shrink:0}
.ob-topbar-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.ob-topbar-name{font-size:12.5px;font-weight:700;color:var(--db-text)}
.ob-topbar-email{font-size:11px;color:var(--db-text-muted)}
.ob-topbar-right{display:flex;align-items:center;gap:12px}
.ob-token-badge{font-size:10px;font-weight:600;color:var(--db-text-muted);background:var(--db-chip);border-radius:5px;padding:2px 7px;white-space:nowrap}
.ob-topbar-link{font-size:11px;font-weight:600;color:var(--db-text-muted);text-decoration:none}
.ob-topbar-link:hover{color:var(--db-text)}

/* Theme toggle switch */
.ob-theme-toggle{width:36px;height:20px;border-radius:10px;border:none;cursor:pointer;position:relative;transition:background .2s ease;flex-shrink:0;background:var(--db-chip)}
.ob-theme-toggle::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:var(--db-invert-bg);transition:transform .2s ease}
[data-theme="dark"] .ob-theme-toggle::after{transform:translateX(16px)}

/* Hamburger + dropdown menu */
.ob-menu-wrap{position:relative}
.ob-hamburger{width:32px;height:32px;border-radius:8px;border:1px solid var(--db-border);background:var(--db-card);display:flex;align-items:center;justify-content:center;cursor:pointer}
.ob-hamburger svg{width:15px;height:15px;stroke:var(--db-text);stroke-width:2;fill:none}
.ob-menu-dropdown{position:absolute;top:calc(100% + 8px);right:0;min-width:220px;background:var(--db-card);border:1px solid var(--db-border);border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.18);padding:8px;z-index:50;display:none}
.ob-menu-dropdown.open{display:block}
.ob-menu-user{padding:8px 10px 10px;border-bottom:1px solid var(--db-border);margin-bottom:6px}
.ob-menu-token{padding:0 10px 8px}
.ob-menu-item{display:block;width:100%;text-align:left;padding:8px 10px;border-radius:8px;font-size:12.5px;font-weight:600;color:var(--db-text);text-decoration:none;background:none;border:none;cursor:pointer;font-family:inherit}
.ob-menu-item:hover{background:var(--db-chip)}

/* ── PAGE: sidebar + card area ── */
.ob-page{display:grid;grid-template-columns:260px 1fr;flex:1;overflow:hidden}

/* ── SIDEBAR ── */
.ob-sidebar{background:var(--db-bg);display:flex;flex-direction:column;overflow-y:auto}

/* Worker steps */
.ob-steps{display:flex;flex-direction:column;padding:18px 24px 0;flex:1}
.ob-workers-hd{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}
.ob-hire-btn{font-size:10px;font-weight:700;color:var(--db-text);background:var(--db-card);border:1px solid var(--db-border);border-radius:6px;padding:3px 8px;text-decoration:none}
.ob-hire-btn:hover{background:var(--db-chip)}

.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative;text-decoration:none;color:inherit}
.ob-step:not(:last-child) .ob-step-rail::after{content:'';position:absolute;left:13px;top:30px;width:2px;height:calc(100% - 6px);background:var(--db-border);border-radius:2px}
.ob-step.done:not(:last-child) .ob-step-rail::after{background:var(--db-invert-bg)}
.ob-step-rail{position:relative;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding-bottom:20px}
.ob-step:last-child .ob-step-rail{padding-bottom:0}
.ob-step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;position:relative;z-index:1;flex-shrink:0;overflow:hidden}
.ob-step.pending .ob-step-num{background:var(--db-chip);color:var(--db-text-muted);border:1.5px solid var(--db-border)}
.ob-step.active  .ob-step-num{background:var(--db-invert-bg);color:var(--db-invert-text);box-shadow:0 0 0 4px rgba(128,128,128,.15)}
.ob-step.done    .ob-step-num{background:var(--db-invert-bg);color:var(--db-invert-text)}
.ob-step-body{padding-top:4px;padding-bottom:20px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:13px;font-weight:700;color:var(--db-text);line-height:1.2}
.ob-step.pending .ob-step-label{color:var(--db-text-muted)}
.ob-step-desc{font-size:11px;color:var(--db-text-muted);margin-top:2px;line-height:1.4;display:flex;align-items:center;gap:5px}
.ob-step.active .ob-step-desc{color:var(--db-text)}
.ob-step.active .ob-step-body{background:var(--db-card);border:1.5px solid var(--db-border);border-radius:12px;padding:10px 14px;margin-right:-4px}

/* Links section below workers */
.ob-links-section{padding:16px 24px 8px;border-top:1px solid var(--db-border);flex-shrink:0}
.ob-links-hd{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:8px}
.ob-link{display:flex;align-items:center;gap:9px;padding:6px 10px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:500;color:var(--db-text-muted);transition:all .12s}
.ob-link:hover{background:var(--db-card);color:var(--db-text)}
.ob-link svg{width:13px;height:13px;stroke:currentColor;stroke-width:1.8;fill:none;flex-shrink:0}

/* Security footer */
.ob-security{margin:8px 24px 16px;padding:13px 15px;border-radius:12px;background:var(--db-chip);border:1px solid var(--db-border);flex-shrink:0}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:4px}
.ob-security-row svg{width:12px;height:12px;stroke:var(--db-text-muted);flex-shrink:0;fill:none}
.ob-security-title{font-size:11.5px;font-weight:700;color:var(--db-text)}
.ob-security p{font-size:10.5px;color:var(--db-text-muted);line-height:1.55}

/* ── CARD AREA ── */
.ob-card-area{display:flex;align-items:center;justify-content:center;padding:8px 24px 20px 12px;overflow:hidden}
.ob-card{
  display:grid;grid-template-columns:1fr 320px;
  width:100%;height:100%;max-height:calc(100vh - 84px);
  border-radius:20px;overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);
  border:1px solid var(--db-border);
  position:relative;
}
.ob-card-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 15%;z-index:0;display:block}

/* ── HERO ── */
.ob-hero{position:relative;overflow:hidden;background:transparent;display:flex;flex-direction:column;z-index:1}
.ob-hero-fade{position:absolute;inset:0;z-index:1;pointer-events:none;background:linear-gradient(to right,#fff 0%,#fff 30%,rgba(255,255,255,.9) 44%,rgba(255,255,255,.3) 62%,transparent 78%)}
.ob-hero-content{position:relative;z-index:2;padding:28px 36px 24px;max-width:470px;display:flex;flex-direction:column;height:100%;overflow-y:auto}
.ob-hero-content::-webkit-scrollbar{width:4px}
.ob-hero-content::-webkit-scrollbar-thumb{background:rgba(0,0,0,.12);border-radius:2px}
.ob-h1{font-size:clamp(1.55rem,2vw,2rem);font-weight:900;letter-spacing:-.04em;line-height:1.1;color:#0D0D0D;margin-bottom:8px;flex-shrink:0}
.ob-sub{font-size:13px;color:#374151;line-height:1.65;margin-bottom:16px;flex-shrink:0}

/* AVA bubble */
.ob-bubble{position:absolute;z-index:3;top:44%;right:6%;transform:translateY(-50%);background:#fff;border:1px solid #E5E7EB;border-radius:16px;border-bottom-left-radius:4px;padding:14px 18px;width:182px;box-shadow:0 4px 16px rgba(0,0,0,.1)}
.ob-bubble p{font-size:12.5px;font-weight:600;color:#0D0D0D;line-height:1.55}

/* ── RIGHT PANEL ── */
.ob-profile{background:var(--db-card);border-left:1px solid var(--db-border);padding:20px;display:flex;flex-direction:column;overflow-y:auto;position:relative;z-index:1}
.ob-profile::-webkit-scrollbar{width:3px}
.ob-profile::-webkit-scrollbar-thumb{background:var(--db-chip);border-radius:2px}
.emp-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:6px}
.emp-name{font-size:1.5rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);line-height:1}
.emp-role{font-size:12px;color:var(--db-text-muted);margin-top:3px;margin-bottom:12px}
.emp-divider{border:none;border-top:1px solid var(--db-border);margin:0 0 12px}

/* TX switcher */
.tx-switcher-wrap{position:relative;flex-shrink:0}
.tx-switcher-btn{display:flex;align-items:center;gap:5px;font-size:10.5px;font-weight:700;font-family:'Inter',monospace;color:var(--db-text);background:var(--db-chip);border:1px solid var(--db-border);border-radius:7px;padding:5px 9px;cursor:pointer}
.tx-switcher-btn svg{stroke:var(--db-text-muted);stroke-width:2;fill:none}
.tx-switcher-dropdown{display:none;position:absolute;top:calc(100% + 6px);right:0;width:230px;max-height:260px;overflow-y:auto;background:var(--db-card);border:1px solid var(--db-border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);padding:6px;z-index:40}
.tx-switcher-dropdown.open{display:block}
.tx-switcher-item{display:block;width:100%;text-align:left;padding:7px 9px;border-radius:7px;background:none;border:none;cursor:pointer;font-family:inherit}
.tx-switcher-item:hover{background:var(--db-chip)}
.tx-switcher-item-label{font-size:11.5px;font-weight:700;color:var(--db-text)}
.tx-switcher-item-meta{font-size:10px;color:var(--db-text-muted);margin-top:1px}

/* Stage timeline */
.ob-sc-feed-item{cursor:pointer;border-radius:8px;padding:3px 4px;margin:0 -4px}
.ob-sc-feed-item:hover{background:var(--db-chip)}

/* Badge row */
.tx-badge-row{display:flex;flex-wrap:wrap;gap:6px 10px;margin:10px 0 4px}
.tx-badge{font-size:11px;font-weight:700;background:none;border:none;padding:0;cursor:pointer;font-family:inherit}
.tx-badge.active{text-decoration:underline;text-underline-offset:3px}

/* Canvas */
.tx-canvas-wrap{flex:1;display:flex;flex-direction:column;overflow:hidden;border-top:1px solid var(--db-border);margin:10px -20px 0;background:#fff}
.tx-canvas-expand-bar{display:flex;justify-content:flex-end;padding:6px 10px;flex-shrink:0}
.tx-canvas-expand-btn{width:22px;height:22px;border-radius:6px;border:1px solid #E0E0E0;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer}
.tx-canvas-expand-btn svg{stroke:#5F6368;stroke-width:2;fill:none}
#tx-canvas-body{flex:1;overflow-y:auto;padding:0 16px 11px}
.tx-data-row{display:flex;flex-direction:column;gap:2px;padding:8px 0;border-bottom:1px solid #F1F3F4}
.tx-data-key{font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#9CA3AF}
.tx-data-val{font-size:12.5px;color:#202124;line-height:1.6;white-space:pre-wrap}

/* Expanded canvas overlay */
/* .ob-profile has position:relative, which would trap an absolutely-positioned
   descendant before it ever reaches .ob-card — drop it to static while expanded
   so .tx-canvas-wrap's positioning resolves against the full card. It still only
   covers the desk/hero region (right:320px) — the right panel itself (switcher,
   timeline, badges, approve/review) stays visible in its own column. */
.ob-card.tx-expanded .ob-profile{position:static;overflow:visible}
.ob-card.tx-expanded .tx-canvas-wrap{position:absolute;top:0;left:0;right:320px;bottom:0;margin:0;z-index:30;border-radius:20px 0 0 20px;border-right:1px solid var(--db-border)}

/* Activity */
.ob-act-hd{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}
.ob-sc-onshift{display:flex;align-items:center;gap:5px;font-size:9px;font-weight:700;color:#15803D;letter-spacing:.08em;text-transform:uppercase;background:#DCFCE7;border-radius:99px;padding:3px 8px}
.ob-sc-onshift-dot{width:5px;height:5px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
.ob-sc-feed{display:flex;flex-direction:column;gap:2px;margin-bottom:4px;max-height:112px;overflow:hidden;transition:max-height .2s ease}
.ob-sc-feed.expanded{max-height:600px}
.ob-sc-feed-item{display:flex;gap:7px;align-items:center;padding:4px 4px}
.ob-sc-feed-time{font-size:9px;color:var(--db-text-muted);font-weight:600;white-space:nowrap;min-width:38px;flex-shrink:0}
.ob-sc-feed-dot{width:14px;height:14px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:7px;font-weight:800;color:#fff}
.ob-sc-feed-text{font-size:11px;color:var(--db-text);font-weight:500;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;min-width:0}
.ob-sc-feed-sub{display:none}
.ob-sc-feed-toggle{font-size:10px;color:var(--db-text-muted);font-weight:600;background:none;border:none;padding:0;cursor:pointer;font-family:inherit;text-align:left;margin-bottom:6px}
.ob-sc-feed-toggle:hover{color:var(--db-text)}
.ob-sc-view-link{font-size:11px;color:var(--db-text-muted);font-weight:600;text-decoration:none;display:block;margin-bottom:2px}
.ob-sc-view-link:hover{color:var(--db-text)}

/* Draft */
.sc-draft-wrap{flex:1;display:flex;flex-direction:column;overflow:hidden;border-top:1px solid #E8EAED;margin:10px -20px 0;background:#fff}
.sc-draft-chrome{background:#F1F3F4;border-bottom:1px solid #E0E0E0;padding:6px 12px;display:flex;align-items:center;gap:6px;flex-shrink:0}
.sc-draft-body{flex:1;overflow-y:auto;padding:11px 16px}
.sc-draft-header-row{display:flex;align-items:baseline;gap:6px;padding:4px 0;border-bottom:1px solid #F1F3F4}
.sc-draft-header-label{font-size:11px;color:#5F6368;font-weight:600;width:44px;flex-shrink:0}
.sc-draft-header-value{font-size:12px;color:#202124;font-weight:500;line-height:1.4;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.sc-draft-subject-row{padding:7px 0 8px;border-bottom:1px solid #E8EAED;margin-bottom:8px}
.sc-draft-subject-text{font-size:13.5px;font-weight:700;color:#202124;line-height:1.3}
.sc-draft-preview{font-size:12px;color:#3C4043;line-height:1.75;white-space:pre-wrap}
.sc-draft-actions{padding:10px 16px;border-top:1px solid #E0E0E0;display:flex;gap:7px;flex-shrink:0}
.sc-draft-actions button,.sc-draft-actions a{flex:1;padding:9px;border-radius:9px;font-size:12px;font-weight:700;text-align:center;cursor:pointer;font-family:inherit}
.sc-btn-approve{background:#0D0D0D;color:#fff;border:none}
.sc-btn-review{background:#fff;color:#374151;border:1.5px solid #E5E7EB;text-decoration:none;display:flex;align-items:center;justify-content:center}

@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow-x:hidden;overflow-y:auto;height:auto;width:100%}
  .ob-shell{height:auto;overflow:visible;width:100%}
  /* Any nested flex/grid child can refuse to shrink below its content's min-content width
     and blow out the layout horizontally — force min-width:0 through the entire page */
  .ob-shell,.ob-shell *{min-width:0}
  .ob-topbar{height:auto;padding:12px 16px;flex-wrap:wrap;gap:6px}
  .ob-topbar-logo{font-size:18px}
  .ob-topbar-email{display:none}
  .ob-topbar-name{max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .ob-page{display:block;height:auto;overflow:visible;width:100%}
  /* No side-by-side columns on mobile — expand can safely cover full width */
  .ob-card.tx-expanded .tx-canvas-wrap{right:0;border-radius:0}
  /* Workers become a horizontal strip in the header — swipe to navigate */
  .ob-sidebar{width:100%;flex-direction:column;padding:0;overflow:hidden;border-bottom:none}
  .ob-steps{display:flex;flex-direction:row;align-items:center;gap:8px;padding:10px 16px;overflow-x:auto;width:100%;flex:none;-webkit-overflow-scrolling:touch}
  .ob-workers-hd{margin-bottom:0;flex-shrink:0;gap:8px}
  .ob-step{flex-shrink:0;align-items:center;gap:8px;background:var(--db-card);border:1.5px solid var(--db-border);border-radius:99px;padding:5px 12px 5px 6px}
  .ob-step.active{border-color:var(--db-invert-bg)}
  .ob-step-rail{padding-bottom:0}
  .ob-step-rail::after{display:none !important}
  .ob-step-num{width:24px;height:24px}
  .ob-step-body{padding:0 !important;background:none !important;border:none !important;margin:0 !important}
  .ob-step-label{font-size:11.5px}
  .ob-step-desc{display:none}
  .ob-links-section{display:none}
  .ob-security{display:none}
  .ob-card-area{padding:16px;overflow:visible;height:auto;align-items:flex-start;width:100%}
  .ob-card{display:flex;flex-direction:column;width:100%;height:auto;max-height:none;box-shadow:0 2px 12px rgba(0,0,0,.08);border:none;position:static}
  .ob-card-bg{display:none}
  .ob-hero{display:flex;flex-direction:column;min-height:unset;background:var(--db-card)}
  .ob-hero-content{position:static;background:var(--db-card);padding:20px;max-width:100%;height:auto;overflow-y:visible;order:1}
  .ob-hero-fade{display:none}
  .ob-bubble{display:none}
  .ob-h1{font-size:1.45rem;color:var(--db-text)}
  .ob-sub{color:var(--db-text-muted)}
  .ob-profile{border-left:none;border-top:1px solid var(--db-border);padding:16px}
}
</style>
</head>
<body>

@php
$previewTx = $approvals->first();

$tokenFmt = $tokenTotal >= 1000000
  ? number_format($tokenTotal/1000000,1).'M'
  : number_format($tokenTotal);
@endphp

<div class="ob-shell">

{{-- ══ TOP BAR: logo left · theme toggle + menu right ══ --}}
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

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ob-sidebar">

    {{-- MY WORKERS --}}
    <div class="ob-steps">
      <div class="ob-workers-hd">
        <a href="{{ route('profile.show') }}" style="color:inherit;text-decoration:none">{{ strtoupper($firstName) }}'S WORKERS</a>
      </div>

      @foreach($workerCatalog as $wc)
      @php
        $wDot  = $wc->status==='active' ? '#22c55e' : '#f59e0b';
        $wHref = !$wc->active ? route('workers.page') : ($wc->slug==='ava' ? route('desk.ava') : route('workers.overview',$wc->slug));
        $isActive = $wc->active && $wc->slug==='ava';
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

    {{-- Links below workers --}}
    <div class="ob-links-section">
      <div class="ob-links-hd">LINKS</div>
      @foreach([
        ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('memory')],
        ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('workers.templates',['slug'=>'ava'])],
        ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('workers.rules','ava')],
        ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', '#'],
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
      <p>You're in control of what<br>Ava can see and access.</p>
    </div>

  </aside>

  {{-- ══ FLOATING CARD ══ --}}
  <div class="ob-card-area">
    <div class="ob-card">

      {{-- Full-card Ava background --}}
      @if($coverImg)
        <img class="ob-card-bg" src="{{ $coverImg }}" alt="">
      @endif

      {{-- ── HERO / LEFT CONTENT ── --}}
      <div class="ob-hero">
        <div class="ob-hero-fade"></div>

        <div class="ob-bubble">
          <p>I've got it from here, {{ $firstName }}. I'll keep you posted!</p>
        </div>

        <div class="ob-hero-content">

          <div style="display:inline-flex;align-items:center;gap:6px;background:#0D0D0D;color:#fff;border-radius:99px;font-size:9.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:5px 12px;margin-bottom:20px;width:fit-content;flex-shrink:0">
            <span style="width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite"></span>
            ON SHIFT &nbsp;·&nbsp; {{ now()->format('g:i A') }}
          </div>

          <h1 class="ob-h1">AVA's Desk.</h1>
          <p class="ob-sub">Renewal Specialist · Monitoring your inbox and protecting your renewals.</p>

          {{-- Today's numbers --}}
          <div class="ob-stat-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;flex-shrink:0;min-width:0">
            @foreach([
              [$incomingCount,'Renewal requests detected','#6366f1'],
              [$incomingCount,'Replies drafted','#8b5cf6'],
              [$waitingCount,'Awaiting your review','#f59e0b'],
              [$completedCount,'Completed today','#22c55e'],
            ] as [$val,$lbl,$clr])
            <div style="min-width:0;background:rgba(255,255,255,.92);border:1px solid rgba(0,0,0,.08);border-radius:10px;padding:11px 13px;backdrop-filter:blur(4px)">
              <div style="font-size:24px;font-weight:900;letter-spacing:-.04em;color:{{ $clr }};line-height:1">{{ $val }}</div>
              <div style="font-size:10px;color:#9CA3AF;margin-top:3px;line-height:1.35">{{ $lbl }}</div>
            </div>
            @endforeach
          </div>

          {{-- Memory — all types --}}
          <div style="background:rgba(255,255,255,.92);border:1px solid rgba(0,0,0,.08);border-radius:12px;padding:14px 15px;backdrop-filter:blur(4px);flex-shrink:0">
            <div style="font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px">AVA's Memory</div>
            @foreach([
              ['Clients',           $clientCount,     '#6366f1'],
              ['Contacts',          $contactCount,    '#8b5cf6'],
              ['Assets',            $assetCount,      '#f59e0b'],
              ['Rules',             $ruleCount,       '#f97316'],
              ['Templates',         $templateCount,   '#22c55e'],
              ['Connected Accounts',$credentialCount, '#06b6d4'],
            ] as [$mk,$mv,$mc])
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:3px">
              <span style="font-size:11.5px;font-weight:600;color:#374151">{{ $mk }}</span>
              <span style="font-size:11.5px;font-weight:800;color:#0D0D0D">{{ $mv }}</span>
            </div>
            <div style="height:2px;background:#E8E7E4;border-radius:99px;overflow:hidden;margin-bottom:7px">
              <div style="height:100%;border-radius:99px;background:{{ $mc }};width:{{ $mv>0?min(100,max(10,$mv*15)).'%':'0%' }}"></div>
            </div>
            @endforeach
          </div>

        </div>
      </div>

      {{-- ── RIGHT PANEL: Transaction Tab ── --}}
      <div class="ob-profile" id="tx-tab" data-initial-tx="{{ $previewTx->tx_id ?? $currentTask->tx_id ?? '' }}">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
          <div>
            <div class="emp-eyebrow">On Shift</div>
            <div class="emp-name">AVA</div>
            <div class="emp-role">Renewal Specialist</div>
          </div>
          <div class="tx-switcher-wrap">
            <button type="button" class="tx-switcher-btn" id="tx-switcher-btn">
              <span id="tx-switcher-label">—</span>
              <svg viewBox="0 0 24 24" width="10" height="10"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="tx-switcher-dropdown" id="tx-switcher-dropdown"></div>
          </div>
        </div>

        <hr class="emp-divider">

        {{-- Stage timeline for the selected transaction --}}
        <div class="ob-act-hd">
          Live Activity
          <a href="{{ route('transactions') }}" class="ob-sc-view-link" style="margin:0">View Live Feed →</a>
        </div>
        <div class="ob-sc-feed" id="tx-timeline">
          <p style="font-size:12px;color:var(--db-text-muted)">Loading…</p>
        </div>
        <button type="button" class="ob-sc-feed-toggle" id="tx-timeline-toggle" style="display:none">Show all →</button>

        {{-- Badge row — quick-jump into the canvas below --}}
        <div class="tx-badge-row" id="tx-badge-row"></div>

        {{-- Canvas — renders whichever stage's artifact is selected --}}
        <div class="tx-canvas-wrap" id="tx-canvas-wrap">
          <div class="tx-canvas-expand-bar">
            <button type="button" class="tx-canvas-expand-btn" id="tx-canvas-expand-btn" title="Expand">
              <svg viewBox="0 0 24 24" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"/></svg>
            </button>
          </div>
          <div id="tx-canvas-body"><p style="font-size:12px;color:var(--db-text-muted)">Select a transaction to view its artifacts.</p></div>
          <div class="sc-draft-actions" id="tx-canvas-actions" style="display:none">
            <button type="button" class="sc-btn-approve" id="tx-approve-btn">Approve &amp; send</button>
            <a href="#" class="sc-btn-review" id="tx-review-link">Review in full</a>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>{{-- ob-page --}}
</div>{{-- ob-shell --}}

<script>
(function () {
  var saved = localStorage.getItem('unit-theme-v2') || 'light';
  document.documentElement.setAttribute('data-theme', saved);

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

<script>
(function () {
  var tab = document.getElementById('tx-tab');
  if (!tab) return;

  var csrf = document.querySelector('meta[name="csrf-token"]').content;
  var switcherBtn   = document.getElementById('tx-switcher-btn');
  var switcherLabel = document.getElementById('tx-switcher-label');
  var switcherDrop  = document.getElementById('tx-switcher-dropdown');
  var timelineEl    = document.getElementById('tx-timeline');
  var badgeRowEl    = document.getElementById('tx-badge-row');
  var canvasBodyEl  = document.getElementById('tx-canvas-body');
  var canvasActions = document.getElementById('tx-canvas-actions');
  var approveBtn    = document.getElementById('tx-approve-btn');
  var reviewLink    = document.getElementById('tx-review-link');
  var expandBtn     = document.getElementById('tx-canvas-expand-btn');
  var timelineToggle = document.getElementById('tx-timeline-toggle');

  var current = null; // last loaded tx-detail payload

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function loadTxList() {
    fetch('{{ route("desk.ava.tx-list") }}')
      .then(function (r) { return r.json(); })
      .then(function (data) {
        switcherDrop.innerHTML = '';
        (data.transactions || []).forEach(function (tx) {
          var btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'tx-switcher-item';
          btn.innerHTML = '<div class="tx-switcher-item-label">' + esc(tx.tx_id) + '</div>'
            + '<div class="tx-switcher-item-meta">' + esc(tx.label) + ' · ' + esc(tx.created_at) + '</div>';
          btn.addEventListener('click', function () {
            switcherDrop.classList.remove('open');
            loadTransaction(tx.tx_id);
          });
          switcherDrop.appendChild(btn);
        });
        if (!data.transactions || !data.transactions.length) {
          switcherDrop.innerHTML = '<p style="font-size:11px;color:var(--db-text-muted);padding:8px">No transactions yet.</p>';
        }
      });
  }

  function loadTransaction(txId) {
    if (!txId) return;
    switcherLabel.textContent = txId;
    timelineEl.innerHTML = '<p style="font-size:12px;color:var(--db-text-muted)">Loading…</p>';
    fetch('/desk/ava/tx/' + encodeURIComponent(txId))
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.error) {
          timelineEl.innerHTML = '<p style="font-size:12px;color:var(--db-text-muted)">' + esc(data.error) + '</p>';
          return;
        }
        current = data;
        renderTimeline(data.stages);
        renderBadges(data.stages);
        var defaultStage = data.stages.find(function (s) { return s.stage_key === 'draft'; })
          || data.stages[data.stages.length - 1];
        if (defaultStage) selectStage(defaultStage.stage_key);
      });
  }

  function renderTimeline(stages) {
    timelineEl.classList.remove('expanded');
    if (!stages.length) {
      timelineEl.innerHTML = '<p style="font-size:12px;color:var(--db-text-muted)">No activity recorded yet.</p>';
      timelineToggle.style.display = 'none';
      return;
    }
    timelineEl.innerHTML = stages.map(function (s, i) {
      return '<div class="ob-sc-feed-item" data-stage="' + s.stage_key + '" title="' + esc(s.summary) + '">'
        + '<span class="ob-sc-feed-time">' + esc(s.timestamp) + '</span>'
        + '<span class="ob-sc-feed-dot" style="background:' + s.color + '">' + (i + 1) + '</span>'
        + '<span class="ob-sc-feed-text">' + esc(s.summary) + '</span>'
        + '</div>';
    }).join('');
    timelineToggle.style.display = stages.length > 4 ? 'block' : 'none';
    timelineToggle.textContent = 'Show all →';
    Array.prototype.forEach.call(timelineEl.querySelectorAll('.ob-sc-feed-item'), function (el) {
      el.addEventListener('click', function () { selectStage(el.dataset.stage); });
    });
  }

  function renderBadges(stages) {
    badgeRowEl.innerHTML = stages.map(function (s) {
      return '<button type="button" class="tx-badge" data-stage="' + s.stage_key + '" style="color:' + s.color + '">' + esc(s.label) + '</button>';
    }).join('');
    Array.prototype.forEach.call(badgeRowEl.querySelectorAll('.tx-badge'), function (el) {
      el.addEventListener('click', function () { selectStage(el.dataset.stage); });
    });
  }

  function selectStage(stageKey) {
    if (!current) return;
    var stage = current.stages.find(function (s) { return s.stage_key === stageKey; });
    if (!stage) return;

    Array.prototype.forEach.call(badgeRowEl.querySelectorAll('.tx-badge'), function (el) {
      el.classList.toggle('active', el.dataset.stage === stageKey);
    });

    renderCanvas(stage);
  }

  function renderCanvas(stage) {
    if (stage.canvas.type === 'email') {
      var p = stage.canvas.payload;
      canvasBodyEl.innerHTML =
        '<div class="sc-draft-header-row"><span class="sc-draft-header-label">To</span><span class="sc-draft-header-value">' + esc(p.to || '—') + '</span></div>'
        + '<div class="sc-draft-header-row"><span class="sc-draft-header-label">From</span><span class="sc-draft-header-value">' + esc(p.from || '—') + '</span></div>'
        + '<div class="sc-draft-subject-row"><div class="sc-draft-subject-text">' + esc(p.subject || '—') + '</div></div>'
        + '<div class="sc-draft-preview">' + esc(p.body || '') + '</div>';
      canvasActions.style.display = 'flex';
      approveBtn.dataset.txId = current.tx_id;
      reviewLink.href = '/transactions/' + encodeURIComponent(current.tx_id);
    } else {
      var payload = stage.canvas.payload || {};
      var rows = Object.keys(payload).map(function (key) {
        var val = payload[key];
        if (val && typeof val === 'object') val = JSON.stringify(val, null, 2);
        var label = key.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
        return '<div class="tx-data-row"><div class="tx-data-key">' + esc(label) + '</div><div class="tx-data-val">' + esc(val) + '</div></div>';
      }).join('');
      canvasBodyEl.innerHTML = rows || '<p style="font-size:12px;color:#9CA3AF;padding-top:12px">No data captured at this stage.</p>';
      canvasActions.style.display = 'none';
    }
  }

  switcherBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    switcherDrop.classList.toggle('open');
  });
  document.addEventListener('click', function (e) {
    if (!switcherDrop.contains(e.target) && e.target !== switcherBtn) {
      switcherDrop.classList.remove('open');
    }
  });

  expandBtn.addEventListener('click', function () {
    tab.closest('.ob-card').classList.toggle('tx-expanded');
  });

  timelineToggle.addEventListener('click', function () {
    var isExpanded = timelineEl.classList.toggle('expanded');
    timelineToggle.textContent = isExpanded ? 'Show less ←' : 'Show all →';
  });

  approveBtn.addEventListener('click', function () {
    var txId = approveBtn.dataset.txId;
    if (!txId) return;
    approveBtn.disabled = true;
    approveBtn.textContent = 'Sending…';
    fetch('/transactions/' + encodeURIComponent(txId) + '/decide', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      body: JSON.stringify({ decision: 'approved' }),
    }).then(function () {
      window.location.reload();
    }).catch(function () {
      approveBtn.disabled = false;
      approveBtn.textContent = 'Approve & send';
    });
  });

  loadTxList();
  loadTransaction(tab.dataset.initialTx);
})();
</script>
</body>
</html>

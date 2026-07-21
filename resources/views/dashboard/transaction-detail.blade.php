<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $tx->tx_id }} — UNIT</title>
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

/* ── SHELL (identical to /desk/{slug}, /workers/{slug}/overview, /memory, /templates, /rules, /fast-track, /connect, /billing, /transactions) ── */
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
.mem-wrap{max-width:1000px;margin:0 auto}

.td-back{font-size:12.5px;color:var(--db-text-muted);text-decoration:none;display:inline-block;margin-bottom:14px}
.td-back:hover{color:var(--db-text)}

.mem-status{border-radius:12px;padding:10px 14px;font-size:13.5px;margin-bottom:16px}
.mem-status.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#22c55e}
.mem-status.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444}

.td-card{border:1px solid var(--db-border);border-radius:16px;padding:20px;margin-bottom:16px}

.td-badge{font-size:11px;padding:2px 9px;border-radius:99px;font-weight:600}
.td-badge-priority{background:var(--db-chip);color:var(--db-text-muted)}
.td-badge-priority.high{background:rgba(245,158,11,.15);color:#fbbf24}
.td-badge-ft{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);color:#fbbf24}
.td-title{font-size:1.3rem;font-weight:700;color:var(--db-text);line-height:1.3;margin-top:8px}
.td-meta{font-size:13px;color:var(--db-text-muted);margin-top:4px}
.td-meta-sm{font-size:11px;color:var(--db-text-muted);margin-top:4px}
.td-gmail-id{font-size:11px;color:var(--db-text-muted);margin-bottom:2px}
.td-gmail-val{font-size:11px;font-family:monospace;color:var(--db-text);word-break:break-all}
.td-gmail-saved{font-size:11px;color:#22c55e;margin-top:2px}

.td-banner{border-radius:14px;padding:16px 18px;margin-bottom:16px}
.td-banner.infra{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.3)}
.td-banner.data{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3)}
.td-banner-title{font-size:13.5px;font-weight:700;margin-bottom:4px}
.td-banner-body{font-size:12px;color:var(--db-text-muted);line-height:1.6}
.td-banner-actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:12px}
.td-btn{font-size:12px;font-weight:600;padding:7px 14px;border-radius:9px;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block;border:none}
.td-btn-danger{background:#dc2626;color:#fff}
.td-btn-amber{background:#d97706;color:#fff}
.td-btn-ghost{background:transparent;border:1px solid var(--db-border);color:var(--db-text-muted)}
.td-link-underline{font-size:11.5px;color:var(--db-text-muted);text-decoration:underline;background:none;border:none;cursor:pointer;font-family:inherit}
.td-link-underline:hover{color:#fbbf24}

.td-dismissed{background:var(--db-chip);border:1px solid var(--db-border);border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:var(--db-text-muted)}

.td-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:768px){.td-grid{grid-template-columns:1fr}}

.td-card-head{display:flex;align-items:center;gap:8px;margin-bottom:14px}
.td-card-num{width:20px;height:20px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0}
.td-card-title{font-size:13px;font-weight:600;color:var(--db-text)}
.td-conf-badge{margin-left:auto;font-size:11px;color:#4ade80}

.td-field-label{font-size:11px;color:var(--db-text-muted);margin-bottom:3px}
.td-field-val{font-size:13px;color:var(--db-text)}
.td-field-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px}
.td-field{margin-top:12px}
.td-field:first-child{margin-top:0}

.td-pre{font-size:11.5px;color:var(--db-text-muted);white-space:pre-wrap;background:var(--db-chip);border-radius:9px;padding:10px 12px;margin-top:4px;line-height:1.6}

.td-review-note{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:9px;padding:10px 12px;margin-top:10px}
.td-review-note-title{font-size:11px;font-weight:600;color:#fbbf24;margin-bottom:2px}
.td-review-note-body{font-size:11px;color:#fcd34d}

.td-decision-hint{font-size:11.5px;color:var(--db-text-muted);margin-top:4px;line-height:1.5}
.td-decision-hint strong{color:var(--db-text)}
.td-textarea{width:100%;border-radius:9px;padding:9px 12px;font-size:12.5px;background:transparent;border:1px solid var(--db-border);color:var(--db-text);font-family:inherit;resize:none;margin-top:12px}
.td-decision-row{display:flex;gap:8px;margin-top:12px}
.td-decision-btn{flex:1;padding:11px;border-radius:11px;font-size:13px;font-weight:700;color:#fff;border:none;cursor:pointer;font-family:inherit}

.td-footer{margin-top:22px;padding-top:16px;border-top:1px solid var(--db-border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px}
.td-footer-note{font-size:11.5px;color:var(--db-text-muted)}

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
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('workers.memory','ava'), false],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('workers.templates',['slug'=>'ava']), false],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('workers.rules','ava'), false],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('workers.fast-track.page','ava'), false],
  ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('workers.connect','ava'), false],
  ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('billing'), false],
  ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('transactions'), true],
];

$read     = $tx->read_output     ? json_decode($tx->read_output)     : null;
$memory   = $tx->memory_output   ? json_decode($tx->memory_output)   : null;
$classify = $tx->classify_output ? json_decode($tx->classify_output) : null;
$draft    = $tx->draft_output    ? json_decode($tx->draft_output)    : null;
$rawInput = json_decode($tx->raw_input ?? '{}', true);
$source   = $rawInput['source'] ?? 'unknown';
$isFastTrack    = $source === 'fast_track_test';
$isFailed       = $tx->status === 'failed';
$isDismissed    = $tx->status === 'dismissed';
$canRefire      = $isFailed && !$isFastTrack;
$canDismiss     = in_array($tx->status, ['failed','draft_ready','human_review','blocked']);
$canDelete      = $isFastTrack;

$hasAnyOutput  = $read || $memory || $classify;
$lowConfidence = $memory && ($memory->confidence ?? 100) < 70;
$unassigned    = $memory && str_contains(strtolower($memory->matched_client ?? ''), 'unassigned');
$failureType   = null;
if ($isFailed) {
    $failureType = $hasAnyOutput ? 'data' : 'infrastructure';
}

$statusColors = [
    'draft_ready'  => ['bg'=>'rgba(167,139,250,.15)','color'=>'#a78bfa'],
    'failed'       => ['bg'=>'rgba(239,68,68,.15)','color'=>'#fca5a5'],
    'dismissed'    => ['bg'=>'var(--db-chip)','color'=>'var(--db-text-muted)'],
    'human_review' => ['bg'=>'rgba(245,158,11,.15)','color'=>'#fcd34d'],
    'approved'     => ['bg'=>'rgba(34,197,94,.15)','color'=>'#86efac'],
    'sent'         => ['bg'=>'rgba(34,197,94,.15)','color'=>'#86efac'],
    'blocked'      => ['bg'=>'rgba(249,115,22,.15)','color'=>'#fb923c'],
];
$statusColor = $statusColors[$tx->status] ?? ['bg'=>'var(--db-chip)','color'=>'var(--db-text-muted)'];
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

      <a href="{{ route('transactions') }}" class="td-back">← Back to Transactions</a>

      @if(session('success'))<div class="mem-status success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="mem-status error">{{ session('error') }}</div>@endif

      {{-- Header --}}
      <div class="td-card">
        <div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:12px">
          <div style="min-width:0">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
              <span class="td-gmail-val">{{ $tx->tx_id }}</span>
              @if($tx->priority)
              <span class="td-badge td-badge-priority {{ in_array($tx->priority, ['High','Critical']) ? 'high' : '' }}">{{ $tx->priority }}</span>
              @endif
              <span class="td-badge" style="background:{{ $statusColor['bg'] }};color:{{ $statusColor['color'] }}">{{ $tx->status }}</span>
              @if($isFastTrack)<span class="td-badge td-badge-ft">⚡ Fast Track Test</span>@endif
            </div>
            <div class="td-title">{{ $tx->category ?? 'Processing...' }}</div>
            @if($tx->worker_slug === 'nux' && $nuxRegister)
            <div class="td-meta">{{ strtoupper($nuxRegister->source_platform ?? '') }} → {{ implode(', ', json_decode($nuxRegister->target_channels ?? '[]', true) ?: []) }} · {{ $nuxRegister->topic ?? '—' }}</div>
            @elseif($memory)
            <div class="td-meta">{{ $memory->matched_client ?? '—' }} · {{ $memory->asset ?? '—' }} · {{ $memory->primary_contact_name ?? '—' }}</div>
            @endif
            <div class="td-meta-sm">{{ \Carbon\Carbon::parse($tx->created_at)->format('M j, Y · g:i A') }} · {{ $source }}</div>
          </div>
          @if($tx->gmail_draft_id)
          <div style="text-align:right;flex-shrink:0">
            <div class="td-gmail-id">Gmail Draft</div>
            <div class="td-gmail-val">{{ $tx->gmail_draft_id }}</div>
            <div class="td-gmail-saved">✓ Saved in Gmail</div>
          </div>
          @endif
        </div>
      </div>

      {{-- Failure context banner --}}
      @if($isFailed)
      <div class="td-banner {{ $failureType === 'infrastructure' ? 'infra' : 'data' }}">
        <div class="td-banner-title" style="color:{{ $failureType === 'infrastructure' ? '#f87171' : '#fbbf24' }}">
          {{ $failureType === 'infrastructure' ? '✕ Infrastructure failure' : '⚠ Data failure' }}
        </div>
        <div class="td-banner-body">
          @if($failureType === 'infrastructure')
            The pipeline job crashed before completing — likely a transient error (token expiry, queue restart, API timeout).
            <strong style="color:var(--db-text)">Re-firing is safe</strong> — the original email will be re-processed from scratch.
          @else
            The pipeline ran but couldn't complete due to missing or mismatched data.
            @if($lowConfidence) Confidence was {{ $memory->confidence }}% — below the required threshold.@endif
            @if($unassigned) No client is linked to this asset.@endif
            <strong style="color:var(--db-text)">Re-firing without fixing the underlying data will produce the same result.</strong>
          @endif
        </div>
        <div class="td-banner-actions">
          @if($failureType === 'infrastructure' && $canRefire)
          <form method="POST" action="{{ route('transactions.refire', $tx->tx_id) }}">
            @csrf
            <button type="submit" class="td-btn td-btn-danger">↺ Re-fire</button>
          </form>
          @endif
          @if($failureType === 'data')
          <a href="{{ route('memory') }}" class="td-btn td-btn-amber">Fix in Memory →</a>
          @if($canDismiss)
          <form method="POST" action="{{ route('transactions.dismiss', $tx->tx_id) }}">
            @csrf
            <button type="submit" class="td-btn td-btn-ghost">Dismiss</button>
          </form>
          @endif
          @if($canRefire)
          <form method="POST" action="{{ route('transactions.refire', $tx->tx_id) }}">
            @csrf
            <button type="submit" class="td-link-underline" onclick="return confirm('Re-firing will not fix the data issue — it will likely fail again. Fix the missing client or asset in Memory first.\n\nContinue anyway?')">Re-fire anyway</button>
          </form>
          @endif
          @endif
        </div>
      </div>
      @endif

      {{-- Dismissed notice --}}
      @if($isDismissed)
      <div class="td-dismissed">○ This transaction was dismissed and removed from active queues. The audit trail is preserved below.</div>
      @endif

      <div class="td-grid">

        {{-- Left column --}}
        <div>

          {{-- Read output --}}
          @if($read)
          <div class="td-card">
            <div class="td-card-head">
              <span class="td-card-num" style="background:rgba(99,102,241,.15);color:#818cf8">1</span>
              <span class="td-card-title">Read</span>
            </div>
            <div class="td-field">
              <div class="td-field-label">Summary</div>
              <div class="td-field-val">{{ $read->plain_english_summary }}</div>
            </div>
            <div class="td-field-row">
              <div><div class="td-field-label">Due Date</div><div class="td-field-val">{{ $read->due_date_or_deadline ?? '—' }}</div></div>
              <div><div class="td-field-label">Urgency</div><div class="td-field-val" style="color:#fbbf24">{{ $read->urgency }}</div></div>
            </div>
            <div class="td-field">
              <div class="td-field-label">Risk if ignored</div>
              <div class="td-field-val" style="color:var(--db-text-muted)">{{ $read->risk_if_ignored }}</div>
            </div>
          </div>
          @endif

          {{-- Memory output --}}
          @if($memory)
          <div class="td-card">
            <div class="td-card-head">
              <span class="td-card-num" style="background:rgba(var(--accent-rgb,241,211,98),.15);color:var(--accent-text,var(--db-text))">2</span>
              <span class="td-card-title">Memory Lookup</span>
              <span class="td-conf-badge">{{ $memory->confidence }}% confidence</span>
            </div>
            <div class="td-field-row">
              <div><div class="td-field-label">Client</div><div class="td-field-val">{{ $memory->matched_client }}</div></div>
              <div><div class="td-field-label">Asset</div><div class="td-field-val">{{ $memory->asset }}</div></div>
              <div><div class="td-field-label">Contact</div><div class="td-field-val">{{ $memory->primary_contact_name }}</div></div>
              <div><div class="td-field-label">Email</div><div class="td-field-val" style="color:var(--db-text-muted)">{{ $memory->primary_contact_email }}</div></div>
            </div>
            @if(!empty($memory->ava_rule))
            <div class="td-field">
              <div class="td-field-label">Rule Applied</div>
              <div class="td-field-val" style="font-size:11.5px;color:var(--accent-text,var(--db-text))">{{ $memory->ava_rule }}</div>
            </div>
            @endif
          </div>
          @endif

          {{-- NUX: repurposed copies --}}
          @if($tx->worker_slug === 'nux' && $nuxRegister)
          @php
            $nuxCopies = json_decode($nuxRegister->repurposed_copies ?? '[]', true) ?: [];
            $nuxChannels = json_decode($nuxRegister->target_channels ?? '[]', true) ?: [];
          @endphp
          <div class="td-card">
            <div class="td-card-head">
              <span class="td-card-num" style="background:rgba(94,234,212,.15);color:#5eead4">⇄</span>
              <span class="td-card-title">Repurposed Content</span>
            </div>
            @forelse($nuxCopies as $copy)
            <div class="td-field">
              <div class="td-field-label" style="color:#5eead4;text-transform:uppercase;letter-spacing:.05em;font-weight:700">{{ strtoupper($copy['channel'] ?? '') }}</div>
              <div class="td-pre">{{ $copy['copy'] ?? '' }}</div>
              <div class="td-field-label" style="margin-top:4px">{{ $copy['char_count'] ?? 0 }} characters</div>
            </div>
            @empty
            <p style="font-size:13px;color:var(--db-text-muted)">No copies available.</p>
            @endforelse

            @if($nuxRegister->image_url)
            <div class="td-field" style="border-top:1px solid var(--db-border);padding-top:14px">
              <div class="td-field-label" style="color:#5eead4;text-transform:uppercase;letter-spacing:.05em;font-weight:700;margin-bottom:8px">Generated Image</div>
              <img src="{{ $nuxRegister->image_url }}" alt="NUX generated image" style="max-width:100%;border-radius:8px;border:1px solid var(--db-border)">
            </div>
            @endif

            @if($nuxRegister->draft_summary)
            <div class="td-field" style="border-top:1px solid var(--db-border);padding-top:10px;color:var(--db-text-muted);font-size:12px">{{ $nuxRegister->draft_summary }}</div>
            @endif
          </div>
          @endif

        </div>

        {{-- Right column --}}
        <div>

          {{-- Classify --}}
          @if($classify)
          <div class="td-card">
            <div class="td-card-head">
              <span class="td-card-num" style="background:rgba(245,158,11,.15);color:#fbbf24">3</span>
              <span class="td-card-title">Classification</span>
            </div>
            <div class="td-field-row">
              <div><div class="td-field-label">Category</div><div class="td-field-val">{{ $classify->category }}</div></div>
              <div><div class="td-field-label">Priority</div><div class="td-field-val" style="color:#fbbf24">{{ $classify->priority }}</div></div>
              <div style="grid-column:1/-1"><div class="td-field-label">Required Action</div><div class="td-field-val" style="color:var(--db-text-muted)">{{ $classify->required_action }}</div></div>
            </div>
          </div>
          @endif

          {{-- Draft --}}
          @if($draft)
          <div class="td-card">
            <div class="td-card-head">
              <span class="td-card-num" style="background:rgba(34,197,94,.15);color:#86efac">4</span>
              <span class="td-card-title">Draft Email</span>
            </div>
            <div class="td-field"><div class="td-field-label">To</div><div class="td-field-val">{{ $draft->to }}</div></div>
            <div class="td-field"><div class="td-field-label">Subject</div><div class="td-field-val">{{ $draft->subject }}</div></div>
            <div class="td-field">
              <div class="td-field-label">Body</div>
              <div class="td-pre">{{ $draft->body }}</div>
            </div>
            @if(!empty($draft->human_review_note))
            <div class="td-review-note">
              <div class="td-review-note-title">Review Note</div>
              <div class="td-review-note-body">{{ $draft->human_review_note }}</div>
            </div>
            @endif
          </div>

          {{-- Human decision --}}
          <div class="td-card">
            <div class="td-card-title" style="margin-bottom:4px">Review &amp; Decide</div>
            @if($tx->gmail_draft_id)
              <div class="td-decision-hint"><span style="color:#4ade80">●</span> Draft saved in your Gmail Drafts folder — open Gmail to edit and send it yourself.</div>
              <div class="td-decision-hint"><strong>Approve</strong> marks it as reviewed · <strong>Reject</strong> deletes the draft from Gmail.</div>
            @else
              <div class="td-decision-hint">No Gmail draft — decision recorded for learning only.</div>
            @endif
            <form method="POST" action="{{ route('transactions.decide', $tx->tx_id) }}">
              @csrf
              <textarea name="notes" rows="2" placeholder="Optional notes — why approved or rejected? Helps AVA improve." class="td-textarea"></textarea>
              <div class="td-decision-row">
                <button name="decision" value="approved" class="td-decision-btn" style="background:#15803d">✓ Approve</button>
                <button name="decision" value="rejected" onclick="return confirm('Reject and delete the Gmail draft?')" class="td-decision-btn" style="background:#7f1d1d">✗ Reject &amp; Discard</button>
              </div>
            </form>
          </div>
          @endif

        </div>
      </div>

      {{-- Footer actions --}}
      @if($canDismiss || $canDelete)
      <div class="td-footer">
        <div style="display:flex;align-items:center;gap:10px">
          @if($canDismiss)
          <form method="POST" action="{{ route('transactions.dismiss', $tx->tx_id) }}">
            @csrf
            <input type="hidden" name="reason" value="Manually dismissed from detail view">
            <button type="submit" onclick="return confirm('Dismiss this transaction? It will be removed from active queues but preserved in the audit log.')" class="td-btn td-btn-ghost">○ Dismiss</button>
          </form>
          @endif
          @if($canDelete)
          <form method="POST" action="{{ route('transactions.delete', $tx->tx_id) }}">
            @csrf @method('DELETE')
            <button type="submit" onclick="return confirm('Permanently delete this fast-track test transaction? This cannot be undone.')" class="td-btn td-btn-ghost" style="color:#f87171;border-color:rgba(239,68,68,.4)">✕ Delete</button>
          </form>
          @endif
        </div>
        <p class="td-footer-note">{{ trim(($canDismiss && !$isFastTrack ? 'Dismiss removes from active queues · ' : '') . ($canDelete ? 'Delete permanently removes test data' : ''), ' · ') }}</p>
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
</script>
</body>
</html>

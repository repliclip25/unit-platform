<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $dep->name }} · Rules — UNIT</title>
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

/* ── SHELL (identical to /desk/{slug}, /workers/{slug}/overview, /memory, /templates) ── */
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

.ob-page{display:grid;grid-template-columns:260px 1fr 320px;flex:1;overflow:hidden}
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
.mem-main{overflow-y:auto;padding:28px 32px 60px;margin:12px 12px 12px 0;background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.mem-wrap{max-width:900px;margin:0 auto}

.mem-status{border-radius:12px;padding:10px 14px;font-size:13.5px;margin-bottom:16px}
.mem-status.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#22c55e}
.mem-status.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444}

.rules-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);margin-bottom:20px}

/* Persona panel */
.persona-card{border:1px solid var(--db-border);border-radius:16px;padding:18px 20px;margin-bottom:16px}
.persona-row{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.persona-eyebrow{font-size:11px;color:var(--db-text-muted);margin-bottom:2px}
.persona-name{font-size:14px;font-weight:700;color:var(--db-text)}
.persona-name.warn{color:#f59e0b}
.persona-tagline{font-size:12px;color:var(--db-text-muted);margin-top:2px}
.persona-picker{width:100%;margin-top:14px;padding-top:14px;border-top:1px solid var(--db-border);display:none}
.persona-picker.open{display:block}
.persona-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-bottom:12px}
.persona-opt{border:1px solid var(--db-border);border-radius:12px;padding:12px 14px;cursor:pointer;display:block}
.persona-opt input{position:absolute;opacity:0;width:0;height:0}
.persona-opt.selected{border-color:var(--db-invert-bg);background:var(--db-chip)}
.persona-opt-label{font-size:12.5px;font-weight:700;color:var(--db-text);margin-bottom:2px}
.persona-opt-tagline{font-size:11px;color:var(--db-text-muted);line-height:1.4}
.persona-note{font-size:11px;color:var(--db-text-muted);margin-bottom:10px}

.mem-btn{padding:9px 16px;border-radius:9px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text);white-space:nowrap}
.mem-btn:hover{opacity:.9}
.mem-btn-secondary{padding:8px 14px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text-muted);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block}
.mem-btn-secondary:hover{background:var(--db-chip);color:var(--db-text)}

.mem-empty-card{background:transparent;border:1px dashed var(--db-border);border-radius:16px;padding:40px 20px;text-align:center}
.mem-empty-sub{font-size:12px;color:var(--db-text-muted)}

/* Drift banner */
.drift-banner{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;border:1px solid rgba(245,158,11,.35);background:rgba(245,158,11,.06);border-radius:14px;padding:14px 16px;margin-bottom:16px}
.drift-title{font-size:12.5px;font-weight:700;color:#f59e0b}
.drift-sub{font-size:11.5px;color:var(--db-text-muted);margin-top:2px}

/* Tabs */
.rules-tabs{display:flex;gap:4px;border-bottom:1px solid var(--db-border);margin-bottom:20px}
.rules-tab{padding:9px 4px;margin-right:18px;font-size:13.5px;font-weight:600;color:var(--db-text-muted);background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;font-family:inherit;white-space:nowrap}
.rules-tab.active{color:var(--db-text);border-color:var(--db-invert-bg)}
.rules-tab-badge{font-size:10px;background:var(--db-chip);color:var(--db-text-muted);border-radius:99px;padding:1px 6px;margin-left:4px}

.mem-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:16px;align-items:flex-start}
@media(max-width:900px){.mem-grid{grid-template-columns:1fr}}
.mem-list{border:1px solid var(--db-border);border-radius:16px;overflow:hidden}
.rule-row{padding:14px 18px;border-bottom:1px solid var(--db-border)}
.rule-row:last-child{border-bottom:none}
.rule-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.rule-id{font-family:monospace;font-size:11.5px;color:var(--db-text-muted)}
.mem-badge{font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px}
.rule-cond{font-size:12.5px;color:var(--db-text);line-height:1.5;margin-top:6px}
.rule-cond span{color:var(--db-text-muted);font-weight:600}
.rule-action{font-size:12px;color:var(--db-text-muted);margin-top:3px;line-height:1.5}
.rule-notes{font-size:11.5px;color:var(--db-text-muted);font-style:italic;margin-top:5px}
.rule-actions{display:flex;gap:10px;flex-shrink:0}
.rule-action-btn{font-size:11.5px;font-weight:600;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit}
.rule-action-btn:hover{color:var(--db-text)}

.mem-form-card{border:1px solid var(--db-border);border-radius:16px;overflow:hidden}
.mem-form-head{padding:14px 18px;border-bottom:1px solid var(--db-border);font-size:13px;font-weight:700;color:var(--db-text)}
.mem-form-head span{font-size:11px;font-weight:400;color:var(--db-text-muted);display:block;margin-top:2px}
.mem-form-body{padding:16px 18px;display:flex;flex-direction:column;gap:12px}
.mem-field-label{font-size:11px;font-weight:600;color:var(--db-text-muted);margin-bottom:5px;display:block}
.mem-select,.mem-input,.mem-textarea{width:100%;border-radius:9px;padding:9px 12px;font-size:13px;background:transparent;border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.mem-select:focus,.mem-input:focus,.mem-textarea:focus{border-color:var(--db-invert-bg)}
.mem-toggle-row{display:flex;align-items:center;gap:10px}
.mem-toggle{position:relative;width:36px;height:20px;flex-shrink:0}
.mem-toggle input{position:absolute;opacity:0;width:0;height:0}
.mem-toggle-track{position:absolute;inset:0;border-radius:99px;background:var(--db-chip);transition:.15s}
.mem-toggle-thumb{position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:var(--db-invert-bg);transition:.15s}
.mem-toggle input:checked ~ .mem-toggle-track{background:var(--db-invert-bg)}
.mem-toggle input:checked ~ .mem-toggle-track .mem-toggle-thumb{transform:translateX(16px);background:var(--db-invert-text)}
.mem-edit-panel{background:transparent;border-top:1px solid var(--db-border);padding:14px 18px}

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
  .mem-right{display:none}
  .mem-main{padding:16px;margin:0;border-radius:0;border-left:none;border-right:none;box-shadow:none}
  .mem-grid{grid-template-columns:1fr}
  .persona-grid{grid-template-columns:1fr}
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
$priorityColors = [
  'Critical' => ['bg' => 'rgba(239,68,68,.12)',  'text' => '#ef4444'],
  'High'     => ['bg' => 'rgba(245,158,11,.12)', 'text' => '#f59e0b'],
  'Medium'   => ['bg' => 'var(--db-chip)',       'text' => 'var(--db-text-muted)'],
  'Low'      => ['bg' => 'var(--db-chip)',       'text' => 'var(--db-text-muted)'],
];
$activeKey  = $dep->persona;
$activeP    = $activeKey && isset($personas[$activeKey]) ? $personas[$activeKey] : null;
$myRules    = $activeKey ? ($rulesByPersona[$activeKey] ?? []) : [];
$myDiff     = $activeKey ? ($diffByPersona[$activeKey] ?? ['stale'=>[],'orphaned'=>[],'missing'=>[]]) : [];
$myIssues   = !empty($myDiff['stale']) || !empty($myDiff['orphaned']) || !empty($myDiff['missing']);
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
      @foreach([
        ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('memory'), false],
        ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('workers.templates',['slug'=>$dep->worker_slug]), false],
        ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('workers.rules',$dep->worker_slug), true],
        ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', '#', false],
        ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('billing'), false],
        ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('transactions'), false],
      ] as [$lbl,$ico,$href,$isActive2])
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
      <p>Rules control how {{ $dep->name }} behaves.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <main class="mem-main">
    <div class="mem-wrap">

      @if(session('success'))<div class="mem-status success">{{ session('success') }}</div>@endif
      @if($errors->any())<div class="mem-status error">{{ $errors->first() }}</div>@endif

      <div class="rules-h1">Rules</div>

      {{-- Persona panel --}}
      <div class="persona-card">
        <div class="persona-row">
          <div>
            <div class="persona-eyebrow">Active use case</div>
            @if($activeP)
              <div class="persona-name">{{ $activeP['label'] }}</div>
              <div class="persona-tagline">{{ $activeP['tagline'] }}</div>
            @else
              <div class="persona-name warn">No use case selected</div>
              <div class="persona-tagline">Select one below to unlock your rules.</div>
            @endif
          </div>
          @if($activeP)
          <button type="button" class="mem-btn-secondary" id="persona-toggle-btn" onclick="togglePersonaPicker()">Change use case</button>
          @endif
        </div>

        <div class="persona-picker {{ $activeP ? '' : 'open' }}" id="persona-picker">
          <form method="POST" action="{{ route('workers.persona', $dep->id) }}">
            @csrf @method('PATCH')
            <div class="persona-grid">
              @foreach($personas as $key => $p)
              <label class="persona-opt {{ $activeKey === $key ? 'selected' : '' }}" onclick="selectPersonaOpt(this)">
                <input type="radio" name="persona" value="{{ $key }}" {{ $activeKey === $key ? 'checked' : '' }}>
                <div class="persona-opt-label">{{ $p['label'] }}</div>
                <div class="persona-opt-tagline">{{ $p['tagline'] }}</div>
              </label>
              @endforeach
            </div>
            @if($activeP)
            <div class="persona-note">Changing use case will re-seed your rules from the latest definition. Custom rules you added will be removed.</div>
            @endif
            <button type="submit" class="mem-btn">{{ $activeP ? 'Save use case' : 'Set use case' }}</button>
          </form>
        </div>
      </div>

      @if($activeP)

        @if($myIssues)
        <div class="drift-banner">
          <div>
            <div class="drift-title">Your rules are out of date</div>
            <div class="drift-sub">
              @if(!empty($myDiff['stale'])) {{ count($myDiff['stale']) }} stale · @endif
              @if(!empty($myDiff['orphaned'])) {{ count($myDiff['orphaned']) }} orphaned · @endif
              @if(!empty($myDiff['missing'])) {{ count($myDiff['missing']) }} new rule{{ count($myDiff['missing']) === 1 ? '' : 's' }} available · @endif
              Resetting takes 2 seconds and keeps platform rules intact.
            </div>
          </div>
          <form method="POST" action="{{ route('workers.rules.reset', $dep->id) }}">
            @csrf
            <button type="submit" class="mem-btn">Reset to latest</button>
          </form>
        </div>
        @endif

        <div class="rules-tabs">
          <button type="button" class="rules-tab active" id="tab-mine" onclick="showRulesTab('mine')">
            {{ $activeP['label'] }}
            @if(count($myRules))<span class="rules-tab-badge">{{ count($myRules) }}</span>@endif
          </button>
          <button type="button" class="rules-tab" id="tab-platform" onclick="showRulesTab('platform')">
            Platform defaults
            @if(count($platformRules))<span class="rules-tab-badge">{{ count($platformRules) }}</span>@endif
          </button>
        </div>

        {{-- My rules tab --}}
        <div id="pane-mine">
          <div class="mem-grid">
            <div class="mem-list">
              @forelse($myRules as $rule)
              @php $pc = $priorityColors[$rule->priority] ?? $priorityColors['Medium']; @endphp
              <div class="rule-row">
                <div class="rule-top">
                  <div style="flex:1;min-width:0">
                    <span class="rule-id">{{ $rule->rule_id }}</span>
                    <span class="mem-badge" style="background:{{ $pc['bg'] }};color:{{ $pc['text'] }}">{{ $rule->priority }}</span>
                    @if(!$rule->active)<span style="font-size:11px;color:var(--db-text-muted)">· disabled</span>@endif
                    <div class="rule-cond"><span>When</span> {{ $rule->condition }}</div>
                    <div class="rule-action">→ {{ $rule->action }}</div>
                    @if($rule->notes)<div class="rule-notes">{{ $rule->notes }}</div>@endif
                  </div>
                  <div class="rule-actions">
                    <button type="button" class="rule-action-btn" onclick="toggleEdit('rule-{{ $rule->id }}')">Edit</button>
                    <form method="POST" action="{{ route('workers.rules.destroy', [$dep->id, $rule->id]) }}">
                      @csrf @method('DELETE')
                      <button type="submit" class="rule-action-btn">Remove</button>
                    </form>
                  </div>
                </div>
              </div>
              <div id="edit-rule-{{ $rule->id }}" class="mem-edit-panel" style="display:none">
                <form method="POST" action="{{ route('workers.rules.update', [$dep->id, $rule->id]) }}" style="display:flex;flex-direction:column;gap:10px">
                  @csrf @method('PATCH')
                  <div style="display:flex;align-items:center;gap:8px">
                    <span class="rule-id">{{ $rule->rule_id }}</span>
                    <select name="priority" class="mem-select" style="width:auto">
                      @foreach(['Critical','High','Medium','Low'] as $pri)<option {{ $rule->priority === $pri ? 'selected' : '' }}>{{ $pri }}</option>@endforeach
                    </select>
                  </div>
                  <div><label class="mem-field-label">When…</label><textarea name="condition" rows="2" required class="mem-textarea">{{ $rule->condition }}</textarea></div>
                  <div><label class="mem-field-label">Then…</label><textarea name="action" rows="2" required class="mem-textarea">{{ $rule->action }}</textarea></div>
                  <div><label class="mem-field-label">Notes</label><input type="text" name="notes" value="{{ $rule->notes }}" class="mem-input"></div>
                  <label class="mem-toggle-row">
                    <div class="mem-toggle"><input type="checkbox" name="approval_required" value="1" {{ $rule->approval_required ? 'checked' : '' }}><div class="mem-toggle-track"><div class="mem-toggle-thumb"></div></div></div>
                    <span style="font-size:12px;color:var(--db-text-muted)">Require human approval</span>
                  </label>
                  <div style="display:flex;gap:8px">
                    <button type="submit" class="mem-btn">Save</button>
                    <button type="button" class="mem-btn-secondary" onclick="toggleEdit('rule-{{ $rule->id }}')">Cancel</button>
                  </div>
                </form>
              </div>
              @empty
              <div class="mem-empty-card" style="border:none;padding:36px 18px">
                <div class="mem-empty-sub">No rules yet. Add one using the form →</div>
              </div>
              @endforelse
            </div>

            <div class="mem-form-card">
              <div class="mem-form-head">Add rule<span>Scoped to {{ $activeP['label'] }}</span></div>
              <form method="POST" action="{{ route('workers.rules.store', $dep->id) }}" class="mem-form-body">
                @csrf
                <input type="hidden" name="persona" value="{{ $activeKey }}">
                <div><label class="mem-field-label">Priority</label>
                  <select name="priority" class="mem-select"><option>Critical</option><option>High</option><option selected>Medium</option><option>Low</option></select>
                </div>
                <div><label class="mem-field-label">When… (condition)</label><textarea name="condition" rows="3" required placeholder="e.g. {{ $activeP['examples'][0] ?? 'renewal notice received' }}" class="mem-textarea"></textarea></div>
                <div><label class="mem-field-label">Then… (action)</label><textarea name="action" rows="3" required placeholder="e.g. Log + draft renewal reminder" class="mem-textarea"></textarea></div>
                <div><label class="mem-field-label">Notes (optional)</label><input type="text" name="notes" placeholder="e.g. Never auto-send" class="mem-input"></div>
                <label class="mem-toggle-row">
                  <div class="mem-toggle"><input type="checkbox" name="approval_required" value="1" checked><div class="mem-toggle-track"><div class="mem-toggle-thumb"></div></div></div>
                  <span style="font-size:12px;color:var(--db-text-muted)">Require human approval before sending</span>
                </label>
                <button type="submit" class="mem-btn">Add rule</button>
              </form>
            </div>
          </div>
        </div>

        {{-- Platform defaults tab --}}
        <div id="pane-platform" style="display:none">
          <div class="mem-list">
            @forelse($platformRules as $rule)
            @php $pc = $priorityColors[$rule->priority] ?? $priorityColors['Medium']; @endphp
            <div class="rule-row">
              <span class="rule-id">{{ $rule->rule_id }}</span>
              <span class="mem-badge" style="background:{{ $pc['bg'] }};color:{{ $pc['text'] }}">{{ $rule->priority }}</span>
              <span class="mem-badge" style="background:var(--db-chip);color:var(--db-text-muted)">Platform</span>
              <div class="rule-cond"><span>When</span> {{ $rule->condition }}</div>
              <div class="rule-action">→ {{ $rule->action }}</div>
              @if($rule->notes)<div class="rule-notes">{{ $rule->notes }}</div>@endif
            </div>
            @empty
            <div class="mem-empty-card" style="border:none;padding:36px 18px"><div class="mem-empty-sub">No platform defaults for this deployment.</div></div>
            @endforelse
          </div>
        </div>

      @else
      <div class="mem-empty-card">
        <div class="mem-empty-sub">Select a use case above to see your rules.</div>
      </div>
      @endif

    </div>
  </main>

  <aside class="mem-right"></aside>

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

function togglePersonaPicker() {
  document.getElementById('persona-picker').classList.toggle('open');
}

function selectPersonaOpt(el) {
  el.parentElement.querySelectorAll('.persona-opt').forEach(function (o) { o.classList.remove('selected'); });
  el.classList.add('selected');
}

function showRulesTab(name) {
  document.getElementById('pane-mine').style.display = name === 'mine' ? 'block' : 'none';
  document.getElementById('pane-platform').style.display = name === 'platform' ? 'block' : 'none';
  document.getElementById('tab-mine').classList.toggle('active', name === 'mine');
  document.getElementById('tab-platform').classList.toggle('active', name === 'platform');
}

function toggleEdit(key) {
  var el = document.getElementById('edit-' + key);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>

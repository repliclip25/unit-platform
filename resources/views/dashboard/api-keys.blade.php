<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Models & API Keys — UNIT</title>
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

.ak-header{display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px}
.ak-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.ak-sub{font-size:12.5px;color:var(--db-text-muted);margin-top:2px}

.mem-btn{padding:9px 16px;border-radius:9px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text);white-space:nowrap}
.mem-btn:hover{opacity:.9}
.mem-btn-secondary{padding:8px 14px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text-muted);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block}
.mem-btn-secondary:hover{background:var(--db-chip);color:var(--db-text)}
.ak-btn-gold{background:var(--accent,#F5C518);color:#111}

.mem-form-card{border:1px solid var(--db-border);border-radius:16px;overflow:hidden;margin-bottom:16px}
.mem-form-head{padding:14px 18px;border-bottom:1px solid var(--db-border);display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.mem-form-title{font-size:13px;font-weight:700;color:var(--db-text)}
.mem-form-sub{font-size:11.5px;color:var(--db-text-muted);margin-top:2px}
.ak-close-btn{background:none;border:none;font-size:16px;color:var(--db-text-muted);cursor:pointer;line-height:1}
.mem-form-body{padding:16px 18px;display:flex;flex-direction:column;gap:12px}
.mem-field-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.mem-field-label{font-size:11px;font-weight:600;color:var(--db-text-muted);margin-bottom:5px;display:block}
.mem-input{width:100%;border-radius:9px;padding:9px 12px;font-size:13px;background:transparent;border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.mem-input:focus{outline:none;border-color:var(--db-invert-bg)}

.ak-provider{border:1px solid var(--db-border);border-radius:16px;overflow:hidden;margin-bottom:14px}
.ak-provider-head{padding:14px 16px;border-bottom:1px solid var(--db-border);display:flex;flex-wrap:wrap;align-items:center;gap:12px}
.ak-provider-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0}
.ak-provider-name{font-size:13px;font-weight:700;color:var(--db-text)}
.ak-provider-count{font-size:11.5px;color:var(--db-text-muted)}
.ak-provider-actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-left:auto}
.ak-key-badge{display:flex;align-items:center;gap:6px;font-size:11px;padding:4px 10px;border-radius:99px}
.ak-key-badge.platform{background:rgba(34,197,94,.12);color:#4ade80;border:1px solid rgba(34,197,94,.25)}
.ak-key-badge.own{background:rgba(var(--accent-rgb,241,211,98),.12);color:var(--accent-text,var(--db-text));border:1px solid rgba(var(--accent-rgb,241,211,98),.25)}
.ak-pulse-dot{width:6px;height:6px;border-radius:50%;background:#4ade80}
.ak-remove-btn{font-size:11px;padding:5px 10px;border-radius:8px;border:1px solid rgba(239,68,68,.35);color:#f87171;background:rgba(239,68,68,.07);cursor:pointer;font-family:inherit}
.ak-addkey-btn{font-size:11.5px;padding:6px 12px;border-radius:8px;border:1px solid var(--db-border);color:var(--db-text-muted);background:transparent;cursor:pointer;font-family:inherit}
.ak-addkey-btn a{margin-left:4px;color:var(--db-text-muted);text-decoration:none}

.ak-model-row{padding:12px 16px;border-bottom:1px solid var(--db-border);display:flex;align-items:flex-start;gap:10px}
.ak-model-row:last-child{border-bottom:none}
.ak-status-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:4px}
.ak-model-name{font-size:12.5px;font-weight:600;color:var(--db-text)}
.ak-model-tier{font-size:10.5px;padding:2px 8px;border-radius:99px;font-weight:600}
.ak-model-tag{font-size:10.5px;padding:2px 8px;border-radius:99px;background:var(--db-chip);color:var(--db-text-muted);border:1px solid var(--db-border)}
.ak-model-id{font-size:11px;font-family:monospace;color:var(--db-text-muted);margin-top:2px}
.ak-model-price{font-size:11px;font-family:monospace;color:var(--db-text-muted);margin-top:2px}
.ak-worker-chip{display:inline-flex;align-items:center;gap:5px;font-size:11px;padding:3px 10px;border-radius:99px;background:rgba(var(--accent-rgb,241,211,98),.1);color:var(--accent-text,var(--db-text));border:1px solid rgba(var(--accent-rgb,241,211,98),.22);text-decoration:none;margin-top:6px;margin-right:5px}

.ak-legend{display:flex;flex-wrap:wrap;align-items:center;gap:16px;margin-top:20px}
.ak-legend-item{display:flex;align-items:center;gap:6px;font-size:11.5px;color:var(--db-text-muted)}
.ak-legend-dot{width:8px;height:8px;border-radius:50%}
.ak-legend-note{font-size:11.5px;color:var(--db-text-muted);margin-left:auto}

.ak-danger{margin-top:32px;border:1px solid rgba(239,68,68,.25);border-radius:16px;overflow:hidden}
.ak-danger-head{padding:14px 18px;background:rgba(239,68,68,.06);border-bottom:1px solid rgba(239,68,68,.15)}
.ak-danger-title{font-size:13px;font-weight:700;color:#f87171}
.ak-danger-body{padding:18px;display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:14px}
.ak-danger-desc{font-size:12px;color:var(--db-text-muted);margin-top:4px;line-height:1.6;max-width:520px}

.ak-modal-overlay{display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.7);align-items:center;justify-content:center;padding:20px}
.ak-modal-overlay.open{display:flex}
.ak-modal{background:var(--db-card);border:1px solid rgba(239,68,68,.3);border-radius:16px;padding:24px;max-width:420px;width:100%}
.ak-modal-title{font-size:16px;font-weight:800;color:#f87171;margin-bottom:8px}
.ak-modal-body{font-size:13px;color:var(--db-text-muted);margin-bottom:20px;line-height:1.6}
.ak-danger-submit{flex:1;padding:11px;border-radius:11px;font-size:13px;font-weight:700;background:#ef4444;color:#fff;border:none;cursor:pointer;font-family:inherit}
.ak-danger-submit:disabled{opacity:.4;cursor:not-allowed}

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
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('workers.memory','ava'), false],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('workers.templates',['slug'=>'ava']), false],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('workers.rules','ava'), false],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('workers.fast-track.page','ava'), false],
  ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('workers.connect','ava'), false],
  ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('billing'), false],
  ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('transactions'), false],
];

$catalog = \App\Platform\Services\LLM\ModelCatalog::all();
$providerMeta = [
    'anthropic' => ['color' => 'var(--accent,#F5C518)', 'bg' => 'rgba(var(--accent-rgb,241,211,98),.1)',   'border' => 'rgba(var(--accent-rgb,241,211,98),.3)',   'keyHint' => 'sk-ant-…',  'docsUrl' => 'https://console.anthropic.com/settings/keys'],
    'openai'    => ['color' => '#10b981', 'bg' => 'rgba(16,185,129,.1)',  'border' => 'rgba(16,185,129,.3)',  'keyHint' => 'sk-…',      'docsUrl' => 'https://platform.openai.com/api-keys'],
    'kimi'      => ['color' => '#06b6d4', 'bg' => 'rgba(6,182,212,.1)',   'border' => 'rgba(6,182,212,.3)',   'keyHint' => 'sk-…',      'docsUrl' => 'https://platform.moonshot.cn/console/api-keys'],
    'google'    => ['color' => '#a855f7', 'bg' => 'rgba(168,85,247,.1)',  'border' => 'rgba(168,85,247,.3)',  'keyHint' => 'AIza…',     'docsUrl' => 'https://aistudio.google.com/app/apikey'],
    'custom'    => ['color' => '#9ca3af', 'bg' => 'rgba(156,163,175,.1)', 'border' => 'rgba(156,163,175,.3)', 'keyHint' => 'optional',  'docsUrl' => '#'],
];
$tierColors = [
    'Fast'      => ['bg' => 'var(--badge-fast-bg)',      'color' => 'var(--badge-fast-text)'],
    'Balanced'  => ['bg' => 'var(--badge-balanced-bg)',  'color' => 'var(--badge-balanced-text)'],
    'Powerful'  => ['bg' => 'var(--badge-powerful-bg)',  'color' => 'var(--badge-powerful-text)'],
    'Reasoning' => ['bg' => 'var(--badge-reasoning-bg)', 'color' => 'var(--badge-reasoning-text)'],
    'Custom'    => ['bg' => 'var(--badge-custom-bg)',    'color' => 'var(--badge-custom-text)'],
];
$workersByModel = $workers->groupBy('model');
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
      <p>Your keys take priority · Encrypted at rest · Never logged</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      @if(session('success'))<div class="mem-status success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="mem-status error">{{ session('error') }}</div>@endif

      <div class="ak-header">
        <div>
          <div class="ak-h1">Models &amp; API Keys</div>
          <div class="ak-sub">All available models · BYOK keys · custom endpoints</div>
        </div>
        <button onclick="document.getElementById('register-panel').style.display='block'" class="mem-btn ak-btn-gold">+ Register Model</button>
      </div>

      {{-- ── Register Custom Model ── --}}
      <div id="register-panel" class="mem-form-card" style="display:none">
        <div class="mem-form-head">
          <div>
            <div class="mem-form-title">Register Custom Model</div>
            <div class="mem-form-sub">Any OpenAI-compatible endpoint — self-hosted Llama, Mistral, Groq, Together.ai, Ollama, etc.</div>
          </div>
          <button onclick="document.getElementById('register-panel').style.display='none'" class="ak-close-btn">✕</button>
        </div>
        <form method="POST" action="{{ route('settings.custom-models.store') }}" class="mem-form-body">
          @csrf
          <div class="mem-field-row">
            <div><label class="mem-field-label">Display Name *</label><input type="text" name="name" placeholder="My Llama 3 Server" class="mem-input"></div>
            <div><label class="mem-field-label">Model Identifier *</label><input type="text" name="model_identifier" placeholder="llama3.2:latest" class="mem-input" style="font-family:monospace"></div>
            <div><label class="mem-field-label">Base URL *</label><input type="url" name="base_url" placeholder="http://localhost:11434/v1" class="mem-input" style="font-family:monospace"></div>
            <div><label class="mem-field-label">API Key (optional)</label><input type="password" name="api_key" placeholder="sk-… or leave blank" autocomplete="new-password" class="mem-input" style="font-family:monospace"></div>
          </div>
          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <button type="submit" class="mem-btn">Register Model</button>
            <span style="font-size:11px;color:var(--db-text-muted)">After registering, select from any worker's Configure tab.</span>
          </div>
        </form>
      </div>

      {{-- ── Model Catalog ── --}}
      @foreach($catalog as $providerKey => $provider)
      @php $pm = $providerMeta[$providerKey]; @endphp
      <div class="ak-provider">
        <div class="ak-provider-head">
          <div class="ak-provider-icon" style="background:{{ $pm['bg'] }};border:1px solid {{ $pm['border'] }};color:{{ $pm['color'] }}">{{ strtoupper(substr($providerKey, 0, 2)) }}</div>
          <div>
            <div class="ak-provider-name">{{ $provider['label'] }}</div>
            <div class="ak-provider-count">{{ count($provider['models']) }} models</div>
          </div>
          <div class="ak-provider-actions">
            @if($platformKeys[$providerKey] ?? false)
            <span class="ak-key-badge platform"><span class="ak-pulse-dot"></span>Platform key</span>
            @endif
            @if($keys->has($providerKey))
            <span class="ak-key-badge own"><span class="ak-pulse-dot" style="background:var(--accent-text,var(--db-text))"></span>Your key</span>
            <form method="POST" action="{{ route('settings.api-keys.destroy', $providerKey) }}" onsubmit="return confirm('Remove {{ $provider['label'] }} key?')">
              @csrf @method('DELETE')
              <button class="ak-remove-btn">Remove</button>
            </form>
            @else
            <button onclick="toggleKeyForm('{{ $providerKey }}')" class="ak-addkey-btn">+ Add key <a href="{{ $pm['docsUrl'] }}" target="_blank" onclick="event.stopPropagation()">↗</a></button>
            @endif
          </div>
        </div>

        {{-- Inline key form --}}
        @if(!$keys->has($providerKey))
        <div id="key-form-{{ $providerKey }}" class="mem-form-body" style="display:none;border-bottom:1px solid var(--db-border)">
          <form method="POST" action="{{ route('settings.api-keys.store') }}">
            @csrf
            <input type="hidden" name="provider" value="{{ $providerKey }}">
            <div class="mem-field-row" style="margin-bottom:10px">
              <div><label class="mem-field-label">Label</label><input type="text" name="label" placeholder="{{ $provider['label'] }} Key" class="mem-input"></div>
              <div><label class="mem-field-label">API Key</label><input type="password" name="api_key" placeholder="{{ $pm['keyHint'] }}" autocomplete="new-password" class="mem-input" style="font-family:monospace"></div>
            </div>
            <div style="display:flex;align-items:center;gap:12px">
              <button type="submit" class="mem-btn">Save Key</button>
              <button type="button" onclick="toggleKeyForm('{{ $providerKey }}')" class="mem-btn-secondary" style="border:none;background:none;padding:0">Cancel</button>
            </div>
          </form>
        </div>
        @endif

        {{-- Models list --}}
        @foreach($provider['models'] as $modelId => $m)
        @php
          $tc           = $tierColors[$m['tier']] ?? $tierColors['Balanced'];
          $usingWorkers = $workersByModel->get($modelId, collect());
          $hasPlatform  = $platformKeys[$providerKey] ?? false;
          $hasOwnKey    = $keys->has($providerKey);
          $dotColor     = $usingWorkers->isNotEmpty() ? '#4ade80' : (($hasPlatform || $hasOwnKey) ? '#4b5563' : 'transparent');
        @endphp
        <div class="ak-model-row">
          <span class="ak-status-dot" style="background:{{ $dotColor }};{{ $dotColor==='transparent' ? 'border:1px solid var(--db-border)' : '' }}"></span>
          <div style="flex:1;min-width:0">
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px">
              <span class="ak-model-name">{{ $m['name'] }}</span>
              <span class="ak-model-tier" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }}">{{ $m['tier'] }}</span>
              @if(!empty($m['recommended']))<span class="ak-model-tag">recommended</span>@endif
              @if($hasPlatform && !$hasOwnKey)<span class="ak-model-tag" style="background:var(--badge-platform-bg);color:var(--badge-platform-text)">Platform</span>@endif
              @if($hasOwnKey)<span class="ak-model-tag" style="background:var(--badge-yourkey-bg);color:var(--badge-yourkey-text)">Your Key</span>@endif
            </div>
            <div class="ak-model-id">{{ $modelId }}</div>
            <div class="ak-model-price">${{ number_format($m['cost_in'], 2) }} in · ${{ number_format($m['cost_out'], 2) }} out / M</div>
            @if($usingWorkers->isNotEmpty())
            <div style="margin-top:2px">
              @foreach($usingWorkers as $w)
              <a href="{{ route('workers.show', $w->worker_slug) }}" class="ak-worker-chip">
                <span style="width:5px;height:5px;border-radius:50%;background:{{ $w->status==='active'?'#4ade80':'#facc15' }}"></span>
                {{ Str::limit($w->name, 18) }}
              </a>
              @endforeach
            </div>
            @endif
          </div>
        </div>
        @endforeach
      </div>
      @endforeach

      {{-- ── Custom Models ── --}}
      @if($customModels->isNotEmpty())
      <div class="ak-provider">
        <div class="ak-provider-head">
          <div class="ak-provider-icon" style="background:rgba(156,163,175,.1);border:1px solid rgba(156,163,175,.3);color:#9ca3af">CU</div>
          <div>
            <div class="ak-provider-name">Custom Models</div>
            <div class="ak-provider-count">Self-hosted &amp; custom endpoints</div>
          </div>
        </div>
        @foreach($customModels as $cm)
        @php $usingWorkers = $workersByModel->get($cm->model_id, collect()); @endphp
        <div class="ak-model-row">
          <span class="ak-status-dot" style="background:{{ $usingWorkers->isNotEmpty() ? '#4ade80' : '#4b5563' }}"></span>
          <div style="flex:1;min-width:0">
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px">
              <span class="ak-model-name">{{ $cm->name }}</span>
              <span class="ak-model-tag">Custom</span>
            </div>
            <div class="ak-model-id">{{ $cm->base_url }} · {{ $cm->model_identifier }}</div>
            @if($usingWorkers->isNotEmpty())
            <div style="margin-top:6px">
              @foreach($usingWorkers as $w)
              <a href="{{ route('workers.show', $w->worker_slug) }}" class="ak-worker-chip">
                <span style="width:5px;height:5px;border-radius:50%;background:#4ade80"></span>
                {{ Str::limit($w->name, 18) }}
              </a>
              @endforeach
            </div>
            @endif
          </div>
          <form method="POST" action="{{ route('settings.custom-models.destroy', $cm->id) }}" onsubmit="return confirm('Remove {{ $cm->name }}?')">
            @csrf @method('DELETE')
            <button class="ak-remove-btn">Remove</button>
          </form>
        </div>
        @endforeach
      </div>
      @endif

      {{-- Legend --}}
      <div class="ak-legend">
        <span class="ak-legend-item"><span class="ak-legend-dot" style="background:#4ade80"></span>Running on a worker</span>
        <span class="ak-legend-item"><span class="ak-legend-dot" style="background:#4b5563"></span>Available · not in use</span>
        <span class="ak-legend-item"><span class="ak-legend-dot" style="background:var(--db-chip);border:1px solid var(--db-border)"></span>No key — add one to unlock</span>
        <span class="ak-legend-note">Your keys take priority · Encrypted at rest · Never logged</span>
      </div>

      {{-- ── Danger Zone ── --}}
      <div class="ak-danger">
        <div class="ak-danger-head"><div class="ak-danger-title">Danger Zone</div></div>
        <div class="ak-danger-body">
          <div>
            <div style="font-size:13px;font-weight:600;color:var(--db-text)">Delete account</div>
            <div class="ak-danger-desc">Schedules your account for deletion — you'll have 30 days to cancel before all workers, transactions, Gmail connections, memory, and billing records are permanently removed.</div>
          </div>
          <button onclick="document.getElementById('delete-account-modal').classList.add('open')" class="mem-btn-secondary" style="border-color:rgba(239,68,68,.4);background:rgba(239,68,68,.07);color:#f87171">Delete account</button>
        </div>
      </div>

      {{-- Delete account modal --}}
      <div id="delete-account-modal" class="ak-modal-overlay">
        <div class="ak-modal">
          <div class="ak-modal-title">Delete your account</div>
          <div class="ak-modal-body">Your account will be <strong style="color:var(--db-text)">scheduled for deletion</strong> — not immediate. You have <strong style="color:var(--db-text)">30 days</strong> to cancel from your Profile page. After that, all workers, transactions, Gmail connections, memory, and billing records are permanently removed.</div>
          <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf @method('DELETE')
            <div style="margin-bottom:14px">
              <label class="mem-field-label">Type <span style="color:#f87171;font-family:monospace">DELETE</span> to confirm</label>
              <input type="text" name="confirm_delete" autocomplete="off" placeholder="DELETE" class="mem-input" style="font-family:monospace"
                     oninput="document.getElementById('confirm-delete-btn').disabled = this.value !== 'DELETE'">
            </div>
            <div style="display:flex;gap:10px">
              <button type="submit" id="confirm-delete-btn" disabled class="ak-danger-submit">Schedule deletion</button>
              <button type="button" onclick="document.getElementById('delete-account-modal').classList.remove('open')" class="mem-btn-secondary" style="flex:1;text-align:center">Cancel</button>
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

function toggleKeyForm(provider) {
  var el = document.getElementById('key-form-' + provider);
  if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>

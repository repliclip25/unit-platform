<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $grant->owner_name }}'s Memory — UNIT</title>
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

/* ── SHELL (identical to /desk/{slug}, /workers/{slug}/overview, /memory, /templates, /rules, /fast-track, /connect) ── */
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

.sh-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.sh-sub{font-size:12.5px;color:var(--db-text-muted);margin-top:3px}
.sh-header-row{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:20px}
.sh-perms{display:flex;flex-wrap:wrap;gap:6px}
.mem-badge{font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:99px;background:var(--db-chip);color:var(--db-text-muted)}

.mem-list{border:1px solid var(--db-border);border-radius:16px;overflow:hidden;margin-bottom:16px}
.mem-list-head{padding:14px 18px;border-bottom:1px solid var(--db-border);display:flex;align-items:center;gap:10px}
.mem-list-title{font-size:13.5px;font-weight:700;color:var(--db-text)}
.mem-list-count{font-size:12px;color:var(--db-text-muted)}
.mem-btn-secondary{padding:8px 14px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text-muted);font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block;margin-left:auto}
.mem-btn-secondary:hover{background:var(--db-chip);color:var(--db-text)}
.mem-btn{padding:9px 16px;border-radius:9px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text);white-space:nowrap}
.mem-btn:hover{opacity:.9}

.mem-row{padding:14px 18px;border-bottom:1px solid var(--db-border);display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap}
.mem-row:last-child{border-bottom:none}
.mem-row-name{font-size:13px;font-weight:600;color:var(--db-text)}
.mem-row-sub{font-size:12.5px;color:var(--db-text-muted);margin-top:2px;display:flex;flex-wrap:wrap;gap:0 10px}
.mem-row-notes{font-size:12px;color:var(--db-text-muted);font-style:italic;margin-top:4px}
.mem-row-empty{padding:36px 18px;text-align:center;font-size:13.5px;color:var(--db-text-muted)}
.mem-row-select{border-radius:8px;padding:6px 10px;font-size:12px;background:transparent;border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.mem-row-copied{font-size:11.5px;color:var(--db-text-muted);font-style:italic}

.mem-form-card{border:1px solid var(--db-border);border-radius:16px;overflow:hidden;margin-bottom:16px}
.mem-form-head{padding:14px 18px;border-bottom:1px solid var(--db-border);font-size:13px;font-weight:700;color:var(--db-text)}
.mem-form-head span{font-size:11px;font-weight:400;color:var(--db-text-muted);display:block;margin-top:2px}
.mem-form-body{padding:16px 18px;display:flex;flex-direction:column;gap:12px}
.mem-field-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.mem-field-label{font-size:11px;font-weight:600;color:var(--db-text-muted);margin-bottom:5px;display:block}
.mem-input{width:100%;border-radius:9px;padding:9px 12px;font-size:13px;background:transparent;border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.mem-input:focus{outline:none;border-color:var(--db-invert-bg)}

.mem-empty-card{background:transparent;border:1px dashed var(--db-border);border-radius:16px;padding:40px 20px;text-align:center}
.mem-empty-sub{font-size:12px;color:var(--db-text-muted)}

.sh-group-card{border:1px solid var(--db-border);border-radius:14px;margin-bottom:10px;overflow:hidden}
.sh-group-head{padding:12px 16px}
.sh-group-items{padding:0 16px 12px;display:flex;flex-wrap:wrap;gap:6px}
.sh-group-chip{font-size:12px;padding:4px 9px;border-radius:8px;background:transparent;border:1px solid var(--db-border);color:var(--db-text-muted);display:flex;align-items:center;gap:5px}

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
$tables = [
    'clients'  => ['label' => 'Clients',  'cols' => ['name', 'industry', 'role', 'preferred_style', 'address']],
    'contacts' => ['label' => 'Contacts', 'cols' => ['name', 'role', 'email', 'phone', 'department']],
    'assets'   => ['label' => 'Assets',   'cols' => ['name', 'type', 'vendor', 'renewal_date', 'cost_per_year']],
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
          <a href="{{ route('app.memory') }}#shared" class="ob-menu-item">
            <svg viewBox="0 0 24 24" class="ob-menu-item-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            My Memory
          </a>
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
      <a href="{{ route('app.memory') }}#shared" class="ob-link">
        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        My Memory
      </a>
    </div>

    <div class="ob-security">
      <div class="ob-security-row">
        <svg viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>Your permissions here are set by {{ $grant->owner_name }} and can be revoked at any time.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      @if(session('success'))<div class="mem-status success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="mem-status error">{{ session('error') }}</div>@endif

      <div class="sh-header-row">
        <div>
          <div class="sh-h1">{{ $grant->owner_name }}'s Memory</div>
          <div class="sh-sub">{{ $grant->deployment_name }} · {{ $grant->worker_slug }}</div>
        </div>
        <div class="sh-perms">
          @foreach($permissions as $p)
          <span class="mem-badge">{{ $p }}</span>
          @endforeach
        </div>
      </div>

      @foreach($tables as $tableName => $meta)
      @php $records = $memory[$tableName] ?? collect(); @endphp
      <div class="mem-list">
        <div class="mem-list-head">
          <span class="mem-list-title">{{ $meta['label'] }}</span>
          <span class="mem-list-count">{{ $records->count() }} records</span>
          @if(in_array('upload', $permissions))
          <button type="button" class="mem-btn-secondary" onclick="toggleUpload('{{ $tableName }}')">+ Add Record</button>
          @endif
        </div>

        @if(in_array('upload', $permissions))
        <div class="mem-form-card" id="upload-{{ $tableName }}" style="display:none;border:none;border-bottom:1px solid var(--db-border);border-radius:0">
          <form method="POST" action="{{ route('app.memory.access.upload', $grant->id) }}" class="mem-form-body">
            @csrf
            <input type="hidden" name="table_name" value="{{ $tableName }}">
            <div class="mem-field-row">
              @foreach($meta['cols'] as $col)
              <div><label class="mem-field-label">{{ ucfirst($col) }}</label><input type="text" name="data[{{ $col }}]" class="mem-input"></div>
              @endforeach
            </div>
            <div style="display:flex;gap:8px">
              <button type="submit" class="mem-btn">Add to Memory</button>
              <button type="button" class="mem-btn-secondary" style="margin-left:0" onclick="toggleUpload('{{ $tableName }}')">Cancel</button>
            </div>
          </form>
        </div>
        @endif

        @forelse($records as $record)
        @php $alreadyCopied = in_array($record->id, $copiedIds[$tableName] ?? []); @endphp
        <div class="mem-row">
          <div style="min-width:0;flex:1">
            <div class="mem-row-name">{{ $record->name ?? '—' }}
              @if($tableName === 'contacts' && !empty($record->is_decision_maker))<span class="mem-badge">Decision Maker</span>@endif
              @if($tableName === 'clients' && !empty($record->status))<span class="mem-badge">{{ $record->status }}</span>@endif
            </div>
            <div class="mem-row-sub">
              @foreach(array_slice($meta['cols'], 1) as $col)
                @if(!empty($record->$col) && $col !== 'status')<span>{{ $record->$col }}</span>@endif
              @endforeach
            </div>
            @if(!empty($record->notes))<div class="mem-row-notes">{{ Str::limit($record->notes, 80) }}</div>@endif
          </div>
          <div style="flex-shrink:0">
            @if($alreadyCopied)
              <span class="mem-row-copied">Copied</span>
            @elseif(in_array('copy', $permissions) && $granteeDeployments->isNotEmpty())
              <form method="POST" action="{{ route('app.memory.access.copy', $grant->id) }}" style="display:flex;gap:6px;align-items:center">
                @csrf
                <input type="hidden" name="table_name" value="{{ $tableName }}">
                <input type="hidden" name="record_id" value="{{ $record->id }}">
                <select name="target_deployment_id" class="mem-row-select">
                  @foreach($granteeDeployments as $gDep)
                  <option value="{{ $gDep->id }}">{{ $gDep->name }}</option>
                  @endforeach
                </select>
                <button type="submit" class="mem-btn-secondary" style="margin-left:0">Copy</button>
              </form>
            @endif
          </div>
        </div>
        @empty
        <div class="mem-row-empty">No {{ strtolower($meta['label']) }} in this memory yet.</div>
        @endforelse
      </div>
      @endforeach

      {{-- ── Groups ─────────────────────────────────────────────────────── --}}
      @if(isset($ownerGroups) && $ownerGroups->isNotEmpty())
      <div class="mem-list-head" style="border:none;padding-left:0;padding-right:0">
        <span class="mem-list-title">Asset Groups</span>
        <span class="mem-list-count">{{ $ownerGroups->count() }} group{{ $ownerGroups->count()!==1?'s':'' }}</span>
      </div>
      @foreach($ownerGroups as $group)
      @php $gDays = null; $nearest = $group->items->whereNotNull('renewal_date')->sortBy('renewal_date')->first(); if ($nearest) $gDays = (int) now()->diffInDays($nearest->renewal_date, false); @endphp
      <div class="sh-group-card">
        <div class="sh-group-head">
          <span class="mem-row-name">{{ $group->name }}</span>
          @if($group->type)<span class="mem-badge">{{ $group->type }}</span>@endif
          @if($gDays !== null)<span class="mem-badge" style="background:{{ $gDays<=0?'rgba(239,68,68,.15)':($gDays<=30?'rgba(245,158,11,.15)':'var(--db-chip)') }};color:{{ $gDays<=0?'#ef4444':($gDays<=30?'#f59e0b':'var(--db-text-muted)') }}">{{ $gDays <= 0 ? 'Expired' : 'Next '.$gDays.'d' }}</span>@endif
          <div class="mem-row-sub">{{ $group->items->count() }} asset{{ $group->items->count()!==1?'s':'' }}{{ !empty($group->client_name) ? ' · '.$group->client_name : '' }}</div>
        </div>
        @if($group->items->isNotEmpty())
        <div class="sh-group-items">
          @foreach($group->items as $item)
          @php $iDays = $item->renewal_date ? (int) now()->diffInDays($item->renewal_date, false) : null; @endphp
          <span class="sh-group-chip">
            <span style="width:6px;height:6px;border-radius:50%;background:{{ $iDays!==null && $iDays<=0 ? '#ef4444' : ($iDays!==null && $iDays<=30 ? '#f59e0b' : 'var(--db-border)') }}"></span>
            {{ $item->name }}
            @if($item->renewal_date)· {{ $item->renewal_date }}@endif
          </span>
          @endforeach
        </div>
        @endif
      </div>
      @endforeach
      @endif

    </div>
  </main>

  <aside class="mem-right"></aside>
  </div>

</div>{{-- ob-page --}}
</div>{{-- ob-shell --}}

<x-self-learn pageKey="memory.shared" />

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

function toggleUpload(table) {
  var el = document.getElementById('upload-' + table);
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>

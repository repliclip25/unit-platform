<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Your Team — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
button,select,input,textarea{outline:none;font-family:inherit}
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

/* ── SHELL (identical to /app/dashboard, /app/billing, etc.) ── */
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
.mem-wrap{max-width:1160px;margin:0 auto}

@keyframes pulse-dot { 0%,100% { opacity:1; } 50% { opacity:.4; } }
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
  ['Billing', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('app.billing'), false],
];

$totalInboxes = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->count();

$workerMeta = [
    'ava' => [
        'color'    => '#B8890A',
        'rgb'      => '245,197,24',
        'icon'     => '✉',
        'badge'    => 'Live',
        'category' => 'RENEWALS',
        'pitch'    => 'I make sure you never miss an important renewal.',
        'about'    => 'I watch your inbox, understand each renewal request, use what I know about your customers and business, prepare the reply, and leave it in Gmail for your approval.',
        'bullets'  => [
            'Reads and classifies every inbound renewal email',
            'Drafts tailored responses using your contacts and templates',
            'Flags urgent or at-risk accounts for immediate review',
            'Logs every action to your renewal register',
        ],
    ],
    'nux' => [
        'color'    => '#a78bfa',
        'rgb'      => '167,139,250',
        'icon'     => '⇄',
        'badge'    => 'Live',
        'category' => 'CONTENT',
        'pitch'    => 'I turn one idea into content everywhere.',
        'about'    => 'I listen for new content, understand your voice and patterns, then create platform-native posts and newsletters — ready to review or publish automatically.',
        'bullets'  => [
            'Watches LinkedIn and X for new posts',
            'Repurposes content for each target platform natively',
            'Generates custom images with AI',
            'Delivers ready-to-publish drafts to your Gmail',
        ],
    ],
];

$defaultMeta = ['color'=>'#F5C518','rgb'=>'245,197,24','icon'=>'⚙','badge'=>'Live','category'=>'AUTOMATION','pitch'=>'','about'=>'','bullets'=>[]];

$deployableWorkers = collect($catalog)->filter(function($worker) use ($contracts, $deploymentCounts, $totalInboxes) {
    $count    = $deploymentCounts->get($worker->slug, 0);
    $contract = $contracts->get($worker->slug);
    $inst     = $contract ? $contract->instances() : [];
    if ($count === 0) return true;
    if (isset($inst['max']) && $inst['max'] !== null && $count >= $inst['max']) return false;
    if (($inst['limit_by'] ?? null) === 'gmail_credentials') return $count < $totalInboxes;
    return $inst['multiple'] ?? false;
})->keyBy('slug');

$visibleCatalog = collect($catalog)->filter(fn($w) =>
    !\App\Platform\Services\WorkerRegistry::isDecommissioned($w->slug) &&
    !\App\Platform\Services\WorkerRegistry::isRemoved($w->slug) &&
    !\App\Platform\Services\WorkerRegistry::isRemoving($w->slug)
);

$depBySlug = $deployments->groupBy('worker_slug');
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
        $wHref = !$wc->active ? '#catalog-'.$wc->slug : ($wc->slug==='ava' ? route('app.desk.ava') : route('app.workers.overview',$wc->slug));
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
              <span style="width:5px;height:5px;border-radius:50%;background:{{ $wDot }};flex-shrink:0;display:inline-block"></span>
              {{ $wc->role }}
            @else
              Not hired — {{ $wc->role }}
            @endif
          </div>
        </div>
      </a>
      @endforeach
      <a href="#" class="ob-step done" style="text-decoration:none;margin-top:4px">
        <div class="ob-step-rail"><div class="ob-step-num" style="background:var(--db-invert-bg);color:var(--db-invert-text)">✓</div></div>
        <div class="ob-step-body"><div class="ob-step-label">You're here</div></div>
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
      <p>Each worker only sees what you connect to it.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      {{-- ── Page header ── --}}
      <div style="margin-bottom:28px;display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:12px">
          <div>
              <p style="font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:6px">Your Team at Work</p>
              <h1 style="font-size:24px;font-weight:900;color:var(--db-text);line-height:1.1">Meet your AI employees.</h1>
              <p style="font-size:13px;color:var(--db-text-muted);margin-top:5px">Each worker runs independently on the UNIT platform, 24/7.</p>
          </div>
          <span style="font-size:11px;color:var(--db-text-muted);padding:5px 12px;border:1px solid var(--db-border);border-radius:20px">{{ $visibleCatalog->count() }} available</span>
      </div>

      {{-- ── Worker Cards ── --}}
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;margin-bottom:32px">

      @foreach($visibleCatalog as $worker)
      @php
          $m          = $workerMeta[$worker->slug] ?? $defaultMeta;
          $reg        = $registryRows[$worker->slug] ?? null;
          $profileImg = $reg?->profile_image ? asset('storage/' . $reg->profile_image) : null;
          $coverImg   = $reg?->cover_image   ? asset('storage/' . $reg->cover_image)   : null;
          $mediaData  = json_decode($reg->media ?? '{}', true);
          $color      = $mediaData['color'] ?? $m['color'];

          $rawGallery   = json_decode($reg->gallery ?? '[]', true) ?? [];
          $galleryItems = array_values(array_filter($rawGallery, fn($g) => !in_array($g['type']??'', ['profile','cover'])));

          $catalogContract = $contracts->get($worker->slug);
          $workerEmployee  = $catalogContract ? $catalogContract->employee() : [];
          $role            = $workerEmployee['title'] ?? $worker->category ?? '';

          $slugDeps = $depBySlug->get($worker->slug, collect());
          $firstDep = $slugDeps->first();
          $depCount = $slugDeps->count();
          $isActive = $firstDep?->status === 'active';
          $isPaused = $firstDep?->status === 'paused';
          $hasDeployment = $firstDep !== null;
          $isLive    = $m['badge'] === 'Live';
          $isTesting = \App\Platform\Services\WorkerRegistry::isTesting($worker->slug);
          $canDeploy = $deployableWorkers->has($worker->slug);

          $statusQuote = null;
          $statusCta   = null;
          if ($hasDeployment && $firstDep) {
              $pendingDrafts = DB::table('transactions')
                  ->where('deployment_id', $firstDep->id)
                  ->where('status', 'draft_ready')
                  ->count();
              $recentCount = DB::table('transactions')
                  ->where('deployment_id', $firstDep->id)
                  ->where('created_at', '>=', now()->subDays(7))
                  ->count();

              if ($pendingDrafts > 0) {
                  $statusQuote = "I've prepared " . $pendingDrafts . " " . ($pendingDrafts === 1 ? 'draft' : 'drafts') . ". " . ($pendingDrafts === 1 ? 'It\'s' : 'One is') . " waiting for your approval.";
                  $statusCta   = ['label' => 'Review now →', 'url' => route('app.workers.transactions', $firstDep->worker_slug)];
              } elseif ($recentCount > 0) {
                  $statusQuote = "Processed {$recentCount} " . ($recentCount === 1 ? 'email' : 'emails') . " this week. Everything's up to date.";
                  $statusCta   = ['label' => 'View activity →', 'url' => route('app.workers.transactions', $firstDep->worker_slug)];
              } else {
                  $statusQuote = "Watching your inbox. Ready to act the moment something comes in.";
                  $statusCta   = ['label' => 'Open workspace →', 'url' => route('app.workers.show', $worker->slug)];
              }
          }
      @endphp

      <div id="catalog-{{ $worker->slug }}"
           style="background:var(--db-bg);border:1px solid {{ $hasDeployment ? 'var(--db-border)' : 'var(--db-border)' }};border-radius:20px;overflow:hidden;display:flex;flex-direction:column;{{ (!$isLive && !$isTesting) ? 'opacity:.45' : '' }}">

          {{-- ── Portrait area ── --}}
          <div style="position:relative;height:300px;overflow:hidden;background:#0d0d0d">
              @if($coverImg)
              <img src="{{ $coverImg }}" alt="{{ $worker->name }}" style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block">
              @elseif($profileImg)
              <img src="{{ $profileImg }}" alt="{{ $worker->name }}" style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block">
              @else
              <div style="width:100%;height:100%;background:linear-gradient(160deg,rgba({{ $m['rgb'] }},.12) 0%,#0d0d0d 100%);display:flex;align-items:center;justify-content:center">
                  <span style="font-size:72px;opacity:.25">{{ $m['icon'] }}</span>
              </div>
              @endif

              <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,0) 40%,rgba(13,13,13,.85) 100%)"></div>

              <div style="position:absolute;top:0;left:0;right:0;padding:16px 18px;display:flex;align-items:flex-start;justify-content:space-between">
                  <div style="display:flex;align-items:center;gap:10px">
                      <div style="width:38px;height:38px;border-radius:10px;background:rgba(0,0,0,.55);border:1px solid rgba(255,255,255,.12);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                          @if($profileImg && $coverImg)
                          <img src="{{ $profileImg }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:10px">
                          @else
                          <span style="font-size:16px">{{ $m['icon'] }}</span>
                          @endif
                      </div>
                      <div>
                          <p style="font-size:15px;font-weight:900;color:#fff;line-height:1.1;text-shadow:0 1px 4px rgba(0,0,0,.6)">{{ $worker->name }}</p>
                          <p style="font-size:10px;color:rgba(255,255,255,.6);margin-top:1px">{{ $role }}</p>
                      </div>
                  </div>

                  @if($hasDeployment && $isActive)
                  <div style="display:flex;align-items:center;gap:6px">
                      <span style="width:6px;height:6px;border-radius:50%;background:#4ade80;display:block;flex-shrink:0;animation:pulse-dot 2s infinite"></span>
                      <span style="font-size:10px;font-weight:700;color:#4ade80;text-shadow:0 0 8px rgba(74,222,128,.5)">On duty</span>
                  </div>
                  @elseif($hasDeployment && $isPaused)
                  <div style="display:flex;align-items:center;gap:6px">
                      <span style="font-size:10px;font-weight:700;color:#fbbf24">⏸ Paused</span>
                  </div>
                  @elseif($isTesting)
                  <span style="font-size:10px;font-weight:700;color:#fbbf24">⚗ Testing</span>
                  @elseif($isLive)
                  <div style="display:flex;align-items:center;gap:6px">
                      <span style="width:6px;height:6px;border-radius:50%;background:#4ade80;display:block;flex-shrink:0"></span>
                      <span style="font-size:10px;font-weight:700;color:#4ade80">Available</span>
                  </div>
                  @else
                  <span style="font-size:10px;font-weight:600;color:rgba(255,255,255,.35)">Coming Soon</span>
                  @endif
              </div>

              <div style="position:absolute;bottom:16px;left:18px">
                  <span style="font-size:9px;font-weight:900;letter-spacing:.14em;padding:4px 12px;border-radius:4px;background:{{ $color }};color:#ffffff;text-transform:uppercase">{{ $m['category'] }}</span>
              </div>
          </div>

          {{-- ── Pitch + About ── --}}
          <div style="padding:18px 20px;flex:1;display:flex;flex-direction:column">

              @if($m['pitch'])
              <p style="font-size:15px;font-weight:800;color:var(--db-text);line-height:1.4;margin-bottom:8px">{{ $m['pitch'] }}</p>
              @endif
              @if($m['about'])
              <p style="font-size:12px;color:var(--db-text-muted);line-height:1.7;margin-bottom:14px">{{ $m['about'] }}</p>
              @elseif($worker->description)
              <p style="font-size:12px;color:var(--db-text-muted);line-height:1.7;margin-bottom:14px">{{ $worker->description }}</p>
              @endif

              @if($hasDeployment && $statusQuote)
              <div style="background:var(--db-chip);border:1px solid var(--db-border);border-radius:10px;padding:11px 14px;margin-bottom:14px">
                  <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--db-text-muted);margin-bottom:4px">Latest update</p>
                  <p style="font-size:12px;color:var(--db-text);line-height:1.6;margin-bottom:8px">{{ $statusQuote }}</p>
                  @if($statusCta)
                  <a href="{{ $statusCta['url'] }}" style="font-size:11px;font-weight:700;color:{{ $color }};text-decoration:none">{{ $statusCta['label'] }}</a>
                  @endif
              </div>
              @endif

              @if(!empty($galleryItems))
              <div style="margin-bottom:14px;margin-top:4px">
                  <div style="display:flex;gap:6px;overflow-x:auto;padding-bottom:2px;scrollbar-width:none">
                  @foreach(array_slice($galleryItems, 0, 3) as $gi => $gitem)
                  @php
                      $gIsYt  = str_starts_with($gitem['type'] ?? '', 'youtube');
                      $gUrl   = $gitem['kind'] === 'url' ? null : asset('storage/' . ($gitem['path'] ?? ''));
                      $ytId   = null;
                      if ($gIsYt && !empty($gitem['url'])) {
                          preg_match('/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $gitem['url'], $ytm);
                          $ytId = $ytm[1] ?? null;
                      }
                  @endphp
                  <div style="flex-shrink:0;border-radius:8px;overflow:hidden;width:80px;height:54px;cursor:pointer;border:1px solid var(--db-border)" onclick="openGallery('{{ $worker->slug }}', {{ $gi }})">
                      @if($ytId)
                      <img src="https://img.youtube.com/vi/{{ $ytId }}/mqdefault.jpg" alt="" style="width:100%;height:100%;object-fit:cover;display:block">
                      @elseif($gUrl)
                      <img src="{{ $gUrl }}" alt="" style="width:100%;height:100%;object-fit:cover;display:block">
                      @endif
                  </div>
                  @endforeach
                  </div>
              </div>
              <script>window['gallery_{{ $worker->slug }}'] = @json($galleryItems);</script>
              @endif

              {{-- ── Deploy / Action area ── --}}
              <div style="margin-top:auto">

              @if(!$isLive && !$isTesting)
              <button disabled style="width:100%;padding:11px;border-radius:10px;border:1px solid var(--db-border);color:var(--db-text-muted);background:transparent;font-size:13px;font-weight:600;cursor:not-allowed">
                  Coming Soon
              </button>

              @elseif($isTesting && !$hasDeployment)
              <div style="text-align:center;padding:10px 0">
                  <p style="font-size:12px;color:#fbbf24;font-weight:600">⚗ In testing — invite only</p>
              </div>

              @elseif($hasDeployment)
              <a href="{{ route('app.workers.show', $worker->slug) }}"
                 style="display:block;text-align:center;width:100%;box-sizing:border-box;padding:12px;border-radius:12px;background:rgba({{ $m['rgb'] }},.12);border:1px solid rgba({{ $m['rgb'] }},.25);color:{{ $color }};font-size:13px;font-weight:700;text-decoration:none">
                  Open workspace →
              </a>

              @elseif($canDeploy)
              <button onclick="toggleDeploy('{{ $worker->slug }}')"
                      id="deploy-btn-{{ $worker->slug }}"
                      data-color="{{ $color }}"
                      data-rgb="{{ $m['rgb'] }}"
                      style="width:100%;padding:12px;border-radius:12px;border:none;background:{{ $color }};color:#ffffff;font-size:13px;font-weight:800;cursor:pointer">
                  Hire Now →
              </button>

              <div id="deploy-form-{{ $worker->slug }}" style="display:none;margin-top:14px">
                  <form method="POST" action="{{ route('app.workers.store') }}">
                      @csrf
                      <input type="hidden" name="worker_slug" value="{{ $worker->slug }}">
                      <div style="margin-bottom:10px">
                          <label style="font-size:11px;color:var(--db-text-muted);display:block;margin-bottom:4px">Deployment name</label>
                          <input type="text" name="name" value="{{ $worker->name }}" required
                              style="width:100%;box-sizing:border-box;background:var(--db-chip);color:var(--db-text);font-size:13px;border:1px solid var(--db-border);border-radius:8px;padding:8px 10px">
                      </div>
                      @if($credentials->isNotEmpty())
                      <div style="margin-bottom:12px">
                          <label style="font-size:11px;color:var(--db-text-muted);display:block;margin-bottom:4px">Gmail inbox</label>
                          <select name="credential_id"
                              style="width:100%;box-sizing:border-box;background:var(--db-chip);color:var(--db-text);font-size:13px;border:1px solid var(--db-border);border-radius:8px;padding:8px 10px">
                              <option value="">— connect after deploy —</option>
                              @foreach($credentials as $cred)
                              <option value="{{ $cred->id }}">{{ $cred->gmail_address }}</option>
                              @endforeach
                          </select>
                      </div>
                      @endif
                      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                          <button type="button" onclick="toggleDeploy('{{ $worker->slug }}')"
                              style="padding:10px;border-radius:8px;border:1px solid var(--db-border);color:var(--db-text-muted);background:transparent;font-size:12px;font-weight:600;cursor:pointer">
                              Cancel
                          </button>
                          <button type="submit"
                              style="padding:10px;border-radius:8px;border:none;background:{{ $color }};color:#ffffff;font-size:12px;font-weight:800;cursor:pointer">
                              Confirm Hire
                          </button>
                      </div>
                  </form>
              </div>

              @else
              <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
                  <div>
                      <p style="font-size:12px;font-weight:600;color:var(--db-text)">{{ $depCount }} instance{{ $depCount !== 1 ? 's' : '' }} running</p>
                      <p style="font-size:11px;color:var(--db-text-muted);margin-top:2px">Connect another inbox to add more</p>
                  </div>
                  @php $connectRoute = $worker->slug === 'nux' ? route('app.nux.connect.linkedin') : route('app.ava.gmail.authorize'); @endphp
                  <a href="{{ $connectRoute }}"
                     style="font-size:11px;font-weight:700;padding:8px 14px;border-radius:8px;background:{{ $color }};color:#ffffff;text-decoration:none;white-space:nowrap;flex-shrink:0">
                      + Connect
                  </a>
              </div>
              @endif

              </div>
          </div>
      </div>
      @endforeach

      </div>

      {{-- ── Free trial note ── --}}
      <div style="background:var(--db-bg);border:1px solid var(--db-border);border-radius:14px;padding:14px 20px;display:flex;align-items:center;gap:12px;max-width:520px">
          <span style="font-size:20px">🎁</span>
          <p style="font-size:12px;color:var(--db-text-muted);line-height:1.6">
              <strong style="color:var(--db-text)">25 free transactions</strong> on every worker. No credit card required until you scale.
          </p>
      </div>

    </div>
  </main>
  </div>

</div>{{-- ob-page --}}
</div>{{-- ob-shell --}}

{{-- ── Gallery lightbox ── --}}
<div id="gallery-lightbox" onclick="if(event.target===this)closeGallery()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;align-items:center;justify-content:center;flex-direction:column">
    <button onclick="closeGallery()" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#fff;font-size:28px;cursor:pointer;opacity:.7">×</button>
    <button onclick="galleryPrev()" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:22px;width:44px;height:44px;border-radius:50%;cursor:pointer">‹</button>
    <button onclick="galleryNext()" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:22px;width:44px;height:44px;border-radius:50%;cursor:pointer">›</button>
    <div id="gallery-lb-media" style="max-width:90vw;max-height:80vh;display:flex;align-items:center;justify-content:center"></div>
    <p id="gallery-lb-caption" style="color:rgba(255,255,255,.65);font-size:13px;margin-top:14px;text-align:center"></p>
    <div id="gallery-lb-dots" style="display:flex;gap:6px;margin-top:12px"></div>
</div>

<x-self-learn pageKey="dashboard.workers" />

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

let _lbSlug = null, _lbIdx = 0;

function openGallery(slug, idx) {
    _lbSlug = slug; _lbIdx = idx;
    document.getElementById('gallery-lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    renderLb();
}
function closeGallery() {
    document.getElementById('gallery-lightbox').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('gallery-lb-media').innerHTML = '';
}
function galleryPrev() { const items = window['gallery_' + _lbSlug] || []; _lbIdx = (_lbIdx - 1 + items.length) % items.length; renderLb(); }
function galleryNext() { const items = window['gallery_' + _lbSlug] || []; _lbIdx = (_lbIdx + 1) % items.length; renderLb(); }
function renderLb() {
    const items = window['gallery_' + _lbSlug] || [];
    if (!items.length) return;
    const item = items[_lbIdx];
    const mediaEl = document.getElementById('gallery-lb-media');
    mediaEl.innerHTML = '';
    const ytMatch = (item.url||'').match(/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (ytMatch) {
        const iframe = document.createElement('iframe');
        iframe.src = 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=1';
        iframe.allow = 'autoplay; encrypted-media'; iframe.allowFullscreen = true;
        iframe.style.cssText = 'width:min(90vw,854px);height:min(80vh,480px);border:none;border-radius:12px';
        mediaEl.appendChild(iframe);
    } else if (item.kind === 'file' && (item.path||'').match(/\.(mp4|mov|webm)$/i)) {
        const v = document.createElement('video');
        v.src = '/storage/' + item.path; v.controls = true; v.autoplay = true;
        v.style.cssText = 'max-width:90vw;max-height:78vh;border-radius:12px';
        mediaEl.appendChild(v);
    } else {
        const img = document.createElement('img');
        img.src = '/storage/' + item.path;
        img.style.cssText = 'max-width:90vw;max-height:78vh;border-radius:12px;object-fit:contain';
        mediaEl.appendChild(img);
    }
    document.getElementById('gallery-lb-caption').textContent = item.caption || '';
    const dots = document.getElementById('gallery-lb-dots');
    dots.innerHTML = '';
    items.forEach((_, i) => {
        const d = document.createElement('div');
        d.style.cssText = `width:7px;height:7px;border-radius:50%;background:${i===_lbIdx?'#fff':'rgba(255,255,255,.3)'};cursor:pointer`;
        d.onclick = () => { _lbIdx = i; renderLb(); };
        dots.appendChild(d);
    });
}
document.addEventListener('keydown', e => {
    if (document.getElementById('gallery-lightbox').style.display !== 'none') {
        if (e.key === 'ArrowLeft') galleryPrev();
        if (e.key === 'ArrowRight') galleryNext();
        if (e.key === 'Escape') closeGallery();
    }
});

function toggleDeploy(slug) {
    const form = document.getElementById('deploy-form-' + slug);
    const btn  = document.getElementById('deploy-btn-' + slug);
    if (!form || !btn) return;
    const open = form.style.display === 'none';
    form.style.display = open ? 'block' : 'none';
    const color = btn.getAttribute('data-color') || '#F5C518';
    const rgb   = btn.getAttribute('data-rgb')   || '245,197,24';
    if (open) {
        btn.textContent = '✕ Cancel';
        btn.style.background = 'rgba(' + rgb + ',.12)';
        btn.style.color = color;
        btn.style.border = '1px solid rgba(' + rgb + ',.25)';
    } else {
        btn.textContent = 'Hire Now →';
        btn.style.background = color;
        btn.style.color = '#ffffff';
        btn.style.border = 'none';
    }
}
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $dep->name }} — UNIT</title>
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

/* ── SHELL (identical to /desk/{slug}, /workers/{slug}/overview) ── */
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

.ob-page{display:flex;flex:1;overflow:hidden;justify-content:center}
.wd-card-area{width:100%;max-width:760px;margin:12px;background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;overflow-y:auto;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.wd-main{padding:28px 32px 60px}

.wd-back{font-size:12px;font-weight:600;color:var(--db-text-muted);text-decoration:none;display:inline-block;margin-bottom:16px}
.wd-back:hover{color:var(--db-text)}

.wd-identity{display:flex;align-items:center;gap:14px;margin-bottom:22px}
.wd-avatar{width:52px;height:52px;border-radius:14px;object-fit:cover;flex-shrink:0}
.wd-avatar-fallback{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:19px;font-weight:800;flex-shrink:0;background:var(--db-chip);color:var(--db-text)}
.wd-name{font-size:1.4rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);line-height:1.15}
.wd-role{font-size:13px;color:var(--db-text-muted);margin-top:2px}
.wd-status{display:inline-flex;align-items:center;gap:6px;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:5px 12px;border-radius:99px;margin-left:auto;flex-shrink:0}
.wd-status.active{background:rgba(34,197,94,.12);color:#22c55e}
.wd-status.paused{background:rgba(245,158,11,.12);color:#f59e0b}
.wd-status.stopped,.wd-status.decommissioned{background:rgba(239,68,68,.12);color:#ef4444}
.wd-status-dot{width:6px;height:6px;border-radius:50%;background:currentColor}

.wd-card{background:var(--db-bg);border:1px solid var(--db-border);border-radius:16px;padding:20px;margin-bottom:16px}
.wd-card-title{font-size:13.5px;font-weight:700;color:var(--db-text);margin-bottom:14px}

.wd-banner{display:flex;align-items:center;justify-content:space-between;gap:12px;border-radius:12px;padding:14px 16px;margin-bottom:12px}
.wd-banner.warn{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25)}
.wd-banner.notice{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25)}
.wd-banner-title{font-size:12.5px;font-weight:700;color:var(--db-text)}
.wd-banner-body{font-size:11.5px;color:var(--db-text-muted);margin-top:2px}
.wd-banner-action{flex-shrink:0;font-size:11.5px;font-weight:700;color:var(--db-text);text-decoration:none;white-space:nowrap;padding:7px 12px;border-radius:8px;background:var(--db-card);border:1px solid var(--db-border)}

.wd-paywall-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px}
.wd-paywall-title{font-size:13.5px;font-weight:700;color:var(--db-text)}
.wd-paywall-body{font-size:12px;color:var(--db-text-muted);margin-top:3px}
.wd-paywall-count{font-size:1.4rem;font-weight:900;color:var(--db-text);text-align:right}
.wd-paywall-count-label{font-size:10.5px;color:var(--db-text-muted);text-align:right}
.wd-plans{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.wd-plan{border:1px solid var(--db-border);border-radius:12px;padding:14px;position:relative}
.wd-plan.recommended{border-color:#F5C518;border-width:1.5px;background:rgba(245,197,24,.06)}
.wd-plan-badge{position:absolute;top:-10px;left:12px;font-size:9px;font-weight:700;background:#F5C518;color:#0D0D0D;padding:3px 8px;border-radius:99px}
.wd-plan-name{font-size:12.5px;font-weight:700;color:var(--db-text)}
.wd-plan-tagline{font-size:10.5px;color:var(--db-text-muted);margin-top:2px}
.wd-plan-price{font-size:1.3rem;font-weight:900;color:var(--db-text);margin:8px 0 2px}
.wd-plan.recommended .wd-plan-price{color:#B8890A}
.wd-plan-price span{font-size:11px;font-weight:500;color:var(--db-text-muted)}
.wd-plan-limit{font-size:11px;color:var(--db-text-muted);margin-bottom:10px}
.wd-plan-btn{width:100%;padding:9px;border-radius:8px;border:none;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text)}

.wd-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.wd-stat{background:var(--db-card);border:1px solid var(--db-border);border-radius:12px;padding:14px;text-align:center}
.wd-stat-num{font-size:1.4rem;font-weight:900;color:var(--db-text)}
.wd-stat-label{font-size:10px;color:var(--db-text-muted);margin-top:2px}

.wd-links{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.wd-link{display:flex;align-items:center;gap:10px;padding:12px;border-radius:10px;border:1px solid var(--db-border);text-decoration:none}
.wd-link:hover{background:var(--db-chip)}
.wd-link-icon{width:32px;height:32px;border-radius:8px;background:var(--db-chip);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.wd-link-icon svg{stroke:var(--db-text);stroke-width:1.8;fill:none}
.wd-link-label{font-size:12.5px;font-weight:600;color:var(--db-text)}

.wd-manage-row{display:flex;gap:10px;flex-wrap:wrap}
.wd-manage-btn{font-size:12px;font-weight:600;padding:9px 16px;border-radius:9px;border:1px solid var(--db-border);background:var(--db-card);color:var(--db-text);cursor:pointer;font-family:inherit}
.wd-manage-btn:hover{background:var(--db-chip)}
.wd-manage-btn.danger{color:#ef4444;border-color:rgba(239,68,68,.3)}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow-x:hidden;overflow-y:auto;height:auto;width:100%}
  .ob-shell{height:auto;overflow:visible;width:100%}
  .ob-shell,.ob-shell *{min-width:0}
  .ob-topbar{height:auto;padding:12px 16px;flex-wrap:wrap;gap:6px}
  .ob-topbar-logo{font-size:18px}
  .ob-topbar-email{display:none}
  .ob-page{display:block;height:auto;overflow:visible;width:100%}
  .ob-menu-mobile-links{display:block}
  .wd-main{padding:16px}
  .wd-card-area{margin:0;border-radius:0;border:none;box-shadow:none;overflow-y:visible}
  .wd-plans,.wd-stats,.wd-links{grid-template-columns:1fr}
  .wd-status{margin-left:0}
  .wd-identity{flex-wrap:wrap}
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
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('workers.memory',$dep->worker_slug)],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('workers.templates',['slug'=>$dep->worker_slug])],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('workers.rules',$dep->worker_slug)],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('workers.fast-track.page',$dep->worker_slug)],
  ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('workers.connect',$dep->worker_slug)],
  ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('workers.billing',$dep->worker_slug)],
  ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('transactions').'?deployment='.$dep->id],
];
$profileImg = $registryRow?->profile_image ? asset('storage/' . $registryRow->profile_image) : null;
@endphp

<div class="ob-shell">

{{-- ══ TOP BAR — identical to /desk/{slug}, /workers/{slug}/overview ══ --}}
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
          @foreach($sidebarLinks as [$lbl,$ico,$href])
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
  <div class="wd-card-area">
  <main class="wd-main">

    <a href="{{ route('dashboard') }}" class="wd-back">← Back to Dashboard</a>

    <div class="wd-identity">
      @if($profileImg)
        <img src="{{ $profileImg }}" alt="{{ $dep->name }}" class="wd-avatar">
      @else
        <div class="wd-avatar-fallback">{{ strtoupper(substr($dep->name, 0, 1)) }}</div>
      @endif
      <div>
        <div class="wd-name">{{ $dep->name }}</div>
        <div class="wd-role">{{ $registryRow->description ?? ucfirst($dep->worker_slug).' Specialist' }}</div>
      </div>
      <span class="wd-status {{ $dep->status }}">
        <span class="wd-status-dot"></span>
        {{ $dep->status === 'active' ? 'On Shift' : ucfirst($dep->status) }}
      </span>
    </div>

    {{-- ── Trial exhausted paywall ── --}}
    @if($isTrialExhausted)
    <div class="wd-card">
      <div class="wd-paywall-head">
        <div>
          <div class="wd-paywall-title">Trial {{ $trialReason === 'expired' ? 'Expired' : 'Complete' }}</div>
          <div class="wd-paywall-body">
            @if($trialReason === 'expired')
              Your 14-day trial period has ended. Subscribe to keep {{ $dep->name }} running.
            @else
              You've used all {{ $billing?->trial_transactions_limit ?? 25 }} free {{ $unitLabel }}. Choose a plan to continue.
            @endif
          </div>
        </div>
        <div>
          <div class="wd-paywall-count">{{ $billing?->trial_transactions_used ?? 0 }}/{{ $billing?->trial_transactions_limit ?? 25 }}</div>
          <div class="wd-paywall-count-label">{{ $unitLabel }} used</div>
        </div>
      </div>
      @if($pricingTiers->isNotEmpty())
      <div class="wd-plans">
        @foreach($pricingTiers as $tier)
        @php $isRecommended = $tier->plan_slug === 'pro'; @endphp
        <div class="wd-plan {{ $isRecommended ? 'recommended' : '' }}">
          @if($isRecommended)<span class="wd-plan-badge">Most popular</span>@endif
          <div class="wd-plan-name">{{ $tier->display_name }}</div>
          <div class="wd-plan-tagline">{{ $tier->tagline }}</div>
          <div class="wd-plan-price">${{ number_format($tier->monthly_flat_rate, 0) }}<span>/month</span></div>
          <div class="wd-plan-limit">{{ $tier->transaction_limit ? number_format($tier->transaction_limit).' '.$unitLabel.'/month' : 'Unlimited '.$unitLabel }}</div>
          <form method="POST" action="{{ route('billing.checkout', $dep->id) }}">
            @csrf
            <input type="hidden" name="plan" value="{{ $tier->plan_slug }}">
            <button type="submit" class="wd-plan-btn">Subscribe — ${{ number_format($tier->monthly_flat_rate, 0) }}/mo</button>
          </form>
        </div>
        @endforeach
      </div>
      @else
      <p style="font-size:12px;color:var(--db-text-muted);text-align:center">Contact us to set up your subscription — {{ config('services.unit.support_email') }}</p>
      @endif
    </div>
    @endif

    {{-- ── System notices ── --}}
    @if($workerStopped)
    <div class="wd-banner warn">
      <div><div class="wd-banner-title">Worker is {{ $dep->status }}</div><div class="wd-banner-body">Not processing any emails right now.</div></div>
      <form method="POST" action="{{ route('workers.status', $dep->id) }}">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="active">
        <button type="submit" class="wd-banner-action">Resume →</button>
      </form>
    </div>
    @endif

    @if($isCanceled)
    <div class="wd-card">
      <div class="wd-card-title">Subscription Canceled</div>
      <p style="font-size:12px;color:var(--db-text-muted);margin-bottom:12px">This worker is stopped. Reactivate by choosing a plan — you'll go through a fresh checkout and your worker will resume immediately.</p>
      @if($subscriptionPlans->isNotEmpty())
      <div class="wd-manage-row">
        @foreach($subscriptionPlans as $sp)
        <form method="POST" action="{{ route('billing.reactivate', $dep->id) }}">
          @csrf
          <input type="hidden" name="plan" value="{{ $sp->plan_slug }}">
          <button type="submit" class="wd-manage-btn">{{ $sp->display_name }}{{ $sp->monthly_flat_rate > 0 ? ' · $'.number_format($sp->monthly_flat_rate,0).'/mo' : ' · Custom' }}</button>
        </form>
        @endforeach
      </div>
      @endif
    </div>
    @endif

    @foreach($watchInactiveInboxes as $inactiveInbox)
    <div class="wd-banner warn">
      <div><div class="wd-banner-title">Gmail disconnected</div><div class="wd-banner-body">{{ $inactiveInbox->gmail_address }} is not watching for new mail.</div></div>
      <a href="{{ route('workers.connect', $dep->worker_slug) }}" class="wd-banner-action">Reconnect →</a>
    </div>
    @endforeach

    @if($billingAlert)
    <div class="wd-banner warn">
      <div><div class="wd-banner-title">Payment past due</div><div class="wd-banner-body">Worker may be suspended soon.</div></div>
      <a href="{{ route('workers.billing', $dep->worker_slug) }}" class="wd-banner-action">Fix billing →</a>
    </div>
    @endif

    @foreach($otherViolations as $violation)
    <div class="wd-banner warn">
      <div style="flex:1">
        <div class="wd-banner-title">{{ $violation['title'] ?? 'Attention needed' }}</div>
        <div class="wd-banner-body">{{ $violation['description'] ?? '' }}</div>
      </div>
      @if(!empty($violation['cta_url']))
        <a href="{{ $violation['cta_url'] }}" class="wd-banner-action">{{ $violation['cta_label'] ?? 'Resolve' }} →</a>
      @endif
    </div>
    @endforeach

    @unless($productionReadiness['ready'])
    <div class="wd-banner notice">
      <div><div class="wd-banner-title">{{ $productionReadiness['title'] }}</div><div class="wd-banner-body">{{ $productionReadiness['body'] }}</div></div>
      <a href="{{ route('workers.connect', $dep->worker_slug) }}" class="wd-banner-action">{{ $productionReadiness['connect_label'] }} →</a>
    </div>
    @endunless

    {{-- ── Activity ── --}}
    <div class="wd-card">
      <div class="wd-card-title">Activity</div>
      <div class="wd-stats">
        <div class="wd-stat"><div class="wd-stat-num">{{ $txCount }}</div><div class="wd-stat-label">Total processed</div></div>
        <div class="wd-stat"><div class="wd-stat-num">{{ $pendingReview }}</div><div class="wd-stat-label">Awaiting review</div></div>
        <div class="wd-stat"><div class="wd-stat-num" style="color:{{ $stuckCount > 0 ? '#ef4444' : 'var(--db-text)' }}">{{ $stuckCount }}</div><div class="wd-stat-label">Stuck / delayed</div></div>
      </div>
    </div>

    {{-- ── Connected accounts ── --}}
    <div class="wd-card">
      <div class="wd-card-title">Connected accounts</div>
      @if($connectedInboxes->isNotEmpty())
        @foreach($connectedInboxes as $inbox)
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--db-border)' : '' }}">
          <span style="width:8px;height:8px;border-radius:50%;background:{{ $inbox->watch_active ? '#22c55e' : '#ef4444' }};flex-shrink:0"></span>
          <span style="font-size:12.5px;color:var(--db-text)">{{ $inbox->gmail_address }}</span>
        </div>
        @endforeach
      @else
        <p style="font-size:12px;color:var(--db-text-muted)">No accounts connected yet.</p>
      @endif
    </div>

    {{-- ── Configure quick links ── --}}
    <div class="wd-card">
      <div class="wd-card-title">Configure</div>
      <div class="wd-links">
        <a href="{{ route('workers.configure', $dep->worker_slug) }}" class="wd-link">
          <div class="wd-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg></div>
          <span class="wd-link-label">Configure</span>
        </a>
        <a href="{{ route('workers.memory', $dep->worker_slug) }}" class="wd-link">
          <div class="wd-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg></div>
          <span class="wd-link-label">Memory</span>
        </a>
        <a href="{{ route('workers.rules', $dep->worker_slug) }}" class="wd-link">
          <div class="wd-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></div>
          <span class="wd-link-label">Rules</span>
        </a>
        <a href="{{ route('workers.templates', $dep->worker_slug) }}" class="wd-link">
          <div class="wd-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
          <span class="wd-link-label">Templates</span>
        </a>
      </div>
    </div>

    {{-- ── Manage worker ── --}}
    <div class="wd-card" style="margin-bottom:0">
      <div class="wd-card-title">Manage worker</div>
      <div class="wd-manage-row">
        @if($dep->status === 'active')
        <form method="POST" action="{{ route('workers.status', $dep->id) }}">
          @csrf @method('PATCH')
          <input type="hidden" name="status" value="paused">
          <button type="submit" class="wd-manage-btn">Pause worker</button>
        </form>
        @elseif($dep->status === 'paused')
        <form method="POST" action="{{ route('workers.status', $dep->id) }}">
          @csrf @method('PATCH')
          <input type="hidden" name="status" value="active">
          <button type="submit" class="wd-manage-btn">Resume worker</button>
        </form>
        @endif
        <form method="POST" action="{{ route('workers.destroy', $dep->id) }}" onsubmit="return confirm('Remove {{ addslashes($dep->name) }}? This cannot be undone.')">
          @csrf @method('DELETE')
          <button type="submit" class="wd-manage-btn danger">Remove worker</button>
        </form>
      </div>
    </div>

  </main>
  </div>
</div>{{-- ob-page --}}
</div>{{-- ob-shell --}}

<x-self-learn pageKey="dashboard.worker-detail" />

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

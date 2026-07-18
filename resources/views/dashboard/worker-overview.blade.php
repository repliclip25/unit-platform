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
html,body{min-height:100%}
body{font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased}

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
body{background:var(--db-bg);color:var(--db-text)}

.wo-topbar{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid var(--db-border)}
.wo-logo{font-size:20px;font-weight:900;letter-spacing:-.04em;color:var(--db-text);text-decoration:none}
.wo-topbar-right{display:flex;align-items:center;gap:12px}
.wo-back{font-size:12px;font-weight:600;color:var(--db-text-muted);text-decoration:none}
.wo-back:hover{color:var(--db-text)}
.wo-theme-toggle{width:36px;height:20px;border-radius:10px;border:none;cursor:pointer;position:relative;background:var(--db-chip)}
.wo-theme-toggle::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:var(--db-invert-bg);transition:transform .2s ease}
[data-theme="dark"] .wo-theme-toggle::after{transform:translateX(16px)}

.wo-wrap{max-width:760px;margin:0 auto;padding:0 20px 60px}

.wo-hero{position:relative;height:180px;border-radius:0 0 20px 20px;overflow:hidden;margin-bottom:52px;background:var(--db-chip)}
.wo-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.wo-hero-fade{position:absolute;inset:0;background:linear-gradient(to top, var(--db-bg) 0%, transparent 60%)}
.wo-hero-avatar{position:absolute;bottom:-40px;left:24px;width:80px;height:80px;border-radius:50%;border:4px solid var(--db-bg);overflow:hidden;background:var(--db-card)}
.wo-hero-avatar img{width:100%;height:100%;object-fit:cover}

.wo-identity{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:10px}
.wo-name{font-size:1.5rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.wo-role{font-size:13px;color:var(--db-text-muted);margin-top:2px}
.wo-status{display:inline-flex;align-items:center;gap:6px;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:5px 12px;border-radius:99px}
.wo-status.active{background:rgba(34,197,94,.12);color:#22c55e}
.wo-status.paused{background:rgba(245,158,11,.12);color:#f59e0b}
.wo-status-dot{width:6px;height:6px;border-radius:50%;background:currentColor}

.wo-card{background:var(--db-card);border:1px solid var(--db-border);border-radius:16px;padding:20px;margin-bottom:16px}
.wo-card-title{font-size:13.5px;font-weight:700;color:var(--db-text);margin-bottom:14px}

/* Paywall */
.wo-paywall{border-color:rgba(245,197,24,.35)}
.wo-paywall-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:16px}
.wo-paywall-title{font-size:13.5px;font-weight:700;color:#F5C518}
.wo-paywall-body{font-size:12px;color:var(--db-text-muted);margin-top:3px}
.wo-paywall-count{font-size:1.4rem;font-weight:900;color:#F5C518;text-align:right}
.wo-paywall-count-label{font-size:10.5px;color:var(--db-text-muted);text-align:right}
.wo-plans{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.wo-plan{border:1px solid var(--db-border);border-radius:12px;padding:14px;position:relative}
.wo-plan.recommended{border-color:#F5C518;background:rgba(245,197,24,.05)}
.wo-plan-badge{position:absolute;top:-10px;left:12px;font-size:9px;font-weight:700;background:#F5C518;color:#0D0D0D;padding:3px 8px;border-radius:99px}
.wo-plan-name{font-size:12.5px;font-weight:700;color:var(--db-text)}
.wo-plan-tagline{font-size:10.5px;color:var(--db-text-muted);margin-top:2px}
.wo-plan-price{font-size:1.3rem;font-weight:900;color:var(--db-text);margin:8px 0 2px}
.wo-plan-price span{font-size:11px;font-weight:500;color:var(--db-text-muted)}
.wo-plan-limit{font-size:11px;color:var(--db-text-muted);margin-bottom:10px}
.wo-plan-btn{width:100%;padding:9px;border-radius:8px;border:none;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text)}
.wo-plan.recommended .wo-plan-btn{background:#F5C518;color:#0D0D0D}

/* Banners */
.wo-banner{display:flex;align-items:flex-start;gap:12px;border-radius:12px;padding:14px 16px;margin-bottom:12px}
.wo-banner.warn{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25)}
.wo-banner.notice{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25)}
.wo-banner-title{font-size:12.5px;font-weight:700;color:var(--db-text)}
.wo-banner-body{font-size:11.5px;color:var(--db-text-muted);margin-top:2px}
.wo-banner-action{flex-shrink:0;font-size:11.5px;font-weight:700;color:var(--db-text);text-decoration:none;white-space:nowrap}

/* Stats */
.wo-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.wo-stat{background:var(--db-bg);border:1px solid var(--db-border);border-radius:12px;padding:14px;text-align:center}
.wo-stat-num{font-size:1.4rem;font-weight:900;color:var(--db-text)}
.wo-stat-label{font-size:10px;color:var(--db-text-muted);margin-top:2px}

/* Links grid */
.wo-links{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.wo-link{display:flex;align-items:center;gap:10px;padding:12px;border-radius:10px;border:1px solid var(--db-border);text-decoration:none}
.wo-link:hover{background:var(--db-chip)}
.wo-link-icon{width:32px;height:32px;border-radius:8px;background:var(--db-chip);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.wo-link-icon svg{stroke:var(--db-text);stroke-width:1.8;fill:none}
.wo-link-label{font-size:12.5px;font-weight:600;color:var(--db-text)}

/* Manage row */
.wo-manage-row{display:flex;gap:10px;flex-wrap:wrap}
.wo-manage-btn{font-size:12px;font-weight:600;padding:9px 16px;border-radius:9px;border:1px solid var(--db-border);background:var(--db-bg);color:var(--db-text);cursor:pointer;font-family:inherit}
.wo-manage-btn:hover{background:var(--db-chip)}
.wo-manage-btn.danger{color:#ef4444;border-color:rgba(239,68,68,.3)}
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
  $unitLabel = $contract ? ($contract->billing()['unit_label_plural'] ?? 'transactions') : 'transactions';
@endphp

<div class="wo-topbar">
  <a href="{{ url('/') }}" class="wo-logo">UNIT</a>
  <div class="wo-topbar-right">
    <a href="{{ route('dashboard') }}" class="wo-back">← Back to Dashboard</a>
    <button type="button" class="wo-theme-toggle" id="theme-toggle" title="Toggle dark/light mode" aria-label="Toggle theme"></button>
  </div>
</div>

<div class="wo-wrap">

  <div class="wo-hero">
    @if($coverImg)<img src="{{ $coverImg }}" class="wo-hero-img" alt="">@endif
    <div class="wo-hero-fade"></div>
    <div class="wo-hero-avatar">
      @if($profileImg)<img src="{{ $profileImg }}" alt="">@endif
    </div>
  </div>

  <div class="wo-identity" style="padding-left:24px">
    <div>
      <div class="wo-name">{{ $dep->name }}</div>
      <div class="wo-role">{{ $registryRow->description ?? ucfirst($dep->worker_slug).' Specialist' }}</div>
    </div>
    <span class="wo-status {{ $dep->status }}"><span class="wo-status-dot"></span> {{ $dep->status === 'active' ? 'On Shift' : 'Paused' }}</span>
  </div>

  @if($isTrialExhausted)
  <div class="wo-card wo-paywall">
    <div class="wo-paywall-head">
      <div>
        <div class="wo-paywall-title">Trial {{ $trialReason === 'expired' ? 'Expired' : 'Complete' }}</div>
        <div class="wo-paywall-body">
          @if($trialReason === 'expired')
            Your 14-day trial period has ended. Subscribe to keep {{ $dep->name }} running.
          @else
            You've used all {{ $billing?->trial_transactions_limit ?? 25 }} free {{ $unitLabel }}. Choose a plan to continue.
          @endif
        </div>
      </div>
      <div>
        <div class="wo-paywall-count">{{ $billing?->trial_transactions_used ?? 0 }}/{{ $billing?->trial_transactions_limit ?? 25 }}</div>
        <div class="wo-paywall-count-label">{{ $unitLabel }} used</div>
      </div>
    </div>
    @if($pricingTiers->isNotEmpty())
    <div class="wo-plans">
      @foreach($pricingTiers as $tier)
      @php $isRecommended = $tier->plan_slug === 'pro'; @endphp
      <div class="wo-plan {{ $isRecommended ? 'recommended' : '' }}">
        @if($isRecommended)<span class="wo-plan-badge">Most popular</span>@endif
        <div class="wo-plan-name">{{ $tier->display_name }}</div>
        <div class="wo-plan-tagline">{{ $tier->tagline }}</div>
        <div class="wo-plan-price">${{ number_format($tier->monthly_flat_rate, 0) }}<span>/month</span></div>
        <div class="wo-plan-limit">{{ $tier->transaction_limit ? number_format($tier->transaction_limit).' '.$unitLabel.'/month' : 'Unlimited '.$unitLabel }}</div>
        <form method="POST" action="{{ route('billing.checkout', $dep->id) }}">
          @csrf
          <input type="hidden" name="plan" value="{{ $tier->plan_slug }}">
          <button type="submit" class="wo-plan-btn">Subscribe — ${{ number_format($tier->monthly_flat_rate, 0) }}/mo</button>
        </form>
      </div>
      @endforeach
    </div>
    @else
    <p style="font-size:12px;color:var(--db-text-muted);text-align:center">Contact us to set up your subscription — {{ config('services.unit.support_email') }}</p>
    @endif
  </div>
  @endif

  @foreach($otherViolations as $violation)
  <div class="wo-banner warn">
    <div style="flex:1">
      <div class="wo-banner-title">{{ $violation['title'] ?? 'Attention needed' }}</div>
      <div class="wo-banner-body">{{ $violation['description'] ?? '' }}</div>
    </div>
    @if(!empty($violation['cta_url']))
      <a href="{{ $violation['cta_url'] }}" class="wo-banner-action">{{ $violation['cta_label'] ?? 'Resolve' }} →</a>
    @endif
  </div>
  @endforeach

  @unless($productionReadiness['ready'])
  <div class="wo-banner notice">
    <div style="flex:1">
      <div class="wo-banner-title">{{ $productionReadiness['title'] }}</div>
      <div class="wo-banner-body">{{ $productionReadiness['body'] }}</div>
    </div>
    <a href="{{ route('workers.connect', $dep->worker_slug) }}" class="wo-banner-action">{{ $productionReadiness['connect_label'] }} →</a>
  </div>
  @endunless

  <div class="wo-card">
    <div class="wo-card-title">Activity</div>
    <div class="wo-stats">
      <div class="wo-stat"><div class="wo-stat-num">{{ $txCount }}</div><div class="wo-stat-label">Total processed</div></div>
      <div class="wo-stat"><div class="wo-stat-num" style="color:#f59e0b">{{ $pendingReview }}</div><div class="wo-stat-label">Awaiting review</div></div>
      <div class="wo-stat"><div class="wo-stat-num" style="color:{{ $stuckCount > 0 ? '#ef4444' : 'var(--db-text)' }}">{{ $stuckCount }}</div><div class="wo-stat-label">Stuck / delayed</div></div>
    </div>
  </div>

  <div class="wo-card">
    <div class="wo-card-title">Connected accounts</div>
    @if($connectedInboxes->isNotEmpty())
      @foreach($connectedInboxes as $inbox)
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0;{{ !$loop->last ? 'border-bottom:1px solid var(--db-border)' : '' }}">
        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;flex-shrink:0"></span>
        <span style="font-size:12.5px;color:var(--db-text)">{{ $inbox->gmail_address ?? $inbox->email ?? 'Connected account' }}</span>
      </div>
      @endforeach
    @else
      <p style="font-size:12px;color:var(--db-text-muted)">No accounts connected yet.</p>
    @endif
  </div>

  <div class="wo-card">
    <div class="wo-card-title">Configure</div>
    <div class="wo-links">
      <a href="{{ route('workers.configure', $dep->worker_slug) }}" class="wo-link">
        <div class="wo-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg></div>
        <span class="wo-link-label">Configure</span>
      </a>
      <a href="{{ route('workers.memory', $dep->worker_slug) }}" class="wo-link">
        <div class="wo-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg></div>
        <span class="wo-link-label">Memory</span>
      </a>
      <a href="{{ route('workers.rules', $dep->worker_slug) }}" class="wo-link">
        <div class="wo-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></div>
        <span class="wo-link-label">Rules</span>
      </a>
      <a href="{{ route('workers.templates', $dep->worker_slug) }}" class="wo-link">
        <div class="wo-link-icon"><svg width="15" height="15" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
        <span class="wo-link-label">Templates</span>
      </a>
    </div>
  </div>

  <div class="wo-card">
    <div class="wo-card-title">Manage worker</div>
    <div class="wo-manage-row">
      <form method="POST" action="{{ route('workers.status', $dep->id) }}">
        @csrf @method('PATCH')
        <input type="hidden" name="status" value="{{ $dep->status === 'active' ? 'paused' : 'active' }}">
        <button type="submit" class="wo-manage-btn">{{ $dep->status === 'active' ? 'Pause worker' : 'Resume worker' }}</button>
      </form>
      <form method="POST" action="{{ route('workers.destroy', $dep->id) }}" onsubmit="return confirm('Remove {{ $dep->name }}? This cannot be undone.')">
        @csrf @method('DELETE')
        <button type="submit" class="wo-manage-btn danger">Remove worker</button>
      </form>
    </div>
  </div>

</div>

<script>
document.getElementById('theme-toggle').addEventListener('click', function () {
  var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('unit-theme-v2', next);
});
</script>
</body>
</html>

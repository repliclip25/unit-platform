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
body{font-family:'Inter',sans-serif;background:#F4F3F1;color:#0D0D0D;-webkit-font-smoothing:antialiased}

/* ── OUTER SHELL ── */
.ob-shell{display:flex;flex-direction:column;height:100vh;overflow:hidden}

/* ── TOP BAR ── */
.ob-topbar{background:#F4F3F1;display:flex;align-items:center;justify-content:space-between;padding:0 24px 0 20px;height:44px;flex-shrink:0;border-bottom:1px solid #E5E7EB}
.ob-topbar-left{display:flex;align-items:center;gap:14px}
.ob-topbar-name{font-size:12.5px;font-weight:700;color:#0D0D0D}
.ob-topbar-email{font-size:11px;color:#9CA3AF}
.ob-topbar-right{display:flex;align-items:center;gap:10px}
.ob-token-badge{font-size:10px;font-weight:600;color:#6B7280;background:#ECEAE6;border-radius:5px;padding:2px 7px;white-space:nowrap}
.ob-topbar-link{font-size:11px;font-weight:600;color:#9CA3AF;text-decoration:none}
.ob-topbar-link:hover{color:#0D0D0D}

/* ── PAGE: sidebar + card area ── */
.ob-page{display:grid;grid-template-columns:260px 1fr;flex:1;overflow:hidden}

/* ── SIDEBAR ── */
.ob-sidebar{background:#F4F3F1;display:flex;flex-direction:column;overflow-y:auto}

/* Logo */
.ob-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;padding:20px 24px 8px;flex-shrink:0}

/* Worker steps */
.ob-steps{display:flex;flex-direction:column;padding:0 24px;flex:1}
.ob-workers-hd{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}
.ob-hire-btn{font-size:10px;font-weight:700;color:#0D0D0D;background:#fff;border:1px solid #E5E7EB;border-radius:6px;padding:3px 8px;text-decoration:none}
.ob-hire-btn:hover{background:#ECEAE6}

.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative;text-decoration:none;color:inherit}
.ob-step:not(:last-child) .ob-step-rail::after{content:'';position:absolute;left:13px;top:30px;width:2px;height:calc(100% - 6px);background:#DCDCDC;border-radius:2px}
.ob-step.done:not(:last-child) .ob-step-rail::after{background:#0D0D0D}
.ob-step-rail{position:relative;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding-bottom:20px}
.ob-step:last-child .ob-step-rail{padding-bottom:0}
.ob-step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;position:relative;z-index:1;flex-shrink:0;overflow:hidden}
.ob-step.pending .ob-step-num{background:#E8E7E4;color:#888;border:1.5px solid #DCDCDC}
.ob-step.active  .ob-step-num{background:#0D0D0D;color:#fff;box-shadow:0 0 0 4px rgba(0,0,0,.1)}
.ob-step.done    .ob-step-num{background:#0D0D0D;color:#fff}
.ob-step-body{padding-top:4px;padding-bottom:20px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:13px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-step.pending .ob-step-label{color:#6B7280}
.ob-step-desc{font-size:11px;color:#9CA3AF;margin-top:2px;line-height:1.4;display:flex;align-items:center;gap:5px}
.ob-step.active .ob-step-desc{color:#374151}
.ob-step.active .ob-step-body{background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:10px 14px;margin-right:-4px}

/* Links section below workers */
.ob-links-section{padding:16px 24px 8px;border-top:1px solid #E5E7EB;flex-shrink:0}
.ob-links-hd{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:8px}
.ob-link{display:flex;align-items:center;gap:9px;padding:6px 10px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:500;color:#6B7280;transition:all .12s}
.ob-link:hover{background:#fff;color:#0D0D0D}
.ob-link svg{width:13px;height:13px;stroke:currentColor;stroke-width:1.8;fill:none;flex-shrink:0}

/* Security footer */
.ob-security{margin:8px 24px 16px;padding:13px 15px;border-radius:12px;background:#ECEAE6;border:1px solid #DCDCDC;flex-shrink:0}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:4px}
.ob-security-row svg{width:12px;height:12px;stroke:#6B7280;flex-shrink:0;fill:none}
.ob-security-title{font-size:11.5px;font-weight:700;color:#0D0D0D}
.ob-security p{font-size:10.5px;color:#6B7280;line-height:1.55}

/* ── CARD AREA ── */
.ob-card-area{display:flex;align-items:center;justify-content:center;padding:20px 24px 20px 12px;overflow:hidden}
.ob-card{
  display:grid;grid-template-columns:1fr 320px;
  width:100%;height:100%;max-height:calc(100vh - 84px);
  border-radius:20px;overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);
  border:1px solid rgba(0,0,0,.07);
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
.ob-profile{background:#fff;border-left:1px solid #F0F0F0;padding:20px;display:flex;flex-direction:column;overflow-y:auto;position:relative;z-index:1}
.ob-profile::-webkit-scrollbar{width:3px}
.ob-profile::-webkit-scrollbar-thumb{background:#E8E7E4;border-radius:2px}
.emp-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:6px}
.emp-name{font-size:1.5rem;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;line-height:1}
.emp-role{font-size:12px;color:#374151;margin-top:3px;margin-bottom:12px}
.emp-divider{border:none;border-top:1px solid #F0F0F0;margin:0 0 12px}

/* Activity */
.ob-act-hd{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}
.ob-sc-onshift{display:flex;align-items:center;gap:5px;font-size:9px;font-weight:700;color:#15803D;letter-spacing:.08em;text-transform:uppercase;background:#DCFCE7;border-radius:99px;padding:3px 8px}
.ob-sc-onshift-dot{width:5px;height:5px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
.ob-sc-feed{display:flex;flex-direction:column;gap:7px;margin-bottom:8px}
.ob-sc-feed-item{display:flex;gap:9px;align-items:flex-start}
.ob-sc-feed-time{font-size:10px;color:#9CA3AF;font-weight:600;white-space:nowrap;padding-top:3px;min-width:44px}
.ob-sc-feed-dot{width:20px;height:20px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:800;color:#fff}
.ob-sc-feed-text{font-size:12px;color:#374151;font-weight:600;line-height:1.35}
.ob-sc-feed-sub{font-size:10.5px;color:#9CA3AF;margin-top:1px}
.ob-sc-view-link{font-size:11px;color:#9CA3AF;font-weight:600;text-decoration:none;display:block;margin-bottom:2px}
.ob-sc-view-link:hover{color:#0D0D0D}

/* Draft */
.sc-draft-wrap{flex:1;display:flex;flex-direction:column;overflow:hidden;border-top:1px solid #E8EAED;margin:10px -20px 0}
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
  html,body{overflow:auto;height:auto}
  .ob-page{grid-template-columns:1fr;height:auto;overflow:visible}
  .ob-sidebar{flex-direction:row;align-items:center;justify-content:space-between;padding:0;overflow:visible}
  .ob-userbar{display:none}
  .ob-logo{padding:14px 20px 14px;font-size:18px;margin-bottom:0;border-bottom:none}
  .ob-steps{display:none}
  .ob-links-section{display:none}
  .ob-security{display:none}
  .ob-card-area{padding:16px;overflow:visible;height:auto;align-items:flex-start}
  .ob-card{display:flex;flex-direction:column;width:100%;height:auto;max-height:none;box-shadow:0 2px 12px rgba(0,0,0,.08);position:static}
  .ob-card-bg{display:none}
  .ob-hero{display:flex;flex-direction:column;min-height:unset;background:#fff}
  .ob-hero-content{position:static;background:#fff;padding:20px;max-width:100%;height:auto;overflow-y:visible;order:1}
  .ob-hero-fade{display:none}
  .ob-bubble{display:none}
  .ob-h1{font-size:1.45rem}
  .ob-profile{border-left:none;border-top:1px solid #F0F0F0;padding:16px}
}
</style>
</head>
<body>

@php
// Pipeline stage descriptions matching AVA's ReadEmailJob → ClassifyEmailJob → MemoryLookupJob → SelectTemplateJob → DraftEmailJob → PushToGmailJob
$activityMap = [
  'ingesting'   => ['label'=>'Renewal request detected',        'sub'=>'Email received from inbox',                       'color'=>'#6366f1'],
  'reading'     => ['label'=>'Reading incoming email',          'sub'=>'Parsing subject, body, sender',                   'color'=>'#6366f1'],
  'classifying' => ['label'=>'Classifying email',               'sub'=>'Identifying renewal type & urgency',              'color'=>'#f59e0b'],
  'memory'      => ['label'=>'Looking up client memory',        'sub'=>'Matching sender to known clients & assets',       'color'=>'#8b5cf6'],
  'template'    => ['label'=>'Selecting best template',         'sub'=>'Matching tone and renewal type',                  'color'=>'#f97316'],
  'drafting'    => ['label'=>'Drafting personalized reply',     'sub'=>'Writing renewal email with client context',        'color'=>'#8b5cf6'],
  'pushing'     => ['label'=>'Pushing draft to Gmail',          'sub'=>'Saving draft to your connected inbox',            'color'=>'#06b6d4'],
  'draft_ready' => ['label'=>'Reply ready for your review',     'sub'=>'Draft saved — awaiting your approval',            'color'=>'#22c55e'],
  'approved'    => ['label'=>'Reply approved & sent',           'sub'=>'Renewal email delivered to customer',             'color'=>'#22c55e'],
  'sent'        => ['label'=>'Renewal email delivered',         'sub'=>'Sent successfully via Gmail',                     'color'=>'#22c55e'],
  'failed'      => ['label'=>'Pipeline error',                  'sub'=>'Something went wrong — needs your attention',     'color'=>'#ef4444'],
  'rejected'    => ['label'=>'Draft rejected',                  'sub'=>'You declined this draft',                         'color'=>'#9CA3AF'],
  'dismissed'   => ['label'=>'Email dismissed',                 'sub'=>'Marked as not a renewal',                         'color'=>'#9CA3AF'],
];
$dotColors = ['#6366f1','#f59e0b','#8b5cf6','#22c55e','#f97316','#06b6d4'];
$apColors  = ['#6366f1','#f59e0b','#22c55e','#f97316','#8b5cf6','#ec4899'];

$previewTx    = $approvals->first();
$previewCl    = $previewTx ? json_decode($previewTx->classify_output??'{}',true) : null;
$previewDraft = $previewTx ? json_decode($previewTx->draft_output??'{}',true)    : null;

$tokenFmt = $tokenTotal >= 1000000
  ? number_format($tokenTotal/1000000,1).'M'
  : number_format($tokenTotal);
@endphp

<div class="ob-shell">

{{-- ══ TOP BAR ══ --}}
<div class="ob-topbar">
  <div class="ob-topbar-left">
    <span class="ob-topbar-name">{{ auth()->user()->name }}</span>
    <span class="ob-topbar-email">{{ auth()->user()->email }}</span>
  </div>
  <div class="ob-topbar-right">
    <span class="ob-token-badge">{{ $tokenFmt }} tokens</span>
    <a href="{{ route('settings.api-keys') }}" class="ob-topbar-link">Settings</a>
    <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf<button type="submit" style="background:none;border:none;padding:0;font-size:11px;font-weight:600;color:#9CA3AF;cursor:pointer;font-family:inherit">Logout</button></form>
  </div>
</div>

<div class="ob-page">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ob-sidebar">

    <div class="ob-logo">UNIT</div>

    {{-- MY WORKERS --}}
    <div class="ob-steps">
      <div class="ob-workers-hd">
        MY WORKERS
        <a href="{{ route('workers.page') }}" class="ob-hire-btn">+ Hire</a>
      </div>

      @foreach($allDeployments as $wd)
      @php
        $wReg  = $registryRows->get($wd->worker_slug);
        $wImg  = ($wd->worker_slug==='ava' && $profileImg) ? $profileImg : ($wReg?->profile_image ? asset('storage/'.$wReg->profile_image) : null);
        $wDot  = $wd->status==='active' ? '#22c55e' : '#f59e0b';
        $wHref = $wd->worker_slug==='ava' ? route('desk.ava') : '#';
        $wRole = $wReg->tagline ?? ucfirst($wd->worker_slug).' Specialist';
        $isActive = $wd->worker_slug==='ava';
      @endphp
      <a href="{{ $wHref }}" class="ob-step {{ $isActive ? 'active' : 'done' }}" style="text-decoration:none">
        <div class="ob-step-rail">
          <div class="ob-step-num" style="{{ !$isActive ? 'background:#E8E7E4;border:none;padding:0' : 'padding:0' }}">
            @if($wImg)
              <img src="{{ $wImg }}" style="width:100%;height:100%;object-fit:cover;display:block" alt="">
            @else
              <span style="font-size:11px;font-weight:800;color:{{ $isActive?'#fff':'#6B7280' }}">{{ strtoupper(substr($wd->worker_slug,0,1)) }}</span>
            @endif
          </div>
        </div>
        <div class="ob-step-body">
          <div class="ob-step-label">{{ strtoupper($wd->worker_slug) }}</div>
          <div class="ob-step-desc">
            <span style="width:5px;height:5px;border-radius:50%;background:{{ $wDot }};flex-shrink:0;display:inline-block;animation:{{ $wd->status==='active'?'pdot 1.4s ease infinite':'none' }}"></span>
            {{ $wRole }}
          </div>
        </div>
      </a>
      @endforeach

      <a href="{{ route('workers.page') }}" class="ob-step pending" style="text-decoration:none;margin-top:4px">
        <div class="ob-step-rail"><div class="ob-step-num" style="background:#F4F3F1;border:1.5px dashed #D1D5DB;color:#9CA3AF;font-size:16px;font-weight:400">+</div></div>
        <div class="ob-step-body"><div class="ob-step-label">Hire a worker</div></div>
      </a>
    </div>

    {{-- Links below workers --}}
    <div class="ob-links-section">
      <div class="ob-links-hd">LINKS</div>
      @foreach([
        ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('memory')],
        ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('workers.templates',['slug'=>'ava'])],
        ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('workers.show','ava')],
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
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;flex-shrink:0">
            @foreach([
              [$incomingCount,'Renewal requests detected','#6366f1'],
              [$incomingCount,'Replies drafted','#8b5cf6'],
              [$waitingCount,'Awaiting your review','#f59e0b'],
              [$completedCount,'Completed today','#22c55e'],
            ] as [$val,$lbl,$clr])
            <div style="background:rgba(255,255,255,.92);border:1px solid rgba(0,0,0,.08);border-radius:10px;padding:11px 13px;backdrop-filter:blur(4px)">
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

      {{-- ── RIGHT PANEL ── --}}
      <div class="ob-profile">
        <div class="emp-eyebrow">On Shift</div>
        <div class="emp-name">AVA</div>
        <div class="emp-role">Renewal Specialist</div>

        <hr class="emp-divider">

        {{-- Live Activity --}}
        <div class="ob-act-hd">
          Live Activity
          <span class="ob-sc-onshift"><span class="ob-sc-onshift-dot"></span> On Shift</span>
        </div>
        <div class="ob-sc-feed">
          @forelse($activity->take(5) as $i => $tx)
          @php
            $am = $activityMap[$tx->status] ?? ['label'=>ucfirst(str_replace('_',' ',$tx->status)),'sub'=>'','color'=>'#9CA3AF'];
            $cl = json_decode($tx->classify_output??'{}',true);
            $txc = $cl['client']??$cl['sender_name']??'';
          @endphp
          <div class="ob-sc-feed-item">
            <span class="ob-sc-feed-time">{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</span>
            <span class="ob-sc-feed-dot" style="background:{{ $dotColors[$i % count($dotColors)] }}">{{ $i+1 }}</span>
            <div>
              <div class="ob-sc-feed-text">{{ $am['label'] }}</div>
              <div class="ob-sc-feed-sub">{{ $txc ?: $am['sub'] }}</div>
            </div>
          </div>
          @empty
          <p style="font-size:12px;color:#9CA3AF">No activity yet — Ava is standing by.</p>
          @endforelse
        </div>
        <a href="{{ route('transactions') }}" class="ob-sc-view-link">View Live Feed →</a>

        {{-- Gmail draft preview --}}
        @if($previewTx)
        <div class="sc-draft-wrap">
          <div class="sc-draft-chrome">
            <span style="width:10px;height:10px;border-radius:50%;background:#FF5F57;display:inline-block;flex-shrink:0"></span>
            <span style="width:10px;height:10px;border-radius:50%;background:#FEBC2E;display:inline-block;flex-shrink:0"></span>
            <span style="width:10px;height:10px;border-radius:50%;background:#28C840;display:inline-block;flex-shrink:0"></span>
            <div style="flex:1;background:#fff;border:1px solid #DADCE0;border-radius:99px;padding:3px 10px;display:flex;align-items:center;gap:6px;margin:0 6px;min-width:0">
              <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#5F6368" stroke-width="2" style="flex-shrink:0"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
              <span style="font-size:9px;color:#5F6368;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">mail.google.com/mail/u/0/#drafts</span>
            </div>
            <svg width="38" height="13" viewBox="0 0 55 18" fill="none" style="flex-shrink:0">
              <path d="M3.9 14.4V8.1L0 5.1V13.2C0 13.85 0.54 14.4 1.2 14.4H3.9Z" fill="#4285F4"/>
              <path d="M19.5 14.4H22.2C22.86 14.4 23.4 13.86 23.4 13.2V5.1L19.5 8.1V14.4Z" fill="#34A853"/>
              <path d="M19.5 2.4L11.7 8.25L3.9 2.4V8.1L11.7 13.95L19.5 8.1V2.4Z" fill="#EA4335"/>
              <path d="M0 5.1L3.9 8.1V2.4L2.1 1.08C1.29 0.48 0 1.05 0 2.07V5.1Z" fill="#C5221F"/>
              <path d="M23.4 5.1V2.07C23.4 1.05 22.11 0.48 21.3 1.08L19.5 2.4V8.1L23.4 5.1Z" fill="#FBBC04"/>
              <text x="27" y="13" font-family="Arial,sans-serif" font-size="11" fill="#5F6368">Gmail</text>
            </svg>
          </div>
          <div class="sc-draft-body">
            <div class="sc-draft-header-row">
              <span class="sc-draft-header-label">To</span>
              <span class="sc-draft-header-value">{{ $previewCl['recipient_email']??$previewCl['sender_email']??'—' }}</span>
            </div>
            <div class="sc-draft-header-row">
              <span class="sc-draft-header-label">From</span>
              <span class="sc-draft-header-value">{{ auth()->user()->email }}</span>
            </div>
            <div class="sc-draft-subject-row">
              <div class="sc-draft-subject-text">{{ $previewDraft['subject']??$previewCl['subject']??'—' }}</div>
            </div>
            <div class="sc-draft-preview">{{ $previewDraft['body']??$previewDraft['email_body']??'' }}</div>
          </div>
          <div class="sc-draft-actions">
            <form method="POST" action="{{ route('transactions.decide',$previewTx->id) }}" style="flex:1;display:contents">
              @csrf<input type="hidden" name="decision" value="approve">
              <button type="submit" class="sc-btn-approve">Approve &amp; send</button>
            </form>
            <a href="{{ route('transactions.show',$previewTx->id) }}" class="sc-btn-review">Review in full</a>
          </div>
        </div>
        @endif

      </div>

    </div>
  </div>

</div>{{-- ob-page --}}
</div>{{-- ob-shell --}}
</body>
</html>

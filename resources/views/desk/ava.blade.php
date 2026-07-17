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

/* ── PAGE SHELL ── */
.ob-page{display:grid;grid-template-columns:260px 1fr;height:100vh;overflow:hidden}

/* ── SIDEBAR ── */
.ob-sidebar{background:#F4F3F1;display:flex;flex-direction:column;padding:32px 24px;overflow-y:auto}
.ob-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;margin-bottom:32px}
.ob-workers-label{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}
.ob-hire-btn{font-size:10px;font-weight:700;color:#0D0D0D;background:#fff;border:1px solid #E5E7EB;border-radius:6px;padding:3px 8px;text-decoration:none}
.ob-hire-btn:hover{background:#ECEAE6}
.ob-worker-row{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;text-decoration:none;margin-bottom:4px;border:1.5px solid transparent;transition:background .12s}
.ob-worker-row:hover{background:#fff}
.ob-worker-row.active{background:#fff;border-color:#E5E7EB}
.ob-worker-av{width:36px;height:36px;border-radius:9px;flex-shrink:0;overflow:hidden;background:#E8E7E4;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#6B7280}
.ob-worker-av img{width:100%;height:100%;object-fit:cover;display:block}
.ob-worker-name{font-size:12.5px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-worker-role{font-size:10.5px;color:#9CA3AF;margin-top:1px}
.ob-worker-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;margin-left:auto}
.ob-links-label{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:6px;margin-top:22px}
.ob-link{display:flex;align-items:center;gap:9px;padding:7px 10px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:500;color:#6B7280;transition:all .12s}
.ob-link:hover{background:#fff;color:#0D0D0D}
.ob-link svg{width:14px;height:14px;stroke:currentColor;stroke-width:1.8;fill:none;flex-shrink:0}
.ob-security{margin-top:auto;padding:14px 16px;border-radius:12px;background:#ECEAE6;border:1px solid #DCDCDC}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:5px}
.ob-security-row svg{width:13px;height:13px;stroke:#6B7280;flex-shrink:0;fill:none}
.ob-security-title{font-size:12px;font-weight:700;color:#0D0D0D}
.ob-security p{font-size:11px;color:#6B7280;line-height:1.55}

/* ── MAIN AREA ── */
.ob-main{display:flex;align-items:stretch;padding:20px 24px 20px 12px;overflow:hidden;background:#EEECEA}

/* ── DESK CARD: 3 columns ── */
.desk-card{
  display:grid;
  grid-template-columns:280px 1fr 340px;
  grid-template-rows:1fr;
  width:100%;
  border-radius:20px;overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);
  border:1px solid rgba(0,0,0,.07);
  background:#fff;
}

/* ── LEFT: Story of the day ── */
.desk-left{
  background:#F4F3F1;border-right:1px solid #E8E7E4;
  padding:28px 22px;display:flex;flex-direction:column;
  overflow-y:auto;
}
.desk-left::-webkit-scrollbar{width:3px}
.desk-left::-webkit-scrollbar-thumb{background:#E0E0DC;border-radius:2px}

.desk-badge{
  display:inline-flex;align-items:center;gap:6px;
  background:#0D0D0D;color:#fff;border-radius:99px;
  font-size:9.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  padding:5px 12px;margin-bottom:20px;width:fit-content;
}
.desk-badge-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}

.desk-title{font-size:1.75rem;font-weight:900;letter-spacing:-.04em;line-height:1.1;color:#0D0D0D;margin-bottom:8px}
.desk-sub{font-size:12.5px;color:#6B7280;line-height:1.65;margin-bottom:22px}

/* Numbers: the story */
.desk-stats{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:20px}
.desk-stat{background:#fff;border:1px solid #E8E7E4;border-radius:10px;padding:11px 13px}
.desk-stat-val{font-size:24px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;line-height:1}
.desk-stat-lbl{font-size:10px;color:#9CA3AF;margin-top:3px;line-height:1.35}

/* Memory section */
.desk-mem-label{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px}
.desk-mem-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:5px}
.desk-mem-key{font-size:12px;font-weight:600;color:#374151}
.desk-mem-val{font-size:12px;font-weight:700;color:#0D0D0D}
.desk-mem-bar{height:3px;background:#E8E7E4;border-radius:99px;overflow:hidden;margin-bottom:10px}
.desk-mem-fill{height:100%;border-radius:99px;background:#0D0D0D}

/* Note */
.desk-note{margin-top:auto;background:#fff;border:1px solid #E8E7E4;border-radius:12px;padding:13px 15px}
.desk-note-label{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:7px}
.desk-note-text{font-size:12px;color:#374151;line-height:1.65;font-style:italic;margin-bottom:8px}
.desk-note-sig{display:flex;align-items:center;justify-content:space-between}
.desk-note-name{font-size:12px;font-weight:700;color:#0D0D0D}
.desk-note-heart svg{width:14px;height:14px;fill:#FDA4AF}

/* ── CENTER: Ava photo ── */
.desk-hero{
  position:relative;overflow:hidden;background:#1a1a2e;
  min-height:0;
}
.desk-hero img{
  width:100%;height:100%;object-fit:cover;object-position:center 15%;
  display:block;
}
.desk-bubble{
  position:absolute;top:24px;left:24px;
  background:#fff;border-radius:16px 16px 16px 4px;
  padding:12px 16px;max-width:210px;
  box-shadow:0 4px 20px rgba(0,0,0,.15);
}
.desk-bubble p{font-size:12.5px;font-weight:600;color:#0D0D0D;line-height:1.5}
.desk-nameplate{
  position:absolute;bottom:20px;left:24px;
  background:rgba(0,0,0,.65);backdrop-filter:blur(8px);
  border-radius:10px;padding:8px 14px;
}
.desk-nameplate-name{font-size:12px;font-weight:800;color:#fff;letter-spacing:.02em}
.desk-nameplate-role{font-size:10px;color:rgba(255,255,255,.6);font-weight:500;letter-spacing:.05em;text-transform:uppercase}

/* ── RIGHT: Activity + Actions ── */
.desk-right{
  display:flex;flex-direction:column;border-left:1px solid #F0F0F0;overflow:hidden;
}

/* Activity section */
.desk-activity{
  flex:0 0 auto;padding:18px 20px 14px;border-bottom:1px solid #F0F0F0;
}
.desk-act-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.desk-act-title{font-size:10px;font-weight:800;color:#0D0D0D;letter-spacing:.08em;text-transform:uppercase}
.desk-onshift-pill{display:flex;align-items:center;gap:5px;font-size:9px;font-weight:700;color:#15803D;letter-spacing:.08em;text-transform:uppercase;background:#DCFCE7;border-radius:99px;padding:3px 8px}
.desk-onshift-dot{width:5px;height:5px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
.desk-feed{display:flex;flex-direction:column;gap:8px}
.desk-feed-item{display:flex;gap:10px;align-items:flex-start}
.desk-feed-time{font-size:10px;color:#9CA3AF;font-weight:600;white-space:nowrap;padding-top:3px;min-width:42px}
.desk-feed-dot{width:22px;height:22px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:800;color:#fff;flex-shrink:0}
.desk-feed-action{font-size:12px;color:#374151;font-weight:600;line-height:1.4}
.desk-feed-client{font-size:11px;color:#9CA3AF;margin-top:1px}
.desk-view-link{font-size:11px;color:#9CA3AF;font-weight:600;text-decoration:none;display:block;margin-top:10px}
.desk-view-link:hover{color:#0D0D0D}

/* Approval queue — fills remaining height */
.desk-approvals{flex:1;display:flex;flex-direction:column;overflow:hidden;border-top:1px solid #EEEDE9}
.desk-ap-header{display:flex;align-items:center;justify-content:space-between;padding:14px 20px 10px;flex-shrink:0}
.desk-ap-title{font-size:10px;font-weight:800;color:#0D0D0D;letter-spacing:.08em;text-transform:uppercase;display:flex;align-items:center;gap:7px}
.desk-ap-count{font-size:10px;font-weight:700;background:#0D0D0D;color:#fff;border-radius:99px;padding:2px 7px}
.desk-ap-link{font-size:11px;font-weight:600;color:#9CA3AF;text-decoration:none}
.desk-ap-link:hover{color:#0D0D0D}
.desk-ap-list{overflow-y:auto;flex:1;padding:0 20px}
.desk-ap-list::-webkit-scrollbar{width:3px}
.desk-ap-list::-webkit-scrollbar-thumb{background:#E8E7E4;border-radius:2px}
.desk-ap-item{padding:11px 13px;border-radius:10px;background:#F9F9F8;border:1px solid #EEEDE9;margin-bottom:7px}
.desk-ap-item:last-child{margin-bottom:0}
.desk-ap-row{display:flex;align-items:center;gap:9px;margin-bottom:8px}
.desk-ap-av{width:28px;height:28px;border-radius:7px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.desk-ap-name{font-size:12.5px;font-weight:700;color:#0D0D0D;line-height:1.2;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.desk-ap-plan{font-size:11px;color:#6B7280;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.desk-ap-badge{font-size:9px;font-weight:700;background:rgba(245,197,24,.25);color:#92400E;border-radius:99px;padding:2px 6px;white-space:nowrap;flex-shrink:0}
.desk-ap-btns{display:flex;gap:5px}
.btn-approve{flex:1;padding:8px;border-radius:7px;background:#0D0D0D;color:#fff;border:none;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .15s}
.btn-approve:hover{opacity:.85}
.btn-edit{flex:1;padding:8px;border-radius:7px;background:#fff;color:#374151;border:1.5px solid #E5E7EB;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:flex;align-items:center;justify-content:center}
.btn-edit:hover{border-color:#374151}
.desk-ap-footer{font-size:11px;font-weight:600;color:#9CA3AF;text-decoration:none;display:block;text-align:center;padding:10px 20px 14px;flex-shrink:0}
.desk-ap-footer:hover{color:#0D0D0D}
.desk-ap-empty{padding:20px;text-align:center;font-size:12.5px;color:#C4C4C0}

@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}

/* ══ MOBILE ══ */
@media(max-width:768px){
  html,body{height:auto;overflow:auto}
  .ob-page{display:block;height:auto;overflow:visible}
  .ob-sidebar,.desk-hero{display:none}
  .ob-main{padding:0;display:block;background:#fff}
  .desk-card{display:block;border-radius:0;border:none;box-shadow:none}
  .desk-left,.desk-right{border:none;overflow:visible;height:auto}
  .desk-left{padding:20px 18px}
  .desk-activity{overflow:visible;height:auto}
  .desk-approvals{flex:none;overflow:visible;height:auto}
  .desk-ap-list{overflow:visible;height:auto}
  .desk-stats{grid-template-columns:repeat(2,1fr)}
  .desk-note{margin-top:20px}

  .mob-nav{display:flex;align-items:center;justify-content:space-between;padding:16px 18px 12px;background:#fff;position:sticky;top:0;z-index:50;border-bottom:1px solid #F0F0EE}
  .mob-logo{font-size:20px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D}
  .mob-avatars{display:flex;align-items:center}
  .mob-av{width:32px;height:32px;border-radius:50%;border:2.5px solid #fff;overflow:hidden;margin-left:-8px;background:#E8E7E4;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#6B7280;flex-shrink:0}
  .mob-av img{width:100%;height:100%;object-fit:cover;display:block}
  .mob-av-count{width:32px;height:32px;border-radius:50%;border:2.5px solid #fff;background:#0D0D0D;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;margin-left:-8px;flex-shrink:0}
  .mob-only{display:block}
}
@media(min-width:769px){
  .mob-only{display:none !important}
  .mob-nav{display:none}
}
</style>
</head>
<body>

@php
$dotColors = ['#6366f1','#f59e0b','#22c55e','#f97316','#8b5cf6','#ec4899','#06b6d4'];
$activityMap = [
  'draft_ready' =>['label'=>'Reply ready for approval', 'color'=>'#22c55e'],
  'approved'    =>['label'=>'Reply approved',           'color'=>'#22c55e'],
  'sent'        =>['label'=>'Reply sent',               'color'=>'#22c55e'],
  'failed'      =>['label'=>'Pipeline error',           'color'=>'#ef4444'],
  'reading'     =>['label'=>'Reading incoming email',   'color'=>'#6366f1'],
  'classifying' =>['label'=>'Classifying email',        'color'=>'#f59e0b'],
  'drafting'    =>['label'=>'Drafting renewal reply',   'color'=>'#8b5cf6'],
  'ingesting'   =>['label'=>'Renewal detected',         'color'=>'#6366f1'],
];
$apColors = ['#6366f1','#f59e0b','#22c55e','#f97316','#8b5cf6','#ec4899'];

$avaNote = \Illuminate\Support\Facades\DB::table('transactions')->where('deployment_id',$depId)->count() === 0
  ? "Ready and standing by, {$firstName}. Give me something to work on."
  : ($waitingCount > 0
    ? "I've drafted {$waitingCount} ".($waitingCount===1?'reply':'replies')." for your review. Let me know what you think."
    : "All caught up. I'll flag anything that needs your attention.");
@endphp

{{-- ── MOBILE NAV (hidden on desktop) ── --}}
<nav class="mob-nav mob-only">
  <span class="mob-logo">UNIT</span>
  <div class="mob-avatars">
    @foreach($allDeployments->take(4) as $wd)
    @php $wReg2=$registryRows->get($wd->worker_slug);$wImg2=$wReg2?->profile_image?asset('storage/'.$wReg2->profile_image):null; @endphp
    <div class="mob-av">@if($wImg2)<img src="{{ $wImg2 }}" alt="">@else{{ strtoupper(substr($wd->worker_slug,0,1)) }}@endif</div>
    @endforeach
    @if($allDeployments->count()>4)<div class="mob-av-count">{{ $allDeployments->count()-4 }}</div>@endif
  </div>
</nav>

<div class="ob-page">

  {{-- ── SIDEBAR ── --}}
  <aside class="ob-sidebar">
    <div class="ob-logo">UNIT</div>

    <div class="ob-workers-label">
      MY WORKERS
      <a href="{{ route('workers.page') }}" class="ob-hire-btn">+ Hire</a>
    </div>

    @foreach($allDeployments as $wd)
    @php
      $wReg=$registryRows->get($wd->worker_slug);
      $wImg=$wReg?->profile_image?asset('storage/'.$wReg->profile_image):null;
      $wDot=$wd->status==='active'?'#22c55e':'#f59e0b';
      $wHref=$wd->worker_slug==='ava'?route('desk.ava'):'#';
      $wRole=$wReg->tagline??ucfirst($wd->worker_slug).' Specialist';
    @endphp
    <a href="{{ $wHref }}" class="ob-worker-row {{ $wd->worker_slug==='ava'?'active':'' }}">
      <div class="ob-worker-av">
        @if($wImg)<img src="{{ $wImg }}" alt="">@else{{ strtoupper(substr($wd->worker_slug,0,1)) }}@endif
      </div>
      <div>
        <div class="ob-worker-name">{{ strtoupper($wd->worker_slug) }}</div>
        <div class="ob-worker-role">{{ $wRole }}</div>
      </div>
      <span class="ob-worker-dot" style="background:{{ $wDot }}"></span>
    </a>
    @endforeach

    <div class="ob-links-label">LINKS</div>
    @foreach([
      ['Knowledge Base','M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253','#'],
      ['Templates','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',route('workers.templates',['slug'=>'ava'])],
      ['Integrations','M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1','#'],
      ['Billing & Plans','M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',route('billing')],
      ['Team Members','M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z','#'],
      ['Activity Log','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',route('transactions')],
    ] as [$lbl,$ico,$href])
    <a href="{{ $href }}" class="ob-link">
      <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ico }}"/></svg>
      {{ $lbl }}
    </a>
    @endforeach

    <div class="ob-security">
      <div class="ob-security-row">
        <svg viewBox="0 0 24 24" stroke-width="1.8" fill="none"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>You're in control of what<br>Ava can see and access.</p>
    </div>
  </aside>

  {{-- ── MAIN AREA ── --}}
  <div class="ob-main">
    <div class="desk-card">

      {{-- ── LEFT: Story of the day ── --}}
      <div class="desk-left">
        <div class="desk-badge">
          <span class="desk-badge-dot"></span>
          ON SHIFT &nbsp;·&nbsp; {{ now()->format('g:i A') }}
        </div>

        <h1 class="desk-title">AVA's Desk</h1>
        <p class="desk-sub">Renewal Specialist<br>She's protecting your renewals and keeping customers close.</p>

        {{-- Numbers —story of the day --}}
        <div class="desk-stats">
          <div class="desk-stat">
            <div class="desk-stat-val" style="color:#6366f1">{{ $incomingCount }}</div>
            <div class="desk-stat-lbl">Renewal requests<br>detected</div>
          </div>
          <div class="desk-stat">
            <div class="desk-stat-val" style="color:#8b5cf6">{{ $incomingCount }}</div>
            <div class="desk-stat-lbl">Replies<br>drafted</div>
          </div>
          <div class="desk-stat">
            <div class="desk-stat-val" style="color:#f59e0b">{{ $waitingCount }}</div>
            <div class="desk-stat-lbl">Awaiting your<br>review</div>
          </div>
          <div class="desk-stat">
            <div class="desk-stat-val" style="color:#22c55e">{{ $completedCount }}</div>
            <div class="desk-stat-lbl">Completed<br>today</div>
          </div>
        </div>

        {{-- Memory — accessible knowledge --}}
        <div class="desk-mem-label">Memory</div>
        <div class="desk-mem-row">
          <span class="desk-mem-key">Clients</span>
          <span class="desk-mem-val">{{ $clientCount }}</span>
        </div>
        <div class="desk-mem-bar"><div class="desk-mem-fill" style="width:{{ min(100,($clientCount/max(5,$clientCount+3))*100) }}%"></div></div>
        <div class="desk-mem-row">
          <span class="desk-mem-key">Contacts</span>
          <span class="desk-mem-val">{{ $contactCount }}</span>
        </div>
        <div class="desk-mem-bar"><div class="desk-mem-fill" style="width:{{ min(100,($contactCount/max(5,$contactCount+3))*100) }}%"></div></div>
        <div class="desk-mem-row" style="margin-top:4px">
          <span class="desk-mem-key" style="font-size:10.5px;color:#9CA3AF">Workload</span>
          <span style="font-size:10.5px;color:#9CA3AF">{{ $clientCount<3?'Light':($clientCount<8?'Medium':'Heavy') }}</span>
        </div>

        {{-- AVA's note --}}
        <div class="desk-note">
          <div class="desk-note-label">AVA's Note</div>
          <p class="desk-note-text">{{ $avaNote }}</p>
          <div class="desk-note-sig">
            <span class="desk-note-name">— Ava</span>
            <div class="desk-note-heart"><svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></div>
          </div>
        </div>
      </div>

      {{-- ── CENTER: Ava photo ── --}}
      <div class="desk-hero">
        @if($coverImg)
          <img src="{{ $coverImg }}" alt="AVA">
        @else
          <div style="position:absolute;inset:0;background:linear-gradient(135deg,#1a1a2e,#2d1b69)"></div>
        @endif
        <div class="desk-bubble">
          <p>I've got it from here, {{ $firstName }}. I'll keep you posted!</p>
        </div>
        <div class="desk-nameplate">
          <div class="desk-nameplate-name">AVA</div>
          <div class="desk-nameplate-role">Renewal Specialist</div>
        </div>
      </div>

      {{-- ── RIGHT: Live Activity + Approvals ── --}}
      <div class="desk-right">

        {{-- Live Activity --}}
        <div class="desk-activity">
          <div class="desk-act-header">
            <span class="desk-act-title">Live Activity</span>
            <span class="desk-onshift-pill">
              <span class="desk-onshift-dot"></span>
              On Shift
            </span>
          </div>
          <div class="desk-feed">
            @forelse($activity->take(5) as $i => $tx)
            @php
              $am=$activityMap[$tx->status]??['label'=>ucfirst(str_replace('_',' ',$tx->status)),'color'=>'#9CA3AF'];
              $cl=json_decode($tx->classify_output??'{}',true);
              $txc=$cl['client']??$cl['sender_name']??'';
            @endphp
            <div class="desk-feed-item">
              <span class="desk-feed-time">{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</span>
              <span class="desk-feed-dot" style="background:{{ $am['color'] }}">{{ $i+1 }}</span>
              <div>
                <div class="desk-feed-action">{{ $am['label'] }}</div>
                @if($txc)<div class="desk-feed-client">{{ $txc }}</div>@endif
              </div>
            </div>
            @empty
            <p style="font-size:12px;color:#C4C4C0">No activity yet — Ava is standing by.</p>
            @endforelse
          </div>
          <a href="{{ route('transactions') }}" class="desk-view-link">View Live Feed →</a>
        </div>

        {{-- Approval Queue --}}
        <div class="desk-approvals">
          <div class="desk-ap-header">
            <span class="desk-ap-title">
              Approvals
              @if($waitingCount>0)<span class="desk-ap-count">{{ $waitingCount }}</span>@endif
            </span>
            <a href="{{ route('transactions',['filter'=>'draft_ready']) }}" class="desk-ap-link">View all →</a>
          </div>

          <div class="desk-ap-list">
            @forelse($approvals as $tx)
            @php
              $cl=json_decode($tx->classify_output??'{}',true);
              $apN=$cl['client']??$cl['sender_name']??'Unknown';
              $apP=$cl['plan']??$cl['product']??'Renewal';
              $apV=$cl['contract_value']??$cl['renewal_value']??'';
              $apC=$apColors[abs(crc32($apN))%count($apColors)];
            @endphp
            <div class="desk-ap-item">
              <div class="desk-ap-row">
                <div class="desk-ap-av" style="background:{{ $apC }}">{{ strtoupper(substr($apN,0,1)) }}</div>
                <div style="flex:1;min-width:0">
                  <div class="desk-ap-name">{{ $apN }}</div>
                  <div class="desk-ap-plan">{{ $apP }}{{ $apV?' — '.$apV:'' }}</div>
                </div>
                <span class="desk-ap-badge">Draft ready</span>
              </div>
              <div class="desk-ap-btns">
                <form method="POST" action="{{ route('transactions.decide',$tx->id) }}" style="flex:1;display:contents">
                  @csrf<input type="hidden" name="decision" value="approve">
                  <button type="submit" class="btn-approve">Approve &amp; send</button>
                </form>
                <a href="{{ route('transactions.show',$tx->id) }}" class="btn-edit">Review in full</a>
              </div>
            </div>
            @empty
            <div class="desk-ap-empty">Nothing awaiting approval.</div>
            @endforelse
          </div>

          @if($waitingCount > 0)
          <a href="{{ route('transactions',['filter'=>'draft_ready']) }}" class="desk-ap-footer">{{ $waitingCount }} draft{{ $waitingCount===1?'':'s' }} waiting →</a>
          @endif
        </div>

      </div>
      {{-- end desk-right --}}

    </div>
    {{-- end desk-card --}}
  </div>

</div>
</body>
</html>

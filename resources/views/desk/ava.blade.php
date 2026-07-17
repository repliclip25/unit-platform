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
.ob-page{display:grid;grid-template-columns:240px 1fr 300px;height:100vh;overflow:hidden}

/* ── LEFT SIDEBAR ── */
.ob-sidebar{background:#F4F3F1;display:flex;flex-direction:column;padding:32px 20px;overflow-y:auto;border-right:1px solid #E8E7E4}
.ob-logo{font-size:20px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;margin-bottom:32px}
.ob-workers-label{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}
.ob-hire-btn{font-size:10px;font-weight:700;color:#0D0D0D;background:#fff;border:1px solid #E5E7EB;border-radius:6px;padding:3px 8px;text-decoration:none}
.ob-hire-btn:hover{background:#ECEAE6}
.ob-worker-row{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:10px;text-decoration:none;margin-bottom:3px;border:1.5px solid transparent;transition:background .12s}
.ob-worker-row:hover{background:#fff}
.ob-worker-row.active{background:#fff;border-color:#E5E7EB}
.ob-worker-av{width:34px;height:34px;border-radius:8px;flex-shrink:0;overflow:hidden;background:#E8E7E4;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#6B7280}
.ob-worker-av img{width:100%;height:100%;object-fit:cover;display:block}
.ob-worker-name{font-size:12px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-worker-role{font-size:10px;color:#9CA3AF;margin-top:1px}
.ob-worker-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;margin-left:auto}
.ob-links-label{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:5px;margin-top:22px}
.ob-link{display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:500;color:#6B7280;transition:all .12s}
.ob-link:hover{background:#fff;color:#0D0D0D}
.ob-link svg{width:13px;height:13px;stroke:currentColor;stroke-width:1.8;fill:none;flex-shrink:0}
.ob-security{margin-top:auto;padding:14px 15px;border-radius:12px;background:#ECEAE6;border:1px solid #DCDCDC}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:4px}
.ob-security-row svg{width:12px;height:12px;stroke:#6B7280;flex-shrink:0;fill:none}
.ob-security-title{font-size:11.5px;font-weight:700;color:#0D0D0D}
.ob-security p{font-size:10.5px;color:#6B7280;line-height:1.55}

/* ── CENTER — MAIN WORKSPACE ── */
.ob-main{background:#fff;display:flex;flex-direction:column;overflow:hidden;border-right:1px solid #F0F0EE}

/* Top bar inside main */
.ob-topbar{display:flex;align-items:center;justify-content:space-between;padding:20px 28px 0;flex-shrink:0}
.ob-topbar-left{display:flex;align-items:center;gap:10px}
.ob-shift-badge{display:flex;align-items:center;gap:6px;background:#0D0D0D;border-radius:99px;padding:5px 11px}
.ob-shift-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
.ob-shift-text{font-size:9.5px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#fff}
.ob-topbar-time{font-size:11px;color:#9CA3AF}
.ob-topbar-right{display:flex;align-items:center;gap:8px}
.ob-filter-btn{font-size:11px;font-weight:600;color:#6B7280;background:#F4F3F1;border:none;border-radius:7px;padding:5px 10px;cursor:pointer;font-family:inherit;transition:background .12s}
.ob-filter-btn:hover,.ob-filter-btn.active{background:#0D0D0D;color:#fff}

/* Stat strip — pure type, no boxes */
.ob-statstrip{display:flex;align-items:center;gap:0;padding:16px 28px 14px;border-bottom:1px solid #F0F0EE;flex-shrink:0}
.ob-stat-item{display:flex;flex-direction:column;padding-right:28px;margin-right:28px;border-right:1px solid #F0F0EE}
.ob-stat-item:last-child{border-right:none;padding-right:0;margin-right:0}
.ob-stat-num{font-size:26px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;line-height:1}
.ob-stat-lbl{font-size:10px;color:#9CA3AF;margin-top:2px;letter-spacing:.02em}
.ob-stat-dot{display:inline-block;width:6px;height:6px;border-radius:50%;margin-right:4px;vertical-align:middle;position:relative;top:-1px}

/* Section headers inside center */
.ob-section-hd{display:flex;align-items:center;justify-content:space-between;padding:16px 28px 10px;flex-shrink:0}
.ob-section-title{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#9CA3AF}
.ob-section-link{font-size:11px;font-weight:600;color:#9CA3AF;text-decoration:none}
.ob-section-link:hover{color:#0D0D0D}

/* ── ACTIVITY FEED — no card borders, just rows ── */
.ob-feed{overflow-y:auto;flex:1;padding:0 28px}
.ob-feed::-webkit-scrollbar{width:3px}
.ob-feed::-webkit-scrollbar-thumb{background:#E8E7E4;border-radius:2px}
.ob-feed-item{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #F5F5F3;cursor:default}
.ob-feed-item:last-child{border-bottom:none}
.ob-feed-node{width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:5px}
.ob-feed-body{flex:1;min-width:0}
.ob-feed-action{font-size:13px;font-weight:600;color:#0D0D0D;line-height:1.3}
.ob-feed-client{font-size:12px;color:#6B7280;margin-top:2px}
.ob-feed-time{font-size:11px;color:#C4C4C0;flex-shrink:0;margin-left:auto;padding-left:12px;white-space:nowrap}
.ob-feed-empty{padding:32px 0;text-align:center;font-size:13px;color:#C4C4C0}

/* ── APPROVAL QUEUE — action rows, these get visual weight ── */
.ob-queue{overflow-y:auto;padding:0 28px 20px;flex-shrink:0}
.ob-queue::-webkit-scrollbar{width:3px}
.ob-queue-item{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;background:#F9F9F8;border:1px solid #EEEDE9;margin-bottom:6px;transition:border-color .12s}
.ob-queue-item:hover{border-color:#D1D5DB}
.ob-queue-item:last-child{margin-bottom:0}
.ob-queue-av{width:32px;height:32px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff}
.ob-queue-info{flex:1;min-width:0}
.ob-queue-name{font-size:13px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-queue-plan{font-size:11px;color:#6B7280;margin-top:1px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.ob-queue-btns{display:flex;gap:5px;flex-shrink:0}
.btn-approve{padding:7px 14px;border-radius:7px;background:#0D0D0D;color:#fff;border:none;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .15s;white-space:nowrap}
.btn-approve:hover{opacity:.82}
.btn-edit{padding:7px 12px;border-radius:7px;background:#fff;color:#374151;border:1.5px solid #E5E7EB;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:flex;align-items:center;justify-content:center;transition:border-color .15s;white-space:nowrap}
.btn-edit:hover{border-color:#374151}
.ob-queue-empty{padding:20px 0;text-align:center;font-size:13px;color:#C4C4C0}
.ob-queue-all{font-size:11px;font-weight:600;color:#9CA3AF;text-decoration:none;display:block;text-align:center;padding:10px 0 0}
.ob-queue-all:hover{color:#0D0D0D}

/* ── RIGHT PANEL ── */
.ob-panel{background:#fff;display:flex;flex-direction:column;overflow-y:auto;padding:24px 20px}
.ob-panel::-webkit-scrollbar{width:3px}
.ob-panel::-webkit-scrollbar-thumb{background:#E8E7E4;border-radius:2px}

.rp-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#C4C4C0;margin-bottom:6px}
.rp-name{font-size:1.6rem;font-weight:900;letter-spacing:-.05em;color:#0D0D0D;line-height:1}
.rp-role{font-size:12px;color:#9CA3AF;margin-top:4px;margin-bottom:18px}
.rp-divider{border:none;border-top:1px solid #F0F0EE;margin:0 0 16px}

.rp-label{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:8px}

/* Status line */
.rp-status-line{display:flex;align-items:center;gap:8px;margin-bottom:4px}
.rp-status-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.rp-status-text{font-size:14px;font-weight:700}
.rp-status-since{font-size:10.5px;color:#9CA3AF;margin-bottom:16px}

/* Task line */
.rp-task-title{font-size:13px;font-weight:700;color:#0D0D0D;margin-bottom:2px}
.rp-task-sub{font-size:11.5px;color:#9CA3AF;margin-bottom:7px}
.rp-bar{height:3px;background:#F0F0EE;border-radius:99px;overflow:hidden;margin-bottom:2px}
.rp-bar-fill{height:100%;border-radius:99px;background:#0D0D0D}
.rp-bar-pct{font-size:10px;color:#C4C4C0;text-align:right;margin-bottom:16px}

/* Numbers */
.rp-nums{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
.rp-num-item{}
.rp-num-val{font-size:22px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;line-height:1}
.rp-num-lbl{font-size:10px;color:#9CA3AF;margin-top:2px}

/* Memory */
.rp-mem-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:4px}
.rp-mem-val{font-size:13px;font-weight:700;color:#0D0D0D}
.rp-mem-bar{height:3px;background:#F0F0EE;border-radius:99px;overflow:hidden;margin-bottom:14px}

/* Note */
.rp-note{font-size:12.5px;color:#374151;line-height:1.7;font-style:italic;margin-bottom:10px}
.rp-note-sig{display:flex;align-items:center;justify-content:space-between;margin-top:auto}
.rp-note-name{font-size:12px;font-weight:700;color:#0D0D0D}
.rp-note-sig svg{width:15px;height:15px;fill:#FDA4AF}

@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}

/* ══ MOBILE ══ */
@media(max-width:768px){
  html,body{height:auto;overflow:auto}
  .ob-page{display:block;height:auto;overflow:visible}
  .ob-sidebar,.ob-panel{display:none}
  .ob-main{border-right:none;overflow:visible;height:auto}
  .ob-feed,.ob-queue{overflow:visible}

  /* Top nav */
  .mob-nav{display:flex;align-items:center;justify-content:space-between;padding:16px 20px 12px;background:#fff;position:sticky;top:0;z-index:50;border-bottom:1px solid #F0F0F0}
  .mob-logo{font-size:20px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D}
  .mob-avatars{display:flex;align-items:center}
  .mob-av{width:32px;height:32px;border-radius:50%;border:2.5px solid #fff;overflow:hidden;margin-left:-8px;background:#E8E7E4;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#6B7280;flex-shrink:0}
  .mob-av img{width:100%;height:100%;object-fit:cover;display:block}
  .mob-av-count{width:32px;height:32px;border-radius:50%;border:2.5px solid #fff;background:#0D0D0D;color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;margin-left:-8px;flex-shrink:0}

  /* Hero strip */
  .mob-hero{padding:20px 20px 0;background:#fff}
  .mob-hero-badge{display:inline-flex;align-items:center;gap:6px;background:#0D0D0D;border-radius:99px;padding:5px 11px;margin-bottom:12px}
  .mob-hero-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
  .mob-hero-badge-text{font-size:9.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#fff}
  .mob-hero-title{font-size:1.7rem;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;margin-bottom:4px}
  .mob-hero-sub{font-size:13px;color:#6B7280;margin-bottom:16px;line-height:1.5}

  /* Stat strip */
  .mob-statstrip{display:flex;overflow-x:auto;gap:0;padding:0 20px 16px;background:#fff;border-bottom:1px solid #F0F0EE}
  .mob-statstrip::-webkit-scrollbar{display:none}
  .mob-stat{display:flex;flex-direction:column;padding-right:20px;margin-right:20px;border-right:1px solid #F0F0EE;flex-shrink:0}
  .mob-stat:last-child{border-right:none}
  .mob-stat-num{font-size:24px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;line-height:1}
  .mob-stat-lbl{font-size:10px;color:#9CA3AF;margin-top:2px}

  /* Sections */
  .mob-section{padding:18px 20px 0}
  .mob-section-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
  .mob-section-title{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#9CA3AF}
  .mob-section-badge{font-size:11px;font-weight:700;background:#0D0D0D;color:#fff;border-radius:99px;padding:2px 8px;margin-left:6px}
  .mob-section-link{font-size:12px;font-weight:600;color:#9CA3AF;text-decoration:none}

  /* Feed rows */
  .mob-feed-item{display:flex;align-items:flex-start;gap:10px;padding:11px 0;border-bottom:1px solid #F5F5F3}
  .mob-feed-item:last-child{border-bottom:none}
  .mob-feed-node{width:7px;height:7px;border-radius:50%;flex-shrink:0;margin-top:5px}
  .mob-feed-action{font-size:13px;font-weight:600;color:#0D0D0D}
  .mob-feed-client{font-size:12px;color:#9CA3AF;margin-top:2px}
  .mob-feed-time{font-size:11px;color:#C4C4C0;flex-shrink:0;margin-left:auto;padding-left:10px}

  /* Approval rows */
  .mob-ap-item{padding:12px 14px;border-radius:12px;background:#F9F9F8;border:1px solid #EEEDE9;margin-bottom:8px}
  .mob-ap-top{display:flex;align-items:center;gap:10px;margin-bottom:10px}
  .mob-ap-av{width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#fff}
  .mob-ap-name{font-size:14px;font-weight:700;color:#0D0D0D;line-height:1.2}
  .mob-ap-plan{font-size:12px;color:#6B7280;margin-top:1px}
  .mob-ap-btns{display:grid;grid-template-columns:1fr 1fr;gap:7px}
  .mob-btn-approve{padding:11px;border-radius:9px;background:#0D0D0D;color:#fff;border:none;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit}
  .mob-btn-edit{padding:11px;border-radius:9px;background:#fff;color:#374151;border:1.5px solid #E5E7EB;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;display:flex;align-items:center;justify-content:center}

  /* Note */
  .mob-note{margin:20px 20px 32px;padding:18px 20px;background:#F4F3F1;border-radius:14px}
  .mob-note-label{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:8px}
  .mob-note-text{font-size:13.5px;color:#374151;line-height:1.7;font-style:italic;margin-bottom:10px}
  .mob-note-sig{display:flex;align-items:center;justify-content:space-between}
  .mob-note-name{font-size:12px;font-weight:700;color:#0D0D0D}
  .mob-note-heart svg{width:16px;height:16px;fill:#FDA4AF}

  .mob-only{display:block}
  .ob-topbar,.ob-statstrip,.ob-section-hd,.ob-feed,.ob-queue{display:none}
}
@media(min-width:769px){
  .mob-only{display:none !important}
  .mob-nav{display:none}
}
</style>
</head>
<body>

@php
$activityMap = [
  'draft_ready' =>['label'=>'Reply ready for approval', 'sub'=>'Ready for your review',          'color'=>'#22c55e'],
  'approved'    =>['label'=>'Reply approved',           'sub'=>'Sent to customer',                'color'=>'#22c55e'],
  'sent'        =>['label'=>'Reply sent',               'sub'=>'Delivered successfully',          'color'=>'#22c55e'],
  'failed'      =>['label'=>'Pipeline error',           'sub'=>'Needs your attention',            'color'=>'#ef4444'],
  'reading'     =>['label'=>'Reading incoming email',   'sub'=>'Parsing content',                 'color'=>'#6366f1'],
  'classifying' =>['label'=>'Classifying email',        'sub'=>'Identifying renewal type',        'color'=>'#f59e0b'],
  'drafting'    =>['label'=>'Drafting renewal reply',   'sub'=>'Preparing personalized response', 'color'=>'#8b5cf6'],
  'ingesting'   =>['label'=>'Renewal request detected', 'sub'=>'Email received',                  'color'=>'#6366f1'],
];
$apColors = ['#6366f1','#f59e0b','#22c55e','#f97316','#8b5cf6','#ec4899'];
@endphp

{{-- ══ MOBILE (hidden on desktop) ══ --}}
<div class="mob-only">
  <nav class="mob-nav">
    <span class="mob-logo">UNIT</span>
    <div class="mob-avatars">
      @foreach($allDeployments->take(4) as $wd)
      @php $wReg2=$registryRows->get($wd->worker_slug);$wImg2=$wReg2?->profile_image?asset('storage/'.$wReg2->profile_image):null; @endphp
      <div class="mob-av">@if($wImg2)<img src="{{ $wImg2 }}" alt="">@else{{ strtoupper(substr($wd->worker_slug,0,1)) }}@endif</div>
      @endforeach
      @if($allDeployments->count()>4)<div class="mob-av-count">{{ $allDeployments->count()-4 }}</div>@endif
    </div>
  </nav>

  <div class="mob-hero" style="background:#fff">
    <div class="mob-hero-badge">
      <span class="mob-hero-dot"></span>
      <span class="mob-hero-badge-text">On Shift — {{ now()->format('g:i A') }}</span>
    </div>
    <h1 class="mob-hero-title">AVA's Desk</h1>
    <p class="mob-hero-sub">Renewal Specialist · Monitoring your inbox</p>
  </div>

  <div class="mob-statstrip">
    @foreach([
      [$incomingCount,'New emails','#6366f1'],
      [$inProgressCount,'In progress','#f59e0b'],
      [$waitingCount,'Need approval','#0D0D0D'],
      [$completedCount,'Done today','#22c55e'],
    ] as [$val,$lbl,$clr])
    <div class="mob-stat">
      <span class="mob-stat-num" style="color:{{ $clr }}">{{ $val }}</span>
      <span class="mob-stat-lbl">{{ $lbl }}</span>
    </div>
    @endforeach
  </div>

  {{-- Approvals --}}
  <div class="mob-section">
    <div class="mob-section-hd">
      <div style="display:flex;align-items:center">
        <span class="mob-section-title">Need Your Approval</span>
        @if($waitingCount>0)<span class="mob-section-badge">{{ $waitingCount }}</span>@endif
      </div>
      <a href="{{ route('transactions',['filter'=>'draft_ready']) }}" class="mob-section-link">View all →</a>
    </div>
    @forelse($approvals as $tx)
    @php
      $cl2=json_decode($tx->classify_output??'{}',true);
      $n2=$cl2['client']??$cl2['sender_name']??'Unknown';
      $p2=$cl2['plan']??$cl2['product']??'Renewal';
      $v2=$cl2['contract_value']??$cl2['renewal_value']??'';
      $c2=$apColors[abs(crc32($n2))%count($apColors)];
    @endphp
    <div class="mob-ap-item">
      <div class="mob-ap-top">
        <div class="mob-ap-av" style="background:{{ $c2 }}">{{ strtoupper(substr($n2,0,1)) }}</div>
        <div>
          <div class="mob-ap-name">{{ $n2 }}</div>
          <div class="mob-ap-plan">{{ $p2 }}{{ $v2?' — '.$v2:'' }}</div>
        </div>
      </div>
      <div class="mob-ap-btns">
        <form method="POST" action="{{ route('transactions.decide',$tx->id) }}">
          @csrf<input type="hidden" name="decision" value="approve">
          <button type="submit" class="mob-btn-approve">Approve</button>
        </form>
        <a href="{{ route('transactions.show',$tx->id) }}" class="mob-btn-edit">Edit</a>
      </div>
    </div>
    @empty
    <p style="font-size:13px;color:#C4C4C0;padding:12px 0">Nothing awaiting approval.</p>
    @endforelse
  </div>

  {{-- Live Feed --}}
  <div class="mob-section" style="padding-bottom:0">
    <div class="mob-section-hd">
      <span class="mob-section-title">Live Activity</span>
      <a href="{{ route('transactions') }}" class="mob-section-link">View all →</a>
    </div>
    @forelse($activity as $tx)
    @php $am=$activityMap[$tx->status]??['label'=>ucfirst(str_replace('_',' ',$tx->status)),'sub'=>'','color'=>'#C4C4C0'];$cl3=json_decode($tx->classify_output??'{}',true);$n3=$cl3['client']??$cl3['sender_name']??''; @endphp
    <div class="mob-feed-item">
      <span class="mob-feed-node" style="background:{{ $am['color'] }}"></span>
      <div style="flex:1">
        <div class="mob-feed-action">{{ $am['label'] }}</div>
        @if($n3)<div class="mob-feed-client">{{ $n3 }}</div>@endif
      </div>
      <span class="mob-feed-time">{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</span>
    </div>
    @empty
    <p style="font-size:13px;color:#C4C4C0;padding:12px 0">No activity yet.</p>
    @endforelse
  </div>

  @php
    $avaNote=$incomingCount===0
      ?"Ready and standing by, {$firstName}. Give me something to work on."
      :($waitingCount>0
        ?"I've drafted {$waitingCount} ".($waitingCount===1?'reply':'replies')." for your review. Let me know what you think."
        :"All caught up. I'll flag anything that needs your attention.");
  @endphp
  <div class="mob-note">
    <div class="mob-note-label">AVA's Note</div>
    <p class="mob-note-text">{{ $avaNote }}</p>
    <div class="mob-note-sig">
      <span class="mob-note-name">— Ava</span>
      <div class="mob-note-heart"><svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></div>
    </div>
  </div>
</div>

{{-- ══ DESKTOP ══ --}}
<div class="ob-page">

  {{-- ── LEFT SIDEBAR ── --}}
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
        <svg viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>You're in control of what<br>Ava can see and access.</p>
    </div>
  </aside>

  {{-- ── CENTER — MAIN WORKSPACE ── --}}
  <main class="ob-main">

    {{-- Top bar --}}
    <div class="ob-topbar">
      <div class="ob-topbar-left">
        <div class="ob-shift-badge">
          <span class="ob-shift-dot"></span>
          <span class="ob-shift-text">On Shift</span>
        </div>
        <span class="ob-topbar-time">{{ now()->format('g:i A') }}</span>
      </div>
      <div class="ob-topbar-right">
        <button class="ob-filter-btn active">All</button>
        <button class="ob-filter-btn">Approvals</button>
        <button class="ob-filter-btn">Activity</button>
      </div>
    </div>

    {{-- Stat strip — pure typography, no boxes --}}
    <div class="ob-statstrip">
      @foreach([
        [$incomingCount,'New emails','#6366f1'],
        [$inProgressCount,'In progress','#f59e0b'],
        [$waitingCount,'Need approval','#0D0D0D'],
        [$completedCount,'Done today','#22c55e'],
      ] as [$val,$lbl,$clr])
      <div class="ob-stat-item">
        <span class="ob-stat-num" style="color:{{ $clr }}">{{ $val }}</span>
        <span class="ob-stat-lbl">{{ $lbl }}</span>
      </div>
      @endforeach
    </div>

    {{-- APPROVALS — items needing action --}}
    @if($waitingCount > 0)
    <div class="ob-section-hd">
      <div style="display:flex;align-items:center;gap:8px">
        <span class="ob-section-title">Need Your Approval</span>
        <span style="font-size:10px;font-weight:700;background:#0D0D0D;color:#fff;border-radius:99px;padding:2px 7px">{{ $waitingCount }}</span>
      </div>
      <a href="{{ route('transactions',['filter'=>'draft_ready']) }}" class="ob-section-link">View all →</a>
    </div>
    <div class="ob-queue">
      @foreach($approvals as $tx)
      @php
        $cl=json_decode($tx->classify_output??'{}',true);
        $apN=$cl['client']??$cl['sender_name']??'Unknown';
        $apP=$cl['plan']??$cl['product']??'Renewal';
        $apV=$cl['contract_value']??$cl['renewal_value']??'';
        $apC=$apColors[abs(crc32($apN))%count($apColors)];
      @endphp
      <div class="ob-queue-item">
        <div class="ob-queue-av" style="background:{{ $apC }}">{{ strtoupper(substr($apN,0,1)) }}</div>
        <div class="ob-queue-info">
          <div class="ob-queue-name">{{ $apN }}</div>
          <div class="ob-queue-plan">{{ $apP }}{{ $apV?' — '.$apV:'' }}</div>
        </div>
        <div class="ob-queue-btns">
          <form method="POST" action="{{ route('transactions.decide',$tx->id) }}" style="display:contents">
            @csrf<input type="hidden" name="decision" value="approve">
            <button type="submit" class="btn-approve">Approve</button>
          </form>
          <a href="{{ route('transactions.show',$tx->id) }}" class="btn-edit">Edit</a>
        </div>
      </div>
      @endforeach
      <a href="{{ route('transactions',['filter'=>'draft_ready']) }}" class="ob-queue-all">{{ $waitingCount }} draft{{ $waitingCount===1?'':'s' }} waiting →</a>
    </div>
    @endif

    {{-- LIVE ACTIVITY FEED --}}
    <div class="ob-section-hd">
      <div style="display:flex;align-items:center;gap:7px">
        <span class="ob-section-title">Live Activity</span>
        <span class="ob-shift-badge" style="padding:3px 8px">
          <span class="ob-shift-dot" style="width:5px;height:5px"></span>
          <span class="ob-shift-text" style="font-size:8.5px">Live</span>
        </span>
      </div>
      <a href="{{ route('transactions') }}" class="ob-section-link">View all →</a>
    </div>

    <div class="ob-feed">
      @forelse($activity as $tx)
      @php
        $am=$activityMap[$tx->status]??['label'=>ucfirst(str_replace('_',' ',$tx->status)),'sub'=>'','color'=>'#C4C4C0'];
        $cl=json_decode($tx->classify_output??'{}',true);
        $txc=$cl['client']??$cl['sender_name']??'';
      @endphp
      <div class="ob-feed-item">
        <span class="ob-feed-node" style="background:{{ $am['color'] }}"></span>
        <div class="ob-feed-body">
          <div class="ob-feed-action">{{ $am['label'] }}</div>
          @if($txc)<div class="ob-feed-client">{{ $txc }}</div>@endif
        </div>
        <span class="ob-feed-time">{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</span>
      </div>
      @empty
      <div class="ob-feed-empty">No activity yet — Ava is standing by.</div>
      @endforelse
    </div>

  </main>

  {{-- ── RIGHT PANEL ── --}}
  <aside class="ob-panel">
    <div class="rp-eyebrow">First Assignment</div>
    <div class="rp-name">AVA</div>
    <div class="rp-role">Renewal Specialist</div>

    <hr class="rp-divider">

    <div class="rp-label">Work Status</div>
    <div class="rp-status-line">
      <span class="rp-status-dot" style="background:{{ $dep->status==='active'?'#22c55e':'#f59e0b' }};{{ $dep->status==='active'?'animation:pdot 1.4s ease infinite':'' }}"></span>
      <span class="rp-status-text" style="color:{{ $dep->status==='active'?'#22c55e':'#f59e0b' }}">{{ $workStatus }}</span>
    </div>
    <div class="rp-status-since">Since {{ \Carbon\Carbon::parse($dep->updated_at)->format('g:i A') }}</div>

    @if($currentTask)
    @php
      $rt=match($currentTask->status){
        'reading','ingesting','classifying'=>'Reading incoming email',
        'drafting','pushing'=>'Drafting renewal reply',
        'draft_ready'=>'Reply awaiting approval',
        'approved','sent'=>'All caught up',
        default=>ucfirst(str_replace('_',' ',$currentTask->status)),
      };
      $rcl=(json_decode($currentTask->classify_output??'{}',true))['client']??'';
      $rp=in_array($currentTask->status,['approved','sent','draft_ready'])?100:67;
    @endphp
    <div class="rp-label">Current Task</div>
    <div class="rp-task-title">{{ $rt }}</div>
    @if($rcl)<div class="rp-task-sub">{{ $rcl }}</div>@else<div style="margin-bottom:7px"></div>@endif
    <div class="rp-bar"><div class="rp-bar-fill" style="width:{{ $rp }}%"></div></div>
    <div class="rp-bar-pct">{{ $rp }}%</div>
    @endif

    <hr class="rp-divider">

    <div class="rp-label">Today</div>
    <div class="rp-nums">
      <div class="rp-num-item">
        <div class="rp-num-val">{{ $incomingCount }}</div>
        <div class="rp-num-lbl">Emails processed</div>
      </div>
      <div class="rp-num-item">
        <div class="rp-num-val">{{ $completedCount }}</div>
        <div class="rp-num-lbl">Completed</div>
      </div>
      <div class="rp-num-item">
        <div class="rp-num-val">{{ $waitingCount }}</div>
        <div class="rp-num-lbl">Need you</div>
      </div>
      <div class="rp-num-item">
        <div class="rp-num-val">{{ $clientCount }}</div>
        <div class="rp-num-lbl">Clients known</div>
      </div>
    </div>

    <hr class="rp-divider">

    <div class="rp-label">Memory</div>
    <div class="rp-mem-row">
      <span style="font-size:12px;font-weight:600;color:#374151">{{ $clientCount }} clients · {{ $contactCount }} contacts</span>
      <span style="font-size:11px;color:#9CA3AF">{{ $clientCount<3?'Light':($clientCount<8?'Medium':'Heavy') }}</span>
    </div>
    <div class="rp-mem-bar">
      <div style="height:100%;border-radius:99px;background:#0D0D0D;width:{{ min(100,($clientCount/max(5,$clientCount+3))*100) }}%"></div>
    </div>

    <hr class="rp-divider">

    @php
      $txTot=\Illuminate\Support\Facades\DB::table('transactions')->where('deployment_id',$depId)->count();
      $avaNote=$txTot===0
        ?"Ready and standing by, {$firstName}. Give me something to work on."
        :($waitingCount>0
          ?"I've drafted {$waitingCount} ".($waitingCount===1?'reply':'replies')." for you to review. Let me know what you think."
          :"All caught up. I'll flag anything that needs your attention.");
    @endphp
    <div class="rp-label">AVA's Note</div>
    <p class="rp-note">{{ $avaNote }}</p>
    <div class="rp-note-sig">
      <span class="rp-note-name">— Ava</span>
      <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
    </div>
  </aside>

</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>AVA's Desk — UNIT</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#F0EFED;color:#0D0D0D}

/* ── PAGE SHELL ── */
.desk-page{display:grid;grid-template-columns:220px 1fr 288px;height:100vh;overflow:hidden}

/* ── LEFT SIDEBAR ── */
.desk-sidebar{
  background:#fff;border-right:1px solid #E8E7E4;
  display:flex;flex-direction:column;overflow-y:auto;padding:20px 0 0;
}
.desk-logo{font-size:20px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;padding:0 18px 20px}
.desk-section-label{
  font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;
  color:#9CA3AF;padding:0 18px;margin-bottom:8px;
  display:flex;align-items:center;justify-content:space-between;
}
.desk-hire-btn{
  font-size:10px;font-weight:700;color:#0D0D0D;
  background:#F4F3F1;border:1px solid #E5E7EB;border-radius:6px;
  padding:3px 8px;text-decoration:none;white-space:nowrap;
}
.desk-hire-btn:hover{background:#E8E7E4}

/* Worker cards in sidebar */
.desk-worker{
  display:flex;align-items:center;gap:10px;
  padding:9px 14px;margin:0 6px;border-radius:10px;
  text-decoration:none;transition:background .12s;
  border:1.5px solid transparent;
}
.desk-worker:hover{background:#F4F3F1}
.desk-worker.active{background:#F4F3F1;border-color:#E5E7EB}
.desk-worker-avatar{
  width:36px;height:36px;border-radius:9px;flex-shrink:0;
  background:#E5E7EB;display:flex;align-items:center;justify-content:center;
  font-size:13px;font-weight:800;color:#6B7280;overflow:hidden;
}
.desk-worker-avatar img{width:100%;height:100%;object-fit:cover;border-radius:9px;display:block}
.desk-worker-info{flex:1;min-width:0}
.desk-worker-name{font-size:12.5px;font-weight:700;color:#0D0D0D;line-height:1.2}
.desk-worker-role{font-size:10.5px;color:#9CA3AF;margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.desk-worker-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}

/* Links section */
.desk-links{padding:0 6px}
.desk-link{
  display:flex;align-items:center;gap:9px;padding:8px 12px;
  border-radius:8px;text-decoration:none;
  font-size:12px;font-weight:500;color:#6B7280;transition:all .12s;
}
.desk-link:hover{background:#F4F3F1;color:#0D0D0D}
.desk-link svg{width:14px;height:14px;stroke:currentColor;stroke-width:1.8;fill:none;flex-shrink:0}

/* Sidebar footer */
.desk-sidebar-footer{margin-top:auto;padding:16px 18px;border-top:1px solid #F0F0EE}
.desk-secure{display:flex;align-items:center;gap:7px;margin-bottom:4px}
.desk-secure svg{width:13px;height:13px;stroke:#9CA3AF;stroke-width:1.8;fill:none}
.desk-secure-title{font-size:11px;font-weight:600;color:#6B7280}
.desk-secure-sub{font-size:10.5px;color:#9CA3AF;line-height:1.5}

/* ── MAIN CONTENT ── */
.desk-main{overflow-y:auto;padding:20px 20px 32px}

/* Hero card */
.desk-hero{
  position:relative;border-radius:18px;overflow:hidden;
  height:260px;margin-bottom:16px;background:#1a1a2e;
}
.desk-hero img{width:100%;height:100%;object-fit:cover;object-position:center top;display:block}
.desk-hero-overlay{
  position:absolute;inset:0;
  background:linear-gradient(to right,rgba(0,0,0,.75) 0%,rgba(0,0,0,.35) 50%,rgba(0,0,0,0) 100%);
}
.desk-hero-inner{position:absolute;top:0;left:0;bottom:0;padding:22px 26px;display:flex;flex-direction:column;justify-content:space-between}
.desk-onshift{display:flex;align-items:center;gap:7px}
.desk-onshift-dot{width:7px;height:7px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
.desk-onshift-text{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.85)}
.desk-onshift-time{font-size:10px;color:rgba(255,255,255,.45);margin-left:4px}
.desk-hero-title{font-size:2.2rem;font-weight:900;color:#fff;letter-spacing:-.04em;line-height:1.05;margin-bottom:5px}
.desk-hero-role{font-size:13px;font-weight:600;color:rgba(255,255,255,.65);margin-bottom:4px}
.desk-hero-desc{font-size:12px;color:rgba(255,255,255,.5);line-height:1.55;max-width:380px}

/* Current task bubble on hero */
.desk-bubble{
  position:absolute;bottom:18px;right:18px;
  background:#fff;border-radius:14px;padding:14px 16px;
  min-width:230px;box-shadow:0 8px 32px rgba(0,0,0,.2);
}
.desk-bubble-label{
  font-size:9px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:#9CA3AF;margin-bottom:6px;
  display:flex;align-items:center;justify-content:space-between;
}
.desk-bubble-label svg{width:13px;height:13px;stroke:#D1D5DB;stroke-width:2;fill:none}
.desk-bubble-title{font-size:13.5px;font-weight:700;color:#0D0D0D;margin-bottom:2px}
.desk-bubble-sub{font-size:11px;color:#6B7280;margin-bottom:10px}
.desk-bar{height:4px;background:#F3F4F6;border-radius:99px;overflow:hidden;margin-bottom:4px}
.desk-bar-fill{height:100%;border-radius:99px;background:#6366f1}
.desk-bar-pct{font-size:10px;color:#9CA3AF;text-align:right}

/* Stats row */
.desk-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px}
.desk-stat{
  background:#fff;border:1px solid #E8E7E4;border-radius:14px;
  padding:16px 18px;display:flex;align-items:center;gap:12px;
}
.desk-stat-icon{
  width:38px;height:38px;border-radius:10px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
}
.desk-stat-icon svg{width:17px;height:17px;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.desk-stat-num{font-size:24px;font-weight:900;color:#0D0D0D;line-height:1}
.desk-stat-lbl{font-size:10.5px;color:#9CA3AF;margin-top:3px}

/* Bottom two-col */
.desk-bottom{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.desk-card{background:#fff;border:1px solid #E8E7E4;border-radius:14px;padding:20px}
.desk-card-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.desk-card-title{font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#0D0D0D}
.desk-card-link{font-size:11px;font-weight:600;color:#9CA3AF;text-decoration:none}
.desk-card-link:hover{color:#0D0D0D}

/* Activity */
.desk-shift-pill{
  display:flex;align-items:center;gap:5px;
  font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
  color:#15803D;background:#DCFCE7;border-radius:99px;padding:3px 8px;
}
.desk-shift-dot{width:5px;height:5px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
.desk-act-wrap{position:relative;padding-left:22px}
.desk-act-line{position:absolute;left:7px;top:6px;bottom:6px;width:1px;background:#F0F0EE}
.desk-act-item{display:flex;gap:10px;align-items:flex-start;margin-bottom:13px;position:relative}
.desk-act-item:last-child{margin-bottom:0}
.desk-act-node{
  position:absolute;left:-22px;top:2px;
  width:15px;height:15px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:7px;font-weight:900;color:#fff;flex-shrink:0;
}
.desk-act-text{font-size:12px;font-weight:600;color:#0D0D0D;line-height:1.3}
.desk-act-sub{font-size:11px;color:#9CA3AF;margin-top:1px}
.desk-act-time{font-size:10px;color:#9CA3AF;white-space:nowrap;margin-left:auto;padding-left:8px;flex-shrink:0}
.desk-view-all{font-size:11px;font-weight:600;color:#9CA3AF;text-decoration:none;display:block;margin-top:12px}
.desk-view-all:hover{color:#0D0D0D}

/* Approvals */
.desk-ap-item{padding:12px 14px;border-radius:12px;background:#F9F9F8;border:1px solid #EEEDE9;margin-bottom:10px}
.desk-ap-item:last-child{margin-bottom:0}
.desk-ap-row{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px}
.desk-ap-avatar{
  width:32px;height:32px;border-radius:8px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:800;color:#fff;
}
.desk-ap-name{font-size:12.5px;font-weight:700;color:#0D0D0D;line-height:1.2}
.desk-ap-plan{font-size:11px;color:#6B7280;margin-top:2px}
.desk-ap-badge{font-size:10px;font-weight:700;background:rgba(245,197,24,.2);color:#92400E;border-radius:99px;padding:2px 8px;white-space:nowrap;flex-shrink:0}
.desk-ap-btns{display:flex;gap:6px}
.btn-ap-approve{
  flex:1;padding:8px;border-radius:8px;background:#0D0D0D;color:#fff;
  border:none;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;
  transition:opacity .15s;width:100%;
}
.btn-ap-approve:hover{opacity:.85}
.btn-ap-edit{
  flex:1;padding:8px;border-radius:8px;background:#fff;color:#374151;
  border:1.5px solid #E5E7EB;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;
  text-decoration:none;display:flex;align-items:center;justify-content:center;
  transition:border-color .15s;
}
.btn-ap-edit:hover{border-color:#0D0D0D;color:#0D0D0D}
.desk-ap-footer{font-size:11px;font-weight:600;color:#9CA3AF;text-decoration:none;display:block;margin-top:10px}
.desk-ap-footer:hover{color:#0D0D0D}

/* ── RIGHT RAIL ── */
.desk-rail{
  background:#fff;border-left:1px solid #E8E7E4;
  overflow-y:auto;padding:20px 18px;
  display:flex;flex-direction:column;
}
.desk-rail-sec{padding-bottom:16px;margin-bottom:16px;border-bottom:1px solid #F0F0EE}
.desk-rail-sec:last-child{border-bottom:none;padding-bottom:0;margin-bottom:0}
.desk-rail-eye{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px}
.desk-rail-ident{display:flex;align-items:center;gap:10px}
.desk-rail-avatar{
  width:40px;height:40px;border-radius:10px;flex-shrink:0;
  border:1.5px solid #F0F0EE;overflow:hidden;background:#F4F3F1;
  display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#9CA3AF;
}
.desk-rail-avatar img{width:100%;height:100%;object-fit:cover;display:block}
.desk-rail-name{font-size:17px;font-weight:900;color:#0D0D0D;letter-spacing:-.03em}
.desk-rail-role{font-size:11px;color:#9CA3AF;margin-top:1px}
.desk-rail-status{display:flex;align-items:center;justify-content:space-between}
.desk-rail-status-dot{width:7px;height:7px;border-radius:50%;display:inline-block;margin-right:5px}
.desk-rail-status-label{font-size:13px;font-weight:700}
.desk-rail-since{font-size:11px;color:#9CA3AF}
.desk-rail-task-title{font-size:13px;font-weight:700;color:#0D0D0D;margin-bottom:2px}
.desk-rail-task-sub{font-size:11px;color:#9CA3AF;margin-bottom:8px}
.desk-rail-bar{height:4px;background:#F3F4F6;border-radius:99px;overflow:hidden;margin-bottom:3px}
.desk-rail-bar-fill{height:100%;border-radius:99px;background:#6366f1}
.desk-rail-pct{font-size:10px;color:#9CA3AF;text-align:right}
.desk-rail-impact-val{font-size:22px;font-weight:900;color:#0D0D0D;line-height:1.1;margin:4px 0 2px}
.desk-rail-impact-sub{font-size:11px;color:#9CA3AF}
.desk-rail-mem-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:5px}
.desk-rail-mem-bar{height:4px;background:#F3F4F6;border-radius:99px;overflow:hidden;margin-bottom:5px}
.desk-rail-note{font-size:12.5px;color:#374151;line-height:1.65;font-style:italic;margin-bottom:8px}
.desk-rail-sig{font-size:12px;font-weight:700;color:#0D0D0D;display:flex;align-items:center;justify-content:space-between}
.desk-rail-sig svg{width:16px;height:16px;fill:#FDA4AF}

@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}
</style>
</head>
<body>

@php
$activityMap = [
  'draft_ready'  => ['label'=>'Reply ready for approval',   'sub'=>'Ready for your review',           'color'=>'#22c55e'],
  'approved'     => ['label'=>'Reply approved',             'sub'=>'Sent to customer',                 'color'=>'#22c55e'],
  'sent'         => ['label'=>'Reply sent',                 'sub'=>'Delivered successfully',           'color'=>'#22c55e'],
  'failed'       => ['label'=>'Pipeline error',             'sub'=>'Needs your attention',             'color'=>'#ef4444'],
  'reading'      => ['label'=>'Reading incoming email',     'sub'=>'Parsing content',                  'color'=>'#6366f1'],
  'classifying'  => ['label'=>'Classifying email',         'sub'=>'Identifying renewal type',         'color'=>'#f59e0b'],
  'drafting'     => ['label'=>'Drafting renewal reply',    'sub'=>'Preparing personalized response',  'color'=>'#8b5cf6'],
  'ingesting'    => ['label'=>'Renewal request detected',  'sub'=>'Email received',                   'color'=>'#6366f1'],
  'dismissed'    => ['label'=>'Dismissed',                 'sub'=>'',                                 'color'=>'#9CA3AF'],
];
$apColors = ['#6366f1','#f59e0b','#22c55e','#f97316','#8b5cf6','#ec4899'];
$wColors  = ['ava'=>'#6366f1','nux'=>'#f59e0b','dox'=>'#22c55e','mox'=>'#f97316'];
@endphp

<div class="desk-page">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="desk-sidebar">
    <div class="desk-logo">UNIT</div>

    <div class="desk-section-label">
      MY WORKERS
      <a href="{{ route('workers.page') }}" class="desk-hire-btn">+ Hire</a>
    </div>

    <div style="padding:0 6px;margin-bottom:18px">
      @foreach($allDeployments as $wd)
      @php
        $wReg  = $registryRows->get($wd->worker_slug);
        $wImg  = $wReg?->profile_image ? asset('storage/'.$wReg->profile_image) : null;
        $wDot  = $wd->status === 'active' ? '#22c55e' : '#f59e0b';
        $wHref = $wd->worker_slug === 'ava' ? route('desk.ava') : '#';
        $wRole = $wReg->tagline ?? ucfirst($wd->worker_slug).' Specialist';
      @endphp
      <a href="{{ $wHref }}" class="desk-worker {{ $wd->worker_slug === 'ava' ? 'active' : '' }}">
        <div class="desk-worker-avatar">
          @if($wImg)<img src="{{ $wImg }}" alt="{{ $wd->worker_slug }}">@else{{ strtoupper(substr($wd->worker_slug,0,1)) }}@endif
        </div>
        <div class="desk-worker-info">
          <div class="desk-worker-name">{{ strtoupper($wd->worker_slug) }}</div>
          <div class="desk-worker-role">{{ $wRole }}</div>
        </div>
        <span class="desk-worker-dot" style="background:{{ $wDot }}"></span>
      </a>
      @endforeach
    </div>

    <div class="desk-section-label">LINKS</div>
    <div class="desk-links" style="margin-bottom:12px">
      @foreach([
        ['Knowledge Base','M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253','#'],
        ['Templates','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',route('workers.templates','ava')],
        ['Integrations','M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1','#'],
        ['Billing & Plans','M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',route('billing.index')],
        ['Team Members','M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z','#'],
        ['Activity Log','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',route('transactions')],
      ] as [$lbl,$ico,$href])
      <a href="{{ $href }}" class="desk-link">
        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ico }}"/></svg>
        {{ $lbl }}
      </a>
      @endforeach
    </div>

    <div class="desk-sidebar-footer">
      <div class="desk-secure">
        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span class="desk-secure-title">Secure. Private. Yours.</span>
      </div>
      <p class="desk-secure-sub">You're in control of what<br>Ava can see and access.</p>
    </div>
  </aside>

  {{-- ══ MAIN ══ --}}
  <main class="desk-main">

    {{-- Hero --}}
    <div class="desk-hero">
      @if($coverImg)
        <img src="{{ $coverImg }}" alt="AVA's Desk">
      @else
        <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a1a2e,#2d1b69)"></div>
      @endif
      <div class="desk-hero-overlay"></div>
      <div class="desk-hero-inner">
        <div class="desk-onshift">
          <span class="desk-onshift-dot"></span>
          <span class="desk-onshift-text">ON SHIFT</span>
          <span class="desk-onshift-time">{{ now()->format('g:i A') }}</span>
        </div>
        <div>
          <h1 class="desk-hero-title">AVA's Desk</h1>
          <p class="desk-hero-role">Renewal Specialist</p>
          <p class="desk-hero-desc">Ava is working on protecting your renewals and building stronger customer relationships.</p>
        </div>
      </div>

      @if($currentTask)
      @php
        $bt = match($currentTask->status){
          'reading','ingesting','classifying'=>'Reading incoming email',
          'drafting','pushing'=>'Drafting renewal reply',
          'draft_ready'=>'Reply awaiting approval',
          'approved','sent'=>'Reply sent',
          default=>ucfirst(str_replace('_',' ',$currentTask->status)),
        };
        $bc = json_decode($currentTask->classify_output??'{}',true);
        $bClient = $bc['client']??$bc['sender_name']??'';
        $bPct = in_array($currentTask->status,['approved','sent','draft_ready'])?100:67;
      @endphp
      <div class="desk-bubble">
        <div class="desk-bubble-label">
          CURRENT TASK
          <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </div>
        <div class="desk-bubble-title">{{ $bt }}</div>
        @if($bClient)<div class="desk-bubble-sub">Customer: {{ $bClient }}</div>@else<div style="margin-bottom:10px"></div>@endif
        <div class="desk-bar"><div class="desk-bar-fill" style="width:{{ $bPct }}%"></div></div>
        <div class="desk-bar-pct">{{ $bPct }}%</div>
      </div>
      @endif
    </div>

    {{-- Stats --}}
    <div class="desk-stats">
      @foreach([
        ['M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z','#6366f1',$incomingCount,'New emails','Incoming'],
        ['M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15','#f59e0b',$inProgressCount,'Working on it','In Progress'],
        ['M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','#8b5cf6',$waitingCount,'Waiting approval','Waiting'],
        ['M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','#22c55e',$completedCount,'Completed today','Completed'],
      ] as [$ico,$clr,$val,$lbl,$sub])
      <div class="desk-stat">
        <div class="desk-stat-icon" style="background:{{ $clr }}18">
          <svg viewBox="0 0 24 24" style="stroke:{{ $clr }}"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ico }}"/></svg>
        </div>
        <div>
          <div class="desk-stat-num">{{ $val }}</div>
          <div class="desk-stat-lbl">{{ $lbl }}</div>
        </div>
      </div>
      @endforeach
    </div>

    {{-- Live Activity + Approvals --}}
    <div class="desk-bottom">

      <div class="desk-card">
        <div class="desk-card-hd">
          <span class="desk-card-title">Live Activity</span>
          <span class="desk-shift-pill"><span class="desk-shift-dot"></span> On Shift</span>
        </div>
        @if($activity->isEmpty())
          <p style="font-size:12px;color:#9CA3AF;text-align:center;padding:24px 0">No activity yet</p>
        @else
        <div class="desk-act-wrap">
          <div class="desk-act-line"></div>
          @foreach($activity as $tx)
          @php
            $am = $activityMap[$tx->status]??['label'=>ucfirst(str_replace('_',' ',$tx->status)),'sub'=>'','color'=>'#9CA3AF'];
            $cl = json_decode($tx->classify_output??'{}',true);
            $txc = $cl['client']??$cl['sender_name']??'';
          @endphp
          <div class="desk-act-item">
            <div class="desk-act-node" style="background:{{ $am['color'] }}">●</div>
            <div style="flex:1;min-width:0">
              <div class="desk-act-text">{{ $am['label'] }}</div>
              <div class="desk-act-sub">{{ $txc ?: $am['sub'] }}</div>
            </div>
            <span class="desk-act-time">{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</span>
          </div>
          @endforeach
        </div>
        @endif
        <a href="{{ route('transactions') }}" class="desk-view-all">View all activity →</a>
      </div>

      <div class="desk-card">
        <div class="desk-card-hd">
          <div style="display:flex;align-items:center;gap:8px">
            <span class="desk-card-title">Approvals</span>
            @if($waitingCount>0)<span style="font-size:10px;font-weight:700;background:#F5C518;color:#000;border-radius:99px;padding:1px 7px">{{ $waitingCount }}</span>@endif
          </div>
          <a href="{{ route('transactions',['filter'=>'draft_ready']) }}" class="desk-card-link">View all →</a>
        </div>
        @if($approvals->isEmpty())
          <p style="font-size:12px;color:#9CA3AF;text-align:center;padding:24px 0">Nothing awaiting approval</p>
        @else
          @foreach($approvals as $tx)
          @php
            $cl = json_decode($tx->classify_output??'{}',true);
            $dr = json_decode($tx->draft_output??'{}',true);
            $apN  = $cl['client']??$cl['sender_name']??'Unknown';
            $apCo = $cl['company']??'';
            $apPl = $cl['plan']??$cl['product']??'Renewal';
            $apV  = $cl['contract_value']??$cl['renewal_value']??'';
            $apC  = $apColors[abs(crc32($apN))%count($apColors)];
          @endphp
          <div class="desk-ap-item">
            <div class="desk-ap-row">
              <div style="display:flex;align-items:center;gap:9px;min-width:0">
                <div class="desk-ap-avatar" style="background:{{ $apC }}">{{ strtoupper(substr($apN,0,1)) }}</div>
                <div style="min-width:0">
                  <div class="desk-ap-name">{{ $apN }}</div>
                  @if($apCo)<div class="desk-ap-plan">{{ $apCo }}</div>@endif
                  <div class="desk-ap-plan">{{ $apPl }}{{ $apV?' — '.$apV:'' }}</div>
                </div>
              </div>
              <span class="desk-ap-badge">Draft ready</span>
            </div>
            <div class="desk-ap-btns">
              <form method="POST" action="{{ route('transactions.decide',$tx->id) }}" style="flex:1">
                @csrf<input type="hidden" name="decision" value="approve">
                <button type="submit" class="btn-ap-approve">Approve</button>
              </form>
              <a href="{{ route('transactions.show',$tx->id) }}" class="btn-ap-edit">Edit</a>
            </div>
          </div>
          @endforeach
        @endif
        <a href="{{ route('transactions',['filter'=>'draft_ready']) }}" class="desk-ap-footer">{{ $waitingCount }} draft{{ $waitingCount===1?'':'s' }} waiting for your approval →</a>
      </div>

    </div>
  </main>

  {{-- ══ RIGHT RAIL ══ --}}
  <aside class="desk-rail">

    <div class="desk-rail-sec">
      <div class="desk-rail-eye">First Assignment</div>
      <div class="desk-rail-ident">
        <div class="desk-rail-avatar">
          @if($profileImg)<img src="{{ $profileImg }}" alt="AVA">@else A @endif
        </div>
        <div>
          <div class="desk-rail-name">AVA</div>
          <div class="desk-rail-role">Renewal Specialist</div>
        </div>
      </div>
    </div>

    <div class="desk-rail-sec">
      <div class="desk-rail-eye">Work Status</div>
      <div class="desk-rail-status">
        <div>
          <span class="desk-rail-status-dot" style="background:{{ $dep->status==='active'?'#22c55e':'#f59e0b' }};{{ $dep->status==='active'?'animation:pdot 1.4s ease infinite':'' }}"></span>
          <span class="desk-rail-status-label" style="color:{{ $dep->status==='active'?'#22c55e':'#f59e0b' }}">{{ $workStatus }}</span>
        </div>
        <span class="desk-rail-since">Since {{ \Carbon\Carbon::parse($dep->updated_at)->format('g:i A') }}</span>
      </div>
    </div>

    @if($currentTask)
    @php
      $rt = match($currentTask->status){
        'reading','ingesting','classifying'=>'Reading incoming email',
        'drafting','pushing'=>'Drafting renewal reply',
        'draft_ready'=>'Reply awaiting approval',
        'approved','sent'=>'All caught up',
        default=>ucfirst(str_replace('_',' ',$currentTask->status)),
      };
      $rc = json_decode($currentTask->classify_output??'{}',true);
      $rcl = $rc['client']??$rc['sender_name']??'';
      $rp = in_array($currentTask->status,['approved','sent','draft_ready'])?100:67;
    @endphp
    <div class="desk-rail-sec">
      <div class="desk-rail-eye">Current Task</div>
      <div class="desk-rail-task-title">{{ $rt }}</div>
      @if($rcl)<div class="desk-rail-task-sub">Customer: {{ $rcl }}</div>@else<div style="margin-bottom:8px"></div>@endif
      <div class="desk-rail-bar"><div class="desk-rail-bar-fill" style="width:{{ $rp }}%"></div></div>
      <div class="desk-rail-pct">{{ $rp }}%</div>
    </div>
    @endif

    <div class="desk-rail-sec">
      <div class="desk-rail-eye">Today's Impact</div>
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:32px;height:32px;border-radius:8px;background:#FEF3C7;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
          <div style="font-size:9px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#9CA3AF">Renewals Managed</div>
          <div class="desk-rail-impact-val">{{ $incomingCount }}</div>
          <div class="desk-rail-impact-sub">{{ $completedCount }} completed today</div>
        </div>
      </div>
    </div>

    <div class="desk-rail-sec">
      <div class="desk-rail-eye">Memory Access</div>
      <div style="display:flex;align-items:center;gap:9px;margin-bottom:8px">
        <div style="width:28px;height:28px;border-radius:7px;background:#EDE9FE;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
        </div>
        <div style="flex:1">
          <div class="desk-rail-mem-row">
            <span style="font-size:12px;font-weight:600;color:#374151">Memory</span>
            <span style="font-size:12px;font-weight:700;color:#0D0D0D">{{ $clientCount }} clients</span>
          </div>
          <div class="desk-rail-mem-bar"><div style="height:100%;border-radius:99px;background:#8b5cf6;width:{{ min(100,$clientCount*20) }}%"></div></div>
        </div>
      </div>
      <p style="font-size:10.5px;color:#9CA3AF;margin-bottom:14px">Ava knows {{ $clientCount }} {{ $clientCount===1?'client':'clients' }}.</p>
      <div class="desk-rail-eye">Memory &amp; Responsibility</div>
      <div class="desk-rail-mem-row">
        <span style="font-size:13px;font-weight:700;color:#0D0D0D">{{ $clientCount }} / {{ max(5,$clientCount+3) }} Clients</span>
        <span style="font-size:10px;color:#9CA3AF">{{ $clientCount<3?'Light Workload':($clientCount<8?'Medium Workload':'Heavy Workload') }}</span>
      </div>
      <div class="desk-rail-mem-bar"><div style="height:100%;border-radius:99px;background:#F5C518;width:{{ min(100,($clientCount/max(5,$clientCount+3))*100) }}%"></div></div>
    </div>

    @php
      $txTotal = \Illuminate\Support\Facades\DB::table('transactions')->where('deployment_id',$depId)->count();
      $avaNote = $txTotal===0
        ? "Ready and standing by, {$firstName}. Give me something to work on."
        : ($waitingCount>0
          ? "I've drafted {$waitingCount} ".($waitingCount===1?'reply':'replies')." for you to review. Let me know what you think."
          : "All caught up. I'll flag anything that needs your attention.");
    @endphp
    <div class="desk-rail-sec">
      <div class="desk-rail-eye">AVA's Note</div>
      <p class="desk-rail-note">{{ $avaNote }}</p>
      <div class="desk-rail-sig">
        <span>— Ava</span>
        <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
      </div>
    </div>

  </aside>

</div>
</body>
</html>

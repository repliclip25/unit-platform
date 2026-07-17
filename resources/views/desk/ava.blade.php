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
.ob-page{display:grid;grid-template-columns:260px 1fr 360px;height:100vh;overflow:hidden}

/* ── LEFT SIDEBAR ── */
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

/* ── CENTER — plain white, reserved ── */
.ob-center{background:#fff;border-left:1px solid #F0F0EE;border-right:1px solid #F0F0EE}

/* ── RIGHT — live activity + draft (exact step-5 styles) ── */
.ob-sc-right{display:flex;flex-direction:column;overflow:hidden;background:#fff}
.ob-sc-activity{flex:0 0 auto;padding:18px 20px 14px;border-bottom:1px solid #F0F0F0}
.ob-sc-activity-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.ob-sc-activity-title{font-size:10px;font-weight:800;color:#0D0D0D;letter-spacing:.08em;text-transform:uppercase}
.ob-sc-onshift{display:flex;align-items:center;gap:5px;font-size:9px;font-weight:700;color:#15803D;letter-spacing:.08em;text-transform:uppercase;background:#DCFCE7;border-radius:99px;padding:3px 8px}
.ob-sc-onshift-dot{width:5px;height:5px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
.ob-sc-feed{display:flex;flex-direction:column;gap:8px}
.ob-sc-feed-item{display:flex;gap:10px;align-items:flex-start}
.ob-sc-feed-time{font-size:10px;color:#9CA3AF;font-weight:600;white-space:nowrap;padding-top:2px;min-width:44px}
.ob-sc-feed-dot{width:22px;height:22px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:800;color:#fff}
.ob-sc-feed-text{font-size:12px;color:#374151;font-weight:600;line-height:1.4}
.ob-sc-feed-sub{font-size:11px;color:#9CA3AF}
.ob-sc-view-link{font-size:11px;color:#9CA3AF;font-weight:600;text-decoration:none;display:block;margin-top:10px}
.ob-sc-view-link:hover{color:#0D0D0D}

/* Draft card */
#scDraftCard{flex:1;display:flex;flex-direction:column;overflow:hidden;border-top:1px solid #E8EAED}
#scDraftCard > div:not(:last-child){flex-shrink:0}
.sc-draft-chrome{background:#F1F3F4;border-bottom:1px solid #E0E0E0;padding:7px 12px;display:flex;align-items:center;gap:7px;flex-shrink:0}
.sc-draft-body{flex:1;overflow-y:auto;padding:12px 16px}
.sc-draft-header-row{display:flex;align-items:baseline;gap:6px;padding:5px 0;border-bottom:1px solid #F1F3F4}
.sc-draft-header-label{font-size:11px;color:#5F6368;font-weight:600;width:48px;flex-shrink:0}
.sc-draft-header-value{font-size:12px;color:#202124;font-weight:500;line-height:1.4}
.sc-draft-subject-row{padding:8px 0 10px;border-bottom:1px solid #E8EAED;margin-bottom:10px}
.sc-draft-subject-text{font-size:14px;font-weight:700;color:#202124;line-height:1.3}
.sc-draft-preview{font-size:12.5px;color:#3C4043;line-height:1.75;white-space:pre-wrap}
.sc-draft-actions{padding:12px 18px;border-top:1px solid #E0E0E0;display:flex;gap:8px;flex-shrink:0}
.sc-draft-actions button,.sc-draft-actions a{flex:1;padding:10px;border-radius:9px;font-size:12px;font-weight:700;text-align:center;cursor:pointer;font-family:inherit}
.sc-btn-approve{background:#0D0D0D;color:#fff;border:none}
.sc-btn-review{background:#fff;color:#374151;border:1.5px solid #E5E7EB;text-decoration:none;display:flex;align-items:center;justify-content:center}

@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}
</style>
</head>
<body>

@php
$activityColors = ['#6366F1','#F59E0B','#8B5CF6','#22c55e','#f97316','#06b6d4'];
$activityMap = [
  'draft_ready' =>['label'=>'Reply ready for your review','color'=>'#22c55e'],
  'approved'    =>['label'=>'Reply approved',             'color'=>'#22c55e'],
  'sent'        =>['label'=>'Reply sent',                 'color'=>'#22c55e'],
  'failed'      =>['label'=>'Pipeline error',             'color'=>'#ef4444'],
  'reading'     =>['label'=>'Reading incoming email',     'color'=>'#6366f1'],
  'classifying' =>['label'=>'Analyzing email...',         'color'=>'#F59E0B'],
  'drafting'    =>['label'=>'Drafting personalized reply...','color'=>'#8B5CF6'],
  'ingesting'   =>['label'=>'New renewal request detected','color'=>'#6366f1'],
];
// Pick first draft_ready tx for the right panel preview
$previewTx = $approvals->first();
$previewCl = $previewTx ? json_decode($previewTx->classify_output??'{}',true) : null;
$previewDraft = $previewTx ? json_decode($previewTx->draft_output??'{}',true) : null;
@endphp

<div class="ob-page">

  {{-- ── LEFT: MY WORKERS sidebar ── --}}
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

  {{-- ── CENTER: plain white, structure TBD ── --}}
  <div class="ob-center"></div>

  {{-- ── RIGHT: exact step-5 right panel ── --}}
  <div class="ob-sc-right">

    <div class="ob-sc-activity">
      <div class="ob-sc-activity-header">
        <span class="ob-sc-activity-title">Live Activity</span>
        <span class="ob-sc-onshift"><span class="ob-sc-onshift-dot"></span> On Shift</span>
      </div>
      <div class="ob-sc-feed">
        @forelse($activity->take(5) as $i => $tx)
        @php
          $am=$activityMap[$tx->status]??['label'=>ucfirst(str_replace('_',' ',$tx->status)),'color'=>'#9CA3AF'];
          $cl=json_decode($tx->classify_output??'{}',true);
          $txc=$cl['client']??$cl['sender_name']??'';
          $dotClr=$activityColors[$i % count($activityColors)];
        @endphp
        <div class="ob-sc-feed-item">
          <span class="ob-sc-feed-time">{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</span>
          <span class="ob-sc-feed-dot" style="background:{{ $dotClr }}">{{ $i+1 }}</span>
          <div>
            <div class="ob-sc-feed-text">{{ $am['label'] }}</div>
            @if($txc)<div class="ob-sc-feed-sub">{{ $txc }}</div>@endif
          </div>
        </div>
        @empty
        <p style="font-size:12px;color:#9CA3AF">No activity yet — Ava is standing by.</p>
        @endforelse
      </div>
      <a href="{{ route('transactions') }}" class="ob-sc-view-link">View Live Feed →</a>
    </div>

    {{-- Draft preview of first waiting approval --}}
    <div id="scDraftCard" style="{{ $previewTx ? '' : 'display:none' }}">
      <div class="sc-draft-chrome">
        <span style="width:10px;height:10px;border-radius:50%;background:#FF5F57;display:inline-block;flex-shrink:0"></span>
        <span style="width:10px;height:10px;border-radius:50%;background:#FEBC2E;display:inline-block;flex-shrink:0"></span>
        <span style="width:10px;height:10px;border-radius:50%;background:#28C840;display:inline-block;flex-shrink:0"></span>
        <div style="flex:1;background:#fff;border:1px solid #DADCE0;border-radius:99px;padding:3px 10px;display:flex;align-items:center;gap:6px;margin:0 6px;min-width:0">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#5F6368" stroke-width="2" style="flex-shrink:0"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
          <span style="font-size:9.5px;color:#5F6368;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">mail.google.com/mail/u/0/#drafts</span>
        </div>
        <svg width="40" height="14" viewBox="0 0 55 18" fill="none" style="flex-shrink:0">
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
      @if($previewTx)
      <div class="sc-draft-actions">
        <form method="POST" action="{{ route('transactions.decide',$previewTx->id) }}" style="flex:1;display:contents">
          @csrf<input type="hidden" name="decision" value="approve">
          <button type="submit" class="sc-btn-approve">Approve &amp; send</button>
        </form>
        <a href="{{ route('transactions.show',$previewTx->id) }}" class="sc-btn-review">Review in full</a>
      </div>
      @endif
    </div>

  </div>

</div>
</body>
</html>

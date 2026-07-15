<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>First Assignment — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{font-family:'Inter',sans-serif;background:#F4F3F1;color:#0D0D0D;-webkit-font-smoothing:antialiased}

.ob-page{display:grid;grid-template-columns:260px 1fr;height:100vh;overflow:hidden}

/* ── SIDEBAR ── */
.ob-sidebar{background:#F4F3F1;display:flex;flex-direction:column;padding:32px 24px;overflow-y:auto}
.ob-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;margin-bottom:44px}
.ob-steps{display:flex;flex-direction:column;flex:1}
.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative;text-decoration:none;color:inherit}
.ob-step:not(:last-child) .ob-step-rail::after{content:'';position:absolute;left:13px;top:30px;width:2px;height:calc(100% - 6px);background:#DCDCDC;border-radius:2px}
.ob-step.done:not(:last-child) .ob-step-rail::after{background:#0D0D0D}
.ob-step-rail{position:relative;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding-bottom:32px}
.ob-step:last-child .ob-step-rail{padding-bottom:0}
.ob-step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;position:relative;z-index:1;flex-shrink:0}
.ob-step.pending .ob-step-num{background:#E8E7E4;color:#888;border:1.5px solid #DCDCDC}
.ob-step.active  .ob-step-num{background:#0D0D0D;color:#fff;box-shadow:0 0 0 4px rgba(0,0,0,.1)}
.ob-step.done    .ob-step-num{background:#22c55e;color:#fff}
.ob-step-body{padding-top:4px;padding-bottom:28px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:13.5px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-step.pending .ob-step-label{color:#6B7280}
.ob-step-desc{font-size:12px;color:#9CA3AF;margin-top:3px;line-height:1.4}
.ob-step.active .ob-step-desc{color:#374151}
.ob-step.active .ob-step-body{background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:10px 14px;margin-right:-4px}
.ob-security{margin-top:8px;padding:14px 16px;border-radius:12px;background:#ECEAE6;border:1px solid #DCDCDC}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:5px}
.ob-security-row svg{width:13px;height:13px;stroke:#6B7280;flex-shrink:0}
.ob-security-title{font-size:12px;font-weight:700;color:#0D0D0D}
.ob-security p{font-size:11px;color:#6B7280;line-height:1.55}

/* ── MAIN AREA ── */
.ob-main{display:flex;align-items:stretch;padding:20px 24px 20px 12px;overflow:hidden;gap:0}

/* Wide card — horizontal pipeline */
.ob-pipeline-card{
  display:grid;
  grid-template-columns:280px 1fr 1fr 1fr 230px;
  width:100%;
  border-radius:20px;overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);
  border:1px solid rgba(0,0,0,.07);
  background:#fff;
}

/* ── LEFT: Intro + input ── */
.ob-left{
  background:#F4F3F1;border-right:1px solid #E8E7E4;
  padding:32px 24px;display:flex;flex-direction:column;overflow-y:auto;
}
.ob-step-tag{
  display:inline-flex;align-items:center;gap:8px;
  font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
  color:#6B7280;margin-bottom:14px;
}
.ob-step-tag svg{width:14px;height:14px;stroke:#6B7280;stroke-width:2;fill:none}
.ob-h1{font-size:1.5rem;font-weight:900;letter-spacing:-.04em;line-height:1.1;color:#0D0D0D;margin-bottom:8px}
.ob-gold{color:#0D0D0D;position:relative;display:inline}
.ob-gold::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}
.ob-sub{font-size:12.5px;color:#374151;line-height:1.65;margin-bottom:20px}

/* Drop zone */
.ob-dropzone{
  border:2px dashed #D1D5DB;border-radius:14px;
  padding:20px 16px;text-align:center;
  background:#fff;cursor:pointer;transition:border-color .15s;
  margin-bottom:10px;flex-shrink:0;
}
.ob-dropzone:hover{border-color:#0D0D0D}
.ob-dropzone-icon{width:36px;height:36px;border-radius:10px;border:1.5px solid #E5E7EB;background:#F9FAFB;display:flex;align-items:center;justify-content:center;margin:0 auto 10px}
.ob-dropzone-icon svg{width:18px;height:18px;stroke:#9CA3AF;stroke-width:1.8;fill:none}
.ob-dropzone-title{font-size:12.5px;font-weight:700;color:#374151;margin-bottom:3px}
.ob-dropzone-hint{font-size:11px;color:#9CA3AF}
#emailPaste{width:100%;min-height:80px;border:1.5px solid #E5E7EB;border-radius:10px;padding:10px;font-size:12px;font-family:inherit;color:#0D0D0D;resize:none;outline:none;margin-top:8px;display:none}
#emailPaste:focus{border-color:#0D0D0D}

.ob-inbox-btn{
  display:flex;align-items:center;justify-content:center;gap:7px;
  width:100%;padding:10px;border-radius:10px;
  border:1.5px solid #E5E7EB;background:#fff;cursor:pointer;
  font-size:12px;font-weight:600;color:#374151;font-family:inherit;
  transition:border-color .15s;margin-bottom:16px;
}
.ob-inbox-btn:hover{border-color:#0D0D0D}
.ob-inbox-btn svg{width:14px;height:14px;stroke:#6B7280;stroke-width:2;fill:none}

/* Run button */
.btn-run{
  display:flex;align-items:center;justify-content:center;gap:8px;
  width:100%;padding:12px;border-radius:12px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  font-size:13px;font-weight:800;font-family:inherit;
  transition:opacity .15s;margin-top:auto;
}
.btn-run:hover{opacity:.88}
.btn-run svg{width:15px;height:15px;stroke:#fff;stroke-width:2.5;fill:none}
.btn-run .spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:none}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── PIPELINE STAGE PANELS ── */
.ob-stage{
  display:flex;flex-direction:column;
  border-right:1px solid #F0F0F0;
  overflow:hidden;position:relative;
  transition:background .3s;
}
.ob-stage:last-of-type{border-right:none}

.ob-stage-header{
  padding:18px 18px 0;flex-shrink:0;
}
.ob-stage-num{
  font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:#9CA3AF;margin-bottom:6px;
}
.ob-stage-title{font-size:14px;font-weight:800;color:#0D0D0D;margin-bottom:0}

/* AVA image area */
.ob-stage-img{
  flex:1;position:relative;overflow:hidden;min-height:0;
  background:#F4F3F1;
}
.ob-stage-img img{width:100%;height:100%;object-fit:cover;object-position:center top;opacity:.35;transition:opacity .5s}
.ob-stage.is-active .ob-stage-img img,.ob-stage.is-done .ob-stage-img img{opacity:1}

/* Pulse overlay when active */
.ob-stage.is-active .ob-stage-img::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(to bottom,transparent 60%,rgba(255,255,255,.6));
}

/* Stage footer */
.ob-stage-footer{
  padding:12px 18px;flex-shrink:0;
  border-top:1px solid #F3F4F6;background:#fff;
}
.ob-progress-bar{height:3px;background:#F3F4F6;border-radius:99px;overflow:hidden;margin-bottom:6px}
.ob-progress-fill{height:100%;background:#F5C518;border-radius:99px;width:0;transition:width 1s ease}
.ob-stage.is-done .ob-progress-fill{background:#22c55e;width:100%}
.ob-stage-status{font-size:11px;color:#9CA3AF;font-weight:600;display:flex;align-items:center;gap:5px}
.ob-stage-status-dot{width:6px;height:6px;border-radius:50%;background:#D1D5DB}
.ob-stage.is-active .ob-stage-status-dot{background:#F5C518;animation:pdot 1s ease infinite}
.ob-stage.is-done .ob-stage-status-dot{background:#22c55e}
@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}

/* Connector arrow between stages */
.ob-arrow{
  position:absolute;right:-16px;top:50%;transform:translateY(-50%);
  width:32px;height:32px;background:#fff;border:1.5px solid #E8E7E4;
  border-radius:50%;display:flex;align-items:center;justify-content:center;
  z-index:5;box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.ob-arrow svg{width:13px;height:13px;stroke:#9CA3AF;stroke-width:2}

/* Draft email preview */
.ob-draft-preview{
  flex:1;overflow-y:auto;padding:12px 16px;
  background:#FAFAFA;
}
.ob-draft-preview::-webkit-scrollbar{width:3px}
.ob-draft-preview::-webkit-scrollbar-thumb{background:rgba(0,0,0,.1);border-radius:2px}
.ob-draft-field{font-size:11px;color:#9CA3AF;margin-bottom:2px}
.ob-draft-field strong{color:#374151}
.ob-draft-divider{border:none;border-top:1px solid #F0F0F0;margin:8px 0}
.ob-draft-body{font-size:11.5px;color:#374151;line-height:1.7;white-space:pre-wrap}

/* Confidence score */
.ob-confidence{
  display:flex;align-items:center;gap:10px;
  padding:10px 16px;border-top:1px solid #F0F0F0;
  background:#fff;flex-shrink:0;
}
.ob-confidence-ring{width:32px;height:32px;flex-shrink:0}
.ob-confidence-ring svg{width:32px;height:32px;transform:rotate(-90deg)}
.ob-confidence-bg{fill:none;stroke:#F3F4F6;stroke-width:4}
.ob-confidence-fill{fill:none;stroke:#22c55e;stroke-width:4;stroke-linecap:round;stroke-dasharray:88;stroke-dashoffset:22}
.ob-confidence-label{font-size:11.5px;font-weight:700;color:#0D0D0D}
.ob-confidence-sub{font-size:10px;color:#9CA3AF}

/* ── RIGHT: AVA Says ── */
.ob-ava-says{
  background:#fff;border-left:1px solid #F0F0F0;
  padding:24px 20px;display:flex;flex-direction:column;
}
.ob-ava-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:12px}
.ob-ava-profile{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.ob-ava-avatar{width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #F0F0F0}
.ob-ava-quote{font-size:12.5px;font-weight:600;color:#0D0D0D;line-height:1.55;margin-bottom:6px}
.ob-ava-quote-sub{font-size:11.5px;color:#6B7280;line-height:1.5}
.ob-ava-actions{display:flex;flex-direction:column;gap:8px;margin-top:auto}

.btn-approve{
  display:flex;align-items:center;gap:8px;justify-content:center;
  padding:12px 16px;border-radius:12px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  font-size:13px;font-weight:800;font-family:inherit;
  transition:opacity .15s;
}
.btn-approve:hover{opacity:.88}
.btn-approve svg{width:16px;height:16px;stroke:#fff;stroke-width:2;fill:none}

.btn-edit{
  display:flex;align-items:center;gap:8px;justify-content:center;
  padding:11px 16px;border-radius:12px;
  background:#fff;color:#374151;border:1.5px solid #E5E7EB;cursor:pointer;
  font-size:13px;font-weight:700;font-family:inherit;
  transition:border-color .15s;
}
.btn-edit:hover{border-color:#0D0D0D;color:#0D0D0D}
.btn-edit svg{width:15px;height:15px;stroke:currentColor;stroke-width:2;fill:none}

/* Waiting state */
.ob-waiting{
  flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
  text-align:center;padding:20px;opacity:.4;
}
.ob-waiting svg{width:32px;height:32px;stroke:#9CA3AF;stroke-width:1.5;fill:none;margin-bottom:10px}
.ob-waiting p{font-size:12px;color:#9CA3AF;line-height:1.5}

/* No deployment warning */
.ob-no-dep{
  background:rgba(245,197,24,.08);border:1px solid rgba(245,197,24,.3);
  border-radius:12px;padding:12px 14px;margin-bottom:14px;
  font-size:12px;color:#92400e;line-height:1.55;
}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow:auto;height:auto}
  .ob-page{grid-template-columns:1fr;height:auto}
  .ob-sidebar{flex-direction:row;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #E5E7EB;background:#fff;position:sticky;top:0;z-index:10}
  .ob-logo{margin-bottom:0;font-size:18px}
  .ob-steps{flex-direction:row;gap:8px;flex:0;align-items:center}
  .ob-step{flex-direction:column;align-items:center;gap:0}
  .ob-step-rail{padding-bottom:0}
  .ob-step:not(:last-child) .ob-step-rail::after{display:none}
  .ob-step-body{display:none}
  .ob-step-num{width:26px;height:26px;font-size:11px}
  .ob-security{display:none}
  .ob-main{padding:16px;overflow:visible;height:auto;flex-direction:column}
  .ob-pipeline-card{grid-template-columns:1fr;border-radius:16px}
  .ob-stage{min-height:280px}
  .ob-ava-says{padding:20px}
  .ob-arrow{display:none}
}
</style>
</head>
<body>

@php
  $depId     = $deployment?->id;
  $hasGmail  = !is_null($credential);
  $txId      = $watchTxId ?? null;
@endphp

<div class="ob-page">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ob-sidebar">
    <div class="ob-logo">UNIT</div>
    <div class="ob-steps">

      <a href="{{ route('hire.ava.welcome') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail"><div class="ob-step-num"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
        <div class="ob-step-body"><div class="ob-step-label">Meet Ava</div><div class="ob-step-desc">Complete</div></div>
      </a>

      <a href="{{ route('hire.ava.workspace') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail"><div class="ob-step-num"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
        <div class="ob-step-body"><div class="ob-step-label">Workspace</div><div class="ob-step-desc">Complete</div></div>
      </a>

      <a href="{{ route('hire.ava.orientation') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail"><div class="ob-step-num"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
        <div class="ob-step-body"><div class="ob-step-label">Orientation</div><div class="ob-step-desc">Complete</div></div>
      </a>

      <a href="{{ route('hire.ava.assignment') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail"><div class="ob-step-num"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
        <div class="ob-step-body"><div class="ob-step-label">First Assignment</div><div class="ob-step-desc">Complete</div></div>
      </a>

      <div class="ob-step active">
        <div class="ob-step-rail"><div class="ob-step-num">5</div></div>
        <div class="ob-step-body">
          <div class="ob-step-label">On Shift</div>
          <div class="ob-step-desc">Ava starts working for you</div>
        </div>
      </div>

    </div>
    <div class="ob-security">
      <div class="ob-security-row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>You're in control of what<br>Ava can see and access.</p>
    </div>
  </aside>

  {{-- ══ PIPELINE CARD ══ --}}
  <div class="ob-main">
    <div class="ob-pipeline-card">

      {{-- LEFT: Intro + trigger --}}
      <div class="ob-left">
        <div class="ob-step-tag">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          Step 5 of 5
        </div>

        <h1 class="ob-h1">Let's give Ava her <span class="ob-gold">first assignment.</span></h1>
        <p class="ob-sub">Send a real renewal email and Ava will draft a reply for you. You're always in control.</p>

        @if(session('error'))
        <div class="ob-no-dep" style="background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.3);color:#991b1b">
          {{ session('error') }}
        </div>
        @endif

        @if(!$depId)
        <div class="ob-no-dep">No AVA deployment found. Complete the previous steps to set up your workspace first.</div>
        @elseif(!$hasGmail)
        <div class="ob-no-dep">Connect your Gmail in Step 2 before running Ava's first assignment.</div>
        @endif

        @if($depId)
        {{-- Drop zone + paste area --}}
        <div class="ob-dropzone" id="dropzone" onclick="togglePaste()">
          <div class="ob-dropzone-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
          <div class="ob-dropzone-title">Drag &amp; drop an email here</div>
          <div class="ob-dropzone-hint">or paste the content</div>
          <textarea id="emailPaste" name="email_content" placeholder="Paste email content here..." onclick="event.stopPropagation()"></textarea>
        </div>

        <form method="POST" action="{{ route('hire.ava.onshift.run') }}" id="fastTrackForm">
          @csrf
          <button type="button" class="ob-inbox-btn" onclick="submitRun()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Give Ava a sample assignment
          </button>
          <button type="button" class="btn-run" id="runBtn" onclick="submitRun()">
            <span id="runLabel">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              Give Ava this assignment
            </span>
            <div class="spinner" id="runSpinner"></div>
          </button>
        </form>
        @endif
      </div>

      {{-- STAGE 1: Analyzing --}}
      <div class="ob-stage" id="stage1">
        <div class="ob-stage-header">
          <div class="ob-stage-num">1. Ava is analyzing...</div>
        </div>
        <div class="ob-stage-img">
          <img src="/images/ava-stand.png" alt="Ava analyzing">
        </div>
        <div class="ob-stage-footer">
          <div class="ob-progress-bar"><div class="ob-progress-fill" id="prog1"></div></div>
          <div class="ob-stage-status">
            <span class="ob-stage-status-dot"></span>
            <span id="status1">Waiting...</span>
          </div>
        </div>
        <div class="ob-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>
      </div>

      {{-- STAGE 2: Drafting --}}
      <div class="ob-stage" id="stage2">
        <div class="ob-stage-header">
          <div class="ob-stage-num">2. Ava is drafting...</div>
        </div>
        <div class="ob-stage-img">
          <img src="/images/ava-desk.png" alt="Ava drafting">
        </div>
        <div class="ob-stage-footer">
          <div class="ob-progress-bar"><div class="ob-progress-fill" id="prog2"></div></div>
          <div class="ob-stage-status">
            <span class="ob-stage-status-dot"></span>
            <span id="status2">Waiting...</span>
          </div>
        </div>
        <div class="ob-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>
      </div>

      {{-- STAGE 3: Reply ready --}}
      <div class="ob-stage" id="stage3" style="grid-column:auto">
        <div class="ob-stage-header" style="padding-bottom:8px">
          <div class="ob-stage-num">3. Reply is ready!</div>
        </div>
        <div class="ob-draft-preview" id="draftPreview">
          <div class="ob-waiting" id="draftWaiting">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p>Ava's draft will<br>appear here</p>
          </div>
          <div id="draftContent" style="display:none">
            <p class="ob-draft-field">To: <strong id="draftTo">—</strong></p>
            <p class="ob-draft-field">Subject: <strong id="draftSubject">—</strong></p>
            <hr class="ob-draft-divider">
            <p class="ob-draft-body" id="draftBody"></p>
          </div>
        </div>
        <div class="ob-confidence" id="confidenceRow" style="display:none">
          <div class="ob-confidence-ring">
            <svg viewBox="0 0 32 32">
              <circle class="ob-confidence-bg" cx="16" cy="16" r="14"/>
              <circle class="ob-confidence-fill" cx="16" cy="16" r="14" id="confFill"/>
            </svg>
          </div>
          <div>
            <div class="ob-confidence-label">Confidence Score</div>
            <div class="ob-confidence-sub" id="confLabel">Calculating...</div>
          </div>
        </div>
      </div>

      {{-- RIGHT: AVA Says --}}
      <div class="ob-ava-says">
        <div class="ob-ava-eyebrow">AVA SAYS</div>
        <div class="ob-ava-profile">
          <img src="/images/ava.png" alt="AVA" class="ob-ava-avatar">
        </div>
        <p class="ob-ava-quote" id="avaQuote">{{ $watchTxId ? 'Working on it...' : 'Ready when you are.' }}</p>
        <p class="ob-ava-quote-sub" id="avaSub">{{ $watchTxId ? 'I\'ll update you as I go.' : 'Give me an assignment and I\'ll get started.' }}</p>

        <div class="ob-ava-actions" id="avaActions" style="opacity:.3;pointer-events:none">
          <button class="btn-approve" id="approveBtn" onclick="approveDraft()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3H14z"/></svg>
            Looks good, approve it
          </button>
          <button class="btn-edit" id="editBtn" onclick="editDraft()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            I'll make some edits
          </button>
          <button class="btn-edit" id="retryBtn" onclick="location.href='{{ route('hire.ava.onshift') }}'" style="display:none;border-color:#FCA5A5;color:#DC2626">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Try again
          </button>
          <a href="{{ route('dashboard') }}" class="btn-edit" id="dashBtn" style="text-decoration:none;margin-top:6px;border-color:#E5E7EB;font-size:11.5px;color:#9CA3AF">
            Go to dashboard →
          </a>
        </div>
      </div>

    </div>
  </div>

</div>

<script>
const DEP_ID  = {{ $depId ?? 'null' }};
const STATUS_URL = '/workers/ava/status/';
const CSRF    = document.querySelector('meta[name=csrf-token]').content;

let txId      = {{ $watchTxId ? '"'.$watchTxId.'"' : 'null' }};
let pollTimer = null;
let stage     = 0; // 0=idle, 1=analyzing, 2=drafting, 3=done

function togglePaste(){
  const ta = document.getElementById('emailPaste');
  ta.style.display = ta.style.display === 'none' ? 'block' : 'none';
  if(ta.style.display === 'block') ta.focus();
}

function setStage(n, statusText1, statusText2){
  // Stage 1
  const s1 = document.getElementById('stage1');
  s1.classList.toggle('is-active', n === 1);
  s1.classList.toggle('is-done', n > 1);
  document.getElementById('prog1').style.width = n > 1 ? '100%' : (n === 1 ? '60%' : '0');
  document.getElementById('status1').textContent = n === 0 ? 'Waiting...' : (n === 1 ? (statusText1 || 'Reading email...') : 'Done');

  // Stage 2
  const s2 = document.getElementById('stage2');
  s2.classList.toggle('is-active', n === 2);
  s2.classList.toggle('is-done', n > 2);
  document.getElementById('prog2').style.width = n > 2 ? '100%' : (n === 2 ? '55%' : '0');
  document.getElementById('status2').textContent = n < 2 ? 'Waiting...' : (n === 2 ? (statusText2 || 'Writing reply...') : 'Done');

  // Stage 3
  document.getElementById('stage3').classList.toggle('is-done', n === 3);
}

function showDraft(data){
  const draft = data.draft_output;
  if(!draft) return;

  document.getElementById('draftWaiting').style.display = 'none';
  document.getElementById('draftContent').style.display = 'block';

  const subject = draft.subject || data.classify_output?.subject || 'Renewal Response';
  const body    = draft.body   || draft.draft || '';
  const toName  = data.memory_output?.contact_name || data.memory_output?.client_name || 'Client';

  document.getElementById('draftTo').textContent      = toName;
  document.getElementById('draftSubject').textContent  = subject;
  document.getElementById('draftBody').textContent     = body;

  // Confidence
  const conf = draft.confidence_score ?? 0.82;
  const pct  = Math.round(conf * 100);
  const circ = 88;
  document.getElementById('confFill').style.strokeDashoffset = circ - (circ * conf);
  document.getElementById('confLabel').textContent = pct + '% confidence';
  document.getElementById('confidenceRow').style.display = 'flex';

  // AVA says
  document.getElementById('avaQuote').textContent = 'How did I do?';
  document.getElementById('avaSub').textContent   = 'Let me know if you\'d like me to adjust anything.';
  document.getElementById('avaActions').style.opacity = '1';
  document.getElementById('avaActions').style.pointerEvents = 'auto';

  // Store draft ID for approve action
  window._txId = data.tx_id;
  window._gmailDraftId = data.gmail_draft_id;

  setStage(3);
  stage = 3;
}

const ERROR_MESSAGES = {
  blocked: {
    quote: "I hit a billing limit and couldn't finish.",
    sub:   "Your trial quota is used up. Subscribe or contact support to continue — your setup is saved.",
    stage: "Blocked by billing policy"
  },
  failed: {
    quote: "Something went wrong on my end.",
    sub:   "I ran into a technical error processing this email. Try running again, or check the dashboard.",
    stage: "Processing failed"
  },
  rejected: {
    quote: "This email didn't match my rules.",
    sub:   "It didn't meet the criteria in your capture rules, so I skipped it. You can adjust your rules in the dashboard.",
    stage: "Skipped by rules"
  },
  dismissed: {
    quote: "This one was dismissed.",
    sub:   "The email was manually dismissed before I could finish. Run another assignment when you're ready.",
    stage: "Dismissed"
  },
};

function showError(s, stageLabel){
  clearInterval(pollTimer);
  const msg = ERROR_MESSAGES[s] || { quote: 'Ava ran into an issue.', sub: 'Check the dashboard for details.', stage: 'Error' };

  // Surface in AVA Says
  document.getElementById('avaQuote').textContent = msg.quote;
  document.getElementById('avaSub').textContent   = msg.sub;

  // Show retry + dashboard in actions area, hide approve/edit
  document.getElementById('avaActions').style.opacity      = '1';
  document.getElementById('avaActions').style.pointerEvents = 'auto';
  document.getElementById('approveBtn').style.display      = 'none';
  document.getElementById('editBtn').style.display         = 'none';
  document.getElementById('retryBtn').style.display        = 'flex';

  // Mark whichever stage was active as errored
  const activeStage = document.querySelector('.ob-stage.is-active');
  if(activeStage){
    activeStage.classList.remove('is-active');
    activeStage.style.background = 'rgba(239,68,68,.04)';
    activeStage.querySelector('.ob-stage-status-dot').style.background = '#ef4444';
    activeStage.querySelector('[id^=status]').textContent = msg.stage;
  }
}

function poll(){
  if(!txId) return;
  fetch(STATUS_URL + txId, { credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
    .then(r => {
      if(r.status === 401){ showError('failed'); clearInterval(pollTimer); return null; }
      return r.json();
    })
    .then(data => {
      if(!data) return;
      const s = data.status;

      // ── Terminal success ──
      if(['draft_ready','approved','sent'].includes(s) || data.draft_output){
        clearInterval(pollTimer);
        showDraft(data);
        return;
      }

      // ── Terminal failures — surface immediately ──
      if(['blocked','failed','rejected','dismissed'].includes(s)){
        showError(s);
        return;
      }

      // ── In-progress — advance stages ──
      if(data.classify_output || data.memory_output || s === 'drafting' || s === 'generating'){
        setStage(2, null, 'Writing reply...');
        stage = 2;
      } else if(data.read_output || s === 'reading' || s === 'classifying'){
        setStage(1, s === 'classifying' ? 'Classifying email...' : 'Reading email...', null);
        stage = 1;
      } else if(s === 'logging' || s === 'selecting_template'){
        setStage(2, null, 'Selecting template...');
        stage = 2;
      }
      // If status is still 'received'/'ingest', stay on stage 1 waiting
    })
    .catch(() => {
      // Network error — don't freeze, just skip this tick
    });
}

function submitRun(){
  if(!DEP_ID){ return; }
  document.getElementById('runLabel').style.display = 'none';
  document.getElementById('runSpinner').style.display = 'block';
  document.getElementById('runBtn').disabled = true;
  document.getElementById('fastTrackForm').submit();
}

function approveDraft(){
  if(!window._txId) return;
  fetch('/transactions/' + window._txId + '/decide', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ decision: 'approve' })
  }).then(() => {
    document.getElementById('avaQuote').textContent = 'Sent to Gmail Drafts!';
    document.getElementById('avaSub').textContent   = "Check your Drafts folder — it's ready to review and send.";
    document.getElementById('approveBtn').textContent = '✓ Approved';
    document.getElementById('approveBtn').style.background = '#22c55e';
  });
}

function editDraft(){
  if(window._gmailDraftId){
    window.open('https://mail.google.com/mail/u/0/#drafts/' + window._gmailDraftId, '_blank');
  } else {
    window.open('https://mail.google.com/mail/u/0/#drafts', '_blank');
  }
}

// On page load with ?watch=txId — job was just dispatched, start polling
if(txId){
  setStage(1, 'Reading email...', null);
  stage = 1;
  poll(); // immediate first hit
  pollTimer = setInterval(poll, 2000);
}
</script>

<x-self-learn pageKey="hire.ava.onshift" />
</body>
</html>

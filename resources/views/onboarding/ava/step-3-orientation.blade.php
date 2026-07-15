<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Orientation — UNIT</title>
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

/* ── CARD AREA ── */
.ob-card-area{display:flex;align-items:center;justify-content:center;padding:20px 24px 20px 12px;overflow:hidden}
.ob-card{
  display:grid;grid-template-columns:1fr 290px;
  width:100%;height:100%;max-height:calc(100vh - 40px);
  border-radius:20px;overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);
  border:1px solid rgba(0,0,0,.07);
}

/* ── HERO ── */
.ob-hero{position:relative;overflow:hidden;background:#2a2420;display:flex;flex-direction:column}
.ob-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 30%}
.ob-hero-fade{
  position:absolute;inset:0;z-index:1;pointer-events:none;
  background:linear-gradient(to right,#fff 0%,#fff 26%,rgba(255,255,255,.88) 40%,rgba(255,255,255,.3) 58%,transparent 74%);
}
.ob-hero-content{
  position:relative;z-index:2;
  padding:28px 36px 24px;max-width:480px;
  display:flex;flex-direction:column;flex:1;
  overflow-y:auto;
}

/* Thin custom scrollbar inside hero */
.ob-hero-content::-webkit-scrollbar{width:4px}
.ob-hero-content::-webkit-scrollbar-track{background:transparent}
.ob-hero-content::-webkit-scrollbar-thumb{background:rgba(0,0,0,.12);border-radius:2px}

/* Step eyebrow */
.ob-step-tag{
  display:inline-flex;align-items:center;gap:9px;
  font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
  color:#6B7280;margin-bottom:14px;width:fit-content;flex-shrink:0;
}
.ob-step-tag svg{width:16px;height:16px;stroke:#6B7280;stroke-width:2;fill:none;flex-shrink:0}

.ob-h1{font-size:clamp(1.6rem,2.1vw,2.1rem);font-weight:900;letter-spacing:-.04em;line-height:1.1;color:#0D0D0D;margin-bottom:8px;flex-shrink:0}
.ob-gold{color:#0D0D0D;position:relative;display:inline}
.ob-gold::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}

.ob-sub{font-size:13px;color:#374151;line-height:1.65;margin-bottom:16px;flex-shrink:0}

/* ── TOPIC CARDS GRID ── */
.ob-topics{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-bottom:14px}

.ob-topic{
  background:rgba(255,255,255,.92);border:1.5px solid rgba(0,0,0,.07);
  border-radius:12px;cursor:pointer;
  transition:border-color .15s,box-shadow .15s;
  overflow:hidden;
  backdrop-filter:blur(4px);
}
.ob-topic:hover{border-color:rgba(0,0,0,.15);box-shadow:0 2px 8px rgba(0,0,0,.06)}
.ob-topic.is-open{border-color:#0D0D0D;box-shadow:0 2px 12px rgba(0,0,0,.1)}
.ob-topic.is-filled{border-color:#22c55e}

/* Card header row */
.ob-topic-header{display:flex;align-items:flex-start;gap:9px;padding:11px 12px;user-select:none}
.ob-topic-icon{
  width:28px;height:28px;border-radius:7px;
  background:#F4F3F1;border:1px solid #E8E7E4;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;
}
.ob-topic-icon svg{width:14px;height:14px;stroke:#374151;stroke-width:1.8;fill:none}
.ob-topic-text{flex:1;min-width:0}
.ob-topic-title{font-size:12px;font-weight:700;color:#0D0D0D;line-height:1.2;margin-bottom:2px}
.ob-topic-desc{font-size:10.5px;color:#6B7280;line-height:1.4}
.ob-topic-check{
  width:18px;height:18px;border-radius:50%;flex-shrink:0;
  background:#22c55e;display:flex;align-items:center;justify-content:center;
  opacity:0;transition:opacity .2s;margin-top:1px;
}
.ob-topic-check svg{width:10px;height:10px;stroke:#fff;stroke-width:3}
.ob-topic.is-filled .ob-topic-check{opacity:1}

/* Expandable textarea */
.ob-topic-expand{
  max-height:0;overflow:hidden;
  transition:max-height .25s ease;
}
.ob-topic.is-open .ob-topic-expand{max-height:140px}
.ob-topic-expand-inner{padding:0 12px 11px}
.ob-topic-textarea{
  width:100%;border:1.5px solid #E5E7EB;border-radius:8px;
  padding:9px 11px;font-size:12px;font-family:inherit;
  color:#0D0D0D;resize:none;outline:none;line-height:1.55;
  background:#FAFAFA;min-height:82px;
  transition:border-color .15s;
}
.ob-topic-textarea::placeholder{color:#B0B7C3}
.ob-topic-textarea:focus{border-color:#0D0D0D;background:#fff}

/* Footer note + Continue button */
.ob-footer-note{font-size:11.5px;color:#9CA3AF;margin-bottom:14px;flex-shrink:0}
.btn-continue{
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 20px;border-radius:13px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  font-size:14.5px;font-weight:800;letter-spacing:-.01em;
  transition:opacity .15s,transform .1s;
  width:100%;flex-shrink:0;
  opacity:.45;pointer-events:none;
}
.btn-continue.is-active{opacity:1;pointer-events:auto}
.btn-continue.is-active:hover{opacity:.88;transform:translateY(-1px)}
.btn-continue svg{width:17px;height:17px;stroke:#fff;stroke-width:2.5;flex-shrink:0}

/* AVA speech bubble over image */
.ob-bubble{
  position:absolute;z-index:3;
  top:44%;right:6%;
  transform:translateY(-50%);
  background:#fff;border:1px solid #E5E7EB;
  border-radius:16px;border-bottom-left-radius:4px;
  padding:14px 18px;width:190px;
  box-shadow:0 4px 16px rgba(0,0,0,.1);
}
.ob-bubble p{font-size:12.5px;font-weight:600;color:#0D0D0D;line-height:1.55}

/* ── RIGHT PANEL ── */
.ob-profile{
  background:#fff;border-left:1px solid #F0F0F0;
  padding:28px 22px;display:flex;flex-direction:column;overflow-y:auto;
}
.emp-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:14px}

/* Circular progress */
.ob-progress-wrap{display:flex;flex-direction:column;align-items:center;margin-bottom:10px}
.ob-progress-ring{position:relative;width:96px;height:96px}
.ob-progress-ring svg{width:96px;height:96px;transform:rotate(-90deg)}
.ob-progress-bg{fill:none;stroke:#F3F4F6;stroke-width:8}
.ob-progress-fill{
  fill:none;stroke:#F5C518;stroke-width:8;stroke-linecap:round;
  stroke-dasharray:251;
  stroke-dashoffset:251;
  transition:stroke-dashoffset .5s ease;
}
.ob-progress-label{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.ob-progress-pct{font-size:20px;font-weight:900;color:#0D0D0D;letter-spacing:-.04em;line-height:1}
.ob-progress-sub{font-size:10px;color:#9CA3AF;margin-top:2px;font-weight:600}
.ob-progress-caption{font-size:12px;color:#6B7280;text-align:center;margin-top:8px;margin-bottom:16px;line-height:1.45}

.emp-divider{border:none;border-top:1px solid #F0F0F0;margin:0 0 14px}
.ob-learn-title{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px}
.ob-learn-list{display:flex;flex-direction:column;gap:9px}
.ob-learn-item{display:flex;align-items:center;gap:9px;font-size:12px;font-weight:600;color:#0D0D0D;transition:color .2s}
.ob-learn-item.is-done{color:#22c55e}
.ob-learn-dot{
  width:10px;height:10px;border-radius:50%;flex-shrink:0;
  background:transparent;border:2px solid #D1D5DB;
  transition:background .2s,border-color .2s;
}
.ob-learn-item.is-done .ob-learn-dot{background:#F5C518;border-color:#F5C518}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow:auto;height:auto}
  .ob-page{grid-template-columns:1fr;height:auto;overflow:visible}
  .ob-sidebar{flex-direction:row;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #E5E7EB;background:#fff;position:sticky;top:0;z-index:10}
  .ob-logo{margin-bottom:0;font-size:18px}
  .ob-steps{flex-direction:row;gap:8px;flex:0;align-items:center}
  .ob-step{flex-direction:column;align-items:center;gap:0}
  .ob-step-rail{padding-bottom:0}
  .ob-step:not(:last-child) .ob-step-rail::after{display:none}
  .ob-step-body{display:none}
  .ob-step-num{width:26px;height:26px;font-size:11px}
  .ob-security{display:none}

  .ob-card-area{padding:16px;overflow:visible;height:auto;align-items:flex-start}
  .ob-card{display:flex;flex-direction:column;width:100%;height:auto;max-height:none;box-shadow:0 2px 12px rgba(0,0,0,.08)}

  .ob-hero{display:flex;flex-direction:column;min-height:unset;background:#fff}
  .ob-hero-img{position:static;display:block;width:100%;height:200px;object-fit:cover;object-position:center 30%;order:2}
  .ob-hero-fade{display:none}
  .ob-bubble{display:none}
  .ob-hero-content{position:static;padding:20px;max-width:100%;overflow-y:visible;order:1}
  .ob-topics{grid-template-columns:1fr 1fr;gap:6px}
  .ob-h1{font-size:1.5rem}
  .ob-profile{border-left:none;border-top:1px solid #F0F0F0;padding:20px}
}
@media(max-width:480px){
  .ob-topics{grid-template-columns:1fr}
  .ob-hero-img{height:160px}
  .ob-h1{font-size:1.35rem}
  .ob-card-area{padding:12px}
  .ob-profile{padding:16px}
}
</style>
</head>
<body>

@php
  $saved = $saved ?? [];
  $topics = [
    'business_basics'     => ['Business Basics',      'Your company, products and services', '<path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>'],
    'customers'           => ['Customers',             'Who your customers are and how you work with them', '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
    'renewal_process'     => ['Renewal Process',       'How renewals work in your business', '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>'],
    'communication_style' => ['Communication Style',   'Your tone, preferences and writing style', '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>'],
    'knowledge_resources' => ['Knowledge & Resources', 'Policies, docs and resources Ava can reference', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
    'faq_objections'      => ['FAQ & Objections',      'Common questions and how to respond', '<circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3M12 17h.01"/>'],
  ];
  $coreKeys = ['business_basics','customers','renewal_process','communication_style'];
@endphp

<div class="ob-page">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ob-sidebar">
    <div class="ob-logo">UNIT</div>
    <div class="ob-steps">

      <a href="{{ route('hire.ava.welcome') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail">
          <div class="ob-step-num">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
        </div>
        <div class="ob-step-body">
          <div class="ob-step-label">Meet Ava</div>
          <div class="ob-step-desc">Complete</div>
        </div>
      </a>

      <a href="{{ route('hire.ava.workspace') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail">
          <div class="ob-step-num">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
        </div>
        <div class="ob-step-body">
          <div class="ob-step-label">Workspace</div>
          <div class="ob-step-desc">Complete</div>
        </div>
      </a>

      <div class="ob-step active">
        <div class="ob-step-rail"><div class="ob-step-num">3</div></div>
        <div class="ob-step-body">
          <div class="ob-step-label">Orientation</div>
          <div class="ob-step-desc">Teach Ava your business</div>
        </div>
      </div>

      <div class="ob-step pending">
        <div class="ob-step-rail"><div class="ob-step-num">4</div></div>
        <div class="ob-step-body">
          <div class="ob-step-label">First Assignment</div>
          <div class="ob-step-desc">Give Ava her first job</div>
        </div>
      </div>

      <div class="ob-step pending">
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

  {{-- ══ FLOATING CARD ══ --}}
  <div class="ob-card-area">
    <div class="ob-card">

      {{-- Hero --}}
      <div class="ob-hero">
        <img class="ob-hero-img" src="/images/ava-desk.png" alt="Ava learning">
        <div class="ob-hero-fade"></div>

        <div class="ob-bubble">
          <p>The more I learn today, the better I'll work for you tomorrow.</p>
        </div>

        <form method="POST" action="{{ route('hire.ava.orientation.save') }}" id="orientationForm">
          @csrf
          <div class="ob-hero-content">

            <div class="ob-step-tag">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              Step 3 of 5
            </div>

            <h1 class="ob-h1">
              Let's get Ava<br>
              <span class="ob-gold">up to speed.</span>
            </h1>

            <p class="ob-sub">The more Ava learns about your business, the better she'll protect your renewals and represent you.</p>

            <div class="ob-topics" id="topicsGrid">
              @foreach($topics as $key => [$title, $desc, $iconPath])
              @php $val = $saved[$key] ?? '' @endphp
              <div class="ob-topic {{ $val ? 'is-filled' : '' }}" data-key="{{ $key }}">
                <div class="ob-topic-header">
                  <div class="ob-topic-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">{!! $iconPath !!}</svg>
                  </div>
                  <div class="ob-topic-text">
                    <div class="ob-topic-title">{{ $title }}</div>
                    <div class="ob-topic-desc">{{ $desc }}</div>
                  </div>
                  <div class="ob-topic-check">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </div>
                </div>
                <div class="ob-topic-expand">
                  <div class="ob-topic-expand-inner">
                    <textarea
                      class="ob-topic-textarea"
                      name="{{ $key }}"
                      placeholder="Tell Ava about {{ strtolower($title) }}…"
                    >{{ $val }}</textarea>
                  </div>
                </div>
              </div>
              @endforeach
            </div>

            <p class="ob-footer-note">You can update or add more anytime.</p>

            <button type="submit" class="btn-continue" id="continueBtn">
              Continue — Save &amp; Next
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>

          </div>
        </form>
      </div>

      {{-- Right panel: live progress ── --}}
      <div class="ob-profile">
        <div class="emp-eyebrow">AVA's Learning Progress</div>

        <div class="ob-progress-wrap">
          <div class="ob-progress-ring">
            <svg viewBox="0 0 100 100">
              <circle class="ob-progress-bg" cx="50" cy="50" r="40"/>
              <circle class="ob-progress-fill" cx="50" cy="50" r="40" id="progressRing"/>
            </svg>
            <div class="ob-progress-label">
              <div class="ob-progress-pct" id="progressPct">0%</div>
              <div class="ob-progress-sub">Knowledge</div>
            </div>
          </div>
          <p class="ob-progress-caption" id="progressCaption">Fill in a topic to get started.</p>
        </div>

        <hr class="emp-divider">

        <div class="ob-learn-title">What Ava is learning</div>
        <div class="ob-learn-list">
          @foreach($topics as $key => [$title, $desc, $iconPath])
          @php $val = $saved[$key] ?? '' @endphp
          <div class="ob-learn-item {{ $val ? 'is-done' : '' }}" id="learn-{{ $key }}">
            <span class="ob-learn-dot"></span>{{ $title }}
          </div>
          @endforeach
        </div>

      </div>

    </div>
  </div>

</div>

<script>
(function(){
  const CIRC = 2 * Math.PI * 40; // ≈ 251.3
  const topics    = @json(array_keys($topics));
  const coreKeys  = @json($coreKeys);
  const ring      = document.getElementById('progressRing');
  const pctEl     = document.getElementById('progressPct');
  const capEl     = document.getElementById('progressCaption');
  const btn       = document.getElementById('continueBtn');

  ring.style.strokeDasharray  = CIRC;
  ring.style.strokeDashoffset = CIRC;

  function getValues(){
    const vals = {};
    topics.forEach(k => {
      const ta = document.querySelector(`[data-key="${k}"] textarea`);
      vals[k] = ta ? ta.value.trim() : '';
    });
    return vals;
  }

  function update(){
    const vals    = getValues();
    const filled  = topics.filter(k => vals[k].length > 0);
    const coreDone = coreKeys.every(k => vals[k].length > 0);
    const pct     = Math.round((filled.length / topics.length) * 100);

    // Ring
    ring.style.strokeDashoffset = CIRC - (CIRC * pct / 100);
    pctEl.textContent = pct + '%';

    // Caption
    if(pct === 0)       capEl.textContent = 'Fill in a topic to get started.';
    else if(pct < 50)   capEl.textContent = 'Good start! Keep going.';
    else if(pct < 84)   capEl.textContent = 'Keep going! You\'re doing great.';
    else if(pct < 100)  capEl.textContent = 'Almost there!';
    else                capEl.textContent = 'Ava is fully briefed. 🎉';

    // Learn list
    topics.forEach(k => {
      const el = document.getElementById('learn-' + k);
      if(el) el.classList.toggle('is-done', vals[k].length > 0);
    });

    // Cards: is-filled state
    topics.forEach(k => {
      const card = document.querySelector(`[data-key="${k}"]`);
      if(card) card.classList.toggle('is-filled', vals[k].length > 0);
    });

    // Continue button — active when all 4 core topics filled
    btn.classList.toggle('is-active', coreDone);
  }

  // Click card header to open/close
  document.querySelectorAll('.ob-topic').forEach(card => {
    card.querySelector('.ob-topic-header').addEventListener('click', function(){
      const isOpen = card.classList.contains('is-open');
      // Close all
      document.querySelectorAll('.ob-topic').forEach(c => c.classList.remove('is-open'));
      if(!isOpen){
        card.classList.add('is-open');
        const ta = card.querySelector('textarea');
        if(ta) setTimeout(() => ta.focus(), 30);
      }
    });

    // Live update on textarea change
    const ta = card.querySelector('textarea');
    if(ta) ta.addEventListener('input', update);
  });

  // Run once on load to reflect saved session data
  update();

  // Auto-open first unfilled card
  const firstEmpty = topics.find(k => {
    const ta = document.querySelector(`[data-key="${k}"] textarea`);
    return ta && ta.value.trim() === '';
  });
  if(firstEmpty){
    const firstCard = document.querySelector(`[data-key="${firstEmpty}"]`);
    if(firstCard) firstCard.classList.add('is-open');
  }
})();
</script>

<x-self-learn page="hire.ava.orientation" />
</body>
</html>

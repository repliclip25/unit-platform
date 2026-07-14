<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Meet Ava — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{font-family:'Inter',sans-serif;background:#F4F3F1;color:#0D0D0D;-webkit-font-smoothing:antialiased}

/* ── PAGE: sidebar | card area ── */
.ob-page{
  display:grid;
  grid-template-columns:260px 1fr;
  height:100vh;
  overflow:hidden;
}

/* ════════════════════════════
   SIDEBAR — permanent page chrome
════════════════════════════ */
.ob-sidebar{
  background:#F4F3F1;
  display:flex;flex-direction:column;
  padding:32px 24px;
  overflow-y:auto;
}

/* Logo — text only */
.ob-logo{
  font-size:21px;font-weight:900;letter-spacing:-.04em;
  color:#0D0D0D;margin-bottom:44px;
}

/* Steps with connector line */
.ob-steps{display:flex;flex-direction:column;flex:1}
.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative}

/* Vertical line: runs from the bottom of one circle to the top of the next */
.ob-step:not(:last-child) .ob-step-rail::after{
  content:'';
  position:absolute;
  left:13px;top:30px;
  width:2px;height:calc(100% - 6px);
  background:#DCDCDC;
  border-radius:2px;
}
.ob-step.done:not(:last-child) .ob-step-rail::after{background:#0D0D0D}

.ob-step-rail{
  position:relative;flex-shrink:0;
  display:flex;flex-direction:column;align-items:center;
  padding-bottom:32px;
}
.ob-step:last-child .ob-step-rail{padding-bottom:0}

.ob-step-num{
  width:28px;height:28px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:800;
  position:relative;z-index:1;flex-shrink:0;
}
.ob-step.pending .ob-step-num{background:#E8E7E4;color:#ADADAD;border:1.5px solid #DCDCDC}
.ob-step.active  .ob-step-num{background:#0D0D0D;color:#fff;box-shadow:0 0 0 4px rgba(0,0,0,.1)}
.ob-step.done    .ob-step-num{background:#0D0D0D;color:#fff}

.ob-step-body{padding-top:4px;padding-bottom:28px}
.ob-step:last-child .ob-step-body{padding-bottom:0}

.ob-step-label{font-size:13.5px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-step.pending .ob-step-label{color:#ADADAD}
.ob-step-desc{font-size:12px;color:#9CA3AF;margin-top:3px;line-height:1.4}
.ob-step.active .ob-step-desc{color:#6B7280}

/* Active step body gets a subtle card */
.ob-step.active .ob-step-body{
  background:#fff;border:1.5px solid #E5E7EB;
  border-radius:12px;padding:10px 14px;
  margin-right:-4px;
}

/* Security note */
.ob-security{margin-top:8px;padding:14px 16px;border-radius:12px;background:#ECEAE6;border:1px solid #DCDCDC}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:5px}
.ob-security-row svg{width:13px;height:13px;stroke:#6B7280;flex-shrink:0}
.ob-security-title{font-size:12px;font-weight:700;color:#374151}
.ob-security p{font-size:11px;color:#9CA3AF;line-height:1.55}

/* ════════════════════════════
   CARD AREA — the big floating card
════════════════════════════ */
.ob-card-area{
  display:flex;
  align-items:center;
  justify-content:center;
  padding:24px 28px 24px 16px;
  overflow:hidden;
}

/* The one floating card: hero left + profile right */
.ob-card{
  display:grid;
  grid-template-columns:1fr 300px;
  width:100%;
  height:100%;
  max-height:calc(100vh - 48px);
  border-radius:24px;
  overflow:hidden;
  box-shadow:0 12px 48px rgba(0,0,0,.13),0 2px 8px rgba(0,0,0,.06);
  border:1px solid rgba(0,0,0,.06);
}

/* ── Left: hero with AVA image ── */
.ob-hero{
  position:relative;
  overflow:hidden;
  background:#ebe8e2;
}
.ob-hero-img{
  position:absolute;inset:0;
  width:100%;height:100%;
  object-fit:cover;object-position:center top;
}
.ob-hero-fade{
  position:absolute;inset:0;z-index:1;pointer-events:none;
  background:linear-gradient(
    to right,
    #fff 0%,#fff 20%,
    rgba(255,255,255,.9) 34%,
    rgba(255,255,255,.45) 52%,
    transparent 70%
  );
}
.ob-hero-content{
  position:relative;z-index:2;
  padding:44px 36px;
  max-width:420px;
  height:100%;
  display:flex;flex-direction:column;justify-content:center;
}

.ob-badge{
  display:inline-flex;align-items:center;gap:7px;
  padding:5px 12px;border-radius:99px;
  background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.22);
  font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:#16a34a;margin-bottom:22px;width:fit-content;
}
.ob-badge-dot{width:7px;height:7px;border-radius:50%;background:#22c55e;flex-shrink:0;animation:pdot 1.6s ease infinite}
@keyframes pdot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.7)}}

.ob-h1{
  font-size:clamp(1.9rem,2.5vw,2.5rem);
  font-weight:900;letter-spacing:-.04em;line-height:1.06;
  color:#0D0D0D;margin-bottom:16px;
}
.ob-gold{color:#F5C518;position:relative;display:inline}
.ob-gold::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}

.ob-sub{font-size:14px;color:#374151;line-height:1.72;margin-bottom:24px}

.ob-bubble{
  background:#fff;border:1px solid #E5E7EB;
  border-radius:16px;border-bottom-left-radius:4px;
  padding:16px 18px;margin-bottom:24px;max-width:310px;
  box-shadow:0 2px 12px rgba(0,0,0,.05);
}
.ob-bubble-icon{width:26px;height:26px;border-radius:50%;background:#F5C518;display:flex;align-items:center;justify-content:center;margin-bottom:9px}
.ob-bubble-icon svg{width:13px;height:13px;stroke:#0D0D0D;stroke-width:2.5}
.ob-bubble p{font-size:13px;color:#374151;line-height:1.65}
.ob-bubble p+p{margin-top:5px}

.ob-proof{display:flex;align-items:center;gap:10px}
.ob-proof-avs{display:flex}
.ob-proof-avs img{width:28px;height:28px;border-radius:50%;border:2px solid #fff;margin-left:-7px;object-fit:cover;box-shadow:0 1px 4px rgba(0,0,0,.12)}
.ob-proof-avs img:first-child{margin-left:0}
.ob-proof-txt{font-size:12px;color:#6B7280;line-height:1.45}
.ob-proof-txt strong{color:#0D0D0D;display:block}

/* ── Right: employee profile panel ── */
.ob-profile{
  background:#fff;
  border-left:1px solid #F0F0F0;
  padding:28px 22px;
  display:flex;flex-direction:column;
  overflow-y:auto;
}

.emp-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px}
.emp-name{font-size:1.65rem;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;line-height:1}
.emp-role{font-size:12.5px;color:#6B7280;margin-top:4px;margin-bottom:18px}

.emp-divider{border:none;border-top:1px solid #F3F4F6;margin:0 0 14px}

.emp-row{display:flex;align-items:center;justify-content:space-between;padding:6.5px 0;border-bottom:1px solid #F9FAFB}
.emp-row:last-child{border-bottom:none}
.emp-row-key{display:flex;align-items:center;gap:6px;font-size:11.5px;color:#9CA3AF}
.emp-row-key svg{width:12px;height:12px;stroke:#D1D5DB;stroke-width:1.8;flex-shrink:0}
.emp-row-val{font-size:11.5px;font-weight:700;color:#0D0D0D}

.emp-what-title{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin:16px 0 10px}
.emp-what-list{display:flex;flex-direction:column;gap:7px;margin-bottom:22px}
.emp-what-item{display:flex;align-items:center;gap:8px;font-size:12px;color:#374151}
.emp-what-dot{
  width:17px;height:17px;border-radius:50%;flex-shrink:0;
  background:rgba(245,197,24,.12);border:1px solid rgba(245,197,24,.3);
  display:flex;align-items:center;justify-content:center;
}
.emp-what-dot svg{width:9px;height:9px;stroke:#C8960A;stroke-width:3}

.btn-hire{
  display:flex;align-items:center;justify-content:space-between;
  width:100%;padding:15px 18px;border-radius:13px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  font-size:14.5px;font-weight:800;letter-spacing:-.01em;
  transition:opacity .15s,transform .1s;
  margin-top:auto;
}
.btn-hire:hover{opacity:.88;transform:translateY(-1px)}
.btn-hire svg{width:17px;height:17px;stroke:#fff;stroke-width:2.5}

.emp-setup-note{
  text-align:center;font-size:11px;color:#9CA3AF;
  margin-top:8px;display:flex;align-items:center;justify-content:center;gap:5px;
}
.emp-setup-note svg{width:11px;height:11px;stroke:#C4C4C4;stroke-width:2}
</style>
</head>
<body>

@php
  $firstName = explode(' ', auth()->user()->name ?? 'there')[0];
  $whatIDo = $intentMeta['what_i_do'] ?? [
    'Monitor your Gmail 24/7',
    'Detect renewal and subscription requests',
    'Understand the customer using your memory',
    'Draft a personalized response',
    'Save it to Gmail Drafts for your review',
    'Learn from every interaction',
  ];
@endphp

<div class="ob-page">

  {{-- ══ SIDEBAR — page chrome only ══ --}}
  <aside class="ob-sidebar">

    <div class="ob-logo">UNIT</div>

    <div class="ob-steps">

      <div class="ob-step active">
        <div class="ob-step-rail"><div class="ob-step-num">1</div></div>
        <div class="ob-step-body">
          <div class="ob-step-label">Meet Ava</div>
          <div class="ob-step-desc">Introduce your worker</div>
        </div>
      </div>

      <div class="ob-step pending">
        <div class="ob-step-rail"><div class="ob-step-num">2</div></div>
        <div class="ob-step-body">
          <div class="ob-step-label">Workspace</div>
          <div class="ob-step-desc">Prepare Ava's desk</div>
        </div>
      </div>

      <div class="ob-step pending">
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
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>We never store your password.<br>You're always in control.</p>
    </div>

  </aside>

  {{-- ══ THE FLOATING CARD (hero + profile) ══ --}}
  <div class="ob-card-area">
    <div class="ob-card">

      {{-- Hero left --}}
      <div class="ob-hero">
        <img class="ob-hero-img" src="/images/ava-stand.png" alt="AVA">
        <div class="ob-hero-fade"></div>
        <div class="ob-hero-content">

          <div class="ob-badge">
            <span class="ob-badge-dot"></span>
            Available for hire
          </div>

          <h1 class="ob-h1">
            {{ $firstName }},<br>
            meet your newest<br>
            <span class="ob-gold">teammate.</span>
          </h1>

          <p class="ob-sub">
            Ava is your Renewal Specialist.<br>
            She works 24/7 to protect your revenue<br>
            and make sure no renewal slips through the cracks.
          </p>

          <div class="ob-bubble">
            <div class="ob-bubble-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <p>Hi {{ $firstName }},</p>
            <p>I'm excited to join your team.</p>
            <p>I'll quietly watch your inbox so you never miss another renewal.</p>
          </div>

          <div class="ob-proof">
            <div class="ob-proof-avs">
              <img src="/images/ava.png" alt="">
              <img src="/images/ava-stand.png" alt="">
              <img src="/images/ava-life.png" alt="">
              <img src="/images/ava.png" alt="" style="filter:hue-rotate(40deg) saturate(.8)">
            </div>
            <div class="ob-proof-txt">
              <strong>2,847+ businesses</strong>
              trust UNIT workers
            </div>
          </div>

        </div>
      </div>

      {{-- Profile right --}}
      <div class="ob-profile">

        <div class="emp-eyebrow">Employee Profile</div>
        <div class="emp-name">AVA</div>
        <div class="emp-role">Renewal Specialist</div>

        <hr class="emp-divider">

        <div class="emp-row">
          <span class="emp-row-key"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>Employee ID</span>
          <span class="emp-row-val">AVA-001</span>
        </div>
        <div class="emp-row">
          <span class="emp-row-key"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path stroke-linecap="round" d="M9 22V12h6v10"/></svg>Department</span>
          <span class="emp-row-val">Customer Success</span>
        </div>
        <div class="emp-row">
          <span class="emp-row-key"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Reports To</span>
          <span class="emp-row-val">You</span>
        </div>
        <div class="emp-row">
          <span class="emp-row-key"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>Shift</span>
          <span class="emp-row-val">24 / 7</span>
        </div>
        <div class="emp-row">
          <span class="emp-row-key"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>Start Date</span>
          <span class="emp-row-val">Today</span>
        </div>
        <div class="emp-row">
          <span class="emp-row-key"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>Employment Type</span>
          <span class="emp-row-val">Digital Worker</span>
        </div>

        <div class="emp-what-title">What Ava will do for you</div>
        <div class="emp-what-list">
          @foreach($whatIDo as $item)
          <div class="emp-what-item">
            <div class="emp-what-dot">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            {{ $item }}
          </div>
          @endforeach
        </div>

        <form method="POST" action="{{ route('onboarding.step.handle', 'welcome') }}">
          @csrf
          <button type="submit" class="btn-hire">
            Hire Ava
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
        </form>

        <div class="emp-setup-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          Estimated setup: Less than 2 minutes
        </div>

      </div>

    </div>
  </div>

</div>

</body>
</html>

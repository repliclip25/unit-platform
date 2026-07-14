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
body{font-family:'Inter',sans-serif;background:#fff;color:#0D0D0D;-webkit-font-smoothing:antialiased}

/* ── SHELL: sidebar | hero | card ── */
.ob-shell{
  display:grid;
  grid-template-columns:220px 1fr 320px;
  height:100vh;
  overflow:hidden;
}

/* ════════════════════════════════
   LEFT SIDEBAR
════════════════════════════════ */
.ob-sidebar{
  background:#fff;
  border-right:1px solid #E5E7EB;
  display:flex;flex-direction:column;
  padding:28px 20px;
  overflow-y:auto;
  z-index:2;
}
.ob-logo{display:flex;align-items:center;gap:9px;margin-bottom:36px}
.ob-logo-mark{width:34px;height:34px;flex-shrink:0}
.ob-logo-name{font-size:15px;font-weight:900;letter-spacing:-.02em;color:#0D0D0D;line-height:1}
.ob-logo-sub{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-top:2px}

.ob-steps{display:flex;flex-direction:column;gap:2px;flex:1}
.ob-step{display:flex;align-items:flex-start;gap:12px;padding:10px 12px;border-radius:12px}
.ob-step.active{background:#F8F8F6}
.ob-step-num{
  width:28px;height:28px;border-radius:50%;flex-shrink:0;margin-top:1px;
  display:flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:800;
}
.ob-step.pending .ob-step-num{background:#F3F4F6;color:#C4C4C4}
.ob-step.active  .ob-step-num{background:#0D0D0D;color:#fff}
.ob-step.done    .ob-step-num{background:#0D0D0D;color:#fff}
.ob-step-label{font-size:13px;font-weight:700;line-height:1.2;color:#0D0D0D}
.ob-step.pending .ob-step-label{color:#C4C4C4}
.ob-step-desc{font-size:11.5px;color:#9CA3AF;margin-top:2px;line-height:1.4}
.ob-step.active .ob-step-desc{color:#6B7280}

.ob-security{
  margin-top:24px;padding:14px;border-radius:12px;
  background:#F8F8F6;border:1px solid #E5E7EB;
}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:5px}
.ob-security-row svg{width:13px;height:13px;stroke:#6B7280;flex-shrink:0}
.ob-security-title{font-size:11.5px;font-weight:700;color:#374151}
.ob-security p{font-size:11px;color:#9CA3AF;line-height:1.55}

/* ════════════════════════════════
   CENTER HERO — AVA fills this entirely
   text overlaid left, image bleeds right
════════════════════════════════ */
.ob-hero{
  position:relative;
  overflow:hidden;
  background:#f5f4f2; /* neutral base shown before image loads */
}

/* AVA full-bleed background image */
.ob-hero-img{
  position:absolute;
  inset:0;
  width:100%;height:100%;
  object-fit:cover;
  object-position:center top;
  display:block;
}

/* White gradient: text readable on left, image shows right */
.ob-hero-fade{
  position:absolute;
  inset:0;
  background:linear-gradient(
    to right,
    #fff 0%,
    #fff 28%,
    rgba(255,255,255,.92) 40%,
    rgba(255,255,255,.5) 58%,
    transparent 75%
  );
  z-index:1;
  pointer-events:none;
}

/* Text content sits above the gradient */
.ob-hero-content{
  position:relative;
  z-index:2;
  padding:44px 36px;
  max-width:420px;
  height:100%;
  display:flex;
  flex-direction:column;
  justify-content:center;
}

/* Available badge */
.ob-badge{
  display:inline-flex;align-items:center;gap:7px;
  padding:5px 12px;border-radius:99px;
  background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.22);
  font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:#16a34a;margin-bottom:22px;width:fit-content;
}
.ob-badge-dot{
  width:7px;height:7px;border-radius:50%;background:#22c55e;
  animation:pulse-dot 1.6s ease infinite;flex-shrink:0;
}
@keyframes pulse-dot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.55;transform:scale(.75)}}

/* Headline */
.ob-h1{
  font-size:clamp(1.9rem,2.6vw,2.5rem);
  font-weight:900;letter-spacing:-.035em;line-height:1.07;
  color:#0D0D0D;margin-bottom:14px;
}
.ob-gold{
  color:#F5C518;
  position:relative;display:inline;
}
.ob-gold::after{
  content:"";position:absolute;
  left:0;right:0;bottom:-3px;
  height:4px;background:#F5C518;border-radius:2px;
}

/* Sub */
.ob-sub{font-size:14px;color:#374151;line-height:1.72;margin-bottom:22px}

/* AVA message bubble */
.ob-bubble{
  background:#fff;border:1px solid #E5E7EB;
  border-radius:16px;border-bottom-left-radius:4px;
  padding:16px 18px;margin-bottom:22px;max-width:320px;
  box-shadow:0 2px 12px rgba(0,0,0,.06);
}
.ob-bubble-icon{
  width:26px;height:26px;border-radius:50%;background:#F5C518;
  display:flex;align-items:center;justify-content:center;margin-bottom:8px;
}
.ob-bubble-icon svg{width:13px;height:13px;stroke:#0D0D0D;stroke-width:2.5}
.ob-bubble p{font-size:13px;color:#374151;line-height:1.65}
.ob-bubble p+p{margin-top:5px}

/* Social proof */
.ob-proof{display:flex;align-items:center;gap:10px}
.ob-proof-avs{display:flex}
.ob-proof-avs img{
  width:28px;height:28px;border-radius:50%;
  border:2px solid #fff;margin-left:-6px;
  object-fit:cover;box-shadow:0 1px 4px rgba(0,0,0,.12);
}
.ob-proof-avs img:first-child{margin-left:0}
.ob-proof-txt{font-size:12px;color:#6B7280;line-height:1.45}
.ob-proof-txt strong{color:#0D0D0D;display:block}

/* ════════════════════════════════
   RIGHT PANEL — Employee Profile Card
════════════════════════════════ */
.ob-card{
  background:#fff;
  border-left:1px solid #E5E7EB;
  overflow-y:auto;
  padding:28px 24px 24px;
  display:flex;flex-direction:column;
}

.emp-eyebrow{
  font-size:9.5px;font-weight:700;letter-spacing:.14em;
  text-transform:uppercase;color:#9CA3AF;margin-bottom:12px;
}
.emp-header{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px}
.emp-name{font-size:1.7rem;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;line-height:1}
.emp-role{font-size:13px;color:#6B7280;margin-top:4px}
.emp-badge-card{
  width:54px;height:62px;border-radius:10px;
  background:#0D0D0D;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  flex-shrink:0;gap:2px;
}
.emp-badge-letter{font-size:22px;font-weight:900;color:#F5C518;line-height:1}
.emp-badge-status{font-size:7px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#22c55e}

.emp-divider{border:none;border-top:1px solid #E5E7EB;margin:0 0 16px}

.emp-row{
  display:flex;align-items:center;justify-content:space-between;
  padding:7px 0;border-bottom:1px solid #F3F4F6;
}
.emp-row:last-child{border-bottom:none}
.emp-row-key{display:flex;align-items:center;gap:7px;font-size:12px;color:#9CA3AF}
.emp-row-key svg{width:13px;height:13px;stroke:#C4C4C4;stroke-width:1.8;flex-shrink:0}
.emp-row-val{font-size:12px;font-weight:600;color:#0D0D0D}

.emp-what-title{
  font-size:9.5px;font-weight:700;letter-spacing:.14em;
  text-transform:uppercase;color:#9CA3AF;
  margin:18px 0 10px;
}
.emp-what-list{display:flex;flex-direction:column;gap:8px;margin-bottom:22px}
.emp-what-item{display:flex;align-items:center;gap:9px;font-size:12.5px;color:#374151}
.emp-what-dot{
  width:18px;height:18px;border-radius:50%;flex-shrink:0;
  background:rgba(245,197,24,.12);border:1px solid rgba(245,197,24,.3);
  display:flex;align-items:center;justify-content:center;
}
.emp-what-dot svg{width:10px;height:10px;stroke:#D4A800;stroke-width:3}

.btn-hire{
  display:flex;align-items:center;justify-content:space-between;
  width:100%;padding:16px 20px;border-radius:14px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  font-size:15px;font-weight:800;letter-spacing:-.01em;
  transition:opacity .15s,transform .1s;
  margin-top:auto;
}
.btn-hire:hover{opacity:.88;transform:translateY(-1px)}
.btn-hire svg{width:18px;height:18px;stroke:#fff;stroke-width:2.5}

.emp-setup-note{
  text-align:center;font-size:11px;color:#9CA3AF;
  margin-top:9px;display:flex;align-items:center;justify-content:center;gap:5px;
}
.emp-setup-note svg{width:12px;height:12px;stroke:#C4C4C4;stroke-width:2}
</style>
</head>
<body>

@php
  $firstName = explode(' ', auth()->user()->name ?? 'there')[0];
  $whatIDo = $intentMeta['what_i_do'] ?? [
    'Watches Gmail continuously',
    'Detects renewals automatically',
    'Drafts personalized replies',
    'Learns every customer interaction',
    'Never misses deadlines',
  ];
@endphp

<div class="ob-shell">

  {{-- ══ LEFT SIDEBAR ══ --}}
  <aside class="ob-sidebar">

    <div class="ob-logo">
      <svg class="ob-logo-mark" viewBox="0 0 34 34" fill="none">
        <rect width="34" height="34" rx="8" fill="#0D0D0D"/>
        <path d="M17 7 L20.5 14.5 L29 15.5 L23 21 L24.5 29.5 L17 25.5 L9.5 29.5 L11 21 L5 15.5 L13.5 14.5 Z" fill="#F5C518"/>
      </svg>
      <div>
        <div class="ob-logo-name">UNIT</div>
        <div class="ob-logo-sub">AI Workers</div>
      </div>
    </div>

    <div class="ob-steps">

      <div class="ob-step active">
        <div class="ob-step-num">1</div>
        <div>
          <div class="ob-step-label">Meet Ava</div>
          <div class="ob-step-desc">Introduce your worker</div>
        </div>
      </div>

      <div class="ob-step pending">
        <div class="ob-step-num">2</div>
        <div>
          <div class="ob-step-label">Workspace</div>
          <div class="ob-step-desc">Prepare Ava's desk</div>
        </div>
      </div>

      <div class="ob-step pending">
        <div class="ob-step-num">3</div>
        <div>
          <div class="ob-step-label">Orientation</div>
          <div class="ob-step-desc">Teach Ava your business</div>
        </div>
      </div>

      <div class="ob-step pending">
        <div class="ob-step-num">4</div>
        <div>
          <div class="ob-step-label">First Assignment</div>
          <div class="ob-step-desc">Give Ava her first job</div>
        </div>
      </div>

      <div class="ob-step pending">
        <div class="ob-step-num">5</div>
        <div>
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

  {{-- ══ CENTER HERO ══ --}}
  <section class="ob-hero">

    {{-- AVA full-bleed image --}}
    <img class="ob-hero-img" src="/images/ava-stand.png" alt="AVA">

    {{-- Left-to-right white fade —text reads over the white zone --}}
    <div class="ob-hero-fade"></div>

    {{-- Text content --}}
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

  </section>

  {{-- ══ RIGHT CARD ══ --}}
  <aside class="ob-card">

    <div class="emp-eyebrow">Employee Profile</div>

    <div class="emp-header">
      <div>
        <div class="emp-name">AVA</div>
        <div class="emp-role">Renewal Specialist</div>
      </div>
      <div class="emp-badge-card">
        <div class="emp-badge-letter">A</div>
        <div class="emp-badge-status">Available</div>
      </div>
    </div>

    <hr class="emp-divider">

    <div class="emp-row">
      <span class="emp-row-key">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Employee ID
      </span>
      <span class="emp-row-val">AVA-001</span>
    </div>
    <div class="emp-row">
      <span class="emp-row-key">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path stroke-linecap="round" d="M9 22V12h6v10"/></svg>
        Department
      </span>
      <span class="emp-row-val">Customer Success</span>
    </div>
    <div class="emp-row">
      <span class="emp-row-key">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Reports To
      </span>
      <span class="emp-row-val">You</span>
    </div>
    <div class="emp-row">
      <span class="emp-row-key">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        Shift
      </span>
      <span class="emp-row-val">24 / 7</span>
    </div>
    <div class="emp-row">
      <span class="emp-row-key">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Start Date
      </span>
      <span class="emp-row-val">Today</span>
    </div>
    <div class="emp-row">
      <span class="emp-row-key">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
        Employment Type
      </span>
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

  </aside>

</div>

</body>
</html>

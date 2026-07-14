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
<link rel="stylesheet" href="/css/unit-public.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{font-family:'Inter',sans-serif;background:#fff;color:#0D0D0D;-webkit-font-smoothing:antialiased}

/* ── SHELL ── */
.ob-shell{display:grid;grid-template-columns:220px 1fr 320px;height:100vh;overflow:hidden}

/* ── LEFT SIDEBAR ── */
.ob-sidebar{
  background:#fff;
  border-right:1px solid #E5E7EB;
  display:flex;flex-direction:column;
  padding:28px 20px;
  overflow-y:auto;
}
.ob-logo{display:flex;align-items:center;gap:9px;margin-bottom:36px}
.ob-logo-mark{width:36px;height:36px;flex-shrink:0}
.ob-logo-text{}
.ob-logo-name{font-size:15px;font-weight:900;letter-spacing:-.02em;color:#0D0D0D;line-height:1}
.ob-logo-sub{font-size:9.5px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-top:1px}

/* Steps */
.ob-steps{display:flex;flex-direction:column;gap:2px;flex:1}
.ob-step{
  display:flex;align-items:flex-start;gap:12px;
  padding:10px 12px;border-radius:12px;
  transition:background .15s;
  cursor:default;
}
.ob-step.active{background:#F8F8F6}
.ob-step-num{
  width:28px;height:28px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:800;flex-shrink:0;margin-top:1px;
  transition:all .2s;
}
.ob-step.pending .ob-step-num{background:#F3F4F6;color:#9CA3AF}
.ob-step.active .ob-step-num{background:#0D0D0D;color:#fff}
.ob-step.done .ob-step-num{background:#0D0D0D;color:#fff}
.ob-step-check{width:28px;height:28px;border-radius:50%;background:#0D0D0D;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
.ob-step-check svg{width:13px;height:13px;stroke:#fff;stroke-width:3}
.ob-step-body{}
.ob-step-label{font-size:13px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-step.pending .ob-step-label{color:#9CA3AF}
.ob-step-desc{font-size:11.5px;color:#9CA3AF;margin-top:2px;line-height:1.4}
.ob-step.active .ob-step-desc{color:#6B7280}

/* Security note */
.ob-security{
  margin-top:24px;padding:14px;border-radius:12px;
  background:#F8F8F6;border:1px solid #E5E7EB;
}
.ob-security-top{display:flex;align-items:center;gap:8px;margin-bottom:6px}
.ob-security-top svg{width:14px;height:14px;stroke:#6B7280;flex-shrink:0}
.ob-security-title{font-size:11.5px;font-weight:700;color:#374151}
.ob-security p{font-size:11px;color:#9CA3AF;line-height:1.5}

/* ── CENTER ── */
.ob-center{
  display:flex;flex-direction:column;
  overflow:hidden;position:relative;
  background:#fff;
}
.ob-center-content{
  padding:44px 40px 32px;
  z-index:2;position:relative;
  max-width:440px;
}

/* Available badge */
.ob-badge{
  display:inline-flex;align-items:center;gap:7px;
  padding:5px 12px;border-radius:99px;
  background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);
  font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:#16a34a;margin-bottom:22px;
}
.ob-badge-dot{width:7px;height:7px;border-radius:50%;background:#22c55e;animation:pulse-dot 1.6s ease infinite}
@keyframes pulse-dot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(.8)}}

/* Headline */
.ob-h1{font-size:clamp(1.8rem,2.8vw,2.4rem);font-weight:900;letter-spacing:-.03em;line-height:1.08;margin-bottom:14px;color:#0D0D0D}
.ob-h1 .gold{color:#F5C518;position:relative;display:inline}
.ob-h1 .gold::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}

/* Sub */
.ob-sub{font-size:14.5px;color:#374151;line-height:1.7;margin-bottom:22px;max-width:360px}

/* AVA message bubble */
.ob-bubble{
  background:#F8F8F6;border:1px solid #E5E7EB;border-radius:16px;border-bottom-left-radius:4px;
  padding:14px 18px;margin-bottom:22px;max-width:340px;
  position:relative;
}
.ob-bubble-icon{
  width:28px;height:28px;border-radius:50%;background:#F5C518;
  display:flex;align-items:center;justify-content:center;
  margin-bottom:8px;flex-shrink:0;
}
.ob-bubble-icon svg{width:14px;height:14px;stroke:#0D0D0D;stroke-width:2.5}
.ob-bubble p{font-size:13px;color:#374151;line-height:1.65}
.ob-bubble p+p{margin-top:6px}

/* Social proof */
.ob-proof{display:flex;align-items:center;gap:10px}
.ob-proof-avs{display:flex}
.ob-proof-avs img{width:28px;height:28px;border-radius:50%;border:2px solid #fff;margin-left:-6px;object-fit:cover;box-shadow:0 1px 4px rgba(0,0,0,.1)}
.ob-proof-avs img:first-child{margin-left:0}
.ob-proof-txt{font-size:12px;color:#6B7280}
.ob-proof-txt strong{color:#0D0D0D}

/* AVA character — fills right half of center */
.ob-ava-art{
  position:absolute;right:-20px;bottom:0;top:0;
  width:58%;
  pointer-events:none;z-index:1;
}
.ob-ava-art img{
  width:100%;height:100%;
  object-fit:contain;object-position:bottom right;
}
/* fade left edge of image */
.ob-ava-art::before{
  content:'';position:absolute;left:0;top:0;bottom:0;width:55%;z-index:2;
  background:linear-gradient(to right,#fff 30%,transparent 100%);
}

/* ── RIGHT PANEL ── */
.ob-right{
  background:#fff;
  border-left:1px solid #E5E7EB;
  overflow-y:auto;
  padding:28px 24px;
  display:flex;flex-direction:column;gap:0;
}

/* Employee profile card */
.emp-eyebrow{font-size:9.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#9CA3AF;margin-bottom:12px}
.emp-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px}
.emp-name{font-size:1.6rem;font-weight:900;letter-spacing:-.03em;color:#0D0D0D;line-height:1}
.emp-title{font-size:13px;color:#6B7280;margin-top:4px}
.emp-badge{
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  width:56px;height:64px;border-radius:10px;
  background:#0D0D0D;
  flex-shrink:0;
}
.emp-badge-letter{font-size:22px;font-weight:900;color:#F5C518;line-height:1}
.emp-badge-status{font-size:7px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#22c55e;margin-top:3px}

/* Details grid */
.emp-grid{display:flex;flex-direction:column;gap:0;margin-bottom:20px;border-top:1px solid #E5E7EB;padding-top:16px}
.emp-row{display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F3F4F6}
.emp-row:last-child{border-bottom:none}
.emp-row-label{display:flex;align-items:center;gap:7px;font-size:12px;color:#9CA3AF}
.emp-row-label svg{width:14px;height:14px;stroke:#9CA3AF;stroke-width:1.8;flex-shrink:0}
.emp-row-val{font-size:12px;font-weight:600;color:#0D0D0D}

/* What Ava does */
.emp-what-label{font-size:9.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px}
.emp-what-list{display:flex;flex-direction:column;gap:7px;margin-bottom:22px}
.emp-what-item{display:flex;align-items:center;gap:9px;font-size:12.5px;color:#374151}
.emp-what-check{width:18px;height:18px;border-radius:50%;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.emp-what-check svg{width:10px;height:10px;stroke:#22c55e;stroke-width:3}

/* Hire button */
.btn-hire{
  display:flex;align-items:center;justify-content:space-between;
  width:100%;padding:16px 20px;border-radius:14px;
  background:#0D0D0D;color:#fff;
  font-size:15px;font-weight:800;letter-spacing:-.01em;
  transition:opacity .15s,transform .1s;
  border:none;cursor:pointer;
}
.btn-hire:hover{opacity:.9;transform:translateY(-1px)}
.btn-hire svg{width:18px;height:18px;stroke:#fff;stroke-width:2.5}
.emp-setup-note{text-align:center;font-size:11.5px;color:#9CA3AF;margin-top:10px;display:flex;align-items:center;justify-content:center;gap:5px}
.emp-setup-note svg{width:12px;height:12px;stroke:#9CA3AF;stroke-width:2}
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

  {{-- ── LEFT SIDEBAR ── --}}
  <aside class="ob-sidebar">

    {{-- Logo --}}
    <div class="ob-logo">
      <svg class="ob-logo-mark" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="36" height="36" rx="8" fill="#0D0D0D"/>
        <path d="M18 8 L22 16 L30 17 L24 23 L25.5 31 L18 27 L10.5 31 L12 23 L6 17 L14 16 Z" fill="#F5C518" stroke="none"/>
      </svg>
      <div class="ob-logo-text">
        <div class="ob-logo-name">UNIT</div>
        <div class="ob-logo-sub">AI Workers</div>
      </div>
    </div>

    {{-- Steps --}}
    <div class="ob-steps">

      {{-- Step 1 — Active --}}
      <div class="ob-step active">
        <div class="ob-step-num">1</div>
        <div class="ob-step-body">
          <div class="ob-step-label">Meet Ava</div>
          <div class="ob-step-desc">Introduce your worker</div>
        </div>
      </div>

      {{-- Step 2 --}}
      <div class="ob-step pending">
        <div class="ob-step-num">2</div>
        <div class="ob-step-body">
          <div class="ob-step-label">Workspace</div>
          <div class="ob-step-desc">Prepare Ava's desk</div>
        </div>
      </div>

      {{-- Step 3 --}}
      <div class="ob-step pending">
        <div class="ob-step-num">3</div>
        <div class="ob-step-body">
          <div class="ob-step-label">Orientation</div>
          <div class="ob-step-desc">Teach Ava your business</div>
        </div>
      </div>

      {{-- Step 4 --}}
      <div class="ob-step pending">
        <div class="ob-step-num">4</div>
        <div class="ob-step-body">
          <div class="ob-step-label">First Assignment</div>
          <div class="ob-step-desc">Give Ava her first job</div>
        </div>
      </div>

      {{-- Step 5 --}}
      <div class="ob-step pending">
        <div class="ob-step-num">5</div>
        <div class="ob-step-body">
          <div class="ob-step-label">On Shift</div>
          <div class="ob-step-desc">Ava starts working for you</div>
        </div>
      </div>

    </div>

    {{-- Security note --}}
    <div class="ob-security">
      <div class="ob-security-top">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>We never store your password.<br>You're always in control.</p>
    </div>

  </aside>

  {{-- ── CENTER ── --}}
  <main class="ob-center">

    <div class="ob-center-content">

      {{-- Available badge --}}
      <div class="ob-badge">
        <span class="ob-badge-dot"></span>
        Available for hire
      </div>

      {{-- Headline --}}
      <h1 class="ob-h1">
        {{ $firstName }},<br>
        meet your newest<br>
        <span class="gold">teammate.</span>
      </h1>

      {{-- Sub --}}
      <p class="ob-sub">
        Ava is your Renewal Specialist.<br>
        She works 24/7 to protect your revenue<br>
        and make sure no renewal slips through the cracks.
      </p>

      {{-- Ava message bubble --}}
      <div class="ob-bubble">
        <div class="ob-bubble-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        </div>
        <p>Hi {{ $firstName }},</p>
        <p>I'm excited to join your team.</p>
        <p>I'll quietly watch your inbox so you never miss another renewal.</p>
      </div>

      {{-- Social proof --}}
      <div class="ob-proof">
        <div class="ob-proof-avs">
          <img src="/images/ava.png" alt="">
          <img src="/images/ava-stand.png" alt="">
          <img src="/images/ava-life.png" alt="">
          <img src="/images/ava.png" alt="" style="filter:hue-rotate(30deg)">
        </div>
        <div class="ob-proof-txt">
          <strong>2,847+ businesses</strong><br>
          already hired their first worker
        </div>
      </div>

    </div>

    {{-- AVA character art --}}
    <div class="ob-ava-art">
      <img src="/images/ava-stand.png" alt="AVA">
    </div>

  </main>

  {{-- ── RIGHT PANEL ── --}}
  <aside class="ob-right">

    <div class="emp-eyebrow">Employee Profile</div>

    {{-- Name + badge --}}
    <div class="emp-header">
      <div>
        <div class="emp-name">AVA</div>
        <div class="emp-title">Renewal Specialist</div>
      </div>
      <div class="emp-badge">
        <div class="emp-badge-letter">A</div>
        <div class="emp-badge-status">Available</div>
      </div>
    </div>

    {{-- Details grid --}}
    <div class="emp-grid">
      <div class="emp-row">
        <span class="emp-row-label">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Employee ID
        </span>
        <span class="emp-row-val">AVA-001</span>
      </div>
      <div class="emp-row">
        <span class="emp-row-label">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path stroke-linecap="round" d="M9 22V12h6v10"/></svg>
          Department
        </span>
        <span class="emp-row-val">Customer Success</span>
      </div>
      <div class="emp-row">
        <span class="emp-row-label">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Reports To
        </span>
        <span class="emp-row-val">You</span>
      </div>
      <div class="emp-row">
        <span class="emp-row-label">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          Shift
        </span>
        <span class="emp-row-val">24 / 7</span>
      </div>
      <div class="emp-row">
        <span class="emp-row-label">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Start Date
        </span>
        <span class="emp-row-val">Today</span>
      </div>
      <div class="emp-row">
        <span class="emp-row-label">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
          Employment Type
        </span>
        <span class="emp-row-val">Digital Worker</span>
      </div>
    </div>

    {{-- What Ava does --}}
    <div class="emp-what-label">What Ava will do for you</div>
    <div class="emp-what-list">
      @foreach($whatIDo as $item)
      <div class="emp-what-item">
        <div class="emp-what-check">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        {{ $item }}
      </div>
      @endforeach
    </div>

    {{-- Hire button --}}
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

{{-- x-self-learn page_key="onboarding.ava.welcome" --}}

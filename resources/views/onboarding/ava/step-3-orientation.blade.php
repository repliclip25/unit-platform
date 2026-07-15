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

/* ── SIDEBAR (identical shell) ── */
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
.ob-hero{position:relative;overflow:hidden;background:#2a2420}
.ob-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 30%}
.ob-hero-fade{
  position:absolute;inset:0;z-index:1;pointer-events:none;
  background:linear-gradient(to right,#fff 0%,#fff 24%,rgba(255,255,255,.85) 38%,rgba(255,255,255,.3) 56%,transparent 72%);
}
.ob-hero-content{
  position:relative;z-index:2;
  padding:32px 36px;max-width:460px;height:100%;
  display:flex;flex-direction:column;justify-content:center;
}

/* Step eyebrow */
.ob-step-tag{
  display:inline-flex;align-items:center;gap:9px;
  font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
  color:#6B7280;margin-bottom:16px;width:fit-content;
}
.ob-step-tag svg{width:16px;height:16px;stroke:#6B7280;stroke-width:2;fill:none;flex-shrink:0}

.ob-h1{
  font-size:clamp(1.7rem,2.2vw,2.2rem);
  font-weight:900;letter-spacing:-.04em;line-height:1.1;
  color:#0D0D0D;margin-bottom:10px;
}
.ob-gold{color:#0D0D0D;position:relative;display:inline}
.ob-gold::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}

.ob-sub{font-size:13.5px;color:#374151;line-height:1.65;margin-bottom:20px}

/* 2-col topic cards grid */
.ob-topics{
  display:grid;grid-template-columns:1fr 1fr;gap:8px;
  margin-bottom:14px;
}
.ob-topic{
  display:flex;align-items:flex-start;gap:9px;
  background:rgba(255,255,255,.92);border:1px solid rgba(0,0,0,.07);
  border-radius:12px;padding:11px 12px;
  backdrop-filter:blur(4px);
}
.ob-topic-icon{
  width:30px;height:30px;border-radius:8px;
  background:#F4F3F1;border:1px solid #E8E7E4;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.ob-topic-icon svg{width:15px;height:15px;stroke:#374151;stroke-width:1.8;fill:none}
.ob-topic-title{font-size:12px;font-weight:700;color:#0D0D0D;line-height:1.2;margin-bottom:3px}
.ob-topic-desc{font-size:11px;color:#6B7280;line-height:1.4}

.ob-footer-note{font-size:11.5px;color:#9CA3AF}

/* AVA speech bubble — floats over the image */
.ob-bubble{
  position:absolute;z-index:3;
  top:50%;right:28%;
  transform:translateY(-60%);
  background:#fff;border:1px solid #E5E7EB;
  border-radius:16px;border-bottom-left-radius:4px;
  padding:14px 18px;width:200px;
  box-shadow:0 4px 16px rgba(0,0,0,.1);
}
.ob-bubble p{font-size:13px;font-weight:600;color:#0D0D0D;line-height:1.55}

/* ── RIGHT PANEL ── */
.ob-profile{
  background:#fff;border-left:1px solid #F0F0F0;
  padding:28px 22px;display:flex;flex-direction:column;overflow-y:auto;
}
.emp-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:14px}

/* Circular progress */
.ob-progress-wrap{display:flex;flex-direction:column;align-items:center;margin-bottom:8px}
.ob-progress-ring{position:relative;width:100px;height:100px}
.ob-progress-ring svg{width:100px;height:100px;transform:rotate(-90deg)}
.ob-progress-bg{fill:none;stroke:#F3F4F6;stroke-width:8}
.ob-progress-fill{fill:none;stroke:#F5C518;stroke-width:8;stroke-linecap:round;stroke-dasharray:264;stroke-dashoffset:74}
.ob-progress-label{
  position:absolute;inset:0;display:flex;flex-direction:column;
  align-items:center;justify-content:center;
}
.ob-progress-pct{font-size:22px;font-weight:900;color:#0D0D0D;letter-spacing:-.04em;line-height:1}
.ob-progress-sub{font-size:10px;color:#9CA3AF;margin-top:2px;font-weight:600}
.ob-progress-caption{font-size:12px;color:#6B7280;text-align:center;margin-top:8px;margin-bottom:18px}

/* Learning list */
.emp-divider{border:none;border-top:1px solid #F0F0F0;margin:0 0 14px}
.ob-learn-title{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px}
.ob-learn-list{display:flex;flex-direction:column;gap:8px;flex:1}
.ob-learn-item{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:600;color:#0D0D0D}
.ob-learn-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.ob-learn-dot.filled{background:#F5C518}
.ob-learn-dot.empty{background:transparent;border:2px solid #D1D5DB}

/* Continue button */
.btn-continue{
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 20px;border-radius:13px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  font-size:14.5px;font-weight:800;letter-spacing:-.01em;
  transition:opacity .15s,transform .1s;
  margin-top:auto;width:100%;text-decoration:none;
}
.btn-continue:hover{opacity:.88;transform:translateY(-1px)}
.btn-continue svg{width:17px;height:17px;stroke:#fff;stroke-width:2.5;flex-shrink:0}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow:auto;height:auto}
  .ob-page{grid-template-columns:1fr;height:auto;overflow:visible}
  .ob-sidebar{
    flex-direction:row;align-items:center;justify-content:space-between;
    padding:14px 20px;border-bottom:1px solid #E5E7EB;
    background:#fff;position:sticky;top:0;z-index:10;
  }
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
  .ob-hero-content{
    position:static;background:#fff;
    padding:24px 20px 20px;max-width:100%;height:auto;
    display:flex;flex-direction:column;justify-content:flex-start;order:1;
  }
  .ob-hero-fade{display:none}
  .ob-hero-img{position:static;display:block;width:100%;height:220px;object-fit:cover;object-position:center 30%;order:2}
  .ob-bubble{display:none}
  .ob-topics{grid-template-columns:1fr 1fr;gap:6px}
  .ob-h1{font-size:1.6rem}
  .ob-profile{border-left:none;border-top:1px solid #F0F0F0;padding:20px}
  .btn-continue{margin-top:16px}
}
@media(max-width:480px){
  .ob-topics{grid-template-columns:1fr}
  .ob-hero-img{height:180px}
  .ob-h1{font-size:1.4rem}
  .ob-card-area{padding:12px}
  .ob-profile{padding:16px}
}
</style>
</head>
<body>

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

        {{-- Speech bubble floats over image --}}
        <div class="ob-bubble">
          <p>The more I learn today, the better I'll work for you tomorrow.</p>
        </div>

        <div class="ob-hero-content">

          <div class="ob-step-tag">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            Step 3 of 5
          </div>

          <h1 class="ob-h1">
            Let's get Ava<br>
            <span class="ob-gold">up to speed.</span>
          </h1>

          <p class="ob-sub">
            The more Ava learns about your business,<br>
            the better she'll protect your renewals<br>
            and represent you.
          </p>

          <div class="ob-topics">

            <div class="ob-topic">
              <div class="ob-topic-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              </div>
              <div>
                <div class="ob-topic-title">Business Basics</div>
                <div class="ob-topic-desc">Your company, products and services</div>
              </div>
            </div>

            <div class="ob-topic">
              <div class="ob-topic-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <div>
                <div class="ob-topic-title">Customers</div>
                <div class="ob-topic-desc">Who your customers are and how you work with them</div>
              </div>
            </div>

            <div class="ob-topic">
              <div class="ob-topic-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
              </div>
              <div>
                <div class="ob-topic-title">Renewal Process</div>
                <div class="ob-topic-desc">How renewals work in your business</div>
              </div>
            </div>

            <div class="ob-topic">
              <div class="ob-topic-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              </div>
              <div>
                <div class="ob-topic-title">Communication Style</div>
                <div class="ob-topic-desc">Your tone, preferences and writing style</div>
              </div>
            </div>

            <div class="ob-topic">
              <div class="ob-topic-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              </div>
              <div>
                <div class="ob-topic-title">Knowledge & Resources</div>
                <div class="ob-topic-desc">Policies, docs and resources Ava can reference</div>
              </div>
            </div>

            <div class="ob-topic">
              <div class="ob-topic-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3M12 17h.01"/></svg>
              </div>
              <div>
                <div class="ob-topic-title">FAQ & Objections</div>
                <div class="ob-topic-desc">Common questions and how to respond</div>
              </div>
            </div>

          </div>

          <p class="ob-footer-note">You can update or add more anytime.</p>

        </div>
      </div>

      {{-- Right panel: progress + continue --}}
      <div class="ob-profile">
        <div class="emp-eyebrow">AVA'S LEARNING PROGRESS</div>

        <div class="ob-progress-wrap">
          <div class="ob-progress-ring">
            <svg viewBox="0 0 100 100">
              <circle class="ob-progress-bg" cx="50" cy="50" r="42"/>
              <circle class="ob-progress-fill" cx="50" cy="50" r="42"/>
            </svg>
            <div class="ob-progress-label">
              <div class="ob-progress-pct">72%</div>
              <div class="ob-progress-sub">Knowledge</div>
            </div>
          </div>
          <p class="ob-progress-caption">Keep going! You're doing great.</p>
        </div>

        <hr class="emp-divider">

        <div class="ob-learn-title">What Ava is learning</div>
        <div class="ob-learn-list">
          <div class="ob-learn-item"><span class="ob-learn-dot filled"></span>Business Basics</div>
          <div class="ob-learn-item"><span class="ob-learn-dot filled"></span>Customers</div>
          <div class="ob-learn-item"><span class="ob-learn-dot filled"></span>Renewal Process</div>
          <div class="ob-learn-item"><span class="ob-learn-dot filled"></span>Communication Style</div>
          <div class="ob-learn-item"><span class="ob-learn-dot empty"></span>Knowledge &amp; Resources</div>
          <div class="ob-learn-item"><span class="ob-learn-dot empty"></span>FAQ &amp; Objections</div>
        </div>

        <a href="{{ route('hire.ava.assignment') }}" class="btn-continue">
          Continue
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>

      </div>

    </div>
  </div>

</div>

<x-self-learn page="hire.ava.orientation" />
</body>
</html>

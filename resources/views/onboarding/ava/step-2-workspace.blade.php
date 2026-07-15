<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Ava's Workspace — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{font-family:'Inter',sans-serif;background:#F4F3F1;color:#0D0D0D;-webkit-font-smoothing:antialiased}

.ob-page{display:grid;grid-template-columns:260px 1fr;height:100vh;overflow:hidden}

/* ── SIDEBAR (identical to step-1) ── */
.ob-sidebar{background:#F4F3F1;display:flex;flex-direction:column;padding:32px 24px;overflow-y:auto}
.ob-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;margin-bottom:44px}
.ob-steps{display:flex;flex-direction:column;flex:1}
.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative}
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

/* ── CARD AREA (identical to step-1) ── */
.ob-card-area{display:flex;align-items:center;justify-content:center;padding:20px 24px 20px 12px;overflow:hidden}
.ob-card{
  display:grid;grid-template-columns:1fr 290px;
  width:100%;height:100%;max-height:calc(100vh - 40px);
  border-radius:20px;overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);
  border:1px solid rgba(0,0,0,.07);
}

/* ── HERO — desk image ── */
.ob-hero{position:relative;overflow:hidden;background:#2a2420}
.ob-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 40%}
.ob-hero-fade{
  position:absolute;inset:0;z-index:1;pointer-events:none;
  background:linear-gradient(to right,#fff 0%,#fff 20%,rgba(255,255,255,.88) 34%,rgba(255,255,255,.4) 52%,transparent 70%);
}
.ob-hero-content{
  position:relative;z-index:2;
  padding:40px 36px;max-width:420px;height:100%;
  display:flex;flex-direction:column;justify-content:center;
}

/* Step eyebrow */
.ob-step-tag{
  display:inline-flex;align-items:center;gap:9px;
  font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
  color:#6B7280;margin-bottom:20px;width:fit-content;
}
.ob-step-tag svg{width:16px;height:16px;stroke:#6B7280;stroke-width:2;fill:none;flex-shrink:0}

.ob-h1{
  font-size:clamp(1.8rem,2.4vw,2.4rem);
  font-weight:900;letter-spacing:-.04em;line-height:1.07;
  color:#0D0D0D;margin-bottom:14px;
}
.ob-sub{font-size:14px;color:#374151;line-height:1.72;margin-bottom:24px}

/* Gmail button — in the hero, same weight as Hire Ava in step-1 */
.btn-gmail{
  display:flex;align-items:center;gap:12px;
  width:fit-content;min-width:220px;
  padding:14px 20px;border-radius:13px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  text-decoration:none;
  font-size:14.5px;font-weight:800;letter-spacing:-.01em;
  transition:opacity .15s,transform .1s;
  margin-bottom:20px;
}
.btn-gmail:hover{opacity:.88;transform:translateY(-1px)}
.btn-gmail-icon{
  width:28px;height:28px;border-radius:6px;background:#fff;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.btn-gmail-text{display:flex;flex-direction:column;gap:2px;flex:1}
.btn-gmail-main{font-size:14px;font-weight:800;color:#fff;line-height:1}
.btn-gmail-sub{font-size:10.5px;color:rgba(255,255,255,.55);line-height:1}
.btn-gmail svg.arrow{width:17px;height:17px;stroke:#fff;stroke-width:2.5;flex-shrink:0}

/* "Your data is safe" strip — replaces social proof in hero */
.ob-safe-strip{
  display:flex;align-items:flex-start;gap:10px;
  background:#fff;border:1px solid #E5E7EB;
  border-radius:12px;padding:12px 14px;
  max-width:310px;
}
.ob-safe-strip svg{width:18px;height:18px;stroke:#16a34a;stroke-width:2;fill:none;flex-shrink:0;margin-top:1px}
.ob-safe-strip-title{font-size:12px;font-weight:700;color:#0D0D0D;margin-bottom:3px}
.ob-safe-strip-body{font-size:12px;color:#4B5563;line-height:1.5}

/* ── RIGHT PANEL (same structure as step-1 profile) ── */
.ob-profile{
  background:#fff;border-left:1px solid #F0F0F0;
  padding:28px 22px;display:flex;flex-direction:column;overflow-y:auto;
}
.emp-eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#6B7280;margin-bottom:10px}
.emp-name{font-size:1.65rem;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;line-height:1}
.emp-role{font-size:13px;color:#374151;margin-top:4px;margin-bottom:16px}
.emp-divider{border:none;border-top:1px solid #F0F0F0;margin:0 0 12px}
.emp-row{display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid #F5F5F5}
.emp-row:last-child{border-bottom:none}
.emp-row-key{display:flex;align-items:center;gap:6px;font-size:12px;color:#6B7280;font-weight:500}
.emp-row-key svg{width:13px;height:13px;stroke:#9CA3AF;stroke-width:1.8;flex-shrink:0}
.emp-row-val{font-size:12px;font-weight:700;display:flex;align-items:center;gap:5px}
.emp-row-val.amber{color:#D97706}
.emp-row-val.grey{color:#6B7280}
.status-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.dot-amber{background:#F59E0B}
.dot-grey{background:#D1D5DB}

.ob-lock-note{
  display:flex;align-items:flex-start;gap:7px;
  font-size:12px;color:#6B7280;line-height:1.55;
  margin-bottom:0;margin-top:12px;
}
.ob-lock-note svg{width:13px;height:13px;stroke:#9CA3AF;flex-shrink:0;margin-top:2px}
.ob-lock-note strong{color:#374151;font-weight:600}

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

  /* On mobile: show text on white ABOVE the image, then image below */
  .ob-hero{display:flex;flex-direction:column;min-height:unset;background:#fff}

  /* Text content sits on top, white background — fully legible */
  .ob-hero-content{
    position:static;background:#fff;
    padding:24px 20px 20px;
    max-width:100%;height:auto;
    display:flex;flex-direction:column;justify-content:flex-start;
    order:1;
  }
  .ob-hero-fade{display:none}

  /* Image below the text */
  .ob-hero-img{
    position:static;display:block;
    width:100%;height:220px;
    object-fit:cover;object-position:center 30%;
    order:2;
  }

  /* Button full width on mobile */
  .btn-gmail{width:100%;min-width:0}
  .ob-h1{font-size:1.75rem}
  .ob-sub{font-size:13.5px;margin-bottom:20px}
  .ob-step-tag{margin-bottom:14px}

  .ob-profile{border-left:none;border-top:1px solid #F0F0F0;padding:20px}
}

@media(max-width:480px){
  .ob-hero-img{height:180px}
  .ob-h1{font-size:1.5rem}
  .ob-card-area{padding:12px}
  .ob-profile{padding:16px}
}
</style>
</head>
<body>

@php $firstName = explode(' ', auth()->user()->name ?? 'there')[0]; @endphp

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
          <div class="ob-step-desc">Introduce your worker</div>
        </div>
      </a>
      <div class="ob-step active">
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

  {{-- ══ FLOATING CARD ══ --}}
  <div class="ob-card-area">
    <div class="ob-card">

      {{-- Hero: desk image --}}
      <div class="ob-hero">
        <img class="ob-hero-img" src="/images/ava-desk.png" alt="Ava's workspace">
        <div class="ob-hero-fade"></div>
        <div class="ob-hero-content">

          <div class="ob-step-tag">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Step 2 of 5
          </div>

          <h1 class="ob-h1">
            Every worker<br>
            needs a<br>
            workspace.
          </h1>

          <p class="ob-sub">
            Ava works inside your Gmail.<br>
            Give her access so she can begin<br>
            monitoring renewals and preparing<br>
            drafts for you.
          </p>

          {{-- Primary action — right after the text, same as Hire Ava in step 1 --}}
          <a href="{{ route('ava.gmail.authorize') }}" class="btn-gmail">
            <div class="btn-gmail-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
              </svg>
            </div>
            <div class="btn-gmail-text">
              <span class="btn-gmail-main">Set Up Workspace</span>
              <span class="btn-gmail-sub">Connect Gmail</span>
            </div>
            <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>

          <div class="ob-safe-strip">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
            <div>
              <div class="ob-safe-strip-title">Your data is safe</div>
              <div class="ob-safe-strip-body">Ava only accesses the data you allow. You can revoke access at any time.</div>
            </div>
          </div>

        </div>
      </div>

      {{-- Right panel: status + security ── no action button here --}}
      <div class="ob-profile">
        <div class="emp-eyebrow">Workspace Setup</div>
        <div class="emp-name">AVA</div>
        <div class="emp-role">Renewal Specialist</div>

        <hr class="emp-divider">

        <div class="emp-row">
          <span class="emp-row-key">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Workspace
          </span>
          <span class="emp-row-val amber"><span class="status-dot dot-amber"></span>Preparing...</span>
        </div>
        <div class="emp-row">
          <span class="emp-row-key">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Gmail Access
          </span>
          <span class="emp-row-val grey"><span class="status-dot dot-grey"></span>Not connected</span>
        </div>
        <div class="emp-row">
          <span class="emp-row-key">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Reports To
          </span>
          <span class="emp-row-val" style="color:#0D0D0D">You</span>
        </div>
        <div class="emp-row">
          <span class="emp-row-key">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            Shift
          </span>
          <span class="emp-row-val" style="color:#0D0D0D">24 / 7</span>
        </div>

        <div class="ob-lock-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
          <span><strong>Secure OAuth 2.0</strong> — We never see or store your password.</span>
        </div>

      </div>

    </div>
  </div>

</div>

</body>
</html>

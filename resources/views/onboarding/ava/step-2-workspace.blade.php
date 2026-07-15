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

/* ══════════════════════════════
   PAGE SHELL  (identical to step-1)
══════════════════════════════ */
.ob-page{
  display:grid;
  grid-template-columns:260px 1fr;
  height:100vh;
  overflow:hidden;
}

/* ══════════════════════════════
   SIDEBAR  (identical to step-1)
══════════════════════════════ */
.ob-sidebar{
  background:#F4F3F1;
  display:flex;flex-direction:column;
  padding:32px 24px;
  overflow-y:auto;
}
.ob-logo{
  font-size:21px;font-weight:900;letter-spacing:-.04em;
  color:#0D0D0D;margin-bottom:44px;
}
.ob-steps{display:flex;flex-direction:column;flex:1}
.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative}
.ob-step:not(:last-child) .ob-step-rail::after{
  content:'';position:absolute;
  left:13px;top:30px;
  width:2px;height:calc(100% - 6px);
  background:#DCDCDC;border-radius:2px;
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
.ob-step.pending .ob-step-num{background:#E8E7E4;color:#888;border:1.5px solid #DCDCDC}
.ob-step.active  .ob-step-num{background:#0D0D0D;color:#fff;box-shadow:0 0 0 4px rgba(0,0,0,.1)}
.ob-step.done    .ob-step-num{background:#0D0D0D;color:#fff}
.ob-step-body{padding-top:4px;padding-bottom:28px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:13.5px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-step.pending .ob-step-label{color:#6B7280}
.ob-step-desc{font-size:12px;color:#9CA3AF;margin-top:3px;line-height:1.4}
.ob-step.active .ob-step-desc{color:#374151}
.ob-step.active .ob-step-body{
  background:#fff;border:1.5px solid #E5E7EB;
  border-radius:12px;padding:10px 14px;margin-right:-4px;
}
.ob-security{
  margin-top:8px;padding:14px 16px;border-radius:12px;
  background:#ECEAE6;border:1px solid #DCDCDC;
}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:5px}
.ob-security-row svg{width:13px;height:13px;stroke:#6B7280;flex-shrink:0}
.ob-security-title{font-size:12px;font-weight:700;color:#0D0D0D}
.ob-security p{font-size:11px;color:#6B7280;line-height:1.55}

/* ══════════════════════════════
   CARD AREA  (identical to step-1)
══════════════════════════════ */
.ob-card-area{
  display:flex;
  align-items:center;
  justify-content:center;
  padding:20px 24px 20px 12px;
  overflow:hidden;
}
.ob-card{
  display:grid;
  grid-template-columns:1fr 300px;
  width:100%;height:100%;
  max-height:calc(100vh - 40px);
  border-radius:20px;
  overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);
  border:1px solid rgba(0,0,0,.07);
}

/* ══════════════════════════════
   HERO — workspace scene
══════════════════════════════ */
.ob-hero{position:relative;overflow:hidden;background:#1a1714}
.ob-hero-img{
  position:absolute;inset:0;width:100%;height:100%;
  object-fit:cover;object-position:center center;
}
/* Right-to-left fade so right panel lifts off cleanly */
.ob-hero-fade{
  position:absolute;inset:0;z-index:1;pointer-events:none;
  background:linear-gradient(to right,rgba(0,0,0,.3) 0%,transparent 50%,rgba(255,255,255,.95) 100%);
}
.ob-hero-content{
  position:relative;z-index:2;
  padding:40px 36px;
  max-width:460px;height:100%;
  display:flex;flex-direction:column;justify-content:center;
}

/* Step eyebrow */
.ob-eyebrow{
  display:flex;align-items:center;gap:8px;
  font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
  color:rgba(255,255,255,.6);margin-bottom:20px;
}
.ob-eyebrow-dot{
  width:7px;height:7px;border-radius:50%;
  background:#F5C518;flex-shrink:0;
}

.ob-h1{
  font-size:clamp(1.9rem,2.6vw,2.6rem);
  font-weight:900;letter-spacing:-.04em;line-height:1.06;
  color:#fff;margin-bottom:14px;
}
.ob-sub{font-size:14px;color:rgba(255,255,255,.7);line-height:1.72;margin-bottom:0}

/* ══════════════════════════════
   RIGHT PANEL — action
══════════════════════════════ */
.ob-panel{
  background:#fff;border-left:1px solid #F0F0F0;
  padding:32px 24px;
  display:flex;flex-direction:column;
  overflow-y:auto;
}

.emp-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px}

/* Mini employee badge */
.emp-badge{
  display:flex;align-items:center;gap:10px;
  padding:10px 12px;border-radius:10px;
  background:#F9F9F8;border:1px solid #F0F0F0;
  margin-bottom:20px;
}
.emp-badge-avatar{
  width:32px;height:32px;border-radius:7px;
  background:#0D0D0D;color:#F5C518;
  display:flex;align-items:center;justify-content:center;
  font-size:13px;font-weight:900;flex-shrink:0;
}
.emp-badge-name{font-size:13px;font-weight:900;color:#0D0D0D;line-height:1}
.emp-badge-role{font-size:11px;color:#9CA3AF;margin-top:2px}

.ob-panel-divider{border:none;border-top:1px solid #F3F4F6;margin:0 0 20px}

/* Status rows */
.ob-status-label{font-size:9px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px}
.ob-status-row{display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid #F9FAFB}
.ob-status-row:last-of-type{border-bottom:none;margin-bottom:20px}
.ob-status-key{font-size:12px;color:#9CA3AF}
.ob-status-val{font-size:12px;font-weight:700;display:flex;align-items:center;gap:5px}
.dot-amber{width:7px;height:7px;border-radius:50%;background:#F59E0B;flex-shrink:0}
.dot-grey{width:7px;height:7px;border-radius:50%;background:#D1D5DB;flex-shrink:0}
.val-amber{color:#D97706}
.val-grey{color:#9CA3AF}

/* Gmail connect button */
.btn-gmail{
  display:flex;align-items:center;gap:12px;
  padding:14px 16px;border-radius:13px;
  background:#0D0D0D;color:#fff;
  border:none;cursor:pointer;width:100%;text-align:left;
  text-decoration:none;
  transition:opacity .15s,transform .1s;
  margin-bottom:12px;
}
.btn-gmail:hover{opacity:.88;transform:translateY(-1px)}
.btn-gmail-icon{
  width:34px;height:34px;border-radius:7px;background:#fff;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.btn-gmail-text{display:flex;flex-direction:column;flex:1}
.btn-gmail-main{font-size:14px;font-weight:800;letter-spacing:-.01em;color:#fff}
.btn-gmail-sub{font-size:11px;color:rgba(255,255,255,.55);margin-top:1px}
.btn-gmail-arrow{margin-left:auto;flex-shrink:0}
.btn-gmail-arrow svg{width:16px;height:16px;stroke:#fff;stroke-width:2.5}

/* Secure note */
.ob-secure-note{
  display:flex;align-items:flex-start;gap:7px;
  font-size:11.5px;color:#9CA3AF;line-height:1.5;
  margin-bottom:20px;
}
.ob-secure-note svg{width:13px;height:13px;stroke:#C4C4C4;flex-shrink:0;margin-top:1px}
.ob-secure-note strong{color:#6B7280;font-weight:600}

/* Data safety card */
.ob-safe-card{
  margin-top:auto;
  background:#F0FDF4;border:1px solid #DCFCE7;
  border-radius:12px;padding:14px;
  display:flex;gap:10px;
}
.ob-safe-icon{
  width:30px;height:30px;border-radius:7px;
  background:#fff;border:1px solid #DCFCE7;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.ob-safe-icon svg{width:14px;height:14px;stroke:#16a34a;stroke-width:2.2}
.ob-safe-title{font-size:9.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#16a34a;margin-bottom:4px}
.ob-safe-body{font-size:11px;color:#4B7A5C;line-height:1.55}

/* ══════════════════════════════
   MOBILE
══════════════════════════════ */
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
  .ob-card{
    display:flex;flex-direction:column;
    width:100%;height:auto;max-height:none;
  }
  .ob-hero{min-height:300px}
  .ob-hero-fade{
    background:linear-gradient(to top,rgba(0,0,0,.6) 0%,transparent 60%);
  }
  .ob-hero-content{
    justify-content:flex-end;
    padding:28px 24px;
    max-width:100%;
  }
  .ob-h1{font-size:1.7rem}
  .ob-panel{border-left:none;border-top:1px solid #F0F0F0;padding:24px 20px}
  .btn-gmail{font-size:14px}
}

@media(max-width:480px){
  .ob-hero{min-height:240px}
  .ob-h1{font-size:1.5rem}
  .ob-card-area{padding:12px}
}
</style>
</head>
<body>

<div class="ob-page">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ob-sidebar">
    <div class="ob-logo">UNIT</div>

    <div class="ob-steps">
      <div class="ob-step done">
        <div class="ob-step-rail"><div class="ob-step-num">✓</div></div>
        <div class="ob-step-body">
          <div class="ob-step-label">Meet Ava</div>
          <div class="ob-step-desc">Introduce your worker</div>
        </div>
      </div>
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

      {{-- Hero: workspace scene --}}
      <div class="ob-hero">
        <img class="ob-hero-img"
             src="/images/ava-life.png"
             alt="Ava's workspace"
             onerror="this.style.opacity='.3'">
        <div class="ob-hero-fade"></div>
        <div class="ob-hero-content">
          <div class="ob-eyebrow">
            <span class="ob-eyebrow-dot"></span>
            Step 2 of 5
          </div>
          <h1 class="ob-h1">Every worker needs<br>a workspace.</h1>
          <p class="ob-sub">
            Ava works inside your Gmail.<br>
            Give her access so she can start<br>
            monitoring renewals and preparing<br>
            drafts for you.
          </p>
        </div>
      </div>

      {{-- Right panel: action --}}
      <div class="ob-panel">
        <div class="emp-eyebrow">Employee Status</div>

        <div class="emp-badge">
          <div class="emp-badge-avatar">A</div>
          <div>
            <div class="emp-badge-name">AVA</div>
            <div class="emp-badge-role">Renewal Specialist</div>
          </div>
        </div>

        <hr class="ob-panel-divider">

        <div class="ob-status-label">Workspace setup</div>
        <div class="ob-status-row">
          <span class="ob-status-key">Workspace</span>
          <span class="ob-status-val val-amber">
            <span class="dot-amber"></span>Preparing...
          </span>
        </div>
        <div class="ob-status-row">
          <span class="ob-status-key">Gmail access</span>
          <span class="ob-status-val val-grey">
            <span class="dot-grey"></span>Not connected
          </span>
        </div>

        {{-- Primary action --}}
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
            <span class="btn-gmail-main">Connect Gmail</span>
            <span class="btn-gmail-sub">Set up Ava's workspace</span>
          </div>
          <div class="btn-gmail-arrow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
        </a>

        <div class="ob-secure-note">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
          <span><strong>Secure OAuth 2.0</strong> — We never see or store your password.</span>
        </div>

        <div class="ob-safe-card">
          <div class="ob-safe-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </div>
          <div>
            <div class="ob-safe-title">Your Data Is Safe</div>
            <div class="ob-safe-body">Ava only accesses the data you allow. You can revoke access at any time.</div>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>

</body>
</html>

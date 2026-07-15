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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Caveat:wght@500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{font-family:'Inter',sans-serif;background:#F4F3F1;color:#0D0D0D;-webkit-font-smoothing:antialiased}

/* ══ PAGE SHELL ══ */
.ob-page{
  display:grid;
  grid-template-columns:240px 1fr;
  grid-template-rows:1fr auto;
  height:100vh;
}

/* ══ SIDEBAR ══ */
.ob-sidebar{
  grid-row:1/3;
  background:#F4F3F1;
  display:flex;flex-direction:column;
  padding:28px 20px;
  overflow-y:auto;
  border-right:1px solid #E8E7E4;
}
.ob-logo{font-size:19px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;margin-bottom:36px}

.ob-steps{display:flex;flex-direction:column;flex:1}
.ob-step{display:flex;align-items:flex-start;gap:12px;position:relative}
.ob-step:not(:last-child) .ob-step-rail::after{
  content:'';position:absolute;left:12px;top:28px;
  width:2px;height:calc(100% - 4px);background:#DCDCDC;border-radius:2px;
}
.ob-step.done:not(:last-child) .ob-step-rail::after{background:#0D0D0D}
.ob-step-rail{position:relative;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding-bottom:28px}
.ob-step:last-child .ob-step-rail{padding-bottom:0}
.ob-step-num{
  width:26px;height:26px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:800;position:relative;z-index:1;
}
.ob-step.pending .ob-step-num{background:#E8E7E4;color:#9CA3AF;border:1.5px solid #DCDCDC}
.ob-step.active  .ob-step-num{background:#0D0D0D;color:#fff;box-shadow:0 0 0 4px rgba(0,0,0,.1)}
.ob-step.done    .ob-step-num{background:#0D0D0D;color:#fff}
/* checkmark for done steps */
.ob-step.done .ob-step-num::after{
  content:'✓';font-size:12px;font-weight:900;
}
.ob-step.done .ob-step-num-inner{display:none}

.ob-step-body{padding-top:3px;padding-bottom:24px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:13px;font-weight:700;color:#6B7280;line-height:1.2}
.ob-step.active .ob-step-label,.ob-step.done .ob-step-label{color:#0D0D0D}
.ob-step-desc{font-size:11.5px;color:#9CA3AF;margin-top:2px;line-height:1.4}
.ob-step.active .ob-step-desc{color:#6B7280}

.ob-security{
  margin-top:auto;padding:13px 14px;border-radius:12px;
  background:#ECEAE6;border:1px solid #DCDCDC;
}
.ob-security-row{display:flex;align-items:center;gap:6px;margin-bottom:4px}
.ob-security-row svg{width:12px;height:12px;stroke:#6B7280;flex-shrink:0}
.ob-security-title{font-size:11.5px;font-weight:700;color:#0D0D0D}
.ob-security p{font-size:10.5px;color:#6B7280;line-height:1.5}

/* ══ MAIN CONTENT ROW ══ */
.ob-main{
  display:grid;
  grid-template-columns:320px 1fr 260px;
  overflow:hidden;
}

/* Left panel */
.ob-left{
  background:#fff;
  padding:36px 28px;
  display:flex;flex-direction:column;justify-content:center;
  border-right:1px solid #F0F0F0;
  overflow-y:auto;
}
.ob-step-eyebrow{
  display:flex;align-items:center;gap:7px;
  font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;
  color:#9CA3AF;margin-bottom:16px;
}
.ob-step-eyebrow svg{width:14px;height:14px;stroke:#F5C518;fill:#F5C518;flex-shrink:0}

.ob-h1{
  font-size:clamp(1.65rem,2vw,2.1rem);
  font-weight:900;letter-spacing:-.04em;line-height:1.08;
  color:#0D0D0D;margin-bottom:14px;
}
.ob-sub{font-size:13.5px;color:#6B7280;line-height:1.75;margin-bottom:28px}

/* Gmail connect button */
.btn-gmail{
  display:flex;align-items:center;gap:14px;
  padding:14px 18px;border-radius:14px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  text-align:left;width:100%;
  transition:opacity .15s,transform .1s;
  margin-bottom:16px;
}
.btn-gmail:hover{opacity:.88;transform:translateY(-1px)}
.btn-gmail-icon{
  width:36px;height:36px;border-radius:8px;background:#fff;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.btn-gmail-text-main{font-size:14.5px;font-weight:800;letter-spacing:-.01em;display:block}
.btn-gmail-text-sub{font-size:11px;color:rgba(255,255,255,.6);display:block;margin-top:2px}

.ob-secure-note{display:flex;align-items:center;gap:7px;font-size:11.5px;color:#9CA3AF}
.ob-secure-note svg{width:13px;height:13px;stroke:#DCDCDC;flex-shrink:0}
.ob-secure-note strong{color:#6B7280;font-weight:600}

/* Center: desk image */
.ob-desk{
  position:relative;overflow:hidden;
  background:linear-gradient(160deg,#2a2420 0%,#1a1410 60%,#0f0c0a 100%);
}
.ob-desk-img{
  position:absolute;inset:0;width:100%;height:100%;
  object-fit:cover;object-position:center center;
  opacity:.92;
}
/* Fallback desk art when image is missing */
.ob-desk-fallback{
  position:absolute;inset:0;
  display:flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg,#1c1916 0%,#2d2520 40%,#1a1512 100%);
}
/* Annotations */
.ob-annotation{
  position:absolute;z-index:3;
  font-family:'Caveat',cursive;
  font-size:17px;color:rgba(255,255,255,.85);
  display:flex;align-items:center;gap:4px;
  pointer-events:none;
}
.ob-annotation svg{width:20px;height:20px;stroke:rgba(255,255,255,.6);fill:none;flex-shrink:0}
.ann-desk{bottom:38%;left:10%}
.ann-inbox{bottom:52%;left:42%}
.ann-trust{bottom:38%;right:8%}

/* Bottom gradient fade on desk */
.ob-desk::after{
  content:'';position:absolute;bottom:0;left:0;right:0;height:80px;z-index:2;
  background:linear-gradient(to bottom,transparent,rgba(0,0,0,.3));
  pointer-events:none;
}

/* Right panel */
.ob-right{
  background:#F9F9F8;
  border-left:1px solid #F0F0F0;
  padding:24px 20px;
  display:flex;flex-direction:column;gap:16px;
  overflow-y:auto;
}

/* Employee status card */
.ob-status-card{
  background:#fff;border:1px solid #F0F0F0;
  border-radius:14px;padding:16px;
}
.ob-status-eyebrow{font-size:8.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#9CA3AF;margin-bottom:12px}
.ob-emp-row{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.ob-emp-avatar{
  width:36px;height:36px;border-radius:8px;
  background:#0D0D0D;color:#F5C518;
  display:flex;align-items:center;justify-content:center;
  font-size:15px;font-weight:900;flex-shrink:0;
}
.ob-emp-name{font-size:14px;font-weight:900;letter-spacing:-.02em;color:#0D0D0D;line-height:1}
.ob-emp-role{font-size:11px;color:#9CA3AF;margin-top:2px}

.ob-status-divider{border:none;border-top:1px solid #F3F4F6;margin:0 0 10px}

.ob-status-row{display:flex;align-items:center;justify-content:space-between;padding:5px 0}
.ob-status-row:not(:last-child){border-bottom:1px solid #F9FAFB}
.ob-status-key{font-size:11.5px;color:#9CA3AF}
.ob-status-val{font-size:11.5px;font-weight:700;display:flex;align-items:center;gap:5px}
.ob-status-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.dot-preparing{background:#F59E0B}
.dot-not-connected{background:#E5E7EB}
.val-preparing{color:#D97706}
.val-not-connected{color:#9CA3AF}

/* Data safety card */
.ob-safe-card{
  background:#fff;border:1px solid #F0F0F0;
  border-radius:14px;padding:16px;
  display:flex;gap:12px;
}
.ob-safe-icon{
  width:34px;height:34px;border-radius:8px;
  background:#F0FDF4;border:1px solid #DCFCE7;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.ob-safe-icon svg{width:16px;height:16px;stroke:#16a34a;stroke-width:2}
.ob-safe-title{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#16a34a;margin-bottom:5px}
.ob-safe-body{font-size:11.5px;color:#6B7280;line-height:1.6}

/* ══ BOTTOM TRUST BAR ══ */
.ob-trust{
  grid-column:2/3;
  background:#fff;border-top:1px solid #F0F0F0;
  padding:12px 28px;
  display:flex;align-items:center;gap:28px;
  overflow-x:auto;
}
.ob-trust-main{display:flex;align-items:center;gap:7px;font-size:11.5px;color:#9CA3AF;white-space:nowrap;flex-shrink:0}
.ob-trust-main svg{width:14px;height:14px;stroke:#DCDCDC;flex-shrink:0}
.ob-trust-divider{width:1px;height:16px;background:#E5E7EB;flex-shrink:0}
.ob-trust-logos{display:flex;align-items:center;gap:24px}
.ob-trust-logo{font-size:12px;font-weight:700;color:#C4C4C4;letter-spacing:-.01em;white-space:nowrap;display:flex;align-items:center;gap:5px}
.ob-trust-logo svg{width:13px;height:13px;fill:#C4C4C4}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow:auto;height:auto}
  .ob-page{grid-template-columns:1fr;grid-template-rows:auto 1fr auto;height:auto}

  .ob-sidebar{
    grid-row:1;border-right:none;border-bottom:1px solid #E8E7E4;
    flex-direction:row;align-items:center;justify-content:space-between;
    padding:14px 20px;
    position:sticky;top:0;z-index:10;background:#fff;
  }
  .ob-logo{margin-bottom:0;font-size:17px}
  .ob-steps{flex-direction:row;gap:6px;flex:0}
  .ob-step{flex-direction:column;align-items:center;gap:0}
  .ob-step-rail{padding-bottom:0}
  .ob-step:not(:last-child) .ob-step-rail::after{display:none}
  .ob-step-body{display:none}
  .ob-step-num{width:24px;height:24px;font-size:11px}
  .ob-security{display:none}

  .ob-main{grid-template-columns:1fr;grid-row:2}
  .ob-desk{height:240px;order:1}
  .ob-left{order:2;padding:24px 20px}
  .ob-right{order:3;border-left:none;border-top:1px solid #F0F0F0}
  .ob-trust{grid-column:1;order:4}

  .ann-desk{bottom:20%;left:8%}
  .ann-inbox{bottom:40%;left:38%}
  .ann-trust{bottom:20%;right:6%}
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
        <div class="ob-step-rail">
          <div class="ob-step-num">✓</div>
        </div>
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
          <div class="ob-step-desc">Ava starts working</div>
        </div>
      </div>
    </div>

    <div class="ob-security">
      <div class="ob-security-row">
        <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>We never store your password.<br>You're always in control.</p>
    </div>
  </aside>

  {{-- ══ MAIN ══ --}}
  <div class="ob-main">

    {{-- Left: content --}}
    <div class="ob-left">
      <div class="ob-step-eyebrow">
        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Step 2 of 5
      </div>

      <h1 class="ob-h1">Every worker needs a workspace.</h1>

      <p class="ob-sub">
        Ava works inside your Gmail.<br>
        Give her access so she can begin<br>
        monitoring renewals and preparing<br>
        drafts for you.
      </p>

      <a href="{{ route('ava.gmail.authorize') }}" class="btn-gmail">
        <div class="btn-gmail-icon">
          {{-- Google G --}}
          <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
        </div>
        <div>
          <span class="btn-gmail-text-main">Set Up Workspace</span>
          <span class="btn-gmail-text-sub">Connect Gmail</span>
        </div>
      </a>

      <div class="ob-secure-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span><strong>Secure OAuth 2.0</strong> &mdash; We never see or store your password.</span>
      </div>
    </div>

    {{-- Center: desk scene --}}
    <div class="ob-desk">
      <img class="ob-desk-img"
           src="/images/ava-desk.png"
           alt="Ava's workspace"
           onerror="this.style.display='none'">

      {{-- Annotations --}}
      <div class="ob-annotation ann-desk">
        <svg viewBox="0 0 40 40"><path d="M10 10 Q15 25 28 30" stroke-linecap="round" stroke-width="1.5"/><polygon points="26,34 30,26 34,32" fill="rgba(255,255,255,.6)"/></svg>
        Her desk
      </div>
      <div class="ob-annotation ann-inbox">
        <svg viewBox="0 0 40 40"><path d="M30 8 Q25 22 12 28" stroke-linecap="round" stroke-width="1.5"/><polygon points="14,32 10,24 6,30" fill="rgba(255,255,255,.6)"/></svg>
        Her inbox
      </div>
      <div class="ob-annotation ann-trust">
        <svg viewBox="0 0 40 40"><path d="M8 8 Q15 24 28 28" stroke-linecap="round" stroke-width="1.5"/><polygon points="26,32 30,24 34,30" fill="rgba(255,255,255,.6)"/></svg>
        Your trust
      </div>
    </div>

    {{-- Right: status --}}
    <div class="ob-right">
      <div class="ob-status-card">
        <div class="ob-status-eyebrow">Employee Status</div>
        <div class="ob-emp-row">
          <div class="ob-emp-avatar">A</div>
          <div>
            <div class="ob-emp-name">AVA</div>
            <div class="ob-emp-role">Renewal Specialist</div>
          </div>
        </div>
        <hr class="ob-status-divider">
        <div class="ob-status-row">
          <span class="ob-status-key">Workspace</span>
          <span class="ob-status-val val-preparing">
            <span class="ob-status-dot dot-preparing"></span>
            Preparing...
          </span>
        </div>
        <div class="ob-status-row">
          <span class="ob-status-key">Access</span>
          <span class="ob-status-val val-not-connected">
            <span class="ob-status-dot dot-not-connected"></span>
            Not connected
          </span>
        </div>
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

  {{-- ══ BOTTOM TRUST BAR ══ --}}
  <div class="ob-trust">
    <div class="ob-trust-main">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      Trusted by 2,847+ businesses worldwide
    </div>
    <div class="ob-trust-divider"></div>
    <div class="ob-trust-logos">
      <div class="ob-trust-logo">
        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Capterra
      </div>
      <div class="ob-trust-logo">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
        Google
      </div>
      <div class="ob-trust-logo">
        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Trustpilot
      </div>
      <div class="ob-trust-logo">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/></svg>
        GetApp
      </div>
    </div>
  </div>

</div>

</body>
</html>

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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Caveat:wght@500;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{font-family:'Inter',sans-serif;background:#F4F3F1;color:#0D0D0D;-webkit-font-smoothing:antialiased}

/* ══════════════════════════════
   PAGE SHELL
   sidebar | content area
   content area stacks: main-row / trust-bar
══════════════════════════════ */
.ob-page{
  display:grid;
  grid-template-columns:260px 1fr;
  grid-template-rows:1fr auto;
  height:100vh;
  overflow:hidden;
}

/* ══════════════════════════════
   SIDEBAR — identical to step-1
══════════════════════════════ */
.ob-sidebar{
  grid-row:1/3;
  background:#F4F3F1;
  display:flex;flex-direction:column;
  padding:32px 24px;
  overflow-y:auto;
  border-right:1px solid #E8E7E4;
}
.ob-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;margin-bottom:44px}

.ob-steps{display:flex;flex-direction:column;flex:1}
.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative}
.ob-step:not(:last-child) .ob-step-rail::after{
  content:'';position:absolute;left:13px;top:30px;
  width:2px;height:calc(100% - 6px);background:#DCDCDC;border-radius:2px;
}
.ob-step.done:not(:last-child) .ob-step-rail::after{background:#0D0D0D}
.ob-step-rail{position:relative;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding-bottom:32px}
.ob-step:last-child .ob-step-rail{padding-bottom:0}
.ob-step-num{
  width:28px;height:28px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:800;position:relative;z-index:1;flex-shrink:0;
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
   MAIN ROW — 3 columns
   left panel | desk image | right panel
══════════════════════════════ */
.ob-main{
  display:grid;
  grid-template-columns:300px 1fr 240px;
  overflow:hidden;
}

/* ── LEFT PANEL ── */
.ob-left{
  background:#fff;
  border-right:1px solid #F0F0F0;
  padding:40px 28px;
  display:flex;flex-direction:column;justify-content:center;
  overflow-y:auto;
}

.ob-eyebrow{
  display:flex;align-items:center;gap:8px;
  font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
  color:#9CA3AF;margin-bottom:18px;
}
.ob-eyebrow svg{width:14px;height:14px;fill:#F5C518;stroke:none;flex-shrink:0}

.ob-h1{
  font-size:clamp(1.55rem,1.9vw,2rem);
  font-weight:900;letter-spacing:-.04em;line-height:1.08;
  color:#0D0D0D;margin-bottom:14px;
}
.ob-sub{
  font-size:13.5px;color:#6B7280;line-height:1.75;
  margin-bottom:28px;
}

/* Gmail button */
.btn-gmail{
  display:flex;align-items:center;gap:14px;
  padding:14px 18px;border-radius:14px;
  background:#0D0D0D;color:#fff;
  border:none;cursor:pointer;width:100%;text-align:left;
  text-decoration:none;
  transition:opacity .15s,transform .1s;
  margin-bottom:16px;
}
.btn-gmail:hover{opacity:.88;transform:translateY(-1px)}
.btn-gmail-icon{
  width:36px;height:36px;border-radius:8px;background:#fff;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.btn-gmail-label{display:flex;flex-direction:column;gap:2px}
.btn-gmail-main{font-size:14.5px;font-weight:800;letter-spacing:-.01em;color:#fff}
.btn-gmail-sub{font-size:11px;color:rgba(255,255,255,.55)}

.ob-lock-note{
  display:flex;align-items:flex-start;gap:8px;
  font-size:12px;color:#9CA3AF;line-height:1.5;
}
.ob-lock-note svg{width:13px;height:13px;stroke:#C4C4C4;flex-shrink:0;margin-top:1px}
.ob-lock-note strong{color:#6B7280;font-weight:600}

/* ── CENTER: DESK IMAGE ── */
.ob-desk{
  position:relative;overflow:hidden;
  background:#1c1a17;
}
.ob-desk-img{
  position:absolute;inset:0;width:100%;height:100%;
  object-fit:cover;object-position:center center;
}
/* Annotations overlay */
.ob-annotations{
  position:absolute;inset:0;z-index:2;pointer-events:none;
}
.ann{
  position:absolute;
  font-family:'Caveat',cursive;
  font-size:18px;font-weight:600;
  color:rgba(20,18,14,.75);
  display:flex;align-items:center;gap:4px;
}
.ann svg{width:28px;height:28px;fill:none;stroke:rgba(20,18,14,.5);stroke-width:1.5;flex-shrink:0}
.ann-desk { bottom:32%; left:8% }
.ann-inbox{ top:28%;  left:44% }
.ann-trust{ bottom:32%; right:6% }

/* ── RIGHT PANEL ── */
.ob-right{
  background:#F9F9F8;
  border-left:1px solid #F0F0F0;
  padding:28px 20px;
  display:flex;flex-direction:column;gap:0;
  overflow-y:auto;
}

.ob-right-eyebrow{
  font-size:8.5px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;
  color:#9CA3AF;margin-bottom:12px;
}

/* AVA employee card */
.ob-emp-card{
  background:#fff;border:1px solid #F0F0F0;border-radius:12px;
  padding:14px;margin-bottom:16px;
  display:flex;align-items:center;gap:10px;
}
.ob-emp-avatar{
  width:38px;height:38px;border-radius:8px;
  background:#0D0D0D;color:#F5C518;
  display:flex;align-items:center;justify-content:center;
  font-size:16px;font-weight:900;flex-shrink:0;
}
.ob-emp-name{font-size:15px;font-weight:900;letter-spacing:-.03em;color:#0D0D0D;line-height:1}
.ob-emp-role{font-size:11px;color:#9CA3AF;margin-top:3px}

/* Status rows */
.ob-status-section{
  background:#fff;border:1px solid #F0F0F0;border-radius:12px;
  padding:14px;margin-bottom:16px;
}
.ob-status-section-label{
  font-size:8.5px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;
  color:#9CA3AF;margin-bottom:10px;
}
.ob-status-row{
  display:flex;align-items:center;justify-content:space-between;
  padding:6px 0;
}
.ob-status-row+.ob-status-row{border-top:1px solid #F9FAFB}
.ob-status-key{font-size:12px;color:#9CA3AF}
.ob-status-val{font-size:12px;font-weight:700;display:flex;align-items:center;gap:5px}
.dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.dot-amber{background:#F59E0B}
.dot-grey{background:#D1D5DB}
.val-amber{color:#D97706}
.val-grey{color:#9CA3AF}

/* Safety card */
.ob-safe-card{
  background:#fff;border:1px solid #F0F0F0;border-radius:12px;
  padding:14px;margin-top:auto;
  display:flex;align-items:flex-start;gap:10px;
}
.ob-safe-icon{
  width:32px;height:32px;border-radius:8px;
  background:#F0FDF4;border:1px solid #DCFCE7;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.ob-safe-icon svg{width:15px;height:15px;stroke:#16a34a;stroke-width:2}
.ob-safe-title{
  font-size:8.5px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;
  color:#16a34a;margin-bottom:4px;
}
.ob-safe-body{font-size:11px;color:#6B7280;line-height:1.6}

/* ══ TRUST BAR ══ */
.ob-trust{
  grid-column:2;
  background:#fff;border-top:1px solid #EBEBEB;
  padding:11px 28px;
  display:flex;align-items:center;gap:24px;
  overflow:hidden;
}
.ob-trust-shield{display:flex;align-items:center;gap:7px;white-space:nowrap;flex-shrink:0}
.ob-trust-shield svg{width:13px;height:13px;stroke:#C4C4C4;flex-shrink:0}
.ob-trust-shield span{font-size:11.5px;color:#9CA3AF}
.ob-trust-sep{width:1px;height:14px;background:#E5E7EB;flex-shrink:0}
.ob-trust-logos{display:flex;align-items:center;gap:22px;flex:1}
.ob-trust-logo{
  font-size:12px;font-weight:700;color:#C4C4C4;
  display:flex;align-items:center;gap:5px;white-space:nowrap;
}
.ob-trust-logo svg{width:12px;height:12px;fill:#C4C4C4}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow:auto;height:auto}
  .ob-page{grid-template-columns:1fr;grid-template-rows:auto 1fr auto;height:auto}

  .ob-sidebar{
    grid-row:1;border-right:none;border-bottom:1px solid #E8E7E4;
    flex-direction:row;align-items:center;justify-content:space-between;
    padding:14px 20px;position:sticky;top:0;z-index:10;background:#fff;
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
  .ob-desk{height:260px;order:1}
  .ob-left{order:2;padding:24px 20px;border-right:none;border-top:1px solid #F0F0F0}
  .ob-right{order:3;border-left:none;border-top:1px solid #F0F0F0}
  .ob-trust{grid-column:1;order:4}

  .ann-desk{bottom:15%;left:6%;font-size:15px}
  .ann-inbox{top:20%;left:36%;font-size:15px}
  .ann-trust{bottom:15%;right:4%;font-size:15px}
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

  {{-- ══ MAIN ROW ══ --}}
  <div class="ob-main">

    {{-- LEFT: text + action --}}
    <div class="ob-left">
      <div class="ob-eyebrow">
        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Step 2 of 5
      </div>

      <h1 class="ob-h1">Every worker needs<br>a workspace.</h1>

      <p class="ob-sub">
        Ava works inside your Gmail.<br>
        Give her access so she can begin<br>
        monitoring renewals and preparing<br>
        drafts for you.
      </p>

      <a href="{{ route('ava.gmail.authorize') }}" class="btn-gmail">
        <div class="btn-gmail-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
        </div>
        <div class="btn-gmail-label">
          <span class="btn-gmail-main">Set Up Workspace</span>
          <span class="btn-gmail-sub">Connect Gmail</span>
        </div>
      </a>

      <div class="ob-lock-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span><strong>Secure OAuth 2.0</strong><br>We never see or store your password.</span>
      </div>
    </div>

    {{-- CENTER: desk image with annotations --}}
    <div class="ob-desk">
      <img class="ob-desk-img"
           src="/images/ava-desk.png"
           alt="Ava's desk"
           onerror="this.style.opacity='.15'">

      <div class="ob-annotations">
        <div class="ann ann-desk">
          {{-- curved arrow pointing right-down --}}
          <svg viewBox="0 0 40 40"><path d="M8 8 Q10 28 32 32" stroke-linecap="round"/><path d="M28 36 L32 28 L36 34" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Her desk
        </div>
        <div class="ann ann-inbox">
          <svg viewBox="0 0 40 40"><path d="M32 8 Q30 28 8 32" stroke-linecap="round"/><path d="M4 30 L10 28 L8 34" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Her inbox
        </div>
        <div class="ann ann-trust">
          <svg viewBox="0 0 40 40"><path d="M8 8 Q10 28 32 32" stroke-linecap="round"/><path d="M28 36 L32 28 L36 34" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Your trust
        </div>
      </div>
    </div>

    {{-- RIGHT: employee status --}}
    <div class="ob-right">

      <div class="ob-right-eyebrow">Employee Status</div>

      <div class="ob-emp-card">
        <div class="ob-emp-avatar">A</div>
        <div>
          <div class="ob-emp-name">AVA</div>
          <div class="ob-emp-role">Renewal Specialist</div>
        </div>
      </div>

      <div class="ob-status-section">
        <div class="ob-status-section-label">Workspace Setup</div>
        <div class="ob-status-row">
          <span class="ob-status-key">Workspace</span>
          <span class="ob-status-val val-amber"><span class="dot dot-amber"></span>Preparing...</span>
        </div>
        <div class="ob-status-row">
          <span class="ob-status-key">Access</span>
          <span class="ob-status-val val-grey"><span class="dot dot-grey"></span>Not connected</span>
        </div>
      </div>

      <div class="ob-safe-card">
        <div class="ob-safe-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
          <div class="ob-safe-title">Your Data Is Safe</div>
          <div class="ob-safe-body">Ava only accesses the data you allow. You can revoke access at any time.</div>
        </div>
      </div>

    </div>
  </div>

  {{-- ══ TRUST BAR ══ --}}
  <div class="ob-trust">
    <div class="ob-trust-shield">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      <span>Trusted by 2,847+ businesses worldwide</span>
    </div>
    <div class="ob-trust-sep"></div>
    <div class="ob-trust-logos">
      <div class="ob-trust-logo">
        <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        Capterra
      </div>
      <div class="ob-trust-logo">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></svg>
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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>UNIT — AI Workers That Never Stop Showing Up</title>
<meta name="description" content="AVA, DOX, MOX, and NUX are your AI workforce. Each one built for a specific job, running 24/7 while you focus on growth.">
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
img{display:block;max-width:100%}
a{text-decoration:none;color:inherit}
button{cursor:pointer;font-family:inherit;border:none;background:none}
ul{list-style:none}

:root{
  --brand:      #4C1D95;
  --brand-dark: #3B1578;
  --brand-soft: rgba(107,43,242,0.08);

  --ava:  #4C1D95;
  --dox:  #111111;
  --mox:  #111111;
  --nux:  #111111;
  --worker-icon-bg: rgba(0,0,0,.07);

  --text:   #0D0D0D;
  --t2:     #374151;
  --t3:     #6B7280;
  --t4:     #9CA3AF;
  --border: #E5E7EB;
  --bg:     #FFFFFF;
  --soft:   #F8F8F6;

  --font-h: 'Inter', sans-serif;
  --font-b: 'Inter', sans-serif;
  --max:    1160px;
  --pad:    clamp(20px,5vw,48px);
}

body{
  font-family:var(--font-b);
  color:var(--text);
  background:var(--bg);
  -webkit-font-smoothing:antialiased;
  overflow-x:hidden;
}

.w{ max-width:var(--max); margin:0 auto; padding:0 var(--pad); }

/* ── NAV ── */
.nav{
  position:fixed;top:0;left:0;right:0;z-index:100;
  background:rgba(255,255,255,0.92);
  backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);
}
.nav-i{
  display:flex;align-items:center;justify-content:space-between;
  height:62px;
}
.logo{display:flex;align-items:center}
.logo-name{
  font-family:var(--font-h);font-size:1.5rem;font-weight:800;
  color:var(--text);letter-spacing:-.5px;
}
.nav-links{display:flex;align-items:center;gap:28px}
.nav-links a{
  font-size:14px;font-weight:500;color:var(--t2);
  transition:color .15s;
}
.nav-links a:hover{color:var(--text)}
.nav-acts{display:flex;align-items:center;gap:10px}
.btn-login{
  padding:8px 18px;border-radius:8px;font-size:14px;font-weight:600;
  color:var(--t2);border:1px solid var(--border);
  transition:all .15s;
}
.btn-login:hover{border-color:#bbb;color:var(--text)}
.btn-cta{
  padding:10px 22px;border-radius:99px;font-size:14px;font-weight:700;
  background:var(--brand);color:#fff;
  display:inline-flex;align-items:center;gap:6px;
  transition:opacity .15s,transform .15s,box-shadow .15s;
  box-shadow:0 2px 12px rgba(107,43,242,0.3);
}
.btn-cta:hover{opacity:.9;transform:translateY(-1px);box-shadow:0 6px 20px rgba(107,43,242,0.35)}
.ham{display:none;flex-direction:column;gap:5px;padding:4px}
.ham span{display:block;width:22px;height:2px;background:var(--text);border-radius:2px}

/* ── HERO ── */
.hero{
  padding-top:62px;
  background:#fff;
  min-height:100vh;
  display:grid;
  grid-template-columns:1fr 1fr;
  overflow:hidden;
}
/* Left column: align text to same x as nav logo.
   .w uses max-width:1160px + padding:var(--pad) centered.
   So logo x = max(var(--pad), (100vw - 1160px)/2 + var(--pad)).
   Mirror that with CSS max(). */
.hero-left{
  display:flex;align-items:center;
  padding-top:clamp(48px,6vw,80px);
  padding-bottom:clamp(48px,6vw,80px);
  padding-right:clamp(32px,4vw,56px);
  padding-left:max(var(--pad), calc((100vw - var(--max)) / 2 + var(--pad)));
}
.hero-left-inner{max-width:520px}
.hero-h{
  font-family:var(--font-h);
  font-size:clamp(2rem,3.8vw,3rem);
  font-weight:800;line-height:1.12;
  letter-spacing:-.03em;
  color:var(--text);
  margin-bottom:20px;
}
.hero-h em{font-style:normal;color:var(--brand)}
.hero-p{
  font-size:clamp(.95rem,1.3vw,1.05rem);
  color:var(--t2);line-height:1.75;
  margin-bottom:32px;
}
.hero-btns{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:36px}
.btn-hero{
  padding:13px 26px;border-radius:99px;font-size:15px;font-weight:700;
  background:var(--brand);color:#fff;
  display:inline-flex;align-items:center;gap:7px;
  box-shadow:0 4px 20px rgba(107,43,242,0.38);
  transition:opacity .15s,transform .15s,box-shadow .15s;
}
.btn-hero:hover{opacity:.9;transform:translateY(-2px);box-shadow:0 10px 28px rgba(107,43,242,0.42)}
.btn-hero-ghost{
  padding:12px 22px;border-radius:99px;font-size:15px;font-weight:600;
  color:var(--t2);border:1.5px solid var(--border);
  display:inline-flex;align-items:center;gap:7px;
  transition:all .15s;
}
.btn-hero-ghost:hover{border-color:#aaa;color:var(--text)}
.hero-proof{display:flex;align-items:center;gap:12px}
.proof-avs{display:flex}
.proof-avs img{
  width:34px;height:34px;border-radius:50%;
  border:2px solid #fff;margin-left:-8px;
  outline:1.5px solid #d1d5db;
  object-fit:cover;object-position:center top;
  flex-shrink:0;
  box-shadow:0 1px 4px rgba(0,0,0,.12);
}
.proof-avs img:first-child{margin-left:0}
.proof-txt{font-size:13px;color:var(--t3);line-height:1.5}
.proof-txt strong{color:var(--text);display:block}
/* Hero image — bleeds to right edge, crossfade between two images */
.hero-right{
  position:relative;
  overflow:hidden;
  background:#000;
}
.hero-slide{
  position:absolute;inset:0;
  width:100%;height:100%;
  object-fit:cover;
  object-position:center top;
  display:block;
  transition:opacity 1.2s ease-in-out;
}
.hero-slide.active{ opacity:1; z-index:1; }
.hero-slide.hidden{ opacity:0; z-index:0; }
/* Spacer so container has height when children are absolute */
.hero-right-spacer{ display:block; width:100%; min-height:calc(100vh - 62px); }
.hero-fade{
  position:absolute;inset:0;
  z-index:3;
  background:linear-gradient(to right,#ffffff 0%,rgba(255,255,255,.55) 15%,transparent 35%);
  pointer-events:none;
}
.hero-badge{
  position:absolute;bottom:28px;right:28px;
  z-index:3;
  background:#fff;
  border:1px solid var(--border);
  border-radius:16px;
  padding:13px 16px;
  display:flex;align-items:center;gap:10px;
  box-shadow:0 4px 20px rgba(0,0,0,0.1);
}
.badge-star{
  width:36px;height:36px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
}
.badge-txt{font-size:13px;font-weight:700;color:var(--text);line-height:1.45}
.badge-txt span{color:var(--t3);font-weight:400;font-size:12px}

/* ── TRUST BAR ── */
.trust{
  border-top:1px solid var(--border);
  border-bottom:1px solid var(--border);
  padding:22px 0;background:var(--soft);
}
.trust-i{
  display:flex;align-items:center;justify-content:center;
  gap:clamp(20px,4vw,52px);flex-wrap:wrap;
}
.trust-lbl{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t4)}
.trust-items{display:flex;align-items:center;gap:clamp(16px,3vw,40px);flex-wrap:wrap}
.trust-item{display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--t3)}
.stars{color:#111;font-size:10px;letter-spacing:1px}

/* ── SECTION ATOMS ── */
.sec{padding:clamp(60px,8vw,100px) 0}
.sec-eye{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--brand);margin-bottom:12px}
.sec-h{
  font-family:var(--font-h);
  font-size:clamp(1.7rem,3.2vw,2.6rem);
  font-weight:800;line-height:1.12;letter-spacing:-.03em;
  color:var(--text);margin-bottom:14px;
}
.sec-p{font-size:1rem;color:var(--t3);line-height:1.7;max-width:520px}
.center{text-align:center}
.center .sec-p{margin:0 auto}

/* ── WORKER CARDS ── */
.workers{background:var(--soft)}
.wk-grid{
  display:grid;grid-template-columns:repeat(4,1fr);
  gap:16px;
}
.wk-card{
  background:#fff;
  border:1px solid var(--border);
  border-radius:20px;
  overflow:hidden;
  display:flex;flex-direction:column;
  position:relative;
  min-height:280px;
  transition:transform .2s,box-shadow .2s;
}
.wk-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,0.08)}

/* Character image — absolute, right side, fills card height */
.wk-img-bg{
  position:absolute;
  right:0;top:0;bottom:0;
  width:55%;
  pointer-events:none;
}
.wk-img-bg img{
  width:100%;height:100%;
  object-fit:cover;
  object-position:center top;
  display:block;
  transform:scale(1.6);
  transform-origin:top center;
}
/* Fade so image blends into white card bg on the left */
.wk-img-bg::after{
  content:'';
  position:absolute;inset:0;
  background:linear-gradient(to right,#ffffff 0%,rgba(255,255,255,.5) 40%,transparent 75%);
}

/* Content — left side, z-index above image */
.wk-content{
  position:relative;z-index:1;
  padding:20px 18px 18px;
  display:flex;flex-direction:column;
  flex:1;
  width:65%; /* leave room for image on right */
}
/* Icon + name inline */
.wk-head{display:flex;align-items:center;gap:9px;margin-bottom:6px}
.wk-icon{
  width:34px;height:34px;border-radius:9px;
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;
}
.wk-icon svg{width:17px;height:17px}
.wk-name{
  font-family:var(--font-h);font-size:1.2rem;font-weight:800;
  letter-spacing:-.03em;line-height:1;
}
.wk-role{font-size:11px;color:var(--t3);font-weight:500;margin-bottom:12px}
.wk-quote{
  font-size:13px;color:var(--t2);line-height:1.65;
  flex:1;margin-bottom:18px;
}
/* Button flush left, auto width */
.btn-wk{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 16px;border-radius:10px;
  font-size:13px;font-weight:700;color:#fff;
  width:fit-content;white-space:nowrap;
  transition:opacity .15s,transform .1s;
}
.btn-wk:hover{opacity:.85;transform:translateY(-1px)}

/* ── TIMELINE ── */
.timeline-sec{background:var(--soft);border-bottom:1px solid var(--border)}
.tl{
  display:grid;grid-template-columns:repeat(5,1fr);
  gap:0;position:relative;margin-top:clamp(40px,5vw,64px);
}
/* connecting line */
.tl::before{
  content:'';position:absolute;
  top:32px;left:12%;right:12%;height:2px;
  background:repeating-linear-gradient(90deg,var(--border) 0,var(--border) 6px,transparent 6px,transparent 12px);
}
.tl-item{display:flex;flex-direction:column;align-items:center;text-align:center;padding:0 8px;position:relative}
/* arrow between items — shows on all except last */
.tl-item:not(:last-child)::after{
  content:'';
  position:absolute;
  top:24px;right:-10px;
  width:0;height:0;
  border-top:8px solid transparent;
  border-bottom:8px solid transparent;
  border-left:10px solid #CBD5E1;
  z-index:2;
}
.tl-node{
  width:64px;height:64px;border-radius:50%;
  background:#fff;border:1.5px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  position:relative;z-index:1;margin-bottom:18px;
  flex-shrink:0;
  box-shadow:0 2px 8px rgba(0,0,0,.06);
}
.tl-node svg{width:26px;height:26px}
.tl-time{font-size:13px;font-weight:700;letter-spacing:.03em;margin-bottom:7px}
.tl-evt{font-size:14.5px;color:var(--t3);line-height:1.6}
.tl-evt strong{color:var(--text);display:block;margin-bottom:2px;font-weight:700}
.tl-item:last-child .tl-node{background:var(--brand);border-color:var(--brand);box-shadow:0 4px 16px rgba(76,29,149,.3)}
.tl-item:last-child .tl-node svg{color:#fff!important;stroke:#fff!important}

/* ── LIFECYCLE ── */
.lifecycle{background:#fff}
/* outer card wraps the whole section */
.lc-card{
  border:1.5px solid var(--border);
  border-radius:28px;
  padding:clamp(32px,4vw,56px);
  display:grid;
  grid-template-columns:260px 1fr;
  gap:clamp(32px,5vw,56px);
  align-items:center;
}
.lc-left .sec-h{margin-bottom:14px;font-size:clamp(1.4rem,2.6vw,2rem)}
.lc-left p{font-size:.95rem;color:var(--t3);line-height:1.7;margin-bottom:24px}
.btn-outline{
  display:inline-flex;align-items:center;gap:7px;
  padding:11px 20px;border-radius:10px;
  font-size:14px;font-weight:600;color:var(--text);
  border:1px solid var(--border);
  transition:all .15s;
}
.btn-outline:hover{border-color:#999}
/* right side: photos + arrows inline */
.lc-row{
  display:flex;align-items:flex-start;gap:0;
}
.lc-photo{
  flex:1;
  border:1px solid var(--border);
  border-radius:18px;
  overflow:hidden;
  background:var(--soft);
}
.lc-photo img{
  width:100%;height:230px;
  object-fit:cover;object-position:center top;
  display:block;
}
.lc-photo-body{padding:14px 16px 16px}
.lc-photo-step{
  font-size:10.5px;font-weight:700;letter-spacing:.08em;
  text-transform:uppercase;margin-bottom:6px;
}
.lc-photo-txt{font-size:13.5px;color:var(--t2);line-height:1.55;font-weight:500}
/* arrow between lifecycle cards */
.lc-arrow{
  flex-shrink:0;
  width:36px;
  display:flex;align-items:center;justify-content:center;
  padding-bottom:80px;
  color:#9CA3AF;
  font-size:22px;
}

/* ── CTA BANNER ── */
.cta-sec{
  background:var(--brand);
  padding:clamp(52px,7vw,88px) 0;
  position:relative;overflow:hidden;
}
.cta-sec::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse 80% 80% at 50% 110%,rgba(255,255,255,.07) 0%,transparent 70%);
}
.cta-i{text-align:center;position:relative;z-index:1}
.cta-i h2{
  font-family:var(--font-h);
  font-size:clamp(1.8rem,3.8vw,2.8rem);
  font-weight:800;color:#fff;letter-spacing:-.03em;
  margin-bottom:12px;
}
.cta-i p{font-size:1rem;color:rgba(255,255,255,.65);margin-bottom:28px}
.btn-white{
  display:inline-flex;align-items:center;gap:7px;
  padding:13px 26px;border-radius:11px;
  font-size:15px;font-weight:700;
  background:#fff;color:var(--brand);
  box-shadow:0 4px 20px rgba(0,0,0,.18);
  transition:opacity .15s,transform .15s;
}
.btn-white:hover{opacity:.94;transform:translateY(-2px)}
.cta-note{margin-top:12px;font-size:13px;color:rgba(255,255,255,.4)}

/* ── FOOTER ── */
.footer{background:#0A0A0A;padding:clamp(40px,6vw,72px) 0 28px}
.ft-grid{
  display:grid;grid-template-columns:2fr 1fr 1fr 1fr;
  gap:44px;margin-bottom:44px;
}
.ft-name{
  font-family:var(--font-h);font-size:1.15rem;font-weight:800;
  color:#fff;margin-bottom:10px;
}
.ft-desc{font-size:13.5px;color:rgba(255,255,255,.3);line-height:1.7;max-width:220px;margin-bottom:20px}
.ft-col-h{
  font-size:10.5px;font-weight:700;letter-spacing:.1em;
  text-transform:uppercase;color:rgba(255,255,255,.25);
  margin-bottom:14px;
}
.ft-links{display:flex;flex-direction:column;gap:9px}
.ft-links a{font-size:13.5px;color:rgba(255,255,255,.4);transition:color .15s}
.ft-links a:hover{color:rgba(255,255,255,.8)}
.ft-bottom{
  border-top:1px solid rgba(255,255,255,.07);padding-top:24px;
  display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
}
.ft-bottom p{font-size:12.5px;color:rgba(255,255,255,.2)}

/* ── MOBILE MENU ── */
.mob-menu{
  display:none;position:fixed;inset:0;z-index:200;
  background:#fff;flex-direction:column;padding:24px var(--pad);
}
.mob-menu.open{display:flex}
.mob-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:36px}
.mob-close{font-size:22px;color:var(--t3);padding:4px}
.mob-links{display:flex;flex-direction:column}
.mob-links a{
  display:block;padding:14px 0;
  font-size:1.05rem;font-weight:600;color:var(--t2);
  border-bottom:1px solid var(--border);
  transition:color .15s;
}
.mob-links a:hover{color:var(--text)}
.mob-ctas{margin-top:28px;display:flex;flex-direction:column;gap:10px}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .wk-grid{grid-template-columns:repeat(2,1fr)}
  .wk-img-bg{width:50%}
  .ft-grid{grid-template-columns:1fr 1fr;gap:28px}
  .tl{grid-template-columns:repeat(3,1fr)}
  .lc-card{grid-template-columns:1fr;gap:28px}
  .lc-row{flex-wrap:wrap;gap:8px}
  .lc-arrow{padding-bottom:0;font-size:14px;width:16px}
}
@media(max-width:768px){
  .nav-links,.nav-acts{display:none}
  .ham{display:flex}
  .hero{grid-template-columns:1fr;min-height:auto}
  .hero-right{order:-1;min-height:300px}
  .hero-left{padding:40px var(--pad);text-align:center}
  .hero-btns{justify-content:center}
  .hero-proof{justify-content:center}
  .tl{grid-template-columns:repeat(2,1fr)}
  .ft-grid{grid-template-columns:1fr}
  .ft-bottom{flex-direction:column;text-align:center}
  .lc-photo{min-width:calc(50% - 16px)}
}
@media(max-width:480px){
  .wk-grid{grid-template-columns:1fr}
  .tl{grid-template-columns:1fr}
  .hero-btns{flex-direction:column;align-items:stretch}
  .btn-hero,.btn-hero-ghost{justify-content:center}
  .lc-photo{min-width:calc(50% - 16px)}
}
</style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <div class="w nav-i">
    <a href="/" class="logo">
      <span class="logo-name">UNIT</span>
    </a>
    <ul class="nav-links">
      <li><a href="#workers">Meet the Team</a></li>
      <li><a href="#timeline">How It Works</a></li>
      <li><a href="{{ route('marketplace') }}">For Business</a></li>
      <li><a href="{{ route('marketplace') }}">Resources</a></li>
      <li><a href="{{ route('pricing') }}">Pricing</a></li>
    </ul>
    <div class="nav-acts">
      <a href="{{ route('login') }}" class="btn-login">Log in</a>
      <a href="{{ route('register') }}" class="btn-cta">
        Hire Your First Worker
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
    <button class="ham" id="ham" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- MOBILE MENU -->
<div class="mob-menu" id="mob">
  <div class="mob-top">
    <a href="/" class="logo">
      <span class="logo-name">UNIT</span>
    </a>
    <button class="mob-close" id="mob-close">✕</button>
  </div>
  <div class="mob-links">
    <a href="#workers" onclick="closeMob()">Meet the Team</a>
    <a href="#timeline" onclick="closeMob()">How It Works</a>
    <a href="{{ route('pricing') }}" onclick="closeMob()">Pricing</a>
    <a href="{{ route('marketplace') }}" onclick="closeMob()">Marketplace</a>
  </div>
  <div class="mob-ctas">
    <a href="{{ route('login') }}" class="btn-login" style="text-align:center;padding:12px">Log in</a>
    <a href="{{ route('register') }}" class="btn-cta" style="padding:12px;justify-content:center">Hire Your First Worker →</a>
  </div>
</div>

<!-- HERO -->
<section class="hero">
  <div class="hero-left">
    <div class="hero-left-inner">
      <h1 class="hero-h">
        Meet the AI workers<br>
        that never stop<br>
        <em>showing up.</em>
      </h1>
      <p class="hero-p">
        Every UNIT worker has one job — and does it exceptionally well. They work 24/7, improve over time, and tell their own story while helping you run your business.
      </p>
      <div class="hero-btns">
        <a href="#workers" class="btn-hero">
          Meet the Team
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('register') }}" class="btn-hero-ghost">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z" fill="currentColor" stroke="none"/></svg>
          Watch Their First Day
        </a>
      </div>
      <div class="hero-proof">
        <div class="proof-avs">
          <img src="/images/ava.png" alt="user">
          <img src="/images/ava.png" alt="user">
          <img src="/images/ava.png" alt="user">
          <img src="/images/ava.png" alt="user">
        </div>
        <p class="proof-txt">
          <strong>2,847+ businesses</strong> already hired their first worker
        </p>
      </div>
    </div>
  </div>

  <div class="hero-right">
    <img src="/images/hero-team-2.png" alt="AVA, DOX, MOX and NUX — the UNIT AI workforce" class="hero-slide active" id="slide-0">
    <img src="/images/hero-team.png"   alt="AVA, DOX, MOX and NUX — selfie"                 class="hero-slide hidden" id="slide-1">
    <span class="hero-right-spacer" aria-hidden="true"></span>
    <div class="hero-fade"></div>
    <div class="hero-badge">
      <div class="badge-star">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
          <path d="M12 2l1.5 4.5H18l-3.75 2.75 1.5 4.5L12 11l-3.75 2.75 1.5-4.5L6 6.5h4.5L12 2z" fill="#F59E0B"/>
          <circle cx="12" cy="12" r="2" fill="#F59E0B" opacity=".4"/>
          <line x1="12" y1="2" x2="12" y2="0.5" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="12" y1="21.5" x2="12" y2="23.5" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="2" y1="12" x2="0.5" y2="12" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round"/>
          <line x1="21.5" y1="12" x2="23.5" y2="12" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </div>
      <div class="badge-txt">Real stories. Real work.<br>Real results.</div>
    </div>
  </div>
</section>

<!-- TRUST -->
<div class="trust">
  <div class="w trust-i">
    <span class="trust-lbl">Trusted by teams who want more done</span>
    <div class="trust-items">
      <div class="trust-item"><span class="stars">★★★★★</span> G2</div>
      <div class="trust-item"><span class="stars">★★★★★</span> Capterra</div>
      <div class="trust-item"><span class="stars">★★★★★</span> Google</div>
      <div class="trust-item"><span class="stars">★★★★★</span> Trustpilot</div>
    </div>
  </div>
</div>

<!-- WORKERS -->
<section class="workers sec" id="workers">
  <div class="w">
    <div class="center" style="margin-bottom:clamp(36px,5vw,56px)">
      <div class="sec-eye">Meet the team</div>
      <h2 class="sec-h">Four workers. Four specialties.<br>One goal: your success.</h2>
      <p class="sec-p">Each UNIT worker has one job — and does it exceptionally well. They run continuously, improve with every task, and report back on everything they do.</p>
    </div>

    <div class="wk-grid">

      <!-- AVA -->
      <div class="wk-card" style="border-top:3px solid var(--ava)">
        <div class="wk-img-bg">
          <img src="/images/ava-stand.png" alt="AVA" style="object-position:center 10%">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(107,43,242,.1)">
              <svg viewBox="0 0 24 24" fill="none" stroke="var(--ava)" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="wk-name" style="color:var(--ava)">AVA</div>
          </div>
          <div class="wk-role">Renewal Coordinator</div>
          <p class="wk-quote">I remember the renewals everyone else forgets. Every deadline. Every client. Every time.</p>
          <a href="{{ route('workers.public.show', 'ava') }}" class="btn-wk" style="background:var(--ava)">
            Watch Ava's Day
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z" fill="currentColor" stroke="none"/></svg>
          </a>
        </div>
      </div>

      <!-- DOX -->
      <div class="wk-card" style="border-top:3px solid #111">
        <div class="wk-img-bg">
          <img src="/images/ava.png" alt="DOX">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2" stroke-linecap="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            </div>
            <div class="wk-name" style="color:#111">DOX</div>
          </div>
          <div class="wk-role">Document Organizer</div>
          <p class="wk-quote">I organize the documents nobody wants to touch — so everything is exactly where you need it.</p>
          <a href="#" class="btn-wk" style="background:#111">
            Watch Dox's Day
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z" fill="currentColor" stroke="none"/></svg>
          </a>
        </div>
      </div>

      <!-- MOX -->
      <div class="wk-card" style="border-top:3px solid #111">
        <div class="wk-img-bg">
          <img src="/images/ava.png" alt="MOX">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </div>
            <div class="wk-name" style="color:#111">MOX</div>
          </div>
          <div class="wk-role">Brand Scout</div>
          <p class="wk-quote">I search the world for moments your brand shouldn't miss — and surface them before you even ask.</p>
          <a href="#" class="btn-wk" style="background:#111">
            Watch Mox's Day
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z" fill="currentColor" stroke="none"/></svg>
          </a>
        </div>
      </div>

      <!-- NUX -->
      <div class="wk-card" style="border-top:3px solid #111">
        <div class="wk-img-bg">
          <img src="/images/ava.png" alt="NUX">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <div class="wk-name" style="color:#111">NUX</div>
          </div>
          <div class="wk-role">Content Creator</div>
          <p class="wk-quote">I turn one idea into content people actually see — across every channel, every format, every time.</p>
          <a href="#" class="btn-wk" style="background:#111">
            Watch Nux's Day
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z" fill="currentColor" stroke="none"/></svg>
          </a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- TIMELINE -->
<section class="timeline-sec sec" id="timeline">
  <div class="w">
    <div class="center">
      <div class="sec-eye">A day inside UNIT</div>
      <h2 class="sec-h">While you focus on growth,<br>they handle everything else.</h2>
    </div>
    <div class="tl">
      <div class="tl-item">
        <div class="tl-node" style="border-color:rgba(107,43,242,.3)">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--ava)" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        </div>
        <div class="tl-time" style="color:var(--ava)">8:00 AM</div>
        <div class="tl-evt"><strong>AVA</strong>finishes three renewals.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node" style="border-color:rgba(0,0,0,.2)">
          <svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.8" stroke-linecap="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
        </div>
        <div class="tl-time" style="color:#111">9:30 AM</div>
        <div class="tl-evt"><strong>DOX</strong>organizes 1,247 files.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node" style="border-color:rgba(0,0,0,.2)">
          <svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </div>
        <div class="tl-time" style="color:#111">11:00 AM</div>
        <div class="tl-evt"><strong>MOX</strong>discovers National Coffee Day.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node" style="border-color:rgba(0,0,0,.2)">
          <svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.8" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        </div>
        <div class="tl-time" style="color:#111">2:00 PM</div>
        <div class="tl-evt"><strong>NUX</strong>publishes six campaigns.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="tl-time" style="color:var(--brand)">5:00 PM</div>
        <div class="tl-evt"><strong>You arrive.</strong>Everything is already done.</div>
      </div>
    </div>
  </div>
</section>

<!-- LIFECYCLE -->
<section class="lifecycle sec">
  <div class="w">
    <div class="lc-card">
      <div class="lc-left">
        <div class="sec-eye">Every worker has a life</div>
        <h2 class="sec-h">They wake up. They receive work. They improve. They write about their day.</h2>
        <p>They're not just tools. They're consistent, reliable, and always getting better.</p>
        <a href="{{ route('register') }}" class="btn-outline">
          See Inside Their World
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
      <div class="lc-row">
        <div class="lc-photo">
          <img src="/images/ava.png" alt="Wake up">
          <div class="lc-photo-body">
            <div class="lc-photo-step" style="color:var(--brand)">1. Wake Up</div>
            <div class="lc-photo-txt">Ready for the day at the desk.</div>
          </div>
        </div>
        <div class="lc-arrow">→</div>
        <div class="lc-photo">
          <img src="/images/ava.png" alt="Receive work">
          <div class="lc-photo-body">
            <div class="lc-photo-step" style="color:#111">2. Receive Work</div>
            <div class="lc-photo-txt">New tasks. New opportunities.</div>
          </div>
        </div>
        <div class="lc-arrow">→</div>
        <div class="lc-photo">
          <img src="/images/ava.png" alt="Do the work">
          <div class="lc-photo-body">
            <div class="lc-photo-step" style="color:#111">3. Do the Work</div>
            <div class="lc-photo-txt">Focus. Execute. Deliver results.</div>
          </div>
        </div>
        <div class="lc-arrow">→</div>
        <div class="lc-photo">
          <img src="/images/ava.png" alt="Write their diary">
          <div class="lc-photo-body">
            <div class="lc-photo-step" style="color:#111">4. Write Their Diary</div>
            <div class="lc-photo-txt">Reflect, learn, get better tomorrow.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-sec">
  <div class="w cta-i">
    <h2>Hire your first worker today.</h2>
    <p>Start with one. Add more as you grow.</p>
    <a href="{{ route('register') }}" class="btn-white">
      Hire Your First Worker
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
    <p class="cta-note">No credit card required.</p>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="w">
    <div class="ft-grid">
      <div>
        <div class="ft-name">UNIT</div>
        <p class="ft-desc">A platform for deploying purpose-built AI workers. Each worker is trained for a specific workflow and runs on your team.</p>
      </div>
      <div>
        <div class="ft-col-h">Workers</div>
        <div class="ft-links">
          <a href="{{ route('workers.public.show', 'ava') }}">AVA — Renewal Coordinator</a>
          <a href="{{ route('marketplace') }}">All Workers</a>
          <a href="{{ route('referral.index') }}">Refer &amp; Earn</a>
        </div>
      </div>
      <div>
        <div class="ft-col-h">Product</div>
        <div class="ft-links">
          <a href="#timeline">How It Works</a>
          <a href="{{ route('marketplace') }}">Marketplace</a>
          <a href="{{ route('pricing') }}">Pricing</a>
          <a href="{{ route('register') }}">Sign Up Free</a>
        </div>
      </div>
      <div>
        <div class="ft-col-h">Legal</div>
        <div class="ft-links">
          <a href="/privacy">Privacy Policy</a>
          <a href="/terms">Terms of Use</a>
        </div>
      </div>
    </div>
    <div class="ft-bottom">
      <p>© {{ date('Y') }} UNIT. All rights reserved.</p>
      <p>Built to work while you don't.</p>
    </div>
  </div>
</footer>

<script>
const ham = document.getElementById('ham');
const mob = document.getElementById('mob');
const mobClose = document.getElementById('mob-close');
ham.addEventListener('click', () => mob.classList.add('open'));
mobClose.addEventListener('click', () => mob.classList.remove('open'));
function closeMob(){ mob.classList.remove('open') }

// Hero image crossfade
(function(){
  const slides = document.querySelectorAll('.hero-slide');
  let cur = 0;
  setInterval(function(){
    slides[cur].classList.remove('active');
    slides[cur].classList.add('hidden');
    cur = (cur + 1) % slides.length;
    slides[cur].classList.remove('hidden');
    slides[cur].classList.add('active');
  }, 5000);
})();

document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const t = document.querySelector(a.getAttribute('href'));
    if(t){ e.preventDefault(); t.scrollIntoView({behavior:'smooth',block:'start'}) }
  });
});
</script>
</body>
</html>

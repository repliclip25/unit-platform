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
<link rel="stylesheet" href="/css/unit-public.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
img{display:block;max-width:100%}
a{text-decoration:none;color:inherit}
button{cursor:pointer;font-family:inherit;border:none;background:none}
ul{list-style:none}

:root{
  --brand:      #F5C518;
  --brand-dark: #0D0D0D;
  --brand-soft: rgba(245,197,24,0.08);

  --ava:  #0D0D0D;
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

/* ── DARK THEME ── */
[data-theme="dark"]{
  --text:   #F3F4F6;
  --t2:     #D1D5DB;
  --t3:     #9CA3AF;
  --t4:     #6B7280;
  --border: #2D2D2D;
  --bg:     #0D0D0D;
  --soft:   #161616;
  --brand:  #F5C518;
}
[data-theme="dark"] .nav{background:rgba(13,13,13,.92);border-color:#2D2D2D}
[data-theme="dark"] .lc-card,[data-theme="dark"] .lc-photo{background:#161616}
[data-theme="dark"] .lc-photo-txt{color:#D1D5DB}
[data-theme="dark"] .lc-left p{color:#9CA3AF}
[data-theme="dark"] .btn-outline{color:#E5E7EB;border-color:#3D3D3D}
.wk-card{background:#fff!important}
[data-theme="dark"] .lc-photo-body{background:#161616}
[data-theme="dark"] .cta-card{background:#1E1333}
[data-theme="dark"] .cta-text h2{color:#F3F4F6}
[data-theme="dark"] .trust-bar{background:#0D0D0D}
[data-theme="dark"] .trust-lbl2{color:#F3F4F6}
[data-theme="dark"] .trust-score-txt{color:#D1D5DB}
[data-theme="dark"] .trust-platform-name{color:#F3F4F6}
[data-theme="dark"] .trust-stars2{color:#F59E0B}
[data-theme="dark"] .trust-platform-stars{color:#F59E0B}
[data-theme="dark"] .features{background:#0D0D0D}
[data-theme="dark"] .feat-body h4{color:#F3F4F6}
[data-theme="dark"] .lifecycle{background:#0D0D0D}
[data-theme="dark"] .workers{background:#0D0D0D}
[data-theme="dark"] .hero{background:#0D0D0D}
[data-theme="dark"] .hero-fade{background:linear-gradient(to right,#0D0D0D 0%,rgba(13,13,13,.7) 20%,transparent 45%)}
[data-theme="dark"] .hero-left{color:#F3F4F6}
[data-theme="dark"] .btn-login{color:#D1D5DB;border-color:#2D2D2D}
[data-theme="dark"] .btn-hero-ghost{color:#D1D5DB;border-color:#3D3D3D}
[data-theme="dark"] .btn-outline{color:#D1D5DB;border-color:#3D3D3D}
[data-theme="dark"] .timeline-sec{background:#0D0D0D}
[data-theme="dark"] .tl::before{background:repeating-linear-gradient(90deg,#3D4451 0,#3D4451 6px,transparent 6px,transparent 12px)}
[data-theme="dark"] .tl-item:not(:last-child)::after{border-left-color:#4B5563}
[data-theme="dark"] .tl-node{background:#fff;border-color:#D1D5DB}
[data-theme="dark"] .tl-node svg{stroke:#111}
[data-theme="dark"] .tl-evt{color:#9CA3AF}
[data-theme="dark"] .tl-evt strong{color:#F3F4F6}
[data-theme="dark"] .trust-bar{border-top:1px solid #2D2D2D}

/* theme toggle button */
.theme-toggle{
  width:36px;height:36px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  border:1px solid var(--border);background:transparent;
  color:var(--t2);cursor:pointer;
  transition:all .2s;flex-shrink:0;
}
.theme-toggle:hover{background:var(--soft);color:var(--text)}
.theme-toggle svg{width:17px;height:17px}
.icon-sun{display:none}
.icon-moon{display:block}
[data-theme="dark"] .icon-sun{display:block}
[data-theme="dark"] .icon-moon{display:none}

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
  background:#0D0D0D;color:#fff;
  display:inline-flex;align-items:center;gap:6px;
  transition:opacity .15s,transform .15s,box-shadow .15s;
  box-shadow:0 2px 12px rgba(0,0,0,0.15);
}
.btn-cta:hover{opacity:.9;transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,0,0,0.15)}
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
.hero-h em{font-style:normal;position:relative;display:inline}
.hero-h em::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}
.hero-p{
  font-size:clamp(.95rem,1.3vw,1.05rem);
  color:var(--t2);line-height:1.75;
  margin-bottom:32px;
}
.hero-btns{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:36px}
.btn-hero{
  padding:13px 26px;border-radius:99px;font-size:15px;font-weight:700;
  background:#0D0D0D;color:#fff;
  display:inline-flex;align-items:center;gap:7px;
  box-shadow:0 4px 20px rgba(0,0,0,0.15);
  transition:opacity .15s,transform .15s,box-shadow .15s;
}
.btn-hero:hover{opacity:.9;transform:translateY(-2px);box-shadow:0 10px 28px rgba(0,0,0,0.2)}
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
/* ── ACTIVITY FEED TICKER ── */
.activity-feed{
  background:#0A0A0A;
  border-top:1px solid #1F1F1F;
  border-bottom:1px solid #1F1F1F;
  padding:0;overflow:hidden;position:relative;
}
.activity-feed::before,.activity-feed::after{
  content:'';position:absolute;top:0;bottom:0;width:80px;z-index:2;pointer-events:none;
}
.activity-feed::before{left:0;background:linear-gradient(to right,#0A0A0A,transparent)}
.activity-feed::after{right:0;background:linear-gradient(to left,#0A0A0A,transparent)}
.feed-track{
  display:flex;align-items:center;gap:0;
  width:max-content;
  animation:feedScroll 70s linear infinite;
}
.feed-track:hover{animation-play-state:paused}
@keyframes feedScroll{
  0%{transform:translateX(0)}
  100%{transform:translateX(-50%)}
}
.feed-item{
  display:flex;align-items:center;gap:12px;
  padding:18px 40px;
  border-right:1px solid #1F1F1F;
  white-space:nowrap;flex-shrink:0;
}
.feed-dot{
  width:8px;height:8px;border-radius:50%;flex-shrink:0;
  position:relative;
}
.feed-dot::after{
  content:'';
  position:absolute;
  inset:-4px;
  border-radius:50%;
  opacity:0;
  animation:dotPing 2.4s ease-out infinite;
}
.feed-dot.green{background:#22C55E;box-shadow:0 0 8px rgba(34,197,94,.7)}
.feed-dot.green::after{background:rgba(34,197,94,.35)}
.feed-dot.blue{background:#3B82F6;box-shadow:0 0 8px rgba(59,130,246,.7)}
.feed-dot.blue::after{background:rgba(59,130,246,.35)}
.feed-dot.amber{background:#F59E0B;box-shadow:0 0 8px rgba(245,158,11,.7)}
.feed-dot.amber::after{background:rgba(245,158,11,.35)}

/* stagger the ping so not all dots pulse simultaneously */
.feed-item:nth-child(2)  .feed-dot::after{animation-delay:.4s}
.feed-item:nth-child(3)  .feed-dot::after{animation-delay:.8s}
.feed-item:nth-child(4)  .feed-dot::after{animation-delay:1.2s}
.feed-item:nth-child(5)  .feed-dot::after{animation-delay:1.6s}
.feed-item:nth-child(6)  .feed-dot::after{animation-delay:2.0s}
.feed-item:nth-child(7)  .feed-dot::after{animation-delay:.3s}
.feed-item:nth-child(8)  .feed-dot::after{animation-delay:.9s}
.feed-item:nth-child(9)  .feed-dot::after{animation-delay:1.5s}
.feed-item:nth-child(10) .feed-dot::after{animation-delay:2.1s}

@keyframes dotPing{
  0%  {transform:scale(1);opacity:.8}
  70% {transform:scale(2.8);opacity:0}
  100%{transform:scale(2.8);opacity:0}
}

.feed-worker{font-size:13px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.feed-action{font-size:14px;color:rgba(255,255,255,.85)}
.feed-time{font-size:12px;color:rgba(255,255,255,.4);margin-left:6px}

/* ── SECTION ATOMS ── */
.sec{padding:clamp(60px,8vw,100px) 0}
.sec-eye{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#0D0D0D;margin-bottom:12px}
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
  min-height:340px;
  transition:transform .2s,box-shadow .2s;
}
.wk-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,0.08)}

/* Character image — absolute, right side, fills card height */
.wk-img-bg{
  position:absolute;
  right:0;top:0;bottom:0;
  width:42%;
  pointer-events:none;
}
.wk-img-bg img{
  width:100%;height:100%;
  object-fit:cover;
  object-position:center top;
  display:block;
}
/* Fade: white → transparent */
.wk-img-bg::after{
  content:'';
  position:absolute;inset:0;
  background:linear-gradient(to right, #fff 0%, rgba(255,255,255,.9) 25%, rgba(255,255,255,.4) 50%, transparent 68%);
}

/* Content — left side, z-index above image */
.wk-content{
  position:relative;z-index:1;
  padding:22px 20px 20px;
  display:flex;flex-direction:column;
  flex:1;
  width:72%;
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
.wk-role{font-size:11px;color:#6B7280;font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:10px}
.wk-quote{
  font-size:13px;color:#374151;line-height:1.65;
  margin-bottom:14px;
}
.wk-bullets{display:flex;flex-direction:column;gap:7px;margin-bottom:18px;flex:1}
.wk-bullet{display:flex;align-items:center;gap:8px;font-size:12.5px;color:#374151}
.wk-check{width:18px;height:18px;border-radius:50%;background:#0D0D0D;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.wk-check svg{width:10px;height:10px;stroke:#fff;stroke-width:3}
.wk-btns{display:flex;align-items:center;gap:7px;flex-wrap:nowrap;margin-top:auto}
.btn-wk{
  display:inline-flex;align-items:center;gap:6px;
  padding:9px 13px;border-radius:10px;
  font-size:12px;font-weight:700;color:#fff;background:#0D0D0D;
  white-space:nowrap;flex:1;justify-content:center;
  transition:opacity .15s,transform .1s;
}
.btn-wk:hover{opacity:.85;transform:translateY(-1px)}
.btn-wk-outline{
  display:inline-flex;align-items:center;gap:6px;
  padding:9px 13px;border-radius:10px;
  font-size:12px;font-weight:700;color:#0D0D0D;
  background:transparent;border:1.5px solid #E5E7EB;
  white-space:nowrap;flex:1;justify-content:center;
  transition:border-color .15s,transform .1s;
}
.btn-wk-outline:hover{border-color:#0D0D0D;transform:translateY(-1px)}

/* ── TIMELINE ── */
.timeline-sec{background:var(--soft);border-bottom:1px solid var(--border)}
.tl{
  display:grid;grid-template-columns:repeat(6,1fr);
  gap:0;position:relative;margin-top:clamp(40px,5vw,64px);
}
/* dashed base line */
.tl::before{
  content:'';position:absolute;
  top:32px;left:8%;right:8%;height:2px;
  background:repeating-linear-gradient(90deg,var(--border) 0,var(--border) 6px,transparent 6px,transparent 12px);
  z-index:0;
}

.tl-item{display:flex;flex-direction:column;align-items:center;text-align:center;padding:0 8px;position:relative}

/* node base */
.tl-node{
  width:64px;height:64px;border-radius:50%;
  background:#fff;border:2px solid #D1D5DB;
  display:flex;align-items:center;justify-content:center;
  position:relative;z-index:2;margin-bottom:18px;
  flex-shrink:0;color:#111;
  box-shadow:0 2px 8px rgba(0,0,0,.06);
}
.tl-node svg{width:26px;height:26px;stroke:currentColor}

/*
  12s total cycle, 2s per node.
  Active window = 0–15% = 1.8s. Next node starts at 2s (16.7%).
  Gap between end of active (1.8s) and next start (2s) = 0.2s — clean, no overlap.
*/
@keyframes nodeActivate{
  0%   {background:#fff;border-color:#D1D5DB;transform:scale(1);color:#111;box-shadow:0 2px 8px rgba(0,0,0,.06)}
  6%   {background:#0D0D0D;border-color:#0D0D0D;transform:scale(1.16);color:#fff;box-shadow:0 0 0 8px rgba(0,0,0,.08),0 0 22px rgba(0,0,0,.2)}
  13%  {background:#0D0D0D;border-color:#0D0D0D;transform:scale(1.12);color:#fff;box-shadow:0 0 0 4px rgba(0,0,0,.04),0 0 12px rgba(0,0,0,.12)}
  20%  {background:#fff;border-color:#D1D5DB;transform:scale(1);color:#111;box-shadow:0 2px 8px rgba(0,0,0,.06)}
  100% {background:#fff;border-color:#D1D5DB;transform:scale(1);color:#111;box-shadow:0 2px 8px rgba(0,0,0,.06)}
}
@keyframes timeFlash{
  0%,20%,100%{color:var(--t3);font-weight:600}
  6%,13%     {color:#0D0D0D;font-weight:800}
}
.tl-item:nth-child(1) .tl-node {animation:nodeActivate 12s ease-in-out infinite 0s}
.tl-item:nth-child(2) .tl-node {animation:nodeActivate 12s ease-in-out infinite 2s}
.tl-item:nth-child(3) .tl-node {animation:nodeActivate 12s ease-in-out infinite 4s}
.tl-item:nth-child(4) .tl-node {animation:nodeActivate 12s ease-in-out infinite 6s}
.tl-item:nth-child(5) .tl-node {animation:nodeActivate 12s ease-in-out infinite 8s}
.tl-item:nth-child(6) .tl-node {animation:nodeActivate 12s ease-in-out infinite 10s}
.tl-item:nth-child(1) .tl-time {animation:timeFlash 12s ease-in-out infinite 0s}
.tl-item:nth-child(2) .tl-time {animation:timeFlash 12s ease-in-out infinite 2s}
.tl-item:nth-child(3) .tl-time {animation:timeFlash 12s ease-in-out infinite 4s}
.tl-item:nth-child(4) .tl-time {animation:timeFlash 12s ease-in-out infinite 6s}
.tl-item:nth-child(5) .tl-time {animation:timeFlash 12s ease-in-out infinite 8s}
.tl-item:nth-child(6) .tl-time {animation:timeFlash 12s ease-in-out infinite 10s}

.tl-time{font-size:13px;font-weight:600;letter-spacing:.03em;margin-bottom:7px;color:var(--t3)}
.tl-evt{font-size:14.5px;color:var(--t3);line-height:1.6}
.tl-evt strong{color:var(--text);display:block;margin-bottom:2px;font-weight:700}
/* last two nodes start purple-tinted so they always feel "special" */
.tl-item:nth-child(5) .tl-node,
.tl-item:nth-child(6) .tl-node{border-color:rgba(0,0,0,.15)}

/* ── LIFECYCLE ── */
.lifecycle{background:#fff;padding-top:0}
/* break lifecycle card past the normal .w max-width */
.lifecycle .w{ max-width:min(1360px, calc(100vw - 48px)); }
/* outer card wraps the whole section — break out of .w padding to fill viewport */
.lc-card{
  border:1.5px solid var(--border);
  border-radius:28px;
  box-shadow:0 8px 40px rgba(0,0,0,.07);
  padding:clamp(32px,4vw,52px) clamp(28px,4vw,52px);
  display:grid;
  grid-template-columns:220px 1fr;
  gap:clamp(28px,4vw,44px);
  align-items:center;
}
.lc-left .sec-h{margin-bottom:14px;font-size:clamp(1.3rem,2.4vw,1.85rem)}
.lc-left p{font-size:.9rem;color:var(--t3);line-height:1.7;margin-bottom:22px}
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
  display:flex;align-items:stretch;
  gap:8px;
}
.lc-photo{
  flex:1;
  border:1px solid var(--border);
  border-radius:18px;
  overflow:hidden;
  background:var(--soft);
}
.lc-photo img{
  width:100%;height:260px;
  object-fit:cover;object-position:center top;
  display:block;
}
.lc-photo-body{padding:14px 16px 18px}
.lc-photo-step{
  font-size:10.5px;font-weight:700;letter-spacing:.08em;
  text-transform:uppercase;margin-bottom:6px;
}
.lc-photo-txt{font-size:13.5px;color:var(--t2);line-height:1.55;font-weight:500}
/* circular arrow badge — sits in the gap, centered on image height */
.lc-arrow{
  flex-shrink:0;
  width:36px;
  height:36px;
  border-radius:50%;
  background:#fff;
  border:1.5px solid var(--border);
  box-shadow:0 2px 10px rgba(0,0,0,.12);
  display:flex;align-items:center;justify-content:center;
  color:var(--t3);
  font-size:15px;
  z-index:10;
  position:relative;
  align-self:flex-start;
  /* center arrow badge at midpoint of image (260px / 2 - 18px) */
  margin-top:calc(130px - 18px);
  /* pull it into both adjacent cards */
  margin-left:-20px;
  margin-right:-20px;
}

/* ── WHAT IS A UNIT WORKER ── */
.what{background:var(--soft);border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
.what-grid{display:grid;grid-template-columns:1fr 1fr;gap:clamp(40px,6vw,80px);align-items:center}
.what-tag{
  display:inline-flex;align-items:center;gap:8px;
  padding:5px 12px;border-radius:99px;
  background:rgba(0,0,0,.05);
  font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:#0D0D0D;margin-bottom:20px;
}
.what-h{
  font-family:var(--font-h);font-size:clamp(1.8rem,3.2vw,2.6rem);
  font-weight:800;letter-spacing:-.03em;line-height:1.12;
  color:var(--text);margin-bottom:16px;
}
.what-h em{font-style:normal;position:relative;display:inline}
.what-h em::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}
.what-sub{font-size:1rem;color:var(--t3);line-height:1.75;margin-bottom:28px;max-width:440px}
.what-pills{display:flex;flex-wrap:wrap;gap:8px}
.what-pill{
  padding:6px 14px;border-radius:99px;
  font-size:12.5px;font-weight:600;color:var(--t2);
  border:1px solid var(--border);background:#fff;
}
[data-theme="dark"] .what-pill{background:#161616}
.what-right{display:flex;flex-direction:column;gap:20px}
.what-item{
  display:flex;gap:14px;align-items:flex-start;
  padding:18px;border-radius:16px;
  background:#fff;border:1px solid var(--border);
}
[data-theme="dark"] .what-item{background:#161616}
.what-num{
  width:32px;height:32px;border-radius:9px;
  background:#0D0D0D;color:#fff;
  font-size:13px;font-weight:800;
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;
}
.what-item-body h4{font-size:14.5px;font-weight:700;color:var(--text);margin-bottom:4px}
.what-item-body p{font-size:13px;color:var(--t3);line-height:1.6}

/* ── VIDEO ── */
.video-sec{background:var(--soft);padding:clamp(52px,7vw,88px) 0}
.video-grid{display:grid;grid-template-columns:1fr 1fr;gap:clamp(40px,6vw,72px);align-items:center}
/* ── RESOURCES RAIL ── */
.res-sec{background:var(--soft);padding:clamp(52px,7vw,88px) 0}
[data-theme="dark"] .res-sec{background:#0D0D0D}
.res-head{
  display:flex;align-items:flex-end;justify-content:space-between;
  margin-bottom:clamp(28px,4vw,40px);flex-wrap:wrap;gap:16px;
}
.res-head-left .sec-eye{margin-bottom:10px}
.res-head-left .sec-h{margin-bottom:0;max-width:480px}
.res-scroll-wrap{position:relative}
.res-scroll-wrap::after{
  content:'';position:absolute;right:0;top:0;bottom:0;width:60px;
  background:linear-gradient(to left,var(--soft),transparent);
  pointer-events:none;z-index:2;
}
[data-theme="dark"] .res-scroll-wrap::after{background:linear-gradient(to left,#0D0D0D,transparent)}
.res-rail{
  display:flex;gap:16px;
  overflow-x:auto;padding-bottom:12px;
  scrollbar-width:none;-ms-overflow-style:none;
}
.res-rail::-webkit-scrollbar{display:none}
.res-card{
  flex-shrink:0;width:360px;
  background:#fff;border:1px solid var(--border);border-radius:16px;
  overflow:hidden;display:flex;flex-direction:column;
  transition:box-shadow .2s,transform .15s;
  cursor:pointer;
}
[data-theme="dark"] .res-card{background:#111;border-color:#2D2D2D}
.res-card:hover{box-shadow:0 8px 28px rgba(0,0,0,.1);transform:translateY(-3px)}
[data-theme="dark"] .res-card:hover{box-shadow:0 8px 28px rgba(0,0,0,.4)}
.res-thumb{
  height:200px;display:flex;align-items:center;justify-content:center;
  position:relative;overflow:hidden;
}
.res-thumb-icon{
  width:48px;height:48px;border-radius:14px;
  display:flex;align-items:center;justify-content:center;
  background:rgba(255,255,255,.15);
}
.res-thumb-icon svg{width:24px;height:24px;stroke:#fff;fill:none}
.res-play-ring{
  position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
}
.res-play-btn{
  width:44px;height:44px;border-radius:50%;background:rgba(255,255,255,.25);
  border:2px solid rgba(255,255,255,.5);
  display:flex;align-items:center;justify-content:center;
  transition:background .2s,transform .15s;
}
.res-card:hover .res-play-btn{background:rgba(255,255,255,.4);transform:scale(1.1)}
.res-play-btn svg{width:16px;height:16px;fill:#fff;stroke:none;margin-left:2px}
.res-body{padding:16px 18px 20px;display:flex;flex-direction:column;flex:1}
.res-badge{
  display:inline-flex;align-items:center;gap:5px;
  font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
  padding:3px 8px;border-radius:6px;margin-bottom:10px;width:fit-content;
}
.res-title{font-size:16px;font-weight:700;color:var(--text);line-height:1.4;margin-bottom:6px;flex:1}
[data-theme="dark"] .res-title{color:#F3F4F6}
.res-meta{font-size:12px;color:var(--t4);display:flex;align-items:center;gap:6px;margin-top:8px}
.res-link{
  display:inline-flex;align-items:center;gap:5px;
  font-size:12.5px;font-weight:600;margin-top:10px;
  color:#0D0D0D;transition:gap .15s;
}
.res-card:hover .res-link{gap:8px}
/* badge colors */
.badge-video{background:rgba(245,197,24,.15);color:#0D0D0D}
[data-theme="dark"] .badge-video{background:rgba(245,197,24,.15);color:#0D0D0D}
.badge-blog{background:rgba(16,185,129,.1);color:#059669}
[data-theme="dark"] .badge-blog{background:rgba(16,185,129,.2);color:#6EE7B7}
.badge-report{background:rgba(59,130,246,.1);color:#2563EB}
[data-theme="dark"] .badge-report{background:rgba(59,130,246,.2);color:#93C5FD}
.badge-insight{background:rgba(245,158,11,.1);color:#D97706}
[data-theme="dark"] .badge-insight{background:rgba(245,158,11,.2);color:#FCD34D}

/* ─── old video stuff (kept for potential re-use) ─── */
.video-wrap{
  position:relative;border-radius:20px;overflow:hidden;
  background:#0D0D0D;aspect-ratio:16/9;
  box-shadow:0 20px 60px rgba(0,0,0,.18);
}
.video-wrap video,.video-wrap iframe{width:100%;height:100%;display:block;object-fit:cover}
/* placeholder shown when no video src */
.video-placeholder{
  position:absolute;inset:0;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:16px;
  background:linear-gradient(135deg,#1a0533 0%,#0D0D0D 100%);
}
.video-play{
  width:64px;height:64px;border-radius:50%;
  background:#0D0D0D;
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 0 0 12px rgba(124,58,237,.2);
  transition:transform .2s,box-shadow .2s;
  cursor:pointer;
}
.video-play:hover{transform:scale(1.08);box-shadow:0 0 0 16px rgba(124,58,237,.15)}
.video-play svg{width:26px;height:26px;margin-left:4px}
.video-caption{font-size:13px;color:rgba(255,255,255,.4);letter-spacing:.04em}
.video-right .sec-eye{margin-bottom:12px}
.video-right .sec-h{margin-bottom:14px}
.video-right p{font-size:1rem;color:var(--t3);line-height:1.75;margin-bottom:28px}
[data-theme="dark"] .video-sec{background:#0D0D0D}

/* ── FAQ ── */
.faq-sec{background:var(--soft);padding:clamp(60px,8vw,100px) 0}
.faq-grid{display:grid;grid-template-columns:1fr 1.6fr;gap:clamp(40px,6vw,72px);align-items:flex-start}
.faq-left .sec-h{margin-bottom:12px}
.faq-left p{font-size:1rem;color:var(--t3);line-height:1.7;margin-bottom:24px}
.faq-list{display:flex;flex-direction:column;gap:0}
.faq-item{border-bottom:1px solid var(--border)}
.faq-item:first-child{border-top:1px solid var(--border)}
.faq-q{
  width:100%;display:flex;align-items:center;justify-content:space-between;
  padding:18px 0;background:none;border:none;cursor:pointer;
  font-family:var(--font-h);font-size:15px;font-weight:700;
  color:var(--text);text-align:left;gap:16px;
}
.faq-q svg{width:18px;height:18px;flex-shrink:0;color:var(--t3);transition:transform .25s}
.faq-item.open .faq-q svg{transform:rotate(45deg)}
.faq-a{
  font-size:14px;color:var(--t3);line-height:1.75;
  max-height:0;overflow:hidden;
  transition:max-height .3s ease,padding .3s ease;
}
.faq-item.open .faq-a{max-height:300px;padding-bottom:16px}
[data-theme="dark"] .faq-sec{background:#0D0D0D}

/* ── FEATURES ── */
.features{background:#fff;padding:clamp(48px,6vw,72px) 0}
.feat-h{
  font-family:var(--font-h);font-size:clamp(1.4rem,2.8vw,2rem);
  font-weight:800;letter-spacing:-.03em;text-align:center;
  margin-bottom:clamp(36px,5vw,52px);
}
.feat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:clamp(20px,3vw,36px)}
.feat-item{display:flex;flex-direction:row;gap:14px;align-items:flex-start}
.feat-icon{
  width:44px;height:44px;border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  flex-shrink:0;
}
.feat-icon svg{width:22px;height:22px}
.feat-body h4{font-size:14.5px;font-weight:700;margin-bottom:5px;color:var(--text)}
.feat-body p{font-size:13px;color:var(--t3);line-height:1.6}

/* ── CTA CARD ── */
.cta-sec{padding:clamp(28px,4vw,48px) 0}
.cta-card{
  background:#EDE9FE;
  border-radius:24px;
  padding:clamp(28px,4vw,44px) clamp(28px,5vw,52px);
  display:flex;align-items:center;justify-content:space-between;
  gap:32px;flex-wrap:wrap;
}
.cta-left{display:flex;align-items:center;gap:20px}
.cta-icon{width:56px;height:56px;flex-shrink:0;color:#0D0D0D}
.cta-icon svg{width:56px;height:56px}
.cta-text h2{
  font-family:var(--font-h);font-size:clamp(1.2rem,2.2vw,1.6rem);
  font-weight:800;color:var(--text);letter-spacing:-.02em;margin-bottom:4px;
}
.cta-text p{font-size:14px;color:var(--t3)}
.cta-right{display:flex;flex-direction:column;align-items:center;gap:8px;flex-shrink:0}
.btn-cta-main{
  display:inline-flex;align-items:center;gap:8px;
  padding:14px 28px;border-radius:12px;
  font-size:15px;font-weight:700;
  background:#0D0D0D;color:#fff;
  box-shadow:0 4px 20px rgba(0,0,0,.12);
  white-space:nowrap;
  transition:opacity .15s,transform .15s;
}
.btn-cta-main:hover{opacity:.9;transform:translateY(-2px)}
.cta-note{font-size:12.5px;color:var(--t4)}

/* ── TRUST BAR ── */
.trust-bar{padding:clamp(32px,4vw,52px) 0 clamp(32px,4vw,52px);background:#fff}
.trust-bar-i{
  display:flex;align-items:center;flex-wrap:wrap;
  gap:clamp(24px,4vw,48px);
}
.trust-left{display:flex;flex-direction:column;gap:10px}
.trust-lbl2{font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--text)}
.trust-avs{display:flex;align-items:center;gap:0}
.trust-avs img{
  width:32px;height:32px;border-radius:50%;
  border:2px solid #fff;outline:1.5px solid #d1d5db;
  object-fit:cover;margin-left:-8px;
}
.trust-avs img:first-child{margin-left:0}
.trust-score{display:flex;align-items:center;gap:8px;margin-top:4px}
.trust-score-avs{display:flex}
.trust-stars2{color:#F59E0B;font-size:12px;letter-spacing:1px}
.trust-score-txt{font-size:13px;font-weight:600;color:var(--t2)}
.trust-platforms{display:flex;align-items:center;gap:clamp(20px,3vw,40px);flex-wrap:wrap;flex:1;justify-content:flex-end}
.trust-platform{display:flex;flex-direction:column;align-items:center;gap:5px}
.trust-platform-name{font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:4px}
.trust-platform-stars{color:#F59E0B;font-size:11px;letter-spacing:1px}

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
.ft-desc{font-size:13.5px;color:rgba(255,255,255,.6);line-height:1.7;max-width:220px;margin-bottom:20px}
.ft-col-h{
  font-size:10.5px;font-weight:700;letter-spacing:.1em;
  text-transform:uppercase;color:rgba(255,255,255,.45);
  margin-bottom:14px;
}
.ft-links{display:flex;flex-direction:column;gap:9px}
.ft-links a{font-size:13.5px;color:rgba(255,255,255,.7);transition:color .15s}
.ft-links a:hover{color:#fff}
.ft-bottom{
  border-top:1px solid rgba(255,255,255,.12);padding-top:24px;
  display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;
}
.ft-bottom p{font-size:12.5px;color:rgba(255,255,255,.45)}

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
  .what-grid,.faq-grid{grid-template-columns:1fr}
  .res-card{width:300px}
  .feat-grid{grid-template-columns:repeat(2,1fr)}
  .trust-platforms{justify-content:flex-start}
  .wk-grid{grid-template-columns:repeat(2,1fr)}
  .wk-img-bg{width:50%}
  .ft-grid{grid-template-columns:1fr 1fr;gap:28px}
  .lc-card{grid-template-columns:1fr;gap:28px}
  .lc-arrow{display:none}
}
@media(max-width:768px){
  .nav-links,.nav-acts{display:none}
  .ham{display:flex}
  .hero{grid-template-columns:1fr;min-height:auto}
  .hero-right{order:-1;min-height:300px}
  .hero-left{padding:40px var(--pad);text-align:center}
  .hero-btns{justify-content:center}
  .hero-proof{justify-content:center}
  .tl{grid-template-columns:repeat(3,1fr)}
  .tl::before{display:none}
  .tl-item:not(:last-child)::after{display:none}
  .ft-grid{grid-template-columns:1fr}
  .ft-bottom{flex-direction:column;text-align:center}
  /* lifecycle: strip the card border, 2-col grid, full bleed */
  .lc-card{border:none;box-shadow:none;padding:0;background:transparent!important}
  .lifecycle .w{padding-left:10px;padding-right:10px;max-width:100%}
  .lc-row{flex-wrap:wrap;gap:8px}
  .lc-photo{min-width:calc(50% - 5px);flex:1}
  .lc-photo img{height:200px}
}
@media(max-width:480px){
  .wk-grid{grid-template-columns:1fr}
  .tl{grid-template-columns:repeat(2,1fr)}
  .hero-btns{flex-direction:column;align-items:stretch}
  .btn-hero,.btn-hero-ghost{justify-content:center}
  .lc-photo{min-width:calc(50% - 5px);flex:1}
  .lc-photo img{height:180px}
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
      <li><a href="/workers">Meet the Workers</a></li>
      <li><a href="#timeline">How It Works</a></li>
      <li><a href="{{ route('marketplace') }}">For Business</a></li>
      <li><a href="#resources">Resources</a></li>
      <li><a href="{{ route('pricing') }}">Pricing</a></li>
    </ul>
    <div class="nav-acts">
      <a href="{{ route('login') }}" class="btn-login" style="border-radius:99px">Log in</a>
      <a href="{{ route('register') }}" class="btn-cta">
        Hire Your First Worker
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <button class="theme-toggle" id="theme-toggle" aria-label="Toggle theme">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
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
    <a href="/workers" onclick="closeMob()">Meet the Workers</a>
    <a href="#timeline" onclick="closeMob()">How It Works</a>
    <a href="{{ route('marketplace') }}" onclick="closeMob()">For Business</a>
    <a href="#resources" onclick="closeMob()">Resources</a>
    <a href="{{ route('pricing') }}" onclick="closeMob()">Pricing</a>
  </div>
  <div class="mob-ctas">
    <a href="{{ route('login') }}" class="btn-login" style="text-align:center;padding:12px;border-radius:99px">Log in</a>
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

<!-- ACTIVITY FEED TICKER -->
<div class="activity-feed">
  <div class="feed-track">
    <!-- set 1 -->
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Renewal drafted for Apex Property Group</span>
      <span class="feed-time">3s ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#fff">DOX</span>
      <span class="feed-action">1,247 lease files sorted and tagged</span>
      <span class="feed-time">14m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot amber"></span>
      <span class="feed-worker" style="color:#fff">MOX</span>
      <span class="feed-action">Brand mention found on LinkedIn — flagged for review</span>
      <span class="feed-time">1m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#fff">NUX</span>
      <span class="feed-action">Campaign published across 3 channels</span>
      <span class="feed-time">22m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Follow-up sent to Sunrise LLC · renewal confirmed</span>
      <span class="feed-time">8m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot blue"></span>
      <span class="feed-worker" style="color:#fff">DOX</span>
      <span class="feed-action">Contract uploaded · client folder updated automatically</span>
      <span class="feed-time">31m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#fff">MOX</span>
      <span class="feed-action">National Coffee Day opportunity surfaced — sent to team</span>
      <span class="feed-time">2h ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">3 renewals processed before 9 AM · zero missed</span>
      <span class="feed-time">today</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot amber"></span>
      <span class="feed-worker" style="color:#fff">NUX</span>
      <span class="feed-action">Content repurposed from last week's report · 6 posts ready</span>
      <span class="feed-time">45m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot blue"></span>
      <span class="feed-worker" style="color:#fff">DOX</span>
      <span class="feed-action">Duplicate files removed · 340 MB recovered</span>
      <span class="feed-time">1h ago</span>
    </div>
    <!-- set 2 — exact clone for seamless loop -->
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Renewal drafted for Apex Property Group</span>
      <span class="feed-time">3s ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#fff">DOX</span>
      <span class="feed-action">1,247 lease files sorted and tagged</span>
      <span class="feed-time">14m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot amber"></span>
      <span class="feed-worker" style="color:#fff">MOX</span>
      <span class="feed-action">Brand mention found on LinkedIn — flagged for review</span>
      <span class="feed-time">1m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#fff">NUX</span>
      <span class="feed-action">Campaign published across 3 channels</span>
      <span class="feed-time">22m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Follow-up sent to Sunrise LLC · renewal confirmed</span>
      <span class="feed-time">8m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot blue"></span>
      <span class="feed-worker" style="color:#fff">DOX</span>
      <span class="feed-action">Contract uploaded · client folder updated automatically</span>
      <span class="feed-time">31m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#fff">MOX</span>
      <span class="feed-action">National Coffee Day opportunity surfaced — sent to team</span>
      <span class="feed-time">2h ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">3 renewals processed before 9 AM · zero missed</span>
      <span class="feed-time">today</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot amber"></span>
      <span class="feed-worker" style="color:#fff">NUX</span>
      <span class="feed-action">Content repurposed from last week's report · 6 posts ready</span>
      <span class="feed-time">45m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot blue"></span>
      <span class="feed-worker" style="color:#fff">DOX</span>
      <span class="feed-action">Duplicate files removed · 340 MB recovered</span>
      <span class="feed-time">1h ago</span>
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
      <div class="wk-card" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/ava-stand.png" alt="AVA" style="object-position:center 10%;transform:scale(1.5);transform-origin:top center">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="wk-name">AVA</div>
          </div>
          <div class="wk-role">Renewals Specialist</div>
          <p class="wk-quote">"I remember the renewals everyone else forgets."</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Tracks every renewal</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Sends reminders</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Reduces churn</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Protects revenue</div>
          </div>
          <div class="wk-btns">
            <a href="{{ route('workers.public.show2', 'ava') }}" class="btn-wk-outline">Watch Ava's Day <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            <a href="{{ route('register') }}" class="btn-wk">Hire AVA <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
          </div>
        </div>
      </div>

      <!-- DOX -->
      <div class="wk-card" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/dox.png" alt="DOX" style="object-position:center top">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            </div>
            <div class="wk-name">DOX</div>
          </div>
          <div class="wk-role">Document Specialist</div>
          <p class="wk-quote">"I organize the documents nobody wants to touch."</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Organizes files</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Finds what's lost</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Structures systems</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates order</div>
          </div>
          <div class="wk-btns">
            <a href="#" class="btn-wk-outline">Watch Dox's Day <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            <a href="{{ route('register') }}" class="btn-wk">Hire DOX <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
          </div>
        </div>
      </div>

      <!-- MOX -->
      <div class="wk-card" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/mox.png" alt="MOX" style="object-position:center top">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </div>
            <div class="wk-name">MOX</div>
          </div>
          <div class="wk-role">Brand Moments Hunter</div>
          <p class="wk-quote">"I find the moments your brand shouldn't miss."</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Finds brand moments</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Tracks opportunities</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates campaigns</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Delivers impact</div>
          </div>
          <div class="wk-btns">
            <a href="#" class="btn-wk-outline">Watch Mox's Day <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            <a href="{{ route('register') }}" class="btn-wk">Hire MOX <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
          </div>
        </div>
      </div>

      <!-- NUX -->
      <div class="wk-card" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/nux.png" alt="NUX" style="object-position:center top">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <div class="wk-name">NUX</div>
          </div>
          <div class="wk-role">Publishing Specialist</div>
          <p class="wk-quote">"I turn one idea into content people actually see."</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates content</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Repurposes ideas</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Publishes daily</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Grows your reach</div>
          </div>
          <div class="wk-btns">
            <a href="#" class="btn-wk-outline">Watch Nux's Day <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            <a href="{{ route('register') }}" class="btn-wk">Hire NUX <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
          </div>
        </div>
      </div>
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
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        </div>
        <div class="tl-time">8:00 AM</div>
        <div class="tl-evt"><strong>AVA</strong>finishes three renewals.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
        </div>
        <div class="tl-time">9:30 AM</div>
        <div class="tl-evt"><strong>DOX</strong>organizes 1,247 files.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </div>
        <div class="tl-time">11:00 AM</div>
        <div class="tl-evt"><strong>MOX</strong>discovers National Coffee Day.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        </div>
        <div class="tl-time">2:00 PM</div>
        <div class="tl-evt"><strong>NUX</strong>publishes six campaigns.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="tl-time">5:00 PM</div>
        <div class="tl-evt"><strong>You arrive.</strong>Everything is already done.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 12a9 9 0 1018 0 9 9 0 00-18 0"/><path d="M12 8v4l3 3"/></svg>
        </div>
        <div class="tl-time">Next morning</div>
        <div class="tl-evt"><strong>They start again.</strong>Without being asked.</div>
      </div>
    </div>
  </div>
</section>

<!-- VIDEO -->
<!-- RESOURCES RAIL -->
<section class="res-sec" id="resources">
  <div class="w">
    <div class="res-head">
      <div class="res-head-left">
        <div class="sec-eye">Resources</div>
        <h2 class="sec-h">From the field — videos, insights,<br>and real examples.</h2>
      </div>
      <a href="{{ route('register') }}" class="btn-outline">
        Browse all
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
    <div class="res-scroll-wrap">
      <div class="res-rail">

        <!-- Card 1: Video -->
        <div class="res-card">
          <div class="res-thumb" style="background:#0D0D0D">
            <div class="res-play-ring">
              <div class="res-play-btn">
                <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
              </div>
            </div>
          </div>
          <div class="res-body">
            <span class="res-badge badge-video">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              Video
            </span>
            <div class="res-title">AVA's First Day: Watch a full renewal handled start to finish</div>
            <div class="res-meta">8 min watch · AVA</div>
            <span class="res-link">Watch now <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
          </div>
        </div>

        <!-- Card 2: Blog -->
        <div class="res-card">
          <div class="res-thumb" style="background:linear-gradient(135deg,#064E3B,#065F46)">
            <div class="res-thumb-icon">
              <svg viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
            </div>
          </div>
          <div class="res-body">
            <span class="res-badge badge-blog">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
              Blog
            </span>
            <div class="res-title">5 signs your team is doing work that a worker should be doing instead</div>
            <div class="res-meta">6 min read · Operations</div>
            <span class="res-link">Read post <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
          </div>
        </div>

        <!-- Card 3: Whitepaper -->
        <div class="res-card">
          <div class="res-thumb" style="background:linear-gradient(135deg,#1E3A5F,#1D4ED8)">
            <div class="res-thumb-icon">
              <svg viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round"><path d="M9 12h6M9 16h6M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/></svg>
            </div>
          </div>
          <div class="res-body">
            <span class="res-badge badge-report">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16v16H4z"/><path d="M4 9h16M9 4v16"/></svg>
              Whitepaper
            </span>
            <div class="res-title">The Future of Work: How AI workers change what your team actually does</div>
            <div class="res-meta">14 pages · PDF download</div>
            <span class="res-link">Download <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
          </div>
        </div>

        <!-- Card 4: Industry Insight -->
        <div class="res-card">
          <div class="res-thumb" style="background:linear-gradient(135deg,#78350F,#B45309)">
            <div class="res-thumb-icon">
              <svg viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            </div>
          </div>
          <div class="res-body">
            <span class="res-badge badge-insight">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
              Insight
            </span>
            <div class="res-title">Why renewal rates drop when teams grow — and how automation reverses it</div>
            <div class="res-meta">3 min read · Real estate</div>
            <span class="res-link">Read insight <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
          </div>
        </div>

        <!-- Card 5: Video -->
        <div class="res-card">
          <div class="res-thumb" style="background:linear-gradient(135deg,#1F1F2E,#3730A3)">
            <div class="res-play-ring">
              <div class="res-play-btn">
                <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
              </div>
            </div>
          </div>
          <div class="res-body">
            <span class="res-badge badge-video">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              Video
            </span>
            <div class="res-title">DOX in action: 1,200 documents sorted in under 3 minutes</div>
            <div class="res-meta">5 min watch · DOX</div>
            <span class="res-link">Watch now <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
          </div>
        </div>

        <!-- Card 6: Blog -->
        <div class="res-card">
          <div class="res-thumb" style="background:linear-gradient(135deg,#14532D,#16A34A)">
            <div class="res-thumb-icon">
              <svg viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
          </div>
          <div class="res-body">
            <span class="res-badge badge-blog">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
              Blog
            </span>
            <div class="res-title">Real feedback from teams who hired their first UNIT worker</div>
            <div class="res-meta">8 min read · Customer stories</div>
            <span class="res-link">Read post <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
          </div>
        </div>

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
          <img src="/images/ava-life.png" alt="Wake up">
          <div class="lc-photo-body">
            <div class="lc-photo-step" style="color:#0D0D0D">1. Wake Up</div>
            <div class="lc-photo-txt">Ready for the day at the desk.</div>
          </div>
        </div>
        <div class="lc-arrow">→</div>
        <div class="lc-photo">
          <img src="/images/dox-life.png" alt="Receive work">
          <div class="lc-photo-body">
            <div class="lc-photo-step" style="color:var(--text)">2. Receive Work</div>
            <div class="lc-photo-txt">New tasks. New opportunities.</div>
          </div>
        </div>
        <div class="lc-arrow">→</div>
        <div class="lc-photo">
          <img src="/images/mox-life.png" alt="Do the work">
          <div class="lc-photo-body">
            <div class="lc-photo-step" style="color:var(--text)">3. Do the Work</div>
            <div class="lc-photo-txt">Focus. Execute. Deliver results.</div>
          </div>
        </div>
        <div class="lc-arrow">→</div>
        <div class="lc-photo">
          <img src="/images/nux-life.png" alt="Write their diary">
          <div class="lc-photo-body">
            <div class="lc-photo-step" style="color:var(--text)">4. Write Their Diary</div>
            <div class="lc-photo-txt">Reflect, learn, get better tomorrow.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features">
  <div class="w">
    <h2 class="feat-h">Built for humans. Powered by AI.</h2>
    <div class="feat-grid">
      <div class="feat-item">
        <div class="feat-icon" style="background:#ECFDF5">
          <svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div class="feat-body">
          <h4>Secure by design</h4>
          <p>Enterprise-grade security and privacy. Your data is always yours.</p>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon" style="background:#FFF7ED">
          <svg viewBox="0 0 24 24" fill="none" stroke="#EA580C" stroke-width="2" stroke-linecap="round"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
        </div>
        <div class="feat-body">
          <h4>Learns and adapts</h4>
          <p>Every task makes them smarter. They improve without extra work from you.</p>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon" style="background:#EFF6FF">
          <svg viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div class="feat-body">
          <h4>Works 24/7</h4>
          <p>No breaks. No vacations. Always showing up when you need them.</p>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon" style="background:#F5F3FF">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div class="feat-body">
          <h4>Made to collaborate</h4>
          <p>Workers can work together seamlessly as your business grows.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="faq-sec" id="faq">
  <div class="w">
    <div class="faq-grid">
      <div class="faq-left">
        <div class="sec-eye">FAQ</div>
        <h2 class="what-h" style="margin-bottom:16px">Not an app.<br>Not a bot.<br><em>A worker.</em></h2>
        <p style="font-size:.975rem;color:var(--t3);line-height:1.75;margin-bottom:20px">A UNIT worker is a system built from your day-to-day workflow. It runs without hand-holding, handles its job end-to-end, and reports back to you — so you always know what was done and why.</p>
        <a href="{{ route('register') }}" class="btn-outline">
          Get started free
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
      <div class="faq-list">
        <div class="faq-item open">
          <button class="faq-q">
            What exactly is a UNIT worker?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">A UNIT worker is an AI system built around a specific job — like managing renewals or organizing documents. It runs autonomously using your existing workflow, handles the task end-to-end, and reports back to you. Think of it as a trained team member that never needs reminders.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            Do I need to set it up every day?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">No. You configure it once during onboarding. After that, your worker runs on its own schedule — checking for new work, making decisions, and completing tasks without you needing to prompt it each time.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            How does a worker know what to do?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">Each worker is trained around a specific workflow — AVA, for example, reads Gmail, classifies renewal emails, and drafts responses using your templates and client history. The worker learns your patterns and improves every time it runs.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            What if the worker makes a mistake?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">Workers flag anything they're uncertain about for your review before taking action. Nothing gets sent or finalized without your approval on edge cases. You stay in control — the worker just handles the volume.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            Can I have more than one worker?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">Yes. You can deploy multiple workers, each handling a different function. They can also work together — for example, AVA can hand off a document to DOX after closing a renewal. Start with one, add more as your team grows.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            How much does it cost?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">Each worker has its own subscription plan. You can start with a free trial and upgrade as you see results. No credit card required to begin. Visit the pricing page for full details.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-sec">
  <div class="w">
    <div class="cta-card">
      <div class="cta-left">
        <div class="cta-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="1.5" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div class="cta-text">
          <h2>Ready to put AI workers to work for you?</h2>
          <p>Start with one. Add more as you grow.</p>
        </div>
      </div>
      <div class="cta-right">
        <a href="{{ route('register') }}" class="btn-cta-main">
          Hire Your First Worker
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <span class="cta-note">No credit card required.</span>
      </div>
    </div>
  </div>
</section>

<!-- TRUST BAR -->
<section class="trust-bar">
  <div class="w">
    <div class="trust-bar-i">
      <div class="trust-left">
        <div class="trust-lbl2">Trusted by businesses of all sizes</div>
        <div class="trust-score">
          <div class="trust-avs">
            <img src="/images/ava.png" alt="">
            <img src="/images/ava.png" alt="" style="filter:hue-rotate(30deg)">
            <img src="/images/ava.png" alt="" style="filter:hue-rotate(60deg)">
            <img src="/images/ava.png" alt="" style="filter:hue-rotate(200deg)">
          </div>
          <span class="trust-stars2">★★★★★</span>
          <span class="trust-score-txt">4.9/5 from 1,200+ reviews</span>
        </div>
      </div>
      <div class="trust-platforms">
        <div class="trust-platform">
          <div class="trust-platform-name">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#007FA8"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            Capterra
          </div>
          <div class="trust-platform-stars">★★★★★</div>
        </div>
        <div class="trust-platform">
          <div class="trust-platform-name" style="font-size:15px;font-weight:800">G<span style="color:#E8170E">2</span></div>
          <div class="trust-platform-stars">★★★★★</div>
        </div>
        <div class="trust-platform">
          <div class="trust-platform-name">
            <span style="color:#4285F4">G</span><span style="color:#EA4335">o</span><span style="color:#FBBC05">o</span><span style="color:#4285F4">g</span><span style="color:#34A853">l</span><span style="color:#EA4335">e</span>
          </div>
          <div class="trust-platform-stars">★★★★★</div>
        </div>
        <div class="trust-platform">
          <div class="trust-platform-name">
            <span style="color:#00B67A">★</span> Trustpilot
          </div>
          <div class="trust-platform-stars">★★★★★</div>
        </div>
        <div class="trust-platform">
          <div class="trust-platform-name">GetApp</div>
          <div class="trust-platform-stars">★★★★★</div>
        </div>
      </div>
    </div>
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
          <a href="{{ route('workers.public.show2', 'ava') }}">AVA — Renewal Coordinator</a>
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

// FAQ accordion
document.querySelectorAll('.faq-q').forEach(btn => {
  btn.addEventListener('click', () => {
    const item = btn.closest('.faq-item');
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
    if(!isOpen) item.classList.add('open');
  });
});

// Theme toggle
(function(){
  const html = document.documentElement;
  const btn = document.getElementById('theme-toggle');
  const saved = localStorage.getItem('unit-theme');
  if(saved === 'dark') html.setAttribute('data-theme','dark');
  btn.addEventListener('click', function(){
    const isDark = html.getAttribute('data-theme') === 'dark';
    if(isDark){
      html.removeAttribute('data-theme');
      localStorage.setItem('unit-theme','light');
    } else {
      html.setAttribute('data-theme','dark');
      localStorage.setItem('unit-theme','dark');
    }
  });
})();

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

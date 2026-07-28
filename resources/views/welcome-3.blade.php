<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>UNIT — AI Agents That Complete Real Business Work</title>
<meta name="description" content="Deploy specialized AI agents that own complete business workflows. UNIT AI Workers manage renewals, documents, publishing, compliance, and more while keeping humans in control.">
<link rel="icon" type="image/png" href="/logo.png">
<link rel="apple-touch-icon" href="/logo.png">
@include('partials.seo-meta', [
    'title'       => 'UNIT — AI Agents That Complete Real Business Work',
    'description' => 'Deploy specialized AI agents that own complete business workflows. UNIT AI Workers manage renewals, documents, publishing, compliance, and more while keeping humans in control.',
    'image'       => asset('images/hero-team-2.png'),
])
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
[data-theme="dark"] .cta-card{background:#161616}
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
.hero-proof{display:flex;align-items:center;gap:10px}
.proof-dot{
  width:9px;height:9px;border-radius:50%;flex-shrink:0;
  background:#22C55E;box-shadow:0 0 8px rgba(34,197,94,.7);
}
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
  display:grid;grid-template-columns:repeat(2,1fr);
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
.badge-case{background:rgba(245,158,11,.1);color:#D97706}
[data-theme="dark"] .badge-case{background:rgba(245,158,11,.2);color:#FCD34D}
.badge-story{background:rgba(100,116,139,.12);color:#475569}
[data-theme="dark"] .badge-story{background:rgba(148,163,184,.2);color:#CBD5E1}
.badge-workflow{background:rgba(13,148,136,.1);color:#0D9488}
[data-theme="dark"] .badge-workflow{background:rgba(45,212,191,.2);color:#5EEAD4}

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
.feat-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:clamp(18px,2.4vw,28px)}
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
  background:var(--brand-soft);
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

/* ── PROBLEMS WE SOLVE ── */
.problems-sec{background:#fff}
[data-theme="dark"] .problems-sec{background:#0D0D0D}
.problems-layout{
  display:grid;grid-template-columns:1.5fr 1fr;
  gap:clamp(24px,3vw,40px);
  margin-top:clamp(32px,4vw,48px);
  align-items:stretch;
}
.problems-grid{
  display:grid;grid-template-columns:repeat(2,1fr);
  border-top:1px solid var(--border);
}
.problem-col{padding:32px 28px;border-left:1px solid var(--border)}
.problem-col:first-child{border-left:none}
.problem-item + .problem-item{margin-top:36px}
.problem-icon{width:26px;height:26px;margin-bottom:18px;color:#0D0D0D}
[data-theme="dark"] .problem-icon{color:#F3F4F6}
.problem-icon svg{width:100%;height:100%}
.problem-text{border-left:2px solid #0D0D0D;padding-left:14px}
[data-theme="dark"] .problem-text{border-left-color:#F3F4F6}
.problem-text h4{font-size:1.05rem;font-weight:700;color:var(--text);margin-bottom:8px}
.problem-text p{font-size:13.5px;color:var(--t3);line-height:1.65}

/* ── PROBLEMS VIDEO CAROUSEL ── */
.problems-video{position:relative;min-height:100%}
.pv-slides{
  display:flex;height:100%;min-height:380px;overflow-x:auto;
  scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;
  scrollbar-width:none;-ms-overflow-style:none;
  border-radius:20px;background:#0D0D0D;
}
.pv-slides::-webkit-scrollbar{display:none}
.pv-slide{
  flex:0 0 100%;scroll-snap-align:start;position:relative;
  display:flex;align-items:center;justify-content:center;
}
.pv-play{
  width:56px;height:56px;border-radius:50%;
  background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.4);
  display:flex;align-items:center;justify-content:center;
}
.pv-play svg{width:20px;height:20px;fill:#fff;margin-left:3px}
.pv-caption{position:absolute;left:18px;bottom:18px}
.pv-caption-txt{
  background:#fff;color:#0D0D0D;font-size:12.5px;font-weight:700;
  padding:9px 15px;border-radius:99px;white-space:nowrap;
}
.pv-dots{position:absolute;bottom:18px;right:18px;display:flex;gap:5px;z-index:4}
.pv-dot{width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.35)}
.pv-dot.active{background:#fff}
.pv-arrow{
  position:absolute;top:50%;transform:translateY(-50%);
  width:36px;height:36px;border-radius:50%;
  background:#fff;border:1.5px solid var(--border);
  box-shadow:0 2px 10px rgba(0,0,0,.15);
  display:flex;align-items:center;justify-content:center;
  z-index:5;color:#0D0D0D;
}
.pv-arrow:hover{border-color:#999}
.pv-arrow svg{width:15px;height:15px}
.pv-prev{left:-16px}
.pv-next{right:-16px}

/* ── INDUSTRIES ── */
.industries-sec{background:#fff}
[data-theme="dark"] .industries-sec{background:#0D0D0D}
.industries-tags{display:flex;flex-wrap:wrap;gap:10px;margin-top:clamp(24px,3vw,36px)}

/* ── WHY AI AGENTS COMPARISON ── */
.compare-sec{background:var(--soft)}
[data-theme="dark"] .compare-sec{background:#0D0D0D}
.compare-grid{
  display:grid;grid-template-columns:repeat(3,1fr);
  border:1px solid var(--border);border-radius:20px;overflow:hidden;
  margin-top:clamp(32px,4vw,48px);background:#fff;
}
[data-theme="dark"] .compare-grid{background:#161616}
.compare-col{border-right:1px solid var(--border)}
.compare-col:last-child{border-right:none}
.compare-col.hl{background:#0D0D0D}
.compare-head{
  padding:24px 16px 18px;font-family:var(--font-h);font-size:14.5px;font-weight:800;
  border-bottom:1px solid var(--border);text-align:center;color:var(--text);
}
.compare-icon{width:26px;height:26px;margin:0 auto 12px;color:var(--text);flex-shrink:0}
.compare-icon svg{width:100%;height:100%}
.compare-hint{display:none}
.compare-col.hl .compare-head{color:#fff;border-bottom-color:rgba(255,255,255,.15)}
.compare-col.hl .compare-icon{color:#fff}
.compare-row{
  padding:16px;font-size:13px;color:var(--t2);
  border-bottom:1px solid var(--border);text-align:center;
}
.compare-col.hl .compare-row{color:#fff;border-bottom-color:rgba(255,255,255,.1)}
.compare-row:last-child{border-bottom:none}

/* ── SECURITY ── */
.security-sec{background:#fff}
[data-theme="dark"] .security-sec{background:#0D0D0D}
.security-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-top:clamp(32px,4vw,48px)}
.security-item{text-align:center;padding:20px 14px}
.security-icon{
  width:44px;height:44px;border-radius:12px;
  background:rgba(0,0,0,.05);color:#0D0D0D;
  display:flex;align-items:center;justify-content:center;
  margin:0 auto 14px;
}
[data-theme="dark"] .security-icon{background:rgba(255,255,255,.08);color:#F3F4F6}
.security-icon svg{width:22px;height:22px}
.security-item h4{font-size:13.5px;font-weight:700;color:var(--text);margin-bottom:5px}
.security-item p{font-size:12px;color:var(--t3);line-height:1.55}

/* ── AI WORKER LIFECYCLE (OPERATING LOOP) ── */
.ops-sec{background:var(--soft)}
[data-theme="dark"] .ops-sec{background:#161616}
.ops-flow{
  display:flex;flex-wrap:wrap;align-items:center;justify-content:center;
  gap:6px;margin-top:clamp(32px,4vw,48px);
}
.ops-step{
  display:flex;align-items:center;gap:8px;
  padding:11px 16px;border-radius:99px;
  background:#fff;border:1px solid var(--border);
  font-size:13px;font-weight:700;color:var(--text);
  white-space:nowrap;
}
[data-theme="dark"] .ops-step{background:#111}
.ops-step-num{
  width:20px;height:20px;border-radius:50%;flex-shrink:0;
  background:#0D0D0D;color:#fff;
  font-size:10px;font-weight:800;
  display:flex;align-items:center;justify-content:center;
}
.ops-arrow{color:var(--t4);font-size:14px;flex-shrink:0}
.ops-loop-note{width:100%;text-align:center;margin-top:14px;font-size:12.5px;color:var(--t3)}

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
  .compare-wrap{position:relative}
  .compare-wrap::after{
    content:'';position:absolute;right:0;top:0;bottom:0;width:36px;
    background:linear-gradient(to left,var(--soft),transparent);
    pointer-events:none;
  }
  [data-theme="dark"] .compare-wrap::after{background:linear-gradient(to left,#0D0D0D,transparent)}
  .compare-grid{
    display:flex;overflow-x:auto;
    scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;
    scrollbar-width:none;-ms-overflow-style:none;
  }
  .compare-grid::-webkit-scrollbar{display:none}
  .compare-col{
    flex:0 0 66%;min-width:210px;
    border-right:1px solid var(--border);
    scroll-snap-align:start;
  }
  .compare-col:last-child{border-right:none}
  .compare-head{text-align:left;display:flex;align-items:center;gap:12px}
  .compare-icon{margin:0}
  .compare-row{text-align:left}
  .compare-hint{
    display:flex!important;align-items:center;gap:6px;justify-content:center;
    font-size:12px;color:var(--t3);margin-top:14px;
  }
  .compare-hint svg{width:13px;height:13px}
  .problems-layout{grid-template-columns:1fr}
  .problems-grid{grid-template-columns:1fr}
  .problem-col{border-left:none;border-top:1px solid var(--border)}
  .problem-col:first-child{border-top:none}
  .pv-slides{min-height:280px}
  .pv-prev{left:8px}
  .pv-next{right:8px}
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

@php
  // Nav CTA should never tell an already-onboarded, logged-in user to
  // "Hire Your First Worker" or "Log in" — both are wrong once they have
  // an account and/or a deployed worker.
  $__navAvaHasDesk = auth()->check() && \Illuminate\Support\Facades\DB::table('worker_deployments')
    ->where('user_id', auth()->id())->where('worker_slug', 'ava')
    ->whereIn('status', ['active', 'paused'])->exists();
@endphp

<x-public-nav :links="[
  ['label' => 'Meet the AI Worker', 'href' => route('public.workers.index')],
  ['label' => 'How It Works',     'href' => '#timeline'],
  ['label' => 'Resources',        'href' => '#resources'],
  ['label' => 'Pricing',          'href' => route('pricing')],
]">
  <x-slot:cta>
    @if($__navAvaHasDesk)
    <a href="{{ route('app.desk.ava') }}" class="btn-cta">
      Go to AVA's Desk
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
    @else
    <a href="{{ route('hire.ava.welcome') }}" class="btn-cta">
      Deploy AVA
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
    @endif
  </x-slot>
  <x-slot:mobileCta>
    @if($__navAvaHasDesk)
    <a href="{{ route('app.desk.ava') }}" class="btn-cta" style="justify-content:center">Go to AVA's Desk →</a>
    @else
    <a href="{{ route('hire.ava.welcome') }}" class="btn-cta" style="justify-content:center">Deploy AVA →</a>
    @endif
  </x-slot>
</x-public-nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-left">
    <div class="hero-left-inner">
      <h1 class="hero-h">
        Deploy AI Agents<br>
        that complete<br>
        <em>real business work.</em>
      </h1>
      <p class="hero-p">
        UNIT is an AI agent platform where every AI agent is designed as a specialized AI Worker. Instead of answering questions, UNIT agents monitor, execute, and complete entire business workflows — from renewals and documents to publishing and compliance — while keeping your team in control.
      </p>
      <div class="hero-btns">
        <a href="#workers" class="btn-hero">
          Meet the AI Agents
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('hire.ava.welcome') }}" class="btn-hero-ghost">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z" fill="currentColor" stroke="none"/></svg>
          Watch AVA Complete a Renewal
        </a>
      </div>
      <div class="hero-proof">
        <span class="proof-dot"></span>
        <p class="proof-txt">
          <strong>AVA is live</strong> and managing renewal workflows for real businesses today
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
{{-- AVA-only: DOX/MOX/NUX are not live yet (see worker cards below), so the
     ticker only shows real workflow stages the live AVA pipeline performs —
     no fabricated activity for Coming Soon workers. --}}
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
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Follow-up sent to Sunrise LLC · renewal confirmed</span>
      <span class="feed-time">8m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot blue"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Gmail inbox synced · 2 new renewals detected</span>
      <span class="feed-time">12m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Invoice prepared for Meridian Realty</span>
      <span class="feed-time">19m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Payment confirmed · Lakeside Partners</span>
      <span class="feed-time">27m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot amber"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Renewal flagged for review · unusual terms detected</span>
      <span class="feed-time">34m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Renewal record updated · Crestview Holdings</span>
      <span class="feed-time">41m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">3 renewals processed before 9 AM · zero missed</span>
      <span class="feed-time">today</span>
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
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Follow-up sent to Sunrise LLC · renewal confirmed</span>
      <span class="feed-time">8m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot blue"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Gmail inbox synced · 2 new renewals detected</span>
      <span class="feed-time">12m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Invoice prepared for Meridian Realty</span>
      <span class="feed-time">19m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Payment confirmed · Lakeside Partners</span>
      <span class="feed-time">27m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot amber"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Renewal flagged for review · unusual terms detected</span>
      <span class="feed-time">34m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">Renewal record updated · Crestview Holdings</span>
      <span class="feed-time">41m ago</span>
    </div>
    <div class="feed-item">
      <span class="feed-dot green"></span>
      <span class="feed-worker" style="color:#0D0D0D">AVA</span>
      <span class="feed-action">3 renewals processed before 9 AM · zero missed</span>
      <span class="feed-time">today</span>
    </div>
  </div>
</div>

<!-- PROBLEMS WE SOLVE -->
<section class="problems-sec sec">
  <div class="w">
    <div style="margin-bottom:0;max-width:640px">
      <div class="sec-eye">Problems we solve</div>
      <h2 class="sec-h">The Problems UNIT AI Agents Solve</h2>
      <p class="sec-p">Before you evaluate a product, you're trying to name a problem. These are the ones UNIT AI Workers are built to own.</p>
    </div>
    <div class="problems-layout">
      <div class="problems-grid">
        <div class="problem-col">
          <div class="problem-item">
            <div class="problem-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="problem-text">
              <h4>Missed Renewals</h4>
              <p>Expirations slip through the cracks when tracking lives in someone's inbox.</p>
            </div>
          </div>
          <div class="problem-item">
            <div class="problem-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
            </div>
            <div class="problem-text">
              <h4>Manual Follow-ups</h4>
              <p>Reminders get sent late — or not at all — when they depend on someone remembering.</p>
            </div>
          </div>
          <div class="problem-item">
            <div class="problem-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
            </div>
            <div class="problem-text">
              <h4>Repetitive Operations</h4>
              <p>The same task, done the same way, hundreds of times a month.</p>
            </div>
          </div>
        </div>
        <div class="problem-col">
          <div class="problem-item">
            <div class="problem-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            </div>
            <div class="problem-text">
              <h4>Document Bottlenecks</h4>
              <p>Files pile up faster than anyone has time to organize them.</p>
            </div>
          </div>
          <div class="problem-item">
            <div class="problem-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <div class="problem-text">
              <h4>Publishing Delays</h4>
              <p>Content sits in drafts because there's no one to push it live.</p>
            </div>
          </div>
          <div class="problem-item">
            <div class="problem-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c1.5 0 2.9.37 4.14 1.02"/></svg>
            </div>
            <div class="problem-text">
              <h4>Compliance Tracking</h4>
              <p>Deadlines and filings are easy to lose track of under deadline pressure.</p>
            </div>
          </div>
        </div>
      </div>

      {{-- AVA is the only live worker, so every clip is a real AVA task —
           no placeholder footage implying DOX/MOX/NUX are already working. --}}
      <div class="problems-video">
        <button class="pv-arrow pv-prev" aria-label="Previous video">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <div class="pv-slides">
          <div class="pv-slide">
            <div class="pv-play"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div>
            <div class="pv-caption"><span class="pv-caption-txt">How AVA handles a renewal</span></div>
          </div>
          <div class="pv-slide">
            <div class="pv-play"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div>
            <div class="pv-caption"><span class="pv-caption-txt">How AVA follows up with a client</span></div>
          </div>
          <div class="pv-slide">
            <div class="pv-play"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div>
            <div class="pv-caption"><span class="pv-caption-txt">How AVA confirms a payment</span></div>
          </div>
        </div>
        <button class="pv-arrow pv-next" aria-label="Next video">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div class="pv-dots">
          <span class="pv-dot active"></span>
          <span class="pv-dot"></span>
          <span class="pv-dot"></span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- WORKERS -->
<section class="workers sec" id="workers">
  <div class="w">
    <div class="center" style="margin-bottom:clamp(36px,5vw,56px)">
      <div class="sec-eye">Meet the AI agents</div>
      <h2 class="sec-h">Specialized AI Agents.<br>Complete Business Workflows.</h2>
      <p class="sec-p">Every UNIT AI Worker owns one operational responsibility from start to finish. Instead of doing a little of everything, each worker becomes exceptional at one business workflow.</p>
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
          <div class="wk-role">AI Renewal Agent</div>
          <p class="wk-quote">Owns your complete renewal lifecycle.</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Tracks upcoming expirations</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Starts renewal campaigns</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Follows up automatically</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Supports invoicing</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Tracks payment confirmation</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Updates renewal records</div>
          </div>
          @php
            $avaHasDesk = auth()->check() && DB::table('worker_deployments')
              ->where('user_id', auth()->id())->where('worker_slug', 'ava')
              ->whereIn('status', ['active', 'paused'])->exists();
          @endphp
          <div class="wk-btns">
            <a href="{{ route('public.workers.show', 'ava') }}" class="btn-wk-outline">Watch Ava's Day <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            @if($avaHasDesk)
              <a href="{{ route('app.desk.ava') }}" class="btn-wk">AVA's Desk <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            @else
              <a href="{{ route('hire.ava.welcome') }}" class="btn-wk">Deploy AVA <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            @endif
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
            <div class="wk-name">DOX <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:99px;background:rgba(245,197,24,.15);color:#8a6a06;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;vertical-align:middle">Coming Soon</span></div>
          </div>
          <div class="wk-role">AI Document Agent</div>
          <p class="wk-quote">Owns document organization, retrieval, and structured workflows.</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Organizes files</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Finds what's lost</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Structures systems</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates order</div>
          </div>
          <div class="wk-btns">
            <span class="btn-wk" style="opacity:.5;cursor:default;pointer-events:none">Coming Soon</span>
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
            <div class="wk-name">MOX <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:99px;background:rgba(245,197,24,.15);color:#8a6a06;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;vertical-align:middle">Coming Soon</span></div>
          </div>
          <div class="wk-role">AI Brand Intelligence Agent</div>
          <p class="wk-quote">Finds high-value brand opportunities worth acting on.</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Finds brand moments</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Tracks opportunities</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates campaigns</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Delivers impact</div>
          </div>
          <div class="wk-btns">
            <span class="btn-wk" style="opacity:.5;cursor:default;pointer-events:none">Coming Soon</span>
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
            <div class="wk-name">NUX <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:99px;background:rgba(245,197,24,.15);color:#8a6a06;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;vertical-align:middle">Coming Soon</span></div>
          </div>
          <div class="wk-role">AI Publishing Agent</div>
          <p class="wk-quote">Owns your publishing workflow from draft to distribution.</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates content</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Repurposes ideas</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Publishes daily</div>
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Grows your reach</div>
          </div>
          <div class="wk-btns">
            <span class="btn-wk" style="opacity:.5;cursor:default;pointer-events:none">Coming Soon</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- WHY AI AGENTS -->
<section class="compare-sec sec">
  <div class="w">
    <div class="center" style="margin-bottom:0">
      <div class="sec-eye">Why AI agents</div>
      <h2 class="sec-h">Why Businesses Are Replacing<br>Repetitive Work with <em class="hl">AI Agents</em></h2>
    </div>
    <div class="compare-wrap">
      <div class="compare-grid">
        <div class="compare-col">
          <div class="compare-head">
            <div class="compare-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg></div>
            Traditional Software
          </div>
          <div class="compare-row">Waits for you to click</div>
          <div class="compare-row">One feature</div>
          <div class="compare-row">Doesn't follow up</div>
          <div class="compare-row">Tool</div>
        </div>
        <div class="compare-col">
          <div class="compare-head">
            <div class="compare-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg></div>
            AI Chatbots
          </div>
          <div class="compare-row">Waits for prompts</div>
          <div class="compare-row">One conversation</div>
          <div class="compare-row">Doesn't remember</div>
          <div class="compare-row">Assistant</div>
        </div>
        <div class="compare-col hl">
          <div class="compare-head">
            <div class="compare-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M13 2L4.09 12.11a1 1 0 00.76 1.65h5.91l-1 8.24 8.91-10.11a1 1 0 00-.76-1.65h-5.91z"/></svg></div>
            UNIT AI Workers
          </div>
          <div class="compare-row">Owns work proactively</div>
          <div class="compare-row">One workflow</div>
          <div class="compare-row">Tracks work until completion</div>
          <div class="compare-row">Worker</div>
        </div>
      </div>
    </div>
    <div class="compare-hint">
      Swipe to compare
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </div>
  </div>
</section>

<!-- TIMELINE -->
<section class="timeline-sec sec" id="timeline">
  <div class="w">
    <div class="center">
      <div class="sec-eye">A day inside UNIT</div>
      <h2 class="sec-h">While You Grow Your Business,<br>Your AI Agents Keep Working.</h2>
    </div>
    <div class="tl">
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </div>
        <div class="tl-time">Step 1</div>
        <div class="tl-evt"><strong>AVA detects</strong>a renewal approaching.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
        </div>
        <div class="tl-time">Step 2</div>
        <div class="tl-evt"><strong>AVA notifies</strong>the customer automatically.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M9 13h6M9 17h6"/></svg>
        </div>
        <div class="tl-time">Step 3</div>
        <div class="tl-evt"><strong>AVA prepares</strong>the invoice.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="tl-time">Step 4</div>
        <div class="tl-evt"><strong>AVA confirms</strong>payment.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
        </div>
        <div class="tl-time">Step 5</div>
        <div class="tl-evt"><strong>AVA updates</strong>the renewal record.</div>
      </div>
      <div class="tl-item">
        <div class="tl-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
        </div>
        <div class="tl-time">Step 6</div>
        <div class="tl-evt"><strong>AVA starts</strong>the next cycle.</div>
      </div>
    </div>
  </div>
</section>

<!-- INDUSTRIES -->
<section class="industries-sec sec">
  <div class="w">
    <div class="center" style="margin-bottom:0">
      <div class="sec-eye">Industries</div>
      <h2 class="sec-h">Built for Teams With Recurring,<br>Deadline-Driven Work</h2>
      <p class="sec-p">UNIT AI Workers are built for organizations where a missed deadline has a real cost — not generic office work.</p>
    </div>
    <div class="industries-tags" style="justify-content:center">
      <span class="what-pill">Insurance</span>
      <span class="what-pill">IT Services</span>
      <span class="what-pill">Digital Agencies</span>
      <span class="what-pill">Compliance</span>
      <span class="what-pill">Professional Services</span>
      <span class="what-pill">Accounting</span>
      <span class="what-pill">Law Firms</span>
      <span class="what-pill">Consultancies</span>
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
        <h2 class="sec-h">Learn How Businesses Use<br>AI Agents to Complete Real Work</h2>
      </div>
      <a href="{{ route('hire.ava.welcome') }}" class="btn-outline">
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
            <div class="res-title">5 signs your team is doing work an AI agent should be doing instead</div>
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
            <div class="res-title">The Future of Work: How AI Agents change what your team actually does</div>
            <div class="res-meta">14 pages · PDF download</div>
            <span class="res-link">Download <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
          </div>
        </div>

        <!-- Card 4: Case Study -->
        <div class="res-card">
          <div class="res-thumb" style="background:linear-gradient(135deg,#78350F,#B45309)">
            <div class="res-thumb-icon">
              <svg viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12l2 2 4-4"/></svg>
            </div>
          </div>
          <div class="res-body">
            <span class="res-badge badge-case">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
              Case Study
            </span>
            <div class="res-title">Inside a real renewal workflow: how AVA tracks, drafts, and follows up</div>
            <div class="res-meta">6 min read · Renewal management</div>
            <span class="res-link">Read case study <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
          </div>
        </div>

        <!-- Card 5: Workflow Breakdown -->
        <div class="res-card">
          <div class="res-thumb" style="background:linear-gradient(135deg,#134E4A,#0F766E)">
            <div class="res-thumb-icon">
              <svg viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round"><path d="M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01"/></svg>
            </div>
          </div>
          <div class="res-body">
            <span class="res-badge badge-workflow">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01"/></svg>
              Workflow Breakdown
            </span>
            <div class="res-title">AVA's renewal workflow, step by step: detect, draft, approve, send</div>
            <div class="res-meta">5 min read · AVA</div>
            <span class="res-link">See the breakdown <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
          </div>
        </div>

        <!-- Card 6: Customer Story -->
        <div class="res-card">
          <div class="res-thumb" style="background:linear-gradient(135deg,#1F2937,#374151)">
            <div class="res-thumb-icon">
              <svg viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
            </div>
          </div>
          <div class="res-body">
            <span class="res-badge badge-story">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
              Customer Story
            </span>
            <div class="res-title">What changes when AVA owns your renewal inbox</div>
            <div class="res-meta">6 min read · Customer story</div>
            <span class="res-link">Read the story <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
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
        <h2 class="sec-h">Every Worker Has a Life</h2>
        <p>Our AI Workers don't just complete work. They learn, teach, reflect, celebrate milestones, and share the experiences behind the job. Follow their stories as they grow alongside your business.</p>
        <a href="{{ route('hire.ava.welcome') }}" class="btn-outline">
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

<!-- SECURITY -->
<section class="security-sec sec">
  <div class="w">
    <div class="center" style="margin-bottom:0">
      <div class="sec-eye">Security</div>
      <h2 class="sec-h">Enterprise-Grade Security, Built In</h2>
      <p class="sec-p">The parts of the platform enterprise buyers ask about first.</p>
    </div>
    <div class="security-grid">
      <div class="security-item">
        <div class="security-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        </div>
        <h4>Tenant Isolation</h4>
        <p>Every tenant's data is scoped and walled off from every other tenant.</p>
      </div>
      <div class="security-item">
        <div class="security-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="16" r="1.5"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        </div>
        <h4>Encryption</h4>
        <p>Credentials and sensitive data are encrypted at rest and never exposed to the frontend.</p>
      </div>
      <div class="security-item">
        <div class="security-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <h4>Human Approval</h4>
        <p>Nothing ships without a human reviewing it first.</p>
      </div>
      <div class="security-item">
        <div class="security-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h6"/></svg>
        </div>
        <h4>Audit Logs</h4>
        <p>Every action a worker takes is logged for review.</p>
      </div>
      <div class="security-item">
        <div class="security-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 019.9-1"/><circle cx="12" cy="16" r="1.5"/></svg>
        </div>
        <h4>Secure Credentials</h4>
        <p>OAuth tokens are decrypted only at runtime — never passed through queues in plaintext.</p>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features">
  <div class="w">
    <h2 class="feat-h">Why Businesses Trust UNIT AI Agents</h2>
    <div class="feat-grid">
      <div class="feat-item">
        <div class="feat-icon" style="background:#ECFDF5">
          <svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="feat-body">
          <h4>Human Approval</h4>
          <p>Every important decision remains under your control.</p>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon" style="background:#EFF6FF">
          <svg viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h6"/></svg>
        </div>
        <div class="feat-body">
          <h4>Audit Trail</h4>
          <p>Every action is recorded.</p>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon" style="background:#FFF7ED">
          <svg viewBox="0 0 24 24" fill="none" stroke="#EA580C" stroke-width="2" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div class="feat-body">
          <h4>Continuous Operation</h4>
          <p>Workers never forget.</p>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon" style="background:#ECFEFF">
          <svg viewBox="0 0 24 24" fill="none" stroke="#0891B2" stroke-width="2" stroke-linecap="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div class="feat-body">
          <h4>Workflow Learning</h4>
          <p>Improves from completed work.</p>
        </div>
      </div>
      <div class="feat-item">
        <div class="feat-icon" style="background:rgba(0,0,0,.05)">
          <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div class="feat-body">
          <h4>Worker Collaboration</h4>
          <p>Multiple workers cooperate across business processes.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- AI WORKER LIFECYCLE (OPERATING LOOP) -->
<section class="ops-sec sec">
  <div class="w">
    <div class="center" style="margin-bottom:0">
      <div class="sec-eye">How it works</div>
      <h2 class="sec-h">Every AI Worker Follows<br>the Same Operating Loop</h2>
      <p class="sec-p">This is the loop every UNIT AI Worker runs — whether it's managing renewals today or a new workflow tomorrow.</p>
    </div>
    <div class="ops-flow">
      <div class="ops-step"><span class="ops-step-num">1</span>Monitor</div>
      <span class="ops-arrow">→</span>
      <div class="ops-step"><span class="ops-step-num">2</span>Detect</div>
      <span class="ops-arrow">→</span>
      <div class="ops-step"><span class="ops-step-num">3</span>Plan</div>
      <span class="ops-arrow">→</span>
      <div class="ops-step"><span class="ops-step-num">4</span>Execute</div>
      <span class="ops-arrow">→</span>
      <div class="ops-step"><span class="ops-step-num">5</span>Request Approval</div>
      <span class="ops-arrow">→</span>
      <div class="ops-step"><span class="ops-step-num">6</span>Complete</div>
      <span class="ops-arrow">→</span>
      <div class="ops-step"><span class="ops-step-num">7</span>Report</div>
      <span class="ops-arrow">→</span>
      <div class="ops-step"><span class="ops-step-num">8</span>Learn</div>
      <span class="ops-arrow">→</span>
      <div class="ops-step"><span class="ops-step-num">9</span>Monitor Again</div>
      <div class="ops-loop-note">The loop never stops — every completed task feeds directly back into monitoring the next one.</div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="faq-sec" id="faq">
  <div class="w">
    <div class="faq-grid">
      <div class="faq-left">
        <div class="sec-eye">FAQ</div>
        <h2 class="what-h" style="margin-bottom:16px">Not an app.<br>Not a bot.<br><em>An AI Agent.</em></h2>
        <p style="font-size:.975rem;color:var(--t3);line-height:1.75;margin-bottom:20px">A UNIT AI Worker is an AI agent built from your day-to-day workflow. It runs without hand-holding, handles its job end-to-end, and reports back to you — so you always know what was done and why.</p>
        <a href="{{ route('hire.ava.welcome') }}" class="btn-outline">
          Get started free
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
      <div class="faq-list">
        <div class="faq-item open">
          <button class="faq-q">
            What is an AI agent?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">An AI agent is software that takes real actions toward a goal — not just answers questions. Instead of waiting for a prompt, it monitors a workflow, makes decisions inside defined rules, and completes tasks end-to-end.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            What is an AI Worker?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">AI Worker is UNIT's name for the AI agents we build. Each AI Worker is a specialized agent trained for one job — like managing renewals — not a general-purpose assistant. It owns that workflow completely, from detection to completion.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            What's the difference between an AI agent and ChatGPT?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">ChatGPT waits for you to type a prompt and answers inside a conversation. A UNIT AI agent doesn't wait — it monitors your systems continuously, decides when action is needed, and executes a workflow on its own, only stopping to ask you for approval on the parts that matter.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            How do AI Workers work?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">Each AI Worker runs a defined pipeline — read the input, classify it, check memory and history, take action, and route the result to you for approval. AVA's renewal pipeline, for example, reads Gmail, classifies emails, drafts a response, and waits for your sign-off before anything goes out.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            Can AI agents send emails?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">Not without you. AVA drafts renewal emails and prepares them in Gmail, but UNIT never sends, submits, or transmits anything on your behalf. You review the draft and send it yourself — that's a deliberate design choice, not a limitation.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            Can AI agents monitor Gmail?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">Yes. AVA connects to your Gmail inbox through Google's official OAuth and watch APIs, reads incoming mail, and classifies renewal-related messages automatically — without you needing to forward or flag anything.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            Can AI agents work together?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">Yes — UNIT's AI Workers are built to hand off context to each other, like AVA closing a renewal and passing the related documents to a document-management worker. AVA is live today; the other workers in that handoff chain are in development.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            What business workflows can UNIT automate?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">AVA, live today, owns the full renewal workflow — tracking expirations, starting campaigns, following up, supporting invoicing, and updating records. Document management, brand monitoring, and content publishing workflows are in active development.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            Which industries use UNIT?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">UNIT is built for organizations with recurring, deadline-driven workflows — including insurance, IT services, digital agencies, compliance-heavy operations, professional services, accounting, law firms, and consultancies.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            Can I deploy multiple AI Workers?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">Yes. A single account can deploy multiple worker instances — even multiple deployments of the same worker across different inboxes or clients. Start with one and add more as your operation grows.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            Do AI agents replace employees?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">No. UNIT's AI Workers are built to own the repetitive parts of a workflow — tracking, drafting, following up — while every meaningful decision routes back to a human for approval. The goal is to remove busywork, not decision-making authority.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            How do approvals work?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">When a worker finishes a task, the result is routed to a review queue in your dashboard. You approve or reject each item — approving keeps the draft in place for you to act on, rejecting removes it. Nothing is sent or finalized without that step.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            How does AVA manage renewals?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">AVA reads your inbox, classifies renewal-related emails, checks your client and contract history, drafts a response using your templates, and puts it in your review queue. Once you approve, the draft is ready in Gmail for you to send.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            Does UNIT integrate with Gmail?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">Yes — Gmail is UNIT's first live integration. AVA connects via OAuth2 and Google's Pub/Sub webhook to watch your inbox and process renewal emails in real time.</div>
        </div>
        <div class="faq-item">
          <button class="faq-q">
            Can UNIT integrate with other systems?
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
          <div class="faq-a">Gmail is live today. Google Workspace, Microsoft 365, Google Drive, Calendar, and additional APIs are on the roadmap as UNIT's worker library grows.</div>
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
          <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="1.5" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div class="cta-text">
          <h2>Ready to Deploy Your First AI Agent?</h2>
          <p>Start with one specialized AI Worker. Add more as your business grows.</p>
        </div>
      </div>
      <div class="cta-right">
        @if($__navAvaHasDesk)
        <a href="{{ route('app.desk.ava') }}" class="btn-cta-main">
          Go to AVA's Desk
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        @else
        <a href="{{ route('hire.ava.welcome') }}" class="btn-cta-main">
          Deploy AVA
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <span class="cta-note">No credit card required.</span>
        @endif
      </div>
    </div>
  </div>
</section>

<!-- TRUST BAR -->
{{-- Relabeled: the previous version claimed a specific "4.9/5 from 1,200+
     reviews" and five review-platform badges (Capterra/G2/Google/Trustpilot/
     GetApp) UNIT is not actually listed on, plus one AVA headshot reused
     four times with hue-rotation to fake distinct avatars. Replaced with
     verifiable trust pillars instead of fabricated social proof. --}}
<section class="trust-bar">
  <div class="w">
    <div class="trust-bar-i">
      <div class="trust-left">
        <div class="trust-lbl2">Built for trust, not just automation</div>
        <div class="trust-score">
          <span class="proof-dot"></span>
          <span class="trust-score-txt">Every workflow stays human-reviewed and fully audited</span>
        </div>
      </div>
      <div class="trust-platforms">
        <div class="trust-platform">
          <div class="trust-platform-name">Human-Reviewed</div>
        </div>
        <div class="trust-platform">
          <div class="trust-platform-name">Audit Logged</div>
        </div>
        <div class="trust-platform">
          <div class="trust-platform-name">Data Encrypted</div>
        </div>
        <div class="trust-platform">
          <div class="trust-platform-name">Gmail OAuth Verified</div>
        </div>
        <div class="trust-platform">
          <div class="trust-platform-name">Tenant Isolated</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<x-public-footer />

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

// Problems-section video carousel
document.querySelectorAll('.problems-video').forEach(wrap => {
  const track = wrap.querySelector('.pv-slides');
  const dots  = wrap.querySelectorAll('.pv-dot');
  const prev  = wrap.querySelector('.pv-prev');
  const next  = wrap.querySelector('.pv-next');

  function goTo(i){ track.scrollTo({left: track.clientWidth * i, behavior:'smooth'}) }
  prev.addEventListener('click', () => track.scrollBy({left: -track.clientWidth, behavior:'smooth'}));
  next.addEventListener('click', () => track.scrollBy({left: track.clientWidth, behavior:'smooth'}));
  dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));

  track.addEventListener('scroll', () => {
    const i = Math.round(track.scrollLeft / track.clientWidth);
    dots.forEach((dot, idx) => dot.classList.toggle('active', idx === i));
  });
});
</script>

<x-self-learn />

@include('partials.tracking')
</body>
</html>

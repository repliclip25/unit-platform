<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $worker['name'] }} — {{ $worker['role'] }} | UNIT</title>
<meta name="description" content="{{ $worker['meta_desc'] }}">
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

@php
  $color    = '#F5C518';
  $colorRgb = '245,197,24';
@endphp

:root{
  --brand:{{ $color }};
  --brand-rgb:{{ $colorRgb }};
  --text:#0D0D0D;--t2:#374151;--t3:#6B7280;--t4:#9CA3AF;
  --border:#E5E7EB;--bg:#FFFFFF;--soft:#F8F8F6;
  --font:'Inter',sans-serif;
  --max:1160px;--pad:clamp(20px,5vw,48px);
}
[data-theme="dark"]{
  --text:#F3F4F6;--t2:#D1D5DB;--t3:#9CA3AF;--t4:#6B7280;
  --border:#2D2D2D;--bg:#0D0D0D;--soft:#161616;
}

body{font-family:var(--font);color:var(--text);background:var(--bg);-webkit-font-smoothing:antialiased;overflow-x:hidden}
.w{max-width:var(--max);margin:0 auto;padding:0 var(--pad)}
/* Gold underline — the ONLY use of --brand on this page */
.hl{position:relative;display:inline}
.hl::after{content:'';position:absolute;left:0;right:0;bottom:-3px;height:4px;background:var(--brand);border-radius:2px}

/* ── NAV ── */
.nav{
  position:fixed;top:0;left:0;right:0;z-index:100;
  background:#fff;
  border-bottom:1px solid #EBEBEB;
  box-shadow:0 1px 0 rgba(0,0,0,.04);
}
.nav-i{
  display:flex;align-items:center;justify-content:space-between;
  height:62px;padding:0 var(--pad);max-width:var(--max);margin:0 auto;
}
.nav-logo{
  font-size:1.25rem;font-weight:800;
  color:#0D0D0D;letter-spacing:-.5px;
  text-decoration:none;flex-shrink:0;
}
.nav-links{display:flex;align-items:center;gap:2px}
.nav-link{
  font-size:13.5px;font-weight:500;color:#374151;
  padding:7px 13px;border-radius:8px;
  transition:color .15s,background .15s;white-space:nowrap;
}
.nav-link:hover{color:#0D0D0D;background:#F5F5F5}
.nav-actions{display:flex;align-items:center;gap:8px}
.btn-theme{
  width:34px;height:34px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  border:1.5px solid #E5E7EB;background:#fff;
  color:#6B7280;cursor:pointer;flex-shrink:0;
  transition:border-color .15s,color .15s;
}
.btn-theme:hover{border-color:#9CA3AF;color:#374151}
.btn-theme svg{width:15px;height:15px}
.btn-login{
  display:inline-flex;align-items:center;
  padding:8px 18px;border-radius:99px;
  font-size:13.5px;font-weight:600;color:#374151;
  border:1.5px solid #D1D5DB;background:#fff;
  transition:border-color .15s,color .15s;
}
.btn-login:hover{border-color:#9CA3AF;color:#0D0D0D}
.btn-nav-hire{
  display:inline-flex;align-items:center;gap:7px;
  padding:9px 20px;border-radius:99px;
  font-size:13.5px;font-weight:700;color:#fff;
  background:#0D0D0D;
  transition:opacity .15s,transform .15s;white-space:nowrap;
}
.btn-nav-hire:hover{opacity:.9;transform:translateY(-1px)}
.btn-nav-hire svg{flex-shrink:0;transition:transform .15s}
.btn-nav-hire:hover svg{transform:translateX(2px)}
@media(max-width:960px){.nav-links{display:none}}
@media(max-width:600px){.btn-login,.btn-theme{display:none}}

/* ── HERO ── */
.hero-worker{
  display:flex;flex-direction:row;
  height:calc(100vh - 62px);
  min-height:560px;
  margin-top:62px;
  background:#0A0A0F;
  overflow:hidden;
}

/* LEFT: video column */
.hero-video-col{
  position:relative;
  flex:1;min-width:0;
  display:flex;flex-direction:column;
  overflow:hidden;
}
.hero-media{
  position:absolute;inset:0;
  display:flex;
}
.hero-media video,
.hero-media img{
  width:100%;height:100%;
  object-fit:cover;object-position:center top;
}
/* gradient: left side dark for text readability */
.hero-media::after{
  content:'';position:absolute;inset:0;
  background:
    linear-gradient(to right, rgba(6,4,15,.88) 0%, rgba(6,4,15,.55) 35%, rgba(6,4,15,.1) 62%, transparent 80%),
    linear-gradient(to top, rgba(6,4,15,.6) 0%, transparent 35%);
}
/* text content over the video */
.hero-text{
  position:relative;z-index:2;
  flex:1;display:flex;flex-direction:column;justify-content:center;
  padding:clamp(32px,5vw,56px) clamp(28px,4vw,48px);
  padding-bottom:16px;
}
.hero-eye{
  font-size:11px;font-weight:700;letter-spacing:.16em;
  text-transform:uppercase;color:rgba(255,255,255,.5);
  margin-bottom:14px;
}
.hero-h{
  font-size:clamp(2rem,4.2vw,3.4rem);
  font-weight:800;line-height:1.06;
  letter-spacing:-.03em;color:#fff;
  margin-bottom:16px;
}
.hero-p{
  font-size:.95rem;color:rgba(255,255,255,.7);
  line-height:1.75;margin-bottom:28px;max-width:400px;
}
.hero-btns{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.btn-hire-hero{
  display:inline-flex;align-items:center;gap:8px;
  padding:12px 22px;border-radius:10px;
  font-size:14.5px;font-weight:700;color:#fff;
  background:#0D0D0D;
  transition:opacity .15s,transform .15s;
}
.btn-hire-hero:hover{opacity:.9;transform:translateY(-1px)}
.btn-watch-hero{
  display:inline-flex;align-items:center;gap:9px;
  padding:11px 18px;border-radius:10px;
  font-size:14px;font-weight:600;color:rgba(255,255,255,.88);
  border:1.5px solid rgba(255,255,255,.22);
  transition:border-color .15s,background .15s;
}
.btn-watch-hero:hover{border-color:rgba(255,255,255,.5);background:rgba(255,255,255,.05)}
.btn-watch-icon{
  width:26px;height:26px;border-radius:50%;
  border:1.5px solid rgba(255,255,255,.4);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.btn-watch-icon svg{width:10px;height:10px;fill:#fff;margin-left:2px}
/* video controls bar — bottom of video col */
.hero-vidbar{
  position:relative;z-index:3;
  display:flex;align-items:center;gap:14px;
  padding:10px clamp(28px,4vw,48px);
  background:rgba(0,0,0,.5);
  backdrop-filter:blur(6px);
  border-top:1px solid rgba(255,255,255,.05);
  flex-shrink:0;
}
.vidbar-play{
  width:28px;height:28px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;cursor:pointer;
}
.vidbar-play svg{width:16px;height:16px;fill:#fff;margin-left:2px}
.vidbar-time{font-size:12px;color:rgba(255,255,255,.6);font-variant-numeric:tabular-nums;white-space:nowrap}
.vidbar-track{flex:1;height:3px;background:rgba(255,255,255,.18);border-radius:99px;position:relative;cursor:pointer}
.vidbar-fill{height:100%;background:var(--brand);border-radius:99px;width:19%}
.vidbar-thumb{
  position:absolute;top:50%;left:19%;transform:translate(-50%,-50%);
  width:13px;height:13px;border-radius:50%;
  background:#fff;box-shadow:0 0 4px rgba(0,0,0,.5);
}
.vidbar-icons{display:flex;align-items:center;gap:14px;flex-shrink:0}
.vidbar-icons svg{width:17px;height:17px;stroke:rgba(255,255,255,.65);fill:none;stroke-width:1.8;cursor:pointer;transition:stroke .12s}
.vidbar-icons svg:hover{stroke:#fff}

/* RIGHT: status panel — separate column, not overlay */
.hero-panel{
  width:300px;flex-shrink:0;
  background:#111118;
  border-left:1px solid rgba(255,255,255,.07);
  display:flex;flex-direction:column;
  padding:32px 24px 24px;
  overflow-y:auto;
}
.hc-status{display:flex;align-items:center;gap:8px;margin-bottom:20px}
.hc-dot{
  width:8px;height:8px;border-radius:50%;
  background:#22C55E;box-shadow:0 0 8px rgba(34,197,94,.8);
  flex-shrink:0;animation:pulse 2s ease infinite;
}
@keyframes pulse{0%,100%{opacity:1;box-shadow:0 0 8px rgba(34,197,94,.8)}50%{opacity:.7;box-shadow:0 0 16px rgba(34,197,94,.4)}}
.hc-status-txt{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#22C55E}
.hc-time{
  font-size:2.4rem;font-weight:800;color:#fff;
  letter-spacing:-.04em;line-height:1;
  font-variant-numeric:tabular-nums;margin-bottom:20px;
}
.hc-task-label{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:8px}
.hc-task-name{font-size:13.5px;font-weight:500;color:rgba(255,255,255,.85);line-height:1.5;margin-bottom:12px}
.hc-task-icon{
  width:40px;height:40px;border-radius:10px;
  border:1px solid rgba(255,255,255,.15);
  background:rgba(255,255,255,.08);
  display:flex;align-items:center;justify-content:center;
  margin-bottom:20px;
}
.hc-task-icon svg{width:20px;height:20px;stroke:#fff;fill:none;stroke-width:1.8}
.hc-divider{height:1px;background:rgba(255,255,255,.07);margin:4px 0 16px}
.hc-completed-label{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:10px}
.hc-done-item{display:flex;align-items:center;gap:9px;margin-bottom:8px;font-size:13px;color:rgba(255,255,255,.8)}
.hc-check{
  width:20px;height:20px;border-radius:50%;flex-shrink:0;
  border:1.5px solid #22C55E;
  display:flex;align-items:center;justify-content:center;
}
.hc-check svg{width:10px;height:10px;stroke:#22C55E;stroke-width:2.5;fill:none}
.hc-revenue-label{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-top:20px;margin-bottom:6px}
.hc-revenue-amount{font-size:2rem;font-weight:800;color:#fff;letter-spacing:-.04em;line-height:1;margin-bottom:4px}
.hc-revenue-streak{font-size:13px;color:rgba(255,255,255,.5);margin-bottom:20px}
.hc-feed-btn{
  display:flex;align-items:center;justify-content:center;gap:7px;
  width:100%;padding:11px 16px;
  border-radius:10px;
  font-size:13.5px;font-weight:600;color:rgba(255,255,255,.8);
  border:1px solid rgba(255,255,255,.14);
  background:rgba(255,255,255,.04);
  transition:all .15s;margin-top:auto;
}
.hc-feed-btn:hover{background:rgba(255,255,255,.09);color:#fff}
@media(max-width:900px){
  .hero-worker{flex-direction:column;height:auto}
  .hero-video-col{min-height:55vh}
  .hero-panel{width:100%;border-left:none;border-top:1px solid rgba(255,255,255,.07);flex-direction:row;flex-wrap:wrap;gap:20px;padding:20px}
}
@media(max-width:600px){
  .hero-panel{flex-direction:column}
}

/* hero status card */
.hero-card{
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12);
  border-radius:20px;
  padding:24px;
  backdrop-filter:blur(20px);
}
.hc-status{display:flex;align-items:center;gap:8px;margin-bottom:18px}
.hc-dot{width:8px;height:8px;border-radius:50%;background:#22C55E;box-shadow:0 0 8px rgba(34,197,94,.8);flex-shrink:0;animation:pulse 2s ease infinite}
@keyframes pulse{0%,100%{opacity:1;box-shadow:0 0 8px rgba(34,197,94,.8)}50%{opacity:.7;box-shadow:0 0 16px rgba(34,197,94,.5)}}
.hc-status-txt{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#22C55E}
.hc-time{font-size:2.2rem;font-weight:800;color:#fff;letter-spacing:-.04em;margin-bottom:4px;font-variant-numeric:tabular-nums}
.hc-task-label{font-size:10.5px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:8px;margin-top:16px}
.hc-task{
  background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
  border-radius:12px;padding:12px 14px;margin-bottom:16px;
}
.hc-task-name{font-size:13px;font-weight:600;color:#fff;margin-bottom:8px}
.hc-progress{height:4px;background:rgba(255,255,255,.1);border-radius:99px;overflow:hidden}
.hc-progress-bar{height:100%;background:rgba(255,255,255,.7);border-radius:99px;width:68%;animation:progAnim 3s ease-in-out infinite alternate}
@keyframes progAnim{from{width:55%}to{width:82%}}
.hc-divider{height:1px;background:rgba(255,255,255,.08);margin:14px 0}
.hc-completed-label{font-size:10.5px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:10px}
.hc-done-item{display:flex;align-items:center;gap:8px;margin-bottom:7px;font-size:13px;color:rgba(255,255,255,.8)}
.hc-check{width:18px;height:18px;border-radius:50%;background:#22C55E;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.hc-check svg{width:10px;height:10px;stroke:#fff;stroke-width:2.5;fill:none}
.hc-revenue{
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.1);
  border-radius:12px;padding:14px;margin-top:14px;
}
.hc-rev-label{font-size:10.5px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:6px}
.hc-rev-amount{font-size:1.6rem;font-weight:800;color:#fff;letter-spacing:-.04em;line-height:1}
.hc-rev-streak{font-size:12px;color:rgba(255,255,255,.5);margin-top:4px}
.hc-feed-btn{
  display:flex;align-items:center;justify-content:center;gap:7px;
  width:100%;margin-top:14px;
  padding:10px;border-radius:10px;
  font-size:13px;font-weight:600;color:rgba(255,255,255,.7);
  border:1px solid rgba(255,255,255,.12);
  transition:all .15s;background:rgba(255,255,255,.04);
}
.hc-feed-btn:hover{background:rgba(255,255,255,.08);color:#fff}

/* ── SECTION SHARED ── */
.sec{padding:clamp(56px,7vw,88px) 0}
.sec-eye{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--t3);margin-bottom:12px}
.sec-h{font-size:clamp(1.6rem,3vw,2.4rem);font-weight:800;line-height:1.12;letter-spacing:-.03em;color:var(--text);margin-bottom:14px}
.sec-p{font-size:1rem;color:var(--t3);line-height:1.7}
.center{text-align:center}.center .sec-p{max-width:520px;margin:0 auto}

/* ── PROBLEM SECTION ── */
.problem-sec{background:#fff;padding:clamp(60px,8vw,96px) 0 0}
[data-theme="dark"] .problem-sec{background:#0D0D0D}
/* centered headline */
.prob-top{text-align:center;margin-bottom:clamp(40px,5vw,64px)}
.prob-top-eye{
  display:inline-flex;align-items:center;gap:6px;
  font-size:11px;font-weight:700;letter-spacing:.13em;text-transform:uppercase;
  color:var(--t3);margin-bottom:18px;
}
.prob-top-h{font-size:clamp(2.2rem,4.5vw,3.6rem);font-weight:800;line-height:1.08;letter-spacing:-.03em;color:var(--text);margin-bottom:0}
.prob-top-h span{color:var(--text)}
.prob-top-sub{font-size:1.05rem;color:var(--t3);margin-top:14px}
/* full-width problem grid */
.prob-split{
  padding:0 var(--pad);
  max-width:var(--max);
  margin:0 auto;
}
/* LEFT — flat, no card wrapper */
.prob-left-header{display:flex;align-items:flex-start;gap:14px;margin-bottom:28px}
.prob-left-icon{
  width:38px;height:38px;border-radius:50%;flex-shrink:0;
  background:#0D0D0D;
  display:flex;align-items:center;justify-content:center;margin-top:2px;
}
.prob-left-icon svg{width:16px;height:16px;stroke:#fff;fill:none;stroke-width:2.5;stroke-linecap:round}
.prob-left-title{font-size:19px;font-weight:800;color:var(--text);line-height:1.2}
.prob-left-sub{font-size:13px;color:var(--t3);margin-top:3px}
/* 4-col × 2-row problem grid — big open cards */
.prob-items{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.prob-item{
  background:#fff;border:1.5px solid #E5E7EB;
  border-radius:20px;padding:32px 28px;
  display:flex;flex-direction:column;gap:16px;
}
[data-theme="dark"] .prob-item{background:#111;border-color:#2D2D2D}
.prob-item-icon{
  width:56px;height:56px;border-radius:16px;flex-shrink:0;
  background:#F3F4F6;
  display:flex;align-items:center;justify-content:center;
}
[data-theme="dark"] .prob-item-icon{background:#1a1a1a}
.prob-item-icon svg{width:28px;height:28px;stroke:#374151;fill:none;stroke-width:1.6;stroke-linecap:round}
[data-theme="dark"] .prob-item-icon svg{stroke:#9CA3AF}
.prob-item-h{font-size:16px;font-weight:700;color:var(--text);line-height:1.3}
.prob-item-p{font-size:13.5px;color:var(--t3);line-height:1.6}
/* impact banner below items */
.prob-impact{
  display:flex;align-items:center;gap:14px;
  margin-top:20px;
  background:#F9F9FB;border:1.5px solid #E5E7EB;
  border-radius:14px;padding:16px 20px;
}
[data-theme="dark"] .prob-impact{background:#111;border-color:#2D2D2D}
.prob-impact-icon{
  width:40px;height:40px;border-radius:10px;flex-shrink:0;
  background:#F3F4F6;display:flex;align-items:center;justify-content:center;
}
[data-theme="dark"] .prob-impact-icon{background:#1a1a1a}
.prob-impact-icon svg{width:20px;height:20px;stroke:#374151;fill:none;stroke-width:1.8;stroke-linecap:round}
[data-theme="dark"] .prob-impact-icon svg{stroke:#9CA3AF}
.prob-impact-text{font-size:14px;color:var(--text);line-height:1.55}
.prob-impact-text span{color:var(--text);font-weight:700}
/* RIGHT — solution panel */
.sol-panel{
  background:#F8F8FD;border:1.5px solid #E5E7EB;
  border-radius:20px;padding:24px 22px;
}
[data-theme="dark"] .sol-panel{background:#111;border-color:#2D2D2D}
.sol-pill{
  display:inline-flex;align-items:center;gap:7px;
  background:var(--brand);color:#fff;
  font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  padding:6px 14px;border-radius:99px;margin-bottom:18px;
}
.sol-meet{font-size:26px;font-weight:800;color:#0D0D0D;margin-bottom:3px;line-height:1.1}
[data-theme="dark"] .sol-meet{color:#F3F4F6}
.sol-meet span{color:var(--brand)}
.sol-role{font-size:14px;color:var(--t3);margin-bottom:20px}
.sol-checks{display:flex;flex-direction:column;gap:10px;margin-bottom:20px}
.sol-check{display:flex;align-items:center;gap:10px;font-size:14px;color:#0D0D0D}
[data-theme="dark"] .sol-check{color:#F3F4F6}
.sol-chk{
  width:22px;height:22px;border-radius:50%;flex-shrink:0;
  background:var(--brand);
  display:flex;align-items:center;justify-content:center;
}
.sol-chk svg{width:11px;height:11px;stroke:#fff;stroke-width:2.5;fill:none;stroke-linecap:round}
.sol-peace{
  display:flex;align-items:flex-start;gap:12px;
  background:#fff;border:1.5px solid #E5E7EB;
  border-radius:14px;padding:16px;
}
[data-theme="dark"] .sol-peace{background:#1a1a1a;border-color:#3D3D3D}
.sol-peace-icon{width:36px;height:36px;border-radius:10px;flex-shrink:0;background:#E9E9FF;display:flex;align-items:center;justify-content:center}
[data-theme="dark"] .sol-peace-icon{background:rgba(var(--brand-rgb),.15)}
.sol-peace-icon svg{width:18px;height:18px;stroke:var(--brand);fill:none;stroke-width:2;stroke-linecap:round}
.sol-peace-h{font-size:14px;font-weight:700;color:var(--brand);margin-bottom:4px}
.sol-peace-p{font-size:12.5px;color:var(--t3);line-height:1.55}
/* bottom CTA strip — full width */
.prob-cta-strip{
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:20px;
  padding:24px var(--pad);
  margin-top:40px;
  border-top:1.5px solid #E5E7EB;
  max-width:var(--max);margin-left:auto;margin-right:auto;
}
[data-theme="dark"] .prob-cta-strip{border-color:#2D2D2D}
.prob-cta-left{display:flex;align-items:center;gap:14px}
.prob-cta-icon{
  width:44px;height:44px;border-radius:12px;flex-shrink:0;
  background:#F3F4F6;display:flex;align-items:center;justify-content:center;
}
[data-theme="dark"] .prob-cta-icon{background:#1a1a1a}
.prob-cta-icon svg{width:22px;height:22px;stroke:#374151;fill:none;stroke-width:1.8;stroke-linecap:round}
[data-theme="dark"] .prob-cta-icon svg{stroke:#9CA3AF}
.prob-cta-t1{font-size:15px;font-weight:700;color:var(--text)}
.prob-cta-t2{font-size:13px;color:var(--t3);margin-top:2px}
.prob-cta-t2 span{color:var(--text);font-weight:700}
.btn-prob-cta{
  display:inline-flex;align-items:center;gap:8px;
  padding:14px 28px;border-radius:10px;
  font-size:15px;font-weight:700;color:#fff;
  background:#0D0D0D;
  transition:opacity .15s,transform .15s;white-space:nowrap;flex-shrink:0;
}
.btn-prob-cta:hover{opacity:.9;transform:translateY(-1px)}
.btn-prob-cta svg{flex-shrink:0}
@media(max-width:900px){.prob-items{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.prob-items{grid-template-columns:1fr}.prob-cta-strip{flex-direction:column;align-items:flex-start}}

/* ── DAY IN LIFE / PIPELINE ── */
.day-sec{background:#F9F9FB;padding:clamp(56px,7vw,88px) 0}
[data-theme="dark"] .day-sec{background:#111}
.day-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:clamp(36px,5vw,56px)}
.day-client{text-align:right}
.day-client-name{font-size:14px;font-weight:700;color:var(--text)}
.day-client-sub{font-size:12.5px;color:var(--t4);margin-top:2px}
/* pipeline row */
.pipeline-row{
  display:flex;align-items:flex-start;
  gap:0;margin-bottom:32px;
  overflow-x:auto;padding-bottom:8px;
}
.pipeline-row::-webkit-scrollbar{height:4px}
.pipeline-row::-webkit-scrollbar-thumb{background:var(--border);border-radius:2px}
.pipe-step{
  display:flex;flex-direction:column;align-items:center;
  text-align:center;flex:1;min-width:96px;position:relative;
}
/* arrow connector */
.pipe-step:not(:last-child)::after{
  content:'→';
  position:absolute;top:32px;left:calc(50% + 38px);
  font-size:16px;color:var(--border);
  line-height:1;z-index:1;pointer-events:none;
}
[data-theme="dark"] .pipe-step:not(:last-child)::after{color:#3D3D3D}
.pipe-step.ps-active:not(:last-child)::after{color:var(--brand);opacity:.5}
/* node */
.pipe-node{
  width:68px;height:68px;border-radius:16px;
  background:#fff;border:1.5px solid #E5E7EB;
  display:flex;align-items:center;justify-content:center;
  margin-bottom:12px;position:relative;
  box-shadow:0 1px 4px rgba(0,0,0,.06);
  transition:border-color .3s,box-shadow .3s,background .3s;
  flex-shrink:0;
}
[data-theme="dark"] .pipe-node{background:#1a1a1a;border-color:#3D3D3D}
.pipe-node svg{width:24px;height:24px;stroke:#BCC0C9;fill:none;stroke-width:1.7;stroke-linecap:round;transition:stroke .3s}
/* number badge */
.pipe-badge{
  position:absolute;bottom:-1px;right:-1px;
  width:20px;height:20px;border-radius:6px 0 0 0;
  background:#D1D5DB;
  font-size:10px;font-weight:800;color:#fff;
  display:flex;align-items:center;justify-content:center;
  transition:background .3s;
}
/* step label + time */
.pipe-label{
  font-size:10px;font-weight:700;letter-spacing:.09em;
  text-transform:uppercase;color:#9CA3AF;margin-bottom:6px;
  transition:color .3s;line-height:1.35;
}
.pipe-time{
  font-size:11px;font-weight:600;color:transparent;
  font-variant-numeric:tabular-nums;min-height:16px;
  transition:color .3s;
}
/* running state */
.pipe-step.ps-running .pipe-node{
  border-color:#0D0D0D;
  box-shadow:0 0 0 4px rgba(0,0,0,.08),0 2px 8px rgba(0,0,0,.08);
  background:rgba(0,0,0,.02);
}
.pipe-step.ps-running .pipe-node svg{stroke:#0D0D0D}
.pipe-step.ps-running .pipe-badge{background:#0D0D0D;animation:badgePulse 1s ease infinite}
@keyframes badgePulse{0%,100%{box-shadow:0 0 0 0 rgba(0,0,0,.3)}50%{box-shadow:0 0 0 5px rgba(0,0,0,0)}}
.pipe-step.ps-running .pipe-label{color:#0D0D0D}
.pipe-step.ps-running .pipe-time{color:#0D0D0D}
/* done state — only icon and time go green, everything else stays neutral */
.pipe-step.ps-done .pipe-node{border-color:#E5E7EB;background:#fff}
.pipe-step.ps-done .pipe-node svg{stroke:#22C55E}
.pipe-step.ps-done .pipe-badge{background:#E5E7EB;color:#374151}
.pipe-step.ps-done .pipe-label{color:var(--t3)}
.pipe-step.ps-done .pipe-time{color:#22C55E;font-weight:700}
/* ticker — sits below all step text, with breathing room */
.pipe-ticker-row{
  min-height:32px;margin-top:24px;margin-bottom:8px;
  display:flex;align-items:center;justify-content:center;gap:8px;
  font-size:13px;color:#0D0D0D;font-weight:600;
}
.pipe-ticker-dot{
  width:7px;height:7px;border-radius:50%;background:#0D0D0D;
  animation:tickDot 1s ease infinite;flex-shrink:0;
}
@keyframes tickDot{0%,100%{opacity:1}50%{opacity:.3}}
/* mission complete bar */
.mission-bar{
  display:flex;align-items:center;justify-content:space-between;
  background:#fff;border:1.5px solid #E5E7EB;
  border-radius:16px;padding:20px 28px;flex-wrap:wrap;gap:16px;
  box-shadow:0 1px 4px rgba(0,0,0,.04);
  opacity:0;transform:translateY(8px);
  transition:opacity .4s,transform .4s;
  margin-top:8px;
}
[data-theme="dark"] .mission-bar{background:#1a1a1a;border-color:#2D2D2D}
.mission-bar.visible{opacity:1;transform:translateY(0)}
.mission-txt{font-size:17px;font-weight:700;color:var(--text)}
.mission-stats{display:flex;gap:40px}
.mission-stat-n{font-size:1.4rem;font-weight:800;color:var(--text);letter-spacing:-.03em}
.mission-stat-l{font-size:11.5px;color:var(--t4);margin-top:2px}
/* CTA buttons below pipeline */
.pipe-cta{display:flex;align-items:center;gap:12px;margin-top:28px;flex-wrap:wrap}
.btn-pipe-hire{
  display:inline-flex;align-items:center;gap:8px;
  padding:13px 26px;border-radius:10px;
  font-size:14.5px;font-weight:700;color:#fff;
  background:#0D0D0D;
  transition:opacity .15s,transform .15s;
}
.btn-pipe-hire:hover{opacity:.8;transform:translateY(-1px)}
.btn-pipe-test{
  display:inline-flex;align-items:center;gap:8px;
  padding:12px 22px;border-radius:10px;
  font-size:14.5px;font-weight:600;color:#0D0D0D;
  border:1.5px solid #D1D5DB;
  background:transparent;
  transition:all .15s;
}
.btn-pipe-test:hover{background:#0D0D0D;color:#fff;border-color:#0D0D0D}
.btn-pipe-test svg,.btn-pipe-hire svg{flex-shrink:0}
@media(max-width:900px){
  .pipe-step{min-width:80px}
  .pipe-step:not(:last-child)::after{left:calc(50% + 30px)}
}
@media(max-width:640px){
  .pipeline-row{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;overflow:visible}
  .pipe-step{min-width:0}
  .pipe-node{width:56px;height:56px;border-radius:12px}
  .pipe-step:not(:last-child)::after{display:none}
}

/* ── TWO-COL SPLIT ── */
.split-sec{background:var(--bg);border-top:1px solid var(--border)}
[data-theme="dark"] .split-sec{background:#0D0D0D;border-color:#2D2D2D}
.split-grid{display:grid;grid-template-columns:1fr 1fr;gap:clamp(40px,6vw,72px);align-items:start}

/* tools grid */
.tools-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-top:20px}
.tool-item{display:flex;flex-direction:column;align-items:center;gap:6px}
.tool-icon{width:48px;height:48px;border-radius:12px;border:1px solid var(--border);background:var(--soft);display:flex;align-items:center;justify-content:center}
[data-theme="dark"] .tool-icon{background:#111;border-color:#2D2D2D}
.tool-icon svg{width:22px;height:22px;color:var(--t3)}
.tool-label{font-size:10px;color:var(--t4);text-align:center;font-weight:500}

/* perf card */
.perf-card{
  background:#0A0A0A;border-radius:20px;padding:28px;
}
[data-theme="dark"] .perf-card{background:#111;border:1px solid #2D2D2D}
.perf-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
.perf-stat{background:#141414;border-radius:12px;padding:16px;text-align:center}
[data-theme="dark"] .perf-stat{background:#1a1a1a}
.perf-n{font-size:1.4rem;font-weight:800;color:#fff;letter-spacing:-.04em;margin-bottom:4px}
.perf-l{font-size:11px;color:rgba(255,255,255,.4)}
.perf-stat.wide{grid-column:span 3;display:grid;grid-template-columns:1fr 1fr;gap:16px}
.perf-note{font-size:12px;color:rgba(255,255,255,.35);line-height:1.6;margin-top:8px;text-align:center}

/* industries */
.industries-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:20px}
.industry-item{display:flex;flex-direction:column;align-items:center;gap:7px;padding:14px 8px;border-radius:12px;background:var(--soft);border:1px solid var(--border)}
[data-theme="dark"] .industry-item{background:#111;border-color:#2D2D2D}
.industry-icon{width:36px;height:36px;display:flex;align-items:center;justify-content:center}
.industry-icon svg{width:22px;height:22px;color:var(--text)}
.industry-label{font-size:11px;font-weight:600;color:var(--t3);text-align:center;line-height:1.3}

/* testimonials */
.testi-col{position:relative}
.testi-grid{display:grid;grid-template-columns:1fr;gap:14px}
.testi-blurred{filter:blur(5px);pointer-events:none;user-select:none;opacity:.7}
.testi-overlay{
  position:absolute;inset:0;
  display:flex;align-items:center;justify-content:center;
  z-index:10;
}
.testi-overlay-card{
  background:#fff;
  border:1.5px solid #E5E7EB;
  border-radius:20px;
  padding:32px 28px;
  text-align:center;
  max-width:340px;
  box-shadow:0 8px 40px rgba(0,0,0,.1);
}
[data-theme="dark"] .testi-overlay-card{background:#111;border-color:#2D2D2D;box-shadow:0 8px 40px rgba(0,0,0,.4)}
.tov-icon{
  width:52px;height:52px;border-radius:16px;
  background:#F3F4F6;
  display:flex;align-items:center;justify-content:center;
  margin:0 auto 16px;
}
[data-theme="dark"] .tov-icon{background:#1a1a1a}
.tov-icon svg{width:26px;height:26px;stroke:#374151;fill:none;stroke-width:1.8;stroke-linecap:round}
[data-theme="dark"] .tov-icon svg{stroke:#9CA3AF}
.tov-h{font-size:17px;font-weight:800;color:var(--text);margin-bottom:8px;line-height:1.25}
.tov-p{font-size:13px;color:var(--t3);line-height:1.6;margin-bottom:20px}
.tov-badges{display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:20px;flex-wrap:wrap}
.tov-badge{
  display:inline-flex;align-items:center;gap:5px;
  font-size:11.5px;font-weight:600;color:var(--t3);
  background:#F3F4F6;border-radius:99px;padding:5px 12px;
}
[data-theme="dark"] .tov-badge{background:#1a1a1a;color:#9CA3AF}
.btn-tov{
  display:inline-flex;align-items:center;gap:8px;
  width:100%;justify-content:center;
  padding:13px 20px;border-radius:12px;
  font-size:14px;font-weight:700;color:#fff;
  background:#0D0D0D;
  transition:opacity .15s;
}
.btn-tov:hover{opacity:.8}
.tov-sub{font-size:11.5px;color:var(--t4);margin-top:12px}
.testi-card{
  background:var(--soft);border:1px solid var(--border);
  border-radius:16px;padding:20px;
}
[data-theme="dark"] .testi-card{background:#111;border-color:#2D2D2D}
.testi-stars{color:#F59E0B;font-size:12px;letter-spacing:1px;margin-bottom:10px}
.testi-q{font-size:13.5px;color:var(--t2);line-height:1.65;font-style:italic;margin-bottom:14px}
[data-theme="dark"] .testi-q{color:#D1D5DB}
.testi-auth{display:flex;align-items:center;gap:10px}
.testi-av{width:36px;height:36px;border-radius:50%;background:#0D0D0D;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0}
.testi-name{font-size:13px;font-weight:700;color:var(--text)}
.testi-co{font-size:11px;color:var(--t4)}
.testi-rev{font-size:13px;font-weight:700;color:var(--t3);margin-top:6px}

/* integrations */
.integrations-sec{background:#fff;border-top:1px solid var(--border);padding:clamp(56px,7vw,80px) 0}
[data-theme="dark"] .integrations-sec{background:#0D0D0D;border-color:#2D2D2D}
.int-top{margin-bottom:32px}
.int-logos{display:flex;flex-wrap:wrap;gap:12px;align-items:center}
.int-logo{
  display:flex;align-items:center;gap:9px;
  padding:12px 20px;border-radius:12px;
  background:#fff;border:1.5px solid #E5E7EB;
  font-size:14px;font-weight:600;color:var(--t2);
  transition:border-color .15s,box-shadow .15s;
}
.int-logo:hover{border-color:#9CA3AF;box-shadow:0 2px 8px rgba(0,0,0,.06)}
[data-theme="dark"] .int-logo{background:#111;border-color:#2D2D2D;color:#D1D5DB}
.int-logo svg{width:18px;height:18px;flex-shrink:0;stroke:var(--t3)}
.int-more{font-size:14px;color:var(--t4);padding:12px 4px;font-weight:500}

/* security */
.security-sec{background:var(--soft);border-top:1px solid var(--border);padding:clamp(56px,7vw,80px) 0}
[data-theme="dark"] .security-sec{background:#161616;border-color:#2D2D2D}
.sec-top{margin-bottom:32px}
.sec-badges{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}
.sec-badge{
  display:flex;flex-direction:column;align-items:center;gap:10px;
  padding:24px 14px;border-radius:16px;
  background:#fff;border:1.5px solid #E5E7EB;text-align:center;
}
[data-theme="dark"] .sec-badge{background:#111;border-color:#2D2D2D}
.sec-badge svg{width:26px;height:26px;stroke:var(--text);fill:none;stroke-width:1.8}
.sec-badge-label{font-size:12px;font-weight:600;color:var(--t2);line-height:1.4}
[data-theme="dark"] .sec-badge-label{color:#D1D5DB}
@media(max-width:768px){.sec-badges{grid-template-columns:repeat(3,1fr)}}
@media(max-width:480px){.sec-badges{grid-template-columns:repeat(2,1fr)}}

/* ── FAQ ── */
.faq-sec{background:var(--bg);border-top:1px solid var(--border)}
[data-theme="dark"] .faq-sec{background:#0D0D0D;border-color:#2D2D2D}
.faq-grid{display:grid;grid-template-columns:1fr 1.5fr;gap:clamp(40px,6vw,72px);align-items:start}
.faq-list{display:flex;flex-direction:column}
.faq-item{border-bottom:1px solid var(--border)}
[data-theme="dark"] .faq-item{border-color:#2D2D2D}
.faq-item:first-child{border-top:1px solid var(--border)}
[data-theme="dark"] .faq-item:first-child{border-color:#2D2D2D}
.faq-q{display:flex;align-items:center;justify-content:space-between;padding:17px 0;cursor:pointer;font-size:14.5px;font-weight:700;color:var(--text);gap:14px}
.faq-icon{font-size:18px;color:var(--t4);transition:transform .2s;flex-shrink:0;line-height:1}
.faq-item.open .faq-icon{transform:rotate(45deg);color:var(--text)}
.faq-a{font-size:13.5px;color:var(--t3);line-height:1.75;max-height:0;overflow:hidden;transition:max-height .3s ease,padding .3s}
.faq-item.open .faq-a{max-height:300px;padding-bottom:16px}

/* ── FINAL CTA ── */
.cta-final{
  position:relative;overflow:hidden;
  background:#0A0A0A;
  padding:clamp(60px,8vw,100px) 0;
  border-top:1px solid #1F1F1F;
}
.cta-final-inner{
  display:grid;grid-template-columns:1fr 380px;
  gap:48px;align-items:center;
}
.cta-final-left{}
.cta-final-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:16px}
.cta-final-h{
  font-size:clamp(1.8rem,4vw,3rem);font-weight:800;
  line-height:1.1;letter-spacing:-.03em;color:#fff;margin-bottom:10px;
}
.cta-final-h em{font-style:normal;color:#fff}
.cta-final-sub{font-size:14px;color:rgba(255,255,255,.5);line-height:1.7;margin-bottom:24px;max-width:400px}
.btn-cta-final{
  display:inline-flex;align-items:center;gap:8px;
  padding:14px 28px;border-radius:12px;
  font-size:15px;font-weight:700;color:#0D0D0D;
  background:#fff;
  transition:opacity .15s,transform .15s;
}
.btn-cta-final:hover{opacity:.9;transform:translateY(-2px)}
.cta-note{font-size:12px;color:rgba(255,255,255,.3);margin-top:10px}
.cta-final-right{position:relative}
.cta-final-right img{
  width:100%;border-radius:20px;
  object-fit:cover;object-position:center top;
  max-height:400px;
}

/* ── FOOTER ── */
.footer{background:#0A0A0A;padding:clamp(32px,4vw,48px) 0 24px;border-top:1px solid #1a1a1a}
.ft-inner{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px}
.ft-logo{font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.4px}
.ft-links{display:flex;gap:20px;flex-wrap:wrap}
.ft-links a{font-size:13px;color:rgba(255,255,255,.45);transition:color .15s}
.ft-links a:hover{color:#fff}
.ft-copy{font-size:12px;color:rgba(255,255,255,.3)}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .hero-inner{grid-template-columns:1fr}
  .hero-card{display:none}
  .prob-grid{grid-template-columns:repeat(2,1fr)}
  .day-flow{grid-template-columns:repeat(3,1fr)}
  .day-flow::before{display:none}
  .split-grid{grid-template-columns:1fr}
  .perf-grid{grid-template-columns:repeat(3,1fr)}
  .perf-stat.wide{grid-column:span 3}
  .faq-grid{grid-template-columns:1fr}
  .cta-final-inner{grid-template-columns:1fr}
  .cta-final-right{display:none}
  .industries-grid{grid-template-columns:repeat(4,1fr)}
  .sec-badges{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:768px){
  .prob-grid{grid-template-columns:1fr}
  .tools-grid{grid-template-columns:repeat(4,1fr)}
  .industries-grid{grid-template-columns:repeat(3,1fr)}
  .sec-badges{grid-template-columns:repeat(2,1fr)}
  .mission-stats{gap:16px}
  .ft-inner{flex-direction:column;text-align:center}
}
@media(max-width:480px){
  .day-flow{grid-template-columns:repeat(2,1fr)}
  .tools-grid{grid-template-columns:repeat(3,1fr)}
  .perf-grid{grid-template-columns:repeat(2,1fr)}
  .perf-stat.wide{grid-column:span 2}
  .industries-grid{grid-template-columns:repeat(2,1fr)}
  .mission-bar{flex-direction:column;align-items:flex-start}
  .int-logos{gap:8px}
}
</style>
</head>
<body>

{{-- NAV --}}
<nav class="nav">
  <div class="nav-i">
    <a href="{{ route('home2') }}" class="nav-logo">UNIT</a>

    <div class="nav-links">
      <a href="{{ route('workers.page') }}" class="nav-link">Meet the Team</a>
      <a href="#day-in-life" class="nav-link">How It Works</a>
      <a href="#faq" class="nav-link">For Business</a>
      <a href="#integrations" class="nav-link">Resources</a>
      <a href="{{ route('pricing') }}" class="nav-link">Pricing</a>
    </div>

    <div class="nav-actions">
      <button class="btn-theme" id="themeToggle" aria-label="Toggle dark mode">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
      </button>
      @auth
        <a href="{{ route('dashboard') }}" class="btn-login">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="btn-login">Log in</a>
      @endauth
      <a href="{{ route('register') }}" class="btn-nav-hire">
        Hire Your First Worker
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</nav>

{{-- HERO --}}
<section class="hero-worker">

  {{-- LEFT: video column --}}
  <div class="hero-video-col">

    {{-- Background media --}}
    <div class="hero-media">
      <img src="/images/ava-hero.jpg" alt="{{ $worker['name'] }}"
           onerror="this.src='/images/ava.png';this.onerror=null">
    </div>

    {{-- Text content over the video --}}
    <div class="hero-text">
      <div class="hero-eye">Meet {{ $worker['name'] }}</div>
      <h1 class="hero-h">She never<br>forgets a <span class="hl">renewal.</span></h1>
    </div>

    {{-- Video controls bar --}}
    <div class="hero-vidbar">
      <div class="vidbar-play">
        <svg viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
      </div>
      <span class="vidbar-time">0:12 / 1:02</span>
      <div class="vidbar-track">
        <div class="vidbar-fill"></div>
        <div class="vidbar-thumb"></div>
      </div>
      <div class="vidbar-icons">
        <svg viewBox="0 0 24 24" stroke-linecap="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 010 14.14M15.54 8.46a5 5 0 010 7.07"/></svg>
        <svg viewBox="0 0 24 24" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        <svg viewBox="0 0 24 24" stroke-linecap="round"><path d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"/></svg>
      </div>
    </div>

  </div>{{-- end .hero-video-col --}}

  {{-- RIGHT: status panel (separate column) --}}
  <div class="hero-panel">
    <div class="hc-status">
      <div class="hc-dot"></div>
      <span class="hc-status-txt">{{ $worker['name'] }} IS ON SHIFT</span>
    </div>
    <div class="hc-time" id="live-clock">09:42 AM</div>
    <div class="hc-task-label">Current Task</div>
    <div class="hc-task-name">Checking 14 contracts expiring this week…</div>
    <div class="hc-task-icon">
      <svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M12 12h.01M12 16h.01" stroke-linecap="round"/></svg>
    </div>
    <div class="hc-divider"></div>
    <div class="hc-completed-label">Completed Today</div>
    @foreach(['42 renewals reviewed','18 reminders sent','6 customers retained'] as $item)
    <div class="hc-done-item">
      <div class="hc-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
      {{ $item }}
    </div>
    @endforeach
    <div class="hc-revenue-label">Revenue Protected</div>
    <div class="hc-revenue-amount">${{ number_format($totalTx * 3.85, 0) }}</div>
    <div class="hc-revenue-streak">{{ $deploymentCount * 18 }}-day streak 🔥</div>
    <a href="{{ route('register') }}" class="hc-feed-btn">
      View Live Feed →
    </a>
  </div>

</section>

{{-- THE PROBLEM --}}
<section class="problem-sec">

  {{-- Centered headline --}}
  <div class="prob-top" style="padding:0 var(--pad)">
    <div class="prob-top-eye">
      <svg viewBox="0 0 24 24" width="13" height="13"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      The Problem
    </div>
    <h2 class="prob-top-h">Renewals are mission-critical.<br>But they're <span class="hl">easy to miss.</span></h2>
    <p class="prob-top-sub">Deadlines slip. Money leaks. Opportunities disappear.</p>
  </div>

  {{-- Full-width problem grid --}}
  <div class="prob-split">

    <div class="prob-left-header">
      <div class="prob-left-icon">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <div>
        <div class="prob-left-title">Which of these problems hit your team?</div>
        <div class="prob-left-sub">If you nodded at even one, you're not alone.</div>
      </div>
    </div>

    {{-- 4×2 grid --}}
    <div class="prob-items">
      @php $problems = [
        ['icon'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>','h'=>'Nobody owns renewals.','p'=>'Permits, licenses, contracts— no single owner, no accountability.'],
        ['icon'=>'<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>','h'=>'Inbox chaos.','p'=>'Renewal emails get buried, deleted, or never even seen.'],
        ['icon'=>'<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>','h'=>'Money leaks quietly.','p'=>'Unused software, duplicates, and forgotten vendors drain budgets.'],
        ['icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','h'=>'Licenses expire without warning.','p'=>'One missed renewal can halt operations or trigger fines.'],
        ['icon'=>'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>','h'=>'Hours wasted every week.','p'=>'Spreadsheets, portals, emails, follow-ups— it never ends.'],
        ['icon'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>','h'=>'Everyone has their own system.','p'=>'No single source of truth. No visibility. No accountability.'],
        ['icon'=>'<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3M12 17h.01"/>','h'=>'You don\'t know what\'s due next.','p'=>'You find out only when someone asks— or after the deadline.'],
        ['icon'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>','h'=>'It depends on one person.','p'=>'Vacation, sick leave, resignation— institutional knowledge disappears.'],
      ]; @endphp
      @foreach($problems as $p)
      <div class="prob-item">
        <div class="prob-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">{!! $p['icon'] !!}</svg></div>
        <div class="prob-item-h">{{ $p['h'] }}</div>
        <div class="prob-item-p">{{ $p['p'] }}</div>
      </div>
      @endforeach
    </div>

    {{-- Impact banner --}}
    <div class="prob-impact">
      <div class="prob-impact-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div class="prob-impact-text">
        Missed renewals don't just create problems. They cost <span>money, time, and credibility.</span>
      </div>
    </div>

  </div>{{-- end .prob-split --}}

  {{-- Bottom CTA bar --}}
  <div class="prob-cta-strip">
    <div class="prob-cta-left">
      <div class="prob-cta-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <div>
        <div class="prob-cta-t1">Renewals are too important to leave to chance.</div>
        <div class="prob-cta-t2">Let <span>AVA</span> handle them — so your business keeps moving forward.</div>
      </div>
    </div>
    <a href="{{ route('register') }}" class="btn-prob-cta">
      See AVA in Action
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
  </div>

</section>

{{-- A DAY IN AVA'S LIFE — AVA's real pipeline, animated --}}
<section class="day-sec" id="day-in-life">
  <div class="w">
    <div class="day-top">
      <div>
        <div class="sec-eye">A day in {{ $worker['name'] }}'s life</div>
        <h2 class="sec-h" style="margin-bottom:0">Follow AVA through one renewal.</h2>
      </div>
    </div>

    {{-- AVA's real 8-stage pipeline (human-friendly labels) --}}
    <div class="pipeline-row" id="pipelineRow">

      <div class="pipe-step" data-step="0" data-time="9:00 AM">
        <div class="pipe-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 3v13M8 7l4-4 4 4"/></svg>
          <span class="pipe-badge">1</span>
        </div>
        <div class="pipe-label">Task Received</div>
        <div class="pipe-time"></div>
      </div>

      <div class="pipe-step" data-step="1" data-time="9:00 AM">
        <div class="pipe-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <span class="pipe-badge">2</span>
        </div>
        <div class="pipe-label">Reads the Email</div>
        <div class="pipe-time"></div>
      </div>

      <div class="pipe-step" data-step="2" data-time="9:01 AM">
        <div class="pipe-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
          <span class="pipe-badge">3</span>
        </div>
        <div class="pipe-label">Figures Out Priority</div>
        <div class="pipe-time"></div>
      </div>

      <div class="pipe-step" data-step="3" data-time="9:01 AM">
        <div class="pipe-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
          <span class="pipe-badge">4</span>
        </div>
        <div class="pipe-label">Looks Up the Customer</div>
        <div class="pipe-time"></div>
      </div>

      <div class="pipe-step" data-step="4" data-time="9:02 AM">
        <div class="pipe-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          <span class="pipe-badge">5</span>
        </div>
        <div class="pipe-label">Logs the Interaction</div>
        <div class="pipe-time"></div>
      </div>

      <div class="pipe-step" data-step="5" data-time="9:02 AM">
        <div class="pipe-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
          <span class="pipe-badge">6</span>
        </div>
        <div class="pipe-label">Picks the Right Message</div>
        <div class="pipe-time"></div>
      </div>

      <div class="pipe-step" data-step="6" data-time="9:03 AM">
        <div class="pipe-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/></svg>
          <span class="pipe-badge">7</span>
        </div>
        <div class="pipe-label">Writes the Email</div>
        <div class="pipe-time"></div>
      </div>

      <div class="pipe-step" data-step="7" data-time="9:03 AM">
        <div class="pipe-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/><polyline points="9 10 12 13 15 10"/></svg>
          <span class="pipe-badge">8</span>
        </div>
        <div class="pipe-label">Lands in Your Inbox</div>
        <div class="pipe-time"></div>
      </div>

    </div>

    {{-- Single shared ticker row — below all steps --}}
    <div class="pipe-ticker-row" id="pipeTickerRow"></div>

    {{-- Mission complete bar --}}
    <div class="mission-bar" id="missionBar">
      <div class="mission-txt">Mission Complete 🎉</div>
      <div class="mission-stats">
        <div>
          <div class="mission-stat-n">3 minutes</div>
          <div class="mission-stat-l">Time Taken</div>
        </div>
        <div>
          <div class="mission-stat-n">35 minutes</div>
          <div class="mission-stat-l">Human effort saved</div>
        </div>
      </div>
    </div>

    {{-- CTAs --}}
    <div class="pipe-cta">
      <a href="{{ route('register') }}" class="btn-pipe-hire">
        Hire AVA
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
      <a href="{{ route('register') }}" class="btn-pipe-test">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        Run a Live Test
      </a>
    </div>
  </div>
</section>

<script>
(function(){
  var steps      = document.querySelectorAll('#pipelineRow .pipe-step');
  var missionBar = document.getElementById('missionBar');
  var tickerRow  = document.getElementById('pipeTickerRow');
  var STEP_MS  = 2200;
  var PAUSE_MS = 3000;

  var tickerMessages = [
    'AVA is picking up a new renewal task…',
    'Reading the renewal email from your inbox…',
    'Figuring out: renewal · high priority · expiring soon…',
    'Pulling up customer history and past renewals…',
    'Logging every action so you have a full audit trail…',
    'Selecting the right tone and message template…',
    'Writing a personalised renewal email for your customer…',
    'Renewal draft ready and waiting in your inbox…',
  ];

  function clearAll(){
    steps.forEach(function(s){
      s.classList.remove('ps-running','ps-done');
      s.querySelector('.pipe-time').textContent = '';
    });
    tickerRow.innerHTML = '';
    missionBar.classList.remove('visible');
  }

  function runStep(i){
    if(i >= steps.length){
      tickerRow.innerHTML = '';
      missionBar.classList.add('visible');
      setTimeout(function(){
        missionBar.classList.remove('visible');
        setTimeout(function(){ clearAll(); runStep(0); }, 500);
      }, PAUSE_MS);
      return;
    }
    var step = steps[i];
    var timeEl = step.querySelector('.pipe-time');
    step.classList.add('ps-running');
    timeEl.textContent = step.dataset.time;  // stamp time when step activates
    tickerRow.innerHTML = '<span class="pipe-ticker-dot"></span>' + tickerMessages[i];
    setTimeout(function(){
      step.classList.remove('ps-running');
      step.classList.add('ps-done');
      // time stays visible in done color
      runStep(i + 1);
    }, STEP_MS);
  }

  setTimeout(function(){ runStep(0); }, 600);
})();
</script>

{{-- EVERYTHING AVA HAS ACCESS TO + LIVE PERFORMANCE --}}
<section class="split-sec sec">
  <div class="w">
    <div class="split-grid">
      <div>
        <div class="sec-eye">Everything {{ $worker['name'] }} has access to</div>
        <h2 class="sec-h" style="font-size:clamp(1.3rem,2.2vw,1.7rem)">{{ $worker['name'] }} securely accesses the data she needs to get the job done.</h2>
        <div class="tools-grid">
          @php
            $tools = [
              ['Calendar','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>'],
              ['CRM','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>'],
              ['Email','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>'],
              ['Contracts','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8"/></svg>'],
              ['Invoices','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>'],
              ['Drive','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="1 6 1 22 8 12 15 22 22 6 15 16"/></svg>'],
              ['Slack','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.5 10c-.83 0-1.5-.67-1.5-1.5v-5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5v5c0 .83-.67 1.5-1.5 1.5z"/><path d="M20.5 10H19V8.5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/><path d="M9.5 14c.83 0 1.5.67 1.5 1.5v5c0 .83-.67 1.5-1.5 1.5S8 21.33 8 20.5v-5c0-.83.67-1.5 1.5-1.5z"/><path d="M3.5 14H5v1.5c0 .83-.67 1.5-1.5 1.5S2 16.33 2 15.5 2.67 14 3.5 14z"/><path d="M14 14.5c0-.83.67-1.5 1.5-1.5h5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-5c-.83 0-1.5-.67-1.5-1.5z"/><path d="M15.5 19H14v1.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5-.67-1.5-1.5-1.5z"/><path d="M10 9.5C10 8.67 9.33 8 8.5 8h-5C2.67 8 2 8.67 2 9.5S2.67 11 3.5 11h5c.83 0 1.5-.67 1.5-1.5z"/><path d="M8.5 5H10V3.5C10 2.67 9.33 2 8.5 2S7 2.67 7 3.5 7.67 5 8.5 5z"/></svg>'],
              ['Teams','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>'],
              ['Knowledge','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>'],
              ['SOPs','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M12 12h.01M12 16h.01"/></svg>'],
            ];
          @endphp
          @foreach($tools as $tool)
          <div class="tool-item">
            <div class="tool-icon">{!! $tool[1] !!}</div>
            <div class="tool-label">{{ $tool[0] }}</div>
          </div>
          @endforeach
        </div>
        <p style="font-size:12.5px;color:var(--t4);margin-top:16px">{{ $worker['name'] }} securely accesses the data she needs to get the job done.</p>
      </div>
      <div>
        <div class="sec-eye" style="color:rgba(255,255,255,.4)">Live Performance</div>
        <div class="perf-card">
          <div class="perf-grid">
            <div class="perf-stat">
              <div class="perf-n">{{ number_format($totalTx) }}</div>
              <div class="perf-l">Renewals Monitored Today</div>
            </div>
            <div class="perf-stat">
              <div class="perf-n">18</div>
              <div class="perf-l">Reminders Sent</div>
            </div>
            <div class="perf-stat">
              <div class="perf-n">${{ number_format($totalTx * 3.85, 0) }}</div>
              <div class="perf-l">Revenue Protected</div>
            </div>
            <div class="perf-stat">
              <div class="perf-n">99.8%</div>
              <div class="perf-l">Accuracy Rate</div>
            </div>
            <div class="perf-stat">
              <div class="perf-n">{{ $deploymentCount * 18 }}</div>
              <div class="perf-l">Day Streak</div>
            </div>
            <div class="perf-stat" style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2)">
              <div class="perf-n" style="color:#22C55E">Live</div>
              <div class="perf-l" style="color:rgba(34,197,94,.6)">Right now</div>
            </div>
          </div>
          <div class="perf-note">Always on. Always working. Always protecting your revenue.</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- WHO HIRES + TESTIMONIALS --}}
<section class="split-sec sec" style="border-top:1px solid var(--border)">
  <div class="w">
    <div class="split-grid">
      <div>
        <div class="sec-eye">Who hires {{ $worker['name'] }}</div>
        <h2 class="sec-h" style="font-size:clamp(1.3rem,2.2vw,1.7rem)">Trusted by businesses across industries.</h2>
        <div class="industries-grid">
          @php
            $industries = [
              ['Dental Clinics','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2a5 5 0 015 5 5 5 0 01-5 5 5 5 0 01-5-5 5 5 0 015-5m0 12c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z"/></svg>'],
              ['Gyms & Fitness','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>'],
              ['Law Firms','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'],
              ['Marketing Agencies','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>'],
              ['Accounting Firms','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>'],
              ['Consultants','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>'],
              ['Membership','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>'],
              ['SaaS','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>'],
            ];
          @endphp
          @foreach($industries as $ind)
          <div class="industry-item">
            <div class="industry-icon">{!! $ind[1] !!}</div>
            <div class="industry-label">{{ $ind[0] }}</div>
          </div>
          @endforeach
        </div>
      </div>
      <div class="testi-col">
        <div class="sec-eye">What business owners say</div>
        {{-- Cards visible but blurred — real reviews coming --}}
        <div class="testi-grid testi-blurred">
          @foreach($worker['testimonials'] as $t)
          <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p class="testi-q">"{{ $t['quote'] }}"</p>
            <div class="testi-auth">
              <div class="testi-av">{{ strtoupper(substr($t['name'],0,1)) }}</div>
              <div>
                <div class="testi-name">{{ $t['name'] }}</div>
                <div class="testi-co">{{ $t['company'] }}</div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        {{-- Overlay soliciting real testimonials --}}
        <div class="testi-overlay">
          <div class="testi-overlay-card">
            <div class="tov-icon">
              <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            </div>
            <div class="tov-h">Have you used AVA?<br>We'd love to hear from you.</div>
            <p class="tov-p">Share your experience and get featured here. Real stories from real operators — no scripts, no fluff.</p>
            <div class="tov-badges">
              <span class="tov-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                Featured on this page
              </span>
              <span class="tov-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                Shared on our socials
              </span>
            </div>
            <a href="mailto:hello@unit.report?subject=My AVA Experience&body=Hi UNIT team, I'd like to share my experience with AVA..." class="btn-tov">
              Share Your Experience →
            </a>
            <div class="tov-sub">Takes 2 minutes · We reply to every submission</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- INTEGRATIONS + SECURITY --}}
{{-- INTEGRATIONS --}}
<section class="integrations-sec">
  <div class="w">
    <div class="int-top">
      <div class="sec-eye">{{ $worker['name'] }} connects with</div>
      <h2 class="sec-h">One-click connections. No complex setup.</h2>
    </div>
    <div class="int-logos">
      @php
        $integrations = [
          ['Google Workspace','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22C6.48 22 2 17.52 2 12S6.48 2 12 2s10 4.48 10 10-4.48 10-10 10z"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>'],
          ['HubSpot','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2a5 5 0 015 5 5 5 0 01-5 5 5 5 0 01-5-5 5 5 0 015-5z"/><path d="M12 12c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z"/></svg>'],
          ['Salesforce','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16v16H4z"/><path d="M4 9h16M9 4v16"/></svg>'],
          ['Slack','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>'],
          ['QuickBooks','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>'],
          ['Outlook','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>'],
          ['Calendly','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>'],
          ['Stripe','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>'],
          ['Zapier','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'],
          ['Gmail','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>'],
          ['Notion','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 9h6M9 13h6M9 17h4"/></svg>'],
        ];
      @endphp
      @foreach($integrations as $int)
      <div class="int-logo">{!! $int[1] !!} {{ $int[0] }}</div>
      @endforeach
      <span class="int-more">+ More</span>
    </div>
  </div>
</section>

{{-- SECURITY --}}
<section class="security-sec">
  <div class="w">
    <div class="sec-top">
      <div class="sec-eye">Enterprise-grade security</div>
      <h2 class="sec-h">Your data is safe. Your trust is everything.</h2>
    </div>
    <div class="sec-badges">
      @php
        $secBadges = [
          ['SOC 2 Type II Compliant','<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
          ['Data encrypted in transit & at rest','<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>'],
          ['Role-based access & permissions','<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>'],
          ['Audit logs & activity history','<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>'],
          ['GDPR Compliant','<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>'],
        ];
      @endphp
      @foreach($secBadges as $badge)
      <div class="sec-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">{!! $badge[1] !!}</svg>
        <div class="sec-badge-label">{{ $badge[0] }}</div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- FAQ --}}
<section class="faq-sec sec">
  <div class="w">
    <div class="faq-grid">
      <div>
        <div class="sec-eye">Frequently asked questions</div>
        <h2 class="sec-h">Everything you want to know about {{ $worker['name'] }}.</h2>
        <a href="{{ route('register') }}" class="btn-nav-hire" style="display:inline-flex;margin-top:8px">
          Get started free
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
      <div class="faq-list">
        @foreach($worker['faq'] as $faq)
        <div class="faq-item">
          <div class="faq-q" onclick="this.closest('.faq-item').classList.toggle('open')">
            <span>{{ $faq['q'] }}</span>
            <span class="faq-icon">+</span>
          </div>
          <div class="faq-a">{{ $faq['a'] }}</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- FINAL CTA --}}
<section class="cta-final">
  <div class="w">
    <div class="cta-final-inner">
      <div class="cta-final-left">
        <div class="cta-final-eyebrow">Your workday ended.</div>
        <h2 class="cta-final-h">{{ $worker['name'] }}'s <em>didn't.</em></h2>
        <p class="cta-final-sub">Tomorrow {{ $worker['name'] }} will protect someone else's business. Or yours.</p>
        <a href="{{ route('register') }}" class="btn-cta-final">
          Hire {{ $worker['name'] }} Today
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <div class="cta-note">No credit card required.</div>
      </div>
      <div class="cta-final-right">
        <img src="/images/ava-stand.png" alt="{{ $worker['name'] }}" style="object-position:center top;max-height:380px;width:auto;margin:0 auto">
      </div>
    </div>
  </div>
</section>

{{-- FOOTER --}}
<footer class="footer">
  <div class="w">
    <div class="ft-inner">
      <div class="ft-logo">UNIT</div>
      <div class="ft-links">
        <a href="{{ route('workers.page') }}">All Workers</a>
        <a href="{{ route('pricing') }}">Pricing</a>
        <a href="{{ route('register') }}">Get Started</a>
        <a href="{{ route('login') }}">Log In</a>
      </div>
      <div class="ft-copy">© {{ date('Y') }} UNIT. All rights reserved.</div>
    </div>
  </div>
</footer>

<script>
// Live clock
(function(){
  function updateClock(){
    var el = document.getElementById('live-clock');
    if(!el) return;
    var now = new Date();
    var h = now.getHours();
    var m = now.getMinutes();
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    el.textContent = h + ':' + (m < 10 ? '0'+m : m) + ' ' + ampm;
  }
  updateClock();
  setInterval(updateClock, 60000);
})();

// Dark mode toggle
(function(){
  var btn = document.getElementById('themeToggle');
  if(!btn) return;
  var html = document.documentElement;
  var moon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>';
  var sun  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';
  btn.addEventListener('click', function(){
    var dark = html.getAttribute('data-theme') === 'dark';
    html.setAttribute('data-theme', dark ? 'light' : 'dark');
    btn.innerHTML = dark ? moon : sun;
  });
})();
</script>

<x-self-learn />

</body>
</html>

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
  $color  = match($worker['slug'] ?? 'ava') {
    'dox'  => '#1F2937',
    'mox'  => '#B45309',
    'nux'  => '#1D4ED8',
    default => '#4C1D95',
  };
  $colorRgb = match($worker['slug'] ?? 'ava') {
    'dox'  => '31,41,55',
    'mox'  => '180,83,9',
    'nux'  => '29,78,216',
    default => '76,29,149',
  };
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

/* ── NAV ── */
.nav{
  position:fixed;top:0;left:0;right:0;z-index:100;
  background:#fff;
  border-bottom:1px solid #EDEDED;
  box-shadow:0 1px 3px rgba(0,0,0,.06);
}
.nav-i{
  display:flex;align-items:center;justify-content:space-between;
  height:68px;padding:0 var(--pad);max-width:var(--max);margin:0 auto;
}
/* logo */
.nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none}
.nav-logo-icon{
  width:38px;height:38px;flex-shrink:0;
}
.nav-logo-text{}
.nav-logo-name{
  display:block;font-size:1.15rem;font-weight:800;
  color:#0D0D0D;letter-spacing:-.4px;line-height:1.1;
}
.nav-logo-sub{
  display:block;font-size:9px;font-weight:700;
  letter-spacing:.18em;text-transform:uppercase;color:#9CA3AF;line-height:1;
}
/* center links */
.nav-links{display:flex;align-items:center;gap:2px}
.nav-link{
  font-size:13.5px;font-weight:500;color:#374151;
  padding:8px 13px;border-radius:8px;
  transition:color .15s,background .15s;white-space:nowrap;
}
.nav-link:hover{color:#0D0D0D;background:#F5F5F5}
/* right actions */
.nav-actions{display:flex;align-items:center;gap:8px}
.btn-login{
  display:inline-flex;align-items:center;
  padding:9px 20px;border-radius:99px;
  font-size:14px;font-weight:600;color:#374151;
  border:1.5px solid #D1D5DB;
  transition:border-color .15s,color .15s;
}
.btn-login:hover{border-color:#9CA3AF;color:#0D0D0D}
.btn-nav-hire{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 22px;border-radius:99px;
  font-size:14px;font-weight:700;color:#fff;
  background:var(--brand);
  box-shadow:0 2px 12px rgba(var(--brand-rgb),.35);
  transition:opacity .15s,transform .15s;white-space:nowrap;
}
.btn-nav-hire:hover{opacity:.9;transform:translateY(-1px)}
.btn-nav-hire svg{transition:transform .15s}
.btn-nav-hire:hover svg{transform:translateX(2px)}
/* mobile: hide center links */
@media(max-width:900px){.nav-links{display:none}}
@media(max-width:600px){.btn-login{display:none}}

/* ── HERO ── */
.hero-worker{
  position:relative;min-height:100vh;
  background:#000;overflow:hidden;
  display:flex;align-items:center;
  padding-top:68px;
}
.hero-bg{position:absolute;inset:0;z-index:0}
.hero-bg img{width:100%;height:100%;object-fit:cover;object-position:center top;opacity:.55}
.hero-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(to right,rgba(0,0,0,.85) 0%,rgba(0,0,0,.5) 50%,rgba(0,0,0,.2) 100%)}
.hero-inner{
  position:relative;z-index:2;
  display:grid;grid-template-columns:1fr 340px;
  gap:48px;align-items:center;
  width:100%;padding:clamp(60px,8vw,100px) var(--pad);
  max-width:var(--max);margin:0 auto;
}
.hero-eye{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:14px}
.hero-h{
  font-size:clamp(2rem,4.5vw,3.4rem);font-weight:800;
  line-height:1.08;letter-spacing:-.03em;
  color:#fff;margin-bottom:18px;
}
.hero-h em{font-style:normal;color:{{ $color }}}
.hero-p{font-size:1rem;color:rgba(255,255,255,.75);line-height:1.75;margin-bottom:28px;max-width:480px}
.hero-btns{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.btn-hire-hero{
  display:inline-flex;align-items:center;gap:8px;
  padding:13px 26px;border-radius:99px;
  font-size:15px;font-weight:700;color:#fff;
  background:var(--brand);
  box-shadow:0 4px 20px rgba(var(--brand-rgb),.5);
  transition:opacity .15s,transform .15s;
}
.btn-hire-hero:hover{opacity:.9;transform:translateY(-2px)}
.btn-watch-hero{
  display:inline-flex;align-items:center;gap:7px;
  padding:12px 20px;border-radius:99px;
  font-size:14px;font-weight:600;color:rgba(255,255,255,.85);
  border:1.5px solid rgba(255,255,255,.3);
  transition:border-color .15s;
}
.btn-watch-hero:hover{border-color:rgba(255,255,255,.7)}

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
.hc-progress-bar{height:100%;background:var(--brand);border-radius:99px;width:68%;animation:progAnim 3s ease-in-out infinite alternate}
@keyframes progAnim{from{width:55%}to{width:82%}}
.hc-divider{height:1px;background:rgba(255,255,255,.08);margin:14px 0}
.hc-completed-label{font-size:10.5px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:10px}
.hc-done-item{display:flex;align-items:center;gap:8px;margin-bottom:7px;font-size:13px;color:rgba(255,255,255,.8)}
.hc-check{width:18px;height:18px;border-radius:50%;background:#22C55E;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.hc-check svg{width:10px;height:10px;stroke:#fff;stroke-width:2.5;fill:none}
.hc-revenue{
  background:linear-gradient(135deg,rgba(var(--brand-rgb),.2),rgba(var(--brand-rgb),.08));
  border:1px solid rgba(var(--brand-rgb),.3);
  border-radius:12px;padding:14px;margin-top:14px;
}
.hc-rev-label{font-size:10.5px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:6px}
.hc-rev-amount{font-size:1.6rem;font-weight:800;color:{{ $color }};letter-spacing:-.04em;line-height:1}
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
.sec-eye{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--brand);margin-bottom:12px}
.sec-h{font-size:clamp(1.6rem,3vw,2.4rem);font-weight:800;line-height:1.12;letter-spacing:-.03em;color:var(--text);margin-bottom:14px}
.sec-p{font-size:1rem;color:var(--t3);line-height:1.7}
.center{text-align:center}.center .sec-p{max-width:520px;margin:0 auto}

/* ── PROBLEM SECTION ── */
.problem-sec{background:var(--bg);border-top:1px solid var(--border)}
[data-theme="dark"] .problem-sec{background:#0D0D0D;border-color:#2D2D2D}
.prob-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:clamp(32px,4vw,48px)}
.prob-card{
  background:var(--soft);border:1px solid var(--border);
  border-radius:16px;padding:24px;
  display:flex;flex-direction:column;gap:12px;
}
[data-theme="dark"] .prob-card{background:#111;border-color:#2D2D2D}
.prob-card.solution{background:rgba(var(--brand-rgb),.06);border-color:rgba(var(--brand-rgb),.2)}
[data-theme="dark"] .prob-card.solution{background:rgba(var(--brand-rgb),.12)}
.prob-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:var(--bg);border:1px solid var(--border);flex-shrink:0}
[data-theme="dark"] .prob-icon{background:#1a1a1a;border-color:#3D3D3D}
.prob-icon svg{width:20px;height:20px;color:var(--t3)}
.prob-card.solution .prob-icon{background:rgba(var(--brand-rgb),.1);border-color:rgba(var(--brand-rgb),.25)}
.prob-card.solution .prob-icon svg{color:var(--brand)}
.prob-h{font-size:15px;font-weight:700;color:var(--text)}
.prob-p{font-size:13px;color:var(--t3);line-height:1.6}
.prob-solution-tag{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--brand);display:flex;align-items:center;gap:6px}
.prob-bullets{display:flex;flex-direction:column;gap:7px;margin-top:4px}
.prob-bullet{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--t2)}
[data-theme="dark"] .prob-bullet{color:#D1D5DB}
.prob-check{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:var(--brand)}
.prob-check svg{width:10px;height:10px;stroke:#fff;stroke-width:2.5;fill:none}

/* ── DAY IN LIFE ── */
.day-sec{background:var(--soft);border-top:1px solid var(--border)}
[data-theme="dark"] .day-sec{background:#161616;border-color:#2D2D2D}
.day-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:clamp(28px,4vw,44px)}
.day-client{text-align:right}
.day-client-name{font-size:14px;font-weight:700;color:var(--text)}
.day-client-sub{font-size:12px;color:var(--t4);margin-top:2px}
/* horizontal step flow */
.day-flow{display:grid;grid-template-columns:repeat(6,1fr);gap:0;margin-bottom:32px;position:relative}
.day-flow::before{content:'';position:absolute;top:36px;left:8%;right:8%;height:2px;background:var(--border);z-index:0}
[data-theme="dark"] .day-flow::before{background:#3D3D3D}
.day-step{display:flex;flex-direction:column;align-items:center;text-align:center;padding:0 6px;position:relative;z-index:1}
.day-step-node{
  width:72px;height:72px;border-radius:14px;
  background:var(--bg);border:1.5px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  margin-bottom:12px;position:relative;
  box-shadow:0 2px 8px rgba(0,0,0,.06);
  overflow:hidden;
}
[data-theme="dark"] .day-step-node{background:#111;border-color:#3D3D3D}
.day-step-node img{width:100%;height:100%;object-fit:cover}
.day-step-node svg{width:26px;height:26px;color:var(--t4)}
.day-step-num{
  position:absolute;bottom:-1px;right:-1px;
  width:20px;height:20px;border-radius:6px 0 0 0;
  background:var(--brand);
  font-size:9px;font-weight:800;color:#fff;
  display:flex;align-items:center;justify-content:center;
  z-index:2;
}
.day-step-label{font-size:11px;font-weight:600;color:var(--brand);letter-spacing:.05em;text-transform:uppercase;margin-bottom:4px}
.day-step-desc{font-size:12px;color:var(--t3);line-height:1.5}
/* mission complete bar */
.mission-bar{
  display:flex;align-items:center;justify-content:space-between;
  background:linear-gradient(135deg,rgba(var(--brand-rgb),.08),rgba(var(--brand-rgb),.03));
  border:1px solid rgba(var(--brand-rgb),.2);
  border-radius:14px;padding:18px 24px;flex-wrap:wrap;gap:16px;
}
.mission-txt{font-size:16px;font-weight:700;color:var(--text)}
.mission-stats{display:flex;gap:32px}
.mission-stat{text-align:center}
.mission-stat-n{font-size:1.2rem;font-weight:800;color:var(--brand);letter-spacing:-.03em}
.mission-stat-l{font-size:11px;color:var(--t4);margin-top:2px}

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
.industry-icon svg{width:22px;height:22px;color:var(--brand)}
.industry-label{font-size:11px;font-weight:600;color:var(--t3);text-align:center;line-height:1.3}

/* testimonials */
.testi-grid{display:grid;grid-template-columns:1fr;gap:14px}
.testi-card{
  background:var(--soft);border:1px solid var(--border);
  border-radius:16px;padding:20px;
}
[data-theme="dark"] .testi-card{background:#111;border-color:#2D2D2D}
.testi-stars{color:#F59E0B;font-size:12px;letter-spacing:1px;margin-bottom:10px}
.testi-q{font-size:13.5px;color:var(--t2);line-height:1.65;font-style:italic;margin-bottom:14px}
[data-theme="dark"] .testi-q{color:#D1D5DB}
.testi-auth{display:flex;align-items:center;gap:10px}
.testi-av{width:36px;height:36px;border-radius:50%;background:var(--brand);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0}
.testi-name{font-size:13px;font-weight:700;color:var(--text)}
.testi-co{font-size:11px;color:var(--t4)}
.testi-rev{font-size:13px;font-weight:700;color:var(--brand);margin-top:6px}

/* integrations */
.integrations-sec{background:var(--soft);border-top:1px solid var(--border)}
[data-theme="dark"] .integrations-sec{background:#161616;border-color:#2D2D2D}
.int-logos{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px;align-items:center}
.int-logo{
  display:flex;align-items:center;gap:8px;
  padding:10px 16px;border-radius:10px;
  background:var(--bg);border:1px solid var(--border);
  font-size:13px;font-weight:600;color:var(--t2);
}
[data-theme="dark"] .int-logo{background:#111;border-color:#2D2D2D;color:#D1D5DB}
.int-logo svg{width:18px;height:18px;flex-shrink:0}
.int-more{font-size:13px;color:var(--t4);padding:10px 16px}

/* security */
.sec-badges{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-top:20px}
.sec-badge{
  display:flex;flex-direction:column;align-items:center;gap:8px;
  padding:16px 10px;border-radius:12px;
  background:var(--bg);border:1px solid var(--border);text-align:center;
}
[data-theme="dark"] .sec-badge{background:#111;border-color:#2D2D2D}
.sec-badge svg{width:24px;height:24px;color:var(--brand)}
.sec-badge-label{font-size:11px;font-weight:600;color:var(--t2);line-height:1.35}
[data-theme="dark"] .sec-badge-label{color:#D1D5DB}

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
.faq-item.open .faq-icon{transform:rotate(45deg);color:var(--brand)}
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
.cta-final-h em{font-style:normal;color:{{ $color }}}
.cta-final-sub{font-size:14px;color:rgba(255,255,255,.5);line-height:1.7;margin-bottom:24px;max-width:400px}
.btn-cta-final{
  display:inline-flex;align-items:center;gap:8px;
  padding:14px 28px;border-radius:12px;
  font-size:15px;font-weight:700;color:#fff;
  background:var(--brand);
  box-shadow:0 4px 20px rgba(var(--brand-rgb),.45);
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
    {{-- Logo --}}
    <a href="{{ route('home2') }}" class="nav-logo">
      <svg class="nav-logo-icon" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="19" cy="19" r="19" fill="#4C1D95"/>
        {{-- sunburst rays --}}
        <g stroke="#F5D97E" stroke-width="2" stroke-linecap="round">
          <line x1="19" y1="4"  x2="19" y2="9"/>
          <line x1="19" y1="29" x2="19" y2="34"/>
          <line x1="4"  y1="19" x2="9"  y2="19"/>
          <line x1="29" y1="19" x2="34" y2="19"/>
          <line x1="8.1"  y1="8.1"  x2="11.6" y2="11.6"/>
          <line x1="26.4" y1="26.4" x2="29.9" y2="29.9"/>
          <line x1="29.9" y1="8.1"  x2="26.4" y2="11.6"/>
          <line x1="11.6" y1="26.4" x2="8.1"  y2="29.9"/>
        </g>
        <circle cx="19" cy="19" r="5" fill="#F5D97E"/>
      </svg>
      <div class="nav-logo-text">
        <span class="nav-logo-name">UNIT</span>
        <span class="nav-logo-sub">AI Workers</span>
      </div>
    </a>

    {{-- Center links --}}
    <div class="nav-links">
      <a href="{{ route('workers.page') }}" class="nav-link">Meet the Team</a>
      <a href="#day-in-life" class="nav-link">How It Works</a>
      <a href="#faq" class="nav-link">For Business</a>
      <a href="#integrations" class="nav-link">Resources</a>
      <a href="{{ route('pricing') }}" class="nav-link">Pricing</a>
    </div>

    {{-- Actions --}}
    <div class="nav-actions">
      @auth
        <a href="{{ route('dashboard') }}" class="btn-login">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="btn-login">Log in</a>
      @endauth
      <a href="{{ route('register') }}" class="btn-nav-hire">
        Hire {{ $worker['name'] }}
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</nav>

{{-- HERO --}}
<section class="hero-worker">
  <div class="hero-bg">
    <img src="/images/ava-stand.png" alt="{{ $worker['name'] }}" style="object-position:center 15%">
  </div>
  <div class="hero-inner">
    <div>
      <div class="hero-eye">Meet {{ $worker['name'] }}</div>
      <h1 class="hero-h">{!! $worker['headline'] !!}</h1>
      <p class="hero-p">{{ $worker['sub'] }}</p>
      <div class="hero-btns">
        <a href="{{ route('register') }}" class="btn-hire-hero">
          Hire {{ $worker['name'] }} Today
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="#day-in-life" class="btn-watch-hero">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z" fill="currentColor" stroke="none"/></svg>
          Watch Full Day
        </a>
      </div>
    </div>

    {{-- Status card --}}
    <div class="hero-card">
      <div class="hc-status">
        <div class="hc-dot"></div>
        <span class="hc-status-txt">{{ $worker['name'] }} IS ON SHIFT</span>
      </div>
      <div class="hc-time" id="live-clock">09:42 AM</div>
      <div class="hc-task-label">Current Task</div>
      <div class="hc-task">
        <div class="hc-task-name">Checking 14 contracts expiring this week…</div>
        <div class="hc-progress"><div class="hc-progress-bar"></div></div>
      </div>
      <div class="hc-divider"></div>
      <div class="hc-completed-label">Completed Today</div>
      @php
        $doneItems = [
          '42 renewals reviewed',
          '18 reminders sent',
          '6 customers retained',
        ];
      @endphp
      @foreach($doneItems as $item)
      <div class="hc-done-item">
        <div class="hc-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
        {{ $item }}
      </div>
      @endforeach
      <div class="hc-revenue">
        <div class="hc-rev-label">Revenue Protected</div>
        <div class="hc-rev-amount">${{ number_format($totalTx * 3.85, 0) }}</div>
        <div class="hc-rev-streak">{{ $deploymentCount * 18 }}-day streak 🔥</div>
      </div>
      <a href="{{ route('register') }}" class="hc-feed-btn">
        View Live Feed
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</section>

{{-- THE PROBLEM --}}
<section class="problem-sec sec">
  <div class="w">
    <div class="center" style="margin-bottom:clamp(32px,4vw,48px)">
      <div class="sec-eye">The Problem</div>
      <h2 class="sec-h">Too many renewals slip through the cracks.</h2>
    </div>
    <div class="prob-grid">
      <div class="prob-card">
        <div class="prob-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div class="prob-h">Your team gets busy.</div>
        <p class="prob-p">Contracts get forgotten. Customers disappear.</p>
      </div>
      <div class="prob-card">
        <div class="prob-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        </div>
        <div class="prob-h">Manual reminders don't scale.</div>
        <p class="prob-p">Someone always slips through.</p>
      </div>
      <div class="prob-card">
        <div class="prob-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div class="prob-h">Every missed renewal costs money.</div>
        <p class="prob-p">Not because customers wanted to leave. Because nobody remembered.</p>
      </div>
      <div class="prob-card solution">
        <div class="prob-solution-tag">
          <img src="/images/ava.png" alt="{{ $worker['name'] }}" style="width:22px;height:22px;border-radius:50%;object-fit:cover;object-position:center top">
          {{ $worker['name'] }} changes that.
        </div>
        <div class="prob-bullets">
          @foreach(['Detects upcoming renewals','Sends timely reminders','Customers renew','Revenue protected'] as $b)
          <div class="prob-bullet">
            <div class="prob-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
            {{ $b }}
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

{{-- A DAY IN AVA'S LIFE --}}
<section class="day-sec sec" id="day-in-life">
  <div class="w">
    <div class="day-top">
      <div>
        <div class="sec-eye">A day in {{ $worker['name'] }}'s life</div>
        <h2 class="sec-h" style="margin-bottom:0">Follow {{ $worker['name'] }} through one renewal.</h2>
      </div>
      <div class="day-client">
        <div class="day-client-name">ABC Dental</div>
        <div class="day-client-sub">Renewal Date: Tomorrow</div>
      </div>
    </div>

    {{-- step flow --}}
    <div class="day-flow">
      @php
        $daySteps = [
          ['num'=>1,'label'=>'Ava notices','desc'=>'A contract is about to expire.','icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>'],
          ['num'=>2,'label'=>'Reviews history','desc'=>'She checks customer details and history.','icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>'],
          ['num'=>3,'label'=>'Prepares reminder','desc'=>'Personalized reminder is created.','icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/></svg>'],
          ['num'=>4,'label'=>'Reminder sent','desc'=>'The reminder is delivered at the perfect time.','icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 01-2.18 2A19.79 19.79 0 0112 18.69a19.5 19.5 0 01-5-5 19.79 19.79 0 01-3.07-8.67A2 2 0 015.91 3h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L10.09 10a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0124 17z"/></svg>'],
          ['num'=>5,'label'=>'Customer renews','desc'=>'The customer renews with one click.','icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'],
          ['num'=>6,'label'=>'Ava updates everything','desc'=>'CRM updated. Invoice logged. Task closed.','icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>'],
        ];
      @endphp
      @foreach($daySteps as $step)
      <div class="day-step">
        <div class="day-step-node">
          {!! $step['icon'] !!}
          <div class="day-step-num">{{ $step['num'] }}</div>
        </div>
        <div class="day-step-label">{{ $step['label'] }}</div>
        <div class="day-step-desc">{{ $step['desc'] }}</div>
      </div>
      @endforeach
    </div>

    {{-- mission complete --}}
    <div class="mission-bar">
      <div class="mission-txt">Mission Complete 🎉</div>
      <div class="mission-stats">
        <div class="mission-stat">
          <div class="mission-stat-n">2 minutes</div>
          <div class="mission-stat-l">Time Taken</div>
        </div>
        <div class="mission-stat">
          <div class="mission-stat-n">35 minutes</div>
          <div class="mission-stat-l">Human effort saved</div>
        </div>
      </div>
    </div>
  </div>
</section>

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
      <div>
        <div class="sec-eye">What business owners say</div>
        <div class="testi-grid">
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
      </div>
    </div>
  </div>
</section>

{{-- INTEGRATIONS + SECURITY --}}
<section class="integrations-sec sec">
  <div class="w">
    <div class="split-grid">
      <div>
        <div class="sec-eye">{{ $worker['name'] }} connects with</div>
        <h2 class="sec-h" style="font-size:clamp(1.3rem,2.2vw,1.7rem);margin-bottom:8px">One-click connections. No complex setup.</h2>
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
            ];
          @endphp
          @foreach($integrations as $int)
          <div class="int-logo">{!! $int[1] !!} {{ $int[0] }}</div>
          @endforeach
          <span class="int-more">+ More</span>
        </div>
      </div>
      <div>
        <div class="sec-eye">Enterprise-grade security</div>
        <h2 class="sec-h" style="font-size:clamp(1.3rem,2.2vw,1.7rem);margin-bottom:8px">Your data is safe. Your trust is everything.</h2>
        <div class="sec-badges">
          @php
            $secBadges = [
              ['SOC 2 Type II Compliant','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'],
              ['Data encrypted in transit & at rest','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>'],
              ['Role-based access & permissions','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>'],
              ['Audit logs & activity history','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>'],
              ['GDPR Compliant','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>'],
            ];
          @endphp
          @foreach($secBadges as $badge)
          <div class="sec-badge">
            {!! $badge[1] !!}
            <div class="sec-badge-label">{{ $badge[0] }}</div>
          </div>
          @endforeach
        </div>
      </div>
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
</script>

<x-self-learn />

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Meet the Team — UNIT AI Workers</title>
<meta name="description" content="Meet every UNIT worker. Each one has one job, does it exceptionally well, and runs 24/7 so you don't have to.">
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
  --brand:#F5C518;--brand-dark:#0D0D0D;
  --text:#0D0D0D;--t2:#374151;--t3:#6B7280;--t4:#9CA3AF;
  --border:#E5E7EB;--bg:#FFFFFF;--soft:#F8F8F6;
  --font-h:'Inter',sans-serif;--font-b:'Inter',sans-serif;
  --max:1160px;--pad:clamp(20px,5vw,48px);
}
[data-theme="dark"]{
  --text:#F3F4F6;--t2:#D1D5DB;--t3:#9CA3AF;--t4:#6B7280;
  --border:#2D2D2D;--bg:#0D0D0D;--soft:#161616;--brand:#F5C518;
}
[data-theme="dark"] .nav{background:rgba(13,13,13,.92);border-color:#2D2D2D}
[data-theme="dark"] .hero-page{background:#0D0D0D}
[data-theme="dark"] .hero-fade-page{background:linear-gradient(to right,#0D0D0D 0%,rgba(13,13,13,.65) 18%,transparent 40%)}
[data-theme="dark"] .workers-sec{background:#0D0D0D}
[data-theme="dark"] .wk-card{background:#111;border-color:#2D2D2D}
[data-theme="dark"] .wk-quote{color:#D1D5DB}
[data-theme="dark"] .wk-bullet{color:#D1D5DB}
[data-theme="dark"] .wk-role{color:#9CA3AF}
[data-theme="dark"] .btn-watch-wk{color:#D1D5DB;border-color:#3D3D3D}
[data-theme="dark"] .btn-watch-wk:hover{border-color:#888;color:#F3F4F6}
[data-theme="dark"] .search-wrap input{background:#111;border-color:#3D3D3D;color:#F3F4F6}
[data-theme="dark"] .search-wrap input::placeholder{color:#6B7280}
[data-theme="dark"] .search-wrap svg{color:#6B7280}
[data-theme="dark"] .tag-btn{background:#1a1a1a;border-color:#3D3D3D;color:#9CA3AF}
[data-theme="dark"] .tag-btn:hover{border-color:#0D0D0D;color:#0D0D0D}
[data-theme="dark"] .tag-btn.on{background:#0D0D0D;border-color:#0D0D0D;color:#fff}
[data-theme="dark"] .behind-bar{background:#0D0D0D;border-color:#2D2D2D}
[data-theme="dark"] .behind-item{border-color:#2D2D2D}
[data-theme="dark"] .behind-icon{background:#1a1a1a}
[data-theme="dark"] .behind-h{color:#F3F4F6}
[data-theme="dark"] .behind-p{color:#9CA3AF}
[data-theme="dark"] .cta-foot{background:#0D0D0D}
[data-theme="dark"] .empty-state{color:#6B7280}
[data-theme="dark"] .btn-login{color:#D1D5DB;border-color:#2D2D2D}

/* theme toggle */
.theme-toggle{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid var(--border);background:transparent;color:var(--t2);cursor:pointer;transition:all .2s;flex-shrink:0}
.theme-toggle:hover{background:var(--soft);color:var(--text)}
.theme-toggle svg{width:17px;height:17px}
.icon-sun{display:none}.icon-moon{display:block}
[data-theme="dark"] .icon-sun{display:block}[data-theme="dark"] .icon-moon{display:none}

body{font-family:var(--font-b);color:var(--text);background:var(--bg);-webkit-font-smoothing:antialiased;overflow-x:hidden}
.w{max-width:var(--max);margin:0 auto;padding:0 var(--pad)}

/* ── NAV ── */
.nav{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(255,255,255,.92);backdrop-filter:blur(16px);border-bottom:1px solid var(--border)}
.nav-i{display:flex;align-items:center;justify-content:space-between;height:62px}
.logo{display:flex;align-items:center}
.logo-name{font-family:var(--font-h);font-size:1.5rem;font-weight:800;color:var(--text);letter-spacing:-.5px}
.nav-links{display:flex;align-items:center;gap:28px}
.nav-links a{font-size:14px;font-weight:500;color:var(--t2);transition:color .15s}
.nav-links a:hover{color:var(--text)}
.nav-links a.active{font-weight:700;color:#0D0D0D}
.nav-acts{display:flex;align-items:center;gap:10px}
.btn-login{padding:8px 18px;border-radius:8px;font-size:14px;font-weight:600;color:var(--t2);border:1px solid var(--border);transition:all .15s}
.btn-login:hover{border-color:#bbb;color:var(--text)}
.btn-cta{padding:10px 22px;border-radius:99px;font-size:14px;font-weight:700;background:#0D0D0D;color:#fff;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 12px rgba(0,0,0,.12);transition:opacity .15s,transform .15s}
.btn-cta:hover{opacity:.9;transform:translateY(-1px)}
.ham{display:none;flex-direction:column;gap:5px;padding:4px}
.ham span{display:block;width:22px;height:2px;background:var(--text);border-radius:2px}

/* ── HERO ── */
.hero-page{padding-top:62px;background:#fff;display:grid;grid-template-columns:1fr 1fr;min-height:68vh;overflow:hidden}
.hero-page-left{display:flex;align-items:center;padding-top:clamp(48px,6vw,80px);padding-bottom:clamp(48px,6vw,80px);padding-right:clamp(32px,4vw,56px);padding-left:max(var(--pad),calc((100vw - var(--max))/2 + var(--pad)))}
.hero-page-inner{max-width:480px}
.page-eye{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#0D0D0D;margin-bottom:14px}
.hero-page-h{font-family:var(--font-h);font-size:clamp(1.9rem,3.6vw,2.8rem);font-weight:800;line-height:1.1;letter-spacing:-.03em;color:var(--text);margin-bottom:18px}
.hero-page-h em{font-style:normal;position:relative;display:inline}
.hero-page-h em::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}
.hero-page-p{font-size:1rem;color:var(--t2);line-height:1.75;margin-bottom:28px;max-width:400px}
.hero-proof{display:flex;align-items:center;gap:12px}
.proof-avs{display:flex}
.proof-avs img{width:34px;height:34px;border-radius:50%;border:2px solid #fff;margin-left:-8px;outline:1.5px solid #d1d5db;object-fit:cover;object-position:center top;flex-shrink:0;box-shadow:0 1px 4px rgba(0,0,0,.12)}
.proof-avs img:first-child{margin-left:0}
.proof-txt{font-size:13px;color:var(--t3);line-height:1.5}
.proof-txt strong{color:var(--text);display:block}
.hero-page-right{position:relative;overflow:hidden;background:#000}
.hero-page-right img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center top}
.hero-page-spacer{display:block;width:100%;min-height:calc(68vh - 62px)}
.hero-fade-page{position:absolute;inset:0;z-index:2;background:linear-gradient(to right,#fff 0%,rgba(255,255,255,.55) 15%,transparent 36%);pointer-events:none}
.hero-badge{position:absolute;bottom:24px;right:24px;z-index:3;background:#fff;border:1px solid var(--border);border-radius:16px;padding:12px 16px;display:flex;align-items:center;gap:10px;box-shadow:0 4px 20px rgba(0,0,0,.1)}
.badge-txt{font-size:13px;font-weight:700;color:var(--text);line-height:1.45}

/* ── ACTIVITY FEED ── */
.activity-feed{background:#0A0A0A;border-top:1px solid #1F1F1F;border-bottom:1px solid #1F1F1F;overflow:hidden;position:relative}
.activity-feed::before,.activity-feed::after{content:'';position:absolute;top:0;bottom:0;width:80px;z-index:2;pointer-events:none}
.activity-feed::before{left:0;background:linear-gradient(to right,#0A0A0A,transparent)}
.activity-feed::after{right:0;background:linear-gradient(to left,#0A0A0A,transparent)}
.feed-track{display:flex;align-items:center;width:max-content;animation:feedScroll 70s linear infinite}
.feed-track:hover{animation-play-state:paused}
@keyframes feedScroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.feed-item{display:flex;align-items:center;gap:12px;padding:17px 36px;border-right:1px solid #1F1F1F;white-space:nowrap;flex-shrink:0}
.feed-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;position:relative}
.feed-dot::after{content:'';position:absolute;inset:-4px;border-radius:50%;opacity:0;animation:dotPing 2.4s ease-out infinite}
.feed-dot.green{background:#22C55E;box-shadow:0 0 8px rgba(34,197,94,.7)}.feed-dot.green::after{background:rgba(34,197,94,.35)}
.feed-dot.blue{background:#3B82F6;box-shadow:0 0 8px rgba(59,130,246,.7)}.feed-dot.blue::after{background:rgba(59,130,246,.35)}
.feed-dot.amber{background:#F59E0B;box-shadow:0 0 8px rgba(245,158,11,.7)}.feed-dot.amber::after{background:rgba(245,158,11,.35)}
.feed-item:nth-child(2) .feed-dot::after{animation-delay:.4s}
.feed-item:nth-child(3) .feed-dot::after{animation-delay:.8s}
.feed-item:nth-child(4) .feed-dot::after{animation-delay:1.2s}
.feed-item:nth-child(5) .feed-dot::after{animation-delay:1.6s}
.feed-item:nth-child(6) .feed-dot::after{animation-delay:2s}
.feed-item:nth-child(7) .feed-dot::after{animation-delay:.3s}
.feed-item:nth-child(8) .feed-dot::after{animation-delay:.9s}
.feed-item:nth-child(9) .feed-dot::after{animation-delay:1.5s}
.feed-item:nth-child(10) .feed-dot::after{animation-delay:2.1s}
@keyframes dotPing{0%{transform:scale(1);opacity:.8}70%{transform:scale(2.8);opacity:0}100%{transform:scale(2.8);opacity:0}}
.feed-worker{font-size:13px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.feed-action{font-size:14px;color:rgba(255,255,255,.85)}
.feed-time{font-size:12px;color:rgba(255,255,255,.4);margin-left:4px}

/* ── SECTION ATOMS ── */
.sec-eye{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#0D0D0D;margin-bottom:12px}
.sec-h{font-family:var(--font-h);font-size:clamp(1.7rem,3.2vw,2.4rem);font-weight:800;line-height:1.12;letter-spacing:-.03em;color:var(--text);margin-bottom:12px}

/* ── WORKERS SECTION ── */
.workers-sec{background:var(--soft);padding:clamp(56px,7vw,88px) 0}

/* search + tags toolbar */
.toolbar{margin-bottom:clamp(32px,4vw,48px)}
.toolbar-top{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:20px;flex-wrap:wrap}
.toolbar-top-left .sec-eye{margin-bottom:6px}
.toolbar-top-left .sec-h{margin-bottom:0;font-size:clamp(1.5rem,2.6vw,2rem)}
.search-wrap{
  position:relative;display:flex;align-items:center;
  flex-shrink:0;
}
.search-wrap svg{position:absolute;left:14px;width:17px;height:17px;color:var(--t4);pointer-events:none;flex-shrink:0}
.search-wrap input{
  padding:11px 16px 11px 42px;
  border-radius:12px;
  border:1.5px solid var(--border);
  background:#fff;
  font-family:var(--font-b);font-size:14px;color:var(--text);
  width:260px;
  outline:none;
  transition:border-color .15s,box-shadow .15s;
}
.search-wrap input:focus{border-color:#0D0D0D;box-shadow:0 0 0 3px rgba(245,197,24,.12)}
.search-wrap input::placeholder{color:var(--t4)}

/* specialty tag pills */
.tags{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.tags-label{font-size:12px;font-weight:600;color:var(--t4);letter-spacing:.04em;margin-right:4px}
.tag-btn{
  padding:6px 14px;border-radius:99px;font-size:12.5px;font-weight:600;
  color:var(--t3);border:1.5px solid var(--border);background:var(--bg);
  cursor:pointer;transition:all .15s;
}
.tag-btn:hover{border-color:#0D0D0D;color:#0D0D0D}
.tag-btn.on{background:#0D0D0D;border-color:#0D0D0D;color:#fff}

/* worker result count */
.result-count{font-size:13px;color:var(--t4);margin-bottom:20px}
.result-count span{font-weight:700;color:var(--t2)}

/* 2-col card grid */
.wk-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:clamp(16px,2vw,24px);
}

/* individual card — mirrors the screenshot layout exactly */
.wk-card{
  background:#fff;
  border:1px solid var(--border);
  border-radius:20px;
  overflow:hidden;
  display:flex;
  position:relative;
  min-height:300px;
  transition:transform .2s,box-shadow .2s;
}
.wk-card:hover{transform:translateY(-3px);box-shadow:0 16px 44px rgba(0,0,0,.08)}
[data-theme="dark"] .wk-card:hover{box-shadow:0 16px 44px rgba(0,0,0,.35)}

/* floating character image — right half */
.wk-img-bg{
  position:absolute;right:0;top:0;bottom:0;
  width:48%;
  pointer-events:none;
}
.wk-img-bg img{width:100%;height:100%;object-fit:cover;object-position:center top}
/* gradient: white → transparent, stops at ~55% so face shows */
.wk-img-bg::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(to right,#fff 0%,rgba(255,255,255,.9) 18%,rgba(255,255,255,.35) 38%,transparent 56%);
}
[data-theme="dark"] .wk-img-bg::after{
  background:linear-gradient(to right,#111 0%,rgba(17,17,17,.88) 18%,rgba(17,17,17,.3) 38%,transparent 56%);
}

/* left content */
.wk-content{
  position:relative;z-index:1;
  padding:clamp(22px,2.5vw,30px);
  display:flex;flex-direction:column;
  width:60%;
}
.wk-head{display:flex;align-items:center;gap:10px;margin-bottom:6px}
.wk-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.wk-icon svg{width:19px;height:19px}
.wk-name{font-family:var(--font-h);font-size:1.35rem;font-weight:800;letter-spacing:-.04em;line-height:1}
.wk-role{font-size:11px;font-weight:600;color:var(--t3);letter-spacing:.05em;text-transform:uppercase;margin-bottom:14px}
.wk-quote{font-size:13.5px;font-weight:600;color:var(--t2);line-height:1.6;margin-bottom:16px;flex:1}
.wk-bullets{display:flex;flex-direction:column;gap:7px;margin-bottom:20px}
.wk-bullet{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:500;color:var(--t2)}
.wk-check{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.wk-check svg{width:10px;height:10px;stroke:#fff;stroke-width:2.5;fill:none}
.wk-btns{display:flex;align-items:center;gap:8px;flex-wrap:nowrap}
.btn-hire-wk{
  display:inline-flex;align-items:center;gap:6px;
  padding:9px 14px;border-radius:10px;
  font-size:12.5px;font-weight:700;color:#fff;
  white-space:nowrap;flex-shrink:0;
  transition:opacity .15s,transform .1s;
}
.btn-hire-wk:hover{opacity:.88;transform:translateY(-1px)}
.btn-watch-wk{
  display:inline-flex;align-items:center;gap:6px;
  padding:9px 14px;border-radius:10px;
  font-size:12.5px;font-weight:700;
  border:1.5px solid var(--border);color:var(--t2);
  white-space:nowrap;flex-shrink:0;
  transition:all .15s;
}
.btn-watch-wk:hover{border-color:currentColor}

/* empty state when search returns nothing */
.empty-state{
  grid-column:1/-1;
  text-align:center;
  padding:80px 20px;
  color:var(--t3);
}
.empty-state svg{width:40px;height:40px;margin:0 auto 16px;opacity:.4}
.empty-state h3{font-size:1.1rem;font-weight:700;margin-bottom:8px;color:var(--t2)}
.empty-state p{font-size:14px}

/* ── BEHIND EVERY WORKER ── */
.behind-bar{background:#fff;border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:clamp(44px,6vw,72px) 0}
.behind-intro{margin-bottom:clamp(32px,4vw,48px);text-align:center}
.behind-intro .sec-p{font-size:1rem;color:var(--t3);line-height:1.7;max-width:480px;margin:0 auto}
.behind-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid var(--border);border-radius:20px;overflow:hidden}
.behind-item{padding:clamp(22px,3vw,34px) clamp(18px,2.5vw,26px);display:flex;flex-direction:column;gap:14px;border-right:1px solid var(--border)}
.behind-item:last-child{border-right:none}
.behind-icon{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;background:var(--soft)}
.behind-icon svg{width:22px;height:22px}
.behind-h{font-size:15.5px;font-weight:700;color:var(--text);line-height:1.35}
.behind-p{font-size:13px;color:var(--t3);line-height:1.65}

/* ── CTA ── */
.cta-foot{background:var(--soft);padding:clamp(44px,6vw,72px) 0}
.cta-foot-inner{
  background:#0D0D0D;
  border-radius:24px;padding:clamp(36px,5vw,56px) clamp(32px,5vw,60px);
  display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap;
  position:relative;overflow:hidden;
}
.cta-foot-inner::before{content:'';position:absolute;right:-80px;top:-80px;width:320px;height:320px;border-radius:50%;background:rgba(255,255,255,.04);pointer-events:none}
.cta-foot-inner::after{content:'';position:absolute;right:100px;bottom:-60px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.03);pointer-events:none}
.cta-foot-left{position:relative;z-index:1}
.cta-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:10px}
.cta-foot-h{font-family:var(--font-h);font-size:clamp(1.5rem,2.8vw,2.2rem);font-weight:800;letter-spacing:-.03em;color:#fff;margin-bottom:6px}
.cta-foot-sub{font-size:14px;color:rgba(255,255,255,.6)}
.cta-foot-right{display:flex;flex-direction:column;align-items:center;gap:8px;position:relative;z-index:1}
.btn-cta-main{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;background:#fff;color:#0D0D0D;box-shadow:0 4px 20px rgba(0,0,0,.2);white-space:nowrap;transition:opacity .15s,transform .15s}
.btn-cta-main:hover{opacity:.95;transform:translateY(-2px)}
.cta-note{font-size:12px;color:rgba(255,255,255,.4)}

/* ── FOOTER ── */
.footer{background:#0A0A0A;padding:clamp(40px,6vw,72px) 0 28px}
.ft-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:44px;margin-bottom:44px}
.ft-name{font-family:var(--font-h);font-size:1.15rem;font-weight:800;color:#fff;margin-bottom:10px}
.ft-desc{font-size:13.5px;color:rgba(255,255,255,.6);line-height:1.7;max-width:220px;margin-bottom:20px}
.ft-col-h{font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:14px}
.ft-links{display:flex;flex-direction:column;gap:9px}
.ft-links a{font-size:13.5px;color:rgba(255,255,255,.7);transition:color .15s}
.ft-links a:hover{color:#fff}
.ft-bottom{border-top:1px solid rgba(255,255,255,.12);padding-top:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
.ft-bottom p{font-size:12.5px;color:rgba(255,255,255,.45)}

/* ── MOBILE MENU ── */
.mob-menu{display:none;position:fixed;inset:0;z-index:200;background:#fff;flex-direction:column;padding:24px var(--pad)}
[data-theme="dark"] .mob-menu{background:#0D0D0D}
.mob-menu.open{display:flex}
.mob-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:36px}
.mob-close{font-size:22px;color:var(--t3);padding:4px}
.mob-links{display:flex;flex-direction:column}
.mob-links a{display:block;padding:14px 0;font-size:1.05rem;font-weight:600;color:var(--t2);border-bottom:1px solid var(--border)}
.mob-ctas{margin-top:28px;display:flex;flex-direction:column;gap:10px}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .wk-grid{grid-template-columns:1fr}
  .wk-img-bg{width:44%}
  .behind-grid{grid-template-columns:repeat(2,1fr)}
  .behind-item:nth-child(2){border-right:none}
  .behind-item:nth-child(3){border-right:1px solid var(--border)}
  .behind-item:nth-child(1),.behind-item:nth-child(2){border-bottom:1px solid var(--border)}
  .ft-grid{grid-template-columns:1fr 1fr;gap:28px}
}
@media(max-width:768px){
  .nav-links,.nav-acts{display:none}
  .ham{display:flex}
  .hero-page{grid-template-columns:1fr;min-height:auto}
  .hero-page-right{order:-1;min-height:260px}
  .hero-page-spacer{min-height:260px}
  .hero-page-left{padding:36px var(--pad);text-align:center}
  .hero-proof{justify-content:center}
  .toolbar-top{flex-direction:column;align-items:flex-start}
  .search-wrap input{width:100%}
  .search-wrap{width:100%}
  .behind-grid{grid-template-columns:1fr 1fr}
  .cta-foot-inner{flex-direction:column;text-align:center;align-items:center}
  .ft-grid{grid-template-columns:1fr}
  .ft-bottom{flex-direction:column;text-align:center}
}
@media(max-width:480px){
  /* stack card: image on top, content below */
  .wk-card{flex-direction:column;min-height:auto}
  .wk-img-bg{
    position:relative;width:100%;height:220px;
    flex-shrink:0;
  }
  .wk-img-bg img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center top}
  /* fade bottom of image into card background */
  .wk-img-bg::after{
    background:linear-gradient(to bottom,transparent 40%,#fff 100%);
  }
  [data-theme="dark"] .wk-img-bg::after{
    background:linear-gradient(to bottom,transparent 40%,#111 100%);
  }
  .wk-content{width:100%;padding-top:8px}
  /* reset AVA's scale transform on mobile */
  .wk-img-bg img{transform:none!important;transform-origin:unset!important;object-position:center top!important}
  .behind-grid{grid-template-columns:1fr}
  .behind-item{border-right:none!important;border-bottom:1px solid var(--border)}
  .behind-item:last-child{border-bottom:none}
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

<!-- NAV -->
<nav class="nav">
  <div class="w nav-i">
    <a href="{{ url("/") }}" class="logo"><span class="logo-name">UNIT</span></a>
    <ul class="nav-links">
      <li><a href="{{ route('public.workers.index') }}" class="active">Meet the Workers</a></li>
      <li><a href="{{ url('/') }}#timeline">How It Works</a></li>
      <li><a href="{{ route('marketplace') }}">For Business</a></li>
      <li><a href="{{ url('/') }}#resources">Resources</a></li>
      <li><a href="{{ route('pricing') }}">Pricing</a></li>
    </ul>
    <div class="nav-acts">
      @auth
        <a href="{{ route('app.dashboard') }}" class="btn-login" style="border-radius:99px">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="btn-login" style="border-radius:99px">Log in</a>
      @endauth
      @if($__navAvaHasDesk)
      <a href="{{ route('app.desk.ava') }}" class="btn-cta">Go to AVA's Desk <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      @else
      <a href="{{ route('hire.ava.welcome') }}" class="btn-cta">Hire Your First Worker <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      @endif
      <button class="theme-toggle" id="theme-toggle" aria-label="Toggle theme">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
    </div>
    <button class="ham" id="ham" aria-label="Menu"><span></span><span></span><span></span></button>
  </div>
</nav>

<!-- MOBILE MENU -->
<div class="mob-menu" id="mob">
  <div class="mob-top">
    <a href="{{ url("/") }}" class="logo"><span class="logo-name">UNIT</span></a>
    <button class="mob-close" id="mob-close">✕</button>
  </div>
  <div class="mob-links">
    <a href="{{ route('public.workers.index') }}" onclick="closeMob()">Meet the Workers</a>
    <a href="{{ url('/') }}#timeline" onclick="closeMob()">How It Works</a>
    <a href="{{ route('marketplace') }}" onclick="closeMob()">For Business</a>
    <a href="{{ url('/') }}#resources" onclick="closeMob()">Resources</a>
    <a href="{{ route('pricing') }}" onclick="closeMob()">Pricing</a>
  </div>
  <div class="mob-ctas">
    @auth
      <a href="{{ route('app.dashboard') }}" class="btn-login" style="text-align:center;padding:12px;border-radius:99px">Dashboard</a>
    @else
      <a href="{{ route('login') }}" class="btn-login" style="text-align:center;padding:12px;border-radius:99px">Log in</a>
    @endauth
    @if($__navAvaHasDesk)
    <a href="{{ route('app.desk.ava') }}" class="btn-cta" style="padding:12px;justify-content:center">Go to AVA's Desk →</a>
    @else
    <a href="{{ route('hire.ava.welcome') }}" class="btn-cta" style="padding:12px;justify-content:center">Hire Your First Worker →</a>
    @endif
  </div>
</div>

<!-- HERO -->
<section class="hero-page">
  <div class="hero-page-left">
    <div class="hero-page-inner">
      <div class="page-eye">Meet the Team</div>
      <h1 class="hero-page-h">Four workers.<br>Four specialties.<br><em>One goal: Your success.</em></h1>
      <p class="hero-page-p">Each UNIT worker has one job — and does it exceptionally well. They work 24/7, improve over time, and show up every day ready to help your business grow.</p>
      <div class="hero-proof">
        <div class="proof-avs">
          <img src="/images/ava.png" alt="AVA">
          <img src="/images/dox.png" alt="DOX">
          <img src="/images/mox.png" alt="MOX">
          <img src="/images/nux.png" alt="NUX">
        </div>
        <p class="proof-txt"><strong>United by purpose.</strong>Built for results.</p>
      </div>
    </div>
  </div>
  <div class="hero-page-right">
    <span class="hero-page-spacer" aria-hidden="true"></span>
    <img src="/images/hero-team-2.png" alt="AVA, DOX, MOX and NUX — the UNIT AI workforce">
    <div class="hero-fade-page"></div>
    <div class="hero-badge">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M12 2l1.5 4.5H18l-3.75 2.75 1.5 4.5L12 11l-3.75 2.75 1.5-4.5L6 6.5h4.5L12 2z" fill="#F59E0B"/></svg>
      <div class="badge-txt">Real stories. Real work.<br>Real results.</div>
    </div>
  </div>
</section>

<!-- ACTIVITY FEED -->
<div class="activity-feed">
  <div class="feed-track">
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Renewal drafted for Apex Property Group</span><span class="feed-time">3s ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#fff">DOX</span><span class="feed-action">1,247 lease files sorted and tagged</span><span class="feed-time">14m ago</span></div>
    <div class="feed-item"><span class="feed-dot amber"></span><span class="feed-worker" style="color:#F59E0B">MOX</span><span class="feed-action">Brand mention found on LinkedIn — flagged for review</span><span class="feed-time">1m ago</span></div>
    <div class="feed-item"><span class="feed-dot blue"></span><span class="feed-worker" style="color:#0D0D0D">NUX</span><span class="feed-action">Campaign published across 3 channels</span><span class="feed-time">22m ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Follow-up sent to Sunrise LLC · renewal confirmed</span><span class="feed-time">8m ago</span></div>
    <div class="feed-item"><span class="feed-dot blue"></span><span class="feed-worker" style="color:#fff">DOX</span><span class="feed-action">Contract uploaded · client folder updated automatically</span><span class="feed-time">31m ago</span></div>
    <div class="feed-item"><span class="feed-dot amber"></span><span class="feed-worker" style="color:#F59E0B">MOX</span><span class="feed-action">National Coffee Day opportunity surfaced</span><span class="feed-time">2h ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">3 renewals processed before 9 AM · zero missed</span><span class="feed-time">today</span></div>
    <div class="feed-item"><span class="feed-dot blue"></span><span class="feed-worker" style="color:#0D0D0D">NUX</span><span class="feed-action">6 posts repurposed from last week's report</span><span class="feed-time">45m ago</span></div>
    <div class="feed-item"><span class="feed-dot blue"></span><span class="feed-worker" style="color:#fff">DOX</span><span class="feed-action">Duplicate files removed · 340 MB recovered</span><span class="feed-time">1h ago</span></div>
    <!-- clone set -->
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Renewal drafted for Apex Property Group</span><span class="feed-time">3s ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#fff">DOX</span><span class="feed-action">1,247 lease files sorted and tagged</span><span class="feed-time">14m ago</span></div>
    <div class="feed-item"><span class="feed-dot amber"></span><span class="feed-worker" style="color:#F59E0B">MOX</span><span class="feed-action">Brand mention found on LinkedIn — flagged for review</span><span class="feed-time">1m ago</span></div>
    <div class="feed-item"><span class="feed-dot blue"></span><span class="feed-worker" style="color:#0D0D0D">NUX</span><span class="feed-action">Campaign published across 3 channels</span><span class="feed-time">22m ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Follow-up sent to Sunrise LLC · renewal confirmed</span><span class="feed-time">8m ago</span></div>
    <div class="feed-item"><span class="feed-dot blue"></span><span class="feed-worker" style="color:#fff">DOX</span><span class="feed-action">Contract uploaded · client folder updated automatically</span><span class="feed-time">31m ago</span></div>
    <div class="feed-item"><span class="feed-dot amber"></span><span class="feed-worker" style="color:#F59E0B">MOX</span><span class="feed-action">National Coffee Day opportunity surfaced</span><span class="feed-time">2h ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">3 renewals processed before 9 AM · zero missed</span><span class="feed-time">today</span></div>
    <div class="feed-item"><span class="feed-dot blue"></span><span class="feed-worker" style="color:#0D0D0D">NUX</span><span class="feed-action">6 posts repurposed from last week's report</span><span class="feed-time">45m ago</span></div>
    <div class="feed-item"><span class="feed-dot blue"></span><span class="feed-worker" style="color:#fff">DOX</span><span class="feed-action">Duplicate files removed · 340 MB recovered</span><span class="feed-time">1h ago</span></div>
  </div>
</div>

<!-- WORKERS -->
<section class="workers-sec" id="workers">
  <div class="w">

    <!-- TOOLBAR -->
    <div class="toolbar">
      <div class="toolbar-top">
        <div class="toolbar-top-left">
          <div class="sec-eye">The Roster</div>
          <h2 class="sec-h">Your AI workforce, ready to hire.</h2>
        </div>
        <div class="search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" id="wk-search" placeholder="Search workers…" autocomplete="off">
        </div>
      </div>
      <div class="tags">
        <span class="tags-label">Specialty:</span>
        <button class="tag-btn on" data-tag="all">All</button>
        <button class="tag-btn" data-tag="renewals">Renewals</button>
        <button class="tag-btn" data-tag="documents">Documents</button>
        <button class="tag-btn" data-tag="brand">Brand</button>
        <button class="tag-btn" data-tag="content">Content</button>
      </div>
    </div>

    <div class="result-count" id="result-count"><span id="count-num">4</span> workers</div>

    <!-- WORKER CARDS -->
    <div class="wk-grid" id="wk-grid">

      <!-- AVA -->
      <div class="wk-card" data-name="ava" data-tags="renewals" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/ava-stand.png" alt="AVA" style="object-position:center 10%;transform:scale(1.45);transform-origin:top center">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(245,197,24,.12)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="wk-name" style="color:#0D0D0D">AVA</div>
          </div>
          <div class="wk-role">Renewals Specialist</div>
          <p class="wk-quote">"I remember the renewals everyone else forgets."</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Tracks every renewal</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Sends reminders</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Reduces churn</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Protects revenue</div>
          </div>
          @php
            $avaHasDesk = auth()->check() && DB::table('worker_deployments')
              ->where('user_id', auth()->id())->where('worker_slug', 'ava')
              ->whereIn('status', ['active', 'paused'])->exists();
          @endphp
          <div class="wk-btns">
            @if($avaHasDesk)
              <a href="{{ route('app.desk.ava') }}" class="btn-hire-wk" style="background:#0D0D0D">AVA's Desk <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            @else
              <a href="{{ route('hire.ava.welcome') }}" class="btn-hire-wk" style="background:#0D0D0D">Hire AVA <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            @endif
            <a href="{{ route('public.workers.show', 'ava') }}" class="btn-watch-wk" style="color:#0D0D0D;border-color:#E5E7EB">Watch Ava's Day <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
          </div>
        </div>
      </div>

      <!-- DOX -->
      <div class="wk-card" data-name="dox" data-tags="documents" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/dox.png" alt="DOX" style="object-position:center top">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            </div>
            <div class="wk-name" style="color:#0D0D0D">DOX</div>
          </div>
          <div class="wk-role">Document Specialist</div>
          <p class="wk-quote">"I organize the documents nobody wants to touch."</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Organizes files</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Finds what's lost</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Structures systems</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates order</div>
          </div>
          <div class="wk-btns">
            <a href="{{ route('hire.ava.welcome') }}" class="btn-hire-wk" style="background:#0D0D0D">Hire DOX <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            <a href="#" class="btn-watch-wk" style="color:#0D0D0D;border-color:#E5E7EB">Watch Dox's Day <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
          </div>
        </div>
      </div>

      <!-- MOX -->
      <div class="wk-card" data-name="mox" data-tags="brand" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/mox.png" alt="MOX" style="object-position:center top">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </div>
            <div class="wk-name" style="color:#0D0D0D">MOX</div>
          </div>
          <div class="wk-role">Brand Moments Hunter</div>
          <p class="wk-quote">"I search the world for moments your brand shouldn't miss."</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Finds brand moments</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Tracks opportunities</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates campaigns</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Delivers impact</div>
          </div>
          <div class="wk-btns">
            <a href="{{ route('hire.ava.welcome') }}" class="btn-hire-wk" style="background:#0D0D0D">Hire MOX <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            <a href="#" class="btn-watch-wk" style="color:#0D0D0D;border-color:#E5E7EB">Watch Mox's Day <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
          </div>
        </div>
      </div>

      <!-- NUX -->
      <div class="wk-card" data-name="nux" data-tags="content" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/nux.png" alt="NUX" style="object-position:center top">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <div class="wk-name" style="color:#0D0D0D">NUX</div>
          </div>
          <div class="wk-role">Publishing Specialist</div>
          <p class="wk-quote">"I turn one idea into content people actually see."</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates content</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Repurposes ideas</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Publishes daily</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Grows your reach</div>
          </div>
          <div class="wk-btns">
            <a href="{{ route('hire.ava.welcome') }}" class="btn-hire-wk" style="background:#0D0D0D">Hire NUX <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            <a href="#" class="btn-watch-wk" style="color:#0D0D0D;border-color:#E5E7EB">Watch Nux's Day <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
          </div>
        </div>
      </div>

    </div><!-- /wk-grid -->
  </div>
</section>

<!-- BEHIND EVERY WORKER -->
<div class="behind-bar">
  <div class="w">
    <div class="behind-intro">
      <div class="sec-eye">Behind every worker</div>
      <h2 class="sec-h">Real lives. Real work. Real results.</h2>
      <p class="sec-p">Every worker operates with the same commitment — learning, improving, and reporting back after every single task.</p>
    </div>
    <div class="behind-grid">
      <div class="behind-item">
        <div class="behind-icon"><svg viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg></div>
        <div>
          <div class="behind-h">They keep a diary.</div>
          <p class="behind-p">Every worker writes about their day — what they did, what they learned, and what's next. You always know what happened.</p>
        </div>
      </div>
      <div class="behind-item">
        <div class="behind-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
        <div>
          <div class="behind-h">They get better.</div>
          <p class="behind-p">They improve with every task, every challenge, and every win. No retraining required — they just keep getting sharper.</p>
        </div>
      </div>
      <div class="behind-item">
        <div class="behind-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></div>
        <div>
          <div class="behind-h">They care.</div>
          <p class="behind-p">They take pride in their work because your success is their mission. Every task matters. Every result counts.</p>
        </div>
      </div>
      <div class="behind-item">
        <div class="behind-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div>
        <div>
          <div class="behind-h">They've never met.</div>
          <p class="behind-p">Each worker believes they're alone at UNIT — for now. As you hire more, they'll learn to collaborate. Stay tuned.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CTA -->
<section class="cta-foot">
  <div class="w">
    <div class="cta-foot-inner">
      <div class="cta-foot-left">
        <div class="cta-eyebrow">Ready to grow?</div>
        <h2 class="cta-foot-h">Hire your first worker today.</h2>
        <p class="cta-foot-sub">Start with one. Add more as you grow.</p>
      </div>
      <div class="cta-foot-right">
        @if($__navAvaHasDesk)
        <a href="{{ route('app.desk.ava') }}" class="btn-cta-main">
          Go to AVA's Desk
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        @else
        <a href="{{ route('hire.ava.welcome') }}" class="btn-cta-main">
          Hire Your First Worker
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <span class="cta-note">No credit card required.</span>
        @endif
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
        <p class="ft-desc">AI workers that show up every day, handle the work that slows you down, and help your business grow.</p>
      </div>
      <div>
        <div class="ft-col-h">Workers</div>
        <div class="ft-links">
          <a href="{{ route('public.workers.show', 'ava') }}">AVA — Renewals</a>
          <a href="#">DOX — Documents</a>
          <a href="#">MOX — Brand</a>
          <a href="#">NUX — Content</a>
        </div>
      </div>
      <div>
        <div class="ft-col-h">Platform</div>
        <div class="ft-links">
          <a href="{{ route('pricing') }}">Pricing</a>
          <a href="{{ route('marketplace') }}">Marketplace</a>
          <a href="{{ route('hire.ava.welcome') }}">Get Started</a>
          <a href="{{ route('login') }}">Log In</a>
        </div>
      </div>
      <div>
        <div class="ft-col-h">Company</div>
        <div class="ft-links">
          <a href="#">About</a>
          <a href="#">Blog</a>
          <a href="#">Contact</a>
          <a href="#">Privacy</a>
        </div>
      </div>
    </div>
    <div class="ft-bottom">
      <p>© {{ date('Y') }} UNIT. All rights reserved.</p>
      <p>Built with purpose. Powered by AI.</p>
    </div>
  </div>
</footer>

<script>
// ── Theme ──
const root = document.documentElement;
const saved = localStorage.getItem('unit-theme');
if(saved) root.setAttribute('data-theme', saved);
document.getElementById('theme-toggle').addEventListener('click', function(){
  const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  root.setAttribute('data-theme', next);
  localStorage.setItem('unit-theme', next);
});

// ── Mobile menu ──
const ham = document.getElementById('ham');
const mob = document.getElementById('mob');
ham.addEventListener('click', () => mob.classList.add('open'));
document.getElementById('mob-close').addEventListener('click', () => mob.classList.remove('open'));
function closeMob(){ mob.classList.remove('open'); }

// ── Search + tag filter ──
const searchEl = document.getElementById('wk-search');
const tagBtns  = document.querySelectorAll('.tag-btn');
const cards    = document.querySelectorAll('.wk-card');
const countEl  = document.getElementById('count-num');
const grid     = document.getElementById('wk-grid');

let activeTag = 'all';

function applyFilter(){
  const q = searchEl.value.trim().toLowerCase();
  let visible = 0;

  cards.forEach(card => {
    const name  = card.dataset.name.toLowerCase();
    const tags  = card.dataset.tags.toLowerCase();
    const text  = card.textContent.toLowerCase();
    const tagOk = activeTag === 'all' || tags.includes(activeTag);
    const qOk   = !q || name.includes(q) || text.includes(q) || tags.includes(q);

    if(tagOk && qOk){
      card.style.display = '';
      visible++;
    } else {
      card.style.display = 'none';
    }
  });

  countEl.textContent = visible;

  // empty state
  let empty = document.getElementById('empty-state');
  if(visible === 0){
    if(!empty){
      empty = document.createElement('div');
      empty.id = 'empty-state';
      empty.className = 'empty-state';
      empty.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <h3>No workers found</h3>
        <p>Try a different search term or specialty filter.</p>`;
      grid.appendChild(empty);
    }
  } else if(empty){
    empty.remove();
  }
}

searchEl.addEventListener('input', applyFilter);

tagBtns.forEach(btn => {
  btn.addEventListener('click', function(){
    tagBtns.forEach(b => b.classList.remove('on'));
    this.classList.add('on');
    activeTag = this.dataset.tag;
    applyFilter();
  });
});
</script>

<x-self-learn />

</body>
</html>

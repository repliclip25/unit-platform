<!DOCTYPE html>
<html lang="en" id="html-root" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $worker['name'] }} — {{ $worker['role'] }} | UNIT</title>
<meta name="description" content="{{ $worker['meta_desc'] }}">
<link rel="icon" type="image/png" href="/logo.png">
<script>(function(){var t=localStorage.getItem('unit-theme-v2')||'light';document.getElementById('html-root').setAttribute('data-theme',t)})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --gold:#142C74;--gold-d:#0e2260;--glow:rgba(20,44,116,0.22);--gold-text:#ffffff;
  --accent:var(--gold);--accent-rgb:20,44,116;--accent-dark:var(--gold-d);
  --green:#22c55e;--green-bg:rgba(34,197,94,0.1);--green-border:rgba(34,197,94,0.25);
  --text:#ffffff;--t2:#cccccc;--t3:#999999;--t4:#555555;
  --line:rgba(255,255,255,0.12);--line2:rgba(255,255,255,0.18);
  --surf:#1a1a1a;--raised:#212121;
  --card:#212121;--cb:rgba(255,255,255,0.12);
  --fd:'Space Grotesk','Inter',sans-serif;--fb:'Inter',sans-serif;
  --bg:#000000;
}
[data-theme="light"]{
  --gold:#142C74;--gold-d:#0e2260;--glow:rgba(20,44,116,0.18);--gold-text:#ffffff;
  --green:#16a34a;--green-bg:rgba(22,163,74,0.08);--green-border:rgba(22,163,74,0.2);
  --text:#000000;--t2:#1a1a1a;--t3:#555555;--t4:#999999;
  --line:#e2e2e0;--line2:#cccccc;
  --surf:#f0f0ee;--raised:#e2e2e0;
  --card:#ffffff;--cb:#e2e2e0;
  --bg:#f9f9f7;
  --accent:var(--gold);--accent-rgb:20,44,116;--accent-dark:var(--gold-d);
}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:var(--fb);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
.w{max-width:1160px;margin:0 auto;padding:0 48px}

/* ── NAV ── */
header{position:sticky;top:0;left:0;right:0;z-index:300;background:rgba(8,8,16,0.94);backdrop-filter:blur(20px);border-bottom:1px solid var(--line)}
.ni{display:flex;align-items:center;justify-content:space-between;padding:18px 48px;max-width:1160px;margin:0 auto}
.brand{display:flex;align-items:center;gap:10px;font-family:var(--fd);font-weight:800;font-size:18px}
.brand img{width:28px;height:28px;border-radius:7px}
.nav-r{display:flex;align-items:center;gap:14px}
.btn-g{display:inline-flex;align-items:center;gap:7px;background:var(--gold);color:#ffffff;font-weight:700;font-size:13.5px;padding:9px 20px;border-radius:8px;border:none;cursor:pointer;transition:transform .15s,box-shadow .2s;white-space:nowrap;font-family:var(--fb)}
.btn-g:hover{transform:translateY(-1px);box-shadow:0 6px 24px var(--glow)}
.btn-gh{font-size:13.5px;color:var(--t3);cursor:pointer;border:none;background:none;font-family:var(--fb);transition:color .15s}
.btn-gh:hover{color:var(--text)}
.tog{width:34px;height:19px;border-radius:10px;border:none;cursor:pointer;position:relative;transition:background .2s;flex-shrink:0}
.tog::after{content:'';position:absolute;top:2.5px;left:2.5px;width:14px;height:14px;border-radius:50%;background:#fff;transition:transform .2s}
[data-theme="dark"] .tog{background:var(--gold)}
[data-theme="light"] .tog{background:#94a3b8}
[data-theme="dark"] .tog::after{transform:translateX(15px)}

/* ── HERO ── */
.worker-hero{padding:80px 0 64px;position:relative;overflow:hidden}
.worker-hero::before{content:'';position:absolute;top:-200px;right:-200px;width:700px;height:700px;border-radius:50%;background:radial-gradient(circle,rgba(243,197,49,0.045) 0%,transparent 65%);pointer-events:none}
.hero-inner{display:grid;grid-template-columns:1fr 440px;gap:72px;align-items:center}
.hero-badge{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(243,197,49,0.28);background:rgba(243,197,49,0.06);color:var(--gold);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:6px 14px;border-radius:100px;margin-bottom:22px}
.hero-pulse{width:6px;height:6px;border-radius:50%;background:var(--gold);animation:blink 2s ease infinite;flex-shrink:0}
.hero-av-row{display:flex;align-items:center;gap:18px;margin-bottom:20px}
.hero-av{width:72px;height:72px;border-radius:18px;background:background:#142C74;display:flex;align-items:center;justify-content:center;font-family:var(--fd);font-weight:800;font-size:28px;color:#ffffff;flex-shrink:0;position:relative}
.hero-live{position:absolute;bottom:-4px;right:-4px;width:16px;height:16px;border-radius:50%;background:var(--green);border:3px solid var(--bg)}
.hero-id .name{font-family:var(--fd);font-size:15px;font-weight:800;color:var(--text)}
.hero-id .role{font-size:12px;color:var(--t3);margin-top:2px}
h1.wh{font-family:var(--fd);font-size:52px;line-height:1.02;font-weight:800;letter-spacing:-2px;margin-bottom:18px;max-width:620px}
h1.wh em{font-style:normal;color:var(--gold)}
.hero-sub{font-size:16px;line-height:1.7;color:var(--t2);max-width:540px;margin-bottom:28px}
.hero-ctas{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}
.btn-ln{display:inline-flex;align-items:center;gap:7px;font-size:13.5px;font-weight:600;padding:9px 20px;border-radius:8px;border:1px solid var(--line2);color:var(--t2);cursor:pointer;background:none;transition:border-color .15s,color .15s;font-family:var(--fb)}
.btn-ln:hover{border-color:var(--gold);color:var(--gold)}
.hero-orgs{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.hero-org-label{font-size:12px;color:var(--t4)}
.org-chip{padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;background:rgba(243,197,49,0.07);border:1px solid rgba(243,197,49,0.14);color:var(--gold)}

/* ── STATS CARD (hero right) ── */
.hero-stats-card{background:var(--card);border:1px solid var(--cb);border-radius:20px;padding:28px;backdrop-filter:blur(14px)}
.hsc-title{font-size:10.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--t4);margin-bottom:20px}
.hsc-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
.hsc-stat{background:var(--surf);border:1px solid var(--line);border-radius:12px;padding:16px}
.hsc-n{font-family:var(--fd);font-size:28px;font-weight:800;color:var(--gold);letter-spacing:-1px;line-height:1}
.hsc-l{font-size:11px;color:var(--t3);margin-top:4px}
.hsc-stat.full{grid-column:1/-1;display:flex;align-items:center;justify-content:space-between}
.hsc-tag{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:100px;background:var(--green-bg);border:1px solid var(--green-border);color:var(--green)}
.hsc-tagdot{width:5px;height:5px;border-radius:50%;background:var(--green);animation:blink 1.4s ease infinite}

/* ── PIPELINE SECTION ── */
.pipeline-sec{padding:80px 0;border-top:1px solid var(--line)}
.sec-head{text-align:center;max-width:600px;margin:0 auto 52px}
.slabel{font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--gold);margin-bottom:12px}
.sh2{font-family:var(--fd);font-size:38px;font-weight:800;letter-spacing:-1.5px;line-height:1.05;margin-bottom:12px}
.ssub{font-size:15px;line-height:1.65;color:var(--t2)}

/* Full pipeline display */
.pipeline-display{background:var(--card);border:1px solid var(--cb);border-radius:20px;overflow:hidden;backdrop-filter:blur(14px)}
.pd-header{display:flex;align-items:center;gap:8px;padding:16px 24px;border-bottom:1px solid var(--line)}
.pd-dot{width:10px;height:10px;border-radius:50%}
.pd-title{font-family:monospace;font-size:11px;color:var(--t3);margin-left:auto;margin-right:0}
.pd-worker-strip{display:flex;align-items:center;gap:14px;padding:18px 24px;border-bottom:1px solid var(--line);background:rgba(255,255,255,0.02)}
.pd-av{width:42px;height:42px;border-radius:11px;background:background:#142C74;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:17px;color:#ffffff;flex-shrink:0}
.pd-wname{font-size:13.5px;font-weight:700;color:var(--text)}
.pd-wstat{font-size:11px;color:var(--t3);display:flex;align-items:center;gap:5px;margin-top:2px}
.pd-stpulse{width:5px;height:5px;border-radius:50%;background:var(--green);animation:blink 1.4s ease infinite}
.pd-badge{margin-left:auto;display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 11px;border-radius:100px;background:var(--green-bg);border:1px solid var(--green-border);color:var(--green)}

/* Node grid — full pipeline */
.pd-nodes{display:grid;gap:6px;padding:24px;align-items:start}
.pd-node{display:flex;flex-direction:column;align-items:center;gap:5px;position:relative}
.pd-node:not(:last-child)::after{content:'';position:absolute;right:-7px;top:18px;width:8px;height:1px;background:var(--t4)}
.pdn-ic{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;border:1px solid var(--cb);background:var(--raised);transition:all .4s}
.pdn-ic svg{width:18px;height:18px;color:var(--t3);transition:color .3s}
.pdn-lb{font-size:9.5px;color:var(--t3);text-align:center;line-height:1.3;transition:color .3s;max-width:60px}
.pdn-t{font-size:9px;font-family:monospace;color:transparent;height:10px;transition:color .3s}
.pd-node.done .pdn-ic{background:var(--green-bg);border-color:var(--green-border)}
.pd-node.done .pdn-ic svg{color:var(--green)}
.pd-node.done .pdn-lb,.pd-node.done .pdn-t{color:var(--green)}
.pd-node.active .pdn-ic{background:rgba(243,197,49,0.12);border-color:rgba(243,197,49,0.4);animation:nodeglow 1.1s ease infinite}
.pd-node.active .pdn-ic svg,.pd-node.active .pdn-lb{color:var(--gold)}
.pd-node.active .pdn-t{color:rgba(243,197,49,0.5)}
.pd-out-bar{padding:12px 24px 20px;border-top:1px solid var(--line);opacity:0;transition:opacity .4s}
.pd-out-bar.show{opacity:1}
.pd-out-text{font-size:11.5px;font-family:monospace;color:var(--t2);line-height:1.6}

/* Step detail list below pipeline */
.pipeline-steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-top:24px}
.ps-item{background:var(--surf);border:1px solid var(--line);border-radius:14px;padding:20px}
.ps-num{font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--t4);margin-bottom:10px}
.ps-icon{width:36px;height:36px;border-radius:9px;background:rgba(243,197,49,0.08);border:1px solid rgba(243,197,49,0.14);display:flex;align-items:center;justify-content:center;margin-bottom:10px}
.ps-icon svg{width:17px;height:17px;color:var(--gold)}
.ps-title{font-size:14px;font-weight:700;color:var(--text);margin-bottom:6px}
.ps-desc{font-size:12.5px;line-height:1.6;color:var(--t3)}
.ps-detail{margin-top:10px;padding:10px 12px;border-radius:8px;background:rgba(0,0,0,0.25);border:1px solid var(--line);font-size:11px;color:var(--t3);font-family:monospace;line-height:1.6}

/* ── WHAT IT DOES ── */
.what-sec{padding:80px 0;border-top:1px solid var(--line);background:rgba(4,4,10,0.5)}
.what-grid{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:start}
.cap-list{display:flex;flex-direction:column;gap:10px;margin-top:16px}
.cap{display:flex;align-items:flex-start;gap:10px;font-size:14px;color:var(--t2)}
.cap svg{color:var(--gold);flex-shrink:0;margin-top:1px}
/* live task queue */
.live-queue{background:var(--card);border:1px solid var(--cb);border-radius:16px;overflow:hidden;backdrop-filter:blur(12px)}
.lq-head{display:flex;align-items:center;gap:8px;padding:14px 18px;border-bottom:1px solid var(--line);font-size:10.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--t4)}
.lq-pulse{width:6px;height:6px;border-radius:50%;background:var(--green);animation:blink 1.4s ease infinite}
.task-item{display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid var(--line);font-size:12.5px}
.task-item:last-child{border-bottom:none}
.task-item.t-done{background:rgba(34,197,94,0.04)}
.task-item.t-run{background:rgba(243,197,49,0.04)}
.task-icon{width:26px;height:26px;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.task-icon.ic-done{background:var(--green-bg)}
.task-icon.ic-run{background:rgba(243,197,49,0.1)}
.task-icon.ic-q{background:var(--surf)}
.task-icon svg{width:12px;height:12px}
.task-icon.ic-done svg{color:var(--green)}
.task-icon.ic-run svg{color:var(--gold)}
.task-icon.ic-q svg{color:var(--t4)}
.task-lbl{flex:1;color:var(--t2)}
.task-badge{font-size:10px;font-weight:700;padding:2px 9px;border-radius:100px}
.tb-done{background:var(--green-bg);color:var(--green)}
.tb-run{background:rgba(243,197,49,0.1);color:var(--gold);animation:blink 1.5s ease infinite}
.tb-q{background:var(--surf);color:var(--t4)}

/* ── FAST TRACK INLINE ── */
.ft-sec{padding:80px 0;border-top:1px solid var(--line)}
.ft-grid{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:start}
.ft-copy h2{font-family:var(--fd);font-size:34px;font-weight:800;letter-spacing:-1px;margin-bottom:14px;line-height:1.1}
.ft-copy p{font-size:15px;line-height:1.65;color:var(--t2);margin-bottom:20px}
.ft-pts{display:flex;flex-direction:column;gap:10px}
.ft-pt{display:flex;align-items:center;gap:10px;font-size:13.5px;color:var(--t2)}
.ft-pt svg{color:var(--gold);flex-shrink:0}
.ft-card{background:var(--card);border:1px solid var(--cb);border-radius:18px;padding:28px;backdrop-filter:blur(14px)}
.ft-card h3{font-family:var(--fd);font-size:18px;font-weight:800;color:var(--text);margin-bottom:5px}
.ft-card p{font-size:13px;color:var(--t3);margin-bottom:20px;line-height:1.5}
.ft-fields{display:flex;flex-direction:column;gap:10px;margin-bottom:12px}
.ft-in{padding:12px 15px;border-radius:10px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);color:var(--text);font-size:14px;outline:none;transition:border-color .2s;font-family:var(--fb);width:100%}
.ft-in:focus{border-color:rgba(243,197,49,0.5)}
.ft-in::placeholder{color:var(--t4)}
.ft-run-btn{width:100%;padding:13px;border-radius:10px;background:var(--gold);color:#ffffff;font-weight:700;font-size:14.5px;border:none;cursor:pointer;font-family:var(--fb);transition:opacity .15s}
.ft-run-btn:hover{opacity:.9}
.ft-run-btn:disabled{opacity:.5;cursor:default}
/* ft sequence */
.ft-seq{display:flex;align-items:center;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:16px;margin-bottom:12px}
.fts{flex:1;display:flex;flex-direction:column;align-items:center;gap:5px;position:relative}
.fts:not(:last-child)::after{content:'';position:absolute;right:-4px;top:16px;width:6px;height:1px;background:var(--t4)}
.fts-ic{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.07);background:rgba(255,255,255,0.03);transition:all .3s}
.fts-ic svg{width:15px;height:15px;color:var(--t3);transition:color .3s}
.fts-lb{font-size:9.5px;color:var(--t3);text-align:center;line-height:1.3;transition:color .3s}
.fts.s-done .fts-ic{background:var(--green-bg);border-color:var(--green-border)}
.fts.s-done .fts-ic svg{color:var(--green)}
.fts.s-done .fts-lb{color:var(--green)}
.fts.s-active .fts-ic{background:rgba(243,197,49,0.1);border-color:rgba(243,197,49,0.4);animation:nodeglow 1s ease infinite}
.fts.s-active .fts-ic svg{color:var(--gold)}
.fts.s-active .fts-lb{color:var(--gold)}
.ft-note{font-size:11px;color:var(--t4);text-align:center}
.ft-result{display:none;margin-top:14px;padding:16px;border-radius:12px;background:rgba(34,197,94,0.06);border:1px solid rgba(34,197,94,0.18)}
.ft-result.show{display:block}
.ft-result-lbl{font-size:12px;font-weight:700;color:var(--green);margin-bottom:6px}
.ft-result-body{font-size:12.5px;color:var(--t2);line-height:1.6;font-family:monospace;margin-bottom:14px}

/* ── TESTIMONIALS ── */
.testi-sec{padding:80px 0;border-top:1px solid var(--line);background:rgba(4,4,10,0.5)}
.testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.testi-card{background:var(--card);border:1px solid var(--cb);border-radius:16px;padding:26px;display:flex;flex-direction:column;gap:14px;backdrop-filter:blur(12px)}
.testi-stars{color:var(--gold);font-size:13px;letter-spacing:2px}
.testi-q{font-size:13.5px;line-height:1.7;color:var(--t2);font-style:italic;flex:1}
.testi-auth{display:flex;align-items:center;gap:10px;margin-top:auto}
.tav{width:34px;height:34px;border-radius:50%;background:background:#142C74;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#ffffff;flex-shrink:0}
.tauth-name{font-size:13px;font-weight:600;color:var(--text)}
.tauth-co{font-size:11px;color:var(--t4)}

/* ── FAQ ── */
.faq-sec{padding:80px 0;border-top:1px solid var(--line)}
.faq-list{max-width:700px;margin:0 auto}
.faq-item{border-bottom:1px solid var(--line)}
.faq-q{display:flex;align-items:center;justify-content:space-between;padding:18px 0;cursor:pointer;font-size:15px;font-weight:600;color:var(--text);gap:12px;transition:color .15s}
.faq-q:hover{color:var(--gold)}
.faq-icon{font-size:20px;color:var(--t4);transition:transform .2s;flex-shrink:0;line-height:1}
.faq-a{padding:0 0 18px;font-size:14px;line-height:1.7;color:var(--t3);display:none}
.faq-item.open .faq-a{display:block}
.faq-item.open .faq-icon{transform:rotate(45deg);color:var(--gold)}

/* ── DEPLOY CTA ── */
.deploy-sec{padding:80px 0;text-align:center;border-top:1px solid var(--line);background:rgba(4,4,10,0.5);position:relative;overflow:hidden}
.deploy-sec::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(243,197,49,0.05) 0%,transparent 65%);pointer-events:none}
.deploy-sec h2{font-family:var(--fd);font-size:44px;font-weight:800;letter-spacing:-2px;margin-bottom:14px;position:relative}
.deploy-sec p{font-size:16px;color:var(--t2);max-width:400px;margin:0 auto 28px;line-height:1.65;position:relative}
.deploy-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;position:relative}

/* footer */
footer{background:rgba(4,4,10,0.99);border-top:1px solid var(--line);padding:28px 0;text-align:center;font-size:13px;color:var(--t4)}
footer a{color:var(--t3);transition:color .15s}
footer a:hover{color:var(--text)}
footer .fa-gold{color:var(--gold)}

@keyframes blink{0%,100%{opacity:1}50%{opacity:.35}}
@keyframes nodeglow{0%,100%{box-shadow:0 0 0 0 rgba(243,197,49,0)}50%{box-shadow:0 0 0 5px rgba(243,197,49,0.14)}}

@media(max-width:900px){
  .hero-inner,.what-grid,.ft-grid{grid-template-columns:1fr}
  .testi-grid{grid-template-columns:1fr}
  h1.wh{font-size:38px;letter-spacing:-1.5px}
  .sh2{font-size:30px}
  .pipeline-steps{grid-template-columns:1fr 1fr}
  .hsc-row{grid-template-columns:1fr 1fr}
  .deploy-sec h2{font-size:32px}
}
@media(max-width:640px){
  .w{padding:0 20px}
  .ni{padding:16px 20px}
  h1.wh{font-size:32px}
  .pipeline-steps{grid-template-columns:1fr}
  .pd-nodes{grid-template-columns:repeat(4,1fr)!important}
  .nav-right-hide{display:none}
}

/* ── Light theme overrides ── */
[data-theme="light"] header{background:rgba(249,249,247,0.97)!important;border-bottom:1px solid #e2e2e0!important}
[data-theme="light"] .brand{color:#000000!important}
[data-theme="light"] .btn-gh{color:#555555!important;background:none!important}
[data-theme="light"] .btn-gh:hover{color:#000000!important}
[data-theme="light"] .tog{background:#cccccc!important}
[data-theme="light"] .slabel{color:var(--gold)!important}
[data-theme="light"] .sh2{color:#000000!important}
[data-theme="light"] h1.wh{color:#000000!important}
[data-theme="light"] h1.wh em{color:var(--gold)!important}
[data-theme="light"] .hero-sub{color:#444444!important}
[data-theme="light"] .hero-badge{color:var(--gold)!important;border-color:rgba(20,44,116,0.25)!important;background:rgba(20,44,116,0.07)!important}
[data-theme="light"] .org-chip{color:#000000!important;border-color:rgba(0,0,0,0.15)!important;background:var(--gold)!important}
/* Stats card numbers — black on light card */
[data-theme="light"] .hsc-n{color:#000000!important}
[data-theme="light"] .hsc-l{color:#555555!important}
[data-theme="light"] .hsc-title{color:#999999!important}
[data-theme="light"] .hsc-stat{border-color:#e2e2e0!important}
/* Pipeline step icons — yellow bg, black icon */
[data-theme="light"] .step-icon,
[data-theme="light"] .pd-step-icon,
[data-theme="light"] .pipe-icon{background:var(--gold)!important;border-color:var(--gold-d)!important}
[data-theme="light"] .step-icon svg,
[data-theme="light"] .pd-step-icon svg,
[data-theme="light"] .pipe-icon svg{color:#000000!important;stroke:#000000!important}
[data-theme="light"] .fa-gold{color:var(--gold)!important}
[data-theme="light"] .btn-ln{color:#444444!important;border-color:#cccccc!important}
[data-theme="light"] .btn-ln:hover{color:var(--gold)!important;border-color:var(--gold)!important}
[data-theme="light"] .pipeline-sec{border-color:#e2e2e0!important}
/* Footer always dark — force light text regardless of theme */
footer{background:rgba(4,4,10,0.99)!important;color:#aaaaaa!important}
footer .brand span{color:#ffffff!important}
footer a{color:#888888!important}
footer a:hover{color:#ffffff!important}
footer .fa-gold{color:var(--gold)!important}
</style>
</head>
<body>

<header>
  <div class="ni">
    <a href="/" class="brand"><img src="/logo.png" alt="UNIT"><span>UNIT</span></a>
    <div class="nav-r">
      <button class="tog" id="tog"></button>
      <a href="/#workers" class="btn-gh nav-right-hide">All Workers</a>
      @auth
        <a href="{{ route('dashboard') }}" class="btn-gh">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="btn-gh nav-right-hide">Sign In</a>
      @endauth
      <a href="{{ route('register') }}" class="btn-g">Hire Free</a>
    </div>
  </div>
</header>

{{-- ── HERO ── --}}
<section class="worker-hero">
  <div class="w">
    <div class="hero-inner">
      <div>
        <div class="hero-badge"><span class="hero-pulse"></span>AI Worker &middot; {{ $worker['category'] }}</div>
        <div class="hero-av-row">
          <div class="hero-av">
            {{ strtoupper(substr($worker['name'],0,1)) }}
            <span class="hero-live"></span>
          </div>
          <div class="hero-id">
            <div class="name">{{ $worker['name'] }}</div>
            <div class="role">{{ $worker['role'] }}</div>
          </div>
        </div>
        <h1 class="wh">{!! $worker['headline'] !!}</h1>
        <p class="hero-sub">{{ $worker['sub'] }}</p>
        <div class="hero-ctas">
          <a href="#fasttrack" class="btn-g" style="padding:11px 24px;font-size:15px">Try Free — 30 Seconds</a>
          <a href="{{ route('register') }}?worker={{ $worker['slug'] }}" class="btn-ln" style="padding:11px 20px;font-size:14px">Hire This Employee</a>
        </div>
        <div class="hero-orgs">
          <span class="hero-org-label">Handles:</span>
          @foreach($worker['orgs'] as $org)
            <span class="org-chip">{{ $org }}</span>
          @endforeach
        </div>
      </div>

      {{-- Stats card --}}
      <div class="hero-stats-card">
        <div class="hsc-title">Live Activity</div>
        <div class="hsc-row">
          <div class="hsc-stat">
            <div class="hsc-n">{{ number_format($deploymentCount) }}</div>
            <div class="hsc-l">Active deployments</div>
          </div>
          <div class="hsc-stat">
            <div class="hsc-n">{{ number_format($totalTx) }}</div>
            <div class="hsc-l">Total transactions</div>
          </div>
          <div class="hsc-stat">
            <div class="hsc-n">{{ $tokensToday >= 1000 ? number_format($tokensToday/1000,1).'K' : number_format($tokensToday) }}</div>
            <div class="hsc-l">Tokens processed today</div>
          </div>
          <div class="hsc-stat">
            <div class="hsc-n">99.2%</div>
            <div class="hsc-l">On-time renewal rate</div>
          </div>
        </div>
        <div class="hsc-stat full" style="background:var(--green-bg);border-color:var(--green-border)">
          <div>
            <div style="font-size:13px;font-weight:700;color:var(--green)">{{ $worker['name'] }} is live</div>
            <div style="font-size:11px;color:rgba(34,197,94,0.65);margin-top:2px">Processing renewals right now</div>
          </div>
          <div class="hsc-tag"><span class="hsc-tagdot"></span>Running</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── FULL PIPELINE DISPLAY ── --}}
<section class="pipeline-sec" id="pipeline">
  <div class="w">
    <div class="sec-head">
      <div class="slabel">End-to-End Pipeline</div>
      <h2 class="sh2">Every step {{ $worker['name'] }} runs</h2>
      <p class="ssub">The complete pipeline — from inbox scan to review-ready draft. Fully automated. Fully visible.</p>
    </div>

    <div class="pipeline-display">
      <div class="pd-header">
        <div class="pd-dot" style="background:#ff5f57"></div>
        <div class="pd-dot" style="background:#ffbd2e"></div>
        <div class="pd-dot" style="background:#28c840"></div>
        <div class="pd-title">{{ strtolower($worker['name']) }}.pipeline — live run</div>
      </div>
      <div class="pd-worker-strip">
        <div class="pd-av">{{ strtoupper(substr($worker['name'],0,1)) }}</div>
        <div>
          <div class="pd-wname">{{ $worker['name'] }} · {{ $worker['role'] }}</div>
          <div class="pd-wstat"><span class="pd-stpulse"></span>Running pipeline · {{ $totalTx }} jobs completed</div>
        </div>
        <div class="pd-badge"><span class="hsc-tagdot"></span>Live</div>
      </div>
      <div class="pd-nodes" id="pipe-nodes" style="grid-template-columns:repeat({{ count($worker['how_steps']) }},1fr)">
        @foreach($worker['how_steps'] as $i => $step)
        <div class="pd-node" id="pdn-{{ $i }}" data-idx="{{ $i }}">
          <div class="pdn-ic">{!! $step['icon'] !!}</div>
          <div class="pdn-lb">{{ $step['title'] }}</div>
          <div class="pdn-t" id="pdn-t-{{ $i }}">--:--</div>
        </div>
        @endforeach
      </div>
      <div class="pd-out-bar" id="pd-out">
        <div class="pd-out-text" id="pd-out-text">
          → Draft generated: "{{ $worker['orgs'][0] ?? 'Agency' }} Renewal — complete"<br>
          → All fields populated · Supporting docs attached<br>
          → Queued for review · No action taken without approval
        </div>
      </div>
    </div>

    {{-- Step detail cards --}}
    <div class="pipeline-steps">
      @foreach($worker['how_steps'] as $i => $step)
      <div class="ps-item">
        <div class="ps-num">Step {{ $i + 1 }} of {{ count($worker['how_steps']) }}</div>
        <div class="ps-icon">{!! $step['icon'] !!}</div>
        <div class="ps-title">{{ $step['title'] }}</div>
        <div class="ps-desc">{{ $step['desc'] }}</div>
        @if(!empty($step['detail']))
        <div class="ps-detail">{{ str_replace('\n', "\n", $step['detail']) }}</div>
        @endif
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── WORKER VIDEO ── --}}
@if(!empty($worker['youtube_id']))
<section style="background:var(--surf);padding:72px 0;border-top:1px solid var(--line)">
  <div class="w" style="max-width:900px">
    <div style="text-align:center;margin-bottom:32px">
      <div class="slabel">See It In Action</div>
      <h2 class="sh2" style="margin-bottom:10px">Watch {{ $worker['name'] }} handle a real job.</h2>
      <p style="font-size:15px;color:var(--t3);max-width:500px;margin:0 auto">A full walkthrough — from email in to draft ready. No narration fluff, just the actual pipeline running.</p>
    </div>
    <div style="border-radius:16px;overflow:hidden;background:#000;box-shadow:0 0 0 1px var(--line),0 24px 48px rgba(0,0,0,0.25)">
      <div style="position:relative;padding-bottom:56.25%;height:0">
        <iframe
          src="https://www.youtube.com/embed/{{ $worker['youtube_id'] }}?rel=0&modestbranding=1&color=white"
          title="{{ $worker['name'] }} Demo — UNIT"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          allowfullscreen
          style="position:absolute;top:0;left:0;width:100%;height:100%;border:0">
        </iframe>
      </div>
    </div>
  </div>
</section>
@endif

{{-- ── WHAT IT DOES ── --}}
<section class="what-sec">
  <div class="w">
    <div class="what-grid">
      <div>
        <div class="slabel">Capabilities</div>
        <h2 class="sh2" style="text-align:left;margin-bottom:14px;max-width:420px">{{ $worker['what_h2'] }}</h2>
        @foreach($worker['what_body'] as $p)
          <p style="font-size:15px;line-height:1.7;color:var(--t2);margin-bottom:14px">{{ $p }}</p>
        @endforeach
        <div class="cap-list">
          @foreach($worker['capabilities'] as $cap)
          <div class="cap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            {{ $cap }}
          </div>
          @endforeach
        </div>
      </div>
      <div class="live-queue">
        <div class="lq-head"><span class="lq-pulse"></span>Live task queue</div>
        <div class="task-item t-done">
          <div class="task-icon ic-done">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
          </div>
          <span class="task-lbl">{{ $worker['orgs'][0] ?? 'Agency' }} #2847 — renewal draft sent</span>
          <span class="task-badge tb-done">Done</span>
        </div>
        <div class="task-item t-done">
          <div class="task-icon ic-done">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
          </div>
          <span class="task-lbl">{{ $worker['orgs'][1] ?? 'Agency' }} #3012 — filing confirmed</span>
          <span class="task-badge tb-done">Done</span>
        </div>
        <div class="task-item t-run">
          <div class="task-icon ic-run">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          </div>
          <span class="task-lbl">{{ $worker['orgs'][2] ?? 'Agency' }} #3198 — generating draft</span>
          <span class="task-badge tb-run">Running</span>
        </div>
        <div class="task-item" style="opacity:.5">
          <div class="task-icon ic-q">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          </div>
          <span class="task-lbl">{{ $worker['orgs'][3] ?? $worker['orgs'][0] }} #3201 — due in 12 days</span>
          <span class="task-badge tb-q">Queued</span>
        </div>
        <div class="task-item" style="opacity:.35;border-bottom:none">
          <div class="task-icon ic-q">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          </div>
          <span class="task-lbl">{{ $worker['orgs'][0] }} #3209 — due in 18 days</span>
          <span class="task-badge tb-q">Queued</span>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── FAST TRACK ── --}}
<section class="ft-sec" id="fasttrack">
  <div class="w">
    <div class="ft-grid">
      <div class="ft-copy">
        <div class="slabel">Fast Track</div>
        <h2>See {{ $worker['name'] }} work right now</h2>
        <p>Enter your email and we'll fire a real test job through {{ $worker['name'] }} — watch the pipeline run live, then get the output in your inbox.</p>
        <div class="ft-pts">
          @foreach(['Real AI — not a demo','Output in under 60 seconds','One-click hire after you see it','No account needed'] as $pt)
          <div class="ft-pt">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            {{ $pt }}
          </div>
          @endforeach
        </div>
      </div>
      <div class="ft-card">
        <h3>Run a free test job</h3>
        <p>We'll run a sample {{ $worker['orgs'][0] ?? 'renewal' }} through {{ $worker['name'] }} and show you the output.</p>
        <div class="ft-seq" id="ft-seq">
          @php
          $ftSteps = [
            ['lb'=>'Intake', 'icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16v16H4z"/><path d="M4 9h16"/></svg>'],
            ['lb'=>'Classify', 'icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7h18M3 12h18M3 17h18"/></svg>'],
            ['lb'=>'Lookup', 'icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>'],
            ['lb'=>'Draft', 'icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/></svg>'],
            ['lb'=>'Queue', 'icon'=>'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.63 19.79 19.79 0 01.12 2.18 2 2 0 012.11 0h3a2 2 0 012 1.72"/></svg>'],
          ];
          @endphp
          @foreach($ftSteps as $idx => $fts)
          <div class="fts" id="fts-{{ $idx }}">
            <div class="fts-ic">{!! $fts['icon'] !!}</div>
            <div class="fts-lb">{{ $fts['lb'] }}</div>
          </div>
          @endforeach
        </div>
        <div class="ft-fields">
          <input type="text" class="ft-in" id="wft-name" placeholder="Your name">
          <input type="email" class="ft-in" id="wft-email" placeholder="Work email">
        </div>
        <button class="ft-run-btn" id="wft-btn" onclick="runWorkerFT()">Run Free Test → </button>
        <div class="ft-note" style="margin-top:10px">No account needed. Output emailed to you instantly.</div>
        <div class="ft-result" id="wft-result">
          <div class="ft-result-lbl">✓ Job queued</div>
          <div class="ft-result-body" id="wft-result-text">Processing complete. Check your email for the full draft output and a one-click hire link for {{ $worker['name'] }}.</div>
          <a href="{{ route('register') }}?worker={{ $worker['slug'] }}" class="btn-g" style="display:block;text-align:center;margin-top:14px;padding:11px;border-radius:9px;font-size:14px;width:100%">Hire {{ $worker['name'] }} free →</a>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── TESTIMONIALS ── --}}
<section class="testi-sec">
  <div class="w">
    <div class="sec-head">
      <div class="slabel">Results</div>
      <h2 class="sh2">Teams that run {{ $worker['name'] }}</h2>
      <p class="ssub">Real outcomes from real operations teams.</p>
    </div>
    <div class="testi-grid">
      @foreach($worker['testimonials'] as $t)
      <div class="testi-card">
        <div class="testi-stars">★★★★★</div>
        <p class="testi-q">"{{ $t['quote'] }}"</p>
        <div class="testi-auth">
          <div class="tav">{{ strtoupper(substr($t['name'],0,1)) }}</div>
          <div>
            <div class="tauth-name">{{ $t['name'] }}</div>
            <div class="tauth-co">{{ $t['company'] }}</div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── FAQ ── --}}
<section class="faq-sec">
  <div class="w">
    <div class="sec-head">
      <div class="slabel">FAQ</div>
      <h2 class="sh2">Common questions</h2>
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
</section>

{{-- ── DEPLOY CTA ── --}}
<section class="deploy-sec">
  <div class="w" style="position:relative">
    <div class="slabel" style="text-align:center">Hire {{ $worker['name'] }}</div>
    <h2 style="font-family:var(--fd);font-size:44px;font-weight:800;letter-spacing:-2px;margin-bottom:14px;text-align:center">Ready to hire {{ $worker['name'] }}?</h2>
    <p style="font-size:16px;color:var(--t2);max-width:400px;margin:0 auto 28px;line-height:1.65;text-align:center">First 25 transactions free. No card, no contracts. Cancel any time.</p>
    <div class="deploy-btns">
      <a href="{{ route('register') }}?worker={{ $worker['slug'] }}" class="btn-g" style="padding:13px 28px;font-size:15px">Hire {{ $worker['name'] }} Free →</a>
      <a href="/marketplace" class="btn-ln" style="padding:13px 20px;font-size:14px">See All Employees</a>
    </div>
  </div>
</section>

<footer>
  <div>© {{ date('Y') }} UNIT &nbsp;·&nbsp;
    <a href="/">Home</a> &nbsp;·&nbsp;
    <a href="/marketplace">Employees</a> &nbsp;·&nbsp;
    <a href="{{ route('register') }}" class="fa-gold">Get Started</a>
  </div>
</footer>

<script>
// Theme toggle
(function(){var t=localStorage.getItem('unit-theme-v2')||'dark';document.getElementById('html-root').setAttribute('data-theme',t)})();
document.getElementById('tog').addEventListener('click',function(){
  var n=document.getElementById('html-root').getAttribute('data-theme')==='dark'?'light':'dark';
  document.getElementById('html-root').setAttribute('data-theme',n);
  localStorage.setItem('unit-theme-v2',n);
});

// Pipeline auto-animation
(function(){
  var nodes = document.querySelectorAll('.pd-node');
  var total = nodes.length;
  var current = 0;
  var times = ['09:12:04','09:12:07','09:12:11','09:12:18','09:12:24','09:12:31','09:12:38'];

  function tick(){
    if(current > 0){
      var prev = nodes[current-1];
      prev.classList.remove('active');
      prev.classList.add('done');
      var t = prev.querySelector('.pdn-t');
      if(t){ t.style.color=''; t.textContent = times[current-1]||''; }
      // replace icon with checkmark
      var ic = prev.querySelector('.pdn-ic');
      if(ic) ic.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path d="m4 12 5 5L20 6"/></svg>';
    }
    if(current < total){
      nodes[current].classList.add('active');
      var at = nodes[current].querySelector('.pdn-t');
      if(at){ at.style.color='rgba(243,197,49,0.5)'; at.textContent='...'; }
      current++;
      setTimeout(tick, current < total ? 900 : 600);
    } else {
      var out = document.getElementById('pd-out');
      if(out) out.classList.add('show');
      // reset after 5s
      setTimeout(function(){
        nodes.forEach(function(n){
          n.classList.remove('done','active');
          var t=n.querySelector('.pdn-t'); if(t){t.style.color='';t.textContent=''}
        });
        // restore original icons
        var origIcons = {!! json_encode(array_map(fn($s) => $s['icon'], $worker['how_steps'])) !!};
        nodes.forEach(function(n,i){
          var ic=n.querySelector('.pdn-ic'); if(ic && origIcons[i]) ic.innerHTML=origIcons[i];
        });
        if(out) out.classList.remove('show');
        current=0;
        setTimeout(tick, 1800);
      }, 5000);
    }
  }
  setTimeout(tick, 1200);
})();

// Fast track inline
function runWorkerFT(){
  var name=document.getElementById('wft-name').value.trim();
  var email=document.getElementById('wft-email').value.trim();
  if(!name||!email){
    document.getElementById('wft-name').style.borderColor=name?'':'rgba(239,68,68,0.5)';
    document.getElementById('wft-email').style.borderColor=email?'':'rgba(239,68,68,0.5)';
    return;
  }
  var btn=document.getElementById('wft-btn');
  btn.disabled=true;btn.textContent='Running…';
  var steps=document.querySelectorAll('.fts');
  var sc=0;
  function nextStep(){
    if(sc>0) steps[sc-1].classList.replace('s-active','s-done');
    if(sc<steps.length){
      steps[sc].classList.add('s-active');
      sc++;
      setTimeout(nextStep, sc<steps.length?900:600);
    } else {
      fetch('{{ route("fast-track.submit") }}',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
        body:JSON.stringify({name:name,email:email,worker_slug:'{{ $worker["slug"] }}',source:'worker_page'})
      })
      .then(function(r){return r.json()})
      .then(function(d){
        document.getElementById('wft-result-text').textContent=d.preview||'Job queued. Check your email for the full output and hire link.';
        document.getElementById('wft-result').classList.add('show');
        btn.textContent='✓ Job Queued';
      })
      .catch(function(){
        document.getElementById('wft-result').classList.add('show');
        btn.textContent='✓ Sent';
      });
    }
  }
  nextStep();
}
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="en" id="html-root" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UNIT — AI Workers for License Renewal Teams</title>
<meta name="description" content="UNIT builds AI workflows for license renewal agencies. Each worker is trained for a specific org, knows the process, and gets it done.">
<link rel="icon" type="image/png" href="/logo.png">
<script>(function(){var t=localStorage.getItem('unit-theme')||'dark';document.getElementById('html-root').setAttribute('data-theme',t)})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --gold:#f3c531;--gold-d:#c9920a;--glow:rgba(243,197,49,0.18);
  --green:#22c55e;--green-bg:rgba(34,197,94,0.1);--green-border:rgba(34,197,94,0.25);
  --text:#f0f0f0;--t2:#b8b8b8;--t3:#7a7a8a;--t4:#4a4a5a;
  --line:rgba(255,255,255,0.07);--line2:rgba(255,255,255,0.13);
  --surf:rgba(255,255,255,0.04);--raised:rgba(255,255,255,0.07);--overlay2:rgba(255,255,255,0.03);
  --card:rgba(12,12,18,0.9);--cb:rgba(255,255,255,0.09);
  --fd:'Space Grotesk','Inter',sans-serif;--fb:'Inter',sans-serif;
  --bg:#080810;
}
[data-theme="light"]{
  --gold:#c9870a;--gold-d:#a36908;--glow:rgba(201,135,10,0.15);
  --green:#16a34a;--green-bg:rgba(22,163,74,0.08);--green-border:rgba(22,163,74,0.2);
  --text:#110f0c;--t2:#3a3530;--t3:#7a6e65;--t4:#b0a090;
  --line:rgba(0,0,0,0.07);--line2:rgba(0,0,0,0.13);
  --surf:rgba(0,0,0,0.03);--raised:rgba(0,0,0,0.05);--overlay2:rgba(0,0,0,0.02);
  --card:rgba(252,250,246,0.95);--cb:rgba(0,0,0,0.09);
  --bg:#F0EBE0;
}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:var(--fb);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
.w{max-width:1200px;margin:0 auto;padding:0 48px}

/* ── NAV ── */
header{position:fixed;top:0;left:0;right:0;z-index:300;transition:background .3s,backdrop-filter .3s}
header.scrolled{background:rgba(6,6,14,0.94);backdrop-filter:blur(20px);border-bottom:1px solid var(--line)}
.ni{display:flex;align-items:center;justify-content:space-between;padding:22px 48px;max-width:1200px;margin:0 auto}
.brand{display:flex;align-items:center;gap:10px;font-family:var(--fd);font-weight:800;font-size:18px}
.brand img{width:30px;height:30px;border-radius:7px}
.nl{display:flex;gap:28px}
.nl a{font-size:14px;color:rgba(255,255,255,0.45);transition:color .15s}
.nl a:hover{color:#fff}
.nr{display:flex;align-items:center;gap:14px}
.btn-g{display:inline-flex;align-items:center;gap:7px;background:var(--gold);color:#12100a;font-weight:700;font-size:14px;padding:10px 22px;border-radius:8px;border:none;cursor:pointer;transition:transform .15s,box-shadow .2s;white-space:nowrap;font-family:var(--fb)}
.btn-g:hover{transform:translateY(-1px);box-shadow:0 8px 32px var(--glow)}
.btn-gh{font-size:14px;color:rgba(255,255,255,0.4);cursor:pointer;border:none;background:none;font-family:var(--fb);transition:color .15s}
.btn-gh:hover{color:#fff}
.btn-ln{display:inline-flex;align-items:center;gap:7px;font-size:14px;font-weight:600;padding:10px 20px;border-radius:8px;border:1px solid var(--line2);color:var(--t2);cursor:pointer;background:none;transition:border-color .15s,color .15s;font-family:var(--fb)}
.btn-ln:hover{border-color:var(--gold);color:var(--gold)}
.tog{width:36px;height:20px;border-radius:10px;border:none;cursor:pointer;position:relative;transition:background .2s}
.tog::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:#fff;transition:transform .2s}
[data-theme="dark"] .tog{background:var(--gold)}
[data-theme="light"] .tog{background:#94a3b8}
[data-theme="dark"] .tog::after{transform:translateX(16px)}

/* ── HERO ── */
.hero{position:relative;min-height:100vh;display:flex;flex-direction:column;justify-content:flex-end;overflow:hidden}
.hero-bg{position:absolute;inset:0;background-image:url('/hero-bg.jpg');background-size:cover;background-position:center 30%;z-index:0}
.hero-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(8,8,16,1) 0%,rgba(8,8,16,0.82) 45%,rgba(8,8,16,0.25) 75%,transparent 100%)}
.hero-content{position:relative;z-index:2;padding:0 0 0}
.eyebrow{display:inline-flex;align-items:center;gap:9px;border:1px solid rgba(243,197,49,0.28);background:rgba(243,197,49,0.06);color:var(--gold);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:7px 15px;border-radius:100px;margin-bottom:28px}
.pulse{width:6px;height:6px;border-radius:50%;background:var(--gold);animation:blink 2s ease infinite;flex-shrink:0}
h1.hh{font-family:var(--fd);font-size:86px;line-height:.95;font-weight:800;letter-spacing:-4px;margin-bottom:26px;max-width:900px}
h1.hh .gold{color:var(--gold)}
.hero-sub{font-size:18px;line-height:1.6;color:var(--t2);max-width:520px;margin-bottom:36px}
.hero-ctas{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:32px}
.hero-trust{display:flex;align-items:center;gap:10px;font-size:13px;color:var(--t3)}
.hdot{width:3px;height:3px;border-radius:50%;background:var(--t4)}

/* ── ORG WORKFLOWS STRIP ── */
.org-strip{position:relative;z-index:2;border-top:1px solid var(--line);background:rgba(6,6,14,0.75);backdrop-filter:blur(14px);padding:28px 0}
.org-inner{display:flex;align-items:center;gap:32px}
.org-strip-label{font-size:10.5px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--t4);flex-shrink:0;white-space:nowrap}
.org-grid{display:grid;grid-template-columns:repeat(4,1fr);flex:1;gap:0}
.org-item{padding:0 24px;border-right:1px solid var(--line);display:flex;align-items:center;gap:14px}
.org-item:last-child{border-right:none}
.org-icon{width:38px;height:38px;border-radius:10px;background:rgba(243,197,49,0.08);border:1px solid rgba(243,197,49,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.org-icon svg{width:18px;height:18px;color:var(--gold)}
.org-name{font-family:var(--fd);font-size:14px;font-weight:800;color:var(--text);letter-spacing:.3px}
.org-workflow{font-size:11px;color:var(--t3);margin-top:2px}

/* ── SECTIONS ── */
.sec{padding:100px 0}
.sec-dark{background:rgba(4,4,10,0.6);border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
.sh{text-align:center;max-width:640px;margin:0 auto 64px}
.slabel{font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--gold);margin-bottom:12px}
.sh2{font-family:var(--fd);font-size:50px;font-weight:800;letter-spacing:-2px;line-height:1.04;margin-bottom:14px}
.ssub{font-size:16px;line-height:1.65;color:var(--t2)}

/* ── PIPELINE BOX (universe section) ── */
.univ-grid{display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:center}
.pipe-shell{background:var(--card);border:1px solid var(--cb);border-radius:18px;overflow:hidden;backdrop-filter:blur(12px)}
.pipe-head{display:flex;align-items:center;gap:7px;padding:14px 20px;border-bottom:1px solid var(--line)}
.pd{width:10px;height:10px;border-radius:50%}
.ptitle{font-family:monospace;font-size:11px;color:var(--t3);margin-left:auto}
.pipe-worker-row{display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid var(--line)}
.pav{width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,var(--gold),var(--gold-d));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:17px;color:#12100a;flex-shrink:0}
.pname{font-size:13px;font-weight:700;color:var(--text)}
.pstat{font-size:11px;color:var(--t3);display:flex;align-items:center;gap:5px;margin-top:2px}
.stpulse{width:5px;height:5px;border-radius:50%;background:var(--green);animation:blink 1.4s ease infinite}
.pipe-nodes-row{display:grid;grid-template-columns:repeat(5,1fr);gap:6px;padding:18px 20px}
.pnode{display:flex;flex-direction:column;align-items:center;gap:4px;position:relative}
.pnode:not(:last-child)::after{content:'';position:absolute;right:-7px;top:15px;width:8px;height:1px;background:var(--t4)}
.pn-ic{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;border:1px solid var(--cb);background:var(--raised);transition:all .35s}
.pn-ic svg{width:16px;height:16px;color:var(--t3);transition:color .3s}
.pn-lb{font-size:9.5px;color:var(--t3);text-align:center;line-height:1.3;transition:color .3s}
.pn-t{font-size:9px;font-family:monospace;color:transparent;height:11px;transition:color .3s}
.pnode.done .pn-ic{background:var(--green-bg);border-color:var(--green-border)}
.pnode.done .pn-ic svg{color:var(--green)}
.pnode.done .pn-lb,.pnode.done .pn-t{color:var(--green)}
.pnode.active .pn-ic{background:rgba(243,197,49,0.12);border-color:rgba(243,197,49,0.4);animation:nodeglow 1.1s ease infinite}
.pnode.active .pn-ic svg,.pnode.active .pn-lb{color:var(--gold)}
.pipe-out-bar{padding:10px 20px 16px;border-top:1px solid var(--line);opacity:0;transition:opacity .4s}
.pipe-out-bar.show{opacity:1}
.pipe-out-text{font-size:11.5px;font-family:monospace;color:var(--t2);line-height:1.5}

/* ── WORKER ROWS ── */
.worker-rows{display:flex;flex-direction:column;gap:24px}
.wrow{background:var(--card);border:1px solid var(--cb);border-radius:20px;overflow:hidden;display:grid;grid-template-columns:280px 1fr 320px;backdrop-filter:blur(12px);transition:border-color .25s,box-shadow .25s}
.wrow:hover{border-color:rgba(243,197,49,0.35);box-shadow:0 16px 60px rgba(0,0,0,.5)}
/* Left: identity */
.wr-left{padding:28px;border-right:1px solid var(--line);display:flex;flex-direction:column;gap:0}
.wr-av-wrap{position:relative;width:64px;height:64px;margin-bottom:16px}
.wr-av{width:64px;height:64px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:26px;color:#12100a;background:linear-gradient(135deg,var(--gold),var(--gold-d))}
.wr-live-badge{position:absolute;bottom:-4px;right:-4px;width:16px;height:16px;border-radius:50%;background:var(--green);border:3px solid var(--card)}
.wr-name{font-family:var(--fd);font-size:22px;font-weight:800;color:var(--text);letter-spacing:-.5px;margin-bottom:1px}
.wr-role{font-size:12px;color:var(--t3);margin-bottom:14px;letter-spacing:.3px}
.wr-tagline{font-size:13px;line-height:1.6;color:var(--t2);font-style:italic;flex:1;margin-bottom:16px}
.wr-orgs{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:18px}
.wr-org{padding:4px 10px;border-radius:6px;font-size:10.5px;font-weight:700;letter-spacing:.5px;background:rgba(243,197,49,0.07);border:1px solid rgba(243,197,49,0.15);color:var(--gold)}
.wr-btns{display:flex;gap:8px;margin-top:auto}
.wrb-p{flex:1;padding:9px 0;border-radius:8px;font-size:12.5px;font-weight:700;background:var(--gold);color:#12100a;border:none;cursor:pointer;text-align:center;font-family:var(--fb);transition:opacity .15s}
.wrb-p:hover{opacity:.9}
.wrb-g{flex:1;padding:9px 0;border-radius:8px;font-size:12.5px;font-weight:600;background:transparent;color:var(--t2);border:1px solid var(--cb);cursor:pointer;text-align:center;font-family:var(--fb);transition:border-color .15s,color .15s}
.wrb-g:hover{border-color:var(--t2);color:var(--text)}
/* Center: workflow steps */
.wr-mid{padding:28px 32px;border-right:1px solid var(--line)}
.wr-mid-label{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--t4);margin-bottom:20px}
.wf-steps{display:flex;flex-direction:column;gap:0}
.wf-step{display:flex;align-items:flex-start;gap:14px;padding:10px 0;position:relative}
.wf-step:not(:last-child)::before{content:'';position:absolute;left:18px;top:38px;bottom:-10px;width:1px;background:var(--line2)}
.wf-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid var(--cb);background:var(--raised);transition:all .3s}
.wf-icon svg{width:18px;height:18px;color:var(--t3);transition:color .3s}
.wf-body{flex:1;padding-top:6px}
.wf-title{font-size:13.5px;font-weight:600;color:var(--text);margin-bottom:1px}
.wf-time{font-size:11px;font-family:monospace;color:var(--t4)}
.wf-step.step-done .wf-icon{background:var(--green-bg);border-color:var(--green-border)}
.wf-step.step-done .wf-icon svg{color:var(--green)}
.wf-step.step-done .wf-title{color:var(--green)}
.wf-step.step-active .wf-icon{background:rgba(243,197,49,0.1);border-color:rgba(243,197,49,0.35);animation:nodeglow 1.1s ease infinite}
.wf-step.step-active .wf-icon svg{color:var(--gold)}
.wf-step.step-active .wf-title{color:var(--gold)}
/* Right: output preview */
.wr-right{padding:28px;display:flex;flex-direction:column;gap:14px}
.wr-right-label{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--t4)}
.preview-email{background:var(--surf);border:1px solid var(--cb);border-radius:12px;padding:14px;display:flex;gap:12px;align-items:flex-start}
.pe-icon{width:34px;height:34px;border-radius:8px;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.pe-icon svg{width:16px;height:16px;color:#818cf8}
.pe-from{font-size:10.5px;color:var(--t3);margin-bottom:3px}
.pe-subject{font-size:12.5px;font-weight:600;color:var(--text);line-height:1.4}
.pe-lines{display:flex;flex-direction:column;gap:3px;margin-top:6px}
.pe-line{height:7px;border-radius:4px;background:var(--raised)}
.preview-draft{background:var(--green-bg);border:1px solid var(--green-border);border-radius:12px;padding:14px;margin-top:auto}
.pd-header{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.pd-dot{width:8px;height:8px;border-radius:50%;background:var(--green)}
.pd-title{font-size:12.5px;font-weight:700;color:var(--green)}
.pd-lines{display:flex;flex-direction:column;gap:3px;margin-bottom:10px}
.pd-line{height:7px;border-radius:4px;background:rgba(34,197,94,0.15)}
.pd-line.short{width:60%}
.pd-action{display:flex;justify-content:flex-end}
.pd-btn{padding:5px 14px;border-radius:6px;font-size:11.5px;font-weight:700;background:var(--green);color:#fff;border:none;cursor:pointer;font-family:var(--fb)}
.wr-footer{font-size:12.5px;color:var(--t3);display:flex;align-items:center;gap:7px;border-top:1px solid var(--line);padding-top:12px;margin-top:auto}
.wr-footer svg{width:14px;height:14px;color:var(--gold)}
/* Coming soon row */
.wrow-soon{opacity:.55;cursor:default}
.wrow-soon .wr-av{background:var(--raised);border:1px solid var(--cb);color:var(--t3)}
.wrow-soon .wr-org{background:rgba(255,255,255,0.04);border-color:rgba(255,255,255,0.08);color:var(--t3)}
.soon-chip{display:inline-flex;padding:4px 12px;border-radius:100px;font-size:10.5px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;background:var(--raised);border:1px solid var(--cb);color:var(--t3);margin-bottom:10px}

/* ── HOW IT WORKS ── */
.how-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;position:relative}
.how-steps::before{content:'';position:absolute;top:26px;left:calc(12.5% + 26px);right:calc(12.5% + 26px);height:1px;background:var(--line2);z-index:0}
.hstep{text-align:center;padding:0 10px;z-index:1;position:relative}
.hnode{width:52px;height:52px;border-radius:50%;background:var(--surf);border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;position:relative}
.hnode svg{width:22px;height:22px;color:var(--gold)}
.hnum{position:absolute;top:-5px;right:-5px;width:18px;height:18px;border-radius:50%;background:var(--gold);color:#12100a;font-size:9px;font-weight:800;display:flex;align-items:center;justify-content:center}
.hstep h3{font-family:var(--fd);font-size:15px;font-weight:700;color:var(--text);margin-bottom:7px}
.hstep p{font-size:13px;line-height:1.6;color:var(--t3)}

/* ── TEAM VIDEO SECTION ── */
.team-sec{position:relative;height:92vh;min-height:560px;overflow:hidden;display:flex;align-items:center;justify-content:center}
.team-video-bg{position:absolute;inset:0;background:#04040c;z-index:0}
.team-video-bg video{width:100%;height:100%;object-fit:cover;opacity:0.45}
.team-video-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(4,4,12,0.88) 0%,rgba(4,4,12,0.55) 50%,rgba(4,4,12,0.75) 100%)}
.team-content{position:relative;z-index:2;max-width:860px;padding:0 48px;text-align:left}
.team-eyebrow{display:inline-flex;align-items:center;gap:9px;border:1px solid rgba(243,197,49,0.28);background:rgba(243,197,49,0.06);color:var(--gold);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:7px 15px;border-radius:100px;margin-bottom:28px}
.team-h{font-family:var(--fd);font-size:68px;font-weight:800;letter-spacing:-3px;line-height:.95;color:#fff;margin-bottom:22px}
.team-h .ghost{color:rgba(255,255,255,0.2)}
.team-sub{font-size:18px;line-height:1.6;color:rgba(255,255,255,0.6);max-width:560px;margin-bottom:32px}
.team-stats{display:flex;gap:40px}
.tstat-n{font-family:var(--fd);font-size:36px;font-weight:800;color:var(--gold);letter-spacing:-1.5px}
.tstat-l{font-size:12px;color:rgba(255,255,255,0.4);margin-top:2px}
/* Play button (if video exists) */
.play-btn{display:inline-flex;align-items:center;gap:10px;padding:12px 24px;border-radius:100px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#fff;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s;backdrop-filter:blur(8px)}
.play-btn:hover{background:rgba(255,255,255,0.14)}
.play-icon{width:32px;height:32px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center}
.play-icon svg{width:14px;height:14px;color:#12100a;margin-left:2px}

/* ── SOCIAL PROOF ── */
.ticker-wrap{background:var(--surf);border:1px solid var(--cb);border-radius:10px;padding:14px 18px;display:flex;align-items:center;gap:12px;overflow:hidden;margin-bottom:40px}
.ticker-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:blink 1.5s ease infinite;flex-shrink:0}
.ticker-scroll{overflow:hidden;flex:1;white-space:nowrap}
.ticker-text{display:inline-block;animation:scroll 24s linear infinite;font-size:13px;color:var(--t2)}
@keyframes scroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.proof-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.proof-card{background:var(--card);border:1px solid var(--cb);border-radius:16px;padding:26px;display:flex;flex-direction:column;gap:14px;backdrop-filter:blur(12px)}
.stars{color:var(--gold);font-size:13px;letter-spacing:3px}
.proof-q{font-size:14px;line-height:1.7;color:var(--t2);font-style:italic;flex:1}
.proof-auth{display:flex;align-items:center;gap:10px}
.pav-sm{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold-d));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#12100a;flex-shrink:0}
.pauth-name{font-size:13px;font-weight:600;color:var(--text)}
.pauth-co{font-size:11.5px;color:var(--t4)}

/* ── STATS ── */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:52px 0}
.stat{text-align:center;padding:0 24px;border-right:1px solid var(--line)}
.stat:first-child{border-left:1px solid var(--line)}
.stat-n{font-family:var(--fd);font-size:46px;font-weight:800;color:var(--gold);letter-spacing:-2px;line-height:1}
.stat-l{font-size:12px;color:var(--t3);margin-top:6px;letter-spacing:.3px}

/* ── CTA ── */
.cta-sec{padding:100px 0;text-align:center;position:relative;overflow:hidden}
.cta-sec::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:700px;height:700px;border-radius:50%;background:radial-gradient(circle,rgba(243,197,49,0.055) 0%,transparent 65%);pointer-events:none}

/* ── FOOTER ── */
footer{background:rgba(4,4,10,0.99);border-top:1px solid var(--line);padding:52px 0 28px}
.foot-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;margin-bottom:40px}
.foot-brand{font-size:13px;color:var(--t3);line-height:1.65;margin-top:10px;max-width:200px}
.foot-col h4{font-size:10.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--t4);margin-bottom:14px}
.foot-col a{display:block;font-size:13.5px;color:var(--t3);margin-bottom:9px;transition:color .15s}
.foot-col a:hover{color:var(--text)}
.foot-btm{display:flex;justify-content:space-between;padding-top:20px;border-top:1px solid var(--line);font-size:12px;color:var(--t4)}

/* ── FAST TRACK MODAL ── */
.ft-ov{position:fixed;inset:0;z-index:500;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;pointer-events:none;transition:opacity .3s}
.ft-ov.open{opacity:1;pointer-events:all}
.ft-bd{position:absolute;inset:0;background:rgba(2,2,10,0.85);backdrop-filter:blur(14px)}
.ft-box{background:#0c0c16;border:1px solid rgba(255,255,255,0.1);border-radius:22px;padding:36px;max-width:620px;width:100%;position:relative;z-index:1;transform:translateY(20px) scale(.96);transition:transform .3s;box-shadow:0 40px 100px rgba(0,0,0,.9)}
.ft-ov.open .ft-box{transform:translateY(0) scale(1)}
.ft-close{position:absolute;top:16px;right:16px;width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.06);border:none;cursor:pointer;font-size:14px;color:var(--t3);display:flex;align-items:center;justify-content:center;transition:all .15s;font-family:var(--fb)}
.ft-close:hover{color:#fff;background:rgba(255,255,255,.12)}
.ft-title{font-family:var(--fd);font-size:22px;font-weight:800;color:var(--text);margin-bottom:5px;letter-spacing:-.4px}
.ft-sub{font-size:13.5px;color:var(--t3);margin-bottom:24px;line-height:1.5}
.ft-row{display:flex;gap:10px;margin-bottom:16px}
.ft-in{flex:1;padding:12px 15px;border-radius:10px;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);color:var(--text);font-size:14px;outline:none;transition:border-color .2s;font-family:var(--fb)}
.ft-in:focus{border-color:rgba(243,197,49,0.5)}
.ft-in::placeholder{color:var(--t4)}
.ft-in.err{border-color:rgba(239,68,68,0.5)}
.ft-run{padding:12px 24px;border-radius:10px;background:var(--gold);color:#12100a;font-weight:700;font-size:14px;border:none;cursor:pointer;white-space:nowrap;font-family:var(--fb);transition:opacity .15s}
.ft-run:hover{opacity:.9}
.ft-run:disabled{opacity:.5;cursor:default}
.ft-seq{display:flex;align-items:center;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:12px;padding:18px 16px;margin-bottom:14px}
.fts{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;position:relative}
.fts:not(:last-child)::after{content:'';position:absolute;right:-4px;top:16px;width:6px;height:1px;background:var(--t4)}
.fts-ic{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.08);background:rgba(255,255,255,0.04);transition:all .3s}
.fts-ic svg{width:17px;height:17px;color:var(--t3);transition:color .3s}
.fts-lb{font-size:10px;color:var(--t3);text-align:center;line-height:1.3;transition:color .3s}
.fts.s-done .fts-ic{background:var(--green-bg);border-color:var(--green-border)}
.fts.s-done .fts-ic svg{color:var(--green)}
.fts.s-done .fts-lb{color:var(--green)}
.fts.s-active .fts-ic{background:rgba(243,197,49,0.1);border-color:rgba(243,197,49,0.4);animation:nodeglow 1s ease infinite}
.fts.s-active .fts-ic svg{color:var(--gold)}
.fts.s-active .fts-lb{color:var(--gold)}
.ft-note{font-size:11.5px;color:var(--t4);text-align:center}
.ft-result{display:none;margin-top:14px;padding:16px;border-radius:12px;background:rgba(34,197,94,0.06);border:1px solid rgba(34,197,94,0.18)}
.ft-result.show{display:block}
.ft-result-lbl{font-size:12px;font-weight:700;color:var(--green);margin-bottom:6px}
.ft-result-body{font-size:12.5px;color:var(--t2);line-height:1.6;font-family:monospace;margin-bottom:14px}
.ft-result-btns{display:flex;gap:8px}

/* ── SHARED SVG HELPERS ── */
svg{display:block}

/* ── ANIMATIONS ── */
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
@keyframes nodeglow{0%,100%{box-shadow:0 0 0 0 rgba(243,197,49,0)}50%{box-shadow:0 0 0 5px rgba(243,197,49,0.14)}}

/* ── RESPONSIVE ── */
@media(max-width:1080px){
  .wrow{grid-template-columns:240px 1fr 260px}
  h1.hh{font-size:62px;letter-spacing:-2.5px}
}
@media(max-width:900px){
  h1.hh{font-size:48px;letter-spacing:-2px}
  .sh2{font-size:38px}
  .wrow{grid-template-columns:1fr}
  .wr-left,.wr-mid{border-right:none;border-bottom:1px solid var(--line)}
  .univ-grid{grid-template-columns:1fr}
  .how-steps{grid-template-columns:1fr 1fr;gap:24px}
  .how-steps::before{display:none}
  .proof-grid{grid-template-columns:1fr}
  .stats-row{grid-template-columns:1fr 1fr}
  .stat{border:none;border-bottom:1px solid var(--line);padding:20px}
  .foot-grid{grid-template-columns:1fr 1fr}
  .org-grid{grid-template-columns:1fr 1fr;gap:12px}
  .team-h{font-size:46px}
}
@media(max-width:900px){
  .org-inner{flex-direction:column;align-items:flex-start;gap:16px}
  .org-strip-label{padding-left:0}
  .org-grid{width:100%}
}
@media(max-width:640px){
  .ni{padding-left:18px;padding-right:18px}
  .w{padding-left:20px;padding-right:20px}
  .nl{display:none}
  .nr .btn-g{display:none}
  h1.hh{font-size:38px;letter-spacing:-1.5px}
  .sh2{font-size:30px}
  .ft-row{flex-direction:column}
  .foot-grid{grid-template-columns:1fr}
  .team-h{font-size:34px;letter-spacing:-1.5px}
  .team-stats{flex-wrap:wrap;gap:24px}
  .org-grid{grid-template-columns:1fr 1fr}
  .org-item{padding:12px 16px}
  .sec{padding:64px 0}
}
</style>
</head>
<body>

{{-- ── NAV ── --}}
<header id="nav">
  <div class="ni">
    <a href="/" class="brand"><img src="/logo.png" alt="UNIT"><span>UNIT</span></a>
    <nav class="nl">
      <a href="#workers">Workers</a>
      <a href="#team">Team</a>
      <a href="#how">How It Works</a>
      <a href="javascript:void(0)" onclick="openFT(null)">Try Free</a>
    </nav>
    <div class="nr">
      <button class="tog" id="tog"></button>
      @auth
        <a href="{{ route('dashboard') }}" class="btn-gh">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="btn-gh">Sign In</a>
        <a href="{{ route('register') }}" class="btn-g">Get Started Free</a>
      @endauth
    </div>
  </div>
</header>

{{-- ── HERO ── --}}
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-content">
    <div class="w" style="padding-bottom:0">
      <div class="eyebrow"><span class="pulse"></span>AI Workforce for Compliance Teams</div>
      <h1 class="hh">Your back-office,<br><span class="gold">on autopilot.</span></h1>
      <p class="hero-sub">UNIT builds AI workflows for license renewal agencies. Each worker is trained for a specific org, knows the process, and gets it done — without hand-holding.</p>
      <div class="hero-ctas">
        @auth
          <a href="{{ route('dashboard') }}" class="btn-g" style="padding:13px 28px;font-size:15px">Go to Dashboard →</a>
        @else
          <button onclick="openFT(null)" class="btn-g" style="padding:13px 28px;font-size:15px">Try a Worker Free →</button>
          <a href="{{ route('register') }}" class="btn-ln" style="padding:13px 22px">Sign Up Free</a>
        @endauth
      </div>
      <div class="hero-trust">
        <span>25 free transactions</span><span class="hdot"></span>
        <span>No credit card</span><span class="hdot"></span>
        <span>Deploy in minutes</span>
      </div>
    </div>

    {{-- Org workflows strip --}}
    <div class="org-strip" style="margin-top:60px">
      <div class="w">
        <div class="org-inner">
          <span class="org-strip-label">Workflows built for</span>
          <div class="org-grid">
            @foreach([
              ['<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>','NYCSCA','License Renewal Workflow'],
              ['<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>','DOB','Permit Filing Workflow'],
              ['<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','FDNY','Fire Safety Renewal Workflow'],
              ['<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>','MTA','Transit Compliance Workflow'],
            ] as [$icon,$name,$desc])
            <div class="org-item">
              <div class="org-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $icon !!}</svg>
              </div>
              <div>
                <div class="org-name">{{ $name }}</div>
                <div class="org-workflow">{{ $desc }}</div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── UNIVERSE / PIPELINE ── --}}
<section class="sec sec-dark">
  <div class="w">
    <div class="univ-grid">
      <div>
        <div class="slabel">The UNIT Universe</div>
        <h2 class="sh2" style="text-align:left;margin:0 0 16px;font-size:42px">Built for the agencies<br>that don't wait.</h2>
        <p class="ssub" style="text-align:left;margin-bottom:28px">Each UNIT worker is purpose-built for a specific org's workflow. Not a general AI assistant — a trained coordinator that knows exactly what needs to happen and when.</p>
        @foreach([
          ['<circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>','Agency-trained from day one','Each worker is built around a specific org\'s renewal process, forms, and requirements.'],
          ['<path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>','Every step logged','Full audit trail on every transaction. You see exactly what ran, when, and why.'],
          ['<path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>','Your approval, always','Workers surface the work. You make the call. Nothing submits without you.'],
        ] as [$ico,$title,$body])
        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:18px">
          <div style="width:38px;height:38px;border-radius:10px;background:rgba(243,197,49,0.08);border:1px solid rgba(243,197,49,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18" style="color:var(--gold)">{!! $ico !!}</svg>
          </div>
          <div>
            <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:3px">{{ $title }}</div>
            <div style="font-size:13.5px;color:var(--t3);line-height:1.6">{{ $body }}</div>
          </div>
        </div>
        @endforeach
      </div>
      {{-- Animated pipeline --}}
      <div class="pipe-shell">
        <div class="pipe-head">
          <span class="pd" style="background:#ef4444"></span>
          <span class="pd" style="background:#f59e0b;margin-left:5px"></span>
          <span class="pd" style="background:#22c55e;margin-left:5px"></span>
          <span class="ptitle">ava · renewal coordinator · live</span>
        </div>
        <div class="pipe-worker-row">
          <div class="pav">A</div>
          <div>
            <div class="pname">AVA</div>
            <div class="pstat"><span class="stpulse"></span>Processing renewal queue</div>
          </div>
        </div>
        <div class="pipe-nodes-row">
          @foreach([
            ['pn0','<path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><polyline points="22,6 12,13 2,6"/>','Inbox<br>Check','0.3s'],
            ['pn1','<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>','History<br>Lookup','1.1s'],
            ['pn2','<path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>','AI<br>Analysis','2.4s'],
            ['pn3','<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>','Draft<br>Ready','3.8s'],
            ['pn4','<path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>','Review<br>Queue','4.6s'],
          ] as [$id,$ico,$lb,$t])
          <div class="pnode" id="{{ $id }}">
            <div class="pn-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $ico !!}</svg></div>
            <div class="pn-lb">{!! $lb !!}</div>
            <div class="pn-t" id="{{ $id }}t">{{ $t }}</div>
          </div>
          @endforeach
        </div>
        <div class="pipe-out-bar" id="pipe-out">
          <div class="pipe-out-text">✓ Draft ready — "NYCSCA #2847, John D. Renewal confirmed, all docs verified. Queued for your approval."</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── WORKER DIRECTORY — full rows ── --}}
<section class="sec" id="workers">
  <div class="w">
    <div class="sh">
      <div class="slabel">Available Workers</div>
      <h2 class="sh2">Meet your AI workforce.</h2>
      <p class="ssub">Each worker has a personality, a specialty, and a specific org workflow. Try any worker free — no account needed.</p>
    </div>

    <div class="worker-rows">

      {{-- ── AVA (LIVE) ── --}}
      <div class="wrow">
        {{-- Left: identity --}}
        <div class="wr-left">
          <div class="wr-av-wrap">
            <div class="wr-av">A</div>
            <span class="wr-live-badge"></span>
          </div>
          <div class="wr-name">AVA</div>
          <div class="wr-role">Renewal &amp; Subscription Coordinator</div>
          <p class="wr-tagline">"Methodical, thorough, never misses a deadline. I watch your inbox so you don't have to — and I draft everything before the agency ever has to ask twice."</p>
          <div class="wr-orgs">
            <span class="wr-org">NYCSCA</span><span class="wr-org">DOB</span><span class="wr-org">FDNY</span><span class="wr-org">MTA</span>
          </div>
          <div class="wr-btns">
            <a href="/w/ava" class="wrb-g">View Profile</a>
            <button class="wrb-p" onclick="openFT('AVA — Renewal Coordinator')">Try Free →</button>
          </div>
        </div>
        {{-- Center: workflow steps --}}
        <div class="wr-mid">
          <div class="wr-mid-label">How AVA works — live run</div>
          <div class="wf-steps" id="wf-ava">
            @foreach([
              ['wfs0','<path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><polyline points="22,6 12,13 2,6"/>','Email received','09:01 AM'],
              ['wfs1','<path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>','AVA understands','09:01 AM'],
              ['wfs2','<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>','Finds the right info','09:02 AM'],
              ['wfs3','<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>','Drafts response','09:03 AM'],
              ['wfs4','<path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>','Notifies you','09:03 AM'],
            ] as [$id,$ico,$title,$time])
            <div class="wf-step" id="{{ $id }}">
              <div class="wf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $ico !!}</svg></div>
              <div class="wf-body">
                <div class="wf-title">{{ $title }}</div>
                <div class="wf-time">{{ $time }}</div>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        {{-- Right: output preview --}}
        <div class="wr-right">
          <div class="wr-right-label">Output preview</div>
          <div class="preview-email">
            <div class="pe-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div>
              <div class="pe-from">Agency · NYCSCA</div>
              <div class="pe-subject">License #2847 renewal due in 14 days</div>
              <div class="pe-lines">
                <div class="pe-line" style="width:100%"></div>
                <div class="pe-line" style="width:80%"></div>
                <div class="pe-line" style="width:60%"></div>
              </div>
            </div>
          </div>
          <div class="preview-draft">
            <div class="pd-header">
              <span class="pd-dot"></span>
              <span class="pd-title">AVA's Draft</span>
            </div>
            <div class="pd-lines">
              <div class="pd-line"></div>
              <div class="pd-line"></div>
              <div class="pd-line short"></div>
            </div>
            <div class="pd-action"><button class="pd-btn">Review Draft</button></div>
          </div>
          <div class="wr-footer">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <span>AVA handles it. <strong>You stay in control.</strong></span>
          </div>
        </div>
      </div>

      {{-- ── NOVA (coming soon) ── --}}
      <div class="wrow wrow-soon">
        <div class="wr-left">
          <div class="wr-av-wrap"><div class="wr-av" style="background:linear-gradient(135deg,#1a1020,#6d28d9);color:#c4b5fd;font-size:26px">N</div></div>
          <span class="soon-chip">Coming Soon</span>
          <div class="wr-name">NOVA</div>
          <div class="wr-role">Filing Specialist</div>
          <p class="wr-tagline">"Precise and relentless. I process permit applications end to end — track every status, catch every field error, and never let a filing sit in limbo."</p>
          <div class="wr-orgs"><span class="wr-org">NYCSCA</span><span class="wr-org">DOB</span></div>
          <div class="wr-btns">
            <button class="wrb-g" style="opacity:.4;cursor:not-allowed" disabled>View Profile</button>
            <button class="wrb-p" style="opacity:.35;cursor:not-allowed" disabled>Coming Soon</button>
          </div>
        </div>
        <div class="wr-mid">
          <div class="wr-mid-label">NOVA's workflow — permit filing</div>
          <div class="wf-steps">
            @foreach([
              ['<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>','Application received','–'],
              ['<path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0"/>','Validates all fields','–'],
              ['<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>','Checks DOB requirements','–'],
              ['<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>','Packages submission','–'],
              ['<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>','Tracks status live','–'],
            ] as [$ico,$title,$time])
            <div class="wf-step">
              <div class="wf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $ico !!}</svg></div>
              <div class="wf-body"><div class="wf-title">{{ $title }}</div><div class="wf-time">{{ $time }}</div></div>
            </div>
            @endforeach
          </div>
        </div>
        <div class="wr-right">
          <div class="wr-right-label">Output preview</div>
          <div class="preview-email" style="opacity:.5">
            <div class="pe-icon" style="background:rgba(109,40,217,0.1);border-color:rgba(109,40,217,0.2)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:#a78bfa"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div>
              <div class="pe-from">DOB · Permit Office</div>
              <div class="pe-subject">Permit application #P-4421 ready</div>
              <div class="pe-lines"><div class="pe-line" style="width:90%"></div><div class="pe-line" style="width:70%"></div></div>
            </div>
          </div>
          <div class="preview-draft" style="opacity:.5;background:rgba(109,40,217,0.08);border-color:rgba(109,40,217,0.2)">
            <div class="pd-header">
              <span class="pd-dot" style="background:#a78bfa"></span>
              <span class="pd-title" style="color:#a78bfa">NOVA's Package</span>
            </div>
            <div class="pd-lines"><div class="pd-line" style="background:rgba(109,40,217,0.15)"></div><div class="pd-line short" style="background:rgba(109,40,217,0.15)"></div></div>
            <div class="pd-action"><button class="pd-btn" style="background:#a78bfa;cursor:not-allowed" disabled>Review Filing</button></div>
          </div>
          <div class="wr-footer">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <span>NOVA files it. <strong>You stay in control.</strong></span>
          </div>
        </div>
      </div>

      {{-- ── REX (coming soon) ── --}}
      <div class="wrow wrow-soon">
        <div class="wr-left">
          <div class="wr-av-wrap"><div class="wr-av" style="background:linear-gradient(135deg,#0e1a10,#16a34a);color:#86efac;font-size:26px">R</div></div>
          <span class="soon-chip">Coming Soon</span>
          <div class="wr-name">REX</div>
          <div class="wr-role">Compliance Monitor</div>
          <p class="wr-tagline">"I'm the early warning system. I live inside your compliance calendar — flagging overdue items and surfacing risk before it turns into a fine or a failed inspection."</p>
          <div class="wr-orgs"><span class="wr-org">FDNY</span><span class="wr-org">OSHA</span></div>
          <div class="wr-btns">
            <button class="wrb-g" style="opacity:.4;cursor:not-allowed" disabled>View Profile</button>
            <button class="wrb-p" style="opacity:.35;cursor:not-allowed" disabled>Coming Soon</button>
          </div>
        </div>
        <div class="wr-mid">
          <div class="wr-mid-label">REX's workflow — compliance monitoring</div>
          <div class="wf-steps">
            @foreach([
              ['<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>','Scans compliance calendar','–'],
              ['<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','Checks FDNY requirements','–'],
              ['<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>','Flags risk items','–'],
              ['<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>','Monitors resolution','–'],
              ['<path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>','Alerts you instantly','–'],
            ] as [$ico,$title,$time])
            <div class="wf-step">
              <div class="wf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $ico !!}</svg></div>
              <div class="wf-body"><div class="wf-title">{{ $title }}</div><div class="wf-time">{{ $time }}</div></div>
            </div>
            @endforeach
          </div>
        </div>
        <div class="wr-right">
          <div class="wr-right-label">Output preview</div>
          <div class="preview-email" style="opacity:.5">
            <div class="pe-icon" style="background:rgba(22,163,74,0.1);border-color:rgba(22,163,74,0.2)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color:#86efac"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
              <div class="pe-from">FDNY · Compliance</div>
              <div class="pe-subject">Fire safety inspection overdue — flagged</div>
              <div class="pe-lines"><div class="pe-line" style="width:85%"></div><div class="pe-line" style="width:65%"></div></div>
            </div>
          </div>
          <div class="preview-draft" style="opacity:.5;background:rgba(22,163,74,0.07);border-color:rgba(22,163,74,0.2)">
            <div class="pd-header">
              <span class="pd-dot" style="background:#86efac"></span>
              <span class="pd-title" style="color:#86efac">REX's Alert</span>
            </div>
            <div class="pd-lines"><div class="pd-line" style="background:rgba(22,163,74,0.15)"></div><div class="pd-line short" style="background:rgba(22,163,74,0.15)"></div></div>
            <div class="pd-action"><button class="pd-btn" style="background:#86efac;color:#064e3b;cursor:not-allowed" disabled>Review Alert</button></div>
          </div>
          <div class="wr-footer">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            <span>REX flags it. <strong>You stay in control.</strong></span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

{{-- ── TEAM VIDEO SECTION ── --}}
<section class="team-sec" id="team">
  <div class="team-video-bg">
    {{-- Replace src with actual video when available --}}
    <video autoplay muted loop playsinline poster="/hero-bg.jpg">
      {{-- <source src="/team-video.mp4" type="video/mp4"> --}}
    </video>
  </div>
  <div class="team-content">
    <div class="team-eyebrow"><span class="pulse"></span>The Team Behind UNIT</div>
    <h2 class="team-h">
      We've run the<br>
      <span class="ghost">workflows.</span><br>
      We built the<br>
      workers.
    </h2>
    <p class="team-sub">We're compliance coordinators, ops leads, and engineers who got tired of watching renewal deadlines slip through the cracks of manual processes. UNIT is what we built to fix it.</p>
    <div class="team-stats">
      <div>
        <div class="tstat-n">3+</div>
        <div class="tstat-l">Years in compliance ops</div>
      </div>
      <div>
        <div class="tstat-n">25k+</div>
        <div class="tstat-l">Transactions automated</div>
      </div>
      <div>
        <div class="tstat-n">4</div>
        <div class="tstat-l">Agency workflows live</div>
      </div>
    </div>
  </div>
</section>

{{-- ── HOW IT WORKS ── --}}
<section class="sec sec-dark" id="how">
  <div class="w">
    <div class="sh">
      <div class="slabel">How It Works</div>
      <h2 class="sh2">Signup to deployed<br>in under 10 minutes</h2>
      <p class="ssub">No engineers, no setup calls. Pick a worker and go.</p>
    </div>
    <div class="how-steps">
      @foreach([
        ['<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>','Create your account','60 seconds. No credit card, no approval process.'],
        ['<path d="M9 12l2 2 4-4"/><rect x="3" y="3" width="18" height="18" rx="3"/>','Pick your worker','Choose by agency. Each worker is pre-trained and ready.'],
        ['<path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>','Try it free','25 transactions at no cost. See real output before you pay.'],
        ['<path d="M13 10V3L4 14h7v7l9-11h-7z"/>','Deploy &amp; go','Subscribe and the worker runs on autopilot. Cancel any time.'],
      ] as [$ico,$title,$desc])
      <div class="hstep">
        <div class="hnode">
          <span class="hnum">{{ $loop->index + 1 }}</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $ico !!}</svg>
        </div>
        <h3>{!! $title !!}</h3>
        <p>{!! $desc !!}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── SOCIAL PROOF ── --}}
<section class="sec">
  <div class="w">
    <div class="sh">
      <div class="slabel">Results</div>
      <h2 class="sh2">Teams that run UNIT<br>don't go back</h2>
    </div>
    <div class="ticker-wrap">
      <span class="ticker-dot"></span>
      <div class="ticker-scroll">
        <span class="ticker-text">
          AVA processed 12 NYCSCA renewals this morning &nbsp;·&nbsp; DOB filing #3012 confirmed — submitted by AVA &nbsp;·&nbsp; FDNY renewal batch complete, 8 filings ready for review &nbsp;·&nbsp; License #3198 draft generated in 4.6s &nbsp;·&nbsp; MTA compliance check passed — zero overdue items &nbsp;·&nbsp;
          AVA processed 12 NYCSCA renewals this morning &nbsp;·&nbsp; DOB filing #3012 confirmed — submitted by AVA &nbsp;·&nbsp; FDNY renewal batch complete, 8 filings ready for review &nbsp;·&nbsp; License #3198 draft generated in 4.6s &nbsp;·&nbsp; MTA compliance check passed — zero overdue items &nbsp;·&nbsp;
        </span>
      </div>
    </div>
    <div class="proof-grid">
      @foreach([
        ['"We used to spend two days a week just managing renewals. AVA handles it before we even see it. Now it\'s a five-minute review."','M.T.','Operations Lead, BuildCo'],
        ['"The draft quality is shockingly good. It pulled the right contact, the right license number, flagged an expiry we missed. Exactly what a real coordinator does."','J.R.','Compliance Director, Northline Services'],
        ['"We were skeptical of AI for compliance work. UNIT gives us full visibility into every step. We\'re more confident in our filings now, not less."','S.L.','Project Manager, Vertex Solutions'],
      ] as [$q,$name,$co])
      <div class="proof-card">
        <div class="stars">★★★★★</div>
        <p class="proof-q">{{ $q }}</p>
        <div class="proof-auth">
          <div class="pav-sm">{{ substr($name,0,1) }}</div>
          <div><div class="pauth-name">{{ $name }}</div><div class="pauth-co">{{ $co }}</div></div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── STATS ── --}}
<div class="w">
  <div class="stats-row">
    @foreach([['25k+','Transactions Processed'],['4','Agency Workflows Live'],['99.2%','On-time Delivery Rate'],['< 10min','Avg. Time to Deploy']] as [$n,$l])
    <div class="stat"><div class="stat-n">{{ $n }}</div><div class="stat-l">{{ $l }}</div></div>
    @endforeach
  </div>
</div>

{{-- ── CTA ── --}}
<section class="cta-sec">
  <div class="w" style="text-align:center;position:relative;z-index:1">
    <div class="slabel">Deploy Today</div>
    <h2 class="sh2" style="max-width:520px;margin:0 auto 14px">Put a worker on<br>your team today.</h2>
    <p class="ssub" style="max-width:380px;margin:0 auto 32px">First 25 transactions free. No card, no contracts.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
      @auth
        <a href="{{ route('dashboard') }}" class="btn-g" style="padding:13px 28px;font-size:15px">Go to Dashboard →</a>
      @else
        <button onclick="openFT(null)" class="btn-g" style="padding:13px 28px;font-size:15px">Try a Worker Free →</button>
        <a href="{{ route('register') }}" class="btn-ln" style="padding:13px 22px">Create Account</a>
      @endauth
    </div>
  </div>
</section>

{{-- ── FOOTER ── --}}
<footer>
  <div class="w">
    <div class="foot-grid">
      <div>
        <a href="/" class="brand"><img src="/logo.png" alt="UNIT"><span>UNIT</span></a>
        <p class="foot-brand">AI workers for license renewal teams. Deploy in minutes, automate the work that slows you down.</p>
      </div>
      <div class="foot-col">
        <h4>Workers</h4>
        <a href="/w/ava">AVA — Renewal Coordinator</a>
        <a href="#workers">All Workers</a>
        <a href="/referral">Refer &amp; Earn</a>
        <a href="{{ route('influencer.apply') }}">Partner Program</a>
      </div>
      <div class="foot-col">
        <h4>Product</h4>
        <a href="#how">How It Works</a>
        <a href="javascript:void(0)" onclick="openFT(null)">Try Free</a>
        <a href="{{ route('register') }}">Sign Up</a>
      </div>
      <div class="foot-col">
        <h4>Company</h4>
        <a href="#team">Our Story</a>
        @auth
          <a href="{{ route('dashboard') }}">Dashboard</a>
        @else
          <a href="{{ route('login') }}">Sign In</a>
        @endauth
        <a href="{{ route('influencer.apply') }}">Become a Partner</a>
      </div>
    </div>
    <div class="foot-btm">
      <span>© {{ date('Y') }} UNIT. All rights reserved.</span>
      <span>Built for operations-focused teams.</span>
    </div>
  </div>
</footer>

{{-- ── FAST TRACK MODAL ── --}}
<div class="ft-ov" id="ft-ov">
  <div class="ft-bd" onclick="closeFT()"></div>
  <div class="ft-box">
    <button class="ft-close" onclick="closeFT()">✕</button>
    <div class="ft-title" id="ft-title">Try AVA — free</div>
    <div class="ft-sub">Enter your details and we'll run a live test job. Watch each step fire in sequence — no account needed.</div>
    <div class="ft-row">
      <input type="text" class="ft-in" id="ft-name" placeholder="Your name">
      <input type="email" class="ft-in" id="ft-email" placeholder="Work email">
      <button class="ft-run" id="ft-run" onclick="runFT()">Run Now →</button>
    </div>
    <div class="ft-seq" id="ft-seq">
      @foreach([
        ['<path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><polyline points="22,6 12,13 2,6"/>','Inbox Check'],
        ['<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>','History Lookup'],
        ['<path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>','AI Analysis'],
        ['<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>','Draft Ready'],
        ['<path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>','Review Queue'],
      ] as [$ico,$lb])
      <div class="fts" id="fts{{ $loop->index }}">
        <div class="fts-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $ico !!}</svg></div>
        <div class="fts-lb">{{ $lb }}</div>
      </div>
      @if(!$loop->last)<div style="color:var(--t4);font-size:10px;margin-bottom:20px;flex-shrink:0;padding:0 2px">→</div>@endif
      @endforeach
    </div>
    <div class="ft-note">No account needed. We'll email you the output + a one-click deploy link.</div>
    <div class="ft-result" id="ft-result">
      <div class="ft-result-lbl">✓ Job complete</div>
      <div class="ft-result-body" id="ft-result-body">Sample renewal processed. Check your email for the full output and a one-click deploy link.</div>
      <div class="ft-result-btns">
        <a href="{{ route('register') }}" class="btn-g" style="flex:1;text-align:center;padding:11px;border-radius:9px;font-size:14px">Deploy this worker free →</a>
        <button onclick="closeFT()" class="btn-ln" style="padding:11px 16px;border-radius:9px;font-size:13px">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// ── Theme ────────────────────────────────
(function(){var t=localStorage.getItem('unit-theme')||'dark';document.getElementById('html-root').setAttribute('data-theme',t)})();
document.getElementById('tog').addEventListener('click',function(){
  var n=document.getElementById('html-root').getAttribute('data-theme')==='dark'?'light':'dark';
  document.getElementById('html-root').setAttribute('data-theme',n);localStorage.setItem('unit-theme',n);
});
window.addEventListener('scroll',function(){document.getElementById('nav').classList.toggle('scrolled',window.scrollY>40)},{passive:true});

// ── Pipeline animation (universe section) ──
(function(){
  var ids=['pn0','pn1','pn2','pn3','pn4'];
  var times=['0.3s','1.1s','2.4s','3.8s','4.6s'];
  var cur=-1;
  function tick(){
    cur++;
    if(cur>0){
      var p=document.getElementById(ids[cur-1]);
      p.classList.remove('active');p.classList.add('done');
      p.querySelector('.pn-t').textContent=times[cur-1];
      // checkmark icon
      p.querySelector('.pn-ic svg').innerHTML='<polyline points="20 6 9 17 4 12"/>';
    }
    if(cur<ids.length){
      document.getElementById(ids[cur]).classList.add('active');
      var d=cur<ids.length-1?900+Math.random()*500:900;
      setTimeout(function(){
        if(cur===ids.length-1){
          var last=document.getElementById(ids[cur]);
          last.classList.remove('active');last.classList.add('done');
          last.querySelector('.pn-t').textContent=times[cur];
          last.querySelector('.pn-ic svg').innerHTML='<polyline points="20 6 9 17 4 12"/>';
          document.getElementById('pipe-out').classList.add('show');
          setTimeout(reset,4200);
        } else tick();
      },d);
    }
  }
  function reset(){
    var svgs=[
      '<path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><polyline points="22,6 12,13 2,6"/>',
      '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
      '<path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',
      '<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>',
      '<path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'
    ];
    ids.forEach(function(id,i){
      var n=document.getElementById(id);
      n.classList.remove('active','done');
      n.querySelector('.pn-t').textContent='';
      n.querySelector('.pn-ic svg').innerHTML=svgs[i];
    });
    document.getElementById('pipe-out').classList.remove('show');
    cur=-1;setTimeout(tick,800);
  }
  setTimeout(tick,1200);
})();

// ── Worker row steps animation ──
(function(){
  var steps=['wfs0','wfs1','wfs2','wfs3','wfs4'];
  var cur=-1;
  function tick(){
    cur++;
    if(cur>0) document.getElementById(steps[cur-1]).classList.replace('step-active','step-done');
    if(cur<steps.length){
      document.getElementById(steps[cur]).classList.add('step-active');
      setTimeout(tick, 1000+Math.random()*600);
    } else {
      setTimeout(function(){
        steps.forEach(function(id){
          var s=document.getElementById(id);
          s.classList.remove('step-done','step-active');
        });
        cur=-1;setTimeout(tick,1200);
      },3500);
    }
  }
  setTimeout(tick,800);
})();

// ── Fast Track Modal ──────────────────────
var FT_IDS=['fts0','fts1','fts2','fts3','fts4'];
function openFT(name){
  document.getElementById('ft-title').textContent='Try '+(name||'AVA')+' — free';
  resetFTSeq();
  document.getElementById('ft-result').classList.remove('show');
  var btn=document.getElementById('ft-run');
  btn.textContent='Run Now →';btn.disabled=false;
  document.getElementById('ft-name').classList.remove('err');
  document.getElementById('ft-email').classList.remove('err');
  document.getElementById('ft-ov').classList.add('open');
  document.body.style.overflow='hidden';
}
function closeFT(){
  document.getElementById('ft-ov').classList.remove('open');
  document.body.style.overflow='';
}
function resetFTSeq(){
  var svgs=[
    '<path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><polyline points="22,6 12,13 2,6"/>',
    '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
    '<path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',
    '<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>',
    '<path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'
  ];
  FT_IDS.forEach(function(id,i){
    var s=document.getElementById(id);
    s.classList.remove('s-active','s-done');
    s.querySelector('.fts-ic svg').innerHTML=svgs[i];
  });
}
function animFTSeq(onDone){
  var idx=-1;
  function next(){
    idx++;
    if(idx>0){
      var p=document.getElementById(FT_IDS[idx-1]);
      p.classList.remove('s-active');p.classList.add('s-done');
      p.querySelector('.fts-ic svg').innerHTML='<polyline points="20 6 9 17 4 12"/>';
    }
    if(idx<FT_IDS.length){
      document.getElementById(FT_IDS[idx]).classList.add('s-active');
      var d=600+Math.random()*350;
      if(idx<FT_IDS.length-1) setTimeout(next,d);
      else setTimeout(function(){
        document.getElementById(FT_IDS[idx]).classList.remove('s-active');
        document.getElementById(FT_IDS[idx]).classList.add('s-done');
        document.getElementById(FT_IDS[idx]).querySelector('.fts-ic svg').innerHTML='<polyline points="20 6 9 17 4 12"/>';
        if(onDone) onDone();
      },d);
    }
  }
  next();
}
function runFT(){
  var name=document.getElementById('ft-name').value.trim();
  var email=document.getElementById('ft-email').value.trim();
  var ok=true;
  if(!name){document.getElementById('ft-name').classList.add('err');ok=false;}
  if(!email){document.getElementById('ft-email').classList.add('err');ok=false;}
  if(!ok) return;
  var btn=document.getElementById('ft-run');
  btn.textContent='Running...';btn.disabled=true;
  animFTSeq(function(){
    fetch('{{ route("fast-track.submit") }}',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
      body:JSON.stringify({name:name,email:email,worker_slug:'ava',source:'homepage_modal'})
    })
    .then(function(r){return r.json()})
    .then(function(d){
      document.getElementById('ft-result-body').textContent=d.preview||'Sample renewal processed for '+name+'. Check '+email+' for the full output and deploy link.';
      document.getElementById('ft-result').classList.add('show');
      btn.textContent='✓ Complete';
    })
    .catch(function(){
      document.getElementById('ft-result').classList.add('show');
      btn.textContent='✓ Complete';
    });
  });
}
</script>
</body>
</html>

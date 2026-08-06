<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Brand Video: AI Agent for Brand Content | UNIT (Early Access)</title>
<meta name="description" content="Brand Video is UNIT's upcoming AI agent for turning brand assets into finished video content. Reserve early access and start training its memory today.">
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="apple-touch-icon" href="/favicon.png">
@include('partials.seo-meta', [
    'title'       => 'Brand Video: AI Agent for Brand Content | UNIT (Early Access)',
    'description' => "Brand Video is UNIT's upcoming AI agent for turning brand assets into finished video content. Reserve early access and start training its memory today.",
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
[data-theme="dark"] .lp-hero{background:#0D0D0D}
[data-theme="dark"] .lp-card-sec{background:#0D0D0D}
[data-theme="dark"] .wk-card{background:#111;border-color:#2D2D2D}
[data-theme="dark"] .wk-quote{color:#D1D5DB}
[data-theme="dark"] .wk-bullet{color:#D1D5DB}
[data-theme="dark"] .wk-role{color:#9CA3AF}
[data-theme="dark"] .behind-bar{background:#0D0D0D;border-color:#2D2D2D}
[data-theme="dark"] .behind-item{border-color:#2D2D2D}
[data-theme="dark"] .behind-icon{background:#1a1a1a}
[data-theme="dark"] .behind-h{color:#F3F4F6}
[data-theme="dark"] .behind-p{color:#9CA3AF}
[data-theme="dark"] .cta-foot{background:#0D0D0D}

body{font-family:var(--font-b);color:var(--text);background:var(--bg);-webkit-font-smoothing:antialiased;overflow-x:hidden}
.w{max-width:var(--max);margin:0 auto;padding:0 var(--pad)}

/* ── HERO (text-only — no character art exists for this worker yet) ── */
.lp-hero{padding:clamp(88px,12vw,140px) 0 clamp(56px,7vw,80px);background:#fff;text-align:center}
.lp-hero-inner{max-width:640px;margin:0 auto}
.lp-badge{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8a6a06;background:rgba(245,197,24,.15);padding:7px 16px;border-radius:99px;margin-bottom:20px}
.lp-badge-dot{width:6px;height:6px;border-radius:50%;background:#F5C518;animation:lpPulse 1.6s ease infinite}
@keyframes lpPulse{0%,100%{opacity:1}50%{opacity:.35}}
.lp-h1{font-family:var(--font-h);font-size:clamp(2rem,4.4vw,3.2rem);font-weight:800;line-height:1.08;letter-spacing:-.03em;color:var(--text);margin-bottom:18px}
.lp-h1 em{font-style:normal;position:relative}
.lp-h1 em::after{content:"";position:absolute;left:0;right:0;bottom:-2px;height:4px;background:#F5C518;border-radius:2px}
.lp-sub{font-size:1.05rem;color:var(--t2);line-height:1.7;margin-bottom:32px}
.lp-cta-row{display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;margin-bottom:14px}
.btn-lp-main{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;background:#0D0D0D;color:#fff;transition:opacity .15s,transform .15s}
.btn-lp-main:hover{opacity:.88;transform:translateY(-2px)}
.lp-note{font-size:12.5px;color:var(--t4)}

/* ── FEATURE CARD (mirrors the roster's .wk-card, single instance) ── */
.lp-card-sec{background:var(--soft);padding:clamp(48px,6vw,72px) 0}
.lp-card-wrap{max-width:760px;margin:0 auto}
.sec-eye{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#0D0D0D;text-align:center;margin-bottom:12px}
.sec-h{font-family:var(--font-h);font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15;letter-spacing:-.03em;color:var(--text);text-align:center;margin-bottom:36px}
.wk-card{background:#fff;border:1px solid var(--border);border-radius:20px;padding:clamp(28px,4vw,40px);border-top:3px solid #0D0D0D}
.wk-head{display:flex;align-items:center;gap:12px;margin-bottom:6px}
.wk-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.07);flex-shrink:0}
.wk-icon svg{width:20px;height:20px}
.wk-name{font-family:var(--font-h);font-size:1.5rem;font-weight:800;letter-spacing:-.04em;color:#0D0D0D}
.lp-soon-tag{display:inline-block;margin-left:8px;padding:3px 10px;border-radius:99px;background:rgba(245,197,24,.15);color:#8a6a06;font-size:10.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;vertical-align:middle}
.wk-role{font-size:12px;font-weight:600;color:var(--t3);letter-spacing:.05em;text-transform:uppercase;margin-bottom:16px}
.wk-quote{font-size:15px;font-weight:600;color:var(--t2);line-height:1.65;margin-bottom:22px}
.wk-bullets{display:grid;grid-template-columns:1fr 1fr;gap:12px 24px}
.wk-bullet{display:flex;align-items:flex-start;gap:10px;font-size:13.5px;font-weight:500;color:var(--t2);line-height:1.5}
.wk-check{width:19px;height:19px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#0D0D0D;margin-top:1px}
.wk-check svg{width:10px;height:10px;stroke:#fff;stroke-width:2.5;fill:none}

/* ── BEHIND EVERY WORKER (unchanged from the roster — platform-wide, true for any worker) ── */
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
.cta-foot-inner{background:#0D0D0D;border-radius:24px;padding:clamp(36px,5vw,56px) clamp(32px,5vw,60px);display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap;position:relative;overflow:hidden}
.cta-foot-inner::before{content:'';position:absolute;right:-80px;top:-80px;width:320px;height:320px;border-radius:50%;background:rgba(255,255,255,.04);pointer-events:none}
.cta-foot-left{position:relative;z-index:1}
.cta-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:10px}
.cta-foot-h{font-family:var(--font-h);font-size:clamp(1.5rem,2.8vw,2.2rem);font-weight:800;letter-spacing:-.03em;color:#fff;margin-bottom:6px}
.cta-foot-sub{font-size:14px;color:rgba(255,255,255,.6)}
.cta-foot-right{display:flex;flex-direction:column;align-items:center;gap:8px;position:relative;z-index:1}
.btn-cta-main{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;background:#fff;color:#0D0D0D;box-shadow:0 4px 20px rgba(0,0,0,.2);white-space:nowrap;transition:opacity .15s,transform .15s}
.btn-cta-main:hover{opacity:.95;transform:translateY(-2px)}
.cta-note{font-size:12px;color:rgba(255,255,255,.4)}

@media(max-width:640px){
  .wk-bullets{grid-template-columns:1fr}
  .behind-grid{grid-template-columns:1fr 1fr}
  .behind-item:nth-child(2){border-right:none}
  .behind-item:nth-child(1),.behind-item:nth-child(2){border-bottom:1px solid var(--border)}
  .cta-foot-inner{flex-direction:column;text-align:center;align-items:center}
}
@media(max-width:420px){
  .behind-grid{grid-template-columns:1fr}
  .behind-item{border-right:none!important;border-bottom:1px solid var(--border)}
  .behind-item:last-child{border-bottom:none}
}
</style>
</head>
<body>

<x-public-nav :links="\App\Support\PublicNav::links()">
  <x-slot:cta>
    <a href="{{ route('presale.signup') }}" class="btn-cta">Reserve Early Access <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
  </x-slot>
  <x-slot:mobileCta>
    <a href="{{ route('presale.signup') }}" class="btn-cta" style="justify-content:center">Reserve Early Access →</a>
  </x-slot>
</x-public-nav>

<!-- HERO -->
<section class="lp-hero">
  <div class="w">
    <div class="lp-hero-inner">
      <div class="lp-badge"><span class="lp-badge-dot"></span> Early Access &middot; Not Yet Live</div>
      <h1 class="lp-h1">The AI Agent for <em>Brand Video</em>, coming to UNIT.</h1>
      <p class="lp-sub">Brand Video will turn your logo, colors, voice, and raw footage into finished video content — the same way AVA owns renewals today. It isn't built yet, but its memory can start training now.</p>
      <div class="lp-cta-row">
        <a href="{{ route('presale.signup') }}" class="btn-lp-main">Reserve Early Access <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      </div>
      <div class="lp-note">No credit card required. Free to start training its memory today.</div>
    </div>
  </div>
</section>

<!-- FEATURE CARD -->
<section class="lp-card-sec">
  <div class="w">
    <div class="lp-card-wrap">
      <div class="sec-eye">What it will do</div>
      <h2 class="sec-h">One worker, one job: your brand's video content.</h2>
      <div class="wk-card">
        <div class="wk-head">
          <div class="wk-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><path d="M15 10l4.55-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.45.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
          </div>
          <div class="wk-name">Brand Video <span class="lp-soon-tag">Early Access</span></div>
        </div>
        <div class="wk-role">AI Brand Video Agent</div>
        <p class="wk-quote">Owns your brand's video content from raw material to finished cut.</p>
        <div class="wk-bullets">
          <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Learns your brand from logos, colors, and voice</div>
          <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Organizes raw footage and images by type</div>
          <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Turns raw material into finished video</div>
          <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Keeps every video on-brand automatically</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- BEHIND EVERY WORKER -->
<div class="behind-bar">
  <div class="w">
    <div class="behind-intro">
      <div class="sec-eye">Behind every worker</div>
      <h2 class="sec-h">Real lives. Real work. Real results.</h2>
      <p class="sec-p">Every UNIT worker operates with the same commitment: learning, improving, and reporting back after every single task.</p>
    </div>
    <div class="behind-grid">
      <div class="behind-item">
        <div class="behind-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg></div>
        <div>
          <div class="behind-h">They keep a diary.</div>
          <p class="behind-p">Every worker writes about their day: what they did, what they learned, and what's next. You always know what happened.</p>
        </div>
      </div>
      <div class="behind-item">
        <div class="behind-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
        <div>
          <div class="behind-h">They get better.</div>
          <p class="behind-p">They improve with every task, every challenge, and every win. No retraining required, they just keep getting sharper.</p>
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
          <p class="behind-p">Each worker believes they're alone at UNIT, for now. As you hire more, they'll learn to collaborate. Stay tuned.</p>
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
        <div class="cta-eyebrow">Get in early</div>
        <h2 class="cta-foot-h">Start training Brand Video's memory today.</h2>
        <p class="cta-foot-sub">Reserve your spot now — your brand profile and assets will be ready the moment it launches.</p>
      </div>
      <div class="cta-foot-right">
        <a href="{{ route('presale.signup') }}" class="btn-cta-main">
          Reserve Early Access
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <span class="cta-note">No credit card required.</span>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<x-public-footer />

@include('partials.tracking')
</body>
</html>

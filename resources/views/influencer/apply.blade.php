<!DOCTYPE html>
<html lang="en" id="html-root" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Influencer Partner Program — UNIT</title>
<meta name="description" content="Earn 20–30% recurring MRR commission by promoting UNIT to license renewal and compliance teams. Apply for your vanity link and partner portal access.">
<link rel="icon" type="image/png" href="/logo.png">
<script>(function(){var t=localStorage.getItem('unit-theme')||'dark';document.getElementById('html-root').setAttribute('data-theme',t)})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
<style>
:root,[data-theme="dark"]{
  --bg:#080810;--card:rgba(12,12,20,0.9);--surf:rgba(255,255,255,0.04);--raised:rgba(255,255,255,0.07);
  --cb:rgba(255,255,255,0.09);--line:rgba(255,255,255,0.07);--line2:rgba(255,255,255,0.13);
  --gold:#f3c531;--gold-d:#c9920a;--glow:rgba(243,197,49,0.18);
  --green:#22c55e;--green-bg:rgba(34,197,94,0.1);--green-border:rgba(34,197,94,0.25);
  --blue:#818cf8;--blue-bg:rgba(129,140,248,0.1);--blue-border:rgba(129,140,248,0.25);
  --text:#f0f0f0;--t2:#b8b8b8;--t3:#7a7a8a;--t4:#4a4a5a;
  --fd:'Space Grotesk','Inter',sans-serif;--fb:'Inter',sans-serif;
}
[data-theme="light"]{
  --bg:#F0EBE0;--card:rgba(252,250,246,0.97);--surf:rgba(0,0,0,0.03);--raised:rgba(0,0,0,0.05);
  --cb:rgba(0,0,0,0.09);--line:rgba(0,0,0,0.07);--line2:rgba(0,0,0,0.13);
  --gold:#c9870a;--gold-d:#a36908;--glow:rgba(201,135,10,0.15);
  --green:#16a34a;--green-bg:rgba(22,163,74,0.08);--green-border:rgba(22,163,74,0.2);
  --blue:#4f46e5;--blue-bg:rgba(79,70,229,0.08);--blue-border:rgba(79,70,229,0.2);
  --text:#110f0c;--t2:#3a3530;--t3:#7a6e65;--t4:#b0a090;
}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:var(--fb);-webkit-font-smoothing:antialiased;min-height:100vh}
a{color:inherit;text-decoration:none}
.w{max-width:960px;margin:0 auto;padding:0 40px}

/* NAV */
nav{display:flex;align-items:center;justify-content:space-between;padding:20px 40px;max-width:960px;margin:0 auto}
.brand{display:flex;align-items:center;gap:9px;font-family:var(--fd);font-weight:800;font-size:18px;color:var(--gold)}
.brand img{width:30px;height:30px;border-radius:7px}
.nav-r{display:flex;align-items:center;gap:14px}
.back{font-size:13px;color:var(--t3);transition:color .15s}
.back:hover{color:var(--text)}
.tog{width:34px;height:19px;border-radius:10px;border:none;cursor:pointer;position:relative;transition:background .2s;flex-shrink:0}
.tog::after{content:'';position:absolute;top:2.5px;left:2.5px;width:14px;height:14px;border-radius:50%;background:#fff;transition:transform .2s}
[data-theme="dark"] .tog{background:var(--gold)}[data-theme="light"] .tog{background:#94a3b8}
[data-theme="dark"] .tog::after{transform:translateX(15px)}

/* HERO */
.hero{padding:60px 0 56px;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-180px;right:-180px;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(243,197,49,0.05) 0%,transparent 65%);pointer-events:none}
.eyebrow{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(243,197,49,0.28);background:rgba(243,197,49,0.06);color:var(--gold);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:6px 14px;border-radius:100px;margin-bottom:22px}
.eyebrow-dot{width:6px;height:6px;border-radius:50%;background:var(--gold)}
h1{font-family:var(--fd);font-size:52px;font-weight:800;letter-spacing:-2.5px;line-height:1.02;margin-bottom:16px;max-width:700px}
h1 .gold{color:var(--gold)}
.hero-sub{font-size:16px;line-height:1.7;color:var(--t2);max-width:560px;margin-bottom:32px}
.hero-trust{display:flex;align-items:center;gap:20px;flex-wrap:wrap}
.htrust{display:flex;align-items:center;gap:7px;font-size:13px;color:var(--t3)}
.htrust svg{color:var(--gold);flex-shrink:0}

/* TIER CARDS */
.tiers-sec{padding:56px 0;border-top:1px solid var(--line)}
.sec-label{font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--gold);margin-bottom:12px;text-align:center}
.sec-h{font-family:var(--fd);font-size:34px;font-weight:800;letter-spacing:-1.2px;margin-bottom:10px;text-align:center}
.sec-sub{font-size:15px;color:var(--t2);text-align:center;margin-bottom:48px;line-height:1.6}

.tier-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px}
.tier-card{background:var(--card);border:1px solid var(--cb);border-radius:18px;padding:28px;backdrop-filter:blur(12px);text-align:center;transition:border-color .2s,box-shadow .2s}
.tier-card:hover{box-shadow:0 12px 40px rgba(0,0,0,.5)}
.tier-card.starter{border-color:rgba(34,197,94,0.25)}
.tier-card.pro{border-color:var(--blue-border);background:linear-gradient(135deg,var(--blue-bg) 0%,var(--card) 60%)}
.tier-card.elite{border-color:rgba(243,197,49,0.35);background:linear-gradient(135deg,rgba(243,197,49,0.06) 0%,var(--card) 60%)}
.tier-icon{font-size:28px;margin-bottom:12px}
.tier-name{font-family:var(--fd);font-size:17px;font-weight:800;margin-bottom:16px}
.tier-card.starter .tier-name{color:var(--green)}
.tier-card.pro .tier-name{color:var(--blue)}
.tier-card.elite .tier-name{color:var(--gold)}
.tier-pct{font-family:var(--fd);font-size:52px;font-weight:800;letter-spacing:-2px;line-height:1;margin-bottom:4px}
.tier-card.starter .tier-pct{color:var(--green)}
.tier-card.pro .tier-pct{color:var(--blue)}
.tier-card.elite .tier-pct{color:var(--gold)}
.tier-unit{font-size:13px;color:var(--t3);margin-bottom:16px}
.tier-range{display:inline-block;font-size:11px;font-weight:700;padding:4px 12px;border-radius:100px;margin-bottom:16px}
.tier-card.starter .tier-range{background:var(--green-bg);border:1px solid var(--green-border);color:var(--green)}
.tier-card.pro .tier-range{background:var(--blue-bg);border:1px solid var(--blue-border);color:var(--blue)}
.tier-card.elite .tier-range{background:rgba(243,197,49,0.1);border:1px solid rgba(243,197,49,0.25);color:var(--gold)}
.tier-perks{display:flex;flex-direction:column;gap:7px;text-align:left;margin-top:4px}
.tier-perk{display:flex;align-items:flex-start;gap:8px;font-size:12.5px;color:var(--t2)}
.tier-perk svg{flex-shrink:0;margin-top:1px}
.tier-card.starter .tier-perk svg{color:var(--green)}
.tier-card.pro .tier-perk svg{color:var(--blue)}
.tier-card.elite .tier-perk svg{color:var(--gold)}

/* EARNINGS CALCULATOR */
.calc-sec{padding:56px 0;border-top:1px solid var(--line);background:rgba(4,4,10,0.5)}
.calc-card{background:var(--card);border:1px solid var(--cb);border-radius:20px;padding:36px;backdrop-filter:blur(14px);max-width:680px;margin:40px auto 0}
.calc-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px}
.calc-field label{font-size:11.5px;font-weight:600;color:var(--t3);display:block;margin-bottom:8px;letter-spacing:.3px}
.calc-field input,
.calc-field select{width:100%;padding:12px 15px;border-radius:10px;border:1px solid var(--line2);background:var(--surf);color:var(--text);font-size:15px;font-weight:600;outline:none;transition:border-color .2s;font-family:var(--fd);appearance:none}
.calc-field input:focus,
.calc-field select:focus{border-color:rgba(243,197,49,0.5)}
.calc-result{background:rgba(243,197,49,0.06);border:1px solid rgba(243,197,49,0.2);border-radius:14px;padding:24px;display:grid;grid-template-columns:repeat(3,1fr);gap:0;margin-top:4px}
.cr-item{text-align:center;padding:0 16px}
.cr-item:not(:last-child){border-right:1px solid rgba(243,197,49,0.15)}
.cr-label{font-size:11px;color:var(--t4);margin-bottom:6px;letter-spacing:.3px}
.cr-val{font-family:var(--fd);font-size:28px;font-weight:800;color:var(--gold);letter-spacing:-1px}

/* HOW IT WORKS */
.how-sec{padding:56px 0;border-top:1px solid var(--line)}
.how-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-top:40px;position:relative}
.how-steps::before{content:'';position:absolute;top:28px;left:calc(12.5% + 28px);right:calc(12.5% + 28px);height:1px;background:var(--line2)}
.how-step{display:flex;flex-direction:column;align-items:center;text-align:center;padding:0 10px;z-index:1}
.hs-node{width:56px;height:56px;border-radius:50%;background:var(--surf);border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;position:relative;flex-shrink:0}
.hs-node svg{width:22px;height:22px;color:var(--gold)}
.hs-num{position:absolute;top:-5px;right:-5px;width:18px;height:18px;border-radius:50%;background:var(--gold);color:#12100a;font-size:9px;font-weight:800;display:flex;align-items:center;justify-content:center}
.hs-title{font-size:14px;font-weight:700;color:var(--text);margin-bottom:6px;font-family:var(--fd)}
.hs-desc{font-size:12.5px;color:var(--t3);line-height:1.6}

/* WHO IT'S FOR */
.for-sec{padding:56px 0;border-top:1px solid var(--line);background:rgba(4,4,10,0.5)}
.for-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:40px}
.for-card{background:var(--card);border:1px solid var(--cb);border-radius:14px;padding:22px;display:flex;align-items:flex-start;gap:14px;backdrop-filter:blur(12px)}
.for-icon{width:42px;height:42px;border-radius:11px;background:rgba(243,197,49,0.08);border:1px solid rgba(243,197,49,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.for-icon svg{width:19px;height:19px;color:var(--gold)}
.for-title{font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px}
.for-desc{font-size:12.5px;color:var(--t3);line-height:1.6}
.for-not{background:rgba(239,68,68,0.04);border-color:rgba(239,68,68,0.15)}
.for-not .for-icon{background:rgba(239,68,68,0.08);border-color:rgba(239,68,68,0.18)}
.for-not .for-icon svg{color:#f87171}
.for-not .for-title{color:#f87171}

/* FAQ */
.faq-sec{padding:56px 0;border-top:1px solid var(--line)}
.faq-list{max-width:680px;margin:40px auto 0}
.faq-item{border-bottom:1px solid var(--line)}
.faq-q{display:flex;align-items:center;justify-content:space-between;padding:17px 0;cursor:pointer;font-size:14.5px;font-weight:600;color:var(--text);gap:12px;transition:color .15s}
.faq-q:hover{color:var(--gold)}
.faq-icon{font-size:20px;color:var(--t4);transition:transform .2s;flex-shrink:0;line-height:1}
.faq-a{padding:0 0 17px;font-size:13.5px;line-height:1.7;color:var(--t3);display:none}
.faq-item.open .faq-a{display:block}
.faq-item.open .faq-icon{transform:rotate(45deg);color:var(--gold)}

/* APPLICATION FORM */
.apply-sec{padding:56px 0;border-top:1px solid var(--line)}
.apply-grid{display:grid;grid-template-columns:1fr 420px;gap:56px;align-items:start;margin-top:40px}
.apply-copy h2{font-family:var(--fd);font-size:30px;font-weight:800;letter-spacing:-1px;margin-bottom:12px;line-height:1.1}
.apply-copy p{font-size:14.5px;color:var(--t2);line-height:1.7;margin-bottom:20px}
.apply-pts{display:flex;flex-direction:column;gap:10px}
.apply-pt{display:flex;align-items:center;gap:10px;font-size:13.5px;color:var(--t2)}
.apply-pt svg{color:var(--gold);flex-shrink:0}
.apply-form-card{background:var(--card);border:1px solid var(--cb);border-radius:18px;padding:28px;backdrop-filter:blur(14px)}
.apply-form-card h3{font-family:var(--fd);font-size:18px;font-weight:800;color:var(--text);margin-bottom:5px}
.apply-form-card p{font-size:13px;color:var(--t3);margin-bottom:22px;line-height:1.5}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-field{display:flex;flex-direction:column;gap:5px;margin-bottom:12px}
.form-field label{font-size:11.5px;font-weight:600;color:var(--t3);letter-spacing:.3px}
.form-field input,
.form-field select,
.form-field textarea{padding:11px 14px;border-radius:9px;border:1px solid var(--line2);background:var(--surf);color:var(--text);font-size:14px;outline:none;transition:border-color .2s;font-family:var(--fb);width:100%}
.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus{border-color:rgba(243,197,49,0.5);box-shadow:0 0 0 3px rgba(243,197,49,0.07)}
.form-field input::placeholder,
.form-field textarea::placeholder{color:var(--t4)}
.form-field select option{background:#0c0c18;color:var(--text)}
.submit-btn{width:100%;padding:13px;border-radius:10px;background:var(--gold);color:#12100a;font-weight:700;font-size:15px;border:none;cursor:pointer;font-family:var(--fb);transition:opacity .15s,transform .15s}
.submit-btn:hover{opacity:.92;transform:translateY(-1px)}
.form-note{font-size:11.5px;color:var(--t4);text-align:center;margin-top:10px;line-height:1.5}
.alert-ok{background:var(--green-bg);border:1px solid var(--green-border);color:var(--green);padding:13px 16px;border-radius:10px;margin-bottom:18px;font-size:13.5px;font-weight:500}
.alert-err{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#f87171;padding:13px 16px;border-radius:10px;margin-bottom:18px;font-size:13.5px}

/* TENANT STRIP */
.tenant-strip{margin:40px 0;background:var(--green-bg);border:1px solid var(--green-border);border-radius:16px;padding:24px 28px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap}
.ts-text h3{font-family:var(--fd);font-size:17px;font-weight:700;color:var(--text);margin-bottom:4px}
.ts-text p{font-size:13px;color:var(--t3);line-height:1.5}
.btn-green{display:inline-flex;align-items:center;gap:7px;font-size:13.5px;font-weight:700;padding:10px 20px;border-radius:9px;background:var(--green-bg);border:1px solid var(--green-border);color:var(--green);cursor:pointer;transition:all .15s;white-space:nowrap;font-family:var(--fb)}
.btn-green:hover{background:rgba(34,197,94,0.18)}

footer{border-top:1px solid var(--line);padding:28px 0;text-align:center;font-size:12.5px;color:var(--t4)}
footer a{color:var(--t3);transition:color .15s}
footer a:hover{color:var(--text)}
footer .fa{color:var(--gold)}

@media(max-width:860px){
  .apply-grid{grid-template-columns:1fr}
  .tier-grid{grid-template-columns:1fr}
  .for-grid{grid-template-columns:1fr}
  .how-steps{grid-template-columns:1fr 1fr;gap:24px}
  .how-steps::before{display:none}
  .calc-grid{grid-template-columns:1fr}
  .calc-result{grid-template-columns:1fr}
  .cr-item{border-right:none!important;border-bottom:1px solid rgba(243,197,49,0.15);padding:14px 0}
  .cr-item:last-child{border-bottom:none}
  h1{font-size:36px;letter-spacing:-1.5px}
}
@media(max-width:640px){
  nav{padding:16px 20px}
  .w{padding:0 20px}
  h1{font-size:30px}
  .sec-h{font-size:26px}
  .form-row{grid-template-columns:1fr}
}
</style>
</head>
<body>

<nav>
  <a href="/" class="brand"><img src="/logo.png" alt="UNIT"><span>UNIT</span></a>
  <div class="nav-r">
    <button class="tog" id="tog"></button>
    <a href="/" class="back">← Back to site</a>
  </div>
</nav>

{{-- HERO --}}
<section class="hero">
  <div class="w">
    <div class="eyebrow"><span class="eyebrow-dot"></span>Influencer Partner Program</div>
    <h1>Earn recurring commission<br>every month. <span class="gold">Up to 30%.</span></h1>
    <p class="hero-sub">Promote UNIT to your audience of contractors, compliance teams, and renewal professionals. Every paying customer you refer earns you a percentage of their monthly subscription — for as long as they stay.</p>
    <div class="hero-trust">
      <div class="htrust">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
        Monthly payouts
      </div>
      <div class="htrust">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
        Vanity link + live dashboard
      </div>
      <div class="htrust">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
        Tier upgrades automatic
      </div>
      <div class="htrust">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
        No cap on earnings
      </div>
    </div>
  </div>
</section>

{{-- TIER CARDS --}}
<section class="tiers-sec">
  <div class="w">
    <div class="sec-label">Commission Tiers</div>
    <h2 class="sec-h">More conversions, higher rate</h2>
    <p class="sec-sub">Your tier upgrades automatically as your referral count grows. You never lose earnings already accrued.</p>
    <div class="tier-grid">
      <div class="tier-card starter">
        <div class="tier-icon">🌱</div>
        <div class="tier-name">Starter</div>
        <div class="tier-pct">20%</div>
        <div class="tier-unit">of MRR / month</div>
        <div class="tier-range">0–4 conversions</div>
        <div class="tier-perks">
          <div class="tier-perk">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            Vanity link (unitplatform.com/r/you)
          </div>
          <div class="tier-perk">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            Partner dashboard with click tracking
          </div>
          <div class="tier-perk">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            Monthly payout via bank or PayPal
          </div>
        </div>
      </div>
      <div class="tier-card pro">
        <div class="tier-icon">⚡</div>
        <div class="tier-name">Pro</div>
        <div class="tier-pct">25%</div>
        <div class="tier-unit">of MRR / month</div>
        <div class="tier-range">5–14 conversions</div>
        <div class="tier-perks">
          <div class="tier-perk">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            Everything in Starter
          </div>
          <div class="tier-perk">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            Priority partner support
          </div>
          <div class="tier-perk">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            Early access to new workers
          </div>
        </div>
      </div>
      <div class="tier-card elite">
        <div class="tier-icon">👑</div>
        <div class="tier-name">Elite</div>
        <div class="tier-pct">30%</div>
        <div class="tier-unit">of MRR / month</div>
        <div class="tier-range">15+ conversions</div>
        <div class="tier-perks">
          <div class="tier-perk">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            Everything in Pro
          </div>
          <div class="tier-perk">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            Co-branded content + feature spotlights
          </div>
          <div class="tier-perk">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            Direct line to UNIT product team
          </div>
        </div>
      </div>
    </div>

    {{-- Tenant strip --}}
    <div class="tenant-strip">
      <div class="ts-text">
        <h3>Already a UNIT customer? Try the Referral Program instead.</h3>
        <p>Tenants earn $25 account credit per conversion — no audience required. Just share your link with another team.</p>
      </div>
      <a href="{{ route('referral.index') }}" class="btn-green">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
        Referral Program →
      </a>
    </div>
  </div>
</section>

{{-- EARNINGS CALCULATOR --}}
<section class="calc-sec">
  <div class="w">
    <div class="sec-label">Earnings Calculator</div>
    <h2 class="sec-h">See what you could earn</h2>
    <p class="sec-sub">Estimate your monthly commission based on your audience size and conversion rate.</p>
    <div class="calc-card">
      <div class="calc-grid">
        <div class="calc-field">
          <label>AUDIENCE SIZE</label>
          <input type="number" id="calc-audience" value="5000" min="100" oninput="calcEarnings()">
        </div>
        <div class="calc-field">
          <label>EST. CONVERSION RATE (%)</label>
          <input type="number" id="calc-conv" value="0.5" step="0.1" min="0.1" max="10" oninput="calcEarnings()">
        </div>
        <div class="calc-field">
          <label>AVG SUBSCRIPTION ($/mo)</label>
          <select id="calc-plan" onchange="calcEarnings()">
            <option value="49">Starter — $49/mo</option>
            <option value="99" selected>Growth — $99/mo</option>
            <option value="199">Scale — $199/mo</option>
          </select>
        </div>
        <div class="calc-field">
          <label>YOUR TIER</label>
          <select id="calc-tier" onchange="calcEarnings()">
            <option value="0.20">Starter — 20%</option>
            <option value="0.25" selected>Pro — 25%</option>
            <option value="0.30">Elite — 30%</option>
          </select>
        </div>
      </div>
      <div class="calc-result">
        <div class="cr-item">
          <div class="cr-label">CONVERSIONS</div>
          <div class="cr-val" id="calc-out-conv">25</div>
        </div>
        <div class="cr-item">
          <div class="cr-label">MONTHLY COMMISSION</div>
          <div class="cr-val" id="calc-out-mo">$618</div>
        </div>
        <div class="cr-item">
          <div class="cr-label">ANNUAL EARNINGS</div>
          <div class="cr-val" id="calc-out-yr">$7.4K</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- HOW IT WORKS --}}
<section class="how-sec">
  <div class="w">
    <div class="sec-label">How It Works</div>
    <h2 class="sec-h">Apply once. Earn every month.</h2>
    <p class="sec-sub">The whole process from application to first payout takes less than a week.</p>
    <div class="how-steps">
      <div class="how-step">
        <div class="hs-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/></svg>
          <span class="hs-num">1</span>
        </div>
        <div class="hs-title">Apply below</div>
        <div class="hs-desc">Fill out the short form. We review all applications within 2 business days.</div>
      </div>
      <div class="how-step">
        <div class="hs-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
          <span class="hs-num">2</span>
        </div>
        <div class="hs-title">Get your vanity link</div>
        <div class="hs-desc">You'll receive a unique URL like <span style="font-family:monospace;font-size:11.5px;color:var(--gold)">unitplatform.com/r/you</span> plus portal access.</div>
      </div>
      <div class="how-step">
        <div class="hs-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          <span class="hs-num">3</span>
        </div>
        <div class="hs-title">Share with your audience</div>
        <div class="hs-desc">Post, email, embed in content. Every click and conversion is tracked live in your dashboard.</div>
      </div>
      <div class="how-step">
        <div class="hs-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
          <span class="hs-num">4</span>
        </div>
        <div class="hs-title">Earn monthly</div>
        <div class="hs-desc">Commission is calculated on the 1st of each month and paid out within 5 business days.</div>
      </div>
    </div>
  </div>
</section>

{{-- WHO IT'S FOR --}}
<section class="for-sec">
  <div class="w">
    <div class="sec-label">Who It's For</div>
    <h2 class="sec-h">Built for niche creators and consultants</h2>
    <p class="sec-sub">The influencer program works best when your audience overlaps with our customers.</p>
    <div class="for-grid">
      <div class="for-card">
        <div class="for-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div>
          <div class="for-title">Compliance &amp; licensing consultants</div>
          <div class="for-desc">Advisors who work with contractors, real estate firms, or government compliance teams day-to-day.</div>
        </div>
      </div>
      <div class="for-card">
        <div class="for-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 01-2.18 2A19.79 19.79 0 018 18.08 19.5 19.5 0 013.08 8 19.79 19.79 0 012.1 2.18a2 2 0 012-.12h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91"/></svg>
        </div>
        <div>
          <div class="for-title">Industry newsletter writers</div>
          <div class="for-desc">Authors covering construction, real estate, municipal compliance, or government contracting.</div>
        </div>
      </div>
      <div class="for-card">
        <div class="for-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
        </div>
        <div>
          <div class="for-title">YouTube &amp; podcast educators</div>
          <div class="for-desc">Creators teaching operations, back-office automation, or regulatory workflows to professional audiences.</div>
        </div>
      </div>
      <div class="for-card">
        <div class="for-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
        </div>
        <div>
          <div class="for-title">Software &amp; ops tooling reviewers</div>
          <div class="for-desc">Bloggers or LinkedIn creators who regularly review B2B software for operations and compliance teams.</div>
        </div>
      </div>
      <div class="for-card for-not">
        <div class="for-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div>
          <div class="for-title">Not a fit: general lifestyle creators</div>
          <div class="for-desc">We don't accept applications from audiences unrelated to compliance, licensing, or business operations.</div>
        </div>
      </div>
      <div class="for-card for-not">
        <div class="for-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div>
          <div class="for-title">Not a fit: under 1,000 followers</div>
          <div class="for-desc">We look for a meaningful existing audience before approving. If you're building one, check back in 6 months.</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- FAQ --}}
<section class="faq-sec">
  <div class="w">
    <div class="sec-label">FAQ</div>
    <h2 class="sec-h">Common questions</h2>
    <div class="faq-list">
      @foreach([
        ['q'=>'When and how do I get paid?','a'=>'Commission is calculated on the 1st of every month based on the MRR of your active referred customers. Payouts are sent within 5 business days via bank transfer or PayPal. There\'s a $50 minimum payout threshold.'],
        ['q'=>'How long does commission last?','a'=>'Commission is recurring — you earn for as long as a referred customer stays subscribed. If they cancel, that commission stops. If they re-subscribe, it resumes.'],
        ['q'=>'How do tier upgrades work?','a'=>'Tiers are based on cumulative paid conversions. Once you hit the threshold (5 for Pro, 15 for Elite), your rate upgrades automatically for all future commission calculations — including existing customers.'],
        ['q'=>'What counts as a conversion?','a'=>'A conversion is when someone you referred activates a paid UNIT subscription. Free trial sign-ups do not count until they convert to paid.'],
        ['q'=>'Can I promote UNIT without applying first?','a'=>'You can share your referral link informally, but to get a vanity link, access the partner dashboard, and receive payouts, you need to complete the application and be approved.'],
        ['q'=>'What\'s the difference between this and the Referral Program?','a'=>'The Referral Program is for existing UNIT customers who want to earn $25 account credit per conversion — no audience required. The Influencer Program is for creators and consultants who promote UNIT to their audience and earn 20–30% recurring MRR commission.'],
        ['q'=>'Can I be in both programs?','a'=>'Yes. If you\'re a UNIT customer and a creator, you can earn account credit through the Referral Program and recurring commission through the Influencer Program simultaneously — with separate links.'],
      ] as $faq)
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

{{-- APPLICATION FORM --}}
<section class="apply-sec" id="apply">
  <div class="w">
    <div class="sec-label">Apply</div>
    <div class="apply-grid">
      <div class="apply-copy">
        <h2>Ready to partner with UNIT?</h2>
        <p>Fill out the short application. We review every submission personally and respond within 2 business days. Approved partners receive their vanity link, dashboard access, and a welcome kit by email.</p>
        <div class="apply-pts">
          @foreach(['2-day review turnaround','Vanity link + live analytics dashboard','Monthly payouts, no minimum contract','Tier upgrades are fully automatic'] as $pt)
          <div class="apply-pt">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m4 12 5 5L20 6"/></svg>
            {{ $pt }}
          </div>
          @endforeach
        </div>
      </div>
      <div class="apply-form-card">
        <h3>Partner application</h3>
        <p>Takes about 2 minutes to complete.</p>

        @if(session('success'))
          <div class="alert-ok">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="alert-err">{{ session('error') }}</div>
        @endif
        @if($errors->any())
          <div class="alert-err">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>
        @endif

        <form action="{{ route('influencer.apply.submit') }}" method="POST">
          @csrf
          <input type="hidden" name="utm_source" value="{{ request()->query('utm_source') }}">
          <div class="form-row">
            <div class="form-field">
              <label>FULL NAME *</label>
              <input type="text" name="name" value="{{ old('name') }}" placeholder="Jane Smith" required>
            </div>
            <div class="form-field">
              <label>EMAIL *</label>
              <input type="email" name="email" value="{{ old('email') }}" placeholder="jane@example.com" required>
            </div>
          </div>
          <div class="form-field">
            <label>PRIMARY CHANNEL *</label>
            <select name="channel" required>
              <option value="">Select your main channel</option>
              @foreach(['LinkedIn','YouTube','Newsletter','Podcast','Instagram','TikTok','Twitter / X','Blog','Other'] as $ch)
                <option value="{{ strtolower(str_replace(' / ','-',$ch)) }}" @selected(old('channel')===strtolower(str_replace(' / ','-',$ch)))>{{ $ch }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-row">
            <div class="form-field">
              <label>AUDIENCE SIZE *</label>
              <select name="audience_size" required>
                <option value="">Select range</option>
                @foreach(['Under 1k','1k–5k','5k–10k','10k–50k','50k–100k','100k+'] as $sz)
                  <option value="{{ $sz }}" @selected(old('audience_size')===$sz)>{{ $sz }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-field">
              <label>NICHE / INDUSTRY</label>
              <input type="text" name="niche" value="{{ old('niche') }}" placeholder="e.g. real estate, compliance">
            </div>
          </div>
          <div class="form-field" style="margin-bottom:16px">
            <label>TELL US ABOUT YOUR AUDIENCE (optional)</label>
            <textarea name="notes" rows="3" placeholder="Who do you reach? Why would UNIT resonate with them?" style="resize:vertical">{{ old('notes') }}</textarea>
          </div>
          <button type="submit" class="submit-btn">Apply to Partner →</button>
          <p class="form-note">We review all applications within 2 business days and respond by email whether approved or not.</p>
        </form>
      </div>
    </div>
  </div>
</section>

<footer>
  <div>© {{ date('Y') }} UNIT &nbsp;·&nbsp;
    <a href="/">Home</a> &nbsp;·&nbsp;
    <a href="{{ route('referral.index') }}">Referral Program</a> &nbsp;·&nbsp;
    <a href="{{ route('register') }}" class="fa">Get Started</a>
  </div>
</footer>

<script>
(function(){var t=localStorage.getItem('unit-theme')||'dark';document.getElementById('html-root').setAttribute('data-theme',t)})();
document.getElementById('tog').addEventListener('click',function(){
  var n=document.getElementById('html-root').getAttribute('data-theme')==='dark'?'light':'dark';
  document.getElementById('html-root').setAttribute('data-theme',n);
  localStorage.setItem('unit-theme',n);
});

function calcEarnings(){
  var audience = parseFloat(document.getElementById('calc-audience').value)||0;
  var convRate = parseFloat(document.getElementById('calc-conv').value)||0;
  var plan     = parseFloat(document.getElementById('calc-plan').value)||99;
  var tier     = parseFloat(document.getElementById('calc-tier').value)||0.25;
  var convs    = Math.round(audience * (convRate / 100));
  var monthly  = convs * plan * tier;
  var annual   = monthly * 12;
  document.getElementById('calc-out-conv').textContent = convs.toLocaleString();
  document.getElementById('calc-out-mo').textContent = '$' + monthly.toLocaleString(undefined,{maximumFractionDigits:0});
  document.getElementById('calc-out-yr').textContent = annual >= 1000 ? '$' + (annual/1000).toFixed(1) + 'K' : '$' + annual.toFixed(0);
}
calcEarnings();
</script>
</body>
</html>

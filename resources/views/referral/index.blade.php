<!DOCTYPE html>
<html lang="en" id="html-root" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Refer & Earn — UNIT Referral Program</title>
<meta name="description" content="Refer license renewal teams to UNIT and earn $25 account credit for every team that subscribes. Plus your referral gets 10 extra free transactions.">
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
.hero-eyebrow{display:inline-flex;align-items:center;gap:8px;border:1px solid rgba(243,197,49,0.28);background:rgba(243,197,49,0.06);color:var(--gold);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:6px 14px;border-radius:100px;margin-bottom:22px}
.hero-eyebrow-dot{width:6px;height:6px;border-radius:50%;background:var(--gold)}
h1{font-family:var(--fd);font-size:52px;font-weight:800;letter-spacing:-2.5px;line-height:1.02;margin-bottom:16px;max-width:700px}
h1 .gold{color:var(--gold)}
.hero-sub{font-size:16px;line-height:1.7;color:var(--t2);max-width:540px;margin-bottom:32px}
.hero-ctas{display:flex;gap:10px;flex-wrap:wrap}
.btn-g{display:inline-flex;align-items:center;gap:7px;background:var(--gold);color:#12100a;font-weight:700;font-size:14px;padding:11px 22px;border-radius:9px;border:none;cursor:pointer;transition:transform .15s,box-shadow .2s;font-family:var(--fb)}
.btn-g:hover{transform:translateY(-1px);box-shadow:0 6px 24px var(--glow)}
.btn-ln{display:inline-flex;align-items:center;gap:7px;font-size:14px;font-weight:600;padding:11px 20px;border-radius:9px;border:1px solid var(--line2);color:var(--t2);cursor:pointer;background:none;transition:border-color .15s,color .15s;font-family:var(--fb)}
.btn-ln:hover{border-color:var(--gold);color:var(--gold)}

/* REWARD CARDS */
.rewards{padding:56px 0;border-top:1px solid var(--line)}
.sec-label{font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--gold);margin-bottom:12px;text-align:center}
.sec-h{font-family:var(--fd);font-size:34px;font-weight:800;letter-spacing:-1.2px;margin-bottom:10px;text-align:center}
.sec-sub{font-size:15px;color:var(--t2);text-align:center;margin-bottom:48px;line-height:1.6}
.reward-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.reward-card{background:var(--card);border:1px solid var(--cb);border-radius:18px;padding:28px;backdrop-filter:blur(12px);position:relative;overflow:hidden}
.reward-card.primary{border-color:rgba(243,197,49,0.3);background:linear-gradient(135deg,rgba(243,197,49,0.06) 0%,var(--card) 60%)}
.reward-card.secondary{border-color:var(--blue-border);background:linear-gradient(135deg,var(--blue-bg) 0%,var(--card) 60%)}
.rc-eyebrow{font-size:10.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:16px}
.rc-eyebrow.gold{color:var(--gold)}
.rc-eyebrow.blue{color:var(--blue)}
.rc-amount{font-family:var(--fd);font-size:56px;font-weight:800;letter-spacing:-3px;line-height:1;margin-bottom:6px}
.rc-amount.gold{color:var(--gold)}
.rc-amount.blue{color:var(--blue)}
.rc-unit{font-size:16px;font-weight:600;color:var(--t3);margin-bottom:12px}
.rc-desc{font-size:13.5px;line-height:1.65;color:var(--t2)}
.rc-badge{position:absolute;top:20px;right:20px;padding:4px 10px;border-radius:100px;font-size:10px;font-weight:700;letter-spacing:.5px}
.rc-badge.you{background:rgba(243,197,49,0.12);border:1px solid rgba(243,197,49,0.25);color:var(--gold)}
.rc-badge.them{background:var(--blue-bg);border:1px solid var(--blue-border);color:var(--blue)}

/* HOW IT WORKS */
.how{padding:56px 0;border-top:1px solid var(--line)}
.how-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-top:40px;position:relative}
.how-steps::before{content:'';position:absolute;top:28px;left:calc(12.5% + 28px);right:calc(12.5% + 28px);height:1px;background:var(--line2)}
.how-step{display:flex;flex-direction:column;align-items:center;text-align:center;padding:0 10px;z-index:1}
.hs-node{width:56px;height:56px;border-radius:50%;background:var(--surf);border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;position:relative;flex-shrink:0}
.hs-node svg{width:22px;height:22px;color:var(--gold)}
.hs-num{position:absolute;top:-5px;right:-5px;width:18px;height:18px;border-radius:50%;background:var(--gold);color:#12100a;font-size:9px;font-weight:800;display:flex;align-items:center;justify-content:center}
.hs-title{font-size:14px;font-weight:700;color:var(--text);margin-bottom:6px;font-family:var(--fd)}
.hs-desc{font-size:12.5px;color:var(--t3);line-height:1.6}

/* TIERS */
.tiers{padding:56px 0;border-top:1px solid var(--line);background:rgba(4,4,10,0.5)}
.tier-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:40px}
.tier-card{background:var(--card);border:1px solid var(--cb);border-radius:14px;padding:20px;text-align:center;backdrop-filter:blur(12px);transition:border-color .2s}
.tier-card:hover{border-color:rgba(243,197,49,0.3)}
.tier-icon{font-size:24px;margin-bottom:10px}
.tier-name{font-family:var(--fd);font-size:15px;font-weight:800;color:var(--text);margin-bottom:4px}
.tier-req{font-size:11px;color:var(--t4);margin-bottom:14px}
.tier-reward{font-family:var(--fd);font-size:24px;font-weight:800;color:var(--gold);margin-bottom:4px}
.tier-reward-label{font-size:11.5px;color:var(--t3)}

/* FAQ */
.faq{padding:56px 0;border-top:1px solid var(--line)}
.faq-list{max-width:680px;margin:40px auto 0}
.faq-item{border-bottom:1px solid var(--line)}
.faq-q{display:flex;align-items:center;justify-content:space-between;padding:17px 0;cursor:pointer;font-size:14.5px;font-weight:600;color:var(--text);gap:12px;transition:color .15s}
.faq-q:hover{color:var(--gold)}
.faq-icon{font-size:20px;color:var(--t4);transition:transform .2s;flex-shrink:0;line-height:1}
.faq-a{padding:0 0 17px;font-size:13.5px;line-height:1.7;color:var(--t3);display:none}
.faq-item.open .faq-a{display:block}
.faq-item.open .faq-icon{transform:rotate(45deg);color:var(--gold)}

/* CTA */
.cta{padding:72px 0;text-align:center;position:relative;overflow:hidden}
.cta::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(243,197,49,0.05) 0%,transparent 65%);pointer-events:none}
.cta h2{font-family:var(--fd);font-size:38px;font-weight:800;letter-spacing:-1.5px;margin-bottom:12px;position:relative}
.cta p{font-size:15px;color:var(--t2);max-width:380px;margin:0 auto 28px;line-height:1.65;position:relative}
.cta-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;position:relative}

/* Also a creator? strip */
.creator-strip{margin:40px 0;background:rgba(129,140,248,0.06);border:1px solid var(--blue-border);border-radius:16px;padding:24px 28px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap}
.cs-text h3{font-family:var(--fd);font-size:17px;font-weight:700;color:var(--text);margin-bottom:4px}
.cs-text p{font-size:13px;color:var(--t3);line-height:1.5}
.btn-blue{display:inline-flex;align-items:center;gap:7px;font-size:13.5px;font-weight:700;padding:10px 20px;border-radius:9px;background:var(--blue-bg);border:1px solid var(--blue-border);color:var(--blue);cursor:pointer;transition:all .15s;white-space:nowrap;font-family:var(--fb)}
.btn-blue:hover{background:rgba(129,140,248,0.18)}

footer{border-top:1px solid var(--line);padding:28px 0;text-align:center;font-size:12.5px;color:var(--t4)}
footer a{color:var(--t3);transition:color .15s}
footer a:hover{color:var(--text)}
footer .fa{color:var(--gold)}

@media(max-width:768px){
  nav{padding:16px 20px}
  .w{padding:0 20px}
  h1{font-size:36px;letter-spacing:-1.5px}
  .reward-grid{grid-template-columns:1fr}
  .how-steps{grid-template-columns:1fr 1fr;gap:24px}
  .how-steps::before{display:none}
  .tier-grid{grid-template-columns:1fr 1fr;gap:10px}
  .sec-h{font-size:26px}
  .cta h2{font-size:28px}
  .rc-amount{font-size:44px}
}
@media(max-width:480px){
  .tier-grid{grid-template-columns:1fr 1fr}
  .hero-ctas{flex-direction:column}
  .btn-g,.btn-ln{width:100%;justify-content:center}
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
    <div class="hero-eyebrow"><span class="hero-eyebrow-dot"></span>Tenant Referral Program</div>
    <h1>Refer a team.<br>Earn <span class="gold">$25 credit.</span></h1>
    <p class="hero-sub">Share your unique referral link with other license renewal teams. When they subscribe, you get $25 account credit — and they get 10 extra free transactions on us.</p>
    <div class="hero-ctas">
      @auth
        <a href="{{ route('dashboard') }}" class="btn-g">Get My Referral Link →</a>
      @else
        <a href="{{ route('register') }}" class="btn-g">Create Account to Refer →</a>
        <a href="{{ route('login') }}" class="btn-ln">Sign In</a>
      @endauth
    </div>
  </div>
</section>

{{-- REWARDS --}}
<section class="rewards">
  <div class="w">
    <div class="sec-label">The Deal</div>
    <h2 class="sec-h">Two-sided rewards</h2>
    <p class="sec-sub">You both win when a referral converts to a paid subscription.</p>
    <div class="reward-grid">
      <div class="reward-card primary">
        <div class="rc-badge you">YOU EARN</div>
        <div class="rc-eyebrow gold">Account Credit</div>
        <div class="rc-amount gold">$25</div>
        <div class="rc-unit">per paid conversion</div>
        <div class="rc-desc">Applied automatically to your UNIT subscription balance the moment your referral activates their first paid plan. No minimum, no cap — refer 10 teams, earn $250.</div>
      </div>
      <div class="reward-card secondary">
        <div class="rc-badge them">THEY GET</div>
        <div class="rc-eyebrow blue">Bonus Trial Transactions</div>
        <div class="rc-amount blue">+10</div>
        <div class="rc-unit">free trial transactions</div>
        <div class="rc-desc">Any team that signs up via your link gets 10 extra transactions on top of the standard 25 — giving them 35 free jobs to test UNIT before paying anything.</div>
      </div>
    </div>

    {{-- Creator strip --}}
    <div class="creator-strip">
      <div class="cs-text">
        <h3>Are you a content creator or consultant?</h3>
        <p>Our Influencer Program offers 20–30% recurring MRR commission — built for people who regularly publish to audiences of compliance and renewal professionals.</p>
      </div>
      <a href="{{ route('influencer.apply') }}" class="btn-blue">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Influencer Program →
      </a>
    </div>
  </div>
</section>

{{-- HOW IT WORKS --}}
<section class="how">
  <div class="w">
    <div class="sec-label">How It Works</div>
    <h2 class="sec-h">Four steps, fully automatic</h2>
    <p class="sec-sub">Once your link is shared, tracking and crediting happen automatically.</p>
    <div class="how-steps">
      <div class="how-step">
        <div class="hs-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span class="hs-num">1</span>
        </div>
        <div class="hs-title">Create an account</div>
        <div class="hs-desc">Sign up free — your unique referral link is generated automatically in your dashboard.</div>
      </div>
      <div class="how-step">
        <div class="hs-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
            <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
          </svg>
          <span class="hs-num">2</span>
        </div>
        <div class="hs-title">Share your link</div>
        <div class="hs-desc">Copy your unique URL from the dashboard. Share it in email, Slack, or anywhere your network lives.</div>
      </div>
      <div class="how-step">
        <div class="hs-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
          </svg>
          <span class="hs-num">3</span>
        </div>
        <div class="hs-title">They sign up + test</div>
        <div class="hs-desc">They get 35 free transactions (25 standard + 10 bonus). We track the referral from click to conversion.</div>
      </div>
      <div class="how-step">
        <div class="hs-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
          </svg>
          <span class="hs-num">4</span>
        </div>
        <div class="hs-title">Credit hits your account</div>
        <div class="hs-desc">The moment they activate a paid subscription, $25 is applied to your UNIT credit balance automatically.</div>
      </div>
    </div>
  </div>
</section>

{{-- MILESTONE TIERS --}}
<section class="tiers">
  <div class="w">
    <div class="sec-label">Milestones</div>
    <h2 class="sec-h">More referrals, more recognition</h2>
    <p class="sec-sub">As your referral count grows, you unlock milestones — and early access to new workers and features.</p>
    <div class="tier-grid">
      <div class="tier-card">
        <div class="tier-icon">🌱</div>
        <div class="tier-name">First Referral</div>
        <div class="tier-req">1 conversion</div>
        <div class="tier-reward">$25</div>
        <div class="tier-reward-label">account credit</div>
      </div>
      <div class="tier-card" style="border-color:rgba(205,127,50,0.3)">
        <div class="tier-icon">🥉</div>
        <div class="tier-name">Bronze</div>
        <div class="tier-req">3 conversions</div>
        <div class="tier-reward">$75</div>
        <div class="tier-reward-label">total earned</div>
      </div>
      <div class="tier-card" style="border-color:rgba(192,192,192,0.3)">
        <div class="tier-icon">🥈</div>
        <div class="tier-name">Silver</div>
        <div class="tier-req">5 conversions</div>
        <div class="tier-reward">$125</div>
        <div class="tier-reward-label">total earned</div>
      </div>
      <div class="tier-card" style="border-color:rgba(243,197,49,0.35)">
        <div class="tier-icon">🏆</div>
        <div class="tier-name">Gold</div>
        <div class="tier-req">10+ conversions</div>
        <div class="tier-reward">$250+</div>
        <div class="tier-reward-label">total earned</div>
      </div>
    </div>
    <p style="text-align:center;font-size:12.5px;color:var(--t4);margin-top:20px">Gold members get early access to new workers, feature previews, and priority support.</p>
  </div>
</section>

{{-- FAQ --}}
<section class="faq">
  <div class="w">
    <div class="sec-label">FAQ</div>
    <h2 class="sec-h">Common questions</h2>
    <div class="faq-list">
      @foreach([
        ['q'=>'When does my $25 credit get applied?','a'=>'Your credit is applied automatically the moment your referred user activates a paid UNIT subscription. You\'ll see it reflected in your billing dashboard immediately.'],
        ['q'=>'Is there a limit on how many teams I can refer?','a'=>'No limit at all. Refer 1 team or 100 — every paid conversion earns you $25 in account credit. There\'s no cap.'],
        ['q'=>'What counts as a "conversion"?','a'=>'A conversion happens when the person you referred activates any paid UNIT subscription. Free trial sign-ups don\'t count — they need to subscribe to a paid plan.'],
        ['q'=>'Where do I find my referral link?','a'=>'Log in to your UNIT dashboard. Your unique referral link is shown on the main dashboard page and on your account settings page. Copy it and share it anywhere.'],
        ['q'=>'Does my referral get the bonus even if they forget to use my link?','a'=>'The bonus is tied to your referral link — they need to sign up through your unique URL to receive the 10 extra trial transactions. You\'ll also only earn credit for link-attributed sign-ups.'],
        ['q'=>'Can I use my own referral link to sign up?','a'=>'No — self-referral isn\'t eligible. The system automatically skips credit if the referrer and referee are the same account.'],
        ['q'=>'What\'s the difference between the referral program and the influencer program?','a'=>'The referral program is for existing UNIT tenants who want to earn account credits by recommending UNIT to other teams. The influencer program is for content creators and consultants who promote UNIT to their audience and earn a recurring MRR commission (20–30%). You can learn more on the Influencer Program page.'],
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

{{-- CTA --}}
<section class="cta">
  <div class="w">
    <div class="sec-label" style="text-align:center">Start referring</div>
    <h2>Your link is already waiting.</h2>
    <p>Sign in to grab your referral URL — it takes 10 seconds and you're ready to start earning.</p>
    <div class="cta-btns">
      @auth
        <a href="{{ route('dashboard') }}" class="btn-g" style="padding:13px 28px;font-size:15px">Go to Dashboard →</a>
      @else
        <a href="{{ route('register') }}" class="btn-g" style="padding:13px 28px;font-size:15px">Create Free Account →</a>
        <a href="{{ route('login') }}" class="btn-ln" style="padding:13px 20px;font-size:14px">Sign In</a>
      @endauth
    </div>
  </div>
</section>

<footer>
  <div>© {{ date('Y') }} UNIT &nbsp;·&nbsp;
    <a href="/">Home</a> &nbsp;·&nbsp;
    <a href="{{ route('influencer.apply') }}">Influencer Program</a> &nbsp;·&nbsp;
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
</script>
</body>
</html>

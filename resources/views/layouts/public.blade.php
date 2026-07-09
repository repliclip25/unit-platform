<!DOCTYPE html>
<html lang="en" id="html-root" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'UNIT') — Purpose-Built AI Workers</title>
<meta name="description" content="@yield('description', 'UNIT is a platform for deploying purpose-built AI workers — each one trained for a specific workflow, ready to run on your team.')">
<link rel="icon" type="image/png" href="/logo.png">
<script>(function(){var t=localStorage.getItem('unit-theme-v2')||'dark';document.getElementById('html-root').setAttribute('data-theme',t)})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --gold:#142C74;--gold-d:#0e2260;--glow:rgba(20,44,116,0.22);--gold-text:#ffffff;
  --accent:var(--gold);--accent-rgb:20,44,116;
  --green:#22c55e;--green-bg:rgba(34,197,94,0.1);--green-border:rgba(34,197,94,0.25);
  --text:#ffffff;--t2:#cccccc;--t3:#999999;--t4:#555555;
  --line:rgba(255,255,255,0.12);--line2:rgba(255,255,255,0.18);
  --surf:#111111;--raised:#1a1a1a;--card:#1c1c1c;
  --fd:'Space Grotesk','Inter',sans-serif;--fb:'Inter',sans-serif;
  --bg:#080810;
}
[data-theme="light"]{
  --gold-text:var(--gold);
  --text:#000000;--t2:#1a1a1a;--t3:#555555;--t4:#999999;
  --line:#e2e2e0;--line2:#cccccc;
  --surf:#f0f0ee;--raised:#e6e6e4;--card:#ffffff;
  --bg:#ffffff;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:var(--fb);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
.w{max-width:1200px;margin:0 auto;padding:0 48px}
.w-md{max-width:900px;margin:0 auto;padding:0 40px}
.w-lg{max-width:1200px;margin:0 auto;padding:0 48px}

/* ── NAV (exact match to homepage) ── */
header#nav{position:sticky;top:0;left:0;right:0;z-index:300;transition:background .3s,backdrop-filter .3s;background:rgba(8,8,16,0.92);backdrop-filter:blur(20px);border-bottom:1px solid var(--line)}
.ni{display:flex;align-items:center;justify-content:space-between;padding:18px 48px;max-width:1200px;margin:0 auto}
.brand{display:flex;align-items:center;gap:10px;font-family:var(--fd);font-weight:800;font-size:18px;color:var(--text)}
.brand img{width:30px;height:30px;border-radius:7px}
.nl{display:flex;gap:28px}
.nl a{font-size:14px;color:rgba(255,255,255,0.45);transition:color .15s;font-weight:500}
.nl a:hover,.nl a.active{color:#fff}
.nr{display:flex;align-items:center;gap:14px}
.btn-g{display:inline-flex;align-items:center;gap:7px;background:var(--gold);color:#ffffff;font-weight:700;font-size:14px;padding:10px 22px;border-radius:8px;border:none;cursor:pointer;transition:transform .15s,box-shadow .2s;white-space:nowrap;font-family:var(--fb)}
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

/* ── SHARED COMPONENTS ── */
.eyebrow{display:inline-flex;align-items:center;gap:9px;border:1px solid rgba(20,44,116,0.28);background:rgba(20,44,116,0.06);color:var(--gold);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:7px 15px;border-radius:100px;margin-bottom:28px}
.slabel{font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--gold-text);margin-bottom:12px}
.sh2{font-family:var(--fd);font-size:44px;font-weight:800;letter-spacing:-1.5px;line-height:1.06;margin-bottom:14px}
.ssub{font-size:16px;line-height:1.65;color:var(--t2)}
.sec{padding:90px 0}
.sec-dark{background:rgba(4,4,10,0.55);border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
.sh{text-align:center;max-width:640px;margin:0 auto 56px}
.pub-divider{height:1px;background:var(--line);margin:40px 0}

/* ── FOOTER (exact match to homepage — always dark) ── */
footer{background:rgba(4,4,10,0.99);border-top:1px solid rgba(255,255,255,0.08);padding:52px 0 28px}
.foot-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr;gap:36px;margin-bottom:40px}
.foot-brand{font-size:13px;color:#777777;line-height:1.65;margin-top:10px;max-width:200px}
.foot-col h4{font-size:10.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#555555;margin-bottom:14px}
.foot-col a{display:block;font-size:13.5px;color:#888888;margin-bottom:9px;transition:color .15s}
.foot-col a:hover{color:#ffffff}
.foot-btm{display:flex;justify-content:space-between;padding-top:20px;border-top:1px solid rgba(255,255,255,0.08);font-size:12px;color:#555555}
.foot-brand-name{color:#ffffff!important;font-family:var(--fd);font-weight:800;font-size:18px}

/* ── PAGE BODY DEFAULTS ── */
.pub-hero{padding:72px 0 56px;border-bottom:1px solid var(--line)}
.pub-hero h1{font-family:var(--fd);font-size:clamp(30px,4vw,48px);font-weight:800;letter-spacing:-1.5px;line-height:1.1;margin-bottom:16px}
.pub-hero p{font-size:17px;color:var(--t3);max-width:580px;line-height:1.7;margin-top:6px}
.pub-body{padding:56px 0 80px}
.pub-body h2{font-family:var(--fd);font-size:22px;font-weight:800;margin:44px 0 12px;color:var(--text)}
.pub-body h2:first-child{margin-top:0}
.pub-body p{font-size:15px;color:var(--t2);line-height:1.82;margin-bottom:18px}
.pub-body ul{padding-left:20px;margin-bottom:18px}
.pub-body li{font-size:15px;color:var(--t2);line-height:1.8;margin-bottom:7px}
.pub-body a{color:var(--gold-text);text-decoration:underline;text-underline-offset:3px}
.pub-body strong{color:var(--text);font-weight:700}
.pub-meta{font-size:12px;color:var(--t4);margin-top:8px}

/* ── LIGHT THEME OVERRIDES ── */
[data-theme="light"] header#nav{background:rgba(249,249,247,0.97)!important;border-bottom:1px solid #e2e2e0!important}
[data-theme="light"] .nl a{color:#555555!important}
[data-theme="light"] .nl a:hover,[data-theme="light"] .nl a.active{color:#000000!important}
[data-theme="light"] .brand{color:#000000!important}
[data-theme="light"] .btn-ln{color:#444444!important;border-color:#cccccc!important}
[data-theme="light"] .btn-ln:hover{color:var(--gold)!important;border-color:var(--gold)!important}
[data-theme="light"] .slabel{color:var(--gold)!important}
[data-theme="light"] .sh2{color:#000000!important}
[data-theme="light"] .ssub{color:#555555!important}
[data-theme="light"] .sec-dark{background:#f0f0ee!important;border-color:#e2e2e0!important}
footer{background:rgba(4,4,10,0.99)!important;color:#aaaaaa!important}

/* ── RESPONSIVE ── */
@media(max-width:900px){
  .foot-grid{grid-template-columns:1fr 1fr}
  .sh2{font-size:34px}
}
@media(max-width:640px){
  .ni{padding:16px 20px}
  .w{padding:0 20px}
  .w-md{padding:0 20px}
  .w-lg{padding:0 20px}
  .nl{display:none}
  .nr .btn-ln{display:none}
  .foot-grid{grid-template-columns:1fr}
}
</style>
@yield('head')
</head>
<body>

{{-- ── NAV (matches homepage exactly) ── --}}
<header id="nav">
  <div class="ni">
    <a href="/" class="brand"><img src="/logo.png" alt="UNIT"><span>UNIT</span></a>
    <nav class="nl">
      <a href="/#workers">Workers</a>
      <a href="{{ route('marketplace') }}" class="{{ request()->routeIs('marketplace') ? 'active' : '' }}">Marketplace</a>
      <a href="{{ route('pricing') }}" class="{{ request()->routeIs('pricing') ? 'active' : '' }}">Pricing</a>
      <a href="{{ route('blog') }}" class="{{ request()->routeIs('blog*') ? 'active' : '' }}">Blog</a>
      <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">Company</a>
    </nav>
    <div class="nr">
      <button class="tog" id="pub-tog"></button>
      @auth
        <a href="{{ route('dashboard') }}" class="btn-g">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="btn-gh">Sign In</a>
        <a href="{{ route('register') }}" class="btn-g">Get Started Free</a>
      @endauth
    </div>
  </div>
</header>

@yield('body')

{{-- ── FOOTER (matches homepage exactly — always dark) ── --}}
<footer>
  <div class="w">
    <div class="foot-grid">
      <div>
        <a href="/" style="display:inline-flex;align-items:center;gap:10px;margin-bottom:4px">
          <img src="/logo.png" alt="UNIT" style="width:26px;height:26px;border-radius:6px">
          <span class="foot-brand-name">UNIT</span>
        </a>
        <p class="foot-brand">A platform for deploying purpose-built AI workers. Each worker is trained for a specific workflow and runs on your team.</p>
      </div>
      <div class="foot-col">
        <h4>Workers</h4>
        <a href="/w/ava">AVA — Renewal Coordinator</a>
        <a href="{{ route('marketplace') }}">All Workers</a>
        <a href="/referral">Refer &amp; Earn</a>
        <a href="{{ route('influencer.apply') }}">Partner Program</a>
      </div>
      <div class="foot-col">
        <h4>Product</h4>
        <a href="/#how">How It Works</a>
        <a href="{{ route('marketplace') }}">Marketplace</a>
        <a href="{{ route('register') }}">Sign Up Free</a>
      </div>
      <div class="foot-col">
        <h4>Company</h4>
        <a href="{{ route('about') }}">About Us</a>
        <a href="{{ route('blog') }}">Blog</a>
        <a href="{{ route('influencer.apply') }}">Become a Partner</a>
      </div>
      <div class="foot-col">
        <h4>Legal</h4>
        <a href="{{ route('privacy') }}">Privacy Policy</a>
        <a href="{{ route('terms') }}">Terms of Use</a>
      </div>
    </div>
    <div class="foot-btm">
      <span>© {{ date('Y') }} UNIT. All rights reserved.</span>
      <span><a href="{{ route('privacy') }}" style="color:inherit">Privacy</a> · <a href="{{ route('terms') }}" style="color:inherit">Terms</a></span>
    </div>
  </div>
</footer>

<script>
document.getElementById('pub-tog').addEventListener('click', function(){
  var h = document.getElementById('html-root');
  var t = h.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  h.setAttribute('data-theme', t);
  localStorage.setItem('unit-theme-v2', t);
});
</script>
@yield('scripts')
</body>
</html>

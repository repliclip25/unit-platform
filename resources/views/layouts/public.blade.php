<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'UNIT') — Purpose-Built AI Workers</title>
<meta name="description" content="@yield('description', 'UNIT is a platform for deploying purpose-built AI workers — each one trained for a specific workflow, ready to run on your team.')">
<link rel="icon" type="image/png" href="/logo.png">
<script>(function(){var t=localStorage.getItem('unit-theme');if(t)document.getElementById('html-root').setAttribute('data-theme',t)})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --gold:#F5C518;--gold-d:#d9ad12;--glow:rgba(245,197,24,.28);--gold-text:#8a6a06;
  --accent:var(--gold);--accent-rgb:245,197,24;
  --green:#22c55e;--green-bg:rgba(34,197,94,0.1);--green-border:rgba(34,197,94,0.25);
  --text:#0D0D0D;--t2:#374151;--t3:#6B7280;--t4:#9CA3AF;
  --line:#E5E7EB;--line2:#d8dade;
  --surf:#F8F8F6;--raised:#F1F1EF;--card:#ffffff;
  --fd:'Inter',sans-serif;--fb:'Inter',sans-serif;
  --bg:#FFFFFF;
}
[data-theme="dark"]{
  --gold-text:#F5C518;
  --text:#ffffff;--t2:#cccccc;--t3:#999999;--t4:#555555;
  --line:rgba(255,255,255,0.12);--line2:rgba(255,255,255,0.18);
  --surf:#111111;--raised:#1a1a1a;--card:#1c1c1c;
  --bg:#080810;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:var(--fb);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
button{cursor:pointer;font-family:inherit}
.w{max-width:1200px;margin:0 auto;padding:0 48px}
.w-md{max-width:900px;margin:0 auto;padding:0 40px}
.w-lg{max-width:1200px;margin:0 auto;padding:0 48px}

/* ── NAV — same visual system as / and /workers, but position:sticky (not
   fixed) so page content doesn't need extra top-padding to compensate ── */
.nav{position:sticky;top:0;left:0;right:0;z-index:100;background:rgba(255,255,255,.92);backdrop-filter:blur(16px);border-bottom:1px solid var(--line)}
[data-theme="dark"] .nav{background:rgba(8,8,16,.92);border-color:var(--line)}
.nav-i{display:flex;align-items:center;justify-content:space-between;height:62px;max-width:1200px;margin:0 auto;padding:0 48px}
.logo{display:flex;align-items:center}
.logo-name{font-family:var(--fd);font-size:1.5rem;font-weight:800;color:var(--text);letter-spacing:-.5px}
.nav-links{display:flex;align-items:center;gap:28px;list-style:none}
.nav-links a{font-size:14px;font-weight:500;color:var(--t2);transition:color .15s}
.nav-links a:hover{color:var(--text)}
.nav-links a.active{font-weight:700;color:var(--text)}
.nav-acts{display:flex;align-items:center;gap:10px}
.btn-login{padding:8px 18px;border-radius:8px;font-size:14px;font-weight:600;color:var(--t2);border:1px solid var(--line);transition:all .15s}
.btn-login:hover{border-color:var(--t4);color:var(--text)}
.btn-cta{padding:10px 22px;border-radius:99px;font-size:14px;font-weight:700;background:#0D0D0D;color:#fff;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 12px rgba(0,0,0,.12);transition:opacity .15s,transform .15s}
.btn-cta:hover{opacity:.9;transform:translateY(-1px)}
[data-theme="dark"] .btn-cta{background:#fff;color:#0D0D0D}
.ham{display:none;flex-direction:column;gap:5px;padding:4px;background:none;border:none}
.ham span{display:block;width:22px;height:2px;background:var(--text);border-radius:2px}

/* theme toggle — identical to / and /workers */
.theme-toggle{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid var(--line);background:transparent;color:var(--t2);cursor:pointer;transition:all .2s;flex-shrink:0}
.theme-toggle:hover{background:var(--surf);color:var(--text)}
.theme-toggle svg{width:17px;height:17px}
.icon-sun{display:none}.icon-moon{display:block}
[data-theme="dark"] .icon-sun{display:block}[data-theme="dark"] .icon-moon{display:none}

/* mobile menu — identical to / and /workers */
.mob-menu{display:none;position:fixed;inset:0;z-index:200;background:#fff;flex-direction:column;padding:24px clamp(20px,5vw,48px)}
[data-theme="dark"] .mob-menu{background:#080810}
.mob-menu.open{display:flex}
.mob-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:36px}
.mob-close{font-size:22px;color:var(--t3);padding:4px;background:none;border:none}
.mob-links{display:flex;flex-direction:column;list-style:none}
.mob-links a{display:block;padding:14px 0;font-size:1.05rem;font-weight:600;color:var(--t2);border-bottom:1px solid var(--line)}
.mob-ctas{margin-top:28px;display:flex;flex-direction:column;gap:10px}

/* ── SHARED CONTENT COMPONENTS (used directly by page bodies) ── */
.eyebrow{display:inline-flex;align-items:center;gap:9px;border:1px solid rgba(245,197,24,.35);background:rgba(245,197,24,.08);color:var(--gold-text);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:7px 15px;border-radius:100px;margin-bottom:28px}
.slabel{font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--gold-text);margin-bottom:12px}
.sh2{font-family:var(--fd);font-size:44px;font-weight:800;letter-spacing:-1.5px;line-height:1.06;margin-bottom:14px;color:var(--text)}
.ssub{font-size:16px;line-height:1.65;color:var(--t2)}
.sec{padding:90px 0}
.sec-dark{background:var(--surf);border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
.sh{text-align:center;max-width:640px;margin:0 auto 56px}
.pub-divider{height:1px;background:var(--line);margin:40px 0}

.btn-g{display:inline-flex;align-items:center;gap:7px;background:var(--gold);color:#0D0D0D;font-weight:700;font-size:14px;padding:10px 22px;border-radius:8px;border:none;cursor:pointer;transition:transform .15s,box-shadow .2s;white-space:nowrap;font-family:var(--fb)}
.btn-g:hover{transform:translateY(-1px);box-shadow:0 8px 32px var(--glow)}
.btn-gh{font-size:14px;color:var(--t4);cursor:pointer;border:none;background:none;font-family:var(--fb);transition:color .15s}
.btn-gh:hover{color:var(--text)}
.btn-ln{display:inline-flex;align-items:center;gap:7px;font-size:14px;font-weight:600;padding:10px 20px;border-radius:8px;border:1px solid var(--line2);color:var(--t2);cursor:pointer;background:none;transition:border-color .15s,color .15s;font-family:var(--fb)}
.btn-ln:hover{border-color:var(--gold);color:var(--gold-text)}

/* ── FOOTER — identical to / and /workers ── */
footer.footer{background:#0A0A0A;padding:clamp(40px,6vw,72px) 0 28px}
.ft-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:44px;margin-bottom:44px}
.ft-name{font-family:var(--fd);font-size:1.15rem;font-weight:800;color:#fff;margin-bottom:10px}
.ft-desc{font-size:13.5px;color:rgba(255,255,255,.6);line-height:1.7;max-width:220px;margin-bottom:20px}
.ft-col-h{font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:14px}
.ft-links{display:flex;flex-direction:column;gap:9px}
.ft-links a{font-size:13.5px;color:rgba(255,255,255,.7);transition:color .15s}
.ft-links a:hover{color:#fff}
.ft-bottom{border-top:1px solid rgba(255,255,255,.12);padding-top:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
.ft-bottom p{font-size:12.5px;color:rgba(255,255,255,.45)}

/* ── PAGE BODY DEFAULTS ── */
.pub-hero{padding:72px 0 56px;border-bottom:1px solid var(--line)}
.pub-hero h1{font-family:var(--fd);font-size:clamp(30px,4vw,48px);font-weight:800;letter-spacing:-1.5px;line-height:1.1;margin-bottom:16px;color:var(--text)}
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

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .ft-grid{grid-template-columns:1fr 1fr;gap:28px}
}
@media(max-width:768px){
  .nav-links,.nav-acts{display:none}
  .ham{display:flex}
}
@media(max-width:900px){
  .sh2{font-size:34px}
}
@media(max-width:640px){
  .nav-i{padding:0 20px}
  .w{padding:0 20px}
  .w-md{padding:0 20px}
  .w-lg{padding:0 20px}
  .ft-grid{grid-template-columns:1fr}
  .ft-bottom{flex-direction:column;text-align:center}
}
</style>
@yield('head')
</head>
<body>

{{-- ── NAV — identical markup to / and /workers ── --}}
<nav class="nav">
  <div class="w nav-i">
    <a href="{{ url('/') }}" class="logo"><span class="logo-name">UNIT</span></a>
    <ul class="nav-links">
      <li><a href="{{ route('public.workers.index') }}" class="{{ request()->routeIs('public.workers.index') ? 'active' : '' }}">Meet the Workers</a></li>
      <li><a href="{{ route('marketplace') }}" class="{{ request()->routeIs('marketplace') ? 'active' : '' }}">Marketplace</a></li>
      <li><a href="{{ route('pricing') }}" class="{{ request()->routeIs('pricing') ? 'active' : '' }}">Pricing</a></li>
      <li><a href="{{ route('blog') }}" class="{{ request()->routeIs('blog*') ? 'active' : '' }}">Blog</a></li>
      <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">Company</a></li>
    </ul>
    <div class="nav-acts">
      <button class="theme-toggle" id="theme-toggle" aria-label="Toggle theme">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      @auth
        <a href="{{ route('app.dashboard') }}" class="btn-login">Dashboard</a>
      @else
        <a href="{{ route('login') }}" class="btn-login">Log in</a>
        <a href="{{ route('register') }}" class="btn-cta">Get Started Free</a>
      @endauth
    </div>
    <button class="ham" id="ham" aria-label="Menu"><span></span><span></span><span></span></button>
  </div>
</nav>

{{-- ── MOBILE MENU — identical markup to / and /workers ── --}}
<div class="mob-menu" id="mob">
  <div class="mob-top">
    <a href="{{ url('/') }}" class="logo"><span class="logo-name">UNIT</span></a>
    <button class="mob-close" id="mob-close">✕</button>
  </div>
  <ul class="mob-links">
    <li><a href="{{ route('public.workers.index') }}" onclick="closeMob()">Meet the Workers</a></li>
    <li><a href="{{ route('marketplace') }}" onclick="closeMob()">Marketplace</a></li>
    <li><a href="{{ route('pricing') }}" onclick="closeMob()">Pricing</a></li>
    <li><a href="{{ route('blog') }}" onclick="closeMob()">Blog</a></li>
    <li><a href="{{ route('about') }}" onclick="closeMob()">Company</a></li>
  </ul>
  <div class="mob-ctas">
    @auth
      <a href="{{ route('app.dashboard') }}" class="btn-login" style="text-align:center">Dashboard</a>
    @else
      <a href="{{ route('login') }}" class="btn-login" style="text-align:center">Log in</a>
      <a href="{{ route('register') }}" class="btn-cta" style="justify-content:center">Get Started Free</a>
    @endauth
  </div>
</div>

@yield('body')

{{-- ── FOOTER — identical markup to / and /workers ── --}}
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
          <a href="{{ route('public.workers.index') }}">All Workers</a>
          <a href="{{ route('referral.index') }}">Refer &amp; Earn</a>
          <a href="{{ route('influencer.apply') }}">Partner Program</a>
        </div>
      </div>
      <div>
        <div class="ft-col-h">Platform</div>
        <div class="ft-links">
          <a href="{{ route('pricing') }}">Pricing</a>
          <a href="{{ route('marketplace') }}">Marketplace</a>
          <a href="{{ route('register') }}">Sign Up Free</a>
          <a href="{{ route('login') }}">Log In</a>
        </div>
      </div>
      <div>
        <div class="ft-col-h">Company</div>
        <div class="ft-links">
          <a href="{{ route('about') }}">About Us</a>
          <a href="{{ route('blog') }}">Blog</a>
          <a href="{{ route('privacy') }}">Privacy Policy</a>
          <a href="{{ route('terms') }}">Terms of Use</a>
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
// ── Theme — identical key/logic to / and /workers ──
const root = document.getElementById('html-root');
const saved = localStorage.getItem('unit-theme');
if (saved) root.setAttribute('data-theme', saved);
document.getElementById('theme-toggle').addEventListener('click', function () {
  const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  root.setAttribute('data-theme', next);
  localStorage.setItem('unit-theme', next);
});

// ── Mobile menu ──
const ham = document.getElementById('ham');
const mob = document.getElementById('mob');
ham.addEventListener('click', () => mob.classList.add('open'));
document.getElementById('mob-close').addEventListener('click', () => mob.classList.remove('open'));
function closeMob() { mob.classList.remove('open'); }
</script>
@yield('scripts')
</body>
</html>

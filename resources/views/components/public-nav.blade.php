@props(['links' => []])
{{-- Single source of truth for the public-site header: markup, CSS, and JS
     all live here (nothing about nav appearance/behavior should be defined
     per-page anymore, that's exactly what caused the nav to drift between
     "position:fixed" on some pages and "position:sticky" on others, and
     "--border" vs "--line" custom property names, before this was
     consolidated). Uses literal color values rather than page-level CSS
     variables so this renders identically no matter which page/design-token
     system includes it. The link set itself is passed in via $links so
     pages can differ in what they link to. --}}
@php
    $__deskRoutes = ['ava' => 'app.desk.ava']; // extend as more workers get real desks
    $__myDesks = [];
    if (auth()->check()) {
        $__deployments = \Illuminate\Support\Facades\DB::table('worker_deployments')
            ->where('user_id', auth()->id())
            ->whereIn('status', ['active', 'paused'])
            ->pluck('worker_slug')
            ->unique();
        foreach ($__deployments as $__slug) {
            if (isset($__deskRoutes[$__slug])) {
                $__myDesks[] = ['slug' => $__slug, 'route' => $__deskRoutes[$__slug]];
            }
        }
    }
@endphp

@once
<style>
.nav{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(255,255,255,.92);backdrop-filter:blur(16px);border-bottom:1px solid #E5E7EB}
[data-theme="dark"] .nav{background:rgba(13,13,13,.92);border-color:#2D2D2D}
.nav-i{display:flex;align-items:center;justify-content:space-between;height:62px;max-width:1200px;margin:0 auto;padding:0 clamp(20px,5vw,48px)}
.nav .logo{display:flex;align-items:center}
.nav .logo-name{font-size:1.25rem;font-weight:800;color:var(--text,#0D0D0D);letter-spacing:-.5px}
.nav-links{display:flex;align-items:center;gap:28px;list-style:none}
.nav-links a{font-size:13.5px;font-weight:500;color:#6B7280;transition:color .15s}
[data-theme="dark"] .nav-links a{color:#9CA3AF}
.nav-links a:hover{color:var(--text,#0D0D0D)}
[data-theme="dark"] .nav-links a:hover{color:#F3F4F6}
.nav-links a.active{font-weight:700;color:var(--text,#0D0D0D)}
[data-theme="dark"] .nav-links a.active{color:#F3F4F6}
.nav-acts{display:flex;align-items:center;gap:10px}
.btn-login{padding:8px 18px;border-radius:8px;font-size:13.5px;font-weight:600;color:#374151;border:1px solid #E5E7EB;transition:all .15s;white-space:nowrap}
[data-theme="dark"] .btn-login{color:#D1D5DB;border-color:#2D2D2D}
.btn-login:hover{border-color:#bbb}
.btn-cta{padding:9px 20px;border-radius:99px;font-size:13.5px;font-weight:700;background:#0D0D0D;color:#fff;display:inline-flex;align-items:center;gap:7px;box-shadow:0 2px 12px rgba(0,0,0,.12);transition:opacity .15s,transform .15s;white-space:nowrap}
[data-theme="dark"] .btn-cta{background:#fff;color:#0D0D0D}
.btn-cta:hover{opacity:.9;transform:translateY(-1px)}
.theme-toggle{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid #E5E7EB;background:transparent;color:#6B7280;cursor:pointer;transition:all .2s;flex-shrink:0}
[data-theme="dark"] .theme-toggle{border-color:#2D2D2D;color:#9CA3AF}
.theme-toggle:hover{background:#F8F8F6}
[data-theme="dark"] .theme-toggle:hover{background:#161616}
.icon-sun{display:none}.icon-moon{display:block}
[data-theme="dark"] .icon-sun{display:block}[data-theme="dark"] .icon-moon{display:none}
.theme-toggle svg{width:17px;height:17px}

/* account menu (dashboard / my desks / profile) */
.nav-account{position:relative}
.nav-account-menu{
  display:none;position:absolute;top:calc(100% + 10px);right:0;
  min-width:200px;background:#fff;border:1px solid #E5E7EB;border-radius:14px;
  box-shadow:0 12px 32px rgba(0,0,0,.12);padding:8px;
}
[data-theme="dark"] .nav-account-menu{background:#161616;border-color:#2D2D2D}
.nav-account-menu.open{display:block}
.nav-account-menu a{display:block;padding:9px 12px;border-radius:8px;font-size:13.5px;font-weight:600;color:#374151}
[data-theme="dark"] .nav-account-menu a{color:#D1D5DB}
.nav-account-menu a:hover{background:#F8F8F6}
[data-theme="dark"] .nav-account-menu a:hover{background:#1a1a1a}

.ham{display:none;flex-direction:column;gap:5px;padding:4px;background:none;border:none}
.ham span{display:block;width:22px;height:2px;background:var(--text,#0D0D0D);border-radius:2px}

.mob-menu{display:none;position:fixed;inset:0;z-index:200;background:#fff;flex-direction:column;padding:24px clamp(20px,5vw,48px);overflow-y:auto}
[data-theme="dark"] .mob-menu{background:#0D0D0D}
.mob-menu.open{display:flex}
.mob-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:36px}
.mob-close{font-size:22px;color:#9CA3AF;padding:4px;background:none;border:none}
.mob-section-label{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#9CA3AF;margin:20px 0 4px}
.mob-section-label:first-child{margin-top:0}
.mob-links{display:flex;flex-direction:column;list-style:none}
.mob-links a{display:block;padding:14px 0;font-size:1.05rem;font-weight:600;color:#374151;border-bottom:1px solid #E5E7EB}
[data-theme="dark"] .mob-links a{color:#D1D5DB;border-color:#2D2D2D}
.mob-ctas{margin-top:28px;display:flex;flex-direction:column;gap:10px}

@media(max-width:960px){.nav-links,.nav-acts{display:none}.ham{display:flex}}
</style>
@endonce

<nav class="nav">
  <div class="w nav-i">
    <a href="{{ url('/') }}" class="logo"><span class="logo-name">UNIT</span></a>
    <ul class="nav-links">
      @foreach ($links as $link)
        <li><a href="{{ $link['href'] }}" class="{{ !empty($link['active']) ? 'active' : '' }}">{{ $link['label'] }}</a></li>
      @endforeach
    </ul>
    <div class="nav-acts">
      @auth
        <div class="nav-account">
          <button type="button" class="btn-login" id="navAccountBtn">Account</button>
          <div class="nav-account-menu" id="navAccountMenu">
            <a href="{{ route('app.dashboard') }}">Dashboard</a>
            @foreach($__myDesks as $desk)
              <a href="{{ route($desk['route']) }}">{{ strtoupper($desk['slug']) }}'s Desk</a>
            @endforeach
            <a href="{{ route('app.profile.show') }}">Profile</a>
          </div>
        </div>
      @else
        <a href="{{ route('login') }}" class="btn-login">Log in</a>
      @endauth
      @isset($cta)
        {{ $cta }}
      @else
        @guest
          <a href="{{ route('register') }}" class="btn-cta">Get Started Free</a>
        @endguest
      @endisset
      <button class="theme-toggle" id="theme-toggle" aria-label="Toggle theme">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
    </div>
    <button class="ham" id="ham" aria-label="Menu"><span></span><span></span><span></span></button>
  </div>
</nav>

<div class="mob-menu" id="mob">
  <div class="mob-top">
    <a href="{{ url('/') }}" class="logo"><span class="logo-name">UNIT</span></a>
    <button class="mob-close" id="mob-close">✕</button>
  </div>
  <div class="mob-section-label">Menu</div>
  <ul class="mob-links">
    @foreach ($links as $link)
      <li><a href="{{ $link['href'] }}" onclick="closeMob()">{{ $link['label'] }}</a></li>
    @endforeach
  </ul>
  @auth
    <div class="mob-section-label">Account</div>
    <ul class="mob-links">
      <li><a href="{{ route('app.dashboard') }}" onclick="closeMob()">Dashboard</a></li>
      @foreach($__myDesks as $desk)
        <li><a href="{{ route($desk['route']) }}" onclick="closeMob()">{{ strtoupper($desk['slug']) }}'s Desk</a></li>
      @endforeach
      <li><a href="{{ route('app.profile.show') }}" onclick="closeMob()">Profile</a></li>
    </ul>
  @endauth
  <div class="mob-ctas">
    @guest
      <a href="{{ route('login') }}" class="btn-login" style="text-align:center">Log in</a>
      @isset($mobileCta)
        {{ $mobileCta }}
      @else
        <a href="{{ route('register') }}" class="btn-cta" style="justify-content:center">Get Started Free</a>
      @endisset
    @endguest
  </div>
</div>

@once
<script>
(function(){
  // ── Theme ──
  var root = document.documentElement;
  var saved = localStorage.getItem('unit-theme');
  if(saved) root.setAttribute('data-theme', saved);
  var themeBtn = document.getElementById('theme-toggle');
  if(themeBtn){
    themeBtn.addEventListener('click', function(){
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      localStorage.setItem('unit-theme', next);
    });
  }

  // ── Mobile menu ──
  var ham = document.getElementById('ham');
  var mob = document.getElementById('mob');
  var mobClose = document.getElementById('mob-close');
  if(ham && mob) ham.addEventListener('click', function(){ mob.classList.add('open'); });
  if(mobClose && mob) mobClose.addEventListener('click', function(){ mob.classList.remove('open'); });
  window.closeMob = function(){ if(mob) mob.classList.remove('open'); };

  // ── Account dropdown ──
  var acctBtn = document.getElementById('navAccountBtn');
  var acctMenu = document.getElementById('navAccountMenu');
  if(acctBtn && acctMenu){
    acctBtn.addEventListener('click', function(e){
      e.stopPropagation();
      acctMenu.classList.toggle('open');
    });
    document.addEventListener('click', function(){ acctMenu.classList.remove('open'); });
  }
})();
</script>
@endonce

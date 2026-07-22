@props(['links' => []])
{{-- Single source of truth for the public-site nav chrome (logo, theme
     toggle, auth buttons, hamburger + mobile menu). The link set itself is
     passed in via $links so pages can differ in what they link to (e.g. the
     homepage links to on-page anchors, other pages link to routes) while
     every page shares the exact same markup, ids, and JS hooks. Edit the
     shared parts here, not per-page. --}}
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
        <a href="{{ route('app.dashboard') }}" class="btn-login">Dashboard</a>
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
  <ul class="mob-links">
    @foreach ($links as $link)
      <li><a href="{{ $link['href'] }}" onclick="closeMob()">{{ $link['label'] }}</a></li>
    @endforeach
  </ul>
  <div class="mob-ctas">
    @auth
      <a href="{{ route('app.dashboard') }}" class="btn-login" style="text-align:center">Dashboard</a>
    @else
      <a href="{{ route('login') }}" class="btn-login" style="text-align:center">Log in</a>
    @endauth
    @isset($mobileCta)
      {{ $mobileCta }}
    @else
      @guest
        <a href="{{ route('register') }}" class="btn-cta" style="justify-content:center">Get Started Free</a>
      @endguest
    @endisset
  </div>
</div>

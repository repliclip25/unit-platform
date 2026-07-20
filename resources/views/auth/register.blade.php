<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Get started — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased;display:flex;flex-direction:column;min-height:100vh}

:root,[data-theme="dark"]{
  --db-bg:#0D0D0D; --db-card:#1A1A1A; --db-text:#F5F5F5; --db-text-muted:#9CA3AF;
  --db-border:rgba(255,255,255,.14); --db-chip:#262626;
  --db-invert-bg:#F5F5F5; --db-invert-text:#0D0D0D;
}
[data-theme="light"]{
  --db-bg:#F4F3F1; --db-card:#ffffff; --db-text:#0D0D0D; --db-text-muted:#9CA3AF;
  --db-border:#E5E7EB; --db-chip:#ECEAE6;
  --db-invert-bg:#0D0D0D; --db-invert-text:#ffffff;
}
body{background:var(--db-bg);color:var(--db-text)}

.au-topbar{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;flex-shrink:0}
.au-logo{font-size:20px;font-weight:900;letter-spacing:-.04em;color:var(--db-text);text-decoration:none}
.au-theme-toggle{width:36px;height:20px;border-radius:10px;border:none;cursor:pointer;position:relative;background:var(--db-chip)}
.au-theme-toggle::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:var(--db-invert-bg);transition:transform .2s ease}
[data-theme="dark"] .au-theme-toggle::after{transform:translateX(16px)}

.au-wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:24px}
.au-card{width:100%;max-width:400px;background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;padding:32px 28px;box-shadow:0 2px 12px rgba(0,0,0,.06)}

.au-badge{display:inline-flex;align-items:center;gap:6px;font-size:9.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:5px 12px;border-radius:99px;background:var(--db-chip);color:var(--db-text-muted);margin-bottom:14px}
.au-badge-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}

.au-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);margin-bottom:4px;text-align:center}
.au-sub{font-size:13px;color:var(--db-text-muted);text-align:center;margin-bottom:24px}

.au-oauth{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;background:var(--db-chip);border:1px solid var(--db-border);border-radius:12px;padding:11px;font-size:13.5px;font-weight:600;color:var(--db-text);text-decoration:none;margin-bottom:18px}
.au-oauth:hover{background:var(--db-border)}

.au-divider{display:flex;align-items:center;gap:10px;margin-bottom:18px}
.au-divider hr{flex:1;border:none;border-top:1px solid var(--db-border)}
.au-divider span{font-size:11px;color:var(--db-text-muted)}

.au-label{display:block;font-size:11.5px;font-weight:600;color:var(--db-text-muted);margin-bottom:6px}
.au-field{margin-bottom:14px}
.au-input{width:100%;border-radius:10px;padding:11px 14px;font-size:13.5px;background:var(--db-bg);border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.au-input:focus{outline:none;border-color:var(--db-invert-bg)}
.au-input::placeholder{color:var(--db-text-muted)}
.au-error{color:#ef4444;font-size:11.5px;margin-top:5px}

.au-submit{width:100%;padding:12px;border-radius:10px;border:none;background:var(--db-invert-bg);color:var(--db-invert-text);font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;margin-top:4px}
.au-submit:hover{opacity:.9}

.au-terms{font-size:11px;color:var(--db-text-muted);text-align:center;margin-top:12px}

.au-footer{text-align:center;margin-top:20px;font-size:12.5px;color:var(--db-text-muted)}
.au-footer a{color:var(--db-text);font-weight:700;text-decoration:none}
.au-footer a:hover{text-decoration:underline}
</style>
<script>
(function () {
  var saved = localStorage.getItem('unit-theme-v2') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();
</script>
</head>
<body>

@php
  $workerIntent = request()->query('worker') ?: old('worker');
  // Current UNIT worker lineup — keep in sync with DeskController::STAGE_META catalog
  $workerMeta = [
    'ava' => ['label' => 'AVA', 'role' => 'Renewal Specialist'],
    'dox' => ['label' => 'DOX', 'role' => 'Document Specialist'],
    'mox' => ['label' => 'MOX', 'role' => 'Brand Moments Hunter'],
    'nux' => ['label' => 'NUX', 'role' => 'Publishing Specialist'],
  ];
  $wm = $workerIntent && isset($workerMeta[$workerIntent]) ? $workerMeta[$workerIntent] : null;
@endphp

<div class="au-topbar">
  <a href="{{ url('/') }}" class="au-logo">UNIT</a>
  <button type="button" class="au-theme-toggle" id="theme-toggle" title="Toggle dark/light mode" aria-label="Toggle theme"></button>
</div>

<div class="au-wrap">
  <div class="au-card">
    <div style="text-align:center">
      @if($wm)
        <div class="au-badge"><span class="au-badge-dot"></span> DEPLOYING {{ strtoupper($wm['label']) }}</div>
        <div class="au-h1">Set up {{ $wm['label'] }} in minutes</div>
        <div class="au-sub">{{ $wm['role'] }} — free to start, no card needed</div>
      @else
        <div class="au-badge"><span class="au-badge-dot"></span> AI WORKFORCE PLATFORM</div>
        <div class="au-h1">Hire your first worker</div>
        <div class="au-sub">Create your UNIT workspace — free to start</div>
      @endif
    </div>

    <a href="{{ route('oauth.redirect', 'google') }}{{ $workerIntent ? '?worker='.$workerIntent : '' }}{{ request()->query('ref') ? ($workerIntent ? '&' : '?').'ref='.request()->query('ref') : '' }}" class="au-oauth">
      <svg width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
      Continue with Google
    </a>

    <div class="au-divider"><hr><span>or register with email</span><hr></div>

    <form method="POST" action="{{ route('register') }}">
      @csrf
      <input type="hidden" name="worker" value="{{ $workerIntent }}">
      @if(request()->query('ref'))
        <input type="hidden" name="ref" value="{{ request()->query('ref') }}">
      @endif
      <div class="au-field">
        <label class="au-label" for="name">Full name</label>
        <input class="au-input" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Jane Smith">
        @error('name')<div class="au-error">{{ $message }}</div>@enderror
      </div>
      <div class="au-field">
        <label class="au-label" for="email">Work email</label>
        <input class="au-input" id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@company.com">
        @error('email')<div class="au-error">{{ $message }}</div>@enderror
      </div>
      <div class="au-field">
        <label class="au-label" for="password">Password</label>
        <input class="au-input" id="password" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 characters">
        @error('password')<div class="au-error">{{ $message }}</div>@enderror
      </div>
      <div class="au-field">
        <label class="au-label" for="password_confirmation">Confirm password</label>
        <input class="au-input" id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
        @error('password_confirmation')<div class="au-error">{{ $message }}</div>@enderror
      </div>
      <button type="submit" class="au-submit">Create workspace</button>
      <div class="au-terms">By registering you agree to our <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms of Service</a> and <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>.</div>
    </form>

    <div class="au-footer">Already have an account? <a href="{{ route('login') }}">Sign in →</a></div>
  </div>
</div>

<script>
document.getElementById('theme-toggle').addEventListener('click', function () {
  var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('unit-theme-v2', next);
});
</script>
</body>
</html>

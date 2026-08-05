<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Brand Memory presale - UNIT</title>
<link rel="icon" type="image/png" href="/favicon.png">
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

.au-label{display:block;font-size:11.5px;font-weight:600;color:var(--db-text-muted);margin-bottom:6px}
.au-field{margin-bottom:14px}
.au-input{width:100%;border-radius:10px;padding:11px 14px;font-size:13.5px;background:var(--db-bg);border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.au-input:focus{outline:none;border-color:var(--db-invert-bg)}
.au-input::placeholder{color:var(--db-text-muted)}
.au-error{color:#ef4444;font-size:11.5px;margin-top:5px}

.au-submit{width:100%;padding:12px;border-radius:10px;border:none;background:var(--db-invert-bg);color:var(--db-invert-text);font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;margin-top:4px}
.au-submit:hover{opacity:.9}

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

<div class="au-topbar">
  <a href="{{ url('/') }}" class="au-logo">UNIT</a>
  <button type="button" class="au-theme-toggle" id="theme-toggle" title="Toggle dark/light mode" aria-label="Toggle theme"></button>
</div>

<div class="au-wrap">
  <div class="au-card">
    <div style="text-align:center">
      <div class="au-badge"><span class="au-badge-dot"></span> EARLY ACCESS</div>
      <div class="au-h1">Start your Brand Memory</div>
      <div class="au-sub">Reserve your spot for the brand video worker and start training its memory now.</div>
    </div>

    <form method="POST" action="{{ route('presale.signup.store') }}">
      @csrf
      <div class="au-field">
        <label class="au-label" for="name">Full name</label>
        <input class="au-input" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Jane Smith">
        @error('name')<div class="au-error">{{ $message }}</div>@enderror
      </div>
      <div class="au-field">
        <label class="au-label" for="company">Company (optional)</label>
        <input class="au-input" id="company" type="text" name="company" value="{{ old('company') }}" autocomplete="organization" placeholder="Acme Inc.">
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
      <button type="submit" class="au-submit">Reserve my spot</button>
    </form>

    <div class="au-footer">Already have an account? <a href="{{ route('login') }}">Sign in</a></div>
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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Verify email — UNIT</title>
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
.au-card{width:100%;max-width:380px;background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;padding:32px 28px;box-shadow:0 2px 12px rgba(0,0,0,.06);text-align:center}

.au-icon{width:52px;height:52px;border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;background:var(--db-chip);border:1px solid var(--db-border)}
.au-h1{font-size:1.4rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);margin-bottom:6px}
.au-sub{font-size:13px;color:var(--db-text-muted);max-width:280px;margin:0 auto 22px;line-height:1.5}

.au-status{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#22c55e;border-radius:12px;padding:10px 14px;font-size:12.5px;margin-bottom:16px}

.au-submit{width:100%;padding:12px;border-radius:10px;border:none;background:var(--db-invert-bg);color:var(--db-invert-text);font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit}
.au-submit:hover{opacity:.9}

.au-signout{background:none;border:none;font-size:12px;font-weight:600;color:var(--db-text-muted);cursor:pointer;font-family:inherit;margin-top:14px}
.au-signout:hover{color:var(--db-text)}
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
    <div class="au-icon">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--db-text)" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg>
    </div>
    <div class="au-h1">Check your email</div>
    <div class="au-sub">We sent a verification link to your email address. Click the link to activate your account.</div>

    @if(session('status') == 'verification-link-sent')
      <div class="au-status">A new verification link has been sent to your email.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="au-submit">Resend Verification Email</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="au-signout">Sign out of this account</button>
    </form>
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

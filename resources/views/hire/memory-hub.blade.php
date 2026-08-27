<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Start training memory - UNITELO</title>
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
  --db-bg:#F4F3F1; --db-card:#ffffff; --db-text:#0D0D0D; --db-text-muted:#6B7280;
  --db-border:#E5E7EB; --db-chip:#ECEAE6;
  --db-invert-bg:#0D0D0D; --db-invert-text:#ffffff;
}
body{background:var(--db-bg);color:var(--db-text)}

.mh-topbar{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;flex-shrink:0}
.mh-logo{font-size:20px;font-weight:900;letter-spacing:-.04em;color:var(--db-text);text-decoration:none}
.mh-topbar-right{display:flex;align-items:center;gap:14px}
.mh-logout{font-size:12.5px;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit}
.mh-logout:hover{color:var(--db-text)}
.mh-theme-toggle{width:36px;height:20px;border-radius:10px;border:none;cursor:pointer;position:relative;background:var(--db-chip)}
.mh-theme-toggle::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:var(--db-invert-bg);transition:transform .2s ease}
[data-theme="dark"] .mh-theme-toggle::after{transform:translateX(16px)}

.mh-wrap{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 24px}
.mh-badge{display:inline-flex;align-items:center;gap:6px;font-size:9.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:5px 12px;border-radius:99px;background:var(--db-chip);color:var(--db-text-muted);margin-bottom:16px}
.mh-h1{font-size:clamp(1.6rem,4vw,2.1rem);font-weight:900;letter-spacing:-.04em;color:var(--db-text);text-align:center;margin-bottom:8px}
.mh-sub{font-size:14px;color:var(--db-text-muted);text-align:center;max-width:480px;margin-bottom:40px;line-height:1.6}

.mh-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;width:100%;max-width:760px}
.mh-card{background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;padding:28px;display:flex;flex-direction:column;text-align:left;box-shadow:0 2px 12px rgba(0,0,0,.06);text-decoration:none;color:inherit;transition:transform .15s,box-shadow .15s}
.mh-card:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(0,0,0,.12)}
.mh-icon{width:44px;height:44px;border-radius:12px;background:var(--db-chip);display:flex;align-items:center;justify-content:center;margin-bottom:16px}
.mh-icon svg{width:22px;height:22px;stroke:var(--db-text);stroke-width:1.6;fill:none}
.mh-card-badge{display:inline-block;align-self:flex-start;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:3px 10px;border-radius:99px;background:rgba(245,197,24,.15);color:#8a6a06;margin-bottom:10px}
.mh-card-name{font-size:1.25rem;font-weight:800;letter-spacing:-.03em;color:var(--db-text);margin-bottom:2px}
.mh-card-role{font-size:12px;font-weight:600;color:var(--db-text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:12px}
.mh-card-desc{font-size:13px;color:var(--db-text-muted);line-height:1.6;margin-bottom:16px;flex:1}
.mh-card-cta{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:var(--db-text)}
.mh-card-cta svg{width:13px;height:13px;stroke:currentColor;stroke-width:2.5;fill:none}

.mh-card-progress{margin-bottom:16px}
.mh-card-progress-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.mh-card-progress-label{font-size:10.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--db-text-muted)}
.mh-card-progress-pct{font-size:11.5px;font-weight:700;color:var(--db-text)}
.mh-card-progress-track{height:5px;border-radius:99px;background:var(--db-chip);overflow:hidden}
.mh-card-progress-fill{height:100%;border-radius:99px;background:#22c55e}

.mh-skip{margin-top:32px;font-size:12.5px;color:var(--db-text-muted);text-decoration:none}
.mh-skip:hover{color:var(--db-text)}

@media(max-width:640px){
  .mh-grid{grid-template-columns:1fr}
}
</style>
<script>
(function () {
  var saved = localStorage.getItem('unit-theme-v2') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();
</script>
</head>
<body>

<div class="mh-topbar">
  <a href="{{ url('/') }}" class="mh-logo">UNITELO</a>
  <div class="mh-topbar-right">
    <button type="button" class="mh-theme-toggle" id="theme-toggle" title="Toggle dark/light mode" aria-label="Toggle theme"></button>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="mh-logout">Logout</button></form>
  </div>
</div>

<div class="mh-wrap">
  <div class="mh-badge">Before you deploy</div>
  <div class="mh-h1">What should we start remembering?</div>
  <div class="mh-sub">Pick a worker to start training its memory. No setup, no Gmail connection, no commitment — just start feeding it what it needs to know.</div>

  <div class="mh-grid">
    @foreach ($classes as $slug => $class)
      <form method="POST" action="{{ route('hire.memory.start', $slug) }}">
        @csrf
        <button type="submit" class="mh-card" style="width:100%;border:1px solid var(--db-border);cursor:pointer;font-family:inherit">
          <div class="mh-icon"><svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $class['icon'] }}"/></svg></div>
          <span class="mh-card-badge">{{ $class['badge'] }}</span>
          <div class="mh-card-name">{{ $class['name'] }}</div>
          <div class="mh-card-role">{{ $class['role'] }}</div>
          <div class="mh-card-desc">{{ $class['desc'] }}</div>
          <div class="mh-card-progress">
            <div class="mh-card-progress-top">
              <span class="mh-card-progress-label">Memory coverage</span>
              <span class="mh-card-progress-pct">{{ $class['coverage']['score'] }}%</span>
            </div>
            <div class="mh-card-progress-track"><div class="mh-card-progress-fill" style="width:{{ $class['coverage']['score'] }}%"></div></div>
          </div>
          <span class="mh-card-cta">{{ $class['coverage']['score'] > 0 ? 'Continue training' : 'Start training' }} <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
        </button>
      </form>
    @endforeach
  </div>

  <a href="{{ route('hire.ava.welcome') }}" class="mh-skip">Skip — I'm ready to deploy AVA now →</a>
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

{{--
    TEMPLATE — One-off action page reference.

    Canonical structure for a page whose only job is a single yes/no
    decision (accept/decline an invitation, confirm a destructive action,
    confirm a password, etc.) — the user makes one choice, then is routed
    elsewhere. Deliberately NOT the UX2 dashboard shell (no sidebar, no
    worker switcher, no LINKS section) — that chrome is for pages people
    navigate around in, and is dead weight on a single-decision screen.
    Matches resources/views/auth/*.blade.php (login, register,
    confirm-password): slim topbar (logo + theme toggle) + one centered
    card, no shared Blade component — copy this structure directly into
    the real page, same as the auth pages do.

    Improve the shell here, then carry matching fixes into real one-off
    action pages (e.g. resources/views/memory/accept.blade.php). Reachable
    at /templates/action.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Action Template — UNIT</title>
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
.au-card{width:100%;max-width:440px;background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)}

/* Header block: icon + title + one-line context */
.act-head{padding:28px 28px 20px}
.act-icon{width:40px;height:40px;border-radius:12px;background:var(--db-chip);display:flex;align-items:center;justify-content:center;margin-bottom:14px}
.act-icon svg{width:20px;height:20px;stroke:var(--db-text);stroke-width:1.8;fill:none}
.act-h1{font-size:1.15rem;font-weight:900;letter-spacing:-.03em;color:var(--db-text);margin-bottom:6px}
.act-sub{font-size:12.5px;color:var(--db-text-muted);line-height:1.6}
.act-sub strong{color:var(--db-text);font-weight:600}

/* Detail list — what will happen / what's included */
.act-detail{padding:20px 28px;border-top:1px solid var(--db-border)}
.act-detail-hd{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:12px}
.act-detail-list{display:flex;flex-direction:column;gap:10px}
.act-detail-row{display:flex;align-items:flex-start;gap:10px}
.act-detail-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;flex-shrink:0;margin-top:5px}
.act-detail-row.muted{opacity:.4}
.act-detail-row.muted .act-detail-dot{background:#ef4444}
.act-detail-title{font-size:12.5px;font-weight:700;color:var(--db-text)}
.act-detail-desc{font-size:12px;color:var(--db-text-muted);margin-top:1px}

/* Footer: primary + secondary action, side by side */
.act-foot{padding:20px 28px;border-top:1px solid var(--db-border);display:flex;flex-direction:column;gap:10px}
@media(min-width:420px){.act-foot{flex-direction:row}}
.act-btn{flex:1;padding:12px;border-radius:12px;font-size:13.5px;font-weight:700;font-family:inherit;cursor:pointer;text-align:center;text-decoration:none}
.act-btn-primary{border:none;background:var(--db-invert-bg);color:var(--db-invert-text)}
.act-btn-primary:hover{opacity:.9}
.act-btn-secondary{border:1px solid var(--db-border);background:transparent;color:var(--db-text-muted)}
.act-btn-secondary:hover{color:var(--db-text)}
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

    <div class="act-head">
      <div class="act-icon">
        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      </div>
      <div class="act-h1">Example Invitation</div>
      <div class="act-sub"><strong>Someone</strong> has invited you to do a thing. One line of context about what this means goes here.</div>
    </div>

    <div class="act-detail">
      <div class="act-detail-hd">What this includes</div>
      <div class="act-detail-list">
        <div class="act-detail-row">
          <span class="act-detail-dot"></span>
          <div><div class="act-detail-title">Included item</div><div class="act-detail-desc">One line describing what this grants or does.</div></div>
        </div>
        <div class="act-detail-row muted">
          <span class="act-detail-dot"></span>
          <div><div class="act-detail-title">Excluded item — never available</div><div class="act-detail-desc">One line describing what this explicitly does not do.</div></div>
        </div>
      </div>
    </div>

    <div class="act-foot">
      <form method="POST" action="#" style="flex:1">
        @csrf
        <button type="submit" class="act-btn act-btn-primary" style="width:100%">Accept</button>
      </form>
      <a href="#" class="act-btn act-btn-secondary">Decline</a>
    </div>

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

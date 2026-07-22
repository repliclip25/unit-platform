<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Error') — UNIT</title>
<meta name="robots" content="noindex">
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --gold:#F5C518;--text:#0D0D0D;--t2:#6B7280;--line:#E5E7EB;--bg:#FFFFFF;
}
@media (prefers-color-scheme: dark){
  :root{--text:#F5F5F5;--t2:#9CA3AF;--line:rgba(255,255,255,.14);--bg:#0D0D0D}
}
body{
  font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);
  min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;
  text-align:center;padding:24px;-webkit-font-smoothing:antialiased;
}
.err-logo{font-size:1.3rem;font-weight:900;letter-spacing:-.04em;color:var(--text);text-decoration:none;position:absolute;top:24px;left:32px}
.err-code{font-size:5.5rem;font-weight:900;letter-spacing:-.05em;line-height:1;color:var(--gold)}
.err-title{font-size:1.4rem;font-weight:800;margin-top:14px;letter-spacing:-.02em}
.err-desc{font-size:14.5px;color:var(--t2);margin-top:8px;max-width:420px;line-height:1.55}
.err-actions{display:flex;gap:10px;margin-top:28px;flex-wrap:wrap;justify-content:center}
.err-btn{padding:10px 22px;border-radius:99px;font-size:14px;font-weight:700;text-decoration:none;transition:opacity .15s,transform .15s}
.err-btn:hover{opacity:.85;transform:translateY(-1px)}
.err-btn-primary{background:#0D0D0D;color:#fff}
@media (prefers-color-scheme: dark){.err-btn-primary{background:#fff;color:#0D0D0D}}
.err-btn-ghost{border:1px solid var(--line);color:var(--text)}
</style>
</head>
<body>
<a href="/" class="err-logo">UNIT</a>
<div class="err-code">@yield('code')</div>
<div class="err-title">@yield('title')</div>
<p class="err-desc">@yield('desc')</p>
<div class="err-actions">
  <a href="/" class="err-btn err-btn-primary">Go home</a>
  <a href="/ai-workers" class="err-btn err-btn-ghost">Browse AI Workers</a>
</div>
</body>
</html>

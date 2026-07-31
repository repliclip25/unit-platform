<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'UNIT'): AI Agent Platform</title>
<meta name="description" content="@yield('description', 'UNIT is an AI agent platform that helps businesses deploy specialized AI Workers to manage recurring business workflows, automate operations, and complete real work.')">
<link rel="icon" type="image/png" href="/logo.png">
<link rel="apple-touch-icon" href="/logo.png">
{{-- Captures the title/description sections above as strings (instead of re-declaring
     them) so every public page gets OG/Twitter tags for free without duplicating copy. --}}
@php
    $__fullTitle = trim($__env->yieldContent('title', 'UNIT')) . ': AI Agent Platform';
    $__fullDesc  = trim($__env->yieldContent('description', 'UNIT is an AI agent platform that helps businesses deploy specialized AI Workers to manage recurring business workflows, automate operations, and complete real work.'));
@endphp
@include('partials.seo-meta', [
    'title'       => $__fullTitle,
    'description' => $__fullDesc,
    'image'       => $__env->yieldContent('og_image', asset('images/hero-team-2.png')),
    'type'        => $__env->yieldContent('og_type', 'website'),
])
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

/* Nav and footer markup/CSS/JS now live entirely in the public-nav and
   public-footer components (see those files), not here. Having a second
   copy of that CSS in this layout was exactly how the nav ended up with
   two different position values and two different border-color variable
   names across the site. */

/* ── SHARED CONTENT COMPONENTS (used directly by page bodies) ── */
.eyebrow{display:inline-flex;align-items:center;gap:9px;border:1px solid var(--line);background:rgba(0,0,0,.04);color:var(--text);font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;padding:7px 15px;border-radius:100px;margin-bottom:28px}
[data-theme="dark"] .eyebrow{background:rgba(255,255,255,.06)}
.slabel{font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--text);margin-bottom:12px}
.sh2{font-family:var(--fd);font-size:44px;font-weight:800;letter-spacing:-1.5px;line-height:1.06;margin-bottom:14px;color:var(--text)}
.ssub{font-size:16px;line-height:1.65;color:var(--t2)}
.sec{padding:90px 0}
.sec-dark{background:var(--surf);border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
.sh{text-align:center;max-width:640px;margin:0 auto 56px}
.pub-divider{height:1px;background:var(--line);margin:40px 0}

.btn-g{display:inline-flex;align-items:center;gap:7px;background:#0D0D0D;color:#fff;font-weight:700;font-size:14px;padding:10px 22px;border-radius:8px;border:none;cursor:pointer;transition:transform .15s,box-shadow .2s;white-space:nowrap;font-family:var(--fb)}
[data-theme="dark"] .btn-g{background:#fff;color:#0D0D0D}
.btn-g:hover{transform:translateY(-1px);box-shadow:0 8px 32px rgba(0,0,0,.2)}
.btn-gh{font-size:14px;color:var(--t4);cursor:pointer;border:none;background:none;font-family:var(--fb);transition:color .15s}
.btn-gh:hover{color:var(--text)}
.btn-ln{display:inline-flex;align-items:center;gap:7px;font-size:14px;font-weight:600;padding:10px 20px;border-radius:8px;border:1px solid var(--line2);color:var(--t2);cursor:pointer;background:none;transition:border-color .15s,color .15s;font-family:var(--fb)}
.btn-ln:hover{border-color:var(--text);color:var(--text)}

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
.pub-body a{color:var(--text);font-weight:600;text-decoration:underline;text-underline-offset:3px}
.pub-body strong{color:var(--text);font-weight:700}
.pub-meta{font-size:12px;color:var(--t4);margin-top:8px}

/* ── RESPONSIVE ── */
@media(max-width:900px){
  .sh2{font-size:34px}
}
@media(max-width:640px){
  .w{padding:0 20px}
  .w-md{padding:0 20px}
  .w-lg{padding:0 20px}
}
</style>
@yield('head')
</head>
<body>

<x-public-nav :links="[
  ['label' => 'Meet the AI Worker', 'href' => route('public.workers.index'), 'active' => request()->routeIs('public.workers.index')],
  ['label' => 'Pricing',          'href' => route('pricing'),              'active' => request()->routeIs('pricing')],
  ['label' => 'Blog',             'href' => route('blog'),                 'active' => request()->routeIs('blog*')],
  ['label' => 'Company',          'href' => route('about'),                'active' => request()->routeIs('about')],
]" />

@yield('body')

<x-public-footer />

{{-- Theme toggle + mobile menu JS now lives in <x-public-nav> (@once script
     block), not duplicated here. --}}
@yield('scripts')

@include('partials.tracking')
</body>
</html>

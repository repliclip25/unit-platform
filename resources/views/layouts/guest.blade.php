<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'UNIT') }}</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root, [data-theme="dark"] {
            --bg-base:      #0d0d0d;
            --bg-card:      rgba(33,33,33,0.97);
            --border:       rgba(255,255,255,0.09);
            --border-card:  rgba(255,255,255,0.09);
            --text-primary:   #ececec;
            --text-secondary: #b4b4b4;
            --text-muted:     #8e8ea0;
            --text-faint:     #555568;
            --nav-bg:       rgba(23,23,23,0.90);
            --nav-border:   rgba(255,255,255,0.08);
            --input-bg:     rgba(255,255,255,0.05);
            --input-border: rgba(255,255,255,0.1);
            --input-text:   #ececec;
            --input-ph:     #555568;
            --link-color:   #f3c531;
        }
        [data-theme="light"] {
            --bg-base:      radial-gradient(ellipse at 50% 100%, #EDE8DF 0%, #F5F0E8 55%, #FDFAF5 100%);
            --bg-card:      rgba(253,250,245,0.97);
            --border:       rgba(0,0,0,0.09);
            --border-card:  rgba(0,0,0,0.09);
            --text-primary:   #1C1917;
            --text-secondary: #44403C;
            --text-muted:     #78716C;
            --text-faint:     #A8A29E;
            --nav-bg:       rgba(253,250,245,0.90);
            --nav-border:   rgba(0,0,0,0.08);
            --input-bg:     #ffffff;
            --input-border: #C2BBB0;
            --input-text:   #1C1917;
            --input-ph:     #A8A29E;
            --link-color:   #92700a;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            background: var(--bg-base);
            color: var(--text-primary);
            transition: background .25s ease, color .2s ease;
        }

        .auth-nav    { background: var(--nav-bg); border-bottom: 1px solid var(--nav-border); backdrop-filter: blur(14px); }
        .auth-card   { background: var(--bg-card); border: 1px solid var(--border-card); backdrop-filter: blur(12px); }
        .auth-card h1, .auth-card .card-title { color: var(--text-primary); }

        .auth-label  { color: var(--text-muted); font-size: 0.75rem; font-weight: 500; display: block; margin-bottom: 6px; }
        .auth-input  {
            width: 100%; border-radius: 12px; padding: 12px 16px; font-size: 0.875rem;
            background: var(--input-bg); border: 1px solid var(--input-border);
            color: var(--input-text); transition: border-color .15s ease, box-shadow .15s ease;
            font-family: inherit;
        }
        .auth-input::placeholder { color: var(--input-ph); }
        .auth-input:focus { outline: none; border-color: #f3c531; box-shadow: 0 0 0 3px rgba(243,197,49,0.15); }

        .auth-muted  { color: var(--text-muted); }
        .auth-faint  { color: var(--text-faint); }
        .auth-link   { color: var(--link-color); font-weight: 600; transition: opacity .15s; }
        .auth-link:hover { opacity: 0.8; }

        /* Toggle */
        .theme-toggle {
            width: 36px; height: 20px; border-radius: 10px; border: none;
            cursor: pointer; position: relative; transition: background .2s ease; flex-shrink: 0;
        }
        .theme-toggle::after {
            content: ''; position: absolute; top: 3px; left: 3px;
            width: 14px; height: 14px; border-radius: 50%; background: white;
            transition: transform .2s ease;
        }
        [data-theme="dark"]  .theme-toggle { background: #f3c531; }
        [data-theme="light"] .theme-toggle { background: #cbd5e1; }
        [data-theme="dark"]  .theme-toggle::after { transform: translateX(16px); }
        [data-theme="light"] .theme-toggle::after { transform: translateX(0); }

        @media(max-width:480px){
            .auth-input { padding: 10px 14px; font-size: 0.8125rem; }
            .space-y-5 > * + * { margin-top: 14px; }
        }
    </style>
    <script>
        /* Prevent flash */
        (function() {
            const t = localStorage.getItem('unit-theme') || 'dark';
            document.getElementById('html-root').setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>

<nav class="auth-nav px-6 py-4 flex items-center justify-between">
    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
        <img src="/logo.png" alt="UNIT" class="w-8 h-8 rounded-md">
        <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.1rem;color:#f3c531">UNIT</span>
    </a>
    <div class="flex items-center gap-3">
        <span id="theme-label" class="text-xs" style="color:var(--text-faint)"></span>
        <button class="theme-toggle" id="theme-toggle" title="Toggle dark/light mode"></button>
    </div>
</nav>

<div class="flex-1 flex items-center justify-center px-4 py-6" style="min-height:calc(100vh - 65px)">
    <div class="w-full max-w-sm">
        <div class="auth-card rounded-2xl" style="padding:clamp(20px,5vw,32px)">
            {{ $slot }}
        </div>
    </div>
</div>

<script>
function applyTheme(theme) {
    document.getElementById('html-root').setAttribute('data-theme', theme);
    const label = document.getElementById('theme-label');
    if (label) label.textContent = theme === 'dark' ? 'Dark' : 'Light';
}

document.addEventListener('DOMContentLoaded', function () {
    applyTheme(localStorage.getItem('unit-theme') || 'dark');
    document.getElementById('theme-toggle').addEventListener('click', function () {
        const next = document.getElementById('html-root').getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('unit-theme', next);
        applyTheme(next);
    });
});
</script>
</body>
</html>

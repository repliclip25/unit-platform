<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" data-theme="light">
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
            --bg-base:      #000000;
            --bg-card:      #212121;
            --border:       rgba(255,255,255,0.12);
            --border-card:  rgba(255,255,255,0.12);
            --text-primary:   #ffffff;
            --text-secondary: #cccccc;
            --text-muted:     #999999;
            --text-faint:     #555555;
            --nav-bg:       rgba(0,0,0,0.92);
            --nav-border:   rgba(255,255,255,0.10);
            --input-bg:     #212121;
            --input-border: rgba(255,255,255,0.15);
            --input-text:   #ffffff;
            --input-ph:     #555555;
            --link-color:   #93aee8;
            --accent:      #142C74;
            --accent-rgb:  20, 44, 116;
            --accent-dark: #0e2260;
            --accent-text: #93aee8;
        }
        [data-theme="light"] {
            --bg-base:      #f9f9f7;
            --bg-card:      #ffffff;
            --border:       #e2e2e0;
            --border-card:  #e2e2e0;
            --text-primary:   #000000;
            --text-secondary: #1a1a1a;
            --text-muted:     #555555;
            --text-faint:     #999999;
            --nav-bg:       rgba(249,249,247,0.92);
            --nav-border:   #e2e2e0;
            --input-bg:     #ffffff;
            --input-border: #e2e2e0;
            --input-text:   #000000;
            --input-ph:     #999999;
            --link-color:   var(--gold);
            --accent:      #142C74;
            --accent-rgb:  20, 44, 116;
            --accent-dark: #0e2260;
            --accent-text: var(--gold);
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
        .auth-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(var(--accent-rgb),0.15); }

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
        [data-theme="dark"]  .theme-toggle { background: var(--accent); }
        [data-theme="light"] .theme-toggle { background: #cccccc; }
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
            const t = localStorage.getItem('unit-theme-v2') || 'light';
            document.getElementById('html-root').setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>

<nav class="auth-nav px-6 py-4 flex items-center justify-between">
    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
        <img src="/logo.png" alt="UNIT" class="w-8 h-8 rounded-md">
        <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.1rem;color:var(--accent-text,var(--accent))">UNIT</span>
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
    applyTheme(localStorage.getItem('unit-theme-v2') || 'light');
    document.getElementById('theme-toggle').addEventListener('click', function () {
        const next = document.getElementById('html-root').getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('unit-theme-v2', next);
        applyTheme(next);
    });
});
</script>
<script>
(function(){
    const SP='<svg style="width:1em;height:1em;display:inline-block;vertical-align:-0.15em;animation:spin 0.7s linear infinite;margin-right:0.35em" fill="none" viewBox="0 0 24 24"><circle style="opacity:.3" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.85" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>';
    const s=document.createElement('style');s.textContent='@keyframes spin{to{transform:rotate(360deg)}}';document.head.appendChild(s);
    function load(btn){
        if(!btn||btn._loadingActive||btn.dataset.noLoading!==undefined)return;
        btn._loadingActive=true;btn.disabled=true;
        btn.style.opacity='0.75';btn.style.cursor='wait';
        btn.innerHTML=SP+(btn.dataset.loadingText||btn.textContent.trim()||'Please wait…');
    }
    document.addEventListener('submit',function(e){
        if(e.target.dataset.noLoading!==undefined)return;
        const b=e.target._submitter||e.target.querySelector('[type="submit"]:not([data-no-loading])')||e.target.querySelector('button:not([data-no-loading])');
        if(b)load(b);
    },true);
    document.addEventListener('click',function(e){
        const b=e.target.closest('[type="submit"]');if(b?.form)b.form._submitter=b;
    },true);
})();
</script>
</body>
</html>

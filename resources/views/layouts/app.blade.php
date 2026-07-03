<!DOCTYPE html>
<html lang="en" class="h-full" id="html-root" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>UNIT — {{ $title ?? 'Command Center' }}</title>
    <link rel="icon" type="image/png" href="/logo.png">
    @if(config('services.gtm_id') && auth()->check() && auth()->user()->role !== 'admin')
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
    var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;
    j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ config("services.gtm_id") }}');</script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        /* ── Theme tokens ───────────────────────────────── */
        /* ── Badge tokens (tier / status chips) ──────────── */
        :root, [data-theme="dark"] {
            --badge-fast-bg:       rgba(6,182,212,0.15);    --badge-fast-text:       #67e8f9;
            --badge-balanced-bg:   rgba(241,211,98,0.15);   --badge-balanced-text:   #fde68a;
            --badge-powerful-bg:   rgba(168,85,247,0.15);   --badge-powerful-text:   #c4b5fd;
            --badge-reasoning-bg:  rgba(239,68,68,0.15);    --badge-reasoning-text:  #fca5a5;
            --badge-platform-bg:   rgba(16,185,129,0.12);   --badge-platform-text:   #6ee7b7;
            --badge-yourkey-bg:    rgba(241,211,98,0.12);   --badge-yourkey-text:    #fde68a;
            --badge-custom-bg:     rgba(156,163,175,0.1);   --badge-custom-text:     #9ca3af;
        }
        [data-theme="light"] {
            --badge-fast-bg:       rgba(6,182,212,0.12);    --badge-fast-text:       #0369a1;
            --badge-balanced-bg:   rgba(241,211,98,0.18);   --badge-balanced-text:   #7a5c00;
            --badge-powerful-bg:   rgba(124,58,237,0.12);   --badge-powerful-text:   #6d28d9;
            --badge-reasoning-bg:  rgba(239,68,68,0.12);    --badge-reasoning-text:  #b91c1c;
            --badge-platform-bg:   rgba(5,150,105,0.10);    --badge-platform-text:   #047857;
            --badge-yourkey-bg:    rgba(241,211,98,0.15);   --badge-yourkey-text:    #7a5c00;
            --badge-custom-bg:     rgba(107,114,128,0.10);  --badge-custom-text:     #4b5563;
        }

        :root, [data-theme="dark"] {
            --bg-base:     #000000;
            --bg-surface:  #111111;
            --bg-raised:   #1a1a1a;
            --bg-card:     #212121;
            --border:        rgba(255,255,255,0.12);
            --border-soft:   rgba(255,255,255,0.06);
            --border-subtle: rgba(255,255,255,0.10);
            --text-primary:   #ffffff;
            --text-secondary: #cccccc;
            --text-muted:     #999999;
            --text-faint:     #555555;
            --mob-bar-bg:  rgba(0,0,0,0.94);
            --scrollbar-track: #111111;
            --scrollbar-thumb: #333333;
            --accent:      #f1d362;
            --accent-rgb:  241, 211, 98;
            --accent-dark: #c9a800;
            --accent-text: #f1d362;
        }
        [data-theme="light"] {
            --bg-base:     #f9f9f7;
            --bg-surface:  #ffffff;
            --bg-raised:   #f0f0ee;
            --bg-card:     #ffffff;
            --border:        #e2e2e0;
            --border-soft:   rgba(0,0,0,0.05);
            --border-subtle: #e8e8e6;
            --text-primary:   #000000;
            --text-secondary: #1a1a1a;
            --text-muted:     #555555;
            --text-faint:     #999999;
            --mob-bar-bg:  rgba(249,249,247,0.94);
            --scrollbar-track: #f0f0ee;
            --scrollbar-thumb: #cccccc;
            --accent:      #f1d362;
            --accent-rgb:  241, 211, 98;
            --accent-dark: #c9a800;
            --accent-text: #7a5c00;
        }

        /* ── Base ───────────────────────────────────────── */
        html, body { background: var(--bg-base); color: var(--text-primary); font-size: 16px; line-height: 1.6; }

        /* ── Sidebar ────────────────────────────────────── */
        #sidebar         { background: var(--bg-surface); border-right: 1px solid var(--border); }
        .nav-section-label { color: var(--text-secondary); }
        .nav-link        { color: var(--text-secondary); }
        .nav-link:hover  { color: var(--text-primary); background: var(--bg-raised); }
        .sidebar-bottom  { border-top: 1px solid var(--border); }
        .usage-card      { background: var(--bg-raised); border: 1px solid var(--border); }
        .worker-avatar   { background: rgba(128,128,128,0.12); color: var(--text-secondary); }

        /* ── Top header ─────────────────────────────────── */
        #main-header     { background: var(--bg-surface); border-bottom: 1px solid var(--border); }
        #main-header h1  { color: var(--text-primary); }
        .header-date     { color: var(--text-secondary); }
        .horizon-link    { color: var(--text-secondary); border: 1px solid var(--border); }
        .horizon-link:hover { color: var(--text-primary); }

        /* ── Main content ───────────────────────────────── */
        #main-content    { background: var(--bg-base); }

        /* ── Dark-mode overrides for Tailwind gray classes ── */
        [data-theme="dark"] .bg-gray-950 { background-color: #000000 !important; }
        [data-theme="dark"] .bg-gray-900 { background-color: #111111 !important; }
        [data-theme="dark"] .bg-gray-800 { background-color: #1a1a1a !important; }
        [data-theme="dark"] .bg-gray-700 { background-color: #2a2a2a !important; }
        [data-theme="dark"] .bg-gray-800\/40 { background-color: rgba(26,26,26,0.5) !important; }
        [data-theme="dark"] .bg-gray-800\/60 { background-color: rgba(26,26,26,0.7) !important; }
        [data-theme="dark"] .bg-gray-900\/40 { background-color: rgba(17,17,17,0.5) !important; }

        [data-theme="dark"] .border-gray-800 { border-color: rgba(255,255,255,0.12) !important; }
        [data-theme="dark"] .border-gray-700 { border-color: rgba(255,255,255,0.16) !important; }
        [data-theme="dark"] .border-gray-600 { border-color: rgba(255,255,255,0.22) !important; }
        [data-theme="dark"] .border-gray-700\/40 { border-color: rgba(255,255,255,0.08) !important; }
        [data-theme="dark"] .border-gray-800\/40 { border-color: rgba(255,255,255,0.06) !important; }
        [data-theme="dark"] .divide-gray-800 > * + * { border-color: rgba(255,255,255,0.12) !important; }

        [data-theme="dark"] .text-white    { color: #ffffff !important; }
        [data-theme="dark"] .text-gray-100 { color: #f0f0f0 !important; }
        [data-theme="dark"] .text-gray-200 { color: #dddddd !important; }
        [data-theme="dark"] .text-gray-300 { color: #bbbbbb !important; }
        [data-theme="dark"] .text-gray-400 { color: #999999 !important; }
        [data-theme="dark"] .text-gray-500 { color: #777777 !important; }
        [data-theme="dark"] .text-gray-600 { color: #666666 !important; }
        [data-theme="dark"] .text-gray-700 { color: #555555 !important; }

        /* Dark mode input/select fields */
        [data-theme="dark"] input,
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background: #212121 !important;
            border-color: rgba(255,255,255,0.15) !important;
            color: #ffffff !important;
        }

        /* Dark mode button borders */
        [data-theme="dark"] button.border,
        [data-theme="dark"] a.border { border-color: rgba(255,255,255,0.16) !important; }
        [data-theme="dark"] .border-gray-700.text-gray-400 { color: #cccccc !important; border-color: rgba(255,255,255,0.16) !important; }

        /* ── Light-mode: all text defaults to black ── */
        [data-theme="light"] body,
        [data-theme="light"] #main-content { color: #000000; }
        [data-theme="light"] #main-content p,
        [data-theme="light"] #main-content span,
        [data-theme="light"] #main-content div,
        [data-theme="light"] #main-content td,
        [data-theme="light"] #main-content th,
        [data-theme="light"] #main-content li,
        [data-theme="light"] #main-content label,
        [data-theme="light"] #main-content h1,
        [data-theme="light"] #main-content h2,
        [data-theme="light"] #main-content h3,
        [data-theme="light"] #main-content h4,
        [data-theme="light"] #main-content a:not([class*="bg-brand"]):not([class*="text-brand"]) {
            color: inherit;
        }

        /* ── Light-mode overrides for hardcoded Tailwind dark classes ── */
        [data-theme="light"] .bg-gray-950 { background-color: #f9f9f7 !important; }
        [data-theme="light"] .bg-gray-900 { background-color: #ffffff !important; }
        [data-theme="light"] .bg-gray-800 { background-color: #f0f0ee !important; }
        [data-theme="light"] .bg-gray-800\/40 { background-color: rgba(240,240,238,0.7) !important; }
        [data-theme="light"] .bg-gray-800\/60 { background-color: rgba(240,240,238,0.85) !important; }
        [data-theme="light"] .bg-gray-700 { background-color: #e2e2e0 !important; }
        [data-theme="light"] .bg-gray-900\/40 { background-color: rgba(255,255,255,0.7) !important; }
        [data-theme="light"] .bg-navy-2,
        [data-theme="light"] [class*="bg-navy"] { background-color: #f0f0ee !important; }

        [data-theme="light"] .border-gray-800 { border-color: #e2e2e0 !important; }
        [data-theme="light"] .border-gray-700 { border-color: #cccccc !important; }
        [data-theme="light"] .border-gray-700\/40 { border-color: rgba(204,204,204,0.6) !important; }
        [data-theme="light"] .border-gray-800\/40 { border-color: rgba(226,226,224,0.8) !important; }
        [data-theme="light"] .border-gray-600 { border-color: #aaaaaa !important; }

        [data-theme="light"] .text-white    { color: #000000 !important; }
        [data-theme="light"] .text-gray-100 { color: #000000 !important; }
        [data-theme="light"] .text-gray-200 { color: #111111 !important; }
        [data-theme="light"] .text-gray-300 { color: #222222 !important; }
        [data-theme="light"] .text-gray-400 { color: #444444 !important; }
        [data-theme="light"] .text-gray-500 { color: #666666 !important; }
        [data-theme="light"] .text-gray-600 { color: #666666 !important; }

        /* Override inline style dark colors */
        [data-theme="light"] [style*="color:#8b91a0"]  { color: #555555 !important; }
        [data-theme="light"] [style*="color:#c9cdd6"]  { color: #222222 !important; }
        [data-theme="light"] [style*="color:#f4f1ea"]  { color: #000000 !important; }
        [data-theme="light"] [style*="color:#4a5063"]  { color: #999999 !important; }
        [data-theme="light"] [style*="color:var(--text-primary)"]   { color: #000000 !important; }
        [data-theme="light"] [style*="color:var(--text-secondary)"] { color: #1a1a1a !important; }
        [data-theme="light"] [style*="color:var(--text-muted)"]     { color: #555555 !important; }
        [data-theme="light"] [style*="color:var(--text-faint)"]     { color: #999999 !important; }

        /* Override inline style dark backgrounds */
        [data-theme="light"] [style*="background:#0a0e1a"],
        [data-theme="light"] [style*="background:#171717"]      { background: #ffffff !important; }
        [data-theme="light"] [style*="background:#05070d"],
        [data-theme="light"] [style*="background:#0d0d0d"],
        [data-theme="light"] [style*="background:#000000"]      { background: #f9f9f7 !important; }
        [data-theme="light"] [style*="background:#0d1220"],
        [data-theme="light"] [style*="background:#212121"]      { background: #f0f0ee !important; }
        [data-theme="light"] [style*="background:rgba(13,18,32"],
        [data-theme="light"] [style*="background:rgba(33,33,33"] { background: rgba(240,240,238,0.9) !important; }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.05)"] { background: rgba(0,0,0,0.03) !important; }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.07)"] { background: rgba(0,0,0,0.04) !important; }

        /* Border inline overrides */
        [data-theme="light"] [style*="border:1px solid rgba(255,255,255,0.08)"] { border-color: #e2e2e0 !important; }
        [data-theme="light"] [style*="border-color:rgba(255,255,255"] { border-color: #e2e2e0 !important; }

        [data-theme="light"] .divide-gray-800 > * + * { border-color: #e2e2e0 !important; }

        /* Subnav tabs in light mode */
        [data-theme="light"] .border-b.border-gray-800 { border-color: #e2e2e0 !important; }
        [data-theme="light"] nav.border-b { border-color: #e2e2e0 !important; }

        /* Input fields in light mode */
        [data-theme="light"] input,
        [data-theme="light"] select,
        [data-theme="light"] textarea {
            background: #ffffff !important;
            border-color: #e2e2e0 !important;
            color: #000000 !important;
        }

        /* Stage/badge chips */
        [data-theme="light"] .bg-brand\/10  { background-color: rgba(var(--accent-rgb),0.15) !important; }
        [data-theme="light"] .bg-brand\/15  { background-color: rgba(var(--accent-rgb),0.18) !important; }
        [data-theme="light"] .bg-brand\/12  { background-color: rgba(var(--accent-rgb),0.15) !important; }
        [data-theme="light"] .bg-brand\/20  { background-color: rgba(var(--accent-rgb),0.20) !important; }
        [data-theme="light"] .bg-purple-900 { background-color: rgba(var(--accent-rgb),0.12) !important; }
        [data-theme="light"] .text-purple-300 { color: var(--accent-text) !important; }

        /* Brand/accent text → dark readable amber in light mode */
        [data-theme="light"] .text-brand      { color: var(--accent-text) !important; }
        [data-theme="light"] .text-brand-deep { color: var(--accent-dark) !important; }
        /* Inline style color overrides for accent text */
        [data-theme="light"] [style*="color:var(--accent)"]     { color: var(--accent-text) !important; }
        [data-theme="light"] [style*="color: var(--accent)"]    { color: var(--accent-text) !important; }
        /* Brand borders in light mode */
        [data-theme="light"] .border-brand\/40 { border-color: rgba(var(--accent-rgb),0.4) !important; }
        [data-theme="light"] .border-brand\/30 { border-color: rgba(var(--accent-rgb),0.3) !important; }

        /* Brand gold buttons: always yellow bg with black text */
        .bg-brand, [class*="bg-brand"]:not([class*="bg-brand/"]):not([class*="bg-brand-"]) {
            background-color: var(--accent) !important;
            color: #000000 !important;
        }

        /* Card hover shadow */
        [data-theme="light"] .bg-gray-900.border:hover,
        [data-theme="light"] .rounded-2xl.border:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.10), 0 1px 4px rgba(0,0,0,0.06) !important;
            transition: box-shadow .2s ease;
        }

        /* ── Light mode card elevation ── */
        [data-theme="light"] .bg-gray-900.border.border-gray-800,
        [data-theme="light"] .bg-gray-900.border.border-gray-800\/40 {
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04) !important;
        }
        [data-theme="light"] .rounded-2xl.border {
            box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 6px 20px rgba(0,0,0,0.04) !important;
        }

        /* Status/alert banners with dark inline backgrounds */
        [data-theme="light"] [style*="background:rgba(239,68,68"] { background: rgba(239,68,68,0.12) !important; color: #991B1B !important; }
        [data-theme="light"] [style*="background:rgba(234,179,8"]  { background: rgba(234,179,8,0.12)  !important; color: #78350F !important; }
        [data-theme="light"] [style*="background:rgba(34,197,94"]  { background: rgba(34,197,94,0.12)  !important; color: #14532D !important; }
        [data-theme="light"] [style*="background:rgba(59,130,246"] { background: rgba(59,130,246,0.12) !important; color: #1E3A5F !important; }

        /* ── Dark-mode: fix inline style navy/blue backgrounds ── */
        [data-theme="dark"] [style*="background:#0a0e1a"]       { background: #111111 !important; }
        [data-theme="dark"] [style*="background:#05070d"]       { background: #000000 !important; }
        [data-theme="dark"] [style*="background:#0d1220"]       { background: #212121 !important; }
        [data-theme="dark"] [style*="background:rgba(10,14,26"] { background: rgba(17,17,17,0.9) !important; }
        [data-theme="dark"] [style*="background:rgba(13,18,32"] { background: rgba(33,33,33,0.9) !important; }
        [data-theme="dark"] [style*="color:#8b91a0"]            { color: #999999 !important; }
        [data-theme="dark"] [style*="color:#4a5063"]            { color: #555555 !important; }
        [data-theme="dark"] [style*="color:#c9cdd6"]            { color: #cccccc !important; }
        [data-theme="dark"] [style*="color:#f4f1ea"]            { color: #ffffff !important; }

        /* ── Mobile bar ─────────────────────────────────── */
        #mob-bar         { background: var(--mob-bar-bg); border-bottom: 1px solid var(--border); }
        #mob-bar span    { color: var(--text-primary); }

        /* Brand button text always black */
        [style*="background:var(--accent)"] { color: #000000 !important; }

        /* ── Toggle button ──────────────────────────────── */
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

        /* ── Scrollbar ──────────────────────────────────── */
        ::-webkit-scrollbar       { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--scrollbar-track); }
        ::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb); border-radius: 3px; }
    </style>
    <script>
        /* Apply saved theme before first paint — prevents flash */
        (function() {
            const t = localStorage.getItem('unit-theme-v2') || 'light';
            document.getElementById('html-root').setAttribute('data-theme', t);
        })();
    </script>
    @stack('head')
</head>
<body class="h-full font-sans antialiased">

{{-- Mobile top bar --}}
<div id="mob-bar" class="lg:hidden fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-4 py-3" style="backdrop-filter:blur(14px)">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
        <img src="/logo.png" alt="UNIT" class="w-7 h-7 rounded-md">
        <span class="font-display font-bold text-base">UNIT</span>
    </a>
    <button id="mob-toggle" class="p-1.5 rounded-md" style="background:rgba(128,128,128,0.1)">
        <svg id="mob-icon-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg id="mob-icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

<div class="min-h-screen flex">

    {{-- Sidebar --}}
    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-64 flex flex-col transition-transform -translate-x-full lg:translate-x-0">

        <div class="px-5 py-5 shrink-0" style="border-bottom:1px solid var(--border)">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <img src="/logo.png" alt="UNIT" class="w-8 h-8 rounded-md">
                <div>
                    <p class="font-display font-bold text-sm leading-tight" style="color:var(--text-primary)">UNIT Platform</p>
                    <p class="text-xs mt-0.5" style="color:var(--text-faint)">Employee OS</p>
                </div>
            </a>
        </div>

        <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-5">

            <div>
                <p class="nav-section-label px-3 mb-1.5 text-xs uppercase tracking-widest font-semibold">Platform</p>
                @php
                    $navLink = fn($active) => 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition ' . ($active ? '' : 'nav-link');
                    $navStyle = fn($active) => $active ? 'background:var(--accent);color:#000000' : '';
                @endphp
                <a href="{{ route('dashboard') }}" class="{{ $navLink(request()->routeIs('dashboard')) }}" style="{{ $navStyle(request()->routeIs('dashboard')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Command Center
                </a>
                <a href="{{ route('transactions') }}" class="{{ $navLink(request()->routeIs('transactions*')) }}" style="{{ $navStyle(request()->routeIs('transactions*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Transactions
                </a>
                <a href="{{ route('billing') }}" class="{{ $navLink(request()->routeIs('billing*')) }}" style="{{ $navStyle(request()->routeIs('billing*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Billing
                </a>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('qa') }}" class="{{ $navLink(request()->routeIs('qa*')) }}" style="{{ $navStyle(request()->routeIs('qa*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    System QA
                </a>
                @endif
            </div>

            <div>
                <div class="px-3 mb-1.5 flex items-center justify-between">
                    <p class="nav-section-label text-xs uppercase tracking-widest font-semibold">My Team</p>
                    <a href="{{ route('workers.deploy') }}" class="text-xs font-semibold" style="color:var(--accent)">+ Hire</a>
                </div>
                @forelse($deployments as $dep)
                    @php $isActive = request()->segment(2) == $dep->id; @endphp
                    <a href="{{ route('workers.show', $dep->worker_slug) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ $isActive ? '' : 'nav-link' }}"
                       style="{{ $isActive ? 'background:var(--accent);color:#000000' : '' }}">
                        <div class="relative shrink-0">
                            <div class="worker-avatar w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold"
                                 style="{{ $isActive ? 'background:rgba(26,20,4,0.2);color:#1a1404' : '' }}">
                                {{ strtoupper(substr($dep->worker_slug, 0, 1)) }}
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full"
                                  style="background:{{ $dep->status === 'active' ? '#4ade80' : '#facc15' }};border:2px solid var(--bg-surface)"></span>
                        </div>
                        <span class="truncate flex-1">{{ $dep->name }}</span>
                    </a>
                @empty
                    <div class="mx-1 px-3 py-4 rounded-lg text-center" style="border:1px dashed var(--border)">
                        <p class="text-xs mb-1" style="color:var(--text-faint)">No employees hired yet</p>
                        <a href="{{ route('workers.deploy') }}" class="text-xs font-semibold" style="color:var(--accent)">Hire your first →</a>
                    </div>
                @endforelse
            </div>

            <div>
                <p class="nav-section-label px-3 mb-1.5 text-xs uppercase tracking-widest font-semibold">Settings</p>
                <a href="{{ route('settings.api-keys') }}" class="{{ $navLink(request()->routeIs('settings.*')) }}" style="{{ $navStyle(request()->routeIs('settings.*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    API Keys
                </a>
                @if(auth()->user()->role === 'admin')
                {{-- ── Admin-only divider ─────────────────────────────── --}}
                <div class="flex items-center gap-2 px-3 my-3">
                    <div class="flex-1 h-px" style="background:var(--border)"></div>
                    <span class="text-xs font-semibold uppercase tracking-widest" style="color:var(--text-faint)">Admin</span>
                    <div class="flex-1 h-px" style="background:var(--border)"></div>
                </div>
                <a href="{{ route('admin.platform') }}" class="{{ $navLink(request()->routeIs('admin.platform') || request()->routeIs('admin.platform.index')) }}" style="{{ $navStyle(request()->routeIs('admin.platform') || request()->routeIs('admin.platform.index')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                    Control Tower
                </a>
                <a href="{{ route('admin.tenants') }}" class="{{ $navLink(request()->routeIs('admin.tenant*')) }}" style="{{ $navStyle(request()->routeIs('admin.tenant*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Tenant Controls
                </a>
                <a href="{{ route('admin.worker-requests') }}" class="{{ $navLink(request()->routeIs('admin.worker-requests*')) }}" style="{{ $navStyle(request()->routeIs('admin.worker-requests*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                    Worker Requests
                </a>
                <a href="{{ route('admin.workers.index') }}" class="{{ $navLink(request()->routeIs('admin.workers*')) }}" style="{{ $navStyle(request()->routeIs('admin.workers*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Worker Builder
                </a>
                <a href="{{ route('admin.influencers') }}" class="{{ $navLink(request()->routeIs('admin.influencer*')) }}" style="{{ $navStyle(request()->routeIs('admin.influencer*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Influencers
                </a>
                <a href="{{ route('admin.prompts') }}" class="{{ $navLink(request()->routeIs('admin.prompts*')) }}" style="{{ $navStyle(request()->routeIs('admin.prompts*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    Prompts
                </a>
                <a href="{{ route('admin.blog') }}" class="{{ $navLink(request()->routeIs('admin.blog*')) }}" style="{{ $navStyle(request()->routeIs('admin.blog*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Blog
                </a>
                <a href="{{ route('admin.desk-cards') }}" class="{{ $navLink(request()->routeIs('admin.desk-cards*')) }}" style="{{ $navStyle(request()->routeIs('admin.desk-cards*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h7"/></svg>
                    Desk Cards
                </a>
                <a href="{{ route('admin.messaging') }}" class="{{ $navLink(request()->routeIs('admin.messaging*')) }}" style="{{ $navStyle(request()->routeIs('admin.messaging*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Messaging
                </a>
                <a href="{{ route('admin.pricing') }}" class="{{ $navLink(request()->routeIs('admin.pricing*')) }}" style="{{ $navStyle(request()->routeIs('admin.pricing*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Pricing
                </a>
                <a href="{{ route('admin.integrations') }}" class="{{ $navLink(request()->routeIs('admin.integrations*')) }}" style="{{ $navStyle(request()->routeIs('admin.integrations*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    Integrations
                </a>
                <a href="{{ route('admin.platform-usage') }}" class="{{ $navLink(request()->routeIs('admin.platform-usage*')) }}" style="{{ $navStyle(request()->routeIs('admin.platform-usage*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    AI Spend
                </a>
                @endif
            </div>

        </nav>

        <div class="sidebar-bottom px-3 py-4 shrink-0 space-y-2">
            @php
                $monthUsage = \Illuminate\Support\Facades\DB::table('usage_events')
                    ->where('user_id', auth()->id())
                    ->whereMonth('created_at', now()->month)
                    ->sum('cost_usd');
            @endphp
            <div class="usage-card rounded-lg px-3 py-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs" style="color:var(--text-faint)">AI Usage (this month)</span>
                    <span class="text-xs font-mono" style="color:var(--text-secondary)">${{ number_format($monthUsage, 4) }}</span>
                </div>
                <div class="flex items-center justify-between mt-1">
                    <span class="text-xs" style="color:var(--text-faint)">Team size</span>
                    <span class="text-xs" style="color:var(--text-muted)">{{ $deployments->count() }} hired</span>
                </div>
            </div>
            <div class="flex items-center justify-between px-1">
                <a href="{{ route('profile.show') }}"
                   class="text-xs truncate flex-1 transition hover:opacity-80"
                   style="color:var(--text-muted);text-decoration:none"
                   title="My Profile">{{ auth()->user()->name }}</a>
                <div style="display:flex;align-items:center;gap:10px">
                    <a href="{{ route('profile.show') }}"
                       class="text-xs transition hover:opacity-80"
                       style="color:var(--text-faint);text-decoration:none">Profile</a>
                    <span style="color:var(--border)">·</span>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline">
                        @csrf
                        <button class="text-xs transition hover:opacity-80" style="color:var(--text-faint)">Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    {{-- Mobile overlay --}}
    <div id="mob-overlay" class="fixed inset-0 z-30 bg-black/60 hidden lg:hidden" onclick="closeSidebar()"></div>

    {{-- Main --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        <header id="main-header" class="shrink-0 px-4 sm:px-6 py-4 flex items-center justify-between pt-16 lg:pt-4">
            <h1 class="font-display font-semibold text-lg">{{ $title ?? 'Command Center' }}</h1>
            <div class="flex items-center gap-3">
                <span class="header-date text-sm hidden sm:block">{{ now()->format('M d, Y') }}</span>
                {{-- Dark/light toggle --}}
                <div class="flex items-center gap-2">
                    <span id="theme-label" class="text-xs hidden sm:block" style="color:var(--text-faint)"></span>
                    <button class="theme-toggle" id="theme-toggle" title="Toggle dark/light mode"></button>
                </div>
                <a href="{{ url('/horizon') }}" target="_blank" class="horizon-link text-xs font-medium px-2.5 py-1.5 rounded-lg transition">Horizon ↗</a>
            </div>
        </header>

        <main id="main-content" class="flex-1 overflow-auto p-4 sm:p-6">

            @if(auth()->check())
            @php
                $__platformViolations = \App\Platform\Services\PolicyEngine::evaluate(auth()->id());
                $__topViolation       = \App\Platform\Services\PolicyEngine::mostSevere($__platformViolations);
            @endphp
            @if($__topViolation)
            @php
                $__c = match($__topViolation['color']) {
                    'red'   => ['bg'=>'rgba(127,29,29,0.18)','border'=>'rgba(239,68,68,0.35)','text'=>'#fca5a5'],
                    'amber' => ['bg'=>'rgba(120,53,15,0.18)', 'border'=>'rgba(245,158,11,0.35)','text'=>'#fde68a'],
                    default => ['bg'=>'rgba(55,65,81,0.2)',   'border'=>'rgba(107,114,128,0.3)','text'=>'#d1d5db'],
                };
                $__cta = $__topViolation['cta_url'] ?? ($__topViolation['cta_route'] ? route($__topViolation['cta_route']) : route('billing'));
            @endphp
            <div class="mb-4 flex items-center justify-between gap-3 px-4 py-3 rounded-xl border text-sm"
                 style="background:{{ $__c['bg'] }};border-color:{{ $__c['border'] }};color:{{ $__c['text'] }}">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="shrink-0">{{ $__topViolation['severity'] === 'hard' ? '⛔' : '⚠' }}</span>
                    <span><strong>{{ $__topViolation['title'] }}:</strong> {{ $__topViolation['description'] }}</span>
                </div>
                <a href="{{ $__cta }}" class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-lg border transition hover:opacity-80"
                   style="border-color:{{ $__c['border'] }};color:{{ $__c['text'] }}">{{ $__topViolation['cta_label'] }} →</a>
            </div>
            @endif
            @endif

            @if(session('success'))
            <div id="flash-success" class="mb-4 flex items-center gap-3 px-4 py-3 rounded-xl border text-sm"
                 style="background:rgba(34,197,94,0.08);border-color:rgba(34,197,94,0.3);color:#86efac">
                <span>✓</span><span>{{ session('success') }}</span>
                <button onclick="document.getElementById('flash-success').remove()" class="ml-auto opacity-50 hover:opacity-100">✕</button>
            </div>
            @endif
            @if(session('error'))
            <div id="flash-error" class="mb-4 flex items-center gap-3 px-4 py-3 rounded-xl border text-sm"
                 style="background:rgba(239,68,68,0.08);border-color:rgba(239,68,68,0.3);color:#fca5a5">
                <span>✕</span><span>{{ session('error') }}</span>
                <button onclick="document.getElementById('flash-error').remove()" class="ml-auto opacity-50 hover:opacity-100">✕</button>
            </div>
            @endif

            {{ $slot }}

            <footer class="mt-10 pt-6 pb-2 flex flex-wrap items-center gap-x-5 gap-y-1" style="border-top:1px solid var(--border)">
                <span class="text-xs font-semibold" style="color:var(--text-faint)">&copy; {{ date('Y') }} UNIT</span>
                <a href="{{ route('terms') }}" class="text-xs" style="color:var(--text-muted)" target="_blank">Terms</a>
                <a href="{{ route('privacy') }}" class="text-xs" style="color:var(--text-muted)" target="_blank">Privacy</a>
                <a href="mailto:hello@unit.report" class="text-xs" style="color:var(--text-muted)">Support</a>
                <a href="{{ route('billing') }}" class="text-xs" style="color:var(--text-muted)">Billing</a>
            </footer>
        </main>
    </div>
</div>

<script>
// ── Theme system ────────────────────────────────────────────────────────────
function applyTheme(theme) {
    document.getElementById('html-root').setAttribute('data-theme', theme);
    const label = document.getElementById('theme-label');
    if (label) { label.textContent = theme === 'dark' ? 'Dark' : 'Light'; }
}

document.addEventListener('DOMContentLoaded', function () {
    const saved = localStorage.getItem('unit-theme-v2') || 'light';
    applyTheme(saved);

    document.getElementById('theme-toggle').addEventListener('click', function () {
        const next = document.getElementById('html-root').getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('unit-theme-v2', next);
        applyTheme(next);
    });
});

// ── Mobile sidebar ──────────────────────────────────────────────────────────
function closeSidebar() {
    document.getElementById('sidebar').classList.add('-translate-x-full');
    document.getElementById('mob-overlay').classList.add('hidden');
    document.getElementById('mob-icon-open').classList.remove('hidden');
    document.getElementById('mob-icon-close').classList.add('hidden');
}
document.getElementById('mob-toggle').addEventListener('click', function () {
    const open = document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('mob-overlay').classList.toggle('hidden', open);
    document.getElementById('mob-icon-open').classList.toggle('hidden', !open);
    document.getElementById('mob-icon-close').classList.toggle('hidden', open);
});
</script>

@livewireScripts

@if(config('services.facebook_pixel_id') && auth()->check() && auth()->user()->role !== 'admin')
{{-- Facebook Pixel --}}
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ config("services.facebook_pixel_id") }}');
fbq('track', 'PageView');
fbq('trackCustom', 'PlatformPage', {
    page: '{{ request()->route()?->getName() ?? request()->path() }}',
    user_id: '{{ auth()->id() }}',
    section: '{{ str_starts_with(request()->route()?->getName() ?? "", "workers.") ? "workers" : (str_starts_with(request()->route()?->getName() ?? "", "billing") ? "billing" : "dashboard") }}'
});
</script>
<noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ config('services.facebook_pixel_id') }}&ev=PageView&noscript=1"/></noscript>
@endif

@if(config('services.gtm_id') && auth()->check() && auth()->user()->role !== 'admin')
{{-- Google Tag Manager (body) --}}
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.gtm_id') }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

{{-- ── Global button loading state ──
     Form submits → spinner + disabled on the submit button.
     Opt-in non-form buttons → add data-loading attribute.
     Exclude with data-no-loading on the button or form.
──────────────────────────────────────────────────────── --}}
<script>
(function () {
    const SPINNER = '<svg class="unit-spinner" style="width:1em;height:1em;display:inline-block;vertical-align:-0.15em;animation:spin 0.7s linear infinite;margin-right:0.35em" fill="none" viewBox="0 0 24 24"><circle style="opacity:.3" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.85" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>';
    if (!document.getElementById('unit-spinner-style')) {
        const s = document.createElement('style');
        s.id = 'unit-spinner-style';
        s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
        document.head.appendChild(s);
    }
    function startLoading(btn, labelOverride) {
        if (!btn || btn.dataset.noLoading !== undefined || btn._loadingActive) return;
        btn._loadingActive = true;
        btn._origInner    = btn.innerHTML;
        btn._origDisabled = btn.disabled;
        btn.disabled = true;
        btn.style.opacity  = '0.75';
        btn.style.cursor   = 'wait';
        const label = labelOverride || btn.dataset.loadingText || btn.textContent.trim() || 'Processing…';
        btn.innerHTML = SPINNER + label;
    }
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.dataset.noLoading !== undefined) return;
        // Use the button that triggered submit if available
        const btn = form._submitter ||
                    form.querySelector('[type="submit"]:not([data-no-loading])') ||
                    form.querySelector('button:not([data-no-loading])');
        if (btn) startLoading(btn);
    }, true);
    // Track which button triggered the submit
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[type="submit"]');
        if (btn?.form) btn.form._submitter = btn;
        // Opt-in non-form buttons
        const loadBtn = e.target.closest('[data-loading]');
        if (loadBtn && !loadBtn.closest('form')) startLoading(loadBtn);
    }, true);
})();
</script>

{{-- ── Searchable select — global enhancer ─────────────────────────────────
     Replaces every <select> (except [data-no-search] and size > 1) with a
     text-filter dropdown. Runs on DOMContentLoaded and after any dynamic
     content is injected (MutationObserver).
──────────────────────────────────────────────────────────────────────────── --}}
<script>
(function () {
    const SKIP_ATTR   = 'data-no-search';
    const DONE_ATTR   = 'data-ss-done';
    const MIN_OPTIONS = 1; // enhance even small selects for consistency

    function enhance(sel) {
        if (sel.hasAttribute(DONE_ATTR) || sel.hasAttribute(SKIP_ATTR)) return;
        if (sel.multiple || sel.size > 1) return;
        sel.setAttribute(DONE_ATTR, '1');

        // Collect options
        const options = Array.from(sel.options).map(o => ({ value: o.value, label: o.text.trim() }));
        const current = options.find(o => o.value === sel.value) || options[0] || { value: '', label: '' };

        // Build wrapper
        const wrap = document.createElement('div');
        wrap.className = 'ss-wrap relative';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);
        sel.style.display = 'none';

        // Text input (inherits width from parent naturally)
        const input = document.createElement('input');
        input.type        = 'text';
        input.value       = current.label === '— none —' || current.label === '— no client —' || current.value === '' ? '' : current.label;
        input.placeholder = current.value === '' ? (current.label || 'Select…') : 'Select…';
        input.autocomplete = 'off';
        input.className   = sel.className.replace('bg-gray-800','').replace('bg-gray-900','')
                            + ' ss-input w-full bg-gray-800 text-white rounded-lg px-3 border border-gray-700 focus:outline-none focus:border-yellow-500 placeholder-gray-600';
        // Preserve explicit py- from original select or fallback
        if (!input.className.includes('py-')) input.className += ' py-2';
        wrap.insertBefore(input, sel);

        // Dropdown
        const drop = document.createElement('div');
        drop.className = 'ss-drop hidden absolute z-50 left-0 right-0 mt-1 bg-gray-800 border border-gray-700 rounded-lg shadow-xl overflow-y-auto';
        drop.style.maxHeight = '220px';
        drop.style.top       = '100%';
        wrap.appendChild(drop);

        function renderDrop(q) {
            const lower   = q.toLowerCase();
            const matches = options.filter(o => o.label.toLowerCase().includes(lower));
            drop.innerHTML = matches.slice(0, 80).map(o =>
                `<div class="ss-opt px-3 py-2 text-sm cursor-pointer hover:bg-gray-700 ${o.value === sel.value ? 'text-yellow-400' : (o.value === '' ? 'text-gray-500' : 'text-white')}"
                    data-val="${o.value}" data-lbl="${o.label.replace(/"/g,'&quot;')}">${o.label}</div>`
            ).join('');
            if (!matches.length) {
                drop.innerHTML = '<div class="px-3 py-2 text-gray-600 text-xs">No results</div>';
            }
            drop.querySelectorAll('.ss-opt').forEach(opt => {
                opt.addEventListener('mousedown', e => {
                    e.preventDefault();
                    sel.value    = opt.dataset.val;
                    input.value  = opt.dataset.val === '' ? '' : opt.dataset.lbl;
                    input.placeholder = opt.dataset.val === '' ? opt.dataset.lbl : 'Select…';
                    drop.classList.add('hidden');
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        }

        input.addEventListener('focus', () => { renderDrop(input.value); drop.classList.remove('hidden'); });
        input.addEventListener('input', () => renderDrop(input.value));
        input.addEventListener('blur',  () => setTimeout(() => drop.classList.add('hidden'), 160));
    }

    function enhanceAll() {
        document.querySelectorAll('select:not([' + DONE_ATTR + '])').forEach(enhance);
    }

    document.addEventListener('DOMContentLoaded', enhanceAll);

    // Pick up selects added dynamically (e.g. inline edit forms toggled in)
    new MutationObserver(enhanceAll).observe(document.body, { childList: true, subtree: true });
})();
</script>
</body>
</html>

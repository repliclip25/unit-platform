<!DOCTYPE html>
<html lang="en" class="h-full" id="html-root" data-theme="dark">
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
        /* ── Theme tokens ───────────────────────────────── */
        :root, [data-theme="dark"] {
            --bg-base:     #0d0d0d;
            --bg-surface:  #171717;
            --bg-raised:   #212121;
            --bg-card:     rgba(33,33,33,0.95);
            --border:      rgba(255,255,255,0.09);
            --border-soft: rgba(255,255,255,0.05);
            --text-primary:   #ececec;
            --text-secondary: #b4b4b4;
            --text-muted:     #8e8ea0;
            --text-faint:     #555568;
            --mob-bar-bg:  rgba(13,13,13,0.92);
            --scrollbar-track: #171717;
            --scrollbar-thumb: #2f2f2f;
        }
        [data-theme="light"] {
            --bg-base:     #F5F0E8;
            --bg-surface:  #FDFAF5;
            --bg-raised:   #EDE8DF;
            --bg-card:     rgba(253,250,245,0.97);
            --border:      rgba(0,0,0,0.09);
            --border-soft: rgba(0,0,0,0.05);
            --text-primary:   #1C1917;
            --text-secondary: #44403C;
            --text-muted:     #78716C;
            --text-faint:     #A8A29E;
            --mob-bar-bg:  rgba(253,250,245,0.94);
            --scrollbar-track: #EDE8DF;
            --scrollbar-thumb: #C7BFB5;
        }

        /* ── Base ───────────────────────────────────────── */
        html, body { background: var(--bg-base); color: var(--text-primary); }

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

        /* ── Dark-mode overrides for Tailwind gray classes (remove blue tint, boost text) ── */
        [data-theme="dark"] .bg-gray-950 { background-color: #0d0d0d !important; }
        [data-theme="dark"] .bg-gray-900 { background-color: #171717 !important; }
        [data-theme="dark"] .bg-gray-800 { background-color: #212121 !important; }
        [data-theme="dark"] .bg-gray-700 { background-color: #2f2f2f !important; }
        [data-theme="dark"] .bg-gray-800\/40 { background-color: rgba(33,33,33,0.5) !important; }
        [data-theme="dark"] .bg-gray-800\/60 { background-color: rgba(33,33,33,0.7) !important; }
        [data-theme="dark"] .bg-gray-900\/40 { background-color: rgba(23,23,23,0.5) !important; }

        [data-theme="dark"] .border-gray-800 { border-color: rgba(255,255,255,0.09) !important; }
        [data-theme="dark"] .border-gray-700 { border-color: rgba(255,255,255,0.13) !important; }
        [data-theme="dark"] .border-gray-600 { border-color: rgba(255,255,255,0.18) !important; }
        [data-theme="dark"] .border-gray-700\/40 { border-color: rgba(255,255,255,0.07) !important; }
        [data-theme="dark"] .border-gray-800\/40 { border-color: rgba(255,255,255,0.05) !important; }
        [data-theme="dark"] .divide-gray-800 > * + * { border-color: rgba(255,255,255,0.09) !important; }

        [data-theme="dark"] .text-white    { color: #ececec !important; }
        [data-theme="dark"] .text-gray-100 { color: #e5e5e5 !important; }
        [data-theme="dark"] .text-gray-200 { color: #d4d4d4 !important; }
        [data-theme="dark"] .text-gray-300 { color: #b4b4b4 !important; }
        [data-theme="dark"] .text-gray-400 { color: #9b9b9b !important; }
        [data-theme="dark"] .text-gray-500 { color: #8e8ea0 !important; }
        [data-theme="dark"] .text-gray-600 { color: #8e8ea0 !important; }
        [data-theme="dark"] .text-gray-700 { color: #6b6b80 !important; }

        /* Dark mode input/select fields */
        [data-theme="dark"] input,
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background: rgba(255,255,255,0.05) !important;
            border-color: rgba(255,255,255,0.10) !important;
            color: #ececec !important;
        }

        /* Dark mode button borders */
        [data-theme="dark"] button.border,
        [data-theme="dark"] a.border { border-color: rgba(255,255,255,0.13) !important; }
        [data-theme="dark"] .border-gray-700.text-gray-400 { color: #b4b4b4 !important; border-color: rgba(255,255,255,0.13) !important; }

        /* ── Light-mode: broad cascade — all content text defaults to dark ── */
        [data-theme="light"] #main-content {
            color: #1C1917;
        }
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
        [data-theme="light"] .bg-gray-950 { background-color: #F5F0E8 !important; }
        [data-theme="light"] .bg-gray-900 { background-color: #FDFAF5 !important; }
        [data-theme="light"] .bg-gray-800 { background-color: #EDE8DF !important; }
        [data-theme="light"] .bg-gray-800\/40 { background-color: rgba(237,232,223,0.7) !important; }
        [data-theme="light"] .bg-gray-800\/60 { background-color: rgba(237,232,223,0.85) !important; }
        [data-theme="light"] .bg-gray-700 { background-color: #DDD8CE !important; }
        [data-theme="light"] .bg-gray-900\/40 { background-color: rgba(253,250,245,0.7) !important; }
        [data-theme="light"] .bg-navy-2,
        [data-theme="light"] [class*="bg-navy"] { background-color: #EDE8DF !important; }

        [data-theme="light"] .border-gray-800 { border-color: #D6D0C7 !important; }
        [data-theme="light"] .border-gray-700 { border-color: #C2BBB0 !important; }
        [data-theme="light"] .border-gray-700\/40 { border-color: rgba(194,187,176,0.6) !important; }
        [data-theme="light"] .border-gray-800\/40 { border-color: rgba(214,208,199,0.8) !important; }
        [data-theme="light"] .border-gray-600 { border-color: #A8A29E !important; }

        [data-theme="light"] .text-white    { color: #1C1917 !important; }
        [data-theme="light"] .text-gray-100 { color: #1C1917 !important; }
        [data-theme="light"] .text-gray-200 { color: #292524 !important; }
        [data-theme="light"] .text-gray-300 { color: #44403C !important; }
        [data-theme="light"] .text-gray-400 { color: #57534E !important; }
        [data-theme="light"] .text-gray-500 { color: #78716C !important; }
        [data-theme="light"] .text-gray-600 { color: #78716C !important; }

        /* Override inline style dark colors via attribute selectors */
        [data-theme="light"] [style*="color:#8b91a0"]  { color: #78716C !important; }
        [data-theme="light"] [style*="color:#c9cdd6"]  { color: #44403C !important; }
        [data-theme="light"] [style*="color:#f4f1ea"]  { color: #1C1917 !important; }
        [data-theme="light"] [style*="color:#4a5063"]  { color: #A8A29E !important; }
        [data-theme="light"] [style*="color:var(--text-primary)"]   { color: #1C1917 !important; }
        [data-theme="light"] [style*="color:var(--text-secondary)"] { color: #44403C !important; }
        [data-theme="light"] [style*="color:var(--text-muted)"]     { color: #78716C !important; }
        [data-theme="light"] [style*="color:var(--text-faint)"]     { color: #A8A29E !important; }

        /* Override inline style dark backgrounds */
        [data-theme="light"] [style*="background:#0a0e1a"],
        [data-theme="light"] [style*="background:#171717"]      { background: #FDFAF5 !important; }
        [data-theme="light"] [style*="background:#05070d"],
        [data-theme="light"] [style*="background:#0d0d0d"]      { background: #F5F0E8 !important; }
        [data-theme="light"] [style*="background:#0d1220"],
        [data-theme="light"] [style*="background:#212121"]      { background: #EDE8DF !important; }
        [data-theme="light"] [style*="background:rgba(13,18,32"],
        [data-theme="light"] [style*="background:rgba(33,33,33"] { background: rgba(237,232,223,0.9) !important; }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.05)"] { background: rgba(0,0,0,0.04) !important; }
        [data-theme="light"] [style*="background:rgba(255,255,255,0.07)"] { background: rgba(0,0,0,0.05) !important; }

        /* Border inline overrides */
        [data-theme="light"] [style*="border:1px solid rgba(255,255,255,0.08)"] { border-color: rgba(0,0,0,0.09) !important; }
        [data-theme="light"] [style*="border-color:rgba(255,255,255"] { border-color: rgba(0,0,0,0.09) !important; }

        [data-theme="light"] .divide-gray-800 > * + * { border-color: #D6D0C7 !important; }

        /* Subnav tabs in light mode */
        [data-theme="light"] .border-b.border-gray-800 { border-color: #D6D0C7 !important; }
        [data-theme="light"] nav.border-b { border-color: #D6D0C7 !important; }

        /* Input fields in light mode */
        [data-theme="light"] input,
        [data-theme="light"] select,
        [data-theme="light"] textarea {
            background: #ffffff !important;
            border-color: #C2BBB0 !important;
            color: #1C1917 !important;
        }

        /* Stage/badge chips */
        [data-theme="light"] .bg-brand\/10  { background-color: rgba(243,197,49,0.18) !important; }
        [data-theme="light"] .bg-brand\/15  { background-color: rgba(243,197,49,0.20) !important; }
        [data-theme="light"] .bg-brand\/12  { background-color: rgba(243,197,49,0.18) !important; }
        [data-theme="light"] .bg-brand\/20  { background-color: rgba(243,197,49,0.22) !important; }
        [data-theme="light"] .bg-purple-900 { background-color: rgba(243,197,49,0.12) !important; }
        [data-theme="light"] .text-purple-300 { color: #78580a !important; }

        /* Yellow/brand text → dark brown in light mode for readability */
        [data-theme="light"] .text-brand      { color: #78580a !important; }
        [data-theme="light"] .text-brand-deep { color: #5c4108 !important; }
        [data-theme="light"] [style*="color:#f3c531"] { color: #78580a !important; }
        [data-theme="light"] [style*="color:#d9a91f"] { color: #78580a !important; }
        /* Brand borders in light mode */
        [data-theme="light"] .border-brand\/40 { border-color: rgba(120,88,10,0.35) !important; }
        [data-theme="light"] .border-brand\/30 { border-color: rgba(120,88,10,0.25) !important; }

        /* Status/alert banners with dark inline backgrounds */
        [data-theme="light"] [style*="background:rgba(239,68,68"] { background: rgba(239,68,68,0.12) !important; color: #991B1B !important; }
        [data-theme="light"] [style*="background:rgba(234,179,8"]  { background: rgba(234,179,8,0.12)  !important; color: #78350F !important; }
        [data-theme="light"] [style*="background:rgba(34,197,94"]  { background: rgba(34,197,94,0.12)  !important; color: #14532D !important; }
        [data-theme="light"] [style*="background:rgba(59,130,246"] { background: rgba(59,130,246,0.12) !important; color: #1E3A5F !important; }

        /* ── Dark-mode: fix inline style navy/blue backgrounds ── */
        [data-theme="dark"] [style*="background:#0a0e1a"]       { background: #171717 !important; }
        [data-theme="dark"] [style*="background:#05070d"]       { background: #0d0d0d !important; }
        [data-theme="dark"] [style*="background:#0d1220"]       { background: #212121 !important; }
        [data-theme="dark"] [style*="background:rgba(10,14,26"] { background: rgba(23,23,23,0.9) !important; }
        [data-theme="dark"] [style*="background:rgba(13,18,32"] { background: rgba(33,33,33,0.9) !important; }
        [data-theme="dark"] [style*="color:#8b91a0"]            { color: #8e8ea0 !important; }
        [data-theme="dark"] [style*="color:#4a5063"]            { color: #555568 !important; }
        [data-theme="dark"] [style*="color:#c9cdd6"]            { color: #b4b4b4 !important; }
        [data-theme="dark"] [style*="color:#f4f1ea"]            { color: #ececec !important; }

        /* ── Mobile bar ─────────────────────────────────── */
        #mob-bar         { background: var(--mob-bar-bg); border-bottom: 1px solid var(--border); }
        #mob-bar span    { color: var(--text-primary); }

        /* ── Brand gold buttons always have dark text ──── */
        .bg-brand, [class*="bg-brand"]:not([class*="bg-brand/"]):not([class*="bg-brand-"]) {
            color: #1a1404 !important;
        }
        [style*="background:#f3c531"], [style*="background: #f3c531"] {
            color: #1a1404 !important;
        }

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
        [data-theme="dark"]  .theme-toggle { background: #f3c531; }
        [data-theme="light"] .theme-toggle { background: #cbd5e1; }
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
            const t = localStorage.getItem('unit-theme') || 'dark';
            document.getElementById('html-root').setAttribute('data-theme', t);
        })();
    </script>
</head>
<body class="h-full font-sans antialiased">

{{-- Mobile top bar --}}
<div id="mob-bar" class="lg:hidden fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-4 py-3" style="backdrop-filter:blur(14px)">
    <div class="flex items-center gap-2.5">
        <img src="/logo.png" alt="UNIT" class="w-7 h-7 rounded-md">
        <span class="font-display font-bold text-base">UNIT</span>
    </div>
    <button id="mob-toggle" class="p-1.5 rounded-md" style="background:rgba(128,128,128,0.1)">
        <svg id="mob-icon-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg id="mob-icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

<div class="min-h-screen flex">

    {{-- Sidebar --}}
    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-64 flex flex-col transition-transform -translate-x-full lg:translate-x-0">

        <div class="px-5 py-5 shrink-0" style="border-bottom:1px solid var(--border)">
            <div class="flex items-center gap-3">
                <img src="/logo.png" alt="UNIT" class="w-8 h-8 rounded-md">
                <div>
                    <p class="font-display font-bold text-sm leading-tight" style="color:var(--text-primary)">UNIT Platform</p>
                    <p class="text-xs mt-0.5" style="color:var(--text-faint)">Worker OS</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-5">

            <div>
                <p class="nav-section-label px-3 mb-1.5 text-xs uppercase tracking-widest font-semibold">Platform</p>
                @php
                    $navLink = fn($active) => 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition ' . ($active ? '' : 'nav-link');
                    $navStyle = fn($active) => $active ? 'background:#f3c531;color:#1a1404' : '';
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
                <p class="nav-section-label px-3 mb-1.5 text-xs uppercase tracking-widest font-semibold">Settings</p>
                <a href="{{ route('settings.api-keys') }}" class="{{ $navLink(request()->routeIs('settings.*')) }}" style="{{ $navStyle(request()->routeIs('settings.*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    API Keys
                </a>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.tenants') }}" class="{{ $navLink(request()->routeIs('admin.tenant*')) }}" style="{{ $navStyle(request()->routeIs('admin.tenant*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Tenant Controls
                </a>
                <a href="{{ route('admin.influencers') }}" class="{{ $navLink(request()->routeIs('admin.influencer*')) }}" style="{{ $navStyle(request()->routeIs('admin.influencer*')) }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Influencers
                </a>
                @endif
            </div>

            <div>
                <div class="px-3 mb-1.5 flex items-center justify-between">
                    <p class="nav-section-label text-xs uppercase tracking-widest font-semibold">Workers</p>
                    <a href="{{ route('workers.deploy') }}" class="text-xs font-semibold" style="color:#f3c531">+ Deploy</a>
                </div>
                @forelse($deployments as $dep)
                    @php $isActive = request()->segment(2) == $dep->id; @endphp
                    <a href="{{ route('workers.show', $dep->id) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ $isActive ? '' : 'nav-link' }}"
                       style="{{ $isActive ? 'background:#f3c531;color:#1a1404' : '' }}">
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
                        <p class="text-xs mb-1" style="color:var(--text-faint)">No workers deployed</p>
                        <a href="{{ route('workers.deploy') }}" class="text-xs font-semibold" style="color:#f3c531">Deploy your first →</a>
                    </div>
                @endforelse
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
                    <span class="text-xs" style="color:var(--text-faint)">Workers</span>
                    <span class="text-xs" style="color:var(--text-muted)">{{ $deployments->count() }} deployed</span>
                </div>
            </div>
            <div class="flex items-center justify-between px-1">
                <span class="text-xs truncate" style="color:var(--text-muted)">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-xs transition hover:opacity-80" style="color:var(--text-faint)">Sign out</button>
                </form>
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
    const saved = localStorage.getItem('unit-theme') || 'dark';
    applyTheme(saved);

    document.getElementById('theme-toggle').addEventListener('click', function () {
        const next = document.getElementById('html-root').getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        localStorage.setItem('unit-theme', next);
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
</body>
</html>

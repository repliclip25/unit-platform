@props(['stepName' => '', 'sequence' => [], 'stepIndex' => -1, 'wide' => false])

<!DOCTYPE html>
<html lang="en" id="ob-html" data-theme="light" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Get started — UNIT</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{
            --accent:#f1d362;--accent-rgb:241,211,98;--accent-text:#f1d362;
            --bg-card:#0d1117;--bg-surface:#111827;--bg-raised:#1a2230;
            --border:rgba(255,255,255,0.10);--border-subtle:rgba(255,255,255,0.07);
            --text-primary:#f9fafb;--text-secondary:#9ca3af;--text-muted:#6b7280;--text-faint:#374151;
        }
        [data-theme="light"]{
            --accent:#f1d362;--accent-rgb:241,211,98;--accent-text:#7a5c00;
            --bg-card:#ffffff;--bg-surface:#f8fafc;--bg-raised:#f1f5f9;
            --border:rgba(0,0,0,0.09);--border-subtle:#e8e8e6;
            --text-primary:#0f172a;--text-secondary:#475569;--text-muted:#64748b;--text-faint:#cbd5e1;
        }
        body{background:#030712;color:#f9fafb;transition:background .25s,color .2s}
        [data-theme="light"] body,[data-theme="light"]>body{background:#f8fafc;color:#0f172a}
        [x-cloak]{display:none!important}
    </style>
    <script>(function(){const t=localStorage.getItem('unit-theme-v2')||'light';document.getElementById('ob-html').setAttribute('data-theme',t);})()</script>
</head>
<body class="h-full font-sans antialiased">

<div class="min-h-screen flex flex-col">

    {{-- Top bar --}}
    <div class="flex items-center justify-between px-8 py-5 border-b border-gray-800 shrink-0">
        <div class="flex items-center gap-2.5">
            <img src="/logo.png" alt="UNIT" class="w-8 h-8 rounded-md">
            <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:1.1rem;color:var(--accent)">UNIT</span>
        </div>

        {{-- Progress pills — only when a sequence is available --}}
        @if(count($sequence) > 0)
        <div class="hidden sm:flex items-center gap-1.5">
            @foreach($sequence as $i => $step)
                @php
                    $isActive   = $i === $stepIndex;
                    $isComplete = $i < $stepIndex;
                @endphp
                <div class="flex items-center gap-1.5">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                        {{ $isComplete ? 'bg-yellow-400 text-gray-950' : ($isActive ? 'bg-gray-900 border-2 border-yellow-400 text-yellow-400' : 'bg-gray-900 border border-gray-700 text-gray-600') }}">
                        @if($isComplete)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>
                    @if($i < count($sequence) - 1)
                        <div class="w-5 h-px {{ $isComplete ? 'bg-yellow-400' : 'bg-gray-800' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Mobile step count --}}
        <div class="sm:hidden text-gray-600 text-sm">
            @if($stepIndex >= 0) Step {{ $stepIndex + 1 }} of {{ count($sequence) }} @endif
        </div>
        @else
        <div></div>
        @endif

        <div class="flex items-center gap-4">
            {{-- Theme toggle --}}
            <button id="ob-theme-btn" type="button" data-no-loading
                    title="Toggle dark/light"
                    class="w-8 h-8 rounded-lg border border-gray-800 flex items-center justify-center text-gray-600 hover:text-gray-400 hover:border-gray-700 transition">
                <svg id="ob-theme-sun" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <circle cx="12" cy="12" r="5"/><path stroke-linecap="round" d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
                <svg id="ob-theme-moon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
            </button>
            <a href="{{ route('onboarding.skip') }}"
               onclick="return confirm('Skip setup? You can always complete it from your dashboard.')"
               class="hidden sm:block text-gray-700 hover:text-gray-500 text-xs transition-colors whitespace-nowrap">
                Skip setup →
            </a>
        </div>
    </div>

    {{-- Content --}}
    <div class="flex-1 flex items-start justify-center px-4 py-10">
        <div class="w-full {{ $wide ? 'max-w-4xl' : 'max-w-lg' }}">

            @if(session('success'))
                <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            {{ $slot }}

        </div>
    </div>

</div>

<script>
(function(){
    const html=document.getElementById('ob-html');
    const sun=document.getElementById('ob-theme-sun');
    const moon=document.getElementById('ob-theme-moon');
    function applyTheme(t){
        html.setAttribute('data-theme',t);
        if(sun){sun.classList.toggle('hidden',t==='dark');moon.classList.toggle('hidden',t==='light');}
        localStorage.setItem('unit-theme-v2',t);
    }
    applyTheme(localStorage.getItem('unit-theme-v2')||'light');
    document.getElementById('ob-theme-btn')?.addEventListener('click',function(){
        applyTheme(html.getAttribute('data-theme')==='dark'?'light':'dark');
    });
})();
</script>
<script>
(function(){
    const SP='<svg style="width:1em;height:1em;display:inline-block;vertical-align:-0.15em;animation:spin 0.7s linear infinite;margin-right:0.35em" fill="none" viewBox="0 0 24 24"><circle style="opacity:.3" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.85" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>';
    const style=document.createElement('style');
    style.textContent='@keyframes spin{to{transform:rotate(360deg)}}';
    document.head.appendChild(style);
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

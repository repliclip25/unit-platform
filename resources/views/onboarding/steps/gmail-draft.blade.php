<!DOCTYPE html>
<html lang="en" id="ob-html" data-theme="light" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your draft is ready — UNIT</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{
            --accent:#142C74;--accent-rgb:20,44,116;--accent-text:#ffffff;
            --bg-card:#0d1117;--bg-surface:#111827;--bg-raised:#1a2230;
            --border:rgba(255,255,255,0.10);--border-subtle:rgba(255,255,255,0.07);
            --text-primary:#f9fafb;--text-secondary:#9ca3af;--text-muted:#6b7280;--text-faint:#374151;
        }
        [data-theme="light"]{
            --accent:#142C74;--accent-rgb:20,44,116;--accent-text:#ffffff;
            --bg-card:#ffffff;--bg-surface:#f8fafc;--bg-raised:#f1f5f9;
            --border:rgba(0,0,0,0.09);--border-subtle:#e8e8e6;
            --text-primary:#0f172a;--text-secondary:#475569;--text-muted:#64748b;--text-faint:#cbd5e1;
        }
        body{background:#030712;color:#f9fafb}
        [data-theme="light"] body{background:#f8fafc;color:#0f172a}
        [x-cloak]{display:none!important}
        @keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
        .fade-up{animation:fadeUp .55s ease both}
        .fade-up-2{animation:fadeUp .55s .12s ease both}
        .fade-up-3{animation:fadeUp .55s .24s ease both}
        .fade-up-4{animation:fadeUp .55s .38s ease both}
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
        <div></div>
    </div>

    {{-- Content --}}
    <div class="flex-1 flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-lg">

            {{-- Eyebrow --}}
            <p class="fade-up text-center text-xs font-bold uppercase tracking-widest mb-6" style="color:var(--accent-text)">
                Draft ready
            </p>

            {{-- Heading --}}
            <div class="fade-up-2 text-center mb-8">
                <h1 class="text-3xl font-black text-white mb-3 leading-tight">Right where you'd expect it.</h1>
                <p class="text-gray-400 text-sm leading-relaxed">Ava never sends emails on her own.</p>
                <p class="text-gray-400 text-sm leading-relaxed mt-1">She prepares them.</p>
                <p class="text-gray-400 text-sm leading-relaxed mt-1">You stay in complete control.</p>
                <p class="text-gray-400 text-sm leading-relaxed mt-3">
                    Review the draft, make changes if you'd like, and click Send whenever you're ready.
                </p>
            </div>

            {{-- Gmail drafts mockup --}}
            <div class="fade-up-3 rounded-2xl overflow-hidden border border-gray-800 bg-gray-950 mb-6">

                {{-- Window chrome --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800 bg-gray-900">
                    <div class="flex items-center gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-500/70"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400/70"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500/70"></div>
                    </div>
                    <span class="text-xs text-gray-600 font-medium">Gmail — Drafts</span>
                    <div class="w-16"></div>
                </div>

                {{-- Draft row --}}
                <div class="px-5 py-4 border-b border-gray-800/60 flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                         style="background:rgba(var(--accent-rgb),.12)">
                        <svg class="w-4 h-4" style="color:var(--accent-text)" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-white text-sm font-semibold truncate">
                                {{ $draft['subject'] ?? 'Renewal Reply — Ava\'s draft' }}
                            </span>
                            <span class="text-xs px-1.5 py-0.5 rounded font-semibold shrink-0"
                                  style="background:rgba(var(--accent-rgb),.15);color:var(--accent-text)">Draft</span>
                        </div>
                        <p class="text-gray-500 text-xs truncate">
                            {{ $draft['client'] ? 'For ' . $draft['client'] . ' — ' : '' }}Ava drafted this reply for your review — no real email was sent.
                        </p>
                    </div>
                    <span class="text-gray-700 text-xs shrink-0 mt-0.5">Just now</span>
                </div>

                {{-- Draft preview --}}
                <div class="px-5 py-4 space-y-1.5">
                    @if($draft['to_name'] ?? null)
                    <p class="text-gray-500 text-xs">Hi {{ $draft['to_name'] }},</p>
                    @endif
                    <p class="text-gray-500 text-xs leading-relaxed">
                        {{ $draft['body_snippet'] ?? 'Ava has prepared a professional renewal response based on your client data and email templates.' }}
                    </p>
                    <p class="text-gray-600 text-xs italic mt-2">— Ava drafted this for your review</p>
                </div>

                {{-- Bottom bar --}}
                <div class="px-5 py-3 border-t border-gray-800/60 flex items-center justify-between">
                    <span class="text-gray-700 text-xs">1 draft in Gmail</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-1 rounded-lg border text-gray-600 border-gray-800">Edit</span>
                        <span class="text-xs px-2 py-1 rounded-lg font-semibold"
                              style="background:rgba(var(--accent-rgb),.15);color:var(--accent-text);border:1px solid rgba(var(--accent-rgb),.2)">Send</span>
                    </div>
                </div>
            </div>

            {{-- CTA --}}
            <div class="fade-up-4">
                <a href="{{ route('onboarding.complete') }}"
                   class="block w-full text-center font-black text-base py-4 rounded-xl transition-all"
                   style="background:var(--accent);color:#ffffff">
                    Enter Workspace
                </a>
                <p class="text-center text-xs text-gray-600 mt-3">
                    Your draft is waiting in Gmail Drafts. Ava is now on duty.
                </p>
            </div>

        </div>
    </div>

</div>

<script>
(function(){
    const t=localStorage.getItem('unit-theme-v2')||'light';
    document.getElementById('ob-html').setAttribute('data-theme',t);
})();
</script>
</body>
</html>

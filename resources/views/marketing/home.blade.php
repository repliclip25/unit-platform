<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIT — AI Workforce Platform for Construction</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: radial-gradient(ellipse at 50% 100%, #0a2040 0%, #050c1a 55%, #020710 100%); }
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
        .float   { animation: float 3.5s ease-in-out infinite; }
        .float-1 { animation: float 3.5s ease-in-out 0.5s infinite; }
        .float-2 { animation: float 3.5s ease-in-out 1.0s infinite; }
        .float-3 { animation: float 3.5s ease-in-out 1.5s infinite; }
        .glass { background: rgba(8,16,32,0.85); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="text-white font-sans antialiased min-h-screen flex flex-col">

    {{-- Nav --}}
    <nav class="flex items-center justify-between px-8 py-5 border-b border-white/5 max-w-7xl mx-auto w-full">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded flex items-center justify-center relative" style="background:var(--accent)">
                <div class="w-4 h-4 bg-gray-900 rounded-sm"></div>
            </div>
            <span class="font-black text-xl tracking-tight text-white">UNIT</span>
        </div>
        <div class="hidden md:flex items-center gap-8 text-sm text-gray-400">
            <a href="#how-it-works" class="hover:text-white transition">How It Works</a>
            <a href="#use-cases" class="hover:text-white transition">Use Cases</a>
            <a href="#pricing" class="hover:text-white transition">Pricing</a>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-white transition px-4 py-2">Sign in</a>
            <a href="{{ route('register') }}" class="text-sm font-bold px-5 py-2.5 rounded-lg text-gray-900 transition" style="background:var(--accent)">
                Get Started
            </a>
        </div>
    </nav>

    {{-- Hero --}}
    <div class="flex-1 flex items-center px-8 py-16 max-w-7xl mx-auto w-full gap-16">

        {{-- Left --}}
        <div class="flex-1 max-w-xl">
            <div class="inline-flex items-center gap-2 border rounded-full px-4 py-1.5 text-xs font-semibold mb-8 tracking-wide" style="border-color:var(--accent);color:var(--accent)">
                <span class="w-1.5 h-1.5 rounded-full inline-block" style="background:var(--accent)"></span>
                AI WORKFORCE PLATFORM FOR CONSTRUCTION
            </div>

            <h1 class="text-5xl md:text-6xl font-black leading-[1.05] mb-5">
                Deploy Digital<br>Workers.
                <br><span style="color:var(--accent)">Get Work Done.</span>
            </h1>

            <p class="text-gray-400 text-lg leading-relaxed mb-3">
                UNIT deploys specialized AI workers that handle the operational work that slows construction teams down.
                Built for construction. Trained on real workflows.
            </p>
            <p class="font-bold mb-8" style="color:var(--accent)">Always in your control.</p>

            <div class="flex gap-3 mb-6">
                <div class="flex-1 flex items-center gap-3 rounded-xl px-4 py-3 border border-white/10" style="background:rgba(255,255,255,0.04)">
                    <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="text-gray-500 text-sm">Enter your work email</span>
                </div>
                <a href="{{ route('register') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-gray-900 whitespace-nowrap" style="background:var(--accent)">
                    Join the Waitlist
                </a>
            </div>

            <div class="flex items-center gap-2 text-gray-600 text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Secure. Connected. Built for construction.
            </div>
        </div>

        {{-- Right — live worker feed --}}
        <div class="hidden lg:block relative flex-1 max-w-md">
            <div class="flex flex-col gap-4">
                <div class="glass border border-white/10 rounded-2xl px-5 py-4 float" style="border-left:3px solid var(--accent)">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-xs shrink-0" style="background:rgba(var(--accent-rgb),0.15);color:var(--accent)">RC</div>
                        <div class="flex-1">
                            <p class="text-white text-sm font-semibold">Renewal Coordinator</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 mt-0.5"><span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span><span class="text-green-400 text-xs">Working</span></div>
                                <span class="text-gray-600 text-xs">Reviewing 12 renewals</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass border border-white/10 rounded-2xl px-5 py-4 float-1 ml-8" style="border-left:3px solid var(--accent)">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-xs shrink-0" style="background:rgba(var(--accent-rgb),0.15);color:var(--accent)">TC</div>
                        <div class="flex-1">
                            <p class="text-white text-sm font-semibold">Transmittal Coordinator</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 mt-0.5"><span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span><span class="text-green-400 text-xs">Working</span></div>
                                <span class="text-gray-600 text-xs">Processing 8 submittals</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass border border-white/10 rounded-2xl px-5 py-4 float-2" style="border-left:3px solid var(--accent)">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-xs shrink-0" style="background:rgba(var(--accent-rgb),0.15);color:var(--accent)">CO</div>
                        <div class="flex-1">
                            <p class="text-white text-sm font-semibold">Change Order Coordinator</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 mt-0.5"><span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span><span class="text-green-400 text-xs">Working</span></div>
                                <span class="text-gray-600 text-xs">Reviewing CO-1274</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass border border-white/10 rounded-2xl px-5 py-4 float-3 ml-8" style="border-left:3px solid var(--accent)">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-xs shrink-0" style="background:rgba(var(--accent-rgb),0.15);color:var(--accent)">CP</div>
                        <div class="flex-1">
                            <p class="text-white text-sm font-semibold">Certified Payroll Coordinator</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5 mt-0.5"><span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span><span class="text-green-400 text-xs">Working</span></div>
                                <span class="text-gray-600 text-xs">Validating payroll package</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute -bottom-6 right-0 glass border border-white/10 rounded-lg px-3 py-1.5 text-xs tracking-widest" style="color:var(--accent)">
                ● ACTIVE DEPLOYMENT ZONE
            </div>
        </div>
    </div>

    {{-- Trust bar --}}
    <div class="border-t border-white/5 py-8 px-8">
        <p class="text-center text-gray-600 text-xs uppercase tracking-widest mb-6">Trusted by Construction Professionals</p>
        <div class="flex items-center justify-center gap-10 flex-wrap max-w-3xl mx-auto">
            @foreach(['BuildCo', 'BluePeak Construction', 'Northline Services', 'Vertex Solutions', 'Stonegate Group'] as $co)
                <span class="text-gray-600 font-semibold text-sm tracking-wide">{{ $co }}</span>
            @endforeach
        </div>
    </div>

</body>
</html>

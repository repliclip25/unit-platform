<x-guest-layout>
    @php
        $workerIntent = request()->query('worker') ?: old('worker');
        $workerMeta = [
            'ava'  => ['label' => 'AVA', 'role' => 'Renewal & Subscription Coordinator', 'icon' => '🤖', 'color' => 'var(--accent)'],
            'nova' => ['label' => 'NOVA', 'role' => 'Invoice & Payment Processor', 'icon' => '💡', 'color' => '#818cf8'],
            'rex'  => ['label' => 'REX', 'role' => 'Document Review Specialist', 'icon' => '📋', 'color' => '#34d399'],
        ];
        $wm = $workerIntent && isset($workerMeta[$workerIntent]) ? $workerMeta[$workerIntent] : null;
    @endphp

    <div class="text-center mb-7">
        @if($wm)
            <div class="inline-flex items-center gap-2 border rounded-full px-4 py-1.5 text-xs font-semibold mb-4 tracking-wide" style="border-color:rgba(var(--accent-rgb),0.4);color:var(--accent);background:rgba(var(--accent-rgb),0.08)">
                <span class="w-1.5 h-1.5 rounded-full inline-block animate-pulse" class="ac-bg"></span>
                DEPLOYING {{ strtoupper($wm['label']) }}
            </div>
            <h1 class="font-display font-bold text-xl mb-1">Set up {{ $wm['label'] }} in minutes</h1>
            <p class="auth-muted text-sm auth-text-dark">{{ $wm['role'] }} — free to start, no card needed</p>
        @else
            <div class="inline-flex items-center gap-2 border rounded-full px-4 py-1.5 text-xs font-semibold mb-4 tracking-wide" style="border-color:rgba(var(--accent-rgb),0.5);color:var(--accent);background:rgba(var(--accent-rgb),0.08)">
                <span class="w-1.5 h-1.5 rounded-full inline-block" class="ac-bg"></span>
                AI WORKFORCE PLATFORM
            </div>
            <h1 class="font-display font-bold text-xl mb-1">Hire your first employee</h1>
            <p class="auth-muted text-sm auth-text-dark">Create your UNIT workspace — free to start</p>
        @endif
    </div>

    {{-- OAuth buttons --}}
    <div class="mb-6">
        <a href="{{ route('oauth.redirect', 'google') }}{{ $workerIntent ? '?worker='.$workerIntent : '' }}{{ request()->query('ref') ? ($workerIntent ? '&' : '?').'ref='.request()->query('ref') : '' }}" class="flex items-center justify-center gap-3 w-full rounded-xl px-4 py-2.5 text-sm font-semibold transition" style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-primary)">
            <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            Continue with Google
        </a>
    </div>

    <div class="relative mb-6">
        <div class="absolute inset-0 flex items-center"><div class="w-full" style="border-top:1px solid var(--border)"></div></div>
        <div class="relative flex justify-center text-xs"><span class="px-3 text-xs" style="background:var(--bg-card);color:var(--text-muted)">or register with email</span></div>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="worker" value="{{ $workerIntent }}">
        @if(request()->query('ref'))
            <input type="hidden" name="ref" value="{{ request()->query('ref') }}">
        @endif
        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Jane Smith" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-xs text-red-400" />
        </div>
        <div>
            <x-input-label for="email" :value="__('Work email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-400" />
        </div>
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 characters" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-400" />
        </div>
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs text-red-400" />
        </div>
        <div class="pt-1">
            <x-primary-button>Create workspace</x-primary-button>
        </div>
        <p class="auth-muted text-xs text-center auth-text-dark">
            By registering you agree to our Terms of Service and Privacy Policy.
        </p>
    </form>

    <p class="text-center mt-5 text-xs auth-muted">
        Already have an account? <a href="{{ route('login') }}" class="auth-link font-semibold">Sign in →</a>
    </p>
</x-guest-layout>

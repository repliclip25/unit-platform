<x-guest-layout>
    <div class="text-center mb-7">
        <h1 class="font-display font-bold text-xl mb-1">Welcome back</h1>
        <p class="auth-muted text-sm auth-text-dark">Sign in to your UNIT workspace</p>
    </div>

    @if(session('status'))
        <div class="mb-5 text-sm rounded-xl px-4 py-3" style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#86efac">
            {{ session('status') }}
        </div>
    @endif

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <div>
            <x-input-label for="email" :value="__('Work email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-400" />
        </div>
        <div>
            <div class="flex justify-between mb-1.5">
                <x-input-label for="password" :value="__('Password')" class="mb-0" />
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-link text-xs font-semibold">Forgot password?</a>
                @endif
            </div>
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-400" />
        </div>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" style="accent-color:#f3c531;border-radius:4px">
            <span class="auth-muted text-xs auth-text-dark">Remember me</span>
        </label>
        <x-primary-button>Sign in</x-primary-button>
    </form>

    @if(Route::has('register'))
    <p class="text-center mt-6 text-xs auth-muted">
        No account? <a href="{{ route('register') }}" class="auth-link font-semibold">Get started free →</a>
    </p>
    @endif
</x-guest-layout>

<x-guest-layout>
    <div class="text-center mb-7">
        <h1 class="font-display font-bold text-xl mb-1">Reset your password</h1>
        <p class="auth-muted text-sm auth-text-dark">Enter your email and we'll send a reset link.</p>
    </div>

    @if(session('status'))
        <div class="mb-5 text-sm rounded-xl px-4 py-3" style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#86efac">
            {{ session('status') }}
        </div>
    @endif

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus placeholder="you@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-400" />
        </div>
        <x-primary-button>Email Reset Link</x-primary-button>
    </form>

    <p class="text-center mt-6 text-xs auth-muted">
        Remember it? <a href="{{ route('login') }}" class="auth-link font-semibold">Back to sign in</a>
    </p>
</x-guest-layout>

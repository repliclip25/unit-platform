<x-guest-layout>
    <div class="text-center mb-7">
        <div class="inline-flex items-center gap-2 border rounded-full px-4 py-1.5 text-xs font-semibold mb-4 tracking-wide" style="border-color:rgba(243,197,49,0.5);color:#f3c531;background:rgba(243,197,49,0.08)">
            <span class="w-1.5 h-1.5 rounded-full inline-block" style="background:#f3c531"></span>
            AI WORKFORCE PLATFORM
        </div>
        <h1 class="font-display font-bold text-xl mb-1">Deploy your first worker</h1>
        <p class="auth-muted text-sm auth-text-dark">Create your UNIT workspace — free to start</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
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

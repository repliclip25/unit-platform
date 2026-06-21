<x-guest-layout>
    <div class="text-center mb-7">
        <h1 class="font-display font-bold text-xl mb-1">Confirm your password</h1>
        <p class="auth-muted text-sm auth-text-dark">This is a secure area. Please confirm your password to continue.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-400" />
        </div>
        <x-primary-button>Confirm & Continue</x-primary-button>
    </form>
</x-guest-layout>

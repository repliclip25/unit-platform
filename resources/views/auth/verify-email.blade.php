<x-guest-layout>
    <div class="text-center mb-7">
        <div class="w-14 h-14 rounded-full mx-auto mb-4 flex items-center justify-center" style="background:rgba(var(--accent-rgb),0.12);border:1px solid rgba(var(--accent-rgb),0.3)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg>
        </div>
        <h1 class="font-display font-bold text-xl mb-1">Check your email</h1>
        <p class="auth-muted text-sm auth-text-dark max-w-xs mx-auto">
            We sent a verification link to your email address. Click the link to activate your account.
        </p>
    </div>

    @if(session('status') == 'verification-link-sent')
        <div class="mb-5 text-sm rounded-xl px-4 py-3 text-center" style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#86efac">
            A new verification link has been sent to your email.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <x-primary-button>Resend Verification Email</x-primary-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="auth-link text-xs font-semibold">Sign out of this account</button>
    </form>
</x-guest-layout>

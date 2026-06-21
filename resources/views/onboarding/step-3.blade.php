<x-onboarding-layout :step="$step">

@php
    $credLabel      = $credentialInfo['label'] ?? 'Account';
    $credHint       = $credentialInfo['hint'] ?? 'Your worker will monitor this account.';
    $authorizeRoute = $credentialInfo['authorize_route'] ?? null;
@endphp

<div class="mb-8">
    <p class="text-yellow-400 text-sm font-semibold uppercase tracking-widest mb-2">Step 2 of 4</p>
    <h1 class="text-2xl font-black text-white mb-2">Connect your {{ $credLabel }}</h1>
    <p class="text-gray-400">{{ $credHint }}</p>
</div>

@if($hasCredential)
<div class="flex items-center gap-3 bg-green-500/10 border border-green-500/20 rounded-xl px-5 py-4 mb-6">
    <div class="w-8 h-8 bg-green-500/20 rounded-full flex items-center justify-center">
        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>
    <div>
        <p class="text-green-400 font-semibold text-sm">{{ $credLabel }} connected</p>
        <p class="text-gray-500 text-xs">{{ $credentialEmail }}</p>
    </div>
</div>
<form method="POST" action="{{ route('onboarding.4') }}">
    @csrf
    <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-bold text-base py-4 rounded-xl transition-colors">
        Continue →
    </button>
</form>
@else
<div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-6 mb-6 text-center">
    <div class="w-14 h-14 bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl">📬</div>
    <p class="text-white font-semibold mb-1">Link your {{ $credLabel }}</p>
    <p class="text-gray-500 text-sm mb-6">You'll be redirected to authorize access. We only read relevant emails for your worker.</p>

    @if($authorizeRoute)
    <form method="POST" action="{{ route('onboarding.3') }}">
        @csrf
        <button type="submit"
            class="inline-flex items-center gap-3 bg-white hover:bg-gray-100 text-gray-900 font-semibold text-sm px-6 py-3 rounded-xl transition-colors">
            <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            Connect with Google
        </button>
    </form>
    @endif
</div>

<div class="text-center">
    <a href="{{ route('onboarding.3.skip') }}" class="text-gray-600 hover:text-gray-400 text-sm transition-colors">
        Skip for now — I'll connect later
    </a>
</div>
@endif

<div class="mt-8 bg-gray-900/50 border border-gray-800 rounded-xl px-5 py-4">
    <p class="text-gray-500 text-xs leading-relaxed">
        🔒 <strong class="text-gray-400">Secure OAuth 2.0.</strong> UNIT never stores your password. You can disconnect at any time from your account settings.
    </p>
</div>

</x-onboarding-layout>

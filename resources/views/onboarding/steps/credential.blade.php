<x-onboarding-layout :stepName="$stepName" :sequence="$sequence" :stepIndex="$stepIndex">

@if($isMultiCredential)
{{-- ── Multi-slot: NUX-style (LinkedIn + X) ────────────────────────────── --}}
<div class="mb-8">
    <h1 class="text-2xl font-black text-white mb-2">Connect your social accounts</h1>
    <p style="color:var(--text-muted)">NUX watches these accounts for new posts to repurpose.</p>
</div>

@foreach($slots as $slot)
@php
    $connected = in_array($slot['key'], $connectedSlots);
    $icon = $slot['key'] === 'linkedin'
        ? '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>'
        : '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.736l7.73-8.835L1.254 2.25H8.08l4.259 5.626 5.905-5.626zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>';
@endphp
<div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 mb-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white"
             style="background:{{ $slot['key'] === 'linkedin' ? 'rgba(10,102,194,.2)' : 'rgba(255,255,255,.1)' }}">
            {!! $icon !!}
        </div>
        <div>
            <p class="text-white font-semibold text-sm">{{ $slot['label'] }}</p>
            <p class="text-xs" style="color:var(--text-muted)">{{ $slot['hint'] }}</p>
        </div>
    </div>
    @if($connected)
    <span class="text-green-400 text-xs font-semibold flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Connected
    </span>
    @else
    <form method="POST" action="{{ route('onboarding.step.handle', 'credential') }}">
        @csrf
        <input type="hidden" name="slot" value="{{ $slot['key'] }}">
        <button type="submit"
                class="text-xs font-semibold px-4 py-2 rounded-lg transition-colors"
                class="ac-on">
            Connect →
        </button>
    </form>
    @endif
</div>
@endforeach

<div class="mt-6">
    <form method="POST" action="{{ route('onboarding.step.handle', 'credential') }}">
        @csrf
        <button type="submit"
                class="w-full font-bold text-base py-4 rounded-xl transition-colors"
                class="ac-on">
            Continue →
        </button>
    </form>
    <div class="text-center mt-4">
        <form method="POST" action="{{ route('onboarding.step.handle', 'credential') }}" class="inline">
            @csrf
            <input type="hidden" name="skip" value="1">
            <button type="submit" class="text-sm transition-colors" style="color:var(--text-muted)">
                Skip for now — I'll connect later
            </button>
        </form>
    </div>
</div>

@else
{{-- ── Single-slot: AVA-style (Gmail OAuth) ────────────────────────────── --}}
@php
    $credLabel      = $credentialInfo['label'] ?? 'Account';
    $credHint       = $credentialInfo['hint'] ?? 'Your worker will monitor this account.';
    $authorizeRoute = $credentialInfo['authorize_route'] ?? null;
@endphp

<div class="mb-6">
    <p class="text-xs font-semibold uppercase tracking-widest mb-3" class="ac-text">Step 1 of 4 — Inbox access</p>
    <h1 class="text-2xl font-black text-white mb-2">Connect your {{ $credLabel }}</h1>
    <p class="text-sm leading-relaxed" style="color:var(--text-muted)">{{ $credHint }} AVA watches your inbox 24/7 — every renewal email that arrives gets classified, matched to a client, and drafted automatically.</p>
</div>

@if($hasCredential)
<div class="flex items-center gap-3 rounded-xl px-5 py-4 mb-6"
     style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2)">
    <div class="w-8 h-8 rounded-full flex items-center justify-center"
         style="background:rgba(34,197,94,.2)">
        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>
    <div>
        <p class="text-green-400 font-semibold text-sm">{{ $credLabel }} connected</p>
        <p class="text-xs" style="color:var(--text-muted)">{{ $credentialEmail }}</p>
    </div>
</div>
<form method="POST" action="{{ route('onboarding.step.handle', 'credential') }}">
    @csrf
    <button type="submit"
            class="w-full font-bold text-base py-4 rounded-xl transition-colors"
            class="ac-on">
        Continue →
    </button>
</form>
@else
<div class="rounded-xl px-5 py-6 mb-6 text-center"
     style="background:var(--bg-card);border:1px solid var(--border)">
    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl"
         style="background:var(--bg-surface)">📬</div>
    <p class="text-white font-semibold mb-1">Link your {{ $credLabel }}</p>
    <p class="text-sm mb-6" style="color:var(--text-muted)">You'll be redirected to authorize access. We only read relevant emails for your worker.</p>

    @if($authorizeRoute)
    <form method="POST" action="{{ route('onboarding.step.handle', 'credential') }}">
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
    <form method="POST" action="{{ route('onboarding.step.handle', 'credential') }}" class="inline">
        @csrf
        <input type="hidden" name="skip" value="1">
        <button type="submit" class="text-sm transition-colors" style="color:var(--text-muted)">
            Skip for now — I'll connect later
        </button>
    </form>
</div>
@endif
@endif

<div class="mt-8 rounded-xl px-5 py-4"
     style="background:var(--bg-card);border:1px solid var(--border)">
    <p class="text-xs leading-relaxed" style="color:var(--text-muted)">
        🔒 <strong style="color:var(--text-secondary)">Secure OAuth 2.0.</strong> UNIT never stores your password. You can disconnect at any time from your account settings.
    </p>
</div>

</x-onboarding-layout>

<x-onboarding-layout :stepName="$stepName" :sequence="$sequence" :stepIndex="$stepIndex">

@php
    $authorizeRoute = $credentialInfo['authorize_route'] ?? null;
@endphp

{{-- Eyebrow --}}
<p class="text-xs font-bold uppercase tracking-widest mb-5" class="ac-text">
    Step 1 of 4 &nbsp;·&nbsp; Give Ava access
</p>

{{-- Heading --}}
<div class="mb-6">
    <h1 class="text-2xl font-black text-white mb-3 leading-snug">Give Ava access to her workspace.</h1>
    <p class="text-gray-400 text-sm leading-relaxed">Every employee needs a desk before they can work.</p>
    <p class="text-gray-400 text-sm leading-relaxed mt-1">For Ava, that's your Gmail inbox.</p>
    <p class="text-gray-400 text-sm leading-relaxed mt-2">
        Connecting Gmail lets her watch for renewal emails, prepare draft replies, and organize work without changing anything unless you approve it.
    </p>
</div>

@if($hasCredential)
{{-- Already connected --}}
<div class="flex items-center gap-3 rounded-xl px-5 py-4 mb-6"
     style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2)">
    <div class="w-8 h-8 rounded-full flex items-center justify-center"
         style="background:rgba(34,197,94,.2)">
        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>
    <div>
        <p class="text-green-400 font-semibold text-sm">Gmail connected</p>
        <p class="text-xs" style="color:var(--text-muted)">{{ $credentialEmail }}</p>
    </div>
</div>
<form method="POST" action="{{ route('onboarding.step.handle', 'gmail') }}">
    @csrf
    <button type="submit"
            class="w-full font-bold text-base py-4 rounded-xl transition-colors"
            class="ac-on">
        Continue
    </button>
</form>

@else
{{-- Not yet connected --}}
<div class="rounded-xl px-5 py-6 mb-5 text-center"
     style="background:var(--bg-card);border:1px solid var(--border)">
    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl"
         style="background:var(--bg-surface)">📬</div>
    <p class="text-white font-semibold text-sm mb-4">Connect your Gmail account</p>

    @if($authorizeRoute)
    <form method="POST" action="{{ route('onboarding.step.handle', 'gmail') }}">
        @csrf
        <button type="submit"
            class="inline-flex items-center gap-3 bg-white hover:bg-gray-100 text-gray-900 font-semibold text-sm px-6 py-3 rounded-xl transition-colors">
            <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            Connect Gmail
        </button>
    </form>
    @endif
</div>

{{-- Security --}}
<div class="rounded-xl px-5 py-4 mb-5" style="background:var(--bg-card);border:1px solid var(--border)">
    <p class="text-xs leading-relaxed" style="color:var(--text-muted)">
        🔒 <strong style="color:var(--text-secondary)">Your password is never shared with UNIT.</strong>
        Google securely handles authentication using OAuth.
        You can disconnect your account anytime.
    </p>
</div>

<div class="text-center">
    <form method="POST" action="{{ route('onboarding.step.handle', 'gmail') }}" class="inline">
        @csrf
        <input type="hidden" name="skip" value="1">
        <button type="submit" class="text-sm transition-colors" style="color:var(--text-muted)">
            I'll do this later
        </button>
    </form>
</div>
@endif

</x-onboarding-layout>

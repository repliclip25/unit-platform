<x-onboarding-layout :stepName="$stepName" :sequence="$sequence" :stepIndex="$stepIndex">

@php $user = auth()->user(); @endphp

<div class="text-center mb-8">
    <div class="inline-flex w-14 h-14 bg-yellow-400/10 border border-yellow-400/20 rounded-2xl items-center justify-center mb-5">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
        </svg>
    </div>
    <h1 class="text-2xl font-black text-white mb-2">Check your inbox</h1>
    <p class="text-gray-400 text-sm leading-relaxed">
        We sent a verification link to<br>
        <strong class="text-white">{{ $user->email }}</strong>
    </p>
    <a href="{{ route('onboarding.verify', ['edit' => 1]) }}"
       class="inline-block mt-2 text-yellow-400 text-xs hover:underline">
        Wrong email? Edit →
    </a>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-5 mb-6 text-center">
    <p class="text-gray-400 text-sm mb-4">
        Click the link in the email to verify and continue setup.
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="inline">
        @csrf
        <button type="submit" class="text-yellow-400 text-sm font-semibold hover:text-yellow-300 transition-colors">
            Resend email →
        </button>
    </form>

    <p class="text-gray-700 text-xs mt-3">Check your spam folder if you don't see it within 2 minutes.</p>
</div>

<div class="text-center">
    <a href="{{ route('logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
       class="text-gray-700 hover:text-gray-500 text-xs transition-colors">
        Sign out and try a different account
    </a>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>
</div>

</x-onboarding-layout>

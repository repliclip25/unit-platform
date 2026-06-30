<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email — UNIT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-gray-950">

<div class="min-h-screen flex flex-col">

    {{-- Top bar --}}
    <div class="flex items-center justify-between px-8 py-5 border-b border-gray-800">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-yellow-400 rounded-lg flex items-center justify-center">
                <div class="w-3.5 h-3.5 bg-gray-950 rounded-sm"></div>
            </div>
            <span class="text-white font-black text-lg tracking-tight">UNIT</span>
        </div>
        <span class="text-gray-600 text-sm">Account setup</span>
    </div>

    <div class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">

            {{-- Flash messages --}}
            @if(session('status') === 'verification-link-sent')
            <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                New verification link sent — check your inbox.
            </div>
            @endif

            @if(session('info'))
            <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-yellow-400/10 border border-yellow-400/20 text-yellow-400 text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('info') }}
            </div>
            @endif

            @if(session('email_updated'))
            <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Email updated. Verification link sent to {{ session('email_updated') }}.
            </div>
            @endif

            {{-- Icon --}}
            <div class="text-center mb-8">
                <div class="inline-flex w-16 h-16 bg-yellow-400/10 border border-yellow-400/20 rounded-2xl items-center justify-center mb-5">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.8">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-black text-white mb-2">Check your inbox</h1>
                <p class="text-gray-400 text-sm leading-relaxed">
                    We sent a verification link to confirm your account before we set up your first worker.
                </p>
            </div>

            {{-- Email display --}}
            @if(!$editMode)
            <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 mb-4">
                <p class="text-gray-500 text-xs mb-1">Verification sent to</p>
                <div class="flex items-center justify-between gap-3">
                    <p class="text-white font-semibold text-sm truncate">{{ auth()->user()->email }}</p>
                    <a href="{{ route('onboarding.verify') }}?edit=1"
                       class="text-yellow-400 text-xs hover:text-yellow-300 font-medium shrink-0 transition-colors">
                        Edit →
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('verification.send') }}" class="mb-6">
                @csrf
                <button type="submit"
                        class="w-full bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-bold text-sm py-3.5 rounded-xl transition-colors">
                    Resend verification email
                </button>
            </form>

            @else
            {{-- Edit email form --}}
            <div class="bg-gray-900 border border-yellow-400/30 rounded-xl px-5 py-5 mb-4">
                <p class="text-yellow-400 font-semibold text-sm mb-1">Update your email</p>
                <p class="text-gray-500 text-xs mb-4">Enter the correct address and we'll send a new verification link.</p>
                <form method="POST" action="{{ route('onboarding.update-email') }}">
                    @csrf
                    @error('email')
                    <p class="text-red-400 text-xs mb-3">{{ $message }}</p>
                    @enderror
                    <input type="email" name="email"
                           value="{{ old('email', auth()->user()->email) }}"
                           required autofocus
                           class="w-full bg-gray-950 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-yellow-500/50 mb-3"
                           placeholder="you@yourcompany.com">
                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-bold text-sm py-2.5 rounded-xl transition-colors">
                            Update & resend
                        </button>
                        <a href="{{ route('onboarding.verify') }}"
                           class="px-4 py-2.5 text-sm text-gray-500 hover:text-gray-300 border border-gray-800 rounded-xl transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
            @endif

            <div class="text-center space-y-2">
                <p class="text-gray-600 text-xs">Didn't get the email? Check your spam folder.</p>
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="text-gray-700 hover:text-gray-500 text-xs transition-colors">
                        Sign out and use a different account
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>

</body>
</html>

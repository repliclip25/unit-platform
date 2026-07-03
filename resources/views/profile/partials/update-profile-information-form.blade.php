<form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

<form method="post" action="{{ route('profile.update') }}" class="space-y-4">
    @csrf
    @method('patch')

    <div>
        <label for="name" class="block text-xs font-medium mb-1.5" style="color:var(--text-muted)">Name</label>
        <input id="name" name="name" type="text" required autofocus autocomplete="name"
               value="{{ old('name', $user->name) }}"
               class="w-full rounded-lg px-3 py-2.5 text-sm focus:outline-none transition"
               style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-primary)">
        @error('name')
            <p class="mt-1.5 text-xs" style="color:#f87171">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-xs font-medium mb-1.5" style="color:var(--text-muted)">Email</label>
        <input id="email" name="email" type="email" required autocomplete="username"
               value="{{ old('email', $user->email) }}"
               class="w-full rounded-lg px-3 py-2.5 text-sm focus:outline-none transition"
               style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-primary)">
        @error('email')
            <p class="mt-1.5 text-xs" style="color:#f87171">{{ $message }}</p>
        @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-2 p-3 rounded-lg" style="background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.25)">
                <p class="text-xs" style="color:#fbbf24">
                    Your email address is unverified.
                    <button form="send-verification" class="underline font-medium ml-1 transition hover:opacity-80" style="color:#fbbf24">
                        Resend verification email
                    </button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-1 text-xs" style="color:#6ee7b7">Verification link sent to your inbox.</p>
                @endif
            </div>
        @endif
    </div>

    <div class="pt-1">
        <button type="submit"
                class="px-5 py-2 rounded-lg text-sm font-bold transition hover:opacity-90"
                style="background:var(--accent);color:#000">
            Save changes
        </button>
    </div>
</form>

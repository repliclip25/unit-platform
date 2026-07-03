<x-app-layout title="Profile">

    @if(session('status') === 'profile-updated')
        <div class="mb-4 rounded-xl px-5 py-3 text-sm" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#6ee7b7">
            Profile updated successfully.
        </div>
    @endif
    @if(session('status') === 'password-updated')
        <div class="mb-4 rounded-xl px-5 py-3 text-sm" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#6ee7b7">
            Password updated successfully.
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-white text-lg font-bold">Profile</h1>
        <p class="text-xs mt-0.5" style="color:var(--text-faint)">Manage your account details and security settings</p>
    </div>

    <div class="space-y-4">

        {{-- Profile Information --}}
        <div class="rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
            <div class="px-5 py-4" style="border-bottom:1px solid var(--border)">
                <p class="text-sm font-semibold" style="color:var(--text-primary)">Profile Information</p>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">Update your name and email address.</p>
            </div>
            <div class="px-5 py-5">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        {{-- Update Password --}}
        <div class="rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
            <div class="px-5 py-4" style="border-bottom:1px solid var(--border)">
                <p class="text-sm font-semibold" style="color:var(--text-primary)">Update Password</p>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">Use a long, random password to keep your account secure.</p>
            </div>
            <div class="px-5 py-5">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="rounded-xl overflow-hidden" style="border:1px solid rgba(239,68,68,.25)">
            <div class="px-5 py-4" style="background:rgba(239,68,68,.05);border-bottom:1px solid rgba(239,68,68,.15)">
                <p class="text-sm font-bold" style="color:#f87171">Danger Zone</p>
            </div>
            <div class="px-5 py-5" style="background:var(--bg-card)">
                @include('profile.partials.delete-user-form')
            </div>
        </div>

    </div>

</x-app-layout>

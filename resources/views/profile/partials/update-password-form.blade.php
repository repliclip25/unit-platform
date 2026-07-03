<form method="post" action="{{ route('password.update') }}" class="space-y-4">
    @csrf
    @method('put')

    <div>
        <label for="update_password_current_password" class="block text-xs font-medium mb-1.5" style="color:var(--text-muted)">Current Password</label>
        <input id="update_password_current_password" name="current_password" type="password"
               autocomplete="current-password"
               class="w-full rounded-lg px-3 py-2.5 text-sm focus:outline-none transition"
               style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-primary)">
        @error('current_password', 'updatePassword')
            <p class="mt-1.5 text-xs" style="color:#f87171">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="update_password_password" class="block text-xs font-medium mb-1.5" style="color:var(--text-muted)">New Password</label>
        <input id="update_password_password" name="password" type="password"
               autocomplete="new-password"
               class="w-full rounded-lg px-3 py-2.5 text-sm focus:outline-none transition"
               style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-primary)">
        @error('password', 'updatePassword')
            <p class="mt-1.5 text-xs" style="color:#f87171">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="update_password_password_confirmation" class="block text-xs font-medium mb-1.5" style="color:var(--text-muted)">Confirm New Password</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password"
               autocomplete="new-password"
               class="w-full rounded-lg px-3 py-2.5 text-sm focus:outline-none transition"
               style="background:var(--bg-surface);border:1px solid var(--border);color:var(--text-primary)">
        @error('password_confirmation', 'updatePassword')
            <p class="mt-1.5 text-xs" style="color:#f87171">{{ $message }}</p>
        @enderror
    </div>

    <div class="pt-1">
        <button type="submit"
                class="px-5 py-2 rounded-lg text-sm font-bold transition hover:opacity-90"
                style="background:var(--accent);color:#000">
            Update password
        </button>
    </div>
</form>

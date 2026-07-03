<div class="flex flex-col sm:flex-row sm:items-start gap-4">
    <div class="flex-1">
        <p class="text-sm font-semibold" style="color:var(--text-primary)">Delete account</p>
        <p class="text-xs mt-1 leading-relaxed" style="color:var(--text-muted)">
            Once your account is deleted, all workers, transactions, Gmail connections, memory, and billing records are permanently removed. This cannot be undone.
        </p>
    </div>
    <button onclick="document.getElementById('delete-profile-modal').classList.remove('hidden')"
            class="shrink-0 self-start text-xs font-semibold px-4 py-2 rounded-lg transition"
            style="border:1px solid rgba(239,68,68,.4);background:rgba(239,68,68,.07);color:#f87171">
        Delete account
    </button>
</div>

{{-- Modal --}}
<div id="delete-profile-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4"
     style="background:rgba(0,0,0,.75)">
    <div class="w-full max-w-md rounded-2xl p-6" style="background:var(--bg-card);border:1px solid rgba(239,68,68,.3)">
        <h3 class="text-base font-bold mb-2" style="color:#f87171">Delete your account</h3>
        <p class="text-sm mb-5 leading-relaxed" style="color:var(--text-muted)">
            This will permanently delete everything — workers, transactions, Gmail connections, memory, and billing records.
            <strong style="color:var(--text-primary)">This cannot be undone.</strong>
        </p>
        <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
            @csrf
            @method('delete')

            <div>
                <label for="delete-password" class="block text-xs font-medium mb-1.5" style="color:var(--text-muted)">
                    Enter your password to confirm
                </label>
                <input id="delete-password" name="password" type="password"
                       placeholder="Your current password"
                       autocomplete="current-password"
                       class="w-full rounded-lg px-3 py-2.5 text-sm focus:outline-none transition"
                       style="background:var(--bg-surface);border:1px solid rgba(239,68,68,.3);color:var(--text-primary)">
                @error('password', 'userDeletion')
                    <p class="mt-1.5 text-xs" style="color:#f87171">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse sm:flex-row gap-3 pt-1">
                <button type="button"
                        onclick="document.getElementById('delete-profile-modal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-center"
                        style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-secondary)">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl text-sm font-bold transition hover:opacity-90"
                        style="background:#ef4444;color:#fff">
                    Permanently delete everything
                </button>
            </div>
        </form>
    </div>
</div>

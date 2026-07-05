<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deletion — UNIT</title>
    <link rel="icon" type="image/png" href="/logo.png">
    @vite(['resources/css/app.css'])
    <style>
        body { background: #030712; color: #f9fafb; font-family: ui-sans-serif, system-ui, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .card { max-width: 440px; width: 100%; background: #0d1117; border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 40px 36px; }
        .logo { display: flex; align-items: center; gap: 10px; margin-bottom: 32px; }
        .logo img { width: 32px; height: 32px; border-radius: 8px; }
        .logo-text { font-weight: 700; font-size: 1.1rem; color: #f1d362; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <img src="/logo.png" alt="UNIT">
        <span class="logo-text">UNIT</span>
    </div>

    @if($invalid)
    {{-- Expired / invalid token --}}
    <div class="text-center py-4">
        <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-5" style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.2)">
            <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h1 class="text-xl font-black text-white mb-3">This link has expired</h1>
        <p class="text-gray-400 text-sm leading-relaxed mb-6">
            Account deletion confirmation links expire after 72 hours and can only be used once. If you still need to delete your account, please contact us.
        </p>
        <a href="https://unit.report" class="block text-center text-sm text-gray-500 hover:text-gray-300 transition">← Back to UNIT</a>
    </div>

    @else
    {{-- Valid token — confirmation form --}}
    <div class="mb-6">
        <div class="w-14 h-14 rounded-full flex items-center justify-center mb-5" style="background:rgba(239,68,68,0.10);border:1px solid rgba(239,68,68,0.2)">
            <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </div>
        <h1 class="text-xl font-black text-white mb-2">Confirm account deletion</h1>
        <p class="text-gray-400 text-sm leading-relaxed">
            Hi <strong class="text-white">{{ $name }}</strong> — the UNIT platform admin has requested that your account be permanently deleted.
        </p>
    </div>

    <div class="rounded-xl px-4 py-3 mb-6" style="background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.18)">
        <p class="text-red-400 text-xs font-semibold mb-1">This action is permanent and irreversible.</p>
        <p class="text-gray-500 text-xs leading-relaxed">
            Clicking confirm will immediately and permanently delete your account (<strong class="text-gray-400">{{ $email }}</strong>), all workers, transactions, client memory, Gmail connections, and billing history. There is no undo.
        </p>
    </div>

    <form method="POST" action="{{ url('/account/delete-confirm/' . $token) }}">
        @csrf
        <button type="submit"
                onclick="return confirm('This will permanently delete your UNIT account. This cannot be undone. Are you sure?')"
                class="w-full py-3.5 rounded-xl font-bold text-sm transition-colors mb-3"
                style="background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.3)">
            Yes, permanently delete my account
        </button>
    </form>

    <a href="https://unit.report/dashboard"
       class="block text-center text-sm text-gray-600 hover:text-gray-400 transition-colors">
        No — take me back to my dashboard
    </a>
    @endif
</div>
</body>
</html>

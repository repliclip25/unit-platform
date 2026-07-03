<x-app-layout title="Connect AVA">

    <div class="max-w-2xl mx-auto">

        @if(session('success'))
            <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Step 1: Connect Gmail --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl mb-4">
            <div class="px-6 py-5 border-b border-gray-800 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold shrink-0 {{ $credential ? 'bg-green-600 text-white' : 'bg-gray-700 text-gray-300' }}">
                        {{ $credential ? '✓' : '1' }}
                    </div>
                    <h2 class="text-white font-semibold">Connect Gmail Account</h2>
                </div>
                @if($credential)
                    <span class="text-green-400 text-xs shrink-0">Connected</span>
                @endif
            </div>
            <div class="px-6 py-5">
                @if($credential)
                    <div class="flex items-center gap-3 mb-4 min-w-0">
                        <div class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center shrink-0">
                            <span class="text-white text-xs">@</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-white text-sm font-medium truncate">{{ $credential->gmail_address }}</p>
                            <p class="text-gray-500 text-xs">Gmail account connected</p>
                        </div>
                    </div>
                    <a href="{{ route('ava.gmail.authorize') }}"
                       class="text-xs text-gray-500 hover:text-white underline">Reconnect different account</a>
                @else
                    <p class="text-gray-400 text-sm mb-4">Connect the Gmail account where your renewal and subscription emails arrive. AVA will monitor this inbox and create drafts there.</p>
                    <a href="{{ route('ava.gmail.authorize') }}"
                       class="inline-flex items-center gap-2 bg-white text-gray-900 text-sm font-medium rounded-lg px-4 py-2 hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Connect with Google
                    </a>
                @endif
            </div>
        </div>

        {{-- Step 2: Activate Watch --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl mb-4">
            <div class="px-6 py-5 border-b border-gray-800 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold shrink-0 {{ $credential?->watch_active ? 'bg-green-600 text-white' : 'bg-gray-700 text-gray-300' }}">
                        {{ $credential?->watch_active ? '✓' : '2' }}
                    </div>
                    <h2 class="text-white font-semibold">Activate Inbox Monitoring</h2>
                </div>
                @if($credential?->watch_active)
                    <span class="text-green-400 text-xs shrink-0">Active · expires {{ \Carbon\Carbon::parse($credential->watch_expires_at)->format('M d') }}</span>
                @endif
            </div>
            <div class="px-6 py-5">
                @if(!$credential)
                    <p class="text-gray-600 text-sm">Connect Gmail first.</p>
                @elseif($credential->watch_active)
                    <p class="text-gray-400 text-sm mb-3">AVA is watching your inbox. Every email that arrives will be automatically processed.</p>
                    <a href="{{ route('ava.gmail.watch') }}"
                       class="text-xs text-gray-500 hover:text-white underline">Renew watch</a>
                @else
                    <p class="text-gray-400 text-sm mb-4">Tell Gmail to push new emails to AVA automatically. This activates real-time inbox monitoring.</p>
                    <a href="{{ route('ava.gmail.watch') }}"
                       class="inline-flex items-center gap-2 bg-brand hover:bg-brand-deep text-brand-text text-sm font-medium rounded-lg px-4 py-2 transition">
                        Activate Inbox Watch
                    </a>
                @endif
            </div>
        </div>

        {{-- Step 3: Add Memory --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl">
            <div class="px-6 py-5 border-b border-gray-800 flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-gray-700 text-gray-300 flex items-center justify-center text-sm font-bold">3</div>
                <h2 class="text-white font-semibold">Configure Memory</h2>
            </div>
            <div class="px-6 py-5">
                <p class="text-gray-400 text-sm mb-4">Add your clients, contacts, and assets so AVA knows who owns what and how to handle each situation.</p>
                <a href="{{ route('memory') }}"
                   class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium rounded-lg px-4 py-2 transition">
                    Go to Memory →
                </a>
            </div>
        </div>

    </div>

</x-app-layout>

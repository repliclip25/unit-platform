<x-app-layout title="Connect — {{ $dep->name }}">

    @include('partials.worker-subnav')

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">{{ session('error') }}</div>
    @endif

    @php
        $credContract   = $contract?->credential() ?? [];
        $credLabel      = $credContract['label'] ?? 'Account';
        $allowMultiple  = $credContract['multiple'] ?? false;
        $authorizeRoute = $credContract['authorize_route'] ?? null;
    @endphp

    <div class="max-w-2xl space-y-6">

        {{-- Header --}}
        <div>
            <h2 class="text-white font-semibold text-base">Connected {{ $credLabel }}s</h2>
            <p class="text-gray-500 text-sm mt-0.5">
                {{ $allowMultiple
                    ? 'All connected accounts are monitored. Drafts are sent from the primary account.'
                    : 'One account is connected per worker instance.' }}
            </p>
        </div>

        {{-- Re-auth banner: only show if any connected account still lacks insert scope --}}
        @if($connectedInboxes->where('has_insert_scope', 0)->isNotEmpty() && $authorizeRoute)
        <div class="bg-yellow-900/20 border border-yellow-700/40 rounded-xl px-5 py-4">
            <div class="flex items-start gap-3 mb-3">
                <span class="text-yellow-400 mt-0.5 shrink-0">⚠</span>
                <div>
                    <p class="text-yellow-300 text-sm font-semibold mb-0.5">One-time re-authorization required</p>
                    <p class="text-gray-400 text-xs leading-relaxed">
                        To run Fast Track end-to-end (email arrives in inbox → AVA reads it → draft created), Google needs one extra permission: <span class="text-gray-300 font-mono">gmail.insert</span>. This is separate from Watch renewal. Click below for each account that shows "needs re-auth".
                    </p>
                </div>
            </div>
            @foreach($connectedInboxes->where('has_insert_scope', 0) as $needsAuth)
            <div class="flex items-center justify-between bg-gray-900/60 rounded-lg px-4 py-2 mb-1.5">
                <span class="text-gray-300 text-xs">{{ $needsAuth->gmail_address }}</span>
                <a href="{{ route($authorizeRoute) }}"
                   class="text-xs px-3 py-1.5 rounded-lg font-semibold bg-yellow-400 text-gray-900 hover:bg-yellow-300 transition shrink-0">
                    Re-authorize →
                </a>
            </div>
            @endforeach
        </div>
        @elseif($connectedInboxes->isNotEmpty() && $connectedInboxes->where('has_insert_scope', 1)->count() === $connectedInboxes->count())
        <div class="bg-green-900/20 border border-green-700/40 rounded-xl px-5 py-3 flex items-center gap-2">
            <span class="text-green-400 text-sm">✓</span>
            <p class="text-green-400 text-xs font-medium">All accounts authorized for full inbox injection — Fast Track runs end-to-end.</p>
        </div>
        @endif

        {{-- Account list --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl divide-y divide-gray-800">

            @forelse($connectedInboxes as $inbox)
            <div class="px-5 py-4 flex items-start gap-4">
                {{-- Avatar --}}
                <div class="w-9 h-9 bg-gray-700 rounded-full flex items-center justify-center text-white text-sm font-bold shrink-0 mt-0.5">
                    {{ strtoupper(substr($inbox->gmail_address, 0, 1)) }}
                </div>

                {{-- Info + Actions stacked --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-white text-sm font-medium truncate">{{ $inbox->gmail_address }}</p>
                        @if($inbox->is_primary)
                            <span class="text-xs bg-brand/15 text-brand border border-brand/40 px-1.5 py-0.5 rounded-full shrink-0">Primary</span>
                        @endif
                    </div>
                    <p class="text-xs mt-0.5">
                        @if($inbox->watch_active)
                            <span class="text-green-400">● Watching</span>
                            <span class="text-gray-600"> · expires {{ \Carbon\Carbon::parse($inbox->watch_expires_at)->format('M j') }}</span>
                        @else
                            <span class="text-yellow-400">● Watch inactive</span>
                        @endif
                        @if(empty($inbox->has_insert_scope))
                            <span class="text-gray-600"> · </span>
                            <span class="text-yellow-600 text-xs">needs re-auth for inbox injection</span>
                        @else
                            <span class="text-gray-600"> · </span>
                            <span class="text-green-600 text-xs">inbox injection enabled</span>
                        @endif
                    </p>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 mt-3 flex-wrap">
                        @if(!$inbox->watch_active)
                            <form method="POST" action="{{ route('workers.inboxes.rewatch', [$dep->id, $inbox->id]) }}">
                                @csrf
                                <button type="submit"
                                        class="text-xs px-3 py-1.5 rounded-lg font-medium border border-yellow-800 text-yellow-400 bg-yellow-900/20 hover:bg-yellow-900/40 transition">
                                    ↺ Rewatch
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('workers.inboxes.rewatch', [$dep->id, $inbox->id]) }}">
                                @csrf
                                <button type="submit"
                                        class="text-xs px-3 py-1.5 rounded-lg font-medium border border-gray-700 text-gray-500 hover:text-green-400 hover:border-green-800 transition">
                                    ↺ Renew watch
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('workers.inboxes.disconnect', [$dep->id, $inbox->pivot_id]) }}"
                              onsubmit="return confirm('Disconnect {{ $inbox->gmail_address }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-gray-600 hover:text-red-400 transition px-2 py-1.5">
                                Disconnect
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
                <div class="px-5 py-8 text-center">
                    <p class="text-gray-500 text-sm mb-1">No {{ strtolower($credLabel) }} connected yet.</p>
                    <p class="text-gray-600 text-xs">Connect an account below to start monitoring.</p>
                </div>
            @endforelse

            {{-- Connect footer --}}
            @if($allowMultiple || $connectedInboxes->isEmpty())
            <div class="px-5 py-4 bg-gray-900/50">
                @if($availableCredentials->isNotEmpty())
                    <p class="text-gray-500 text-xs mb-3">Connect an already-authorized account to this worker:</p>
                    <form method="POST" action="{{ route('workers.inboxes.connect', $dep->id) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="credential_id"
                                class="flex-1 bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:border-brand focus:outline-none">
                            <option value="">Choose a {{ strtolower($credLabel) }}…</option>
                            @foreach($availableCredentials as $cred)
                                <option value="{{ $cred->id }}">{{ $cred->gmail_address }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="text-xs px-4 py-2 rounded-lg bg-brand/15 text-brand border border-brand/40 hover:bg-brand-deep/70 transition shrink-0 font-medium">
                            + Connect
                        </button>
                    </form>

                    @if($authorizeRoute)
                    <p class="text-gray-600 text-xs mt-3">
                        Or
                        <a href="{{ route($authorizeRoute) }}" class="text-brand hover:underline">
                            authorize a new {{ strtolower($credLabel) }} →
                        </a>
                    </p>
                    @endif
                @elseif($authorizeRoute)
                    <a href="{{ route($authorizeRoute) }}"
                       class="inline-flex items-center gap-2 text-sm text-brand hover:text-brand font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Connect another {{ strtolower($credLabel) }}
                    </a>
                @endif
            </div>
            @endif

        </div>

        {{-- Info callout --}}
        <div class="bg-gray-900/50 border border-gray-800 rounded-xl px-5 py-4">
            <p class="text-gray-500 text-xs leading-relaxed">
                🔒 <strong class="text-gray-400">Secure OAuth 2.0.</strong>
                UNIT never stores your password. Watch subscriptions expire every 7 days and are auto-renewed by the scheduler.
                You can disconnect at any time.
            </p>
        </div>

    </div>

</x-app-layout>

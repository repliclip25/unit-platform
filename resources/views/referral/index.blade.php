<x-app-layout title="Refer & Earn">

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-white text-2xl font-bold">Refer & Earn</h1>
        <p class="text-gray-500 text-sm mt-1">Invite colleagues to UNIT. Earn $25 credit when they go paid.</p>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 text-center">
            <p class="text-white text-3xl font-bold">{{ $referral->signups }}</p>
            <p class="text-gray-600 text-xs mt-1 uppercase tracking-wide">Signed up</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 text-center">
            <p class="font-bold text-3xl" class="ac-text">{{ $referral->converted }}</p>
            <p class="text-gray-600 text-xs mt-1 uppercase tracking-wide">Converted</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 text-center">
            <p class="text-white text-3xl font-bold">${{ number_format($referral->balance, 0) }}</p>
            <p class="text-gray-600 text-xs mt-1 uppercase tracking-wide">Credit balance</p>
        </div>
    </div>

    {{-- Tier progress --}}
    @if($referral->nextTier)
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-400">Progress to <span class="font-semibold" class="ac-text">{{ $referral->tierLabel }}</span></span>
            <span class="text-sm text-gray-500">{{ $referral->converted }} / {{ $referral->nextTier }} conversions</span>
        </div>
        <div class="w-full h-2 rounded-full bg-gray-800">
            <div class="h-full rounded-full" style="width:{{ $referral->tierPct }}%;background:var(--accent)"></div>
        </div>
    </div>
    @else
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 flex items-center gap-3">
        <span class="text-2xl">&#127942;</span>
        <div>
            <p class="font-semibold" class="ac-text">Gold Referrer</p>
            <p class="text-gray-500 text-sm">10+ conversions — you're in the top tier.</p>
        </div>
    </div>
    @endif

    {{-- Referral link --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-5"
         x-data="{ copied: false }">
        <p class="text-gray-400 text-sm mb-3 font-medium">Your referral link</p>
        <div class="flex items-center gap-3">
            <div class="flex-1 flex items-center gap-2 rounded-lg px-3 py-2.5 min-w-0 border border-gray-800 bg-gray-950">
                <span class="text-sm font-mono truncate text-gray-300">{{ $referralUrl }}</span>
            </div>
            <button @click="navigator.clipboard.writeText('{{ $referralUrl }}'); copied=true; setTimeout(()=>copied=false,2500)"
                    class="shrink-0 text-sm px-5 py-2.5 rounded-lg font-bold transition"
                    class="ac-on">
                <span x-show="!copied">Copy Link</span>
                <span x-show="copied">Copied &#10003;</span>
            </button>
        </div>
        <p class="text-gray-600 text-xs mt-2">Code: <span class="font-mono text-gray-400">{{ $referralCode }}</span></p>
    </div>

    {{-- Best ways to refer --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-5">
        <p class="text-white font-semibold mb-4">Best ways to refer</p>
        <div class="space-y-3">

            <a href="mailto:?subject=Tool that automates license renewals&body=Hey%2C%0A%0AI've been using UNIT Platform to automate our license renewal workflow — it handles reading the email%2C looking up the client%2C and drafting the response automatically. Saves a ton of time.%0A%0AThought you might want to try it. Use my link and you'll get double the usual free trial%3A%0A%0A{{ $referralUrl }}%0A%0A"
               class="flex items-center gap-4 p-3 rounded-lg border border-gray-800 hover:border-gray-700 transition group">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(var(--accent-rgb),0.1)">
                    <svg class="w-4 h-4" fill="none" stroke="var(--accent)" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-200">Email a colleague</p>
                    <p class="text-xs text-gray-600">Works best — personal and specific to their workflow.</p>
                </div>
                <svg class="w-4 h-4 text-gray-700 group-hover:text-gray-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($referralUrl) }}"
               target="_blank"
               class="flex items-center gap-4 p-3 rounded-lg border border-gray-800 hover:border-gray-700 transition group">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 bg-blue-950">
                    <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-200">Share on LinkedIn</p>
                    <p class="text-xs text-gray-600">Great for reaching procurement and compliance teams.</p>
                </div>
                <svg class="w-4 h-4 text-gray-700 group-hover:text-gray-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            <a href="https://twitter.com/intent/tweet?text={{ urlencode('I use UNIT Platform to automate license renewal workflows — it reads the email, looks up the client, and drafts the response. Use my link for double the free trial: ' . $referralUrl) }}"
               target="_blank"
               class="flex items-center gap-4 p-3 rounded-lg border border-gray-800 hover:border-gray-700 transition group">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 bg-gray-800">
                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.261 5.632zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-200">Post on X</p>
                    <p class="text-xs text-gray-600">Good for visibility in your professional network.</p>
                </div>
                <svg class="w-4 h-4 text-gray-700 group-hover:text-gray-500 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

        </div>
    </div>

    {{-- Recent credits table --}}
    @if($credits->count() > 0)
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-800">
            <p class="text-white font-semibold">Recent activity</p>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="text-left px-5 py-2.5 text-gray-600 text-xs font-medium uppercase tracking-wide">Referred user</th>
                    <th class="text-left px-5 py-2.5 text-gray-600 text-xs font-medium uppercase tracking-wide">Status</th>
                    <th class="text-right px-5 py-2.5 text-gray-600 text-xs font-medium uppercase tracking-wide">Credit</th>
                    <th class="text-right px-5 py-2.5 text-gray-600 text-xs font-medium uppercase tracking-wide">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($credits as $credit)
                <tr>
                    <td class="px-5 py-3 text-gray-300">{{ $credit->referred_email ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if($credit->event === 'paid_conversion')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-900/50 text-green-400">Converted</span>
                        @elseif($credit->event === 'signup')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-900/50 text-yellow-400">Signed up</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-800 text-gray-500">{{ ucfirst($credit->event) }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right font-mono" class="ac-text">
                        ${{ number_format($credit->credit_usd ?? 0, 0) }}
                    </td>
                    <td class="px-5 py-3 text-right text-gray-600 text-xs">
                        {{ \Carbon\Carbon::parse($credit->created_at)->format('M j, Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-8 text-center">
        <p class="text-gray-600 text-sm">No referrals yet. Share your link to get started.</p>
    </div>
    @endif

    {{-- How it works --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-5">
        <p class="text-white font-semibold mb-4">How it works</p>
        <ol class="space-y-3">
            <li class="flex gap-3">
                <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-xs font-bold" class="ac-on-soft-md">1</span>
                <div>
                    <p class="text-sm text-gray-200 font-medium">Share your link</p>
                    <p class="text-xs text-gray-600 mt-0.5">Send it to anyone who does license renewal or compliance work.</p>
                </div>
            </li>
            <li class="flex gap-3">
                <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-xs font-bold" class="ac-on-soft-md">2</span>
                <div>
                    <p class="text-sm text-gray-200 font-medium">They sign up and get 20 free transactions</p>
                    <p class="text-xs text-gray-600 mt-0.5">Double the usual free trial — a meaningful incentive to try it.</p>
                </div>
            </li>
            <li class="flex gap-3">
                <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 text-xs font-bold" class="ac-on-soft-md">3</span>
                <div>
                    <p class="text-sm text-gray-200 font-medium">You earn $25 credit when they subscribe</p>
                    <p class="text-xs text-gray-600 mt-0.5">Applied to your UNIT account automatically. No cap on earnings.</p>
                </div>
            </li>
        </ol>
    </div>

</div>

</x-app-layout>

<x-app-layout title="Billing — {{ $dep->name }}">

    @include('partials.worker-subnav')

    @php
        $isActive  = $billing?->status === 'active';
        $isTrial   = $billing?->status === 'trial';
        $trialUsed = $billing?->trial_transactions_used ?? 0;
        $trialLimit= $billing?->trial_transactions_limit ?? 10;
        $trialLeft = max(0, $trialLimit - $trialUsed);
        $trialPct  = $trialLimit > 0 ? round(($trialUsed / $trialLimit) * 100) : 0;

        $flatRate      = (float) ($pricing?->monthly_flat_rate ?? 25.00);
        $aiCostMonth   = (float) ($monthUsage?->cost_usd ?? 0);
        $markup        = 1.30;
        $aiPassthrough = $aiCostMonth * $markup;

        // Prorate flat fee if subscription started mid-month
        $subStart    = $billing?->billing_period_start ? \Carbon\Carbon::parse($billing->billing_period_start) : null;
        $isProrated  = $isActive && $subStart && $subStart->month == now()->month && $subStart->year == now()->year && $subStart->day > 1;
        $daysInMonth = now()->daysInMonth;
        $daysActive  = $isProrated ? (now()->diffInDays($subStart) + 1) : $daysInMonth;
        $flatCharge  = $isProrated ? round($flatRate * ($daysActive / $daysInMonth), 2) : $flatRate;
        $estimatedBill = $isActive ? ($flatCharge + $aiPassthrough) : 0;

        $tokensIn  = (int) ($monthUsage?->tokens_in  ?? 0);
        $tokensOut = (int) ($monthUsage?->tokens_out ?? 0);
        $txCount   = (int) ($monthUsage?->tx_count   ?? 0);

        $allTimeCost = (float) ($allTime?->cost ?? 0);
        $allTimeTx   = (int)   ($allTime?->tx_count ?? 0);
    @endphp

    <div class="max-w-4xl space-y-6">

        {{-- Status banner --}}
        @if($isTrial)
        <div class="bg-brand/10 border border-brand-deep/40 rounded-xl px-5 py-4 flex items-start justify-between gap-4">
            <div>
                <p class="text-brand font-semibold text-sm mb-0.5">Trial Plan</p>
                <p class="text-gray-400 text-xs leading-relaxed">
                    {{ $trialLeft }} of {{ $trialLimit }} free transactions remaining.
                    @if($trialLeft === 0)
                        <span class="text-red-400 font-medium">Trial exhausted — upgrade to continue processing.</span>
                    @else
                        Upgrade anytime to unlock unlimited processing and unlock full AI cost reporting.
                    @endif
                </p>
                <div class="mt-2 w-full max-w-xs h-1.5 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $trialPct >= 80 ? 'bg-red-500' : 'bg-brand' }}"
                         style="width: {{ $trialPct }}%"></div>
                </div>
            </div>
            <a href="{{ route('billing.checkout', $dep->id) }}"
               class="shrink-0 text-xs px-4 py-2 rounded-lg bg-brand hover:bg-brand-deep text-brand-text font-semibold transition">
                Upgrade →
            </a>
        </div>
        @elseif($isActive)
        <div class="bg-green-900/20 border border-green-700/40 rounded-xl px-5 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-green-400 text-lg">✓</span>
                <div>
                    <p class="text-green-300 font-semibold text-sm">Active Subscription</p>
                    <p class="text-gray-400 text-xs">${{ number_format($flatRate, 2) }}/mo flat + AI passthrough at cost + 30%</p>
                </div>
            </div>
            <a href="{{ route('billing.portal') }}"
               class="shrink-0 text-xs px-4 py-2 rounded-lg border border-gray-700 text-gray-400 hover:text-white hover:border-gray-500 transition">
                Manage billing →
            </a>
        </div>
        @else
        <div class="bg-red-900/20 border border-red-700/40 rounded-xl px-5 py-4 flex items-start justify-between gap-4">
            <div>
                <p class="text-red-300 font-semibold text-sm">
                    {{ $billing ? ucfirst($billing->status) : 'No billing record' }}
                </p>
                @if(!$billing)
                    <p class="text-gray-400 text-xs mt-1 leading-relaxed">
                        This deployment was created without a billing record — likely deployed before the billing system was in place.
                        It can still process emails but cannot be subscribed until an admin backfills the record.
                    </p>
                    <p class="text-gray-600 text-xs mt-1.5">Contact your platform admin to fix this in Tenant Controls → Deployment Billing Status.</p>
                @else
                    <p class="text-gray-400 text-xs mt-0.5">Contact support or upgrade to restore access.</p>
                @endif
            </div>
        </div>
        @endif

        {{-- This month snapshot --}}
        <div>
            <h2 class="text-white font-semibold text-sm mb-3">This Month — {{ now()->format('F Y') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-4">
                    <p class="text-gray-500 text-xs">Transactions</p>
                    <p class="text-white text-2xl font-semibold mt-1">{{ number_format($txCount) }}</p>
                    @if($isActive && $pricing?->included_transactions)
                        <p class="text-gray-600 text-xs mt-0.5">of {{ number_format($pricing->included_transactions) }} included</p>
                    @endif
                </div>
                <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-4">
                    <p class="text-gray-500 text-xs">Tokens In</p>
                    <p class="text-white text-2xl font-semibold mt-1">{{ $tokensIn >= 1000 ? number_format($tokensIn/1000, 1).'K' : number_format($tokensIn) }}</p>
                    <p class="text-gray-600 text-xs mt-0.5">input tokens</p>
                </div>
                <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-4">
                    <p class="text-gray-500 text-xs">Tokens Out</p>
                    <p class="text-white text-2xl font-semibold mt-1">{{ $tokensOut >= 1000 ? number_format($tokensOut/1000, 1).'K' : number_format($tokensOut) }}</p>
                    <p class="text-gray-600 text-xs mt-0.5">output tokens</p>
                </div>
                <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-4">
                    <p class="text-gray-500 text-xs">AI Cost</p>
                    <p class="text-white text-2xl font-semibold mt-1">${{ number_format($aiCostMonth, 4) }}</p>
                    @if($isActive)
                        <p class="text-gray-600 text-xs mt-0.5">+30% = ${{ number_format($aiPassthrough, 4) }} billed</p>
                    @else
                        <p class="text-gray-600 text-xs mt-0.5">platform cost</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Billing model breakdown (active subscribers) --}}
        @if($isActive)
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
            <h3 class="text-white font-semibold text-sm mb-4">Estimated Bill This Month</h3>
            <div class="space-y-2">
                <div class="flex items-start justify-between text-sm">
                    <div>
                        <span class="text-gray-400">Platform flat rate</span>
                        @if($isProrated)
                            <p class="text-amber-600 text-xs mt-0.5">
                                Prorated {{ $daysActive }}/{{ $daysInMonth }} days (subscribed {{ $subStart->format('M j') }}) · full rate ${{ number_format($flatRate, 2) }}/mo
                            </p>
                        @endif
                    </div>
                    <span class="text-white font-mono shrink-0">${{ number_format($flatCharge, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-400">AI passthrough (${{ number_format($aiCostMonth, 4) }} × 1.30)</span>
                    <span class="text-white font-mono">${{ number_format($aiPassthrough, 4) }}</span>
                </div>
                <div class="border-t border-gray-800 pt-2 flex items-center justify-between text-sm font-semibold">
                    <span class="text-gray-300">Estimated total</span>
                    <span class="text-brand font-mono">${{ number_format($estimatedBill, 2) }}</span>
                </div>
            </div>
            <p class="text-gray-600 text-xs mt-3">Final invoice generated at end of billing period. AI passthrough reflects actual Anthropic/OpenAI cost + 30% platform fee.</p>
        </div>
        @endif

        {{-- Per-stage breakdown --}}
        @if($stageBreakdown->isNotEmpty())
        <div>
            <h3 class="text-white font-semibold text-sm mb-3">AI Cost by Pipeline Stage</h3>
            <div class="bg-gray-900 border border-gray-800 rounded-xl divide-y divide-gray-800">
                @foreach($stageBreakdown as $stage)
                <div class="px-5 py-3 flex items-center gap-4">
                    <div class="w-20 shrink-0">
                        <span class="text-xs font-mono text-brand bg-brand/12 border border-brand/30 px-2 py-0.5 rounded">{{ $stage->stage }}</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-1.5 bg-gray-800 rounded-full overflow-hidden">
                                @php $maxCost = $stageBreakdown->max('cost'); @endphp
                                <div class="h-full bg-brand rounded-full"
                                     style="width: {{ $maxCost > 0 ? round(($stage->cost / $maxCost) * 100) : 0 }}%"></div>
                            </div>
                            <span class="text-gray-400 text-xs w-24 text-right shrink-0">{{ number_format($stage->tokens) }} tokens</span>
                        </div>
                    </div>
                    <div class="text-right shrink-0 w-28">
                        <p class="text-white text-xs font-mono">${{ number_format($stage->cost, 6) }}</p>
                        <p class="text-gray-600 text-xs">{{ number_format($stage->calls) }} calls</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Daily sparkline (last 30 days) --}}
        @if($dailySpend->isNotEmpty())
        <div>
            <h3 class="text-white font-semibold text-sm mb-3">Daily AI Cost — Last 30 Days</h3>
            <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4">
                @php
                    $days    = collect();
                    $maxDay  = 0;
                    for ($i = 29; $i >= 0; $i--) {
                        $d    = now()->subDays($i)->format('Y-m-d');
                        $cost = (float) ($dailySpend[$d]->cost ?? 0);
                        $days->push(['day' => $d, 'label' => now()->subDays($i)->format('M j'), 'cost' => $cost]);
                        if ($cost > $maxDay) $maxDay = $cost;
                    }
                @endphp
                <div class="flex items-end gap-0.5 h-16">
                    @foreach($days as $d)
                    @php $pct = $maxDay > 0 ? ($d['cost'] / $maxDay) * 100 : 0; @endphp
                    <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
                        <div class="w-full rounded-sm bg-brand/70 hover:bg-brand transition"
                             style="height: {{ max(2, $pct) }}%"
                             title="{{ $d['label'] }}: ${{ number_format($d['cost'], 6) }}"></div>
                        @if($loop->iteration % 7 === 1)
                        <span class="absolute -bottom-5 text-gray-600 text-xs whitespace-nowrap">{{ $d['label'] }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                <div class="mt-6 flex items-center justify-between text-xs text-gray-600">
                    <span>$0.000000</span>
                    <span>Max/day: ${{ number_format($maxDay, 6) }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Monthly history --}}
        @if($monthlyHistory->isNotEmpty())
        <div>
            <h3 class="text-white font-semibold text-sm mb-3">Monthly History</h3>
            <div class="bg-gray-900 border border-gray-800 rounded-xl divide-y divide-gray-800">
                <div class="px-5 py-2.5 grid grid-cols-4 gap-4 text-xs text-gray-600 uppercase tracking-wide">
                    <span>Month</span>
                    <span class="text-right">Transactions</span>
                    <span class="text-right">Tokens</span>
                    <span class="text-right">AI Cost</span>
                </div>
                @foreach($monthlyHistory->reverse() as $month)
                <div class="px-5 py-3 grid grid-cols-4 gap-4 text-sm">
                    <span class="text-gray-300">{{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('M Y') }}</span>
                    <span class="text-right text-gray-400">{{ number_format($month->tx_count) }}</span>
                    <span class="text-right text-gray-400">{{ number_format($month->tokens) }}</span>
                    <span class="text-right text-white font-mono">${{ number_format($month->cost, 4) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- All-time --}}
        <div class="bg-gray-900/50 border border-gray-800 rounded-xl px-5 py-4">
            <h3 class="text-gray-500 text-xs uppercase tracking-wide mb-3">All-Time Totals</h3>
            <div class="flex items-center gap-8">
                <div>
                    <p class="text-white font-semibold">{{ number_format($allTimeTx) }}</p>
                    <p class="text-gray-600 text-xs">transactions processed</p>
                </div>
                <div>
                    <p class="text-white font-semibold">{{ number_format($allTime?->tokens ?? 0) }}</p>
                    <p class="text-gray-600 text-xs">total tokens</p>
                </div>
                <div>
                    <p class="text-white font-semibold">${{ number_format($allTimeCost, 4) }}</p>
                    <p class="text-gray-600 text-xs">AI cost (platform)</p>
                </div>
                @if($isActive)
                <div>
                    <p class="text-brand font-semibold">${{ number_format($allTimeCost * $markup, 4) }}</p>
                    <p class="text-gray-600 text-xs">AI billed (+ 30%)</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Billing model explainer --}}
        <div class="bg-gray-900/50 border border-gray-800 rounded-xl px-5 py-4 space-y-2">
            <p class="text-gray-500 text-xs font-semibold uppercase tracking-wide">How billing works</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <div>
                    <p class="text-gray-300 text-xs font-medium">Platform flat fee</p>
                    <p class="text-gray-500 text-xs mt-0.5">${{ number_format($flatRate, 2) }}/mo per active worker. Covers infrastructure, scheduling, watch renewals, and support.</p>
                </div>
                <div>
                    <p class="text-gray-300 text-xs font-medium">AI passthrough</p>
                    <p class="text-gray-500 text-xs mt-0.5">Actual token cost billed at cost + 30%. Tenants using their own API key pay flat fee only.</p>
                </div>
                <div>
                    <p class="text-gray-300 text-xs font-medium">Overage</p>
                    <p class="text-gray-500 text-xs mt-0.5">{{ number_format($pricing?->included_transactions ?? 500) }} transactions included. ${{ number_format($pricing?->overage_price_per_tx ?? 0.10, 2) }} per additional transaction beyond that.</p>
                </div>
            </div>
        </div>

    </div>

</x-app-layout>

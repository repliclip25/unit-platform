<x-app-layout title="Billing">

    @php
        $totalCost   = (float) ($monthlyUsage->total_cost   ?? 0);
        $totalTokens = (int)   ($monthlyUsage->total_tokens ?? 0);
        $capPct      = $spendCap > 0 ? min(100, ($totalCost / $spendCap) * 100) : 0;
        $capDanger   = $spendCap > 0 && $capPct >= 80;
    @endphp

    {{-- Platform-level policy violations --}}
    @if(!empty($policyViolations['platform']))
        <div class="mb-5">
            @include('partials.policy-violations', ['violations' => $policyViolations['platform']])
        </div>
    @endif

    {{-- Header row --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-white text-lg font-bold">Billing & Usage</h1>
            <p class="text-gray-500 text-xs mt-0.5">{{ now()->format('F Y') }} · resets on the 1st</p>
        </div>
        <a href="{{ route('billing.portal') }}"
           class="text-sm bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2 rounded-lg border border-gray-700 transition">
            Manage Payment →
        </a>
    </div>

    {{-- Promo banner --}}
    @if($promotions->isNotEmpty())
    <div class="mb-5 flex items-center gap-3 px-5 py-3 rounded-xl border border-yellow-700/40"
         style="background:rgba(113,63,18,0.15)">
        <span class="text-yellow-400">✦</span>
        @foreach($promotions as $promo)
            <span class="text-yellow-300 text-sm font-semibold">{{ $promo->discount_pct }}% off
                {{ $promo->applies_to === 'all' ? 'all workers' : $promo->worker_slug }} — {{ $promo->name }}</span>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-3 gap-5 mb-6">

        {{-- Monthly spend meter --}}
        <div class="col-span-2 bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-gray-500 text-xs">AI Spend · {{ now()->format('F Y') }}</p>
                    <p class="text-white text-3xl font-bold font-mono mt-1">${{ number_format($totalCost, 4) }}</p>
                    <p class="text-gray-600 text-xs mt-0.5">{{ number_format($totalTokens) }} tokens consumed</p>
                </div>
                @if($spendCap > 0)
                <div class="text-right">
                    <p class="text-gray-500 text-xs">Monthly cap</p>
                    <p class="text-sm font-mono {{ $capDanger ? 'text-red-400' : 'text-gray-300' }}">${{ number_format($spendCap, 2) }}</p>
                </div>
                @endif
            </div>

            @if($spendCap > 0)
            {{-- Spend vs cap progress --}}
            <div class="mb-4">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="text-gray-600">$0</span>
                    <span class="{{ $capDanger ? 'text-red-400 font-semibold' : 'text-gray-500' }}">
                        {{ number_format($capPct, 1) }}% of cap used
                    </span>
                    <span class="text-gray-600">${{ number_format($spendCap, 2) }}</span>
                </div>
                <div class="h-2.5 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700"
                         style="width:{{ $capPct }}%;background:{{ $capDanger ? 'linear-gradient(90deg,#ef4444,#dc2626)' : 'linear-gradient(90deg,#f3c531,#d9a91f)' }}"></div>
                </div>
                @if($capDanger)
                <p class="text-red-400 text-xs mt-1.5">⚠ Approaching cap — new transactions will block when reached</p>
                @endif
            </div>
            @endif

            {{-- 30-day sparkline --}}
            @if($dailySpend->isNotEmpty())
            @php
                $days    = collect(range(0,29))->map(fn($i) => now()->subDays(29-$i)->format('Y-m-d'));
                $values  = $days->map(fn($d) => (float)($dailySpend[$d] ?? 0));
                $maxVal  = max($values->max(), 0.00001);
                $bars    = $values->map(fn($v) => round(($v / $maxVal) * 40));
            @endphp
            <div>
                <p class="text-gray-700 text-xs mb-2">Last 30 days</p>
                <div class="flex items-end gap-px h-10">
                    @foreach($bars as $i => $h)
                    <div class="flex-1 rounded-sm transition-all"
                         style="height:{{ max(2,$h) }}px;background:{{ $values[$i] > 0 ? 'rgba(243,197,49,0.7)' : '#1f2937' }}"
                         title="{{ $days[$i] }}: ${{ number_format($values[$i], 6) }}"></div>
                    @endforeach
                </div>
                <div class="flex justify-between text-gray-700 text-xs mt-1">
                    <span>{{ now()->subDays(29)->format('M d') }}</span>
                    <span>Today</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Stage breakdown --}}
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <p class="text-gray-500 text-xs mb-4">Cost by Pipeline Stage</p>
            @if($stageBreakdown->isEmpty())
                <p class="text-gray-700 text-xs">No AI usage this month</p>
            @else
                @php $maxStageCost = $stageBreakdown->max('cost') ?: 0.0001; @endphp
                <div class="space-y-3">
                    @foreach($stageBreakdown as $stage)
                    @php $pct = min(100, ($stage->cost / $maxStageCost) * 100); @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-gray-400 text-xs capitalize">{{ $stage->stage }}</span>
                            <span class="text-gray-400 text-xs font-mono">${{ number_format($stage->cost, 4) }}</span>
                        </div>
                        <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full" style="width:{{ $pct }}%;background:#f3c531"></div>
                        </div>
                        <p class="text-gray-700 text-xs mt-0.5">{{ number_format($stage->calls) }} calls · {{ number_format($stage->tokens) }} tokens</p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- Estimated Bill This Month --}}
    @php
        $estimatedFlat   = 0;
        $flatLineItems   = [];
        foreach ($deployments as $dep) {
            $bill  = $billingRecords[$dep->id] ?? null;
            $price = $pricing[$dep->worker_slug] ?? null;
            if (!$bill || !$price || $bill->status !== 'active') continue;

            $flat  = (float) $price->monthly_flat_rate;
            $start = $bill->billing_period_start ? \Carbon\Carbon::parse($bill->billing_period_start) : null;

            if ($start && $start->month == now()->month && $start->year == now()->year && $start->day > 1) {
                // Prorated: charged only from subscription start to end of month
                $daysInMonth  = now()->daysInMonth;
                $daysActive   = now()->diffInDays($start) + 1;
                $prorated     = round($flat * ($daysActive / $daysInMonth), 2);
                $flatLineItems[] = [
                    'name'      => $dep->name,
                    'flat'      => $flat,
                    'charge'    => $prorated,
                    'prorated'  => true,
                    'days'      => $daysActive,
                    'totalDays' => $daysInMonth,
                    'start'     => $start->format('M j'),
                ];
                $estimatedFlat += $prorated;
            } else {
                $flatLineItems[] = [
                    'name'     => $dep->name,
                    'flat'     => $flat,
                    'charge'   => $flat,
                    'prorated' => false,
                ];
                $estimatedFlat += $flat;
            }
        }
        $aiPassthrough   = round($totalCost * 1.30, 4);
        $estimatedTotal  = $estimatedFlat + $aiPassthrough;
        $hasActiveWorkers = count($flatLineItems) > 0;
    @endphp

    @if($hasActiveWorkers || $totalCost > 0)
    <div class="bg-gray-900 border border-gray-800 rounded-2xl mb-6 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
            <h2 class="text-white text-sm font-semibold">Estimated Bill This Month</h2>
            <p class="text-gray-600 text-xs">{{ now()->format('F Y') }} · final invoice generated at period end</p>
        </div>
        <div class="px-5 py-4 space-y-2.5">

            {{-- Per-deployment flat fees --}}
            @foreach($flatLineItems as $item)
            <div class="flex items-start justify-between text-sm">
                <div>
                    <span class="text-gray-300">{{ $item['name'] }} — flat rate</span>
                    @if($item['prorated'])
                        <span class="ml-2 text-xs px-1.5 py-0.5 rounded bg-amber-900/30 text-amber-400 border border-amber-800/40">
                            prorated {{ $item['days'] }}/{{ $item['totalDays'] }} days from {{ $item['start'] }}
                        </span>
                        <p class="text-gray-600 text-xs mt-0.5">Full rate ${{ number_format($item['flat'], 2) }}/mo · charged for {{ $item['days'] }} of {{ $item['totalDays'] }} days</p>
                    @endif
                </div>
                <span class="text-gray-300 font-mono shrink-0">${{ number_format($item['charge'], 2) }}</span>
            </div>
            @endforeach

            {{-- AI passthrough line --}}
            @if($totalCost > 0)
            <div class="flex items-center justify-between text-sm">
                <div>
                    <span class="text-gray-300">AI passthrough (${{ number_format($totalCost, 4) }} × 1.30)</span>
                    <p class="text-gray-600 text-xs mt-0.5">Actual Anthropic cost + 30% platform fee · {{ number_format($totalTokens) }} tokens</p>
                </div>
                <span class="text-gray-300 font-mono shrink-0">${{ number_format($aiPassthrough, 4) }}</span>
            </div>
            @endif

            {{-- Divider + total --}}
            <div class="pt-2 border-t border-gray-800 flex items-center justify-between">
                <span class="text-white text-sm font-semibold">Estimated total</span>
                <span class="text-brand text-lg font-bold font-mono">${{ number_format($estimatedTotal, 2) }}</span>
            </div>

            @if(count($flatLineItems) > 1)
            <p class="text-gray-700 text-xs pt-1">
                {{ count($flatLineItems) }} active worker subscription{{ count($flatLineItems) > 1 ? 's' : '' }} ·
                each billed independently by Stripe
            </p>
            @endif
        </div>
    </div>
    @endif

    {{-- Worker subscriptions --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl mb-6">
        <div class="px-5 py-4 border-b border-gray-800">
            <h2 class="text-white text-sm font-semibold">Worker Subscriptions</h2>
        </div>

        @forelse($deployments as $dep)
            @php
                $bill     = $billingRecords[$dep->id] ?? null;
                $price    = $pricing[$dep->worker_slug] ?? null;
                $spend    = $workerSpend[$dep->id]     ?? null;
                $wCost    = (float) ($spend->cost   ?? 0);
                $wTokens  = (int)   ($spend->tokens ?? 0);
                $trialUsed  = (int) ($bill?->trial_transactions_used  ?? 0);
                $trialLimit = (int) ($bill?->trial_transactions_limit ?? 10);
                $trialPct   = $trialLimit > 0 ? min(100, ($trialUsed / $trialLimit) * 100) : 0;
                $trialWarn  = $trialPct >= 70;
                $statusColor = match($bill?->status) {
                    'active'   => ['bg'=>'rgba(34,197,94,0.1)','border'=>'rgba(34,197,94,0.3)','text'=>'#4ade80','label'=>'Active'],
                    'trial'    => ['bg'=>'rgba(245,193,0,0.1)','border'=>'rgba(245,193,0,0.3)','text'=>'#fbbf24','label'=>'Trial'],
                    'past_due' => ['bg'=>'rgba(239,68,68,0.1)','border'=>'rgba(239,68,68,0.3)','text'=>'#f87171','label'=>'Past Due'],
                    'paused'   => ['bg'=>'rgba(107,114,128,0.1)','border'=>'rgba(107,114,128,0.3)','text'=>'#9ca3af','label'=>'Paused'],
                    'canceled' => ['bg'=>'rgba(127,29,29,0.1)','border'=>'rgba(127,29,29,0.4)','text'=>'#fca5a5','label'=>'Canceled'],
                    default    => ['bg'=>'rgba(107,114,128,0.1)','border'=>'#374151','text'=>'#6b7280','label'=>ucfirst($bill?->status ?? 'Unknown')],
                };
            @endphp
            <div class="px-5 py-5 border-b border-gray-800/60 last:border-0">
                <div class="flex items-start justify-between gap-4">

                    {{-- Worker info --}}
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 bg-brand/15 rounded-xl flex items-center justify-center shrink-0">
                            <span class="text-brand font-bold text-sm">{{ strtoupper(substr($dep->worker_slug,0,1)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-white text-sm font-medium truncate">{{ $dep->name }}</p>
                            <p class="text-gray-600 text-xs">{{ $dep->worker_slug }}</p>
                            @if($bill?->billing_period_start && $bill->status === 'active')
                                @php $subStart = \Carbon\Carbon::parse($bill->billing_period_start); @endphp
                                <p class="text-gray-700 text-xs mt-0.5">
                                    Subscribed {{ $subStart->format('M j, Y') }}
                                    @if($subStart->isCurrentMonth() && $subStart->day > 1)
                                        <span class="text-amber-600"> · prorated this month</span>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Status badge --}}
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium shrink-0"
                          style="background:{{ $statusColor['bg'] }};border:1px solid {{ $statusColor['border'] }};color:{{ $statusColor['text'] }}">
                        {{ $statusColor['label'] }}
                    </span>

                    {{-- This month's spend --}}
                    <div class="text-right shrink-0">
                        <p class="text-gray-500 text-xs">This month</p>
                        <p class="text-white text-sm font-mono">${{ number_format($wCost, 4) }}</p>
                        <p class="text-gray-600 text-xs">{{ number_format($wTokens) }} tokens</p>
                    </div>

                    {{-- Pricing --}}
                    @if($price)
                    <div class="text-right shrink-0">
                        <p class="text-gray-500 text-xs">Plan</p>
                        <p class="text-gray-300 text-sm">${{ number_format($price->monthly_flat_rate, 2) }}/mo</p>
                        <p class="text-gray-600 text-xs">+${{ number_format($price->overage_price_per_tx, 2) }}/tx over {{ number_format($price->included_transactions) }}</p>
                    </div>
                    @endif

                    {{-- Action button --}}
                    <div class="shrink-0">
                        @if(($bill?->status ?? '') === 'trial')
                            <a href="{{ route('billing.checkout', $dep->id) }}"
                               class="text-xs px-4 py-2 rounded-lg font-bold text-gray-900 hover:opacity-90 transition block text-center"
                               style="background:#F5C100">Subscribe</a>
                        @elseif(($bill?->status ?? '') === 'active')
                            <a href="{{ route('billing.portal') }}"
                               class="text-xs px-4 py-2 rounded-lg font-medium text-gray-400 border border-gray-700 hover:bg-gray-800 transition block text-center">Manage</a>
                        @elseif(in_array($bill?->status ?? '', ['past_due','canceled']))
                            <a href="{{ route('billing.portal') }}"
                               class="text-xs px-4 py-2 rounded-lg font-medium text-red-400 border border-red-800 hover:bg-red-900/20 transition block text-center">Reactivate</a>
                        @endif
                    </div>

                </div>

                {{-- Worker-level policy violations --}}
                @if(!empty($policyViolations[$dep->id]))
                    <div class="mt-4 pl-12">
                        @include('partials.policy-violations', ['violations' => $policyViolations[$dep->id]])
                    </div>
                @endif

                {{-- Trial progress bar --}}
                @if(($bill?->status ?? '') === 'trial' && empty($policyViolations[$dep->id]))
                <div class="mt-4 pl-12">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="{{ $trialWarn ? 'text-yellow-400' : 'text-gray-600' }}">
                            Trial: {{ $trialUsed }} / {{ $trialLimit }} transactions used
                        </span>
                        <span class="text-gray-600">{{ $trialLimit - $trialUsed }} remaining</span>
                    </div>
                    <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                             style="width:{{ $trialPct }}%;background:{{ $trialWarn ? '#f59e0b' : '#d9a91f' }}"></div>
                    </div>
                    @if($trialWarn && $trialUsed < $trialLimit)
                        <p class="text-yellow-600 text-xs mt-1">Subscribe to continue after trial ends</p>
                    @elseif($trialUsed >= $trialLimit)
                        <p class="text-red-400 text-xs mt-1 font-medium">Trial exhausted — new emails will be blocked until you subscribe</p>
                    @endif
                </div>
                @endif

            </div>
        @empty
            <div class="px-5 py-8 text-center text-gray-600 text-sm">No workers deployed yet.</div>
        @endforelse
    </div>

    {{-- Invoices --}}
    @if($invoices->isNotEmpty())
    <div class="bg-gray-900 border border-gray-800 rounded-2xl">
        <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
            <h2 class="text-white text-sm font-semibold">Invoices</h2>
            @if(auth()->user()->role === 'admin')
                <span class="text-xs text-amber-600 border border-amber-900/40 rounded px-2 py-0.5">Admin: void clears a due invoice from Stripe without charging the tenant</span>
            @endif
        </div>
        @foreach($invoices as $invoice)
        @php
            $isDue    = !$invoice->paid && ($invoice->asStripeInvoice()->status ?? '') !== 'void';
            $isVoided = ($invoice->asStripeInvoice()->status ?? '') === 'void';
        @endphp
        <div class="px-5 py-3 border-b border-gray-800/60 last:border-0 flex items-center justify-between gap-4">
            <div>
                <p class="text-white text-xs">{{ $invoice->date()->format('F j, Y') }}</p>
                <p class="text-gray-500 text-xs">${{ number_format($invoice->rawTotal() / 100, 2) }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($isVoided)
                    <span class="text-xs px-2 py-0.5 rounded bg-gray-800 text-gray-600">Voided</span>
                @elseif($invoice->paid)
                    <span class="text-xs px-2 py-0.5 rounded bg-green-900/40 text-green-400">Paid</span>
                @else
                    <span class="text-xs px-2 py-0.5 rounded bg-red-900/40 text-red-400">Due</span>
                @endif

                <a href="{{ route('billing.invoice', $invoice->id) }}"
                   class="text-xs text-gray-500 hover:text-brand">Download PDF →</a>

                @if(auth()->user()->role === 'admin' && $isDue)
                <form method="POST" action="{{ route('admin.invoices.void', $invoice->id) }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    <button type="submit"
                            onclick="return confirm('Void invoice {{ $invoice->id }} for ${{ number_format($invoice->rawTotal() / 100, 2) }}? This cancels the outstanding balance in Stripe — the tenant will no longer owe this amount.')"
                            class="text-xs px-2.5 py-1 rounded border border-amber-800 text-amber-500 hover:bg-amber-900/20 transition">
                        Void
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

</x-app-layout>

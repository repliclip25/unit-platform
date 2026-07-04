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
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="font-bold text-lg" style="color:var(--text-primary)">Billing & Usage</h1>
            <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ now()->format('F Y') }} · resets on the 1st</p>
        </div>
        <a href="{{ route('billing.portal') }}"
           class="text-sm px-4 py-2 rounded-lg shrink-0"
           style="background:var(--bg-raised);color:var(--text-secondary);border:1px solid var(--border)">
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

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">

        {{-- Monthly spend meter --}}
        <div class="sm:col-span-2 rounded-2xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
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
                         style="width:{{ $capPct }}%;background:{{ $capDanger ? 'linear-gradient(90deg,#ef4444,#dc2626)' : 'linear-gradient(90deg,var(--accent),#d9a91f)' }}"></div>
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
                         style="height:{{ max(2,$h) }}px;background:{{ $values[$i] > 0 ? 'rgba(var(--accent-rgb),0.7)' : '#1f2937' }}"
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
        <div class="rounded-2xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
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
                            <div class="h-full rounded-full" style="width:{{ $pct }}%;background:var(--accent)"></div>
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
        $invoiceWorkers  = [];
        $grandTotal      = 0;

        foreach ($deployments as $dep) {
            $bill    = $billingRecords[$dep->id] ?? null;
            $depStages = $workerStageBreakdown[$dep->id] ?? collect();

            // Pipeline AI cost (email processing)
            $pipelineCost   = $depStages->whereIn('stage', $pipelineStages)->sum('cost');
            $pipelineTokens = $depStages->whereIn('stage', $pipelineStages)->sum('tokens');
            $pipelineCalls  = $depStages->whereIn('stage', $pipelineStages)->sum('calls');

            // Testing / prompt rewrite AI cost
            $testCost       = $depStages->whereIn('stage', $testStages)->sum('cost');
            $testTokens     = $depStages->whereIn('stage', $testStages)->sum('tokens');
            $testCalls      = $depStages->whereIn('stage', $testStages)->sum('calls');

            $totalDepCost   = (float) ($workerSpend[$dep->id]->cost ?? 0);
            $totalDepTokens = (int)   ($workerSpend[$dep->id]->tokens ?? 0);

            if (!$bill || ($bill->status !== 'active' && $totalDepCost == 0)) continue;

            $isActive = $bill?->status === 'active';
            $isTrial  = $bill?->status === 'trial';

            // Subscription flat rate
            $flatCharge  = 0;
            $flatFull    = 0;
            $prorated    = false;
            $prorateNote = '';
            $planLabel   = null;
            $unitUsed    = (int) ($bill?->unit_count ?? 0);
            $unitLabel   = $bill?->billing_unit ?? 'email';
            $unitLimit   = null;

            if ($isActive && $bill->plan_slug) {
                $tier      = ($pricingTiers[$dep->worker_slug] ?? collect())->firstWhere('plan_slug', $bill->plan_slug);
                $flatFull  = (float) ($tier?->monthly_flat_rate ?? 0);
                $unitLimit = $tier?->transaction_limit ?? null;
                $planLabel = $tier ? ucfirst($tier->plan_slug) . ' plan' : null;

                $start = $bill->billing_period_start ? \Carbon\Carbon::parse($bill->billing_period_start) : null;
                if ($start && $start->month == now()->month && $start->year == now()->year && $start->day > 1) {
                    $daysIn      = now()->daysInMonth;
                    $daysActive  = now()->diffInDays($start) + 1;
                    $flatCharge  = round($flatFull * ($daysActive / $daysIn), 2);
                    $prorated    = true;
                    $prorateNote = $daysActive . '/' . $daysIn . ' days from ' . $start->format('M j');
                } else {
                    $flatCharge = $flatFull;
                }
                $grandTotal += $flatCharge;
            }

            // AI passthrough per worker (cost × 1.30)
            $aiPassWorker = round($totalDepCost * 1.30, 4);
            $grandTotal  += $aiPassWorker;

            if ($flatCharge > 0 || $totalDepCost > 0 || $isTrial) {
                $invoiceWorkers[] = compact(
                    'dep', 'bill', 'isActive', 'isTrial',
                    'flatCharge', 'flatFull', 'prorated', 'prorateNote', 'planLabel',
                    'unitUsed', 'unitLabel', 'unitLimit',
                    'pipelineCost', 'pipelineTokens', 'pipelineCalls',
                    'testCost', 'testTokens', 'testCalls',
                    'totalDepCost', 'totalDepTokens', 'aiPassWorker'
                );
            }
        }
    @endphp

    @if(count($invoiceWorkers) > 0 || $totalCost > 0)
    <div class="rounded-2xl mb-6 overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-1" style="border-bottom:1px solid var(--border)">
            <div>
                <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Estimated Bill This Month</h2>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">One invoice accumulates all active worker subscriptions · final amount generated at period end</p>
            </div>
            <p class="text-xs shrink-0" style="color:var(--text-muted)">{{ now()->format('F Y') }}</p>
        </div>

        {{-- Per-worker blocks --}}
        @foreach($invoiceWorkers as $iw)
        <div class="px-5 py-4" style="border-bottom:1px solid var(--border)">

            {{-- Worker header --}}
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold text-gray-900"
                         style="background:var(--accent)">{{ strtoupper(substr($iw['dep']->worker_slug,0,1)) }}</div>
                    <span class="text-sm font-semibold" style="color:var(--text-primary)">{{ $iw['dep']->name }}</span>
                    @if($iw['isTrial'])
                        <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(var(--accent-rgb),0.15);color:#fbbf24;border:1px solid rgba(var(--accent-rgb),0.3)">Trial</span>
                    @elseif($iw['planLabel'])
                        <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(34,197,94,0.1);color:#4ade80;border:1px solid rgba(34,197,94,0.3)">{{ $iw['planLabel'] }}</span>
                    @endif
                </div>
                <span class="text-sm font-mono font-semibold" style="color:var(--text-primary)">
                    ${{ number_format($iw['flatCharge'] + $iw['aiPassWorker'], 4) }}
                </span>
            </div>

            <div class="space-y-2 pl-8">

                {{-- Subscription line --}}
                @if($iw['flatCharge'] > 0)
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs" style="color:var(--text-secondary)">Subscription · {{ $iw['planLabel'] }}</span>
                        @if($iw['prorated'])
                            <span class="ml-2 text-xs px-1.5 py-0.5 rounded" style="background:rgba(245,158,11,0.15);color:#fbbf24;border:1px solid rgba(245,158,11,0.3)">
                                prorated {{ $iw['prorateNote'] }}
                            </span>
                        @endif
                        <p class="text-xs mt-0.5" style="color:var(--text-muted)">
                            @if(!is_null($iw['unitLimit']))
                                {{ number_format($iw['unitUsed']) }} / {{ number_format($iw['unitLimit']) }} {{ $iw['unitLabel'] }}s used this period
                            @else
                                {{ number_format($iw['unitUsed']) }} {{ $iw['unitLabel'] }}s processed · unlimited
                            @endif
                            @if($iw['prorated'])
                                · full rate ${{ number_format($iw['flatFull'], 2) }}/mo
                            @endif
                        </p>
                    </div>
                    <span class="text-xs font-mono shrink-0 ml-4" style="color:var(--text-secondary)">${{ number_format($iw['flatCharge'], 2) }}</span>
                </div>
                @endif

                {{-- Pipeline AI cost --}}
                @if($iw['pipelineCost'] > 0)
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs" style="color:var(--text-secondary)">AI · Email pipeline</span>
                        <p class="text-xs mt-0.5" style="color:var(--text-muted)">
                            ${{ number_format($iw['pipelineCost'], 4) }} × 1.30 · {{ number_format($iw['pipelineCalls']) }} calls · {{ number_format($iw['pipelineTokens']) }} tokens
                        </p>
                    </div>
                    <span class="text-xs font-mono shrink-0 ml-4" style="color:var(--text-secondary)">${{ number_format($iw['pipelineCost'] * 1.30, 4) }}</span>
                </div>
                @endif

                {{-- Prompt testing / rewrite AI cost --}}
                @if($iw['testCost'] > 0)
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-xs" style="color:var(--text-secondary)">AI · Prompt testing & rewrites</span>
                        <p class="text-xs mt-0.5" style="color:var(--text-muted)">
                            ${{ number_format($iw['testCost'], 4) }} × 1.30 · {{ number_format($iw['testCalls']) }} test runs · {{ number_format($iw['testTokens']) }} tokens
                        </p>
                    </div>
                    <span class="text-xs font-mono shrink-0 ml-4" style="color:var(--text-secondary)">${{ number_format($iw['testCost'] * 1.30, 4) }}</span>
                </div>
                @endif

                {{-- Trial notice --}}
                @if($iw['isTrial'] && $iw['totalDepCost'] == 0 && $iw['flatCharge'] == 0)
                <p class="text-xs" style="color:var(--text-muted)">Free trial · no charge until you subscribe</p>
                @endif
            </div>
        </div>
        @endforeach

        {{-- Grand total --}}
        <div class="px-5 py-4 flex items-center justify-between">
            <div>
                <span class="text-sm font-semibold" style="color:var(--text-primary)">Estimated total</span>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">Collected as one invoice via Stripe at period end</p>
            </div>
            <span class="text-xl font-bold font-mono" style="color:var(--accent-text)">${{ number_format($grandTotal, 2) }}</span>
        </div>
    </div>
    @endif

    {{-- Worker subscriptions --}}
    <div class="rounded-2xl mb-6" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4" style="border-bottom:1px solid var(--border)">
            <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Worker Subscriptions</h2>
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
                    'trial'    => ['bg'=>'rgba(var(--accent-rgb),0.1)','border'=>'rgba(var(--accent-rgb),0.3)','text'=>'#fbbf24','label'=>'Trial'],
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
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                             style="background:rgba(var(--accent-rgb),0.15)">
                            <span class="font-bold text-sm" style="color:var(--accent-text)">{{ strtoupper(substr($dep->worker_slug,0,1)) }}</span>
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
                    @php
                        $currentPlanSlug = $bill?->plan_slug;
                        $currentTier     = $currentPlanSlug
                            ? ($pricingTiers[$dep->worker_slug] ?? collect())->firstWhere('plan_slug', $currentPlanSlug)
                            : null;
                        $displayPrice    = $currentTier ?? $price;
                    @endphp
                    @if($displayPrice)
                    <div class="text-right shrink-0">
                        <p class="text-gray-500 text-xs">Plan</p>
                        <p class="text-gray-300 text-sm">
                            @if($currentTier)
                                {{ ucfirst($currentTier->plan_slug) }} · ${{ number_format($currentTier->price_monthly ?? $currentTier->monthly_flat_rate ?? 0, 2) }}/mo
                            @else
                                ${{ number_format($displayPrice->monthly_flat_rate ?? 0, 2) }}/mo
                            @endif
                        </p>
                        <p class="text-gray-600 text-xs">
                            @if($currentTier && !is_null($currentTier->transaction_limit))
                                {{ number_format($currentTier->transaction_limit) }} {{ $bill?->billing_unit ?? 'email' }}s/mo
                            @elseif($currentTier)
                                Unlimited {{ $bill?->billing_unit ?? 'email' }}s
                            @endif
                        </p>
                    </div>
                    @endif

                    {{-- Action button --}}
                    <div class="shrink-0">
                        @if(($bill?->status ?? '') === 'trial')
                            <button onclick="togglePlans('plans-{{ $dep->id }}')"
                                    class="text-xs px-4 py-2 rounded-lg font-bold text-gray-900 hover:opacity-90 transition"
                                    style="background:var(--accent)">Choose Plan ↓</button>
                        @elseif(($bill?->status ?? '') === 'active')
                            <div class="text-right">
                                @php
                                    $activePlan     = $bill->plan_slug ?? 'starter';
                                    $activePricing  = ($pricingTiers[$dep->worker_slug] ?? collect())->firstWhere('plan_slug', $activePlan);
                                    $unitLabel      = $bill->billing_unit ?? 'email';
                                    $unitUsed       = (int) ($bill->unit_count ?? 0);
                                    $unitLimit      = $activePricing?->transaction_limit ?? null;
                                @endphp
                                <span class="text-xs px-2 py-0.5 rounded-full bg-green-900/40 text-green-400 border border-green-800/40 block mb-1">
                                    {{ ucfirst($activePlan) }} plan
                                </span>
                                @if($unitLimit)
                                    <span class="text-xs block mb-2" style="color:var(--text-muted)">
                                        {{ number_format($unitUsed) }} / {{ number_format($unitLimit) }} {{ $unitLabel }}s
                                    </span>
                                @else
                                    <span class="text-xs block mb-2" style="color:var(--text-muted)">{{ number_format($unitUsed) }} {{ $unitLabel }}s this month</span>
                                @endif
                                <a href="{{ route('billing.portal') }}"
                                   class="text-xs px-4 py-2 rounded-lg font-medium text-gray-400 border border-gray-700 hover:bg-gray-800 transition block text-center">Manage</a>
                            </div>
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
                            Trial: {{ $trialUsed }} / {{ $trialLimit }} emails used
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

                {{-- Plan picker (shown on trial, toggled by Choose Plan button) --}}
                @if(($bill?->status ?? '') === 'trial')
                @php $depTiers = $pricingTiers[$dep->worker_slug] ?? collect(); @endphp
                <div id="plans-{{ $dep->id }}" class="hidden mt-5 border-t pt-5" style="border-color:var(--border)">
                    <p class="text-xs mb-4" style="color:var(--text-muted)">Choose the plan that fits your volume:</p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">
                        @foreach($depTiers as $tier)
                        @php
                            $isEnterprise = $tier->plan_slug === 'enterprise';
                            $isPro        = $tier->plan_slug === 'pro';
                            $highlights   = json_decode($tier->plan_highlights ?? '[]', true);
                        @endphp
                        <div style="
                            background:var(--bg-raised);
                            border:1px solid {{ $isPro ? 'rgba(var(--accent-rgb),0.5)' : 'var(--border)' }};
                            border-radius:12px;padding:16px;
                            display:flex;flex-direction:column;gap:12px;position:relative;
                        ">
                            @if($isPro)
                            <span style="
                                position:absolute;top:-10px;left:14px;
                                font-size:10px;padding:2px 8px;border-radius:99px;
                                font-weight:700;color:#111;background:var(--accent)
                            ">Most popular</span>
                            @endif

                            <div>
                                <p style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $tier->display_name }}</p>
                                <p style="font-size:22px;font-weight:700;color:var(--text-primary);margin-top:4px;line-height:1">
                                    @if($isEnterprise) Custom
                                    @else ${{ number_format($tier->monthly_flat_rate, 0) }}<span style="font-size:11px;font-weight:400;color:var(--text-muted)">/mo</span>
                                    @endif
                                </p>
                                <p style="font-size:11px;color:var(--text-muted);margin-top:3px">
                                    @if($tier->transaction_limit) {{ number_format($tier->transaction_limit) }} emails/mo
                                    @else Unlimited emails
                                    @endif
                                </p>
                            </div>

                            <ul style="flex:1;display:flex;flex-direction:column;gap:6px;list-style:none;padding:0;margin:0">
                                @foreach($highlights as $h)
                                <li style="display:flex;align-items:flex-start;gap:6px;font-size:11px;color:var(--text-secondary)">
                                    <span style="color:#4ade80;flex-shrink:0;margin-top:1px">✓</span> {{ $h }}
                                </li>
                                @endforeach
                            </ul>

                            @if($isEnterprise)
                                <a href="mailto:hello@unit.report?subject=AVA Enterprise"
                                   style="display:block;text-align:center;font-size:12px;font-weight:600;padding:8px 12px;border-radius:8px;border:1px solid var(--border);color:var(--text-secondary);text-decoration:none">
                                    Contact Us
                                </a>
                            @else
                                <a href="{{ route('billing.checkout', $dep->id) }}?plan={{ $tier->plan_slug }}"
                                   style="display:block;text-align:center;font-size:12px;font-weight:700;padding:8px 12px;border-radius:8px;text-decoration:none;color:#111;
                                          {{ $isPro ? 'background:var(--accent)' : 'border:1px solid var(--border);color:var(--text-secondary)' }}">
                                    Get {{ ucfirst($tier->plan_slug) }}
                                </a>
                            @endif
                        </div>
                        @endforeach
                    </div>
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
            @if(auth()->user()->isAdmin())
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
                   class="text-xs text-gray-500" style="text-decoration:none">Download PDF →</a>

                @if(auth()->user()->isAdmin() && $isDue)
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

<script>
function togglePlans(id) {
    const el = document.getElementById(id);
    if (el) el.classList.toggle('hidden');
}

// Auto-open plan picker if ?pick={deploymentId} is in URL (e.g. from trial gate or Subscribe Now redirect)
(function() {
    const params = new URLSearchParams(window.location.search);
    const pickId = params.get('pick');
    if (pickId) {
        const el = document.getElementById('plans-' + pickId);
        if (el) {
            el.classList.remove('hidden');
            setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'center' }), 150);
        }
    }
})();
</script>
</x-app-layout>

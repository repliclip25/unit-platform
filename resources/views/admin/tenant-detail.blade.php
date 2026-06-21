<x-app-layout :title="'Tenant · ' . $tenant->name">
@php
    $isBlocked      = (bool) $tenant->blocked_at;
    $memberSince    = \Carbon\Carbon::parse($tenant->created_at);
    $memberDays     = $memberSince->diffInDays(now());
    $memberDur      = $memberSince->diffForHumans(null, true, true, 2);
    $activeSub      = $subscriptions->firstWhere('stripe_status', 'active') ?? $subscriptions->first();
    $subSince       = $activeSub ? \Carbon\Carbon::parse($activeSub->created_at) : null;
    $subDur         = $subSince ? $subSince->diffForHumans(null, true, true, 2) : null;
    $activeDepCount = $deployments->where('status','active')->count();
    $trialDepCount  = $deployments->where('billing_status','trial')->count();
    $paidDepCount   = $deployments->where('billing_status','active')->count();
    $monthlyMrr     = $deployments->where('billing_status','active')->sum('monthly_flat_rate');

    $riskLevel = count($churnSignals) === 0 ? 'low'
               : (count($churnSignals) === 1 ? 'medium' : 'high');
    $riskColor = ['low' => '#4ade80', 'medium' => '#f3c531', 'high' => '#f87171'][$riskLevel];

    $ctaOptions = [
        'upgrade'   => ['label' => 'Upgrade Now →',       'url' => url('/billing')],
        'dashboard' => ['label' => 'Go to Dashboard →',   'url' => url('/dashboard')],
        'billing'   => ['label' => 'Manage Billing →',    'url' => url('/billing')],
        'workers'   => ['label' => 'Explore Workers →',   'url' => url('/workers')],
        'support'   => ['label' => 'Contact Support →',   'url' => 'mailto:' . config('mail.from.address')],
        'none'      => ['label' => '',                    'url' => ''],
    ];

    $messageTemplates = [
        'feedback'     => ['subject' => 'Quick question about your UNIT experience', 'body' => "Hi {$tenant->name},\n\nYou've been using UNIT for a little while now and we'd love to hear how it's going.\n\nWhat's been the most valuable thing so far? And is there anything that hasn't worked the way you expected?\n\nEven a sentence or two helps us improve for you.\n\nBest,\nThe UNIT Team", 'cta' => 'none'],
        'upsell'       => ['subject' => 'Unlock unlimited processing on UNIT', 'body' => "Hi {$tenant->name},\n\nYou've been putting UNIT to work — and we've noticed! You're getting close to your trial limit.\n\nUpgrading to an active plan gives you:\n· Unlimited transactions\n· Full AI cost reporting\n· Priority support\n· No interruptions\n\nYour workers stay running. You stay ahead.", 'cta' => 'upgrade'],
        'trial_end'    => ['subject' => 'Your free trial is ending soon', 'body' => "Hi {$tenant->name},\n\nJust a heads-up — your UNIT trial is almost up.\n\nDon't let your workers go offline. Upgrade before your trial ends to keep processing without interruption.\n\nWe built UNIT to save you time on every renewal cycle. Let's keep it going.", 'cta' => 'upgrade'],
        'check_in'     => ['subject' => "How's UNIT working for you?", 'body' => "Hi {$tenant->name},\n\nWanted to check in personally and see how things are going with UNIT.\n\nAre the workers processing the way you expected? Anything slowing you down?\n\nReply to this — we read every response.\n\nBest,\nThe UNIT Team", 'cta' => 'none'],
        'reengagement' => ['subject' => 'We miss you — your workers are ready', 'body' => "Hi {$tenant->name},\n\nWe noticed you haven't logged in recently. Your workers are still deployed and ready to run.\n\nIf something felt off or you hit a snag, we'd love to help. Just reply to this email.\n\nCome back and let's make UNIT work the way you need it to.", 'cta' => 'dashboard'],
        'billing_issue'=> ['subject' => 'Action needed: billing issue on your account', 'body' => "Hi {$tenant->name},\n\nWe noticed a billing issue on your account that needs your attention. To avoid any disruption to your workers, please review your payment method.\n\nIf you have any questions, just reply to this email — we're happy to help.", 'cta' => 'billing'],
        'custom'       => ['subject' => '', 'body' => '', 'cta' => 'none'],
    ];
@endphp

<div class="space-y-6 pb-12">

    {{-- ── Header ──────────────────────────────────────────── --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.tenants') }}" class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Tenants
            </a>
            <div class="w-px h-4 bg-gray-800"></div>
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-base font-bold shrink-0"
                     style="background:{{ $isBlocked ? 'rgba(239,68,68,0.15)' : 'rgba(243,197,49,0.15)' }};color:{{ $isBlocked ? '#fca5a5' : '#f3c531' }}">
                    {{ strtoupper(substr($tenant->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-white font-semibold text-base">{{ $tenant->name }}</h1>
                        @if($isBlocked)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-red-900/40 text-red-400 border border-red-800/50">Blocked</span>
                        @elseif($paidDepCount > 0)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-900/30 text-green-400 border border-green-800/40">Subscribed</span>
                        @elseif($trialDepCount > 0)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-brand/15 text-brand border border-brand/30">Trial</span>
                        @endif
                        @if(count($churnSignals) > 0)
                            <span class="text-xs px-2 py-0.5 rounded-full border font-medium"
                                  style="background:{{ $riskColor }}18;color:{{ $riskColor }};border-color:{{ $riskColor }}40">
                                {{ ucfirst($riskLevel) }} Risk
                            </span>
                        @endif
                    </div>
                    <p class="text-gray-500 text-xs mt-0.5">{{ $tenant->email }} · #{{ $tenant->id }} · {{ $memberDur }} on platform</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($daysSinceLogin !== null)
                <span class="text-xs border border-gray-800 rounded px-2 py-1 {{ $daysSinceLogin > 7 ? 'text-red-400' : 'text-gray-500' }}">
                    Last login {{ $daysSinceLogin === 0 ? 'today' : $daysSinceLogin . 'd ago' }}
                </span>
            @endif
            <span class="text-xs border border-gray-800 rounded px-2 py-1 text-gray-500">{{ $tenant->role ?? 'tenant' }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="text-sm rounded-xl px-4 py-3" style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);color:#86efac">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="text-sm rounded-xl px-4 py-3" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#fca5a5">{{ session('error') }}</div>
    @endif

    {{-- ── Churn Risk Banner ───────────────────────────────── --}}
    @if(count($churnSignals) > 0)
    <div class="rounded-xl px-5 py-3.5 flex items-start justify-between gap-4"
         style="background:{{ $riskColor }}0f;border:1px solid {{ $riskColor }}30">
        <div>
            <p class="text-sm font-semibold" style="color:{{ $riskColor }}">{{ ucfirst($riskLevel) }} churn risk detected</p>
            <div class="flex flex-wrap gap-x-4 gap-y-0.5 mt-1">
                @foreach($churnSignals as $sig)
                    <span class="text-xs text-gray-400">· {{ $sig }}</span>
                @endforeach
            </div>
        </div>
        <span class="text-xs text-gray-500 shrink-0 mt-0.5">Take action →</span>
    </div>
    @endif

    {{-- ── KPI Bar ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-2.5">
        @php $kpis = [
            ['l'=>'Member',        'v'=>$memberSince->format('M j, Y'),         's'=>$memberDur],
            ['l'=>'Subscription',  'v'=>$subSince ? $subSince->format('M j, Y') : '—', 's'=>$subDur ? 'for '.$subDur : 'No active sub'],
            ['l'=>'Workers',       'v'=>$deployments->count(),                  's'=>$activeDepCount.' active · '.$trialDepCount.' trial'],
            ['l'=>'Est. MRR',      'v'=>'$'.number_format($monthlyMrr,2),       's'=>$paidDepCount.' paid plan(s)'],
            ['l'=>'AI Spend',      'v'=>'$'.number_format($totalSpend,4),       's'=>'all time · avg $'.number_format($usageAvg->avg_cost ?? 0, 4).'/call'],
            ['l'=>'Success Rate',  'v'=>$txSuccessRate !== null ? $txSuccessRate.'%' : '—', 's'=>$txTotal.' total · '.$txFailed.' failed'],
            ['l'=>'Page Views',    'v'=>number_format($totalViews),              's'=>$viewsThisWeek.' this week'],
        ]; @endphp
        @foreach($kpis as $k)
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-3.5 py-3">
            <p class="text-gray-500 text-xs uppercase tracking-wide leading-none">{{ $k['l'] }}</p>
            <p class="text-white font-bold text-lg mt-1.5 leading-none">{{ $k['v'] }}</p>
            <p class="text-gray-600 text-xs mt-1 leading-tight">{{ $k['s'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── Referral Card (Admin View) ────────────────────── --}}
    <div class="rounded-2xl overflow-hidden relative"
         style="background:linear-gradient(135deg,#1a1404 0%,#221905 50%,#111 100%);border:1px solid rgba(243,197,49,0.2)">
        <div class="absolute top-0 right-0 w-48 h-48 rounded-full pointer-events-none"
             style="background:radial-gradient(circle,rgba(243,197,49,0.10) 0%,transparent 70%);transform:translate(30%,-30%)"></div>
        <div class="relative px-6 py-5">
            <div class="flex items-start justify-between gap-6 flex-wrap">

                {{-- Identity + source --}}
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-semibold uppercase tracking-widest" style="color:#f3c531">Referral Program</span>
                        @if($referredBy)
                            <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(243,197,49,0.12);border:1px solid rgba(243,197,49,0.25);color:#f3c531">
                                Referred by {{ $referredBy->name }}
                            </span>
                        @endif
                    </div>
                    <p class="text-white font-semibold">{{ $referralStats->converted }} paid conversion(s) · ${{ number_format($referralStats->earned, 2) }} earned · ${{ number_format($referralStats->balance, 2) }} balance</p>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs font-mono" style="color:rgba(243,197,49,0.7)">Code: {{ $referralCode }}</span>
                        <span class="text-xs" style="color:rgba(243,197,49,0.4)">·</span>
                        <span class="text-xs" style="color:rgba(243,197,49,0.5)">{{ $referralStats->signups }} signed up · {{ $referralStats->converted }} converted</span>
                        @if($referralStats->nextTier)
                        <span class="text-xs" style="color:rgba(243,197,49,0.5)">· {{ $referralStats->converted }}/{{ $referralStats->nextTier }} to {{ $referralStats->tierLabel }}</span>
                        @endif
                    </div>
                </div>

                {{-- Referral list --}}
                @if($referralList->isNotEmpty())
                <div class="space-y-1.5 min-w-64">
                    @foreach($referralList as $ref)
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-5 h-5 rounded-md flex items-center justify-center text-xs font-bold shrink-0"
                                 style="background:rgba(243,197,49,0.15);color:#f3c531">{{ strtoupper(substr($ref->referee_name,0,1)) }}</div>
                            <span class="text-white text-xs truncate">{{ $ref->referee_name }}</span>
                            <span class="text-xs shrink-0 px-1.5 py-0.5 rounded"
                                  style="background:{{ $ref->event === 'paid_conversion' ? 'rgba(74,222,128,0.12)' : 'rgba(243,197,49,0.10)' }};color:{{ $ref->event === 'paid_conversion' ? '#4ade80' : '#f3c531' }}">
                                {{ $ref->event === 'paid_conversion' ? 'Paid ✓' : 'Trial' }}
                            </span>
                        </div>
                        <div class="text-right shrink-0">
                            @if($ref->credit_usd > 0)
                                <span class="text-xs font-semibold" style="color:#f3c531">+${{ number_format($ref->credit_usd,2) }}</span>
                            @endif
                            <span class="text-xs ml-2" style="color:rgba(243,197,49,0.4)">{{ \Carbon\Carbon::parse($ref->created_at)->format('M j') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-xs" style="color:rgba(243,197,49,0.4)">No referrals yet</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ── 3-col grid ──────────────────────────────────────── --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- ────── LEFT 2/3 ────── --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Subscription --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-white text-sm font-semibold">Subscription & Billing</h2>
                        <p class="text-gray-500 text-xs mt-0.5">Stripe status · deployment billing · trial progress</p>
                    </div>
                    @if($activeSub)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-green-900/25 text-green-400 border border-green-800/40 capitalize">{{ $activeSub->stripe_status }}</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-800 text-gray-500 border border-gray-700">No Stripe sub</span>
                    @endif
                </div>

                {{-- Stripe subs --}}
                @if($subscriptions->isNotEmpty())
                <div class="divide-y divide-gray-800/50">
                    @foreach($subscriptions as $sub)
                    @php
                        $sColor = match($sub->stripe_status) {
                            'active'=>'text-green-400','trialing'=>'text-brand',
                            'past_due'=>'text-red-400','canceled'=>'text-gray-600',default=>'text-gray-500',
                        };
                        $items = $subItems->where('subscription_id',$sub->id);
                    @endphp
                    <div class="px-5 py-3.5 flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="{{ $sColor }} text-xs font-semibold capitalize">{{ $sub->stripe_status }}</span>
                                <span class="text-gray-600 text-xs font-mono">{{ $sub->stripe_id }}</span>
                            </div>
                            <p class="text-gray-600 text-xs mt-1">{{ $sub->type ?? 'default' }}
                                @if($sub->trial_ends_at) · Trial ends <span class="text-brand">{{ \Carbon\Carbon::parse($sub->trial_ends_at)->format('M j') }}</span>@endif
                                @if($sub->ends_at) · Ends <span class="text-red-400">{{ \Carbon\Carbon::parse($sub->ends_at)->format('M j, Y') }}</span>@endif
                            </p>
                            @foreach($items as $item)
                            <p class="text-gray-700 text-xs font-mono mt-0.5">{{ $item->stripe_price }}{{ $item->quantity > 1 ? ' × '.$item->quantity : '' }}</p>
                            @endforeach
                        </div>
                        <div class="text-right shrink-0 text-xs">
                            <p class="text-gray-600">Since</p>
                            <p class="text-white">{{ \Carbon\Carbon::parse($sub->created_at)->format('M j, Y') }}</p>
                            <p class="text-gray-600">{{ \Carbon\Carbon::parse($sub->created_at)->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-4 text-xs text-gray-500">No Stripe subscriptions. Tenant may be on free trial.</div>
                @endif

                {{-- Deployment billing --}}
                <div class="border-t border-gray-800 px-5 py-4">
                    <p class="text-gray-400 text-xs font-medium mb-3 uppercase tracking-wide">Deployment Billing</p>
                    @foreach($deployments as $dep)
                    @php
                        $bColor = match($dep->billing_status ?? 'none') {
                            'active'=>'text-green-400','trial'=>'text-brand','past_due'=>'text-red-400',default=>'text-gray-600',
                        };
                        $tPct = ($dep->trial_transactions_limit ?? 10) > 0
                            ? min(100,(($dep->trial_transactions_used ?? 0) / ($dep->trial_transactions_limit ?? 10))*100) : 0;
                    @endphp
                    <div class="flex items-center gap-3 mb-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-white text-xs font-medium truncate">{{ $dep->name }}</span>
                                <span class="{{ $bColor }} text-xs capitalize shrink-0">{{ $dep->billing_status ?? 'none' }}</span>
                                @if($dep->monthly_flat_rate)<span class="text-gray-600 text-xs">${{ number_format($dep->monthly_flat_rate,2) }}/mo</span>@endif
                            </div>
                            @if(($dep->billing_status ?? '') === 'trial')
                            <div class="flex items-center gap-2 mt-1">
                                <div class="w-28 h-1 rounded-full bg-gray-800 overflow-hidden">
                                    <div class="h-full rounded-full {{ $tPct >= 80 ? 'bg-red-500' : 'bg-brand' }}" style="width:{{ $tPct }}%"></div>
                                </div>
                                <span class="text-gray-600 text-xs">{{ $dep->trial_transactions_used ?? 0 }}/{{ $dep->trial_transactions_limit ?? 10 }} tx</span>
                            </div>
                            @endif
                        </div>
                        <span class="text-gray-600 text-xs shrink-0">{{ \Carbon\Carbon::parse($dep->deployed_at)->format('M j') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Transaction Health --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-white text-sm font-semibold">Transaction Health</h2>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $txTotal }} total · avg {{ number_format($usageAvg->avg_tokens ?? 0) }} tokens/call</p>
                    </div>
                    <span class="text-lg font-bold {{ ($txSuccessRate ?? 100) >= 80 ? 'text-green-400' : (($txSuccessRate ?? 100) >= 50 ? 'text-brand' : 'text-red-400') }}">
                        {{ $txSuccessRate !== null ? $txSuccessRate.'%' : '—' }}
                    </span>
                </div>
                <div class="px-5 py-4">
                    @php
                        $txDisplay = [
                            'completed'   => ['label'=>'Completed',    'color'=>'#4ade80'],
                            'draft_ready' => ['label'=>'Draft Ready',  'color'=>'#f3c531'],
                            'approved'    => ['label'=>'Approved',     'color'=>'#60a5fa'],
                            'failed'      => ['label'=>'Failed',       'color'=>'#f87171'],
                            'processing'  => ['label'=>'Processing',   'color'=>'#a78bfa'],
                            'received'    => ['label'=>'Received',     'color'=>'#8e8ea0'],
                            'dismissed'   => ['label'=>'Dismissed',    'color'=>'#555568'],
                        ];
                    @endphp
                    <div class="grid grid-cols-3 md:grid-cols-4 gap-2 mb-4">
                        @foreach($txDisplay as $status => $info)
                        @php $cnt = $txStats[$status]->cnt ?? 0; if(!$cnt) continue; @endphp
                        <div class="rounded-lg px-3 py-2.5 text-center" style="background:{{ $info['color'] }}12;border:1px solid {{ $info['color'] }}25">
                            <p class="text-sm font-bold" style="color:{{ $info['color'] }}">{{ $cnt }}</p>
                            <p class="text-gray-500 text-xs">{{ $info['label'] }}</p>
                        </div>
                        @endforeach
                    </div>
                    {{-- Success/fail bar --}}
                    @if($txTotal > 0)
                    <div class="h-2 rounded-full bg-gray-800 overflow-hidden flex">
                        <div class="h-full bg-green-500" style="width:{{ ($txCompleted/$txTotal)*100 }}%"></div>
                        <div class="h-full bg-red-500" style="width:{{ ($txFailed/$txTotal)*100 }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600 mt-1">
                        <span>{{ $txCompleted }} successful</span>
                        <span>{{ $txFailed }} failed</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- AI Spend Chart --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-white text-sm font-semibold">AI Spend — Last 8 Months</h2>
                        <p class="text-gray-500 text-xs mt-0.5">Total: ${{ number_format($totalSpend,4) }}</p>
                    </div>
                    @if($monthlyMrr > 0)
                    <span class="text-xs text-green-400 font-medium">${{ number_format($monthlyMrr,2) }}/mo MRR</span>
                    @endif
                </div>
                <div class="px-5 py-5">
                    @if($monthlySpend->isEmpty())
                        <p class="text-gray-600 text-sm text-center py-4">No usage data yet</p>
                    @else
                    @php $maxSpend = $monthlySpend->max('spend') ?: 1; @endphp
                    <div class="space-y-2">
                        @foreach($monthlySpend as $mo)
                        @php $pct = min(100, ($mo->spend / $maxSpend) * 100); @endphp
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 text-xs w-14 shrink-0">{{ \Carbon\Carbon::parse($mo->month.'-01')->format("M 'y") }}</span>
                            <div class="flex-1 h-5 rounded bg-gray-800 overflow-hidden relative">
                                <div class="h-full rounded" style="width:{{ $pct }}%;background:linear-gradient(90deg,#f3c531,#d9a91f)"></div>
                                <span class="absolute inset-0 flex items-center px-2 text-xs font-medium" style="color:{{ $pct > 25 ? '#1a1404' : '#f3c531' }}">
                                    ${{ number_format($mo->spend,4) }}
                                </span>
                            </div>
                            <span class="text-gray-600 text-xs shrink-0 w-32 text-right">{{ number_format($mo->tokens) }} tok · {{ $mo->calls }} calls</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Usage Map --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-white text-sm font-semibold">Usage Map</h2>
                        <p class="text-gray-500 text-xs mt-0.5">30-day page activity · {{ $totalViews }} lifetime views</p>
                    </div>
                    <div class="text-right">
                        <p class="text-brand text-xs font-semibold">{{ $viewsThisWeek }} this week</p>
                        @if($lastActivity)
                        <p class="text-gray-600 text-xs">Last: {{ \Carbon\Carbon::parse($lastActivity)->diffForHumans() }}</p>
                        @endif
                    </div>
                </div>
                @if($usageMap->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <p class="text-gray-500 text-sm">No activity tracked yet</p>
                        <p class="text-gray-600 text-xs mt-1">Tracking begins on next page visit</p>
                    </div>
                @else
                @php
                    $sections = $usageMap->groupBy('section');
                    $maxV = $usageMap->max('visits') ?: 1;
                    $sColors = ['dashboard'=>'#f3c531','workers'=>'#4ade80','billing'=>'#60a5fa','transactions'=>'#f97316','settings'=>'#a78bfa','admin'=>'#f43f5e'];
                @endphp
                <div class="px-5 py-4 space-y-5">
                    <div class="grid grid-cols-3 md:grid-cols-6 gap-2">
                        @foreach($sections as $sec => $pages)
                        @php $c = $sColors[$sec ?? ''] ?? '#8e8ea0'; $sv = $pages->sum('visits'); @endphp
                        <div class="rounded-xl px-3 py-2.5 text-center" style="background:{{ $c }}18;border:1px solid {{ $c }}30">
                            <p class="text-sm font-bold" style="color:{{ $c }}">{{ $sv }}</p>
                            <p class="text-gray-500 text-xs capitalize mt-0.5">{{ $sec ?? 'other' }}</p>
                        </div>
                        @endforeach
                    </div>
                    <div class="space-y-1.5">
                        @foreach($usageMap->take(15) as $row)
                        @php $pct = min(100,($row->visits/$maxV)*100); $c = $sColors[$row->section ?? ''] ?? '#8e8ea0'; @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full shrink-0" style="background:{{ $c }}"></div>
                            <span class="text-gray-400 text-xs w-44 truncate font-mono shrink-0">{{ $row->page }}</span>
                            <div class="flex-1 h-1.5 rounded-full bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full" style="width:{{ $pct }}%;background:{{ $c }}"></div>
                            </div>
                            <span class="text-gray-500 text-xs w-6 text-right shrink-0">{{ $row->visits }}</span>
                            <span class="text-gray-700 text-xs w-16 text-right shrink-0">{{ \Carbon\Carbon::parse($row->last_seen)->diffForHumans(null,true,true) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Fast Track --}}
            @if($fastTracks->isNotEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                    <h2 class="text-white text-sm font-semibold">Fast Track History</h2>
                    <span class="text-xs text-gray-600 border border-gray-800 rounded px-2 py-0.5">{{ $fastTracks->count() }}</span>
                </div>
                <div class="divide-y divide-gray-800/50">
                    @foreach($fastTracks as $ft)
                    <div class="px-5 py-3 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-white text-xs font-medium truncate">{{ $ft->name ?: $ft->email }}</p>
                                <span class="text-xs px-1.5 py-0.5 rounded bg-brand/10 text-brand shrink-0">{{ $ft->worker_slug }}</span>
                            </div>
                            <p class="text-gray-600 text-xs">{{ $ft->email }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-xs {{ $ft->subscribed ? 'text-green-400' : 'text-gray-600' }}">{{ $ft->subscribed ? 'Subscribed ✓' : 'Not subscribed' }}</span>
                            <p class="text-gray-700 text-xs">{{ \Carbon\Carbon::parse($ft->created_at)->format('M j, Y') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Recent Transactions --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800">
                    <h2 class="text-white text-sm font-semibold">Recent Transactions</h2>
                </div>
                @if($recentTx->isEmpty())
                    <div class="px-5 py-6 text-center"><p class="text-gray-500 text-sm">No transactions</p></div>
                @else
                <div class="divide-y divide-gray-800/40">
                    @foreach($recentTx as $tx)
                    @php $txColor = ['completed'=>'text-green-400','draft_ready'=>'text-brand','failed'=>'text-red-400','processing'=>'text-blue-400','dismissed'=>'text-gray-700'][$tx->status] ?? 'text-gray-500'; @endphp
                    <div class="px-5 py-2.5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="{{ $txColor }} text-xs font-medium capitalize shrink-0">{{ str_replace('_',' ',$tx->status) }}</span>
                            <span class="text-gray-500 text-xs truncate">{{ $tx->category ?? $tx->worker_slug }}</span>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="text-gray-700 text-xs">{{ \Carbon\Carbon::parse($tx->created_at)->diffForHumans() }}</span>
                            <a href="{{ route('transactions.show', $tx->id) }}" class="text-xs text-brand hover:underline">View →</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>

        {{-- ────── RIGHT 1/3 ────── --}}
        <div class="space-y-5">

            {{-- Account --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800"><h2 class="text-white text-sm font-semibold">Account</h2></div>
                <div class="px-5 py-4 space-y-2.5 text-xs">
                    @foreach([
                        ['Member since', $memberSince->format('M j, Y') . ' ('.$memberDur.')', ''],
                        ['Email',        $tenant->email, ''],
                        ['Role',         $tenant->role ?? 'tenant', 'text-brand font-medium'],
                        ['Verified',     $tenant->email_verified_at ? 'Yes ✓' : 'No', $tenant->email_verified_at ? 'text-green-400' : 'text-amber-400'],
                        ['Stripe ID',    $tenant->stripe_id ?? '—', 'font-mono text-gray-400 text-xs'],
                        ['Card',         $tenant->pm_type ? strtoupper($tenant->pm_type).' ···· '.$tenant->pm_last_four : '—', ''],
                        ['Last login',   $lastLogin ? \Carbon\Carbon::createFromTimestamp($lastLogin)->diffForHumans() : 'Never', $daysSinceLogin > 7 ? 'text-red-400' : 'text-white'],
                    ] as [$label, $value, $class])
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-gray-500 shrink-0">{{ $label }}</span>
                        <span class="text-white {{ $class }} text-right truncate">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Access Control --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800">
                    <h2 class="text-white text-sm font-semibold">Access Control</h2>
                </div>
                <div class="px-5 py-4 space-y-4">
                    <div>
                        <p class="text-gray-400 text-xs font-medium mb-2">Monthly Spend Cap</p>
                        <form method="POST" action="{{ route('admin.tenants.spend-cap', $tenant->id) }}" class="flex gap-2">
                            @csrf
                            <input type="number" name="cap" step="0.01" min="0" value="{{ $tenant->monthly_spend_cap }}" placeholder="No cap"
                                   class="flex-1 text-xs px-3 py-2 rounded-lg border border-gray-800 bg-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-brand">
                            <button class="text-xs px-3 py-2 rounded-lg bg-brand text-brand-text font-semibold hover:bg-brand-deep transition shrink-0">Set</button>
                        </form>
                        <p class="text-gray-700 text-xs mt-1">{{ $tenant->monthly_spend_cap ? '$'.number_format($tenant->monthly_spend_cap,2).'/mo' : 'No cap' }}</p>
                    </div>
                    <div class="border-t border-gray-800 pt-4" x-data="{ open: false }">
                        @if($isBlocked)
                            <div class="rounded-lg px-3 py-2.5 mb-3 text-xs" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2)">
                                <p class="text-red-400 font-medium">Blocked {{ \Carbon\Carbon::parse($tenant->blocked_at)->diffForHumans() }}</p>
                                @if($tenant->block_reason)<p class="text-gray-500 mt-0.5">{{ $tenant->block_reason }}</p>@endif
                            </div>
                            <form method="POST" action="{{ route('admin.tenants.toggle-block', $tenant->id) }}">@csrf
                                <button class="w-full text-xs py-2 rounded-lg bg-green-900/30 text-green-400 border border-green-800/40 hover:bg-green-800/40 transition font-medium">Unblock Tenant</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.tenants.toggle-block', $tenant->id) }}">@csrf
                                <div x-show="!open">
                                    <button type="button" @click="open=true" class="w-full text-xs py-2 rounded-lg bg-red-900/30 text-red-400 border border-red-800/40 hover:bg-red-800/40 transition font-medium">Block Tenant</button>
                                </div>
                                <div x-show="open" class="space-y-2">
                                    <textarea name="reason" rows="2" placeholder="Block reason…" required class="w-full text-xs px-3 py-2 rounded-lg border border-gray-800 bg-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-red-500 resize-none"></textarea>
                                    <div class="flex gap-2">
                                        <button type="button" @click="open=false" class="flex-1 text-xs py-2 rounded-lg border border-gray-800 text-gray-500 hover:text-white transition">Cancel</button>
                                        <button class="flex-1 text-xs py-2 rounded-lg bg-red-900/40 text-red-400 border border-red-800/40 transition font-medium">Confirm</button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                    @if($apiKeys->isNotEmpty())
                    <div class="border-t border-gray-800 pt-4">
                        <p class="text-gray-400 text-xs font-medium mb-2">API Keys</p>
                        @foreach($apiKeys as $k)
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-500 capitalize">{{ $k->provider }}</span>
                            <span class="text-green-400">Active</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @if($sessions->isNotEmpty())
                    <div class="border-t border-gray-800 pt-4">
                        <p class="text-gray-400 text-xs font-medium mb-2">Sessions ({{ $sessions->count() }})</p>
                        @foreach($sessions as $s)
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600 font-mono">{{ substr($s->id,0,10) }}…</span>
                            <span class="text-gray-600">{{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Security --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800"><h2 class="text-white text-sm font-semibold">Security</h2></div>
                <div class="px-5 py-4 space-y-4">
                    <div x-data="{ open: false }">
                        <div class="flex justify-between items-center">
                            <p class="text-gray-400 text-xs font-medium">Admin Password Reset</p>
                            <button @click="open=!open" class="text-xs text-brand hover:underline">Change</button>
                        </div>
                        <form x-show="open" method="POST" action="{{ route('admin.tenants.reset-password', $tenant->id) }}" class="mt-3 space-y-2" x-transition>
                            @csrf
                            <input type="password" name="password" placeholder="New password (min 8)" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-800 bg-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-brand">
                            <button class="w-full text-xs py-2 rounded-lg bg-brand text-brand-text font-semibold hover:bg-brand-deep transition">Set Password</button>
                        </form>
                    </div>
                    <div class="border-t border-gray-800 pt-4">
                        <div class="flex justify-between items-center">
                            <p class="text-gray-400 text-xs font-medium">Two-Factor Auth</p>
                            <span class="text-xs px-2 py-0.5 rounded bg-gray-800 text-gray-500 border border-gray-700">Not configured</span>
                        </div>
                        <p class="text-gray-600 text-xs mt-1.5">Managed by the tenant via account settings.</p>
                    </div>
                </div>
            </div>

            {{-- ── Message Tenant ── --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden"
                 x-data="messagingPanel()" x-init="init()">
                <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-white text-sm font-semibold">Message Tenant</h2>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $messageLog->count() }} sent previously</p>
                    </div>
                    @if($messageLog->isNotEmpty())
                    <button @click="showLog=!showLog" class="text-xs text-gray-500 hover:text-white transition">History</button>
                    @endif
                </div>

                {{-- Message history --}}
                @if($messageLog->isNotEmpty())
                <div x-show="showLog" class="border-b border-gray-800 divide-y divide-gray-800/50">
                    @foreach($messageLog as $ml)
                    <div class="px-5 py-3">
                        <div class="flex justify-between items-start">
                            <p class="text-white text-xs font-medium">{{ $ml->subject }}</p>
                            <span class="text-gray-700 text-xs shrink-0 ml-2">{{ \Carbon\Carbon::parse($ml->sent_at)->format('M j') }}</span>
                        </div>
                        <p class="text-gray-600 text-xs mt-0.5 line-clamp-1">{{ $ml->body }}</p>
                        <span class="text-xs px-1.5 py-0.5 rounded bg-gray-800 text-gray-500 mt-1 inline-block">{{ $ml->template }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                <div class="px-5 py-4 space-y-3">
                    {{-- Template picker --}}
                    <div>
                        <label class="text-gray-400 text-xs font-medium block mb-1.5">Template</label>
                        <select x-model="template" @change="applyTemplate()" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-800 bg-gray-800 text-white focus:outline-none focus:border-brand">
                            <option value="custom">— Custom message —</option>
                            <option value="feedback">💬 Feedback Request</option>
                            <option value="upsell">⬆️ Upsell — Upgrade Plan</option>
                            <option value="trial_end">⏳ Trial Ending Soon</option>
                            <option value="check_in">👋 Check-In</option>
                            <option value="reengagement">🔁 Re-engagement</option>
                            <option value="billing_issue">⚠️ Billing Issue</option>
                        </select>
                    </div>

                    {{-- AI Generate --}}
                    <div class="rounded-lg px-3 py-2.5" style="background:rgba(243,197,49,0.06);border:1px solid rgba(243,197,49,0.15)">
                        <p class="text-brand text-xs font-medium mb-2">✦ AI Draft</p>
                        <div class="flex gap-2">
                            <select x-model="aiGoal" class="flex-1 text-xs px-2 py-1.5 rounded border border-gray-800 bg-gray-800 text-white focus:outline-none focus:border-brand">
                                <option value="feedback">Feedback request</option>
                                <option value="upsell">Upsell to paid</option>
                                <option value="reengagement">Re-engagement</option>
                                <option value="check_in">Friendly check-in</option>
                            </select>
                            <button type="button" @click="generateAI()" :disabled="aiLoading"
                                    class="text-xs px-3 py-1.5 rounded bg-brand text-brand-text font-semibold hover:bg-brand-deep transition shrink-0 disabled:opacity-60">
                                <span x-show="!aiLoading">Generate</span>
                                <span x-show="aiLoading">…</span>
                            </button>
                        </div>
                        <p x-show="aiError" class="text-red-400 text-xs mt-1.5" x-text="aiError"></p>
                    </div>

                    <form method="POST" action="{{ route('admin.tenants.message', $tenant->id) }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="template" :value="template">
                        <div>
                            <label class="text-gray-400 text-xs font-medium block mb-1.5">Subject</label>
                            <input type="text" name="subject" x-model="subject" placeholder="Subject…" required
                                   class="w-full text-xs px-3 py-2 rounded-lg border border-gray-800 bg-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-brand">
                        </div>
                        <div>
                            <label class="text-gray-400 text-xs font-medium block mb-1.5">Message</label>
                            <textarea name="body" x-model="body" rows="8" placeholder="Message body…" required
                                      class="w-full text-xs px-3 py-2 rounded-lg border border-gray-800 bg-gray-800 text-white placeholder-gray-600 focus:outline-none focus:border-brand resize-none leading-relaxed"></textarea>
                        </div>

                        {{-- CTA --}}
                        <div>
                            <label class="text-gray-400 text-xs font-medium block mb-1.5">Call to Action Button</label>
                            <select x-model="ctaKey" @change="applyCta()" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-800 bg-gray-800 text-white focus:outline-none focus:border-brand mb-2">
                                <option value="none">— No CTA button —</option>
                                <option value="upgrade">⬆️ Upgrade Now</option>
                                <option value="dashboard">🏠 Go to Dashboard</option>
                                <option value="billing">💳 Manage Billing</option>
                                <option value="workers">⚡ Explore Workers</option>
                                <option value="support">💬 Contact Support</option>
                            </select>
                            <template x-if="ctaKey !== 'none'">
                                <div class="space-y-1.5">
                                    <input type="hidden" name="cta_label" :value="ctaLabel">
                                    <input type="hidden" name="cta_url"   :value="ctaUrl">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600 text-xs">Preview:</span>
                                        <span class="text-xs px-3 py-1 rounded-md font-semibold" style="background:#f3c531;color:#1a1404" x-text="ctaLabel"></span>
                                    </div>
                                    <input type="text" x-model="ctaUrl" class="w-full text-xs px-2 py-1.5 rounded border border-gray-800 bg-gray-800 text-gray-400 focus:outline-none focus:border-brand font-mono">
                                </div>
                            </template>
                        </div>

                        <button type="submit" class="w-full text-xs py-2.5 rounded-lg bg-brand text-brand-text font-bold hover:bg-brand-deep transition">
                            Send Message to {{ $tenant->name }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Policy Log --}}
            @if($policyLog->isNotEmpty())
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-800"><h2 class="text-white text-sm font-semibold">Policy Log</h2></div>
                <div class="divide-y divide-gray-800/50">
                    @foreach($policyLog as $log)
                    <div class="px-5 py-3 flex items-start justify-between gap-3">
                        <div>
                            <span class="text-xs font-mono text-amber-400">{{ $log->policy_code ?? '—' }}</span>
                            @if(isset($log->notes) && $log->notes)<p class="text-gray-600 text-xs mt-0.5">{{ $log->notes }}</p>@endif
                        </div>
                        <span class="text-gray-700 text-xs shrink-0">{{ \Carbon\Carbon::parse($log->created_at)->format('M j') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<script>
const TEMPLATES = @json($messageTemplates);
const CTA_OPTIONS = @json($ctaOptions);
const AI_URL = "{{ route('admin.tenants.ai-message', $tenant->id) }}";
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function messagingPanel() {
    return {
        template: 'custom',
        subject:  '',
        body:     '',
        ctaKey:   'none',
        ctaLabel: '',
        ctaUrl:   '',
        aiGoal:   'feedback',
        aiLoading: false,
        aiError:   '',
        showLog:   false,

        init() {},

        applyTemplate() {
            const t = TEMPLATES[this.template];
            if (t) {
                this.subject = t.subject;
                this.body    = t.body;
                this.ctaKey  = t.cta ?? 'none';
                this.applyCta();
            }
        },

        applyCta() {
            const o = CTA_OPTIONS[this.ctaKey] ?? { label: '', url: '' };
            this.ctaLabel = o.label;
            this.ctaUrl   = o.url;
        },

        async generateAI() {
            this.aiLoading = true;
            this.aiError   = '';
            try {
                const r = await fetch(AI_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ goal: this.aiGoal })
                });
                const data = await r.json();
                if (data.subject) this.subject = data.subject;
                if (data.body)    this.body    = data.body;
                this.template = 'custom';
            } catch(e) {
                this.aiError = 'AI generation failed. Try again.';
            } finally {
                this.aiLoading = false;
            }
        }
    };
}
</script>
</x-app-layout>

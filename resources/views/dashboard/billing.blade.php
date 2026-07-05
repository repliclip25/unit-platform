<x-app-layout title="Billing">

    {{-- Platform-level policy violations --}}
    @if(!empty($policyViolations['platform']))
        <div class="mb-5">
            @include('partials.policy-violations', ['violations' => $policyViolations['platform']])
        </div>
    @endif

    {{-- Header row --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="font-bold text-lg" style="color:var(--text-primary)">Billing & Subscriptions</h1>
            <p class="text-xs mt-0.5" style="color:var(--text-muted)">Manage your worker subscriptions and view invoice history</p>
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

    {{-- Worker subscriptions --}}
    <div class="rounded-2xl mb-6" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4" style="border-bottom:1px solid var(--border)">
            <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Worker Subscriptions</h2>
        </div>

        @forelse($deployments as $dep)
            @php
                $bill       = $billingRecords[$dep->id] ?? null;
                $status     = $bill?->status ?? 'unknown';
                $depTiers   = $pricingTiers[$dep->worker_slug] ?? collect();

                // Trial state
                $trialUsed  = (int) ($bill?->trial_transactions_used  ?? 0);
                $trialLimit = (int) ($bill?->trial_transactions_limit ?? 25);
                $trialPct   = $trialLimit > 0 ? min(100, ($trialUsed / $trialLimit) * 100) : 0;
                $trialWarn  = $trialPct >= 70;
                $trialDaysLeft = null;
                if ($bill?->trial_ends_at) {
                    $trialDaysLeft = max(0, (int) now()->diffInDays(\Carbon\Carbon::parse($bill->trial_ends_at), false));
                }

                // Active subscription state
                $activeTier  = null;
                $unitUsed    = (int) ($bill?->unit_count ?? 0);
                $unitLimit   = null;
                $unitLabel   = $bill?->billing_unit ?? 'email';
                $renewalDate = null;
                if ($status === 'active' && $bill?->plan_slug) {
                    $activeTier  = $depTiers->firstWhere('plan_slug', $bill->plan_slug);
                    $unitLimit   = $activeTier?->transaction_limit ?? null;
                    if ($bill?->billing_period_start) {
                        $renewalDate = \Carbon\Carbon::parse($bill->billing_period_start)->addMonth()->startOfMonth();
                    }
                }
                $usagePct  = ($unitLimit && $unitLimit > 0) ? min(100, ($unitUsed / $unitLimit) * 100) : 0;
                $usageWarn = $unitLimit && $usagePct >= 80;

                $statusColor = match($status) {
                    'active'          => ['bg'=>'rgba(34,197,94,0.1)','border'=>'rgba(34,197,94,0.3)','text'=>'#4ade80','label'=>'Active'],
                    'trial'           => ['bg'=>'rgba(var(--accent-rgb),0.1)','border'=>'rgba(var(--accent-rgb),0.3)','text'=>'#fbbf24','label'=>'Trial'],
                    'trial_exhausted' => ['bg'=>'rgba(239,68,68,0.1)','border'=>'rgba(239,68,68,0.3)','text'=>'#f87171','label'=>'Trial Ended'],
                    'past_due'        => ['bg'=>'rgba(239,68,68,0.1)','border'=>'rgba(239,68,68,0.3)','text'=>'#f87171','label'=>'Past Due'],
                    'canceled'        => ['bg'=>'rgba(127,29,29,0.1)','border'=>'rgba(127,29,29,0.4)','text'=>'#fca5a5','label'=>'Canceled'],
                    default           => ['bg'=>'rgba(107,114,128,0.1)','border'=>'#374151','text'=>'#9ca3af','label'=>ucfirst($status)],
                };
            @endphp

            <div class="px-5 py-5 border-b border-gray-800/60 last:border-0">

                {{-- Worker row --}}
                <div class="flex items-start justify-between gap-4 flex-wrap">

                    {{-- Worker identity --}}
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                             style="background:rgba(var(--accent-rgb),0.15)">
                            <span class="font-bold text-sm" style="color:var(--accent-text)">{{ strtoupper(substr($dep->worker_slug,0,1)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium truncate" style="color:var(--text-primary)">{{ $dep->name }}</p>
                            <p class="text-xs" style="color:var(--text-faint)">{{ $dep->worker_slug }}</p>
                        </div>
                    </div>

                    {{-- Status + plan + action --}}
                    <div class="flex items-center gap-3 shrink-0 flex-wrap">

                        {{-- Status badge --}}
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium"
                              style="background:{{ $statusColor['bg'] }};border:1px solid {{ $statusColor['border'] }};color:{{ $statusColor['text'] }}">
                            {{ $statusColor['label'] }}
                        </span>

                        @if($status === 'active' && $activeTier)
                            {{-- Active: plan + renewal --}}
                            <span class="text-xs px-2.5 py-1 rounded-full"
                                  style="background:var(--bg-raised);color:var(--text-secondary);border:1px solid var(--border)">
                                {{ $activeTier->display_name }}
                                @if($activeTier->monthly_flat_rate > 0)
                                    · ${{ number_format($activeTier->monthly_flat_rate, 0) }}/mo
                                @endif
                            </span>
                            @if($renewalDate)
                            <span class="text-xs" style="color:var(--text-muted)">
                                Renews {{ $renewalDate->format('M j, Y') }}
                            </span>
                            @endif
                            <a href="{{ route('billing.portal') }}"
                               class="text-xs px-3 py-1.5 rounded-lg font-medium transition"
                               style="background:var(--bg-raised);color:var(--text-secondary);border:1px solid var(--border)">
                                Manage
                            </a>

                        @elseif($status === 'trial' || $status === 'trial_exhausted')
                            {{-- Trial: Choose Plan CTA --}}
                            <button onclick="togglePlans('plans-{{ $dep->id }}')"
                                    class="text-xs px-4 py-1.5 rounded-lg font-bold text-gray-900 hover:opacity-90 transition"
                                    style="background:var(--accent)">
                                Choose Plan ↓
                            </button>

                        @elseif($status === 'past_due')
                            <a href="{{ route('billing.portal') }}"
                               class="text-xs px-4 py-1.5 rounded-lg font-medium transition"
                               style="background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.3)">
                                Update Payment →
                            </a>

                        @elseif($status === 'canceled')
                            <button onclick="togglePlans('plans-{{ $dep->id }}')"
                                    class="text-xs px-4 py-1.5 rounded-lg font-medium transition"
                                    style="background:rgba(252,165,165,0.1);color:#fca5a5;border:1px solid rgba(127,29,29,0.4)">
                                Reactivate →
                            </button>
                        @endif
                    </div>

                </div>

                {{-- Worker-level policy violations --}}
                @if(!empty($policyViolations[$dep->id]))
                    <div class="mt-4 pl-12">
                        @include('partials.policy-violations', ['violations' => $policyViolations[$dep->id]])
                    </div>
                @endif

                {{-- Trial progress (only shown during active trial) --}}
                @if($status === 'trial')
                <div class="mt-4 pl-12 max-w-sm">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span style="color:{{ $trialWarn ? '#f59e0b' : 'var(--text-muted)' }}">
                            {{ $trialUsed }} / {{ $trialLimit }} emails used
                        </span>
                        <span style="color:var(--text-faint)">
                            {{ $trialLimit - $trialUsed }} remaining
                            @if(!is_null($trialDaysLeft))
                                · {{ $trialDaysLeft }}d left
                            @endif
                        </span>
                    </div>
                    <div class="h-1.5 rounded-full overflow-hidden" style="background:var(--bg-raised)">
                        <div class="h-full rounded-full transition-all duration-700"
                             style="width:{{ $trialPct }}%;background:{{ $trialWarn ? '#f59e0b' : 'var(--accent)' }}"></div>
                    </div>
                    @if($trialWarn && $trialUsed < $trialLimit)
                        <p class="text-xs mt-1.5" style="color:#f59e0b">
                            Running low — subscribe to keep AVA processing after your trial ends
                        </p>
                    @endif
                </div>
                @elseif($status === 'trial_exhausted')
                <div class="mt-3 pl-12">
                    <p class="text-xs" style="color:#f87171">
                        Your free trial has ended. Choose a plan below to continue processing emails.
                    </p>
                </div>
                @endif

                {{-- Active: overage warning (only when finite limit AND >80% used) --}}
                @if($status === 'active' && $unitLimit && $usageWarn)
                <div class="mt-4 pl-12 max-w-sm">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span style="color:{{ $usagePct >= 100 ? '#f87171' : '#f59e0b' }}">
                            {{ number_format($unitUsed) }} / {{ number_format($unitLimit) }} {{ $unitLabel }}s this period
                        </span>
                        <span style="color:var(--text-faint)">{{ number_format(100 - $usagePct, 0) }}% remaining</span>
                    </div>
                    <div class="h-1.5 rounded-full overflow-hidden" style="background:var(--bg-raised)">
                        <div class="h-full rounded-full transition-all duration-700"
                             style="width:{{ $usagePct }}%;background:{{ $usagePct >= 100 ? '#ef4444' : '#f59e0b' }}"></div>
                    </div>
                    @if($usagePct >= 100)
                        <p class="text-xs mt-1.5" style="color:#f87171">
                            Monthly limit reached — new emails are queued until your renewal date
                        </p>
                    @else
                        <p class="text-xs mt-1.5" style="color:#f59e0b">
                            Approaching your plan limit. Resets on {{ $renewalDate?->format('M j') ?? 'renewal' }}.
                        </p>
                    @endif
                </div>
                @endif

                {{-- Plan picker (trial, trial_exhausted, or canceled) --}}
                @if(in_array($status, ['trial', 'trial_exhausted', 'canceled']))
                <div id="plans-{{ $dep->id }}"
                     class="{{ ($status === 'trial_exhausted' || !empty($policyViolations[$dep->id])) ? '' : 'hidden' }} mt-5 border-t pt-5"
                     style="border-color:var(--border)">

                    @if($status === 'canceled')
                    <p class="text-xs mb-4" style="color:var(--text-muted)">Select a plan to reactivate your subscription:</p>
                    @else
                    <p class="text-xs mb-4" style="color:var(--text-muted)">Choose the plan that fits your volume:</p>
                    @endif

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
                                <a href="mailto:hello@unit.report?subject=AVA Enterprise Enquiry"
                                   style="display:block;text-align:center;font-size:12px;font-weight:600;padding:8px 12px;border-radius:8px;border:1px solid var(--border);color:var(--text-secondary);text-decoration:none">
                                    Contact Us
                                </a>
                            @elseif($status === 'canceled')
                                <form method="POST" action="{{ route('billing.reactivate', $dep->id) }}">
                                    @csrf
                                    <input type="hidden" name="plan" value="{{ $tier->plan_slug }}">
                                    <button type="submit"
                                            style="display:block;width:100%;text-align:center;font-size:12px;font-weight:700;padding:8px 12px;border-radius:8px;cursor:pointer;border:none;
                                                   {{ $isPro ? 'background:var(--accent);color:#111' : 'background:var(--bg-raised);border:1px solid var(--border);color:var(--text-secondary)' }}">
                                        Reactivate on {{ ucfirst($tier->plan_slug) }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('billing.checkout', $dep->id) }}?plan={{ $tier->plan_slug }}"
                                   style="display:block;text-align:center;font-size:12px;font-weight:700;padding:8px 12px;border-radius:8px;text-decoration:none;
                                          {{ $isPro ? 'background:var(--accent);color:#111' : 'border:1px solid var(--border);color:var(--text-secondary)' }}">
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
            <div class="px-5 py-8 text-center text-sm" style="color:var(--text-muted)">No workers deployed yet.</div>
        @endforelse
    </div>

    {{-- Invoices --}}
    @if($invoices->isNotEmpty())
    <div class="rounded-2xl" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4 flex items-center justify-between" style="border-bottom:1px solid var(--border)">
            <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Invoices</h2>
            @if(auth()->user()->isAdmin())
                <span class="text-xs px-2 py-0.5 rounded" style="color:#f59e0b;border:1px solid rgba(245,158,11,0.3);background:rgba(245,158,11,0.08)">
                    Admin: void clears an outstanding invoice in Stripe without charging
                </span>
            @endif
        </div>
        @foreach($invoices as $invoice)
        @php
            $isDue    = !$invoice->paid && ($invoice->asStripeInvoice()->status ?? '') !== 'void';
            $isVoided = ($invoice->asStripeInvoice()->status ?? '') === 'void';
        @endphp
        <div class="px-5 py-3 flex items-center justify-between gap-4" style="border-bottom:1px solid rgba(255,255,255,0.04)">
            <div>
                <p class="text-sm" style="color:var(--text-primary)">{{ $invoice->date()->format('F j, Y') }}</p>
                <p class="text-xs" style="color:var(--text-muted)">${{ number_format($invoice->rawTotal() / 100, 2) }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($isVoided)
                    <span class="text-xs px-2 py-0.5 rounded" style="background:var(--bg-raised);color:var(--text-faint)">Voided</span>
                @elseif($invoice->paid)
                    <span class="text-xs px-2 py-0.5 rounded" style="background:rgba(34,197,94,0.1);color:#4ade80;border:1px solid rgba(34,197,94,0.25)">Paid</span>
                @else
                    <span class="text-xs px-2 py-0.5 rounded" style="background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.25)">Due</span>
                @endif

                <a href="{{ route('billing.invoice', $invoice->id) }}"
                   class="text-xs" style="color:var(--text-muted);text-decoration:none">Download PDF →</a>

                @if(auth()->user()->isAdmin() && $isDue)
                <form method="POST" action="{{ route('admin.invoices.void', $invoice->id) }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    <button type="submit"
                            onclick="return confirm('Void invoice {{ $invoice->id }} for ${{ number_format($invoice->rawTotal() / 100, 2) }}?')"
                            class="text-xs px-2.5 py-1 rounded transition"
                            style="color:#f59e0b;border:1px solid rgba(245,158,11,0.3);background:transparent">
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

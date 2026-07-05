<x-app-layout title="Billing">

    {{-- Platform-level policy violations --}}
    @if(!empty($policyViolations['platform']))
        <div class="mb-5">
            @include('partials.policy-violations', ['violations' => $policyViolations['platform']])
        </div>
    @endif

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

    @forelse($deployments as $dep)
    @php
        $bill        = $billingRecords[$dep->id] ?? null;
        $status      = $bill?->status ?? 'unknown';
        $depTiers    = $pricingTiers[$dep->worker_slug] ?? collect();

        // Value metric
        $processed   = (int) ($emailsProcessed[$dep->id]->total ?? 0);
        $minPerEmail = 16; // avg minutes saved per processed email
        $hoursSaved  = round($processed * $minPerEmail / 60, 1);

        // Trial state
        $trialUsed   = (int) ($bill?->trial_transactions_used  ?? 0);
        $trialLimit  = (int) ($bill?->trial_transactions_limit ?? 25);
        $trialLeft   = max(0, $trialLimit - $trialUsed);
        $trialPct    = $trialLimit > 0 ? min(100, ($trialUsed / $trialLimit) * 100) : 0;
        $trialDaysLeft = null;
        if ($bill?->trial_ends_at) {
            $trialDaysLeft = max(0, (int) now()->diffInDays(\Carbon\Carbon::parse($bill->trial_ends_at), false));
        }
        $trialUrgent = $trialPct >= 60;

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

        $isTrialState     = in_array($status, ['trial', 'trial_exhausted']);
        $isCanceledState  = $status === 'canceled';
        $isActiveState    = $status === 'active';
        $isPastDue        = $status === 'past_due';
        $isExhausted      = $status === 'trial_exhausted';
    @endphp

    {{-- ═══════════════════════════════════════════════════════════════════
         TRIAL / EXHAUSTED — conversion page layout
    ═══════════════════════════════════════════════════════════════════ --}}
    @if($isTrialState || $isCanceledState)

    {{-- Value hero --}}
    <div class="mb-6 rounded-2xl px-6 py-8 text-center relative overflow-hidden"
         style="background:var(--bg-card);border:1px solid var(--border)">

        {{-- Subtle radial glow behind the number --}}
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
                    width:280px;height:280px;border-radius:50%;
                    background:radial-gradient(circle,rgba(var(--accent-rgb),0.10) 0%,transparent 70%);
                    pointer-events:none"></div>

        {{-- Worker identity --}}
        <div class="flex items-center justify-center gap-2 mb-6">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-gray-900"
                 style="background:var(--accent)">{{ strtoupper(substr($dep->worker_slug,0,1)) }}</div>
            <span class="text-sm font-semibold" style="color:var(--text-secondary)">{{ $dep->name }}</span>
            @if($isCanceledState)
            <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(127,29,29,0.2);color:#fca5a5;border:1px solid rgba(127,29,29,0.4)">Canceled</span>
            @elseif($isExhausted)
            <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.3)">Trial Ended</span>
            @else
            <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(var(--accent-rgb),0.12);color:#fbbf24;border:1px solid rgba(var(--accent-rgb),0.3)">Free Trial</span>
            @endif
        </div>

        @if($processed > 0)
        {{-- Value clock --}}
        <p class="text-xs uppercase tracking-widest mb-2" style="color:var(--text-muted)">Time returned to your team</p>
        <div class="flex items-baseline justify-center gap-2 mb-1">
            <span style="font-size:clamp(3rem,10vw,5rem);font-weight:700;line-height:1;color:var(--accent-text)">{{ $hoursSaved }}</span>
            <span class="text-xl font-medium" style="color:var(--text-secondary)">hours</span>
        </div>
        <p class="text-sm mb-6" style="color:var(--text-muted)">
            {{ number_format($processed) }} {{ $processed === 1 ? 'email' : 'emails' }} processed · {{ $dep->worker_slug }} worker
        </p>

        {{-- Conversion hook --}}
        @if($isExhausted)
        <p class="text-base font-semibold mb-1" style="color:var(--text-primary)">Your trial is over. AVA is on pause.</p>
        <p class="text-sm mb-6 max-w-sm mx-auto" style="color:var(--text-muted)">
            Subscribe to keep those hours in your week — every renewal email will keep getting read, classified, and drafted automatically.
        </p>
        @elseif($isCanceledState)
        <p class="text-base font-semibold mb-1" style="color:var(--text-primary)">AVA is off. Your inbox is back on you.</p>
        <p class="text-sm mb-6 max-w-sm mx-auto" style="color:var(--text-muted)">
            Reactivate to put those hours back on the clock.
        </p>
        @else
        <p class="text-base font-semibold mb-1" style="color:var(--text-primary)">That time is yours — keep it.</p>
        <p class="text-sm mb-6 max-w-sm mx-auto" style="color:var(--text-muted)">
            AVA processes every renewal email in the background. Subscribe and it never stops working for you.
        </p>
        @endif

        @else
        {{-- No emails yet — still sell the promise --}}
        <p class="text-xs uppercase tracking-widest mb-4" style="color:var(--text-muted)">Your free trial is active</p>
        <p class="text-2xl font-bold mb-2" style="color:var(--text-primary)">AVA is ready to work.</p>
        <p class="text-sm mb-6 max-w-xs mx-auto" style="color:var(--text-muted)">
            Connect your inbox and run AVA on a few renewals. Then subscribe to keep the hours coming back to you.
        </p>
        @endif

        {{-- Trial meter (only during active trial, not exhausted/canceled) --}}
        @if($status === 'trial')
        <div class="max-w-xs mx-auto mb-6">
            <div class="flex items-center justify-between text-xs mb-2">
                <span style="color:{{ $trialUrgent ? '#f59e0b' : 'var(--text-muted)' }}">
                    {{ $trialUsed }} of {{ $trialLimit }} free emails used
                </span>
                <span style="color:{{ $trialUrgent ? '#f59e0b' : 'var(--text-faint)' }}">
                    {{ $trialLeft }} left
                    @if(!is_null($trialDaysLeft)) · {{ $trialDaysLeft }}d @endif
                </span>
            </div>
            <div class="h-2 rounded-full overflow-hidden" style="background:var(--bg-raised)">
                <div class="h-full rounded-full transition-all duration-700"
                     style="width:{{ $trialPct }}%;background:{{ $trialUrgent ? 'linear-gradient(90deg,#f59e0b,#ef4444)' : 'var(--accent)' }}"></div>
            </div>
            @if($trialLeft <= 5 && $trialLeft > 0)
            <p class="text-xs mt-2" style="color:#f59e0b">
                Only {{ $trialLeft }} free email{{ $trialLeft === 1 ? '' : 's' }} left. Subscribe now to keep processing.
            </p>
            @elseif($trialLeft === 0)
            <p class="text-xs mt-2" style="color:#f87171">
                Trial exhausted. New emails are paused until you subscribe.
            </p>
            @endif
        </div>
        @endif

        {{-- Worker policy violations --}}
        @if(!empty($policyViolations[$dep->id]))
        <div class="mb-6 text-left max-w-sm mx-auto">
            @include('partials.policy-violations', ['violations' => $policyViolations[$dep->id]])
        </div>
        @endif

    </div>

    {{-- Plan cards — always visible, no toggle --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold" style="color:var(--text-primary)">
                @if($isCanceledState) Choose a plan to reactivate
                @else Pick a plan — your trial emails carry over
                @endif
            </h2>
            <span class="text-xs" style="color:var(--text-muted)">No lock-in · cancel anytime</span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px">
            @foreach($depTiers as $tier)
            @php
                $isEnterprise = $tier->plan_slug === 'enterprise';
                $isPro        = $tier->plan_slug === 'pro';
                $highlights   = json_decode($tier->plan_highlights ?? '[]', true);
            @endphp
            <div style="
                background:var(--bg-card);
                border:{{ $isPro ? '2px solid rgba(var(--accent-rgb),0.6)' : '1px solid var(--border)' }};
                border-radius:16px;padding:20px;
                display:flex;flex-direction:column;gap:14px;position:relative;
            ">
                @if($isPro)
                <span style="
                    position:absolute;top:-11px;left:16px;
                    font-size:10px;padding:2px 10px;border-radius:99px;
                    font-weight:700;letter-spacing:.04em;color:#111;background:var(--accent)
                ">RECOMMENDED</span>
                @endif

                <div>
                    <p style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $tier->display_name }}</p>
                    <p style="font-size:28px;font-weight:800;color:var(--text-primary);margin-top:6px;line-height:1">
                        @if($isEnterprise)
                            Custom
                        @else
                            ${{ number_format($tier->monthly_flat_rate, 0) }}<span style="font-size:13px;font-weight:400;color:var(--text-muted)">/month</span>
                        @endif
                    </p>
                    <p style="font-size:11px;color:var(--text-muted);margin-top:4px">
                        @if($tier->transaction_limit) {{ number_format($tier->transaction_limit) }} emails/month
                        @else Unlimited emails
                        @endif
                    </p>
                </div>

                <ul style="flex:1;display:flex;flex-direction:column;gap:8px;list-style:none;padding:0;margin:0">
                    @foreach($highlights as $h)
                    <li style="display:flex;align-items:flex-start;gap:7px;font-size:12px;color:var(--text-secondary)">
                        <span style="color:#4ade80;flex-shrink:0;margin-top:1px;font-size:11px">✓</span> {{ $h }}
                    </li>
                    @endforeach
                </ul>

                @if($isEnterprise)
                    <a href="mailto:hello@unit.report?subject=AVA Enterprise Enquiry&body=Hi, I'm interested in AVA Enterprise for my organisation."
                       style="display:block;text-align:center;font-size:13px;font-weight:600;padding:10px 16px;border-radius:10px;border:1px solid var(--border);color:var(--text-secondary);text-decoration:none;transition:opacity .15s"
                       onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                        Contact Us →
                    </a>
                @elseif($isCanceledState)
                    <form method="POST" action="{{ route('billing.reactivate', $dep->id) }}">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $tier->plan_slug }}">
                        <button type="submit"
                                style="display:block;width:100%;text-align:center;font-size:13px;font-weight:700;padding:10px 16px;border-radius:10px;cursor:pointer;border:none;transition:opacity .15s;
                                       {{ $isPro ? 'background:var(--accent);color:#111' : 'background:var(--bg-raised);border:1px solid var(--border);color:var(--text-secondary)' }}"
                                onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                            Reactivate on {{ ucfirst($tier->plan_slug) }} →
                        </button>
                    </form>
                @else
                    <a href="{{ route('billing.checkout', $dep->id) }}?plan={{ $tier->plan_slug }}"
                       style="display:block;text-align:center;font-size:13px;font-weight:700;padding:10px 16px;border-radius:10px;text-decoration:none;transition:opacity .15s;
                              {{ $isPro ? 'background:var(--accent);color:#111' : 'background:var(--bg-raised);border:1px solid var(--border);color:var(--text-secondary)' }}"
                       onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        Get {{ ucfirst($tier->plan_slug) }} →
                    </a>
                @endif
            </div>
            @endforeach
        </div>

        <p class="text-center text-xs mt-4" style="color:var(--text-faint)">
            Billed monthly via Stripe · cancel anytime from your billing portal · no hidden fees
        </p>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         ACTIVE — subscription confirmed view
    ═══════════════════════════════════════════════════════════════════ --}}
    @elseif($isActiveState)

    <div class="rounded-2xl mb-5 overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">

        {{-- Header --}}
        <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
             style="border-bottom:1px solid var(--border)">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                     style="background:rgba(var(--accent-rgb),0.15)">
                    <span class="font-bold text-sm" style="color:var(--accent-text)">{{ strtoupper(substr($dep->worker_slug,0,1)) }}</span>
                </div>
                <div>
                    <p class="text-sm font-semibold" style="color:var(--text-primary)">{{ $dep->name }}</p>
                    @if($activeTier)
                    <p class="text-xs" style="color:var(--text-muted)">
                        {{ $activeTier->display_name }}
                        @if($activeTier->monthly_flat_rate > 0) · ${{ number_format($activeTier->monthly_flat_rate, 0) }}/mo @endif
                        @if($renewalDate) · renews {{ $renewalDate->format('M j, Y') }} @endif
                    </p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <span class="text-xs px-2.5 py-1 rounded-full font-medium"
                      style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#4ade80">Active</span>
                <a href="{{ route('billing.portal') }}"
                   class="text-xs px-3 py-1.5 rounded-lg font-medium transition"
                   style="background:var(--bg-raised);color:var(--text-secondary);border:1px solid var(--border)">
                    Manage Payment →
                </a>
            </div>
        </div>

        {{-- Value + usage row --}}
        <div class="px-5 py-5 flex flex-col sm:flex-row items-start sm:items-center gap-5">

            {{-- Value clock --}}
            @if($processed > 0)
            <div class="flex items-baseline gap-2 shrink-0">
                <span style="font-size:2.5rem;font-weight:800;line-height:1;color:var(--accent-text)">{{ $hoursSaved }}</span>
                <div>
                    <p class="text-sm font-medium" style="color:var(--text-secondary)">hours returned</p>
                    <p class="text-xs" style="color:var(--text-muted)">{{ number_format($processed) }} emails processed</p>
                </div>
            </div>
            <div class="hidden sm:block w-px self-stretch" style="background:var(--border)"></div>
            @endif

            {{-- Usage (only if finite limit) --}}
            @if($unitLimit)
            <div class="flex-1 max-w-xs">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span style="color:{{ $usageWarn ? '#f59e0b' : 'var(--text-muted)' }}">
                        {{ number_format($unitUsed) }} / {{ number_format($unitLimit) }} {{ $unitLabel }}s this period
                    </span>
                    @if($renewalDate)
                    <span style="color:var(--text-faint)">resets {{ $renewalDate->format('M j') }}</span>
                    @endif
                </div>
                <div class="h-1.5 rounded-full overflow-hidden" style="background:var(--bg-raised)">
                    <div class="h-full rounded-full transition-all"
                         style="width:{{ $usagePct }}%;background:{{ $usagePct >= 100 ? '#ef4444' : ($usageWarn ? '#f59e0b' : 'var(--accent)') }}"></div>
                </div>
                @if($usagePct >= 100)
                <p class="text-xs mt-1.5" style="color:#f87171">Limit reached — emails queue until renewal</p>
                @elseif($usageWarn)
                <p class="text-xs mt-1.5" style="color:#f59e0b">Approaching your plan limit</p>
                @endif
            </div>
            @elseif($processed > 0)
            <div>
                <p class="text-sm" style="color:var(--text-secondary)">Unlimited plan · AVA keeps working</p>
                <p class="text-xs" style="color:var(--text-muted)">{{ number_format($unitUsed) }} emails this billing period</p>
            </div>
            @endif

        </div>

        {{-- Worker policy violations --}}
        @if(!empty($policyViolations[$dep->id]))
        <div class="px-5 pb-5">
            @include('partials.policy-violations', ['violations' => $policyViolations[$dep->id]])
        </div>
        @endif

    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         PAST DUE
    ═══════════════════════════════════════════════════════════════════ --}}
    @elseif($isPastDue)

    <div class="rounded-2xl mb-5 px-6 py-6 text-center" style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.3)">
        <div class="flex items-center justify-center gap-2 mb-3">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold"
                 style="background:rgba(239,68,68,0.2);color:#f87171">{{ strtoupper(substr($dep->worker_slug,0,1)) }}</div>
            <span class="text-sm font-medium" style="color:var(--text-secondary)">{{ $dep->name }}</span>
            <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.3)">Past Due</span>
        </div>
        <p class="text-base font-semibold mb-1" style="color:var(--text-primary)">Payment failed. AVA is paused.</p>
        <p class="text-sm mb-5" style="color:var(--text-muted)">Update your payment method to resume processing.</p>
        <a href="{{ route('billing.portal') }}"
           class="inline-block text-sm font-bold px-6 py-3 rounded-xl transition"
           style="background:#ef4444;color:#fff">
            Update Payment Method →
        </a>
    </div>

    @endif

    @empty
        <div class="rounded-2xl px-5 py-8 text-center text-sm" style="color:var(--text-muted);background:var(--bg-card);border:1px solid var(--border)">
            No workers deployed yet.
        </div>
    @endforelse

    {{-- Invoices --}}
    @if($invoices->isNotEmpty())
    <div class="rounded-2xl mt-2" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4 flex items-center justify-between" style="border-bottom:1px solid var(--border)">
            <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Invoice History</h2>
            @if(auth()->user()->isAdmin())
                <span class="text-xs px-2 py-0.5 rounded" style="color:#f59e0b;border:1px solid rgba(245,158,11,0.3);background:rgba(245,158,11,0.06)">
                    Admin: void clears outstanding invoice in Stripe
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

{{-- Auto-scroll to plans if ?pick= in URL --}}
<script>
(function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('pick')) {
        const el = document.querySelector('[data-plans]');
        if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'center' }), 150);
    }
})();
</script>
</x-app-layout>

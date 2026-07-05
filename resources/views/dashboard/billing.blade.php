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

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="font-bold text-lg" style="color:var(--text-primary)">Billing & Subscriptions</h1>
            <p class="text-xs mt-0.5" style="color:var(--text-muted)">Each worker subscription bills to one monthly invoice</p>
        </div>
        <a href="{{ route('billing.portal') }}"
           class="text-sm px-4 py-2 rounded-lg shrink-0 transition"
           style="background:var(--bg-raised);color:var(--text-secondary);border:1px solid var(--border)">
            Manage Payment →
        </a>
    </div>

    {{-- ─────────────────────────────────────────────────────────────────────
         One card per worker deployment
    ───────────────────────────────────────────────────────────────────── --}}
    <div class="space-y-5 mb-6">

    @forelse($deployments as $dep)
    @php
        $bill        = $billingRecords[$dep->id] ?? null;
        $status      = $bill?->status ?? 'unknown';
        $depTiers    = $pricingTiers[$dep->worker_slug] ?? collect();

        // Value metric — worker-specific
        $processed   = (int) ($emailsProcessed[$dep->id]->total ?? 0);
        $minPerEmail = 16;
        $hoursSaved  = $processed > 0 ? round($processed * $minPerEmail / 60, 1) : null;

        // Trial
        $trialUsed   = (int) ($bill?->trial_transactions_used  ?? 0);
        $trialLimit  = (int) ($bill?->trial_transactions_limit ?? 25);
        $trialLeft   = max(0, $trialLimit - $trialUsed);
        $trialPct    = $trialLimit > 0 ? min(100, ($trialUsed / $trialLimit) * 100) : 0;
        $trialDaysLeft = null;
        if ($bill?->trial_ends_at) {
            $trialDaysLeft = max(0, (int) now()->diffInDays(\Carbon\Carbon::parse($bill->trial_ends_at), false));
        }

        // Active plan
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

        $isTrialState    = in_array($status, ['trial', 'trial_exhausted']);
        $isCanceled      = $status === 'canceled';
        $isActive        = $status === 'active';
        $isPastDue       = $status === 'past_due';
        $isExhausted     = $status === 'trial_exhausted';
        $needsConversion = $isTrialState || $isCanceled;

        $statusColor = match($status) {
            'active'          => ['bg'=>'rgba(34,197,94,0.1)','border'=>'rgba(34,197,94,0.3)','text'=>'#4ade80','label'=>'Active'],
            'trial'           => ['bg'=>'rgba(var(--accent-rgb),0.1)','border'=>'rgba(var(--accent-rgb),0.3)','text'=>'#fbbf24','label'=>'Free Trial'],
            'trial_exhausted' => ['bg'=>'rgba(239,68,68,0.1)','border'=>'rgba(239,68,68,0.3)','text'=>'#f87171','label'=>'Trial Ended'],
            'past_due'        => ['bg'=>'rgba(239,68,68,0.1)','border'=>'rgba(239,68,68,0.3)','text'=>'#f87171','label'=>'Past Due'],
            'canceled'        => ['bg'=>'rgba(127,29,29,0.1)','border'=>'rgba(127,29,29,0.4)','text'=>'#fca5a5','label'=>'Canceled'],
            default           => ['bg'=>'rgba(107,114,128,0.1)','border'=>'#374151','text'=>'#9ca3af','label'=>ucfirst($status)],
        };
    @endphp

    <div class="rounded-2xl overflow-hidden"
         style="background:var(--bg-card);border:1px solid {{ $needsConversion && $processed > 0 ? 'rgba(var(--accent-rgb),0.25)' : 'var(--border)' }}">

        {{-- ── Card header: worker identity + status + value clock ── --}}
        <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
             style="border-bottom:1px solid var(--border)">

            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                     style="background:rgba(var(--accent-rgb),0.15)">
                    <span class="font-bold text-sm" style="color:var(--accent-text)">{{ strtoupper(substr($dep->worker_slug,0,1)) }}</span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold" style="color:var(--text-primary)">{{ $dep->name }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                              style="background:{{ $statusColor['bg'] }};border:1px solid {{ $statusColor['border'] }};color:{{ $statusColor['text'] }}">
                            {{ $statusColor['label'] }}
                        </span>
                        @if($isActive && $activeTier)
                            <span class="text-xs px-2 py-0.5 rounded-full"
                                  style="background:var(--bg-raised);color:var(--text-secondary);border:1px solid var(--border)">
                                {{ $activeTier->display_name }}
                                @if($activeTier->monthly_flat_rate > 0)· ${{ number_format($activeTier->monthly_flat_rate, 0) }}/mo@endif
                            </span>
                        @endif
                    </div>
                    <p class="text-xs mt-0.5" style="color:var(--text-faint)">
                        {{ $dep->worker_slug }}
                        @if($isActive && $renewalDate) · renews {{ $renewalDate->format('M j, Y') }} @endif
                        @if($isPastDue) · <span style="color:#f87171">payment failed</span> @endif
                    </p>
                </div>
            </div>

            {{-- Right: value clock + transactions link --}}
            <div class="flex items-center gap-4 shrink-0">
                @if($hoursSaved !== null)
                <div class="text-right">
                    <div class="flex items-baseline gap-1 justify-end">
                        <span style="font-size:1.75rem;font-weight:800;line-height:1;color:var(--accent-text)">{{ $hoursSaved }}</span>
                        <span class="text-xs font-medium" style="color:var(--text-muted)">hrs saved</span>
                    </div>
                    <a href="{{ route('transactions') }}?deployment={{ $dep->id }}"
                       class="text-xs block mt-0.5 hover:underline"
                       style="color:var(--text-faint)">
                        {{ number_format($processed) }} emails → view transactions
                    </a>
                </div>
                @else
                <a href="{{ route('transactions') }}?deployment={{ $dep->id }}"
                   class="text-xs" style="color:var(--text-faint)">View transactions →</a>
                @endif
            </div>

        </div>

        {{-- ── Body: status-specific content ── --}}
        <div class="px-5 py-5">

            {{-- Worker policy violations --}}
            @if(!empty($policyViolations[$dep->id]))
            <div class="mb-4">
                @include('partials.policy-violations', ['violations' => $policyViolations[$dep->id]])
            </div>
            @endif

            @if($status === 'trial')
            {{-- Trial: progress + urgency + CTA --}}
            <div class="mb-5">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span style="color:{{ $trialPct >= 60 ? '#f59e0b' : 'var(--text-muted)' }}">
                        {{ $trialUsed }} / {{ $trialLimit }} free emails used
                    </span>
                    <span style="color:var(--text-faint)">
                        {{ $trialLeft }} remaining
                        @if(!is_null($trialDaysLeft)) · {{ $trialDaysLeft }}d left @endif
                    </span>
                </div>
                <div class="h-2 rounded-full overflow-hidden" style="background:var(--bg-raised)">
                    <div class="h-full rounded-full transition-all duration-700"
                         style="width:{{ $trialPct }}%;background:{{ $trialPct >= 60 ? 'linear-gradient(90deg,#f59e0b,#ef4444)' : 'var(--accent)' }}"></div>
                </div>
                @if($trialLeft <= 5 && $trialLeft > 0)
                <p class="text-xs mt-1.5" style="color:#f59e0b">
                    Only {{ $trialLeft }} free email{{ $trialLeft === 1 ? '' : 's' }} left — subscribe to keep processing
                </p>
                @endif
            </div>

            @elseif($isExhausted)
            <div class="mb-5 px-4 py-3 rounded-xl" style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2)">
                <p class="text-sm font-semibold mb-0.5" style="color:#f87171">Your trial has ended. AVA is paused.</p>
                <p class="text-xs" style="color:var(--text-muted)">Subscribe to resume automatic email processing.</p>
            </div>

            @elseif($isPastDue)
            <div class="mb-5 px-4 py-3 rounded-xl" style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2)">
                <p class="text-sm font-semibold mb-0.5" style="color:#f87171">Payment failed — worker is paused</p>
                <p class="text-xs mb-3" style="color:var(--text-muted)">Update your payment method to resume.</p>
                <a href="{{ route('billing.portal') }}"
                   class="inline-block text-xs font-bold px-4 py-2 rounded-lg"
                   style="background:#ef4444;color:#fff">Update Payment Method →</a>
            </div>

            @elseif($isCanceled)
            <div class="mb-5 px-4 py-3 rounded-xl" style="background:rgba(127,29,29,0.08);border:1px solid rgba(127,29,29,0.3)">
                <p class="text-sm font-semibold mb-0.5" style="color:#fca5a5">Subscription canceled</p>
                <p class="text-xs" style="color:var(--text-muted)">
                    @if($hoursSaved !== null)
                        AVA returned {{ $hoursSaved }} hours to your team. Reactivate to keep that going.
                    @else
                        Reactivate to resume automatic email processing.
                    @endif
                </p>
            </div>

            @elseif($isActive)
            {{-- Active: email usage (only if finite limit and worth showing) --}}
            @if($unitLimit && $usageWarn)
            <div class="mb-4 max-w-sm">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span style="color:{{ $usagePct >= 100 ? '#f87171' : '#f59e0b' }}">
                        {{ number_format($unitUsed) }} / {{ number_format($unitLimit) }} {{ $unitLabel }}s this period
                    </span>
                    @if($renewalDate)
                    <span style="color:var(--text-faint)">resets {{ $renewalDate->format('M j') }}</span>
                    @endif
                </div>
                <div class="h-1.5 rounded-full overflow-hidden" style="background:var(--bg-raised)">
                    <div class="h-full rounded-full transition-all"
                         style="width:{{ $usagePct }}%;background:{{ $usagePct >= 100 ? '#ef4444' : '#f59e0b' }}"></div>
                </div>
                @if($usagePct >= 100)
                <p class="text-xs mt-1.5" style="color:#f87171">Monthly limit reached — emails queue until renewal</p>
                @else
                <p class="text-xs mt-1.5" style="color:#f59e0b">Approaching your plan limit · renews {{ $renewalDate?->format('M j') }}</p>
                @endif
            </div>
            @else
            <p class="text-sm" style="color:var(--text-muted)">
                @if($unitLimit)
                    {{ number_format($unitUsed) }} / {{ number_format($unitLimit) }} {{ $unitLabel }}s used this period
                @else
                    {{ number_format($unitUsed) }} {{ $unitLabel }}s processed this period · unlimited plan
                @endif
            </p>
            @endif
            <div class="mt-3">
                <a href="{{ route('billing.portal') }}"
                   class="inline-block text-xs px-3 py-1.5 rounded-lg font-medium transition"
                   style="background:var(--bg-raised);color:var(--text-secondary);border:1px solid var(--border)">
                    Manage Subscription →
                </a>
            </div>
            @endif

            {{-- Plan picker — trial, exhausted, or canceled --}}
            @if($needsConversion && $depTiers->isNotEmpty())
            <div class="{{ ($isExhausted || $isCanceled) ? '' : 'mt-2' }}">
                @if($status === 'trial')
                <p class="text-xs mb-3" style="color:var(--text-muted)">
                    Choose a plan — your trial emails carry over, no charge until you subscribe:
                </p>
                @elseif($isCanceled)
                <p class="text-xs mb-3" style="color:var(--text-muted)">Select a plan to reactivate:</p>
                @else
                <p class="text-xs mb-3" style="color:var(--text-muted)">Pick a plan to resume:</p>
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
                        border:{{ $isPro ? '2px solid rgba(var(--accent-rgb),0.55)' : '1px solid var(--border)' }};
                        border-radius:14px;padding:18px;
                        display:flex;flex-direction:column;gap:12px;position:relative;
                    ">
                        @if($isPro)
                        <span style="position:absolute;top:-10px;left:14px;font-size:10px;padding:2px 10px;border-radius:99px;font-weight:700;letter-spacing:.04em;color:#111;background:var(--accent)">RECOMMENDED</span>
                        @endif

                        <div>
                            <p style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $tier->display_name }}</p>
                            <p style="font-size:26px;font-weight:800;color:var(--text-primary);margin-top:4px;line-height:1">
                                @if($isEnterprise) Custom
                                @else ${{ number_format($tier->monthly_flat_rate, 0) }}<span style="font-size:12px;font-weight:400;color:var(--text-muted)">/mo</span>
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
                                <span style="color:#4ade80;flex-shrink:0;margin-top:1px;font-size:10px">✓</span> {{ $h }}
                            </li>
                            @endforeach
                        </ul>

                        @if($isEnterprise)
                            <a href="mailto:hello@unit.report?subject=AVA Enterprise Enquiry"
                               style="display:block;text-align:center;font-size:12px;font-weight:600;padding:9px 12px;border-radius:9px;border:1px solid var(--border);color:var(--text-secondary);text-decoration:none">
                                Contact Us →
                            </a>
                        @elseif($isCanceled)
                            <form method="POST" action="{{ route('billing.reactivate', $dep->id) }}">
                                @csrf
                                <input type="hidden" name="plan" value="{{ $tier->plan_slug }}">
                                <button type="submit"
                                        style="display:block;width:100%;text-align:center;font-size:12px;font-weight:700;padding:9px 12px;border-radius:9px;cursor:pointer;border:none;
                                               {{ $isPro ? 'background:var(--accent);color:#111' : 'background:transparent;border:1px solid var(--border);color:var(--text-secondary)' }}">
                                    Reactivate {{ ucfirst($tier->plan_slug) }} →
                                </button>
                            </form>
                        @else
                            <a href="{{ route('billing.checkout', $dep->id) }}?plan={{ $tier->plan_slug }}"
                               style="display:block;text-align:center;font-size:12px;font-weight:700;padding:9px 12px;border-radius:9px;text-decoration:none;
                                      {{ $isPro ? 'background:var(--accent);color:#111' : 'border:1px solid var(--border);color:var(--text-secondary)' }}">
                                Get {{ ucfirst($tier->plan_slug) }} →
                            </a>
                        @endif
                    </div>
                    @endforeach
                </div>

                <p class="text-xs mt-3" style="color:var(--text-faint)">
                    All plans billed monthly · cancel anytime · one Stripe invoice for all your workers
                </p>
            </div>
            @endif

        </div>{{-- end card body --}}

    </div>{{-- end worker card --}}
    @empty
    <div class="rounded-2xl px-5 py-10 text-center" style="background:var(--bg-card);border:1px solid var(--border)">
        <p class="text-sm" style="color:var(--text-muted)">No workers deployed yet.</p>
    </div>
    @endforelse

    </div>{{-- end space-y-5 --}}

    {{-- ─────────────────────────────────────────────────────────────────────
         Invoice history — shared across all worker subscriptions
    ───────────────────────────────────────────────────────────────────── --}}
    @if($invoices->isNotEmpty())
    <div class="rounded-2xl" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2"
             style="border-bottom:1px solid var(--border)">
            <div>
                <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Invoice History</h2>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">All worker subscriptions appear on one monthly invoice</p>
            </div>
            @if(auth()->user()->isAdmin())
                <span class="text-xs px-2 py-0.5 rounded shrink-0" style="color:#f59e0b;border:1px solid rgba(245,158,11,0.3);background:rgba(245,158,11,0.06)">
                    Admin: void clears invoice in Stripe
                </span>
            @endif
        </div>

        @foreach($invoices as $invoice)
        @php
            $isDue    = !$invoice->paid && ($invoice->asStripeInvoice()->status ?? '') !== 'void';
            $isVoided = ($invoice->asStripeInvoice()->status ?? '') === 'void';
        @endphp
        <div class="px-5 py-3 flex items-center justify-between gap-4"
             style="border-bottom:1px solid rgba(255,255,255,0.04)">
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

{{-- Auto-scroll to specific deployment if ?pick= in URL --}}
<script>
(function() {
    const params = new URLSearchParams(window.location.search);
    const pickId = params.get('pick');
    if (pickId) {
        const el = document.getElementById('dep-' + pickId);
        if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 150);
    }
})();
</script>
</x-app-layout>

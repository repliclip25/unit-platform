@extends('layouts.public')
@section('title', 'Pricing')
@section('description', 'Each UNIT AI agent is priced for the specific workflow it owns. Start free on any AI Worker, no card required.')

@section('body')

<style>
.pc-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:20px;
    margin:44px 0 68px;
    align-items:stretch;
}

/* ── BASE CARD ──
   One consistent neutral card style (white/black border, matching the
   platform's default chrome), the worker plan is the actual product
   being sold so it gets the featured dark-inverted treatment (same
   pattern as .sec-dark/.cta-final elsewhere), not a distinct color. */
.pc-card {
    border-radius:20px;
    display:flex;
    flex-direction:column;
    position:relative;
    overflow:hidden;
    background:var(--card);
    border:1px solid var(--line);
    box-shadow:0 2px 16px rgba(0,0,0,.06);
    transition:transform .2s,box-shadow .2s;
}
.pc-card:hover { transform:translateY(-2px);box-shadow:0 6px 32px rgba(0,0,0,.1) }

.pc-card-worker { background:#0D0D0D;border-color:#0D0D0D;color:#fff }
[data-theme="dark"] .pc-card-worker { background:#161616;border-color:#2D2D2D }
.pc-card-worker .pc-name,
.pc-card-worker .pc-price,
.pc-card-worker .pc-price-unit { color:#fff }
.pc-card-worker .pc-tagline,
.pc-card-worker .pc-price-sub { color:rgba(255,255,255,.55) }
.pc-card-worker .pc-features li { color:rgba(255,255,255,.75) }
.pc-card-worker .pc-tx-def { background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.1);color:rgba(255,255,255,.6) }
.pc-card-worker .pc-tx-def strong { color:#fff }

.pc-glow-stripe { height:3px;width:100%;position:absolute;top:0;left:0;background:var(--gold) }
.pc-inner { padding:24px;display:flex;flex-direction:column;flex:1 }

/* Tier badge - neutral everywhere, gold dot marks the featured plan */
.pc-tier {
    display:inline-flex;align-items:center;gap:5px;
    font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;
    padding:4px 10px;border-radius:99px;margin-bottom:16px;width:fit-content;
    background:rgba(0,0,0,.05);color:var(--t2);border:1px solid var(--line);
}
[data-theme="dark"] .pc-tier { background:rgba(255,255,255,.06) }
.pc-card-worker .pc-tier { background:rgba(255,255,255,.1);color:#fff;border-color:rgba(255,255,255,.15) }

.pc-name    { font-size:18px;font-weight:800;color:var(--text);font-family:var(--fd);margin-bottom:5px;line-height:1.2 }
.pc-tagline { font-size:13px;color:var(--t3);line-height:1.6;margin-bottom:18px }

.pc-price-row { display:flex;align-items:baseline;gap:4px;margin-bottom:3px }
.pc-price     { font-size:40px;font-weight:800;color:var(--text);line-height:1;font-family:var(--fd) }
.pc-price-unit{ font-size:13px;color:var(--t3) }
.pc-price-sub { font-size:12px;color:var(--t3);margin-bottom:16px }

.pc-tx-def {
    font-size:12px;color:var(--t3);
    padding:9px 12px;border-radius:9px;line-height:1.55;margin-bottom:18px;
    background:rgba(0,0,0,.03);border:1px solid var(--line);
}
[data-theme="dark"] .pc-tx-def { background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.08) }
.pc-tx-def strong { color:var(--text) }
.pc-card-worker .pc-tx-def strong { color:#fff }

.pc-features { list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px }
.pc-features li { font-size:13px;color:var(--t2);display:flex;align-items:flex-start;gap:8px;line-height:1.5 }
.pc-check { width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;background:rgba(0,0,0,.06) }
[data-theme="dark"] .pc-check { background:rgba(255,255,255,.08) }
.pc-card-worker .pc-check { background:rgba(255,255,255,.12) }

/* Buttons - always black/white, matching the platform's CTA convention */
.pc-cta { margin-top:auto;padding-top:20px }
.pc-btn {
    display:block;width:100%;text-align:center;
    padding:12px 0;border-radius:10px;
    font-size:13px;font-weight:700;text-decoration:none;
    background:#0D0D0D;color:#fff;
    transition:opacity .15s,transform .15s;
}
.pc-btn:hover { opacity:.88;transform:translateY(-1px) }
.pc-card-worker .pc-btn { background:#fff;color:#0D0D0D }
.pc-btn-ghost { background:transparent;color:var(--text);border:1px solid var(--line2) }
.pc-worker-link {
    display:block;text-align:center;margin-top:10px;
    font-size:12px;color:var(--t3);text-decoration:none;
    transition:color .15s;
}
.pc-worker-link:hover { color:var(--t2) }
.pc-card-worker .pc-worker-link { color:rgba(255,255,255,.45) }
.pc-card-worker .pc-worker-link:hover { color:rgba(255,255,255,.7) }

/* Note banner - neutral, not tinted */
.pc-note {
    border-radius:12px;padding:14px 18px;margin-bottom:36px;
    display:flex;gap:11px;align-items:flex-start;
    background:rgba(0,0,0,.03);border:1px solid var(--line);
}
[data-theme="dark"] .pc-note { background:rgba(255,255,255,.04) }
.pc-note p { font-size:13px;color:var(--t2);line-height:1.6;margin:0 }

/* FAQ */
.faq-wrap { max-width:640px;margin:0 auto }
.faq-item { border-bottom:1px solid var(--line);padding:16px 0 }
.faq-item:first-of-type { border-top:1px solid var(--line) }
.faq-item summary { font-size:14px;font-weight:600;color:var(--text);cursor:pointer;list-style:none;display:flex;align-items:center;justify-content:space-between;gap:12px }
.faq-item p { font-size:13px;color:var(--t3);line-height:1.75;margin:10px 0 0;padding-right:20px }
[data-theme="light"] .faq-item p { color:#555 }
.faq-chevron { width:15px;height:15px;color:var(--t4);flex-shrink:0;transition:transform .2s }

@media(max-width:680px){ .pc-grid { grid-template-columns:1fr } }
</style>

<div class="w pub-hero" style="text-align:center">
    <div class="eyebrow">Pricing</div>
    <h1>An AI agent for every workflow.</h1>
    <p style="font-size:16px;color:var(--t3);max-width:480px;margin:0 auto;line-height:1.7">Each UNIT AI agent is priced for what it automates, and what that's worth to your team. Start free, no card required.</p>
</div>

<div class="w" style="max-width:1040px;margin:0 auto;padding:0 24px 96px">

    <div class="pc-note">
        <svg style="width:16px;height:16px;color:var(--text);flex-shrink:0;margin-top:2px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p><strong style="color:var(--text)">You only pay for the worker you deploy.</strong> Each worker has a monthly rate covering a set number of transactions, then a low overage rate after that. What counts as a "transaction" depends on the worker, defined on each card.</p>
    </div>

    @if($plans->count())
    <div class="pc-grid">

        {{-- FREE TRIAL --}}
        <div class="pc-card pc-card-free">
            <div class="pc-inner">
                <div class="pc-tier">
                    <svg style="width:6px;height:6px" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                    Free Trial
                </div>
                <div class="pc-name">Try any worker free</div>
                <div class="pc-tagline">See real work done on your real data before you spend a dollar.</div>
                <div class="pc-price-row"><span class="pc-price">$0</span><span class="pc-price-unit">to start</span></div>
                <div class="pc-price-sub">
                    @if($trialTransactions)
                        {{ number_format($trialTransactions) }} transactions, no card required
                    @else
                        No card required
                    @endif
                </div>
                <div class="pc-tx-def">
                    @if($trialTransactions)
                        <strong>Every worker ships with {{ number_format($trialTransactions) }} free transactions{{ $trialDays ? ' over '.$trialDays.' days' : '' }}.</strong> Full pipeline. Your inbox, your clients, your templates, not a sandbox.
                    @else
                        <strong>Every worker ships with a free trial.</strong> Full pipeline. Your inbox, your clients, your templates, not a sandbox.
                    @endif
                </div>
                <ul class="pc-features">
                    @foreach(['Full AI pipeline on live data','Memory bank, templates & rules','Human review dashboard','Upgrade anytime, no restart'] as $f)
                    <li>
                        <span class="pc-check"><svg style="width:8px;height:8px" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                @php
                    if (!auth()->check()) {
                        $freeCtaLabel = 'Get started free';
                        $freeCtaHref  = route('register');
                    } elseif ($userBilling->isNotEmpty()) {
                        $freeCtaLabel = 'Go to your dashboard';
                        $freeCtaHref  = route('app.dashboard');
                    } else {
                        $freeCtaLabel = 'Get started free';
                        $freeCtaHref  = route('app.workers.index');
                    }
                @endphp
                <div class="pc-cta">
                    <a href="{{ $freeCtaHref }}" class="pc-btn pc-btn-ghost">{{ $freeCtaLabel }}</a>
                </div>
            </div>
        </div>

        {{-- WORKER CARDS --}}
        @foreach($plans as $plan)
        @php
            // Short name: take part before the separator
            $shortName = trim(preg_split('/\s*[:—\-]\s*/', $plan->display_name ?: $plan->worker_slug)[0]);

            // Nudge logged-in users based on their real subscription state for
            // this worker, instead of always sending them back to Register.
            $slug        = $plan->worker_slug;
            $billing     = $userBilling[$slug] ?? null;
            $deskRoute   = \Illuminate\Support\Facades\Route::has("app.desk.{$slug}") ? route("app.desk.{$slug}") : route('app.workers.billing', $slug);
            $billingRoute = route('app.workers.billing', $slug);

            if (!auth()->check()) {
                $ctaLabel = "Deploy {$shortName}";
                $ctaHref  = route('register');
                $banner   = null;
            } elseif (!$billing) {
                $ctaLabel = "Deploy {$shortName}";
                $ctaHref  = \Illuminate\Support\Facades\Route::has("hire.{$slug}.welcome") ? route("hire.{$slug}.welcome") : route('app.workers.index');
                $banner   = null;
            } elseif ($billing->status === 'active') {
                $ctaLabel = "Go to your desk";
                $ctaHref  = $deskRoute;
                $banner   = "You're already on {$shortName}: this is your current plan.";
            } elseif ($billing->status === 'trial') {
                $used     = (int) $billing->trial_transactions_used;
                $limit    = (int) $billing->trial_transactions_limit;
                $ctaLabel = "Upgrade to {$shortName}";
                $ctaHref  = $billingRoute;
                $banner   = "Free trial in progress: {$used}/{$limit} transactions used.";
            } elseif ($billing->status === 'trial_exhausted') {
                $ctaLabel = "Upgrade now";
                $ctaHref  = $billingRoute;
                $banner   = "Your free trial is used up, upgrade to keep {$shortName} running.";
            } elseif ($billing->status === 'past_due') {
                $ctaLabel = "Update payment method";
                $ctaHref  = route('app.billing.portal');
                $banner   = "Payment past due on your {$shortName} subscription.";
            } elseif ($billing->status === 'canceled') {
                $ctaLabel = "Reactivate {$shortName}";
                $ctaHref  = $billingRoute;
                $banner   = "Your {$shortName} subscription was canceled.";
            } else {
                $ctaLabel = "Deploy {$shortName}";
                $ctaHref  = route('app.workers.index');
                $banner   = null;
            }
        @endphp
        <div class="pc-card pc-card-worker">
            <div class="pc-glow-stripe"></div>
            <div class="pc-inner">
                <div class="pc-tier">
                    <svg style="width:6px;height:6px" fill="var(--gold)" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                    {{ strtoupper($plan->worker_slug) }} Worker
                </div>
                <div class="pc-name">{{ $plan->display_name ?: strtoupper($plan->worker_slug) }}</div>
                @if($plan->tagline)<div class="pc-tagline">{{ $plan->tagline }}</div>@endif
                @if($banner)
                <div class="pc-tx-def" style="margin-bottom:14px">
                    <strong style="color:var(--gold)">{{ $banner }}</strong>
                </div>
                @endif
                <div class="pc-price-row">
                    <span class="pc-price">${{ number_format($plan->monthly_flat_rate) }}</span>
                    <span class="pc-price-unit">/month</span>
                </div>
                <div class="pc-price-sub">
                    @if($plan->included_transactions > 0)
                        {{ number_format($plan->included_transactions) }} tx included · ${{ number_format($plan->overage_price_per_tx, 2) }}/tx after
                    @else
                        Unlimited transactions
                    @endif
                </div>
                @if($plan->transaction_label)
                <div class="pc-tx-def"><strong>1 transaction =</strong> {{ $plan->transaction_label }}</div>
                @endif
                <ul class="pc-features">
                    @foreach([
                        number_format($plan->included_transactions).' transactions/month included',
                        'Full pipeline, no features gated',
                        'Memory bank, templates & custom rules',
                        'Usage dashboard & audit trail',
                        'Email support + onboarding help',
                    ] as $f)
                    <li>
                        <span class="pc-check"><svg style="width:8px;height:8px" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <div class="pc-cta">
                    <a href="{{ $ctaHref }}" class="pc-btn">{{ $ctaLabel }}</a>
                    @if($plan->worker_url)
                    <a href="{{ $plan->worker_url }}" class="pc-worker-link">Learn more about {{ $shortName }} →</a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        {{-- ENTERPRISE --}}
        <div class="pc-card pc-card-enterprise">
            <div class="pc-inner">
                <div class="pc-tier">
                    <svg style="width:6px;height:6px" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                    Enterprise
                </div>
                <div class="pc-name">High-volume & custom</div>
                <div class="pc-tagline">For teams running large volumes, needing SLAs, or wanting a worker built for their exact workflow.</div>
                <div class="pc-price-row"><span class="pc-price" style="font-size:30px">Custom</span></div>
                <div class="pc-price-sub">Volume pricing · Annual options · Dedicated support</div>
                <ul class="pc-features" style="margin-top:16px">
                    @foreach(['Unlimited transaction volume','Dedicated processing queue','Custom worker for your workflow','Uptime SLA + priority support','White-label options available'] as $f)
                    <li>
                        <span class="pc-check"><svg style="width:8px;height:8px" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <div class="pc-cta">
                    <a href="mailto:hello@unit.report?subject=Enterprise inquiry" class="pc-btn pc-btn-ghost">Talk to us</a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div style="text-align:center;padding:72px 0"><p style="color:var(--t3)">Pricing coming soon.</p></div>
    @endif

    {{-- FAQ - single source of truth for both the visible accordion and
         the FAQPage schema below, so they can't drift out of sync. --}}
    @php
        $__pricingFaqs = [
            ['q' => 'What counts as a transaction?', 'a' => 'It depends on the worker: each card above defines exactly what "1 transaction" means for that specific worker. For AVA it\'s one renewal email processed through the full pipeline: read, classified, drafted, and pushed to your Gmail drafts.'],
            ['q' => 'What happens when I run out of transactions?', 'a' => 'Processing continues at the overage rate shown on the card. You can set a monthly spend cap from billing to prevent unexpected charges.'],
            ['q' => 'Can I run multiple workers?', 'a' => 'Yes. Each worker you deploy is billed independently. They don\'t share transaction pools or billing, each meters separately.'],
            ['q' => 'Can I cancel anytime?', 'a' => 'Yes. Cancel from billing at any time. Your worker runs through the end of the period. Data is preserved for 30 days after.'],
            ['q' => 'Do you use my email content to train AI?', 'a' => 'No. Content is processed in memory to generate drafts and stored only in your account audit trail. We never use your data to train AI models.'],
            ['q' => 'Is there a long-term contract?', 'a' => 'No. All plans are month-to-month. Enterprise includes annual pricing options.'],
        ];
    @endphp
    <div class="faq-wrap">
        <h2 style="font-family:var(--fd);font-size:24px;font-weight:800;color:var(--text);text-align:center;margin-bottom:6px">Common questions</h2>
        <p style="text-align:center;color:var(--t3);font-size:13px;margin-bottom:32px">Anything else? <a href="mailto:hello@unit.report" style="color:var(--text);font-weight:600;text-decoration:underline">hello@unit.report</a></p>
        @foreach($__pricingFaqs as $faq)
        <details class="faq-item">
            <summary>{{ $faq['q'] }}<svg class="faq-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></summary>
            <p>{{ $faq['a'] }}</p>
        </details>
        @endforeach
    </div>

</div>

@section('head')
<script type="application/ld+json">{!! json_encode([
    '@@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type' => 'Question',
        'name' => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ], $__pricingFaqs),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

<script>
document.querySelectorAll('.faq-item').forEach(function(d){
    d.addEventListener('toggle',function(){ this.querySelector('.faq-chevron').style.transform=this.open?'rotate(180deg)':'' });
});
</script>
@endsection

@extends('layouts.public')
@section('title', 'Pricing — UNIT')
@section('description', 'Each UNIT worker is priced for the specific work it does. Start free on any worker, no card required.')

@section('body')

<style>
.pc-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:20px;
    margin:44px 0 68px;
    align-items:stretch;
}

/* ── BASE CARD ── */
.pc-card {
    border-radius:20px;
    display:flex;
    flex-direction:column;
    position:relative;
    overflow:hidden;
    transition:transform .2s,box-shadow .2s;
}
.pc-card:hover { transform:translateY(-2px) }

[data-theme="dark"] .pc-card-free       { background:#0e0e16;border:1px solid rgba(74,222,128,.2);box-shadow:0 16px 48px rgba(0,0,0,.45) }
[data-theme="dark"] .pc-card-worker     { border:1px solid rgba(255,255,255,.08);box-shadow:0 16px 48px rgba(0,0,0,.45) }
[data-theme="dark"] .pc-card-enterprise { background:#0e0e16;border:1px solid rgba(96,165,250,.2);box-shadow:0 16px 48px rgba(0,0,0,.45) }
[data-theme="dark"] .pc-card:hover      { box-shadow:0 24px 64px rgba(0,0,0,.6) }

[data-theme="light"] .pc-card-free      { background:#fff;border:1px solid #d4f5e1;box-shadow:0 2px 16px rgba(0,0,0,.06) }
[data-theme="light"] .pc-card-worker    { background:#fff;border:1px solid #e8e8e6;box-shadow:0 2px 16px rgba(0,0,0,.06) }
[data-theme="light"] .pc-card-enterprise{ background:#fff;border:1px solid #cde3fb;box-shadow:0 2px 16px rgba(0,0,0,.06) }
[data-theme="light"] .pc-card:hover     { box-shadow:0 6px 32px rgba(0,0,0,.1) }

.pc-glow-stripe { height:3px;width:100%;position:absolute;top:0;left:0 }
.pc-inner { padding:24px;display:flex;flex-direction:column;flex:1 }

/* Tier badge — explicit colors per theme */
.pc-tier {
    display:inline-flex;align-items:center;gap:5px;
    font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;
    padding:4px 10px;border-radius:99px;margin-bottom:16px;width:fit-content;
}

/* Worker card badge adapts to light: use dark text on tinted bg */
[data-theme="light"] .pc-tier-worker { color:#000 !important;opacity:.75 }

.pc-name    { font-size:18px;font-weight:800;color:var(--text);font-family:'Space Grotesk',sans-serif;margin-bottom:5px;line-height:1.2 }
.pc-tagline { font-size:13px;color:var(--t3);line-height:1.6;margin-bottom:18px }

.pc-price-row { display:flex;align-items:baseline;gap:4px;margin-bottom:3px }
.pc-price     { font-size:40px;font-weight:800;color:var(--text);line-height:1;font-family:'Space Grotesk',sans-serif }
.pc-price-unit{ font-size:13px;color:var(--t3) }
.pc-price-sub { font-size:12px;color:var(--t3);margin-bottom:16px }

.pc-tx-def {
    font-size:12px;color:var(--t3);
    padding:9px 12px;border-radius:9px;line-height:1.55;margin-bottom:18px;
}
[data-theme="dark"]  .pc-tx-def { background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08) }
[data-theme="light"] .pc-tx-def { background:#f4f4f2;border:1px solid #e0e0de;color:#555 }
[data-theme="light"] .pc-tx-def strong { color:#111 }

.pc-features { list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px }
.pc-features li { font-size:13px;color:var(--t2);display:flex;align-items:flex-start;gap:8px;line-height:1.5 }
[data-theme="light"] .pc-features li { color:#333 }
.pc-check { width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px }

/* Buttons */
.pc-cta { margin-top:auto;padding-top:20px }
.pc-btn {
    display:block;width:100%;text-align:center;
    padding:12px 0;border-radius:10px;
    font-size:13px;font-weight:700;text-decoration:none;
    transition:opacity .15s,transform .15s;
}
.pc-btn:hover { opacity:.88;transform:translateY(-1px) }
.pc-worker-link {
    display:block;text-align:center;margin-top:10px;
    font-size:12px;color:var(--t3);text-decoration:none;
    transition:color .15s;
}
.pc-worker-link:hover { color:var(--t2) }

/* Note banner */
.pc-note {
    border-radius:12px;padding:14px 18px;margin-bottom:36px;
    display:flex;gap:11px;align-items:flex-start;
}
[data-theme="dark"]  .pc-note { background:rgba(241,211,98,.05);border:1px solid rgba(241,211,98,.16) }
[data-theme="light"] .pc-note { background:#fffbea;border:1px solid #f0d96e }
.pc-note p { font-size:13px;color:var(--t2);line-height:1.6;margin:0 }
[data-theme="light"] .pc-note p { color:#4a3a00 }

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
    <h1>A worker for every workflow.</h1>
    <p style="font-size:16px;color:var(--t3);max-width:480px;margin:0 auto;line-height:1.7">Each UNIT worker is priced for what it automates — and what that's worth to your team. Start free, no card required.</p>
</div>

<div class="w" style="max-width:1040px;margin:0 auto;padding:0 24px 96px">

    <div class="pc-note">
        <svg style="width:16px;height:16px;color:var(--gold);flex-shrink:0;margin-top:2px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p><strong style="color:var(--text)">You only pay for the worker you deploy.</strong> Each worker has a monthly rate covering a set number of transactions, then a low overage rate after that. What counts as a "transaction" depends on the worker — defined on each card.</p>
    </div>

    @if($plans->count())
    <div class="pc-grid">

        {{-- FREE TRIAL --}}
        <div class="pc-card pc-card-free">
            <div class="pc-glow-stripe" style="background:linear-gradient(90deg,transparent,rgba(74,222,128,.55),transparent)"></div>
            <div class="pc-inner">
                <div class="pc-tier" style="background:rgba(74,222,128,.12);color:#16a34a;border:1px solid rgba(74,222,128,.25)">
                    <svg style="width:6px;height:6px" fill="#4ade80" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                    Free Trial
                </div>
                <div class="pc-name">Try any worker free</div>
                <div class="pc-tagline">See real work done on your real data before you spend a dollar.</div>
                <div class="pc-price-row"><span class="pc-price">$0</span><span class="pc-price-unit">to start</span></div>
                <div class="pc-price-sub">10 transactions, no card required</div>
                <div class="pc-tx-def">
                    <strong>Every worker ships with 10 free transactions.</strong> Full pipeline. Your inbox, your clients, your templates — not a sandbox.
                </div>
                <ul class="pc-features">
                    @foreach(['Full AI pipeline on live data','Memory bank, templates & rules','Human review dashboard','Upgrade anytime — no restart'] as $f)
                    <li>
                        <span class="pc-check" style="background:rgba(74,222,128,.12)"><svg style="width:8px;height:8px" fill="none" stroke="#16a34a" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <div class="pc-cta">
                    <a href="{{ route('register') }}" class="pc-btn" style="background:rgba(74,222,128,.14);color:#16a34a;border:1px solid rgba(74,222,128,.3)">Get started free</a>
                </div>
            </div>
        </div>

        {{-- WORKER CARDS --}}
        @foreach($plans as $plan)
        @php
            $accent = $plan->accent_color ?? '#142C74';
            $hex = ltrim($accent, '#');
            $r = hexdec(substr($hex,0,2)); $g = hexdec(substr($hex,2,2)); $b = hexdec(substr($hex,4,2));
            // Short name: take part before — or -
            $shortName = trim(preg_split('/\s*[—\-]\s*/', $plan->display_name ?: $plan->worker_slug)[0]);
        @endphp
        <div class="pc-card pc-card-worker" style="background:linear-gradient(150deg,rgba({{ $r }},{{ $g }},{{ $b }},.06) 0%,transparent 50%)">
            <div class="pc-glow-stripe" style="background:linear-gradient(90deg,transparent,{{ $accent }},transparent);opacity:.6"></div>
            <div class="pc-inner">
                <div class="pc-tier pc-tier-worker" style="background:rgba({{ $r }},{{ $g }},{{ $b }},.12);color:{{ $accent }};border:1px solid rgba({{ $r }},{{ $g }},{{ $b }},.22)">
                    <svg style="width:6px;height:6px" fill="{{ $accent }}" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                    {{ strtoupper($plan->worker_slug) }} Worker
                </div>
                <div class="pc-name">{{ $plan->display_name ?: strtoupper($plan->worker_slug) }}</div>
                @if($plan->tagline)<div class="pc-tagline">{{ $plan->tagline }}</div>@endif
                <div class="pc-price-row">
                    <span class="pc-price">${{ number_format($plan->monthly_flat_rate) }}</span>
                    <span class="pc-price-unit">/month</span>
                </div>
                <div class="pc-price-sub">{{ number_format($plan->included_transactions) }} tx included · ${{ number_format($plan->overage_price_per_tx, 2) }}/tx after</div>
                @if($plan->transaction_label)
                <div class="pc-tx-def"><strong>1 transaction =</strong> {{ $plan->transaction_label }}</div>
                @endif
                <ul class="pc-features">
                    @foreach([
                        number_format($plan->included_transactions).' transactions/month included',
                        'Full pipeline — no features gated',
                        'Memory bank, templates & custom rules',
                        'Usage dashboard & audit trail',
                        'Email support + onboarding help',
                    ] as $f)
                    <li>
                        <span class="pc-check" style="background:rgba({{ $r }},{{ $g }},{{ $b }},.1)"><svg style="width:8px;height:8px" fill="none" stroke="{{ $accent }}" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <div class="pc-cta">
                    <a href="{{ route('register') }}" class="pc-btn" style="background:{{ $accent }};color:#ffffff">Deploy {{ $shortName }}</a>
                    @if($plan->worker_url)
                    <a href="{{ $plan->worker_url }}" class="pc-worker-link">Learn more about {{ $shortName }} →</a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        {{-- ENTERPRISE --}}
        <div class="pc-card pc-card-enterprise">
            <div class="pc-glow-stripe" style="background:linear-gradient(90deg,transparent,rgba(96,165,250,.5),transparent)"></div>
            <div class="pc-inner">
                <div class="pc-tier" style="background:rgba(96,165,250,.1);color:#1d6fb8;border:1px solid rgba(96,165,250,.22)">
                    <svg style="width:6px;height:6px" fill="#60a5fa" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                    Enterprise
                </div>
                <div class="pc-name">High-volume & custom</div>
                <div class="pc-tagline">For teams running large volumes, needing SLAs, or wanting a worker built for their exact workflow.</div>
                <div class="pc-price-row"><span class="pc-price" style="font-size:30px">Custom</span></div>
                <div class="pc-price-sub">Volume pricing · Annual options · Dedicated support</div>
                <ul class="pc-features" style="margin-top:16px">
                    @foreach(['Unlimited transaction volume','Dedicated processing queue','Custom worker for your workflow','Uptime SLA + priority support','White-label options available'] as $f)
                    <li>
                        <span class="pc-check" style="background:rgba(96,165,250,.1)"><svg style="width:8px;height:8px" fill="none" stroke="#1d6fb8" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                        {{ $f }}
                    </li>
                    @endforeach
                </ul>
                <div class="pc-cta">
                    <a href="mailto:hello@unit.report?subject=Enterprise inquiry" class="pc-btn" style="background:rgba(96,165,250,.1);color:#1d6fb8;border:1px solid rgba(96,165,250,.25)">Talk to us</a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div style="text-align:center;padding:72px 0"><p style="color:var(--t3)">Pricing coming soon.</p></div>
    @endif

    {{-- FAQ --}}
    <div class="faq-wrap">
        <h2 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:800;color:var(--text);text-align:center;margin-bottom:6px">Common questions</h2>
        <p style="text-align:center;color:var(--t3);font-size:13px;margin-bottom:32px">Anything else? <a href="mailto:hello@unit.report" style="color:var(--gold-text);text-decoration:underline">hello@unit.report</a></p>
        @foreach([
            ['What counts as a transaction?','It depends on the worker — each card above defines exactly what "1 transaction" means for that specific worker. For AVA it\'s one renewal email processed through the full pipeline: read, classified, drafted, and pushed to your Gmail drafts.'],
            ['What happens when I run out of transactions?','Processing continues at the overage rate shown on the card. You can set a monthly spend cap from billing to prevent unexpected charges.'],
            ['Can I run multiple workers?','Yes. Each worker you deploy is billed independently. They don\'t share transaction pools or billing — each meters separately.'],
            ['Can I cancel anytime?','Yes. Cancel from billing at any time. Your worker runs through the end of the period. Data is preserved for 30 days after.'],
            ['Do you use my email content to train AI?','No. Content is processed in memory to generate drafts and stored only in your account audit trail. We never use your data to train AI models.'],
            ['Is there a long-term contract?','No. All plans are month-to-month. Enterprise includes annual pricing options.'],
        ] as [$q,$a])
        <details class="faq-item">
            <summary>{{ $q }}<svg class="faq-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></summary>
            <p>{{ $a }}</p>
        </details>
        @endforeach
    </div>

</div>

<script>
document.querySelectorAll('.faq-item').forEach(function(d){
    d.addEventListener('toggle',function(){ this.querySelector('.faq-chevron').style.transform=this.open?'rotate(180deg)':'' });
});
</script>
@endsection

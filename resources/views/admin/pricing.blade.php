<x-app-layout title="Worker Pricing">

<style>
/* ── Layout ─────────────────────────────────────────────────────────────── */
.pr-wrap    { display:grid;grid-template-columns:360px 1fr;gap:20px;align-items:start }
.pr-left    { position:sticky;top:20px;max-height:calc(100vh - 80px);overflow-y:auto }
.pr-right   { position:sticky;top:20px }

/* ── Mobile ─────────────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .pr-wrap        { grid-template-columns:1fr;gap:0 }
    .pr-left        { position:static;max-height:none;overflow:visible }
    .pr-right       { position:static;display:none }
    .pr-right.mobile-open { display:block;margin-top:12px }
    .ep-body        { max-height:none }
    .wf-grid        { grid-template-columns:1fr }
    .wf-full        { grid-column:1 }
    .mobile-back    { display:flex !important }
    .ue-costs-grid  { grid-template-columns:1fr 1fr !important }
    .ue-costs-grid > div:nth-child(2) { border-right:none !important }
    .ue-costs-grid > div:nth-child(3) { border-top:1px solid var(--border) }
    .ue-costs-grid > div:nth-child(4) { border-top:1px solid var(--border) }
}

/* ── Plan list ──────────────────────────────────────────────────────────── */
.pl-group   { margin-bottom:20px }
.pl-worker  { font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
              color:var(--text-muted);padding:0 4px;margin-bottom:6px;display:flex;align-items:center;gap:8px }
.pl-card    { background:var(--bg-card);border:1px solid var(--border);border-radius:12px;
              margin-bottom:6px;cursor:pointer;transition:border-color .15s,box-shadow .15s;overflow:hidden }
.pl-card:hover       { border-color:rgba(241,211,98,.4) }
.pl-card.active      { border-color:var(--accent);box-shadow:0 0 0 1px var(--accent) }
.pl-bar     { height:2px }
.pl-inner   { padding:12px 14px;display:flex;align-items:center;gap:10px }
.pl-dot     { width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.pl-info    { flex:1;min-width:0 }
.pl-name    { font-size:13px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:6px;flex-wrap:wrap }
.pl-sub     { font-size:11px;color:var(--text-muted);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.pl-price   { font-size:14px;font-weight:700;color:var(--text-primary);flex-shrink:0;text-align:right }
.pl-price-sub { font-size:10px;color:var(--text-muted);font-weight:400 }

/* ── Badges ─────────────────────────────────────────────────────────────── */
.bd         { font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px }
.bd-live    { background:rgba(34,197,94,.1);color:#4ade80;border:1px solid rgba(34,197,94,.2) }
.bd-hidden  { background:rgba(156,163,175,.08);color:var(--text-muted);border:1px solid var(--border) }
.bd-promo   { background:rgba(168,85,247,.12);color:#c084fc;border:1px solid rgba(168,85,247,.25) }
.bd-mode-live { background:rgba(34,197,94,.1);color:#4ade80;border:1px solid rgba(34,197,94,.2) }
.bd-mode-test { background:rgba(245,158,11,.12);color:#fbbf24;border:1px solid rgba(245,158,11,.25) }

/* ── Edit panel ─────────────────────────────────────────────────────────── */
.ep-wrap    { background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden }
.ep-empty   { padding:64px 24px;text-align:center }
.ep-hd      { padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px }
.ep-body    { padding:20px;max-height:calc(100vh - 160px);overflow-y:auto }

/* ── Mode toggle ────────────────────────────────────────────────────────── */
.mode-toggle        { display:flex;border:1px solid var(--border);border-radius:9px;overflow:hidden;flex-shrink:0 }
.mode-toggle button { font-size:11px;font-weight:700;padding:5px 12px;border:none;cursor:pointer;transition:all .15s;background:transparent;color:var(--text-muted) }
.mode-toggle button.on-test  { background:rgba(245,158,11,.15);color:#fbbf24 }
.mode-toggle button.on-live  { background:rgba(34,197,94,.12);color:#4ade80 }

/* ── Form ───────────────────────────────────────────────────────────────── */
.wf-grid  { display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:0 }
.wf-full  { grid-column:1/-1 }
.wf-lbl   { font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);margin-bottom:3px }
.wf-input,.wf-select,.wf-textarea {
    width:100%;background:var(--bg-raised);border:1px solid var(--border);
    border-radius:8px;padding:7px 10px;font-size:13px;color:var(--text-primary);
    outline:none;transition:border .15s;appearance:none;-webkit-appearance:none;box-sizing:border-box
}
.wf-input:focus,.wf-select:focus,.wf-textarea:focus { border-color:var(--accent) }
.wf-hint  { font-size:11px;color:var(--text-muted);margin-top:3px }
.wf-sep   { font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;
            color:var(--text-muted);margin:14px 0 8px;padding-top:14px;border-top:1px solid var(--border) }
.wf-sel-wrap { position:relative }
.wf-sel-wrap::after { content:'';position:absolute;right:10px;top:50%;transform:translateY(-50%);
    width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;
    border-top:5px solid var(--text-muted);pointer-events:none }
/* ss-wrap (JS-enhanced searchable select) provides its own chevron button */
.wf-sel-wrap:has(.ss-wrap)::after { display:none }
.wf-color-row { display:flex;gap:8px;align-items:center }
.wf-swatch { width:32px;height:32px;border-radius:7px;border:1px solid var(--border);cursor:pointer;padding:2px;background:none;flex-shrink:0 }
.wf-verify-row { display:flex;gap:7px;align-items:flex-start }
.wf-verify-row .wf-input { flex:1 }
.wf-verify-badge { font-size:11px;padding:3px 8px;border-radius:6px;margin-top:4px;display:none }
.wf-verify-ok  { background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80 }
.wf-verify-err { background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171 }

.wp-btn      { font-size:12px;font-weight:600;padding:6px 14px;border-radius:8px;cursor:pointer;transition:all .15s;white-space:nowrap }
.wp-btn-gold { background:var(--accent);color:#12100a;border:none }
.wp-btn-gold:hover { opacity:.88 }
.wp-btn-out  { background:transparent;border:1px solid var(--border);color:var(--text-secondary) }
.wp-btn-out:hover  { border-color:var(--accent);color:var(--text-primary) }
.wp-btn-red  { background:transparent;border:1px solid rgba(239,68,68,.25);color:#f87171 }
.wp-btn-red:hover  { background:rgba(239,68,68,.08) }
.wp-btn-sm   { font-size:11px;padding:5px 10px;border-radius:7px;cursor:pointer;border:1px solid var(--border);background:var(--bg-raised);color:var(--text-secondary);white-space:nowrap;transition:all .15s }
.wp-btn-sm:hover   { border-color:var(--accent);color:var(--text-primary) }

/* ── Add modal (kept for new plan) ─────────────────────────────────────── */
.wp-modal-bg  { display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;align-items:center;justify-content:center }
.wp-modal-bg.open { display:flex }
.wp-modal     { background:var(--bg-card);border:1px solid var(--border);border-radius:18px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;padding:24px }
</style>

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-semibold" style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80">{{ session('success') }}</div>
@endif
@if(session('warning'))
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-semibold" style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);color:#fbbf24">{{ session('warning') }}</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold" style="color:var(--text-primary)">Worker Pricing</h1>
        <p class="text-sm mt-0.5" style="color:var(--text-muted)">Click a plan to edit. Billing mode controls which Stripe environment is active per worker.</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <a href="{{ $stripeDashBase }}products" target="_blank"
           style="font-size:12px;padding:6px 12px;border-radius:8px;border:1px solid var(--border);color:var(--text-secondary);text-decoration:none;display:flex;align-items:center;gap:5px">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.6 7.2c0-.8.7-1.1 1.7-1.1 1.5 0 3.5.5 4.8 1.2V3.5C18.8 2.9 17 2.5 15.3 2.5c-3.5 0-5.9 1.8-5.9 4.9 0 4.8 6.6 4 6.6 6.1 0 .9-.8 1.2-1.9 1.2-1.7 0-3.8-.7-5.4-1.6v3.9c1.8.8 3.7 1.2 5.4 1.2 3.6 0 6.1-1.8 6.1-4.9-.1-5.1-6.7-4.2-6.6-6.1z" fill="#635BFF"/></svg>
            Stripe Dashboard
        </a>
        <button onclick="openAdd()" class="wp-btn wp-btn-gold">+ Add Plan</button>
    </div>
</div>

<div class="pr-wrap">

    {{-- ── LEFT: Plan list ──────────────────────────────────────── --}}
    <div class="pr-left">
        @forelse($plans->groupBy('worker_slug') as $slug => $workerPlans)
        @php
            $firstPlan  = $workerPlans->first();
            $workerMode = $firstPlan->billing_mode ?? 'test';
        @endphp
        <div class="pl-group">
            <div class="pl-worker">
                {{ strtoupper($slug) }}
                <span class="bd {{ $workerMode === 'live' ? 'bd-mode-live' : 'bd-mode-test' }}">
                    {{ $workerMode === 'live' ? '● LIVE' : '○ TEST' }}
                </span>
            </div>

            @foreach($workerPlans as $plan)
            @php
                $accent = $plan->accent_color ?? '#f1d362';
                $hex = ltrim($accent,'#');
                $r=hexdec(substr($hex,0,2));$g=hexdec(substr($hex,2,2));$b=hexdec(substr($hex,4,2));
                $hasPromo = !empty($plan->discount_pct) || !empty($plan->promo_label);
                $effectiveRate = $plan->monthly_flat_rate > 0 && !empty($plan->discount_pct)
                    ? $plan->monthly_flat_rate * (1 - $plan->discount_pct / 100)
                    : $plan->monthly_flat_rate;
                $isEditing = request('editing') == $plan->id;
                $hasLivePrice = !empty($plan->stripe_flat_price_id);
                $hasTestPrice = !empty($plan->stripe_test_price_id);
            @endphp
            <div class="pl-card {{ $isEditing ? 'active' : '' }}"
                 id="card-{{ $plan->id }}"
                 onclick="selectPlan({{ $plan->id }})">
                <div class="pl-bar" style="background:{{ $accent }}"></div>
                <div class="pl-inner">
                    <div class="pl-dot" style="background:rgba({{ $r }},{{ $g }},{{ $b }},.15);border:1px solid rgba({{ $r }},{{ $g }},{{ $b }},.3)">
                        <div style="width:9px;height:9px;border-radius:50%;background:{{ $accent }}"></div>
                    </div>
                    <div class="pl-info">
                        <div class="pl-name">
                            {{ $plan->display_name ?: strtoupper($plan->worker_slug) }}
                            <span class="bd {{ $plan->active ? 'bd-live' : 'bd-hidden' }}" style="font-size:9px">{{ $plan->active ? 'live' : 'hidden' }}</span>
                            @if($hasPromo)<span class="bd bd-promo" style="font-size:9px">{{ $plan->promo_label ?: round($plan->discount_pct).'% off' }}</span>@endif
                        </div>
                        <div class="pl-sub" style="font-family:monospace">
                            @if($workerMode === 'live' && $hasLivePrice)
                                <span style="color:#635BFF;font-size:9px">▸</span> {{ $plan->stripe_flat_price_id }}
                            @elseif($workerMode === 'test' && $hasTestPrice)
                                <span style="color:#fbbf24;font-size:9px">▸</span> {{ $plan->stripe_test_price_id }}
                            @else
                                <span style="color:#f87171;font-size:9px">⚠ no {{ $workerMode }} price linked</span>
                            @endif
                        </div>
                    </div>
                    <div class="pl-price">
                        @if($plan->monthly_flat_rate > 0)
                            ${{ number_format($effectiveRate, 0) }}<span class="pl-price-sub">/mo</span>
                        @else
                            <span style="font-size:12px;color:var(--text-muted)">Custom</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @empty
        <div style="text-align:center;padding:48px 24px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px">
            <p style="color:var(--text-muted);font-size:14px;margin-bottom:16px">No workers set up yet.</p>
            <button onclick="openAdd()" class="wp-btn wp-btn-gold">+ Add First Worker</button>
        </div>
        @endforelse
    </div>

    {{-- ── RIGHT: Edit panel ───────────────────────────────────── --}}
    <div class="pr-right">
        <div class="ep-wrap" id="edit-panel">
            <div class="ep-empty" id="ep-empty">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" style="margin:0 auto 12px;display:block;opacity:.2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <p style="color:var(--text-muted);font-size:13px">Select a plan on the left to edit it</p>
            </div>
            <div id="ep-content" style="display:none">
                <div class="ep-hd" id="ep-header">
                    <button type="button" class="mobile-back" onclick="closeMobilePanel()"
                        style="display:none;background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0 8px 0 0;flex-shrink:0;font-size:18px;line-height:1">←</button>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:14px;font-weight:600;color:var(--text-primary)" id="ep-title">—</div>
                        <div style="font-size:11px;color:var(--text-muted);font-family:monospace" id="ep-slugs">—</div>
                    </div>
                    <div class="mode-toggle" id="ep-mode-toggle"></div>
                    <form id="ep-toggle-form" method="POST" style="margin:0">
                        @csrf
                    </form>
                </div>
                <div class="ep-body">
                    <form id="ep-form" method="POST">
                        @csrf @method('PUT')

                        <div class="wf-grid">
                            <div class="wf-full">
                                <div class="wf-lbl">Display Name</div>
                                <input name="display_name" id="ef-display_name" class="wf-input" placeholder="AVA — Starter">
                            </div>
                            <div class="wf-full">
                                <div class="wf-lbl">Tagline</div>
                                <input name="tagline" id="ef-tagline" class="wf-input" placeholder="What this plan does in one line">
                            </div>
                            <div class="wf-full">
                                <div class="wf-lbl">Transaction Definition</div>
                                <input name="transaction_label" id="ef-transaction_label" class="wf-input" placeholder="one renewal email processed end-to-end">
                                <div class="wf-hint">Shown as "1 transaction = ..." on the public page</div>
                            </div>
                            <div>
                                <div class="wf-lbl">Worker Page URL</div>
                                <input name="worker_url" id="ef-worker_url" class="wf-input" placeholder="/w/ava">
                            </div>
                            <div>
                                <div class="wf-lbl">Sort Order</div>
                                <input type="number" name="sort_order" id="ef-sort_order" class="wf-input">
                            </div>
                            <div class="wf-full">
                                <div class="wf-lbl">Accent Color</div>
                                <div class="wf-color-row">
                                    <input type="color" id="ef-cp" value="#f1d362" class="wf-swatch" oninput="document.getElementById('ef-accent_color').value=this.value">
                                    <input type="text" id="ef-accent_color" name="accent_color" class="wf-input" value="#f1d362" style="font-family:monospace" oninput="document.getElementById('ef-cp').value=this.value">
                                </div>
                            </div>
                        </div>

                        {{-- TRIAL PLAN --}}
                        <div class="wf-sep">Trial Plan</div>
                        <div class="wf-grid">
                            <div class="wf-full" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:9px;background:var(--bg-raised);border:1px solid var(--border)">
                                <input type="checkbox" name="is_trial_plan" id="ef-is_trial_plan" value="1" style="width:16px;height:16px;accent-color:var(--accent);cursor:pointer" onchange="onTrialPlanToggle(this.checked)">
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:var(--text-primary)">This is the trial experience plan</div>
                                    <div class="wf-hint" style="margin-top:0">Trial plans are shown during onboarding but hidden from the subscription paywall — subscribers move to Pro or Enterprise</div>
                                </div>
                            </div>
                            <div id="ef-trial-days-wrap">
                                <div class="wf-lbl">Trial Duration (days)</div>
                                <input type="number" name="trial_days" id="ef-trial_days" class="wf-input" placeholder="30" min="1" max="365">
                                <div class="wf-hint">Leave blank to use the platform default. Options: 14 · 30 · 60 · 90</div>
                            </div>
                            <div></div>
                        </div>

                        <div class="wf-sep">Pricing</div>
                        <div class="wf-grid">
                            <div>
                                <div class="wf-lbl">Free Trial Transactions</div>
                                <input type="number" name="free_transactions" id="ef-free_transactions" class="wf-input">
                            </div>
                            <div>
                                <div class="wf-lbl">Monthly Flat Rate ($)</div>
                                <input type="number" step="0.01" name="monthly_flat_rate" id="ef-monthly_flat_rate" class="wf-input" oninput="refreshUEFromForm()">
                                <div class="wf-hint">Must match the Stripe price charge</div>
                            </div>
                            <div>
                                <div class="wf-lbl">Transaction Limit <span style="font-weight:400;color:var(--text-muted)">(0 = unlimited)</span></div>
                                <input type="number" name="transaction_limit" id="ef-transaction_limit" class="wf-input" oninput="refreshUEFromForm()">
                                <div class="wf-hint">Enforced by PolicyEngine</div>
                            </div>
                            <div>
                                <div class="wf-lbl">Support Label</div>
                                <input name="support_label" id="ef-support_label" class="wf-input" placeholder="Email support">
                            </div>
                        </div>

                        <div class="wf-sep">Plan Highlights</div>
                        <textarea name="plan_highlights" id="ef-plan_highlights" class="wf-input wf-textarea" rows="5"
                                  style="resize:vertical;font-size:12px;line-height:1.6"
                                  placeholder="One highlight per line&#10;100 emails/month&#10;Full pipeline"></textarea>
                        <div class="wf-hint" style="margin-top:4px">One bullet per line · 4–6 lines ideal</div>

                        {{-- AI TIER --}}
                        <div class="wf-sep">AI Tier</div>
                        <div class="wf-grid">
                            <div class="wf-full">
                                <div class="wf-lbl">Tier</div>
                                <div class="wf-sel-wrap">
                                    <select name="ai_tier" id="ef-ai_tier" class="wf-select" onchange="onTierChange(this.value)">
                                        <option value="economy">Economy — Haiku for all stages</option>
                                        <option value="standard">Standard — Haiku classify · Sonnet draft</option>
                                        <option value="premium">Premium — Sonnet for all stages</option>
                                    </select>
                                </div>
                                <div class="wf-hint">Controls which Claude model runs each pipeline stage for subscribers on this plan</div>
                            </div>
                            <div id="ef-stage-models-container" style="display:contents"></div>
                            <div>
                                <div class="wf-lbl">Draft Downgrade Threshold <span style="font-weight:400;color:var(--text-muted)">(0 = none)</span></div>
                                <input type="number" name="draft_model_threshold" id="ef-draft_model_threshold" class="wf-input" placeholder="500">
                                <div class="wf-hint">After this many transactions/mo, draft switches to the classify model to protect margin</div>
                            </div>
                        </div>
                        <div id="ai-tier-cost" style="margin-top:8px;padding:10px 12px;border-radius:9px;background:var(--bg-raised);border:1px solid var(--border);font-size:12px;color:var(--text-muted)"></div>

                        {{-- UNIT ECONOMICS --}}
                        <div class="wf-sep">Unit Economics</div>
                        <div id="ue-panel" style="border-radius:10px;overflow:hidden;border:1px solid var(--border)">
                            <div style="display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--border)">
                                <div style="padding:10px 14px;border-right:1px solid var(--border)">
                                    <div style="font-size:10px;font-weight:600;letter-spacing:.06em;color:var(--text-muted);margin-bottom:4px">REVENUE</div>
                                    <div id="ue-revenue" style="font-size:18px;font-weight:700;color:var(--text-primary)">—</div>
                                    <div style="font-size:10px;color:var(--text-muted)">monthly flat rate</div>
                                </div>
                                <div style="padding:10px 14px">
                                    <div style="font-size:10px;font-weight:600;letter-spacing:.06em;color:var(--text-muted);margin-bottom:4px">GROSS PROFIT</div>
                                    <div id="ue-gp" style="font-size:18px;font-weight:700;color:var(--text-primary)">—</div>
                                    <div id="ue-gp-pct" style="font-size:10px;color:var(--text-muted)">after all costs</div>
                                </div>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(4,1fr);background:var(--bg-raised)" class="ue-costs-grid">
                                <div style="padding:10px 14px;border-right:1px solid var(--border)">
                                    <div style="font-size:10px;color:var(--text-muted);margin-bottom:3px">AI Cost</div>
                                    <div id="ue-ai" style="font-size:13px;font-weight:600;color:var(--text-primary)">—</div>
                                    <div id="ue-ai-note" style="font-size:10px;color:var(--text-muted)">avg volume</div>
                                </div>
                                <div style="padding:10px 14px;border-right:1px solid var(--border)">
                                    <div style="font-size:10px;color:var(--text-muted);margin-bottom:3px">Stripe Fee</div>
                                    <div id="ue-stripe" style="font-size:13px;font-weight:600;color:var(--text-primary)">—</div>
                                    <div style="font-size:10px;color:var(--text-muted)">2.9% + $0.30</div>
                                </div>
                                <div style="padding:10px 14px;border-right:1px solid var(--border)">
                                    <div style="font-size:10px;color:var(--text-muted);margin-bottom:3px">Infra</div>
                                    <div id="ue-infra" style="font-size:13px;font-weight:600;color:var(--text-primary)">—</div>
                                    <div style="font-size:10px;color:var(--text-muted)">~$0.50/tenant/mo</div>
                                </div>
                                <div style="padding:10px 14px">
                                    <div style="font-size:10px;color:var(--text-muted);margin-bottom:3px">Tax Reserve</div>
                                    <div id="ue-tax" style="font-size:13px;font-weight:600;color:var(--text-primary)">—</div>
                                    <div style="font-size:10px;color:var(--text-muted)">~8% sales tax</div>
                                </div>
                            </div>
                            <div style="padding:8px 14px;background:var(--bg-surface);border-top:1px solid var(--border);font-size:11px;color:var(--text-muted)">
                                Based on avg volume: <span id="ue-vol-label" style="color:var(--text-secondary);font-weight:600">—</span> transactions/mo
                                · <span id="ue-cost-per-tx" style="color:var(--text-secondary)">—</span> cost/tx
                                · <span id="ue-margin-status" style="font-weight:600">—</span>
                            </div>
                        </div>

                        {{-- STRIPE LIVE PRICE --}}
                        <div class="wf-sep">Stripe — Live Price ID</div>
                        <div>
                            <div class="wf-lbl">Live Price ID <span style="font-weight:400;text-transform:none;letter-spacing:0">— from <a href="{{ $stripeDashBase }}prices" target="_blank" style="color:var(--accent-text);text-decoration:none">Stripe Prices →</a></span></div>
                            <div class="wf-verify-row">
                                <input type="text" name="stripe_flat_price_id" id="ef-stripe_flat_price_id"
                                       class="wf-input" style="font-family:monospace;font-size:12px" placeholder="price_live_1ABC...">
                                <button type="button" class="wp-btn-sm" onclick="verifyPricePanel('live')">Verify</button>
                            </div>
                            <div id="live-price-badge" class="wf-verify-badge"></div>
                            <div class="wf-hint">Used when billing mode is set to <strong>LIVE</strong></div>
                        </div>

                        {{-- STRIPE TEST PRICE --}}
                        <div class="wf-sep">Stripe — Test Price ID</div>
                        <div>
                            <div class="wf-lbl">Test Price ID <span style="font-weight:400;text-transform:none;letter-spacing:0">— from <a href="https://dashboard.stripe.com/test/prices" target="_blank" style="color:var(--accent-text);text-decoration:none">Stripe Test Prices →</a></span></div>
                            <div class="wf-verify-row">
                                <input type="text" name="stripe_test_price_id" id="ef-stripe_test_price_id"
                                       class="wf-input" style="font-family:monospace;font-size:12px" placeholder="price_test_1ABC...">
                                <button type="button" class="wp-btn-sm" onclick="verifyPricePanel('test')">Verify</button>
                            </div>
                            <div id="test-price-badge" class="wf-verify-badge"></div>
                            <div class="wf-hint">Used when billing mode is set to <strong>TEST</strong></div>
                        </div>

                        {{-- DISCOUNT / PROMO --}}
                        <div class="wf-sep">Discount &amp; Promotions</div>
                        <div class="wf-grid">
                            <div>
                                <div class="wf-lbl">Discount % <span style="font-weight:400;text-transform:none;letter-spacing:0">(display only)</span></div>
                                <input type="number" step="0.01" min="0" max="100" name="discount_pct" id="ef-discount_pct" class="wf-input" placeholder="20">
                                <div class="wf-hint">Shows crossed-out price on cards — use a Stripe coupon for actual discount</div>
                            </div>
                            <div>
                                <div class="wf-lbl">Promo Label</div>
                                <input name="promo_label" id="ef-promo_label" class="wf-input" placeholder="Launch pricing">
                            </div>
                            <div>
                                <div class="wf-lbl">Promo Expires</div>
                                <input type="date" name="promo_expires_at" id="ef-promo_expires_at" class="wf-input">
                                <div class="wf-hint">Badge auto-hides after this date</div>
                            </div>
                            <div>
                                <div class="wf-lbl">Stripe Coupon ID</div>
                                <div style="display:flex;gap:7px">
                                    <input type="text" name="stripe_coupon_id" id="ef-stripe_coupon_id"
                                           class="wf-input" style="font-family:monospace;font-size:12px" placeholder="LAUNCH50">
                                    <button type="button" class="wp-btn-sm" onclick="verifyCouponPanel()">Verify</button>
                                </div>
                                <div id="coupon-badge" class="wf-verify-badge"></div>
                                <div class="wf-hint">Applied at checkout — create in <a href="{{ $stripeDashBase }}coupons" target="_blank" style="color:var(--accent-text);text-decoration:none">Stripe Coupons →</a></div>
                            </div>
                        </div>

                        <input type="hidden" name="billing_mode" id="ef-billing_mode" value="test">

                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                            <button type="button" id="ep-visibility-btn" class="wp-btn wp-btn-out" style="font-size:12px" onclick="submitVisibilityToggle()">—</button>
                            <button type="submit" class="wp-btn wp-btn-gold">Save changes</button>
                        </div>
                    </form>

                    {{-- Visibility toggle form lives OUTSIDE ep-form to avoid nesting --}}
                    <form id="ep-visibility-form" method="POST" style="display:none">
                        @csrf
                        @method('POST')
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── ADD MODAL ─────────────────────────────────────────────────────── --}}
<div id="add-modal" class="wp-modal-bg">
<div class="wp-modal">
    <div class="flex items-center justify-between mb-5">
        <h2 class="font-bold text-sm" style="color:var(--text-primary)">Add Plan to Worker</h2>
        <button onclick="closeAdd()" style="background:none;border:none;font-size:20px;line-height:1;cursor:pointer;color:var(--text-muted)">×</button>
    </div>
    <form method="POST" action="{{ route('admin.pricing.store') }}">
        @csrf
        <input type="hidden" id="add-slug-val" name="worker_slug">

        <div style="margin-bottom:12px">
            <div class="wf-lbl">Worker <span style="color:#f87171">*</span></div>
            <div class="wf-sel-wrap">
                <select class="wf-select" id="add-worker-sel" onchange="onPickWorker(this)">
                    <option value="">— Select a worker —</option>
                    @foreach($registryWorkers as $w)
                    <option value="{{ $w->slug }}" data-name="{{ $w->name }}">{{ $w->name }}</option>
                    @endforeach
                    <option value="__new__">＋ Enter slug manually</option>
                </select>
            </div>
        </div>

        <div id="add-custom-slug" style="display:none;margin-bottom:12px">
            <div class="wf-lbl">Slug <span style="color:#f87171">*</span></div>
            <input id="add-slug-input" class="wf-input" style="font-family:monospace" placeholder="e.g. docfiler"
                   oninput="document.getElementById('add-slug-val').value=this.value.toLowerCase().replace(/\s+/g,'-')">
        </div>

        <div id="add-fields" style="display:none">
            <div class="wf-grid">
                <div class="wf-full">
                    <div class="wf-lbl">Display Name <span style="color:#f87171">*</span></div>
                    <input name="display_name" id="add-display-name" class="wf-input" placeholder="AVA — Starter" required>
                </div>
                <div class="wf-full">
                    <div class="wf-lbl">Plan Slug <span style="color:#f87171">*</span></div>
                    <input name="plan_slug" class="wf-input" style="font-family:monospace" placeholder="starter" required>
                    <div class="wf-hint">Lowercase — starter, pro, enterprise</div>
                </div>
                <div>
                    <div class="wf-lbl">Monthly Rate ($) <span style="color:#f87171">*</span></div>
                    <input type="number" step="0.01" name="monthly_flat_rate" class="wf-input" placeholder="49" required>
                </div>
                <div>
                    <div class="wf-lbl">Transaction Limit <span style="font-weight:400;color:var(--text-muted)">(0 = ∞)</span></div>
                    <input type="number" name="transaction_limit" class="wf-input" value="0">
                </div>
                <div>
                    <div class="wf-lbl">Free Trial Transactions</div>
                    <input type="number" name="free_transactions" class="wf-input" value="10">
                </div>
                <div>
                    <div class="wf-lbl">Sort Order</div>
                    <input type="number" name="sort_order" class="wf-input" value="10">
                </div>
                <div>
                    <div class="wf-lbl">Billing Mode</div>
                    <div class="wf-sel-wrap">
                        <select name="billing_mode" class="wf-select">
                            <option value="test">Test (development)</option>
                            <option value="live">Live (production)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <div class="wf-lbl">Worker Page URL</div>
                    <input name="worker_url" id="add-worker-url" class="wf-input" placeholder="/w/ava">
                </div>
            </div>

            <div style="margin-top:12px">
                <div class="wf-lbl">Test Price ID <span style="font-weight:400;text-transform:none;letter-spacing:0">(optional)</span></div>
                <input type="text" name="stripe_test_price_id" class="wf-input" style="font-family:monospace;font-size:12px" placeholder="price_test_1ABC...">
            </div>

            <div class="flex justify-end gap-2 mt-5">
                <button type="button" onclick="closeAdd()" class="wp-btn wp-btn-out">Cancel</button>
                <button type="submit" class="wp-btn wp-btn-gold">Add Plan</button>
            </div>
        </div>
    </form>
</div>
</div>

{{-- Embed plan data for JS hydration --}}
<script>
const PLANS = {
    @foreach($plans as $plan)
    {{ $plan->id }}: {
        id:                   {{ $plan->id }},
        worker_slug:          @json($plan->worker_slug),
        plan_slug:            @json($plan->plan_slug),
        display_name:         @json($plan->display_name ?? ''),
        tagline:              @json($plan->tagline ?? ''),
        transaction_label:    @json($plan->transaction_label ?? ''),
        worker_url:           @json($plan->worker_url ?? ''),
        sort_order:           {{ $plan->sort_order ?? 0 }},
        accent_color:         @json($plan->accent_color ?? '#f1d362'),
        free_transactions:    {{ $plan->free_transactions ?? 0 }},
        included_transactions: {{ $plan->included_transactions ?? 0 }},
        monthly_flat_rate:    {{ $plan->monthly_flat_rate ?? 0 }},
        transaction_limit:    {{ $plan->transaction_limit ?? 0 }},
        support_label:        @json($plan->support_label ?? ''),
        plan_highlights:      @json(implode("\n", json_decode($plan->plan_highlights ?? '[]', true))),
        is_trial_plan:          {{ $plan->is_trial_plan ? 'true' : 'false' }},
        trial_days:             {{ $plan->trial_days ?? 'null' }},
        billing_mode:           @json($plan->billing_mode ?? 'test'),
        ai_tier:                @json($plan->ai_tier ?? 'economy'),
        classify_model:         @json($plan->classify_model ?? 'claude-haiku-4-5-20251001'),
        draft_model:            @json($plan->draft_model ?? 'claude-haiku-4-5-20251001'),
        draft_model_threshold:  {{ $plan->draft_model_threshold ?? 0 }},
        stage_models:           @json(json_decode($plan->stage_models ?? 'null') ?? new stdClass),
        stripe_flat_price_id:   @json($plan->stripe_flat_price_id ?? ''),
        stripe_test_price_id: @json($plan->stripe_test_price_id ?? ''),
        stripe_coupon_id:     @json($plan->stripe_coupon_id ?? ''),
        discount_pct:         @json($plan->discount_pct ?? ''),
        promo_label:          @json($plan->promo_label ?? ''),
        promo_expires_at:     @json($plan->promo_expires_at ? date('Y-m-d', strtotime($plan->promo_expires_at)) : ''),
        active:               {{ $plan->active ? 'true' : 'false' }},
    },
    @endforeach
};

// AI stages per worker — sourced from WorkerContract::aiStages(), never hardcoded
const AI_STAGES = @json($aiStagesMap);

const _csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
const TOGGLE_BASE  = '{{ url("admin/pricing") }}';

let _activePlanId = null;

function selectPlan(id) {
    if (_activePlanId === id) return;
    _activePlanId = id;

    // Highlight card
    document.querySelectorAll('.pl-card').forEach(c => c.classList.remove('active'));
    document.getElementById('card-' + id)?.classList.add('active');

    const p = PLANS[id];
    if (!p) return;

    // Show panel
    document.getElementById('ep-empty').style.display = 'none';
    document.getElementById('ep-content').style.display = 'block';

    // On mobile, reveal the right column and scroll to it
    const rightCol = document.querySelector('.pr-right');
    if (rightCol && window.innerWidth <= 768) {
        rightCol.classList.add('mobile-open');
        setTimeout(() => rightCol.scrollIntoView({ behavior: 'smooth', block: 'start' }), 50);
    }

    // Header
    document.getElementById('ep-title').textContent = p.display_name || p.worker_slug.toUpperCase();
    document.getElementById('ep-slugs').textContent = p.worker_slug + ' · ' + p.plan_slug;

    // Mode toggle
    buildModeToggle(id, p.billing_mode);

    // Form action
    document.getElementById('ep-form').action = TOGGLE_BASE + '/' + id;

    // Visibility toggle form (outside ep-form to avoid invalid nesting)
    const visForm = document.getElementById('ep-visibility-form');
    if (visForm) visForm.action = TOGGLE_BASE + '/' + id + '/toggle';
    const vBtn = document.getElementById('ep-visibility-btn');
    if (vBtn) {
        vBtn.textContent = p.active ? 'Hide from pricing page' : 'Go live on pricing page';
        vBtn.className   = 'wp-btn ' + (p.active ? 'wp-btn-red' : 'wp-btn-out');
    }

    // Populate fields
    const fields = ['display_name','tagline','transaction_label','worker_url','sort_order',
                    'free_transactions','monthly_flat_rate','transaction_limit','support_label',
                    'plan_highlights','stripe_flat_price_id','stripe_test_price_id',
                    'stripe_coupon_id','discount_pct','promo_label','promo_expires_at',
                    'draft_model_threshold'];
    fields.forEach(f => {
        const el = document.getElementById('ef-' + f);
        if (el) el.value = p[f] ?? '';
    });

    // AI tier selects
    setSelectValue('ef-ai_tier', p.ai_tier || 'economy');
    renderAiStageSelectors(p.worker_slug, p.stage_models || {});
    updateCostPreview(p);
    updateUnitEconomics(p);

    // Color
    document.getElementById('ef-accent_color').value = p.accent_color;
    document.getElementById('ef-cp').value = p.accent_color;

    // Hidden billing_mode
    document.getElementById('ef-billing_mode').value = p.billing_mode;

    // Trial plan toggle
    const trialCb = document.getElementById('ef-is_trial_plan');
    if (trialCb) trialCb.checked = !!p.is_trial_plan;
    const trialDaysEl = document.getElementById('ef-trial_days');
    if (trialDaysEl) trialDaysEl.value = p.trial_days ?? '';
    onTrialPlanToggle(!!p.is_trial_plan);

    // Clear verify badges
    ['live-price-badge','test-price-badge','coupon-badge'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.style.display='none'; el.className='wf-verify-badge'; }
    });
}

function buildModeToggle(planId, currentMode) {
    const wrap = document.getElementById('ep-mode-toggle');
    wrap.innerHTML = '';

    const testBtn = document.createElement('button');
    testBtn.type = 'button';
    testBtn.textContent = '○ TEST';
    testBtn.className = currentMode === 'test' ? 'on-test' : '';
    testBtn.onclick = () => submitModeChange(planId, 'test');

    const liveBtn = document.createElement('button');
    liveBtn.type = 'button';
    liveBtn.textContent = '● LIVE';
    liveBtn.className = currentMode === 'live' ? 'on-live' : '';
    liveBtn.onclick = () => submitModeChange(planId, 'live');

    wrap.appendChild(testBtn);
    wrap.appendChild(liveBtn);
}

function submitModeChange(planId, mode) {
    const form = document.getElementById('ep-toggle-form');
    form.action = TOGGLE_BASE + '/' + planId + '/billing-mode';

    let inp = form.querySelector('input[name="billing_mode"]');
    if (!inp) { inp = document.createElement('input'); inp.type='hidden'; inp.name='billing_mode'; form.appendChild(inp); }
    inp.value = mode;

    if (mode === 'live' && !confirm('Switch ' + (PLANS[planId]?.worker_slug?.toUpperCase() || '') + ' to LIVE billing? Real Stripe charges will apply for all plans under this worker.')) return;
    form.submit();
}

// ── Verify helpers ──────────────────────────────────────────────────────

function verifyPricePanel(mode) {
    const fieldId = mode === 'live' ? 'ef-stripe_flat_price_id' : 'ef-stripe_test_price_id';
    const badgeId = mode === 'live' ? 'live-price-badge' : 'test-price-badge';
    const priceId = document.getElementById(fieldId)?.value.trim();
    if (!priceId) return;

    const badge = document.getElementById(badgeId);
    badge.textContent = 'Checking…';
    badge.className   = 'wf-verify-badge';
    badge.style.display = 'inline-block';

    fetch('{{ route("admin.pricing.verify-price") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':_csrf},
        body: JSON.stringify({price_id: priceId, mode: mode})
    }).then(r => r.json()).then(d => {
        if (d.error) {
            badge.textContent = '✗ ' + d.error;
            badge.className   = 'wf-verify-badge wf-verify-err';
        } else {
            badge.textContent = '✓ $' + d.amount.toFixed(2) + '/' + d.interval + ' · ' + d.product + (d.active ? '' : ' (inactive)');
            badge.className   = 'wf-verify-badge wf-verify-ok';
        }
    }).catch(() => {
        badge.textContent = '✗ Request failed';
        badge.className   = 'wf-verify-badge wf-verify-err';
    });
}

function verifyCouponPanel() {
    const cid   = document.getElementById('ef-stripe_coupon_id')?.value.trim();
    const badge = document.getElementById('coupon-badge');
    if (!cid) return;

    badge.textContent = 'Checking…';
    badge.className   = 'wf-verify-badge';
    badge.style.display = 'inline-block';

    fetch('{{ route("admin.pricing.verify-coupon") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':_csrf},
        body: JSON.stringify({coupon_id: cid})
    }).then(r => r.json()).then(d => {
        if (d.error) {
            badge.textContent = '✗ ' + d.error;
            badge.className   = 'wf-verify-badge wf-verify-err';
        } else {
            badge.textContent = '✓ ' + d.summary + ' · ' + d.duration + (d.valid ? '' : ' (expired)');
            badge.className   = 'wf-verify-badge wf-verify-ok';
        }
    }).catch(() => {
        badge.textContent = '✗ Request failed';
        badge.className   = 'wf-verify-badge wf-verify-err';
    });
}

// ── Add modal ──────────────────────────────────────────────────────────

function openAdd()  { document.getElementById('add-modal').classList.add('open') }
function closeAdd() { document.getElementById('add-modal').classList.remove('open') }

function onPickWorker(sel) {
    const fields    = document.getElementById('add-fields');
    const customRow = document.getElementById('add-custom-slug');
    const slugVal   = document.getElementById('add-slug-val');
    const displayEl = document.getElementById('add-display-name');
    const urlEl     = document.getElementById('add-worker-url');
    const opt       = sel.options[sel.selectedIndex];

    if (!sel.value) { fields.style.display='none'; customRow.style.display='none'; return; }
    fields.style.display = 'block';

    if (sel.value === '__new__') {
        customRow.style.display = 'block';
        slugVal.value = '';
    } else {
        customRow.style.display = 'none';
        slugVal.value = sel.value;
        if (displayEl && opt.dataset.name) displayEl.value = opt.dataset.name;
        if (urlEl) urlEl.value = '/w/' + sel.value;
    }
}

document.querySelector('#add-modal').addEventListener('click', function(e) {
    if (e.target === this) closeAdd();
});

function closeMobilePanel() {
    const rightCol = document.querySelector('.pr-right');
    if (rightCol) rightCol.classList.remove('mobile-open');
    _activePlanId = null;
    document.querySelectorAll('.pl-card').forEach(c => c.classList.remove('active'));
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function renderAiStageSelectors(workerSlug, stageModels) {
    const container = document.getElementById('ef-stage-models-container');
    if (!container) return;
    container.innerHTML = '';

    const stages = AI_STAGES[workerSlug] || [];
    if (!stages.length) {
        container.innerHTML = '<div style="grid-column:1/-1;color:var(--text-muted);font-size:12px;padding:4px 0">No AI stages defined for this worker</div>';
        return;
    }

    const modelOptions = [
        { value: 'claude-haiku-4-5-20251001', label: 'Haiku 4.5 — $0.80/M · fast' },
        { value: 'claude-sonnet-4-6',         label: 'Sonnet 4.6 — $3.00/M · balanced' },
        { value: 'claude-opus-4-7',           label: 'Opus 4.7 — $15.00/M · powerful' },
    ];

    stages.forEach(stage => {
        const key     = stage.key;
        const label   = stage.label || key;
        const current = stageModels[key] || 'claude-haiku-4-5-20251001';

        const wrap = document.createElement('div');

        const lbl = document.createElement('div');
        lbl.className = 'wf-lbl';
        lbl.textContent = label;

        const selWrap = document.createElement('div');
        selWrap.className = 'wf-sel-wrap';

        const sel = document.createElement('select');
        sel.name      = 'stage_models[' + key + ']';
        sel.id        = 'ef-stage_models_' + key;
        sel.className = 'wf-select';
        sel.onchange  = () => { updateCostPreview(null); refreshUEFromForm(); };

        modelOptions.forEach(opt => {
            const o = document.createElement('option');
            o.value       = opt.value;
            o.textContent = opt.label;
            if (opt.value === current) o.selected = true;
            sel.appendChild(o);
        });

        const hint = document.createElement('div');
        hint.className   = 'wf-hint';
        hint.textContent = stage.job_class ? stage.job_class.replace(/Job$/, '') + 'Job' : '';

        selWrap.appendChild(sel);
        wrap.appendChild(lbl);
        wrap.appendChild(selWrap);
        wrap.appendChild(hint);
        container.appendChild(wrap);
    });
}

function getStageModelsFromForm() {
    const container = document.getElementById('ef-stage-models-container');
    if (!container) return {};
    const result = {};
    container.querySelectorAll('select[name^="stage_models["]').forEach(sel => {
        const m = sel.name.match(/stage_models\[(.+)\]/);
        if (m) result[m[1]] = sel.value;
    });
    return result;
}

function refreshUEFromForm() {
    const get = id => document.getElementById(id)?.value ?? '';
    const sm  = getStageModelsFromForm();
    updateUnitEconomics({
        monthly_flat_rate:      get('ef-monthly_flat_rate'),
        transaction_limit:      get('ef-transaction_limit'),
        included_transactions:  get('ef-transaction_limit') || 200,
        stage_models:           sm,
        draft_model_threshold:  get('ef-draft_model_threshold'),
    });
}

function onTrialPlanToggle(checked) {
    const wrap = document.getElementById('ef-trial-days-wrap');
    if (wrap) wrap.style.opacity = checked ? '1' : '0.4';
}

function submitVisibilityToggle() {
    const form = document.getElementById('ep-visibility-form');
    if (form && form.action) form.submit();
}

function setSelectValue(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    for (let i = 0; i < el.options.length; i++) {
        if (el.options[i].value === value) { el.selectedIndex = i; return; }
    }
}

function onTierChange(tier) {
    const presets = {
        economy:  { default: 'claude-haiku-4-5-20251001', draft: 'claude-haiku-4-5-20251001' },
        standard: { default: 'claude-haiku-4-5-20251001', draft: 'claude-sonnet-4-6' },
        premium:  { default: 'claude-sonnet-4-6',         draft: 'claude-sonnet-4-6' },
    };
    const p = presets[tier];
    if (!p) return;

    // Apply preset to all rendered stage selects
    const container = document.getElementById('ef-stage-models-container');
    if (container) {
        container.querySelectorAll('select[name^="stage_models["]').forEach(sel => {
            const m = sel.name.match(/stage_models\[(.+)\]/);
            const key = m ? m[1] : '';
            setSelectValue(sel.id, key === 'draft' ? p.draft : p.default);
        });
    }

    const threshEl = document.getElementById('ef-draft_model_threshold');
    if (tier === 'standard' && threshEl && !threshEl.value) threshEl.value = 500;
    if (tier !== 'standard' && threshEl) threshEl.value = '';
    updateCostPreview(null);
    refreshUEFromForm();
}

function stageModelCostPerEmail(stageModels, threshold, vol) {
    const modelCost = {
        'claude-haiku-4-5-20251001': 0.00025,
        'claude-sonnet-4-6':         0.0030,
        'claude-opus-4-7':           0.0180,
    };
    // Token budget per stage key
    const stageToks = { read: 400, classify: 500, memory: 300, template: 200, draft: 1200 };
    const fallback  = modelCost['claude-haiku-4-5-20251001'];

    const sm = stageModels || {};

    function costForModels(overrideDraft) {
        let c = 0;
        Object.entries(stageToks).forEach(([key, toks]) => {
            const model = (key === 'draft' && overrideDraft) ? overrideDraft : (sm[key] || 'claude-haiku-4-5-20251001');
            c += (modelCost[model] ?? fallback) * toks / 1000;
        });
        return c;
    }

    const draftModel   = sm['draft'] || 'claude-haiku-4-5-20251001';
    const downgradeModel = sm['classify'] || sm['read'] || 'claude-haiku-4-5-20251001';

    const thresh = parseInt(threshold) || 0;
    if (thresh > 0 && vol > thresh) {
        const above = vol - thresh;
        return (
            (thresh * costForModels(null)) +
            (above  * costForModels(downgradeModel))
        ) / vol;
    }
    return costForModels(null);
}

function updateUnitEconomics(plan) {
    const revenue = parseFloat(plan.monthly_flat_rate) || 0;
    const limit   = parseInt(plan.transaction_limit)   || 0;
    const vol     = limit > 0 ? limit : (parseInt(plan.included_transactions) || 200);

    const costPerEmail = stageModelCostPerEmail(plan.stage_models, plan.draft_model_threshold, vol);

    const aiCost    = costPerEmail * vol;
    const stripeFee = revenue > 0 ? (revenue * 0.029 + 0.30) : 0;
    const infra     = 0.50;
    const taxRes    = revenue * 0.08; // ~8% sales tax reserve
    const totalCost = aiCost + stripeFee + infra + taxRes;
    const gp        = revenue - totalCost;
    const gpPct     = revenue > 0 ? (gp / revenue * 100) : 0;

    const fmt = v => v >= 0 ? '$' + v.toFixed(2) : '−$' + Math.abs(v).toFixed(2);
    const pctColor = gpPct >= 60 ? 'var(--badge-fast-text)' : gpPct >= 40 ? 'var(--badge-balanced-text)' : '#f87171';
    const marginLabel = gpPct >= 60 ? '✓ On target (50–70%+)' : gpPct >= 40 ? '⚠ Below target' : '✗ Margin too thin';

    const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    const setStyle = (id, k, v) => { const el = document.getElementById(id); if (el) el.style[k] = v; };

    setText('ue-revenue',   revenue > 0 ? fmt(revenue) : 'Custom');
    setText('ue-gp',        revenue > 0 ? fmt(gp)      : '—');
    setText('ue-gp-pct',    revenue > 0 ? gpPct.toFixed(1) + '% gross margin' : 'Custom pricing');
    setStyle('ue-gp', 'color', revenue > 0 ? pctColor : 'var(--text-muted)');

    setText('ue-ai',       revenue > 0 ? fmt(aiCost)    : '—');
    setText('ue-ai-note',  vol + ' tx @ ' + '$' + costPerEmail.toFixed(4) + '/tx');
    setText('ue-stripe',   revenue > 0 ? fmt(stripeFee) : '—');
    setText('ue-infra',    fmt(infra));
    setText('ue-tax',      revenue > 0 ? fmt(taxRes)    : '—');

    setText('ue-vol-label',     vol);
    setText('ue-cost-per-tx',   '$' + costPerEmail.toFixed(4));
    setText('ue-margin-status', revenue > 0 ? marginLabel : 'Set monthly rate to see margin');
    setStyle('ue-margin-status', 'color', pctColor);
}

function updateCostPreview(_unused) {
    const el = document.getElementById('ai-tier-cost');
    if (!el) return;

    const sm        = getStageModelsFromForm();
    const threshold = document.getElementById('ef-draft_model_threshold')?.value || 0;
    const costPerEmail = stageModelCostPerEmail(sm, threshold, 200);
    const costStr      = '$' + costPerEmail.toFixed(4);
    const threshNote   = parseInt(threshold) > 0 ? ` · auto-downgrade after ${threshold} emails/mo` : '';

    el.textContent = `~${costStr} per email${threshNote}`;
    el.style.color = costPerEmail < 0.02 ? 'var(--badge-fast-text)' : costPerEmail < 0.04 ? 'var(--badge-balanced-text)' : 'var(--badge-powerful-text)';
}

// Auto-open the plan that was just saved (via ?editing=ID)
const editingId = {{ request('editing', 'null') }};
if (editingId && PLANS[editingId]) {
    selectPlan(editingId);
}
</script>

</x-app-layout>

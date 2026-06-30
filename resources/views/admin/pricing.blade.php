<x-app-layout title="Worker Pricing">

<style>
.wp-card    { background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:14px }
.wp-stripe  { height:2px }
.wp-row     { padding:16px 20px;display:flex;align-items:center;gap:16px }
.wp-dot     { width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.wp-info    { flex:1;min-width:0 }
.wp-name    { font-size:13px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:8px;flex-wrap:wrap }
.wp-tag     { font-size:10px;font-weight:700;font-family:monospace;padding:1px 7px;border-radius:5px;background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border) }
.wp-tagline { font-size:12px;color:var(--text-muted);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis }
.wp-price   { text-align:right;flex-shrink:0 }
.wp-price-main { font-size:15px;font-weight:700;color:var(--text-primary) }
.wp-price-sub  { font-size:11px;color:var(--text-muted) }
.wp-actions { display:flex;gap:8px;flex-shrink:0 }
.wp-live    { font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;background:rgba(34,197,94,.1);color:#4ade80;border:1px solid rgba(34,197,94,.2) }
.wp-hidden  { font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;background:rgba(156,163,175,.08);color:var(--text-muted);border:1px solid var(--border) }
.wp-promo   { font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;background:rgba(168,85,247,.12);color:#c084fc;border:1px solid rgba(168,85,247,.25) }

.wp-btn        { font-size:12px;font-weight:600;padding:6px 12px;border-radius:8px;cursor:pointer;transition:all .15s;white-space:nowrap }
.wp-btn-gold   { background:var(--accent);color:#12100a;border:none }
.wp-btn-gold:hover { opacity:.88 }
.wp-btn-out    { background:transparent;border:1px solid var(--border);color:var(--text-secondary) }
.wp-btn-out:hover  { border-color:var(--accent);color:var(--text-primary) }
.wp-btn-red    { background:transparent;border:1px solid rgba(239,68,68,.25);color:#f87171 }
.wp-btn-red:hover  { background:rgba(239,68,68,.08) }
.wp-btn-sm     { font-size:11px;padding:5px 10px;border-radius:7px;cursor:pointer;border:1px solid var(--border);background:var(--bg-raised);color:var(--text-secondary);white-space:nowrap;transition:all .15s }
.wp-btn-sm:hover   { border-color:var(--accent);color:var(--text-primary) }

/* modal */
.wp-modal-bg  { display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999;align-items:center;justify-content:center }
.wp-modal-bg.open { display:flex }
.wp-modal     { background:var(--bg-card);border:1px solid var(--border);border-radius:18px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;padding:24px }

/* form */
.wf-grid  { display:grid;grid-template-columns:1fr 1fr;gap:12px }
.wf-full  { grid-column:1/-1 }
.wf-lbl   { font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px }
.wf-input,.wf-select {
    width:100%;background:var(--bg-raised);border:1px solid var(--border);
    border-radius:9px;padding:8px 11px;font-size:13px;color:var(--text-primary);
    outline:none;transition:border .15s;appearance:none;-webkit-appearance:none;
}
.wf-input:focus,.wf-select:focus { border-color:var(--accent) }
.wf-hint  { font-size:11px;color:var(--text-muted);margin-top:3px }
.wf-sep   { font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;
            color:var(--text-muted);margin:16px 0 10px;padding-top:16px;border-top:1px solid var(--border) }
.wf-sel-wrap { position:relative }
.wf-sel-wrap::after { content:'';position:absolute;right:11px;top:50%;transform:translateY(-50%);
    width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;
    border-top:5px solid var(--text-muted);pointer-events:none }
.wf-color-row { display:flex;gap:8px;align-items:center }
.wf-swatch { width:34px;height:34px;border-radius:8px;border:1px solid var(--border);cursor:pointer;padding:2px;background:none;flex-shrink:0 }

/* verify row */
.wf-verify-row { display:flex;gap:8px;align-items:flex-start }
.wf-verify-row .wf-input { flex:1 }
.wf-verify-badge { font-size:11px;padding:4px 9px;border-radius:6px;margin-top:4px;display:none }
.wf-verify-ok  { background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80 }
.wf-verify-err { background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171 }
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
        <p class="text-sm mt-0.5" style="color:var(--text-muted)">
            Create products &amp; prices in Stripe, then paste the Price ID here to link them.
            @if($stripeTestMode)
            <span style="font-size:10px;font-weight:700;padding:1px 7px;border-radius:5px;background:rgba(245,158,11,.15);color:#fbbf24;border:1px solid rgba(245,158,11,.3);margin-left:6px;vertical-align:middle">TEST MODE</span>
            @endif
        </p>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <a href="{{ $stripeDashBase }}products" target="_blank"
           style="font-size:12px;padding:6px 12px;border-radius:8px;border:1px solid var(--border);color:var(--text-secondary);text-decoration:none;display:flex;align-items:center;gap:5px">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.6 7.2c0-.8.7-1.1 1.7-1.1 1.5 0 3.5.5 4.8 1.2V3.5C18.8 2.9 17 2.5 15.3 2.5c-3.5 0-5.9 1.8-5.9 4.9 0 4.8 6.6 4 6.6 6.1 0 .9-.8 1.2-1.9 1.2-1.7 0-3.8-.7-5.4-1.6v3.9c1.8.8 3.7 1.2 5.4 1.2 3.6 0 6.1-1.8 6.1-4.9-.1-5.1-6.7-4.2-6.6-6.1z" fill="#635BFF"/></svg>
            Stripe Dashboard →
        </a>
        <button onclick="openAdd()" class="wp-btn wp-btn-gold" style="font-size:13px;padding:8px 16px">+ Add Plan</button>
    </div>
</div>

@forelse($plans as $plan)
@php
    $accent = $plan->accent_color ?? '#f1d362';
    $hex = ltrim($accent,'#');
    $r=hexdec(substr($hex,0,2));$g=hexdec(substr($hex,2,2));$b=hexdec(substr($hex,4,2));
    $hasPromo = !empty($plan->discount_pct) || !empty($plan->promo_label);
    $effectiveRate = $plan->monthly_flat_rate > 0 && !empty($plan->discount_pct)
        ? $plan->monthly_flat_rate * (1 - $plan->discount_pct / 100)
        : $plan->monthly_flat_rate;
@endphp
<div class="wp-card">
    <div class="wp-stripe" style="background:linear-gradient(90deg,transparent,{{ $accent }},transparent)"></div>
    <div class="wp-row">
        <div class="wp-dot" style="background:rgba({{ $r }},{{ $g }},{{ $b }},.15);border:1px solid rgba({{ $r }},{{ $g }},{{ $b }},.3)">
            <div style="width:10px;height:10px;border-radius:50%;background:{{ $accent }}"></div>
        </div>
        <div class="wp-info">
            <div class="wp-name">
                {{ $plan->display_name ?: strtoupper($plan->worker_slug) }}
                <span class="wp-tag">{{ $plan->worker_slug }}</span>
                @if($plan->plan_slug)<span class="wp-tag" style="color:var(--accent-text)">{{ $plan->plan_slug }}</span>@endif
                <span class="{{ $plan->active ? 'wp-live' : 'wp-hidden' }}">{{ $plan->active ? 'Live' : 'Hidden' }}</span>
                @if($hasPromo)<span class="wp-promo">{{ $plan->promo_label ?: round($plan->discount_pct).'% off' }}</span>@endif
            </div>
            @if($plan->tagline)<div class="wp-tagline">{{ $plan->tagline }}</div>@endif
            <div style="font-size:10px;color:var(--text-muted);margin-top:4px;font-family:monospace;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                @if($plan->stripe_flat_price_id)
                    <span style="color:#635BFF">▸</span>
                    <a href="{{ $stripeDashBase }}prices/{{ $plan->stripe_flat_price_id }}" target="_blank"
                       style="color:var(--text-muted);text-decoration:none">{{ $plan->stripe_flat_price_id }}</a>
                    @if($plan->stripe_coupon_id)
                        · <span style="color:#c084fc">coupon: {{ $plan->stripe_coupon_id }}</span>
                    @endif
                @else
                    <span style="color:#f87171">⚠ No Stripe price linked — click Edit to add</span>
                @endif
            </div>
        </div>
        <div class="wp-price">
            <div class="wp-price-main">
                @if($plan->monthly_flat_rate > 0)
                    @if(!empty($plan->discount_pct))
                        <span style="text-decoration:line-through;font-size:12px;color:var(--text-muted);font-weight:400">${{ number_format($plan->monthly_flat_rate, 0) }}</span>
                        ${{ number_format($effectiveRate, 0) }}<span style="font-weight:400;font-size:11px;color:var(--text-muted)">/mo</span>
                    @else
                        ${{ number_format($plan->monthly_flat_rate, 0) }}<span style="font-weight:400;font-size:11px;color:var(--text-muted)">/mo</span>
                    @endif
                @else
                    <span style="color:var(--text-muted);font-size:13px">Custom</span>
                @endif
            </div>
            <div class="wp-price-sub">
                @if($plan->transaction_limit) {{ number_format($plan->transaction_limit) }} emails/mo
                @else Unlimited
                @endif
            </div>
        </div>
        <div class="wp-actions">
            <button onclick="openEdit({{ $plan->id }})" class="wp-btn wp-btn-out">Edit</button>
            <form method="POST" action="{{ route('admin.pricing.toggle', $plan->id) }}" onsubmit="return confirm('{{ $plan->active ? 'Hide from pricing page?' : 'Go live on pricing page?' }}')">
                @csrf
                <button type="submit" class="wp-btn {{ $plan->active ? 'wp-btn-red' : 'wp-btn-out' }}">{{ $plan->active ? 'Hide' : 'Go Live' }}</button>
            </form>
        </div>
    </div>
</div>

{{-- EDIT MODAL --}}
<div id="edit-{{ $plan->id }}" class="wp-modal-bg">
<div class="wp-modal">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-sm" style="color:var(--text-primary)">Edit — {{ $plan->display_name ?: $plan->worker_slug }}</h2>
        <button onclick="closeEdit({{ $plan->id }})" style="background:none;border:none;font-size:20px;line-height:1;cursor:pointer;color:var(--text-muted)">×</button>
    </div>
    <form method="POST" action="{{ route('admin.pricing.update', $plan->id) }}">
        @csrf @method('PUT')
        <div class="wf-grid">
            <div class="wf-full">
                <div class="wf-lbl">Display Name</div>
                <input name="display_name" class="wf-input" value="{{ $plan->display_name }}" placeholder="AVA — Renewal Coordinator">
            </div>
            <div class="wf-full">
                <div class="wf-lbl">Tagline</div>
                <input name="tagline" class="wf-input" value="{{ $plan->tagline }}" placeholder="Handles renewal emails so your coordinator focuses on exceptions">
            </div>
            <div class="wf-full">
                <div class="wf-lbl">Transaction Definition</div>
                <input name="transaction_label" class="wf-input" value="{{ $plan->transaction_label }}" placeholder="one renewal email processed end-to-end">
                <div class="wf-hint">Shown as "1 transaction = ..." on the public page.</div>
            </div>
            <div>
                <div class="wf-lbl">Worker Page URL</div>
                <input name="worker_url" class="wf-input" value="{{ $plan->worker_url }}" placeholder="/w/ava">
            </div>
            <div>
                <div class="wf-lbl">Sort Order</div>
                <input type="number" name="sort_order" class="wf-input" value="{{ $plan->sort_order }}">
            </div>
            <div class="wf-full">
                <div class="wf-lbl">Accent Color</div>
                <div class="wf-color-row">
                    <input type="color" id="cp-{{ $plan->id }}" value="{{ $plan->accent_color ?? '#f1d362' }}" class="wf-swatch"
                           oninput="document.getElementById('ch-{{ $plan->id }}').value=this.value">
                    <input type="text" id="ch-{{ $plan->id }}" name="accent_color" class="wf-input" value="{{ $plan->accent_color ?? '#f1d362' }}"
                           style="font-family:monospace" oninput="document.getElementById('cp-{{ $plan->id }}').value=this.value">
                </div>
            </div>
        </div>

        <div class="wf-sep">Pricing</div>
        <div class="wf-grid">
            <div>
                <div class="wf-lbl">Free Trial Emails</div>
                <input type="number" name="free_transactions" class="wf-input" value="{{ $plan->free_transactions }}">
            </div>
            <div>
                <div class="wf-lbl">Monthly Flat Rate ($)</div>
                <input type="number" step="0.01" name="monthly_flat_rate" class="wf-input" value="{{ $plan->monthly_flat_rate }}">
                <div class="wf-hint">Display price — must match what the linked Stripe price charges</div>
            </div>
            <div>
                <div class="wf-lbl">Email Limit / Mo <span style="color:var(--text-muted);font-weight:400">(0 = unlimited)</span></div>
                <input type="number" name="transaction_limit" class="wf-input" value="{{ $plan->transaction_limit ?? 0 }}">
                <div class="wf-hint">Enforced by PolicyEngine</div>
            </div>
            <div>
                <div class="wf-lbl">Support Label</div>
                <input name="support_label" class="wf-input" value="{{ $plan->support_label }}" placeholder="Email support">
            </div>
        </div>

        <div class="wf-sep">Plan Highlights</div>
        <div style="margin-bottom:14px">
            <textarea name="plan_highlights" class="wf-input" rows="5"
                      style="resize:vertical;font-size:12px;line-height:1.6"
                      placeholder="One highlight per line&#10;100 emails processed per month&#10;Full 8-stage pipeline">{{ implode("\n", json_decode($plan->plan_highlights ?? '[]', true)) }}</textarea>
            <div class="wf-hint">One bullet per line — shown on plan cards and checkout. 4–6 lines ideal.</div>
        </div>

        {{-- STRIPE LINK --}}
        <div class="wf-sep">Stripe Link</div>
        <div style="margin-bottom:12px">
            <div class="wf-lbl">Price ID <span style="font-weight:400;text-transform:none;letter-spacing:0">— copy from <a href="{{ $stripeDashBase }}prices" target="_blank" style="color:var(--accent-text);text-decoration:none">Stripe Prices →</a></span></div>
            <div class="wf-verify-row">
                <input type="text" name="stripe_flat_price_id" id="price-id-{{ $plan->id }}"
                       class="wf-input" style="font-family:monospace;font-size:12px"
                       value="{{ $plan->stripe_flat_price_id }}" placeholder="price_1ABC...">
                <button type="button" class="wp-btn-sm" onclick="verifyPrice({{ $plan->id }})">Verify</button>
            </div>
            <div id="price-badge-{{ $plan->id }}" class="wf-verify-badge"></div>
            <div class="wf-hint">Paste the <code>price_...</code> ID from your Stripe product. The flat rate above must match what Stripe will charge.</div>
        </div>

        {{-- DISCOUNT / PROMO --}}
        <div class="wf-sep">Discount &amp; Promotions</div>
        <div class="wf-grid">
            <div>
                <div class="wf-lbl">Discount % <span style="font-weight:400;text-transform:none;letter-spacing:0">(display only)</span></div>
                <input type="number" step="0.01" min="0" max="100" name="discount_pct"
                       class="wf-input" value="{{ $plan->discount_pct }}" placeholder="20">
                <div class="wf-hint">Shows crossed-out original price on plan cards. Does not affect Stripe charge — use a coupon for that.</div>
            </div>
            <div>
                <div class="wf-lbl">Promo Label</div>
                <input name="promo_label" class="wf-input" value="{{ $plan->promo_label }}" placeholder="Launch pricing">
                <div class="wf-hint">Badge shown on the plan card, e.g. "Launch pricing" or "50% off"</div>
            </div>
            <div>
                <div class="wf-lbl">Promo Expires</div>
                <input type="date" name="promo_expires_at" class="wf-input"
                       value="{{ $plan->promo_expires_at ? date('Y-m-d', strtotime($plan->promo_expires_at)) : '' }}">
                <div class="wf-hint">Badge auto-hides after this date (leave blank = permanent)</div>
            </div>
            <div>
                <div class="wf-lbl">Stripe Coupon ID</div>
                <div class="wf-verify-row" style="flex-direction:column;gap:6px">
                    <div style="display:flex;gap:8px;width:100%">
                        <input type="text" name="stripe_coupon_id" id="coupon-id-{{ $plan->id }}"
                               class="wf-input" style="font-family:monospace;font-size:12px"
                               value="{{ $plan->stripe_coupon_id }}" placeholder="LAUNCH50">
                        <button type="button" class="wp-btn-sm" onclick="verifyCoupon({{ $plan->id }})">Verify</button>
                    </div>
                    <div id="coupon-badge-{{ $plan->id }}" class="wf-verify-badge"></div>
                </div>
                <div class="wf-hint">Applied at Stripe checkout — create in <a href="{{ $stripeDashBase }}coupons" target="_blank" style="color:var(--accent-text);text-decoration:none">Stripe Coupons →</a></div>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-5">
            <button type="button" onclick="closeEdit({{ $plan->id }})" class="wp-btn wp-btn-out">Cancel</button>
            <button type="submit" class="wp-btn wp-btn-gold" style="font-size:13px;padding:8px 18px">Save</button>
        </div>
    </form>
</div>
</div>
@empty
<div style="text-align:center;padding:56px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px">
    <p style="color:var(--text-muted);font-size:14px;margin-bottom:16px">No workers set up yet.</p>
    <button onclick="openAdd()" class="wp-btn wp-btn-gold" style="font-size:13px;padding:8px 18px">+ Add First Worker</button>
</div>
@endforelse

{{-- ADD MODAL --}}
<div id="add-modal" class="wp-modal-bg">
<div class="wp-modal">
    <div class="flex items-center justify-between mb-5">
        <h2 class="font-bold text-sm" style="color:var(--text-primary)">Add Worker to Pricing</h2>
        <button onclick="closeAdd()" style="background:none;border:none;font-size:20px;line-height:1;cursor:pointer;color:var(--text-muted)">×</button>
    </div>
    <form method="POST" action="{{ route('admin.pricing.store') }}">
        @csrf
        <input type="hidden" id="add-slug-val" name="worker_slug">

        <div style="margin-bottom:14px">
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

        <div id="add-custom-slug" style="display:none;margin-bottom:14px">
            <div class="wf-lbl">Slug <span style="color:#f87171">*</span></div>
            <input id="add-slug-input" class="wf-input" style="font-family:monospace" placeholder="e.g. docfiler"
                   oninput="document.getElementById('add-slug-val').value=this.value.toLowerCase().replace(/\s+/g,'-')">
        </div>

        <div id="add-fields" style="display:none">
            <div class="wf-sep" style="border-top:none;padding-top:0;margin-top:0">Card Copy</div>
            <div class="wf-grid">
                <div class="wf-full">
                    <div class="wf-lbl">Display Name <span style="color:#f87171">*</span></div>
                    <input name="display_name" id="add-display-name" class="wf-input" placeholder="AVA — Starter" required>
                </div>
                <div class="wf-full">
                    <div class="wf-lbl">Plan Slug <span style="color:#f87171">*</span></div>
                    <input name="plan_slug" class="wf-input" style="font-family:monospace" placeholder="starter" required>
                    <div class="wf-hint">Lowercase — e.g. starter, pro, enterprise</div>
                </div>
                <div class="wf-full">
                    <div class="wf-lbl">Tagline</div>
                    <input name="tagline" class="wf-input" placeholder="Handles renewal emails end-to-end">
                </div>
                <div>
                    <div class="wf-lbl">Worker Page URL</div>
                    <input name="worker_url" id="add-worker-url" class="wf-input" placeholder="/w/ava">
                </div>
                <div>
                    <div class="wf-lbl">Accent Color</div>
                    <div class="wf-color-row">
                        <input type="color" id="add-cp" value="#f1d362" class="wf-swatch" oninput="document.getElementById('add-ch').value=this.value">
                        <input type="text" id="add-ch" name="accent_color" class="wf-input" value="#f1d362" style="font-family:monospace" oninput="document.getElementById('add-cp').value=this.value">
                    </div>
                </div>
            </div>

            <div class="wf-sep">Pricing</div>
            <div class="wf-grid">
                <div>
                    <div class="wf-lbl">Monthly Rate ($) <span style="color:#f87171">*</span></div>
                    <input type="number" step="0.01" name="monthly_flat_rate" class="wf-input" placeholder="49" required>
                </div>
                <div>
                    <div class="wf-lbl">Email Limit / Mo <span style="color:var(--text-muted);font-weight:400">(0 = unlimited)</span></div>
                    <input type="number" name="transaction_limit" class="wf-input" placeholder="200" value="0">
                </div>
                <div>
                    <div class="wf-lbl">Free Trial Emails</div>
                    <input type="number" name="free_transactions" class="wf-input" value="10">
                </div>
                <div>
                    <div class="wf-lbl">Sort Order</div>
                    <input type="number" name="sort_order" class="wf-input" value="10">
                </div>
            </div>

            <div class="wf-sep">Stripe Link <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:10px">(optional — can add after creating in Stripe)</span></div>
            <div style="margin-bottom:14px">
                <div class="wf-lbl">Price ID</div>
                <div class="wf-verify-row">
                    <input type="text" name="stripe_flat_price_id" id="add-price-id"
                           class="wf-input" style="font-family:monospace;font-size:12px" placeholder="price_1ABC...">
                    <button type="button" class="wp-btn-sm" onclick="verifyPriceAdd()">Verify</button>
                </div>
                <div id="add-price-badge" class="wf-verify-badge"></div>
            </div>

            <div class="flex justify-end gap-2 mt-5">
                <button type="button" onclick="closeAdd()" class="wp-btn wp-btn-out">Cancel</button>
                <button type="submit" class="wp-btn wp-btn-gold" style="font-size:13px;padding:8px 18px">Add Worker</button>
            </div>
        </div>
    </form>
</div>
</div>

<script>
const _csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

function openAdd()  { document.getElementById('add-modal').classList.add('open') }
function closeAdd() { document.getElementById('add-modal').classList.remove('open') }
function openEdit(id)  { document.getElementById('edit-'+id).classList.add('open') }
function closeEdit(id) { document.getElementById('edit-'+id).classList.remove('open') }

function onPickWorker(sel) {
    var fields    = document.getElementById('add-fields');
    var customRow = document.getElementById('add-custom-slug');
    var slugVal   = document.getElementById('add-slug-val');
    var displayEl = document.getElementById('add-display-name');
    var urlEl     = document.getElementById('add-worker-url');
    var opt       = sel.options[sel.selectedIndex];

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

function verifyPrice(planId) {
    var inp   = document.getElementById('price-id-' + planId);
    var badge = document.getElementById('price-badge-' + planId);
    _doVerifyPrice(inp.value.trim(), badge);
}

function verifyPriceAdd() {
    var inp   = document.getElementById('add-price-id');
    var badge = document.getElementById('add-price-badge');
    _doVerifyPrice(inp.value.trim(), badge);
}

function _doVerifyPrice(priceId, badge) {
    if (!priceId) return;
    badge.textContent = 'Checking…';
    badge.className   = 'wf-verify-badge';
    badge.style.display = 'inline-block';
    fetch('{{ route("admin.pricing.verify-price") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':_csrf},
        body: JSON.stringify({price_id: priceId})
    }).then(r => r.json()).then(d => {
        if (d.error) {
            badge.textContent = '✗ ' + d.error;
            badge.className   = 'wf-verify-badge wf-verify-err';
        } else {
            var s = '✓ $' + d.amount.toFixed(2) + '/' + d.interval + ' · ' + d.product;
            if (!d.active) s += ' (inactive)';
            badge.textContent = s;
            badge.className   = 'wf-verify-badge wf-verify-ok';
        }
    }).catch(() => {
        badge.textContent = '✗ Request failed';
        badge.className   = 'wf-verify-badge wf-verify-err';
    });
}

function verifyCoupon(planId) {
    var inp   = document.getElementById('coupon-id-' + planId);
    var badge = document.getElementById('coupon-badge-' + planId);
    var cid   = inp.value.trim();
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
            var s = '✓ ' + d.summary + ' · ' + d.duration;
            if (!d.valid) s += ' (invalid/expired)';
            badge.textContent = s;
            badge.className   = 'wf-verify-badge wf-verify-ok';
        }
    }).catch(() => {
        badge.textContent = '✗ Request failed';
        badge.className   = 'wf-verify-badge wf-verify-err';
    });
}

document.querySelectorAll('.wp-modal-bg').forEach(function(bg){
    bg.addEventListener('click', function(e){ if(e.target===this) this.classList.remove('open') });
});
</script>

</x-app-layout>

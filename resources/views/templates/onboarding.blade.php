{{--
    TEMPLATE — Onboarding layout reference.

    Canonical structure for a multi-step onboarding flow: progress-step
    sidebar (no worker switcher, no LINKS section — pre-hire), floating
    two-column card (hero image + intro copy on the left, live profile/
    progress panel on the right).

    Improve the shell here (via <x-onboarding-shell>) or the hero/profile
    conventions below, then carry matching fixes into the real
    resources/views/onboarding/{worker}/step-*.blade.php files. Reachable
    at /templates/onboarding.
--}}
@php
$steps = [
    ['label' => 'Meet Worker',    'desc' => 'Complete',                    'state' => 'done', 'href' => '#'],
    ['label' => 'Workspace',      'desc' => 'Complete',                    'state' => 'done', 'href' => '#'],
    ['label' => 'Orientation',    'desc' => 'Complete',                    'state' => 'done', 'href' => '#'],
    ['label' => 'First Assignment', 'desc' => 'Give the worker their first job', 'state' => 'active', 'num' => 4],
    ['label' => 'On Shift',       'desc' => 'Worker starts working for you', 'state' => 'pending', 'num' => 5],
];
@endphp
<x-onboarding-shell title="Onboarding Template — UNIT" :steps="$steps">

    <x-slot:hero>
        <img class="ob-hero-img" src="/images/ava-stand.png" alt="Example worker illustration">
        <div class="ob-hero-fade"></div>
        <div class="ob-bubble">
            <p>Example chat bubble — tell me who your first client is, I'll take it from there.</p>
        </div>
        <div class="ob-hero-content">
            <div class="ob-step-tag">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                STEP 4 OF 5
            </div>
            <div class="ob-h1">Introduce Worker to <span class="ob-gold">your first clients.</span></div>
            <div class="ob-sub">One line explaining why this step matters and what the tenant should do.</div>

            <div class="ob-form">
                <div class="ob-form-title">Add another client (optional)</div>
                <div class="ob-form-grid">
                    <div class="ob-field">
                        <label>Client / Company <span class="ob-field-req">*</span></label>
                        <input type="text" placeholder="Acme Corp">
                    </div>
                    <div class="ob-field">
                        <label>Contact name <span class="ob-field-req">*</span></label>
                        <input type="text" placeholder="e.g. Maria Torres">
                    </div>
                    <div class="ob-field">
                        <label>Contact email <span class="ob-field-req">*</span></label>
                        <input type="text" placeholder="e.g. maria@company.com">
                    </div>
                    <div class="ob-field">
                        <label>Asset / service name <span class="ob-field-req">*</span></label>
                        <input type="text" placeholder="acmecorp.com">
                    </div>
                </div>
                <div class="ob-form-actions">
                    <button type="button" class="btn-add">
                        <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Add client
                    </button>
                    <a href="#" class="ob-import-link">
                        <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import CSV
                    </a>
                </div>
            </div>

            <p class="ob-hint">This step is optional — you can skip it and add clients later from Memory.</p>
        </div>
    </x-slot:hero>

    <x-slot:profile>
        <div class="emp-eyebrow">First Assignment</div>
        <div class="emp-name">WORKER</div>
        <div class="emp-role">Example role description</div>
        <hr class="emp-divider">

        <div class="ob-coverage-label">
            <span class="ob-coverage-title">Memory Coverage</span>
            <span class="ob-coverage-pct">40%</span>
        </div>
        <div class="ob-coverage-bar"><div class="ob-coverage-fill" style="width:40%"></div></div>
        <div class="ob-coverage-note">The worker knows 2 clients. That's enough to start working.</div>

        <div class="ob-clients-title">Added so far</div>
        <div class="ob-client-row">
            <span class="ob-client-dot"></span>
            <span class="ob-client-name">Example Client One</span>
        </div>
        <div class="ob-client-row">
            <span class="ob-client-dot"></span>
            <span class="ob-client-name">Example Client Two</span>
        </div>

        <div style="margin-top:auto">
            <a href="#" class="btn-continue is-active">
                Continue — Put worker on shift
                <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </x-slot:profile>

</x-onboarding-shell>

<x-app-layout title="My Profile">

@php
$hasPassword = !empty(auth()->user()->password);
$initials    = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
@endphp

<style>
.pf-input {
    width:100%;box-sizing:border-box;background:var(--bg-raised);color:var(--text-primary);
    font-size:13px;border:1px solid var(--border);border-radius:9px;padding:9px 12px;
    outline:none;transition:border-color .15s;font-family:inherit;
}
.pf-input:focus   { border-color:rgba(var(--accent-rgb),.5); }
.pf-input:disabled{ opacity:.5;cursor:default; }
.pf-label {
    font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
    color:var(--text-muted);margin-bottom:6px;display:block;
}
.pf-btn {
    padding:9px 18px;border-radius:9px;border:none;font-size:13px;font-weight:700;
    cursor:pointer;font-family:inherit;transition:opacity .15s;
}
.pf-btn:hover { opacity:.88; }
.pf-btn-primary { background:var(--accent);color:#12100a; }
.pf-btn-ghost   { background:transparent;border:1px solid var(--border);color:var(--text-secondary); }
.pf-btn-danger  { background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);color:#f87171; }
.pf-card        { background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden;margin-bottom:16px; }
.pf-card-head   { padding:16px 20px;border-bottom:1px solid var(--border); }
.pf-card-head h2{ font-size:13px;font-weight:700;color:var(--text-primary); }
.pf-card-head p { font-size:12px;color:var(--text-muted);margin-top:2px; }
.pf-card-body   { padding:20px; }
.pf-field       { margin-bottom:16px; }
.pf-field:last-child{ margin-bottom:0; }
.pf-row {
    display:flex;align-items:center;justify-content:space-between;
    gap:12px;padding:11px 0;border-bottom:1px solid var(--border-subtle);
}
.pf-row:last-child{ border-bottom:none;padding-bottom:0; }
.pf-divider     { border:none;border-top:1px solid var(--border-subtle);margin:18px 0; }

/* Collapsible sections */
.pf-accordion-body { overflow:hidden; transition:max-height .25s ease, padding .2s ease; }
.pf-accordion-body.closed { max-height:0 !important; padding-top:0; padding-bottom:0; }
.pf-chevron { transition:transform .2s ease; display:inline-block; }
.pf-accordion-body.closed ~ * .pf-chevron,
.pf-chevron.open { transform:rotate(180deg); }

/* Grid */
.pf-grid { display:grid;grid-template-columns:1fr 1fr;gap:20px; }
@media(max-width:768px) { .pf-grid { grid-template-columns:1fr; } }

/* Hero */
.pf-hero { display:flex;flex-wrap:wrap;align-items:flex-start;gap:16px;padding:20px;background:var(--bg-card);border:1px solid var(--border);border-radius:16px;margin-bottom:16px; }
.pf-hero-left { display:flex;align-items:flex-start;gap:14px;flex:1;min-width:220px; }

/* Stat strip */
.pf-stats-strip { display:flex;gap:12px;overflow-x:auto;padding-bottom:4px;margin-bottom:16px;scrollbar-width:none; }
.pf-stats-strip::-webkit-scrollbar { display:none; }
.pf-stat-card {
    flex-shrink:0;min-width:160px;padding:14px 16px;background:var(--bg-card);
    border:1px solid var(--border);border-radius:14px;
}
</style>

{{-- Pending deletion banner --}}
@if($user->deletion_requested_at)
@php $deletionDate = \Carbon\Carbon::parse($user->deletion_requested_at)->addDays(30); @endphp
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px">
    <div>
        <p style="font-size:14px;font-weight:700;color:#f87171;margin-bottom:2px">⚠ Account scheduled for deletion</p>
        <p style="font-size:12px;color:rgba(248,113,113,.7)">All data deleted on <strong>{{ $deletionDate->format('F j, Y') }}</strong> ({{ $deletionDate->diffForHumans() }}).</p>
    </div>
    <form method="POST" action="{{ route('profile.cancel-deletion') }}">
        @csrf
        <button type="submit" style="padding:8px 16px;border-radius:9px;border:none;background:#ef4444;color:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">Cancel deletion</button>
    </form>
</div>
@endif

@if(session('success'))
<div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80;border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;margin-bottom:14px">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171;border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;margin-bottom:14px">{{ session('error') }}</div>
@endif

{{-- ── Profile hero ── --}}
<div class="pf-hero">
    <div class="pf-hero-left">
        @if($user->avatar)
            <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                 style="width:58px;height:58px;border-radius:14px;object-fit:cover;border:2px solid var(--border);flex-shrink:0">
        @else
            <div style="width:58px;height:58px;border-radius:14px;background:linear-gradient(135deg,rgba(var(--accent-rgb),.15),rgba(var(--accent-rgb),.05));border:2px solid rgba(var(--accent-rgb),.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-weight:800;font-size:20px;color:var(--accent-text)">{{ $initials }}</div>
        @endif
        <div style="flex:1;min-width:0">
            <p style="font-size:18px;font-weight:800;color:var(--text-primary);line-height:1.2;word-break:break-word">{{ $user->name }}</p>
            <p style="font-size:13px;color:var(--text-muted);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $user->email }}</p>
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:8px">
                <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80">● {{ ucfirst($user->role) }}</span>
                <span style="font-size:11px;color:var(--text-faint)">Member since {{ \Carbon\Carbon::parse($user->created_at)->format('M Y') }}</span>
                @if($user->google_id)
                <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--text-muted)">G Google</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Referral code --}}
    @if($referralUrl)
    <div style="flex-shrink:0">
        <p style="font-size:10px;color:var(--text-faint);margin-bottom:5px;text-transform:uppercase;letter-spacing:.05em">Referral link</p>
        <div style="display:flex;align-items:center;gap:6px">
            <code style="font-size:12px;font-weight:700;color:var(--accent-text);background:rgba(var(--accent-rgb),.08);border:1px solid rgba(var(--accent-rgb),.2);padding:4px 10px;border-radius:7px;letter-spacing:.03em">{{ $user->referral_code }}</code>
            <button id="copy-ref-btn"
                    onclick="navigator.clipboard.writeText('{{ $referralUrl }}').then(()=>{ this.textContent='✓ Copied';setTimeout(()=>this.textContent='Share link',1800) })"
                    style="font-size:11px;font-weight:600;padding:5px 11px;border-radius:7px;border:1px solid var(--border);background:transparent;color:var(--text-muted);cursor:pointer;white-space:nowrap">
                Share link
            </button>
        </div>
    </div>
    @endif
</div>

{{-- ── Worker stat cards ── --}}
<div class="pf-stats-strip">
    @foreach($workerStats as $stat)
    <div class="pf-stat-card">
        <p style="font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-faint);margin-bottom:4px">{{ $stat['label'] }}</p>
        <p style="font-size:30px;font-weight:900;line-height:1;letter-spacing:-.03em;color:var(--accent-text)">{{ $stat['value'] }}</p>
        <p style="font-size:11px;color:var(--text-faint);margin-top:3px">{{ $stat['subtitle'] }}</p>
    </div>
    @endforeach

    {{-- Referral earnings card --}}
    <div class="pf-stat-card">
        <p style="font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-faint);margin-bottom:4px">YOU · REFERRAL EARNINGS</p>
        <p style="font-size:30px;font-weight:900;line-height:1;letter-spacing:-.03em;color:var(--accent-text)">${{ number_format($referralEarnings, 2) }}</p>
        <p style="font-size:11px;color:var(--text-faint);margin-top:3px">{{ $referralCount }} paid {{ $referralCount === 1 ? 'conversion' : 'conversions' }}</p>
    </div>

    {{-- Team enrichments card --}}
    <div class="pf-stat-card">
        <p style="font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--text-faint);margin-bottom:4px">TEAM · MEMORY ENTRIES</p>
        <p style="font-size:30px;font-weight:900;line-height:1;letter-spacing:-.03em;color:var(--accent-text)">{{ number_format($contactsAdded) }}</p>
        <p style="font-size:11px;color:var(--text-faint);margin-top:3px">Clients & contacts in memory</p>
    </div>
</div>

{{-- ── Main grid: Team + Connected (left), Collapsible settings (right) ── --}}
<div class="pf-grid">

{{-- LEFT: My Team + Connected Accounts --}}
<div>

    {{-- My Team --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <h2>My Team</h2>
            <p>{{ $deployments->count() }} employee{{ $deployments->count() !== 1 ? 's' : '' }} hired</p>
        </div>
        <div class="pf-card-body">
            @forelse($deployments as $dep)
            @php
                $contract = $contracts->get($dep->worker_slug);
                $employee = $contract ? $contract->employee() : [];
                $isActive = $dep->status === 'active';
            @endphp
            <div class="pf-row" style="align-items:flex-start">
                <div style="width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:15px;flex-shrink:0;background:rgba(var(--accent-rgb),.1);color:var(--accent-text)">
                    {{ strtoupper(substr($dep->worker_slug, 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <p style="font-size:13px;font-weight:700;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $dep->name }}</p>
                    <p style="font-size:11px;color:var(--text-muted);margin-top:1px">{{ $employee['title'] ?? strtoupper($dep->worker_slug) }}</p>
                    <p style="font-size:10px;font-weight:600;margin-top:4px;color:{{ $isActive ? '#4ade80' : '#fbbf24' }}">
                        ● {{ ucfirst($dep->status) }}
                        <span style="font-weight:400;color:var(--text-faint)"> · since {{ \Carbon\Carbon::parse($dep->created_at)->format('M j, Y') }}</span>
                    </p>
                </div>
                <a href="{{ route('workers.show', $dep->worker_slug) }}"
                   style="font-size:11px;font-weight:700;padding:6px 12px;border-radius:8px;background:var(--bg-raised);border:1px solid var(--border);color:var(--text-secondary);text-decoration:none;white-space:nowrap;flex-shrink:0;transition:border-color .15s"
                   onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--border)'">
                    Open →
                </a>
            </div>
            @empty
            <div style="text-align:center;padding:20px 0">
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:10px">No employees hired yet.</p>
                <a href="{{ route('workers.deploy') }}"
                   style="font-size:12px;font-weight:700;padding:8px 16px;border-radius:9px;background:var(--accent);color:#12100a;text-decoration:none">
                    Hire your first employee →
                </a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Connected Accounts --}}
    <div class="pf-card">
        <div class="pf-card-head">
            <h2>Connected Accounts</h2>
            <p>OAuth credentials and integrations</p>
        </div>
        <div class="pf-card-body">

            <div class="pf-row">
                <div style="display:flex;align-items:center;gap:10px;min-width:0">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.06);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">G</div>
                    <div style="min-width:0">
                        <p style="font-size:13px;font-weight:600;color:var(--text-primary)">Google Account</p>
                        <p style="font-size:11px;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $user->email }}</p>
                    </div>
                </div>
                <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;flex-shrink:0;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80">
                    {{ $user->google_id ? 'Linked' : 'Not linked' }}
                </span>
            </div>

            @forelse($gmailCredentials as $cred)
            @php
                $watchOk    = $cred->watch_active && $cred->watch_expires_at && \Carbon\Carbon::parse($cred->watch_expires_at)->isFuture();
                $usedByDeps = $deploymentCredentials->get($cred->id, collect());
                // Build label: "AVA — Gmail Access" from the workers using this inbox
                $workerLabels = $usedByDeps->map(function($dc) use ($deployments, $contracts) {
                    $dep = $deployments->firstWhere('id', $dc->deployment_id ?? null);
                    if (!$dep) return null;
                    $contract = $contracts->get($dep->worker_slug);
                    $name = $contract ? ($contract->employee()['name'] ?? strtoupper($dep->worker_slug)) : strtoupper($dep->worker_slug);
                    return $name . ' — Gmail Access';
                })->filter()->unique()->values();
                $connLabel = $workerLabels->isNotEmpty() ? $workerLabels->implode(', ') : 'Gmail Access';
            @endphp
            <div class="pf-row">
                <div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(241,211,98,.08);border:1px solid rgba(241,211,98,.15);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;color:var(--accent-text)">✉</div>
                    <div style="min-width:0">
                        <p style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $connLabel }}</p>
                        <p style="font-size:11px;color:var(--text-muted);margin-top:1px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $cred->gmail_address }}</p>
                        <span style="font-size:10px;font-weight:600;color:{{ $watchOk ? '#4ade80' : '#fbbf24' }}">{{ $watchOk ? '● Active' : '⚠ Inactive' }}</span>
                    </div>
                </div>
                @if(!$watchOk)
                <a href="{{ route('ava.gmail.authorize') }}"
                   style="font-size:11px;font-weight:600;padding:5px 10px;border-radius:7px;background:rgba(241,211,98,.1);border:1px solid rgba(241,211,98,.2);color:var(--accent-text);text-decoration:none;white-space:nowrap;flex-shrink:0">
                    Reconnect
                </a>
                @endif
            </div>
            @empty
            <div style="text-align:center;padding:12px 0">
                <p style="font-size:12px;color:var(--text-muted);margin-bottom:8px">No Gmail inboxes connected.</p>
                <a href="{{ route('ava.gmail.authorize') }}" style="font-size:12px;font-weight:600;color:var(--accent-text);text-decoration:none">+ Connect Gmail →</a>
            </div>
            @endforelse

            <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border-subtle)">
                <div class="pf-row">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;color:var(--text-faint)">in</div>
                        <div><p style="font-size:13px;font-weight:600;color:var(--text-muted)">LinkedIn</p><p style="font-size:11px;color:var(--text-faint)">Required for NUX</p></div>
                    </div>
                    <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--text-faint);white-space:nowrap">Coming soon</span>
                </div>
                <div class="pf-row">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;color:var(--text-faint)">𝕏</div>
                        <div><p style="font-size:13px;font-weight:600;color:var(--text-muted)">X (Twitter)</p><p style="font-size:11px;color:var(--text-faint)">Required for NUX</p></div>
                    </div>
                    <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--text-faint);white-space:nowrap">Coming soon</span>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- RIGHT: Collapsible Account / Security / Danger --}}
<div>

    {{-- Account --}}
    <div class="pf-card">
        <button onclick="toggleAccordion('acc-account', this)"
                style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:none;border:none;cursor:pointer;text-align:left">
            <div>
                <p style="font-size:13px;font-weight:700;color:var(--text-primary)">Account</p>
                <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Name and email address</p>
            </div>
            <span class="pf-chevron" style="color:var(--text-faint);font-size:14px">▾</span>
        </button>
        <div id="acc-account" class="pf-accordion-body closed" style="max-height:600px">
            <div style="padding:0 20px 20px;border-top:1px solid var(--border)">
                <div style="height:16px"></div>
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PATCH')
                    <div class="pf-field">
                        <label class="pf-label" for="name">Full name</label>
                        <input id="name" name="name" type="text" class="pf-input" value="{{ old('name', $user->name) }}" required>
                        @error('name')<p style="color:#f87171;font-size:11px;margin-top:4px">{{ $message }}</p>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Email address</label>
                        <input type="email" class="pf-input" value="{{ $user->email }}" disabled>
                        <p style="font-size:11px;color:var(--text-faint);margin-top:4px">
                            @if($user->google_id) Managed by your Google account. @else Contact support to change your email. @endif
                        </p>
                    </div>
                    <button type="submit" class="pf-btn pf-btn-primary">Save changes</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Security --}}
    <div class="pf-card">
        <button onclick="toggleAccordion('acc-security', this)"
                style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:none;border:none;cursor:pointer;text-align:left">
            <div>
                <p style="font-size:13px;font-weight:700;color:var(--text-primary)">Security</p>
                <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Password and active sessions</p>
            </div>
            <span class="pf-chevron" style="color:var(--text-faint);font-size:14px">▾</span>
        </button>
        <div id="acc-security" class="pf-accordion-body closed" style="max-height:800px">
            <div style="padding:0 20px 20px;border-top:1px solid var(--border)">
                <div style="height:16px"></div>
                @if($hasPassword)
                <p style="font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:12px">Change password</p>
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf @method('PUT')
                    <div class="pf-field">
                        <label class="pf-label" for="current_password">Current password</label>
                        <input id="current_password" name="current_password" type="password" class="pf-input" autocomplete="current-password" required>
                        @error('current_password')<p style="color:#f87171;font-size:11px;margin-top:4px">{{ $message }}</p>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="password">New password</label>
                        <input id="password" name="password" type="password" class="pf-input" autocomplete="new-password" required>
                        @error('password')<p style="color:#f87171;font-size:11px;margin-top:4px">{{ $message }}</p>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="password_confirmation">Confirm new password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="pf-input" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="pf-btn pf-btn-primary">Update password</button>
                </form>
                <hr class="pf-divider">
                @else
                <div style="background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;padding:12px 14px;margin-bottom:16px">
                    <p style="font-size:12px;color:var(--text-muted)">You signed in with Google — no password set on this account.</p>
                </div>
                @endif

                <div class="pf-row">
                    <div><p style="font-size:13px;font-weight:600;color:var(--text-primary)">Two-factor auth</p><p style="font-size:11px;color:var(--text-muted);margin-top:2px">Authenticator app (TOTP)</p></div>
                    <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--text-muted);white-space:nowrap">Coming soon</span>
                </div>

                @if($sessions->isNotEmpty())
                <p style="font-size:12px;font-weight:700;color:var(--text-secondary);margin:16px 0 8px">Active sessions</p>
                @foreach($sessions as $sess)
                <div class="pf-row">
                    <div style="min-width:0">
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:5px;margin-bottom:2px">
                            <p style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $sess->device['browser'] }} on {{ $sess->device['os'] }}</p>
                            @if($sess->is_current)
                            <span style="font-size:9px;font-weight:700;padding:2px 7px;border-radius:20px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.25);color:#4ade80;white-space:nowrap">This device</span>
                            @endif
                        </div>
                        <p style="font-size:11px;color:var(--text-faint)">{{ $sess->ip_address ?? 'Unknown IP' }} · {{ $sess->last_active_at->diffForHumans() }}</p>
                    </div>
                    @if(!$sess->is_current)
                    <form method="POST" action="{{ route('profile.session.revoke', $sess->id) }}" style="flex-shrink:0">
                        @csrf @method('DELETE')
                        <button type="submit" class="pf-btn pf-btn-ghost" style="font-size:11px;padding:6px 12px">End</button>
                    </form>
                    @endif
                </div>
                @endforeach
                @if($sessions->where('is_current', false)->count() > 1)
                <div style="margin-top:12px">
                    <form method="POST" action="{{ route('profile.sessions.revoke-all') }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="pf-btn pf-btn-ghost" style="font-size:12px;width:100%">End all other sessions</button>
                    </form>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Danger Zone --}}
    <div class="pf-card" style="border-color:rgba(239,68,68,.2)">
        <button onclick="toggleAccordion('acc-danger', this)"
                style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:none;border:none;cursor:pointer;text-align:left">
            <div>
                <p style="font-size:13px;font-weight:700;color:#f87171">Danger zone</p>
                <p style="font-size:12px;color:var(--text-muted);margin-top:2px">Permanently delete your account</p>
            </div>
            <span class="pf-chevron" style="color:var(--text-faint);font-size:14px">▾</span>
        </button>
        <div id="acc-danger" class="pf-accordion-body closed" style="max-height:400px">
            <div style="padding:0 20px 20px;border-top:1px solid rgba(239,68,68,.15)">
                <div style="height:16px"></div>
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:14px;line-height:1.6">
                    Schedules deletion of your account and all data. You have 30 days to cancel.
                </p>
                <button onclick="document.getElementById('delete-modal').style.display='flex'"
                        class="pf-btn pf-btn-danger"
                        {{ $user->deletion_requested_at ? 'disabled style=opacity:.4;cursor:not-allowed' : '' }}>
                    {{ $user->deletion_requested_at ? 'Deletion already scheduled' : 'Delete my account' }}
                </button>
            </div>
        </div>
    </div>

</div>
</div>{{-- end grid --}}

{{-- Delete modal --}}
<div id="delete-modal" style="display:none;position:fixed;inset:0;z-index:200;background:rgba(0,0,0,.75);align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--bg-card);border:1px solid rgba(239,68,68,.3);border-radius:16px;padding:24px;max-width:420px;width:100%">
        <h3 style="font-size:16px;font-weight:800;color:#f87171;margin-bottom:8px">Delete account</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px;line-height:1.6">
            Your account will be <strong style="color:var(--text-secondary)">scheduled for deletion</strong> — not immediate. You have <strong style="color:var(--text-secondary)">30 days</strong> to cancel. After that, all employees, transactions, memory, and subscriptions are permanently removed.
        </p>
        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf @method('DELETE')
            <div class="pf-field">
                <label class="pf-label" for="confirm_delete">Type DELETE to confirm</label>
                <input id="confirm_delete" name="confirm_delete" type="text" class="pf-input" placeholder="DELETE" autocomplete="off">
                @error('confirm_delete')<p style="color:#f87171;font-size:11px;margin-top:4px">{{ $message }}</p>@enderror
            </div>
            <div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap">
                <button type="button" onclick="document.getElementById('delete-modal').style.display='none'"
                        class="pf-btn pf-btn-ghost" style="flex:1;min-width:120px">Cancel</button>
                <button type="submit" id="delete-submit" disabled
                        class="pf-btn pf-btn-danger" style="flex:1;min-width:120px;opacity:.5">
                    Delete permanently
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAccordion(id, btn) {
    const body    = document.getElementById(id);
    const chevron = btn.querySelector('.pf-chevron');
    const isOpen  = !body.classList.contains('closed');
    if (isOpen) {
        body.style.maxHeight = '0';
        body.style.paddingTop = '0';
        body.style.paddingBottom = '0';
        body.classList.add('closed');
        chevron.style.transform = 'rotate(0deg)';
    } else {
        body.style.maxHeight = body.getAttribute('style').match(/max-height:(\d+px)/)?.[1] ?? '600px';
        body.classList.remove('closed');
        chevron.style.transform = 'rotate(180deg)';
    }
}

document.getElementById('confirm_delete')?.addEventListener('input', function() {
    const btn = document.getElementById('delete-submit');
    btn.disabled = this.value !== 'DELETE';
    btn.style.opacity = this.value === 'DELETE' ? '1' : '.5';
});
</script>

</x-app-layout>

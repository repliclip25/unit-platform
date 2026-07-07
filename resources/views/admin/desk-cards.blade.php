<x-app-layout title="Desk Cards — Admin">

<style>
.dc-card        { background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:24px; }
.dc-header      { padding:16px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border-subtle); }
.dc-tier-label  { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted); }
.dc-row         { display:flex;align-items:flex-start;gap:16px;padding:14px 20px;border-bottom:1px solid var(--border-subtle); }
.dc-row:last-child { border-bottom:none; }
.dc-row-info    { flex:1;min-width:0; }
.dc-row-label   { font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:2px; }
.dc-row-key     { font-size:11px;color:var(--text-faint);font-family:monospace; }
.dc-row-desc    { font-size:12px;color:var(--text-muted);margin-top:3px; }
.dc-controls    { display:flex;align-items:center;gap:16px;flex-shrink:0; }
.dc-toggle-wrap { display:flex;flex-direction:column;align-items:center;gap:4px; }
.dc-toggle-sub  { font-size:10px;color:var(--text-faint);text-align:center;white-space:nowrap; }

/* Toggle switch */
.tog            { position:relative;width:38px;height:22px;cursor:pointer;flex-shrink:0; }
.tog input      { opacity:0;width:0;height:0;position:absolute; }
.tog-track      { position:absolute;inset:0;border-radius:11px;transition:.2s;background:var(--bg-raised);border:1px solid var(--border); }
.tog input:checked ~ .tog-track { background:var(--accent);border-color:var(--accent); }
.tog-thumb      { position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:#000;transition:.2s;pointer-events:none; }
.tog input:checked ~ .tog-track .tog-thumb { transform:translateX(16px);background:#000; }
.tog-track-light .tog-thumb { background:var(--bg-card); }
.tog input:not(:checked) ~ .tog-track .tog-thumb { background:var(--text-muted); }
</style>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold" style="color:var(--text-primary)">Desk Card Configuration</h1>
        <p class="text-sm mt-1" style="color:var(--text-muted)">Control which cards are available on the "Your Desk" feed and what's on by default for new users.</p>
    </div>
    <a href="{{ route('admin.platform-usage') }}" class="text-xs px-3 py-1.5 rounded-lg transition hover:opacity-80"
       style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">← Admin</a>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl text-sm" style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#4ade80">
    {{ session('success') }}
</div>
@endif

@php
    $tierLabels = ['pipeline'=>'Pipeline (worker-declared)','memory'=>'Memory','growth'=>'Growth','platform'=>'Platform / Marketing'];
    $tierOrder  = ['pipeline','memory','growth','platform'];
@endphp

@foreach($tierOrder as $tier)
@if($grouped->has($tier))
<div class="dc-card">
    <div class="dc-header">
        <span class="dc-tier-label">{{ $tierLabels[$tier] ?? ucfirst($tier) }}</span>
        <span class="text-xs" style="color:var(--text-faint)">{{ $grouped[$tier]->count() }} {{ $grouped[$tier]->count() === 1 ? 'card' : 'cards' }}</span>
    </div>

    {{-- Column headers --}}
    <div class="dc-row" style="padding-top:10px;padding-bottom:10px;background:var(--bg-raised)">
        <div class="dc-row-info">
            <span class="dc-tier-label">Card</span>
        </div>
        <div class="dc-controls">
            <div class="dc-toggle-wrap"><span class="dc-tier-label">Active</span></div>
            <div class="dc-toggle-wrap"><span class="dc-tier-label">Default on</span></div>
        </div>
    </div>

    @foreach($grouped[$tier] as $card)
    <div class="dc-row" id="row-{{ str_replace('.', '-', $card['key']) }}">
        <div class="dc-row-info">
            <div class="dc-row-label">{{ $card['label'] }}</div>
            <div class="dc-row-key">{{ $card['key'] }}</div>
            <div class="dc-row-desc">{{ $card['description'] }}</div>
        </div>
        <div class="dc-controls">
            {{-- Active toggle --}}
            <div class="dc-toggle-wrap">
                <label class="tog" title="Toggle globally active">
                    <input type="checkbox"
                           @if($card['active']) checked @endif
                           onchange="adminToggle('{{ $card['key'] }}', this, 'active')">
                    <div class="tog-track"><div class="tog-thumb"></div></div>
                </label>
                <span class="dc-toggle-sub" id="lbl-active-{{ str_replace('.', '-', $card['key']) }}">
                    {{ $card['active'] ? 'On' : 'Off' }}
                </span>
            </div>
            {{-- Default toggle --}}
            <div class="dc-toggle-wrap">
                <label class="tog" title="Show for new users by default">
                    <input type="checkbox"
                           @if($card['default_on']) checked @endif
                           onchange="adminToggle('{{ $card['key'] }}', this, 'default')">
                    <div class="tog-track"><div class="tog-thumb"></div></div>
                </label>
                <span class="dc-toggle-sub" id="lbl-default-{{ str_replace('.', '-', $card['key']) }}">
                    {{ $card['default_on'] ? 'Yes' : 'No' }}
                </span>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
@endforeach

<x-self-learn
    page-key="admin.desk-cards"
    title="How desk cards work"
    body="Pipeline cards are declared by each WorkerContract::deskCards() and appear here only when at least one matching worker is deployed. Toggling 'Active' hides the card from all users immediately. Toggling 'Default on' affects new users only — existing users keep their own saved preference." />

<script>
function adminToggle(key, checkbox, type) {
    var slug = key.replace(/\./g, '-');
    var url  = type === 'active'
        ? '/admin/desk-cards/' + key + '/toggle'
        : '/admin/desk-cards/' + key + '/toggle-default';

    var lblId = type === 'active' ? 'lbl-active-' + slug : 'lbl-default-' + slug;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({}),
    })
    .then(r => r.json())
    .then(data => {
        var lbl = document.getElementById(lblId);
        if (!lbl) return;
        if (type === 'active') {
            lbl.textContent = data.active ? 'On' : 'Off';
        } else {
            lbl.textContent = data.default_on ? 'Yes' : 'No';
        }
    })
    .catch(() => { checkbox.checked = !checkbox.checked; }); // revert on error
}
</script>

</x-app-layout>

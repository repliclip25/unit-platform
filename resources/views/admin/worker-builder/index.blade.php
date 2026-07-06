<x-app-layout title="Worker Builder">
<div style="max-width:1100px;margin:0 auto;padding:32px 24px">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <p style="font-size:11px;font-weight:600;letter-spacing:0.08em;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px">Admin · Control Tower</p>
            <h1 style="font-size:24px;font-weight:700;color:var(--text-primary)">Worker Builder</h1>
            <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Register, design, and scaffold new workers from their DNA up.</p>
        </div>
        <a href="{{ route('admin.workers.create') }}"
            style="background:var(--accent);color:#000;font-size:13px;font-weight:700;padding:10px 20px;border-radius:10px;text-decoration:none;white-space:nowrap">
            + Register Worker
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px">
        {{ session('error') }}
    </div>
    @endif

    {{-- Worker cards --}}
    @if($workers->isEmpty())
    <div style="text-align:center;padding:64px 24px;background:var(--bg-card);border:1px solid var(--border);border-radius:16px">
        <div style="font-size:32px;margin-bottom:12px">⚙️</div>
        <p style="font-size:16px;font-weight:600;color:var(--text-primary);margin-bottom:6px">No workers registered yet</p>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px">Start by registering a new worker — define its DNA, pipeline, prompts, and QA requirements, then generate the code scaffold.</p>
        <a href="{{ route('admin.workers.create') }}"
            style="background:var(--accent);color:#000;font-size:13px;font-weight:700;padding:10px 20px;border-radius:10px;text-decoration:none">
            Register First Worker
        </a>
    </div>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">
        @foreach($workers as $w)
        @php
            $stageCount = count($w->pipeline_stages ?? []);
            $color      = $w->status_meta['color'];
        @endphp
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden">

            {{-- Card top bar (status color) --}}
            <div style="height:4px;background:{{ $color }}"></div>

            <div style="padding:20px">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <p style="font-size:15px;font-weight:700;color:var(--text-primary)">{{ $w->name }}</p>
                        <p style="font-size:11px;font-family:monospace;color:var(--text-muted)">{{ $w->slug }} · v{{ $w->version }}</p>
                    </div>
                    <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;background:{{ $color }}22;color:{{ $color }}">
                        {{ $w->status_meta['label'] }}
                    </span>
                </div>

                <p style="font-size:12px;color:var(--text-secondary);margin-bottom:14px;line-height:1.5">{{ $w->description }}</p>

                <div class="flex items-center gap-3 mb-4" style="font-size:11px;color:var(--text-muted)">
                    @if(!empty($w->org['name']))
                    <span>🏢 {{ $w->org['name'] }}</span>
                    @endif
                    <span>⚙ {{ $stageCount }} stage{{ $stageCount !== 1 ? 's' : '' }}</span>
                    @if(!empty($w->tags))
                    <span>🏷 {{ count($w->tags) }} tags</span>
                    @endif
                </div>

                @if($w->folder_path)
                <p style="font-size:10px;font-family:monospace;color:var(--text-faint);margin-bottom:12px;padding:4px 8px;background:var(--bg-raised);border-radius:6px">
                    📁 {{ $w->folder_path }}
                </p>
                @endif

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('admin.workers.edit', $w->slug) }}"
                        style="font-size:11px;font-weight:600;padding:5px 12px;border-radius:8px;background:var(--accent);color:#000;text-decoration:none">
                        Edit DNA
                    </a>
                    <a href="{{ route('admin.workers.personas', $w->slug) }}"
                        style="font-size:11px;font-weight:600;padding:5px 12px;border-radius:8px;border:1px solid var(--border);color:var(--text-primary);background:transparent;text-decoration:none">
                        Personas
                    </a>
                    <a href="{{ route('admin.workers.rules', $w->slug) }}"
                        style="font-size:11px;font-weight:600;padding:5px 12px;border-radius:8px;border:1px solid var(--border);color:var(--text-primary);background:transparent;text-decoration:none">
                        Rules
                    </a>
                    @if($w->status === 'registered' || $w->status === 'scaffolded')
                    <form method="POST" action="{{ route('admin.workers.scaffold', $w->slug) }}" style="margin:0">
                        @csrf
                        <button type="submit" style="font-size:11px;font-weight:600;padding:5px 12px;border-radius:8px;border:1px solid var(--border);color:var(--text-primary);background:transparent;cursor:pointer">
                            {{ $w->scaffold_generated_at ? '↺ Re-scaffold' : '⚡ Generate Scaffold' }}
                        </button>
                    </form>
                    @endif

                    {{-- Status stepper --}}
                    <form method="POST" action="{{ route('admin.workers.status', $w->slug) }}" style="margin:0">
                        @csrf
                        <select name="status" onchange="this.form.submit()"
                            style="font-size:11px;padding:5px 8px;border-radius:8px;border:1px solid var(--border);background:var(--bg-raised);color:var(--text-primary);cursor:pointer">
                            @foreach($statuses as $key => $meta)
                            <option value="{{ $key }}" {{ $w->status === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </form>

                    <a href="{{ route('admin.workers.export', $w->slug) }}"
                       title="Download full worker schema as JSON"
                       style="font-size:11px;font-weight:600;padding:5px 12px;border-radius:8px;border:1px solid var(--border);color:var(--text-primary);background:transparent;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export Schema
                    </a>

                    @if($w->status !== 'published')
                    <form method="POST" action="{{ route('admin.workers.destroy', $w->slug) }}"
                        onsubmit="return confirm('Delete {{ $w->name }}? This cannot be undone.')" style="margin:0">
                        @csrf @method('DELETE')
                        <button type="submit" style="font-size:11px;font-weight:600;padding:5px 10px;border-radius:8px;border:1px solid #f87171;color:#f87171;background:transparent;cursor:pointer">
                            Delete
                        </button>
                    </form>
                    @endif
                </div>

                {{-- Lifecycle section --}}
                @php
                    $lc = $w->lifecycle_status ?? 'active';
                    $lcMeta = [
                        'active'         => ['label'=>'Active',         'bg'=>'#166534', 'text'=>'#bbf7d0'],
                        'testing'        => ['label'=>'Testing',        'bg'=>'#713f12', 'text'=>'#fef08a'],
                        'decommissioned' => ['label'=>'Decommissioned', 'bg'=>'#7f1d1d', 'text'=>'#fca5a5'],
                        'removing'       => ['label'=>'Removing…',      'bg'=>'#4a044e', 'text'=>'#f0abfc'],
                        'removed'        => ['label'=>'Removed',        'bg'=>'#1e293b', 'text'=>'#94a3b8'],
                    ][$lc] ?? ['label'=>$lc, 'bg'=>'#1e293b', 'text'=>'#94a3b8'];
                @endphp
                <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)">

                    {{-- Status row --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                        <span style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted)">Platform Track</span>
                        <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:{{ $lcMeta['bg'] }};color:{{ $lcMeta['text'] }};display:inline-flex;align-items:center;gap:5px">
                            @if($lc === 'removing')
                                <span style="width:6px;height:6px;border-radius:50%;background:#f0abfc;animation:pulse 1.2s infinite;display:inline-block"></span>
                            @endif
                            {{ $lcMeta['label'] }}
                        </span>
                    </div>

                    {{-- Action buttons --}}
                    @if(!in_array($lc, ['removing','removed']))
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                        @if($lc !== 'active')
                        <form method="POST" action="{{ route('admin.workers.commission', $w->slug) }}" style="margin:0">
                            @csrf
                            <button type="submit" style="width:100%;font-size:11px;font-weight:600;padding:6px 0;border-radius:8px;border:1px solid #4ade80;color:#4ade80;background:transparent;cursor:pointer">
                                ↑ Commission
                            </button>
                        </form>
                        @else
                        <div></div>
                        @endif

                        @if($lc !== 'testing')
                        <form method="POST" action="{{ route('admin.workers.testing', $w->slug) }}" style="margin:0">
                            @csrf
                            <button type="submit" style="width:100%;font-size:11px;font-weight:600;padding:6px 0;border-radius:8px;border:1px solid #fbbf24;color:#fbbf24;background:transparent;cursor:pointer">
                                ⚗ Testing Mode
                            </button>
                        </form>
                        @else
                        <div></div>
                        @endif

                        @if($lc !== 'decommissioned')
                        <form method="POST" action="{{ route('admin.workers.decommission', $w->slug) }}"
                              onsubmit="return confirm('Decommission {{ $w->name }}? All active deployments halt. Data is preserved.')"
                              style="margin:0;grid-column:1/-1">
                            @csrf
                            <button type="submit" style="width:100%;font-size:11px;font-weight:600;padding:6px 0;border-radius:8px;border:1px solid #f87171;color:#f87171;background:transparent;cursor:pointer">
                                ⊗ Decommission
                            </button>
                        </form>
                        @else
                        <button onclick="openRemoveModal('{{ $w->slug }}', '{{ addslashes($w->name) }}')"
                                style="width:100%;font-size:11px;font-weight:600;padding:6px 0;border-radius:8px;border:1px solid #dc2626;color:#dc2626;background:rgba(220,38,38,.08);cursor:pointer;grid-column:1/-1">
                            ✕ Remove &amp; Wipe Data
                        </button>
                        @endif
                    </div>
                    @endif

                    @if($lc === 'removing')
                    <p style="font-size:11px;color:var(--text-muted);margin-top:6px;font-style:italic">Soft-deleting tenant data in background…</p>
                    @elseif($lc === 'removed')
                    <p style="font-size:11px;color:var(--text-muted);margin-top:6px">All tenant data soft-deleted. Worker archived.</p>
                    @endif

                </div>

                @if($w->scaffold_generated_at)
                <p style="font-size:10px;color:var(--text-faint);margin-top:10px">
                    Scaffold generated {{ \Carbon\Carbon::parse($w->scaffold_generated_at)->diffForHumans() }}
                </p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Back link --}}
    <div style="margin-top:32px">
        <a href="{{ route('admin.platform') }}" style="font-size:12px;color:var(--text-muted);text-decoration:none">← Back to Control Tower</a>
    </div>

</div>

{{-- Remove Worker Modal --}}
<div id="remove-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);align-items:center;justify-content:center">
    <div style="background:var(--bg-card);border:1px solid #dc2626;border-radius:16px;padding:28px 32px;max-width:440px;width:90%;box-shadow:0 24px 64px rgba(0,0,0,.5)">
        <div style="font-size:18px;font-weight:800;color:#dc2626;margin-bottom:8px">⚠ Remove Worker</div>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:6px;line-height:1.6">
            This will <strong>soft-delete all tenant data</strong> for this worker across every account:
            transactions, renewal register, billing rows, credentials, templates, and deployments.
        </p>
        <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px">
            Data is recoverable by admin but hidden from all tenants immediately. This runs as a background job.
        </p>
        <p style="font-size:12px;font-weight:700;color:var(--text-primary);margin-bottom:6px">
            Type <span id="remove-modal-slug-label" style="font-family:monospace;color:#dc2626"></span> to confirm:
        </p>
        <form id="remove-modal-form" method="POST" style="margin:0">
            @csrf
            <input type="text" name="confirm_name" id="remove-confirm-input"
                placeholder="worker slug"
                style="width:100%;padding:9px 12px;border-radius:8px;border:1px solid #dc2626;background:var(--bg-raised);color:var(--text-primary);font-size:13px;font-family:monospace;margin-bottom:14px;box-sizing:border-box"
                autocomplete="off">
            <div style="display:flex;gap:10px">
                <button type="button" onclick="closeRemoveModal()"
                    style="flex:1;padding:9px;border-radius:8px;border:1px solid var(--border);color:var(--text-secondary);background:transparent;cursor:pointer;font-size:13px">
                    Cancel
                </button>
                <button type="submit"
                    style="flex:1;padding:9px;border-radius:8px;background:#dc2626;color:#fff;border:none;cursor:pointer;font-size:13px;font-weight:700">
                    Remove Worker
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRemoveModal(slug, name) {
    document.getElementById('remove-modal-slug-label').textContent = slug;
    document.getElementById('remove-confirm-input').value = '';
    document.getElementById('remove-modal-form').action = '/admin/workers/' + slug + '/remove';
    const modal = document.getElementById('remove-modal');
    modal.style.display = 'flex';
    setTimeout(() => document.getElementById('remove-confirm-input').focus(), 50);
}
function closeRemoveModal() {
    document.getElementById('remove-modal').style.display = 'none';
}
document.getElementById('remove-modal').addEventListener('click', function(e) {
    if (e.target === this) closeRemoveModal();
});
</script>
</x-app-layout>

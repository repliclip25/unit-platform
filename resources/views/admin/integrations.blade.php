<x-app-layout title="Integration Registry">

<style>
.ir-card        { background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden; }
.ir-section-hd  { padding:18px 24px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px; }
.ir-section-hd h2 { font-size:13px;font-weight:700;color:var(--text-primary);text-transform:uppercase;letter-spacing:.07em; }
.ir-row         { display:grid;grid-template-columns:1fr;gap:0; }
.ir-item        { padding:16px 24px;border-bottom:1px solid var(--border-subtle);display:flex;align-items:flex-start;gap:16px; }
.ir-item:last-child { border-bottom:0; }
.ir-icon        { width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:18px; }
.ir-body        { flex:1;min-width:0; }
.ir-label       { font-size:14px;font-weight:600;color:var(--text-primary);display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
.ir-meta        { font-size:12px;color:var(--text-muted);margin-top:3px; }
.ir-urls        { margin-top:10px;display:flex;flex-direction:column;gap:6px; }
.ir-url-row     { display:flex;align-items:center;gap:8px; }
.ir-url-tag     { font-size:10px;font-weight:700;letter-spacing:.06em;padding:2px 7px;border-radius:5px;flex-shrink:0; }
.tag-local      { background:rgba(96,165,250,.12);color:#60a5fa; }
.tag-prod       { background:rgba(34,197,94,.12);color:#4ade80; }
.tag-env        { background:rgba(156,163,175,.1);color:var(--text-muted); }
.ir-url-val     { font-size:12px;font-family:monospace;color:var(--text-secondary);background:var(--bg-raised);padding:3px 8px;border-radius:6px;border:1px solid var(--border);flex:1;word-break:break-all; }
.ir-env-row     { display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:8px; }
.env-chip       { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-family:monospace;padding:2px 8px;border-radius:6px;border:1px solid var(--border); }
.env-set        { background:rgba(34,197,94,.08);color:#4ade80;border-color:rgba(34,197,94,.2); }
.env-missing    { background:rgba(239,68,68,.08);color:#f87171;border-color:rgba(239,68,68,.2); }
.ir-actions     { display:flex;align-items:center;gap:8px;flex-shrink:0; }
.type-badge     { font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;letter-spacing:.05em;text-transform:uppercase; }
.type-oauth     { background:rgba(139,92,246,.15);color:#a78bfa; }
.type-webhook   { background:rgba(251,146,60,.12);color:#fb923c; }
.type-pubsub    { background:rgba(34,211,238,.12);color:#22d3ee; }
.type-api_key   { background:rgba(241,211,98,.12);color:var(--accent-text); }
.type-smtp      { background:rgba(96,165,250,.12);color:#60a5fa; }
.type-callback_url { background:rgba(52,211,153,.12);color:#34d399; }
.type-database  { background:rgba(251,191,36,.12);color:#fbbf24; }
.type-storage   { background:rgba(167,139,250,.12);color:#a78bfa; }
.type-sdk       { background:rgba(209,213,219,.1);color:var(--text-secondary); }
.type-websocket { background:rgba(34,211,238,.12);color:#22d3ee; }
.type-sftp      { background:rgba(156,163,175,.1);color:var(--text-muted); }
.status-badge   { font-size:10px;font-weight:700;padding:2px 9px;border-radius:99px; }
.st-configured  { background:rgba(34,197,94,.12);color:#4ade80; }
.st-partial     { background:rgba(241,211,98,.12);color:var(--accent-text); }
.st-missing     { background:rgba(239,68,68,.12);color:#f87171; }
.st-no_keys     { background:rgba(156,163,175,.1);color:var(--text-muted); }
.st-pending     { background:rgba(156,163,175,.1);color:var(--text-muted); }
.ir-notes       { margin-top:8px;font-size:12px;color:var(--text-muted);line-height:1.5;padding:8px 10px;background:var(--bg-raised);border-radius:8px;border-left:3px solid var(--border); }
.ir-edit-btn    { font-size:11px;font-weight:600;padding:4px 10px;border-radius:8px;border:1px solid var(--border);background:var(--bg-raised);color:var(--text-secondary);cursor:pointer;transition:all .15s; }
.ir-edit-btn:hover { border-color:var(--accent);color:var(--text-primary); }
.ir-del-btn     { font-size:11px;font-weight:600;padding:4px 10px;border-radius:8px;border:1px solid rgba(239,68,68,.25);background:rgba(239,68,68,.06);color:#f87171;cursor:pointer;transition:all .15s; }
.ir-del-btn:hover { background:rgba(239,68,68,.14); }
.worker-hd      { display:flex;align-items:center;gap:10px;padding:14px 24px 10px;border-bottom:1px solid var(--border);background:var(--bg-raised); }
.worker-slug    { font-size:11px;font-weight:800;padding:3px 10px;border-radius:99px;letter-spacing:.1em;text-transform:uppercase; }
.golive-row     { display:grid;grid-template-columns:1fr 32px 1fr;gap:8px;align-items:center;padding:12px 24px;border-bottom:1px solid var(--border-subtle); }
.golive-row:last-child { border-bottom:0; }
.tab-btn        { font-size:12px;font-weight:600;padding:7px 18px;border-radius:10px;border:none;cursor:pointer;transition:all .15s;color:var(--text-muted);background:transparent; }
.tab-btn.active { background:var(--bg-card);color:var(--text-primary);box-shadow:0 1px 4px rgba(0,0,0,.3); }

/* modal */
.ir-modal-bg    { display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1000;align-items:center;justify-content:center; }
.ir-modal-bg.open { display:flex; }
.ir-modal       { background:var(--bg-card);border:1px solid var(--border);border-radius:20px;width:100%;max-width:560px;padding:28px;max-height:90vh;overflow-y:auto; }
.ir-input       { width:100%;background:var(--bg-raised);border:1px solid var(--border);border-radius:10px;padding:8px 12px;font-size:13px;color:var(--text-primary);outline:none;transition:border .15s; }
.ir-input:focus { border-color:var(--accent); }
.ir-label-sm    { font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px; }
</style>

{{-- Flash --}}
@if(session('int_success'))
<div class="mb-5 px-4 py-3 rounded-xl text-sm font-semibold" style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80">
    {{ session('int_success') }}
</div>
@endif

{{-- Page header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold" style="color:var(--text-primary)">Integration Registry</h1>
        <p class="text-sm mt-0.5" style="color:var(--text-muted)">Every external service the platform and workers depend on to function.</p>
    </div>
    <button onclick="openAddModal()" class="ir-edit-btn" style="border-color:rgba(var(--accent-rgb),.4);color:var(--accent-text)">+ Add Integration</button>
</div>

{{-- Tabs --}}
<div style="background:var(--bg-raised);padding:4px;border-radius:12px;display:inline-flex;gap:2px;margin-bottom:24px">
    <button class="tab-btn active" onclick="showTab('platform',this)">Platform</button>
    <button class="tab-btn" onclick="showTab('workers',this)">Workers</button>
    <button class="tab-btn" onclick="showTab('golive',this)">
        Go-Live Checklist
        @if($goLive->count())
        <span class="ml-1 text-xs font-bold px-1.5 py-0.5 rounded-full" style="background:rgba(241,211,98,.2);color:var(--accent-text)">{{ $goLive->count() }}</span>
        @endif
    </button>
</div>

{{-- ── PLATFORM TAB ──────────────────────────────────────────────────────── --}}
<div id="tab-platform">
    <div class="ir-card">
        @foreach($platform as $int)
        @php $pending = ($int->meta['status'] ?? '') === 'pending'; @endphp
        <div class="ir-item" id="int-{{ $int->id }}">
            <div class="ir-icon" style="background:var(--bg-raised)">
                @if($int->service === 'google_oauth')
                    <svg viewBox="0 0 24 24" class="w-5 h-5"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                @elseif($int->service === 'apple_oauth')
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor" style="color:var(--text-primary)"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.7 9.05 7.4c1.34.07 2.28.74 3.06.79 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.32 3-2.51 4zm-3.08-17.52c.06 2.06-1.52 3.72-3.5 3.57-.28-1.97 1.67-3.86 3.5-3.57z"/></svg>
                @elseif($int->service === 'stripe')
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="#635BFF"><path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/></svg>
                @elseif($int->service === 'anthropic')
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor" style="color:#d4a574"><path d="M17.304 3.541 13.362 14.57h-2.55L6.696 3.541H9.18l2.822 8.312 2.822-8.312zM4 20.459l2.424-6.73H8.77l-2.424 6.73zm10.81-6.73 2.424 6.73H14.89l-2.424-6.73z"/></svg>
                @elseif($int->service === 'smtp')
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--text-secondary)"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                @elseif($int->service === 'flare')
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor" style="color:#f97316"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                @else
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--text-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.1-1.1m-.757-4.9a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                @endif
            </div>
            <div class="ir-body">
                <div class="ir-label">
                    {{ $int->label }}
                    <span class="type-badge type-{{ $int->type }}">{{ str_replace('_',' ',$int->type) }}</span>
                    @if($pending)
                        <span class="status-badge st-pending">pending</span>
                    @else
                        <span class="status-badge st-{{ $int->status }}">{{ str_replace('_',' ',$int->status) }}</span>
                    @endif
                </div>
                @if($int->description)
                <div class="ir-meta" style="margin-top:5px;font-size:12px;color:var(--text-secondary);line-height:1.55">{{ $int->description }}</div>
                @endif

                {{-- URLs --}}
                @if($int->local_url || $int->production_url)
                <div class="ir-urls">
                    @if($int->local_url)
                    <div class="ir-url-row">
                        <span class="ir-url-tag tag-local">LOCAL</span>
                        <span class="ir-url-val">{{ $int->local_url }}</span>
                        <button onclick="copyText('{{ $int->local_url }}')" title="Copy" class="ir-edit-btn" style="padding:3px 8px;display:flex;align-items:center"><svg viewBox="0 0 24 24" style="width:12px;height:12px" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg></button>
                    </div>
                    @endif
                    @if($int->production_url)
                    <div class="ir-url-row">
                        <span class="ir-url-tag tag-prod">PROD</span>
                        <span class="ir-url-val">{{ $int->production_url }}</span>
                        <button onclick="copyText('{{ $int->production_url }}')" title="Copy" class="ir-edit-btn" style="padding:3px 8px;display:flex;align-items:center"><svg viewBox="0 0 24 24" style="width:12px;height:12px" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg></button>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Env keys --}}
                @if(!empty($int->env_keys))
                <div class="ir-env-row">
                    @foreach($int->env_keys as $key)
                    @php $set = !empty(env($key)); @endphp
                    <span class="env-chip {{ $set ? 'env-set' : 'env-missing' }}">
                        {{ $set ? '✓' : '✗' }} {{ $key }}
                    </span>
                    @endforeach
                </div>
                @endif

                {{-- Notes --}}
                @if($int->notes)
                <div class="ir-notes">{{ $int->notes }}</div>
                @endif

                {{-- Console link --}}
                @if(!empty($int->meta['console']))
                <div class="mt-2">
                    <a href="{{ $int->meta['console'] }}" target="_blank" class="text-xs font-semibold" style="color:var(--accent-text)">
                        Open Console ↗
                    </a>
                </div>
                @endif
            </div>
            <div class="ir-actions">
                <button onclick="openEditModal({{ $int->id }},'{{ addslashes($int->label) }}','{{ addslashes($int->description ?? '') }}','{{ addslashes($int->local_url ?? '') }}','{{ addslashes($int->production_url ?? '') }}','{{ addslashes($int->notes ?? '') }}')" class="ir-edit-btn">Edit</button>
                <form method="POST" action="{{ route('admin.integrations.destroy', $int->id) }}" onsubmit="return confirm('Remove this integration entry?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="ir-del-btn">✕</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── WORKERS TAB ──────────────────────────────────────────────────────── --}}
<div id="tab-workers" style="display:none">
    @forelse($workers as $slug => $ints)
    @php
        $workerColors = ['ava'=>'#142C74','nova'=>'#818cf8','rex'=>'#34d399','lena'=>'#fb923c'];
        $wColor = $workerColors[$slug] ?? '#60a5fa';
    @endphp
    <div class="ir-card mb-4">
        <div class="worker-hd">
            <span class="worker-slug" style="background:{{ $wColor }}22;color:{{ $wColor }}">{{ strtoupper($slug) }}</span>
            <span class="text-sm font-semibold" style="color:var(--text-secondary)">{{ $ints->count() }} integration{{ $ints->count() !== 1 ? 's' : '' }}</span>
        </div>
        @foreach($ints as $int)
        <div class="ir-item" id="int-{{ $int->id }}">
            <div class="ir-icon" style="background:var(--bg-raised)">
                @if($int->service === 'gmail_oauth')
                    <svg viewBox="0 0 24 24" class="w-5 h-5"><path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z" fill="#EA4335"/></svg>
                @elseif($int->service === 'gmail_pubsub')
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor" style="color:#4285F4"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" fill="#4285F4" opacity=".3"/><circle cx="12" cy="12" r="3" fill="#4285F4"/><path d="M12 6.5a1 1 0 011 1V9a1 1 0 01-2 0V7.5a1 1 0 011-1zm0 9a1 1 0 011 1v1.5a1 1 0 01-2 0V16.5a1 1 0 011-1zm5.5-5.5a1 1 0 010 2H16a1 1 0 010-2h1.5zM8 12a1 1 0 010 2H6.5a1 1 0 010-2H8z" fill="#4285F4"/></svg>
                @else
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" style="color:var(--text-muted)"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.1-1.1m-.757-4.9a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                @endif
            </div>
            <div class="ir-body">
                <div class="ir-label">
                    {{ $int->label }}
                    <span class="type-badge type-{{ $int->type }}">{{ str_replace('_',' ',$int->type) }}</span>
                    <span class="status-badge st-{{ $int->status }}">{{ str_replace('_',' ',$int->status) }}</span>
                </div>
                @if($int->description)
                <div class="ir-meta" style="margin-top:5px;font-size:12px;color:var(--text-secondary);line-height:1.55">{{ $int->description }}</div>
                @endif

                @if($int->local_url || $int->production_url)
                <div class="ir-urls">
                    @if($int->local_url)
                    <div class="ir-url-row">
                        <span class="ir-url-tag tag-local">LOCAL</span>
                        <span class="ir-url-val">{{ $int->local_url }}</span>
                        <button onclick="copyText('{{ $int->local_url }}')" class="ir-edit-btn" style="padding:3px 8px;display:flex;align-items:center"><svg viewBox="0 0 24 24" style="width:12px;height:12px" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg></button>
                    </div>
                    @endif
                    @if($int->production_url)
                    <div class="ir-url-row">
                        <span class="ir-url-tag tag-prod">PROD</span>
                        <span class="ir-url-val">{{ $int->production_url }}</span>
                        <button onclick="copyText('{{ $int->production_url }}')" class="ir-edit-btn" style="padding:3px 8px;display:flex;align-items:center"><svg viewBox="0 0 24 24" style="width:12px;height:12px" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg></button>
                    </div>
                    @endif
                </div>
                @endif

                @if(!empty($int->env_keys))
                <div class="ir-env-row">
                    @foreach($int->env_keys as $key)
                    @php $set = !empty(env($key)); @endphp
                    <span class="env-chip {{ $set ? 'env-set' : 'env-missing' }}">
                        {{ $set ? '✓' : '✗' }} {{ $key }}
                    </span>
                    @endforeach
                </div>
                @endif

                @if(!empty($int->meta['stores']))
                <div class="ir-meta mt-1">Stores: {{ $int->meta['stores'] }}</div>
                @endif
                @if(!empty($int->meta['csrf_exempt']) && $int->meta['csrf_exempt'])
                <div class="ir-meta mt-1" style="color:#fb923c">⚠ CSRF exempt — verify request signature in controller</div>
                @endif
                @if(!empty($int->meta['watch_expires']))
                <div class="ir-meta mt-1">Watch expiry: {{ $int->meta['watch_expires'] }}</div>
                @endif

                @if($int->notes)
                <div class="ir-notes">{{ $int->notes }}</div>
                @endif

                @if(!empty($int->meta['console']))
                <div class="mt-2">
                    <a href="{{ $int->meta['console'] }}" target="_blank" class="text-xs font-semibold" style="color:var(--accent-text)">Open Console ↗</a>
                </div>
                @endif
            </div>
            <div class="ir-actions">
                <button onclick="openEditModal({{ $int->id }},'{{ addslashes($int->label) }}','{{ addslashes($int->description ?? '') }}','{{ addslashes($int->local_url ?? '') }}','{{ addslashes($int->production_url ?? '') }}','{{ addslashes($int->notes ?? '') }}')" class="ir-edit-btn">Edit</button>
                <form method="POST" action="{{ route('admin.integrations.destroy', $int->id) }}" onsubmit="return confirm('Remove?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="ir-del-btn">✕</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @empty
    <div class="ir-card" style="padding:32px;text-align:center;color:var(--text-muted)">No worker integrations registered yet.</div>
    @endforelse
</div>

{{-- ── GO-LIVE TAB ──────────────────────────────────────────────────────── --}}
<div id="tab-golive" style="display:none">
    <div class="ir-card">
        <div class="ir-section-hd">
            <h2>URLs to update before going live</h2>
            <span class="text-xs" style="color:var(--text-muted)">These are all local URLs that need a production equivalent</span>
        </div>
        <div style="display:grid;grid-template-columns:180px 1fr 24px 1fr;gap:0">
            <div style="padding:10px 24px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);border-bottom:1px solid var(--border)">Integration</div>
            <div style="padding:10px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#60a5fa;border-bottom:1px solid var(--border)">Local</div>
            <div style="border-bottom:1px solid var(--border)"></div>
            <div style="padding:10px 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#4ade80;border-bottom:1px solid var(--border)">Production</div>
        </div>
        @foreach($goLive as $int)
        <div style="display:grid;grid-template-columns:180px 1fr 24px 1fr;align-items:center;border-bottom:1px solid var(--border-subtle);padding:0">
            <div style="padding:14px 24px;font-size:12px;font-weight:600;color:var(--text-secondary)">
                {{ $int->label }}
                @if($int->scope === 'worker')<br><span class="text-xs" style="color:var(--text-muted);text-transform:uppercase;font-weight:700">{{ $int->worker_slug }}</span>@endif
            </div>
            <div style="padding:14px 12px;font-family:monospace;font-size:11px;color:#60a5fa;word-break:break-all">{{ $int->local_url }}</div>
            <div style="text-align:center;color:var(--text-muted);font-size:14px">→</div>
            <div style="padding:14px 12px">
                <form method="POST" action="{{ route('admin.integrations.update', $int->id) }}" style="display:flex;gap:6px;align-items:center">
                    @csrf @method('PUT')
                    <input type="hidden" name="label" value="{{ $int->label }}">
                    <input type="hidden" name="local_url" value="{{ $int->local_url }}">
                    <input type="hidden" name="notes" value="{{ $int->notes }}">
                    <input type="text" name="production_url" value="{{ $int->production_url }}" class="ir-input" style="font-family:monospace;font-size:11px" placeholder="https://unit.report/...">
                    <button type="submit" class="ir-edit-btn" style="white-space:nowrap;flex-shrink:0">Save</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── EDIT MODAL ───────────────────────────────────────────────────────── --}}
<div class="ir-modal-bg" id="editModal">
    <div class="ir-modal">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-bold" style="color:var(--text-primary)">Edit Integration</h3>
            <button onclick="closeModal('editModal')" style="color:var(--text-muted);font-size:20px;background:none;border:none;cursor:pointer">✕</button>
        </div>
        <form method="POST" id="editForm" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <div class="ir-label-sm">Label</div>
                <input type="text" name="label" id="edit_label" class="ir-input" required>
            </div>
            <div>
                <div class="ir-label-sm">Description <span style="font-weight:400;text-transform:none;letter-spacing:0">— what it does and what's expected</span></div>
                <textarea name="description" id="edit_description" class="ir-input" rows="3" style="resize:vertical"></textarea>
            </div>
            <div>
                <div class="ir-label-sm">Local URL</div>
                <input type="text" name="local_url" id="edit_local_url" class="ir-input" placeholder="http://localhost:8000/...">
            </div>
            <div>
                <div class="ir-label-sm">Production URL</div>
                <input type="text" name="production_url" id="edit_prod_url" class="ir-input" placeholder="https://unit.report/...">
            </div>
            <div>
                <div class="ir-label-sm">Notes <span style="font-weight:400;text-transform:none;letter-spacing:0">— warnings, deployment instructions</span></div>
                <textarea name="notes" id="edit_notes" class="ir-input" rows="2" style="resize:vertical"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="ir-edit-btn" style="flex:1;padding:9px;font-size:13px;border-color:rgba(var(--accent-rgb),.4);color:var(--accent-text)">Save Changes</button>
                <button type="button" onclick="closeModal('editModal')" class="ir-edit-btn" style="flex:1;padding:9px;font-size:13px">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ── ADD MODAL ────────────────────────────────────────────────────────── --}}
<div class="ir-modal-bg" id="addModal">
    <div class="ir-modal">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-bold" style="color:var(--text-primary)">Add Integration</h3>
            <button onclick="closeModal('addModal')" style="color:var(--text-muted);font-size:20px;background:none;border:none;cursor:pointer">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.integrations.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="ir-label-sm">Scope</div>
                    <select name="scope" class="ir-input" required onchange="toggleWorkerSlug(this.value)">
                        <option value="platform">Platform</option>
                        <option value="worker">Worker</option>
                    </select>
                </div>
                <div id="slugField" style="display:none">
                    <div class="ir-label-sm">Worker Slug</div>
                    <input type="text" name="worker_slug" class="ir-input" placeholder="ava, nova…">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="ir-label-sm">Service Key</div>
                    <input type="text" name="service" class="ir-input" placeholder="google_oauth" required>
                </div>
                <div>
                    <div class="ir-label-sm">Type</div>
                    <select name="type" class="ir-input" required>
                        <optgroup label="Auth & Callbacks">
                            <option value="oauth">OAuth — 3-legged user authorization</option>
                            <option value="callback_url">Callback URL — redirect destination after external flow</option>
                        </optgroup>
                        <optgroup label="Inbound">
                            <option value="webhook">Webhook — HTTP push from external service</option>
                            <option value="pubsub">Pub/Sub — Google Cloud push subscription</option>
                            <option value="websocket">WebSocket — real-time persistent connection</option>
                        </optgroup>
                        <optgroup label="Outbound / Credentials">
                            <option value="api_key">API Key — outbound calls with key auth</option>
                            <option value="sdk">SDK — library-based, no URL needed</option>
                        </optgroup>
                        <optgroup label="Infrastructure">
                            <option value="smtp">SMTP — mail server</option>
                            <option value="database">Database — direct DB connection</option>
                            <option value="storage">Storage — S3 / Spaces / Drive / SharePoint</option>
                            <option value="sftp">SFTP — file transfer (legacy enterprise)</option>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div>
                <div class="ir-label-sm">Label</div>
                <input type="text" name="label" class="ir-input" placeholder="Human-readable name" required>
            </div>
            <div>
                <div class="ir-label-sm">Description <span style="font-weight:400;text-transform:none;letter-spacing:0">— what it does and what's expected</span></div>
                <textarea name="description" class="ir-input" rows="3" style="resize:vertical" placeholder="Describe what this integration does, what credentials are needed, and any setup requirements."></textarea>
            </div>
            <div>
                <div class="ir-label-sm">Local URL</div>
                <input type="text" name="local_url" class="ir-input" placeholder="http://localhost:8000/...">
            </div>
            <div>
                <div class="ir-label-sm">Production URL</div>
                <input type="text" name="production_url" class="ir-input" placeholder="https://unit.report/...">
            </div>
            <div>
                <div class="ir-label-sm">ENV Keys <span style="color:var(--text-muted);font-weight:400">(comma-separated)</span></div>
                <input type="text" name="env_keys" class="ir-input" placeholder="SERVICE_KEY, SERVICE_SECRET">
            </div>
            <div>
                <div class="ir-label-sm">Notes</div>
                <textarea name="notes" class="ir-input" rows="2" style="resize:vertical"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="ir-edit-btn" style="flex:1;padding:9px;font-size:13px;border-color:rgba(var(--accent-rgb),.4);color:var(--accent-text)">Add Integration</button>
                <button type="button" onclick="closeModal('addModal')" class="ir-edit-btn" style="flex:1;padding:9px;font-size:13px">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showTab(name, btn) {
    ['platform','workers','golive'].forEach(t => {
        document.getElementById('tab-'+t).style.display = t === name ? 'block' : 'none';
    });
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function openEditModal(id, label, description, localUrl, prodUrl, notes) {
    document.getElementById('editForm').action = `/admin/integrations/${id}`;
    document.getElementById('edit_label').value       = label;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_local_url').value   = localUrl;
    document.getElementById('edit_prod_url').value    = prodUrl;
    document.getElementById('edit_notes').value       = notes;
    document.getElementById('editModal').classList.add('open');
}

function openAddModal() {
    document.getElementById('addModal').classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function toggleWorkerSlug(val) {
    document.getElementById('slugField').style.display = val === 'worker' ? 'block' : 'none';
}

function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        const el = document.createElement('div');
        el.textContent = 'Copied!';
        el.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#1e1e1e;color:#4ade80;padding:8px 16px;border-radius:10px;font-size:13px;font-weight:600;border:1px solid rgba(34,197,94,.3);z-index:9999';
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 1800);
    });
}

// Close modals on backdrop click
document.querySelectorAll('.ir-modal-bg').forEach(bg => {
    bg.addEventListener('click', e => { if (e.target === bg) bg.classList.remove('open'); });
});
</script>

</x-app-layout>

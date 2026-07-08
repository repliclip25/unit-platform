<x-app-layout title="Messaging">

<style>
/* ── tabs ── */
.msg-tabs    { display:flex;gap:4px;padding:4px;border-radius:12px;margin-bottom:24px;width:fit-content }
.msg-tab     { font-size:12px;font-weight:600;padding:7px 16px;border-radius:9px;border:none;cursor:pointer;
               transition:all .15s;color:var(--text-muted);background:transparent }
.msg-tab.on  { background:var(--bg-card);color:var(--text-primary);box-shadow:0 1px 4px rgba(0,0,0,.25) }
[data-theme="light"] .msg-tabs { background:#ebebeb }
[data-theme="dark"]  .msg-tabs { background:rgba(255,255,255,.06) }

/* ── sequence groups ── */
.seq-group      { margin-bottom:28px }
.seq-group-hd   { display:flex;align-items:center;gap:10px;margin-bottom:12px }
.seq-group-icon { width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0 }
.seq-group-title { font-size:13px;font-weight:700;color:var(--text-primary) }
.seq-group-sub   { font-size:11px;color:var(--text-muted) }

/* ── sequence timeline ── */
.seq-item    { display:flex;gap:0;position:relative }
.seq-line    { width:40px;display:flex;flex-direction:column;align-items:center;flex-shrink:0 }
.seq-dot     { width:10px;height:10px;border-radius:50%;margin-top:20px;flex-shrink:0;z-index:1 }
.seq-connector { flex:1;width:2px;background:var(--border);min-height:12px }
.seq-card    { flex:1;margin:6px 0 6px 8px;background:var(--bg-card);border:1px solid var(--border);
               border-radius:12px;padding:14px 18px;transition:border-color .15s;cursor:pointer }
.seq-card:hover { border-color:rgba(241,211,98,.4) }
.seq-day     { font-size:10px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);margin-bottom:3px }
.seq-label   { font-size:13px;font-weight:700;color:var(--text-primary) }
.seq-desc    { font-size:12px;color:var(--text-muted);line-height:1.5;margin-top:2px }
.seq-meta    { display:flex;align-items:center;gap:8px;margin-top:8px;flex-wrap:wrap }
.seq-pill    { font-size:10px;font-weight:600;padding:2px 8px;border-radius:99px }
.seq-pill-state { background:rgba(241,211,98,.1);color:var(--accent-text);border:1px solid rgba(241,211,98,.2) }
.seq-pill-on    { background:rgba(34,197,94,.1);color:#4ade80;border:1px solid rgba(34,197,94,.2) }
.seq-pill-off   { background:rgba(156,163,175,.08);color:var(--text-muted);border:1px solid var(--border) }

/* ── template editor ── */
.tpl-card    { background:var(--bg-card);border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:12px }
.tpl-head    { padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;cursor:pointer }
.tpl-head:hover .tpl-label { color:var(--accent-text) }
.tpl-badge   { font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px }
.tpl-badge-on  { background:rgba(34,197,94,.1);color:#4ade80;border:1px solid rgba(34,197,94,.2) }
.tpl-badge-off { background:rgba(156,163,175,.08);color:var(--text-muted);border:1px solid var(--border) }
.tpl-badge-wk  { background:rgba(96,165,250,.1);color:#60a5fa;border:1px solid rgba(96,165,250,.25) }
.tpl-badge-nl  { background:rgba(167,139,250,.1);color:#a78bfa;border:1px solid rgba(167,139,250,.25) }
.tpl-badge-pl  { background:rgba(251,191,36,.1);color:#fbbf24;border:1px solid rgba(251,191,36,.25) }
.tpl-label   { font-size:13px;font-weight:700;color:var(--text-primary);transition:color .15s }
.tpl-sub     { font-size:11px;color:var(--text-muted);margin-top:1px }
.tpl-body    { padding:0 20px 20px;display:none }
.tpl-body.open { display:block }
.tpl-input,.tpl-textarea { width:100%;background:var(--bg-raised);border:1px solid var(--border);
               border-radius:10px;padding:9px 12px;font-size:13px;color:var(--text-primary);
               outline:none;transition:border .15s }
.tpl-input:focus,.tpl-textarea:focus { border-color:var(--accent) }
.tpl-textarea { font-family:monospace;line-height:1.65;resize:vertical;min-height:160px }
.tpl-lbl     { font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
               color:var(--text-muted);margin-bottom:5px }
.tpl-hint    { font-size:11px;color:var(--text-muted);margin-top:4px }
.tpl-actions { display:flex;align-items:center;gap:8px;margin-top:16px;flex-wrap:wrap }
.tpl-group-label { font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;
                   color:var(--text-muted);margin:20px 0 8px;padding-left:2px }

/* ── AI Rewrite ── */
.ai-card     { background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:14px }
.ai-split    { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px }
.ai-pane     { background:var(--bg-raised);border:1px solid var(--border);border-radius:11px;padding:16px }
.ai-pane-hd  { font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:10px }
.ai-subject  { font-size:13px;font-weight:600;color:var(--text-primary);margin-bottom:8px }
.ai-body-txt { font-size:12px;color:var(--text-secondary);line-height:1.75;white-space:pre-wrap;font-family:monospace }
.ai-notes-box { font-size:12px;color:var(--text-muted);line-height:1.6;padding:10px 12px;
               border-radius:9px;margin-top:12px;font-style:italic }
[data-theme="dark"]  .ai-notes-box { background:rgba(241,211,98,.05);border:1px solid rgba(241,211,98,.15) }
[data-theme="light"] .ai-notes-box { background:#fffbea;border:1px solid #f0d96e;color:#5a4500 }
.ai-spinner  { display:none;align-items:center;gap:8px;font-size:13px;color:var(--text-muted);padding:24px 0 }
.ai-spinner svg { animation:spin 1s linear infinite }
@keyframes spin { to { transform:rotate(360deg) } }

/* ── Feedback ── */
.fb-card     { background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:18px 20px;margin-bottom:12px }
.fb-section-lbl { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
                  color:var(--accent-text);margin-bottom:4px }
.fb-text     { font-size:12px;color:var(--text-secondary);line-height:1.7;
               padding:10px 12px;border-radius:9px;background:var(--bg-raised);border:1px solid var(--border) }
.fb-empty    { text-align:center;padding:48px 24px;color:var(--text-muted);font-size:14px }

/* ── buttons ── */
.m-btn       { font-size:12px;font-weight:600;padding:7px 14px;border-radius:9px;cursor:pointer;transition:all .15s;white-space:nowrap }
.m-btn-gold  { background:var(--accent);color:#ffffff;border:none }
.m-btn-gold:hover { opacity:.88 }
.m-btn-out   { background:transparent;border:1px solid var(--border);color:var(--text-secondary) }
.m-btn-out:hover { border-color:var(--accent);color:var(--text-primary) }
.m-btn-red   { background:transparent;border:1px solid rgba(239,68,68,.25);color:#f87171 }
.m-btn-blue  { background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.25);color:#60a5fa }
.m-btn-blue:hover { background:rgba(96,165,250,.18) }

/* ── worker tabs — reuse msg-tab pattern ── */
.wk-tab-count { display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;
                border-radius:50%;font-size:9px;font-weight:800;background:rgba(255,255,255,.12);
                color:inherit;margin-left:5px }
.msg-tab.on .wk-tab-count { background:rgba(0,0,0,.15) }
.wk-tab-empty { font-style:italic }

@media(max-width:700px){ .ai-split { grid-template-columns:1fr } }
</style>

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-semibold" style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl text-sm font-semibold" style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:#f87171">{{ session('error') }}</div>
@endif

<div class="flex items-start justify-between mb-4">
    <div>
        <h1 class="text-xl font-bold" style="color:var(--text-primary)">Messaging</h1>
        <p class="text-sm mt-0.5" style="color:var(--text-muted)">Worker-specific onboarding, platform onboarding for non-deployers, and the 90-day newsletter arc — all DB-backed and AI-rewritable.</p>
    </div>
</div>

{{-- Tabs --}}
<div class="msg-tabs">
    @foreach([['sequences','Sequences'],['templates','Templates'],['rewrite','AI Rewrite'],['feedback','Feedback Sources']] as [$t,$l])
    <button class="msg-tab {{ $tab===$t ? 'on' : '' }}" onclick="switchTab('{{ $t }}')">{{ $l }}</button>
    @endforeach
</div>

{{-- ══════════════════════ SEQUENCES TAB ══════════════════════ --}}
<div id="tab-sequences" class="{{ $tab==='sequences' ? '' : 'hidden' }}">

    <p class="text-sm mb-6" style="color:var(--text-muted);max-width:620px">The job runs daily. Each template key is sent at most once per tenant — ever. Worker onboarding fires based on first_worker_slug. Platform onboarding fires only if no worker has been deployed. Newsletter goes to all active tenants at the specified day milestone.</p>

    {{-- Worker Onboarding --}}
    <div class="seq-group">
        <div class="seq-group-hd">
            <div class="seq-group-icon" style="background:rgba(96,165,250,.12);border:1px solid rgba(96,165,250,.2)">
                <svg style="width:14px;height:14px;color:#60a5fa" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
            </div>
            <div>
                <div class="seq-group-title">Worker Onboarding</div>
                <div class="seq-group-sub">Per-worker sequences. Only fires for tenants whose first deployed worker matches the worker slug.</div>
            </div>
        </div>

        @php $workerGroups = $grouped->get('worker_onboarding', collect())->groupBy('worker_slug') @endphp
        @forelse($workerGroups as $slug => $slugTemplates)
        <div style="margin-bottom:16px">
            <div style="font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:3px 10px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;margin-bottom:8px;background:rgba(96,165,250,.08);border:1px solid rgba(96,165,250,.2);color:#60a5fa">
                <svg style="width:8px;height:8px" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                {{ strtoupper($slug) }}
            </div>
            @foreach($slugTemplates as $tpl)
            <div class="seq-item">
                <div class="seq-line">
                    <div class="seq-dot" style="background:{{ $tpl->active ? 'var(--accent)' : 'var(--border)' }}"></div>
                    @if(!$loop->last)<div class="seq-connector"></div>@endif
                </div>
                <div class="seq-card" onclick="switchTab('templates');openTpl({{ $tpl->id }})">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="seq-day">Day {{ $tpl->day_offset }} · {{ $tpl->trigger_state ?? 'any' }}</div>
                            <div class="seq-label">{{ $tpl->label }}</div>
                            <div class="seq-desc">{{ $tpl->description }}</div>
                            <div class="seq-meta">
                                <span class="seq-pill seq-pill-state">{{ $tpl->trigger_condition }}</span>
                                <span class="{{ $tpl->active ? 'seq-pill seq-pill-on' : 'seq-pill seq-pill-off' }}">{{ $tpl->active ? 'Active' : 'Paused' }}</span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0" style="min-width:110px">
                            <div class="text-xs" style="color:var(--text-muted)">{{ Str::limit($tpl->subject, 36) }}</div>
                            @if($tpl->last_ai_rewrite_at)
                            <div class="text-xs mt-1" style="color:var(--text-muted)">AI: {{ \Carbon\Carbon::parse($tpl->last_ai_rewrite_at)->diffForHumans() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @empty
        <div style="padding:24px;border-radius:12px;border:1px dashed var(--border);color:var(--text-muted);font-size:13px;text-align:center">No worker onboarding templates. Create templates with sequence = worker_onboarding in the Templates tab.</div>
        @endforelse
    </div>

    {{-- Platform Onboarding --}}
    <div class="seq-group">
        <div class="seq-group-hd">
            <div class="seq-group-icon" style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.25)">
                <svg style="width:14px;height:14px;color:#fbbf24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <div class="seq-group-title">Platform Onboarding</div>
                <div class="seq-group-sub">Only fires for tenants who never deployed any worker. Worker-agnostic — explores their need.</div>
            </div>
        </div>
        @foreach($grouped->get('platform_onboarding', collect()) as $tpl)
        <div class="seq-item">
            <div class="seq-line">
                <div class="seq-dot" style="background:{{ $tpl->active ? '#fbbf24' : 'var(--border)' }}"></div>
                @if(!$loop->last)<div class="seq-connector"></div>@endif
            </div>
            <div class="seq-card" onclick="switchTab('templates');openTpl({{ $tpl->id }})">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="seq-day">Day {{ $tpl->day_offset }}</div>
                        <div class="seq-label">{{ $tpl->label }}</div>
                        <div class="seq-desc">{{ $tpl->description }}</div>
                        <div class="seq-meta">
                            <span class="seq-pill" style="background:rgba(251,191,36,.1);color:#fbbf24;border:1px solid rgba(251,191,36,.25)">no worker deployed</span>
                            <span class="{{ $tpl->active ? 'seq-pill seq-pill-on' : 'seq-pill seq-pill-off' }}">{{ $tpl->active ? 'Active' : 'Paused' }}</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0" style="min-width:110px">
                        <div class="text-xs" style="color:var(--text-muted)">{{ Str::limit($tpl->subject, 36) }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Newsletter --}}
    <div class="seq-group">
        <div class="seq-group-hd">
            <div class="seq-group-icon" style="background:rgba(167,139,250,.1);border:1px solid rgba(167,139,250,.25)">
                <svg style="width:14px;height:14px;color:#a78bfa" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            </div>
            <div>
                <div class="seq-group-title">Newsletter — 90-Day Arc</div>
                <div class="seq-group-sub">All active tenants. One send per milestone, forever deduplicated. AI-rewritable using feedback sources + topic focus.</div>
            </div>
        </div>
        @foreach($grouped->get('newsletter', collect()) as $tpl)
        <div class="seq-item">
            <div class="seq-line">
                <div class="seq-dot" style="background:{{ $tpl->active ? '#a78bfa' : 'var(--border)' }}"></div>
                @if(!$loop->last)<div class="seq-connector"></div>@endif
            </div>
            <div class="seq-card" onclick="switchTab('templates');openTpl({{ $tpl->id }})">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="seq-day">Day {{ $tpl->day_offset }}</div>
                        <div class="seq-label">{{ $tpl->label }}</div>
                        <div class="seq-desc">{{ $tpl->topic ?? $tpl->description }}</div>
                        <div class="seq-meta">
                            <span class="seq-pill" style="background:rgba(167,139,250,.1);color:#a78bfa;border:1px solid rgba(167,139,250,.25)">all tenants</span>
                            <span class="{{ $tpl->active ? 'seq-pill seq-pill-on' : 'seq-pill seq-pill-off' }}">{{ $tpl->active ? 'Active' : 'Paused' }}</span>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0" style="min-width:110px">
                        <div class="text-xs" style="color:var(--text-muted)">{{ Str::limit($tpl->subject, 36) }}</div>
                        @if($tpl->last_ai_rewrite_at)
                        <div class="text-xs mt-1" style="color:var(--text-muted)">AI: {{ \Carbon\Carbon::parse($tpl->last_ai_rewrite_at)->diffForHumans() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>

{{-- ══════════════════════ TEMPLATES TAB ══════════════════════ --}}
<div id="tab-templates" class="{{ $tab==='templates' ? '' : 'hidden' }}">
    <p class="text-sm mb-5" style="color:var(--text-muted)">Use <code style="font-size:11px;padding:1px 5px;border-radius:4px;background:var(--bg-raised)">{name}</code> and <code style="font-size:11px;padding:1px 5px;border-radius:4px;background:var(--bg-raised)">{app_url}</code> — replaced at send time.</p>

    {{-- ── Worker Onboarding — worker picker ── --}}
    <div class="tpl-group-label" style="margin-top:0">Worker Onboarding</div>

    @php
    $workerOnboardingBySlug = $templates->where('sequence','worker_onboarding')->groupBy('worker_slug');
    $registeredSlugs = $allWorkers->pluck('slug');
    // workers in registry but no templates yet
    $unsetWorkers = $allWorkers->filter(fn($w) => !$workerOnboardingBySlug->has($w->slug));
    // first active slug to show by default
    $firstSlug = $workerOnboardingBySlug->keys()->first() ?? '';
    @endphp

    {{-- Worker toggle --}}
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px">
        <div class="msg-tabs" style="margin-bottom:0">
            @foreach($workerOnboardingBySlug as $slug => $__)
            @php $wInfo = $allWorkers->firstWhere('slug',$slug) @endphp
            <button type="button" class="msg-tab {{ $loop->first ? 'on' : '' }}" onclick="switchWorker('{{ $slug }}')" id="wktab-{{ $slug }}" data-slug="{{ $slug }}">
                {{ $wInfo->name ?? strtoupper($slug) }}
                <span class="wk-tab-count">{{ $workerOnboardingBySlug[$slug]->count() }}</span>
            </button>
            @endforeach

            @foreach($unsetWorkers as $w)
            <button type="button" class="msg-tab wk-tab-empty" onclick="switchWorker('{{ $w->slug }}')" id="wktab-{{ $w->slug }}" data-slug="{{ $w->slug }}">
                + {{ $w->name }}
            </button>
            @endforeach
        </div>

        <button type="button" class="m-btn m-btn-out" style="font-size:11px;padding:5px 12px" onclick="openAddStep(document.querySelector('#tab-templates .msg-tab.on')?.dataset?.slug || '{{ $firstSlug }}')">＋ Add Step</button>
    </div>

    {{-- Per-worker template panels --}}
    @foreach($workerOnboardingBySlug as $slug => $slugTemplates)
    <div class="wk-panel" id="wkpanel-{{ $slug }}" style="{{ !$loop->first ? 'display:none' : '' }}">
        @foreach($slugTemplates as $tpl)
        @include('admin.messaging-tpl-card', ['tpl' => $tpl, 'badge' => 'tpl-badge tpl-badge-wk', 'badgeText' => strtoupper($slug)])
        @endforeach
    </div>
    @endforeach

    @foreach($unsetWorkers as $w)
    <div class="wk-panel" id="wkpanel-{{ $w->slug }}" style="display:none">
        <div style="padding:28px 20px;border-radius:14px;border:1px dashed var(--border);text-align:center">
            <div style="font-size:14px;font-weight:600;color:var(--text-primary);margin-bottom:6px">No onboarding sequence for {{ $w->name }} yet</div>
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:16px">Create the first step — tenants who deploy {{ $w->name }} as their first worker will receive it.</div>
            <button type="button" class="m-btn m-btn-gold" onclick="openAddStep('{{ $w->slug }}')">＋ Create First Step</button>
        </div>
    </div>
    @endforeach

    {{-- ── Welcome ── --}}
    <div class="tpl-group-label">Welcome</div>
    <p style="font-size:11px;color:var(--text-muted);margin-bottom:10px">Sent immediately on registration. Worker-agnostic — prospects haven't deployed anything yet.</p>
    @foreach($templates->where('sequence','welcome') as $tpl)
    @include('admin.messaging-tpl-card', ['tpl' => $tpl, 'badge' => 'tpl-badge', 'badgeText' => 'Welcome', 'badgeStyle' => 'background:rgba(52,211,153,.1);color:#34d399;border:1px solid rgba(52,211,153,.25)'])
    @endforeach

    {{-- ── Platform Onboarding ── --}}
    <div class="tpl-group-label">Platform Onboarding</div>
    <p style="font-size:11px;color:var(--text-muted);margin-bottom:10px">Only fires for tenants who never deploy a worker. Day 3, 7, and 14 check-ins.</p>
    @foreach($templates->where('sequence','platform_onboarding') as $tpl)
    @include('admin.messaging-tpl-card', ['tpl' => $tpl, 'badge' => 'tpl-badge tpl-badge-pl', 'badgeText' => 'Platform'])
    @endforeach

    {{-- ── Memory Enrichment ── --}}
    <div class="tpl-group-label">Memory Enrichment</div>
    <p style="font-size:11px;color:var(--text-muted);margin-bottom:10px">Platform-scoped. Fires Day 1, 3, and 7 after onboarding for tenants whose deployed workers use memory but haven't reached the health threshold (5 complete records). Stops automatically once healthy.</p>
    @foreach($templates->where('sequence','memory_enrichment') as $tpl)
    @include('admin.messaging-tpl-card', ['tpl' => $tpl, 'badge' => 'tpl-badge', 'badgeText' => 'Memory', 'badgeStyle' => 'background:rgba(var(--accent-rgb),0.1);color:var(--accent-text);border:1px solid rgba(var(--accent-rgb),0.3)'])
    @endforeach

    {{-- ── Newsletter ── --}}
    <div class="tpl-group-label">Newsletter — 90-Day Arc</div>
    <p style="font-size:11px;color:var(--text-muted);margin-bottom:10px">Goes to all active tenants. Day 7, 14, 30, 60, 90 milestones. One send per key, ever.</p>
    @foreach($templates->where('sequence','newsletter') as $tpl)
    @include('admin.messaging-tpl-card', ['tpl' => $tpl, 'badge' => 'tpl-badge tpl-badge-nl', 'badgeText' => 'Newsletter'])
    @endforeach

    {{-- ── Transactional ── --}}
    <div class="tpl-group-label">Transactional — Billing</div>
    <p style="font-size:11px;color:var(--text-muted);margin-bottom:10px">Triggered by Stripe events. Subject and body are editable; the send logic is wired to webhooks.</p>
    @foreach($templates->where('sequence','transactional') as $tpl)
    @include('admin.messaging-tpl-card', ['tpl' => $tpl, 'badge' => 'tpl-badge', 'badgeText' => 'Billing', 'badgeStyle' => 'background:rgba(251,113,133,.08);color:#fb7185;border:1px solid rgba(251,113,133,.2)'])
    @endforeach

    {{-- ── Influencer ── --}}
    <div class="tpl-group-label">Influencer / Partner</div>
    <p style="font-size:11px;color:var(--text-muted);margin-bottom:10px">Application received, approval, first signup, paid conversion, and tier upgrade notifications sent to influencer partners.</p>
    @foreach($templates->where('sequence','influencer') as $tpl)
    @include('admin.messaging-tpl-card', ['tpl' => $tpl, 'badge' => 'tpl-badge', 'badgeText' => 'Influencer', 'badgeStyle' => 'background:rgba(251,191,36,.1);color:#fbbf24;border:1px solid rgba(251,191,36,.3)'])
    @endforeach

    {{-- ── Referral ── --}}
    <div class="tpl-group-label">Referral</div>
    <p style="font-size:11px;color:var(--text-muted);margin-bottom:10px">Referred tenants get a distinct welcome acknowledging the referral and their bonus transactions. Peer referrers are notified on first signup and on conversion.</p>
    @foreach($templates->where('sequence','referral') as $tpl)
    @php $isInternal = str_contains($tpl->key, '_peer_'); @endphp
    @include('admin.messaging-tpl-card', ['tpl' => $tpl, 'badge' => 'tpl-badge', 'badgeText' => $isInternal ? 'Referrer' : 'Referred',
        'badgeStyle' => $isInternal
            ? 'background:rgba(167,139,250,.1);color:#a78bfa;border:1px solid rgba(167,139,250,.25)'
            : 'background:rgba(52,211,153,.1);color:#34d399;border:1px solid rgba(52,211,153,.25)'
    ])
    @endforeach

    {{-- ── Inbound ── --}}
    <div class="tpl-group-label">Inbound — Worker Requests & Subscribe</div>
    <p style="font-size:11px;color:var(--text-muted);margin-bottom:10px">Auto-replies and internal notifications triggered by public form submissions.</p>
    @foreach($templates->where('sequence','inbound') as $tpl)
    @php
    $isInternal = str_contains($tpl->key, '_admin');
    @endphp
    @include('admin.messaging-tpl-card', ['tpl' => $tpl,
        'badge' => 'tpl-badge',
        'badgeText' => $isInternal ? 'Internal' : 'Auto-Reply',
        'badgeStyle' => $isInternal
            ? 'background:rgba(156,163,175,.1);color:var(--text-muted);border:1px solid var(--border)'
            : 'background:rgba(96,165,250,.1);color:#60a5fa;border:1px solid rgba(96,165,250,.25)'
    ])
    @endforeach
</div>

{{-- Add Step modal --}}
<div id="add-step-modal" style="display:none;position:fixed;inset:0;z-index:1000;align-items:center;justify-content:center">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.55)" onclick="closeAddStep()"></div>
    <div style="position:relative;width:100%;max-width:520px;margin:20px;background:var(--bg-card);border:1px solid var(--border);border-radius:18px;padding:28px;max-height:90vh;overflow-y:auto">
        <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px">Add Onboarding Step</div>
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:20px">Adding to: <strong id="add-step-worker-label" class="ac-text"></strong></div>
        <input type="hidden" id="add-step-worker-slug">
        <div style="display:flex;flex-direction:column;gap:12px">
            <div>
                <div class="tpl-lbl">Day offset</div>
                <input id="as-day" type="number" min="0" class="tpl-input" placeholder="e.g. 3" value="3">
            </div>
            <div>
                <div class="tpl-lbl">Trigger state</div>
                <select id="as-trigger" class="tpl-input">
                    <option value="no_gmail">no_gmail — Gmail not connected</option>
                    <option value="no_tx">no_tx — Connected but no emails processed</option>
                    <option value="no_activity">no_activity — Deployed but no activity</option>
                    <option value="any">any — Always send on this day</option>
                </select>
            </div>
            <div>
                <div class="tpl-lbl">Label</div>
                <input id="as-label" class="tpl-input" placeholder="e.g. Day 3 — No Gmail connected">
            </div>
            <div>
                <div class="tpl-lbl">Description</div>
                <input id="as-desc" class="tpl-input" placeholder="When and why this email fires">
            </div>
            <div>
                <div class="tpl-lbl">Subject</div>
                <input id="as-subject" class="tpl-input" placeholder="Email subject line">
            </div>
            <div>
                <div class="tpl-lbl">From Name</div>
                <input id="as-from" class="tpl-input" value="Franklin at UNIT">
            </div>
            <div>
                <div class="tpl-lbl">Body</div>
                <textarea id="as-body" class="tpl-textarea" style="min-height:140px" placeholder="Hi {name},&#10;&#10;...&#10;&#10;Franklin at UNIT"></textarea>
                <div class="tpl-hint">Placeholders: {name} · {app_url}</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;margin-top:20px">
            <button type="button" class="m-btn m-btn-gold" onclick="submitAddStep()">Create Step</button>
            <button type="button" class="m-btn m-btn-out" onclick="closeAddStep()">Cancel</button>
        </div>
    </div>
</div>

{{-- ══════════════════════ AI REWRITE TAB ══════════════════════ --}}
<div id="tab-rewrite" class="{{ $tab==='rewrite' ? '' : 'hidden' }}">
    <div class="ai-card">
        <div>
            <h2 class="text-sm font-bold" style="color:var(--text-primary)">AI Rewrite</h2>
            <p class="text-xs mt-1" style="color:var(--text-muted);max-width:520px">Claude reads the template, your notes, real user language from intake forms, and the newsletter topic focus. Review both versions before accepting.</p>
        </div>

        <div style="margin-top:16px;display:grid;grid-template-columns:1fr auto;gap:10px;align-items:flex-end">
            <div>
                <div class="tpl-lbl">Template</div>
                <div style="position:relative">
                    <select id="rw-select" class="tpl-input" style="appearance:none;padding-right:32px" onchange="loadRewriteTemplate(this)">
                        <option value="">— Select template —</option>
                        @foreach([
                            ['worker_onboarding','Worker Onboarding'],
                            ['platform_onboarding','Platform Onboarding'],
                            ['newsletter','Newsletter — 90 Days'],
                            ['welcome','Welcome'],
                            ['transactional','Transactional — Billing'],
                            ['inbound','Inbound — Auto-Replies'],
                        ] as [$sk,$sl])
                        @if($templates->where('sequence',$sk)->isNotEmpty())
                        <optgroup label="{{ $sl }}">
                            @foreach($templates->where('sequence',$sk) as $tpl)
                            <option value="{{ $tpl->id }}" data-subject="{{ $tpl->subject }}" data-body="{{ $tpl->body }}" data-label="{{ $tpl->label }}" data-topic="{{ $tpl->topic ?? '' }}">{{ $tpl->label }}</option>
                            @endforeach
                        </optgroup>
                        @endif
                        @endforeach
                    </select>
                    <svg style="position:absolute;right:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--text-muted);pointer-events:none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
            <button class="m-btn m-btn-blue" onclick="runRewrite()" id="rw-btn" disabled>
                <svg style="width:12px;height:12px;display:inline;margin-right:4px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Generate
            </button>
        </div>

        <div style="margin-top:12px">
            <div class="tpl-lbl">Rewrite notes (optional)</div>
            <input id="rw-notes" class="tpl-input" placeholder="e.g. shorter, more direct, focus on missed revenue risk">
        </div>

        <div class="ai-spinner" id="rw-spinner">
            <svg style="width:18px;height:18px;color:var(--accent)" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-dashoffset="10"/></svg>
            Claude is reading the template and feedback sources…
        </div>

        <div class="ai-split" id="rw-split" style="display:none">
            <div class="ai-pane">
                <div class="ai-pane-hd">Current</div>
                <div class="ai-subject" id="rw-orig-subj"></div>
                <div class="ai-body-txt" id="rw-orig-body"></div>
            </div>
            <div class="ai-pane" style="border-color:rgba(241,211,98,.3)">
                <div class="ai-pane-hd" class="ac-text">AI Suggestion</div>
                <div class="ai-subject" id="rw-new-subj" class="ac-text"></div>
                <div class="ai-body-txt" id="rw-new-body"></div>
            </div>
        </div>

        <div class="ai-notes-box" id="rw-ai-notes" style="display:none"></div>

        <div id="rw-actions" style="display:none;gap:8px;margin-top:14px" class="flex">
            <button class="m-btn m-btn-gold" onclick="acceptRewrite()">Accept & Save</button>
            <button class="m-btn m-btn-out" onclick="resetRewrite()">Discard</button>
        </div>
    </div>

    <div class="ai-card" style="margin-top:4px">
        <div class="text-xs font-bold uppercase tracking-wider mb-3" style="color:var(--text-muted);letter-spacing:.08em">Memory Sources Used in Every Rewrite</div>
        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach([
                ['Pain Points','worker_requests.pain_points','Language prospects use to describe what\'s broken in their process.'],
                ['Current Process','worker_requests.current_process','How prospects describe the manual work they want to automate.'],
                ['Newsletter Topic','platform_email_templates.topic','Topic/focus per newsletter template — tells Claude what angle to write from.'],
            ] as [$name,$field,$desc])
            <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;border-radius:10px;background:var(--bg-raised);border:1px solid var(--border)">
                <div style="width:8px;height:8px;border-radius:50%;background:#4ade80;flex-shrink:0;margin-top:4px"></div>
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $name }}</div>
                    <div style="font-size:11px;font-family:monospace;color:var(--text-muted);margin:2px 0 4px">{{ $field }}</div>
                    <div style="font-size:12px;color:var(--text-secondary)">{{ $desc }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══════════════════════ FEEDBACK SOURCES TAB ══════════════════════ --}}
<div id="tab-feedback" class="{{ $tab==='feedback' ? '' : 'hidden' }}">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:20px">
        <p class="text-sm" style="color:var(--text-muted);max-width:560px">Real language from intake forms — the exact words prospects use to describe their problems. Claude reads this when rewriting any template.</p>
        <div style="font-size:12px;color:var(--text-muted);flex-shrink:0">{{ count($feedbackSources) }} entries</div>
    </div>

    @if(count($feedbackSources))
    @foreach($feedbackSources as $fb)
    <div class="fb-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <div style="font-size:13px;font-weight:600;color:var(--text-primary)">{{ $fb->name }}{{ $fb->company ? ' · '.$fb->company : '' }}</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ \Carbon\Carbon::parse($fb->created_at)->format('M j, Y') }}</div>
        </div>
        @if($fb->pain_points)
        <div style="margin-bottom:10px">
            <div class="fb-section-lbl">Pain Points</div>
            <div class="fb-text">{{ $fb->pain_points }}</div>
        </div>
        @endif
        @if($fb->current_process)
        <div>
            <div class="fb-section-lbl">Current Process</div>
            <div class="fb-text">{{ $fb->current_process }}</div>
        </div>
        @endif
    </div>
    @endforeach
    @else
    <div class="fb-empty">
        <svg style="width:32px;height:32px;color:var(--text-muted);margin:0 auto 12px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        <p>No intake form submissions yet. Once prospects submit worker requests, their language feeds into AI rewrites.</p>
    </div>
    @endif
</div>

<script>
var _rwTemplateId = null, _rwNewSubject = null, _rwNewBody = null, _rwNewNotes = null;

function switchTab(t) {
    ['sequences','templates','rewrite','feedback'].forEach(function(id){
        document.getElementById('tab-'+id).classList.toggle('hidden', id!==t);
    });
    document.querySelectorAll('.msg-tab').forEach(function(btn,i){
        btn.classList.toggle('on', ['sequences','templates','rewrite','feedback'][i]===t);
    });
    if (t === 'feedback') { window.location.href = '{{ route("admin.messaging") }}?tab=feedback'; }
}

function toggleTpl(id) {
    var body = document.getElementById('tpl-body-'+id);
    var chev = document.getElementById('tpl-chevron-'+id);
    var open = body.classList.toggle('open');
    chev.style.transform = open ? 'rotate(180deg)' : '';
}

function openTpl(id) {
    var body = document.getElementById('tpl-body-'+id);
    var chev = document.getElementById('tpl-chevron-'+id);
    if (!body.classList.contains('open')) {
        body.classList.add('open');
        if (chev) chev.style.transform = 'rotate(180deg)';
    }
    var card = document.getElementById('tpl-card-'+id);
    if (card) setTimeout(function(){ card.scrollIntoView({behavior:'smooth',block:'start'}) }, 50);
}

function openRewrite(id, subject, body) {
    switchTab('rewrite');
    var sel = document.getElementById('rw-select');
    sel.value = id;
    loadRewriteTemplate(sel);
}

function loadRewriteTemplate(sel) {
    var opt = sel.options[sel.selectedIndex];
    _rwTemplateId = sel.value || null;
    document.getElementById('rw-btn').disabled = !_rwTemplateId;
    document.getElementById('rw-orig-subj').textContent = opt.dataset.subject || '';
    document.getElementById('rw-orig-body').textContent  = opt.dataset.body    || '';
    document.getElementById('rw-split').style.display    = sel.value ? 'grid' : 'none';
    document.getElementById('rw-ai-notes').style.display = 'none';
    document.getElementById('rw-actions').style.display  = 'none';
    _rwNewSubject = null; _rwNewBody = null;
}

function runRewrite() {
    if (!_rwTemplateId) return;
    var spinner = document.getElementById('rw-spinner');
    var btn     = document.getElementById('rw-btn');
    spinner.style.display = 'flex';
    btn.disabled = true;
    fetch('{{ url("/admin/messaging") }}/' + _rwTemplateId + '/rewrite', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ notes: document.getElementById('rw-notes').value })
    })
    .then(r => r.json())
    .then(data => {
        spinner.style.display = 'none'; btn.disabled = false;
        if (data.error) { alert(data.error); return; }
        _rwNewSubject = data.subject; _rwNewBody = data.body; _rwNewNotes = data.notes;
        document.getElementById('rw-new-subj').textContent  = data.subject;
        document.getElementById('rw-new-body').textContent   = data.body;
        document.getElementById('rw-split').style.display    = 'grid';
        var nb = document.getElementById('rw-ai-notes');
        nb.textContent = data.notes || ''; nb.style.display = data.notes ? 'block' : 'none';
        document.getElementById('rw-actions').style.display = 'flex';
    })
    .catch(err => { spinner.style.display='none'; btn.disabled=false; alert('Failed: '+err.message); });
}

function acceptRewrite() {
    if (!_rwTemplateId || !_rwNewSubject) return;
    fetch('{{ url("/admin/messaging") }}/' + _rwTemplateId + '/accept', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ subject: _rwNewSubject, body: _rwNewBody, notes: _rwNewNotes })
    })
    .then(r => r.json())
    .then(() => { window.location.href = '{{ route("admin.messaging") }}?tab=templates'; })
    .catch(err => alert('Save failed: '+err.message));
}

function resetRewrite() {
    _rwNewSubject = null; _rwNewBody = null;
    document.getElementById('rw-new-subj').textContent  = '';
    document.getElementById('rw-new-body').textContent   = '';
    document.getElementById('rw-ai-notes').style.display = 'none';
    document.getElementById('rw-actions').style.display  = 'none';
}

function doTestSend(id, btn) {
    var orig = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Sending…';
    fetch('{{ url("/admin/messaging") }}/' + id + '/test-send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = orig;
        if (data.error) { showFlash(data.error, 'error'); return; }
        showFlash(data.message || 'Test sent', 'success');
    })
    .catch(err => { btn.disabled=false; btn.textContent=orig; showFlash('Send failed: '+err.message,'error'); });
}

function doReset(id) {
    if (!confirm('Reset to factory default? Your edits will be lost.')) return;
    fetch('{{ url("/admin/messaging") }}/' + id + '/reset', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { showFlash(data.error, 'error'); return; }
        showFlash('Template reset to default.', 'success');
        setTimeout(() => window.location.href = '{{ route("admin.messaging") }}?tab=templates', 800);
    })
    .catch(err => showFlash('Reset failed: '+err.message,'error'));
}

function switchWorker(slug) {
    document.querySelectorAll('.wk-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('#tab-templates .msg-tabs .msg-tab').forEach(t => t.classList.remove('on'));
    var panel = document.getElementById('wkpanel-' + slug);
    var tab   = document.getElementById('wktab-' + slug);
    if (panel) panel.style.display = 'block';
    if (tab)   tab.classList.add('on');
}

function openAddStep(slug) {
    var wInfo = {
        @foreach($allWorkers as $w)
        '{{ $w->slug }}': '{{ $w->name }}',
        @endforeach
    };
    document.getElementById('add-step-worker-slug').value = slug;
    document.getElementById('add-step-worker-label').textContent = wInfo[slug] || slug.toUpperCase();
    // pre-fill label
    document.getElementById('as-label').value = '';
    document.getElementById('as-subject').value = '';
    document.getElementById('as-body').value = '';
    document.getElementById('as-day').value = '3';
    document.getElementById('as-trigger').value = 'no_gmail';
    var modal = document.getElementById('add-step-modal');
    modal.style.display = 'flex';
}

function closeAddStep() {
    document.getElementById('add-step-modal').style.display = 'none';
}

function submitAddStep() {
    var slug = document.getElementById('add-step-worker-slug').value;
    var day  = document.getElementById('as-day').value;
    var trigger = document.getElementById('as-trigger').value;
    var triggerLabels = { no_gmail:'day_offset = '+day+' AND no Gmail credentials', no_tx:'day_offset = '+day+' AND has Gmail AND no transactions', no_activity:'day_offset = '+day+' AND has worker AND no transactions', any:'day_offset = '+day };

    var payload = {
        sequence:          'worker_onboarding',
        worker_slug:       slug,
        day_offset:        parseInt(day),
        trigger_state:     trigger,
        trigger_condition: triggerLabels[trigger] || ('day_offset = '+day),
        label:             document.getElementById('as-label').value,
        description:       document.getElementById('as-desc').value,
        subject:           document.getElementById('as-subject').value,
        from_name:         document.getElementById('as-from').value,
        body:              document.getElementById('as-body').value,
    };

    if (!payload.label || !payload.subject || !payload.body) {
        showFlash('Label, subject, and body are required.', 'error'); return;
    }

    fetch('{{ route("admin.messaging.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { showFlash(data.error, 'error'); return; }
        closeAddStep();
        showFlash('Step created.', 'success');
        setTimeout(() => window.location.href = '{{ route("admin.messaging") }}?tab=templates', 700);
    })
    .catch(err => showFlash('Failed: ' + err.message, 'error'));
}

function showFlash(msg, type) {
    var el = document.createElement('div');
    el.textContent = msg;
    el.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;padding:12px 20px;border-radius:12px;font-size:13px;font-weight:600;max-width:360px;box-shadow:0 4px 20px rgba(0,0,0,.3);transition:opacity .4s';
    if (type === 'success') {
        el.style.background = 'rgba(34,197,94,.15)'; el.style.border = '1px solid rgba(34,197,94,.3)'; el.style.color = '#4ade80';
    } else {
        el.style.background = 'rgba(239,68,68,.1)'; el.style.border = '1px solid rgba(239,68,68,.25)'; el.style.color = '#f87171';
    }
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity='0'; setTimeout(()=>el.remove(),400); }, 3500);
}
</script>

</x-app-layout>

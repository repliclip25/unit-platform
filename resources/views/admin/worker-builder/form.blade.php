<x-app-layout :title="$worker ? 'Edit Worker: ' . $worker->name : 'Register Worker'">
<div style="max-width:900px;margin:0 auto;padding:32px 24px">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p style="font-size:11px;font-weight:600;letter-spacing:0.08em;color:var(--text-muted);text-transform:uppercase;margin-bottom:4px">
                Admin · Worker Builder
            </p>
            <h1 style="font-size:22px;font-weight:700;color:var(--text-primary)">
                {{ $worker ? 'Edit Worker DNA' : 'Register New Worker' }}
            </h1>
            @if($worker)
            <p style="font-size:12px;font-family:monospace;color:var(--text-muted);margin-top:4px">{{ $worker->slug }} · v{{ $worker->version }}</p>
            @endif
        </div>
        <a href="{{ route('admin.workers.index') }}" style="font-size:12px;color:var(--text-muted);text-decoration:none">← Back</a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    {{-- Scaffold + Status bar (edit only) --}}
    @if($worker)
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:14px 16px;margin-bottom:20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <span style="font-size:11px;color:var(--text-muted)">Status:</span>
        <form method="POST" action="{{ route('admin.workers.status', $worker->slug) }}" style="margin:0;display:flex;gap:8px;align-items:center">
            @csrf
            <select name="status" style="font-size:12px;padding:5px 8px;border-radius:8px;border:1px solid var(--border);background:var(--bg-raised);color:var(--text-primary)">
                @foreach($statuses as $key => $meta)
                <option value="{{ $key }}" {{ $worker->status === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                @endforeach
            </select>
            <button type="submit" style="font-size:11px;font-weight:600;padding:5px 10px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-primary);cursor:pointer">Update</button>
        </form>
        <div style="margin-left:auto;display:flex;gap:8px">
            <form method="POST" action="{{ route('admin.workers.scaffold', $worker->slug) }}" style="margin:0">
                @csrf
                <button type="submit" style="font-size:12px;font-weight:700;padding:7px 16px;border-radius:8px;background:var(--accent);color:#000;border:none;cursor:pointer">
                    {{ $worker->scaffold_generated_at ? '↺ Re-generate Scaffold' : '⚡ Generate Scaffold' }}
                </button>
            </form>
            @if($worker->folder_path)
            <span style="font-size:10px;font-family:monospace;color:var(--text-faint);align-self:center">📁 {{ $worker->folder_path }}</span>
            @endif
        </div>
    </div>
    @endif

    <form method="POST" action="{{ $worker ? route('admin.workers.update', $worker->slug) : route('admin.workers.store') }}" enctype="multipart/form-data">
        @csrf
        @php
        // ── Completion counts per section ────────────────────────────────────
        $w = $worker ?? null;

        if ($w) {
            $s1f = collect([$w->name??'', $w->slug??'', $w->description??'', !empty($w->tags)?'x':''])->filter(fn($v)=>$v!=='')->count();
            $s2f = collect([$w->org['name']??'', $w->org['website']??'', $w->org['logo']??''])->filter(fn($v)=>$v!=='')->count();
            $s3n = count($w->pipeline_stages ?? []);
            $s4n = count($w->qa_requirements ?? []);
            $cl2 = $w->credential ?? []; if (!empty($cl2) && isset($cl2['type'])) $cl2 = [$cl2];
            $s5n = count(array_filter($cl2, fn($c)=>($c['type']??'none')!=='none'));
            $s6f = collect([$w->owner['name']??'', $w->owner['contact']??'', $w->owner['website']??''])->filter(fn($v)=>$v!=='')->count();
            $s7n = count($w->subscriptions ?? []);
            $s8n = count($w->version_changelog ?? []);
        } else {
            $s1f=$s2f=$s5f=$s6f=0; $s3n=$s4n=$s5n=$s7n=$s8n=0;
        }
        @endphp

        {{-- ── Section 1: Identity ─────────────────────────────────────────── --}}
        @include('admin.worker-builder.partials.section-header', ['label'=>'1','title'=>'Identity','desc'=>'Core worker identification','open'=>true,'filled'=>$s1f,'total'=>4])
        <div class="wb-card" id="wb-section-1-body" style="margin-top:4px">
            <div class="wb-row-2">
                <div class="wb-field">
                    <label class="wb-label">WORKER NAME</label>
                    <input type="text" name="name" value="{{ old('name', $worker->name ?? '') }}" required placeholder="e.g. DOB Permit Tracker" class="wb-input">
                </div>
                <div class="wb-field">
                    <label class="wb-label">SLUG <span class="wb-hint">lowercase, hyphens only</span></label>
                    <input type="text" name="slug" id="slug-field" value="{{ old('slug', $worker->slug ?? '') }}" required placeholder="e.g. dob-permit-tracker" class="wb-input wb-mono" {{ $worker ? 'readonly style=opacity:0.6' : '' }}>
                </div>
            </div>
            <div class="wb-row-2">
                <div class="wb-field">
                    <label class="wb-label">VERSION</label>
                    <input type="text" name="version" value="{{ old('version', $worker->version ?? '1.0') }}" class="wb-input wb-mono">
                </div>
                <div class="wb-field">
                    <label class="wb-label">TAGS <span class="wb-hint">comma-separated</span></label>
                    <input type="text" name="tags_raw" value="{{ old('tags_raw', isset($worker->tags) ? implode(', ', $worker->tags) : '') }}" placeholder="permit, nyc, dob, inspection" class="wb-input">
                </div>
            </div>
            <div class="wb-field">
                <label class="wb-label">DESCRIPTION</label>
                <textarea name="description" rows="2" placeholder="One-sentence description shown on the marketplace card" class="wb-input">{{ old('description', $worker->description ?? '') }}</textarea>
            </div>
        </div>

        {{-- ── Section 2: Organisation ──────────────────────────────────────── --}}
        @include('admin.worker-builder.partials.section-header', ['label'=>'2','title'=>'Organisation','desc'=>'The org or platform this worker is built on','open'=>false,'filled'=>$s2f,'total'=>3])
        <div class="wb-card" id="wb-section-2-body" style="display:none;margin-top:4px">
            <div class="wb-row-3">
                <div class="wb-field">
                    <label class="wb-label">ORG NAME</label>
                    <input type="text" name="org_name" value="{{ old('org_name', $worker->org['name'] ?? '') }}" placeholder="e.g. NYC Dept. of Buildings" class="wb-input">
                </div>
                <div class="wb-field">
                    <label class="wb-label">ABBREVIATION</label>
                    <input type="text" name="org_abbreviation" value="{{ old('org_abbreviation', $worker->org['abbreviation'] ?? '') }}" placeholder="DOB" class="wb-input wb-mono">
                </div>
                <div class="wb-field">
                    <label class="wb-label">TYPE</label>
                    <select name="org_type" class="wb-input">
                        @foreach(['platform','government','crm','erp','custom'] as $t)
                        <option value="{{ $t }}" {{ ($worker->org['type'] ?? 'platform') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="wb-row-2">
                <div class="wb-field">
                    <label class="wb-label">ORG WEBSITE</label>
                    <input type="url" name="org_website" value="{{ old('org_website', $worker->org['website'] ?? '') }}" placeholder="https://www.nyc.gov/dob" class="wb-input">
                </div>
                <div class="wb-field">
                    <label class="wb-label">LOGO KEY</label>
                    <input type="text" name="org_logo" value="{{ old('org_logo', $worker->org['logo'] ?? '') }}" placeholder="dob / gmail / salesforce" class="wb-input">
                </div>
            </div>
        </div>

        {{-- ── Section 3: Pipeline Stages ───────────────────────────────────── --}}
        @include('admin.worker-builder.partials.section-header', ['label'=>'3','title'=>'Pipeline Stages','desc'=>'Ordered job stages — each becomes a Laravel Job class','open'=>false,'status'=>$s3n.' stage'.($s3n!==1?'s':'')])
        <div class="wb-card" id="stages-container" style="display:none;margin-top:4px">
            <div id="stages-list">
                @php $stageList = old('stages', $worker->pipeline_stages ?? []); @endphp
                @forelse($stageList as $i => $stage)
                @include('admin.worker-builder.partials.stage-row', ['i' => $i, 'stage' => $stage])
                @empty
                <p style="font-size:12px;color:var(--text-muted);text-align:center;padding:20px 0" id="stages-empty">No stages yet. Add your first pipeline stage below.</p>
                @endforelse
            </div>
            <button type="button" onclick="addStage()" class="wb-add-btn" style="margin-top:12px">+ Add Stage</button>
        </div>

        {{-- ── Section 4: QA Requirements ───────────────────────────────────── --}}
        @include('admin.worker-builder.partials.section-header', ['label'=>'4','title'=>'QA Requirements','desc'=>'Per-stage pass conditions the QA evaluator runs after each transaction','open'=>false,'status'=>$s4n.' check'.($s4n!==1?'s':'')])
        <div class="wb-card" id="qa-container" style="display:none;margin-top:4px">
            <div style="font-size:11px;color:var(--text-muted);margin-bottom:12px">
                Available checks: <code>FIELD_NOT_NULL</code> · <code>FIELD_NOT_EMPTY</code> · <code>VALUE_ABOVE</code> · <code>VALID_EMAIL</code> · <code>STATUS_IN</code>
            </div>
            <div id="qa-list">
                @php $qaList = old('qa', $worker->qa_requirements ?? []); @endphp
                @forelse($qaList as $i => $q)
                @include('admin.worker-builder.partials.qa-row', ['i' => $i, 'q' => $q])
                @empty
                <p style="font-size:12px;color:var(--text-muted);text-align:center;padding:16px 0" id="qa-empty">No QA checks defined yet.</p>
                @endforelse
            </div>
            <button type="button" onclick="addQa()" class="wb-add-btn" style="margin-top:12px">+ Add QA Check</button>
        </div>

        {{-- ── Section 5: Credential & Instances ───────────────────────────── --}}
        @include('admin.worker-builder.partials.section-header', ['label'=>'5','title'=>'Credential & Instances','desc'=>'How this worker connects and how many instances a tenant can deploy','open'=>false,'status'=>$s5n.' credential'.($s5n!==1?'s':'')])
        <div class="wb-card" id="wb-section-5-body" style="display:none;margin-top:4px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                <p style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin:0">Credentials <span style="font-weight:400;font-size:10px">(add one per connection type)</span></p>
                <button type="button" onclick="addCredential()" class="wb-add-btn" style="width:auto;padding:5px 12px">+ Add Credential Slot</button>
            </div>
            <div id="credentials-list">
                @php
                    $credList = old('credentials', $worker->credential ?? []);
                    // Normalise: if stored as single object (legacy), wrap
                    if (!empty($credList) && isset($credList['type'])) $credList = [$credList];
                @endphp
                @forelse($credList as $ci => $cred)
                <div class="cred-row wb-stage-block" data-cred="{{ $ci }}">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                        <span style="font-size:11px;font-weight:700;color:var(--text-muted)">CREDENTIAL SLOT {{ $ci + 1 }}</span>
                        <button type="button" onclick="removeRow(this,'credentials-list','cred-empty','No credential slots defined.')" style="font-size:11px;color:#f87171;background:none;border:none;cursor:pointer">Remove</button>
                    </div>
                    <div class="wb-row-3" style="margin-bottom:10px">
                        <div class="wb-field">
                            <label class="wb-label">TYPE</label>
                            <select name="credentials[{{ $ci }}][type]" class="wb-input">
                                @foreach(['none','gmail_oauth','api_key','oauth2','webhook','database'] as $t)
                                <option value="{{ $t }}" {{ ($cred['type'] ?? 'none') === $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="wb-field">
                            <label class="wb-label">LABEL</label>
                            <input type="text" name="credentials[{{ $ci }}][label]" value="{{ $cred['label'] ?? '' }}" placeholder="Gmail Account" class="wb-input">
                        </div>
                        <div class="wb-field">
                            <label class="wb-label">OPTIONS</label>
                            <div style="display:flex;flex-direction:column;gap:4px;margin-top:6px">
                                <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer">
                                    <input type="checkbox" name="credentials[{{ $ci }}][required]" value="1" {{ !empty($cred['required']) ? 'checked' : '' }}>
                                    Required
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer">
                                    <input type="checkbox" name="credentials[{{ $ci }}][multiple]" value="1" {{ !empty($cred['multiple']) ? 'checked' : '' }}>
                                    Allow multiple
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="wb-field" style="margin-bottom:10px">
                        <label class="wb-label">HINT</label>
                        <input type="text" name="credentials[{{ $ci }}][hint]" value="{{ $cred['hint'] ?? '' }}" placeholder="Helper text shown during connect step" class="wb-input">
                    </div>
                    <div class="wb-row-2">
                        <div class="wb-field">
                            <label class="wb-label">CONNECT ROUTE</label>
                            <input type="text" name="credentials[{{ $ci }}][connect_route]" value="{{ $cred['connect_route'] ?? '' }}" placeholder="app.workers.gmail.connect" class="wb-input wb-mono">
                        </div>
                        <div class="wb-field">
                            <label class="wb-label">AUTHORIZE ROUTE</label>
                            <input type="text" name="credentials[{{ $ci }}][authorize_route]" value="{{ $cred['authorize_route'] ?? '' }}" placeholder="app.workers.gmail.authorize" class="wb-input wb-mono">
                        </div>
                    </div>
                </div>
                @empty
                <p style="font-size:12px;color:var(--text-muted);text-align:center;padding:16px 0" id="cred-empty">No credential slots. Add one if this worker requires a connection.</p>
                @endforelse
            </div>

            <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border-soft)">
                <p style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin-bottom:10px">Instances</p>
                <div class="wb-row-3">
                    <div class="wb-field">
                        <label class="wb-label">INSTANCE LABEL</label>
                        <input type="text" name="inst_label" value="{{ old('inst_label', $worker->instances['label'] ?? 'deployment') }}" placeholder="inbox / project / account" class="wb-input">
                    </div>
                    <div class="wb-field">
                        <label class="wb-label">MIN</label>
                        <input type="number" name="inst_min" value="{{ old('inst_min', $worker->instances['min'] ?? 1) }}" class="wb-input" min="0">
                    </div>
                    <div class="wb-field">
                        <label class="wb-label">MAX <span class="wb-hint">blank = unlimited</span></label>
                        <input type="number" name="inst_max" value="{{ old('inst_max', $worker->instances['max'] ?? '') }}" class="wb-input" min="1">
                    </div>
                </div>
                <div class="wb-row-2" style="margin-top:10px">
                    <div class="wb-field">
                        <label class="wb-label">RATIONALE</label>
                        <input type="text" name="inst_rationale" value="{{ old('inst_rationale', $worker->instances['rationale'] ?? '') }}" placeholder="Why this limit exists" class="wb-input">
                    </div>
                    <div class="wb-field">
                        <label class="wb-label">MULTIPLE DEPLOYMENTS?</label>
                        <label style="display:flex;align-items:center;gap:6px;margin-top:8px;font-size:12px;cursor:pointer">
                            <input type="checkbox" name="inst_multiple" value="1" {{ !empty($worker->instances['multiple']) ? 'checked' : '' }}>
                            Allow more than one per tenant
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Section 6: Owner ─────────────────────────────────────────────── --}}
        @include('admin.worker-builder.partials.section-header', ['label'=>'6','title'=>'Owner','desc'=>'Who built this worker — license, SLA, contact','open'=>false,'filled'=>$s6f,'total'=>3])
        <div class="wb-card" id="wb-section-6-body" style="display:none;margin-top:4px">
            <div class="wb-row-3" style="margin-bottom:14px">
                <div class="wb-field">
                    <label class="wb-label">OWNER TYPE</label>
                    <select name="owner_type" class="wb-input">
                        @foreach(['platform','partner','custom'] as $t)
                        <option value="{{ $t }}" {{ ($worker->owner['type'] ?? 'platform') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="wb-field">
                    <label class="wb-label">OWNER NAME</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name', $worker->owner['name'] ?? 'UNIT') }}" class="wb-input">
                </div>
                <div class="wb-field">
                    <label class="wb-label">LICENSE</label>
                    <select name="owner_license" class="wb-input">
                        @foreach(['proprietary','commercial','mit','apache2','gpl'] as $l)
                        <option value="{{ $l }}" {{ ($worker->owner['license'] ?? 'proprietary') === $l ? 'selected' : '' }}>{{ strtoupper($l) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="wb-row-3">
                <div class="wb-field">
                    <label class="wb-label">CONTACT EMAIL</label>
                    <input type="email" name="owner_contact" value="{{ old('owner_contact', $worker->owner['contact'] ?? 'hello@unit.report') }}" class="wb-input">
                </div>
                <div class="wb-field">
                    <label class="wb-label">WEBSITE</label>
                    <input type="url" name="owner_website" value="{{ old('owner_website', $worker->owner['website'] ?? 'https://unit.report') }}" class="wb-input">
                </div>
                <div class="wb-field">
                    <label class="wb-label">SINCE (year) · SLA</label>
                    <div style="display:grid;grid-template-columns:80px 1fr;gap:8px;margin-top:2px">
                        <input type="number" name="owner_since" value="{{ old('owner_since', $worker->owner['since'] ?? date('Y')) }}" class="wb-input" min="2020">
                        <input type="text" name="owner_sla" value="{{ old('owner_sla', $worker->owner['sla'] ?? '') }}" placeholder="99.9% · 4h response" class="wb-input">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Section 7: Media ─────────────────────────────────────────────── --}}
        @php
        $mediaItems = json_decode($worker->gallery ?? '[]', true) ?? [];
        $s7mCount = count($mediaItems);
        @endphp
        @include('admin.worker-builder.partials.section-header', ['label'=>'7','title'=>'Media','desc'=>'Brand color, voice, and all visual assets for this worker','open'=>false,'status'=>$s7mCount.' item'.($s7mCount!==1?'s':'')])
        <div class="wb-card" id="wb-section-7-body" style="display:none;margin-top:4px">

            {{-- Brand color + Quote — part of main form --}}
            <div class="wb-row-2" style="margin-bottom:20px">
                <div class="wb-field">
                    <label class="wb-label">BRAND COLOR</label>
                    <div style="display:flex;gap:8px;align-items:center;margin-top:6px">
                        <input type="color" name="media_color" id="media-color-picker" value="{{ old('media_color', $worker->media['color'] ?? '#142C74') }}" style="width:40px;height:32px;border:none;cursor:pointer;border-radius:6px;background:none" oninput="document.getElementById('media-color-text').value=this.value">
                        <input type="text" id="media-color-text" value="{{ old('media_color', $worker->media['color'] ?? '#142C74') }}" oninput="document.getElementById('media-color-picker').value=this.value;document.querySelector('[name=media_color]').value=this.value" style="flex:1;background:var(--bg-raised);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;color:var(--text-primary);font-family:monospace" placeholder="#142C74">
                    </div>
                </div>
                <div class="wb-field">
                    <label class="wb-label">WORKER QUOTE <span class="wb-hint">first-person, shown on profile card</span></label>
                    <input type="text" name="media_quote" value="{{ old('media_quote', $worker->media['quote'] ?? '') }}" placeholder="I read every permit so you don't have to." class="wb-input">
                </div>
            </div>

            {{-- Media items — saved independently via AJAX --}}
            <div style="border-top:1px solid var(--border);padding-top:16px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                    <div>
                        <p style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;margin:0">Media Items</p>
                        <p style="font-size:10px;color:var(--text-muted);margin-top:2px">Profile photo, cover, gallery images &amp; YouTube videos</p>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center">
                        <span id="media-save-msg" style="font-size:11px;display:none"></span>
                        <button type="button" onclick="addMediaItem()" style="font-size:12px;font-weight:700;padding:6px 14px;border-radius:8px;background:transparent;border:1px solid var(--border);color:var(--text-primary);cursor:pointer">+ Add Media</button>
                        <button type="button" onclick="saveMediaItems()" style="font-size:12px;font-weight:700;padding:6px 16px;border-radius:8px;background:var(--accent);color:#000;border:none;cursor:pointer">Save Media</button>
                    </div>
                </div>

                <div id="media-items-list" style="display:flex;flex-direction:column;gap:10px">
                @forelse($mediaItems as $mi => $mitem)
                @php
                    $mtype = $mitem['type'] ?? 'gallery';
                    $mkind = $mitem['kind'] ?? 'file';
                    $mpath = $mitem['path'] ?? '';
                    $murl  = $mitem['url'] ?? '';
                    $mcap  = $mitem['caption'] ?? '';
                    $isUrl = in_array($mtype, ['youtube_intro','youtube_pipeline']);
                    $previewSrc = ($mpath && !$isUrl) ? asset('storage/' . $mpath) : null;
                    if ($isUrl && $murl) {
                        preg_match('/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $murl, $ytm);
                        $ytThumb = isset($ytm[1]) ? "https://img.youtube.com/vi/{$ytm[1]}/mqdefault.jpg" : null;
                    } else { $ytThumb = null; }
                @endphp
                <div class="media-item-row" style="background:var(--bg-raised);border:1px solid var(--border);border-radius:12px;padding:14px;display:grid;grid-template-columns:160px 1fr auto;gap:12px;align-items:start"
                     data-type="{{ $mtype }}" data-kind="{{ $mkind }}" data-path="{{ $mpath }}" data-url="{{ $murl }}" data-caption="{{ $mcap }}">
                    <div>
                        <label class="wb-label" style="margin-bottom:4px">TYPE</label>
                        <select class="wb-input mi-type" style="font-size:12px" onchange="onMiTypeChange(this)">
                            @foreach(['profile'=>'Profile Photo','cover'=>'Cover Image','display'=>'Display Image','gallery'=>'Gallery Image','youtube_intro'=>'YouTube — Intro','youtube_pipeline'=>'YouTube — Pipeline'] as $tv => $tl)
                            <option value="{{ $tv }}" {{ $mtype === $tv ? 'selected' : '' }}>{{ $tl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="mi-file-wrap" style="{{ $isUrl ? 'display:none' : '' }}">
                            <label class="wb-label" style="margin-bottom:4px">IMAGE FILE <span class="wb-hint">JPG, PNG, GIF, WebP · max 20 MB</span></label>
                            @if($previewSrc)
                            <div class="mi-current-preview" style="margin-bottom:6px;display:flex;gap:8px;align-items:center">
                                <img src="{{ $previewSrc }}" alt="" style="width:60px;height:40px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">
                                <span style="font-size:10px;color:var(--text-muted);word-break:break-all">{{ basename($mpath) }}</span>
                            </div>
                            @endif
                            <input type="file" class="mi-file" accept="image/*" style="font-size:11px;color:var(--text-muted);width:100%" onchange="onMiFileChange(this)">
                            <div class="mi-new-preview" style="margin-top:6px"></div>
                        </div>
                        <div class="mi-url-wrap" style="{{ !$isUrl ? 'display:none' : '' }}">
                            <label class="wb-label" style="margin-bottom:4px">YOUTUBE URL</label>
                            @if($ytThumb)
                            <div class="mi-current-preview" style="margin-bottom:6px;display:flex;gap:8px;align-items:center">
                                <img src="{{ $ytThumb }}" alt="" style="width:60px;height:40px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">
                                <span style="font-size:10px;color:var(--text-muted);word-break:break-all">{{ $murl }}</span>
                            </div>
                            @endif
                            <input type="text" class="wb-input mi-url" value="{{ $murl }}" placeholder="https://www.youtube.com/watch?v=..." style="font-size:12px">
                        </div>
                        <div style="margin-top:8px">
                            <label class="wb-label" style="margin-bottom:3px">CAPTION <span class="wb-hint">optional</span></label>
                            <input type="text" class="wb-input mi-caption" value="{{ $mcap }}" placeholder="Describe this media" style="font-size:12px">
                        </div>
                    </div>
                    <button type="button" onclick="this.closest('.media-item-row').remove();updateMediaEmpty()"
                            style="margin-top:20px;width:28px;height:28px;border-radius:50%;background:rgba(248,113,113,.15);color:#f87171;border:1px solid rgba(248,113,113,.3);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0">×</button>
                </div>
                @empty
                <p id="media-empty" style="font-size:12px;color:var(--text-muted);text-align:center;padding:24px 0">No media yet. Click <strong>+ Add Media</strong> to add the worker's profile photo, cover image, or videos.</p>
                @endforelse
                </div>
            </div>
        </div>

        {{-- ── Section 8: Subscriptions ─────────────────────────────────────── --}}
        @include('admin.worker-builder.partials.section-header', ['label'=>'8','title'=>'Event Subscriptions','desc'=>'Events from other workers this worker listens to','open'=>false,'status'=>$s7n.' subscription'.($s7n!==1?'s':'')])
        <div class="wb-card" id="subs-container" style="display:none;margin-top:4px">
            <div id="subs-list">
                @php $subsList = old('subscriptions', $worker->subscriptions ?? []); @endphp
                @forelse($subsList as $si => $sub)
                <div class="sub-row wb-stage-block" data-sub="{{ $si }}">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                        <span style="font-size:11px;font-weight:700;color:var(--text-muted)">SUBSCRIPTION</span>
                        <button type="button" onclick="removeRow(this,'subs-list','subs-empty','No subscriptions defined.')" style="font-size:11px;color:#f87171;background:none;border:none;cursor:pointer">Remove</button>
                    </div>
                    <div class="wb-row-3" style="margin-bottom:10px">
                        <div class="wb-field">
                            <label class="wb-label">EVENT NAME</label>
                            <input type="text" name="subscriptions[{{ $si }}][event]" value="{{ $sub['event'] ?? '' }}" placeholder="email.classified" class="wb-input wb-mono">
                        </div>
                        <div class="wb-field">
                            <label class="wb-label">FROM WORKER</label>
                            <input type="text" name="subscriptions[{{ $si }}][from_worker]" value="{{ $sub['from_worker'] ?? '' }}" placeholder="ava" class="wb-input wb-mono">
                        </div>
                        <div class="wb-field">
                            <label class="wb-label">HANDLER STAGE</label>
                            <input type="text" name="subscriptions[{{ $si }}][handler_stage]" value="{{ $sub['handler_stage'] ?? '' }}" placeholder="classify" class="wb-input wb-mono">
                        </div>
                    </div>
                    <div class="wb-row-2">
                        <div class="wb-field">
                            <label class="wb-label">DESCRIPTION</label>
                            <input type="text" name="subscriptions[{{ $si }}][description]" value="{{ $sub['description'] ?? '' }}" placeholder="What triggers this subscription" class="wb-input">
                        </div>
                        <div class="wb-field">
                            <label class="wb-label">REQUIRED?</label>
                            <label style="display:flex;align-items:center;gap:6px;margin-top:8px;font-size:12px;cursor:pointer">
                                <input type="checkbox" name="subscriptions[{{ $si }}][required]" value="1" {{ !empty($sub['required']) ? 'checked' : '' }}>
                                Deployment fails without this event
                            </label>
                        </div>
                    </div>
                </div>
                @empty
                <p style="font-size:12px;color:var(--text-muted);text-align:center;padding:16px 0" id="subs-empty">No subscriptions. Add one if this worker reacts to events from another worker.</p>
                @endforelse
            </div>
            <button type="button" onclick="addSubscription()" class="wb-add-btn" style="margin-top:12px">+ Add Subscription</button>
        </div>

        {{-- ── Section 9: Version Changelog ────────────────────────────────── --}}
        @include('admin.worker-builder.partials.section-header', ['label'=>'9','title'=>'Version Changelog','desc'=>'Release notes and upgrade paths between versions','open'=>false,'status'=>$s8n.' version'.($s8n!==1?'s':'')])
        <div class="wb-card" id="changelog-container" style="display:none;margin-top:4px">
            <div id="changelog-list">
                @php $changelogList = old('changelog', $worker->version_changelog ?? []); @endphp
                @forelse($changelogList as $cli => $cl)
                <div class="cl-row wb-stage-block" data-cl="{{ $cli }}">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                        <span style="font-size:11px;font-weight:700;color:var(--text-muted)">RELEASE</span>
                        <button type="button" onclick="removeRow(this,'changelog-list','cl-empty','No changelog entries.')" style="font-size:11px;color:#f87171;background:none;border:none;cursor:pointer">Remove</button>
                    </div>
                    <div class="wb-row-3" style="margin-bottom:10px">
                        <div class="wb-field">
                            <label class="wb-label">VERSION</label>
                            <input type="text" name="changelog[{{ $cli }}][version]" value="{{ $cl['version'] ?? '' }}" placeholder="1.1" class="wb-input wb-mono">
                        </div>
                        <div class="wb-field">
                            <label class="wb-label">DATE</label>
                            <input type="date" name="changelog[{{ $cli }}][date]" value="{{ $cl['date'] ?? '' }}" class="wb-input">
                        </div>
                        <div class="wb-field">
                            <label class="wb-label">BREAKING CHANGE?</label>
                            <label style="display:flex;align-items:center;gap:6px;margin-top:8px;font-size:12px;cursor:pointer">
                                <input type="checkbox" name="changelog[{{ $cli }}][breaking]" value="1" {{ !empty($cl['breaking']) ? 'checked' : '' }}>
                                Breaking — existing deployments affected
                            </label>
                        </div>
                    </div>
                    <div class="wb-field" style="margin-bottom:10px">
                        <label class="wb-label">RELEASE NOTES</label>
                        <textarea name="changelog[{{ $cli }}][notes]" rows="2" placeholder="What changed in this version" class="wb-input">{{ $cl['notes'] ?? '' }}</textarea>
                    </div>
                    <div class="wb-row-2">
                        <div class="wb-field">
                            <label class="wb-label">BREAKING REASON <span class="wb-hint">if breaking</span></label>
                            <input type="text" name="changelog[{{ $cli }}][breaking_reason]" value="{{ $cl['breaking_reason'] ?? '' }}" placeholder="Credential format changed" class="wb-input">
                        </div>
                        <div class="wb-field">
                            <label class="wb-label">UPGRADE STEPS <span class="wb-hint">one per line</span></label>
                            <textarea name="changelog[{{ $cli }}][upgrade_steps]" rows="2" placeholder="Re-connect credential&#10;Re-run seed command" class="wb-input">{{ isset($cl['upgrade_steps']) ? implode("\n", (array)$cl['upgrade_steps']) : '' }}</textarea>
                        </div>
                    </div>
                </div>
                @empty
                <p style="font-size:12px;color:var(--text-muted);text-align:center;padding:16px 0" id="cl-empty">No changelog entries yet.</p>
                @endforelse
            </div>
            <button type="button" onclick="addChangelog()" class="wb-add-btn" style="margin-top:12px">+ Add Version Entry</button>
        </div>

        {{-- Submit --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:24px">
            <a href="{{ route('admin.workers.index') }}" style="font-size:12px;color:var(--text-muted);text-decoration:none">Cancel</a>
            <div style="display:flex;gap:10px;align-items:center">
                @if($worker && in_array($worker->status, ['registered','scaffolded']))
                <button type="button" onclick="scaffoldWorker()" style="font-size:13px;font-weight:700;padding:10px 20px;border-radius:10px;background:transparent;border:1px solid var(--accent);color:var(--accent-text);cursor:pointer">
                    ⚡ Save & Scaffold
                </button>
                @endif
                <button type="submit" style="font-size:13px;font-weight:700;padding:10px 24px;border-radius:10px;background:var(--accent);color:#000;border:none;cursor:pointer">
                    {{ $worker ? 'Save DNA' : 'Register Worker' }}
                </button>
            </div>
        </div>
    </form>

</div>

{{-- Credential slot template --}}
<template id="credential-template">
    <div class="cred-row wb-stage-block" data-cred="__ci__">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:700;color:var(--text-muted)">CREDENTIAL SLOT</span>
            <button type="button" onclick="removeRow(this,'credentials-list','cred-empty','No credential slots defined.')" style="font-size:11px;color:#f87171;background:none;border:none;cursor:pointer">Remove</button>
        </div>
        <div class="wb-row-3" style="margin-bottom:10px">
            <div class="wb-field">
                <label class="wb-label">TYPE</label>
                <select name="credentials[__ci__][type]" class="wb-input">
                    <option value="none">none</option>
                    <option value="gmail_oauth">gmail_oauth</option>
                    <option value="api_key">api_key</option>
                    <option value="oauth2">oauth2</option>
                    <option value="webhook">webhook</option>
                    <option value="database">database</option>
                </select>
            </div>
            <div class="wb-field">
                <label class="wb-label">LABEL</label>
                <input type="text" name="credentials[__ci__][label]" placeholder="Gmail Account" class="wb-input">
            </div>
            <div class="wb-field">
                <label class="wb-label">OPTIONS</label>
                <div style="display:flex;flex-direction:column;gap:4px;margin-top:6px">
                    <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer">
                        <input type="checkbox" name="credentials[__ci__][required]" value="1" checked>
                        Required
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer">
                        <input type="checkbox" name="credentials[__ci__][multiple]" value="1">
                        Allow multiple
                    </label>
                </div>
            </div>
        </div>
        <div class="wb-field" style="margin-bottom:10px">
            <label class="wb-label">HINT</label>
            <input type="text" name="credentials[__ci__][hint]" placeholder="Helper text shown during connect step" class="wb-input">
        </div>
        <div class="wb-row-2">
            <div class="wb-field">
                <label class="wb-label">CONNECT ROUTE</label>
                <input type="text" name="credentials[__ci__][connect_route]" placeholder="app.workers.gmail.connect" class="wb-input wb-mono">
            </div>
            <div class="wb-field">
                <label class="wb-label">AUTHORIZE ROUTE</label>
                <input type="text" name="credentials[__ci__][authorize_route]" placeholder="app.workers.gmail.authorize" class="wb-input wb-mono">
            </div>
        </div>
    </div>
</template>

{{-- Subscription template --}}
<template id="sub-template">
    <div class="sub-row wb-stage-block" data-sub="__si__">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:700;color:var(--text-muted)">SUBSCRIPTION</span>
            <button type="button" onclick="removeRow(this,'subs-list','subs-empty','No subscriptions defined.')" style="font-size:11px;color:#f87171;background:none;border:none;cursor:pointer">Remove</button>
        </div>
        <div class="wb-row-3" style="margin-bottom:10px">
            <div class="wb-field">
                <label class="wb-label">EVENT NAME</label>
                <input type="text" name="subscriptions[__si__][event]" placeholder="email.classified" class="wb-input wb-mono">
            </div>
            <div class="wb-field">
                <label class="wb-label">FROM WORKER</label>
                <input type="text" name="subscriptions[__si__][from_worker]" placeholder="ava" class="wb-input wb-mono">
            </div>
            <div class="wb-field">
                <label class="wb-label">HANDLER STAGE</label>
                <input type="text" name="subscriptions[__si__][handler_stage]" placeholder="classify" class="wb-input wb-mono">
            </div>
        </div>
        <div class="wb-row-2">
            <div class="wb-field">
                <label class="wb-label">DESCRIPTION</label>
                <input type="text" name="subscriptions[__si__][description]" placeholder="What triggers this subscription" class="wb-input">
            </div>
            <div class="wb-field">
                <label class="wb-label">REQUIRED?</label>
                <label style="display:flex;align-items:center;gap:6px;margin-top:8px;font-size:12px;cursor:pointer">
                    <input type="checkbox" name="subscriptions[__si__][required]" value="1">
                    Deployment fails without this event
                </label>
            </div>
        </div>
    </div>
</template>

{{-- Changelog template --}}
<template id="changelog-template">
    <div class="cl-row wb-stage-block" data-cl="__cli__">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:700;color:var(--text-muted)">RELEASE</span>
            <button type="button" onclick="removeRow(this,'changelog-list','cl-empty','No changelog entries.')" style="font-size:11px;color:#f87171;background:none;border:none;cursor:pointer">Remove</button>
        </div>
        <div class="wb-row-3" style="margin-bottom:10px">
            <div class="wb-field">
                <label class="wb-label">VERSION</label>
                <input type="text" name="changelog[__cli__][version]" placeholder="1.1" class="wb-input wb-mono">
            </div>
            <div class="wb-field">
                <label class="wb-label">DATE</label>
                <input type="date" name="changelog[__cli__][date]" class="wb-input">
            </div>
            <div class="wb-field">
                <label class="wb-label">BREAKING CHANGE?</label>
                <label style="display:flex;align-items:center;gap:6px;margin-top:8px;font-size:12px;cursor:pointer">
                    <input type="checkbox" name="changelog[__cli__][breaking]" value="1">
                    Breaking — existing deployments affected
                </label>
            </div>
        </div>
        <div class="wb-field" style="margin-bottom:10px">
            <label class="wb-label">RELEASE NOTES</label>
            <textarea name="changelog[__cli__][notes]" rows="2" placeholder="What changed in this version" class="wb-input"></textarea>
        </div>
        <div class="wb-row-2">
            <div class="wb-field">
                <label class="wb-label">BREAKING REASON <span class="wb-hint">if breaking</span></label>
                <input type="text" name="changelog[__cli__][breaking_reason]" placeholder="Credential format changed" class="wb-input">
            </div>
            <div class="wb-field">
                <label class="wb-label">UPGRADE STEPS <span class="wb-hint">one per line</span></label>
                <textarea name="changelog[__cli__][upgrade_steps]" rows="2" placeholder="Re-connect credential&#10;Re-run seed command" class="wb-input"></textarea>
            </div>
        </div>
    </div>
</template>

{{-- Stage row template --}}
<template id="stage-template">
    <div class="stage-row wb-stage-block" data-index="__i__">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:700;color:var(--text-muted)">STAGE <span class="stage-num">?</span></span>
            <button type="button" onclick="removeRow(this, 'stages-list', 'stages-empty', 'No stages yet. Add your first pipeline stage below.')" style="font-size:11px;color:#f87171;background:none;border:none;cursor:pointer">Remove</button>
        </div>
        <div class="wb-row-3" style="margin-bottom:10px">
            <div class="wb-field">
                <label class="wb-label">STAGE LABEL</label>
                <input type="text" name="stages[__i__][label]" placeholder="Read Email" class="wb-input" oninput="syncStageKey(this)">
            </div>
            <div class="wb-field">
                <label class="wb-label">JOB CLASS NAME</label>
                <input type="text" name="stages[__i__][job_class]" placeholder="ReadEmailJob" class="wb-input wb-mono">
            </div>
            <div class="wb-field">
                <label class="wb-label">ICON</label>
                <select name="stages[__i__][icon]" class="wb-input">
                    @foreach(['check','mail','tag','brain','log','template','draft','send','bolt','search'] as $ic)
                    <option value="{{ $ic }}">{{ $ic }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="wb-field" style="margin-bottom:10px">
            <label class="wb-label">STAGE DESCRIPTION <span class="wb-hint">shown under label in pipeline visual</span></label>
            <input type="text" name="stages[__i__][sub]" placeholder="Parse & extract fields from raw email" class="wb-input">
        </div>
        <div style="display:flex;gap:12px;margin-bottom:12px;align-items:center">
            <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer">
                <input type="checkbox" name="stages[__i__][uses_ai]" value="1" class="ai-toggle" onchange="toggleAiBlock(this)">
                Uses AI (Claude)
            </label>
        </div>
        <div class="ai-block" style="display:none;background:var(--bg-raised);border:1px solid var(--border-soft);border-radius:10px;padding:14px;margin-bottom:4px">
            <p style="font-size:10px;font-weight:700;color:var(--accent-text);text-transform:uppercase;margin-bottom:10px">AI Configuration</p>
            <div class="wb-row-3" style="margin-bottom:10px">
                <div class="wb-field">
                    <label class="wb-label">MODEL OVERRIDE <span class="wb-hint">blank = deployment default</span></label>
                    <input type="text" name="stages[__i__][model]" placeholder="claude-sonnet-4-6" class="wb-input wb-mono">
                </div>
                <div class="wb-field">
                    <label class="wb-label">OUTPUT FORMAT</label>
                    <select name="stages[__i__][output_format]" class="wb-input">
                        <option value="json">JSON</option>
                        <option value="text">Text</option>
                    </select>
                </div>
                <div class="wb-field">
                    <label class="wb-label">MAX TOKENS</label>
                    <input type="number" name="stages[__i__][max_tokens]" value="500" class="wb-input" min="50" max="8000">
                </div>
            </div>
            <div class="wb-field" style="margin-bottom:10px">
                <label class="wb-label">SYSTEM PROMPT <span class="wb-hint">role declaration — who the AI is in this stage</span></label>
                <textarea name="stages[__i__][system_prompt]" rows="3" placeholder="You are an expert email classifier for a renewal coordination platform. Your job is to..." class="wb-input" style="font-family:monospace;font-size:12px;resize:vertical"></textarea>
            </div>
            <div class="wb-field">
                <label class="wb-label">USER PROMPT TEMPLATE <span class="wb-hint">use {PLACEHOLDER} for dynamic values</span></label>
                <textarea name="stages[__i__][user_prompt]" rows="5" placeholder="Classify the following email:&#10;&#10;From: {FROM}&#10;Subject: {SUBJECT}&#10;Body: {BODY}&#10;&#10;Return JSON with: {&quot;category&quot;: ..., &quot;confidence&quot;: 0.0-1.0, &quot;priority&quot;: ...}" class="wb-input" style="font-family:monospace;font-size:12px;resize:vertical"></textarea>
            </div>
            <div class="wb-field" style="margin-top:10px">
                <label class="wb-label">EXPECTED OUTPUT SHAPE <span class="wb-hint">JSON keys the model must return</span></label>
                <input type="text" name="stages[__i__][output_shape]" placeholder='{"category": "string", "confidence": "float", "priority": "string"}' class="wb-input wb-mono">
            </div>
        </div>
    </div>
</template>

{{-- QA row template --}}
<template id="qa-template">
    <div class="qa-row" data-index="__i__" style="background:var(--bg-raised);border:1px solid var(--border-soft);border-radius:10px;padding:14px;margin-bottom:8px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:11px;font-weight:700;color:var(--text-muted)">QA CHECK</span>
            <button type="button" onclick="removeRow(this, 'qa-list', 'qa-empty', 'No QA checks defined yet.')" style="font-size:11px;color:#f87171;background:none;border:none;cursor:pointer">Remove</button>
        </div>
        <div class="wb-row-3" style="margin-bottom:10px">
            <div class="wb-field">
                <label class="wb-label">STAGE KEY</label>
                <input type="text" name="qa[__i__][stage]" placeholder="read / classify / push" class="wb-input wb-mono">
            </div>
            <div class="wb-field">
                <label class="wb-label">CHECK TYPE</label>
                <select name="qa[__i__][check]" class="wb-input" onchange="toggleQaFields(this)">
                    <option value="FIELD_NOT_NULL">FIELD_NOT_NULL</option>
                    <option value="FIELD_NOT_EMPTY">FIELD_NOT_EMPTY</option>
                    <option value="VALUE_ABOVE">VALUE_ABOVE</option>
                    <option value="VALID_EMAIL">VALID_EMAIL</option>
                    <option value="STATUS_IN">STATUS_IN</option>
                </select>
            </div>
            <div class="wb-field">
                <label class="wb-label">LABEL</label>
                <input type="text" name="qa[__i__][label]" placeholder="Subject must not be null" class="wb-input">
            </div>
        </div>
        <div class="wb-row-3">
            <div class="wb-field qa-field-field">
                <label class="wb-label">FIELD <span class="wb-hint">dot-path e.g. output.subject</span></label>
                <input type="text" name="qa[__i__][field]" placeholder="output.subject" class="wb-input wb-mono">
            </div>
            <div class="wb-field qa-threshold-field" style="display:none">
                <label class="wb-label">THRESHOLD <span class="wb-hint">0.0–1.0</span></label>
                <input type="number" name="qa[__i__][threshold]" step="0.01" min="0" max="1" placeholder="0.7" class="wb-input">
            </div>
            <div class="wb-field qa-values-field" style="display:none">
                <label class="wb-label">VALUES <span class="wb-hint">comma-separated</span></label>
                <input type="text" name="qa[__i__][values]" placeholder="draft_ready,approved,sent" class="wb-input wb-mono">
            </div>
        </div>
    </div>
</template>

<style>
.wb-card      { background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:16px }
.wb-field     { display:flex;flex-direction:column }
.wb-label     { font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:5px }
.wb-hint      { font-weight:400;font-size:9px;color:var(--text-faint);text-transform:none;letter-spacing:0 }
.wb-input     { background:var(--bg-raised);border:1px solid var(--border);border-radius:8px;padding:7px 10px;font-size:12px;color:var(--text-primary);width:100% }
.wb-input:focus { outline:none;border-color:var(--accent) }
.wb-mono      { font-family:monospace }
.wb-row-2     { display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px }
.wb-row-3     { display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px }
.wb-add-btn   { font-size:12px;font-weight:600;padding:8px 16px;border-radius:8px;border:1px dashed var(--border);color:var(--text-secondary);background:transparent;cursor:pointer;width:100% }
.wb-add-btn:hover { border-color:var(--accent);color:var(--accent-text) }
.wb-stage-block { background:var(--bg-raised);border:1px solid var(--border-soft);border-radius:12px;padding:16px;margin-bottom:10px }
</style>

<script>
// ── Save & Scaffold ───────────────────────────────────────────────────────────
function scaffoldWorker() {
    document.querySelector('form[enctype="multipart/form-data"]')?.submit();
}

// ── Section collapse/expand ──────────────────────────────────────────────────
const _sectionMap = {
    'wb-section-3': 'stages-container',
    'wb-section-4': 'qa-container',
    'wb-section-8': 'subs-container',
    'wb-section-9': 'changelog-container',
};
function toggleSection(id) {
    const bodyId = _sectionMap[id] || (id + '-body');
    const body = document.getElementById(bodyId);
    if (!body) return;
    const isOpen = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : 'block';
    document.querySelectorAll(`#${id}-chevron`).forEach(el => {
        el.style.transform = isOpen ? '' : 'rotate(180deg)';
    });
}

// ── Media items (AJAX save) ───────────────────────────────────────────────────
const _ytTypes = ['youtube_intro','youtube_pipeline'];
const _mediaUrl = '{{ route("admin.workers.media", $worker->slug) }}';
const _mediaCsrf = '{{ csrf_token() }}';

function addMediaItem() {
    updateMediaEmpty(true);
    const html = `<div class="media-item-row" data-type="gallery" data-kind="file" data-path="" data-url="" data-caption=""
          style="background:var(--bg-raised);border:1px solid var(--border);border-radius:12px;padding:14px;display:grid;grid-template-columns:160px 1fr auto;gap:12px;align-items:start">
        <div>
            <label class="wb-label" style="margin-bottom:4px">TYPE</label>
            <select class="wb-input mi-type" style="font-size:12px" onchange="onMiTypeChange(this)">
                <option value="profile">Profile Photo</option>
                <option value="cover">Cover Image</option>
                <option value="display">Display Image</option>
                <option value="gallery" selected>Gallery Image</option>
                <option value="youtube_intro">YouTube — Intro</option>
                <option value="youtube_pipeline">YouTube — Pipeline</option>
            </select>
        </div>
        <div>
            <div class="mi-file-wrap">
                <label class="wb-label" style="margin-bottom:4px">IMAGE FILE <span class="wb-hint">JPG, PNG, GIF, WebP · max 20 MB</span></label>
                <input type="file" class="mi-file" accept="image/*" style="font-size:11px;color:var(--text-muted);width:100%" onchange="onMiFileChange(this)">
                <div class="mi-new-preview" style="margin-top:6px"></div>
            </div>
            <div class="mi-url-wrap" style="display:none">
                <label class="wb-label" style="margin-bottom:4px">YOUTUBE URL</label>
                <input type="text" class="wb-input mi-url" placeholder="https://www.youtube.com/watch?v=..." style="font-size:12px">
            </div>
            <div style="margin-top:8px">
                <label class="wb-label" style="margin-bottom:3px">CAPTION <span class="wb-hint">optional</span></label>
                <input type="text" class="wb-input mi-caption" placeholder="Describe this media" style="font-size:12px">
            </div>
        </div>
        <button type="button" onclick="this.closest('.media-item-row').remove();updateMediaEmpty()"
                style="margin-top:20px;width:28px;height:28px;border-radius:50%;background:rgba(248,113,113,.15);color:#f87171;border:1px solid rgba(248,113,113,.3);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0">×</button>
    </div>`;
    document.getElementById('media-items-list').insertAdjacentHTML('beforeend', html);
}

function updateMediaEmpty(forceHide) {
    const list = document.getElementById('media-items-list');
    const empty = document.getElementById('media-empty');
    const hasRows = list.querySelectorAll('.media-item-row').length > 0;
    if (forceHide || hasRows) {
        if (empty) empty.remove();
    } else if (!hasRows && !empty) {
        list.insertAdjacentHTML('beforeend', '<p id="media-empty" style="font-size:12px;color:var(--text-muted);text-align:center;padding:24px 0">No media yet. Click <strong>+ Add Media</strong> to add the worker\'s profile photo, cover image, or videos.</p>');
    }
}

function onMiTypeChange(sel) {
    const row = sel.closest('.media-item-row');
    const isYt = _ytTypes.includes(sel.value);
    row.querySelector('.mi-file-wrap').style.display = isYt ? 'none' : '';
    row.querySelector('.mi-url-wrap').style.display  = isYt ? '' : 'none';
}

function onMiFileChange(input) {
    const row = input.closest('.media-item-row');
    const preview = row.querySelector('.mi-new-preview');
    if (!input.files || !input.files[0]) return;
    const url = URL.createObjectURL(input.files[0]);
    preview.innerHTML = `<img src="${url}" style="width:80px;height:54px;object-fit:cover;border-radius:6px;border:2px solid var(--accent)">`;
}

async function saveMediaItems() {
    const rows = document.querySelectorAll('.media-item-row');
    const fd = new FormData();
    fd.append('_token', _mediaCsrf);

    let idx = 0;
    for (const row of rows) {
        const type = row.querySelector('.mi-type').value;
        const caption = row.querySelector('.mi-caption').value;
        fd.append(`media_items[${idx}][type]`, type);
        fd.append(`media_items[${idx}][caption]`, caption);

        if (_ytTypes.includes(type)) {
            fd.append(`media_items[${idx}][kind]`, 'url');
            fd.append(`media_items[${idx}][url]`, row.querySelector('.mi-url').value);
        } else {
            fd.append(`media_items[${idx}][kind]`, 'file');
            fd.append(`media_items[${idx}][path]`, row.dataset.path || '');
            const fileInput = row.querySelector('.mi-file');
            if (fileInput && fileInput.files[0]) {
                fd.append(`media_items[${idx}][file]`, fileInput.files[0]);
            }
        }
        idx++;
    }

    const msg = document.getElementById('media-save-msg');
    msg.style.display = 'inline';
    msg.style.color = 'var(--text-muted)';
    msg.textContent = 'Saving…';

    try {
        const res = await fetch(_mediaUrl, {
            method: 'POST',
            body: fd,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        let data;
        try { data = await res.json(); } catch(e) { throw new Error('Server returned non-JSON (status ' + res.status + ')'); }

        if (!res.ok) {
            // Laravel validation error (422) returns {errors:{...}, message:'...'}
            const first = data.errors ? Object.values(data.errors)[0]?.[0] : (data.message || data.error || 'Save failed');
            msg.style.color = '#f87171';
            msg.textContent = first;
        } else if (data.success) {
            msg.style.color = '#4ade80';
            msg.textContent = `Saved — ${data.count} item${data.count !== 1 ? 's' : ''}`;
            // Update data-path on each row from server response so re-saves preserve paths
            const items = data.items || [];
            const rowEls = document.querySelectorAll('.media-item-row');
            items.forEach((item, i) => {
                if (rowEls[i] && item.path) rowEls[i].dataset.path = item.path;
            });
        } else {
            msg.style.color = '#f87171';
            msg.textContent = data.error || 'Save failed';
        }
    } catch (e) {
        msg.style.color = '#f87171';
        msg.textContent = e.message || 'Network error';
        console.error('saveMedia error:', e);
    }
    setTimeout(() => { msg.style.display = 'none'; }, 4000);
}

function previewImg(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeGalleryItem(id, idx) {
    const el = document.getElementById(id);
    if (el) el.remove();
    // Clear the hidden inputs so the path isn't submitted
    document.querySelectorAll(`[name="gallery_existing[${idx}][path]"]`).forEach(i => i.value = '');
}

function previewGalleryNew(input) {
    const container = document.getElementById('gallery-new-preview');
    container.innerHTML = '';
    Array.from(input.files).forEach((file, i) => {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'position:relative;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;overflow:hidden';
        const isVideo = file.type.startsWith('video/');
        const url = URL.createObjectURL(file);
        if (isVideo) {
            const v = document.createElement('video');
            v.src = url; v.style.cssText = 'width:100%;height:90px;object-fit:cover;display:block'; v.muted = true;
            wrap.appendChild(v);
        } else {
            const img = document.createElement('img');
            img.src = url; img.style.cssText = 'width:100%;height:90px;object-fit:cover;display:block';
            wrap.appendChild(img);
        }
        const cap = document.createElement('input');
        cap.type = 'text'; cap.name = `gallery_new_caption[${i}]`;
        cap.placeholder = 'Caption';
        cap.style.cssText = 'width:100%;box-sizing:border-box;background:transparent;border:none;border-top:1px solid var(--border);padding:5px 8px;font-size:10px;color:var(--text-primary);outline:none';
        wrap.appendChild(cap);
        container.appendChild(wrap);
    });
}

let stageCount = {{ count(old('stages', $worker->pipeline_stages ?? [])) }};
let qaCount    = {{ count(old('qa', $worker->qa_requirements ?? [])) }};
let credCount  = {{ count(old('credentials', isset($worker->credential) && isset($worker->credential['type']) ? [$worker->credential] : ($worker->credential ?? []))) }};
let subsCount  = {{ count(old('subscriptions', $worker->subscriptions ?? [])) }};
let clCount    = {{ count(old('changelog', $worker->version_changelog ?? [])) }};

// Auto-generate slug from name on create
@if(!$worker)
document.querySelector('[name=name]').addEventListener('input', function() {
    const slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
    document.getElementById('slug-field').value = slug;
});
@endif

// Sync color inputs
document.querySelector('[name=media_color]')?.addEventListener('input', function() {
    document.getElementById('color-text').value = this.value;
});

function addStage() {
    document.getElementById('stages-empty')?.remove();
    const tpl = document.getElementById('stage-template').innerHTML
        .replace(/__i__/g, stageCount);
    const div = document.createElement('div');
    div.innerHTML = tpl;
    document.getElementById('stages-list').appendChild(div.firstElementChild);
    renumberStages();
    stageCount++;
}

function addQa() {
    document.getElementById('qa-empty')?.remove();
    const tpl = document.getElementById('qa-template').innerHTML
        .replace(/__i__/g, qaCount);
    const div = document.createElement('div');
    div.innerHTML = tpl;
    document.getElementById('qa-list').appendChild(div.firstElementChild);
    qaCount++;
}

function addCredential() {
    document.getElementById('cred-empty')?.remove();
    const tpl = document.getElementById('credential-template').innerHTML
        .replace(/__ci__/g, credCount);
    const div = document.createElement('div');
    div.innerHTML = tpl;
    document.getElementById('credentials-list').appendChild(div.firstElementChild);
    credCount++;
}

function addSubscription() {
    document.getElementById('subs-empty')?.remove();
    const tpl = document.getElementById('sub-template').innerHTML
        .replace(/__si__/g, subsCount);
    const div = document.createElement('div');
    div.innerHTML = tpl;
    document.getElementById('subs-list').appendChild(div.firstElementChild);
    subsCount++;
}

function addChangelog() {
    document.getElementById('cl-empty')?.remove();
    const tpl = document.getElementById('changelog-template').innerHTML
        .replace(/__cli__/g, clCount);
    const div = document.createElement('div');
    div.innerHTML = tpl;
    document.getElementById('changelog-list').appendChild(div.firstElementChild);
    clCount++;
}

function removeRow(btn, listId, emptyId, emptyText) {
    const row = btn.closest('[data-index]');
    row.remove();
    renumberStages();
    if (document.getElementById(listId).querySelectorAll('[data-index]').length === 0) {
        const p = document.createElement('p');
        p.id = emptyId;
        p.style = 'font-size:12px;color:var(--text-muted);text-align:center;padding:16px 0';
        p.textContent = emptyText;
        document.getElementById(listId).appendChild(p);
    }
}

function renumberStages() {
    document.querySelectorAll('.stage-num').forEach((el, i) => el.textContent = i + 1);
}

function toggleAiBlock(checkbox) {
    const block = checkbox.closest('.wb-stage-block').querySelector('.ai-block');
    block.style.display = checkbox.checked ? 'block' : 'none';
}

function toggleQaFields(select) {
    const row = select.closest('.qa-row');
    const check = select.value;
    row.querySelector('.qa-threshold-field').style.display = check === 'VALUE_ABOVE' ? 'flex' : 'none';
    row.querySelector('.qa-values-field').style.display    = check === 'STATUS_IN'   ? 'flex' : 'none';
}

function syncStageKey(input) {
    // Job class auto-suggest from label
    const block = input.closest('.wb-stage-block');
    const jobField = block.querySelector('[name*="[job_class]"]');
    if (!jobField.value) {
        const label = input.value;
        const cc = label.replace(/(?:^|\s)\S/g, c => c.toUpperCase()).replace(/\s+/g, '') + 'Job';
        jobField.placeholder = cc;
    }
}

// Re-init existing AI blocks on page load
document.querySelectorAll('.ai-toggle').forEach(cb => {
    if (cb.checked) cb.closest('.wb-stage-block')?.querySelector('.ai-block')?.style?.setProperty('display','block');
});

// Re-init existing QA check selects
document.querySelectorAll('.qa-row select[name*="[check]"]').forEach(sel => toggleQaFields(sel));
</script>
</x-app-layout>

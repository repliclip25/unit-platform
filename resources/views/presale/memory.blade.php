<x-onboarding-shell title="Memory Training — UNIT" :steps="$steps" security-text="Your brand assets live in your own Google Drive, not on UNIT's servers.">

    <x-slot:styles>
    <style>
    /* No brand-video worker art exists yet — a plain light panel reads better
       than the fade-to-nothing look the shell assumes an illustration fills. */
    .ob-hero{background:#F4F3F1}
    .pm-hero-content{max-width:560px;margin:0 auto}

    /* Give the right (profile/coverage) column more room — card structure and
       shadow untouched, just the hero/profile split within it. */
    @media(min-width:1025px){
        .ob-card{grid-template-columns:1fr 420px}
    }
    .pm-flash{display:flex;align-items:center;gap:8px;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:12.5px;font-weight:600;flex-shrink:0}
    .pm-flash.success{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.22);color:#16a34a}
    .pm-flash.error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.22);color:#dc2626}

    .pm-field-full{grid-column:1/-1}
    .pm-field textarea{width:100%;border:1.5px solid #E5E7EB;border-radius:8px;padding:8px 10px;font-size:12.5px;font-family:inherit;color:#0D0D0D;background:#fff;outline:none;resize:vertical;min-height:64px}
    .pm-field textarea:focus{border-color:#0D0D0D}
    .pm-field input[type="color"]{width:100%;height:34px;border-radius:8px;border:1.5px solid #E5E7EB;padding:2px;cursor:pointer}

    .pm-drive-connected{display:flex;align-items:center;gap:10px;margin-bottom:4px}
    .pm-drive-connected svg{width:16px;height:16px;stroke:#22c55e;stroke-width:2;fill:none;flex-shrink:0}
    .pm-drive-email{font-size:12.5px;font-weight:700;color:#0D0D0D}
    .pm-drive-link{font-size:11.5px;color:#6B7280;text-decoration:none}
    .pm-drive-link:hover{color:#0D0D0D}
    .pm-drive-quota{font-size:11px;color:#9CA3AF;margin-top:2px}

    .pm-cat-list{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:12px}
    .pm-cat-chip{display:inline-flex;align-items:center;gap:7px;padding:5px 6px 5px 12px;border-radius:99px;background:#ECEAE6;font-size:12px;font-weight:600;color:#0D0D0D}
    .pm-cat-chip form{display:flex}
    .pm-cat-remove{width:15px;height:15px;border-radius:50%;border:none;background:transparent;color:#9CA3AF;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0}
    .pm-cat-remove:hover{color:#0D0D0D;background:#DCDCDC}
    .pm-cat-remove svg{width:9px;height:9px;stroke:currentColor;stroke-width:2.6;fill:none}
    .pm-cat-add{display:flex;gap:8px}

    .pm-upload-row{display:flex;gap:8px;align-items:center;margin-bottom:12px}
    .pm-select{border:1.5px solid #E5E7EB;border-radius:8px;padding:8px 10px;font-size:12.5px;font-family:inherit;color:#0D0D0D;background:#fff;outline:none;max-width:160px;flex-shrink:0}
    .pm-file{flex:1;font-size:12px}

    .pm-asset-row{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #F0F0F0}
    .pm-asset-row:last-child{border-bottom:none}
    .pm-asset-icon{width:26px;height:26px;border-radius:7px;background:#ECEAE6;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .pm-asset-icon svg{width:12px;height:12px;stroke:#6B7280;stroke-width:1.8;fill:none}
    .pm-asset-name{font-size:12.5px;font-weight:600;color:#0D0D0D}
    .pm-asset-sub{font-size:11px;color:#9CA3AF}
    .pm-asset-link{margin-left:auto;font-size:11.5px;color:#6B7280;text-decoration:none;flex-shrink:0}
    .pm-asset-link:hover{color:#0D0D0D}
    .pm-empty-note{font-size:12px;color:#9CA3AF;padding:6px 0}

    .pm-mini-icon{width:20px;height:20px;border-radius:6px;background:#ECEAE6;display:flex;align-items:center;justify-content:center;flex-shrink:0}
    .pm-mini-icon svg{width:10px;height:10px;stroke:#6B7280;stroke-width:2;fill:none}
    </style>
    </x-slot:styles>

    <x-slot:hero>
        <div class="ob-hero-content pm-hero-content">
            <div class="ob-step-tag">
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                MEMORY TRAINING
            </div>
            <div class="ob-h1">Train the <span class="ob-gold">brand-video</span> worker's memory.</div>
            <div class="ob-sub">Everything here becomes what the worker knows about your brand once it launches. Assets you upload go straight into your own Google Drive.</div>

            @if (session('success'))
                <div class="pm-flash success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="pm-flash error">{{ session('error') }}</div>
            @endif

            {{-- Business profile --}}
            <div class="ob-form">
                <div class="ob-form-title">Business Profile</div>
                <form method="POST" action="{{ route('presale.profile.save') }}">
                    @csrf
                    <div class="ob-form-grid">
                        <div class="ob-field">
                            <label>Business name</label>
                            <input type="text" name="business_name" value="{{ old('business_name', $profile->business_name ?? '') }}" placeholder="Acme Inc.">
                        </div>
                        <div class="ob-field">
                            <label>Tagline</label>
                            <input type="text" name="tagline" value="{{ old('tagline', $profile->tagline ?? '') }}" placeholder="What you're known for">
                        </div>
                        <div class="ob-field pm-field-full pm-field">
                            <label>Brand voice</label>
                            <textarea name="brand_voice" placeholder="Formal, playful, technical...">{{ old('brand_voice', $profile->brand_voice ?? '') }}</textarea>
                        </div>
                        <div class="ob-field pm-field">
                            <label>Primary color</label>
                            <input type="color" name="primary_color" value="{{ old('primary_color', $profile->primary_color ?? '#0D0D0D') }}">
                        </div>
                        <div class="ob-field pm-field">
                            <label>Secondary color</label>
                            <input type="color" name="secondary_color" value="{{ old('secondary_color', $profile->secondary_color ?? '#F5C518') }}">
                        </div>
                        <div class="ob-field pm-field-full pm-field">
                            <label>Reference links</label>
                            <textarea name="reference_links" placeholder="One per line: website, existing videos, social profiles...">{{ old('reference_links', $profile->reference_links ?? '') }}</textarea>
                        </div>
                        <div class="ob-field pm-field-full pm-field">
                            <label>Notes</label>
                            <textarea name="notes" placeholder="Do's, don'ts, past feedback...">{{ old('notes', $profile->notes ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="ob-form-actions">
                        <button type="submit" class="btn-add">Save profile</button>
                    </div>
                </form>
            </div>

            {{-- Google Drive --}}
            <div class="ob-form">
                <div class="ob-form-title">Google Drive</div>
                @if ($credential)
                    <div class="pm-drive-connected">
                        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span class="pm-drive-email">{{ $credential->drive_email }}</span>
                        @if ($credential->root_folder_url)
                            <a href="{{ $credential->root_folder_url }}" target="_blank" rel="noopener" class="pm-drive-link">Open folder →</a>
                        @endif
                    </div>
                    @if ($quota)
                        <div class="pm-drive-quota">
                            @if ($quota['limit'] === null)
                                Unlimited storage
                            @else
                                {{ number_format($quota['available'] / 1073741824, 1) }} GB free of {{ number_format($quota['limit'] / 1073741824, 1) }} GB
                            @endif
                        </div>
                    @endif
                @else
                    <p class="ob-hint" style="margin-bottom:10px">Connect the Google account you want your brand assets stored in.</p>
                    <a href="{{ route('presale.drive.authorize') }}" class="btn-add" style="display:inline-flex;text-decoration:none">Connect Google Drive</a>
                @endif
            </div>

            {{-- Folders --}}
            <div class="ob-form">
                <div class="ob-form-title">Folders</div>
                <div class="pm-cat-list">
                    @forelse ($categories as $category)
                        <span class="pm-cat-chip">
                            {{ $category->name }}
                            <form method="POST" action="{{ route('presale.categories.destroy', $category->id) }}" onsubmit="return confirm('Remove the &quot;{{ $category->name }}&quot; folder from this list? Files already in Drive are not deleted.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="pm-cat-remove" title="Remove"><svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </form>
                        </span>
                    @empty
                        <span class="ob-hint">No folders yet.</span>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('presale.categories.store') }}" class="pm-cat-add">
                    @csrf
                    <input type="text" name="name" placeholder="e.g. B-roll, Testimonials" required maxlength="100" style="flex:1;border:1.5px solid #E5E7EB;border-radius:8px;padding:8px 10px;font-size:12.5px;font-family:inherit">
                    <button type="submit" class="btn-add" style="flex-shrink:0">Add</button>
                </form>
            </div>

            {{-- Assets --}}
            <div class="ob-form">
                <div class="ob-form-title">Brand Assets</div>
                @if ($credential)
                    <form method="POST" action="{{ route('presale.drive.upload') }}" enctype="multipart/form-data" class="pm-upload-row">
                        @csrf
                        <select name="category_id" required class="pm-select">
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <input type="file" name="asset" accept="image/*,video/*,.pdf,.ppt,.pptx,.key" required class="pm-file">
                        <button type="submit" class="btn-add" style="flex-shrink:0">Upload</button>
                    </form>
                    @error('asset')<div class="pm-flash error">{{ $message }}</div>@enderror
                @else
                    <p class="ob-hint" style="margin-bottom:10px">Connect Google Drive above to start uploading.</p>
                @endif

                @forelse ($assets as $asset)
                    <div class="pm-asset-row">
                        <div class="pm-asset-icon">
                            @if ($asset->kind === 'video')
                                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.55-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.45.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            @else
                                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @endif
                        </div>
                        <div>
                            <div class="pm-asset-name">{{ $asset->name }}</div>
                            <div class="pm-asset-sub">{{ $asset->category_name ?? 'Uncategorized' }} &middot; {{ \Illuminate\Support\Carbon::parse($asset->uploaded_at)->diffForHumans() }}</div>
                        </div>
                        @if ($asset->web_view_link)
                            <a href="{{ $asset->web_view_link }}" target="_blank" rel="noopener" class="pm-asset-link">Open</a>
                        @endif
                    </div>
                @empty
                    <p class="pm-empty-note">No assets uploaded yet.</p>
                @endforelse
            </div>
        </div>
    </x-slot:hero>

    <x-slot:profile>
        <div class="emp-eyebrow">Memory Training</div>
        <div class="emp-name">{{ $profile->business_name ?? auth()->user()->name }}</div>
        <div class="emp-role">Training data for the brand-video worker</div>
        <hr class="emp-divider">

        <div class="ob-coverage-label">
            <span class="ob-coverage-title">Memory Coverage</span>
            <span class="ob-coverage-pct">{{ $coveragePct }}%</span>
        </div>
        <div class="ob-coverage-bar"><div class="ob-coverage-fill" style="width:{{ $coveragePct }}%"></div></div>
        <div class="ob-coverage-note">
            @if ($coveragePct === 100)
                Fully trained — the worker will have everything it needs on day one.
            @else
                {{ 4 - (int) round($coveragePct / 25) }} step{{ (4 - (int) round($coveragePct / 25)) === 1 ? '' : 's' }} left to finish training.
            @endif
        </div>

        <div class="ob-clients-title">Uploaded so far</div>
        @forelse ($assets->take(5) as $asset)
            <div class="ob-client-row">
                <span class="pm-mini-icon">
                    @if ($asset->kind === 'video')
                        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.55-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.45.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    @else
                        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    @endif
                </span>
                <span class="ob-client-name">{{ $asset->name }}</span>
            </div>
        @empty
            <div class="ob-clients-empty">Nothing uploaded yet.</div>
        @endforelse

        <div style="margin-top:auto">
            @if ($credential && $credential->root_folder_url)
                <a href="{{ $credential->root_folder_url }}" target="_blank" rel="noopener" class="btn-continue is-active">
                    Open Brand Folder in Drive
                    <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @else
                <button type="button" class="btn-continue" disabled>
                    Connect Drive first
                    <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            @endif
        </div>
    </x-slot:profile>

</x-onboarding-shell>

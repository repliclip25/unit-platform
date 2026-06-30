<div class="tpl-card" id="tpl-card-{{ $tpl->id }}">
    <div class="tpl-head" onclick="toggleTpl({{ $tpl->id }})">
        <div class="flex items-center gap-3 min-w-0">
            <span class="{{ $badge }}" @if(!empty($badgeStyle)) style="{{ $badgeStyle }}" @endif>{{ $badgeText }}</span>
            <span class="{{ $tpl->active ? 'tpl-badge tpl-badge-on' : 'tpl-badge tpl-badge-off' }}">{{ $tpl->active ? 'Active' : 'Paused' }}</span>
            <div class="min-w-0">
                <div class="tpl-label">{{ $tpl->label }}</div>
                <div class="tpl-sub">{{ $tpl->subject }}</div>
            </div>
        </div>
        <svg id="tpl-chevron-{{ $tpl->id }}" style="width:15px;height:15px;color:var(--text-muted);transition:transform .2s;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>
    <div class="tpl-body" id="tpl-body-{{ $tpl->id }}">
        <form method="POST" action="{{ route('admin.messaging.update', $tpl->id) }}">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <div style="grid-column:1/-1">
                    <div class="tpl-lbl">Subject</div>
                    <input name="subject" class="tpl-input" value="{{ $tpl->subject }}">
                </div>
                <div>
                    <div class="tpl-lbl">From Name</div>
                    <input name="from_name" class="tpl-input" value="{{ $tpl->from_name }}">
                </div>
                <div style="display:flex;align-items:flex-end">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-secondary);cursor:pointer">
                        <input type="checkbox" name="active" value="1" {{ $tpl->active ? 'checked' : '' }} style="width:15px;height:15px">
                        Active
                    </label>
                </div>
                @if($tpl->topic !== null)
                <div style="grid-column:1/-1">
                    <div class="tpl-lbl">Topic / Focus <span style="font-weight:400;text-transform:none;letter-spacing:0">(used by AI rewrite)</span></div>
                    <input name="topic" class="tpl-input" value="{{ $tpl->topic }}" placeholder="What angle should AI take when rewriting this?">
                </div>
                @endif
                <div style="grid-column:1/-1">
                    <div class="tpl-lbl">Body</div>
                    <textarea name="body" class="tpl-textarea">{{ $tpl->body }}</textarea>
                    <div class="tpl-hint">Placeholders: {name} · {app_url}</div>
                </div>
            </div>
            <div class="tpl-actions">
                <button type="submit" class="m-btn m-btn-gold">Save</button>
                <button type="button" class="m-btn m-btn-blue" onclick="openRewrite({{ $tpl->id }}, {{ json_encode($tpl->subject) }}, {{ json_encode($tpl->body) }})">
                    <svg style="width:12px;height:12px;display:inline;margin-right:4px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    AI Rewrite
                </button>
                <button type="button" class="m-btn m-btn-out" onclick="doTestSend({{ $tpl->id }}, this)">Send Test →</button>
                <button type="button" class="m-btn m-btn-red" onclick="doReset({{ $tpl->id }})">Reset</button>
                @if($tpl->last_ai_rewrite_at)
                <span class="text-xs" style="color:var(--text-muted);margin-left:auto">AI rewritten {{ \Carbon\Carbon::parse($tpl->last_ai_rewrite_at)->diffForHumans() }}</span>
                @endif
            </div>
        </form>
    </div>
</div>

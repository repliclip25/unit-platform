<div class="stage-row wb-stage-block" data-index="{{ $i }}">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
        <span style="font-size:11px;font-weight:700;color:var(--text-muted)">STAGE <span class="stage-num">{{ $i + 1 }}</span></span>
        <button type="button" onclick="removeRow(this, 'stages-list', 'stages-empty', 'No stages yet. Add your first pipeline stage below.')" style="font-size:11px;color:#f87171;background:none;border:none;cursor:pointer">Remove</button>
    </div>
    <div class="wb-row-3" style="margin-bottom:10px">
        <div class="wb-field">
            <label class="wb-label">STAGE LABEL</label>
            <input type="text" name="stages[{{ $i }}][label]" value="{{ $stage['label'] ?? '' }}" placeholder="Read Email" class="wb-input" oninput="syncStageKey(this)">
        </div>
        <div class="wb-field">
            <label class="wb-label">JOB CLASS NAME</label>
            <input type="text" name="stages[{{ $i }}][job_class]" value="{{ $stage['job_class'] ?? '' }}" placeholder="ReadEmailJob" class="wb-input wb-mono">
        </div>
        <div class="wb-field">
            <label class="wb-label">ICON</label>
            <select name="stages[{{ $i }}][icon]" class="wb-input">
                @foreach(['check','mail','tag','brain','log','template','draft','send','bolt','search'] as $ic)
                <option value="{{ $ic }}" {{ ($stage['icon'] ?? 'check') === $ic ? 'selected' : '' }}>{{ $ic }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="wb-field" style="margin-bottom:10px">
        <label class="wb-label">STAGE DESCRIPTION</label>
        <input type="text" name="stages[{{ $i }}][sub]" value="{{ $stage['sub'] ?? '' }}" placeholder="Parse & extract fields from raw email" class="wb-input">
    </div>
    <div style="display:flex;gap:12px;margin-bottom:12px;align-items:center">
        <label style="display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer">
            <input type="checkbox" name="stages[{{ $i }}][uses_ai]" value="1" class="ai-toggle" onchange="toggleAiBlock(this)" {{ !empty($stage['uses_ai']) ? 'checked' : '' }}>
            Uses AI (Claude)
        </label>
    </div>
    <div class="ai-block" style="display:{{ !empty($stage['uses_ai']) ? 'block' : 'none' }};background:var(--bg-raised);border:1px solid var(--border-soft);border-radius:10px;padding:14px;margin-bottom:4px">
        <p style="font-size:10px;font-weight:700;color:var(--accent-text);text-transform:uppercase;margin-bottom:10px">AI Configuration</p>
        <div class="wb-row-3" style="margin-bottom:10px">
            <div class="wb-field">
                <label class="wb-label">MODEL OVERRIDE</label>
                <input type="text" name="stages[{{ $i }}][model]" value="{{ $stage['model'] ?? '' }}" placeholder="claude-sonnet-4-6" class="wb-input wb-mono">
            </div>
            <div class="wb-field">
                <label class="wb-label">OUTPUT FORMAT</label>
                <select name="stages[{{ $i }}][output_format]" class="wb-input">
                    <option value="json" {{ ($stage['output_format'] ?? 'json') === 'json' ? 'selected' : '' }}>JSON</option>
                    <option value="text" {{ ($stage['output_format'] ?? '') === 'text' ? 'selected' : '' }}>Text</option>
                </select>
            </div>
            <div class="wb-field">
                <label class="wb-label">MAX TOKENS</label>
                <input type="number" name="stages[{{ $i }}][max_tokens]" value="{{ $stage['max_tokens'] ?? 500 }}" class="wb-input" min="50" max="8000">
            </div>
        </div>
        <div class="wb-field" style="margin-bottom:10px">
            <label class="wb-label">SYSTEM PROMPT</label>
            <textarea name="stages[{{ $i }}][system_prompt]" rows="3" placeholder="You are an expert..." class="wb-input" style="font-family:monospace;font-size:12px;resize:vertical">{{ $stage['system_prompt'] ?? '' }}</textarea>
        </div>
        <div class="wb-field">
            <label class="wb-label">USER PROMPT TEMPLATE</label>
            <textarea name="stages[{{ $i }}][user_prompt]" rows="5" class="wb-input" style="font-family:monospace;font-size:12px;resize:vertical">{{ $stage['user_prompt'] ?? '' }}</textarea>
        </div>
        <div class="wb-field" style="margin-top:10px">
            <label class="wb-label">EXPECTED OUTPUT SHAPE</label>
            <input type="text" name="stages[{{ $i }}][output_shape]" value="{{ $stage['output_shape'] ?? '' }}" class="wb-input wb-mono">
        </div>
    </div>
</div>

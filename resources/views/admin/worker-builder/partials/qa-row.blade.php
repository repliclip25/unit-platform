<div class="qa-row" data-index="{{ $i }}" style="background:var(--bg-raised);border:1px solid var(--border-soft);border-radius:10px;padding:14px;margin-bottom:8px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
        <span style="font-size:11px;font-weight:700;color:var(--text-muted)">QA CHECK</span>
        <button type="button" onclick="removeRow(this, 'qa-list', 'qa-empty', 'No QA checks defined yet.')" style="font-size:11px;color:#f87171;background:none;border:none;cursor:pointer">Remove</button>
    </div>
    <div class="wb-row-3" style="margin-bottom:10px">
        <div class="wb-field">
            <label class="wb-label">STAGE KEY</label>
            <input type="text" name="qa[{{ $i }}][stage]" value="{{ $q['stage'] ?? '' }}" placeholder="read / classify / push" class="wb-input wb-mono">
        </div>
        <div class="wb-field">
            <label class="wb-label">CHECK TYPE</label>
            <select name="qa[{{ $i }}][check]" class="wb-input" onchange="toggleQaFields(this)">
                @foreach(['FIELD_NOT_NULL','FIELD_NOT_EMPTY','VALUE_ABOVE','VALID_EMAIL','STATUS_IN'] as $chk)
                <option value="{{ $chk }}" {{ ($q['check'] ?? '') === $chk ? 'selected' : '' }}>{{ $chk }}</option>
                @endforeach
            </select>
        </div>
        <div class="wb-field">
            <label class="wb-label">LABEL</label>
            <input type="text" name="qa[{{ $i }}][label]" value="{{ $q['label'] ?? '' }}" placeholder="Subject must not be null" class="wb-input">
        </div>
    </div>
    <div class="wb-row-3">
        <div class="wb-field qa-field-field">
            <label class="wb-label">FIELD</label>
            <input type="text" name="qa[{{ $i }}][field]" value="{{ $q['field'] ?? '' }}" placeholder="output.subject" class="wb-input wb-mono">
        </div>
        <div class="wb-field qa-threshold-field" style="display:{{ ($q['check'] ?? '') === 'VALUE_ABOVE' ? 'flex' : 'none' }};flex-direction:column">
            <label class="wb-label">THRESHOLD</label>
            <input type="number" name="qa[{{ $i }}][threshold]" value="{{ $q['threshold'] ?? '' }}" step="0.01" min="0" max="1" class="wb-input">
        </div>
        <div class="wb-field qa-values-field" style="display:{{ ($q['check'] ?? '') === 'STATUS_IN' ? 'flex' : 'none' }};flex-direction:column">
            <label class="wb-label">VALUES</label>
            <input type="text" name="qa[{{ $i }}][values]" value="{{ is_array($q['values'] ?? null) ? implode(',', $q['values']) : ($q['values'] ?? '') }}" placeholder="draft_ready,approved,sent" class="wb-input wb-mono">
        </div>
    </div>
</div>

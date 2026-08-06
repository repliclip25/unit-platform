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

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Brand Memory - UNIT</title>
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
button,select,input,textarea{outline:none}
html,body{height:100%}

:root,[data-theme="dark"]{
  --db-bg:#0D0D0D; --db-card:#1A1A1A; --db-text:#F5F5F5; --db-text-muted:#9CA3AF;
  --db-border:rgba(255,255,255,.14); --db-chip:#262626;
  --db-invert-bg:#F5F5F5; --db-invert-text:#0D0D0D;
}
[data-theme="light"]{
  --db-bg:#F4F3F1; --db-card:#ffffff; --db-text:#0D0D0D; --db-text-muted:#6B7280;
  --db-border:#E5E7EB; --db-chip:#ECEAE6;
  --db-invert-bg:#0D0D0D; --db-invert-text:#ffffff;
}
body{font-family:'Inter',sans-serif;background:var(--db-bg);color:var(--db-text);-webkit-font-smoothing:antialiased}

.bm-topbar{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid var(--db-border)}
.bm-logo{font-size:20px;font-weight:900;letter-spacing:-.04em;color:var(--db-text);text-decoration:none}
.bm-topbar-right{display:flex;align-items:center;gap:14px}
.bm-topbar-name{font-size:13px;font-weight:700;color:var(--db-text)}
.bm-logout{font-size:12.5px;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit}
.bm-logout:hover{color:var(--db-text)}

.bm-wrap{max-width:760px;margin:0 auto;padding:32px 24px 80px}
.bm-badge{display:inline-flex;align-items:center;gap:6px;font-size:9.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:5px 12px;border-radius:99px;background:var(--db-chip);color:var(--db-text-muted);margin-bottom:14px}
.bm-h1{font-size:1.6rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.bm-sub{font-size:13.5px;color:var(--db-text-muted);margin-top:4px;max-width:560px}

.bm-status{border-radius:12px;padding:10px 14px;font-size:13.5px;margin:20px 0}
.bm-status.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#22c55e}
.bm-status.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444}

.bm-section{margin-top:28px}
.bm-section-title{font-size:13.5px;font-weight:700;color:var(--db-text);margin-bottom:2px}
.bm-section-sub{font-size:12.5px;color:var(--db-text-muted);margin-bottom:14px}

.bm-card{border:1px solid var(--db-border);border-radius:16px;padding:20px}

.bm-field{margin-bottom:14px}
.bm-field-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.bm-label{display:block;font-size:11.5px;font-weight:600;color:var(--db-text-muted);margin-bottom:6px}
.bm-input,.bm-textarea{width:100%;border-radius:10px;padding:10px 13px;font-size:13.5px;background:var(--db-bg);border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.bm-textarea{resize:vertical;min-height:76px}
.bm-input:focus,.bm-textarea:focus{outline:none;border-color:var(--db-invert-bg)}
.bm-color-input{width:100%;height:38px;border-radius:10px;border:1px solid var(--db-border);background:var(--db-bg);padding:3px;cursor:pointer}

.bm-btn{padding:10px 18px;border-radius:9px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text)}
.bm-btn:hover{opacity:.9}
.bm-btn-secondary{padding:10px 18px;border-radius:9px;border:1px solid var(--db-border);background:transparent;color:var(--db-text);font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.bm-btn-secondary:hover{background:var(--db-chip)}

.bm-drive-row{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.bm-drive-connected{display:flex;align-items:center;gap:10px}
.bm-drive-connected svg{width:16px;height:16px;stroke:#22c55e;fill:none;stroke-width:2;flex-shrink:0}
.bm-drive-email{font-size:13px;font-weight:600;color:var(--db-text)}
.bm-drive-folder{font-size:12px;color:var(--db-text-muted);text-decoration:none}
.bm-drive-folder:hover{color:var(--db-text)}

.bm-row{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--db-border)}
.bm-row:last-child{border-bottom:none}
.bm-row-icon{width:32px;height:32px;border-radius:8px;background:var(--db-chip);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.bm-row-icon svg{width:15px;height:15px;stroke:var(--db-text-muted);fill:none;stroke-width:1.8}
.bm-row-name{font-size:13px;font-weight:600;color:var(--db-text)}
.bm-row-sub{font-size:12px;color:var(--db-text-muted);margin-top:1px;text-transform:capitalize}
.bm-row-link{margin-left:auto;font-size:12.5px;color:var(--db-text-muted);text-decoration:none;flex-shrink:0}
.bm-row-link:hover{color:var(--db-text)}
.bm-empty{padding:24px 0;text-align:center;font-size:13px;color:var(--db-text-muted)}

.bm-cat-list{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
.bm-cat-chip{display:inline-flex;align-items:center;gap:8px;padding:6px 8px 6px 14px;border-radius:99px;background:var(--db-chip);font-size:12.5px;font-weight:600;color:var(--db-text)}
.bm-cat-chip form{display:flex}
.bm-cat-remove{width:16px;height:16px;border-radius:50%;border:none;background:transparent;color:var(--db-text-muted);cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0}
.bm-cat-remove:hover{color:var(--db-text);background:var(--db-border)}
.bm-cat-remove svg{width:10px;height:10px;stroke:currentColor;stroke-width:2.4;fill:none}
.bm-cat-add{display:flex;gap:8px}
.bm-select{width:100%;border-radius:10px;padding:10px 13px;font-size:13.5px;background:var(--db-bg);border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
</style>
<script>
(function () {
  var saved = localStorage.getItem('unit-theme-v2') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();
</script>
</head>
<body>

<div class="bm-topbar">
  <a href="{{ url('/') }}" class="bm-logo">UNIT</a>
  <div class="bm-topbar-right">
    <span class="bm-topbar-name">{{ auth()->user()->name }}</span>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="bm-logout">Logout</button></form>
  </div>
</div>

<div class="bm-wrap">
  <div class="bm-badge">EARLY ACCESS &middot; BRAND VIDEO WORKER</div>
  <div class="bm-h1">Brand Memory</div>
  <div class="bm-sub">Everything you enter here trains the brand video worker before it launches. Images and video you upload go straight into your own Google Drive.</div>

  @if (session('success'))
    <div class="bm-status success">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="bm-status error">{{ session('error') }}</div>
  @endif

  {{-- Brand profile --}}
  <div class="bm-section">
    <div class="bm-section-title">Brand profile</div>
    <div class="bm-section-sub">The facts and voice the worker will draw on when it drafts videos for you.</div>
    <form method="POST" action="{{ route('presale.profile.save') }}" class="bm-card">
      @csrf
      <div class="bm-field-row">
        <div class="bm-field">
          <label class="bm-label" for="business_name">Business name</label>
          <input class="bm-input" id="business_name" name="business_name" value="{{ old('business_name', $profile->business_name ?? '') }}" placeholder="Acme Inc.">
        </div>
        <div class="bm-field">
          <label class="bm-label" for="tagline">Tagline</label>
          <input class="bm-input" id="tagline" name="tagline" value="{{ old('tagline', $profile->tagline ?? '') }}" placeholder="What you're known for">
        </div>
      </div>
      <div class="bm-field">
        <label class="bm-label" for="brand_voice">Brand voice</label>
        <textarea class="bm-textarea" id="brand_voice" name="brand_voice" placeholder="How should the worker sound? Formal, playful, technical...">{{ old('brand_voice', $profile->brand_voice ?? '') }}</textarea>
      </div>
      <div class="bm-field-row">
        <div class="bm-field">
          <label class="bm-label" for="primary_color">Primary color</label>
          <input class="bm-color-input" id="primary_color" type="color" name="primary_color" value="{{ old('primary_color', $profile->primary_color ?? '#0D0D0D') }}">
        </div>
        <div class="bm-field">
          <label class="bm-label" for="secondary_color">Secondary color</label>
          <input class="bm-color-input" id="secondary_color" type="color" name="secondary_color" value="{{ old('secondary_color', $profile->secondary_color ?? '#F5C518') }}">
        </div>
      </div>
      <div class="bm-field">
        <label class="bm-label" for="reference_links">Reference links</label>
        <textarea class="bm-textarea" id="reference_links" name="reference_links" placeholder="One per line: website, existing videos, social profiles...">{{ old('reference_links', $profile->reference_links ?? '') }}</textarea>
      </div>
      <div class="bm-field">
        <label class="bm-label" for="notes">Notes</label>
        <textarea class="bm-textarea" id="notes" name="notes" placeholder="Anything else the worker should know: do's, don'ts, past feedback...">{{ old('notes', $profile->notes ?? '') }}</textarea>
      </div>
      <button type="submit" class="bm-btn">Save brand profile</button>
    </form>
  </div>

  {{-- Google Drive --}}
  <div class="bm-section">
    <div class="bm-section-title">Google Drive</div>
    <div class="bm-section-sub">Logos, sample videos, and images live in your own Drive, not on UNIT's servers.</div>
    <div class="bm-card">
      <div class="bm-drive-row">
        @if ($credential)
          <div class="bm-drive-connected">
            <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <div>
              <div class="bm-drive-email">{{ $credential->drive_email }}</div>
              @if ($credential->root_folder_url)
                <a href="{{ $credential->root_folder_url }}" target="_blank" rel="noopener" class="bm-drive-folder">Open brand folder in Drive</a>
              @endif
            </div>
          </div>
        @else
          <div>
            <div class="bm-row-name">Not connected</div>
            <div class="bm-row-sub" style="text-transform:none">Connect Drive to start uploading assets.</div>
          </div>
          <a href="{{ route('presale.drive.authorize') }}" class="bm-btn-secondary">Connect Google Drive</a>
        @endif
      </div>
    </div>
  </div>

  {{-- Categories --}}
  <div class="bm-section">
    <div class="bm-section-title">Folders</div>
    <div class="bm-section-sub">These become real folders inside your Drive brand folder. Add whatever you need, rename or remove anytime.</div>
    <div class="bm-card">
      <div class="bm-cat-list">
        @forelse ($categories as $category)
          <span class="bm-cat-chip">
            {{ $category->name }}
            <form method="POST" action="{{ route('presale.categories.destroy', $category->id) }}" onsubmit="return confirm('Remove the &quot;{{ $category->name }}&quot; folder from this list? Files already in Drive are not deleted.')">
              @csrf @method('DELETE')
              <button type="submit" class="bm-cat-remove" title="Remove"><svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </form>
          </span>
        @empty
          <span class="bm-row-sub">No folders yet.</span>
        @endforelse
      </div>
      <form method="POST" action="{{ route('presale.categories.store') }}" class="bm-cat-add">
        @csrf
        <input class="bm-input" type="text" name="name" placeholder="e.g. B-roll, Testimonials, Product shots" required maxlength="100" style="flex:1">
        <button type="submit" class="bm-btn-secondary" style="flex-shrink:0">Add folder</button>
      </form>
    </div>
  </div>

  {{-- Assets --}}
  <div class="bm-section">
    <div class="bm-section-title">Brand assets</div>
    <div class="bm-section-sub">Upload into any folder above. Files go straight into the matching Drive subfolder.</div>
    <div class="bm-card">
      @if ($credential)
        <form method="POST" action="{{ route('presale.drive.upload') }}" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:center;margin-bottom:{{ $assets->isEmpty() ? '0' : '16px' }}">
          @csrf
          <select name="category_id" required class="bm-select" style="max-width:180px;flex-shrink:0">
            @foreach ($categories as $category)
              <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
          </select>
          <input type="file" name="asset" accept="image/*,video/*,.pdf,.ppt,.pptx,.key" required class="bm-input" style="flex:1">
          <button type="submit" class="bm-btn" style="flex-shrink:0">Upload</button>
        </form>
        @error('asset')<div class="bm-status error">{{ $message }}</div>@enderror
      @endif

      @forelse ($assets as $asset)
        <div class="bm-row">
          <div class="bm-row-icon">
            @if ($asset->kind === 'video')
              <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.55-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.45.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            @else
              <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            @endif
          </div>
          <div>
            <div class="bm-row-name">{{ $asset->name }}</div>
            <div class="bm-row-sub">{{ $asset->category_name ?? 'Uncategorized' }} &middot; uploaded {{ \Illuminate\Support\Carbon::parse($asset->uploaded_at)->diffForHumans() }}</div>
          </div>
          @if ($asset->web_view_link)
            <a href="{{ $asset->web_view_link }}" target="_blank" rel="noopener" class="bm-row-link">Open in Drive</a>
          @endif
        </div>
      @empty
        <div class="bm-empty">{{ $credential ? 'No assets uploaded yet.' : 'Connect Google Drive above to start uploading.' }}</div>
      @endforelse
    </div>
  </div>

  <x-self-learn pageKey="presale.dashboard" title="Welcome to Brand Memory" body="This page trains the brand video worker before it launches. Save your brand profile and connect Google Drive to start uploading logos, images, and sample videos." />
</div>

</body>
</html>

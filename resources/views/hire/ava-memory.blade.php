<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>AVA Memory - UNIT</title>
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

.am-topbar{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid var(--db-border)}
.am-logo{font-size:20px;font-weight:900;letter-spacing:-.04em;color:var(--db-text);text-decoration:none}
.am-topbar-right{display:flex;align-items:center;gap:14px}
.am-topbar-name{font-size:13px;font-weight:700;color:var(--db-text)}
.am-logout{font-size:12.5px;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit}
.am-logout:hover{color:var(--db-text)}

.am-wrap{max-width:640px;margin:0 auto;padding:32px 24px 80px}
.am-badge{display:inline-flex;align-items:center;gap:6px;font-size:9.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:5px 12px;border-radius:99px;background:var(--db-chip);color:var(--db-text-muted);margin-bottom:14px}
.am-h1{font-size:1.6rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.am-sub{font-size:13.5px;color:var(--db-text-muted);margin-top:4px;max-width:520px}

.am-coverage{margin-top:20px;padding:16px 18px;border:1px solid var(--db-border);border-radius:14px;background:var(--db-card)}
.am-coverage-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.am-coverage-label{font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--db-text-muted)}
.am-coverage-pct{font-size:13px;font-weight:700;color:var(--db-text)}
.am-coverage-track{height:6px;border-radius:99px;background:var(--db-chip);overflow:hidden}
.am-coverage-fill{height:100%;border-radius:99px;background:#22c55e;transition:width .2s ease}
.am-coverage-hint{font-size:11.5px;color:var(--db-text-muted);margin-top:8px}

.am-status{border-radius:12px;padding:10px 14px;font-size:13.5px;margin:20px 0}
.am-status.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#22c55e}
.am-status.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444}

.am-card{border:1px solid var(--db-border);border-radius:16px;padding:20px;margin-top:24px}
.am-card-title{font-size:13.5px;font-weight:700;color:var(--db-text);margin-bottom:14px}
.am-field{margin-bottom:14px}
.am-label{display:block;font-size:11.5px;font-weight:600;color:var(--db-text-muted);margin-bottom:6px}
.am-input,.am-textarea{width:100%;border-radius:10px;padding:10px 13px;font-size:13.5px;background:var(--db-bg);border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.am-textarea{resize:vertical;min-height:64px}
.am-input:focus,.am-textarea:focus{border-color:var(--db-invert-bg)}
.am-btn{padding:10px 18px;border-radius:9px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text)}
.am-btn:hover{opacity:.9}

.am-row{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--db-border)}
.am-row:last-child{border-bottom:none}
.am-row-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;flex-shrink:0}
.am-row-name{font-size:13px;font-weight:600;color:var(--db-text);flex:1}
.am-row-notes{font-size:12px;color:var(--db-text-muted);margin-top:2px}
.am-row-remove{font-size:12px;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit;flex-shrink:0}
.am-row-remove:hover{color:#ef4444}
.am-empty{padding:24px 0;text-align:center;font-size:13px;color:var(--db-text-muted)}

.am-cta{margin-top:28px;padding:16px;border-radius:14px;background:var(--db-chip);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.am-cta-text{font-size:12.5px;color:var(--db-text-muted)}
.am-cta-btn{font-size:12.5px;font-weight:700;color:var(--db-text);text-decoration:none;white-space:nowrap}

.am-client{border-bottom:1px solid var(--db-border)}
.am-client:last-child{border-bottom:none}
.am-client summary{list-style:none;cursor:pointer;display:flex;align-items:center;gap:12px;padding:12px 0}
.am-client summary::-webkit-details-marker{display:none}
.am-chevron{width:11px;height:11px;stroke:var(--db-text-muted);stroke-width:2.4;fill:none;flex-shrink:0;transition:transform .15s}
.am-client[open] .am-chevron{transform:rotate(180deg)}
.am-client-body{padding:0 0 18px 20px}
.am-sub-title{font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:8px}
.am-sub-title:not(:first-child){margin-top:16px}
.am-sub-row{display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--db-border)}
.am-sub-row:last-of-type{border-bottom:none}
.am-sub-name{font-size:12.5px;font-weight:600;color:var(--db-text)}
.am-sub-meta{font-size:11.5px;color:var(--db-text-muted);margin-top:1px}
.am-inline-form{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
.am-input-sm{flex:1;min-width:110px;border-radius:8px;padding:8px 10px;font-size:12.5px;background:var(--db-bg);border:1px solid var(--db-border);color:var(--db-text);font-family:inherit}
.am-input-sm:focus{border-color:var(--db-invert-bg)}
.am-btn-sm{padding:8px 14px;border-radius:8px;border:none;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text);flex-shrink:0}
.am-btn-sm:hover{opacity:.9}
.am-empty-sm{font-size:12px;color:var(--db-text-muted);padding:2px 0 6px}
.am-client-remove{margin-top:16px;font-size:11.5px;color:var(--db-text-muted);background:none;border:none;cursor:pointer;font-family:inherit}
.am-client-remove:hover{color:#ef4444}
</style>
<script>
(function () {
  var saved = localStorage.getItem('unit-theme-v2') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();
</script>
</head>
<body>

<div class="am-topbar">
  <a href="{{ url('/') }}" class="am-logo">UNIT</a>
  <div class="am-topbar-right">
    <span class="am-topbar-name">{{ auth()->user()->name }}</span>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="am-logout">Logout</button></form>
  </div>
</div>

<div class="am-wrap">
  <div class="am-badge">Before you deploy</div>
  <div class="am-h1">AVA Memory</div>
  <div class="am-sub">Add the clients AVA will manage renewals for. Nothing here requires connecting Gmail — do that whenever you're ready to actually deploy.</div>

  <div class="am-coverage">
    <div class="am-coverage-top">
      <span class="am-coverage-label">Memory coverage</span>
      <span class="am-coverage-pct">{{ $coverage['score'] }}%</span>
    </div>
    <div class="am-coverage-track"><div class="am-coverage-fill" style="width:{{ $coverage['score'] }}%"></div></div>
    <div class="am-coverage-hint">
      @if ($coverage['healthy'])
        AVA has everything it needs from {{ $coverage['complete'] }} client{{ $coverage['complete'] === 1 ? '' : 's' }}.
      @else
        {{ $coverage['needed'] }} more client{{ $coverage['needed'] === 1 ? '' : 's' }} need a name, a contact with an email, and an asset with a renewal date to count as fully covered.
      @endif
    </div>
  </div>

  @if (session('success'))
    <div class="am-status success">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="am-status error">{{ session('error') }}</div>
  @endif

  <div class="am-card">
    <div class="am-card-title">Add a client</div>
    <form method="POST" action="{{ route('hire.ava.memory.store') }}">
      @csrf
      <div class="am-field">
        <label class="am-label" for="name">Client / company name</label>
        <input class="am-input" id="name" name="name" value="{{ old('name') }}" required placeholder="Acme Corp">
        @error('name')<div class="am-status error">{{ $message }}</div>@enderror
      </div>
      <div class="am-field">
        <label class="am-label" for="notes">Notes (optional)</label>
        <textarea class="am-textarea" id="notes" name="notes" placeholder="Anything AVA should know about this client">{{ old('notes') }}</textarea>
      </div>
      <button type="submit" class="am-btn">Add client</button>
    </form>
  </div>

  <div class="am-card">
    <div class="am-card-title">Clients added so far ({{ $clients->count() }})</div>
    @forelse ($clients as $client)
      @php
        $clientContacts = $contacts[$client->id] ?? collect();
        $clientAssets   = $assets[$client->id] ?? collect();
      @endphp
      <details class="am-client">
        <summary>
          <span class="am-row-dot"></span>
          <div style="flex:1">
            <div class="am-row-name">{{ $client->name }}</div>
            <div class="am-row-notes">
              {{ $clientContacts->count() }} contact{{ $clientContacts->count() === 1 ? '' : 's' }} &middot; {{ $clientAssets->count() }} asset{{ $clientAssets->count() === 1 ? '' : 's' }}
              @if ($client->notes) &middot; {{ $client->notes }} @endif
            </div>
          </div>
          <svg class="am-chevron" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </summary>

        <div class="am-client-body">
          <div class="am-sub-title">Contacts</div>
          @forelse ($clientContacts as $contact)
            <div class="am-sub-row">
              <div style="flex:1">
                <div class="am-sub-name">{{ $contact->name }}</div>
                <div class="am-sub-meta">{{ $contact->email }}{{ $contact->phone ? ' · '.$contact->phone : '' }}</div>
              </div>
              <form method="POST" action="{{ route('hire.ava.memory.contacts.destroy', $contact->id) }}">
                @csrf @method('DELETE')
                <button type="submit" class="am-row-remove">Remove</button>
              </form>
            </div>
          @empty
            <div class="am-empty-sm">No contacts yet.</div>
          @endforelse
          <form method="POST" action="{{ route('hire.ava.memory.contacts.store', $client->id) }}" class="am-inline-form">
            @csrf
            <input type="text" name="name" placeholder="Contact name" required class="am-input-sm">
            <input type="email" name="email" placeholder="Email" required class="am-input-sm">
            <input type="text" name="phone" placeholder="Phone (optional)" class="am-input-sm">
            <button type="submit" class="am-btn-sm">Add</button>
          </form>

          <div class="am-sub-title">Assets</div>
          @forelse ($clientAssets as $asset)
            <div class="am-sub-row">
              <div style="flex:1">
                <div class="am-sub-name">{{ $asset->name }}</div>
                <div class="am-sub-meta">{{ $asset->type }}{{ $asset->renewal_date ? ' · renews '.\Illuminate\Support\Carbon::parse($asset->renewal_date)->format('M j, Y') : '' }}</div>
              </div>
              <form method="POST" action="{{ route('hire.ava.memory.assets.destroy', $asset->id) }}">
                @csrf @method('DELETE')
                <button type="submit" class="am-row-remove">Remove</button>
              </form>
            </div>
          @empty
            <div class="am-empty-sm">No assets yet.</div>
          @endforelse
          <form method="POST" action="{{ route('hire.ava.memory.assets.store', $client->id) }}" class="am-inline-form">
            @csrf
            <input type="text" name="name" placeholder="e.g. acmecorp.com" required class="am-input-sm">
            <select name="type" class="am-input-sm">
              <option>Domain</option>
              <option>SSL</option>
              <option>Hosting</option>
              <option>SaaS</option>
              <option>Other</option>
            </select>
            <input type="date" name="renewal_date" class="am-input-sm" style="flex:0 0 140px">
            <button type="submit" class="am-btn-sm">Add</button>
          </form>

          <form method="POST" action="{{ route('hire.ava.memory.destroy', $client->id) }}" onsubmit="return confirm('Remove {{ $client->name }} and everything under it?')">
            @csrf @method('DELETE')
            <button type="submit" class="am-client-remove">Remove this client</button>
          </form>
        </div>
      </details>
    @empty
      <div class="am-empty">No clients yet — add your first one above.</div>
    @endforelse
  </div>

  <div class="am-cta">
    <span class="am-cta-text">Ready to put AVA to work?</span>
    <a href="{{ route('hire.ava.welcome') }}" class="am-cta-btn">Deploy AVA →</a>
  </div>
</div>

</body>
</html>

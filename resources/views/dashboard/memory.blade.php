<x-app-layout title="Memory">

@if(session('success'))
    <div class="mb-4 rounded-xl px-5 py-3 text-sm" style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 rounded-xl px-5 py-3 text-sm" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171">{{ session('error') }}</div>
@endif

{{-- ── Set persona prompt — unlocks tailored asset types + copy below ────────── --}}
@if(!$personaKey && $avaDeploymentId && !empty($personaOptions))
<div class="mb-6 rounded-xl px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3" style="background:var(--bg-card);border:1px solid var(--border)">
    <div class="flex-1">
        <p class="text-sm font-medium" style="color:var(--text-primary)">Tell us what AVA is renewing for you</p>
        <p class="text-xs mt-0.5" style="color:var(--text-muted)">Pick your use case to get the right asset types and terminology below — takes one click.</p>
    </div>
    <form method="POST" action="{{ route('workers.persona', $avaDeploymentId) }}" class="flex items-center gap-2 shrink-0">
        @csrf @method('PATCH')
        <select name="persona" required class="text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
            <option value="">Choose a use case…</option>
            @foreach($personaOptions as $key => $p)
            <option value="{{ $key }}">{{ $p['label'] }}</option>
            @endforeach
        </select>
        <button type="submit" class="text-sm rounded-lg px-4 py-2 font-semibold transition hover:opacity-90" class="ac-on">Save</button>
    </form>
</div>
@endif

{{-- ── Hub header ──────────────────────────────────────────────────────────── --}}
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold" style="color:var(--text-primary)">Memory</h1>
        <p class="text-xs mt-0.5 max-w-xl" style="color:var(--text-muted)">
            Your memory is your AI's training data. Every client, contact, asset, and group you build here powers every worker you deploy — now and in the future.
        </p>
    </div>
    {{-- Profile code ──────────────────────────────────────────────────── --}}
    <div class="text-right shrink-0">
        <p class="text-xs mb-0.5" style="color:var(--text-faint)">Your profile code</p>
        <div class="flex items-center gap-2">
            <span class="font-mono text-sm font-bold tracking-widest px-3 py-1.5 rounded-lg"
                  style="background:var(--bg-card);border:1px solid var(--border);color:var(--text-primary)">{{ $myProfileCode ?? '—' }}</span>
            <button onclick="navigator.clipboard.writeText('{{ $myProfileCode }}');this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500)"
                    class="text-xs transition hover:opacity-80" style="color:var(--text-muted)">Copy</button>
        </div>
    </div>
</div>

{{-- ── Stats strip ─────────────────────────────────────────────────────────── --}}
@php
    $urgentAssets = $assets->filter(fn($a) => $a->renewal_date && now()->diffInDays($a->renewal_date, false) <= 30 && now()->diffInDays($a->renewal_date, false) >= 0)->count();
    $expiredAssets = $assets->filter(fn($a) => $a->renewal_date && now()->diffInDays($a->renewal_date, false) < 0)->count();
@endphp
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    @foreach([
        ['label'=>'Clients',  'value'=>$clients->count(),  'sub'=>''],
        ['label'=>'Contacts', 'value'=>$contacts->count(), 'sub'=>''],
        ['label'=>'Assets',   'value'=>$assets->count(),   'sub'=> $urgentAssets ? $urgentAssets.' expiring soon' : ($expiredAssets ? $expiredAssets.' expired' : '')],
        ['label'=>'Groups',   'value'=>$myGroups->count(), 'sub'=>$myDeployments->count().' worker'.($myDeployments->count()!==1?'s':'')],
    ] as $stat)
    <div class="rounded-xl px-4 py-3" style="background:var(--bg-card);border:1px solid var(--border)">
        <p class="text-2xl font-bold" style="color:var(--text-primary)">{{ $stat['value'] }}</p>
        <p class="text-xs font-medium mt-0.5" style="color:var(--text-muted)">{{ $stat['label'] }}</p>
        @if($stat['sub'])
        <p class="text-xs mt-0.5" style="color:{{ str_contains($stat['sub'],'expir') ? '#fbbf24' : 'var(--text-faint)' }}">{{ $stat['sub'] }}</p>
        @endif
    </div>
    @endforeach
</div>

{{-- ── Tab bar ──────────────────────────────────────────────────────────────── --}}
<div class="overflow-x-auto mb-6" style="border-bottom:1px solid var(--border)">
    <div class="flex gap-1 min-w-max">
        <button onclick="showTab('mine')" id="tab-mine"
                class="hub-tab px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2"
                style="color:var(--text-primary);border-color:var(--accent)">My Memory</button>
        <button onclick="showTab('shared')" id="tab-shared"
                class="hub-tab px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 border-transparent transition hover:opacity-80"
                style="color:var(--text-muted)">
            Shared With Me
            @if($incoming->count())
            <span class="ml-1.5 text-xs rounded-full px-1.5 py-0.5" style="background:var(--bg-raised);color:var(--text-muted)">{{ $incoming->count() }}</span>
            @endif
        </button>
        <button onclick="showTab('access')" id="tab-access"
                class="hub-tab px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 border-transparent transition hover:opacity-80"
                style="color:var(--text-muted)">
            Access
            @if($outgoing->count())
            <span class="ml-1.5 text-xs rounded-full px-1.5 py-0.5" style="background:var(--bg-raised);color:var(--text-muted)">{{ $outgoing->count() }}</span>
            @endif
        </button>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════════════
     TAB: MY MEMORY
     ════════════════════════════════════════════════════════════════════════════ --}}
<div id="pane-mine" class="hub-pane">

    {{-- Bulk import bar --}}
    <div class="rounded-xl px-5 py-4 mb-5" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium" style="color:var(--text-primary)">Bulk Import</p>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">Upload a CSV or Excel file to populate clients, contacts, or assets.</p>
            </div>
            <form method="POST" action="{{ route('memory.import.preview') }}" enctype="multipart/form-data"
                  class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2" id="import-form">
                @csrf
                <select name="type" id="import-type" onchange="updateTemplateLink(this.value)"
                        class="text-sm rounded-lg px-3 py-2 border focus:outline-none"
                        style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                    <option value="clients">Clients</option>
                    <option value="contacts">Contacts</option>
                    <option value="assets">Assets</option>
                </select>
                <label class="cursor-pointer">
                    <span class="block text-center text-sm rounded-lg px-3 py-2 border transition hover:opacity-80"
                          id="file-label" style="background:var(--bg-raised);color:var(--text-secondary);border-color:var(--border)">Choose file…</span>
                    <input type="file" name="file" accept=".csv,.xlsx,.xls" required class="hidden"
                           onchange="document.getElementById('file-label').textContent = this.files[0]?.name ?? 'Choose file…'">
                </label>
                <button type="submit" class="text-sm rounded-lg px-4 py-2 font-semibold transition hover:opacity-90"
                        class="ac-on">Preview Import</button>
            </form>
        </div>
        <div class="mt-2 flex items-center gap-1 text-xs" style="color:var(--text-faint)">
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download template:
            <a id="tpl-link" href="{{ route('memory.import.template', 'clients') }}"
               class="transition hover:opacity-80" class="ac-text">clients_import_template.csv</a>
        </div>
    </div>

    {{-- Sub-tab nav --}}
    <div class="overflow-x-auto mb-6" style="border-bottom:1px solid var(--border)">
        <div class="flex gap-1 min-w-max">
            <button onclick="showSubTab('clients')" id="subtab-clients"
                    class="sub-tab px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2"
                    style="color:var(--text-primary);border-color:var(--accent)">
                Clients <span class="ml-1 text-xs opacity-60">{{ $clients->count() }}</span>
            </button>
            <button onclick="showSubTab('contacts')" id="subtab-contacts"
                    class="sub-tab px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 border-transparent transition hover:opacity-80"
                    style="color:var(--text-muted)">
                Contacts <span class="ml-1 text-xs opacity-60">{{ $contacts->count() }}</span>
            </button>
            <button onclick="showSubTab('assets')" id="subtab-assets"
                    class="sub-tab px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 border-transparent transition hover:opacity-80"
                    style="color:var(--text-muted)">
                Assets <span class="ml-1 text-xs opacity-60">{{ $assets->count() }}</span>
            </button>
            <button onclick="showSubTab('groups')" id="subtab-groups"
                    class="sub-tab px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 border-transparent transition hover:opacity-80"
                    style="color:var(--text-muted)">
                Groups <span class="ml-1 text-xs opacity-60">{{ $myGroups->count() }}</span>
            </button>
            <button onclick="showSubTab('rules')" id="subtab-rules"
                    class="sub-tab px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 border-transparent transition hover:opacity-80"
                    style="color:var(--text-muted)">
                AVA Rules <span class="ml-1 text-xs opacity-60">{{ $rules->count() }}</span>
            </button>
        </div>
    </div>

    {{-- ── CLIENTS ─────────────────────────────────────────────────────────── --}}
    <div id="sub-clients" class="sub-pane">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
                <div class="divide-y" style="border-color:var(--border-subtle)">
                    @forelse($clients as $client)
                    <div class="px-5 py-4 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $client->name }}</p>
                                @if(!empty($client->status) && $client->status !== 'active')
                                <span class="text-xs px-1.5 py-0.5 rounded font-medium
                                    {{ $client->status === 'prospect' ? 'bg-blue-900/40 text-blue-400' : ($client->status === 'inactive' ? 'bg-gray-800 text-gray-500' : 'bg-red-900/40 text-red-400') }}">
                                    {{ ucfirst($client->status) }}
                                </span>
                                @endif
                            </div>
                            <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ $client->preferred_style }}{{ $client->industry ? ' · ' . $client->industry : '' }}</p>
                            @if(!empty($client->address))<p class="text-xs mt-0.5" style="color:var(--text-faint)">{{ $client->address }}</p>@endif
                            @if($client->notes)<p class="text-xs mt-1 line-clamp-2" style="color:var(--text-faint)">{{ $client->notes }}</p>@endif
                        </div>
                        <form method="POST" action="{{ route('memory.clients.destroy', $client->id) }}" class="shrink-0">
                            @csrf @method('DELETE')
                            <button class="text-xs transition hover:opacity-80" style="color:var(--text-faint)"
                                    onclick="return confirm('Remove {{ addslashes($client->name) }}?')">Remove</button>
                        </form>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center text-sm" style="color:var(--text-faint)">No clients yet.</div>
                    @endforelse
                </div>
            </div>
            <div class="rounded-xl h-fit" style="background:var(--bg-card);border:1px solid var(--border)">
                <div class="px-5 py-4" style="border-bottom:1px solid var(--border-subtle)">
                    <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Add {{ ucfirst($memoryCopy['client_noun']) }}</h3>
                </div>
                <form method="POST" action="{{ route('memory.clients.store') }}" class="px-5 py-4 space-y-3">
                    @csrf
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">{{ ucfirst($memoryCopy['client_noun']) }} name</label>
                    <input type="text" name="name" required placeholder="e.g. {{ $memoryCopy['example_client'] }}" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Industry</label>
                        <input type="text" name="industry" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                        <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Status</label>
                        <select name="status" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                            <option value="active">Active</option><option value="prospect">Prospect</option>
                            <option value="inactive">Inactive</option><option value="churned">Churned</option>
                        </select></div>
                    </div>
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Preferred Style</label>
                    <select name="preferred_style" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                        <option>Professional</option><option>Friendly</option><option>Formal</option><option>Concise</option>
                    </select></div>
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Address</label>
                    <input type="text" name="address" placeholder="Street, City, State…" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Notes</label>
                    <textarea name="notes" rows="2" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none resize-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></textarea></div>
                    <button type="submit" class="w-full text-sm rounded-lg py-2 font-semibold transition hover:opacity-90" class="ac-on">Add Client</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── CONTACTS ─────────────────────────────────────────────────────────── --}}
    <div id="sub-contacts" class="sub-pane hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
                <div class="divide-y" style="border-color:var(--border-subtle)">
                    @forelse($contacts as $contact)
                    @php $cn = $clients->firstWhere('id', $contact->client_id); @endphp
                    <div class="px-5 py-4 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $contact->name }}</p>
                                @if(!empty($contact->is_decision_maker))
                                <span class="text-xs px-1.5 py-0.5 rounded font-medium bg-yellow-900/40 text-yellow-400">Decision Maker</span>
                                @endif
                            </div>
                            <p class="text-xs mt-0.5 truncate" class="ac-text">{{ $contact->email }}</p>
                            <p class="text-xs" style="color:var(--text-muted)">{{ implode(' · ', array_filter([$contact->phone, $contact->role, $contact->department])) }}</p>
                            @if($cn)<p class="text-xs mt-0.5" style="color:var(--text-faint)">{{ $cn->name }}</p>@endif
                        </div>
                        <form method="POST" action="{{ route('memory.contacts.destroy', $contact->id) }}" class="shrink-0">
                            @csrf @method('DELETE')
                            <button class="text-xs transition hover:opacity-80" style="color:var(--text-faint)"
                                    onclick="return confirm('Remove {{ addslashes($contact->name) }}?')">Remove</button>
                        </form>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center text-sm" style="color:var(--text-faint)">No contacts yet.</div>
                    @endforelse
                </div>
            </div>
            <div class="rounded-xl h-fit" style="background:var(--bg-card);border:1px solid var(--border)">
                <div class="px-5 py-4" style="border-bottom:1px solid var(--border-subtle)">
                    <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Add Contact</h3>
                </div>
                <form method="POST" action="{{ route('memory.contacts.store') }}" class="px-5 py-4 space-y-3">
                    @csrf
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Full Name</label>
                    <input type="text" name="name" required class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Email</label>
                    <input type="email" name="email" required class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Phone</label>
                        <input type="text" name="phone" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                        <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Role</label>
                        <input type="text" name="role" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                    </div>
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Department</label>
                    <input type="text" name="department" placeholder="e.g. Procurement, IT, Finance" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Client</label>
                    <select name="client_id" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                        <option value="">— none —</option>
                        @foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select></div>
                    <label class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg border cursor-pointer" style="border-color:var(--border);background:var(--bg-raised)">
                        <div><p class="text-xs font-semibold" style="color:var(--text-primary)">Decision Maker</p>
                        <p class="text-xs mt-0.5" style="color:var(--text-muted)">Key decision authority for this client</p></div>
                        <div class="relative shrink-0">
                            <input type="checkbox" name="is_decision_maker" value="1" class="sr-only peer">
                            <div class="w-9 h-5 rounded-full transition peer-checked:bg-yellow-400 bg-gray-700 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-4"></div>
                        </div>
                    </label>
                    <button type="submit" class="w-full text-sm rounded-lg py-2 font-semibold transition hover:opacity-90" class="ac-on">Add Contact</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── ASSETS ───────────────────────────────────────────────────────────── --}}
    <div id="sub-assets" class="sub-pane hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
                <div class="divide-y" style="border-color:var(--border-subtle)">
                    @forelse($assets as $asset)
                    @php
                        $days     = $asset->renewal_date ? now()->diffInDays($asset->renewal_date, false) : null;
                        $urgColor = $days === null ? 'var(--text-faint)' : ($days <= 0 ? '#f87171' : ($days <= 15 ? '#fbbf24' : ($days <= 30 ? '#facc15' : 'var(--text-muted)')));
                        $cn       = $clients->firstWhere('id', $asset->client_id);
                    @endphp
                    <div class="px-5 py-4 flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $asset->name }}</p>
                                @if(!empty($asset->status) && $asset->status !== 'active')
                                <span class="text-xs px-1.5 py-0.5 rounded font-medium
                                    {{ $asset->status === 'expiring' ? 'bg-amber-900/40 text-amber-400' : ($asset->status === 'expired' ? 'bg-red-900/40 text-red-400' : 'bg-gray-800 text-gray-500') }}">
                                    {{ ucfirst($asset->status) }}
                                </span>
                                @endif
                            </div>
                            <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ $asset->type }}{{ $asset->vendor ? ' · ' . $asset->vendor : '' }}</p>
                            @if($cn)<p class="text-xs mt-0.5" style="color:var(--text-faint)">{{ $cn->name }}</p>@endif
                            @if($asset->renewal_date)
                            <p class="text-xs mt-1 font-medium" style="color:{{ $urgColor }}">
                                {{ $asset->renewal_date }}
                                <span class="font-normal" style="color:var(--text-faint)"> — {{ $days <= 0 ? 'expired' : $days . ' days' }}</span>
                            </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <button onclick="toggleAssetEdit({{ $asset->id }})" class="text-xs font-medium transition hover:opacity-80" class="ac-text">Edit</button>
                            <form method="POST" action="{{ route('memory.assets.destroy', $asset->id) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs transition hover:opacity-80" style="color:var(--text-faint)"
                                        onclick="return confirm('Remove {{ addslashes($asset->name) }}?')">Remove</button>
                            </form>
                        </div>
                    </div>
                    <div id="asset-edit-{{ $asset->id }}" class="hidden px-5 pb-4" style="background:var(--bg-raised);border-top:1px solid var(--border-subtle)">
                        <form method="POST" action="{{ route('memory.assets.update', $asset->id) }}" class="pt-4 space-y-3">
                            @csrf @method('PATCH')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="sm:col-span-2"><label class="text-xs block mb-1" style="color:var(--text-muted)">Asset Name</label>
                                <input type="text" name="name" value="{{ $asset->name }}" required class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)"></div>
                                <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Type</label>
                                <select name="type" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                                    @foreach($assetTypes as $t)
                                    <option @if($asset->type === $t) selected @endif>{{ $t }}</option>
                                    @endforeach
                                </select></div>
                                <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Vendor</label>
                                <input type="text" name="vendor" value="{{ $asset->vendor }}" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)"></div>
                                <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Renewal Date</label>
                                <input type="date" name="renewal_date" value="{{ $asset->renewal_date }}" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)"></div>
                                <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Status</label>
                                <select name="status" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                                    @foreach(['active','expiring','expired','cancelled'] as $s)
                                    <option @if(($asset->status ?? 'active') === $s) selected @endif>{{ $s }}</option>
                                    @endforeach
                                </select></div>
                                <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Cost / Year ($)</label>
                                <input type="number" name="cost_per_year" step="0.01" value="{{ $asset->cost_per_year }}" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)"></div>
                                <div class="sm:col-span-2"><label class="text-xs block mb-1" style="color:var(--text-muted)">Client</label>
                                <select name="client_id" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                                    <option value="">— none —</option>
                                    @foreach($clients as $cl)<option value="{{ $cl->id }}" @if($asset->client_id == $cl->id) selected @endif>{{ $cl->name }}</option>@endforeach
                                </select></div>
                            </div>
                            <div class="flex gap-2 pt-1">
                                <button type="submit" class="text-sm px-4 py-2 rounded-lg font-semibold transition hover:opacity-90" class="ac-on">Save</button>
                                <button type="button" onclick="toggleAssetEdit({{ $asset->id }})" class="text-sm px-4 py-2 rounded-lg transition hover:opacity-80" style="background:var(--bg-card);color:var(--text-muted);border:1px solid var(--border)">Cancel</button>
                            </div>
                        </form>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center text-sm" style="color:var(--text-faint)">No assets yet.</div>
                    @endforelse
                </div>
            </div>
            <div class="rounded-xl h-fit" style="background:var(--bg-card);border:1px solid var(--border)">
                <div class="px-5 py-4" style="border-bottom:1px solid var(--border-subtle)">
                    <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Add Asset</h3>
                </div>
                <form method="POST" action="{{ route('memory.assets.store') }}" class="px-5 py-4 space-y-3">
                    @csrf
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">{{ ucfirst($memoryCopy['asset_noun']) }} name</label>
                    <input type="text" name="name" required placeholder="e.g. {{ $memoryCopy['example_asset'] }}" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Type</label>
                        <select name="type" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                            @foreach($assetTypes as $t)<option>{{ $t }}</option>@endforeach
                        </select></div>
                        <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Vendor</label>
                        <input type="text" name="vendor" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Renewal Date</label>
                        <input type="date" name="renewal_date" required class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                        <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Status</label>
                        <select name="status" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                            <option value="active">Active</option><option value="expiring">Expiring</option><option value="expired">Expired</option><option value="cancelled">Cancelled</option>
                        </select></div>
                    </div>
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Cost / Year ($)</label>
                    <input type="number" name="cost_per_year" step="0.01" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Client</label>
                    <select name="client_id" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                        <option value="">— none —</option>
                        @foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select></div>
                    <button type="submit" class="w-full text-sm rounded-lg py-2 font-semibold transition hover:opacity-90" class="ac-on">Add Asset</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── GROUPS ───────────────────────────────────────────────────────────── --}}
    <div id="sub-groups" class="sub-pane hidden">
        @if($myDeployments->isEmpty())
        <div class="rounded-xl px-5 py-12 text-center" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-sm" style="color:var(--text-muted)">No workers deployed yet. Deploy a worker to start creating asset groups.</p>
        </div>
        @elseif($myGroups->isEmpty())
        <div class="rounded-xl px-5 py-12 text-center" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-sm font-medium mb-1" style="color:var(--text-primary)">No groups yet</p>
            <p class="text-xs mb-4" style="color:var(--text-muted)">Groups are created from within each worker's memory page. Go to a worker's Memory tab to create your first group.</p>
            @foreach($myDeployments as $dep)
            <a href="{{ route('workers.memory.groups', $dep->id) }}" class="inline-block mr-2 mb-2 text-xs px-4 py-2 rounded-lg border transition hover:opacity-80" style="border-color:var(--border);color:var(--text-muted)">
                {{ $dep->name }} →
            </a>
            @endforeach
        </div>
        @else
        @php $groupsByDep = $myGroups->groupBy('deployment_id'); @endphp
        @foreach($groupsByDep as $depId => $depGroups)
        @php $depName = $depGroups->first()->deployment_name; $workerSlug = $depGroups->first()->worker_slug; @endphp
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--text-faint)">{{ $depName }}</span>
                    <span class="text-xs px-2 py-0.5 rounded font-mono" style="background:var(--bg-raised);color:var(--text-faint)">{{ $workerSlug }}</span>
                </div>
                <a href="{{ route('workers.memory.groups', $depId) }}" class="text-xs transition hover:opacity-80" class="ac-text">Manage →</a>
            </div>
            <div class="space-y-3">
                @foreach($depGroups as $group)
                @php
                    $nearestExpiry = $group->items->whereNotNull('renewal_date')->sortBy('renewal_date')->first();
                    $gDays = $nearestExpiry ? (int) now()->diffInDays($nearestExpiry->renewal_date, false) : null;
                @endphp
                <div class="rounded-xl" style="background:var(--bg-card);border:1px solid var(--border)">
                    <div class="px-5 py-3.5 flex flex-wrap items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold" style="color:var(--text-primary)">{{ $group->name }}</p>
                                @if($group->type)
                                <span class="text-xs px-2 py-0.5 rounded-full" style="background:var(--bg-raised);color:var(--accent-text);border:1px solid var(--border)">{{ $group->type }}</span>
                                @endif
                                @if($gDays !== null)
                                <span class="text-xs {{ $gDays <= 0 ? 'text-red-400' : ($gDays <= 15 ? 'text-amber-400' : ($gDays <= 30 ? 'text-yellow-400' : 'text-gray-500')) }}">
                                    {{ $gDays <= 0 ? 'Expired' : 'Next ' . $gDays . 'd' }}
                                </span>
                                @endif
                            </div>
                            <p class="text-xs mt-0.5" style="color:var(--text-muted)">
                                {{ $group->items->count() }} asset{{ $group->items->count() !== 1 ? 's' : '' }}
                                @if($group->client_name) · {{ $group->client_name }} @endif
                            </p>
                        </div>
                        <a href="{{ route('workers.memory.groups', $depId) }}" class="text-xs px-3 py-1.5 rounded-lg border transition hover:opacity-80 shrink-0" style="border-color:var(--border);color:var(--text-muted)">Edit</a>
                    </div>
                    @if($group->items->isNotEmpty())
                    <div class="px-5 pb-3 flex flex-wrap gap-2">
                        @foreach($group->items as $item)
                        @php $iDays = $item->renewal_date ? (int) now()->diffInDays($item->renewal_date, false) : null; @endphp
                        <span class="text-xs px-2 py-1 rounded-lg flex items-center gap-1.5" style="background:var(--bg-raised);color:var(--text-muted)">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $iDays !== null && $iDays <= 0 ? 'bg-red-400' : ($iDays !== null && $iDays <= 30 ? 'bg-amber-400' : 'bg-gray-600') }}"></span>
                            {{ $item->name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        @endif
    </div>

    {{-- ── AVA RULES ────────────────────────────────────────────────────────── --}}
    <div id="sub-rules" class="sub-pane hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
                <div class="divide-y" style="border-color:var(--border-subtle)">
                    @forelse($rules as $rule)
                    <div class="px-5 py-4 flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="text-xs font-mono font-bold" class="ac-text">{{ $rule->rule_id }}</span>
                                @php $pc = match($rule->priority) { 'Critical'=>'#f87171','High'=>'#fbbf24','Medium'=>'var(--text-muted)',default=>'var(--text-faint)' }; @endphp
                                <span class="text-xs font-medium" style="color:{{ $pc }}">{{ $rule->priority }}</span>
                                @if(!$rule->active)<span class="text-xs px-1.5 py-0.5 rounded" style="background:var(--bg-raised);color:var(--text-faint)">Inactive</span>@endif
                            </div>
                            <p class="text-xs leading-relaxed" style="color:var(--text-secondary)">{{ $rule->condition }}</p>
                            <p class="text-xs mt-1 leading-relaxed" style="color:var(--text-muted)">→ {{ $rule->action }}</p>
                        </div>
                        <form method="POST" action="{{ route('memory.rules.destroy', $rule->id) }}" class="shrink-0">
                            @csrf @method('DELETE')
                            <button class="text-xs transition hover:opacity-80" style="color:var(--text-faint)"
                                    onclick="return confirm('Remove rule {{ addslashes($rule->rule_id) }}?')">Remove</button>
                        </form>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center text-sm" style="color:var(--text-faint)">No rules yet.</div>
                    @endforelse
                </div>
            </div>
            <div class="rounded-xl h-fit" style="background:var(--bg-card);border:1px solid var(--border)">
                <div class="px-5 py-4" style="border-bottom:1px solid var(--border-subtle)">
                    <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Add Rule</h3>
                </div>
                <form method="POST" action="{{ route('memory.rules.store') }}" class="px-5 py-4 space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Rule ID</label>
                        <input type="text" name="rule_id" placeholder="AVA-007" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none font-mono" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></div>
                        <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Priority</label>
                        <select name="priority" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                            <option>Critical</option><option>High</option><option>Medium</option><option>Low</option>
                        </select></div>
                    </div>
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Condition (when…)</label>
                    <textarea name="condition" rows="3" required class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none resize-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></textarea></div>
                    <div><label class="text-xs block mb-1" style="color:var(--text-muted)">Action (then…)</label>
                    <textarea name="action" rows="3" required class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none resize-none" style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></textarea></div>
                    <button type="submit" class="w-full text-sm rounded-lg py-2 font-semibold transition hover:opacity-90" class="ac-on">Add Rule</button>
                </form>
            </div>
        </div>
    </div>

</div>{{-- /pane-mine --}}

{{-- ════════════════════════════════════════════════════════════════════════════
     TAB: SHARED WITH ME
     ════════════════════════════════════════════════════════════════════════════ --}}
<div id="pane-shared" class="hub-pane hidden">
    @forelse($incoming as $grant)
    @php $perms = json_decode($grant->permissions, true); @endphp
    <div class="mb-4 rounded-xl" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4 flex flex-wrap items-start gap-4" style="border-bottom:1px solid var(--border-subtle)">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold" style="color:var(--text-primary)">{{ $grant->owner_name }}'s Memory</p>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ $grant->deployment_name }} · {{ $grant->worker_slug }}</p>
                <div class="flex flex-wrap gap-1.5 mt-2">
                    @foreach($perms as $p)
                    <span class="text-xs px-2 py-0.5 rounded-md font-medium" style="background:var(--bg-raised);color:var(--text-muted)">{{ $p }}</span>
                    @endforeach
                </div>
            </div>
            {{-- Memory stats preview --}}
            <div class="flex gap-4 text-center shrink-0">
                @foreach([['Clients',$grant->client_count],['Contacts',$grant->contact_count],['Assets',$grant->asset_count],['Groups',$grant->group_count]] as [$label,$count])
                <div>
                    <p class="text-base font-bold" style="color:var(--text-primary)">{{ $count }}</p>
                    <p class="text-xs" style="color:var(--text-faint)">{{ $label }}</p>
                </div>
                @endforeach
            </div>
            <a href="{{ route('memory.shared', $grant->id) }}"
               class="text-sm px-4 py-2 rounded-lg font-semibold transition hover:opacity-90 shrink-0 self-center"
               class="ac-on">Open Memory →</a>
        </div>
        {{-- Grant meta --}}
        <div class="px-5 py-2.5 flex flex-wrap gap-x-5 gap-y-1">
            <span class="text-xs" style="color:var(--text-faint)">Accepted {{ \Carbon\Carbon::parse($grant->accepted_at)->diffForHumans() }}</span>
            <span class="text-xs" style="color:var(--text-faint)">{{ $grant->owner_email }}</span>
        </div>
    </div>
    @empty
    <div class="rounded-xl px-5 py-16 text-center" style="background:var(--bg-card);border:1px solid var(--border)">
        <p class="text-sm font-medium mb-1" style="color:var(--text-primary)">No shared memories yet</p>
        <p class="text-xs" style="color:var(--text-muted)">When a team member grants you access to their memory, it will appear here.</p>
    </div>
    @endforelse
</div>

{{-- ════════════════════════════════════════════════════════════════════════════
     TAB: ACCESS MANAGEMENT
     ════════════════════════════════════════════════════════════════════════════ --}}
<div id="pane-access" class="hub-pane hidden">

    {{-- Outgoing grants --}}
    <h2 class="text-sm font-bold mb-3" style="color:var(--text-primary)">Who has access to your memory</h2>
    @forelse($outgoing as $grant)
    @php $perms = json_decode($grant->permissions, true); @endphp
    <div class="mb-3 rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-semibold" style="color:var(--text-primary)">{{ $grant->grantee_name }}</p>
                    <span class="font-mono text-xs" style="color:var(--text-faint)">{{ $grant->grantee_code }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-bold
                        {{ $grant->status === 'accepted' ? 'bg-green-900/40 text-green-400' : 'bg-yellow-900/40 text-yellow-400' }}">
                        {{ ucfirst($grant->status) }}
                    </span>
                </div>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ $grant->deployment_name }} · {{ $grant->worker_slug }}</p>
                <div class="flex gap-1.5 flex-wrap mt-1.5">
                    @foreach($perms as $p)
                    <span class="text-xs px-2 py-0.5 rounded-md" style="background:var(--bg-raised);color:var(--text-muted)">{{ $p }}</span>
                    @endforeach
                </div>
            </div>
            <div class="text-right shrink-0">
                <p class="text-xs" style="color:var(--text-faint)">{{ $grant->event_count }} actions</p>
                @if($grant->last_action)
                <p class="text-xs" style="color:var(--text-faint)">Last: {{ \Carbon\Carbon::parse($grant->last_action)->diffForHumans() }}</p>
                @endif
                <form method="POST" action="{{ route('memory.access.revoke', $grant->id) }}" class="mt-2"
                      onsubmit="return confirm('Revoke access for {{ $grant->grantee_name }}?')">
                    @csrf
                    <button class="text-xs px-3 py-1.5 rounded-lg border transition hover:border-red-800 hover:text-red-400" style="border-color:var(--border);color:var(--text-faint)">Revoke</button>
                </form>
            </div>
        </div>
        {{-- Audit trail --}}
        @if($grant->event_count > 0)
        <div style="border-top:1px solid var(--border-subtle)">
            <button onclick="toggleAudit({{ $grant->id }})"
                    class="w-full px-5 py-2.5 text-left text-xs flex items-center gap-2 transition hover:opacity-80"
                    style="color:var(--text-faint)">
                <span id="audit-chevron-{{ $grant->id }}">▶</span> Activity trail
            </button>
            <div id="audit-{{ $grant->id }}" class="hidden" style="border-top:1px solid var(--border-subtle)">
                @php
                    $events = DB::table('memory_access_events as e')
                        ->join('users as u', 'u.id', '=', 'e.actor_user_id')
                        ->where('e.grant_id', $grant->id)
                        ->select('e.*', 'u.name as actor_name')
                        ->orderByDesc('e.created_at')->limit(20)->get();
                @endphp
                <div class="divide-y" style="border-color:var(--border-subtle)">
                    @foreach($events as $ev)
                    <div class="px-5 py-2.5 flex items-center gap-3">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0
                            {{ $ev->action==='modified'?'bg-yellow-400':($ev->action==='uploaded'?'bg-blue-400':($ev->action==='copied'?'bg-purple-400':'bg-gray-600')) }}"></span>
                        <div class="flex-1 min-w-0 text-xs">
                            <span class="font-medium" style="color:var(--text-primary)">{{ $ev->actor_name }}</span>
                            <span style="color:var(--text-muted)"> {{ $ev->action }} </span>
                            <span class="font-mono" style="color:var(--text-secondary)">{{ $ev->table_name }}#{{ $ev->record_id }}</span>
                        </div>
                        <span class="text-xs shrink-0" style="color:var(--text-faint)">{{ \Carbon\Carbon::parse($ev->created_at)->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
    @empty
    <div class="rounded-xl px-5 py-10 text-center mb-6" style="background:var(--bg-card);border:1px solid var(--border)">
        <p class="text-sm" style="color:var(--text-muted)">You haven't shared your memory with anyone yet.</p>
    </div>
    @endforelse

    {{-- Invite form --}}
    <div class="mt-6 rounded-xl" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4 rounded-t-xl" style="border-bottom:1px solid var(--border-subtle)">
            <p class="text-sm font-semibold" style="color:var(--text-primary)">Invite a team member</p>
            <p class="text-xs mt-0.5" style="color:var(--text-muted)">They must already have a UNIT account. Enter their profile code (UNIT-XXXXX) or email.</p>
        </div>
        <form method="POST" action="{{ route('memory.access.invite') }}" class="px-5 py-5 space-y-5">
            @csrf
            @if($errors->any())
            <div class="rounded-lg px-4 py-3 text-sm" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171">{{ $errors->first() }}</div>
            @endif
            <div>
                <label class="block text-xs mb-1.5 font-medium" style="color:var(--text-muted)">Profile code or email</label>
                <input type="text" name="lookup" value="{{ old('lookup') }}" placeholder="UNIT-AB3XY or name@company.com"
                       class="w-full text-sm rounded-lg px-3 py-2.5 border focus:outline-none font-mono"
                       style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
            </div>
            <div>
                <label class="block text-xs mb-1.5 font-medium" style="color:var(--text-muted)">Which deployment's memory</label>
                <select name="deployment_id" class="w-full text-sm rounded-lg px-3 py-2.5 border focus:outline-none"
                        style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                    <option value="">Select a deployment…</option>
                    @foreach($myDeployments as $dep)
                    <option value="{{ $dep->id }}" {{ old('deployment_id') == $dep->id ? 'selected' : '' }}>{{ $dep->name }} ({{ $dep->worker_slug }})</option>
                    @endforeach
                </select>
            </div>
            @php
                $permOptions = [
                    ['view',   'View',   'Read memory records — clients, contacts, assets'],
                    ['copy',   'Copy',   'Duplicate records into their own workspace'],
                    ['upload', 'Upload', 'Add new records to your memory'],
                    ['modify', 'Modify', 'Edit existing records in your memory'],
                ];
            @endphp
            <div>
                <label class="block text-xs mb-2 font-medium" style="color:var(--text-muted)">Permissions</label>
                <div class="space-y-2">
                    @foreach($permOptions as [$val, $label, $desc])
                    @php $checked = in_array($val, old('permissions', ['view'])); @endphp
                    <label class="flex items-center justify-between gap-4 p-3 rounded-lg border cursor-pointer transition hover:border-gray-600"
                           style="border-color:var(--border)">
                        <div>
                            <p class="text-xs font-semibold" style="color:var(--text-primary)">{{ $label }}</p>
                            <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ $desc }}</p>
                        </div>
                        <div class="relative shrink-0">
                            <input type="checkbox" name="permissions[]" value="{{ $val }}" {{ $checked ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-9 h-5 rounded-full transition peer-checked:bg-yellow-400 bg-gray-700 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-4"></div>
                        </div>
                    </label>
                    @endforeach
                    <div class="flex items-center justify-between gap-4 p-3 rounded-lg border opacity-40" style="border-color:var(--border)">
                        <div><p class="text-xs font-semibold" style="color:var(--text-primary)">Delete</p>
                        <p class="text-xs mt-0.5" style="color:var(--text-muted)">Never available to collaborators</p></div>
                        <div class="w-9 h-5 rounded-full relative shrink-0" style="background:var(--bg-raised)">
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-gray-600"></div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="w-full sm:w-auto text-sm px-6 py-2.5 rounded-lg font-semibold transition hover:opacity-90" class="ac-on">Send Invitation</button>
        </form>
    </div>
</div>

<x-self-learn
    page-key="memory"
    title="Your Memory Hub"
    body="Memory is the foundation of everything on UNIT. Every client, contact, asset, group, and rule you build here trains your AI workers. The more you add, the smarter every worker becomes. Share your memory with team members to collaborate — they can view, copy, or contribute, but they can never delete your original records." />

<script>
const templateRouteBase = "{{ url('/memory/import/template') }}";
function updateTemplateLink(type) {
    const link = document.getElementById('tpl-link');
    link.href = templateRouteBase + '/' + type;
    link.textContent = type + '_import_template.csv';
}

function showTab(name) {
    document.querySelectorAll('.hub-pane').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.hub-tab').forEach(b => {
        b.style.color       = 'var(--text-muted)';
        b.style.borderColor = 'transparent';
    });
    document.getElementById('pane-' + name).classList.remove('hidden');
    const btn = document.getElementById('tab-' + name);
    btn.style.color       = 'var(--text-primary)';
    btn.style.borderColor = 'var(--accent)';
}

function showSubTab(name) {
    document.querySelectorAll('.sub-pane').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.sub-tab').forEach(b => {
        b.style.color       = 'var(--text-muted)';
        b.style.borderColor = 'transparent';
    });
    document.getElementById('sub-' + name).classList.remove('hidden');
    const btn = document.getElementById('subtab-' + name);
    btn.style.color       = 'var(--text-primary)';
    btn.style.borderColor = 'var(--accent)';
}

function toggleAssetEdit(id) {
    document.getElementById('asset-edit-' + id).classList.toggle('hidden');
}

function toggleAudit(id) {
    const panel   = document.getElementById('audit-' + id);
    const chevron = document.getElementById('audit-chevron-' + id);
    const hidden  = panel.classList.toggle('hidden');
    chevron.textContent = hidden ? '▶' : '▼';
}

// Handle #hash navigation (e.g. redirect from /memory/access)
const hash = window.location.hash.replace('#', '');
if (['mine','shared','access'].includes(hash)) {
    showTab(hash);
} else if (['clients','contacts','assets','groups','rules'].includes(hash)) {
    showSubTab(hash);
}

// If errors on invite form, open access tab
@if($errors->any())
    document.addEventListener('DOMContentLoaded', () => showTab('access'));
@endif
</script>

</x-app-layout>

<x-app-layout title="Memory">

@if(session('success'))
    <div class="mb-4 rounded-xl px-5 py-3 text-sm" style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#4ade80">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 rounded-xl px-5 py-3 text-sm" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171">{{ session('error') }}</div>
@endif

{{-- Bulk import bar --}}
<div class="rounded-xl px-5 py-4 mb-5" style="background:var(--bg-card);border:1px solid var(--border)">
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium" style="color:var(--text-primary)">Bulk Import</p>
            <p class="text-xs mt-0.5" style="color:var(--text-muted)">Upload a CSV or Excel file to populate clients, contacts, or assets. Column mapping is automatic.</p>
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
                      id="file-label"
                      style="background:var(--bg-raised);color:var(--text-secondary);border-color:var(--border)">Choose file…</span>
                <input type="file" name="file" accept=".csv,.xlsx,.xls" required class="hidden"
                       onchange="document.getElementById('file-label').textContent = this.files[0]?.name ?? 'Choose file…'">
            </label>
            <button type="submit"
                    class="text-sm rounded-lg px-4 py-2 font-semibold transition hover:opacity-90"
                    style="background:var(--accent);color:#000">Preview Import</button>
        </form>
    </div>
    <div class="mt-2 flex items-center gap-1 text-xs" style="color:var(--text-faint)">
        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Download template:
        <a id="tpl-link" href="{{ route('memory.import.template', 'clients') }}"
           class="transition hover:opacity-80" style="color:var(--accent-text)">clients_import_template.csv</a>
    </div>
</div>

{{-- Tab nav (horizontally scrollable on mobile) --}}
<div class="overflow-x-auto mb-6" style="border-bottom:1px solid var(--border)">
    <div class="flex gap-1 min-w-max">
        <button onclick="showTab('clients')" id="tab-clients"
                class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2"
                style="color:var(--text-primary);border-color:var(--accent)">Clients</button>
        <button onclick="showTab('contacts')" id="tab-contacts"
                class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 border-transparent transition hover:opacity-80"
                style="color:var(--text-muted)">Contacts</button>
        <button onclick="showTab('assets')" id="tab-assets"
                class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 border-transparent transition hover:opacity-80"
                style="color:var(--text-muted)">Assets</button>
        <button onclick="showTab('rules')" id="tab-rules"
                class="tab-btn px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 border-transparent transition hover:opacity-80"
                style="color:var(--text-muted)">AVA Rules</button>
    </div>
</div>

{{-- ── CLIENTS ── --}}
<div id="pane-clients" class="tab-pane">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- List --}}
        <div class="lg:col-span-2 rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
            <div class="divide-y" style="border-color:var(--border-subtle)">
                @forelse($clients as $client)
                    <div class="px-5 py-4 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $client->name }}</p>
                            <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ $client->preferred_style }}{{ $client->industry ? ' · ' . $client->industry : '' }}</p>
                            @if($client->notes)
                                <p class="text-xs mt-1 line-clamp-2" style="color:var(--text-faint)">{{ $client->notes }}</p>
                            @endif
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

        {{-- Add form --}}
        <div class="rounded-xl h-fit" style="background:var(--bg-card);border:1px solid var(--border)">
            <div class="px-5 py-4" style="border-bottom:1px solid var(--border-subtle)">
                <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Add Client</h3>
            </div>
            <form method="POST" action="{{ route('memory.clients.store') }}" class="px-5 py-4 space-y-3">
                @csrf
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Client Name</label>
                    <input type="text" name="name" required class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                           style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                </div>
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Industry</label>
                    <input type="text" name="industry" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                           style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                </div>
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Preferred Style</label>
                    <select name="preferred_style" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                            style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                        <option>Professional</option>
                        <option>Friendly</option>
                        <option>Formal</option>
                        <option>Concise</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Notes</label>
                    <textarea name="notes" rows="3" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none resize-none"
                              style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></textarea>
                </div>
                <button type="submit" class="w-full text-sm rounded-lg py-2 font-semibold transition hover:opacity-90"
                        style="background:var(--accent);color:#000">Add Client</button>
            </form>
        </div>

    </div>
</div>

{{-- ── CONTACTS ── --}}
<div id="pane-contacts" class="tab-pane hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
            <div class="divide-y" style="border-color:var(--border-subtle)">
                @forelse($contacts as $contact)
                    @php $cn = $clients->firstWhere('id', $contact->client_id); @endphp
                    <div class="px-5 py-4 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $contact->name }}</p>
                            <p class="text-xs mt-0.5 truncate" style="color:var(--accent-text)">{{ $contact->email }}</p>
                            <p class="text-xs" style="color:var(--text-muted)">
                                {{ implode(' · ', array_filter([$contact->phone, $contact->role])) }}
                            </p>
                            @if($cn)
                                <p class="text-xs mt-0.5" style="color:var(--text-faint)">{{ $cn->name }}</p>
                            @endif
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
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Full Name</label>
                    <input type="text" name="name" required class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                           style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                </div>
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Email</label>
                    <input type="email" name="email" required class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                           style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs block mb-1" style="color:var(--text-muted)">Phone</label>
                        <input type="text" name="phone" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                               style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                    </div>
                    <div>
                        <label class="text-xs block mb-1" style="color:var(--text-muted)">Role</label>
                        <input type="text" name="role" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                               style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                    </div>
                </div>
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Client</label>
                    <select name="client_id" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                            style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                        <option value="">— none —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full text-sm rounded-lg py-2 font-semibold transition hover:opacity-90"
                        style="background:var(--accent);color:#000">Add Contact</button>
            </form>
        </div>

    </div>
</div>

{{-- ── ASSETS ── --}}
<div id="pane-assets" class="tab-pane hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
            <div class="divide-y" style="border-color:var(--border-subtle)">
                @forelse($assets as $asset)
                    @php
                        $days = $asset->renewal_date ? now()->diffInDays($asset->renewal_date, false) : null;
                        $urgColor = $days === null ? 'var(--text-faint)' : ($days <= 0 ? '#f87171' : ($days <= 15 ? '#fbbf24' : ($days <= 30 ? '#facc15' : 'var(--text-muted)')));
                        $cn = $clients->firstWhere('id', $asset->client_id);
                    @endphp

                    {{-- Summary row --}}
                    <div class="px-5 py-4 flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $asset->name }}</p>
                            <p class="text-xs mt-0.5" style="color:var(--text-muted)">
                                {{ $asset->type }}{{ $asset->vendor ? ' · ' . $asset->vendor : '' }}
                            </p>
                            @if($cn)
                                <p class="text-xs mt-0.5" style="color:var(--text-faint)">{{ $cn->name }}</p>
                            @endif
                            @if($asset->renewal_date)
                                <p class="text-xs mt-1 font-medium" style="color:{{ $urgColor }}">
                                    {{ $asset->renewal_date }}
                                    <span class="font-normal" style="color:var(--text-faint)">
                                        — {{ $days <= 0 ? 'expired' : $days . ' days' }}
                                    </span>
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <button onclick="toggleAssetEdit({{ $asset->id }})"
                                    class="text-xs font-medium transition hover:opacity-80"
                                    style="color:var(--accent-text)">Edit</button>
                            <form method="POST" action="{{ route('memory.assets.destroy', $asset->id) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs transition hover:opacity-80" style="color:var(--text-faint)"
                                        onclick="return confirm('Remove {{ addslashes($asset->name) }}?')">Remove</button>
                            </form>
                        </div>
                    </div>

                    {{-- Inline edit form (hidden by default) --}}
                    <div id="asset-edit-{{ $asset->id }}" class="hidden px-5 pb-4" style="background:var(--bg-raised);border-top:1px solid var(--border-subtle)">
                        <form method="POST" action="{{ route('memory.assets.update', $asset->id) }}" class="pt-4 space-y-3">
                            @csrf @method('PATCH')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="sm:col-span-2">
                                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Asset Name</label>
                                    <input type="text" name="name" value="{{ $asset->name }}" required
                                           class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                                           style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                                </div>
                                <div>
                                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Type</label>
                                    <select name="type" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                                            style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                                        @foreach(['SSL Certificate','Domain','Hosting','SaaS Subscription','Other'] as $t)
                                            <option @if($asset->type === $t) selected @endif>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Vendor</label>
                                    <input type="text" name="vendor" value="{{ $asset->vendor }}"
                                           class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                                           style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                                </div>
                                <div>
                                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Renewal Date</label>
                                    <input type="date" name="renewal_date" value="{{ $asset->renewal_date }}"
                                           class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                                           style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                                </div>
                                <div>
                                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Cost / Year ($)</label>
                                    <input type="number" name="cost_per_year" step="0.01" value="{{ $asset->cost_per_year }}"
                                           class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                                           style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Client</label>
                                    <select name="client_id" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                                            style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                                        <option value="">— none —</option>
                                        @foreach($clients as $cl)
                                            <option value="{{ $cl->id }}" @if($asset->client_id == $cl->id) selected @endif>{{ $cl->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2 pt-1">
                                <button type="submit" class="text-sm px-4 py-2 rounded-lg font-semibold transition hover:opacity-90"
                                        style="background:var(--accent);color:#000">Save</button>
                                <button type="button" onclick="toggleAssetEdit({{ $asset->id }})"
                                        class="text-sm px-4 py-2 rounded-lg transition hover:opacity-80"
                                        style="background:var(--bg-card);color:var(--text-muted);border:1px solid var(--border)">Cancel</button>
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
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Asset Name</label>
                    <input type="text" name="name" placeholder="e.g. example.com SSL" required
                           class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                           style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs block mb-1" style="color:var(--text-muted)">Type</label>
                        <select name="type" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                                style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                            <option>SSL Certificate</option>
                            <option>Domain</option>
                            <option>Hosting</option>
                            <option>SaaS Subscription</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs block mb-1" style="color:var(--text-muted)">Vendor</label>
                        <input type="text" name="vendor" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                               style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs block mb-1" style="color:var(--text-muted)">Renewal Date</label>
                        <input type="date" name="renewal_date" required
                               class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                               style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                    </div>
                    <div>
                        <label class="text-xs block mb-1" style="color:var(--text-muted)">Cost / Year ($)</label>
                        <input type="number" name="cost_per_year" step="0.01"
                               class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                               style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                    </div>
                </div>
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Client</label>
                    <select name="client_id" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                            style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                        <option value="">— none —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full text-sm rounded-lg py-2 font-semibold transition hover:opacity-90"
                        style="background:var(--accent);color:#000">Add Asset</button>
            </form>
        </div>

    </div>
</div>

{{-- ── AVA RULES ── --}}
<div id="pane-rules" class="tab-pane hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 rounded-xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
            <div class="divide-y" style="border-color:var(--border-subtle)">
                @forelse($rules as $rule)
                    <div class="px-5 py-4 flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="text-xs font-mono font-bold" style="color:var(--accent-text)">{{ $rule->rule_id }}</span>
                                @php
                                    $pc = match($rule->priority) {
                                        'Critical' => '#f87171',
                                        'High'     => '#fbbf24',
                                        'Medium'   => 'var(--text-muted)',
                                        default    => 'var(--text-faint)',
                                    };
                                @endphp
                                <span class="text-xs font-medium" style="color:{{ $pc }}">{{ $rule->priority }}</span>
                                @if(!$rule->active)
                                    <span class="text-xs px-1.5 py-0.5 rounded" style="background:var(--bg-raised);color:var(--text-faint)">Inactive</span>
                                @endif
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
                    <div>
                        <label class="text-xs block mb-1" style="color:var(--text-muted)">Rule ID</label>
                        <input type="text" name="rule_id" placeholder="AVA-007" required
                               class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none font-mono"
                               style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                    </div>
                    <div>
                        <label class="text-xs block mb-1" style="color:var(--text-muted)">Priority</label>
                        <select name="priority" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                                style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                            <option>Critical</option>
                            <option>High</option>
                            <option>Medium</option>
                            <option>Low</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Condition (when…)</label>
                    <textarea name="condition" rows="3" required
                              class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none resize-none"
                              style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></textarea>
                </div>
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Action (then…)</label>
                    <textarea name="action" rows="3" required
                              class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none resize-none"
                              style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></textarea>
                </div>
                <button type="submit" class="w-full text-sm rounded-lg py-2 font-semibold transition hover:opacity-90"
                        style="background:var(--accent);color:#000">Add Rule</button>
            </form>
        </div>

    </div>
</div>

<script>
const templateRouteBase = "{{ url('/memory/import/template') }}";
function updateTemplateLink(type) {
    const link = document.getElementById('tpl-link');
    link.href = templateRouteBase + '/' + type;
    link.textContent = type + '_import_template.csv';
}

function showTab(name) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.style.color        = 'var(--text-muted)';
        b.style.borderColor  = 'transparent';
    });
    document.getElementById('pane-' + name).classList.remove('hidden');
    const btn = document.getElementById('tab-' + name);
    btn.style.color       = 'var(--text-primary)';
    btn.style.borderColor = 'var(--accent)';
}

const hash = window.location.hash.replace('#', '');
if (['clients','contacts','assets','rules'].includes(hash)) showTab(hash);

function toggleAssetEdit(id) {
    const el = document.getElementById('asset-edit-' + id);
    el.classList.toggle('hidden');
}
</script>

</x-app-layout>

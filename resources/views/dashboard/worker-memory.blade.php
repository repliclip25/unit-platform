<x-app-layout title="{{ $dep->name }} · Memory">

    @include('partials.worker-subnav')

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Discovered assets — enrichment review --}}
    @if($discoveredAssets->count())
    <div class="mb-6 border border-gray-700 rounded-xl overflow-hidden">
        {{-- Header trigger --}}
        <button onclick="toggleDiscovered()" id="discovered-toggle"
            class="w-full flex items-center justify-between px-5 py-3.5 bg-gray-900 hover:bg-gray-800 transition text-left">
            <div class="flex items-center gap-3">
                {{-- Brain / enrichment icon --}}
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:rgba(241,211,98,0.12)">
                    <svg class="w-4 h-4" style="color:var(--accent-text)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-white">AVA discovered {{ $discoveredAssets->count() }} potential asset{{ $discoveredAssets->count() > 1 ? 's' : '' }} from your inbox</p>
                    <p class="text-xs text-gray-500 mt-0.5">Review and confirm to enrich your platform memory</p>
                </div>
            </div>
            <svg id="discovered-chevron" class="w-4 h-4 text-gray-500 transition-transform duration-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        {{-- Expandable items --}}
        <div id="discovered-panel" class="hidden divide-y divide-gray-800 bg-gray-950">
            @foreach($discoveredAssets as $da)
            <div class="px-5 py-5">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <p class="text-white text-sm font-medium leading-snug">{{ $da->name }}</p>
                        <p class="text-gray-500 text-xs mt-0.5">Detected {{ \Carbon\Carbon::parse($da->created_at)->diffForHumans() }}</p>
                    </div>
                    <form method="POST" action="{{ route('workers.memory.assets.destroy', [$dep->id, $da->id]) }}" class="shrink-0">
                        @csrf @method('DELETE')
                        <button class="text-gray-600 hover:text-red-400 text-xs transition">Dismiss</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('workers.memory.assets.approve', [$dep->id, $da->id]) }}">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                        <div class="sm:col-span-2">
                            <label class="text-gray-500 text-xs block mb-1">Asset name</label>
                            <input type="text" name="name" value="{{ $da->name }}" required
                                class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-yellow-500 placeholder-gray-600">
                        </div>
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Type <span class="text-red-500">*</span></label>
                            <input type="text" name="type" value="{{ $da->type !== 'discovered' ? $da->type : '' }}" required
                                placeholder="Domain, SSL, SaaS…" list="da-type-list-{{ $da->id }}"
                                class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-yellow-500 placeholder-gray-600">
                            <datalist id="da-type-list-{{ $da->id }}">
                                <option>SSL Certificate</option><option>Domain</option><option>Hosting</option>
                                <option>Website Management</option><option>SaaS Subscription</option>
                                <option>Insurance Policy</option><option>License</option><option>Contract</option>
                            </datalist>
                        </div>
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Vendor</label>
                            <input type="text" name="vendor" value="{{ $da->vendor }}" placeholder="e.g. Namecheap"
                                class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-yellow-500 placeholder-gray-600">
                        </div>
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Renewal date</label>
                            <input type="date" name="renewal_date" value="{{ $da->renewal_date }}"
                                class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="text-gray-500 text-xs block mb-1">Client</label>
                            <select name="client_id" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2.5 border border-gray-700 focus:outline-none focus:border-yellow-500">
                                <option value="">— no client —</option>
                                @foreach($clients as $cl)<option value="{{ $cl->id }}" {{ $da->client_id == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="w-full text-sm font-semibold rounded-lg py-2.5 transition" style="background:var(--accent);color:#000;">
                        Confirm &amp; Add to Memory
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Bulk import bar --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 mb-5">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1">
                <p class="text-white text-sm font-medium">Bulk Import</p>
                <p class="text-gray-500 text-xs mt-0.5">Upload a CSV or Excel file to populate clients, contacts, or assets.</p>
            </div>
            <form method="POST" action="{{ route('workers.memory.import.preview', $dep->id) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                @csrf
                <select name="type" id="import-type" onchange="updateTemplateLink(this.value)" class="bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none">
                    <option value="clients">Clients</option>
                    <option value="contacts">Contacts</option>
                    <option value="assets">Assets</option>
                </select>
                <label class="cursor-pointer">
                    <span class="bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm rounded-lg px-3 py-2 border border-gray-700 inline-block" id="file-label">Choose file…</span>
                    <input type="file" name="file" accept=".csv,.xlsx,.xls" required class="hidden" onchange="document.getElementById('file-label').textContent = this.files[0]?.name ?? 'Choose file…'">
                </label>
                <button type="submit" class="text-sm rounded-lg px-4 py-2 transition font-medium" style="background:var(--accent);color:#000;">Preview Import</button>
            </form>
        </div>
        <div class="mt-2 flex items-center gap-1 text-xs text-gray-600">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download template:
            <a id="tpl-link" href="{{ route('memory.import.template', 'clients') }}" style="color:var(--accent-text)" class="hover:underline">clients_import_template.csv</a>
        </div>
    </div>

    {{-- Tab nav --}}
    <div class="flex gap-1 mb-6 border-b border-gray-800">
        <button onclick="showTab('clients')" id="tab-clients" class="tab-btn px-4 py-2 text-sm font-medium text-white border-b-2 border-yellow-400">Clients</button>
        <button onclick="showTab('contacts')" id="tab-contacts" class="tab-btn px-4 py-2 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-white">Contacts</button>
        <button onclick="showTab('assets')" id="tab-assets" class="tab-btn px-4 py-2 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-white">Assets</button>
    </div>

    {{-- CLIENTS --}}
    <div id="pane-clients" class="tab-pane grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="col-span-2 bg-gray-900 border border-gray-800 rounded-xl">
            <div class="divide-y divide-gray-800">
                @forelse($clients as $client)
                    <div class="px-5 py-4">
                        {{-- Display row --}}
                        <div class="flex items-start justify-between" id="client-row-{{ $client->id }}">
                            <div>
                                <p class="text-white text-sm font-medium">{{ $client->name }}</p>
                                <p class="text-gray-500 text-xs mt-0.5">{{ $client->preferred_style }}{{ $client->industry ? ' · ' . $client->industry : '' }}</p>
                                @if($client->notes)<p class="text-gray-600 text-xs mt-1">{{ $client->notes }}</p>@endif
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <button onclick="toggleEdit('client', {{ $client->id }})" class="text-gray-500 hover:text-white text-xs">Edit</button>
                                <form method="POST" action="{{ route('workers.memory.clients.destroy', [$dep->id, $client->id]) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-gray-600 hover:text-red-400 text-xs">Remove</button>
                                </form>
                            </div>
                        </div>
                        {{-- Inline edit form --}}
                        <form method="POST" action="{{ route('workers.memory.clients.update', [$dep->id, $client->id]) }}"
                            id="client-edit-{{ $client->id }}" class="hidden mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @csrf @method('PATCH')
                            <input type="text" name="name" value="{{ $client->name }}" required placeholder="Name"
                                class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                            <input type="text" name="industry" value="{{ $client->industry }}" placeholder="Industry"
                                class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                            <select name="preferred_style" class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                                @foreach(['Professional','Friendly','Formal','Concise'] as $s)
                                    <option {{ $client->preferred_style === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                            <textarea name="notes" rows="1" placeholder="Notes" class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">{{ $client->notes }}</textarea>
                            <div class="sm:col-span-2 flex gap-2">
                                <button type="submit" class="text-xs font-medium rounded-lg px-4 py-1.5 transition" style="background:var(--accent);color:#000;">Save</button>
                                <button type="button" onclick="toggleEdit('client', {{ $client->id }})" class="text-gray-400 hover:text-white text-xs px-2">Cancel</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-600 text-sm">No clients yet. Add one or import a CSV.</div>
                @endforelse
            </div>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl h-fit">
            <div class="px-5 py-4 border-b border-gray-800"><h3 class="text-white text-sm font-semibold">Add Client</h3></div>
            <form method="POST" action="{{ route('workers.memory.clients.store', $dep->id) }}" class="px-5 py-4 space-y-3">
                @csrf
                <div><label class="text-gray-400 text-xs block mb-1">Client Name</label><input type="text" name="name" required class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Industry</label><input type="text" name="industry" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Preferred Style</label>
                    <select name="preferred_style" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                        <option>Professional</option><option>Friendly</option><option>Formal</option><option>Concise</option>
                    </select>
                </div>
                <div><label class="text-gray-400 text-xs block mb-1">Notes</label><textarea name="notes" rows="2" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400"></textarea></div>
                <button type="submit" class="w-full text-sm rounded-lg py-2 transition font-medium" style="background:var(--accent);color:#000;">Add Client</button>
            </form>
        </div>
    </div>

    {{-- CONTACTS --}}
    <div id="pane-contacts" class="tab-pane hidden grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="col-span-2 bg-gray-900 border border-gray-800 rounded-xl">
            <div class="divide-y divide-gray-800">
                @forelse($contacts as $contact)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between" id="contact-row-{{ $contact->id }}">
                            <div>
                                <p class="text-white text-sm font-medium">{{ $contact->name }}</p>
                                <p class="text-xs mt-0.5" style="color:var(--accent-text)">{{ $contact->email }}</p>
                                <p class="text-gray-500 text-xs">{{ $contact->phone }}{{ $contact->role ? ' · ' . $contact->role : '' }}</p>
                                @php $cn = $clients->firstWhere('id', $contact->client_id); @endphp
                                @if($cn)<p class="text-gray-600 text-xs">{{ $cn->name }}</p>@endif
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <button onclick="toggleEdit('contact', {{ $contact->id }})" class="text-gray-500 hover:text-white text-xs">Edit</button>
                                <form method="POST" action="{{ route('workers.memory.contacts.destroy', [$dep->id, $contact->id]) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-gray-600 hover:text-red-400 text-xs">Remove</button>
                                </form>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('workers.memory.contacts.update', [$dep->id, $contact->id]) }}"
                            id="contact-edit-{{ $contact->id }}" class="hidden mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @csrf @method('PATCH')
                            <input type="text" name="name" value="{{ $contact->name }}" required placeholder="Name"
                                class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                            <input type="email" name="email" value="{{ $contact->email }}" required placeholder="Email"
                                class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                            <input type="text" name="phone" value="{{ $contact->phone }}" placeholder="Phone"
                                class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                            <input type="text" name="role" value="{{ $contact->role }}" placeholder="Role / Title"
                                class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                            <select name="client_id" class="sm:col-span-2 bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                                <option value="">— no client —</option>
                                @foreach($clients as $cl)<option value="{{ $cl->id }}" {{ $contact->client_id == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>@endforeach
                            </select>
                            <div class="sm:col-span-2 flex gap-2">
                                <button type="submit" class="text-xs font-medium rounded-lg px-4 py-1.5 transition" style="background:var(--accent);color:#000;">Save</button>
                                <button type="button" onclick="toggleEdit('contact', {{ $contact->id }})" class="text-gray-400 hover:text-white text-xs px-2">Cancel</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-600 text-sm">No contacts yet.</div>
                @endforelse
            </div>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl h-fit">
            <div class="px-5 py-4 border-b border-gray-800"><h3 class="text-white text-sm font-semibold">Add Contact</h3></div>
            <form method="POST" action="{{ route('workers.memory.contacts.store', $dep->id) }}" class="px-5 py-4 space-y-3">
                @csrf
                <div><label class="text-gray-400 text-xs block mb-1">Full Name</label><input type="text" name="name" required class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Email</label><input type="email" name="email" required class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Phone</label><input type="text" name="phone" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Role</label><input type="text" name="role" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Client</label>
                    <select name="client_id" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                        <option value="">— none —</option>
                        @foreach($clients as $cl)<option value="{{ $cl->id }}">{{ $cl->name }}</option>@endforeach
                    </select>
                </div>
                <button type="submit" class="w-full text-sm rounded-lg py-2 transition font-medium" style="background:var(--accent);color:#000;">Add Contact</button>
            </form>
        </div>
    </div>

    {{-- ASSETS --}}
    <div id="pane-assets" class="tab-pane hidden grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="col-span-2 bg-gray-900 border border-gray-800 rounded-xl">
            <div class="divide-y divide-gray-800">
                @forelse($assets as $asset)
                    @php
                        $days = $asset->renewal_date ? (int) now()->diffInDays($asset->renewal_date, false) : null;
                    @endphp
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between" id="asset-row-{{ $asset->id }}">
                            <div>
                                <p class="text-white text-sm font-medium">{{ $asset->name }}</p>
                                <p class="text-gray-500 text-xs mt-0.5">{{ $asset->type }}{{ $asset->vendor ? ' · ' . $asset->vendor : '' }}</p>
                                @php $cn = $clients->firstWhere('id', $asset->client_id); @endphp
                                @if($cn)<p class="text-gray-600 text-xs">{{ $cn->name }}</p>@endif
                            </div>
                            <div class="text-right flex flex-col items-end gap-2">
                                @if($days !== null)
                                <div>
                                    <p class="text-xs {{ $days <= 0 ? 'text-red-400' : ($days <= 15 ? 'text-amber-400' : ($days <= 30 ? 'text-yellow-400' : 'text-gray-400')) }}">{{ $asset->renewal_date }}</p>
                                    <p class="text-gray-600 text-xs">{{ $days <= 0 ? 'Expired' : $days . ' days' }}</p>
                                </div>
                                @endif
                                <div class="flex items-center gap-3">
                                    <button onclick="toggleEdit('asset', {{ $asset->id }})" class="text-gray-500 hover:text-white text-xs">Edit</button>
                                    <form method="POST" action="{{ route('workers.memory.assets.destroy', [$dep->id, $asset->id]) }}">
                                        @csrf @method('DELETE')
                                        <button class="text-gray-600 hover:text-red-400 text-xs">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('workers.memory.assets.update', [$dep->id, $asset->id]) }}"
                            id="asset-edit-{{ $asset->id }}" class="hidden mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @csrf @method('PATCH')
                            <input type="text" name="name" value="{{ $asset->name }}" required placeholder="Asset name"
                                class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                            <input type="text" name="type" value="{{ $asset->type }}" required placeholder="Type" list="asset-type-edit-{{ $asset->id }}"
                                class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                            <datalist id="asset-type-edit-{{ $asset->id }}">
                                <option>SSL Certificate</option><option>Domain</option><option>Hosting</option>
                                <option>Website Management</option><option>SaaS Subscription</option>
                                <option>Insurance Policy</option><option>License</option><option>Contract</option>
                            </datalist>
                            <input type="text" name="vendor" value="{{ $asset->vendor }}" placeholder="Vendor"
                                class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                            <input type="date" name="renewal_date" value="{{ $asset->renewal_date }}"
                                class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                            <input type="number" name="cost_per_year" value="{{ $asset->cost_per_year }}" step="0.01" placeholder="Cost / year ($)"
                                class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                            <select name="client_id" class="bg-gray-800 text-white text-xs rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                                <option value="">— no client —</option>
                                @foreach($clients as $cl)<option value="{{ $cl->id }}" {{ $asset->client_id == $cl->id ? 'selected' : '' }}>{{ $cl->name }}</option>@endforeach
                            </select>
                            <div class="sm:col-span-2 flex gap-2">
                                <button type="submit" class="text-xs font-medium rounded-lg px-4 py-1.5 transition" style="background:var(--accent);color:#000;">Save</button>
                                <button type="button" onclick="toggleEdit('asset', {{ $asset->id }})" class="text-gray-400 hover:text-white text-xs px-2">Cancel</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-600 text-sm">No assets yet. Add one or import a CSV.</div>
                @endforelse
            </div>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl h-fit">
            <div class="px-5 py-4 border-b border-gray-800"><h3 class="text-white text-sm font-semibold">Add Asset</h3></div>
            <form method="POST" action="{{ route('workers.memory.assets.store', $dep->id) }}" class="px-5 py-4 space-y-3">
                @csrf
                <div><label class="text-gray-400 text-xs block mb-1">Asset Name</label><input type="text" name="name" required class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Type</label>
                    <input type="text" name="type" required list="asset-type-suggestions"
                        placeholder="e.g. Website Management, Insurance Policy…"
                        class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                    <datalist id="asset-type-suggestions">
                        <option>SSL Certificate</option><option>Domain</option><option>Hosting</option>
                        <option>Website Management</option><option>SaaS Subscription</option>
                        <option>Insurance Policy</option><option>License</option><option>Permit</option>
                        <option>Contract</option><option>Certification</option>
                        <option>Equipment Lease</option><option>Maintenance Agreement</option><option>Other</option>
                    </datalist>
                </div>
                <div><label class="text-gray-400 text-xs block mb-1">Vendor</label><input type="text" name="vendor" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Renewal Date</label><input type="date" name="renewal_date" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Client</label>
                    <select name="client_id" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400">
                        <option value="">— none —</option>
                        @foreach($clients as $cl)<option value="{{ $cl->id }}">{{ $cl->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="text-gray-400 text-xs block mb-1">Cost / Year ($)</label><input type="number" name="cost_per_year" step="0.01" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-yellow-400"></div>
                <button type="submit" class="w-full text-sm rounded-lg py-2 transition font-medium" style="background:var(--accent);color:#000;">Add Asset</button>
            </form>
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
            b.classList.remove('text-white','border-yellow-400');
            b.classList.add('text-gray-400','border-transparent');
        });
        document.getElementById('pane-' + name).classList.remove('hidden');
        const btn = document.getElementById('tab-' + name);
        btn.classList.add('text-white','border-yellow-400');
        btn.classList.remove('text-gray-400','border-transparent');
    }
    function toggleDiscovered() {
        const panel   = document.getElementById('discovered-panel');
        const chevron = document.getElementById('discovered-chevron');
        const open    = panel.classList.toggle('hidden') === false;
        chevron.style.transform = open ? 'rotate(180deg)' : '';
    }
    function toggleEdit(type, id) {
        const form = document.getElementById(type + '-edit-' + id);
        form.classList.toggle('hidden');
    }
    const hash = window.location.hash.replace('#','');
    if (['clients','contacts','assets'].includes(hash)) showTab(hash);
    </script>

</x-app-layout>

<x-app-layout title="{{ $dep->name }} · Memory">

    @include('partials.worker-subnav')

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">{{ session('error') }}</div>
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
                <select name="type" id="import-type" onchange="updateTemplateLink(this.value)" class="bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                    <option value="clients">Clients</option>
                    <option value="contacts">Contacts</option>
                    <option value="assets">Assets</option>
                </select>
                <label class="cursor-pointer">
                    <span class="bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm rounded-lg px-3 py-2 border border-gray-700 inline-block" id="file-label">Choose file…</span>
                    <input type="file" name="file" accept=".csv,.xlsx,.xls" required class="hidden" onchange="document.getElementById('file-label').textContent = this.files[0]?.name ?? 'Choose file…'">
                </label>
                <button type="submit" class="bg-brand hover:bg-brand-deep text-brand-text text-sm rounded-lg px-4 py-2 transition">Preview Import</button>
            </form>
        </div>
        <div class="mt-2 flex items-center gap-1 text-xs text-gray-600">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download template:
            <a id="tpl-link" href="{{ route('memory.import.template', 'clients') }}" class="text-brand hover:text-brand hover:underline">clients_import_template.csv</a>
        </div>
    </div>

    {{-- Tab nav --}}
    <div class="flex gap-1 mb-6 border-b border-gray-800">
        <button onclick="showTab('clients')" id="tab-clients" class="tab-btn px-4 py-2 text-sm font-medium text-white border-b-2 border-brand">Clients</button>
        <button onclick="showTab('contacts')" id="tab-contacts" class="tab-btn px-4 py-2 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-white">Contacts</button>
        <button onclick="showTab('assets')" id="tab-assets" class="tab-btn px-4 py-2 text-sm font-medium text-gray-400 border-b-2 border-transparent hover:text-white">Assets</button>
    </div>

    {{-- CLIENTS --}}
    <div id="pane-clients" class="tab-pane grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="col-span-2 bg-gray-900 border border-gray-800 rounded-xl">
            <div class="divide-y divide-gray-800">
                @forelse($clients as $client)
                    <div class="px-5 py-4 flex items-start justify-between">
                        <div>
                            <p class="text-white text-sm font-medium">{{ $client->name }}</p>
                            <p class="text-gray-500 text-xs mt-0.5">{{ $client->preferred_style }}{{ $client->industry ? ' · ' . $client->industry : '' }}</p>
                            @if($client->notes)<p class="text-gray-600 text-xs mt-1">{{ $client->notes }}</p>@endif
                        </div>
                        <form method="POST" action="{{ route('workers.memory.clients.destroy', [$dep->id, $client->id]) }}">
                            @csrf @method('DELETE')
                            <button class="text-gray-600 hover:text-red-400 text-xs">Remove</button>
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
                <div><label class="text-gray-400 text-xs block mb-1">Client Name</label><input type="text" name="name" required class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Industry</label><input type="text" name="industry" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Preferred Style</label>
                    <select name="preferred_style" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                        <option>Professional</option><option>Friendly</option><option>Formal</option><option>Concise</option>
                    </select>
                </div>
                <div><label class="text-gray-400 text-xs block mb-1">Notes</label><textarea name="notes" rows="2" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></textarea></div>
                <button type="submit" class="w-full bg-brand hover:bg-brand-deep text-brand-text text-sm rounded-lg py-2 transition">Add Client</button>
            </form>
        </div>
    </div>

    {{-- CONTACTS --}}
    <div id="pane-contacts" class="tab-pane hidden grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="col-span-2 bg-gray-900 border border-gray-800 rounded-xl">
            <div class="divide-y divide-gray-800">
                @forelse($contacts as $contact)
                    <div class="px-5 py-4 flex items-start justify-between">
                        <div>
                            <p class="text-white text-sm font-medium">{{ $contact->name }}</p>
                            <p class="text-brand text-xs mt-0.5">{{ $contact->email }}</p>
                            <p class="text-gray-500 text-xs">{{ $contact->phone }}{{ $contact->role ? ' · ' . $contact->role : '' }}</p>
                            @php $cn = $clients->firstWhere('id', $contact->client_id); @endphp
                            @if($cn)<p class="text-gray-600 text-xs">{{ $cn->name }}</p>@endif
                        </div>
                        <form method="POST" action="{{ route('workers.memory.contacts.destroy', [$dep->id, $contact->id]) }}">
                            @csrf @method('DELETE')
                            <button class="text-gray-600 hover:text-red-400 text-xs">Remove</button>
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
                <div><label class="text-gray-400 text-xs block mb-1">Full Name</label><input type="text" name="name" required class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Email</label><input type="email" name="email" required class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Phone</label><input type="text" name="phone" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Role</label><input type="text" name="role" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Client</label>
                    <select name="client_id" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                        <option value="">— none —</option>
                        @foreach($clients as $cl)<option value="{{ $cl->id }}">{{ $cl->name }}</option>@endforeach
                    </select>
                </div>
                <button type="submit" class="w-full bg-brand hover:bg-brand-deep text-brand-text text-sm rounded-lg py-2 transition">Add Contact</button>
            </form>
        </div>
    </div>

    {{-- ASSETS --}}
    <div id="pane-assets" class="tab-pane hidden grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="col-span-2 bg-gray-900 border border-gray-800 rounded-xl">
            <div class="divide-y divide-gray-800">
                @forelse($assets as $asset)
                    @php $days = now()->diffInDays($asset->renewal_date, false); @endphp
                    <div class="px-5 py-4 flex items-start justify-between">
                        <div>
                            <p class="text-white text-sm font-medium">{{ $asset->name }}</p>
                            <p class="text-gray-500 text-xs mt-0.5">{{ $asset->type }}{{ $asset->vendor ? ' · ' . $asset->vendor : '' }}</p>
                            @php $cn = $clients->firstWhere('id', $asset->client_id); @endphp
                            @if($cn)<p class="text-gray-600 text-xs">{{ $cn->name }}</p>@endif
                        </div>
                        <div class="text-right flex flex-col items-end gap-2">
                            <div>
                                <p class="text-xs {{ $days <= 0 ? 'text-red-400' : ($days <= 15 ? 'text-amber-400' : ($days <= 30 ? 'text-yellow-400' : 'text-gray-400')) }}">{{ $asset->renewal_date }}</p>
                                <p class="text-gray-600 text-xs">{{ $days <= 0 ? 'Expired' : $days . ' days' }}</p>
                            </div>
                            <form method="POST" action="{{ route('workers.memory.assets.destroy', [$dep->id, $asset->id]) }}">
                                @csrf @method('DELETE')
                                <button class="text-gray-600 hover:text-red-400 text-xs">Remove</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-600 text-sm">No assets yet.</div>
                @endforelse
            </div>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-xl h-fit">
            <div class="px-5 py-4 border-b border-gray-800"><h3 class="text-white text-sm font-semibold">Add Asset</h3></div>
            <form method="POST" action="{{ route('workers.memory.assets.store', $dep->id) }}" class="px-5 py-4 space-y-3">
                @csrf
                <div><label class="text-gray-400 text-xs block mb-1">Asset Name</label><input type="text" name="name" required class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Type</label>
                    <input type="text" name="type" required list="asset-type-suggestions"
                        placeholder="e.g. Website Management, Insurance Policy…"
                        class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                    <datalist id="asset-type-suggestions">
                        <option>SSL Certificate</option>
                        <option>Domain</option>
                        <option>Hosting</option>
                        <option>Website Management</option>
                        <option>SaaS Subscription</option>
                        <option>Insurance Policy</option>
                        <option>License</option>
                        <option>Permit</option>
                        <option>Contract</option>
                        <option>Certification</option>
                        <option>Equipment Lease</option>
                        <option>Maintenance Agreement</option>
                        <option>Other</option>
                    </datalist>
                </div>
                <div><label class="text-gray-400 text-xs block mb-1">Vendor</label><input type="text" name="vendor" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Renewal Date</label><input type="date" name="renewal_date" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></div>
                <div><label class="text-gray-400 text-xs block mb-1">Client</label>
                    <select name="client_id" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                        <option value="">— none —</option>
                        @foreach($clients as $cl)<option value="{{ $cl->id }}">{{ $cl->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="text-gray-400 text-xs block mb-1">Cost / Year ($)</label><input type="number" name="cost_per_year" step="0.01" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"></div>
                <button type="submit" class="w-full bg-brand hover:bg-brand-deep text-brand-text text-sm rounded-lg py-2 transition">Add Asset</button>
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
            b.classList.remove('text-white','border-brand');
            b.classList.add('text-gray-400','border-transparent');
        });
        document.getElementById('pane-' + name).classList.remove('hidden');
        const btn = document.getElementById('tab-' + name);
        btn.classList.add('text-white','border-brand');
        btn.classList.remove('text-gray-400','border-transparent');
    }
    const hash = window.location.hash.replace('#','');
    if (['clients','contacts','assets'].includes(hash)) showTab(hash);
    </script>

</x-app-layout>

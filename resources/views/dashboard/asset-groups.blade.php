<x-app-layout title="{{ $dep->name }} · Asset Groups">

    @include('partials.worker-subnav')

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('workers.memory', $dep->worker_slug) }}"
                   class="text-xs transition hover:opacity-80" style="color:var(--text-faint)">← Memory</a>
            </div>
            <h1 class="text-lg font-bold" style="color:var(--text-primary)">Asset Groups</h1>
            <p class="text-xs mt-0.5" style="color:var(--text-muted)">
                Bundle related assets from your memory into logical groups for this worker.
                Groups are specific to this deployment — the underlying assets stay shared platform memory.
            </p>
        </div>
        <button onclick="document.getElementById('new-group-form').classList.toggle('hidden')"
                class="text-sm px-4 py-2 rounded-lg font-semibold transition hover:opacity-90 shrink-0"
                class="ac-on">
            + New Group
        </button>
    </div>

    {{-- New Group Form --}}
    <div id="new-group-form" class="hidden mb-6 rounded-xl" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4 rounded-t-xl" style="border-bottom:1px solid var(--border-subtle)">
            <p class="text-sm font-semibold" style="color:var(--text-primary)">Create a new group</p>
        </div>
        <form method="POST" action="{{ route('workers.memory.groups.store', $dep->id) }}" class="px-5 py-4 space-y-3">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Group Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. ACME Corp Website Stack"
                           class="w-full text-sm rounded-lg px-3 py-2.5 border focus:outline-none"
                           style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                </div>
                @if($groupTypes)
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Group Type</label>
                    <select name="type" class="w-full text-sm rounded-lg px-3 py-2.5 border focus:outline-none"
                            style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                        <option value="">— select type —</option>
                        @foreach($groupTypes as $gt)
                            <option value="{{ $gt['value'] }}">{{ $gt['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Client (optional)</label>
                    <select name="client_id" class="w-full text-sm rounded-lg px-3 py-2.5 border focus:outline-none"
                            style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)">
                        <option value="">— no client —</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Notes</label>
                    <textarea name="notes" rows="2" placeholder="What does this group represent?"
                              class="w-full text-sm rounded-lg px-3 py-2.5 border focus:outline-none resize-none"
                              style="background:var(--bg-raised);color:var(--text-primary);border-color:var(--border)"></textarea>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="text-sm px-5 py-2 rounded-lg font-semibold transition hover:opacity-90"
                        class="ac-on">Create Group</button>
                <button type="button" onclick="document.getElementById('new-group-form').classList.add('hidden')"
                        class="text-sm px-4 py-2 rounded-lg border transition hover:opacity-80"
                        style="border-color:var(--border);color:var(--text-muted)">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Group type legend --}}
    @if($groupTypes)
    <div class="mb-5 flex flex-wrap gap-2">
        @foreach($groupTypes as $gt)
        <div class="flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full"
             style="background:var(--bg-card);border:1px solid var(--border);color:var(--text-muted)">
            <span class="font-semibold" style="color:var(--text-secondary)">{{ $gt['label'] }}</span>
            <span>·</span>
            <span>{{ $gt['description'] }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Groups list --}}
    @forelse($groups as $group)
    @php
        $typeLabel = collect($groupTypes)->firstWhere('value', $group->type)['label'] ?? $group->type;
        $nearestExpiry = $group->items->whereNotNull('renewal_date')->sortBy('renewal_date')->first();
        $days = $nearestExpiry ? (int) now()->diffInDays($nearestExpiry->renewal_date, false) : null;
    @endphp
    <div class="mb-4 rounded-xl" style="background:var(--bg-card);border:1px solid var(--border)">

        {{-- Group header --}}
        <div class="px-5 py-4 rounded-t-xl flex flex-wrap items-start gap-3" style="border-bottom:1px solid var(--border-subtle)">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-bold" style="color:var(--text-primary)">{{ $group->name }}</p>
                    @if($group->type)
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                          style="background:var(--bg-raised);color:var(--accent-text);border:1px solid var(--border)">
                        {{ $typeLabel }}
                    </span>
                    @endif
                    @if($days !== null)
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $days <= 0 ? 'bg-red-900/40 text-red-400' : ($days <= 15 ? 'bg-amber-900/40 text-amber-400' : ($days <= 30 ? 'bg-yellow-900/40 text-yellow-400' : 'bg-gray-800 text-gray-400')) }}">
                        {{ $days <= 0 ? 'Expired' : 'Next expiry ' . $days . 'd' }}
                    </span>
                    @endif
                </div>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">
                    {{ $group->items->count() }} asset{{ $group->items->count() !== 1 ? 's' : '' }}
                    @if($group->client_name) · {{ $group->client_name }} @endif
                </p>
                @if($group->notes)
                    <p class="text-xs mt-1 italic" style="color:var(--text-faint)">{{ $group->notes }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="toggleGroupEdit({{ $group->id }})"
                        class="text-xs font-medium transition hover:opacity-80"
                        class="ac-text">Edit</button>
                <form method="POST" action="{{ route('workers.memory.groups.destroy', [$dep->id, $group->id]) }}"
                      onsubmit="return confirm('Remove group \'{{ addslashes($group->name) }}\'? Assets are not deleted.')">
                    @csrf @method('DELETE')
                    <button class="text-xs transition hover:opacity-80" style="color:var(--text-faint)">Remove</button>
                </form>
            </div>
        </div>

        {{-- Inline edit form --}}
        <div id="group-edit-{{ $group->id }}" class="hidden px-5 py-4 overflow-visible" style="background:var(--bg-raised);border-bottom:1px solid var(--border-subtle)">
            <form method="POST" action="{{ route('workers.memory.groups.update', [$dep->id, $group->id]) }}"
                  class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @csrf @method('PATCH')
                <div class="sm:col-span-2">
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Group Name</label>
                    <input type="text" name="name" value="{{ $group->name }}" required
                           class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                           style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                </div>
                @if($groupTypes)
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Group Type</label>
                    <select name="type" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                            style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                        <option value="">— none —</option>
                        @foreach($groupTypes as $gt)
                            <option value="{{ $gt['value'] }}" {{ $group->type === $gt['value'] ? 'selected' : '' }}>
                                {{ $gt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Client</label>
                    <select name="client_id" class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none"
                            style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                        <option value="">— none —</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ $group->client_id == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs block mb-1" style="color:var(--text-muted)">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none resize-none"
                              style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">{{ $group->notes }}</textarea>
                </div>
                <div class="sm:col-span-2 flex gap-2">
                    <button type="submit" class="text-sm px-4 py-2 rounded-lg font-semibold transition hover:opacity-90"
                            class="ac-on">Save</button>
                    <button type="button" onclick="toggleGroupEdit({{ $group->id }})"
                            class="text-sm px-4 py-2 rounded-lg border transition hover:opacity-80"
                            style="border-color:var(--border);color:var(--text-muted)">Cancel</button>
                </div>
            </form>
        </div>

        {{-- Asset items --}}
        <div class="divide-y" style="border-color:var(--border-subtle)">
            @forelse($group->items as $item)
            @php
                $iDays = $item->renewal_date ? (int) now()->diffInDays($item->renewal_date, false) : null;
                $iColor = $iDays === null ? 'var(--text-faint)' : ($iDays <= 0 ? '#f87171' : ($iDays <= 15 ? '#fbbf24' : ($iDays <= 30 ? '#facc15' : 'var(--text-muted)')));
            @endphp
            <div class="px-5 py-3 flex items-center gap-3">
                <div class="w-1.5 h-1.5 rounded-full shrink-0" style="background:{{ $iColor }}"></div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium" style="color:var(--text-primary)">{{ $item->name }}</p>
                    <p class="text-xs" style="color:var(--text-muted)">
                        {{ $item->type }}{{ $item->vendor ? ' · ' . $item->vendor : '' }}
                        @if($item->renewal_date)
                            · <span style="color:{{ $iColor }}">{{ $item->renewal_date }}</span>
                            @if($iDays !== null)
                                <span style="color:var(--text-faint)">({{ $iDays <= 0 ? 'expired' : $iDays . 'd' }})</span>
                            @endif
                        @endif
                    </p>
                </div>
                <form method="POST"
                      action="{{ route('workers.memory.groups.items.remove', [$dep->id, $group->id, $item->id]) }}">
                    @csrf @method('DELETE')
                    <button class="text-xs transition hover:opacity-80" style="color:var(--text-faint)">Remove</button>
                </form>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-xs" style="color:var(--text-faint)">
                No assets in this group yet. Add one below.
            </div>
            @endforelse
        </div>

        {{-- Add asset to group --}}
        @php
            $groupItemIds    = $group->items->pluck('id')->toArray();
            $availableAssets = $assets
                ->where('client_id', $group->client_id)   // client-scoped
                ->where('type', '!=', 'discovered')        // confirmed assets only
                ->whereNotIn('id', $groupItemIds);
        @endphp
        @if(!$group->client_id)
        <div class="px-5 py-3 rounded-b-xl text-xs" style="border-top:1px solid var(--border-subtle);color:var(--text-faint)">
            Assign a client to this group to add assets.
        </div>
        @elseif($availableAssets->isNotEmpty())
        <div class="px-5 py-3 rounded-b-xl" style="border-top:1px solid var(--border-subtle);background:var(--bg-raised)">
            <form method="POST" action="{{ route('workers.memory.groups.items.add', [$dep->id, $group->id]) }}"
                  class="flex items-center gap-2 flex-wrap">
                @csrf
                <select name="asset_id" required
                        class="flex-1 min-w-0 text-sm rounded-lg px-3 py-2 border focus:outline-none"
                        style="background:var(--bg-card);color:var(--text-primary);border-color:var(--border)">
                    <option value="">— select asset to add —</option>
                    @foreach($availableAssets as $a)
                    <option value="{{ $a->id }}">
                        {{ $a->name }} ({{ $a->type }}{{ $a->renewal_date ? ' · ' . $a->renewal_date : '' }})
                    </option>
                    @endforeach
                </select>
                <button type="submit"
                        class="text-sm px-4 py-2 rounded-lg border transition hover:opacity-80 shrink-0"
                        style="border-color:var(--border);color:var(--text-muted)">Add Asset</button>
            </form>
        </div>
        @endif

    </div>
    @empty
    <div class="rounded-xl px-5 py-16 text-center" style="background:var(--bg-card);border:1px solid var(--border);overflow:hidden">
        <p class="text-sm font-medium mb-1" style="color:var(--text-primary)">No groups yet</p>
        <p class="text-xs mb-4" style="color:var(--text-muted)">
            Group related assets together — e.g. all assets for one client's website, or all policies under one contract.
        </p>
        <button onclick="document.getElementById('new-group-form').classList.remove('hidden'); window.scrollTo({top:0,behavior:'smooth'})"
                class="text-sm px-5 py-2 rounded-lg font-semibold transition hover:opacity-90"
                class="ac-on">Create your first group</button>
    </div>
    @endforelse

    <x-self-learn
        page-key="workers.memory.groups"
        title="Asset Groups"
        body="Groups let you bundle related assets into logical sets for this worker. The assets themselves stay in platform memory — groups are just a worker-specific lens. When AVA drafts a renewal notice for any asset in a group, she can reference the full group context and bundle multiple expiring assets into one message instead of sending separate notices." />

    <script>
    function toggleGroupEdit(id) {
        document.getElementById('group-edit-' + id).classList.toggle('hidden');
    }
    </script>

</x-app-layout>

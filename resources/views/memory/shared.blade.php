<x-app-layout title="Shared Memory">

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('memory') }}#shared" class="text-xs transition hover:opacity-80" style="color:var(--text-faint)">← Memory</a>
            </div>
            <h1 class="text-white text-lg font-bold">{{ $grant->owner_name }}'s Memory</h1>
            <p class="text-gray-500 text-xs mt-0.5">{{ $grant->deployment_name }} · {{ $grant->worker_slug }}</p>
        </div>
        <div class="flex flex-wrap gap-1.5">
            @foreach($permissions as $p)
            <span class="text-xs px-2 py-1 rounded-md bg-gray-800 text-gray-400 font-medium">{{ $p }}</span>
            @endforeach
        </div>
    </div>

    @php
        $tables = [
            'clients'  => ['label' => 'Clients',  'icon' => '◉', 'cols' => ['name', 'email', 'phone', 'company', 'status', 'renewal_date', 'address']],
            'contacts' => ['label' => 'Contacts', 'icon' => '◎', 'cols' => ['name', 'email', 'phone', 'role', 'company', 'department']],
            'assets'   => ['label' => 'Assets',   'icon' => '◈', 'cols' => ['name', 'type', 'value']],
        ];
    @endphp

    <div class="space-y-6">
    @foreach($tables as $tableName => $meta)
        @php $records = $memory[$tableName] ?? collect(); @endphp
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800 flex items-center gap-3">
                <span class="text-gray-500">{{ $meta['icon'] }}</span>
                <p class="text-white text-sm font-semibold">{{ $meta['label'] }}</p>
                <span class="text-gray-600 text-xs">{{ $records->count() }} records</span>

                @if(in_array('upload', $permissions))
                <button onclick="toggleUpload('{{ $tableName }}')"
                    class="ml-auto text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:border-gray-500 hover:text-white transition">
                    + Add Record
                </button>
                @endif
            </div>

            {{-- Upload form --}}
            @if(in_array('upload', $permissions))
            <div id="upload-{{ $tableName }}" class="hidden border-b border-gray-800 px-5 py-4 bg-gray-950/40">
                <form method="POST" action="{{ route('memory.access.upload', $grant->id) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="table_name" value="{{ $tableName }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($meta['cols'] as $col)
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 font-medium">{{ ucfirst($col) }}</label>
                            <input type="text" name="data[{{ $col }}]"
                                class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:border-gray-500 focus:outline-none">
                        </div>
                        @endforeach
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="text-xs px-4 py-2 rounded-lg font-semibold ac-on">Add to Memory</button>
                        <button type="button" onclick="toggleUpload('{{ $tableName }}')"
                            class="text-xs px-4 py-2 rounded-lg border border-gray-700 text-gray-400 hover:text-white transition">Cancel</button>
                    </div>
                </form>
            </div>
            @endif

            {{-- Records --}}
            @if($records->isEmpty())
            <div class="px-5 py-8 text-center">
                <p class="text-gray-600 text-xs">No {{ strtolower($meta['label']) }} in this memory yet.</p>
            </div>
            @else
            <div class="divide-y divide-gray-800">
                @foreach($records as $record)
                @php
                    $alreadyCopied = in_array($record->id, $copiedIds[$tableName] ?? []);
                @endphp
                <div class="px-5 py-3.5 flex flex-wrap items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-white text-sm font-semibold">{{ $record->name ?? '—' }}</p>
                            @if($tableName === 'contacts' && !empty($record->is_decision_maker))
                            <span class="text-xs px-1.5 py-0.5 rounded bg-yellow-900/40 text-yellow-400 font-medium">Decision Maker</span>
                            @endif
                            @if($tableName === 'clients' && !empty($record->status))
                            <span class="text-xs px-1.5 py-0.5 rounded bg-gray-800 text-gray-400">{{ $record->status }}</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-0.5 mt-0.5">
                            @foreach(array_slice($meta['cols'], 1) as $col)
                                @if(!empty($record->$col) && $col !== 'status')
                                <span class="text-gray-500 text-xs">{{ $record->$col }}</span>
                                @endif
                            @endforeach
                        </div>
                        @if(!empty($record->notes))
                        <p class="text-gray-600 text-xs mt-1 italic">{{ Str::limit($record->notes, 80) }}</p>
                        @endif
                        @if(!empty($record->meta) && $record->meta !== 'null')
                        <p class="text-gray-700 text-xs mt-0.5 font-mono">+ extended data</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($alreadyCopied)
                        <span class="text-xs text-gray-600 italic">Copied</span>
                        @elseif(in_array('copy', $permissions) && $granteeDeployments->isNotEmpty())
                        <form method="POST" action="{{ route('memory.access.copy', $grant->id) }}">
                            @csrf
                            <input type="hidden" name="table_name" value="{{ $tableName }}">
                            <input type="hidden" name="record_id" value="{{ $record->id }}">
                            <select name="target_deployment_id" class="bg-gray-800 border border-gray-700 text-gray-400 text-xs rounded-lg px-2 py-1.5 focus:outline-none mr-1">
                                @foreach($granteeDeployments as $dep)
                                <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:border-purple-700 hover:text-purple-400 transition">
                                Copy
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    @endforeach
    </div>

    {{-- ── Groups ─────────────────────────────────────────────────────────── --}}
    @if(isset($ownerGroups) && $ownerGroups->isNotEmpty())
    <div class="mt-6">
        <div class="flex items-center gap-2 mb-3">
            <p class="text-sm font-semibold" style="color:var(--text-primary)">Asset Groups</p>
            <span class="text-xs" style="color:var(--text-faint)">{{ $ownerGroups->count() }} group{{ $ownerGroups->count()!==1?'s':'' }}</span>
        </div>
        <div class="space-y-3">
            @foreach($ownerGroups as $group)
            <div class="rounded-xl" style="background:var(--bg-card);border:1px solid var(--border)">
                <div class="px-5 py-3.5 flex flex-wrap items-center gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-semibold" style="color:var(--text-primary)">{{ $group->name }}</p>
                            @if($group->type)
                            <span class="text-xs px-2 py-0.5 rounded-full" style="background:var(--bg-raised);color:var(--accent-text);border:1px solid var(--border)">{{ $group->type }}</span>
                            @endif
                        </div>
                        <p class="text-xs mt-0.5" style="color:var(--text-muted)">
                            {{ $group->items->count() }} asset{{ $group->items->count()!==1?'s':'' }}
                            @if(!empty($group->client_name)) · {{ $group->client_name }} @endif
                        </p>
                    </div>
                </div>
                @if($group->items->isNotEmpty())
                <div class="px-5 pb-3 flex flex-wrap gap-2">
                    @foreach($group->items as $item)
                    @php $iDays = $item->renewal_date ? (int) now()->diffInDays($item->renewal_date, false) : null; @endphp
                    <span class="text-xs px-2.5 py-1 rounded-lg flex items-center gap-1.5" style="background:var(--bg-raised);color:var(--text-muted)">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $iDays !== null && $iDays <= 0 ? 'bg-red-400' : ($iDays !== null && $iDays <= 30 ? 'bg-amber-400' : 'bg-gray-600') }}"></span>
                        {{ $item->name }}
                        @if($item->renewal_date)<span style="color:var(--text-faint)">· {{ $item->renewal_date }}</span>@endif
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <x-self-learn
        page-key="memory.shared"
        title="Shared Memory View"
        body="You're viewing memory shared with you by another user. Your permissions are shown at the top. Copied records go into your own deployment and are tagged so you can delete your copy later without affecting the original. Every action you take is logged in the owner's activity trail." />

    <script>
    function toggleUpload(table) {
        document.getElementById('upload-' + table).classList.toggle('hidden');
    }
    </script>

</x-app-layout>

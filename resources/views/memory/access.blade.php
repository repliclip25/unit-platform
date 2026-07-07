<x-app-layout title="Memory Access">

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-white text-lg font-bold">Memory Access</h1>
            <p class="text-gray-500 text-xs mt-0.5">Grant team members access to your worker memory, or view memory shared with you.</p>
        </div>
        <div class="text-right">
            <p class="text-gray-600 text-xs mb-0.5">Your profile code</p>
            <div class="flex items-center gap-2">
                <span class="font-mono text-sm font-bold text-white tracking-widest bg-gray-900 border border-gray-700 rounded-lg px-3 py-1.5">{{ $myProfileCode ?? '—' }}</span>
                <button onclick="navigator.clipboard.writeText('{{ $myProfileCode }}'); this.textContent='Copied!'; setTimeout(()=>this.textContent='Copy',1500)"
                    class="text-xs text-gray-500 hover:text-white transition">Copy</button>
            </div>
        </div>
    </div>

    {{-- Tab bar --}}
    <div class="overflow-x-auto mb-6">
        <div class="flex gap-1 bg-gray-900 border border-gray-800 p-1 rounded-xl w-fit min-w-max">
            <button onclick="switchTab('outgoing')" id="tab-outgoing"
                class="ma-tab px-4 py-2 rounded-lg text-sm font-semibold transition bg-gray-800 text-white whitespace-nowrap">
                Shared by Me
                @if($outgoing->count()) <span class="ml-1.5 text-xs bg-gray-700 text-gray-300 rounded-full px-1.5 py-0.5">{{ $outgoing->count() }}</span> @endif
            </button>
            <button onclick="switchTab('incoming')" id="tab-incoming"
                class="ma-tab px-4 py-2 rounded-lg text-sm font-semibold transition text-gray-400 hover:text-white whitespace-nowrap">
                Shared With Me
                @if($incoming->count()) <span class="ml-1.5 text-xs bg-gray-700 text-gray-300 rounded-full px-1.5 py-0.5">{{ $incoming->count() }}</span> @endif
            </button>
            <button onclick="switchTab('invite')" id="tab-invite"
                class="ma-tab px-4 py-2 rounded-lg text-sm font-semibold transition text-gray-400 hover:text-white whitespace-nowrap">
                + Invite Someone
            </button>
        </div>
    </div>

    {{-- ── SHARED BY ME ──────────────────────────────────────────────────────── --}}
    <div id="panel-outgoing" class="ma-panel space-y-4">
        @forelse($outgoing as $grant)
        @php $perms = json_decode($grant->permissions, true); @endphp
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <div class="px-5 py-4 flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-white text-sm font-semibold">{{ $grant->grantee_name }}</p>
                        <span class="font-mono text-xs text-gray-500">{{ $grant->grantee_code }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold
                            {{ $grant->status === 'accepted' ? 'bg-green-900/40 text-green-400' : 'bg-yellow-900/40 text-yellow-400' }}">
                            {{ ucfirst($grant->status) }}
                        </span>
                    </div>
                    <p class="text-gray-500 text-xs mt-0.5">{{ $grant->deployment_name }} · {{ $grant->worker_slug }}</p>
                    <div class="flex gap-1.5 flex-wrap mt-2">
                        @foreach($perms as $p)
                        <span class="text-xs px-2 py-0.5 rounded-md bg-gray-800 text-gray-400">{{ $p }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-gray-600 text-xs">{{ $grant->event_count }} actions</p>
                    @if($grant->last_action)
                    <p class="text-gray-600 text-xs">Last: {{ \Carbon\Carbon::parse($grant->last_action)->diffForHumans() }}</p>
                    @endif
                    <form method="POST" action="{{ route('memory.access.revoke', $grant->id) }}" class="mt-2"
                          onsubmit="return confirm('Revoke access for {{ $grant->grantee_name }}?')">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-500 hover:border-red-800 hover:text-red-400 transition">
                            Revoke
                        </button>
                    </form>
                </div>
            </div>

            {{-- Audit trail (collapsible) --}}
            @if($grant->event_count > 0)
            <div class="border-t border-gray-800">
                <button onclick="toggleAudit({{ $grant->id }})"
                    class="w-full px-5 py-2.5 text-left text-xs text-gray-600 hover:text-gray-400 transition flex items-center gap-2">
                    <span id="audit-chevron-{{ $grant->id }}">▶</span> View activity trail
                </button>
                <div id="audit-{{ $grant->id }}" class="hidden border-t border-gray-800">
                    @php
                        $events = \Illuminate\Support\Facades\DB::table('memory_access_events as e')
                            ->join('users as u', 'u.id', '=', 'e.actor_user_id')
                            ->where('e.grant_id', $grant->id)
                            ->select('e.*', 'u.name as actor_name')
                            ->orderByDesc('e.created_at')
                            ->limit(20)
                            ->get();
                    @endphp
                    <div class="divide-y divide-gray-800">
                        @foreach($events as $ev)
                        <div class="px-5 py-2.5 flex items-center gap-3">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0
                                {{ $ev->action === 'modified' ? 'bg-yellow-400' : ($ev->action === 'uploaded' ? 'bg-blue-400' : ($ev->action === 'copied' ? 'bg-purple-400' : 'bg-gray-600')) }}">
                            </span>
                            <div class="flex-1 min-w-0">
                                <span class="text-white text-xs font-medium">{{ $ev->actor_name }}</span>
                                <span class="text-gray-500 text-xs"> {{ $ev->action }} </span>
                                <span class="text-gray-400 text-xs font-mono">{{ $ev->table_name }}#{{ $ev->record_id }}</span>
                                @if($ev->notes)
                                <span class="text-gray-600 text-xs"> — {{ $ev->notes }}</span>
                                @endif
                            </div>
                            <span class="text-gray-700 text-xs shrink-0">{{ \Carbon\Carbon::parse($ev->created_at)->diffForHumans() }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-12 text-center">
            <p class="text-gray-500 text-sm">You haven't shared any memory yet.</p>
            <button onclick="switchTab('invite')" class="mt-2 text-xs font-semibold" style="color:var(--accent-text)">Invite someone →</button>
        </div>
        @endforelse
    </div>

    {{-- ── SHARED WITH ME ────────────────────────────────────────────────────── --}}
    <div id="panel-incoming" class="ma-panel hidden space-y-4">
        @forelse($incoming as $grant)
        @php $perms = json_decode($grant->permissions, true); @endphp
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-4 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-semibold">{{ $grant->owner_name }}'s memory</p>
                <p class="text-gray-500 text-xs mt-0.5">{{ $grant->deployment_name }} · {{ $grant->worker_slug }}</p>
                <div class="flex gap-1.5 flex-wrap mt-2">
                    @foreach($perms as $p)
                    <span class="text-xs px-2 py-0.5 rounded-md bg-gray-800 text-gray-400">{{ $p }}</span>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('memory.shared', $grant->id) }}"
               class="text-xs px-4 py-2 rounded-lg font-semibold transition shrink-0"
               style="background:var(--accent);color:#000">
                Open Memory →
            </a>
        </div>
        @empty
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-12 text-center">
            <p class="text-gray-500 text-sm">No memory has been shared with you yet.</p>
        </div>
        @endforelse
    </div>

    {{-- ── INVITE FORM ───────────────────────────────────────────────────────── --}}
    <div id="panel-invite" class="ma-panel hidden">
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800">
                <p class="text-white text-sm font-semibold">Invite a team member</p>
                <p class="text-gray-500 text-xs mt-0.5">They must already have a UNIT account. Enter their profile code (e.g. UNIT-AB3XY) or email address.</p>
            </div>
            <form method="POST" action="{{ route('memory.access.invite') }}" class="px-5 py-5 space-y-5">
                @csrf

                @if($errors->any())
                <div class="bg-red-900/40 border border-red-800 text-red-300 rounded-lg px-4 py-3 text-sm">
                    {{ $errors->first() }}
                </div>
                @endif

                <div>
                    <label class="block text-xs text-gray-500 mb-1.5 font-medium">Profile code or email</label>
                    <input type="text" name="lookup" value="{{ old('lookup') }}"
                        placeholder="UNIT-AB3XY or name@company.com"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:border-gray-500 focus:outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1.5 font-medium">Which deployment's memory</label>
                    <select name="deployment_id"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2.5 text-white text-sm focus:border-gray-500 focus:outline-none">
                        <option value="">Select a deployment…</option>
                        @foreach($myDeployments as $dep)
                        <option value="{{ $dep->id }}" {{ old('deployment_id') == $dep->id ? 'selected' : '' }}>
                            {{ $dep->name }} ({{ $dep->worker_slug }})
                        </option>
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
                    <label class="block text-xs text-gray-500 mb-2 font-medium">Permissions</label>
                    <div class="space-y-2">
                        @foreach($permOptions as [$val, $label, $desc])
                        @php $checked = in_array($val, old('permissions', ['view'])); @endphp
                        <label class="flex items-center justify-between gap-4 p-3 rounded-lg border border-gray-700 hover:border-gray-600 cursor-pointer transition">
                            <div>
                                <p class="text-white text-xs font-semibold">{{ $label }}</p>
                                <p class="text-gray-500 text-xs mt-0.5">{{ $desc }}</p>
                            </div>
                            <div class="relative shrink-0">
                                <input type="checkbox" name="permissions[]" value="{{ $val }}"
                                    {{ $checked ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="w-9 h-5 rounded-full transition peer-checked:bg-yellow-400 bg-gray-700
                                            after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                            after:w-4 after:h-4 after:rounded-full after:bg-white after:transition-all
                                            peer-checked:after:translate-x-4"></div>
                            </div>
                        </label>
                        @endforeach
                        <div class="flex items-center justify-between gap-4 p-3 rounded-lg border border-gray-800 opacity-40">
                            <div>
                                <p class="text-white text-xs font-semibold">Delete</p>
                                <p class="text-gray-500 text-xs mt-0.5">Never available to collaborators</p>
                            </div>
                            <div class="w-9 h-5 rounded-full bg-gray-800 relative shrink-0">
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-gray-600"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full sm:w-auto text-sm px-6 py-2.5 rounded-lg font-semibold transition"
                    style="background:var(--accent);color:#000">
                    Send Invitation
                </button>
            </form>
        </div>
    </div>

    <x-self-learn
        page-key="memory.access"
        title="Memory Access & Collaboration"
        body="Share your worker memory with team members by profile code or email. They must already have a UNIT account. You control exactly what they can do — view, copy, upload, or modify — and they can never delete your records. Every action they take is logged in the activity trail." />

    <script>
    function switchTab(id) {
        document.querySelectorAll('.ma-tab').forEach(t => {
            t.classList.remove('bg-gray-800', 'text-white');
            t.classList.add('text-gray-400', 'hover:text-white');
        });
        document.querySelectorAll('.ma-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('tab-' + id).classList.remove('text-gray-400', 'hover:text-white');
        document.getElementById('tab-' + id).classList.add('bg-gray-800', 'text-white');
        document.getElementById('panel-' + id).classList.remove('hidden');
    }

    function toggleAudit(id) {
        const panel   = document.getElementById('audit-' + id);
        const chevron = document.getElementById('audit-chevron-' + id);
        const hidden  = panel.classList.toggle('hidden');
        chevron.textContent = hidden ? '▶' : '▼';
    }

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => switchTab('invite'));
    @endif
    </script>

</x-app-layout>

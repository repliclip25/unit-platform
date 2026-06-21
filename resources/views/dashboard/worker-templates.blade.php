<x-app-layout title="{{ $dep->name }} · Templates">

    @include('partials.worker-subnav')

    @php
        $userId     = auth()->id();
        $byCategory = $templates->groupBy('category');
        $editTarget = session('edit_template');
    @endphp

    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-white font-semibold">Email Templates</h2>
                <p class="text-gray-500 text-xs mt-0.5">
                    Platform defaults are read-only — click <strong class="text-gray-400">Customize</strong> to create your editable copy.
                    AVA always uses your custom version first.
                </p>
            </div>
            <button onclick="openAddModal()"
                    class="text-xs px-4 py-2 rounded-xl font-medium bg-brand hover:bg-brand-deep text-brand-text transition">
                + New Template
            </button>
        </div>

        {{-- Template groups by category --}}
        @forelse($byCategory as $category => $group)
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">

            <div class="px-5 py-3 border-b border-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h3 class="text-white text-sm font-semibold">{{ $category }}</h3>
                    @php $tenantCount = $group->where('user_id', $userId)->count(); @endphp
                    @if($tenantCount)
                        <span class="text-xs bg-brand/15 text-brand border border-brand/40 px-2 py-0.5 rounded-full">{{ $tenantCount }} custom</span>
                    @endif
                </div>
                <span class="text-gray-600 text-xs">{{ $group->count() }} template(s)</span>
            </div>

            <div class="divide-y divide-gray-800">
            @php
                // Collect IDs of defaults that have already been forked by this tenant
                $forkedIds = $group->where('user_id', $userId)->pluck('forked_from')->filter()->flip();
            @endphp
            @foreach($group as $t)
            @php
                $isOwned   = (int)$t->user_id === $userId;
                $isDefault = !$t->user_id;
                // Hide platform default if tenant already has a custom copy of it
                if ($isDefault && isset($forkedIds[$t->id])) continue;
            @endphp

            <div class="px-5 py-4" id="template-{{ $t->id }}">

                {{-- Top row: name + badges + actions --}}
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-white text-sm font-medium">{{ $t->name }}</p>
                        @if($isDefault)
                            <span class="text-xs bg-gray-800 text-gray-500 border border-gray-700 px-2 py-0.5 rounded-full">Platform default</span>
                        @else
                            <span class="text-xs bg-brand/12 text-brand border border-brand/40 px-2 py-0.5 rounded-full">Your template</span>
                            @if($t->forked_from)
                                <span class="text-xs text-gray-600">forked from default</span>
                            @endif
                        @endif
                        @if($t->approval_required)
                            <span class="text-xs bg-amber-900/30 text-amber-400 border border-amber-800 px-2 py-0.5 rounded-full"
                                  title="AVA will draft the email but wait for you to review and send it from Transactions">
                                ✋ You review before send
                            </span>
                        @else
                            <span class="text-xs bg-green-900/20 text-green-500 border border-green-900 px-2 py-0.5 rounded-full"
                                  title="AVA sends immediately without waiting for your review">
                                ⚡ Auto-sends
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Test send --}}
                        <form method="POST" action="{{ route('workers.templates.test', [$dep->id, $t->id]) }}">
                            @csrf
                            <button type="submit"
                                    title="Send test to {{ auth()->user()->email }}"
                                    class="text-xs px-3 py-1.5 rounded-lg font-medium bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white border border-gray-700 transition">
                                ▶ Test Send
                            </button>
                        </form>

                        @if($isDefault)
                            <button onclick="customizeTemplate({{ $t->id }})"
                                    id="customize-btn-{{ $t->id }}"
                                    class="text-xs px-3 py-1.5 rounded-lg font-medium bg-brand/12 hover:bg-brand-deep/60 text-brand border border-brand/40 transition">
                                Customize
                            </button>
                        @else
                            <button onclick="openEditModal({{ $t->id }}, {{ json_encode($t) }})"
                                    class="text-xs px-3 py-1.5 rounded-lg font-medium bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700 transition">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('workers.templates.destroy', [$dep->id, $t->id]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Remove this template?')"
                                        class="text-xs px-3 py-1.5 rounded-lg font-medium text-red-500 hover:text-red-400 border border-red-900/40 hover:bg-red-900/20 transition">
                                    Remove
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Preview --}}
                <p class="text-xs mb-1">
                    <span class="text-gray-600">Tone: </span><span class="text-gray-400">{{ $t->tone }}</span>
                </p>
                <p class="text-xs mb-2">
                    <span class="text-gray-600">Subject: </span>
                    <span class="text-gray-300 font-mono">{{ $t->subject_template }}</span>
                </p>
                <pre class="text-gray-500 text-xs whitespace-pre-wrap bg-gray-950 rounded-xl px-4 py-3 border border-gray-800 leading-relaxed">{{ $t->body_template }}</pre>

            </div>
            @endforeach
            </div>

        </div>
        @empty
            <div class="bg-gray-900 border border-dashed border-gray-700 rounded-2xl p-12 text-center">
                <p class="text-gray-500 text-sm">No templates yet.</p>
                <p class="text-gray-600 text-xs mt-1">Platform defaults will appear here once seeded.</p>
            </div>
        @endforelse

    </div>

    {{-- ── Edit Modal ────────────────────────────────────────────────── --}}
    <div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center"
         style="background:rgba(0,0,0,0.8);backdrop-filter:blur(8px)">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-2xl mx-4 overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-white font-semibold text-sm">Edit Template</h3>
                    <p class="text-gray-500 text-xs mt-0.5">Changes apply to your worker only — platform default is unchanged.</p>
                </div>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-white text-xl leading-none">✕</button>
            </div>
            <form id="edit-form" method="POST" class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
                @csrf @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-gray-400 text-xs block mb-1">Template Name</label>
                        <input type="text" name="name" id="edit-name" required
                               class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:border-brand focus:outline-none">
                    </div>
                    <div>
                        <label class="text-gray-400 text-xs block mb-1">Tone</label>
                        <input type="text" name="tone" id="edit-tone"
                               class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:border-brand focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="text-gray-400 text-xs block mb-1">Subject Template</label>
                    <input type="text" name="subject_template" id="edit-subject" required
                           class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:border-brand focus:outline-none font-mono">
                    <p class="text-gray-600 text-xs mt-1">Available: <span class="font-mono">@{{asset}}, @{{due_date}}, @{{client}}, @{{renewal_price}}</span></p>
                </div>

                <div>
                    <label class="text-gray-400 text-xs block mb-1">Body Template</label>
                    <textarea name="body_template" id="edit-body" rows="11" required
                              class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:border-brand focus:outline-none font-mono resize-none"></textarea>
                    <p class="text-gray-600 text-xs mt-1">Available: <span class="font-mono">@{{contact_first_name}}, @{{asset}}, @{{client}}, @{{due_date}}, @{{sender_name}}, @{{renewal_price}}, @{{days_until_expiry}}</span></p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="approval_required" id="edit-approval" value="1"
                           class="rounded border-gray-600 bg-gray-800 text-brand focus:ring-brand">
                    <label for="edit-approval" class="text-gray-400 text-xs">You review drafts before AVA sends <span class="text-gray-600">(uncheck to let AVA auto-send)</span></label>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white bg-brand hover:bg-brand transition">
                        Save Changes
                    </button>
                    <button type="button" onclick="closeEditModal()"
                            class="px-6 py-2.5 rounded-xl text-sm text-gray-400 border border-gray-700 hover:bg-gray-800 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Add Modal ─────────────────────────────────────────────────── --}}
    <div id="add-modal" class="fixed inset-0 z-50 hidden items-center justify-center"
         style="background:rgba(0,0,0,0.8);backdrop-filter:blur(8px)">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-2xl mx-4 overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between shrink-0">
                <h3 class="text-white font-semibold text-sm">New Template</h3>
                <button onclick="closeAddModal()" class="text-gray-500 hover:text-white text-xl leading-none">✕</button>
            </div>
            <form method="POST" action="{{ route('workers.templates.store', $dep->id) }}" class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-gray-400 text-xs block mb-1">Template Name</label>
                        <input type="text" name="name" required
                               class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:border-brand focus:outline-none">
                    </div>
                    <div>
                        <label class="text-gray-400 text-xs block mb-1">Category</label>
                        <input type="text" name="category" required placeholder="e.g. Domain Renewal"
                               class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:border-brand focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="text-gray-400 text-xs block mb-1">Tone</label>
                    <input type="text" name="tone" value="Professional, concise"
                           class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:border-brand focus:outline-none">
                </div>

                <div>
                    <label class="text-gray-400 text-xs block mb-1">Subject Template</label>
                    <input type="text" name="subject_template" required
                           class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:border-brand focus:outline-none font-mono">
                </div>

                <div>
                    <label class="text-gray-400 text-xs block mb-1">Body Template</label>
                    <textarea name="body_template" rows="8" required
                              class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:border-brand focus:outline-none font-mono resize-none"></textarea>
                    <p class="text-gray-600 text-xs mt-1">Available: <span class="font-mono">@{{contact_first_name}}, @{{asset}}, @{{client}}, @{{due_date}}, @{{sender_name}}, @{{renewal_price}}</span></p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="approval_required" value="1" checked
                           class="rounded border-gray-600 bg-gray-800 text-brand focus:ring-brand">
                    <label class="text-gray-400 text-xs">You review drafts before AVA sends <span class="text-gray-600">(uncheck to let AVA auto-send)</span></label>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white bg-brand hover:bg-brand transition">
                        Save Template
                    </button>
                    <button type="button" onclick="closeAddModal()"
                            class="px-6 py-2.5 rounded-xl text-sm text-gray-400 border border-gray-700 hover:bg-gray-800 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const WORKER_ID = {{ $dep->id }};

    function openEditModal(id, t) {
        document.getElementById('edit-name').value    = t.name;
        document.getElementById('edit-tone').value    = t.tone || '';
        document.getElementById('edit-subject').value = t.subject_template;
        document.getElementById('edit-body').value    = t.body_template;
        document.getElementById('edit-approval').checked = !!parseInt(t.approval_required);
        document.getElementById('edit-form').action   = '/workers/' + WORKER_ID + '/templates/' + id;
        showModal('edit-modal');
    }
    function closeEditModal() { hideModal('edit-modal'); }
    function openAddModal()   { showModal('add-modal'); }
    function closeAddModal()  { hideModal('add-modal'); }

    async function customizeTemplate(defaultId) {
        const btn = document.getElementById('customize-btn-' + defaultId);
        btn.textContent = 'Creating…';
        btn.disabled = true;

        try {
            const res = await fetch('/workers/' + WORKER_ID + '/templates/' + defaultId + '/fork', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.template) {
                // Open edit modal immediately with the forked template
                openEditModal(data.template.id, data.template);
                // Reload page on modal close so the new copy appears
                document.getElementById('edit-modal').addEventListener('click', function handler(e) {
                    if (e.target === this) { location.reload(); this.removeEventListener('click', handler); }
                });
                document.getElementById('edit-form').addEventListener('submit', function() {
                    sessionStorage.setItem('template_saved', '1');
                }, { once: true });
            }
        } catch(e) {
            btn.textContent = 'Customize';
            btn.disabled = false;
            alert('Failed to customize template. Please try again.');
        }
    }
    function showModal(id) {
        const m = document.getElementById(id);
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function hideModal(id) {
        const m = document.getElementById(id);
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
    @if(session('edit_template'))
    document.addEventListener('DOMContentLoaded', function () {
        const card = document.getElementById('template-{{ session('edit_template') }}');
        if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    @endif
    </script>

</x-app-layout>

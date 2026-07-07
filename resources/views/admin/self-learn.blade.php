<x-app-layout title="Self Learn Registry">

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-900 border border-red-700 text-red-200 rounded-xl px-5 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-white text-lg font-bold">Self Learn Registry</h1>
        <p class="text-gray-500 text-xs mt-0.5">Platform-wide contextual guidance shown to users on each page. Edit copy, toggle visibility, or bump version to force a reshow.</p>
    </div>

    {{-- Entries --}}
    <div class="space-y-4">
        @forelse($entries as $entry)
        @php
            $rate = $entry->stats['dismiss_rate'];
            $rateColor = $rate === null ? 'text-gray-600' : ($rate >= 80 ? 'text-green-400' : ($rate >= 50 ? 'text-yellow-400' : 'text-gray-400'));
        @endphp
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">

            {{-- Entry header --}}
            <div class="px-5 py-4 border-b border-gray-800 flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-white text-sm font-semibold font-mono">{{ $entry->page_key }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold
                            {{ $entry->active ? 'bg-green-900/40 text-green-400' : 'bg-gray-800 text-gray-500' }}">
                            {{ $entry->active ? 'Active' : 'Hidden' }}
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-800 text-gray-400 font-mono">
                            v{{ $entry->version }}
                        </span>
                    </div>
                    <p class="text-gray-600 text-xs mt-0.5">Last updated {{ \Carbon\Carbon::parse($entry->updated_at)->diffForHumans() }}</p>
                </div>

                {{-- Engagement stats --}}
                <div class="flex items-center gap-4 text-right shrink-0">
                    <div>
                        <p class="text-gray-600 text-xs">Shown</p>
                        <p class="text-white text-sm font-bold">{{ number_format($entry->stats['shown']) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-xs">Dismissed</p>
                        <p class="text-white text-sm font-bold">{{ number_format($entry->stats['dismissed']) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-xs">Dismiss rate</p>
                        <p class="text-sm font-bold {{ $rateColor }}">{{ $rate !== null ? $rate . '%' : '—' }}</p>
                    </div>
                </div>

                {{-- Controls --}}
                <div class="flex items-center gap-2 shrink-0">
                    <form method="POST" action="{{ route('admin.self-learn.toggle', $entry->page_key) }}">
                        @csrf
                        <button type="submit"
                            class="text-xs px-3 py-1.5 rounded-lg border transition font-medium
                                {{ $entry->active
                                    ? 'border-gray-700 text-gray-400 hover:border-red-800 hover:text-red-400'
                                    : 'border-gray-700 text-gray-400 hover:border-green-800 hover:text-green-400' }}">
                            {{ $entry->active ? 'Hide' : 'Show' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.self-learn.bump', $entry->page_key) }}"
                          onsubmit="return confirm('Bump version to v{{ $entry->version + 1 }}? All users who dismissed this will see it again.')">
                        @csrf
                        <button type="submit"
                            class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:border-yellow-700 hover:text-yellow-400 transition font-medium">
                            ↑ Reshow All
                        </button>
                    </form>
                    <button onclick="toggleEdit('{{ $entry->page_key }}')"
                        class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:border-gray-500 hover:text-white transition font-medium">
                        Edit
                    </button>
                </div>
            </div>

            {{-- Current content preview --}}
            <div class="px-5 py-4 border-b border-gray-800">
                <p class="text-white text-xs font-semibold mb-1">{{ $entry->title }}</p>
                <p class="text-gray-500 text-xs leading-relaxed">{{ $entry->body }}</p>
            </div>

            {{-- Edit form (hidden by default) --}}
            <div id="edit-{{ $entry->page_key }}" class="hidden px-5 py-5 bg-gray-950/50">
                <form method="POST" action="{{ route('admin.self-learn.update', $entry->page_key) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Title</label>
                        <input type="text" name="title" value="{{ old('title', $entry->title) }}"
                            class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:border-gray-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1 font-medium">Body</label>
                        <textarea name="body" rows="4"
                            class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:border-gray-500 focus:outline-none resize-none leading-relaxed">{{ old('body', $entry->body) }}</textarea>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                            class="text-xs px-4 py-2 rounded-lg font-semibold transition"
                            style="background:var(--accent);color:#000">
                            Save
                        </button>
                        <button type="button" onclick="toggleEdit('{{ $entry->page_key }}')"
                            class="text-xs px-4 py-2 rounded-lg border border-gray-700 text-gray-400 hover:text-white transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

        </div>
        @empty
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-12 text-center">
            <p class="text-gray-500 text-sm">No Self Learn entries registered yet.</p>
            <p class="text-gray-600 text-xs mt-1">Add <code class="text-gray-400">&lt;x-self-learn&gt;</code> to any admin blade to register a page.</p>
        </div>
        @endforelse
    </div>

    {{-- How it works --}}
    <div class="mt-8 bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-800">
            <p class="text-white text-sm font-semibold">How Self Learn works</p>
        </div>
        <div class="divide-y divide-gray-800">
            @foreach([
                ['Component', 'Add <x-self-learn page-key="your.key" title="Fallback" body="Fallback..." /> to any page. The component reads live content from this registry.'],
                ['Versioning', 'Each entry has a version number. When you bump the version, all users who previously dismissed it will see the updated content again.'],
                ['Tracking', 'Shown and dismissed events are recorded per user per version. Dismiss rate = unique dismissals ÷ unique impressions.'],
                ['Active toggle', 'Hiding an entry removes it from the UI instantly for all users — their dismissed state is preserved if you re-activate later.'],
            ] as [$label, $desc])
            <div class="px-5 py-3.5 flex items-start gap-4">
                <span class="w-2 h-2 rounded-full shrink-0 mt-1.5 bg-green-400"></span>
                <div>
                    <p class="text-white text-xs font-semibold">{{ $label }}</p>
                    <p class="text-gray-500 text-xs mt-0.5 leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <script>
    function toggleEdit(key) {
        const panel = document.getElementById('edit-' + key);
        panel.classList.toggle('hidden');
    }
    </script>

</x-app-layout>

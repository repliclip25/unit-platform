<x-onboarding-layout :step="$step">

<div class="mb-8">
    <p class="text-yellow-400 text-sm font-semibold uppercase tracking-widest mb-2">Step 1 of 4</p>
    <h1 class="text-2xl font-black text-white mb-2">Pick your first worker</h1>
    <p class="text-gray-400">Each worker is trained for a specific job. You can deploy more later.</p>
</div>

<form method="POST" action="{{ route('onboarding.2') }}" id="worker-form">
    @csrf
    <input type="hidden" name="worker_slug" id="selected_slug" value="">

    <div class="space-y-3 mb-8" id="worker-cards">
        @foreach($workers as $worker)
        @php
            $meta = json_decode($worker->blueprint ?? '{}', true)['meta'] ?? [];
            $category = $meta['category'] ?? 'general';
            $icons = ['renewal' => '🔄', 'invoice' => '🧾', 'compliance' => '📋', 'general' => '⚙️'];
            $icon = $icons[$category] ?? '⚙️';
        @endphp
        <button type="button"
            onclick="selectWorker('{{ $worker->slug }}')"
            id="card-{{ $worker->slug }}"
            class="worker-card w-full text-left bg-gray-900 border border-gray-800 rounded-xl px-5 py-5 hover:border-yellow-400/50 transition-all group">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 bg-gray-800 group-hover:bg-yellow-400/10 rounded-xl flex items-center justify-center text-xl shrink-0 transition-colors">
                    {{ $icon }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-white font-bold">{{ $worker->name }}</p>
                        <span class="text-xs bg-green-500/10 text-green-400 border border-green-500/20 px-2 py-0.5 rounded-full">Free trial</span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed">{{ $worker->description }}</p>
                </div>
                <div id="check-{{ $worker->slug }}" class="hidden w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 text-gray-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
        </button>
        @endforeach
    </div>

    <button type="submit" id="continue-btn"
        class="w-full bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-bold text-base py-4 rounded-xl transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
        disabled>
        Continue →
    </button>
</form>

<script>
function selectWorker(slug) {
    document.getElementById('selected_slug').value = slug;
    document.querySelectorAll('.worker-card').forEach(c => {
        c.classList.remove('border-yellow-400', 'bg-yellow-400/5');
        c.classList.add('border-gray-800');
    });
    document.querySelectorAll('[id^="check-"]').forEach(c => c.classList.add('hidden'));
    document.getElementById('card-' + slug).classList.add('border-yellow-400', 'bg-yellow-400/5');
    document.getElementById('card-' + slug).classList.remove('border-gray-800');
    document.getElementById('check-' + slug).classList.remove('hidden');
    document.getElementById('continue-btn').disabled = false;
}
</script>

</x-onboarding-layout>

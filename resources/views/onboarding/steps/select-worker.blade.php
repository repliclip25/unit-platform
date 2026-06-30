<x-onboarding-layout :stepName="$stepName" :sequence="$sequence" :stepIndex="$stepIndex" :wide="true">

@php
    $enriched = $workers->map(function ($w) use ($contracts) {
        $contract = $contracts[$w->slug] ?? null;
        $employee = $contract ? $contract->employee() : [];
        return [
            'slug'         => $w->slug,
            'name'         => $w->name,
            'description'  => $w->description,
            'tags'         => $contract ? $contract->tags()            : [],
            'media'        => $contract ? $contract->media()           : ['color' => 'var(--accent)'],
            'stages'       => $contract ? $contract->pipelineStages()  : [],
            'setup'        => $contract ? $contract->onboardingSteps() : [],
            'title'        => $employee['title']        ?? '',
            'introduction' => $employee['introduction'] ?? '',
            'what_i_do'    => $employee['what_i_do']    ?? [],
        ];
    })->values()->toArray();
@endphp

<script>
window._WORKERS = @json($enriched);
function workerPicker() {
    return {
        workers:  window._WORKERS || [],
        selected: null,
        query:    '',

        /* Returns the selected worker object, or null */
        wd: function() {
            if (!this.selected) return null;
            for (var i = 0; i < this.workers.length; i++) {
                if (this.workers[i].slug === this.selected) return this.workers[i];
            }
            return null;
        },

        get filtered() {
            var q = (this.query || '').toLowerCase().trim();
            if (!q) return this.workers;
            var ws = this.workers;
            return ws.filter(function(w) {
                return w.name.toLowerCase().indexOf(q) >= 0
                    || w.description.toLowerCase().indexOf(q) >= 0
                    || (w.tags || []).some(function(t){ return t.toLowerCase().indexOf(q) >= 0; });
            });
        },

        pick: function(slug) {
            this.selected = (this.selected === slug) ? null : slug;
        }
    };
}
</script>

<div x-data="workerPicker()" class="flex flex-col lg:flex-row gap-6">

    {{-- ── LEFT ─────────────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        <div class="mb-5">
            <h1 class="text-2xl font-black text-white mb-1">Hire your first employee</h1>
            <p class="text-gray-500 text-sm">Each employee is trained for a specific job. You can hire more later.</p>
        </div>

        {{-- Search --}}
        <div class="relative mb-4">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-600 pointer-events-none"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="query"
                   placeholder="Search workers..."
                   class="w-full bg-gray-900 border border-gray-800 focus:border-yellow-400/50 rounded-xl pl-9 pr-4 py-2.5 text-sm text-gray-200 placeholder-gray-600 outline-none transition-colors"/>
        </div>

        {{-- Worker list --}}
        <div class="space-y-2 overflow-y-auto pr-1" style="max-height:62vh">
            <template x-for="worker in filtered" :key="worker.slug">
                <button type="button" @click="pick(worker.slug)"
                        class="w-full text-left rounded-xl border px-4 py-4 transition-all duration-200"
                        :class="selected === worker.slug
                            ? 'border-yellow-400 bg-yellow-400/5 shadow-lg'
                            : (selected ? 'border-gray-800 bg-gray-900 opacity-40' : 'border-gray-800 bg-gray-900 hover:border-gray-700')">

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center"
                             :style="'background:' + (worker.media.color || 'var(--accent)') + '22'">
                            <span class="text-sm font-black"
                                  :style="'color:' + (worker.media.color || 'var(--accent)')"
                                  x-text="worker.name.charAt(0)"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <p class="text-white font-bold text-sm" x-text="worker.name"></p>
                                <span class="text-xs bg-green-500/10 text-green-400 border border-green-500/20 px-1.5 py-0.5 rounded-full shrink-0">Free trial</span>
                            </div>
                            <p class="text-xs mb-0.5 line-clamp-1"
                               x-show="worker.title"
                               x-text="worker.title"
                               :style="'color:' + (worker.media.color || 'var(--accent)')"></p>
                            <p class="text-gray-500 text-xs leading-relaxed line-clamp-1" x-text="worker.description"></p>
                        </div>
                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0"
                             :class="selected === worker.slug ? 'bg-yellow-400' : 'bg-gray-800'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"
                                 :class="selected === worker.slug ? 'text-gray-950' : 'text-gray-600 opacity-40'">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Pipeline strip on selection --}}
                    <div x-show="selected === worker.slug"
                         class="mt-3 pt-3 border-t border-yellow-400/10">
                        <p class="text-yellow-400/40 text-xs mb-1.5 uppercase tracking-wider">Pipeline</p>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="stage in (worker.stages || [])" :key="stage.key">
                                <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-400/10 text-yellow-400/60 whitespace-nowrap"
                                      x-text="stage.label"></span>
                            </template>
                        </div>
                    </div>
                </button>
            </template>

            <div x-show="filtered.length === 0" class="py-10 text-center">
                <p class="text-gray-600 text-sm">No workers match your search.</p>
                <button type="button" @click="query = ''" class="text-xs text-yellow-400/60 hover:text-yellow-400 mt-2 transition">Clear</button>
            </div>
        </div>
    </div>

    {{-- ── RIGHT ────────────────────────────────────────────────────── --}}
    <div class="lg:w-80 shrink-0 flex flex-col gap-4">

        <div class="rounded-2xl border border-gray-800 bg-gray-900 flex-1 flex flex-col" style="min-height:320px">

            {{-- Empty state --}}
            <div x-show="!selected"
                 class="flex-1 flex flex-col items-center justify-center px-6 py-16 text-center">
                <div class="w-14 h-14 bg-gray-800 border border-gray-700 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                    </svg>
                </div>
                <p class="text-gray-500 text-sm">Select a worker<br>to see what you'll set up</p>
            </div>

            {{-- Selected state — driven by `selected` string, data accessed via wd() method --}}
            <div x-show="selected" class="flex flex-col flex-1">

                {{-- Colour banner --}}
                <div class="h-20 rounded-t-2xl shrink-0"
                     :style="'background:linear-gradient(135deg,' + (wd() ? wd().media.color : 'var(--accent)') + '28 0%,#0d1117 100%)'">
                </div>

                <div class="px-5 pt-5 pb-5 flex-1 overflow-y-auto">
                    <p class="text-white font-bold text-sm mb-0.5" x-text="wd() ? wd().name : ''"></p>
                    <p class="text-xs mb-3" x-text="wd() ? wd().title : ''"
                       :style="'color:' + (wd() ? wd().media.color : 'var(--accent)')"></p>

                    {{-- Introduction quote if available, else fall back to description --}}
                    @foreach($enriched as $workerData)
                    <div x-show="selected === '{{ $workerData['slug'] }}'">
                        @if(!empty($workerData['introduction']))
                        <p class="text-gray-500 text-xs leading-relaxed mb-4 italic">"{{ $workerData['introduction'] }}"</p>
                        @else
                        <p class="text-gray-500 text-xs leading-relaxed mb-4">{{ $workerData['description'] }}</p>
                        @endif
                    </div>
                    @endforeach

                    {{-- What I do — capabilities list if available, else setup steps --}}
                    @foreach($enriched as $workerData)
                    <div x-show="selected === '{{ $workerData['slug'] }}'">
                        @if(!empty($workerData['what_i_do']))
                        <p class="text-gray-600 text-xs uppercase tracking-widest font-semibold mb-3">What I do</p>
                        <div class="space-y-2">
                            @foreach($workerData['what_i_do'] as $capability)
                            <div class="flex items-start gap-2.5">
                                <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 mt-0.5"
                                     style="background:{{ $workerData['media']['color'] ?? 'var(--accent)' }}22">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"
                                         style="color:{{ $workerData['media']['color'] ?? 'var(--accent)' }}">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-gray-400 text-xs leading-relaxed">{{ $capability }}</p>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-gray-600 text-xs uppercase tracking-widest font-semibold mb-3">What you'll set up</p>
                        <div class="space-y-3">
                            @foreach($workerData['setup'] as $stepIndex => $step)
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 text-gray-950"
                                     style="background:{{ $workerData['media']['color'] ?? 'var(--accent)' }}">
                                    {{ $stepIndex + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-white">{{ $step['label'] }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $step['description'] }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach

                    <div class="mt-5 pt-4 border-t border-gray-800 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/>
                        </svg>
                        <p class="text-gray-600 text-xs">Takes about 3 minutes to set up</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Continue --}}
        <form method="POST" action="{{ route('onboarding.step.handle', 'select-worker') }}">
            @csrf
            <input type="hidden" name="worker_slug" x-bind:value="selected">
            <button type="submit"
                    :disabled="!selected"
                    class="w-full font-bold text-base py-4 rounded-xl transition-all"
                    :class="selected ? 'bg-yellow-400 hover:bg-yellow-300 text-gray-950' : 'bg-gray-800 border border-gray-700 text-gray-500 cursor-not-allowed'">
                <span x-show="!selected">Select an employee to hire</span>
                <span x-show="selected">Hire &amp; Set Up →</span>
            </button>
        </form>
    </div>

</div>

</x-onboarding-layout>

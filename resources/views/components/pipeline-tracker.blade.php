{{--
    pipeline-tracker — canonical fast-track pipeline visual.

    Used identically in:
      - Onboarding fast-track step (server-rendered, auto-starts)
      - Dashboard QA modal (triggered via 'open-pipeline' window event)

    Props:
      txId          Transaction ID to poll (empty string = wait for event)
      autoStart     Whether to start polling immediately (true for onboarding)
      onCompleteUrl URL to navigate to on success (null = stay on page)
      context       'onboarding' | 'dashboard' — controls footer actions
--}}
@props([
    'txId'          => '',
    'autoStart'     => false,
    'onCompleteUrl' => null,
    'context'       => 'dashboard',
])

<div x-data="pipelineTracker(
        '{{ $txId }}',
        {{ $autoStart ? 'true' : 'false' }},
        '{{ $onCompleteUrl }}'
     )"
     x-init="init()"
     @open-pipeline.window="openWith($event.detail.txId)"
     @close-pipeline.window="close()">

    {{-- ── Stage circles ── --}}
    <div class="overflow-x-auto pb-2">
        <div class="flex items-start min-w-max px-2 py-4 gap-0">
            <template x-for="(stage, i) in stages" :key="stage.key">
                <div class="flex items-center">
                    {{-- Stage node --}}
                    <div class="flex flex-col items-center w-28">

                        {{-- Circle --}}
                        <div class="relative w-16 h-16 rounded-full flex items-center justify-center transition-all duration-500"
                             :class="{
                                'border-2 border-green-500 bg-green-900/30':   stage.status === 'done',
                                'border-2 border-red-500   bg-red-900/30':     stage.status === 'fail',
                                'border-2 border-brand     bg-brand/10 ring-4 ring-brand/20': stage.status === 'active',
                                'border-2 border-gray-700  bg-gray-800/40':    stage.status === 'pending',
                             }">

                            {{-- Done: check --}}
                            <template x-if="stage.status === 'done'">
                                <svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </template>

                            {{-- Fail: X --}}
                            <template x-if="stage.status === 'fail'">
                                <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </template>

                            {{-- Active: pulsing icon --}}
                            <template x-if="stage.status === 'active'">
                                <div class="animate-pulse">
                                    <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            </template>

                            {{-- Pending: dot --}}
                            <template x-if="stage.status === 'pending'">
                                <div class="w-3 h-3 rounded-full bg-gray-600"></div>
                            </template>
                        </div>

                        {{-- Label --}}
                        <p class="text-xs font-bold text-center mt-2 leading-tight transition-colors duration-300"
                           :class="{
                               'text-green-400': stage.status === 'done',
                               'text-red-400':   stage.status === 'fail',
                               'text-brand':     stage.status === 'active',
                               'text-gray-500':  stage.status === 'pending',
                           }"
                           x-text="stage.label"></p>

                        {{-- Sub-label --}}
                        <p class="text-gray-600 text-xs text-center leading-tight mt-0.5 px-1"
                           x-text="stage.sub"></p>

                        {{-- Status text --}}
                        <p class="text-xs text-center mt-1 font-medium"
                           :class="{
                               'text-green-400': stage.status === 'done',
                               'text-red-400':   stage.status === 'fail',
                               'text-brand':     stage.status === 'active',
                               'text-gray-700':  stage.status === 'pending',
                           }">
                            <span x-show="stage.status === 'done'">Done</span>
                            <span x-show="stage.status === 'fail'">Failed</span>
                            <span x-show="stage.status === 'active'">Running...</span>
                            <span x-show="stage.status === 'pending'">—</span>
                        </p>

                    </div>

                    {{-- Arrow between stages --}}
                    <template x-if="i < stages.length - 1">
                        <div class="w-8 flex items-center justify-center mb-8 shrink-0">
                            <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Empty / loading state --}}
            <template x-if="!stages.length">
                <div class="flex items-center justify-center w-full py-8">
                    <div class="w-4 h-4 border-2 border-brand/40 border-t-brand rounded-full animate-spin mr-3"></div>
                    <p class="text-gray-500 text-sm">Starting pipeline...</p>
                </div>
            </template>
        </div>
    </div>

    {{-- ── Status bar ── --}}
    <div class="flex items-center justify-between px-1 pt-2 border-t border-gray-800 mt-1">
        <div class="flex items-center gap-2.5">
            <template x-if="!done">
                <div class="w-4 h-4 border-2 border-brand/40 border-t-brand rounded-full animate-spin shrink-0"></div>
            </template>
            <template x-if="done && !failed">
                <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </template>
            <template x-if="done && failed">
                <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </template>
            <span class="text-sm text-gray-400" x-text="statusMsg"></span>
        </div>

        {{-- Context-specific footer actions --}}
        @if($context === 'onboarding')
        <div class="flex items-center gap-3">
            <a x-show="done && !failed" href="{{ $onCompleteUrl }}"
               class="text-sm font-bold px-4 py-1.5 rounded-lg transition"
               class="ac-on">
                Go to dashboard →
            </a>
            <a x-show="done && failed" href="{{ $onCompleteUrl }}"
               class="text-sm font-bold px-4 py-1.5 rounded-lg bg-gray-800 text-gray-300 transition hover:bg-gray-700">
                Continue to dashboard →
            </a>
        </div>
        @else
        <div class="flex items-center gap-3">
            <a x-show="done && !failed" :href="'/transactions/' + txId"
               class="text-xs text-brand hover:underline">View full transaction →</a>
            <button @click="$dispatch('close-pipeline')"
                    class="text-xs text-gray-500 hover:text-white transition">Close</button>
        </div>
        @endif
    </div>

</div>

<script>
function pipelineTracker(initialTxId, autoStart, onCompleteUrl) {
    return {
        txId:      initialTxId,
        stages:    [],
        done:      false,
        failed:    false,
        statusMsg: 'Starting pipeline...',
        interval:  null,

        init() {
            if (autoStart && this.txId) this.start();
        },

        openWith(txId) {
            this.txId     = txId;
            this.stages   = [];
            this.done     = false;
            this.failed   = false;
            this.statusMsg = 'Starting pipeline...';
            clearInterval(this.interval);
            this.start();
        },

        close() {
            clearInterval(this.interval);
        },

        start() {
            this.fetchStatus();
            this.interval = setInterval(() => this.fetchStatus(), 2000);
            setTimeout(() => clearInterval(this.interval), 180000);
        },

        async fetchStatus() {
            if (!this.txId) return;
            try {
                const res  = await fetch('/qa/pipeline/' + this.txId, {
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (res.status === 404) {
                    // Transaction not found — may still be inserting, retry up to 10s then show error
                    this._notFoundCount = (this._notFoundCount || 0) + 1;
                    if (this._notFoundCount > 5) {
                        clearInterval(this.interval);
                        this.done = true; this.failed = true;
                        this.statusMsg = 'Could not find transaction — try refreshing';
                    }
                    return;
                }
                if (!res.ok) return;
                const data = await res.json();
                if (data.stages) this.stages = data.stages;
                if (data.done) {
                    clearInterval(this.interval);
                    this.done   = true;
                    this.failed = data.failed;
                    this.statusMsg = data.blocked
                        ? 'Billing blocked — free trial exhausted or spend cap reached'
                        : (data.failed ? 'Pipeline failed — check transaction details' : 'Pipeline complete');
                    window.dispatchEvent(new CustomEvent('ft-pipeline-done', { detail: { failed: data.failed } }));
                } else {
                    const active = data.stages?.find(s => s.status === 'active');
                    this.statusMsg = active
                        ? (active.label + ' — ' + (active.sub ?? ''))
                        : 'Processing...';
                }
            } catch(e) { console.error('pipeline poll error', e); }
        },
    };
}
</script>

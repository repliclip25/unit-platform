<x-onboarding-layout :step="$step">

@php
    $ft = $contract?->fastTrack() ?? [];
    $sampleSubject = $ft['subject'] ?? 'Domain Renewal Notice — yourdomain.com expires in 30 days';
    $sampleRaw     = $ft['raw_email'] ?? '';
@endphp

<div class="mb-8">
    <p class="text-yellow-400 text-sm font-semibold uppercase tracking-widest mb-2">Step 4 of 4</p>
    <h1 class="text-2xl font-black text-white mb-2">Run a live test</h1>
    <p class="text-gray-400">Send a sample email through your worker. Watch it read, classify, and draft a response — live.</p>
</div>

@if(session('fast_track_running'))
{{-- Pipeline is running --}}
<div id="pipeline-running">
    <div class="bg-gray-900 border border-yellow-400/20 rounded-xl px-5 py-6 mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
            <p class="text-yellow-400 font-semibold text-sm">Pipeline running...</p>
        </div>

        <div class="space-y-3" id="pipeline-stages">
            @foreach(['Reading email' => 'reading', 'Classifying' => 'classifying', 'Memory lookup' => 'memory_lookup', 'Generating draft' => 'drafting', 'Delivering draft' => 'draft_ready'] as $label => $status)
            <div class="flex items-center gap-3" id="stage-{{ $status }}">
                <div class="w-5 h-5 rounded-full border border-gray-700 flex items-center justify-center shrink-0 stage-icon">
                    <div class="w-1.5 h-1.5 bg-gray-600 rounded-full"></div>
                </div>
                <span class="text-gray-500 text-sm stage-label">{{ $label }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <p class="text-center text-gray-600 text-xs mb-6">This usually takes 20–40 seconds</p>
</div>

<div id="pipeline-complete" class="hidden">
    <div class="bg-green-500/10 border border-green-500/20 rounded-xl px-5 py-6 mb-6 text-center">
        <div class="text-4xl mb-3">🎉</div>
        <p class="text-green-400 font-bold text-lg mb-1">Your worker just ran its first pipeline!</p>
        <p class="text-gray-400 text-sm">A draft was created and is waiting in your review queue.</p>
    </div>
</div>

<div id="pipeline-failed" class="hidden">
    <div class="bg-red-500/10 border border-red-500/20 rounded-xl px-5 py-5 mb-6">
        <p class="text-red-400 font-semibold text-sm mb-1">Something went wrong with the test</p>
        <p class="text-gray-500 text-xs">This sometimes happens if your account isn't connected yet. You can still continue — try a real test from your dashboard.</p>
    </div>
</div>

<a href="{{ route('onboarding.complete') }}" id="finish-btn" class="hidden w-full block text-center bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-bold text-base py-4 rounded-xl transition-colors mb-3">
    Go to my workspace →
</a>
<a href="{{ route('onboarding.complete') }}" id="skip-finish-btn" class="block text-center text-gray-600 hover:text-gray-400 text-sm transition-colors">
    Skip test — go to dashboard
</a>

<script>
const txId = '{{ session('fast_track_running') }}';
const statusMap = {
    'reading':      ['Reading email'],
    'classifying':  ['Reading email', 'Classifying'],
    'memory_lookup':['Reading email', 'Classifying', 'Memory lookup'],
    'logging':      ['Reading email', 'Classifying', 'Memory lookup'],
    'templating':   ['Reading email', 'Classifying', 'Memory lookup'],
    'drafting':     ['Reading email', 'Classifying', 'Memory lookup', 'Generating draft'],
    'draft_ready':  ['Reading email', 'Classifying', 'Memory lookup', 'Generating draft', 'Delivering draft'],
    'failed':       null,
};

const stageKeys = ['reading', 'classifying', 'memory_lookup', 'drafting', 'draft_ready'];

function markStage(status) {
    const done = statusMap[status];
    if (!done) return;
    stageKeys.forEach((key, i) => {
        const icon = document.querySelector('#stage-' + key + ' .stage-icon');
        const label = document.querySelector('#stage-' + key + ' .stage-label');
        if (!icon) return;
        if (done.length > i + 1) {
            icon.innerHTML = '<svg class="w-3 h-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
            icon.classList.remove('border-gray-700');
            icon.classList.add('border-green-500', 'bg-green-500/10');
            label.classList.remove('text-gray-500');
            label.classList.add('text-gray-300');
        } else if (done.length === i + 1) {
            icon.innerHTML = '<div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>';
            icon.classList.remove('border-gray-700');
            icon.classList.add('border-yellow-400/50');
            label.classList.remove('text-gray-500');
            label.classList.add('text-yellow-400');
        }
    });
}

let attempts = 0;
const poll = setInterval(async () => {
    attempts++;
    if (attempts > 60) {
        clearInterval(poll);
        document.getElementById('pipeline-failed').classList.remove('hidden');
        document.getElementById('finish-btn').classList.remove('hidden');
        return;
    }
    try {
        const res = await fetch('/transactions/' + txId + '/status');
        const data = await res.json();
        const status = data.status;
        markStage(status);
        if (status === 'draft_ready' || status === 'human_review') {
            clearInterval(poll);
            setTimeout(() => {
                document.getElementById('pipeline-complete').classList.remove('hidden');
                document.getElementById('finish-btn').classList.remove('hidden');
                document.getElementById('skip-finish-btn').classList.add('hidden');
            }, 800);
        } else if (status === 'failed') {
            clearInterval(poll);
            document.getElementById('pipeline-failed').classList.remove('hidden');
            document.getElementById('finish-btn').classList.remove('hidden');
        }
    } catch(e) {}
}, 2000);
</script>

@else
{{-- Not yet run --}}
<div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-5 mb-6">
    <p class="text-gray-400 text-sm mb-3">Sample email that will be sent through the pipeline:</p>
    <div class="bg-gray-800 rounded-lg px-4 py-3 text-sm text-gray-300 leading-relaxed font-mono whitespace-pre-wrap">{{ $sampleRaw ?: $sampleSubject }}</div>
</div>

@if(!$hasCredential)
<div class="bg-yellow-400/10 border border-yellow-400/20 rounded-xl px-5 py-4 mb-5">
    <p class="text-yellow-400 text-sm font-semibold mb-1">⚠ Account not connected</p>
    <p class="text-gray-400 text-xs">The test will still run but the draft delivery step will be skipped. <a href="{{ route('onboarding.step', 3) }}" class="text-yellow-400 underline">Connect your account first</a> for a full run.</p>
</div>
@endif

<form method="POST" action="{{ route('onboarding.5') }}">
    @csrf
    <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-300 text-gray-950 font-bold text-base py-4 rounded-xl transition-colors mb-3">
        ⚡ Run live test
    </button>
</form>

<a href="{{ route('onboarding.complete') }}" class="block text-center text-gray-600 hover:text-gray-400 text-sm transition-colors">
    Skip — go straight to dashboard
</a>
@endif

</x-onboarding-layout>

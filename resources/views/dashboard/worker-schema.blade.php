<x-app-layout title="Schema — {{ $identity['name'] }}">

    @include('partials.worker-subnav')

    {{-- Header --}}
    <div class="rounded-2xl border border-gray-800 px-6 py-4 mb-6 flex items-center justify-between" style="background:#141414">
        <div class="flex items-center gap-8">
            <div>
                <p class="text-gray-600 text-xs uppercase tracking-widest mb-0.5">Worker</p>
                <p class="text-white font-semibold text-sm">{{ $identity['name'] }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-xs uppercase tracking-widest mb-0.5">Org / Platform</p>
                <p class="text-white font-semibold text-sm">{{ $org['name'] }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-xs uppercase tracking-widest mb-0.5">Channel</p>
                <span class="text-xs px-3 py-1 rounded-full font-medium"
                      style="background:rgba(59,130,246,0.15);color:#60a5fa;border:1px solid rgba(59,130,246,0.3)">
                    {{ $org['name'] }}
                </span>
            </div>
            <div>
                <p class="text-gray-600 text-xs uppercase tracking-widest mb-0.5">Version</p>
                <p class="text-gray-400 text-sm font-mono">{{ $identity['version'] }}</p>
            </div>
        </div>
        <p class="text-gray-600 text-xs">I/O Contract — defines what this worker accepts, produces, and emits.</p>
    </div>

    {{-- ── Row 1: Input + Commit ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">

        {{-- Input --}}
        <div class="rounded-2xl border border-gray-800 p-5" style="background:#141414">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold uppercase tracking-widest" style="color:#60a5fa">Input</span>
                <span class="text-gray-600 text-xs">— what triggers this worker</span>
            </div>
            <p class="text-gray-400 text-xs mb-3">{{ $input['description'] }}</p>
            <p class="text-gray-600 text-xs mb-2">Source: <span class="text-gray-500">{{ $input['source'] }}</span></p>
            <div class="rounded-xl p-3 space-y-1.5 font-mono text-xs" style="background:#0d0d0d;border:1px solid #1e1e1e">
                @foreach($input['fields'] as $f)
                <div class="flex items-start gap-2">
                    <span style="color:{{ $f['required'] ? '#60a5fa' : '#6b7280' }}">{{ $f['key'] }}</span>
                    <span class="text-gray-700">:</span>
                    <span class="text-gray-500">{{ $f['type'] }}</span>
                    <span class="text-gray-600 truncate">— {{ $f['description'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Commit --}}
        <div class="rounded-2xl border border-gray-800 p-5" style="background:#141414">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold uppercase tracking-widest" style="color:#f59e0b">Commit</span>
                <span class="text-gray-600 text-xs">— what humans or workers can inject mid-pipeline</span>
            </div>
            @if($commit === null)
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mb-3"
                         style="background:rgba(107,114,128,0.08);border:1px solid #252525">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">No injection point</p>
                    <p class="text-gray-600 text-xs mt-1 max-w-xs leading-relaxed">
                        This worker's pipeline is fully autonomous. It does not accept external commits from humans or other workers.
                    </p>
                </div>
            @else
                <p class="text-gray-400 text-xs mb-3">{{ $commit['description'] }}</p>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-xs px-2 py-0.5 rounded-full"
                          style="background:rgba(245,158,11,0.1);color:#fbbf24;border:1px solid rgba(245,158,11,0.3)">
                        source: {{ $commit['source'] }}
                    </span>
                    <span class="text-xs text-gray-500">by {{ $commit['injected_by'] }}</span>
                </div>
                <div class="rounded-xl p-3 space-y-1.5 font-mono text-xs" style="background:#0d0d0d;border:1px solid #1e1e1e">
                    @foreach($commit['fields'] as $f)
                    <div class="flex items-start gap-2">
                        <span style="color:{{ $f['required'] ? '#fbbf24' : '#6b7280' }}">{{ $f['key'] }}</span>
                        <span class="text-gray-700">:</span>
                        <span class="text-gray-500">{{ $f['type'] }}</span>
                        <span class="text-gray-600 truncate">— {{ $f['description'] }}</span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Pipeline ───────────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-gray-800 p-5 mb-4" style="background:#141414">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--accent)">Pipeline</span>
            <span class="text-gray-600 text-xs">— {{ count($pipeline) }} stages, each with typed inputs and outputs</span>
        </div>

        <div class="space-y-2">
            @foreach($pipeline as $stage)
            @php $isTerminal = $stage['connects_to'] === null; @endphp
            <div class="rounded-xl border p-4" style="background:#1a1a1a;border-color:#252525">
                <div class="flex items-start gap-4">

                    {{-- Stage number + label --}}
                    <div class="flex items-center gap-3 shrink-0" style="min-width:170px">
                        <div class="flex items-center justify-center w-7 h-7 rounded-lg text-xs font-bold shrink-0"
                             style="background:rgba(var(--accent-rgb),0.15);border:1px solid rgba(var(--accent-rgb),0.35);color:var(--accent)">
                            {{ $stage['stage'] }}
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">{{ $stage['label'] }}</p>
                            <p class="text-gray-600 text-xs">of {{ $stage['total'] }}</p>
                        </div>
                    </div>

                    {{-- Accepts --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-600 text-xs mb-1.5">← from <span class="text-gray-500">{{ $stage['receives_from'] }}</span></p>
                        <div class="flex flex-wrap gap-1">
                            @foreach($stage['accepts'] as $f)
                            <span class="text-xs px-2 py-0.5 rounded font-mono"
                                  style="background:rgba(59,130,246,0.08);color:#93c5fd;border:1px solid rgba(59,130,246,0.15)"
                                  title="{{ $f['description'] }}">{{ $f['key'] }}</span>
                            @endforeach
                        </div>
                    </div>

                    <span class="text-gray-700 shrink-0 pt-4">→</span>

                    {{-- Produces --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-gray-600 text-xs mb-1.5">produces</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach($stage['produces'] as $f)
                            <span class="text-xs px-2 py-0.5 rounded font-mono"
                                  style="background:rgba(16,185,129,0.08);color:#6ee7b7;border:1px solid rgba(16,185,129,0.15)"
                                  title="{{ $f['description'] }}">{{ $f['key'] }}</span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Emits + connects_to --}}
                    <div class="shrink-0 text-right" style="min-width:140px">
                        @if(!empty($stage['can_emit']))
                            <p class="text-gray-600 text-xs mb-1">emits</p>
                            <div class="flex flex-col gap-1 items-end">
                                @foreach($stage['can_emit'] as $ev)
                                <span class="text-xs px-1.5 py-0.5 rounded font-mono"
                                      style="background:rgba(245,158,11,0.08);color:#fbbf24;border:1px solid rgba(245,158,11,0.2)">
                                    ↗ {{ $ev }}
                                </span>
                                @endforeach
                            </div>
                        @endif
                        <p class="text-gray-700 text-xs mt-2">
                            {{ $isTerminal ? 'terminal' : '→ ' . last(explode('\\', $stage['connects_to'])) }}
                        </p>
                    </div>

                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Emit ──────────────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-gray-800 p-5" style="background:#141414">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:#f59e0b">Emit</span>
            <span class="text-gray-600 text-xs">— {{ count($emits) }} events available to downstream workers</span>
        </div>

        <div class="grid grid-cols-2 gap-3">
            @foreach($emits as $emit)
            <div class="rounded-xl border p-4" style="background:#1a1a1a;border-color:#252525">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <code class="text-xs font-mono px-2 py-0.5 rounded"
                          style="background:rgba(245,158,11,0.1);color:#fbbf24;border:1px solid rgba(245,158,11,0.25)">
                        {{ $emit['event'] }}
                    </code>
                    @if($emit['reusable'])
                    <span class="text-xs text-gray-600 shrink-0">reusable</span>
                    @endif
                </div>
                <p class="text-xs text-gray-600 mb-0.5">fired from <span class="text-gray-500">{{ $emit['fired_from'] }}</span></p>
                <p class="text-xs text-gray-600 mb-3 leading-relaxed">{{ $emit['description'] }}</p>
                <div class="rounded-lg p-2.5 space-y-1 font-mono text-xs" style="background:#0d0d0d;border:1px solid #1e1e1e">
                    @foreach($emit['fields'] as $f)
                    <div class="flex items-center gap-2">
                        <span style="color:#fbbf24">{{ $f['key'] }}</span>
                        <span class="text-gray-700">:</span>
                        <span class="text-gray-500">{{ $f['type'] }}</span>
                        <span class="text-gray-700 truncate text-xs">— {{ $f['description'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Row 4: Output ───────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-gray-800 p-5 mb-4" style="background:#141414">
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:#34d399">Output</span>
            <span class="text-gray-600 text-xs">— the final artefact this worker produces</span>
        </div>
        <p class="text-gray-400 text-xs mb-1">{{ $output['description'] }}</p>
        <div class="flex items-center gap-4 mb-3 flex-wrap">
            <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(52,211,153,0.10);color:#34d399;border:1px solid rgba(52,211,153,0.25)">
                format: {{ $output['format'] }}
            </span>
            <span class="text-xs text-gray-500">destination: <span class="text-gray-400">{{ $output['destination'] }}</span></span>
        </div>
        <div class="rounded-xl p-3 space-y-1.5 font-mono text-xs mb-3" style="background:#0d0d0d;border:1px solid #1e1e1e">
            @foreach($output['fields'] as $f)
            <div class="flex items-start gap-2">
                <span style="color:{{ $f['nullable'] ? '#6b7280' : '#34d399' }}">{{ $f['key'] }}</span>
                <span class="text-gray-700">:</span>
                <span class="text-gray-500">{{ $f['type'] }}</span>
                @if(!$f['nullable'])<span class="text-red-800 text-xs">*</span>@endif
                <span class="text-gray-600 truncate">— {{ $f['description'] }}</span>
            </div>
            @endforeach
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="rounded-lg p-3 text-xs" style="background:#0d0d0d;border:1px solid #1e1e1e">
                <p class="text-gray-600 uppercase tracking-wider mb-1">Human action</p>
                <p class="text-gray-400 leading-relaxed">{{ $output['human_action'] }}</p>
            </div>
            @if($output['auto_action'])
            <div class="rounded-lg p-3 text-xs" style="background:#0d0d0d;border:1px solid #1e1e1e">
                <p class="text-gray-600 uppercase tracking-wider mb-1">Auto action</p>
                <p class="text-gray-400 leading-relaxed">{{ $output['auto_action'] }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Row 5: Prompts ───────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-gray-800 p-5 mb-4" style="background:#141414">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:#a78bfa">Prompts</span>
            <span class="text-gray-600 text-xs">— AI instructions per pipeline stage</span>
        </div>
        <div class="space-y-3">
            @foreach($prompts as $p)
            <div class="rounded-xl p-4" style="background:#0d0d0d;border:1px solid #1e1e1e">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-300">{{ $p['label'] }}</span>
                    @if($p['uses_ai'])
                        <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(167,139,250,0.12);color:#a78bfa;border:1px solid rgba(167,139,250,0.25)">
                            AI · {{ $p['output_format'] }} · {{ $p['max_tokens'] }} tokens
                        </span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(107,114,128,0.1);color:#6b7280;border:1px solid #252525">
                            no AI
                        </span>
                    @endif
                </div>
                @if($p['uses_ai'])
                <div class="space-y-2">
                    <div>
                        <p class="text-gray-700 text-xs uppercase tracking-wider mb-1">System</p>
                        <p class="text-gray-500 text-xs font-mono leading-relaxed">{{ $p['system'] }}</p>
                    </div>
                    <div>
                        <p class="text-gray-700 text-xs uppercase tracking-wider mb-1">User template</p>
                        <p class="text-gray-500 text-xs font-mono leading-relaxed whitespace-pre-wrap">{{ Str::limit($p['user'], 300) }}</p>
                    </div>
                    @if(is_array($p['output_shape']))
                    <div>
                        <p class="text-gray-700 text-xs uppercase tracking-wider mb-1">Output keys</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($p['output_shape'] as $key)
                            <code class="text-xs px-2 py-0.5 rounded" style="background:#141414;color:#a78bfa;border:1px solid #252525">{{ $key }}</code>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div>
                        <p class="text-gray-700 text-xs uppercase tracking-wider mb-1">Output shape</p>
                        <p class="text-gray-500 text-xs leading-relaxed">{{ $p['output_shape'] }}</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Row 6: Owner ─────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-gray-800 p-5 mb-4" style="background:#141414">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:#f1d362">Owner</span>
            <span class="text-gray-600 text-xs">— who built and is responsible for this worker</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <p class="text-gray-700 text-xs uppercase tracking-wider mb-1">Name</p>
                <div class="flex items-center gap-2">
                    <p class="text-gray-300 text-sm font-semibold">{{ $owner['name'] }}</p>
                    @if($owner['verified'])
                    <span class="text-xs px-1.5 py-0.5 rounded" style="background:rgba(241,211,98,0.12);color:#f1d362;border:1px solid rgba(241,211,98,0.25)">✓ verified</span>
                    @endif
                </div>
            </div>
            <div>
                <p class="text-gray-700 text-xs uppercase tracking-wider mb-1">Type</p>
                <p class="text-gray-400 text-sm capitalize">{{ $owner['type'] }}</p>
            </div>
            <div>
                <p class="text-gray-700 text-xs uppercase tracking-wider mb-1">License</p>
                <p class="text-gray-400 text-sm">{{ $owner['license'] }}</p>
            </div>
            <div>
                <p class="text-gray-700 text-xs uppercase tracking-wider mb-1">Since</p>
                <p class="text-gray-400 text-sm">{{ $owner['since'] }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-gray-700 text-xs uppercase tracking-wider mb-1">Contact</p>
                <a href="mailto:{{ $owner['contact'] }}" class="text-gray-400 text-sm hover:text-gray-200 transition">{{ $owner['contact'] }}</a>
            </div>
            <div class="col-span-2">
                <p class="text-gray-700 text-xs uppercase tracking-wider mb-1">SLA</p>
                <p class="text-gray-400 text-sm">{{ $owner['sla'] }}</p>
            </div>
        </div>
    </div>

</x-app-layout>

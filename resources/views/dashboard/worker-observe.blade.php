<x-app-layout title="{{ $dep->name }} · Observe">

    @include('partials.worker-subnav')

    {{-- Timeframe picker --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-sm font-semibold" style="color:var(--text-primary)">Inbox Intelligence</h2>
            <p class="text-xs mt-0.5" style="color:var(--text-muted)">Raw activity, pipeline efficiency, and AI spend for this worker.</p>
        </div>
        <div class="flex items-center gap-1 rounded-lg p-1" style="background:var(--bg-surface);border:1px solid var(--border)">
            @foreach([1 => '24h', 7 => '7d', 14 => '14d', 30 => '30d'] as $d => $label)
                <a href="{{ route('workers.observe', $dep->worker_slug) }}?days={{ $d }}"
                   class="px-3 py-1 rounded text-xs font-medium transition"
                   style="{{ $days == $d ? 'background:var(--bg-raised);color:var(--text-primary)' : 'color:var(--text-muted)' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Top stat cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        @php
            $total     = $funnel->total ?? 0;
            $filtered  = $funnel->filtered_out ?? 0;
            $dismissed = $funnel->dismissed ?? 0;
            $completed = $funnel->completed ?? 0;
            $failed    = $funnel->failed ?? 0;
            $totalHits = $chartDays->sum('hits');
            $passRate  = $total > 0 ? round(($completed / $total) * 100) : 0;
            $filterRate = $total > 0 ? round((($filtered + $dismissed) / $total) * 100) : 0;
            $avgSecs   = $avgDuration ? round($avgDuration) : null;
            $avgLabel  = $avgSecs ? ($avgSecs < 60 ? "{$avgSecs}s" : round($avgSecs/60, 1).'m') : '—';
        @endphp

        <div class="rounded-xl p-4" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-xs mb-1" style="color:var(--text-muted)">Pub/Sub Hits</p>
            <p class="text-2xl font-bold" style="color:var(--text-primary)">{{ number_format($totalHits) }}</p>
            <p class="text-xs mt-1" style="color:var(--text-faint)">raw inbox signals</p>
        </div>

        <div class="rounded-xl p-4" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-xs mb-1" style="color:var(--text-muted)">Emails Ingested</p>
            <p class="text-2xl font-bold" style="color:var(--text-primary)">{{ number_format($total) }}</p>
            <p class="text-xs mt-1" style="color:var(--text-faint)">entered pipeline</p>
        </div>

        <div class="rounded-xl p-4" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-xs mb-1" style="color:var(--text-muted)">Drafted</p>
            <p class="text-2xl font-bold" style="color:var(--text-primary)">{{ number_format($completed) }}</p>
            <p class="text-xs mt-1" style="color:{{ $passRate > 0 ? 'var(--text-muted)' : 'var(--text-faint)' }}">{{ $passRate }}% pass rate</p>
        </div>

        <div class="rounded-xl p-4" style="background:var(--bg-card);border:1px solid var(--border)">
            <p class="text-xs mb-1" style="color:var(--text-muted)">Avg Duration</p>
            <p class="text-2xl font-bold" style="color:var(--text-primary)">{{ $avgLabel }}</p>
            <p class="text-xs mt-1" style="color:var(--text-faint)">per transaction</p>
        </div>
    </div>

    {{-- Chart: hits vs pipeline vs completed --}}
    <div class="rounded-xl p-5 mb-5" style="background:var(--bg-card);border:1px solid var(--border)">
        <h3 class="text-xs font-semibold mb-4" style="color:var(--text-primary)">Activity Timeline</h3>
        @php
            $maxVal = $chartDays->max(fn($d) => max($d['hits'], $d['total']));
            $maxVal = max($maxVal, 1);
        @endphp
        <div class="flex items-end gap-1.5 h-28">
            @foreach($chartDays as $day)
                @php
                    $hitH  = $maxVal > 0 ? round(($day['hits'] / $maxVal) * 100) : 0;
                    $txH   = $maxVal > 0 ? round(($day['total'] / $maxVal) * 100) : 0;
                    $doneH = $maxVal > 0 ? round(($day['completed'] / $maxVal) * 100) : 0;
                @endphp
                <div class="flex-1 flex flex-col items-center gap-0.5 group relative">
                    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 hidden group-hover:flex flex-col items-center z-10 pointer-events-none">
                        <div class="rounded px-2 py-1 text-xs whitespace-nowrap" style="background:var(--bg-raised);border:1px solid var(--border);color:var(--text-primary)">
                            <div>{{ $day['label'] }}</div>
                            <div style="color:var(--text-muted)">{{ $day['hits'] }} hits · {{ $day['total'] }} ingested · {{ $day['completed'] }} drafted</div>
                        </div>
                    </div>
                    <div class="w-full flex items-end gap-px" style="height:100px">
                        <div class="flex-1 rounded-sm opacity-30" style="height:{{ $hitH }}%;background:#6366f1;min-height:{{ $day['hits'] > 0 ? '2px' : '0' }}"></div>
                        <div class="flex-1 rounded-sm opacity-60" style="height:{{ $txH }}%;background:var(--accent);min-height:{{ $day['total'] > 0 ? '2px' : '0' }}"></div>
                        <div class="flex-1 rounded-sm" style="height:{{ $doneH }}%;background:#22c55e;min-height:{{ $day['completed'] > 0 ? '2px' : '0' }}"></div>
                    </div>
                    <span class="text-xs mt-1 opacity-0 group-hover:opacity-100 transition" style="color:var(--text-faint);font-size:9px">{{ now()->parse($day['day'])->format('d') }}</span>
                </div>
            @endforeach
        </div>
        <div class="flex items-center gap-4 mt-3">
            <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm opacity-30" style="background:#6366f1"></div><span class="text-xs" style="color:var(--text-muted)">Pub/Sub hits</span></div>
            <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm opacity-60" style="background:var(--accent)"></div><span class="text-xs" style="color:var(--text-muted)">Ingested</span></div>
            <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm" style="background:#22c55e"></div><span class="text-xs" style="color:var(--text-muted)">Drafted</span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">

        {{-- Pipeline funnel --}}
        <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
            <h3 class="text-xs font-semibold mb-4" style="color:var(--text-primary)">Pipeline Funnel</h3>
            @php
                $funnelSteps = [
                    ['label' => 'Ingested',     'value' => $total,     'color' => 'var(--accent)',  'pct' => 100],
                    ['label' => 'Passed Filter','value' => $total - $filtered, 'color' => '#818cf8', 'pct' => $total > 0 ? round((($total-$filtered)/$total)*100) : 0],
                    ['label' => 'Classified',   'value' => $total - $filtered - $dismissed, 'color' => '#38bdf8', 'pct' => $total > 0 ? round((($total-$filtered-$dismissed)/$total)*100) : 0],
                    ['label' => 'Drafted',      'value' => $completed, 'color' => '#22c55e', 'pct' => $total > 0 ? round(($completed/$total)*100) : 0],
                ];
            @endphp
            <div class="space-y-3">
                @foreach($funnelSteps as $step)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs" style="color:var(--text-muted)">{{ $step['label'] }}</span>
                            <span class="text-xs font-medium" style="color:var(--text-primary)">{{ number_format($step['value']) }}</span>
                        </div>
                        <div class="h-1.5 rounded-full" style="background:var(--bg-raised)">
                            <div class="h-1.5 rounded-full transition-all" style="width:{{ $step['pct'] }}%;background:{{ $step['color'] }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 grid grid-cols-3 gap-2" style="border-top:1px solid var(--border)">
                <div class="text-center">
                    <p class="text-xs font-semibold" style="color:var(--text-primary)">{{ $filtered }}</p>
                    <p class="text-xs" style="color:var(--text-faint)">filtered</p>
                </div>
                <div class="text-center">
                    <p class="text-xs font-semibold" style="color:var(--text-primary)">{{ $dismissed }}</p>
                    <p class="text-xs" style="color:var(--text-faint)">dismissed</p>
                </div>
                <div class="text-center">
                    <p class="text-xs font-semibold" style="color:#f87171">{{ $failed }}</p>
                    <p class="text-xs" style="color:var(--text-faint)">failed</p>
                </div>
            </div>
        </div>

        {{-- AI spend by stage --}}
        <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
            <h3 class="text-xs font-semibold mb-4" style="color:var(--text-primary)">AI Spend by Stage</h3>
            @if($stageSpend->isEmpty())
                <p class="text-xs" style="color:var(--text-faint)">No AI usage recorded in this period.</p>
            @else
                @php $maxCost = $stageSpend->max('cost') ?: 1; @endphp
                <div class="space-y-3">
                    @foreach($stageSpend as $s)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-mono" style="color:var(--text-muted)">{{ $s->stage }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs" style="color:var(--text-faint)">{{ $s->calls }}x</span>
                                    <span class="text-xs font-medium" style="color:var(--text-primary)">${{ number_format($s->cost, 4) }}</span>
                                </div>
                            </div>
                            <div class="h-1.5 rounded-full" style="background:var(--bg-raised)">
                                <div class="h-1.5 rounded-full" style="width:{{ round(($s->cost/$maxCost)*100) }}%;background:var(--accent)"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 flex items-center justify-between" style="border-top:1px solid var(--border)">
                    <span class="text-xs" style="color:var(--text-muted)">Total this period</span>
                    <span class="text-sm font-bold" style="color:var(--text-primary)">${{ number_format($stageSpend->sum('cost'), 4) }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Connected inboxes --}}
    <div class="rounded-xl p-5" style="background:var(--bg-card);border:1px solid var(--border)">
        <h3 class="text-xs font-semibold mb-3" style="color:var(--text-primary)">Monitored Inboxes</h3>
        @forelse($inboxes as $inbox)
            <div class="flex items-center justify-between py-2" style="border-bottom:1px solid var(--border-subtle)">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-green-400"></div>
                    <span class="text-sm font-mono" style="color:var(--text-primary)">{{ $inbox->gmail_address }}</span>
                </div>
                @php
                    $inboxHits = $chartDays->sum('hits');
                @endphp
                <span class="text-xs" style="color:var(--text-muted)">{{ $inboxHits }} hits this period</span>
            </div>
        @empty
            <p class="text-xs" style="color:var(--text-faint)">No inboxes connected.</p>
        @endforelse
    </div>

</x-app-layout>

{{--
    @include('partials.policy-violations', ['violations' => $violations, 'context' => 'billing|worker'])
    $violations = PolicyEngine::evaluate($userId, $deploymentId)
--}}

@if(!empty($violations))
@php
    $colorMap = [
        'red'   => ['bg'=>'rgba(127,29,29,0.15)',  'border'=>'rgba(239,68,68,0.35)',  'icon_bg'=>'rgba(239,68,68,0.15)',  'icon'=>'#ef4444', 'title'=>'#fca5a5', 'text'=>'#fda4af', 'step'=>'#7f1d1d'],
        'amber' => ['bg'=>'rgba(120,53,15,0.15)',   'border'=>'rgba(245,158,11,0.35)', 'icon_bg'=>'rgba(245,158,11,0.12)', 'icon'=>'#f59e0b', 'title'=>'#fcd34d', 'text'=>'#fde68a', 'step'=>'#78350f'],
        'gray'  => ['bg'=>'rgba(55,65,81,0.2)',     'border'=>'rgba(107,114,128,0.3)', 'icon_bg'=>'rgba(107,114,128,0.1)','icon'=>'#6b7280', 'title'=>'#d1d5db', 'text'=>'#9ca3af', 'step'=>'#374151'],
    ];

    $severityIcon = [
        'hard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>',
        'soft' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
    ];
@endphp

<div class="space-y-3">
@foreach($violations as $v)
    @php
        $c = $colorMap[$v['color']] ?? $colorMap['gray'];
        $deploymentId = $v['context']['deployment_id'] ?? null;
    @endphp
    <div class="rounded-2xl border p-5" style="background:{{ $c['bg'] }};border-color:{{ $c['border'] }}">
        <div class="flex items-start gap-4">

            {{-- Icon --}}
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                 style="background:{{ $c['icon_bg'] }}">
                <svg class="w-5 h-5" fill="none" stroke="{{ $c['icon'] }}" viewBox="0 0 24 24">
                    {!! $severityIcon[$v['severity']] !!}
                </svg>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-0.5">
                    <p class="text-sm font-bold" style="color:{{ $c['title'] }}">{{ $v['title'] }}</p>
                    <span class="text-xs px-1.5 py-0.5 rounded font-mono"
                          style="background:{{ $c['icon_bg'] }};color:{{ $c['icon'] }}">
                        {{ $v['code'] }}
                    </span>
                    <span class="text-xs px-1.5 py-0.5 rounded"
                          style="background:rgba(0,0,0,0.2);color:{{ $c['text'] }}">
                        {{ $v['level'] === 'platform' ? 'Platform-wide' : 'This worker' }}
                        · {{ $v['severity'] === 'hard' ? 'Hard block' : 'Soft block' }}
                    </span>
                </div>

                <p class="text-xs mb-3" style="color:{{ $c['text'] }}">{{ $v['description'] }}</p>

                {{-- Context details --}}
                @if(!empty($v['context']['reason']))
                    <p class="text-xs mb-3 px-3 py-2 rounded-lg" style="background:rgba(0,0,0,0.2);color:{{ $c['text'] }}">
                        <strong>Admin note:</strong> {{ $v['context']['reason'] }}
                    </p>
                @endif
                @if(!empty($v['context']['spent']) && !empty($v['context']['cap']))
                    <p class="text-xs mb-3 font-mono" style="color:{{ $c['text'] }}">
                        ${{ number_format($v['context']['spent'], 4) }} spent of ${{ number_format($v['context']['cap'], 2) }} cap
                        @if(!empty($v['context']['resets_on']))— resets {{ $v['context']['resets_on'] }}@endif
                    </p>
                @endif
                @if(!empty($v['context']['used']) && isset($v['context']['limit']))
                    <p class="text-xs mb-3 font-mono" style="color:{{ $c['text'] }}">
                        {{ $v['context']['used'] }} / {{ $v['context']['limit'] }} trial transactions used
                    </p>
                @endif

                {{-- Resolution steps --}}
                <div class="mb-4">
                    <p class="text-xs font-semibold mb-2" style="color:{{ $c['title'] }}">How to resolve:</p>
                    <ol class="space-y-1">
                        @foreach($v['resolution'] as $i => $step)
                        <li class="flex items-start gap-2 text-xs" style="color:{{ $c['text'] }}">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5"
                                  style="background:{{ $c['step'] }};color:{{ $c['icon'] }}">{{ $i + 1 }}</span>
                            {{ $step }}
                        </li>
                        @endforeach
                    </ol>
                </div>

                {{-- CTA --}}
                @if($v['self_service'])
                    @php
                        $href = $v['cta_url']
                            ?? ($v['cta_route']
                                ? (($v['cta_route'] === 'billing.checkout' && $deploymentId)
                                    ? route('billing.checkout', $deploymentId)
                                    : (($v['cta_route'] === 'workers.show' && $deploymentId)
                                        ? route('workers.show', $deploymentId)
                                        : route($v['cta_route'])))
                                : '#');
                    @endphp
                    <a href="{{ $href }}"
                       class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-lg transition"
                       style="background:{{ $c['icon_bg'] }};color:{{ $c['title'] }};border:1px solid {{ $c['border'] }}"
                       {{ str_starts_with($href, 'mailto:') ? 'target="_blank"' : '' }}>
                        {{ $v['cta_label'] }} →
                    </a>
                @else
                    <a href="mailto:support@unit.app"
                       class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-lg transition"
                       style="background:{{ $c['icon_bg'] }};color:{{ $c['title'] }};border:1px solid {{ $c['border'] }}"
                       target="_blank">
                        {{ $v['cta_label'] }} →
                    </a>
                @endif
            </div>

        </div>
    </div>
@endforeach
</div>
@endif

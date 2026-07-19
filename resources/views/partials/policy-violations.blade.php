{{--
    @include('partials.policy-violations', ['violations' => $violations, 'context' => 'billing|worker'])
    $violations = PolicyEngine::evaluate($userId, $deploymentId)

    Styled with inline styles only (no Tailwind utility classes) — this partial is
    included both from x-app-layout pages (Tailwind loaded) and standalone UX2
    pages (no Tailwind at all, e.g. billing.blade.php). Inline styles render
    identically in both contexts.
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

<div>
@foreach($violations as $v)
    @php
        $c = $colorMap[$v['color']] ?? $colorMap['gray'];
        $deploymentId = $v['context']['deployment_id'] ?? null;
    @endphp
    <div style="border-radius:16px;border:1px solid {{ $c['border'] }};padding:20px;background:{{ $c['bg'] }};{{ $loop->last ? '' : 'margin-bottom:12px' }}">
        <div style="display:flex;align-items:flex-start;gap:16px">

            {{-- Icon --}}
            <div style="width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:{{ $c['icon_bg'] }}">
                <svg style="width:20px;height:20px" fill="none" stroke="{{ $c['icon'] }}" viewBox="0 0 24 24">
                    {!! $severityIcon[$v['severity']] !!}
                </svg>
            </div>

            {{-- Content --}}
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px">
                    <p style="font-size:14px;font-weight:700;color:var(--db-text, {{ $c['title'] }})">{{ $v['title'] }}</p>
                    <span style="font-size:11px;padding:2px 6px;border-radius:6px;font-family:monospace;background:{{ $c['icon_bg'] }};color:{{ $c['icon'] }}">
                        {{ $v['code'] }}
                    </span>
                    <span style="font-size:11px;padding:2px 6px;border-radius:6px;background:var(--db-chip, rgba(0,0,0,0.2));color:var(--db-text-muted, {{ $c['text'] }})">
                        {{ $v['level'] === 'platform' ? 'Platform-wide' : 'This worker' }}
                        · {{ $v['severity'] === 'hard' ? 'Hard block' : 'Soft block' }}
                    </span>
                </div>

                <p style="font-size:12px;margin-bottom:12px;color:var(--db-text-muted, {{ $c['text'] }})">{{ $v['description'] }}</p>

                {{-- Context details --}}
                @if(!empty($v['context']['reason']))
                    <p style="font-size:12px;margin-bottom:12px;padding:8px 12px;border-radius:10px;background:var(--db-chip, rgba(0,0,0,0.2));color:var(--db-text-muted, {{ $c['text'] }})">
                        <strong>Admin note:</strong> {{ $v['context']['reason'] }}
                    </p>
                @endif
                @if(!empty($v['context']['spent']) && !empty($v['context']['cap']))
                    <p style="font-size:12px;margin-bottom:12px;font-family:monospace;color:var(--db-text-muted, {{ $c['text'] }})">
                        ${{ number_format($v['context']['spent'], 4) }} spent of ${{ number_format($v['context']['cap'], 2) }} cap
                        @if(!empty($v['context']['resets_on']))— resets {{ $v['context']['resets_on'] }}@endif
                    </p>
                @endif
                @if(!empty($v['context']['used']) && isset($v['context']['limit']))
                    <p style="font-size:12px;margin-bottom:12px;font-family:monospace;color:var(--db-text-muted, {{ $c['text'] }})">
                        {{ $v['context']['used'] }} / {{ $v['context']['limit'] }} trial transactions used
                    </p>
                @endif

                {{-- Resolution steps --}}
                <div style="margin-bottom:16px">
                    <p style="font-size:12px;font-weight:600;margin-bottom:8px;color:var(--db-text, {{ $c['title'] }})">How to resolve:</p>
                    <ol>
                        @foreach($v['resolution'] as $i => $step)
                        <li style="display:flex;align-items:flex-start;gap:8px;font-size:12px;margin-top:4px;color:var(--db-text-muted, {{ $c['text'] }})">
                            <span style="width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;margin-top:2px;background:{{ $c['step'] }};color:{{ $c['icon'] }}">{{ $i + 1 }}</span>
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
                                ? match(true) {
                                    $v['cta_route'] === 'billing' && $deploymentId
                                        => route('billing') . '?pick=' . $deploymentId,
                                    $v['cta_route'] === 'billing.checkout' && $deploymentId
                                        => route('billing') . '?pick=' . $deploymentId,
                                    $v['cta_route'] === 'workers.show' && $deploymentId
                                        => route('workers.show', $deploymentId),
                                    default => route($v['cta_route']),
                                }
                                : '#');
                    @endphp
                    <a href="{{ $href }}"
                       style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;padding:8px 16px;border-radius:10px;text-decoration:none;background:{{ $c['icon_bg'] }};color:var(--db-text, {{ $c['title'] }});border:1px solid {{ $c['border'] }}"
                       {{ str_starts_with($href, 'mailto:') ? 'target="_blank"' : '' }}>
                        {{ $v['cta_label'] }} →
                    </a>
                @else
                    <a href="mailto:support@unit.app"
                       style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;padding:8px 16px;border-radius:10px;text-decoration:none;background:{{ $c['icon_bg'] }};color:var(--db-text, {{ $c['title'] }});border:1px solid {{ $c['border'] }}"
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

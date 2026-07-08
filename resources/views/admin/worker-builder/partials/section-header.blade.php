@php
$sectionId  = 'wb-section-' . $label;
$isOpen     = $open ?? false;
$filledCount = $filled ?? null;
$totalCount  = $total ?? null;
$statusStr   = $status ?? null;
@endphp
<div onclick="toggleSection('{{ $sectionId }}')"
     style="display:flex;align-items:center;gap:12px;margin:20px 0 0;padding:12px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;cursor:pointer;user-select:none;transition:border-color .15s"
     id="{{ $sectionId }}-header"
     onmouseover="this.style.borderColor='rgba(255,255,255,.2)'" onmouseout="this.style.borderColor='var(--border)'">

    {{-- Number badge --}}
    <div style="width:26px;height:26px;border-radius:50%;background:var(--accent);color:#000;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ $label }}</div>

    {{-- Title + desc --}}
    <div style="flex:1;min-width:0">
        <span style="font-size:14px;font-weight:700;color:var(--text-primary)">{{ $title }}</span>
        <span style="font-size:11px;color:var(--text-muted);margin-left:8px">{{ $desc }}</span>
    </div>

    {{-- Completion indicator --}}
    @if($statusStr)
    <span style="font-size:11px;font-weight:600;color:var(--text-muted);flex-shrink:0">{{ $statusStr }}</span>
    @elseif($filledCount !== null && $totalCount !== null)
    @php
        $pct = $totalCount > 0 ? round($filledCount / $totalCount * 100) : 0;
        $indicatorColor = $pct >= 100 ? '#4ade80' : ($pct >= 50 ? '#142C74' : '#94a3b8');
    @endphp
    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0">
        <div style="width:48px;height:4px;background:var(--bg-raised);border-radius:2px;overflow:hidden">
            <div style="width:{{ $pct }}%;height:100%;background:{{ $indicatorColor }};border-radius:2px;transition:width .3s"></div>
        </div>
        <span style="font-size:11px;font-weight:600;color:{{ $indicatorColor }};min-width:32px">{{ $filledCount }}/{{ $totalCount }}</span>
    </div>
    @endif

    {{-- Chevron --}}
    <svg id="{{ $sectionId }}-chevron" style="width:16px;height:16px;color:var(--text-muted);flex-shrink:0;transition:transform .2s;{{ $isOpen ? 'transform:rotate(180deg)' : '' }}"
         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
    </svg>
</div>

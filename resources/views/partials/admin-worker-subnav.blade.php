@php
    $tabs = [
        ['label' => 'Edit',      'route' => 'admin.workers.edit',     'active' => 'edit'],
        ['label' => 'Personas',  'route' => 'admin.workers.personas', 'active' => 'personas'],
        ['label' => 'Rules',     'route' => 'admin.workers.rules',    'active' => 'rules'],
    ];
@endphp
<div class="flex items-center gap-0.5 mb-6 overflow-x-auto" style="border-bottom:1px solid var(--border);scrollbar-width:none">
    @foreach($tabs as $tab)
    <a href="{{ route($tab['route'], $slug) }}"
       class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px whitespace-nowrap transition"
       style="color:{{ ($active ?? '') === $tab['active'] ? 'var(--text-primary)' : 'var(--text-muted)' }};border-color:{{ ($active ?? '') === $tab['active'] ? 'var(--accent)' : 'transparent' }}">
        {{ $tab['label'] }}
    </a>
    @endforeach
    <div class="ml-auto flex items-center gap-3 pb-2 pl-4">
        <span class="font-mono text-xs text-gray-600">{{ $slug }}</span>
        <a href="{{ route('admin.workers.index') }}" class="text-xs text-gray-600 hover:text-gray-400 transition">← All workers</a>
    </div>
</div>

@php
    $tabs = [
        ['label' => 'Overview',   'route' => 'workers.show',      'match' => 'workers.show'],
        ['label' => 'Memory',     'route' => 'workers.memory',     'match' => 'workers.memory*'],
        ['label' => 'Templates',  'route' => 'workers.templates',  'match' => 'workers.templates*'],
        ['label' => 'Rules',      'route' => 'workers.rules',      'match' => 'workers.rules*'],
        ['label' => 'Connect',    'route' => 'workers.connect',    'match' => 'workers.connect'],
        ['label' => 'Configure',  'route' => 'workers.configure',  'match' => 'workers.configure'],
        ['label' => 'Log',        'route' => 'workers.log',        'match' => 'workers.log'],
        ['label' => 'Billing',    'route' => 'workers.billing',    'match' => 'workers.billing'],
    ];
@endphp
<div class="flex items-center gap-0.5 mb-6 overflow-x-auto" style="border-bottom:1px solid var(--border);scrollbar-width:none">
    @foreach($tabs as $tab)
        <a href="{{ route($tab['route'], $dep->worker_slug) }}"
           class="px-3 sm:px-4 py-2 text-sm font-medium border-b-2 -mb-px whitespace-nowrap transition {{ request()->routeIs($tab['match']) ? 'border-brand' : 'border-transparent' }}"
           style="color:{{ request()->routeIs($tab['match']) ? 'var(--text-primary)' : 'var(--text-muted)' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
    <div class="ml-auto hidden sm:flex items-center gap-3 pb-2 pl-4 shrink-0">
        <span class="text-xs {{ $dep->status === 'active' ? 'text-green-400' : 'text-yellow-400' }}">● {{ ucfirst($dep->status) }}</span>
        <span class="text-xs" style="color:var(--text-faint)">·</span>
        <span class="text-xs" style="color:var(--text-muted)">{{ $dep->worker_slug }}</span>
        <span class="text-xs" style="color:var(--text-faint)">·</span>
        <a href="{{ route('workers.show', $dep->worker_slug) }}#fast-track"
           class="flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg transition hover:opacity-90"
           class="ac-on">
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Fast Track
        </a>
    </div>
</div>

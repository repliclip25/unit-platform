@php
    $tabs = [
        ['label' => 'Overview',   'route' => 'workers.show',      'match' => 'workers.show'],
        ['label' => 'Memory',     'route' => 'workers.memory',     'match' => 'workers.memory*'],
        ['label' => 'Templates',  'route' => 'workers.templates',  'match' => 'workers.templates*'],
        ['label' => 'Rules',      'route' => 'workers.rules',      'match' => 'workers.rules*'],
        ['label' => 'Connect',    'route' => 'workers.connect',    'match' => 'workers.connect'],
        ['label' => 'Configure',  'route' => 'workers.configure',  'match' => 'workers.configure'],
        ['label' => 'Log',        'route' => 'workers.log',        'match' => 'workers.log'],
        ['label' => 'Schema',     'route' => 'workers.schema',     'match' => 'workers.schema'],
        ['label' => 'Billing',    'route' => 'workers.billing',    'match' => 'workers.billing'],
    ];
@endphp
<div class="flex items-center gap-1 mb-6 border-b border-gray-800 overflow-x-auto">
    @foreach($tabs as $tab)
        <a href="{{ route($tab['route'], $dep->id) }}"
           class="px-4 py-2 text-sm font-medium border-b-2 -mb-px whitespace-nowrap {{ request()->routeIs($tab['match']) ? 'text-white border-brand' : 'text-gray-500 border-transparent hover:text-white' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
    <div class="ml-auto flex items-center gap-2 pb-2 pl-4 shrink-0">
        <span class="text-xs {{ $dep->status === 'active' ? 'text-green-400' : 'text-yellow-400' }}">● {{ ucfirst($dep->status) }}</span>
        <span class="text-gray-700 text-xs">·</span>
        <span class="text-gray-500 text-xs">{{ $dep->worker_slug }}</span>
    </div>
</div>

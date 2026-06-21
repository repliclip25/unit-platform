<x-app-layout title="Workers">

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-3 gap-6">

        {{-- Deployed workers list --}}
        <div class="col-span-2 space-y-3">
            <h2 class="text-white font-semibold text-sm mb-3">Deployed Workers</h2>

            @forelse($deployments as $dep)
                @php
                    $config = json_decode($dep->config, true) ?? [];
                    $statusColor = match($dep->status) {
                        'active' => 'text-green-400 bg-green-900 border-green-800',
                        'paused' => 'text-yellow-400 bg-yellow-900 border-yellow-800',
                        default  => 'text-gray-500 bg-gray-800 border-gray-700',
                    };
                    $dotColor = match($dep->status) {
                        'active' => 'bg-green-400',
                        'paused' => 'bg-yellow-400',
                        default  => 'bg-gray-500',
                    };
                @endphp
                <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-brand/15 rounded-lg flex items-center justify-center">
                                <span class="text-brand font-bold text-sm">{{ strtoupper(substr($dep->worker_slug, 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="text-white font-medium text-sm">{{ $dep->name }}</p>
                                <p class="text-gray-500 text-xs">{{ $dep->worker_slug }} · deployed {{ \Carbon\Carbon::parse($dep->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 text-xs px-2 py-1 rounded border {{ $statusColor }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }} {{ $dep->status === 'active' ? 'animate-pulse' : '' }}"></span>
                                {{ ucfirst($dep->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <p class="text-gray-600">Capture scope</p>
                            <p class="text-gray-300 mt-0.5">{{ $config['capture_scope'] ?? 'All emails' }}</p>
                        </div>
                        @if(!empty($config['capture_keywords']))
                        <div>
                            <p class="text-gray-600">Keywords</p>
                            <p class="text-gray-300 mt-0.5">{{ implode(', ', $config['capture_keywords']) }}</p>
                        </div>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center gap-3 pt-4 border-t border-gray-800">
                        <a href="{{ route('workers.show', $dep->id) }}" class="text-xs text-brand hover:text-brand">Configure →</a>
                        <div class="flex items-center gap-2 ml-auto">
                            @if($dep->status === 'active')
                                <form method="POST" action="{{ route('workers.status', $dep->id) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="paused">
                                    <button class="text-xs text-yellow-500 hover:text-yellow-400 border border-yellow-800 rounded px-2 py-1">Pause</button>
                                </form>
                            @elseif($dep->status === 'paused')
                                <form method="POST" action="{{ route('workers.status', $dep->id) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="active">
                                    <button class="text-xs text-green-500 hover:text-green-400 border border-green-800 rounded px-2 py-1">Resume</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('workers.destroy', $dep->id) }}" onsubmit="return confirm('Remove this worker?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-gray-600 hover:text-red-400">Remove</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-gray-900 border border-dashed border-gray-700 rounded-xl p-10 text-center">
                    <p class="text-gray-500 text-sm">No workers deployed yet.</p>
                    <p class="text-gray-600 text-xs mt-1">Deploy a worker from the catalog →</p>
                </div>
            @endforelse
        </div>

        {{-- Deploy new worker — respects WorkerContract::instances() --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl h-fit">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="text-white text-sm font-semibold">Deploy a Worker</h3>
            </div>

            @php
                // Check each catalog worker against its contract instances() limits
                $deployableWorkers = collect($catalog)->filter(function($worker) use ($contracts, $deploymentCounts) {
                    $contract = $contracts->get($worker->slug);
                    if (!$contract) return true; // no contract = no restriction
                    $inst  = $contract->instances();
                    $count = $deploymentCounts->get($worker->slug, 0);
                    if (!$inst['multiple'] && $count >= 1) return false;
                    if ($inst['max'] !== null && $count >= $inst['max']) return false;
                    return true;
                });
            @endphp

            @if($deployableWorkers->isEmpty())
                <div class="px-5 py-8 text-center">
                    <p class="text-gray-500 text-sm">All available workers are already deployed.</p>
                    <p class="text-gray-600 text-xs mt-1">New worker types will appear here when added to the marketplace.</p>
                </div>
            @else
            <form method="POST" action="{{ route('workers.store') }}" class="px-5 py-4 space-y-4" id="deploy-form">
                @csrf

                <div>
                    <label class="text-gray-400 text-xs block mb-1">Worker</label>
                    <select name="worker_slug" required id="deploy-worker-slug"
                            class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand"
                            onchange="updateDeployHint(this.value)">
                        @foreach($deployableWorkers as $worker)
                        @php
                            $c     = $contracts->get($worker->slug);
                            $inst  = $c ? $c->instances() : null;
                            $count = $deploymentCounts->get($worker->slug, 0);
                            $label = $inst ? "({$count}" . ($inst['max'] ? '/'.$inst['max'] : '') . ' deployed)' : '';
                        @endphp
                            <option value="{{ $worker->slug }}"
                                    data-hint="{{ $inst ? $inst['rationale'] : '' }}"
                                    data-label="{{ $inst ? $inst['label'] : '' }}"
                                    data-connect-route="{{ $c ? ($c->credential()['connect_route'] ?? '') : '' }}"
                                    data-authorize-route="{{ $c ? ($c->credential()['authorize_route'] ?? '') : '' }}"
                                    data-credential-label="{{ $c ? ($c->credential()['label'] ?? 'Account') : 'Account' }}">
                                {{ $worker->name }} {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p id="deploy-worker-hint" class="text-gray-600 text-xs mt-1"></p>
                </div>

                <div>
                    <label class="text-gray-400 text-xs block mb-1">Deployment Name</label>
                    <input type="text" name="name" placeholder="e.g. AVA — Renewals" required
                           class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                </div>

                <div>
                    <label id="deploy-cred-label" class="text-gray-400 text-xs block mb-1">Account</label>
                    <select name="credential_id"
                            class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                        <option value="">— connect later —</option>
                        @foreach($credentials as $cred)
                            <option value="{{ $cred->id }}">{{ $cred->gmail_address }}</option>
                        @endforeach
                    </select>
                    @if($credentials->isEmpty())
                        <p class="text-gray-600 text-xs mt-1" id="deploy-no-cred">
                            No account connected yet.
                            <a href="#" id="deploy-connect-link" class="text-brand hover:underline">Connect one →</a>
                        </p>
                    @endif
                </div>

                <div>
                    <label class="text-gray-400 text-xs block mb-1">Capture Scope</label>
                    <input type="text" name="capture_scope" value="All incoming emails"
                           class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                </div>

                <div>
                    <label class="text-gray-400 text-xs block mb-1">Capture Keywords <span class="text-gray-600">(comma-separated)</span></label>
                    <input type="text" name="capture_keywords" placeholder="renew, invoice, expires, subscription"
                           class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                </div>

                <button type="submit" class="w-full bg-brand hover:bg-brand-deep text-brand-text text-sm rounded-lg py-2 transition">
                    Deploy Worker
                </button>
            </form>
            @endif
        </div>

        <script>
        function updateDeployHint(slug) {
            const sel    = document.getElementById('deploy-worker-slug');
            const opt    = sel.options[sel.selectedIndex];
            const hint   = document.getElementById('deploy-worker-hint');
            const label  = document.getElementById('deploy-cred-label');
            const link   = document.getElementById('deploy-connect-link');
            const authorizeRoute = opt.dataset.authorizeRoute;

            if (hint)  hint.textContent  = opt.dataset.hint || '';
            if (label) label.textContent = opt.dataset.credentialLabel || 'Account';
            if (link && authorizeRoute) link.href = '/' + authorizeRoute.replace('.', '/');
        }
        document.addEventListener('DOMContentLoaded', () => {
            const sel = document.getElementById('deploy-worker-slug');
            if (sel) updateDeployHint(sel.value);
        });
        </script>

    </div>

</x-app-layout>

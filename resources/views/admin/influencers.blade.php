<x-app-layout>
<div class="py-8 px-4 sm:px-6 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-primary)">Influencer Partners</h1>
            <p class="text-sm mt-0.5" style="color:var(--text-muted)">Revenue-share partners driving growth via vanity links</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('influencer.apply') }}" target="_blank"
               class="px-4 py-2 rounded-lg text-sm font-medium border"
               style="border-color:var(--border-subtle);color:var(--text-secondary)">
                View Apply Page ↗
            </a>
            <a href="{{ route('admin.tenants') }}"
               class="px-4 py-2 rounded-lg text-sm font-medium"
               style="background:var(--bg-raised);color:var(--text-secondary)">
                ← Tenants
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium" style="background:rgba(34,197,94,0.15);color:#16a34a;border:1px solid rgba(34,197,94,0.3)">
            {{ session('success') }}
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $total      = $stats->count();
            $active     = $stats->where('status','active')->count();
            $pending    = $stats->where('status','pending')->count();
            $totalEarned = $stats->sum('total_earned');
            $pendingPayout = $stats->sum('pending_payout');
        @endphp
        <div class="rounded-xl p-4" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
            <div class="text-2xl font-bold" style="color:var(--text-primary)">{{ $total }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-muted)">Total Partners</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
            <div class="text-2xl font-bold text-green-500">{{ $active }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-muted)">Active</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
            <div class="text-2xl font-bold" style="color:#f3c531">{{ $pending }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-muted)">Pending Review</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
            <div class="text-2xl font-bold" style="color:var(--text-primary)">${{ number_format($pendingPayout, 2) }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-muted)">Pending Payout</div>
        </div>
    </div>

    {{-- Influencer Table --}}
    <div class="rounded-xl overflow-hidden" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
        <table class="w-full text-sm">
            <thead>
                <tr style="border-bottom:1px solid var(--border-subtle)">
                    <th class="text-left px-4 py-3 font-medium" style="color:var(--text-muted)">Influencer</th>
                    <th class="text-left px-4 py-3 font-medium" style="color:var(--text-muted)">Channel</th>
                    <th class="text-left px-4 py-3 font-medium" style="color:var(--text-muted)">Slug</th>
                    <th class="text-center px-4 py-3 font-medium" style="color:var(--text-muted)">Status</th>
                    <th class="text-center px-4 py-3 font-medium" style="color:var(--text-muted)">Tier</th>
                    <th class="text-right px-4 py-3 font-medium" style="color:var(--text-muted)">Clicks</th>
                    <th class="text-right px-4 py-3 font-medium" style="color:var(--text-muted)">Conv.</th>
                    <th class="text-right px-4 py-3 font-medium" style="color:var(--text-muted)">Earned</th>
                    <th class="text-right px-4 py-3 font-medium" style="color:var(--text-muted)">Pending</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats as $inf)
                <tr style="border-bottom:1px solid var(--border-subtle)" class="hover:bg-white/5 transition-colors">
                    <td class="px-4 py-3">
                        <div class="font-medium" style="color:var(--text-primary)">{{ $inf->name }}</div>
                        <div class="text-xs" style="color:var(--text-muted)">{{ $inf->email }}</div>
                    </td>
                    <td class="px-4 py-3" style="color:var(--text-secondary)">
                        {{ $inf->channel ?? '—' }}
                        @if($inf->audience_size)
                            <span class="text-xs" style="color:var(--text-muted)">({{ $inf->audience_size }})</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <code class="text-xs px-2 py-0.5 rounded" style="background:var(--bg-raised);color:#f3c531">/r/{{ $inf->slug }}</code>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $sc = match($inf->status) {
                                'active'   => 'text-green-500 bg-green-500/10',
                                'pending'  => 'text-yellow-400 bg-yellow-400/10',
                                'paused'   => 'text-blue-400 bg-blue-400/10',
                                'rejected' => 'text-red-400 bg-red-400/10',
                                default    => 'text-gray-400 bg-gray-400/10',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">{{ ucfirst($inf->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $tc = match($inf->tier) {
                                'elite'   => 'text-yellow-400',
                                'pro'     => 'text-blue-400',
                                default   => 'text-gray-400',
                            };
                        @endphp
                        <span class="text-xs font-semibold {{ $tc }}">{{ ucfirst($inf->tier) }}</span>
                    </td>
                    <td class="px-4 py-3 text-right font-mono text-sm" style="color:var(--text-secondary)">{{ number_format($inf->clicks) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm" style="color:var(--text-secondary)">{{ $inf->conversions }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm text-green-500">${{ number_format($inf->total_earned, 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm" style="color:#f3c531">${{ number_format($inf->pending_payout, 2) }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.influencers.show', $inf->id) }}"
                           class="px-3 py-1 rounded text-xs font-medium"
                           style="background:var(--bg-raised);color:var(--text-secondary)">
                            Manage →
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-12 text-center" style="color:var(--text-muted)">
                        No influencer partners yet. Share the <a href="{{ route('influencer.apply') }}" class="underline" style="color:#f3c531">application page</a> to get started.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</x-app-layout>

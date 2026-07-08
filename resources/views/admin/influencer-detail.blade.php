<x-app-layout>
<div class="py-8 px-4 sm:px-6 max-w-6xl mx-auto" x-data="{ payoutOpen: false }">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.influencers') }}" class="text-sm" style="color:var(--text-muted)">← Influencers</a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold" style="color:var(--text-primary)">{{ $influencer->name }}</h1>
                @php
                    $sc = match($influencer->status) {
                        'active'   => 'text-green-500 bg-green-500/10',
                        'pending'  => 'text-yellow-400 bg-yellow-400/10',
                        'paused'   => 'text-blue-400 bg-blue-400/10',
                        'rejected' => 'text-red-400 bg-red-400/10',
                        default    => 'text-gray-400 bg-gray-400/10',
                    };
                @endphp
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc }}">{{ ucfirst($influencer->status) }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ match($influencer->tier) { 'elite' => 'text-yellow-400 bg-yellow-400/10', 'pro' => 'text-blue-400 bg-blue-400/10', default => 'text-gray-400 bg-gray-400/10' } }}">{{ ucfirst($influencer->tier) }}</span>
            </div>
            <div class="flex items-center gap-4 mt-1 text-sm" style="color:var(--text-muted)">
                <span>{{ $influencer->email }}</span>
                <span>·</span>
                <span>{{ $influencer->channel ?? 'No channel' }}</span>
                @if($influencer->audience_size)
                    <span>·</span>
                    <span>{{ $influencer->audience_size }} audience</span>
                @endif
                <span>·</span>
                <code class="text-xs px-2 py-0.5 rounded" style="background:var(--bg-raised);color:var(--text-secondary);border:1px solid var(--border)">
                    {{ url('/r/' . $influencer->slug) }}
                </code>
            </div>
        </div>
        @if($influencer->status === 'pending')
        <form action="{{ route('admin.influencers.approve', $influencer->id) }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-bold" style="background:var(--accent);color:#ffffff">
                Approve & Activate
            </button>
        </form>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg text-sm font-medium" style="background:rgba(34,197,94,0.15);color:#16a34a;border:1px solid rgba(34,197,94,0.3)">
            {{ session('success') }}
        </div>
    @endif

    {{-- KPI Bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="rounded-xl p-4" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
            <div class="text-2xl font-bold" style="color:var(--text-primary)">{{ number_format($stats->clicks) }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-muted)">Total Clicks</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
            <div class="text-2xl font-bold" style="color:var(--text-primary)">{{ $stats->signups }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-muted)">Signups</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
            <div class="text-2xl font-bold text-green-500">{{ $stats->converted }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-muted)">Paid Conversions</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
            <div class="text-2xl font-bold" style="color:var(--text-primary)">${{ number_format($stats->mrr, 2) }}</div>
            <div class="text-xs mt-0.5" style="color:var(--text-muted)">MRR Attributed</div>
        </div>
        <div class="rounded-xl p-4" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
            <div class="text-2xl font-bold" style="color:var(--text-primary)">{{ $stats->convRate }}%</div>
            <div class="text-xs mt-0.5" style="color:var(--text-muted)">Click→Convert</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Earnings + Payout --}}
        <div class="space-y-4">

            {{-- Earnings Summary --}}
            <div class="rounded-xl p-5" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
                <h3 class="text-sm font-semibold mb-4" style="color:var(--text-muted)">Earnings</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span style="color:var(--text-secondary)">Total Earned</span>
                        <span class="font-mono font-semibold text-green-500">${{ number_format($stats->earned, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color:var(--text-secondary)">Pending Payout</span>
                        <span class="font-mono font-semibold" style="color:var(--text-primary)">${{ number_format($stats->pending, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color:var(--text-secondary)">Paid Out</span>
                        <span class="font-mono font-semibold" style="color:var(--text-secondary)">${{ number_format($stats->paidOut, 2) }}</span>
                    </div>
                    <div class="pt-3" style="border-top:1px solid var(--border-subtle)">
                        <div class="text-xs mb-1" style="color:var(--text-muted)">Commission Rate</div>
                        <div class="text-xl font-bold" style="color:var(--text-primary)">{{ round($influencer->commission_rate * 100) }}%</div>
                        <div class="text-xs" style="color:var(--text-muted)">of MRR per active referral / month</div>
                    </div>
                    <div class="pt-2">
                        <div class="text-xs mb-1" style="color:var(--text-muted)">Payout via</div>
                        <div class="capitalize font-medium" style="color:var(--text-secondary)">{{ $influencer->payout_method }}</div>
                        <div class="text-xs" style="color:var(--text-muted)">{{ $influencer->payout_email ?? 'No payout email set' }}</div>
                    </div>
                </div>

                @if($stats->pending > 0)
                <div class="mt-4">
                    <button @click="payoutOpen = !payoutOpen"
                            class="w-full py-2 rounded-lg text-sm font-bold"
                            style="background:var(--accent);color:#ffffff">
                        Record Payout
                    </button>
                </div>
                <div x-show="payoutOpen" x-cloak class="mt-3">
                    <form action="{{ route('admin.influencers.payout', $influencer->id) }}" method="POST" class="space-y-2">
                        @csrf
                        <input type="number" name="amount" step="0.01" min="0.01" max="{{ $stats->pending }}"
                               value="{{ $stats->pending }}"
                               class="w-full px-3 py-2 rounded-lg text-sm"
                               style="background:var(--bg-raised);color:var(--text-primary);border:1px solid var(--border-subtle)" />
                        <button type="submit" class="w-full py-2 rounded-lg text-sm font-medium text-green-500"
                                style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3)">
                            Confirm Payout
                        </button>
                    </form>
                </div>
                @endif
            </div>

            {{-- Settings --}}
            <div class="rounded-xl p-5" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
                <h3 class="text-sm font-semibold mb-4" style="color:var(--text-muted)">Settings</h3>
                <form action="{{ route('admin.influencers.update', $influencer->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs" style="color:var(--text-muted)">Status</label>
                        <select name="status" class="w-full mt-1 px-3 py-2 rounded-lg text-sm"
                                style="background:var(--bg-raised);color:var(--text-primary);border:1px solid var(--border-subtle)">
                            @foreach(['pending','active','paused','rejected'] as $s)
                                <option value="{{ $s }}" @selected($influencer->status === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs" style="color:var(--text-muted)">Tier</label>
                        <select name="tier" class="w-full mt-1 px-3 py-2 rounded-lg text-sm"
                                style="background:var(--bg-raised);color:var(--text-primary);border:1px solid var(--border-subtle)">
                            @foreach(['starter','pro','elite'] as $t)
                                <option value="{{ $t }}" @selected($influencer->tier === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs" style="color:var(--text-muted)">Commission Rate (e.g. 0.25 = 25%)</label>
                        <input type="number" name="commission_rate" step="0.01" min="0.01" max="0.50"
                               value="{{ $influencer->commission_rate }}"
                               class="w-full mt-1 px-3 py-2 rounded-lg text-sm"
                               style="background:var(--bg-raised);color:var(--text-primary);border:1px solid var(--border-subtle)" />
                    </div>
                    <div>
                        <label class="text-xs" style="color:var(--text-muted)">Payout Email</label>
                        <input type="email" name="payout_email" value="{{ $influencer->payout_email }}"
                               placeholder="paypal@example.com"
                               class="w-full mt-1 px-3 py-2 rounded-lg text-sm"
                               style="background:var(--bg-raised);color:var(--text-primary);border:1px solid var(--border-subtle)" />
                    </div>
                    <div>
                        <label class="text-xs" style="color:var(--text-muted)">Payout Method</label>
                        <select name="payout_method" class="w-full mt-1 px-3 py-2 rounded-lg text-sm"
                                style="background:var(--bg-raised);color:var(--text-primary);border:1px solid var(--border-subtle)">
                            @foreach(['paypal','bank','stripe'] as $m)
                                <option value="{{ $m }}" @selected($influencer->payout_method === $m)>{{ ucfirst($m) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs" style="color:var(--text-muted)">Admin Notes</label>
                        <textarea name="notes" rows="3" placeholder="Internal notes..."
                                  class="w-full mt-1 px-3 py-2 rounded-lg text-sm resize-none"
                                  style="background:var(--bg-raised);color:var(--text-primary);border:1px solid var(--border-subtle)">{{ $influencer->notes }}</textarea>
                    </div>
                    <button type="submit" class="w-full py-2 rounded-lg text-sm font-bold"
                            style="background:var(--accent);color:#ffffff">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>

        {{-- Right: Activity --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Monthly breakdown --}}
            @if($stats->monthly->count())
            <div class="rounded-xl p-5" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
                <h3 class="text-sm font-semibold mb-4" style="color:var(--text-muted)">Monthly Performance</h3>
                <div class="space-y-2">
                    @foreach($stats->monthly as $m)
                    <div class="flex items-center gap-3">
                        <div class="w-20 text-xs font-mono" style="color:var(--text-muted)">{{ $m->month }}</div>
                        <div class="flex-1 rounded-full h-2" style="background:var(--bg-raised)">
                            @php $maxComm = $stats->monthly->max('commission') ?: 1; @endphp
                            <div class="h-2 rounded-full" style="width:{{ min(100, ($m->commission/$maxComm)*100) }}%;background:var(--accent)"></div>
                        </div>
                        <div class="text-xs w-16 text-right font-mono text-green-500">${{ number_format($m->commission, 2) }}</div>
                        <div class="text-xs w-12 text-right" style="color:var(--text-muted)">{{ $m->conversions }}x</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Credit log --}}
            <div class="rounded-xl overflow-hidden" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
                <div class="px-5 py-4" style="border-bottom:1px solid var(--border-subtle)">
                    <h3 class="text-sm font-semibold" style="color:var(--text-muted)">Referral Events</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border-subtle)">
                                <th class="text-left px-4 py-2 text-xs font-medium" style="color:var(--text-muted)">Date</th>
                                <th class="text-left px-4 py-2 text-xs font-medium" style="color:var(--text-muted)">Event</th>
                                <th class="text-right px-4 py-2 text-xs font-medium" style="color:var(--text-muted)">MRR</th>
                                <th class="text-right px-4 py-2 text-xs font-medium" style="color:var(--text-muted)">Commission</th>
                                <th class="text-center px-4 py-2 text-xs font-medium" style="color:var(--text-muted)">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($credits as $c)
                            <tr style="border-bottom:1px solid var(--border-subtle)">
                                <td class="px-4 py-2 text-xs font-mono" style="color:var(--text-muted)">{{ \Carbon\Carbon::parse($c->created_at)->format('M d, Y') }}</td>
                                <td class="px-4 py-2 text-xs font-medium" style="color:var(--text-secondary)">{{ ucfirst(str_replace('_',' ',$c->event)) }}</td>
                                <td class="px-4 py-2 text-xs font-mono text-right" style="color:var(--text-secondary)">${{ number_format($c->mrr_attributed, 2) }}</td>
                                <td class="px-4 py-2 text-xs font-mono text-right text-green-500">${{ number_format($c->credit_usd, 2) }}</td>
                                <td class="px-4 py-2 text-center">
                                    <span class="px-2 py-0.5 rounded text-xs {{ match($c->status) { 'paid' => 'text-green-500 bg-green-500/10', 'pending_payout' => 'text-yellow-400 bg-yellow-400/10', default => 'text-gray-400 bg-gray-400/10' } }}">{{ ucfirst($c->status) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-sm" style="color:var(--text-muted)">No events yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent clicks --}}
            <div class="rounded-xl overflow-hidden" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
                <div class="px-5 py-4" style="border-bottom:1px solid var(--border-subtle)">
                    <h3 class="text-sm font-semibold" style="color:var(--text-muted)">Recent Clicks</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border-subtle)">
                                <th class="text-left px-4 py-2 text-xs font-medium" style="color:var(--text-muted)">Date</th>
                                <th class="text-left px-4 py-2 text-xs font-medium" style="color:var(--text-muted)">Source</th>
                                <th class="text-left px-4 py-2 text-xs font-medium" style="color:var(--text-muted)">Campaign</th>
                                <th class="text-center px-4 py-2 text-xs font-medium" style="color:var(--text-muted)">Converted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clicks as $cl)
                            <tr style="border-bottom:1px solid var(--border-subtle)">
                                <td class="px-4 py-2 text-xs font-mono" style="color:var(--text-muted)">{{ \Carbon\Carbon::parse($cl->created_at)->format('M d, g:ia') }}</td>
                                <td class="px-4 py-2 text-xs" style="color:var(--text-secondary)">{{ $cl->utm_source ?? 'direct' }}</td>
                                <td class="px-4 py-2 text-xs" style="color:var(--text-secondary)">{{ $cl->utm_campaign ?? '—' }}</td>
                                <td class="px-4 py-2 text-center">
                                    @if($cl->converted)
                                        <span class="text-xs text-green-500">✓</span>
                                    @else
                                        <span class="text-xs" style="color:var(--text-muted)">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-sm" style="color:var(--text-muted)">No clicks recorded yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>
</x-app-layout>

<x-app-layout title="Platform Token Usage">
<div class="max-w-5xl space-y-8">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-white">Platform Token Usage</h1>
      <p class="text-gray-500 text-sm mt-1">Platform AI spend (top) and tenant worker spend (bottom) — tracked separately.</p>
    </div>
    <a href="{{ route('admin.prompts') }}" class="text-xs text-gray-500 hover:text-white transition px-3 py-2 rounded-lg border border-gray-800 hover:border-gray-600">← Prompts</a>
  </div>

  {{-- ── Platform Usage ───────────────────────────────────────────────────────── --}}
  <div>
    <div class="flex items-center gap-3 mb-4">
      <div class="text-xs font-bold uppercase tracking-widest text-gray-500">Platform Prompts</div>
      <div class="flex-1 border-t border-gray-800"></div>
    </div>

    <div class="grid grid-cols-4 gap-4 mb-6">
      @foreach([
        ['Total Calls',  number_format($totals->calls ?? 0)],
        ['Tokens In',    number_format($totals->tokens_in ?? 0)],
        ['Tokens Out',   number_format($totals->tokens_out ?? 0)],
        ['Total Cost',   '$' . number_format($totals->total_cost ?? 0, 4)],
      ] as [$label, $value])
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <div class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-2">{{ $label }}</div>
        <div class="text-white text-2xl font-bold font-mono">{{ $value }}</div>
      </div>
      @endforeach
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-800">
        <div class="text-white text-sm font-semibold">Usage by Prompt</div>
      </div>
      @if($byKey->isEmpty())
        <div class="px-6 py-10 text-center text-gray-600 text-sm">No platform usage recorded yet.</div>
      @else
      <table class="w-full text-sm">
        <thead>
          <tr class="text-gray-600 text-xs border-b border-gray-800">
            <th class="text-left px-6 py-3 font-semibold uppercase tracking-wider">Prompt Key</th>
            <th class="text-right px-6 py-3 font-semibold uppercase tracking-wider">Calls</th>
            <th class="text-right px-6 py-3 font-semibold uppercase tracking-wider">Tokens In</th>
            <th class="text-right px-6 py-3 font-semibold uppercase tracking-wider">Tokens Out</th>
            <th class="text-right px-6 py-3 font-semibold uppercase tracking-wider">Cost (USD)</th>
            <th class="text-right px-6 py-3 font-semibold uppercase tracking-wider">Last Used</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
          @foreach($byKey as $row)
          <tr class="hover:bg-gray-800/30 transition">
            <td class="px-6 py-3 font-mono text-xs text-yellow-400">{{ $row->prompt_key }}</td>
            <td class="px-6 py-3 text-right text-gray-300">{{ number_format($row->calls) }}</td>
            <td class="px-6 py-3 text-right text-gray-400 font-mono text-xs">{{ number_format($row->tokens_in) }}</td>
            <td class="px-6 py-3 text-right text-gray-400 font-mono text-xs">{{ number_format($row->tokens_out) }}</td>
            <td class="px-6 py-3 text-right text-gray-300 font-mono text-xs">${{ number_format($row->total_cost, 6) }}</td>
            <td class="px-6 py-3 text-right text-gray-600 text-xs">{{ \Carbon\Carbon::parse($row->last_used)->diffForHumans() }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @endif
    </div>
  </div>

  {{-- ── Tenant / Worker Usage ────────────────────────────────────────────────── --}}
  <div>
    <div class="flex items-center gap-3 mb-4">
      <div class="text-xs font-bold uppercase tracking-widest text-gray-500">Tenant Workers</div>
      <div class="flex-1 border-t border-gray-800"></div>
    </div>

    <div class="grid grid-cols-4 gap-4 mb-6">
      @foreach([
        ['Total Calls',  number_format($tenantTotals->calls ?? 0)],
        ['Tokens In',    number_format($tenantTotals->tokens_in ?? 0)],
        ['Tokens Out',   number_format($tenantTotals->tokens_out ?? 0)],
        ['Total Cost',   '$' . number_format($tenantTotals->total_cost ?? 0, 4)],
      ] as [$label, $value])
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <div class="text-gray-500 text-xs uppercase tracking-widest font-semibold mb-2">{{ $label }}</div>
        <div class="text-white text-2xl font-bold font-mono">{{ $value }}</div>
      </div>
      @endforeach
    </div>

    <div class="grid grid-cols-2 gap-4">

      {{-- By Tenant --}}
      <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800">
          <div class="text-white text-sm font-semibold">By Tenant</div>
        </div>
        @if($byTenant->isEmpty())
          <div class="px-6 py-10 text-center text-gray-600 text-sm">No tenant usage yet.</div>
        @else
        <table class="w-full text-sm">
          <thead>
            <tr class="text-gray-600 text-xs border-b border-gray-800">
              <th class="text-left px-5 py-3 font-semibold uppercase tracking-wider">Tenant</th>
              <th class="text-right px-5 py-3 font-semibold uppercase tracking-wider">Calls</th>
              <th class="text-right px-5 py-3 font-semibold uppercase tracking-wider">Cost</th>
              <th class="text-right px-5 py-3 font-semibold uppercase tracking-wider">Last</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-800">
            @foreach($byTenant as $row)
            <tr class="hover:bg-gray-800/30 transition">
              <td class="px-5 py-3">
                <div class="text-gray-200 text-xs font-medium truncate max-w-[140px]">{{ $row->name }}</div>
                <div class="text-gray-600 text-xs truncate max-w-[140px]">{{ $row->email }}</div>
              </td>
              <td class="px-5 py-3 text-right text-gray-400 text-xs">{{ number_format($row->calls) }}</td>
              <td class="px-5 py-3 text-right text-gray-300 font-mono text-xs">${{ number_format($row->total_cost, 4) }}</td>
              <td class="px-5 py-3 text-right text-gray-600 text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($row->last_used)->diffForHumans() }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>

      {{-- By Worker --}}
      <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800">
          <div class="text-white text-sm font-semibold">By Worker</div>
        </div>
        @if($byWorker->isEmpty())
          <div class="px-6 py-10 text-center text-gray-600 text-sm">No worker usage yet.</div>
        @else
        <table class="w-full text-sm">
          <thead>
            <tr class="text-gray-600 text-xs border-b border-gray-800">
              <th class="text-left px-5 py-3 font-semibold uppercase tracking-wider">Worker</th>
              <th class="text-right px-5 py-3 font-semibold uppercase tracking-wider">Calls</th>
              <th class="text-right px-5 py-3 font-semibold uppercase tracking-wider">Tokens In</th>
              <th class="text-right px-5 py-3 font-semibold uppercase tracking-wider">Tokens Out</th>
              <th class="text-right px-5 py-3 font-semibold uppercase tracking-wider">Cost</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-800">
            @foreach($byWorker as $row)
            <tr class="hover:bg-gray-800/30 transition">
              <td class="px-5 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-xs uppercase"
                  style="background:rgba(241,211,98,0.12);color:#f1d362">{{ $row->worker_slug }}</span>
              </td>
              <td class="px-5 py-3 text-right text-gray-400 text-xs">{{ number_format($row->calls) }}</td>
              <td class="px-5 py-3 text-right text-gray-400 font-mono text-xs">{{ number_format($row->tokens_in) }}</td>
              <td class="px-5 py-3 text-right text-gray-400 font-mono text-xs">{{ number_format($row->tokens_out) }}</td>
              <td class="px-5 py-3 text-right text-gray-300 font-mono text-xs">${{ number_format($row->total_cost, 4) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>

    </div>
  </div>

  {{-- ── Platform Recent Events ───────────────────────────────────────────────── --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-800">
      <div class="text-white text-sm font-semibold">Recent Platform Events <span class="text-gray-600 font-normal text-xs">(last 50)</span></div>
    </div>
    @if($recent->isEmpty())
      <div class="px-6 py-10 text-center text-gray-600 text-sm">No events yet.</div>
    @else
    <table class="w-full text-sm">
      <thead>
        <tr class="text-gray-600 text-xs border-b border-gray-800">
          <th class="text-left px-6 py-3 font-semibold uppercase tracking-wider">Time</th>
          <th class="text-left px-6 py-3 font-semibold uppercase tracking-wider">Prompt Key</th>
          <th class="text-left px-6 py-3 font-semibold uppercase tracking-wider">Triggered By</th>
          <th class="text-left px-6 py-3 font-semibold uppercase tracking-wider">Model</th>
          <th class="text-right px-6 py-3 font-semibold uppercase tracking-wider">In</th>
          <th class="text-right px-6 py-3 font-semibold uppercase tracking-wider">Out</th>
          <th class="text-right px-6 py-3 font-semibold uppercase tracking-wider">Cost</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        @foreach($recent as $e)
        <tr class="hover:bg-gray-800/30 transition">
          <td class="px-6 py-2.5 text-gray-600 text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($e->created_at)->format('M j, g:ia') }}</td>
          <td class="px-6 py-2.5 font-mono text-xs text-yellow-400">{{ $e->prompt_key }}</td>
          <td class="px-6 py-2.5 text-gray-500 text-xs">{{ $e->triggered_by ?? '—' }}</td>
          <td class="px-6 py-2.5 text-gray-600 text-xs font-mono">{{ $e->model }}</td>
          <td class="px-6 py-2.5 text-right text-gray-400 font-mono text-xs">{{ number_format($e->tokens_input) }}</td>
          <td class="px-6 py-2.5 text-right text-gray-400 font-mono text-xs">{{ number_format($e->tokens_output) }}</td>
          <td class="px-6 py-2.5 text-right text-gray-400 font-mono text-xs">${{ number_format($e->cost_usd, 6) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>

</div>
</x-app-layout>

<x-app-layout title="AI Spend">
<div class="space-y-8">

  {{-- ── Header ──────────────────────────────────────────────────────────────── --}}
  <div>
    <h1 class="text-xl font-bold" style="color:var(--text-primary)">AI Spend</h1>
    <p class="text-sm mt-1" style="color:var(--text-secondary)">Every token spent on this platform — platform prompts and tenant worker calls — tracked to the individual call.</p>
    <div class="mt-3 rounded-xl px-4 py-3 flex gap-3" style="background:var(--bg-raised);border:1px solid var(--border)">
      <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--text-faint)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <p class="text-xs leading-relaxed" style="color:var(--text-faint)">
        <strong style="color:var(--text-secondary)">How this is tracked:</strong>
        Platform AI calls (blog rewrites, worker request follow-ups, etc.) write to <code class="font-mono text-xs px-1 py-0.5 rounded" style="background:var(--bg-card)">platform_usage_events</code> keyed by <code class="font-mono text-xs px-1 py-0.5 rounded" style="background:var(--bg-card)">prompt_key</code>.
        Tenant worker calls write to <code class="font-mono text-xs px-1 py-0.5 rounded" style="background:var(--bg-card)">usage_events</code> tagged with <code class="font-mono text-xs px-1 py-0.5 rounded" style="background:var(--bg-card)">worker_slug</code>, <code class="font-mono text-xs px-1 py-0.5 rounded" style="background:var(--bg-card)">stage</code>, and <code class="font-mono text-xs px-1 py-0.5 rounded" style="background:var(--bg-card)">tx_id</code>.
        Every worker inherits this via <code class="font-mono text-xs px-1 py-0.5 rounded" style="background:var(--bg-card)">ClaudeService</code> — stage names come from the worker's <code class="font-mono text-xs px-1 py-0.5 rounded" style="background:var(--bg-card)">prompts()</code> contract method and will be linkable to AI endpoints as the platform grows.
      </p>
    </div>
  </div>

  {{-- ── Platform spend ───────────────────────────────────────────────────────── --}}
  <div>
    <div class="flex items-center gap-3 mb-4">
      <div class="text-xs font-bold uppercase tracking-widest" style="color:var(--text-faint)">Platform Prompts</div>
      <div class="flex-1 h-px" style="background:var(--border)"></div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
      @foreach([
        ['Total Calls',  number_format($totals->calls ?? 0)],
        ['Tokens In',    number_format($totals->tokens_in ?? 0)],
        ['Tokens Out',   number_format($totals->tokens_out ?? 0)],
        ['Total Cost',   '$' . number_format($totals->total_cost ?? 0, 4)],
      ] as [$label, $value])
      <div class="rounded-2xl p-4" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="text-xs uppercase tracking-widest font-semibold mb-1.5" style="color:var(--text-faint)">{{ $label }}</div>
        <div class="text-xl font-bold font-mono" style="color:var(--text-primary)">{{ $value }}</div>
      </div>
      @endforeach
    </div>

    <div class="rounded-2xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
      <div class="px-5 py-4" style="border-bottom:1px solid var(--border)">
        <div class="text-sm font-semibold" style="color:var(--text-primary)">Usage by Prompt Key</div>
      </div>
      @if($byKey->isEmpty())
        <div class="px-6 py-10 text-center text-sm" style="color:var(--text-faint)">No platform usage recorded yet.</div>
      @else
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[560px]">
          <thead>
            <tr class="text-xs uppercase tracking-wider" style="color:var(--text-faint);border-bottom:1px solid var(--border)">
              <th class="text-left px-5 py-3 font-semibold">Prompt Key</th>
              <th class="text-right px-5 py-3 font-semibold">Calls</th>
              <th class="text-right px-5 py-3 font-semibold">Tokens In</th>
              <th class="text-right px-5 py-3 font-semibold">Tokens Out</th>
              <th class="text-right px-5 py-3 font-semibold">Cost</th>
              <th class="text-right px-5 py-3 font-semibold">Last Used</th>
            </tr>
          </thead>
          <tbody>
            @foreach($byKey as $row)
            <tr class="transition" style="border-bottom:1px solid var(--border-subtle)">
              <td class="px-5 py-3 font-mono text-xs" style="color:#f1d362">{{ $row->prompt_key }}</td>
              <td class="px-5 py-3 text-right text-xs" style="color:var(--text-secondary)">{{ number_format($row->calls) }}</td>
              <td class="px-5 py-3 text-right font-mono text-xs" style="color:var(--text-muted)">{{ number_format($row->tokens_in) }}</td>
              <td class="px-5 py-3 text-right font-mono text-xs" style="color:var(--text-muted)">{{ number_format($row->tokens_out) }}</td>
              <td class="px-5 py-3 text-right font-mono text-xs" style="color:var(--text-secondary)">${{ number_format($row->total_cost, 6) }}</td>
              <td class="px-5 py-3 text-right text-xs whitespace-nowrap" style="color:var(--text-faint)">{{ \Carbon\Carbon::parse($row->last_used)->diffForHumans() }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- ── Tenant spend ─────────────────────────────────────────────────────────── --}}
  <div>
    <div class="flex items-center gap-3 mb-4">
      <div class="text-xs font-bold uppercase tracking-widest" style="color:var(--text-faint)">Tenant Workers</div>
      <div class="flex-1 h-px" style="background:var(--border)"></div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
      @foreach([
        ['Total Calls',  number_format($tenantTotals->calls ?? 0)],
        ['Tokens In',    number_format($tenantTotals->tokens_in ?? 0)],
        ['Tokens Out',   number_format($tenantTotals->tokens_out ?? 0)],
        ['Total Cost',   '$' . number_format($tenantTotals->total_cost ?? 0, 4)],
      ] as [$label, $value])
      <div class="rounded-2xl p-4" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="text-xs uppercase tracking-widest font-semibold mb-1.5" style="color:var(--text-faint)">{{ $label }}</div>
        <div class="text-xl font-bold font-mono" style="color:var(--text-primary)">{{ $value }}</div>
      </div>
      @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

      {{-- By Tenant --}}
      <div class="rounded-2xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4" style="border-bottom:1px solid var(--border)">
          <div class="text-sm font-semibold" style="color:var(--text-primary)">By Tenant</div>
        </div>
        @if($byTenant->isEmpty())
          <div class="px-6 py-10 text-center text-sm" style="color:var(--text-faint)">No tenant usage yet.</div>
        @else
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[320px]">
            <thead>
              <tr class="text-xs uppercase tracking-wider" style="color:var(--text-faint);border-bottom:1px solid var(--border)">
                <th class="text-left px-5 py-3 font-semibold">Tenant</th>
                <th class="text-right px-5 py-3 font-semibold">Calls</th>
                <th class="text-right px-5 py-3 font-semibold">Cost</th>
                <th class="text-right px-5 py-3 font-semibold">Last</th>
              </tr>
            </thead>
            <tbody>
              @foreach($byTenant as $row)
              <tr style="border-bottom:1px solid var(--border-subtle)">
                <td class="px-5 py-3">
                  <div class="text-xs font-medium truncate max-w-[140px]" style="color:var(--text-secondary)">{{ $row->name }}</div>
                  <div class="text-xs truncate max-w-[140px]" style="color:var(--text-faint)">{{ $row->email }}</div>
                </td>
                <td class="px-5 py-3 text-right text-xs" style="color:var(--text-muted)">{{ number_format($row->calls) }}</td>
                <td class="px-5 py-3 text-right font-mono text-xs" style="color:var(--text-secondary)">${{ number_format($row->total_cost, 4) }}</td>
                <td class="px-5 py-3 text-right text-xs whitespace-nowrap" style="color:var(--text-faint)">{{ \Carbon\Carbon::parse($row->last_used)->diffForHumans() }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>

      {{-- By Worker --}}
      <div class="rounded-2xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
        <div class="px-5 py-4" style="border-bottom:1px solid var(--border)">
          <div class="text-sm font-semibold" style="color:var(--text-primary)">By Worker</div>
        </div>
        @if($byWorker->isEmpty())
          <div class="px-6 py-10 text-center text-sm" style="color:var(--text-faint)">No worker usage yet.</div>
        @else
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[320px]">
            <thead>
              <tr class="text-xs uppercase tracking-wider" style="color:var(--text-faint);border-bottom:1px solid var(--border)">
                <th class="text-left px-5 py-3 font-semibold">Worker</th>
                <th class="text-right px-5 py-3 font-semibold">Calls</th>
                <th class="text-right px-5 py-3 font-semibold">Tokens</th>
                <th class="text-right px-5 py-3 font-semibold">Cost</th>
              </tr>
            </thead>
            <tbody>
              @foreach($byWorker as $row)
              <tr style="border-bottom:1px solid var(--border-subtle)">
                <td class="px-5 py-3">
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase" style="background:rgba(241,211,98,0.12);color:#f1d362">{{ $row->worker_slug }}</span>
                </td>
                <td class="px-5 py-3 text-right text-xs" style="color:var(--text-muted)">{{ number_format($row->calls) }}</td>
                <td class="px-5 py-3 text-right font-mono text-xs" style="color:var(--text-muted)">{{ number_format($row->tokens_in + $row->tokens_out) }}</td>
                <td class="px-5 py-3 text-right font-mono text-xs" style="color:var(--text-secondary)">${{ number_format($row->total_cost, 4) }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>

    </div>
  </div>

  {{-- ── Microscopic: by stage ────────────────────────────────────────────────── --}}
  <div>
    <div class="flex items-center gap-3 mb-4">
      <div class="text-xs font-bold uppercase tracking-widest" style="color:var(--text-faint)">By Pipeline Stage</div>
      <div class="flex-1 h-px" style="background:var(--border)"></div>
      <span class="text-xs px-2 py-0.5 rounded-full font-semibold" style="background:rgba(241,211,98,0.12);color:#f1d362">Microscopic</span>
    </div>
    <p class="text-xs mb-4" style="color:var(--text-faint)">
      Every AI call broken down by the pipeline stage that triggered it. Stage names are declared in each worker's <code class="font-mono px-1 py-0.5 rounded" style="background:var(--bg-raised)">prompts()</code> contract method and will be linked to their endpoint definitions as the platform matures.
    </p>

    <div class="rounded-2xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
      @if($byStage->isEmpty())
        <div class="px-6 py-10 text-center text-sm" style="color:var(--text-faint)">No stage-level data yet — stages are tagged as worker pipelines run.</div>
      @else
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[600px]">
          <thead>
            <tr class="text-xs uppercase tracking-wider" style="color:var(--text-faint);border-bottom:1px solid var(--border)">
              <th class="text-left px-5 py-3 font-semibold">Worker</th>
              <th class="text-left px-5 py-3 font-semibold">Stage</th>
              <th class="text-right px-5 py-3 font-semibold">Calls</th>
              <th class="text-right px-5 py-3 font-semibold">Tokens In</th>
              <th class="text-right px-5 py-3 font-semibold">Tokens Out</th>
              <th class="text-right px-5 py-3 font-semibold">Total Cost</th>
              <th class="text-right px-5 py-3 font-semibold">Avg/Call</th>
              <th class="text-right px-5 py-3 font-semibold">Last Used</th>
            </tr>
          </thead>
          <tbody>
            @foreach($byStage as $row)
            <tr style="border-bottom:1px solid var(--border-subtle)">
              <td class="px-5 py-2.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase" style="background:rgba(241,211,98,0.12);color:#f1d362">{{ $row->worker_slug }}</span>
              </td>
              <td class="px-5 py-2.5 font-mono text-xs" style="color:var(--text-secondary)">{{ $row->stage ?? '—' }}</td>
              <td class="px-5 py-2.5 text-right text-xs" style="color:var(--text-muted)">{{ number_format($row->calls) }}</td>
              <td class="px-5 py-2.5 text-right font-mono text-xs" style="color:var(--text-faint)">{{ number_format($row->tokens_in) }}</td>
              <td class="px-5 py-2.5 text-right font-mono text-xs" style="color:var(--text-faint)">{{ number_format($row->tokens_out) }}</td>
              <td class="px-5 py-2.5 text-right font-mono text-xs" style="color:var(--text-primary)">${{ number_format($row->total_cost, 6) }}</td>
              <td class="px-5 py-2.5 text-right font-mono text-xs" style="color:var(--text-muted)">${{ number_format($row->avg_cost, 6) }}</td>
              <td class="px-5 py-2.5 text-right text-xs whitespace-nowrap" style="color:var(--text-faint)">{{ \Carbon\Carbon::parse($row->last_used)->diffForHumans() }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- ── Atom-level call log ──────────────────────────────────────────────────── --}}
  <div>
    <div class="flex items-center gap-3 mb-4">
      <div class="text-xs font-bold uppercase tracking-widest" style="color:var(--text-faint)">Call Log</div>
      <div class="flex-1 h-px" style="background:var(--border)"></div>
      <span class="text-xs" style="color:var(--text-faint)">last 100 calls</span>
    </div>

    <div class="rounded-2xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
      @if($callLog->isEmpty())
        <div class="px-6 py-10 text-center text-sm" style="color:var(--text-faint)">No tenant calls recorded yet.</div>
      @else
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[640px]">
          <thead>
            <tr class="text-xs uppercase tracking-wider" style="color:var(--text-faint);border-bottom:1px solid var(--border)">
              <th class="text-left px-5 py-3 font-semibold">Time</th>
              <th class="text-left px-5 py-3 font-semibold">Tenant</th>
              <th class="text-left px-5 py-3 font-semibold">Worker</th>
              <th class="text-left px-5 py-3 font-semibold">Stage</th>
              <th class="text-left px-5 py-3 font-semibold">TX</th>
              <th class="text-right px-5 py-3 font-semibold">In</th>
              <th class="text-right px-5 py-3 font-semibold">Out</th>
              <th class="text-right px-5 py-3 font-semibold">Cost</th>
            </tr>
          </thead>
          <tbody>
            @foreach($callLog as $e)
            <tr style="border-bottom:1px solid var(--border-subtle)">
              <td class="px-5 py-2 text-xs whitespace-nowrap" style="color:var(--text-faint)">{{ \Carbon\Carbon::parse($e->created_at)->format('M j, g:ia') }}</td>
              <td class="px-5 py-2 text-xs" style="color:var(--text-muted)">{{ $e->tenant_name }}</td>
              <td class="px-5 py-2">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold uppercase" style="background:rgba(241,211,98,0.10);color:#f1d362">{{ $e->worker_slug }}</span>
              </td>
              <td class="px-5 py-2 font-mono text-xs" style="color:var(--text-secondary)">{{ $e->stage ?? '—' }}</td>
              <td class="px-5 py-2 font-mono text-xs truncate max-w-[80px]" style="color:var(--text-faint)" title="{{ $e->tx_id }}">{{ $e->tx_id ? substr($e->tx_id, 0, 8) . '…' : '—' }}</td>
              <td class="px-5 py-2 text-right font-mono text-xs" style="color:var(--text-faint)">{{ number_format($e->tokens_input) }}</td>
              <td class="px-5 py-2 text-right font-mono text-xs" style="color:var(--text-faint)">{{ number_format($e->tokens_output) }}</td>
              <td class="px-5 py-2 text-right font-mono text-xs" style="color:var(--text-primary)">${{ number_format($e->cost_usd, 6) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

  {{-- ── Platform recent events ───────────────────────────────────────────────── --}}
  <div>
    <div class="flex items-center gap-3 mb-4">
      <div class="text-xs font-bold uppercase tracking-widest" style="color:var(--text-faint)">Platform Events</div>
      <div class="flex-1 h-px" style="background:var(--border)"></div>
      <span class="text-xs" style="color:var(--text-faint)">last 50</span>
    </div>

    <div class="rounded-2xl overflow-hidden" style="background:var(--bg-card);border:1px solid var(--border)">
      @if($recent->isEmpty())
        <div class="px-6 py-10 text-center text-sm" style="color:var(--text-faint)">No events yet.</div>
      @else
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[600px]">
          <thead>
            <tr class="text-xs uppercase tracking-wider" style="color:var(--text-faint);border-bottom:1px solid var(--border)">
              <th class="text-left px-5 py-3 font-semibold">Time</th>
              <th class="text-left px-5 py-3 font-semibold">Prompt Key</th>
              <th class="text-left px-5 py-3 font-semibold">Triggered By</th>
              <th class="text-left px-5 py-3 font-semibold">Model</th>
              <th class="text-right px-5 py-3 font-semibold">In</th>
              <th class="text-right px-5 py-3 font-semibold">Out</th>
              <th class="text-right px-5 py-3 font-semibold">Cost</th>
            </tr>
          </thead>
          <tbody>
            @foreach($recent as $e)
            <tr style="border-bottom:1px solid var(--border-subtle)">
              <td class="px-5 py-2 text-xs whitespace-nowrap" style="color:var(--text-faint)">{{ \Carbon\Carbon::parse($e->created_at)->format('M j, g:ia') }}</td>
              <td class="px-5 py-2 font-mono text-xs" style="color:#f1d362">{{ $e->prompt_key }}</td>
              <td class="px-5 py-2 text-xs" style="color:var(--text-muted)">{{ $e->triggered_by ?? '—' }}</td>
              <td class="px-5 py-2 font-mono text-xs" style="color:var(--text-faint)">{{ $e->model }}</td>
              <td class="px-5 py-2 text-right font-mono text-xs" style="color:var(--text-faint)">{{ number_format($e->tokens_input) }}</td>
              <td class="px-5 py-2 text-right font-mono text-xs" style="color:var(--text-faint)">{{ number_format($e->tokens_output) }}</td>
              <td class="px-5 py-2 text-right font-mono text-xs" style="color:var(--text-primary)">${{ number_format($e->cost_usd, 6) }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>
  </div>

</div>
</x-app-layout>

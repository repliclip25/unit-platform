{{--
  Reusable cadence-tab switcher for a transaction's client_drafts array.
  Shown in both Draft Email and Approve & Send so the tenant sees every
  round's actual message in one place, reviewing once instead of hunting
  across two stage cards for the same content.

  Expects: $clientDrafts (array), $wrapId (unique string per usage).
--}}
@if(count($clientDrafts))
@php
  $defaultIdx = collect($clientDrafts)->search(fn($c) => empty($c['approved_at']) && empty($c['placeholder']));
  if ($defaultIdx === false) $defaultIdx = collect($clientDrafts)->search(fn($c) => empty($c['placeholder']));
  if ($defaultIdx === false) $defaultIdx = 0;
@endphp
<div class="tc-draft-tabs">
  @foreach($clientDrafts as $idx => $cd)
  <button type="button"
    class="tc-draft-tab {{ !empty($cd['approved_at']) ? 'consumed' : '' }} {{ !empty($cd['placeholder']) ? 'upcoming' : '' }} {{ !empty($cd['preview']) ? 'upcoming' : '' }} {{ $idx === $defaultIdx ? 'active' : '' }}"
    onclick="document.querySelectorAll('#{{ $wrapId }} .tc-draft-pane').forEach(function(el){el.style.display='none'});
             document.getElementById('{{ $wrapId }}-{{ $idx }}').style.display='block';
             this.parentElement.querySelectorAll('.tc-draft-tab').forEach(function(el){el.classList.remove('active')});
             this.classList.add('active')">
    {{ ['1st','2nd','3rd'][($cd['reminder_number'] ?? 1) - 1] ?? ($cd['reminder_number'] . 'th') }}
    @if(!empty($cd['approved_at']))<span class="tc-draft-dot"></span>@endif
  </button>
  @endforeach
</div>
<div id="{{ $wrapId }}">
  @foreach($clientDrafts as $idx => $cd)
  <div id="{{ $wrapId }}-{{ $idx }}" class="tc-draft-pane" style="display:{{ $idx === $defaultIdx ? 'block' : 'none' }}">
    @if(!empty($cd['placeholder']))
    <div class="tc-msg-meta">
      <strong>{{ ['1st','2nd','3rd'][($cd['reminder_number'] ?? 1) - 1] ?? ($cd['reminder_number'] . 'th') }} reminder</strong>
      · not drafted yet — fires {{ $cd['days_before_expiry'] }} days before expiry, if the renewal is still open then
    </div>
    <p style="font-size:12px;color:var(--db-text-muted);font-style:italic">Nothing consumed here yet.</p>
    @else
    <div class="tc-msg-meta" style="display:flex;align-items:baseline;justify-content:space-between;gap:8px">
      <span>
        <strong>{{ ['1st','2nd','3rd'][($cd['reminder_number'] ?? 1) - 1] ?? ($cd['reminder_number'] . 'th') }} reminder</strong>
        @if(!empty($cd['days_before_expiry']))· {{ $cd['days_before_expiry'] }} days before expiry @endif
        @if(!empty($cd['preview']))
          · preview — approving round 1 authorizes this cadence; the real message is regenerated closer to the date, so wording may shift
        @elseif(!empty($cd['approved_at']))
          · approved {{ \Carbon\Carbon::parse($cd['approved_at'])->format('M j, g:i A') }} — consumed
        @else
          · drafted, not yet approved
        @endif
      </span>
      {{-- Round 2/3 can resolve to a different email_templates row than
           round 1 — each round's own template_id (see DraftEmailJob /
           UnitPlatform::recordClientDraft()) links here, not always
           round 1's. --}}
      @if(!empty($cd['template_id']))
      <a href="{{ route('app.workers.templates', $tx->worker_slug) }}#template-{{ $cd['template_id'] }}" style="font-size:11px;color:var(--accent-text,var(--db-text));text-decoration:underline;white-space:nowrap">Edit template</a>
      @endif
    </div>
    <div class="tc-msg"><strong>{{ $cd['subject'] ?? '' }}</strong>{{ "\n\n" }}{{ $cd['body'] ?? '' }}</div>
    {{-- Copy/Open-in-email moved to the Push to Gmail card (see AVA 2.2
         changelog) — that's the stage that actually knows whether Gmail is
         connected; at review time here, nothing's been decided yet. --}}
    @endif
  </div>
  @endforeach
</div>
@else
<p style="font-size:12px;color:var(--db-text-muted)">No draft yet.</p>
@endif

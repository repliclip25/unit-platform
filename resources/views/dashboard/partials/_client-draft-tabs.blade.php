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

    {{-- A preview isn't real yet — nothing to copy or send. No Gmail
         connected for this deployment — PushToGmailJob skips the API call
         entirely and this draft only ever exists in UNIT (see its own
         docblock, 'in_app_only'). The tenant still needs a way to actually
         send a REAL draft: copy the text, or hand off to whatever email
         client is already configured on their device via mailto:, neither
         of which needs any Gmail connection at all. --}}
    @if(empty($cd['preview']) && !$tx->gmail_draft_id)
    <div style="display:flex;gap:8px;margin-top:8px">
      <button type="button" class="tc-btn tc-btn-ghost" style="width:auto;display:inline-flex;align-items:center;gap:6px;padding:6px 12px"
        onclick="navigator.clipboard.writeText({{ \Illuminate\Support\Js::from(($cd['subject'] ?? '') . "\n\n" . ($cd['body'] ?? '')) }}); var el=this.querySelector('.tc-copy-label'); var prev=el.textContent; el.textContent='Copied'; setTimeout(function(){el.textContent=prev}, 1500)">
        <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="1.8" fill="none"><rect x="9" y="9" width="12" height="12" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 15H4a1 1 0 01-1-1V4a1 1 0 011-1h10a1 1 0 011 1v1"/></svg>
        <span class="tc-copy-label">Copy</span>
      </button>
      <a class="tc-btn tc-btn-ghost" style="width:auto;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;text-decoration:none"
        href="mailto:{{ rawurlencode($cd['to'] ?? '') }}?subject={{ rawurlencode($cd['subject'] ?? '') }}&amp;body={{ rawurlencode($cd['body'] ?? '') }}">
        <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="1.8" fill="none"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 6 9-6"/></svg>
        Open in Email
      </a>
    </div>
    @endif
    @endif
  </div>
  @endforeach
</div>
@else
<p style="font-size:12px;color:var(--db-text-muted)">No draft yet.</p>
@endif

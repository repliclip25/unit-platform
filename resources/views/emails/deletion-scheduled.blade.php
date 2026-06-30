@php $emailTitle = 'Account deletion scheduled — UNIT'; @endphp
@include('emails.partials.header')

<div class="badge-red" style="display:inline-flex;align-items:center;gap:6px;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444;font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;margin-bottom:20px">
  ⚠ Deletion scheduled
</div>

<h1>Your account will be deleted on {{ $deletionDate }}</h1>
<p>Hi {{ $name }}, we received a request to permanently delete your UNIT account.</p>

<p>Your account, all hired employees, all transactions, memory, and connected accounts will be permanently deleted on <strong>{{ $deletionDate }}</strong>.</p>

<div class="alert-yellow">
  <strong>Changed your mind?</strong> Log in before {{ $deletionDate }} and cancel the deletion from your profile page. After that date, recovery is not possible.
</div>

<a href="{{ url('/profile') }}" class="btn">Cancel deletion →</a>

<hr class="divider">

<p style="font-size:13px; color:#71717a;">If you did not request this, log in immediately and cancel. If you need help, reply to this email.</p>

@include('emails.partials.footer', ['footerNote' => "You're receiving this because a deletion was requested on your UNIT account."])

@php $emailTitle = 'AVA draft ready — UNIT'; @endphp
@include('emails.partials.header')

<div class="badge-violet"><span style="font-size:10px;">✦</span> AVA · Draft Ready</div>

<h1>A draft is waiting for your review</h1>
<p>Hi {{ $name }}, AVA processed a renewal email and prepared a draft response. Review it and approve or edit before it's sent.</p>

<table class="info-table">
  <tr>
    <td>Asset</td>
    <td>{{ $asset }}</td>
  </tr>
  <tr>
    <td>Client</td>
    <td>{{ $client }}</td>
  </tr>
  @if($contactName)
  <tr>
    <td>Contact</td>
    <td>{{ $contactName }}</td>
  </tr>
  @endif
  @if($subject)
  <tr>
    <td>Draft subject</td>
    <td>{{ $subject }}</td>
  </tr>
  @endif
  @if($confidence !== null)
  <tr>
    <td>AVA confidence</td>
    <td>
      <strong>{{ $confidence }}%</strong>
      <div class="bar-wrap" style="margin-top:6px;">
        <div class="bar-fill" style="width:{{ $confidence }}%; background:{{ $confidence >= 70 ? '#16a34a' : '#f97316' }};"></div>
      </div>
    </td>
  </tr>
  @endif
</table>

@if($confidence !== null && $confidence < 70)
<div class="alert-yellow">
  ⚠ <strong>Low confidence ({{ $confidence }}%).</strong> The client or asset match may be uncertain — please verify the details before approving.
</div>
@endif

<a href="{{ url('/transactions?filter=pending') }}" class="btn">Review draft →</a>

<hr class="divider">

<p style="font-size:12px; color:#a1a1aa;">Transaction ID: <code>{{ $txId }}</code>{{ $fastTrack ? ' · ⚡ Fast track' : '' }}</p>

@include('emails.partials.footer', ['footerNote' => "You're receiving this because AVA completed a draft and it's ready for your review."])

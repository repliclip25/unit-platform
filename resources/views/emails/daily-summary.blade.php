@php $emailTitle = $summarySubject; @endphp
@include('emails.partials.header')

<h1>{{ $summarySubject }}</h1>
<p style="font-size:13px; color:#a1a1aa; margin-bottom:20px;">{{ $date }}</p>

<table class="info-table">
  <tr>
    <td>Processed</td>
    <td><strong>{{ $total }} renewal{{ $total !== 1 ? 's' : '' }}</strong></td>
  </tr>
  <tr>
    <td>Urgent</td>
    <td>
      @if($urgent > 0)
        <strong style="color:#dc2626;">{{ $urgent }} urgent</strong>
      @else
        <span style="color:#16a34a;">None</span>
      @endif
    </td>
  </tr>
</table>

@if($urgent > 0)
<div class="alert-red">
  <strong>{{ $urgent }} urgent {{ $urgent === 1 ? 'item requires' : 'items require' }} your attention.</strong> Review and approve drafts before they expire.
</div>
@endif

<hr class="divider">

<p style="white-space:pre-wrap; font-size:14px; line-height:1.7; color:#3f3f46;">{{ $body }}</p>

<a href="{{ url('/app/transactions') }}" class="btn">View all transactions →</a>

@include('emails.partials.footer', ['footerNote' => "AVA · Daily Summary · You're receiving this as part of your UNIT workspace digest."])

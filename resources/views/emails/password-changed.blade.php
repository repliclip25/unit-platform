@php $emailTitle = 'Password changed — UNIT'; @endphp
@include('emails.partials.header')

<h1>Your password was changed</h1>
<p>Hi {{ $name }}, this is a confirmation that your UNIT account password was successfully updated. This change takes effect immediately across all your devices.</p>

<table class="info-table">
  <tr>
    <td>Changed at</td>
    <td>{{ now()->format('M j, Y · g:i A') }} UTC</td>
  </tr>
</table>

<a href="{{ url('/app/dashboard') }}" class="btn">Go to your workspace →</a>

<hr class="divider">

<div class="alert-red">
  <strong>Didn't make this change?</strong> Your account may be compromised. Reply to this email immediately and we'll help you secure it.
</div>

@include('emails.partials.footer', ['footerNote' => "You're receiving this because your UNIT account password was just changed."])

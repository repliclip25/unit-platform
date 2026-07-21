@php $emailTitle = 'Gmail Connected — UNIT'; @endphp
@include('emails.partials.header')

<div class="badge-green"><span class="dot" style="background:#16a34a;"></span> Connected</div>

<h1>Gmail is connected</h1>
<p>Hi {{ $name }}, your Gmail account has been linked to UNIT. AVA will now monitor your inbox and handle renewal and subscription emails automatically.</p>

<table class="info-table">
  <tr>
    <td>Gmail account</td>
    <td>{{ $gmailAddress }}</td>
  </tr>
  <tr>
    <td>Status</td>
    <td><strong>Active — inbox watch running</strong></td>
  </tr>
</table>

<a href="{{ url('/app/dashboard') }}" class="btn">View your workspace →</a>

<hr class="divider">

<div class="alert-red">
  <strong>Not you?</strong> If you didn't connect this Gmail account, secure your UNIT account immediately by replying to this email.
</div>

@include('emails.partials.footer', ['footerNote' => "You're receiving this because a Gmail account was just connected to your UNIT workspace."])

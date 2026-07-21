@php $emailTitle = $workerName . ' is live — UNIT'; @endphp
@include('emails.partials.header')

<div class="badge-green"><span class="dot" style="background:#16a34a;"></span> Live</div>

<h1>{{ $workerName }} is now on your team</h1>
<p>Hi {{ $name }}, your new employee has been hired and is actively monitoring for work.</p>

<table class="info-table">
  <tr>
    <td>Worker</td>
    <td>{{ $workerName }}</td>
  </tr>
  <tr>
    <td>Type</td>
    <td>{{ strtoupper($workerSlug) }}</td>
  </tr>
  @if($workerDesc)
  <tr>
    <td>Title</td>
    <td>{{ $workerDesc }}</td>
  </tr>
  @endif
  <tr>
    <td>Deployment</td>
    <td>#{{ $deploymentId }}</td>
  </tr>
  <tr>
    <td>Trial ends</td>
    <td>{{ $trialEndsAt }}</td>
  </tr>
</table>

<div class="alert-yellow">
  ⚡ <strong>Free trial active.</strong> Your worker will process transactions at no cost until {{ $trialEndsAt }}.
</div>

<a href="{{ url('/app/workers/' . $deploymentId) }}" class="btn">View your worker →</a>

<hr class="divider">

<p style="font-size:13px; color:#71717a;">Next step: make sure your Gmail inbox is connected and the watch is active so {{ $workerName }} can start working for you.</p>

@include('emails.partials.footer', ['footerNote' => "You're receiving this because you just deployed a worker on UNIT. Questions? Reply to this email."])

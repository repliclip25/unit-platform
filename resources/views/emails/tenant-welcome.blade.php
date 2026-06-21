@php $emailTitle = 'Welcome to UNIT'; @endphp
@include('emails.partials.header')

<h1>Welcome to UNIT, {{ $name }}</h1>
<p>Your AI workforce platform is ready. Here's how to get your first worker running in the next few minutes:</p>

<table class="info-table">
  <tr>
    <td>Step 1</td>
    <td><strong>Connect your Gmail inbox</strong> so AVA can monitor it</td>
  </tr>
  <tr>
    <td>Step 2</td>
    <td><strong>Upload your memory</strong> — clients, contacts, and assets</td>
  </tr>
  <tr>
    <td>Step 3</td>
    <td><strong>Deploy AVA</strong> and let it get to work</td>
  </tr>
</table>

<a href="{{ url('/dashboard') }}" class="btn">Go to your workspace →</a>

@include('emails.partials.footer', ['footerNote' => "You're receiving this because you just created a UNIT account. Questions? Reply to this email."])

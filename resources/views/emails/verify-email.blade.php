@php $emailTitle = 'Verify your UNIT account'; @endphp
@include('emails.partials.header')

<h1>Confirm your email address</h1>
<p>Hi {{ $name }}, welcome to UNIT. Click the button below to verify your email and activate your account.</p>
<p>This link expires in <strong>60 minutes</strong>.</p>

<a href="{{ $url }}" class="btn">Verify my email →</a>

<hr class="divider">

<p style="font-size:13px; color:#71717a;">If the button doesn't work, copy and paste this link into your browser:</p>
<p class="url-fallback">{{ $url }}</p>

@include('emails.partials.footer', ['footerNote' => "If you didn't create a UNIT account, you can safely ignore this email."])

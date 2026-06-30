@php $emailTitle = 'Your UNIT account has been deleted'; @endphp
@include('emails.partials.header')

<h1>Your account has been permanently deleted</h1>
<p>Hi {{ $name }}, your UNIT account associated with <strong>{{ $email }}</strong> has been permanently deleted.</p>

<p>All of your data — hired employees, transactions, memory, connected accounts, and billing — has been removed from our systems.</p>

<hr class="divider">

<p style="font-size:13px; color:#71717a;">If you believe this was done in error, please reply to this email within 7 days and we will investigate. After that, recovery is not possible.</p>

@include('emails.partials.footer', ['footerNote' => "You're receiving this final email because your UNIT account was deleted."])

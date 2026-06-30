@extends('layouts.public')
@section('title', 'Terms of Use')
@section('description', 'Terms and conditions for using the UNIT platform.')

@section('body')
<div class="w pub-hero">
  <div class="eyebrow">Legal</div>
  <h1>Terms of Use</h1>
  <p class="pub-meta">Last updated: June 23, 2026</p>
</div>

<div class="w pub-body">

  <p>These Terms of Use ("Terms") govern your access to and use of the UNIT platform operated by UNIT ("we," "us," or "our"). By creating an account or using the platform, you agree to these Terms.</p>

  <h2>1. Use of the Platform</h2>
  <p>UNIT provides AI-powered worker automation for compliance and license renewal operations. You may use the platform only for lawful business purposes. You may not:</p>
  <ul>
    <li>Use the platform to process spam, unsolicited communications, or fraudulent emails</li>
    <li>Attempt to reverse-engineer, scrape, or extract our AI models, prompts, or pipeline logic</li>
    <li>Share your account credentials with unauthorized third parties</li>
    <li>Use UNIT workers to generate and send emails without human review (our workers require your approval before sending)</li>
    <li>Circumvent the platform's quota, billing, or rate-limiting systems</li>
  </ul>

  <h2>2. Accounts</h2>
  <p>You are responsible for maintaining the security of your account. You must immediately notify us of any unauthorized access at <a href="mailto:hello@unit.report">hello@unit.report</a>. We are not liable for losses caused by unauthorized account access if you failed to take reasonable precautions.</p>

  <h2>3. Gmail Integration</h2>
  <p>When you connect a Gmail inbox, you authorize UNIT to read incoming emails and push draft replies to your Gmail Drafts folder. UNIT <strong>does not send emails on your behalf without your explicit approval</strong>. All drafted responses require you to review and approve before anything is sent. You may disconnect Gmail access at any time from your dashboard.</p>

  <h2>4. AI-Generated Content</h2>
  <p>UNIT workers use AI (Claude by Anthropic) to generate draft content. You acknowledge that:</p>
  <ul>
    <li>AI-generated drafts require your review before use</li>
    <li>UNIT is not liable for errors in AI-generated content that you approve and use</li>
    <li>You are responsible for the accuracy and compliance of any communication sent from your account</li>
  </ul>

  <h2>5. Billing and Subscriptions</h2>
  <p>Trial accounts include a limited number of free pipeline transactions. After the trial, continued access requires an active subscription. Subscriptions are billed in advance on a monthly or annual basis via Stripe. You may cancel at any time; cancellation takes effect at the end of the current billing period. Refunds are not provided for partial billing periods.</p>

  <h2>6. Acceptable Use of Worker Output</h2>
  <p>You may use worker output (drafted emails, renewal notices, compliance reports) for your internal business operations. You may not resell, redistribute, or sublicense UNIT output as a standalone product.</p>

  <h2>7. Uptime and Service Availability</h2>
  <p>We aim for high availability but do not guarantee 100% uptime. We are not liable for losses due to platform downtime, third-party API outages (Google, Anthropic, Stripe), or network disruptions. Scheduled maintenance will be announced in advance where possible.</p>

  <h2>8. Data and Privacy</h2>
  <p>Your use of UNIT is also governed by our <a href="{{ route('privacy') }}">Privacy Policy</a>, which is incorporated into these Terms by reference.</p>

  <h2>9. Termination</h2>
  <p>We reserve the right to suspend or terminate accounts that violate these Terms, engage in abusive usage patterns, or fail to pay for subscription services. You may terminate your account at any time by contacting <a href="mailto:hello@unit.report">hello@unit.report</a>.</p>

  <h2>10. Limitation of Liability</h2>
  <p>To the maximum extent permitted by law, UNIT is not liable for indirect, incidental, special, consequential, or punitive damages arising from your use of the platform. Our total liability for any claim shall not exceed the amount you paid to us in the 12 months preceding the claim.</p>

  <h2>11. Changes to Terms</h2>
  <p>We may update these Terms. We'll notify you of material changes by email. Continued use of the platform after the effective date constitutes acceptance of the revised Terms.</p>

  <h2>12. Contact</h2>
  <p>Questions about these Terms? Email us at <a href="mailto:hello@unit.report">hello@unit.report</a>.</p>

</div>
@endsection

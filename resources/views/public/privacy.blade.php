@extends('layouts.public')
@section('title', 'Privacy Policy')
@section('description', 'How UNIT collects, uses, and protects your data.')

@section('body')
<div class="w pub-hero">
  <div class="eyebrow">Legal</div>
  <h1>Privacy Policy</h1>
  <p class="pub-meta">Last updated: June 23, 2026</p>
</div>

<div class="w pub-body">

  <p>UNIT ("we," "our," or "us") operates the platform available at unit.report. This Privacy Policy explains how we collect, use, and protect information when you use our services.</p>

  <h2>1. Information We Collect</h2>
  <p><strong>Account information.</strong> When you register, we collect your name, email address, and password hash. We never store passwords in plain text.</p>
  <p><strong>Gmail credentials.</strong> When you connect a Gmail inbox to a worker deployment, we store an OAuth2 refresh token issued by Google. We never store your Gmail password. The token is used only to monitor your inbox for relevant emails and push drafts back into Gmail Drafts. You can revoke this access at any time from your Google Account settings or from your UNIT dashboard.</p>
  <p><strong>Email content.</strong> When a worker processes an email, the email body, subject, and sender information are stored temporarily as part of the transaction record. This data is used to run the pipeline (read → classify → draft) and is retained in your transaction log for review and audit purposes.</p>
  <p><strong>AI interaction data.</strong> Prompts sent to Claude (Anthropic's API) and the responses are processed through Anthropic's infrastructure. We do not use your email content to train AI models. See <a href="https://www.anthropic.com/privacy" target="_blank" rel="noopener">Anthropic's Privacy Policy</a> for how they handle API requests.</p>
  <p><strong>Usage data.</strong> We collect token usage, transaction counts, and timestamps for billing and quota tracking purposes.</p>
  <p><strong>Payment information.</strong> Payments are processed by Stripe. We do not store credit card numbers. See <a href="https://stripe.com/privacy" target="_blank" rel="noopener">Stripe's Privacy Policy</a>.</p>

  <h2>2. How We Use Your Information</h2>
  <ul>
    <li>To operate and improve the UNIT platform and worker pipelines</li>
    <li>To monitor inboxes and process renewal emails on your behalf</li>
    <li>To generate AI-assisted draft responses for your review</li>
    <li>To calculate billing, usage quotas, and trial limits</li>
    <li>To send transactional emails (deployment notifications, billing receipts)</li>
    <li>To provide customer support</li>
  </ul>
  <p>We do not sell your data to third parties. We do not use your email content for advertising.</p>

  <h2>3. Data Retention</h2>
  <p>Transaction records (including processed email content) are retained for as long as your account is active. You may request deletion of specific transactions from the Transactions log in your dashboard. To delete your account and all associated data, contact <a href="mailto:hello@unit.report">hello@unit.report</a>.</p>

  <h2>4. Third-Party Services</h2>
  <p>UNIT integrates with:</p>
  <ul>
    <li><strong>Google / Gmail API</strong> — inbox access and draft push</li>
    <li><strong>Anthropic Claude API</strong> — AI processing of email content</li>
    <li><strong>Stripe</strong> — payment processing</li>
    <li><strong>Redis</strong> — job queue processing (ephemeral, not persisted)</li>
  </ul>
  <p>Each third-party provider has their own privacy policy governing their use of data.</p>

  <h2>5. Security</h2>
  <p>We use TLS/HTTPS for all data in transit. OAuth tokens are stored encrypted at rest. Access to production data is restricted to authorized personnel. We use Google's standard OAuth2 flow — we never handle your Google password.</p>

  <h2>6. Your Rights</h2>
  <p>You may request access to, correction of, or deletion of your personal data at any time by emailing <a href="mailto:hello@unit.report">hello@unit.report</a>. You may also disconnect Gmail access from your dashboard at any time, which revokes our inbox access immediately.</p>

  <h2>7. Children's Privacy</h2>
  <p>UNIT is a business operations platform not intended for use by anyone under the age of 18. We do not knowingly collect personal information from minors.</p>

  <h2>8. Changes to This Policy</h2>
  <p>We may update this policy from time to time. We'll notify active users by email of any material changes. Continued use of UNIT after changes constitutes acceptance of the updated policy.</p>

  <h2>9. Contact</h2>
  <p>Questions about this Privacy Policy? Reach us at <a href="mailto:hello@unit.report">hello@unit.report</a>.</p>

</div>
@endsection

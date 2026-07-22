@extends('layouts.public')
@section('title', 'About UNIT')
@section('description', 'UNIT is a platform for deploying purpose-built AI workers — each one trained for a specific workflow, ready to run on your team.')

@section('body')
<div class="w pub-hero">
  <div class="eyebrow">The Company</div>
  <h1>Built by people who<br>ran the workflows first.</h1>
  <p>We're operations leads and engineers who got tired of building automation that still needed people to babysit it. UNIT is what we built instead.</p>
</div>

<div class="w pub-body">

  <h2>Our story</h2>
  <p>We didn't start as a software company. We started inside operations teams — running renewal pipelines, managing compliance calendars, chasing filings across government agencies. The work was repetitive, high-stakes, and completely manual. Every missed step meant downstream problems that took hours to untangle.</p>
  <p>The tools that existed were either too generic (spreadsheets, project management apps) or too expensive (custom enterprise software no small team could afford). So we started automating our own workflows — piece by piece — until we had something that actually ran the process instead of just organizing it.</p>
  <p>UNIT is that system, made available to every team.</p>

  <div class="pub-divider"></div>

  <h2>What we believe</h2>
  <p><strong>Workers, not just tools.</strong> A spreadsheet doesn't know when a filing window opens. A general AI assistant doesn't know what documentation a specific agency requires. Our workers are trained on the actual process — they know the forms, the deadlines, and the quirks of each workflow.</p>
  <p><strong>Humans stay in control.</strong> Every worker surfaces work for your review. Nothing goes out without your approval. The AI does the prep; you make the call.</p>
  <p><strong>Transparency over magic.</strong> Every transaction is logged. Every pipeline step is visible. You always know exactly what ran, when it ran, and why.</p>

  <div class="pub-divider"></div>

  <h2>Our workers</h2>
  <p>UNIT's first deployed worker is <strong>AVA</strong> — a Renewal &amp; Subscription Coordinator trained on NYC agency workflows. AVA monitors inboxes, classifies renewals, pulls client history, drafts responses, and queues them for human review.</p>
  <p>More workers are in development — NOVA (Filing Specialist) and REX (Compliance Monitor). Each is purpose-built for a specific org and workflow, not a general-purpose assistant. The lineup will grow over time as new workers are built and validated.</p>
  <p><a href="{{ route('public.workers.index') }}">Browse all workers →</a></p>

  <div class="pub-divider"></div>

  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:8px">
    @foreach([
      ['3+', 'Years building workflow automation'],
      ['4', 'Org-specific workflows live'],
      ['25k+', 'Transactions run by AI workers'],
    ] as [$n,$l])
    <div style="background:var(--card);border:1px solid var(--line);border-radius:14px;padding:28px 24px">
      <div style="font-family:var(--fd);font-size:36px;font-weight:800;color:var(--gold-text);margin-bottom:6px">{{ $n }}</div>
      <div style="font-size:13.5px;color:var(--t3)">{{ $l }}</div>
    </div>
    @endforeach
  </div>

  <div class="pub-divider"></div>

  <h2>Get in touch</h2>
  <p>Questions, partnership inquiries, or you want to talk through a workflow you're trying to automate — reach us at <a href="mailto:hello@unit.report">hello@unit.report</a>.</p>
  <p>If you're interested in early access, a pilot, or building a worker for your team's workflow, <a href="{{ route('influencer.apply') }}">apply to our partner program</a>.</p>

</div>
@endsection

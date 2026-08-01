@extends('layouts.public')
@section('title', 'Insurance License Renewal Software — AVA by UNIT')
@section('description', "AVA is AI-powered insurance license renewal software — monitoring your agency's inbox, tracking producer license and policy renewal dates, and preparing every response for human review before it sends.")
@section('og_type', 'website')

@php
    $__faq = [
        ['q' => 'Does AVA submit renewals automatically?', 'a' => 'No. AVA prepares and drafts the renewal, then queues it for your review. Nothing reaches your client without your explicit approval, that gate never gets skipped.'],
        ['q' => 'Can I prove what happened, for compliance or a client dispute?', 'a' => 'Yes. Every closed renewal gets a UNIT-branded archive PDF, every draft, every reminder, every approval decision, with a QR code linking to a signed, downloadable copy for a full year.'],
        ['q' => 'How does AVA access my renewal inbox?', 'a' => 'AVA connects to Gmail via OAuth2 and a real-time watch webhook. You choose which inbox she monitors, and you can revoke access at any time.'],
        ['q' => 'Can I cancel my subscription?', 'a' => 'Yes, cancel any time, no questions asked. Your data stays accessible for 30 days after cancellation.'],
    ];
    $__faqSchema = [
        '@context' => 'https://schema.org',
        '@type'     => 'FAQPage',
        'mainEntity' => array_map(fn ($f) => [
            '@type'          => 'Question',
            'name'           => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($f['a'])],
        ], $__faq),
    ];
@endphp

@section('head')
<script type="application/ld+json">{!! json_encode([
    '@@context'    => 'https://schema.org',
    '@type'       => 'Service',
    'name'        => 'AVA: AI Insurance License Renewal Software',
    'serviceType' => 'Insurance License Renewal',
    'description' => "AVA monitors your agency's inbox, tracks producer license and policy renewal dates, and drafts every renewal response for human review.",
    'url'         => url()->current(),
    'provider'    => ['@type' => 'Organization', 'name' => 'UNIT', 'url' => url('/')],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($__faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Insurance', 'item' => url()->current()],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<style>
.belief-list{display:flex;flex-direction:column}
.belief-item{display:flex;gap:20px;padding:26px 0;border-top:1px solid var(--line)}
.belief-item:last-child{border-bottom:1px solid var(--line)}
.belief-icon{width:40px;height:40px;flex-shrink:0;border-radius:10px;background:rgba(0,0,0,.04);display:flex;align-items:center;justify-content:center;color:var(--text)}
[data-theme="dark"] .belief-icon{background:rgba(255,255,255,.06)}
.belief-icon svg{width:19px;height:19px}
.belief-text h4{font-size:1.05rem;font-weight:800;color:var(--text);margin-bottom:6px}
.belief-text p{font-size:14.5px;color:var(--t3);line-height:1.7}
.about-sec{padding:clamp(48px,6vw,72px) 0}
.about-sec-h{max-width:640px;margin-bottom:36px}
.about-cta{position:relative;overflow:hidden;background:#0A0A0A;padding:clamp(56px,7vw,88px) 0;border-top:1px solid #1F1F1F}
.about-cta-inner{max-width:640px}
.about-cta-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:16px}
.about-cta h2{font-family:var(--fd);font-size:clamp(1.6rem,3.4vw,2.4rem);font-weight:800;line-height:1.15;letter-spacing:-.03em;color:#fff;margin-bottom:14px}
.about-cta p{font-size:14.5px;color:rgba(255,255,255,.55);line-height:1.75;margin-bottom:10px}
.about-cta a.btn-cta-final{display:inline-flex;align-items:center;gap:8px;padding:13px 26px;border-radius:12px;font-size:14.5px;font-weight:700;color:#0D0D0D;background:#fff;margin-top:14px;transition:opacity .15s,transform .15s}
.about-cta a.btn-cta-final:hover{opacity:.9;transform:translateY(-2px)}
.about-cta a.cta-link{color:rgba(255,255,255,.75);font-weight:600;text-decoration:underline;text-underline-offset:3px}
</style>
@endsection

@section('body')

<div class="w pub-hero">
  <div class="eyebrow">For Insurance Brokers</div>
  <h1>License renewals that don't slip through a shared inbox.</h1>
  <p>AVA is an AI renewal coordinator that monitors your agency's inbox, tracks producer license and policy renewal dates, and prepares every response for your review before it goes out.</p>
</div>

<section class="about-sec">
  <div class="w" style="max-width:800px">
    <div class="about-sec-h">
      <div class="slabel">The problem</div>
      <h2 class="sh2" style="font-size:32px">Renewal tracking shouldn't depend on someone remembering</h2>
    </div>
    <div class="pub-body" style="padding:0">
      <p>Producer licenses, E&amp;O policies, and carrier appointments all run on separate renewal clocks. When that tracking lives in someone's head or a shared inbox nobody owns, a missed date means a lapsed license or a client left uncovered.</p>
      <p>AVA reads your renewal inbox continuously, so a notice never sits unopened for days waiting for someone to have time to deal with it.</p>
    </div>
  </div>
</section>

<section class="about-sec sec-dark">
  <div class="w" style="max-width:800px">
    <div class="about-sec-h">
      <div class="slabel">How AVA helps</div>
      <h2 class="sh2" style="font-size:32px">One worker, watching every renewal</h2>
    </div>
    <div class="belief-list">
      <div class="belief-item">
        <div class="belief-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="belief-text">
          <h4>Monitors your inbox in real time</h4>
          <p>AVA connects to Gmail and watches for renewal, policy, and license notices as they arrive, not on a weekly check-in.</p>
        </div>
      </div>
      <div class="belief-item">
        <div class="belief-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
        <div class="belief-text">
          <h4>Tracks every renewal to completion</h4>
          <p>Once a renewal is detected, AVA logs it and keeps working the case until it's resolved, not just flagged once and forgotten.</p>
        </div>
      </div>
      <div class="belief-item">
        <div class="belief-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c1.5 0 2.9.37 4.14 1.02"/></svg></div>
        <div class="belief-text">
          <h4>Drafts the response, you approve it</h4>
          <p>AVA prepares the renewal reply using what it knows about the client or policy, and queues it in Gmail Drafts. Nothing sends without your review.</p>
        </div>
      </div>
      <div class="belief-item">
        <div class="belief-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8"/></svg></div>
        <div class="belief-text">
          <h4>Keeps a record for every renewal</h4>
          <p>Every step is logged, so when a regulator or client asks what happened on a given license, there's an answer.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="about-sec">
  <div class="w" style="max-width:800px">
    <div class="about-sec-h">
      <div class="slabel">Questions</div>
      <h2 class="sh2" style="font-size:32px">Common questions from insurance brokers</h2>
    </div>
    <div class="pub-body" style="padding:0">
      @foreach ($__faq as $item)
      <h3 style="font-size:18px">{{ $item['q'] }}</h3>
      <p>{!! $item['a'] !!}</p>
      @endforeach
    </div>
  </div>
</section>

<section class="about-cta">
  <div class="w">
    <div class="about-cta-inner">
      <div class="about-cta-eyebrow">See it in action</div>
      <h2>AVA started as a fix for domain and hosting renewals.</h2>
      <p>AVA also handles renewals for <a href="{{ route('compliance') }}" class="cta-link">compliance and licensing firms</a>, and it started as a fix for <a href="{{ route('agencies') }}" class="cta-link">IT and digital agency renewal tracking</a>, still its primary use case today.</p>
      <a href="{{ route('public.workers.show', 'ava') }}" class="btn-cta-final">
        See AVA's full profile
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</section>

@endsection

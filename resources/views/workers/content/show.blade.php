{{--
    Generic renderer for any worker's /{worker}/{path} search-market page.
    One shared view for every worker — see WorkerContentController and the
    worker_content_pages migration. Do not fork this per worker; add rows
    to worker_content_pages instead.
--}}
@extends('layouts.public')
@section('title', $page->seo_title)
@section('description', $page->meta_description)

@section('head')
<style>
/* Fixed nav is 63px tall and position:fixed — hero padding-top must clear
   it. Flat 88px on mobile (a vw-scaled value bottoms out too low at narrow
   widths); from 520px up, the clamp already stays safely above 63px. */
.wc-hero{padding-top:88px;padding-bottom:clamp(28px,4vw,40px)}
@media(min-width:520px){.wc-hero{padding-top:clamp(56px,7vw,76px)}}
.wc-hero .eyebrow{margin-bottom:14px}
.wc-hero h1{font-family:var(--fd);font-size:clamp(28px,4vw,44px);font-weight:800;letter-spacing:-1px;line-height:1.1;color:var(--text);max-width:760px}
.wc-hero-sub{font-size:16.5px;color:var(--t2);line-height:1.7;max-width:640px;margin-top:16px}
.wc-cta-row{display:flex;gap:12px;flex-wrap:wrap;margin-top:26px}
.wc-hero--split{display:flex;align-items:center;gap:clamp(24px,4vw,56px)}
.wc-hero-text{flex:1;min-width:0}
.wc-hero-img{flex-shrink:0;width:clamp(180px,24vw,280px)}
.wc-hero-img img{width:100%;height:auto;max-height:360px;object-fit:cover;border-radius:20px;display:block}
@media(max-width:680px){.wc-hero--split{flex-wrap:wrap-reverse}.wc-hero-img{width:clamp(160px,52vw,240px);margin:0 auto}}

.wc-body-img{margin:36px auto;text-align:center;max-width:420px}
.wc-body-img img{width:100%;border-radius:16px;display:block}
.wc-body-img figcaption{font-size:12.5px;color:var(--t3);margin-top:10px}

.wc-preface-img{max-width:280px;margin:0 auto 44px;text-align:center}
.wc-preface-img img{width:100%;border-radius:20px;display:block}

.wc-body h2{font-family:var(--fd);font-size:clamp(22px,2.6vw,28px);font-weight:800;letter-spacing:-.5px;color:var(--text);margin:44px 0 14px}
.wc-body h3{font-size:16px;font-weight:700;color:var(--text);margin:24px 0 8px}
.wc-body p{font-size:15px;color:var(--t2);line-height:1.75;margin:0 0 14px}
.wc-body ul,.wc-body ol{margin:0 0 16px;padding-left:22px}
.wc-body li{font-size:15px;color:var(--t2);line-height:1.7;margin-bottom:6px}
.wc-body strong{color:var(--text)}
.wc-body a{color:var(--text);font-weight:600;text-decoration:underline;text-underline-offset:3px}

.wc-cta-band{background:var(--text);color:#fff;border-radius:20px;padding:clamp(32px,5vw,52px);margin:52px auto;text-align:center}
[data-theme="dark"] .wc-cta-band{background:var(--raised)}
.wc-cta-band h2{color:#fff;margin-top:0}
[data-theme="dark"] .wc-cta-band h2{color:var(--text)}
.wc-cta-band p{color:rgba(255,255,255,.7);max-width:520px;margin:0 auto 22px}
[data-theme="dark"] .wc-cta-band p{color:var(--t2)}

.wc-faq-wrap{max-width:720px;margin:0 auto}
.wc-faq-item{border-bottom:1px solid var(--line);padding:16px 0}
.wc-faq-item:first-of-type{border-top:1px solid var(--line)}
.wc-faq-item summary{font-size:14.5px;font-weight:600;color:var(--text);cursor:pointer;list-style:none;display:flex;align-items:center;justify-content:space-between;gap:12px}
.wc-faq-item p{font-size:13.5px;color:var(--t3);line-height:1.75;margin:10px 0 0;padding-right:20px}
.wc-faq-chevron{width:15px;height:15px;color:var(--t4);flex-shrink:0;transition:transform .2s}
.wc-faq-item[open] .wc-faq-chevron{transform:rotate(180deg)}
</style>
@php
    $__workerName = $worker->name ?? ucfirst($workerSlug);
    $__ownerName  = 'UNITELO';
    if ($worker && $worker->owner) {
        $__ownerDecoded = json_decode($worker->owner, true);
        $__ownerName = $__ownerDecoded['name'] ?? $__ownerName;
    }
    $__serviceSchema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => $page->h1,
        'serviceType' => $page->page_family,
        'description' => $page->meta_description,
        'url'         => url()->current(),
        'provider'    => [
            '@type' => 'Organization',
            'name'  => $__ownerName,
            'url'   => url('/'),
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($__serviceSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@if($faqs->count())
<script type="application/ld+json">{!! json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => $faqs->map(fn($f) => [
        '@type'          => 'Question',
        'name'           => $f->question,
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer],
    ])->values()->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
<script type="application/ld+json">{!! json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'UNITELO', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $__workerName, 'item' => route('public.workers.show', $workerSlug)],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $page->h1, 'item' => url()->current()],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('body')
<div class="w wc-hero{{ $page->hero_image ? ' wc-hero--split' : '' }}">
    <div class="wc-hero-text">
        <div class="eyebrow">{{ $page->page_family }}</div>
        <h1>{{ $page->h1 }}</h1>
        <p class="wc-hero-sub">{{ $page->meta_description }}</p>
        <div class="wc-cta-row">
            <a href="{{ $page->cta_route ? route($page->cta_route) : route('register') }}" class="btn-g">{{ $page->cta_label ?? "Deploy {$__workerName}" }}</a>
            <a href="{{ route('public.workers.show', $workerSlug) }}" class="btn-ln">See How {{ $__workerName }} Works</a>
        </div>
    </div>
    @if($page->hero_image)
    <div class="wc-hero-img">
        <img src="{{ asset($page->hero_image) }}" alt="{{ $page->hero_image_alt ?? $__workerName }}" loading="eager">
    </div>
    @endif
</div>

<div class="w wc-body">
    {!! $page->body !!}
</div>

<div class="w wc-cta-band">
    <h2>{{ $page->cta_headline ?? "Give {$__workerName} the responsibility." }}</h2>
    <p>{{ $page->cta_subtext ?? $page->meta_description }}</p>
    <a href="{{ $page->cta_route ? route($page->cta_route) : route('register') }}" class="btn-g" style="display:inline-flex">{{ $page->cta_label ?? "Deploy {$__workerName}" }}</a>
</div>

@if($page->faq_image)
<div class="w">
    <div class="wc-preface-img">
        <img src="{{ asset($page->faq_image) }}" alt="{{ $page->faq_image_alt ?? $__workerName }}" loading="lazy">
    </div>
</div>
@endif

@if($faqs->count())
<div class="w" style="max-width:760px;margin:0 auto;padding-bottom:80px">
    <div class="sh" style="text-align:center;margin-bottom:28px">
        <div class="sh2">Frequently Asked Questions</div>
    </div>
    <div class="wc-faq-wrap">
        @foreach($faqs as $faq)
        <details class="wc-faq-item">
            <summary>{{ $faq->question }}<svg class="wc-faq-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></summary>
            <p>{{ $faq->answer }}</p>
        </details>
        @endforeach
    </div>
</div>
@endif
@endsection

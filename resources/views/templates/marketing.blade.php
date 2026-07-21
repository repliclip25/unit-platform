{{--
    TEMPLATE — Public marketing page reference.

    This is the canonical structure for public marketing pages: About,
    Pricing, Privacy, Terms, Marketplace, Blog, and Influencer Apply all
    extend layouts.public and follow this shape. The nav, mobile menu,
    theme toggle, and footer here are byte-identical to / and /workers —
    fix them there (or in resources/views/layouts/public.blade.php) if
    something's wrong, never duplicate them into a page.

    Improve the shared classes below (.pub-hero, .pub-body, .eyebrow,
    .sh2, .ssub, .sec, .btn-g, .btn-ln, .w/.w-md/.w-lg) in
    layouts/public.blade.php, then every page using them updates at once.

    Reachable at /templates/marketing.
--}}
@extends('layouts.public')
@section('title', 'Marketing Page Template')
@section('description', 'Reference structure for public marketing pages.')

@section('body')
<div class="w pub-hero">
  <div class="eyebrow">Example Eyebrow Label</div>
  <h1>Page headline goes here,<br>two lines max.</h1>
  <p>One or two sentences of supporting copy explaining what this page is for and why a visitor should keep reading.</p>
</div>

<div class="w pub-body">
  <h2>A content section heading</h2>
  <p>Body copy uses <code>.pub-body p</code> — comfortable line height, muted color, readable measure. This is the same class every legal/about page paragraph uses.</p>
  <ul>
    <li>List items use <code>.pub-body li</code></li>
    <li>Same muted color and spacing as paragraphs</li>
  </ul>

  <div class="pub-divider"></div>

  <h2>Buttons available to any page body</h2>
  <p style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">
    <a href="#" class="btn-g">Primary — .btn-g</a>
    <a href="#" class="btn-ln">Secondary — .btn-ln</a>
  </p>
</div>

<div class="sec sec-dark">
  <div class="w sh">
    <div class="slabel">Section Label</div>
    <div class="sh2">A centered section heading</div>
    <p class="ssub">Used for a full-width band that breaks up the page — pricing tiers, testimonials, FAQ, etc. <code>.sec-dark</code> gives it a subtly different background from the page.</p>
  </div>
</div>
@endsection

{{-- Canonical + Open Graph + Twitter Card tags, shared by every public page.
     Expects: $title, $description, optional $image, $type ('website'/'article'), $canonical --}}
@php
    $__seoImage     = $image ?? asset('images/hero-team-2.jpg');
    $__seoCanonical = $canonical ?? url()->current();
    $__seoType      = $type ?? 'website';
@endphp
<link rel="canonical" href="{{ $__seoCanonical }}">
<meta property="og:site_name" content="UNITELO">
<meta property="og:type" content="{{ $__seoType }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $__seoImage }}">
<meta property="og:url" content="{{ $__seoCanonical }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $__seoImage }}">

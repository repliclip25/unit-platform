@extends('layouts.public')
@section('title', 'Blog')
@section('description', 'Insights on workflow automation, AI workers, and operations, from the team building UNIT.')

@section('head')
<style>
.blog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px;margin-top:40px}
.blog-card{background:var(--card);border:1px solid var(--line);border-radius:16px;overflow:hidden;transition:border-color .2s;display:flex;flex-direction:column}
.blog-card:hover{border-color:rgba(241,211,98,0.3)}
.blog-img{height:180px;background:var(--surf);display:flex;align-items:center;justify-content:center;border-bottom:1px solid var(--line);overflow:hidden}
.blog-img img{width:100%;height:100%;object-fit:cover}
.blog-img-placeholder{font-size:48px;opacity:.15}
.blog-body{padding:22px;flex:1;display:flex;flex-direction:column;gap:10px}
.blog-tag{font-size:10.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--text)}
.blog-title{font-family:var(--fd);font-size:17px;font-weight:700;line-height:1.35;color:var(--text)}
.blog-excerpt{font-size:13.5px;color:var(--t3);line-height:1.65}
.blog-meta{font-size:12px;color:var(--t4);margin-top:auto}
.blog-cta{display:inline-flex;align-items:center;gap:4px;font-size:13px;font-weight:600;color:var(--text);margin-top:8px;text-decoration:none}
.blog-cta:hover{text-decoration:underline}
.coming-chip{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(255,255,255,0.06);border:1px solid var(--line);color:var(--t4)}
</style>
@endsection

@section('body')
<div class="w-lg pub-hero">
  <div class="eyebrow">Blog</div>
  <h1>Insights on workflow automation.</h1>
  <p>How AI workers are changing the way operations teams work: real processes, real results.</p>
</div>

<div class="w-lg" style="padding-bottom:80px">

  {{-- Featured post - most recently published, not hardcoded --}}
  @if($featured)
  <div style="background:var(--card);border:1px solid var(--line);border-radius:18px;overflow:hidden;display:grid;grid-template-columns:1fr 1fr;margin-top:40px">
    <div style="background:linear-gradient(135deg,#0a0800,#1a1200);display:flex;align-items:center;justify-content:center;padding:60px 40px;min-height:260px;border-right:1px solid var(--line)">
      <div style="font-size:72px;font-weight:900;font-family:var(--fd);color:rgba(241,211,98,0.15);line-height:1;letter-spacing:-4px;text-align:center">{{ strtoupper($featured->worker_slug ?? 'UNIT') }}</div>
    </div>
    <div style="padding:40px">
      <div class="blog-tag">{{ $featured->tag }}</div>
      <h2 style="font-family:var(--fd);font-size:24px;font-weight:800;margin:12px 0 14px;line-height:1.25">{{ $featured->title }}</h2>
      <p style="font-size:14px;color:var(--t3);line-height:1.65;margin-bottom:20px">{{ $featured->excerpt }}</p>
      <div class="blog-meta">{{ \Carbon\Carbon::parse($featured->created_at)->format('M Y') }} · {{ ceil(str_word_count(strip_tags($featured->body)) / 200) }} min read</div>
      <a href="{{ route('blog.show', $featured->slug) }}" class="blog-cta">Read article →</a>
    </div>
  </div>
  @endif

  <div class="blog-grid">

    {{-- DB posts (everything except the featured one above) --}}
    @foreach($dbPosts as $dbp)
    <div class="blog-card">
      <div class="blog-img">
        @if($dbp->cover_image)
          <img src="{{ Storage::url($dbp->cover_image) }}" alt="{{ $dbp->title }}" loading="lazy">
        @else
          <div class="blog-img-placeholder">📝</div>
        @endif
      </div>
      <div class="blog-body">
        <div class="blog-tag">{{ $dbp->tag }}</div>
        <div class="blog-title">{{ $dbp->title }}</div>
        <div class="blog-excerpt">{{ $dbp->excerpt }}</div>
        <div class="blog-meta">{{ \Carbon\Carbon::parse($dbp->created_at)->format('M Y') }} · {{ ceil(str_word_count(strip_tags($dbp->body)) / 200) }} min read</div>
        <a href="{{ route('blog.show', $dbp->slug) }}" class="blog-cta">Read →</a>
      </div>
    </div>
    @endforeach

  </div>

  @if($dbPosts->isEmpty() && !$featured)
  <p style="text-align:center;color:var(--t4);padding:40px 0">More posts coming soon.</p>
  @endif

  <div style="margin-top:60px;text-align:center;padding:40px;background:var(--card);border:1px solid var(--line);border-radius:18px">
    <div style="font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--t4);margin-bottom:12px">Newsletter</div>
    <div style="font-family:var(--fd);font-size:22px;font-weight:800;margin-bottom:10px">Get new posts in your inbox</div>
    <p style="font-size:14px;color:var(--t3);margin-bottom:24px;max-width:400px;margin-left:auto;margin-right:auto">One email when we publish something new. No marketing, no roundups, just the article.</p>
    <div style="display:flex;gap:8px;max-width:380px;margin:0 auto">
      <input type="email" placeholder="you@yourfirm.com" style="flex:1;padding:10px 14px;border-radius:9px;border:1px solid var(--line);background:var(--surf);color:var(--text);font-size:14px;outline:none">
      <button class="btn-g" style="padding:10px 18px;white-space:nowrap">Subscribe</button>
    </div>
  </div>

  <div style="margin-top:24px;text-align:center;padding:28px 40px;background:var(--card);border:1px solid var(--line);border-radius:18px">
    <div style="font-family:var(--fd);font-size:17px;font-weight:800;margin-bottom:6px">Write about UNIT and get paid for it</div>
    <p style="font-size:13.5px;color:var(--t3);margin-bottom:16px">Newsletter writers and industry creators earn 20–30% recurring commission through our Partner Program.</p>
    <a href="{{ route('influencer.apply') }}" class="btn-ln" style="display:inline-block">Partner Program →</a>
  </div>

</div>
@endsection

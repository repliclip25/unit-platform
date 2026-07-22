@extends('layouts.public')
@section('title', $post['title'])
@section('description', $post['excerpt'])

@section('head')
<style>
.blog-article-wrap{display:grid;grid-template-columns:1fr 280px;gap:56px;align-items:start;padding:56px 0 80px}
.blog-article{min-width:0}
.blog-article-tag{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold-text);margin-bottom:14px}
.blog-article h1{font-family:var(--fd);font-size:clamp(24px,3.5vw,40px);font-weight:800;letter-spacing:-1px;line-height:1.15;margin-bottom:16px}
.blog-article-meta{font-size:13px;color:var(--t4);margin-bottom:40px;display:flex;align-items:center;gap:12px}
.blog-article-meta span{display:flex;align-items:center;gap:5px}
.article-body h2{font-family:var(--fd);font-size:21px;font-weight:800;margin:44px 0 12px;color:var(--text)}
.article-body h2:first-child{margin-top:0}
.article-body p{font-size:16px;color:var(--t2);line-height:1.82;margin-bottom:20px}
.article-body ul{padding-left:20px;margin-bottom:20px}
.article-body li{font-size:16px;color:var(--t2);line-height:1.8;margin-bottom:8px}
.article-body a{color:var(--gold-text);text-decoration:underline;text-underline-offset:3px}
.article-body strong{color:var(--text)}
.article-body blockquote{border-left:3px solid var(--gold-text);padding:14px 20px;margin:28px 0;background:rgba(241,211,98,0.04);border-radius:0 8px 8px 0;font-style:italic;color:var(--t2)}
.article-divider{height:1px;background:var(--line);margin:40px 0}

/* Sidebar */
.blog-sidebar{position:sticky;top:80px;display:flex;flex-direction:column;gap:24px}
.sidebar-card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:22px}
.sidebar-label{font-size:10.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--t4);margin-bottom:14px}
.sidebar-post{display:block;padding:12px 0;border-bottom:1px solid var(--line);font-size:13.5px;color:var(--t2);line-height:1.45;transition:color .15s}
.sidebar-post:last-child{border-bottom:none;padding-bottom:0}
.sidebar-post:hover{color:var(--text)}
.sidebar-tag{font-size:10.5px;font-weight:700;color:var(--t4);margin-bottom:5px;display:block}
.share-btn{display:flex;align-items:center;gap:8px;width:100%;padding:9px 12px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--t2);font-size:13px;font-weight:600;cursor:pointer;font-family:var(--fb);transition:border-color .15s;margin-bottom:8px;text-align:left}
.share-btn:hover{border-color:var(--t3);color:var(--text)}

/* CTA card */
.sidebar-cta{background:linear-gradient(135deg,rgba(241,211,98,0.08),rgba(241,211,98,0.04));border:1px solid rgba(241,211,98,0.2);border-radius:14px;padding:22px;text-align:center}
.sidebar-cta h3{font-family:var(--fd);font-size:16px;font-weight:800;margin-bottom:8px}
.sidebar-cta p{font-size:13px;color:var(--t3);line-height:1.55;margin-bottom:16px}

/* Progress bar */
.read-progress{position:fixed;top:60px;left:0;right:0;height:2px;background:var(--line);z-index:200}
.read-progress-bar{height:100%;background:var(--gold);width:0%;transition:width .1s}

@media(max-width:900px){
  .blog-article-wrap{grid-template-columns:1fr}
  .blog-sidebar{position:static}
}
</style>
@endsection

@section('body')

{{-- Reading progress bar --}}
<div class="read-progress"><div class="read-progress-bar" id="read-bar"></div></div>

{{-- Breadcrumb --}}
<div class="w" style="padding-top:32px;padding-bottom:0">
  <div style="font-size:13px;color:var(--t4);display:flex;align-items:center;gap:8px">
    <a href="{{ route('blog') }}" style="color:var(--t4);transition:color .15s" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--t4)'">Blog</a>
    <span>→</span>
    <span style="color:var(--t3)">{{ $post['tag'] }}</span>
  </div>
</div>

<div class="w">
  <div class="blog-article-wrap">

    {{-- Article --}}
    <article class="blog-article" id="article-body">
      <div class="blog-article-tag">{{ $post['tag'] }}</div>
      <h1>{{ $post['title'] }}</h1>

      @if(!empty($post['cover_image']))
      <div style="margin:24px 0 32px;border-radius:16px;overflow:hidden;max-height:420px">
        <img src="{{ Storage::url($post['cover_image']) }}" alt="{{ $post['title'] }}"
          style="width:100%;height:100%;object-fit:cover;display:block" loading="lazy">
      </div>
      @endif

      <div class="blog-article-meta">
        <span>
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          {{ $post['date'] }}
        </span>
        <span>·</span>
        <span>
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          {{ $post['read'] }} read
        </span>
      </div>

      <div class="article-body">
        @foreach($post['body'] as [$type, $content])
          @if($type === 'h2')
            <h2>{{ $content }}</h2>
          @elseif($type === 'p')
            <p>{{ $content }}</p>
          @elseif($type === 'blockquote')
            <blockquote>{{ $content }}</blockquote>
          @elseif($type === 'ul')
            <ul>@foreach($content as $li)<li>{{ $li }}</li>@endforeach</ul>
          @elseif($type === 'html')
            {!! $content !!}
          @elseif($type === 'divider')
            <div class="article-divider"></div>
          @endif
        @endforeach
      </div>

      {{-- Article footer --}}
      <div style="margin-top:56px;padding-top:32px;border-top:1px solid var(--line)">
        <div style="display:flex;align-items:center;gap:16px">
          <div style="width:46px;height:46px;border-radius:50%;background:rgba(241,211,98,0.12);border:1px solid rgba(241,211,98,0.2);display:flex;align-items:center;justify-content:center;font-family:var(--fd);font-weight:800;font-size:18px;color:var(--gold-text);flex-shrink:0">F</div>
          <div>
            <div style="font-size:14px;font-weight:700;color:var(--text)">Franklin</div>
            <div style="font-size:13px;color:var(--t4)">UNIT · Compliance Operations</div>
          </div>
        </div>
      </div>

      {{-- Next/prev nav --}}
      <div style="margin-top:40px;display:flex;justify-content:space-between">
        <a href="{{ route('blog') }}" class="btn-ln" style="font-size:13px">← All posts</a>
        <a href="{{ route('register') }}" class="btn-g" style="font-size:13px">Try UNIT Free →</a>
      </div>
    </article>

    {{-- Sidebar --}}
    <aside class="blog-sidebar">

      {{-- Share --}}
      <div class="sidebar-card">
        <div class="sidebar-label">Share</div>
        <button class="share-btn" onclick="navigator.clipboard.writeText(window.location.href);this.textContent='✓ Link copied'">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
          Copy link
        </button>
        <a href="https://twitter.com/intent/tweet?text={{ urlencode($post['title']) }}&url={{ urlencode(url()->current()) }}" target="_blank" class="share-btn" style="display:flex;text-decoration:none">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          Share on X
        </a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" class="share-btn" style="display:flex;text-decoration:none">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
          Share on LinkedIn
        </a>
      </div>

      {{-- More articles --}}
      <div class="sidebar-card">
        <div class="sidebar-label">More from the blog</div>
        @foreach([
          ['Automation', 'Why license renewal agencies are still running on spreadsheets'],
          ['AI & Compliance', 'The right way to use AI in compliance work: guardrails, not guesses'],
          ['Product', 'What it means for a worker to be "trained on an org\'s workflow"'],
        ] as [$tag, $title])
        <a href="{{ route('blog') }}" class="sidebar-post">
          <span class="sidebar-tag">{{ $tag }}</span>
          {{ $title }}
        </a>
        @endforeach
      </div>

      {{-- Deploy CTA --}}
      <div class="sidebar-cta">
        <h3>Deploy a worker free</h3>
        <p>Browse UNIT workers and run a live test — first 25 transactions free, no card required.</p>
        <a href="{{ route('marketplace') }}" class="btn-g" style="display:block;text-align:center;font-size:13px">Browse Workers →</a>
      </div>

      {{-- Partner program CTA --}}
      <div class="sidebar-cta">
        <h3>Earn with UNIT</h3>
        <p>Creators and consultants earn 20–30% recurring commission promoting UNIT to their audience.</p>
        <a href="{{ route('influencer.apply') }}" class="btn-ln" style="display:block;text-align:center;font-size:13px">Partner Program →</a>
      </div>

    </aside>
  </div>
</div>
@endsection

@section('scripts')
<script>
// Reading progress
(function(){
  var bar = document.getElementById('read-bar');
  var art = document.getElementById('article-body');
  if (!bar || !art) return;
  window.addEventListener('scroll', function() {
    var rect = art.getBoundingClientRect();
    var total = art.offsetHeight - window.innerHeight;
    var scrolled = Math.max(0, -rect.top);
    bar.style.width = Math.min(100, Math.round((scrolled / total) * 100)) + '%';
  }, {passive: true});
})();
</script>
@endsection

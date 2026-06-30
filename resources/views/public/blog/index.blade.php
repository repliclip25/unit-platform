@extends('layouts.public')
@section('title', 'Blog')
@section('description', 'Insights on workflow automation, AI workers, and operations — from the team building UNIT.')

@section('head')
<style>
.blog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px;margin-top:40px}
.blog-card{background:var(--card);border:1px solid var(--line);border-radius:16px;overflow:hidden;transition:border-color .2s;display:flex;flex-direction:column}
.blog-card:hover{border-color:rgba(241,211,98,0.3)}
.blog-img{height:180px;background:var(--surf);display:flex;align-items:center;justify-content:center;border-bottom:1px solid var(--line);overflow:hidden}
.blog-img img{width:100%;height:100%;object-fit:cover}
.blog-img-placeholder{font-size:48px;opacity:.15}
.blog-body{padding:22px;flex:1;display:flex;flex-direction:column;gap:10px}
.blog-tag{font-size:10.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold-text)}
.blog-title{font-family:var(--fd);font-size:17px;font-weight:700;line-height:1.35;color:var(--text)}
.blog-excerpt{font-size:13.5px;color:var(--t3);line-height:1.65}
.blog-meta{font-size:12px;color:var(--t4);margin-top:auto}
.blog-cta{display:inline-flex;align-items:center;gap:4px;font-size:13px;font-weight:600;color:var(--gold-text);margin-top:8px;text-decoration:none}
.blog-cta:hover{text-decoration:underline}
.coming-chip{display:inline-block;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(255,255,255,0.06);border:1px solid var(--line);color:var(--t4)}
</style>
@endsection

@section('body')
<div class="w-lg pub-hero">
  <div class="eyebrow">Blog</div>
  <h1>Insights on workflow automation.</h1>
  <p>How AI workers are changing the way operations teams work — real processes, real results.</p>
</div>

<div class="w-lg" style="padding-bottom:80px">

  {{-- Featured post --}}
  <div style="background:var(--card);border:1px solid var(--line);border-radius:18px;overflow:hidden;display:grid;grid-template-columns:1fr 1fr;margin-top:40px">
    <div style="background:linear-gradient(135deg,#0a0800,#1a1200);display:flex;align-items:center;justify-content:center;padding:60px 40px;min-height:260px;border-right:1px solid var(--line)">
      <div style="font-size:72px;font-weight:900;font-family:var(--fd);color:rgba(241,211,98,0.15);line-height:1;letter-spacing:-4px;text-align:center">AVA</div>
    </div>
    <div style="padding:40px">
      <div class="blog-tag">Automation · AVA</div>
      <h2 style="font-family:var(--fd);font-size:24px;font-weight:800;margin:12px 0 14px;line-height:1.25">How AVA processes a NYCSCA renewal from inbox to draft in under 5 minutes</h2>
      <p style="font-size:14px;color:var(--t3);line-height:1.65;margin-bottom:20px">A step-by-step walkthrough of AVA's 8-stage pipeline — what each job does, how memory lookup works, and why the human-review gate matters.</p>
      <div class="blog-meta">June 2026 · 8 min read</div>
      <a href="{{ route('blog.show', 'how-ava-processes-nycsca-renewal') }}" class="blog-cta">Read article →</a>
    </div>
  </div>

  <div class="blog-grid">

    {{-- DB posts --}}
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

    @foreach([
      ['Operations', 'Why license renewal agencies are still running on spreadsheets — and what it costs them', 'The hidden cost isn\'t the missed deadline. It\'s the four hours every week a coordinator spends doing the same lookup in three different systems.', 'May 2026', '6 min'],
      ['AI & Compliance', 'The right way to use AI in compliance work: guardrails, not guesses', 'AI that sends emails on your behalf without review isn\'t a feature — it\'s a liability. Here\'s how UNIT approaches the human-in-the-loop question.', 'May 2026', '5 min'],
      ['Product', 'What it means for a worker to be "trained on an org\'s workflow"', 'AVA doesn\'t just process text. It knows what NYCSCA requires, what a DOB filing looks like, and when an FDNY renewal is at risk. Here\'s how we built that.', 'April 2026', '7 min'],
      ['Operations', 'The 5 renewal failure modes we see most — and how to prevent them', 'Missed deadlines, wrong contact info, unsigned forms, duplicate filings, and the catch-all: nobody checked. Every one of these is preventable.', 'April 2026', '4 min'],
      ['Product', 'Prompt overrides: how tenants customize AVA without breaking it', 'Our new per-stage prompt override system lets teams tune AVA\'s behavior for their specific workflows — with safety rails to prevent misconfiguration.', 'June 2026', '5 min'],
      ['Engineering', 'Building a multi-tenant AI pipeline on Laravel and Claude', 'The architecture behind UNIT\'s worker system — queue design, prompt injection, token metering, and why we chose Claude for every stage.', 'March 2026', '10 min'],
    ] as [$tag, $title, $excerpt, $date, $read])
    <div class="blog-card">
      <div class="blog-img">
        <div class="blog-img-placeholder">📄</div>
      </div>
      <div class="blog-body">
        <div class="blog-tag">{{ $tag }}</div>
        <div class="blog-title">{{ $title }}</div>
        <div class="blog-excerpt">{{ $excerpt }}</div>
        <div class="blog-meta">{{ $date }} · {{ $read }} read</div>
        <a href="#" class="blog-cta">Read →</a>
      </div>
    </div>
    @endforeach

  </div>

  <div style="margin-top:60px;text-align:center;padding:40px;background:var(--card);border:1px solid var(--line);border-radius:18px">
    <div style="font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--t4);margin-bottom:12px">Newsletter</div>
    <div style="font-family:var(--fd);font-size:22px;font-weight:800;margin-bottom:10px">Get new posts in your inbox</div>
    <p style="font-size:14px;color:var(--t3);margin-bottom:24px;max-width:400px;margin-left:auto;margin-right:auto">One email when we publish something new. No marketing, no roundups — just the article.</p>
    <div style="display:flex;gap:8px;max-width:380px;margin:0 auto">
      <input type="email" placeholder="you@yourfirm.com" style="flex:1;padding:10px 14px;border-radius:9px;border:1px solid var(--line);background:var(--surf);color:var(--text);font-size:14px;outline:none">
      <button class="btn-g" style="padding:10px 18px;white-space:nowrap">Subscribe</button>
    </div>
  </div>

</div>
@endsection

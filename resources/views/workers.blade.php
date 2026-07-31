<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Meet the Team: UNIT AI Agents</title>
<meta name="description" content="Meet UNIT's AI agents, each an AI worker trained for one job. AVA, our AI renewal coordinator, is live; more AI agents are coming soon.">
<link rel="icon" type="image/png" href="/logo.png">
<link rel="apple-touch-icon" href="/logo.png">
@include('partials.seo-meta', [
    'title'       => 'Meet the Team: UNIT AI Agents',
    'description' => "Meet UNIT's AI agents, each an AI worker trained for one job. AVA, our AI renewal coordinator, is live; more AI agents are coming soon.",
    'image'       => asset('images/hero-team-2.png'),
])
<script type="application/ld+json">{!! json_encode([
    '@@context' => 'https://schema.org',
    '@type'    => 'ItemList',
    'name'     => 'UNIT AI Agents',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'item' => ['@type' => 'Service', 'name' => 'AVA', 'serviceType' => 'AI Renewal Agent', 'url' => route('public.workers.show', 'ava')]],
        ['@type' => 'ListItem', 'position' => 2, 'item' => ['@type' => 'Service', 'name' => 'DOX', 'serviceType' => 'AI Document Agent']],
        ['@type' => 'ListItem', 'position' => 3, 'item' => ['@type' => 'Service', 'name' => 'MOX', 'serviceType' => 'AI Brand Intelligence Agent']],
        ['@type' => 'ListItem', 'position' => 4, 'item' => ['@type' => 'Service', 'name' => 'NUX', 'serviceType' => 'AI Publishing Agent']],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/unit-public.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
img{display:block;max-width:100%}
a{text-decoration:none;color:inherit}
button{cursor:pointer;font-family:inherit;border:none;background:none}
ul{list-style:none}

:root{
  --brand:#F5C518;--brand-dark:#0D0D0D;
  --text:#0D0D0D;--t2:#374151;--t3:#6B7280;--t4:#9CA3AF;
  --border:#E5E7EB;--bg:#FFFFFF;--soft:#F8F8F6;
  --font-h:'Inter',sans-serif;--font-b:'Inter',sans-serif;
  --max:1160px;--pad:clamp(20px,5vw,48px);
}
[data-theme="dark"]{
  --text:#F3F4F6;--t2:#D1D5DB;--t3:#9CA3AF;--t4:#6B7280;
  --border:#2D2D2D;--bg:#0D0D0D;--soft:#161616;--brand:#F5C518;
}
[data-theme="dark"] .nav{background:rgba(13,13,13,.92);border-color:#2D2D2D}
[data-theme="dark"] .hero-page{background:#0D0D0D}
[data-theme="dark"] .hero-fade-page{background:linear-gradient(to right,#0D0D0D 0%,rgba(13,13,13,.65) 18%,transparent 40%)}
[data-theme="dark"] .workers-sec{background:#0D0D0D}
[data-theme="dark"] .wk-card{background:#111;border-color:#2D2D2D}
[data-theme="dark"] .wk-quote{color:#D1D5DB}
[data-theme="dark"] .wk-bullet{color:#D1D5DB}
[data-theme="dark"] .wk-role{color:#9CA3AF}
[data-theme="dark"] .btn-watch-wk{color:#D1D5DB;border-color:#3D3D3D}
[data-theme="dark"] .btn-watch-wk:hover{border-color:#888;color:#F3F4F6}
[data-theme="dark"] .search-wrap input{background:#111;border-color:#3D3D3D;color:#F3F4F6}
[data-theme="dark"] .search-wrap input::placeholder{color:#6B7280}
[data-theme="dark"] .search-wrap svg{color:#6B7280}
[data-theme="dark"] .tag-btn{background:#1a1a1a;border-color:#3D3D3D;color:#9CA3AF}
[data-theme="dark"] .tag-btn:hover{border-color:#0D0D0D;color:#0D0D0D}
[data-theme="dark"] .tag-btn.on{background:#0D0D0D;border-color:#0D0D0D;color:#fff}
[data-theme="dark"] .behind-bar{background:#0D0D0D;border-color:#2D2D2D}
[data-theme="dark"] .behind-item{border-color:#2D2D2D}
[data-theme="dark"] .behind-icon{background:#1a1a1a}
[data-theme="dark"] .icon-diary{stroke:#F3F4F6}
[data-theme="dark"] .behind-h{color:#F3F4F6}
[data-theme="dark"] .behind-p{color:#9CA3AF}
[data-theme="dark"] .cta-foot{background:#0D0D0D}
[data-theme="dark"] .empty-state{color:#6B7280}
[data-theme="dark"] .btn-login{color:#D1D5DB;border-color:#2D2D2D}

body{font-family:var(--font-b);color:var(--text);background:var(--bg);-webkit-font-smoothing:antialiased;overflow-x:hidden}
.w{max-width:var(--max);margin:0 auto;padding:0 var(--pad)}

/* Nav and footer CSS/JS now live in the public-nav and public-footer
   components (single source of truth), not duplicated per-page. */

/* ── HERO ── */
.hero-page{padding-top:62px;background:#fff;display:grid;grid-template-columns:1fr 1fr;min-height:68vh;overflow:hidden}
.hero-page-left{display:flex;align-items:center;padding-top:clamp(48px,6vw,80px);padding-bottom:clamp(48px,6vw,80px);padding-right:clamp(32px,4vw,56px);padding-left:max(var(--pad),calc((100vw - var(--max))/2 + var(--pad)))}
.hero-page-inner{max-width:480px}
.page-eye{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#0D0D0D;margin-bottom:14px}
.hero-page-h{font-family:var(--font-h);font-size:clamp(1.9rem,3.6vw,2.8rem);font-weight:800;line-height:1.1;letter-spacing:-.03em;color:var(--text);margin-bottom:18px}
.hero-page-h em{font-style:normal;position:relative;display:inline}
.hero-page-h em::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}
.hero-page-p{font-size:1rem;color:var(--t2);line-height:1.75;margin-bottom:28px;max-width:400px}
.hero-proof{display:flex;align-items:center;gap:12px}
.proof-avs{display:flex}
.proof-avs img{width:34px;height:34px;border-radius:50%;border:2px solid #fff;margin-left:-8px;outline:1.5px solid #d1d5db;object-fit:cover;object-position:center top;flex-shrink:0;box-shadow:0 1px 4px rgba(0,0,0,.12)}
.proof-avs img:first-child{margin-left:0}
.proof-txt{font-size:13px;color:var(--t3);line-height:1.5}
.proof-txt strong{color:var(--text);display:block}
.hero-page-right{position:relative;overflow:hidden;background:#000}
.hero-page-right img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center top}
.hero-page-spacer{display:block;width:100%;min-height:calc(68vh - 62px)}
.hero-fade-page{position:absolute;inset:0;z-index:2;background:linear-gradient(to right,#fff 0%,rgba(255,255,255,.55) 15%,transparent 36%);pointer-events:none}
.hero-badge{position:absolute;bottom:24px;right:24px;z-index:3;background:#fff;border:1px solid var(--border);border-radius:16px;padding:12px 16px;display:flex;align-items:center;gap:10px;box-shadow:0 4px 20px rgba(0,0,0,.1)}
.badge-txt{font-size:13px;font-weight:700;color:var(--text);line-height:1.45}

/* ── ACTIVITY FEED ── */
.activity-feed{background:#0A0A0A;border-top:1px solid #1F1F1F;border-bottom:1px solid #1F1F1F;overflow:hidden;position:relative}
.activity-feed::before,.activity-feed::after{content:'';position:absolute;top:0;bottom:0;width:80px;z-index:2;pointer-events:none}
.activity-feed::before{left:0;background:linear-gradient(to right,#0A0A0A,transparent)}
.activity-feed::after{right:0;background:linear-gradient(to left,#0A0A0A,transparent)}
.feed-track{display:flex;align-items:center;width:max-content;animation:feedScroll 70s linear infinite}
.feed-track:hover{animation-play-state:paused}
@keyframes feedScroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.feed-item{display:flex;align-items:center;gap:12px;padding:17px 36px;border-right:1px solid #1F1F1F;white-space:nowrap;flex-shrink:0}
.feed-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;position:relative}
.feed-dot::after{content:'';position:absolute;inset:-4px;border-radius:50%;opacity:0;animation:dotPing 2.4s ease-out infinite}
.feed-dot.green{background:#22C55E;box-shadow:0 0 8px rgba(34,197,94,.7)}.feed-dot.green::after{background:rgba(34,197,94,.35)}
.feed-dot.blue{background:#3B82F6;box-shadow:0 0 8px rgba(59,130,246,.7)}.feed-dot.blue::after{background:rgba(59,130,246,.35)}
.feed-dot.amber{background:#F59E0B;box-shadow:0 0 8px rgba(245,158,11,.7)}.feed-dot.amber::after{background:rgba(245,158,11,.35)}
.feed-item:nth-child(2) .feed-dot::after{animation-delay:.4s}
.feed-item:nth-child(3) .feed-dot::after{animation-delay:.8s}
.feed-item:nth-child(4) .feed-dot::after{animation-delay:1.2s}
.feed-item:nth-child(5) .feed-dot::after{animation-delay:1.6s}
.feed-item:nth-child(6) .feed-dot::after{animation-delay:2s}
.feed-item:nth-child(7) .feed-dot::after{animation-delay:.3s}
.feed-item:nth-child(8) .feed-dot::after{animation-delay:.9s}
.feed-item:nth-child(9) .feed-dot::after{animation-delay:1.5s}
.feed-item:nth-child(10) .feed-dot::after{animation-delay:2.1s}
@keyframes dotPing{0%{transform:scale(1);opacity:.8}70%{transform:scale(2.8);opacity:0}100%{transform:scale(2.8);opacity:0}}
.feed-worker{font-size:13px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.feed-action{font-size:14px;color:rgba(255,255,255,.85)}
.feed-time{font-size:12px;color:rgba(255,255,255,.4);margin-left:4px}

/* ── SECTION ATOMS ── */
.sec-eye{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#0D0D0D;margin-bottom:12px}
.sec-h{font-family:var(--font-h);font-size:clamp(1.7rem,3.2vw,2.4rem);font-weight:800;line-height:1.12;letter-spacing:-.03em;color:var(--text);margin-bottom:12px}

/* ── WORKERS SECTION ── */
.workers-sec{background:var(--soft);padding:clamp(56px,7vw,88px) 0}

/* search + tags toolbar */
.toolbar{margin-bottom:clamp(32px,4vw,48px)}
.toolbar-top{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:20px;flex-wrap:wrap}
.toolbar-top-left .sec-eye{margin-bottom:6px}
.toolbar-top-left .sec-h{margin-bottom:0;font-size:clamp(1.5rem,2.6vw,2rem)}
.search-wrap{
  position:relative;display:flex;align-items:center;
  flex-shrink:0;
}
.search-wrap svg{position:absolute;left:14px;width:17px;height:17px;color:var(--t4);pointer-events:none;flex-shrink:0}
.search-wrap input{
  padding:11px 16px 11px 42px;
  border-radius:12px;
  border:1.5px solid var(--border);
  background:#fff;
  font-family:var(--font-b);font-size:14px;color:var(--text);
  width:260px;
  outline:none;
  transition:border-color .15s,box-shadow .15s;
}
.search-wrap input:focus{border-color:#0D0D0D;box-shadow:0 0 0 3px rgba(245,197,24,.12)}
.search-wrap input::placeholder{color:var(--t4)}

/* specialty tag pills */
.tags{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.tags-label{font-size:12px;font-weight:600;color:var(--t4);letter-spacing:.04em;margin-right:4px}
.tag-btn{
  padding:6px 14px;border-radius:99px;font-size:12.5px;font-weight:600;
  color:var(--t3);border:1.5px solid var(--border);background:var(--bg);
  cursor:pointer;transition:all .15s;
}
.tag-btn:hover{border-color:#0D0D0D;color:#0D0D0D}
.tag-btn.on{background:#0D0D0D;border-color:#0D0D0D;color:#fff}

/* worker result count */
.result-count{font-size:13px;color:var(--t4);margin-bottom:20px}
.result-count span{font-weight:700;color:var(--t2)}

/* 2-col card grid */
.wk-grid{
  display:grid;
  grid-template-columns:repeat(2,1fr);
  gap:clamp(16px,2vw,24px);
}

/* individual card - mirrors the screenshot layout exactly */
.wk-card{
  background:#fff;
  border:1px solid var(--border);
  border-radius:20px;
  overflow:hidden;
  display:flex;
  position:relative;
  min-height:300px;
  transition:transform .2s,box-shadow .2s;
}
.wk-card:hover{transform:translateY(-3px);box-shadow:0 16px 44px rgba(0,0,0,.08)}
[data-theme="dark"] .wk-card:hover{box-shadow:0 16px 44px rgba(0,0,0,.35)}

/* floating character image - right half */
.wk-img-bg{
  position:absolute;right:0;top:0;bottom:0;
  width:48%;
  pointer-events:none;
}
.wk-img-bg img{width:100%;height:100%;object-fit:cover;object-position:center top}
/* gradient: white → transparent, stops at ~55% so face shows */
.wk-img-bg::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(to right,#fff 0%,rgba(255,255,255,.9) 18%,rgba(255,255,255,.35) 38%,transparent 56%);
}
[data-theme="dark"] .wk-img-bg::after{
  background:linear-gradient(to right,#111 0%,rgba(17,17,17,.88) 18%,rgba(17,17,17,.3) 38%,transparent 56%);
}

/* left content */
.wk-content{
  position:relative;z-index:1;
  padding:clamp(22px,2.5vw,30px);
  display:flex;flex-direction:column;
  width:60%;
}
.wk-head{display:flex;align-items:center;gap:10px;margin-bottom:6px}
.wk-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.wk-icon svg{width:19px;height:19px}
.wk-name{font-family:var(--font-h);font-size:1.35rem;font-weight:800;letter-spacing:-.04em;line-height:1}
.wk-role{font-size:11px;font-weight:600;color:var(--t3);letter-spacing:.05em;text-transform:uppercase;margin-bottom:14px}
.wk-quote{font-size:13.5px;font-weight:600;color:var(--t2);line-height:1.6;margin-bottom:16px;flex:1}
.wk-bullets{display:flex;flex-direction:column;gap:7px;margin-bottom:20px}
.wk-bullet{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:500;color:var(--t2)}
.wk-check{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.wk-check svg{width:10px;height:10px;stroke:#fff;stroke-width:2.5;fill:none}
.wk-btns{display:flex;align-items:center;gap:8px;flex-wrap:nowrap}
.btn-hire-wk{
  display:inline-flex;align-items:center;gap:6px;
  padding:9px 14px;border-radius:10px;
  font-size:12.5px;font-weight:700;color:#fff;
  white-space:nowrap;flex-shrink:0;
  transition:opacity .15s,transform .1s;
}
.btn-hire-wk:hover{opacity:.88;transform:translateY(-1px)}
.btn-watch-wk{
  display:inline-flex;align-items:center;gap:6px;
  padding:9px 14px;border-radius:10px;
  font-size:12.5px;font-weight:700;
  border:1.5px solid var(--border);color:var(--t2);
  white-space:nowrap;flex-shrink:0;
  transition:all .15s;
}
.btn-watch-wk:hover{border-color:currentColor}

/* empty state when search returns nothing */
.empty-state{
  grid-column:1/-1;
  text-align:center;
  padding:80px 20px;
  color:var(--t3);
}
.empty-state svg{width:40px;height:40px;margin:0 auto 16px;opacity:.4}
.empty-state h3{font-size:1.1rem;font-weight:700;margin-bottom:8px;color:var(--t2)}
.empty-state p{font-size:14px}

/* ── BEHIND EVERY WORKER ── */
.behind-bar{background:#fff;border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:clamp(44px,6vw,72px) 0}
.behind-intro{margin-bottom:clamp(32px,4vw,48px);text-align:center}
.behind-intro .sec-p{font-size:1rem;color:var(--t3);line-height:1.7;max-width:480px;margin:0 auto}
.behind-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid var(--border);border-radius:20px;overflow:hidden}
.behind-item{padding:clamp(22px,3vw,34px) clamp(18px,2.5vw,26px);display:flex;flex-direction:column;gap:14px;border-right:1px solid var(--border)}
.behind-item:last-child{border-right:none}
.behind-icon{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;background:var(--soft)}
.behind-icon svg{width:22px;height:22px}
.behind-h{font-size:15.5px;font-weight:700;color:var(--text);line-height:1.35}
.behind-p{font-size:13px;color:var(--t3);line-height:1.65}

/* ── CTA ── */
.cta-foot{background:var(--soft);padding:clamp(44px,6vw,72px) 0}
.cta-foot-inner{
  background:#0D0D0D;
  border-radius:24px;padding:clamp(36px,5vw,56px) clamp(32px,5vw,60px);
  display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap;
  position:relative;overflow:hidden;
}
.cta-foot-inner::before{content:'';position:absolute;right:-80px;top:-80px;width:320px;height:320px;border-radius:50%;background:rgba(255,255,255,.04);pointer-events:none}
.cta-foot-inner::after{content:'';position:absolute;right:100px;bottom:-60px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.03);pointer-events:none}
.cta-foot-left{position:relative;z-index:1}
.cta-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:10px}
.cta-foot-h{font-family:var(--font-h);font-size:clamp(1.5rem,2.8vw,2.2rem);font-weight:800;letter-spacing:-.03em;color:#fff;margin-bottom:6px}
.cta-foot-sub{font-size:14px;color:rgba(255,255,255,.6)}
.cta-foot-right{display:flex;flex-direction:column;align-items:center;gap:8px;position:relative;z-index:1}
.btn-cta-main{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;background:#fff;color:#0D0D0D;box-shadow:0 4px 20px rgba(0,0,0,.2);white-space:nowrap;transition:opacity .15s,transform .15s}
.btn-cta-main:hover{opacity:.95;transform:translateY(-2px)}
.cta-note{font-size:12px;color:rgba(255,255,255,.4)}

/* Nav and footer CSS/JS now live in the public-nav and public-footer
   components (single source of truth), not duplicated per-page. */

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .wk-grid{grid-template-columns:1fr}
  .wk-img-bg{width:44%}
  .behind-grid{grid-template-columns:repeat(2,1fr)}
  .behind-item:nth-child(2){border-right:none}
  .behind-item:nth-child(3){border-right:1px solid var(--border)}
  .behind-item:nth-child(1),.behind-item:nth-child(2){border-bottom:1px solid var(--border)}
}
@media(max-width:768px){
  .hero-page{grid-template-columns:1fr;min-height:auto}
  .hero-page-right{order:-1;min-height:260px}
  .hero-page-spacer{min-height:260px}
  .hero-page-left{padding:36px var(--pad);text-align:center}
  .hero-proof{justify-content:center}
  .toolbar-top{flex-direction:column;align-items:flex-start}
  .search-wrap input{width:100%}
  .search-wrap{width:100%}
  .behind-grid{grid-template-columns:1fr 1fr}
  .cta-foot-inner{flex-direction:column;text-align:center;align-items:center}
  .ft-grid{grid-template-columns:1fr}
  .ft-bottom{flex-direction:column;text-align:center}
}
@media(max-width:480px){
  /* stack card: image on top, content below */
  .wk-card{flex-direction:column;min-height:auto}
  .wk-img-bg{
    position:relative;width:100%;height:220px;
    flex-shrink:0;
  }
  .wk-img-bg img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center top}
  /* fade bottom of image into card background */
  .wk-img-bg::after{
    background:linear-gradient(to bottom,transparent 40%,#fff 100%);
  }
  [data-theme="dark"] .wk-img-bg::after{
    background:linear-gradient(to bottom,transparent 40%,#111 100%);
  }
  .wk-content{width:100%;padding-top:8px}
  /* reset AVA's scale transform on mobile */
  .wk-img-bg img{transform:none!important;transform-origin:unset!important;object-position:center top!important}
  .behind-grid{grid-template-columns:1fr}
  .behind-item{border-right:none!important;border-bottom:1px solid var(--border)}
  .behind-item:last-child{border-bottom:none}
}
</style>
</head>
<body>

@php
  // Nav CTA should never tell an already-onboarded, logged-in user to
  // "Deploy AVA" or "Log in" - both are wrong once they have
  // an account and/or a deployed worker.
  $__navAvaHasDesk = auth()->check() && \Illuminate\Support\Facades\DB::table('worker_deployments')
    ->where('user_id', auth()->id())->where('worker_slug', 'ava')
    ->whereIn('status', ['active', 'paused'])->exists();
@endphp

<x-public-nav :links="[
  ['label' => 'Meet the AI Worker', 'href' => route('public.workers.index'), 'active' => true],
  ['label' => 'How It Works',     'href' => url('/') . '#timeline'],
  ['label' => 'Resources',        'href' => url('/') . '#resources'],
  ['label' => 'Pricing',          'href' => route('pricing')],
]">
  <x-slot:cta>
    @if($__navAvaHasDesk)
    <a href="{{ route('app.desk.ava') }}" class="btn-cta">Go to AVA's Desk <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    @else
    <a href="{{ route('hire.ava.welcome') }}" class="btn-cta">Deploy AVA <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
    @endif
  </x-slot>
  <x-slot:mobileCta>
    @if($__navAvaHasDesk)
    <a href="{{ route('app.desk.ava') }}" class="btn-cta" style="justify-content:center">Go to AVA's Desk →</a>
    @else
    <a href="{{ route('hire.ava.welcome') }}" class="btn-cta" style="justify-content:center">Deploy AVA →</a>
    @endif
  </x-slot>
</x-public-nav>

<!-- HERO -->
<section class="hero-page">
  <div class="hero-page-left">
    <div class="hero-page-inner">
      <div class="page-eye">Meet the AI Agents</div>
      <h1 class="hero-page-h">Specialized AI Agents.<br>Complete Business<br><em>Workflows.</em></h1>
      <p class="hero-page-p">Every UNIT AI Worker owns one operational responsibility from start to finish. Instead of doing a little of everything, each worker becomes exceptional at one business workflow.</p>
      <div class="hero-proof">
        <div class="proof-avs">
          <img src="/images/ava.png" alt="AVA">
          <img src="/images/dox.png" alt="DOX">
          <img src="/images/mox.png" alt="MOX">
          <img src="/images/nux.png" alt="NUX">
        </div>
        <p class="proof-txt"><strong>United by purpose.</strong>Built for results.</p>
      </div>
    </div>
  </div>
  <div class="hero-page-right">
    <span class="hero-page-spacer" aria-hidden="true"></span>
    <img src="/images/hero-team-2.png" alt="AVA, DOX, MOX and NUX: the UNIT AI workforce">
    <div class="hero-fade-page"></div>
    <div class="hero-badge">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M12 2l1.5 4.5H18l-3.75 2.75 1.5 4.5L12 11l-3.75 2.75 1.5-4.5L6 6.5h4.5L12 2z" fill="#F59E0B"/></svg>
      <div class="badge-txt">Real stories. Real work.<br>Real results.</div>
    </div>
  </div>
</section>

<!-- ACTIVITY FEED -->
{{-- AVA-only: DOX/MOX/NUX are not live yet (see roster below), so the
     ticker only shows real workflow stages the live AVA pipeline
     performs - no fabricated activity for Coming Soon workers. Matches
     the homepage ticker for consistency. --}}
<div class="activity-feed">
  <div class="feed-track">
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Renewal drafted for Apex Property Group</span><span class="feed-time">3s ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Follow-up sent to Sunrise LLC · renewal confirmed</span><span class="feed-time">8m ago</span></div>
    <div class="feed-item"><span class="feed-dot blue"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Gmail inbox synced · 2 new renewals detected</span><span class="feed-time">12m ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Invoice prepared for Meridian Realty</span><span class="feed-time">19m ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Payment confirmed · Lakeside Partners</span><span class="feed-time">27m ago</span></div>
    <div class="feed-item"><span class="feed-dot amber"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Renewal flagged for review · unusual terms detected</span><span class="feed-time">34m ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Renewal record updated · Crestview Holdings</span><span class="feed-time">41m ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">3 renewals processed before 9 AM · zero missed</span><span class="feed-time">today</span></div>
    <!-- clone set -->
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Renewal drafted for Apex Property Group</span><span class="feed-time">3s ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Follow-up sent to Sunrise LLC · renewal confirmed</span><span class="feed-time">8m ago</span></div>
    <div class="feed-item"><span class="feed-dot blue"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Gmail inbox synced · 2 new renewals detected</span><span class="feed-time">12m ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Invoice prepared for Meridian Realty</span><span class="feed-time">19m ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Payment confirmed · Lakeside Partners</span><span class="feed-time">27m ago</span></div>
    <div class="feed-item"><span class="feed-dot amber"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Renewal flagged for review · unusual terms detected</span><span class="feed-time">34m ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">Renewal record updated · Crestview Holdings</span><span class="feed-time">41m ago</span></div>
    <div class="feed-item"><span class="feed-dot green"></span><span class="feed-worker" style="color:#0D0D0D">AVA</span><span class="feed-action">3 renewals processed before 9 AM · zero missed</span><span class="feed-time">today</span></div>
  </div>
</div>

<!-- WORKERS -->
<section class="workers-sec" id="workers">
  <div class="w">

    <!-- TOOLBAR -->
    <div class="toolbar">
      <div class="toolbar-top">
        <div class="toolbar-top-left">
          <div class="sec-eye">The Roster</div>
          <h2 class="sec-h">AVA is live. The rest of the roster is coming.</h2>
        </div>
        <div class="search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" id="wk-search" placeholder="Search workers…" autocomplete="off">
        </div>
      </div>
      <div class="tags">
        <span class="tags-label">Specialty:</span>
        <button class="tag-btn on" data-tag="all">All</button>
        <button class="tag-btn" data-tag="renewals">Renewals</button>
        <button class="tag-btn" data-tag="documents">Documents</button>
        <button class="tag-btn" data-tag="brand">Brand</button>
        <button class="tag-btn" data-tag="content">Content</button>
      </div>
    </div>

    <div class="result-count" id="result-count"><span id="count-num">4</span> workers</div>

    <!-- WORKER CARDS -->
    <div class="wk-grid" id="wk-grid">

      <!-- AVA -->
      <div class="wk-card" data-name="ava" data-tags="renewals" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/ava-stand.png" alt="AVA" style="object-position:center 10%;transform:scale(1.45);transform-origin:top center">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
            <div class="wk-name" style="color:#0D0D0D">AVA</div>
          </div>
          <div class="wk-role">AI Renewal Agent</div>
          <p class="wk-quote">Owns your complete renewal lifecycle.</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Tracks upcoming expirations</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Starts renewal campaigns</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Follows up automatically</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Supports invoicing</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Tracks payment confirmation</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Updates renewal records</div>
          </div>
          @php
            $avaHasDesk = auth()->check() && DB::table('worker_deployments')
              ->where('user_id', auth()->id())->where('worker_slug', 'ava')
              ->whereIn('status', ['active', 'paused'])->exists();
          @endphp
          <div class="wk-btns">
            @if($avaHasDesk)
              <a href="{{ route('app.desk.ava') }}" class="btn-hire-wk" style="background:#0D0D0D">AVA's Desk <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            @else
              <a href="{{ route('hire.ava.welcome') }}" class="btn-hire-wk" style="background:#0D0D0D">Deploy AVA <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
            @endif
            <a href="{{ route('public.workers.show', 'ava') }}" class="btn-watch-wk" style="color:#0D0D0D;border-color:#E5E7EB">Watch Ava's Day <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
          </div>
        </div>
      </div>

      <!-- DOX -->
      <div class="wk-card" data-name="dox" data-tags="documents" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/dox.png" alt="DOX" style="object-position:center top">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            </div>
            <div class="wk-name" style="color:#0D0D0D">DOX <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:99px;background:rgba(245,197,24,.15);color:#8a6a06;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;vertical-align:middle">Coming Soon</span></div>
          </div>
          <div class="wk-role">AI Document Agent</div>
          <p class="wk-quote">Owns document organization, retrieval, and structured workflows.</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Organizes files</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Finds what's lost</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Structures systems</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates order</div>
          </div>
          <div class="wk-btns">
            <span class="btn-hire-wk" style="background:#0D0D0D;opacity:.5;cursor:default;pointer-events:none">Coming Soon</span>
          </div>
        </div>
      </div>

      <!-- MOX -->
      <div class="wk-card" data-name="mox" data-tags="brand" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/mox.png" alt="MOX" style="object-position:center top">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </div>
            <div class="wk-name" style="color:#0D0D0D">MOX <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:99px;background:rgba(245,197,24,.15);color:#8a6a06;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;vertical-align:middle">Coming Soon</span></div>
          </div>
          <div class="wk-role">AI Brand Intelligence Agent</div>
          <p class="wk-quote">Finds high-value brand opportunities worth acting on.</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Finds brand moments</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Tracks opportunities</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates campaigns</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Delivers impact</div>
          </div>
          <div class="wk-btns">
            <span class="btn-hire-wk" style="background:#0D0D0D;opacity:.5;cursor:default;pointer-events:none">Coming Soon</span>
          </div>
        </div>
      </div>

      <!-- NUX -->
      <div class="wk-card" data-name="nux" data-tags="content" style="border-top:3px solid #0D0D0D">
        <div class="wk-img-bg">
          <img src="/images/nux.png" alt="NUX" style="object-position:center top">
        </div>
        <div class="wk-content">
          <div class="wk-head">
            <div class="wk-icon" style="background:rgba(0,0,0,.07)">
              <svg viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
            <div class="wk-name" style="color:#0D0D0D">NUX <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:99px;background:rgba(245,197,24,.15);color:#8a6a06;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;vertical-align:middle">Coming Soon</span></div>
          </div>
          <div class="wk-role">AI Publishing Agent</div>
          <p class="wk-quote">Owns your publishing workflow from draft to distribution.</p>
          <div class="wk-bullets">
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Creates content</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Repurposes ideas</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Publishes daily</div>
            <div class="wk-bullet"><div class="wk-check" style="background:#0D0D0D"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>Grows your reach</div>
          </div>
          <div class="wk-btns">
            <span class="btn-hire-wk" style="background:#0D0D0D;opacity:.5;cursor:default;pointer-events:none">Coming Soon</span>
          </div>
        </div>
      </div>

    </div><!-- /wk-grid -->
  </div>
</section>

<!-- BEHIND EVERY WORKER -->
<div class="behind-bar">
  <div class="w">
    <div class="behind-intro">
      <div class="sec-eye">Behind every worker</div>
      <h2 class="sec-h">Real lives. Real work. Real results.</h2>
      <p class="sec-p">Every worker operates with the same commitment: learning, improving, and reporting back after every single task.</p>
    </div>
    <div class="behind-grid">
      <div class="behind-item">
        <div class="behind-icon"><svg class="icon-diary" viewBox="0 0 24 24" fill="none" stroke="#0D0D0D" stroke-width="2" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg></div>
        <div>
          <div class="behind-h">They keep a diary.</div>
          <p class="behind-p">Every worker writes about their day: what they did, what they learned, and what's next. You always know what happened.</p>
        </div>
      </div>
      <div class="behind-item">
        <div class="behind-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
        <div>
          <div class="behind-h">They get better.</div>
          <p class="behind-p">They improve with every task, every challenge, and every win. No retraining required, they just keep getting sharper.</p>
        </div>
      </div>
      <div class="behind-item">
        <div class="behind-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg></div>
        <div>
          <div class="behind-h">They care.</div>
          <p class="behind-p">They take pride in their work because your success is their mission. Every task matters. Every result counts.</p>
        </div>
      </div>
      <div class="behind-item">
        <div class="behind-icon"><svg viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div>
        <div>
          <div class="behind-h">They've never met.</div>
          <p class="behind-p">Each worker believes they're alone at UNIT, for now. As you hire more, they'll learn to collaborate. Stay tuned.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CTA -->
<section class="cta-foot">
  <div class="w">
    <div class="cta-foot-inner">
      <div class="cta-foot-left">
        <div class="cta-eyebrow">Ready to grow?</div>
        <h2 class="cta-foot-h">Ready to deploy your first AI Agent?</h2>
        <p class="cta-foot-sub">Start with one specialized AI Worker. Add more as your business grows.</p>
      </div>
      <div class="cta-foot-right">
        @if($__navAvaHasDesk)
        <a href="{{ route('app.desk.ava') }}" class="btn-cta-main">
          Go to AVA's Desk
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        @else
        <a href="{{ route('hire.ava.welcome') }}" class="btn-cta-main">
          Deploy AVA
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <span class="cta-note">No credit card required.</span>
        @endif
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<x-public-footer />

<script>
// Theme toggle + mobile menu JS now lives in the public-nav component, not duplicated here.

// ── Search + tag filter ──
const searchEl = document.getElementById('wk-search');
const tagBtns  = document.querySelectorAll('.tag-btn');
const cards    = document.querySelectorAll('.wk-card');
const countEl  = document.getElementById('count-num');
const grid     = document.getElementById('wk-grid');

let activeTag = 'all';

function applyFilter(){
  const q = searchEl.value.trim().toLowerCase();
  let visible = 0;

  cards.forEach(card => {
    const name  = card.dataset.name.toLowerCase();
    const tags  = card.dataset.tags.toLowerCase();
    const text  = card.textContent.toLowerCase();
    const tagOk = activeTag === 'all' || tags.includes(activeTag);
    const qOk   = !q || name.includes(q) || text.includes(q) || tags.includes(q);

    if(tagOk && qOk){
      card.style.display = '';
      visible++;
    } else {
      card.style.display = 'none';
    }
  });

  countEl.textContent = visible;

  // empty state
  let empty = document.getElementById('empty-state');
  if(visible === 0){
    if(!empty){
      empty = document.createElement('div');
      empty.id = 'empty-state';
      empty.className = 'empty-state';
      empty.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <h3>No workers found</h3>
        <p>Try a different search term or specialty filter.</p>`;
      grid.appendChild(empty);
    }
  } else if(empty){
    empty.remove();
  }
}

searchEl.addEventListener('input', applyFilter);

tagBtns.forEach(btn => {
  btn.addEventListener('click', function(){
    tagBtns.forEach(b => b.classList.remove('on'));
    this.classList.add('on');
    activeTag = this.dataset.tag;
    applyFilter();
  });
});
</script>

<x-self-learn />

@include('partials.tracking')
</body>
</html>

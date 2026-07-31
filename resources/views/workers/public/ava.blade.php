<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $worker['name'] }}: AI Agent for Renewals | UNIT</title>
<meta name="description" content="{{ $worker['meta_desc'] }}">
<link rel="icon" type="image/png" href="/logo.png">
<link rel="apple-touch-icon" href="/logo.png">
@include('partials.seo-meta', [
    'title'       => $worker['name'] . ': AI Agent for Renewals | UNIT',
    'description' => $worker['meta_desc'],
    'image'       => asset('images/ava-skyline-og.png'),
])
@php
    $__serviceSchema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => $worker['name'] . ': AI Renewal Agent',
        'serviceType' => $worker['category'] ?? $worker['role'],
        'description' => $worker['meta_desc'],
        'image'       => asset('images/ava-skyline-og.png'),
        'url'         => url()->current(),
        'provider'    => [
            '@type' => 'Organization',
            'name'  => 'UNIT',
            'url'   => url('/'),
        ],
    ];
    // aggregateRating/review only ever appear once real approved reviews
    // exist - Google's own guidance is to omit AggregateRating entirely
    // rather than show it with zero backing data.
    if ($reviews->count() > 0) {
        $__serviceSchema['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => round($reviews->avg('rating'), 1),
            'reviewCount' => $reviews->count(),
        ];
        $__serviceSchema['review'] = $reviews->map(fn($r) => [
            '@type'  => 'Review',
            'author' => ['@type' => 'Person', 'name' => $r->author_name],
            'reviewRating' => ['@type' => 'Rating', 'ratingValue' => $r->rating, 'bestRating' => 5],
            'reviewBody' => $r->quote,
        ])->values()->all();
    }
@endphp
<script type="application/ld+json">{!! json_encode($__serviceSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => array_map(fn($f) => [
        '@type' => 'Question',
        'name' => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ], $worker['faq']),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'UNIT', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'AI Workers', 'item' => route('public.workers.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $worker['name'], 'item' => url()->current()],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@@context'    => 'https://schema.org',
    '@type'       => 'HowTo',
    'name'        => "How {$worker['name']} Handles a Renewal",
    'description' => "The real, end-to-end process {$worker['name']} runs on every renewal, from detection to archived proof.",
    'step'        => array_map(fn($s, $i) => [
        '@type'    => 'HowToStep',
        'position' => $i + 1,
        'name'     => $s['title'],
        'text'     => $s['desc'],
    ], $worker['pipeline'], array_keys($worker['pipeline'])),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@@context'     => 'https://schema.org',
    '@type'        => 'VideoObject',
    'name'         => "Meet {$worker['name']}, UNIT's {$worker['role']}",
    'description'  => $worker['hero_video']['transcript'],
    'thumbnailUrl' => asset($worker['hero_video']['thumbnail']),
    'contentUrl'   => asset($worker['hero_video']['url']),
    'uploadDate'   => $worker['hero_video']['upload_date'],
    'duration'     => $worker['hero_video']['duration_iso'],
    'transcript'   => $worker['hero_video']['transcript'],
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

@php
  $color    = '#F5C518';
  $colorRgb = '245,197,24';
@endphp

:root{
  --brand:{{ $color }};
  --brand-rgb:{{ $colorRgb }};
  --text:#0D0D0D;--t2:#374151;--t3:#6B7280;--t4:#9CA3AF;
  --border:#E5E7EB;--bg:#FFFFFF;--soft:#F8F8F6;
  --font:'Inter',sans-serif;
  --max:1160px;--pad:clamp(20px,5vw,48px);
}
[data-theme="dark"]{
  --text:#F3F4F6;--t2:#D1D5DB;--t3:#9CA3AF;--t4:#6B7280;
  --border:#2D2D2D;--bg:#0D0D0D;--soft:#161616;
}

body{font-family:var(--font);color:var(--text);background:var(--bg);-webkit-font-smoothing:antialiased;overflow-x:hidden}
.w{max-width:var(--max);margin:0 auto;padding:0 var(--pad)}
/* Gold underline - the ONLY use of --brand on this page */
.hl{position:relative;display:inline}
.hl::after{content:'';position:absolute;left:0;right:0;bottom:-3px;height:4px;background:var(--brand);border-radius:2px}

/* ── NAV - identical structure to / and /ai-workers (shared x-public-nav) ── */
.nav{position:fixed;top:0;left:0;right:0;z-index:100;background:rgba(255,255,255,.92);backdrop-filter:blur(16px);border-bottom:1px solid var(--border)}
[data-theme="dark"] .nav{background:rgba(13,13,13,.92);border-color:#2D2D2D}
.nav-i{display:flex;align-items:center;justify-content:space-between;height:62px}
.logo{display:flex;align-items:center}
.logo-name{font-size:1.25rem;font-weight:800;color:var(--text);letter-spacing:-.5px}
.nav-links{display:flex;align-items:center;gap:28px;list-style:none}
.nav-links a{font-size:13.5px;font-weight:500;color:var(--t2);transition:color .15s}
.nav-links a:hover{color:var(--text)}
.nav-links a.active{font-weight:700;color:var(--text)}
.nav-acts{display:flex;align-items:center;gap:10px}
.btn-login{padding:8px 18px;border-radius:8px;font-size:13.5px;font-weight:600;color:var(--t2);border:1px solid var(--border);transition:all .15s}
.btn-login:hover{border-color:#9CA3AF;color:var(--text)}
.btn-cta{padding:9px 20px;border-radius:99px;font-size:13.5px;font-weight:700;background:#0D0D0D;color:#fff;display:inline-flex;align-items:center;gap:7px;box-shadow:0 2px 12px rgba(0,0,0,.12);transition:opacity .15s,transform .15s;white-space:nowrap}
.btn-cta:hover{opacity:.9;transform:translateY(-1px)}
.btn-cta svg{flex-shrink:0;transition:transform .15s}
.btn-cta:hover svg{transform:translateX(2px)}
[data-theme="dark"] .btn-cta{background:#fff;color:#0D0D0D}
.ham{display:none;flex-direction:column;gap:5px;padding:4px;background:none;border:none}
.ham span{display:block;width:22px;height:2px;background:var(--text);border-radius:2px}
.theme-toggle{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid var(--border);background:transparent;color:var(--t2);cursor:pointer;transition:all .2s;flex-shrink:0}
.theme-toggle:hover{background:var(--soft);color:var(--text)}
.theme-toggle svg{width:15px;height:15px}
.icon-sun{display:none}.icon-moon{display:block}
[data-theme="dark"] .icon-sun{display:block}[data-theme="dark"] .icon-moon{display:none}
@media(max-width:960px){.nav-links,.nav-acts{display:none}.ham{display:flex}}

/* ── MOBILE MENU - identical to / and /ai-workers ── */
.mob-menu{display:none;position:fixed;inset:0;z-index:200;background:#fff;flex-direction:column;padding:24px var(--pad)}
[data-theme="dark"] .mob-menu{background:#0D0D0D}
.mob-menu.open{display:flex}
.mob-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:36px}
.mob-close{font-size:22px;color:var(--t3);padding:4px}
.mob-links{display:flex;flex-direction:column;list-style:none}
.mob-links a{display:block;padding:14px 0;font-size:1.05rem;font-weight:600;color:var(--t2);border-bottom:1px solid var(--border)}
.mob-ctas{margin-top:28px;display:flex;flex-direction:column;gap:10px}

/* ── FOOTER - identical to / and /ai-workers ── */
.footer{background:#0A0A0A;padding:clamp(40px,6vw,72px) 0 28px}
.ft-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:44px;margin-bottom:44px}
.ft-name{font-size:1.15rem;font-weight:800;color:#fff;margin-bottom:10px}
.ft-desc{font-size:13.5px;color:rgba(255,255,255,.6);line-height:1.7;max-width:220px;margin-bottom:20px}
.ft-col-h{font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:14px}
.ft-links{display:flex;flex-direction:column;gap:9px}
.ft-links a{font-size:13.5px;color:rgba(255,255,255,.7);transition:color .15s}
.ft-links a:hover{color:#fff}
.ft-bottom{border-top:1px solid rgba(255,255,255,.12);padding-top:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
.ft-bottom p{font-size:12.5px;color:rgba(255,255,255,.45)}

/* ── HERO ── */
.hero-worker{
  display:flex;flex-direction:row;
  height:calc(100vh - 62px);
  min-height:560px;
  margin-top:62px;
  background:#0A0A0F;
  overflow:hidden;
}

/* LEFT: video column */
.hero-video-col{
  position:relative;
  flex:1;min-width:0;
  display:flex;flex-direction:column;
  overflow:hidden;
}
.hero-media{
  position:absolute;inset:0;
  display:flex;
}
.hero-media video,
.hero-media img{
  width:100%;height:100%;
  object-fit:cover;object-position:center top;
}
/* gradient: left side dark for text readability */
.hero-media::after{
  content:'';position:absolute;inset:0;
  background:
    linear-gradient(to right, rgba(6,4,15,.88) 0%, rgba(6,4,15,.55) 35%, rgba(6,4,15,.1) 62%, transparent 80%),
    linear-gradient(to top, rgba(6,4,15,.6) 0%, transparent 35%);
}
/* text content over the video */
.hero-text{
  position:relative;z-index:2;
  flex:1;display:flex;flex-direction:column;justify-content:flex-end;
  padding:clamp(32px,5vw,56px) clamp(28px,4vw,48px);
  padding-bottom:clamp(32px,5vw,56px);
}
.hero-copy{
  transition:opacity .35s ease,transform .35s ease,max-height .35s ease;
  max-height:400px;opacity:1;overflow:hidden;
}
.hero-copy.hero-copy-hidden{
  opacity:0;transform:translateY(-8px);max-height:0;pointer-events:none;
}
.hero-eye{
  font-size:11px;font-weight:700;letter-spacing:.16em;
  text-transform:uppercase;color:rgba(255,255,255,.5);
  margin-bottom:14px;
}
.hero-h{
  font-size:clamp(2rem,4.2vw,3.4rem);
  font-weight:800;line-height:1.06;
  letter-spacing:-.03em;color:#fff;
  margin-bottom:16px;
}
.hero-p{
  font-size:.95rem;color:rgba(255,255,255,.7);
  line-height:1.75;margin-bottom:28px;max-width:400px;
}
.hero-btns{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.hero-transcript{margin-top:14px}
.hero-transcript summary{
  font-size:12px;color:rgba(255,255,255,.5);cursor:pointer;
  list-style:none;display:inline-flex;align-items:center;gap:5px;
  transition:color .15s;
}
.hero-transcript summary::-webkit-details-marker{display:none}
.hero-transcript summary:hover{color:rgba(255,255,255,.8)}
.hero-transcript summary::before{content:'▸';font-size:10px}
.hero-transcript[open] summary::before{content:'▾'}
.hero-transcript p{font-size:12.5px;color:rgba(255,255,255,.6);line-height:1.65;margin-top:8px;max-width:420px}
.btn-hire-hero{
  display:inline-flex;align-items:center;gap:8px;
  padding:12px 22px;border-radius:10px;
  font-size:14.5px;font-weight:700;color:#fff;
  background:#0D0D0D;
  transition:opacity .15s,transform .15s;
}
.btn-hire-hero:hover{opacity:.9;transform:translateY(-1px)}
.btn-watch-hero{
  display:inline-flex;align-items:center;gap:9px;
  padding:11px 18px;border-radius:10px;
  font-size:14px;font-weight:600;color:rgba(255,255,255,.88);
  border:1.5px solid rgba(255,255,255,.22);
  transition:border-color .15s,background .15s;
}
.btn-watch-hero:hover{border-color:rgba(255,255,255,.5);background:rgba(255,255,255,.05)}
.btn-watch-icon{
  width:26px;height:26px;border-radius:50%;
  border:1.5px solid rgba(255,255,255,.4);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.btn-watch-icon svg{width:10px;height:10px;fill:#fff;margin-left:2px}

/* RIGHT: status panel - separate column, not overlay */
.hero-panel{
  width:300px;flex-shrink:0;
  background:#111118;
  border-left:1px solid rgba(255,255,255,.07);
  display:flex;flex-direction:column;
  padding:32px 24px 24px;
  overflow-y:auto;
}
.hc-status{display:flex;align-items:center;gap:8px;margin-bottom:20px}
.hc-dot{
  width:8px;height:8px;border-radius:50%;
  background:#22C55E;box-shadow:0 0 8px rgba(34,197,94,.8);
  flex-shrink:0;animation:pulse 2s ease infinite;
}
@keyframes pulse{0%,100%{opacity:1;box-shadow:0 0 8px rgba(34,197,94,.8)}50%{opacity:.7;box-shadow:0 0 16px rgba(34,197,94,.4)}}
.hc-status-txt{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#22C55E}
.hc-time{
  font-size:2.4rem;font-weight:800;color:#fff;
  letter-spacing:-.04em;line-height:1;
  font-variant-numeric:tabular-nums;margin-bottom:20px;
}
.hc-task-label{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:8px}
.hc-task-name{font-size:13.5px;font-weight:500;color:rgba(255,255,255,.85);line-height:1.5;margin-bottom:12px}
.hc-task-icon{
  width:40px;height:40px;border-radius:10px;
  border:1px solid rgba(255,255,255,.15);
  background:rgba(255,255,255,.08);
  display:flex;align-items:center;justify-content:center;
  margin-bottom:20px;
}
.hc-task-icon svg{width:20px;height:20px;stroke:#fff;fill:none;stroke-width:1.8}
.hc-divider{height:1px;background:rgba(255,255,255,.07);margin:4px 0 16px}
.hc-completed-label{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:10px}
.hc-done-item{display:flex;align-items:center;gap:9px;margin-bottom:8px;font-size:13px;color:rgba(255,255,255,.8)}
.hc-check{
  width:20px;height:20px;border-radius:50%;flex-shrink:0;
  border:1.5px solid #22C55E;
  display:flex;align-items:center;justify-content:center;
}
.hc-check svg{width:10px;height:10px;stroke:#22C55E;stroke-width:2.5;fill:none}
.hc-revenue-label{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-top:20px;margin-bottom:6px}
.hc-revenue-amount{font-size:2rem;font-weight:800;color:#fff;letter-spacing:-.04em;line-height:1;margin-bottom:4px}
.hc-revenue-streak{font-size:13px;color:rgba(255,255,255,.5);margin-bottom:20px}
.hc-feed-btn{
  display:flex;align-items:center;justify-content:center;gap:7px;
  width:100%;padding:11px 16px;
  border-radius:10px;
  font-size:13.5px;font-weight:600;color:rgba(255,255,255,.8);
  border:1px solid rgba(255,255,255,.14);
  background:rgba(255,255,255,.04);
  transition:all .15s;margin-top:auto;
}
.hc-feed-btn:hover{background:rgba(255,255,255,.09);color:#fff}
@media(max-width:900px){
  .hero-worker{flex-direction:column;height:auto}
  .hero-video-col{min-height:55vh}
  .hero-panel{width:100%;border-left:none;border-top:1px solid rgba(255,255,255,.07);flex-direction:row;flex-wrap:wrap;gap:20px;padding:20px}
}
@media(max-width:600px){
  .hero-panel{flex-direction:column}
}

/* hero status card */
.hero-card{
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12);
  border-radius:20px;
  padding:24px;
  backdrop-filter:blur(20px);
}
.hc-status{display:flex;align-items:center;gap:8px;margin-bottom:18px}
.hc-dot{width:8px;height:8px;border-radius:50%;background:#22C55E;box-shadow:0 0 8px rgba(34,197,94,.8);flex-shrink:0;animation:pulse 2s ease infinite}
@keyframes pulse{0%,100%{opacity:1;box-shadow:0 0 8px rgba(34,197,94,.8)}50%{opacity:.7;box-shadow:0 0 16px rgba(34,197,94,.5)}}
.hc-status-txt{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#22C55E}
.hc-time{font-size:2.2rem;font-weight:800;color:#fff;letter-spacing:-.04em;margin-bottom:4px;font-variant-numeric:tabular-nums}
.hc-task-label{font-size:10.5px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:8px;margin-top:16px}
.hc-task{
  background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
  border-radius:12px;padding:12px 14px;margin-bottom:16px;
}
.hc-task-name{font-size:13px;font-weight:600;color:#fff;margin-bottom:8px}
.hc-progress{height:4px;background:rgba(255,255,255,.1);border-radius:99px;overflow:hidden}
.hc-progress-bar{height:100%;background:rgba(255,255,255,.7);border-radius:99px;width:68%;animation:progAnim 3s ease-in-out infinite alternate}
@keyframes progAnim{from{width:55%}to{width:82%}}
.hc-divider{height:1px;background:rgba(255,255,255,.08);margin:14px 0}
.hc-completed-label{font-size:10.5px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:10px}
.hc-done-item{display:flex;align-items:center;gap:8px;margin-bottom:7px;font-size:13px;color:rgba(255,255,255,.8)}
.hc-check{width:18px;height:18px;border-radius:50%;background:#22C55E;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.hc-check svg{width:10px;height:10px;stroke:#fff;stroke-width:2.5;fill:none}
.hc-revenue{
  background:rgba(255,255,255,.05);
  border:1px solid rgba(255,255,255,.1);
  border-radius:12px;padding:14px;margin-top:14px;
}
.hc-rev-label{font-size:10.5px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:6px}
.hc-rev-amount{font-size:1.6rem;font-weight:800;color:#fff;letter-spacing:-.04em;line-height:1}
.hc-rev-streak{font-size:12px;color:rgba(255,255,255,.5);margin-top:4px}
.hc-feed-btn{
  display:flex;align-items:center;justify-content:center;gap:7px;
  width:100%;margin-top:14px;
  padding:10px;border-radius:10px;
  font-size:13px;font-weight:600;color:rgba(255,255,255,.7);
  border:1px solid rgba(255,255,255,.12);
  transition:all .15s;background:rgba(255,255,255,.04);
}
.hc-feed-btn:hover{background:rgba(255,255,255,.08);color:#fff}

/* ── SECTION SHARED ── */
.sec{padding:clamp(56px,7vw,88px) 0}
.sec-eye{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--t3);margin-bottom:12px}
.sec-h{font-size:clamp(1.6rem,3vw,2.4rem);font-weight:800;line-height:1.12;letter-spacing:-.03em;color:var(--text);margin-bottom:14px}
.sec-p{font-size:1rem;color:var(--t3);line-height:1.7}
.center{text-align:center}.center .sec-p{max-width:520px;margin:0 auto}

/* ── PROBLEM SECTION ── */
.problem-sec{background:#fff;padding:clamp(60px,8vw,96px) 0 0}
[data-theme="dark"] .problem-sec{background:#0D0D0D}
/* centered headline */
.prob-top{text-align:center;margin-bottom:clamp(40px,5vw,64px)}
.prob-top-eye{
  display:inline-flex;align-items:center;gap:6px;
  font-size:11px;font-weight:700;letter-spacing:.13em;text-transform:uppercase;
  color:var(--t3);margin-bottom:18px;
}
.prob-top-h{font-size:clamp(2.2rem,4.5vw,3.6rem);font-weight:800;line-height:1.08;letter-spacing:-.03em;color:var(--text);margin-bottom:0}
.prob-top-h span{color:var(--text)}
.prob-top-sub{font-size:1.05rem;color:var(--t3);margin-top:14px}
/* full-width problem grid */
.prob-split{
  padding:0 var(--pad);
  max-width:var(--max);
  margin:0 auto;
}
/* LEFT - flat, no card wrapper */
.prob-left-header{display:flex;align-items:flex-start;gap:14px;margin-bottom:28px}
.prob-left-icon{
  width:38px;height:38px;border-radius:50%;flex-shrink:0;
  background:#0D0D0D;
  display:flex;align-items:center;justify-content:center;margin-top:2px;
}
.prob-left-icon svg{width:16px;height:16px;stroke:#fff;fill:none;stroke-width:2.5;stroke-linecap:round}
.prob-left-title{font-size:19px;font-weight:800;color:var(--text);line-height:1.2}
.prob-left-sub{font-size:13px;color:var(--t3);margin-top:3px}
/* 4-col × 2-row problem grid - big open cards */
.prob-items{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.prob-item{
  background:#fff;border:1.5px solid #E5E7EB;
  border-radius:20px;padding:32px 28px;
  display:flex;flex-direction:column;gap:16px;
}
[data-theme="dark"] .prob-item{background:#111;border-color:#2D2D2D}
.prob-item-icon{
  width:56px;height:56px;border-radius:16px;flex-shrink:0;
  background:#F3F4F6;
  display:flex;align-items:center;justify-content:center;
}
[data-theme="dark"] .prob-item-icon{background:#1a1a1a}
.prob-item-icon svg{width:28px;height:28px;stroke:#374151;fill:none;stroke-width:1.6;stroke-linecap:round}
[data-theme="dark"] .prob-item-icon svg{stroke:#9CA3AF}
.prob-item-h{font-size:16px;font-weight:700;color:var(--text);line-height:1.3}
.prob-item-p{font-size:13.5px;color:var(--t3);line-height:1.6}
/* impact banner below items */
.prob-impact{
  display:flex;align-items:center;gap:14px;
  margin-top:20px;
  background:#F9F9FB;border:1.5px solid #E5E7EB;
  border-radius:14px;padding:16px 20px;
}
[data-theme="dark"] .prob-impact{background:#111;border-color:#2D2D2D}
.prob-impact-icon{
  width:40px;height:40px;border-radius:10px;flex-shrink:0;
  background:#F3F4F6;display:flex;align-items:center;justify-content:center;
}
[data-theme="dark"] .prob-impact-icon{background:#1a1a1a}
.prob-impact-icon svg{width:20px;height:20px;stroke:#374151;fill:none;stroke-width:1.8;stroke-linecap:round}
[data-theme="dark"] .prob-impact-icon svg{stroke:#9CA3AF}
.prob-impact-text{font-size:14px;color:var(--text);line-height:1.55}
.prob-impact-text span{color:var(--text);font-weight:700}
.prob-related-link{font-size:13px;color:var(--t3);margin-top:18px}
.prob-related-link a{color:var(--text);font-weight:600;text-decoration:underline;text-underline-offset:2px}
/* RIGHT - solution panel */
.sol-panel{
  background:#F8F8FD;border:1.5px solid #E5E7EB;
  border-radius:20px;padding:24px 22px;
}
[data-theme="dark"] .sol-panel{background:#111;border-color:#2D2D2D}
.sol-pill{
  display:inline-flex;align-items:center;gap:7px;
  background:#0D0D0D;color:#fff;
  font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  padding:6px 14px;border-radius:99px;margin-bottom:18px;
}
.sol-meet{font-size:26px;font-weight:800;color:#0D0D0D;margin-bottom:3px;line-height:1.1}
[data-theme="dark"] .sol-meet{color:#F3F4F6}
.sol-meet span{position:relative}
.sol-meet span::after{content:'';position:absolute;left:0;right:0;bottom:-2px;height:3px;background:var(--brand);border-radius:2px}
.sol-role{font-size:14px;color:var(--t3);margin-bottom:20px}
.sol-checks{display:flex;flex-direction:column;gap:10px;margin-bottom:20px}
.sol-check{display:flex;align-items:center;gap:10px;font-size:14px;color:#0D0D0D}
[data-theme="dark"] .sol-check{color:#F3F4F6}
.sol-chk{
  width:22px;height:22px;border-radius:50%;flex-shrink:0;
  background:#0D0D0D;
  display:flex;align-items:center;justify-content:center;
}
.sol-chk svg{width:11px;height:11px;stroke:#fff;stroke-width:2.5;fill:none;stroke-linecap:round}
.sol-peace{
  display:flex;align-items:flex-start;gap:12px;
  background:#fff;border:1.5px solid #E5E7EB;
  border-radius:14px;padding:16px;
}
[data-theme="dark"] .sol-peace{background:#1a1a1a;border-color:#3D3D3D}
.sol-peace-icon{width:36px;height:36px;border-radius:10px;flex-shrink:0;background:rgba(0,0,0,.05);display:flex;align-items:center;justify-content:center}
[data-theme="dark"] .sol-peace-icon{background:rgba(255,255,255,.08)}
.sol-peace-icon svg{width:18px;height:18px;stroke:#0D0D0D;fill:none;stroke-width:2;stroke-linecap:round}
[data-theme="dark"] .sol-peace-icon svg{stroke:#F3F4F6}
.sol-peace-h{font-size:14px;font-weight:700;color:#0D0D0D;margin-bottom:4px}
[data-theme="dark"] .sol-peace-h{color:#F3F4F6}
.sol-peace-p{font-size:12.5px;color:var(--t3);line-height:1.55}
.btn-prob-cta{
  display:inline-flex;align-items:center;gap:8px;
  padding:14px 28px;border-radius:10px;
  font-size:15px;font-weight:700;color:#fff;
  background:#0D0D0D;
  transition:opacity .15s,transform .15s;white-space:nowrap;flex-shrink:0;
}
.btn-prob-cta:hover{opacity:.9;transform:translateY(-1px)}
.btn-prob-cta svg{flex-shrink:0}
@media(max-width:900px){.prob-items{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.prob-items{grid-template-columns:1fr}}

/* ── DAY IN LIFE / PIPELINE ── */
.day-sec{background:#F9F9FB;padding:clamp(56px,7vw,88px) 0}
[data-theme="dark"] .day-sec{background:#111}
.day-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:clamp(36px,5vw,56px)}
.day-client{text-align:right}
.day-client-name{font-size:14px;font-weight:700;color:var(--text)}
.day-client-sub{font-size:12.5px;color:var(--t4);margin-top:2px}
/* pipeline row */
/* spinning border container */
@property --pipe-angle{syntax:'<angle>';inherits:false;initial-value:0deg}
.pipe-wrap{
  position:relative;
  border-radius:20px;
  padding:2px;
  background:
    conic-gradient(from var(--pipe-angle),
      #E5E7EB 0%,#E5E7EB 80%,
      #0D0D0D 87%,
      #E5E7EB 93%,#E5E7EB 100%)
    border-box;
  animation:pipeSpin 4s linear infinite;
  margin-bottom:32px;
}
[data-theme="dark"] .pipe-wrap{
  background:
    conic-gradient(from var(--pipe-angle),
      #2D2D2D 0%,#2D2D2D 80%,
      #fff 87%,
      #2D2D2D 93%,#2D2D2D 100%)
    border-box;
}
@keyframes pipeSpin{to{--pipe-angle:360deg}}
.pipe-wrap-inner{
  background:#fff;border-radius:18px;
  padding:28px 24px 20px;
}
[data-theme="dark"] .pipe-wrap-inner{background:#161616}
.pipeline-row{
  display:flex;align-items:flex-start;
  gap:0;
  overflow-x:auto;padding-bottom:4px;
}
.pipeline-row::-webkit-scrollbar{height:4px}
.pipeline-row::-webkit-scrollbar-thumb{background:var(--border);border-radius:2px}
.pipe-step{
  display:flex;flex-direction:column;align-items:center;
  text-align:center;flex:1;min-width:96px;position:relative;
}
/* arrow connector */
.pipe-step:not(:last-child)::after{
  content:'→';
  position:absolute;top:32px;left:calc(50% + 38px);
  font-size:16px;color:var(--border);
  line-height:1;z-index:1;pointer-events:none;
}
[data-theme="dark"] .pipe-step:not(:last-child)::after{color:#3D3D3D}
.pipe-step.ps-active:not(:last-child)::after{color:#0D0D0D;opacity:.5}
/* node */
.pipe-node{
  width:68px;height:68px;border-radius:16px;
  background:#fff;border:1.5px solid #E5E7EB;
  display:flex;align-items:center;justify-content:center;
  margin-bottom:12px;position:relative;
  box-shadow:0 1px 4px rgba(0,0,0,.06);
  transition:border-color .3s,box-shadow .3s,background .3s;
  flex-shrink:0;
}
[data-theme="dark"] .pipe-node{background:#1a1a1a;border-color:#3D3D3D}
.pipe-node svg{width:24px;height:24px;stroke:#BCC0C9;fill:none;stroke-width:1.7;stroke-linecap:round;transition:stroke .3s}
/* number badge */
.pipe-badge{
  position:absolute;bottom:-1px;right:-1px;
  width:20px;height:20px;border-radius:6px 0 0 0;
  background:#D1D5DB;
  font-size:10px;font-weight:800;color:#fff;
  display:flex;align-items:center;justify-content:center;
  transition:background .3s;
}
/* step label + time */
.pipe-label{
  font-size:10px;font-weight:700;letter-spacing:.09em;
  text-transform:uppercase;color:#9CA3AF;margin-bottom:6px;
  transition:color .3s;line-height:1.35;
}
.pipe-time{
  font-size:11px;font-weight:600;color:transparent;
  font-variant-numeric:tabular-nums;min-height:16px;
  transition:color .3s;letter-spacing:.04em;text-transform:uppercase;
}
.pipe-step.ps-running .pipe-time.pipe-who-you,
.pipe-step.ps-done .pipe-time.pipe-who-you{color:#B45309}
[data-theme="dark"] .pipe-step.ps-running .pipe-time.pipe-who-you,
[data-theme="dark"] .pipe-step.ps-done .pipe-time.pipe-who-you{color:#F5C518}
/* check circle inside node - hidden by default */
.pipe-check{
  display:none;
  width:34px;height:34px;border-radius:50%;
  background:#22C55E;
  align-items:center;justify-content:center;flex-shrink:0;
}
.pipe-check svg{width:16px;height:16px;stroke:#fff;stroke-width:2.5;fill:none;stroke-linecap:round}
/* running state */
.pipe-step.ps-running .pipe-node{
  border-color:#0D0D0D;border-width:2px;
  box-shadow:0 0 0 3px rgba(0,0,0,.08);
  background:#fff;
}
.pipe-step.ps-running .pipe-node svg{stroke:#0D0D0D}
.pipe-step.ps-running .pipe-badge{background:#0D0D0D;animation:badgePulse 1s ease infinite}
@keyframes badgePulse{0%,100%{box-shadow:0 0 0 0 rgba(0,0,0,.3)}50%{box-shadow:0 0 0 5px rgba(0,0,0,0)}}
.pipe-step.ps-running .pipe-label{color:#0D0D0D;font-weight:800}
.pipe-step.ps-running .pipe-time{color:#0D0D0D}
/* done state - bold black border stays; icon goes green; time stays black */
.pipe-step.ps-done .pipe-node{border-color:#0D0D0D;border-width:2px;background:#fff}
.pipe-step.ps-done .pipe-node svg{display:block;stroke:#22C55E}
.pipe-step.ps-done .pipe-badge{background:#0D0D0D}
.pipe-step.ps-done .pipe-label{color:var(--t3)}
.pipe-step.ps-done .pipe-time{color:#0D0D0D;font-weight:600}
/* ticker - sits below all step text, with breathing room */
.pipe-ticker-row{
  min-height:32px;margin-top:24px;margin-bottom:8px;
  display:flex;align-items:center;justify-content:center;gap:8px;
  font-size:13px;color:#0D0D0D;font-weight:600;
}
.pipe-ticker-dot{
  width:7px;height:7px;border-radius:50%;background:#0D0D0D;
  animation:tickDot 1s ease infinite;flex-shrink:0;
}
@keyframes tickDot{0%,100%{opacity:1}50%{opacity:.3}}
/* mission complete bar */
.mission-bar{
  display:flex;align-items:center;justify-content:space-between;
  background:#fff;border:1.5px solid #E5E7EB;
  border-radius:16px;padding:20px 28px;flex-wrap:wrap;gap:16px;
  box-shadow:0 1px 4px rgba(0,0,0,.04);
  opacity:0;transform:translateY(8px);
  transition:opacity .4s,transform .4s;
  margin-top:8px;
}
[data-theme="dark"] .mission-bar{background:#1a1a1a;border-color:#2D2D2D}
.mission-bar.visible{opacity:1;transform:translateY(0)}
.mission-txt{font-size:17px;font-weight:700;color:var(--text)}
/* CTA buttons below pipeline */
.pipe-cta{display:flex;align-items:center;gap:12px;margin-top:28px;flex-wrap:wrap;width:fit-content}
.btn-pipe-hire{
  display:inline-flex;align-items:center;gap:8px;
  padding:13px 26px;border-radius:10px;
  font-size:14.5px;font-weight:700;color:#fff;
  background:#0D0D0D;
  transition:opacity .15s,transform .15s;
}
.btn-pipe-hire:hover{opacity:.8;transform:translateY(-1px)}
.btn-pipe-test{
  display:inline-flex;align-items:center;gap:8px;
  padding:12px 22px;border-radius:10px;
  font-size:14.5px;font-weight:600;color:#0D0D0D;
  border:1.5px solid #D1D5DB;
  background:transparent;
  transition:all .15s;
}
.btn-pipe-test:hover{background:#0D0D0D;color:#fff;border-color:#0D0D0D}
.btn-pipe-test svg,.btn-pipe-hire svg{flex-shrink:0}
@media(max-width:900px){
  .pipe-step{min-width:80px}
  .pipe-step:not(:last-child)::after{left:calc(50% + 30px)}
}
@media(max-width:640px){
  .pipeline-row{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;overflow:visible}
  .pipe-step{min-width:0}
  .pipe-node{width:56px;height:56px;border-radius:12px}
  .pipe-step:not(:last-child)::after{display:none}
}

/* ── TWO-COL SPLIT ── */
.split-sec{background:var(--bg);border-top:1px solid var(--border)}
[data-theme="dark"] .split-sec{background:#0D0D0D;border-color:#2D2D2D}
.split-grid{display:grid;grid-template-columns:1fr 1fr;gap:clamp(40px,6vw,72px);align-items:start}

/* ── THE PARTS EVERYONE SKIPS - escalation + archive ── */
.edge-sec{background:var(--soft);border-top:1px solid var(--border);padding:clamp(56px,7vw,88px) 0}
[data-theme="dark"] .edge-sec{background:#141414;border-color:#2D2D2D}
.edge-head{text-align:center;max-width:680px;margin:0 auto 44px}
.edge-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:clamp(20px,2.5vw,28px)}
.edge-card{background:var(--bg);border:1px solid var(--border);border-radius:20px;overflow:hidden}
[data-theme="dark"] .edge-card{background:#0D0D0D;border-color:#2D2D2D}
.edge-img{aspect-ratio:16/10;overflow:hidden}
.edge-img img{width:100%;height:100%;object-fit:cover}
.edge-body{padding:24px}
.edge-eye{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);margin-bottom:10px}
.edge-h3{font-size:1.15rem;font-weight:800;letter-spacing:-.02em;color:var(--text);margin-bottom:10px;line-height:1.25}
.edge-p{font-size:14px;color:var(--t2);line-height:1.68}
.edge-btns{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px}
.btn-edge-proof,.btn-edge-watch{
  display:inline-flex;align-items:center;gap:6px;
  font-size:12.5px;font-weight:700;
  padding:9px 14px;border-radius:8px;
  border:1px solid var(--border);background:transparent;color:var(--text);
  cursor:pointer;transition:background .15s,border-color .15s;
}
.btn-edge-proof:hover,.btn-edge-watch:hover{background:var(--soft);border-color:var(--t4)}
[data-theme="dark"] .btn-edge-proof,[data-theme="dark"] .btn-edge-watch{border-color:#2D2D2D}
@media(max-width:1024px){.edge-grid{grid-template-columns:1fr 1fr}}
@media(max-width:760px){.edge-grid{grid-template-columns:1fr}}

/* ── EDGE STATEMENT MODAL (proof screenshots + worker-explainer video) ── */
.edge-modal{
  position:fixed;inset:0;z-index:500;
  display:none;align-items:center;justify-content:center;
  padding:24px;
}
.edge-modal.open{display:flex}
.edge-modal-backdrop{position:absolute;inset:0;background:rgba(13,13,13,.7);backdrop-filter:blur(4px)}
.edge-modal-box{
  position:relative;background:var(--bg);border-radius:20px;
  max-width:720px;width:100%;max-height:85vh;overflow-y:auto;
  padding:20px;box-shadow:0 24px 64px rgba(0,0,0,.35);
}
[data-theme="dark"] .edge-modal-box{background:#161616;border:1px solid #2D2D2D}
.edge-modal-close{
  position:absolute;top:14px;right:14px;z-index:2;
  width:32px;height:32px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  background:var(--soft);border:1px solid var(--border);color:var(--text);
  cursor:pointer;
}
[data-theme="dark"] .edge-modal-close{background:#1a1a1a;border-color:#2D2D2D}
.edge-modal-label{font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--t3);margin-bottom:12px;padding-right:36px}
.edge-modal-img{width:100%;border-radius:12px;border:1px solid var(--border);display:block}
.edge-modal-video{width:100%;border-radius:12px;display:block;background:#000}
.edge-modal-novideo{text-align:center;padding:32px 16px;color:var(--t3)}
.edge-modal-novideo-h{font-size:16px;font-weight:800;color:var(--text);margin-top:14px;margin-bottom:8px}
.edge-modal-novideo-p{font-size:13.5px;line-height:1.6;max-width:420px;margin:0 auto}

/* ── MEET AVA - first-person narrative ── */
.meet-sec{background:var(--bg);border-top:1px solid var(--border);padding:clamp(56px,7vw,88px) 0}
[data-theme="dark"] .meet-sec{background:#0D0D0D;border-color:#2D2D2D}
.meet-grid{display:grid;grid-template-columns:.85fr 1.15fr;gap:clamp(36px,5vw,64px);align-items:center}
.meet-img-wrap{border-radius:20px;overflow:hidden;border:1px solid var(--border)}
.meet-img-wrap img{width:100%;height:100%;object-fit:cover;object-position:center 20%;max-height:520px}
.meet-quote{font-size:clamp(1.5rem,2.6vw,2.1rem);font-weight:700;line-height:1.32;letter-spacing:-.02em;color:var(--text);margin-bottom:20px}
.meet-body p{font-size:15px;color:var(--t2);line-height:1.8;margin-bottom:16px}
.meet-sig{font-size:13px;color:var(--t4);margin-top:20px;font-style:italic}
@media(max-width:860px){.meet-grid{grid-template-columns:1fr}.meet-img-wrap img{max-height:360px}}

/* ── MEMORY - the tenant-data differentiator ── */
.mem-sec{background:var(--soft);border-top:1px solid var(--border);padding:clamp(56px,7vw,88px) 0}
[data-theme="dark"] .mem-sec{background:#141414;border-color:#2D2D2D}
.mem-head{max-width:680px;margin:0 auto 44px;text-align:center}
.mem-grid{display:grid;grid-template-columns:.8fr 1.2fr;gap:clamp(36px,5vw,64px);align-items:center}
.mem-img-wrap{border-radius:20px;overflow:hidden;border:1px solid var(--border)}
.mem-img-wrap img{width:100%;height:100%;object-fit:cover;object-position:center 15%;max-height:480px}
.mem-intro{font-size:15px;color:var(--t2);line-height:1.75;margin-bottom:24px}
.mem-list{display:flex;flex-direction:column;gap:16px;margin-bottom:24px}
.mem-item{display:flex;align-items:flex-start;gap:14px}
.mem-chk{
  width:26px;height:26px;border-radius:50%;flex-shrink:0;margin-top:1px;
  background:#0D0D0D;display:flex;align-items:center;justify-content:center;
}
[data-theme="dark"] .mem-chk{background:#fff}
.mem-chk svg{width:12px;height:12px;stroke:#fff;stroke-width:2.5;fill:none;stroke-linecap:round}
[data-theme="dark"] .mem-chk svg{stroke:#0D0D0D}
.mem-item-h{font-size:14.5px;font-weight:700;color:var(--text);margin-bottom:2px}
.mem-item-p{font-size:13.5px;color:var(--t3);line-height:1.55}
.mem-note{
  font-size:13.5px;color:var(--t2);line-height:1.7;
  background:var(--bg);border:1px solid var(--border);border-radius:14px;
  padding:16px 18px;
}
[data-theme="dark"] .mem-note{background:#0D0D0D;border-color:#2D2D2D}
@media(max-width:860px){.mem-grid{grid-template-columns:1fr}.mem-img-wrap img{max-height:340px}}

/* tools grid - icon left, label right, no outer card */
.tools-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px 24px;margin-top:28px}
.tool-item{display:flex;align-items:center;gap:14px}
.tool-icon{width:52px;height:52px;border-radius:12px;border:1px solid var(--border);background:var(--soft);display:flex;align-items:center;justify-content:center;flex-shrink:0}
[data-theme="dark"] .tool-icon{background:#111;border-color:#2D2D2D}
.tool-icon svg{width:24px;height:24px;color:var(--text)}
.tool-label{font-size:14.5px;color:var(--text);text-align:left;font-weight:700;line-height:1.3}

/* perf card */
.perf-card{
  background:#0A0A0A;border-radius:20px;padding:28px;
}
[data-theme="dark"] .perf-card{background:#111;border:1px solid #2D2D2D}
.perf-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px}
.perf-stat{background:#141414;border-radius:12px;padding:16px;text-align:center}
[data-theme="dark"] .perf-stat{background:#1a1a1a}
.perf-n{font-size:1.4rem;font-weight:800;color:#fff;letter-spacing:-.04em;margin-bottom:4px}
.perf-l{font-size:11px;color:rgba(255,255,255,.4)}
.perf-stat.wide{grid-column:span 3;display:grid;grid-template-columns:1fr 1fr;gap:16px}
.perf-note{font-size:12px;color:rgba(255,255,255,.35);line-height:1.6;margin-top:8px;text-align:center}

/* industries - icon left, label right, no outer card */
.industries-grid{display:grid;grid-template-columns:1fr;gap:20px;margin-top:28px}
.industry-item{display:flex;align-items:center;gap:14px}
.industry-icon{width:52px;height:52px;border-radius:12px;background:var(--soft);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0}
[data-theme="dark"] .industry-icon{background:#111;border-color:#2D2D2D}
.industry-icon svg{width:24px;height:24px;color:var(--text)}
.industry-label{font-size:15px;font-weight:700;color:var(--text);text-align:left;line-height:1.3}

/* testimonials */
.testi-col{position:relative}
.testi-grid{display:grid;grid-template-columns:1fr;gap:14px}
.testi-blurred{filter:blur(5px);pointer-events:none;user-select:none;opacity:.7}
.testi-overlay{
  position:absolute;inset:0;
  display:flex;align-items:center;justify-content:center;
  z-index:10;
}
.testi-overlay-card{
  background:#fff;
  border:1.5px solid #E5E7EB;
  border-radius:20px;
  padding:32px 28px;
  text-align:center;
  max-width:340px;
  box-shadow:0 8px 40px rgba(0,0,0,.1);
}
[data-theme="dark"] .testi-overlay-card{background:#111;border-color:#2D2D2D;box-shadow:0 8px 40px rgba(0,0,0,.4)}
.tov-icon{
  width:52px;height:52px;border-radius:16px;
  background:#F3F4F6;
  display:flex;align-items:center;justify-content:center;
  margin:0 auto 16px;
}
[data-theme="dark"] .tov-icon{background:#1a1a1a}
.tov-icon svg{width:26px;height:26px;stroke:#374151;fill:none;stroke-width:1.8;stroke-linecap:round}
[data-theme="dark"] .tov-icon svg{stroke:#9CA3AF}
.tov-h{font-size:17px;font-weight:800;color:var(--text);margin-bottom:8px;line-height:1.25}
.tov-p{font-size:13px;color:var(--t3);line-height:1.6;margin-bottom:20px}
.tov-badges{display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:20px;flex-wrap:wrap}
.tov-badge{
  display:inline-flex;align-items:center;gap:5px;
  font-size:11.5px;font-weight:600;color:var(--t3);
  background:#F3F4F6;border-radius:99px;padding:5px 12px;
}
[data-theme="dark"] .tov-badge{background:#1a1a1a;color:#9CA3AF}
.btn-tov{
  display:inline-flex;align-items:center;gap:8px;
  width:100%;justify-content:center;
  padding:13px 20px;border-radius:12px;
  font-size:14px;font-weight:700;color:#fff;
  background:#0D0D0D;
  transition:opacity .15s;
}
.btn-tov:hover{opacity:.8}
.tov-sub{font-size:11.5px;color:var(--t4);margin-top:12px}
.testi-card{
  background:var(--soft);border:1px solid var(--border);
  border-radius:16px;padding:20px;
}
[data-theme="dark"] .testi-card{background:#111;border-color:#2D2D2D}
.testi-stars{color:#F59E0B;font-size:12px;letter-spacing:1px;margin-bottom:10px}
.testi-q{font-size:13.5px;color:var(--t2);line-height:1.65;font-style:italic;margin-bottom:14px}
[data-theme="dark"] .testi-q{color:#D1D5DB}
.testi-auth{display:flex;align-items:center;gap:10px}
.testi-av{width:36px;height:36px;border-radius:50%;background:#0D0D0D;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0}
.testi-name{font-size:13px;font-weight:700;color:var(--text)}
.testi-co{font-size:11px;color:var(--t4)}
.testi-rev{font-size:13px;font-weight:700;color:var(--t3);margin-top:6px}
.testi-skel{height:10px;border-radius:99px;background:var(--border);margin-bottom:8px}
[data-theme="dark"] .testi-skel{background:#2D2D2D}
.testi-skel:last-child{margin-bottom:0}
.testi-more{margin-top:14px;text-align:center}
.testi-more a{font-size:12.5px;color:var(--t3);font-weight:600}
.testi-more a:hover{color:var(--text)}

/* integrations */
.integrations-sec{background:#fff;border-top:1px solid var(--border);padding:clamp(56px,7vw,80px) 0}
[data-theme="dark"] .integrations-sec{background:#0D0D0D;border-color:#2D2D2D}
.int-top{margin-bottom:32px}
.int-logos{display:flex;flex-wrap:wrap;gap:12px;align-items:center}
.int-logo{
  display:flex;align-items:center;gap:9px;
  padding:12px 20px;border-radius:12px;
  background:#fff;border:1.5px solid #E5E7EB;
  font-size:14px;font-weight:600;color:var(--t2);
  transition:border-color .15s,box-shadow .15s;
}
.int-logo:hover{border-color:#9CA3AF;box-shadow:0 2px 8px rgba(0,0,0,.06)}
[data-theme="dark"] .int-logo{background:#111;border-color:#2D2D2D;color:#D1D5DB}
.int-logo svg{width:18px;height:18px;flex-shrink:0;stroke:var(--t3)}
.int-more{font-size:14px;color:var(--t4);padding:12px 4px;font-weight:500}

/* security */
.security-sec{background:var(--soft);border-top:1px solid var(--border);padding:clamp(56px,7vw,80px) 0}
[data-theme="dark"] .security-sec{background:#161616;border-color:#2D2D2D}
.sec-top{margin-bottom:32px}
.sec-badges{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.sec-badge{
  display:flex;flex-direction:column;align-items:center;gap:10px;
  padding:24px 14px;border-radius:16px;
  background:#fff;border:1.5px solid #E5E7EB;text-align:center;
}
[data-theme="dark"] .sec-badge{background:#111;border-color:#2D2D2D}
.sec-badge svg{width:26px;height:26px;stroke:var(--text);fill:none;stroke-width:1.8}
.sec-badge-label{font-size:12px;font-weight:600;color:var(--t2);line-height:1.4}
[data-theme="dark"] .sec-badge-label{color:#D1D5DB}
@media(max-width:768px){.sec-badges{grid-template-columns:repeat(3,1fr)}}
@media(max-width:480px){.sec-badges{grid-template-columns:repeat(2,1fr)}}

/* ── RESOURCES ── */
.res-sec{background:var(--bg);border-top:1px solid var(--border)}
[data-theme="dark"] .res-sec{background:#0D0D0D;border-color:#2D2D2D}
.res-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px;margin-top:28px}
.res-card{
  display:block;padding:22px;border-radius:16px;
  background:var(--soft);border:1px solid var(--border);
  transition:border-color .15s,transform .15s;
}
.res-card:hover{border-color:var(--t4);transform:translateY(-2px)}
[data-theme="dark"] .res-card{background:#111;border-color:#2D2D2D}
.res-tag{font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--t4);margin-bottom:10px}
.res-h3{font-size:15px;font-weight:800;color:var(--text);line-height:1.35;margin-bottom:8px}
.res-p{font-size:13px;color:var(--t3);line-height:1.6;margin-bottom:14px}
.res-link{font-size:12.5px;font-weight:700;color:var(--text)}

/* ── FAQ ── */
.faq-sec{background:var(--bg);border-top:1px solid var(--border)}
[data-theme="dark"] .faq-sec{background:#0D0D0D;border-color:#2D2D2D}
.faq-grid{display:grid;grid-template-columns:1fr 1.5fr;gap:clamp(40px,6vw,72px);align-items:start}
.faq-list{display:flex;flex-direction:column}
.faq-item{border-bottom:1px solid var(--border)}
[data-theme="dark"] .faq-item{border-color:#2D2D2D}
.faq-item:first-child{border-top:1px solid var(--border)}
[data-theme="dark"] .faq-item:first-child{border-color:#2D2D2D}
.faq-q{display:flex;align-items:center;justify-content:space-between;padding:17px 0;cursor:pointer;font-size:14.5px;font-weight:700;color:var(--text);gap:14px}
.faq-icon{font-size:18px;color:var(--t4);transition:transform .2s;flex-shrink:0;line-height:1}
.faq-item.open .faq-icon{transform:rotate(45deg);color:var(--text)}
.faq-a{font-size:13.5px;color:var(--t3);line-height:1.75;max-height:0;overflow:hidden;transition:max-height .3s ease,padding .3s}
.faq-a a{color:var(--text);font-weight:600;text-decoration:underline;text-underline-offset:2px}
.faq-item.open .faq-a{max-height:300px;padding-bottom:16px}

/* ── FINAL CTA ── */
.cta-final{
  position:relative;overflow:hidden;
  background:#0A0A0A;
  padding:clamp(60px,8vw,100px) 0;
  border-top:1px solid #1F1F1F;
}
.cta-final-inner{
  display:grid;grid-template-columns:1fr 380px;
  gap:48px;align-items:center;
}
.cta-final-left{}
.cta-final-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:16px}
.cta-final-h{
  font-size:clamp(1.8rem,4vw,3rem);font-weight:800;
  line-height:1.1;letter-spacing:-.03em;color:#fff;margin-bottom:10px;
}
.cta-final-h em{font-style:normal;color:#fff}
.cta-final-sub{font-size:14px;color:rgba(255,255,255,.5);line-height:1.7;margin-bottom:24px;max-width:400px}
.btn-cta-final{
  display:inline-flex;align-items:center;gap:8px;
  padding:14px 28px;border-radius:12px;
  font-size:15px;font-weight:700;color:#0D0D0D;
  background:#fff;
  transition:opacity .15s,transform .15s;
}
.btn-cta-final:hover{opacity:.9;transform:translateY(-2px)}
.cta-note{font-size:12px;color:rgba(255,255,255,.3);margin-top:10px}
.cta-final-right{position:relative}
.cta-final-right img{
  width:100%;border-radius:20px;
  object-fit:cover;object-position:center top;
  max-height:400px;
}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .hero-inner{grid-template-columns:1fr}
  .hero-card{display:none}
  .prob-grid{grid-template-columns:repeat(2,1fr)}
  .day-flow{grid-template-columns:repeat(3,1fr)}
  .day-flow::before{display:none}
  .split-grid{grid-template-columns:1fr}
  .perf-grid{grid-template-columns:repeat(3,1fr)}
  .perf-stat.wide{grid-column:span 3}
  .faq-grid{grid-template-columns:1fr}
  .cta-final-inner{grid-template-columns:1fr}
  .cta-final-right{display:none}
  .sec-badges{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:768px){
  .prob-grid{grid-template-columns:1fr}
  .sec-badges{grid-template-columns:repeat(2,1fr)}
  .mission-stats{gap:16px}
  .ft-grid{grid-template-columns:1fr}
  .ft-bottom{flex-direction:column;text-align:center}
}
@media(max-width:480px){
  .day-flow{grid-template-columns:repeat(2,1fr)}
  .perf-grid{grid-template-columns:repeat(2,1fr)}
  .perf-stat.wide{grid-column:span 2}
  .industries-grid{grid-template-columns:1fr}
  .mission-bar{flex-direction:column;align-items:flex-start}
  .int-logos{gap:8px}
}
</style>
</head>
<body>

@php
  $avaHasDesk = auth()->check() && \Illuminate\Support\Facades\DB::table('worker_deployments')
    ->where('user_id', auth()->id())->where('worker_slug', 'ava')
    ->whereIn('status', ['active', 'paused'])->exists();
@endphp

{{-- NAV - shared structure with / and /ai-workers; upcoming worker pages reuse this same component --}}
<x-public-nav :links="[
  ['label' => 'Meet the AI Worker', 'href' => route('public.workers.index')],
  ['label' => 'How It Works',      'href' => '#day-in-life'],
  ['label' => 'For Business',      'href' => '#faq'],
  ['label' => 'Resources',         'href' => '#integrations'],
  ['label' => 'Pricing',           'href' => route('pricing')],
]">
  <x-slot:cta>
    @if($avaHasDesk)
    <a href="{{ route('app.desk.ava') }}" class="btn-cta">
      Go to AVA's Desk
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
    @else
    <a href="{{ route('hire.ava.welcome') }}" class="btn-cta">
      Deploy AVA
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
    @endif
  </x-slot>
  <x-slot:mobileCta>
    @if($avaHasDesk)
    <a href="{{ route('app.desk.ava') }}" class="btn-cta" style="justify-content:center">Go to AVA's Desk →</a>
    @else
    <a href="{{ route('hire.ava.welcome') }}" class="btn-cta" style="justify-content:center">Deploy AVA →</a>
    @endif
  </x-slot>
</x-public-nav>

{{-- HERO -
     Real photo (ava-skyline.png) as poster, real video (AVA.MP4) wired
     with an actual play/pause toggle instead of a decorative scrubber
     bar around a static image with a hardcoded fake elapsed time. --}}
<section class="hero-worker">

  {{-- LEFT: video column --}}
  <div class="hero-video-col" id="avaHeroVideoWrap">

    {{-- Background media --}}
    <div class="hero-media">
      <video id="avaHeroVideo" poster="{{ asset($worker['hero_video']['thumbnail']) }}" playsinline preload="metadata">
        <source src="{{ asset($worker['hero_video']['url']) }}" type="video/mp4">
        <track kind="captions" src="{{ asset('videos/AVA.vtt') }}" srclang="en" label="English" default>
      </video>
    </div>

    {{-- Text content over the video --}}
    <div class="hero-text">
      <div class="hero-copy" id="avaHeroCopy">
        <div class="hero-eye">UNIT's AI Renewal Worker</div>
        <h1 class="hero-h">She never<br>forgets a <span class="hl">renewal.</span></h1>
        <p class="hero-p">An AI agent that watches your inbox <em>and</em> your renewal calendar, drafts every renewal, and keeps a human in control of every send.</p>
      </div>
      <div class="hero-btns">
        @if($avaHasDesk)
        <a href="{{ route('app.desk.ava') }}" class="btn-hire-hero">Go to AVA's Desk</a>
        @else
        <a href="{{ route('hire.ava.welcome') }}" class="btn-hire-hero">Deploy AVA</a>
        @endif
        <button type="button" class="btn-watch-hero" id="avaHeroPlayBtn">
          <span class="btn-watch-icon"><svg viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg></span>
          Watch Her Story
        </button>
      </div>
      {{-- Visible transcript - captions cover screen-reader/accessibility use
           while playing, but a <track> isn't crawlable text. This is, plus
           it lets anyone skim what she says without sound. --}}
      <details class="hero-transcript">
        <summary>Read the transcript</summary>
        <p>{{ $worker['hero_video']['transcript'] }}</p>
      </details>
    </div>

  </div>{{-- end .hero-video-col --}}

  {{-- RIGHT: status panel (separate column) -
       Real numbers only: $totalTx / $deploymentCount come straight from
       the DB in WorkerPublicController (no invented fallback numbers, no
       fabricated multiplier formulas for "revenue protected" or "streak"
       that used to render here regardless of actual usage. --}}
  <div class="hero-panel">
    <div class="hc-status">
      <div class="hc-dot"></div>
      <span class="hc-status-txt">{{ $worker['name'] }} IS LIVE</span>
    </div>
    <div class="hc-revenue-label">Renewals processed</div>
    <div class="hc-revenue-amount">{{ number_format($totalTx) }}</div>
    <div class="hc-divider"></div>
    <div class="hc-revenue-label">Businesses running AVA</div>
    <div class="hc-revenue-amount">{{ number_format($deploymentCount) }}</div>
    <a href="{{ route('hire.ava.welcome') }}" class="hc-feed-btn">
      Deploy AVA →
    </a>
  </div>

</section>

<script>
(function(){
  var video = document.getElementById('avaHeroVideo');
  var btn   = document.getElementById('avaHeroPlayBtn');
  var copy  = document.getElementById('avaHeroCopy');
  if(!video || !btn) return;
  function setLabel(){
    btn.innerHTML = (video.paused
      ? '<span class="btn-watch-icon"><svg viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg></span>Watch Her Story'
      : '<span class="btn-watch-icon"><svg viewBox="0 0 24 24"><rect x="6" y="5" width="4" height="14"/><rect x="14" y="5" width="4" height="14"/></svg></span>Pause');
  }
  function setCopyVisibility(){
    if(!copy) return;
    copy.classList.toggle('hero-copy-hidden', !video.paused);
  }
  btn.addEventListener('click', function(){
    if(video.paused) video.play(); else video.pause();
  });
  video.addEventListener('play', setLabel);
  video.addEventListener('pause', setLabel);
  video.addEventListener('ended', setLabel);
  video.addEventListener('play', setCopyVisibility);
  video.addEventListener('pause', setCopyVisibility);
  video.addEventListener('ended', setCopyVisibility);
})();
</script>

{{-- THE PROBLEM --}}
<section class="problem-sec">

  {{-- Centered headline --}}
  <div class="prob-top" style="padding:0 var(--pad)">
    <div class="prob-top-eye">
      <svg viewBox="0 0 24 24" width="13" height="13"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      The Problem
    </div>
    <h2 class="prob-top-h">Renewals are mission-critical.<br>But they're <span class="hl">easy to miss.</span></h2>
    <p class="prob-top-sub">Deadlines slip. Money leaks. Opportunities disappear.</p>
  </div>

  {{-- Full-width problem grid --}}
  <div class="prob-split">

    <div class="prob-left-header">
      <div class="prob-left-icon">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <div>
        <div class="prob-left-title">Which of these problems hit your team?</div>
        <div class="prob-left-sub">If you nodded at even one, you're not alone.</div>
      </div>
    </div>

    {{-- 4×2 grid --}}
    <div class="prob-items">
      @php $problems = [
        ['icon'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>','h'=>'Nobody owns renewals.','p'=>'Permits, licenses, contracts. No single owner, no accountability.'],
        ['icon'=>'<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>','h'=>'Inbox chaos.','p'=>'Renewal emails get buried, deleted, or never even seen.'],
        ['icon'=>'<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>','h'=>'Money leaks quietly.','p'=>'Unused software, duplicates, and forgotten vendors drain budgets.'],
        ['icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','h'=>'Licenses expire without warning.','p'=>'One missed renewal can halt operations or trigger fines.'],
        ['icon'=>'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>','h'=>'Hours wasted every week.','p'=>'Spreadsheets, portals, emails, follow-ups. It never ends.'],
        ['icon'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>','h'=>'Everyone has their own system.','p'=>'No single source of truth. No visibility. No accountability.'],
        ['icon'=>'<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3M12 17h.01"/>','h'=>'You don\'t know what\'s due next.','p'=>'You find out only when someone asks, or after the deadline.'],
        ['icon'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>','h'=>'It depends on one person.','p'=>'Vacation, sick leave, resignation. Institutional knowledge disappears.'],
      ]; @endphp
      @foreach($problems as $p)
      <div class="prob-item">
        <div class="prob-item-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round">{!! $p['icon'] !!}</svg></div>
        <div class="prob-item-h">{{ $p['h'] }}</div>
        <div class="prob-item-p">{{ $p['p'] }}</div>
      </div>
      @endforeach
    </div>

    {{-- Impact banner --}}
    <div class="prob-impact">
      <div class="prob-impact-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div class="prob-impact-text">
        Missed renewals don't just create problems. They cost <span>money, time, and credibility.</span>
      </div>
    </div>

    <p class="prob-related-link">
      Related reading: <a href="{{ route('blog.show', '5-signs-your-team-needs-an-ai-agent') }}">5 Signs Your Team Is Doing Work an AI Agent Should Be Doing Instead →</a>
    </p>

  </div>{{-- end .prob-split --}}

</section>

{{-- MEET AVA - first-person, in her own words --}}
<section class="meet-sec">
  <div class="w">
    <div class="meet-grid">
      <div class="meet-img-wrap">
        <img src="/images/ava-selfie.png" alt="AVA, UNIT's AI Renewal Agent, speaking directly to camera">
      </div>
      <div>
        <div class="sec-eye">In her own words</div>
        <div class="meet-quote">"I don't wait to be <span class="hl">asked.</span>"</div>
        <div class="meet-body">
          <p>I watch two things: your inbox, and the calendar. If a renewal email lands, I read it. If no email ever comes but a deadline in your records is approaching, I catch that too. Either way, I match it to what I already know about the account and draft a response, not sent, just ready in your Gmail drafts.</p>
          <p>Then I wait. Nothing goes to your client until you say so. But I won't let it sit forgotten. If you go quiet, I'll remind you, a little more each time, until you act.</p>
          <p>When it's done, I move the renewal date forward, write up a signed record of everything that happened, and start watching for the next one. That's the job. I don't get tired of it.</p>
        </div>
        <div class="meet-sig">AVA, UNIT's AI Renewal Agent</div>
        <a href="#day-in-life" class="btn-prob-cta" style="margin-top:20px">
          See AVA in Action
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <p class="prob-related-link" style="margin-top:16px">
          Related reading: <a href="{{ route('blog.show', 'what-changes-when-ava-owns-your-renewal-inbox') }}">What Changes When AVA Owns Your Renewal Inbox →</a>
        </p>
      </div>
    </div>
  </div>
</section>

{{-- MEMORY - the tenant-data differentiator: AVA performs based on what
     she's taught, not a generic shared model. This is a platform-wide
     principle (see UNIT's "know the tenant first" philosophy), not just
     an AVA detail, so this section pattern belongs on every worker page. --}}
<section class="mem-sec">
  <div class="w">
    <div class="mem-head">
      <div class="sec-eye">The real differentiator</div>
      <h2 class="sec-h" style="margin-bottom:0">She only performs as well as what you teach her.</h2>
    </div>
    <div class="mem-grid">
      <div class="mem-img-wrap">
        <img src="/images/ava-boss.png" alt="AVA, confident and in control of her own workflow">
      </div>
      <div>
        <p class="mem-intro">AVA doesn't draw on some shared, generic model of every business that runs her. Her memory is built entirely from what you give her, which is exactly what makes her drafts, her judgment calls, and her tone specific to your business instead of generic.</p>
        <div class="mem-list">
          <div class="mem-item">
            <div class="mem-chk"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
            <div>
              <div class="mem-item-h">Client & Contact Records</div>
              <div class="mem-item-p">Who you work with, and how to reach them.</div>
            </div>
          </div>
          <div class="mem-item">
            <div class="mem-chk"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
            <div>
              <div class="mem-item-h">Renewal & Asset History</div>
              <div class="mem-item-p">What's expiring, when, and what happened last time.</div>
            </div>
          </div>
          <div class="mem-item">
            <div class="mem-chk"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
            <div>
              <div class="mem-item-h">Your Email Templates & Tone</div>
              <div class="mem-item-p">How your business actually talks to clients.</div>
            </div>
          </div>
          <div class="mem-item">
            <div class="mem-chk"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
            <div>
              <div class="mem-item-h">Every Logged Transaction</div>
              <div class="mem-item-p">A full trail of what she's done, and why she did it.</div>
            </div>
          </div>
        </div>
        <div class="mem-note">None of this is shared across tenants. Her memory is exclusively yours, and it's the reason her output feels like it came from someone who already knows your business.</div>
      </div>
    </div>
  </div>
</section>

{{-- A DAY IN AVA'S LIFE - AVA's real pipeline, animated --}}
<section class="day-sec" id="day-in-life">
  <div class="w">
    <div class="day-top">
      <div>
        <div class="sec-eye">A day in {{ $worker['name'] }}'s life</div>
        <h2 class="sec-h" style="margin-bottom:0">Follow AVA through one renewal.</h2>
      </div>
    </div>

    {{-- AVA's real full pipeline, grouped into 10 honest steps - same
         data drives the HowTo schema in <head>, so this can't drift out
         of sync with what's claimed to search engines. --}}
    @php
      $pipeIcons = [
        '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>',
        '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        '<path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>',
        '<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/>',
        '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/><polyline points="9 10 12 13 15 10"/>',
        '<path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>',
        '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12l2 2 4-4"/>',
        '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        '<polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/>',
      ];
    @endphp
    <div class="pipe-wrap">
      <div class="pipe-wrap-inner">
      <div class="pipeline-row" id="pipelineRow">

      @foreach($worker['pipeline'] as $i => $step)
      <div class="pipe-step" data-step="{{ $i }}" data-who="{{ $step['who'] }}">
        <div class="pipe-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round">{!! $pipeIcons[$i] !!}</svg>
          <div class="pipe-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
          <span class="pipe-badge">{{ $i + 1 }}</span>
        </div>
        <div class="pipe-label">{{ $step['title'] }}</div>
        <div class="pipe-time pipe-who-{{ strtolower($step['who']) }}">{{ $step['who'] === 'AVA' ? 'AVA' : 'You decide' }}</div>
      </div>
      @endforeach

      </div>{{-- end pipeline-row --}}
      </div>{{-- end pipe-wrap-inner --}}
    </div>{{-- end pipe-wrap --}}

    {{-- Single shared ticker row - below all steps --}}
    <div class="pipe-ticker-row" id="pipeTickerRow"></div>

    {{-- Cycle-complete bar - no fabricated time-savings numbers --}}
    <div class="mission-bar" id="missionBar">
      <div class="mission-txt">Cycle Complete. Watching for the Next One 🎉</div>
    </div>

    {{-- CTAs - $avaHasDesk computed once near the top of the page --}}
    <div class="pipe-cta">
      @if($avaHasDesk)
        <a href="{{ route('app.desk.ava') }}" class="btn-pipe-hire">
          AVA's Desk
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      @else
        <a href="{{ route('hire.ava.welcome') }}" class="btn-pipe-hire">
          Deploy AVA
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      @endif
      <a href="{{ $avaHasDesk ? route('app.workers.fast-track.page', 'ava') : route('hire.ava.welcome') }}" class="btn-pipe-test">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        Run a Live Test
      </a>
    </div>
    <p class="prob-related-link">
      Want the written walkthrough? <a href="{{ route('blog.show', 'avas-renewal-workflow-step-by-step') }}">AVA's Renewal Workflow, Step by Step →</a>
    </p>
  </div>
</section>

{{-- EDGE STATEMENTS - the differentiators most renewal tools skip.
     Each one is backed by a real screenshot of the actual feature (not a
     mockup) via "See Proof", plus a "Watch AVA Explain" slot that's real
     infrastructure for UNIT Studio's worker-explainer videos - wired now,
     showing an honest "coming soon" state until those videos exist. --}}
<section class="edge-sec">
  <div class="w">
    <div class="edge-head">
      <div class="sec-eye">What most renewal tools skip</div>
      <h2 class="sec-h" style="margin-bottom:0">The parts of the job everyone forgets to build.</h2>
    </div>
    <div class="edge-grid">
      @php
        $edgeStatements = [
          [
            'img'   => '/images/ava-boss.png',
            'img_pos' => 'center 20%',
            'eye'   => 'Her memory, your data',
            'h3'    => 'She only knows what you teach her.',
            'p'     => "AVA's memory, your clients, your contacts, your renewal history, your templates, lives inside your account. You decide what she remembers and how she responds. Nothing is shared across tenants, and nothing gets assumed. A worker that doesn't know your business first can't actually produce value for it.",
            'proof' => '/images/proof/memory-dashboard.png',
            'proof_label' => 'The actual Memory dashboard',
            'video' => null,
          ],
          [
            'img'   => '/images/ava-active.png',
            'img_pos' => 'center 15%',
            'eye'   => 'Escalation, not silence',
            'h3'    => "She won't let a renewal die quietly.",
            'p'     => "If a draft sits unapproved, AVA doesn't wait forever, and she doesn't nag every five minutes either. Reminders escalate on a schedule matched to urgency: gentle first, then direct, then urgent as the deadline nears. After a few unanswered attempts, she pauses and waits for you. She won't spam you, but she won't let it disappear either.",
            'proof' => '/images/proof/escalation-reminders.png',
            'proof_label' => 'A real reminder thread, gentle to urgent',
            'video' => null,
          ],
          [
            'img'   => '/images/ava-in-office.png',
            'img_pos' => null,
            'eye'   => 'The Archive',
            'h3'    => 'Every renewal, provable.',
            'p'     => 'When a renewal closes, AVA builds a complete record: every draft, every reminder, every approval, every payment confirmation, into a downloadable PDF with a QR code. Hand it to a client, an auditor, or your own team and prove exactly what happened, and when.',
            'proof' => '/images/proof/archive-record.png',
            'proof_label' => 'A real generated archive PDF',
            'video' => null,
          ],
        ];
      @endphp
      @foreach($edgeStatements as $i => $edge)
      <div class="edge-card">
        <div class="edge-img"><img src="{{ $edge['img'] }}" alt="AVA: {{ $edge['eye'] }}" @if($edge['img_pos']) style="object-position:{{ $edge['img_pos'] }}" @endif></div>
        <div class="edge-body">
          <div class="edge-eye">{{ $edge['eye'] }}</div>
          <h3 class="edge-h3">{{ $edge['h3'] }}</h3>
          <p class="edge-p">{{ $edge['p'] }}</p>
          <div class="edge-btns">
            <button type="button" class="btn-edge-proof" data-edge-proof="{{ $i }}">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
              See Proof
            </button>
            <button type="button" class="btn-edge-watch" data-edge-watch="{{ $i }}">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              Watch AVA Explain
            </button>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Edge-statement videos - forward-compatible: once UNIT Studio produces a
     real video for one of these, setting its 'video' field to a path is the
     only change needed anywhere. Nothing renders here until a video exists. --}}
@foreach($edgeStatements as $edge)
@if(!empty($edge['video']))
<script type="application/ld+json">{!! json_encode([
    '@@context'     => 'https://schema.org',
    '@type'        => 'VideoObject',
    'name'         => "{$worker['name']}: {$edge['eye']}",
    'description'  => $edge['p'],
    'thumbnailUrl' => asset($edge['img']),
    'contentUrl'   => asset($edge['video']),
    'uploadDate'   => $edge['video_upload_date'] ?? now()->toDateString(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
@endforeach

{{-- Edge statement proof/video modal - shared by all cards above --}}
<div class="edge-modal" id="edgeModal">
  <div class="edge-modal-backdrop" data-edge-close></div>
  <div class="edge-modal-box">
    <button type="button" class="edge-modal-close" data-edge-close>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="edge-modal-body" id="edgeModalBody"></div>
  </div>
</div>

@php
  $edgeProofData = collect($edgeStatements)->map(fn($e) => ['proof' => $e['proof'], 'label' => $e['proof_label'], 'video' => $e['video']]);
@endphp
<script>
(function(){
  var data  = @json($edgeProofData);
  var modal = document.getElementById('edgeModal');
  var body  = document.getElementById('edgeModalBody');
  if(!modal || !body) return;

  function openProof(i){
    var d = data[i];
    body.innerHTML = '<div class="edge-modal-label">' + d.label + '</div>'
      + '<img src="' + d.proof + '" alt="' + d.label + '" class="edge-modal-img">';
    modal.classList.add('open');
  }
  function openVideo(i){
    var d = data[i];
    if(d.video){
      body.innerHTML = '<video class="edge-modal-video" controls autoplay src="' + d.video + '"></video>';
    } else {
      body.innerHTML = '<div class="edge-modal-novideo">'
        + '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>'
        + '<div class="edge-modal-novideo-h">Video coming soon</div>'
        + '<p class="edge-modal-novideo-p">AVA hasn\'t recorded this one yet. Every UNIT worker will eventually explain their own edge statements on camera, this is one of the first spots that video will go.</p>'
        + '</div>';
    }
    modal.classList.add('open');
  }
  function close(){ modal.classList.remove('open'); body.innerHTML = ''; }

  document.querySelectorAll('[data-edge-proof]').forEach(function(btn){
    btn.addEventListener('click', function(){ openProof(parseInt(btn.dataset.edgeProof, 10)); });
  });
  document.querySelectorAll('[data-edge-watch]').forEach(function(btn){
    btn.addEventListener('click', function(){ openVideo(parseInt(btn.dataset.edgeWatch, 10)); });
  });
  document.querySelectorAll('[data-edge-close]').forEach(function(el){
    el.addEventListener('click', close);
  });
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape') close(); });
})();
</script>

<script>
(function(){
  var steps      = document.querySelectorAll('#pipelineRow .pipe-step');
  var missionBar = document.getElementById('missionBar');
  var tickerRow  = document.getElementById('pipeTickerRow');
  var STEP_MS  = 2200;
  var PAUSE_MS = 3000;

  var tickerMessages = [
    'Detecting a renewal: inbox or registry threshold…',
    'Reading and classifying what kind of renewal this is…',
    'Checking memory for this client\'s history…',
    'Drafting the renewal message and logging the transaction…',
    'Placing the draft in your Gmail drafts…',
    'Waiting for your approval, she\'ll remind you if it sits…',
    'Handling the invoice, or waiting if you skip it…',
    'Waiting for payment confirmation…',
    'Updating the renewal date for next cycle…',
    'Archiving the proof and resetting the watch…',
  ];

  function clearAll(){
    steps.forEach(function(s){
      s.classList.remove('ps-running','ps-done');
    });
    tickerRow.innerHTML = '';
    missionBar.classList.remove('visible');
  }

  function runStep(i){
    if(i >= steps.length){
      tickerRow.innerHTML = '';
      missionBar.classList.add('visible');
      setTimeout(function(){
        missionBar.classList.remove('visible');
        setTimeout(function(){ clearAll(); runStep(0); }, 500);
      }, PAUSE_MS);
      return;
    }
    var step = steps[i];
    step.classList.add('ps-running');
    tickerRow.innerHTML = '<span class="pipe-ticker-dot"></span>' + tickerMessages[i];
    setTimeout(function(){
      step.classList.remove('ps-running');
      step.classList.add('ps-done');
      runStep(i + 1);
    }, STEP_MS);
  }

  setTimeout(function(){ runStep(0); }, 600);
})();
</script>

{{-- EVERYTHING AVA HAS ACCESS TO + LIVE PERFORMANCE --}}
<section class="split-sec sec">
  <div class="w">
    <div class="split-grid">
      <div>
        <div class="sec-eye">What {{ $worker['name'] }} actually touches</div>
        <h2 class="sec-h" style="font-size:clamp(1.3rem,2.2vw,1.7rem)">No sprawling app list to connect. Just the data she needs.</h2>
        <div class="tools-grid">
          @php
            $tools = [
              ['Gmail','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>'],
              ['Client & Contact Records','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>'],
              ['Renewal Registry','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>'],
              ['Invoices (You decide)','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>'],
              ['Transaction Log','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8"/></svg>'],
            ];
          @endphp
          @foreach($tools as $tool)
          <div class="tool-item">
            <div class="tool-icon">{!! $tool[1] !!}</div>
            <div class="tool-label">{{ $tool[0] }}</div>
          </div>
          @endforeach
        </div>
        <p style="font-size:12.5px;color:var(--t4);margin-top:16px">Gmail is the only third-party connection required. Everything else is UNIT's own memory layer, built specifically for renewals.</p>
      </div>
      <div>
        <div class="sec-eye" style="color:rgba(255,255,255,.4)">Live Performance</div>
        {{-- Real numbers only - dropped the fabricated "Reminders Sent",
             "Revenue Protected" (was $totalTx*3.85), "Accuracy Rate", and
             "Day Streak" (was $deploymentCount*18) tiles that used to
             render here regardless of actual usage. --}}
        <div class="perf-card">
          <div class="perf-grid">
            <div class="perf-stat">
              <div class="perf-n">{{ number_format($totalTx) }}</div>
              <div class="perf-l">Renewals Processed</div>
            </div>
            <div class="perf-stat">
              <div class="perf-n">{{ number_format($deploymentCount) }}</div>
              <div class="perf-l">Businesses Running AVA</div>
            </div>
            <div class="perf-stat" style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2)">
              <div class="perf-n" style="color:#22C55E">Live</div>
              <div class="perf-l" style="color:rgba(34,197,94,.6)">Right now</div>
            </div>
          </div>
          <div class="perf-note">Always on. Always working. Every draft reviewed by a human before it's sent.</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- WHO HIRES + TESTIMONIALS --}}
<section class="split-sec sec" style="border-top:1px solid var(--border)">
  <div class="w">
    <div class="split-grid">
      <div>
        <div class="sec-eye">Who hires {{ $worker['name'] }}</div>
        <h2 class="sec-h" style="font-size:clamp(1.3rem,2.2vw,1.7rem)">Built around the businesses running her today.</h2>
        <div class="industries-grid">
          @php
            $industries = [
              ['IT & Digital Agencies','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>'],
              ['Insurance Brokers','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'],
              ['Compliance & Licensing Firms','<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12l2 2 4-4"/></svg>'],
            ];
          @endphp
          @foreach($industries as $ind)
          <div class="industry-item">
            <div class="industry-icon">{!! $ind[1] !!}</div>
            <div class="industry-label">{{ $ind[0] }}</div>
          </div>
          @endforeach
        </div>
        <p style="font-size:12.5px;color:var(--t4);margin-top:16px">Any business with recurring renewals fits. These are the industries where AVA runs today.</p>
      </div>
      <div class="testi-col">
        <div class="sec-eye">What business owners say</div>
        @if($reviews->count() > 0)
        {{-- Real, admin-approved reviews only (see AdminReviewController) --}}
        <div class="testi-grid">
          @foreach($reviews as $review)
          <div class="testi-card">
            <div class="testi-stars" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
            <p class="testi-q">"{{ $review->quote }}"</p>
            <div class="testi-auth">
              <div class="testi-av">{{ strtoupper(substr($review->author_name, 0, 1)) }}</div>
              <div>
                <div class="testi-name">{{ $review->author_name }}</div>
                @if($review->author_company)<div class="testi-co">{{ $review->author_company }}</div>@endif
              </div>
            </div>
          </div>
          @endforeach
        </div>
        <div class="testi-more">
          <a href="mailto:hello@unit.report?subject=My AVA Experience&body=Hi UNIT team, I'd like to share my experience with AVA...">Share your own experience →</a>
        </div>
        @else
        {{-- Skeleton placeholders, not fake quotes - no invented names,
             companies, or star ratings sitting in the DOM for crawlers to
             read as real content. Real reviews replace these as they come in. --}}
        <div class="testi-grid testi-blurred">
          @for ($i = 0; $i < 3; $i++)
          <div class="testi-card">
            <div class="testi-skel" style="width:30%"></div>
            <div class="testi-skel" style="width:100%;margin-top:10px"></div>
            <div class="testi-skel" style="width:90%"></div>
            <div class="testi-skel" style="width:60%;margin-bottom:14px"></div>
            <div class="testi-auth">
              <div class="testi-av">?</div>
              <div style="flex:1">
                <div class="testi-skel" style="width:70px"></div>
                <div class="testi-skel" style="width:50px;height:8px"></div>
              </div>
            </div>
          </div>
          @endfor
        </div>
        {{-- Overlay soliciting real testimonials --}}
        <div class="testi-overlay">
          <div class="testi-overlay-card">
            <div class="tov-icon">
              <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            </div>
            <div class="tov-h">Have you used AVA?<br>We'd love to hear from you.</div>
            <p class="tov-p">Share your experience and get featured here. Real stories from real operators, no scripts, no fluff.</p>
            <div class="tov-badges">
              <span class="tov-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                Featured on this page
              </span>
              <span class="tov-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                Shared on our socials
              </span>
            </div>
            <a href="mailto:hello@unit.report?subject=My AVA Experience&body=Hi UNIT team, I'd like to share my experience with AVA..." class="btn-tov">
              Share Your Experience →
            </a>
            <div class="tov-sub">Takes 2 minutes · We reply to every submission</div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</section>

{{-- INTEGRATIONS + SECURITY --}}
{{-- INTEGRATIONS --}}
<section class="integrations-sec">
  <div class="w">
    <div class="int-top">
      <div class="sec-eye">{{ $worker['name'] }} connects with</div>
      <h2 class="sec-h">One real connection. No app sprawl to manage.</h2>
    </div>
    <div class="int-logos">
      <div class="int-logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Gmail
      </div>
      <span class="int-more">More connections as new AI Workers ship</span>
    </div>
  </div>
</section>

{{-- SECURITY --}}
<section class="security-sec">
  <div class="w">
    <div class="sec-top">
      <div class="sec-eye">Security</div>
      <h2 class="sec-h">Built to be trusted with your inbox.</h2>
    </div>
    <div class="sec-badges">
      @php
        $secBadges = [
          ['Data encrypted in transit & at rest','<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>'],
          ['Role-based access & permissions','<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>'],
          ['Audit logs & activity history','<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>'],
          ['Nothing sends without your approval','<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
        ];
      @endphp
      @foreach($secBadges as $badge)
      <div class="sec-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">{!! $badge[1] !!}</svg>
        <div class="sec-badge-label">{{ $badge[0] }}</div>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- RESOURCES - real published posts about this worker (DB-driven, see
     WorkerPublicController::show()) plus the real whitepaper. Internal
     links only, nothing here points to a page that doesn't exist. --}}
@if($resources->count())
<section class="res-sec sec">
  <div class="w">
    <div class="sec-top">
      <div class="sec-eye">Go deeper</div>
      <h2 class="sec-h">See how {{ $worker['name'] }} actually works, in writing.</h2>
    </div>
    <div class="res-grid">
      @foreach($resources as $post)
      <a href="{{ route('blog.show', $post->slug) }}" class="res-card">
        <div class="res-tag">{{ $post->tag }}</div>
        <h3 class="res-h3">{{ $post->title }}</h3>
        <p class="res-p">{{ $post->excerpt }}</p>
        <span class="res-link">Read the article →</span>
      </a>
      @endforeach
      <a href="{{ asset('whitepapers/unit-ai-agents-vs-ai-workers.pdf') }}" target="_blank" rel="noopener" class="res-card">
        <div class="res-tag">Whitepaper</div>
        <h3 class="res-h3">AI Agents vs. AI Workers</h3>
        <p class="res-p">How UNIT turns a manual process into a deployed AI Worker, using AVA's real build as the example.</p>
        <span class="res-link">Download the PDF →</span>
      </a>
    </div>
  </div>
</section>
@endif

{{-- FAQ --}}
<section class="faq-sec sec">
  <div class="w">
    <div class="faq-grid">
      <div>
        <div class="sec-eye">Frequently asked questions</div>
        <h2 class="sec-h">Everything you want to know about {{ $worker['name'] }}.</h2>
        @if($avaHasDesk)
        <a href="{{ route('app.desk.ava') }}" class="btn-cta" style="display:inline-flex;margin-top:8px">
          Go to AVA's Desk
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        @else
        <a href="{{ route('hire.ava.welcome') }}" class="btn-cta" style="display:inline-flex;margin-top:8px">
          Get started free
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        @endif
      </div>
      <div class="faq-list">
        @foreach($worker['faq'] as $faq)
        <div class="faq-item">
          <div class="faq-q" onclick="this.closest('.faq-item').classList.toggle('open')">
            <span>{{ $faq['q'] }}</span>
            <span class="faq-icon">+</span>
          </div>
          <div class="faq-a">{!! $faq['a'] !!}</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- FINAL CTA --}}
<section class="cta-final">
  <div class="w">
    <div class="cta-final-inner">
      <div class="cta-final-left">
        <div class="cta-final-eyebrow">Your workday ended.</div>
        <h2 class="cta-final-h">{{ $worker['name'] }}'s <em>didn't.</em></h2>
        <p class="cta-final-sub">Tomorrow {{ $worker['name'] }} will protect someone else's business. Or yours.</p>
        @if($avaHasDesk)
        <a href="{{ route('app.desk.ava') }}" class="btn-cta-final">
          Go to {{ $worker['name'] }}'s Desk
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        @else
        <a href="{{ route('hire.ava.welcome') }}" class="btn-cta-final">
          Deploy {{ $worker['name'] }} Today
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <div class="cta-note">No credit card required.</div>
        @endif
      </div>
      <div class="cta-final-right">
        <img src="/images/ava-stand.png" alt="AVA, ready to deploy" style="object-position:center top;max-height:380px;width:auto;margin:0 auto">
      </div>
    </div>
  </div>
</section>

{{-- FOOTER --}}
<x-public-footer />

<script>
// ── Theme - identical key/logic to / and /ai-workers ──
const root = document.documentElement;
const saved = localStorage.getItem('unit-theme');
if(saved) root.setAttribute('data-theme', saved);
document.getElementById('theme-toggle').addEventListener('click', function(){
  const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  root.setAttribute('data-theme', next);
  localStorage.setItem('unit-theme', next);
});

// ── Mobile menu - identical to / and /ai-workers ──
const ham = document.getElementById('ham');
const mob = document.getElementById('mob');
ham.addEventListener('click', () => mob.classList.add('open'));
document.getElementById('mob-close').addEventListener('click', () => mob.classList.remove('open'));
function closeMob(){ mob.classList.remove('open'); }
</script>

<x-self-learn />

@include('partials.tracking')
</body>
</html>

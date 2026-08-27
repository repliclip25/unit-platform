@php
// icon key -> SVG path lookup, used for the hero/feature-card icon and any
// stroke-style detail icon. Add an entry here when a new presale worker
// needs an icon not already covered.
$icons = [
    'video'    => 'M15 10l4.55-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.45.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
    'film'     => 'M15 10l4.55-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.45.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
    'sparkles' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 13l-5.714 2.143L13 22l-2.286-6.857L5 13l5.714-2.143L13 2z',
    'user'     => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
    'folder'   => 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z',
];
$iconPath = $icons[$worker['icon'] ?? 'video'] ?? $icons['video'];

// Google Drive gets its real logomark instead of a generic stroke icon —
// it's an actual integration, not an abstract concept.
$driveLogo = '<svg viewBox="0 0 87.3 78" xmlns="http://www.w3.org/2000/svg">
    <path d="m6.6 66.85 3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8h-27.5c0 1.55.4 3.1 1.2 4.5z" fill="#0066da"/>
    <path d="m43.65 25-13.75-23.8c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44a9.06 9.06 0 0 0 -1.2 4.5h27.5z" fill="#00ac47"/>
    <path d="m73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75 7.65-13.25c.8-1.4 1.2-2.95 1.2-4.5h-27.5l5.85 11.5z" fill="#ea4335"/>
    <path d="m43.65 25 13.75-23.8c-1.35-.8-2.9-1.2-4.5-1.2h-18.5c-1.6 0-3.15.45-4.5 1.2z" fill="#00832d"/>
    <path d="m59.8 53h-32.3l-13.75 23.8c1.35.8 2.9 1.2 4.5 1.2h50.8c1.6 0 3.15-.45 4.5-1.2z" fill="#2684fc"/>
    <path d="m73.4 26.5-12.7-22c-.8-1.4-1.95-2.5-3.3-3.3l-13.75 23.8 16.15 28h27.45c0-1.55-.4-3.1-1.2-4.5z" fill="#ffba00"/>
</svg>';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $worker['name'] }}: {{ $worker['role'] }} | UNITELO (Early Access)</title>
<meta name="description" content="{{ $worker['meta_desc'] }}">
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="apple-touch-icon" href="/favicon.png">
@include('partials.seo-meta', [
    'title'       => "{$worker['name']}: {$worker['role']} | UNITELO (Early Access)",
    'description' => $worker['meta_desc'],
    'image'       => asset('images/hero-team-2.png'),
])
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
[data-theme="dark"] .lp-card-sec{background:#0D0D0D}
[data-theme="dark"] .lp-detail-sec{background:#0D0D0D}
[data-theme="dark"] .lp-detail-row{border-color:#2D2D2D}
[data-theme="dark"] .lp-detail-icon svg{stroke:#F3F4F6}
[data-theme="dark"] .icon-diary{stroke:#F3F4F6}
[data-theme="dark"] .wk-check{background:#F3F4F6}
[data-theme="dark"] .wk-check svg{stroke:#0D0D0D}
[data-theme="dark"] .wk-card{background:#111;border-color:#2D2D2D}
[data-theme="dark"] .wk-quote{color:#D1D5DB}
[data-theme="dark"] .wk-bullet{color:#D1D5DB}
[data-theme="dark"] .wk-role{color:#9CA3AF}
[data-theme="dark"] .behind-bar{background:#0D0D0D;border-color:#2D2D2D}
[data-theme="dark"] .behind-item{border-color:#2D2D2D}
[data-theme="dark"] .behind-icon{background:#1a1a1a}
[data-theme="dark"] .behind-h{color:#F3F4F6}
[data-theme="dark"] .behind-p{color:#9CA3AF}
[data-theme="dark"] .cta-foot{background:#0D0D0D}

body{font-family:var(--font-b);color:var(--text);background:var(--bg);-webkit-font-smoothing:antialiased;overflow-x:hidden}
.w{max-width:var(--max);margin:0 auto;padding:0 var(--pad)}

/* ── HERO (split layout, matching /ai-workers — right panel is a placeholder,
   not fabricated character art, since presale workers have none yet) ── */
.hero-page{padding-top:62px;background:#fff;display:grid;grid-template-columns:1fr 1fr;min-height:64vh;overflow:hidden}
.hero-page-left{display:flex;align-items:center;padding-top:clamp(48px,6vw,80px);padding-bottom:clamp(48px,6vw,80px);padding-right:clamp(32px,4vw,56px);padding-left:max(var(--pad),calc((100vw - var(--max))/2 + var(--pad)))}
.hero-page-inner{max-width:480px}
.lp-badge{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#8a6a06;background:rgba(245,197,24,.15);padding:7px 16px;border-radius:99px;margin-bottom:18px}
.lp-badge-dot{width:6px;height:6px;border-radius:50%;background:#F5C518;animation:lpPulse 1.6s ease infinite}
@keyframes lpPulse{0%,100%{opacity:1}50%{opacity:.35}}
.hero-page-h{font-family:var(--font-h);font-size:clamp(1.9rem,3.6vw,2.8rem);font-weight:800;line-height:1.1;letter-spacing:-.03em;color:var(--text);margin-bottom:18px}
.hero-page-h em{font-style:normal;position:relative;display:inline}
.hero-page-h em::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}
.hero-page-p{font-size:1rem;color:var(--t2);line-height:1.75;margin-bottom:28px;max-width:420px}
.lp-cta-row{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:12px}
.btn-lp-main{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;background:#0D0D0D;color:#fff;transition:opacity .15s,transform .15s}
.btn-lp-main:hover{opacity:.88;transform:translateY(-2px)}
.lp-note{font-size:12.5px;color:var(--t4)}

.hero-page-right{position:relative;overflow:hidden;background:#000}
.hero-page-spacer{display:block;width:100%;min-height:calc(64vh - 62px)}
.lp-placeholder-panel{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:radial-gradient(circle at 50% 42%,#1c1c1c 0%,#000 72%)}
.lp-placeholder-icon{width:96px;height:96px;opacity:.22}
.hero-fade-page{position:absolute;inset:0;z-index:2;background:linear-gradient(to right,#fff 0%,rgba(255,255,255,.55) 15%,transparent 36%);pointer-events:none}
.hero-badge{position:absolute;bottom:24px;right:24px;z-index:3;background:#fff;border:1px solid var(--border);border-radius:16px;padding:12px 16px;display:flex;align-items:center;gap:10px;box-shadow:0 4px 20px rgba(0,0,0,.1)}
.badge-txt{font-size:13px;font-weight:700;color:var(--text);line-height:1.45}

/* ── SECTION ATOMS ── */
.sec-eye{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text);text-align:center;margin-bottom:12px}
.sec-h{font-family:var(--font-h);font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;line-height:1.15;letter-spacing:-.03em;color:var(--text);text-align:center;margin-bottom:36px}

/* ── FEATURE CARD (overview bullets) ── */
.lp-card-sec{background:var(--soft);padding:clamp(48px,6vw,72px) 0}
.lp-card-wrap{max-width:760px;margin:0 auto}
.wk-card{background:#fff;border:1px solid var(--border);border-radius:20px;padding:clamp(28px,4vw,40px);border-top:3px solid #0D0D0D}
.wk-head{display:flex;align-items:center;gap:12px;margin-bottom:6px}
.wk-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:var(--soft);flex-shrink:0}
.wk-icon svg{width:20px;height:20px;stroke:var(--text)}
.wk-name{font-family:var(--font-h);font-size:1.5rem;font-weight:800;letter-spacing:-.04em;color:var(--text)}
.lp-soon-tag{display:inline-block;margin-left:8px;padding:3px 10px;border-radius:99px;background:rgba(245,197,24,.15);color:#8a6a06;font-size:10.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;vertical-align:middle}
.wk-role{font-size:12px;font-weight:600;color:var(--t3);letter-spacing:.05em;text-transform:uppercase;margin-bottom:16px}
.wk-quote{font-size:15px;font-weight:600;color:var(--t2);line-height:1.65;margin-bottom:22px}
.wk-bullets{display:grid;grid-template-columns:1fr 1fr;gap:12px 24px}
.wk-bullet{display:flex;align-items:flex-start;gap:10px;font-size:13.5px;font-weight:500;color:var(--t2);line-height:1.5}
.wk-check{width:19px;height:19px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#0D0D0D;margin-top:1px}
.wk-check svg{width:10px;height:10px;stroke:#fff;stroke-width:2.5;fill:none}

/* ── DETAIL: Connects To / Produces / Memory Requirements ── */
.lp-detail-sec{background:#fff;padding:clamp(48px,6vw,72px) 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border)}
.lp-detail-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:clamp(28px,4vw,40px)}
.lp-detail-h{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);margin-bottom:16px}
.lp-detail-row{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid var(--border)}
.lp-detail-col .lp-detail-row:last-child{border-bottom:none}
.lp-detail-icon{width:44px;height:44px;flex-shrink:0;display:flex;align-items:center;justify-content:center}
.lp-detail-icon svg{width:44px;height:44px;stroke:#0D0D0D;stroke-width:1.4;fill:none}
.lp-detail-label{font-size:13.5px;font-weight:700;color:var(--text);line-height:1.3}
.lp-detail-desc{font-size:12.5px;color:var(--t3);line-height:1.5;margin-top:2px}

/* ── BEHIND EVERY WORKER ── */
.behind-bar{background:var(--soft);padding:clamp(44px,6vw,72px) 0}
.behind-intro{margin-bottom:clamp(32px,4vw,48px);text-align:center}
.behind-intro .sec-p{font-size:1rem;color:var(--t3);line-height:1.7;max-width:480px;margin:0 auto}
.behind-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid var(--border);border-radius:20px;overflow:hidden;background:#fff}
[data-theme="dark"] .behind-grid{background:#111}
.behind-item{padding:clamp(22px,3vw,34px) clamp(18px,2.5vw,26px);display:flex;flex-direction:column;gap:14px;border-right:1px solid var(--border)}
.behind-item:last-child{border-right:none}
.behind-icon{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;background:var(--soft)}
.behind-icon svg{width:22px;height:22px}
.behind-h{font-size:15.5px;font-weight:700;color:var(--text);line-height:1.35}
.behind-p{font-size:13px;color:var(--t3);line-height:1.65}

/* ── CTA ── */
.cta-foot{background:#fff;padding:clamp(44px,6vw,72px) 0}
.cta-foot-inner{background:#0D0D0D;border-radius:24px;padding:clamp(36px,5vw,56px) clamp(32px,5vw,60px);display:flex;align-items:center;justify-content:space-between;gap:32px;flex-wrap:wrap;position:relative;overflow:hidden}
.cta-foot-inner::before{content:'';position:absolute;right:-80px;top:-80px;width:320px;height:320px;border-radius:50%;background:rgba(255,255,255,.04);pointer-events:none}
.cta-foot-left{position:relative;z-index:1}
.cta-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:10px}
.cta-foot-h{font-family:var(--font-h);font-size:clamp(1.5rem,2.8vw,2.2rem);font-weight:800;letter-spacing:-.03em;color:#fff;margin-bottom:6px}
.cta-foot-sub{font-size:14px;color:rgba(255,255,255,.6)}
.cta-foot-right{display:flex;flex-direction:column;align-items:center;gap:8px;position:relative;z-index:1}
.btn-cta-main{display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;background:#fff;color:#0D0D0D;box-shadow:0 4px 20px rgba(0,0,0,.2);white-space:nowrap;transition:opacity .15s,transform .15s}
.btn-cta-main:hover{opacity:.95;transform:translateY(-2px)}
.cta-note{font-size:12px;color:rgba(255,255,255,.4)}

@media(max-width:1024px){
  .lp-detail-grid{grid-template-columns:1fr}
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
  .lp-cta-row{justify-content:center}
  .wk-bullets{grid-template-columns:1fr}
  .cta-foot-inner{flex-direction:column;text-align:center;align-items:center}
}
@media(max-width:480px){
  .behind-grid{grid-template-columns:1fr}
  .behind-item{border-right:none!important;border-bottom:1px solid var(--border)}
  .behind-item:last-child{border-bottom:none}
}
</style>
</head>
<body>

<x-public-nav :links="\App\Support\PublicNav::links()">
  <x-slot:cta>
    <a href="{{ route('register', ['worker' => $worker['slug']]) }}" class="btn-cta">Reserve Early Access <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
  </x-slot>
  <x-slot:mobileCta>
    <a href="{{ route('register', ['worker' => $worker['slug']]) }}" class="btn-cta" style="justify-content:center">Reserve Early Access →</a>
  </x-slot>
</x-public-nav>

<!-- HERO -->
<section class="hero-page">
  <div class="hero-page-left">
    <div class="hero-page-inner">
      <div class="lp-badge"><span class="lp-badge-dot"></span> Early Access &middot; Not Yet Live</div>
      <h1 class="hero-page-h">The AI Agent for <em>{{ $worker['name'] }}</em>, coming to UNITELO.</h1>
      <p class="hero-page-p">{{ $worker['tagline'] }} It isn't built yet, but its memory can start training now.</p>
      <div class="lp-cta-row">
        <a href="{{ route('register', ['worker' => $worker['slug']]) }}" class="btn-lp-main">Reserve Early Access <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
      </div>
      <div class="lp-note">No credit card required. Free to start training its memory today.</div>
    </div>
  </div>
  <div class="hero-page-right">
    <span class="hero-page-spacer" aria-hidden="true"></span>
    <div class="lp-placeholder-panel">
      <svg class="lp-placeholder-icon" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $iconPath }}"/></svg>
    </div>
    <div class="hero-fade-page"></div>
    <div class="hero-badge">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M12 2l1.5 4.5H18l-3.75 2.75 1.5 4.5L12 11l-3.75 2.75 1.5-4.5L6 6.5h4.5L12 2z" fill="#F59E0B"/></svg>
      <div class="badge-txt">In development.<br>Reserve your spot early.</div>
    </div>
  </div>
</section>

<!-- FEATURE CARD -->
<section class="lp-card-sec">
  <div class="w">
    <div class="lp-card-wrap">
      <div class="sec-eye">What it will do</div>
      <h2 class="sec-h">One worker, one job: {{ $worker['category'] }}.</h2>
      <div class="wk-card">
        <div class="wk-head">
          <div class="wk-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="{{ $iconPath }}"/></svg>
          </div>
          <div class="wk-name">{{ $worker['name'] }} <span class="lp-soon-tag">Early Access</span></div>
        </div>
        <div class="wk-role">{{ $worker['role'] }}</div>
        <p class="wk-quote">{{ $worker['tagline'] }}</p>
        <div class="wk-bullets">
          @foreach ($worker['bullets'] as $bullet)
            <div class="wk-bullet"><div class="wk-check"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>{{ $bullet }}</div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

<!-- DETAIL: Connects To / Produces / Memory Requirements -->
<section class="lp-detail-sec">
  <div class="w">
    <div class="lp-detail-grid">
      <div class="lp-detail-col">
        <div class="lp-detail-h">Connects to</div>
        @foreach ($worker['connects_to'] as $item)
          <div class="lp-detail-row">
            <div class="lp-detail-icon">
              @if (($item['icon'] ?? null) === 'drive')
                {!! $driveLogo !!}
              @else
                <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon'] ?? 'folder'] ?? $icons['folder'] }}"/></svg>
              @endif
            </div>
            <div>
              <div class="lp-detail-label">{{ $item['label'] }}</div>
              <div class="lp-detail-desc">{{ $item['desc'] }}</div>
            </div>
          </div>
        @endforeach
      </div>
      <div class="lp-detail-col">
        <div class="lp-detail-h">What it produces</div>
        @foreach ($worker['produces'] as $item)
          <div class="lp-detail-row">
            <div class="lp-detail-icon"><svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon'] ?? 'folder'] ?? $icons['folder'] }}"/></svg></div>
            <div>
              <div class="lp-detail-label">{{ $item['label'] }}</div>
              <div class="lp-detail-desc">{{ $item['desc'] }}</div>
            </div>
          </div>
        @endforeach
      </div>
      <div class="lp-detail-col">
        <div class="lp-detail-h">Memory requirements</div>
        @foreach ($worker['memory_requirements'] as $item)
          <div class="lp-detail-row">
            <div class="lp-detail-icon"><svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon'] ?? 'folder'] ?? $icons['folder'] }}"/></svg></div>
            <div>
              <div class="lp-detail-label">{{ $item['label'] }}</div>
              <div class="lp-detail-desc">{{ $item['desc'] }}</div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

<!-- BEHIND EVERY WORKER -->
<div class="behind-bar">
  <div class="w">
    <div class="behind-intro">
      <div class="sec-eye">Behind every worker</div>
      <h2 class="sec-h">Real lives. Real work. Real results.</h2>
      <p class="sec-p">Every UNITELO worker operates with the same commitment: learning, improving, and reporting back after every single task.</p>
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
          <p class="behind-p">Each worker believes they're alone at UNITELO, for now. As you hire more, they'll learn to collaborate. Stay tuned.</p>
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
        <div class="cta-eyebrow">Get in early</div>
        <h2 class="cta-foot-h">Start training {{ $worker['name'] }}'s memory today.</h2>
        <p class="cta-foot-sub">Reserve your spot now — your brand profile and assets will be ready the moment it launches.</p>
      </div>
      <div class="cta-foot-right">
        <a href="{{ route('register', ['worker' => $worker['slug']]) }}" class="btn-cta-main">
          Reserve Early Access
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <span class="cta-note">No credit card required.</span>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<x-public-footer />

@include('partials.tracking')
</body>
</html>

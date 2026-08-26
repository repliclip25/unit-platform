@extends('layouts.public')
@section('title', 'About UNITELO | AI Agents Built for Real Business Work')
@section('description', 'UNITELO turns specialized AI agents into AI Workers trained on organizational Memory and responsible for complete business workflows.')

@section('head')
<style>
/* ── ABOUT HERO ── */
.about-hero{padding:clamp(56px,8vw,88px) 0 0;overflow:hidden}
.about-hero-grid{display:grid;grid-template-columns:1.1fr 1fr;gap:clamp(32px,5vw,64px);align-items:center}
.about-hero h1{font-family:var(--fd);font-size:clamp(30px,4.4vw,52px);font-weight:800;letter-spacing:-1.5px;line-height:1.08;color:var(--text);margin-bottom:18px}
.about-hero-p{font-size:16.5px;color:var(--t2);line-height:1.7;max-width:480px}
.about-hero-img{border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.12)}
.about-hero-img img{width:100%;display:block;object-fit:cover;object-position:center 20%;max-height:440px}

/* ── SECTION HEADER ── */
.about-sec{padding:clamp(48px,6vw,72px) 0}
.about-sec-h{max-width:640px;margin-bottom:36px}

/* ── BELIEF ROWS (icon-left, not boxed cards) ── */
.belief-list{display:flex;flex-direction:column}
.belief-item{display:flex;gap:20px;padding:26px 0;border-top:1px solid var(--line)}
.belief-item:last-child{border-bottom:1px solid var(--line)}
.belief-icon{width:40px;height:40px;flex-shrink:0;border-radius:10px;background:rgba(0,0,0,.04);display:flex;align-items:center;justify-content:center;color:var(--text)}
[data-theme="dark"] .belief-icon{background:rgba(255,255,255,.06)}
.belief-icon svg{width:19px;height:19px}
.belief-text h4{font-size:1.05rem;font-weight:800;color:var(--text);margin-bottom:6px}
.belief-text p{font-size:14.5px;color:var(--t3);line-height:1.7}

/* ── WORKFORCE GRID ── */
.workforce-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
.wf-card{border:1px solid var(--line);border-radius:16px;overflow:hidden;background:var(--card);transition:transform .15s,box-shadow .2s}
.wf-card:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(0,0,0,.08)}
.wf-img{aspect-ratio:1/1;overflow:hidden;background:#111}
.wf-img img{width:100%;height:100%;object-fit:cover;object-position:center top}
.wf-body{padding:16px 18px 20px}
.wf-name{font-family:var(--fd);font-size:1.1rem;font-weight:800;letter-spacing:-.02em;color:var(--text);display:flex;align-items:center;gap:8px;margin-bottom:4px}
.wf-badge{display:inline-block;padding:2px 8px;border-radius:99px;background:rgba(245,197,24,.15);color:#8a6a06;font-size:9.5px;font-weight:700;letter-spacing:.05em;text-transform:uppercase}
.wf-role{font-size:10.5px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--t3);margin-bottom:10px}
.wf-quote{font-size:13px;color:var(--t2);line-height:1.6}

/* ── NUMBERS PANEL ── */
.numbers-panel{background:#0A0A0A;border-radius:24px;padding:clamp(36px,5vw,56px) clamp(28px,5vw,56px);display:grid;grid-template-columns:repeat(2,1fr);gap:32px}
.numbers-panel .stat-n{font-family:var(--fd);font-size:clamp(2.4rem,5vw,3.4rem);font-weight:800;color:#fff;letter-spacing:-.02em;margin-bottom:6px}
.numbers-panel .stat-l{font-size:13px;color:rgba(255,255,255,.5)}

/* ── FINAL CTA (matches worker page pattern) ── */
.about-cta{position:relative;overflow:hidden;background:#0A0A0A;padding:clamp(56px,7vw,88px) 0;border-top:1px solid #1F1F1F}
.about-cta-inner{max-width:640px}
.about-cta-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:16px}
.about-cta h2{font-family:var(--fd);font-size:clamp(1.6rem,3.4vw,2.4rem);font-weight:800;line-height:1.15;letter-spacing:-.03em;color:#fff;margin-bottom:14px}
.about-cta p{font-size:14.5px;color:rgba(255,255,255,.55);line-height:1.75;margin-bottom:10px}
.about-cta a.btn-cta-final{display:inline-flex;align-items:center;gap:8px;padding:13px 26px;border-radius:12px;font-size:14.5px;font-weight:700;color:#0D0D0D;background:#fff;margin-top:14px;transition:opacity .15s,transform .15s}
.about-cta a.btn-cta-final:hover{opacity:.9;transform:translateY(-2px)}
.about-cta a.cta-link{color:rgba(255,255,255,.75);font-weight:600;text-decoration:underline;text-underline-offset:3px}

@media(max-width:900px){
  .about-hero-grid{grid-template-columns:1fr}
  .about-hero-img{order:-1}
  .workforce-grid{grid-template-columns:repeat(2,1fr)}
  .numbers-panel{grid-template-columns:1fr}
}
</style>
@endsection

@section('body')

<section class="about-hero">
  <div class="w about-hero-grid">
    <div>
      <div class="eyebrow">The Company</div>
      <h1>Built by people who ran the workflows first.</h1>
      <p class="about-hero-p">We're operations leads and engineers who got tired of building automation that still needed people to babysit it. UNITELO is what we built instead.</p>
    </div>
    <div class="about-hero-img">
      <img src="{{ asset('images/hero-team-2.png') }}" alt="AVA, DOX, MOX and NUX: the UNITELO AI workforce">
    </div>
  </div>
</section>

<section class="about-sec">
  <div class="w" style="max-width:800px">
    <div class="about-sec-h">
      <div class="slabel">Our story</div>
      <h2 class="sh2" style="font-size:32px">From running the process to automating it</h2>
    </div>
    <div class="pub-body" style="padding:0">
      <p>We didn't start as a software company. We started running an IT and digital agency, tracking domain renewals, hosting renewals, and vendor contracts across every client at once. A missed renewal wasn't just an internal mistake, it was a client-facing problem. Reminder emails got buried, filtered, or never arrived at all.</p>
      <p>The tools that existed were either too generic (spreadsheets, shared inboxes) or too expensive (enterprise software built for teams far bigger than ours). So we built AVA to solve our own problem first: a worker that watched every renewal, not just the inbox, and caught the ones that would have otherwise slipped through.</p>
      <p>UNITELO is that system, made available to every team.</p>
    </div>
  </div>
</section>

<section class="about-sec sec-dark">
  <div class="w" style="max-width:800px">
    <div class="about-sec-h">
      <div class="slabel">What we believe</div>
      <h2 class="sh2" style="font-size:32px">Three things every UNITELO worker is built on</h2>
    </div>
    <div class="belief-list">
      <div class="belief-item">
        <div class="belief-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
        <div class="belief-text">
          <h4>Workers, not just tools</h4>
          <p>A spreadsheet doesn't know when a filing window opens. A general AI assistant doesn't know what documentation a specific agency requires. Our workers are trained on the actual process: they know the forms, the deadlines, and the quirks of each workflow.</p>
        </div>
      </div>
      <div class="belief-item">
        <div class="belief-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c1.5 0 2.9.37 4.14 1.02"/></svg></div>
        <div class="belief-text">
          <h4>Humans stay in control</h4>
          <p>Every worker surfaces work for your review. Nothing goes out without your approval. The AI does the prep; you make the call.</p>
        </div>
      </div>
      <div class="belief-item">
        <div class="belief-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/></svg></div>
        <div class="belief-text">
          <h4>Transparency over magic</h4>
          <p>Every transaction is logged. Every pipeline step is visible. You always know exactly what ran, when it ran, and why.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="about-sec">
  <div class="w">
    <div class="about-sec-h">
      <div class="slabel">Our AI workforce</div>
      <h2 class="sh2" style="font-size:32px">One live worker. Three more in development.</h2>
      <p class="ssub" style="margin-top:10px">UNITELO's first live AI Worker is AVA, an AI Renewal Coordinator built to track domain, hosting, and vendor renewals for IT &amp; digital agencies, now also serving Insurance Brokers and Compliance &amp; Licensing Firms. Each worker that follows is purpose-built for a specific workflow, not a general-purpose assistant.</p>
    </div>
    <div class="workforce-grid">
      <a href="{{ route('public.workers.show', 'ava') }}" class="wf-card" style="text-decoration:none">
        <div class="wf-img"><img src="{{ asset('images/ava-stand.png') }}" alt="AVA"></div>
        <div class="wf-body">
          <div class="wf-name">AVA</div>
          <div class="wf-role">AI Renewal Worker</div>
          <p class="wf-quote">Owns your complete renewal lifecycle.</p>
        </div>
      </a>
      <div class="wf-card">
        <div class="wf-img"><img src="{{ asset('images/dox.png') }}" alt="DOX"></div>
        <div class="wf-body">
          <div class="wf-name">DOX <span class="wf-badge">Coming Soon</span></div>
          <div class="wf-role">AI Document Worker</div>
          <p class="wf-quote">Owns document organization, retrieval, and structured workflows.</p>
        </div>
      </div>
      <div class="wf-card">
        <div class="wf-img"><img src="{{ asset('images/mox.png') }}" alt="MOX"></div>
        <div class="wf-body">
          <div class="wf-name">MOX <span class="wf-badge">Coming Soon</span></div>
          <div class="wf-role">AI Brand Intelligence Worker</div>
          <p class="wf-quote">Finds high-value brand opportunities worth acting on.</p>
        </div>
      </div>
      <div class="wf-card">
        <div class="wf-img"><img src="{{ asset('images/nux.png') }}" alt="NUX"></div>
        <div class="wf-body">
          <div class="wf-name">NUX <span class="wf-badge">Coming Soon</span></div>
          <div class="wf-role">AI Publishing Worker</div>
          <p class="wf-quote">Owns your publishing workflow from draft to distribution.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="about-sec">
  <div class="w">
    <div class="numbers-panel">
      <div>
        <div class="stat-n">{{ number_format($totalTx) }}</div>
        <div class="stat-l">Renewals processed by AVA</div>
      </div>
      <div>
        <div class="stat-n">{{ number_format($liveDeployments) }}</div>
        <div class="stat-l">Businesses running UNITELO</div>
      </div>
    </div>
  </div>
</section>

<section class="about-cta">
  <div class="w">
    <div class="about-cta-inner">
      <div class="about-cta-eyebrow">Get in touch</div>
      <h2>Questions, partnerships, or a workflow you want automated.</h2>
      <p>Email us at <a href="mailto:hello@unit.report" class="cta-link">hello@unit.report</a> and we'll get back to you.</p>
      <p>Interested in early access, a pilot, or building a worker for your team's workflow?</p>
      <a href="{{ route('influencer.apply') }}" class="btn-cta-final">
        Apply to our partner program
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</section>

@endsection

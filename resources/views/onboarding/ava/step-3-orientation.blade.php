<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Orientation — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{font-family:'Inter',sans-serif;background:#F4F3F1;color:#0D0D0D;-webkit-font-smoothing:antialiased}

.ob-page{display:grid;grid-template-columns:260px 1fr;height:100vh;overflow:hidden}

/* ── SIDEBAR ── */
.ob-sidebar{background:#F4F3F1;display:flex;flex-direction:column;padding:32px 24px;overflow-y:auto}
.ob-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;margin-bottom:44px}
.ob-steps{display:flex;flex-direction:column;flex:1}
.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative;text-decoration:none;color:inherit}
.ob-step:not(:last-child) .ob-step-rail::after{content:'';position:absolute;left:13px;top:30px;width:2px;height:calc(100% - 6px);background:#DCDCDC;border-radius:2px}
.ob-step.done:not(:last-child) .ob-step-rail::after{background:#0D0D0D}
.ob-step-rail{position:relative;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding-bottom:32px}
.ob-step:last-child .ob-step-rail{padding-bottom:0}
.ob-step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;position:relative;z-index:1;flex-shrink:0}
.ob-step.pending .ob-step-num{background:#E8E7E4;color:#888;border:1.5px solid #DCDCDC}
.ob-step.active  .ob-step-num{background:#0D0D0D;color:#fff;box-shadow:0 0 0 4px rgba(0,0,0,.1)}
.ob-step.done    .ob-step-num{background:#22c55e;color:#fff}
.ob-step-body{padding-top:4px;padding-bottom:28px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:13.5px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-step.pending .ob-step-label{color:#6B7280}
.ob-step-desc{font-size:12px;color:#9CA3AF;margin-top:3px;line-height:1.4}
.ob-step.active .ob-step-desc{color:#374151}
.ob-step.active .ob-step-body{background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:10px 14px;margin-right:-4px}
.ob-security{margin-top:8px;padding:14px 16px;border-radius:12px;background:#ECEAE6;border:1px solid #DCDCDC}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:5px}
.ob-security-row svg{width:13px;height:13px;stroke:#6B7280;flex-shrink:0}
.ob-security-title{font-size:12px;font-weight:700;color:#0D0D0D}
.ob-security p{font-size:11px;color:#6B7280;line-height:1.55}

/* ── CARD AREA ── */
.ob-card-area{display:flex;align-items:center;justify-content:center;padding:20px 24px 20px 12px;overflow:hidden}
.ob-card{
  display:grid;grid-template-columns:1fr 290px;
  width:100%;height:100%;max-height:calc(100vh - 40px);
  border-radius:20px;overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);
  border:1px solid rgba(0,0,0,.07);
}

/* ── HERO ── */
.ob-hero{position:relative;overflow:hidden;background:#2a2420}
.ob-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 30%}
.ob-hero-fade{
  position:absolute;inset:0;z-index:1;pointer-events:none;
  background:linear-gradient(to right,#fff 0%,#fff 28%,rgba(255,255,255,.9) 42%,rgba(255,255,255,.35) 60%,transparent 76%);
}
.ob-hero-content{
  position:relative;z-index:2;
  padding:36px 36px 32px;max-width:460px;height:100%;
  display:flex;flex-direction:column;justify-content:center;
  overflow-y:auto;
}
.ob-hero-content::-webkit-scrollbar{width:4px}
.ob-hero-content::-webkit-scrollbar-track{background:transparent}
.ob-hero-content::-webkit-scrollbar-thumb{background:rgba(0,0,0,.12);border-radius:2px}

/* Step eyebrow */
.ob-step-tag{
  display:inline-flex;align-items:center;gap:9px;
  font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
  color:#6B7280;margin-bottom:16px;width:fit-content;flex-shrink:0;
}
.ob-step-tag svg{width:16px;height:16px;stroke:#6B7280;stroke-width:2;fill:none;flex-shrink:0}

.ob-h1{font-size:clamp(1.65rem,2.1vw,2.1rem);font-weight:900;letter-spacing:-.04em;line-height:1.1;color:#0D0D0D;margin-bottom:8px}
.ob-gold{color:#0D0D0D;position:relative;display:inline}
.ob-gold::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}
.ob-sub{font-size:13px;color:#374151;line-height:1.65;margin-bottom:22px}

/* ── PERSONA CARDS ── */
.ob-personas{display:flex;flex-direction:column;gap:8px;margin-bottom:20px}

.ob-persona{
  display:flex;align-items:flex-start;gap:12px;
  background:rgba(255,255,255,.9);border:1.5px solid rgba(0,0,0,.08);
  border-radius:13px;padding:13px 14px;cursor:pointer;
  transition:border-color .15s,box-shadow .15s,background .15s;
  backdrop-filter:blur(4px);position:relative;
}
.ob-persona:hover{border-color:rgba(0,0,0,.2);background:rgba(255,255,255,.96)}
.ob-persona.is-selected{
  border-color:#0D0D0D;background:#fff;
  box-shadow:0 2px 12px rgba(0,0,0,.1);
}

/* Hidden radio */
.ob-persona input[type=radio]{position:absolute;opacity:0;width:0;height:0}

.ob-persona-icon{
  width:36px;height:36px;border-radius:10px;
  background:#F4F3F1;border:1px solid #E8E7E4;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
  transition:background .15s,border-color .15s;
}
.ob-persona.is-selected .ob-persona-icon{background:#0D0D0D;border-color:#0D0D0D}
.ob-persona-icon svg{width:17px;height:17px;stroke:#374151;stroke-width:1.8;fill:none;transition:stroke .15s}
.ob-persona.is-selected .ob-persona-icon svg{stroke:#fff}

.ob-persona-body{flex:1;min-width:0}
.ob-persona-label{font-size:13px;font-weight:800;color:#0D0D0D;line-height:1.2;margin-bottom:3px}
.ob-persona-tagline{font-size:11.5px;color:#6B7280;line-height:1.4}

/* Detail text — shown only when selected */
.ob-persona-detail{
  font-size:12px;color:#374151;line-height:1.55;
  margin-top:8px;
  display:none;
}
.ob-persona.is-selected .ob-persona-detail{display:block}

/* Example pills */
.ob-persona-examples{display:flex;flex-wrap:wrap;gap:5px;margin-top:8px;display:none}
.ob-persona.is-selected .ob-persona-examples{display:flex}
.ob-persona-pill{
  font-size:10.5px;padding:3px 9px;border-radius:99px;
  background:#F4F3F1;border:1px solid #E8E7E4;color:#374151;font-weight:500;
}

/* Check badge top-right */
.ob-persona-check{
  width:20px;height:20px;border-radius:50%;
  background:#0D0D0D;flex-shrink:0;margin-top:1px;
  display:flex;align-items:center;justify-content:center;
  opacity:0;transition:opacity .15s;
}
.ob-persona-check svg{width:11px;height:11px;stroke:#fff;stroke-width:3}
.ob-persona.is-selected .ob-persona-check{opacity:1}

/* Continue button */
.btn-continue{
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 20px;border-radius:13px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  font-size:14.5px;font-weight:800;letter-spacing:-.01em;
  transition:opacity .15s,transform .1s;width:100%;
  opacity:.4;pointer-events:none;
}
.btn-continue.is-active{opacity:1;pointer-events:auto}
.btn-continue.is-active:hover{opacity:.88;transform:translateY(-1px)}
.btn-continue svg{width:17px;height:17px;stroke:#fff;stroke-width:2.5;flex-shrink:0}

/* AVA speech bubble */
.ob-bubble{
  position:absolute;z-index:3;
  top:46%;right:6%;
  transform:translateY(-50%);
  background:#fff;border:1px solid #E5E7EB;
  border-radius:16px;border-bottom-left-radius:4px;
  padding:14px 18px;width:186px;
  box-shadow:0 4px 16px rgba(0,0,0,.1);
}
.ob-bubble p{font-size:12.5px;font-weight:600;color:#0D0D0D;line-height:1.55}

/* ── RIGHT PANEL ── */
.ob-profile{
  background:#fff;border-left:1px solid #F0F0F0;
  padding:28px 22px;display:flex;flex-direction:column;overflow-y:auto;
}
.emp-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px}
.emp-name{font-size:1.65rem;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;line-height:1}
.emp-role{font-size:13px;color:#374151;margin-top:4px;margin-bottom:16px}
.emp-divider{border:none;border-top:1px solid #F0F0F0;margin:0 0 14px}

/* What Ava will configure */
.ob-config-title{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px}
.ob-config-list{display:flex;flex-direction:column;gap:8px;flex:1}
.ob-config-item{display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#374151;line-height:1.45}
.ob-config-dot{
  width:6px;height:6px;border-radius:50%;background:#D1D5DB;
  flex-shrink:0;margin-top:5px;transition:background .3s;
}
.ob-config-dot.active{background:#F5C518}

/* Selected persona summary in right panel */
.ob-persona-summary{
  background:#F4F3F1;border:1px solid #E8E7E4;border-radius:12px;
  padding:12px 14px;margin-bottom:14px;
  display:none;
}
.ob-persona-summary.is-visible{display:block}
.ob-persona-summary-label{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#9CA3AF;margin-bottom:4px}
.ob-persona-summary-name{font-size:14px;font-weight:800;color:#0D0D0D}
.ob-persona-summary-tag{font-size:11.5px;color:#6B7280;margin-top:2px}

.ob-config-note{
  font-size:11px;color:#9CA3AF;line-height:1.55;
  margin-top:auto;padding-top:14px;
  border-top:1px solid #F0F0F0;
}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow:auto;height:auto}
  .ob-page{grid-template-columns:1fr;height:auto;overflow:visible}
  .ob-sidebar{flex-direction:row;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #E5E7EB;background:#fff;position:sticky;top:0;z-index:10}
  .ob-logo{margin-bottom:0;font-size:18px}
  .ob-steps{flex-direction:row;gap:8px;flex:0;align-items:center}
  .ob-step{flex-direction:column;align-items:center;gap:0}
  .ob-step-rail{padding-bottom:0}
  .ob-step:not(:last-child) .ob-step-rail::after{display:none}
  .ob-step-body{display:none}
  .ob-step-num{width:26px;height:26px;font-size:11px}
  .ob-security{display:none}
  .ob-card-area{padding:16px;overflow:visible;height:auto;align-items:flex-start}
  .ob-card{display:flex;flex-direction:column;width:100%;height:auto;max-height:none;box-shadow:0 2px 12px rgba(0,0,0,.08)}
  .ob-hero{display:flex;flex-direction:column;min-height:unset;background:#fff}
  .ob-hero-content{position:static;background:#fff;padding:20px;max-width:100%;height:auto;overflow-y:visible;justify-content:flex-start;order:1}
  .ob-hero-img{position:static;display:block;width:100%;height:200px;object-fit:cover;object-position:center 30%;order:2}
  .ob-hero-fade{display:none}
  .ob-bubble{display:none}
  .ob-h1{font-size:1.5rem}
  .ob-profile{border-left:none;border-top:1px solid #F0F0F0;padding:20px}
}
@media(max-width:480px){
  .ob-hero-img{height:160px}
  .ob-h1{font-size:1.35rem}
  .ob-card-area{padding:12px}
  .ob-profile{padding:16px}
}
</style>
</head>
<body>

@php
  $iconSvgs = [
    'computer'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
    'shield'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
    'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
    'grid'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>',
  ];
@endphp

<div class="ob-page">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ob-sidebar">
    <div class="ob-logo">UNIT</div>
    <div class="ob-steps">

      <a href="{{ route('hire.ava.welcome') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail">
          <div class="ob-step-num">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
        </div>
        <div class="ob-step-body">
          <div class="ob-step-label">Meet Ava</div>
          <div class="ob-step-desc">Complete</div>
        </div>
      </a>

      <a href="{{ route('hire.ava.workspace') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail">
          <div class="ob-step-num">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
        </div>
        <div class="ob-step-body">
          <div class="ob-step-label">Workspace</div>
          <div class="ob-step-desc">Complete</div>
        </div>
      </a>

      <div class="ob-step active">
        <div class="ob-step-rail"><div class="ob-step-num">3</div></div>
        <div class="ob-step-body">
          <div class="ob-step-label">Orientation</div>
          <div class="ob-step-desc">Teach Ava your business</div>
        </div>
      </div>

      <div class="ob-step pending">
        <div class="ob-step-rail"><div class="ob-step-num">4</div></div>
        <div class="ob-step-body">
          <div class="ob-step-label">First Assignment</div>
          <div class="ob-step-desc">Give Ava her first job</div>
        </div>
      </div>

      <div class="ob-step pending">
        <div class="ob-step-rail"><div class="ob-step-num">5</div></div>
        <div class="ob-step-body">
          <div class="ob-step-label">On Shift</div>
          <div class="ob-step-desc">Ava starts working for you</div>
        </div>
      </div>

    </div>
    <div class="ob-security">
      <div class="ob-security-row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>You're in control of what<br>Ava can see and access.</p>
    </div>
  </aside>

  {{-- ══ FLOATING CARD ══ --}}
  <div class="ob-card-area">
    <div class="ob-card">

      {{-- Hero --}}
      <div class="ob-hero">
        <img class="ob-hero-img" src="/images/ava-desk.png" alt="Ava orientation">
        <div class="ob-hero-fade"></div>

        <div class="ob-bubble">
          <p>Tell me what you do — I'll know exactly what to look for.</p>
        </div>

        <div class="ob-hero-content">

          <div class="ob-step-tag">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            Step 3 of 5
          </div>

          <h1 class="ob-h1">
            What kind of renewals<br>
            do you <span class="ob-gold">manage?</span>
          </h1>

          <p class="ob-sub">Not every renewal looks the same. Choose the business type closest to yours so Ava knows what to look for and how to communicate with your clients.</p>

          <form method="POST" action="{{ route('hire.ava.orientation.save') }}" id="personaForm">
            @csrf

            @if($errors->any())
            <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:12.5px;color:#dc2626">
              {{ $errors->first() }}
            </div>
            @endif

            <div class="ob-personas" id="personaList">
              @foreach($personas as $key => $p)
              @php $icon = $iconSvgs[$p['icon'] ?? 'grid'] ?? $iconSvgs['grid'] @endphp
              <label class="ob-persona {{ ($current ?? '') === $key ? 'is-selected' : '' }}" data-key="{{ $key }}">
                <input type="radio" name="persona" value="{{ $key }}" {{ ($current ?? '') === $key ? 'checked' : '' }}>
                <div class="ob-persona-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">{!! $icon !!}</svg>
                </div>
                <div class="ob-persona-body">
                  <div class="ob-persona-label">{{ $p['label'] }}</div>
                  <div class="ob-persona-tagline">{{ $p['tagline'] }}</div>
                  <div class="ob-persona-detail">{{ $p['detail'] }}</div>
                  <div class="ob-persona-examples">
                    @foreach($p['examples'] as $ex)
                    <span class="ob-persona-pill">{{ $ex }}</span>
                    @endforeach
                  </div>
                </div>
                <div class="ob-persona-check">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
              </label>
              @endforeach
            </div>

            <button type="submit" class="btn-continue {{ $current ? 'is-active' : '' }}" id="continueBtn">
              Continue — Set Up Ava
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>

          </form>

          <p style="font-size:11px;color:#9CA3AF;margin-top:10px">You can change this from your worker settings later.</p>

        </div>
      </div>

      {{-- Right panel ── --}}
      <div class="ob-profile">
        <div class="emp-eyebrow">Orientation</div>
        <div class="emp-name">AVA</div>
        <div class="emp-role">Renewal Specialist</div>

        <hr class="emp-divider">

        {{-- Selected persona summary — updates via JS --}}
        <div class="ob-persona-summary" id="personaSummary">
          <div class="ob-persona-summary-label">Business Type</div>
          <div class="ob-persona-summary-name" id="summaryName">—</div>
          <div class="ob-persona-summary-tag" id="summaryTag"></div>
        </div>

        <div class="ob-config-title">What Ava will configure</div>
        <div class="ob-config-list">
          <div class="ob-config-item">
            <span class="ob-config-dot" id="dot-rules"></span>
            Capture rules tuned to your renewal type
          </div>
          <div class="ob-config-item">
            <span class="ob-config-dot" id="dot-templates"></span>
            Email templates matched to your clients
          </div>
          <div class="ob-config-item">
            <span class="ob-config-dot" id="dot-assets"></span>
            Asset types set for your industry
          </div>
          <div class="ob-config-item">
            <span class="ob-config-dot" id="dot-tone"></span>
            Tone and language for your sector
          </div>
        </div>

        <p class="ob-config-note">
          Ava uses this to pre-configure her rules and templates before she starts watching your inbox.
        </p>
      </div>

    </div>
  </div>

</div>

<script>
(function(){
  const personas = @json(collect($personas)->map(fn($p) => ['label' => $p['label'], 'tagline' => $p['tagline']]));
  const dots     = ['dot-rules','dot-templates','dot-assets','dot-tone'];
  const summary  = document.getElementById('personaSummary');
  const sumName  = document.getElementById('summaryName');
  const sumTag   = document.getElementById('summaryTag');
  const btn      = document.getElementById('continueBtn');

  function updatePanel(key){
    const p = personas[key];
    if(p){
      sumName.textContent = p.label;
      sumTag.textContent  = p.tagline;
      summary.classList.add('is-visible');
      dots.forEach((id,i) => {
        setTimeout(() => {
          const el = document.getElementById(id);
          if(el) el.classList.add('active');
        }, i * 120);
      });
    }
    btn.classList.toggle('is-active', !!key);
  }

  document.querySelectorAll('.ob-persona').forEach(card => {
    card.addEventListener('click', function(){
      // Deselect all
      document.querySelectorAll('.ob-persona').forEach(c => c.classList.remove('is-selected'));
      dots.forEach(id => { const el = document.getElementById(id); if(el) el.classList.remove('active'); });

      card.classList.add('is-selected');
      const radio = card.querySelector('input[type=radio]');
      if(radio) radio.checked = true;

      updatePanel(card.dataset.key);
    });
  });

  // Init from server-side preselect
  const preselected = document.querySelector('.ob-persona.is-selected');
  if(preselected) updatePanel(preselected.dataset.key);
})();
</script>

<x-self-learn page="hire.ava.orientation" />
</body>
</html>

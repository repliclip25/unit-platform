{{--
    Single source of truth for the onboarding layout: progress-step sidebar
    (no worker switcher, no LINKS section — onboarding is pre-hire) + a
    floating two-column card (hero left, profile panel right).

    This is a deliberately separate design system from <x-ux2-shell> — light
    only, no theme toggle, hardcoded hex colors matching the original
    onboarding flow. Do not try to unify the two; they serve different
    moments (first-run vs. day-to-day platform use).

    Usage:
        <x-onboarding-shell title="First Assignment — UNIT" :steps="$steps" security-text="...">
            <x-slot:hero>
                <img class="ob-hero-img" src="..." alt="">
                <div class="ob-hero-fade"></div>
                <div class="ob-bubble"><p>...</p></div>
                <div class="ob-hero-content">...</div>
            </x-slot:hero>
            <x-slot:profile>...</x-slot:profile>
        </x-onboarding-shell>

    $steps: array of ['label' => ..., 'desc' => ..., 'state' => 'done'|'active'|'pending', 'href' => optional]
--}}
@props([
    'title'        => 'Onboarding — UNIT',
    'steps'        => [],
    'securityText' => "You're in control of what the worker can see and access.",
])
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title }}</title>
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

/* ── HERO (left) ── */
.ob-hero{position:relative;overflow:hidden;background:#1e1b18;display:flex;flex-direction:column}
.ob-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 20%}
.ob-hero-fade{
  position:absolute;inset:0;z-index:1;pointer-events:none;
  background:linear-gradient(to right,#fff 0%,#fff 30%,rgba(255,255,255,.9) 44%,rgba(255,255,255,.3) 62%,transparent 78%);
}
.ob-hero-content{
  position:relative;z-index:2;
  padding:28px 36px 24px;max-width:470px;
  display:flex;flex-direction:column;height:100%;
  overflow-y:auto;
}
.ob-hero-content::-webkit-scrollbar{width:4px}
.ob-hero-content::-webkit-scrollbar-track{background:transparent}
.ob-hero-content::-webkit-scrollbar-thumb{background:rgba(0,0,0,.12);border-radius:2px}

.ob-step-tag{
  display:inline-flex;align-items:center;gap:9px;
  font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
  color:#6B7280;margin-bottom:14px;width:fit-content;flex-shrink:0;
}
.ob-step-tag svg{width:16px;height:16px;stroke:#6B7280;stroke-width:2;fill:none;flex-shrink:0}

.ob-h1{font-size:clamp(1.55rem,2vw,2rem);font-weight:900;letter-spacing:-.04em;line-height:1.1;color:#0D0D0D;margin-bottom:8px;flex-shrink:0}
.ob-gold{color:#0D0D0D;position:relative;display:inline}
.ob-gold::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}
.ob-sub{font-size:13px;color:#374151;line-height:1.65;margin-bottom:16px;flex-shrink:0}

.ob-flash{
  display:flex;align-items:center;gap:8px;
  background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.22);
  border-radius:10px;padding:10px 14px;margin-bottom:14px;
  font-size:12.5px;font-weight:600;color:#16a34a;flex-shrink:0;
}
.ob-flash svg{flex-shrink:0}

.ob-form{
  background:rgba(255,255,255,.94);border:1.5px solid rgba(0,0,0,.08);
  border-radius:16px;padding:16px;margin-bottom:12px;
  backdrop-filter:blur(4px);flex-shrink:0;
}
.ob-form-title{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#6B7280;margin-bottom:12px}
.ob-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px}
.ob-field label{display:block;font-size:11px;font-weight:600;color:#6B7280;margin-bottom:4px}
.ob-field input,.ob-field select{
  width:100%;border:1.5px solid #E5E7EB;border-radius:8px;
  padding:8px 10px;font-size:12.5px;font-family:inherit;
  color:#0D0D0D;background:#fff;outline:none;
  transition:border-color .15s;
}
.ob-field input::placeholder{color:#B0B7C3}
.ob-field input:focus,.ob-field select:focus{border-color:#0D0D0D}
.ob-field.full{grid-column:1/-1}
.ob-field-req{color:#ef4444;margin-left:2px}

.ob-form-actions{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:2px}
.btn-add{
  display:flex;align-items:center;gap:6px;
  padding:9px 16px;border-radius:10px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  font-size:12.5px;font-weight:700;font-family:inherit;
  transition:opacity .15s;
}
.btn-add:hover{opacity:.85}
.btn-add svg{width:14px;height:14px;stroke:#fff;stroke-width:2.5}

.ob-import-link{
  font-size:11.5px;color:#6B7280;text-decoration:none;
  display:flex;align-items:center;gap:5px;
  transition:color .15s;
}
.ob-import-link:hover{color:#0D0D0D}
.ob-import-link svg{width:13px;height:13px;stroke:currentColor;stroke-width:2}

.ob-form-error{font-size:12px;color:#dc2626;margin-bottom:8px}
.ob-hint{font-size:11.5px;color:#9CA3AF;flex-shrink:0}

.ob-bubble{
  position:absolute;z-index:3;
  top:44%;right:6%;
  transform:translateY(-50%);
  background:#fff;border:1px solid #E5E7EB;
  border-radius:16px;border-bottom-left-radius:4px;
  padding:14px 18px;width:182px;
  box-shadow:0 4px 16px rgba(0,0,0,.1);
}
.ob-bubble p{font-size:12.5px;font-weight:600;color:#0D0D0D;line-height:1.55}

/* ── PROFILE PANEL (right) ── */
.ob-profile{
  background:#fff;border-left:1px solid #F0F0F0;
  padding:24px 20px;display:flex;flex-direction:column;overflow-y:auto;
}
.emp-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:8px}
.emp-name{font-size:1.5rem;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;line-height:1}
.emp-role{font-size:12.5px;color:#374151;margin-top:4px;margin-bottom:14px}
.emp-divider{border:none;border-top:1px solid #F0F0F0;margin:0 0 12px}

.ob-coverage-label{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
.ob-coverage-title{font-size:11px;font-weight:700;color:#374151}
.ob-coverage-pct{font-size:11px;font-weight:800;color:#22c55e}
.ob-coverage-bar{height:6px;background:#F3F4F6;border-radius:99px;overflow:hidden;margin-bottom:4px}
.ob-coverage-fill{height:100%;background:#22c55e;border-radius:99px;transition:width .6s ease}
.ob-coverage-note{font-size:10.5px;color:#9CA3AF;margin-bottom:14px;line-height:1.4}

.ob-clients-title{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:8px}
.ob-clients-empty{font-size:12px;color:#C4C9D4;text-align:center;padding:16px 0;line-height:1.55}
.ob-client-row{
  display:flex;align-items:center;gap:8px;
  padding:7px 0;border-bottom:1px solid #F5F5F5;
}
.ob-client-row:last-child{border-bottom:none}
.ob-client-dot{width:7px;height:7px;border-radius:50%;background:#22c55e;flex-shrink:0}
.ob-client-name{font-size:12px;font-weight:700;color:#0D0D0D;flex:1;min-width:0;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}
.ob-client-asset{font-size:11px;color:#9CA3AF;white-space:nowrap}

.btn-seed{
  width:100%;display:flex;align-items:center;gap:8px;
  padding:9px 14px;border-radius:10px;
  background:transparent;border:1.5px solid #E5E7EB;cursor:pointer;
  font-size:11.5px;font-weight:600;color:#6B7280;font-family:inherit;
  transition:border-color .15s,color .15s;margin-bottom:8px;
}
.btn-seed:hover{border-color:#0D0D0D;color:#0D0D0D}
.btn-seed svg{width:13px;height:13px;stroke:currentColor;stroke-width:2;flex-shrink:0}

.btn-continue{
  display:flex;align-items:center;justify-content:space-between;
  padding:13px 18px;border-radius:13px;
  background:#0D0D0D;color:#fff;border:none;cursor:not-allowed;
  font-size:13.5px;font-weight:800;letter-spacing:-.01em;
  transition:opacity .15s,transform .1s;width:100%;
  text-decoration:none;opacity:.35;pointer-events:none;
}
.btn-continue.is-active{opacity:1;pointer-events:auto;cursor:pointer}
.btn-continue.is-active:hover{opacity:.88;transform:translateY(-1px)}
.btn-continue svg{width:16px;height:16px;stroke:#fff;stroke-width:2.5;flex-shrink:0}

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
  .ob-hero-content{position:static;background:#fff;padding:20px;max-width:100%;height:auto;overflow-y:visible;order:1}
  .ob-hero-img{position:static;display:block;width:100%;height:200px;object-fit:cover;object-position:center 20%;order:2}
  .ob-hero-fade{display:none}
  .ob-bubble{display:none}
  .ob-form-grid{grid-template-columns:1fr}
  .ob-h1{font-size:1.45rem}
  .ob-profile{border-left:none;border-top:1px solid #F0F0F0;padding:20px}
}
@media(max-width:480px){
  .ob-hero-img{height:160px}
  .ob-h1{font-size:1.3rem}
  .ob-card-area{padding:12px}
  .ob-profile{padding:16px}
}
</style>
{{ $styles ?? '' }}
</head>
<body>

<div class="ob-page">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ob-sidebar">
    <div class="ob-logo">UNIT</div>
    <div class="ob-steps">
      @foreach($steps as $step)
      @php $tag = ($step['state'] ?? 'pending') === 'done' && !empty($step['href']) ? 'a' : 'div'; @endphp
      <{{ $tag }} @if($tag==='a') href="{{ $step['href'] }}" @endif class="ob-step {{ $step['state'] ?? 'pending' }}" style="text-decoration:none">
        <div class="ob-step-rail">
          <div class="ob-step-num">
            @if(($step['state'] ?? 'pending') === 'done')
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            @else
              {{ $step['num'] ?? '' }}
            @endif
          </div>
        </div>
        <div class="ob-step-body">
          <div class="ob-step-label">{{ $step['label'] }}</div>
          <div class="ob-step-desc">{{ $step['desc'] }}</div>
        </div>
      </{{ $tag }}>
      @endforeach
    </div>
    <div class="ob-security">
      <div class="ob-security-row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>{{ $securityText }}</p>
    </div>
  </aside>

  {{-- ══ FLOATING CARD ══ --}}
  <div class="ob-card-area">
    <div class="ob-card">
      <div class="ob-hero">
        {{ $hero }}
      </div>
      <aside class="ob-profile">
        {{ $profile }}
      </aside>
    </div>
  </div>

</div>
{{ $scripts ?? '' }}
</body>
</html>

{{--
    TEMPLATE — "Worker's Desk" layout reference.

    This is the canonical structure for a worker's home page (currently only
    AVA's desk at /desk/ava, future workers get their own). Distinct from
    the plain "Page" template: the main column is a hero card (background
    image, fade, intro copy, chat bubble) instead of a plain content card,
    and the right panel carries the worker's identity + live transaction
    detail instead of being empty or a simple summary.

    Improve the shell here (via <x-ux2-shell>) or the hero/profile
    conventions below, then carry matching fixes into desk/ava.blade.php
    and any future worker desk page. Reachable at /templates/desk.
--}}
<x-ux2-shell title="Desk Template — UNIT" active-slug="ava" active-link="" security-text="Example security blurb goes here.">

    <x-slot:styles>
    <style>
    .tpl-card-area{display:flex;align-items:center;justify-content:center;padding:8px 24px 20px 12px;overflow:hidden}
    .tpl-card{display:grid;grid-template-columns:1fr 320px;width:100%;height:100%;max-height:calc(100vh - 84px);border-radius:20px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);border:1px solid var(--db-border);position:relative}
    .tpl-hero{position:relative;overflow:hidden;background:#1e1b18;display:flex;flex-direction:column;z-index:1}
    .tpl-hero-fade{position:absolute;inset:0;z-index:1;pointer-events:none;background:linear-gradient(to right,#fff 0%,#fff 30%,rgba(255,255,255,.9) 44%,rgba(255,255,255,.3) 62%,transparent 78%)}
    .tpl-hero-content{position:relative;z-index:2;padding:28px 36px 24px;max-width:470px}
    .tpl-h1{font-size:clamp(1.55rem,2vw,2rem);font-weight:900;letter-spacing:-.04em;line-height:1.1;color:#0D0D0D;margin-bottom:8px}
    .tpl-sub{font-size:13px;color:#374151;line-height:1.65}
    .tpl-bubble{position:absolute;z-index:3;top:44%;right:6%;transform:translateY(-50%);background:#fff;border:1px solid #E5E7EB;border-radius:16px;border-bottom-left-radius:4px;padding:14px 18px;width:182px;box-shadow:0 4px 16px rgba(0,0,0,.1)}
    .tpl-bubble p{font-size:12.5px;font-weight:600;color:#0D0D0D;line-height:1.55}
    .tpl-profile{background:var(--db-card);border-left:1px solid var(--db-border);padding:20px;display:flex;flex-direction:column;overflow-y:auto;position:relative;z-index:1}
    .tpl-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:6px}
    .tpl-name{font-size:1.5rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);line-height:1}
    .tpl-role{font-size:12px;color:var(--db-text-muted);margin-top:3px;margin-bottom:12px}
    .tpl-divider{border:none;border-top:1px solid var(--db-border);margin:0 0 12px}
    .tpl-badge-row{display:flex;flex-wrap:wrap;gap:6px 10px;margin-bottom:12px}
    .tpl-badge{font-size:11px;font-weight:700;color:var(--db-text-muted)}
    .tpl-badge.active{color:var(--db-text);text-decoration:underline;text-underline-offset:3px}
    .tpl-canvas{flex:1;border-top:1px solid var(--db-border);margin:0 -20px;padding:12px 20px 0;overflow-y:auto}
    .tpl-data-row{padding:8px 0;border-bottom:1px solid var(--db-border)}
    .tpl-data-key{font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--db-text-muted)}
    .tpl-data-val{font-size:12.5px;color:var(--db-text);line-height:1.6}
    @media(max-width:1024px){ .tpl-card{display:flex;flex-direction:column;height:auto;max-height:none} .tpl-bubble{display:none} }
    </style>
    </x-slot:styles>

    <div class="tpl-card-area">
      <div class="tpl-card">
        <div class="tpl-hero">
          <div class="tpl-hero-fade"></div>
          <div class="tpl-bubble"><p>Example worker chat bubble — a short line of personality.</p></div>
          <div class="tpl-hero-content">
            <div class="tpl-h1">Welcome back, @{{firstName}}</div>
            <div class="tpl-sub">One or two lines summarizing what the worker did recently, or what needs review right now.</div>
          </div>
        </div>

        <aside class="tpl-profile">
          <div class="tpl-eyebrow">On Shift</div>
          <div class="tpl-name">WORKER</div>
          <div class="tpl-role">Example role description</div>
          <hr class="tpl-divider">

          <div class="tpl-badge-row">
            <span class="tpl-badge active">Reviewed</span>
            <span class="tpl-badge">Assessed</span>
            <span class="tpl-badge">Verified</span>
            <span class="tpl-badge">Prepared</span>
            <span class="tpl-badge">Delivered</span>
          </div>

          <div class="tpl-canvas">
            <div class="tpl-data-row">
              <div class="tpl-data-key">Example field</div>
              <div class="tpl-data-val">Example value pulled from the active transaction</div>
            </div>
            <div class="tpl-data-row">
              <div class="tpl-data-key">Another field</div>
              <div class="tpl-data-val">More example content</div>
            </div>
          </div>
        </aside>
      </div>
    </div>

</x-ux2-shell>

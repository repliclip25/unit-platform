{{--
    TEMPLATE — "Page" layout reference.

    This is the canonical structure for a plain content page built on the
    UX2 shell: Memory, Templates, Rules, Fast Track, Connect, and Billing
    all follow this exact shape (title/subtitle header, one or more cards
    in the main column, an empty or populated right panel).

    Improve the shell here (via <x-ux2-shell>) or the page-body conventions
    below, then carry matching fixes into the real pages that use this
    shape. Reachable at /templates/page.
--}}
<x-ux2-shell title="Page Template — UNIT" active-slug="ava" active-link="Memory" security-text="Example security blurb goes here.">

    <x-slot:styles>
    <style>
    .tpl-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);margin-bottom:4px}
    .tpl-sub{font-size:13px;color:var(--db-text-muted);margin-bottom:20px}
    .tpl-card{border:1px solid var(--db-border);border-radius:16px;padding:20px;margin-bottom:16px}
    .tpl-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap}
    .tpl-card-title{font-size:14px;font-weight:700;color:var(--db-text)}
    .tpl-card-sub{font-size:12px;color:var(--db-text-muted);margin-top:2px}
    .tpl-row{padding:12px 0;border-bottom:1px solid var(--db-border);font-size:13px;color:var(--db-text)}
    .tpl-row:last-child{border-bottom:none}
    .tpl-empty{text-align:center;padding:36px 18px;border:1px dashed var(--db-border);border-radius:14px;color:var(--db-text-muted);font-size:12.5px}
    </style>
    </x-slot:styles>

    <div class="mem-card-area">
      <main class="mem-main">
        <div class="mem-wrap">

          {{-- Status banners — real pages show these conditionally via session('success')/session('error') --}}
          <div class="mem-status success">Example success banner</div>

          <div class="tpl-h1">Page Title</div>
          <div class="tpl-sub">One line explaining what this page lets the tenant do.</div>

          {{-- Primary content card --}}
          <div class="tpl-card">
            <div class="tpl-card-head">
              <div>
                <div class="tpl-card-title">Card title</div>
                <div class="tpl-card-sub">Card subtitle / count / metadata</div>
              </div>
              <button type="button" class="mem-btn">Primary action</button>
            </div>
            <div class="tpl-row">Example row one — label, value, inline actions</div>
            <div class="tpl-row">Example row two</div>
            <div class="tpl-row">Example row three</div>
          </div>

          {{-- Empty-state pattern --}}
          <div class="tpl-empty">Empty state — shown when there's nothing to list yet</div>

        </div>
      </main>

      <aside class="mem-right"></aside>
    </div>

</x-ux2-shell>

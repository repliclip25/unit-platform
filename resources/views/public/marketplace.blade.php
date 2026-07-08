@extends('layouts.public')
@section('title', 'Worker Marketplace')
@section('description', 'Browse UNIT AI employees — each one purpose-built for a specific workflow. Hire the one that fits your process.')

@section('head')
<style>
/* ── Search ── */
.mkt-search-wrap{padding:48px 0 0}
.mkt-search-box{position:relative;max-width:540px}
.mkt-search-box input{width:100%;padding:13px 20px 13px 48px;border-radius:12px;border:1px solid var(--line2);background:var(--card);color:var(--text);font-size:15px;outline:none;transition:border-color .2s;font-family:var(--fb)}
.mkt-search-box input:focus{border-color:rgba(241,211,98,0.5)}
.mkt-search-box input::placeholder{color:var(--t4)}
.mkt-search-icon{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--t4)}
.filter-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:16px}
.filter-btn{font-size:12.5px;font-weight:600;padding:6px 14px;border-radius:20px;border:1px solid var(--line);background:transparent;color:var(--t3);cursor:pointer;transition:all .15s;font-family:var(--fb)}
.filter-btn.active,.filter-btn:hover{background:rgba(241,211,98,0.1);border-color:rgba(241,211,98,0.4);color:var(--gold-text)}

/* ── Worker Card Grid ── */
.worker-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;margin-top:40px;padding-bottom:60px}
.wk-card{background:var(--card);border:1px solid var(--line);border-radius:18px;overflow:hidden;display:flex;flex-direction:column;transition:border-color .2s,box-shadow .2s}
.wk-card:hover{border-color:rgba(241,211,98,0.3);box-shadow:0 12px 40px rgba(0,0,0,0.3)}
.wk-card-head{padding:24px 24px 0}
.wk-card-identity{display:flex;align-items:center;gap:14px;margin-bottom:16px}
.wk-av{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-family:var(--fd);font-weight:800;font-size:22px;flex-shrink:0}
.wk-card-name{font-family:var(--fd);font-size:20px;font-weight:800;letter-spacing:-.3px;line-height:1}
.wk-card-role{font-size:12.5px;color:var(--t3);margin-top:3px}
.wk-card-desc{font-size:14px;color:var(--t2);line-height:1.65;margin-bottom:16px}
.wk-card-caps{display:flex;flex-direction:column;gap:6px;margin-bottom:18px;padding-bottom:18px;border-bottom:1px solid var(--line)}
.wk-cap-item{display:flex;align-items:flex-start;gap:8px;font-size:13px;color:var(--t2)}
.wk-cap-dot{width:5px;height:5px;border-radius:50%;flex-shrink:0;margin-top:6px}
.wk-card-tags{display:flex;flex-wrap:wrap;gap:5px;padding:0 24px 18px}
.wk-tag{font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;background:rgba(255,255,255,0.06);border:1px solid var(--line);color:var(--t3)}
[data-theme="light"] .wk-tag{background:rgba(0,0,0,0.04)}
.wk-card-btns{padding:0 24px 24px;display:flex;gap:8px;margin-top:auto}
.live-chip{display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:700;padding:4px 10px;border-radius:20px;background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.25);color:#22c55e;margin-left:auto}
.live-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pulse-g 2s infinite;flex-shrink:0}
@keyframes pulse-g{0%,100%{opacity:1}50%{opacity:.4}}
.soon-chip{display:inline-flex;font-size:10.5px;font-weight:700;padding:4px 10px;border-radius:20px;background:rgba(255,255,255,0.06);border:1px solid var(--line);color:var(--t4);margin-left:auto}
.wk-card-soon{opacity:.55}

/* ── Request Worker Section ── */
.req-sec{background:var(--surf);border-top:1px solid var(--line);padding:80px 0}
[data-theme="light"] .req-sec{background:#f5f5f3}
.req-grid{display:grid;grid-template-columns:1fr 1.2fr;gap:64px;align-items:start}
.req-form{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:36px}
.req-form label{display:block;font-size:11.5px;font-weight:700;letter-spacing:.5px;color:var(--t3);margin-bottom:6px;text-transform:uppercase}
.req-in{width:100%;padding:11px 14px;border-radius:9px;border:1px solid var(--line);background:var(--raised);color:var(--text);font-size:14px;outline:none;transition:border-color .2s;font-family:var(--fb);margin-bottom:16px}
.req-in:focus{border-color:rgba(241,211,98,0.5)}
.req-in::placeholder{color:var(--t4)}
textarea.req-in{resize:vertical;min-height:100px;line-height:1.6}
.req-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.req-btn{width:100%;padding:13px;border-radius:10px;background:var(--gold);color:var(--gold-text);font-size:15px;font-weight:700;border:none;cursor:pointer;font-family:var(--fb);transition:opacity .15s;margin-top:4px}
.req-btn:hover{opacity:.88}
.req-btn:disabled{opacity:.5;cursor:default}
.req-note{font-size:12px;color:var(--t4);text-align:center;margin-top:12px;line-height:1.5}
.success-box{background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.25);border-radius:14px;padding:28px;text-align:center}
.success-icon{font-size:32px;margin-bottom:12px}

/* ── Field tooltip ── */
.field-tip{position:relative;display:inline-flex;align-items:center;margin-left:6px;cursor:pointer;vertical-align:middle}
.field-tip-icon{width:15px;height:15px;border-radius:50%;background:rgba(255,255,255,0.1);border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:var(--t4);line-height:1;transition:border-color .15s,color .15s}
[data-theme="light"] .field-tip-icon{background:rgba(0,0,0,0.06);border-color:#cccccc;color:#888}
.field-tip:hover .field-tip-icon{border-color:var(--gold-text);color:var(--gold-text)}
.field-tip-bubble{position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%);background:#1a1a24;border:1px solid rgba(255,255,255,0.12);border-radius:10px;padding:10px 13px;width:230px;font-size:12px;color:rgba(255,255,255,0.8);line-height:1.55;font-weight:400;letter-spacing:0;text-transform:none;pointer-events:none;opacity:0;transition:opacity .15s;z-index:50;white-space:normal;box-shadow:0 8px 24px rgba(0,0,0,.5)}
.field-tip-bubble::after{content:'';position:absolute;top:100%;left:50%;transform:translateX(-50%);border:5px solid transparent;border-top-color:#1a1a24}
.field-tip:hover .field-tip-bubble{opacity:1}

/* ── Responsive ── */
@media(max-width:900px){
  .worker-grid{grid-template-columns:1fr}
  .req-grid{grid-template-columns:1fr}
}
</style>
@endsection

@section('body')

{{-- Hero + Search --}}
<div class="w">
  <div class="mkt-search-wrap">
    <div class="slabel">Marketplace</div>
    <h1 style="font-family:var(--fd);font-size:clamp(28px,4vw,46px);font-weight:800;letter-spacing:-1.5px;line-height:1.1;margin-bottom:14px">Purpose-built AI employees,<br>ready to hire.</h1>
    <p style="font-size:16px;color:var(--t3);max-width:520px;line-height:1.65;margin-bottom:28px">Each employee is trained for a specific workflow — not a general assistant. Browse by what you need, hire the one that fits.</p>

    <div class="mkt-search-box">
      <svg class="mkt-search-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="mkt-search" placeholder="Search workers, orgs, or capabilities…" autocomplete="off">
    </div>
    <div class="filter-row">
      <button class="filter-btn active" data-filter="all">All Workers</button>
      <button class="filter-btn" data-filter="renewal">Renewal</button>
      <button class="filter-btn" data-filter="leads">Lead Qualification</button>
      <button class="filter-btn" data-filter="newsletter">Newsletter</button>
      <button class="filter-btn" data-filter="filing">Filing</button>
      <button class="filter-btn" data-filter="compliance">Compliance</button>
    </div>
  </div>
</div>

{{-- Worker Card Grid --}}
<div class="w" style="padding-bottom:0">
  <div class="worker-grid" id="worker-list">

    {{-- ── AVA ── --}}
    <div class="wk-card" data-tags="renewal ava gmail nycsca dob fdny mta">
      <div class="wk-card-head">
        <div class="wk-card-identity">
          <div class="wk-av" style="background:linear-gradient(135deg,#0a1535,#142C74);color:#142C74">A</div>
          <div>
            <div class="wk-card-name">AVA</div>
            <div class="wk-card-role">Renewal Coordinator</div>
          </div>
          <span class="live-chip"><span class="live-dot"></span>Live</span>
        </div>
        <p class="wk-card-desc">I make sure you never miss an important renewal. I watch your inbox, understand each renewal request, use what I know about your customers and business, prepare the reply, and leave it in Gmail for your approval.</p>
        <div class="wk-card-caps">
          @foreach(['Monitor your Gmail 24/7','Detect renewal and subscription requests','Understand the customer using your memory','Draft a personalized response','Save it to Gmail Drafts for your review','Learn from every interaction'] as $c)
          <div class="wk-cap-item"><span class="wk-cap-dot" style="background:var(--gold-text)"></span>{{ $c }}</div>
          @endforeach
        </div>
      </div>
      <div class="wk-card-tags">
        <span class="wk-tag">NYCSCA</span><span class="wk-tag">DOB</span><span class="wk-tag">FDNY</span><span class="wk-tag">MTA</span><span class="wk-tag">Gmail</span><span class="wk-tag">Renewal</span>
      </div>
      <div class="wk-card-btns">
        <a href="/w/ava" class="btn-ln" style="flex:1;text-align:center">View Profile</a>
        <a href="{{ route('register') }}?worker=ava" class="btn-g" style="flex:1;text-align:center;justify-content:center">Hire Free →</a>
      </div>
    </div>

    {{-- ── LIQ ── --}}
    <div class="wk-card wk-card-soon" data-tags="leads liq crm sales qualification email">
      <div class="wk-card-head">
        <div class="wk-card-identity">
          <div class="wk-av" style="background:linear-gradient(135deg,#0a1a2e,#0369a1);color:#7dd3fc">L</div>
          <div>
            <div class="wk-card-name">LIQ</div>
            <div class="wk-card-role">Lead Qualification Specialist</div>
          </div>
          <span class="soon-chip">Coming Soon</span>
        </div>
        <p class="wk-card-desc">Scores and qualifies inbound leads, enriches CRM records, sequences follow-ups, and hands off a clean summary to your sales team.</p>
        <div class="wk-card-caps">
          @foreach(['Inbound lead scoring and intent analysis','CRM enrichment and deduplication','Follow-up sequencing and timing','Qualification summary for sales handoff','Disqualification routing to nurture'] as $c)
          <div class="wk-cap-item"><span class="wk-cap-dot" style="background:#7dd3fc"></span>{{ $c }}</div>
          @endforeach
        </div>
      </div>
      <div class="wk-card-tags">
        <span class="wk-tag">CRM</span><span class="wk-tag">Email</span><span class="wk-tag">Web Forms</span><span class="wk-tag">Sales</span>
      </div>
      <div class="wk-card-btns">
        <button class="btn-ln" disabled style="flex:1;opacity:.4;cursor:not-allowed">View Profile</button>
        <button class="btn-g" disabled style="flex:1;opacity:.4;cursor:not-allowed;justify-content:center">Coming Soon</button>
      </div>
    </div>

    {{-- ── NUX ── --}}
    <div class="wk-card" data-tags="nux linkedin x twitter social media publishing content">
      <div class="wk-card-head">
        <div class="wk-card-identity">
          <div class="wk-av" style="background:linear-gradient(135deg,#0a1a1a,#0d9488);color:#5eead4">N</div>
          <div>
            <div class="wk-card-name">NUX</div>
            <div class="wk-card-role" style="color:#5eead4">Multi-Channel Publishing Coordinator</div>
          </div>
        </div>
        <p class="wk-card-desc">Watches your LinkedIn and X posts, repurposes them as native content for platforms you don't post on, generates images, and delivers ready-to-publish drafts to your Gmail.</p>
        <div class="wk-card-caps">
          @foreach(['Watch LinkedIn and X for new posts','Detect content worth repurposing','Adapt copy for each target platform natively','Generate a custom image with AI','Deliver ready-to-publish drafts to Gmail'] as $c)
          <div class="wk-cap-item"><span class="wk-cap-dot" style="background:#5eead4"></span>{{ $c }}</div>
          @endforeach
        </div>
      </div>
      <div class="wk-card-tags">
        <span class="wk-tag">LinkedIn</span><span class="wk-tag">X</span><span class="wk-tag">Social Media</span><span class="wk-tag">Gmail</span>
      </div>
      <div class="wk-card-btns">
        <a href="/w/nux" class="btn-ln" style="flex:1;text-align:center">View Profile</a>
        <a href="{{ route('register') }}?worker=nux" class="btn-g" style="flex:1;text-align:center;justify-content:center">Hire Free →</a>
      </div>
    </div>

    {{-- ── NOVA ── --}}
    <div class="wk-card wk-card-soon" data-tags="filing nova dob nycsca permits">
      <div class="wk-card-head">
        <div class="wk-card-identity">
          <div class="wk-av" style="background:linear-gradient(135deg,#1a1020,#6d28d9);color:#c4b5fd">N</div>
          <div>
            <div class="wk-card-name">NOVA</div>
            <div class="wk-card-role">Filing Specialist</div>
          </div>
          <span class="soon-chip">Coming Soon</span>
        </div>
        <p class="wk-card-desc">Processes permit applications end to end — tracks every status, catches every field error, and never lets a filing sit in limbo.</p>
        <div class="wk-card-caps">
          @foreach(['Application intake and field validation','DOB and NYCSCA requirement checks','Status tracking and deadline alerts','Submission package assembly','Error flagging before submission'] as $c)
          <div class="wk-cap-item"><span class="wk-cap-dot" style="background:#c4b5fd"></span>{{ $c }}</div>
          @endforeach
        </div>
      </div>
      <div class="wk-card-tags">
        <span class="wk-tag">DOB</span><span class="wk-tag">NYCSCA</span><span class="wk-tag">Permits</span><span class="wk-tag">Filing</span>
      </div>
      <div class="wk-card-btns">
        <button class="btn-ln" disabled style="flex:1;opacity:.4;cursor:not-allowed">View Profile</button>
        <button class="btn-g" disabled style="flex:1;opacity:.4;cursor:not-allowed;justify-content:center">Coming Soon</button>
      </div>
    </div>

    {{-- ── REX ── --}}
    <div class="wk-card wk-card-soon" data-tags="compliance rex fdny osha risk">
      <div class="wk-card-head">
        <div class="wk-card-identity">
          <div class="wk-av" style="background:linear-gradient(135deg,#0e1a10,#16a34a);color:#86efac">R</div>
          <div>
            <div class="wk-card-name">REX</div>
            <div class="wk-card-role">Compliance Monitor</div>
          </div>
          <span class="soon-chip">Coming Soon</span>
        </div>
        <p class="wk-card-desc">Lives inside your compliance calendar — flags overdue items, surfaces risk before it becomes a fine, and monitors resolution in real time.</p>
        <div class="wk-card-caps">
          @foreach(['Compliance calendar scanning','FDNY and OSHA requirement checks','Risk flagging and escalation','Real-time resolution monitoring','Instant alerts for critical items'] as $c)
          <div class="wk-cap-item"><span class="wk-cap-dot" style="background:#86efac"></span>{{ $c }}</div>
          @endforeach
        </div>
      </div>
      <div class="wk-card-tags">
        <span class="wk-tag">FDNY</span><span class="wk-tag">OSHA</span><span class="wk-tag">Risk</span><span class="wk-tag">Compliance</span>
      </div>
      <div class="wk-card-btns">
        <button class="btn-ln" disabled style="flex:1;opacity:.4;cursor:not-allowed">View Profile</button>
        <button class="btn-g" disabled style="flex:1;opacity:.4;cursor:not-allowed;justify-content:center">Coming Soon</button>
      </div>
    </div>

  </div>

  <div id="no-results" style="display:none;text-align:center;padding:80px 0;color:var(--t4)">
    <div style="font-size:36px;margin-bottom:12px">🔍</div>
    <div style="font-size:16px;font-weight:600;margin-bottom:8px">No workers match that search</div>
    <div style="font-size:14px">Try a different keyword, or <a href="#request-worker" style="color:var(--gold-text);text-decoration:underline">request a worker for your workflow</a>.</div>
  </div>
</div>

{{-- ── REQUEST A WORKER ── --}}
<section class="req-sec" id="request-worker">
  <div class="w">
    <div class="req-grid">
      <div>
        <div class="slabel">Don't see what you need?</div>
        <h2 class="sh2" style="text-align:left;margin-bottom:16px">Request a worker<br>for your workflow.</h2>
        <p class="ssub" style="text-align:left;margin-bottom:28px">Tell us how you currently handle things — the process, the pain, the volume. We'll have our AI review your submission and send you a few follow-up questions. That reply kicks off the conversation about whether we can build it.</p>
        <div style="display:flex;flex-direction:column;gap:14px">
          @foreach([
            ['<path d="M9 12l2 2 4-4"/><rect x="3" y="3" width="18" height="18" rx="3"/>','You describe your current process'],
            ['<circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>','AI reviews it within minutes'],
            ['<path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><polyline points="22,6 12,13 2,6"/>','You get follow-up questions by email'],
            ['<path d="M13 10V3L4 14h7v7l9-11h-7z"/>','We scope and build if it fits'],
          ] as [$ico, $step])
          <div style="display:flex;align-items:center;gap:14px">
            <div style="width:36px;height:36px;border-radius:9px;background:rgba(241,211,98,0.08);border:1px solid rgba(241,211,98,0.18);display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="color:var(--gold-text)">{!! $ico !!}</svg>
            </div>
            <span style="font-size:14px;color:var(--t2)">{{ $step }}</span>
          </div>
          @endforeach
        </div>
      </div>

      <div class="req-form">
        @if(session('request_sent'))
          <div class="success-box" id="success-box">
            <div class="success-icon">✉️</div>
            <div style="font-family:var(--fd);font-size:18px;font-weight:800;margin-bottom:8px">Details received.</div>
            <p style="font-size:14px;color:var(--t3);line-height:1.65">A follow-up email is on its way — it'll have a few questions so we can learn more about your workflow. Just reply to that email to continue the conversation.</p>
          </div>
        @else
          <form method="POST" action="{{ route('marketplace.request') }}" id="req-form">
            @csrf
            @if($errors->any())
              <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:13.5px;color:#f87171">
                {{ $errors->first() }}
              </div>
            @endif

            <div class="req-row">
              <div>
                <label>Your name *</label>
                <input type="text" name="name" class="req-in" placeholder="Jane Smith" value="{{ old('name') }}" required>
              </div>
              <div>
                <label>Email *</label>
                <input type="email" name="email" class="req-in" placeholder="you@yourfirm.com" value="{{ old('email') }}" required>
              </div>
            </div>

            <div class="req-row">
              <div>
                <label>Company / Agency</label>
                <input type="text" name="company" class="req-in" placeholder="BuildCo LLC" value="{{ old('company') }}">
              </div>
              <div>
                <label>Your role</label>
                <input type="text" name="role" class="req-in" placeholder="Operations Lead" value="{{ old('role') }}">
              </div>
            </div>

            <label>
              Org / Agency you work with
              <span class="field-tip">
                <span class="field-tip-icon">!</span>
                <span class="field-tip-bubble">The external system, agency, or platform your workflow touches — e.g. NYCSCA, DOB, Salesforce, Gmail, Stripe. This helps us understand what the worker would need to connect to.</span>
              </span>
            </label>
            <input type="text" name="org" class="req-in" placeholder="NYCSCA, DOB, Salesforce, Gmail, other…" value="{{ old('org') }}">

            <label>
              How do you currently handle this process? *
              <span class="field-tip">
                <span class="field-tip-icon">!</span>
                <span class="field-tip-bubble">Walk us through what actually happens — step by step. What triggers the work? Who does what, in what order? What tools are involved? The more specific, the better — this is what we'll use to scope the worker.</span>
              </span>
            </label>
            <textarea name="current_process" class="req-in" placeholder="e.g. When a renewal notice hits our inbox, someone manually copies the details into a spreadsheet, looks up the client in our CRM, drafts a reply, and emails it for review. Half the time the wrong template gets used." required style="min-height:130px">{{ old('current_process') }}</textarea>

            <label>
              What goes wrong or slows you down?
              <span class="field-tip">
                <span class="field-tip-icon">!</span>
                <span class="field-tip-bubble">The failure modes — things that get missed, done late, done wrong, or just take too long. Even small recurring friction is worth mentioning.</span>
              </span>
            </label>
            <textarea name="pain_points" class="req-in" placeholder="e.g. Wrong contact info, missed deadlines, duplicated work across two people, approvals sitting in someone's inbox for days…" style="min-height:80px">{{ old('pain_points') }}</textarea>

            <label>
              Volume
              <span class="field-tip">
                <span class="field-tip-icon">!</span>
                <span class="field-tip-bubble">Rough estimate of how often this work happens — per day, week, or month. Doesn't need to be exact. This helps us understand scale.</span>
              </span>
            </label>
            <input type="text" name="volume" class="req-in" placeholder="e.g. 30–50 per month, ~10 per week" value="{{ old('volume') }}">

            <button type="submit" class="req-btn" id="req-submit">Send Request →</button>
            <p class="req-note">Your details are received. We'll send a follow-up email with a few questions to learn more — just reply to that email to continue.</p>
          </form>
        @endif
      </div>
    </div>
  </div>
</section>

@endsection

@section('scripts')
<script>
// ── Search / filter ──────────────────────────────────────────────────────────
const searchInput  = document.getElementById('mkt-search');
const filterBtns   = document.querySelectorAll('.filter-btn');
const workerCards  = document.querySelectorAll('#worker-list .wk-card');
const noResults    = document.getElementById('no-results');
let activeFilter   = 'all';

function applyFilters() {
  const q = searchInput.value.toLowerCase().trim();
  let visible = 0;
  workerCards.forEach(card => {
    const tags  = (card.dataset.tags || '').toLowerCase();
    const text  = card.innerText.toLowerCase();
    const matchQ      = !q || text.includes(q) || tags.includes(q);
    const matchFilter = activeFilter === 'all' || tags.includes(activeFilter);
    const show = matchQ && matchFilter;
    card.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  noResults.style.display = visible === 0 ? 'block' : 'none';
}

searchInput.addEventListener('input', applyFilters);

filterBtns.forEach(btn => {
  btn.addEventListener('click', function() {
    filterBtns.forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    activeFilter = this.dataset.filter;
    applyFilters();
  });
});

// ── Form submit spinner ──────────────────────────────────────────────────────
const reqForm = document.getElementById('req-form');
const reqBtn  = document.getElementById('req-submit');
if (reqForm && reqBtn) {
  reqForm.addEventListener('submit', function() {
    reqBtn.disabled = true;
    reqBtn.textContent = 'Sending…';
  });
}

// ── Auto-scroll to success box after redirect ────────────────────────────────
const successBox = document.getElementById('success-box');
if (successBox) {
  setTimeout(function() {
    successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }, 120);
}
</script>
@endsection

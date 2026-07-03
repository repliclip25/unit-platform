<x-app-layout title="Your Team">

@php
$totalInboxes = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->count();

$workerMeta = [
    'ava' => [
        'color'    => '#f1d362',
        'rgb'      => '241,211,98',
        'icon'     => '✉',
        'badge'    => 'Live',
        'category' => 'RENEWALS',
        'bullets'  => [
            'Reads and classifies every inbound renewal email',
            'Drafts tailored responses using your contacts and templates',
            'Flags urgent or at-risk accounts for immediate review',
            'Logs every action to your renewal register',
        ],
    ],
    'nux' => [
        'color'    => '#a78bfa',
        'rgb'      => '167,139,250',
        'icon'     => '⇄',
        'badge'    => 'Live',
        'category' => 'CONTENT',
        'bullets'  => [
            'Watches LinkedIn and X for new posts',
            'Repurposes content for each target platform natively',
            'Generates custom images with AI',
            'Delivers ready-to-publish drafts to your Gmail',
        ],
    ],
];

$defaultMeta = ['color'=>'var(--accent)','rgb'=>'241,211,98','icon'=>'⚙','badge'=>'Live','category'=>'AUTOMATION','bullets'=>[]];

$deployableWorkers = collect($catalog)->filter(function($worker) use ($contracts, $deploymentCounts, $totalInboxes) {
    $count    = $deploymentCounts->get($worker->slug, 0);
    $contract = $contracts->get($worker->slug);
    $inst     = $contract ? $contract->instances() : [];
    if ($count === 0) return true;
    if (isset($inst['max']) && $inst['max'] !== null && $count >= $inst['max']) return false;
    if (($inst['limit_by'] ?? null) === 'gmail_credentials') return $count < $totalInboxes;
    return $inst['multiple'] ?? false;
})->keyBy('slug');

$visibleCatalog = collect($catalog)->filter(fn($w) =>
    !\App\Platform\Services\WorkerRegistry::isDecommissioned($w->slug) &&
    !\App\Platform\Services\WorkerRegistry::isRemoved($w->slug) &&
    !\App\Platform\Services\WorkerRegistry::isRemoving($w->slug)
);

$depBySlug = $deployments->groupBy('worker_slug');
@endphp

{{-- ── Page header ──────────────────────────────────────────────────────────── --}}
<div style="margin-bottom:32px;display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
        <p style="font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:6px">Your Team at Work</p>
        <h1 style="font-size:26px;font-weight:900;color:var(--text-primary);line-height:1.1">Meet your AI employees.</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:5px">Each worker runs independently on the UNIT platform, 24/7.</p>
    </div>
    <span style="font-size:11px;color:var(--text-muted);padding:5px 12px;border:1px solid var(--border);border-radius:20px">{{ $visibleCatalog->count() }} available</span>
</div>

{{-- ── Worker Cards ─────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;margin-bottom:40px">

@foreach($visibleCatalog as $worker)
@php
    $m          = $workerMeta[$worker->slug] ?? $defaultMeta;
    $reg        = $registryRows[$worker->slug] ?? null;
    $profileImg = $reg?->profile_image ? asset('storage/' . $reg->profile_image) : null;
    $coverImg   = $reg?->cover_image   ? asset('storage/' . $reg->cover_image)   : null;
    $mediaData  = json_decode($reg->media ?? '{}', true);
    $color      = $mediaData['color'] ?? $m['color'];
    $mediaQuote = $mediaData['quote'] ?? '';

    $rawGallery   = json_decode($reg->gallery ?? '[]', true) ?? [];
    $galleryItems = array_values(array_filter($rawGallery, fn($g) => !in_array($g['type']??'', ['profile','cover'])));

    $catalogContract = $contracts->get($worker->slug);
    $workerEmployee  = $catalogContract ? $catalogContract->employee() : [];
    $role            = $workerEmployee['title'] ?? $worker->category ?? '';

    $slugDeps = $depBySlug->get($worker->slug, collect());
    $firstDep = $slugDeps->first();
    $depCount = $slugDeps->count();
    $isActive = $firstDep?->status === 'active';
    $isPaused = $firstDep?->status === 'paused';
    $hasDeployment = $firstDep !== null;
    $isLive    = $m['badge'] === 'Live';
    $isTesting = \App\Platform\Services\WorkerRegistry::isTesting($worker->slug);
    $canDeploy = $deployableWorkers->has($worker->slug);

    // Live status message for hired workers
    $statusQuote = null;
    $statusCta   = null;
    if ($hasDeployment && $firstDep) {
        $pendingDrafts = DB::table('transactions')
            ->where('deployment_id', $firstDep->id)
            ->where('status', 'draft_ready')
            ->count();
        $recentCount = DB::table('transactions')
            ->where('deployment_id', $firstDep->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($pendingDrafts > 0) {
            $statusQuote = "I've prepared " . $pendingDrafts . " " . ($pendingDrafts === 1 ? 'draft' : 'drafts') . ". " . ($pendingDrafts === 1 ? 'It\'s' : 'One is') . " waiting for your approval.";
            $statusCta   = ['label' => 'Review now →', 'url' => route('transactions.index')];
        } elseif ($recentCount > 0) {
            $statusQuote = "Processed {$recentCount} " . ($recentCount === 1 ? 'email' : 'emails') . " this week. Everything's up to date.";
            $statusCta   = ['label' => 'View activity →', 'url' => route('transactions.index')];
        } else {
            $statusQuote = "Watching your inbox. Ready to act the moment something comes in.";
            $statusCta   = ['label' => 'Open workspace →', 'url' => route('workers.show', $worker->slug)];
        }
    }
@endphp

<div id="catalog-{{ $worker->slug }}"
     style="background:var(--bg-card);border:1px solid {{ $hasDeployment ? 'var(--border)' : 'var(--border-subtle)' }};border-radius:20px;overflow:hidden;display:flex;flex-direction:column;{{ (!$isLive && !$isTesting) ? 'opacity:.45' : '' }}">

    {{-- ── Portrait area ──────────────────────────────────────────────────── --}}
    <div style="position:relative;height:300px;overflow:hidden;background:#0d0d0d">
        @if($coverImg)
        <img src="{{ $coverImg }}" alt="{{ $worker->name }}"
             style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block">
        @elseif($profileImg)
        <img src="{{ $profileImg }}" alt="{{ $worker->name }}"
             style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block">
        @else
        {{-- Fallback gradient --}}
        <div style="width:100%;height:100%;background:linear-gradient(160deg,rgba({{ $m['rgb'] }},.12) 0%,#0d0d0d 100%);display:flex;align-items:center;justify-content:center">
            <span style="font-size:72px;opacity:.25">{{ $m['icon'] }}</span>
        </div>
        @endif

        {{-- Gradient fade to card bg --}}
        <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,0) 40%,rgba(13,13,13,.85) 100%)"></div>

        {{-- Top row: icon + name + role + status --}}
        <div style="position:absolute;top:0;left:0;right:0;padding:16px 18px;display:flex;align-items:flex-start;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(0,0,0,.55);border:1px solid rgba(255,255,255,.12);backdrop-filter:blur(6px);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    @if($profileImg && $coverImg)
                    <img src="{{ $profileImg }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:10px">
                    @else
                    <span style="font-size:16px">{{ $m['icon'] }}</span>
                    @endif
                </div>
                <div>
                    <p style="font-size:15px;font-weight:900;color:#fff;line-height:1.1;text-shadow:0 1px 4px rgba(0,0,0,.6)">{{ $worker->name }}</p>
                    <p style="font-size:10px;color:rgba(255,255,255,.6);margin-top:1px">{{ $role }}</p>
                </div>
            </div>

            {{-- Status badge --}}
            @if($hasDeployment && $isActive)
            <div style="display:flex;align-items:center;gap:5px;background:rgba(0,0,0,.55);backdrop-filter:blur(6px);border:1px solid rgba(74,222,128,.3);border-radius:20px;padding:4px 10px">
                <span style="width:6px;height:6px;border-radius:50%;background:#4ade80;display:block;animation:pulse-dot 2s infinite"></span>
                <span style="font-size:10px;font-weight:700;color:#4ade80">On duty</span>
            </div>
            @elseif($hasDeployment && $isPaused)
            <div style="display:flex;align-items:center;gap:5px;background:rgba(0,0,0,.55);backdrop-filter:blur(6px);border:1px solid rgba(251,191,36,.3);border-radius:20px;padding:4px 10px">
                <span style="font-size:10px">⏸</span>
                <span style="font-size:10px;font-weight:700;color:#fbbf24">Paused</span>
            </div>
            @elseif($isTesting)
            <div style="background:rgba(0,0,0,.55);backdrop-filter:blur(6px);border:1px solid rgba(251,191,36,.3);border-radius:20px;padding:4px 10px">
                <span style="font-size:10px;font-weight:700;color:#fbbf24">⚗ Testing</span>
            </div>
            @elseif($isLive)
            <div style="background:rgba(0,0,0,.55);backdrop-filter:blur(6px);border:1px solid rgba(74,222,128,.25);border-radius:20px;padding:4px 10px">
                <span style="font-size:10px;font-weight:700;color:#4ade80">● Live</span>
            </div>
            @else
            <div style="background:rgba(0,0,0,.55);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:4px 10px">
                <span style="font-size:10px;font-weight:700;color:rgba(255,255,255,.4)">Coming Soon</span>
            </div>
            @endif
        </div>

        {{-- Category label — bottom of portrait --}}
        <div style="position:absolute;bottom:16px;left:18px">
            <span style="font-size:9px;font-weight:900;letter-spacing:.14em;padding:4px 12px;border-radius:4px;background:{{ $color }};color:#12100a;text-transform:uppercase">{{ $m['category'] }}</span>
        </div>
    </div>

    {{-- ── Status / Quote area ─────────────────────────────────────────────── --}}
    <div style="padding:18px 20px;flex:1;display:flex;flex-direction:column">

        @if($hasDeployment && $statusQuote)
        {{-- Live status for hired worker --}}
        <div style="display:flex;gap:10px;flex:1">
            <span style="font-size:22px;color:{{ $color }};opacity:.7;line-height:1;flex-shrink:0;margin-top:-2px">"</span>
            <div>
                <p style="font-size:13px;color:var(--text-secondary);line-height:1.6;margin-bottom:12px">{{ $statusQuote }}</p>
                @if($statusCta)
                <a href="{{ $statusCta['url'] }}"
                   style="font-size:12px;font-weight:700;color:{{ $color }};text-decoration:none;transition:opacity .15s"
                   onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'">
                    {{ $statusCta['label'] }}
                </a>
                @endif
            </div>
        </div>

        @elseif($hasDeployment)
        {{-- Hired but no activity yet --}}
        <div style="display:flex;gap:10px;flex:1">
            <span style="font-size:22px;color:{{ $color }};opacity:.7;line-height:1;flex-shrink:0;margin-top:-2px">"</span>
            <div>
                <p style="font-size:13px;color:var(--text-secondary);line-height:1.6;margin-bottom:12px">Ready and watching. Connect an inbox to get started.</p>
                <a href="{{ route('workers.show', $worker->slug) }}"
                   style="font-size:12px;font-weight:700;color:{{ $color }};text-decoration:none">
                    Set up →
                </a>
            </div>
        </div>

        @elseif($worker->description)
        {{-- Not hired — show tagline --}}
        <p style="font-size:13px;color:var(--text-muted);line-height:1.6;margin-bottom:16px;flex:1">{{ $worker->description }}</p>

        @else
        <div style="flex:1"></div>
        @endif

        {{-- ── Gallery strip (if media exists) ───────────────────────────── --}}
        @if(!empty($galleryItems))
        <div style="margin-bottom:14px;margin-top:4px">
            <div style="display:flex;gap:6px;overflow-x:auto;padding-bottom:2px;scrollbar-width:none">
            @foreach(array_slice($galleryItems, 0, 3) as $gi => $gitem)
            @php
                $gIsYt  = str_starts_with($gitem['type'] ?? '', 'youtube');
                $gIsVid = !$gIsYt && str_ends_with(strtolower($gitem['path'] ?? ''), 'mp4');
                $gUrl   = $gitem['kind'] === 'url' ? null : asset('storage/' . ($gitem['path'] ?? ''));
                $ytId   = null;
                if ($gIsYt && !empty($gitem['url'])) {
                    preg_match('/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $gitem['url'], $ytm);
                    $ytId = $ytm[1] ?? null;
                }
            @endphp
            <div style="flex-shrink:0;border-radius:8px;overflow:hidden;width:80px;height:54px;cursor:pointer;border:1px solid var(--border)"
                 onclick="openGallery('{{ $worker->slug }}', {{ $gi }})">
                @if($ytId)
                <img src="https://img.youtube.com/vi/{{ $ytId }}/mqdefault.jpg" alt="" style="width:100%;height:100%;object-fit:cover;display:block">
                @elseif($gUrl)
                <img src="{{ $gUrl }}" alt="" style="width:100%;height:100%;object-fit:cover;display:block">
                @endif
            </div>
            @endforeach
            </div>
        </div>
        <script>window['gallery_{{ $worker->slug }}'] = @json($galleryItems);</script>
        @endif

        {{-- ── Deploy / Action area ───────────────────────────────────────── --}}
        <div style="margin-top:auto">

        @if(!$isLive && !$isTesting)
        <button disabled style="width:100%;padding:11px;border-radius:10px;border:1px solid var(--border);color:var(--text-muted);background:transparent;font-size:13px;font-weight:600;cursor:not-allowed">
            Coming Soon
        </button>

        @elseif($isTesting && !$hasDeployment)
        <div style="text-align:center;padding:10px 0">
            <p style="font-size:12px;color:#fbbf24;font-weight:600">⚗ In testing — invite only</p>
        </div>

        @elseif($hasDeployment)
        <a href="{{ route('workers.show', $worker->slug) }}"
           style="display:block;text-align:center;width:100%;box-sizing:border-box;padding:12px;border-radius:12px;background:rgba({{ $m['rgb'] }},.12);border:1px solid rgba({{ $m['rgb'] }},.25);color:{{ $color }};font-size:13px;font-weight:700;text-decoration:none;transition:background .15s"
           onmouseover="this.style.background='rgba({{ $m['rgb'] }},.2)'" onmouseout="this.style.background='rgba({{ $m['rgb'] }},.12)'">
            Open workspace →
        </a>

        @elseif($canDeploy)
        <button onclick="toggleDeploy('{{ $worker->slug }}')"
                id="deploy-btn-{{ $worker->slug }}"
                data-color="{{ $color }}"
                data-rgb="{{ $m['rgb'] }}"
                style="width:100%;padding:12px;border-radius:12px;border:none;background:{{ $color }};color:#12100a;font-size:13px;font-weight:800;cursor:pointer;transition:opacity .15s"
                onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
            Hire {{ $worker->name }} →
        </button>

        <div id="deploy-form-{{ $worker->slug }}" style="display:none;margin-top:14px">
            <form method="POST" action="{{ route('workers.store') }}">
                @csrf
                <input type="hidden" name="worker_slug" value="{{ $worker->slug }}">
                <div style="margin-bottom:10px">
                    <label style="font-size:11px;color:var(--text-muted);display:block;margin-bottom:4px">Deployment name</label>
                    <input type="text" name="name" value="{{ $worker->name }}" required
                        style="width:100%;box-sizing:border-box;background:var(--bg-raised);color:var(--text-primary);font-size:13px;border:1px solid var(--border);border-radius:8px;padding:8px 10px;outline:none">
                </div>
                @if($credentials->isNotEmpty())
                <div style="margin-bottom:12px">
                    <label style="font-size:11px;color:var(--text-muted);display:block;margin-bottom:4px">Gmail inbox</label>
                    <select name="credential_id"
                        style="width:100%;box-sizing:border-box;background:var(--bg-raised);color:var(--text-primary);font-size:13px;border:1px solid var(--border);border-radius:8px;padding:8px 10px;outline:none">
                        <option value="">— connect after deploy —</option>
                        @foreach($credentials as $cred)
                        <option value="{{ $cred->id }}">{{ $cred->gmail_address }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                    <button type="button" onclick="toggleDeploy('{{ $worker->slug }}')"
                        style="padding:10px;border-radius:8px;border:1px solid var(--border);color:var(--text-muted);background:transparent;font-size:12px;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit"
                        style="padding:10px;border-radius:8px;border:none;background:{{ $color }};color:#12100a;font-size:12px;font-weight:800;cursor:pointer">
                        Confirm Hire
                    </button>
                </div>
            </form>
        </div>

        @else
        {{-- Max instances --}}
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div>
                <p style="font-size:12px;font-weight:600;color:var(--text-secondary)">{{ $depCount }} instance{{ $depCount !== 1 ? 's' : '' }} running</p>
                <p style="font-size:11px;color:var(--text-muted);margin-top:2px">Connect another inbox to add more</p>
            </div>
            @php $connectRoute = $worker->slug === 'nux' ? route('nux.connect.linkedin') : route('ava.gmail.authorize'); @endphp
            <a href="{{ $connectRoute }}"
               style="font-size:11px;font-weight:700;padding:8px 14px;border-radius:8px;background:{{ $color }};color:#12100a;text-decoration:none;white-space:nowrap;flex-shrink:0">
                + Connect
            </a>
        </div>
        @endif

        </div>
    </div>
</div>
@endforeach

</div>

{{-- ── Free trial note ──────────────────────────────────────────────────────── --}}
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:14px 20px;display:flex;align-items:center;gap:12px;max-width:520px">
    <span style="font-size:20px">🎁</span>
    <p style="font-size:12px;color:var(--text-muted);line-height:1.6">
        <strong style="color:var(--text-secondary)">25 free transactions</strong> on every worker. No credit card required until you scale.
    </p>
</div>

{{-- ── Gallery lightbox ─────────────────────────────────────────────────────── --}}
<div id="gallery-lightbox" onclick="if(event.target===this)closeGallery()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;align-items:center;justify-content:center;flex-direction:column">
    <button onclick="closeGallery()" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#fff;font-size:28px;cursor:pointer;opacity:.7">×</button>
    <button onclick="galleryPrev()" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:22px;width:44px;height:44px;border-radius:50%;cursor:pointer">‹</button>
    <button onclick="galleryNext()" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:22px;width:44px;height:44px;border-radius:50%;cursor:pointer">›</button>
    <div id="gallery-lb-media" style="max-width:90vw;max-height:80vh;display:flex;align-items:center;justify-content:center"></div>
    <p id="gallery-lb-caption" style="color:rgba(255,255,255,.65);font-size:13px;margin-top:14px;text-align:center"></p>
    <div id="gallery-lb-dots" style="display:flex;gap:6px;margin-top:12px"></div>
</div>

<style>
@keyframes pulse-dot {
    0%,100% { opacity:1; }
    50%      { opacity:.4; }
}
</style>

<script>
let _lbSlug = null, _lbIdx = 0;

function openGallery(slug, idx) {
    _lbSlug = slug; _lbIdx = idx;
    document.getElementById('gallery-lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    renderLb();
}
function closeGallery() {
    document.getElementById('gallery-lightbox').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('gallery-lb-media').innerHTML = '';
}
function galleryPrev() { const items = window['gallery_' + _lbSlug] || []; _lbIdx = (_lbIdx - 1 + items.length) % items.length; renderLb(); }
function galleryNext() { const items = window['gallery_' + _lbSlug] || []; _lbIdx = (_lbIdx + 1) % items.length; renderLb(); }
function renderLb() {
    const items = window['gallery_' + _lbSlug] || [];
    if (!items.length) return;
    const item = items[_lbIdx];
    const mediaEl = document.getElementById('gallery-lb-media');
    mediaEl.innerHTML = '';
    const ytMatch = (item.url||'').match(/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (ytMatch) {
        const iframe = document.createElement('iframe');
        iframe.src = 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=1';
        iframe.allow = 'autoplay; encrypted-media'; iframe.allowFullscreen = true;
        iframe.style.cssText = 'width:min(90vw,854px);height:min(80vh,480px);border:none;border-radius:12px';
        mediaEl.appendChild(iframe);
    } else if (item.kind === 'file' && (item.path||'').match(/\.(mp4|mov|webm)$/i)) {
        const v = document.createElement('video');
        v.src = '/storage/' + item.path; v.controls = true; v.autoplay = true;
        v.style.cssText = 'max-width:90vw;max-height:78vh;border-radius:12px';
        mediaEl.appendChild(v);
    } else {
        const img = document.createElement('img');
        img.src = '/storage/' + item.path;
        img.style.cssText = 'max-width:90vw;max-height:78vh;border-radius:12px;object-fit:contain';
        mediaEl.appendChild(img);
    }
    document.getElementById('gallery-lb-caption').textContent = item.caption || '';
    const dots = document.getElementById('gallery-lb-dots');
    dots.innerHTML = '';
    items.forEach((_, i) => {
        const d = document.createElement('div');
        d.style.cssText = `width:7px;height:7px;border-radius:50%;background:${i===_lbIdx?'#fff':'rgba(255,255,255,.3)'};cursor:pointer`;
        d.onclick = () => { _lbIdx = i; renderLb(); };
        dots.appendChild(d);
    });
}
document.addEventListener('keydown', e => {
    if (document.getElementById('gallery-lightbox').style.display !== 'none') {
        if (e.key === 'ArrowLeft') galleryPrev();
        if (e.key === 'ArrowRight') galleryNext();
        if (e.key === 'Escape') closeGallery();
    }
});

function toggleDeploy(slug) {
    const form = document.getElementById('deploy-form-' + slug);
    const btn  = document.getElementById('deploy-btn-' + slug);
    if (!form || !btn) return;
    const open = form.style.display === 'none';
    form.style.display = open ? 'block' : 'none';
    const color = btn.getAttribute('data-color') || 'var(--accent)';
    const rgb   = btn.getAttribute('data-rgb')   || '241,211,98';
    if (open) {
        btn.textContent = '✕ Cancel';
        btn.style.background = 'rgba(' + rgb + ',.12)';
        btn.style.color = color;
        btn.style.border = '1px solid rgba(' + rgb + ',.25)';
    } else {
        btn.textContent = 'Hire ' + slug.charAt(0).toUpperCase() + slug.slice(1) + ' →';
        btn.style.background = color;
        btn.style.color = '#12100a';
        btn.style.border = 'none';
    }
}
</script>

</x-app-layout>

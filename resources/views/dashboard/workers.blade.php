<x-app-layout title="Employee Roster">


@php
$totalInboxes = DB::table('user_gmail_credentials')->where('user_id', auth()->id())->count();

// Worker visual identity — color/icon/bullets per slug
// New workers added via builder inherit defaults until added here
$workerVisuals = [
    'ava' => [
        'color'  => '#f1d362',
        'rgb'    => '241,211,98',
        'icon'   => '✉',
        'badge'  => 'Live',
        'bullets'=> [
            'Reads and classifies every inbound renewal email',
            'Drafts tailored responses using your contacts and templates',
            'Flags urgent or at-risk accounts for immediate review',
            'Logs every action to your renewal register',
        ],
    ],
    'nux' => [
        'color'  => '#5eead4',
        'rgb'    => '94,234,212',
        'icon'   => '⇄',
        'badge'  => 'Live',
        'bullets'=> [
            'Watches LinkedIn and X for new posts',
            'Repurposes content for each target platform natively',
            'Generates custom images with AI',
            'Delivers ready-to-publish drafts to your Gmail',
        ],
    ],
];

$defaultVisual = ['color'=>'var(--accent)','rgb'=>'241,211,98','icon'=>'⚙','badge'=>'Live','bullets'=>[]];

$deployableWorkers = collect($catalog)->filter(function($worker) use ($contracts, $deploymentCounts, $totalInboxes) {
    $count    = $deploymentCounts->get($worker->slug, 0);
    $contract = $contracts->get($worker->slug);
    $inst     = $contract ? $contract->instances() : [];
    if ($count === 0) return true;
    if (isset($inst['max']) && $inst['max'] !== null && $count >= $inst['max']) return false;
    if (($inst['limit_by'] ?? null) === 'gmail_credentials') return $count < $totalInboxes;
    return $inst['multiple'] ?? false;
})->keyBy('slug');

// Only show non-decommissioned workers in catalog
$visibleCatalog = collect($catalog)->filter(fn($w) =>
    !\App\Platform\Services\WorkerRegistry::isDecommissioned($w->slug) &&
    !\App\Platform\Services\WorkerRegistry::isRemoved($w->slug) &&
    !\App\Platform\Services\WorkerRegistry::isRemoving($w->slug)
);
@endphp

{{-- ── Your Workers strip (all workers, deployment status inline) ─────────── --}}
@php
$depBySlug = $deployments->groupBy('worker_slug');
@endphp
<div style="margin-bottom:36px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
        <h2 style="font-size:11px;font-weight:700;color:var(--text-muted);letter-spacing:.08em;text-transform:uppercase">Your Team</h2>
        @if($deployments->count())
        <span style="font-size:11px;color:var(--text-muted)">{{ $deployments->count() }} hired</span>
        @endif
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px">
    @foreach($visibleCatalog as $worker)
    @php
        $v        = $workerVisuals[$worker->slug] ?? $defaultVisual;
        $reg      = $registryRows[$worker->slug] ?? null;
        $thumbImg = $reg?->profile_image ? asset('storage/' . $reg->profile_image) : null;
        $coverThumb = $reg?->cover_image ? asset('storage/' . $reg->cover_image) : null;
        $accentColor = $reg ? (json_decode($reg->media ?? '{}', true)['color'] ?? $v['color']) : $v['color'];
        $slugDeps = $depBySlug->get($worker->slug, collect());
        $firstDep = $slugDeps->first();
        $depCount = $slugDeps->count();
        $isActive = $firstDep?->status === 'active';
        $isPaused = $firstDep?->status === 'paused';
        $hasDeployment = $firstDep !== null;
    @endphp
    <div style="background:var(--bg-card);border:1px solid {{ $hasDeployment ? 'var(--border)' : 'var(--border-subtle)' }};border-radius:14px;overflow:hidden;{{ $hasDeployment ? '' : 'opacity:.65' }}">
        {{-- Mini cover strip --}}
        @if($coverThumb && $hasDeployment)
        <div style="height:44px;overflow:hidden;position:relative">
            <img src="{{ $coverThumb }}" alt="" style="width:100%;height:100%;object-fit:cover;object-position:center 30%;display:block">
            <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,.1),rgba(0,0,0,.5))"></div>
        </div>
        @elseif($hasDeployment)
        <div style="height:4px;background:{{ $accentColor }}"></div>
        @else
        <div style="height:3px;background:var(--border)"></div>
        @endif

        <div style="padding:12px 14px;display:flex;align-items:center;gap:11px">
            {{-- Avatar --}}
            <div style="width:38px;height:38px;border-radius:10px;flex-shrink:0;overflow:hidden;border:2px solid {{ $hasDeployment ? "rgba(255,255,255,.1)" : 'var(--border)' }};
                        {{ $thumbImg ? '' : 'display:flex;align-items:center;justify-content:center;font-size:16px;background:rgba(241,211,98,.08)' }}">
                @if($thumbImg)
                <img src="{{ $thumbImg }}" alt="{{ $worker->name }}" style="width:100%;height:100%;object-fit:cover;display:block">
                @else
                {{ $v['icon'] }}
                @endif
            </div>

            {{-- Name + status --}}
            <div style="flex:1;min-width:0">
                <p style="font-size:13px;font-weight:700;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $worker->name }}</p>
                <p style="font-size:10px;color:var(--text-muted);margin-top:1px">
                    @if($isActive)
                    <span style="color:#4ade80;font-weight:600">● Active</span> · {{ $depCount }} instance{{ $depCount > 1 ? 's' : '' }}
                    @elseif($isPaused)
                    <span style="color:#fbbf24;font-weight:600">⏸ Paused</span>
                    @else
                    Not hired
                    @endif
                </p>
            </div>

            {{-- Action --}}
            @if($hasDeployment)
            <a href="{{ route('workers.show', $worker->slug) }}"
               style="font-size:11px;font-weight:700;padding:5px 11px;border-radius:8px;background:var(--accent);color:#12100a;text-decoration:none;flex-shrink:0;white-space:nowrap">
                Open →
            </a>
            @else
            <a href="#catalog-{{ $worker->slug }}"
               style="font-size:11px;font-weight:600;padding:5px 11px;border-radius:8px;border:1px solid var(--border);color:var(--text-muted);text-decoration:none;flex-shrink:0;white-space:nowrap">
                Hire
            </a>
            @endif
        </div>
    </div>
    @endforeach
    </div>
</div>

{{-- ── Worker Catalog ────────────────────────────────────────────────────── --}}
<div style="margin-bottom:16px;display:flex;align-items:baseline;justify-content:space-between">
    <div>
        <h2 style="font-size:18px;font-weight:800;color:var(--text-primary)">Employee Roster</h2>
        <p style="font-size:12px;color:var(--text-muted);margin-top:3px">Hire an AI employee for your team. Each employee runs independently on the UNIT platform.</p>
    </div>
    <span style="font-size:11px;color:var(--text-muted)">{{ $visibleCatalog->count() }} available</span>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(360px,1fr));gap:16px;margin-bottom:32px">
@foreach($visibleCatalog as $worker)
@php
    $v         = $workerVisuals[$worker->slug] ?? $defaultVisual;
    $reg       = $registryRows[$worker->slug] ?? null;
    $profileImg = $reg?->profile_image ? asset('storage/' . $reg->profile_image) : null;
    $coverImg   = $reg?->cover_image   ? asset('storage/' . $reg->cover_image)   : null;
    $mediaColor = $reg ? (json_decode($reg->media ?? '{}', true)['color'] ?? $v['color']) : $v['color'];
    $mediaQuote = $reg ? (json_decode($reg->media ?? '{}', true)['quote'] ?? '') : '';
    $rawGallery   = json_decode($reg->gallery ?? '[]', true) ?? [];
    $galleryItems = array_values(array_filter($rawGallery, fn($g) => !in_array($g['type']??'', ['profile','cover'])));
    $catalogContract = $contracts->get($worker->slug);
    $workerEmployee = $catalogContract ? $catalogContract->employee() : [];
    $p         = array_merge($v, [
        'color'   => $mediaColor,
        'role'    => $workerEmployee['title'] ?? $worker->category ?? '',
        'tagline' => $worker->description ?? '',
        'org'     => $worker->org ?? '',
    ]);
    $canDeploy = $deployableWorkers->has($worker->slug);
    $isLive    = $p['badge'] === 'Live';
    $isTesting = \App\Platform\Services\WorkerRegistry::isTesting($worker->slug);
    $count     = $deploymentCounts->get($worker->slug, 0);
@endphp

<div id="catalog-{{ $worker->slug }}" style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden;display:flex;flex-direction:column;{{ $isLive ? '' : 'opacity:0.55' }}">

    {{-- Cover image / hero --}}
    @if($coverImg)
    <div style="position:relative;height:160px;overflow:hidden">
        <img src="{{ $coverImg }}" alt="{{ $worker->name }} cover"
             style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block">
        <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,0) 40%,rgba(0,0,0,.75) 100%)"></div>
        {{-- Status badge overlaid on cover --}}
        <div style="position:absolute;top:10px;right:12px">
            @if($isTesting)
            <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;background:rgba(0,0,0,.6);color:#fbbf24;border:1px solid rgba(251,191,36,.4);backdrop-filter:blur(4px)">⚗ Testing</span>
            @elseif($isLive)
            <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;background:rgba(0,0,0,.6);color:#4ade80;border:1px solid rgba(74,222,128,.4);backdrop-filter:blur(4px)">● Live</span>
            @else
            <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;background:rgba(0,0,0,.5);color:#94a3b8;border:1px solid rgba(148,163,184,.3)">Coming Soon</span>
            @endif
        </div>
        {{-- Profile image pinned to bottom-left of cover --}}
        @if($profileImg)
        <div style="position:absolute;bottom:-24px;left:20px">
            <img src="{{ $profileImg }}" alt="{{ $worker->name }}"
                 style="width:52px;height:52px;border-radius:14px;object-fit:cover;border:3px solid var(--bg-card);box-shadow:0 4px 16px rgba(0,0,0,.4)">
        </div>
        @endif
    </div>
    @else
    {{-- Fallback: colored accent bar + icon --}}
    <div style="height:4px;background:{{ $p['color'] }}"></div>
    @endif

    <div style="padding:{{ $coverImg && $profileImg ? '32px' : '20px' }} 22px 0">

        {{-- Header (no cover) or post-cover name row --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px">
            <div style="display:flex;align-items:center;gap:10px">
                @if(!$coverImg)
                <div style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;background:rgba(241,211,98,.1);border:1px solid rgba(241,211,98,.2)">
                    @if($profileImg)
                    <img src="{{ $profileImg }}" alt="" style="width:44px;height:44px;border-radius:11px;object-fit:cover">
                    @else
                    {{ $p['icon'] }}
                    @endif
                </div>
                @endif
                <div>
                    <p style="font-size:17px;font-weight:800;color:var(--text-primary);line-height:1.2">{{ $worker->name }}</p>
                    <p style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ $p['role'] }}</p>
                </div>
            </div>
            @if(!$coverImg)
            <div>
                @if($isTesting)
                <span style="font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px;background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.2)">⚗ Testing</span>
                @elseif($isLive)
                <span style="font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px;background:rgba(74,222,128,.1);color:#4ade80;border:1px solid rgba(74,222,128,.2)">● Live</span>
                @else
                <span style="font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px;background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">Soon</span>
                @endif
            </div>
            @endif
        </div>

        {{-- Worker quote if set --}}
        @if($mediaQuote)
        <p style="font-size:12px;color:{{ $p['color'] }};font-style:italic;margin-bottom:8px;opacity:.85">"{{ $mediaQuote }}"</p>
        @endif

        {{-- Tagline --}}
        <p style="font-size:13px;color:var(--text-secondary);line-height:1.6;margin-bottom:14px">{{ $p['tagline'] }}</p>

        {{-- Feature bullets --}}
        @if(!empty($p['bullets']))
        <ul style="margin-bottom:18px;list-style:none;padding:0;display:flex;flex-direction:column;gap:6px">
            @foreach($p['bullets'] as $b)
            <li style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text-muted)">
                <span style="width:5px;height:5px;border-radius:50%;flex-shrink:0;background:{{ $p['color'] }}"></span>
                {{ $b }}
            </li>
            @endforeach
        </ul>
        @endif
    </div>

    {{-- Gallery strip --}}
    @if(!empty($galleryItems))
    <div style="padding:0 22px 18px">
        <p style="font-size:10px;font-weight:700;letter-spacing:.07em;color:var(--text-muted);text-transform:uppercase;margin-bottom:10px">See it in action</p>
        <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:4px;scrollbar-width:none">
        @foreach($galleryItems as $gi => $gitem)
        @php
            $gKind = $gitem['kind'] ?? 'file';
            $gIsYt = str_starts_with($gitem['type'] ?? '', 'youtube');
            $gIsVid = $gKind === 'url' ? false : str_starts_with(\Illuminate\Support\Str::lower(pathinfo($gitem['path'] ?? '', PATHINFO_EXTENSION)), 'mp4');
            $gUrl = $gKind === 'url' ? null : asset('storage/' . ($gitem['path'] ?? ''));
            $ytId = null;
            if ($gIsYt && !empty($gitem['url'])) {
                preg_match('/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $gitem['url'], $ytm);
                $ytId = $ytm[1] ?? null;
            }
        @endphp
        <div style="flex-shrink:0;border-radius:10px;overflow:hidden;position:relative;width:200px;height:130px;cursor:pointer;border:1px solid var(--border)" onclick="openGallery('{{ $worker->slug }}', {{ $gi }})">
            @if($ytId)
            <img src="https://img.youtube.com/vi/{{ $ytId }}/mqdefault.jpg" alt="{{ $gitem['caption'] ?? '' }}" style="width:100%;height:100%;object-fit:cover;display:block">
            <div style="position:absolute;inset:0;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center">
                <div style="width:44px;height:44px;background:rgba(255,0,0,.85);border-radius:50%;display:flex;align-items:center;justify-content:center">
                    <span style="font-size:18px;color:#fff;margin-left:3px">▶</span>
                </div>
            </div>
            @elseif($gIsVid)
            <video src="{{ $gUrl }}" style="width:100%;height:100%;object-fit:cover;display:block" muted preload="metadata"></video>
            <div style="position:absolute;inset:0;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center">
                <span style="font-size:28px;opacity:.9">▶</span>
            </div>
            @else
            <img src="{{ $gUrl }}" alt="{{ $gitem['caption'] ?? '' }}" style="width:100%;height:100%;object-fit:cover;display:block">
            @endif
            @if(!empty($gitem['caption']))
            <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(to top,rgba(0,0,0,.8),transparent);padding:8px 10px 6px">
                <p style="font-size:10px;color:#fff;margin:0">{{ $gitem['caption'] }}</p>
            </div>
            @endif
        </div>
        @endforeach
        </div>
    </div>
    {{-- Gallery lightbox data --}}
    <script>
    window['gallery_{{ $worker->slug }}'] = @json($galleryItems);
    </script>
    @endif

    {{-- Deploy section --}}
    <div style="border-top:1px solid var(--border);padding:16px 22px;margin-top:auto">

        @if(!$isLive && !$isTesting)
        {{-- Coming soon --}}
        <button disabled style="width:100%;padding:11px;border-radius:10px;border:1px solid var(--border);color:var(--text-muted);background:transparent;font-size:13px;font-weight:600;cursor:not-allowed">
            Coming Soon
        </button>

        @elseif($isTesting)
        {{-- Testing mode — no public deploy --}}
        <div style="text-align:center;padding:8px 0">
            <p style="font-size:12px;color:#fbbf24;font-weight:600">This worker is in testing mode</p>
            <p style="font-size:11px;color:var(--text-muted);margin-top:2px">Available to testing-access users only via Fast Track</p>
        </div>

        @elseif($canDeploy)
        {{-- Deployable --}}
        <button onclick="toggleDeploy('{{ $worker->slug }}')"
                id="deploy-btn-{{ $worker->slug }}"
                style="width:100%;padding:11px;border-radius:10px;border:none;background:{{ $p['color'] }};color:#12100a;font-size:13px;font-weight:700;cursor:pointer;transition:opacity .15s"
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
                        style="padding:9px;border-radius:8px;border:1px solid var(--border);color:var(--text-muted);background:transparent;font-size:12px;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit"
                        style="padding:9px;border-radius:8px;border:none;background:{{ $p['color'] }};color:#12100a;font-size:12px;font-weight:700;cursor:pointer">
                        Confirm Hire
                    </button>
                </div>
            </form>
        </div>

        @else
        {{-- Max instances reached --}}
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
                <p style="font-size:12px;font-weight:600;color:var(--text-secondary)">{{ $count }} deployment{{ $count !== 1 ? 's' : '' }} running</p>
                <p style="font-size:11px;color:var(--text-muted);margin-top:2px">Add another Gmail inbox to deploy a second instance</p>
            </div>
            @php $connectRoute = $worker->slug === 'nux' ? route('nux.connect.linkedin') : route('ava.gmail.authorize'); @endphp
            <a href="{{ $connectRoute }}"
               style="font-size:11px;font-weight:700;padding:7px 12px;border-radius:8px;background:var(--accent);color:#12100a;text-decoration:none;white-space:nowrap;flex-shrink:0;margin-left:12px">
                + Connect
            </a>
        </div>
        @endif
    </div>
</div>
@endforeach
</div>

{{-- Free trial note --}}
<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:14px 18px;display:flex;align-items:center;gap:10px;max-width:480px">
    <span style="font-size:18px">🎁</span>
    <p style="font-size:12px;color:var(--text-muted);line-height:1.5">
        <strong style="color:var(--text-secondary)">25 free transactions</strong> on every worker. No credit card required until you scale.
    </p>
</div>

{{-- Lightbox overlay --}}
<div id="gallery-lightbox" onclick="if(event.target===this)closeGallery()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;align-items:center;justify-content:center;flex-direction:column">
    <button onclick="closeGallery()" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#fff;font-size:28px;cursor:pointer;opacity:.7">×</button>
    <button onclick="galleryPrev()" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:22px;width:44px;height:44px;border-radius:50%;cursor:pointer">‹</button>
    <button onclick="galleryNext()" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:22px;width:44px;height:44px;border-radius:50%;cursor:pointer">›</button>
    <div id="gallery-lb-media" style="max-width:90vw;max-height:80vh;display:flex;align-items:center;justify-content:center"></div>
    <p id="gallery-lb-caption" style="color:rgba(255,255,255,.65);font-size:13px;margin-top:14px;text-align:center"></p>
    <div id="gallery-lb-dots" style="display:flex;gap:6px;margin-top:12px"></div>
</div>

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
    const url = '/storage/' + item.path;
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
        v.src = '/storage/' + item.path; v.controls = true; v.autoplay = true; v.muted = false;
        v.style.cssText = 'max-width:90vw;max-height:78vh;border-radius:12px';
        mediaEl.appendChild(v);
    } else {
        const img = document.createElement('img');
        img.src = url; img.style.cssText = 'max-width:90vw;max-height:78vh;border-radius:12px;object-fit:contain';
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
    const open = form.style.display === 'none';
    form.style.display = open ? 'block' : 'none';
    if (open) {
        btn.textContent = '✕ Cancel';
        btn.style.background = 'transparent';
        btn.style.color = 'var(--text-muted)';
        btn.style.border = '1px solid var(--border)';
    } else {
        btn.textContent = 'Hire ' + slug.charAt(0).toUpperCase() + slug.slice(1) + ' →';
        btn.style.background = btn.getAttribute('data-color') || 'var(--accent)';
        btn.style.color = '#12100a';
        btn.style.border = 'none';
    }
}
// Store original colors
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[id^="deploy-btn-"]').forEach(btn => {
        btn.setAttribute('data-color', btn.style.background);
    });
});
</script>

</x-app-layout>

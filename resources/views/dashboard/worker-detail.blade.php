<x-app-layout title="{{ $dep->name }}">

    @include('partials.worker-subnav')

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @php
        $config       = json_decode($dep->config, true) ?? [];
        $coverImg     = $registryRow?->cover_image   ? asset('storage/' . $registryRow->cover_image)   : null;
        $profileImg   = $registryRow?->profile_image ? asset('storage/' . $registryRow->profile_image) : null;
        $mediaColor   = $registryRow ? (json_decode($registryRow->media ?? '{}', true)['color'] ?? '#f1d362') : '#f1d362';
        $mediaQuote   = $registryRow ? (json_decode($registryRow->media ?? '{}', true)['quote'] ?? '') : '';
        $rawGallery   = json_decode($registryRow?->gallery ?? '[]', true) ?? [];
        $galleryItems = array_values(array_filter($rawGallery, fn($g) => !in_array($g['type']??'', ['profile','cover'])));
    @endphp

    {{-- ── Worker Hero ──────────────────────────────────────────────────────── --}}
    @if($coverImg)
    <div style="position:relative;height:200px;border-radius:16px;overflow:hidden;margin-bottom:20px">
        <img src="{{ $coverImg }}" alt="{{ $dep->name }}"
             style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block">
        <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,0) 30%,rgba(0,0,0,.8) 100%)"></div>
        {{-- Worker identity over cover --}}
        <div style="position:absolute;bottom:0;left:0;right:0;padding:18px 20px;display:flex;align-items:flex-end;gap:14px">
            @if($profileImg)
            <img src="{{ $profileImg }}" alt="{{ $dep->name }}"
                 style="width:60px;height:60px;border-radius:14px;object-fit:cover;border:3px solid rgba(255,255,255,.15);flex-shrink:0;box-shadow:0 4px 20px rgba(0,0,0,.5)">
            @endif
            <div style="flex:1;min-width:0">
                <p style="font-size:20px;font-weight:800;color:#fff;line-height:1.2;text-shadow:0 2px 8px rgba(0,0,0,.6)">{{ $dep->name }}</p>
                @if($mediaQuote)
                <p style="font-size:12px;color:rgba(255,255,255,.7);margin-top:3px;font-style:italic">"{{ $mediaQuote }}"</p>
                @endif
            </div>
            <span style="font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;flex-shrink:0;
                background:rgba(0,0,0,.5);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);
                color:{{ $dep->status === 'active' ? '#4ade80' : '#fbbf24' }}">
                {{ $dep->status === 'active' ? '● Active' : '⏸ Paused' }}
            </span>
        </div>
    </div>
    @endif

    {{-- ── Gallery ─────────────────────────────────────────────────────────── --}}
    @if(!empty($galleryItems))
    <div style="margin-bottom:24px">
        <p style="font-size:11px;font-weight:700;letter-spacing:.07em;color:var(--text-muted);text-transform:uppercase;margin-bottom:10px">Worker in Action</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
        @foreach($galleryItems as $gi => $gitem)
        @php
            $isYt = in_array($gitem['type']??'', ['youtube_intro','youtube_pipeline']);
            $isFileVideo = !$isYt && ($gitem['kind']??'') === 'file' && preg_match('/\.(mp4|mov|webm)$/i', $gitem['path']??'');
            if ($isYt) {
                preg_match('/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $gitem['url']??'', $ytm);
                $ytId = $ytm[1] ?? '';
                $thumb = "https://img.youtube.com/vi/{$ytId}/mqdefault.jpg";
            } else {
                $gUrl = asset('storage/' . ($gitem['path']??''));
            }
        @endphp
        <div style="border-radius:12px;overflow:hidden;position:relative;height:140px;cursor:pointer;border:1px solid var(--border)" onclick="openDetailGallery({{ $gi }})">
            @if($isYt)
            <img src="{{ $thumb }}" alt="{{ $gitem['caption'] ?? '' }}" style="width:100%;height:100%;object-fit:cover;display:block">
            <div style="position:absolute;inset:0;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center">
                <span style="width:40px;height:40px;background:#f00;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff">▶</span>
            </div>
            @elseif($isFileVideo)
            <video src="{{ $gUrl }}" style="width:100%;height:100%;object-fit:cover;display:block" muted preload="metadata"></video>
            <div style="position:absolute;inset:0;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center">
                <span style="font-size:32px;opacity:.9">▶</span>
            </div>
            @else
            <img src="{{ $gUrl }}" alt="{{ $gitem['caption'] ?? '' }}" style="width:100%;height:100%;object-fit:cover;display:block">
            @endif
            @if(!empty($gitem['caption']))
            <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(to top,rgba(0,0,0,.8),transparent);padding:8px 10px 6px">
                <p style="font-size:11px;color:#fff">{{ $gitem['caption'] }}</p>
            </div>
            @endif
        </div>
        @endforeach
        </div>
    </div>

    {{-- Detail page lightbox --}}
    <div id="detail-gallery-lb" onclick="if(event.target===this)closeDetailGallery()"
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;align-items:center;justify-content:center;flex-direction:column">
        <button onclick="closeDetailGallery()" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#fff;font-size:28px;cursor:pointer;opacity:.7">×</button>
        <button onclick="detailGalleryPrev()" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:22px;width:44px;height:44px;border-radius:50%;cursor:pointer">‹</button>
        <button onclick="detailGalleryNext()" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:22px;width:44px;height:44px;border-radius:50%;cursor:pointer">›</button>
        <div id="detail-lb-media" style="max-width:90vw;max-height:80vh;display:flex;align-items:center;justify-content:center"></div>
        <p id="detail-lb-caption" style="color:rgba(255,255,255,.65);font-size:13px;margin-top:14px;text-align:center"></p>
        <div id="detail-lb-dots" style="display:flex;gap:6px;margin-top:12px"></div>
    </div>
    <script>
    const _detailGallery = @json($galleryItems);
    let _detailIdx = 0;
    function openDetailGallery(idx) { _detailIdx = idx; document.getElementById('detail-gallery-lb').style.display='flex'; document.body.style.overflow='hidden'; renderDetailLb(); }
    function closeDetailGallery() { document.getElementById('detail-gallery-lb').style.display='none'; document.body.style.overflow=''; document.getElementById('detail-lb-media').innerHTML=''; }
    function detailGalleryPrev() { _detailIdx = (_detailIdx - 1 + _detailGallery.length) % _detailGallery.length; renderDetailLb(); }
    function detailGalleryNext() { _detailIdx = (_detailIdx + 1) % _detailGallery.length; renderDetailLb(); }
    const _ytTypes = ['youtube_intro','youtube_pipeline'];
    function _ytId(url) { const m = (url||'').match(/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/); return m ? m[1] : null; }
    function renderDetailLb() {
        const item = _detailGallery[_detailIdx];
        const el = document.getElementById('detail-lb-media');
        el.innerHTML = '';
        if (_ytTypes.includes(item.type)) {
            const id = _ytId(item.url||'');
            const iframe = document.createElement('iframe');
            iframe.src = 'https://www.youtube.com/embed/' + id + '?autoplay=1';
            iframe.style.cssText = 'width:min(90vw,800px);height:min(50vw,450px);border:none;border-radius:12px';
            iframe.allow = 'autoplay; fullscreen';
            el.appendChild(iframe);
        } else if (item.kind === 'file' && /\.(mp4|mov|webm)$/i.test(item.path||'')) {
            const v = document.createElement('video'); v.src='/storage/'+item.path; v.controls=true; v.autoplay=true; v.style.cssText='max-width:90vw;max-height:78vh;border-radius:12px'; el.appendChild(v);
        } else {
            const img = document.createElement('img'); img.src='/storage/'+(item.path||''); img.style.cssText='max-width:90vw;max-height:78vh;border-radius:12px;object-fit:contain'; el.appendChild(img);
        }
        document.getElementById('detail-lb-caption').textContent = item.caption || '';
        const dots = document.getElementById('detail-lb-dots');
        dots.innerHTML = '';
        _detailGallery.forEach((_,i) => { const d=document.createElement('div'); d.style.cssText=`width:7px;height:7px;border-radius:50%;background:${i===_detailIdx?'#fff':'rgba(255,255,255,.3)'};cursor:pointer`; d.onclick=()=>{_detailIdx=i;renderDetailLb();}; dots.appendChild(d); });
    }
    document.addEventListener('keydown', e => {
        if (document.getElementById('detail-gallery-lb').style.display !== 'none') {
            if (e.key==='ArrowLeft') detailGalleryPrev();
            if (e.key==='ArrowRight') detailGalleryNext();
            if (e.key==='Escape') closeDetailGallery();
        }
    });
    </script>
    @endif

    {{-- ── Policy Violations ───────────────────────────────────────────────── --}}
    @if(!empty($policyViolations))
    <div class="mb-5">
        @include('partials.policy-violations', ['violations' => $policyViolations])
    </div>
    @endif

    {{-- ── Production Readiness ───────────────────────────────────────────── --}}
    @if(!$productionReadiness['ready'])
    <div class="mb-4 flex items-start justify-between gap-4 px-5 py-4 rounded-xl border"
         style="background:rgba(239,68,68,0.06);border-color:rgba(239,68,68,0.3)">
        <div class="flex items-start gap-3">
            <span class="text-red-400 mt-0.5 shrink-0">⛔</span>
            <div>
                <p class="text-red-300 font-semibold text-sm">{{ $productionReadiness['banner_title'] }}</p>
                <p class="text-gray-400 text-xs mt-0.5 leading-relaxed">
                    {{ $productionReadiness['banner_body'] }}
                    @if(!$isMultiCredential && $credential)
                        Fast Track can still run using <span class="text-gray-300">{{ $credential->gmail_address }}</span>
                        (a credential on your account), but that account is <strong>not monitoring this worker</strong> in production.
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('workers.connect', $dep->id) }}"
           class="shrink-0 text-xs px-3 py-1.5 rounded-lg border border-red-700 text-red-400 hover:bg-red-900/20 transition font-medium">
            {{ $productionReadiness['connect_label'] }}
        </a>
    </div>
    @endif

    {{-- ── Operational Alerts ──────────────────────────────────────────────── --}}
    @if($pendingReview > 0 || $stuckCount > 0)
    <div class="space-y-2 mb-4">
        @if($pendingReview > 0)
        <div class="flex items-center justify-between px-4 py-3 rounded-xl border"
             style="background:rgba(var(--accent-rgb),0.08);border-color:rgba(var(--accent-rgb),0.35)">
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full animate-pulse" style="background:var(--accent)"></span>
                <span class="text-sm font-medium" style="color:var(--text-primary)">
                    <strong>{{ $pendingReview }}</strong> draft{{ $pendingReview !== 1 ? 's' : '' }} awaiting your review
                </span>
            </div>
            <a href="{{ route('transactions', ['filter' => 'draft_ready']) }}"
               class="text-xs px-3 py-1.5 rounded-lg font-medium transition"
               style="background:var(--accent);color:#000000;border:1px solid var(--accent-dark)">
                Review Now →
            </a>
        </div>
        @endif

        @if($stuckCount > 0)
        <div class="flex items-center justify-between px-4 py-3 rounded-xl border"
             style="background:rgba(245,158,11,0.08);border-color:rgba(245,158,11,0.3)">
            <div class="flex items-center gap-3">
                <span style="color:#f59e0b">⚠</span>
                <span class="text-sm" style="color:#fcd34d">
                    <strong>{{ $stuckCount }}</strong> transaction{{ $stuckCount !== 1 ? 's' : '' }} stuck mid-pipeline for &gt;5 min
                </span>
            </div>
            <button id="recover-btn" onclick="recoverStuck()"
                class="text-xs px-3 py-1.5 rounded-lg font-medium transition"
                style="background:rgba(245,158,11,0.2);color:#fcd34d;border:1px solid rgba(245,158,11,0.4)">
                ↺ Recover All
            </button>
        </div>
        @endif
    </div>
    @endif

    {{-- ── Employer Overview — contract-driven panels ─────────────────────── --}}
    @if(!empty($overviewPanels))
    <div class="space-y-5">
        @foreach($overviewPanels as $panel)
            @php $data = $panel['data'] ?? []; @endphp

            {{-- ── action_queue ─────────────────────────────────────────── --}}
            @if($panel['type'] === 'action_queue')
            <div style="background:var(--bg-card);border:1px solid var(--border)" class="rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--border)">
                    <div class="flex items-center gap-2">
                        <p class="text-white font-semibold text-sm">{{ $panel['title'] }}</p>
                        @if(($data['count'] ?? 0) > 0)
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:rgba(var(--accent-rgb),.15);color:var(--accent-text)">{{ $data['count'] }}</span>
                        @endif
                    </div>
                    @if(($data['count'] ?? 0) > 0)
                    <a href="{{ route('transactions.index', ['filter' => 'draft_ready']) }}" class="text-xs font-medium transition" style="color:var(--accent-text)">Review all →</a>
                    @endif
                </div>
                @if(empty($data['items']))
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm" style="color:var(--text-muted)">{{ $panel['empty'] ?? 'Nothing awaiting review.' }}</p>
                    </div>
                @else
                    <div class="divide-y" style="border-color:var(--border-subtle)">
                        @foreach($data['items'] as $item)
                        <a href="{{ route('transactions.show', $item['tx_id']) }}" class="flex items-start justify-between px-5 py-4 hover:bg-white/5 transition group">
                            <div class="flex-1 min-w-0 pr-4">
                                <p class="text-white text-sm font-medium truncate">{{ $item['client'] ?? 'Unknown sender' }}</p>
                                <p class="text-xs mt-0.5 truncate" style="color:var(--text-muted)">{{ $item['asset'] }}</p>
                                @if($item['days_left'] !== null && $item['days_left'] <= 30)
                                <p class="text-xs mt-1 {{ $item['days_left'] <= 7 ? 'text-red-400' : 'text-amber-400' }}">
                                    {{ $item['days_left'] <= 0 ? 'Expired' : 'Renews in ' . $item['days_left'] . ' days' }}
                                </p>
                                @endif
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs px-2 py-1 rounded-lg font-medium" style="background:rgba(var(--accent-rgb),.12);color:var(--accent-text)">Draft ready</span>
                                <p class="text-xs mt-1" style="color:var(--text-faint)">{{ \Carbon\Carbon::parse($item['created_at'])->diffForHumans() }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            {{-- ── horizon ──────────────────────────────────────────────── --}}
            @if($panel['type'] === 'horizon')
            <div style="background:var(--bg-card);border:1px solid var(--border)" class="rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b" style="border-color:var(--border)">
                    <p class="text-white font-semibold text-sm">{{ $panel['title'] }}</p>
                    @if(($data['total'] ?? 0) === 0)
                    <p class="text-xs mt-0.5" style="color:var(--text-muted)">No assets with renewal dates set — add them in Memory.</p>
                    @endif
                </div>
                @if(($data['total'] ?? 0) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x" style="border-color:var(--border-subtle)">
                    @foreach($data['buckets'] as $bucket)
                    <div class="px-5 py-4">
                        <p class="text-xs font-semibold mb-3" style="color:var(--text-muted)">
                            {{ $bucket['prev'] === 0 ? 'Within ' . $bucket['window'] . ' days' : ($bucket['prev'] + 1) . '–' . $bucket['window'] . ' days' }}
                        </p>
                        @if(empty($bucket['items']))
                            <p class="text-xs" style="color:var(--text-faint)">None</p>
                        @else
                            <div class="space-y-2">
                            @foreach($bucket['items'] as $asset)
                            <div>
                                <p class="text-white text-xs font-medium leading-snug">{{ $asset['name'] }}</p>
                                <p class="text-xs" style="color:var(--text-muted)">
                                    {{ $asset['client'] ? $asset['client'] . ' · ' : '' }}{{ $asset['days_left'] }}d
                                </p>
                            </div>
                            @endforeach
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            {{-- ── metric_strip ─────────────────────────────────────────── --}}
            @if($panel['type'] === 'metric_strip')
            @php $metrics = $data['metrics'] ?? []; @endphp
            @if(!empty($metrics))
            <div style="background:var(--bg-card);border:1px solid var(--border)" class="rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b" style="border-color:var(--border)">
                    <p class="text-white font-semibold text-sm">{{ $panel['title'] }}</p>
                    <p class="text-xs mt-0.5" style="color:var(--text-muted)">Since {{ \Carbon\Carbon::parse($data['since'])->format('M j') }}</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-{{ count($metrics) }} divide-y sm:divide-y-0 sm:divide-x" style="border-color:var(--border-subtle)">
                    @foreach($metrics as $m)
                    <div class="px-5 py-4">
                        <p class="text-2xl font-bold text-white">{{ $m['value'] !== null ? $m['value'] . $m['suffix'] : '—' }}</p>
                        <p class="text-xs mt-1" style="color:var(--text-muted)">{{ $m['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif

            {{-- ── alert_feed ───────────────────────────────────────────── --}}
            @if($panel['type'] === 'alert_feed')
            @if(($data['count'] ?? 0) > 0)
            <div style="background:var(--bg-card);border:1px solid var(--border)" class="rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b" style="border-color:var(--border)">
                    <p class="text-white font-semibold text-sm">{{ $panel['title'] }}</p>
                </div>
                <div class="divide-y" style="border-color:var(--border-subtle)">
                    @foreach($data['alerts'] as $alert)
                    <div class="flex items-start gap-3 px-5 py-4">
                        <div class="mt-0.5 shrink-0">
                            @if($alert['severity'] === 'error')
                                <span class="w-2 h-2 rounded-full bg-red-500 inline-block mt-1"></span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-amber-400 inline-block mt-1"></span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm" style="color:var(--text-primary)">{{ $alert['message'] }}</p>
                        </div>
                        @if(!empty($alert['action']) && !empty($alert['route']))
                        <a href="{{ route($alert['route'], $alert['params'] ?? []) }}"
                           class="text-xs font-medium shrink-0 transition" style="color:var(--accent-text)">
                            {{ $alert['action'] }} →
                        </a>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif

            {{-- ── activity_feed ────────────────────────────────────────── --}}
            @if($panel['type'] === 'activity_feed')
            @php $items = $data['items'] ?? []; @endphp
            @if(!empty($items))
            <div style="background:var(--bg-card);border:1px solid var(--border)" class="rounded-xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:var(--border)">
                    <p class="text-white font-semibold text-sm">{{ $panel['title'] }}</p>
                    <a href="{{ route('transactions.index') }}" class="text-xs transition" style="color:var(--text-muted)">View all →</a>
                </div>
                <div class="divide-y" style="border-color:var(--border-subtle)">
                    @foreach($items as $item)
                    <a href="{{ route('transactions.show', $item['tx_id']) }}"
                       class="flex items-center gap-3 px-5 py-3.5 hover:bg-white/5 transition">
                        <div class="w-1.5 h-1.5 rounded-full shrink-0
                            {{ $item['status'] === 'draft_ready'  ? 'bg-yellow-400' :
                               ($item['status'] === 'approved' || $item['status'] === 'sent' ? 'bg-green-500' :
                               ($item['status'] === 'failed'   ? 'bg-red-500' : 'bg-gray-600')) }}">
                        </div>
                        <p class="text-sm flex-1 min-w-0 truncate" style="color:var(--text-secondary)">{{ $item['sentence'] }}</p>
                        <p class="text-xs shrink-0" style="color:var(--text-faint)">{{ \Carbon\Carbon::parse($item['created_at'])->diffForHumans(null, true) }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
            @endif

        @endforeach
    </div>

    @else
    {{-- Fallback: no overview contract declared — show legacy layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: stats + recent activity --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Status bar --}}
            <div class="rounded-xl p-4 sm:p-5 flex flex-wrap items-center justify-between gap-3" style="background:var(--bg-card);border:1px solid var(--border)">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:var(--accent)">
                        <span class="font-bold text-sm" style="color:#000000">{{ strtoupper(substr($dep->worker_slug, 0, 1)) }}</span>
                    </div>
                    <div>
                        <p class="text-white font-semibold">{{ $dep->name }}</p>
                        <p class="text-gray-500 text-xs">{{ $dep->worker_slug }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($dep->status === 'active')
                        <span class="flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded" style="background:rgba(34,197,94,0.15);color:#16a34a;border:1px solid rgba(34,197,94,0.35)">
                            <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background:#16a34a"></span> Active
                        </span>
                        <form method="POST" action="{{ route('workers.status', $dep->id) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="paused">
                            <button class="text-xs px-3 py-1 rounded" style="color:var(--accent-text);border:1px solid rgba(var(--accent-rgb),0.4)">Pause</button>
                        </form>
                    @elseif($dep->status === 'paused')
                        <span class="flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded" style="background:var(--badge-balanced-bg);color:var(--badge-balanced-text);border:1px solid rgba(var(--accent-rgb),0.35)">
                            <span class="w-1.5 h-1.5 rounded-full" style="background:var(--badge-balanced-text)"></span> Paused
                        </span>
                        <form method="POST" action="{{ route('workers.status', $dep->id) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="active">
                            <button class="text-xs px-3 py-1 rounded" style="color:#16a34a;border:1px solid rgba(34,197,94,0.35)">Resume</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('workers.destroy', $dep->id) }}" onsubmit="return confirm('Remove this worker?')">
                        @csrf @method('DELETE')
                        <button class="text-xs px-3 py-1 rounded transition" style="color:var(--text-muted);border:1px solid var(--border)">Remove</button>
                    </form>
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-3 sm:gap-4">
                <div class="rounded-xl p-3 sm:p-4" style="background:var(--bg-card);border:1px solid var(--border)">
                    <p class="text-xs" style="color:var(--text-muted)">Transactions</p>
                    <p class="text-xl sm:text-2xl font-semibold mt-1" style="color:var(--text-primary)">{{ $txCount }}</p>
                </div>
                <div class="rounded-xl p-3 sm:p-4" style="background:var(--bg-card);border:1px solid var(--border)">
                    <p class="text-xs" style="color:var(--text-muted)">Tokens Used</p>
                    <p class="text-xl sm:text-2xl font-semibold mt-1 truncate" style="color:var(--text-primary)">{{ number_format($usage->tokens ?? 0) }}</p>
                </div>
                <div class="rounded-xl p-3 sm:p-4" style="background:var(--bg-card);border:1px solid var(--border)">
                    <p class="text-xs" style="color:var(--text-muted)">AI Cost</p>
                    <p class="text-xl sm:text-2xl font-semibold mt-1 truncate" style="color:var(--text-primary)">${{ number_format($usage->cost ?? 0, 4) }}</p>
                </div>
            </div>

            {{-- Idea Submission (NUX only) --}}
            @if($isMultiCredential)
            <div class="rounded-xl p-4 sm:p-5" style="background:var(--bg-card);border:1px solid var(--border)">
                <h3 class="text-sm font-semibold mb-1" style="color:var(--text-primary)">Submit an Idea</h3>
                <p class="text-xs mb-3" style="color:var(--text-muted)">Record a thought or draft — NUX will repurpose it into polished posts for your chosen channels.</p>
                <form id="idea-form" onsubmit="submitIdea(event)">
                    @csrf
                    <textarea id="idea-text" name="idea_text" rows="3"
                              placeholder="What's on your mind? A lesson learned, a hot take, a story…"
                              class="w-full text-sm rounded-lg px-3 py-2 border focus:outline-none resize-none"
                              style="background:var(--bg-surface);color:var(--text-primary);border-color:var(--border);placeholder-color:var(--text-faint)"></textarea>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="text-xs shrink-0" style="color:var(--text-muted)">Post to:</span>
                        @foreach(['linkedin','x'] as $ch)
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs" style="color:var(--text-secondary)">
                            <input type="checkbox" name="target_channels[]" value="{{ $ch }}" class="rounded" {{ $ch === 'linkedin' ? 'checked' : '' }}>
                            {{ ucfirst($ch) }}
                        </label>
                        @endforeach
                        <button type="submit" id="idea-submit-btn"
                                class="ml-auto text-xs font-bold px-4 py-2 rounded-xl hover:opacity-90 transition"
                                style="background:var(--accent);color:#1a1404">
                            Submit Idea
                        </button>
                    </div>
                    <p id="idea-feedback" class="text-xs mt-2 hidden"></p>
                </form>
            </div>
            @endif

            {{-- Fast Track + Subscribe --}}
            @php
                $ftUses      = (int) ($config['fast_track_uses'] ?? 0);
                $ftBilling   = \Illuminate\Support\Facades\DB::table('deployment_billing')->where('deployment_id', $dep->id)->first();
                $ftMax       = (int) ($ftBilling?->trial_transactions_limit ?: 10);
                $ftLeft      = max(0, $ftMax - $ftUses);
                $ftSubscribed = $ftBilling && $ftBilling->status === 'active';
                $watchTxId   = request('watch');
                // Multi-credential workers (NUX) always have a demo payload — no Gmail inbox needed for Fast Track
                $ftInbox     = $isMultiCredential ? null : ($connectedInboxes->firstWhere('is_primary', true) ?? $connectedInboxes->first());
                $ftFallback  = (!$isMultiCredential && $ftInbox === null && $credential) ? $credential : null;
                $ftCanRun    = $isMultiCredential || $ftInbox !== null || $ftFallback !== null;
            @endphp

            <div class="rounded-xl p-4 sm:p-5" style="background:var(--bg-card);border:1px solid var(--border)">

                {{-- Header row --}}
                <div class="flex items-center justify-between mb-3 gap-3">
                    <div>
                        <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Fast Track Test</h3>
                        <p class="text-xs mt-0.5" style="color:var(--text-muted)">{{ $productionReadiness['fast_track_desc'] }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        @if($ftSubscribed)
                            <span class="text-xs text-green-400 font-medium">Unlimited · Active</span>
                        @else
                            <span class="text-xs font-mono {{ $ftLeft > 2 ? '' : ($ftLeft > 0 ? 'text-amber-400' : 'text-red-400') }}" style="{{ $ftLeft > 2 ? 'color:var(--text-muted)' : '' }}">
                                {{ $ftLeft }}/{{ $ftMax }} trial left
                            </span>
                            <div class="mt-1 h-1 w-20 ml-auto rounded-full overflow-hidden" style="background:var(--border)">
                                <div class="h-full rounded-full transition-all"
                                     style="width:{{ $ftMax > 0 ? ($ftUses / $ftMax) * 100 : 0 }}%;background:{{ $ftLeft > 3 ? '#a78bfa' : ($ftLeft > 0 ? '#f59e0b' : '#ef4444') }}"></div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Inbox hint / selector (Gmail workers only) --}}
                @if($isMultiCredential)
                    {{-- Multi-credential workers use a demo payload — no inbox selector needed --}}
                    <form method="POST" action="{{ route('workers.fast-track', $dep->id) }}" id="ft-form">
                        @csrf
                        <p class="text-xs mb-3" style="color:var(--text-muted)">
                            Uses the built-in demo post — no social account needed to test.
                        </p>
                    </form>
                @elseif($connectedInboxes->count() > 1)
                <form method="POST" action="{{ route('workers.fast-track', $dep->id) }}" id="ft-form" class="mb-3">
                    @csrf
                    <select name="credential_id"
                            class="w-full text-xs rounded-lg px-3 py-2 border focus:outline-none"
                            style="background:var(--bg-surface);color:var(--text-primary);border-color:var(--border)">
                        @foreach($connectedInboxes as $inbox)
                            <option value="{{ $inbox->id }}" {{ $inbox->is_primary ? 'selected' : '' }}>
                                {{ $inbox->gmail_address }}{{ $inbox->is_primary ? ' · Primary' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @elseif($ftInbox)
                <form method="POST" action="{{ route('workers.fast-track', $dep->id) }}" id="ft-form">
                    @csrf
                    <input type="hidden" name="credential_id" value="{{ $ftInbox->id }}">
                    <p class="text-xs mb-3" style="color:var(--text-muted)">Will run on <span style="color:var(--text-secondary)">{{ $ftInbox->gmail_address }}</span></p>
                </form>
                @elseif($ftFallback)
                <form method="POST" action="{{ route('workers.fast-track', $dep->id) }}" id="ft-form">
                    @csrf
                    <div class="mb-3 px-3 py-2 rounded-lg border border-amber-800/50 text-xs" style="background:rgba(120,53,15,0.15)">
                        <p class="text-amber-400 font-medium mb-0.5">Using fallback inbox</p>
                        <p class="text-amber-200/60">{{ $ftFallback->gmail_address }} — <a href="{{ route('workers.connect', $dep->id) }}" class="underline">connect it properly</a> for live monitoring.</p>
                    </div>
                </form>
                @else
                <div id="ft-form"></div>
                @endif

                @if($ftLeft > 0 || $ftSubscribed)
                    {{-- Split: Fast Track + Subscribe side by side --}}
                    <div class="{{ $ftSubscribed ? '' : 'grid grid-cols-1 sm:grid-cols-2 gap-3' }}">

                        {{-- Fast Track button --}}
                        @if($ftCanRun)
                        <button type="submit" form="ft-form" id="ft-submit-btn"
                                onclick="this.disabled=true;this.innerHTML='<span style=\'opacity:0.6\'>Running…</span>';document.getElementById(\'ft-form\').submit();"
                                class="w-full text-sm font-bold px-4 py-3 rounded-xl hover:opacity-90 flex items-center justify-center gap-2 transition"
                                style="background:var(--accent);color:#1a1404">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Run Fast Track
                        </button>
                        @else
                        <div class="w-full text-center text-xs py-3 rounded-xl border" style="color:var(--text-faint);border-color:var(--border)">
                            <a href="{{ route('workers.connect', $dep->id) }}" style="color:var(--text-muted)" class="hover:underline">{{ $productionReadiness['no_credential_msg'] }}</a>
                        </div>
                        @endif

                        {{-- Subscribe CTA — only while on trial --}}
                        @if(!$ftSubscribed)
                        <a href="{{ route('workers.billing', $dep->worker_slug) }}"
                           class="w-full flex flex-col items-center justify-center gap-0.5 px-4 py-3 rounded-xl border transition hover:border-yellow-400/40 group"
                           style="border-color:var(--border);background:var(--bg-surface)">
                            <span class="text-sm font-bold group-hover:text-yellow-300 transition" style="color:var(--text-primary)">Subscribe →</span>
                            <span class="text-xs" style="color:var(--text-muted)">Unlock unlimited runs</span>
                        </a>
                        @endif

                    </div>
                @else
                    {{-- Trial exhausted --}}
                    <div class="rounded-xl border border-red-900/40 px-4 py-4 text-center space-y-3" style="background:rgba(127,29,29,0.08)">
                        <p class="text-sm font-semibold text-red-400">Trial runs used up</p>
                        <p class="text-xs" style="color:var(--text-muted)">Subscribe to unlock unlimited Fast Track runs and live email processing.</p>
                        <a href="{{ route('workers.billing', $dep->worker_slug) }}"
                           class="inline-block text-sm font-bold px-5 py-2 rounded-xl hover:opacity-90 transition"
                           style="background:var(--accent);color:#1a1404">
                            Choose a plan →
                        </a>
                    </div>
                @endif
            </div>

            {{-- Pipeline modal (auto-opens when ?watch= is present) --}}
            <div id="pipeline-modal" class="fixed inset-0 z-50 flex items-center justify-center {{ $watchTxId ? '' : 'hidden' }}"
                 style="background:rgba(2,4,10,0.85);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px)">
                <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-2xl"
                     style="width:calc(100vw - 48px);max-width:1000px">

                    {{-- Header --}}
                    <div class="px-7 py-4 border-b border-gray-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                                 style="background:rgba(var(--accent-rgb),0.12);border:1px solid rgba(var(--accent-rgb),0.25)">
                                <svg class="w-4 h-4" fill="none" stroke="var(--accent)" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-semibold text-sm">Fast Track Pipeline</p>
                                <p id="modal-tx-id" class="text-gray-600 text-xs font-mono">{{ $watchTxId ?? '' }}</p>
                            </div>
                        </div>
                        <button onclick="closePipelineModal()"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-600 hover:text-white hover:bg-gray-800 transition text-sm">✕</button>
                    </div>

                    {{-- Pipeline flow --}}
                    @php
                    $pipelineSteps = [
                        'ingest'   => ['label'=>'Inject & Fetch',   'sub'=>'Insert into inbox, read back',  'icon'=>'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
                        'read'     => ['label'=>'Read Email',       'sub'=>'Parse & extract fields',        'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        'classify' => ['label'=>'Classify',         'sub'=>'Category, priority & type',    'icon'=>'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                        'memory'   => ['label'=>'Memory Lookup',    'sub'=>'Match client, asset & rules',  'icon'=>'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18'],
                        'log'      => ['label'=>'Log Transaction',   'sub'=>'Write to register',            'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        'template' => ['label'=>'Select Template',   'sub'=>'Pick best-match template',     'icon'=>'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                        'draft'    => ['label'=>'Draft Email',       'sub'=>'AI-personalised draft',        'icon'=>'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z'],
                        'push'     => ['label'=>'Push to Gmail',     'sub'=>'Create draft in inbox',        'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ];
                    @endphp

                    <div class="px-6 pt-8 pb-5">
                        <div class="flex items-start justify-between">
                            @foreach($pipelineSteps as $key => $step)
                                {{-- Step --}}
                                <div id="stage-{{ $key }}" class="flex flex-col items-center flex-1">
                                    {{-- Circle --}}
                                    <div class="stage-bubble w-14 h-14 rounded-full border-2 flex items-center justify-center relative transition-all duration-300"
                                         style="border-color:#2d3748;background:#0d1117">
                                        {{-- Pending icon --}}
                                        <svg class="stage-icon w-6 h-6 transition-colors duration-300 absolute" style="color:#374151" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $step['icon'] }}"/>
                                        </svg>
                                        {{-- Checkmark --}}
                                        <svg class="stage-check w-7 h-7 hidden absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        {{-- Error X --}}
                                        <svg class="stage-x w-6 h-6 hidden absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </div>
                                    {{-- Label --}}
                                    <p class="stage-label text-xs font-semibold text-center mt-3 leading-tight px-1" style="color:#4b5563">{{ $step['label'] }}</p>
                                    <p class="text-gray-700 text-xs text-center mt-1 leading-tight px-1 hidden sm:block">{{ $step['sub'] }}</p>
                                    {{-- Status badge --}}
                                    <p class="stage-badge-text text-xs font-mono mt-2" style="color:#2d3748">·</p>
                                </div>

                                {{-- Arrow connector --}}
                                @if(!$loop->last)
                                <div id="arrow-{{ $key }}" class="flex items-center shrink-0" style="padding-bottom:48px;width:32px">
                                    <svg viewBox="0 0 32 10" fill="none" class="w-full">
                                        <path d="M0 5 H24 M20 1 L30 5 L20 9" stroke="#2d3748" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="arrow-path"/>
                                    </svg>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Status bar --}}
                    <div class="mx-6 mb-5 rounded-xl border px-5 py-3 flex items-center gap-3 transition-all duration-500"
                         id="pipeline-status-bar" style="background:rgba(255,255,255,0.02);border-color:#1f2937">
                        <div id="pipeline-spinner" class="{{ $watchTxId ? '' : 'hidden' }} shrink-0">
                            <svg class="animate-spin w-4 h-4" style="color:var(--accent)" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>
                        <span id="pipeline-overall" class="text-sm font-medium flex-1" style="color:#6b7280">
                            {{ $watchTxId ? 'Initialising pipeline…' : '' }}
                        </span>
                        <button onclick="closePipelineModal()" class="text-xs text-gray-600 hover:text-gray-400 shrink-0">Close</button>
                    </div>

                </div>
            </div>

            {{-- Recent transactions --}}
            <div class="rounded-xl" style="background:var(--bg-card);border:1px solid var(--border)">
                <div class="px-5 py-4 flex items-center justify-between" style="border-bottom:1px solid var(--border)">
                    <h3 class="text-sm font-semibold" style="color:var(--text-primary)">Recent Activity</h3>
                    <a href="{{ route('transactions') }}" class="text-xs text-gray-500 hover:text-brand">View all →</a>
                </div>
                @forelse($recentTx as $tx)
                    <div class="px-5 py-3 border-b border-gray-800 last:border-0 flex items-center justify-between">
                        <div>
                            <a href="{{ route('transactions.show', $tx->tx_id) }}" class="text-xs font-mono hover:underline" style="color:var(--text-secondary)">{{ $tx->tx_id }}</a>
                            <span class="text-gray-500 text-xs ml-2">{{ $tx->category }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($tx->created_at)->diffForHumans() }}</span>
                            @php
                                $badgeStyle = $tx->status === 'draft_ready'
                                    ? 'background:rgba(var(--accent-rgb),0.18);color:var(--accent-text);border:1px solid rgba(var(--accent-rgb),0.35)'
                                    : ($tx->status === 'failed'
                                        ? 'background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.3)'
                                        : 'background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)');
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded" style="{{ $badgeStyle }}">{{ $tx->status }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-600 text-sm">No transactions yet for this worker.</div>
                @endforelse
            </div>

            {{-- Connected Accounts summary — links to Connect tab --}}
            @php
                $watchInactive = $connectedInboxes->where('watch_active', false)->count();
            @endphp
            <a href="{{ route('workers.connect', $dep->id) }}"
               class="block bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl px-5 py-4 transition group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white text-sm font-semibold group-hover:text-brand transition">
                            Connected Accounts
                        </p>
                        <p class="text-gray-500 text-xs mt-0.5">
                            @if($isMultiCredential)
                                @if(empty($productionReadiness['connected_accounts']))
                                    <span class="text-red-400">⛔ No accounts connected — not production ready</span>
                                @else
                                    @foreach($productionReadiness['connected_accounts'] as $platform)
                                        <span class="text-green-400">✓ {{ ucfirst($platform) }}</span>{{ !$loop->last ? ' · ' : '' }}
                                    @endforeach
                                @endif
                            @else
                                @if($connectedInboxes->isEmpty())
                                    <span class="text-red-400">⛔ No inbox connected — not production ready</span>
                                @else
                                    {{ $connectedInboxes->count() }} connected
                                    @if($watchInactive > 0)
                                        · <span class="text-yellow-400">{{ $watchInactive }} watch inactive</span>
                                    @else
                                        · <span class="text-green-400">all watching</span>
                                    @endif
                                @endif
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($isMultiCredential)
                            @foreach($productionReadiness['connected_accounts'] as $platform)
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                     style="background:var(--accent);color:#000000;box-shadow:0 0 0 2px rgba(34,197,94,0.4)">
                                    {{ strtoupper(substr($platform, 0, 1)) }}
                                </div>
                            @endforeach
                            @if(empty($productionReadiness['connected_accounts']))
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                     style="background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)">—</div>
                            @endif
                        @else
                            @foreach($connectedInboxes->take(3) as $inbox)
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                     style="{{ $inbox->watch_active ? 'background:var(--accent);color:#000000;box-shadow:0 0 0 2px rgba(34,197,94,0.4)' : 'background:var(--bg-raised);color:var(--text-muted);border:1px solid var(--border)' }}">
                                    {{ strtoupper(substr($inbox->gmail_address, 0, 1)) }}
                                </div>
                            @endforeach
                            @if($connectedInboxes->count() > 3)
                                <span class="text-gray-600 text-xs">+{{ $connectedInboxes->count() - 3 }}</span>
                            @endif
                        @endif
                        <svg class="w-4 h-4 text-gray-600 group-hover:text-brand transition ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>

        </div>

        {{-- Right: quick links --}}
        <div class="space-y-3">

            @php $config2 = json_decode($dep->config, true) ?? []; @endphp

            {{-- Configure link --}}
            <a href="{{ route('workers.configure', $dep->id) }}"
               class="flex items-center justify-between rounded-xl px-4 py-3.5 transition group" style="background:var(--bg-card);border:1px solid var(--border)">
                <div>
                    <p class="text-white text-sm font-medium group-hover:text-brand transition">Configuration</p>
                    <p class="text-gray-500 text-xs mt-0.5">
                        {{ $config2['ai_model'] ?? 'claude-sonnet-4-6' }}
                        · {{ $config2['capture_scope'] ?? 'All emails' }}
                    </p>
                </div>
                <svg class="w-4 h-4 text-gray-600 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Memory link --}}
            <a href="{{ route('workers.memory', $dep->id) }}"
               class="flex items-center justify-between rounded-xl px-4 py-3.5 transition group" style="background:var(--bg-card);border:1px solid var(--border)">
                <div>
                    <p class="text-white text-sm font-medium group-hover:text-brand transition">Memory</p>
                    <p class="text-gray-500 text-xs mt-0.5">Clients · Contacts · Assets</p>
                </div>
                <svg class="w-4 h-4 text-gray-600 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Rules link --}}
            <a href="{{ route('workers.rules', $dep->id) }}"
               class="flex items-center justify-between rounded-xl px-4 py-3.5 transition group" style="background:var(--bg-card);border:1px solid var(--border)">
                <div>
                    <p class="text-white text-sm font-medium group-hover:text-brand transition">Rules</p>
                    <p class="text-gray-500 text-xs mt-0.5">Processing & action rules</p>
                </div>
                <svg class="w-4 h-4 text-gray-600 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

            {{-- Schema link --}}
            <a href="{{ route('workers.schema', $dep->id) }}"
               class="flex items-center justify-between rounded-xl px-4 py-3.5 transition group" style="background:var(--bg-card);border:1px solid var(--border)">
                <div>
                    <p class="text-white text-sm font-medium group-hover:text-brand transition">Pipeline Schema</p>
                    <p class="text-gray-500 text-xs mt-0.5">Input · Pipeline · Emit</p>
                </div>
                <svg class="w-4 h-4 text-gray-600 group-hover:text-brand transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>

        </div>

    </div>
    @endif {{-- end legacy fallback --}}

    <script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

    async function recoverStuck() {
        const btn = document.getElementById('recover-btn');
        if (!btn) return;
        btn.textContent = 'Recovering…';
        btn.disabled = true;
        try {
            const res = await fetch('{{ route('qa.recover-stuck') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();
            btn.textContent = `✓ ${data.recovered} re-dispatched`;
            btn.style.color = '#86efac';
            btn.style.borderColor = 'rgba(34,197,94,0.4)';
            btn.style.background = 'rgba(34,197,94,0.1)';
        } catch(e) {
            btn.textContent = 'Error — retry';
            btn.disabled = false;
        }
    }

    // ── Pipeline modal polling ──────────────────────────────────────────────
    const WATCH_TX = '{{ $watchTxId ?? '' }}';
    const STAGE_ORDER = ['ingest','read','classify','memory','log','template','draft','push'];
    const STAGE_STATUS_MAP = {
        ingesting:        'ingest',
        reading:          'read',
        classifying:      'classify',
        memory_lookup:    'memory',
        logging:          'log',
        template_select:  'template',
        drafting:         'draft',
        pushing:          'push',
        draft_ready:      'push',
        blocked:          'read',
        sent:             'push',
        approved:         'push',
    };

    function closePipelineModal() {
        document.getElementById('pipeline-modal').classList.add('hidden');
    }

    function setStageState(key, state) { // state: pending | active | done | failed
        const el = document.getElementById('stage-' + key);
        if (!el) return;
        const bubble  = el.querySelector('.stage-bubble');
        const icon    = el.querySelector('.stage-icon');
        const check   = el.querySelector('.stage-check');
        const x       = el.querySelector('.stage-x');
        const label   = el.querySelector('.stage-label');
        const badgeEl = el.querySelector('.stage-badge-text');

        // Reset
        icon.classList.remove('hidden');
        check.classList.add('hidden');
        x.classList.add('hidden');

        if (state === 'done') {
            bubble.style.borderColor = '#34d399';
            bubble.style.background  = 'rgba(52,211,153,0.12)';
            icon.classList.add('hidden');
            check.classList.remove('hidden');
            label.style.color  = '#34d399';
            badgeEl.textContent = 'Done';
            badgeEl.style.color = '#34d399';
        } else if (state === 'active') {
            bubble.style.borderColor = '#a78bfa';
            bubble.style.background  = 'rgba(167,139,250,0.12)';
            icon.style.color = '#a78bfa';
            bubble.style.boxShadow = '0 0 0 4px rgba(167,139,250,0.15)';
            label.style.color  = '#c4b5fd';
            badgeEl.textContent = 'Running…';
            badgeEl.style.color = '#a78bfa';
        } else if (state === 'failed') {
            bubble.style.borderColor = '#f87171';
            bubble.style.background  = 'rgba(248,113,113,0.12)';
            icon.classList.add('hidden');
            x.classList.remove('hidden');
            label.style.color  = '#f87171';
            badgeEl.textContent = 'Failed';
            badgeEl.style.color = '#f87171';
        } else {
            bubble.style.borderColor = '#374151';
            bubble.style.background  = '#111827';
            bubble.style.boxShadow   = '';
            icon.style.color = '#4b5563';
            label.style.color  = '#6b7280';
            badgeEl.textContent = '—';
            badgeEl.style.color = '#374151';
        }

        // Arrow connector after this stage
        const arrowEl = document.getElementById('arrow-' + key);
        if (arrowEl) {
            const path = arrowEl.querySelector('.arrow-path');
            if (path) path.style.stroke = state === 'done' ? '#34d399' : '#2d3748';
        }
    }

    function updatePipelineUI(data) {
        const currentKey = STAGE_STATUS_MAP[data.status] ?? null;
        const currentIdx = currentKey ? STAGE_ORDER.indexOf(currentKey) : -1;

        STAGE_ORDER.forEach((key, idx) => {
            if (data.failed && idx === currentIdx) {
                setStageState(key, 'failed');
            } else if (idx < currentIdx || (data.done && !data.failed)) {
                setStageState(key, 'done');
            } else if (idx === currentIdx) {
                setStageState(key, data.failed ? 'failed' : 'active');
            } else {
                setStageState(key, 'pending');
            }
        });

        const overall  = document.getElementById('pipeline-overall');
        const spinner  = document.getElementById('pipeline-spinner');
        const bar      = document.getElementById('pipeline-status-bar');

        if (data.done && !data.failed) {
            spinner.classList.add('hidden');
            overall.textContent    = '✓ Complete — draft ready in Gmail';
            overall.style.color    = '#34d399';
            bar.style.borderColor  = 'rgba(52,211,153,0.3)';
            bar.style.background   = 'rgba(52,211,153,0.06)';
            setTimeout(() => {
                closePipelineModal();
                const url = new URL(window.location.href);
                url.searchParams.delete('watch');
                window.location.href = url.toString();
            }, 3500);
        } else if (data.failed) {
            spinner.classList.add('hidden');
            overall.textContent   = '✕ Pipeline failed — check the Transactions log for details';
            overall.style.color   = '#f87171';
            bar.style.borderColor = 'rgba(248,113,113,0.3)';
            bar.style.background  = 'rgba(248,113,113,0.06)';
        } else {
            const labels = { reading:'Reading email…', classifying:'Classifying…', memory_lookup:'Looking up memory…', logging:'Logging transaction…', template_select:'Selecting template…', drafting:'Drafting email with AI…', pushing:'Pushing to Gmail…' };
            overall.textContent = labels[data.status] ?? 'Processing…';
        }
    }

    if (WATCH_TX) {
        const statusUrl = '{{ url('/transactions') }}/' + WATCH_TX + '/status';

        function poll() {
            fetch(statusUrl, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    updatePipelineUI(data);
                    if (!data.done) setTimeout(poll, 2000);
                })
                .catch(() => setTimeout(poll, 3000));
        }

        poll();
    }

    // Highlight selected model card on radio change
    document.querySelectorAll('input[name="ai_model"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('input[name="ai_model"]').forEach(r => {
                const card = r.closest('label').querySelector('div');
                if (r.checked) {
                    // styling is handled server-side on next load; JS just re-submits visually
                }
            });
        });
    });
    </script>

    @if($isMultiCredential)
    <script>
    async function submitIdea(e) {
        e.preventDefault();
        const btn  = document.getElementById('idea-submit-btn');
        const fb   = document.getElementById('idea-feedback');
        const text = document.getElementById('idea-text').value.trim();
        const channels = [...document.querySelectorAll('input[name="target_channels[]"]:checked')].map(c => c.value);

        if (!text || text.length < 10) {
            fb.textContent = 'Please write at least 10 characters.';
            fb.style.color = 'var(--text-muted)';
            fb.classList.remove('hidden');
            return;
        }
        if (channels.length === 0) {
            fb.textContent = 'Select at least one channel.';
            fb.style.color = 'var(--text-muted)';
            fb.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Submitting…';
        fb.classList.add('hidden');

        try {
            const res = await fetch('{{ route('nux.submit.idea', $dep->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ idea_text: text, target_channels: channels }),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                document.getElementById('idea-text').value = '';
                fb.textContent = data.message;
                fb.style.color = '#4ade80';
                fb.classList.remove('hidden');
                btn.textContent = 'Submit Idea';
                btn.disabled = false;
                // Open pipeline watcher
                if (data.tx_id) {
                    window.location.href = window.location.pathname + '?watch=' + data.tx_id;
                }
            } else {
                fb.textContent = data.error ?? 'Something went wrong.';
                fb.style.color = '#f87171';
                fb.classList.remove('hidden');
                btn.textContent = 'Submit Idea';
                btn.disabled = false;
            }
        } catch (err) {
            fb.textContent = 'Network error — please try again.';
            fb.style.color = '#f87171';
            fb.classList.remove('hidden');
            btn.textContent = 'Submit Idea';
            btn.disabled = false;
        }
    }
    </script>
    @endif

</x-app-layout>

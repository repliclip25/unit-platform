<x-app-layout title="AVA's Desk">

@php
$statusColors = [
    'draft_ready'   => ['bg' => 'rgba(var(--accent-rgb),.15)', 'text' => 'var(--accent-text)', 'label' => 'Draft ready'],
    'approved'      => ['bg' => 'rgba(34,197,94,.15)',         'text' => '#16a34a',             'label' => 'Approved'],
    'sent'          => ['bg' => 'rgba(34,197,94,.15)',         'text' => '#16a34a',             'label' => 'Sent'],
    'failed'        => ['bg' => 'rgba(239,68,68,.15)',         'text' => '#dc2626',             'label' => 'Failed'],
    'rejected'      => ['bg' => 'rgba(239,68,68,.15)',         'text' => '#dc2626',             'label' => 'Rejected'],
    'reading'       => ['bg' => 'rgba(99,102,241,.15)',        'text' => '#6366f1',             'label' => 'Reading'],
    'classifying'   => ['bg' => 'rgba(99,102,241,.15)',        'text' => '#6366f1',             'label' => 'Classifying'],
    'drafting'      => ['bg' => 'rgba(99,102,241,.15)',        'text' => '#6366f1',             'label' => 'Drafting'],
    'ingesting'     => ['bg' => 'rgba(99,102,241,.15)',        'text' => '#6366f1',             'label' => 'Ingesting'],
    'dismissed'     => ['bg' => 'rgba(156,163,175,.15)',       'text' => 'var(--text-muted)',   'label' => 'Dismissed'],
];
$activityIcons = [
    'draft_ready'  => ['icon' => '✦', 'color' => '#f1d362'],
    'approved'     => ['icon' => '✓', 'color' => '#22c55e'],
    'sent'         => ['icon' => '✈', 'color' => '#22c55e'],
    'failed'       => ['icon' => '!', 'color' => '#ef4444'],
    'reading'      => ['icon' => '👁', 'color' => '#6366f1'],
    'drafting'     => ['icon' => '✏', 'color' => '#8b5cf6'],
    'classifying'  => ['icon' => '⋯', 'color' => '#f59e0b'],
];
@endphp

{{-- ═══ TWO-COLUMN LAYOUT: left content + right rail ═══ --}}
<div style="display:grid;grid-template-columns:1fr 280px;gap:24px;align-items:start">

{{-- ═══ LEFT: main content ═══ --}}
<div style="min-width:0">

    {{-- ── Hero card ─────────────────────────────────────────────────────── --}}
    <div style="position:relative;height:240px;border-radius:18px;overflow:hidden;margin-bottom:20px;background:var(--bg-card);border:1px solid var(--border)">
        {{-- Cover image --}}
        @if($coverImg)
        <img src="{{ $coverImg }}" alt="AVA"
             style="width:100%;height:100%;object-fit:cover;object-position:center top;display:block;opacity:.85">
        @else
        <div style="width:100%;height:100%;background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%)"></div>
        @endif
        <div style="position:absolute;inset:0;background:linear-gradient(to right,rgba(0,0,0,.7) 0%,rgba(0,0,0,.2) 60%,rgba(0,0,0,0) 100%)"></div>

        {{-- Identity overlay --}}
        <div style="position:absolute;top:0;left:0;bottom:0;padding:24px 28px;display:flex;flex-direction:column;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
                <span style="width:7px;height:7px;border-radius:50%;background:#22c55e;display:inline-block;animation:adot 1.4s ease infinite"></span>
                <span style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.8)">ON SHIFT</span>
                <span style="font-size:10px;color:rgba(255,255,255,.5);margin-left:4px">{{ now()->format('g:i A') }}</span>
            </div>
            <div>
                <h1 style="font-size:2rem;font-weight:900;color:#fff;letter-spacing:-.03em;line-height:1.1;margin-bottom:6px">AVA's Desk</h1>
                <p style="font-size:13px;color:rgba(255,255,255,.7);font-weight:500;margin-bottom:0">Renewal Specialist — Ava is protecting your renewals and building stronger customer relationships.</p>
            </div>
        </div>

        {{-- Current task bubble --}}
        @if($currentTask)
        @php
            $taskLabel = match($currentTask->status) {
                'reading','ingesting','classifying' => 'Reading incoming email',
                'drafting','pushing'                => 'Drafting renewal reply',
                'draft_ready'                       => 'Reply awaiting approval',
                'approved','sent'                   => 'Reply sent',
                default                             => ucfirst(str_replace('_', ' ', $currentTask->status)),
            };
            $classify = json_decode($currentTask->classify_output ?? '{}', true);
            $taskClient = $classify['client'] ?? $classify['sender_name'] ?? '';
        @endphp
        <div style="position:absolute;bottom:20px;right:20px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:14px 16px;min-width:220px;box-shadow:0 8px 32px rgba(0,0,0,.3)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted)">Current Task</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--text-faint)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <p style="font-size:13px;font-weight:700;color:var(--text-primary);margin-bottom:3px">{{ $taskLabel }}</p>
            @if($taskClient)
            <p style="font-size:11px;color:var(--text-muted);margin-bottom:10px">Customer: {{ $taskClient }}</p>
            @else
            <div style="margin-bottom:10px"></div>
            @endif
            <div style="height:4px;background:var(--bg-raised);border-radius:99px;overflow:hidden">
                <div style="height:100%;border-radius:99px;background:var(--accent);width:{{ in_array($currentTask->status, ['approved','sent','draft_ready']) ? '100' : '67' }}%"></div>
            </div>
            <p style="font-size:10px;color:var(--text-faint);margin-top:4px;text-align:right">{{ in_array($currentTask->status, ['approved','sent','draft_ready']) ? '100' : '67' }}%</p>
        </div>
        @endif
    </div>

    {{-- ── Pipeline stats row ───────────────────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
        @foreach([
            ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => '#6366f1', 'val' => $incomingCount,   'label' => 'New emails',      'sub' => 'Incoming'],
            ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'color' => '#f59e0b', 'val' => $inProgressCount, 'label' => 'Working on it',   'sub' => 'In Progress'],
            ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => '#8b5cf6', 'val' => $waitingCount,    'label' => 'Waiting approval',  'sub' => 'Waiting'],
            ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => '#22c55e', 'val' => $completedCount,  'label' => 'Completed today',  'sub' => 'Completed'],
        ] as $stat)
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:12px">
            <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:{{ $stat['color'] }}1a">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $stat['color'] }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="{{ $stat['icon'] }}"/>
                </svg>
            </div>
            <div>
                <div style="font-size:22px;font-weight:900;color:var(--text-primary);line-height:1">{{ $stat['val'] }}</div>
                <div style="font-size:10.5px;color:var(--text-muted);margin-top:2px">{{ $stat['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Bottom two-col: Live Activity + Approvals ───────────────────── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        {{-- Live Activity --}}
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:20px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                <span style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--text-primary)">Live Activity</span>
                <span style="display:flex;align-items:center;gap:5px;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#16a34a;background:rgba(34,197,94,.12);border-radius:99px;padding:3px 8px">
                    <span style="width:5px;height:5px;border-radius:50%;background:#22c55e;display:inline-block;animation:adot 1.4s ease infinite"></span>
                    On Shift
                </span>
            </div>
            @if($activity->isEmpty())
            <p style="font-size:12px;color:var(--text-faint);text-align:center;padding:24px 0">No activity yet today</p>
            @else
            <div style="position:relative;padding-left:20px">
                <div style="position:absolute;left:7px;top:6px;bottom:6px;width:1px;background:var(--border)"></div>
                @foreach($activity as $tx)
                @php
                    $ai = $activityIcons[$tx->status] ?? ['icon' => '·', 'color' => 'var(--text-faint)'];
                    $cl = json_decode($tx->classify_output ?? '{}', true);
                    $txClient = $cl['client'] ?? $cl['sender_name'] ?? '';
                    $txLabel = match($tx->status) {
                        'draft_ready'  => 'Reply ready for approval',
                        'approved'     => 'Reply approved',
                        'sent'         => 'Reply sent',
                        'failed'       => 'Pipeline failed',
                        'reading'      => 'Reading email',
                        'drafting'     => 'Drafting reply',
                        'classifying'  => 'Classifying email',
                        default        => ucfirst(str_replace('_',' ',$tx->status)),
                    };
                @endphp
                <div style="display:flex;gap:10px;align-items:flex-start;margin-bottom:12px;position:relative">
                    <div style="position:absolute;left:-20px;top:3px;width:14px;height:14px;border-radius:50%;background:{{ $ai['color'] }}22;display:flex;align-items:center;justify-content:center;font-size:7px;color:{{ $ai['color'] }};border:1px solid {{ $ai['color'] }}44;font-weight:700">{{ $ai['icon'] }}</div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ $txLabel }}</div>
                        @if($txClient)
                        <div style="font-size:11px;color:var(--text-muted)">{{ $txClient }}</div>
                        @endif
                    </div>
                    <span style="font-size:10px;color:var(--text-faint);white-space:nowrap;flex-shrink:0">{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</span>
                </div>
                @endforeach
            </div>
            @endif
            <a href="{{ route('transactions') }}" style="display:block;margin-top:8px;font-size:11px;color:var(--text-faint);text-decoration:none;font-weight:600" class="hover:opacity-80">View all activity →</a>
        </div>

        {{-- Approvals --}}
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:20px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--text-primary)">Approvals</span>
                    @if($waitingCount > 0)
                    <span style="font-size:10px;font-weight:700;background:var(--accent);color:#000;border-radius:99px;padding:1px 7px">{{ $waitingCount }}</span>
                    @endif
                </div>
                <a href="{{ route('transactions', ['filter' => 'draft_ready']) }}" style="font-size:11px;color:var(--text-faint);text-decoration:none;font-weight:600">View all →</a>
            </div>
            @if($approvals->isEmpty())
            <p style="font-size:12px;color:var(--text-faint);text-align:center;padding:24px 0">Nothing awaiting approval</p>
            @else
            <div style="display:flex;flex-direction:column;gap:10px">
                @foreach($approvals as $tx)
                @php
                    $cl = json_decode($tx->classify_output ?? '{}', true);
                    $dr = json_decode($tx->draft_output   ?? '{}', true);
                    $apClient  = $cl['client'] ?? $cl['sender_name'] ?? 'Unknown';
                    $apSubject = $dr['subject'] ?? $cl['subject'] ?? 'Renewal Response';
                    $apValue   = $cl['contract_value'] ?? $cl['renewal_value'] ?? null;
                    $apPlan    = $cl['plan'] ?? $cl['product'] ?? null;
                @endphp
                <div style="padding:12px 14px;border-radius:12px;background:var(--bg-surface);border:1px solid var(--border)">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px">
                        <div style="min-width:0">
                            <p style="font-size:12.5px;font-weight:700;color:var(--text-primary);margin-bottom:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $apClient }}</p>
                            <p style="font-size:11px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $apSubject }}</p>
                            @if($apValue)
                            <p style="font-size:11px;color:var(--text-faint);margin-top:2px">{{ $apPlan ? $apPlan . ' — ' : '' }}{{ $apValue }}</p>
                            @endif
                        </div>
                        <span style="font-size:9.5px;font-weight:700;letter-spacing:.04em;white-space:nowrap;padding:3px 8px;border-radius:99px;background:rgba(var(--accent-rgb),.15);color:var(--accent-text);flex-shrink:0">Draft ready</span>
                    </div>
                    <div style="display:flex;gap:6px">
                        <form method="POST" action="{{ route('transactions.decide', $tx->id) }}" style="flex:1">
                            @csrf
                            <input type="hidden" name="decision" value="approve">
                            <button type="submit" style="width:100%;padding:7px;border-radius:8px;background:var(--text-primary);color:var(--bg-card);border:none;font-size:11.5px;font-weight:700;cursor:pointer;font-family:inherit">Approve</button>
                        </form>
                        <a href="{{ route('transactions.show', $tx->id) }}" style="flex:1;padding:7px;border-radius:8px;background:transparent;color:var(--text-secondary);border:1.5px solid var(--border);font-size:11.5px;font-weight:700;cursor:pointer;font-family:inherit;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center">Edit</a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            <a href="{{ route('transactions', ['filter' => 'draft_ready']) }}" style="display:block;margin-top:10px;font-size:11px;color:var(--text-faint);text-decoration:none;font-weight:600">
                {{ $waitingCount }} draft{{ $waitingCount === 1 ? '' : 's' }} waiting for your approval →
            </a>
        </div>

    </div>

</div>
{{-- /left --}}

{{-- ═══ RIGHT RAIL ═══ --}}
<div style="display:flex;flex-direction:column;gap:14px">

    {{-- Worker identity --}}
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:18px">
        <p style="font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-faint);margin-bottom:10px">First Assignment</p>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
            @if($profileImg)
            <img src="{{ $profileImg }}" alt="AVA" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1.5px solid var(--border);flex-shrink:0">
            @else
            <div style="width:40px;height:40px;border-radius:10px;background:var(--bg-raised);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">🤖</div>
            @endif
            <div>
                <p style="font-size:16px;font-weight:900;color:var(--text-primary);letter-spacing:-.02em">AVA</p>
                <p style="font-size:11px;color:var(--text-muted)">Renewal Specialist</p>
            </div>
        </div>

        {{-- Work status --}}
        <p style="font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-faint);margin-bottom:6px">Work Status</p>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <div style="display:flex;align-items:center;gap:6px">
                <span style="width:7px;height:7px;border-radius:50%;background:{{ $dep->status === 'active' ? '#22c55e' : '#f59e0b' }};display:inline-block{{ $dep->status === 'active' ? ';animation:adot 1.4s ease infinite' : '' }}"></span>
                <span style="font-size:13px;font-weight:700;color:{{ $dep->status === 'active' ? '#22c55e' : '#f59e0b' }}">{{ $workStatus }}</span>
            </div>
            <span style="font-size:11px;color:var(--text-faint)">Since {{ \Carbon\Carbon::parse($dep->updated_at)->format('g:i A') }}</span>
        </div>

        {{-- Current task --}}
        @if($currentTask)
        @php
            $railLabel = match($currentTask->status) {
                'reading','ingesting','classifying' => 'Reading incoming email',
                'drafting','pushing'                => 'Drafting renewal reply',
                'draft_ready'                       => 'Reply awaiting approval',
                'approved','sent'                   => 'All caught up',
                default                             => ucfirst(str_replace('_',' ',$currentTask->status)),
            };
            $rc = json_decode($currentTask->classify_output ?? '{}', true);
            $railClient = $rc['client'] ?? $rc['sender_name'] ?? '';
        @endphp
        <p style="font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-faint);margin-bottom:6px">Current Task</p>
        <p style="font-size:13px;font-weight:700;color:var(--text-primary);margin-bottom:2px">{{ $railLabel }}</p>
        @if($railClient)
        <p style="font-size:11px;color:var(--text-muted);margin-bottom:8px">Customer: {{ $railClient }}</p>
        @else
        <div style="margin-bottom:8px"></div>
        @endif
        <div style="height:4px;background:var(--bg-raised);border-radius:99px;margin-bottom:4px;overflow:hidden">
            <div style="height:100%;border-radius:99px;background:var(--accent);width:{{ in_array($currentTask->status, ['approved','sent','draft_ready']) ? '100' : '67' }}%"></div>
        </div>
        <p style="font-size:10px;color:var(--text-faint);text-align:right">{{ in_array($currentTask->status, ['approved','sent','draft_ready']) ? '100' : '67' }}%</p>
        @endif
    </div>

    {{-- Today's Impact --}}
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:18px">
        <p style="font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-faint);margin-bottom:12px">Today's Impact</p>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(var(--accent-rgb),.12);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--accent-text)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            </div>
            <div>
                <p style="font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-faint)">Renewals Managed</p>
                <p style="font-size:22px;font-weight:900;color:var(--text-primary);line-height:1.1">{{ $incomingCount }}</p>
                <p style="font-size:10.5px;color:var(--text-muted)">{{ $completedCount }} completed today</p>
            </div>
        </div>
    </div>

    {{-- Memory Access --}}
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:18px">
        <p style="font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-faint);margin-bottom:12px">Memory Access</p>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
            <div style="width:30px;height:30px;border-radius:8px;background:rgba(139,92,246,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
            </div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                    <span style="font-size:12px;font-weight:600;color:var(--text-secondary)">Memory</span>
                    <span style="font-size:12px;font-weight:700;color:var(--text-primary)">{{ $clientCount }} clients</span>
                </div>
                <div style="height:4px;background:var(--bg-raised);border-radius:99px;overflow:hidden">
                    <div style="height:100%;border-radius:99px;background:#8b5cf6;width:{{ min(100, $clientCount * 20) }}%"></div>
                </div>
            </div>
        </div>
        <p style="font-size:11px;color:var(--text-faint)">Ava knows {{ $clientCount }} {{ $clientCount === 1 ? 'client' : 'clients' }}.</p>

        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)">
            <p style="font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-faint);margin-bottom:8px">Memory & Responsibility</p>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                <span style="font-size:12.5px;font-weight:700;color:var(--text-primary)">{{ $clientCount }} / {{ max(5, $clientCount + 3) }} Clients</span>
                <span style="font-size:10px;color:var(--text-muted)">{{ $clientCount < 3 ? 'Light Workload' : ($clientCount < 8 ? 'Medium Workload' : 'Heavy Workload') }}</span>
            </div>
            <div style="height:4px;background:var(--bg-raised);border-radius:99px;overflow:hidden">
                <div style="height:100%;border-radius:99px;background:var(--accent);width:{{ min(100, ($clientCount / max(5, $clientCount + 3)) * 100) }}%"></div>
            </div>
        </div>
    </div>

    {{-- AVA's Note --}}
    @php
        $avaTxCount = \Illuminate\Support\Facades\DB::table('transactions')->where('deployment_id', $depId)->count();
        $avaNote = $avaTxCount === 0
            ? "Ready and standing by, {$firstName}. Give me something to work on."
            : ($waitingCount > 0
                ? "I've drafted {$waitingCount} " . ($waitingCount === 1 ? 'reply' : 'replies') . " for you to review. Let me know what you think."
                : "All caught up. I'll flag anything that needs your attention.");
    @endphp
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:18px">
        <p style="font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--text-faint);margin-bottom:10px">AVA's Note</p>
        <div style="display:flex;gap:3px;margin-bottom:8px">
            <svg width="12" height="10" viewBox="0 0 24 20" fill="var(--text-faint)" style="flex-shrink:0;margin-top:2px"><path d="M0 0h10v10H5C5 15 7 18 10 20H7C3 18 0 15 0 10V0zm14 0h10v10h-5c0 5 2 8 5 10h-3c-4-2-7-5-7-10V0z"/></svg>
        </div>
        <p style="font-size:12.5px;color:var(--text-secondary);line-height:1.65;font-style:italic">{{ $avaNote }}</p>
        <p style="font-size:12px;font-weight:700;color:var(--text-primary);margin-top:8px">— Ava</p>
    </div>

    {{-- Quick links --}}
    <div style="display:flex;flex-direction:column;gap:6px">
        <a href="{{ route('transactions', ['filter' => 'draft_ready']) }}"
           style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:10px;background:var(--bg-card);border:1px solid var(--border);text-decoration:none;font-size:12px;font-weight:600;color:var(--text-secondary)">
            Review Drafts
            @if($waitingCount > 0)
            <span style="font-size:10px;font-weight:700;background:var(--accent);color:#000;border-radius:99px;padding:1px 7px">{{ $waitingCount }}</span>
            @else
            <span>→</span>
            @endif
        </a>
        <a href="{{ route('memory') }}"
           style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:10px;background:var(--bg-card);border:1px solid var(--border);text-decoration:none;font-size:12px;font-weight:600;color:var(--text-secondary)">
            Update Memory <span style="color:var(--text-faint)">→</span>
        </a>
        <a href="{{ route('hire.ava.onshift') }}"
           style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-radius:10px;background:var(--bg-card);border:1px solid var(--border);text-decoration:none;font-size:12px;font-weight:600;color:var(--text-secondary)">
            Run Fast Track <span style="color:var(--text-faint)">→</span>
        </a>
    </div>

</div>
{{-- /right rail --}}

</div>

<style>
@keyframes adot { 0%,100%{opacity:1} 50%{opacity:.3} }
</style>

<x-self-learn page="ava-desk" />

</x-app-layout>

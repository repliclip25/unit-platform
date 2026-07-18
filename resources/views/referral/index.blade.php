<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Refer & Earn — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{min-height:100%}
body{font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased}

:root,[data-theme="dark"]{
  --db-bg:#0D0D0D; --db-card:#1A1A1A; --db-text:#F5F5F5; --db-text-muted:#9CA3AF;
  --db-border:rgba(255,255,255,.14); --db-chip:#262626;
  --db-invert-bg:#F5F5F5; --db-invert-text:#0D0D0D;
}
[data-theme="light"]{
  --db-bg:#F4F3F1; --db-card:#ffffff; --db-text:#0D0D0D; --db-text-muted:#9CA3AF;
  --db-border:#E5E7EB; --db-chip:#ECEAE6;
  --db-invert-bg:#0D0D0D; --db-invert-text:#ffffff;
}
body{background:var(--db-bg);color:var(--db-text)}

.rf-topbar{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-bottom:1px solid var(--db-border)}
.rf-logo{font-size:20px;font-weight:900;letter-spacing:-.04em;color:var(--db-text);text-decoration:none}
.rf-topbar-right{display:flex;align-items:center;gap:12px}
.rf-back{font-size:12px;font-weight:600;color:var(--db-text-muted);text-decoration:none}
.rf-back:hover{color:var(--db-text)}
.rf-theme-toggle{width:36px;height:20px;border-radius:10px;border:none;cursor:pointer;position:relative;background:var(--db-chip)}
.rf-theme-toggle::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:var(--db-invert-bg);transition:transform .2s ease}
[data-theme="dark"] .rf-theme-toggle::after{transform:translateX(16px)}

.rf-wrap{max-width:720px;margin:0 auto;padding:32px 20px 60px}
.rf-h1{font-size:1.7rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text);margin-bottom:4px}
.rf-sub{font-size:13.5px;color:var(--db-text-muted);margin-bottom:24px}

.rf-card{background:var(--db-card);border:1px solid var(--db-border);border-radius:16px;padding:20px;margin-bottom:16px}

.rf-stats{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px}
.rf-stat{background:var(--db-card);border:1px solid var(--db-border);border-radius:16px;padding:18px 14px;text-align:center}
.rf-stat-num{font-size:1.7rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.rf-stat-label{font-size:10px;color:var(--db-text-muted);text-transform:uppercase;letter-spacing:.06em;margin-top:4px}

.rf-progress-track{width:100%;height:8px;border-radius:99px;background:var(--db-chip);overflow:hidden}
.rf-progress-fill{height:100%;border-radius:99px;background:#F5C518}

.rf-tier-gold{display:flex;align-items:center;gap:12px}

.rf-link-label{font-size:12.5px;color:var(--db-text-muted);margin-bottom:10px;font-weight:600}
.rf-link-row{display:flex;align-items:center;gap:10px}
.rf-link-box{flex:1;display:flex;align-items:center;min-width:0;padding:11px 14px;border-radius:10px;border:1px solid var(--db-border);background:var(--db-bg)}
.rf-link-text{font-size:12.5px;font-family:monospace;color:var(--db-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rf-copy-btn{flex-shrink:0;font-size:12.5px;font-weight:700;padding:11px 18px;border-radius:10px;border:none;background:var(--db-invert-bg);color:var(--db-invert-text);cursor:pointer;font-family:inherit}
.rf-code{font-size:11px;color:var(--db-text-muted);margin-top:8px}
.rf-code span{font-family:monospace;color:var(--db-text)}

.rf-card-title{font-size:13.5px;font-weight:700;color:var(--db-text);margin-bottom:14px}

.rf-share-item{display:flex;align-items:center;gap:14px;padding:12px;border-radius:10px;border:1px solid var(--db-border);text-decoration:none;margin-bottom:8px}
.rf-share-item:last-child{margin-bottom:0}
.rf-share-item:hover{background:var(--db-chip)}
.rf-share-icon{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.rf-share-text{flex:1;min-width:0}
.rf-share-title{font-size:12.5px;font-weight:600;color:var(--db-text)}
.rf-share-sub{font-size:11px;color:var(--db-text-muted);margin-top:1px}
.rf-share-item svg.rf-chev{stroke:var(--db-text-muted);flex-shrink:0}

.rf-table{width:100%;font-size:12.5px;border-collapse:collapse}
.rf-table th{text-align:left;padding:10px 4px;color:var(--db-text-muted);font-size:10px;text-transform:uppercase;letter-spacing:.05em;font-weight:600;border-bottom:1px solid var(--db-border)}
.rf-table td{padding:10px 4px;border-bottom:1px solid var(--db-border);color:var(--db-text)}
.rf-table tr:last-child td{border-bottom:none}
.rf-status-badge{font-size:10.5px;padding:2px 8px;border-radius:99px;font-weight:600}
.rf-empty{text-align:center;padding:28px 0;font-size:12.5px;color:var(--db-text-muted)}

.rf-step{display:flex;gap:12px;margin-bottom:14px}
.rf-step:last-child{margin-bottom:0}
.rf-step-num{width:24px;height:24px;border-radius:50%;background:var(--db-chip);color:var(--db-text);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0}
.rf-step-title{font-size:12.5px;font-weight:600;color:var(--db-text)}
.rf-step-sub{font-size:11px;color:var(--db-text-muted);margin-top:2px}
</style>
<script>
(function () {
  var saved = localStorage.getItem('unit-theme-v2') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();
</script>
</head>
<body>

<div class="rf-topbar">
  <a href="{{ url('/') }}" class="rf-logo">UNIT</a>
  <div class="rf-topbar-right">
    <a href="{{ route('dashboard') }}" class="rf-back">← Back to Dashboard</a>
    <button type="button" class="rf-theme-toggle" id="theme-toggle" title="Toggle dark/light mode" aria-label="Toggle theme"></button>
  </div>
</div>

<div class="rf-wrap">
  <div class="rf-h1">Refer & Earn</div>
  <div class="rf-sub">Invite colleagues to UNIT. Earn $25 credit when they go paid.</div>

  <div class="rf-stats">
    <div class="rf-stat"><div class="rf-stat-num">{{ $referral->signups }}</div><div class="rf-stat-label">Signed up</div></div>
    <div class="rf-stat"><div class="rf-stat-num" style="color:#F5C518">{{ $referral->converted }}</div><div class="rf-stat-label">Converted</div></div>
    <div class="rf-stat"><div class="rf-stat-num">${{ number_format($referral->balance, 0) }}</div><div class="rf-stat-label">Credit balance</div></div>
  </div>

  @if($referral->nextTier)
  <div class="rf-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
      <span style="font-size:12.5px;color:var(--db-text-muted)">Progress to <strong style="color:var(--db-text)">{{ $referral->tierLabel }}</strong></span>
      <span style="font-size:12px;color:var(--db-text-muted)">{{ $referral->converted }} / {{ $referral->nextTier }} conversions</span>
    </div>
    <div class="rf-progress-track"><div class="rf-progress-fill" style="width:{{ $referral->tierPct }}%"></div></div>
  </div>
  @else
  <div class="rf-card rf-tier-gold">
    <span style="font-size:26px">🏆</span>
    <div>
      <div style="font-size:13px;font-weight:700;color:#F5C518">Gold Referrer</div>
      <div style="font-size:12px;color:var(--db-text-muted)">10+ conversions — you're in the top tier.</div>
    </div>
  </div>
  @endif

  <div class="rf-card">
    <div class="rf-link-label">Your referral link</div>
    <div class="rf-link-row">
      <div class="rf-link-box"><span class="rf-link-text">{{ $referralUrl }}</span></div>
      <button type="button" class="rf-copy-btn" id="rf-copy-btn" data-url="{{ $referralUrl }}">Copy Link</button>
    </div>
    <div class="rf-code">Code: <span>{{ $referralCode }}</span></div>
  </div>

  <div class="rf-card">
    <div class="rf-card-title">Best ways to refer</div>

    <a href="mailto:?subject=Tool that automates license renewals&body=Hey%2C%0A%0AI've been using UNIT Platform to automate our license renewal workflow — it handles reading the email%2C looking up the client%2C and drafting the response automatically. Saves a ton of time.%0A%0AThought you might want to try it. Use my link and you'll get double the usual free trial%3A%0A%0A{{ $referralUrl }}%0A%0A" class="rf-share-item">
      <div class="rf-share-icon" style="background:var(--db-chip)">
        <svg width="16" height="16" fill="none" stroke="var(--db-text)" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
      </div>
      <div class="rf-share-text"><div class="rf-share-title">Email a colleague</div><div class="rf-share-sub">Works best — personal and specific to their workflow.</div></div>
      <svg class="rf-chev" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>

    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($referralUrl) }}" target="_blank" class="rf-share-item">
      <div class="rf-share-icon" style="background:rgba(10,102,194,.12)">
        <svg width="16" height="16" fill="#0a66c2" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
      </div>
      <div class="rf-share-text"><div class="rf-share-title">Share on LinkedIn</div><div class="rf-share-sub">Great for reaching procurement and compliance teams.</div></div>
      <svg class="rf-chev" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>

    <a href="https://twitter.com/intent/tweet?text={{ urlencode('I use UNIT Platform to automate license renewal workflows — it reads the email, looks up the client, and drafts the response. Use my link for double the free trial: ' . $referralUrl) }}" target="_blank" class="rf-share-item">
      <div class="rf-share-icon" style="background:var(--db-chip)">
        <svg width="16" height="16" fill="var(--db-text)" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.261 5.632zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
      </div>
      <div class="rf-share-text"><div class="rf-share-title">Post on X</div><div class="rf-share-sub">Good for visibility in your professional network.</div></div>
      <svg class="rf-chev" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
  </div>

  <div class="rf-card">
    <div class="rf-card-title">Recent activity</div>
    @if($credits->count() > 0)
    <table class="rf-table">
      <thead><tr><th>Referred user</th><th>Status</th><th style="text-align:right">Credit</th><th style="text-align:right">Date</th></tr></thead>
      <tbody>
        @foreach($credits as $credit)
        <tr>
          <td>{{ $credit->referred_email ?? '—' }}</td>
          <td>
            @if($credit->event === 'paid_conversion')
              <span class="rf-status-badge" style="background:rgba(34,197,94,.15);color:#22c55e">Converted</span>
            @elseif($credit->event === 'signup')
              <span class="rf-status-badge" style="background:rgba(245,197,24,.15);color:#F5C518">Signed up</span>
            @else
              <span class="rf-status-badge" style="background:var(--db-chip);color:var(--db-text-muted)">{{ ucfirst($credit->event) }}</span>
            @endif
          </td>
          <td style="text-align:right;font-family:monospace">${{ number_format($credit->credit_usd ?? 0, 0) }}</td>
          <td style="text-align:right;color:var(--db-text-muted)">{{ \Carbon\Carbon::parse($credit->created_at)->format('M j, Y') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div class="rf-empty">No referrals yet. Share your link to get started.</div>
    @endif
  </div>

  <div class="rf-card">
    <div class="rf-card-title">How it works</div>
    <div class="rf-step">
      <div class="rf-step-num">1</div>
      <div><div class="rf-step-title">Share your link</div><div class="rf-step-sub">Send it to anyone who does license renewal or compliance work.</div></div>
    </div>
    <div class="rf-step">
      <div class="rf-step-num">2</div>
      <div><div class="rf-step-title">They sign up and get 20 free transactions</div><div class="rf-step-sub">Double the usual free trial — a meaningful incentive to try it.</div></div>
    </div>
    <div class="rf-step">
      <div class="rf-step-num">3</div>
      <div><div class="rf-step-title">You earn $25 credit when they subscribe</div><div class="rf-step-sub">Applied to your UNIT account automatically. No cap on earnings.</div></div>
    </div>
  </div>

</div>

<script>
document.getElementById('theme-toggle').addEventListener('click', function () {
  var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('unit-theme-v2', next);
});

var copyBtn = document.getElementById('rf-copy-btn');
copyBtn.addEventListener('click', function () {
  navigator.clipboard.writeText(copyBtn.dataset.url).then(function () {
    var original = copyBtn.textContent;
    copyBtn.textContent = 'Copied ✓';
    setTimeout(function () { copyBtn.textContent = original; }, 2500);
  });
});
</script>
</body>
</html>

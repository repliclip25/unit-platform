<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $tx->tx_id }} — UNIT</title>
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
button,select,input,textarea{outline:none}
button:focus,select:focus{outline:none;box-shadow:none}
html,body{height:100%;overflow:hidden}

:root,[data-theme="dark"]{
  --db-bg:#0D0D0D; --db-card:#1A1A1A; --db-text:#F5F5F5; --db-text-muted:#9CA3AF;
  --db-border:rgba(255,255,255,.14); --db-chip:#262626;
  --db-invert-bg:#F5F5F5; --db-invert-text:#0D0D0D;
}
[data-theme="light"]{
  --db-bg:#F4F3F1; --db-card:#ffffff; --db-text:#0D0D0D; --db-text-muted:#6B7280;
  --db-border:#E5E7EB; --db-chip:#ECEAE6;
  --db-invert-bg:#0D0D0D; --db-invert-text:#ffffff;
}

body{font-family:'Inter',sans-serif;background:var(--db-bg);color:var(--db-text);-webkit-font-smoothing:antialiased}

/* ── SHELL (identical to /desk/{slug}, /workers/{slug}/overview, /memory, /templates, /rules, /fast-track, /connect, /billing, /transactions) ── */
.ob-shell{display:flex;flex-direction:column;height:100vh;overflow:hidden}
.ob-topbar{background:var(--db-bg);display:flex;align-items:center;justify-content:space-between;padding:0 24px;height:52px;flex-shrink:0}
.ob-topbar-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.ob-topbar-name{font-size:13.5px;font-weight:700;color:var(--db-text)}
.ob-topbar-email{font-size:12px;color:var(--db-text-muted)}
.ob-topbar-right{display:flex;align-items:center;gap:12px}
.ob-token-badge{font-size:11px;font-weight:600;color:var(--db-text-muted);background:var(--db-chip);border-radius:5px;padding:2px 7px;white-space:nowrap}
.ob-theme-toggle{width:36px;height:20px;border-radius:10px;border:none;cursor:pointer;position:relative;background:var(--db-chip)}
.ob-theme-toggle::after{content:'';position:absolute;top:3px;left:3px;width:14px;height:14px;border-radius:50%;background:var(--db-invert-bg);transition:transform .2s ease}
[data-theme="dark"] .ob-theme-toggle::after{transform:translateX(16px)}
.ob-menu-wrap{position:relative}
.ob-hamburger{width:32px;height:32px;border-radius:8px;border:1px solid var(--db-border);background:var(--db-card);display:flex;align-items:center;justify-content:center;cursor:pointer}
.ob-hamburger svg{width:15px;height:15px;stroke:var(--db-text);stroke-width:2;fill:none}
.ob-menu-dropdown{position:absolute;top:calc(100% + 8px);right:0;min-width:220px;background:var(--db-card);border:1px solid var(--db-border);border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.18);padding:8px;z-index:50;display:none}
.ob-menu-dropdown.open{display:block}
.ob-menu-user{padding:8px 10px 10px;border-bottom:1px solid var(--db-border);margin-bottom:6px}
.ob-menu-avatar{width:34px;height:34px;border-radius:50%;background:var(--db-chip);color:var(--db-text);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0}
.ob-menu-item-icon{width:13px;height:13px;stroke:currentColor;stroke-width:1.8;fill:none;margin-right:8px;vertical-align:-2px;flex-shrink:0}
.ob-menu-token{padding:0 10px 8px}
.ob-menu-item{display:block;width:100%;text-align:left;padding:8px 10px;border-radius:8px;font-size:13.5px;font-weight:600;color:var(--db-text);text-decoration:none;background:none;border:none;cursor:pointer;font-family:inherit}
.ob-menu-item:hover{background:var(--db-chip)}
.ob-menu-mobile-links{display:none}

.ob-page{display:grid;grid-template-columns:260px 1fr;flex:1;overflow:hidden}
.mem-card-area{display:grid;grid-template-columns:1fr 320px;margin:12px 12px 12px 0;background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.ob-sidebar{background:var(--db-bg);display:flex;flex-direction:column;overflow-y:auto}
.ob-steps{display:flex;flex-direction:column;padding:18px 24px 0;flex:1}
.ob-workers-hd{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:10px}
.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative;text-decoration:none;color:inherit}
.ob-step:not(:last-child) .ob-step-rail::after{content:'';position:absolute;left:13px;top:30px;width:2px;height:calc(100% - 6px);background:var(--db-border);border-radius:2px}
.ob-step.done:not(:last-child) .ob-step-rail::after{background:var(--db-invert-bg)}
.ob-step-rail{position:relative;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding-bottom:20px}
.ob-step:last-child .ob-step-rail{padding-bottom:0}
.ob-step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;position:relative;z-index:1;flex-shrink:0;overflow:hidden}
.ob-step.pending .ob-step-num{background:var(--db-chip);color:var(--db-text-muted);border:1.5px solid var(--db-border)}
.ob-step.done .ob-step-num{background:var(--db-invert-bg);color:var(--db-invert-text)}
.ob-step-body{padding-top:4px;padding-bottom:20px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:14px;font-weight:700;color:var(--db-text);line-height:1.2}
.ob-step.pending .ob-step-label{color:var(--db-text-muted)}
.ob-step-desc{font-size:12px;color:var(--db-text-muted);margin-top:2px;line-height:1.4;display:flex;align-items:center;gap:5px}

.ob-links-section{padding:16px 24px 8px;border-top:1px solid var(--db-border);flex-shrink:0}
.ob-links-hd{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--db-text-muted);margin-bottom:8px}
.ob-link{display:flex;align-items:center;gap:9px;padding:6px 10px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:500;color:var(--db-text-muted);transition:all .12s}
.ob-link:hover{background:var(--db-card);color:var(--db-text)}
.ob-link svg{width:13px;height:13px;stroke:currentColor;stroke-width:1.8;fill:none;flex-shrink:0}
.ob-link.active{background:var(--db-card);color:var(--db-text)}

.ob-security{margin:8px 24px 16px;padding:13px 15px;border-radius:12px;background:var(--db-chip);border:1px solid var(--db-border);flex-shrink:0}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:4px}
.ob-security-row svg{width:12px;height:12px;stroke:var(--db-text-muted);flex-shrink:0;fill:none}
.ob-security-title{font-size:12.5px;font-weight:700;color:var(--db-text)}
.ob-security p{font-size:11.5px;color:var(--db-text-muted);line-height:1.55}

.mem-right{background:var(--db-card);border-left:1px solid var(--db-border);overflow-y:auto}
.tc-panel-empty{padding:24px 20px;font-size:12.5px;color:var(--db-text-muted)}
.tc-panel{padding:20px}
.tc-panel-head{font-size:13px;font-weight:700;color:var(--db-text);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--db-border)}

/* ── CONTENT ── */
.mem-main{overflow-y:auto;padding:28px 32px 60px}
.mem-wrap{max-width:1000px;margin:0 auto}

.td-back{font-size:12.5px;color:var(--db-text-muted);text-decoration:none;display:inline-block;margin-bottom:14px}
.td-back:hover{color:var(--db-text)}

.mem-status{border-radius:12px;padding:10px 14px;font-size:13.5px;margin-bottom:16px}
.mem-status.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#22c55e}
.mem-status.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#ef4444}

.td-card{border:1px solid var(--db-border);border-radius:16px;padding:20px;margin-bottom:16px}

.td-badge{font-size:11px;padding:2px 9px;border-radius:99px;font-weight:600}
.td-badge-priority{background:var(--db-chip);color:var(--db-text-muted)}
.td-badge-priority.high{background:rgba(245,158,11,.15);color:#fbbf24}
.td-badge-ft{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);color:#fbbf24}
.td-title{font-size:1.3rem;font-weight:700;color:var(--db-text);line-height:1.3;margin-top:8px}
.td-meta{font-size:13px;color:var(--db-text-muted);margin-top:4px}
.td-meta-sm{font-size:11px;color:var(--db-text-muted);margin-top:4px}
.td-gmail-id{font-size:11px;color:var(--db-text-muted);margin-bottom:2px}
.td-gmail-val{font-size:11px;font-family:monospace;color:var(--db-text);word-break:break-all}
.td-tx-select{font-size:11px;font-family:monospace;color:var(--db-text);background:transparent;border:1px solid var(--db-border);border-radius:6px;padding:2px 6px;max-width:280px;cursor:pointer}
.td-tx-select:hover{border-color:var(--db-text-muted)}
.td-gmail-saved{font-size:11px;color:#22c55e;margin-top:2px}

.td-banner{border-radius:14px;padding:16px 18px;margin-bottom:16px}
.td-banner.infra{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.3)}
.td-banner.data{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3)}
.td-banner-title{font-size:13.5px;font-weight:700;margin-bottom:4px}
.td-banner-body{font-size:12px;color:var(--db-text-muted);line-height:1.6}
.td-banner-actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:12px}
.td-btn{font-size:12px;font-weight:600;padding:7px 14px;border-radius:9px;cursor:pointer;font-family:inherit;text-decoration:none;display:inline-block;border:none}
.td-btn-danger{background:#dc2626;color:#fff}
.td-btn-amber{background:#d97706;color:#fff}
.td-btn-ghost{background:transparent;border:1px solid var(--db-border);color:var(--db-text-muted)}
.td-link-underline{font-size:11.5px;color:var(--db-text-muted);text-decoration:underline;background:none;border:none;cursor:pointer;font-family:inherit}
.td-link-underline:hover{color:#fbbf24}

.td-dismissed{background:var(--db-chip);border:1px solid var(--db-border);border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:var(--db-text-muted)}

.td-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:768px){.td-grid{grid-template-columns:1fr}}

.td-card-head{display:flex;align-items:center;gap:8px;margin-bottom:14px}
.td-card-num{width:20px;height:20px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0}
.td-card-title{font-size:13px;font-weight:600;color:var(--db-text)}
.td-conf-badge{margin-left:auto;font-size:11px;color:#4ade80}

.td-field-label{font-size:11px;color:var(--db-text-muted);margin-bottom:3px}
.td-field-val{font-size:13px;color:var(--db-text)}
.td-field-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px}
.td-field{margin-top:12px}
.td-field:first-child{margin-top:0}

.td-pre{font-size:11.5px;color:var(--db-text-muted);white-space:pre-wrap;background:var(--db-chip);border-radius:9px;padding:10px 12px;margin-top:4px;line-height:1.6}

.td-review-note{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:9px;padding:10px 12px;margin-top:10px}
.td-review-note-title{font-size:11px;font-weight:600;color:#fbbf24;margin-bottom:2px}
.td-review-note-body{font-size:11px;color:#fcd34d}

.td-decision-hint{font-size:11.5px;color:var(--db-text-muted);margin-top:4px;line-height:1.5}
.td-decision-hint strong{color:var(--db-text)}
.td-textarea{width:100%;border-radius:9px;padding:9px 12px;font-size:12.5px;background:transparent;border:1px solid var(--db-border);color:var(--db-text);font-family:inherit;resize:none;margin-top:12px}
.td-decision-row{display:flex;gap:8px;margin-top:12px}
.td-decision-btn{flex:1;padding:11px;border-radius:11px;font-size:13px;font-weight:700;color:#fff;border:none;cursor:pointer;font-family:inherit}

.td-footer{margin-top:22px;padding-top:16px;border-top:1px solid var(--db-border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px}
.td-footer-note{font-size:11.5px;color:var(--db-text-muted)}

/* ── Transaction Center — standardized, contract-driven stage list.
   Every stage renders the same way regardless of worker: done (checked,
   full opacity), active (highlighted — gold border + buttons if it's a
   hard gate, a lighter tag otherwise), pending (dimmed, no content). ── */
.tc-list{display:flex;flex-direction:column;gap:8px;margin-bottom:16px}
.tc-stage{border:1px solid var(--db-border);border-radius:12px;padding:13px 16px;opacity:.4;transition:opacity .2s}
.tc-stage-active{box-shadow:0 0 0 2px var(--accent-text,var(--accent)) inset}
.tc-stage.is-done,.tc-stage.is-active{opacity:1}
.tc-stage.is-active.gate-hard{border-color:#F5C518;background:rgba(245,197,24,.08)}
.tc-stage-head{display:flex;align-items:center;gap:10px}
.tc-stage-icon{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10.5px;font-weight:800;flex-shrink:0;background:var(--db-chip);color:var(--db-text-muted)}
.tc-stage.is-done .tc-stage-icon{background:#22c55e;color:#fff}
.tc-stage.is-active.gate-hard .tc-stage-icon{background:#F5C518;color:#412402}
.tc-stage.is-skipped{opacity:1}
.tc-stage.is-skipped .tc-stage-icon{background:var(--db-chip);color:var(--db-text-muted);border:1.5px dashed var(--db-border)}
.tc-stage-label{font-size:13px;font-weight:700;color:var(--db-text)}
.tc-stage-sub{font-size:11px;color:var(--db-text-muted);margin-top:1px}
.tc-stage-tag{font-size:9px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;padding:3px 9px;border-radius:99px;margin-left:auto;flex-shrink:0;white-space:nowrap}
.tc-stage-tag.blocks{background:rgba(239,159,39,.15);color:#EF9F27}
.tc-stage-tag.soft,.tc-stage-tag.skippable{background:var(--db-chip);color:var(--db-text-muted)}
.tc-stage-tag.skipped{background:rgba(239,68,68,.1);color:#ef4444}
.tc-stage-body{margin-top:10px;padding-left:32px}
.tc-msg{background:var(--db-chip);border-radius:8px;padding:9px 11px;font-size:11.5px;color:var(--db-text-muted);line-height:1.6;margin-top:6px;white-space:pre-wrap}
.tc-msg-meta{font-size:10.5px;color:var(--db-text-muted);margin-bottom:3px;display:flex;align-items:center;gap:6px}
.tc-msg-meta strong{color:var(--db-text);font-weight:600}
.tc-field-row{display:flex;flex-wrap:wrap;gap:16px;margin-top:4px}
.tc-field{font-size:11.5px}
.tc-field .lbl{color:var(--db-text-muted);display:block;font-size:10px;margin-bottom:2px}
.tc-btn-row{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
.tc-btn{flex:1;min-width:120px;padding:9px 12px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;text-align:center;border:none;text-decoration:none;display:block}
.tc-btn-primary{background:#F5C518;color:#412402}
.tc-btn-ghost{background:transparent;border:1px solid var(--db-border);color:var(--db-text-muted)}
.tc-file-input{font-size:11.5px;color:var(--db-text-muted);margin-bottom:8px}

/* Client draft round tabs — blue = actually approved/consumed, gray = drafted but never acted on */
.tc-draft-tabs{display:flex;gap:6px;margin-bottom:8px}
.tc-draft-tab{font-size:11px;font-weight:700;padding:5px 12px;border-radius:99px;cursor:pointer;font-family:inherit;border:1px solid var(--db-border);background:var(--db-chip);color:var(--db-text-muted);display:flex;align-items:center;gap:6px}
.tc-draft-tab.consumed{background:rgba(59,130,246,.15);border-color:rgba(59,130,246,.4);color:#60a5fa}
.tc-draft-tab.upcoming{opacity:.5;border-style:dashed}
.tc-draft-tab.active{box-shadow:0 0 0 1.5px var(--db-text) inset}
.tc-draft-dot{width:6px;height:6px;border-radius:50%;background:#60a5fa;flex-shrink:0}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow-x:hidden;overflow-y:auto;height:auto;width:100%}
  .ob-shell{height:auto;overflow:visible;width:100%}
  .ob-shell,.ob-shell *{min-width:0}
  .ob-topbar{height:auto;padding:12px 16px;flex-wrap:wrap;gap:6px}
  .ob-topbar-logo{font-size:18px}
  .ob-topbar-email{display:none}
  .ob-page{display:block;height:auto;overflow:visible;width:100%}
  .ob-sidebar{width:100%;flex-direction:column;padding:0;overflow:hidden;border-bottom:none}
  .ob-steps,.ob-links-section,.ob-security{display:none}
  .ob-menu-mobile-links{display:block}
  .mem-right{display:none}
  .mem-main{padding:16px}
  .mem-card-area{display:block;margin:0;border-radius:0;border:none;box-shadow:none;background:var(--db-card)}
}
</style>
<script>
(function () {
  var saved = localStorage.getItem('unit-theme-v2') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();
</script>
</head>
<body>

@php
$tokenFmt = $tokenTotal >= 1000000 ? number_format($tokenTotal/1000000,1).'M' : number_format($tokenTotal);
$sidebarLinks = [
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('app.workers.memory',$tx->worker_slug), false],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('app.workers.templates',['slug'=>$tx->worker_slug]), false],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('app.workers.rules',$tx->worker_slug), false],
  ['Configure',    'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', route('app.workers.configure', $tx->worker_slug), false],

  ['Settings',     'M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75', route('app.workers.settings', $tx->worker_slug), false],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('app.workers.fast-track.page',$tx->worker_slug), false],
  ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('app.workers.connect',$tx->worker_slug), false],
  ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('app.billing'), false],
  ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('app.workers.transactions',$tx->worker_slug), true],
];

$read     = $tx->read_output     ? json_decode($tx->read_output)     : null;
$memory   = $tx->memory_output   ? json_decode($tx->memory_output)   : null;
$classify = $tx->classify_output ? json_decode($tx->classify_output) : null;
$draft    = $tx->draft_output    ? json_decode($tx->draft_output)    : null;
$rawInput = json_decode($tx->raw_input ?? '{}', true);
$source   = $rawInput['source'] ?? 'unknown';
$sourceLabels = [
    'gmail_webhook'    => 'Fetched from Gmail',
    'asset_watch'      => 'Detected from asset registry',
    'human_trigger'    => 'Started manually ("Renew Now")',
    'fast_track_test'  => 'Fast Track test run',
    'manual_test'      => 'Manual test run',
    'public_demo'      => 'Public demo run',
];
$sourceLabel = $sourceLabels[$source] ?? $source;
$isFastTrack    = $source === 'fast_track_test';
$isFailed       = $tx->status === 'failed';
$isDismissed    = $tx->status === 'dismissed';
$isFiltered     = $tx->status === 'filtered_out';
$canRefire      = $isFailed && !$isFastTrack;
$canDismiss     = in_array($tx->status, ['failed','draft_ready','human_review','blocked']);
$canDelete      = $isFastTrack;

$hasAnyOutput  = $read || $memory || $classify;
$lowConfidence = $memory && ($memory->confidence ?? 100) < 70;
$unassigned    = $memory && str_contains(strtolower($memory->matched_client ?? ''), 'unassigned');
$failureType   = null;
if ($isFailed) {
    $failureType = $hasAnyOutput ? 'data' : 'infrastructure';
}

$statusColors = [
    'draft_ready'  => ['bg'=>'rgba(167,139,250,.15)','color'=>'#a78bfa'],
    'failed'       => ['bg'=>'rgba(239,68,68,.15)','color'=>'#fca5a5'],
    'dismissed'    => ['bg'=>'var(--db-chip)','color'=>'var(--db-text-muted)'],
    'human_review' => ['bg'=>'rgba(245,158,11,.15)','color'=>'#fcd34d'],
    'approved'     => ['bg'=>'rgba(34,197,94,.15)','color'=>'#86efac'],
    'sent'         => ['bg'=>'rgba(34,197,94,.15)','color'=>'#86efac'],
    'blocked'      => ['bg'=>'rgba(249,115,22,.15)','color'=>'#fb923c'],
    'filtered_out' => ['bg'=>'rgba(107,114,128,.15)','color'=>'#9ca3af'],
];
$statusColor = $statusColors[$tx->status] ?? ['bg'=>'var(--db-chip)','color'=>'var(--db-text-muted)'];
@endphp

<div class="ob-shell">

{{-- ══ TOP BAR ══ --}}
<div class="ob-topbar">
  <a href="{{ route('app.dashboard') }}" class="ob-topbar-logo" style="text-decoration:none">UNIT</a>
  <div class="ob-topbar-right">
    <a href="{{ route('app.profile.show') }}" class="ob-topbar-name" style="text-decoration:none">{{ auth()->user()->name }}</a>
    <button class="ob-theme-toggle" id="theme-toggle" type="button" title="Toggle dark/light mode" aria-label="Toggle theme"></button>
    <div class="ob-menu-wrap">
      <button class="ob-hamburger" id="menu-toggle" type="button" aria-label="Menu">
        <svg viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="ob-menu-dropdown" id="menu-dropdown">
        <div class="ob-menu-user" style="display:flex;align-items:center;gap:10px">
          <div class="ob-menu-avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
          <div style="min-width:0">
            <div class="ob-topbar-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth()->user()->name }}</div>
            <div class="ob-topbar-email" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth()->user()->email }}</div>
          </div>
        </div>
        <div class="ob-menu-mobile-links">
          <a href="{{ route('app.dashboard') }}" class="ob-menu-item">
            <svg viewBox="0 0 24 24" class="ob-menu-item-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
          </a>
          @foreach($sidebarLinks as [$lbl,$ico,$href,])
          <a href="{{ $href }}" class="ob-menu-item">
            <svg viewBox="0 0 24 24" class="ob-menu-item-icon"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ico }}"/></svg>
            {{ $lbl }}
          </a>
          @endforeach
          <div style="border-top:1px solid var(--db-border);margin:6px 0"></div>
        </div>
        <div class="ob-menu-token"><span class="ob-token-badge">{{ $tokenFmt }} tokens</span></div>
        <a href="{{ route('app.settings.api-keys') }}" class="ob-menu-item">Settings</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="ob-menu-item">Logout</button></form>
      </div>
    </div>
  </div>
</div>

<div class="ob-page">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ob-sidebar">
    <div class="ob-steps">
      <div class="ob-workers-hd">
        <a href="{{ route('app.profile.show') }}" style="color:inherit;text-decoration:none">{{ strtoupper($firstName) }}'S WORKERS</a>
      </div>
      @foreach($workerCatalog as $wc)
      @php
        $wDot  = $wc->status==='active' ? '#22c55e' : '#f59e0b';
        $wHref = !$wc->active ? route('public.workers.index') : ($wc->slug==='ava' ? route('app.desk.ava') : route('app.workers.overview',$wc->slug));
      @endphp
      <a href="{{ $wHref }}" class="ob-step {{ $wc->active ? 'done' : 'pending' }}" style="text-decoration:none{{ !$wc->active ? ';opacity:.5' : '' }}">
        <div class="ob-step-rail">
          <div class="ob-step-num" style="padding:0">
            @if($wc->image)
              <img src="{{ $wc->image }}" style="width:100%;height:100%;object-fit:cover;display:block{{ !$wc->active ? ';filter:grayscale(1)' : '' }}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
              <span style="display:none;font-size:11px;font-weight:800;color:#6B7280;width:100%;height:100%;align-items:center;justify-content:center">{{ substr($wc->name,0,1) }}</span>
            @else
              <span style="font-size:11px;font-weight:800;color:#6B7280">{{ substr($wc->name,0,1) }}</span>
            @endif
          </div>
        </div>
        <div class="ob-step-body">
          <div class="ob-step-label">{{ $wc->name }}</div>
          <div class="ob-step-desc">
            @if($wc->active)
              <span style="width:5px;height:5px;border-radius:50%;background:{{ $wDot }};flex-shrink:0;display:inline-block"></span>{{ $wc->role }}
            @else
              Not hired — {{ $wc->role }}
            @endif
          </div>
        </div>
      </a>
      @endforeach
      <a href="{{ route('public.workers.index') }}" class="ob-step pending" style="text-decoration:none;margin-top:4px">
        <div class="ob-step-rail"><div class="ob-step-num" style="background:var(--db-chip);border:1.5px dashed var(--db-border);color:var(--db-text-muted);font-size:16px;font-weight:400">+</div></div>
        <div class="ob-step-body"><div class="ob-step-label">Hire a worker</div></div>
      </a>
    </div>

    <div class="ob-links-section">
      <div class="ob-links-hd">LINKS</div>
      @foreach($sidebarLinks as [$lbl,$ico,$href,$isActive])
      <a href="{{ $href }}" class="ob-link {{ $isActive ? 'active' : '' }}">
        <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ico }}"/></svg>
        {{ $lbl }}
      </a>
      @endforeach
    </div>

    <div class="ob-security">
      <div class="ob-security-row">
        <svg viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>Every action your workers take is logged here.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      <a href="{{ route('app.workers.transactions', $tx->worker_slug) }}" class="td-back">← Back to Transactions</a>

      @if(session('success'))<div class="mem-status success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="mem-status error">{{ session('error') }}</div>@endif

      {{-- Header --}}
      <div class="td-card">
        <div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:12px">
          <div style="min-width:0">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
              {{-- TX- selector — jump to any other transaction in this
                   deployment's pipeline without going back to the list. --}}
              <select id="tx-selector" class="td-tx-select" title="Switch transaction">
                <option value="" disabled selected>{{ $tx->tx_id }}</option>
                @foreach($otherTransactions as $other)
                  @if($other->tx_id !== $tx->tx_id)
                  <option value="{{ route('app.transactions.show', ['slug' => $tx->worker_slug, 'txId' => $other->tx_id]) }}">{{ $other->tx_id }} — {{ $other->category ?? 'Processing' }} ({{ $other->status }})</option>
                  @endif
                @endforeach
              </select>
              @if($tx->priority)
              <span class="td-badge td-badge-priority {{ in_array($tx->priority, ['High','Critical']) ? 'high' : '' }}">{{ $tx->priority }}</span>
              @endif
              <span class="td-badge" style="background:{{ $statusColor['bg'] }};color:{{ $statusColor['color'] }}">{{ $tx->status }}</span>
              @if($isFastTrack)<span class="td-badge td-badge-ft">⚡ Fast Track Test</span>@endif
            </div>
            <div class="td-title">{{ $tx->category ?? 'Processing...' }}</div>
            @if($tx->worker_slug === 'nux' && $nuxRegister)
            <div class="td-meta">{{ strtoupper($nuxRegister->source_platform ?? '') }} → {{ implode(', ', json_decode($nuxRegister->target_channels ?? '[]', true) ?: []) }} · {{ $nuxRegister->topic ?? '—' }}</div>
            @elseif($memory)
            <div class="td-meta">{{ $memory->matched_client ?? '—' }} · {{ $memory->asset ?? '—' }} · {{ $memory->primary_contact_name ?? '—' }}</div>
            @endif
            <div class="td-meta-sm">{{ \Carbon\Carbon::parse($tx->created_at)->format('M j, Y · g:i A') }} · {{ $sourceLabel }}</div>
          </div>
          @if($tx->gmail_draft_id)
          <div style="text-align:right;flex-shrink:0">
            <div class="td-gmail-id">Gmail Draft</div>
            <div class="td-gmail-val">{{ $tx->gmail_draft_id }}</div>
            <div class="td-gmail-saved">✓ Saved in Gmail</div>
          </div>
          @endif
        </div>
      </div>

      {{-- Bundle breakdown — this transaction covers a renews_together
           group, not a single asset. Shown once, near the top, since it's
           relevant context for every stage below rather than belonging to
           any one of them. --}}
      @if($memory && !empty($memory->line_items))
      <div class="td-card" style="border-color:rgba(59,130,246,.3);background:rgba(59,130,246,.05)">
        <div class="td-card-head" style="margin-bottom:10px">
          <span class="td-card-num" style="background:rgba(59,130,246,.15);color:#60a5fa">⇄</span>
          <span class="td-card-title">Bundled renewal — {{ count($memory->line_items) }} services</span>
        </div>
        @foreach($memory->line_items as $item)
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:6px 0{{ !$loop->last ? ';border-bottom:1px solid var(--db-border)' : '' }}">
          <div style="min-width:0">
            <div style="font-size:12.5px;font-weight:600;color:var(--db-text)">{{ $item->name }}</div>
            <div style="font-size:11px;color:var(--db-text-muted)">{{ $item->type }}{{ $item->vendor ? ' · ' . $item->vendor : '' }}</div>
          </div>
          <div style="font-size:11.5px;color:var(--db-text-muted);white-space:nowrap">{{ $item->renewal_date ? \Carbon\Carbon::parse($item->renewal_date)->format('M j, Y') : '—' }}</div>
        </div>
        @endforeach
      </div>
      @endif

      {{-- Failure context banner --}}
      @if($isFailed)
      <div class="td-banner {{ $failureType === 'infrastructure' ? 'infra' : 'data' }}">
        <div class="td-banner-title" style="color:{{ $failureType === 'infrastructure' ? '#f87171' : '#fbbf24' }}">
          {{ $failureType === 'infrastructure' ? '✕ Infrastructure failure' : '⚠ Data failure' }}
        </div>
        <div class="td-banner-body">
          @if($failureType === 'infrastructure')
            The pipeline job crashed before completing — likely a transient error (token expiry, queue restart, API timeout).
            <strong style="color:var(--db-text)">Re-firing is safe</strong> — the original email will be re-processed from scratch.
          @else
            The pipeline ran but couldn't complete due to missing or mismatched data.
            @if($lowConfidence) Confidence was {{ $memory->confidence }}% — below the required threshold.@endif
            @if($unassigned) No client is linked to this asset.@endif
            <strong style="color:var(--db-text)">Re-firing without fixing the underlying data will produce the same result.</strong>
          @endif
        </div>
        <div class="td-banner-actions">
          @if($failureType === 'infrastructure' && $canRefire)
          <form method="POST" action="{{ route('app.transactions.refire', $tx->tx_id) }}">
            @csrf
            <button type="submit" class="td-btn td-btn-danger">↺ Re-fire</button>
          </form>
          @endif
          @if($failureType === 'data')
          <a href="{{ route('app.memory') }}" class="td-btn td-btn-amber">Fix in Memory →</a>
          @if($canDismiss)
          <form method="POST" action="{{ route('app.transactions.dismiss', $tx->tx_id) }}">
            @csrf
            <button type="submit" class="td-btn td-btn-ghost">Dismiss</button>
          </form>
          @endif
          @if($canRefire)
          <form method="POST" action="{{ route('app.transactions.refire', $tx->tx_id) }}">
            @csrf
            <button type="submit" class="td-link-underline" onclick="return confirm('Re-firing will not fix the data issue — it will likely fail again. Fix the missing client or asset in Memory first.\n\nContinue anyway?')">Re-fire anyway</button>
          </form>
          @endif
          @endif
        </div>
      </div>
      @endif

      {{-- Notify Customer discovery — shown once a renewal has actually
           closed out (reached notify_customer or later) if the tenant has
           never turned this on. It's an opt-in feature with no other
           discovery path, so the moment it would have mattered is exactly
           when to mention it. --}}
      @php
        $notifyCustomerStage = collect($stages)->firstWhere('key', 'notify_customer');
        $showNotifyCustomerTip = $notifyCustomerStage
            && in_array($notifyCustomerStage['state'], ['done', 'active'], true)
            && !\App\Platform\SDK\UnitPlatform::gateEnabled($tx->deployment_id, 'notify_customer', false);
      @endphp
      @if($showNotifyCustomerTip)
      <div class="td-banner data" style="border-color:rgba(59,130,246,.3);background:rgba(59,130,246,.08)">
        <div class="td-banner-title" style="color:#60a5fa">💡 This renewal closed without telling the client</div>
        <div class="td-banner-body">
          AVA emailed you that this renewal is complete, but didn't notify the client directly — that's an opt-in feature, off by default.
          Turn on <strong style="color:var(--db-text)">"Renewal Complete Notice to Client"</strong> in AVA Settings if you'd like AVA to tell them the next renewal date automatically.
        </div>
        <div class="td-banner-actions">
          <a href="{{ route('app.workers.settings', $tx->worker_slug) }}" class="td-btn td-btn-amber" style="background:#2563eb">Open AVA Settings →</a>
        </div>
      </div>
      @endif

      {{-- Dismissed notice --}}
      @if($isDismissed)
      <div class="td-dismissed">○ This transaction was dismissed and removed from active queues. The audit trail is preserved below.</div>
      @endif

      {{-- Filtered notice — Stage 0 (Capture Filter) dropped this before it ever
           reached Read/Classify/Memory/Draft. Those cards below are correctly
           empty; this explains why instead of leaving the page looking blank. --}}
      @if($isFiltered)
      <div class="td-banner data">
        <div class="td-banner-title" style="color:#9ca3af">◌ Filtered — never processed</div>
        <div class="td-banner-body">
          This email never reached AVA's pipeline. It was screened out by your capture rules before Read/Classify/Memory/Draft ran.
          @if($tx->filter_reason)
            <br><strong style="color:var(--db-text)">Reason:</strong> {{ $tx->filter_reason }}
          @endif
        </div>
        <div class="td-banner-actions">
          <a href="{{ route('app.workers.rules', $dep->worker_slug ?? 'ava') }}" class="td-btn td-btn-amber">Review Capture Rules →</a>
        </div>
      </div>
      @endif

      {{-- NUX: repurposed copies — not AVA's gate-driven pipeline, kept as its own card --}}
      @if($tx->worker_slug === 'nux' && $nuxRegister)
      @php
        $nuxCopies = json_decode($nuxRegister->repurposed_copies ?? '[]', true) ?: [];
      @endphp
      <div class="td-card">
        <div class="td-card-head">
          <span class="td-card-num" style="background:rgba(94,234,212,.15);color:#5eead4">⇄</span>
          <span class="td-card-title">Repurposed Content</span>
        </div>
        @forelse($nuxCopies as $copy)
        <div class="td-field">
          <div class="td-field-label" style="color:#5eead4;text-transform:uppercase;letter-spacing:.05em;font-weight:700">{{ strtoupper($copy['channel'] ?? '') }}</div>
          <div class="td-pre">{{ $copy['copy'] ?? '' }}</div>
          <div class="td-field-label" style="margin-top:4px">{{ $copy['char_count'] ?? 0 }} characters</div>
        </div>
        @empty
        <p style="font-size:13px;color:var(--db-text-muted)">No copies available.</p>
        @endforelse

        @if($nuxRegister->image_url)
        <div class="td-field" style="border-top:1px solid var(--db-border);padding-top:14px">
          <div class="td-field-label" style="color:#5eead4;text-transform:uppercase;letter-spacing:.05em;font-weight:700;margin-bottom:8px">Generated Image</div>
          <img src="{{ $nuxRegister->image_url }}" alt="NUX generated image" style="max-width:100%;border-radius:8px;border:1px solid var(--db-border)">
        </div>
        @endif

        @if($nuxRegister->draft_summary)
        <div class="td-field" style="border-top:1px solid var(--db-border);padding-top:10px;color:var(--db-text-muted);font-size:12px">{{ $nuxRegister->draft_summary }}</div>
        @endif
      </div>
      @endif

      {{-- ══ TRANSACTION CENTER — the standardized, contract-driven stage list.
           Done stages in order, the active gate (if any) highlighted, pending
           stages dimmed. Same rendering for any worker's own gate_type stages. ══ --}}
      <div class="tc-list">
        @foreach($stages as $stage)
        @php
          $isGate = !empty($stage['gate_type']);
          $classes = 'tc-stage is-' . $stage['state'] . ($isGate ? ' gate-' . $stage['gate_type'] : '') . (!empty($stage['skipped_by_gate']) ? ' is-skipped' : '');
        @endphp
        <div class="{{ $classes }}" data-stage-key="{{ $stage['key'] }}" onclick="tcShowPanel('{{ $stage['key'] }}')" style="cursor:pointer">
          <div class="tc-stage-head">
            <span class="tc-stage-icon">
              @if(!empty($stage['skipped_by_gate']))–@elseif($stage['state'] === 'done')✓@else{{ $stage['i'] + 1 }}@endif
            </span>
            <div style="min-width:0">
              <div class="tc-stage-label">{{ $stage['label'] }}</div>
              @if($stage['state'] !== 'done')
              <div class="tc-stage-sub">{{ $stage['sub'] }}</div>
              @elseif(!empty($stage['completed_at']))
              {{-- Reuses the sub-label's space, which otherwise goes blank
                   once a stage is done — sourced from transaction_stage_log
                   (see UnitPlatform::stageCompletedAt()), the same universal
                   completion log the archive PDF's timestamps read from.
                   duration_ms is only present for stages that log a
                   'started' row (UnitPlatform::stageStarted()) — older
                   transactions and a few gate-driven stages (human_decide,
                   confirm_payment) legitimately show no duration, since
                   there's nothing to time for a synchronous human action. --}}
              @php $durMs = $stage['duration_ms'] ?? null; @endphp
              <div class="tc-stage-sub" title="{{ \Carbon\Carbon::parse($stage['completed_at'])->format('M j, Y \a\t g:i A') }}">completed {{ \Carbon\Carbon::parse($stage['completed_at'])->diffForHumans() }}@if($durMs) &middot; took {{ $durMs < 1000 ? $durMs.'ms' : ($durMs < 60000 ? round($durMs/1000, 1).'s' : round($durMs/60000, 1).'m') }}@endif</div>
              @endif
            </div>
            @if(!empty($stage['skipped_by_gate']))<span class="tc-stage-tag skipped">skipped — disabled in settings</span>@endif
            @if($stage['gate_type'] === 'hard')<span class="tc-stage-tag blocks">blocks renewal</span>@endif
            @if($stage['gate_type'] === 'soft')<span class="tc-stage-tag soft">optional · won't block</span>@endif
            @if($stage['gate_type'] === 'skippable')<span class="tc-stage-tag skippable">skippable</span>@endif
            {{-- Nudges are a separate, gate-watching cron (ApprovalReminderJob) —
                 not a pipeline stage of their own — so they're surfaced here as a
                 small collapsed badge on the gate they watch, not as a stage. --}}
            @if($stage['key'] === 'human_decide' && !empty($stage['reminders']))
            <span class="tc-stage-tag soft" title="Sent because you hadn't decided yet — see below">{{ count($stage['reminders']) }} nudge{{ count($stage['reminders']) === 1 ? '' : 's' }} sent</span>
            @endif
          </div>

          @if(!empty($stage['skipped_by_gate']))
          <div class="tc-stage-body">
            <p style="font-size:12px;color:var(--db-text-muted)">
              This stage never ran — <strong style="color:var(--db-text)">{{ $stage['label'] }}</strong> is turned off in
              <a href="{{ route('app.workers.settings', $tx->worker_slug) }}" style="color:var(--accent-text,var(--db-text));text-decoration:underline">AVA Settings</a>.
            </p>
          </div>
          @elseif($stage['state'] !== 'pending')
          <div class="tc-stage-body">

            {{-- Approve & Send — up to 3 client reminder drafts. A tab per
                 round: blue = the user actually approved/consumed that draft,
                 gray = it was drafted but superseded (never acted on). --}}
            @if($stage['key'] === 'human_decide')
              @include('dashboard.partials._client-draft-tabs', ['clientDrafts' => $stage['client_drafts'], 'wrapId' => 'cd-decide-' . $tx->tx_id])

              @foreach($stage['reminders'] as $r)
              <div class="tc-msg-meta" style="margin-top:8px"><strong>Nudge — attempt {{ $r['attempt_number'] }}</strong> · {{ \Carbon\Carbon::parse($r['sent_at'])->format('M j, g:i A') }}</div>
              <div class="tc-msg">{{ $r['subject'] }}{{ "\n\n" }}{{ $r['body'] }}</div>
              @endforeach

              @php
                // Approving round 1 authorizes the whole cadence now, not
                // just that message (see ClientReminderCycleJob /
                // PushToGmailJob) — human_decision is set once and never
                // reset between rounds, so it alone (not any individual
                // draft's approved_at) is what "still needs a first
                // decision" means. Rounds 2/3 legitimately never get their
                // own approved_at anymore; that's expected, not stuck.
                $awaitingDecision = empty($tx->human_decision);
              @endphp
              @if($stage['state'] === 'active' && $tx->status !== 'rejected')
                @if($awaitingDecision)
                @if($tx->nudging_paused_at)
                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin:8px 0">
                  <p style="font-size:12px;color:var(--db-text-muted)">Paused — no response after several reminders.</p>
                  <form method="POST" action="{{ route('app.transactions.resume-nudging', $tx->tx_id) }}">
                    @csrf<button type="submit" class="tc-btn tc-btn-ghost">Resume</button>
                  </form>
                </div>
                @endif
                <div class="tc-btn-row">
                  <form method="POST" action="{{ route('app.transactions.decide', $tx->tx_id) }}" style="flex:1">
                    @csrf<input type="hidden" name="decision" value="approved">
                    <button type="submit" class="tc-btn tc-btn-primary" style="width:100%">Approve — continue this cadence</button>
                  </form>
                  <form method="POST" action="{{ route('app.transactions.decide', $tx->tx_id) }}" style="flex:1" onclick="return confirm('Reject and delete the Gmail draft?')">
                    @csrf<input type="hidden" name="decision" value="rejected">
                    <button type="submit" class="tc-btn tc-btn-ghost" style="width:100%">Reject</button>
                  </form>
                </div>
                <p style="font-size:11.5px;color:var(--db-text-muted);margin-top:6px">Approving once covers all 3 rounds — no need to come back and approve rounds 2 and 3 separately. You can stop the remaining reminders at any time.</p>
                <form method="POST" action="{{ route('app.transactions.decide', $tx->tx_id) }}" style="margin-top:6px" onclick="return confirm('This skips the remaining reminder rounds and moves straight to fulfillment (invoice, documents, payment). Use this only if you already closed this renewal with the client outside AVA.\n\nContinue?')">
                  @csrf<input type="hidden" name="decision" value="approved"><input type="hidden" name="skip_cadence" value="1">
                  <button type="submit" class="td-link-underline" style="width:100%;text-align:center;padding:6px 0">Approve &amp; proceed <span style="opacity:.7">— already closed this outside AVA, skip remaining reminders</span></button>
                </form>
                @elseif($tx->cadence_skipped)
                <p style="font-size:12px;color:var(--db-text-muted);margin-top:8px">
                  ✓ Approved via "Approve &amp; proceed" — remaining reminders were skipped, moved straight to fulfillment.
                </p>
                @elseif($tx->cadence_stopped)
                <p style="font-size:12px;color:var(--db-text-muted);margin-top:8px">
                  ■ Remaining reminders stopped. The renewal itself is unaffected — this only stopped further client nudges.
                </p>
                @else
                <p style="font-size:12px;color:var(--db-text-muted);margin-top:8px">
                  ✓ Approved — this cadence continues automatically. Waiting for the next scheduled reminder.
                </p>
                <form method="POST" action="{{ route('app.transactions.stop-cadence', $tx->tx_id) }}" style="margin-top:6px" onclick="return confirm('Stop sending the remaining reminders for this renewal? The renewal itself stays open — this only stops AVA from nudging the client further.\n\nContinue?')">
                  @csrf
                  <button type="submit" class="td-link-underline" style="width:100%;text-align:center;padding:6px 0">Stop remaining reminders</button>
                </form>
                @endif
              @endif
            @endif

            {{-- Request Invoice — soft nudge with OCR --}}
            @if($stage['key'] === 'request_invoice')
              @php $inv = $stage['content'] ?? []; @endphp
              @if(($inv['status'] ?? null) === 'attached')
                <div class="tc-field-row">
                  <div class="tc-field"><span class="lbl">Amount</span>{{ $inv['ocr']['amount'] ?? '—' }} {{ $inv['ocr']['currency'] ?? '' }}</div>
                  <div class="tc-field"><span class="lbl">Issued</span>{{ $inv['ocr']['issued_date'] ?? '—' }}</div>
                  <div class="tc-field"><span class="lbl">Due</span>{{ $inv['ocr']['due_date'] ?? '—' }}</div>
                </div>
                @foreach($inv['client_messages'] ?? [] as $m)
                <div class="tc-msg-meta"><strong>Message {{ $m['sequence'] ?? 1 }}</strong> · {{ \Carbon\Carbon::parse($m['sent_at'])->format('M j, g:i A') }}</div>
                <div class="tc-msg">{{ $m['subject'] ?? '' }}{{ "\n\n" }}{{ $m['body'] ?? '' }}</div>
                @endforeach
              @elseif(($inv['status'] ?? null) === 'simulated')
                <div class="tc-msg">{{ $inv['sample'] ?? '' }}</div>
              @else
                <p style="font-size:12px;color:var(--db-text-muted);margin-bottom:8px">Not attached yet — this won't block the renewal either way.</p>
                <form method="POST" action="{{ route('app.transactions.attach-invoice', $tx->tx_id) }}" enctype="multipart/form-data">
                  @csrf
                  <input type="file" name="invoice_file" accept="application/pdf" class="tc-file-input" required>
                  <button type="submit" class="tc-btn tc-btn-ghost">Attach invoice</button>
                </form>
              @endif
              @foreach($stage['reminders'] as $r)
              <div class="tc-msg-meta" style="margin-top:8px"><strong>Nudge — attempt {{ $r['attempt_number'] }}</strong> · {{ \Carbon\Carbon::parse($r['sent_at'])->format('M j, g:i A') }}</div>
              <div class="tc-msg">{{ $r['subject'] }}{{ "\n\n" }}{{ $r['body'] }}</div>
              @endforeach
            @endif

            {{-- Request Documents — skippable yes/no --}}
            @if($stage['key'] === 'request_documents')
              @php $docs = $stage['content'] ?? []; @endphp
              @if(($docs['status'] ?? null) === 'skipped')
                <p style="font-size:12px;color:var(--db-text-muted)">No documents needed for this renewal.</p>
              @elseif(($docs['status'] ?? null) === 'attached')
                @foreach($docs['client_messages'] ?? [] as $m)
                <div class="tc-msg-meta"><strong>Message {{ $m['sequence'] ?? 1 }}</strong> · {{ \Carbon\Carbon::parse($m['sent_at'])->format('M j, g:i A') }}</div>
                <div class="tc-msg">{{ $m['subject'] ?? '' }}{{ "\n\n" }}{{ $m['body'] ?? '' }}</div>
                @endforeach
              @else
                <p style="font-size:12px;color:var(--db-text-muted);margin-bottom:8px">Any documents to send the client?</p>
                <div class="tc-btn-row">
                  <form method="POST" action="{{ route('app.transactions.attach-documents', $tx->tx_id) }}" enctype="multipart/form-data" style="flex:1">
                    @csrf
                    <input type="file" name="document_file" class="tc-file-input" required>
                    <button type="submit" class="tc-btn tc-btn-ghost" style="width:100%">Yes, attach one</button>
                  </form>
                  <form method="POST" action="{{ route('app.transactions.skip-documents', $tx->tx_id) }}" style="flex:1">
                    @csrf
                    <button type="submit" class="tc-btn tc-btn-ghost" style="width:100%">No, skip</button>
                  </form>
                </div>
              @endif
            @endif

            {{-- Confirm Payment — the one hard gate downstream of Approve & Send --}}
            @if($stage['key'] === 'confirm_payment')
              @php $pay = $stage['content'] ?? []; @endphp
              @foreach($stage['reminders'] as $r)
              <div class="tc-msg-meta"><strong>Reminder — attempt {{ $r['attempt_number'] }}</strong> · {{ \Carbon\Carbon::parse($r['sent_at'])->format('M j, g:i A') }}</div>
              <div class="tc-msg">{{ $r['subject'] }}{{ "\n\n" }}{{ $r['body'] }}</div>
              @endforeach

              @if(($pay['confirmed'] ?? null) === true)
                <p style="font-size:12px;color:var(--db-text-muted)">Confirmed {{ !empty($pay['confirmed_at']) ? \Carbon\Carbon::parse($pay['confirmed_at'])->format('M j, g:i A') : '' }}.</p>
              @elseif(($pay['confirmed'] ?? null) === false)
                <p style="font-size:12px;color:var(--db-text-muted)">Canceled {{ !empty($pay['canceled_at']) ? \Carbon\Carbon::parse($pay['canceled_at'])->format('M j, g:i A') : '' }}.</p>
              @elseif($stage['state'] === 'active')
                @if($tx->nudging_paused_at)
                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px">
                  <p style="font-size:12px;color:var(--db-text-muted)">Paused — no response after several reminders.</p>
                  <form method="POST" action="{{ route('app.transactions.resume-nudging', $tx->tx_id) }}">
                    @csrf<button type="submit" class="tc-btn tc-btn-ghost">Resume</button>
                  </form>
                </div>
                @endif
                <p style="font-size:12px;color:var(--db-text-muted);margin-bottom:10px">Confirm that payment has been received for this transaction.</p>
                <div class="tc-btn-row">
                  <form method="POST" action="{{ route('app.transactions.confirm-renewal', $tx->tx_id) }}" style="flex:1">
                    @csrf<button type="submit" class="tc-btn tc-btn-primary" style="width:100%">Confirm payment</button>
                  </form>
                  <form method="POST" action="{{ route('app.transactions.cancel-renewal', $tx->tx_id) }}" style="flex:1" onclick="return confirm('Cancel this renewal?')">
                    @csrf<button type="submit" class="tc-btn tc-btn-ghost" style="width:100%">Cancel renewal</button>
                  </form>
                </div>
              @endif
            @endif

            {{-- Everything else — generic content dump from the contract's output_column --}}
            @if(!in_array($stage['key'], ['human_decide','request_invoice','request_documents','confirm_payment']) && $stage['content'])
              @php $c = $stage['content']; @endphp
              @if($stage['key'] === 'draft_email')
                {{-- Same tab switcher as Approve & Send — the tenant sees
                     every cadence message here too, click a round to preview
                     its template, and only needs to approve once below. --}}
                @include('dashboard.partials._client-draft-tabs', ['clientDrafts' => $stage['client_drafts'], 'wrapId' => 'cd-draft-' . $tx->tx_id])
              @elseif($stage['key'] === 'notify_stakeholders')
                <div class="tc-msg">{{ $c['subject'] ?? '' }}{{ "\n\n" }}{{ $c['body'] ?? '' }}</div>
              @elseif($stage['key'] === 'notify_customer')
                @if(!empty($c['sent']))
                  <div class="tc-msg-meta">Sent to {{ $c['to'] ?? 'the client' }} · next renewal {{ $c['next_renewal_date'] ?? '—' }}</div>
                @elseif(!empty($c['cadence_skipped']))
                  <div class="tc-msg-meta" style="color:var(--db-text-muted)">Drafted, not sent — approved via "Approve &amp; proceed," already closed with the client outside AVA</div>
                @elseif(empty($c['to']))
                  <div class="tc-msg-meta" style="color:var(--db-text-muted)">Drafted, not sent — no client email on file</div>
                @else
                  <div class="tc-msg-meta" style="color:var(--db-text-muted)">Drafted, not sent — "Renewal Complete Notice to Client" is off in AVA Settings</div>
                @endif
                <div class="tc-msg">{{ $c['subject'] ?? '' }}{{ "\n\n" }}{{ $c['body'] ?? '' }}</div>
              @elseif($stage['key'] === 'archive_evidence' && !empty($c['path']))
                <a href="{{ route('app.transactions.archive-download', $tx->tx_id) }}" class="tc-btn tc-btn-ghost" style="display:inline-block;width:auto">Download PDF archive →</a>
              @elseif($stage['key'] === 'schedule_next_watch')
                <p style="font-size:12px;color:var(--db-text-muted)">
                  Watch log cleared{{ !empty($c['asset']) ? " for {$c['asset']}" : '' }} — this asset re-enters monitoring for its next cycle.
                </p>
              @else
                <div class="tc-field-row">
                  @foreach($c as $k => $v)
                    @if(is_bool($v))
                    <div class="tc-field"><span class="lbl">{{ ucwords(str_replace('_',' ',$k)) }}</span>{{ $v ? 'Yes' : 'No' }}</div>
                    @elseif(is_scalar($v) && $v !== null && $v !== '')
                    <div class="tc-field"><span class="lbl">{{ ucwords(str_replace('_',' ',$k)) }}</span>{{ $v }}</div>
                    @endif
                  @endforeach
                </div>
              @endif
            @endif

          </div>
          @endif
        </div>
        @endforeach
      </div>

      {{-- Footer actions --}}
      @if($canDismiss || $canDelete)
      <div class="td-footer">
        <div style="display:flex;align-items:center;gap:10px">
          @if($canDismiss)
          <form method="POST" action="{{ route('app.transactions.dismiss', $tx->tx_id) }}">
            @csrf
            <input type="hidden" name="reason" value="Manually dismissed from detail view">
            <button type="submit" onclick="return confirm('Dismiss this transaction? It will be removed from active queues but preserved in the audit log.')" class="td-btn td-btn-ghost">○ Dismiss</button>
          </form>
          @endif
          @if($canDelete)
          <form method="POST" action="{{ route('app.transactions.delete', $tx->tx_id) }}">
            @csrf @method('DELETE')
            <button type="submit" onclick="return confirm('Permanently delete this fast-track test transaction? This cannot be undone.')" class="td-btn td-btn-ghost" style="color:#f87171;border-color:rgba(239,68,68,.4)">✕ Delete</button>
          </form>
          @endif
        </div>
        <p class="td-footer-note">{{ trim(($canDismiss && !$isFastTrack ? 'Dismiss removes from active queues · ' : '') . ($canDelete ? 'Delete permanently removes test data' : ''), ' · ') }}</p>
      </div>
      @endif

    </div>
  </main>

  <aside class="mem-right" id="tc-right-panel">
    {{-- One panel per stage, pre-rendered server-side (buildStageList()
         already computed everything) and toggled with plain JS on stage-
         card click — matches this page's existing vanilla-JS pattern
         (theme toggle, menu dropdown), no new framework. Only stages with
         real additional detail get real content; the rest fall through to
         a neutral placeholder rather than being wired up empty. --}}
    <div class="tc-panel-empty" id="tc-panel-placeholder">
      <p>Click a stage to see its details here.</p>
    </div>
    @foreach($stages as $panelStage)
    <div class="tc-panel" id="tc-panel-{{ $panelStage['key'] }}" style="display:none">
      <div class="tc-panel-head">{{ $panelStage['label'] }}</div>
      @switch($panelStage['key'])
        @case('webhook')
          <div class="tc-field-row">
            <div class="tc-field"><span class="lbl">Detected via</span>{{ $panelStage['label'] }}</div>
            <div class="tc-field"><span class="lbl">When</span>{{ \Carbon\Carbon::parse($tx->created_at)->format('M j, Y · g:i A') }}</div>
          </div>
          <p style="font-size:12px;color:var(--db-text-muted);margin-top:8px">{{ $panelStage['sub'] }}</p>
          @break

        @case('read_email')
          @php $readContent = $panelStage['content'] ?? []; @endphp
          @if($readContent)
            <div class="tc-field-row">
              @foreach($readContent as $k => $v)
                @if(is_scalar($v) && $v !== null && $v !== '')
                <div class="tc-field"><span class="lbl">{{ ucwords(str_replace('_',' ',$k)) }}</span>{{ is_bool($v) ? ($v ? 'Yes' : 'No') : $v }}</div>
                @endif
              @endforeach
            </div>
          @else
            <p style="font-size:12px;color:var(--db-text-muted)">Not reached yet.</p>
          @endif
          @break

        @case('log_entry')
          @if($renewalRegisterRow)
            <div class="tc-field-row">
              <div class="tc-field"><span class="lbl">Category</span>{{ $renewalRegisterRow->category }}</div>
              <div class="tc-field"><span class="lbl">Asset</span>{{ $renewalRegisterRow->asset }}</div>
              <div class="tc-field"><span class="lbl">Client</span>{{ $renewalRegisterRow->client }}</div>
              <div class="tc-field"><span class="lbl">Contact</span>{{ $renewalRegisterRow->contact }}</div>
              <div class="tc-field"><span class="lbl">Priority</span>{{ $renewalRegisterRow->priority }}</div>
              <div class="tc-field"><span class="lbl">Status</span>{{ $renewalRegisterRow->status }}</div>
              @if($renewalRegisterRow->due_date)
              <div class="tc-field"><span class="lbl">Due Date</span>{{ \Carbon\Carbon::parse($renewalRegisterRow->due_date)->format('M j, Y') }}</div>
              @endif
            </div>
            <p style="font-size:11px;color:var(--db-text-muted);margin-top:8px">This is the actual renewal_register row this stage wrote.</p>
          @else
            <p style="font-size:12px;color:var(--db-text-muted)">Not reached yet.</p>
          @endif
          @break

        @default
          <p style="font-size:12px;color:var(--db-text-muted)">No additional detail for this stage yet.</p>
      @endswitch
    </div>
    @endforeach
  </aside>
  </div>

</div>{{-- ob-page --}}
</div>{{-- ob-shell --}}

<script>
(function () {
  document.getElementById('theme-toggle').addEventListener('click', function () {
    var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('unit-theme-v2', next);
  });

  var menuToggle = document.getElementById('menu-toggle');
  var menuDropdown = document.getElementById('menu-dropdown');
  menuToggle.addEventListener('click', function (e) {
    e.stopPropagation();
    menuDropdown.classList.toggle('open');
  });
  document.addEventListener('click', function (e) {
    if (!menuDropdown.contains(e.target) && e.target !== menuToggle) {
      menuDropdown.classList.remove('open');
    }
  });

  var txSelector = document.getElementById('tx-selector');
  if (txSelector) {
    txSelector.addEventListener('change', function () {
      if (this.value) window.location.href = this.value;
    });
  }
})();

// Every stage's panel is already rendered server-side (buildStageList()
// computed it) — this just shows the one that matches the clicked stage
// and hides the rest, plus highlights the active card. No AJAX round-trip.
function tcShowPanel(stageKey) {
  document.getElementById('tc-panel-placeholder').style.display = 'none';
  document.querySelectorAll('.tc-panel').forEach(function (el) {
    el.style.display = (el.id === 'tc-panel-' + stageKey) ? 'block' : 'none';
  });
  document.querySelectorAll('[data-stage-key]').forEach(function (el) {
    el.classList.toggle('tc-stage-active', el.dataset.stageKey === stageKey);
  });
}
</script>

@include('partials.tracking')
</body>
</html>

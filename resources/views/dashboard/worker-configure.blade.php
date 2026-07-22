<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Configure — {{ $dep->name }} — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
button,select,input,textarea{outline:none;font-family:inherit}
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

/* ── SHELL (identical to /app/workers/{slug}/billing, /app/desk/{slug}, etc.) ── */
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
.mem-card-area{display:grid;grid-template-columns:1fr;margin:12px 12px 12px 0;background:var(--db-card);border:1px solid var(--db-border);border-radius:20px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06)}
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
.ob-step.active .ob-step-num{background:var(--db-invert-bg);color:var(--db-invert-text);box-shadow:0 0 0 4px rgba(128,128,128,.15)}
.ob-step.done .ob-step-num{background:var(--db-invert-bg);color:var(--db-invert-text)}
.ob-step-body{padding-top:4px;padding-bottom:20px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:14px;font-weight:700;color:var(--db-text);line-height:1.2}
.ob-step.pending .ob-step-label{color:var(--db-text-muted)}
.ob-step-desc{font-size:12px;color:var(--db-text-muted);margin-top:2px;line-height:1.4;display:flex;align-items:center;gap:5px}
.ob-step.active .ob-step-body{background:var(--db-card);border:1.5px solid var(--db-border);border-radius:12px;padding:10px 14px;margin-right:-4px}

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

/* ── CONTENT ── */
.mem-main{overflow-y:auto;padding:28px 32px 60px}
.mem-wrap{max-width:1100px;margin:0 auto}
.mem-status{border-radius:12px;padding:10px 14px;font-size:13.5px;margin-bottom:16px}
.mem-status.success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#22c55e}

.cfg-h1{font-size:1.55rem;font-weight:900;letter-spacing:-.04em;color:var(--db-text)}
.cfg-sub{font-size:12.5px;color:var(--db-text-muted);margin-top:2px;margin-bottom:20px}
.cfg-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:20px;align-items:flex-start}
@media(max-width:900px){.cfg-grid{grid-template-columns:1fr}}

.cfg-card{border:1px solid var(--db-border);border-radius:16px;overflow:hidden;margin-bottom:16px;background:var(--db-bg)}
.cfg-card.danger{border-color:rgba(239,68,68,.3)}
.cfg-card-head{padding:16px 18px;border-bottom:1px solid var(--db-border)}
.cfg-card-head.danger-head{border-color:rgba(239,68,68,.3)}
.cfg-card-title{font-size:13.5px;font-weight:700;color:var(--db-text)}
.cfg-card-title.danger-title{color:#ef4444}
.cfg-card-sub{font-size:11.5px;color:var(--db-text-muted);margin-top:2px;line-height:1.5}
.cfg-card-body{padding:18px}

.cfg-label{font-size:11px;font-weight:600;color:var(--db-text-muted);text-transform:uppercase;letter-spacing:.04em;display:block;margin-bottom:6px}
.cfg-input,.cfg-select,.cfg-textarea{width:100%;background:var(--db-chip);color:var(--db-text);font-size:13px;border-radius:9px;padding:9px 12px;border:1px solid var(--db-border)}
.cfg-textarea{font-family:ui-monospace,monospace;resize:vertical}
.cfg-hint{font-size:11px;color:var(--db-text-muted);margin-top:6px;line-height:1.5}
.cfg-hint code{background:var(--db-chip);padding:1px 5px;border-radius:4px;font-size:10.5px}
.cfg-field{margin-bottom:16px}
.cfg-field:last-child{margin-bottom:0}
.cfg-row2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:640px){.cfg-row2{grid-template-columns:1fr}}

.cfg-btn{padding:9px 18px;border-radius:9px;border:none;font-size:12.5px;font-weight:700;cursor:pointer;font-family:inherit;background:var(--db-invert-bg);color:var(--db-invert-text)}
.cfg-btn:hover{opacity:.9}
.cfg-btn-danger{font-size:12px;font-weight:700;padding:9px 16px;border-radius:9px;border:1px solid rgba(239,68,68,.35);color:#ef4444;background:transparent;cursor:pointer;font-family:inherit}
.cfg-btn-danger:hover{background:rgba(239,68,68,.08)}
.cfg-btn-ghost{font-size:11.5px;font-weight:600;padding:6px 12px;border-radius:8px;border:1px solid var(--db-border);color:var(--db-text-muted);background:transparent;cursor:pointer;font-family:inherit}
.cfg-btn-ghost:hover{color:var(--db-text);border-color:var(--db-text-muted)}

/* Toggle */
.cfg-toggle-row{display:flex;align-items:center;gap:12px;cursor:pointer;user-select:none}
.cfg-toggle-track{width:38px;height:21px;border-radius:11px;position:relative;flex-shrink:0;transition:background .2s;background:var(--db-chip);border:1px solid var(--db-border)}
.cfg-toggle-track.on{background:var(--db-invert-bg)}
.cfg-toggle-knob{position:absolute;top:2px;left:2px;width:15px;height:15px;border-radius:50%;background:var(--db-invert-text);transition:left .2s}
.cfg-toggle-track.on .cfg-toggle-knob{left:19px;background:var(--db-invert-bg)}
[data-theme="dark"] .cfg-toggle-track.on .cfg-toggle-knob{background:var(--db-text)}

/* Persona picker */
.cfg-persona{display:flex;align-items:center;gap:12px;border:1px solid var(--db-border);border-radius:10px;padding:12px 14px;cursor:pointer;margin-bottom:8px;transition:all .15s}
.cfg-persona:last-child{margin-bottom:0}
.cfg-persona.selected{border-color:#F5C518;background:rgba(245,197,24,.06)}
.cfg-persona-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:var(--db-chip);color:var(--db-text-muted)}
.cfg-persona.selected .cfg-persona-icon{color:#B8890A;background:rgba(245,197,24,.15)}
.cfg-persona-title{font-size:13px;font-weight:600;color:var(--db-text)}
.cfg-persona-tag{font-size:11px;color:var(--db-text-muted)}
.cfg-persona-check{width:18px;height:18px;border-radius:50%;background:#F5C518;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.cfg-persona-badge{display:inline-flex;align-items:center;gap:6px;font-size:11px;padding:3px 10px;border-radius:99px;border:1px solid rgba(245,197,24,.3);background:rgba(245,197,24,.08);color:#B8890A}

/* Prompt overrides */
.cfg-stage{padding:18px 0;border-bottom:1px solid var(--db-border)}
.cfg-stage:last-child{border-bottom:none;padding-bottom:0}
.cfg-stage:first-child{padding-top:0}
.cfg-stage-head{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px}
.cfg-stage-name{font-size:13px;font-weight:700;color:var(--db-text)}
.cfg-stage-key{font-size:11px;color:var(--db-text-muted);font-family:monospace}
.cfg-badge{font-size:10.5px;font-weight:600;padding:2px 9px;border-radius:99px}
.cfg-badge-on{background:rgba(245,197,24,.12);color:#B8890A}
.cfg-badge-off{font-size:11px;color:var(--db-text-muted);text-decoration:underline;cursor:pointer;background:none;border:none;font-family:inherit}
.cfg-reset-btn{font-size:11px;color:#ef4444;background:none;border:none;cursor:pointer;font-family:inherit}
.cfg-test-btn{font-size:11.5px;font-weight:600;padding:7px 14px;border-radius:8px;border:1px solid var(--db-border);color:var(--db-text-muted);background:transparent;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px}
.cfg-test-btn:hover{color:var(--db-text);border-color:var(--db-text-muted)}
.cfg-test-btn:disabled{opacity:.4;cursor:not-allowed}
.cfg-test-result{margin-top:10px;background:var(--db-chip);border:1px solid var(--db-border);border-radius:10px;padding:12px 14px}
.cfg-test-result pre{white-space:pre-wrap;word-break:break-word;font-size:11.5px;font-family:monospace;color:var(--db-text)}

/* Model picker */
.cfg-provider-tag{font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:6px;margin-bottom:10px;display:inline-block}
.cfg-model{display:block;cursor:pointer;margin-bottom:6px}
.cfg-model-card{border-radius:11px;border:1.5px solid var(--db-border);padding:12px 14px;transition:all .15s}
.cfg-model.selected .cfg-model-card{border-color:#F5C518;background:rgba(245,197,24,.06)}
.cfg-model-name{font-size:12px;font-weight:700;color:var(--db-text)}
.cfg-model-tier{font-size:10px;font-weight:600;padding:2px 7px;border-radius:99px;margin-left:6px}
.cfg-model-cost{font-size:10.5px;color:var(--db-text-muted);margin-top:4px;font-family:monospace}
.cfg-model-radio{width:15px;height:15px;border-radius:50%;border:2px solid var(--db-border);flex-shrink:0}
.cfg-model.selected .cfg-model-radio{border-color:#F5C518;background:#F5C518}
.cfg-model-divider{border-top:1px solid var(--db-border);margin:14px 0}

/* Default prompt modal */
.cfg-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:60;display:none;align-items:center;justify-content:center;padding:20px}
.cfg-modal-overlay.open{display:flex}
.cfg-modal{background:var(--db-card);border:1px solid var(--db-border);border-radius:16px;width:100%;max-width:640px;max-height:80vh;display:flex;flex-direction:column}
.cfg-modal-head{padding:16px 18px;border-bottom:1px solid var(--db-border);display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.cfg-modal-close{background:none;border:none;color:var(--db-text-muted);font-size:18px;cursor:pointer;line-height:1}
.cfg-modal-body{padding:16px 18px;overflow-y:auto;flex:1}
.cfg-modal-label{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--db-text-muted);margin-bottom:6px}
.cfg-modal-pre{background:var(--db-chip);border-radius:9px;padding:10px 12px;font-size:11.5px;font-family:monospace;white-space:pre-wrap;word-break:break-word;color:var(--db-text);margin-bottom:14px}
.cfg-modal-foot{padding:14px 18px;border-top:1px solid var(--db-border);display:flex;gap:10px}

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
  ['Memory',       'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18', route('app.workers.memory',$dep->worker_slug), false],
  ['Templates',    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', route('app.workers.templates',['slug'=>$dep->worker_slug]), false],
  ['Rules',        'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', route('app.workers.rules',$dep->worker_slug), false],
  ['Configure',    'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', route('app.workers.configure', $dep->worker_slug), true],
  ['Fast Track',   'M13 10V3L4 14h7v7l9-11h-7z', route('app.workers.fast-track.page',$dep->worker_slug), false],
  ['Integrations', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1', route('app.workers.connect',$dep->worker_slug), false],
  ['Billing',      'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', route('app.workers.billing',$dep->worker_slug), false],
  ['Activity Log', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', route('app.workers.transactions',$dep->worker_slug), false],
];
$config        = json_decode($dep->config, true) ?? [];
$capture       = $config['capture'] ?? [];
$currentModel  = $config['ai_model'] ?? 'claude-sonnet-4-6';
$summaryHour   = (int) ($config['summary_hour'] ?? 8);
$summaryHours  = [
    6  => '6:00 AM — Early riser',
    7  => '7:00 AM — Before the day starts',
    8  => '8:00 AM — Morning briefing (recommended)',
    9  => '9:00 AM — After standup',
    12 => '12:00 PM — Midday check-in',
    17 => '5:00 PM — End of day recap',
    18 => '6:00 PM — After hours',
];
$catalogModels = \App\Platform\Services\LLM\ModelCatalog::all();

$kwLines  = implode("\n", $capture['capture_keywords']     ?? []);
$domLines = implode("\n", $capture['capture_domains']      ?? []);
$sndLines = implode("\n", $capture['capture_senders_only'] ?? []);
$excLines = implode("\n", $capture['exclude_senders']      ?? []);

$providerColors = [
    'anthropic' => ['color' => '#B8890A', 'bg' => 'rgba(245,197,24,.12)',  'border' => 'rgba(245,197,24,.3)'],
    'openai'    => ['color' => '#10b981', 'bg' => 'rgba(16,185,129,.12)', 'border' => 'rgba(16,185,129,.3)'],
    'kimi'      => ['color' => '#06b6d4', 'bg' => 'rgba(6,182,212,.12)',  'border' => 'rgba(6,182,212,.3)'],
    'google'    => ['color' => '#a855f7', 'bg' => 'rgba(168,85,247,.12)', 'border' => 'rgba(168,85,247,.3)'],
];
$tierColors = [
    'Fast'      => ['bg' => 'rgba(6,182,212,.15)',  'color' => '#0891b2'],
    'Balanced'  => ['bg' => 'rgba(245,197,24,.15)',  'color' => '#B8890A'],
    'Powerful'  => ['bg' => 'rgba(168,85,247,.15)','color' => '#7e22ce'],
    'Reasoning' => ['bg' => 'rgba(239,68,68,.15)', 'color' => '#dc2626'],
];
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
        $isActive = $wc->active && $wc->slug === $dep->worker_slug;
      @endphp
      <a href="{{ $wHref }}" class="ob-step {{ $isActive ? 'active' : ($wc->active ? 'done' : 'pending') }}" style="text-decoration:none{{ !$wc->active ? ';opacity:.5' : '' }}">
        <div class="ob-step-rail">
          <div class="ob-step-num" style="{{ !$isActive ? 'background:#E8E7E4;border:none;padding:0' : 'padding:0' }}">
            @if($wc->image)
              <img src="{{ $wc->image }}" style="width:100%;height:100%;object-fit:cover;display:block{{ !$wc->active ? ';filter:grayscale(1)' : '' }}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
              <span style="display:none;font-size:11px;font-weight:800;color:{{ $isActive?'#fff':'#6B7280' }};width:100%;height:100%;align-items:center;justify-content:center">{{ substr($wc->name,0,1) }}</span>
            @else
              <span style="font-size:11px;font-weight:800;color:{{ $isActive?'#fff':'#6B7280' }}">{{ substr($wc->name,0,1) }}</span>
            @endif
          </div>
        </div>
        <div class="ob-step-body">
          <div class="ob-step-label">{{ $wc->name }}</div>
          <div class="ob-step-desc">
            @if($wc->active)
              <span style="width:5px;height:5px;border-radius:50%;background:{{ $wDot }};flex-shrink:0;display:inline-block"></span>
              {{ $wc->role }}
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
      <p>Changes here only affect {{ $dep->name }}.</p>
    </div>
  </aside>

  {{-- ══ CONTENT ══ --}}
  <div class="mem-card-area">
  <main class="mem-main">
    <div class="mem-wrap">

      @if(session('success'))
      <div class="mem-status success">{{ session('success') }}</div>
      @endif

      <div class="cfg-h1">Configure — {{ $dep->name }}</div>
      <div class="cfg-sub">Deployment settings, capture rules, AI model, and prompt overrides for this worker.</div>

      <div class="cfg-grid">
        {{-- LEFT COLUMN --}}
        <div>

          {{-- Deployment Settings --}}
          <div class="cfg-card">
            <div class="cfg-card-head">
              <div class="cfg-card-title">Deployment Settings</div>
              <div class="cfg-card-sub">Core configuration for this worker instance</div>
            </div>
            <div class="cfg-card-body">
              <form method="POST" action="{{ route('app.workers.config', $dep->id) }}">
                @csrf @method('PATCH')
                <div class="cfg-field">
                  <label class="cfg-label">Deployment Name</label>
                  <input type="text" name="name" value="{{ $dep->name }}" required class="cfg-input">
                </div>
                <button type="submit" class="cfg-btn">Save Settings</button>
              </form>
            </div>
          </div>

          {{-- Daily Summary Timing --}}
          <div class="cfg-card">
            <div class="cfg-card-head">
              <div class="cfg-card-title">Daily Summary</div>
              <div class="cfg-card-sub">Choose when {{ $dep->name }} emails you a daily recap of what it processed. Only sent on days with activity.</div>
            </div>
            <div class="cfg-card-body">
              <form method="POST" action="{{ route('app.workers.config', $dep->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="name" value="{{ $dep->name }}">
                <input type="hidden" name="ai_model" value="{{ $currentModel }}">
                <div class="cfg-field">
                  <label class="cfg-label">Send daily summary at</label>
                  <select name="summary_hour" class="cfg-select">
                    @foreach($summaryHours as $hour => $label)
                    <option value="{{ $hour }}" {{ $summaryHour === $hour ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                  <p class="cfg-hint">Times are UTC. 8 AM UTC ≈ 4 AM ET / 1 AM PT — adjust based on your timezone.</p>
                </div>
                <button type="submit" class="cfg-btn">Save Timing</button>
              </form>
            </div>
          </div>

          {{-- Capture Guardrails --}}
          <div class="cfg-card">
            <div class="cfg-card-head">
              <div class="cfg-card-title">Capture Guardrails</div>
              <div class="cfg-card-sub">Filter which emails enter the pipeline. Emails that don't match are marked <code>filtered_out</code> — no AI runs, no cost.</div>
            </div>
            <div class="cfg-card-body">
              <form method="POST" action="{{ route('app.workers.config', $dep->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="name" value="{{ $dep->name }}">
                <input type="hidden" name="ai_model" value="{{ $currentModel }}">

                <div class="cfg-row2">
                  <div class="cfg-field">
                    <label class="cfg-label">Keywords</label>
                    <textarea name="capture_keywords" rows="4" placeholder="renew&#10;invoice&#10;expires&#10;subscription" class="cfg-textarea">{{ $kwLines }}</textarea>
                    <p class="cfg-hint">One per line. Email must contain at least one (subject or body). Leave blank to capture all.</p>
                  </div>
                  <div class="cfg-field">
                    <label class="cfg-label">Allowed Domains</label>
                    <textarea name="capture_domains" rows="4" placeholder="godaddy.com&#10;namecheap.com&#10;stripe.com" class="cfg-textarea">{{ $domLines }}</textarea>
                    <p class="cfg-hint">One per line. Sender must be from one of these. Leave blank to allow any domain.</p>
                  </div>
                </div>

                <div class="cfg-row2">
                  <div class="cfg-field">
                    <label class="cfg-label">Allowed Senders Only</label>
                    <textarea name="capture_senders_only" rows="3" placeholder="billing@godaddy.com&#10;alerts@stripe.com" class="cfg-textarea">{{ $sndLines }}</textarea>
                    <p class="cfg-hint">One per line. Only process emails from these exact addresses. Leave blank to allow any sender.</p>
                  </div>
                  <div class="cfg-field">
                    <label class="cfg-label">Excluded Senders</label>
                    <textarea name="exclude_senders" rows="3" placeholder="noreply@parking.com&#10;promo@ads.com" class="cfg-textarea">{{ $excLines }}</textarea>
                    <p class="cfg-hint">One per line. Always blocked — checked before all other rules.</p>
                  </div>
                </div>

                @php $requireAll = !empty($capture['capture_require_all']); @endphp
                <div class="cfg-field">
                  <label class="cfg-toggle-row">
                    <input type="checkbox" name="capture_require_all" value="1" {{ $requireAll ? 'checked' : '' }} id="toggle_require_all" style="position:absolute;opacity:0;pointer-events:none">
                    <span class="cfg-toggle-track {{ $requireAll ? 'on' : '' }}" id="toggle_require_all_track" onclick="document.getElementById('toggle_require_all').click(); updateToggle();">
                      <span class="cfg-toggle-knob"></span>
                    </span>
                    <span style="font-size:13px;color:var(--db-text)">
                      Require <strong>all</strong> keywords to match
                      <span style="color:var(--db-text-muted);font-size:11.5px"> (off = any match is enough)</span>
                    </span>
                  </label>
                </div>
                <script>
                function updateToggle() {
                  var cb = document.getElementById('toggle_require_all');
                  var track = document.getElementById('toggle_require_all_track');
                  setTimeout(function() { track.classList.toggle('on', cb.checked); }, 0);
                }
                </script>

                <div class="cfg-field">
                  <label class="cfg-label">Capture Scope</label>
                  <input type="text" name="capture_scope" value="{{ $capture['capture_scope'] ?? 'All incoming emails' }}" placeholder="Renewal and subscription emails only" class="cfg-input">
                  <p class="cfg-hint">Human-readable description shown on your dashboard.</p>
                </div>

                <button type="submit" class="cfg-btn">Save Guardrails</button>
              </form>
            </div>
          </div>

          {{-- Use Case / Persona --}}
          @php
            $personas       = $contract->personas();
            $currentPersona = $dep->persona ?? null;
            $personaIcons   = [
                'computer'  => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
                'shield'    => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
                'clipboard' => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
                'grid'      => '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>',
            ];
          @endphp
          @if(!empty($personas))
          <div class="cfg-card">
            <div class="cfg-card-head" style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
              <div>
                <div class="cfg-card-title">Use Case</div>
                <div class="cfg-card-sub">Changing this swaps your capture rules to match the selected use case.</div>
              </div>
              @if($currentPersona && isset($personas[$currentPersona]))
              <span class="cfg-persona-badge">
                {!! $personaIcons[$personas[$currentPersona]['icon'] ?? 'grid'] !!}
                {{ $personas[$currentPersona]['label'] }}
              </span>
              @endif
            </div>
            <div class="cfg-card-body">
              <form method="POST" action="{{ route('app.workers.persona', $dep->id) }}" id="persona-form">
                @csrf @method('PATCH')
                @foreach($personas as $key => $p)
                <label class="cfg-persona {{ $currentPersona === $key ? 'selected' : '' }}" onclick="selectPersona('{{ $key }}')" id="persona-label-{{ $key }}">
                  <input type="radio" name="persona" value="{{ $key }}" {{ $currentPersona === $key ? 'checked' : '' }} style="display:none">
                  <div class="cfg-persona-icon">{!! $personaIcons[$p['icon'] ?? 'grid'] !!}</div>
                  <div style="flex:1;min-width:0">
                    <div class="cfg-persona-title">{{ $p['label'] }}</div>
                    <div class="cfg-persona-tag">{{ $p['tagline'] }}</div>
                  </div>
                  <div class="cfg-persona-check" style="{{ $currentPersona === $key ? '' : 'display:none' }}" id="persona-check-{{ $key }}">
                    <svg width="10" height="10" viewBox="0 0 12 12" fill="none"><path d="M2 6l3 3 5-5" stroke="#0D0D0D" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </div>
                </label>
                @endforeach
                <button type="submit" class="cfg-btn" style="margin-top:10px">Save use case</button>
              </form>
              <script>
              function selectPersona(key) {
                document.querySelectorAll('.cfg-persona').forEach(function(el) { el.classList.remove('selected'); });
                document.querySelectorAll('[id^="persona-check-"]').forEach(function(el) { el.style.display = 'none'; });
                document.getElementById('persona-label-' + key).classList.add('selected');
                var check = document.getElementById('persona-check-' + key);
                if (check) check.style.display = 'flex';
              }
              </script>
            </div>
          </div>
          @endif

          {{-- Prompt Overrides --}}
          @if(!empty($pipelineStages))
          @php
            $hasLastTx = !is_null($lastTx);
            $lastTxRaw = $hasLastTx ? (json_decode($lastTx->raw_input, true) ?? []) : [];
          @endphp
          <div class="cfg-card" id="prompt-overrides-card">
            <div class="cfg-card-head">
              <div class="cfg-card-title">Prompt Overrides</div>
              <div class="cfg-card-sub">
                Customise what {{ $dep->name }} does at each AI stage. Click <strong>using default — view</strong> to see the built-in prompt before editing.
                @if($hasLastTx)
                  · "Test this prompt" uses your last email (from <span style="font-family:monospace">{{ $lastTxRaw['from'] ?? '?' }}</span>) as input.
                @else
                  · No transactions yet — send an email to your inbox to enable prompt testing.
                @endif
              </div>
            </div>
            <div class="cfg-card-body">
              <form method="POST" action="{{ route('app.workers.prompt-overrides', $dep->id) }}">
                @csrf

                @foreach($pipelineStages as $stage)
                @php
                  $stageKey    = $stage['key'];
                  $override    = $overrideRows[$stageKey] ?? null;
                  $hasJob      = !empty($stage['job_class']);
                  $defaults    = $defaultPrompts[$stageKey] ?? null;
                  $defaultSys  = $defaults['system']  ?? '';
                  $defaultUser = $defaults['user']    ?? '';
                  $isOverridden = $override && ($override->system_prompt || $override->user_prompt);
                @endphp
                @if($hasJob && ($defaultSys || $defaultUser))
                <div class="cfg-stage" data-stage="{{ $stageKey }}">
                  <div class="cfg-stage-head">
                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px">
                      <span class="cfg-stage-name">{{ $stage['label'] }}</span>
                      <span class="cfg-stage-key">{{ $stageKey }}</span>
                      @if($isOverridden)
                      <span class="cfg-badge cfg-badge-on">overridden</span>
                      <button type="button" onclick="showDefaultModal('{{ $stageKey }}', '{{ addslashes($stage['label']) }}')" class="cfg-badge-off">view default</button>
                      @else
                      <button type="button" onclick="showDefaultModal('{{ $stageKey }}', '{{ addslashes($stage['label']) }}')" class="cfg-badge-off">using default — view</button>
                      @endif
                    </div>
                    @if($isOverridden)
                    <button type="button" onclick="clearStage('{{ $stageKey }}')" class="cfg-reset-btn">Reset to default</button>
                    @endif
                  </div>

                  <div class="cfg-field">
                    <label class="cfg-label">System Prompt <span style="text-transform:none;font-weight:400">— who {{ $dep->name }} is in this stage</span></label>
                    <textarea id="sys_{{ $stageKey }}" name="stages[{{ $stageKey }}][system]" rows="3" placeholder="{{ $defaultSys ? 'Leave blank to use default...' : 'No default — enter a system prompt' }}" class="cfg-textarea">{{ $override?->system_prompt ?? '' }}</textarea>
                  </div>

                  <div class="cfg-field">
                    <label class="cfg-label">User Prompt <span style="text-transform:none;font-weight:400">— placeholders: <code>{RAW_EMAIL}</code> <code>{READ_OUTPUT}</code></span></label>
                    <textarea id="user_{{ $stageKey }}" name="stages[{{ $stageKey }}][user]" rows="6" placeholder="{{ $defaultUser ? 'Leave blank to use default...' : 'No default — enter a user prompt' }}" class="cfg-textarea">{{ $override?->user_prompt ?? '' }}</textarea>
                  </div>

                  <button type="button" class="cfg-test-btn" onclick="testPrompt('{{ $stageKey }}', {{ $dep->id }})" {{ $hasLastTx ? '' : 'disabled' }}>
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Test this prompt
                  </button>
                  <div id="test_result_{{ $stageKey }}" class="cfg-test-result" style="display:none"></div>
                </div>
                @endif
                @endforeach

                <div style="padding-top:14px;display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                  <button type="submit" class="cfg-btn">Save Prompt Overrides</button>
                  <p style="font-size:11px;color:var(--db-text-muted)">Clear both fields for a stage to revert it to the worker default.</p>
                </div>
              </form>
            </div>
          </div>

          {{-- Default prompt modal --}}
          <div id="default-modal" class="cfg-modal-overlay">
            <div class="cfg-modal">
              <div class="cfg-modal-head">
                <div>
                  <div class="cfg-card-title" id="modal-title">Default Prompt</div>
                  <div class="cfg-card-sub">Read-only — this is what {{ $dep->name }} uses when no override is set</div>
                </div>
                <button onclick="closeDefaultModal()" class="cfg-modal-close">✕</button>
              </div>
              <div class="cfg-modal-body">
                <div class="cfg-modal-label">System Prompt</div>
                <pre id="modal-system" class="cfg-modal-pre"></pre>
                <div class="cfg-modal-label">User Prompt</div>
                <pre id="modal-user" class="cfg-modal-pre"></pre>
              </div>
              <div class="cfg-modal-foot">
                <button onclick="useDefaultAsStartingPoint()" class="cfg-btn">Use as starting point</button>
                <button onclick="closeDefaultModal()" class="cfg-btn-ghost">Close</button>
              </div>
            </div>
          </div>
          @endif

          {{-- Danger zone --}}
          <div class="cfg-card danger">
            <div class="cfg-card-head danger-head">
              <div class="cfg-card-title danger-title">Danger Zone</div>
              <div class="cfg-card-sub">These actions are permanent and cannot be undone.</div>
            </div>
            <div class="cfg-card-body" style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap">
              <div>
                <p style="font-size:13px;font-weight:600;color:var(--db-text)">Remove this worker</p>
                <p style="font-size:11.5px;color:var(--db-text-muted);margin-top:2px">Deletes the deployment and all associated configuration. Transaction history is preserved.</p>
              </div>
              <form method="POST" action="{{ route('app.workers.destroy', $dep->id) }}" onsubmit="return confirm('Permanently remove {{ addslashes($dep->name) }}? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="cfg-btn-danger">Remove Worker</button>
              </form>
            </div>
          </div>

        </div>

        {{-- RIGHT COLUMN: AI Model --}}
        <div>
          <div class="cfg-card">
            <div class="cfg-card-head">
              <div class="cfg-card-title">AI Processing Model</div>
              <div class="cfg-card-sub">Choose the model powering this worker's pipeline</div>
            </div>
            <div class="cfg-card-body">
              <form method="POST" action="{{ route('app.workers.model', $dep->id) }}">
                @csrf @method('PATCH')

                @foreach($catalogModels as $providerKey => $provider)
                @php $pc = $providerColors[$providerKey] ?? $providerColors['anthropic']; @endphp
                <div>
                  <span class="cfg-provider-tag" style="background:{{ $pc['bg'] }};color:{{ $pc['color'] }};border:1px solid {{ $pc['border'] }}">{{ $provider['label'] }}</span>
                  @foreach($provider['models'] as $modelId => $mo)
                  @php
                    $selected = $currentModel === $modelId;
                    $tc = $tierColors[$mo['tier']] ?? $tierColors['Balanced'];
                  @endphp
                  <label class="cfg-model {{ $selected ? 'selected' : '' }}">
                    <input type="radio" name="ai_model" value="{{ $modelId }}" {{ $selected ? 'checked' : '' }} style="display:none">
                    <div class="cfg-model-card" style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
                      <div style="flex:1;min-width:0">
                        <span class="cfg-model-name">{{ $mo['name'] }}</span>
                        <span class="cfg-model-tier" style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }}">{{ $mo['tier'] }}</span>
                        @if(!empty($mo['recommended']))
                        <span class="cfg-model-tier" style="background:var(--db-chip);color:var(--db-text-muted)">recommended</span>
                        @endif
                        <div class="cfg-model-cost">in ${{ number_format($mo['cost_in'], 2) }} · out ${{ number_format($mo['cost_out'], 2) }} / M tokens</div>
                      </div>
                      <span class="cfg-model-radio" style="display:inline-block;margin-top:2px"></span>
                    </div>
                  </label>
                  @endforeach
                </div>
                @if(!$loop->last)<div class="cfg-model-divider"></div>@endif
                @endforeach

                @if($customModels->isNotEmpty())
                <div class="cfg-model-divider"></div>
                <div>
                  <span class="cfg-provider-tag" style="background:rgba(99,102,241,.12);color:#6366f1;border:1px solid rgba(99,102,241,.3)">Custom</span>
                  @foreach($customModels as $cm)
                  @php $selected = $currentModel === $cm->model_id; @endphp
                  <label class="cfg-model {{ $selected ? 'selected' : '' }}">
                    <input type="radio" name="ai_model" value="{{ $cm->model_id }}" {{ $selected ? 'checked' : '' }} style="display:none">
                    <div class="cfg-model-card" style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px">
                      <div style="flex:1;min-width:0">
                        <span class="cfg-model-name">{{ $cm->name }}</span>
                        <span class="cfg-model-tier" style="background:rgba(99,102,241,.15);color:#6366f1">Custom</span>
                        <div class="cfg-model-cost">{{ $cm->model_identifier }}</div>
                      </div>
                      <span class="cfg-model-radio" style="display:inline-block;margin-top:2px"></span>
                    </div>
                  </label>
                  @endforeach
                </div>
                @endif

                <button type="submit" class="cfg-btn" style="width:100%;margin-top:14px">Apply Model</button>
              </form>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>
  </div>

</div>{{-- ob-page --}}
</div>{{-- ob-shell --}}

<x-self-learn pageKey="dashboard.worker-configure" />

@if(!empty($pipelineStages))
<script>
const DEFAULT_PROMPTS = @json($defaultPrompts);
let _activeModalStage = null;

function showDefaultModal(stageKey, stageLabel) {
    const d = DEFAULT_PROMPTS[stageKey] ?? {};
    document.getElementById('modal-title').textContent = stageLabel + ' — Default Prompt';
    document.getElementById('modal-system').textContent = d.system || '(none)';
    document.getElementById('modal-user').textContent   = d.user   || '(none)';
    _activeModalStage = stageKey;
    document.getElementById('default-modal').classList.add('open');
}

function closeDefaultModal() {
    document.getElementById('default-modal').classList.remove('open');
    _activeModalStage = null;
}

function useDefaultAsStartingPoint() {
    if (!_activeModalStage) return;
    const d = DEFAULT_PROMPTS[_activeModalStage] ?? {};
    const sysEl  = document.getElementById('sys_'  + _activeModalStage);
    const userEl = document.getElementById('user_' + _activeModalStage);
    if (sysEl  && !sysEl.value)  sysEl.value  = d.system || '';
    if (userEl && !userEl.value) userEl.value = d.user   || '';
    closeDefaultModal();
    document.querySelector(`[data-stage="${_activeModalStage}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function clearStage(stageKey) {
    if (!confirm('Reset "' + stageKey + '" to default? Your override will be cleared.')) return;
    document.getElementById('sys_'  + stageKey).value = '';
    document.getElementById('user_' + stageKey).value = '';
    document.getElementById('sys_'  + stageKey).closest('form').requestSubmit();
}

document.getElementById('default-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDefaultModal();
});

async function testPrompt(stageKey, depId) {
    const sysEl    = document.getElementById('sys_'  + stageKey);
    const userEl   = document.getElementById('user_' + stageKey);
    const resultEl = document.getElementById('test_result_' + stageKey);
    const defaults = DEFAULT_PROMPTS[stageKey] ?? {};

    const systemVal = sysEl?.value.trim()  || defaults.system || '';
    const userVal   = userEl?.value.trim() || defaults.user   || '';

    if (!systemVal && !userVal) {
        resultEl.innerHTML = '<p style="color:#f59e0b;font-size:12px">Enter a prompt or the default will be used.</p>';
        resultEl.style.display = 'block';
        return;
    }

    resultEl.innerHTML = '<div style="display:flex;align-items:center;gap:8px;color:var(--db-text-muted);font-size:12px"><svg width="14" height="14" style="animation:spin 1s linear infinite" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Running against your last email…</div>';
    resultEl.style.display = 'block';

    try {
        const resp = await fetch(`/app/workers/${depId}/prompt-test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
            },
            body: JSON.stringify({ stage_key: stageKey, system: systemVal, user: userVal }),
        });
        const data = await resp.json();

        if (resp.status === 402 || data.gate === 'PROMPT_TEST_EXHAUSTED') {
            resultEl.innerHTML = `
                <p style="color:#f59e0b;font-size:12px;font-weight:600;margin-bottom:4px">Free tests used up</p>
                <p style="color:#f59e0b;font-size:12px">${escHtml(data.error ?? 'You have used all free prompt tests.')}</p>
                ${data.subscribe ? `<a href="${escHtml(data.subscribe)}" class="cfg-btn" style="display:inline-block;margin-top:8px;text-decoration:none">Subscribe to continue</a>` : ''}`;
            return;
        }

        if (!resp.ok || data.error) {
            resultEl.innerHTML = `
                <p style="color:#ef4444;font-size:12px;font-weight:600;margin-bottom:4px">Error</p>
                <p style="color:#ef4444;font-size:11.5px;font-family:monospace">${escHtml(data.error ?? 'Unknown error')}</p>`;
            return;
        }

        const txInfo = data.tx_used
            ? `<p style="color:var(--db-text-muted);font-size:11px;margin-top:8px">Tested against: <span style="font-family:monospace">${escHtml(data.tx_used.from)} — ${escHtml(data.tx_used.subject)}</span></p>`
            : '';

        const trialBadge = data.trial
            ? `<p style="color:var(--db-text-muted);font-size:11px;margin-top:4px">${data.trial.remaining} free test${data.trial.remaining !== 1 ? 's' : ''} remaining</p>`
            : '';

        const outputStr = typeof data.output === 'object'
            ? JSON.stringify(data.output, null, 2)
            : String(data.output);

        resultEl.innerHTML = `
            <p style="color:var(--db-text-muted);font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px">Output</p>
            <pre style="color:#16a34a">${escHtml(outputStr)}</pre>
            ${txInfo}
            ${trialBadge}`;
    } catch(e) {
        resultEl.innerHTML = `<p style="color:#ef4444;font-size:12px">Request failed: ${escHtml(e.message)}</p>`;
    }
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
<style>@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}</style>
@endif

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
})();
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>AVA's Desk — UNIT</title>
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{font-family:'Inter',sans-serif;background:#F4F3F1;color:#0D0D0D;-webkit-font-smoothing:antialiased}

/* ── PAGE SHELL (identical to onboarding) ── */
.ob-page{display:grid;grid-template-columns:260px 1fr;height:100vh;overflow:hidden}

/* ── SIDEBAR ── */
.ob-sidebar{background:#F4F3F1;display:flex;flex-direction:column;padding:32px 24px;overflow-y:auto}
.ob-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;margin-bottom:32px}

/* Worker list in sidebar */
.ob-workers-label{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:10px;display:flex;align-items:center;justify-content:space-between}
.ob-hire-btn{font-size:10px;font-weight:700;color:#0D0D0D;background:#fff;border:1px solid #E5E7EB;border-radius:6px;padding:3px 8px;text-decoration:none}
.ob-hire-btn:hover{background:#F4F3F1}
.ob-worker-card{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;text-decoration:none;margin-bottom:4px;border:1.5px solid transparent;transition:background .12s}
.ob-worker-card:hover{background:#fff}
.ob-worker-card.active{background:#fff;border-color:#E5E7EB}
.ob-worker-avatar{width:36px;height:36px;border-radius:9px;flex-shrink:0;overflow:hidden;background:#E8E7E4;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#6B7280}
.ob-worker-avatar img{width:100%;height:100%;object-fit:cover;display:block}
.ob-worker-name{font-size:12.5px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-worker-role{font-size:10.5px;color:#9CA3AF;margin-top:1px}
.ob-worker-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;margin-left:auto}

/* Links */
.ob-links-label{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:6px;margin-top:20px}
.ob-link{display:flex;align-items:center;gap:9px;padding:7px 10px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:500;color:#6B7280;transition:all .12s}
.ob-link:hover{background:#fff;color:#0D0D0D}
.ob-link svg{width:14px;height:14px;stroke:currentColor;stroke-width:1.8;fill:none;flex-shrink:0}

.ob-security{margin-top:auto;padding:14px 16px;border-radius:12px;background:#ECEAE6;border:1px solid #DCDCDC}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:5px}
.ob-security-row svg{width:13px;height:13px;stroke:#6B7280;flex-shrink:0;fill:none}
.ob-security-title{font-size:12px;font-weight:700;color:#0D0D0D}
.ob-security p{font-size:11px;color:#6B7280;line-height:1.55}

/* ── CARD AREA ── */
.ob-card-area{display:flex;align-items:center;justify-content:center;padding:20px 24px 20px 12px;overflow:hidden}
.ob-card{
  display:grid;grid-template-columns:1fr 290px;
  width:100%;height:100%;max-height:calc(100vh - 40px);
  border-radius:20px;overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);
  border:1px solid rgba(0,0,0,.07);
}

/* ── HERO / MAIN CONTENT ── */
.ob-hero{position:relative;overflow:hidden;background:#1e1b18;display:flex;flex-direction:column}
.ob-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 15%}
.ob-hero-fade{
  position:absolute;inset:0;z-index:1;pointer-events:none;
  background:linear-gradient(to right,#fff 0%,#fff 28%,rgba(255,255,255,.92) 40%,rgba(255,255,255,.5) 55%,rgba(255,255,255,.1) 72%,transparent 88%);
}
.ob-hero-content{
  position:relative;z-index:2;
  padding:24px 32px 20px;
  display:flex;flex-direction:column;height:100%;
  overflow-y:auto;
}
.ob-hero-content::-webkit-scrollbar{width:4px}
.ob-hero-content::-webkit-scrollbar-thumb{background:rgba(0,0,0,.1);border-radius:2px}

/* ON SHIFT badge */
.ob-onshift{display:flex;align-items:center;gap:7px;margin-bottom:18px;flex-shrink:0}
.ob-onshift-dot{width:7px;height:7px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
.ob-onshift-text{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#374151}
.ob-onshift-time{font-size:10px;color:#9CA3AF}

/* Title block */
.ob-desk-title{font-size:clamp(1.6rem,2.2vw,2.2rem);font-weight:900;letter-spacing:-.04em;line-height:1.05;color:#0D0D0D;margin-bottom:4px;flex-shrink:0}
.ob-desk-role{font-size:13px;font-weight:600;color:#6B7280;margin-bottom:4px;flex-shrink:0}
.ob-desk-desc{font-size:12.5px;color:#374151;line-height:1.6;margin-bottom:20px;flex-shrink:0;max-width:420px}

/* Stats row */
.ob-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:18px;flex-shrink:0}
.ob-stat{
  background:rgba(255,255,255,.92);border:1px solid rgba(0,0,0,.07);
  border-radius:12px;padding:12px 14px;backdrop-filter:blur(4px);
}
.ob-stat-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:8px}
.ob-stat-icon svg{width:14px;height:14px;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.ob-stat-num{font-size:22px;font-weight:900;color:#0D0D0D;line-height:1}
.ob-stat-lbl{font-size:10px;color:#9CA3AF;margin-top:2px}

/* Bottom two panels */
.ob-bottom{display:grid;grid-template-columns:1fr 1fr;gap:10px;flex:1;min-height:0}
.ob-panel{
  background:rgba(255,255,255,.9);border:1px solid rgba(0,0,0,.07);
  border-radius:14px;padding:16px;backdrop-filter:blur(4px);
  display:flex;flex-direction:column;overflow:hidden;
}
.ob-panel-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-shrink:0}
.ob-panel-title{font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#0D0D0D}
.ob-panel-link{font-size:11px;font-weight:600;color:#9CA3AF;text-decoration:none}
.ob-panel-link:hover{color:#0D0D0D}

/* On-shift pill */
.ob-shift-pill{display:flex;align-items:center;gap:4px;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#15803D;background:#DCFCE7;border-radius:99px;padding:3px 7px}
.ob-shift-pill-dot{width:5px;height:5px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}

/* Activity list */
.ob-act{overflow-y:auto;flex:1}
.ob-act-item{display:flex;gap:9px;align-items:flex-start;padding:6px 0;border-bottom:1px solid #F5F5F3}
.ob-act-item:last-child{border-bottom:none}
.ob-act-node{width:14px;height:14px;border-radius:50%;flex-shrink:0;margin-top:2px;display:flex;align-items:center;justify-content:center}
.ob-act-text{font-size:11.5px;font-weight:600;color:#0D0D0D;line-height:1.3}
.ob-act-sub{font-size:10.5px;color:#9CA3AF;margin-top:1px}
.ob-act-time{font-size:10px;color:#9CA3AF;white-space:nowrap;margin-left:auto;padding-left:6px;flex-shrink:0}
.ob-view-all{font-size:11px;font-weight:600;color:#9CA3AF;text-decoration:none;display:block;margin-top:8px;flex-shrink:0}
.ob-view-all:hover{color:#0D0D0D}

/* Approval badge */
.ob-ap-badge{font-size:9.5px;font-weight:700;background:rgba(245,197,24,.2);color:#92400E;border-radius:99px;padding:2px 7px;white-space:nowrap;flex-shrink:0}

/* Approval items */
.ob-ap-list{overflow-y:auto;flex:1}
.ob-ap-item{padding:10px 12px;border-radius:10px;background:#F9F9F8;border:1px solid #EEEDE9;margin-bottom:8px}
.ob-ap-item:last-child{margin-bottom:0}
.ob-ap-row{display:flex;align-items:flex-start;justify-content:space-between;gap:6px;margin-bottom:7px}
.ob-ap-av{width:28px;height:28px;border-radius:7px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.ob-ap-name{font-size:12px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-ap-plan{font-size:11px;color:#6B7280;margin-top:1px}
.ob-ap-btns{display:flex;gap:5px}
.btn-approve{flex:1;padding:7px;border-radius:7px;background:#0D0D0D;color:#fff;border:none;font-size:11.5px;font-weight:700;cursor:pointer;font-family:inherit;transition:opacity .15s;width:100%}
.btn-approve:hover{opacity:.85}
.btn-edit{flex:1;padding:7px;border-radius:7px;background:#fff;color:#374151;border:1.5px solid #E5E7EB;font-size:11.5px;font-weight:700;cursor:pointer;font-family:inherit;text-decoration:none;display:flex;align-items:center;justify-content:center;transition:border-color .15s}
.btn-edit:hover{border-color:#0D0D0D}
.ob-ap-footer{font-size:11px;font-weight:600;color:#9CA3AF;text-decoration:none;display:block;margin-top:8px;flex-shrink:0}
.ob-ap-footer:hover{color:#0D0D0D}

/* Current task bubble */
.ob-bubble{
  position:absolute;z-index:3;bottom:24px;right:24px;
  background:#fff;border:1px solid #E5E7EB;
  border-radius:16px 16px 4px 16px;
  padding:14px 18px;width:200px;
  box-shadow:0 4px 16px rgba(0,0,0,.1);
}
.ob-bubble-label{font-size:9px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#9CA3AF;margin-bottom:6px;display:flex;align-items:center;justify-content:space-between}
.ob-bubble-title{font-size:13px;font-weight:700;color:#0D0D0D;margin-bottom:8px;line-height:1.3}
.ob-bubble-bar{height:4px;background:#F3F4F6;border-radius:99px;overflow:hidden;margin-bottom:3px}
.ob-bubble-fill{height:100%;border-radius:99px;background:#6366f1}
.ob-bubble-pct{font-size:10px;color:#9CA3AF;text-align:right}

/* ── RIGHT PANEL ── */
.ob-profile{background:#fff;border-left:1px solid #F0F0F0;padding:24px 20px;display:flex;flex-direction:column;overflow-y:auto}
.emp-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:8px}
.emp-name{font-size:1.5rem;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;line-height:1}
.emp-role{font-size:12.5px;color:#374151;margin-top:4px;margin-bottom:14px}
.emp-divider{border:none;border-top:1px solid #F0F0F0;margin:0 0 14px}
.emp-section-label{font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin-bottom:8px}

/* Status */
.emp-status-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.emp-status-dot{width:7px;height:7px;border-radius:50%;display:inline-block;margin-right:5px}
.emp-status-label{font-size:13px;font-weight:700}
.emp-status-since{font-size:10.5px;color:#9CA3AF}

/* Task */
.emp-task-title{font-size:12.5px;font-weight:700;color:#0D0D0D;margin-bottom:2px}
.emp-task-sub{font-size:11px;color:#9CA3AF;margin-bottom:8px}
.emp-bar{height:4px;background:#F3F4F6;border-radius:99px;overflow:hidden;margin-bottom:3px}
.emp-bar-fill{height:100%;border-radius:99px;background:#6366f1}
.emp-bar-pct{font-size:10px;color:#9CA3AF;text-align:right;margin-bottom:14px}

/* Impact */
.emp-impact-row{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.emp-impact-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.emp-impact-val{font-size:20px;font-weight:900;color:#0D0D0D;line-height:1;margin:2px 0}
.emp-impact-sub{font-size:10.5px;color:#9CA3AF}

/* Memory */
.emp-mem-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:5px}
.emp-mem-bar{height:4px;background:#F3F4F6;border-radius:99px;overflow:hidden;margin-bottom:5px}
.emp-mem-sub{font-size:10.5px;color:#9CA3AF;margin-bottom:14px}

/* AVA's note */
.emp-note{font-size:12px;color:#374151;line-height:1.65;font-style:italic;margin-bottom:8px}
.emp-note-sig{font-size:12px;font-weight:700;color:#0D0D0D;display:flex;align-items:center;justify-content:space-between;margin-top:auto}
.emp-note-sig svg{width:16px;height:16px;fill:#FDA4AF}

@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}

/* ══ MOBILE ══ */
@media(max-width:768px){
  html,body{height:auto;overflow:auto}

  /* Page becomes single column, sidebar hidden */
  .ob-page{display:block;height:auto;overflow:visible}
  .ob-sidebar{display:none}

  /* Card area fills screen width, no padding constraints */
  .ob-card-area{padding:0;display:block;overflow:visible}
  .ob-card{display:block;border-radius:0;border:none;box-shadow:none;height:auto;max-height:none}

  /* ── MOBILE TOP NAV ── */
  .mob-nav{display:flex;align-items:center;justify-content:space-between;padding:16px 20px 12px;background:#fff;position:sticky;top:0;z-index:50;border-bottom:1px solid #F0F0F0}
  .mob-nav-logo{font-size:20px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D}
  .mob-avatars{display:flex;align-items:center}
  .mob-av{width:34px;height:34px;border-radius:50%;border:2.5px solid #fff;overflow:hidden;margin-left:-8px;background:#E8E7E4;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#6B7280;flex-shrink:0}
  .mob-av img{width:100%;height:100%;object-fit:cover;display:block}
  .mob-av-count{width:34px;height:34px;border-radius:50%;border:2.5px solid #fff;background:#0D0D0D;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;margin-left:-8px;flex-shrink:0}

  /* ── MOBILE HERO CARD ── */
  .mob-hero-card{position:relative;margin:12px 16px;border-radius:20px;overflow:hidden;min-height:260px;background:#1e1b18}
  .mob-hero-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 10%}
  .mob-hero-fade{position:absolute;inset:0;background:linear-gradient(to right,rgba(0,0,0,.75) 0%,rgba(0,0,0,.6) 40%,rgba(0,0,0,.1) 70%,transparent 100%)}
  .mob-hero-content{position:relative;z-index:2;padding:22px 20px 22px;max-width:65%}
  .mob-onshift{display:inline-flex;align-items:center;gap:6px;background:#0D0D0D;border-radius:99px;padding:5px 10px;margin-bottom:14px}
  .mob-onshift-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
  .mob-onshift-text{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#fff}
  .mob-hero-title{font-size:1.6rem;font-weight:900;letter-spacing:-.04em;color:#fff;line-height:1.1;margin-bottom:8px}
  .mob-hero-desc{font-size:12.5px;color:rgba(255,255,255,.8);line-height:1.55;margin-bottom:18px}
  .mob-hero-btns{display:flex;flex-direction:column;gap:8px}
  .mob-btn-primary{display:flex;align-items:center;justify-content:center;gap:6px;background:#0D0D0D;color:#fff;border:none;border-radius:12px;padding:12px 18px;font-size:14px;font-weight:700;font-family:inherit;cursor:pointer;text-decoration:none}
  .mob-btn-secondary{display:flex;align-items:center;justify-content:center;gap:6px;background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:12px;padding:11px 18px;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;text-decoration:none;backdrop-filter:blur(8px)}

  /* Ava speech bubble on hero */
  .mob-bubble{position:absolute;z-index:3;bottom:16px;right:12px;background:#fff;border-radius:14px 14px 4px 14px;padding:11px 14px;max-width:160px;box-shadow:0 4px 16px rgba(0,0,0,.15)}
  .mob-bubble-text{font-size:12px;font-weight:600;color:#0D0D0D;line-height:1.45}

  /* ── MOBILE STATS ── */
  .mob-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:12px 16px 0}
  .mob-stats-row2{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:8px 16px 0}
  .mob-stat{background:#fff;border:1px solid #EEEDE9;border-radius:14px;padding:14px 14px 12px;display:flex;align-items:flex-start;gap:10px}
  .mob-stat-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .mob-stat-icon svg{width:15px;height:15px;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
  .mob-stat-num{font-size:20px;font-weight:900;color:#0D0D0D;line-height:1}
  .mob-stat-lbl{font-size:10px;color:#9CA3AF;margin-top:1px;line-height:1.3}

  /* ── MOBILE APPROVALS ── */
  .mob-section{padding:20px 16px 0}
  .mob-section-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
  .mob-section-title{font-size:17px;font-weight:800;color:#0D0D0D;letter-spacing:-.02em}
  .mob-section-badge{font-size:12px;font-weight:700;background:#6366f1;color:#fff;border-radius:99px;padding:2px 9px;margin-left:6px}
  .mob-section-link{font-size:13px;font-weight:600;color:#6366f1;text-decoration:none}
  .mob-ap-item{background:#fff;border:1px solid #EEEDE9;border-radius:14px;padding:14px;margin-bottom:10px}
  .mob-ap-top{display:flex;align-items:center;gap:12px;margin-bottom:6px}
  .mob-ap-av{width:40px;height:40px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff}
  .mob-ap-av img{width:100%;height:100%;object-fit:cover;border-radius:50%}
  .mob-ap-name{font-size:14px;font-weight:700;color:#0D0D0D}
  .mob-ap-company{font-size:12px;color:#6B7280;margin-top:1px}
  .mob-ap-badge{font-size:10px;font-weight:700;color:#6366f1;background:#EEF2FF;border-radius:99px;padding:3px 8px;white-space:nowrap;margin-left:auto;flex-shrink:0}
  .mob-ap-plan{font-size:12.5px;color:#374151;margin-bottom:10px;line-height:1.4}
  .mob-ap-value{font-weight:700;color:#0D0D0D}
  .mob-ap-btns{display:grid;grid-template-columns:1fr 1fr;gap:8px}
  .mob-btn-approve{padding:11px;border-radius:10px;background:#0D0D0D;color:#fff;border:none;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit}
  .mob-btn-edit{padding:11px;border-radius:10px;background:#fff;color:#374151;border:1.5px solid #E5E7EB;font-size:13.5px;font-weight:700;cursor:pointer;font-family:inherit;text-decoration:none;display:flex;align-items:center;justify-content:center}

  /* ── MOBILE AVA NOTE ── */
  .mob-note-card{margin:20px 16px 32px;background:#F4F3F1;border-radius:16px;padding:18px 20px}
  .mob-note-eyebrow{display:flex;align-items:center;gap:8px;margin-bottom:10px}
  .mob-note-icon{width:28px;height:28px;border-radius:8px;background:#0D0D0D;display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .mob-note-label{font-size:9.5px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF}
  .mob-note-text{font-size:14px;color:#374151;line-height:1.7;font-style:italic;margin-bottom:12px}
  .mob-note-sig{display:flex;align-items:center;justify-content:space-between}
  .mob-note-sig-name{font-size:13px;font-weight:700;color:#0D0D0D}
  .mob-note-heart svg{width:18px;height:18px;fill:#FDA4AF}

  /* Hide desktop elements on mobile */
  .ob-hero,.ob-profile,.ob-bubble{display:none}
  /* Show mobile-only elements */
  .mob-only{display:block}
}

/* Hide mobile elements on desktop */
@media(min-width:769px){
  .mob-only{display:none !important}
  .mob-nav{display:none}
}
</style>
</head>
<body>

@php
$activityMap = [
  'draft_ready' =>['label'=>'Reply ready for approval', 'sub'=>'Ready for your review',          'color'=>'#22c55e'],
  'approved'    =>['label'=>'Reply approved',           'sub'=>'Sent to customer',                'color'=>'#22c55e'],
  'sent'        =>['label'=>'Reply sent',               'sub'=>'Delivered successfully',          'color'=>'#22c55e'],
  'failed'      =>['label'=>'Pipeline error',           'sub'=>'Needs your attention',            'color'=>'#ef4444'],
  'reading'     =>['label'=>'Reading incoming email',   'sub'=>'Parsing content',                 'color'=>'#6366f1'],
  'classifying' =>['label'=>'Classifying email',       'sub'=>'Identifying renewal type',        'color'=>'#f59e0b'],
  'drafting'    =>['label'=>'Drafting renewal reply',  'sub'=>'Preparing personalized response', 'color'=>'#8b5cf6'],
  'ingesting'   =>['label'=>'Renewal request detected','sub'=>'Email received',                  'color'=>'#6366f1'],
];
$apColors = ['#6366f1','#f59e0b','#22c55e','#f97316','#8b5cf6','#ec4899'];
@endphp

{{-- ══════════════════════════════════════════════
     MOBILE LAYOUT (hidden on desktop via CSS)
══════════════════════════════════════════════ --}}
<div class="mob-only">

  {{-- Top Nav --}}
  <nav class="mob-nav">
    <span class="mob-nav-logo">UNIT</span>
    <div class="mob-avatars">
      @foreach($allDeployments->take(4) as $wd)
      @php $wReg2=$registryRows->get($wd->worker_slug);$wImg2=$wReg2?->profile_image?asset('storage/'.$wReg2->profile_image):null; @endphp
      <div class="mob-av">@if($wImg2)<img src="{{ $wImg2 }}" alt="">@else{{ strtoupper(substr($wd->worker_slug,0,1)) }}@endif</div>
      @endforeach
      @if($allDeployments->count()>4)<div class="mob-av-count">{{ $allDeployments->count()-4 }}</div>@endif
    </div>
  </nav>

  {{-- Hero card --}}
  <div class="mob-hero-card">
    @if($coverImg)<img src="{{ $coverImg }}" alt="AVA" class="mob-hero-img">@endif
    <div class="mob-hero-fade"></div>
    <div class="mob-hero-content">
      <div class="mob-onshift">
        <span class="mob-onshift-dot"></span>
        <span class="mob-onshift-text">On Shift</span>
      </div>
      <h1 class="mob-hero-title">Ava is on shift.</h1>
      <p class="mob-hero-desc">She's monitoring your inbox and will alert you when action is needed.</p>
      <div class="mob-hero-btns">
        <a href="{{ route('transactions') }}" class="mob-btn-primary">Go to Desk &rarr;</a>
        <a href="#" class="mob-btn-secondary">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor" stroke="none"/></svg>
          Watch Ava in action
        </a>
      </div>
    </div>
    <div class="mob-bubble">
      <p class="mob-bubble-text">I've got it from here, {{ $firstName }}. I'll keep you posted!</p>
    </div>
  </div>

  {{-- Stats row 1: 3 cols --}}
  <div class="mob-stats">
    @foreach([
      ['M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z','#6366f1',$incomingCount,'Incoming','New emails'],
      ['M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15','#f59e0b',$inProgressCount,'In Progress','Working on it'],
      ['M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','#8b5cf6',$waitingCount,'Waiting','Approval'],
    ] as [$ico,$clr,$val,$t,$s])
    <div class="mob-stat" style="flex-direction:column;gap:8px">
      <div class="mob-stat-icon" style="background:{{ $clr }}18"><svg viewBox="0 0 24 24" style="stroke:{{ $clr }}"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ico }}"/></svg></div>
      <div>
        <div class="mob-stat-num">{{ $val }}</div>
        <div class="mob-stat-lbl">{{ $t }}<br>{{ $s }}</div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Stats row 2: 2 cols --}}
  <div class="mob-stats-row2">
    <div class="mob-stat" style="flex-direction:column;gap:8px">
      <div class="mob-stat-icon" style="background:#22c55e18"><svg viewBox="0 0 24 24" style="stroke:#22c55e"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
      <div>
        <div class="mob-stat-num">{{ $completedCount }}</div>
        <div class="mob-stat-lbl">Completed<br>Today</div>
      </div>
    </div>
    <div class="mob-stat" style="flex-direction:column;gap:8px">
      <div class="mob-stat-icon" style="background:#22c55e18">
        <span style="width:7px;height:7px;border-radius:50%;background:#22c55e;display:block;animation:pdot 1.4s ease infinite"></span>
      </div>
      <div>
        <div class="mob-stat-num" style="font-size:16px;color:#22c55e">Active</div>
        <div class="mob-stat-lbl">Monitoring<br>inbox 24/7</div>
      </div>
    </div>
  </div>

  {{-- Approvals --}}
  <div class="mob-section">
    <div class="mob-section-hd">
      <div style="display:flex;align-items:center">
        <span class="mob-section-title">Approvals</span>
        @if($waitingCount>0)<span class="mob-section-badge">{{ $waitingCount }}</span>@endif
      </div>
      <a href="{{ route('transactions',['filter'=>'draft_ready']) }}" class="mob-section-link">View all</a>
    </div>

    @forelse($approvals as $tx)
    @php
      $cl2=json_decode($tx->classify_output??'{}',true);
      $apN2=$cl2['client']??$cl2['sender_name']??'Unknown';
      $apC2=$cl2['company']??$cl2['organization']??'';
      $apP2=$cl2['plan']??$cl2['product']??'Renewal';
      $apV2=$cl2['contract_value']??$cl2['renewal_value']??'';
      $apClr2=$apColors[abs(crc32($apN2))%count($apColors)];
    @endphp
    <div class="mob-ap-item">
      <div class="mob-ap-top">
        <div class="mob-ap-av" style="background:{{ $apClr2 }}">{{ strtoupper(substr($apN2,0,1)) }}</div>
        <div>
          <div class="mob-ap-name">{{ $apN2 }}</div>
          <div class="mob-ap-company">{{ $apC2 ?: 'Client' }}</div>
        </div>
        <span class="mob-ap-badge">Draft ready</span>
      </div>
      <div class="mob-ap-plan">{{ $apP2 }}{{ $apV2?' — <span class="mob-ap-value">'.$apV2.'</span>':'' }}</div>
      <div class="mob-ap-btns">
        <form method="POST" action="{{ route('transactions.decide',$tx->id) }}">
          @csrf<input type="hidden" name="decision" value="approve">
          <button type="submit" class="mob-btn-approve">Approve</button>
        </form>
        <a href="{{ route('transactions.show',$tx->id) }}" class="mob-btn-edit">Edit</a>
      </div>
    </div>
    @empty
    <p style="font-size:13px;color:#9CA3AF;padding:12px 0">Nothing awaiting approval right now.</p>
    @endforelse
  </div>

  {{-- AVA's Note --}}
  @php
    $txTotal2=\Illuminate\Support\Facades\DB::table('transactions')->where('deployment_id',$depId)->count();
    $avaNote2=$txTotal2===0
      ?"Ready and standing by, {$firstName}. Give me something to work on."
      :($waitingCount>0
        ?"I found {$waitingCount} renewal ".($waitingCount===1?'opportunity':'opportunities')." ready for your approval. Tomorrow I'll protect even more."
        :"All caught up. I'll flag anything that needs your attention.");
  @endphp
  <div class="mob-note-card">
    <div class="mob-note-eyebrow">
      <div class="mob-note-icon">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22"/></svg>
      </div>
      <span class="mob-note-label">AVA's Note</span>
    </div>
    <p class="mob-note-text">{{ $avaNote2 }}</p>
    <div class="mob-note-sig">
      <span class="mob-note-sig-name">— Ava</span>
      <div class="mob-note-heart"><svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></div>
    </div>
  </div>

</div>
{{-- ══ END MOBILE ══ --}}

<div class="ob-page">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ob-sidebar">
    <div class="ob-logo">UNIT</div>

    <div class="ob-workers-label">
      MY WORKERS
      <a href="{{ route('workers.page') }}" class="ob-hire-btn">+ Hire</a>
    </div>

    @foreach($allDeployments as $wd)
    @php
      $wReg  = $registryRows->get($wd->worker_slug);
      $wImg  = $wReg?->profile_image ? asset('storage/'.$wReg->profile_image) : null;
      $wDot  = $wd->status === 'active' ? '#22c55e' : '#f59e0b';
      $wHref = $wd->worker_slug === 'ava' ? route('desk.ava') : '#';
      $wRole = $wReg->tagline ?? ucfirst($wd->worker_slug).' Specialist';
    @endphp
    <a href="{{ $wHref }}" class="ob-worker-card {{ $wd->worker_slug === 'ava' ? 'active' : '' }}">
      <div class="ob-worker-avatar">
        @if($wImg)<img src="{{ $wImg }}" alt="">@else{{ strtoupper(substr($wd->worker_slug,0,1)) }}@endif
      </div>
      <div>
        <div class="ob-worker-name">{{ strtoupper($wd->worker_slug) }}</div>
        <div class="ob-worker-role">{{ $wRole }}</div>
      </div>
      <span class="ob-worker-dot" style="background:{{ $wDot }}"></span>
    </a>
    @endforeach

    <div class="ob-links-label">LINKS</div>
    @foreach([
      ['Knowledge Base','M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253','#'],
      ['Templates','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',route('workers.templates',['slug'=>'ava'])],
      ['Integrations','M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1','#'],
      ['Billing & Plans','M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',route('billing')],
      ['Team Members','M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z','#'],
      ['Activity Log','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',route('transactions')],
    ] as [$lbl,$ico,$href])
    <a href="{{ $href }}" class="ob-link">
      <svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ico }}"/></svg>
      {{ $lbl }}
    </a>
    @endforeach

    <div class="ob-security" style="margin-top:auto">
      <div class="ob-security-row">
        <svg viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>You're in control of what<br>Ava can see and access.</p>
    </div>
  </aside>

  {{-- ══ CARD AREA ══ --}}
  <div class="ob-card-area">
    <div class="ob-card">

      {{-- ── HERO / CONTENT ── --}}
      <div class="ob-hero">
        @if($coverImg)
          <img src="{{ $coverImg }}" alt="AVA's Desk" class="ob-hero-img">
        @else
          <div style="position:absolute;inset:0;background:linear-gradient(135deg,#1a1a2e,#2d1b69)"></div>
        @endif
        <div class="ob-hero-fade"></div>

        <div class="ob-hero-content">

          {{-- On shift badge --}}
          <div class="ob-onshift">
            <span class="ob-onshift-dot"></span>
            <span class="ob-onshift-text">ON SHIFT</span>
            <span class="ob-onshift-time">{{ now()->format('g:i A') }}</span>
          </div>

          {{-- Title --}}
          <h1 class="ob-desk-title">AVA's Desk</h1>
          <p class="ob-desk-role">Renewal Specialist</p>
          <p class="ob-desk-desc">Ava is working on protecting your renewals and building stronger customer relationships.</p>

          {{-- Stats --}}
          <div class="ob-stats">
            @foreach([
              ['M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z','#6366f1',$incomingCount,  'New emails'],
              ['M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15','#f59e0b',$inProgressCount,'Working on it'],
              ['M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','#8b5cf6',$waitingCount,  'Waiting approval'],
              ['M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z','#22c55e',$completedCount,'Completed today'],
            ] as [$ico,$clr,$val,$lbl])
            <div class="ob-stat">
              <div class="ob-stat-icon" style="background:{{ $clr }}18">
                <svg viewBox="0 0 24 24" style="stroke:{{ $clr }}"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ico }}"/></svg>
              </div>
              <div class="ob-stat-num">{{ $val }}</div>
              <div class="ob-stat-lbl">{{ $lbl }}</div>
            </div>
            @endforeach
          </div>

          {{-- Live Activity + Approvals --}}
          <div class="ob-bottom">

            {{-- Live Activity --}}
            <div class="ob-panel">
              <div class="ob-panel-hd">
                <span class="ob-panel-title">Live Activity</span>
                <span class="ob-shift-pill"><span class="ob-shift-pill-dot"></span> On Shift</span>
              </div>
              <div class="ob-act">
                @forelse($activity as $tx)
                @php
                  $am = $activityMap[$tx->status]??['label'=>ucfirst(str_replace('_',' ',$tx->status)),'sub'=>'','color'=>'#9CA3AF'];
                  $cl = json_decode($tx->classify_output??'{}',true);
                  $txc = $cl['client']??$cl['sender_name']??'';
                @endphp
                <div class="ob-act-item">
                  <div class="ob-act-node" style="background:{{ $am['color'] }}22;border:1.5px solid {{ $am['color'] }}55">
                    <svg width="6" height="6" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="{{ $am['color'] }}"/></svg>
                  </div>
                  <div style="flex:1;min-width:0">
                    <div class="ob-act-text">{{ $am['label'] }}</div>
                    <div class="ob-act-sub">{{ $txc ?: $am['sub'] }}</div>
                  </div>
                  <span class="ob-act-time">{{ \Carbon\Carbon::parse($tx->created_at)->format('g:i A') }}</span>
                </div>
                @empty
                <p style="font-size:12px;color:#9CA3AF;text-align:center;padding:20px 0">No activity yet</p>
                @endforelse
              </div>
              <a href="{{ route('transactions') }}" class="ob-view-all">View all activity →</a>
            </div>

            {{-- Approvals --}}
            <div class="ob-panel">
              <div class="ob-panel-hd">
                <div style="display:flex;align-items:center;gap:7px">
                  <span class="ob-panel-title">Approvals</span>
                  @if($waitingCount>0)<span style="font-size:10px;font-weight:700;background:#F5C518;color:#000;border-radius:99px;padding:1px 6px">{{ $waitingCount }}</span>@endif
                </div>
                <a href="{{ route('transactions',['filter'=>'draft_ready']) }}" class="ob-panel-link">View all →</a>
              </div>
              <div class="ob-ap-list">
                @forelse($approvals as $tx)
                @php
                  $cl = json_decode($tx->classify_output??'{}',true);
                  $apN = $cl['client']??$cl['sender_name']??'Unknown';
                  $apP = $cl['plan']??$cl['product']??'Renewal';
                  $apV = $cl['contract_value']??$cl['renewal_value']??'';
                  $apC = $apColors[abs(crc32($apN))%count($apColors)];
                @endphp
                <div class="ob-ap-item">
                  <div class="ob-ap-row">
                    <div style="display:flex;align-items:center;gap:8px;min-width:0">
                      <div class="ob-ap-av" style="background:{{ $apC }}">{{ strtoupper(substr($apN,0,1)) }}</div>
                      <div style="min-width:0">
                        <div class="ob-ap-name">{{ $apN }}</div>
                        <div class="ob-ap-plan">{{ $apP }}{{ $apV?' — '.$apV:'' }}</div>
                      </div>
                    </div>
                    <span class="ob-ap-badge">Draft ready</span>
                  </div>
                  <div class="ob-ap-btns">
                    <form method="POST" action="{{ route('transactions.decide',$tx->id) }}" style="flex:1">
                      @csrf<input type="hidden" name="decision" value="approve">
                      <button type="submit" class="btn-approve">Approve</button>
                    </form>
                    <a href="{{ route('transactions.show',$tx->id) }}" class="btn-edit">Edit</a>
                  </div>
                </div>
                @empty
                <p style="font-size:12px;color:#9CA3AF;text-align:center;padding:20px 0">Nothing awaiting approval</p>
                @endforelse
              </div>
              <a href="{{ route('transactions',['filter'=>'draft_ready']) }}" class="ob-ap-footer">{{ $waitingCount }} draft{{ $waitingCount===1?'':'s' }} waiting →</a>
            </div>

          </div>
        </div>

        {{-- Current task bubble --}}
        @if($currentTask)
        @php
          $bt = match($currentTask->status){
            'reading','ingesting','classifying'=>'Reading email',
            'drafting','pushing'=>'Drafting reply',
            'draft_ready'=>'Reply awaiting approval',
            'approved','sent'=>'Reply sent',
            default=>ucfirst(str_replace('_',' ',$currentTask->status)),
          };
          $bp = in_array($currentTask->status,['approved','sent','draft_ready'])?100:67;
        @endphp
        <div class="ob-bubble">
          <div class="ob-bubble-label">
            CURRENT TASK
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </div>
          <div class="ob-bubble-title">{{ $bt }}</div>
          <div class="ob-bubble-bar"><div class="ob-bubble-fill" style="width:{{ $bp }}%"></div></div>
          <div class="ob-bubble-pct">{{ $bp }}%</div>
        </div>
        @endif
      </div>

      {{-- ── RIGHT PANEL ── --}}
      <div class="ob-profile">
        <div class="emp-eyebrow">First Assignment</div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
          @if($profileImg)
          <img src="{{ $profileImg }}" alt="AVA" style="width:36px;height:36px;border-radius:9px;object-fit:cover;border:1.5px solid #F0F0EE;flex-shrink:0">
          @endif
          <div>
            <div class="emp-name">AVA</div>
            <div class="emp-role">Renewal Specialist</div>
          </div>
        </div>

        <hr class="emp-divider">

        <div class="emp-section-label">Work Status</div>
        <div class="emp-status-row">
          <div>
            <span class="emp-status-dot" style="background:{{ $dep->status==='active'?'#22c55e':'#f59e0b' }};animation:{{ $dep->status==='active'?'pdot 1.4s ease infinite':'none' }}"></span>
            <span class="emp-status-label" style="color:{{ $dep->status==='active'?'#22c55e':'#f59e0b' }}">{{ $workStatus }}</span>
          </div>
          <span class="emp-status-since">Since {{ \Carbon\Carbon::parse($dep->updated_at)->format('g:i A') }}</span>
        </div>

        @if($currentTask)
        @php
          $rt = match($currentTask->status){
            'reading','ingesting','classifying'=>'Reading incoming email',
            'drafting','pushing'=>'Drafting renewal reply',
            'draft_ready'=>'Reply awaiting approval',
            'approved','sent'=>'All caught up',
            default=>ucfirst(str_replace('_',' ',$currentTask->status)),
          };
          $rc = json_decode($currentTask->classify_output??'{}',true);
          $rcl = $rc['client']??$rc['sender_name']??'';
          $rp = in_array($currentTask->status,['approved','sent','draft_ready'])?100:67;
        @endphp
        <div class="emp-section-label">Current Task</div>
        <div class="emp-task-title">{{ $rt }}</div>
        @if($rcl)<div class="emp-task-sub">Customer: {{ $rcl }}</div>@else<div style="margin-bottom:8px"></div>@endif
        <div class="emp-bar"><div class="emp-bar-fill" style="width:{{ $rp }}%"></div></div>
        <div class="emp-bar-pct">{{ $rp }}%</div>
        @endif

        <hr class="emp-divider">

        <div class="emp-section-label">Today's Impact</div>
        <div class="emp-impact-row">
          <div class="emp-impact-icon" style="background:#FEF3C7">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <div style="font-size:9px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#9CA3AF">Renewals Managed</div>
            <div class="emp-impact-val">{{ $incomingCount }}</div>
            <div class="emp-impact-sub">{{ $completedCount }} completed today</div>
          </div>
        </div>

        <hr class="emp-divider">

        <div class="emp-section-label">Memory Access</div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
          <div style="width:26px;height:26px;border-radius:7px;background:#EDE9FE;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round"><path d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
          </div>
          <div style="flex:1">
            <div class="emp-mem-row">
              <span style="font-size:11.5px;font-weight:600;color:#374151">Memory</span>
              <span style="font-size:11.5px;font-weight:700;color:#0D0D0D">{{ $clientCount }} clients</span>
            </div>
            <div class="emp-mem-bar"><div style="height:100%;border-radius:99px;background:#8b5cf6;width:{{ min(100,$clientCount*20) }}%"></div></div>
          </div>
        </div>
        <div class="emp-mem-sub">Ava knows {{ $clientCount }} {{ $clientCount===1?'client':'clients' }}.</div>

        <div class="emp-section-label">Memory &amp; Responsibility</div>
        <div class="emp-mem-row">
          <span style="font-size:12.5px;font-weight:700;color:#0D0D0D">{{ $clientCount }} / {{ max(5,$clientCount+3) }} Clients</span>
          <span style="font-size:10px;color:#9CA3AF">{{ $clientCount<3?'Light':($clientCount<8?'Medium':'Heavy') }} Workload</span>
        </div>
        <div class="emp-mem-bar"><div style="height:100%;border-radius:99px;background:#F5C518;width:{{ min(100,($clientCount/max(5,$clientCount+3))*100) }}%"></div></div>

        <hr class="emp-divider" style="margin-top:14px">

        @php
          $txTotal = \Illuminate\Support\Facades\DB::table('transactions')->where('deployment_id',$depId)->count();
          $avaNote = $txTotal===0
            ? "Ready and standing by, {$firstName}. Give me something to work on."
            : ($waitingCount>0
              ? "I've drafted {$waitingCount} ".($waitingCount===1?'reply':'replies')." for you to review. Let me know what you think."
              : "All caught up. I'll flag anything that needs your attention.");
        @endphp
        <div class="emp-section-label">AVA's Note</div>
        <p class="emp-note">{{ $avaNote }}</p>
        <div class="emp-note-sig">
          <span>— Ava</span>
          <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </div>
      </div>

    </div>
  </div>

</div>
</body>
</html>

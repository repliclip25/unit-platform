<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>First Assignment — UNIT</title>
<link rel="icon" type="image/png" href="/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{font-family:'Inter',sans-serif;background:#F4F3F1;color:#0D0D0D;-webkit-font-smoothing:antialiased}

.ob-page{display:grid;grid-template-columns:260px 1fr;height:100vh;overflow:hidden}

/* ── SIDEBAR ── */
.ob-sidebar{background:#F4F3F1;display:flex;flex-direction:column;padding:32px 24px;overflow-y:auto}
.ob-logo{font-size:21px;font-weight:900;letter-spacing:-.04em;color:#0D0D0D;margin-bottom:44px}
.ob-steps{display:flex;flex-direction:column;flex:1}
.ob-step{display:flex;align-items:flex-start;gap:14px;position:relative;text-decoration:none;color:inherit}
.ob-step:not(:last-child) .ob-step-rail::after{content:'';position:absolute;left:13px;top:30px;width:2px;height:calc(100% - 6px);background:#DCDCDC;border-radius:2px}
.ob-step.done:not(:last-child) .ob-step-rail::after{background:#0D0D0D}
.ob-step-rail{position:relative;flex-shrink:0;display:flex;flex-direction:column;align-items:center;padding-bottom:32px}
.ob-step:last-child .ob-step-rail{padding-bottom:0}
.ob-step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;position:relative;z-index:1;flex-shrink:0}
.ob-step.pending .ob-step-num{background:#E8E7E4;color:#888;border:1.5px solid #DCDCDC}
.ob-step.active  .ob-step-num{background:#0D0D0D;color:#fff;box-shadow:0 0 0 4px rgba(0,0,0,.1)}
.ob-step.done    .ob-step-num{background:#22c55e;color:#fff}
.ob-step-body{padding-top:4px;padding-bottom:28px}
.ob-step:last-child .ob-step-body{padding-bottom:0}
.ob-step-label{font-size:13.5px;font-weight:700;color:#0D0D0D;line-height:1.2}
.ob-step.pending .ob-step-label{color:#6B7280}
.ob-step-desc{font-size:12px;color:#9CA3AF;margin-top:3px;line-height:1.4}
.ob-step.active .ob-step-desc{color:#374151}
.ob-step.active .ob-step-body{background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:10px 14px;margin-right:-4px}
.ob-security{margin-top:8px;padding:14px 16px;border-radius:12px;background:#ECEAE6;border:1px solid #DCDCDC}
.ob-security-row{display:flex;align-items:center;gap:7px;margin-bottom:5px}
.ob-security-row svg{width:13px;height:13px;stroke:#6B7280;flex-shrink:0}
.ob-security-title{font-size:12px;font-weight:700;color:#0D0D0D}
.ob-security p{font-size:11px;color:#6B7280;line-height:1.55}

/* ── MAIN AREA ── */
.ob-main{display:flex;align-items:stretch;padding:20px 24px 20px 12px;overflow:hidden;gap:0;background:#EEECEA}

/* Wide card — horizontal pipeline */
.ob-pipeline-card{
  display:grid;
  grid-template-columns:280px 1fr 1fr 1fr 230px;
  width:100%;
  border-radius:20px;overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);
  border:1px solid rgba(0,0,0,.07);
  background:#fff;
}

/* ── SUCCESS CARD — header bar on top (badge/message/CTAs), canvas +
   Ava rail below getting the full width instead of sharing it with a
   dedicated left column. ── */
.ob-success-card{
  display:none;width:100%;flex-direction:column;
  border-radius:20px;overflow:hidden;
  box-shadow:0 2px 12px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.03);
  border:1px solid rgba(0,0,0,.07);
  background:#fff;
  height:calc(100vh - 40px);
}
.ob-success-card.is-visible{display:flex}

/* Header bar */
.ob-sc-header{
  flex:0 0 auto;background:#F4F3F1;border-bottom:1px solid #E8E7E4;
  padding:18px 24px;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;
}
.ob-sc-header-text{display:flex;align-items:center;gap:16px;flex-wrap:wrap;min-width:0}
.ob-sc-badge{
  display:inline-flex;align-items:center;gap:6px;
  background:#0D0D0D;color:#fff;border-radius:99px;
  font-size:9.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  padding:5px 12px;width:fit-content;flex-shrink:0;
}
.ob-sc-badge-dot{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
.ob-sc-h1{font-size:1.2rem;font-weight:900;letter-spacing:-.03em;line-height:1.15;color:#0D0D0D}
.ob-sc-sub{font-size:12px;color:#6B7280;line-height:1.5;max-width:340px}
.ob-sc-btns{display:flex;align-items:center;gap:10px;flex-shrink:0}
.btn-dash{
  display:flex;align-items:center;justify-content:center;gap:8px;
  padding:10px 16px;border-radius:11px;white-space:nowrap;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  font-size:12.5px;font-weight:800;font-family:inherit;text-decoration:none;
  transition:opacity .15s;
}
.btn-dash:hover{opacity:.88}
.btn-dash svg{width:14px;height:14px;stroke:#fff;stroke-width:2.5;fill:none}
.btn-watch{
  display:flex;align-items:center;justify-content:center;gap:8px;
  padding:9px 16px;border-radius:11px;white-space:nowrap;
  background:#fff;color:#374151;border:1.5px solid #E5E7EB;cursor:pointer;
  font-size:12.5px;font-weight:700;font-family:inherit;text-decoration:none;
  transition:border-color .15s;
}
.btn-watch:hover{border-color:#0D0D0D;color:#0D0D0D}
.btn-watch svg{width:14px;height:14px;stroke:currentColor;stroke-width:2;fill:none}

/* Content row: canvas + right rail, below the header */
.ob-sc-content{flex:1;display:grid;grid-template-columns:minmax(0,1fr) 360px;min-height:0}

/* Success canvas — center stage, now the live run-through instead of a
   static hero photo. The pipeline itself is the main event on this page. */
.ob-sc-canvas{
  position:relative;overflow-y:auto;background:#F4F3F1;
  padding:24px 28px;display:flex;flex-direction:column;
}

/* Ava mini-hero — moved into the right rail, above the draft card */
.ob-sc-hero-mini{
  position:relative;overflow:hidden;background:#1a1a2e;
  flex:0 0 240px;
}
.ob-sc-hero-mini img{
  width:100%;height:100%;object-fit:cover;object-position:center 20%;
  display:block;
}
.ob-sc-nameplate{
  position:absolute;bottom:16px;left:18px;
  background:rgba(0,0,0,.65);backdrop-filter:blur(8px);
  border-radius:9px;padding:7px 12px;
}
.ob-sc-nameplate-name{font-size:11px;font-weight:800;color:#fff;letter-spacing:.02em}
.ob-sc-nameplate-title{font-size:9.5px;color:rgba(255,255,255,.6);font-weight:500;letter-spacing:.05em;text-transform:uppercase}

/* Success right: Ava mini-hero + draft */
.ob-sc-right{
  display:flex;flex-direction:column;border-left:1px solid #F0F0F0;overflow:hidden;
}
.ob-sc-activity-header{
  display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex:0 0 auto;
}
.ob-sc-activity-title{font-size:11px;font-weight:800;color:#0D0D0D;letter-spacing:.08em;text-transform:uppercase}
.ob-sc-onshift{
  display:flex;align-items:center;gap:5px;
  font-size:9px;font-weight:700;color:#15803D;letter-spacing:.08em;text-transform:uppercase;
  background:#DCFCE7;border-radius:99px;padding:3px 8px;
}
.ob-sc-onshift-dot{width:5px;height:5px;border-radius:50%;background:#22c55e;animation:pdot 1.4s ease infinite}
.ob-sc-view-link{font-size:11px;color:#9CA3AF;font-weight:600;text-decoration:none;display:block;margin-top:14px;flex:0 0 auto}
.ob-sc-view-link:hover{color:#0D0D0D}

/* Draft card fills the remaining height */
#scDraftCard{flex:1;display:flex;flex-direction:column;overflow:hidden;border-top:1px solid #E8EAED}
#scDraftCard > div:not(:last-child){flex-shrink:0}
/* Draft chrome bar */
.sc-draft-chrome{background:#F1F3F4;border-bottom:1px solid #E0E0E0;padding:7px 12px;display:flex;align-items:center;gap:7px;flex-shrink:0}
/* Draft body grows */
.sc-draft-body{flex:1;overflow-y:auto;padding:12px 16px}
/* Email header rows */
.sc-draft-header-row{display:flex;align-items:baseline;gap:6px;padding:5px 0;border-bottom:1px solid #F1F3F4}
.sc-draft-header-label{font-size:11px;color:#5F6368;font-weight:600;width:48px;flex-shrink:0}
.sc-draft-header-value{font-size:12px;color:#202124;font-weight:500;line-height:1.4}
.sc-draft-subject-row{padding:8px 0 10px;border-bottom:1px solid #E8EAED;margin-bottom:10px}
.sc-draft-subject-text{font-size:14px;font-weight:700;color:#202124;line-height:1.3}
.sc-draft-preview{font-size:12.5px;color:#3C4043;line-height:1.75;white-space:pre-wrap}
/* Draft actions */
.sc-draft-actions{padding:12px 18px;border-top:1px solid #E0E0E0;display:flex;gap:8px;flex-shrink:0}
.sc-draft-actions button,.sc-draft-actions a{flex:1;padding:10px;border-radius:9px;font-size:12px;font-weight:700;text-align:center;cursor:pointer}
.sc-btn-approve{background:#0D0D0D;color:#fff;border:none;font-family:inherit}
.sc-btn-review{background:#fff;color:#374151;border:1.5px solid #E5E7EB;text-decoration:none;display:flex;align-items:center;justify-content:center}

/* ── 9-section live run-through grid — now the main canvas, not a sidebar
   list. Mirrors the approved onshift_extended_9_sections mockup, but wired
   to real transaction data via polling (fastTrackStatus), not scripted.
   Stages 8 (Fulfill) and 9 (Confirm & renew) each bundle several real
   pipeline stages — sub-items make that bundling visible instead of
   hiding it behind one vague label. ── */
.ob-9grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;flex:1}
.ob-9cell{
  display:flex;flex-direction:column;gap:10px;padding:16px 18px;border-radius:14px;
  background:#fff;border:1.5px solid #E8E7E4;transition:background .3s,border-color .3s;
}
.ob-9cell-head{display:flex;align-items:center;gap:10px}
.ob-9cell.is-active{background:#FFFBEA;border-color:#F5C518}
.ob-9cell.is-done{background:#F0FDF4;border-color:#BBF7D0}
.ob-9cell.is-human{background:#FFFBEA;border-color:#F5C518}
.ob-9dot{width:28px;height:28px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;background:#E5E7EB;color:#9CA3AF;transition:background .3s,color .3s}
.ob-9cell.is-active .ob-9dot{background:#F5C518;color:#0D0D0D;animation:pdot 1s ease infinite}
.ob-9cell.is-done .ob-9dot{background:#22c55e;color:#fff}
.ob-9label{font-size:13.5px;font-weight:800;color:#9CA3AF;line-height:1.25}
.ob-9cell.is-active .ob-9label,.ob-9cell.is-done .ob-9label{color:#0D0D0D}
.ob-9sub{font-size:11px;color:#C4C7CC;font-weight:600;margin-top:1px}
.ob-9cell.is-active .ob-9sub{color:#92400e}
.ob-9cell.is-done .ob-9sub{color:#16A34A}

/* Sub-items — only present on the Fulfill and Confirm & renew cells */
.ob-9subitems{display:flex;flex-direction:column;gap:6px;padding-left:38px}
.ob-9subitem{display:flex;align-items:center;gap:7px;font-size:11px;font-weight:600;color:#C4C7CC}
.ob-9subitem-dot{width:6px;height:6px;border-radius:50%;background:#E5E7EB;flex-shrink:0}
.ob-9subitem.is-active{color:#92400e}
.ob-9subitem.is-active .ob-9subitem-dot{background:#F5C518;animation:pdot 1s ease infinite}
.ob-9subitem.is-done{color:#16A34A}
.ob-9subitem.is-done .ob-9subitem-dot{background:#22c55e}

@media(min-width:1025px){
  .ob-9grid{grid-template-columns:repeat(3,1fr);grid-auto-rows:minmax(96px,auto)}
}

/* ── LEFT: Intro + input ── */
.ob-left{
  background:#F4F3F1;border-right:1px solid #E8E7E4;
  padding:32px 24px;display:flex;flex-direction:column;overflow-y:auto;
}
.ob-step-tag{
  display:inline-flex;align-items:center;gap:8px;
  font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;
  color:#6B7280;margin-bottom:14px;
}
.ob-step-tag svg{width:14px;height:14px;stroke:#6B7280;stroke-width:2;fill:none}
.ob-h1{font-size:1.5rem;font-weight:900;letter-spacing:-.04em;line-height:1.1;color:#0D0D0D;margin-bottom:8px}
.ob-gold{color:#0D0D0D;position:relative;display:inline}
.ob-gold::after{content:"";position:absolute;left:0;right:0;bottom:-3px;height:4px;background:#F5C518;border-radius:2px}
.ob-sub{font-size:12.5px;color:#374151;line-height:1.65;margin-bottom:20px}

/* Drop zone */
.ob-dropzone{
  border:2px dashed #D1D5DB;border-radius:14px;
  padding:20px 16px;text-align:center;
  background:#fff;cursor:pointer;transition:border-color .15s;
  margin-bottom:10px;flex-shrink:0;
}
.ob-dropzone:hover{border-color:#0D0D0D}
.ob-dropzone-icon{width:36px;height:36px;border-radius:10px;border:1.5px solid #E5E7EB;background:#F9FAFB;display:flex;align-items:center;justify-content:center;margin:0 auto 10px}
.ob-dropzone-icon svg{width:18px;height:18px;stroke:#9CA3AF;stroke-width:1.8;fill:none}
.ob-dropzone-title{font-size:12.5px;font-weight:700;color:#374151;margin-bottom:3px}
.ob-dropzone-hint{font-size:11px;color:#9CA3AF}
#emailPaste{width:100%;min-height:80px;border:1.5px solid #E5E7EB;border-radius:10px;padding:10px;font-size:12px;font-family:inherit;color:#0D0D0D;resize:none;outline:none;margin-top:8px;display:none}
#emailPaste:focus{border-color:#0D0D0D}

.ob-inbox-btn{
  display:flex;align-items:center;justify-content:center;gap:7px;
  width:100%;padding:10px;border-radius:10px;
  border:1.5px solid #E5E7EB;background:#fff;cursor:pointer;
  font-size:12px;font-weight:600;color:#374151;font-family:inherit;
  transition:border-color .15s;margin-bottom:16px;
}
.ob-inbox-btn:hover{border-color:#0D0D0D}
.ob-inbox-btn svg{width:14px;height:14px;stroke:#6B7280;stroke-width:2;fill:none}

/* Run button */
.btn-run{
  display:flex;align-items:center;justify-content:center;gap:8px;
  width:100%;padding:12px;border-radius:12px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  font-size:13px;font-weight:800;font-family:inherit;
  transition:opacity .15s;margin-top:auto;
}
.btn-run:hover{opacity:.88}
.btn-run svg{width:15px;height:15px;stroke:#fff;stroke-width:2.5;fill:none}
.btn-run .spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:none}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── PIPELINE STAGE PANELS ── */
.ob-stage{
  display:flex;flex-direction:column;
  border-right:1px solid #F0F0F0;
  overflow:hidden;position:relative;
  transition:background .3s;
}
.ob-stage:last-of-type{border-right:none}

.ob-stage-header{
  padding:18px 18px 0;flex-shrink:0;
}
.ob-stage-num{
  font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
  color:#9CA3AF;margin-bottom:6px;
}
.ob-stage-title{font-size:14px;font-weight:800;color:#0D0D0D;margin-bottom:0}

/* AVA image area */
.ob-stage-img{
  flex:1;position:relative;overflow:hidden;min-height:0;max-height:220px;
  background:#F4F3F1;
}
.ob-stage-img img{width:100%;height:100%;object-fit:cover;object-position:center top;opacity:.35;transition:opacity .5s}
.ob-stage.is-active .ob-stage-img img,.ob-stage.is-done .ob-stage-img img{opacity:1}

/* ── SUCCESS PANEL (replaces stage 1+2 on completion) ── */
.ob-success-panel{
  display:none;flex-direction:column;align-items:center;justify-content:center;
  border-right:1px solid #F0F0F0;background:#F4F3F1;overflow:hidden;
  position:relative;text-align:center;
}
.ob-success-panel.is-visible{display:flex}
.ob-success-panel img{
  width:100%;height:220px;object-fit:cover;object-position:center top;
  border-bottom:1px solid #E8E7E4;
}
.ob-success-msg{padding:20px 24px}
.ob-success-msg h3{font-size:15px;font-weight:800;color:#0D0D0D;margin-bottom:6px}
.ob-success-msg p{font-size:11.5px;color:#6B7280;line-height:1.6}
.ob-success-badge{
  display:inline-flex;align-items:center;gap:6px;
  background:#DCFCE7;color:#15803D;border-radius:99px;
  font-size:10px;font-weight:700;padding:4px 10px;margin-bottom:10px;
}
.ob-success-badge svg{width:11px;height:11px;stroke:currentColor;stroke-width:2.5;fill:none}

/* Pulse overlay when active */
.ob-stage.is-active .ob-stage-img::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(to bottom,transparent 60%,rgba(255,255,255,.6));
}

/* Stage footer */
.ob-stage-footer{
  padding:12px 18px;flex-shrink:0;
  border-top:1px solid #F3F4F6;background:#fff;
}
.ob-progress-bar{height:3px;background:#F3F4F6;border-radius:99px;overflow:hidden;margin-bottom:6px}
.ob-progress-fill{height:100%;background:#F5C518;border-radius:99px;width:0;transition:width 1s ease}
.ob-stage.is-done .ob-progress-fill{background:#22c55e;width:100%}
.ob-stage-status{font-size:11px;color:#9CA3AF;font-weight:600;display:flex;align-items:center;gap:5px}
.ob-stage-status-dot{width:6px;height:6px;border-radius:50%;background:#D1D5DB}
.ob-stage.is-active .ob-stage-status-dot{background:#F5C518;animation:pdot 1s ease infinite}
.ob-stage.is-done .ob-stage-status-dot{background:#22c55e}
@keyframes pdot{0%,100%{opacity:1}50%{opacity:.3}}

/* ── Per-stage sub-steps within a column — one row per raw contract stage,
   e.g. "Analyzing" lists Read Email / Classify / Memory Lookup / Log
   Transaction. Driven by AvaWorker::pipelineStages() via
   PipelineStageService::onboardingColumns(). ── */
.ob-substeps{display:flex;flex-direction:column;gap:7px;margin-top:2px}
.ob-substep{display:flex;align-items:center;gap:8px;font-size:11px;color:#9CA3AF;font-weight:600;transition:color .2s}
.ob-substep-num{width:16px;height:16px;border-radius:50%;background:#F3F4F6;color:#9CA3AF;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:800;flex-shrink:0;transition:background .2s,color .2s}
.ob-substep.is-active{color:#0D0D0D}
.ob-substep.is-active .ob-substep-num{background:#F5C518;color:#0D0D0D;animation:pdot 1s ease infinite}
.ob-substep.is-done{color:#0D0D0D}
.ob-substep.is-done .ob-substep-num{background:#22c55e;color:#fff}

/* Connector arrow between stages */
.ob-arrow{
  position:absolute;right:-16px;top:50%;transform:translateY(-50%);
  width:32px;height:32px;background:#fff;border:1.5px solid #E8E7E4;
  border-radius:50%;display:flex;align-items:center;justify-content:center;
  z-index:5;box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.ob-arrow svg{width:13px;height:13px;stroke:#9CA3AF;stroke-width:2}

/* Draft email preview */
.ob-draft-preview{
  flex:1;overflow-y:auto;padding:12px 16px;
  background:#FAFAFA;
}
.ob-draft-preview::-webkit-scrollbar{width:3px}
.ob-draft-preview::-webkit-scrollbar-thumb{background:rgba(0,0,0,.1);border-radius:2px}
.ob-draft-field{font-size:11px;color:#9CA3AF;margin-bottom:2px}
.ob-draft-field strong{color:#374151}
.ob-draft-divider{border:none;border-top:1px solid #F0F0F0;margin:8px 0}
.ob-draft-body{font-size:11.5px;color:#374151;line-height:1.7;white-space:pre-wrap}

/* Confidence score */
.ob-confidence{
  display:flex;align-items:center;gap:10px;
  padding:10px 16px;border-top:1px solid #F0F0F0;
  background:#fff;flex-shrink:0;
}
.ob-confidence-ring{width:32px;height:32px;flex-shrink:0}
.ob-confidence-ring svg{width:32px;height:32px;transform:rotate(-90deg)}
.ob-confidence-bg{fill:none;stroke:#F3F4F6;stroke-width:4}
.ob-confidence-fill{fill:none;stroke:#22c55e;stroke-width:4;stroke-linecap:round;stroke-dasharray:88;stroke-dashoffset:22}
.ob-confidence-label{font-size:11.5px;font-weight:700;color:#0D0D0D}
.ob-confidence-sub{font-size:10px;color:#9CA3AF}

/* ── RIGHT: AVA Says ── */
.ob-ava-says{
  background:#fff;border-left:1px solid #F0F0F0;
  padding:24px 20px;display:flex;flex-direction:column;
}
.ob-ava-eyebrow{font-size:9px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#9CA3AF;margin-bottom:12px}
.ob-ava-profile{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.ob-ava-avatar{width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #F0F0F0}
.ob-ava-quote{font-size:12.5px;font-weight:600;color:#0D0D0D;line-height:1.55;margin-bottom:6px}
.ob-ava-quote-sub{font-size:11.5px;color:#6B7280;line-height:1.5}
.ob-ava-actions{display:flex;flex-direction:column;gap:8px;margin-top:auto}

.btn-approve{
  display:flex;align-items:center;gap:8px;justify-content:center;
  padding:12px 16px;border-radius:12px;
  background:#0D0D0D;color:#fff;border:none;cursor:pointer;
  font-size:13px;font-weight:800;font-family:inherit;
  transition:opacity .15s;
}
.btn-approve:hover{opacity:.88}
.btn-approve svg{width:16px;height:16px;stroke:#fff;stroke-width:2;fill:none}

.btn-edit{
  display:flex;align-items:center;gap:8px;justify-content:center;
  padding:11px 16px;border-radius:12px;
  background:#fff;color:#374151;border:1.5px solid #E5E7EB;cursor:pointer;
  font-size:13px;font-weight:700;font-family:inherit;
  transition:border-color .15s;
}
.btn-edit:hover{border-color:#0D0D0D;color:#0D0D0D}
.btn-edit svg{width:15px;height:15px;stroke:currentColor;stroke-width:2;fill:none}

/* Waiting state */
.ob-waiting{
  flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
  text-align:center;padding:20px;opacity:.4;
}
.ob-waiting svg{width:32px;height:32px;stroke:#9CA3AF;stroke-width:1.5;fill:none;margin-bottom:10px}
.ob-waiting p{font-size:12px;color:#9CA3AF;line-height:1.5}

/* No deployment warning */
.ob-no-dep{
  background:rgba(245,197,24,.08);border:1px solid rgba(245,197,24,.3);
  border-radius:12px;padding:12px 14px;margin-bottom:14px;
  font-size:12px;color:#92400e;line-height:1.55;
}

/* ══ MOBILE ══ */
@media(max-width:1024px){
  html,body{overflow:auto;height:auto}
  .ob-page{grid-template-columns:1fr;height:auto}
  .ob-sidebar{flex-direction:row;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #E5E7EB;background:#fff;position:sticky;top:0;z-index:10}
  .ob-logo{margin-bottom:0;font-size:18px}
  .ob-steps{flex-direction:row;gap:8px;flex:0;align-items:center}
  .ob-step{flex-direction:column;align-items:center;gap:0}
  .ob-step-rail{padding-bottom:0}
  .ob-step:not(:last-child) .ob-step-rail::after{display:none}
  .ob-step-body{display:none}
  .ob-step-num{width:26px;height:26px;font-size:11px}
  .ob-security{display:none}
  .ob-main{padding:16px;overflow:visible;height:auto;flex-direction:column}
  .ob-pipeline-card{grid-template-columns:1fr;border-radius:16px}
  .ob-stage{min-height:280px}
  .ob-ava-says{padding:20px}
  .ob-arrow{display:none}

  /* Success card mobile */
  .ob-success-card{border-radius:16px;height:auto}
  .ob-sc-header{padding:20px;flex-direction:column;align-items:flex-start}
  .ob-sc-btns{width:100%}
  .btn-dash,.btn-watch{flex:1}
  .ob-sc-content{grid-template-columns:1fr}
  #scDraftCard{min-height:320px}
  .ob-9grid{grid-template-columns:1fr}
  .ob-sc-right{border-left:none;border-top:1px solid #F0F0F0}
  .ob-sc-hero-mini{min-height:220px}
}
</style>
</head>
<body>

@php
  $depId     = $deployment?->id;
  $hasGmail  = !is_null($credential);
  $txId      = $watchTxId ?? null;
@endphp

<div class="ob-page">

  {{-- ══ SIDEBAR ══ --}}
  <aside class="ob-sidebar">
    <div class="ob-logo">UNIT</div>
    <div class="ob-steps">

      <a href="{{ route('hire.ava.welcome') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail"><div class="ob-step-num"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
        <div class="ob-step-body"><div class="ob-step-label">Meet Ava</div><div class="ob-step-desc">Complete</div></div>
      </a>

      <a href="{{ route('hire.ava.workspace') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail"><div class="ob-step-num"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
        <div class="ob-step-body"><div class="ob-step-label">Workspace</div><div class="ob-step-desc">Complete</div></div>
      </a>

      <a href="{{ route('hire.ava.orientation') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail"><div class="ob-step-num"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
        <div class="ob-step-body"><div class="ob-step-label">Orientation</div><div class="ob-step-desc">Complete</div></div>
      </a>

      <a href="{{ route('hire.ava.assignment') }}" class="ob-step done" style="text-decoration:none">
        <div class="ob-step-rail"><div class="ob-step-num"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
        <div class="ob-step-body"><div class="ob-step-label">First Assignment</div><div class="ob-step-desc">Complete</div></div>
      </a>

      <div class="ob-step active">
        <div class="ob-step-rail"><div class="ob-step-num">5</div></div>
        <div class="ob-step-body">
          <div class="ob-step-label">On Shift</div>
          <div class="ob-step-desc">Ava starts working for you</div>
        </div>
      </div>

    </div>
    <div class="ob-security">
      <div class="ob-security-row">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        <span class="ob-security-title">Secure. Private. Yours.</span>
      </div>
      <p>You're in control of what<br>Ava can see and access.</p>
    </div>
  </aside>

  {{-- ══ PIPELINE CARD ══ --}}
  <div class="ob-main">
    <div class="ob-pipeline-card">

      {{-- LEFT: Intro + trigger --}}
      <div class="ob-left">
        <div class="ob-step-tag">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          Step 5 of 5
        </div>

        <h1 class="ob-h1">Let's give Ava her <span class="ob-gold">first assignment.</span></h1>
        <p class="ob-sub">Send a real renewal email and Ava will draft a reply for you. You're always in control.</p>

        @if(session('error'))
        <div class="ob-no-dep" style="background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.3);color:#991b1b">
          {{ session('error') }}
        </div>
        @endif

        @if(!$depId)
        <div class="ob-no-dep">No AVA deployment found. Complete the previous steps first.</div>
        @elseif(!$hasGmail)
        <div class="ob-no-dep">Connect your Gmail in Step 2 before running Ava's first assignment.</div>
        @endif

        @if($depId)

        {{-- Trial counter --}}
        @php
          $billing = \Illuminate\Support\Facades\DB::table('deployment_billing')->where('deployment_id', $depId)->first();
          $trialsUsed  = $billing?->trial_transactions_used  ?? 0;
          $trialsLimit = $billing?->trial_transactions_limit ?? \App\Platform\Services\PlatformDefaults::freeTransactionsFor('ava');
          $isSubscribed = $billing?->status === 'active';
        @endphp
        @if(!$isSubscribed)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-radius:10px;background:#F4F3F1;border:1px solid #E5E7EB;margin-bottom:14px">
          <span style="font-size:11px;font-weight:600;color:#6B7280">Trial transactions</span>
          <span style="font-size:12px;font-weight:800;color:{{ $trialsUsed >= $trialsLimit ? '#DC2626' : '#0D0D0D' }}">{{ $trialsUsed }} / {{ $trialsLimit }}</span>
        </div>
        @endif

        {{-- INPUT state: shown when no active TX --}}
        <div id="inputArea" style="{{ $watchTxId ? 'display:none' : '' }}">
          <div class="ob-dropzone" id="dropzone" onclick="togglePaste()">
            <div class="ob-dropzone-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div class="ob-dropzone-title">Drag &amp; drop an email here</div>
            <div class="ob-dropzone-hint">or paste the content</div>
            <textarea id="emailPaste" name="email_content" placeholder="Paste email content here..." onclick="event.stopPropagation()"></textarea>
          </div>
          <form method="POST" action="{{ route('hire.ava.onshift.run') }}" id="fastTrackForm">
            @csrf
            <button type="button" class="ob-inbox-btn" onclick="submitRun()">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              Use a sample renewal email
            </button>
            <button type="button" class="btn-run" id="runBtn" onclick="submitRun()">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              Give Ava this assignment
            </button>
          </form>
        </div>

        {{-- RUNNING state: shown when TX is active --}}
        <div id="runningArea" style="{{ $watchTxId ? '' : 'display:none' }}">
          <div style="padding:14px 16px;border-radius:14px;background:#fff;border:1.5px solid #E5E7EB;margin-bottom:12px;display:flex;align-items:center;gap:10px">
            <div style="width:8px;height:8px;border-radius:50%;background:#F5C518;animation:pdot 1s ease infinite;flex-shrink:0"></div>
            <span style="font-size:12px;font-weight:700;color:#0D0D0D">Ava is on it — watch the pipeline</span>
          </div>
          <div style="font-size:10.5px;color:#9CA3AF;text-align:center;font-weight:500" id="runningTxLabel">TX: {{ $watchTxId }}</div>
        </div>

        @endif
      </div>

      {{-- STAGES 1 & 2 — derived from the contract's pipeline groups, see
           OnboardingController::showAvaOnShift() / PipelineStageService.
           Column 3 (Reply is ready) keeps its own richer markup below. --}}
      @foreach($onshiftColumns as $col)
        @if($col['num'] === 3) @continue @endif
      <div class="ob-stage" id="stage{{ $col['num'] }}">
        <div class="ob-stage-header">
          <div class="ob-stage-num">{{ $col['num'] }}. {{ $col['label'] }}</div>
        </div>
        <div class="ob-stage-img">
          <img src="{{ $col['image'] ?? '/images/ava-stand.png' }}" alt="{{ $col['label'] }}">
        </div>
        <div class="ob-stage-footer">
          <div class="ob-progress-bar"><div class="ob-progress-fill" id="prog{{ $col['num'] }}"></div></div>
          <div class="ob-stage-status">
            <span class="ob-stage-status-dot"></span>
            <span id="status{{ $col['num'] }}">Waiting...</span>
          </div>
          <div class="ob-substeps">
            @foreach($col['stages'] as $i => $stage)
            <div class="ob-substep" id="substep-{{ $stage['key'] }}">
              <span class="ob-substep-num">{{ $i + 1 }}</span>
              <span>{{ $stage['label'] }}</span>
            </div>
            @endforeach
          </div>
        </div>
        <div class="ob-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>
      </div>
      @endforeach

      {{-- STAGE 3: Reply ready --}}
      <div class="ob-stage" id="stage3" style="grid-column:auto">
        <div class="ob-stage-header" style="padding-bottom:8px">
          <div class="ob-stage-num">{{ $onshiftColumns[2]['num'] }}. {{ $onshiftColumns[2]['label'] }}</div>
        </div>
        <div class="ob-draft-preview" id="draftPreview">
          <div class="ob-waiting" id="draftWaiting">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p>Ava's draft will<br>appear here</p>
          </div>
          <div id="draftContent" style="display:none">
            <p class="ob-draft-field">To: <strong id="draftTo">—</strong></p>
            <p class="ob-draft-field">Subject: <strong id="draftSubject">—</strong></p>
            <hr class="ob-draft-divider">
            <p class="ob-draft-body" id="draftBody"></p>
          </div>
        </div>
        <div class="ob-confidence" id="confidenceRow" style="display:none">
          <div class="ob-confidence-ring">
            <svg viewBox="0 0 32 32">
              <circle class="ob-confidence-bg" cx="16" cy="16" r="14"/>
              <circle class="ob-confidence-fill" cx="16" cy="16" r="14" id="confFill"/>
            </svg>
          </div>
          <div>
            <div class="ob-confidence-label">Confidence Score</div>
            <div class="ob-confidence-sub" id="confLabel">Calculating...</div>
          </div>
        </div>
      </div>

      {{-- RIGHT: AVA Says --}}
      <div class="ob-ava-says">
        <div class="ob-ava-eyebrow">AVA SAYS</div>
        <div class="ob-ava-profile">
          <img src="/images/ava.png" alt="AVA" class="ob-ava-avatar">
        </div>
        <p class="ob-ava-quote" id="avaQuote">{{ $watchTxId ? 'Working on it...' : 'Ready when you are.' }}</p>
        <p class="ob-ava-quote-sub" id="avaSub">{{ $watchTxId ? 'I\'ll update you as I go.' : 'Give me an assignment and I\'ll get started.' }}</p>

        <div class="ob-ava-actions" id="avaActions" style="opacity:.3;pointer-events:none">
          <button class="btn-approve" id="approveBtn" onclick="approveDraft()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3H14z"/></svg>
            Looks good, approve it
          </button>
          <button class="btn-edit" id="editBtn" onclick="editDraft()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            I'll make some edits
          </button>
          <button class="btn-edit" id="retryBtn" onclick="location.href='{{ route('hire.ava.onshift') }}'" style="display:none;border-color:#FCA5A5;color:#DC2626">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Try again
          </button>
          {{-- Next step button — shown on success --}}
          <a href="{{ route('app.dashboard') }}" class="btn-approve" id="nextBtn" style="display:none;text-decoration:none;background:#22c55e">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            Go to your dashboard
          </a>
          <a href="{{ route('hire.ava.onshift') }}" class="btn-edit" style="text-decoration:none;margin-top:2px;border-color:#E5E7EB;font-size:11.5px;color:#9CA3AF">
            Run another assignment
          </a>
        </div>
      </div>

    </div>

  {{-- ══ SUCCESS CARD (swaps in after pipeline completes) ══ --}}
  <div class="ob-success-card" id="successCard">

    {{-- Header: badge + message + CTAs, full width — the live run-through
         below gets the space this used to take up as a side column. --}}
    <div class="ob-sc-header">
      <div class="ob-sc-header-text">
        <div class="ob-sc-badge">
          <span class="ob-sc-badge-dot"></span>
          On Shift
        </div>
        <div>
          <h1 class="ob-sc-h1">Ava is officially on shift. 🎉</h1>
          <p class="ob-sc-sub">She's monitoring your inbox and will alert you when action is needed.</p>
        </div>
      </div>

      <div class="ob-sc-btns">
        <a href="{{ route('app.desk.ava') }}" class="btn-dash">
          Go to AVA's Desk
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ route('app.desk.ava') }}" class="btn-watch">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8" fill="currentColor" stroke="none"/></svg>
          Watch Ava in action
        </a>
      </div>
    </div>

    <div class="ob-sc-content">

    {{-- Center: the live run-through canvas — the main event on this page --}}
    <div class="ob-sc-canvas">
      <div class="ob-sc-activity-header">
        <span class="ob-sc-activity-title">Live Run-Through</span>
        <span class="ob-sc-onshift"><span class="ob-sc-onshift-dot"></span> On Shift</span>
      </div>
      <div class="ob-9grid" id="nineGrid">
        <div class="ob-9cell" id="nine-detect"><div class="ob-9cell-head"><span class="ob-9dot">1</span><div><div class="ob-9label">Detect</div><div class="ob-9sub" id="nine-detect-sub">—</div></div></div></div>
        <div class="ob-9cell" id="nine-understand"><div class="ob-9cell-head"><span class="ob-9dot">2</span><div><div class="ob-9label">Understand</div><div class="ob-9sub" id="nine-understand-sub">—</div></div></div></div>
        <div class="ob-9cell" id="nine-verify"><div class="ob-9cell-head"><span class="ob-9dot">3</span><div><div class="ob-9label">Verify</div><div class="ob-9sub" id="nine-verify-sub">—</div></div></div></div>
        <div class="ob-9cell" id="nine-prepare"><div class="ob-9cell-head"><span class="ob-9dot">4</span><div><div class="ob-9label">Prepare</div><div class="ob-9sub" id="nine-prepare-sub">—</div></div></div></div>
        <div class="ob-9cell" id="nine-draft"><div class="ob-9cell-head"><span class="ob-9dot">5</span><div><div class="ob-9label">Draft</div><div class="ob-9sub" id="nine-draft-sub">—</div></div></div></div>
        <div class="ob-9cell" id="nine-deliver"><div class="ob-9cell-head"><span class="ob-9dot">6</span><div><div class="ob-9label">Deliver</div><div class="ob-9sub" id="nine-deliver-sub">—</div></div></div></div>
        <div class="ob-9cell" id="nine-approve"><div class="ob-9cell-head"><span class="ob-9dot">7</span><div><div class="ob-9label">You approve</div><div class="ob-9sub" id="nine-approve-sub">Waiting</div></div></div></div>
        <div class="ob-9cell" id="nine-fulfill">
          <div class="ob-9cell-head"><span class="ob-9dot">8</span><div><div class="ob-9label">Fulfill</div><div class="ob-9sub" id="nine-fulfill-sub">—</div></div></div>
          <div class="ob-9subitems">
            <div class="ob-9subitem" id="nine-fulfill-sub-invoice"><span class="ob-9subitem-dot"></span>Request invoice</div>
            <div class="ob-9subitem" id="nine-fulfill-sub-documents"><span class="ob-9subitem-dot"></span>Request documents</div>
            <div class="ob-9subitem" id="nine-fulfill-sub-payment"><span class="ob-9subitem-dot"></span>Confirm payment</div>
          </div>
        </div>
        <div class="ob-9cell" id="nine-renew">
          <div class="ob-9cell-head"><span class="ob-9dot">9</span><div><div class="ob-9label">Confirm &amp; renew</div><div class="ob-9sub" id="nine-renew-sub">—</div></div></div>
          <div class="ob-9subitems">
            <div class="ob-9subitem" id="nine-renew-sub-date"><span class="ob-9subitem-dot"></span>Update renewal date</div>
            <div class="ob-9subitem" id="nine-renew-sub-archive"><span class="ob-9subitem-dot"></span>Archive evidence</div>
            <div class="ob-9subitem" id="nine-renew-sub-notify"><span class="ob-9subitem-dot"></span>Notify stakeholders</div>
            <div class="ob-9subitem" id="nine-renew-sub-watch"><span class="ob-9subitem-dot"></span>Schedule next watch</div>
          </div>
        </div>
      </div>
      <a href="/app/transactions" class="ob-sc-view-link">View Live Feed →</a>
    </div>

    {{-- Right: Ava mini-hero + draft --}}
    <div class="ob-sc-right">
      <div class="ob-sc-hero-mini">
        <img src="/images/ava-desk.png" alt="Ava on shift">
        <div class="ob-sc-nameplate">
          <div class="ob-sc-nameplate-name">AVA</div>
          <div class="ob-sc-nameplate-title">Renewal Specialist</div>
        </div>
      </div>

      {{-- Draft card: fills remaining height, hidden until draft is ready --}}
      <div id="scDraftCard" style="display:none">
        <div class="sc-draft-chrome">
          <span style="width:10px;height:10px;border-radius:50%;background:#FF5F57;display:inline-block;flex-shrink:0"></span>
          <span style="width:10px;height:10px;border-radius:50%;background:#FEBC2E;display:inline-block;flex-shrink:0"></span>
          <span style="width:10px;height:10px;border-radius:50%;background:#28C840;display:inline-block;flex-shrink:0"></span>
          <div style="flex:1;background:#fff;border:1px solid #DADCE0;border-radius:99px;padding:3px 10px;display:flex;align-items:center;gap:6px;margin:0 6px;min-width:0">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#5F6368" stroke-width="2" style="flex-shrink:0"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
            <span style="font-size:9.5px;color:#5F6368;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">mail.google.com/mail/u/0/#drafts</span>
          </div>
          <svg width="40" height="14" viewBox="0 0 55 18" fill="none" style="flex-shrink:0">
            <path d="M3.9 14.4V8.1L0 5.1V13.2C0 13.85 0.54 14.4 1.2 14.4H3.9Z" fill="#4285F4"/>
            <path d="M19.5 14.4H22.2C22.86 14.4 23.4 13.86 23.4 13.2V5.1L19.5 8.1V14.4Z" fill="#34A853"/>
            <path d="M19.5 2.4L11.7 8.25L3.9 2.4V8.1L11.7 13.95L19.5 8.1V2.4Z" fill="#EA4335"/>
            <path d="M0 5.1L3.9 8.1V2.4L2.1 1.08C1.29 0.48 0 1.05 0 2.07V5.1Z" fill="#C5221F"/>
            <path d="M23.4 5.1V2.07C23.4 1.05 22.11 0.48 21.3 1.08L19.5 2.4V8.1L23.4 5.1Z" fill="#FBBC04"/>
            <text x="27" y="13" font-family="Arial,sans-serif" font-size="11" fill="#5F6368">Gmail</text>
          </svg>
        </div>
        <div class="sc-draft-body">
          <div class="sc-draft-header-row">
            <span class="sc-draft-header-label">To</span>
            <span class="sc-draft-header-value" id="scDraftTo">—</span>
          </div>
          <div class="sc-draft-header-row">
            <span class="sc-draft-header-label">From</span>
            <span class="sc-draft-header-value" id="scDraftFrom">{{ auth()->user()->email }}</span>
          </div>
          <div class="sc-draft-subject-row">
            <div class="sc-draft-subject-text" id="scDraftSubject">—</div>
          </div>
          <div class="sc-draft-preview" id="scDraftBody"></div>
        </div>
        <div class="sc-draft-actions">
          <button onclick="scApproveDraft()" id="scApproveBtn" class="sc-btn-approve">Approve &amp; send</button>
          <a href="/app/transactions" class="sc-btn-review">Review in full</a>
        </div>
      </div>

    </div>

    </div>{{-- /ob-sc-content --}}

  </div>

  </div>{{-- /ob-main --}}

</div>{{-- /ob-page --}}

<script>
const DEP_ID  = {{ $depId ?? 'null' }};
const STATUS_URL = '/workers/ava/status/';
const CSRF    = document.querySelector('meta[name=csrf-token]').content;

let txId      = @json($watchTxId);
let pollTimer = null;
let stage     = 0; // 0=idle, 1=analyzing, 2=drafting, 3=done

// Contract stage key -> onboarding column number, built from the same
// grouped data the columns above render from (PipelineStageService) — not a
// second hardcoded mapping. Mirrors Fast Track's STATUS_TO_STAGE approach.
const STAGE_KEY_TO_COLUMN = @json(
    collect($onshiftColumns)->flatMap(fn($col) => collect($col['stage_keys'])->mapWithKeys(fn($key) => [$key => $col['num']]))
);
const STATUS_TO_STAGE_KEY = {
  received: 'webhook', ingesting: 'webhook', reading: 'read_email', classifying: 'classify',
  memory_lookup: 'memory', logging: 'log_entry', template_select: 'select_template',
  drafting: 'draft_email', pushing: 'push_draft', draft_ready: 'push_draft',
  approved: 'push_draft', sent: 'push_draft', blocked: 'read_email',
};

// Every raw stage across all columns, in order, with its output column —
// drives the sub-step dots. Same source as the labels rendered above.
const ALL_STAGES = @json(collect($onshiftColumns)->pluck('stages')->flatten(1)->values());

function updateSubsteps(data, activeStageKey){
  const activeIdx = ALL_STAGES.findIndex(function(st){ return st.key === activeStageKey; });
  ALL_STAGES.forEach(function(st, i){
    const el = document.getElementById('substep-' + st.key);
    if(!el) return;
    const hasOutput = st.output_column && !!data[st.output_column];
    const passedByIndex = activeIdx > -1 && i < activeIdx;
    const done = hasOutput || passedByIndex;
    el.classList.toggle('is-done', done);
    el.classList.toggle('is-active', !done && i === activeIdx);
  });
}

function togglePaste(){
  const ta = document.getElementById('emailPaste');
  ta.style.display = ta.style.display === 'none' ? 'block' : 'none';
  if(ta.style.display === 'block') ta.focus();
}

function setStage(n, statusText1, statusText2){
  // Stage 1
  const s1 = document.getElementById('stage1');
  s1.classList.toggle('is-active', n === 1);
  s1.classList.toggle('is-done', n > 1);
  document.getElementById('prog1').style.width = n > 1 ? '100%' : (n === 1 ? '60%' : '0');
  document.getElementById('status1').textContent = n === 0 ? 'Waiting...' : (n === 1 ? (statusText1 || 'Reading email...') : 'Done');

  // Stage 2
  const s2 = document.getElementById('stage2');
  s2.classList.toggle('is-active', n === 2);
  s2.classList.toggle('is-done', n > 2);
  document.getElementById('prog2').style.width = n > 2 ? '100%' : (n === 2 ? '55%' : '0');
  document.getElementById('status2').textContent = n < 2 ? 'Waiting...' : (n === 2 ? (statusText2 || 'Writing reply...') : 'Done');

  // Stage 3
  document.getElementById('stage3').classList.toggle('is-done', n === 3);
}

// ── 9-section live run-through — driven entirely by real transaction data,
// not a scripted animation. Sections 1-6 mirror what the pipeline card
// already shows; 7-9 are new: the human decision gate and the fulfillment
// stages (10-16) that used to not exist at all. ──────────────────────────
const NINE_SECTIONS = ['detect','understand','verify','prepare','draft','deliver','approve','fulfill','renew'];
function nineState(data){
  const s = data.status;
  const st = {};
  st.detect     = data.read_output ? 'done' : (['reading','received','ingesting'].includes(s) ? 'active' : 'pending');
  st.understand = data.classify_output ? 'done' : (st.detect === 'done' && s === 'classifying' ? 'active' : (st.detect === 'done' ? 'pending' : 'pending'));
  st.verify     = data.memory_output ? 'done' : (st.understand === 'done' && s === 'memory_lookup' ? 'active' : 'pending');
  st.prepare    = data.draft_output ? 'done' : (st.verify === 'done' && ['logging','template_select'].includes(s) ? 'active' : 'pending');
  st.draft      = data.draft_output ? 'done' : (st.prepare === 'done' && s === 'drafting' ? 'active' : (st.prepare === 'done' ? 'active' : 'pending'));
  st.deliver    = ['draft_ready','approved','sent'].includes(s) ? 'done' : (st.draft === 'done' && s === 'pushing' ? 'active' : 'pending');
  st.approve    = ['approved','sent'].includes(s) ? 'done' : (st.deliver === 'done' ? 'active' : 'pending');
  const fs = data.fulfillment_stage;
  const fulfillDone = fs && ['update_renewal_date','archive_evidence','notify_stakeholders','schedule_next_watch'].includes(fs);
  st.fulfill    = fulfillDone ? 'done' : (st.approve === 'done' && fs ? 'active' : 'pending');
  st.renew      = fs === 'schedule_next_watch' ? 'done' : (st.fulfill === 'done' && fs ? 'active' : 'pending');
  return st;
}
const NINE_SUBS = {
  detect:     d => d.read_output ? 'Email read' : 'Watching inbox',
  understand: d => d.classify_output ? (d.classify_output.category || 'Classified') : 'Analyzing',
  verify:     d => d.memory_output ? (d.memory_output.matched_client || 'Matched') : 'Checking memory',
  prepare:    d => d.draft_output ? 'Template selected' : 'Preparing',
  draft:      d => d.draft_output ? 'Draft written' : 'Writing',
  deliver:    d => ['draft_ready','approved','sent'].includes(d.status) ? 'In Gmail' : 'Sending',
  approve:    d => ['approved','sent'].includes(d.status) ? 'You approved' : 'Waiting on you',
  fulfill:    d => {
    if(!d.fulfillment_stage) return '—';
    if(d.fulfillment_stage === 'confirm_payment') return 'Waiting on payment';
    if(d.invoice_output || d.documents_output) return 'Requesting docs';
    return 'In progress';
  },
  renew:      d => d.fulfillment_stage === 'schedule_next_watch' ? 'Cycle complete' : (d.payment_output ? 'Renewing' : '—'),
};
// Stages 8 and 9 each bundle several real pipeline stages — these sub-item
// states make the bundling visible instead of collapsing it into one label.
function subState(el, isDone, isActive){
  if(!el) return;
  el.classList.toggle('is-done', isDone);
  el.classList.toggle('is-active', !isDone && isActive);
}
function updateSubItems(data){
  const fs = data.fulfillment_stage;
  subState(document.getElementById('nine-fulfill-sub-invoice'),   !!data.invoice_output,   fs === 'request_invoice');
  subState(document.getElementById('nine-fulfill-sub-documents'), !!data.documents_output, fs === 'request_documents');
  subState(document.getElementById('nine-fulfill-sub-payment'),   !!(data.payment_output && data.payment_output.confirmed), fs === 'confirm_payment');
  subState(document.getElementById('nine-renew-sub-date'),        !!data.renewal_output,   fs === 'update_renewal_date');
  subState(document.getElementById('nine-renew-sub-archive'),     !!data.archive_output,   fs === 'archive_evidence');
  subState(document.getElementById('nine-renew-sub-notify'),      !!data.notify_output,    fs === 'notify_stakeholders');
  subState(document.getElementById('nine-renew-sub-watch'),       fs === 'schedule_next_watch', false);
}
function updateNineGrid(data){
  const states = nineState(data);
  NINE_SECTIONS.forEach(function(key){
    const cell = document.getElementById('nine-' + key);
    if(!cell) return;
    const s = states[key];
    cell.classList.toggle('is-active', s === 'active');
    cell.classList.toggle('is-done', s === 'done');
    const sub = document.getElementById('nine-' + key + '-sub');
    if(sub && (s === 'active' || s === 'done')) sub.textContent = NINE_SUBS[key](data);
  });
  updateSubItems(data);
}

let fulfillmentPollTimer = null;
function startFulfillmentPoll(){
  clearInterval(fulfillmentPollTimer);
  fulfillmentPollTimer = setInterval(function(){
    fetch(STATUS_URL + txId, { credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
      .then(r => r.ok ? r.json() : null)
      .then(data => {
        if(!data) return;
        updateNineGrid(data);
        if(data.fulfillment_stage === 'schedule_next_watch' || data.status === 'rejected'){
          clearInterval(fulfillmentPollTimer);
        }
      })
      .catch(() => {});
  }, 4000);
  // Stop polling after 10 minutes regardless — a human gate may just sit
  // unconfirmed for a while, no need to keep hammering the endpoint forever.
  setTimeout(function(){ clearInterval(fulfillmentPollTimer); }, 600000);
}

function showDraft(data){
  clearInterval(pollTimer);
  clearTimeout(_pollTimeout);
  stage = 3;
  updateNineGrid(data);
  startFulfillmentPoll();

  // Swap pipeline card → success card
  document.querySelector('.ob-pipeline-card').style.display = 'none';
  const sc = document.getElementById('successCard');
  sc.classList.add('is-visible');

  window._txId = data.tx_id;

  // Populate draft card
  const draft = data.draft_output;
  if(draft){
    const subject   = draft.subject || data.classify_output?.subject || 'Renewal Response';
    const body      = draft.body || draft.draft || '';
    const toAddr    = draft.to || data.classify_output?.from || data.email_metadata?.from || '';
    document.getElementById('scDraftSubject').textContent = subject;
    document.getElementById('scDraftBody').textContent    = body;
    if(toAddr) document.getElementById('scDraftTo').textContent = toAddr;
    document.getElementById('scDraftCard').style.display  = '';
    window._scTxId = data.tx_id;
    window._scGmailDraftId = data.gmail_draft_id;
  } else {
    // No draft output yet — show a prompt to check Gmail
    document.getElementById('scDraftCard').innerHTML = '<div style="padding:4px 0;font-size:12px;color:#374151;font-weight:600">📬 Your draft is in Gmail.</div><div style="font-size:11px;color:#9CA3AF;margin-top:4px">Check your Drafts folder to review and send.</div>';
    document.getElementById('scDraftCard').style.display = '';
  }
}

function scApproveDraft(){
  if(!window._scTxId) return;
  const btn = document.getElementById('scApproveBtn');
  btn.textContent = 'Approving...';
  btn.disabled = true;
  fetch('/app/transactions/' + window._scTxId + '/decide', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ decision: 'approve' })
  }).then(() => {
    btn.textContent = '✓ Approved — check Gmail Drafts';
    btn.style.background = '#22c55e';
  }).catch(() => {
    btn.textContent = 'Approve & send';
    btn.disabled = false;
  });
}

const ERROR_MESSAGES = {
  blocked: {
    quote: "I hit a billing limit and couldn't finish.",
    sub:   "Your trial quota is used up. Subscribe or contact support to continue — your setup is saved.",
    stage: "Blocked by billing policy"
  },
  failed: {
    quote: "Something went wrong on my end.",
    sub:   "I ran into a technical error processing this email. Try running again, or check the dashboard.",
    stage: "Processing failed"
  },
  rejected: {
    quote: "This email didn't match my rules.",
    sub:   "It didn't meet the criteria in your capture rules, so I skipped it. You can adjust your rules in the dashboard.",
    stage: "Skipped by rules"
  },
  dismissed: {
    quote: "This one was dismissed.",
    sub:   "The email was manually dismissed before I could finish. Run another assignment when you're ready.",
    stage: "Dismissed"
  },
  timeout: {
    quote: "I'm taking longer than expected.",
    sub:   "The pipeline might be busy or the queue worker is restarting. Try again in a moment — your setup is intact.",
    stage: "Timed out"
  },
};

function resetToInput(){
  document.getElementById('inputArea').style.display  = '';
  document.getElementById('runningArea').style.display = 'none';
}

function showError(s, stageLabel){
  clearInterval(pollTimer);
  clearTimeout(_pollTimeout);
  resetToInput();
  const msg = ERROR_MESSAGES[s] || { quote: 'Ava ran into an issue.', sub: 'Check the dashboard for details.', stage: 'Error' };

  // Surface in AVA Says
  document.getElementById('avaQuote').textContent = msg.quote;
  document.getElementById('avaSub').textContent   = msg.sub;

  // Show retry + dashboard in actions area, hide approve/edit
  document.getElementById('avaActions').style.opacity      = '1';
  document.getElementById('avaActions').style.pointerEvents = 'auto';
  document.getElementById('approveBtn').style.display      = 'none';
  document.getElementById('editBtn').style.display         = 'none';
  document.getElementById('retryBtn').style.display        = 'flex';

  // Mark whichever stage was active as errored
  const activeStage = document.querySelector('.ob-stage.is-active');
  if(activeStage){
    activeStage.classList.remove('is-active');
    activeStage.style.background = 'rgba(239,68,68,.04)';
    activeStage.querySelector('.ob-stage-status-dot').style.background = '#ef4444';
    activeStage.querySelector('[id^=status]').textContent = msg.stage;
  }
}

let _pollCount = 0;
function poll(){
  if(!txId) return;
  _pollCount++;
  fetch(STATUS_URL + txId, { credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
    .then(r => {
      if(r.status === 401){ showError('failed'); clearInterval(pollTimer); return null; }
      if(r.status === 404){ return null; }
      return r.json();
    })
    .then(data => {
      if(!data) return;
      const s = data.status;

      // ── Terminal success ──
      if(['draft_ready','approved','sent'].includes(s) || data.draft_output){
        clearInterval(pollTimer);
        clearTimeout(_pollTimeout);
        showDraft(data);
        return;
      }

      // ── Terminal failures — surface immediately ──
      if(['blocked','failed','rejected','dismissed'].includes(s)){
        showError(s);
        return;
      }

      // ── In-progress — advance stages, driven by STAGE_KEY_TO_COLUMN
      // (built from the same contract groups the columns render from) ──
      const STATUS_LABELS = {
        reading: 'Reading email...', classifying: 'Classifying email...',
        memory_lookup: 'Looking up memory...', logging: 'Logging transaction...',
        template_select: 'Selecting template...', drafting: 'Writing reply...',
      };
      const stageKey = STATUS_TO_STAGE_KEY[s];
      let col = stageKey ? STAGE_KEY_TO_COLUMN[stageKey] : null;
      // Fall back to output-field presence if the status string itself isn't
      // in the map (e.g. a future stage added to the contract before its
      // status string is added here — degrade gracefully, don't get stuck).
      if(!col){
        if(data.classify_output || data.memory_output || s === 'generating') col = 2;
        else if(data.read_output) col = 1;
      }

      if(col === 2){
        setStage(2, null, STATUS_LABELS[s] || 'Writing reply...');
        stage = 2;
      } else if(col === 1){
        setStage(1, STATUS_LABELS[s] || 'Reading email...', null);
        stage = 1;
      } else if(stage < 1){
        setStage(1, 'Processing...', null); stage = 1;
      }

      updateSubsteps(data, stageKey);
    })
    .catch(() => {});
}

let _pollTimeout = null;
function startPollWithTimeout(){
  poll();
  pollTimer = setInterval(poll, 2000);
  // 60s safety net: if nothing resolved, show a timeout error
  _pollTimeout = setTimeout(() => {
    if(stage < 3){
      clearInterval(pollTimer);
      showError('timeout');
    }
  }, 60000);
}

function submitRun(){
  if(!DEP_ID){ return; }
  document.getElementById('inputArea').style.display   = 'none';
  document.getElementById('runningArea').style.display = '';
  document.getElementById('runningTxLabel').textContent = 'Submitting...';
  setStage(1, 'Reading email...', null);
  stage = 1;
  document.getElementById('fastTrackForm').submit();
}

function approveDraft(){
  if(!window._txId) return;
  fetch('/app/transactions/' + window._txId + '/decide', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ decision: 'approve' })
  }).then(() => {
    document.getElementById('avaQuote').textContent = 'Sent to Gmail Drafts!';
    document.getElementById('avaSub').textContent   = "Check your Drafts folder — it's ready to review and send.";
    document.getElementById('approveBtn').textContent = '✓ Approved';
    document.getElementById('approveBtn').style.background = '#22c55e';
  });
}

function editDraft(){
  if(window._scGmailDraftId){
    window.open('https://mail.google.com/mail/u/0/#drafts/' + window._scGmailDraftId, '_blank');
  } else {
    window.open('https://mail.google.com/mail/u/0/#drafts', '_blank');
  }
}

// On page load with ?watch=txId — job was just dispatched, start polling
if(txId){
  setStage(1, 'Reading email...', null);
  stage = 1;
  startPollWithTimeout();
}
</script>

<x-self-learn pageKey="hire.ava.onshift" />

@include('partials.tracking')
</body>
</html>

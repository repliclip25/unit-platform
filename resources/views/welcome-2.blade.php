<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>UNIT — AI Workers That Never Stop Showing Up</title>
<meta name="description" content="AVA, DOX, MOX, and NUX are your AI workforce. Each one built for a specific job. All of them running 24/7 while you focus on growth.">
<link rel="icon" type="image/png" href="/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<style>
/* ── Reset ── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
img{display:block;max-width:100%}
a{text-decoration:none}
button{cursor:pointer;font-family:inherit}

/* ── Tokens ── */
:root{
  --brand:       #7C3AED;
  --brand-dark:  #5B21B6;
  --brand-soft:  rgba(124,58,237,0.09);
  --brand-ring:  rgba(124,58,237,0.22);

  --ava:  #7C3AED; --ava-soft: rgba(124,58,237,0.10);
  --dox:  #059669; --dox-soft: rgba(5,150,105,0.10);
  --mox:  #D97706; --mox-soft: rgba(217,119,6,0.10);
  --nux:  #2563EB; --nux-soft: rgba(37,99,235,0.10);

  --dark:  #09090B;
  --dark2: #18181B;
  --dark3: #27272A;

  --text:    #0F0F0F;
  --t2:      #374151;
  --t3:      #6B7280;
  --t4:      #9CA3AF;
  --border:  #E5E7EB;
  --soft-bg: #F9FAFB;
  --white:   #FFFFFF;

  --r-sm: 10px;
  --r-md: 16px;
  --r-lg: 24px;
  --r-xl: 32px;

  --font-head: 'Syne', sans-serif;
  --font-body: 'Inter', sans-serif;

  --max-w: 1180px;
  --pad:   clamp(20px, 5vw, 48px);
}

body{
  font-family: var(--font-body);
  color: var(--text);
  background: var(--white);
  -webkit-font-smoothing: antialiased;
  overflow-x: hidden;
}

/* ── Layout helpers ── */
.wrap{ max-width: var(--max-w); margin: 0 auto; padding: 0 var(--pad); }
.section{ padding: clamp(64px, 8vw, 112px) 0; }

/* ═══════════════════════════════════════════
   NAV
═══════════════════════════════════════════ */
.nav{
  position: fixed; top: 0; left: 0; right: 0; z-index: 100;
  background: rgba(9,9,11,0.82);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255,255,255,0.07);
}
.nav-inner{
  display: flex; align-items: center; justify-content: space-between;
  height: 64px;
}
.nav-logo{
  display: flex; align-items: center; gap: 10px;
}
.nav-logo-mark{
  width: 34px; height: 34px; border-radius: 9px;
  background: var(--brand);
  display: flex; align-items: center; justify-content: center;
}
.nav-logo-mark svg{ width: 18px; height: 18px; }
.nav-logo-name{
  font-family: var(--font-head);
  font-size: 1.15rem; font-weight: 800;
  color: #fff; letter-spacing: -.5px;
}
.nav-links{
  display: flex; align-items: center; gap: 32px;
  list-style: none;
}
.nav-links a{
  font-size: 14px; font-weight: 500;
  color: rgba(255,255,255,0.65);
  transition: color .15s;
}
.nav-links a:hover{ color: #fff; }
.nav-actions{ display: flex; align-items: center; gap: 10px; }
.btn-ghost-nav{
  padding: 8px 18px; border-radius: 8px; font-size: 14px; font-weight: 600;
  color: rgba(255,255,255,0.75); background: transparent;
  border: 1px solid rgba(255,255,255,0.14);
  transition: all .15s;
}
.btn-ghost-nav:hover{ background: rgba(255,255,255,0.07); color: #fff; }
.btn-primary{
  padding: 9px 20px; border-radius: 9px; font-size: 14px; font-weight: 700;
  background: var(--brand); color: #fff; border: none;
  display: inline-flex; align-items: center; gap: 6px;
  transition: opacity .15s, transform .15s, box-shadow .15s;
  box-shadow: 0 0 0 0 rgba(124,58,237,0);
}
.btn-primary:hover{
  opacity: .9; transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(124,58,237,0.35);
}
.nav-mobile-toggle{
  display: none; flex-direction: column; gap: 5px;
  background: none; border: none; padding: 4px;
}
.nav-mobile-toggle span{
  display: block; width: 22px; height: 2px;
  background: rgba(255,255,255,0.8); border-radius: 2px;
  transition: all .2s;
}

/* ═══════════════════════════════════════════
   HERO
═══════════════════════════════════════════ */
.hero{
  background: var(--dark);
  min-height: 100vh;
  display: flex; align-items: center;
  padding-top: 64px;
  position: relative;
  overflow: hidden;
}
.hero::before{
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(ellipse 70% 60% at 65% 50%, rgba(124,58,237,0.18) 0%, transparent 70%);
  pointer-events: none;
}
.hero-inner{
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  align-items: center;
  padding: clamp(48px, 8vw, 96px) var(--pad);
  max-width: var(--max-w);
  margin: 0 auto;
  width: 100%;
}
.hero-eyebrow{
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
  color: var(--brand); margin-bottom: 24px;
}
.hero-eyebrow-dot{
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--brand);
  animation: pulse-brand 2s infinite;
}
@keyframes pulse-brand{
  0%,100%{ opacity:1; transform:scale(1); }
  50%{ opacity:.5; transform:scale(1.4); }
}
.hero-headline{
  font-family: var(--font-head);
  font-size: clamp(2.4rem, 5vw, 3.8rem);
  font-weight: 800;
  color: #fff;
  line-height: 1.08;
  letter-spacing: -.03em;
  margin-bottom: 24px;
}
.hero-headline em{
  font-style: normal;
  background: linear-gradient(135deg, #a78bfa, var(--brand));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero-sub{
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  color: rgba(255,255,255,0.55);
  line-height: 1.7;
  max-width: 440px;
  margin-bottom: 36px;
}
.hero-ctas{ display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 44px; }
.btn-primary-lg{
  padding: 14px 28px; border-radius: 12px; font-size: 15px; font-weight: 700;
  background: var(--brand); color: #fff; border: none;
  display: inline-flex; align-items: center; gap: 8px;
  transition: opacity .15s, transform .15s, box-shadow .15s;
  box-shadow: 0 4px 24px rgba(124,58,237,0.4);
}
.btn-primary-lg:hover{ opacity:.9; transform:translateY(-2px); box-shadow: 0 12px 32px rgba(124,58,237,0.45); }
.btn-ghost-lg{
  padding: 13px 24px; border-radius: 12px; font-size: 15px; font-weight: 600;
  background: transparent; color: rgba(255,255,255,0.7);
  border: 1px solid rgba(255,255,255,0.16);
  display: inline-flex; align-items: center; gap: 8px;
  transition: all .15s;
}
.btn-ghost-lg:hover{ background: rgba(255,255,255,0.06); color: #fff; }
.hero-proof{
  display: flex; align-items: center; gap: 14px;
}
.hero-proof-avatars{
  display: flex;
}
.hero-proof-avatars span{
  width: 32px; height: 32px; border-radius: 50%;
  border: 2px solid var(--dark);
  margin-left: -8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700; color: #fff;
  background: var(--dark3);
}
.hero-proof-avatars span:first-child{ margin-left: 0; }
.hero-proof-text{
  font-size: 13px; color: rgba(255,255,255,0.45);
}
.hero-proof-text strong{ color: rgba(255,255,255,0.75); }
.hero-image{
  position: relative;
}
.hero-image img{
  width: 100%;
  max-height: 600px;
  object-fit: cover;
  object-position: center top;
  border-radius: var(--r-xl);
  box-shadow: 0 32px 80px rgba(0,0,0,0.6);
}
.hero-image-badge{
  position: absolute; bottom: 24px; left: -20px;
  background: rgba(9,9,11,0.82);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 14px;
  padding: 12px 16px;
  display: flex; align-items: center; gap: 10px;
}
.hero-image-badge-dot{
  width: 8px; height: 8px; border-radius: 50%;
  background: #22c55e;
  box-shadow: 0 0 0 3px rgba(34,197,94,0.2);
  animation: pulse-green 2s infinite;
  flex-shrink: 0;
}
@keyframes pulse-green{
  0%,100%{ box-shadow: 0 0 0 3px rgba(34,197,94,0.2); }
  50%{ box-shadow: 0 0 0 6px rgba(34,197,94,0.08); }
}
.hero-image-badge-text{
  font-size: 12px; font-weight: 600; color: #fff; white-space: nowrap;
}
.hero-image-badge-text span{ color: rgba(255,255,255,0.45); font-weight: 400; }

/* ═══════════════════════════════════════════
   TRUST BAR
═══════════════════════════════════════════ */
.trust{
  background: var(--soft-bg);
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  padding: 28px 0;
}
.trust-inner{
  display: flex; align-items: center; justify-content: center;
  gap: clamp(24px, 4vw, 60px); flex-wrap: wrap;
}
.trust-label{
  font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
  color: var(--t4); white-space: nowrap;
}
.trust-items{ display: flex; align-items: center; gap: clamp(20px, 3vw, 44px); flex-wrap: wrap; }
.trust-item{
  display: flex; align-items: center; gap: 7px;
  font-size: 13px; font-weight: 600; color: var(--t3);
}
.trust-stars{ color: #F59E0B; font-size: 11px; letter-spacing: 1px; }

/* ═══════════════════════════════════════════
   SECTION HEADERS
═══════════════════════════════════════════ */
.section-eyebrow{
  display: inline-block;
  font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
  color: var(--brand); margin-bottom: 14px;
}
.section-title{
  font-family: var(--font-head);
  font-size: clamp(1.8rem, 3.5vw, 2.8rem);
  font-weight: 800; line-height: 1.12; letter-spacing: -.03em;
  color: var(--text); margin-bottom: 16px;
}
.section-sub{
  font-size: 1rem; color: var(--t3); line-height: 1.7; max-width: 520px;
}
.section-header{ margin-bottom: clamp(40px, 6vw, 64px); }
.section-header.centered{ text-align: center; }
.section-header.centered .section-sub{ margin: 0 auto; }

/* ═══════════════════════════════════════════
   WORKER CARDS
═══════════════════════════════════════════ */
.workers{ background: var(--white); }
.worker-grid{
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}
.worker-card{
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  overflow: hidden;
  transition: transform .2s, box-shadow .2s;
  display: flex; flex-direction: column;
}
.worker-card:hover{
  transform: translateY(-4px);
  box-shadow: 0 20px 48px rgba(0,0,0,0.10);
}
.worker-card-top{
  height: 4px; width: 100%;
}
.worker-card-image{
  position: relative;
  background: var(--soft-bg);
  overflow: hidden;
}
.worker-card-image img{
  width: 100%;
  height: 260px;
  object-fit: cover;
  object-position: center top;
  display: block;
}
.worker-card-status{
  position: absolute; top: 12px; right: 12px;
  padding: 4px 10px; border-radius: 20px;
  font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
  display: flex; align-items: center; gap: 5px;
  backdrop-filter: blur(8px);
}
.worker-card-status.live{
  background: rgba(34,197,94,0.15); color: #16a34a;
  border: 1px solid rgba(34,197,94,0.25);
}
.worker-card-status.soon{
  background: rgba(0,0,0,0.4); color: rgba(255,255,255,0.7);
  border: 1px solid rgba(255,255,255,0.15);
}
.worker-card-status-dot{
  width: 5px; height: 5px; border-radius: 50%;
  background: currentColor;
}
.worker-card-body{
  padding: 20px; flex: 1; display: flex; flex-direction: column;
}
.worker-card-icon{
  width: 36px; height: 36px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 12px; flex-shrink: 0;
}
.worker-card-icon svg{ width: 18px; height: 18px; }
.worker-card-name{
  font-family: var(--font-head);
  font-size: 1.25rem; font-weight: 800;
  letter-spacing: -.02em; margin-bottom: 2px;
}
.worker-card-role{
  font-size: 12px; font-weight: 500; color: var(--t3); margin-bottom: 12px;
}
.worker-card-quote{
  font-size: 14px; color: var(--t2); line-height: 1.65; margin-bottom: 20px; flex: 1;
  font-style: italic;
}
.btn-worker{
  display: inline-flex; align-items: center; gap: 6px;
  padding: 9px 16px; border-radius: 9px;
  font-size: 13px; font-weight: 700; border: none;
  transition: opacity .15s, transform .15s;
  color: #fff; width: 100%; justify-content: center;
}
.btn-worker:hover{ opacity:.85; transform:translateY(-1px); }
.btn-worker-ghost{
  display: inline-flex; align-items: center; gap: 6px;
  padding: 9px 16px; border-radius: 9px;
  font-size: 13px; font-weight: 600;
  width: 100%; justify-content: center;
  background: transparent; transition: opacity .15s;
  border: 1px solid var(--border); color: var(--t3);
}

/* ═══════════════════════════════════════════
   TIMELINE — A DAY INSIDE UNIT
═══════════════════════════════════════════ */
.timeline-section{ background: var(--dark); }
.timeline-section .section-eyebrow{ color: #a78bfa; }
.timeline-section .section-title{ color: #fff; }
.timeline-section .section-sub{ color: rgba(255,255,255,0.45); }
.timeline{
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0;
  position: relative;
}
.timeline::before{
  content: '';
  position: absolute;
  top: 28px; left: 10%; right: 10%;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(124,58,237,0.6), rgba(124,58,237,0.6), rgba(124,58,237,0.6), transparent);
}
.timeline-item{
  display: flex; flex-direction: column; align-items: center;
  padding: 0 12px;
  text-align: center;
}
.timeline-node{
  width: 56px; height: 56px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 20px;
  position: relative; z-index: 1;
  border: 2px solid rgba(255,255,255,0.1);
  background: var(--dark2);
  flex-shrink: 0;
}
.timeline-node svg{ width: 22px; height: 22px; }
.timeline-time{
  font-size: 11px; font-weight: 700; letter-spacing: .06em;
  margin-bottom: 8px;
}
.timeline-event{
  font-size: 13px; color: rgba(255,255,255,0.55); line-height: 1.6;
}
.timeline-event strong{ color: rgba(255,255,255,0.85); display: block; font-weight: 600; margin-bottom: 3px; }
.timeline-item:last-child .timeline-node{
  background: var(--brand); border-color: var(--brand);
}
.timeline-item:last-child .timeline-time{ color: #a78bfa; }

/* ═══════════════════════════════════════════
   HOW THEY WORK
═══════════════════════════════════════════ */
.howworks{ background: var(--soft-bg); }
.steps-grid{
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}
.step-card{
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r-lg);
  padding: 28px 24px;
}
.step-number{
  font-family: var(--font-head);
  font-size: 2.5rem; font-weight: 800;
  color: var(--border); margin-bottom: 16px; line-height: 1;
}
.step-icon{
  width: 44px; height: 44px; border-radius: 12px;
  background: var(--brand-soft);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 16px;
}
.step-icon svg{ width: 22px; height: 22px; color: var(--brand); }
.step-title{
  font-family: var(--font-head);
  font-size: 1.1rem; font-weight: 800;
  margin-bottom: 10px; letter-spacing: -.02em;
}
.step-desc{
  font-size: 14px; color: var(--t3); line-height: 1.65;
}

/* ═══════════════════════════════════════════
   CTA BANNER
═══════════════════════════════════════════ */
.cta-banner{
  background: var(--brand);
  padding: clamp(56px, 7vw, 96px) 0;
  position: relative; overflow: hidden;
}
.cta-banner::before{
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(ellipse 80% 80% at 50% 120%, rgba(255,255,255,0.08) 0%, transparent 70%);
}
.cta-banner-inner{
  text-align: center; position: relative; z-index: 1;
}
.cta-banner h2{
  font-family: var(--font-head);
  font-size: clamp(1.8rem, 4vw, 3rem);
  font-weight: 800; color: #fff; letter-spacing: -.03em;
  margin-bottom: 16px;
}
.cta-banner p{
  font-size: 1rem; color: rgba(255,255,255,0.65);
  margin-bottom: 32px;
}
.btn-white{
  display: inline-flex; align-items: center; gap: 8px;
  padding: 14px 28px; border-radius: 12px;
  font-size: 15px; font-weight: 700;
  background: #fff; color: var(--brand); border: none;
  transition: opacity .15s, transform .15s, box-shadow .15s;
  box-shadow: 0 4px 24px rgba(0,0,0,0.2);
}
.btn-white:hover{ opacity:.94; transform:translateY(-2px); box-shadow: 0 12px 32px rgba(0,0,0,0.3); }
.cta-note{
  margin-top: 14px; font-size: 13px; color: rgba(255,255,255,0.45);
}

/* ═══════════════════════════════════════════
   FOOTER
═══════════════════════════════════════════ */
.footer{
  background: var(--dark);
  border-top: 1px solid rgba(255,255,255,0.07);
  padding: clamp(40px, 6vw, 72px) 0 32px;
}
.footer-grid{
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 48px;
  margin-bottom: 48px;
}
.footer-brand-name{
  font-family: var(--font-head);
  font-size: 1.2rem; font-weight: 800;
  color: #fff; margin-bottom: 12px;
}
.footer-brand-desc{
  font-size: 14px; color: rgba(255,255,255,0.35);
  line-height: 1.7; max-width: 240px; margin-bottom: 24px;
}
.footer-col-title{
  font-size: 11px; font-weight: 700; letter-spacing: .1em;
  text-transform: uppercase; color: rgba(255,255,255,0.3);
  margin-bottom: 16px;
}
.footer-links{ list-style: none; display: flex; flex-direction: column; gap: 10px; }
.footer-links a{
  font-size: 14px; color: rgba(255,255,255,0.45);
  transition: color .15s;
}
.footer-links a:hover{ color: rgba(255,255,255,0.85); }
.footer-bottom{
  border-top: 1px solid rgba(255,255,255,0.07);
  padding-top: 28px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 12px;
}
.footer-bottom p{
  font-size: 13px; color: rgba(255,255,255,0.25);
}

/* ═══════════════════════════════════════════
   MOBILE NAV DRAWER
═══════════════════════════════════════════ */
.mobile-menu{
  display: none;
  position: fixed; inset: 0; z-index: 200;
  background: var(--dark);
  flex-direction: column;
  padding: 24px var(--pad);
}
.mobile-menu.open{ display: flex; }
.mobile-menu-top{
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 40px;
}
.mobile-close{
  background: none; border: none;
  color: rgba(255,255,255,0.6); font-size: 24px; line-height: 1;
}
.mobile-nav-links{
  list-style: none; display: flex; flex-direction: column; gap: 8px;
}
.mobile-nav-links a{
  display: block; padding: 14px 0;
  font-size: 1.1rem; font-weight: 600; color: rgba(255,255,255,0.75);
  border-bottom: 1px solid rgba(255,255,255,0.07);
  transition: color .15s;
}
.mobile-nav-links a:hover{ color: #fff; }
.mobile-nav-ctas{ margin-top: 32px; display: flex; flex-direction: column; gap: 12px; }

/* ═══════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════ */
@media(max-width: 1024px){
  .worker-grid{ grid-template-columns: repeat(2, 1fr); }
  .steps-grid{ grid-template-columns: repeat(2, 1fr); }
  .footer-grid{ grid-template-columns: 1fr 1fr; gap: 32px; }
  .timeline{ gap: 16px; }
  .timeline::before{ display: none; }
}

@media(max-width: 768px){
  .nav-links, .nav-actions{ display: none; }
  .nav-mobile-toggle{ display: flex; }
  .hero-inner{
    grid-template-columns: 1fr;
    text-align: center;
    padding-top: 80px; padding-bottom: 48px;
  }
  .hero-sub{ margin: 0 auto 32px; }
  .hero-ctas{ justify-content: center; }
  .hero-proof{ justify-content: center; }
  .hero-image{ order: -1; }
  .hero-image img{ max-height: 380px; }
  .hero-image-badge{ left: 8px; bottom: 8px; }
  .timeline{ grid-template-columns: repeat(2, 1fr); }
  .footer-grid{ grid-template-columns: 1fr; gap: 28px; }
  .footer-bottom{ flex-direction: column; text-align: center; }
}

@media(max-width: 480px){
  .worker-grid{ grid-template-columns: 1fr; }
  .steps-grid{ grid-template-columns: 1fr; }
  .timeline{ grid-template-columns: 1fr; }
  .hero-ctas{ flex-direction: column; align-items: stretch; }
  .btn-primary-lg, .btn-ghost-lg{ justify-content: center; width: 100%; }
}
</style>
</head>
<body>

<!-- ── NAV ── -->
<nav class="nav">
  <div class="wrap nav-inner">
    <a href="/" class="nav-logo">
      <div class="nav-logo-mark">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
      </div>
      <span class="nav-logo-name">UNIT</span>
    </a>

    <ul class="nav-links">
      <li><a href="#workers">Meet the Team</a></li>
      <li><a href="#how-it-works">How It Works</a></li>
      <li><a href="{{ route('public.pricing') }}">Pricing</a></li>
      <li><a href="{{ route('marketplace') }}">Marketplace</a></li>
    </ul>

    <div class="nav-actions">
      <a href="{{ route('login') }}" class="btn-ghost-nav">Log in</a>
      <a href="{{ route('register') }}" class="btn-primary">
        Hire Your First Worker
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>

    <button class="nav-mobile-toggle" id="menu-open" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- ── MOBILE MENU ── -->
<div class="mobile-menu" id="mobile-menu">
  <div class="mobile-menu-top">
    <a href="/" class="nav-logo">
      <div class="nav-logo-mark">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
      </div>
      <span class="nav-logo-name">UNIT</span>
    </a>
    <button class="mobile-close" id="menu-close">✕</button>
  </div>
  <ul class="mobile-nav-links">
    <li><a href="#workers" onclick="closeMobileMenu()">Meet the Team</a></li>
    <li><a href="#how-it-works" onclick="closeMobileMenu()">How It Works</a></li>
    <li><a href="{{ route('public.pricing') }}" onclick="closeMobileMenu()">Pricing</a></li>
    <li><a href="{{ route('marketplace') }}" onclick="closeMobileMenu()">Marketplace</a></li>
  </ul>
  <div class="mobile-nav-ctas">
    <a href="{{ route('login') }}" class="btn-ghost-nav" style="text-align:center;padding:13px">Log in</a>
    <a href="{{ route('register') }}" class="btn-primary" style="padding:13px;justify-content:center">Hire Your First Worker →</a>
  </div>
</div>

<!-- ── HERO ── -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-left">
      <div class="hero-eyebrow">
        <span class="hero-eyebrow-dot"></span>
        AI Workforce Platform
      </div>
      <h1 class="hero-headline">
        Meet the workers that never stop<br>
        <em>showing up.</em>
      </h1>
      <p class="hero-sub">
        AVA, DOX, MOX, and NUX are your founding AI team — each one built for a specific job, running 24/7, and getting better every single day.
      </p>
      <div class="hero-ctas">
        <a href="{{ route('register') }}" class="btn-primary-lg">
          Hire Your First Worker
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="#workers" class="btn-ghost-lg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M10 8l6 4-6 4V8z" fill="currentColor" stroke="none"/></svg>
          Meet the Team
        </a>
      </div>
      <div class="hero-proof">
        <div class="hero-proof-avatars">
          <span style="background:#7C3AED">A</span>
          <span style="background:#059669">D</span>
          <span style="background:#D97706">M</span>
          <span style="background:#2563EB">N</span>
        </div>
        <p class="hero-proof-text"><strong>4 workers.</strong> One platform. Every workflow covered.</p>
      </div>
    </div>

    <div class="hero-image">
      <img src="/images/hero-team.png" alt="The UNIT AI workforce team — AVA, DOX, MOX, and NUX">
      <div class="hero-image-badge">
        <div class="hero-image-badge-dot"></div>
        <div class="hero-image-badge-text">
          All 4 workers online <span>· Processing now</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── TRUST BAR ── -->
<div class="trust">
  <div class="wrap trust-inner">
    <span class="trust-label">Trusted by teams at</span>
    <div class="trust-items">
      <div class="trust-item">
        <span class="trust-stars">★★★★★</span>
        G2
      </div>
      <div class="trust-item">
        <span class="trust-stars">★★★★★</span>
        Capterra
      </div>
      <div class="trust-item">
        <span class="trust-stars">★★★★★</span>
        Google
      </div>
      <div class="trust-item">
        <span class="trust-stars">★★★★★</span>
        Trustpilot
      </div>
    </div>
  </div>
</div>

<!-- ── WORKERS ── -->
<section class="workers section" id="workers">
  <div class="wrap">
    <div class="section-header centered">
      <div class="section-eyebrow">Meet the team</div>
      <h2 class="section-title">Four workers. Four specialties.<br>One goal: your success.</h2>
      <p class="section-sub">Each UNIT worker has one job — and does it exceptionally well. They run continuously, improve with every task, and report back on everything they do.</p>
    </div>

    <div class="worker-grid">

      {{-- AVA --}}
      <div class="worker-card">
        <div class="worker-card-top" style="background:var(--ava)"></div>
        <div class="worker-card-image">
          <img src="/images/ava.png" alt="AVA — Renewal Coordinator">
          <div class="worker-card-status live">
            <span class="worker-card-status-dot"></span> Live
          </div>
        </div>
        <div class="worker-card-body">
          <div class="worker-card-icon" style="background:var(--ava-soft)">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--ava)" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          </div>
          <div class="worker-card-name" style="color:var(--ava)">AVA</div>
          <div class="worker-card-role">Renewal Coordinator</div>
          <p class="worker-card-quote">"I remember the renewals everyone else forgets. Every deadline. Every client. Every time."</p>
          <a href="{{ route('workers.public', 'ava') }}" class="btn-worker" style="background:var(--ava)">
            Meet AVA
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </a>
        </div>
      </div>

      {{-- DOX --}}
      <div class="worker-card">
        <div class="worker-card-top" style="background:var(--dox)"></div>
        <div class="worker-card-image">
          <img src="/images/ava.png" alt="DOX — Document Organizer" style="filter:hue-rotate(135deg) saturate(0.8)">
          <div class="worker-card-status soon">
            <span class="worker-card-status-dot"></span> Coming Soon
          </div>
        </div>
        <div class="worker-card-body">
          <div class="worker-card-icon" style="background:var(--dox-soft)">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--dox)" stroke-width="2" stroke-linecap="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
          </div>
          <div class="worker-card-name" style="color:var(--dox)">DOX</div>
          <div class="worker-card-role">Document Organizer</div>
          <p class="worker-card-quote">"I organize the documents nobody wants to touch — so everything is exactly where you need it."</p>
          <button class="btn-worker-ghost" disabled>
            Joining the team soon
          </button>
        </div>
      </div>

      {{-- MOX --}}
      <div class="worker-card">
        <div class="worker-card-top" style="background:var(--mox)"></div>
        <div class="worker-card-image">
          <img src="/images/ava.png" alt="MOX — Brand Scout" style="filter:hue-rotate(200deg) saturate(0.75)">
          <div class="worker-card-status soon">
            <span class="worker-card-status-dot"></span> Coming Soon
          </div>
        </div>
        <div class="worker-card-body">
          <div class="worker-card-icon" style="background:var(--mox-soft)">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--mox)" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </div>
          <div class="worker-card-name" style="color:var(--mox)">MOX</div>
          <div class="worker-card-role">Brand Scout</div>
          <p class="worker-card-quote">"I search the world for moments your brand shouldn't miss — and surface them before you even ask."</p>
          <button class="btn-worker-ghost" disabled>
            Joining the team soon
          </button>
        </div>
      </div>

      {{-- NUX --}}
      <div class="worker-card">
        <div class="worker-card-top" style="background:var(--nux)"></div>
        <div class="worker-card-image">
          <img src="/images/ava.png" alt="NUX — Content Creator" style="filter:hue-rotate(270deg) saturate(0.7)">
          <div class="worker-card-status soon">
            <span class="worker-card-status-dot"></span> Coming Soon
          </div>
        </div>
        <div class="worker-card-body">
          <div class="worker-card-icon" style="background:var(--nux-soft)">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--nux)" stroke-width="2" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          </div>
          <div class="worker-card-name" style="color:var(--nux)">NUX</div>
          <div class="worker-card-role">Content Creator</div>
          <p class="worker-card-quote">"I turn one idea into content people actually see — across every channel, every format, every time."</p>
          <button class="btn-worker-ghost" disabled>
            Joining the team soon
          </button>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ── TIMELINE ── -->
<section class="timeline-section section" id="how-it-works">
  <div class="wrap">
    <div class="section-header centered">
      <div class="section-eyebrow">A day inside UNIT</div>
      <h2 class="section-title" style="color:#fff">While you focus on growth,<br>they handle everything else.</h2>
      <p class="section-sub" style="color:rgba(255,255,255,0.45);margin:0 auto">From the moment your team logs in, your UNIT workers are already running.</p>
    </div>

    <div class="timeline">
      <div class="timeline-item">
        <div class="timeline-node" style="border-color:rgba(124,58,237,0.4)">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--ava)" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        </div>
        <div class="timeline-time" style="color:var(--ava)">8:00 AM</div>
        <div class="timeline-event"><strong>AVA</strong> processes your overnight renewals and queues 3 draft replies for review.</div>
      </div>
      <div class="timeline-item">
        <div class="timeline-node" style="border-color:rgba(5,150,105,0.4)">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--dox)" stroke-width="1.8" stroke-linecap="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
        </div>
        <div class="timeline-time" style="color:var(--dox)">9:30 AM</div>
        <div class="timeline-event"><strong>DOX</strong> organizes 1,247 files from your last 90 days and builds a searchable index.</div>
      </div>
      <div class="timeline-item">
        <div class="timeline-node" style="border-color:rgba(217,119,6,0.4)">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--mox)" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </div>
        <div class="timeline-time" style="color:var(--mox)">11:00 AM</div>
        <div class="timeline-event"><strong>MOX</strong> discovers a brand moment trending in your industry — surfaces it to your feed.</div>
      </div>
      <div class="timeline-item">
        <div class="timeline-node" style="border-color:rgba(37,99,235,0.4)">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--nux)" stroke-width="1.8" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        </div>
        <div class="timeline-time" style="color:var(--nux)">2:00 PM</div>
        <div class="timeline-event"><strong>NUX</strong> publishes six content pieces from a brief you wrote at breakfast.</div>
      </div>
      <div class="timeline-item">
        <div class="timeline-node">
          <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div class="timeline-time" style="color:#a78bfa">5:00 PM</div>
        <div class="timeline-event"><strong>You arrive.</strong> Everything is already done. Your team just needed a UNIT.</div>
      </div>
    </div>
  </div>
</section>

<!-- ── HOW WORKERS WORK ── -->
<section class="howworks section">
  <div class="wrap">
    <div class="section-header">
      <div class="section-eyebrow">Every worker has a life</div>
      <h2 class="section-title">They wake up. They receive work.<br>They improve. They write about their day.</h2>
      <p class="section-sub">They're not just tools — they're consistent, reliable, and always getting better.</p>
    </div>

    <div class="steps-grid">
      <div class="step-card">
        <div class="step-number">01</div>
        <div class="step-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 3a6 6 0 009 9 9 9 0 11-9-9z"/></svg>
        </div>
        <div class="step-title">Wake Up</div>
        <p class="step-desc">Every morning, workers come online, check their queue, and get ready for the day ahead — no alarm required.</p>
      </div>
      <div class="step-card">
        <div class="step-number">02</div>
        <div class="step-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        </div>
        <div class="step-title">Receive Work</div>
        <p class="step-desc">New tasks flow in automatically — from your inbox, your integrations, or direct assignments you push their way.</p>
      </div>
      <div class="step-card">
        <div class="step-number">03</div>
        <div class="step-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <div class="step-title">Do the Work</div>
        <p class="step-desc">They execute with precision — classify, draft, organize, publish. Every action logged. Every result reviewable.</p>
      </div>
      <div class="step-card">
        <div class="step-number">04</div>
        <div class="step-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        </div>
        <div class="step-title">Write Their Diary</div>
        <p class="step-desc">At the end of each day, every worker reflects, logs learnings, and gets smarter for tomorrow's tasks.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA BANNER ── -->
<section class="cta-banner">
  <div class="wrap cta-banner-inner">
    <h2>Hire your first worker today.</h2>
    <p>Start with one. Add more as you grow. No credit card required.</p>
    <a href="{{ route('register') }}" class="btn-white">
      Get Started Free
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
    </a>
    <p class="cta-note">10 free transactions on any worker. No card required.</p>
  </div>
</section>

<!-- ── FOOTER ── -->
<footer class="footer">
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <div class="footer-brand-name">UNIT</div>
        <p class="footer-brand-desc">A platform for deploying purpose-built AI workers. Each worker is trained for a specific workflow and runs on your team.</p>
      </div>
      <div>
        <div class="footer-col-title">Workers</div>
        <ul class="footer-links">
          <li><a href="{{ route('workers.public', 'ava') }}">AVA — Renewal Coordinator</a></li>
          <li><a href="{{ route('marketplace') }}">All Workers</a></li>
          <li><a href="{{ route('referral.index') }}">Refer &amp; Earn</a></li>
        </ul>
      </div>
      <div>
        <div class="footer-col-title">Product</div>
        <ul class="footer-links">
          <li><a href="#how-it-works">How It Works</a></li>
          <li><a href="{{ route('marketplace') }}">Marketplace</a></li>
          <li><a href="{{ route('public.pricing') }}">Pricing</a></li>
          <li><a href="{{ route('register') }}">Sign Up Free</a></li>
        </ul>
      </div>
      <div>
        <div class="footer-col-title">Legal</div>
        <ul class="footer-links">
          <li><a href="/privacy">Privacy Policy</a></li>
          <li><a href="/terms">Terms of Use</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© {{ date('Y') }} UNIT. All rights reserved.</p>
      <p style="color:rgba(255,255,255,0.2);font-size:12px">Built to work while you don't.</p>
    </div>
  </div>
</footer>

<script>
// Mobile menu
const menuOpen  = document.getElementById('menu-open');
const menuClose = document.getElementById('menu-close');
const mobileMenu = document.getElementById('mobile-menu');

menuOpen.addEventListener('click',  () => mobileMenu.classList.add('open'));
menuClose.addEventListener('click', () => mobileMenu.classList.remove('open'));
function closeMobileMenu(){ mobileMenu.classList.remove('open'); }

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if(target){ e.preventDefault(); target.scrollIntoView({ behavior:'smooth', block:'start' }); }
  });
});
</script>
</body>
</html>

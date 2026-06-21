<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $emailTitle ?? 'UNIT' }}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; color: #18181b; -webkit-font-smoothing: antialiased; }
  .outer { background: #f4f4f5; padding: 40px 16px; }
  .container { max-width: 560px; margin: 0 auto; }

  /* Header */
  .header { background: #ffffff; border-radius: 12px 12px 0 0; padding: 28px 36px 20px; border-bottom: 3px solid #F5C100; }
  .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
  .logo-mark { width: 32px; height: 32px; background: #F5C100; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .logo-inner { width: 14px; height: 14px; background: #0a0f1e; border-radius: 2px; }
  .logo-name { font-size: 18px; font-weight: 900; color: #0a0f1e; letter-spacing: -0.5px; }

  /* Body */
  .body { background: #ffffff; padding: 36px 36px 32px; }
  h1 { font-size: 22px; font-weight: 800; color: #0a0f1e; line-height: 1.3; margin-bottom: 12px; }
  p { font-size: 15px; line-height: 1.65; color: #3f3f46; margin-bottom: 16px; }
  p:last-child { margin-bottom: 0; }
  strong { color: #0a0f1e; }
  a.btn { display: inline-block; background: #F5C100; color: #0a0f1e; font-size: 15px; font-weight: 700; text-decoration: none; padding: 13px 30px; border-radius: 8px; margin-top: 8px; }
  .divider { border: none; border-top: 1px solid #e4e4e7; margin: 28px 0; }

  /* Info rows */
  .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
  .info-table td { padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; vertical-align: top; }
  .info-table td:first-child { color: #71717a; width: 40%; }
  .info-table td:last-child { color: #0a0f1e; font-weight: 500; text-align: right; }
  .info-table tr:last-child td { border-bottom: none; }

  /* Badges */
  .badge-green { display: inline-flex; align-items: center; gap: 6px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; margin-bottom: 20px; }
  .badge-violet { display: inline-flex; align-items: center; gap: 6px; background: #f5f3ff; border: 1px solid #ddd6fe; color: #7c3aed; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; margin-bottom: 20px; }
  .dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

  /* Alert boxes */
  .alert-yellow { background: #fffbeb; border-left: 3px solid #F5C100; padding: 12px 16px; border-radius: 0 6px 6px 0; font-size: 14px; color: #92400e; margin: 16px 0; }
  .alert-red { background: #fff1f2; border-left: 3px solid #f43f5e; padding: 12px 16px; border-radius: 0 6px 6px 0; font-size: 14px; color: #9f1239; margin: 16px 0; }

  /* Confidence bar */
  .bar-wrap { background: #e4e4e7; border-radius: 4px; height: 6px; margin-top: 6px; }
  .bar-fill { height: 6px; border-radius: 4px; }

  /* Footer */
  .footer { background: #fafafa; border-radius: 0 0 12px 12px; border-top: 1px solid #e4e4e7; padding: 20px 36px; }
  .footer p { font-size: 12px; color: #a1a1aa; line-height: 1.6; margin: 0; }

  /* URL fallback */
  .url-fallback { word-break: break-all; font-size: 12px; color: #a1a1aa; margin-top: 8px; }

  @media (max-width: 600px) {
    .header, .body, .footer { padding-left: 20px; padding-right: 20px; }
    a.btn { display: block; text-align: center; }
  }
</style>
</head>
<body>
<div class="outer">
<div class="container">

  <div class="header">
    <div class="logo">
      <div class="logo-mark"><div class="logo-inner"></div></div>
      <span class="logo-name">UNIT</span>
    </div>
  </div>

  <div class="body">

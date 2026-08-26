{{-- Single source of truth for the public-site footer, included by every
     public page (home, /ai-workers, /ai-workers/{slug}, about, pricing,
     blog, terms, privacy, influencer/apply). Edit here, not per-page.
     Markup AND CSS both live here now (see public-nav.blade.php for why:
     CSS duplicated per-page is exactly how things drift unnoticed). --}}
{{-- Sitewide Organization + WebSite JSON-LD lives here since this partial
     already renders on every public page, one edit point, not nine. --}}
@once
<style>
.footer{background:#0A0A0A;padding:clamp(40px,6vw,72px) 0 28px}
.footer .ft-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:44px;margin-bottom:44px}
.footer .ft-name{font-size:1.15rem;font-weight:800;color:#fff;margin-bottom:10px}
.footer .ft-desc{font-size:13.5px;color:rgba(255,255,255,.6);line-height:1.7;max-width:220px;margin-bottom:20px}
.footer .ft-col-h{font-size:10.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:14px}
.footer .ft-links{display:flex;flex-direction:column;gap:9px}
.footer .ft-links a{font-size:13.5px;color:rgba(255,255,255,.7);transition:color .15s}
.footer .ft-links a:hover{color:#fff}
.footer .ft-bottom{border-top:1px solid rgba(255,255,255,.12);padding-top:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
.footer .ft-bottom p{font-size:12.5px;color:rgba(255,255,255,.45)}
@media(max-width:768px){
  .footer .ft-grid{grid-template-columns:1fr}
  .footer .ft-bottom{flex-direction:column;text-align:center}
}
</style>
@endonce
<script type="application/ld+json">{!! json_encode([
    '@@context'    => 'https://schema.org',
    '@type'       => 'Organization',
    'name'        => config('app.name'),
    'url'         => url('/'),
    'logo'        => asset('favicon.png'),
    'description' => config('app.name') . ' is an AI agent platform that helps businesses deploy specialized AI Workers to manage recurring business workflows, automate operations, and complete real work.',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@@context' => 'https://schema.org',
    '@type'    => 'WebSite',
    'name'     => config('app.name'),
    'url'      => url('/'),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<footer class="footer">
  <div class="w">
    <div class="ft-grid">
      <div>
        <div class="ft-name">{{ config('app.name') }}</div>
        <p class="ft-desc">{{ config('app.name') }} is an AI agent platform that helps businesses deploy specialized AI Workers to manage recurring business workflows, automate operations, and complete real work.</p>
      </div>
      <div>
        <div class="ft-col-h">Workers</div>
        <div class="ft-links">
          <a href="{{ route('public.workers.show', 'ava') }}">AVA: Renewals</a>
          <a href="{{ route('public.workers.index') }}">All Workers</a>
          <a href="{{ route('app.referral.index') }}">Refer &amp; Earn</a>
          <a href="{{ route('influencer.apply') }}">Partner Program</a>
        </div>
      </div>
      <div>
        <div class="ft-col-h">Platform</div>
        <div class="ft-links">
          <a href="{{ route('pricing') }}">Pricing</a>
          <a href="{{ route('register') }}">Sign Up Free</a>
          <a href="{{ route('login') }}">Log In</a>
        </div>
      </div>
      <div>
        <div class="ft-col-h">Company</div>
        <div class="ft-links">
          <a href="{{ route('about') }}">About Us</a>
          <a href="{{ route('blog') }}">Blog</a>
          <a href="{{ route('privacy') }}">Privacy Policy</a>
          <a href="{{ route('terms') }}">Terms of Use</a>
        </div>
      </div>
    </div>
    <div class="ft-bottom">
      <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
      <p>Built with purpose. Powered by AI.</p>
    </div>
  </div>
</footer>

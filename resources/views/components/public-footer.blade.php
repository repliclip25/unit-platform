{{-- Single source of truth for the public-site footer — included by every
     public page (home, /ai-workers, /ai-workers/{slug}, about, pricing,
     blog, terms, privacy, influencer/apply). Edit here, not per-page. --}}
<footer class="footer">
  <div class="w">
    <div class="ft-grid">
      <div>
        <div class="ft-name">UNIT</div>
        <p class="ft-desc">AI workers that show up every day, handle the work that slows you down, and help your business grow.</p>
      </div>
      <div>
        <div class="ft-col-h">Workers</div>
        <div class="ft-links">
          <a href="{{ route('public.workers.show', 'ava') }}">AVA — Renewals</a>
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
      <p>© {{ date('Y') }} UNIT. All rights reserved.</p>
      <p>Built with purpose. Powered by AI.</p>
    </div>
  </div>
</footer>

{{-- Shared analytics/tracking loader — included near </body> on both the
     public layout and the authenticated app layout so the public marketing
     funnel is measured, not just the logged-in app. Scripts only load after
     consent (or immediately if consent was already granted in a prior visit).
     Admins are excluded so internal traffic doesn't pollute analytics. --}}
@unless(auth()->check() && auth()->user()->isAdmin())
@if(config('services.gtm_id') || config('services.facebook_pixel_id'))
<div id="unit-consent-banner" style="display:none;position:fixed;left:16px;right:16px;bottom:16px;z-index:9999;max-width:520px;margin:0 auto;background:#0D0D0D;color:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 12px 40px rgba(0,0,0,.35);font-size:13.5px;line-height:1.5">
    <p style="margin:0 0 12px">We use cookies to understand site usage and improve UNIT. See our <a href="{{ route('privacy') }}" style="color:#F5C518;text-decoration:underline">privacy policy</a>.</p>
    <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" id="unit-consent-decline" style="padding:8px 16px;border-radius:8px;border:1px solid rgba(255,255,255,.25);background:none;color:#fff;font-weight:600;font-size:13px;cursor:pointer">Decline</button>
        <button type="button" id="unit-consent-accept" style="padding:8px 16px;border-radius:8px;border:none;background:#F5C518;color:#0D0D0D;font-weight:700;font-size:13px;cursor:pointer">Accept</button>
    </div>
</div>

<script>
(function () {
    var GTM_ID   = @json(config('services.gtm_id'));
    var PIXEL_ID = @json(config('services.facebook_pixel_id'));
    var USER_ID  = @json(auth()->id());
    var ROUTE    = @json(request()->route()?->getName() ?? '');
    var PAGE     = @json(request()->route()?->getName() ?? request()->path());
    var SECTION  = ROUTE.indexOf('workers.') === 0 ? 'workers'
                  : ROUTE.indexOf('billing') === 0 ? 'billing'
                  : (USER_ID ? 'dashboard' : 'public');

    window.initUnitAnalytics = function () {
        if (window.__unitAnalyticsLoaded) return;
        window.__unitAnalyticsLoaded = true;

        if (GTM_ID) {
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
            var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;
            j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer',GTM_ID);

            var nos = document.createElement('noscript');
            var ifr = document.createElement('iframe');
            ifr.src = 'https://www.googletagmanager.com/ns.html?id=' + GTM_ID;
            ifr.height = 0; ifr.width = 0; ifr.style.display = 'none'; ifr.style.visibility = 'hidden';
            nos.appendChild(ifr);
            document.body.appendChild(nos);
        }

        if (PIXEL_ID) {
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
            (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', PIXEL_ID);
            fbq('track', 'PageView');
            fbq('trackCustom', 'PlatformPage', { page: PAGE, user_id: USER_ID, section: SECTION });
        }
    };

    var consent = localStorage.getItem('unit-consent');
    if (consent === 'granted') {
        window.initUnitAnalytics();
    } else if (consent !== 'denied') {
        document.addEventListener('DOMContentLoaded', function () {
            var banner = document.getElementById('unit-consent-banner');
            if (banner) banner.style.display = 'block';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var accept  = document.getElementById('unit-consent-accept');
        var decline = document.getElementById('unit-consent-decline');
        var banner  = document.getElementById('unit-consent-banner');
        if (accept) accept.addEventListener('click', function () {
            localStorage.setItem('unit-consent', 'granted');
            if (banner) banner.style.display = 'none';
            window.initUnitAnalytics();
        });
        if (decline) decline.addEventListener('click', function () {
            localStorage.setItem('unit-consent', 'denied');
            if (banner) banner.style.display = 'none';
        });
    });
})();
</script>
@endif
@endunless

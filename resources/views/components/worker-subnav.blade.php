@props(['items' => []])
{{-- Page-specific in-page jump nav for worker profile pages (AVA, and future
     DOX/MOX/NUX pages per WORKER-PROFILE.md). Sits below the global
     <x-public-nav>, which stays identical across every public page - this is
     the one place a page is allowed to expose its own anchors, kept visually
     and structurally separate so it can never be mistaken for site nav. --}}
@once
<style>
.worker-subnav{position:sticky;top:62px;z-index:90;background:#fff;border-bottom:1px solid #E5E7EB;overflow-x:auto;scrollbar-width:none}
.worker-subnav::-webkit-scrollbar{display:none}
[data-theme="dark"] .worker-subnav{background:#0D0D0D;border-color:#2D2D2D}
.worker-subnav-i{display:flex;align-items:center;gap:28px;height:46px;max-width:1200px;margin:0 auto;padding:0 clamp(20px,5vw,48px);white-space:nowrap}
.worker-subnav-i a{font-size:13px;font-weight:600;color:#6B7280;transition:color .15s;flex-shrink:0}
[data-theme="dark"] .worker-subnav-i a{color:#9CA3AF}
.worker-subnav-i a:hover{color:#0D0D0D}
[data-theme="dark"] .worker-subnav-i a:hover{color:#F3F4F6}
</style>
@endonce
@if(count($items))
<nav class="worker-subnav">
  <div class="worker-subnav-i">
    @foreach($items as $item)
      <a href="{{ $item['href'] }}">{{ $item['label'] }}</a>
    @endforeach
  </div>
</nav>
@endif

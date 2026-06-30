<x-app-layout title="Platform Prompts">
<div class="max-w-4xl space-y-8">

  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-white">Platform Prompts</h1>
      <p class="text-gray-500 text-sm mt-1">All AI prompts used across platform features. Edits override the built-in defaults and take effect immediately.</p>
    </div>
    <a href="{{ route('admin.platform-usage') }}" class="text-xs text-gray-500 hover:text-white transition px-3 py-2 rounded-lg border border-gray-800 hover:border-gray-600">Token Usage →</a>
  </div>

  @if(session('saved'))
    <div class="bg-green-950/40 border border-green-800/50 rounded-xl px-4 py-3 text-green-300 text-sm">Prompt saved.</div>
  @endif
  @if(session('reset'))
    <div class="bg-blue-950/40 border border-blue-800/50 rounded-xl px-4 py-3 text-blue-300 text-sm">Prompt reset to default.</div>
  @endif

  @foreach($registry as $category => $prompts)
  <div>
    <div class="flex items-center gap-3 mb-4">
      <div class="text-xs font-bold uppercase tracking-widest text-gray-500">{{ $category }}</div>
      <div class="flex-1 border-t border-gray-800"></div>
    </div>

    <div class="space-y-4">
      @foreach($prompts as $key => $meta)
        @php
          $isCustom = $rows->has($key);
          $current  = $isCustom ? $rows->get($key)->value : ($defaults[$key] ?? '');
        @endphp

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
          <div class="flex items-start justify-between mb-3 gap-4">
            <div>
              <div class="text-white text-sm font-semibold">{{ $meta['label'] }}</div>
              <div class="text-gray-500 text-xs mt-0.5">{{ $meta['desc'] }}</div>
              @if(!empty($meta['placeholders']))
                <div class="text-gray-700 text-xs mt-2">
                  Placeholders:
                  @foreach($meta['placeholders'] as $ph)
                    <code class="text-gray-500 bg-gray-800 px-1.5 py-0.5 rounded text-xs ml-1">{{ $ph }}</code>
                  @endforeach
                </div>
              @endif
            </div>
            <span class="shrink-0 text-xs font-semibold px-2 py-0.5 rounded-full border
              {{ $isCustom ? 'border-yellow-700/60 text-yellow-500 bg-yellow-950/30' : 'border-gray-700 text-gray-600 bg-gray-800/40' }}">
              {{ $isCustom ? 'Custom' : 'Default' }}
            </span>
          </div>

          <form method="POST" action="{{ route('admin.prompts.update') }}">
            @csrf
            <input type="hidden" name="key" value="{{ $key }}">
            <textarea name="value" rows="8"
              class="w-full bg-gray-950 border border-gray-700 text-gray-200 rounded-xl px-4 py-3 text-xs leading-relaxed outline-none focus:border-gray-600 transition resize-y font-mono"
              style="min-height:160px">{{ $current }}</textarea>
            <div class="flex items-center justify-end mt-3 gap-2">
              @if($isCustom)
                <form method="POST" action="{{ route('admin.prompts.reset') }}" class="inline" onsubmit="return confirm('Reset to default?')">
                  @csrf
                  <input type="hidden" name="key" value="{{ $key }}">
                  <button type="submit" class="text-xs text-gray-500 hover:text-white transition px-3 py-1.5 rounded-lg border border-gray-700 hover:border-gray-500">Reset to default</button>
                </form>
              @endif
              <button type="submit" class="text-xs font-semibold px-4 py-1.5 rounded-lg" style="background:var(--accent);color:#000">Save</button>
            </div>
          </form>
        </div>
      @endforeach
    </div>
  </div>
  @endforeach

</div>
</x-app-layout>

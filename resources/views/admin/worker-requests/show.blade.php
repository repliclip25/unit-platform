<x-app-layout title="Worker Request — {{ $req->name }}">
<div class="space-y-6 max-w-4xl">

  <div class="flex items-center gap-3">
    <a href="{{ route('admin.worker-requests') }}" class="text-gray-500 hover:text-white text-xs transition">← All Requests</a>
  </div>

  @if(session('saved'))
    <div class="bg-green-950/40 border border-green-800/50 rounded-xl px-4 py-3 text-green-300 text-sm">Status updated.</div>
  @endif

  {{-- Header --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <div class="text-white font-semibold text-lg">{{ $req->name }}</div>
        <div class="text-gray-500 text-sm mt-0.5">{{ $req->email }}</div>
        @if($req->company)
          <div class="text-gray-400 text-xs mt-1">{{ $req->company }} @if($req->role)· {{ $req->role }}@endif</div>
        @endif
      </div>
      <form method="POST" action="{{ route('admin.worker-requests.status', $req->id) }}" class="flex items-center gap-2">
        @csrf
        <select name="status" class="bg-gray-800 border border-gray-700 text-gray-200 text-xs rounded-lg px-3 py-2 outline-none">
          @foreach(['pending','contacted','scoping','building','done','declined'] as $s)
            <option value="{{ $s }}" @selected($req->status === $s)>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
        <button type="submit" class="text-xs px-3 py-2 rounded-lg font-semibold" class="ac-on">Save</button>
      </form>
    </div>
    <div class="mt-3 flex items-center gap-3 text-xs text-gray-600">
      @if($req->org)<span class="text-gray-400 font-medium">{{ $req->org }}</span> · @endif
      @if($req->volume)<span>Volume: {{ $req->volume }}</span> · @endif
      <span>Submitted {{ \Carbon\Carbon::parse($req->created_at)->format('M j, Y g:ia') }}</span>
    </div>
  </div>

  {{-- Submission fields --}}
  <div class="grid grid-cols-1 gap-4">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
      <div class="text-gray-500 text-xs font-semibold uppercase tracking-widest mb-3">Current Process</div>
      <p class="text-gray-200 text-sm leading-relaxed whitespace-pre-wrap">{{ $req->current_process }}</p>
    </div>

    @if($req->pain_points)
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
      <div class="text-gray-500 text-xs font-semibold uppercase tracking-widest mb-3">What Goes Wrong / Slows Them Down</div>
      <p class="text-gray-200 text-sm leading-relaxed whitespace-pre-wrap">{{ $req->pain_points }}</p>
    </div>
    @endif

    @if($req->ai_followup)
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
      <div class="text-gray-500 text-xs font-semibold uppercase tracking-widest mb-3">AI Follow-Up Sent</div>
      <p class="text-gray-200 text-sm leading-relaxed whitespace-pre-wrap">{{ $req->ai_followup }}</p>
    </div>
    @endif
  </div>

  {{-- Danger zone --}}
  <div class="bg-gray-900 border border-red-900/40 rounded-2xl p-5 flex items-center justify-between">
    <div>
      <div class="text-red-400 text-sm font-semibold">Delete this request</div>
      <div class="text-gray-600 text-xs mt-0.5">Permanently removes the submission and all associated data.</div>
    </div>
    <form method="POST" action="{{ route('admin.worker-requests.destroy', $req->id) }}" onsubmit="return confirm('Delete this request? This cannot be undone.')">
      @csrf @method('DELETE')
      <button type="submit" class="text-xs px-4 py-2 rounded-lg border border-red-800/60 text-red-400 hover:bg-red-950/40 transition font-semibold">Delete</button>
    </form>
  </div>

</div>
</x-app-layout>

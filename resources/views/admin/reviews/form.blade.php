<x-app-layout title="Add Review">
<div class="max-w-2xl mx-auto space-y-4">

  <div class="flex items-center gap-3">
    <a href="{{ route('admin.reviews') }}" class="text-gray-500 hover:text-white text-xs transition">← Reviews</a>
    <span class="text-gray-700">/</span>
    <span class="text-gray-400 text-xs">New Review</span>
  </div>

  <p class="text-gray-500 text-xs">Transcribe a real review received by email or elsewhere. Set status to Approved to make it live immediately, or Pending to hold it for review first.</p>

  @if($errors->any())
    <div class="bg-red-950/40 border border-red-800/50 rounded-xl px-4 py-3 text-red-300 text-sm space-y-1">
      @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('admin.reviews.store') }}">
    @csrf

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-4">
      <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Worker *</label>
      <select name="worker_slug" class="w-full bg-gray-950 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm outline-none focus:border-gray-500 transition" required>
        @foreach($workers as $w)
          <option value="{{ $w->slug }}" {{ old('worker_slug') === $w->slug ? 'selected' : '' }}>{{ $w->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Author Name *</label>
        <input type="text" name="author_name" value="{{ old('author_name') }}"
          class="w-full bg-gray-950 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm outline-none focus:border-gray-500 transition"
          placeholder="e.g. Maria T." required>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Company</label>
        <input type="text" name="author_company" value="{{ old('author_company') }}"
          class="w-full bg-gray-950 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm outline-none focus:border-gray-500 transition"
          placeholder="Optional, e.g. BuildCo">
      </div>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-4">
      <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Rating *</label>
      <select name="rating" class="w-full bg-gray-950 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm outline-none focus:border-gray-500 transition" required>
        @for($i = 5; $i >= 1; $i--)
          <option value="{{ $i }}" {{ old('rating', 5) == $i ? 'selected' : '' }}>{{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }} ({{ $i }})</option>
        @endfor
      </select>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-4">
      <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Quote *</label>
      <textarea name="quote" rows="4"
        class="w-full bg-gray-950 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm outline-none focus:border-gray-500 transition"
        placeholder="The actual review text, verbatim." required>{{ old('quote') }}</textarea>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-4">
      <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Status *</label>
      <select name="status" class="w-full bg-gray-950 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm outline-none focus:border-gray-500 transition" required>
        <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Approved (live immediately)</option>
        <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pending (hold for review)</option>
      </select>
    </div>

    <button type="submit" class="text-xs px-5 py-2.5 rounded-lg font-semibold ac-on">Save Review</button>
  </form>

</div>
</x-app-layout>

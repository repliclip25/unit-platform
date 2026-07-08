<x-app-layout title="{{ $post ? 'Edit Post' : 'New Blog Post' }}">

{{-- Quill CSS loaded inline since layout has no @stack --}}
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
/* Quill dark theme overrides */
.ql-toolbar.ql-snow{
  background:#1a1a24;border:1px solid #2e2e40;border-bottom:none;
  border-radius:12px 12px 0 0;padding:10px 12px;flex-wrap:wrap;
}
.ql-container.ql-snow{
  background:#111118;border:1px solid #2e2e40;border-radius:0 0 12px 12px;
  min-height:340px;font-size:15px;color:#d8d8e8;font-family:inherit;
}
.ql-editor{min-height:320px;line-height:1.75;padding:16px 20px}
.ql-editor.ql-blank::before{color:#444458;font-style:normal}
.ql-snow .ql-stroke{stroke:#7070a0}
.ql-snow .ql-fill,.ql-snow .ql-stroke.ql-fill{fill:#7070a0}
.ql-snow .ql-picker{color:#7070a0}
.ql-snow .ql-picker-options{background:#1e1e2e;border:1px solid #2e2e40;border-radius:8px}
.ql-snow .ql-picker-item:hover,.ql-snow .ql-picker-item.ql-selected{color:#142C74}
.ql-snow button:hover .ql-stroke,.ql-snow button.ql-active .ql-stroke{stroke:#142C74}
.ql-snow button:hover .ql-fill,.ql-snow button.ql-active .ql-fill{fill:#142C74}
.ql-snow button:hover,.ql-snow .ql-picker-label:hover{color:#142C74}
.ql-snow .ql-active{color:#142C74}
.ql-snow .ql-formats{margin-right:10px}
</style>

<div class="max-w-3xl mx-auto space-y-4">

  <div class="flex items-center gap-3">
    <a href="{{ route('admin.blog') }}" class="text-gray-500 hover:text-white text-xs transition">← Blog Posts</a>
    <span class="text-gray-700">/</span>
    <span class="text-gray-400 text-xs">{{ $post ? 'Edit' : 'New Post' }}</span>
  </div>

  @if($errors->any())
    <div class="bg-red-950/40 border border-red-800/50 rounded-xl px-4 py-3 text-red-300 text-sm space-y-1">
      @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
  @endif

  <form method="POST" action="{{ $post ? route('admin.blog.update', $post->id) : route('admin.blog.store') }}" id="blog-form" enctype="multipart/form-data">
    @csrf
    @if($post) @method('PUT') @endif

    {{-- Title --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-4">
      <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Title *</label>
      <input type="text" name="title" id="post-title" value="{{ old('title', $post->title ?? '') }}"
        class="w-full bg-gray-950 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm outline-none focus:border-gray-500 transition"
        placeholder="e.g. How AVA processes a NYCSCA renewal in under 5 minutes" required>
    </div>

    {{-- Slug + Tag responsive row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}"
          class="w-full bg-gray-950 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm outline-none focus:border-gray-500 transition font-mono"
          placeholder="auto-generated from title">
        <p class="text-gray-600 text-xs mt-2">Leave blank to auto-generate.</p>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Tag / Category *</label>
        <input type="text" name="tag" id="post-tag" value="{{ old('tag', $post->tag ?? '') }}"
          class="w-full bg-gray-950 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm outline-none focus:border-gray-500 transition"
          placeholder="e.g. Automation" required>
      </div>
    </div>

    {{-- Author + Worker responsive row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Author</label>
        <input type="text" name="author" value="{{ old('author', $post->author ?? 'UNIT') }}"
          class="w-full bg-gray-950 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm outline-none focus:border-gray-500 transition"
          placeholder="Franklin">
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Worker</label>
        <select name="worker_slug" class="w-full bg-gray-950 border border-gray-700 text-gray-300 text-sm rounded-xl px-3 py-2.5 outline-none focus:border-gray-500 transition">
          <option value="">— None —</option>
          @foreach($workers as $w)
            <option value="{{ $w->slug }}" @selected(old('worker_slug', $post->worker_slug ?? '') === $w->slug)>
              {{ strtoupper($w->slug) }} — {{ $w->name }}
            </option>
          @endforeach
        </select>
        <p class="text-gray-600 text-xs mt-2">Associate with a specific worker.</p>
      </div>
    </div>

    {{-- Excerpt --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-4">
      <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-2">Excerpt *</label>
      <textarea name="excerpt" rows="2"
        class="w-full bg-gray-950 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm outline-none focus:border-gray-500 transition resize-none"
        placeholder="A one or two sentence summary that appears on the blog index card." required>{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
      <p class="text-gray-600 text-xs mt-2">Keep under 180 characters for best display on the card.</p>
    </div>

    {{-- Cover Image --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-4">
      <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest mb-3">Cover Image</label>

      @if($post && $post->cover_image)
      <div class="mb-3 flex items-start gap-4">
        <img src="{{ Storage::url($post->cover_image) }}" alt="Cover" class="rounded-xl object-cover" style="height:120px;width:200px">
        <div>
          <p class="text-gray-500 text-xs mb-2">Current cover image.</p>
          <label class="flex items-center gap-2 text-xs text-red-400 cursor-pointer">
            <input type="checkbox" name="remove_cover_image" value="1" class="rounded">
            Remove this image
          </label>
        </div>
      </div>
      @endif

      <div id="img-drop-zone"
        class="relative border-2 border-dashed border-gray-700 rounded-xl p-6 text-center cursor-pointer transition hover:border-gray-500"
        onclick="document.getElementById('cover_image_input').click()">
        <input type="file" name="cover_image" id="cover_image_input" accept="image/*" class="hidden">
        <div id="img-preview" class="hidden mb-3">
          <img id="img-preview-img" src="" alt="" class="mx-auto rounded-lg object-cover" style="max-height:160px;max-width:100%">
        </div>
        <div id="img-placeholder">
          <svg class="mx-auto mb-2 text-gray-600" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
          <p class="text-gray-500 text-sm">Click to upload or drag and drop</p>
          <p class="text-gray-700 text-xs mt-1">JPG, PNG, WebP, GIF — max 4MB</p>
        </div>
      </div>
    </div>

    {{-- Body WYSIWYG --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-4">
      <div class="flex items-center justify-between mb-4">
        <label class="block text-gray-500 text-xs font-semibold uppercase tracking-widest">Body *</label>
        <button type="button" id="ai-rewrite-btn"
          class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg transition"
          style="background:rgba(241,211,98,0.12);color:#142C74;border:1px solid rgba(241,211,98,0.25)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          AI Rewrite
        </button>
      </div>
      <div id="ai-status" class="hidden text-xs mb-3 flex items-center gap-2" style="color:#142C74">
        <svg class="animate-spin shrink-0" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        Rewriting with Claude...
      </div>
      {{-- Quill editor --}}
      <div id="quill-editor">{!! old('body', $post->body ?? '') !!}</div>
      <input type="hidden" name="body" id="body-input">
    </div>

    {{-- Status + Submit --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <label class="text-gray-400 text-sm font-medium">Status</label>
        <select name="status" class="bg-gray-800 border border-gray-700 text-gray-200 text-sm rounded-lg px-3 py-2 outline-none">
          <option value="draft" @selected(old('status', $post->status ?? 'draft') === 'draft')>Draft — hidden</option>
          <option value="published" @selected(old('status', $post->status ?? '') === 'published')>Published — live</option>
        </select>
      </div>
      <div class="flex items-center gap-3">
        <a href="{{ route('admin.blog') }}" class="text-xs text-gray-500 hover:text-white transition px-3 py-2">Cancel</a>
        <button type="submit" class="text-sm px-5 py-2.5 rounded-lg font-semibold" class="ac-on">
          {{ $post ? 'Save Changes' : 'Create Post' }}
        </button>
      </div>
    </div>

  </form>

  @if($post)
  <div class="bg-gray-900 border border-red-900/40 rounded-2xl p-5 flex flex-wrap items-center justify-between gap-3">
    <div>
      <div class="text-red-400 text-sm font-semibold">Delete this post</div>
      <div class="text-gray-600 text-xs mt-0.5">Permanently removes this post. Published readers will get a 404.</div>
    </div>
    <form method="POST" action="{{ route('admin.blog.destroy', $post->id) }}" onsubmit="return confirm('Delete this post? This cannot be undone.')">
      @csrf @method('DELETE')
      <button type="submit" class="text-xs px-4 py-2 rounded-lg border border-red-800/60 text-red-400 hover:bg-red-950/40 transition font-semibold">Delete Post</button>
    </form>
  </div>
  @endif

</div>

{{-- Image preview --}}
<script>
document.getElementById('cover_image_input').addEventListener('change', function () {
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('img-preview-img').src = e.target.result;
    document.getElementById('img-preview').classList.remove('hidden');
    document.getElementById('img-placeholder').classList.add('hidden');
  };
  reader.readAsDataURL(file);
});

// Drag-and-drop
const zone = document.getElementById('img-drop-zone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = '#142C74'; });
zone.addEventListener('dragleave', () => { zone.style.borderColor = ''; });
zone.addEventListener('drop', e => {
  e.preventDefault();
  zone.style.borderColor = '';
  const file = e.dataTransfer.files[0];
  if (!file || !file.type.startsWith('image/')) return;
  const dt = new DataTransfer();
  dt.items.add(file);
  document.getElementById('cover_image_input').files = dt.files;
  document.getElementById('cover_image_input').dispatchEvent(new Event('change'));
});
</script>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
const quill = new Quill('#quill-editor', {
  theme: 'snow',
  placeholder: 'Start writing your article here...',
  modules: {
    toolbar: [
      [{ header: [2, 3, false] }],
      ['bold', 'italic', 'underline'],
      [{ list: 'ordered' }, { list: 'bullet' }],
      ['blockquote', 'link'],
      ['clean']
    ]
  }
});

// Copy Quill HTML to hidden input on submit
document.getElementById('blog-form').addEventListener('submit', function () {
  document.getElementById('body-input').value = quill.root.innerHTML;
});

// AI Rewrite
document.getElementById('ai-rewrite-btn').addEventListener('click', async function () {
  const draft = quill.root.innerHTML.trim();
  if (!draft || draft === '<p><br></p>') {
    alert('Write a draft first, then click AI Rewrite.');
    return;
  }
  const btn    = this;
  const status = document.getElementById('ai-status');
  btn.disabled = true;
  status.classList.remove('hidden');

  try {
    const res = await fetch('{{ route('admin.blog.ai-rewrite') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        draft: draft,
        title: document.getElementById('post-title').value,
        tag:   document.getElementById('post-tag').value,
      }),
    });
    const data = await res.json();
    if (data.html) {
      quill.root.innerHTML = data.html;
    } else {
      alert('AI rewrite failed: ' + (data.error || 'Unknown error'));
    }
  } catch (e) {
    alert('Request failed: ' + e.message);
  } finally {
    btn.disabled = false;
    status.classList.add('hidden');
  }
});
</script>
</x-app-layout>

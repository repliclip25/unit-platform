<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminBlogController extends Controller
{
    public function index()
    {
        $posts = DB::table('blog_posts')->orderByDesc('created_at')->get();
        return view('admin.blog.index', compact('posts'));
    }

    public function create()
    {
        $workers = \Illuminate\Support\Facades\DB::table('worker_registry')->select('slug', 'name')->orderBy('name')->get();
        return view('admin.blog.form', ['post' => null, 'workers' => $workers]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug']        = $this->uniqueSlug($data['slug'] ?: Str::slug($data['title']));
        $data['cover_image'] = $this->handleImageUpload($request, null);
        $data['created_at']  = now();
        $data['updated_at']  = now();

        DB::table('blog_posts')->insert($data);

        return redirect()->route('admin.blog')->with('saved', 'Post created.');
    }

    public function edit(int $id)
    {
        $post    = DB::table('blog_posts')->where('id', $id)->firstOrFail();
        $workers = DB::table('worker_registry')->select('slug', 'name')->orderBy('name')->get();
        return view('admin.blog.form', compact('post', 'workers'));
    }

    public function update(Request $request, int $id)
    {
        $data     = $this->validated($request);
        $existing = DB::table('blog_posts')->where('id', $id)->firstOrFail();

        if ($data['slug'] !== $existing->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'] ?: Str::slug($data['title']), $id);
        }

        $newImage = $this->handleImageUpload($request, $existing->cover_image ?? null);
        $data['cover_image'] = $newImage;
        $data['updated_at']  = now();

        DB::table('blog_posts')->where('id', $id)->update($data);

        return redirect()->route('admin.blog')->with('saved', 'Post updated.');
    }

    public function publish(int $id)
    {
        DB::table('blog_posts')->where('id', $id)->update([
            'status'     => 'published',
            'updated_at' => now(),
        ]);
        return back()->with('saved', 'Post published.');
    }

    public function destroy(int $id)
    {
        $post = DB::table('blog_posts')->where('id', $id)->first();
        if ($post && $post->cover_image) {
            Storage::disk(config('filesystems.media_disk', 'public'))->delete($post->cover_image);
        }
        DB::table('blog_posts')->where('id', $id)->delete();
        return redirect()->route('admin.blog')->with('saved', 'Post deleted.');
    }

    private function handleImageUpload(Request $request, ?string $existing): ?string
    {
        if ($request->boolean('remove_cover_image')) {
            if ($existing) Storage::disk(config('filesystems.media_disk', 'public'))->delete($existing);
            return null;
        }
        if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
            if ($existing) Storage::disk(config('filesystems.media_disk', 'public'))->delete($existing);
            return $request->file('cover_image')->store('blog', 'public');
        }
        return $existing;
    }

    public function aiRewrite(Request $request)
    {
        $request->validate([
            'draft' => 'required|string|max:20000',
            'title' => 'nullable|string|max:200',
            'tag'   => 'nullable|string|max:60',
        ]);

        try {
            $promptCtrl = new AdminPromptController();
            $defaults   = $promptCtrl->defaults();
            $settings   = \Illuminate\Support\Facades\DB::table('platform_settings')
                ->whereIn('key', ['blog_rewrite_system','blog_rewrite_user'])
                ->get()->keyBy('key');

            $system = $settings->has('blog_rewrite_system')
                ? $settings->get('blog_rewrite_system')->value
                : $defaults['blog_rewrite_system'];

            $userTemplate = $settings->has('blog_rewrite_user')
                ? $settings->get('blog_rewrite_user')->value
                : $defaults['blog_rewrite_user'];

            $userPrompt = str_replace(
                ['{draft}', '{title}', '{tag}'],
                [$request->draft, $request->title ?? '', $request->tag ?? ''],
                $userTemplate
            );

            $platform = new \App\Platform\Services\PlatformClaude();
            $html     = $platform->ask($system, $userPrompt, 2000, 'blog_rewrite', 'admin:blog_rewrite');

            return response()->json(['html' => $html]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function validated(Request $request): array
    {
        $request->validate([
            'title'        => 'required|string|max:200',
            'slug'         => 'nullable|string|max:200|regex:/^[a-z0-9\-]*$/',
            'tag'          => 'required|string|max:60',
            'excerpt'      => 'required|string|max:400',
            'body'         => 'required|string',
            'author'       => 'nullable|string|max:80',
            'status'       => 'required|in:draft,published',
            'worker_slug'  => 'nullable|string|max:60',
            'cover_image'  => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        return [
            'title'       => $request->title,
            'slug'        => $request->slug ?? '',
            'tag'         => $request->tag,
            'excerpt'     => $request->excerpt,
            'body'        => $request->body,
            'author'      => $request->author ?? 'UNIT',
            'status'      => $request->status,
            'worker_slug' => $request->worker_slug ?: null,
        ];
    }

    private function uniqueSlug(string $base, ?int $exceptId = null): string
    {
        $slug = $base;
        $i    = 1;
        while (true) {
            $q = DB::table('blog_posts')->where('slug', $slug);
            if ($exceptId) $q->where('id', '!=', $exceptId);
            if (!$q->exists()) break;
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}

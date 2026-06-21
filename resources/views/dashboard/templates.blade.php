<x-app-layout title="Email Templates">

    @if(session('success'))
        <div class="mb-4 bg-green-900 border border-green-700 text-green-200 rounded-xl px-5 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-3 gap-6">

        {{-- Template list --}}
        <div class="col-span-2 space-y-3">
            @foreach($templates->groupBy('category') as $category => $group)
                <div class="bg-gray-900 border border-gray-800 rounded-xl">
                    <div class="px-5 py-3 border-b border-gray-800 flex items-center justify-between">
                        <h3 class="text-white text-sm font-semibold">{{ $category }}</h3>
                        <span class="text-gray-600 text-xs">{{ $group->count() }} template(s)</span>
                    </div>
                    @foreach($group as $template)
                        <div class="px-5 py-4 border-b border-gray-800 last:border-0">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-white text-sm font-medium">{{ $template->name }}</p>
                                        @if(!$template->user_id)
                                            <span class="text-xs bg-gray-800 text-gray-500 px-2 py-0.5 rounded">Platform default</span>
                                        @endif
                                    </div>
                                    <p class="text-gray-500 text-xs mt-0.5">Tone: {{ $template->tone }}</p>
                                </div>
                                @if($template->user_id)
                                    <form method="POST" action="{{ route('templates.destroy', $template->id) }}">
                                        @csrf @method('DELETE')
                                        <button class="text-gray-600 hover:text-red-400 text-xs">Remove</button>
                                    </form>
                                @endif
                            </div>
                            <p class="text-gray-400 text-xs mb-1"><span class="text-gray-600">Subject:</span> {{ $template->subject_template }}</p>
                            <pre class="text-gray-500 text-xs whitespace-pre-wrap bg-gray-800 rounded-lg p-3 mt-2">{{ $template->body_template }}</pre>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Add template form --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl h-fit">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="text-white text-sm font-semibold">Add Custom Template</h3>
            </div>
            <form method="POST" action="{{ route('templates.store') }}" class="px-5 py-4 space-y-4">
                @csrf
                <div>
                    <label class="text-gray-400 text-xs block mb-1">Template Name</label>
                    <input type="text" name="name" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand" required>
                </div>
                <div>
                    <label class="text-gray-400 text-xs block mb-1">Category</label>
                    <select name="category" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                        <option>SSL Expiry</option>
                        <option>Domain Renewal</option>
                        <option>Hosting Invoice</option>
                        <option>SaaS Renewal</option>
                        <option>Failed Payment</option>
                        <option>Other</option>
                    </select>
                </div>
                <div>
                    <label class="text-gray-400 text-xs block mb-1">Tone</label>
                    <input type="text" name="tone" value="Professional, concise" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand">
                </div>
                <div>
                    <label class="text-gray-400 text-xs block mb-1">Subject Template</label>
                    <input type="text" name="subject_template" placeholder="e.g. Action Required: @{{asset}} expires @{{due_date}}" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand" required>
                </div>
                <div>
                    <label class="text-gray-400 text-xs block mb-1">Body Template</label>
                    <p class="text-gray-600 text-xs mb-1">Available: @{{contact_first_name}} @{{asset}} @{{client}} @{{due_date}} @{{sender_name}}</p>
                    <textarea name="body_template" rows="8" class="w-full bg-gray-800 text-white text-sm rounded-lg px-3 py-2 border border-gray-700 focus:outline-none focus:border-brand" required></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="approval_required" id="approval_required" value="1" checked class="rounded">
                    <label for="approval_required" class="text-gray-400 text-xs">Requires approval before sending</label>
                </div>
                <button type="submit" class="w-full bg-brand hover:bg-brand-deep text-brand-text text-sm rounded-lg py-2 transition">
                    Save Template
                </button>
            </form>
        </div>
    </div>

</x-app-layout>

<x-app-layout title="Accept Memory Access">

    <div class="max-w-lg mx-auto mt-8">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">

            <div class="px-6 py-5 border-b border-gray-800">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style="background:rgba(var(--accent-rgb),0.15)">
                    <svg class="w-5 h-5" class="ac-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h1 class="text-white text-base font-bold">Memory Access Invitation</h1>
                <p class="text-gray-500 text-xs mt-1">
                    <strong class="text-gray-300">{{ $grant->owner_name }}</strong> has invited you to collaborate on their
                    <strong class="text-gray-300">{{ $grant->deployment_name }}</strong> memory.
                </p>
            </div>

            <div class="px-6 py-5 border-b border-gray-800">
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-widest mb-3">What you can do</p>
                @php $perms = json_decode($grant->permissions, true); @endphp
                <div class="space-y-2">
                    @foreach([
                        ['view',   'View memory records', 'Read clients, contacts, and assets'],
                        ['copy',   'Copy records', 'Duplicate records into your own workspace'],
                        ['upload', 'Upload records', 'Add new records to their memory'],
                        ['modify', 'Modify records', 'Edit existing records in their memory'],
                    ] as [$val, $label, $desc])
                    @if(in_array($val, $perms))
                    <div class="flex items-start gap-3">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 shrink-0 mt-1.5"></span>
                        <div>
                            <p class="text-white text-xs font-semibold">{{ $label }}</p>
                            <p class="text-gray-500 text-xs">{{ $desc }}</p>
                        </div>
                    </div>
                    @endif
                    @endforeach
                    <div class="flex items-start gap-3 opacity-40">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0 mt-1.5"></span>
                        <div>
                            <p class="text-white text-xs font-semibold">Delete — never available</p>
                            <p class="text-gray-500 text-xs">You cannot delete any of their records</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5 flex flex-col sm:flex-row gap-3">
                <form method="POST" action="{{ route('memory.access.accept.store', $token) }}" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full py-2.5 rounded-xl text-sm font-bold transition"
                        class="ac-on">
                        Accept Invitation
                    </button>
                </form>
                <a href="{{ route('dashboard') }}"
                   class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-center border border-gray-700 text-gray-400 hover:text-white transition">
                    Decline
                </a>
            </div>

        </div>
    </div>

</x-app-layout>

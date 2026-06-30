<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Get started — UNIT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-gray-950">

<div class="min-h-screen flex flex-col">

    {{-- Top bar --}}
    <div class="flex items-center justify-between px-8 py-5 border-b border-gray-800">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-yellow-400 rounded-lg flex items-center justify-center">
                <div class="w-3.5 h-3.5 bg-gray-950 rounded-sm"></div>
            </div>
            <span class="text-white font-black text-lg tracking-tight">UNIT</span>
        </div>

        {{-- Progress steps --}}
        @php
        $stepLabels = [1 => 'Worker', 2 => 'Credentials', 3 => 'Memory', 4 => 'Rules', 5 => 'Test'];
        @endphp
        <div class="hidden sm:flex items-center gap-1">
            @foreach($stepLabels as $s => $label)
                @php
                    $isActive   = $s === $step;
                    $isComplete = $s < $step;
                @endphp
                <div class="flex items-center gap-1">
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                        {{ $isComplete ? 'bg-yellow-400/20 text-yellow-400' : ($isActive ? 'bg-gray-700 text-white' : 'text-gray-700') }}">
                        @if($isComplete)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-xs
                                {{ $isActive ? 'bg-yellow-400 text-gray-950' : 'bg-gray-800 text-gray-600' }}">{{ $s }}</span>
                        @endif
                        {{ $label }}
                    </div>
                    @if($s < 5)
                        <div class="w-4 h-px {{ $s < $step ? 'bg-yellow-400/40' : 'bg-gray-800' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
        {{-- Mobile: just step count --}}
        <div class="sm:hidden text-gray-600 text-sm">Step {{ $step }} of 5</div>

        <a href="{{ route('onboarding.skip') }}"
           onclick="return confirm('Skip setup? You can always complete it from your dashboard.')"
           class="hidden sm:block text-gray-700 hover:text-gray-500 text-xs transition-colors whitespace-nowrap">
            Skip setup →
        </a>
    </div>


{{-- Content --}}
    <div class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-lg">
            @if(session('success'))
                <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            {{ $slot }}
        </div>
    </div>

</div>

</body>
</html>

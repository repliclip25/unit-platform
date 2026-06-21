@props(['step' => 1])

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
    <div class="flex items-center justify-between px-8 py-5 border-b border-gray-800 shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 bg-yellow-400 rounded-lg flex items-center justify-center">
                <div class="w-3 h-3 bg-gray-950 rounded-sm"></div>
            </div>
            <span class="text-white font-black text-base tracking-tight">UNIT</span>
        </div>

        {{-- Progress steps --}}
        <div class="hidden sm:flex items-center gap-1.5">
            @foreach([1,2,3,4,5] as $s)
                @php
                    $isActive   = $s === $step;
                    $isComplete = $s < $step;
                @endphp
                <div class="flex items-center gap-1.5">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                        {{ $isComplete ? 'bg-yellow-400 text-gray-950' : ($isActive ? 'bg-gray-900 border-2 border-yellow-400 text-yellow-400' : 'bg-gray-900 border border-gray-700 text-gray-600') }}">
                        @if($isComplete)
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $s }}
                        @endif
                    </div>
                    @if($s < 5)
                        <div class="w-6 h-px {{ $s < $step ? 'bg-yellow-400' : 'bg-gray-800' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="text-gray-600 text-sm">Step {{ $step }} of 5</div>
    </div>

    {{-- Content --}}
    <div class="flex-1 flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-lg">

            @if(session('success'))
                <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
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

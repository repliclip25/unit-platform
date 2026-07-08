<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center w-full px-5 py-3 rounded-xl font-bold text-sm transition hover:opacity-90 active:scale-[0.98]']) }}
        class="ac-on">
    {{ $slot }}
</button>

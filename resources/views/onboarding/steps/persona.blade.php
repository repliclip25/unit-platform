<x-onboarding-layout :stepName="$stepName" :sequence="$sequence" :stepIndex="$stepIndex">

@php
    $personas = [
        'it_agency' => [
            'label'    => 'IT / Digital Agency',
            'tagline'  => 'Domains, SSL, hosting, SaaS subscriptions',
            'detail'   => 'You manage tech renewals for clients. A lapsed domain or expired SSL cert costs them visibility and costs you credibility.',
            'examples' => ['Domain renewals', 'SSL certificates', 'Hosting plans', 'SaaS subscriptions'],
            'icon'     => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        ],
        'insurance_broker' => [
            'label'    => 'Insurance Broker',
            'tagline'  => 'P&C, commercial, liability, auto policies',
            'detail'   => 'A missed renewal means an uninsured client. You track dozens of policies across carriers — AVA makes sure nothing slips.',
            'examples' => ['Commercial auto', 'General liability', 'Property & casualty', 'Workers\' comp'],
            'icon'     => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
        ],
        'compliance' => [
            'label'    => 'Compliance / Licensing',
            'tagline'  => 'Business licenses, permits, certifications',
            'detail'   => 'A lapsed license can shut operations down instantly. You keep clients legal — AVA keeps track so you don\'t have to chase every expiry manually.',
            'examples' => ['Business licenses', 'Professional certifications', 'Operating permits', 'Trade registrations'],
            'icon'     => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
        ],
        'other' => [
            'label'    => 'Other',
            'tagline'  => 'Anything where a missed renewal is a disaster',
            'detail'   => 'You track something else — contracts, memberships, subscriptions, warranties. If a lapse causes damage, AVA\'s the right tool.',
            'examples' => ['Service contracts', 'Vendor agreements', 'Memberships', 'Warranties'],
            'icon'     => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>',
        ],
    ];
@endphp

<div class="mb-6">
    <p class="text-xs font-semibold uppercase tracking-widest mb-3" style="color:var(--accent-text)">Step 2 of 4 — Your use case</p>
    <h1 class="text-2xl font-black text-white mb-2">What kind of renewals do you manage?</h1>
    <p class="text-gray-400 text-sm leading-relaxed">
        AVA works across industries — but she's most useful when she knows your world. Pick the one that fits you best. This shapes what she looks for and how she communicates with your clients.
    </p>
</div>

@if($errors->any())
<div class="mb-4 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3">
    <p class="text-red-400 text-sm">{{ $errors->first() }}</p>
</div>
@endif

<form method="POST" action="{{ route('onboarding.step.handle', 'persona') }}" x-data="{ selected: '{{ $current ?? '' }}' }">
    @csrf

    <div class="space-y-3 mb-6">
        @foreach($personas as $key => $p)
        <label class="block cursor-pointer">
            <input type="radio" name="persona" value="{{ $key }}" class="sr-only" x-model="selected">
            <div class="rounded-xl border px-5 py-4 transition-all duration-150"
                 :class="selected === '{{ $key }}'
                     ? 'border-yellow-400/60 bg-yellow-400/5'
                     : 'border-gray-800 bg-gray-900 hover:border-gray-700'">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5 transition-colors"
                         :class="selected === '{{ $key }}' ? 'bg-yellow-400/15' : 'bg-gray-800'"
                         style="{{ $current === $key ? 'background:rgba(var(--accent-rgb),0.12)' : '' }}">
                        <span :class="selected === '{{ $key }}' ? 'text-yellow-400' : 'text-gray-500'"
                              style="{{ $current === $key ? 'color:var(--accent-text)' : '' }}">
                            {!! $p['icon'] !!}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <p class="text-white font-bold text-sm"
                               :class="selected === '{{ $key }}' ? 'text-white' : 'text-gray-300'">
                                {{ $p['label'] }}
                            </p>
                            <div x-show="selected === '{{ $key }}'" x-cloak
                                 class="w-4 h-4 rounded-full flex items-center justify-center shrink-0"
                                 style="background:var(--accent)">
                                <svg class="w-2.5 h-2.5" viewBox="0 0 12 12" fill="#111">
                                    <path d="M2 6l3 3 5-5" stroke="#111" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-gray-500 text-xs mb-2">{{ $p['tagline'] }}</p>
                        <p x-show="selected === '{{ $key }}'" x-cloak
                           class="text-gray-400 text-xs leading-relaxed mb-2">{{ $p['detail'] }}</p>
                        <div x-show="selected === '{{ $key }}'" x-cloak class="flex flex-wrap gap-1.5">
                            @foreach($p['examples'] as $ex)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-800 text-gray-500">{{ $ex }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </label>
        @endforeach
    </div>

    <button type="submit"
            class="w-full font-bold text-base py-4 rounded-xl transition-all"
            style="background:var(--accent);color:#1a1404"
            :class="selected ? 'opacity-100' : 'opacity-50 cursor-not-allowed'"
            :disabled="!selected">
        Continue →
    </button>

    <p class="text-center text-xs text-gray-600 mt-3">
        You can change this later from your worker settings.
    </p>
</form>

</x-onboarding-layout>

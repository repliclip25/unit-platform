<?php

namespace App\Support;

class PublicNav
{
    /**
     * The one canonical set of top-level nav links for every public page.
     * Real routes only, no same-page anchors, so the destination is valid
     * no matter which page renders it.
     */
    public static function links(): array
    {
        return [
            ['label' => 'Meet the AI Worker', 'href' => route('public.workers.index'), 'active' => request()->routeIs('public.workers.*')],
            ['label' => 'Pricing',            'href' => route('pricing'),              'active' => request()->routeIs('pricing')],
            ['label' => 'Blog',               'href' => route('blog'),                 'active' => request()->routeIs('blog*')],
            ['label' => 'Company',            'href' => route('about'),                'active' => request()->routeIs('about')],
        ];
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PresaleWorkerController extends Controller
{
    /**
     * Presale-stage workers — validated business use cases not yet built.
     * Each entry drives a landing page at /presale/{slug} used to collect
     * early signups before the worker exists. Once a worker actually ships,
     * this entry retires in favor of its real WorkerPublicController page.
     */
    private static array $workers = [
        'brand-video' => [
            'name'       => 'Brand Video',
            'slug'       => 'brand-video',
            'role'       => 'AI Brand Video Agent',
            'category'   => 'Brand Content Creation',
            'meta_desc'  => "Brand Video is UNIT's upcoming AI agent for turning brand assets into finished video content. Reserve early access and start training its memory today.",
            'tagline'    => "Owns your brand's video content from raw material to finished cut.",

            'connects_to' => [
                ['icon' => 'drive', 'label' => 'Google Drive', 'desc' => 'Where your brand assets and finished videos live — in your own account, not ours.'],
            ],

            'produces' => [
                ['icon' => 'film',     'label' => 'Finished video files', 'desc' => 'Delivered straight into your Drive, organized by folder.'],
                ['icon' => 'sparkles', 'label' => 'On-brand cuts',        'desc' => 'Built from your logo, colors, and voice — not generic templates.'],
            ],

            'memory_requirements' => [
                ['icon' => 'user',   'label' => 'Business Profile', 'desc' => 'Business name, tagline, brand voice, and colors.'],
                ['icon' => 'folder', 'label' => 'Brand Assets',     'desc' => 'Logos, raw footage, and images, organized into folders by type.'],
            ],

            'bullets' => [
                'Learns your brand from logos, colors, and voice',
                'Organizes raw footage and images by type',
                'Turns raw material into finished video',
                'Keeps every video on-brand automatically',
            ],
        ],
    ];

    public function show(string $slug): View
    {
        $worker = self::$workers[$slug] ?? null;
        abort_unless($worker, 404);

        return view('presale.worker-landing', compact('worker'));
    }
}

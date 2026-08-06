<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MemoryHubController extends Controller
{
    /**
     * Memory classes a tenant can start training before deploying anything.
     * The strategy: training memory is low-commitment (no Gmail connection,
     * no billing) — this is the platform's easy route to collect real user
     * investment ahead of the harder "deploy a worker" decision. Add an entry
     * here whenever a worker (built or presale-stage) has its own memory bundle.
     */
    private const CLASSES = [
        'ava' => [
            'name'  => 'AVA Memory',
            'role'  => 'Renewal Coordinator',
            'desc'  => 'Clients, contacts, and assets AVA reads when it drafts renewals — start now, connect Gmail whenever you\'re ready.',
            'icon'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'badge' => 'Live worker',
        ],
        'brand-video' => [
            'name'  => 'Brand Memory',
            'role'  => 'Brand Video Agent',
            'desc'  => 'Business profile and Drive-hosted assets the brand-video worker will use once it launches.',
            'icon'  => 'M15 10l4.55-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.45.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
            'badge' => 'Early access',
        ],
    ];

    public function show(): View
    {
        return view('hire.memory-hub', ['classes' => self::CLASSES]);
    }

    /**
     * Routes a tenant into a memory class, converting them into that class's
     * track on first click. AVA needs no conversion (clients/contacts/assets
     * are already platform-scoped, not deployment-scoped). Brand Memory flags
     * the account as presale on first click — the click itself is the
     * "commitment" this page exists to collect.
     */
    public function start(string $slug): RedirectResponse
    {
        abort_unless(isset(self::CLASSES[$slug]), 404);

        if ($slug === 'ava') {
            return redirect()->route('hire.ava.memory');
        }

        $user = auth()->user();
        if (!$user->presale_worker) {
            $user->update([
                'presale_worker'          => $slug,
                'onboarding_completed_at' => $user->onboarding_completed_at ?? now(),
            ]);
            PresaleController::seedDefaultCategories($user->id);
        }

        return redirect()->route('presale.memory');
    }
}

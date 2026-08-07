<?php

namespace App\Http\Controllers;

use App\Platform\Services\Memory\MemoryClassRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MemoryHubController extends Controller
{
    public function show(): View
    {
        $userId = auth()->id();
        $classes = MemoryClassRegistry::all();

        foreach ($classes as $slug => &$class) {
            $class['coverage'] = MemoryClassRegistry::score($slug, $userId);
        }
        unset($class);

        return view('hire.memory-hub', ['classes' => $classes]);
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
        abort_unless(MemoryClassRegistry::get($slug), 404);

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

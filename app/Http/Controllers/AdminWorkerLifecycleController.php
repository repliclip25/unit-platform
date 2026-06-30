<?php

namespace App\Http\Controllers;

use App\Platform\Services\WorkerRegistry;
use Illuminate\Http\Request;

class AdminWorkerLifecycleController extends Controller
{
    public function commission(Request $request, string $slug)
    {
        $this->guardSlug($slug);
        WorkerRegistry::commission($slug);
        return back()->with('success', "Worker '{$slug}' is now active.");
    }

    public function setTesting(Request $request, string $slug)
    {
        $this->guardSlug($slug);
        WorkerRegistry::setTesting($slug);
        return back()->with('success', "Worker '{$slug}' set to testing mode. Transactions tagged is_test=true, not billed.");
    }

    public function decommission(Request $request, string $slug)
    {
        $this->guardSlug($slug);
        WorkerRegistry::decommission($slug);
        return back()->with('success', "Worker '{$slug}' decommissioned. All active deployments stopped. Historical data preserved.");
    }

    public function remove(Request $request, string $slug)
    {
        $this->guardSlug($slug);

        $request->validate([
            'confirm_name' => ['required', 'string', function ($attr, $value, $fail) use ($slug) {
                if (strtolower(trim($value)) !== strtolower($slug)) {
                    $fail("Name does not match. Type '{$slug}' to confirm.");
                }
            }],
        ]);

        WorkerRegistry::remove($slug, auth()->id());

        return back()->with('success', "Worker '{$slug}' removal started. All tenant data is being soft-deleted in the background.");
    }

    private function guardSlug(string $slug): void
    {
        abort_unless(in_array($slug, WorkerRegistry::slugs()), 404, 'Unknown worker slug.');
    }
}

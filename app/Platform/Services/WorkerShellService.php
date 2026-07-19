<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;

/**
 * WorkerShellService — builds the data needed to render the shared UNIT app
 * shell (top bar + worker sidebar) used by every authenticated new-UI page:
 * /desk/{slug}, /workers/{slug}/overview, and future pages built the same way.
 */
class WorkerShellService
{
    // stage_key => [output column, business-facing label, color] — shared across
    // any page that renders per-transaction pipeline data (desk, worker overview)
    public const STAGE_META = [
        'read'     => ['col' => 'read_output',     'label' => 'Reviewed',  'color' => '#6366f1'],
        'classify' => ['col' => 'classify_output', 'label' => 'Assessed',  'color' => '#f59e0b'],
        'memory'   => ['col' => 'memory_output',   'label' => 'Verified',  'color' => '#8b5cf6'],
        'draft'    => ['col' => 'draft_output',    'label' => 'Prepared',  'color' => '#f97316'],
        'push'     => ['col' => null,              'label' => 'Delivered','color' => '#06b6d4'],
    ];

    // Full UNIT worker catalog — keep in sync with the real roster
    private const CATALOG_META = [
        'ava' => ['name' => 'AVA', 'role' => 'Renewal Specialist'],
        'dox' => ['name' => 'DOX', 'role' => 'Document Specialist'],
        'mox' => ['name' => 'MOX', 'role' => 'Brand Moments Hunter'],
        'nux' => ['name' => 'NUX', 'role' => 'Publishing Specialist'],
    ];

    public static function build(int $userId, string $activeSlug): array
    {
        $deployedBySlug = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'paused'])
            ->orderBy('created_at')
            ->get()
            ->unique('worker_slug')
            ->keyBy('worker_slug');

        $catalogSlugs = array_keys(self::CATALOG_META);
        $registryRows = DB::table('worker_registry')->whereIn('slug', $catalogSlugs)->get()->keyBy('slug');

        $activeRegistryRow = $registryRows->get($activeSlug);
        $profileImg = $activeRegistryRow?->profile_image ? asset('storage/' . $activeRegistryRow->profile_image) : null;
        $coverImg   = $activeRegistryRow?->cover_image   ? asset('storage/' . $activeRegistryRow->cover_image)   : null;

        $workerCatalog = collect($catalogSlugs)->map(function ($slug) use ($deployedBySlug, $registryRows, $activeSlug, $profileImg) {
            $wDep = $deployedBySlug->get($slug);
            $wReg = $registryRows->get($slug);
            return (object) [
                'slug'   => $slug,
                'name'   => self::CATALOG_META[$slug]['name'],
                'role'   => self::CATALOG_META[$slug]['role'],
                'active' => (bool) $wDep,
                'status' => $wDep->status ?? null,
                'image'  => $slug === $activeSlug && $profileImg
                    ? $profileImg
                    : ($wReg?->profile_image ? asset('storage/' . $wReg->profile_image) : asset('images/' . $slug . '.png')),
            ];
        });

        $tokenTotal = rescue(
            fn() => (int) DB::table('usage_events')->where('user_id', $userId)->sum(DB::raw('tokens_input + tokens_output')),
            0,
            false
        );

        return [
            'workerCatalog'       => $workerCatalog,
            'registryRows'        => $registryRows,
            'registryRow'         => $activeRegistryRow,
            'profileImg'          => $profileImg,
            'coverImg'            => $coverImg,
            'tokenTotal'          => $tokenTotal,
            'activeDeploymentId'  => $deployedBySlug->get($activeSlug)?->id,
        ];
    }
}

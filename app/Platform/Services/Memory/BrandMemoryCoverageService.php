<?php

namespace App\Platform\Services\Memory;

use Illuminate\Support\Facades\DB;

/**
 * Brand Memory's own formula — doesn't fit the clients/contacts/assets shape
 * MemoryCoverageCalculator scores, since brand material (profile, Drive
 * folders, uploaded assets) is a different kind of memory entirely. Same
 * output shape as the other memory classes (score/complete/total/needed/
 * healthy) so the UI can render one progress bar regardless of which memory
 * class it's for, plus a `checklist` breakdown for step-by-step display.
 */
class BrandMemoryCoverageService
{
    public static function score(int $userId): array
    {
        $hasProfile = DB::table('brand_profiles')
            ->where('user_id', $userId)
            ->whereNotNull('business_name')
            ->where('business_name', '!=', '')
            ->exists();

        $hasDrive = DB::table('user_drive_credentials')->where('user_id', $userId)->exists();

        $hasFolder = DB::table('brand_memory_categories')
            ->where('user_id', $userId)
            ->whereNotNull('drive_folder_id')
            ->exists();

        $hasAsset = DB::table('brand_assets')->where('user_id', $userId)->exists();

        $checklist = [
            'profile' => $hasProfile,
            'drive'   => $hasDrive,
            'folders' => $hasFolder,
            'assets'  => $hasAsset,
        ];

        $total    = count($checklist);
        $complete = count(array_filter($checklist));
        $score    = (int) round(($complete / $total) * 100);

        return [
            'score'     => $score,
            'complete'  => $complete,
            'total'     => $total,
            'needed'    => $total - $complete,
            'healthy'   => $score >= 100,
            'checklist' => $checklist,
        ];
    }
}

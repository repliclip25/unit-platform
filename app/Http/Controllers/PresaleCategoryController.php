<?php

namespace App\Http\Controllers;

use App\Platform\Services\GoogleDrive\DriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresaleCategoryController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->isPresale(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $exists = DB::table('brand_memory_categories')
            ->where('user_id', $user->id)
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            return redirect()->route('presale.memory')->with('error', "You already have a \"{$data['name']}\" category.");
        }

        $nextOrder = (int) DB::table('brand_memory_categories')->where('user_id', $user->id)->max('sort_order') + 1;

        $categoryId = DB::table('brand_memory_categories')->insertGetId([
            'user_id'    => $user->id,
            'name'       => $data['name'],
            'sort_order' => $nextOrder,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // If Drive is already connected, create the matching subfolder right away
        // rather than waiting for the next OAuth round-trip.
        $credential = DB::table('user_drive_credentials')->where('user_id', $user->id)->first();
        if ($credential && $credential->root_folder_id) {
            $drive  = app(DriveService::class, ['credential' => $credential]);
            $folder = $drive->createSubfolder($credential->root_folder_id, $data['name']);

            DB::table('brand_memory_categories')->where('id', $categoryId)->update([
                'drive_folder_id'  => $folder['id'],
                'drive_folder_url' => $folder['url'],
                'updated_at'       => now(),
            ]);
        }

        return redirect()->route('presale.memory')->with('success', "Added \"{$data['name']}\" category.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->isPresale(), 404);

        DB::table('brand_memory_categories')->where('id', $id)->where('user_id', $user->id)->delete();

        return redirect()->route('presale.memory')->with('success', 'Category removed. Any files already in Drive are untouched.');
    }
}

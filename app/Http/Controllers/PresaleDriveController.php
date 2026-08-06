<?php

namespace App\Http\Controllers;

use App\Platform\Services\GoogleDrive\DriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PresaleDriveController extends Controller
{
    public function authorize()
    {
        $query = http_build_query([
            'client_id'     => config('services.google_drive.client_id'),
            'redirect_uri'  => config('services.google_drive.redirect_uri'),
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/drive.file openid email',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');
        if (!$code) {
            return redirect()->route('presale.memory')->with('error', 'Google did not return an authorization code. Please try again.');
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => config('services.google_drive.client_id'),
            'client_secret' => config('services.google_drive.client_secret'),
            'redirect_uri'  => config('services.google_drive.redirect_uri'),
            'grant_type'    => 'authorization_code',
        ]);

        if ($response->failed()) {
            return redirect()->route('presale.memory')->with('error', 'Google Drive connection failed: ' . $response->body());
        }

        $refreshToken = Crypt::encryptString($response->json('refresh_token'));
        $accessToken  = $response->json('access_token');
        $scope        = $response->json('scope');

        $userInfo = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');
        $email    = $userInfo->successful() ? ($userInfo->json('email') ?? null) : null;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('presale.memory')->with('error', 'Could not retrieve a valid Google account email. Please try again.');
        }

        $user = auth()->user();

        DB::table('user_drive_credentials')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'drive_email'   => $email,
                'refresh_token' => $refreshToken,
                'scope'         => $scope,
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        $credential = DB::table('user_drive_credentials')->where('user_id', $user->id)->first();
        $drive      = app(DriveService::class, ['credential' => $credential]);
        $rootFolder = $drive->ensureFolder($user->name);

        $this->backfillCategoryFolders($drive, $user->id, $rootFolder['id']);

        return redirect()->route('presale.memory')->with('success', "Google Drive connected: {$email}");
    }

    public function upload(Request $request)
    {
        $request->validate([
            'asset'       => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm,pdf,ppt,pptx,key', 'max:204800'], // 200MB
            'category_id' => ['required', 'integer'],
        ]);

        $user       = auth()->user();
        $credential = DB::table('user_drive_credentials')->where('user_id', $user->id)->first();

        if (!$credential) {
            return redirect()->route('presale.memory')->with('error', 'Connect Google Drive before uploading assets.');
        }

        $category = DB::table('brand_memory_categories')
            ->where('id', $request->input('category_id'))
            ->where('user_id', $user->id)
            ->first();

        if (!$category) {
            return redirect()->route('presale.memory')->with('error', 'Choose a valid category before uploading.');
        }

        $drive = app(DriveService::class, ['credential' => $credential]);
        $file  = $request->file('asset');

        $quota = $drive->getStorageQuota();
        if ($quota['available'] !== null && $file->getSize() > $quota['available']) {
            $availableMb = number_format($quota['available'] / 1048576, 1);
            return redirect()->route('presale.memory')->with('error', "Not enough space in your Google Drive (only {$availableMb} MB free). Free up space or use a different Drive account.");
        }

        $folderId = $category->drive_folder_id;
        if (!$folderId) {
            $root     = $drive->ensureFolder($user->name);
            $folder   = $drive->createSubfolder($root['id'], $category->name);
            $folderId = $folder['id'];

            DB::table('brand_memory_categories')->where('id', $category->id)->update([
                'drive_folder_id'  => $folder['id'],
                'drive_folder_url' => $folder['url'],
                'updated_at'       => now(),
            ]);
        }

        $businessName = DB::table('brand_profiles')->where('user_id', $user->id)->value('business_name') ?? $user->name;

        $result = $drive->uploadFile($file, $folderId, [
            'description' => "Uploaded via UNIT Brand Memory for {$businessName} — category: {$category->name}",
            'properties'  => [
                'unit_uploaded_via' => 'brand-memory-presale',
                'unit_user_id'      => (string) $user->id,
                'unit_category'     => $category->name,
            ],
        ]);

        DB::table('brand_assets')->insert([
            'user_id'       => $user->id,
            'category_id'   => $category->id,
            'drive_file_id' => $result['id'],
            'name'          => $result['name'],
            'mime_type'     => $result['mimeType'] ?? $file->getMimeType(),
            'kind'          => str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image',
            'web_view_link' => $result['webViewLink'] ?? null,
            'uploaded_at'   => now(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('presale.memory')->with('success', "Uploaded {$result['name']} to {$category->name}.");
    }

    /**
     * Any category created before Drive was connected (or before this feature
     * shipped) won't have a Drive folder yet — create the missing ones now.
     */
    private function backfillCategoryFolders(DriveService $drive, int $userId, string $rootFolderId): void
    {
        $missing = DB::table('brand_memory_categories')
            ->where('user_id', $userId)
            ->whereNull('drive_folder_id')
            ->get();

        foreach ($missing as $category) {
            $folder = $drive->createSubfolder($rootFolderId, $category->name);

            DB::table('brand_memory_categories')->where('id', $category->id)->update([
                'drive_folder_id'  => $folder['id'],
                'drive_folder_url' => $folder['url'],
                'updated_at'       => now(),
            ]);
        }
    }
}

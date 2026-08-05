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
            return redirect()->route('presale.dashboard')->with('error', 'Google did not return an authorization code. Please try again.');
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => config('services.google_drive.client_id'),
            'client_secret' => config('services.google_drive.client_secret'),
            'redirect_uri'  => config('services.google_drive.redirect_uri'),
            'grant_type'    => 'authorization_code',
        ]);

        if ($response->failed()) {
            return redirect()->route('presale.dashboard')->with('error', 'Google Drive connection failed: ' . $response->body());
        }

        $refreshToken = Crypt::encryptString($response->json('refresh_token'));
        $accessToken  = $response->json('access_token');
        $scope        = $response->json('scope');

        $userInfo = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');
        $email    = $userInfo->successful() ? ($userInfo->json('email') ?? null) : null;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('presale.dashboard')->with('error', 'Could not retrieve a valid Google account email. Please try again.');
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
        app(DriveService::class, ['credential' => $credential])->ensureFolder($user->name);

        return redirect()->route('presale.dashboard')->with('success', "Google Drive connected: {$email}");
    }

    public function upload(Request $request)
    {
        $request->validate([
            'asset' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm', 'max:204800'], // 200MB
        ]);

        $user       = auth()->user();
        $credential = DB::table('user_drive_credentials')->where('user_id', $user->id)->first();

        if (!$credential) {
            return redirect()->route('presale.dashboard')->with('error', 'Connect Google Drive before uploading assets.');
        }

        $file   = $request->file('asset');
        $drive  = app(DriveService::class, ['credential' => $credential]);
        $folder = $drive->ensureFolder($user->name);
        $result = $drive->uploadFile($file, $folder['id']);

        DB::table('brand_assets')->insert([
            'user_id'       => $user->id,
            'drive_file_id' => $result['id'],
            'name'          => $result['name'],
            'mime_type'     => $result['mimeType'] ?? $file->getMimeType(),
            'kind'          => str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image',
            'web_view_link' => $result['webViewLink'] ?? null,
            'uploaded_at'   => now(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('presale.dashboard')->with('success', "Uploaded {$result['name']} to your Drive.");
    }
}

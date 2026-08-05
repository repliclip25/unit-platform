<?php

namespace App\Platform\Services\GoogleDrive;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DriveService
{
    private object $credential;
    private string $accessToken;

    public function __construct(object $credential)
    {
        $this->credential = $credential;

        $refreshToken = Crypt::decryptString($credential->refresh_token);
        $this->accessToken = $this->getAccessToken($refreshToken);

        DB::table('user_drive_credentials')
            ->where('id', $credential->id)
            ->update(['token_last_used_at' => now(), 'updated_at' => now()]);
    }

    /**
     * Ensures a per-tenant brand folder exists in the customer's Drive, creating
     * it on first use. Persists the folder id/url back onto the credential row.
     */
    public function ensureFolder(string $ownerName): array
    {
        if ($this->credential->root_folder_id) {
            return [
                'id'  => $this->credential->root_folder_id,
                'url' => $this->credential->root_folder_url,
            ];
        }

        $response = Http::withToken($this->accessToken)
            ->post('https://www.googleapis.com/drive/v3/files', [
                'name'     => "UNIT Brand Memory - {$ownerName}",
                'mimeType' => 'application/vnd.google-apps.folder',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Drive folder creation failed: ' . $response->body());
        }

        $folderId  = $response->json('id');
        $folderUrl = "https://drive.google.com/drive/folders/{$folderId}";

        DB::table('user_drive_credentials')
            ->where('id', $this->credential->id)
            ->update([
                'root_folder_id'  => $folderId,
                'root_folder_url' => $folderUrl,
                'updated_at'      => now(),
            ]);

        return ['id' => $folderId, 'url' => $folderUrl];
    }

    /**
     * Uploads a file straight into the given Drive folder. Never touches our own
     * disk beyond PHP's transient upload temp file.
     *
     * Drive's multipart upload wants a raw `multipart/related` body (metadata part
     * + media part) — this is not the same thing as an HTML `multipart/form-data`
     * upload, so it's built by hand rather than via Http::attach().
     */
    public function uploadFile(UploadedFile $file, string $folderId): array
    {
        $boundary = 'unit-' . bin2hex(random_bytes(16));

        $metadata = json_encode([
            'name'    => $file->getClientOriginalName(),
            'parents' => [$folderId],
        ]);

        $body = "--{$boundary}\r\n"
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . $metadata . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: {$file->getMimeType()}\r\n\r\n"
            . file_get_contents($file->getRealPath()) . "\r\n"
            . "--{$boundary}--";

        $response = Http::withToken($this->accessToken)
            ->withBody($body, "multipart/related; boundary={$boundary}")
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,mimeType,webViewLink');

        if ($response->failed()) {
            throw new \RuntimeException('Drive upload failed: ' . $response->body());
        }

        return $response->json();
    }

    private function getAccessToken(string $refreshToken): string
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id'     => config('services.google_drive.client_id'),
            'client_secret' => config('services.google_drive.client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Drive token refresh failed: ' . $response->body());
        }

        return $response->json('access_token');
    }
}

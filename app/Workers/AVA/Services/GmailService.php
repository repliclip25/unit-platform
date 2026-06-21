<?php

namespace App\Workers\AVA\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GmailService
{
    private string $accessToken;
    private string $fromAddress;

    public function __construct(?object $credential = null)
    {
        // Credential from DB takes priority over .env fallback
        $refreshToken      = $credential?->refresh_token ?? config('services.gmail.refresh_token');
        $this->fromAddress = $credential?->gmail_address ?? config('services.gmail.address');
        $this->accessToken = $this->getAccessToken($refreshToken);
    }

    public function sendEmail(string $to, string $subject, string $body): string
    {
        $raw = $this->buildRawMessage($to, $subject, $body);

        $response = Http::withToken($this->accessToken)
            ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                'raw' => $raw,
            ]);

        if ($response->failed()) {
            Log::error('Gmail send failed', ['body' => $response->body()]);
            throw new \RuntimeException('Gmail send failed: ' . $response->body());
        }

        return $response->json('id');
    }

    public function createDraft(string $to, string $subject, string $body): string
    {
        $raw = $this->buildRawMessage($to, $subject, $body);

        $response = Http::withToken($this->accessToken)
            ->post('https://gmail.googleapis.com/gmail/v1/users/me/drafts', [
                'message' => ['raw' => $raw],
            ]);

        if ($response->failed()) {
            Log::error('Gmail draft creation failed', ['body' => $response->body()]);
            throw new \RuntimeException('Gmail draft failed: ' . $response->body());
        }

        return $response->json('id');
    }

    public function sendDraft(string $draftId): string
    {
        $response = Http::withToken($this->accessToken)
            ->post("https://gmail.googleapis.com/gmail/v1/users/me/drafts/send", [
                'id' => $draftId,
            ]);

        if ($response->failed()) {
            Log::error('Gmail send draft failed', ['draft_id' => $draftId, 'body' => $response->body()]);
            throw new \RuntimeException('Gmail send draft failed: ' . $response->body());
        }

        return $response->json('id');
    }

    public function deleteDraft(string $draftId): void
    {
        $response = Http::withToken($this->accessToken)
            ->delete("https://gmail.googleapis.com/gmail/v1/users/me/drafts/{$draftId}");

        if ($response->failed() && $response->status() !== 404) {
            Log::warning('Gmail delete draft failed', ['draft_id' => $draftId, 'body' => $response->body()]);
        }
    }

    /**
     * Insert a raw RFC 2822 message directly into the inbox (no external send).
     * Used by Fast Track to simulate an inbound email without contacting real senders.
     * Returns the Gmail message ID of the inserted message.
     */
    public function insertIntoInbox(string $from, string $to, string $subject, string $body): string
    {
        $raw = $this->buildRawMessageFrom($from, $to, $subject, $body);

        $response = Http::withToken($this->accessToken)
            ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages?internalDateSource=dateHeader', [
                'raw'      => $raw,
                'labelIds' => ['INBOX'],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gmail insert failed: ' . $response->body());
        }

        return $response->json('id');
    }

    /**
     * Fetch a message from Gmail as a decoded RFC 2822 string.
     * This is what the production webhook does after receiving a Pub/Sub notification.
     */
    public function fetchRawMessage(string $messageId): string
    {
        $response = Http::withToken($this->accessToken)
            ->get("https://gmail.googleapis.com/gmail/v1/users/me/messages/{$messageId}", [
                'format' => 'raw',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gmail fetch failed: ' . $response->body());
        }

        $raw = $response->json('raw');
        return base64_decode(strtr($raw, '-_', '+/'));
    }

    private function buildRawMessage(string $to, string $subject, string $body): string
    {
        return $this->buildRawMessageFrom($this->fromAddress, $to, $subject, $body);
    }

    private function buildRawMessageFrom(string $from, string $to, string $subject, string $body): string
    {
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $date    = now()->format('D, d M Y H:i:s O');
        $message = "From: {$from}\r\nTo: {$to}\r\nDate: {$date}\r\nSubject: {$encodedSubject}\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n{$body}";
        return rtrim(strtr(base64_encode($message), '+/', '-_'), '=');
    }

    private function getAccessToken(string $refreshToken): string
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id'     => config('services.gmail.client_id'),
            'client_secret' => config('services.gmail.client_secret'),
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gmail token refresh failed: ' . $response->body());
        }

        return $response->json('access_token');
    }
}

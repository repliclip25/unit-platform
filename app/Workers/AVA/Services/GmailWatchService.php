<?php

namespace App\Workers\AVA\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GmailWatchService
{
    private string $accessToken;

    public function __construct(private readonly ?\stdClass $credential = null)
    {
        $refreshToken      = $this->credential?->refresh_token ?? config('services.gmail.refresh_token');
        $this->accessToken = $this->getAccessToken($refreshToken);
    }

    // Register Gmail inbox watch — call once, renews weekly via scheduler
    public function watch(string $topicName): array
    {
        $response = Http::withToken($this->accessToken)
            ->post('https://gmail.googleapis.com/gmail/v1/users/me/watch', [
                'topicName'           => $topicName,
                'labelIds'            => ['INBOX'],
                'labelFilterBehavior' => 'INCLUDE',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gmail watch failed: ' . $response->body());
        }

        $result    = $response->json();
        $historyId = $result['historyId'];

        // Mark watch active and store historyId for this credential
        if ($this->credential) {
            $expiresAt = isset($result['expiration'])
                ? date('Y-m-d H:i:s', (int)($result['expiration'] / 1000))
                : now()->addDays(7)->toDateTimeString();
            DB::table('user_gmail_credentials')
                ->where('id', $this->credential->id)
                ->update(['watch_active' => true, 'watch_expires_at' => $expiresAt, 'updated_at' => now()]);
        }
        $this->saveHistoryId($historyId);

        return $result;
    }

    // Called by webhook — returns ALL new messages since last processed historyId
    // Read/unread status is irrelevant. Deduplication prevents double-processing.
    public function getNewMessages(string $incomingHistoryId): array
    {
        $lastHistoryId = $this->loadHistoryId();

        // Use the earlier of the two IDs to avoid gaps
        $startId = $lastHistoryId
            ? min((int) $lastHistoryId, (int) $incomingHistoryId - 1)
            : (int) $incomingHistoryId - 1;

        $response = Http::withToken($this->accessToken)
            ->get('https://gmail.googleapis.com/gmail/v1/users/me/history', [
                'startHistoryId' => max(1, $startId),
                'historyTypes'   => 'messageAdded',
                'labelId'        => 'INBOX',
            ]);

        if ($response->failed()) {
            Log::error('AVA: Gmail history fetch failed', ['body' => $response->body()]);
            return [];
        }

        // Save the latest historyId so next call picks up from here
        $newHistoryId = $response->json('historyId');
        if ($newHistoryId) {
            $this->saveHistoryId($newHistoryId);
        }

        // Collect all message IDs added across all history records
        $messageIds = [];
        foreach ($response->json('history') ?? [] as $record) {
            foreach ($record['messagesAdded'] ?? [] as $added) {
                $messageIds[] = $added['message']['id'];
            }
        }

        if (empty($messageIds)) {
            return [];
        }

        // Deduplicate — skip any message AVA has already processed
        $alreadyProcessed = DB::table('processed_messages')
            ->whereIn('message_id', $messageIds)
            ->pluck('message_id')
            ->toArray();

        $newIds = array_diff($messageIds, $alreadyProcessed);

        if (empty($newIds)) {
            Log::info('AVA: all messages already processed', ['ids' => $messageIds]);
            return [];
        }

        // Fetch full content for each new message
        $messages = [];
        foreach ($newIds as $messageId) {
            try {
                $messages[] = $this->getMessage($messageId);
            } catch (\Throwable $e) {
                Log::error('AVA: failed to fetch message', ['id' => $messageId, 'error' => $e->getMessage()]);
            }
        }

        return $messages;
    }

    public function getMessage(string $messageId): array
    {
        $response = Http::withToken($this->accessToken)
            ->get("https://gmail.googleapis.com/gmail/v1/users/me/messages/{$messageId}", [
                'format' => 'full',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gmail get message failed: ' . $response->body());
        }

        $data    = $response->json();
        $headers = collect($data['payload']['headers']);

        $subject = $headers->firstWhere('name', 'Subject')['value'] ?? '';
        $from    = $headers->firstWhere('name', 'From')['value'] ?? '';
        $date    = $headers->firstWhere('name', 'Date')['value'] ?? '';
        $body    = $this->extractBody($data['payload']);

        return [
            'message_id' => $messageId,
            'subject'    => $subject,
            'from'       => $from,
            'date'       => $date,
            'raw_email'  => "From: {$from}\nSubject: {$subject}\nDate: {$date}\n\n{$body}",
        ];
    }

    // Mark a message as processed to prevent duplicates
    public function markProcessed(string $messageId, string $txId): void
    {
        DB::table('processed_messages')->insertOrIgnore([
            'message_id' => $messageId,
            'tx_id'      => $txId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function extractBody(array $payload): string
    {
        if (!empty($payload['body']['data'])) {
            return base64_decode(strtr($payload['body']['data'], '-_', '+/'));
        }
        foreach ($payload['parts'] ?? [] as $part) {
            if (in_array($part['mimeType'], ['text/plain', 'text/html'])) {
                if (!empty($part['body']['data'])) {
                    return base64_decode(strtr($part['body']['data'], '-_', '+/'));
                }
            }
        }
        return '';
    }

    private function saveHistoryId(string $historyId): void
    {
        $key = $this->credential ? 'gmail_history_id_' . $this->credential->id : 'gmail_history_id';
        DB::table('ava_state')->upsert(
            ['key' => $key, 'value' => $historyId, 'updated_at' => now(), 'created_at' => now()],
            ['key'],
            ['value', 'updated_at']
        );
    }

    private function loadHistoryId(): ?string
    {
        $key = $this->credential ? 'gmail_history_id_' . $this->credential->id : 'gmail_history_id';
        return DB::table('ava_state')->where('key', $key)->value('value');
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

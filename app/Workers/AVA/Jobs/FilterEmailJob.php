<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FilterEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $tx  = DB::table('transactions')->where('tx_id', $this->txId)->firstOrFail();
        $dep = $tx->deployment_id
            ? DB::table('worker_deployments')->where('id', $tx->deployment_id)->first()
            : null;

        $config  = json_decode($dep?->config ?? '{}', true) ?: [];
        $capture = $config['capture'] ?? [];

        $rawInput = json_decode($tx->raw_input ?? '{}', true) ?: [];
        $from     = strtolower($rawInput['from']    ?? '');
        $subject  = strtolower($rawInput['subject'] ?? '');
        $body     = strtolower($rawInput['raw_email'] ?? '');

        // ── Rule 1: excluded senders — always skip ────────────────────────────
        $excludeSenders = array_filter(array_map('strtolower', $capture['exclude_senders'] ?? []));
        foreach ($excludeSenders as $excluded) {
            if ($excluded && str_contains($from, $excluded)) {
                $this->dropWith($tx, "Sender excluded: {$from} matches '{$excluded}'");
                return;
            }
        }

        // ── Rule 2: allowed domains — if set, from must match one ─────────────
        $allowedDomains = array_filter(array_map('strtolower', $capture['capture_domains'] ?? []));
        if (!empty($allowedDomains)) {
            $domainMatch = false;
            foreach ($allowedDomains as $domain) {
                if ($domain && str_contains($from, $domain)) {
                    $domainMatch = true;
                    break;
                }
            }
            if (!$domainMatch) {
                $this->dropWith($tx, "Sender domain not in allowed list: {$from}");
                return;
            }
        }

        // ── Rule 3: allowed senders — if set, from must match one ────────────
        $allowedSenders = array_filter(array_map('strtolower', $capture['capture_senders_only'] ?? []));
        if (!empty($allowedSenders)) {
            $senderMatch = false;
            foreach ($allowedSenders as $allowed) {
                if ($allowed && str_contains($from, $allowed)) {
                    $senderMatch = true;
                    break;
                }
            }
            if (!$senderMatch) {
                $this->dropWith($tx, "Sender not in allow-list: {$from}");
                return;
            }
        }

        // ── Rule 4: keywords — checked against subject + body ─────────────────
        $keywords   = array_filter(array_map('strtolower', $capture['capture_keywords'] ?? []));
        $requireAll = !empty($capture['capture_require_all']);
        if (!empty($keywords)) {
            $searchText = $subject . ' ' . $body;
            $matches    = array_filter($keywords, fn($kw) => $kw && str_contains($searchText, $kw));

            $passed = $requireAll
                ? count($matches) === count($keywords)
                : count($matches) > 0;

            if (!$passed) {
                $mode = $requireAll ? 'all' : 'any';
                $this->dropWith($tx, "No keyword match ({$mode}): [" . implode(', ', $keywords) . "]");
                return;
            }
        }

        // ── Passed all rules — advance to ReadEmailJob ────────────────────────
        UnitPlatform::setStatus($this->txId, 'queued');
        $queue = $dep ? ($dep->worker_slug ?? 'ava') : 'ava';
        ReadEmailJob::dispatch($this->txId)->onQueue($queue);
    }

    private function dropWith(object $tx, string $reason): void
    {
        DB::table('transactions')->where('tx_id', $this->txId)->update([
            'status'        => 'filtered_out',
            'filter_reason' => $reason,
            'updated_at'    => now(),
        ]);
        Log::info('AVA FilterEmailJob: email filtered out', [
            'tx_id'  => $this->txId,
            'from'   => json_decode($tx->raw_input ?? '{}', true)['from'] ?? '?',
            'reason' => $reason,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        // Filter failure should not block the pipeline — let the email through
        Log::warning('AVA FilterEmailJob failed, passing email through', [
            'tx_id' => $this->txId,
            'error' => $e->getMessage(),
        ]);
        DB::table('transactions')
            ->where('tx_id', $this->txId)
            ->where('status', 'pending')
            ->update(['status' => 'queued', 'updated_at' => now()]);
    }
}

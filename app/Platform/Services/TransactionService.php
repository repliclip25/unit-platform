<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    public function create(string $workerSlug, array $rawInput): object
    {
        $txId = $this->generateTxId();

        DB::table('transactions')->insert([
            'tx_id'         => $txId,
            'worker_slug'   => $workerSlug,
            'user_id'       => $rawInput['user_id'] ?? null,
            'deployment_id' => $rawInput['deployment_id'] ?? null,
            'status'        => 'received',
            'raw_input'     => json_encode($rawInput),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->log($workerSlug, $txId, 'transaction_created', ['raw_input' => $rawInput]);

        // Increment usage counters — fire-and-forget so counter issues never block ingestion
        if (!empty($rawInput['deployment_id'])) {
            try {
                DB::table('deployment_billing')
                    ->where('deployment_id', $rawInput['deployment_id'])
                    ->increment('trial_transactions_used');
                DB::table('deployment_billing')
                    ->where('deployment_id', $rawInput['deployment_id'])
                    ->increment('period_transaction_count');
            } catch (\Throwable) {}
        }

        return $this->find($txId);
    }

    public function find(string $txId): object
    {
        return DB::table('transactions')->where('tx_id', $txId)->firstOrFail();
    }

    public function updateStatus(string $txId, string $status): void
    {
        DB::table('transactions')
            ->where('tx_id', $txId)
            ->update(['status' => $status, 'updated_at' => now()]);
    }

    public function updateStageOutput(string $txId, string $column, array $data): void
    {
        DB::table('transactions')
            ->where('tx_id', $txId)
            ->update([$column => json_encode($data), 'updated_at' => now()]);
    }

    public function log(string $workerSlug, ?string $txId, string $event, array $payload = [], string $level = 'info'): void
    {
        DB::table('platform_events')->insert([
            'worker_slug' => $workerSlug,
            'tx_id'       => $txId,
            'event'       => $event,
            'payload'     => json_encode($payload),
            'level'       => $level,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    // Resolve the isolated queue name for a transaction's deployment
    public function queueForTx(object $tx): string
    {
        if ($tx->deployment_id) {
            $dep = DB::table('worker_deployments')->where('id', $tx->deployment_id)->first();
            if ($dep) return $dep->worker_slug . '-' . $dep->id;
        }
        return 'ava'; // platform fallback
    }

    public static function queueForDeployment(int $deploymentId, string $workerSlug): string
    {
        return $workerSlug . '-' . $deploymentId;
    }

    private function generateTxId(): string
    {
        do {
            $id = 'TX-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        } while (DB::table('transactions')->where('tx_id', $id)->exists());

        return $id;
    }
}

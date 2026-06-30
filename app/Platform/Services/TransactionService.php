<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    public function create(string $workerSlug, array $rawInput): object
    {
        $txId         = $this->generateTxId();
        $deploymentId = $rawInput['deployment_id'] ?? null;

        DB::transaction(function () use ($txId, $workerSlug, $rawInput, $deploymentId) {
            DB::table('transactions')->insert([
                'tx_id'         => $txId,
                'worker_slug'   => $workerSlug,
                'user_id'       => $rawInput['user_id'] ?? null,
                'deployment_id' => $deploymentId,
                'status'        => 'received',
                'is_test'       => $rawInput['is_test'] ?? false,
                'raw_input'     => json_encode($rawInput),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            if ($deploymentId) {
                // Always increment unit_count — used for quota enforcement on all plan types
                DB::table('deployment_billing')
                    ->where('deployment_id', $deploymentId)
                    ->increment('unit_count');

                // Only increment trial counter while the deployment is still in trial
                $billingStatus = DB::table('deployment_billing')
                    ->where('deployment_id', $deploymentId)
                    ->value('status');

                if ($billingStatus === 'trial') {
                    DB::table('deployment_billing')
                        ->where('deployment_id', $deploymentId)
                        ->increment('trial_transactions_used');
                }
            }
        });

        $this->log($workerSlug, $txId, 'transaction_created', ['raw_input' => $rawInput]);

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
        $userId = $txId ? DB::table('transactions')->where('tx_id', $txId)->value('user_id') : null;

        DB::table('platform_events')->insert([
            'user_id'     => $userId,
            'worker_slug' => $workerSlug,
            'tx_id'       => $txId,
            'event'       => $event,
            'payload'     => json_encode($payload),
            'level'       => $level,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    // Resolve the queue for a transaction.
    // Uses worker-type queues (not per-deployment) so Horizon can manage one pool
    // per worker type regardless of how many deployments exist.
    // Tenant isolation is at the job level — every job carries deployment_id + user_id.
    public function queueForTx(object $tx): string
    {
        if ($tx->deployment_id) {
            $slug = DB::table('worker_deployments')
                ->where('id', $tx->deployment_id)
                ->value('worker_slug');
            if ($slug) return $slug; // e.g. 'ava', 'nova', 'rex'
        }
        return 'default';
    }

    // Kept for backwards compatibility — returns worker-type queue, not deployment-scoped.
    public static function queueForDeployment(int $deploymentId, string $workerSlug): string
    {
        return $workerSlug;
    }

    private function generateTxId(): string
    {
        do {
            $id = 'TX-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        } while (DB::table('transactions')->where('tx_id', $id)->exists());

        return $id;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = [
            'transactions' => [
                'idx_tx_user'               => ['user_id'],
                'idx_tx_deployment'         => ['deployment_id'],
                'idx_tx_status'             => ['status'],
                'idx_tx_user_status'        => ['user_id', 'status'],
                'idx_tx_deployment_status'  => ['deployment_id', 'status'],
                'idx_tx_user_created'       => ['user_id', 'created_at'],
                'idx_tx_created'            => ['created_at'],
            ],
            'usage_events' => [
                'idx_usage_user_created'        => ['user_id', 'created_at'],
                'idx_usage_deployment_created'  => ['deployment_id', 'created_at'],
            ],
            'platform_events' => [
                'idx_pevents_created'      => ['created_at'],
                'idx_pevents_slug_created' => ['worker_slug', 'created_at'],
            ],
            'policy_enforcement_log' => [
                'idx_pel_user_created' => ['user_id', 'created_at'],
                'idx_pel_created'      => ['created_at'],
            ],
        ];

        if (DB::getDriverName() === 'sqlite') {
            foreach ($indexes as $table => $tableIndexes) {
                foreach ($tableIndexes as $name => $columns) {
                    $cols = implode(', ', $columns);
                    DB::statement("CREATE INDEX IF NOT EXISTS {$name} ON {$table} ({$cols})");
                }
            }
            return;
        }

        // MySQL — check existing index names before adding
        foreach ($indexes as $table => $tableIndexes) {
            $existing = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name')->unique()->toArray();
            Schema::table($table, function ($blueprint) use ($tableIndexes, $existing) {
                foreach ($tableIndexes as $name => $columns) {
                    if (!in_array($name, $existing)) {
                        $blueprint->index($columns, $name);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        $map = [
            'transactions'           => ['idx_tx_user','idx_tx_deployment','idx_tx_status','idx_tx_user_status','idx_tx_deployment_status','idx_tx_user_created','idx_tx_created'],
            'usage_events'           => ['idx_usage_user_created','idx_usage_deployment_created'],
            'platform_events'        => ['idx_pevents_created','idx_pevents_slug_created'],
            'policy_enforcement_log' => ['idx_pel_user_created','idx_pel_created'],
        ];

        foreach ($map as $table => $names) {
            if (DB::getDriverName() === 'sqlite') {
                foreach ($names as $name) {
                    DB::statement("DROP INDEX IF EXISTS {$name}");
                }
                continue;
            }
            $existing = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name')->unique()->toArray();
            Schema::table($table, function ($blueprint) use ($names, $existing) {
                foreach ($names as $name) {
                    if (in_array($name, $existing)) {
                        $blueprint->dropIndex($name);
                    }
                }
            });
        }
    }
};

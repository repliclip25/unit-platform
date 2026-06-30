<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedUserRules extends Command
{
    protected $signature = 'ava:seed-rules {userId}';
    protected $description = 'Copy platform AVA rules to a specific user deployment';

    public function handle(): void
    {
        $userId = (int) $this->argument('userId');

        $dep = DB::table('worker_deployments')
            ->where('user_id', $userId)
            ->where('worker_slug', 'ava')
            ->orderBy('id')
            ->first();

        if (!$dep) {
            $this->error("No AVA deployment found for user {$userId}");
            return;
        }

        DB::table('ava_rules')
            ->where('user_id', $userId)
            ->where('is_platform', 1)
            ->delete();

        $platformRules = DB::table('ava_rules')->whereNull('user_id')->get();

        foreach ($platformRules as $rule) {
            DB::table('ava_rules')->insertOrIgnore([
                'user_id'           => $userId,
                'deployment_id'     => $dep->id,
                'rule_id'           => $rule->rule_id,
                'condition'         => $rule->condition,
                'priority'          => $rule->priority,
                'action'            => $rule->action,
                'approval_required' => $rule->approval_required,
                'notes'             => $rule->notes,
                'active'            => 1,
                'is_platform'       => 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        $this->info("Seeded {$platformRules->count()} rules for user {$userId} / deployment {$dep->id}");
    }
}

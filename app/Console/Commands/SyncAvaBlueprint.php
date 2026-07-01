<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncAvaBlueprint extends Command
{
    protected $signature = 'ava:sync-blueprint';
    protected $description = 'Sync AVA worker blueprint from AVAWorker contract to workers table';

    public function handle(): void
    {
        $blueprint = [
            'sdk' => [
                'fast_track' => 'unit worker:test ava --deployment={deployment_id}',
                'decommission' => 'unit worker:decommission ava --deployment={deployment_id}',
                'deploy_command' => 'unit worker:deploy ava --tenant={tenant_id}',
                'manifest_version' => '2',
            ],
            'meta' => [
                'name' => 'AVA — Renewal & Subscription Coordinator',
                'slug' => 'ava',
                'license' => 'UNIT Platform License',
                'version' => '1.0.0',
                'built_by' => 'UNIT Platform',
                'category' => 'renewal',
                'description' => 'Monitors inboxes for renewal and subscription emails, classifies them with AI, looks up memory, generates professional response drafts, and delivers them — fully automated.',
            ],
            'memory' => [
                'owned' => [
                    ['scope' => 'user+worker_slug', 'table' => 'email_templates', 'access' => 'read', 'description' => 'Response templates scoped to this worker.'],
                    ['scope' => 'deployment', 'table' => 'ava_rules', 'access' => 'read', 'description' => 'Routing and behaviour rules per deployment.'],
                ],
                'shared' => [
                    ['scope' => 'user', 'table' => 'clients', 'access' => 'read+write', 'description' => 'Tenant client list.'],
                    ['scope' => 'user', 'table' => 'contacts', 'access' => 'read+write', 'description' => 'Known contacts.'],
                    ['scope' => 'user', 'table' => 'assets', 'access' => 'read+write', 'description' => 'Tracked assets.'],
                ],
            ],
            'pipeline' => [
                ['job' => 'ReadEmailJob',      'step' => 1, 'input' => 'raw_email',       'queue_key' => 'read_email', 'description' => 'Parse raw email',              'output_column' => 'read_output'],
                ['job' => 'ClassifyEmailJob',  'step' => 2, 'input' => 'read_output',     'queue_key' => 'classify',  'description' => 'AI classify',                  'output_column' => 'classify_output'],
                ['job' => 'MemoryLookupJob',   'step' => 3, 'input' => 'classify_output', 'queue_key' => 'memory',    'description' => 'Resolve contacts, assets, rules','output_column' => 'memory_output'],
                ['job' => 'LogTransactionJob', 'step' => 4, 'input' => 'memory_output',   'queue_key' => 'log',       'description' => 'Write to transaction register', 'output_column' => null],
                ['job' => 'SelectTemplateJob', 'step' => 5, 'input' => 'memory_output',   'queue_key' => 'template',  'description' => 'Pick best matching template',   'output_column' => 'template_output'],
                ['job' => 'DraftEmailJob',     'step' => 6, 'input' => 'template_output', 'queue_key' => 'draft',     'description' => 'AI generate draft',            'output_column' => 'draft_output'],
                ['job' => 'PushToGmailJob',    'step' => 7, 'input' => 'draft_output',    'queue_key' => 'push',      'description' => 'Create Gmail draft',           'output_column' => 'gmail_draft_id'],
            ],
            'subscribes' => [],
        ];

        $updated = DB::table('workers')
            ->where('slug', 'ava')
            ->update(['blueprint' => json_encode($blueprint), 'updated_at' => now()]);

        $this->info($updated ? 'AVA blueprint synced.' : 'No AVA worker row found.');
    }
}

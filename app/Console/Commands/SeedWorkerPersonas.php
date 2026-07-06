<?php

namespace App\Console\Commands;

use App\Platform\Services\WorkerRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedWorkerPersonas extends Command
{
    protected $signature   = 'unit:seed-personas {slug? : Worker slug (default: all)}';
    protected $description = 'Migrate hardcoded personas from WorkerContract into worker_personas table';

    public function handle(): int
    {
        $slugs = $this->argument('slug')
            ? [$this->argument('slug')]
            : DB::table('workers')->pluck('slug')->all();

        foreach ($slugs as $slug) {
            $contract = WorkerRegistry::resolve($slug);
            $personas = $contract->personas();

            if (empty($personas)) {
                $this->line("  {$slug}: no personas defined — skipping");
                continue;
            }

            $order = 0;
            foreach ($personas as $key => $p) {
                $exists = DB::table('worker_personas')
                    ->where('worker_slug', $slug)
                    ->where('key', $key)
                    ->exists();

                if ($exists) {
                    $this->line("  {$slug}/{$key}: already seeded — skipping");
                    continue;
                }

                DB::table('worker_personas')->insert([
                    'worker_slug'   => $slug,
                    'key'           => $key,
                    'label'         => $p['label'],
                    'tagline'       => $p['tagline'],
                    'detail'        => $p['detail'],
                    'icon'          => $p['icon'] ?? 'grid',
                    'asset_types'   => json_encode($p['asset_types'] ?? []),
                    'examples'      => json_encode($p['examples'] ?? []),
                    'memory_copy'   => json_encode($p['memory_copy'] ?? []),
                    'nudge_copy'    => json_encode($p['nudge_copy'] ?? []),
                    'capture_rules' => json_encode($p['capture_rules'] ?? []),
                    'is_active'     => true,
                    'sort_order'    => $order++,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                $this->info("  {$slug}/{$key}: seeded");
            }
        }

        $this->info('Done.');
        return 0;
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkerRegistrySeeder extends Seeder
{
    public function run(): void
    {
        $workers = [
            [
                'name'        => 'AVA',
                'slug'        => 'ava',
                'version'     => '1.0',
                'description' => 'Reads every renewal email, drafts tailored responses using your client memory and templates, and leaves them ready for your approval in Gmail.',
                'status'      => 'published',
                'media'       => json_encode(['color' => '#f1d362', 'quote' => '']),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'NUX',
                'slug'        => 'nux',
                'version'     => '1.0',
                'description' => 'Watches for new content, repurposes it for each platform natively, and delivers ready-to-publish drafts to your Gmail.',
                'status'      => 'published',
                'media'       => json_encode(['color' => '#a78bfa', 'quote' => '']),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        foreach ($workers as $worker) {
            $exists = DB::table('worker_registry')->where('slug', $worker['slug'])->exists();
            if (!$exists) {
                DB::table('worker_registry')->insert($worker);
                echo "Seeded: {$worker['slug']}\n";
            } else {
                echo "Already exists: {$worker['slug']}\n";
            }
        }
    }
}

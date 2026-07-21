<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Email template bodies in platform_email_templates hardcode links as
 * {app_url}/dashboard, {app_url}/billing, etc — literal strings resolved at
 * send-time, not routes. The tenant-app route rename (routes/web.php,
 * commit 09db8b5) moved all of these under /app/..., but pre-existing DB
 * rows were never touched by that rename (it only changed code + views).
 * Every tenant lifecycle email below still points at the pre-rename paths.
 */
return new class extends Migration
{
    private const REPLACEMENTS = [
        '{app_url}/dashboard'          => '{app_url}/app/dashboard',
        '{app_url}/settings'           => '{app_url}/app/settings',
        '{app_url}/memory'             => '{app_url}/app/memory',
        '{app_url}/billing'            => '{app_url}/app/billing',
        '{app_url}/workers/ava/memory' => '{app_url}/app/workers/ava/memory',
    ];

    public function up(): void
    {
        $this->apply(self::REPLACEMENTS);
    }

    public function down(): void
    {
        $this->apply(array_flip(self::REPLACEMENTS));
    }

    private function apply(array $replacements): void
    {
        // Longest keys first so '{app_url}/workers/ava/memory' is replaced
        // whole before the shorter '{app_url}/dashboard' etc. patterns run
        // (none currently overlap, but this keeps it safe if more are added).
        uksort($replacements, fn($a, $b) => strlen($b) <=> strlen($a));

        DB::table('platform_email_templates')->select('id', 'body')->orderBy('id')->get()
            ->each(function ($row) use ($replacements) {
                $body = $row->body;
                foreach ($replacements as $old => $new) {
                    $body = str_replace($old, $new, $body);
                }
                if ($body !== $row->body) {
                    DB::table('platform_email_templates')->where('id', $row->id)->update([
                        'body'       => $body,
                        'updated_at' => now(),
                    ]);
                }
            });
    }
};

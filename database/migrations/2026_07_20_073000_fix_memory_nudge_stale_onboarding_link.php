<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The memory_nudge_d1/d3/d7 email templates were seeded with links to
 * /onboarding/step/memory, a route removed when the old generic onboarding
 * dispatcher was deleted (superseded by the v2 hire.ava.* flow). Existing
 * rows already in the DB need the stale link swapped for the real, live
 * memory page.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('platform_email_templates')
            ->whereIn('key', ['memory_nudge_d1', 'memory_nudge_d3', 'memory_nudge_d7'])
            ->where('body', 'like', '%/onboarding/step/memory%')
            ->get()
            ->each(function ($row) {
                DB::table('platform_email_templates')
                    ->where('id', $row->id)
                    ->update([
                        'body'       => str_replace('/onboarding/step/memory', '/workers/ava/memory', $row->body),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        DB::table('platform_email_templates')
            ->whereIn('key', ['memory_nudge_d1', 'memory_nudge_d3', 'memory_nudge_d7'])
            ->where('body', 'like', '%/workers/ava/memory%')
            ->get()
            ->each(function ($row) {
                DB::table('platform_email_templates')
                    ->where('id', $row->id)
                    ->update([
                        'body'       => str_replace('/workers/ava/memory', '/onboarding/step/memory', $row->body),
                        'updated_at' => now(),
                    ]);
            });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// NUX is not production-ready for v1 launch.
// Setting status to 'created' removes it from the tenant catalog —
// WorkerController::index only shows workers with status in [running, configured, connected].
// Re-enable: update status back to 'running' when NUX ships.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('workers')->where('slug', 'nux')->update(['status' => 'created']);
    }

    public function down(): void
    {
        DB::table('workers')->where('slug', 'nux')->update(['status' => 'running']);
    }
};

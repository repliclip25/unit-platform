<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('worker_registry', function (Blueprint $table) {
            $table->string('lifecycle_status')->default('active')->after('status');
        });

        // Revert the 'published' → 'active' rename from the previous migration —
        // that migration was overloading the build-status column.
        // The build status should stay as 'published'; lifecycle goes into lifecycle_status.
        DB::table('worker_registry')->where('status', 'active')->update(['status' => 'published']);
    }

    public function down(): void
    {
        Schema::table('worker_registry', function (Blueprint $table) {
            $table->dropColumn('lifecycle_status');
        });
        DB::table('worker_registry')->where('status', 'published')->update(['status' => 'active']);
    }
};

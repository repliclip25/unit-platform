<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') return;

        DB::statement("ALTER TABLE assets MODIFY COLUMN type VARCHAR(100) NOT NULL DEFAULT 'Other'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') return;

        // Re-normalise any values not in the original enum before reverting
        DB::table('assets')
            ->whereNotIn('type', ['SSL', 'Domain', 'Hosting', 'SaaS', 'Other'])
            ->update(['type' => 'Other']);

        DB::statement("ALTER TABLE assets MODIFY COLUMN type ENUM('SSL','Domain','Hosting','SaaS','Other') NOT NULL DEFAULT 'Other'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') return;

        DB::statement("ALTER TABLE assets MODIFY COLUMN type ENUM('SSL','Domain','Hosting','SaaS','Other','discovered') NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') return;

        DB::statement("ALTER TABLE assets MODIFY COLUMN type ENUM('SSL','Domain','Hosting','SaaS','Other') NULL");
    }
};

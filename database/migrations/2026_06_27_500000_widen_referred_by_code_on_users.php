<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') return;

        DB::statement('ALTER TABLE `users` MODIFY COLUMN `referred_by_code` VARCHAR(64) NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') return;

        DB::statement('ALTER TABLE `users` MODIFY COLUMN `referred_by_code` VARCHAR(12) NULL');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') return;
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('received','ingesting','reading','classifying','memory_lookup','logging','templating','drafting','pushing','draft_ready','human_review','approved','sent','failed','blocked') NOT NULL DEFAULT 'received'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') return;
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('received','reading','classifying','memory_lookup','logging','templating','drafting','draft_ready','human_review','approved','sent','failed','blocked') NOT NULL DEFAULT 'received'");
    }
};

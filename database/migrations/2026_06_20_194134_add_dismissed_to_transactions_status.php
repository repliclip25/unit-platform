<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM(
            'received','ingesting','reading','classifying','memory_lookup',
            'logging','templating','drafting','pushing',
            'draft_ready','human_review','approved','sent',
            'failed','blocked','dismissed'
        ) NOT NULL DEFAULT 'received'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM(
            'received','ingesting','reading','classifying','memory_lookup',
            'logging','templating','drafting','pushing',
            'draft_ready','human_review','approved','sent',
            'failed','blocked'
        ) NOT NULL DEFAULT 'received'");
    }
};

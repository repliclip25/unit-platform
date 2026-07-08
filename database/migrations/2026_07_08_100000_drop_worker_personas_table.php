<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('worker_personas');
    }

    public function down(): void
    {
        // Personas are now defined in WorkerContract::personas() — no rollback
    }
};

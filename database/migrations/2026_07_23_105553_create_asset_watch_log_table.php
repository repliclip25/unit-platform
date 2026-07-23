<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Dedup ledger for AssetExpiryWatchJob — tracks which (asset, threshold)
        // pairs have already produced a synthetic renewal-reminder transaction,
        // so a daily scan doesn't redraft the same asset every single day.
        Schema::create('asset_watch_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->string('threshold', 20); // '30' | '14' | '7' | '1' | 'overdue'
            $table->timestamp('notified_at');
            $table->timestamps();

            $table->index(['asset_id', 'threshold']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_watch_log');
    }
};

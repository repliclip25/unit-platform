<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fast_track_scenarios', function (Blueprint $table) {
            // When set, the scenario runs against this real asset (and its real
            // client) instead of free-text placeholders — the only way Memory
            // Lookup can produce a genuinely earned high-confidence match
            // rather than "no match found" against a fictional test asset.
            $table->unsignedBigInteger('asset_id')->nullable()->after('deployment_id');
            $table->text('invoice_sample')->nullable()->after('custom_note');
        });
    }

    public function down(): void
    {
        Schema::table('fast_track_scenarios', function (Blueprint $table) {
            $table->dropColumn(['asset_id', 'invoice_sample']);
        });
    }
};

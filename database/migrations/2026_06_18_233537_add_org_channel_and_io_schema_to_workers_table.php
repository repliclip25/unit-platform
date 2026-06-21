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
        Schema::table('workers', function (Blueprint $table) {
            $table->string('org')->nullable()->after('slug');          // e.g. NYCSCA, DOB, FDNY, Gmail
            $table->string('channel')->nullable()->after('org');       // e.g. gmail, outlook, api, webhook
            $table->json('input_schema')->nullable()->after('channel');
            $table->json('output_schema')->nullable()->after('input_schema');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn(['org', 'channel', 'input_schema', 'output_schema']);
        });
    }
};

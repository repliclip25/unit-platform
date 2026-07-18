<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_stage_log', function (Blueprint $table) {
            $table->text('context_summary')->nullable()->after('error_summary');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_stage_log', function (Blueprint $table) {
            $table->dropColumn('context_summary');
        });
    }
};

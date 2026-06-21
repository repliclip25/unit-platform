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
        Schema::table('worker_deployments', function (Blueprint $table) {
            $table->json('pipeline_config')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('worker_deployments', function (Blueprint $table) {
            $table->dropColumn('pipeline_config');
        });
    }
};

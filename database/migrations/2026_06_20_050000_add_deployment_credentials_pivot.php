<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_credentials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deployment_id')->index();
            $table->unsignedBigInteger('credential_id')->index();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['deployment_id', 'credential_id']);
        });

        // Migrate existing credential_id links into the pivot
        DB::table('worker_deployments')
            ->whereNotNull('credential_id')
            ->get()
            ->each(function ($dep) {
                DB::table('deployment_credentials')->insertOrIgnore([
                    'deployment_id' => $dep->id,
                    'credential_id' => $dep->credential_id,
                    'is_primary'    => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_credentials');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Track which model generated each spend event
        Schema::table('usage_events', function (Blueprint $table) {
            $table->string('model', 80)->nullable()->after('stage');
        });

        // Seed platform default model into platform_configs
        DB::table('platform_configs')->updateOrInsert(
            ['key' => 'default_ai_model'],
            [
                'group'       => 'ai',
                'value'       => 'claude-sonnet-4-6',
                'type'        => 'string',
                'label'       => 'Default AI Model',
                'description' => 'Fallback model used for all tenant deployments that have not set their own model.',
                'updated_at'  => now(),
                'created_at'  => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::table('usage_events', function (Blueprint $table) {
            $table->dropColumn('model');
        });
        DB::table('platform_configs')->where('key', 'default_ai_model')->delete();
    }
};

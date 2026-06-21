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
        // Rules are per-deployment
        Schema::table('ava_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('deployment_id')->nullable()->after('user_id');
        });

        // Templates are per-worker-slug (shared across all deployments of same worker type)
        Schema::table('email_templates', function (Blueprint $table) {
            $table->string('worker_slug')->nullable()->default('ava')->after('user_id');
        });

        // Register rows already have deployment_id via transactions.deployment_id
        // but renewal_register also needs it for direct scoping
        Schema::table('renewal_register', function (Blueprint $table) {
            $table->unsignedBigInteger('deployment_id')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('ava_rules', function (Blueprint $table) {
            $table->dropColumn('deployment_id');
        });
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn('worker_slug');
        });
        Schema::table('renewal_register', function (Blueprint $table) {
            $table->dropColumn('deployment_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds worker_class to worker_registry so externally-built workers can be
 * installed without touching the platform's $map. Platform-built workers
 * (e.g. AVA) remain in $map and do not need a row here. External workers
 * set this column to their fully-qualified class name and are resolved
 * dynamically by WorkerRegistry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_registry', function (Blueprint $table) {
            // Fully-qualified PHP class name of the WorkerContract implementation.
            // Null for workers that are DB-metadata-only (no code backed class).
            $table->string('worker_class')->nullable()->after('slug');

            // Who installed this worker and when — audit trail for marketplace installs.
            $table->string('installed_by')->nullable()->after('worker_class');
            $table->timestamp('installed_at')->nullable()->after('installed_by');

            // License key provided at install time — used for future license verification.
            $table->string('license_key')->nullable()->after('installed_at');
        });
    }

    public function down(): void
    {
        Schema::table('worker_registry', function (Blueprint $table) {
            $table->dropColumn(['worker_class', 'installed_by', 'installed_at', 'license_key']);
        });
    }
};

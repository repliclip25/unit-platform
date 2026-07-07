<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Clients ──────────────────────────────────────────────────────────
        // renewal_date does NOT belong here — renewals are on assets, not clients.
        // AVA flow: asset expires → find client → find contact → notify.
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'status')) {
                $table->string('status', 50)->default('active');
            }
            if (!Schema::hasColumn('clients', 'address')) {
                $table->string('address', 500)->nullable();
            }
            if (!Schema::hasColumn('clients', 'meta')) {
                $table->json('meta')->nullable();
            }
        });

        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'phone')) {
                $table->string('phone', 50)->nullable();
            }
            if (!Schema::hasColumn('contacts', 'department')) {
                $table->string('department', 100)->nullable();
            }
            if (!Schema::hasColumn('contacts', 'is_decision_maker')) {
                $table->boolean('is_decision_maker')->default(false);
            }
            if (!Schema::hasColumn('contacts', 'meta')) {
                $table->json('meta')->nullable();
            }
        });

        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'status')) {
                $table->string('status', 50)->default('active');
            }
            if (!Schema::hasColumn('assets', 'meta')) {
                $table->json('meta')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['status', 'address', 'meta'],
                fn($col) => Schema::hasColumn('clients', $col)
            ));
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['phone', 'department', 'is_decision_maker', 'meta'],
                fn($col) => Schema::hasColumn('contacts', $col)
            ));
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['status', 'meta'],
                fn($col) => Schema::hasColumn('assets', $col)
            ));
        });
    }
};

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
                // Relationship state — every worker filters/prioritizes on this
                $table->string('status', 50)->default('active')->after('company');
            }
            if (!Schema::hasColumn('clients', 'address')) {
                // Physical location — location-based workers (DOB, FDNY, NYCSCA)
                $table->string('address', 500)->nullable()->after('status');
            }
            if (!Schema::hasColumn('clients', 'meta')) {
                // Worker-specific extended data — each worker writes its own keys
                $table->json('meta')->nullable()->after('address');
            }
        });

        // ── Contacts ─────────────────────────────────────────────────────────
        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'phone')) {
                $table->string('phone', 50)->nullable()->after('email');
            }
            if (!Schema::hasColumn('contacts', 'department')) {
                // Org structure — helps workers understand decision chains
                $table->string('department', 100)->nullable()->after('role');
            }
            if (!Schema::hasColumn('contacts', 'is_decision_maker')) {
                // Most important flag for outreach/renewal workers
                $table->boolean('is_decision_maker')->default(false)->after('department');
            }
            if (!Schema::hasColumn('contacts', 'meta')) {
                $table->json('meta')->nullable()->after('is_decision_maker');
            }
        });

        // ── Assets ───────────────────────────────────────────────────────────
        // renewal_date already exists from original migration.
        // Adding: cost_per_year (already exists), status, meta.
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'status')) {
                // active / expiring / expired / cancelled — AVA uses this to triage
                $table->string('status', 50)->default('active')->after('renewal_date');
            }
            if (!Schema::hasColumn('assets', 'meta')) {
                $table->json('meta')->nullable()->after('notes');
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

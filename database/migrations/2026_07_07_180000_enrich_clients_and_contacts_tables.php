<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Clients ──────────────────────────────────────────────────────────
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'status')) {
                // Relationship state — used by every worker to filter/prioritize
                $table->string('status', 50)->default('active')->after('company');
            }
            if (!Schema::hasColumn('clients', 'renewal_date')) {
                // Contract/subscription renewal — core to AVA and any billing worker
                $table->date('renewal_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('clients', 'address')) {
                // Physical location — required by location-based workers (DOB, FDNY, NYCSCA)
                $table->string('address', 500)->nullable()->after('renewal_date');
            }
            if (!Schema::hasColumn('clients', 'meta')) {
                // Worker-specific extended data — each worker reads/writes its own keys
                $table->json('meta')->nullable()->after('address');
            }
        });

        // ── Contacts ─────────────────────────────────────────────────────────
        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'phone')) {
                $table->string('phone', 50)->nullable()->after('email');
            }
            if (!Schema::hasColumn('contacts', 'department')) {
                // Org structure context — helps workers understand decision chains
                $table->string('department', 100)->nullable()->after('role');
            }
            if (!Schema::hasColumn('contacts', 'is_decision_maker')) {
                // Single most important flag for outreach/renewal workers
                $table->boolean('is_decision_maker')->default(false)->after('department');
            }
            if (!Schema::hasColumn('contacts', 'meta')) {
                $table->json('meta')->nullable()->after('is_decision_maker');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['status', 'renewal_date', 'address', 'meta'],
                fn($col) => Schema::hasColumn('clients', $col)
            ));
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['phone', 'department', 'is_decision_maker', 'meta'],
                fn($col) => Schema::hasColumn('contacts', $col)
            ));
        });
    }
};

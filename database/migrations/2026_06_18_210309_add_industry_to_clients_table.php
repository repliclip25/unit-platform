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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('industry')->nullable()->after('name');
            $table->string('role')->nullable()->after('industry');
        });
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('role')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['industry', 'role']);
        });
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->json('data')->nullable()->after('notes');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->json('data')->nullable()->after('is_primary');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->json('data')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('clients',  fn($t) => $t->dropColumn('data'));
        Schema::table('contacts', fn($t) => $t->dropColumn('data'));
        Schema::table('assets',   fn($t) => $t->dropColumn('data'));
    }
};

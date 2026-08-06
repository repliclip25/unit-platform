<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brand_assets', function (Blueprint $table) {
            $table->text('context')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('brand_assets', function (Blueprint $table) {
            $table->dropColumn('context');
        });
    }
};

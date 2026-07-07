<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_self_learn_dismissed', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('page_key');
        });
    }

    public function down(): void
    {
        Schema::table('user_self_learn_dismissed', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('platform_events', 'type')) {
            Schema::table('platform_events', function (Blueprint $table) {
                $table->string('type')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('platform_events', 'type')) {
            Schema::table('platform_events', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};

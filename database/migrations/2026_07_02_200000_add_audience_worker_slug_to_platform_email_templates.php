<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_email_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('platform_email_templates', 'audience')) {
                $table->string('audience')->default('all')->after('sequence');
            }
            if (!Schema::hasColumn('platform_email_templates', 'worker_slug')) {
                $table->string('worker_slug')->nullable()->after('audience');
            }
        });
    }

    public function down(): void
    {
        Schema::table('platform_email_templates', function (Blueprint $table) {
            $table->dropColumn(['audience', 'worker_slug']);
        });
    }
};

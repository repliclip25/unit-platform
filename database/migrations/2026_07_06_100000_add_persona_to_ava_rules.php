<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ava_rules', function (Blueprint $table) {
            // NULL = platform rule (applies to all personas)
            // non-null = persona-specific rule (e.g. 'insurance_broker', 'it_agency')
            $table->string('persona', 50)->nullable()->after('deployment_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('ava_rules', function (Blueprint $table) {
            $table->dropColumn('persona');
        });
    }
};

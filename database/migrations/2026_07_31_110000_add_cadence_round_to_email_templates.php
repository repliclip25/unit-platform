<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // Null = applies to any round (today's behavior, unchanged for
            // every existing row). 1/2/3 = only this cadence round — lets a
            // tenant give the 2nd/3rd reminder distinct wording instead of
            // every round reusing whatever was picked for the 1st.
            $table->unsignedTinyInteger('cadence_round')->nullable()->after('tone');
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn(['cadence_round']);
        });
    }
};

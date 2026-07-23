<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // FilterEmailJob's drop reason (e.g. "Sender excluded: ... matches
        // '...'") was previously only written to Log::info() — invisible to
        // the tenant. Persisting it makes Stage 0 (Capture Filter) visible
        // instead of a silent backend-only decision.
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('filter_reason', 500)->nullable()->after('human_notes');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('filter_reason');
        });
    }
};

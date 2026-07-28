<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Draft -> Approve -> Send is no longer a single occurrence — it's
            // up to 3 client-facing reminders on a real calendar cadence
            // (30 / 15 / 0 days before expiry), each needing its own
            // approval. client_reminder_number tracks which one we're on;
            // client_drafts holds the full content of every one sent so far
            // (draft_output stays as "most recent", this is the full history).
            $table->unsignedTinyInteger('client_reminder_number')->default(0)->after('draft_output');
            $table->json('client_drafts')->nullable()->after('client_reminder_number');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['client_reminder_number', 'client_drafts']);
        });
    }
};

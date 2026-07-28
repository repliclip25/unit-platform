<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Every reminder actually sent while waiting at a gate, packaged
            // as one JSON array instead of a dedicated table — each entry:
            // {stage_key, attempt_number, subject, body, sent_at}. Replaces
            // relying on payment_reminder_sent_at alone (a single overwritten
            // timestamp with no record of what was actually said).
            $table->json('reminders')->nullable()->after('payment_reminder_sent_at');

            // Mirrors payment_reminder_sent_at for the Approve & Send gate,
            // which never had its own reminder cadence before this.
            $table->timestamp('approval_reminder_sent_at')->nullable()->after('reminders');

            // Set once nudging stops after too many unanswered reminders —
            // the transaction isn't dismissed or lost, just set aside until
            // a human resumes it. Both reminder jobs skip a tx once this is set.
            $table->timestamp('nudging_paused_at')->nullable()->after('approval_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['reminders', 'approval_reminder_sent_at', 'nudging_paused_at']);
        });
    }
};

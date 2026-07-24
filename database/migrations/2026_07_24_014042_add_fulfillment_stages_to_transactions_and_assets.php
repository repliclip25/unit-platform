<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Stages 10-16 (invoice, documents, payment confirmation, reschedule)
        // are tracked on the same transaction as stages 0-9, not a separate
        // record — fulfillment_stage is a plain string (not an enum) so a
        // future stage can be added without another ALTER TABLE MODIFY COLUMN
        // migration. Nullable/absent means the transaction never entered
        // fulfillment (e.g. rejected at stage 9) — "not every transaction
        // requires the full pipeline" per the agreed design.
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('fulfillment_stage', 40)->nullable()->after('filter_reason');
            $table->json('invoice_output')->nullable()->after('draft_output');
            $table->json('documents_output')->nullable()->after('invoice_output');
            $table->json('payment_output')->nullable()->after('documents_output');
            $table->json('archive_output')->nullable()->after('payment_output');
            $table->timestamp('payment_reminder_sent_at')->nullable()->after('payment_output');
        });

        // Renewal cadence — needed to compute "next renewal date" (stage 13).
        // Nothing currently stores this; cost_per_year implied an assumption,
        // not a stored fact.
        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedInteger('renewal_cadence_days')->nullable()->after('renewal_date');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'fulfillment_stage', 'invoice_output', 'documents_output',
                'payment_output', 'archive_output', 'payment_reminder_sent_at',
            ]);
        });
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('renewal_cadence_days');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * push_draft's output_column was null in pipelineStages() — PushToGmailJob
 * always committed real data (in_app_only, recipient, fast_track, auto_sent)
 * via commitOutput(), but with no output_column configured, stageColumns()
 * never mapped it to anything and the write silently no-opped every time.
 * Only gmail_draft_id (a separate top-level column) ever actually landed.
 *
 * Old transactions have nothing to backfill from — this column starts null
 * for all of them and only populates going forward.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->json('push_output')->nullable()->after('draft_output');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['push_output']);
        });
    }
};

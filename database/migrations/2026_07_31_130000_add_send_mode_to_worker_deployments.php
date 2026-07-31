<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('worker_deployments', function (Blueprint $table) {
            // 'draft' (default, today's behavior) — the approved email is
            // left as a Gmail draft; the tenant opens Gmail and sends it
            // themselves. 'direct' — UNIT sends it immediately via the
            // Gmail API the moment the tenant clicks Approve & Send. Both
            // require the same OAuth scope already granted (gmail.compose
            // covers sending, not just drafting) — this is purely a
            // tenant-chosen behavior toggle, not a permissions change.
            $table->string('send_mode')->default('draft')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('worker_deployments', function (Blueprint $table) {
            $table->dropColumn(['send_mode']);
        });
    }
};

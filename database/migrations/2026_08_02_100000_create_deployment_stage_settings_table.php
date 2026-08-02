<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deployment_stage_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deployment_id')->constrained('worker_deployments')->cascadeOnDelete();
            // 'trigger' — how transactions enter the pipeline (gmail_watch, asset_watch)
            // 'stage'   — whether a pipeline stage runs at all (request_invoice,
            //             request_documents, confirm_payment, archive_evidence) —
            //             key matches pipelineStages()'s own 'key', so advance()
            //             can look it up directly with no separate mapping.
            // 'message' — whether a stage that DOES run also sends its email(s)
            //             (client_cadence, nudge_me, plus per-stage_key sends
            //             like request_invoice_followup, notify_stakeholders)
            $table->string('type');
            $table->string('key');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['deployment_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_stage_settings');
    }
};

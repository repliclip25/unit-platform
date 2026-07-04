<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            // Per-stage model map: {"read":"claude-haiku-4-5-20251001","draft":"claude-sonnet-4-6",...}
            // Takes priority over classify_model / draft_model (which remain as legacy fallbacks).
            $table->json('stage_models')->nullable()->after('draft_model_threshold');
        });

        if (DB::getDriverName() === 'sqlite') return;

        // Backfill existing rows from classify_model / draft_model columns.
        // AVA stages: read/classify/memory/template → classify_model, draft → draft_model
        $rows = DB::table('worker_pricing')
            ->where('worker_slug', 'ava')
            ->whereNull('stage_models')
            ->get(['id', 'classify_model', 'draft_model']);

        foreach ($rows as $row) {
            $classifyModel = $row->classify_model ?: 'claude-haiku-4-5-20251001';
            $draftModel    = $row->draft_model    ?: 'claude-sonnet-4-6';

            DB::table('worker_pricing')->where('id', $row->id)->update([
                'stage_models' => json_encode([
                    'read'     => $classifyModel,
                    'classify' => $classifyModel,
                    'memory'   => $classifyModel,
                    'template' => $classifyModel,
                    'draft'    => $draftModel,
                ]),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->dropColumn('stage_models');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nux_performance_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('deployment_id');
            $table->unsignedBigInteger('nux_register_id');
            $table->string('tx_id');
            $table->unsignedTinyInteger('tracking_day'); // 7 | 14 | 30 | 90
            $table->string('tracking_mode')->default('manual'); // manual | auto
            // Platform metrics (nullable — filled when submitted)
            $table->unsignedBigInteger('impressions')->nullable();
            $table->unsignedBigInteger('likes')->nullable();
            $table->unsignedBigInteger('comments')->nullable();
            $table->unsignedBigInteger('shares')->nullable();
            $table->unsignedBigInteger('clicks')->nullable();
            $table->unsignedBigInteger('reach')->nullable();
            $table->string('platform_post_url')->nullable(); // URL of the live post
            $table->text('notes')->nullable();              // Free-form user observation
            $table->timestamp('due_at');                    // When feedback is expected
            $table->timestamp('submitted_at')->nullable();  // Null = pending
            $table->boolean('enriched')->default(false);    // Has memory been updated?
            $table->timestamps();

            $table->index(['user_id', 'tracking_day']);
            $table->index(['nux_register_id', 'tracking_day']);
            $table->index('submitted_at');
            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nux_performance_log');
    }
};

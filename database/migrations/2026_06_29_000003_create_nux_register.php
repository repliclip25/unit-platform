<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nux_register', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('deployment_id');
            $table->unsignedBigInteger('transaction_id')->nullable();

            // Source post
            $table->string('source_platform', 20);       // linkedin | x
            $table->string('source_post_id', 255);
            $table->string('source_post_url', 500)->nullable();
            $table->string('source_author', 255)->nullable();
            $table->timestamp('source_posted_at')->nullable();

            // Repurposed output
            $table->json('target_channels');             // ['x', 'linkedin']
            $table->json('repurposed_copies');           // [{channel, copy, char_count}]
            $table->string('image_url', 1000)->nullable();
            $table->string('image_path', 500)->nullable();
            $table->string('draft_summary', 500)->nullable();

            // Gmail delivery
            $table->string('gmail_draft_id', 255)->nullable();

            // Classification snapshot
            $table->string('post_type', 50)->nullable();
            $table->string('topic', 255)->nullable();
            $table->string('tone', 50)->nullable();
            $table->string('repurpose_value', 20)->nullable();

            // Review state
            $table->string('status', 30)->default('draft_ready'); // draft_ready | reviewed | published | skipped
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('deployment_id');
            $table->index('transaction_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nux_register');
    }
};

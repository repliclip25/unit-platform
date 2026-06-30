<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nux_post_tracker', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('deployment_id');

            $table->string('platform', 20);          // linkedin | x
            $table->string('post_id', 255);           // platform-native post ID
            $table->string('post_url', 500)->nullable();
            $table->text('post_text');
            $table->string('author', 255)->nullable();
            $table->timestamp('posted_at')->nullable();

            // Classification results
            $table->string('post_type', 50)->nullable();  // thought_leadership | tip | story | product | other
            $table->string('topic', 255)->nullable();
            $table->string('tone', 50)->nullable();
            $table->string('repurpose_value', 20)->nullable(); // high | medium | low | skipped
            $table->float('confidence')->nullable();
            $table->string('skip_reason', 500)->nullable();

            // Processing state
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('deployment_id');
            $table->index(['deployment_id', 'platform', 'post_id']); // dedup check
            $table->index('processed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nux_post_tracker');
    }
};

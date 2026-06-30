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
        Schema::create('nux_memory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('deployment_id')->nullable(); // null = tenant-global
            $table->string('source_key');   // brand_voice | channel_rules | image_style | content_pillars | top_performing_posts | brand_colors | performance_patterns
            $table->text('content');
            $table->timestamps();

            $table->unique(['user_id', 'deployment_id', 'source_key']);
            $table->index(['user_id', 'source_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nux_memory');
    }
};

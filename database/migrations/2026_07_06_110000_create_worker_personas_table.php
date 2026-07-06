<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_personas', function (Blueprint $table) {
            $table->id();
            $table->string('worker_slug', 50)->index();
            $table->string('key', 50);
            $table->string('label', 100);
            $table->string('tagline', 200);
            $table->text('detail');
            $table->string('icon', 50)->default('grid');
            $table->json('asset_types')->nullable();
            $table->json('examples')->nullable();
            $table->json('memory_copy')->nullable();
            $table->json('nudge_copy')->nullable();
            $table->json('capture_rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['worker_slug', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_personas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Real customer reviews per worker. Only 'approved' rows are ever shown
    // publicly or counted toward Service schema aggregateRating, so a
    // pending/rejected row can never leak onto a public page or into search
    // results by accident.
    public function up(): void
    {
        Schema::create('worker_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('worker_slug');
            $table->string('author_name');
            $table->string('author_company')->nullable();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('quote');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['worker_slug', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_reviews');
    }
};

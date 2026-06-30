<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_registry', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version')->default('1.0');
            $table->text('description')->nullable();

            // Status: registered → scaffolded → in_development → testing → published → retired
            $table->string('status')->default('registered');

            // JSON blocks matching WorkerContract sections
            $table->json('org')->nullable();
            $table->json('pipeline_stages')->nullable();
            $table->json('qa_requirements')->nullable();
            $table->json('credential')->nullable();
            $table->json('instances')->nullable();
            $table->json('deployment_fields')->nullable();
            $table->json('train_schema')->nullable();
            $table->json('tags')->nullable();
            $table->json('owner')->nullable();
            $table->json('media')->nullable();
            $table->json('notifications')->nullable();

            // Build tracking
            $table->string('folder_path')->nullable();
            $table->timestamp('scaffold_generated_at')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_registry');
    }
};

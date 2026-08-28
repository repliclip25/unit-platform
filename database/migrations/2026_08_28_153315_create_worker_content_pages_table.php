<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Search-market content pages for a worker's /{worker}/ URL namespace (e.g.
// /ava/renewal-management/), one row per canonical page. Shaped like
// worker_pricing — a generic per-worker table any future worker can get rows
// in, rather than a page-per-file structure hardcoded to one worker.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_content_pages', function (Blueprint $table) {
            $table->id();
            $table->string('worker_slug');
            $table->string('tier');                 // Tier 1 / Tier 2 / Tier 3
            $table->string('page_family');           // Category Pillar, Asset Solution, Lifecycle Solution, Industry/Customer, Guide/Resource
            $table->string('url_path')->unique();    // e.g. renewal-management, assets/domains
            $table->string('primary_lifecycle_stage')->nullable();
            $table->string('primary_query');
            $table->text('secondary_queries')->nullable(); // JSON array
            $table->string('seo_title');
            $table->string('meta_description', 500);
            $table->string('h1');
            $table->longText('body');                // HTML — same pattern as blog_posts.body
            $table->string('cta_label')->nullable();
            $table->string('cta_route')->nullable();
            $table->string('publishing_wave')->default('Wave 1');
            $table->string('status')->default('draft'); // draft, published
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('worker_content_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('worker_content_pages')->cascadeOnDelete();
            $table->text('question');
            $table->text('answer');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_content_faqs');
        Schema::dropIfExists('worker_content_pages');
    }
};

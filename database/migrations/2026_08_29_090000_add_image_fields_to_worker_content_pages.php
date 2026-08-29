<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Optional per-page imagery for the hero and the pre-FAQ band. Both are
// nullable so a worker with no photo library renders exactly as before —
// no visual regression for future workers without images.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_content_pages', function (Blueprint $table) {
            $table->string('hero_image')->nullable()->after('body');
            $table->string('hero_image_alt')->nullable()->after('hero_image');
            $table->string('faq_image')->nullable()->after('hero_image_alt');
            $table->string('faq_image_alt')->nullable()->after('faq_image');
        });
    }

    public function down(): void
    {
        Schema::table('worker_content_pages', function (Blueprint $table) {
            $table->dropColumn(['hero_image', 'hero_image_alt', 'faq_image', 'faq_image_alt']);
        });
    }
};

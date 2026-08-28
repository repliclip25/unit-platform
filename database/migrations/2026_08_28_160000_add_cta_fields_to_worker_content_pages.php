<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Splits the closing CTA band's headline from its button label — they were
// wrongly sharing cta_label, producing a visibly redundant band.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_content_pages', function (Blueprint $table) {
            $table->string('cta_headline')->nullable()->after('cta_label');
            $table->text('cta_subtext')->nullable()->after('cta_headline');
        });
    }

    public function down(): void
    {
        Schema::table('worker_content_pages', function (Blueprint $table) {
            $table->dropColumn(['cta_headline', 'cta_subtext']);
        });
    }
};

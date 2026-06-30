<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('worker_slug');
            $table->string('tagline')->nullable()->after('display_name');
            $table->string('transaction_label')->nullable()->after('tagline');
            $table->string('worker_url')->nullable()->after('transaction_label');
            $table->string('accent_color')->nullable()->default('#f1d362')->after('worker_url');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('accent_color');
        });
    }

    public function down(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'tagline', 'transaction_label', 'worker_url', 'accent_color', 'sort_order']);
        });
    }
};

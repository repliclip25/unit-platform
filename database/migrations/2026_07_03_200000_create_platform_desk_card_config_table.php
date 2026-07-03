<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_desk_card_config', function (Blueprint $table) {
            $table->string('card_key')->primary();
            $table->boolean('active')->default(true);       // admin can disable globally
            $table->boolean('default_on')->default(true);   // admin can change default
            $table->string('label')->nullable();            // admin can override label
            $table->string('description')->nullable();      // admin can override description
            $table->unsignedSmallInteger('sort_order')->default(50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_desk_card_config');
    }
};

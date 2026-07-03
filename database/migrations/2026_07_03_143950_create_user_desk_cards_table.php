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
        Schema::create('user_desk_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('card_key');                       // e.g. 'pipeline.drafts'
            $table->boolean('visible')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamp('last_dismissed_at')->nullable(); // one-time milestone cards
            $table->timestamps();

            $table->unique(['user_id', 'card_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_desk_cards');
    }
};

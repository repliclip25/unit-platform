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
        // Stores AVA's last known Gmail historyId — ensures no emails are ever missed
        Schema::create('ava_state', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->timestamps();
        });

        // Tracks every Gmail message ID AVA has seen — prevents duplicate processing
        Schema::create('processed_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->unique();
            $table->string('tx_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_messages');
        Schema::dropIfExists('ava_state');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_self_learn_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('page_key');
            $table->enum('event', ['shown', 'dismissed']);
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'page_key']);
            $table->index(['page_key', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_self_learn_events');
    }
};

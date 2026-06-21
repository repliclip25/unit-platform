<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_gmail_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('gmail_address');
            $table->text('refresh_token');
            $table->string('history_id')->nullable();       // last processed Gmail historyId
            $table->timestamp('watch_expires_at')->nullable(); // Gmail watch expiry
            $table->boolean('watch_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_gmail_credentials');
    }
};

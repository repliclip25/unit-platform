<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_drive_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('drive_email');
            $table->text('refresh_token');
            $table->text('scope')->nullable();
            $table->string('root_folder_id')->nullable();
            $table->text('root_folder_url')->nullable();
            $table->timestamp('token_last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_drive_credentials');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nux_oauth_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('deployment_id')->nullable();

            $table->string('platform', 20);           // linkedin | x
            $table->string('platform_user_id', 255)->nullable();
            $table->string('platform_username', 255)->nullable();
            $table->string('platform_display_name', 255)->nullable();

            // Encrypted tokens — never stored plain
            $table->text('access_token');             // Crypt::encryptString()
            $table->text('refresh_token')->nullable(); // Crypt::encryptString()
            $table->timestamp('token_expires_at')->nullable();

            // OAuth 2.0 PKCE state (X uses this)
            $table->string('code_verifier', 500)->nullable();
            $table->string('state', 255)->nullable();

            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'platform']);
            $table->index(['deployment_id', 'platform']);
            $table->unique(['user_id', 'platform']); // one token per platform per user
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nux_oauth_tokens');
    }
};

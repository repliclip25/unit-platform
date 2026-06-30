<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('scope');           // 'platform' | 'worker'
            $table->string('worker_slug')->nullable(); // 'ava', 'nova', etc — null for platform
            $table->string('service');         // 'google_oauth', 'gmail_pubsub', 'stripe', 'anthropic', 'smtp', etc.
            $table->string('label');           // "Google Sign-In", "AVA Gmail Pub/Sub"
            $table->string('type');            // 'oauth' | 'webhook' | 'pubsub' | 'api_key' | 'callback_url' | 'smtp'
            $table->string('local_url')->nullable();
            $table->string('production_url')->nullable();
            $table->json('env_keys')->nullable();   // which .env keys this integration uses
            $table->json('meta')->nullable();        // extra config: topic names, scopes, etc.
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_integrations');
    }
};

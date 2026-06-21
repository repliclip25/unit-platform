<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fast_track_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('worker_slug');
            $table->string('source');              // 'tenant_fast_track' | 'public_demo'
            $table->unsignedBigInteger('user_id')->nullable(); // null for public/outsider submissions
            $table->string('tx_id')->nullable();   // transaction fired for this lead
            $table->boolean('subscribed')->default(true);  // newsletter opt-in (default yes)
            $table->json('flags')->nullable();     // { deployment_id, org, version, ... }
            $table->timestamps();

            $table->index('email');
            $table->index('worker_slug');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fast_track_leads');
    }
};

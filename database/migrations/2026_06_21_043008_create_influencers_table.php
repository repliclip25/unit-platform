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
        Schema::create('influencers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('slug', 32)->unique();           // vanity URL: /r/{slug}
            $table->string('channel')->nullable();           // linkedin, youtube, newsletter, etc.
            $table->string('audience_size')->nullable();     // "10k", "50k", etc.
            $table->string('niche')->nullable();             // "license compliance", "real estate", etc.
            $table->string('status')->default('pending');    // pending | active | paused | rejected
            $table->string('tier')->default('starter');      // starter | pro | elite
            $table->decimal('commission_rate', 5, 4)->default(0.20); // 0.20 = 20%
            $table->string('payout_email')->nullable();
            $table->string('payout_method')->default('paypal'); // paypal | bank | stripe
            $table->decimal('total_earned', 10, 2)->default(0);
            $table->decimal('pending_payout', 10, 2)->default(0);
            $table->decimal('paid_out', 10, 2)->default(0);
            $table->text('notes')->nullable();               // admin notes
            $table->string('utm_source')->nullable();        // for ad tracking
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('influencers');
    }
};

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
        Schema::create('referral_clicks', function (Blueprint $table) {
            $table->id();
            // source: 'tenant' uses referral_code, 'influencer' uses influencer slug
            $table->string('ref_type');                     // 'tenant' | 'influencer'
            $table->string('ref_code');                     // code or slug
            $table->unsignedBigInteger('influencer_id')->nullable()->index();
            $table->string('ip')->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('landing_page')->nullable();
            $table->boolean('converted')->default(false);   // did they sign up?
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_clicks');
    }
};

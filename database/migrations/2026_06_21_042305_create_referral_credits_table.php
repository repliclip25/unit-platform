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
        Schema::create('referral_credits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id')->index();   // who earned the credit
            $table->unsignedBigInteger('referee_id')->index();    // who converted
            $table->string('event');                               // 'signup' | 'paid_conversion'
            $table->decimal('credit_usd', 8, 2)->default(0);     // credit earned
            $table->integer('bonus_tx')->default(0);              // bonus trial tx given to referee
            $table->string('status')->default('pending');          // pending | applied | expired
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_credits');
    }
};

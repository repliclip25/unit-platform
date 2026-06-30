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
        Schema::create('verification_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();          // email, phone, kyc, business, location
            $table->string('label');                   // "Email Address", "Phone Number", etc.
            $table->string('description')->nullable(); // shown to user during onboarding
            $table->boolean('required')->default(false);
            $table->boolean('blocks_onboarding')->default(true); // if false, can proceed without it
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_requirements');
    }
};

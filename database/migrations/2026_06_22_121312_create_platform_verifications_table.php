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
        Schema::create('platform_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');                    // email, phone, kyc, business, location
            $table->timestamp('verified_at')->nullable();
            $table->json('data')->nullable();          // e.g. phone number, KYC doc ref, etc.
            $table->string('verified_by')->default('self'); // self, admin, third_party
            $table->timestamps();

            $table->unique(['user_id', 'type']);
            $table->index(['user_id', 'verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_verifications');
    }
};

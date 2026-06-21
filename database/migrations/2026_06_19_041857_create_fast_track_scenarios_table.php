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
        Schema::create('fast_track_scenarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deployment_id')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('scenario_title')->default('Domain Renewal Test');
            $table->string('sender_name')->default('Namecheap Renewals Team');
            $table->string('sender_email')->default('renewals@namecheap.com');
            $table->string('asset_name')->default('yourdomain.com');
            $table->string('asset_type')->default('Domain');
            $table->string('contact_name')->default('My Client');
            $table->string('renewal_price')->default('$12.98/year');
            $table->unsignedInteger('days_until_expiry')->default(14);
            $table->text('custom_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fast_track_scenarios');
    }
};

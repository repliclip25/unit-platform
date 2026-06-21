<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_pricing', function (Blueprint $table) {
            $table->id();
            $table->string('worker_slug')->unique();
            $table->unsignedInteger('free_transactions')->default(10);
            $table->decimal('monthly_flat_rate', 8, 2)->default(0);
            $table->unsignedInteger('included_transactions')->default(500);
            $table->decimal('overage_price_per_tx', 8, 4)->default(0.10);
            $table->string('stripe_flat_price_id')->nullable();
            $table->string('stripe_overage_price_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('platform_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->decimal('discount_pct', 5, 2);
            $table->enum('applies_to', ['all', 'worker'])->default('all');
            $table->string('worker_slug')->nullable();
            $table->boolean('applies_to_overage')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('deployment_billing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('deployment_id');
            $table->string('worker_slug');
            $table->string('stripe_subscription_id')->nullable();
            $table->enum('status', ['trial', 'active', 'past_due', 'canceled', 'paused'])->default('trial');
            $table->unsignedInteger('trial_transactions_used')->default(0);
            $table->unsignedInteger('trial_transactions_limit');
            $table->date('billing_period_start')->nullable();
            $table->unsignedInteger('period_transaction_count')->default(0);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_billing');
        Schema::dropIfExists('platform_promotions');
        Schema::dropIfExists('worker_pricing');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->decimal('discount_pct', 5, 2)->nullable()->after('monthly_flat_rate');
            $table->string('promo_label', 80)->nullable()->after('discount_pct');
            $table->timestamp('promo_expires_at')->nullable()->after('promo_label');
            $table->string('stripe_coupon_id', 120)->nullable()->after('stripe_flat_price_id');
        });
    }

    public function down(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->dropColumn(['discount_pct', 'promo_label', 'promo_expires_at', 'stripe_coupon_id']);
        });
    }
};

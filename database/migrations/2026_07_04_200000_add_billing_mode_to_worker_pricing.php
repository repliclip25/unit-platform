<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->string('billing_mode', 10)->default('test')->after('stripe_product_id');
            $table->string('stripe_test_price_id', 120)->nullable()->after('stripe_flat_price_id');
        });

        Schema::table('deployment_billing', function (Blueprint $table) {
            $table->string('billing_mode', 10)->default('test')->after('plan_slug');
        });
    }

    public function down(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->dropColumn(['billing_mode', 'stripe_test_price_id']);
        });

        Schema::table('deployment_billing', function (Blueprint $table) {
            $table->dropColumn('billing_mode');
        });
    }
};

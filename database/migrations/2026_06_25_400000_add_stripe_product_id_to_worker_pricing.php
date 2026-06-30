<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->string('stripe_product_id')->nullable()->after('stripe_overage_price_id');
        });
    }

    public function down(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->dropColumn('stripe_product_id');
        });
    }
};

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
        Schema::table('referral_credits', function (Blueprint $table) {
            $table->unsignedBigInteger('influencer_id')->nullable()->after('referrer_id')->index();
            $table->string('ref_type')->default('tenant')->after('influencer_id'); // 'tenant' | 'influencer'
            $table->decimal('commission_rate', 5, 4)->nullable()->after('credit_usd');
            $table->decimal('mrr_attributed', 8, 2)->default(0)->after('commission_rate');
        });
    }

    public function down(): void
    {
        Schema::table('referral_credits', function (Blueprint $table) {
            $table->dropColumn(['influencer_id', 'ref_type', 'commission_rate', 'mrr_attributed']);
        });
    }
};

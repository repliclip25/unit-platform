<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deployment_billing', function (Blueprint $table) {
            // How many prompt-test API calls this deployment has made (trial tenants only)
            $table->unsignedInteger('prompt_test_uses')->default(0)->after('trial_transactions_used');
            // Cap for trial tenants — subscribed tenants are unlimited
            $table->unsignedInteger('prompt_test_limit')->default(5)->after('prompt_test_uses');
        });
    }

    public function down(): void
    {
        Schema::table('deployment_billing', function (Blueprint $table) {
            $table->dropColumn(['prompt_test_uses', 'prompt_test_limit']);
        });
    }
};

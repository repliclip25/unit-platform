<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spend cap breach tracking on users
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('spend_cap_breach_since')->nullable()->after('monthly_spend_cap');
        });

        // Audit log for automated enforcement actions
        Schema::create('policy_enforcement_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('action', 50);       // auto_block, breach_detected, etc.
            $table->string('policy_code', 50);
            $table->string('detail', 500)->nullable();
            $table->timestamp('created_at');
        });

        // past_due_since on deployment_billing for the 3-day grace rule
        if (Schema::hasTable('deployment_billing') && !Schema::hasColumn('deployment_billing', 'past_due_since')) {
            Schema::table('deployment_billing', function (Blueprint $table) {
                $table->timestamp('past_due_since')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('spend_cap_breach_since');
        });
        Schema::dropIfExists('policy_enforcement_log');
        if (Schema::hasColumn('deployment_billing', 'past_due_since')) {
            Schema::table('deployment_billing', function (Blueprint $table) {
                $table->dropColumn('past_due_since');
            });
        }
    }
};

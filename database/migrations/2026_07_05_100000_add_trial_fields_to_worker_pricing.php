<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            // Marks this plan as the trial experience tier — not shown in subscription paywall.
            // Each worker should have exactly one trial plan; defaultPlan() points to it.
            $table->boolean('is_trial_plan')->default(false)->after('active');

            // Per-plan trial duration. Null = falls back to platform_configs.trial_days or
            // the PlatformDefaults::TRIAL_DAYS constant. Lets admins set 14/30/60/90 per worker.
            $table->unsignedSmallInteger('trial_days')->nullable()->after('is_trial_plan');
        });

        if (DB::getDriverName() === 'sqlite') return;

        // Mark the AVA Starter plan as the trial plan with a 30-day window
        DB::table('worker_pricing')
            ->where('worker_slug', 'ava')
            ->where('plan_slug', 'starter')
            ->update(['is_trial_plan' => true, 'trial_days' => 30]);
    }

    public function down(): void
    {
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->dropColumn(['is_trial_plan', 'trial_days']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── users: master Stripe subscription ──────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_subscription_id')->nullable()->after('stripe_id');
        });

        // ── deployment_billing: item-level tracking + unit model ────────────
        Schema::table('deployment_billing', function (Blueprint $table) {
            $table->string('stripe_subscription_item_id')->nullable()->after('stripe_subscription_id');
            $table->string('billing_unit')->default('transaction')->after('plan_slug');
            $table->unsignedInteger('unit_count')->default(0)->after('billing_unit');
        });

        // ── worker_pricing: declare billing unit per plan ───────────────────
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->string('billing_unit')->default('transaction')->after('plan_slug');
        });

        // Update AVA pricing rows with correct billing unit
        DB::table('worker_pricing')->where('worker_slug', 'ava')->update(['billing_unit' => 'email']);

        // ── platform_configs: trial gate toggle ─────────────────────────────
        DB::table('platform_configs')->updateOrInsert(
            ['key' => 'trial_payment_required'],
            [
                'group'       => 'billing',
                'value'       => 'false',
                'type'        => 'boolean',
                'label'       => 'Require payment method for new trials',
                'description' => 'When enabled, new tenants must enter a payment method before starting a trial. Card is not charged until trial ends.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );

        DB::table('platform_configs')->updateOrInsert(
            ['key' => 'trial_days'],
            [
                'group'       => 'billing',
                'value'       => '14',
                'type'        => 'integer',
                'label'       => 'Trial duration (days)',
                'description' => 'Number of days before a gated trial converts to a paid subscription.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::table('users', fn($t) => $t->dropColumn('stripe_subscription_id'));
        Schema::table('deployment_billing', fn($t) => $t->dropColumn(['stripe_subscription_item_id', 'billing_unit', 'unit_count']));
        Schema::table('worker_pricing', fn($t) => $t->dropColumn('billing_unit'));
        DB::table('platform_configs')->whereIn('key', ['trial_payment_required', 'trial_days'])->delete();
    }
};

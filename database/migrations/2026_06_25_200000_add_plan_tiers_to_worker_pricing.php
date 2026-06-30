<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── worker_pricing: add plan_slug, drop old unique, add new unique ──
        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->string('plan_slug')->default('starter')->after('worker_slug');
            $table->text('plan_highlights')->nullable()->after('overage_price_per_tx'); // JSON array
            $table->boolean('prompt_overrides')->default(false)->after('plan_highlights');
            $table->string('support_label')->nullable()->after('prompt_overrides');
            $table->integer('transaction_limit')->nullable()->after('included_transactions'); // null = unlimited
        });

        // Drop the existing unique on worker_slug alone, replace with composite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS worker_pricing_worker_slug_unique');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS worker_pricing_slug_plan_unique ON worker_pricing (worker_slug, plan_slug)');
        } else {
            Schema::table('worker_pricing', function (Blueprint $table) {
                $table->dropUnique('worker_pricing_worker_slug_unique');
                $table->unique(['worker_slug', 'plan_slug'], 'worker_pricing_slug_plan_unique');
            });
        }

        // ── deployment_billing: add plan_slug ───────────────────────────────
        Schema::table('deployment_billing', function (Blueprint $table) {
            $table->string('plan_slug')->nullable()->after('worker_slug');
        });

        // ── Seed AVA tiers ──────────────────────────────────────────────────
        // Update the existing AVA row to be the Starter tier
        DB::table('worker_pricing')
            ->where('worker_slug', 'ava')
            ->update([
                'plan_slug'           => 'starter',
                'display_name'        => 'AVA — Starter',
                'monthly_flat_rate'   => 49.00,
                'transaction_limit'   => 100,
                'included_transactions' => 100,
                'prompt_overrides'    => false,
                'support_label'       => 'Email support',
                'plan_highlights'     => json_encode([
                    '100 emails processed per month',
                    'Full 8-stage pipeline',
                    'Gmail draft creation',
                    'Memory: clients, contacts, assets',
                    'Email support',
                ]),
                'updated_at' => now(),
            ]);

        // Insert Pro tier
        DB::table('worker_pricing')->insert([
            'worker_slug'             => 'ava',
            'plan_slug'               => 'pro',
            'display_name'            => 'AVA — Pro',
            'tagline'                 => 'Unlimited processing + prompt overrides',
            'transaction_label'       => 'email',
            'monthly_flat_rate'       => 149.00,
            'transaction_limit'       => null, // unlimited
            'included_transactions'   => 0,
            'free_transactions'       => 0,
            'overage_price_per_tx'    => 0.00,
            'prompt_overrides'        => true,
            'support_label'           => 'Priority email support',
            'plan_highlights'         => json_encode([
                'Unlimited emails processed',
                'Per-stage prompt overrides',
                'Multi-inbox support',
                'Advanced renewal register',
                'Priority email support',
            ]),
            'active'     => true,
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert Enterprise tier
        DB::table('worker_pricing')->insert([
            'worker_slug'             => 'ava',
            'plan_slug'               => 'enterprise',
            'display_name'            => 'AVA — Enterprise',
            'tagline'                 => 'Custom volume + dedicated support',
            'transaction_label'       => 'email',
            'monthly_flat_rate'       => 0.00, // custom — handled off-platform
            'transaction_limit'       => null,
            'included_transactions'   => 0,
            'free_transactions'       => 0,
            'overage_price_per_tx'    => 0.00,
            'prompt_overrides'        => true,
            'support_label'           => 'Dedicated support + SLA',
            'plan_highlights'         => json_encode([
                'Unlimited emails processed',
                'Per-stage prompt overrides',
                'Dedicated support & SLA',
                'Custom onboarding',
                'Volume pricing',
            ]),
            'active'     => true,
            'sort_order' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Remove AVA pro/enterprise rows
        DB::table('worker_pricing')
            ->where('worker_slug', 'ava')
            ->whereIn('plan_slug', ['pro', 'enterprise'])
            ->delete();

        // Revert starter row
        DB::table('worker_pricing')
            ->where('worker_slug', 'ava')
            ->where('plan_slug', 'starter')
            ->update(['plan_slug' => 'starter', 'display_name' => 'AVA', 'updated_at' => now()]);

        Schema::table('deployment_billing', function (Blueprint $table) {
            $table->dropColumn('plan_slug');
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('worker_pricing', function (Blueprint $table) {
                $table->dropUnique('worker_pricing_slug_plan_unique');
                $table->unique('worker_slug', 'worker_pricing_worker_slug_unique');
            });
        }

        Schema::table('worker_pricing', function (Blueprint $table) {
            $table->dropColumn(['plan_slug', 'plan_highlights', 'prompt_overrides', 'support_label', 'transaction_limit']);
        });
    }
};

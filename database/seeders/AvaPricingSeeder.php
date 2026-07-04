<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvaPricingSeeder extends Seeder
{
    public function run(): void
    {
        // Wipe existing AVA pricing rows so this is idempotent
        DB::table('worker_pricing')->where('worker_slug', 'ava')->delete();

        $now = now();

        DB::table('worker_pricing')->insert([

            // ── Starter ──────────────────────────────────────────────────────
            [
                'worker_slug'          => 'ava',
                'plan_slug'            => 'starter',
                'billing_unit'         => 'email',
                'display_name'         => 'AVA — Starter',
                'tagline'              => 'Automate your renewal inbox. Review only what needs a human.',
                'transaction_label'    => 'one renewal email read, classified, drafted, and delivered to your review queue',
                'worker_url'           => '/w/ava',
                'accent_color'         => '#cb0655',
                'sort_order'           => 1,
                'free_transactions'    => 25,
                'monthly_flat_rate'    => 49.00,
                'discount_pct'         => 0,
                'promo_label'          => null,
                'promo_expires_at'     => null,
                'included_transactions'=> 200,
                'transaction_limit'    => 200,
                'overage_price_per_tx' => 0.25,
                'plan_highlights'      => json_encode([
                    '200 renewal emails processed per month',
                    'Full 8-stage pipeline — read, classify, draft, review',
                    'Gmail inbox connect & draft delivery',
                    'Client, contact & asset memory',
                    'Renewal register with status tracking',
                    'Email support',
                ]),
                'prompt_overrides'     => false,
                'support_label'        => 'Email support',
                'billing_mode'          => 'test',
                'ai_tier'               => 'economy',
                'classify_model'        => 'claude-haiku-4-5-20251001',
                'draft_model'           => 'claude-haiku-4-5-20251001',
                'draft_model_threshold' => null,
                'stage_models'          => json_encode(['read'=>'claude-haiku-4-5-20251001','classify'=>'claude-haiku-4-5-20251001','memory'=>'claude-haiku-4-5-20251001','template'=>'claude-haiku-4-5-20251001','draft'=>'claude-haiku-4-5-20251001']),
                'stripe_flat_price_id'  => '',
                'stripe_test_price_id'  => '',
                'stripe_coupon_id'      => '',
                'stripe_overage_price_id' => '',
                'stripe_product_id'     => '',
                'active'                => true,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],

            // ── Pro ──────────────────────────────────────────────────────────
            [
                'worker_slug'          => 'ava',
                'plan_slug'            => 'pro',
                'billing_unit'         => 'email',
                'display_name'         => 'AVA — Pro',
                'tagline'              => 'Unlimited processing, full customisation, and multi-inbox coverage.',
                'transaction_label'    => 'one renewal email read, classified, drafted, and delivered to your review queue',
                'worker_url'           => '/w/ava',
                'accent_color'         => '#f1d362',
                'sort_order'           => 2,
                'free_transactions'    => 25,
                'monthly_flat_rate'    => 149.00,
                'discount_pct'         => 0,
                'promo_label'          => null,
                'promo_expires_at'     => null,
                'included_transactions'=> 0,
                'transaction_limit'    => null,
                'overage_price_per_tx' => 0.00,
                'plan_highlights'      => json_encode([
                    'Unlimited renewal emails processed',
                    'Per-stage prompt overrides — customise every AI step',
                    'Connect up to 5 Gmail inboxes',
                    'Advanced renewal register with analytics',
                    'Fast Track — one-click approve & send',
                    'Priority email support',
                ]),
                'prompt_overrides'     => true,
                'support_label'        => 'Priority email support',
                'billing_mode'          => 'test',
                'ai_tier'               => 'standard',
                'classify_model'        => 'claude-haiku-4-5-20251001',
                'draft_model'           => 'claude-sonnet-4-6',
                'draft_model_threshold' => 500,
                'stage_models'          => json_encode(['read'=>'claude-haiku-4-5-20251001','classify'=>'claude-haiku-4-5-20251001','memory'=>'claude-haiku-4-5-20251001','template'=>'claude-haiku-4-5-20251001','draft'=>'claude-sonnet-4-6']),
                'stripe_flat_price_id'  => '',
                'stripe_test_price_id'  => '',
                'stripe_coupon_id'      => '',
                'stripe_overage_price_id' => '',
                'stripe_product_id'     => '',
                'active'                => true,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],

            // ── Enterprise ───────────────────────────────────────────────────
            [
                'worker_slug'          => 'ava',
                'plan_slug'            => 'enterprise',
                'billing_unit'         => 'email',
                'display_name'         => 'AVA — Enterprise',
                'tagline'              => 'Custom volume, dedicated onboarding, and an SLA you can quote.',
                'transaction_label'    => 'one renewal email read, classified, drafted, and delivered to your review queue',
                'worker_url'           => '/w/ava',
                'accent_color'         => '#a78bfa',
                'sort_order'           => 3,
                'free_transactions'    => 25,
                'monthly_flat_rate'    => 0.00,
                'discount_pct'         => 0,
                'promo_label'          => null,
                'promo_expires_at'     => null,
                'included_transactions'=> 0,
                'transaction_limit'    => null,
                'overage_price_per_tx' => 0.00,
                'plan_highlights'      => json_encode([
                    'Unlimited emails — volume pricing available',
                    'Full prompt overrides on every pipeline stage',
                    'Unlimited inbox connections',
                    'Dedicated onboarding & setup support',
                    'SLA guarantee with uptime commitment',
                    'Dedicated support manager',
                ]),
                'prompt_overrides'     => true,
                'support_label'        => 'Dedicated support manager',
                'billing_mode'          => 'test',
                'ai_tier'               => 'premium',
                'classify_model'        => 'claude-sonnet-4-6',
                'draft_model'           => 'claude-sonnet-4-6',
                'draft_model_threshold' => null,
                'stage_models'          => json_encode(['read'=>'claude-sonnet-4-6','classify'=>'claude-sonnet-4-6','memory'=>'claude-sonnet-4-6','template'=>'claude-sonnet-4-6','draft'=>'claude-sonnet-4-6']),
                'stripe_flat_price_id'  => '',
                'stripe_test_price_id'  => '',
                'stripe_coupon_id'      => '',
                'stripe_overage_price_id' => '',
                'stripe_product_id'     => '',
                'active'                => true,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],

        ]);

        $this->command->info('AVA pricing seeded: Starter ($49) · Pro ($149) · Enterprise (custom)');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeTenants extends Command
{
    protected $signature   = 'tenants:purge';
    protected $description = 'Wipe all operational data for a clean slate. Tenant accounts are deleted. Admin accounts are kept but their operational data is cleared.';

    public function handle(): int
    {
        $allUserIds    = DB::table('users')->pluck('id')->toArray();
        $tenantIds     = DB::table('users')->where('role', 'tenant')->pluck('id')->toArray();
        $adminIds      = DB::table('users')->where('role', 'admin')->pluck('id')->toArray();

        $this->info('Purging ' . count($tenantIds) . ' tenant account(s) + operational data for ' . count($adminIds) . ' admin(s)...');

        DB::transaction(function () use ($allUserIds, $tenantIds, $adminIds) {
            $allDepIds    = DB::table('worker_deployments')->whereIn('user_id', $allUserIds)->pluck('id')->toArray();
            $tenantDepIds = DB::table('worker_deployments')->whereIn('user_id', $tenantIds)->pluck('id')->toArray();

            // ── Deployment-scoped tables (all users) ──────────────────────────
            if ($allDepIds) {
                DB::table('deployment_credentials')->whereIn('deployment_id', $allDepIds)->delete();
                DB::table('deployment_billing')->whereIn('deployment_id', $allDepIds)->delete();
                DB::table('deployment_prompt_overrides')->whereIn('deployment_id', $allDepIds)->delete();
            }

            // ── Operational tables — wipe for ALL users ───────────────────────
            $operationalTables = [
                'transactions',
                'transaction_stage_log',
                'renewal_register',
                'usage_events',
                'user_gmail_credentials',
                'ava_state',
                'clients',
                'contacts',
                'assets',
                'memory_contributions',
                'platform_verifications',
                'policy_enforcement_log',
                'processed_messages',
                'tenant_api_keys',
                'tenant_custom_models',
                'tenant_email_log',
                'subscriptions',
                'worker_onboarding_sessions',
            ];

            foreach ($operationalTables as $table) {
                DB::table($table)->whereIn('user_id', $allUserIds)->delete();
            }

            // ── Tenant-only tables (platform defaults preserved via NULL user_id) ──
            DB::table('ava_rules')->whereIn('user_id', $allUserIds)->delete();
            DB::table('email_templates')->whereIn('user_id', $allUserIds)->delete();

            // worker_events uses source_user_id
            DB::table('worker_events')->whereIn('source_user_id', $allUserIds)->delete();

            // ── Deployments ───────────────────────────────────────────────────
            DB::table('worker_deployments')->whereIn('user_id', $allUserIds)->delete();

            // ── Orphaned rows with no user_id (catch early iteration data) ──
            foreach (['transactions','renewal_register','clients','contacts','assets','processed_messages','ava_state'] as $t) {
                DB::table($t)->whereNull('user_id')->delete();
            }

            // ── Truly orphaned deployment-scoped rows (no parent deployment) ──
            $remainingDepIds = DB::table('worker_deployments')->pluck('id')->toArray();
            if (empty($remainingDepIds)) {
                DB::table('deployment_credentials')->delete();
                DB::table('deployment_billing')->delete();
                DB::table('deployment_prompt_overrides')->delete();
            }

            // ── Subscription items (keyed by subscription_id, not user_id) ───
            DB::table('subscription_items')->delete();

            // ── Queue cleanup ─────────────────────────────────────────────────
            DB::table('failed_jobs')->delete();

            // ── Platform audit log — wipe entirely (tx_ids no longer exist) ──
            DB::table('platform_events')->delete();

            // ── Sessions for all users ────────────────────────────────────────
            DB::table('sessions')->whereIn('user_id', $allUserIds)->delete();

            // ── Delete tenant accounts, preserve admin accounts ───────────────
            if ($tenantIds) {
                DB::table('users')->whereIn('id', $tenantIds)->delete();
            }

            // ── Reset admin onboarding state and billing so they start fresh ──
            if ($adminIds) {
                DB::table('users')->whereIn('id', $adminIds)->update([
                    'onboarding_completed_at' => null,
                    'onboarding_skipped'      => false,
                    'stripe_id'               => null,
                    'trial_ends_at'           => null,
                    'updated_at'              => now(),
                ]);
            }

            // ── Reset worker registry to active ───────────────────────────────
            DB::table('worker_registry')
                ->whereIn('lifecycle_status', ['decommissioned', 'removing', 'removed'])
                ->update(['lifecycle_status' => 'active', 'updated_at' => now()]);
        });

        $this->info('Clean slate. Admin account(s) preserved — onboarding reset so they can walk the flow fresh.');
        $this->info('Platform config, default rules, and platform-level templates untouched.');
        return 0;
    }
}

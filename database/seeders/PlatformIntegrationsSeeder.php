<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlatformIntegrationsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $integrations = [
            // ── Platform: Auth ────────────────────────────────────────────────
            [
                'scope'          => 'platform',
                'worker_slug'    => null,
                'service'        => 'google_oauth',
                'label'          => 'Google Sign-In',
                'type'           => 'oauth',
                'local_url'      => 'http://localhost:8000/auth/google/callback',
                'production_url' => 'https://unit.report/auth/google/callback',
                'env_keys'       => json_encode(['GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET', 'GOOGLE_REDIRECT_URI']),
                'meta'           => json_encode(['scopes' => ['email', 'profile', 'openid'], 'console' => 'https://console.cloud.google.com/apis/credentials']),
                'notes'          => 'OAuth consent screen must list unit.report as authorized domain. Redirect URI must exactly match GOOGLE_REDIRECT_URI.',
                'sort_order'     => 10,
            ],
            [
                'scope'          => 'platform',
                'worker_slug'    => null,
                'service'        => 'apple_oauth',
                'label'          => 'Apple Sign-In',
                'type'           => 'oauth',
                'local_url'      => null,
                'production_url' => 'https://unit.report/auth/apple/callback',
                'env_keys'       => json_encode(['APPLE_CLIENT_ID', 'APPLE_CLIENT_SECRET', 'APPLE_REDIRECT_URI']),
                'meta'           => json_encode(['status' => 'pending', 'note' => 'Requires paid Apple Developer account. Client secret is a JWT, expires every 6 months.']),
                'notes'          => 'Pending Apple Developer account approval. Apple does not work on localhost — requires HTTPS production URL only.',
                'sort_order'     => 11,
            ],

            // ── Platform: Billing ─────────────────────────────────────────────
            [
                'scope'          => 'platform',
                'worker_slug'    => null,
                'service'        => 'stripe',
                'label'          => 'Stripe Billing',
                'type'           => 'webhook',
                'local_url'      => 'http://localhost:8000/stripe/webhook',
                'production_url' => 'https://unit.report/stripe/webhook',
                'env_keys'       => json_encode(['STRIPE_KEY', 'STRIPE_SECRET', 'STRIPE_WEBHOOK_SECRET']),
                'meta'           => json_encode(['dashboard' => 'https://dashboard.stripe.com/webhooks', 'events' => ['customer.subscription.created', 'customer.subscription.deleted', 'invoice.payment_succeeded', 'invoice.payment_failed']]),
                'notes'          => 'Webhook signing secret (STRIPE_WEBHOOK_SECRET) comes from the Stripe dashboard webhook endpoint — different from the API secret key.',
                'sort_order'     => 20,
            ],

            // ── Platform: AI ──────────────────────────────────────────────────
            [
                'scope'          => 'platform',
                'worker_slug'    => null,
                'service'        => 'anthropic',
                'label'          => 'Anthropic Claude API',
                'type'           => 'api_key',
                'local_url'      => null,
                'production_url' => null,
                'env_keys'       => json_encode(['CLAUDE_API_KEY', 'CLAUDE_MODEL']),
                'meta'           => json_encode(['console' => 'https://console.anthropic.com', 'model' => 'claude-sonnet-4-6']),
                'notes'          => 'Platform-level AI for all workers. Prompt caching enabled (anthropic-beta: prompt-caching-2024-07-31).',
                'sort_order'     => 30,
            ],

            // ── Platform: Email / SMTP ────────────────────────────────────────
            [
                'scope'          => 'platform',
                'worker_slug'    => null,
                'service'        => 'smtp',
                'label'          => 'Platform SMTP (Namecheap)',
                'type'           => 'smtp',
                'local_url'      => null,
                'production_url' => null,
                'env_keys'       => json_encode(['MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_ENCRYPTION', 'MAIL_FROM_ADDRESS']),
                'meta'           => json_encode(['host' => 'premium105.web-hosting.com', 'port' => 465, 'encryption' => 'ssl', 'from' => 'hello@unit.report', 'warning' => 'Do NOT use mail.unit.report — SSL cert mismatch']),
                'notes'          => 'Namecheap shared hosting SMTP. Use actual server hostname (premium105.web-hosting.com), not the domain alias.',
                'sort_order'     => 40,
            ],

            // ── Platform: Error Monitoring ────────────────────────────────────
            [
                'scope'          => 'platform',
                'worker_slug'    => null,
                'service'        => 'flare',
                'label'          => 'Flare Error Monitoring',
                'type'           => 'api_key',
                'local_url'      => null,
                'production_url' => null,
                'env_keys'       => json_encode(['FLARE_KEY']),
                'meta'           => json_encode(['console' => 'https://flareapp.io', 'package' => 'spatie/laravel-flare', 'note' => 'Only activates in production']),
                'notes'          => 'Run: composer require spatie/laravel-flare. Set FLARE_KEY in production .env. Inactive in local/staging.',
                'sort_order'     => 50,
            ],

            // ── AVA: Gmail OAuth (tenant connections) ─────────────────────────
            [
                'scope'          => 'worker',
                'worker_slug'    => 'ava',
                'service'        => 'gmail_oauth',
                'label'          => 'AVA Gmail OAuth',
                'type'           => 'oauth',
                'local_url'      => 'http://localhost:8000/workers/ava/gmail/callback',
                'production_url' => 'https://unit.report/workers/ava/gmail/callback',
                'env_keys'       => json_encode(['GMAIL_CLIENT_ID', 'GMAIL_CLIENT_SECRET', 'GMAIL_REDIRECT_URI']),
                'meta'           => json_encode(['scopes' => ['gmail.modify', 'gmail.readonly'], 'console' => 'https://console.cloud.google.com/apis/credentials', 'stores' => 'user_gmail_credentials (refresh token encrypted at rest)']),
                'notes'          => 'Tenants connect their own Gmail inboxes. Refresh tokens encrypted with Laravel Crypt before storage. Legacy unencrypted tokens supported via fallback in GmailService.',
                'sort_order'     => 100,
            ],

            // ── AVA: Gmail Pub/Sub ────────────────────────────────────────────
            [
                'scope'          => 'worker',
                'worker_slug'    => 'ava',
                'service'        => 'gmail_pubsub',
                'label'          => 'AVA Gmail Pub/Sub Watch',
                'type'           => 'pubsub',
                'local_url'      => 'http://localhost:8000/workers/ava/gmail/webhook',
                'production_url' => 'https://unit.report/workers/ava/gmail/webhook',
                'env_keys'       => json_encode(['GMAIL_PUBSUB_TOPIC']),
                'meta'           => json_encode(['topic' => 'projects/unit-platform/topics/ava-gmail-inbox', 'console' => 'https://console.cloud.google.com/cloudpubsub', 'watch_expires' => '7 days — auto-renewed by scheduler', 'csrf_exempt' => true]),
                'notes'          => 'GCP Pub/Sub push subscription sends new email notifications to the webhook endpoint. CSRF exempt. Gmail watch expires every 7 days — renewed daily by the gmail:check-tokens scheduler. Webhook ingest is in GmailController@webhook.',
                'sort_order'     => 110,
            ],
        ];

        foreach ($integrations as $integration) {
            DB::table('platform_integrations')->insertOrIgnore(array_merge($integration, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}

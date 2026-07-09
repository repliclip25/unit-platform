<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', env('APP_URL', '') . '/auth/google/callback'),
    ],

    'apple' => [
        'client_id'     => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect'      => env('APPLE_REDIRECT_URI', '/auth/apple/callback'),
    ],

    'facebook_pixel_id' => env('FACEBOOK_PIXEL_ID'),
    'gtm_id'            => env('GTM_ID'),

    'claude' => [
        'api_key' => env('CLAUDE_API_KEY'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'kimi' => [
        'api_key' => env('KIMI_API_KEY'),
    ],

    'google_ai' => [
        'api_key' => env('GOOGLE_AI_API_KEY'),
    ],

    'stripe' => [
        'live_key'            => env('STRIPE_LIVE_KEY'),
        'live_secret'         => env('STRIPE_LIVE_SECRET'),
        'live_webhook_secret' => env('STRIPE_LIVE_WEBHOOK_SECRET'),
        'test_key'            => env('STRIPE_TEST_KEY', env('STRIPE_KEY')),
        'test_secret'         => env('STRIPE_TEST_SECRET', env('STRIPE_SECRET')),
        'test_webhook_secret' => env('STRIPE_TEST_WEBHOOK_SECRET', env('STRIPE_WEBHOOK_SECRET')),
    ],

    'unit' => [
        'noreply_email' => env('UNIT_NOREPLY_EMAIL', 'hello@unit.report'),
        'noreply_name'  => env('UNIT_NOREPLY_NAME', 'UNIT'),
        'support_email' => env('UNIT_SUPPORT_EMAIL', 'support@unit.app'),
        'admin_email'   => env('UNIT_ADMIN_EMAIL', 'hello@unit.report'),
    ],

    'gmail' => [
        'client_id'            => env('GMAIL_CLIENT_ID'),
        'client_secret'        => env('GMAIL_CLIENT_SECRET'),
        'redirect_uri'         => env('GMAIL_REDIRECT_URI'),
        'refresh_token'        => env('GMAIL_REFRESH_TOKEN'),
        'address'              => env('AVA_GMAIL_ADDRESS'),
        'handler_email'        => env('AVA_HANDLER_EMAIL'),
        'pubsub_topic'         => env('GMAIL_PUBSUB_TOPIC'),
        'pubsub_service_account' => env('GMAIL_PUBSUB_SERVICE_ACCOUNT'), // e.g. service-{project-number}@gcp-sa-pubsub.iam.gserviceaccount.com
    ],

];

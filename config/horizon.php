<?php

use Illuminate\Support\Str;

return [

    'domain' => env('HORIZON_DOMAIN'),
    'path'   => env('HORIZON_PATH', 'horizon'),
    'driver' => env('QUEUE_CONNECTION', 'redis'),
    'redis'  => [
        'connection' => env('HORIZON_REDIS_CONNECTION', 'default'),
        'queue'      => env('HORIZON_QUEUE_PREFIX', 'horizon'),
    ],

    'prefix' => env('HORIZON_PREFIX', Str::slug(env('APP_NAME', 'unit'), '_') . '_horizon:'),

    'middleware' => ['web', 'auth'],

    'waits' => [
        'redis:fast-track' => 3,   // alert if fast-track queue backs up > 3s
        'redis:ava'        => 60,  // alert if ava queue backs up > 60s
        'redis:default'    => 60,
    ],

    'trim' => [
        'recent'        => 60,
        'pending'       => 60,
        'completed'     => 60,
        'recent_failed' => 10080,
        'failed'        => 10080,
        'monitored'     => 10080,
    ],

    'silenced'         => [],
    'metrics'          => ['trim_snapshots' => ['job' => 24, 'queue' => 24]],
    'fast_termination' => false,
    'memory_limit'     => 256,

    'defaults' => [

        /*
         * fast-track — always-on, always responsive.
         * Serves onboarding fast-track tests and any on-demand pipeline triggers.
         * Fixed process count — no balancing needed, jobs are short-lived.
         */
        'fast-track' => [
            'connection' => 'redis',
            'queue'      => ['fast-track'],
            'balance'    => false,
            'processes'  => 3,
            'tries'      => 2,
            'timeout'    => 60,
        ],

        /*
         * ava — all AVA pipeline jobs across every deployment and tenant.
         * Tenant isolation is at the JOB level (deployment_id + user_id in payload).
         * One queue serves thousands of deployments — scale processes, not queues.
         * auto-balance lets Horizon add workers when queue depth grows.
         */
        'ava' => [
            'connection'   => 'redis',
            'queue'        => ['ava'],
            'balance'      => 'auto',
            'processes'    => 5,
            'minProcesses' => 2,
            'maxProcesses' => 20,
            'tries'        => 3,
            'timeout'      => 120,
        ],

        /*
         * default — notifications, billing events, emails, background cleanup.
         */
        'default' => [
            'connection'   => 'redis',
            'queue'        => ['default'],
            'balance'      => 'simple',
            'processes'    => 2,
            'tries'        => 3,
            'timeout'      => 60,
        ],
    ],

    'environments' => [

        'production' => [
            'fast-track' => [
                'processes'    => 3,
                'minProcesses' => 3,
                'maxProcesses' => 5,
            ],
            'ava' => [
                'processes'    => 10,
                'minProcesses' => 5,
                'maxProcesses' => 50,  // auto-scales as queue depth grows
            ],
            'default' => [
                'processes'    => 3,
                'minProcesses' => 2,
                'maxProcesses' => 10,
            ],
        ],

        'local' => [
            'fast-track' => ['processes' => 2],
            'ava'        => ['processes' => 3, 'balance' => 'simple'],
            'default'    => ['processes' => 1],
        ],
    ],

];

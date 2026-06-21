<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkerSchemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::table('workers')->updateOrInsert(
            ['slug' => 'ava'],
            [
                'name'    => 'AVA — Renewal & Subscription Coordinator',
                'org'     => 'Gmail',
                'channel'     => 'gmail',
                'input_schema'  => json_encode([
                    'external' => [
                        [
                            'source'      => 'Gmail Pub/Sub Webhook',
                            'trigger'     => 'New email received in connected inbox',
                            'fields'      => [
                                'message_id'  => 'string — Gmail message ID',
                                'thread_id'   => 'string — Gmail thread ID',
                                'from'        => 'string — sender email address',
                                'subject'     => 'string — email subject line',
                                'body'        => 'string — plain text email body',
                                'received_at' => 'datetime — timestamp of receipt',
                            ],
                        ],
                    ],
                    'from_workers' => [
                        [
                            'worker_slug' => 'change-order-coordinator',
                            'event'       => 'co.approved',
                            'description' => 'Notifies AVA when a change order is approved so it can watch for related renewal impacts',
                            'fields'      => ['co_number' => 'string', 'client' => 'string', 'amount' => 'number'],
                        ],
                    ],
                ]),
                'output_schema' => json_encode([
                    'external' => [
                        [
                            'destination' => 'Tenant inbox (Laravel Mail)',
                            'event'       => 'Daily summary email sent each evening',
                            'fields'      => [
                                'subject'      => 'string — summary subject line',
                                'body'         => 'string — AI-generated summary',
                                'total'        => 'integer — items processed today',
                                'urgent_count' => 'integer — high/critical priority items',
                            ],
                        ],
                        [
                            'destination' => 'Gmail (via Gmail API)',
                            'event'       => 'Draft created for tenant review',
                            'fields'      => [
                                'to'      => 'string — recipient email',
                                'subject' => 'string — draft subject',
                                'body'    => 'string — AI-generated draft body',
                            ],
                        ],
                    ],
                    'to_workers' => [
                        [
                            'worker_slug' => 'certified-payroll-coordinator',
                            'event'       => 'renewal.logged',
                            'description' => 'Publishes renewal log entry so payroll coordinator can flag workers on expiring certs',
                            'fields'      => [
                                'asset'      => 'string — asset name',
                                'client'     => 'string — matched client',
                                'priority'   => 'string — Critical|High|Medium|Low',
                                'expires_at' => 'date — renewal/expiry date',
                            ],
                        ],
                        [
                            'worker_slug' => 'transmittal-coordinator',
                            'event'       => 'renewal.logged',
                            'description' => 'Notifies transmittal coordinator when a cert renewal is logged that may block an open submittal',
                            'fields'      => [
                                'asset'    => 'string',
                                'client'   => 'string',
                                'priority' => 'string',
                            ],
                        ],
                    ],
                ]),
                'updated_at' => now(),
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AvaMemorySeeder extends Seeder
{
    public function run(): void
    {
        // Register AVA as the first worker
        DB::table('workers')->insertOrIgnore([
            'slug'     => 'ava-renewal-coordinator',
            'name'     => 'AVA',
            'category' => 'Operations',
            'version'  => '1.0',
            'status'   => 'running',
            'manifest' => json_encode([
                'purpose'      => 'Subscription & Renewal Coordinator',
                'human_model'  => 'End',
                'auto_send'    => false,
                'summary_time' => '17:00',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Clients
        $clients = [
            ['name' => 'Acme Inc',          'preferred_style' => 'Professional, concise', 'notes' => 'Ask before renewal'],
            ['name' => 'Bright Dental',     'preferred_style' => 'Friendly, simple',      'notes' => 'Monthly maintenance client'],
            ['name' => 'Northline Services','preferred_style' => 'Executive summary style','notes' => 'Sends approvals by email'],
            ['name' => 'Vertex Solutions',  'preferred_style' => 'Direct, detailed',       'notes' => 'Uses ticket numbers'],
            ['name' => 'Internal',          'preferred_style' => 'Internal',               'notes' => 'Internal assets'],
        ];

        foreach ($clients as $client) {
            DB::table('clients')->insertOrIgnore(array_merge($client, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        $clientIds = DB::table('clients')->pluck('id', 'name');

        // Contacts
        $contacts = [
            ['client' => 'Acme Inc',           'name' => 'John Smith',  'email' => 'john@acme.com',           'phone' => '555-0101', 'is_primary' => true],
            ['client' => 'Bright Dental',      'name' => 'Maria Lopez', 'email' => 'maria@brightdental.com',  'phone' => '555-0102', 'is_primary' => true],
            ['client' => 'Northline Services', 'name' => 'David Chen',  'email' => 'david@northline.com',     'phone' => '555-0103', 'is_primary' => true],
            ['client' => 'Vertex Solutions',   'name' => 'Aisha Green', 'email' => 'aisha@vertex.com',        'phone' => '555-0104', 'is_primary' => true],
        ];

        foreach ($contacts as $c) {
            DB::table('contacts')->insertOrIgnore([
                'client_id'  => $clientIds[$c['client']],
                'name'       => $c['name'],
                'email'      => $c['email'],
                'phone'      => $c['phone'],
                'is_primary' => $c['is_primary'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Assets
        $assets = [
            ['name' => 'example.com',        'type' => 'SSL',     'client' => 'Acme Inc',           'vendor' => 'Cloudflare',       'renewal_date' => '2025-05-28', 'service_owner' => 'Franklin', 'notes' => 'Ask before renewal'],
            ['name' => 'acme.com',           'type' => 'Domain',  'client' => 'Acme Inc',           'vendor' => 'Namecheap',        'renewal_date' => '2025-06-15', 'service_owner' => 'Franklin', 'notes' => 'Renew if approved'],
            ['name' => 'brightdental.com',   'type' => 'Hosting', 'client' => 'Bright Dental',      'vendor' => 'SiteGround',       'renewal_date' => '2025-06-02', 'service_owner' => 'Franklin', 'notes' => 'Annual plan'],
            ['name' => 'northline.io',       'type' => 'SaaS',    'client' => 'Northline Services', 'vendor' => 'Google Workspace', 'renewal_date' => '2025-05-30', 'service_owner' => 'Franklin', 'notes' => '25 seats'],
            ['name' => 'vertexops.com',      'type' => 'Domain',  'client' => 'Vertex Solutions',   'vendor' => 'GoDaddy',          'renewal_date' => '2025-06-20', 'service_owner' => 'Franklin', 'notes' => 'Client pays directly'],
            ['name' => 'padyclub.com',       'type' => 'Domain',  'client' => 'Internal',           'vendor' => 'Namecheap',        'renewal_date' => '2025-07-03', 'service_owner' => 'Franklin', 'notes' => 'Internal asset'],
        ];

        foreach ($assets as $a) {
            DB::table('assets')->insertOrIgnore([
                'name'         => $a['name'],
                'type'         => $a['type'],
                'client_id'    => $clientIds[$a['client']],
                'vendor'       => $a['vendor'],
                'renewal_date' => $a['renewal_date'],
                'service_owner'=> $a['service_owner'],
                'notes'        => $a['notes'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // AVA Rules
        $rules = [
            ['rule_id' => 'AVA-001', 'condition' => 'SSL expires in <= 15 days',       'priority' => 'High',     'action' => 'Log + draft client approval email + notify Franklin',           'approval_required' => true,  'notes' => 'Never auto-send'],
            ['rule_id' => 'AVA-002', 'condition' => 'Domain expires in <= 30 days',    'priority' => 'Medium',   'action' => 'Log + draft renewal reminder',                                  'approval_required' => true,  'notes' => 'If internal asset, notify only'],
            ['rule_id' => 'AVA-003', 'condition' => 'Hosting invoice due <= 7 days',   'priority' => 'High',     'action' => 'Log + draft payment reminder',                                  'approval_required' => true,  'notes' => 'Check client owner'],
            ['rule_id' => 'AVA-004', 'condition' => 'SaaS renewal notice',             'priority' => 'Medium',   'action' => 'Log + summarize cost and due date',                             'approval_required' => false, 'notes' => 'Notify Franklin'],
            ['rule_id' => 'AVA-005', 'condition' => 'Failed payment',                  'priority' => 'Critical', 'action' => 'Log + draft urgent message + notify Franklin immediately',      'approval_required' => true,  'notes' => 'Flag as urgent'],
            ['rule_id' => 'AVA-006', 'condition' => 'Low confidence memory match',     'priority' => 'High',     'action' => 'Do not draft. Ask Franklin to confirm client/asset.',           'approval_required' => true,  'notes' => 'Human clarification required'],
        ];

        foreach ($rules as $rule) {
            DB::table('ava_rules')->insertOrIgnore(array_merge($rule, [
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

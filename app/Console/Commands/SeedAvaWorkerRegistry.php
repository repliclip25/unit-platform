<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedAvaWorkerRegistry extends Command
{
    protected $signature   = 'workers:seed-ava';
    protected $description = 'Seed AVA worker DNA into worker_registry table';

    public function handle(): void
    {
        $exists = DB::table('worker_registry')->where('slug', 'ava')->exists();
        if ($exists) {
            if (!$this->confirm('AVA already exists in worker_registry. Overwrite?', false)) {
                $this->info('Skipped.');
                return;
            }
            DB::table('worker_registry')->where('slug', 'ava')->delete();
        }

        // Pipeline stages — merging pipelineStages() with prompts() data
        // Add branches key to all stages (empty = linear)
        $stages = [
            [
                'key'           => 'webhook',
                'label'         => 'Inject & Fetch',
                'sub'           => 'Insert into inbox, read back',
                'job_class'     => '',
                'icon'          => 'bolt',
                'uses_ai'       => false,
                'model'         => null,
                'system_prompt' => '',
                'user_prompt'   => '',
                'output_format' => 'json',
                'output_shape'  => '',
                'max_tokens'    => 0,
                'branches'      => [],
            ],
            [
                'key'           => 'read_email',
                'label'         => 'Read Email',
                'sub'           => 'Parse & extract fields',
                'job_class'     => 'ReadEmailJob',
                'icon'          => 'mail',
                'uses_ai'       => true,
                'model'         => null,
                'system_prompt' => "You are Ava, UNIT's Subscription & Renewal Coordinator. Return valid JSON only. No extra text.",
                'user_prompt'   => "Read the email below and explain what it means.\n\nReturn valid JSON only with:\n{\n  \"plain_english_summary\": \"\",\n  \"what_happened\": \"\",\n  \"action_needed\": \"\",\n  \"due_date_or_deadline\": \"\",\n  \"risk_if_ignored\": \"\",\n  \"urgency\": \"Low|Medium|High|Critical\",\n  \"questions_for_memory_lookup\": []\n}\n\nEMAIL:\n{RAW_EMAIL}",
                'output_format' => 'json',
                'output_shape'  => '{"plain_english_summary":"string","what_happened":"string","action_needed":"string","due_date_or_deadline":"string","risk_if_ignored":"string","urgency":"string","questions_for_memory_lookup":"array"}',
                'max_tokens'    => 512,
                'branches'      => [],
            ],
            [
                'key'           => 'classify',
                'label'         => 'Classify',
                'sub'           => 'Category, priority & type',
                'job_class'     => 'ClassifyEmailJob',
                'icon'          => 'tag',
                'uses_ai'       => true,
                'model'         => null,
                'system_prompt' => "You are Ava, UNIT's Subscription & Renewal Coordinator. Return valid JSON only. No extra text.",
                'user_prompt'   => "Classify this transaction using the email understanding below.\n\nAvailable categories: Domain Renewal, SSL Expiry, Hosting Invoice, SaaS Renewal, Failed Payment, Security Alert, Meeting Request, Client Support, Other\n\nReturn JSON:\n{\n  \"category\": \"\",\n  \"subcategory\": \"\",\n  \"priority\": \"Low|Medium|High|Critical\",\n  \"required_action\": \"\",\n  \"register_to_update\": \"\",\n  \"status\": \"\",\n  \"reason\": \"\"\n}\n\nCONTEXT:\n{READ_OUTPUT}",
                'output_format' => 'json',
                'output_shape'  => '{"category":"string","subcategory":"string","priority":"string","required_action":"string","register_to_update":"string","status":"string","reason":"string"}',
                'max_tokens'    => 256,
                'branches'      => [],
            ],
            [
                'key'           => 'memory',
                'label'         => 'Memory Lookup',
                'sub'           => 'Match client, asset & rules',
                'job_class'     => 'MemoryLookupJob',
                'icon'          => 'brain',
                'uses_ai'       => true,
                'model'         => null,
                'system_prompt' => "You are Ava, UNIT's Subscription & Renewal Coordinator. Return valid JSON only. No extra text.",
                'user_prompt'   => "Using the extracted email information and the memory tables below, find who owns this asset and how it should be handled.\n\nReturn JSON:\n{\n  \"asset\": \"\",\n  \"matched_client\": \"\",\n  \"primary_contact_name\": \"\",\n  \"primary_contact_email\": \"\",\n  \"related_project_or_service\": \"\",\n  \"client_preference\": \"\",\n  \"ava_rule\": \"\",\n  \"confidence\": 0,\n  \"missing_information\": []\n}\n\nEXTRACTED EMAIL CONTEXT:\n{READ_OUTPUT}\n\nMEMORY TABLES:\n{MEMORY_TABLES}",
                'output_format' => 'json',
                'output_shape'  => '{"asset":"string","matched_client":"string","primary_contact_name":"string","primary_contact_email":"string","related_project_or_service":"string","client_preference":"string","ava_rule":"string","confidence":"float","missing_information":"array"}',
                'max_tokens'    => 768,
                'branches'      => [],
            ],
            [
                'key'           => 'log_entry',
                'label'         => 'Log Transaction',
                'sub'           => 'Write to register',
                'job_class'     => 'LogTransactionJob',
                'icon'          => 'log',
                'uses_ai'       => false,
                'model'         => null,
                'system_prompt' => '',
                'user_prompt'   => '',
                'output_format' => 'json',
                'output_shape'  => '',
                'max_tokens'    => 0,
                'branches'      => [],
            ],
            [
                'key'           => 'select_template',
                'label'         => 'Select Template',
                'sub'           => 'Pick best-match template',
                'job_class'     => 'SelectTemplateJob',
                'icon'          => 'template',
                'uses_ai'       => false,
                'model'         => null,
                'system_prompt' => '',
                'user_prompt'   => '',
                'output_format' => 'json',
                'output_shape'  => '',
                'max_tokens'    => 0,
                'branches'      => [],
            ],
            [
                'key'           => 'draft_email',
                'label'         => 'Draft Email',
                'sub'           => 'AI-personalised draft',
                'job_class'     => 'DraftEmailJob',
                'icon'          => 'draft',
                'uses_ai'       => true,
                'model'         => null,
                'system_prompt' => 'You are Ava, a professional email coordinator. Return only the email body — no subject line, no JSON, no extra text.',
                'user_prompt'   => "Write an email body using the template structure below.\n\nTemplate style: {TEMPLATE_NAME}\nTone: {TONE}\nTemplate body to follow:\n{BODY_TEMPLATE}\n\nFill in:\n- Contact first name: {FIRST_NAME}\n- Asset: {ASSET}\n- Client: {CLIENT}\n- Due date: {DUE_DATE}\n- Category: {CATEGORY}\n- Approval required: {APPROVAL_REQUIRED}\n- Sign as: {SENDER_NAME}\n\nRules:\n- Keep it concise\n- Do not promise work is done\n- Ask for approval when required\n- Return only the email body",
                'output_format' => 'text',
                'output_shape'  => 'Professional email body addressed to the contact, referencing the asset and due date, signed by the tenant.',
                'max_tokens'    => 1024,
                'branches'      => [],
            ],
            [
                'key'           => 'push_draft',
                'label'         => 'Push to Gmail',
                'sub'           => 'Create draft in inbox',
                'job_class'     => 'PushToGmailJob',
                'icon'          => 'send',
                'uses_ai'       => false,
                'model'         => null,
                'system_prompt' => '',
                'user_prompt'   => '',
                'output_format' => 'json',
                'output_shape'  => '',
                'max_tokens'    => 0,
                'branches'      => [],
            ],
        ];

        $qa = [
            ['stage' => 'read',     'check' => 'OUTPUT_NOT_EMPTY', 'label' => 'Email parsed successfully',        'field' => null,       'threshold' => null, 'values' => []],
            ['stage' => 'classify', 'check' => 'FIELD_NOT_NULL',   'label' => 'Email category resolved',          'field' => 'category', 'threshold' => null, 'values' => []],
            ['stage' => 'classify', 'check' => 'VALUE_ABOVE',      'label' => 'Classification confidence ≥ 40%',  'field' => 'confidence','threshold' => 0.4, 'values' => []],
            ['stage' => 'memory',   'check' => 'OUTPUT_NOT_EMPTY', 'label' => 'Memory lookup completed',          'field' => null,       'threshold' => null, 'values' => []],
            ['stage' => 'template', 'check' => 'FIELD_NOT_NULL',   'label' => 'Template selected',                'field' => 'template_id','threshold' => null,'values' => []],
            ['stage' => 'draft',    'check' => 'FIELD_NOT_EMPTY',  'label' => 'Draft body generated',             'field' => 'body',     'threshold' => null, 'values' => []],
            ['stage' => 'draft',    'check' => 'VALID_EMAIL',      'label' => 'Valid recipient resolved',          'field' => 'to',       'threshold' => null, 'values' => []],
            ['stage' => 'push',     'check' => 'STATUS_IN',        'label' => 'Draft pushed to Gmail',            'field' => 'status',   'threshold' => null, 'values' => ['draft_ready', 'sent']],
        ];

        DB::table('worker_registry')->insert([
            'name'                  => 'AVA Email Worker',
            'slug'                  => 'ava',
            'version'               => '1.0',
            'description'           => 'Monitors your Gmail inbox, classifies renewal and subscription emails, and drafts responses using your contacts, assets, and rules.',
            'status'                => 'published',
            'org'                   => json_encode([
                'name'         => 'Gmail',
                'abbreviation' => null,
                'type'         => 'platform',
                'website'      => 'https://mail.google.com',
                'logo'         => 'gmail',
            ]),
            'pipeline_stages'       => json_encode($stages),
            'qa_requirements'       => json_encode($qa),
            'credential'            => json_encode([
                [
                    'key'             => 'gmail_inbox',
                    'type'            => 'gmail_oauth',
                    'label'           => 'Gmail Account',
                    'hint'            => 'The inbox AVA will monitor for renewal and subscription emails.',
                    'required'        => true,
                    'multiple'        => true,
                    'connect_route'   => 'ava.connect',
                    'authorize_route' => 'ava.gmail.authorize',
                ],
            ]),
            'instances'             => json_encode([
                'multiple'  => true,
                'min'       => 1,
                'max'       => null,
                'label'     => 'inbox',
                'rationale' => 'Each AVA instance monitors one Gmail inbox independently — deploy one per email account you want covered.',
            ]),
            'deployment_fields'     => json_encode([
                ['key' => 'capture_scope',    'label' => 'Capture Scope',    'type' => 'text', 'placeholder' => 'e.g. Renewal and subscription emails', 'default' => 'All incoming emails', 'hint' => 'Describe what type of emails this worker should process.'],
                ['key' => 'capture_keywords', 'label' => 'Capture Keywords', 'type' => 'text', 'placeholder' => 'renew, invoice, expires, subscription',  'default' => '',                    'hint' => 'Comma-separated keywords. Leave blank to capture all emails.'],
            ]),
            'train_schema'          => json_encode([
                ['key' => 'clients',   'label' => 'Clients',         'description' => 'Company or individual names AVA should recognise when reading emails.',                          'required' => false, 'format_hint' => 'CSV upload or manual entry'],
                ['key' => 'contacts',  'label' => 'Contacts',        'description' => 'People AVA addresses in drafted emails — name, email, role.',                                   'required' => false, 'format_hint' => 'CSV upload or manual entry'],
                ['key' => 'assets',    'label' => 'Assets',          'description' => 'Domains, SSL certificates, SaaS subscriptions — anything with a renewal date.',                'required' => false, 'format_hint' => 'CSV upload or manual entry'],
                ['key' => 'rules',     'label' => 'AVA Rules',       'description' => 'Natural-language instructions AVA follows when drafting.',                                       'required' => false, 'format_hint' => 'Manual entry'],
                ['key' => 'templates', 'label' => 'Email Templates', 'description' => 'Draft templates AVA selects from based on email category.',                                     'required' => false, 'format_hint' => 'Edit from platform defaults'],
            ]),
            'tags'                  => json_encode(['email', 'gmail', 'renewal', 'subscription', 'domain', 'ssl', 'invoice', 'inbox', 'draft', 'automation', 'coordinator']),
            'owner'                 => json_encode([
                'type'     => 'platform',
                'name'     => 'UNIT',
                'contact'  => 'hello@unit.report',
                'website'  => 'https://unit.report',
                'license'  => 'proprietary',
                'sla'      => '99.9% pipeline uptime · 4h support response · daily digest on failures',
                'since'    => 2024,
                'verified' => true,
            ]),
            'media'                 => json_encode([
                'color'  => '#f3c531',
                'quote'  => "Methodical, thorough, never misses a deadline. I watch your inbox so you don't have to — and I draft everything before the agency ever has to ask twice.",
                'avatar' => '/workers/ava/avatar.png',
                'banner' => '/workers/ava/banner.jpg',
            ]),
            'notifications'         => json_encode([
                ['key' => 'drafts_awaiting_review', 'level' => 'info',    'query' => 'tx_draft_ready_undecided', 'trigger' => ['operator' => '>', 'value' => 0], 'message' => '{count} draft{plural} awaiting your review', 'action_label' => 'Review', 'action_route' => 'transactions'],
                ['key' => 'urgent_unresolved',      'level' => 'warning', 'query' => 'tx_urgent_open',          'trigger' => ['operator' => '>', 'value' => 0], 'message' => '{count} urgent item{plural} with no action taken', 'action_label' => 'View', 'action_route' => 'transactions'],
                ['key' => 'failed_today',           'level' => 'error',   'query' => 'tx_failed_today',         'trigger' => ['operator' => '>', 'value' => 0], 'message' => '{count} transaction{plural} failed today', 'action_label' => 'Inspect', 'action_route' => 'transactions'],
            ]),
            'subscriptions'         => json_encode([]),
            'version_changelog'     => json_encode([
                [
                    'version'         => '1.0',
                    'date'            => '2024-01-01',
                    'notes'           => 'Initial release. Gmail watch, 8-stage pipeline, renewal register, memory layers.',
                    'breaking'        => false,
                    'breaking_reason' => '',
                    'upgrade_steps'   => [],
                ],
            ]),
            'folder_path'           => 'app/Workers/AVA',
            'scaffold_generated_at' => null,
            'published_at'          => now(),
            'created_by'            => null,
            'updated_by'            => null,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        $this->info('✓ AVA worker seeded into worker_registry (status: published).');
        $this->line('  → Edit at /admin/workers/ava/edit');
    }
}

<?php

namespace App\Workers\AVA;

use App\Platform\Contracts\WorkerContract;
use App\Platform\Enums\QACheck;
use App\Workers\AVA\Jobs\ClassifyEmailJob;
use App\Workers\AVA\Jobs\DraftEmailJob;
use App\Workers\AVA\Jobs\MemoryLookupJob;
use App\Workers\AVA\Jobs\PushToGmailJob;
use App\Workers\AVA\Jobs\ReadEmailJob;
use App\Workers\AVA\Jobs\SelectTemplateJob;

class AvaWorker implements WorkerContract
{
    // ── Block 1: Identity ────────────────────────────────────────────────────

    public function identity(): array
    {
        return [
            'name'        => 'AVA Email Worker',
            'slug'        => 'ava',
            'version'     => '1.0',
            'description' => 'Monitors your Gmail inbox, classifies renewal and subscription emails, and drafts responses using your contacts, assets, and rules.',
        ];
    }

    public function org(): array
    {
        return [
            'name'         => 'Gmail',
            'abbreviation' => null,
            'type'         => 'platform',
            'website'      => 'https://mail.google.com',
            'logo'         => 'gmail',
        ];
    }

    public function demoPayload(): array
    {
        return [
            'source'     => 'public_demo',
            'message_id' => 'demo_' . substr(md5('ava-demo'), 0, 12),
            'subject'    => 'Domain Renewal Notice — yourdomain.com expires in 30 days',
            'from'       => 'noreply@registrar-demo.com',
            'date'       => now()->toRfc2822String(),
            'raw_email'  => implode("\n", [
                'From: Domain Registrar <noreply@registrar-demo.com>',
                'Subject: Domain Renewal Notice — yourdomain.com expires in 30 days',
                'Date: ' . now()->toRfc2822String(),
                '',
                'Dear Customer,',
                '',
                'This is a reminder that yourdomain.com is due for renewal on '
                    . now()->addDays(30)->format('F j, Y') . '.',
                '',
                'Please log in to your account to renew before it expires.',
                '',
                'Regards,',
                'The Registrar Team',
            ]),
        ];
    }

    // ── Block 2: Deployment DNA ──────────────────────────────────────────────

    public function instances(): array
    {
        return [
            'multiple'  => true,
            'min'       => 1,
            'max'       => null,
            'label'     => 'inbox',
            'rationale' => 'Each AVA instance monitors one Gmail inbox independently — deploy one per email account you want covered.',
        ];
    }

    public function credential(): array
    {
        return [
            'type'            => 'gmail_oauth',
            'label'           => 'Gmail Account',
            'hint'            => 'The inbox AVA will monitor for renewal and subscription emails.',
            'multiple'        => true,
            'connect_route'   => 'ava.connect',
            'authorize_route' => 'ava.gmail.authorize',
        ];
    }

    public function deploymentFields(): array
    {
        return [
            [
                'key'         => 'capture_scope',
                'label'       => 'Capture Scope',
                'type'        => 'text',
                'placeholder' => 'e.g. Renewal and subscription emails',
                'default'     => 'All incoming emails',
                'hint'        => 'Describe what type of emails this worker should process.',
            ],
            [
                'key'         => 'capture_keywords',
                'label'       => 'Capture Keywords',
                'type'        => 'text',
                'placeholder' => 'renew, invoice, expires, subscription',
                'default'     => '',
                'hint'        => 'Comma-separated keywords. Leave blank to capture all emails.',
            ],
        ];
    }

    public function trainSchema(): array
    {
        return [
            [
                'key'         => 'clients',
                'label'       => 'Clients',
                'description' => 'Company or individual names AVA should recognise when reading emails. Used to match incoming emails to the right client record.',
                'required'    => false,
                'format_hint' => 'CSV upload or manual entry',
            ],
            [
                'key'         => 'contacts',
                'label'       => 'Contacts',
                'description' => 'People AVA addresses in drafted emails — name, email, role. Matched against incoming sender and client records.',
                'required'    => false,
                'format_hint' => 'CSV upload or manual entry',
            ],
            [
                'key'         => 'assets',
                'label'       => 'Assets',
                'description' => 'Domains, SSL certificates, SaaS subscriptions — anything with a renewal date. AVA uses these to identify what an email is about.',
                'required'    => false,
                'format_hint' => 'CSV upload or manual entry',
            ],
            [
                'key'         => 'rules',
                'label'       => 'AVA Rules',
                'description' => 'Natural-language instructions AVA follows when drafting. E.g. "Always CC accounts@company.com on SSL renewal emails."',
                'required'    => false,
                'format_hint' => 'Manual entry',
            ],
            [
                'key'         => 'templates',
                'label'       => 'Email Templates',
                'description' => 'Draft templates AVA selects from based on email category. Customise platform defaults or write your own.',
                'required'    => false,
                'format_hint' => 'Edit from platform defaults',
            ],
        ];
    }

    public function fastTrack(): array
    {
        return [
            'source'     => 'fast_track_test',
            'message_id' => 'ft_' . substr(md5('ava-fast-track'), 0, 12),
            'subject'    => '[Fast Track] Domain Renewal Notice — yourdomain.com expires in 30 days',
            'from'       => 'noreply@registrar.com',
            'date'       => now()->toRfc2822String(),
            'raw_email'  => implode("\n", [
                'From: Domain Registrar <noreply@registrar.com>',
                'Subject: Domain Renewal Notice — yourdomain.com expires in 30 days',
                'Date: ' . now()->toRfc2822String(),
                '',
                'Dear Customer,',
                '',
                'This is a reminder that yourdomain.com is due for renewal on '
                    . now()->addDays(30)->format('F j, Y') . '.',
                '',
                'Please log in to your account to renew before it expires.',
                '',
                'Regards,',
                'The Registrar Team',
            ]),
        ];
    }

    // ── Block 3: Pipeline ────────────────────────────────────────────────────

    public function input(): array
    {
        return [
            'description' => 'Incoming Gmail message delivered via Google Pub/Sub webhook',
            'source'      => 'Gmail Pub/Sub webhook',
            'fields'      => [
                ['key' => 'message_id', 'type' => 'string', 'description' => 'Gmail message ID',                     'required' => true],
                ['key' => 'subject',    'type' => 'string', 'description' => 'Email subject line',                   'required' => true],
                ['key' => 'from',       'type' => 'string', 'description' => 'Sender address',                       'required' => true],
                ['key' => 'date',       'type' => 'string', 'description' => 'Email date header (RFC 2822)',          'required' => true],
                ['key' => 'raw_email',  'type' => 'string', 'description' => 'Full plain-text email body with headers','required' => true],
            ],
        ];
    }

    public function pipeline(): array
    {
        $total = 6;
        return [
            [
                'stage'        => 1,
                'total'        => $total,
                'job'          => ReadEmailJob::class,
                'label'        => 'Read Email',
                'receives_from'=> 'input',
                'accepts'      => [
                    ['key' => 'message_id', 'type' => 'string', 'description' => 'Gmail message ID'],
                    ['key' => 'subject',    'type' => 'string', 'description' => 'Email subject line'],
                    ['key' => 'from',       'type' => 'string', 'description' => 'Sender address'],
                    ['key' => 'raw_email',  'type' => 'string', 'description' => 'Full email body'],
                ],
                'produces'     => [
                    ['key' => 'sender',          'type' => 'string', 'description' => 'Resolved sender name and address'],
                    ['key' => 'subject',         'type' => 'string', 'description' => 'Cleaned subject line'],
                    ['key' => 'body',            'type' => 'string', 'description' => 'Extracted email body'],
                    ['key' => 'intent_signals',  'type' => 'array',  'description' => 'Keywords and signals extracted for classification'],
                    ['key' => 'detected_domains','type' => 'array',  'description' => 'Domains found in the email body'],
                ],
                'connects_to'  => ClassifyEmailJob::class,
                'can_emit'     => ['discovery.domain_detected'],
            ],
            [
                'stage'        => 2,
                'total'        => $total,
                'job'          => ClassifyEmailJob::class,
                'label'        => 'Classify Email',
                'receives_from'=> 'Read Email',
                'accepts'      => [
                    ['key' => 'sender',         'type' => 'string', 'description' => 'Sender from read stage'],
                    ['key' => 'subject',        'type' => 'string', 'description' => 'Subject from read stage'],
                    ['key' => 'body',           'type' => 'string', 'description' => 'Body from read stage'],
                    ['key' => 'intent_signals', 'type' => 'array',  'description' => 'Signals from read stage'],
                ],
                'produces'     => [
                    ['key' => 'category',        'type' => 'string', 'description' => 'Primary category e.g. domain_renewal, ssl_renewal'],
                    ['key' => 'subcategory',     'type' => 'string', 'description' => 'Subcategory for finer routing'],
                    ['key' => 'priority',        'type' => 'string', 'description' => 'urgent | normal | low'],
                    ['key' => 'required_action', 'type' => 'string', 'description' => 'What action the email requires'],
                    ['key' => 'confidence',      'type' => 'float',  'description' => 'Classification confidence 0–1'],
                ],
                'connects_to'  => MemoryLookupJob::class,
                'can_emit'     => [],
            ],
            [
                'stage'        => 3,
                'total'        => $total,
                'job'          => MemoryLookupJob::class,
                'label'        => 'Memory Lookup',
                'receives_from'=> 'Classify Email',
                'accepts'      => [
                    ['key' => 'category',   'type' => 'string', 'description' => 'Classification from classify stage'],
                    ['key' => 'sender',     'type' => 'string', 'description' => 'Sender from read stage'],
                    ['key' => 'body',       'type' => 'string', 'description' => 'Body from read stage'],
                ],
                'produces'     => [
                    ['key' => 'matched_client',           'type' => 'string', 'description' => 'Client name matched from memory'],
                    ['key' => 'primary_contact_name',     'type' => 'string', 'description' => 'Contact name to address in draft'],
                    ['key' => 'primary_contact_email',    'type' => 'string', 'description' => 'Contact email for the To field'],
                    ['key' => 'asset',                    'type' => 'string', 'description' => 'Asset name the email concerns'],
                    ['key' => 'ava_rule',                 'type' => 'string', 'description' => 'Matching rule from tenant rules'],
                    ['key' => 'confidence',               'type' => 'float',  'description' => 'Memory match confidence 0–1'],
                    ['key' => 'related_project_or_service','type' => 'string','description' => 'Related project or service if found'],
                    ['key' => 'client_preference',        'type' => 'string', 'description' => 'Known client communication preference'],
                ],
                'connects_to'  => SelectTemplateJob::class,
                'can_emit'     => [
                    'discovery.client_unknown',
                    'discovery.asset_unknown',
                    'discovery.contact_placeholder',
                ],
            ],
            [
                'stage'        => 4,
                'total'        => $total,
                'job'          => SelectTemplateJob::class,
                'label'        => 'Select Template',
                'receives_from'=> 'Memory Lookup',
                'accepts'      => [
                    ['key' => 'category',  'type' => 'string', 'description' => 'Category from classify stage'],
                    ['key' => 'templates', 'type' => 'array',  'description' => 'Tenant templates from memory'],
                    ['key' => 'rules',     'type' => 'array',  'description' => 'Tenant rules from memory'],
                ],
                'produces'     => [
                    ['key' => 'template_id',       'type' => 'integer', 'description' => 'Selected template DB id'],
                    ['key' => 'template_name',     'type' => 'string',  'description' => 'Template name for logging'],
                    ['key' => 'approval_required', 'type' => 'boolean', 'description' => 'Whether draft requires human review before sending'],
                ],
                'connects_to'  => DraftEmailJob::class,
                'can_emit'     => [],
            ],
            [
                'stage'        => 5,
                'total'        => $total,
                'job'          => DraftEmailJob::class,
                'label'        => 'Draft Email',
                'receives_from'=> 'Select Template',
                'accepts'      => [
                    ['key' => 'template_id', 'type' => 'integer', 'description' => 'Template from select stage'],
                    ['key' => 'memory',      'type' => 'array',   'description' => 'Full memory context for variable substitution'],
                ],
                'produces'     => [
                    ['key' => 'to',                'type' => 'string',  'description' => 'Resolved recipient email'],
                    ['key' => 'subject',           'type' => 'string',  'description' => 'Drafted subject line'],
                    ['key' => 'body',              'type' => 'string',  'description' => 'Drafted email body'],
                    ['key' => 'low_confidence',    'type' => 'boolean', 'description' => 'True if AVA was uncertain about context'],
                    ['key' => 'human_review_note', 'type' => 'string',  'description' => 'Note to reviewer when low_confidence is true'],
                ],
                'connects_to'  => PushToGmailJob::class,
                'can_emit'     => [],
            ],
            [
                'stage'        => 6,
                'total'        => $total,
                'job'          => PushToGmailJob::class,
                'label'        => 'Push to Gmail',
                'receives_from'=> 'Draft Email',
                'accepts'      => [
                    ['key' => 'to',               'type' => 'string',  'description' => 'Recipient from draft stage'],
                    ['key' => 'subject',          'type' => 'string',  'description' => 'Subject from draft stage'],
                    ['key' => 'body',             'type' => 'string',  'description' => 'Body from draft stage'],
                    ['key' => 'approval_required','type' => 'boolean', 'description' => 'From template stage'],
                ],
                'produces'     => [
                    ['key' => 'gmail_draft_id', 'type' => 'string', 'description' => 'Gmail draft ID created via API'],
                    ['key' => 'to',             'type' => 'string', 'description' => 'Final recipient used'],
                    ['key' => 'status',         'type' => 'string', 'description' => 'draft_ready | sent | suppressed'],
                    ['key' => 'auto_sent',      'type' => 'boolean','description' => 'True if sent without human review'],
                ],
                'connects_to'  => null,
                'can_emit'     => ['renewal.draft_ready'],
            ],
        ];
    }

    public function emit(): array
    {
        return [
            [
                'event'       => 'discovery.domain_detected',
                'fired_from'  => 'Read Email',
                'description' => 'A domain was found in the email body that does not exist in the tenant\'s assets table.',
                'reusable'    => true,
                'fields'      => [
                    ['key' => 'domain',     'type' => 'string', 'description' => 'The detected domain name'],
                    ['key' => 'source_email','type' => 'string','description' => 'The email the domain was found in'],
                ],
            ],
            [
                'event'       => 'discovery.client_unknown',
                'fired_from'  => 'Memory Lookup',
                'description' => 'An organisation was referenced in the email that has no matching client record in memory.',
                'reusable'    => true,
                'fields'      => [
                    ['key' => 'name',       'type' => 'string', 'description' => 'Detected organisation name'],
                    ['key' => 'confidence', 'type' => 'float',  'description' => 'How confident AVA is in the detection'],
                    ['key' => 'context',    'type' => 'string', 'description' => 'Sentence in the email where the org was mentioned'],
                ],
            ],
            [
                'event'       => 'discovery.asset_unknown',
                'fired_from'  => 'Memory Lookup',
                'description' => 'An asset (domain, SSL cert, subscription) was mentioned that is not in the tenant\'s assets table.',
                'reusable'    => true,
                'fields'      => [
                    ['key' => 'name',       'type' => 'string', 'description' => 'Detected asset name'],
                    ['key' => 'type_hint',  'type' => 'string', 'description' => 'Probable asset type inferred from context'],
                    ['key' => 'expiry_hint','type' => 'string', 'description' => 'Expiry date if detectable from email'],
                ],
            ],
            [
                'event'       => 'discovery.contact_placeholder',
                'fired_from'  => 'Memory Lookup',
                'description' => 'A contact was partially resolved — enough to draft to, but fields are incomplete. Placeholder surfaced for human review and memory enrichment.',
                'reusable'    => true,
                'fields'      => [
                    ['key' => 'name',       'type' => 'string', 'description' => 'Contact name if detected'],
                    ['key' => 'email',      'type' => 'string', 'description' => 'Contact email if detected'],
                    ['key' => 'missing',    'type' => 'array',  'description' => 'Field names that could not be resolved'],
                ],
            ],
            [
                'event'       => 'renewal.draft_ready',
                'fired_from'  => 'Push to Gmail',
                'description' => 'Full handover packet emitted when AVA creates a Gmail draft. Downstream workers subscribe to this for billing, CRM sync, reporting, etc.',
                'reusable'    => true,
                'fields'      => [
                    ['key' => 'draft',          'type' => 'object', 'description' => 'Draft details: gmail_draft_id, subject, to, status, fast_track, low_confidence, review_note, created_at'],
                    ['key' => 'asset',          'type' => 'object', 'description' => 'Asset context: name, type, registrar, expiry, days_left'],
                    ['key' => 'client',         'type' => 'object', 'description' => 'Client context: name, account'],
                    ['key' => 'contact',        'type' => 'object', 'description' => 'Contact context: name, email, phone, role'],
                    ['key' => 'service',        'type' => 'object', 'description' => 'Service context: related_project, client_preference, ava_rule'],
                    ['key' => 'classification', 'type' => 'object', 'description' => 'Classification: category, subcategory, priority, action'],
                    ['key' => 'ava',            'type' => 'object', 'description' => 'AVA metadata: confidence, draft_ready_at'],
                ],
            ],
        ];
    }

    public function commit(): ?array
    {
        // AVA is a fully autonomous pipeline — it does not accept external injections.
        // Future workers that pause mid-pipeline for human or worker input will return
        // a commit schema here.
        return null;
    }

    // ── Block 3b: Notifications ─────────────────────────────────────────────

    public function notifications(): array
    {
        return [
            [
                'key'          => 'drafts_awaiting_review',
                'level'        => 'info',
                'query'        => 'tx_draft_ready_undecided',
                'trigger'      => ['operator' => '>', 'value' => 0],
                'message'      => '{count} draft{plural} awaiting your review',
                'action_label' => 'Review',
                'action_route' => 'transactions',
                'action_params'=> ['filter' => 'draft_ready'],
            ],
            [
                'key'          => 'urgent_unresolved',
                'level'        => 'warning',
                'query'        => 'tx_urgent_open',
                'trigger'      => ['operator' => '>', 'value' => 0],
                'message'      => '{count} urgent item{plural} with no action taken',
                'action_label' => 'View',
                'action_route' => 'transactions',
                'action_params'=> ['filter' => 'urgent'],
            ],
            [
                'key'          => 'failed_today',
                'level'        => 'error',
                'query'        => 'tx_failed_today',
                'trigger'      => ['operator' => '>', 'value' => 0],
                'message'      => '{count} transaction{plural} failed today',
                'action_label' => 'Inspect',
                'action_route' => 'transactions',
                'action_params'=> ['filter' => 'failed'],
            ],
        ];
    }

    // ── Block 3d: Dashboard Surface ─────────────────────────────────────────

    public function dashboard(): array
    {
        return [
            'accent' => 'violet',
            'icon'   => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'stats'  => [
                ['key' => 'tx_draft_ready', 'label' => 'Drafts Ready'],
                ['key' => 'tx_urgent',      'label' => 'Urgent'],
                ['key' => 'tx_today',       'label' => 'Today'],
            ],
        ];
    }

    // ── Block 4: Quality ────────────────────────────────────────────────────

    public function qaRequirements(): array
    {
        return [
            [
                'stage' => 'read',
                'check' => QACheck::OUTPUT_NOT_EMPTY,
                'label' => 'Email parsed successfully',
            ],
            [
                'stage' => 'classify',
                'check' => QACheck::FIELD_NOT_NULL,
                'field' => 'category',
                'label' => 'Email category resolved',
            ],
            [
                'stage' => 'classify',
                'check' => QACheck::VALUE_ABOVE,
                'field' => 'confidence',
                'threshold' => 0.4,
                'label' => 'Classification confidence ≥ 40%',
            ],
            [
                'stage' => 'memory',
                'check' => QACheck::OUTPUT_NOT_EMPTY,
                'label' => 'Memory lookup completed',
            ],
            [
                'stage' => 'template',
                'check' => QACheck::FIELD_NOT_NULL,
                'field' => 'template_id',
                'label' => 'Template selected',
            ],
            [
                'stage' => 'draft',
                'check' => QACheck::FIELD_NOT_EMPTY,
                'field' => 'body',
                'label' => 'Draft body generated',
            ],
            [
                'stage' => 'draft',
                'check' => QACheck::VALID_EMAIL,
                'field' => 'to',
                'label' => 'Valid recipient resolved',
            ],
            [
                'stage' => 'push',
                'check' => QACheck::STATUS_IN,
                'field' => 'status',
                'values' => ['draft_ready', 'sent'],
                'label' => 'Draft pushed to Gmail',
            ],
        ];
    }
}

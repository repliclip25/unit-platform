<?php

namespace App\Workers\AVA;

use App\Platform\Contracts\WorkerContract;
use App\Platform\Enums\QACheck;
use App\Workers\AVA\Jobs\ClassifyEmailJob;
use App\Workers\AVA\Jobs\DailySummaryJob;
use App\Workers\AVA\Jobs\DraftEmailJob;
use App\Workers\AVA\Jobs\FastTrackIngestJob;
use App\Workers\AVA\Jobs\FilterEmailJob;
use App\Workers\AVA\Jobs\LogTransactionJob;
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

    public function employee(): array
    {
        return [
            'name'        => 'AVA',
            'pronoun'     => 'she',
            'title'       => 'Renewal Coordinator',
            'department'  => 'Customer Success',
            'employer'    => 'Freelancers, Solo Founders, Startup CEOs, Agency Owners',
            'mission'     => 'Never let a subscription, contract, invoice, or renewal request go unanswered.',
            'statement'   => 'I monitor your inbox and make sure no renewal, subscription, or contract ever slips through the cracks — so you never lose a client to an overlooked email.',
            'connects_to' => ['Gmail', 'Clients', 'Contacts', 'Assets'],
            'introduction'=> "Hi, I'm AVA. I make sure you never miss an important renewal. I watch your inbox, understand each renewal request, use what I know about your customers and business, prepare the reply, and leave it in Gmail for your approval.",
            'what_i_do'   => [
                'Monitor your Gmail 24/7',
                'Detect renewal and subscription requests',
                'Understand the customer using your memory',
                'Draft a personalized response',
                'Save it to Gmail Drafts for your review',
                'Learn from every interaction',
            ],
            'activity_labels' => [
                'watching'      => 'Inbox for renewal notices',
                'working_on'    => 'renewal responses',
                'waiting_label' => 'drafts to review',
                'memory_label'  => 'Customer history, subscription plans, writing style, company policies, past renewals',
            ],
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

    // ── Block 2: Onboarding Requirements ────────────────────────────────────

    public function platformRequirements(): array
    {
        return ['email'];
    }

    public function onboardingSteps(): array
    {
        return [
            [
                'name'        => 'credential',
                'label'       => 'Connect your Gmail',
                'description' => 'AVA watches this inbox for renewal and subscription emails.',
                'optional'    => false,
                'icon'        => 'mail',
            ],
            [
                'name'        => 'memory',
                'label'       => 'Upload your contacts & assets',
                'description' => 'Your clients, domains, and subscriptions — so AVA knows who each email is about.',
                'optional'    => true,
                'icon'        => 'brain',
            ],
            [
                'name'        => 'fast-track',
                'label'       => 'Run a live test',
                'description' => 'Fire a sample email through AVA and watch it draft a response end-to-end.',
                'optional'    => false,
                'icon'        => 'bolt',
            ],
        ];
    }

    public function tags(): array
    {
        return [
            'email', 'gmail', 'renewal', 'subscription', 'domain', 'ssl',
            'invoice', 'inbox', 'draft', 'automation', 'coordinator',
        ];
    }

    public function media(): array
    {
        return [
            'avatar' => '/workers/ava/avatar.png',
            'banner' => '/workers/ava/banner.jpg',
            'color'  => '#f3c531',
            'quote'  => 'Methodical, thorough, never misses a deadline. I watch your inbox so you don\'t have to — and I draft everything before the agency ever has to ask twice.',
        ];
    }

    public function ingestJobClass(): string
    {
        return \App\Workers\AVA\Jobs\FilterEmailJob::class;
    }

    public function pipelineStages(): array
    {
        return [
            ['key' => 'webhook',        'label' => 'Inject & Fetch',  'sub' => 'Insert into inbox, read back',   'icon' => 'bolt',     'job_class' => null],
            ['key' => 'read_email',     'label' => 'Read Email',      'sub' => 'Parse & extract fields',         'icon' => 'mail',     'job_class' => 'ReadEmailJob'],
            ['key' => 'classify',       'label' => 'Classify',        'sub' => 'Category, priority & type',      'icon' => 'tag',      'job_class' => 'ClassifyEmailJob'],
            ['key' => 'memory',         'label' => 'Memory Lookup',   'sub' => 'Match client, asset & rules',    'icon' => 'brain',    'job_class' => 'MemoryLookupJob'],
            ['key' => 'log_entry',      'label' => 'Log Transaction', 'sub' => 'Write to register',              'icon' => 'log',      'job_class' => 'LogTransactionJob'],
            ['key' => 'select_template','label' => 'Select Template', 'sub' => 'Pick best-match template',       'icon' => 'template', 'job_class' => 'SelectTemplateJob'],
            ['key' => 'draft_email',    'label' => 'Draft Email',     'sub' => 'AI-personalised draft',          'icon' => 'draft',    'job_class' => 'DraftEmailJob'],
            ['key' => 'push_draft',     'label' => 'Push to Gmail',   'sub' => 'Create draft in inbox',          'icon' => 'send',     'job_class' => 'PushToGmailJob'],
        ];
    }

    // ── Block 2: Deployment DNA ──────────────────────────────────────────────

    public function instances(): array
    {
        return [
            'multiple'  => true,
            'min'       => 1,
            'max'       => null,
            'limit_by'  => 'gmail_credentials', // enforce: one deployment per connected Gmail inbox
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
                'hint'        => 'Human-readable description of what this deployment monitors.',
            ],
            [
                'key'         => 'capture_keywords',
                'label'       => 'Keywords',
                'type'        => 'text',
                'placeholder' => 'renew, invoice, expires, subscription',
                'default'     => '',
                'hint'        => 'Email must contain at least one keyword (subject or body). Leave blank to capture all.',
            ],
            [
                'key'         => 'capture_domains',
                'label'       => 'Allowed Domains',
                'type'        => 'text',
                'placeholder' => 'godaddy.com, namecheap.com, stripe.com',
                'default'     => '',
                'hint'        => 'Only process emails from these sender domains. Leave blank to allow any.',
            ],
            [
                'key'         => 'exclude_senders',
                'label'       => 'Excluded Senders',
                'type'        => 'text',
                'placeholder' => 'noreply@parking.com, promo@ads.com',
                'default'     => '',
                'hint'        => 'Always skip emails from these addresses. Checked before all other rules.',
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

    public function fastTrackOutcome(): array
    {
        return [
            'headline'      => 'AVA just handled her first renewal — end to end.',
            'what_happened' => [
                ['icon' => 'read',      'text' => 'Read the email and pulled out the key details — sender, domain, deadline.'],
                ['icon' => 'classify',  'text' => 'Classified it as a renewal notice and scored it by urgency.'],
                ['icon' => 'memory',    'text' => 'Checked your contacts and assets to find who this email belongs to.'],
                ['icon' => 'template',  'text' => 'Selected the right response template based on your rules.'],
                ['icon' => 'draft',     'text' => 'Drafted a reply, ready for you to review and send — or approve automatically.'],
            ],
            'where_to_find' => [
                'label' => 'See the transaction in your workspace',
                'hint'  => 'Go to Transactions → look for the Fast Track entry. The draft is waiting under "Draft Ready."',
            ],
            'going_forward' => 'From now on, every renewal or subscription email that hits your Gmail will go through this same pipeline — automatically, without you lifting a finger.',
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

    public function subscriptions(): array
    {
        return [
            [
                'slug'                => 'starter',
                'label'               => 'Starter',
                'price_monthly'       => 49,
                'price_currency'      => 'USD',
                'transaction_limit'   => 100,
                'transaction_overage' => null,
                'prompt_overrides'    => false,
                'support'             => 'Email support',
                'highlights'          => [
                    '100 emails processed per month',
                    'Full 8-stage pipeline',
                    'Gmail draft creation',
                    'Memory: clients, contacts, assets',
                    'Email support',
                ],
            ],
            [
                'slug'                => 'pro',
                'label'               => 'Pro',
                'price_monthly'       => 149,
                'price_currency'      => 'USD',
                'transaction_limit'   => null, // unlimited
                'transaction_overage' => null,
                'prompt_overrides'    => true,
                'support'             => 'Priority email support',
                'highlights'          => [
                    'Unlimited emails processed',
                    'Per-stage prompt overrides',
                    'Multi-inbox support',
                    'Advanced renewal register',
                    'Priority email support',
                ],
            ],
            [
                'slug'                => 'enterprise',
                'label'               => 'Enterprise',
                'price_monthly'       => null, // custom
                'price_currency'      => 'USD',
                'transaction_limit'   => null,
                'transaction_overage' => null,
                'prompt_overrides'    => true,
                'support'             => 'Dedicated support + SLA',
                'highlights'          => [
                    'Unlimited emails processed',
                    'Per-stage prompt overrides',
                    'Dedicated support & SLA',
                    'Custom onboarding',
                    'Volume pricing',
                ],
            ],
        ];
    }

    public function versionChangelog(): array
    {
        return [
            [
                'version'         => '1.0',
                'date'            => '2024-01-01',
                'notes'           => 'Initial release. Gmail watch, 8-stage pipeline, renewal register, memory layers.',
                'breaking'        => false,
                'breaking_reason' => '',
                'upgrade_steps'   => [],
            ],
        ];
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

    public function deskCards(): array
    {
        return [
            'processed' => [
                'label'       => 'Emails Processed',
                'description' => 'How many emails AVA handled this week',
                'default'     => true,
                'default_pos' => 10,
                'dismissible' => false,
            ],
            'drafts' => [
                'label'       => 'Drafts Ready',
                'description' => 'Drafts waiting for your review in Gmail',
                'default'     => true,
                'default_pos' => 20,
                'dismissible' => false,
            ],
            'urgent' => [
                'label'       => 'Urgent Items',
                'description' => 'High-priority renewals needing your attention',
                'default'     => true,
                'default_pos' => 30,
                'dismissible' => false,
            ],
            'stuck' => [
                'label'       => 'Failed / Stuck',
                'description' => 'Emails AVA couldn\'t process or that got stuck',
                'default'     => true,
                'default_pos' => 40,
                'dismissible' => false,
            ],
        ];
    }

    public function valueClock(): array
    {
        return [
            'label'   => 'Hours Saved, All Time',
            'metric'  => 'hours_saved_alltime',
            'unit'    => 'hrs',
            'subtitle'=> '{count} emails processed',
            'formula' => 'emails processed × 0.25 hrs',
            'source'  => 'Each email AVA handles saves an estimated 15 minutes of manual follow-up, drafting, and tracking.',
            'scope'   => 'deployment',
        ];
    }

    public function overview(): array
    {
        return [
            'worker_name'  => 'AVA',
            'worker_role'  => 'Renewal Coordinator',
            'value_clock'  => [
                'metric' => 'hours_saved',
                'label'  => 'hours returned to your week',
                'period' => 'week',
            ],
            'briefing_verbs' => [
                'processed'  => 'processed',
                'unit'       => 'emails',
                'output'     => 'renewal drafts',
                'learning'   => 'new contacts learned',
            ],
            'panels' => [
                [
                    'type'     => 'action_queue',
                    'title'    => 'Needs Your Eyes',
                    'empty'    => 'Nothing waiting — AVA has everything covered.',
                    'priority' => 1,
                    'max_items' => 10,
                ],
                [
                    'type'     => 'horizon',
                    'title'    => 'Coming Up',
                    'windows'  => [30, 60, 90],
                    'priority' => 2,
                ],
                [
                    'type'     => 'metric_strip',
                    'title'    => 'This Week',
                    'period'   => 'week',
                    'metrics'  => ['emails_processed', 'approved_sent', 'hours_saved', 'response_rate'],
                    'priority' => 3,
                ],
                [
                    'type'     => 'alert_feed',
                    'title'    => 'Where I Got Stuck',
                    'empty'    => 'No issues — clean run.',
                    'priority' => 4,
                ],
                [
                    'type'     => 'activity_feed',
                    'title'    => 'What I Did',
                    'limit'    => 6,
                    'priority' => 5,
                ],
            ],
        ];
    }

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

    // ── Block 5: Output ──────────────────────────────────────────────────────

    public function output(): array
    {
        return [
            'description'  => 'A Gmail draft placed in the connected inbox, ready for human review and one-click send. Every processed email produces exactly one draft.',
            'destination'  => 'Gmail Drafts + renewal_register table',
            'format'       => 'email_draft',
            'fields'       => [
                ['key' => 'gmail_draft_id', 'type' => 'string',  'description' => 'Gmail API draft ID — used to send or delete the draft',   'nullable' => false],
                ['key' => 'to',             'type' => 'string',  'description' => 'Recipient email address resolved from memory lookup',       'nullable' => false],
                ['key' => 'subject',        'type' => 'string',  'description' => 'Email subject line from template + placeholders',           'nullable' => false],
                ['key' => 'body',           'type' => 'string',  'description' => 'Full email body — template-filled or Claude-generated',     'nullable' => false],
                ['key' => 'human_review_note', 'type' => 'string', 'description' => 'Internal note shown to tenant on the transaction detail', 'nullable' => true],
                ['key' => 'low_confidence', 'type' => 'boolean', 'description' => 'True when memory match confidence < 70% — flags caution',  'nullable' => false],
                ['key' => 'auto_sent',      'type' => 'boolean', 'description' => 'True when template approval_required = false and email was auto-sent without human review', 'nullable' => true],
            ],
            'human_action' => 'Review the draft on the Transactions page → Approve (sends immediately) or Reject (deletes draft).',
            'auto_action'  => 'When a template has approval_required = false, AVA sends the email immediately without creating a draft for review.',
        ];
    }

    // ── Block 6: Prompts ─────────────────────────────────────────────────────

    public function prompts(): array
    {
        return [
            [
                'stage'         => 'read',
                'label'         => 'Read Email',
                'uses_ai'       => true,
                'model'         => null,
                'system'        => 'You are Ava, UNIT\'s Subscription & Renewal Coordinator. Return valid JSON only. No extra text.',
                'user'          => "Read the email below and explain what it means.\n\nReturn valid JSON only with:\n{\n  \"plain_english_summary\": \"\",\n  \"what_happened\": \"\",\n  \"action_needed\": \"\",\n  \"due_date_or_deadline\": \"\",\n  \"risk_if_ignored\": \"\",\n  \"urgency\": \"Low|Medium|High|Critical\",\n  \"questions_for_memory_lookup\": []\n}\n\nEMAIL:\n{RAW_EMAIL}",
                'output_format' => 'json',
                'output_shape'  => ['plain_english_summary', 'what_happened', 'action_needed', 'due_date_or_deadline', 'risk_if_ignored', 'urgency', 'questions_for_memory_lookup'],
                'max_tokens'    => 512,
            ],
            [
                'stage'         => 'classify',
                'label'         => 'Classify',
                'uses_ai'       => true,
                'model'         => null,
                'system'        => 'You are Ava, UNIT\'s Subscription & Renewal Coordinator. Return valid JSON only. No extra text.',
                'user'          => "Classify this transaction using the email understanding below.\n\nAvailable categories: Domain Renewal, SSL Expiry, Hosting Invoice, SaaS Renewal, Failed Payment, Security Alert, Meeting Request, Client Support, Other\n\nReturn JSON:\n{\n  \"category\": \"\",\n  \"subcategory\": \"\",\n  \"priority\": \"Low|Medium|High|Critical\",\n  \"required_action\": \"\",\n  \"register_to_update\": \"\",\n  \"status\": \"\",\n  \"reason\": \"\"\n}\n\nCONTEXT:\n{READ_OUTPUT}",
                'output_format' => 'json',
                'output_shape'  => ['category', 'subcategory', 'priority', 'required_action', 'register_to_update', 'status', 'reason'],
                'max_tokens'    => 256,
            ],
            [
                'stage'         => 'memory',
                'label'         => 'Memory Lookup',
                'uses_ai'       => true,
                'model'         => null,
                'system'        => 'You are Ava, UNIT\'s Subscription & Renewal Coordinator. Return valid JSON only. No extra text.',
                'user'          => "Using the extracted email information and the memory tables below, find who owns this asset and how it should be handled.\n\nReturn JSON:\n{\n  \"asset\": \"\",\n  \"matched_client\": \"\",\n  \"primary_contact_name\": \"\",\n  \"primary_contact_email\": \"\",\n  \"related_project_or_service\": \"\",\n  \"client_preference\": \"\",\n  \"ava_rule\": \"\",\n  \"confidence\": 0,\n  \"missing_information\": []\n}\n\nEXTRACTED EMAIL CONTEXT:\n{READ_OUTPUT}\n\nMEMORY TABLES:\n{MEMORY_TABLES}",
                'output_format' => 'json',
                'output_shape'  => ['asset', 'matched_client', 'primary_contact_name', 'primary_contact_email', 'related_project_or_service', 'client_preference', 'ava_rule', 'confidence', 'missing_information'],
                'max_tokens'    => 768,
            ],
            [
                'stage'         => 'log_entry',
                'label'         => 'Log Transaction',
                'uses_ai'       => false,
                'model'         => null,
                'system'        => null,
                'user'          => null,
                'output_format' => null,
                'output_shape'  => null,
                'max_tokens'    => null,
            ],
            [
                'stage'         => 'select_template',
                'label'         => 'Select Template',
                'uses_ai'       => false,
                'model'         => null,
                'system'        => null,
                'user'          => null,
                'output_format' => null,
                'output_shape'  => null,
                'max_tokens'    => null,
            ],
            [
                'stage'         => 'draft_email',
                'label'         => 'Draft Email',
                'uses_ai'       => true,
                'model'         => null,
                'system'        => 'You are Ava, a professional email coordinator. Return only the email body — no subject line, no JSON, no extra text.',
                'user'          => "Write an email body using the template structure below.\n\nTemplate style: {TEMPLATE_NAME}\nTone: {TONE}\nTemplate body to follow:\n{BODY_TEMPLATE}\n\nFill in:\n- Contact first name: {FIRST_NAME}\n- Asset: {ASSET}\n- Client: {CLIENT}\n- Due date: {DUE_DATE}\n- Category: {CATEGORY}\n- Approval required: {APPROVAL_REQUIRED}\n- Sign as: {SENDER_NAME}\n\nRules:\n- Keep it concise\n- Do not promise work is done\n- Ask for approval when required\n- Return only the email body",
                'output_format' => 'text',
                'output_shape'  => 'Professional email body addressed to the contact, referencing the asset and due date, signed by the tenant.',
                'max_tokens'    => 1024,
            ],
            [
                'stage'         => 'push_draft',
                'label'         => 'Push to Gmail',
                'uses_ai'       => false,
                'model'         => null,
                'system'        => null,
                'user'          => null,
                'output_format' => null,
                'output_shape'  => null,
                'max_tokens'    => null,
            ],
        ];
    }

    // ── Block 7: Owner ───────────────────────────────────────────────────────

    public function owner(): array
    {
        return [
            'type'     => 'platform',
            'name'     => 'UNIT',
            'contact'  => 'hello@unit.report',
            'website'  => 'https://unit.report',
            'license'  => 'proprietary',
            'sla'      => '99.9% pipeline uptime · 4h support response · daily digest on failures',
            'since'    => 2024,
            'verified' => true,
        ];
    }

    // ── Block 8: Platform Integration ────────────────────────────────────────

    public function scheduledJobs(): array
    {
        return [
            [
                'job'            => DailySummaryJob::class,
                'cron'           => '0 * * * *',
                'queue'          => 'ava',
                'per_deployment' => true,
                'name'           => 'daily_summary',
            ],
        ];
    }

    public function fastTrackJobClass(): string
    {
        return FastTrackIngestJob::class;
    }

    public function stuckRecoveryMap(): array
    {
        return [
            'received'      => ReadEmailJob::class,
            'reading'       => ReadEmailJob::class,
            'classifying'   => ClassifyEmailJob::class,
            'memory_lookup' => MemoryLookupJob::class,
            'logging'       => LogTransactionJob::class,
            'templating'    => SelectTemplateJob::class,
            'drafting'      => DraftEmailJob::class,
        ];
    }
}

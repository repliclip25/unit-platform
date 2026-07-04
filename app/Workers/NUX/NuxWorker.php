<?php

namespace App\Workers\NUX;

use App\Platform\Contracts\WorkerContract;
use App\Platform\Enums\QACheck;
use App\Workers\NUX\Jobs\ClassifyPostJob;
use App\Workers\NUX\Jobs\DraftPostJob;
use App\Workers\NUX\Jobs\MediaJob;
use App\Workers\NUX\Jobs\PushToGmailJob;
use App\Workers\NUX\Jobs\ReadPostJob;
use App\Workers\NUX\Jobs\RepurposePostJob;

class NuxWorker implements WorkerContract
{
    // ── Block 1: Identity ────────────────────────────────────────────────────

    public function identity(): array
    {
        return [
            'name'        => 'NUX Publishing Worker',
            'slug'        => 'nux',
            'version'     => '1.0',
            'description' => 'Watches your LinkedIn and X feeds, repurposes top posts across channels, generates custom images, and delivers ready-to-publish drafts to your Gmail for approval.',
        ];
    }

    public function employee(): array
    {
        return [
            'name'        => 'NUX',
            'pronoun'     => 'she',
            'title'       => 'Multi-Channel Publishing Coordinator',
            'department'  => 'Marketing',
            'employer'    => 'Freelancers, Solo Founders, Content Creators, Agency Owners',
            'mission'     => 'Turn every great post into multi-channel reach — without extra work.',
            'statement'   => 'I turn your ideas and existing content into polished posts across every channel — so your voice stays consistent without you having to show up every day.',
            'connects_to' => ['LinkedIn', 'Twitter/X', 'Contacts', 'Gmail'],
            'introduction'=> "Hi, I'm NUX. I watch your LinkedIn and X accounts for posts worth repurposing. When I find one, I adapt it for the right channel, generate a matching image, and drop the draft in your Gmail for review. You write once — I handle the reach.",
            'what_i_do'   => [
                'Watch your LinkedIn and X feeds for new posts',
                'Detect content worth repurposing across channels',
                'Adapt each post to fit the target platform tone and format',
                'Generate a custom image using AI',
                'Deliver a ready-to-publish draft to your Gmail',
                'Track what\'s been repurposed so nothing runs twice',
            ],
            'activity_labels' => [
                'watching'      => 'LinkedIn and X feeds for new content',
                'working_on'    => 'publishing drafts',
                'waiting_label' => 'drafts to review',
                'memory_label'  => 'Brand voice, post history, channel preferences, image style guidelines',
            ],
        ];
    }

    public function org(): array
    {
        return [
            'name'         => 'LinkedIn & X',
            'abbreviation' => null,
            'type'         => 'platform',
            'website'      => 'https://linkedin.com',
            'logo'         => 'linkedin',
        ];
    }

    public function demoPayload(): array
    {
        return [
            'source'      => 'public_demo',
            'post_id'     => 'demo_' . substr(md5('nux-demo'), 0, 12),
            'platform'    => 'linkedin',
            'author'      => 'Demo User',
            'posted_at'   => now()->toIso8601String(),
            'post_text'   => "Just wrapped a 3-month client engagement where we rebuilt their entire content strategy from scratch.\n\nKey lesson: consistency beats virality every time.\n\nIf you post 3x per week for 6 months, you'll outperform someone who goes viral once and disappears.\n\nThe algorithm rewards showing up.",
            'post_url'    => 'https://linkedin.com/posts/demo',
            'target_channels' => ['x'],
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
                'label'       => 'Connect LinkedIn & X',
                'description' => 'NUX watches these accounts for new posts to repurpose.',
                'optional'    => false,
                'icon'        => 'link',
            ],
            [
                'name'        => 'gmail',
                'label'       => 'Connect your Gmail',
                'description' => 'NUX delivers finished drafts to this inbox for your review.',
                'optional'    => false,
                'icon'        => 'mail',
            ],
            [
                'name'        => 'memory',
                'label'       => 'Define your brand voice',
                'description' => 'Your tone guidelines, image style preferences, and channel rules — so every draft sounds like you.',
                'optional'    => true,
                'icon'        => 'brain',
            ],
            [
                'name'        => 'fast-track',
                'label'       => 'Run a live test',
                'description' => 'Submit a post and watch NUX repurpose it end-to-end.',
                'optional'    => false,
                'icon'        => 'bolt',
            ],
        ];
    }

    // ── Block 2: Deployment DNA ──────────────────────────────────────────────

    public function instances(): array
    {
        return [
            'multiple'  => false,
            'min'       => 1,
            'max'       => 1,
            'label'     => 'account set',
            'rationale' => 'One NUX instance manages your full social presence — LinkedIn + X are wired together under one deployment.',
        ];
    }

    public function credential(): array
    {
        return [
            [
                'key'             => 'linkedin',
                'type'            => 'oauth2',
                'label'           => 'LinkedIn Account',
                'hint'            => 'The LinkedIn profile or page NUX watches for new posts.',
                'required'        => true,
                'multiple'        => false,
                'connect_route'   => 'nux.connect.linkedin',
                'authorize_route' => 'nux.linkedin.authorize',
            ],
            [
                'key'             => 'x',
                'type'            => 'oauth2',
                'label'           => 'X (Twitter) Account',
                'hint'            => 'The X account NUX watches and repurposes content for.',
                'required'        => true,
                'multiple'        => false,
                'connect_route'   => 'nux.connect.x',
                'authorize_route' => 'nux.x.authorize',
            ],
            [
                'key'             => 'inbox',
                'type'            => 'gmail_oauth',
                'label'           => 'Gmail Delivery Inbox',
                'hint'            => 'NUX delivers finished drafts here for your review.',
                'required'        => true,
                'multiple'        => false,
                'connect_route'   => 'nux.connect.gmail',
                'authorize_route' => 'nux.gmail.authorize',
            ],
        ];
    }

    public function deploymentFields(): array
    {
        return [
            [
                'key'         => 'source_platform',
                'label'       => 'Source Platform',
                'type'        => 'select',
                'placeholder' => '',
                'default'     => 'linkedin',
                'hint'        => 'NUX watches this platform for new posts to repurpose.',
                'options'     => [
                    ['value' => 'linkedin', 'label' => 'LinkedIn'],
                    ['value' => 'x',        'label' => 'X (Twitter)'],
                    ['value' => 'both',     'label' => 'Both'],
                ],
            ],
            [
                'key'         => 'target_channels',
                'label'       => 'Target Channels',
                'type'        => 'text',
                'placeholder' => 'x, linkedin',
                'default'     => '',
                'hint'        => 'Comma-separated channels NUX repurposes content for (opposite of source).',
            ],
            [
                'key'         => 'min_post_length',
                'label'       => 'Minimum Post Length (chars)',
                'type'        => 'text',
                'placeholder' => '100',
                'default'     => '100',
                'hint'        => 'Only repurpose posts longer than this. Filters out short-form noise.',
            ],
            [
                'key'         => 'generate_image',
                'label'       => 'Generate Images',
                'type'        => 'toggle',
                'placeholder' => '',
                'default'     => true,
                'hint'        => 'Use DALL-E 3 to generate a custom image for each repurposed post.',
            ],
        ];
    }

    public function trainSchema(): array
    {
        return [
            [
                'key'         => 'brand_voice',
                'label'       => 'Brand Voice',
                'description' => 'Tone, vocabulary, and style rules NUX follows when rewriting content. E.g. "Conversational but professional. No hashtag spam. Always end with a question."',
                'required'    => false,
                'format_hint' => 'Manual entry',
            ],
            [
                'key'         => 'image_style',
                'label'       => 'Image Style Guidelines',
                'description' => 'Visual direction for DALL-E 3 image generation. E.g. "Clean, minimal, navy and white palette. No people. Abstract data visuals."',
                'required'    => false,
                'format_hint' => 'Manual entry',
            ],
            [
                'key'         => 'channel_rules',
                'label'       => 'Channel Rules',
                'description' => 'Per-channel formatting rules. E.g. "X posts max 280 chars. LinkedIn posts can be longer with a hook in line 1."',
                'required'    => false,
                'format_hint' => 'Manual entry',
            ],
            [
                'key'         => 'excluded_topics',
                'label'       => 'Excluded Topics',
                'description' => 'Topics or keywords NUX should never repurpose. E.g. "politics, pricing, competitor mentions."',
                'required'    => false,
                'format_hint' => 'Manual entry',
            ],
        ];
    }

    public function tags(): array
    {
        return [
            'linkedin', 'x', 'twitter', 'social', 'content', 'publishing',
            'repurpose', 'marketing', 'image', 'ai', 'draft', 'automation',
            'creator', 'brand', 'reach',
        ];
    }

    public function media(): array
    {
        return [
            'avatar' => '/workers/nux/avatar.png',
            'banner' => '/workers/nux/banner.jpg',
            'color'  => '#5eead4',
            'quote'  => 'You write once. I find the reach. Every post you publish is one I can turn into three — tailored, on-brand, and ready before you even finish your coffee.',
        ];
    }

    public function fastTrack(): array
    {
        return [
            'source'    => 'fast_track_test',
            'post_id'   => 'ft_' . substr(md5('nux-fast-track'), 0, 12),
            'platform'  => 'linkedin',
            'author'    => 'You',
            'posted_at' => now()->toIso8601String(),
            'post_text' => "Just wrapped a 3-month client engagement where we rebuilt their entire content strategy from scratch.\n\nKey lesson: consistency beats virality every time.\n\nIf you post 3x per week for 6 months, you'll outperform someone who goes viral once and disappears.\n\nThe algorithm rewards showing up.",
            'post_url'  => 'https://linkedin.com/posts/fast-track-test',
            'target_channels' => ['x'],
        ];
    }

    public function fastTrackOutcome(): array
    {
        return [
            'headline'      => 'NUX just turned one LinkedIn post into a ready-to-publish X thread.',
            'what_happened' => [
                ['icon' => 'read',     'text' => 'Read the LinkedIn post and pulled out the core idea, tone, and key hook.'],
                ['icon' => 'classify', 'text' => 'Classified it as a thought leadership post — high repurpose value.'],
                ['icon' => 'draft',    'text' => 'Rewrote it for X — tighter, punchier, under 280 chars with a strong hook.'],
                ['icon' => 'image',    'text' => 'Generated a custom image using DALL-E 3 to match your brand style.'],
                ['icon' => 'send',     'text' => 'Delivered the full draft to your Gmail — ready to copy and post.'],
            ],
            'where_to_find' => [
                'label' => 'Find the draft in your workspace',
                'hint'  => 'Go to Transactions → look for the Fast Track entry. The draft is under "Draft Ready."',
            ],
            'going_forward' => 'From now on, every new post NUX detects on LinkedIn or X will go through this same pipeline — automatically repurposed, imaged, and delivered to your Gmail without you lifting a finger.',
        ];
    }

    public function pipelineStages(): array
    {
        return [
            ['key' => 'read_post',    'label' => 'Read Post',       'sub' => 'Fetch and parse source post',      'icon' => 'mail',     'job_class' => 'ReadPostJob'],
            ['key' => 'classify',     'label' => 'Classify',        'sub' => 'Type, topic, repurpose value',     'icon' => 'tag',      'job_class' => 'ClassifyPostJob'],
            ['key' => 'repurpose',    'label' => 'Repurpose',       'sub' => 'AI rewrite for each target channel','icon' => 'draft',    'job_class' => 'RepurposePostJob'],
            ['key' => 'media',        'label' => 'Generate Image',  'sub' => 'DALL-E 3 custom visual',           'icon' => 'image',    'job_class' => 'MediaJob'],
            ['key' => 'draft_post',   'label' => 'Package Draft',   'sub' => 'Compile copies + image',           'icon' => 'log',      'job_class' => 'DraftPostJob'],
            ['key' => 'push_draft',   'label' => 'Deliver to Inbox','sub' => 'Send content package for review',  'icon' => 'send',     'job_class' => 'PushToGmailJob'],
        ];
    }

    // ── Block 3: Pipeline ────────────────────────────────────────────────────

    public function ingestJobClass(): string
    {
        return ReadPostJob::class;
    }

    public function input(): array
    {
        return [
            'description' => 'A social media post from LinkedIn or X — detected via API polling, submitted manually via fast track, or captured as a raw idea.',
            'source'      => 'LinkedIn API / X API / manual submission / idea',
            'fields'      => [
                ['key' => 'source',          'type' => 'string', 'description' => 'Input source: poller | fast_track | idea',  'required' => false, 'default' => 'poller'],
                ['key' => 'post_id',         'type' => 'string', 'description' => 'Platform post ID (omit for idea source)',   'required' => false],
                ['key' => 'platform',        'type' => 'string', 'description' => 'Source platform: linkedin | x | idea',     'required' => false],
                ['key' => 'author',          'type' => 'string', 'description' => 'Post author name',                          'required' => false],
                ['key' => 'posted_at',       'type' => 'string', 'description' => 'ISO 8601 timestamp of original post',       'required' => false],
                ['key' => 'post_text',       'type' => 'string', 'description' => 'Full post body text (poller/fast_track)',   'required' => false],
                ['key' => 'idea_text',       'type' => 'string', 'description' => 'Raw idea text (idea source only)',          'required' => false],
                ['key' => 'post_url',        'type' => 'string', 'description' => 'Canonical URL of the original post',        'required' => false],
                ['key' => 'target_channels', 'type' => 'array',  'description' => 'Channels to repurpose for e.g. [\'x\']',    'required' => true],
            ],
        ];
    }

    public function pipeline(): array
    {
        $total = 6;
        return [
            [
                'stage'         => 1,
                'total'         => $total,
                'job'           => ReadPostJob::class,
                'label'         => 'Read Post',
                'receives_from' => 'input',
                'accepts'       => [
                    ['key' => 'post_id',         'type' => 'string', 'description' => 'Platform post ID'],
                    ['key' => 'platform',        'type' => 'string', 'description' => 'Source platform'],
                    ['key' => 'post_text',       'type' => 'string', 'description' => 'Full post body text'],
                    ['key' => 'target_channels', 'type' => 'array',  'description' => 'Target channels list'],
                ],
                'produces'      => [
                    ['key' => 'post_text',        'type' => 'string', 'description' => 'Cleaned post body'],
                    ['key' => 'platform',         'type' => 'string', 'description' => 'Confirmed source platform'],
                    ['key' => 'post_url',         'type' => 'string', 'description' => 'Canonical post URL'],
                    ['key' => 'author',           'type' => 'string', 'description' => 'Resolved author name'],
                    ['key' => 'posted_at',        'type' => 'string', 'description' => 'Post timestamp'],
                    ['key' => 'word_count',       'type' => 'integer','description' => 'Word count of the post'],
                    ['key' => 'detected_topics',  'type' => 'array',  'description' => 'Topics extracted from post text'],
                    ['key' => 'target_channels',  'type' => 'array',  'description' => 'Passed through target channels'],
                ],
                'connects_to'   => ClassifyPostJob::class,
                'can_emit'      => [],
            ],
            [
                'stage'         => 2,
                'total'         => $total,
                'job'           => ClassifyPostJob::class,
                'label'         => 'Classify Post',
                'receives_from' => 'Read Post',
                'accepts'       => [
                    ['key' => 'post_text',       'type' => 'string', 'description' => 'Cleaned post body'],
                    ['key' => 'platform',        'type' => 'string', 'description' => 'Source platform'],
                    ['key' => 'word_count',      'type' => 'integer','description' => 'Post word count'],
                    ['key' => 'detected_topics', 'type' => 'array',  'description' => 'Topics from read stage'],
                ],
                'produces'      => [
                    ['key' => 'post_type',        'type' => 'string', 'description' => 'thought_leadership | tip | story | product | other'],
                    ['key' => 'topic',            'type' => 'string', 'description' => 'Primary topic of the post'],
                    ['key' => 'tone',             'type' => 'string', 'description' => 'conversational | professional | motivational | educational'],
                    ['key' => 'repurpose_value',  'type' => 'string', 'description' => 'high | medium | low — whether post is worth repurposing'],
                    ['key' => 'confidence',       'type' => 'float',  'description' => 'Classification confidence 0–1'],
                    ['key' => 'skip_reason',      'type' => 'string', 'description' => 'If repurpose_value = low, why NUX is skipping this post'],
                ],
                'connects_to'   => RepurposePostJob::class,
                'can_emit'      => ['content.low_value_skipped'],
            ],
            [
                'stage'         => 3,
                'total'         => $total,
                'job'           => RepurposePostJob::class,
                'label'         => 'Repurpose',
                'receives_from' => 'Classify Post',
                'accepts'       => [
                    ['key' => 'post_text',       'type' => 'string', 'description' => 'Original post body'],
                    ['key' => 'post_type',       'type' => 'string', 'description' => 'Post type from classify'],
                    ['key' => 'tone',            'type' => 'string', 'description' => 'Original tone'],
                    ['key' => 'topic',           'type' => 'string', 'description' => 'Post topic'],
                    ['key' => 'target_channels', 'type' => 'array',  'description' => 'Channels to repurpose for'],
                ],
                'produces'      => [
                    ['key' => 'repurposed_copies', 'type' => 'array',  'description' => 'Array of {channel, copy, char_count} — one per target channel'],
                    ['key' => 'image_prompt',      'type' => 'string', 'description' => 'DALL-E 3 prompt for image generation'],
                    ['key' => 'image_needed',      'type' => 'boolean','description' => 'Whether an image should be generated'],
                ],
                'connects_to'   => MediaJob::class,
                'can_emit'      => [],
            ],
            [
                'stage'         => 4,
                'total'         => $total,
                'job'           => MediaJob::class,
                'label'         => 'Generate Image',
                'receives_from' => 'Repurpose',
                'accepts'       => [
                    ['key' => 'image_prompt',  'type' => 'string',  'description' => 'DALL-E 3 prompt'],
                    ['key' => 'image_needed',  'type' => 'boolean', 'description' => 'Whether to generate'],
                    ['key' => 'topic',         'type' => 'string',  'description' => 'Post topic for fallback prompt'],
                ],
                'produces'      => [
                    ['key' => 'image_url',      'type' => 'string',  'description' => 'Generated image URL from DALL-E 3 (null if skipped)'],
                    ['key' => 'image_path',     'type' => 'string',  'description' => 'Local storage path after download (null if skipped)'],
                    ['key' => 'image_generated','type' => 'boolean', 'description' => 'True if image was successfully generated'],
                ],
                'connects_to'   => DraftPostJob::class,
                'can_emit'      => [],
            ],
            [
                'stage'         => 5,
                'total'         => $total,
                'job'           => DraftPostJob::class,
                'label'         => 'Package Draft',
                'receives_from' => 'Generate Image',
                'accepts'       => [
                    ['key' => 'repurposed_copies', 'type' => 'array',   'description' => 'Repurposed copy per channel from stage 3'],
                    ['key' => 'image_url',         'type' => 'string',  'description' => 'Generated image URL (null if skipped)'],
                    ['key' => 'image_path',        'type' => 'string',  'description' => 'Local storage path of image (null if skipped)'],
                    ['key' => 'post_text',         'type' => 'string',  'description' => 'Original post text for attribution'],
                    ['key' => 'post_url',          'type' => 'string',  'description' => 'URL of the original source post'],
                ],
                'produces'      => [
                    ['key' => 'email_subject',  'type' => 'string', 'description' => 'Subject line of the delivery email (e.g. "NUX: Repurposed for LinkedIn + X — leadership")'],
                    ['key' => 'email_body',     'type' => 'string', 'description' => 'HTML delivery package: all repurposed copies + image, ready to copy-paste and post'],
                    ['key' => 'draft_summary',  'type' => 'string', 'description' => 'One-line summary shown on the transaction card'],
                ],
                'connects_to'   => PushToGmailJob::class,
                'can_emit'      => [],
            ],
            [
                'stage'         => 6,
                'total'         => $total,
                'job'           => PushToGmailJob::class,
                'label'         => 'Deliver to Inbox',
                'receives_from' => 'Package Draft',
                'accepts'       => [
                    ['key' => 'email_subject', 'type' => 'string', 'description' => 'Delivery email subject'],
                    ['key' => 'email_body',    'type' => 'string', 'description' => 'HTML content package'],
                ],
                'produces'      => [
                    ['key' => 'gmail_draft_id', 'type' => 'string', 'description' => 'Gmail draft ID — open it to copy each platform post and publish'],
                    ['key' => 'status',         'type' => 'string', 'description' => 'draft_ready | failed'],
                ],
                'connects_to'   => null,
                'can_emit'      => ['content.draft_ready'],
            ],
        ];
    }

    public function emit(): array
    {
        return [
            [
                'event'       => 'content.low_value_skipped',
                'fired_from'  => 'Classify Post',
                'description' => 'Post was classified as low repurpose value and skipped — no further processing.',
                'reusable'    => false,
                'fields'      => [
                    ['key' => 'post_id',     'type' => 'string', 'description' => 'The skipped post ID'],
                    ['key' => 'skip_reason', 'type' => 'string', 'description' => 'Why NUX determined this post is not worth repurposing'],
                ],
            ],
            [
                'event'       => 'content.draft_ready',
                'fired_from'  => 'Deliver to Inbox',
                'description' => 'Full repurposed content packet delivered — copies per target channel + AI image landed in your Gmail drafts, ready to copy-paste and publish.',
                'reusable'    => true,
                'fields'      => [
                    ['key' => 'draft',    'type' => 'object', 'description' => 'Delivery details: gmail_draft_id, subject, status, created_at'],
                    ['key' => 'source',   'type' => 'object', 'description' => 'Source post: platform, post_id, post_url, author, posted_at'],
                    ['key' => 'content',  'type' => 'object', 'description' => 'Repurposed content: copies (array), image_url, image_generated'],
                    ['key' => 'classify', 'type' => 'object', 'description' => 'Classification: post_type, topic, tone, repurpose_value, confidence'],
                ],
            ],
        ];
    }

    public function commit(): ?array
    {
        return null;
    }

    public function subscriptions(): array
    {
        return [];
    }

    public function versionChangelog(): array
    {
        return [
            [
                'version'         => '1.0',
                'date'            => '2026-06-29',
                'notes'           => 'Initial release. LinkedIn + X watching, 6-stage repurpose pipeline, DALL-E 3 image generation, inbox delivery. Idea input source + T+7/14/30/90 performance feedback loop.',
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
                'key'           => 'drafts_awaiting_review',
                'level'         => 'info',
                'query'         => 'tx_draft_ready_undecided',
                'trigger'       => ['operator' => '>', 'value' => 0],
                'message'       => '{count} repurposed draft{plural} awaiting your review',
                'action_label'  => 'Review',
                'action_route'  => 'transactions',
                'action_params' => ['filter' => 'draft_ready'],
            ],
            [
                'key'           => 'failed_today',
                'level'         => 'error',
                'query'         => 'tx_failed_today',
                'trigger'       => ['operator' => '>', 'value' => 0],
                'message'       => '{count} post{plural} failed to process today',
                'action_label'  => 'Inspect',
                'action_route'  => 'transactions',
                'action_params' => ['filter' => 'failed'],
            ],
        ];
    }

    // ── Block 3d: Dashboard Surface ─────────────────────────────────────────

    public function deskCards(): array
    {
        return [
            'drafted' => [
                'label'       => 'Posts Drafted',
                'description' => 'Pieces of content NUX drafted this week',
                'default'     => true,
                'default_pos' => 10,
                'dismissible' => false,
            ],
            'published' => [
                'label'       => 'Posts Published',
                'description' => 'Content approved and pushed to your channels',
                'default'     => true,
                'default_pos' => 20,
                'dismissible' => false,
            ],
            'trial' => [
                'label'       => 'Free Credits',
                'description' => 'How many free trial transactions you\'ve used',
                'default'     => true,
                'default_pos' => 5,
                'dismissible' => false,
            ],
        ];
    }

    public function valueClock(): array
    {
        return [
            'label'   => 'Posts Drafted, All Time',
            'metric'  => 'posts_drafted_alltime',
            'unit'    => '',
            'subtitle'=> '{count} pieces of content created',
            'formula' => 'count of drafted & published posts',
            'source'  => 'Every post NUX drafts replaces 30–60 minutes of writing, editing, and scheduling.',
            'scope'   => 'deployment',
        ];
    }

    public function overview(): array
    {
        return [
            'worker_name'  => 'NUX',
            'worker_role'  => 'Content Engine',
            'value_clock'  => [
                'metric' => 'approved_sent',
                'label'  => 'posts published this week',
                'period' => 'week',
            ],
            'briefing_verbs' => [
                'processed'  => 'repurposed',
                'unit'       => 'ideas',
                'output'     => 'posts drafted',
                'learning'   => 'content patterns learned',
            ],
            'panels' => [
                [
                    'type'      => 'action_queue',
                    'title'     => 'Needs Your Eyes',
                    'empty'     => 'Nothing waiting — NUX has everything covered.',
                    'priority'  => 1,
                    'max_items' => 10,
                ],
                [
                    'type'    => 'metric_strip',
                    'title'   => 'This Week',
                    'period'  => 'week',
                    'metrics' => ['emails_processed', 'approved_sent', 'hours_saved', 'response_rate'],
                    'priority' => 2,
                ],
                [
                    'type'     => 'alert_feed',
                    'title'    => 'Where I Got Stuck',
                    'empty'    => 'No issues — clean run.',
                    'priority' => 3,
                ],
                [
                    'type'     => 'activity_feed',
                    'title'    => 'What I Did',
                    'limit'    => 6,
                    'priority' => 4,
                ],
            ],
        ];
    }

    public function dashboard(): array
    {
        return [
            'accent' => 'teal',
            'icon'   => 'M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z',
            'stats'  => [
                ['key' => 'tx_draft_ready', 'label' => 'Drafts Ready'],
                ['key' => 'tx_today',       'label' => 'Today'],
                ['key' => 'tx_total',       'label' => 'Total Posts'],
            ],
        ];
    }

    // ── Block 4: Quality ────────────────────────────────────────────────────

    public function qaRequirements(): array
    {
        return [
            [
                'stage' => 'read_post',
                'check' => QACheck::OUTPUT_NOT_EMPTY,
                'label' => 'Post read and parsed successfully',
            ],
            [
                'stage' => 'classify',
                'check' => QACheck::FIELD_NOT_NULL,
                'field' => 'post_type',
                'label' => 'Post type resolved',
            ],
            [
                'stage' => 'classify',
                'check' => QACheck::VALUE_ABOVE,
                'field' => 'confidence',
                'threshold' => 0.4,
                'label' => 'Classification confidence ≥ 40%',
            ],
            [
                'stage' => 'repurpose',
                'check' => QACheck::FIELD_NOT_EMPTY,
                'field' => 'repurposed_copies',
                'label' => 'At least one repurposed copy generated',
            ],
            [
                'stage' => 'draft_post',
                'check' => QACheck::FIELD_NOT_EMPTY,
                'field' => 'email_body',
                'label' => 'Content package compiled with all repurposed copies',
            ],
            [
                'stage' => 'push_draft',
                'check' => QACheck::STATUS_IN,
                'field' => 'status',
                'values' => ['draft_ready'],
                'label' => 'Content package delivered to inbox',
            ],
        ];
    }

    // ── Block 5: Output ──────────────────────────────────────────────────────

    public function output(): array
    {
        return [
            'description'  => 'A content package delivered to your Gmail drafts — one repurposed version per target channel plus an AI-generated image, ready to copy and publish.',
            'destination'  => 'Gmail Drafts + nux_register table',
            'format'       => 'email_draft',
            'fields'       => [
                ['key' => 'gmail_draft_id',    'type' => 'string',  'description' => 'Gmail draft ID — open to access all repurposed copies and image', 'nullable' => false],
                ['key' => 'repurposed_copies', 'type' => 'array',   'description' => 'Array of {channel, copy, char_count} — one entry per target channel', 'nullable' => false],
                ['key' => 'image_url',         'type' => 'string',  'description' => 'DALL-E 3 generated image URL (null if image generation was skipped)', 'nullable' => true],
                ['key' => 'source_post_url',   'type' => 'string',  'description' => 'URL of the original source post for attribution',                  'nullable' => true],
                ['key' => 'draft_summary',     'type' => 'string',  'description' => 'One-line summary shown on the transaction card',                   'nullable' => false],
            ],
            'human_action' => 'Open the Gmail draft → copy each repurposed post to its platform and publish. Submit performance metrics after 7, 14, 30, and 90 days.',
            'auto_action'  => null,
        ];
    }

    // ── Block 6: Prompts ─────────────────────────────────────────────────────

    public function prompts(): array
    {
        return [
            [
                'stage'         => 'read_post',
                'label'         => 'Read Post',
                'uses_ai'       => false,
                'model'         => null,
                'system'        => null,
                'user'          => null,
                'output_format' => null,
                'output_shape'  => null,
                'max_tokens'    => null,
            ],
            [
                'stage'         => 'classify',
                'label'         => 'Classify Post',
                'uses_ai'       => true,
                'model'         => null,
                'system'        => 'You are NUX, a social media content strategist. Return valid JSON only. No extra text.',
                'user'          => "Analyze this social media post and classify it.\n\nReturn JSON only:\n{\n  \"post_type\": \"thought_leadership|tip|story|product|other\",\n  \"topic\": \"\",\n  \"tone\": \"conversational|professional|motivational|educational\",\n  \"repurpose_value\": \"high|medium|low\",\n  \"confidence\": 0.0,\n  \"skip_reason\": \"\"\n}\n\nOnly set skip_reason if repurpose_value is low.\n\nPOST:\n{POST_TEXT}",
                'output_format' => 'json',
                'output_shape'  => ['post_type', 'topic', 'tone', 'repurpose_value', 'confidence', 'skip_reason'],
                'max_tokens'    => 256,
            ],
            [
                'stage'         => 'repurpose',
                'label'         => 'Repurpose',
                'uses_ai'       => true,
                'model'         => null,
                'system'        => 'You are NUX, a social media content strategist. Return valid JSON only. No extra text.',
                'user'          => "Repurpose this {SOURCE_PLATFORM} post for each target channel listed below.\n\nFor each channel, write a new version that fits its native format and character limits:\n- x: max 280 chars, punchy hook, no hashtag spam\n- linkedin: can be longer, hook in line 1, can include hashtags\n\nAlso write a DALL-E 3 image prompt for a visual that matches this post's topic and tone.\n\nBrand voice: {BRAND_VOICE}\nChannel rules: {CHANNEL_RULES}\n\nReturn JSON:\n{\n  \"repurposed_copies\": [\n    {\"channel\": \"\", \"copy\": \"\", \"char_count\": 0}\n  ],\n  \"image_prompt\": \"\",\n  \"image_needed\": true\n}\n\nTarget channels: {TARGET_CHANNELS}\n\nORIGINAL POST:\n{POST_TEXT}",
                'output_format' => 'json',
                'output_shape'  => ['repurposed_copies', 'image_prompt', 'image_needed'],
                'max_tokens'    => 1024,
            ],
            [
                'stage'         => 'media',
                'label'         => 'Generate Image',
                'uses_ai'       => true,
                'model'         => 'dall-e-3',
                'system'        => null,
                'user'          => '{IMAGE_PROMPT}',
                'output_format' => 'image_url',
                'output_shape'  => 'Square 1024x1024 image matching the post topic and brand style.',
                'max_tokens'    => null,
            ],
            [
                'stage'         => 'draft_post',
                'label'         => 'Package Draft',
                'uses_ai'       => false,
                'model'         => null,
                'system'        => null,
                'user'          => null,
                'output_format' => null,
                'output_shape'  => null,
                'max_tokens'    => null,
            ],
            [
                'stage'         => 'push_draft',
                'label'         => 'Deliver to Inbox',
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
            'contact'  => config('services.unit.noreply_email'),
            'website'  => 'https://unit.report',
            'license'  => 'proprietary',
            'sla'      => '99.9% pipeline uptime · 4h support response · daily digest on failures',
            'since'    => 2026,
            'verified' => true,
        ];
    }

    // ── Block 8: Platform Integration ────────────────────────────────────────

    public function scheduledJobs(): array
    {
        return [
            [
                'job'            => \App\Workers\NUX\Jobs\NuxPerformanceFeedbackJob::class,
                'name'           => 'performance-feedback',
                'cron'           => '0 6 * * *',
                'queue'          => 'nux-scheduled',
                'per_deployment' => true,
                'description'    => 'Create pending performance tracking slots at T+7/14/30/90 and enrich nux_memory with engagement patterns after manual feedback.',
            ],
        ];
    }

    public function fastTrackJobClass(): string
    {
        return '';
    }

    public function stuckRecoveryMap(): array
    {
        return [
            'reading'     => ReadPostJob::class,
            'classifying' => ClassifyPostJob::class,
            'repurposing' => RepurposePostJob::class,
            'generating'  => MediaJob::class,
            'drafting'    => DraftPostJob::class,
        ];
    }
}

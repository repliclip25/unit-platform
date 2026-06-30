<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('workers')->where('slug', 'nux')->exists()) {
            return;
        }

        DB::table('workers')->insert([
            'slug'               => 'nux',
            'name'               => 'NUX — Multi-Channel Publishing Coordinator',
            'category'           => 'Marketing',
            'version'            => '1.0',
            'org'                => 'LinkedIn · X · Gmail',
            'channel'            => 'social',
            'status'             => 'running',
            'marketplace_status' => 'published',
            'description'        => 'Watches LinkedIn and X for high-value posts, repurposes them across channels with AI, optionally generates a custom image, and delivers ready-to-publish drafts to your Gmail inbox.',
            'built_by'           => 'UNIT Platform',
            'qa_checklist'       => json_encode([]),
            'blueprint'          => json_encode([
                'meta' => [
                    'name'        => 'NUX — Multi-Channel Publishing Coordinator',
                    'slug'        => 'nux',
                    'version'     => '1.0.0',
                    'built_by'    => 'UNIT Platform',
                    'category'    => 'marketing',
                    'description' => 'Watches LinkedIn and X for high-value posts, repurposes them across channels with AI, and delivers ready-to-publish drafts.',
                ],
                'pipeline' => [
                    ['step' => 1, 'job' => 'ReadPostJob',     'description' => 'Read and dedup social post'],
                    ['step' => 2, 'job' => 'ClassifyPostJob',  'description' => 'AI classify: type, topic, repurpose value'],
                    ['step' => 3, 'job' => 'RepurposePostJob', 'description' => 'AI repurpose for each target channel'],
                    ['step' => 4, 'job' => 'MediaJob',         'description' => 'DALL-E 3 image generation (optional)'],
                    ['step' => 5, 'job' => 'DraftPostJob',     'description' => 'Compile HTML email draft'],
                    ['step' => 6, 'job' => 'PushToGmailJob',   'description' => 'Deliver draft to Gmail inbox'],
                ],
                'structure' => ['root' => 'app/Workers/NUX'],
            ]),
            'input_schema' => json_encode([
                'sections' => [[
                    'title'       => 'Social Post Poller',
                    'description' => 'Triggered by NuxPostPollerService when a new LinkedIn or X post is detected.',
                    'sample'      => [
                        'source'          => 'poller',
                        'post_id'         => 'urn:li:activity:7200000000000000000',
                        'platform'        => 'linkedin',
                        'author'          => 'Jane Doe',
                        'posted_at'       => '2026-06-29T09:00:00Z',
                        'post_text'       => 'Consistency is the secret weapon of great content marketers...',
                        'post_url'        => 'https://linkedin.com/posts/janedoe_content-strategy',
                        'target_channels' => ['x'],
                    ],
                ]],
            ]),
            'output_schema' => json_encode([
                'sections' => [[
                    'title'       => 'Gmail Draft',
                    'description' => 'HTML email with all channel copies and optional image, delivered to the tenant Gmail inbox.',
                    'sample'      => [
                        'gmail_draft_id' => 'r-abc123',
                        'subject'        => '[NUX] New draft ready — LinkedIn post repurposed for X',
                        'draft_summary'  => 'Repurposed linkedin post for x (thought_leadership)',
                    ],
                ]],
            ]),
            'emit_schema' => json_encode([
                'sections' => [
                    [
                        'event'       => 'content.draft_ready',
                        'title'       => 'Content Draft Ready',
                        'description' => 'Fired when NUX delivers a repurposed content draft to Gmail.',
                        'sample'      => ['event' => 'content.draft_ready', 'tx_id' => 'NUX-20260629-abc123'],
                    ],
                    [
                        'event'       => 'content.low_value_skipped',
                        'title'       => 'Post Skipped — Low Value',
                        'description' => 'Fired when NUX classifies a post as low repurpose value and skips it.',
                        'sample'      => ['event' => 'content.low_value_skipped', 'tx_id' => 'NUX-20260629-xyz789'],
                    ],
                ],
            ]),
            'manifest'   => json_encode(['description' => 'Watches social feeds, repurposes posts with AI, delivers drafts to inbox.']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('workers')->where('slug', 'nux')->delete();
    }
};

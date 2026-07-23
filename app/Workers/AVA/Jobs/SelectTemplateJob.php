<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SelectTemplateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;

    public function __construct(public string $txId) {}

    public function handle(): void
    {
        $input    = UnitPlatform::getInput($this->txId);
        $classify = $input->stage('classify');
        $memory   = $input->stage('memory');
        $category = $classify['category'] ?? 'Other';

        // Templates pre-loaded by UnitPlatform::getInput() — no DB calls needed
        $userTemplates    = $input->memory['templates'];
        $defaultTemplates = $input->memory['templates_default'];

        // 1. User-customised template for this category
        $template = collect($userTemplates)->firstWhere('category', $category);

        // 2. Platform default for this category
        if (!$template) {
            $template = collect($defaultTemplates)
                ->where('category', $category)
                ->where('is_default', true)
                ->first();
        }

        // 3. Generic fallback
        if (!$template) {
            $template = collect($defaultTemplates)
                ->where('category', 'Other')
                ->where('is_default', true)
                ->first();
        }

        $template = $template ? (array) $template : [];

        $output = [
            'template_id'       => $template['id']               ?? null,
            'template_name'     => $template['name']             ?? 'Generic Response',
            'tone'              => $template['tone']             ?? 'Professional, concise',
            'subject_template'  => $template['subject_template'] ?? 'Action Required: {{asset}}',
            'body_template'     => $template['body_template']    ?? '',
            'approval_required' => $template['approval_required'] ?? true,
            'low_confidence'    => !empty($memory['low_confidence_warning']),
        ];

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:  'template',
            status: 'templating',
            data:   $output,
        ));

        UnitPlatform::log('ava', $this->txId, 'template_selected', [
            'template_name' => $output['template_name'],
            'category'      => $category,
        ]);

        UnitPlatform::advance($this->txId, 'select_template');
    }

    public function failed(\Throwable $e): void
    {
        if ($e instanceof \App\Platform\Exceptions\BillingException) {
            UnitPlatform::setStatus($this->txId, 'blocked');
            UnitPlatform::log('ava', $this->txId, 'billing_blocked', ['code' => $e->billingCode, 'reason' => $e->getMessage()], 'warning');
            $this->delete();
            return;
        }
        UnitPlatform::setStatus($this->txId, 'failed');
        UnitPlatform::log('ava', $this->txId, 'job_failed', [
            'job' => 'SelectTemplateJob', 'error' => $e->getMessage(),
        ], 'error');
    }
}

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

        // Round 1 — DraftEmailJob re-resolves for rounds 2/3 itself, since
        // this job only ever runs once, at the start of the transaction.
        $template = \App\Platform\Services\TemplateResolver::resolve($category, $userTemplates, $defaultTemplates, 1);

        // NOT "does a round-2/3 template exist for this category" — the
        // round-2/3 templates that actually exist are mostly the generic
        // empty-category fallback (matches every category via
        // TemplateResolver's matchesRoundAnyCategory), so that check is
        // almost always true and tells the tenant nothing. The real
        // question is whether THIS transaction will actually go through
        // more than one round — same condition DraftEmailJob already uses
        // to decide whether to generate round 2/3 previews at all.
        $hasCadenceRounds = !$input->isFastTrack()
            && UnitPlatform::gateEnabled($input->deploymentId, 'client_cadence', true);

        $output = [
            'template_id'        => $template['id']               ?? null,
            'template_name'      => $template['name']             ?? 'Generic Response',
            'tone'               => $template['tone']             ?? 'Professional, concise',
            'subject_template'   => $template['subject_template'] ?? 'Action Required: {{asset}}',
            'body_template'      => $template['body_template']    ?? '',
            'approval_required'  => $template['approval_required'] ?? true,
            'low_confidence'     => !empty($memory['low_confidence_warning']),
            'has_cadence_rounds' => $hasCadenceRounds,
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
            UnitPlatform::setStatus($this->txId, 'blocked', $e->getMessage());
            UnitPlatform::log('ava', $this->txId, 'billing_blocked', ['code' => $e->billingCode, 'reason' => $e->getMessage()], 'warning');
            $this->delete();
            return;
        }
        UnitPlatform::setStatus($this->txId, 'failed', $e->getMessage());
        UnitPlatform::log('ava', $this->txId, 'job_failed', [
            'job' => 'SelectTemplateJob', 'error' => $e->getMessage(),
        ], 'error');
    }
}

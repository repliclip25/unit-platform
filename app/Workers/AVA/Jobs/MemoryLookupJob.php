<?php

namespace App\Workers\AVA\Jobs;

use App\Platform\SDK\UnitPlatform;
use App\Platform\SDK\WorkerOutput;
use App\Platform\Services\ClaudeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MemoryLookupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 90;

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function __construct(public string $txId) {}

    public function handle(ClaudeService $claude): void
    {
        $input = UnitPlatform::getInput($this->txId);
        $claude->configure($input->modelFor('memory'), $input->userId, $input->workerSlug);
        UnitPlatform::setStatus($this->txId, 'memory_lookup');

        $readOutput = $input->stage('read');

        // Build keyword set from the read-stage output + raw email subject/from
        $raw         = $input->raw;
        $questions   = $readOutput['questions_for_memory_lookup'] ?? [];
        $summary     = ($readOutput['plain_english_summary'] ?? '') . ' ' . ($readOutput['action_needed'] ?? '');
        $emailFrom   = $raw['fast_track_from'] ?? '';
        $emailSubj   = $raw['fast_track_subject'] ?? '';

        $keywordSource = strtolower(implode(' ', array_merge($questions, [$summary, $emailFrom, $emailSubj])));
        // Extract meaningful words (4+ chars, not stopwords)
        $stopwords = ['that','this','with','from','have','will','your','what','which','does','been','they','them','their','about','into','than','then','when','where','also','some','very','just','more','only','such','same','each','most'];
        preg_match_all('/\b[a-z0-9][a-z0-9\.\-]{3,}\b/', $keywordSource, $matches);
        $keywords = array_values(array_diff(array_unique($matches[0]), $stopwords));

        // Pre-filter memory to top matches — caps prompt size regardless of tenant data volume
        $memory = [
            'clients'  => $this->filterRecords($input->memory['clients']  ?? [], $keywords, 15),
            'contacts' => $this->filterRecords($input->memory['contacts'] ?? [], $keywords, 20),
            'assets'   => $this->filterRecords($input->memory['assets']   ?? [], $keywords, 20),
            'ava_rules'=> $this->prepareRules($input->memory['rules'] ?? []),
        ];

        $system = 'You are Ava, UNIT\'s Subscription & Renewal Coordinator. Return valid JSON only. No extra text. '
            . 'When selecting an ava_rule, prefer rules with priority Critical > High > Medium > Low. '
            . 'Persona-specific rules (is_platform=false) take precedence over platform rules (is_platform=true) when both could apply.';

        $prompt = <<<PROMPT
Using the extracted email information and the memory tables below, find who owns this asset and how it should be handled.

Return JSON:
{
  "asset": "",
  "matched_client": "",
  "primary_contact_name": "",
  "primary_contact_email": "",
  "related_project_or_service": "",
  "client_preference": "",
  "ava_rule": "",
  "confidence": 0,
  "missing_information": []
}

EXTRACTED EMAIL CONTEXT:
{$this->jsonPretty($readOutput)}

MEMORY TABLES:
{$this->jsonPretty($memory)}
PROMPT;

        $output = $claude->ask($system, $prompt, $input->maxTokens('memory', 768), $this->txId, 'memory');

        // Low confidence — flag but continue pipeline
        $confidence      = $output['confidence'] ?? 0;
        $lowConfRuleId   = $this->findLowConfidenceRuleId($input->memory['rules'] ?? []);
        if ($confidence < 70) {
            UnitPlatform::log('ava', $this->txId, 'low_confidence_flagged', [
                'confidence' => $confidence,
                'rule'       => $lowConfRuleId,
                'action'     => 'continuing_with_draft',
            ], 'warning');

            $output['low_confidence_warning'] = "AVA confidence is {$confidence}%. "
                . "Client/asset match is uncertain. Please verify before sending.";
        }

        UnitPlatform::commitOutput($this->txId, new WorkerOutput(
            stage:  'memory',
            status: 'memory_lookup',
            data:   $output,
        ));

        UnitPlatform::log('ava', $this->txId, 'memory_matched', $output);

        // Best-effort memory contributions — never let this block the pipeline
        try {
            if (!empty($output['primary_contact_email']) && !empty($output['primary_contact_name'])) {
                UnitPlatform::contributeMemory($this->txId, 'contacts', [
                    'name'  => $output['primary_contact_name'],
                    'email' => $output['primary_contact_email'],
                ]);
            }
            if (!empty($output['asset'])) {
                UnitPlatform::contributeMemory($this->txId, 'assets', [
                    'name' => $output['asset'],
                ]);
            }
        } catch (\Throwable $e) {
            UnitPlatform::log('ava', $this->txId, 'memory_contribute_skipped', [
                'error' => $e->getMessage(),
            ], 'warning');
        }

        LogTransactionJob::dispatch($this->txId)->onQueue($input->queue);
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
            'job' => 'MemoryLookupJob', 'error' => $e->getMessage(),
        ], 'error');
    }

    /**
     * Sort rules by priority (Critical → High → Medium → Low), persona rules
     * before platform rules within the same priority tier, then cap at 10.
     * Sends only condition + action + priority + rule_id to Claude — drops DB internals.
     */
    private function prepareRules(array $rules): array
    {
        $order = ['Critical' => 0, 'High' => 1, 'Medium' => 2, 'Low' => 3];

        usort($rules, function ($a, $b) use ($order) {
            $pa = $order[$this->ruleField($a, 'priority', 'Low')] ?? 3;
            $pb = $order[$this->ruleField($b, 'priority', 'Low')] ?? 3;
            if ($pa !== $pb) return $pa <=> $pb;
            // Within same priority tier: persona rules (is_platform=false) before platform rules
            $ia = (int) $this->ruleField($a, 'is_platform', 1);
            $ib = (int) $this->ruleField($b, 'is_platform', 1);
            return $ia <=> $ib;
        });

        return array_map(fn($r) => [
            'rule_id'    => $this->ruleField($r, 'rule_id',    ''),
            'condition'  => $this->ruleField($r, 'condition',  ''),
            'action'     => $this->ruleField($r, 'action',     ''),
            'priority'   => $this->ruleField($r, 'priority',   'Medium'),
            'is_platform'=> (bool) $this->ruleField($r, 'is_platform', true),
        ], array_slice($rules, 0, 10));
    }

    /**
     * Find the rule_id of the low-confidence / human-confirmation rule for this deployment.
     * Matches on condition keywords; falls back to 'AVA-006' if none found.
     */
    private function findLowConfidenceRuleId(array $rules): string
    {
        foreach ($rules as $r) {
            $condition = strtolower($this->ruleField($r, 'condition', ''));
            if (str_contains($condition, 'confidence')) {
                return $this->ruleField($r, 'rule_id', 'AVA-006');
            }
        }
        return 'AVA-006';
    }

    /** Read a field from a rule that may be stdClass (DB row) or array. */
    private function ruleField(mixed $rule, string $field, mixed $default): mixed
    {
        if (is_object($rule)) return $rule->{$field} ?? $default;
        return $rule[$field] ?? $default;
    }

    /**
     * Score each record against keywords, return top $limit by score.
     * Records with zero keyword hits are still included if total count <= $limit,
     * ensuring small tenants always have full context.
     */
    private function filterRecords(array $records, array $keywords, int $limit): array
    {
        if (count($records) <= $limit) return $records;
        if (empty($keywords)) return array_slice($records, 0, $limit);

        $scored = [];
        foreach ($records as $i => $rec) {
            $haystack = strtolower(json_encode($rec));
            $score    = 0;
            foreach ($keywords as $kw) {
                if (str_contains($haystack, $kw)) $score++;
            }
            $scored[] = ['rec' => $rec, 'score' => $score, 'i' => $i];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score'] ?: $a['i'] <=> $b['i']);

        return array_column(array_slice($scored, 0, $limit), 'rec');
    }

    private function jsonPretty(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}

<?php

namespace App\Platform\SDK;

/**
 * Output a worker job commits back to UNIT after completing a pipeline stage.
 */
final class WorkerOutput
{
    public function __construct(
        public readonly string  $stage,          // 'read' | 'classify' | 'memory' | 'template' | 'draft' | 'push'
        public readonly string  $status,         // transaction status after this stage
        public readonly array   $data,           // the stage output payload
        public readonly ?string $category    = null,  // shortcut: also writes transactions.category
        public readonly ?string $priority    = null,  // shortcut: also writes transactions.priority
        public readonly ?string $gmailDraftId = null, // shortcut: also writes transactions.gmail_draft_id
    ) {}
}

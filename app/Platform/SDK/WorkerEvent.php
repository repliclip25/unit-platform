<?php

namespace App\Platform\SDK;

/**
 * An event a worker emits at a pipeline stage for other workers to react to.
 * UNIT acts as the broker — the emitting worker never knows who subscribes.
 *
 * Example:
 *   UnitPlatform::emit($txId, new WorkerEvent('renewal.classified', [
 *       'category' => 'Domain Renewal',
 *       'asset'    => 'yourdomain.com',
 *       'priority' => 'High',
 *   ]));
 */
final class WorkerEvent
{
    public function __construct(
        public readonly string $name,     // dot-notation: 'renewal.classified', 'renewal.draft_ready'
        public readonly array  $payload,  // data other workers need to act on this event
    ) {}
}

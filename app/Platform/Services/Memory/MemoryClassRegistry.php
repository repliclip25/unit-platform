<?php

namespace App\Platform\Services\Memory;

/**
 * The platform's memory classes — one per standalone memory bundle. Each
 * connects to a worker only through that worker's contract (memoryRequirements()
 * for AVA-shaped classes; a bespoke formula for classes that don't fit that
 * shape, like Brand Memory), never through deployment status. Add an entry
 * here whenever a worker (built or presale-stage) gets its own memory bundle.
 */
class MemoryClassRegistry
{
    private const CLASSES = [
        'ava' => [
            'name'    => 'AVA Memory',
            'role'    => 'Renewal Coordinator',
            'desc'    => 'Clients, contacts, and assets AVA reads when it drafts renewals — start now, connect Gmail whenever you\'re ready.',
            'icon'    => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'badge'   => 'Live worker',
            'service' => AvaMemoryCoverageService::class,
        ],
        'brand-video' => [
            'name'    => 'Brand Memory',
            'role'    => 'Brand Video Agent',
            'desc'    => 'Business profile and Drive-hosted assets the brand-video worker will use once it launches.',
            'icon'    => 'M15 10l4.55-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.45.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
            'badge'   => 'Early access',
            'service' => BrandMemoryCoverageService::class,
        ],
    ];

    public static function all(): array
    {
        return self::CLASSES;
    }

    public static function get(string $slug): ?array
    {
        return self::CLASSES[$slug] ?? null;
    }

    public static function slugs(): array
    {
        return array_keys(self::CLASSES);
    }

    /**
     * Standard shape regardless of memory class: score, complete, total,
     * needed, healthy (plus any class-specific extras, e.g. Brand Memory's
     * `checklist`). Unknown slugs score as empty rather than throwing.
     */
    public static function score(string $slug, int $userId): array
    {
        $class = self::CLASSES[$slug]['service'] ?? null;

        if (!$class) {
            return ['score' => 0, 'complete' => 0, 'total' => 0, 'needed' => 0, 'healthy' => false];
        }

        return $class::score($userId);
    }
}

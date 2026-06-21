<?php

namespace App\Platform\Services;

use App\Platform\Contracts\WorkerContract;
use App\Workers\AVA\AvaWorker;

/**
 * Maps worker slugs to their WorkerContract implementation.
 * Add a new entry here when a new worker blueprint is built.
 */
class WorkerRegistry
{
    private static array $map = [
        'ava' => AvaWorker::class,
    ];

    public static function resolve(string $slug): ?WorkerContract
    {
        $class = self::$map[$slug] ?? null;
        return $class ? new $class() : null;
    }

    /** All registered workers — used by marketplace listing and deploy wizard. */
    public static function all(): array
    {
        return array_map(fn($class) => new $class(), self::$map);
    }

    public static function slugs(): array
    {
        return array_keys(self::$map);
    }
}

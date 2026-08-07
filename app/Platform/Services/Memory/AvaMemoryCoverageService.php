<?php

namespace App\Platform\Services\Memory;

use App\Platform\Services\WorkerRegistry;

/**
 * AVA's memory class, standalone from deployment — unlike MemoryHealthService
 * (which only scores tenants with an active AVA deployment), this reads AVA's
 * memoryRequirements() straight off the contract so a tenant training memory
 * before ever deploying AVA still gets a real coverage number.
 */
class AvaMemoryCoverageService
{
    public static function score(int $userId): array
    {
        $requirements = WorkerRegistry::resolve('ava')->memoryRequirements();

        return MemoryCoverageCalculator::scoreClients($userId, $requirements);
    }
}

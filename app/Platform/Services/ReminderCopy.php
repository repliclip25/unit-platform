<?php

namespace App\Platform\Services;

/**
 * Shared escalation logic for gate reminders — the same tone progression
 * applies whether it's AVA nudging a tenant to approve a draft or confirm
 * payment, or a future worker's own gate. Callers supply the wording per
 * tone; this only decides which tone an attempt number maps to.
 */
class ReminderCopy
{
    public const MAX_ATTEMPTS = 4; // after this many, nudging pauses — see Transaction::nudging_paused_at

    public static function tone(int $attemptNumber): string
    {
        return match (true) {
            $attemptNumber <= 1 => 'gentle',
            $attemptNumber === 2 => 'direct',
            default => 'urgent',
        };
    }
}

<?php

namespace App\Console\Commands;

use App\Http\Controllers\ProfileController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeScheduledDeletions extends Command
{
    protected $signature   = 'users:purge-deletions';
    protected $description = 'Hard-delete accounts whose 30-day grace period has expired.';

    public function handle(): int
    {
        $expired = DB::table('users')
            ->whereNotNull('deletion_requested_at')
            ->where('deletion_requested_at', '<=', now()->subDays(30))
            ->pluck('id');

        if ($expired->isEmpty()) {
            $this->info('No accounts due for deletion.');
            return 0;
        }

        $this->info("Purging {$expired->count()} account(s)...");

        foreach ($expired as $userId) {
            ProfileController::hardDelete($userId);
            $this->line("  Deleted user #{$userId}");
        }

        $this->info('Done.');
        return 0;
    }
}

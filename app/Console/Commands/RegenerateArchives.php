<?php

namespace App\Console\Commands;

use App\Workers\AVA\Jobs\ArchiveEvidenceJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RegenerateArchives extends Command
{
    protected $signature   = 'archives:regenerate';
    protected $description = 'Re-run ArchiveEvidenceJob for every transaction that already has an archive on record — for picking up PDF template changes (header, timestamps, styling) on old archives.';

    public function handle(): int
    {
        $txIds = DB::table('transactions')->whereNotNull('archive_output')->pluck('tx_id');

        if ($txIds->isEmpty()) {
            $this->info('No archived transactions found.');
            return 0;
        }

        $this->info("Regenerating {$txIds->count()} archive(s)...");

        $failed = 0;
        foreach ($txIds as $txId) {
            try {
                (new ArchiveEvidenceJob($txId))->handle();
                $this->line("  {$txId} OK");
            } catch (\Throwable $e) {
                $failed++;
                $this->error("  {$txId} FAILED: {$e->getMessage()}");
            }
        }

        $this->info($failed ? "Done — {$failed} failure(s), see above." : 'Done — all regenerated.');
        return $failed ? 1 : 0;
    }
}

<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class UnitNotifier
{
    public static function workerDeployed(int $deploymentId): void
    {
        try {
            $dep  = DB::table('worker_deployments')->where('id', $deploymentId)->first();
            $user = $dep ? DB::table('users')->where('id', $dep->user_id)->first() : null;
            if (!$user || !$dep) return;

            EmailDispatcher::send('worker_deployed', $user->email, $user->name, $user->id, [
                '{worker_name}' => $dep->name,
                '{worker_slug}' => $dep->worker_slug,
            ]);
        } catch (\Throwable $e) {
            Log::error('[UnitNotifier] workerDeployed failed', ['dep' => $deploymentId, 'error' => $e->getMessage()]);
        }
    }

    public static function adminAlert(string $subject, string $message): void
    {
        try {
            Mail::raw($message, fn($m) => $m
                ->to(config('services.unit.admin_email'), config('services.unit.noreply_name') . ' Admin')
                ->subject('[UNIT Alert] ' . $subject)
            );
        } catch (\Throwable $e) {
            Log::error('[UnitNotifier] adminAlert failed', ['error' => $e->getMessage()]);
        }
    }

    public static function draftReady(string $txId, array $payload): void
    {
        try {
            $tx   = DB::table('transactions')->where('tx_id', $txId)->first();
            $user = $tx ? DB::table('users')->where('id', $tx->user_id)->first() : null;
            if (!$user || !$tx) return;

            $asset      = $payload['asset']['name']    ?? 'Unknown asset';
            $client     = $payload['client']['name']   ?? 'Unknown client';
            $draftSubj  = $payload['draft']['subject'] ?? 'Renewal';
            $confidence = $payload['ava']['confidence'] ?? null;
            $appUrl     = config('app.url');

            EmailDispatcher::send('draft_ready', $user->email, $user->name, $user->id, [
                '{draft_subject}' => $draftSubj,
                '{client}'        => $client,
                '{asset}'         => $asset,
                '{confidence}'    => $confidence ?? '—',
                '{tx_id}'         => $txId,
            ]);
        } catch (\Throwable $e) {
            Log::error('[UnitNotifier] draftReady failed', ['tx_id' => $txId, 'error' => $e->getMessage()]);
        }
    }
}

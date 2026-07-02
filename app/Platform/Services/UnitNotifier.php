<?php

namespace App\Platform\Services;

use App\Mail\WorkerDeployed;
use App\Mail\DraftReady;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Central communication layer for the UNIT platform.
 *
 * Workers and platform events key into this service to send
 * notifications. Nothing calls Mail:: directly except this class
 * and the auth controllers (which own their own lifecycle emails).
 *
 * All methods are fire-and-forget (queued). They log on failure
 * but never throw — a notification failure must never break a pipeline.
 */
class UnitNotifier
{
    /**
     * Notify tenant that their worker has been deployed.
     */
    public static function workerDeployed(int $deploymentId): void
    {
        try {
            $dep  = DB::table('worker_deployments')->where('id', $deploymentId)->first();
            $user = $dep ? DB::table('users')->where('id', $dep->user_id)->first() : null;
            if (!$user || !$dep) return;

            $contract = \App\Platform\Services\WorkerRegistry::resolve($dep->worker_slug);
            $employee = \App\Platform\Services\WorkerRegistry::isNull($contract) ? [] : $contract->employee();

            Mail::to($user->email)->send(new WorkerDeployed(
                name:           $user->name,
                workerName:     $dep->name,
                workerSlug:     $dep->worker_slug,
                workerDesc:     $employee['title'] ?? '',
                deploymentId:   $deploymentId,
                trialEndsAt:    now()->addDays(14)->format('F j, Y'),
            ));
        } catch (\Throwable $e) {
            Log::error('[UnitNotifier] workerDeployed failed', ['dep' => $deploymentId, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Alert admin of a platform-level error or critical event.
     * Fire-and-forget — never throws.
     */
    public static function adminAlert(string $subject, string $message): void
    {
        try {
            Mail::raw($message, fn($m) => $m
                ->to('hello@unit.report', 'UNIT Admin')
                ->subject('[UNIT Alert] ' . $subject)
            );
        } catch (\Throwable $e) {
            Log::error('[UnitNotifier] adminAlert failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Notify tenant that AVA has a draft ready for review.
     *
     * Called by PushToGmailJob after renewal.draft_ready emits.
     */
    public static function draftReady(string $txId, array $payload): void
    {
        try {
            $tx   = DB::table('transactions')->where('tx_id', $txId)->first();
            $user = $tx ? DB::table('users')->where('id', $tx->user_id)->first() : null;
            if (!$user || !$tx) return;

            Mail::to($user->email)->send(new DraftReady(
                name:          $user->name,
                txId:          $txId,
                asset:         $payload['asset']['name'] ?? 'Unknown asset',
                client:        $payload['client']['name'] ?? 'Unknown client',
                contactName:   $payload['contact']['name'] ?? null,
                subject:       $payload['draft']['subject'] ?? null,
                confidence:    $payload['ava']['confidence'] ?? null,
                fastTrack:     $payload['draft']['fast_track'] ?? false,
            ));
        } catch (\Throwable $e) {
            Log::error('[UnitNotifier] draftReady failed', ['tx_id' => $txId, 'error' => $e->getMessage()]);
        }
    }
}

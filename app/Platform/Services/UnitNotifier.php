<?php

namespace App\Platform\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Platform\Services\EmailDispatcher;

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

    // Minimum confidence to send a draft-ready email.
    // Below this threshold the client/asset match is too uncertain to be actionable.
    private const DRAFT_NOTIFY_CONFIDENCE_FLOOR = 50;

    public static function draftReady(string $txId, array $payload): void
    {
        try {
            $tx   = DB::table('transactions')->where('tx_id', $txId)->first();
            $user = $tx ? DB::table('users')->where('id', $tx->user_id)->first() : null;
            if (!$user || !$tx) return;

            $asset      = $payload['asset']['name']    ?? 'Unknown asset';
            $client     = $payload['client']['name']   ?? 'Unknown client';
            $draftSubj  = $payload['draft']['subject'] ?? 'Renewal';
            $confidence = isset($payload['ava']['confidence']) ? (int) $payload['ava']['confidence'] : null;

            // Skip notification when confidence is too low to be actionable.
            // The draft still exists in the dashboard — we just don't send a midnight
            // email the broker can't act on without first fixing their memory data.
            if ($confidence !== null && $confidence < self::DRAFT_NOTIFY_CONFIDENCE_FLOOR) {
                Log::info('[UnitNotifier] draftReady skipped — confidence below floor', [
                    'tx_id'      => $txId,
                    'confidence' => $confidence,
                    'floor'      => self::DRAFT_NOTIFY_CONFIDENCE_FLOOR,
                ]);
                return;
            }

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

    /**
     * Fires the "Ava just handled your first renewal" email exactly once —
     * when the first real (non-fast-track) transaction completes for this user.
     */
    public static function maybeFirstRealRenewal(string $txId): void
    {
        try {
            $tx   = DB::table('transactions')->where('tx_id', $txId)->first();
            $user = $tx ? DB::table('users')->where('id', $tx->user_id)->first() : null;

            if (!$user || !$tx) {
                Log::warning('[UnitNotifier] maybeFirstRealRenewal: tx or user not found', ['tx_id' => $txId]);
                return;
            }

            // Only fire once ever
            $alreadySent = DB::table('tenant_email_log')
                ->where('user_id', $user->id)
                ->where('template_key', 'ava_first_real_renewal')
                ->exists();

            if ($alreadySent) {
                Log::info('[UnitNotifier] maybeFirstRealRenewal: already sent, skipping', ['user_id' => $user->id]);
                return;
            }

            // Verify template exists before attempting send
            $tplExists = DB::table('platform_email_templates')
                ->where('key', 'ava_first_real_renewal')
                ->where('active', true)
                ->exists();

            if (!$tplExists) {
                Log::warning('[UnitNotifier] maybeFirstRealRenewal: template missing from DB — run php artisan migrate --force', ['tx_id' => $txId]);
                return;
            }

            Log::info('[UnitNotifier] maybeFirstRealRenewal: sending to ' . $user->email, ['tx_id' => $txId, 'user_id' => $user->id]);

            // Route through EmailDispatcher so SMTP config, logging, and error handling are consistent
            EmailDispatcher::send('ava_first_real_renewal', $user->email, $user->name, $user->id);

        } catch (\Throwable $e) {
            Log::error('[UnitNotifier] maybeFirstRealRenewal failed', ['tx_id' => $txId, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }
}

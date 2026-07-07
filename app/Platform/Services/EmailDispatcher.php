<?php

namespace App\Platform\Services;

use App\Http\Controllers\AdminMessagingController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailDispatcher
{
    /**
     * Send a template-driven email and log it.
     *
     * @param  string       $key          Template key (e.g. 'draft_ready')
     * @param  string       $toEmail      Recipient address
     * @param  string       $toName       Recipient name
     * @param  int|null     $userId       User ID for log (null for non-tenant emails)
     * @param  array        $vars         Extra {placeholder} → value replacements
     * @param  array        $fallback     Fallback template data if key not in DB
     */
    // Keys that must only ever be sent once per user — dedup enforced at dispatcher level
    private const SEND_ONCE_KEYS = [
        'welcome_tenant',
        'referral_welcome_tenant',
        'ava_worker_selected',
        'ava_first_real_renewal',
        'ava_abandon_no_gmail',
        'ava_abandon_no_clients',
        'ava_abandon_no_fasttrack',
    ];

    public static function send(
        string  $key,
        string  $toEmail,
        string  $toName,
        ?int    $userId,
        array   $vars    = [],
        array   $fallback = []
    ): void {
        try {
            // Hard dedup for one-time emails — prevent double-sends regardless of call site
            if ($userId && in_array($key, self::SEND_ONCE_KEYS)) {
                $alreadySent = DB::table('tenant_email_log')
                    ->where('user_id', $userId)
                    ->where('template_key', $key)
                    ->where('status', 'sent')
                    ->exists();

                if ($alreadySent) {
                    Log::info("EmailDispatcher: dedup blocked [{$key}]", ['user_id' => $userId]);
                    return;
                }
            }

            $tpl = AdminMessagingController::getTemplate($key, $fallback);
            if (!$tpl) {
                Log::warning("EmailDispatcher: no template for key [{$key}]");
                return;
            }

            $appUrl = config('app.url');
            $baseVars = array_merge([
                '{name}'    => $toName,
                '{app_url}' => $appUrl,
            ], $vars);

            $subject = str_replace(array_keys($baseVars), array_values($baseVars), $tpl->subject);
            $body    = str_replace(array_keys($baseVars), array_values($baseVars), $tpl->body);
            $from    = $tpl->from_name ?? 'UNIT Universe';

            Mail::raw($body, fn($m) => $m
                ->to($toEmail, $toName)
                ->subject($subject)
                ->replyTo(config('services.unit.noreply_email'), $from)
            );

            self::log($key, $userId, $toEmail, $subject, 'sent');

        } catch (\Throwable $e) {
            Log::error("EmailDispatcher: send failed [{$key}]", [
                'to'    => $toEmail,
                'error' => $e->getMessage(),
            ]);
            self::log($key, $userId, $toEmail, $key, 'failed');
        }
    }

    /**
     * Log a sent email to tenant_email_log.
     */
    public static function log(
        string  $key,
        ?int    $userId,
        string  $toEmail,
        string  $subject,
        string  $status = 'sent',
        ?string $resendId = null
    ): void {
        try {
            DB::table('tenant_email_log')->insert([
                'user_id'      => $userId,
                'template_key' => $key,
                'resend_id'    => $resendId,
                'to_email'     => $toEmail,
                'subject'      => $subject,
                'status'       => $status,
                'sent_at'      => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("EmailDispatcher: log failed [{$key}]", ['error' => $e->getMessage()]);
        }
    }
}

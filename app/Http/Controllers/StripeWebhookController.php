<?php

namespace App\Http\Controllers;

use App\Platform\Services\UsageGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): \Illuminate\Http\Response
    {
        $secret    = config('cashier.webhook.secret');
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook: invalid signature');
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            Log::warning('Stripe webhook: parse error', ['error' => $e->getMessage()]);
            return response('Webhook error', 400);
        }

        Log::info('Stripe webhook received', ['type' => $event->type]);

        match ($event->type) {
            'invoice.payment_failed'        => $this->onPaymentFailed($event->data->object),
            'customer.subscription.updated' => $this->onSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->onSubscriptionDeleted($event->data->object),
            'invoice.paid'                  => $this->onInvoicePaid($event->data->object),
            default                         => null,
        };

        return response('OK', 200);
    }

    // ── Payment failed → mark past_due ───────────────────────────────────────

    private function onPaymentFailed(object $invoice): void
    {
        $subId = $invoice->subscription ?? null;
        if (!$subId) return;

        $rows = DB::table('deployment_billing')->where('stripe_subscription_id', $subId)->get();

        foreach ($rows as $billing) {
            DB::table('deployment_billing')->where('id', $billing->id)->update([
                'status'         => 'past_due',
                'past_due_since' => now(),
                'updated_at'     => now(),
            ]);
            Log::warning('Stripe: payment failed → past_due', ['deployment_id' => $billing->deployment_id]);
            $this->notifyTenant($billing->user_id, 'payment_failed');
        }
    }

    // ── Subscription status updated ───────────────────────────────────────────

    private function onSubscriptionUpdated(object $subscription): void
    {
        $statusMap = [
            'active'     => 'active',
            'past_due'   => 'past_due',
            'paused'     => 'paused',
            'canceled'   => 'canceled',
            'unpaid'     => 'past_due',
            'incomplete' => 'past_due',
        ];

        $mapped = $statusMap[$subscription->status] ?? null;
        if (!$mapped) return;

        $rows = DB::table('deployment_billing')->where('stripe_subscription_id', $subscription->id)->get();

        foreach ($rows as $billing) {
            $update = ['status' => $mapped, 'updated_at' => now()];

            if ($mapped === 'past_due' && !$billing->past_due_since) {
                $update['past_due_since'] = now();
            }
            if ($mapped === 'active') {
                $update['past_due_since'] = null;
            }

            DB::table('deployment_billing')->where('id', $billing->id)->update($update);

            Log::info('Stripe: subscription updated', [
                'deployment_id' => $billing->deployment_id,
                'status'        => $mapped,
            ]);

            if ($mapped === 'past_due') {
                $this->notifyTenant($billing->user_id, 'payment_failed');
            }
        }
    }

    // ── Subscription canceled ─────────────────────────────────────────────────

    private function onSubscriptionDeleted(object $subscription): void
    {
        $rows = DB::table('deployment_billing')->where('stripe_subscription_id', $subscription->id)->get();

        foreach ($rows as $billing) {
            DB::table('deployment_billing')->where('id', $billing->id)->update([
                'status'     => 'canceled',
                'updated_at' => now(),
            ]);
            Log::warning('Stripe: subscription canceled', ['deployment_id' => $billing->deployment_id]);
            $this->notifyTenant($billing->user_id, 'subscription_canceled');
        }
    }

    // ── Invoice paid → restore active, auto-unblock if was PAYMENT_PAST_DUE ──

    private function onInvoicePaid(object $invoice): void
    {
        $subId = $invoice->subscription ?? null;
        if (!$subId) return;

        $billing = DB::table('deployment_billing')->where('stripe_subscription_id', $subId)->first();
        if (!$billing) return;

        DB::table('deployment_billing')->where('stripe_subscription_id', $subId)->update([
            'status'         => 'active',
            'past_due_since' => null,
            'updated_at'     => now(),
        ]);

        // Auto-unblock tenant if they were hard-blocked for PAYMENT_PAST_DUE
        $user = DB::table('users')->where('id', $billing->user_id)->first();
        if ($user?->block_policy_code === 'PAYMENT_PAST_DUE') {
            UsageGuard::unblockUser($billing->user_id);
            $this->notifyTenant($billing->user_id, 'payment_resolved');
            Log::info('Stripe: invoice paid → user auto-unblocked', ['user_id' => $billing->user_id]);
        }

        Log::info('Stripe: invoice paid → active', ['sub_id' => $subId]);
    }

    // ── Email tenant ──────────────────────────────────────────────────────────

    private function notifyTenant(int $userId, string $event): void
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) return;

        $templates = [
            'payment_failed' => [
                'subject' => 'Action Required: Payment failed on your UNIT account',
                'body'    => "Hi {$user->name},\n\nA payment on your UNIT account failed and your worker processing has been paused.\n\nPlease update your payment method to resume:\n" . url('/billing') . "\n\nUNIT Platform",
            ],
            'subscription_canceled' => [
                'subject' => 'Your UNIT worker subscription has been canceled',
                'body'    => "Hi {$user->name},\n\nYour subscription has been canceled and email processing has stopped.\n\nYou can reactivate anytime — all your data is preserved:\n" . url('/billing') . "\n\nUNIT Platform",
            ],
            'payment_resolved' => [
                'subject' => 'Your UNIT account has been reactivated',
                'body'    => "Hi {$user->name},\n\nYour payment was received and your account is now active again. Processing has resumed automatically.\n\nUNIT Platform",
            ],
        ];

        $tpl = $templates[$event] ?? null;
        if (!$tpl) return;

        try {
            Mail::raw($tpl['body'], fn($m) => $m->to($user->email)->subject($tpl['subject']));
        } catch (\Throwable $e) {
            Log::error('Stripe webhook: email failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
    }
}

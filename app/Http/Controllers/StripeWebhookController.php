<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AdminMessagingController;
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

    // ── Resolve tenant user from Stripe subscription ID ──────────────────────
    // The unified billing model stores stripe_subscription_id on `users` (one
    // master sub per tenant). All deployment_billing rows for that user share
    // that subscription; individual items are tracked via stripe_subscription_item_id.

    private function userBySubId(string $subId): ?object
    {
        return DB::table('users')->where('stripe_subscription_id', $subId)->first();
    }

    private function billingRowsByUserId(int $userId)
    {
        return DB::table('deployment_billing')
            ->where('user_id', $userId)
            ->whereNotIn('status', ['trial', 'canceled'])
            ->get();
    }

    // ── Payment failed → mark all tenant deployments past_due ────────────────

    private function onPaymentFailed(object $invoice): void
    {
        $subId = $invoice->subscription ?? null;
        if (!$subId) return;

        $user = $this->userBySubId($subId);
        if (!$user) {
            Log::warning('Stripe: payment_failed — no user found for sub', ['sub_id' => $subId]);
            return;
        }

        $rows = $this->billingRowsByUserId($user->id);

        foreach ($rows as $billing) {
            DB::table('deployment_billing')->where('id', $billing->id)->update([
                'status'         => 'past_due',
                'past_due_since' => now(),
                'updated_at'     => now(),
            ]);
            Log::warning('Stripe: payment failed → past_due', ['deployment_id' => $billing->deployment_id]);
        }

        $this->notifyTenant($user->id, 'payment_failed');
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

        $user = $this->userBySubId($subscription->id);
        if (!$user) {
            Log::info('Stripe: subscription.updated — no user found', ['sub_id' => $subscription->id]);
            return;
        }

        $rows = $this->billingRowsByUserId($user->id);

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
        }

        if ($mapped === 'past_due') {
            $this->notifyTenant($user->id, 'payment_failed');
        }
    }

    // ── Subscription canceled ─────────────────────────────────────────────────

    private function onSubscriptionDeleted(object $subscription): void
    {
        $user = $this->userBySubId($subscription->id);
        if (!$user) {
            Log::warning('Stripe: subscription.deleted — no user found', ['sub_id' => $subscription->id]);
            return;
        }

        // Get all active/past_due deployments for this tenant
        $rows = DB::table('deployment_billing')
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['trial', 'canceled'])
            ->get();

        foreach ($rows as $billing) {
            DB::table('deployment_billing')->where('id', $billing->id)->update([
                'status'     => 'canceled',
                'updated_at' => now(),
            ]);

            DB::table('worker_deployments')->where('id', $billing->deployment_id)->update([
                'status'     => 'stopped',
                'updated_at' => now(),
            ]);

            Log::warning('Stripe: subscription canceled → deployment stopped', [
                'deployment_id' => $billing->deployment_id,
            ]);
        }

        $this->notifyTenant($user->id, 'subscription_canceled');
    }

    // ── Invoice paid → restore active, auto-unblock if was PAYMENT_PAST_DUE ──

    private function onInvoicePaid(object $invoice): void
    {
        $subId = $invoice->subscription ?? null;
        if (!$subId) return;

        $user = $this->userBySubId($subId);
        if (!$user) {
            Log::info('Stripe: invoice.paid — no user found', ['sub_id' => $subId]);
            return;
        }

        DB::table('deployment_billing')
            ->where('user_id', $user->id)
            ->where('status', 'past_due')
            ->update([
                'status'         => 'active',
                'past_due_since' => null,
                'updated_at'     => now(),
            ]);

        // Auto-unblock if hard-blocked for payment failure
        if ($user->block_policy_code === 'PAYMENT_PAST_DUE') {
            UsageGuard::unblockUser($user->id);
            $this->notifyTenant($user->id, 'payment_resolved');
            Log::info('Stripe: invoice paid → user auto-unblocked', ['user_id' => $user->id]);
        }

        Log::info('Stripe: invoice paid → active', ['sub_id' => $subId, 'user_id' => $user->id]);
    }

    // ── Email tenant ──────────────────────────────────────────────────────────

    private function notifyTenant(int $userId, string $event): void
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) return;

        $keyMap = [
            'payment_failed'        => 'billing_payment_failed',
            'subscription_canceled' => 'billing_subscription_canceled',
            'payment_resolved'      => 'billing_payment_resolved',
        ];

        $key = $keyMap[$event] ?? null;
        if (!$key) return;

        $tpl = AdminMessagingController::getTemplate($key);
        if (!$tpl) return;

        $appUrl  = config('app.url');
        $subject = str_replace(['{name}', '{app_url}'], [$user->name, $appUrl], $tpl->subject);
        $body    = str_replace(['{name}', '{app_url}'], [$user->name, $appUrl], $tpl->body);

        try {
            Mail::raw($body, fn($m) => $m
                ->to($user->email, $user->name)
                ->subject($subject)
                ->replyTo(config('services.unit.noreply_email'), $tpl->from_name)
            );
        } catch (\Throwable $e) {
            Log::error('Stripe webhook: email failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\RecordInvoiceFromStripeAction;
use App\Models\Payment;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierController
{
    /**
     * Handle invoice.paid webhook events.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function handleInvoicePaid(array $payload): Response
    {
        return $this->recordPaidInvoice($payload);
    }

    /**
     * Handle invoice.payment_succeeded for compatibility.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function handleInvoicePaymentSucceeded(array $payload): Response
    {
        parent::handleInvoicePaymentSucceeded($payload);

        return $this->recordPaidInvoice($payload);
    }

    /**
     * Handle invoice.payment_failed / payment_failed style events.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function handleInvoicePaymentFailed(array $payload): Response
    {
        $invoice = $payload['data']['object'];

        if ($user = $this->getUserByStripeId($invoice['customer'] ?? null)) {
            Payment::query()->updateOrCreate(
                [
                    'stripe_invoice_id' => $invoice['id'],
                    'user_id' => $user->id,
                ],
                [
                    'stripe_payment_intent_id' => $invoice['payment_intent'] ?? null,
                    'amount' => (int) ($invoice['amount_due'] ?? 0),
                    'currency' => strtolower((string) ($invoice['currency'] ?? 'usd')),
                    'status' => 'failed',
                    'description' => $invoice['description'] ?? 'Failed subscription payment',
                    'paid_at' => null,
                    'metadata' => [
                        'attempt_count' => $invoice['attempt_count'] ?? null,
                        'next_payment_attempt' => $invoice['next_payment_attempt'] ?? null,
                    ],
                ]
            );
        }

        return $this->successMethod();
    }

    /**
     * Alias for payment_failed naming from the project requirements.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function handlePaymentFailed(array $payload): Response
    {
        return $this->handleInvoicePaymentFailed($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function recordPaidInvoice(array $payload): Response
    {
        $invoice = $payload['data']['object'];

        if ($user = $this->getUserByStripeId($invoice['customer'] ?? null)) {
            app(RecordInvoiceFromStripeAction::class)->handle($user, $invoice, 'paid');
        }

        return $this->successMethod();
    }
}

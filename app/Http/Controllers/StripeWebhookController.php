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
                    // Convert Cents to Dollars for Failed Payment
                    'amount' => $this->convertCentsToDollars($invoice['amount_due'] ?? 0),
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
     * Record Paid Invoice - Main Entry Point for Successful Payments
     *
     * @param  array<string, mixed>  $payload
     */
    protected function recordPaidInvoice(array $payload): Response
    {
        $invoice = $payload['data']['object'];

        if ($user = $this->getUserByStripeId($invoice['customer'] ?? null)) {
            // convert amount from cents to dollar
            // In payment record, store the amount in dollars (e.g., 9.99 instead of 999 cents)
            $this->recordPaymentWithCorrectAmount($user, $invoice);

            // Record Invoice via Action (Original Functionality Intact)
            app(RecordInvoiceFromStripeAction::class)->handle($user, $invoice, 'paid');
        }

        return $this->successMethod();
    }

    /**
     * NEW METHOD: Record Payment with Correct Amount
     *
     * @param  mixed  $user
     * @param  array<string, mixed>  $invoice
     */
    protected function recordPaymentWithCorrectAmount($user, array $invoice): void
    {
        //  Convert Cents to Dollars (e.g., 999 -> 9.99)
        $amountInDollars = $this->convertCentsToDollars($invoice['amount_paid'] ?? $invoice['amount_due'] ?? 0);
        $currency = strtolower((string) ($invoice['currency'] ?? 'usd'));

        // Payment Record Update/Create
        Payment::query()->updateOrCreate(
            [
                'stripe_invoice_id' => $invoice['id'],
                'user_id' => $user->id,
            ],
            [
                'stripe_payment_intent_id' => $invoice['payment_intent'] ?? null,
                'amount' => $amountInDollars, // Correct Amount Store
                'currency' => $currency,
                'status' => 'succeeded',
                'description' => $invoice['description'] ?? 'Subscription payment',
                'paid_at' => now(),
                'metadata' => [
                    'invoice_number' => $invoice['number'] ?? null,
                    'hosted_invoice_url' => $invoice['hosted_invoice_url'] ?? null,
                ],
            ]
        );
    }

    /**
     * NEW HELPER METHOD: Convert Cents to Dollars
     *
     * @param  int|float  $amountInCents
     * @return float
     */
    protected function convertCentsToDollars(int|float $amountInCents): float
    {
        // Stripe always sends amount in cents (e.g., 999 for $9.99)
        // Divide by 100 to get dollars
        return round($amountInCents / 100, 2);
    }
}

<?php

namespace App\Actions;

use App\Jobs\GenerateInvoicePdf;
use App\Jobs\SendPaymentReceipt;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RecordInvoiceFromStripeAction
{
    /**
     * @param  array<string, mixed>  $stripeInvoice
     */
    public function handle(User $user, array $stripeInvoice, string $status = 'paid'): Invoice
    {
        $stripeInvoiceId = $stripeInvoice['id'];

        // Convert Cents to Dollars
        $amountInCents = (int) ($stripeInvoice['amount_paid'] ?? $stripeInvoice['amount_due'] ?? 0);
        $amountInDollars = $this->convertCentsToDollars($amountInCents);

        $currency = strtolower((string) ($stripeInvoice['currency'] ?? 'usd'));
        $number = $stripeInvoice['number'] ?? ('INV-'.Str::upper(Str::random(8)));

        $priceId = data_get($stripeInvoice, 'lines.data.0.price.id')
            ?? data_get($stripeInvoice, 'lines.data.0.pricing.price_details.price');

        $plan = is_string($priceId) ? Plan::findByStripePriceId($priceId) : null;
        $interval = $plan && is_string($priceId) ? $plan->intervalForStripePriceId($priceId) : null;

        // Invoice Create/Update
        $invoice = Invoice::query()->updateOrCreate(
            ['stripe_invoice_id' => $stripeInvoiceId],
            [
                'user_id' => $user->id,
                'number' => $number,
                'amount' => $amountInDollars, // store in dollars (e.g., 9.99 instead of 999 cents)
                'currency' => $currency,
                'status' => $status,
                'plan_name' => $plan?->name,
                'billing_interval' => $interval,
                'invoice_date' => isset($stripeInvoice['created'])
                    ? Carbon::createFromTimestamp($stripeInvoice['created'])
                    : now(),
                'period_start' => isset($stripeInvoice['period_start'])
                    ? Carbon::createFromTimestamp($stripeInvoice['period_start'])
                    : null,
                'period_end' => isset($stripeInvoice['period_end'])
                    ? Carbon::createFromTimestamp($stripeInvoice['period_end'])
                    : null,
                'line_items' => $stripeInvoice['lines']['data'] ?? [],
            ]
        );

        //  Payment Create/Update
        $payment = Payment::query()->updateOrCreate(
            [
                'stripe_invoice_id' => $stripeInvoiceId,
                'user_id' => $user->id,
            ],
            [
                'invoice_id' => $invoice->id,
                'stripe_payment_intent_id' => $stripeInvoice['payment_intent'] ?? null,
                'amount' => $amountInDollars, // store in dollars (e.g., 9.99 instead of 999 cents)
                'currency' => $currency,
                'status' => $status === 'paid' ? 'succeeded' : $status,
                'description' => $plan
                    ? "{$plan->name} ({$interval})"
                    : ($stripeInvoice['description'] ?? 'Subscription payment'),
                'paid_at' => $status === 'paid' ? now() : null,
                'metadata' => [
                    'stripe_status' => $stripeInvoice['status'] ?? null,
                    'amount_in_cents' => $amountInCents, // Save in cents for reference or debugging
                ],
            ]
        );

        if ($status === 'paid') {
            GenerateInvoicePdf::dispatch($invoice);
            SendPaymentReceipt::dispatch($payment);
        }

        return $invoice;
    }

    /**
     *  NEW HELPER METHOD: Convert Cents to Dollars
     *
     * Stripe always sends amount in cents (e.g., 999 for $9.99)
     * This method converts it to dollars (e.g., 9.99)
     *
     * @param  int  $amountInCents
     * @return float
     */
    protected function convertCentsToDollars(int $amountInCents): float
    {
        return round($amountInCents / 100, 2);
    }
}

<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'invoice_id' => null,
            'stripe_payment_intent_id' => 'pi_'.Str::lower(Str::random(24)),
            'stripe_invoice_id' => 'in_'.Str::lower(Str::random(24)),
            'amount' => fake()->randomElement([999, 1999, 4999]),
            'currency' => 'usd',
            'status' => 'succeeded',
            'description' => 'Subscription payment',
            'paid_at' => now()->subDays(fake()->numberBetween(0, 40)),
            'metadata' => [],
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'failed',
            'paid_at' => null,
        ]);
    }

    public function forInvoice(Invoice $invoice): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $invoice->user_id,
            'invoice_id' => $invoice->id,
            'stripe_invoice_id' => $invoice->stripe_invoice_id,
            'amount' => $invoice->amount,
        ]);
    }
}

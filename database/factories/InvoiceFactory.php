<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'stripe_invoice_id' => 'in_'.Str::lower(Str::random(24)),
            'number' => 'INV-'.strtoupper(Str::random(8)),
            'amount' => fake()->randomElement([999, 1999, 4999, 9990]),
            'currency' => 'usd',
            'status' => 'paid',
            'plan_name' => fake()->randomElement(['Basic', 'Pro', 'Enterprise']),
            'billing_interval' => fake()->randomElement(['monthly', 'yearly']),
            'pdf_path' => null,
            'invoice_date' => now()->subDays(fake()->numberBetween(1, 60)),
            'period_start' => now()->subMonth(),
            'period_end' => now(),
            'line_items' => [],
        ];
    }
}

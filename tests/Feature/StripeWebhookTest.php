<?php

use App\Jobs\GenerateInvoicePdf;
use App\Jobs\SendPaymentReceipt;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

test('invoice paid webhook stores payment history and dispatches jobs', function () {
    Bus::fake();

    $plan = Plan::factory()->create([
        'stripe_monthly_price_id' => 'price_test_monthly',
    ]);

    $user = User::factory()->create([
        'stripe_id' => 'cus_test_123',
    ]);

    $payload = [
        'id' => 'evt_test_invoice_paid',
        'type' => 'invoice.paid',
        'data' => [
            'object' => [
                'id' => 'in_test_123',
                'customer' => 'cus_test_123',
                'number' => 'INV-WH-001',
                'amount_paid' => $plan->monthly_price,
                'amount_due' => $plan->monthly_price,
                'currency' => 'usd',
                'status' => 'paid',
                'created' => now()->timestamp,
                'period_start' => now()->subMonth()->timestamp,
                'period_end' => now()->timestamp,
                'payment_intent' => 'pi_test_123',
                'description' => 'Pro subscription',
                'lines' => [
                    'data' => [
                        [
                            'price' => ['id' => 'price_test_monthly'],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $this->postJson(route('cashier.webhook'), $payload)
        ->assertSuccessful();

    $this->assertDatabaseHas('invoices', [
        'user_id' => $user->id,
        'stripe_invoice_id' => 'in_test_123',
        'status' => 'paid',
    ]);

    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'stripe_invoice_id' => 'in_test_123',
        'status' => 'succeeded',
    ]);

    Bus::assertDispatched(GenerateInvoicePdf::class);
    Bus::assertDispatched(SendPaymentReceipt::class);
});

test('payment failed webhook stores failed payment', function () {
    $user = User::factory()->create([
        'stripe_id' => 'cus_failed_123',
    ]);

    $payload = [
        'id' => 'evt_test_payment_failed',
        'type' => 'invoice.payment_failed',
        'data' => [
            'object' => [
                'id' => 'in_failed_123',
                'customer' => 'cus_failed_123',
                'amount_due' => 2999,
                'currency' => 'usd',
                'payment_intent' => 'pi_failed_123',
                'description' => 'Failed charge',
                'attempt_count' => 1,
            ],
        ],
    ];

    $this->postJson(route('cashier.webhook'), $payload)
        ->assertSuccessful();

    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'stripe_invoice_id' => 'in_failed_123',
        'status' => 'failed',
    ]);
});

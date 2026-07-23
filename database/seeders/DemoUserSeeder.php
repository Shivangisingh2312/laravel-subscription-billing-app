<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@billing.test'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]
        );

        $subscriber = User::query()->updateOrCreate(
            ['email' => 'user@billing.test'],
            [
                'name' => 'Demo Subscriber',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => false,
                'stripe_id' => 'cus_demo_subscriber',
            ]
        );

        $freeUser = User::query()->updateOrCreate(
            ['email' => 'free@billing.test'],
            [
                'name' => 'Free User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => false,
            ]
        );

        $pro = Plan::query()->where('slug', 'pro')->firstOrFail();

        $subscription = $subscriber->subscriptions()->updateOrCreate(
            ['type' => 'default'],
            [
                'stripe_id' => 'demo_sub_'.Str::lower(Str::random(12)),
                'stripe_status' => 'active',
                'stripe_price' => $pro->stripe_monthly_price_id,
                'quantity' => 1,
                'trial_ends_at' => null,
                'ends_at' => null,
            ]
        );

        $subscription->items()->updateOrCreate(
            ['stripe_id' => 'si_demo_'.Str::lower(Str::random(10))],
            [
                'stripe_product' => 'prod_demo_pro',
                'stripe_price' => $pro->stripe_monthly_price_id,
                'quantity' => 1,
            ]
        );

        foreach (range(1, 4) as $index) {
            $invoice = Invoice::query()->updateOrCreate(
                ['number' => 'INV-DEMO-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT)],
                [
                    'user_id' => $subscriber->id,
                    'stripe_invoice_id' => 'in_demo_'.Str::lower(Str::random(14)).$index,
                    'amount' => $pro->monthly_price,
                    'currency' => 'usd',
                    'status' => 'paid',
                    'plan_name' => $pro->name,
                    'billing_interval' => 'monthly',
                    'invoice_date' => now()->subMonths(4 - $index),
                    'period_start' => now()->subMonths(5 - $index)->startOfMonth(),
                    'period_end' => now()->subMonths(4 - $index)->endOfMonth(),
                    'line_items' => [
                        [
                            'description' => 'Pro monthly',
                            'amount' => $pro->monthly_price,
                        ],
                    ],
                ]
            );

            Payment::query()->updateOrCreate(
                ['stripe_invoice_id' => $invoice->stripe_invoice_id],
                [
                    'user_id' => $subscriber->id,
                    'invoice_id' => $invoice->id,
                    'stripe_payment_intent_id' => 'pi_demo_'.Str::lower(Str::random(14)).$index,
                    'amount' => $invoice->amount,
                    'currency' => 'usd',
                    'status' => 'succeeded',
                    'description' => 'Pro (monthly)',
                    'paid_at' => $invoice->invoice_date,
                    'metadata' => ['seeded' => true],
                ]
            );
        }

        Payment::query()->updateOrCreate(
            ['stripe_invoice_id' => 'in_demo_failed_001'],
            [
                'user_id' => $subscriber->id,
                'amount' => $pro->monthly_price,
                'currency' => 'usd',
                'status' => 'failed',
                'description' => 'Failed card charge (demo)',
                'paid_at' => null,
                'metadata' => ['seeded' => true],
            ]
        );

        // Quiet unused variable warning style for static analysis readability.
        unset($admin, $freeUser);
    }
}

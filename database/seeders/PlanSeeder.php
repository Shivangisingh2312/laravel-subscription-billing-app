<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $prices = config('services.stripe.prices');

        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Essential billing tools for individuals getting started.',
                'monthly_price' => 999,
                'yearly_price' => 9900,
                'stripe_monthly_price_id' => $prices['basic_monthly'],
                'stripe_yearly_price_id' => $prices['basic_yearly'],
                'features' => [
                    'Up to 3 team members',
                    'Basic invoicing',
                    'Email support',
                    '14-day free trial',
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Advanced controls for growing teams and businesses.',
                'monthly_price' => 2999,
                'yearly_price' => 29900,
                'stripe_monthly_price_id' => $prices['pro_monthly'],
                'stripe_yearly_price_id' => $prices['pro_yearly'],
                'features' => [
                    'Up to 20 team members',
                    'Advanced reporting',
                    'Priority email support',
                    'Usage analytics',
                    '14-day free trial',
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Full platform access with dedicated support and controls.',
                'monthly_price' => 9999,
                'yearly_price' => 99900,
                'stripe_monthly_price_id' => $prices['enterprise_monthly'],
                'stripe_yearly_price_id' => $prices['enterprise_yearly'],
                'features' => [
                    'Unlimited team members',
                    'Custom SLAs',
                    'Dedicated account manager',
                    'SSO & advanced security',
                    '14-day free trial',
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                $plan + ['is_active' => true]
            );
        }
    }
}

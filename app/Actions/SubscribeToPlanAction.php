<?php

namespace App\Actions;

use App\Models\Plan;
use App\Models\User;
use Laravel\Cashier\Checkout;
use RuntimeException;

class SubscribeToPlanAction
{
    public function handle(User $user, Plan $plan, string $interval): Checkout
    {
        if (! in_array($interval, ['monthly', 'yearly'], true)) {
            throw new RuntimeException('Invalid billing interval.');
        }

        $stripePriceId = $plan->stripePriceIdFor($interval);

        if (blank($stripePriceId)) {
            throw new RuntimeException('This plan is not configured with a Stripe price ID.');
        }

        if ($user->subscribed('default')) {
            throw new RuntimeException('You already have an active subscription. Use upgrade or downgrade instead.');
        }

        $builder = $user->newSubscription('default', $stripePriceId);

        if (! $user->hasUsedTrial()) {
            $builder->trialDays(14);
        }

        return $builder->checkout([
            'success_url' => route('subscriptions.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('subscriptions.cancel'),
            'metadata' => [
                'plan_id' => (string) $plan->id,
                'billing_interval' => $interval,
            ],
        ]);
    }
}

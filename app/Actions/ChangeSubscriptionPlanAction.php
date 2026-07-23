<?php

namespace App\Actions;

use App\Models\Plan;
use App\Models\User;
use RuntimeException;

class ChangeSubscriptionPlanAction
{
    public function handle(User $user, Plan $plan, string $interval): void
    {
        if (! in_array($interval, ['monthly', 'yearly'], true)) {
            throw new RuntimeException('Invalid billing interval.');
        }

        $user->unsetRelation('subscriptions');

        $subscription = $user->subscription('default');

        if (! $subscription || (! $subscription->active() && ! $subscription->onTrial() && ! $subscription->onGracePeriod())) {
            throw new RuntimeException('No active subscription found.');
        }

        $stripePriceId = $plan->stripePriceIdFor($interval);

        if (blank($stripePriceId)) {
            throw new RuntimeException('This plan is not configured with a Stripe price ID.');
        }

        if ($subscription->stripe_price === $stripePriceId) {
            throw new RuntimeException('You are already on this plan.');
        }

        $stripeId = (string) $subscription->stripe_id;

        if (str_starts_with($stripeId, 'admin_sub_') || str_starts_with($stripeId, 'demo_sub_')) {
            $subscription->forceFill([
                'stripe_price' => $stripePriceId,
                'stripe_status' => 'active',
                'ends_at' => null,
            ])->save();

            return;
        }

        $subscription->swapAndInvoice($stripePriceId);
    }
}

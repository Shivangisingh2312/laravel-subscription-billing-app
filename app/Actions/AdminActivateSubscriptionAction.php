<?php

namespace App\Actions;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

class AdminActivateSubscriptionAction
{
    public function handle(User $user, int $planId, string $interval): void
    {
        if (! in_array($interval, ['monthly', 'yearly'], true)) {
            throw new RuntimeException('Invalid billing interval.');
        }

        $plan = Plan::query()->active()->findOrFail($planId);
        $stripePriceId = $plan->stripePriceIdFor($interval);

        if (blank($stripePriceId)) {
            throw new RuntimeException('Selected plan is missing Stripe price configuration.');
        }

        $existing = $user->subscription('default');

        if ($existing && ($existing->active() || $existing->onTrial() || $existing->onGracePeriod())) {
            throw new RuntimeException('User already has an active subscription. Cancel it first.');
        }

        // Local/admin activation for demo environments without calling Stripe.
        $user->subscriptions()->updateOrCreate(
            [
                'type' => 'default',
                'stripe_id' => $existing?->stripe_id ?? ('admin_sub_'.Str::lower(Str::random(16))),
            ],
            [
                'stripe_status' => 'active',
                'stripe_price' => $stripePriceId,
                'quantity' => 1,
                'trial_ends_at' => null,
                'ends_at' => null,
            ]
        );

        $user->unsetRelation('subscriptions');
    }
}

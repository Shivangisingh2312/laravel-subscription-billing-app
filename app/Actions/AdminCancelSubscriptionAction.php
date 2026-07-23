<?php

namespace App\Actions;

use App\Models\User;
use RuntimeException;

class AdminCancelSubscriptionAction
{
    public function handle(User $user, bool $immediately = true): void
    {
        $subscription = $user->subscription('default');

        if (! $subscription) {
            throw new RuntimeException('User does not have a subscription.');
        }

        // Admin-managed demo subscriptions are local-only (no live Stripe id prefix).
        if (str_starts_with((string) $subscription->stripe_id, 'admin_sub_')
            || str_starts_with((string) $subscription->stripe_id, 'demo_sub_')) {
            $subscription->forceFill([
                'stripe_status' => 'canceled',
                'ends_at' => now(),
            ])->save();

            return;
        }

        if ($immediately) {
            $subscription->cancelNow();

            return;
        }

        $subscription->cancel();
    }
}

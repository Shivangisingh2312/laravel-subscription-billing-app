<?php

namespace App\Actions;

use App\Models\User;
use RuntimeException;

class CancelSubscriptionAction
{
    public function handle(User $user, bool $immediately = false): void
    {
        $user->unsetRelation('subscriptions');

        $subscription = $user->subscription('default');

        if (! $subscription || (! $subscription->active() && ! $subscription->onTrial() && ! $subscription->onGracePeriod())) {
            throw new RuntimeException('No active subscription found.');
        }

        if ($this->isLocalSubscription($subscription->stripe_id)) {
            $subscription->forceFill([
                'stripe_status' => 'canceled',
                'ends_at' => $immediately ? now() : now()->addDays(30),
            ])->save();

            return;
        }

        if ($immediately) {
            $subscription->cancelNow();

            return;
        }

        $subscription->cancel();
    }

    private function isLocalSubscription(?string $stripeId): bool
    {
        $stripeId = (string) $stripeId;

        return str_starts_with($stripeId, 'admin_sub_')
            || str_starts_with($stripeId, 'demo_sub_');
    }
}

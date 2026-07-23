<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;

#[Fillable(['name', 'email', 'password', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, Notifiable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_admin' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function localInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function currentPlan(): ?Plan
    {
        $subscription = $this->subscription('default');

        if (! $subscription || (! $subscription->active() && ! $subscription->onTrial() && ! $subscription->onGracePeriod())) {
            return null;
        }

        if (! $subscription->stripe_price) {
            return null;
        }

        return Plan::findByStripePriceId($subscription->stripe_price);
    }

    public function currentBillingInterval(): ?string
    {
        $subscription = $this->subscription('default');
        $plan = $this->currentPlan();

        if (! $subscription?->stripe_price || ! $plan) {
            return null;
        }

        return $plan->intervalForStripePriceId($subscription->stripe_price);
    }

    public function hasUsedTrial(): bool
    {
        return $this->subscriptions()
            ->whereNotNull('trial_ends_at')
            ->exists();
    }

    public function subscriptionStatusLabel(): string
    {
        /** @var Subscription|null $subscription */
        $subscription = $this->subscription('default');

        if (! $subscription) {
            return 'No subscription';
        }

        if ($subscription->onTrial()) {
            return 'Trialing';
        }

        if ($subscription->onGracePeriod()) {
            return 'Canceling';
        }

        if ($subscription->canceled()) {
            return 'Canceled';
        }

        if ($subscription->pastDue()) {
            return 'Past due';
        }

        if ($subscription->active()) {
            return 'Active';
        }

        return ucfirst((string) $subscription->stripe_status);
    }
}

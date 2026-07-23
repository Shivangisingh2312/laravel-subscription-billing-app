<?php

use App\Models\Plan;
use App\Models\User;
use App\Support\MapsStripeExceptions;
use Stripe\Exception\AuthenticationException;

test('subscribe shows a friendly alert when stripe keys are missing', function () {
    config(['cashier.secret' => 'stripe_secret_placeholder']);

    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    $this->actingAs($user)
        ->from(route('plans.index'))
        ->post(route('subscriptions.store'), [
            'plan_id' => $plan->id,
            'interval' => 'monthly',
        ])
        ->assertRedirect(route('plans.index'))
        ->assertSessionHas('error');

    expect(session('error'))->toContain('Stripe is not configured correctly');
});

test('subscribe shows a friendly alert when stripe authentication fails', function () {
    config(['cashier.secret' => 'invalid_stripe_secret_for_testing']);

    $user = User::factory()->create();
    $plan = Plan::factory()->create([
        'stripe_monthly_price_id' => 'price_test_monthly',
    ]);

    $this->mock(\App\Actions\SubscribeToPlanAction::class, function ($mock): void {
        $mock->shouldReceive('handle')
            ->once()
            ->andThrow(AuthenticationException::factory(
                'Invalid API Key provided: sk_test_***',
                401,
                null,
                [],
                null
            ));
    });

    $this->actingAs($user)
        ->from(route('plans.index'))
        ->post(route('subscriptions.store'), [
            'plan_id' => $plan->id,
            'interval' => 'monthly',
        ])
        ->assertRedirect(route('plans.index'))
        ->assertSessionHas('error');

    expect(session('error'))->toContain('Stripe is not configured correctly');
});

test('maps stripe authentication exceptions to readable messages', function () {
    $exception = AuthenticationException::factory('Invalid API Key provided', 401, null, [], null);

    expect(MapsStripeExceptions::message($exception))
        ->toContain('Stripe is not configured correctly');
});

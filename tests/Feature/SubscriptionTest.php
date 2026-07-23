<?php

use App\Actions\AdminActivateSubscriptionAction;
use App\Models\Plan;
use App\Models\User;

test('users can cancel a local subscription', function () {
    $user = User::factory()->create();
    $plan = Plan::factory()->create();

    app(AdminActivateSubscriptionAction::class)->handle($user, $plan->id, 'monthly');

    expect($user->fresh()->subscribed('default'))->toBeTrue();

    $this->actingAs($user->fresh())
        ->delete(route('subscriptions.destroy'))
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->subscription('default')->canceled())->toBeTrue();
});

test('subscribe requires authentication', function () {
    $plan = Plan::factory()->create();

    $this->post(route('subscriptions.store'), [
        'plan_id' => $plan->id,
        'interval' => 'monthly',
    ])->assertRedirect(route('login'));
});

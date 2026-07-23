<?php

use App\Models\User;

test('admins can view the admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee('Total revenue');
});

test('non admins cannot access the admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admins can manually activate and cancel subscriptions', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $plan = \App\Models\Plan::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.users.subscription.activate', $user), [
            'plan_id' => $plan->id,
            'interval' => 'monthly',
        ])
        ->assertRedirect();

    expect($user->fresh()->subscribed('default'))->toBeTrue();

    $this->actingAs($admin)
        ->post(route('admin.users.subscription.cancel', $user), [
            'immediately' => 1,
        ])
        ->assertRedirect();

    expect($user->fresh()->subscription('default')->canceled())->toBeTrue();
});

<?php

use App\Models\User;

test('verified users can view the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Current subscription');
});

test('guests are redirected from the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

<?php

use App\Models\Plan;
use App\Models\User;

test('guests can view pricing plans', function () {
    Plan::factory()->create(['name' => 'Basic', 'slug' => 'basic', 'sort_order' => 1]);
    Plan::factory()->create(['name' => 'Pro', 'slug' => 'pro', 'sort_order' => 2]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Basic')
        ->assertSee('Pro');
});

test('verified users can view plans page', function () {
    $user = User::factory()->create();
    Plan::factory()->create(['name' => 'Enterprise', 'slug' => 'enterprise']);

    $this->actingAs($user)
        ->get(route('plans.index'))
        ->assertSuccessful()
        ->assertSee('Enterprise');
});

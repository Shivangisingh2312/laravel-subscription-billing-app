<?php

test('sync command fails when stripe is not configured', function () {
    config(['cashier.secret' => 'sk_test_your_secret_key']);

    $this->artisan('billing:sync-stripe-prices')
        ->assertFailed()
        ->expectsOutputToContain('Stripe secret key is missing');
});

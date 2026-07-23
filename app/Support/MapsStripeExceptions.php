<?php

namespace App\Support;

use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;
use Throwable;

class MapsStripeExceptions
{
    public static function message(Throwable $exception): string
    {
        if ($exception instanceof AuthenticationException) {
            return 'Stripe is not configured correctly. Add valid STRIPE_KEY and STRIPE_SECRET test keys to your .env file, then run: php artisan config:clear';
        }

        if ($exception instanceof CardException) {
            return $exception->getMessage() ?: 'Your card was declined. Please try another payment method.';
        }

        if ($exception instanceof RateLimitException) {
            return 'Stripe is temporarily busy. Please wait a moment and try again.';
        }

        if ($exception instanceof InvalidRequestException) {
            $message = $exception->getMessage();

            if (str_contains(strtolower($message), 'no such price')) {
                return 'This plan is not linked to a valid Stripe Price ID. Update the STRIPE_PRICE_* values in your .env file.';
            }

            return 'Stripe rejected the request: '.$message;
        }

        if ($exception instanceof ApiErrorException) {
            return 'Payment provider error: '.$exception->getMessage();
        }

        return $exception->getMessage() !== ''
            ? $exception->getMessage()
            : 'Something went wrong while processing your subscription. Please try again.';
    }

    public static function stripeIsConfigured(): bool
    {
        $secret = (string) config('cashier.secret');

        if ($secret === '' || str_contains($secret, 'your_secret_key') || str_contains($secret, 'sk_test_your')) {
            return false;
        }

        return str_starts_with($secret, 'sk_test_') || str_starts_with($secret, 'sk_live_');
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'prices' => [
            'basic_monthly' => env('STRIPE_PRICE_BASIC_MONTHLY', 'price_basic_monthly'),
            'basic_yearly' => env('STRIPE_PRICE_BASIC_YEARLY', 'price_basic_yearly'),
            'pro_monthly' => env('STRIPE_PRICE_PRO_MONTHLY', 'price_pro_monthly'),
            'pro_yearly' => env('STRIPE_PRICE_PRO_YEARLY', 'price_pro_yearly'),
            'enterprise_monthly' => env('STRIPE_PRICE_ENTERPRISE_MONTHLY', 'price_enterprise_monthly'),
            'enterprise_yearly' => env('STRIPE_PRICE_ENTERPRISE_YEARLY', 'price_enterprise_yearly'),
        ],
    ],

];

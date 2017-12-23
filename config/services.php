<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'url' => [
        'baHa' => env('BA_HA_URL', 'https://ani.gamer.com.tw/'),
        'comic99770' => env('COMIC_99770_URL', 'http://99770.hhxxee.com/comic/'),
        'twitch' => 'https://www.twitch.tv/',
    ],

    'api' => [
        'twitch' => 'https://api.twitch.tv',
    ],

    'slack' => [
        'channel' => '#general',
        'username' => 'Jarvis',
        'slash' => [
            'twitch' => env('SLASH_TOKEN_TWITCH'),
            'animation' => env('SLASH_TOKEN_ANIMATION'),
            'comic' => env('SLASH_TOKEN_COMIC'),
        ],
    ],

];

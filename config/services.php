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
            'secretary' => env('SLASH_TOKEN_SECRETARY'),
        ],
    ],

    'aws' => [
        'sms' => [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_REGION'),
            'from' => 'Tai',
            'max_price_usd' => '0.5',
        ]
    ],

    'bangumi' => [
        'api_endpoint' => 'https://api.bgm.tv',
        'app_id' => env('BGM_APP_ID'),
        'secret' => env('BGM_SECRET'),
    ],

    'anime1' => [
        'name' => 'Anime1.me 動畫線上看',
        'endpoint' => 'https://anime1.me/',
        'icon' => 'https://static.anime1.me/logo/260x260.png',
        'image' => 'https://static.anime1.me/playerImg/5.jpg',
    ],
];

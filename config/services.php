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

    // AI Scanner Penyakit Tanaman (BAB 5.3.1) — pilih provider deteksi lewat
    // AI_SCANNER_PROVIDER di .env:
    //   local   -> mesin klasifikasi katalog lokal (default, tanpa API key)
    //   plantid -> Plant.id API v3 (Kindwise), lihat PLANT_ID_API_KEY di bawah
    //   custom  -> API model AI custom (mis. server Python/TensorFlow sendiri)
    'ai_scanner' => [
        'provider' => env('AI_SCANNER_PROVIDER', 'local'),
        'url' => env('AI_SCANNER_API_URL'), // hanya dipakai provider "custom"
    ],

    // Plant.id API v3 (Kindwise) — https://web.plant.id / admin.kindwise.com
    'plant_id' => [
        'api_key' => env('PLANT_ID_API_KEY'),
    ],

];

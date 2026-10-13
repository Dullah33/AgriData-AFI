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
    //   gemini  -> Google Gemini Vision, lihat GEMINI_API_KEY di bawah (disarankan untuk production)
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

    // Google Gemini API — https://aistudio.google.com/apikey
    // Dipakai dua cara:
    //   1. Provider utama AI Scanner (AI_SCANNER_PROVIDER=gemini): foto
    //      langsung dianalisis Gemini Vision.
    //   2. Pelengkap field "penyebab" & "penanganan" saat Plant.id tidak
    //      menyediakan data tersebut untuk kelas penyakit tertentu.
    //
    // Default model memakai alias "gemini-flash-latest" yang selalu diarahkan
    // Google ke model Flash terbaru. Model versi tetap (mis. gemini-2.0-flash)
    // dimatikan Google secara berkala, dan begitu itu terjadi fitur scan ikut
    // mati. Kalau ingin hasil yang konsisten, pin ke versi tertentu lewat
    // GEMINI_MODEL, misalnya gemini-3.5-flash.
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-flash-latest'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 45),
    ],

    // Midtrans Snap (payment gateway) - https://dashboard.midtrans.com
    // Kalau server key kosong, metode Midtrans hanya muncul bila
    // MIDTRANS_SIMULASI=true dan aplikasi tidak berjalan di production,
    // supaya tidak ada yang bisa "membayar" lewat tombol simulasi di server asli.
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
        'simulasi' => (bool) env('MIDTRANS_SIMULASI', false),
    ],

];
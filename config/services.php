<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    /*
    |--------------------------------------------------------------------------
    | REST API Master Data Yayasan
    |--------------------------------------------------------------------------
    |
    | Modul Manajemen Akses & API Token (Sanctum) milik yayasan menyediakan data
    | master (Unit, Siswa, Karyawan & Guru, dst) lewat endpoint /api/v1/*.
    | Lihat PRD bagian "Kebutuhan Data dari Yayasan".
    |
    */
    'yayasan_master' => [
        'base_url' => env('YAYASAN_MASTER_API_URL', 'https://gevano.my.id/api/v1'),
        'token' => env('YAYASAN_MASTER_API_TOKEN'),
        'ca_bundle' => env('YAYASAN_MASTER_API_CA_BUNDLE'),
    ],

];

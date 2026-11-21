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

    'siakad' => [
        'url' => env('SIAKAD_API_URL', 'https://apisiakad.ponpesnaa.net/v1'),
        'auth_type' => env('SIAKAD_AUTH_TYPE', 'none'), // none | bearer | api_key
        'token' => env('SIAKAD_API_TOKEN'),
        'api_key_header' => env('SIAKAD_API_KEY_HEADER', 'X-API-Key'),
        'timeout' => (int) env('SIAKAD_API_TIMEOUT', 60),
        'retry_times' => (int) env('SIAKAD_API_RETRY', 2),
        'verify_ssl' => filter_var(env('SIAKAD_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN),
    ],

];

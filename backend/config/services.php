<?php

declare(strict_types=1);

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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'logto' => [
        'endpoint'                  => env('LOGTO_ENDPOINT', 'https://sso.home.test'),
        'app_id'                    => env('LOGTO_APP_ID'),
        'app_secret'                => env('LOGTO_APP_SECRET'),
        'redirect_uri'              => env('LOGTO_REDIRECT_URI', 'https://identity.home.test/auth/callback'),
        'post_logout_redirect_uri'  => env('LOGTO_POST_LOGOUT_REDIRECT_URI', env('APP_URL', 'https://identity.home.test')),
        'frontend_url'              => env('FRONTEND_URL', 'https://identity.home.test'),
        'scopes'                    => env('LOGTO_SCOPES', 'openid profile email phone offline_access roles'),
        'management_api_resource'   => env('LOGTO_MANAGEMENT_API_RESOURCE', 'https://default.logto.app/api'),
        'management_api_identifier' => env('LOGTO_MANAGEMENT_API_IDENTIFIER', 'https://default.logto.app/api'),
        'management_app_id'         => env('LOGTO_MANAGEMENT_APP_ID'),
        'management_app_secret'     => env('LOGTO_MANAGEMENT_APP_SECRET'),
        'webhook_signing_key'       => env('LOGTO_WEBHOOK_SIGNING_KEY', env('LOGTO_WEBHOOK_SECRET', '')),
    ],
];

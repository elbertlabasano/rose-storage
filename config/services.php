<?php

return [
    'keycloak' => [
        'client_id' => env('KEYCLOAK_CLIENT_ID', 'storage'),
        'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        'redirect' => env('KEYCLOAK_REDIRECT_URI'),
        'base_url' => env('KEYCLOAK_BASE_URL', 'https://sso.rosebakeshop.net'),
        'realms' => env('KEYCLOAK_REALM', 'jathnier'),
        'guzzle' => ['connect_timeout' => 5, 'timeout' => 20],
    ],
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'resend' => ['key' => env('RESEND_KEY')],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
];



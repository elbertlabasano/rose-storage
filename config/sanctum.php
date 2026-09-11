<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,localhost:3000')),
    'guard' => ['web'],
    'expiration' => null,
    'default_token_minutes' => (int) env('SANCTUM_DEFAULT_TOKEN_MINUTES', 1440),
    'token_max_seconds' => (int) env('SANCTUM_TOKEN_MAX_SECONDS', 31536000),
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];

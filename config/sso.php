<?php

return [
    'enabled' => (bool) env('SSO_ENABLED', false),
    'idcode_claim' => env('SSO_IDCODE_CLAIM', 'idcode'),
    'token_lifetime_minutes' => (int) env('SSO_TOKEN_LIFETIME_MINUTES', 120),
];

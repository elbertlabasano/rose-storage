<?php

require dirname(__DIR__).'/vendor/autoload.php';

define('ROSE_TEST_PROCESS', true);

// Ignore real .env values and any application config cache before Laravel boots.
$environment = [
    'APP_ENV' => 'testing',
    'APP_CONFIG_CACHE' => sys_get_temp_dir().'/rose-phpunit-'.bin2hex(random_bytes(16)).'.php',
    'APP_ROUTES_CACHE' => sys_get_temp_dir().'/rose-phpunit-routes-'.bin2hex(random_bytes(16)).'.php',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
];
foreach ($environment as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        $middleware->alias([
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn ($request, $e) =>
            $request->is('api/*') || $request->expectsJson()
        );
    })->create();

if (defined('ROSE_TEST_PROCESS') && ROSE_TEST_PROCESS) {
    $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
        $app['config']->set([
            'app.env' => 'testing',
            'database.default' => 'sqlite',
            'database.connections' => [
                'sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true],
            ],
            'cache.default' => 'array',
            'session.driver' => 'array',
            'queue.default' => 'sync',
            'filesystems.default' => 'local',
        ]);
        // Validate every newly requested connection, including runtime config overrides.
        $app->extend('db.factory', fn () => new \Tests\Support\SafeConnectionFactory($app));
    });
}

return $app;

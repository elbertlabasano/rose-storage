<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Always isolate the test suite from the developer's real database.
     *
     * This is intentionally enforced after Laravel boots so a cached local
     * configuration cannot cause RefreshDatabase to reset the MySQL database.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $app['config']->set([
            'app.env' => 'testing',
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
            'session.driver' => 'array',
            'queue.default' => 'sync',
            'filesystems.default' => 'local',
        ]);

        return $app;
    }
}
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        if (! defined('ROSE_TEST_PROCESS') || ! ROSE_TEST_PROCESS) {
            throw new \LogicException('Use phpunit.xml and tests/bootstrap.php to run tests safely.');
        }

        return parent::createApplication();
    }
}

<?php

namespace PrivateEvent\Apidox\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use PrivateEvent\Apidox\ApidoxServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [ApidoxServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.url', 'https://example.test');
    }
}

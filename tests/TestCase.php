<?php

namespace Authxolote\Sdk\Tests;

use Authxolote\Sdk\AuthxoloteServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            AuthxoloteServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('authxolote.url', 'https://authxolote.test/api');
        $app['config']->set('authxolote.token', 'fake-token');
        $app['config']->set('authxolote.debug', false);
        
        $app['config']->set('auth.guards.authxolote', [
            'driver' => 'authxolote',
            'provider' => 'users',
        ]);
    }
}

<?php

namespace Authxolote\Sdk;

use Authxolote\Sdk\Console\Commands\AttachRolesCommand;
use Authxolote\Sdk\Guards\AuthxoloteGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthxoloteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (file_exists(__DIR__.'/../config/authxolote.php')) {
            $this->mergeConfigFrom(__DIR__.'/../config/authxolote.php', 'authxolote');
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            if (file_exists(__DIR__.'/../config/authxolote.php')) {
                $this->publishes([
                    __DIR__.'/../config/authxolote.php' => config_path('authxolote.php'),
                ], 'authxolote-config');
            }

            $this->commands([
                AttachRolesCommand::class,
            ]);
        }

        Auth::extend('authxolote', function ($app, $name, array $config) {
            return new AuthxoloteGuard(
                $app['request'],
                config('authxolote.url'),
                config('authxolote.cache', true),
                config('authxolote.sync_user', true)
            );
        });
    }
}

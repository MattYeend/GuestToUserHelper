<?php

namespace MattYeend\GuestToUserHelper;

use Illuminate\Support\ServiceProvider;
use MattYeend\GuestToUserHelper\Http\Middleware\AssignGuestIdentifier;
use Illuminate\Foundation\Configuration\Middleware as MiddlewareConfig;

class GuestUpgradeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/guest-upgrade.php', 'guest-upgrade');

        $this->app->singleton(GuestMigrator::class, function ($app) {
            return new GuestMigrator(
                models: config('guest-upgrade.models', []),
                userKey: config('guest-upgrade.user_foreign_key', 'user_id')
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/guest-upgrade.php' => config_path('guest-upgrade.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        // Middleware alias for routes
        $this->app['router']->aliasMiddleware('guest.identifier', AssignGuestIdentifier::class);

        // Laravel 10/11 - push via Kernel
        if (file_exists(app_path('Http/Kernel.php'))) {
            $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
            $kernel->pushMiddleware(AssignGuestIdentifier::class);
        }
        // Laravel 12 - configure via MiddlewareConfig
        elseif (class_exists(MiddlewareConfig::class) && method_exists($this->app, 'afterResolving')) {
            $this->app->afterResolving(MiddlewareConfig::class, function (MiddlewareConfig $middleware) {
                $middleware->appendToGroup('web', AssignGuestIdentifier::class);
            });
        }
    }
}

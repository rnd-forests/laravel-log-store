<?php

namespace App\Providers;

use App\Components\Log\LogManager;
use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerManager();
    }

    /**
     * Register log manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton(LogManager::class, function ($app) {
            return new LogManager($app, $app->make('validator'));
        });

        $this->app->alias(LogManager::class, 'log-manager');
    }
}

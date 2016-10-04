<?php

namespace GenTux\PubSub;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(
            [
                __DIR__.'/config.php' => config_path('pubsub.php'),
            ]
        );

        $this->app->bind(
            \GenTux\PubSub\Contracts\PubSub::class,
            \GenTux\PubSub\Drivers\Google\PubSub::class
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config.php', 'pubsub');
    }
}

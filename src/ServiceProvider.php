<?php

namespace GenTux\GooglePubSub;

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
            \GenTux\GooglePubSub\Contracts\PubSub::class,
            \GenTux\GooglePubSub\Drivers\Google\PubSub::class
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config.php', 'queue.connections.pubsub');
    }
}
